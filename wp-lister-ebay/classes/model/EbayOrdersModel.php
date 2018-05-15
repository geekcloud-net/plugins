<?php
/**
 * EbayOrdersModel class
 *
 * responsible for managing orders and talking to ebay
 * 
 */

class EbayOrdersModel extends WPL_Model {

	const TABLENAME = 'ebay_orders';

	var $_session;
	var $_cs;

	var $count_total    = 0;
	var $count_skipped  = 0;
	var $count_updated  = 0;
	var $count_inserted = 0;
	var $count_failed   = 0;
	var $report         = array();
	var $ModTimeTo      = false;
	var $ModTimeFrom    = false;
	var $NumberOfDays   = false;

	var $total_items;
	var $total_pages;
	var $current_page;
	var $current_lastdate;

	public function __construct() {
		parent::__construct();
		
		global $wpdb;
		$this->tablename = $wpdb->prefix . 'ebay_orders';
	}


	function updateOrders( $session, $days = false, $current_page = 1, $order_ids = false ) {
		WPLE()->logger->info('*** updateOrders('.$days.') - page '.$current_page);

		// this is a cron job if no number of days and no order IDs are requested
		$is_cron_job = $days == false && $order_ids == false ? true : false;

		$this->initServiceProxy($session);

		// set request handler
		$this->_cs->setHandler( 'OrderType', array( & $this, 'handleOrderType' ) );
		// $this->_cs->setHandler( 'PaginationResultType', array( & $this, 'handlePaginationResultType' ) );

		// build request
		$req = new GetOrdersRequestType();
		$req->setOrderRole( 'Seller' );
		// $req->setIncludeContainingOrder(true);

		// check if we need to calculate lastdate
		if ( $this->current_lastdate ) {
			$lastdate = $this->current_lastdate;
			WPLE()->logger->info('used current_lastdate from last run: '.$lastdate);
		} else {

			// period 30 days, which is the maximum allowed
			$now = time();
			$lastdate = $this->getDateOfLastOrder( $this->account_id );
			WPLE()->logger->info("getDateOfLastOrder( {$this->account_id} ) returned: ".$lastdate);
			if ($lastdate) $lastdate = mysql2date('U', $lastdate);

			// if last date is older than 30 days, fall back to default
			if ( $lastdate < $now - 3600 * 24 * 30 ) {
				WPLE()->logger->info('resetting lastdate - fall back default ');
				$lastdate = false;
			} 

		}

		// save lastdate for next page
		$this->current_lastdate = $lastdate;

		// fetch orders by IDs
		if ( is_array( $order_ids ) ) {
			$OrderIDArray = new OrderIDArrayType();
			foreach ( $order_ids as $id ) {
				$order = $this->getItem( $id );
				$OrderIDArray->addOrderID( $order['order_id'] );
			}
			$req->setOrderIDArray( $OrderIDArray );
		// parameter $days
		} elseif ( $days ) {
			$req->NumberOfDays  = $days;
			$this->NumberOfDays = $days;
			WPLE()->logger->info('NumberOfDays: '.$req->NumberOfDays);

		// default: orders since last change
		} elseif ( $lastdate ) {
			$req->ModTimeFrom  = gmdate( 'Y-m-d H:i:s', $lastdate );
			$req->ModTimeTo    = gmdate( 'Y-m-d H:i:s', time() );
			$this->ModTimeFrom = $req->ModTimeFrom;
			$this->ModTimeTo   = $req->ModTimeTo;
			WPLE()->logger->info('lastdate: '.$lastdate);
			WPLE()->logger->info('ModTimeFrom: '.$req->ModTimeFrom);
			WPLE()->logger->info('ModTimeTo: '.$req->ModTimeTo);

		// fallback: one day (max allowed by ebay: 30 days)
		} else {
			$days = 1;
			$req->NumberOfDays  = $days;
			$this->NumberOfDays = $days;
			WPLE()->logger->info('NumberOfDays (fallback): '.$req->NumberOfDays);
		}


		// $req->DetailLevel = $Facet_DetailLevelCodeType->ReturnAll;
		//if ( ! $this->is_ajax() ) $req->setDetailLevel('ReturnAll');
        // set DetailLevel to return all data to include external transactions
        $req->setDetailLevel('ReturnAll');

		// set pagination for first page
		$custom_page_size   = get_option( 'wplister_fetch_orders_page_size', 50 );
		$items_per_page     = $is_cron_job ? $custom_page_size : 100; // For GetOrders, the maximum value is 100 and the default value is 25 (which is too low in some rare cases)
		$this->current_page = $current_page;

		$Pagination = new PaginationType();
		$Pagination->setEntriesPerPage( $items_per_page );
		$Pagination->setPageNumber( $this->current_page );
		$req->setPagination( $Pagination );


		// get orders (single page)
		WPLE()->logger->info('fetching orders - page '.$this->current_page);
		$res = $this->_cs->GetOrders( $req );

		$this->total_pages = $res->PaginationResult->TotalNumberOfPages;
		$this->total_items = $res->PaginationResult->TotalNumberOfEntries;

		// get order with pagination helper (doesn't work as expected)
		// EbatNs_PaginationHelper($proxy, $callName, $request, $responseElementToMerge = '__COUNT_BY_HANDLER', $maxEntries = 200, $pageSize = 200, $initialPage = 1)
		// $helper = new EbatNs_PaginationHelper( $this->_cs, 'GetOrders', $req, 'OrderArray', 20, 10, 1);
		// $res = $helper->QueryAll();


		// handle response and check if successful
		if ( $this->handleResponse($res) ) {
			WPLE()->logger->info( "*** Orders updated successfully." );
			// WPLE()->logger->info( "*** PaginationResult:".print_r($res->PaginationResult,1) );
			// WPLE()->logger->info( "*** processed response:".print_r($res,1) );

			WPLE()->logger->info( "*** current_page : ".$this->current_page );
			WPLE()->logger->info( "*** total_pages  : ".$this->total_pages );
			WPLE()->logger->info( "*** total_items  : ".$this->total_items );

			WPLE()->logger->info( "** count_inserted: ".$this->count_inserted );
			WPLE()->logger->info( "** count_updated : ".$this->count_updated );
			WPLE()->logger->info( "** count_skipped : ".$this->count_skipped );
			WPLE()->logger->info( "** count_failed  : ".$this->count_failed );

			// fetch next page recursively - only in days mode, or if no new orders have been fetched yet
			if ( $res->HasMoreOrders && ( ! $is_cron_job || $this->count_inserted == 0 ) ) {
				$this->current_page++;
				$this->updateOrders( $session, $days, $this->current_page );
			}


		} else {
			WPLE()->logger->error( "Error on orders update".print_r( $res, 1 ) );			
		}
	}

