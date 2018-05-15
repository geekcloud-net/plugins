<?php
/**
 * WPLA_CronActions
 *
 * This class contains action hooks that are usually trigger via wp_cron()
 * 
 */

class WPLA_CronActions {

	var $dblogger;
	var $lockfile;

	public function __construct() {

		// add main cron handler
		add_action('wpla_update_schedule', 		array( &$this, 'cron_update_schedule' ) );
		add_action('wpla_daily_schedule', 		array( &$this, 'cron_daily_schedule' ) );
		add_action('wpla_fba_report_schedule', 	array( &$this, 'cron_fba_report_schedule' ) );

 		// handle external cron calls
		add_action('wp_ajax_wplister_run_scheduled_tasks', 			array( &$this, 'cron_update_schedule' ), 20 ); // wplister_run_scheduled_tasks
		add_action('wp_ajax_nopriv_wplister_run_scheduled_tasks', 	array( &$this, 'cron_update_schedule' ), 20 );
		add_action('wp_ajax_wpla_run_scheduled_tasks', 				array( &$this, 'cron_update_schedule' ), 20 ); // wpla_run_scheduled_tasks
		add_action('wp_ajax_nopriv_wpla_run_scheduled_tasks', 		array( &$this, 'cron_update_schedule' ), 20 );
		add_action('wp_ajax_wpla_request_inventory_report', 		array( &$this, 'request_daily_inventory_report' ) ); // wpla_request_inventory_report
		add_action('wp_ajax_nopriv_wpla_request_inventory_report', 	array( &$this, 'request_daily_inventory_report' ) );

		// add internal action hooks
		add_action('wpla_update_orders', 						array( &$this, 'action_update_orders' ) );
		add_action('wpla_update_reports', 						array( &$this, 'action_update_reports' ) );
		add_action('wpla_update_feeds', 						array( &$this, 'action_update_feeds' ) );
		add_action('wpla_submit_pending_feeds', 				array( &$this, 'action_submit_pending_feeds' ) );
		add_action('wpla_update_pricing_info',  				array( &$this, 'action_update_pricing_info' ) );
		add_action('wpla_update_missing_asins', 				array( &$this, 'action_update_missing_asins' ) );
		add_action('wpla_clean_log_table', 						array( &$this, 'action_clean_log_table' ) );
		add_action('wpla_clean_tables', 						array( &$this, 'action_clean_tables' ) );
		add_action('wpla_reprice_products', 					array( &$this, 'action_reprice_products' ) );
		add_action('wpla_autosubmit_fba_orders', 				array( &$this, 'action_autosubmit_fba_orders' ) );
		add_action('wpla_request_daily_quality_report', 		array( &$this, 'request_daily_quality_report' ) );
		add_action('wpla_request_daily_fba_report', 			array( &$this, 'request_daily_fba_report' ) );
		add_action('wpla_request_daily_inventory_report', 		array( &$this, 'request_daily_inventory_report' ) );
		add_action('wpla_request_daily_fba_shipments_report', 	array( &$this, 'request_daily_fba_shipments_report' ) );

		// add custom cron schedules
		add_filter( 'cron_schedules', array( &$this, 'cron_add_custom_schedules' ) );

		## BEGIN PRO ##
		// listen to checkout event
		add_action('woocommerce_reduce_order_stock', array( &$this, 'handle_reduce_order_stock'), 5, 1 );
		## END PRO ##

	}

