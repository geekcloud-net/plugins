<?php

class WPLA_InventoryCheck extends WPLA_Model  {
	
    var $last_product_id         = 0;
    var $last_product_object     = array();
    // var $last_product_var_id     = 0;
    // var $last_product_variations = array();
    var $last_profile_id         = 0;
    var $last_profile_object     = array();

	// check_wc_out_of_sync
	public function checkProductInventory( $mode = 'published', $compare_prices = false, $step = 0 ) {

		$batch_size = get_option( 'wpla_inventory_check_batch_size', 200 );
		$limit      = $batch_size;
		$offset     = $batch_size * $step;

		// get all published listings
		$lm = new WPLA_ListingsModel();
		// $listings = $mode == 'published' ? $lm->getWhere( 'status', 'online' ) : $lm->getWhere( 'status', 'sold' );
		$listings = $mode == 'published' ? WPLA_ListingQueryHelper::getAllPublished( $limit, $offset ) : WPLA_ListingQueryHelper::getAllSold( $limit, $offset );
		if ( empty($listings) ) return false;

		// restore previous data
		$tmp_result = get_option('wpla_inventory_check_queue_data', false);
		if ( $tmp_result ) {
			$out_of_sync_products = $tmp_result['out_of_sync_products'];
			$published_count      = $tmp_result['published_count'];
		} else {
			$out_of_sync_products = array();
			$published_count      = 0;
		}

		// process published listings
		foreach ( $listings as $item ) {

			// check wc product
			$item = (array) $item;
			$post_id = $item['post_id'];
			$_product = $this->getProduct( $post_id );
			// echo "<pre>";print_r($_product);echo"</pre>";die();

			// checking parent variations makes no sense in WPLA, so skip them
			if ( wpla_get_product_meta( $_product, 'product_type' ) == 'variable' ) continue;


			// get stock level and price
			$stock = WPLA_ProductWrapper::getStock( $item['post_id'] );
			$price = WPLA_ProductWrapper::getPrice( $item['post_id'] );
			// $item['price_max'] = $price;
			// echo "<pre>";print_r($price);echo"</pre>";#die();
			// echo "<pre>";print_r($item);echo"</pre>";die();

			// check for sale price on amazon side
	        $sale_price       = $this->getSalePriceForItem( $item );
			$amazon_price     = $sale_price ? $sale_price : $item['price'];

	
			// check if product and amazon listing are in sync
			$in_sync = true;

			// check stock level
			if ( $stock != $item['quantity'] )
				$in_sync = false;

			// check price
			if ( $compare_prices ) {
			
				if ( round( $price, 2 ) != round( $amazon_price, 2 ) )
					$in_sync = false;
			
			}

			// check max price
			// if ( isset( $price_max ) && isset( $item['price_max'] ) && ( round( $price_max, 2 ) != round ( $item['price_max'], 2 ) ) )
			// 	$in_sync = false;

			// if in sync, continue with next item
			if ( $in_sync )
				continue;


			// mark listing as changed 
			if ( isset( $_REQUEST['mark_as_changed'] ) && $_REQUEST['mark_as_changed'] == 'yes' ) {

				if ( $_product ) {
					// only existing products can have a profile re-applied
					$lm->markItemAsModified( $item['post_id'], true ); // mark as modified, but skip updating feeds
				}

				// in case the product is missing, force the listing to be changed (?)
				$lm->updateListing( $item['id'], array( 'status' => 'changed' ) );

				$item['status'] = 'changed';
			}


			// add to list of out of sync products
			$item['price_woo']      = $price;
			$item['price_woo_max']  = isset( $price_max ) ? $price_max : false;
			$item['stock']          = $stock;
			$item['exists']         = $_product ? true : false;
			$item['type']           = $_product ? wpla_get_product_meta( $_product, 'product_type' ) : 'missing';
			$item['parent_id']      = WPLA_ProductWrapper::getVariationParent( $post_id );
			$out_of_sync_products[] = $item;

			// count products which have not yet been marked as changed
			if ( $item['status'] == 'online' ) $published_count += 1;
		}

		// store result so far
		$tmp_result = array(
			'mode'                 => $mode,
			'compare_prices'       => $compare_prices,
			'out_of_sync_products' => $out_of_sync_products,
			'published_count'      => $published_count,
		);
		update_option('wpla_inventory_check_queue_data', $tmp_result, 'no');

		// true means we processed more items
		return true;

	} // checkProductInventory()


