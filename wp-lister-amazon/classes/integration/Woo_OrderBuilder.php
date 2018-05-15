<?php

class WPLA_OrderBuilder {

	var $vat_enabled = false;
	var $vat_total   = 0;
    var $vat_rates   = array();
    var $shipping_taxes = array();


	//
	// update woo order from ebay order
	// 
	function updateOrderFromAmazonOrder( $id, $post_id = false ) {
		// WPLA()->logger->info( 'updateOrderFromAmazonOrder #'.$id );

		// get order details
		$ordersModel = new WPLA_OrdersModel();		
		$item        = $ordersModel->getItem( $id );
		$details     = $item['details'];
		if ( ! $post_id ) $post_id = $item['post_id'];

		// prevent WooCommerce from sending out notification emails when updating order status
		$this->disableEmailNotifications();

		// get order
		$order = wc_get_order( $post_id );
		if ( ! $order ) return false;
		
		
		// do nothing if order is already marked as completed, refunded, cancelled or failed
		// if ( $order->status == 'completed' ) return $post_id;
		if ( in_array( $order->get_status(), array( 'completed', 'cancelled', 'refunded', 'failed' ) ) ) return $post_id;

		// the above blacklist won't work for custom order statuses created by the WooCommerce Order Status Manager extension
		// a custom order status should be left untouched as it probably serves a custom purpose - so whitelist all values used by WP-Lister:
		if ( ! in_array( $order->get_status(), array( 'pending', 'processing', 'on-hold', 'completed' ) ) ) return $post_id;

		// order status
		if ( $item['status'] == 'Unshipped') { // TODO: what's the status when payment is complete?
			// unshipped orders: use config
			$new_order_status = get_option( 'wpla_new_order_status', 'processing' );
		} elseif ( $item['status'] == 'Shipped') {
			// shipped orders: use config 
			$new_order_status = get_option( 'wpla_shipped_order_status', 'completed' );
		} else {
			// anything else: on hold
			$new_order_status = 'on-hold';
		}

		// update order status
		if ( $order->get_status() != $new_order_status ) {

			$history_message = "Order #$post_id status was updated from {$order->get_status()} to $new_order_status";
			$history_details = array( 'post_id' => $post_id );
			WPLA_OrdersImporter::addHistory( $item['order_id'], 'update_order', $history_message, $history_details );

			$order->update_status( $new_order_status );

			// update order creation date
			$timestamp     = strtotime($item['date_created'].' UTC');
			$post_date     = WPLA_DateTimeHelper::convertTimestampToLocalTime( $timestamp );
			$post_date_gmt = date_i18n( 'Y-m-d H:i:s', $timestamp, true );

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
		}

		return $post_id;
	} // updateOrderFromAmazonOrder()



