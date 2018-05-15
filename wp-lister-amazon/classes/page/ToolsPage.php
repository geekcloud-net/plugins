<?php
/**
 * ToolsPage class
 * 
 */

class WPLA_ToolsPage extends WPLA_Page {

	const slug = 'tools';

	public function onWpInit() {
		// parent::onWpInit();

		// custom (raw) screen options for tools page
		add_screen_options_panel('wpla_setting_options', '', array( &$this, 'renderSettingsOptions'), 'wp-lister_page_wpla-tools' );

		// load styles and scripts for this page only
		// add_action( 'admin_print_styles', array( &$this, 'onWpPrintStyles' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'onWpEnqueueScripts' ) );		
		add_thickbox();
	}

	public function onWpAdminMenu() {
		parent::onWpAdminMenu();

        $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'repricing'; 
        $title_prefix = '';
		if ( $active_tab == 'repricing' ) $title_prefix = 'Repricing - '; 
		if ( $active_tab == 'inventory' ) $title_prefix = 'Inventory - '; 
		if ( $active_tab == 'skugen'    ) $title_prefix = 'SKU - '; 
		if ( $active_tab == 'stock_log' ) $title_prefix = 'Stock - '; 

		add_submenu_page( self::ParentMenuId, $this->getSubmenuPageTitle( $title_prefix . 'Tools' ), __('Tools','wpla'), 
						  self::ParentPermissions, $this->getSubmenuId( 'tools' ), array( &$this, 'onDisplayToolsPage' ) );
	}