	public function showProductInventoryCheckResult( $mode = 'published' ) {

		// restore previous data
		$tmp_result = get_option('wpla_inventory_check_queue_data', false);
		$out_of_sync_products = @$tmp_result['out_of_sync_products'];
		$published_count      = @$tmp_result['published_count'];
		$compare_prices       = @$tmp_result['compare_prices'];
		$mode                 = @$tmp_result['mode'];

		// return if empty
		if ( empty( $out_of_sync_products ) ) {
			WPLA()->showMessage('All '.$mode.' listings seem to be in sync.', 0, 1);
			return;			
		}

		$msg = '<p>';
		$msg .= 'Warning: '.sizeof($out_of_sync_products).' '.$mode.' listings are out of sync or missing in WooCommerce.';
		$msg .= '</p>';

		// table header
		$msg .= '<table style="width:100%">';
		$msg .= "<tr>";
		$msg .= "<th style='text-align:left'>SKU</th>";
		$msg .= "<th style='text-align:left'>Product</th>";
		$msg .= "<th style='text-align:left'>Local Qty</th>";
		$msg .= "<th style='text-align:left'>Amazon Qty</th>";
		$msg .= "<th style='text-align:left'>Local Price</th>";
		$msg .= "<th style='text-align:left'>Amazon Price</th>";
		$msg .= "<th style='text-align:left'>ASIN</th>";
		$msg .= "<th style='text-align:left'>Status</th>";
		$msg .= "</tr>";

		// table rows
		foreach ( $out_of_sync_products as $item ) {
			// echo "<pre>";print_r($item['asin']);echo"</pre>";#die();

			// get column data
			$sku          = $item['sku'];
			$qty          = $item['quantity'];
			$stock        = $item['stock'];
			$title        = $item['listing_title'];
			$post_id      = $item['post_id'];
			$asin         = $item['asin'];
			$status       = $item['status'];
			$exists       = $item['exists'];
			$price        = wc_price( $item['price'] );
			$price_woo    = wc_price( $item['price_woo'] );
			$product_type = $item['type'] == 'simple' ? '' : $item['type'];

			// highlight changed values
			$changed_stock     =   intval( $item['quantity']   )     ==   intval( $item['stock']     )     ? false : true;
			$changed_price     = floatval( $item['price'] )     == floatval( $item['price_woo'] )     ? false : true;
			$changed_price_max = floatval(@$item['price_max'] ) == floatval( $item['price_woo_max'] ) ? false : true;
			$stock_css         = $changed_stock                       ? 'color:darkred;' : '';
			$price_css         = $changed_price || $changed_price_max ? 'color:darkred;' : '';
			if ( ! $compare_prices ) $price_css = '';

			// build links
			// $amazon_url  = $item['ViewItemURL'] ? $item['ViewItemURL'] : $amazon_url = 'http://www.amazon.com/itm/'.$asin;
			$amazon_url  = 'admin.php?page=wpla&s='.$asin;
			$amazon_link = '<a href="'.$amazon_url.'" target="_blank">'.$asin.'</a>';
			$edit_link   = '<a href="post.php?action=edit&post='. ( $item['parent_id'] ? $item['parent_id'] : $post_id ) .'" target="_blank">'.$title.'</a>';

			// mark non existent products
			if ( ! $exists ) {
				$stock    = 'N/A';
				$post_id .= ' missing!';
			}

			// show price range for variations
			// if ( $item['price_woo_max'] )
			// 	$price_woo .= ' - '.woocommerce_price( $item['price_woo_max'] );
			// if ( @$item['price_max'] )
			// 	$price .= ' - '.woocommerce_price( $item['price_max'] );

			// build table row
			$msg .= "<tr>";
			$msg .= "<td>$sku</td>";
			$msg .= "<td>$edit_link <span style='color:silver'>$product_type (#$post_id)</span></td>";
			$msg .= "<td style='$stock_css'>$stock</td>";
			$msg .= "<td style='$stock_css'>$qty</td>";
			$msg .= "<td style='$price_css'>$price_woo</td>";
			$msg .= "<td style='$price_css'>$price</td>";
			$msg .= "<td>$amazon_link</td>";
			$msg .= "<td>$status</td>";
			$msg .= "</tr>";
		}
		$msg .= '</table>';

		// buttons
		$msg .= '<p>';

		// show 'check again' button
		$url  = 'admin.php?page=wpla-tools&tab=inventory&action=check_wc_out_of_sync&mode='.$mode.'&prices='.$compare_prices.'&_wpnonce='.wp_create_nonce('wpla_tools_page');
		$msg .= '<a href="'.$url.'" class="button">'.__('Check again','wpla').'</a> &nbsp; ';

		// show 'mark all as changed' button
		if ( $mode == 'published' )
		if ( $published_count ) {
			$url = 'admin.php?page=wpla-tools&tab=inventory&action=check_wc_out_of_sync&mark_as_changed=yes&mode='.$mode.'&prices='.$compare_prices.'&_wpnonce='.wp_create_nonce('wpla_tools_page');
			$msg .= '<a href="'.$url.'" class="button">'.__('Mark all as changed','wpla').'</a> &nbsp; ';
			$msg .= 'Click this button to mark all found listings as changed in WP-Lister.';
		} else {
			// $msg .= '<a id="btn_revise_all_changed_items_reminder" class="btn_revise_all_changed_items_reminder button wpl_job_button">' . __('Revise all changed items','wpla') . '</a>';
			// $msg .= ' &nbsp; ';
			// $msg .= 'Click to revise all changed items.';
		}
		$msg .= '</p>';		

		WPLA()->showMessage( $msg, 1, 1 );

	} // showProductInventoryCheckResult()






