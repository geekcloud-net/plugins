<?php
/**
 * creates an order in WooCommerce - and optionally a customer in WP
 */

## BEGIN PRO ##

class WPL_WooOrderBuilder {

	var $id;
	var $vat_enabled    = false;
	var $vat_total      = 0;
	var $vat_rates      = array();
	var $shipping_taxes = array();

	//
	// update woo order from ebay order
	// 
	function updateOrderFromEbayOrder( $id, $post_id = false ) {
		WPLE()->logger->debug( 'updateOrderFromEbayOrder #'.$id );

		// get order details
		$ordersModel = new EbayOrdersModel();		
		$item        = $ordersModel->getItem( $id );
		$details     = $item['details'];
		if ( ! $post_id ) $post_id = $item['post_id'];

		// prevent WooCommerce from sending out notification emails when updating order status
		$this->disableEmailNotifications();

		// prevent WP-Lister from sending CompleteSale request when the status for an already shipped order is set to Completed
		remove_action( 'woocommerce_order_status_completed', array( 'WpLister_Order_MetaBox', 'handle_woocommerce_order_status_update' ), 0 );


		// get order
		$order = OrderWrapper::getOrder( $post_id );


		// update order creation date
		$timestamp     = strtotime($item['date_created'].' UTC');
		$post_date     = $ordersModel->convertTimestampToLocalTime( $timestamp );
		$post_date_gmt = date_i18n( 'Y-m-d H:i:s', $timestamp, true );
		// $post_date  = date_i18n( 'Y-m-d H:i:s', strtotime($item['date_created'].' UTC'), false );

		// check if shipping address has changed
		$shipping_details = $details->ShippingAddress;
		$billing_details  = $details->ShippingAddress;
		$new_shipping_address = false;
		if ( get_post_meta( $post_id, '_shipping_address_1', true ) != stripslashes( $shipping_details->Street1 ) ) 	$new_shipping_address = true;
		if ( get_post_meta( $post_id, '_shipping_address_2', true ) != stripslashes( $shipping_details->Street2 ) ) 	$new_shipping_address = true;
		if ( get_post_meta( $post_id, '_shipping_postcode',  true ) != stripslashes( $shipping_details->PostalCode ) ) 	$new_shipping_address = true;
		if ( get_post_meta( $post_id, '_billing_address_1',  true ) != stripslashes( $billing_details->Street1 ) ) 	    $new_shipping_address = true;
		if ( get_post_meta( $post_id, '_billing_postcode',   true ) != stripslashes( $billing_details->PostalCode ) ) 	$new_shipping_address = true;

		// never update shipping address for orders with multi leg shipping enabled
		// if ( $details->IsMultiLegShipping ) $this->processMultiLegShipping( $details, $post_id );
		if ( $details->IsMultiLegShipping ) $new_shipping_address = false;

		// update shipping address if required
		if ( $new_shipping_address ) {

			// optional fields
            // strip out spaces so WC displays it #14208 #16959
			if ($billing_details->Phone == 'Invalid Request') $billing_details->Phone = '';
			update_post_meta( $post_id, '_billing_phone', str_replace( ' ', '', stripslashes( $billing_details->Phone ) ) );

			// billing address
			@list( $billing_firstname, $billing_lastname )     = explode( " ", $billing_details->Name, 2 );
			update_post_meta( $post_id, '_billing_first_name', 	stripslashes( $billing_firstname ) );
			update_post_meta( $post_id, '_billing_last_name', 	stripslashes( $billing_lastname ) );
			update_post_meta( $post_id, '_billing_company', 	stripslashes( $billing_details->CompanyName ) );
			update_post_meta( $post_id, '_billing_address_1', 	stripslashes( $billing_details->Street1 ) );
			update_post_meta( $post_id, '_billing_address_2', 	stripslashes( $billing_details->Street2 ) );
			update_post_meta( $post_id, '_billing_city', 		stripslashes( $billing_details->CityName ) );
			update_post_meta( $post_id, '_billing_postcode', 	stripslashes( $billing_details->PostalCode ) );
			update_post_meta( $post_id, '_billing_country', 	stripslashes( $billing_details->Country ) );
			update_post_meta( $post_id, '_billing_state', 		stripslashes( $billing_details->StateOrProvince ) );
			
			// update shipping address
			@list( $shipping_firstname, $shipping_lastname )   = explode( " ", $shipping_details->Name, 2 );
			update_post_meta( $post_id, '_shipping_first_name', stripslashes( $shipping_firstname ) );
			update_post_meta( $post_id, '_shipping_last_name', 	stripslashes( $shipping_lastname ) );
			update_post_meta( $post_id, '_shipping_company', 	stripslashes( $shipping_details->CompanyName ) );
			update_post_meta( $post_id, '_shipping_address_1', 	stripslashes( $shipping_details->Street1 ) );
			update_post_meta( $post_id, '_shipping_address_2', 	stripslashes( $shipping_details->Street2 ) );
			update_post_meta( $post_id, '_shipping_city', 		stripslashes( $shipping_details->CityName ) );
			update_post_meta( $post_id, '_shipping_postcode', 	stripslashes( $shipping_details->PostalCode ) );
			update_post_meta( $post_id, '_shipping_country', 	stripslashes( $shipping_details->Country ) );
			update_post_meta( $post_id, '_shipping_state', 		stripslashes( $shipping_details->StateOrProvince ) );

			// add order note
			$history_message = "Order #$post_id shipping address was modified.";
			$history_details = array( 'post_id' => $post_id );
			$ordersModel->addHistory( $item['order_id'], 'update_order', $history_message, $history_details );
		}
		
		// update _paid_date (mysql time format)
		if ( $details->PaidTime != '' ) {
			$paid_date = $ordersModel->convertTimestampToLocalTime( strtotime( $details->PaidTime ) );
			update_post_meta( $post_id, '_paid_date', $paid_date );
		}

		// handle refunded orders
		WPLE()->logger->info('updateOrderFromeBayOrder: handle_refunds: ' . get_option( 'wplister_handle_ebay_refunds', 1 ) );
		if ( 1 == get_option( 'wplister_handle_ebay_refunds', 0 ) ) {
			$this->handleOrderRefunds( $item, $order );
		}

        // update the WC order with the PayPal transaction ID if available
        $this->processPayPalTransactionID( $post_id, $details );

        // update shipment details
        $this->recordShipmentTracking( $post_id, $details );

        // update shipping totals in case PayPal charges a different shipping amount than the eBay order #18515
        $this->updateShippingTotal( $post_id, $details );
		
		// do nothing if order is already marked as completed, refunded, cancelled or failed
		// if ( $order->status == 'completed' ) return $post_id;
		if ( in_array( $order->get_status(), array( 'completed', 'cancelled', 'refunded', 'failed' ) ) ) return $post_id;

		// the above blacklist won't work for custom order statuses created by the WooCommerce Order Status Manager extension
		// a custom order status should be left untouched as it probably serves a custom purpose - so whitelist all values used by WP-Lister:
		if ( ! in_array( $order->get_status(), array( 'pending', 'processing', 'on-hold', 'completed' ) ) ) return $post_id;


		// order status
		if ( ( $item['eBayPaymentStatus'] == 'PayPalPaymentInProcess' ) || ( $details->PaidTime == '' ) ) { 	
			$new_order_status = get_option( 'wplister_unpaid_order_status', 'on-hold' );
		} elseif ( ( $item['CompleteStatus'] == 'Completed' ) && ( $details->ShippedTime != '' ) ) { 
			// if order is marked as shipped on eBay, change status to completed
			$new_order_status = get_option( 'wplister_shipped_order_status', 'completed' );
		} elseif ( $item['CompleteStatus'] == 'Completed' ) { 
			$new_order_status = get_option( 'wplister_new_order_status', 'processing' );
		} else {
			$new_order_status = 'pending';
		}

		// update order status
		if ( $order->get_status() != $new_order_status ) {

			$history_message = "Order #$post_id status was updated from {$order->get_status()} to $new_order_status";
			$history_details = array( 'post_id' => $post_id );
			$ordersModel->addHistory( $item['order_id'], 'update_order', $history_message, $history_details );

			$order->update_status( $new_order_status );

		}

        // fix the order date after updating order status (WC2.6+)
        if ( version_compare( WC_VERSION, '3.0', '>=' ) ) {
            $order->set_date_created( $post_date );
            $order->save();
        } else {
            $update_post_data  = array(
                'ID'            => $post_id,
                'post_date'     => $post_date,
                'post_date_gmt' => $post_date_gmt,
            );
            wp_update_post( $update_post_data );
        }

		return $post_id;
	} // updateOrderFromEbayOrder()