	// run update schedule - called by wp_cron if activated
	public function cron_update_schedule() {
        WPLA()->logger->info("*** WP-CRON: cron_update_schedule()");

        // log cron run to db
		if ( get_option('wpla_log_to_db') == '1' ) {
			$dblogger = new WPLA_AmazonLogger();
			$dblogger->updateLog( array(
				'callname'    => 'cron_job_triggered',
				'request_url' => 'internal action hook',
				'request'     => maybe_serialize( $_REQUEST ),
				'response'    => 'last run: '.human_time_diff( get_option('wpla_cron_last_run') ).' ago',
				'success'     => 'Success'
	        ));
		}

        // check if this is a staging site
        if ( WPLA_Setup::isStagingSite() ) {
	        WPLA()->logger->info("WP-CRON: staging site detected! terminating execution...");

			update_option( 'wpla_cron_schedule', '' );
			update_option( 'wpla_create_orders', '' );

	        // remove scheduled event
		    $timestamp = wp_next_scheduled(  'wpla_update_schedule' );
	    	wp_unschedule_event( $timestamp, 'wpla_update_schedule' );

	        // remove scheduled event
		    $timestamp = wp_next_scheduled(  'wpla_fba_report_schedule' );
	    	wp_unschedule_event( $timestamp, 'wpla_fba_report_schedule' );

        	return;
        }

        // check if update is already running
        if ( ! $this->checkLock() ) {
	        WPLA()->logger->error("WP-CRON: already running! terminating execution...");
        	return;
        }

		// update reports - checks for submitted reports automatically now
		do_action('wpla_update_reports');

		// update feeds - checks for submitted feeds itself now
		do_action('wpla_update_feeds');

        // update orders
		do_action('wpla_update_orders');

		// submit pending feeds - after processing orders!
		do_action('wpla_submit_pending_feeds');

		// run full cron job not more often than every 5 minutes (270s = 4.5min / 30s buffer)
		$ts_last_full_run = get_option( 'wpla_cron_last_full_run', 0 );
		if ( $ts_last_full_run < time() - 270 ) {
	
	        // update pricing info
			do_action('wpla_update_pricing_info');

	        // update missing ASINs
			do_action('wpla_update_missing_asins');

			## BEGIN PRO ##
	        // run repricing tool - if enabled
			if ( get_option( 'wpla_enable_auto_repricing' ) )
				do_action('wpla_reprice_products');

	        // submit FBA orders - if enabled
			if ( get_option( 'wpla_fba_autosubmit_orders' ) )
				do_action('wpla_autosubmit_fba_orders');
			## END PRO ##

			// store timestamp
			update_option( 'wpla_cron_last_full_run', time() );
	
		}

		// check daily schedule - trigger now if not executed within 36 hours
        $last_run = get_option('wpla_daily_cron_last_run');
        if ( $last_run < time() - 36 * 3600 ) {
	        WPLA()->logger->warn('*** WP-CRON: Daily schedule has NOT run since '.human_time_diff( $last_run ).' ago');
			do_action( 'wpla_daily_schedule' );
			do_action( 'wpla_fba_report_schedule' ); // if the daily schedule didn't run, we can assume the FBA schedule didn't run either
        }


		// clean up
		$this->removeLock();

		// store timestamp
		update_option( 'wpla_cron_last_run', time() );

        WPLA()->logger->info("*** WP-CRON: cron_update_schedule() finished");
	}

	// run daily schedule - called by wp_cron
	public function cron_daily_schedule() {
        WPLA()->logger->info("*** WP-CRON: cron_daily_schedule()");

    	// check timestamp - do not run daily schedule more often than every 24 hours (except when triggered manually)
        $last_run = get_option('wpla_daily_cron_last_run');
        $manually = isset($_REQUEST['action']) && $_REQUEST['action'] == 'wpla_run_daily_schedule' ? true : false;
        if ( $last_run > time() - 24 * 3600 && ! $manually ) {
	        WPLA()->logger->warn('*** WP-CRON: cron_daily_schedule() EXIT - last run: '.human_time_diff( $last_run ).' ago');
	        return;
        }

		// request daily inventory report
		if ( get_option( 'wpla_autofetch_inventory_report' ) && ! WPLA_Setup::isStagingSite() )
			do_action('wpla_request_daily_inventory_report');

		// request daily listing quality report
		if ( get_option( 'wpla_autofetch_listing_quality_feeds', 1 ) && ! WPLA_Setup::isStagingSite() )
			do_action('wpla_request_daily_quality_report');

		// clean tables
		do_action('wpla_clean_log_table');
		do_action('wpla_clean_tables');

		// store timestamp
		update_option( 'wpla_daily_cron_last_run', time() );

        WPLA()->logger->info("*** WP-CRON: cron_daily_schedule() finished");
        if ( $manually ) wpla_show_message('Daily maintenance schedule was executed successfully.');
	}

	// run FBA report schedule - called by wp_cron
	public function cron_fba_report_schedule() {
        WPLA()->logger->info("*** WP-CRON: cron_fba_report_schedule()");

    	// check timestamp - do not run FBA schedule more often than every 3 hours
        $last_run = get_option('wpla_fba_report_cron_last_run');
        if ( $last_run > time() -  3 * 3600 ) {
	        WPLA()->logger->warn('*** WP-CRON: cron_fba_report_schedule() EXIT - last run: '.human_time_diff( $last_run ).' ago');
	        return;
        }

		// request FBA shipments report
		if ( get_option( 'wpla_fba_enabled' ) && ! WPLA_Setup::isStagingSite() )
			do_action('wpla_request_daily_fba_shipments_report');

		// request daily FBA inventory report
		if ( get_option( 'wpla_fba_enabled' ) && ! WPLA_Setup::isStagingSite() )
			do_action('wpla_request_daily_fba_report');

		// store timestamp
		update_option( 'wpla_fba_report_cron_last_run', time() );

        WPLA()->logger->info("*** WP-CRON: cron_fba_report_schedule() finished");
	}