	// check_wc_out_of_stock
	public function checkProductStock( $step = 0 ) {

		$batch_size = get_option( 'wpla_inventory_check_batch_size', 200 );
		$limit      = $batch_size;
		$offset     = $batch_size * $step;

		// get all published listings
		$lm = new WPLA_ListingsModel();
		$listings = WPLA_ListingQueryHelper::getAllPublished( $limit, $offset );
		if ( empty($listings) ) return false;

		// restore previous data
		$tmp_result = get_option('wpla_inventory_check_queue_data', false);
		if ( $tmp_result ) {
			$out_of_stock_products = $tmp_result['out_of_stock_products'];
		} else {
			$out_of_stock_products = array();
		}

		// process published listings
		foreach ( $listings as $item ) {

			// get wc product
			$item = (array) $item;
			$_product = $this->getProduct( $item['post_id'] );

			// checking parent variations makes no sense in WPLA, so skip them
			if ( wpla_get_product_meta( $_product, 'product_type' ) == 'variable' ) continue;

			// check stock level
			$stock = WPLA_ProductWrapper::getStock( $item['post_id'] );
			// $stock = $_product ? $_product->get_total_stock() : 0;
			if ( $stock > 0 )
				continue;

			if ( $item['quantity'] == 0 )
				continue;

			// mark listing as changed
			if ( isset( $_REQUEST['mark_as_changed'] ) && $_REQUEST['mark_as_changed'] == 'yes' ) {
				$lm->updateListing( $item['id'], array( 'status' => 'changed' ) );
				$item['status'] = 'changed';
			}

			// add to list of out of stock products
			$item['stock']     = $stock;
			$item['exists']    = $_product ? true : false;
			$item['parent_id'] = WPLA_ProductWrapper::getVariationParent( $item['post_id'] );
			$out_of_stock_products[] = $item;

		}

		// store result so far
		$tmp_result = array(
			'out_of_stock_products' => $out_of_stock_products,
		);
		update_option('wpla_inventory_check_queue_data', $tmp_result, 'no');

		// true means we processed more items
		return true;

	} // checkProductStock()