	// function updateSingleOrder( $session, $id ) {

	// 	$this->initServiceProxy($session);

	// 	// get order item to update
	// 	$order = $this->getItem( $id );

	// 	// build request
	// 	$req = new GetItemOrdersRequestType();
	// 	$req->ItemID 		= $order['item_id'];
	// 	$req->OrderID = $order['order_id'];

	// 	WPLE()->logger->info('ItemID: '.$req->ItemID);
	// 	WPLE()->logger->info('OrderID: '.$req->OrderID);

	// 	// $req->DetailLevel = $Facet_DetailLevelCodeType->ReturnAll;
	// 	$req->setDetailLevel('ReturnAll');
	// 	$req->setIncludeContainingOrder(true);

	// 	// download the data
	// 	$res = $this->_cs->GetItemOrders( $req );

	// 	// handle response and check if successful
	// 	if ( $this->handleResponse($res) ) {

	// 		// since GetItemOrders returns the Item object outside of the Order object, 
	// 		// we need to rearrange it before we pass it to handleOrderType()
	// 		$Order = $res->OrderArray[0];
	// 		$Order->Item = $res->Item;
	// 		$this->handleOrderType( 'OrderType', $Order );

	// 		WPLE()->logger->info( sprintf("Order %s updated successfully.", $req->OrderID ) );
	// 	} else {
	// 		WPLE()->logger->error( "Error on orders update".print_r( $res, 1 ) );			
	// 	}
	// }

	// function handlePaginationResultType( $type, $Detail ) {
	// 	//#type $Detail PaginationResultType
	// 	$this->total_pages = $Detail->TotalNumberOfPages;
	// 	$this->total_items = $Detail->TotalNumberOfEntries;
	// 	WPLE()->logger->info( 'handlePaginationResultType()'.print_r( $Detail, 1 ) );
	// }

	function handleOrderType( $type, $Detail ) {
		//global $wpdb;
		//#type $Detail OrderType
		// WPLE()->logger->info( 'handleOrderType()'.print_r( $Detail, 1 ) );

		// map OrderType to DB columns
		$data = $this->mapItemDetailToDB( $Detail );
		if (!$data) return true;
		// WPLE()->logger->info( 'handleOrderType() mapped data: '.print_r( $data, 1 ) );

		$this->insertOrUpdate( $data, $Detail );

		// this will remove item from result
		return true;
	}

	function insertOrUpdate( $data, $Detail ) {
		global $wpdb;

		// try to get existing order by order id
		$order = $this->getOrderByOrderID( $data['order_id'] );

		if ( $order ) {

            // extract the ShippedTime
            $item_details = maybe_unserialize( $data['details'] );
            if ( $item_details ) {
                $shipped_time = self::convertEbayDateToSql( $item_details->ShippedTime );
                if ( $shipped_time ) $data['ShippedTime'] = $shipped_time;
            }

			// update existing order
			WPLE()->logger->info( 'update order #' . $data['order_id'] . ' - LastTimeModified: ' . $data['LastTimeModified'] );
			$result = $wpdb->update( $this->tablename, $data, array( 'order_id' => $data['order_id'] ) );
			if ( $result === false ) {
				WPLE()->logger->error( 'failed to update order - MySQL said: '.$wpdb->last_error );
				wple_show_message( 'Failed to update order #'.$data['order_id'].' - MySQL said: '.$wpdb->last_error, 'error' );
			}
			$insert_id = $order['id'];

	        /*** ## BEGIN PRO ## ***/

			// create or update order from order - if enabled
	        $this->createOrUpdateWooCommerceOrder( $insert_id, $data );

	        /*** ## END PRO ## ***/

			$this->addToReport( 'updated', $data );
		
		} else {
		
			// create new order
            // extract the ShippedTime
            $item_details = maybe_unserialize( $data['details'] );
            if ( $item_details ) {
                $shipped_time = self::convertEbayDateToSql( $item_details->ShippedTime );
                if ( $shipped_time ) $data['ShippedTime'] = $shipped_time;
            }

			WPLE()->logger->info( 'insert order #' . $data['order_id'] . ' - LastTimeModified: ' . $data['LastTimeModified'] );
			$result = $wpdb->insert( $this->tablename, $data );
			if ( $result === false ) {
				WPLE()->logger->error( 'insert order failed - MySQL said: '.$wpdb->last_error );
				$this->addToReport( 'error', $data, false, $wpdb->last_error );
				wple_show_message( 'Failed to insert order #'.$data['order_id'].' - MySQL said: '.$wpdb->last_error, 'error' );
				return false;
			}
			$Details       = maybe_unserialize( $data['details'] );
			$order_post_id = false;
			$insert_id     = $wpdb->insert_id;
			// WPLE()->logger->info( 'insert_id: '.$insert_id );

			// process order line items
			$tm = new TransactionsModel();
			foreach ( $Details->TransactionArray as $Transaction ) {

				// avoid empty transaction id (auctions)
				$transaction_id = $Transaction->TransactionID;
				if ( intval( $transaction_id ) == 0 ) {
					// use negative OrderLineItemID to separate from real TransactionIDs
					$transaction_id = 0 - str_replace('-', '', $Transaction->OrderLineItemID);
				}

				// check if we already processed this TransactionID
				if ( $existing_transaction = $tm->getTransactionByTransactionID( $transaction_id ) ) {

					// add history record
					$history_message = "Skipped already processed transaction {$transaction_id}";
					$history_details = array( 'ebay_id' => $ebay_id );
					$this->addHistory( $data['order_id'], 'skipped_transaction', $history_message, $history_details );

					// TODO: optionally update transaction to reflect correct CompleteStatus etc. - like so:
					// $tm->updateTransactionFromEbayOrder( $data, $Transaction );

					// skip processing listing items
					continue;
				}

				// check if item has variation 
				$hasVariations = false;
				$VariationSpecifics = array();
		        if ( is_object( @$Transaction->Variation ) ) {
					foreach ($Transaction->Variation->VariationSpecifics as $spec) {
		                $VariationSpecifics[ $spec->Name ] = $spec->Value[0];
		            }
					$hasVariations = true;
		        } 

				// update listing sold quantity and status
				$this->processListingItem( $data['order_id'], $Transaction->Item->ItemID, $Transaction->QuantityPurchased, $data, $VariationSpecifics, $Transaction );

				// create transaction record for future reference
				$tm->createTransactionFromEbayOrder( $data, $Transaction );
			}


	        /*** ## BEGIN PRO ## ***/

			// create order from order - if enabled
	        $this->createOrUpdateWooCommerceOrder( $insert_id, $data );

	        /*** ## END PRO ## ***/

			$this->addToReport( 'inserted', $data, $order_post_id );

		}

	} // insertOrUpdate()