	//
	// create woo order from ebay order
	// 
	function createWooOrderFromEbayOrder( $id ) {
		global $wpdb;

		// get order details
		$ordersModel = new EbayOrdersModel();		
		$item        = $ordersModel->getItem( $id );
		$details     = $item['details'];

		$timestamp     = strtotime($item['date_created'].' UTC');
		$post_date     = $ordersModel->convertTimestampToLocalTime( $timestamp );
		$post_date_gmt = date_i18n( 'Y-m-d H:i:s', $timestamp, true );
		// $date_created  = $item['date_created'];
		// $post_date_gmt = date_i18n( 'Y-m-d H:i:s', strtotime($item['date_created'].' UTC'), true );
		// $post_date     = date_i18n( 'Y-m-d H:i:s', strtotime($item['date_created'].' UTC'), false );

		// create order comment
		$order_comment  = '';
		if ( $details->BuyerCheckoutMessage != '' ) {
			$order_comment  = $details->BuyerCheckoutMessage . "\n";
		}
		$order_comment .= sprintf( __('eBay User ID: %s', 'wplister'), $details->BuyerUserID );
		if ( $details->ShippingDetails->SellingManagerSalesRecordNumber != '' ) {
			$order_comment .= "\n" . sprintf( __('eBay Sales Record ID: %s', 'wplister'), $details->ShippingDetails->SellingManagerSalesRecordNumber );
		}
		if ( $details->ContainseBayPlusTransaction == true ) {
			$order_comment .= "\n" . __('Contains eBay Plus Transaction', 'wplister');
		}

		// Create shop_order post object
        $post_data = apply_filters( 'wplister_order_post_data', array(
			'post_title'     => 'Order &ndash; '.date('F j, Y @ h:i A', strtotime( $post_date ) ),
			'post_content'   => '',
			'post_excerpt'   => stripslashes( $order_comment ),
			'post_date'      => $post_date, //The time post was made.
			'post_date_gmt'  => $post_date_gmt, //The time post was made, in GMT.
			'post_type'      => 'shop_order',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_status'    => function_exists('wc_create_order') ? 'wc-pending' : 'publish' // WC2.2 / WC2.6 support
		), $id, $item );

		// Insert order into the database
		$post_id = wp_insert_post( $post_data );

		// Update wp_order_id of order record
		$ordersModel->updateWpOrderID( $id, $post_id );	

		// store OrderID to mark order originated on eBay
		update_post_meta( $post_id, '_ebay_order_id',        $item['order_id'] );
		update_post_meta( $post_id, '_ebay_user_id',         $details->BuyerUserID );
		update_post_meta( $post_id, '_ebay_account_id',      $item['account_id'] );
		update_post_meta( $post_id, '_ebay_site_id',         $item['site_id'] );
		update_post_meta( $post_id, '_ebay_sales_record_id', $details->ShippingDetails->SellingManagerSalesRecordNumber );

		// store eBay user name for account
		$accounts = WPLE()->accounts;
		$account  = isset( $accounts[ $item['account_id'] ] ) ? $accounts[ $item['account_id'] ] : false;
		if ( $account ) update_post_meta( $post_id, '_ebay_account_name', $account->user_name );

		/* the following code is inspired by woocommerce_process_shop_order_meta() in writepanel-order_data.php */

		// add order key
		add_post_meta( $post_id, '_order_key',    'wc_' . uniqid('order_'), true );
		add_post_meta( $post_id, '_created_via',  'ebay', true );
		add_post_meta( $post_id, '_order_version', WC_VERSION, true );

		// update address
		$billing_details = $details->ShippingAddress;
		$shipping_details = $details->ShippingAddress;

		// optional billing address / RegistrationAddress
		// if ( isset( $details->Buyer->RegistrationAddress ) ) {
		// 	$billing_details = $details->Buyer->RegistrationAddress;
		// }

		// optional fields
        // strip out spaces so WC displays it #14208 #16959
		if ($billing_details->Phone == 'Invalid Request') $billing_details->Phone = '';
        update_post_meta( $post_id, '_billing_phone', str_replace( ' ', '', stripslashes( $billing_details->Phone ) ) );

		// billing address
		@list( $billing_firstname, $billing_lastname )     = explode( " ", $billing_details->Name, 2 );
		update_post_meta( $post_id, '_billing_first_name', 	stripslashes( $billing_firstname ) );
		update_post_meta( $post_id, '_billing_last_name', 	stripslashes( $billing_lastname ) );
		update_post_meta( $post_id, '_billing_company', 	stripslashes( $billing_details->CompanyName ) );
		update_post_meta( $post_id, '_billing_address_1', 	stripslashes( $billing_details->Street1 ) );
		update_post_meta( $post_id, '_billing_address_2', 	stripslashes( $billing_details->Street2 ) );
		update_post_meta( $post_id, '_billing_city', 		stripslashes( $billing_details->CityName ) );
		update_post_meta( $post_id, '_billing_postcode', 	stripslashes( $billing_details->PostalCode ) );
		update_post_meta( $post_id, '_billing_country', 	stripslashes( $billing_details->Country ) );
		update_post_meta( $post_id, '_billing_state', 		stripslashes( $billing_details->StateOrProvince ) );
		
		// shipping address
		@list( $shipping_firstname, $shipping_lastname )   = explode( " ", $shipping_details->Name, 2 );
		update_post_meta( $post_id, '_shipping_first_name', stripslashes( $shipping_firstname ) );
		update_post_meta( $post_id, '_shipping_last_name', 	stripslashes( $shipping_lastname ) );
		update_post_meta( $post_id, '_shipping_company', 	stripslashes( $shipping_details->CompanyName ) );
		update_post_meta( $post_id, '_shipping_address_1', 	stripslashes( $shipping_details->Street1 ) );
		update_post_meta( $post_id, '_shipping_address_2', 	stripslashes( $shipping_details->Street2 ) );
		update_post_meta( $post_id, '_shipping_city', 		stripslashes( $shipping_details->CityName ) );
		update_post_meta( $post_id, '_shipping_postcode', 	stripslashes( $shipping_details->PostalCode ) );
		update_post_meta( $post_id, '_shipping_country', 	stripslashes( $shipping_details->Country ) );
		update_post_meta( $post_id, '_shipping_state', 		stripslashes( $shipping_details->StateOrProvince ) );
		
		// order details
		update_post_meta( $post_id, '_billing_email', 		$item['buyer_email']);
		update_post_meta( $post_id, '_cart_discount', 		'0');
		update_post_meta( $post_id, '_order_discount', 		'0');
		update_post_meta( $post_id, '_order_tax', 			'0.00' );
		update_post_meta( $post_id, '_order_shipping_tax', 	'0.00' );
		update_post_meta( $post_id, '_customer_user', 		'0' );
		// update_post_meta( $post_id, '_prices_include_tax', 	'yes' );
		update_post_meta( $post_id, '_prices_include_tax', 	get_option( 'woocommerce_prices_include_tax' ) );

		// convert state names to ISO code
		self::fixCountryStates( $post_id );

		// Order Total
		$order_total = $details->Total->value;
		update_post_meta( $post_id, '_order_currency', $details->Total->attributeValues['currencyID'] );
		update_post_meta( $post_id, '_order_total', rtrim(rtrim(number_format( $order_total, 4, '.', ''), '0'), '.') );
		// update_post_meta( $post_id, '_order_currency', get_woocommerce_currency() );


		// update shipping
		// update_post_meta( $post_id, '_order_shipping', 			$shipping_total );
		// update_post_meta( $post_id, '_shipping_method', 		stripslashes( $shipping_method )); // TODO: mapping
		// update_post_meta( $post_id, '_shipping_method_title', 	$shipping_title );
		// update_post_meta( $post_id, '_order_shipping', isset($details->ShippingServiceSelected->ShippingServiceCost->value) ? $details->ShippingServiceSelected->ShippingServiceCost->value : '' );


		// Payment method handling
		$pm = new EbayPaymentModel();
		$payment_title  = $pm->getTitleByServiceName( $details->CheckoutStatus->PaymentMethod );
		$payment_method = $details->CheckoutStatus->PaymentMethod;
		// convert some eBay payment methods to WooCommerce equivalents
		// https://developer.ebay.com/DevZone/flex/docs/Reference/com/ebay/shoppingservice/BuyerPaymentMethodCodeType.html
		if ( $payment_method == 'PayPal' ) 						$payment_method = 'paypal';
		if ( $payment_method == 'COD' ) 						$payment_method = 'cod';
		if ( $payment_method == 'MoneyXferAccepted' ) 			$payment_method = 'bacs'; 
		if ( $payment_method == 'MoneyXferAcceptedInCheckout' ) $payment_method = 'bacs'; 
		update_post_meta( $post_id, '_payment_method', 			$payment_method ); 
		update_post_meta( $post_id, '_payment_method_title', 	$payment_title  );
	
		// update _paid_date (mysql time format)
		if ( $details->PaidTime != '' ) {
			$paid_date = $ordersModel->convertTimestampToLocalTime( strtotime( $details->PaidTime ) );
			update_post_meta( $post_id, '_paid_date', $paid_date );
		}

		$this->processPayPalTransactionID( $post_id, $details );

		// Tax rows (WC 1.x)
		// $order_taxes = array();
		// [...]		
		// update_post_meta( $post_id, '_order_taxes', $order_taxes );
			

		// Order line item(s)
		$this->processOrderLineItems( $details, $post_id );

		// shipping info
		$this->processOrderShipping( $post_id, $item );

		// process tax
		$this->processOrderVAT( $post_id, $item );

		// process sales tax
		$this->processSalesTax( $post_id, $item, $details );

		// process orders which use Global Shipping Program
		$this->processMultiLegShipping( $details, $post_id );


		// prevent WooCommerce from sending out notification emails when updating order status or creating customers
		$this->disableEmailNotifications();

		// prevent WP-Lister from sending CompleteSale request when the status for an already shipped order is set to Completed
		remove_action( 'woocommerce_order_status_completed', array( 'WpLister_Order_MetaBox', 'handle_woocommerce_order_status_update' ), 0 );


		// create customer user - if enabled
		if ( get_option( 'wplister_create_customers' ) ) {
			$user_id = $this->addCustomer( $item['buyer_email'], $details );
			update_post_meta( $post_id, '_customer_user', $user_id );
		}

		// support for WooCommerce Sequential Order Numbers Pro 1.5.6
		if ( isset( $GLOBALS['wc_seq_order_number_pro'] ) && method_exists( $GLOBALS['wc_seq_order_number_pro'], 'set_sequential_order_number' ) )
			$GLOBALS['wc_seq_order_number_pro']->set_sequential_order_number( $post_id );

		// support for WooCommerce Sequential Order Numbers Pro 1.7.0+
		if ( function_exists('wc_seq_order_number_pro') && method_exists( wc_seq_order_number_pro(), 'set_sequential_order_number' ) )
			wc_seq_order_number_pro()->set_sequential_order_number( $post_id );


		// order metadata had been saved, now get it so we can manipulate status
		$order = OrderWrapper::getOrder( $post_id );
		
		// order status
		if ( ( $item['eBayPaymentStatus'] == 'PayPalPaymentInProcess' ) || ( $details->PaidTime == '' ) ) { 	
			$new_order_status = get_option( 'wplister_unpaid_order_status', 'on-hold' );
		} elseif ( ( $item['CompleteStatus'] == 'Completed' ) && ( $details->ShippedTime != '' ) ) { 
			// if order is marked as shipped on eBay, change status to completed
			$new_order_status = get_option( 'wplister_shipped_order_status', 'completed' );
		} elseif ( $item['CompleteStatus'] == 'Completed') { 
			$new_order_status = get_option( 'wplister_new_order_status', 'processing' );
		} else {
			$new_order_status = 'pending';
		}

		$order->update_status( $new_order_status );

		// fix the order date after updating order status (WC2.6+)
        if ( version_compare( WC_VERSION, '3.0', '>=' ) ) {
            $order->set_date_created( $post_date );
            $order->save();
        } else {
            $update_post_data  = array(
                    'ID'            => $post_id,
                    'post_date'     => $post_date,
                    'post_date_gmt' => $post_date_gmt,
                );
            wp_update_post( $update_post_data );
        }

		// fix the completed date for completed orders - which is set to the current time by update_status()
		if ( $new_order_status == 'completed' ) {
			update_post_meta( $post_id, '_completed_date', $post_date );
		}

		// handle refunded orders
		WPLE()->logger->info('updateOrderFromeBayOrder: handle_refunds: ' . get_option( 'wplister_handle_ebay_refunds', 1 ) );
		if ( 1 == get_option( 'wplister_handle_ebay_refunds', 0 ) ) {
			$this->handleOrderRefunds( $item, $order );
		}

		// allow other developers to post-process orders created by WP-Lister
		// if you hook into this, please check if get_product() actually returns a valid product object
		// WP-Lister might create order line items which do not exist in WooCommerce!
		// 
		// bad code looks like this:
		// $product = get_product( $item['product_id'] );
		// echo $product->get_sku();
		//
		// good code should look like this:
		// $_product = $order->get_product_from_item( $item );
		// if ( $_product->exists() ) { ... };

		do_action( 'wplister_after_create_order_with_nonexisting_items', $post_id );
		do_action( 'wplister_after_create_order', $post_id ); // deprecated, but still used by WooCommerce Cost Of Goods 1.7.4

		// trigger WooCommerce webhook order.created - by simulating an incoming WC REST API request
		do_action( 'woocommerce_api_create_order', $post_id, array(), $order );

		return $post_id;

	} // createWooOrderFromEbayOrder()


