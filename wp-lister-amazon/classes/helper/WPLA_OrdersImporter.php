<?php

class WPLA_OrdersImporter {
	
	var $account;
	public $result;
	public $updated_count = 0;
	public $imported_count = 0;
	public $throttling_is_active = false;

	const TABLENAME = 'amazon_orders';


	public function importOrder( $order, $account ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		// skip processing if requests are throttled already
		if ( $this->throttling_is_active == true ) return false;

		// check if order exists in WPLA and is already up to date (TODO: optimize)
		// (LastUpdateDate apparently isn't updated when OrderStatus changes from Pending to Canceled - so we need to compare date and status!)
		if ( $id = $this->order_id_exists( $order->AmazonOrderId ) ) {			
			$om = new WPLA_OrdersModel();
			$amazon_order = $om->getItem( $id );
			if ( $amazon_order['LastTimeModified'] == $this->convertIsoDateToSql( $order->LastUpdateDate ) && 
				 $amazon_order['status']           == $order->OrderStatus ) {
				WPLA()->logger->info('Order '.$order->AmazonOrderId.' has not been modified since '.$amazon_order['LastTimeModified'].' and is up to date.');
				wpla_show_message(   'Order '.$order->AmazonOrderId.' has not been modified since '.$amazon_order['LastTimeModified'].' and is up to date.');

				// if "Filter orders" is enabled, make sure the order is assigned to the right account_id
				if ( get_option( 'wpla_fetch_orders_filter', 0 ) == 1 ) {
					if ( $amazon_order['account_id'] != $account->id ) {

						// update account_id on existing order
						$data = array( 'account_id' => $account->id );
						$wpdb->update( $table, $data, array( 'order_id' => $order->AmazonOrderId ) );

						WPLA()->logger->info('Order '.$order->AmazonOrderId.' was switched from account ID '.$amazon_order['account_id'].' to: '.$account->id);
						wpla_show_message(   'Order '.$order->AmazonOrderId.' was switched from account ID '.$amazon_order['account_id'].' to: '.$account->id);
					}
				}

				return null;
			}
		}


		$data = array(
			'order_id'             => $order->AmazonOrderId,
			'status'               => $order->OrderStatus,
			// pending orders are missing some details
			'total'                => isset( $order->OrderTotal->Amount ) ? $order->OrderTotal->Amount : '',
			'currency'             => isset( $order->OrderTotal->CurrencyCode ) ? $order->OrderTotal->CurrencyCode : '',
			'buyer_name'           => isset( $order->BuyerName ) ? $order->BuyerName : '',
			'buyer_email'          => isset( $order->BuyerEmail ) ? $order->BuyerEmail : '',
			'PaymentMethod'        => isset( $order->PaymentMethod ) ? $order->PaymentMethod : '',
			'ShippingAddress_City' => isset( $order->ShippingAddress->City ) ? $order->ShippingAddress->City : '',
			'date_created'         => $this->convertIsoDateToSql( $order->PurchaseDate ),
			'LastTimeModified'     => $this->convertIsoDateToSql( $order->LastUpdateDate ),
			'account_id'		   => $account->id,
			'details'			   => json_encode( $order )
		);

		// fetch order line items from Amazon - required for both new and updated orders
		$this->api     = new WPLA_AmazonAPI( $account->id );

		// Don't check and update line items when the order has already been shipped/completed
        // to prevent throttling from Amazon #16649
        $items = false;
        if ( get_option( 'wpla_conditional_order_item_updates' ) == 1 ) {
            if ( $order->OrderStatus != 'Shipped' ) {
                $items         = $this->api->getOrderLineItems( $order->AmazonOrderId );
                $data['items'] = maybe_serialize( $items );
            }
        } else {
            $items         = $this->api->getOrderLineItems( $order->AmazonOrderId );
            $data['items'] = maybe_serialize( $items );
        }

        // check if ListOrderItems request is throttled
        // if true, skip ALL further requests / order processing until next cron run
        if ( is_object($items) && isset($items->Error->Message) ) {
            $this->throttling_is_active = true;
            wpla_show_message('ListOrderItems requests are throttled. Skipping further order processing until next run.','warn');
            return false;
        }

		// check if order exists in WPLA
		if ( $id = $this->order_id_exists( $order->AmazonOrderId ) ) {

			// load existing order record from wp_amazon_orders 
			$ordersModel        = new WPLA_OrdersModel();
			$wpla_order         = $ordersModel->getItem( $id );
			$wpla_order_updated = false;

			// check if order status was updated
			// if pending -> Canceled: revert stock reduction by processing history records
			// if pending -> Shipped / Unshipped: create WooCommerce order if enabled (done in createOrUpdateWooCommerceOrder())
			if ( $order->OrderStatus != $wpla_order['status'] ) {

				$old_order_status = $wpla_order['status'];
				$new_order_status = $order->OrderStatus;

				// add history record
				$history_message = "Order status has changed from ".$old_order_status." to ".$new_order_status;
				$history_details = array( 'id' => $id, 'new_status' => $new_order_status, 'old_status' => $old_order_status, 'LastTimeModified' => $data['LastTimeModified'] );
				self::addHistory( $data['order_id'], 'order_status_changed', $history_message, $history_details );

				// if pending -> Canceled: revert stock reduction by processing history records
				if ( ( $old_order_status == 'Pending' ) && ( $new_order_status == 'Canceled' ) ) {

					// revert stock reduction
					$this->revertStockReduction( $wpla_order );

					// add history record
					$history_message = "Stock levels have been replenished";
					$history_details = array( 'id' => $id );
					self::addHistory( $data['order_id'], 'revert_stock', $history_message, $history_details );

				}

                // Update WC Order's status based on the new amazon order status
                if ( !empty( $wpla_order['post_id'] ) ) {
				    // Save the amazon order first so WPLA could adjust the WC order's status accordingly #22305
                    $wpdb->update( $table, $data, array( 'order_id' => $order->AmazonOrderId ) );
                    $wpla_order_updated = true;

				    $wob = new WPLA_OrderBuilder();
				    $wob->updateOrderFromAmazonOrder( $id, $wpla_order['post_id'] );
                }

			} // if status changed

			// update existing order
            if ( !$wpla_order_updated ) {
			    $wpdb->update( $table, $data, array( 'order_id' => $order->AmazonOrderId ) );
            }
			$this->updated_count++;

			// add history record
			$history_message = "Order details were updated - ".$data['LastTimeModified'];
			$history_details = array( 'id' => $id, 'status' => $data['status'], 'LastTimeModified' => $data['LastTimeModified'] );
			self::addHistory( $data['order_id'], 'order_updated', $history_message, $history_details );

		} else {

			// insert new order
			$wpdb->insert( $table, $data );
			$this->imported_count++;
			$id = $wpdb->insert_id;
			echo $wpdb->last_error;

			// add history record
			$history_message = "Order was added with status: ".$data['status'];
			$history_details = array( 'id' => $id, 'status' => $data['status'], 'LastTimeModified' => $data['LastTimeModified'] );
			self::addHistory( $data['order_id'], 'order_inserted', $history_message, $history_details );

			// process ordered items - unless order has been cancelled
			if ( $data['status'] != 'Canceled') {
				foreach ($items as $item) {
					// process each item and reduce stock level
					$success = $this->processListingItem( $item, $order );
				}
			}

		} // if order does not exist


		## BEGIN PRO ##
		// create woocommerce order - if enabled
		if ( get_option( 'wpla_create_orders' ) ) {
			$this->createOrUpdateWooCommerceOrder( $id );
		}
		## END PRO ##

		return $id;
	} // importOrder()