	// fetch missing ASINs - called by do_action()
	public function action_update_missing_asins() {
        WPLA()->logger->info("do_action: wpla_update_missing_asins");

		$accounts      = WPLA_AmazonAccount::getAll();
		$listingsModel = new WPLA_ListingsModel();
		$batch_size    = 10; // update 10 items at a time

		foreach ($accounts as $account ) {

			$account_id    = $account->id;
			$listings      = $listingsModel->getAllOnlineWithoutASIN( $account_id, 10, OBJECT_K );
			if ( empty($listings) ) continue;

			// process one listing at a time (for now)
			foreach ( $listings as $listing ) {
				WPLA()->logger->info('fetching ASIN for SKU '.$listing->sku.' ('.$listing->id.') - type: '.$listing->product_type );
	
				$api    = new WPLA_AmazonAPI( $account->id );
				$result = $api->getMatchingProductForId( $listing->sku, 'SellerSKU' );

				if ( $result->success )  {

					if ( ! empty( $result->product->ASIN ) ) {

						// update listing ASIN
						$listingsModel->updateWhere( array( 'id' => $listing->id ), array( 'asin' => $result->product->ASIN ) );
						WPLA()->logger->info('new ASIN for listing #'.$listing->id . ': '.$result->product->ASIN );

					} else {

						// this is what happens when new products are listed but Amazon fails to assign an ASIN
						// in which case the user might have to contact Amazon seller support...
						$error_msg  = sprintf( __('There was a problem fetching product details for %s.','wpla'), $listing->sku );
						WPLA()->logger->error( $error_msg . ' - empty product data!');

						$error_msg  .= ' This SKU does not seem to exist in your inventory on Seller Central. You can try to submit this listing again, but you may have to report this to Amazon Seller Support.';

						// // build history array (overwrite)
						// $history = array (
						//   	'errors' => array (
						// 	    array (
						// 			'original-record-number' => '0',
						// 			'sku'                    => $listing->sku,
						// 			'error-code'             => '42',
						// 			'error-type'             => 'Error',
						// 			'error-message'          => $error_msg,
						// 	    ),
						// 	),
						//   	'warnings' => array(),
						// );

						// load existing history data - or init new history array
						$history = maybe_unserialize( $listing->history );
						if ( ! is_array($history) || ! isset($history['errors'] ) ) {
							$history = array (
								'errors'   => array(),
								'warnings' => array(),
							);
						}

						// add custom error to history array
						$error_42 = array (
							'original-record-number' => '0',
							'sku'                    => $listing->sku,
							'error-code'             => '42',
							'error-type'             => 'Error',
							'error-message'          => $error_msg,
						);
						$history['errors'][] = $error_42;

						// mark as failed - and include error message
						$listingsModel->updateWhere( array( 'id' => $listing->id ), array( 'status' => 'failed', 'history' => serialize($history) ) );

					}

				} elseif ( $result->Error && $result->Error->Message ) {
					$errors  = sprintf( __('There was a problem fetching product details for %s.','wpla'), $listing->sku ) .'<br>Error: '. $result->Error->Message;
					WPLA()->logger->error( $errors );
				} else {
					$errors  = sprintf( __('There was a problem fetching product details for %s.','wpla'), $listing->sku );
					WPLA()->logger->error( $errors );
				}
	
			} // foreach listing

		} // each account

	} // action_update_missing_asins ()


	// fetch lowest prices - called by do_action()
	public function action_update_pricing_info() {
        WPLA()->logger->info("do_action: wpla_update_pricing_info");

		$accounts = WPLA_AmazonAccount::getAll();
		// $listingsModel = new WPLA_ListingsModel();
		$batch_size = 200; // 10 requests per batch (for now - maximum should be 20 requests = 400 items / max. 600 items per minute)

		foreach ($accounts as $account ) {

			$account_id    = $account->id;
			$listings      = WPLA_ListingQueryHelper::getItemsDueForPricingUpdateForAcccount( $account_id, $batch_size );
			$listing_ASINs = array();
			WPLA()->logger->info( sprintf( '%s items with outdated pricing info found for account %s.', sizeof($listings), $account->title ) );

			// build array of ASINs
        	foreach ($listings as $listing) {
        		// skip duplicate ASINs - they throw an error from Amazon
        		if ( in_array( $listing->asin, $listing_ASINs ) ) continue;

       			$listing_ASINs[] = $listing->asin;        			
        	}
        	if ( empty($listing_ASINs) ) continue;


        	// process batches of 20 ASINs
        	for ($page=0; $page < 10; $page++) { 
        		$page_size = 20;

        		// splice ASINs
        		$offset = $page * $page_size;
        		$ASINs_for_this_batch = array_slice( $listing_ASINs, $offset, $page_size );
        		if ( empty($ASINs_for_this_batch) ) continue;

        		// run update
	        	$this->update_pricing_info_for_asins( $ASINs_for_this_batch, $account_id );

				WPLA()->logger->info( sprintf( '%s ASINs had their pricing info updated - account %s.', sizeof($listing_ASINs), $account->title ) );
        	}


		} // each account

	} // action_update_pricing_info ()


