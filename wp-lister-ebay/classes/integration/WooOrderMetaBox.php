<?php
/**
 * custom metabox for woocommerce orders created by WP-Lister Pro
 */

## BEGIN PRO ##

class WpLister_Order_MetaBox {

	// var $providers;

	/**
	 * Constructor
	 */
	function __construct() {

		// add_action( 'admin_print_styles', array( __CLASS__, 'admin_styles' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
		// add_action( 'woocommerce_process_shop_order_meta', array( __CLASS__, 'save_meta_box' ), 0, 2 );
        add_action( 'wp_ajax_wpl_update_ebay_feedback', array( __CLASS__, 'update_ebay_feedback' ) ); 

        // this hook needs to be registered even when is_admin() is false:
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'handle_woocommerce_order_status_update' ), 0, 1 );

	}


	// static function admin_styles() {
	// 	wp_enqueue_style( 'shipment_tracking_styles', plugins_url( basename( dirname( __FILE__ ) ) ) . '/assets/css/admin.css' );
	// }

	/**
	 * Add the meta box for shipment info on the order page
	 *
	 * @access public
	 */
	static function add_meta_box() {
		global $post;

		// skip if this is not an order created by WP-Lister
		// if ( ! isset( $_GET['post'] ) ) return;
		// $ebay_transaction_id = get_post_meta( $_GET['post'], '_ebay_transaction_id', true );
		$ebay_order_id 		 = get_post_meta( $post->ID, '_ebay_order_id', true );
		if ( ! $ebay_order_id ) return;

		$title = __('eBay', 'wplister') . ' <small style="color:#999"> #' . $ebay_order_id . '</small>';
		add_meta_box( 'woocommerce-ebay-details', $title, array( __CLASS__, 'meta_box' ), 'shop_order', 'side', 'core');			

	}

	/**
	 * Show the meta box for shipment info on the order page
	 *
	 * @access public
	 */
	static function meta_box() {
		global $post;

		// get order details
		$ebay_order_id = get_post_meta( $post->ID, '_ebay_order_id', true );
		$om            = new EbayOrdersModel();
		$order         = $om->getOrderByOrderID( $ebay_order_id );
		$account       = $order ? WPLE_eBayAccount::getAccount( $order['account_id'] ) : false;

        // display ebay info and account
        echo '<p>';

        echo __('This order was placed on eBay.', 'wplister');
        if ( WPLE()->multi_account && $account ) echo ' ('.$account->title.')';

        echo ' [<a href="admin.php?page=wplister-orders&s='.$ebay_order_id.'" target="_blank">view</a>]';

		$marked_as_shipped = get_post_meta( $post->ID, '_ebay_marked_as_shipped', true );
		if ( $marked_as_shipped ) echo '<br>'.'Marked as shipped: '.$marked_as_shipped;

		$feedback_left     = get_post_meta( $post->ID, '_ebay_feedback_left', true );
		if ( $feedback_left     ) echo '<br>'.'Feedback was left: '.$feedback_left;

        echo '</p>';


		// tracking providers		
		$selected_provider  = get_post_meta( $post->ID, '_tracking_provider', true );
		if ( ! $selected_provider ) $selected_provider  = get_post_meta( $post->ID, '_custom_tracking_provider', true );
		$selected_provider  = apply_filters( 'wplister_set_shipping_provider_for_order', $selected_provider, $post->ID );
		$shipping_providers = apply_filters( 'wplister_available_shipping_providers', self::getProviders() );

		echo '<p class="form-field wpl_tracking_provider_field"><label for="wpl_tracking_provider">' . __('Shipping service', 'wplister') . ':</label><br/><select id="wpl_tracking_provider" name="wpl_tracking_provider" class="wple_chosen_select" style="width:100%;">';

		echo '<option value="">-- ' . __('Select shipping service', 'wplister') . ' --</option>';
		$matching_provider_found = false;
		foreach ( $shipping_providers as $provider => $display_name  ) {
			echo '<option value="' . $provider . '" ' . selected( $provider, $selected_provider, true ) . '>' . $display_name . '</option>';
			if ( $provider == $selected_provider ) $matching_provider_found = true;
		}
		// if no matching provider was found, add the selected provider to the list (support for WPLA and 3rd party plugins)
		if ( $selected_provider && ! $matching_provider_found ) {
			echo '<option value="' . $selected_provider . '" ' . selected( $selected_provider, $selected_provider, true ) . '>' . $selected_provider . '</option>';			
		}
		echo '</select> ';

		// allow 3rd party code to fill in the tracking number and shipping date automatically
		$tracking_number = get_post_meta( $post->ID, '_tracking_number', true );
		$tracking_number = apply_filters( 'wplister_set_tracking_number_for_order', $tracking_number, $post->ID );

		$date_shipped    = get_post_meta( $post->ID, '_date_shipped', true );
		$shipping_date   = $date_shipped ? date( 'Y-m-d', $date_shipped ) : '';
		$shipping_date   = apply_filters( 'wplister_set_shipping_date_for_order', $shipping_date, $post->ID );

		woocommerce_wp_text_input( array(
			'id' 			=> 'wpl_tracking_number',
			'label' 		=> __('Tracking ID:', 'wplister'),
			'placeholder' 	=> '',
			'description' 	=> '',
			'value'			=> $tracking_number
		) );

		woocommerce_wp_text_input( array(
			'id' 			=> 'wpl_date_shipped',
			'label' 		=> __('Shipping date:', 'wplister'),
			'placeholder' 	=> '',
			'description' 	=> '',
			'class'			=> 'date-picker-field',
			'value'			=> $shipping_date
		) );

		woocommerce_wp_text_input( array(
			'id' 			=> 'wpl_feedback_text',
			'label' 		=> __('Your feedback:', 'wplister'),
			'placeholder' 	=> '',
			'description' 	=> 'Feedback is always positive.',
			'custom_attributes' => array( 'maxlength' => 80 ),
			'value'			=> get_post_meta( $post->ID, '_feedback_text', true )
		) );

		// woocommerce_wp_checkbox( array( 'id' => 'wpl_update_ebay_on_save', 'wrapper_class' => 'update_ebay', 'label' => __('Update on save?', 'wplister') ) );


        // $feedback_text = get_post_meta( $post->ID, '_feedback_text', true );
        // if ( $feedback_text ) {
        //     echo '<p>';
        //     echo '<a id="btn_update_again" href="'.$transaction_id.'" target="_blank" class="button">Update again</a>';
        //     echo '</p>';
        // } else {
            echo '<p>';
            echo '<div id="btn_update_ebay_feedback_spinner" style="float:left;display:none"><img src="'.WPLISTER_URL.'/img/ajax-loader-f9.gif"/></div>';
            echo '<a href="#" id="btn_update_ebay_feedback" class="button button-primary">Update on eBay</a>';
            // echo '<a id="btn_update_again" href="#" style="display:none" target="_blank" class="button">Update again</a>';
            echo '<div id="ebay_result_info" class="updated" style="display:none"><p></p></div>';
            echo '</p>';
            // echo "<br><br>";
        // }



        wc_enqueue_js("

            var wpl_updateEbayFeedback = function ( post_id ) {


                var tracking_provider = jQuery('#wpl_tracking_provider').val();
                var tracking_number = jQuery('#wpl_tracking_number').val();
                var date_shipped = jQuery('#wpl_date_shipped').val();
                var feedback_text = jQuery('#wpl_feedback_text').val();
                
                // load task list
                var params = {
                    action: 'wpl_update_ebay_feedback',
                    order_id: post_id,
                    wpl_tracking_provider: tracking_provider,
                    wpl_tracking_number: tracking_number,
                    wpl_date_shipped: date_shipped,
                    wpl_feedback_text: feedback_text,
                    nonce: 'TODO'
                };
                var jqxhr = jQuery.getJSON( ajaxurl, params )
                .success( function( response ) { 

                    jQuery('#btn_update_ebay_feedback_spinner').hide();

                    if ( response.success ) {

                        // var transaction_id = response.transaction_id;
                        // var logMsg = 'Transaction #'+transaction_id+' was created.';
                        var logMsg = 'Order details were updated on eBay.';
                        jQuery('#ebay_result_info p').html( logMsg );
                        jQuery('#ebay_result_info').slideDown();
                        jQuery('#btn_update_ebay_feedback').hide('fast');
                        jQuery('#btn_update_again').attr('href',response.invoice_url);
                        jQuery('#btn_update_again').show('fast');

                    } else {

                        var logMsg = '<b>There was a problem updating this order on eBay</b><br><br>'+response.error;
                        jQuery('#ebay_result_info p').html( logMsg );
                        jQuery('#ebay_result_info').addClass( 'error' ).removeClass('updated');
                        jQuery('#ebay_result_info').slideDown();

                        jQuery('#btn_update_ebay_feedback').removeClass('disabled');
                    }


                })
                .error( function(e,xhr,error) { 
                    jQuery('#ebay_result_info p').html( 'The server responded: ' + e.responseText + '<br>' );

                    jQuery('#btn_update_ebay_feedback_spinner').hide();
                    jQuery('#btn_update_ebay_feedback').removeClass('disabled');

                    console.log( 'error', xhr, error ); 
                    console.log( e.responseText ); 
                });

            }

            jQuery('#btn_update_ebay_feedback').click(function(){

                var post_id = jQuery('#post_ID').val();

                jQuery('#btn_update_ebay_feedback_spinner').show();
                jQuery(this).addClass('disabled');
                wpl_updateEbayFeedback( post_id );

                return false;
            });
        ");
	
	}

	/**
	 * Order Downloads Save
	 *
	 * Function for processing and storing all order downloads.
	 */
	static function save_meta_box( $post_id, $post ) {
		if ( isset( $_POST['wpl_tracking_number'] ) ) {

			// // get field values
			// $wpl_tracking_provider		= esc_attr( $_POST['wpl_tracking_provider'] );
			// $wpl_tracking_number 		= esc_attr( $_POST['wpl_tracking_number'] );
			// $wpl_date_shipped			= esc_attr( strtotime( $_POST['wpl_date_shipped'] ) );
			// $wpl_feedback_text 			= esc_attr( $_POST['wpl_feedback_text'] );

			// // Update order data
			// update_post_meta( $post_id, '_tracking_provider', $wpl_tracking_provider );
			// update_post_meta( $post_id, '_tracking_number', $wpl_tracking_number );
			// update_post_meta( $post_id, '_date_shipped', $wpl_date_shipped );
			// update_post_meta( $post_id, '_feedback_text', $wpl_feedback_text );

			// // build array
			// $data = array();
			// $data['TrackingNumber']  = $wpl_tracking_number;
			// $data['TrackingCarrier'] = $wpl_tracking_provider;
			// $data['ShippedTime']     = $wpl_date_shipped;
			// $data['FeedbackText']    = $wpl_feedback_text;

			// // call CompleteOrder on eBay
			// if ( ( $wpl_tracking_number != '' ) && 
			// 	 ( $wpl_date_shipped != '' ) && 
			// 	 ( $wpl_feedback_text != '' ) )  {

			// 	// $message = __('Order details were updated on eBay.','wplister');
			// 	// echo '<div id="message" class="" style="display:block !important;"><p>'.$message.'</p></div>';

			// 	WPLE()->initEC();
			// 	WPLE()->EC->completeOrder( $post_id, $data );
			// 	WPLE()->EC->closeEbay();
			// 	// wple_show_message( __('Order details were updated on eBay.','wplister') );
				
			// 	WPLE()->logger->info( 'save_meta_box' );
			// }

		}
	} // save_meta_box()

	// handle order status changed to "completed" - and complete ebay order
    static public function handle_woocommerce_order_status_update( $post_id ) {

    	// check if auto complete option is enabled
    	if ( get_option( 'wplister_auto_complete_sales' ) != 1 ) return;

    	// check if default status for new created orders is completed - skip further processing if it is
		if ( get_option( 'wplister_new_order_status', 'processing' ) == 'completed' ) return;

    	// check if this order came in from eBay
        $ebay_order_id = get_post_meta( $post_id, '_ebay_order_id', true );
    	if ( ! $ebay_order_id ) return;


		// build array
		$data = array();
		// $data['ShippedTime']  = gmdate('U');
		$data['ShippedTime']     = '_now_';
		$data['FeedbackText']    = get_option( 'wplister_default_feedback_text', '' );

		// check if there are tracking details stored by other plugins - like Shipstation or Shipment Tracking
		$wpl_tracking_provider = get_post_meta( $post_id, '_tracking_provider', true );
		$wpl_tracking_number   = get_post_meta( $post_id, '_tracking_number', true );
		if ( $wpl_tracking_number && $wpl_tracking_provider ) {
			$data['TrackingNumber']  = trim( $wpl_tracking_number );
			$data['TrackingCarrier'] = trim( $wpl_tracking_provider );
		}

		// add support for WC Shipment Tracking v1.6.6 which stores tracking data using a different meta key
        $wc_tracking_data = get_post_meta( $post_id, '_wc_shipment_tracking_items', true );
		if ( $wc_tracking_data ) {
		    $wc_tracking_data = current( $wc_tracking_data );
		    $data['TrackingNumber']  = $wc_tracking_data['tracking_number'];
		    $data['TrackingCarrier'] = $wc_tracking_data['tracking_provider'];
        }

		// check if tracking details are included in POST request (ie. an order is completed from the order details page)
		$wpl_tracking_provider	 = isset( $_REQUEST['wpl_tracking_provider'] ) ? esc_attr( $_REQUEST['wpl_tracking_provider'] ) : false;
		$wpl_tracking_number 	 = isset( $_REQUEST['wpl_tracking_number']   ) ? esc_attr( $_REQUEST['wpl_tracking_number']   ) : false;
		if ( $wpl_tracking_number && $wpl_tracking_provider ) {
			$data['TrackingNumber']  = trim( $wpl_tracking_number );
			$data['TrackingCarrier'] = trim( $wpl_tracking_provider );
		}


    	// complete sale on eBay
		$response = self::callCompleteOrder( $post_id, $data, true );


		// Update order data if request was successful
		if ( $response->success ) {
			update_post_meta( $post_id, '_feedback_text', $data['FeedbackText'] );
		}

		// error handling is done in callCompleteOrder()
		// if ( WPLE()->EC->isSuccess ) {
		// if ( $response->success ) {
		// }
    }

    /**
     * update feedback and tracking details on ebay (ajax)
     */
    static function update_ebay_feedback() {

		// get field values
        $post_id 					= $_REQUEST['order_id'];
		$wpl_tracking_provider		= esc_attr( $_REQUEST['wpl_tracking_provider'] );
		$wpl_tracking_number 		= esc_attr( $_REQUEST['wpl_tracking_number'] );
		$wpl_date_shipped			= esc_attr( strtotime( $_REQUEST['wpl_date_shipped'] ) );
		$wpl_feedback_text 			= esc_attr( $_REQUEST['wpl_feedback_text'] );

		// if tracking number is set, but date is missing, set date today.
		if ( trim($wpl_tracking_number) != '' ) {
			if ( $wpl_date_shipped == '' ) $wpl_date_shipped = gmdate('U');
		}

		// build array
		$data = array();
		$data['TrackingNumber']  = trim( $wpl_tracking_number );
		$data['TrackingCarrier'] = trim( $wpl_tracking_provider );
		$data['ShippedTime']     = trim( $wpl_date_shipped );
		$data['FeedbackText']    = trim( $wpl_feedback_text );

		// if feedback text is empty, use default feedback text
		if ( ! $data['FeedbackText'] ) {
			$data['FeedbackText'] = get_option( 'wplister_default_feedback_text', '' );
		}

    	// check if this order came in from eBay
        $ebay_order_id = get_post_meta( $post_id, '_ebay_order_id', true );
    	if ( ! $ebay_order_id ) die('This is not an eBay order.');

    	// moved to self::callCompleteOrder() so it will be triggered for do_action(wple_complete_sale_on_ebay)
    	//$data = apply_filters( 'wplister_complete_order_data', $data, $post_id );

    	// complete sale on eBay
		$response = self::callCompleteOrder( $post_id, $data );

		// WPLE()->initEC();
		// $response = WPLE()->EC->completeOrder( $post_id, $data );
		// WPLE()->EC->closeEbay();

		// Update order data if request was successful
		if ( $response->success ) {
			update_post_meta( $post_id, '_tracking_provider', $wpl_tracking_provider );
			update_post_meta( $post_id, '_tracking_number', $wpl_tracking_number );
			update_post_meta( $post_id, '_date_shipped', $wpl_date_shipped );
			update_post_meta( $post_id, '_feedback_text', $wpl_feedback_text );
		}

        self::returnJSON( $response );
        exit();

    }

    static public function callCompleteOrder( $post_id, $data, $verbose = false ) {

        // get eBay order
        $sm = new EbayOrdersModel();
        $ebay_order = $sm->getOrderByPostID( $post_id );
    	if ( ! $ebay_order ) return;

        $data = apply_filters( 'wplister_complete_order_data', $data, $post_id, $ebay_order );

		// get account_id for eBay order
		$account_id = $ebay_order['account_id'];

		// get account title for order notes
        $account_title = isset( WPLE()->accounts[ $account_id ] ) ? WPLE()->accounts[ $account_id ]->title : '_unknown_';
        $account_title = ' ('.$account_title.')';

		// get order
		$order = wc_get_order( $post_id );

		// add order note - only when acting on order status change event
		if ( $verbose )	$order->add_order_note( __('Preparing to complete sale on eBay...', 'wplister') . $account_title );


		// make sure feedback is only left once - prevent Error 55
		$feedback_left = get_post_meta( $post_id, '_ebay_feedback_left', true );
		if ( isset($data['FeedbackText']) && $feedback_left == 'yes' ) unset( $data['FeedbackText'] );

		// make sure ShippedTime is a timestamp
		if ( isset($data['ShippedTime']) && ! is_numeric($data['ShippedTime']) ) {
			$data['ShippedTime'] = strtotime( $data['ShippedTime'] );
		}

		// fuzzy match tracking provider
		if ( isset($data['TrackingCarrier']) ) {
			$data['TrackingCarrier'] = self::findMatchingTrackingProvider( $data['TrackingCarrier'] );
		}


		// call eBay
		WPLE()->initEC( $account_id );
		$response = WPLE()->EC->completeOrder( $ebay_order['id'], $data );
		WPLE()->EC->closeEbay();


		// handle result
		if ( $response->success ) {
			$order->add_order_note( __('eBay sale was completed successfully.', 'wplister') . $account_title );
		} elseif ( $response->error ) {
			$error_msg = ' ' . $response->error;
			$order->add_order_note( __('There was a problem completing the sale on eBay.', 'wplister') . $error_msg . $account_title );
			update_post_meta( $post_id, '_wple_debug_last_error', $response );
		} else {
			$order->add_order_note( __('There was a problem completing the sale on eBay! Please check the database log and contact support.', 'wplister') . $account_title );
			update_post_meta( $post_id, '_wple_debug_last_error', $response );
			// WPLE()->logger->error('EC::lastResults:' . print_r(WPLE()->EC->lastResults,1) );
		}

		// remember if feedback was left
		if ( $response->success && isset( $data['FeedbackText'] ) && trim( $data['FeedbackText'] ) ) {
			update_post_meta( $post_id, '_ebay_feedback_left', 'yes' );
		}

		// Error 55 usually means feedback was already left
		if ( $response->error_code == 55 ) {
			update_post_meta( $post_id, '_ebay_feedback_left', 'yes' );
		}

		// remember if order was marked as shipped on eBay
		if ( $response->success && isset( $data['ShippedTime'] ) ) {
			update_post_meta( $post_id, '_ebay_marked_as_shipped', 'yes' );
		}

    	return $response;
    } // callCompleteOrder()
    

    static public function findMatchingTrackingProvider( $provider_name ) {
    	$providers = self::getProviders();

    	foreach ( $providers as $key => $name ) {
    		// return lower case match
    		if ( strtolower($key) == strtolower($provider_name) ) {
    			return $key;
    		}
    	}

    	// return 'Other';
    	return $provider_name; // if no match is found, return original provider name - eBay should accept most values
    } // findMatchingTrackingProvider()


    // TODO: Commonly used shipping carriers can be found by calling GeteBayDetails with DetailName set to ShippingCarrierDetails 
    // and examining the returned ShippingCarrierDetails.ShippingCarrier field. 
    static public function getProviders() {
    	return array(
			'APC'                    => 'APC',
			'Australia Post'         => 'Australia Post',
			'Canada Post'            => 'Canada Post',
			'Chronopost'             => 'Chronopost',
			'City Link'              => 'City Link',
			'ColiposteDomestic'      => 'Coliposte Domestic',
			'ColiposteInternational' => 'Coliposte International',
			'Correos'                => 'Correos',
			'Deutsche Post'          => 'Deutsche Post',
			'DHL'                    => 'DHL',
			'DHL Global Mail'        => 'DHL Global Mail',
			'Direct Freight'         => 'Direct Freight',
			'DPD'                    => 'DPD',
			'DTDC'                   => 'DTDC',
			'FedEx'                  => 'Fedex',
			'GLS'                    => 'GLS',
			'Hermes'                 => 'Hermes',
			'iLoxx'                  => 'iLoxx',
			'Interlink Express'      => 'Interlink Express',
			'Nacex'                  => 'Nacex',
			'OnTrac'                 => 'OnTrac',
			'ParcelForce'            => 'ParcelForce',
			'PostNL'                 => 'PostNL',
			'Posten AB'              => 'Posten AB',
			'Royal Mail'             => 'Royal Mail',
			'SAPO'                   => 'SAPO',
			'StarTrack'              => 'Star Track',
			'SmartSend'              => 'Smart Send',
			'TNT'                    => 'TNT',
			'UK Mail'                => 'UK Mail',
			'UPS'                    => 'UPS',
			'USPS'                   => 'U.S. Postal Service',
			'Other'                  => 'Other postal service'
		);
    } // getProviders()

   
    static public function returnJSON( $data ) {
        header('content-type: application/json; charset=utf-8');
        echo json_encode( $data );
    }
    

}
$WpLister_Order_MetaBox = new WpLister_Order_MetaBox();

## END PRO ##


