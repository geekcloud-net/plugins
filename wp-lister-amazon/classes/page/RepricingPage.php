<?php
/**
 * WPLA_RepricingPage class
 * 
 */

class WPLA_RepricingPage extends WPLA_Page {

	const slug = 'tools';

	public function onWpInit() {

		// Add custom screen options
		$load_action = "load-".$this->main_admin_menu_slug."_page_wpla-".self::slug;
		add_action( $load_action, array( &$this, 'addScreenOptions' ) );

		if ( get_option( 'wpla_enable_repricing_page' ) ) {
			$load_action = "load-".$this->main_admin_menu_slug."_page_wpla-".'settings';
			add_action( $load_action.'-repricing', array( &$this, 'addScreenOptions' ) );
		}

	}

	function addScreenOptions() {
		if ( isset($_GET['tab']) && $_GET['tab'] != 'repricing' ) return;
		
		if ( ( isset($_GET['action']) ) && ( $_GET['action'] == 'edit' ) ) {
			// on edit page render developers options
			add_screen_options_panel('wpla_developer_options', '', array( &$this, 'renderDeveloperOptions'), 'toplevel_page_wpla' );

		} else {

			// render table options
			$option = 'per_page';
			$args = array(
		    	'label' => 'Listings',
		        'default' => 20,
		        'option' => 'listings_per_page'
		        );
			add_screen_option( $option, $args );
			$this->repricingTable = new WPLA_RepricingTable();

		}

	    // add_thickbox();
		// wp_enqueue_script( 'thickbox' );
		// wp_enqueue_style( 'thickbox' );

	}
	

	public function handleActions() {
		if ( ! current_user_can('manage_amazon_listings') ) return;
	}
	
	public function resubmitPnqUpdateForSelectedItems() {

		$item_ids = $_REQUEST['listing'];

		foreach ( $item_ids as $listing_id ) {
			$data = array( 'pnq_status' => 1 );
			WPLA_ListingsModel::updateWhere( array( 'id' => $listing_id ), $data );
		}

        $this->showMessage( count($item_ids) . ' product prices have been scheduled for resubmission.');
	}
	
	public function applyLowestPricesToSelectedItems() {

		$item_ids = $_REQUEST['listing'];
		$items = WPLA_ListingsModel::getItems( $item_ids );
		if ( empty($items) ) return;

        $changed_product_ids1 = WPLA_RepricingHelper::adjustLowestPriceForProducts( $items, true );
        $changed_product_ids2 = WPLA_RepricingHelper::resetProductsToMaxPrice( $items, true );

        $changed_product_ids  = array_merge( $changed_product_ids1, $changed_product_ids2 );
        $this->showMessage( count($changed_product_ids) . ' of ' . count($items) . ' product prices have been updated.');
	}
	
	public function applyLowestPricesToAllItems() {

		$changed_product_ids = WPLA_RepricingHelper::repriceProducts();

        $this->showMessage( count($changed_product_ids) . ' product prices have been updated.');
	}
	
	public function applyMinMaxPrices() {

		$item_ids = $_REQUEST['item_ids'] ? explode( ',', $_REQUEST['item_ids'] ) : array();
		WPLA_MinMaxPriceWizard::updateMinMaxPrices( $item_ids );

        $this->showMessage( count($item_ids) . ' minimum and / or maximum prices have been updated.');
	}
	

	public function displayRepricingPage() {

		// handle actions and show notes
		// $this->handleActions();

		if ( $this->requestAction() == 'wpla_apply_lowest_price_to_all_items' ) {
		    check_admin_referer( 'wpla_apply_lowest_price_to_all_items' );
			$this->applyLowestPricesToAllItems();
		}

		if ( $this->requestAction() == 'wpla_resubmit_pnq_update' ) {
		    check_admin_referer( 'bulk-listings' );
			$this->resubmitPnqUpdateForSelectedItems();
		}

		if ( $this->requestAction() == 'wpla_bulk_apply_lowest_prices' ) {
		    check_admin_referer( 'bulk-listings' );
			$this->applyLowestPricesToSelectedItems();
		}

		if ( $this->requestAction() == 'wpla_bulk_apply_minmax_prices' ) {
			$this->applyMinMaxPrices();
		}

		// handle bulk action - get_compet_price
		if ( $this->requestAction() == 'wpla_get_compet_price' ) {
		    check_admin_referer( 'bulk-listings' );
			WPLA()->pages['listings']->get_compet_price();
			WPLA()->pages['listings']->get_lowest_offers();
		}

		if ( $this->requestAction() == 'wpla_resubmit_all_failed_prices' ) {
		    check_admin_referer( 'wpla_resubmit_all_failed_prices' );
			$lm = new WPLA_ListingsModel();
			$items = $lm->getWhere( 'pnq_status', -1 );
			foreach ( $items as $item ) {
				// set pnq status to changed (1)
				$lm->updateWhere( array( 'id' => $item->id ), array( 'pnq_status' => 1 ) );
			}
			$this->showMessage( sprintf( __('%s failed prices were scheduled for resubmission.','wpla'), count($items) ) );
		}


	    // create table and fetch items to show
	    $this->repricingTable = new WPLA_RepricingTable();
	    $this->repricingTable->prepare_items();

		$active_tab  = 'repricing';
	    $form_action = 'admin.php?page='.self::ParentMenuId.'-tools'.'&tab='.$active_tab;
	    if ( @$_REQUEST['page'] == 'wpla-settings-repricing' )
		    $form_action = 'admin.php?page=wpla-settings-repricing';

		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			'listingsTable'				=> $this->repricingTable,
			'default_account'			=> get_option( 'wpla_default_account_id' ),

			'tools_url'				    => 'admin.php?page='.self::ParentMenuId.'-tools',
			'form_action'				=> $form_action
		);
		$this->display( 'tools_repricing', $aData );
	}


} // WPLA_RepricingPage
