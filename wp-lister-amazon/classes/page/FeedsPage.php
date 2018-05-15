<?php
/**
 * WPLA_FeedsPage class
 * 
 */

class WPLA_FeedsPage extends WPLA_Page {

	const slug = 'feeds';

	public function onWpInit() {

		// Add custom screen options
		$load_action = "load-".$this->main_admin_menu_slug."_page_wpla-".self::slug;
		add_action( $load_action, array( &$this, 'addScreenOptions' ) );

		add_action('wp_ajax_wpla_feed_details', array( &$this, 'ajax_view_feed_details' ) );
		add_action('wp_ajax_nopriv_wpla_feed_details', array( &$this, 'ajax_view_feed_details' ) );

		$this->handleSubmitOnInit();
	}

	public function onWpAdminMenu() {
		parent::onWpAdminMenu();

		add_submenu_page( self::ParentMenuId, $this->getSubmenuPageTitle( 'Feeds' ), __('Feeds','wpla'), 
						  self::ParentPermissions, $this->getSubmenuId( 'feeds' ), array( &$this, 'displayFeedsPage' ) );
	}

	function addScreenOptions() {
		
		// render table options
		$option = 'per_page';
		$args = array(
			'label'   => 'Feeds',
			'default' => 20,
			'option'  => 'feeds_per_page'
	        );
		add_screen_option( $option, $args );
		$this->feedsTable = new WPLA_FeedsTable();
	
	    // add_thickbox();
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

	}
	

