<?php
/**
 * WPLA_ReportsPage class
 * 
 */

class WPLA_ReportsPage extends WPLA_Page {

	const slug = 'reports';

	public function onWpInit() {

		// Add custom screen options
		$load_action = "load-".$this->main_admin_menu_slug."_page_wpla-".self::slug;
		add_action( $load_action, array( &$this, 'addScreenOptions' ) );

		add_action('wp_ajax_wpla_report_details', array( &$this, 'ajax_view_report_details' ) );
		add_action('wp_ajax_nopriv_wpla_report_details', array( &$this, 'ajax_view_report_details' ) );

		$this->handleSubmitOnInit();
	}

	public function onWpAdminMenu() {
		parent::onWpAdminMenu();

		add_submenu_page( self::ParentMenuId, $this->getSubmenuPageTitle( 'Reports' ), __('Reports','wpla'), 
						  self::ParentPermissions, $this->getSubmenuId( 'reports' ), array( &$this, 'displayReportsPage' ) );
	}

	function addScreenOptions() {
		
		// render table options
		$option = 'per_page';
		$args = array(
	    	'label' => 'Reports',
	        'default' => 20,
	        'option' => 'reports_per_page'
	        );
		add_screen_option( $option, $args );
		$this->reportsTable = new WPLA_ReportsTable();
	
	    // add_thickbox();
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

	}
	

