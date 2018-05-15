<?php
/**
 * WPL_CronActions
 *
 * This class contains action hooks that are usually trigger via wp_cron()
 * 
 */

class WPL_CronActions extends WPL_Core {
	
	var $lockfile;

	public function __construct() {
		parent::__construct();
		
		// add cron handler
		add_action('wplister_update_auctions', 						array( &$this, 'cron_update_auctions' ) );
		add_action('wple_daily_schedule', 	   						array( &$this, 'cron_daily_schedule' ) );

		// add internal action hooks
		add_action('wple_clean_log_table', 							array( &$this, 'action_clean_log_table' ) );
		add_action('wple_clean_tables', 							array( &$this, 'action_clean_tables' ) );
		add_action('wple_clean_listing_archive', 					array( &$this, 'action_clean_listing_archive' ) );

		// add custom cron schedules
		add_filter( 'cron_schedules', 								array( &$this, 'cron_add_custom_schedules' ) );
 
 		// handle external cron calls
		add_action('wp_ajax_wplister_run_scheduled_tasks', 			array( &$this, 'cron_update_auctions' ) ); // wplister_run_scheduled_tasks
		add_action('wp_ajax_nopriv_wplister_run_scheduled_tasks', 	array( &$this, 'cron_update_auctions' ) );
		add_action('wp_ajax_wple_run_scheduled_tasks', 				array( &$this, 'cron_update_auctions' ) ); // wple_run_scheduled_tasks
		add_action('wp_ajax_nopriv_wple_run_scheduled_tasks', 		array( &$this, 'cron_update_auctions' ) );

	}

	
	// update auctions - called by wp_cron if activated
	public function cron_update_auctions() {
        WPLE()->logger->info("*** WP-CRON: cron_update_auctions()");

        // log cron run to db
		if ( get_option('wplister_log_to_db') == '1' ) {
            $dblogger = new WPL_EbatNs_Logger();
	        $dblogger->updateLog( array(
				'callname'    => 'cron_job_triggered',
				'request_url' => 'internal action hook',
				'request'     => maybe_serialize( $_REQUEST ),
				'response'    => 'last run: '.human_time_diff( get_option('wplister_cron_last_run') ).' ago',
				'success'     => 'Success'
	        ));
		}

        // check if this is a staging site
        if ( $this->isStagingSite() ) {
	        WPLE()->logger->info("WP-CRON: staging site detected! terminating execution...");
			self::updateOption( 'cron_auctions', '' );
			self::updateOption( 'create_orders', '' );
        	return;
        }

        // check if update is already running
        if ( ! $this->checkLock() ) {
	        WPLE()->logger->error("WP-CRON: already running! terminating execution...");
        	return;
        }

        // get accounts
		$accounts = WPLE_eBayAccount::getAll( false, true ); // sort by id
		if ( ! empty( $accounts) ) {

			// loop each active account
			$processed_accounts = array();
			foreach ( $accounts as $account ) {

				// make sure we don't process the same account twice
				if ( in_array( $account->user_name, $processed_accounts ) ) {
			        WPLE()->logger->info("skipping account {$account->id} - user name {$account->user_name} was already processed");
					continue;
				}

				$this->initEC( $account->id );
				$this->EC->updateEbayOrders();
				$this->EC->updateListings(); // TODO: specify account
				$this->EC->updateEbayMessages();
				$this->EC->closeEbay();
				$processed_accounts[] = $account->user_name;

			}

		} else {

			// fallback to pre 1.5.2 behaviour
			$this->initEC();
			$this->EC->updateEbayOrders();

			// update ended items and process relist schedule
			$this->EC->updateListings(); 
			$this->EC->closeEbay();

		}

		// check daily schedule - trigger now if not executed within 36 hours
        $last_run = get_option('wple_daily_cron_last_run');
        if ( $last_run < time() - 36 * 3600 ) {
	        WPLE()->logger->warn('*** WP-CRON: Daily schedule has NOT run since '.human_time_diff( $last_run ).' ago');
			do_action( 'wple_daily_schedule' );
        }


		// clean up
		$this->removeLock();

		// store timestamp
		self::updateOption( 'cron_last_run', time() );

        WPLE()->logger->info("*** WP-CRON: cron_update_auctions() finished");
	} // cron_update_auctions()


	// run daily schedule - called by wp_cron
	public function cron_daily_schedule() {
        WPLE()->logger->info("*** WP-CRON: cron_daily_schedule()");
        $manually = isset($_REQUEST['action']) && $_REQUEST['action'] == 'wple_run_daily_schedule' ? true : false;

		// clean log table
		do_action('wple_clean_log_table');
		do_action('wple_clean_tables');

		// clean archive
		do_action('wple_clean_listing_archive');

		// store timestamp
		update_option( 'wple_daily_cron_last_run', time() );

        WPLE()->logger->info("*** WP-CRON: cron_daily_schedule() finished");
        if ( $manually ) wple_show_message('Daily maintenance schedule was executed successfully.');
	}

