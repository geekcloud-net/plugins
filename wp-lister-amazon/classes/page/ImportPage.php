<?php
/**
 * ImportPage class
 * 
 */

class WPLA_ImportPage extends WPLA_Page {

	const slug = 'import';

	public function onWpInit() {
		// parent::onWpInit();

		// custom (raw) screen options for import page
		// add_screen_options_panel('wpla_setting_options', '', array( &$this, 'renderSettingsOptions'), 'wp-lister_page_wpla-import' );

		// load styles and scripts for this page only
		add_action( 'admin_print_styles', array( &$this, 'onWpPrintStyles' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'onWpEnqueueScripts' ) );		
		// add_thickbox();
	}

	public function onWpAdminMenu() {
		parent::onWpAdminMenu();

		add_submenu_page( self::ParentMenuId, $this->getSubmenuPageTitle( 'Import' ), __('Import','wpla'), 
						  self::ParentPermissions, $this->getSubmenuId( 'import' ), array( &$this, 'onDisplayImportPage' ) );
	}

	public function onDisplayImportPage() {
		// $this->check_wplister_setup();

		$mode   = isset($_REQUEST['mode'])   ? $_REQUEST['mode']   : false;
		$step   = isset($_REQUEST['step'])   ? $_REQUEST['step']   : false;
		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;

		// check action - and nonce - for POST requests
		if ( isset($_POST['action']) ) {
			if ( check_admin_referer( 'wpla_import_page' ) ) {

				// wpla_bulk_import_asins
				if ( 'wpla_bulk_import_asins' == $action ) {
					if ( $this->importASINs() ) {
						return $this->displayForeignImportPage();
					}
				}
	
				// wpla_request_new_inventory_report
				if ( 'wpla_request_new_inventory_report' == $action ) {
					$this->requestNewInventoryReport();
				}
	
				// wpla_update_import_options
				if ( 'wpla_update_import_options' == $action ) {
					$this->saveImportOptions();
				}
	
			} else {
				die ('not allowed');
			}
		}

		// trigger reports update
		if ( $this->requestAction() == 'wpla_update_reports' ) {
		    check_admin_referer( 'wpla_update_reports' );
			do_action( 'wpla_update_reports' );
		}

		// import page is reloaded after ajax tasks have been processed
		if ( $mode == 'asin' && $step >= 3 ) {
			return $this->displayForeignImportPage( $import_is_done = true );
		}

		// step 4 - import is done
		if ( $mode == 'inventory' && $step == 4 ) {
			return $this->displayFinishedImportPage();
		}

		// process selected report and display preview
		// if ( $mode == 'inventory' && $step == 2 ) {
		if ( $mode == 'inventory' && $step ) {
			return $this->displayPreviewImportPage( $step );
		}

		$this->displayMainImportPage();

	} // onDisplayImportPage()


	public function displayMainImportPage() {

		$recent_reports      = WPLA_AmazonReport::getRecentInventoryReports();
		$reports_in_progress = $this->checkReportsInProgress();
		$default_account	 = WPLA_AmazonAccount::getAccount( get_option( 'wpla_default_account_id' ) );

		$aData = array(
			'plugin_url'                    => self::$PLUGIN_URL,
			'message'                       => $this->message,		
			'recent_reports'                => $recent_reports,		
			'reports_in_progress'           => $reports_in_progress,		
			'reports_update_woo_stock'      => get_option( 'wpla_reports_update_woo_stock', 1 ),
			'reports_update_woo_price'      => get_option( 'wpla_reports_update_woo_price', 1 ),
			'reports_update_woo_condition'  => get_option( 'wpla_reports_update_woo_condition', 1 ),
			'import_creates_all_variations' => get_option( 'wpla_import_creates_all_variations', 0 ),
			'import_variations_as_simple'   => get_option( 'wpla_import_variations_as_simple', 0 ),
			'default_account_title'         => $default_account ? $default_account->title : 'invalid!',
			'form_action'                   => 'admin.php?page='.self::ParentMenuId.'-import'
		);

		$this->display( 'import/import_page', $aData );

	} // displayMainImportPage()


	// process selected report and display preview
	public function displayPreviewImportPage( $step ) {

		// analyse report content
		$report         = new WPLA_AmazonReport( $_REQUEST['report_id'] );
		$account        = new WPLA_AmazonAccount( $report->account_id );
		$report_summary = WPLA_ImportHelper::analyzeReportForPreview( $report );
		$status_summary = WPLA_ListingsModel::getStatusSummary();

		// skip step 3 if no products are to be imported
		// if ( $step == 3 && count($report_summary->products_to_import) == 0 ) {
		if ( $step == 3 && intval(@$status_summary->imported) == 0 ) {
			return $this->displayFinishedImportPage();
		}

		$aData = array(
			'plugin_url'                   => self::$PLUGIN_URL,
			'message'                      => $this->message,		
			'step'                         => $step,
			'report_summary'               => $report_summary,
			'status_summary'               => $status_summary,
			'account'                      => $account,
			'report_id'                    => $report->id,
			'data_rows'                    => $report->get_data_rows(), // TODO: use cache
			'reports_update_woo_stock'     => get_option( 'wpla_reports_update_woo_stock', 1 ),
			'reports_update_woo_price'     => get_option( 'wpla_reports_update_woo_price', 1 ),
			'reports_update_woo_condition' => get_option( 'wpla_reports_update_woo_condition', 1 ),
			'form_action'                  => 'admin.php?page='.self::ParentMenuId.'-import'
		);

		$this->display( 'import/preview_import_page', $aData );

	} // displayPreviewImportPage()


