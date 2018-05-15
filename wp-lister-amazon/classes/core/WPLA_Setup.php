<?php

class WPLA_Setup extends WPLA_Core {
	
	// check if setup is incomplete and display next step
	public function checkSetup( $page = false ) {
		global $pagenow;

		// check if incomatible plugins are active
		$this->checkPlugins();

		// check if cURL is loaded
		if ( ! self::isCurlLoaded() ) return false;

		// check for windows server
		// if ( self::isWindowsServer() ) return false;
		self::isWindowsServer( $page );

		// create folders if neccessary
		// if ( self::checkFolders() ) return false;

		// check for updates
		self::checkForUpdates();

		// check if cron is working properly
		self::checkCron();

		// check if PHP, WooCommerce and WP are up to date
		self::checkVersions();

		// check for multisite installation
		// if ( self::checkMultisite() ) return false;

		// setup wizard
		// if ( self::getOption('amazon_token') == '' ) {
		if ( ( '1' == self::getOption('setup_next_step') ) && ( $page != 'settings') ) {
		
			$msg1 = __('You have not linked WP-Lister to your Amazon account yet.','wpla');
			$msg2 = __('To complete the setup procedure go to %s and follow the instructions.','wpla');
			$link = '<a href="admin.php?page=wpla-settings">'.__('Settings','wpla').'</a>';
			$msg2 = sprintf($msg2, $link);
			$msg = "<p><b>$msg1</b></p><p>$msg2</p>";
			wpla_show_message($msg);
		
			// update_option('wpla_setup_next_step', '0');
		
		}

		
		// db upgrade
		WPLA_UpgradeHelper::upgradeDB();

		// check if all db tables exist
		self::checkDatabaseTables( $page );

		// clean db
		// self::cleanDB();
	
	} // checkSetup()