	// fetch and process lowest price info for up to 20 ASINs
	public function update_pricing_info_for_asins( $listing_ASINs, $account_id ) {
		$listingsModel = new WPLA_ListingsModel();

    	// fetch Buy Box pricing info and process result
		$api    = new WPLA_AmazonAPI( $account_id );
		$result = $api->getCompetitivePricingForId( $listing_ASINs );
		$listingsModel->processBuyBoxPricingResult( $result, $account_id );

		// return if lowest offers are disabled
		// if ( ! get_option('wpla_repricing_use_lowest_offer') ) return;

    	// fetch Lowest Offer info and process result
		$api    = new WPLA_AmazonAPI( $account_id );
		$result = $api->getLowestOfferListingsForASIN( $listing_ASINs );
		$listingsModel->processLowestOfferPricingResult( $result, $account_id );

	} // update_pricing_info_for_asins ()


	// apply lowest prices - called by do_action()
	public function action_reprice_products() {
		## BEGIN PRO ##
        WPLA()->logger->info("do_action: wpla_reprice_products");

        // reprice products
		$changed_product_ids = WPLA_RepricingHelper::repriceProducts();

		do_action( 'wpla_repriced_products', $changed_product_ids );

		WPLA()->logger->info( sprintf( '%s product prices have been updated.', sizeof($changed_product_ids) ) );
		## END PRO ##
	} // action_reprice_products ()


	// auto submit FBA orders - called by do_action()
	public function action_autosubmit_fba_orders() {
		## BEGIN PRO ##
        WPLA()->logger->info("do_action: wpla_autosubmit_fba_orders");

        // only run if FBA is enabled #15403
        if ( ! get_option( 'wpla_fba_enabled' ) ) {
            WPLA()->logger->info( 'action_autosubmit_fba_orders() skipped because FBA is disabled' );
            return;
        }

        $orders = WPLA_FbaHelper::getRecentOrders();
		$msg    = '';
		// $msg .= "total orders: ".count($orders)."<br>";

		foreach ($orders as $order_post) {

			// check if order can be fulfilled via FBA
			$checkresult = WPLA_FbaHelper::orderCanBeFulfilledViaFBA( $order_post, true );

			// if there are items found for FBA, create new FBA feed
			if ( is_array($checkresult) && ! empty($checkresult) ) {

				WPLA_FbaHelper::submitOrderToFBA( $order_post->ID );

				$msg = "Order ID {$order_post->ID} from {$order_post->post_date} was submitted to FBA.";
		        wpla_show_message($msg);
		        WPLA()->logger->info($msg);

			} else {
				$msg .= "Order ID {$order_post->ID} from {$order_post->post_date} was skipped: $checkresult <br>";
			}

		} // foreach order

        if ( $msg ) wpla_show_message($msg);
		## END PRO ##
	} // action_autosubmit_fba_orders ()


	// fetch new orders - called by do_action()
	public function action_update_orders() {
        WPLA()->logger->info("do_action: wpla_update_orders");

		$accounts = WPLA_AmazonAccount::getAll();

		foreach ($accounts as $account ) {

			$api = new WPLA_AmazonAPI( $account->id );

			// get date of last order
			$om = new WPLA_OrdersModel();
			$lastdate = $om->getDateOfLastOrder( $account->id );
			WPLA()->logger->info('getDateOfLastOrder() returned: '.$lastdate);

			$days = isset($_REQUEST['days']) && $_REQUEST['days'] ? $_REQUEST['days'] : false;
			if ( ! $lastdate && ! $days ) $days = 1;

			// get orders
			$orders = $api->getOrders( $lastdate, $days );
			// echo "<pre>";print_r($orders);echo"</pre>";#die();

			if ( is_array( $orders ) ) {

				// run the import
				$importer = new WPLA_OrdersImporter();
				$success = $importer->importOrders( $orders, $account );

				$msg  = sprintf( __('%s order(s) were processed for account %s.','wpla'), sizeof($orders), $account->title );
				if ( $importer->updated_count  > 0 ) $msg .= "\n".'Updated orders: '.$importer->updated_count ;
				if ( $importer->imported_count > 0 ) $msg .= "\n".'Created orders: '.$importer->imported_count;					
				WPLA()->logger->info( $msg );
				$this->showMessage( nl2br($msg),0,1 );

			} elseif ( $orders->Error->Message ) {
				$msg = sprintf( __('There was a problem downloading orders for account %s.','wpla'), $account->title ) .' - Error: '. $orders->Error->Message;
				WPLA()->logger->error( $msg );
				$this->showMessage( nl2br($msg),1,1 );
			} else {
				$msg = sprintf( __('There was a problem downloading orders for account %s.','wpla'), $account->title );
				WPLA()->logger->error( $msg );
				$this->showMessage( nl2br($msg),1,1 );
			}

		}
		$this->message = '';

	} // action_update_orders()