	## BEGIN PRO ##
	// create or update WooCommerce order from wpla_order
	function createOrUpdateWooCommerceOrder( $id ) {

		// load updated order record from wp_amazon_orders 
		$ordersModel = new WPLA_OrdersModel();
		$wpla_order  = $ordersModel->getItem( $id );

		// return if no order found or if WC order has already been created
		if ( ! $wpla_order ) return;
		// if ( ! empty( $wpla_order['post_id'] ) ) return;

		// check if order has been cancelled or is still pending - don't create WooCommerce order then
		if ( in_array( $wpla_order['status'], array( 'Canceled', 'Pending' ) ) ) {
			WPLA()->logger->info( 'skipped woo order creation - status is '.$wpla_order['status'].' - order id #'.$wpla_order['order_id'] );				
			return;
		} 

		// check if order line items have been downloaded successfully 
		// prevents invalid orders being created if request throtteling should kick in
		if ( ! is_array( $wpla_order['items'] ) ) {
			WPLA()->logger->info( 'skipped woo order creation - order line items are invalid (request throttled?)' );
			return;
		}

		// maybe skip orders containing only foreign items
		if ( get_option( 'wpla_skip_foreign_item_orders' ) ) {

			// check if order line items exists in WP-Lister			
			$order_items     = maybe_unserialize( $wpla_order['items'] );
			$has_known_items = false;
			$listingsModel   = new WPLA_ListingsModel();
			foreach ( $order_items as $item ) {
				// $listing = $listingsModel->getItemBySKU( $item->SellerSKU, false ); // maybe use getItemBySkuAndAccount() instead?
				$listing = $listingsModel->getItemByASIN( $item->ASIN, false );
				if ( $listing ) $has_known_items = true;
			}

			// skip if order contains no known items
			if ( ! $has_known_items ) {
				// $history_message = "Not creating order in WooCommerce - no known items found"; // don't add history row - or check first whether it was already added!
				// $this->addHistory( $wpla_order['order_id'], 'skipped_create_order', $history_message, array() );
				WPLA()->logger->info( 'No known items found, skipped creating order in WooCommerce - order id #'.$wpla_order['order_id'] );
				return;
			}

		}

		// check if WooCommerce order already exists
		if ( empty( $wpla_order['post_id'] ) ) {

			// allow other code/plugins to decide whether an order in WooCommerce should be created
			$skip_create_order_reason = apply_filters('wpla_reason_for_not_creating_wc_order', false, $wpla_order );
			if ( $skip_create_order_reason ) {
				// add history record
				$history_message = "Order was not created: ".$skip_create_order_reason;
				$history_details = array( 'reason' => $skip_create_order_reason );
				self::addHistory( $wpla_order['order_id'], 'hook_skipped_order', $history_message, $history_details );
				return;
			}

			// create WooCommerce order
			$ob = new WPLA_OrderBuilder();
			$order_post_id = $ob->createWooOrderFromAmazonOrder( $id );

			// add history record
			$history_message = "Order #$order_post_id was created";
			$history_details = array( 'post_id' => $order_post_id, 'status' => $wpla_order['status'], 'user_id' => get_current_user_id() );
			self::addHistory( $wpla_order['order_id'], 'create_order', $history_message, $history_details );

		} else {

			// update WooCommerce order
			$ob = new WPLA_OrderBuilder();
			$ob->updateOrderFromAmazonOrder( $id, $wpla_order['post_id'] );

		}

	} // createOrUpdateWooCommerceOrder()
	## END PRO ##

