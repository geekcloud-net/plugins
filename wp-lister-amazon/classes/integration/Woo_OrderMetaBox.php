<?php
/**
 * add amazon metabox to order edit page
 */

## BEGIN PRO ##

class WPLA_Order_MetaBox {
	var $providers;

	/**
	 * Constructor
	 */
	function __construct() {

		add_action( 'add_meta_boxes',                       array( &$this, 'add_meta_box' ) );
        add_action( 'wp_ajax_wpla_update_amazon_shipment', 	array( &$this, 'update_amazon_shipment_ajax' ) ); 
        add_action( 'wp_ajax_wpla_submit_order_to_fba', 	array( &$this, 'submit_order_to_fba' ) ); 

		// handle order status changed to "completed" - and complete Amazon order
		add_action( 'woocommerce_order_status_completed', array( &$this, 'handle_woocommerce_order_status_update_completed' ), 0, 1 );

		add_action( 'woocommerce_process_shop_order_meta', array( &$this, 'save_meta_box' ), 0, 2 );
	}

	static function getShippingProviders() {

		$providers = array(
			'Amazon Logistics',
			'Blue Package',
			'USPS',
			'UPS',
			'UPSMI',
			'FedEx',
			'Deutsche Post',
			'DHL',
			'DHL Global Mail',
			'DPD',
			'Fastway',
			'UPS Mail Innovations',
			'Lasership',
			'Royal Mail',
			'FedEx SmartPost',
			'OSM',
			'OnTrac',
			'Streamlite',
			'Newgistics',
			'Canada Post',
			'City Link',
            'Correos',
			'GLS',
			'GO!',
			'Hermes Logistik Gruppe',
			'Parcelforce',
			'TNT',
			'Target',
			'SagawaExpress',
			'NipponExpress',
			'YamatoTransport',
			'Other'
		);

		return $providers;
	}


	/**
	 * Add the meta box for shipment info on the order page
	 *
	 * @access public
	 */
	function add_meta_box() {
		global $post;
		if ( ! isset( $_GET['post'] ) ) return;

		// check if this is an order created by WP-Lister for Amazon
		$amazon_order_id = get_post_meta( $post->ID, '_wpla_amazon_order_id', true );
		if ( $amazon_order_id ) {

			// show meta box for Amazon orders
			$title = __('Amazon', 'wpla') . ' <small style="color:#999"> #' . $amazon_order_id . '</small>';
			add_meta_box( 'woocommerce-amazon-details', $title, array( &$this, 'meta_box_for_amazon_orders' ), 'shop_order', 'side', 'core');		

		} elseif ( get_option( 'wpla_fba_enabled' ) ) {

			// show FBA meta box for Non-Amazon orders
			$title = __('Amazon', 'wpla');
			add_meta_box( 'woocommerce-amazon-details', $title, array( &$this, 'meta_box_for_non_amazon_orders' ), 'shop_order', 'side', 'core');		

		}

	}