	// convert country state names to ISO code (New South Wales -> NSW)
	function fixCountryStates( $post_id ) {
		if ( ! class_exists('WC_Countries') ) return; // requires WC2.3+

		$billing_country_code = get_post_meta( $post_id, '_billing_country', true );
		$billing_state_name   = get_post_meta( $post_id, '_billing_state', true );
		$country_states       = WC()->countries->get_states( $billing_country_code );
		if ( $state_code = array_search( $billing_state_name, $country_states ) ) {
			update_post_meta( $post_id, '_billing_state', $state_code );
		}

		$shipping_country_code = get_post_meta( $post_id, '_shipping_country', true );
		$shipping_state_name   = get_post_meta( $post_id, '_shipping_state', true );
		$country_states        = WC()->countries->get_states( $shipping_country_code );
		if ( $state_code = array_search( $shipping_state_name, $country_states ) ) {
			update_post_meta( $post_id, '_shipping_state', $state_code );
		}

	} // fixCountryStates()


	// process shipping info - create shipping line item
	function processOrderShipping( $post_id, $item ) {

		// shipping fee (gross)
		$shipping_total = $this->getShippingTotal( $item );

		// get shipping method title
		$sm = new EbayShippingModel();
		$shipping_method = $this->getShippingMethod( $item );
		$shipping_title  = $sm->getTitleByServiceName( $shipping_method );
		

		// calculate shipping tax amount - and adjust shipping total
		$shipping_tax_amount = 0;
		//if ( $this->vat_enabled ) {
            $vat_percent         = get_option( 'wplister_orders_fixed_vat_rate' );
			$shipping_tax_amount = $this->calculateShippingTaxAmount( $shipping_total, $post_id );
            //$shipping_tax_amount = $vat_percent ? $shipping_tax_amount : 0; // disable VAT if no percentage set
			$shipping_total      = $shipping_total - $shipping_tax_amount;
		//}

		// update shipping total (net - after substracting taxes)
		update_post_meta( $post_id, '_order_shipping', $shipping_total );

		// shipping method
		$details = $item['details'];
		$shipping_method_id_map    = apply_filters( 'wplister_shipping_service_id_map', array() );
		$shipping_method_id        = array_key_exists($shipping_method, $shipping_method_id_map) ? $shipping_method_id_map[$shipping_method] : $shipping_method;
		$shipping_method_title_map = apply_filters( 'wplister_shipping_service_title_map', array() );
		$shipping_method_title     = array_key_exists($shipping_method, $shipping_method_title_map) ? $shipping_method_title_map[$shipping_method] : $shipping_title;
		// this only works up to WC2.1:
		// update_post_meta( $post_id, '_shipping_method', 	  $shipping_method_id );
		// update_post_meta( $post_id, '_shipping_method_title', $shipping_method_title );

		// get global tax rate id for order item array
        /*$tax_rate_id        = get_option( 'wplister_process_order_tax_rate_id' );
        $autodetect_taxes   = get_option( 'wplister_orders_autodetect_tax_rates', 0 );

        if ( $autodetect_taxes ) {
            $order = wc_get_order( $post_id );
            $matched_tax_rates = WC_Tax::find_shipping_rates( array(
                'country' 	=> $order->shipping_country,
                'state' 	=> $order->shipping_state,
                'postcode' 	=> $order->shipping_postcode,
                'city' 		=> $order->shipping_city
            ) );

            $shipping_taxes = WC_Tax::calc_shipping_tax( $shipping_total, $matched_tax_rates );
        } else {
            $shipping_taxes = $shipping_tax_amount == 0 ? array() : array( $tax_rate_id => $shipping_tax_amount );
        }*/

		// create shipping info as order line items - WC2.2
		$item_id = wc_add_order_item( $post_id, array(
	 		'order_item_name' 		=> $shipping_method_title,
	 		'order_item_type' 		=> 'shipping'
	 	) );
	 	if ( $item_id ) {
	 	    $shipping_taxes = $this->shipping_taxes;
	 	    $shipping_taxes['total'] = $shipping_taxes;
	 	    wc_add_order_item_meta( $item_id, 'cost', 		$shipping_total );
		 	wc_add_order_item_meta( $item_id, 'method_id', $shipping_total == 0 ? 'free_shipping' : 'other' );
		 	wc_add_order_item_meta( $item_id, 'taxes', 	$shipping_taxes );
		}

		// filter usage:
		// add_filter( 'wplister_shipping_service_title_map', 'my_amazon_shipping_service_title_map' );
		// function my_amazon_shipping_service_title_map( $map ) {
		// 	$map = array_merge( $map, array(
		// 		'Std DE Dom' => 'DHL Paket'
		// 	));
		// 	return $map;
		// }
		// add_filter( 'wplister_shipping_service_id_map', 'my_amazon_shipping_service_id_map' );
		// function my_amazon_shipping_service_id_map( $map ) {
		// 	$map = array_merge( $map, array(
		// 		'Std DE Dom' => 'flat_rate'
		// 	));
		// 	return $map;
		// }

	} // processOrderShipping()



	function processSalesTax( $post_id, $item, $details ) {
		global $wpdb;

		$SalesTax        = $details->ShippingDetails->SalesTax;
		$SalesTaxPercent = $SalesTax->SalesTaxPercent;
		$SalesTaxState   = $SalesTax->SalesTaxState;
		$SalesTaxAmount  = $SalesTax->SalesTaxAmount->value;

		if ( ! floatval($SalesTaxAmount) ) return;
		// echo "<pre>";print_r($SalesTaxAmount);echo"</pre>";#die();
		// echo "<pre>";print_r($details);echo"</pre>";#die();

		// get tax rate
		$tax_rate_id = get_option( 'wplister_process_order_sales_tax_rate_id' );
		$tax_rate    = $wpdb->get_row( "SELECT tax_rate_id, tax_rate_country, tax_rate_state, tax_rate_name, tax_rate_priority FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_id = '$tax_rate_id'" );

		// do not store sales tax if no sales tax rate ID is selected #18242
		if ( !$tax_rate_id ) {
		    return;
        }

		$code      = WC_Tax::get_rate_code( $tax_rate_id );
		$tax_code  = $code ? $code : 'TAX-'.$SalesTaxState;
		$tax_label = $tax_rate_id ? $tax_rate->tax_rate_name : 'Sales Tax';
		$tax_label.= ' - '.$SalesTaxState.' ('.$SalesTaxPercent.'%)';

		$item_id = wc_add_order_item( $post_id, array(
	 		'order_item_name' 		=> $tax_code,
	 		'order_item_type' 		=> 'tax'
	 	) );

	 	// Add line item meta
	 	if ( $item_id ) {
		 	wc_add_order_item_meta( $item_id, 'compound', 0 );
		 	wc_add_order_item_meta( $item_id, 'tax_amount', $this->format_decimal( $SalesTaxAmount ) );
		 	wc_add_order_item_meta( $item_id, 'shipping_tax_amount', 0 );

		 	// if ( $tax_rate_id ) {
		 		wc_add_order_item_meta( $item_id, 'rate_id', $tax_rate_id );
		 		wc_add_order_item_meta( $item_id, 'label', $tax_label );
		 	// }
		}

		// store total order tax
		update_post_meta( $post_id, '_order_tax', $this->format_decimal( $SalesTaxAmount ) ); 			
		// update_post_meta( $post_id, '_order_shipping_tax', $this->format_decimal( $shipping_tax_amount ) ); 			

	} // processSalesTax()