	public function displayReportsPage() {
		$this->check_wplister_setup();
	
		// handle actions and show notes
		$this->handleActions();

	    // create table and fetch items to show
	    // $this->reportsTable = new WPLA_ReportsTable();
	    $this->reportsTable->prepare_items();

	    $reports_in_progress = get_option( 'wpla_reports_in_progress', 0 );
	    if ( $reports_in_progress > 0 ) {
        	$next_schedule = $this->print_schedule_info( 'wpla_update_schedule' );
	    	$msg = '<p>';
	    	$msg .= sprintf( __('%s report request(s) are currently in progress.','wpla'), $reports_in_progress );
	    	// $msg .= ' Please click Update Reports until all reports are done.';
	    	$msg .= ' ';
	    	$msg .= sprintf( __('Next check for processed reports will be executed %s','wpla'), $next_schedule );
	    	$msg .= '&nbsp;&nbsp;&nbsp;<a href="admin.php?page=wpla-reports&action=wpla_update_reports&_wpnonce='. wp_create_nonce( 'wpla_update_reports' ) .'" class="button button-small">'.__('Check now','wpla').'</a></p>';
			$this->showMessage( $msg );
	    }

		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			'reportsTable'				=> $this->reportsTable,
			'reports_in_progress'		=> $reports_in_progress,
		
			'form_action'				=> 'admin.php?page='.self::ParentMenuId.'-reports'
		);
		$this->display( 'reports_page', $aData );

	}


	public function handleSubmitOnInit() {
		if ( ! current_user_can('manage_amazon_listings') ) return;

		// handle preview action - logged in users (deprecated - only used on import page)
		if ( $this->requestAction() == 'view_amazon_report_details' ) {
			$this->showReportDetails( $_REQUEST['amazon_report'] );
			exit();
		}

		// download report as text/csv file
		if ( $this->requestAction() == 'wpla_download_report' ) {
		    check_admin_referer( 'wpla_download_report' );
			$this->downloadReportContent( $_REQUEST['amazon_report'] );
			exit();
		}

	}

	public function ajax_view_report_details() {

		// show report details - if not logged in, check signature
		if ( $this->requestAction() == 'wpla_report_details' ) {

			$report = WPLA_AmazonReport::getReportByRequestId( $_REQUEST['rrid'] );
			if ( ! $report ) die('unknown report');

			$signature = md5( $report->ReportRequestId . get_option('wpla_instance') );
			if ( $_REQUEST['sig'] != $signature ) die('invalid signature');

			$this->showReportDetails( $report->id );
			exit();
		}

	}

	public function handleActions() {
		if ( ! current_user_can('manage_amazon_listings') ) return;
	
		// trigger reports update
		if ( $this->requestAction() == 'wpla_update_reports' ) {
		    check_admin_referer( 'wpla_update_reports' );
			do_action( 'wpla_update_reports' );
		}

		// trigger report request
		if ( $this->requestAction() == 'wpla_request_report' ) {
		    check_admin_referer( 'wpla_request_report' );

			$accounts = WPLA_AmazonAccount::getAll();

			foreach ($accounts as $account ) {

				$api = new WPLA_AmazonAPI( $account->id );

				// request report - returns request list as array on success
				$reports = $api->requestReport( $_REQUEST['wpla_report_type']);

				if ( is_array( $reports ) )  {

					// process the result
					// $this->processReportsRequestList( $reports, $account );
					WPLA_AmazonReport::processReportsRequestList( $reports, $account );

					$this->showMessage( sprintf( __('Report requested for account %s.','wpla'), $account->title ) );

				} elseif ( $reports->Error->Message ) {
					$this->showMessage( sprintf( __('There was a problem requesting the report for account %s.','wpla'), $account->title ) .'<br>Error: '. $reports->Error->Message, 1 );
				} else {
					$this->showMessage( sprintf( __('There was a problem requesting the report for account %s.','wpla'), $account->title ), 1 );
				}

			}

		}

		// handle load report action
		if ( $this->requestAction() == 'load_report_from_amazon' ) {
		    check_admin_referer( 'wpla_load_report' );

			$report = new WPLA_AmazonReport( $_REQUEST['amazon_report'] );
			$report->loadFromAmazon();

			// $api = new WPLA_AmazonAPI( $report->account_id );
			// $api->getReport( $report->GeneratedReportId );

			$this->showMessage( __('Report was downloaded from Amazon.','wpla') );

		}

		// handle process report action
		if ( $this->requestAction() == 'process_amazon_report' ) {
		    check_admin_referer( 'wpla_process_report' );

			$this->processReportData( $_REQUEST['amazon_report'] );
			$this->showMessage( __('Report was processed.','wpla') );
		}
		// handle process report action
		if ( $this->requestAction() == 'process_fba_shipment_report' ) {
		    check_admin_referer( 'wpla_process_fba_report' );
			$this->processFbaShipmentReportData( $_REQUEST['amazon_report'] );
		}

		// handle delete_amazon_report action
		if ( $this->requestAction() == 'delete_amazon_report' ) {
		    check_admin_referer( 'bulk-reports' );
			$this->deleteReports( $_REQUEST['amazon_report'] );
			$this->showMessage( __('Selected items were removed.','wpla') );
		}

		// handle update_amazon_report action
		if ( $this->requestAction() == 'update_amazon_report' ) {
		    check_admin_referer( 'bulk-reports' );
			$this->updateReports( $_REQUEST['amazon_report'] );
			$this->showMessage( __('Selected items were updated.','wpla') );
		}

	}
	
	public function processFbaShipmentReportData( $id ) {
		
		$report = new WPLA_AmazonReport( $id );
		$rows   = $report->get_data_rows();

		$result = WPLA_ReportProcessor::processAmazonShipmentsReportPage( $report, $rows, null, null );
		$this->showMessage( sprintf( __('Report was processed - %s orders were updated.','wpla'), $result->count ) );

	}

	public function processReportData( $id ) {
		
		$report = new WPLA_AmazonReport( $id );

		// $data = $report->data;
        $rows = $report->get_data_rows();

		$lm = new WPLA_ListingsModel();
		foreach ($rows as $row) {
			$lm->updateItemFromReportCSV( $row, $report->account_id );				
		}

		$msg  = 'Imported: '.$lm->imported_count.'<br>';
		$msg .= 'Updated: '.$lm->updated_count.'<br>';
		$this->showMessage( $msg );

	}

	public function deleteReports( $reports ) {
		
		foreach ($reports as $id) {
			$report = new WPLA_AmazonReport( $id );
			$report->delete();
		}

	}
	
	public function updateReports( $reports ) {
		
		foreach ($reports as $id) {
			$report = new WPLA_AmazonReport( $id );
			$report->loadFromAmazon();
		}

	}
	
	public function downloadReportContent( $id ) {
	
		$report = new WPLA_AmazonReport( $id );
		if ( ! $report ) die('Invalid report');
		$report_id = $report->ReportRequestId ? $report->ReportRequestId : $id;
		$filename  = 'amazon-report-'.$report_id.'.csv';

		// send as csv
		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=".$filename);
		if ( @filesize($feed->data) ) header('Content-Length: ' . filesize($report->data) );

		// Disable caching
		header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
		header("Pragma: no-cache"); // HTTP 1.0
		header("Expires: 0"); // Proxies

		// send content
		echo $report->data;
		exit();	
	}

	public function showReportDetails( $id ) {
	
		// get amazon_report record
		$report = new WPLA_AmazonReport( $id );
		
		// get WooCommerce report
		// $wc_report_notes = $amazon_report['post_id'] ? $this->get_report_notes( $amazon_report['post_id'] ) : false;

		// check for query paramater
		$query = isset( $_REQUEST['query'] ) ? sanitize_text_field( $_REQUEST['query'] ) : '';

        $rows = $report->get_data_rows( $query );
		unset( $report->data );
		unset( $report->results );

		// limit to 1000 rows per page
		$limit = 1000;
		$offset = 0;
		$total_rows = sizeof( $rows );
		if ( $total_rows > $limit ) {
			$rows = array_splice( $rows, $offset, $limit );
		}

		$aData = array(
			'report'				=> $report,
			'rows'					=> $rows,
			'total_rows'			=> $total_rows,
			'query'					=> $query,
		);
		$this->display( 'report_details', $aData );
		
	}


}