	/**
	 * Show the FBA meta box for Non-Amazon orders
	 *
	 * @access public
	 */
	function meta_box_for_non_amazon_orders() {
		global $post;

		// check if order has already been fulfilled by Amazon
        $submission_status = get_post_meta( $post->ID, '_wpla_fba_submission_status', true );
        if ( $submission_status == 'shipped') 
        	return $this->show_fba_tracking_details();

        if ( $submission_status == 'failed' ) {
	        echo '<p><b>' . __('There was a problem submitting this order to be fulfilled by Amazon!', 'wpla') . '</b></p>';
	        // $this->show_fba_tracking_details(); // TODO: store and show last used _wpla_DeliverySLA for automatic submissions as well
	    }

	    // held submissions can be resubmitted
        if ( $submission_status == 'hold' ) {
	        echo '<p>' . __('The ordered items(s) have been held back on FBA until this order is completed. To ship the held items please visit Seller Central.', 'wpla') . '</p>';
        }

		// check if order is eligible to be fulfilled via FBA
		$checkresult = WPLA_FbaHelper::orderCanBeFulfilledViaFBA( $post );
		if ( ! is_array( $checkresult ) ) {
	        echo '<p>' . $checkresult . '</p>';
	        return;
		}

		// all right - this order can be fulfilled via FBA
        if ( $submission_status == 'failed' ) {
	        echo '<p>' . __('You can try to submit this order again.', 'wpla') . '</p>';
	    } elseif ( $submission_status == 'hold' ) {
	        echo '<p>' . __('You can submit this on-hold order again.', 'wpla') . '</p>';
	    } else {
    	    echo '<p>' . __('This order can be fulfilled by Amazon.', 'wpla') . '</p>';
	    }

        echo '<table style="width:100%">';
    	echo '<tr>';
    	echo '<th style="text-align:left;">'.'ASIN'.'</th>';
    	echo '<th style="text-align:left;">'.'Purchased'.'</th>';
    	echo '<th style="text-align:left;">'.'FBA Qty'.'</th>';
    	echo '</tr>';

		$items_available_on_fba = $checkresult;
        foreach ( $items_available_on_fba as $listing ) {
        	echo '<tr>';
        	echo '<td>'.$listing->asin.'</td>';
        	echo '<td>'.$listing->purchased_qty.'</td>';
        	echo '<td>'.$listing->fba_quantity.'</td>';
        	echo '</tr>';
        }
        echo '</table>';

        // DeliverySLA option
        $default_sla = get_option( 'wpla_fba_default_delivery_sla', 'Standard' );
		echo '<p class="form-field wpla_DeliverySLA_field"><label for="wpla_DeliverySLA">' . __('Shipping service', 'wpla') . '</label><br/><select id="wpla_DeliverySLA" name="wpla_DeliverySLA" class="chosen_select" style="width:100%;">';
		echo '<option value="Standard"  '.( $default_sla == 'Standard'  ? 'selected' : '').' > '  . __('Standard', 'wpla') . ' (3-5 business days)</option>';
		echo '<option value="Expedited" '.( $default_sla == 'Expedited' ? 'selected' : '').' > ' . __('Expedited', 'wpla') . ' (2 business days)</option>';
		echo '<option value="Priority"  '.( $default_sla == 'Priority'  ? 'selected' : '').' > '  . __('Priority', 'wpla') . ' (1 business day)</option>';
		echo '</select> ';

		woocommerce_wp_text_input( array(
			'id' 			=> 'wpla_NotificationEmail',
			'label' 		=> __('Notification Email', 'wpla'),
			'placeholder' 	=> '',
			'description' 	=> '',
			'value'			=> get_post_meta( $post->ID, '_billing_email', true )
		) );

		woocommerce_wp_text_input( array(
			'id' 			=> 'wpla_DisplayableOrderComment',
			'label' 		=> __('Packing Slip Comment', 'wpla'),
			'placeholder' 	=> 'Thank you for your order.',
			'description' 	=> '',
			'value'			=> get_option( 'wpla_fba_default_order_comment' )
		) );


		$submit_button_label = in_array( $submission_status, array('failed','hold') ) ? 'Submit again' : 'Submit to FBA';
        echo '<p>';
        echo '<div id="btn_submit_order_to_fba_spinner" style="float:right;display:none"><img src="'.WPLA_URL.'/img/ajax-loader.gif"/></div>';
        echo '<div class="spinner"></div>';
        echo '<a href="#" id="btn_submit_order_to_fba" class="button button-primary">'.$submit_button_label.'</a>';
        echo '<div id="amazon_result_info" class="updated" style="display:none"><p></p></div>';
        echo '</p>';

        wc_enqueue_js("

            var wpla_submitOrderToFBA = function ( post_id ) {

				var wpla_DeliverySLA             = jQuery('#wpla_DeliverySLA').val();
				var wpla_NotificationEmail       = jQuery('#wpla_NotificationEmail').val();
				var wpla_DisplayableOrderComment = jQuery('#wpla_DisplayableOrderComment').val();

                // prepare request
                var params = {
					action: 'wpla_submit_order_to_fba',
					order_id: post_id,
					wpla_DeliverySLA: wpla_DeliverySLA,
					wpla_NotificationEmail: wpla_NotificationEmail,
					wpla_DisplayableOrderComment: wpla_DisplayableOrderComment,
                    nonce: 'TODO'
                };
                var jqxhr = jQuery.getJSON( ajaxurl, params )
                .success( function( response ) { 

                    jQuery('#woocommerce-amazon-details .spinner').hide();

                    if ( response.success ) {

                        var logMsg = 'Order was submitted to Amazon.';
                        jQuery('#amazon_result_info p').html( logMsg );
	                    jQuery('#amazon_result_info').addClass( 'updated' ).removeClass('error');
                        jQuery('#amazon_result_info').slideDown();
                        jQuery('#btn_submit_order_to_fba').hide('fast');

                    } else {

                        var logMsg = '<b>There was a problem submitting this order to Amazon</b><br><br>'+response.error;
                        jQuery('#amazon_result_info p').html( logMsg );
                        jQuery('#amazon_result_info').addClass( 'error' ).removeClass('updated');
                        jQuery('#amazon_result_info').slideDown();

                        jQuery('#btn_submit_order_to_fba').removeClass('disabled');
                    }


                })
                .error( function(e,xhr,error) { 
                    jQuery('#amazon_result_info p').html( 'The server responded: ' + e.responseText + '<br>' );
                    jQuery('#amazon_result_info').addClass( 'error' ).removeClass('updated');
                    jQuery('#amazon_result_info').slideDown();

                    jQuery('#woocommerce-amazon-details .spinner').hide();
                    jQuery('#btn_submit_order_to_fba').removeClass('disabled');

                    console.log( 'error', xhr, error ); 
                    console.log( e.responseText ); 
                });

            }

            jQuery('#btn_submit_order_to_fba').click(function(){

                var post_id = jQuery('#post_ID').val();

                // jQuery('#btn_submit_order_to_fba_spinner').show();
                jQuery('#woocommerce-amazon-details .spinner').show();
                jQuery(this).addClass('disabled');
                wpla_submitOrderToFBA( post_id );

                return false;
            });


        ");

	} // meta_box_for_non_amazon_orders()

	function show_fba_tracking_details() {
		global $post;

        echo '<p>' . __('This order has been fulfilled by Amazon.', 'wpla') . '</p>';

        echo '<table style="width:100%">';

    	echo '<tr>';
    	echo '<th style="text-align:left;">'.'Tracking #'.'</th>';
    	echo '<td style="text-align:left;">'. get_post_meta( $post->ID, '_wpla_fba_tracking_number', true ) .'</td>';
    	echo '</tr>';

    	echo '<tr>';
    	echo '<th style="text-align:left;">'.'Carrier'.'</th>';
    	echo '<td style="text-align:left;">'. get_post_meta( $post->ID, '_wpla_fba_ship_carrier', true ) .'</td>';
    	echo '</tr>';

    	$date = get_post_meta( $post->ID, '_wpla_fba_shipment_date', true );
    	$date = date( 'Y-m-d H:i', strtotime( $date ) );
    	echo '<tr>';
    	echo '<th style="text-align:left;">'.'Shipped'.'</th>';
    	echo '<td style="text-align:left;">'. $date .'</td>';
    	echo '</tr>';

    	$date = get_post_meta( $post->ID, '_wpla_fba_estimated_arrival_date', true );
    	$date = date( 'Y-m-d', strtotime( $date ) );
    	echo '<tr>';
    	echo '<th style="text-align:left;">'.'Est. arrival'.'</th>';
    	echo '<td style="text-align:left;">'. $date .'</td>';
    	echo '</tr>';

    	echo '<tr>';
    	echo '<th style="text-align:left;">'.'Service level'.'</th>';
    	echo '<td style="text-align:left;">'. get_post_meta( $post->ID, '_wpla_fba_ship_service_level', true ) .'</td>';
    	echo '</tr>';

        echo '</table>';

	} // show_fba_tracking_details()

	/**
	 * Show the meta box for shipment info on the order page
	 *
	 * @access public
	 */
	function meta_box_for_amazon_orders() {
		global $post;

		$amazon_order_id    = get_post_meta( $post->ID, '_wpla_amazon_order_id', true );
		$selected_provider  = get_post_meta( $post->ID, '_wpla_tracking_provider', true );
		$shipping_providers = apply_filters( 'wpla_available_shipping_providers', self::getShippingProviders() );

		// get order details
		$om    = new WPLA_OrdersModel();
		$order = $om->getOrderByOrderID( $amazon_order_id );

        if ( $order ) {

	        // display amazon account
	        $account = WPLA_AmazonAccount::getAccount( $order->account_id );
	        if ( $account ) {
		        echo '<p>';
		        echo __('This order was placed on Amazon.', 'wpla');
		        echo '('.$account->title.')';
		        echo ' [<a href="admin.php?page=wpla-orders&s='.$amazon_order_id.'" target="_blank">view</a>]';
		        echo '</p>';
	        }

			// check if order has already been fulfilled by Amazon
	        $submission_status = get_post_meta( $post->ID, '_wpla_fba_submission_status', true );
	        if ( $submission_status == 'shipped') {
	        	return $this->show_fba_tracking_details();
	        }

			// check for FBA
        	$order_details = json_decode( $order->details );
	        if ( is_object( $order_details ) && ( $order_details->FulfillmentChannel == 'AFN' ) ) {
		        echo '<p>';
		        echo __('This order will be fulfilled by Amazon.', 'wpla');
		        echo '</p>';
		        return;
	        }

        }

		echo '<p class="form-field wpla_tracking_provider_field"><label for="wpla_tracking_provider">' . __('Shipping service', 'wpla') . ':</label><br/><select id="wpla_tracking_provider" name="wpla_tracking_provider" class="chosen_select" style="width:100%;">';

		echo '<option value="">-- ' . __('Select shipping service', 'wpla') . ' --</option>';
		foreach ( $shipping_providers as $provider ) {
			echo '<option value="' . $provider . '" ' . selected( $provider, $selected_provider, false ) . '>' . $provider . '</option>';
		}

		echo '</select> ';

		// Add filters to the tracking number and provider
        $tracking_provider = apply_filters( 'wpla_set_tracking_service_for_order', get_post_meta( $post->ID, '_wpla_tracking_service_name', true ), $post->ID );
        $tracking_number   = apply_filters( 'wpla_set_tracking_number_for_order', get_post_meta( $post->ID, '_wpla_tracking_number', true ), $post->ID );

		woocommerce_wp_text_input( array(
			'id' 			=> 'wpla_tracking_service_name',
			'label' 		=> __('Service provider', 'wpla'),
			'placeholder' 	=> '',
			'description' 	=> '',
			'value'			=> $tracking_provider
		) );

		woocommerce_wp_text_input( array(
			'id' 			=> 'wpla_tracking_number',
			'label' 		=> __('Tracking ID', 'wpla'),
			'placeholder' 	=> '',
			'description' 	=> '',
			'value'			=> $tracking_number
		) );


		// get current local time
		$tz = WPLA_DateTimeHelper::getLocalTimeZone();
		$nw = new DateTime('now', new DateTimeZone( $tz ));

		// convert stored date/time from UTC to local time
		$wpla_date_shipped = get_post_meta( $post->ID, '_wpla_date_shipped', true );
		$wpla_time_shipped = get_post_meta( $post->ID, '_wpla_time_shipped', true );

		// check if date and time are both valid
		if ( DateTime::createFromFormat('Y-m-d H:i:s', $wpla_date_shipped.' '.$wpla_time_shipped) ) {

			// convert date/time from UTC to local timezone
			$tz = WPLA_DateTimeHelper::getLocalTimeZone();
			$dt = new DateTime( $wpla_date_shipped.' '.$wpla_time_shipped, new DateTimeZone( 'UTC' ) );
			$dt->setTimeZone( new DateTimeZone( $tz ) );
			$wpla_date_shipped = $dt->format('Y-m-d');
			$wpla_time_shipped = $dt->format('H:i:s');

		}

		woocommerce_wp_text_input( array(
			'id' 			=> 'wpla_date_shipped',
			'label' 		=> __('Shipping date', 'wpla'),
			'placeholder' 	=> 'Current date: ' . $nw->format('Y-m-d'),
			'description' 	=> '',
			'class'			=> 'date-picker-field',
			'value'			=> $wpla_date_shipped
		) );

		woocommerce_wp_text_input( array(
			'id' 			=> 'wpla_time_shipped',
			'label' 		=> __('Shipping time', 'wpla'),
			'placeholder' 	=> 'Current time: ' . $nw->format('H:i:s'), // . ' ' . $tz,
			'description' 	=> '<small>Timezone: '.$tz.'</small>',
			// 'desc_tip'		=>  true,
			'class'			=> 'time-picker-field',
			'value'			=> $wpla_time_shipped
		) );

        // echo '<p>';
        // echo '<small>Local timezone: '.$tz.'</small>';
        // echo '</p>';

		// woocommerce_wp_checkbox( array( 'id' => 'wpla_update_amazon_on_save', 'wrapper_class' => 'update_amazon', 'label' => __('Update on save?', 'wpla') ) );

		// show submission status if it exists
        if ( $submission_status = get_post_meta( $post->ID, '_wpla_submission_result', true ) ) {
	        echo '<p>';
	        if ( $submission_status == 'success' ) {
		        echo 'Submitted to Amazon: yes';
	        } else {
	        	$history = maybe_unserialize( $submission_status );
		        echo 'Submission Log:';
		        echo '<div style="color:darkred; font-size:0.8em;">';
	            if ( is_array( $history ) ) {
	                foreach ($history['errors'] as $result) {
	                    echo '<b>'.$result['error-type'].':</b> '.$result['error-message'].' ('.$result['error-code'].')<br>';
	                }
	                foreach ($history['warnings'] as $result) {
	                    echo '<b>'.$result['error-type'].':</b> '.$result['error-message'].' ('.$result['error-code'].')<br>';
	                }
	            }
		        echo '</div>';        	
	        }
	        echo '</p>';        	
        }

        echo '<p>';
        echo '<div id="btn_update_amazon_shipment_spinner" style="float:right;display:none"><img src="'.WPLA_URL.'/img/ajax-loader.gif"/></div>';
        echo '<div class="spinner"></div>';
        echo '<a href="#" id="btn_update_amazon_shipment" class="button button-primary">'.__('Mark as shipped on Amazon','wpla').'</a>';
        echo '<div id="amazon_result_info" class="updated" style="display:none"><p></p></div>';
        echo '</p>';

        wc_enqueue_js("

            var wpla_updateAmazonFeedback = function ( post_id ) {


                var tracking_provider 		= jQuery('#wpla_tracking_provider').val();
                var tracking_service_name 	= jQuery('#wpla_tracking_service_name').val();
                var tracking_number 		= jQuery('#wpla_tracking_number').val();
                var date_shipped 			= jQuery('#wpla_date_shipped').val();
                var time_shipped 			= jQuery('#wpla_time_shipped').val();
                
                // load task list
                var params = {
                    action: 'wpla_update_amazon_shipment',
                    order_id: post_id,
                    wpla_tracking_provider: tracking_provider,
                    wpla_tracking_service_name: tracking_service_name,
                    wpla_tracking_number: tracking_number,
                    wpla_date_shipped: date_shipped,
                    wpla_time_shipped: time_shipped,
                    nonce: 'TODO'
                };
                var jqxhr = jQuery.getJSON( ajaxurl, params )
                .success( function( response ) { 

                    // jQuery('#btn_update_amazon_shipment_spinner').hide();
                    jQuery('#woocommerce-amazon-details .spinner').hide();

                    if ( response.success ) {

                        var logMsg = 'Shipping status was updated and will be submitted to Amazon.';
                        jQuery('#amazon_result_info p').html( logMsg );
	                    jQuery('#amazon_result_info').addClass( 'updated' ).removeClass('error');
                        jQuery('#amazon_result_info').slideDown();
                        jQuery('#btn_update_amazon_shipment').hide('fast');

                    } else {

                        var logMsg = '<b>There was a problem updating this order on Amazon</b><br><br>'+response.error;
                        jQuery('#amazon_result_info p').html( logMsg );
                        jQuery('#amazon_result_info').addClass( 'error' ).removeClass('updated');
                        jQuery('#amazon_result_info').slideDown();

                        jQuery('#btn_update_amazon_shipment').removeClass('disabled');
                    }


                })
                .error( function(e,xhr,error) { 
                    jQuery('#amazon_result_info p').html( 'The server responded: ' + e.responseText + '<br>' );
                    jQuery('#amazon_result_info').addClass( 'error' ).removeClass('updated');
                    jQuery('#amazon_result_info').slideDown();

                    // jQuery('#btn_update_amazon_shipment_spinner').hide();
                    jQuery('#woocommerce-amazon-details .spinner').hide();
                    jQuery('#btn_update_amazon_shipment').removeClass('disabled');

                    console.log( 'error', xhr, error ); 
                    console.log( e.responseText ); 
                });

            }

            jQuery('#btn_update_amazon_shipment').click(function(){

                var post_id = jQuery('#post_ID').val();

                // jQuery('#btn_update_amazon_shipment_spinner').show();
                jQuery('#woocommerce-amazon-details .spinner').show();
                jQuery(this).addClass('disabled');
                wpla_updateAmazonFeedback( post_id );

                return false;
            });

            jQuery('#wpla_tracking_provider').change(function(){

                var tracking_provider = jQuery('#wpla_tracking_provider').val();
                // alert(tracking_provider);

                if ( tracking_provider == 'Other' ) {
	                jQuery('.wpla_tracking_service_name_field').slideDown();
                } else {
	                jQuery('.wpla_tracking_service_name_field').slideUp();
                }

                return false;
            });
			if ( 'Other' != jQuery('#wpla_tracking_provider').val() ) {
	            jQuery('.wpla_tracking_service_name_field').hide();
			}

            // fix jQuery datepicker today button
			jQuery('button.ui-datepicker-current').live('click', function() {
			    jQuery.datepicker._curInst.input.datepicker('setDate', new Date()).datepicker('hide').blur();
			});

        ");
	
	} // meta_box_for_amazon_orders()

	public function save_meta_box( $post_id ) {
		// check if this order came in from amazon
		if ( ! get_post_meta( $post_id, '_wpla_amazon_order_id', true ) ) return;

		// check if this order has already been submitted to Amazon
		if ( get_post_meta( $post_id, '_wpla_date_shipped', true ) != '' ) return;

		$tracking_provider		= trim( esc_attr( @$_POST['wpla_tracking_provider'] ) );
		$tracking_number 		= trim( esc_attr( @$_POST['wpla_tracking_number'] ) );
		$date_shipped			= trim( esc_attr( @$_POST['wpla_date_shipped'] ) );
		$time_shipped			= trim( esc_attr( @$_POST['wpla_time_shipped'] ) );
		$tracking_service_name	= trim( esc_attr( @$_POST['wpla_tracking_service_name'] ) );

		if ( ! empty( $tracking_provider ) && ! empty( $tracking_number ) ) {

			// validate shipping time
			if ( $time_shipped && ! DateTime::createFromFormat('H:i:s', $time_shipped) && ! DateTime::createFromFormat('H:i', $time_shipped) ) {
				$time_shipped = '';
			}

			// validate shipping date
			if ( $date_shipped ) {

				// if valid, convert from local timezone to UTC
				if ( DateTime::createFromFormat('Y-m-d', $date_shipped) ) {

					// if shipping time is empty, set to current local time before converting to UTC
					if ( ! $time_shipped ) {
						$tz = WPLA_DateTimeHelper::getLocalTimeZone();
						$dt = new DateTime('now', new DateTimeZone( $tz ));
						$time_shipped = $dt->format('H:i:s'); // current local time
					}

					// convert date/time from local timezone to UTC
					$tz = WPLA_DateTimeHelper::getLocalTimeZone();
					$dt = new DateTime( $date_shipped.' '.$time_shipped, new DateTimeZone( $tz ) );
					$dt->setTimeZone( new DateTimeZone('UTC') );
					$date_shipped = $dt->format('Y-m-d'); // current date in UTC
					$time_shipped = $dt->format('H:i:s'); // current time in UTC

				} else {
					// if invalid, set date to today
					$dt = new DateTime( 'now', new DateTimeZone('UTC') );
					$date_shipped = $dt->format('Y-m-d'); // current date in UTC
					$time_shipped = $dt->format('H:i:s'); // current time in UTC
				}

			}

			// if date is missing, but tracking number is set, set date to today
			if ( ! $date_shipped && $tracking_number ) {
				$dt = new DateTime( 'now', new DateTimeZone('UTC') );
				$date_shipped = $dt->format('Y-m-d'); // current date in UTC
				$time_shipped = $dt->format('H:i:s'); // current time in UTC
			}

			update_post_meta( $post_id, '_wpla_tracking_provider', 		$tracking_provider );
			update_post_meta( $post_id, '_wpla_tracking_number', 		$tracking_number );
			update_post_meta( $post_id, '_wpla_date_shipped', 			$date_shipped );
			update_post_meta( $post_id, '_wpla_time_shipped', 			$time_shipped );
			update_post_meta( $post_id, '_wpla_tracking_service_name', 	$tracking_service_name );

			$feed = new WPLA_AmazonFeed();
			$feed->updateShipmentFeed( $post_id );

		} // if tracking data set

	} // save_meta_box()


	// handle order status changed to "completed" - and complete amazon order
    public function handle_woocommerce_order_status_update_completed( $post_id ) {
	    WPLA()->logger->info('handle_woocommerce_order_status_update_completed for #'. $post_id);

    	// check if auto complete option is enabled
    	if ( get_option( 'wpla_auto_complete_sales' ) != 1 ) return;

    	// check if default status for new created orders is completed - skip further processing if it is
		if ( get_option( 'wpla_new_order_status', 'processing' ) == 'completed' ) return;

    	// check if this order came in from amazon
    	if ( ! get_post_meta( $post_id, '_wpla_amazon_order_id', true ) ) return;

    	// check if this order has already been submitted to Amazon
    	if ( get_post_meta( $post_id, '_wpla_date_shipped', true ) != '' ) return;


		// get order details from wp_amazon_orders
		$amazon_order_id = get_post_meta( $post_id, '_wpla_amazon_order_id', true );
		$om              = new WPLA_OrdersModel();
		$order           = $om->getOrderByOrderID( $amazon_order_id );

		// check if this order is already marked as Shipped on Amazon - skip if it is
    	$order_status = $order ? $order->status : false;
        if ( $order_status == 'Shipped' ) {
			WPLA()->logger->info('auto complete sales: skipped already shipped order from Shipment Feed - order id: '.$post_id);
        	return; // prevent resetting the shipment date when importing old shipped orders (like after restoring a backup)
        }

		// check if this is an FBA order - skip if it is
    	$order_details = $order ? json_decode( $order->details ) : false;
        if ( is_object( $order_details ) && ( $order_details->FulfillmentChannel == 'AFN' ) ) {
			WPLA()->logger->info('auto complete sales: skipped FBA order from Shipment Feed - order id: '.$post_id);
        	return; // FBA orders don't need to be completed
        }

		// set shipping date and time to now
		$dt = new DateTime('now', new DateTimeZone('UTC'));
		update_post_meta( $post_id, '_wpla_date_shipped', 			$dt->format('Y-m-d') );
		update_post_meta( $post_id, '_wpla_time_shipped', 			$dt->format('H:i:s') ); // UTC timezone

	    // only use the default shipping provider if it hasn't been previously set yet
	    if ( get_post_meta( $post_id, '_wpla_tracking_provider', true ) == '' ) {
		    update_post_meta( $post_id, '_wpla_tracking_provider', 		get_option( 'wpla_default_shipping_provider', '' ) );
		    update_post_meta( $post_id, '_wpla_tracking_service_name', 	get_option( 'wpla_default_shipping_service_name', '' ) );
	    }

		// check if there are tracking details stored by other plugins - like Shipstation or Shipment Tracking
		$wpl_tracking_provider = trim( get_post_meta( $post_id, '_tracking_provider', true ) );
		$wpl_tracking_number   = trim( get_post_meta( $post_id, '_tracking_number', true ) );
		if ( $wpl_tracking_number && $wpl_tracking_provider ) {
			if ( in_array( $wpl_tracking_provider, WPLA_Order_MetaBox::getShippingProviders() ) ) {
				update_post_meta( $post_id, '_wpla_tracking_provider', 		$wpl_tracking_provider );
				update_post_meta( $post_id, '_wpla_tracking_service_name', 	'' );
			} else {
				update_post_meta( $post_id, '_wpla_tracking_provider', 		'Other' );
				update_post_meta( $post_id, '_wpla_tracking_service_name', 	$wpl_tracking_provider );
			}
			update_post_meta( $post_id, '_wpla_tracking_number', 		$wpl_tracking_number );
		}

		// check for tracking details stored by WooForce Shipment Tracking plugin
		$wf_wc_shipment_source = maybe_unserialize( get_post_meta( $post_id, 'wf_wc_shipment_source', true ) );
		if ( is_array( $wf_wc_shipment_source ) && empty( $wpl_tracking_number ) ) {
			update_post_meta( $post_id, '_wpla_tracking_provider', 		'Other' );
			update_post_meta( $post_id, '_wpla_tracking_service_name', 	$wf_wc_shipment_source['shipping_service'] );
			update_post_meta( $post_id, '_wpla_tracking_number', 		$wf_wc_shipment_source['shipment_id_cs'] );			
		}

        // add support for WC Shipment Tracking v1.6.6 which stores tracking data using a different meta key
        $wc_tracking_data = get_post_meta( $post_id, '_wc_shipment_tracking_items', true );
        if ( $wc_tracking_data ) {
            $wc_tracking_data = current( $wc_tracking_data );

            // try to get the formatted tracking provider #20091
            $tracking_provider = $wc_tracking_data['tracking_provider'];
            if ( class_exists( 'WC_Shipment_Tracking_Actions' ) ) {
                $shipment_tracking = WC_Shipment_Tracking_Actions::get_instance();
                $formatted_tracking_item = $shipment_tracking->get_formatted_tracking_item( $post_id, $wc_tracking_data );

                if ( $formatted_tracking_item['formatted_tracking_provider'] ) {
                    $tracking_provider = $formatted_tracking_item['formatted_tracking_provider'];
                }
            }

            update_post_meta( $post_id, '_wpla_tracking_provider',      'Other' );
            update_post_meta( $post_id, '_wpla_tracking_service_name',  $tracking_provider );
            update_post_meta( $post_id, '_wpla_tracking_number',        $wc_tracking_data['tracking_number'] );
        }

		// update shipment feed
		$feed = new WPLA_AmazonFeed();
		$feed->updateShipmentFeed( $post_id );

    } // handle_woocommerce_order_status_update_completed()


    /**
     * update shipping date and tracking details on amazon (ajax)
     */
    function update_amazon_shipment_ajax() {

		// get field values
        $post_id 					= $_REQUEST['order_id'];
		$wpla_tracking_provider		= trim( esc_attr( $_REQUEST['wpla_tracking_provider'] ) );
		$wpla_tracking_number 		= trim( esc_attr( $_REQUEST['wpla_tracking_number'] ) );
		$wpla_date_shipped			= trim( esc_attr( $_REQUEST['wpla_date_shipped'] ) );
		$wpla_time_shipped			= trim( esc_attr( $_REQUEST['wpla_time_shipped'] ) );
		$wpla_tracking_service_name	= trim( esc_attr( $_REQUEST['wpla_tracking_service_name'] ) );

	    WPLA()->logger->info( 'update_amazon_shipment_ajax request data: ' . print_r( $_REQUEST, true ) );

		// validate shipping time
		if ( $wpla_time_shipped && ! DateTime::createFromFormat('H:i:s', $wpla_time_shipped) && ! DateTime::createFromFormat('H:i', $wpla_time_shipped) ) {
			$wpla_time_shipped = '';
		}

		// validate shipping date 
		if ( $wpla_date_shipped ) {

			// if valid, convert from local timezone to UTC
			if ( DateTime::createFromFormat('Y-m-d', $wpla_date_shipped) ) {

				// if shipping time is empty, set to current local time before converting to UTC
				if ( ! $wpla_time_shipped ) {
					$tz = WPLA_DateTimeHelper::getLocalTimeZone();
					$dt = new DateTime('now', new DateTimeZone( $tz ));
					$wpla_time_shipped = $dt->format('H:i:s'); // current local time
				}

				// convert date/time from local timezone to UTC
				$tz = WPLA_DateTimeHelper::getLocalTimeZone();
				$dt = new DateTime( $wpla_date_shipped.' '.$wpla_time_shipped, new DateTimeZone( $tz ) );
				$dt->setTimeZone( new DateTimeZone('UTC') );
				$wpla_date_shipped = $dt->format('Y-m-d'); // current date in UTC
				$wpla_time_shipped = $dt->format('H:i:s'); // current time in UTC

			} else {
				// if invalid, set date to today
				$dt = new DateTime( 'now', new DateTimeZone('UTC') );
				$wpla_date_shipped = $dt->format('Y-m-d'); // current date in UTC
				$wpla_time_shipped = $dt->format('H:i:s'); // current time in UTC
			}

		}

		// if date is missing, but tracking number is set, set date to today
		if ( ! $wpla_date_shipped && $wpla_tracking_number ) {
			$dt = new DateTime( 'now', new DateTimeZone('UTC') );
			$wpla_date_shipped = $dt->format('Y-m-d'); // current date in UTC
			$wpla_time_shipped = $dt->format('H:i:s'); // current time in UTC
		}


		// update order data
		update_post_meta( $post_id, '_wpla_tracking_provider', 		$wpla_tracking_provider );
		update_post_meta( $post_id, '_wpla_tracking_number', 		$wpla_tracking_number );
		update_post_meta( $post_id, '_wpla_date_shipped', 			$wpla_date_shipped );
		update_post_meta( $post_id, '_wpla_time_shipped', 			$wpla_time_shipped );
		update_post_meta( $post_id, '_wpla_tracking_service_name', 	$wpla_tracking_service_name );


		$response = new stdClass();

		if ( ! $wpla_date_shipped ) {
			$response->success = false;
			$response->error = 'You need to select a shipping date.';
		} else {
			$feed = new WPLA_AmazonFeed();
			$feed->updateShipmentFeed( $post_id );
			$response->success = true;
		}

        $this->returnJSON( $response );
        exit();

    } // update_amazon_shipment_ajax()


    /**
     * submit order to be fulfilled via FBA (ajax)
     */
    function submit_order_to_fba() {
        // only run if FBA is enabled #15403
        if ( ! get_option( 'wpla_fba_enabled' ) ) {
            WPLA()->logger->info( 'submit_order_to_fba() skipped because FBA is disabled' );
            $response = new stdClass();
            $response->success = false;
            $response->error = 'FBA is disabled';

            $this->returnJSON( $response );
            exit();
        }

		// get field values
        $post_id = $_REQUEST['order_id'];

		// update order data
		update_post_meta( $post_id, '_wpla_DeliverySLA', 			 trim( esc_attr( $_REQUEST['wpla_DeliverySLA'] ) ) );
		update_post_meta( $post_id, '_wpla_NotificationEmail', 		 trim( esc_attr( $_REQUEST['wpla_NotificationEmail'] ) ) );
		update_post_meta( $post_id, '_wpla_DisplayableOrderComment', trim( esc_attr( $_REQUEST['wpla_DisplayableOrderComment'] ) ) );
		// update_post_meta( $post_id, '_wpla_fba_submission_status',   'submitted' );

		// create FBA feed
		$response = WPLA_FbaHelper::submitOrderToFBA( $post_id );

		// if ( $missing ) {
		// 	$response = new stdClass();
		// 	$response->success = false;
		// 	$response->error = 'You need to select a shipping date.';
		// }

        $this->returnJSON( $response );
        exit();

    } // submit_order_to_fba()

    public function returnJSON( $data ) {
        header('content-type: application/json; charset=utf-8');
        echo json_encode( $data );
    }
    

} // class WPLA_Order_MetaBox
// $WPLA_Order_MetaBox = new WPLA_Order_MetaBox();

## END PRO ##