	public function onDisplayToolsPage() {
		
		$this->check_wplister_setup();

		// Repricing tab
        $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'repricing'; 
		if ( $active_tab == 'repricing' ) { 
			return WPLA()->pages['repricing']->displayRepricingPage();
		}
		if ( $active_tab == 'skugen' ) { 
			return WPLA()->pages['skugen']->displaySkuGenPage();
		}
		if ( $active_tab == 'stock_log' ) { 
			return WPLA()->pages['stocklog']->displayStockLogPage();
		}

		// check action - and nonce
		if ( isset($_REQUEST['action']) ) {
			if ( check_admin_referer( 'wpla_tools_page' ) ) {

				// view_logfile
				if ( $_REQUEST['action'] == 'view_logfile') {				
					$this->viewLogfile();
				}

				// wpla_clear_log
				if ( $_REQUEST['action'] == 'wpla_clear_log') {				
					$this->clearLogfile();
					$this->showMessage('Log file was cleared.');
				}

				// update_amazon_orders
				if ( $_REQUEST['action'] == 'update_amazon_orders_30') {				
					do_action( 'wpla_update_orders' );
				}
	
				// wpla_run_daily_schedule
				if ( $_REQUEST['action'] == 'wpla_run_daily_schedule') {
					do_action( 'wpla_daily_schedule' );
				}
				
				// wpla_run_update_schedule
				if ( $_REQUEST['action'] == 'wpla_run_update_schedule') {
					do_action( 'wpla_update_schedule' );
				}

				// wpla_run_autosubmit_fba_orders
				if ( $_REQUEST['action'] == 'wpla_run_autosubmit_fba_orders') {
					do_action( 'wpla_autosubmit_fba_orders' );
				}

				// wpla_refresh_minmax_prices_from_wc
				if ( $_REQUEST['action'] == 'wpla_refresh_minmax_prices_from_wc') {
					$this->refreshMinMaxPrices();
					wpla_show_message('Minimum and maximum prices in WP-Lister have been refreshed.');
				}

				// wpla_match_all_unlisted_with_asin
				if ( $_REQUEST['action'] == 'wpla_match_all_unlisted_with_asin') {
					$this->matchAllUnlistedWithASIN();
				}


				// check_wc_out_of_sync
				if ( $_REQUEST['action'] == 'check_wc_out_of_sync') {				

					$ic = new WPLA_InventoryCheck();
					$mode            = isset( $_REQUEST['mode'] )   		 ? $_REQUEST['mode']   			: 'published';
					$prices          = isset( $_REQUEST['prices'] ) 		 ? $_REQUEST['prices'] 			: false;
					$mark_as_changed = isset( $_REQUEST['mark_as_changed'] ) ? $_REQUEST['mark_as_changed'] : false;
					$step            = isset( $_REQUEST['step']   ) 		 ? $_REQUEST['step']   			: 0;
					$batch_size      = get_option( 'wpla_inventory_check_batch_size', 200 );

					// check new batch of items
					$new_items_were_processed = $ic->checkProductInventory( $mode, $prices, $step );

					if ( $new_items_were_processed ) {

						// continue with step+1
						$msg = 'Checking inventory, please wait... ';
						if ( $mark_as_changed == 'yes' ) {
							$msg = 'Updating listing status, please wait... ';
						} 
						$msg .= '<img src="'.WPLA_URL.'/img/ajax-loader.gif" style="float:left; margin-right:1em; margin-top:0.3em;"/>';

						$step++;
						$msg .= '<br><small>Step '.$step.' / '.($step * $batch_size).' items checked </small>';

						// build button, which is triggered by js automatically
						$url  = 'admin.php?page=wpla-tools&tab=inventory&action=check_wc_out_of_sync&mode='.$mode.'&prices='.$prices.'&mark_as_changed='.$mark_as_changed.'&step='.$step.'&_wpnonce='.wp_create_nonce('wpla_tools_page');
						$msg .= '<a href="'.$url.'" id="wpla_auto_next_step" class="button" style="display:none">Next</a>';
						wpla_show_message( $msg );

					} else {
				
						// show results
						$ic->showProductInventoryCheckResult( $mode );

						// clear tmp data
						update_option('wpla_inventory_check_queue_data', '', 'no');

					}

				} // check_wc_out_of_sync

				// check_wc_out_of_stock
				if ( $_REQUEST['action'] == 'check_wc_out_of_stock') {				

					$ic = new WPLA_InventoryCheck();
					$mark_as_changed = isset( $_REQUEST['mark_as_changed'] ) ? $_REQUEST['mark_as_changed'] : false;
					$step            = isset( $_REQUEST['step']   ) 		 ? $_REQUEST['step']   			: 0;
					$batch_size      = get_option( 'wpla_inventory_check_batch_size', 200 );

					// check new batch of items
					$new_items_were_processed = $ic->checkProductStock( $step );

					if ( $new_items_were_processed ) {

						// continue with step+1
						$msg = 'Checking for out of stock products, please wait... ';
						if ( $mark_as_changed == 'yes' ) {
							$msg = 'Updating listing status, please wait... ';
						} 
						$msg .= '<img src="'.WPLA_URL.'/img/ajax-loader.gif" style="float:left; margin-right:1em; margin-top:0.3em;"/>';

						$step++;
						$msg .= '<br><small>Step '.$step.' / '.($step * $batch_size).' items checked </small>';

						// build button, which is triggered by js automatically
						$url  = 'admin.php?page=wpla-tools&tab=inventory&action=check_wc_out_of_stock&mark_as_changed='.$mark_as_changed.'&step='.$step.'&_wpnonce='.wp_create_nonce('wpla_tools_page');
						$msg .= '<a href="'.$url.'" id="wpla_auto_next_step" class="button" style="display:none">Next</a>';
						wpla_show_message( $msg );

					} else {
				
						// show results
						$ic->showProductStockCheckResult();

						// clear tmp data
						update_option('wpla_inventory_check_queue_data', '', 'no');

					}

				} // check_wc_out_of_stock

				// check_wc_fba_stock
				if ( $_REQUEST['action'] == 'check_wc_fba_stock') {				

					$ic = new WPLA_InventoryCheck();
					$wpla_copy_fba_qty_to_woo = isset( $_REQUEST['wpla_copy_fba_qty_to_woo'] ) ? $_REQUEST['wpla_copy_fba_qty_to_woo'] : false;
					$mode            = isset( $_REQUEST['mode']   ) 		 ? $_REQUEST['mode']   			: 'in_stock_only';
					$step            = isset( $_REQUEST['step']   ) 		 ? $_REQUEST['step']   			: 0;
					$batch_size      = get_option( 'wpla_inventory_check_batch_size', 200 );

					// check new batch of items
					$new_items_were_processed = $ic->checkFBAStock( $mode, $step );

					if ( $new_items_were_processed ) {

						// continue with step+1
						$msg = 'Checking FBA stock levels, please wait... ';
						if ( $wpla_copy_fba_qty_to_woo == 'yes' ) {
							$msg = 'Updating WooCommerce stock levels from FBA, please wait... ';
						} 
						$msg .= '<img src="'.WPLA_URL.'/img/ajax-loader.gif" style="float:left; margin-right:1em; margin-top:0.3em;"/>';

						$step++;
						$msg .= '<br><small>Step '.$step.' / '.($step * $batch_size).' items checked </small>';

						// build button, which is triggered by js automatically
						$url  = 'admin.php?page=wpla-tools&tab=inventory&action=check_wc_fba_stock&mode='.$mode.'&wpla_copy_fba_qty_to_woo='.$wpla_copy_fba_qty_to_woo.'&step='.$step.'&_wpnonce='.wp_create_nonce('wpla_tools_page');
						$msg .= '<a href="'.$url.'" id="wpla_auto_next_step" class="button" style="display:none">Next</a>';
						wpla_show_message( $msg );

					} else {
				
						// show results
						$ic->showFBAStockCheckResult( $mode );

						// clear tmp data
						update_option('wpla_inventory_check_queue_data', '', 'no');

					}

				} // check_wc_fba_stock

				// check_wc_sold_stock
				if ( $_REQUEST['action'] == 'check_wc_sold_stock') {				
					$ic = new WPLA_InventoryCheck();
					$ic->checkSoldStock();
				}

				// wpla_fix_variable_stock_status
				if ( $_REQUEST['action'] == 'wpla_fix_variable_stock_status') {				
					$this->fixVariableStockStatus();
					wpla_show_message('All variation stock levels have been synchronized.');
				}

				// wpla_check_for_missing_products
				if ( $_REQUEST['action'] == 'wpla_check_for_missing_products') {				
					$this->findMissingProducts();
				}

				// wpla_fix_stale_postmeta
				if ( $_REQUEST['action'] == 'wpla_fix_stale_postmeta') {				
					$this->fixStalePostMetaRecords();
				}

				// wpla_fix_orphan_child_products
				if ( $_REQUEST['action'] == 'wpla_fix_orphan_child_products') {				
					$this->fixOrphanChildProducts();
				}

				// wpla_fix_deleted_products
				if ( $_REQUEST['action'] == 'wpla_fix_deleted_products') {				
					$this->fixDeletedProducts();
				}

				// wpla_fix_spaces_in_asins
				if ( $_REQUEST['action'] == 'wpla_fix_spaces_in_asins') {				
					$this->fixSpacesInASINs();
				}

				// wpla_remove_all_imported_products
				if ( $_REQUEST['action'] == 'wpla_remove_all_imported_products') {				
					$this->removeAllImportedProducts();
				}

				// wpla_upgrade_tables_to_utf8mb4
				if ( $_REQUEST['action'] == 'wpla_upgrade_tables_to_utf8mb4') {				
					$this->upgradeTablesUTF8MB4();
				}

				// wpla_repair_crashed_tables
				if ( $_REQUEST['action'] == 'wpla_repair_crashed_tables') {				
					$this->repairCrashedTables();
				}

	
			} else {
				die ('not allowed');
			}
		}

		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,		
			'debug'						=> isset($debug) ? $debug : '',
			'log_size'					=> file_exists( WPLA()->logger->file ) ? filesize( WPLA()->logger->file ) : '',
			'tools_url'	 				=> 'admin.php?page='.self::ParentMenuId.'-tools',
			'form_action'				=> 'admin.php?page='.self::ParentMenuId.'-tools'.'&tab='.$active_tab
		);

