<?php
	/**
	 * WC_Shipment_Tracking class
	 */
	if ( ! class_exists( 'WC_Shipment_Tracking' ) ) {

		class WC_Shipment_Tracking {

			/**
			 * Constructor
			 */
			public function __construct() {
				add_action( 'admin_print_styles', array( $this, 'admin_styles' ) );
				add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
				add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_meta_box' ), 0, 2 );
				add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

				// View Order Page
				add_action( 'woocommerce_view_order', array( $this, 'display_tracking_info' ) );
				add_action( 'woocommerce_email_before_order_table', array( $this, 'email_display' ) );

				// Customer / Order CSV Export column headers/data
				add_filter( 'wc_customer_order_csv_export_order_headers',   array( $this, 'add_tracking_info_to_csv_export_column_headers' ) );
				add_filter( 'wc_customer_order_csv_export_order_row',       array( $this, 'add_tracking_info_to_csv_export_column_data' ), 10, 3 );
			}

			/**
			 * Get shiping providers
			 * @return array
			 */
			public function get_providers() {
				return apply_filters( 'wc_shipment_tracking_get_providers', array(
					'Argentina' => array(
						'EnvioPack'
							=> 'https://seguimiento.enviopack.com/%1$s',
					),					 
				) );
			}

			/**
			 * Localisation
			 */
			public function load_plugin_textdomain() {
				load_plugin_textdomain( 'wc_shipment_tracking', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			}

			public function admin_styles() {
				wp_enqueue_style( 'shipment_tracking_styles', plugins_url( basename( dirname( __FILE__ ) ) ) . '/assets/css/admin.css' );
			}

			/**
			 * Add the meta box for shipment info on the order page
			 *
			 * @access public
			 */
			public function add_meta_box() {
				add_meta_box( 'woocommerce-shipment-tracking', __('Seguimiento de Envios', 'wc_shipment_tracking'), array( $this, 'meta_box' ), 'shop_order', 'side', 'high');
			}

			/**
			 * Show the meta box for shipment info on the order page
			 *
			 * @access public
			 */
			public function meta_box() {
				global $woocommerce, $post;

				// Providers
				echo '<p class="form-field tracking_provider_field"><label for="tracking_provider">' . __('Empresa:', 'wc_shipment_tracking') . '</label><br/><select id="tracking_provider" name="tracking_provider" class="chosen_select" style="width:100%;">';

				echo '<option value="">' . __('Ingreso Manual', 'wc_shipment_tracking') . '</option>';

				$selected_provider = get_post_meta( $post->ID, '_tracking_provider', true );

				if ( ! $selected_provider )
					$selected_provider = sanitize_title( apply_filters( 'woocommerce_shipment_tracking_default_provider', '' ) );

				foreach ( $this->get_providers() as $provider_group => $providers ) {

					echo '<optgroup label="' . $provider_group . '">';

					foreach ( $providers as $provider => $url ) {

						echo '<option value="' . sanitize_title( $provider ) . '" ' . selected( sanitize_title( $provider ), $selected_provider, true ) . '>' . $provider . '</option>';

					}

					echo '</optgroup>';

				}

				echo '</select> ';

				woocommerce_wp_text_input( array(
					'id' 			=> 'custom_tracking_provider',
					'label' 		=> __('Empresa:', 'wc_shipment_tracking'),
					'placeholder' 	=> '',
					'description' 	=> '',
					'value'			=> get_post_meta( $post->ID, '_custom_tracking_provider', true )
				) );

				woocommerce_wp_text_input( array(
					'id' 			=> 'tracking_number',
					'label' 		=> __('Tracking:', 'wc_shipment_tracking'),
					'placeholder' 	=> '',
					'description' 	=> '',
					'value'			=> get_post_meta( $post->ID, '_tracking_number', true )
				) );

				woocommerce_wp_text_input( array(
					'id' 			=> 'custom_tracking_link',
					'label' 		=> __('Link:', 'wc_shipment_tracking'),
					'placeholder' 	=> 'http://',
					'description' 	=> '',
					'value'			=> get_post_meta( $post->ID, '_custom_tracking_link', true )
				) );

				woocommerce_wp_text_input( array(
					'id' 			=> 'date_shipped',
					'label' 		=> __('Fecha Envio:', 'wc_shipment_tracking'),
					'placeholder' 	=> 'YYYY-MM-DD',
					'description' 	=> '',
					'class'			=> 'date-picker-field',
					'value'			=> ( $date = get_post_meta( $post->ID, '_date_shipped', true ) ) ? date( 'Y-m-d', $date ) : ''
				) );

				// Live preview
				echo '<p class="preview_tracking_link">' . __('Ver:', 'wc_shipment_tracking') . ' <a href="" target="_blank">' . __('Haga clic aquí para rastrear su envío', 'wc_shipment_tracking') . '</a></p>';

				$provider_array = array();

				foreach ( $this->get_providers() as $providers ) {
					foreach ( $providers as $provider => $format ) {
						$provider_array[sanitize_title( $provider )] = urlencode( $format );
					}
				}

				$js = "
					jQuery('p.custom_tracking_link_field, p.custom_tracking_provider_field').hide();

					jQuery('input#custom_tracking_link, input#tracking_number, #tracking_provider').change(function(){

						var tracking = jQuery('input#tracking_number').val();
						var provider = jQuery('#tracking_provider').val();
						var providers = jQuery.parseJSON( '" . json_encode( $provider_array ) . "' );

						var postcode = jQuery('#_shipping_postcode').val();

						if ( ! postcode )
							postcode = jQuery('#_billing_postcode').val();

						postcode = encodeURIComponent( postcode );

						var link = '';

						if ( providers[ provider ] ) {
							link = providers[provider];
							link = link.replace( '%251%24s', tracking );
							link = link.replace( '%252%24s', postcode );
							link = decodeURIComponent( link );

							jQuery('p.custom_tracking_link_field, p.custom_tracking_provider_field').hide();
						} else {
							jQuery('p.custom_tracking_link_field, p.custom_tracking_provider_field').show();

							link = jQuery('input#custom_tracking_link').val();
						}

						if ( link ) {
							jQuery('p.preview_tracking_link a').attr('href', link);
							jQuery('p.preview_tracking_link').show();
						} else {
							jQuery('p.preview_tracking_link').hide();
						}

					}).change();
				";

				if ( function_exists( 'wc_enqueue_js' ) ) {
					wc_enqueue_js( $js );
				} else {
					$woocommerce->add_inline_js( $js );
				}
			}

			/**
			 * Order Downloads Save
			 *
			 * Function for processing and storing all order downloads.
			 */
			public function save_meta_box( $post_id, $post ) {
				if ( isset( $_POST['tracking_number'] ) ) {

					// Download data
					$tracking_provider        = woocommerce_clean( $_POST['tracking_provider'] );
					$custom_tracking_provider = woocommerce_clean( $_POST['custom_tracking_provider'] );
					$custom_tracking_link     = woocommerce_clean( $_POST['custom_tracking_link'] );
					$tracking_number          = woocommerce_clean( $_POST['tracking_number'] );
					$date_shipped             = woocommerce_clean( strtotime( $_POST['date_shipped'] ) );

					// Update order data
					update_post_meta( $post_id, '_tracking_provider', $tracking_provider );
					update_post_meta( $post_id, '_custom_tracking_provider', $custom_tracking_provider );
					update_post_meta( $post_id, '_tracking_number', $tracking_number );
					update_post_meta( $post_id, '_custom_tracking_link', $custom_tracking_link );
					update_post_meta( $post_id, '_date_shipped', $date_shipped );
				}
			}

			/**
			 * Display Shipment info in the frontend (order view/tracking page).
			 *
			 * @access public
			 */
			public function display_tracking_info( $order_id, $for_email = false ) {

				$tracking_provider = get_post_meta( $order_id, '_tracking_provider', true );
				$tracking_number   = get_post_meta( $order_id, '_tracking_number', true );
				$date_shipped      = get_post_meta( $order_id, '_date_shipped', true );
				$postcode          = get_post_meta( $order_id, '_shipping_postcode', true );

				if ( ! $postcode )
					$postcode		= get_post_meta( $order_id, '_billing_postcode', true );

				if ( ! $tracking_number )
					return;

				if ( $date_shipped )
					$date_shipped = ' ' . sprintf( __( 'el %s', 'wc_shipment_tracking' ), date_i18n( __( 'l j F Y', 'wc_shipment_tracking'), $date_shipped ) );

				$tracking_link = '';

				if ( $tracking_provider ) {

					$link_format = '';

					foreach ( $this->get_providers() as $providers ) {
						foreach ( $providers as $provider => $format ) {
							if ( sanitize_title( $provider ) == $tracking_provider ) {
								$link_format = $format;
								$tracking_provider = $provider;
								break;
							}
						}
						if ( $link_format ) break;
					}

					if ( $link_format ) {
						$link = sprintf( $link_format, $tracking_number, urlencode( $postcode ) );
						if ( $for_email ) {
							$tracking_link = sprintf( __('Haga clic aquí para rastrear su envío', 'wc_shipment_tracking') . ': <a href="%s">%s</a>', $link, $link );
						} else {
							$tracking_link = sprintf( '<a href="%s">' . __('Haga clic aquí para rastrear su envío', 'wc_shipment_tracking') . '.</a>', $link, $link );
						}
					}

					$tracking_provider = ' ' . __('por', 'wc_shipment_tracking') . ' <strong>' . $tracking_provider . '</strong>';

					echo wpautop( sprintf( __('Your order was shipped%s%s. Tracking number %s. %s', 'wc_shipment_tracking'), $date_shipped, $tracking_provider, $tracking_number, $tracking_link ) );

				} else {

					$custom_tracking_link     = get_post_meta( $order_id, '_custom_tracking_link', true );
					$custom_tracking_provider = get_post_meta( $order_id, '_custom_tracking_provider', true );

					if ( $custom_tracking_provider )
						$tracking_provider = ' ' . __('por', 'wc_shipment_tracking') . ' <strong>' . $custom_tracking_provider . '</strong>';
					else
						$tracking_provider = '';

					if ( $custom_tracking_link ) {
						$tracking_link = sprintf( '<a href="%s">' . __('Haga clic aquí para rastrear su envío', 'wc_shipment_tracking') . '.</a>', $custom_tracking_link . $tracking_number );
					} elseif ( strstr( $tracking_number, '<a' ) ) {
						$tracking_link = sprintf( '<a href="%s">%s.</a>', $tracking_number, $tracking_number );
					} else {
						$tracking_link = '';
					}

					echo wpautop( sprintf( __('Su pedido fue enviado%s%s. El número de rastreo %s. %s', 'wc_shipment_tracking'), $date_shipped, $tracking_provider, $tracking_number, $tracking_link ) );
				}

			}

			/**
			 * Display shipment info in customer emails.
			 *
			 * @access public
			 * @return void
			 */
			public function email_display( $order ) {
				$this->display_tracking_info( $order->id, true );
			}

			/**
			 * Adds support for Customer/Order CSV Export by adding appropraite column headers
			 *
			 * @param array $headers existing array of header key/names for the CSV export
			 * @return array
			 */
			public function add_tracking_info_to_csv_export_column_headers( $headers ) {

				$headers['tracking_provider']        = 'tracking_provider';
				$headers['custom_tracking_provider'] = 'custom_tracking_provider';
				$headers['tracking_number']          = 'tracking_number';
				$headers['custom_tracking_link']     = 'custom_tracking_link';
				$headers['date_shipped']             = 'date_shipped';

				return $headers;
			}

			/**
			 * Adds support for Customer/Order CSV Export by adding data for the column headers
			 *
			 * @param array $order_data generated order data matching the column keys in the header
			 * @param WC_Order $order order being exported
			 * @param \WC_CSV_Export_Generator $csv_generator instance
			 * @return array
			 */
			public function add_tracking_info_to_csv_export_column_data( $order_data, $order, $csv_generator ) {

				$tracking_provider        = get_post_meta( $order->id, '_tracking_provider', true );
				$custom_tracking_provider = get_post_meta( $order->id, '_custom_tracking_provider', true );
				$tracking_number          = get_post_meta( $order->id, '_tracking_number', true );
				$custom_tracking_link     = get_post_meta( $order->id, '_custom_tracking_link', true );
				$date_shipped             = get_post_meta( $order->id, '_date_shipped', true );

				$tracking_data = array(
					'tracking_provider'        => $tracking_provider,
					'custom_tracking_provider' => $custom_tracking_provider,
					'tracking_number'          => $tracking_number,
					'custom_tracking_link'     => $custom_tracking_link,
					'date_shipped'             => $date_shipped ? date_i18n( __( 'Y-m-d', 'wc_shipment_tracking' ), $date_shipped ) : '',
				);

				$new_order_data = array();

				if ( isset( $csv_generator->order_format ) && ( 'default_one_row_per_item' == $csv_generator->order_format || 'legacy_one_row_per_item' == $csv_generator->order_format ) ) {

					foreach ( $order_data as $data ) {
						$new_order_data[] = array_merge( (array) $data, $tracking_data );
					}

				} else {

					$new_order_data = array_merge( $order_data, $tracking_data );
				}

				return $new_order_data;
			}

		}

	}

	/**
	 * Register this class globally
	 */
	$GLOBALS['WC_Shipment_Tracking'] = new WC_Shipment_Tracking();