	// update submitted reports - called by do_action()
	public function action_update_reports() {
        WPLA()->logger->info("do_action: wpla_update_reports");

        // reset counter - will be updated in processReportsRequestList()
		update_option( 'wpla_reports_in_progress', 0 );

		$accounts = WPLA_AmazonAccount::getAll();

		foreach ($accounts as $account ) {

			// get all submitted reports for this account
			$submitted_reports = WPLA_AmazonReport::getSubmittedReportsForAccount( $account->id );
			$ReportRequestIds = array();
			foreach ($submitted_reports as $report) {
				$ReportRequestIds[] = $report->ReportRequestId;
			}

			// do nothing if no submitted reports are found (disable to fetch all recent reports)
			if ( empty( $ReportRequestIds ) ) {
				$msg  = sprintf( __('No pending report request(s) for account %s.','wpla'), $account->title );
				WPLA()->logger->info( $msg );
				$this->showMessage( nl2br($msg),0,1 );
				continue;
			}

			// get report requests
			$api = new WPLA_AmazonAPI( $account->id );
			$reports = $api->getReportRequestList_v2( $ReportRequestIds );

			if ( is_array( $reports ) )  {

				// process report request list
				WPLA_AmazonReport::processReportsRequestList( $reports, $account, true );

				$msg  = sprintf( __('%s report request(s) were found for account %s.','wpla'), sizeof($reports), $account->title );
				WPLA()->logger->info( $msg );
				$this->showMessage( nl2br($msg),0,1 );

			} elseif ( $reports->Error->Message ) {
				$msg = sprintf( __('There was a problem fetching report requests for account %s.','wpla'), $account->title ) .' - Error: '. $reports->Error->Message;
				WPLA()->logger->error( $msg );
				$this->showMessage( nl2br($msg),1,1 );
			} else {
				$msg = sprintf( __('There was a problem fetching report requests for account %s.','wpla'), $account->title );
				WPLA()->logger->error( $msg );
				$this->showMessage( nl2br($msg),1,1 );
			}

		}

	} // action_update_reports()


	// update submitted feeds - called by do_action()
	public function action_update_feeds() {
        WPLA()->logger->info("do_action: wpla_update_feeds");

		$accounts = WPLA_AmazonAccount::getAll();
		$feeds_in_progress = 0;

		foreach ( $accounts as $account ) {

			// get all submitted feeds for this account
			$submitted_feeds = WPLA_AmazonFeed::getSubmittedFeedsForAccount( $account->id );
			$FeedSubmissionIds = array();
			foreach ($submitted_feeds as $feed) {
				$FeedSubmissionIds[] = $feed->FeedSubmissionId;
			}

			// do nothing if no submitted feeds are found (disable to fetch all recent feeds)
			if ( empty( $FeedSubmissionIds ) ) {
				$msg  = sprintf( __('No submitted feeds found for account %s.','wpla'), $account->title );
				WPLA()->logger->info( $msg );
				$this->showMessage( nl2br($msg),0,1 );
				continue;
			}

			// get feed submissions
			$api = new WPLA_AmazonAPI( $account->id );
			$feeds = $api->getFeedSubmissionList( $FeedSubmissionIds );

			if ( is_array( $feeds ) )  {

				// process feed submission list
				$feeds_in_progress += WPLA_AmazonFeed::processFeedsSubmissionList( $feeds, $account );

				$msg  = sprintf( __('%s feed submission(s) were found for account %s.','wpla'), sizeof($feeds), $account->title );
				WPLA()->logger->info( $msg );
				$this->showMessage( nl2br($msg),0,1 );

			} elseif ( $feeds->Error->Message ) {
				$msg = sprintf( __('There was a problem fetching feed submissions for account %s.','wpla'), $account->title ) .' - Error: '. $feeds->Error->Message;
				WPLA()->logger->error( $msg );
				$this->showMessage( nl2br($msg),1,1 );
			} else {
				$msg = sprintf( __('There was a problem fetching feed submissions for account %s.','wpla'), $account->title );
				WPLA()->logger->error( $msg );
				$this->showMessage( nl2br($msg),1,1 );
			}

		}

		// update feed progress status
		update_option( 'wpla_feeds_in_progress', $feeds_in_progress );

	} // action_update_feeds()


