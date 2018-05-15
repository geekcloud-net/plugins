<?php
/**
 * WPLA_StockLogPage class
 * 
 */

class WPLA_StockLogPage extends WPLA_Page {

	const slug = 'tools';

	public function onWpInit() {

		// Add custom screen options
		$load_action = "load-".$this->main_admin_menu_slug."_page_wpla-".self::slug;
		add_action( $load_action, array( &$this, 'addScreenOptions' ) );

	}

	function addScreenOptions() {
		if ( isset($_GET['tab']) && $_GET['tab'] != 'stock_log' ) return;
		if ( ! isset($_GET['tab']) ) return;
		
		if ( ( isset($_GET['action']) ) && ( $_GET['action'] == 'edit' ) ) {
			// on edit page render developers options
			add_screen_options_panel('wpla_developer_options', '', array( &$this, 'renderDeveloperOptions'), 'toplevel_page_wpla' );

		} else {

			// render table options
			$option = 'per_page';
			$args = array(
		    	'label' => 'Log entries',
		        'default' => 20,
		        'option' => 'logs_per_page'
		        );
			add_screen_option( $option, $args );
			$this->stocklogTable = new WPLA_StockLogTable();

		}

	}
	
	public function displayStockLogPage() {

	    // create table and fetch items to show
	    $this->stocklogTable = new WPLA_StockLogTable();
	    $this->stocklogTable->prepare_items();

		$active_tab  = 'stock_log';
	    $form_action = 'admin.php?page='.self::ParentMenuId.'-tools'.'&tab='.$active_tab;

		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			'listingsTable'				=> $this->stocklogTable,
			'default_account'			=> get_option( 'wpla_default_account_id' ),
			'tableSize'					=> $this->getTableSize(),

			'tools_url'				    => 'admin.php?page='.self::ParentMenuId.'-tools',
			'form_action'				=> $form_action
		);
		$this->display( 'tools_stocklog', $aData );
	}

	public function getTableSize() {
		global $wpdb;
		$dbname = $wpdb->dbname;
		$table  = $wpdb->prefix.'amazon_stock_log';

		// check if MySQL server has gone away and reconnect if required - WP 3.9+
		if ( method_exists( $wpdb, 'check_connection') ) $wpdb->check_connection();

		$sql = "
			SELECT round(((data_length + index_length) / 1024 / 1024), 1) AS 'size' 
			FROM information_schema.TABLES 
			WHERE table_schema = '$dbname'
			  AND table_name = '$table' ";
		// echo "<pre>";print_r($sql);echo"</pre>";#die();

		$size = $wpdb->get_var($sql);
		if ( $wpdb->last_error ) echo 'Error in getTableSize(): '.$wpdb->last_error;

		return $size;
	}


} // WPLA_StockLogPage