		if ( $active_tab == 'developer' ) { 
			$this->display( 'tools_debug', $aData );
			return;
		}

		$this->display( 'tools_page', $aData );
	}


	public function matchAllUnlistedWithASIN() {
		global $wpdb;

		$items = $wpdb->get_results("
            SELECT 
            	pm.meta_value as ASIN,
            	p.ID, p.post_title, p.post_type, p.post_modified,
            	a.sku, a.id
            FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->posts}                 p ON pm.post_id = p.ID
            LEFT JOIN {$wpdb->prefix}amazon_listings a ON pm.post_id = a.post_id

            WHERE p.ID IS NOT NULL
			  AND ( p.post_type = 'product' OR p.post_type = 'product_variation' )
			  AND pm.meta_key = '_wpla_asin'
			  AND pm.meta_value <> ''
			  AND a.id IS NULL

            ORDER BY pm.post_id
            LIMIT 1000
		");
		// echo "<pre>";print_r($items);echo"</pre>";#die();

		$mode  = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : false;
		if ( $mode == 'create_listings' ) {

			$lm = new WPLA_ListingsModel();
			$msg = '';
			foreach ( $items as $product ) {

				$asin               = $product->ASIN;
				$post_id            = $product->ID;
				$default_account_id = get_option( 'wpla_default_account_id', 1 );
				$lm->lastError 	    = null;
				// $lm->last_insert_id = null;

				$success = $lm->insertMatchedProduct( $post_id, $asin, $default_account_id );
				if ( $success ) {

					// $msg .= "Listing {$lm->last_insert_id} was created for ASIN $asin (#$post_id) <br>";
					// if ( $lm->lastError ) $msg .= "{$lm->lastError} <br>";
					$msg .= "{$lm->lastError} <br>";

				} else {
					$msg .= "<b>Failed to match product: {$lm->lastError} </b><br>";
				}

			}

			wpla_show_message( $msg );
			return;
		} // if create_listings


		if ( ! empty($items) ) {

			$nonce      = wp_create_nonce('wpla_tools_page');
			$btn_import = '<a href="admin.php?page=wpla-tools&tab=developer&action=wpla_match_all_unlisted_with_asin&mode=create_listings&_wpnonce='.$nonce.'" class="button button-small button-primary"  >'.'Create listings'.'</a>';
			$buttons    = ' &nbsp; ' . $btn_import;
			wpla_show_message('There are '.sizeof($items).' products(s) that can be matched automatically.'.$buttons, 'info');

		} else {

			wpla_show_message('No products found. All products with ASINs already exist in WP-Lister.');

		}

	} // matchAllUnlistedWithASIN()


	public function refreshMinMaxPrices() {
		global $wpdb;

		$min_prices = $wpdb->get_results("
			SELECT 
				post_id,
				meta_value 
			FROM {$wpdb->prefix}postmeta
			WHERE meta_key = '_amazon_minimum_price'
		");
		
		$max_prices = $wpdb->get_results("
			SELECT 
				post_id,
				meta_value 
			FROM {$wpdb->prefix}postmeta
			WHERE meta_key = '_amazon_maximum_price'
		");

		foreach ($min_prices as $record) {
			$wpdb->update( $wpdb->prefix.'amazon_listings', array( 'min_price' => $record->meta_value, 'pnq_status' => 1 ), array( 'post_id' => $record->post_id ) );
			// echo "<pre>";print_r($wpdb->last_query);echo"</pre>";#die();
		}

		foreach ($max_prices as $record) {
			$wpdb->update( $wpdb->prefix.'amazon_listings', array( 'max_price' => $record->meta_value, 'pnq_status' => 1 ), array( 'post_id' => $record->post_id ) );
			// echo "<pre>";print_r($wpdb->last_query);echo"</pre>";#die();
		}

	} // refreshMinMaxPrices()


	public function fixVariableStockStatus() {

		// get all parent variations
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => -1,
			'tax_query' => array(
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => array( 'variable' ),
				),
			),
		);
		$query = new WP_Query( $args );
		$parent_variations = $query->posts;

		// loop products
		foreach ( $parent_variations as $post ) {
			// $this->fixVariableStockStatusForProduct( $post->ID );
			WC_Product_Variable::sync_stock_status( $post->ID );
		}

	} // fixVariableStockStatus()


	// Find items which are linked to a product which does not exist in WooCommerce
	public function findMissingProducts() {

		$items = WPLA_ListingQueryHelper::findMissingProducts();
		$mode  = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : false;

		if ( $mode == 'delete' ) {
			foreach ( $items as $item ) {
				WPLA_ListingsModel::deleteItem( $item->id );
			}
			wpla_show_message( sizeof($items).' items have been deleted.');
			return;
		}

		if ( $mode == 'import' ) {
			foreach ( $items as $item ) {
				$data = array( 'status' => 'imported' );
				WPLA_ListingsModel::updateWhere( array( 'id' => $item->id ), $data );
			}
			wpla_show_message( sizeof($items).' items have been added to the import queue.');
			return;
		}

		if ( ! empty($items) ) {

			$nonce      = wp_create_nonce('wpla_tools_page');
			$btn_delete = '<a href="admin.php?page=wpla-tools&tab=inventory&action=wpla_check_for_missing_products&mode=delete&_wpnonce='.$nonce.'" class="button button-small button-secondary">'.'Delete all from DB'.'</a> &nbsp; ';
			$btn_import = '<a href="admin.php?page=wpla-tools&tab=inventory&action=wpla_check_for_missing_products&mode=import&_wpnonce='.$nonce.'" class="button button-small button-primary"  >'.'Add to import queue'.'</a>';
			$buttons    = ' &nbsp; ' . $btn_delete . $btn_import;
			wpla_show_message('There are '.sizeof($items).' listing(s) without a linked product in WooCommerce.'.$buttons, 'error');

		} else {

			wpla_show_message('No missing products found.');

		}

	} // findMissingProducts()



	// clear wp_post table from child variations without parent product
	public function fixOrphanChildProducts() {
		global $wpdb;

        $posts = $wpdb->get_results("
            SELECT p1.ID, p1.post_title, p1.post_type, p1.post_modified
            FROM {$wpdb->posts} p1
            LEFT JOIN {$wpdb->posts} p2 ON p1.post_parent = p2.ID
            WHERE p1.post_parent <> 0
              AND p1.post_type <> 'attachment'
              AND p2.ID IS NULL
            ORDER BY p1.ID
        ");
        // echo "<pre>";print_r($posts);echo"</pre>";#die();

		$mode  = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : false;
		if ( $mode == 'delete' ) {
			foreach ( $posts as $post ) {
				$wpdb->delete( $wpdb->posts, array( 'ID' => $post->ID ), array( '%d' ) );
			}
			wpla_show_message('Your posts table has been cleaned.');
			return;
		}

		if ( ! empty($posts) ) {

			$nonce      = wp_create_nonce('wpla_tools_page');
			$btn_delete = '<a href="admin.php?page=wpla-tools&tab=developer&action=wpla_fix_orphan_child_products&mode=delete&_wpnonce='.$nonce.'" class="button button-small button-primary">'.'Clean posts table'.'</a>';
			$buttons    = ' &nbsp; ' . $btn_delete;
			wpla_show_message('There are '.sizeof($posts).' stale child records for non-existent posts in your wp_posts table.'.$buttons, 'error');


			$table_html = '<table style="width:100%;">';
			$table_html .= '<tr>';
			$table_html .= '<th style="text-align:left">'.'ID'.'</th>';
			$table_html .= '<th style="text-align:left">'.'Title'.'</th>';
			$table_html .= '<th style="text-align:left">'.'Last Modified'.'</th>';
			$table_html .= '<th style="text-align:left">'.'Post Type'.'</th>';
			$table_html .= '</tr>';
			foreach ( $posts as $post ) {
				$table_html .= '<tr>';
				$table_html .= '<td>'.$post->ID.'</td>';
				$table_html .= '<td>'.$post->post_title.'</td>';
				$table_html .= '<td>'.$post->post_modified.'</td>';
				$table_html .= '<td>'.$post->post_type.'</td>';
				$table_html .= '</tr>';
			}
			$table_html .= '</table>';

			wpla_show_message( $table_html, 'error' );

		} else {
			wpla_show_message('Your posts table is clean - no orphaned variations were found.');
		}

	} // fixOrphanChildProducts()


	// remove any leading or trailing whitespace from wp_amazon_listings.asin
	public function fixSpacesInASINs() {
		global $wpdb;

        $asins = $wpdb->get_col("
            SELECT asin
            FROM {$wpdb->prefix}amazon_listings
            WHERE asin LIKE '% %';
        ");
        if ( ! $asins ) return;

        $result = $wpdb->get_results("
            UPDATE {$wpdb->prefix}amazon_listings
            SET asin = TRIM( REPLACE( REPLACE( REPLACE( asin,'\t','' ), '\n','' ), '\r','' ) );
        ");

		wpla_show_message('The following ASINs have been fixed: '.join(', ',$asins));

	} // fixSpacesInASINs()


	// clear wp_amazon_listings table from listings where the WooCommerce product has been deleted
	public function fixDeletedProducts() {
		global $wpdb;

        $items = $wpdb->get_results("
            SELECT a.id, a.post_id, a.asin, a.sku, a.listing_title, a.*
            FROM {$wpdb->prefix}amazon_listings a
            LEFT JOIN {$wpdb->posts} p ON a.post_id = p.ID
            WHERE a.post_id <> 0
              AND p.ID IS NULL
            ORDER BY a.post_id
        ");
        // echo "<pre>";print_r($items);echo"</pre>";#die();

		$mode  = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : false;
		if ( $mode == 'delete' ) {
			foreach ( $items as $item ) {
				$wpdb->delete( $wpdb->prefix.'amazon_listings', array( 'ID' => $item->id ), array( '%d' ) );
			}
			wpla_show_message('Your listings table has been cleaned.');
			return;
		}

		if ( ! empty($items) ) {

			$nonce      = wp_create_nonce('wpla_tools_page');
			$btn_delete = '<a href="admin.php?page=wpla-tools&tab=developer&action=wpla_fix_deleted_products&mode=delete&_wpnonce='.$nonce.'" class="button button-small button-primary">'.'Clean listings table'.'</a>';
			$buttons    = ' &nbsp; ' . $btn_delete;
			wpla_show_message('There are '.sizeof($items).' listing records for non-existent products in your listings table.'.$buttons, 'error');

			$table_html = '<table style="width:100%;">';
			$table_html .= '<tr>';
			$table_html .= '<th style="text-align:left">'.'SKU'.'</th>';
			$table_html .= '<th style="text-align:left">'.'ASIN'.'</th>';
			$table_html .= '<th style="text-align:left">'.'Title'.'</th>';
			$table_html .= '<th style="text-align:left">'.'Post ID'.'</th>';
			$table_html .= '<th style="text-align:left">'.'Listing ID'.'</th>';
			$table_html .= '</tr>';
			foreach ( $items as $item ) {
				$table_html .= '<tr>';
				$table_html .= '<td><a href="admin.php?page=wpla&s='.$item->sku.'" target="_blank">'.$item->sku.'</a></td>';
				$table_html .= '<td>'.$item->asin.'</td>';
				$table_html .= '<td>'.$item->listing_title.'</td>';
				$table_html .= '<td>'.$item->post_id.'</td>';
				$table_html .= '<td>'.$item->id.'</td>';
				$table_html .= '</tr>';
			}
			$table_html .= '</table>';

			wpla_show_message( $table_html, 'error' );

		} else {
			wpla_show_message('Your listings table is clean - no missing products found.');
		}

	} // fixDeletedProducts()



	// clear wp_postmeta table from stale records without posts
	public function fixStalePostMetaRecords() {
		global $wpdb;

        $total_count = $wpdb->get_var("
            SELECT count(pm.meta_id)
            FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE p.ID IS NULL
            ORDER BY pm.post_id
        ");

        $post_ids = $wpdb->get_col("
            SELECT DISTINCT pm.post_id
            FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE p.ID IS NULL
            ORDER BY pm.post_id
        ");
        // echo "<pre>";print_r($post_ids);echo"</pre>";die();

		$mode  = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : false;
		if ( $mode == 'delete' ) {
			foreach ( $post_ids as $post_id ) {
				$wpdb->delete( $wpdb->postmeta, array( 'post_id' => $post_id ), array( '%d' ) );
			}
			wpla_show_message('Your post meta table has been cleaned.');
			return;
		}

		if ( ! empty($post_ids) ) {

			$nonce      = wp_create_nonce('wpla_tools_page');
			$btn_delete = '<a href="admin.php?page=wpla-tools&tab=developer&action=wpla_fix_stale_postmeta&mode=delete&_wpnonce='.$nonce.'" class="button button-small button-primary">'.'Clean post meta'.'</a>';
			$buttons    = ' &nbsp; ' . $btn_delete;
			wpla_show_message('There are '.$total_count.' stale records for '.sizeof($post_ids).' non-existent posts in your wp_postmeta table.'.$buttons, 'error');

		} else {
			wpla_show_message('Your post meta table is clean.');
		}


	} // fixStalePostMetaRecords()





	// convert plugin tables to utf8mb4
	// (this should happen automatically on WP4.2, but WordPress only converts utf8 tables and leaves latin1 tables unchanged)
	public function upgradeTablesUTF8MB4() {
		global $wpdb;

		// get list of our tables
		$tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}amazon_%'" );
		if ( empty($tables) ) {
			wpla_show_message('no tables found.','error');
			return;
		}

		// convert all tables
		foreach ( $tables as $table ) {
			$converted = WPLA_UpgradeHelper::convert_custom_table_to_utf8mb4( $table );
			if ( $converted ) {
				wpla_show_message('Table <i>'.$table.'</i> was converted.');
			} else {
				wpla_show_message('Table <i>'.$table.'</i> was not converted.','error');
			}
		}

	} // upgradeTablesUTF8MB4()


	// check and repair all SQL tables
	public function repairCrashedTables() {
		global $wpdb;
		$repaired = 0;

		// get list of all tables
		$tables = $wpdb->get_col( "SHOW TABLES" );
		if ( empty($tables) ) {
			wpla_show_message('no tables found.','error');
			return;
		}

		// convert all tables
		foreach ( $tables as $table ) {

			// check table
			// $check_result = $wpdb->get_results( "CHECK TABLE `$table`" );
			$check_result = $wpdb->get_results( "CHECK TABLE `$table` QUICK" );
			if ( empty( $check_result ) ) continue;
			if ( ! is_array( $check_result ) ) continue;

			// check result
			$msg_text = $check_result[0]->Msg_text;
            if( $msg_text == 'Table is already up to date' || $msg_text == 'OK' ) continue;

            // table needs to be repaired
			$repair_result = $wpdb->get_results( "REPAIR TABLE `$table`" );
			// echo "<pre>";print_r($repair_result);echo"</pre>";
			// echo "<pre>";print_r($wpdb->last_error);echo"</pre>";

			wpla_show_message('Table <i>'.$table.'</i> was repaired.');
			$repaired++;
		}

		wpla_show_message( $repaired . ' table(s) have been repaired.');

	} // repairCrashedTables()





	// remove all imported products and listings - to start from scratch
	public function removeAllImportedProducts() {
		global $wpdb;

        $listing_ids = $wpdb->get_col("
            SELECT al.id
            FROM {$wpdb->prefix}amazon_listings al
            WHERE al.source = 'imported'
               OR al.source = 'foreign_import'
        ");

        $post_ids = $wpdb->get_col("
            SELECT pm.post_id
            FROM {$wpdb->postmeta} pm
            WHERE pm.meta_key   = '_amazon_item_source'
              AND pm.meta_value = 'imported'
        ");
        // echo "<pre>";print_r($post_ids);echo"</pre>";die();

		$mode  = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : false;
		if ( $mode == 'deletion_confirmed' ) {

			foreach ( $post_ids as $post_id ) {
				WPLA_ProductBuilder::deleteProduct( $post_id );
			}

			foreach ( $listing_ids as $listing_id ) {
				$wpdb->delete( $wpdb->prefix.'amazon_listings', array( 'id' => $listing_id ), array( '%d' ) );
			}

			wpla_show_message('All imported products and listings have been removed.');
			return;
		}

		if ( ! empty($post_ids) ) {

			$nonce      = wp_create_nonce('wpla_tools_page');
			$btn_delete = '<a href="admin.php?page=wpla-tools&tab=developer&action=wpla_remove_all_imported_products&mode=deletion_confirmed&_wpnonce='.$nonce.'" class="button button-small button-secondary">'.'Yes, I want to remove all imported products'.'</a>';
			$buttons    = ' &nbsp; ' . $btn_delete;
			wpla_show_message('Are you sure you want to remove '.sizeof($post_ids).' products and '.sizeof($listing_ids).' listings which were imported from Amazon? '.$buttons, 'warn');

		} else {
			wpla_show_message('There are no imported products to remove.');
		}


	} // removeAllImportedProducts()













	public function viewLogfile() {

		echo "<pre>";
		echo readfile( WPLA()->logger->file );
		echo "<br>logfile: " . WPLA()->logger->file . "<br>";
		echo "</pre>";

	}

	public function clearLogfile() {
		file_put_contents( WPLA()->logger->file, '' );
	}

	public function renderSettingsOptions() {
		?>
		<div class="hidden" id="screen-options-wrap" style="display: block;">
			<form method="post" action="" id="dev-settings">
				<h5>Show on screen</h5>
				<div class="metabox-prefs">
						<label for="dev-hide">
							<input type="checkbox" onclick="jQuery('#DeveloperToolBox').toggle();return false;" value="dev" id="dev-hide" name="dev-hide" class="hide-column-tog">
							Developer options
						</label>
					<br class="clear">
				</div>
			</form>
		</div>
		<?php
	}


	
	public function onWpPrintStyles() {

		// deprecated
		// jQuery UI theme - for progressbar
		// wp_register_style('jQueryUITheme', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/themes/cupertino/jquery-ui.css');
		// wp_register_style('jQueryUITheme', plugins_url( 'css/smoothness/jquery-ui-1.8.22.custom.css' , WPLA_PATH.'/wp-lister.php' ) );
		// wp_enqueue_style('jQueryUITheme'); 

	}

	public function onWpEnqueueScripts() {

		// testing:
		// jQuery UI progressbar
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-progressbar');

        // only enqueue JobRunner.js on WPLA pages
        if ( ! isset( $_REQUEST['page'] ) ) return;
       	if ( substr( $_REQUEST['page'], 0, 4 ) != 'wpla' ) return;

		// jqueryFileTree
		wp_register_script( 'wpla_JobRunner', self::$PLUGIN_URL.'js/classes/JobRunner.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-progressbar' ), WPLA_VERSION );
		wp_enqueue_script( 'wpla_JobRunner' );

		wp_localize_script('wpla_JobRunner', 'wpla_JobRunner_i18n', array(
				'msg_loading_tasks' 	=> __('fetching list of tasks', 'wpla').'...',
				'msg_estimating_time' 	=> __('estimating time left', 'wpla').'...',
				'msg_finishing_up' 		=> __('finishing up', 'wpla').'...',
				'msg_all_completed' 	=> __('All {0} tasks have been completed.', 'wpla'),
				'msg_processing' 		=> __('processing {0} of {1}', 'wpla'),
				'msg_time_left' 		=> __('about {0} remaining', 'wpla'),
				'footer_dont_close' 	=> __("Please don't close this window until all tasks are completed.", 'wpla')
			)
		);

	    // jQuery UI Dialog
    	// wp_enqueue_style( 'wp-jquery-ui-dialog' );
	    // wp_enqueue_script ( 'jquery-ui-dialog' ); 

	}


}