	// submit pending feeds - called by do_action()
	public function action_submit_pending_feeds() {
        WPLA()->logger->info("do_action: wpla_submit_pending_feeds");

		$accounts = WPLA_AmazonAccount::getAll();

		foreach ($accounts as $account ) {

			// refresh feeds
			WPLA_AmazonFeed::updatePendingFeedForAccount( $account );

			// get pending feeds for account
			$feeds = WPLA_AmazonFeed::getAllPendingFeedsForAccount( $account->id );
	        WPLA()->logger->info("found ".sizeof($feeds)." pending feeds for account {$account->id}");

			foreach ($feeds as $feed) {

				$autosubmit_feeds = array(
					'_POST_FLAT_FILE_PRICEANDQUANTITYONLY_UPDATE_DATA_',	// Price and Quantity Update Feed
					'_POST_FLAT_FILE_LISTINGS_DATA_',						// Flat File Listings Feed
					'_POST_FLAT_FILE_FULFILLMENT_DATA_',					// Order Fulfillment Feed
					'_POST_FLAT_FILE_FULFILLMENT_ORDER_REQUEST_DATA_',		// Flat File FBA Shipment Injection Fulfillment Feed
					'_POST_FLAT_FILE_INVLOADER_DATA_',						// Inventory Loader Feed (Product Removal)
				);

				if ( ! in_array( $feed->FeedType, $autosubmit_feeds ) ) {
			        WPLA()->logger->info("skipped pending feed {$feed->id} ({$feed->FeedType}) for account {$account->id} - autosubmit disabled for feed type");
			        continue;
				}

				// submit feed
				$feed->submit();

		        WPLA()->logger->info("submitted pending feed {$feed->id} ({$feed->FeedType}) for account {$account->id}");
			}

		}

	} // action_submit_pending_feeds()




	// request listing quality reports for all active accounts
	public function request_daily_quality_report() {

		$report_type = '_GET_MERCHANT_LISTINGS_DEFECT_DATA_';
		$accounts    = WPLA_AmazonAccount::getAll();

		foreach ($accounts as $account ) {

			$api = new WPLA_AmazonAPI( $account->id );

			// request report - returns request list as array on success
			$reports = $api->requestReport( $report_type );

			if ( is_array( $reports ) )  {

				// process the result
				WPLA_AmazonReport::processReportsRequestList( $reports, $account, true );

			} elseif ( $reports->Error->Message ) {
			} else {
			}

		} // foreach account

	} // request_daily_quality_report()


	// request FBA inventory reports for all active accounts
	public function request_daily_fba_report() {

		$report_type = '_GET_AFN_INVENTORY_DATA_';
		$accounts    = WPLA_AmazonAccount::getAll();

		foreach ($accounts as $account ) {

			$api = new WPLA_AmazonAPI( $account->id );

			// request report - returns request list as array on success
			$reports = $api->requestReport( $report_type );

			if ( is_array( $reports ) )  {

				// process the result
				WPLA_AmazonReport::processReportsRequestList( $reports, $account, true );

			} elseif ( $reports->Error->Message ) {
			} else {
			}

		} // foreach account

	} // request_daily_fba_report()


	// request FBA fulfilled shipment reports for all active accounts
	public function request_daily_fba_shipments_report() {

		$report_type = '_GET_AMAZON_FULFILLED_SHIPMENTS_DATA_';
		$accounts    = WPLA_AmazonAccount::getAll();

		foreach ($accounts as $account ) {

			$api = new WPLA_AmazonAPI( $account->id );

			// request report - returns request list as array on success
			$reports = $api->requestReport( $report_type );

			if ( is_array( $reports ) )  {

				// process the result
				WPLA_AmazonReport::processReportsRequestList( $reports, $account, true );

			} elseif ( $reports->Error->Message ) {
			} else {
			}

		} // foreach account

	} // request_daily_fba_shipments_report()


	// request merchant inventory reports for all active accounts
	public function request_daily_inventory_report() {

		$report_type = '_GET_MERCHANT_LISTINGS_DATA_';
		$accounts    = WPLA_AmazonAccount::getAll();

		foreach ($accounts as $account ) {

			$api = new WPLA_AmazonAPI( $account->id );

			// request report - returns request list as array on success
			$reports = $api->requestReport( $report_type );

			if ( is_array( $reports ) )  {

				// process the result
				WPLA_AmazonReport::processReportsRequestList( $reports, $account, true );

			} elseif ( $reports->Error->Message ) {
			} else {
			}

		} // foreach account

	} // request_daily_inventory_report()