	public function displayForeignImportPage( $import_is_done = false ) {

		$lm = new WPLA_ListingsModel();
		$listings = $lm->getAllImported( 'foreign_import' );

		// return to step 1 if no products are to be imported
		if ( ! $import_is_done && count($listings) == 0 ) {
			return $this->displayMainImportPage();
		}
		// skip step 3 if no products are to be imported
		if ( $import_is_done && count($listings) == 0 ) {
			return $this->displayFinishedImportPage();
		}

		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,		
			'listings'					=> $listings,		
			'import_is_done'			=> $import_is_done,		
			'form_action'				=> 'admin.php?page='.self::ParentMenuId.'-import'
		);

		$this->display( 'import/foreign_import_page', $aData );

	} // displayForeignImportPage()


	// import is done
	public function displayFinishedImportPage() {

		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,		
			'report_id'					=> isset( $_REQUEST['report_id'] ) ? $_REQUEST['report_id'] : false,
			'mode'						=> isset( $_REQUEST['mode'] ) ? $_REQUEST['mode'] : false,
		);

		$this->display( 'import/finished_import_page', $aData );

	} // displayFinishedImportPage()



	public function saveImportOptions() {

		update_option( 'wpla_reports_update_woo_price', 		$this->getValueFromPost( 'reports_update_woo_price' 	 ) ? 1 : 0 );	
		update_option( 'wpla_reports_update_woo_stock', 		$this->getValueFromPost( 'reports_update_woo_stock' 	 ) ? 1 : 0 );
		update_option( 'wpla_reports_update_woo_condition', 	$this->getValueFromPost( 'reports_update_woo_condition'  ) ? 1 : 0 );
		update_option( 'wpla_import_creates_all_variations', 	$this->getValueFromPost( 'import_creates_all_variations' ) ? 1 : 0 );
		update_option( 'wpla_import_variations_as_simple', 		$this->getValueFromPost( 'import_variations_as_simple' 	 ) ? 1 : 0 );

		$this->showMessage( __('Report processing options were saved.','wpla') );

	} // saveImportOptions()



	public function importASINs() {

		$asin_list = trim( $_REQUEST['wpla_asin_list'] );
		if ( ! $asin_list ) {
			$this->showMessage('You need to enter a least one ASIN to import.',1);			
			return false;
		}

		$lm = new WPLA_ListingsModel();
		$import_account_id = $_REQUEST['wpla_import_account_id'];
		if ( ! $import_account_id ) {
			$import_account_id = get_option( 'wpla_default_account_id', 1 );
		}

		$asin_array = explode("\n", $asin_list);
		foreach ($asin_array as $ASIN) {

			$ASIN = trim( $ASIN );
			if ( ! $ASIN ) continue;

			$row  = array();
			$row['asin']                = $ASIN;
			$row['seller-sku']          = $ASIN;
			$row['item-name']           = $ASIN . ' (import to fetch title from Amazon)';
			$row['open-date']           = gmdate('Y-m-d H:i:s');
			$row['item-description']    = '';
			$row['fulfillment-channel'] = '';
			$row['quantity']            = 0;
			$row['price']               = 0;
			$row['source']              = 'foreign_import';

			$lm->updateItemFromReportCSV( $row, $import_account_id );				
			// $this->showMessage('Product '.$ASIN.' was prepared for import.');			
		}
		if ( $lm->imported_count )
			$this->showMessage( $lm->imported_count . ' new products were prepared for import.');
		if ( $lm->updated_count )
			$this->showMessage( $lm->updated_count . ' ASINs already exist and have been skipped.');
		
		return $lm->imported_count + $lm->updated_count;
	} // importASINs()


	
	public function requestNewInventoryReport( $report_type = '_GET_MERCHANT_LISTINGS_DATA_' ) {

		$accounts = WPLA_AmazonAccount::getAll();

		foreach ($accounts as $account ) {

			$api = new WPLA_AmazonAPI( $account->id );

			// request report - returns request list as array on success
			$reports = $api->requestReport( $report_type );

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

	} // requestNewInventoryReport()


	public function checkReportsInProgress() {

	    $reports_in_progress = get_option( 'wpla_reports_in_progress', 0 );
	    if ( $reports_in_progress > 0 ) {
        	$next_schedule = $this->print_schedule_info( 'wpla_update_schedule' );
	    	$msg = '<p>';
	    	$msg .= sprintf( __('%s report request(s) are currently in progress.','wpla'), $reports_in_progress );
	    	$msg .= ' ';
	    	$msg .= sprintf( __('Next check for processed reports will be executed %s','wpla'), $next_schedule );
	    	$msg .= '&nbsp;&nbsp;&nbsp;<a href="admin.php?page=wpla-import&action=wpla_update_reports&_wpnonce='. wp_create_nonce( 'wpla_update_reports' ) .'" class="button button-small">'.__('Check now','wpla').'</a></p>';
			$this->showMessage( $msg );
	    }

	    return $reports_in_progress;
	} // requestNewInventoryReport()


	
	public function onWpPrintStyles() {

		// testing:
		// jQuery UI theme - for progressbar
		// wp_register_style('jQueryUITheme', plugins_url( 'css/smoothness/jquery-ui-1.8.22.custom.css' , WPLA_PATH.'/wp-lister.php' ) );
		// wp_enqueue_style('jQueryUITheme'); 

	}

	public function onWpEnqueueScripts() {
	}


}