	// calculate shipping tax amount based on global VAT rate
	// (VAT is usually applied to shipping fee)
	function calculateShippingTaxAmount( $shipping_total, $post_id ) {

		// get global VAT rate
		$vat_percent        = get_option( 'wplister_orders_fixed_vat_rate' );
        $autodetect_taxes   = get_option( 'wplister_orders_autodetect_tax_rates', 0 );
        $shipping_tax_amount= 0;
        $shipping_taxes     = array();

		if ( !$autodetect_taxes && !$vat_percent ) {
		    return 0;
        }

        if ( !$autodetect_taxes ) {
            // calculate VAT
            $tax_rate_id        = get_option( 'wplister_process_order_tax_rate_id' );
            $shipping_tax_amount = $shipping_total / ( 1 + ( 1 / ( $vat_percent / 100 ) ) );	// calc VAT from gross amount
            $shipping_taxes     = $shipping_tax_amount == 0 ? array() : array( $tax_rate_id => $shipping_tax_amount );

            if ( $shipping_tax_amount ) {
                $this->vat_enabled  = true;
            }
        } else {
		    $order = wc_get_order( $post_id );
            $matched_tax_rates = WC_Tax::find_shipping_rates( array(
                'country' 	=> wple_get_order_meta( $order, 'shipping_country' ),
                'state' 	=> wple_get_order_meta( $order, 'shipping_state' ),
                'postcode' 	=> wple_get_order_meta( $order, 'shipping_postcode' ),
                'city' 		=> wple_get_order_meta( $order, 'shipping_city' )
            ) );

            $shipping_taxes = array();
            $new_shipping_total = false;
            WPLE()->logger->info('Matched rates: '. print_r($matched_tax_rates, true));
            foreach ( $matched_tax_rates as $key => $rate ) {
                if ( $rate['shipping'] != 'yes' ) {
                    continue;
                }

                $this->vat_enabled = true;

                // get the gross shipping fee (without VAT) and the applied VAT amount
                $tax_rate = (100 + $rate['rate']) / 100;
                $new_shipping_total = $shipping_total / $tax_rate;
                $shipping_tax = $shipping_total - $new_shipping_total;


                // Add rate
                if ( ! isset( $shipping_taxes[ $key ] ) )
                    $shipping_taxes[ $key ] = $shipping_tax;
                else
                    $shipping_taxes[ $key ] += $shipping_tax;

                // Recording shipping taxes in the vat_rates array duplicates the value in the order totals
                //if ( ! isset( $this->vat_rates[ $key ] ) )
                //    $this->vat_rates[ $key ] = $shipping_tax;
                //else
                //    $this->vat_rates[ $key ] += $shipping_tax;

            }
        }

        if ( !empty( $shipping_taxes ) ) {
            $this->shipping_taxes = $shipping_taxes;
            $shipping_tax_amount = array_sum( $shipping_taxes );
        }

		return $shipping_tax_amount;
	}


	function processOrderVAT( $post_id, $item ) {
		global $wpdb;

		WPLE()->logger->info( 'processOrderVAT() #'. $post_id );
		WPLE()->logger->debug( print_r( $item, true ) );

		if ( ! $this->vat_enabled ) {
		    WPLE()->logger->info( 'vat_enabled is false' );
		    return;
        }

		$tax_rate_id        = get_option( 'wplister_process_order_tax_rate_id' );
		$autodetect_taxes   = get_option( 'wplister_orders_autodetect_tax_rates', 0 );

		WPLE()->logger->info( '$tax_rate_id: '. $tax_rate_id );
		WPLE()->logger->info( '$autodetect_taxes: '. $autodetect_taxes );

        if ( !$autodetect_taxes && !$tax_rate_id ) {
            // don't add VAT if no tax rate set.
            WPLE()->logger->info( 'autodetect_taxes and tax_rate_id are not set. Exiting.' );
            return;
        }

		// shipping fee (gross)
		$shipping_total = $this->getShippingTotal( $item );
        WPLE()->logger->info( 'getShippingTotal(): '. $shipping_total );

		// calculate shipping tax (from gross amount)
        $shipping_tax_amount = $this->calculateShippingTaxAmount( $shipping_total, $post_id );
        WPLE()->logger->info( 'calculateShippingTaxAmount: '. $shipping_tax_amount );

		// disabled this since it's already being checked in self::calculateShippingTaxAmount()
        //$shipping_tax_amount = $vat_percent ? $shipping_tax_amount : 0; // disable VAT if no percentage set


        WPLE()->logger->debug( 'vat_rates: ' . print_r( $this->vat_rates, true ) );

        // store shipping taxes separately if vat_rates is empty #17729
        if ( empty( $this->vat_rates ) && !empty( $this->shipping_taxes ) ) {
            foreach ( $this->shipping_taxes as $rate_id => $tax_amount ) {
                $this->addOrderLineTax( $post_id, $rate_id, 0, $tax_amount );
            }
        } else {
            foreach ( $this->vat_rates as $tax_rate_id => $tax_amount ) {
                // Pull the correct shipping tax for the current tax rate
                $shipping_tax = isset( $this->shipping_taxes[ $tax_rate_id ] ) ? $this->shipping_taxes[ $tax_rate_id ] : 0;

                $this->addOrderLineTax( $post_id, $tax_rate_id, $tax_amount, $shipping_tax );
            }
        }

		// store total order tax
        WPLE()->logger->info( 'Storing _order_tax: '. $this->vat_total );
        WPLE()->logger->info( 'Storing _order_shipping_tax: '. $shipping_tax_amount );
		update_post_meta( $post_id, '_order_tax', $this->format_decimal( $this->vat_total ) ); 			
		update_post_meta( $post_id, '_order_shipping_tax', $this->format_decimal( $shipping_tax_amount ) );

        // if autodetect taxes is enabled and woocommerce_prices_include_tax is disabled,
        // add the tax total to the order total #15043
        //
        // Added the 'wplister_include_vat_in_order_total' filter to allow external code to prevent VAT from being added to the order total #16294
        if ( $autodetect_taxes && get_option( 'woocommerce_prices_include_tax', 'no' ) == 'no' && apply_filters( 'wplister_include_vat_in_order_total', true, $post_id, $item ) ) {
            $order_total = get_post_meta( $post_id, '_order_total', true );
            update_post_meta( $post_id, '_order_total', $order_total + $this->vat_total );
        }
	} // processOrderVAT()


	function processPayPalTransactionID( $post_id, $details ) {
		if ( ! $details->ExternalTransaction ) return;

		// fetch PayPal transaction ID
		$transaction_id = is_array( $details->ExternalTransaction ) ? $details->ExternalTransaction[0]->ExternalTransactionID : null;

		// alternative way of fetching the PayPal transaction ID
		// if ( $details->MonetaryDetails && is_array( $details->MonetaryDetails->Payments->Payment ) ) {
		// 	$transaction_id = $details->MonetaryDetails->Payments->Payment[0]->ReferenceID->value;
		// }

		update_post_meta( $post_id, '_transaction_id', $transaction_id ); 

	} // processPayPalTransactionID()