	## BEGIN PRO ##
	public function handle_reduce_order_stock( $order ) {
		global $woocommerce;

		// check if inventory sync is enabled
		if ( ! get_option( 'wpla_sync_inventory' ) ) return;

		$listingsModel = new WPLA_ListingsModel();
		// $cart = $woocommerce->cart->get_cart();

		WPLA()->logger->info('handle order #'.wpla_get_order_meta( $order, 'id' ) );
		// WPLA()->logger->info('posted: '.print_r($posted,1) );
		// WPLA()->logger->info('cart: '.print_r($cart,1) );

		// Reduce stock levels and do any other actions with products in the cart
		$post_IDs   = array();
		$cart_items = array();
		foreach ( $order->get_items() as $item ) {

			if ( (@$item['id']>0) || (@$item['product_id']>0) ) {

				// get post ID for WC2.0 and WC1.x
				$post_id = isset( $item['product_id'] ) ? $item['product_id'] : $item['id'];
				WPLA()->logger->info('processing reduce stock for product #' . $post_id . '');

				// check if this is a variable product and get SKU
				$variation_id = isset( $item['variation_id'] ) ? $item['variation_id'] : '';
				if ( $variation_id ) {
					$sku = get_post_meta( $variation_id, '_sku', true );
					WPLA()->logger->info('processing variation ID ' . $variation_id . ' - SKU: '.$sku);
				}

				$_product = $order->get_product_from_item( $item );
				if ( $_product && $_product->exists() && $_product->managing_stock() ) {

					// reduce quantity for simple product
					if ( ! $variation_id ) {

						// check if this product has a listing
						$listing = $listingsModel->getItemByPostID( $post_id );
						if ( ! $listing ) continue;

						// update stock level
						$listingsModel->setListingQuantity( $post_id, wpla_get_product_meta( $_product, 'stock' ) );
						WPLA()->logger->info('new stock: ' . wpla_get_product_meta( $_product, 'stock' ) . '');

						// check if listing is online
						if ( $listing->status != 'online' ) continue;

						// mark as modified
						$listingsModel->markItemAsModified( $post_id );
						WPLA()->logger->info('marked item as modified: ' . $post_id . '');
						$post_IDs[] = $post_id;

						// $cart_item = new stdClass();
						// $cart_item->post_id      = $post_id;
						// $cart_item->variation_id = $variation_id;
						// $cart_item->sku          = $_product->get_sku();
						// $cart_items[] = $cart_item;

					} elseif ( $variation_id ) {
						// reduce quantity for variation

						// check if this product has a listing
						$listing = $listingsModel->getItemByPostID( $variation_id );
						if ( ! $listing ) continue;

						// update stock level
						$listingsModel->setListingQuantity( $variation_id, wpla_get_product_meta( $_product, 'stock' ) );
						WPLA()->logger->info('new stock: ' . wpla_get_product_meta( $_product, 'stock' ) . '');

						// check if listing is online
						if ( $listing->status != 'online' ) continue;

						// mark as modified - using parent post_id will mark all child variations as modified
						$listingsModel->markItemAsModified( $post_id );
						WPLA()->logger->info('marked item as modified: ' . $post_id . '');
						$post_IDs[] = $variation_id;

					// } elseif ( $variation_id && $sku ) {
						// TODO: reduce quantity for SKU
					}


				} // if product exists

			} // if product_id

		} // foreach cart item


		// add order note
		if ( count($post_IDs) > 0 ) {

			$order->add_order_note( sprintf( __('Scheduled inventory update for %s item(s) on Amazon...', 'wpla'), count($post_IDs) ) .' '. join(', ',$post_IDs) );
			// WPLA()->logger->info('cart_items:' . print_r($cart_items,1) );

		}

	} // handle_reduce_order_stock()
	## END PRO ##

	
	public function action_clean_log_table() {
		global $wpdb;

		// clean log table
		$days_to_keep = get_option( 'wpla_log_days_limit', 30 );
		$rows = $wpdb->query('DELETE FROM '.$wpdb->prefix.'amazon_log WHERE timestamp < DATE_SUB(NOW(), INTERVAL '.$days_to_keep.' DAY )');
		WPLA()->logger->info('Cleaned table amazon_log - affected rows: ' . $rows);

		// clean stock log table
		$days_to_keep = get_option( 'wpla_stock_days_limit', 180 );
		$rows = $wpdb->query('DELETE FROM '.$wpdb->prefix.'amazon_stock_log WHERE timestamp < DATE_SUB(NOW(), INTERVAL '.$days_to_keep.' DAY )');
		WPLA()->logger->info('Cleaned table amazon_stock_log - affected rows: ' . $rows);

	} // action_clean_log_table()