	public function action_clean_log_table() {
		global $wpdb;
		// if ( get_option('wplister_log_to_db') == '1' ) {
		if ( $days_to_keep = get_option( 'wplister_log_days_limit', 30 ) ) {			
			$rows = $wpdb->query('DELETE FROM '.$wpdb->prefix.'ebay_log WHERE timestamp < DATE_SUB(NOW(), INTERVAL '.intval($days_to_keep).' DAY )');
			WPLE()->logger->info('Cleaned table ebay_log - affected rows: ' . $rows);

			$rows = $wpdb->query('DELETE FROM '.$wpdb->prefix.'ebay_jobs WHERE date_created < DATE_SUB(NOW(), INTERVAL '.intval($days_to_keep).' DAY )');
			WPLE()->logger->info('Cleaned table ebay_jobs - affected rows: ' . $rows);
		}
	} // action_clean_log_table()

	public function action_clean_tables() {
		global $wpdb;

		// clean orders table (date_created)
		$days_to_keep = get_option( 'wplister_orders_days_limit', '' );
		if ( $days_to_keep ) {
			$rows = $wpdb->query('DELETE FROM '.$wpdb->prefix.'ebay_orders WHERE date_created < DATE_SUB(NOW(), INTERVAL '.$days_to_keep.' DAY )');
			WPLE()->logger->info('Cleaned table ebay_orders - affected rows: ' . $rows);

			$rows = $wpdb->query('DELETE FROM '.$wpdb->prefix.'ebay_transactions WHERE date_created < DATE_SUB(NOW(), INTERVAL '.$days_to_keep.' DAY )');
			WPLE()->logger->info('Cleaned table ebay_transactions - affected rows: ' . $rows);
		}

	} // action_clean_tables()

	public function action_clean_listing_archive() {
		global $wpdb;
		if ( $days_to_keep = get_option( 'wplister_archive_days_limit', 90 ) ) {			
			$rows = $wpdb->query('DELETE FROM '.$wpdb->prefix.'ebay_auctions WHERE status = "archived" AND end_date < DATE_SUB(NOW(), INTERVAL '.intval($days_to_keep).' DAY )');
			WPLE()->logger->info('Cleaned table ebay_auctions - affected rows: ' . $rows);
		}
	} // action_clean_listing_archive()


	public function checkLock() {

		// get full path to lockfile
		$uploads        = wp_upload_dir();
		$lockfile       = $uploads['basedir'] . '/' . 'wplister_sync.lock';
		$this->lockfile = $lockfile;

		// skip locking if lockfile is not writeable
		if ( ! is_writable( $lockfile ) && ! is_writable( dirname( $lockfile ) ) ) {
	        WPLE()->logger->error("lockfile not writable: ".$lockfile);
	        return true;
		}

		// create lockfile if it doesn't exist
		if ( ! file_exists( $lockfile ) ) {
			$ts = time();
			file_put_contents( $lockfile, $ts );
	        WPLE()->logger->info("lockfile created at TS $ts: ".$lockfile);
	        return true;
		}

		// lockfile exists - check TS
		$ts = (int) file_get_contents($lockfile); 

		// check if TS is outdated (after 10min.)
		if ( $ts < ( time() - 600 ) ) { 
	        WPLE()->logger->info("stale lockfile found for TS ".$ts.' - '.human_time_diff( $ts ).' ago' );

	        // update lockfile 
			$ts = time();
			file_put_contents( $lockfile, $ts ); 
	        
	        WPLE()->logger->info("lockfile updated for TS $ts: ".$lockfile);
	        return true;
		} else { 
			// process is still alive - can not run twice
	        WPLE()->logger->info("SKIP CRON - sync already running with TS ".$ts.' - '.human_time_diff( $ts ).' ago' );
			return false; 
		} 

		return true;
	} // checkLock()

	public function removeLock() {
		if ( file_exists( $this->lockfile ) ) {
			unlink( $this->lockfile );
	        WPLE()->logger->info("lockfile was removed: ".$this->lockfile);
		}
	}

	public function cron_add_custom_schedules( $schedules ) {
		$schedules['five_min'] = array(
			'interval' => 60 * 5,
			'display' => 'Once every five minutes'
		);
		$schedules['ten_min'] = array(
			'interval' => 60 * 10,
			'display' => 'Once every ten minutes'
		);
		$schedules['fifteen_min'] = array(
			'interval' => 60 * 15,
			'display' => 'Once every fifteen minutes'
		);
		$schedules['thirty_min'] = array(
			'interval' => 60 * 30,
			'display' => 'Once every thirty minutes'
		);
		return $schedules;
	}


} // class WPL_CronActions

$WPL_CronActions = new WPL_CronActions();