	//
	// create woo order from amazon order
	// 
	function createWooOrderFromAmazonOrder( $id ) {
		global $wpdb;

		// get order details
		$ordersModel = new WPLA_OrdersModel();		
		$item        = $ordersModel->getItem( $id );
		$details     = $item['details'];

		$timestamp     = strtotime($item['date_created'].' UTC');
		$post_date     = WPLA_DateTimeHelper::convertTimestampToLocalTime( $timestamp );
		$post_date_gmt = date_i18n( 'Y-m-d H:i:s', $timestamp, true );
		// $date_created  = $item['date_created'];
		// $post_date_gmt = date_i18n( 'Y-m-d H:i:s', strtotime($item['date_created'].' UTC'), true );
		// $post_date     = date_i18n( 'Y-m-d H:i:s', strtotime($item['date_created'].' UTC'), false );

		// create order comment
		$order_comment = sprintf( __( 'Amazon Order ID: %s', 'wpla' ), $item['order_id'] );

		// Create shop_order post object
        $post_data = apply_filters( 'wpla_order_post_data', array(
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
		// $ordersModel->updateOrder( $id, array( 'post_id' => $post_id ) );

		/* the following code is inspired by woocommerce_process_shop_order_meta() in writepanel-order_data.php */

		// Set the currency first before anything else! #16900
        update_post_meta( $post_id, '_order_currency', $details->OrderTotal->CurrencyCode );

		// Add key
		add_post_meta( $post_id, '_order_key',    'wc_' . uniqid('order_'), true );
		add_post_meta( $post_id, '_created_via',  'amazon', true );
		add_post_meta( $post_id, '_order_version', WC_VERSION, true );

		// Update post data
		// update_post_meta( $post_id, '_transaction_id', $id );
		// update_post_meta( $post_id, '_amazon_item_id', $item['item_id'] );
		update_post_meta( $post_id, '_wpla_amazon_order_id', $item['order_id'] );


		$billing_details = $details->ShippingAddress;
		$shipping_details = $details->ShippingAddress;

		// optional billing address / RegistrationAddress
		// if ( isset( $details->Buyer->RegistrationAddress ) ) {
		// 	$billing_details = $details->Buyer->RegistrationAddress;
		// }

		// if AddressLine1 is missing or empty, use AddressLine2 instead
		if ( empty( $billing_details->AddressLine1 ) ) {
			$billing_details->AddressLine1 = @$billing_details->AddressLine2;
			$billing_details->AddressLine2 = '';
		}
		if ( empty( $shipping_details->AddressLine1 ) ) {
			$shipping_details->AddressLine1 = @$shipping_details->AddressLine2;
			$shipping_details->AddressLine2 = '';
		}

		// optional fields
		if ($billing_details->Phone == 'Invalid Request') $billing_details->Phone = '';
		update_post_meta( $post_id, '_billing_phone', stripslashes( $billing_details->Phone ));

		// billing address
		@list( $billing_firstname, $billing_lastname )     = explode( " ", $details->BuyerName, 2 );
		update_post_meta( $post_id, '_billing_first_name', 	stripslashes( $billing_firstname ) );
		update_post_meta( $post_id, '_billing_last_name', 	stripslashes( $billing_lastname ) );
		// update_post_meta( $post_id, '_billing_company', 	stripslashes( $billing_details->CompanyName ) );
		update_post_meta( $post_id, '_billing_address_1', 	stripslashes( @$billing_details->AddressLine1 ) );
		update_post_meta( $post_id, '_billing_address_2', 	stripslashes( @$billing_details->AddressLine2 ) );
		update_post_meta( $post_id, '_billing_city', 		stripslashes( @$billing_details->City ) );
		update_post_meta( $post_id, '_billing_postcode', 	stripslashes( @$billing_details->PostalCode ) );
		update_post_meta( $post_id, '_billing_country', 	stripslashes( @$billing_details->CountryCode ) );
		update_post_meta( $post_id, '_billing_state', 		stripslashes( WPLA_CountryHelper::get_state_two_letter_code( @$billing_details->StateOrRegion ) ) );
		
		// shipping address
		@list( $shipping_firstname, $shipping_lastname )   = explode( " ", $shipping_details->Name, 2 );
		update_post_meta( $post_id, '_shipping_first_name', stripslashes( $shipping_firstname ) );
		update_post_meta( $post_id, '_shipping_last_name', 	stripslashes( $shipping_lastname ) );
		// update_post_meta( $post_id, '_shipping_company', 	stripslashes( $shipping_details->CompanyName ) );
		update_post_meta( $post_id, '_shipping_address_1', 	stripslashes( @$shipping_details->AddressLine1 ) );
		update_post_meta( $post_id, '_shipping_address_2', 	stripslashes( @$shipping_details->AddressLine2 ) );
		update_post_meta( $post_id, '_shipping_city', 		stripslashes( @$shipping_details->City ) );
		update_post_meta( $post_id, '_shipping_postcode', 	stripslashes( @$shipping_details->PostalCode ) );
		update_post_meta( $post_id, '_shipping_country', 	stripslashes( @$shipping_details->CountryCode ) );
		update_post_meta( $post_id, '_shipping_state', 		stripslashes( WPLA_CountryHelper::get_state_two_letter_code( @$shipping_details->StateOrRegion ) ) );

		// convert state names to ISO code
		self::fixCountryStates( $post_id );

		// email address - if enabled
		if ( ! get_option('wpla_create_orders_without_email') ) {
			update_post_meta( $post_id, '_billing_email', 	$item['buyer_email'] );
		}
		
		// order details
		update_post_meta( $post_id, '_cart_discount', 		'0');
		update_post_meta( $post_id, '_order_discount', 		'0');
		update_post_meta( $post_id, '_order_tax', 			'0.00' );
		update_post_meta( $post_id, '_order_shipping_tax', 	'0.00' );
		update_post_meta( $post_id, '_customer_user', 		'0' );
		// update_post_meta( $post_id, '_prices_include_tax', 	'yes' );
		update_post_meta( $post_id, '_prices_include_tax', 	get_option( 'woocommerce_prices_include_tax' ) );


		// Order Total
		$order_total = $details->OrderTotal->Amount;
		update_post_meta( $post_id, '_order_total', rtrim(rtrim(number_format( $order_total, 4, '.', ''), '0'), '.') );

		// WC_Order::save() is only available in WC3.0+ #22301
		$wc_order = wc_get_order( $post_id );
		if ( is_callable( array( $wc_order, 'save' ) ) ) {
            $wc_order->set_total( $order_total );
            $wc_order->save();
        }

		// Payment method handling
		$payment_method = $details->PaymentMethod;
		if ( $payment_method == 'PayPal' ) $payment_method = 'paypal'; // TODO: more mapping
		if ( $payment_method == 'Other'  ) $payment_method = get_option( 'wpla_orders_default_payment_title', 'Other' );
		update_post_meta( $post_id, '_payment_method', $payment_method ); 
		update_post_meta( $post_id, '_payment_method_title', $payment_method );
	

		// Order line item(s)
		$this->processOrderLineItems( $item['items'], $post_id );

		// shipping info
		$this->processOrderShipping( $post_id, $item );

		// process tax
		$this->processOrderVAT( $post_id, $item );


		// prevent WooCommerce from sending out notification emails when updating order status or creating customers
		$this->disableEmailNotifications();

		// create user account for customer - if enabled
		if ( get_option( 'wpla_create_customers' ) ) {
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
		$order = function_exists( 'wc_get_order' ) ? wc_get_order( $post_id ) : new WC_Order( $post_id );

		// would be nice if this worked:
		// $order->calculate_taxes();
		// $order->update_taxes();
		
		// order status
		if ( $item['status'] == 'Unshipped') { // TODO: what's the status when payment is complete?
			// unshipped orders: use config
			$new_order_status = get_option( 'wpla_new_order_status', 'processing' );
			$order->update_status( $new_order_status );
		} elseif ( $item['status'] == 'Shipped') {
			// shipped orders: use config 
			$new_order_status = get_option( 'wpla_shipped_order_status', 'completed' );
			$order->update_status( $new_order_status );
		} else {
			// anything else: on hold
			$new_order_status = 'on-hold';
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

		// fix the completed date for completed orders - which is set to the current time by update_status()
		if ( $new_order_status == 'completed' ) {
			update_post_meta( $post_id, '_completed_date', $post_date );
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

		do_action( 'wpla_after_create_order_with_nonexisting_items', $post_id );
		do_action( 'wpla_after_create_order', $post_id );

		// trigger WooCommerce webhook order.created - by simulating an incoming WC REST API request
		do_action( 'woocommerce_api_create_order', $post_id, array(), $order );

		return $post_id;

	} // createWooOrderFromAmazonOrder()


	// convert country state names to ISO code (New South Wales -> NSW or Quebec -> QC)
	function fixCountryStates( $post_id ) {
		if ( ! class_exists('WC_Countries') ) return; // requires WC2.3+

		$billing_country_code = get_post_meta( $post_id, '_billing_country', true );
		$billing_state_name   = get_post_meta( $post_id, '_billing_state', true );
		$country_states       = WC()->countries->get_states( $billing_country_code );
		$state_code           = $country_states ? array_search( strtolower($billing_state_name), array_map('strtolower',$country_states) ) : false; // case insensitive array_search()
		if ( $state_code ) {
			update_post_meta( $post_id, '_billing_state', $state_code );
		}

		$shipping_country_code = get_post_meta( $post_id, '_shipping_country', true );
		$shipping_state_name   = get_post_meta( $post_id, '_shipping_state', true );
		$country_states        = WC()->countries->get_states( $shipping_country_code );
		$state_code            = $country_states ? array_search( strtolower($shipping_state_name), array_map('strtolower',$country_states) ) : false; // case insensitive array_search()
		if ( $state_code ) {
			update_post_meta( $post_id, '_shipping_state', $state_code );
		}

	} // fixCountryStates()


	// process shipping info - create shipping line item
	function processOrderShipping( $post_id, $item ) {
		
		// shipping fee (gross)
		$shipping_total = $this->getShippingTotal( $item['items'] );

        /* Deprecated code - shipping tax can now be calculated using the autodetect feature
        // calculate shipping tax amount (VAT is usually applied to shipping fee)
        $shipping_tax_amount = 0;
        if ( $this->vat_enabled ) {
            $vat_percent         = get_option( 'wpla_orders_fixed_vat_rate' );

            if ( ! empty( $vat_percent ) ) {
                $shipping_tax_amount = $shipping_total / ( 1 + ( 1 / ( $vat_percent / 100 ) ) );	// calc VAT from gross amount
                $shipping_tax_amount = $vat_percent ? $shipping_tax_amount : 0;						// disable VAT if no percentage set
            }

            $shipping_total = $shipping_total - $shipping_tax_amount;
        }*/

		// calculate shipping tax amount (VAT is usually applied to shipping fee)
        $shipping_tax_amount = $this->calculateShippingTaxAmount( $shipping_total, $post_id );

        if ( 'import' != get_option( 'wpla_orders_tax_mode', 'none' ) ) {
            // Do not deduct tax from shipping total if importing taxes from Amazon #20062
            $shipping_total = $shipping_total - $shipping_tax_amount;
        }

		// update shipping total (gross - without taxes)
		update_post_meta( $post_id, '_order_shipping', $shipping_total );

		// shipping method
		$details = $item['details'];
		$shipping_method_id_map    = apply_filters( 'wpla_shipping_service_id_map', array() );
		$shipping_method_id        = array_key_exists($details->ShipServiceLevel, $shipping_method_id_map) ? $shipping_method_id_map[$details->ShipServiceLevel] : $details->ShipServiceLevel;
		$shipping_method_title_map = apply_filters( 'wpla_shipping_service_title_map', array() );
		$shipping_method_title     = array_key_exists($details->ShipServiceLevel, $shipping_method_title_map) ? $shipping_method_title_map[$details->ShipServiceLevel] : $details->ShipServiceLevel;
		// this only works up to WC2.1:
		// update_post_meta( $post_id, '_shipping_method', 	  $shipping_method_id );
		// update_post_meta( $post_id, '_shipping_method_title', $shipping_method_title );

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
		 	wc_add_order_item_meta( $item_id, 'total_tax', array_sum( $shipping_taxes['total'] ) );
		}

		// filter usage:
		// add_filter( 'wpla_shipping_service_title_map', 'my_amazon_shipping_service_title_map' );
		// function my_amazon_shipping_service_title_map( $map ) {
		// 	$map = array_merge( $map, array(
		// 		'Std DE Dom' => 'DHL Paket'
		// 	));
		// 	return $map;
		// }
		// add_filter( 'wpla_shipping_service_id_map', 'my_amazon_shipping_service_id_map' );
		// function my_amazon_shipping_service_id_map( $map ) {
		// 	$map = array_merge( $map, array(
		// 		'Std DE Dom' => 'flat_rate'
		// 	));
		// 	return $map;
		// }

	} // processOrderShipping()

    // calculate shipping tax amount based on global VAT rate
    // (VAT is usually applied to shipping fee)
    function calculateShippingTaxAmount( $shipping_total, $post_id ) {
        WPLA()->logger->info( '[tax] calculateShippingTaxAmount('. $shipping_total .', '. $post_id .')' );

        // get global VAT rate
        $tax_mode           = get_option( 'wpla_orders_tax_mode' );
        $vat_percent        = get_option( 'wpla_orders_fixed_vat_rate' );
        $shipping_tax_amount= 0;

        WPLA()->logger->info( '[tax] tax_mode: '. $tax_mode );
        WPLA()->logger->info( '[tax] vat_percent: '. $vat_percent );

        if ( ! $tax_mode ) {
            return 0;
        } elseif ( $tax_mode == 'autodetect' ) {
            $order = wc_get_order( $post_id );
            $matched_tax_rates = WC_Tax::find_shipping_rates( array(
                'country' 	=> wpla_get_order_meta( $order, 'shipping_country' ),
                'state' 	=> wpla_get_order_meta( $order, 'shipping_state' ),
                'postcode' 	=> wpla_get_order_meta( $order, 'shipping_postcode' ),
                'city' 		=> wpla_get_order_meta( $order, 'shipping_city' )
            ) );

            $shipping_taxes = array();
            WPLA()->logger->info('[tax] Matched rates: '. print_r($matched_tax_rates, true));
            foreach ( $matched_tax_rates as $key => $rate ) {
                //if ( $rate['compound'] == 'yes' || $rate['shipping'] != 'yes' ) {
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
        } elseif ( $tax_mode == 'fixed' && $vat_percent ) {
            // calculate VAT
            $tax_rate_id        = get_option( 'wpla_orders_tax_rate_id' );
            $shipping_tax_amount = $shipping_total / ( 1 + ( 1 / ( $vat_percent / 100 ) ) );	// calc VAT from gross amount
            $shipping_taxes     = $shipping_tax_amount == 0 ? array() : array( $tax_rate_id => $shipping_tax_amount );

            WPLA()->logger->info( '[tax] tax_rate_id: '. $tax_rate_id );
        } elseif ( $tax_mode == 'import' ) {
            // Amazon shipping taxes are stored in WPLA_OrderBuilder::shipping_taxes
            return array_sum( $this->shipping_taxes );
        }

        if ( !empty( $shipping_taxes ) ) {
            $this->shipping_taxes = $shipping_taxes;
            $shipping_tax_amount = array_sum( $shipping_taxes );
        }

        WPLA()->logger->info( '[tax] shipping_tax_amount: '. $shipping_tax_amount );

        return $shipping_tax_amount;
    }

	function processOrderVAT( $post_id, $item ) {
		global $wpdb;

		$tax_mode           = get_option( 'wpla_orders_tax_mode' );

		WPLA()->logger->info( '[tax] processOrderVAT('. $post_id .')' );
		WPLA()->logger->info( '[tax] tax_mode: '. $tax_mode );
		WPLA()->logger->info( '[tax] vat_rates: '. print_r( $this->vat_rates, true ) );
		WPLA()->logger->info( '[tax] shipping_taxes: '. print_r( $this->shipping_taxes, true ) );

        // don't add VAT tax mode isn't set or disabled
        if ( ! $tax_mode ) {
            return;
        }

		// shipping fee (gross)
		$shipping_total      = $this->getShippingTotal( $item['items'] );

        // calculate shipping tax (from gross amount)
        $shipping_tax_amount = $this->calculateShippingTaxAmount( $shipping_total, $post_id );

        // store shipping taxes separately if $vat_rates is empty #17729
        if ( empty( $this->vat_rates ) && !empty( $this->shipping_taxes ) ) {
            foreach ( $this->shipping_taxes as $rate_id => $tax_amount ) {
                $this->addOrderLineTax( $post_id, $rate_id, 0, $tax_amount );
            }
        } else {
            foreach ( $this->vat_rates as $tax_rate_id => $tax_amount ) {
                // Pull the correct shipping tax for the current tax rate
                $shipping_tax = isset( $this->shipping_taxes[ $tax_rate_id ] ) ? $this->shipping_taxes[ $tax_rate_id ] : 0;

                // remove shipping taxes that have been already recorded
                unset( $this->shipping_taxes[ $tax_rate_id ] );

                $this->addOrderLineTax( $post_id, $tax_rate_id, $tax_amount, $shipping_tax );
            }

            // record other shipping taxes
            foreach ( $this->shipping_taxes as $rate_id => $tax_amount ) {
                $this->addOrderLineTax( $post_id, $rate_id, 0, $tax_amount );
            }
        }

        /* Moved this chunk of code to self::addOrderLineTax()
        if ( 0 == $autodetect_taxes ) {
            // don't add VAT if no tax rate set
            if ( !$this->vat_enabled || !$tax_rate_id ) {
                return;
            }

            // calculate shipping tax amount (VAT is usually applied to shipping fee)
            $vat_percent         = get_option( 'wpla_orders_fixed_vat_rate' );
            $shipping_tax_amount = $shipping_total / ( 1 + ( 1 / ( $vat_percent / 100 ) ) );	// calc VAT from gross amount
            $shipping_tax_amount = $vat_percent ? $shipping_tax_amount : 0;						// disable VAT if no percentage set

            // get tax rate
            $tax_rate    = $wpdb->get_row( "SELECT tax_rate_id, tax_rate_country, tax_rate_state, tax_rate_name, tax_rate_priority FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_id = '$tax_rate_id'" );

            $code      = WC_Tax::get_rate_code( $tax_rate_id );
            $tax_code  = $code ? $code : __('VAT','wpla');
            $tax_label = $tax_rate_id ? $tax_rate->tax_rate_name : WC()->countries->tax_or_vat();

            $item_id = wc_add_order_item( $post_id, array(
                'order_item_name' 		=> $tax_code,
                'order_item_type' 		=> 'tax'
            ) );

            // Add line item meta
            if ( $item_id ) {
                wc_add_order_item_meta( $item_id, 'compound', 0 );
                wc_add_order_item_meta( $item_id, 'tax_amount', $this->format_decimal( $this->vat_total ) );
                wc_add_order_item_meta( $item_id, 'shipping_tax_amount', $this->format_decimal( $shipping_tax_amount ) );

                if ( $tax_rate_id ) {
                    wc_add_order_item_meta( $item_id, 'rate_id', $tax_rate_id );
                    wc_add_order_item_meta( $item_id, 'label', $tax_label );
                }
            }
        } else {
            // attempt to figure the taxes (order and shipping) from the tax rules set in WC
            // $order_taxes = $this->getOrderTaxes( $post_id );
            $location   = self::getOrderTaxAddress( $post_id );

            $rates          = WC_Tax::find_rates( $location );
            $shipping_rates = array();
            if ( is_array( $rates ) ) {
                foreach ( $rates as $key => $rate ) {
                    if ( 'yes' === $rate['shipping'] ) {
                        $shipping_rates[ $key ] = $rate;
                    }
                }
            }

            $shipping_taxes = WC_Tax::calc_shipping_tax( $shipping_total, $shipping_rates );

            WPLA()->logger->debug( 'vat_rates: ' . print_r( $this->vat_rates, true ) );
            foreach ( $this->vat_rates as $tax_rate_id => $tax_amount ) {
                // get tax rate
                $tax_rate    = $wpdb->get_row( "SELECT tax_rate_id, tax_rate_country, tax_rate_state, tax_rate_name, tax_rate_priority FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_id = '$tax_rate_id'" );
                WPLA()->logger->debug( '$tax_rate: '. print_r( $tax_rate, true ) );

                $code      = WC_Tax::get_rate_code( $tax_rate_id );
                $tax_code  = $code ? $code : __('VAT','wpla');
                $tax_label = $tax_rate_id ? $tax_rate->tax_rate_name : WC()->countries->tax_or_vat();

                $item_id = wc_add_order_item( $post_id, array(
                    'order_item_name' 		=> $tax_code,
                    'order_item_type' 		=> 'tax'
                ) );
                WPLA()->logger->info( 'Added order item: '. $item_id );

                // Add line item meta
                if ( $item_id ) {
                    wc_add_order_item_meta( $item_id, 'compound', 0 );
                    wc_add_order_item_meta( $item_id, 'tax_amount', $this->format_decimal( $tax_amount ) );

                    if ( isset( $shipping_taxes[ $tax_rate_id ] ) ) {
                        $shipping_tax_amount += $shipping_taxes[ $tax_rate_id ];
                        wc_add_order_item_meta( $item_id, 'shipping_tax_amount', $this->format_decimal( $shipping_taxes[ $tax_rate_id ] ) );
                    }

                    // if ( $tax_rate_id ) {
                    wc_add_order_item_meta( $item_id, 'rate_id', $tax_rate_id );
                    wc_add_order_item_meta( $item_id, 'label', $tax_label );
                    // }
                }
            }
        }*/

        // store total order tax
        WPLA()->logger->info( '[tax] Storing _order_tax: '. $this->vat_total );
        WPLA()->logger->info( '[tax] Storing _order_shipping_tax: '. $shipping_tax_amount );
        update_post_meta( $post_id, '_order_tax', $this->format_decimal( $this->vat_total ) );
        update_post_meta( $post_id, '_order_shipping_tax', $this->format_decimal( $shipping_tax_amount ) );

        // if autodetect taxes is enabled and woocommerce_prices_include_tax is disabled,
        // add the tax total to the order total #15043
        //
        // Added the 'wplister_include_vat_in_order_total' filter to allow external code to prevent VAT from being added to the order total #16294
        if ( $tax_mode == 'autodetect' && get_option( 'woocommerce_prices_include_tax', 'no' ) == 'no' && apply_filters( 'wpla_include_vat_in_order_total', true, $post_id, $item ) ) {
            $order_total = get_post_meta( $post_id, '_order_total', true );
            update_post_meta( $post_id, '_order_total', $order_total + $this->vat_total );
        }
	} // processOrderVAT()

	function createOrderLineItem( $item, $post_id ) {

		// get listing item from db
		$listingsModel = new WPLA_ListingsModel();
		$listingItem   = $listingsModel->getItemBySKU( $item->SellerSKU );

		$product_id			= $listingItem ? $listingItem->post_id : '0';
		$item_name 			= $listingItem ? $listingItem->listing_title : $item->Title;
		$item_quantity 		= $item->QuantityOrdered;

		$line_subtotal		= $item->ItemPrice->Amount;
		$line_total 		= $item->ItemPrice->Amount;

		// Record promotional discounts
		if ( 1 == get_option( 'wpla_record_discounts', 0 ) ) {
			if ( isset( $item->PromotionDiscount ) ) {
				$discount_amount = $item->PromotionDiscount->Amount;

				if ( $discount_amount ) {
                    WPLA()->logger->info( '[tax] discount: '. $discount_amount );

					$order_discount = get_post_meta( $post_id, '_cart_discount', true );

					if ( ! $order_discount ) {
						$order_discount = 0;
					}

					$order_discount += $discount_amount;
					update_post_meta( $post_id, '_cart_discount', $order_discount );

					// Reduce the line total so the taxes will be adjusted accordingly #20833
					$line_total     -= $discount_amount;
					$line_subtotal  -= $discount_amount;
				}
			}
		}

        $product_price = $line_total / $item_quantity;

        if ( $item_quantity == 0 ) {
            $line_subtotal = 0;
            $line_total = 0;
            $product_price = 0;
        }

        WPLA()->logger->info( '[tax] createOrderLineItem() for #'. $post_id );
        WPLA()->logger->info( '[tax] quantity: '. $item_quantity );
        WPLA()->logger->info( '[tax] line_total: '. $line_total );
        WPLA()->logger->info( '[tax] product_price: '. $product_price );

		// default to no tax
		$vat_enabled        = false;
		$line_subtotal_tax	= '0.00';
		$line_tax		 	= '0.00';
		$item_tax_class		= '';

		// // regard global VAT processing setting (disabled in favor of new getProductTax() method)
		// $vat_percent = get_option( 'wpla_orders_fixed_vat_rate' );
		// $vat_percent = floatval( $vat_percent );
		// $vat_enabled = $vat_percent > 0 ? true : false;

        $taxes = $this->getProductTax( $product_price, $product_id, $item_quantity, $post_id, $item );
		WPLA()->logger->info( "[tax] getProductTax() returned: ".print_r($taxes,1) );

		if ( $taxes['line_tax'] > 0 ) {
            $vat_enabled = true;
        }

		// process VAT if enabled
		if ( $vat_enabled ) {
			// calculate VAT included in line total
			// $vat_tax = $line_total * $vat_percent / 100; 					// calc VAT from net amount
			//$vat_tax = $line_total / ( 1 + ( 1 / ( $vat_percent / 100 ) ) );	// calc VAT from gross amount
			// WPLA()->logger->info( 'VAT: '.$vat_tax );

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
            /*if ( $tax_rate_id ) {
                WPLA()->logger->info( 'Adding vat '. $vat_tax . ' for rate '. $tax_rate_id );
                @$this->vat_rates[ $tax_rate_id ] += $vat_tax;
                WPLA()->logger->info( 'VAT Rates: '. print_r( $this->vat_rates, 1 ) );
            }*/
            // Use $taxes['line_tax_data'] to store multiple tax rates if available #13585
            if ( is_array( $taxes['line_tax_data']['total'] ) ) {
                foreach ( $taxes['line_tax_data']['total'] as $rate_id => $amount ) {
                    @$this->vat_rates[ $rate_id ] += $amount;
                }
            }

			// $vat_tax = wc_round_tax_total( $vat_tax );
			$vat_tax = $this->format_decimal( $vat_tax );
			WPLA()->logger->info( '[tax] VAT: '.$vat_tax );
			WPLA()->logger->info( '[tax] vat_total: '.$this->vat_total );

			$line_subtotal_tax	= $vat_tax;
			$line_tax		 	= $vat_tax;

			// adjust item price if prices include tax
			// (obsolete, this already happens in getProductTax() and we use the returned $line_total and $line_subtotal now)
			// if prices do not include tax, but VAT is enabled, adjust item price as well (the same happens with shipping fee)
			// if ( get_option( 'woocommerce_prices_include_tax' ) == 'yes' ) {
				// $line_total    = $line_total    - $vat_tax;
				// $line_subtotal = $line_subtotal - $vat_tax;
			// }

			// try to get product object to set tax class
			$_product = WPLA_ProductWrapper::getProduct( $product_id );

			// set tax class
			if ( $_product && is_object($_product) )
				$item_tax_class		= $_product->get_tax_class();

			WPLA()->logger->info( "[tax] tax_class for product ID $product_id: ".$item_tax_class );
		}

		/* Moved to WPLA_OrderBuilder::getProductTax()
		if ( get_option( 'wpla_orders_tax_mode' == 'import' ) ) {
            $tax_rate_id    = get_option( 'wpla_orders_tax_rate_id' );
            $amazon_item_tax = $item->ItemTax->Amount;

            if ( $amazon_item_tax ) {
                $this->vat_enabled = true;
                $this->vat_total   += $amazon_item_tax;
                $line_tax          += $amazon_item_tax;
                $line_subtotal_tax += $amazon_item_tax;

                if ( is_array( $taxes['line_tax_data']['total'] ) ) {
                    @$taxes['line_tax_data']['total'][ $tax_rate_id ] += $amazon_item_tax;
                    @$taxes['line_tax_data']['subtotal'][ $tax_rate_id ] += $amazon_item_tax;
                    @$this->vat_rates[ $tax_rate_id ] += $amazon_item_tax;
                }
            }
        }*/

		// check if item is variation - and get variation_id and parent post_id
		$isVariation = $listingItem ? $listingItem->product_type == 'variation' : false;
		if ( $isVariation ) {
			$product_id   = $listingItem->parent_id;
			$variation_id = $listingItem->post_id;
		}

		// Record last sale date for repricing purposes #10477
        $last_sale_date = false;
        if ( ! empty( $variation_id ) ) {
            $product_post_id    = $variation_id;
            $last_sale_date     = current_time( 'mysql' );
        } elseif ( ! empty( $product_id ) ) {
            $product_post_id    = $product_id;
            $last_sale_date     = current_time( 'mysql' );
        }

        if ( $last_sale_date ) {
            update_post_meta( $product_post_id, '_wpla_last_purchase_date', $last_sale_date );
        }

		// get global tax rate id for order item array (TODO: make this based on product tax)
		//$tax_rate_id = get_option( 'wpla_orders_tax_rate_id' );

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

        $order_item = apply_filters( 'wpla_order_builder_line_item', $order_item, $post_id );

		WPLA()->logger->debug( '[tax] order_item: '. print_r( $order_item, true ) );

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
		 	wc_add_order_item_meta( $item_id, 'SKU', 				$item->SellerSKU );

		 	// handle GiftMessageText
			if ( isset( $item->GiftMessageText ) && $item->GiftMessageText ) {
			 	wc_add_order_item_meta( $item_id, 'Gift Message', 	$item->GiftMessageText );
			}

	 	}

	 	// // add variation attributes as order item meta (WC2.2)
	 	// if ( $item_id && $isVariation ) {
	 	// 	foreach ($VariationSpecifics as $attribute_name => $value) {
		//  	woocommerce_add_order_item_meta( $item_id, $attribute_name,	$value );
	 	// 	}
	 	// }

	 	$this->processGiftWrapOption( $item, $post_id );

	} // createOrderLineItem()

	// process optional gift wrap option
	function processGiftWrapOption( $item, $post_id ) {

		if ( ! isset( $item->GiftWrapLevel ) ) return;

		// gift wrap price and title
		$giftwrap_total        = $item->GiftWrapPrice->Amount;
		$shipping_method_title = 'Gift wrap option: ' . $item->GiftWrapLevel . ' (' . $item->SellerSKU . ')';

		// calculate VAT tax amount
		$giftwrap_tax_amount = 0;
		if ( $this->vat_enabled ) {
			$vat_percent         = get_option( 'wpla_orders_fixed_vat_rate' );
			$giftwrap_tax_amount = $giftwrap_total / ( 1 + ( 1 / ( $vat_percent / 100 ) ) );	// calc VAT from gross amount
			$giftwrap_tax_amount = $vat_percent ? $giftwrap_tax_amount : 0;						// disable VAT if no percentage set
			// $giftwrap_total   = $giftwrap_total - $giftwrap_tax_amount;

			// adjust line item price if prices include tax
			// if ( get_option( 'woocommerce_prices_include_tax' ) == 'yes' ) {
				$giftwrap_total  = $giftwrap_total - $giftwrap_tax_amount;
			// }

			$this->vat_total  	+= $giftwrap_tax_amount;
		}

		// get global tax rate id for order item array (TODO: make this a global option - Shipping Tax Rate)
		$tax_rate_id = get_option( 'wpla_orders_tax_rate_id' );

		// create shipping info as order line items - WC2.2
		$item_id = wc_add_order_item( $post_id, array(
	 		'order_item_name' 		=> $shipping_method_title,
	 		'order_item_type' 		=> 'shipping'
	 	) );
	 	if ( $item_id ) {
		 	wc_add_order_item_meta( $item_id, 'cost', 		$giftwrap_total );
		 	wc_add_order_item_meta( $item_id, 'method_id', $giftwrap_total == 0 ? 'free_shipping' : 'other' );
		 	wc_add_order_item_meta( $item_id, 'taxes', 	$giftwrap_tax_amount == 0 ? array() : array( $tax_rate_id => $giftwrap_tax_amount ) );
		}

	} // processGiftWrapOption()


	function processOrderLineItems( $items, $post_id ) {

		// WC 2.0 only
		if ( ! function_exists('woocommerce_add_order_item_meta') ) return;

		#echo "<pre>";print_r($items);echo"</pre>";die();

		foreach ( $items as $item ) {
			$this->createOrderLineItem( $item, $post_id );
		}

	} // processOrderLineItems()


	function getShippingTotal( $items ) {
		$shipping_total = 0;

		foreach ( $items as $item ) {
			if ( isset( $item->ShippingPrice ) ) {
				$shipping_total += $item->ShippingPrice->Amount;
			}
			if ( isset( $item->ShippingDiscount ) ) {
				$shipping_total -= $item->ShippingDiscount->Amount;
			}
		}
		return $shipping_total;

	} // getShippingTotal()





	public function updateOrder( $order_id, $data ) {
		#...
		// if ( $updated ) {
		// 	$woocommerce->clear_order_transients( $order_id );
		// 	WPLA()->logger->info( "updated order $order_id ($asin): $orders_name " );
		// 	$this->updated_count++;
		// }

		return $order_id;
	}






	/**
	 * addCustomer, adds a new WordPress user account
	 *
	 * @param unknown $customers_name
	 * @return $customers_id
	 */
	public function addCustomer( $user_email, $details ) {
		global $wpdb;
		// WPLA()->logger->info( "addCustomer() - data: ".print_r($details,1) );

		// skip if user_email exists
		if ( $user_id = email_exists( $user_email ) ) {
			// $this->show_message('Error: email already exists: '.$user_email, 1 );
			WPLA()->logger->info( "email already exists $user_email" );
			return $user_id;
		}

		// get user data
		$amazon_user_email  = $details->BuyerEmail;

		// get shipping address with first and last name
		$shipping_details = $details->ShippingAddress;
		@list( $shipping_firstname, $shipping_lastname ) = explode( " ", $shipping_details->Name, 2 );
		$user_firstname  = sanitize_user( $shipping_firstname, true );
		$user_lastname   = sanitize_user( $shipping_lastname, true );
		$user_fullname   = sanitize_user( $shipping_details->Name, true );

		// generate password
		$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );


		// create wp_user
		$wp_user = array(
			'user_login' => $amazon_user_email,
			'user_email' => $user_email,
			'first_name' => $user_firstname,
			'last_name'  => $user_lastname,
			// 'user_registered' => date( 'Y-m-d H:i:s', strtotime($customer['customers_info_date_account_created']) ),
			'user_pass' => $random_password,
			'role' => 'customer'
			);
		$user_id = wp_insert_user( $wp_user ) ;

		if ( is_wp_error($user_id)) {

			WPLA()->logger->error( 'error creating user '.$user_email.' - WP said: '.$user_id->get_error_message() );
			return false;

		} else {

			// add user meta
			update_user_meta( $user_id, '_amazon_user_email', 	$amazon_user_email );
			update_user_meta( $user_id, 'billing_email', 		$user_email );
			update_user_meta( $user_id, 'paying_customer', 		1 );
			
			// optional phone number
			if ($shipping_details->Phone == 'Invalid Request') $shipping_details->Phone = '';
			update_user_meta( $user_id, 'billing_phone', 		stripslashes( $shipping_details->Phone ));

			// if AddressLine1 is missing or empty, use AddressLine2 instead
			if ( empty( $shipping_details->AddressLine1 ) ) {
				$shipping_details->AddressLine1 = @$shipping_details->AddressLine2;
				$shipping_details->AddressLine2 = '';
			}

			// billing
			update_user_meta( $user_id, 'billing_first_name', 	$user_firstname );
			update_user_meta( $user_id, 'billing_last_name', 	$user_lastname );
			update_user_meta( $user_id, 'billing_company', 		stripslashes( @$shipping_details->CompanyName ) );
			update_user_meta( $user_id, 'billing_address_1', 	stripslashes( @$shipping_details->AddressLine1 ) );
			update_user_meta( $user_id, 'billing_address_2', 	stripslashes( @$shipping_details->AddressLine2 ) );
			update_user_meta( $user_id, 'billing_city', 		stripslashes( @$shipping_details->City ) );
			update_user_meta( $user_id, 'billing_postcode', 	stripslashes( @$shipping_details->PostalCode ) );
			update_user_meta( $user_id, 'billing_country', 		stripslashes( @$shipping_details->CountryCode ) );
			update_user_meta( $user_id, 'billing_state', 		stripslashes( WPLA_CountryHelper::get_state_two_letter_code( @$shipping_details->StateOrRegion ) ) );
			
			// shipping
			update_user_meta( $user_id, 'shipping_first_name', 	$user_firstname );
			update_user_meta( $user_id, 'shipping_last_name', 	$user_lastname );
			update_user_meta( $user_id, 'shipping_company', 	stripslashes( @$shipping_details->CompanyName ) );
			update_user_meta( $user_id, 'shipping_address_1', 	stripslashes( @$shipping_details->AddressLine1 ) );
			update_user_meta( $user_id, 'shipping_address_2', 	stripslashes( @$shipping_details->AddressLine2 ) );
			update_user_meta( $user_id, 'shipping_city', 		stripslashes( @$shipping_details->City ) );
			update_user_meta( $user_id, 'shipping_postcode', 	stripslashes( @$shipping_details->PostalCode ) );
			update_user_meta( $user_id, 'shipping_country', 	stripslashes( @$shipping_details->CountryCode ) );
			update_user_meta( $user_id, 'shipping_state', 		stripslashes( WPLA_CountryHelper::get_state_two_letter_code( @$shipping_details->StateOrRegion ) ) );
			
			WPLA()->logger->info( "added customer $user_id ".$user_email." ($amazon_user_email) " );

		}

		return $user_id;

	} // addCustomer()



	function disableEmailNotifications() {

		// prevent WooCommerce from sending out notification emails when updating order status
		if ( get_option( 'wpla_disable_new_order_emails', 1 ) )
			add_filter( 'woocommerce_email_enabled_new_order', 					array( $this, 'returnFalse' ), 10, 2 );
		if ( get_option( 'wpla_disable_completed_order_emails', 1 ) )
			add_filter( 'woocommerce_email_enabled_customer_completed_order', 	array( $this, 'returnFalse' ), 10, 2 );
        if ( get_option( 'wpla_disable_on_hold_order_emails', 1 ) )
            add_filter( 'woocommerce_email_enabled_customer_on_hold_order', 	array( $this, 'returnFalse' ), 10, 2 );
		if ( get_option( 'wpla_disable_processing_order_emails', 1 ) )
			add_filter( 'woocommerce_email_enabled_customer_processing_order', 	array( $this, 'returnFalse' ), 10, 2 );
		if ( get_option( 'wpla_disable_new_account_emails', 1 ) )
			add_filter( 'woocommerce_email_enabled_customer_new_account', 		array( $this, 'returnFalse' ), 10, 2 );

	}

	function returnFalse( $param1, $param2 = false ) {
		return false;
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
     * Calculate the taxes based on the product's tax class and the order's shipping address
     *
     * @param float $product_price
     * @param int $product_id
     * @param int $quantity
     * @param int $order_id
     * @param object $item
     * @return array
     */
    public function getProductTax( $product_price, $product_id, $quantity, $order_id, $item ) {
        global $woocommerce;
		WPLA()->logger->info( "calling getProductTax( $product_price, $product_id, $quantity, $order_id )" );

		$tax_mode = get_option( 'wpla_orders_tax_mode' );
		WPLA()->logger->info( 'Tax Mode: '. $tax_mode );

		if ( ! $tax_mode ) {
            $line_total    = $product_price;
            $line_subtotal = $product_price;

            return array(
                'line_total'            => $line_total,
                'line_tax'              => 0,
                'line_subtotal'         => $line_subtotal,
                'line_subtotal_tax'     => 0,
                'line_tax_data'         => array(
                    'total' 	=> array(),
                    'subtotal' 	=> array(),
                ),
                'tax_rate_id'           => '',
            );
        } elseif ( $tax_mode == 'autodetect' ) {
            $this->loadCartClasses();

            $cart       = $woocommerce->cart;
            $product    = wc_get_product( $product_id );
            $order      = wc_get_order( $order_id );
            WPLA()->logger->debug( "getProductTax() cart object: ".print_r($cart,1) );

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
                wpla_get_order_meta( $order, 'shipping_country' ),
                wpla_get_order_meta( $order, 'shipping_state' ),
                wpla_get_order_meta( $order, 'shipping_postcode' ),
                wpla_get_order_meta( $order, 'shipping_city' )
            );

            // prevent fatal error:
            // Call to a member function needs_shipping() on a non-object in woocommerce/includes/class-wc-customer.php line 333
            add_filter( 'woocommerce_apply_base_tax_for_local_pickup', '__return_false' );

            $line_price         = $product_price * $quantity;
            $line_subtotal      = 0;
            $line_subtotal_tax  = 0;

            $prices_include_tax = get_option( 'woocommerce_prices_include_tax', 'no' );
            $tax_class          = $product->get_tax_class( 'unfiltered' );

            // calculate subtotal
            if ( !$product->is_taxable() ) {

                WPLA()->logger->info( "getProductTax() step 1 - not taxable (mode 1)" );

                $line_subtotal = $line_price;

            } elseif ( $prices_include_tax == 'yes' ) {
                WPLA()->logger->info( 'Found tax class: '. $tax_class );

                // Get base tax rates
                if ( empty( $shop_tax_rates[ $tax_class ] ) ) {
                    $shop_tax_rates[ $tax_class ] = WC_Tax::get_base_tax_rates( $tax_class );
                }

                // Get item tax rates
                if ( empty( $tax_rates[ $tax_class ] ) ) {
                    $tax_rates[ $tax_class ] = WC_Tax::get_rates( $tax_class);
                }

                $base_tax_rates = $shop_tax_rates[ $tax_class ];
                $item_tax_rates = $tax_rates[ $tax_class ];

                /**
                 * ADJUST TAX - Calculations when base tax is not equal to the item tax
                 */
                if ( $item_tax_rates !== $base_tax_rates ) {

                    WPLA()->logger->info( "getProductTax() step 1 - prices include tax (mode 2a)" );

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

                    WPLA()->logger->info( "getProductTax() step 1 - prices include tax (mode 2b)" );

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

                WPLA()->logger->info( "getProductTax() step 1 - prices exclude tax (mode 3)" );

                // Get item tax rates
                if ( empty( $tax_rates[ $tax_class ] ) ) {
                    $tax_rates[ $tax_class ] = WC_Tax::get_rates( $tax_class );
                }

                $item_tax_rates        = $tax_rates[ $tax_class ];

                // Base tax for line before discount - we will store this in the order data
                $taxes                 = WC_Tax::calc_tax( $line_price, $item_tax_rates );
                $line_subtotal_tax     = array_sum( $taxes );
                $line_subtotal         = $line_price;
            }

            WPLA()->logger->info( "getProductTax() mid - line_subtotal    : $line_subtotal" );
            WPLA()->logger->info( "getProductTax() mid - line_subtotal_tax: $line_subtotal_tax" );

            // calculate line tax

            // Prices
            $base_price = $product_price;
            $line_price = $product_price * $quantity;

            // Tax data
            $taxes = array();
            $discounted_taxes = array();

            if ( !$product->is_taxable() ) {

                WPLA()->logger->info( "getProductTax() step 2 - not taxable (mode 1)" );

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

                $base_tax_rates = $shop_tax_rates[ $tax_class ];
                $item_tax_rates = $tax_rates[ $tax_class ];

                /**
                 * ADJUST TAX - Calculations when base tax is not equal to the item tax
                 */
                if ( $item_tax_rates !== $base_tax_rates ) {

                    WPLA()->logger->info( "getProductTax() step 2 - prices include tax (mode 2a)" );

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

                    WPLA()->logger->info( "getProductTax() step 2 - prices include tax (mode 2b)" );

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

                WPLA()->logger->info( "getProductTax() step 2 - prices exclude tax (mode 3)" );

                $item_tax_rates        = $tax_rates[ $tax_class ];

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

            WPLA()->logger->info( "getProductTax() end - line_subtotal    : $line_subtotal" );
            WPLA()->logger->info( "getProductTax() end - line_subtotal_tax: $line_subtotal_tax" );
            WPLA()->logger->info( "getProductTax() end - item_tax_rates   : ".print_r($item_tax_rates,1) );

            return array(
                'tax_rate_id'           => $tax_rate_id,
                'line_total'            => $line_total,
                'line_tax'              => $line_tax,
                'line_subtotal'         => $line_subtotal,
                'line_subtotal_tax'     => $line_subtotal_tax,
                'line_tax_data'         => array('total' => $discounted_taxes, 'subtotal' => $taxes )
            );
        } elseif ( $tax_mode == 'fixed' ) {
            $vat_percent = get_option( 'wpla_orders_fixed_vat_rate' );
            WPLA()->logger->info( 'VAT% (global): ' . $vat_percent );

            $vat_percent = floatval( $vat_percent );

            // convert single price to total price
            $product_price = $product_price * $quantity;

            // get global tax rate id for order item array
            $tax_rate_id = get_option( 'wpla_orders_tax_rate_id' );
            $vat_tax     = 0;

            if ( $vat_percent ) {
                $vat_tax = $product_price / ( 1 + ( 1 / ( $vat_percent / 100 ) ) );	// calc VAT from gross amount
                $vat_tax = $this->format_decimal( $vat_tax );
            }

            $line_total    = $product_price;
            $line_subtotal = $product_price;

            // adjust item price if prices include tax (no, always subtract tax)
            // (apparently line item prices should always be stored without taxes!)
            // if ( get_option( 'woocommerce_prices_include_tax' ) == 'yes' ) {
            $line_total    = $product_price - $vat_tax;
            $line_subtotal = $product_price - $vat_tax;
            // }

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
        } elseif ( $tax_mode == 'import' ) {
            $line_subtotal		= $item->ItemPrice->Amount;
            $line_total 		= $item->ItemPrice->Amount;
            $tax_rate_id        = get_option( 'wpla_orders_tax_rate_id' );
            $amazon_item_tax    = $item->ItemTax->Amount;

            // Record shipping tax #20062
            if ( $item->ShippingTax->Amount ) {
                $this->shipping_taxes[ $tax_rate_id ] += $item->ShippingTax->Amount;
            }

            // Disabled due to causing double taxes #19699
            //if ( $amazon_item_tax ) {
            //    $this->vat_enabled = true;
            //    $this->vat_total   += $amazon_item_tax;
            //    @$this->vat_rates[ $tax_rate_id ] += $amazon_item_tax;
            //}

            return array(
                'line_total'            => $line_total,
                'line_tax'              => $amazon_item_tax,
                'line_subtotal'         => $line_subtotal,
                'line_subtotal_tax'     => $amazon_item_tax,
                'line_tax_data'         => array(
                    'total' 	=> array( $tax_rate_id => $amazon_item_tax ),
                    'subtotal' 	=> array( $tax_rate_id => $amazon_item_tax ),
                ),
                'tax_rate_id'           => $tax_rate_id,
            );
        }
    } // getProductTax()

    /**
     * Get all applicable taxes for the given order
     *
     * @param int $post_id
     * @return array
     */
    public function getOrderTaxes( $post_id ) {
        $order                  = wc_get_order( $post_id );
        $taxes                  = array();
        $shipping_taxes         = array();
        $order_item_tax_classes = array();
        $order_address          = self::getOrderTaxAddress( $order );

        WPLA()->logger->info( 'getOrderTaxes for #'. $post_id );

        $items = $order->get_items();
        $is_vat_exempt = get_post_meta( $post_id, '_is_vat_exempt', true );

        if ( get_option( 'woocommerce_calc_taxes' ) === 'yes' && $is_vat_exempt != 'yes' ) {
            $line_total = $line_subtotal = array();

            foreach ( $items as $item_id => $item ) {
                // Prevent undefined warnings
                if ( ! isset( $item['line_tax'] ) ) {
                    $item['line_tax'] = array();
                }

                if ( ! isset( $item['line_subtotal_tax'] ) ) {
                    $item['line_subtotal_tax'] = array();
                }

                $item['order_taxes'] = array();
                $item_id                            = absint( $item_id );
                $line_total[ $item_id ]             = isset( $item['line_total'] ) ? wc_format_decimal( $item['line_total'] ) : 0;
                $line_subtotal[ $item_id ]          = isset( $item['line_subtotal'] ) ? wc_format_decimal( $item['line_subtotal'] ) : $line_total[ $item_id ];
                $order_item_tax_classes[ $item_id ] = isset( $item['order_item_tax_class'] ) ? sanitize_text_field( $item['order_item_tax_class'] ) : '';
                $product_id                         = $item['product_id'];

                // Get product details
                if ( get_post_type( $product_id ) == 'product' ) {
                    $_product        = wc_get_product( $product_id );
                    $item_tax_status = $_product->get_tax_status();
                } else {
                    $item_tax_status = 'taxable';
                }

                if ( '0' !== $order_item_tax_classes[ $item_id ] && 'taxable' === $item_tax_status ) {
                    $tax_rates = WC_Tax::find_rates( array(
                        'country'   => $order_address['country'],
                        'state'     => $order_address['state'],
                        'postcode'  => $order_address['postcode'],
                        'city'      => $order_address['city'],
                        'tax_class' => $order_item_tax_classes[ $item_id ]
                    ) );

                    $line_taxes          = WC_Tax::calc_tax( $line_total[ $item_id ], $tax_rates, false );
                    $line_subtotal_taxes = WC_Tax::calc_tax( $line_subtotal[ $item_id ], $tax_rates, false );

                    // Set the new line_tax
                    foreach ( $line_taxes as $_tax_id => $_tax_value ) {
                        $item['line_tax'][ $_tax_id ] = $_tax_value;
                    }

                    // Set the new line_subtotal_tax
                    foreach ( $line_subtotal_taxes as $_tax_id => $_tax_value ) {
                        $item['line_subtotal_tax'][ $_tax_id ] = $_tax_value;
                    }

                    // Sum the item taxes
                    foreach ( array_keys( $taxes + $line_taxes ) as $key ) {
                        $taxes[ $key ] = ( isset( $line_taxes[ $key ] ) ? $line_taxes[ $key ] : 0 ) + ( isset( $taxes[ $key ] ) ? $taxes[ $key ] : 0 );
                    }
                }
            }

            return $taxes;
        }
    }

    /**
     * Returns location of the order where the tax is based on (shipping/billing/shop)
     * @param int|WC_Order $order
     * @return array
     */
    public static function getOrderTaxAddress( $order ) {
        if ( is_numeric( $order ) ) {
            $order = wc_get_order( $order );
        }

        $tax_based_on = get_option( 'woocommerce_tax_based_on' );

        switch( $tax_based_on ) {
            case 'shipping':
                $country    = wpla_get_order_meta( $order, 'shipping_country' );
                $state      = wpla_get_order_meta( $order, 'shipping_state' );
                $postcode   = wpla_get_order_meta( $order, 'shipping_postcode' );
                $city       = wpla_get_order_meta( $order, 'shipping_city' );
                break;

            case 'billing':
                $country    = wpla_get_order_meta( $order, 'billing_country' );
                $state      = wpla_get_order_meta( $order, 'billing_state' );
                $postcode   = wpla_get_order_meta( $order, 'billing_postcode' );
                $city       = wpla_get_order_meta( $order, 'billing_city' );
                break;

            default:
                $default  = wc_get_base_location();
                $country  = $default['country'];
                $state    = $default['state'];
                $postcode = '';
                $city     = '';
                break;
        }

        return array(
            'country'   => $country,
            'state'     => $state,
            'postcode'  => $postcode,
            'city'      => $city
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
        WPLA()->logger->debug( '$tax_rate: '. print_r( $tax_rate, true ) );

        $code      = WC_Tax::get_rate_code( $tax_rate_id );
        $tax_code  = $code ? $code : __('VAT','wpla');
        $tax_label = $tax_rate_id ? $tax_rate->tax_rate_name : WC()->countries->tax_or_vat();

        $item_id = wc_add_order_item( $order_id, array(
            'order_item_name' 		=> $tax_code,
            'order_item_type' 		=> 'tax'
        ) );
        WPLA()->logger->info( 'Added order tax item: '. $item_id );

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


} // class WPLA_OrderBuilder