	public function displayFeedsPage() {
		$this->check_wplister_setup();
	
		// handle actions and show notes
		$this->handleActions();
		$this->showNotifications();

		// upate pending feed
		WPLA_AmazonFeed::updatePendingFeeds();

	    // create table and fetch items to show
	    // $this->feedsTable = new WPLA_FeedsTable();
	    $this->feedsTable->prepare_items();

	    $feeds_in_progress = self::getOption( 'feeds_in_progress', 0 );
	    if ( $feeds_in_progress > 0 ) {
        	$next_schedule = $this->print_schedule_info( 'wpla_update_schedule' );
	    	$msg = '<p>';
	    	$msg .= sprintf( __('%s feed submission(s) are currently in progress.','wpla'), $feeds_in_progress );
	    	// $msg .= ' Please click Update Feeds until all feeds have been processed.';
	    	$msg .= ' ';
	    	$msg .= sprintf( __('Next check for updated feeds will be executed %s','wpla'), $next_schedule );
	    	$msg .= '&nbsp;&nbsp;&nbsp;<a href="admin.php?page=wpla-feeds&action=wpla_update_feeds&_wpnonce='. wp_create_nonce( 'wpla_update_feeds' ) .'" class="button button-small">'.__('Check now','wpla').'</a></p>';
			$this->showMessage( $msg );
	    }

		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			'feedsTable'				=> $this->feedsTable,
			'feeds_in_progress'			=> $feeds_in_progress,
		
			'form_action'				=> 'admin.php?page='.self::ParentMenuId.'-feeds'
		);
		$this->display( 'feeds_page', $aData );

	}


	public function handleSubmitOnInit() {
		if ( ! current_user_can('manage_amazon_listings') ) return;

		// show feed details
		if ( $this->requestAction() == 'view_amazon_feed_details' ) {
		    check_admin_referer( 'wpla_view_feed_details' );
			$this->showFeedDetails( $_REQUEST['amazon_feed'] );
			exit();
		}
		// show raw feed data
		if ( $this->requestAction() == 'view_amazon_feed_details_raw' ) {
		    check_admin_referer( 'wpla_view_feed_details_raw' );
			$this->showRawFeedData( $_REQUEST['amazon_feed'] );
			exit();
		}
		// show feed processing results
		if ( $this->requestAction() == 'view_amazon_feed_results' ) {
		    check_admin_referer( 'wpla_view_feed_results' );
			$this->showFeedResults( $_REQUEST['amazon_feed'] );
			exit();
		}
		// download feed as text/csv file
		if ( $this->requestAction() == 'wpla_download_feed_content' ) {
		    check_admin_referer( 'wpla_download_feed_content' );
			$this->downloadFeedContent( $_REQUEST['amazon_feed'] );
			exit();
		}
		// download feed processing results as text/csv file
		if ( $this->requestAction() == 'wpla_download_feed_results' ) {
		    check_admin_referer( 'wpla_download_feed_results' );
			$this->downloadFeedContent( $_REQUEST['amazon_feed'], true );
			exit();
		}

	}

	public function handleActions() {
		if ( ! current_user_can('manage_amazon_listings') ) return;
	
		// trigger feeds update
		if ( $this->requestAction() == 'wpla_update_feeds' ) {
		    check_admin_referer( 'wpla_update_feeds' );
			do_action( 'wpla_update_feeds' );
		}

		
		// submit feed
		if ( $this->requestAction() == 'submit_feed_to_amazon' ) {
		    check_admin_referer( 'wpla_submit_feed' );

			$feed = new WPLA_AmazonFeed( $_REQUEST['amazon_feed'] );
			if ( $feed->status != 'pending' ) {
				$this->showMessage( __('This feed has already been submitted to Amazon.','wpla'), 1 );
				return;
			}
			$result = $feed->submit();

			if ( $result->success ) {
				$this->showMessage( __('Feed has been submitted to Amazon.','wpla') );
			} else {
				$this->showMessage( __('There was a problem submitting your feed to Amazon.','wpla') .'<br><pre>'. $result->ErrorMessage .'</pre>', 1 );
				// echo "<pre>";print_r($result);echo"</pre>";#die();
			}
		}

		// check feed - doesn't work via API
		// if ( $this->requestAction() == 'check_feed_on_amazon' ) {

		// 	$feed = new WPLA_AmazonFeed( $_REQUEST['amazon_feed'] );
		// 	$feed->createCheckFeed();

		// 	$this->showMessage( __('A check feed has been created and was submitted to Amazon for verification.','wpla') );
		// }

		// submit all pending feeds
		if ( $this->requestAction() == 'submit_pending_feeds_to_amazon' ) {
		    check_admin_referer( 'wpla_submit_pending_feeds' );

			do_action( 'wpla_submit_pending_feeds' );

			$this->showMessage( __('Pending feed(s) have been submitted to Amazon.','wpla') );
		}

		// handle update feed action
		if ( $this->requestAction() == 'update_amazon_feed' ) {
            check_admin_referer( 'bulk-feeds' );

			$this->updateFeedStatus( $_REQUEST['amazon_feed'] );
		}

		// handle process feed action
		if ( $this->requestAction() == 'process_amazon_feed_results' ) {
		    check_admin_referer( 'wpla_process_feed_results' );
			$this->processFeedResult( $_REQUEST['amazon_feed'] );
		}

		// handle delete action
		if ( $this->requestAction() == 'cancel_amazon_feed' ) {
            check_admin_referer( 'bulk-feeds' );
			$this->cancelFeeds( $_REQUEST['amazon_feed'] );
			$this->showMessage( __('Selected feeds were cancelled.','wpla') );
		}

		// handle delete action
		if ( $this->requestAction() == 'delete_amazon_feed' ) {
            check_admin_referer( 'bulk-feeds' );

			$this->deleteFeeds( $_REQUEST['amazon_feed'] );
			$this->showMessage( __('Selected feeds were removed.','wpla') );
		}

	}


	public function showNotifications() {

        // get listing status summary
        $summary = WPLA_ListingsModel::getStatusSummary();
        
        // check for prepared items and display info
        if ( isset($summary->prepared) ) {
        	// $next_schedule = $this->print_schedule_info( 'wpla_update_schedule' );
			// $msg  = '<p>';
			// $msg .= sprintf( __('%d %s product(s) will be submitted to Amazon %s.','wpla'), $summary->prepared, 'prepared', $next_schedule );
			// $msg .= '&nbsp;&nbsp;';
			// $msg .= '<a href="admin.php?page=wpla&listing_status=prepared" id="" class="button button-small wpl_job_button">' . __('Show products','wpla') . '</a>';
			// $msg .= '&nbsp;&nbsp;';
			// $msg .= '<a href="admin.php?page=wpla-feeds&action=submit_pending_feeds_to_amazon" id="" class="button button-small wpl_job_button">' . __('Submit pending feeds','wpla') . '</a>';
			// $msg .= '</p>';
			// $this->showMessage( $msg );				

			// check prepared products for problems
			$problems = WPLA_FeedValidator::checkPreparedProducts();
			if ( $problems ) $this->showMessage( $problems, 1 );		
        }

        // check for changed, matched and prepared items - and show message
        $is_feed_page = isset($_GET['page']) && ($_GET['page'] == 'wpla-feeds');
        if ( isset($summary->changed) ||  isset($summary->prepared) ||  isset($summary->matched) ) {
        	$next_schedule = $this->print_schedule_info( 'wpla_update_schedule' );

        	// build nice combined message
        	$summary_msg = '';
        	$summary_array = array();
        	foreach ( array('changed','prepared','matched') as $status) {
        		if ( ! isset($summary->$status) ) continue;
        		$link_url   = 'admin.php?page=wpla&listing_status='.$status;
        		$link_title = $summary->$status . ' ' . $status;
        		$summary_array[] = '<a href="'.$link_url.'">'.$link_title.'</a>';
        	}
        	$summary_msg = join(' and ', $summary_array);

			$msg  = '<p>';
			$msg .= sprintf( __('%s product(s) will be submitted to Amazon %s.','wpla'), $summary_msg, $next_schedule );
			$msg .= '&nbsp;&nbsp;';

			if ( $is_feed_page ) {
				$msg .= '<a href="admin.php?page=wpla-feeds&action=submit_pending_feeds_to_amazon&_wpnonce='. wp_create_nonce( 'wpla_submit_pending_feeds' ) .'" id="" class="button button-small wpl_job_button">' . __('Submit pending feeds','wpla') . '</a>';
			} else {
				$msg .= '<a href="admin.php?page=wpla-feeds" id="" class="button button-small wpl_job_button">' . __('Visit feeds','wpla') . '</a>';				
			}

			$msg .= '</p>';
			$this->showMessage( $msg );		
        }

	} // showNotifications()


	// update selected feeds 
	public function updateFeedStatus( $feed_ids ) {
        WPLA()->logger->info("updateFeedStatus() - ".join(', ',$feed_ids));
        // echo "<pre>";print_r($feed_ids);echo"</pre>";die();

        if ( empty($feed_ids) ) return;

		$accounts = WPLA_AmazonAccount::getAll();

		foreach ($feed_ids as $feed_id ) {

			$feed    = new WPLA_AmazonFeed( $feed_id );
			$account = new WPLA_AmazonAccount( $feed->account_id );
			$api     = new WPLA_AmazonAPI( $feed->account_id );

			// get feed submissions
			$feeds = $api->getFeedSubmissionList( $feed->FeedSubmissionId );

			if ( is_array( $feeds ) )  {

				// run the import
				WPLA_AmazonFeed::processFeedsSubmissionList( $feeds, $account );

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

	} // action_update_feeds()
	
	public function processFeedResult( $id ) {
		
		$feed = new WPLA_AmazonFeed( $id );
		$feed->processSubmissionResult();

		$msg  = __('Feed result was processed.','wpla') . '<br><br>';
		$msg .= 'Errors: '.sizeof($feed->errors).'<br>';
		$msg .= 'Warnings: '.sizeof($feed->warnings).'<br>';
		$this->showMessage( $msg );

	}

	public function deleteFeeds( $feeds ) {
		
		foreach ($feeds as $id) {
			$feed = new WPLA_AmazonFeed( $id );
			$feed->delete();
		}

	}
	
	public function cancelFeeds( $feeds ) {
		
		foreach ($feeds as $id) {
			$feed = new WPLA_AmazonFeed( $id );
			$feed->cancel();
		}

	}
	
	public function ajax_view_feed_details() {

		// show feed details - if not logged in, check signature
		if ( $this->requestAction() == 'wpla_feed_details' ) {

			$feed = WPLA_AmazonFeed::getFeed( $_REQUEST['id'] );
			if ( ! $feed ) die('unknown feed');

			$signature = md5( $feed->id . get_option('wpla_instance') );
			if ( $_REQUEST['sig'] != $signature ) die('invalid signature');

			$this->showFeedDetails( $feed->id );
			exit();
		}

	}

	public function showFeedDetails( $id ) {
	
		// get amazon_feed record
		$feed = new WPLA_AmazonFeed( $id );
		
		// prepare feed content
		// $rows = WPLA_ReportProcessor::csv_to_array( $feed_data );
		$rows = $feed->getDataArray();

		// Strip out the CYD message from the feed results
        if ( stristr( $feed->results, 'Looking for an easier way to take action on your listings' ) !== false ) {
            $start = strpos( $feed->results, 'Looking for an easier way to take action on your listings' );
            $end = strpos( $feed->results, "\n\n", $start );
            $feed->results = substr_replace( $feed->results, '', $start, $end - $start + 2 );
        }

		// prepare feed result
		$result_header  = implode("\n", array_slice(explode("\n", $feed->results), 0, 4));
		$result_content = implode("\n", array_slice(explode("\n", $feed->results), 4));
		$result_content = str_replace('original-record-number', '#', $result_content);
		$result_content = str_replace('error-code', 'code', $result_content);
		$result_content = str_replace('error-type', 'type', $result_content);
		$result_rows    = WPLA_ReportProcessor::csv_to_array( $result_content );

		// send log entry to support
		if ( isset($_REQUEST['send_to_support']) && $_REQUEST['send_to_support'] == 'yes' ) {
			$this->sendRecordToSupport( $id, $rows );
		}

		unset( $feed->data );
		unset( $feed->results );

		$aData = array(
			'feed'				=> $feed,
			'rows'				=> $rows,
			'result_rows'		=> $result_rows,
			'result_header'		=> $result_header,
		);
		$this->display( 'feed_details', $aData );
		
	}

	public function showRawFeedData( $id ) {
	
		$feed = new WPLA_AmazonFeed( $id );
		header("Content-Type:text/plain");
		echo $feed->data;
		exit();
		
	}

	public function downloadFeedContent( $id, $use_results = false ) {
	
		$feed = new WPLA_AmazonFeed( $id );
		if ( ! $feed ) die('Invalid feed');
		$feed_id  = $feed->FeedSubmissionId ? $feed->FeedSubmissionId : $id;
		$filename = $use_results ? 'amazon-feed-'.$feed_id.'-results.csv' : 'amazon-feed-'.$feed_id.'.csv';

		// send as csv
		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=".$filename);
		if ( @filesize($feed->data) ) header('Content-Length: ' . filesize($feed->data) );

		// Disable caching
		header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
		header("Pragma: no-cache"); // HTTP 1.0
		header("Expires: 0"); // Proxies

		// send content
		echo $use_results ? $feed->results : $feed->data;
		exit();	
	}

	public function showFeedResults( $id ) {
	
		// get amazon_feed record
		$feed = new WPLA_AmazonFeed( $id );

		$result_header  = implode("\n", array_slice(explode("\n", $feed->results), 0, 4));
		$result_content = implode("\n", array_slice(explode("\n", $feed->results), 4));
		$result_content = str_replace('original-record-number', '#', $result_content);
		$result_content = str_replace('error-code', 'code', $result_content);
		$result_content = str_replace('error-type', 'type', $result_content);
		
		$rows = WPLA_ReportProcessor::csv_to_array( $result_content );
		unset( $feed->data );
		unset( $feed->results );


		$aData = array(
			'feed'				=> $feed,
			'rows'				=> $rows,
			'result_header'		=> $result_header,
		);
		$this->display( 'feed_results', $aData );
		
	}



	public function sendRecordToSupport( $id, $row = null ) {

		// check nonce
		if ( ! check_admin_referer( 'wpla_send_to_support' ) ) return;

		// get html content
		// $content = $this->display( 'log_details', array( 'row' => $row, 'version' => WPLA_VERSION ), false );

		// build email
		$to          = 'support@wplab.com';
		$subject     = 'Amazon feed #'.$id.' - '. str_replace( 'http://','', get_bloginfo('wpurl') );

		$user_name   = $_REQUEST['user_name'] ? $_REQUEST['user_name'] : 'unknown user';
		$user_email  = sanitize_email( $_REQUEST['user_email'] );
		$user_msg    = stripslashes( $_REQUEST['user_msg'] );
		$headers     = 'From: '.$user_name.' <'.$user_email.'>' . "\r\n";
		$attachments = array();

		$message  = '';
		$message .= 'Name:    '.$user_name.'<br>';
		$message .= 'Email:   '.$user_email.'<br>';
		$message .= 'Website: '.get_bloginfo('wpurl').'<br>';
		$message .= '<br>'.nl2br($user_msg).'<br>';
		$message .= '<hr>';
		$message .= 'WP-Lister for Amazon: '.WPLA_VERSION.'<br>';
		$message .= 'WooCommerce: '.WC_VERSION.'<br>';
		$message .= 'WordPress: '.get_bloginfo ( 'version' ).'<br>';
		$message .= 'PHP: '.phpversion().'<br>';
		// $message .= $content;
		// $message .= '<hr>';

		// send email as html
		add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));
		wp_mail($to, $subject, $message, $headers, $attachments);
		
		echo '<br><div style="text-align:center;font-family:sans-serif;">';
		echo 'Your request was sent to '.$to.'.';
		echo '<br><br>';
		echo 'Thank you for using WP-Lister for Amazon.</div>';
		exit;
	}


} // class WPLA_FeedsPage