	function createOrderLineItem( $Transaction, $post_id ) {
		// get listing item from db
        if ( get_option( 'wplister_match_sales_by_sku', 0 ) == 1) {
            $listingItem = WPLE_ListingQueryHelper::findItemBySku( $Transaction->Item->SKU );
        } else {
            $listingItem = WPLE_ListingQueryHelper::findItemByEbayID( $Transaction->Item->ItemID );
        }

		WPLE()->logger->info( 'createOrderLineItem for order #'.$post_id );
		// WPLE()->logger->info( 'createOrderLineItem - listingItem: '.print_r($listingItem,1) );
		// WPLE()->logger->info( 'createOrderLineItem - Transaction: '.print_r($Transaction,1) );

		$product_id			= $listingItem ? $listingItem->post_id : '0';
		$item_name 			= $listingItem ? $listingItem->auction_title : $Transaction->Item->Title;

		$item_quantity 		= $Transaction->QuantityPurchased;
		$line_subtotal		= $item_quantity * $Transaction->TransactionPrice->value;
		$line_total 		= $item_quantity * $Transaction->TransactionPrice->value;
		$product_price      = $Transaction->TransactionPrice->value;

		// default to no tax
		$line_subtotal_tax	= '0.00';
		$line_tax		 	= '0.00';
		$item_tax_class		= '';
		$tax_rate_id		= ''; // prevent "Notice: Undefined variable"

		// if auto-detect is disabled and use profile VAT is enabled
		if ( 0 == get_option( 'wplister_orders_autodetect_tax_rates', 0 ) && 1 == get_option( 'wplister_process_order_vat', 1 ) ) {
			// check if listing has VAT enabled in its profile
			$vat_enabled = $listingItem && $listingItem->profile_data['details']['tax_mode'] == 'fix' ? true : false;
			$taxes = $this->getProductTaxFromProfile( $listingItem, $product_price, $item_quantity );

            // don't add VAT if no tax rate set
            // (set $vat_enabled to false here here to prevent subtracting tax from line item price - processOrderVAT() will not add VAT without tax_rate_id!)
            if ( ! get_option( 'wplister_process_order_tax_rate_id' ) ) $vat_enabled = false;
		} else {
			$taxes = $this->getProductTax( $product_price, $product_id, $item_quantity, $post_id );

			if ( $taxes['line_tax'] > 0 ) {
				$vat_enabled = true;
			}
		}

		$vat_enabled = apply_filters( 'wple_order_has_vat_enabled', $vat_enabled, $post_id, $Transaction );

		// process VAT if enabled
		if ( $vat_enabled ) {
			//WPLE()->logger->info( 'VAT%: '. $vat_percent );

			// calculate VAT included in line total
			// $vat_tax = $line_total * $vat_percent / 100; 					// calc VAT from net amount
			//$vat_tax = $line_total / ( 1 + ( 1 / ( $vat_percent / 100 ) ) );	// calc VAT from gross amount
			// WPLE()->logger->info( 'VAT: '.$vat_tax );

	        if ( $taxes['line_subtotal_tax'] ) {
	            $line_subtotal_tax = $taxes['line_subtotal_tax'];
	        }

	        if ( $taxes['line_tax'] ) {
	            $line_tax = $taxes['line_tax'];
	        }

	        if ( $taxes['tax_rate_id'] ) {
	            $tax_rate_id = $taxes['tax_rate_id'];
	        }

	        if ( $taxes['line_total'] ) {
	            $line_total = $taxes['line_total'];
	        }

	        if ( $taxes['line_subtotal'] ) {
	            $line_subtotal = $taxes['line_subtotal'];
	        }

			// keep record of total VAT
			$vat_tax = $line_tax;
			$this->vat_enabled = true;
			$this->vat_total  += $vat_tax;

			// and keep track of the used tax rates so we can store them with the order later
			//if ( $tax_rate_id ) {
            //    @$this->vat_rates[ $tax_rate_id ] += $vat_tax;
            //}
            //
            // Use $taxes['line_tax_data'] to store multiple tax rates if available #13585
            if ( is_array( $taxes['line_tax_data']['total'] ) ) {
                foreach ( $taxes['line_tax_data']['total'] as $rate_id => $amount ) {
                    @$this->vat_rates[ $rate_id ] += $amount;
                }
            }

			// $vat_tax = wc_round_tax_total( $vat_tax );
			$vat_tax = $this->format_decimal( $vat_tax );
			WPLE()->logger->info( 'VAT: '.$vat_tax );
			WPLE()->logger->info( 'vat_total: '.$this->vat_total );

			$line_subtotal_tax	= $vat_tax;
			$line_tax		 	= $vat_tax;

			// adjust item price if prices include tax
			// if prices do not include tax, but VAT is enabled, adjust item price as well (the same happens with shipping fee)
			// if ( get_option( 'woocommerce_prices_include_tax' ) == 'yes' ) {
				// $line_total    = $line_total    - $vat_tax;
				// $line_subtotal = $line_subtotal - $vat_tax;
			// }

			// try to get product object to set tax class
			if ( $listingItem && is_object( $listingItem ) ) {
				$_product = ProductWrapper::getProduct( $listingItem->post_id );
			}

			// set tax class
			if ( isset( $_product ) && is_object($_product) ) {
				$item_tax_class		= $_product->get_tax_class();
				WPLE()->logger->info( 'found product '. wple_get_product_meta( $_product, 'id' ).' - using tax_class: '.$item_tax_class );
			}

			WPLE()->logger->info( 'tax_class: '.$item_tax_class );
		}

		// process sales tax
		if ( is_array($Transaction->Taxes->TaxDetails) ) {
			foreach ( $Transaction->Taxes->TaxDetails as $Tax ) {
				// skip everything but sales tax
				if ( 'SalesTax' != $Tax->Imposition ) continue;
				if ( ! floatval( $Tax->TaxAmount->value ) ) continue;

				$line_subtotal_tax	= $Tax->TaxAmount->value;
				$line_tax		 	= $Tax->TaxAmount->value;
				$item_tax_class		= '';
				WPLE()->logger->info( 'SalesTax: '.$Tax->TaxAmount->value );
			}
		}

		// check if item has variation 
		$isVariation        = false;
		$VariationSKU       = false;
		$VariationSpecifics = array();
        if ( is_object( @$Transaction->Variation ) ) {
            foreach ($Transaction->Variation->VariationSpecifics as $spec) {
                $VariationSpecifics[ $spec->Name ] = $spec->Value[0];
            }
			$isVariation  = true;
			$VariationSKU = $Transaction->Variation->SKU;
        } 

		// get variation_id
		if ( $isVariation ) {
			$variation_id = ProductWrapper::findVariationID( $product_id, $VariationSpecifics, $VariationSKU );
		}

		// support split variations since variation check above doesn't account for them #
        if ( ! $isVariation && $listingItem->parent_id > 0 ) {
            $product_id = $listingItem->parent_id;
            $variation_id = $listingItem->post_id;
        }

		$order_item = array();

		$order_item['product_id'] 			= $product_id;
		$order_item['variation_id'] 		= isset( $variation_id ) ? $variation_id : '0';
		$order_item['name'] 				= $item_name;
		// $order_item['tax_class']			= $_product->get_tax_class();
		$order_item['tax_class']			= $item_tax_class;
		$order_item['qty'] 					= $item_quantity;
		$order_item['line_subtotal'] 		= $this->format_decimal( $line_subtotal );
		$order_item['line_subtotal_tax'] 	= $line_subtotal_tax;
		$order_item['line_total'] 			= $this->format_decimal( $line_total );
		$order_item['line_tax'] 			= $line_tax;
		$order_item['line_tax_data'] 		= array( 
			//'total' 	=> array( $tax_rate_id => $line_tax ),
			//'subtotal' 	=> array( $tax_rate_id => $line_subtotal_tax ),
            'total'     => $taxes['line_tax_data']['total'],
            'subtotal'  => $taxes['line_tax_data']['subtotal']
		);

		$order_item = apply_filters( 'wplister_order_builder_line_item', $order_item, $post_id );

		// Add line item
	   	$item_id = wc_add_order_item( $post_id, array(
	 		'order_item_name' 		=> $order_item['name'],
	 		'order_item_type' 		=> 'line_item'
	 	) );

	 	// Add line item meta
	 	if ( $item_id ) {
		 	wc_add_order_item_meta( $item_id, '_qty', 				$order_item['qty'] );
		 	wc_add_order_item_meta( $item_id, '_tax_class', 		$order_item['tax_class'] );
		 	wc_add_order_item_meta( $item_id, '_product_id', 		$order_item['product_id'] );
		 	wc_add_order_item_meta( $item_id, '_variation_id', 	$order_item['variation_id'] );
		 	wc_add_order_item_meta( $item_id, '_line_subtotal', 	$order_item['line_subtotal'] );
		 	wc_add_order_item_meta( $item_id, '_line_subtotal_tax',$order_item['line_subtotal_tax'] );
		 	wc_add_order_item_meta( $item_id, '_line_total', 		$order_item['line_total'] );
		 	wc_add_order_item_meta( $item_id, '_line_tax', 		$order_item['line_tax'] );
		 	wc_add_order_item_meta( $item_id, '_line_tax_data', 	$order_item['line_tax_data'] );

			// store SKU as order line item meta field
			$ItemSKU = $VariationSKU ? $VariationSKU : $Transaction->Item->SKU;
			$store_sku_as_order_meta = get_option( 'wplister_store_sku_as_order_meta', 1 );
			if ( $ItemSKU && $store_sku_as_order_meta ) {
		 		wc_add_order_item_meta( $item_id, 'SKU', 			$ItemSKU );
			}

	 	}

	 	// add variation attributes as order item meta (WC2.2)
	 	if ( $item_id && $isVariation ) {
	 		foreach ($VariationSpecifics as $attribute_name => $value) {
			 	wc_add_order_item_meta( $item_id, $attribute_name,	$value );
	 		}
	 	}

		WPLE()->logger->info( 'order item created - item_id: '.$item_id );
		WPLE()->logger->info( 'order item data: '.print_r($order_item,1) );
		WPLE()->logger->info( '***' );

	} // createOrderLineItem()


	function processOrderLineItems( $Details, $post_id ) {

		// WC 2.0 only
		if ( ! function_exists('woocommerce_add_order_item_meta') ) return;

		foreach ( $Details->TransactionArray as $Transaction ) {
			$this->createOrderLineItem( $Transaction, $post_id );
		}
		 
	} // processOrderLineItems()

	function getShippingTotal( $item ) {
		$details     = $item['details'];

		// check selected shipping service
		$shipping_total  = 0;

		$ShippingServiceSelected = $details->getShippingServiceSelected();
		if ( $ShippingServiceSelected && method_exists($ShippingServiceSelected, 'getShippingServiceCost'))
			$ShippingServiceCost = $ShippingServiceSelected->getShippingServiceCost();

		if ( isset( $ShippingServiceCost ) && $ShippingServiceCost->value )
			$shipping_total = $ShippingServiceCost->value;

		return apply_filters( 'wplister_ebay_order_shipping_total', $shipping_total, $item );

	} // getShippingTotal()

	function getShippingMethod( $item ) {
		$details     = $item['details'];

		// check selected shipping service
		$shipping_method = 'N/A';

		$ShippingServiceSelected = $details->getShippingServiceSelected();
		if ( $ShippingServiceSelected && method_exists($ShippingServiceSelected, 'getShippingServiceCost'))
			$ShippingServiceCost = $ShippingServiceSelected->getShippingServiceCost();

		if ( $ShippingServiceSelected && method_exists($ShippingServiceSelected, 'getShippingService'))
			$shipping_method = $ShippingServiceSelected->getShippingService();

		return $shipping_method;	 
	} // getShippingMethod()