	public function showProductStockCheckResult( $mode = 'out_of_stock' ) {

		// restore previous data
		$tmp_result = get_option('wpla_inventory_check_queue_data', false);
		$out_of_stock_products = $tmp_result['out_of_stock_products'];
		// $published_count      = $tmp_result['published_count'];
		// $compare_prices       = $tmp_result['compare_prices'];
		// $mode                 = $tmp_result['mode'];


		// return if empty
		if ( empty( $out_of_stock_products ) ) {
			WPLA()->showMessage('No out of stock products found.', 0, 1);
			return;			
		}

		$msg = '<p>';
		$msg .= sprintf( 'Warning: %s published listings are out of stock or missing in WooCommerce.', sizeof($out_of_stock_products) );
		$msg .= '</p>';

		// table header
		$msg .= '<table style="width:100%">';
		$msg .= "<tr>";
		$msg .= "<th style='text-align:left'>Stock</th>";
		$msg .= "<th style='text-align:left'>SKU</th>";
		$msg .= "<th style='text-align:left'>Product</th>";
		$msg .= "<th style='text-align:left'>Qty</th>";
		$msg .= "<th style='text-align:left'>ASIN</th>";
		$msg .= "<th style='text-align:left'>Status</th>";
		$msg .= "</tr>";

		// table rows
		foreach ( $out_of_stock_products as $item ) {

			// get column data
			$sku     = $item['sku'];
			$qty     = $item['quantity'];
			$stock   = $item['stock'] . ' x ';
			$title   = $item['listing_title'];
			$post_id = $item['post_id'];
			$asin    = $item['asin'];
			$status  = $item['status'];
			$exists  = $item['exists'];

			// build links
			// $amazon_url  = $item['ViewItemURL'] ? $item['ViewItemURL'] : $amazon_url = 'http://www.amazon.com/itm/'.$asin;
			$amazon_url  = 'admin.php?page=wpla&s='.$asin;
			$amazon_link = '<a href="'.$amazon_url.'" target="_blank">'.$asin.'</a>';
			$edit_link   = '<a href="post.php?action=edit&post='. ( $item['parent_id'] ? $item['parent_id'] : $post_id ) .'" target="_blank">'.$title.'</a>';

			// mark non existent products
			if ( ! $exists ) {
				$stock    = 'N/A';
				$post_id .= ' missing!';
			}

			// build table row
			$msg .= "<tr>";
			$msg .= "<td>$stock</td>";
			$msg .= "<td>$sku</td>";
			$msg .= "<td>$edit_link (ID $post_id)</td>";
			$msg .= "<td>$qty x </td>";
			$msg .= "<td>$amazon_link</td>";
			$msg .= "<td>$status</td>";
			$msg .= "</tr>";
		}
		$msg .= '</table>';


		$msg .= '<p>';
		$url = 'admin.php?page=wpla-tools&action=check_wc_out_of_stock&mark_as_changed=yes&_wpnonce='.wp_create_nonce('wpla_tools_page');
		$msg .= '<a href="'.$url.'" class="button">'.__('Mark all as changed','wpla').'</a> &nbsp; ';
		$msg .= 'Click this button to mark all found listings as changed in WP-Lister.';
		$msg .= '</p>';

		WPLA()->showMessage( $msg, 1, 1 );

	} // showProductStockCheckResult()





	// check_wc_fba_stock
	public function checkFBAStock( $mode = 'in_stock_only', $step = 0 ) {

		$batch_size = get_option( 'wpla_inventory_check_batch_size', 200 );
		$limit      = $batch_size;
		$offset     = $batch_size * $step;

		// get all published listings
		$lm = new WPLA_ListingsModel();
		$out_of_sync_products = array();

		if ( $mode == 'all_stock' ) {
			$listings = $lm->getAllItemsUsingFBA( $limit, $offset );
		} else {
			$listings = $lm->getAllItemsWithStockInFBA( $limit, $offset );
		}
		if ( empty($listings) ) return false;

		// restore previous data
		$tmp_result = get_option('wpla_inventory_check_queue_data', false);
		if ( $tmp_result ) {
			$out_of_sync_products = $tmp_result['out_of_sync_products'];
		} else {
			$out_of_sync_products = array();
		}

		// process FBA listings
		foreach ( $listings as $item ) {

			// get wc product
			$item = (array) $item;
			$_product = $this->getProduct( $item['post_id'] );
			if ( ! $_product ) continue;

			// checking parent variations makes no sense in WPLA, so skip them
			if ( wpla_get_product_meta( $_product, 'product_type' ) == 'variable' ) continue;

			// check stock level
			$stock = WPLA_ProductWrapper::getStock( $item['post_id'] );
			if ( $stock == $item['fba_quantity'] ) 
				continue;

			// copy FBA qty to Woo
            if ( isset( $_REQUEST['wpla_copy_fba_qty_to_woo'] ) && $_REQUEST['wpla_copy_fba_qty_to_woo'] == 'yes' ) {
				update_post_meta( $item['post_id'], '_stock', $item['fba_quantity'] );
				continue;
			}

			// add to list of out of stock products
			$item['stock']     = $stock;
			$item['parent_id'] = WPLA_ProductWrapper::getVariationParent( $item['post_id'] );
			$out_of_sync_products[] = $item;

		}

		// store result so far
		$tmp_result = array(
			'out_of_sync_products' => $out_of_sync_products,
		);
		update_option('wpla_inventory_check_queue_data', $tmp_result, 'no');

		// true means we processed more items
		return true;

	} // checkFBAStock()