	// revert stock reduction by processing history records
	function revertStockReduction( $wpla_order ) {
		global $wpdb;

		if ( ! is_array( $wpla_order['history'] ) ) return;

		foreach ( $wpla_order['history'] as $history_record ) {
			
			// filter reduce_stock actions
			if ( $history_record->action != 'reduce_stock' ) continue;

			// make sure purchased qty was recorded (since 0.9.2.8)
			$details = $history_record->details;
			if ( ! isset( $details['qty_purchased'] ) ) continue;
			$quantity_purchased = $details['qty_purchased'];

			// handle non-FBA quantity
			if ( ! isset( $details['fba_quantity'] ) && isset( $details['sku'] ) ) {

				// get listing item
				$lm = new WPLA_ListingsModel();
				$listing = $lm->getItemBySKU( $details['sku'] );

				// update quantity for FBA orders
				$quantity      = $listing->quantity      + $quantity_purchased;
				$quantity_sold = $listing->quantity_sold - $quantity_purchased;

				$wpdb->update( $wpdb->prefix.'amazon_listings', 
					array( 
						'quantity'  => $quantity,
						'quantity_sold' => $quantity_sold 
					), 
					array( 'sku' => $details['sku'] ) 
				);

			}

			// handle FBA quantity
			if ( isset( $details['fba_quantity'] ) && isset( $details['sku'] ) ) {

				// get listing item
				$lm = new WPLA_ListingsModel();
				$listing = $lm->getItemBySKU( $details['sku'] );

				// update quantity for FBA orders
				$fba_quantity  = $listing->fba_quantity  + $quantity_purchased;
				$quantity_sold = $listing->quantity_sold - $quantity_purchased;

				$wpdb->update( $wpdb->prefix.'amazon_listings', 
					array( 
						'fba_quantity'  => $fba_quantity,
						'quantity_sold' => $quantity_sold 
					), 
					array( 'sku' => $details['sku'] ) 
				);

			}

			// handle WooCommerce quantity
			if ( isset( $details['product_id'] ) ) {

				// increase product stock
				$post_id = $details['product_id'];
				$newstock = WPLA_ProductWrapper::increaseStockBy( $post_id, $quantity_purchased, $wpla_order['order_id'] );
				WPLA()->logger->info( 'increased product stock for #'.$post_id.' by '.$quantity_purchased.' - new qty: '.$newstock );

				// notify WP-Lister for eBay (and other plugins)
				do_action( 'wpla_inventory_status_changed', $post_id );
				if ( isset($details['parent_id']) && $details['parent_id'] ) {
					do_action( 'wpla_inventory_status_changed', $details['parent_id'] );
				}
			}


		} // each history record

	} // revertStockReduction()