	function processMultiLegShipping( $details, $post_id ) {

		// check if multi leg shipping is enabled
		if ( ! get_option( 'wplister_process_multileg_orders', 0 ) ) return;
		if ( ! $details->getIsMultiLegShipping() ) return;

		// shortcuts
		$ShipToAddress          = $details->MultiLegShippingDetails->SellerShipmentToLogisticsProvider->ShipToAddress;
		$ShippingServiceDetails = $details->MultiLegShippingDetails->SellerShipmentToLogisticsProvider->ShippingServiceDetails;
		// echo "<pre>";print_r($ShipToAddress);echo"</pre>";
		// echo "<pre>";print_r($ShippingServiceDetails);echo"</pre>";


		// shipping address
		@list( $shipping_firstname, $shipping_lastname )   = explode( " ", $ShipToAddress->Name, 2 );
		update_post_meta( $post_id, '_shipping_first_name', stripslashes( $shipping_firstname ) );
		update_post_meta( $post_id, '_shipping_last_name', 	stripslashes( $shipping_lastname ) );
		update_post_meta( $post_id, '_shipping_company', 	stripslashes( '' ) ); 						// could be "eBay"...
		update_post_meta( $post_id, '_shipping_address_1', 	stripslashes( 'Reference# '.$ShipToAddress->ReferenceID ) );
		update_post_meta( $post_id, '_shipping_address_2', 	stripslashes( $ShipToAddress->Street1 ) );
		update_post_meta( $post_id, '_shipping_city', 		stripslashes( $ShipToAddress->CityName ) );
		update_post_meta( $post_id, '_shipping_postcode', 	stripslashes( $ShipToAddress->PostalCode ) );
		update_post_meta( $post_id, '_shipping_country', 	stripslashes( $ShipToAddress->Country ) );
		update_post_meta( $post_id, '_shipping_state', 		stripslashes( $ShipToAddress->StateOrProvince ) );


		// shipping service
		$shipping_total   = 0;
		$shipping_method = 'N/A';

		if ( $ShippingServiceDetails && method_exists($ShippingServiceDetails, 'getTotalShippingCost'))
			$TotalShippingCost = $ShippingServiceDetails->getTotalShippingCost();

		if ( isset( $TotalShippingCost ) && $TotalShippingCost->value )
			$shipping_total = $TotalShippingCost->value;

		if ( $ShippingServiceDetails && method_exists($ShippingServiceDetails, 'getShippingService'))
			$shipping_method = $ShippingServiceDetails->getShippingService();

		// get shipping method title
		$sm = new EbayShippingModel();
		$shipping_title = $sm->getTitleByServiceName( $shipping_method );

		// update shipping
		update_post_meta( $post_id, '_order_shipping', 			$shipping_total );
		update_post_meta( $post_id, '_shipping_method', 		stripslashes( $shipping_method )); // TODO: mapping
		update_post_meta( $post_id, '_shipping_method_title', 	$shipping_title );


		// fix order total (which should not include the shipping fee eBay charges the buyer)
		// (TotalShippingCost / $shipping_total should be zero for orders that use global shipping)
		// TODO: remove the shipping record (order line item) created in self::processOrderShipping()
		$order_total = $details->Subtotal->value + $shipping_total;
		update_post_meta( $post_id, '_order_total', rtrim(rtrim(number_format( $order_total, 4, '.', ''), '0'), '.') );

        // remove the shipping tax from the order #13388
        $order = OrderWrapper::getOrder( $post_id );

        $tax_items = $order->get_items( 'tax' );

        if ( $tax_items ) {
            foreach ( $tax_items as $tax_item_id => $tax_item ) {
                woocommerce_update_order_item_meta( $tax_item_id, 'shipping_tax_amount', 0 );
            }
        }

        update_post_meta( $post_id, '_order_shipping_tax', 0 );

	} // processMultiLegShipping()

	/**
	 * Handle refunds made on ebay/paypal
	 * @param object    $item
	 * @param WC_Order  $order
	 */
	function handleOrderRefunds( $item, $order ) {
		global $wpdb;

		WPLE()->logger->info('handleOrderRefunds on order #'. wple_get_order_meta( $order, 'id' ) );

		$details = $item['details'];

		if ( $details->MonetaryDetails->Refunds ) {
		    // get existing refunds for the order
            $existing_refunds = $order->get_refunds();
            $refunds_reference_ids = array();
            foreach ( $existing_refunds as $wc_refund ) {
                $ref_id = get_post_meta( $wc_refund->id, '_ebay_reference_id', true );

                if ( $ref_id ) {
                    $refunds_reference_ids[] = $ref_id;
                }
            }

			WPLE()->logger->info('handleOrderRefunds: Refunds found' );
			foreach (  $details->MonetaryDetails->Refunds->Refund as $refund ) {
			    $reason = sprintf( __( 'eBay order refund %s (eBay Order #: %s)', 'wplister' ), $details->OrderID );

				if ( $refund->ReferenceID ) {
				    // check if this refund row has already been processed before
				    if ( in_array( $refund->ReferenceID->value, $refunds_reference_ids ) ) {
				        continue;
                    }
					$reason .= ' (Refund #: '. $refund->ReferenceID->value .')';
				}

				WPLE()->logger->info('handleOrderRefunds: eBay Order #'. $details->OrderID . ' (Amt: ' . $refund->RefundAmount->value . ')' );

				// for WC2.2+, record the refund so it reflects in the WC Order
				if ( function_exists( 'wc_create_refund' ) ) {
					$wc_refund = wc_create_refund( array(
						'amount'    => abs( $refund->RefundAmount->value ),
						'reason'    => $reason,
						'order_id'  => wple_get_order_meta( $order, 'id' )
					) );

					if ( !is_wp_error( $wc_refund ) ) {
					    $refund_id = is_callable( array( $wc_refund, 'get_id' ) ) ? $wc_refund->get_id() : $wc_refund->id;
					    update_post_meta( $refund_id, '_ebay_reference_id', $refund->ReferenceID->value );
                    }
				}

				// update WC Order's status to refunded. Add the note separately so it gets added regardless of the order's previous status
				$order->update_status( 'refunded' );
				$order->add_order_note( $reason );

				// update ebay order's status too
				$wpdb->update( $wpdb->prefix . 'ebay_orders', array( 'CompleteStatus' => 'Refunded' ), array( 'id' => $item['id'] ) );

				WPLE()->logger->info('handleOrderRefunds: completed' );
			}
		}
	}

	/**
	 * Record shipment tracking details
	 *
	 * @param int $post_id
	 * @param OrderType $details
	 */
	public function recordShipmentTracking( $post_id, $details ) {
		$provider           = get_post_meta( $post_id, '_tracking_provider', true );
		$tracking_number    = get_post_meta( $post_id, '_tracking_number', true );

		if ( ! empty( $provider ) && ! empty( $tracking_number ) ) {
			WPLE()->logger->info('recordShipmentTracking: Tracking already set for ' . $post_id );
			return;
		}

		if ( ! @$details->TransactionArray ) {
			WPLE()->logger->info('recordShipmentTracking: TransactionArray is empty for ' . $post_id );
			return;
		}

		$transaction = current( $details->TransactionArray );

		if ( $transaction->ShippingDetails->ShipmentTrackingDetails ) {
			foreach ( $transaction->ShippingDetails->ShipmentTrackingDetails as $shipment ) {
				$provider = WpLister_Order_MetaBox::findMatchingTrackingProvider( $shipment->ShippingCarrierUsed );
				$tracking_number = $shipment->ShipmentTrackingNumber;

				update_post_meta( $post_id, '_tracking_provider', $provider );
				update_post_meta( $post_id, '_tracking_number', $tracking_number );

				WPLE()->logger->info('recordShipmentTracking: Recorded '. $tracking_number . ' via ' . $provider . ' for '. $post_id );
				break;
			}
		}

	}

    /**
     * Update the shipping total if the value in $details is different that the currently stored value
     *
     * @param int       $order_id
     * @param OrderType $details
     */
	public function updateShippingTotal( $order_id, $details ) {
	    $current_total = get_post_meta( $order_id, '_order_shipping', true );
        $new_total     = $current_total;

        $ShippingServiceSelected = $details->getShippingServiceSelected();
        if ( $ShippingServiceSelected && method_exists($ShippingServiceSelected, 'getShippingServiceCost'))
            $ShippingServiceCost = $ShippingServiceSelected->getShippingServiceCost();

        if ( isset( $ShippingServiceCost ) && $ShippingServiceCost->value ) {
            $new_total = $ShippingServiceCost->value;
        }

        // calculate shipping tax amount - and adjust shipping total
        $shipping_tax_amount = $this->calculateShippingTaxAmount( $new_total, $order_id );

        $new_total      = $new_total - $shipping_tax_amount;

        if ( $new_total && $new_total != $current_total ) {
            // update the shipping fee
            update_post_meta( $order_id, '_order_shipping', $new_total );

            $order          = wc_get_order( $order_id );
            $shipping_items = $order->get_items( 'shipping' );

            if ( $shipping_items ) {
                if ( version_compare( WC_VERSION, '3.0', '>=' ) ) {
                    $item = current( $shipping_items );
                    $item_id = $item->get_id();
                } else {
                    reset( $shipping_items );
                    $item_id = key( $shipping_items );
                }

                wc_update_order_item_meta( $item_id, 'cost', $new_total );
            }
        }
    }

	function format_decimal( $number ) {

		// wc_format_decimal() exists in WC 2.1+ only
		if ( function_exists('wc_format_decimal') ) 
			return wc_format_decimal( $number );

		$dp     = get_option( 'woocommerce_price_num_decimals' );
		$number = number_format( floatval( $number ), $dp, '.', '' );
		return $number;
		 
	} // format_decimal()



	/**
	 * addCustomer, adds a new customer to newsletter subscriptions
	 *
	 * @param unknown $customers_name
	 * @return $customers_id
	 */
	public function addCustomer( $user_email, $details ) {
		global $wpdb;
		// WPLE()->logger->info( "addCustomer() - data: ".print_r($details,1) );

		// skip if user_email exists
		if ( $user_id = email_exists( $user_email ) ) {
			// $this->show_message('Error: email already exists: '.$user_email, 1 );
			WPLE()->logger->info( "email already exists $user_email" );
			return $user_id;
		}

		// get user data
		$ebay_user_id    = $details->BuyerUserID;
		$user_name       = $details->BuyerUserID;

		// get shipping address with first and last name
		$shipping_details = $details->ShippingAddress;
		@list( $shipping_firstname, $shipping_lastname ) = explode( " ", $shipping_details->Name, 2 );
		$user_firstname  = sanitize_user( $shipping_firstname, true );
		$user_lastname   = sanitize_user( $shipping_lastname, true );
		$user_fullname   = sanitize_user( $shipping_details->Name, true );

		// generate password
		$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );


		// check if user with ebay account as username exists
		$user_id = username_exists( $ebay_user_id );
		if ( $user_id ) {
			WPLE()->logger->info( "user already exists: $user_name - $user_email ($user_id) " );
			return $user_id;
		}

		// create wp_user
		$wp_user = array(
			'user_login' => $user_name,
			'user_email' => $user_email,
			'first_name' => $user_firstname,
			'last_name' => $user_lastname,
			// 'user_registered' => gmdate( 'Y-m-d H:i:s', strtotime($customer['customers_info_date_account_created']) ),
			'user_pass' => $random_password,
			'role' => 'customer'
			);
		$user_id = wp_insert_user( $wp_user ) ;