	public function showFBAStockCheckResult( $mode = 'in_stock_only' ) {

		// restore previous data
		$tmp_result = get_option('wpla_inventory_check_queue_data', false);
		$out_of_sync_products = @$tmp_result['out_of_sync_products'];
		// $mode                 = $tmp_result['mode'];

		// return if empty
		if ( empty( $out_of_sync_products ) ) {
			WPLA()->showMessage('All FBA products are in sync with WooCommerce.', 0, 1);
			return;			
		}

		$msg = '<p>';
		$msg .= sprintf( 'There are %s FBA products have a different stock level in WooCommerce.', sizeof($out_of_sync_products) );
		$msg .= '</p>';

		// table header
		$msg .= '<table style="width:100%">';
		$msg .= "<tr>";
		$msg .= "<th style='text-align:left'>SKU</th>";
		$msg .= "<th style='text-align:left'>Product</th>";
		$msg .= "<th style='text-align:left'>FBA</th>";
		$msg .= "<th style='text-align:left'>WooCommerce</th>";
		$msg .= "<th style='text-align:left'>ASIN</th>";
		$msg .= "<th style='text-align:left'>Status</th>";
		$msg .= "</tr>";

		// table rows
		foreach ( $out_of_sync_products as $item ) {

			// get column data
			$sku     = $item['sku'];
			$qty     = $item['quantity'];
			$fba_qty = $item['fba_quantity'];
			$stock   = $item['stock'];
			$title   = $item['listing_title'];
			$post_id = $item['post_id'];
			$asin    = $item['asin'];
			$status  = $item['status'];

			// build links
			// $amazon_url  = $item['ViewItemURL'] ? $item['ViewItemURL'] : $amazon_url = 'http://www.amazon.com/itm/'.$asin;
			$amazon_url  = 'admin.php?page=wpla&s='.$asin;
			$amazon_link = '<a href="'.$amazon_url.'" target="_blank">'.$asin.'</a>';
			$edit_link   = '<a href="post.php?action=edit&post='. ( $item['parent_id'] ? $item['parent_id'] : $post_id ) .'" target="_blank">'.$title.'</a>';

			// build table row
			$msg .= "<tr>";
			$msg .= "<td>$sku</td>";
			$msg .= "<td>$edit_link</td>";
			$msg .= "<td>$fba_qty</td>";
			$msg .= "<td>$stock</td>";
			$msg .= "<td>$amazon_link</td>";
			$msg .= "<td>$status</td>";
			$msg .= "</tr>";
		}
		$msg .= '</table>';


		$msg .= '<p>';
		$url = 'admin.php?page=wpla-tools&tab=inventory&action=check_wc_fba_stock&wpla_copy_fba_qty_to_woo=yes&mode='.$mode.'&_wpnonce='.wp_create_nonce('wpla_tools_page');
		$msg .= '<a href="'.$url.'" class="button">'.__('Copy FBA quantity to WooCommerce','wpla').'</a> &nbsp; ';
		$msg .= 'Click this button set the stock level in WooCommerce to the current FBA quantity for each found product.';
		$msg .= '</p>';

		WPLA()->showMessage( $msg, 1, 1 );


	} // checkFBAStock()