	// update listing sold quantity and status
	function processListingItem( $item, $order ) {
		global $wpdb;

		// abort if item data is invalid
		if ( ! isset( $item->ASIN ) && ! isset( $item->QuantityOrdered ) ) {
			$history_message = "Error fetching order line items - request throttled?";
			$history_details = array();
			self::addHistory( $order->AmazonOrderId, 'request_throttled', $history_message, $history_details );
			return false;
		}

		$order_id           = $order->AmazonOrderId;
		$asin               = $item->ASIN;
		$sku                = $item->SellerSKU;
		$quantity_purchased = $item->QuantityOrdered;
		
		// get listing item
		$lm = new WPLA_ListingsModel();
		$listing = $lm->getItemBySKU( $sku );

		// skip if this listing does not exist in WP-Lister
		if ( ! $listing ) {
			$history_message = "Skipped unknown SKU {$sku} ({$asin})";
			$history_details = array( 'sku' => $sku, 'asin' => $asin );
			self::addHistory( $order_id, 'skipped_item', $history_message, $history_details );
			return true;
		}


		// handle FBA orders
		if ( $order->FulfillmentChannel == 'AFN' ) {

			// update quantity for FBA orders
			$fba_quantity  = $listing->fba_quantity  - $quantity_purchased;
			$quantity_sold = $listing->quantity_sold + $quantity_purchased;

			$wpdb->update( $wpdb->prefix.'amazon_listings', 
				array( 
					'fba_quantity'  => $fba_quantity,
					'quantity_sold' => $quantity_sold 
				), 
				array( 'sku' => $sku ) 
			);

			// add history record
			$history_message = "FBA quantity reduced by $quantity_purchased for listing {$sku} ({$asin}) - FBA stock $fba_quantity ($quantity_sold sold)";
			$history_details = array( 'fba_quantity' => $fba_quantity, 'sku' => $sku, 'asin' => $asin, 'qty_purchased' => $quantity_purchased, 'listing_id' => $listing->id );
			self::addHistory( $order_id, 'reduce_stock', $history_message, $history_details );

		} else {

			// update quantity for non-FBA orders
			$quantity_total = $listing->quantity      - $quantity_purchased;
			$quantity_sold  = $listing->quantity_sold + $quantity_purchased;
			$wpdb->update( $wpdb->prefix.'amazon_listings', 
				array( 
					'quantity'      => $quantity_total,
					'quantity_sold' => $quantity_sold 
				), 
				array( 'sku' => $sku ) 
			);

			// add history record
			$history_message = "Quantity reduced by $quantity_purchased for listing {$sku} ({$asin}) - new stock: $quantity_total ($quantity_sold sold)";
			$history_details = array( 'newstock' => $quantity_total, 'sku' => $sku, 'asin' => $asin, 'qty_purchased' => $quantity_purchased, 'listing_id' => $listing->id );
			self::addHistory( $order_id, 'reduce_stock', $history_message, $history_details );

		}



		// mark listing as sold when last item is sold
		// if ( $quantity_total == 0 ) {
		// 	$wpdb->update( $wpdb->prefix.'amazon_listings', 
		// 		array( 'status' => 'sold', 'date_finished' => $data['date_created'], ), 
		// 		array( 'sku' => $sku ) 
		// 	);
		// 	WPLA()->logger->info( 'marked item '.$sku.' as SOLD ');
		// }


		## BEGIN PRO ##
		// reduce product stock - if enabled
		if ( get_option( 'wpla_sync_inventory' ) == '1' ) {

			// skip if no post_id set (imported products which have not yet been created in WooCommerce)
			if ( ! $listing->post_id ) {

				// add history record
				$history_message = "No product found for SKU $sku (".$listing->status.")";
				$history_details = array( 'sku' => $sku, 'asin' => $asin, 'id' => $listing->id );
				self::addHistory( $order_id, 'skipped_product', $history_message, $history_details );

				return false;				
			}

			if ( apply_filters( 'wpla_skip_quantity_sync', false, $listing, $sku, $asin ) ) {
                $history_message = 'Skipped product sync because of wpla_skip_quantity_sync';
                $history_details = array( 'sku' => $sku, 'asin' => $asin, 'id' => $listing->id );
			    self::addHistory( $order_id,'skipped_product', $history_message, $history_details );
			    return false;
            }

			// reduce product stock
			// $post_id = $wpdb->get_var( 'SELECT post_id FROM '.$wpdb->prefix.'amazon_listings WHERE asin = '.$asin );
			$post_id   = $listing->post_id;
			$parent_id = $listing->parent_id;
			$newstock = WPLA_ProductWrapper::decreaseStockBy( $post_id, $quantity_purchased, $order_id );
			WPLA()->logger->info( 'reduced product stock for #'.$post_id.' by '.$quantity_purchased.' - new qty: '.$newstock );

			// notify WP-Lister for eBay (and other plugins)
			do_action( 'wpla_inventory_status_changed', $post_id );
			if ( $parent_id ) do_action( 'wpla_inventory_status_changed', $parent_id ); // trigger stock update for parent variation as well

            // update other listings with the same post/parent ID of the stock update (using $skip_updating_feeds = true to improve performance)
            // (only if there are more than one account)
            if ( sizeof( WPLA()->accounts ) > 1 ) {
            	do_action( 'wpla_product_has_changed', $post_id, true );
            	if ( $parent_id ) do_action( 'wpla_product_has_changed', $parent_id, true );
            }

			// add history record
			$history_message = "Stock reduced by $quantity_purchased for product {$sku} (#$post_id) - new stock is $newstock";
			$history_details = array( 'product_id' => $post_id, 'parent_id' => $parent_id, 'newstock' => $newstock, 'qty_purchased' => $quantity_purchased );
			self::addHistory( $order_id, 'reduce_stock', $history_message, $history_details );

		}
		## END PRO ##

		return true;
	} // processListingItem()