		if ( is_wp_error($user_id)) {

			WPLE()->logger->error( 'error creating user '.$user_email.' - WP said: '.$user_id->get_error_message() );
			return false;

		} else {

			// add user meta
			update_user_meta( $user_id, '_ebay_user_id', 		$ebay_user_id );
			update_user_meta( $user_id, 'billing_email', 		$user_email );
			update_user_meta( $user_id, 'paying_customer', 		1 );
			
			// optional phone number
            // strip out spaces so WC displays it #14208 #16959
			if ($shipping_details->Phone == 'Invalid Request') $shipping_details->Phone = '';
			update_user_meta( $user_id, 'billing_phone', str_replace( ' ', '', stripslashes( $shipping_details->Phone ) ) );

			// billing
			update_user_meta( $user_id, 'billing_first_name', 	$user_firstname );
			update_user_meta( $user_id, 'billing_last_name', 	$user_lastname );
			update_user_meta( $user_id, 'billing_company', 		stripslashes( $shipping_details->CompanyName ) );
			update_user_meta( $user_id, 'billing_address_1', 	stripslashes( $shipping_details->Street1 ) );
			update_user_meta( $user_id, 'billing_address_2', 	stripslashes( $shipping_details->Street2 ) );
			update_user_meta( $user_id, 'billing_city', 		stripslashes( $shipping_details->CityName ) );
			update_user_meta( $user_id, 'billing_postcode', 	stripslashes( $shipping_details->PostalCode ) );
			update_user_meta( $user_id, 'billing_country', 		stripslashes( $shipping_details->Country ) );
			update_user_meta( $user_id, 'billing_state', 		stripslashes( $shipping_details->StateOrProvince ) );
			
			// shipping
			update_user_meta( $user_id, 'shipping_first_name', 	$user_firstname );
			update_user_meta( $user_id, 'shipping_last_name', 	$user_lastname );
			update_user_meta( $user_id, 'shipping_company', 	stripslashes( $shipping_details->CompanyName ) );
			update_user_meta( $user_id, 'shipping_address_1', 	stripslashes( $shipping_details->Street1 ) );
			update_user_meta( $user_id, 'shipping_address_2', 	stripslashes( $shipping_details->Street2 ) );
			update_user_meta( $user_id, 'shipping_city', 		stripslashes( $shipping_details->CityName ) );
			update_user_meta( $user_id, 'shipping_postcode', 	stripslashes( $shipping_details->PostalCode ) );
			update_user_meta( $user_id, 'shipping_country', 	stripslashes( $shipping_details->Country ) );
			update_user_meta( $user_id, 'shipping_state', 		stripslashes( $shipping_details->StateOrProvince ) );
			
			WPLE()->logger->info( "added customer $user_id ".$user_email." ($ebay_user_id) " );

		}

		do_action( 'wplister_created_customer_from_order', $user_id, $details );