	// check_wc_sold_stock
	public function checkSoldStock() {

		// get all published listings
		$lm = new WPLA_ListingsModel();
		$listings = $lm->getWhere( 'status', 'sold' );
		$out_of_stock_products = array();

		// process published listings
		foreach ( $listings as $item ) {

			// get wc product
			$_product = $this->getProduct( $item['post_id'] );

			// checking parent variations makes no sense in WPLA, so skip them
			if ( wpla_get_product_meta( $_product, 'product_type' ) == 'variable' ) continue;

			// check stock level
			// $stock = WPLA_ProductWrapper::getStock( $item['post_id'] );
            $stock = 0;

            if ( $_product ) {
                $stock = WPLA_ProductWrapper::getStock( $_product );
            }

			if ( $stock == 0 )
				continue;

			// mark listing as changed
			// if ( isset( $_REQUEST['mark_as_changed'] ) ) {
			// 	$lm->updateListing( $item['id'], array( 'status' => 'changed' ) );
			// 	$item['status'] = 'changed';
			// }

			// add to list of out of stock products
			$item['stock']     = $stock;
			$item['exists']    = $_product ? true : false;
			$item['parent_id'] = WPLA_ProductWrapper::getVariationParent( $item['post_id'] );
			$out_of_stock_products[] = $item;

		}

		// return if empty
		if ( empty( $out_of_stock_products ) ) {
			WPLA()->showMessage('No sold products have stock in WooCommerce.', 0, 1);
			return;			
		}

		$msg = '<p>';
		$msg .= 'Warning: Some sold listings are still in stock in WooCommerce.';
		$msg .= '</p>';

		// table header
		$msg .= '<table style="width:100%">';
		$msg .= "<tr>";
		$msg .= "<th style='text-align:left'>Stock</th>";
		$msg .= "<th style='text-align:left'>SKU</th>";
		$msg .= "<th style='text-align:left'>Product</th>";
		$msg .= "<th style='text-align:left'>Qty</th>";
		$msg .= "<th style='text-align:left'>ASIN</th>";
		$msg .= "<th style='text-align:left'>Ended at</th>";
		$msg .= "<th style='text-align:left'>Status</th>";
		$msg .= "</tr>";

		// table rows
		foreach ( $out_of_stock_products as $item ) {

			// get column data
			// $qty     = $item['quantity'] - $item['quantity_sold'];
			$sku     = $item['sku'];
			$qty     = $item['quantity'];
			$stock   = $item['stock'] . ' x ';
			$title   = $item['listing_title'];
			$post_id = $item['post_id'];
			$asin    = $item['asin'];
			$status  = $item['status'];
			$exists  = $item['exists'];
			$date_ended = $item['date_finished'] ? $item['date_finished'] : $item['end_date'];

			// build links
			$amazon_url = $item['ViewItemURL'] ? $item['ViewItemURL'] : $amazon_url = 'http://www.amazon.com/itm/'.$asin;
			$amazon_url  = 'admin.php?page=wpla&s='.$asin;
			$amazon_link = '<a href="'.$amazon_url.'" target="_blank">'.$asin.'</a>';
			$edit_link   = '<a href="post.php?action=edit&post='. ( $item['parent_id'] ? $item['parent_id'] : $post_id ) .'" target="_blank">'.$title.'</a>';

			// mark non existent products
			if ( ! $exists ) {
				$stock    = 'N/A';
				$post_id .= ' missing!';
			}

			// build table row
			$msg .= "<tr>";
			$msg .= "<td>$stock</td>";
			$msg .= "<td>$sku</td>";
			$msg .= "<td>$edit_link (ID $post_id)</td>";
			$msg .= "<td>$qty x </td>";
			$msg .= "<td>$amazon_link</td>";
			$msg .= "<td>$date_ended</td>";
			$msg .= "<td>$status</td>";
			$msg .= "</tr>";
		}
		$msg .= '</table>';

		// show 'check again' button
		$msg .= '<p>';
		$url  = 'admin.php?page=wpla-tools&action=check_wc_sold_stock&_wpnonce='.wp_create_nonce('wpla_tools_page');
		$msg .= '<a href="'.$url.'" class="button">'.__('Check again','wpla').'</a> &nbsp; ';
		$msg .= '</p>';

		// $msg .= '<p>';
		// $url = 'admin.php?page=wpla-tools&action=check_wc_out_of_stock&mark_as_changed=yes&_wpnonce='.wp_create_nonce('wpla_tools_page');
		// $msg .= '<a href="'.$url.'" class="button">'.__('Mark all as changed','wpla').'</a> &nbsp; ';
		// $msg .= 'Click this button to mark all found listings as changed in WP-Lister.';
		// $msg .= '</p>';

		WPLA()->showMessage( $msg, 1, 1 );


	} // checkSoldStock()

    function getSalePriceForItem($item) {        
        if ( ! $item['post_id'] ) return false;
        $post_id = $item['post_id'];

        $product = $this->getProduct( $post_id );
        $profile = $this->getProfile( $item['profile_id'] );

        $value   = wpla_get_product_meta( $product, 'sale_price' );          // WC2.0 compat
        $value   = $profile ? $profile->processProfilePrice( $value ) : $value;
        $value   = apply_filters( 'wpla_filter_sale_price', $value, $post_id, $product, $item, $profile );

        return $value;
    }

    // get profile object - if possible from cache
    function getProfile( $profile_id ) {

        // update cache if required
        if ( $this->last_profile_id != $profile_id ) {
            $this->last_profile_object = new WPLA_AmazonProfile( $profile_id );
            $this->last_profile_id     = $profile_id;
        }

        return $this->last_profile_object;
    }
        
    // get product object - if possible from cache
    function getProduct( $post_id ) {

        // update cache if required
        if ( $this->last_product_id != $post_id ) {
            $this->last_product_object = WPLA_ProductWrapper::getProduct( $post_id );
            $this->last_product_id     = $post_id;
        }

        return $this->last_product_object;
    }
	

} // class WPLA_InventoryCheck