	public function action_clean_tables() {
		global $wpdb;

		// clean feeds table (date_created)
		$days_to_keep = get_option( 'wpla_feeds_days_limit', 90 );
		if ( $days_to_keep ) {
			$rows = $wpdb->query('DELETE FROM '.$wpdb->prefix.'amazon_feeds WHERE date_created < DATE_SUB(NOW(), INTERVAL '.$days_to_keep.' DAY )');
			WPLA()->logger->info('Cleaned table amazon_feeds - affected rows: ' . $rows);
		}

		// clean reports table (SubmittedDate)
		$days_to_keep = get_option( 'wpla_reports_days_limit', 90 );
		if ( $days_to_keep ) {
			$rows = $wpdb->query('DELETE FROM '.$wpdb->prefix.'amazon_reports WHERE SubmittedDate < DATE_SUB(NOW(), INTERVAL '.$days_to_keep.' DAY )');
			WPLA()->logger->info('Cleaned table amazon_reports - affected rows: ' . $rows);
		}

		// clean orders table (date_created)
		$days_to_keep = get_option( 'wpla_orders_days_limit', '' );
		if ( $days_to_keep ) {
			$rows = $wpdb->query('DELETE FROM '.$wpdb->prefix.'amazon_orders WHERE date_created < DATE_SUB(NOW(), INTERVAL '.$days_to_keep.' DAY )');
			WPLA()->logger->info('Cleaned table amazon_orders - affected rows: ' . $rows);
		}

	} // action_clean_tables()


	public function checkLock() {

		// get full path to lockfile
		$uploads        = wp_upload_dir();
		$lockfile       = $uploads['basedir'] . '/' . 'wpla_sync.lock';
		$this->lockfile = $lockfile;

		// skip locking if lockfile is not writeable
		if ( ! is_writable( $lockfile ) && ! is_writable( dirname( $lockfile ) ) ) {
	        WPLA()->logger->error("lockfile not writable: ".$lockfile);
	        return true;
		}

		// create lockfile if it doesn't exist
		if ( ! file_exists( $lockfile ) ) {
			$ts = time();
			file_put_contents( $lockfile, $ts );
	        WPLA()->logger->info("lockfile created at TS $ts: ".$lockfile);
	        return true;
		}

		// lockfile exists - check TS
		$ts = (int) file_get_contents($lockfile); 

		// check if TS is outdated (after 10min.)
		if ( $ts < ( time() - 600 ) ) { 
	        WPLA()->logger->info("stale lockfile found for TS ".$ts.' - '.human_time_diff( $ts ).' ago' );

	        // update lockfile 
			$ts = time();
			file_put_contents( $lockfile, $ts ); 
	        
	        WPLA()->logger->info("lockfile updated for TS $ts: ".$lockfile);
	        return true;
		} else { 
			// process is still alive - can not run twice
	        WPLA()->logger->info("SKIP CRON - sync already running with TS ".$ts.' - '.human_time_diff( $ts ).' ago' );
			return false; 
		} 

		return true;
	} // checkLock()

	public function removeLock() {
		if ( file_exists( $this->lockfile ) ) {
			unlink( $this->lockfile );
	        WPLA()->logger->info("lockfile was removed: ".$this->lockfile);
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
		$schedules['three_hours'] = array(
			'interval' => 60 * 60 * 3,
			'display' => 'Once every three hours'
		);
		$schedules['six_hours'] = array(
			'interval' => 60 * 60 * 6,
			'display' => 'Once every six hours'
		);
		$schedules['twelve_hours'] = array(
			'interval' => 60 * 60 * 12,
			'display' => 'Once every twelve hours'
		);
		return $schedules;
	}
			
	public function showMessage($message, $errormsg = false, $echo = false) {		

		// don't output message when doing cron
		if ( defined('DOING_CRON') && DOING_CRON ) return;

		if ( defined('WPLISTER_RESELLER_VERSION') ) $message = apply_filters( 'wpla_tooltip_text', $message );

		$class = ($errormsg) ? 'error' : 'updated fade';
		$class = ($errormsg == 2) ? 'updated update-nag' : $class; 	// warning
		$message = '<div id="message" class="'.$class.'" style="display:block !important"><p>'.$message.'</p></div>'."\n";
		if ($echo) {
			echo $message;
		} else {
			$this->message .= $message;
		}
	}
	
}
// $WPLA_CronActions = new WPLA_CronActions();