		return $user_id;

	} // addCustomer()


	function disableEmailNotifications() {

		// prevent WooCommerce from sending out notification emails when updating order status
		if ( get_option( 'wplister_disable_new_order_emails' ) )
			add_filter( 'woocommerce_email_enabled_new_order', array( $this, 'returnFalse' ), 10, 2 );
		if ( get_option( 'wplister_disable_completed_order_emails' ) )
			add_filter( 'woocommerce_email_enabled_customer_completed_order', array( $this, 'returnFalse' ), 10, 2 );
		if ( get_option( 'wplister_disable_processing_order_emails' ) )
			add_filter( 'woocommerce_email_enabled_customer_processing_order', array( $this, 'returnFalse' ), 10, 2 );

	}

	function returnFalse( $param1, $param2 = false ) {
		return false;
	}

	/**
	 * Calculate the taxes based on the product's tax class and the order's shipping address
	 *
	 * @param float $product_price
	 * @param int $product_id
	 * @param int $quantity
	 * @param int $order_id
	 * @return array
	 */
	public function getProductTax( $product_price, $product_id, $quantity, $order_id ) {
		global $woocommerce;
		WPLE()->logger->info( "calling getProductTax( $product_price, $product_id, $quantity, $order_id )" );

		// if auto-detect is disabled, defer to the previous method of tax computation
		if ( 0 == get_option( 'wplister_orders_autodetect_tax_rates', 0 ) ) {
			WPLE()->logger->info( 'Taxes Autodetect disabled' );
			$vat_percent = get_option( 'wplister_orders_fixed_vat_rate' );
			WPLE()->logger->info( 'VAT% (global): ' . $vat_percent );

			$vat_percent = floatval( $vat_percent );

			// convert single price to total price
			$product_price = $product_price * $quantity;

			// get global tax rate id for order item array
			$tax_rate_id = get_option( 'wplister_process_order_tax_rate_id' );
			$vat_tax     = 0;

			if ( $vat_percent ) {
				$vat_tax = $product_price / ( 1 + ( 1 / ( $vat_percent / 100 ) ) );	// calc VAT from gross amount
				$vat_tax = $this->format_decimal( $vat_tax );
			}

			$line_total    = $product_price;
			$line_subtotal = $product_price;

			// adjust item price if prices include tax
			if ( get_option( 'woocommerce_prices_include_tax' ) == 'yes' ) {
				$line_total    = $product_price - $vat_tax;
				$line_subtotal = $product_price - $vat_tax;
			}

			return array(
				'line_total'            => $line_total,
				'line_tax'              => $vat_tax,
				'line_subtotal'         => $line_subtotal,
				'line_subtotal_tax'     => $vat_tax,
				'line_tax_data'         => array(
					'total' 	=> array( $tax_rate_id => $vat_tax ),
					'subtotal' 	=> array( $tax_rate_id => $vat_tax ),
				),
				'tax_rate_id'           => $tax_rate_id,
			);
		} else {
			$this->loadCartClasses();

			$cart       = $woocommerce->cart;
			$product    = wc_get_product( $product_id );
			$order      = wc_get_order( $order_id );
			WPLE()->logger->info( "getProductTax() cart object: ".print_r($cart,1) );

			if ( !$product || !is_object($product) ) {
				return array(
					'line_total'            => $product_price * $quantity,
					'line_tax'              => '0.0',
					'line_subtotal'         => $product_price * $quantity,
					'line_subtotal_tax'     => '0.0',
					'line_tax_data'         => array('total' => array(), 'subtotal' => array())
				);
			}

			$tax_rates      = array();
			$shop_tax_rates = array();

			// set the shipping location to the order's shipping address
			// so WC can determine whether or not this zone is taxable or not
			$woocommerce->customer->set_shipping_location(
				wple_get_order_meta( $order, 'shipping_country' ),
				wple_get_order_meta( $order, 'shipping_state' ),
				wple_get_order_meta( $order, 'shipping_postcode' ),
				wple_get_order_meta( $order, 'shipping_city' )
			);

			// prevent fatal error:
			// Call to a member function needs_shipping() on a non-object in woocommerce/includes/class-wc-customer.php line 333
			add_filter( 'woocommerce_apply_base_tax_for_local_pickup', '__return_false' );

			$line_price         = $product_price * $quantity;
			$line_subtotal      = 0;
			$line_subtotal_tax  = 0;

			$prices_include_tax = get_option( 'woocommerce_prices_include_tax', 'no' );

			// calculate subtotal
			if ( !$product->is_taxable() ) {

				WPLE()->logger->info( "getProductTax() step 1 - not taxable (mode 1)" );

				$line_subtotal = $line_price;

			} elseif ( $prices_include_tax == 'yes' ) {

				// Get base tax rates
				if ( empty( $shop_tax_rates[ $product->get_tax_class() ] ) ) {
					$shop_tax_rates[ $product->get_tax_class() ] = WC_Tax::get_base_tax_rates( $product->get_tax_class() );
				}

				// Get item tax rates
				if ( empty( $tax_rates[ $product->get_tax_class() ] ) ) {
					$tax_rates[ $product->get_tax_class() ] = WC_Tax::get_rates( $product->get_tax_class() );
				}

				$base_tax_rates = $shop_tax_rates[ $product->get_tax_class() ];
				$item_tax_rates = $tax_rates[ $product->get_tax_class() ];

				/**
				 * ADJUST TAX - Calculations when base tax is not equal to the item tax
				 */
				if ( $item_tax_rates !== $base_tax_rates ) {
					WPLE()->logger->info( "getProductTax() step 1 - prices include tax (mode 2a)" );

					// Work out a new base price without the shop's base tax
					$taxes                 = WC_Tax::calc_tax( $line_price, $base_tax_rates, true, true );

					// Now we have a new item price (excluding TAX)
					$line_subtotal         = $line_price - array_sum( $taxes );

					// Now add modifed taxes
					$tax_result            = WC_Tax::calc_tax( $line_subtotal, $item_tax_rates );
					$line_subtotal_tax     = array_sum( $tax_result );

					/**
					 * Regular tax calculation (customer inside base and the tax class is unmodified
					 */
				} else {
					WPLE()->logger->info( "getProductTax() step 1 - prices include tax (mode 2b)" );

					// Calc tax normally
					$taxes                 = WC_Tax::calc_tax( $line_price, $item_tax_rates, true );
					$line_subtotal_tax     = array_sum( $taxes );
					$line_subtotal         = $line_price - array_sum( $taxes );
				}

				/**
				 * Prices exclude tax
				 *
				 * This calculation is simpler - work with the base, untaxed price.
				 */
			} else {

				WPLE()->logger->info( "getProductTax() step 1 - prices exclude tax (mode 3)" );

				// Get item tax rates
				if ( empty( $tax_rates[ $product->get_tax_class() ] ) ) {
					$tax_rates[ $product->get_tax_class() ] = WC_Tax::get_rates( $product->get_tax_class() );
				}

				$item_tax_rates        = $tax_rates[ $product->get_tax_class() ];

				// Base tax for line before discount - we will store this in the order data
				$taxes                 = WC_Tax::calc_tax( $line_price, $item_tax_rates );
				$line_subtotal_tax     = array_sum( $taxes );
				$line_subtotal         = $line_price;
			}

			WPLE()->logger->info( "getProductTax() mid - line_subtotal    : $line_subtotal" );
			WPLE()->logger->info( "getProductTax() mid - line_subtotal_tax: $line_subtotal_tax" );

			// calculate line tax

			// Prices
			$base_price = $product_price;
			$line_price = $product_price * $quantity;

			// Tax data
			$taxes = array();
			$discounted_taxes = array();

			if ( !$product->is_taxable() ) {

				WPLE()->logger->info( "getProductTax() step 2 - not taxable (mode 1)" );

				// Discounted Price (price with any pre-tax discounts applied)
				$discounted_price      = $base_price;
				$line_subtotal_tax     = 0;
				$line_subtotal         = $line_price;
				$line_tax              = 0;
				$line_total            = wc_round_tax_total( $discounted_price * $quantity );

				/**
				 * Prices include tax
				 */
				// } elseif ( $cart->prices_include_tax ) { // this doesn't work - $cart is empty!
			} elseif ( $prices_include_tax == 'yes' ) {

				$base_tax_rates = $shop_tax_rates[ $product->get_tax_class() ];
				$item_tax_rates = $tax_rates[ $product->get_tax_class() ];

				/**
				 * ADJUST TAX - Calculations when base tax is not equal to the item tax
				 */
				if ( $item_tax_rates !== $base_tax_rates ) {

					WPLE()->logger->info( "getProductTax() step 2 - prices include tax (mode 2a)" );

					// Work out a new base price without the shop's base tax
					$taxes             = WC_Tax::calc_tax( $line_price, $base_tax_rates, true, true );

					// Now we have a new item price (excluding TAX)
					$line_subtotal     = wc_round_tax_total( $line_price - array_sum( $taxes ) );

					// Now add modifed taxes
					$taxes             = WC_Tax::calc_tax( $line_subtotal, $item_tax_rates );
					$line_subtotal_tax = array_sum( $taxes );

					// Adjusted price (this is the price including the new tax rate)
					$adjusted_price    = ( $line_subtotal + $line_subtotal_tax ) / $quantity;

					// Apply discounts
					$discounted_price  = $adjusted_price;
					$discounted_taxes  = WC_Tax::calc_tax( $discounted_price * $quantity, $item_tax_rates, true );
					$line_tax          = array_sum( $discounted_taxes );
					$line_total        = ( $discounted_price * $quantity ) - $line_tax;

					/**
					 * Regular tax calculation (customer inside base and the tax class is unmodified
					 */
				} else {

					WPLE()->logger->info( "getProductTax() step 2 - prices include tax (mode 2b)" );

					// Work out a new base price without the shop's base tax
					$taxes             = WC_Tax::calc_tax( $line_price, $item_tax_rates, true );

					// Now we have a new item price (excluding TAX)
					$line_subtotal     = $line_price - array_sum( $taxes );
					$line_subtotal_tax = array_sum( $taxes );

					// Calc prices and tax (discounted)
					$discounted_price = $base_price;
					$discounted_taxes = WC_Tax::calc_tax( $discounted_price * $quantity, $item_tax_rates, true );
					$line_tax         = array_sum( $discounted_taxes );
					$line_total       = ( $discounted_price * $quantity ) - $line_tax;
				}

				/**
				 * Prices exclude tax
				 */
			} else {

				WPLE()->logger->info( "getProductTax() step 2 - prices exclude tax (mode 3)" );

				$item_tax_rates        = $tax_rates[ $product->get_tax_class() ];

				// Work out a new base price without the shop's base tax
				$taxes                 = WC_Tax::calc_tax( $line_price, $item_tax_rates );

				// Now we have the item price (excluding TAX)
				$line_subtotal         = $line_price;
				$line_subtotal_tax     = array_sum( $taxes );

				// Now calc product rates
				$discounted_price      = $base_price;
				$discounted_taxes      = WC_Tax::calc_tax( $discounted_price * $quantity, $item_tax_rates );
				$discounted_tax_amount = array_sum( $discounted_taxes );
				$line_tax              = $discounted_tax_amount;
				$line_total            = $discounted_price * $quantity;
			}

			$tax_rate_id = '';

			foreach ( $item_tax_rates as $rate_id => $rate ) {
				$tax_rate_id = $rate_id;
				break;
			}

			WPLE()->logger->info( "getProductTax() end - line_subtotal    : $line_subtotal" );
			WPLE()->logger->info( "getProductTax() end - line_subtotal_tax: $line_subtotal_tax" );

			return array(
				'tax_rate_id'           => $tax_rate_id,
				'line_total'            => $line_total,
				'line_tax'              => $line_tax,
				'line_subtotal'         => $line_subtotal,
				'line_subtotal_tax'     => $line_subtotal_tax,
				'line_tax_data'         => array('total' => $discounted_taxes, 'subtotal' => $taxes )
			);
		}
	} // getProductTax()

	public function getProductTaxFromProfile( $listing, $product_price, $quantity ) {
		$vat_enabled = $listing && $listing->profile_data['details']['tax_mode'] == 'fix' ? true : false;
		$vat_percent = $vat_enabled && $listing->profile_data['details']['vat_percent']
			? $listing->profile_data['details']['vat_percent']
			: get_option( 'wplister_orders_fixed_vat_rate' );
		WPLE()->logger->info( 'VAT%: ' . $vat_percent.' - ' . ($vat_enabled ? 'profile' : 'fallback') );

		$vat_percent = floatval( $vat_percent );

		// convert single price to total price
		$product_price = $product_price * $quantity;

		// get global tax rate id for order item array
		$tax_rate_id = get_option( 'wplister_process_order_tax_rate_id' );
		$vat_tax     = 0;

		if ( $vat_percent ) {
			$vat_tax = $product_price / ( 1 + ( 1 / ( $vat_percent / 100 ) ) );	// calc VAT from gross amount
			$vat_tax = $this->format_decimal( $vat_tax );
		}

		$line_total    = $product_price;
		$line_subtotal = $product_price;

		// adjust item price if prices include tax
		if ( get_option( 'woocommerce_prices_include_tax' ) == 'yes' ) {
			$line_total    = $product_price - $vat_tax;
			$line_subtotal = $product_price - $vat_tax;
		}

		return array(
			'line_total'            => $line_total,
			'line_tax'              => $vat_tax,
			'line_subtotal'         => $line_subtotal,
			'line_subtotal_tax'     => $vat_tax,
			'line_tax_data'         => array(
				'total' 	=> array( $tax_rate_id => $vat_tax ),
				'subtotal' 	=> array( $tax_rate_id => $vat_tax ),
			),
			'tax_rate_id'           => $tax_rate_id,
		);
	}

    /**
     * Adds a 'tax' line item to the specified order
     *
     * @param int   $order_id
     * @param int   $tax_rate_id
     * @param float $tax_amount
     * @param float $shipping_tax_amount
     * @return void
     */
	private function addOrderLineTax( $order_id, $tax_rate_id, $tax_amount = 0, $shipping_tax_amount = 0 ) {
	    global $wpdb;

        // get tax rate
        $tax_rate    = $wpdb->get_row( "SELECT tax_rate_id, tax_rate_country, tax_rate_state, tax_rate_name, tax_rate_priority FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_id = '$tax_rate_id'" );
        WPLE()->logger->debug( '$tax_rate: '. print_r( $tax_rate, true ) );

        $code      = WC_Tax::get_rate_code( $tax_rate_id );
        $tax_code  = $code ? $code : __('VAT','wplister');
        $tax_label = $tax_rate_id ? $tax_rate->tax_rate_name : WC()->countries->tax_or_vat();

        $item_id = wc_add_order_item( $order_id, array(
            'order_item_name' 		=> $tax_code,
            'order_item_type' 		=> 'tax'
        ) );
        WPLE()->logger->info( 'Added order tax item: '. $item_id );

        // Add line item meta
        if ( $item_id ) {
            wc_add_order_item_meta( $item_id, 'compound', 0 );
            wc_add_order_item_meta( $item_id, 'tax_amount', $this->format_decimal( $tax_amount ) );
            wc_add_order_item_meta( $item_id, 'shipping_tax_amount', $this->format_decimal( $shipping_tax_amount ) );

            wc_add_order_item_meta( $item_id, 'rate_id', $tax_rate_id );
            wc_add_order_item_meta( $item_id, 'label', $tax_label );
        }
    }

	/**
	 * Include cart files because WC only preloads them when the request
	 * is coming from the frontend
	 */
	public function loadCartClasses() {
		global $woocommerce;

		if ( file_exists($woocommerce->plugin_path() .'/classes/class-wc-cart.php') ) {
			require_once $woocommerce->plugin_path() .'/classes/abstracts/abstract-wc-session.php';
			require_once $woocommerce->plugin_path() .'/classes/class-wc-session-handler.php';
			require_once $woocommerce->plugin_path() .'/classes/class-wc-cart.php';
			require_once $woocommerce->plugin_path() .'/classes/class-wc-checkout.php';
			require_once $woocommerce->plugin_path() .'/classes/class-wc-customer.php';
		} else {
			require_once $woocommerce->plugin_path() .'/includes/abstracts/abstract-wc-session.php';
			require_once $woocommerce->plugin_path() .'/includes/class-wc-session-handler.php';
			require_once $woocommerce->plugin_path() .'/includes/class-wc-cart.php';
			require_once $woocommerce->plugin_path() .'/includes/class-wc-checkout.php';
			require_once $woocommerce->plugin_path() .'/includes/class-wc-customer.php';
		}

		if (! $woocommerce->session ) {
			$woocommerce->session = new WC_Session_Handler();
		}

		if (! $woocommerce->customer ) {
			$woocommerce->customer = new WC_Customer();
		}
	} // loadCartClasses()
} // class WPL_WooOrderBuilder
// $WPL_WooOrderBuilder = new WPL_WooOrderBuilder();

## END PRO ##