	// clean database of old log records
	// TODO: hook this into daily cron schedule (DONE!)
	public function cleanDB() {
		global $wpdb;

		if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'wpla-settings' ) && ( self::getOption('log_to_db') == '1' ) ) {
			$days_to_keep = self::getOption( 'log_days_limit', 30 );		
			// $delete_count = $wpdb->get_var('SELECT count(id) FROM '.$wpdb->prefix.'amazon_log WHERE timestamp < DATE_SUB(NOW(), INTERVAL 1 MONTH )');
			$delete_count = $wpdb->get_var('SELECT count(id) FROM '.$wpdb->prefix.'amazon_log WHERE timestamp < DATE_SUB(NOW(), INTERVAL '.$days_to_keep.' DAY )');
			if ( $delete_count ) {
				$wpdb->query('DELETE FROM '.$wpdb->prefix.'amazon_log WHERE timestamp < DATE_SUB(NOW(), INTERVAL '.$days_to_keep.' DAY )');
				// $this->showMessage( __('Log entries cleaned: ','wpla') . $delete_count );
			}
		}
	}



	// check if cURL is loaded
	public function isCurlLoaded() {

		if( ! extension_loaded('curl') ) {
			wpla_show_message("
				<b>Required PHP extension missing</b><br>
				<br>
				Your server doesn't seem to have the <a href='http://www.php.net/curl' target='_blank'>cURL</a> php extension installed.<br>
				cURL ist required by WP-Lister to be able to talk with Amazon.<br>
				<br>
				On a recent debian based linux server running PHP 5 this should do the trick:<br>
				<br>
				<code>
					apt-get install php5-curl <br>
					/etc/init.d/apache2 restart
				</code>
				<br>
				<br>
				You'll require root access on your server to install additional php extensions!<br>
				If you are on a shared host, you need to ask your hoster to enable the cURL php extension for you.<br>
				<br>
				For more information on how to install the cURL php extension on other servers check <a href='http://stackoverflow.com/questions/1347146/how-to-enable-curl-in-php' target='_blank'>this page on stackoverflow</a>.
			",'error');
			return false;
		}

		return true;
	}

	// check server is running windows - or Solaris
	public function isWindowsServer( $page ) {

		if ( $page != 'settings' ) return;

		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {

			wpla_show_message("
				<b>Warning: Server requirements not met - this server runs on windows.</b><br>
				<br>
				WP-Lister currently only supports unixoid operating systems like Linux, FreeBSD and OS X.<br>
				Support for windows servers is still experimental and should not be used on production sites!
			",'warn');
			return true;
		}

		if (strtoupper(substr(PHP_OS, 0, 5)) === 'SUNOS') {

			wpla_show_message("
				<b>Warning: Server requirements not met - this server runs on Solaris (SunOS).</b><br>
				<br>
				WP-Lister for Amazon currently only supports Linux, FreeBSD and OS X.<br>
				Running WP-Lister on a Solaris server makes it impossible to communicate with the Amazon API.
			",'error');
			return true;
		}

		return false;
	}

	// check if WP_Cron is working properly
	public function checkCron() {

		$cron_interval  = get_option( 'wpla_cron_schedule' );
		$next_scheduled = wp_next_scheduled( 'wpla_update_schedule' ) ;
		if ( 'external' == $cron_interval ) $cron_interval = false;

		if ( $cron_interval && ! $next_scheduled ) {

			wpla_show_message( 
				'<p>'
				. '<b>Warning: WordPress Cron Job has been disabled - scheduled WP-Lister tasks are not executed!</b>'
				. '<br><br>'
				. 'The task schedule has been reset just now in order to automatically fix this.'
				. '<br><br>'
				. 'If this message does not disappear, please visit the <a href="admin.php?page=wpla-settings&tab=settings">Settings</a> page and click <i>Save Settings</i> or contact support.'
				. '</p>'
			,'warn');

			// this should fix it:
			wp_schedule_event( time(), $cron_interval, 'wpla_update_schedule' );

		}

		// schedule daily event if not set yet
		if ( ! wp_next_scheduled( 'wpla_daily_schedule' ) ) {
			wp_schedule_event( time(), 'daily', 'wpla_daily_schedule' );
		}

		// schedule FBA Shipment report request - if not set yet
		if ( ! wp_next_scheduled( 'wpla_fba_report_schedule' ) && ! WPLA_Setup::isStagingSite() ) {
			$schedule = get_option( 'wpla_fba_report_schedule', 'daily' );
			wp_schedule_event( time(), $schedule, 'wpla_fba_report_schedule' );
		}

	}

	// check versions
	public function checkVersions() {

		// WP-Lister for eBay 1.6+
		if ( defined('WPLISTER_VERSION') && version_compare( WPLISTER_VERSION, '1.6', '<') ) {
			wpla_show_message( 
				'<p>'
				. '<b>Warning: Your version of WP-Lister for eBay '.WPLISTER_VERSION.' is not fully compatible with WP-Lister for Amazon.</b>'
				. '<br><br>'
				. 'To prevent any issues, please update to WP-Lister for eBay 1.6 or better.'
				. '</p>'
			,'warn');
		}

		// check if WooCommerce is up to date
		$required_version    = '2.2.4';
		$woocommerce_version = defined('WC_VERSION') ? WC_VERSION : WOOCOMMERCE_VERSION;
		if ( version_compare( $woocommerce_version, $required_version ) < 0 ) {
			wpla_show_message("
				<b>Warning: Your WooCommerce version is outdated.</b><br>
				<br>
				WP-Lister requires WooCommerce $required_version to be installed. You are using WooCommerce $woocommerce_version.<br>
				You should always keep your site and plugins updated.<br>
			",'warn');
		}

		// PHP 5.3+
		if ( version_compare(phpversion(), '5.3', '<')) {
			wpla_show_message( 
				'<p>'
				. '<b>Warning: Your PHP version '.phpversion().' is outdated.</b>'
				. '<br><br>'
				. 'Your server should have PHP 5.3 or better installed.'
				. ' '
				. 'Please contact your hosting support and ask them to update your PHP version.'
				. '</p>'
			,'warn');
		}

		// OpenSSL 0.9.8o or later is required by Amazon (as of late 2015)
		// https://sellercentral.amazon.com/forums/ann.jspa?annID=284
		if ( defined('OPENSSL_VERSION_NUMBER') && ( OPENSSL_VERSION_NUMBER < 0x009080ff ) ) {
			wpla_show_message( 
				'<p>'
				. '<b>Warning: Your version of '.OPENSSL_VERSION_TEXT.' is outdated and not supported by Amazon anymore.</b>'
				. '<br><br>'
				. 'To prevent any issues communicating with the Amazon API, please ask your hosting provider to update OpenSSL to version 0.9.8o or better.'
				. '</p>'
			,'warn');
		}

	}


	// checks for incompatible plugins
	public function checkPlugins() {

		// // Plugin Name: SEO by SQUIRRLY
		// // Plugin URI: http://www.squirrly.co
		// // Plugin URI: https://wordpress.org/plugins/squirrly-seo/
		// // Version: 6.0.8
		// if ( defined('SQ_VERSION') && class_exists('SQ_ObjController') ) {

		// 	wpla_show_message("
		// 		<b>Warning: An incompatible plugin was found.</b><br>
		// 		<br>
		// 		You seem to have the <i>SEO by SQUIRRLY</i> plugin installed, which is known to cause issues with WP-Lister.<br>
		// 		Version 6.0.8 of this plugin prevents WP-Lister from being notified when a product is updated on the edit product page.<br>
		// 		It does so by calling <i>remove_action()</i> to remove the action hook for 'save_post' from within the method <i>hookSavePost()</i> which is triggered by executing the 'save_post' action in the first place.<br>
		// 		<br>
		// 		In order to use WP-Lister, you need to deactivate this plugin and use another SEO plugin - like the <i>Yoast SEO</i> plugin by Yoast.
		// 	",'warn');
		// 	return false;

		// }

	} // checkPlugins()


	// checks for multisite network
	public function checkMultisite() {

		if ( is_multisite() ) {

			// check for network activation
			if ( ! function_exists( 'is_plugin_active_for_network' ) )
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

			if ( function_exists('is_network_admin') && is_plugin_active_for_network( plugin_basename( WPLA_PATH.'/wp-lister-amazon.php' ) ) )
				wpla_show_message("network activated!");
			else
				wpla_show_message("not network activated!");


			// $this->showMessage("
			// 	<b>Multisite installation detected</b><br>
			// 	<br>
			// 	This is a site network...<br>
			// ");
			return true;
		}

		return false;
	}


	// check for updates
	public function checkForUpdates() {
		## BEGIN PRO ##
		// global $WPLA_CustomUpdater;
		// if ( ! class_exists('WPLA_CustomUpdater') ) return;

		// check if current user has permission to update plugins
		if ( ! current_user_can( 'update_plugins' ) ) return;

		$update = get_option( 'wpla_update_details' );

		if ( $update && is_object( $update ) ) {

			// check timestamp
			if ( ( time() - $update->timestamp ) > 24*3600 ) {
			
				// $update = $WPLA_CustomUpdater->check_for_new_version();
				$update = WPLAUP()->check_for_new_version( true );

			}

		} else {
			// $update = $WPLA_CustomUpdater->check_for_new_version();
			$update = WPLAUP()->check_for_new_version( true );
		}

		if ( $update && is_object( $update ) ) {

			if ( version_compare( $update->new_version, WPLA_VERSION ) > 0 ) {

				// $install_update_button = '<a href="update-core.php" class="button">'.__('Install Update','wpla') . '</a>';

				// generate update URL with nonce
				$slug    = 'wp-lister-amazon/wp-lister-amazon.php';
				$action  = 'upgrade-plugin';
				$btn_url = wp_nonce_url(
				    add_query_arg(
				        array(
				            'action' => $action,
				            'plugin' => $slug
				        ),
				        admin_url( 'update.php' )
				    ),
				    $action.'_'.$slug
				);
				$install_update_button = '<a href="'.$btn_url.'" class="button button-primary">'.__('Install Update','wpla') . '</a>';

				wpla_show_message( 
					'<p>'. sprintf( __('An update to %s is available.','wpla'),  $update->title . ' ' . $update->new_version )
					// . __('Please visit your WordPress Updates to install the new version.','wpla') . '<br><br>'
					. '&nbsp;&nbsp;'
					. '<a href="#"" onclick="jQuery(\'.update_details_info\').slideToggle();return false;" class="button">'.__('Show Details','wpla') . '</a>'
					. '&nbsp;&nbsp;&nbsp;'
					. $install_update_button
					. '</p>'
					. '<div class="update_details_info" style="display:none; border-top: 2px dashed #eee;">' 
					. ( $update->upgrade_html ? $update->upgrade_html . '<br>' : '' )
					. ( $update->upgrade_notice ? '<em>' . $update->upgrade_notice . '</em>' : '' )
					. '<br>'
					. '<em>Last checked: '.human_time_diff( $update->timestamp ) . ' ago</em>'
					. '</div>'
				,'warn');

			}

		}

		## END PRO ##
	}

	// check if all database tables exist
	static function checkDatabaseTables( $page ) {
		global $wpdb;

		if ( $page != 'settings' ) return;
		if ( 0 == get_option('wpla_db_version', 0) ) return;

		$required_tables = array(
			'amazon_accounts',
			'amazon_btg',
			'amazon_categories',
			'amazon_feed_templates',
			'amazon_feed_tpl_data',
			'amazon_feed_tpl_values',
			'amazon_feeds',
			'amazon_jobs',
			'amazon_listings',
			'amazon_log',
			'amazon_markets',
			'amazon_orders',
			'amazon_payment',
			'amazon_profiles',
			'amazon_reports',
			'amazon_shipping',
			'amazon_stock_log',
		);

		$tables  = $wpdb->get_col('show tables like "'.$wpdb->prefix.'amazon%" ');
		$missing = array();

		foreach ($required_tables as $tablename ) {
			if ( ! in_array( $wpdb->prefix.$tablename, $tables ) ) {
				$missing[] = $tablename;
			}
		}

		if ( ! empty($missing) ) {
			wpla_show_message( '<b>Error: The following table(s) are missing in your database: ' . join(', ', $missing) . '</b><br>Please contact support or reinstall WP-Lister from scratch, using the "Uninstall on removal" option.', 'error' );
		}

	} // checkDatabaseTables()


	// check if there are active accounts using the same MerchantID
	static function checkForAccountsWithSameMerchantID() {

		$found_accounts = WPLA_AmazonAccount::getDuplicateMerchantIDs();
		if ( empty( $found_accounts ) ) return;
		if ( get_option( 'wpla_fetch_orders_filter', 0 ) == 1 ) return;

		// show message
		$msg = '<b>Important: You are using the same Merchant ID on multiple accounts.</b>' . '<br><br>';
		$msg .= 'This is not a problem, but you need to enable the "Filter orders" setting option to make sure that orders are imported separately for each account.'. '<br>';
		$msg .= 'Currently that option is disabled, which can lead to problems where orders could get assigned to the wrong account or marketplace.'. '<br><br>';
		$msg .= 'Please note that when you enable that option, you need to add an account for every marketplace you are selling on, or WP-Lister will not be able to fetch all orders.'. '<br><br>';
		$msg .= sprintf('<a href="%s" class="button button-secondary">Open general settings page</a>', 'admin.php?page=wpla-settings' );
		wpla_show_message($msg,'warn');

	}


	// check for listings, profiles and orders using an invalid / nonexisting account
	static function checkDbForInvalidAccounts() {
		global $wpdb;
		$accounts              = WPLA()->accounts;
		$default_account_id    = get_option( 'wpla_default_account_id' );
		$default_account       = isset( $accounts[ $default_account_id ] ) ? $accounts[ $default_account_id ] : false;
		$default_account_title = $default_account ? $default_account->title : 'MISSING DEFAULT ACCOUNT';
		if ( empty($accounts) ) return;

		// get list of all active account IDs
		$active_account_ids = array();
		foreach ($accounts as $account) {
			$active_account_ids[] = $account->id;
		}
		$active_account_ids_sql = join(', ', $active_account_ids);

		// find data with invalid account IDs
		$listings_count = $wpdb->get_var("
			SELECT count(account_id)
			  FROM ".$wpdb->prefix."amazon_listings
			 WHERE NOT account_id IN ( $active_account_ids_sql )
		");
		$profiles_count = $wpdb->get_var("
			SELECT count(account_id)
			  FROM ".$wpdb->prefix."amazon_profiles
			 WHERE NOT account_id IN ( $active_account_ids_sql )
		");
		$orders_count = $wpdb->get_var("
			SELECT count(account_id)
			  FROM ".$wpdb->prefix."amazon_orders
			 WHERE NOT account_id IN ( $active_account_ids_sql )
		");

		// return if no problems found
		if ( ! $listings_count && ! $profiles_count && ! $orders_count ) return;

		// compile summary
		$what_exactly = array();
		if ( $listings_count ) $what_exactly[] = $listings_count . ' listings';
		if ( $profiles_count ) $what_exactly[] = $profiles_count . ' profiles';
		if ( $orders_count   ) $what_exactly[] = $orders_count   . ' orders';
		$what_exactly = join(' and ',$what_exactly);

		$btn_url = wp_nonce_url( 'admin.php?page=wpla-settings&tab=accounts&action=wpla_assign_invalid_data_to_default_account', 'wpla_assign_invalid_data_to_default_account' );

		// show message
		$msg = sprintf('<b>Warning: There are %s using an account which does not exist anymore.</b>',$what_exactly) . '<br><br>';
		$msg .= 'This can happen when you delete an account from WP-Lister without removing all listings, profiles and orders first.'. '<br>';
		$msg .= sprintf('Please click the button below to assign all found items to your default account <b>%s</b> (ID %s).', $default_account_title, $default_account_id ) . '<br><br>';
		$msg .= sprintf('<a href="%s" class="button button-secondary">Assign found items to default account</a>', $btn_url );
		wpla_show_message($msg,'warn');

	} // checkDbForInvalidAccounts()

	// fix listings, profiles and orders using an invalid / nonexisting account
	static function fixItemsUsingInvalidAccounts() {
		global $wpdb;
		$accounts           = WPLA()->accounts;
		$default_account_id = get_option( 'wpla_default_account_id' );
		$default_account    = isset( $accounts[ $default_account_id ] ) ? $accounts[ $default_account_id ] : false;
		if ( ! $default_account ) die('Invalid default account set!');

		// get list of all active account IDs
		$active_account_ids = array();
		foreach ($accounts as $account) {
			$active_account_ids[] = $account->id;
		}
		$active_account_ids_sql = join(', ', $active_account_ids);

		// find data with invalid account IDs
		$listings_count = $wpdb->get_var("
			UPDATE ".$wpdb->prefix."amazon_listings
			   SET account_id = '$default_account_id'
			 WHERE NOT account_id IN ( $active_account_ids_sql )
		");
		$profiles_count = $wpdb->get_var("
			UPDATE ".$wpdb->prefix."amazon_profiles
			   SET account_id = '$default_account_id'
			 WHERE NOT account_id IN ( $active_account_ids_sql )
		");
		$orders_count = $wpdb->get_var("
			UPDATE ".$wpdb->prefix."amazon_orders
			   SET account_id = '$default_account_id'
			 WHERE NOT account_id IN ( $active_account_ids_sql )
		");
		// echo $wpdb->last_query;
		echo $wpdb->last_error;

		// show message
		$msg = 'All found items have been assigned to your default account.';
		wpla_show_message($msg);

	} // fixItemsUsingInvalidAccounts()


	static public function isStagingSite() {
		$staging_site_pattern = get_option('wpla_staging_site_pattern');
		if ( ! $staging_site_pattern ) {
			update_option('wpla_staging_site_pattern','staging'); // if no pattern set, use default 'staging'
			return false;
		}

		$domain = $_SERVER["SERVER_NAME"];
		
		if ( preg_match( "/$staging_site_pattern/", $domain ) ) {
			return true;
		}
		if ( preg_match( "/wpstagecoach.com/", $domain ) ) {
			return true;
		}

		return false;
	}


} // class WPLA_Setup