	// add order history entry
	static function addHistory( $order_id, $action, $msg, $details = array(), $success = true ) {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLENAME;

		// build history record
		$record = new stdClass();
		$record->action  = $action;
		$record->msg     = $msg;
		$record->details = $details;
		$record->success = $success;
		$record->time    = time();

		// load history
		$history = $wpdb->get_var( "
			SELECT history
			FROM $table
			WHERE order_id = '$order_id'
		" );

		// init with empty array
		$history = maybe_unserialize( $history );
		if ( ! $history ) $history = array();

		// prevent fatal error if $history is not an array
		if ( ! is_array( $history ) ) {
			WPLA()->logger->error( "invalid history value in OrdersImporter::addHistory(): ".$history);

			// build history record
			$rec = new stdClass();
			$rec->action  = 'reset_history';
			$rec->msg     = 'Corrupted history data was cleared';
			$rec->details = array();
			$rec->success = 'ERROR';
			$rec->time    = time();

			$history = array();
			$history[] = $record;
		}

		// add record
		$history[] = $record;

		// update history
		$history = serialize( $history );
		$wpdb->query( "
			UPDATE $table
			SET history = '$history'
			WHERE order_id = '$order_id'
		" );

	}


	/*
	// decrease stock quantity for WooCommerce product
	static function decreaseStockBy( $post_id, $by, $VariationSpecifics = array(), $order_id = false ) {

		if ( count( $VariationSpecifics ) == 0 ) {
			$product = self::getProduct( $post_id );
		} else {
			$variation_id = self::findVariationID( $post_id, $VariationSpecifics );
			$product = self::getProduct( $variation_id, true );

			// add history record
			if ( $order_id ) {
				$om = new WPLA_OrdersModel();
				// $history_message = "Stock reduced by $by for variation #$variation_id";
				// $history_details = array( 'variation_id' => $variation_id );
				// $om->addHistory( $order_id, 'reduce_stock', $history_message, $history_details );			
			}

		}
		if ( ! $product ) return false;

		// patch backorders product config unless backorders were enabled in settings
		if ( $product->backorders_allowed() ) {
			if ( get_option( 'wpla_allow_backorders', 0 ) == 1 ) {
				$product->backorders = 'no';
			} elseif ( $order_id ) {
				$om = new WPLA_OrdersModel();
				// $history_message = "Warning: backorders are enabled for product #$post_id";
				// $history_details = array( 'post_id' => $post_id );
				// $om->addHistory( $order_id, 'backorders_allowed', $history_message, $history_details );							
			}
		}

		// check if stock management is enabled for product
		if ( $product->managing_stock() ) {		
			// if yes, call reduce_stock()
			$stock = $product->reduce_stock( $by );
		}

		// // check if stock management is enabled for product
		// if ( ! $product->managing_stock() && ! $product->backorders_allowed() ) {		
		// 	// if not, just mark it as out of stock
		// 	update_post_meta($product->id, '_stock_status', 'outofstock');
		// 	$stock = 0;
		// } else {
		// 	// if yes, call reduce_stock()
		// 	$stock = $product->reduce_stock( $by );
		// }

		return $stock;
	}	
	*/

	public function importOrders( $orders, $account ) {

		// $this->api     = new WPLA_AmazonAPI( $account->id );
		// $this->account = $account;

		foreach ( $orders as $order ) {
			$this->importOrder( $order, $account );
		}

	}

	public function importOrderItems( $items, $order_id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		// echo "<pre>";print_r($order_id);echo"</pre>";#die();
		// echo "<pre>";print_r($items);echo"</pre>";#die();

		$data = array(
			'items'			   => maybe_serialize( $items )
		);

		$wpdb->update( $table, $data, array( 'order_id' => $order_id ) );
		echo $wpdb->last_error;
	}

	function order_id_exists( $order_id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$id = $wpdb->get_var( "
			SELECT id
			FROM $table
			WHERE order_id = '$order_id'
		" );

		return $id;
	}

	// convert 2013-02-14T08:00:58.000Z to 2013-02-14 08:00:58
	public function convertIsoDateToSql( $iso_date ) {
		$search = array( 'T', '.000Z', 'Z' );
		$replace = array( ' ', '' );
		$sql_date = str_replace( $search, $replace, $iso_date );
		return $sql_date;
	}



}

