<?php
/**
 * Main class
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Mailchimp
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCMC' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCMC_Premium' ) ) {
	/**
	 * WooCommerce Mailchimp
	 *
	 * @since 1.0.0
	 */
	class YITH_WCMC_Premium extends YITH_WCMC {
		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCMC_Premium
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCMC_Premium
		 * @since 1.0.0
		 */
		public static function get_instance(){
			if( is_null( self::$instance ) ){
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @param array $details
		 * @return \YITH_WCMC_Premium
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'register_shortcode' ) );
			add_action( 'init', array( $this, 'post_form_subscribe' ) );
			add_action( 'init', array( $this, 'register_campaign_cookie' ) );
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'register_ecommerce360_campaign_data' ), 10, 1 );
			add_action( 'woocommerce_order_status_completed', array( $this, 'ecommerce360_handling' ), 15, 1 );
			add_action( 'woocommerce_order_status_processing', array( $this, 'ecommerce360_handling' ), 15, 1 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

			// handles ajax requests
			add_action( 'wp_ajax_yith_wcmc_subscribe', array( $this, 'ajax_form_subscribe' ) );
			add_action( 'wp_ajax_nopriv_yith_wcmc_subscribe', array( $this, 'ajax_form_subscribe' ) );

			// inits widget
			add_action( 'widgets_init', array( $this, 'register_widget' ) );

			parent::__construct();
		}

		/* === ENQUEUE SCRIPTS === */

		/**
		 * Enqueue scripts
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function enqueue() {
			$path = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '/unminified' : '';
			$prefix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '' : '.min';

			wp_enqueue_script( 'yith-wcmc', YITH_WCMC_URL . 'assets/js' . $path . '/yith-wcmc' . $prefix . '.js', array( 'jquery', 'jquery-blockui' ), YITH_WCMC_VERSION, true );

			wp_localize_script( 'yith-wcmc', 'yith_wcmc', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'actions' => array(
					'yith_wcmc_subscribe_action' => 'yith_wcmc_subscribe'
				)
			) );
		}
		
		/* === HANDLE ECOMMERCE 360 INTEGRATION === */
		
		/**
		 * Register cookie for ecommerce 360 campagins
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function register_campaign_cookie() {
			$ecommerce360_enable = 'yes' == get_option( 'yith_wcmc_ecommerce360_enable' );
			$ecommerce360_cookie_lifetime = get_option( 'yith_wcmc_ecommerce360_cookie_lifetime' );

			if( isset( $_GET['mc_cid'] ) || isset( $_GET['mc_eid'] ) && $ecommerce360_enable ){
				$data_to_set = array();

				if( ! empty( $_GET['mc_cid'] ) ){
					$data_to_set['cid'] = $_GET['mc_cid'];
				}

				if( ! empty( $_GET['mc_eid'] ) ){
					$data_to_set['eid'] = $_GET['mc_eid'];
				}

				$parsed_data = urlencode( serialize( $data_to_set ) );

				wc_setcookie( 'yith_wcmc_ecommerce_360', $parsed_data, time() + $ecommerce360_cookie_lifetime );
			}
		}

		/* === HANDLE ORDER SUBSCRIPTION === */

		/**
		 * Call subscribe API handle, to register user to a specific list
		 *
		 * @param $order_id int Order id
		 *
		 * @return bool status of the operation
		 */
		public function order_subscribe( $order_id, $args = array() ){
			$order = wc_get_order( $order_id );
			$integration_mode = get_option( 'yith_wcmc_mailchimp_integration_mode', 'simple' );
			$args = array();

			$args['replace_interests'] = 'yes' == get_option( 'yith_wcmc_replace_interests', true );

			if( 'simple' == $integration_mode ){
				//manage groups
				$selected_groups = get_option( 'yith_wcmc_mailchimp_groups', array() );
				$group_structure = $this->_create_group_structure( $selected_groups );

				if( ! empty( $group_structure ) ){
					$args['merge_vars']['groupings'] = $group_structure;
					$args['merge_vars']['FNAME'] = yit_get_prop( $order, 'billing_first_name', true );
					$args['merge_vars']['LNAME'] = yit_get_prop( $order, 'billing_last_name', true );
				}

				$res = parent::order_subscribe( $order_id, $args );
			}
			else{
				$res = true;
				$advanced_options = get_option( 'yith_wcmc_advanced_integration', array() );

				if( ! empty( $advanced_options ) ){
					foreach( $advanced_options as $option ){

						// checks conditions
						$selected_conditions = isset( $option['conditions'] ) ? $option['conditions'] : array();
						if( ! empty( $selected_conditions ) ){
							if( ! $this->_check_conditions( $selected_conditions, $order_id ) ){
								continue;
							}
						}

						// set list id to current section
						$args['id'] = $option['list'];

						// manage groups
						$selected_groups = isset( $option['groups'] ) ? $option['groups'] : array();
						$group_structure = $this->_create_group_structure( $selected_groups );

						if( ! empty( $group_structure ) ){
							$args['merge_vars']['groupings'] = $group_structure;
						}

						// manage fields
						$selected_fields = isset( $option['fields'] ) ? $option['fields'] : array();
						$field_structure = $this->_create_field_structure( $selected_fields, $order_id );

						if( ! empty( $field_structure ) ){
							$args['merge_vars'] = array_merge(
								isset( $args['merge_vars'] ) ? $args['merge_vars'] : array(),
								$field_structure
							);
						}

						$partial = parent::order_subscribe( $order_id, $args );
						$res = ( ! $partial ) ? false : $res;
					}
				}
			}

			return $res;
		}

		/**
		 * Register MailChimp eCommerce360 campaign data (if ecommerce 360 integration is enabled)
		 *
		 * @param $order_id int WooCommerce order id
		 * @return void
		 * @since 1.0.0
		 */
		public function register_ecommerce360_campaign_data( $order_id ) {
			$order = wc_get_order( $order_id );

			// handle campaigns (if ecommerce 360 integration is enabled)
			$ecommerce360_enable = 'yes' == get_option( 'yith_wcmc_ecommerce360_enable' );

			if( $ecommerce360_enable ) {
				$cookie_campaign = isset( $_COOKIE['yith_wcmc_ecommerce_360'] ) ? maybe_unserialize( urldecode( $_COOKIE['yith_wcmc_ecommerce_360'] ) ) : false;
				yit_save_prop( $order, '_yith_wcmc_ecommerce_360_data', $cookie_campaign );

				// delete cookie
				wc_setcookie( 'yith_wcmc_ecommerce_360', '', time() - 3600 );
			}
		}

		/**
		 * Handle campaigns
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function ecommerce360_handling( $order_id ){
			$order = wc_get_order( $order_id );

			$parse              = parse_url( site_url() );
			$campaign_data      = get_post_meta( $order_id, '_yith_wcmc_ecommerce_360_data', true );
			$campaign_processed = get_post_meta( $order_id, '_yith_wcmc_ecommerce_360_processed', true );

			if ( $campaign_processed != 'yes' && isset( $campaign_data['cid'] ) && isset( $campaign_data['eid'] ) ) {
				$request_arg = array(
					'order' => array(
						'id'          => $order_id,
						'campaign_id' => isset( $campaign_data['cid'] ) ? $campaign_data['cid'] : false,
						'email_id'    => isset( $campaign_data['eid'] ) ? $campaign_data['eid'] : false,
						'email'       => yit_get_prop( $order, 'billing_email', true ),
						'total'       => $order->get_total(),
						'order_date'  => date( 'Y-m-d H:i:s', strtotime( yit_get_prop( $order, 'order_date' ) ) ),
						'shipping'    => method_exists( $order, 'get_shipping_total' ) ? $order->get_shipping_total() : $order->get_total_shipping(),
						'tax'         => $order->get_total_tax(),
						'store_id'    => substr( preg_replace( '/[^a-zA-Z0-9]+/', '', $parse['host'] ), 0, 32 ),
						'store_name'  => $parse['host']
					)
				);

				$items_arg = array();
				$line_items = $order->get_items( 'line_item' );

				if( ! empty( $line_items ) ){
					foreach( $line_items as $item_id => $item ){
						if( is_object( $item ) ){
							/**
							 * @var $item \WC_Order_Item_Product
							 */
							$product_id = $item->get_product_id();
							$product = $item->get_product();
						}
						else {
							$product_id = ( ! empty( $item['variation_id'] ) ) ? $item['variation_id'] : $item['product_id'];
							$product    = wc_get_product( $product_id );
						}
						
						$product_terms = get_the_terms( $product_id, 'product_cat' );

						if( empty( $product_terms ) ){
							continue;
						}

						$main_product_term = $product_terms[0];

						$items_arg[] = array(
							'line_num' => $item_id,
							'product_id' => $product_id,
							'sku' => yit_get_prop( $product, 'sku', true ),
							'product_name' => $product->get_title(),
							'category_id' => $main_product_term->term_id,
							'category_name' => $main_product_term->name,
							'qty' => $item['qty'],
							'cost' => $order->get_item_total( $item )
						);
					}
				}

				$request_arg['order']['items'] = $items_arg;
				$this->do_request( 'ecomm/order-add', $request_arg );

				yit_save_prop( $order, '_yith_wcmc_ecommerce_360_processed', 'yes' );
			}
		}

		/**
		 * Create structure to register mail to interest groups
		 *
		 * @param $selected_groups array Array of selected groups
		 *
		 * @return array A valid array to use in subscription request
		 * @since 1.0.0
		 */
		protected function _create_group_structure( $selected_groups ){
			if( empty( $selected_groups ) ) {
				return array();
			}

			$group_to_register = array();
			$group_structure = array();

			foreach( $selected_groups as $group ){
				if( strpos( $group, '-' ) === false ){
					continue;
				}

				list( $group_id, $interest_group ) = explode( '-', $group );

				if( ! isset( $group_to_register[ $group_id ] ) ){
					$group_to_register[ $group_id ] = array( $interest_group );
				}
				elseif( ! in_array( $group, $group_to_register[ $group_id ] ) ){
					$group_to_register[ $group_id ][] = $interest_group;
				}
			}

			if( ! empty( $group_to_register ) ){
				foreach( $group_to_register as $id => $interest_groups ){
					$group_structure[] = array(
						'id' => $id,
						'groups' => $interest_groups
					);
				}
			}
			return $group_structure;
		}

		/**
		 * Create structure to register fields to a specific user
		 *
		 * @param $selected_fields array Array of selected fields to register
		 * @param $order_id int Order id
		 *
		 * @return array A valid array to use in subscription request
		 * @since 1.0.0
		 */
		protected function _create_field_structure( $selected_fields, $order_id ){
			if( empty( $selected_fields ) ){
				return array();
			}

			$order = wc_get_order( $order_id );

			if( empty( $order ) ){
				return array();
			}

			$field_structure = array();

			foreach( $selected_fields as $field ){
				$field_structure[ $field['merge_var'] ] = yit_get_prop( $order, $field['checkout'], true );
			}

			return $field_structure;
		}

		/**
		 * Check if selected conditions are matched
		 *
		 * @param $selected_conditions array Array of selected conditions to match
		 * @param $order_id int Order id
		 *
		 * @return boolean True, if all conditions are matched; false otherwise
		 * @since 1.0.0
		 */
		protected function _check_conditions( $selected_conditions, $order_id ){
			$order = wc_get_order( $order_id );
			$condition_result = true;

			if( empty( $selected_conditions ) ){
				return true;
			}

			foreach( $selected_conditions as $condition ){
				$condition_type = $condition['condition'];

				switch( $condition_type ){
					case 'product_in_cart':

						$set_operator = $condition['op_set'];
						$selected_products = explode( ',', $condition['products'] );
						$items = $order->get_items( 'line_item' );
						$products_in_cart = array();

						if( ! empty( $items ) ){
							foreach( $items as $item ){
								if( is_object( $item ) ){
									/**
									 * @var $item \WC_Order_Item_Product
									 */
									$products_in_cart[] = $item->get_product_id();
								}
								else {
									if ( ! empty( $item['product_id'] ) && ! in_array( $item['product_id'], $products_in_cart ) ) {
										$products_in_cart[] = $item['product_id'];
									}

									if ( ! empty( $item['variation_id'] ) && ! in_array( $item['variation_id'], $products_in_cart ) ) {
										$products_in_cart[] = $item['variation_id'];
									}
								}
							}
						}

						switch( $set_operator ){
							case 'contains_one':

								if( ! empty( $selected_products ) && ! empty( $products_in_cart ) ){
									$found = false;
									foreach( (array) $selected_products as $product ){
										if( in_array( $product, $products_in_cart ) ){
											$found = true;
											break;
										}
									}

									if( ! $found ){
										$condition_result = false;
									}
								}
								elseif( ! empty( $selected_products ) ){
									$condition_result = false;
								}

								break;
							case 'contains_all':

								if( ! empty( $selected_products ) && ! empty( $products_in_cart ) ){
									foreach( (array) $selected_products as $product ){
										if( ! in_array( $product, $products_in_cart ) ){
											$condition_result = false;
											break;
										}
									}
								}
								elseif( ! empty( $selected_products ) ){
									$condition_result = false;
								}

								break;
							case 'not_contain':

								if( ! empty( $selected_products ) && ! empty( $products_in_cart ) ){
									foreach( (array) $selected_products as $product ){
										if( in_array( $product, $products_in_cart ) ){
											$condition_result = false;
											break;
										}
									}
								}
								elseif( ! empty( $selected_products ) ){
									$condition_result = false;
								}

								break;
						}

						break;
					case 'product_cat_in_cart':

						$set_operator = $condition['op_set'];
						$selected_cats = $condition['prod_cats'];
						$items = $order->get_items( 'line_item' );
						$cats_in_cart = array();

						if( ! empty( $items ) ){
							foreach( $items as $item ){
								/**
								 * @var $item array|\WC_Order_Item_Product
								 */
								$product_id = is_object( $item ) ? $item->get_product_id() : $item['product_id'];
								$item_terms = get_the_terms( $product_id, 'product_cat' );

								if( ! empty( $item_terms ) ){
									foreach( $item_terms as $term ){
										if( ! in_array( $term->term_id, $cats_in_cart ) ){
											$cats_in_cart[] = $term->term_id;
										}
									}
								}
							}
						}

						switch( $set_operator ){
							case 'contains_one':

								if( ! empty( $selected_cats ) && ! empty( $cats_in_cart ) ){
									$found = false;
									foreach( (array) $selected_cats as $cat ){
										if( in_array( $cat, $cats_in_cart ) ){
											$found = true;
											break;
										}
									}

									if( ! $found ){
										$condition_result = false;
									}
								}
								elseif( ! empty( $selected_cats ) ){
									$condition_result = false;
								}

								break;
							case 'contains_all':

								if( ! empty( $selected_cats ) && ! empty( $cats_in_cart ) ){
									foreach( (array) $selected_cats as $cat ){
										if( ! in_array( $cat, $cats_in_cart ) ){
											$condition_result = false;
											break;
										}
									}
								}
								elseif( ! empty( $selected_cats ) ){
									$condition_result = false;
								}

								break;
							case 'not_contain':

								if( ! empty( $selected_cats ) && ! empty( $cats_in_cart ) ){
									foreach( (array) $selected_cats as $cat ){
										if( in_array( $cat, $cats_in_cart ) ){
											$condition_result = false;
											break;
										}
									}
								}
								elseif( ! empty( $selected_cats ) ){
									$condition_result = false;
								}

								break;
						}

						break;
					case 'order_total':

						$number_operator = $condition['op_number'];
						$threshold = $condition['order_total'];
						$order_total = $order->get_total();

						switch( $number_operator ){
							case 'less_than':
								if( ! ( $order_total < $threshold ) ){
									$condition_result = false;
								}
								break;
							case 'less_or_equal':
								if( ! ( $order_total <= $threshold ) ){
									$condition_result = false;
								}
								break;
							case 'equal':
								if( ! ( $order_total == $threshold ) ){
									$condition_result = false;
								}
								break;
							case 'greater_or_equal':
								if( ! ( $order_total >= $threshold ) ){
									$condition_result = false;
								}
								break;
							case 'greater_than':
								if( ! ( $order_total > $threshold ) ){
									$condition_result = false;
								}
								break;
						}

						break;
					case 'custom':

						$operator = $condition['op_mixed'];
						$field_key = $condition['custom_key'];
						$expected_value = $condition['custom_value'];

						// retrieve field value (first check in post meta)
						$field = yit_get_prop( $order, $field_key, true );

						// retrieve field value (then check in $_REQUEST superglobal)
						if( empty( $field ) ){
							$field = isset( $_REQUEST[ $field_key ] ) ? $_REQUEST[ $field_key ] : '';
						}

						// nothing found? condition failed
						if( empty( $field ) ){
							$condition_result = false;
							break;
						}

						switch( $operator ){
							case 'is':
								if( ! ( strcmp( $field, $expected_value ) == 0 ) ){
									$condition_result = false;
								}
								break;
							case 'not_is':
								if( ! ( strcmp( $field, $expected_value ) != 0 ) ){
									$condition_result = false;
								}
								break;
							case 'contains':
								if( ! ( strpos( $field, $expected_value ) !== false ) ){
									$condition_result = false;
								}
								break;
							case 'not_contains':
								if( ! ( strpos( $field, $expected_value ) === false ) ){
									$condition_result = false;
								}
								break;
							case 'less_than':
								if( ! ( $field < $expected_value ) ){
									$condition_result = false;
								}
								break;
							case 'less_or_equal':
								if( ! ( $field <= $expected_value ) ){
									$condition_result = false;
								}
								break;
							case 'equal':
								if( ! ( $field == $expected_value ) ){
									$condition_result = false;
								}
								break;
							case 'greater_or_equal':
								if( ! ( $field >= $expected_value ) ){
									$condition_result = false;
								}
								break;
							case 'greater_than':
								if( ! ( $field > $expected_value ) ){
									$condition_result = false;
								}
								break;
						}

						break;
				}

				if( ! $condition_result ){
					break;
				}
			}

			return $condition_result;
		}

		/* === HANDLE SHORTCODE === */

		/**
		 * Register newsletter subscription form shortcode
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function register_shortcode() {
			add_shortcode( 'yith_wcmc_subscription_form', array( $this, 'print_shortcode' ) );
		}

		/**
		 * Print newsletter subscription form shortcode
		 *
		 * @param $atts array Array of attributes passed to shortcode
		 * @param $content string Shortcode content
		 *
		 * @return string Shortcode template
		 * @since 1.0.0
		 */
		public function print_shortcode( $atts, $content = "" ) {
			// generate unique shortcode id
			$unique_id = mt_rand();

			// generate basic default array
			$defaults = array(
				'title' => get_option( 'yith_wcmc_shortcode_title' ),
				'submit_label' => get_option( 'yith_wcmc_shortcode_submit_button_label' ),
				'success_message' => get_option( 'yith_wcmc_shortcode_success_message' ),
				'hide_form_after_registration' => get_option( 'yith_wcmc_shortcode_hide_after_registration' ),
				'email_type' => get_option( 'yith_wcmc_shortcode_email_type', 'html' ),
				'double_optin' => get_option( 'yith_wcmc_shortcode_double_optin' ),
				'update_existing' => get_option( 'yith_wcmc_shortcode_update_existing' ),
				'replace_interests' => get_option( 'yith_wcmc_shortcode_replace_interests' ),
				'send_welcome' => get_option( 'yith_wcmc_shortcode_send_welcome' ),
				'list' => get_option( 'yith_wcmc_shortcode_mailchimp_list' ),
				'groups' => implode( '#%,%#', get_option( 'yith_wcmc_shortcode_mailchimp_groups', array() ) ),
				'groups_to_prompt' => get_option( 'yith_wcmc_shortcode_mailchimp_groups_selectable', array() ),
				'widget' => 'no'
			);

			// add defaults for fields
			$selected_fields = get_option( 'yith_wcmc_shortcode_custom_fields' );
			$textual_fields = '';

			if( ! empty( $selected_fields ) ){
				$first = true;
				foreach( $selected_fields as $field ){
					if( ! $first ){
						$textual_fields .= '|';
					}

					$textual_fields .= $field['name'] . ',' . $field['merge_var'];

					$first = false;
				}
			}

			$fields_default = array( 'fields' => $textual_fields );
			$defaults = array_merge( $defaults, $fields_default );

			// add defaults for style
			$style_defaults = array(
				'enable_style' => get_option( 'yith_wcmc_shortcode_style_enable' ),
				'round_corners' => get_option( 'yith_wcmc_shortcode_subscribe_button_round_corners', 'no' ),
				'background_color' => get_option( 'yith_wcmc_shortcode_subscribe_button_background_color' ),
				'text_color' => get_option( 'yith_wcmc_shortcode_subscribe_button_color' ),
				'border_color' => get_option( 'yith_wcmc_shortcode_subscribe_button_border_color' ),
				'background_hover_color' => get_option( 'yith_wcmc_shortcode_subscribe_button_background_hover_color' ),
				'text_hover_color' => get_option( 'yith_wcmc_shortcode_subscribe_button_hover_color' ),
				'border_hover_color' => get_option( 'yith_wcmc_shortcode_subscribe_button_border_hover_color' ),
				'custom_css' => get_option( 'yith_wcmc_shortcode_custom_css' ),
			);

			$defaults = array_merge( $defaults, $style_defaults );

			// generate atts array
			$atts = shortcode_atts( $defaults, $atts );

			// generate structure for fields
			$fields_chunk = array();
			$fields_subchunk = array_filter( explode( '|', $atts['fields'] ) );
			if( ! empty( $fields_subchunk ) ){
				foreach( $fields_subchunk as $subchunk ){
					if( strpos( $subchunk, ',' ) === false ){
						continue;
					}

					list( $name, $merge_var ) = explode( ',', $subchunk );
					$fields_chunk[ $merge_var ] = array( 'name' => $name, 'merge_var' => $merge_var );
				}
			}
			$atts['fields'] = $fields_chunk;

			// extract variables for the template
			extract( $atts );

			// define context
			$context = ( isset( $widget ) && $widget == 'yes' ) ? 'widget' : 'shortcode';

			// replace "yes"/"no" values with true/false
			$double_optin      = ( 'yes' == $double_optin );
			$update_existing   = ( 'yes' == $update_existing );
			$replace_interests = ( 'yes' == $replace_interests );
			$send_welcome      = ( 'yes' == $send_welcome );
			$enable_style      = ( 'yes' == $enable_style );
			$round_corners     = ( 'yes' == $round_corners );

			if( empty( $list ) ){
				return '';
			}

			// retrieve fields informations from mailchimp
			$fields_data = array();

			if( ! empty( $fields ) ) {
				$fields_data_raw = $this->do_request( 'lists/merge-vars', array( 'id' => array( $list ) ) );

				if ( ! isset( $fields_data_raw['data'] ) || ! isset( $fields_data_raw['data'][0] ) || ! isset( $fields_data_raw['data'][0]['merge_vars'] ) || empty( $fields_data_raw['data'][0]['merge_vars'] ) ) {
					return '';
				}

				$fields_data_raw = $fields_data_raw['data'][0]['merge_vars'];

				foreach ( $fields_data_raw as $data ) {
					$fields_data[ $data['tag'] ] = $data;
				}
			}

			// retrieve groups informations from mailchimp
			$groups_data = array();
			$groups_to_prompt = ! is_array( $groups_to_prompt ) ? explode( '#%,%#', $groups_to_prompt ) : $groups_to_prompt;

			if( ! empty( $groups_to_prompt ) ){
				$available_groups = $this->do_request( 'lists/interest-groupings', array( 'id' => $list ) );
				$available_groups_ids = ! isset( $available_groups['status'] ) ? wp_list_pluck( $available_groups, 'id' ) : array();

				foreach( $groups_to_prompt as $interest_raw ){
					if( strpos( $interest_raw, '-' ) === false ){
						continue;
					}
					
					list( $group, $interest ) = explode( '-', $interest_raw );

					if( false !== $index = array_search( $group, $available_groups_ids ) ){
						if( ! isset( $groups_data[ $group ] ) ){
							$groups_data[ $group ] = array(
								'id' => $group,
								'name' => $available_groups[ $index ]['name'],
								'type' => $available_groups[ $index ]['form_field'],
								'interests' => array(
									$interest
								)
							);
						}
						else{
							$groups_data[ $group ]['interests'][] = $interest;
						}
					}
				}
			}

			// retrieve style information for template
			$style = '';

			if( $enable_style ){
				$style = sprintf(
					'#subscription_form_%d input[type="submit"]{
					    color: %s;
					    border: 1px solid %s;
					    border-radius: %dpx;
					    background: %s;
					}
					#subscription_form_%d input[type="submit"]:hover{
					    color: %s;
					    border: 1px solid %s;
					    background: %s;
					}
					%s',
					$unique_id,
					$text_color,
					$border_color,
					( $round_corners ) ? 5 : 0,
					$background_color,
					$unique_id,
					$text_hover_color,
					$border_hover_color,
					$background_hover_color,
					$custom_css
				);
			}

			$use_placeholders = apply_filters( 'yith_wcmc_use_placeholders_instead_of_labels', false );

			// retrieve template for the subscription form
			$template_name = 'mailchimp-subscription-form.php';

			$located = locate_template( array(
				trailingslashit( WC()->template_path() ) . 'wcmc/' . $template_name,
				trailingslashit( WC()->template_path() ) . $template_name,
				'wcmc/' . $template_name,
				$template_name
			) );

			if( ! $located ){
				$located = YITH_WCMC_DIR . 'templates/' . $template_name;
			}

			// returns form template
			ob_start();

			include( $located );

			return ob_get_clean();
		}

		/**
		 * Print single subscription form field
		 *
		 * @param $id int Unique id of the shortcode
		 * @param $panel_options array Array of options setted in settings panel
		 * @param $mailchimp_data array Array of data retreieved from mailchimp server
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function print_field( $id, $panel_options, $mailchimp_data, $context = 'shortcode' ){
			if( ! $mailchimp_data['public'] ){
				return;
			}

			$use_placeholders = apply_filters( 'yith_wcmc_use_placeholders_instead_of_labels', false );
			$placeholder = ! empty( $panel_options['name'] ) && $use_placeholders ? $panel_options['name'] : '';

			// retrieve template for the subscription form
			$template_name = strtolower( $mailchimp_data['field_type'] ) . '.php';

			$located = locate_template( array(
				trailingslashit( WC()->template_path() ) . 'wcmc/types/' . $template_name,
				trailingslashit( WC()->template_path() ) . 'types/' . $template_name,
				'wcmc/types/' . $template_name,
				'types/' . $template_name
			) );

			if( ! $located ){
				$located = YITH_WCMC_DIR . 'templates/types/' . $template_name;
			}

			include( $located );
		}

		/**
		 * Print single subscription interests group form
		 *
		 * @param $id int Unique form id
		 * @param $mailchimp_data mixed Array of options retrieved from MailChimp servers
		 *
		 * @return void
		 * @since 1.0.7
		 */
		public function print_groups( $id, $mailchimp_data ) {
			// set correct index in MailChimp data
			$mailchimp_data['tag'] = $mailchimp_data['id'];
			$mailchimp_data['req'] = false;
			$mailchimp_data['choices'] = array_combine( $mailchimp_data['interests'], $mailchimp_data['interests'] );

			$use_placeholders = apply_filters( 'yith_wcmc_use_placeholders_instead_of_labels', false );
			$placeholder = ! empty( $mailchimp_data['name'] ) && $use_placeholders ? $mailchimp_data['name'] : '';

			// retrieve template for the subscription form
			$template_name = strtolower( $mailchimp_data['type'] ) . '.php';

			$located = locate_template( array(
				trailingslashit( WC()->template_path() ) . 'wcmc/types/' . $template_name,
				trailingslashit( WC()->template_path() ) . 'types/' . $template_name,
				'wcmc/types/' . $template_name,
				'types/' . $template_name
			) );

			if( ! $located ){
				$located = YITH_WCMC_DIR . 'templates/types/' . $template_name;
			}

			include( $located );
		}

		/* === HANDLE WIDGET === */

		/**
		 * Registers widget used to show subscription form
		 *
		 * @return void
		 * @since1.0.0
		 */
		public function register_widget() {
			register_widget( 'YITH_WCMC_Widget' );
		}

		/* === HANDLE FORM SUBSCRIPTION === */

		/**
		 * Register a user using form fields
		 *
		 * @return array Array with status code and messages
		 * @since 1.0.0
		 */
		public function form_subscribe() {
			// retrieve minimum required fields for subscription
			$list = isset( $_POST['list'] ) ? $_POST['list'] : false;
			$email = isset( $_POST['EMAIL'] ) ? $_POST['EMAIL'] : false;
			$nonce = isset( $_POST['yith_wcmc_subscribe_nonce'] ) ? $_POST['yith_wcmc_subscribe_nonce'] : false;

			// check existance of minimum required fields
			if( empty( $list ) || empty( $email ) || empty( $nonce ) ){
				return array(
					'status'  => false,
					'code'    => false,
					'message' => apply_filters( 'yith_wcmc_missing_required_arguments_error_message', __( 'Required arguments missing', 'yith-woocommerce-mailchimp' ) )
				);
			}

			// check nonce
			if( ! wp_verify_nonce( $nonce, 'yith_wcmc_subscribe' ) ){
				return array(
					'status'  => false,
					'code'    => false,
					'message' => apply_filters( 'yith_wcmc_operation_denied_error_message', __( 'Ops! It seems you are not allowed to do this', 'yith-woocommerce-mailchimp' ) )
				);
			}

			// retrieve additional params
			$groups = isset( $_POST['groups'] ) ? $_POST['groups'] : '';
			$email_type = isset( $_POST['email_type'] ) ? $_POST['email_type'] : 'html';
			$double_optin = ! empty( $_POST['double_optin'] ) ? true : false;
			$update_existing = ! empty( $_POST['update_existing'] ) ? true : false;
			$replace_interests = ! empty( $_POST['replace_interests'] ) ? true : false;
			$send_welcome = ! empty( $_POST['send_welcome'] ) ? true : false;
			$success_message = ! empty( $_POST['success_message'] ) ? wp_kses_post( $_POST['success_message'] ) : __( 'Great! You\'re now subscribed to our newsletter', 'yith-woocommerce-mailchimp' );

			// retrieve merge vars
			$fields_row_data = $this->do_request( 'lists/merge-vars', array( 'id' => array( $list ) ) );
			$fields_data = isset( $fields_row_data['data'][0]['merge_vars'] ) ? $fields_row_data['data'][0]['merge_vars'] : array();

			$data_to_submit = array();
			if( ! empty( $fields_data ) ){
				foreach( $fields_data as $field ){
					$name = $field['tag'];
					if( isset( $_POST[ $name ] ) && '' != $_POST[ $name ] ){
						$value = $_POST[ $name ];

						// reformat submitted values
						if( $field['field_type'] == 'birthday' ){
							$value = str_replace( '-', '/', substr( $value, -5 ) );
						}

						$data_to_submit[ $name ] = $value;
					}
				}
			}

			// retrieve groups
			$groups_to_submit = array();
			if( ! empty( $groups ) ){
				$groups_chunks = explode( '#%,%#', $groups );

				if( ! empty( $groups_chunks ) ){
					foreach( $groups_chunks as $chunk ){
						if( strpos( $chunk, '-' ) === false ){
							continue;
						}

						list( $id, $name ) = explode( '-', $chunk );

						if( array_key_exists( $id, $groups_to_submit ) ){
							if( ! in_array( $name, $groups_to_submit[ $id ]['groups'] ) ){
								$groups_to_submit[ $id ]['groups'][] = $name;
							}
						}
						else{
							$groups_to_submit[ $id ] = array(
								'id' => $id,
								'groups' => array( $name )
							);
						}
					}
				}
			}

			// retrieve chosen interests
			$available_groups = $this->do_request( 'lists/interest-groupings', array( 'id' => $list ) );
			$available_groups_ids = ! isset( $available_groups['status'] ) ? wp_list_pluck( $available_groups, 'id' ) : array();

			if( ! empty( $available_groups_ids ) ){
				foreach( $available_groups_ids as $group_id ){
					if( isset( $_POST[ $group_id ] ) && $interests = $_POST[ $group_id ] ){
						if( array_key_exists( $group_id, $groups_to_submit ) ){
							$groups_to_submit[ $group_id ]['groups'] = array_merge( $groups_to_submit[ $group_id ]['groups'], (array) $interests );
						}
						else{
							$groups_to_submit[ $group_id ] = array(
								'id' => (string) $group_id,
								'groups' => (array) $interests
							);
						}
					}
				}
			}

			$groups_to_submit = array_values( $groups_to_submit );

			// generate argument structure to send within the request
			$args = array(
				'merge_vars' => array_merge(
					$data_to_submit,
					array( 'groupings' => $groups_to_submit )
				),
				'email_type' => $email_type,
				'double_optin' => $double_optin,
				'update_existing' => $update_existing,
				'replace_interests' => $replace_interests,
				'send_welcome' => $send_welcome
			);

			$res = $this->subscribe( $list, $email, $args );

			if( ! isset( $res['status'] ) || $res['status'] ){
				$res = array(
					'status' => true,
					'message' => apply_filters( 'yith_wcmc_correctly_subscribed_message', stripslashes( $success_message ), $list, $email )
				);
			}

			return $res;
		}

		/**
		 * Calls form_subscribe(), when posting form data, and adds woocommerce notice with result of the operation
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function post_form_subscribe() {
			if( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' && isset( $_POST['yith_wcmc_subscribe_nonce'] ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ){
				$res = $this->form_subscribe();

				wc_add_notice( $res['message'], ( $res['status'] ) ? 'yith-wcmc-success' : 'yith-wcmc-error' );
			}
		}

		/* === HANDLES AJAX REQUESTS === */

		/**
		 * Calls form_subscribe(), from an AJAX request, and print JSON encoded version of its result
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function ajax_form_subscribe() {
			wp_send_json( $this->form_subscribe() );
		}
	}
}

/**
 * Unique access to instance of YITH_WCMC_Premium class
 *
 * @return \YITH_WCMC_Premium
 * @since 1.0.0
 */
function YITH_WCMC_Premium(){
	return YITH_WCMC_Premium::get_instance();
}