    /*** ## BEGIN PRO ## ***/
	// create or update WooCommere order
	function createOrUpdateWooCommerceOrder( $order_id, $data ) {
		global $wpdb;

		// check if order creation is enabled
		if ( get_option( 'wplister_create_orders' ) == '0' ) return;

		// maybe skip orders containing only foreign items
		if ( get_option( 'wplister_skip_foreign_item_orders' ) ) {

			// check if order line items exists in WP-Lister			
			$order_items     = maybe_unserialize( $data['items'] );
			$has_known_items = false;
			foreach ( $order_items as $item ) {
				$listing_id = $wpdb->get_var( $wpdb->prepare("SELECT id FROM {$wpdb->prefix}ebay_auctions WHERE ebay_id = %s", $item['item_id'] ) );
				if ( $listing_id ) $has_known_items = true;
			}

			// skip if order contains no known items
			if ( ! $has_known_items ) {
				// $history_message = "Not creating order in WooCommerce - no known items found"; // don't add history row - or check first whether it was already added!
				// $this->addHistory( $data['order_id'], 'skipped_create_order', $history_message, array() );
				WPLE()->logger->info( 'No known items found, skipped creating order in WooCommerce - order id #'.$data['order_id'] );	
				return;
			}

		}

		// check if WooCommerce order already exists
		// TODO: check if order has been deleted or moved to trash
		$ordersModel   = new EbayOrdersModel();		
		$ebay_order    = $ordersModel->getItem( $order_id ); 
		$ebay_order_id = $data['order_id']; // eg. 161100001960-1007900745006
		// $woo_order_exists = $ebay_order['post_id'] != '' ? true : false;

		WPLE()->logger->info( 'checking if WooCommerce order exists for eBay order '.$ebay_order_id.' - order_id: '.$order_id );				
		$woo_order_exists = false;
		$debug_log        = '';

		// first check if an order exists for the stored post_id
		if ( $ebay_order['post_id'] ) {
			$debug_log .= "check 1: check if order id exists: ".$ebay_order['post_id']."\n";
			WPLE()->logger->info( 'found possible woo order #'.$ebay_order['post_id'].' by reference' );
			if ( self::wooOrderExists( $ebay_order['post_id'] ) ) {
				$woo_order_exists = true;
				$post_id = $ebay_order['post_id'];
				WPLE()->logger->info( 'found existing woo order #'.$post_id.' by reference' );
				$debug_log .= "check 1: order $post_id exists \n";
				// echo "<pre>found order: ";print_r($post_id);echo"</pre>";#die();				
			}
		};

		// if nothing found, check for an order with the same _ebay_order_id 
		if ( ! $woo_order_exists ) {
			$debug_log .= "check 2: search for order in wp_postmeta: $ebay_order_id \n";

			$post_id = $wpdb->get_var( $wpdb->prepare("
				SELECT post_id FROM {$wpdb->prefix}postmeta
				WHERE meta_key   = '_ebay_order_id'
				  AND meta_value = %s
				ORDER BY post_id ASC
			", $ebay_order_id ) );
			$debug_log .= "check 2: found order $post_id \n";
			WPLE()->logger->info( 'found possible woo order #'.$post_id.' by ebay_order_id '.$ebay_order_id );
			if ( is_numeric( $post_id ) && self::wooOrderExists( $post_id ) ) {
				$woo_order_exists = true;
				WPLE()->logger->info( 'found existing woo order #'.$post_id.' by ebay_order_id '.$ebay_order_id );
				$debug_log .= "check 2: order $post_id exists \n";
				// echo "<pre>found order post_id by ebay_order_id: ";print_r($post_id);echo"</pre>";#die();				
			}
		}

		// if still nothing found, check for an order with the same _ebay_transaction_id 
		if ( ! $woo_order_exists ) {
			$debug_log .= "check 3: still nothing found... \n";

			// check if ebay_order_id has the format ItemID-TransactionID (is not a combined order)
			if ( strpos( $ebay_order_id, '-' ) ) {
				list( $ebay_item_id, $ebay_transaction_id ) = explode( '-', $ebay_order_id );
				$debug_log .= "check 3: search for order in wp_postmeta by transaction_id: $ebay_transaction_id \n";
	
				$post_id = $wpdb->get_var( $wpdb->prepare("
					SELECT post_id FROM {$wpdb->prefix}postmeta
					WHERE meta_key   = '_ebay_transaction_id'
					  AND meta_value = %s
					ORDER BY post_id ASC
				", $ebay_transaction_id ) );
				$debug_log .= "check 3: found order $post_id \n";
				WPLE()->logger->info( 'found possible woo order #'.$post_id.' by ebay_transaction_id '.$ebay_transaction_id );

				if ( is_numeric( $post_id ) && self::wooOrderExists( $post_id ) ) {
					$woo_order_exists = true;
					WPLE()->logger->info( 'found existing woo order #'.$post_id.' by ebay_transaction_id '.$ebay_transaction_id );
					$debug_log .= "check 3: order $post_id exists \n";
					// echo "<pre>found order post_id by ebay_transaction_id: ";print_r($post_id);echo"</pre>";#die();				
				}

			}
		}


		// update or create
		if ( $woo_order_exists ) {

			// if we found a post_id by now but $ebay_order['post_id'] is empty or different, let's update the order record
			if ( ! $ebay_order['post_id'] || $ebay_order['post_id'] != $post_id )
				$this->updateWpOrderID( $order_id, $post_id );

			// update order from order
			$wob = new WPL_WooOrderBuilder();
			$order_post_id = $wob->updateOrderFromEbayOrder( $order_id, $post_id );
			WPLE()->logger->info( 'updated order #'.$order_post_id.' - from ebay order id #'.$data['order_id'] );

		} else {
			// check if order is completed - and if it matters
			if ( $data['CompleteStatus'] != 'Completed') {
				if ( get_option( 'wplister_create_incomplete_orders', 0 ) == 0 ) {
					WPLE()->logger->info( 'skipped order creation as status is '.$data['CompleteStatus'].' - order id #'.$data['order_id'] );
					return;
				}
			}

			$debug_log .= "summary: order $order_id does not exist! \n";
			if ( isset( $post_id ) ) 
				$debug_log .= "note: an invalid post_id $post_id was found, but did not exist. \n";

			// create order from order
			$wob = new WPL_WooOrderBuilder();
			$order_post_id = $wob->createWooOrderFromEbayOrder( $order_id );
			WPLE()->logger->info( 'created woo order #'.$order_post_id.' - from ebay order id #'.$data['order_id'] );

			// add history record
			$history_message = "Order #$order_post_id was created";
			$history_details = array( 'post_id' => $order_post_id, 'ebay_order_id' => $ebay_order_id );
			$history_details['debug_log'] = $debug_log; 							 // debug data
			$this->addHistory( $data['order_id'], 'create_order', $history_message, $history_details );

		}

	} // createOrUpdateWooCommerceOrder()

    /*** ## END PRO ## ***/


	// check if woocommcer order exists and has not been moved to the trash
	static function wooOrderExists( $post_id ) {

        $_order = wc_get_order( $post_id );

		if ( $_order ) {

			if ( $_order->get_status() == 'trash' ) return false;

			return wple_get_order_meta( $_order, 'id' );

		}

		return false;
	} // wooOrderExists()


	// update listing sold quantity and status
	function processListingItem( $order_id, $ebay_id, $quantity_purchased, $data, $VariationSpecifics, $Transaction ) {
		global $wpdb;
		$has_been_replenished = false;

		// check if this listing exists in WP-Lister
        $listing_id = $wpdb->get_var( $wpdb->prepare("SELECT id FROM {$wpdb->prefix}ebay_auctions WHERE ebay_id = %s", $ebay_id ) );

        if ( ! $listing_id && get_option( 'wplister_match_sales_by_sku', 0 ) == 1 ) {
            // If no listing is found using the eBay Item ID, check if we need to match using the SKU
            $listingItem = WPLE_ListingQueryHelper::findItemBySku( $Transaction->Item->SKU );

            if ( $listingItem ) {
                $listing_id = $listingItem->id;

                $history_message = "Matched SKU ({$Transaction->Item->SKU}) to Listing #$listing_id";
                $history_details = array( 'ebay_id' => $ebay_id );
                $this->addHistory( $order_id, 'match_sku', $history_message, $history_details );
            }
        }

        if ( ! $listing_id ) {
            $history_message = "Skipped foreign item #{$ebay_id}";
            $history_details = array( 'ebay_id' => $ebay_id );
            $this->addHistory( $order_id, 'skipped_item', $history_message, $history_details );
            return;
        }

		// get current values from db
		$quantity_total = $wpdb->get_var( $wpdb->prepare("SELECT quantity      FROM {$wpdb->prefix}ebay_auctions WHERE id = %s", $listing_id ) );
		$quantity_sold  = $wpdb->get_var( $wpdb->prepare("SELECT quantity_sold FROM {$wpdb->prefix}ebay_auctions WHERE id = %s", $listing_id ) );

		// increase the listing's quantity_sold
		$quantity_sold = $quantity_sold + $quantity_purchased;
		$wpdb->update( $wpdb->prefix.'ebay_auctions', 
			array( 'quantity_sold' => $quantity_sold ), 
			array( 'id' => $listing_id )
		);

		// add history record
		$history_message = "Sold quantity increased by $quantity_purchased for listing #{$listing_id} ({$ebay_id}) - sold $quantity_sold";
		$history_details = array( 'listing_id' => $listing_id, 'ebay_id' => $ebay_id, 'quantity_sold' => $quantity_sold, 'quantity_total' => $quantity_total );
		$this->addHistory( $order_id, 'reduce_stock', $history_message, $history_details );


        /*** ## BEGIN PRO ## ***/

		// reduce product stock - if enabled
		if ( get_option( 'wplister_handle_stock' ) == '1' ) {

            if ( get_option( 'wplister_match_sales_by_sku', 0 ) == 1 ) {
                // Get local product from the listing's SKU
                $listingItem = WPLE_ListingQueryHelper::findItemBySku( $Transaction->Item->SKU );

                if ( $listingItem ) {
                    $post_id = $listingItem->post_id;

                    // add history record
                    $history_message = "Found product #$post_id for eBay SKU ". $Transaction->Item->SKU;
                    $history_details = array( 'product_id' => $post_id, 'ebay_id' => $ebay_id, 'sku' => $Transaction->Item->SKU );
                    $this->addHistory( $order_id, 'product_query_result', $history_message, $history_details );
                }
            } else {
                // get post_id for listing by ebay_id
                $post_id = $wpdb->get_var( $wpdb->prepare("SELECT post_id FROM {$wpdb->prefix}ebay_auctions WHERE ebay_id = %s", $ebay_id ) );

                // add history record
                $history_message = "Found product #$post_id for eBay item $ebay_id";
                $history_details = array( 'product_id' => $post_id, 'ebay_id' => $ebay_id );
                $this->addHistory( $order_id, 'product_query_result', $history_message, $history_details );
            }

			// handle variations
			if ( sizeof( $VariationSpecifics ) > 0 ) {
				$VariationSKU = $Transaction->Variation->SKU;
				$variation_id = ProductWrapper::findVariationID( $post_id, $VariationSpecifics, $VariationSKU );
			} else {
				$VariationSKU = null;
				$variation_id = null;
			}

			// add history record - track original stock level
			$actual_post_id  = $variation_id ? $variation_id : $post_id;
			$oldstock_before = ProductWrapper::getStock( $actual_post_id );
			$history_message = "Current stock level for product #$actual_post_id: $oldstock_before";
			$history_details = array( 'product_id' => $post_id, 'variation_id' => $variation_id, 'oldstock' => $oldstock_before );
			$this->addHistory( $order_id, 'check_stock_before', $history_message, $history_details );

			// reduce product stock
			$newstock = ProductWrapper::decreaseStockBy( $post_id, $quantity_purchased, $order_id, $variation_id );
			WPLE()->logger->info( 'reduced product stock for #'.$post_id.' / '.$variation_id.' by '.$quantity_purchased.' - new qty: '.$newstock );
			do_action( 'wple_product_stock_decreased', $post_id, $quantity_purchased, $order_id, $variation_id );

			// add history record (detailed)
			$history_message = "Stock reduced by $quantity_purchased for product #$post_id ($actual_post_id) - new stock is $newstock";
			$history_details = array( 
				'product_id'         => $post_id, 
				'variation_id'       => $variation_id, 
				'post_id'            => $actual_post_id, 
				'newstock'           => $newstock, 
				'quantity_purchased' => $quantity_purchased, 
				'VariationSpecifics' => $VariationSpecifics, 
				'VariationSKU'       => $VariationSKU );
			$this->addHistory( $order_id, 'reduce_stock', $history_message, $history_details );

			// notify WP-Lister for Amazon (and other plugins)
			do_action( 'wplister_inventory_status_changed', $post_id );

			// add history record - double check new stock level
			$newstock_checked = ProductWrapper::getStock( $actual_post_id );
			$history_message = "Verified new stock for product #$actual_post_id: $newstock_checked";
			$history_details = array( 'product_id' => $post_id, 'variation_id' => $variation_id, 'newstock' => $newstock_checked );
			$this->addHistory( $order_id, 'check_stock_after', $history_message, $history_details );

		} else {

			// add history record - sync sales is disabled
			$history_message = "Synchronize sales is disabled - stock will not be reduced";
			$this->addHistory( $order_id, 'inventory_sync_off', $history_message, array() );

		}


		$listing         = ListingsModel::getItem( $listing_id );
		$post_id         = $listing['post_id'];
		$profile_details = $listing['profile_data']['details'];

		// flag to make sure that the wplister_revise_inventory_status is called only once
        $revise_status_called = false;

		// handle auto_replenish option
		if ( isset( $profile_details['auto_replenish'] ) && $profile_details['auto_replenish'] ) {

	        // get max_quantity from profile
    	    $max_quantity = ( isset( $profile_details['max_quantity'] ) && intval( $profile_details['max_quantity'] )  > 0 ) ? $profile_details['max_quantity'] : false ; 

    	    // revise inventory status to replenish stock on eBay
    	    if ( $max_quantity ) {

				do_action( 'wplister_revise_inventory_status', $post_id );
				$revise_status_called = true;
				WPLE()->logger->info( 'stock level replenished for product '.$post_id.'');

				// add history record
				$history_message = "Stock level replenished for product #$post_id - max qty: $max_quantity";
				$history_details = array( 'product_id' => $post_id, 'max_quantity' => $max_quantity );
				$this->addHistory( $order_id, 'replenish_stock', $history_message, $history_details );

				// prevent listing from being marked as sold
				$quantity_total += $quantity_purchased;
				$has_been_replenished = true;
    	    }

		} // if auto_replenish

        // make sure wplister_revise_inventory_status is called for products
        // that are listed on multiple ebay sites
        if ( ! $revise_status_called ) {
            $product_listings = WPLE_ListingQueryHelper::getAllListingsFromPostID( $post_id );

            if ( count( $product_listings ) > 1 ) {
                WPLE()->logger->info('Revising inventory status for listings linked to #'. $post_id);
                WPLE()->logger->info( print_r( $product_listings, true ) );
                do_action( 'wplister_revise_inventory_status', $post_id );
                WPLE()->logger->info( 'stock level revised for product ' . $post_id );
            }
        }

        /*** ## END PRO ## ***/

		// mark listing as sold when last item is sold - unless Out Of Stock Control (oosc) is enabled
        if ( ! ListingsModel::thisAccountUsesOutOfStockControl( $data['account_id'] ) ) {
			if ( $quantity_sold == $quantity_total && ! $has_been_replenished ) {

                // make sure this product is out of stock before we mark listing as sold - free version excluded
                $listing_item = ListingsModel::getItem( $listing_id );
                if ( WPLISTER_LIGHT || ListingsModel::checkStockLevel( $listing_item ) == false ) {

					$wpdb->update( $wpdb->prefix.'ebay_auctions', 
						array( 'status' => 'sold', 'date_finished' => $data['date_created'], ), 
						array( 'ebay_id' => $ebay_id ) 
					);
					WPLE()->logger->info( 'marked item #'.$ebay_id.' as SOLD ');

				}
			}
        }

	} // processListingItem()


	// add order history entry
	function addHistory( $order_id, $action, $msg, $details = array(), $success = true ) {
		global $wpdb;

		// build history record
		$record = new stdClass();
		$record->action  = $action;
		$record->msg     = $msg;
		$record->details = $details;
		$record->success = $success;
		$record->time    = time();

		// load history
		$history = $wpdb->get_var( $wpdb->prepare("
			SELECT history
			FROM $this->tablename
			WHERE order_id = %s
		", $order_id ) );

		// init with empty array
		$history = maybe_unserialize( $history );
		if ( ! $history ) $history = array();

		// prevent fatal error if $history is not an array
		if ( ! is_array( $history ) ) {
			WPLE()->logger->error( "invalid history value in EbayOrdersModel::addHistory(): ".$history);

			// build history record
			$rec = new stdClass();
			$rec->action  = 'reset_history';
			$rec->msg     = 'Corrupted history data was cleared';
			$rec->details = array();
			$rec->success = false;
			$rec->time    = time();

			$history = array();
			$history[] = $record;
		}

		// add record
		$history[] = $record;

		// update history
		$history = serialize( $history );
		$wpdb->query( $wpdb->prepare("
			UPDATE $this->tablename
			SET history    = %s
			WHERE order_id = %s
		", $history, $order_id ) );

	}

	function mapItemDetailToDB( $Detail ) {
		//#type $Detail OrderType

		$data['date_created']              = self::convertEbayDateToSql( $Detail->CreatedTime );
		$data['LastTimeModified']          = self::convertEbayDateToSql( $Detail->CheckoutStatus->LastModifiedTime );

		$data['order_id']            	   = $Detail->OrderID;
		$data['total']                     = $Detail->Total->value;
		$data['currency']                  = $Detail->Total->attributeValues['currencyID'];
		$data['buyer_userid']              = $Detail->BuyerUserID;

		$data['CompleteStatus']            = $Detail->OrderStatus;
		$data['eBayPaymentStatus']         = $Detail->CheckoutStatus->eBayPaymentStatus;
		$data['PaymentMethod']             = $Detail->CheckoutStatus->PaymentMethod;
		$data['CheckoutStatus']            = $Detail->CheckoutStatus->Status;

		$data['ShippingService']           = $Detail->ShippingServiceSelected->ShippingService;
		$data['ShippingAddress_City']      = $Detail->ShippingAddress->CityName;
		$data['buyer_name']                = $Detail->Buyer->RegistrationAddress->Name;
		$data['buyer_email']               = $Detail->TransactionArray[0]->Buyer->Email;

		$data['site_id']    	 		   = $this->site_id;
		$data['account_id']    	 		   = $this->account_id;

		// use buyer name from shipping address if registration address is empty
		if ( $data['buyer_name'] == '' ) {
			$data['buyer_name'] = $Detail->ShippingAddress->Name;
		}

		// process transactions / items
		$items = array();
		foreach ( $Detail->TransactionArray as $Transaction ) {
			$VariationSpecifics = false;
			$sku = $Transaction->Item->SKU;

			// process variation details
			if ( is_object( @$Transaction->Variation ) ) {
				$VariationSpecifics = array();
				$sku = $Transaction->Variation->SKU;

				if ( is_array($Transaction->Variation->VariationSpecifics) )
				foreach ( $Transaction->Variation->VariationSpecifics as $varspec ) {
					$attribute_name  = $varspec->Name;
					$attribute_value = $varspec->Value[0];
					$VariationSpecifics[ $attribute_name ] = $attribute_value;
				}
			}

			$newitem = array();
			$newitem['item_id']            = $Transaction->Item->ItemID;
			$newitem['title']              = $Transaction->Item->Title;
			$newitem['sku']                = $sku;
			$newitem['quantity']           = $Transaction->QuantityPurchased;
			$newitem['transaction_id']     = $Transaction->TransactionID;
			$newitem['OrderLineItemID']    = $Transaction->OrderLineItemID;
			$newitem['TransactionPrice']   = $Transaction->TransactionPrice->value;
			$newitem['VariationSpecifics'] = $VariationSpecifics;
			$items[] = $newitem;
			// echo "<pre>";print_r($Transaction);echo"</pre>";die();
		}
		$data['items'] = serialize( $items );


		// maybe skip orders from foreign sites
		if ( get_option( 'wplister_skip_foreign_site_orders' ) ) {

			// get WP-Lister eBay site
			$ebay_sites	   = EbayController::getEbaySites();
			$wplister_site = $ebay_sites[ get_option( 'wplister_ebay_site_id' ) ];

			// check if sites match - skip if they don't
			if ( $Transaction->TransactionSiteID != $wplister_site ) {
				WPLE()->logger->info( "skipped order #".$Detail->OrderID." from foreign site #".$Detail->Item->Site." / ".$Transaction->TransactionSiteID );			
				$this->addToReport( 'skipped', $data );
				return false;						
			}

		}

		// skip orders that are older than the oldest order in WP-Lister / when WP-Lister was first connected to eBay
		if ( $first_order_date_created_ts = $this->getDateOfFirstOrder() ) {

			// convert to timestamps
			$this_order_date_created_ts = strtotime( $data['date_created'] );

			// skip if order date is older
			if ( $this_order_date_created_ts < $first_order_date_created_ts ) {
				WPLE()->logger->info( "skipped old order #".$Detail->OrderID." created at ".$data['date_created'] );			
				WPLE()->logger->info( "timestamps: $this_order_date_created_ts / ".gmdate('Y-m-d H:i:s',$this_order_date_created_ts)." (order)  <  $first_order_date_created_ts ".gmdate('Y-m-d H:i:s',$first_order_date_created_ts)." (ref)" );			
				$this->addToReport( 'skipped', $data );
				return false;						
			}

		}


        // save GetOrders reponse in details
		$data['details'] = self::encodeObject( $Detail );

		WPLE()->logger->info( "IMPORTING order #".$Detail->OrderID );							

		return $data;
	} // mapItemDetailToDB()


	function addToReport( $status, $data, $wp_order_id = false, $error = false ) {

		$rep = new stdClass();
		$rep->status           = $status;
		$rep->order_id         = $data['order_id'];
		$rep->date_created     = $data['date_created'];
		$rep->OrderLineItemID  = $data['OrderLineItemID'];
		$rep->LastTimeModified = $data['LastTimeModified'];
		$rep->total            = $data['total'];
		$rep->data             = $data;
		// $rep->newstock         = $newstock;
		$rep->wp_order_id      = $wp_order_id;
		$rep->error            = $error;

		$this->report[] = $rep;

		switch ($status) {
			case 'skipped':
				$this->count_skipped++;
				break;
			case 'updated':
				$this->count_updated++;
				break;
			case 'inserted':
				$this->count_inserted++;
				break;
			case 'error':
			case 'failed':
				$this->count_failed++;
				break;
		}
		$this->count_total++;

	}

	function getHtmlTimespan() {
		if ( $this->NumberOfDays ) {
			return sprintf( __('the last %s days','wplister'), $this->NumberOfDays );
		} elseif ( $this->ModTimeFrom ) {
			return sprintf( __('from %s to %s','wplister'), $this->ModTimeFrom , $this->ModTimeTo );
		}
	}

	function getHtmlReport() {

		$html  = '<div class="ebay_order_report" style="display:none">';
		$html .= '<br>';
		$html .= __('New orders created','wplister') .': '. $this->count_inserted .' '. '<br>';
		$html .= __('Existing orders updated','wplister')  .': '. $this->count_updated  .' '. '<br>';
		if ( $this->count_skipped ) $html .= __('Old or foreign orders skipped','wplister')  .': '. $this->count_skipped  .' '. '<br>';
		if ( $this->count_failed ) $html .= __('Orders failed to create','wplister')  .': '. $this->count_failed  .' '. '<br>';
		$html .= '<br>';

		if ( $this->count_skipped ) $html .= __('Note: Orders from foreign eBay sites were skipping during update.','wplister') . '<br><br>';

		$html .= '<table style="width:99%">';
		$html .= '<tr>';
		$html .= '<th align="left">'.__('Last modified','wplister').'</th>';
		$html .= '<th align="left">'.__('Order ID','wplister').'</th>';
		$html .= '<th align="left">'.__('Action','wplister').'</th>';
		$html .= '<th align="left">'.__('Total','wplister').'</th>';
		// $html .= '<th align="left">'.__('Title','wplister').'</th>';
		$html .= '<th align="left">'.__('Buyer ID','wplister').'</th>';
		$html .= '<th align="left">'.__('Date created','wplister').'</th>';
		$html .= '</tr>';
		
		foreach ($this->report as $item) {
			$html .= '<tr>';
			$html .= '<td>'.$item->LastTimeModified.'</td>';
			$html .= '<td>'.$item->order_id.'</td>';
			$html .= '<td>'.$item->status.'</td>';
			$html .= '<td>'.$item->total.'</td>';
			// $html .= '<td>'.@$item->data['item_title'].'</td>';
			$html .= '<td>'.@$item->data['buyer_userid'].'</td>';
			$html .= '<td>'.$item->date_created.'</td>';
			$html .= '</tr>';
			if ( $item->error ) {
				$html .= '<tr>';
				$html .= '<td colspan="7" style="color:darkred;">ERROR: '.$item->error.'</td>';
				$html .= '</tr>';			
			}
		}

		$html .= '</table>';
		$html .= '</div>';
		return $html;
	}

	/* the following methods could go into another class, since they use wpdb instead of EbatNs_DatabaseProvider */

	function getAll() {
		global $wpdb;
		$items = $wpdb->get_results( "
			SELECT *
			FROM $this->tablename
			ORDER BY id DESC
		", ARRAY_A );

		return $items;
	}

	function getItem( $id ) {
		global $wpdb;

		$item = $wpdb->get_row( $wpdb->prepare("
			SELECT *
			FROM $this->tablename
			WHERE id = %s
		", $id 
		), ARRAY_A );

		// decode OrderType object with eBay classes loaded
		$item['details'] = self::decodeObject( $item['details'], false, true );
		$item['history'] = maybe_unserialize( $item['history'] );
		$item['items']   = maybe_unserialize( $item['items'] );

		return $item;
	}

	static function getWhere( $column, $value ) {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;

		$items = $wpdb->get_results( $wpdb->prepare("
			SELECT *
			FROM $table
			WHERE $column = %s
		", $value 
		), OBJECT_K);		

		return $items;
	}

	function getOrderByOrderID( $order_id ) {
		global $wpdb;

		$order = $wpdb->get_row( $wpdb->prepare("
			SELECT *
			FROM $this->tablename
			WHERE order_id = %s
		", $order_id 
		), ARRAY_A );

		return $order;
	}
	function getAllOrderByOrderID( $order_id ) {
		global $wpdb;

		$order = $wpdb->get_results( $wpdb->prepare("
			SELECT *
			FROM $this->tablename
			WHERE order_id = %s
		", $order_id 
		), ARRAY_A );

		return $order;
	}

	function getOrderByPostID( $post_id ) {
		global $wpdb;

		$order = $wpdb->get_row( $wpdb->prepare("
			SELECT *
			FROM $this->tablename
			WHERE post_id = %s
		", $post_id 
		), ARRAY_A );

		return $order;
	}

	function getAllDuplicateOrders() {
		global $wpdb;	
		$items = $wpdb->get_results("
			SELECT order_id, COUNT(*) c
			FROM $this->tablename
			GROUP BY order_id 
			HAVING c > 1
		", OBJECT_K);		

		if ( ! empty($items) ) {
			$order = array();
			foreach ($items as &$item) {
				$orders[] = $item->order_id;
			}
			$items = $orders;
		}

		return $items;		
	}

	// get the newest modification date of all orders in WP-Lister
	function getDateOfLastOrder( $account_id ) {
		global $wpdb;
		$lastdate = $wpdb->get_var( $wpdb->prepare("
			SELECT LastTimeModified
			FROM $this->tablename
			WHERE account_id = %s
			ORDER BY LastTimeModified DESC LIMIT 1
		", $account_id ) );

		// if there are no orders yet, check the date of the last transaction
		if ( ! $lastdate ) {
			$tm = new TransactionsModel();
			$lastdate = $tm->getDateOfLastCreatedTransaction( $account_id );
			if ($lastdate) {
				// add two minutes to prevent importing the same transaction again
				$lastdate = mysql2date('U', $lastdate) + 120;
				$lastdate = gmdate('Y-m-d H:i:s', $lastdate );
			}
		}
		return $lastdate;
	}

	// get the creation date of the oldest order in WP-Lister - as unix timestamp
	function getDateOfFirstOrder() {
		global $wpdb;

		// regard ignore_orders_before_ts timestamp if set
		if ( $ts = get_option('ignore_orders_before_ts') ) {
			WPLE()->logger->info( "getDateOfFirstOrder() - using ignore_orders_before_ts: $ts (raw)");
			return $ts;
		}

		$date = $wpdb->get_var( "
			SELECT date_created
			FROM $this->tablename
			ORDER BY date_created ASC LIMIT 1
		" );

		return strtotime($date);
	}

	function deleteItem( $id ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare("
			DELETE
			FROM $this->tablename
			WHERE id = %s
		", $id ) );
	}

	function updateWpOrderID( $id, $wp_order_id ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare("
			UPDATE $this->tablename
			SET post_id = %s
			WHERE id    = %s
		", $wp_order_id, $id ) );
		echo $wpdb->last_error;
	}

	function getStatusSummary() {
		global $wpdb;
		$result = $wpdb->get_results("
			SELECT CompleteStatus, count(*) as total
			FROM $this->tablename
			GROUP BY CompleteStatus
		");

		$summary = new stdClass();
		foreach ($result as $row) {
			$CompleteStatus = $row->CompleteStatus;
			$summary->$CompleteStatus = $row->total;
		}

		// count total items as well
		$total_items = $wpdb->get_var("
			SELECT COUNT( id ) AS total_items
			FROM $this->tablename
		");
		$summary->total_items = $total_items;

        // Shipped and Unshipped
        $total_items = $wpdb->get_var("
			SELECT COUNT( o.id ) AS total_items
			FROM $this->tablename o
			WHERE ShippedTime <> '' 
		");
        $summary->shipped    = $total_items;
        $summary->unshipped  = $summary->total_items - $total_items;

        // count orders which do (not) exist in WooCommerce
        $total_items = $wpdb->get_var("
			SELECT COUNT( o.id ) AS total_items
			FROM $this->tablename o
			LEFT JOIN {$wpdb->prefix}posts p ON o.post_id = p.ID 
			WHERE p.ID IS NOT NULL
		");
        $summary->has_wc_order    = $total_items;
        $summary->has_no_wc_order = $summary->total_items - $total_items;

		return $summary;
	}


	function getPageItems( $current_page, $per_page ) {
		global $wpdb;

        $orderby  = (!empty($_REQUEST['orderby'])) ? esc_sql( $_REQUEST['orderby'] ) : 'date_created';
        $order    = (!empty($_REQUEST['order']))   ? esc_sql( $_REQUEST['order']   ) : 'desc';
        $offset   = ( $current_page - 1 ) * $per_page;
        $per_page = esc_sql( $per_page );

        $join_sql  = '';
        $where_sql = 'WHERE 1 = 1 ';

        // filter order_status
		$order_status = ( isset($_REQUEST['order_status']) ? esc_sql( $_REQUEST['order_status'] ) : 'all');
		if ( $order_status != 'all' ) {
			$where_sql .= "AND o.CompleteStatus = '".$order_status."' ";
		}

		// filter shipped status
        $shipped = isset($_REQUEST['shipped']) ? esc_sql( $_REQUEST['shipped'] ) : '';
        if ( $shipped ) {
            $where_sql .= $shipped == 'yes' ? "AND ShippedTime <> '' " : "AND ShippedTime = '' ";
        }

        // filter has_wc_order
        $has_wc_order = isset($_REQUEST['has_wc_order']) ? esc_sql( $_REQUEST['has_wc_order'] ) : '';
        if ( $has_wc_order ) {
            // $where_sql .= $has_wc_order == 'yes' ? "AND o.post_id IS NOT NULL " : "AND o.post_id IS NULL ";
            $join_sql  .= "LEFT JOIN {$wpdb->prefix}posts p ON o.post_id = p.ID ";
            $where_sql .= $has_wc_order == 'yes' ? "AND p.ID IS NOT NULL " : "AND p.ID IS NULL ";
        }

        // filter account_id
		$account_id = ( isset($_REQUEST['account_id']) ? esc_sql( $_REQUEST['account_id'] ) : false);
		if ( $account_id ) {
			$where_sql .= "
				 AND o.account_id = '".$account_id."'
			";
		} 

        // filter search_query
		$search_query = ( isset($_REQUEST['s']) ? esc_sql( $_REQUEST['s'] ) : false);
		if ( $search_query ) {
			$where_sql .= "
				AND  ( o.buyer_name   LIKE '%".$search_query."%'
					OR o.items        LIKE '%".$search_query."%'
					OR o.buyer_userid     = '".$search_query."'
					OR o.buyer_email      = '".$search_query."'
					OR o.order_id         = '".$search_query."'
					OR o.post_id          = '".$search_query."'
					OR o.ShippingAddress_City LIKE '%".$search_query."%' )
			";
		} 


        // get items
		$items = $wpdb->get_results("
			SELECT *
			FROM $this->tablename o
            $join_sql 
            $where_sql
			ORDER BY $orderby $order
            LIMIT $offset, $per_page
		", ARRAY_A);

		// get total items count - if needed
		if ( ( $current_page == 1 ) && ( count( $items ) < $per_page ) ) {
			$this->total_items = count( $items );
		} else {
			$this->total_items = $wpdb->get_var("
				SELECT COUNT(*)
				FROM $this->tablename o
	            $join_sql 
    	        $where_sql
				ORDER BY $orderby $order
			");			
		}

		// foreach( $items as &$profile ) {
		// 	$profile['details'] = self::decodeObject( $profile['details'] );
		// }

		return $items;
	}


    ## BEGIN PRO ##

    // call CompleteSale for a single order
	public function completeEbayOrder( $session, $id, $data ) {

		$this->initServiceProxy($session);

		// get order item to update
		$order = $this->getItem( $id );

		// build request
		$req = new CompleteSaleRequestType();
		// $req->ItemID = $order['item_id'];
		$req->OrderID  = $order['order_id'];
		$req->Shipment = new ShipmentType();

		// handle shipping date
		if ( isset( $data['ShippedTime'] ) ) {

			// process ShippedTime parameter
			if ( $data['ShippedTime'] == '_now_' || $data['ShippedTime'] == '' ) {

				// use current gmt date and time
				$ShippedTime = gmdate( 'Y-m-d H:i:s' );

			} else {

				// or use the user provided date Y-m-d at 8am
				$ShippedTime = gmdate( 'Y-m-d', $data['ShippedTime'] ) . ' 08:00:00';

				// unless that is before the order creation date
				if ( strtotime( $ShippedTime ) < strtotime( $order['date_created'] ) ) {
					// in which case use current gmt date and time
					$ShippedTime = gmdate( 'Y-m-d H:i:s' );				
				}

			}
			
			$ShippedTime = $this->convertSqlDateToEbay( $ShippedTime );
			$req->Shipment->ShippedTime = $ShippedTime;
			$req->Shipped = true;
			$req->Paid = true;	// TODO: make this an option in order meta box
		}		

		// handle tracking info
		if ( isset( $data['TrackingNumber'] ) && trim( $data['TrackingNumber'] ) ) {
			$req->Shipment->ShipmentTrackingDetails = new ShipmentTrackingDetailsType();
			$req->Shipment->ShipmentTrackingDetails->ShipmentTrackingNumber = $data['TrackingNumber'];
			$req->Shipment->ShipmentTrackingDetails->ShippingCarrierUsed    = $data['TrackingCarrier'];
		}		

		// handle feedback
		if ( isset( $data['FeedbackText'] ) && trim( $data['FeedbackText'] ) ) {
			$data['FeedbackText'] = strlen($data['FeedbackText']) > 80 ? substr( $data['FeedbackText'], 0, 80 ) : $data['FeedbackText'];
			$req->FeedbackInfo = new FeedbackInfoType();
			$req->FeedbackInfo->CommentType = 'Positive';
			$req->FeedbackInfo->CommentText = $data['FeedbackText'];
			$req->FeedbackInfo->TargetUser  = $order['buyer_userid'];
		}

		// $req->Paid = true;
		// $req->Shipped = true;

		WPLE()->logger->info('completeEbayOrder(): '.$id);
		WPLE()->logger->info('OrderID: '.$req->OrderID);

		// $req->DetailLevel = $Facet_DetailLevelCodeType->ReturnAll;
		$req->setDetailLevel('ReturnAll');

		// If BestEffort is specified for CompleteSale, the Ack field in the response could return PartialFailure if one change fails but another succeeds. 
		// For example, if the seller attempts to leave feedback twice for the same order line item, 
		// the feedback changes would fail but any paid or shipped status changes would succeed.
		$req->setErrorHandling( 'BestEffort' );

		// download the data
		$res = $this->_cs->CompleteSale( $req );

		// handle response and check if successful
		$success = false;
		$error = '';
		if ( $this->handleResponse($res) ) {
			$success = true;
			WPLE()->logger->info( sprintf("Order %s updated successfully.", $id ) );
		} else {
			WPLE()->logger->error( "Error on CompleteSale(): ".print_r( $res, 1 ) );			
			if ( is_object($res) ) {
				$error = "eBay said: ".$res->Errors[0]->LongMessage;
			} else {
				$error = "Error on CompleteSale() - invalid response: ".print_r( $res, 1 );
			}
		}

        // build response
        $response = new stdClass();
        $response->success      = $success;
        $response->error        = $error;
        $response->error_code   = $this->handle_error_code;
        return $response;

	} // completeEbayOrder()

    ## END PRO ##


} // class EbayOrdersModel
