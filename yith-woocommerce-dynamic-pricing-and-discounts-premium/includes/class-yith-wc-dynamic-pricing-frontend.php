<?php

if ( ! defined( 'ABSPATH' ) || ! defined( 'YITH_YWDPD_VERSION' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Implements frontend features of YITH WooCommerce Dynamic Pricing and Discounts
 *
 * @class   YITH_WC_Dynamic_Pricing_Frontend
 * @package YITH WooCommerce Dynamic Pricing and Discounts
 * @since   1.0.0
 * @author  Yithemes
 */
if ( ! class_exists( 'YITH_WC_Dynamic_Pricing_Frontend' ) ) {

	/**
	 * Class YITH_WC_Dynamic_Pricing_Frontend
	 */
	class YITH_WC_Dynamic_Pricing_Frontend {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WC_Dynamic_Pricing_Frontend
		 */
		protected static $instance;

		public $get_product_filter;

		/**
		 * The pricing rules
		 *
		 * @access public
		 * @var string
		 * @since  1.0.0
		 */
		public $pricing_rules = array();

		/**
		 * @var array
		 */
		public $table_rules = array();

		/**
		 * @var array
		 */
		public $has_get_price_filter = array();
		public $has_get_price_html_filter = array();
		public $cart_processed = false;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WC_Dynamic_Pricing_Frontend
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0.0
		 * @author Emanuela Castorina
		 */
		public function __construct() {

			$enabled =  YITH_WC_Dynamic_Pricing()->get_option( 'enabled' );

			if ( ! ywdpd_is_true( $enabled ) ) {
				return;
			}

			$this->get_product_filter = version_compare( WC()->version, '2.7.0', '>=' ) ? 'product_' : '';

			if ( ( ! empty( $_REQUEST['add-to-cart'] ) && is_numeric( $_REQUEST['add-to-cart'] ) ) ||
			     ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'woocommerce_add_to_cart' ) ||
			     ( isset( $_REQUEST['wc-ajax'] ) && 'add_to_cart' == $_REQUEST['wc-ajax'] )
			) {
				add_action( 'woocommerce_add_to_cart', array( $this, 'cart_process_discounts' ), 99 );
			} else {
				if ( empty( $_POST['apply_coupon'] ) || empty( $_POST['coupon_code'] ) ) {
					add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'cart_process_discounts' ), 99 );
				} else {
					add_action( 'woocommerce_applied_coupon', array( $this, 'cart_process_discounts' ), 9 );
				}
			}

			//Filters to format prices
			add_filter( 'woocommerce_get_price_html', array( &$this, 'get_price_html' ), 10, 2 );
			add_filter( 'woocommerce_get_variation_price_html', array( &$this, 'get_price_html' ), 10, 2 );
			add_filter( 'woocommerce_' . $this->get_product_filter . 'get_price', array( $this, 'get_price' ), 10, 2 );
			add_filter( 'woocommerce_' . $this->get_product_filter . 'variation_get_price', array( $this, 'get_price' ), 10, 2 );
			add_filter( 'woocommerce_cart_item_price', array( $this, 'replace_cart_item_price' ), 100, 3 );


			//Quantity table
			$show_quantity_table = YITH_WC_Dynamic_Pricing()->get_option( 'show_quantity_table' );
			if ( ywdpd_is_true( $show_quantity_table ) ) {
				$this->table_quantity_init();
				add_filter( 'woocommerce_available_variation', array( $this, 'add_params_to_available_variation' ), 10, 3 );
			}

			add_shortcode( 'yith_ywdpd_quantity_table', array( $this, 'table_quantity_shortcode' ) );


			//Notes on products
			$show_note_on_products = YITH_WC_Dynamic_Pricing()->get_option( 'show_note_on_products' );
			if ( ywdpd_is_true( $show_note_on_products ) ) {
				$this->note_on_products_init();
			}

			add_action( 'init', array( $this, 'init' ), 10 );

			$priority = ( function_exists( 'YITH_WCCL_Frontend' ) ) ? 5 : 10;

			//custom styles and javascripts
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ), $priority );



		}

		function init() {
			$this->pricing_rules = YITH_WC_Dynamic_Pricing()->get_pricing_rules();
		}


		/**
		 * Remove from cart only dynamic coupons
		 *
		 * @since  1.2.0
		 * @author Emanuela Castorina
		 */
		function remove_dynamic_coupons() {
			$applied_coupons = WC()->cart->get_applied_coupons();
			foreach ( $applied_coupons as $applied_coupon ) {
				$cp   = new WC_Coupon( $applied_coupon );
				$meta = $cp->get_meta( 'ywdpd_coupon', true );
				if ( ! empty( $meta ) ) {
					WC()->cart->remove_coupon( $cp->get_code() );
				}
			}
		}

		/**
		 * Process dynamic pricing in cart
		 *
		 * @since  1.0.0
		 * @author Emanuela Castorina
		 */
		public function cart_process_discounts() {

			if ( empty( WC()->cart->cart_contents ) || $this->cart_processed ) {
				return;
			}
			do_action( 'ywdpd_before_cart_process_discounts' );

			if ( version_compare( WC()->version, '2.7', '<' ) ) {
				WC()->cart->remove_coupon( YITH_WC_Dynamic_Discounts()->label_coupon );
			} else {
				$this->remove_dynamic_coupons();
			}

			WC()->session->set( 'refresh_totals', true );

			$cart_sort      = array();
			$bundled_cart   = array();
			$composite_cart = array();
			$mix_match_cart = array();

			//empty old discounts and reset the available quantity
			foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
				// if the product is a bundle or a bundle item
				if ( isset( $cart_item['bundled_by'] ) || isset( $cart_item['cartstamp'] ) ) {
					$bundled_cart[ $cart_item_key ] = WC()->cart->cart_contents[ $cart_item_key ];
				} elseif ( isset( $cart_item['mnm_config'] ) || isset( $cart_item['mnm_container'] ) ) {
					$mix_match_cart[ $cart_item_key ] = WC()->cart->cart_contents[ $cart_item_key ];
				} elseif ( isset( $cart_item['yith_wcp_component_data'] ) || isset( $cart_item['yith_wcp_child_component_data'] ) ) {
					$composite_cart[ $cart_item_key ] = WC()->cart->cart_contents[ $cart_item_key ];
				} else {
					WC()->cart->cart_contents[ $cart_item_key ]['available_quantity'] = $cart_item['quantity'];
					if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['ywdpd_discounts'] ) ) {
						unset( WC()->cart->cart_contents[ $cart_item_key ]['ywdpd_discounts'] );
					}
					$cart_sort[ $cart_item_key ] = WC()->cart->cart_contents[ $cart_item_key ];
				}
			}


			@uasort( $cart_sort, 'YITH_WC_Dynamic_Pricing_Helper::sort_by_price' );

			WC()->cart->cart_contents = $cart_sort;
			remove_filter( 'woocommerce_' . $this->get_product_filter . 'get_price', array( $this, 'get_price' ), 10 );
			//add processed pricing rules on each cart item
			foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
				if ( ! YITH_WC_Dynamic_Pricing_Helper()->check_cart_item_filter_exclusion( $cart_item ) ) {
					YITH_WC_Dynamic_Pricing()->get_applied_rules_to_product( $cart_item_key, $cart_item );
				}
			}

			//apply the discount to each cart item
			foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
				if ( isset( $cart_item['ywdpd_discounts'] ) ) {
					WC()->cart->calculate_totals();
					YITH_WC_Dynamic_Pricing()->apply_discount( $cart_item, $cart_item_key );
				}
			}

			WC()->cart->cart_contents = array_merge( WC()->cart->cart_contents, $bundled_cart, $composite_cart, $mix_match_cart );
			WC()->cart->calculate_totals();
			if ( ! isset( $_REQUEST['remove_coupon'] ) ) {
				YITH_WC_Dynamic_Discounts()->apply_discount();

			}

			if ( isset( $_REQUEST['apply_coupon'] ) ) {
				unset( $_REQUEST['apply_coupon'] );
			}

			do_action( 'ywdpd_after_cart_process_discounts' );

			$this->cart_processed = true;
			add_filter( 'woocommerce_' . $this->get_product_filter . 'get_price', array( $this, 'get_price' ), 10, 2 );
		}

		/**
		 * Replace the price in the cart
		 *
		 * @since  1.0.0
		 * @author Emanuela Castorina
		 *
		 * @param $price
		 * @param $cart_item
		 * @param $cart_item_key
		 *
		 * @return mixed|string
		 */
		public function replace_cart_item_price( $price, $cart_item, $cart_item_key ) {

			/*
			//old version mini cart
			if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
				define( 'WOOCOMMERCE_CART', true );
			}
			*/

			do_action('ywdpd_before_replace_cart_item_price', $price, $cart_item, $cart_item_key );


			if ( ! isset( $cart_item['ywdpd_discounts'] ) || YITH_WC_Dynamic_Pricing_Helper()->check_cart_item_filter_exclusion( $cart_item ) ) {
				return $price;
			}

			$old_price = $price;
			remove_filter( 'woocommerce_' . $this->get_product_filter . 'get_price', array( $this, 'get_price' ), 10);
			foreach ( $cart_item['ywdpd_discounts'] as $discount ) {
				if ( isset( $discount['status'] ) && $discount['status'] == 'applied' ) {

					if ( floatval($cart_item['ywdpd_discounts']['default_price']) > floatval( $cart_item['data']->get_price() ) && wc_price( $cart_item['ywdpd_discounts']['default_price'] ) != WC()->cart->get_product_price( $cart_item['data'] ) ) {
						$price = '<del>' . wc_price( $cart_item['ywdpd_discounts']['default_price'] ) . '</del> ' . WC()->cart->get_product_price( $cart_item['data'] );
						break;
					} else {
						return $price;
					}

				}
			}

			$price = apply_filters( 'ywdpd_replace_cart_item_price', $price, $old_price, $cart_item, $cart_item_key );

			WC()->cart->calculate_totals();

			return $price;
		}

		/**
		 * Add custom params to variations
		 *
		 * @access public
		 *
		 * @param $args      array
		 * @param $product   object
		 * @param $variation object
		 *
		 * @return array
		 * @since  1.1.1
		 */
		public function add_params_to_available_variation( $args, $product, $variation ) {

			$args['table_price'] = $this->table_quantity( $variation );

			return $args;
		}

		/**
		 * Show table quantity in the single product if there's a pricing rule
		 *
		 * @since  1.0.0
		 * @author Emanuela Castorina
		 */
		public function show_note_on_products() {
			global $product;

			$valid_rules = $this->pricing_rules;

			if ( empty( $valid_rules ) || YITH_WC_Dynamic_Pricing_Helper()->is_in_exclusion_rule( array( 'product_id' => $product->get_id() ) ) ) {
				return;
			}

			foreach ( $valid_rules as $rule ) {

				$show_onsale = isset( $rule['apply_on_sale'] ) && ywdpd_is_true( $rule['apply_on_sale'] );

				if ( ! $show_onsale && ( $product->get_sale_price() !== $product->get_regular_price() && $product->get_sale_price() === $product->get_price() ) ) {
					continue;
				}


				if ( isset( $rule['table_note_apply_to'] ) && $rule['table_note_apply_to'] != '' && in_array( $rule['discount_mode'], array(
						'bulk',
						'special_offer'
					) ) && YITH_WC_Dynamic_Pricing_Helper()->valid_product_to_apply( $rule, $product, true )
				) {
					echo '<div class="show_note_on_apply_products">' . stripslashes( $rule['table_note_apply_to'] ) . '</div>';
				}


				if ( isset( $rule['table_note_adjustment_to'] ) && $rule['table_note_adjustment_to'] != '' && in_array( $rule['discount_mode'], array(
						'bulk',
						'special_offer'
					) ) && YITH_WC_Dynamic_Pricing_Helper()->valid_product_to_adjust( $rule, array( 'product_id' => $product->get_id() ) )
				) {
					echo '<div class="show_note_on_apply_products">' . stripslashes( $rule['table_note_adjustment_to'] ) . '</div>';
				}
			}
		}

		/**
		 * @param $price
		 * @param $product WC_Product|WC_Product_Variable
		 *
		 * @return mixed|string
		 */
		function get_price_html( $price, $product ) {

			global $woocommerce_loop;

			if ( ( ( is_cart() || is_checkout() ) && is_null( $woocommerce_loop ) ) || ! YITH_WC_Dynamic_Pricing()->check_discount( $product ) ) {
				return $price;
			}

			$product_id = yit_get_prop( $product, 'id' );
			if ( array_key_exists( $product_id, $this->has_get_price_html_filter ) || apply_filters( 'ywdpd_get_price_html_exclusion', false, $price, $product )  ) {
				return isset( $this->has_get_price_html_filter[ $product_id ] ) ? $this->has_get_price_html_filter[ $product_id ] : $price;
			}

			$display_regular_price = function_exists( 'wc_get_price_to_display' ) ? wc_get_price_to_display( $product, array( 'qty' => 1, 'price' => $product->get_price( 'edit' ) ) ) : $product->get_display_price( $product->get_regular_price() );

			$price_format          = YITH_WC_Dynamic_Pricing()->get_option( 'price_format', '<del>%original_price%</del> %discounted_price%' );
			$new_price             = $price_format;
			$percentual_discount   = '';
			$discount_html         = '';

			if ( $product->is_type( 'variable' ) ) {

				$prices = array(
					$product->get_variation_price( 'min', true ),
					$product->get_variation_price( 'max', true )
				);

				$min_variation_regular_price = $this->get_min_regular_variation_price( $product );
				$max_variation_regular_price = $this->get_max_regular_variation_price( $product );

				remove_filter( 'woocommerce_' . $this->get_product_filter . 'get_price', array( $this, 'get_price' ), 10 );
				$show_minimum_price = YITH_WC_Dynamic_Pricing()->get_option( 'show_minimum_price' );
				if ( ywdpd_is_true( $show_minimum_price ) ) {
					$discount     = $this->get_minimum_price( $product );
					$discount_max = $this->get_maximum_price( $product );
				} else {
					$discount_max = $this->get_maximum_price( $product, 1 );
					$discount     = $this->get_minimum_price( $product, 1 );
				}
				add_filter( 'woocommerce_' . $this->get_product_filter . 'get_price', array( $this, 'get_price' ), 10, 2 );

				if ( $prices[0] == $prices[1] && $min_variation_regular_price == $prices[0] ) {

					$display_regular_price = function_exists( 'wc_get_price_to_display' ) ? wc_get_price_to_display( $product, array( 'qty' => 1, 'price' => $this->get_min_regular_variation_price( $product ) ) ) : $product->get_display_price( $this->get_min_regular_variation_price( $product ) );

					remove_filter( 'woocommerce_' . $this->get_product_filter . 'get_price', array( $this, 'get_price' ), 10 );
					$show_minimum_price = YITH_WC_Dynamic_Pricing()->get_option( 'show_minimum_price' );
					if ( ywdpd_is_true( $show_minimum_price ) ) {
						$discount = function_exists( 'wc_get_price_to_display' ) ? wc_get_price_to_display( $product, array( 'qty' => 1, 'price' => $this->get_minimum_price( $product ) ) ) : $product->get_display_price( $this->get_minimum_price( $product ) );
					} else {
						$discount = function_exists( 'wc_get_price_to_display' ) ? wc_get_price_to_display( $product, array( 'qty' => 1, 'price' => $this->get_minimum_price( $product, 1 ) ) ) : $product->get_display_price( $this->get_minimum_price( $product, 1 ) );
					}

					add_filter( 'woocommerce_' . $this->get_product_filter . 'get_price', array( $this, 'get_price' ), 10, 2 );

					$discount_html = wc_price( $discount );

					if ( $display_regular_price ) {
						$per_disc = 100 - ( $discount / $display_regular_price * 100 );
						if ( $per_disc > 0 ) {
							$percentual_discount = apply_filters( 'ywdpd_percentual_discount', '-' . number_format( $per_disc, 2, '.', '' ) . '%', $per_disc );
						}
					}

				} else {

					if ( $discount != $min_variation_regular_price || $discount != $min_variation_regular_price || $discount_max != $max_variation_regular_price ) {
						$dp_min_variation_regular_price = function_exists( 'wc_get_price_to_display' ) ? wc_get_price_to_display( $product, array( 'price' => $min_variation_regular_price ) ) : $product->get_display_price( $min_variation_regular_price );

						$dp_max_variation_regular_price = function_exists( 'wc_get_price_to_display' ) ? wc_get_price_to_display( $product, array( 'price' => $max_variation_regular_price ) ) : $product->get_display_price( $max_variation_regular_price );

						if ( $min_variation_regular_price < $max_variation_regular_price ) {
							$display_regular_price = apply_filters( 'ywdpd_change_variable_products_html_regular_price', wc_price( $dp_min_variation_regular_price ) . '-' . wc_price( $dp_max_variation_regular_price ), $dp_min_variation_regular_price, $dp_max_variation_regular_price );

						} else {
							$display_regular_price = wc_price( $dp_min_variation_regular_price );
						}

						$new_price = str_replace( '%original_price%', $display_regular_price, $new_price );

						$dp_discount     = function_exists( 'wc_get_price_to_display' ) ? wc_get_price_to_display( $product, array( 'price' => $discount ) ) : $product->get_display_price( $discount );
						$dp_discount_max = function_exists( 'wc_get_price_to_display' ) ? wc_get_price_to_display( $product, array( 'price' => $discount_max ) ) : $product->get_display_price( $discount_max );

						if ( $discount_max != $discount ) {
							$discount_html = apply_filters( 'ywdpd_change_variable_products_html_discount_price', wc_price( $dp_discount ) . '-' . wc_price( $dp_discount_max ), $dp_discount, $dp_discount_max );
						} else {
							$discount_html = wc_price( $dp_discount );
						}

						if ( $min_variation_regular_price !== 0 && $min_variation_regular_price != 0.00 ) {
							$per_disc = 100 - ( $discount / $min_variation_regular_price * 100 );
							if ( $per_disc > 0 ) {
								$percentual_discount = apply_filters( 'ywdpd_percentual_discount', '-' . number_format( $per_disc, 2, '.', '' ) . '%', $per_disc );
							}
						}
					} else {
						$discount  = false;
						$new_price = $price;
					}
				}

			} else {

				remove_filter( 'woocommerce_' . $this->get_product_filter . 'get_price', array( $this, 'get_price' ), 10 );
				remove_filter( 'woocommerce_' . $this->get_product_filter . 'variation_get_price', array( $this, 'get_price' ), 10 );
				$show_minimum_price = YITH_WC_Dynamic_Pricing()->get_option( 'show_minimum_price' );
				if ( ywdpd_is_true( $show_minimum_price ) ) {
					$discount = $this->get_minimum_price( $product );
				} else {
					$discount = $this->get_minimum_price( $product, 1 );
				}


				add_filter( 'woocommerce_' . $this->get_product_filter . 'get_price', array( $this, 'get_price' ), 10, 2 );
				add_filter( 'woocommerce_' . $this->get_product_filter . 'variation_get_price', array( $this, 'get_price' ), 10, 2 );

				$discount      = function_exists( 'wc_get_price_to_display' ) ? wc_get_price_to_display( $product, array( 'price' => $discount ) ) : $product->get_display_price( $discount );
				$discount_html = wc_price( $discount );

			}


			if ( $discount >= 0 && $discount != $display_regular_price ) {

				if ( empty( $percentual_discount ) && $display_regular_price != 0 ) {
					$per_disc = 100 - ( $discount / $display_regular_price * 100 );

					if ( $per_disc > 0 ) {
						$percentual_discount = apply_filters( 'ywdpd_percentual_discount', '-' . number_format( $per_disc, 2, '.', '' ) . '%', $per_disc );
					}
				}

				$new_price = str_replace( '%original_price%', wc_price( $display_regular_price ), $new_price );
				$new_price = str_replace( '%discounted_price%', $discount_html, $new_price );
				$new_price = str_replace( '%percentual_discount%', $percentual_discount, $new_price );
				$new_price .= $product->get_price_suffix();

			} else {
				$show_minimum_price = YITH_WC_Dynamic_Pricing()->get_option( 'show_minimum_price' );
				if ( ywdpd_is_true( $show_minimum_price ) ) {
					$new_price = wc_price( $discount );
				} else {
					$new_price = $price;
				}
			}

			add_filter( 'woocommerce_' . $this->get_product_filter . 'get_price', array( $this, 'get_price' ), 10, 2 );
			$this->has_get_price_html_filter[ $product_id ] = $new_price;

			return apply_filters( 'yith_ywdpd_single_bulk_discount', $new_price, $product );

		}

		/**
		 * Only the first quantity table can be applied to the product
		 *
		 * @param $product WC_Product
		 *
		 * @return mixed
		 */
		public function get_table_rules( $product ) {

			if ( isset( $this->table_rules[ $product->get_id() ] ) ) {
				return $this->table_rules[ $product->get_id() ];
			}

			$valid_rules = $this->pricing_rules;

			$table_rules = array();
			if ( empty( $valid_rules ) || YITH_WC_Dynamic_Pricing_Helper()->is_in_exclusion_rule( array( 'product_id' => $product->get_id() ) ) ) {
				add_filter( 'woocommerce_' . $this->get_product_filter . 'get_price', array( $this, 'get_price' ), 10, 2 );
				$this->table_rules[ $product->get_id() ] = $table_rules;

				return false;
			}


			// build rules array
			foreach ( $valid_rules as $rule ) {

				if ( ! ywdpd_is_true( $rule['active'] ) ||
				     $rule['discount_mode'] != 'bulk' ||
				     ! YITH_WC_Dynamic_Pricing_Helper()->valid_product_to_apply_bulk( $rule, $product, false )
				) {
					continue;
				}

				//	add_filter( 'woocommerce_get_price', array( $this, 'get_price' ), 10, 2 );
				$table_rules[]                           = $rule;
				$this->table_rules[ $product->get_id() ] = $table_rules;

				break;
			}


			return $table_rules;
		}

		/**
		 * @param        $product WC_Product|WC_Product_Variable
		 * @param string $min_quantity
		 *
		 * @return int|mixed
		 */
		public function get_minimum_price( $product, $min_quantity = '' ) {

			$table_rules   = $this->get_table_rules( $product );
			$minimum_price = $product->get_price();

			$discount_price     = $minimum_price;
			$min_quantity_check = 0;
			$last_check         = true;
			if ( $table_rules ) {
				foreach ( $table_rules as $rules ) {
					$main_rule = $rules;
					foreach ( $rules['rules'] as $rule ) {

						if ( $product->is_type( 'variable' ) ) {
							$prices = $product->get_variation_prices();
							$prices = isset( $prices['price'] ) ? $prices['price'] : array();

							if ( $prices ) {
								$min_price = current( $prices );
								$max_price = end( $prices );
								if ( $min_price == $max_price ) {
									//for products where only the variation is discounted
									foreach ( $prices as $id => $p ) {
										if ( YITH_WC_Dynamic_Pricing_Helper()->valid_product_to_apply_bulk( $main_rule, wc_get_product( $id ) ) ) {
											$curr_discount_price = ywdpd_get_discounted_price_table( $p, $rule );
										} else {
											$curr_discount_price = $p;
										}
										$discount_price = $curr_discount_price < $discount_price ? $curr_discount_price : $discount_price;
									}
								} else {
									$min_key       = array_search( $min_price, $prices );
									$minimum_price = $min_price;
									if ( $min_quantity != '' && $rule['min_quantity'] != $min_quantity ) {
										continue;
									}

									if ( YITH_WC_Dynamic_Pricing_Helper()->valid_product_to_apply_bulk( $rules, wc_get_product( $min_key ) ) ) {
										$discount_min_price = ywdpd_get_discounted_price_table( $min_price, $rule );
									} else {
										$discount_min_price = $min_price;
									}

									$discount_price = $discount_min_price;
								}

							}

						} else {

							$price = $product->get_price();

							if ( YITH_WC_Dynamic_Pricing_Helper()->valid_product_to_apply_bulk( $rules, $product ) ) {
								$discount_price = ywdpd_get_discounted_price_table( $price, $rule );
							} else {
								$discount_price = $price;
							}

							if( isset( $rule['discount_amount'] ) && $rule['discount_amount'] <= 0 ){
								$minimum_price = $discount_price < $minimum_price ? $discount_price : $minimum_price ;
								$last_check =  false;
							}

						}

						if ( $min_quantity != '' && $rule['min_quantity'] == $min_quantity ) {
							$min_quantity_check = 1;
							break;
						}
					}
				}
			}

			if ( ! $last_check || ( $min_quantity != '' && ! $min_quantity_check ) ) {
				return $minimum_price;
			}

			$minimum_price = $minimum_price > $discount_price ? $discount_price : $minimum_price;

			return $minimum_price;
		}

		/**
		 * @param        $product WC_Product|WC_Product_Variable
		 * @param string $min_quantity
		 *
		 * @return int|mixed
		 */
		public function get_maximum_price( $product, $min_quantity = '' ) {

			$table_rules    = $this->get_table_rules( $product );
			$maximum_price  = $product->get_price();
			$discount_price = 0;
			if ( $table_rules ) {
				foreach ( $table_rules as $rules ) {
					foreach ( $rules['rules'] as $rule ) {
						$main_rule = $rules;
						if ( $product->is_type( 'variable' ) ) {
							$prices = $product->get_variation_prices();
							$prices = isset( $prices['price'] ) ? $prices['price'] : array();

							if ( $prices ) {
								$min_price = current( $prices );
								$max_price = end( $prices );
								if ( $min_price == $max_price ) {
									//for products where only the variation is discounted
									foreach ( $prices as $id => $p ) {
										if ( YITH_WC_Dynamic_Pricing_Helper()->valid_product_to_apply_bulk( $main_rule, wc_get_product( $id ) ) ) {
											$curr_discount_price = ywdpd_get_discounted_price_table( $p, $rule );
										} else {
											$curr_discount_price = $p;
										}
										$discount_price = $curr_discount_price > $discount_price ? $curr_discount_price : $discount_price;
									}
								} else {
									$max_key       = array_search( $max_price, $prices );
									$maximum_price = $max_price;

									if ( $min_quantity != '' && $rule['min_quantity'] != $min_quantity ) {
										continue;
									}


									if ( YITH_WC_Dynamic_Pricing_Helper()->valid_product_to_apply_bulk( $rules, wc_get_product( $max_key ) ) ) {
										$discount_max_price = ywdpd_get_discounted_price_table( $max_price, $rule );
									} else {
										$discount_max_price = $max_price;
									}

									$discount_price = $discount_max_price > $discount_price ? $discount_max_price : $discount_price;
								}
							}


						} else {
							$discount_price = ywdpd_get_discounted_price_table( $maximum_price, $rule );
						}

						if ( $min_quantity != '' && $rule['min_quantity'] == $min_quantity ) {
							break;
						}
					}
				}
			}

			//			error_log( '$discount_price:' );
			//			error_log( $discount_price);

			if ( $discount_price ) {
				//$maximum_price = $maximum_price < $discount_price ? $discount_price : $maximum_price;
				$maximum_price = $discount_price;
			}

			return $maximum_price;
		}

		/**
		 * @param $product WC_Product_Variable
		 *
		 * @since  1.1.3
		 * @return string
		 */
		function get_min_regular_variation_price( $product ) {

			$price = null;

			if ( $product->is_type( 'variable' ) ) {

				$prices_array = $product->get_variation_prices();

				if ( isset( $prices_array['price'] ) ) {

					foreach ( $prices_array['price'] as $single_price ) {

						if ( ! isset( $price ) ) {

							$price = $single_price;

						} else if ( $price > 0 && $single_price < $price ) {

							$price = $single_price;

						}
					}
				}
			}

			return isset( $price ) ? $price : '';

		}

		/**
		 * @param $product WC_Product_Variable
		 *
		 * @since  1.1.3
		 * @return string
		 */
		function get_max_regular_variation_price( $product ) {

			$price = null;

			if ( $product->is_type( 'variable' ) ) {

				$prices_array = $product->get_variation_prices();

				if ( isset( $prices_array['price'] ) ) {

					foreach ( $prices_array['price'] as $single_price ) {

						if ( ! isset( $price ) ) {

							$price = $single_price;

						} else if ( $price > 0 && $single_price > $price ) {

							$price = $single_price;

						}

					}

				}

			}

			return isset( $price ) ? $price : '';

		}

		/**
		 * @param $price
		 * @param $product
		 *
		 * @return mixed
		 */
		function get_price( $price, $product ) {

			global $woocommerce_loop;

			if ( ( ( is_cart() || is_checkout() ) && is_null( $woocommerce_loop ) ) || ! YITH_WC_Dynamic_Pricing()->check_discount( $product ) || ! apply_filters( 'ywdpd_apply_discount', true, $price, $product ) || empty( $price ) ) {
				return $price;
			}

			$product_id = $product->get_id();

			if ( array_key_exists( $product_id, $this->has_get_price_filter ) || apply_filters( 'ywdpd_get_price_exclusion', false, $price, $product ) || YITH_WC_Dynamic_Pricing_Helper()->is_in_exclusion_rule( array( 'product_id' => $product_id ) ) ) {
				return isset( $this->has_get_price_filter[ $product_id ] ) ? $this->has_get_price_filter[ $product_id ] : $price;
			}

			$discount = (string) YITH_WC_Dynamic_Pricing()->get_discount_price( $price, $product );

			$this->has_get_price_filter[ $product_id ] = $discount;

			return apply_filters( 'yith_ywdpd_get_price', $discount, $product );

		}

		/**
		 * Enqueue styles and scripts
		 *
		 * @access public
		 * @return void
		 * @since  1.0.0
		 */
		public function enqueue_styles_scripts() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_register_script( 'yith_ywdpd_frontend', YITH_YWDPD_ASSETS_URL . '/js/ywdpd-frontend' . $suffix . '.js', array( 'jquery' ), YITH_YWDPD_VERSION, true );
			wp_enqueue_style( 'yith_ywdpd_frontend', YITH_YWDPD_ASSETS_URL . '/css/frontend.css' );


			if ( $this->check_pricing_rules_combination() ) {

				$script = "jQuery( document.body ).on( 'updated_cart_totals', function(){
						window.location.href = window.location.href;
					});";
				wp_add_inline_script( 'wc-cart', $script );
			}

			wp_enqueue_script( 'yith_ywdpd_frontend' );
		}

		/**
		 * Check if pricing rules has disabled the combination with coupons
		 *
		 * @access public
		 * @return bool
		 * @since  1.1.4
		 */
		function check_pricing_rules_combination() {
			if ( ! WC()->cart ) {
				return false;
			}
			$cart_coupons = WC()->cart->applied_coupons;
			if ( ! empty( $cart_coupons ) && $this->pricing_rules ) {
				foreach ( $this->pricing_rules as $pricing_rule ) {
					$with_other_coupons = isset( $pricing_rule['disable_with_other_coupon'] ) && ywdpd_is_true( $pricing_rule['disable_with_other_coupon'] );
					if ( $with_other_coupons ) {
						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Show table quantity in the single product if there's a pricing rule
		 *
		 * @since  1.0.0
		 * @author Emanuela Castorina
		 *
		 * @param bool $product
		 * @param bool $sh
		 */
		public function show_table_quantity( $product = false, $sh = false ) {
			if ( ! $product ) {
				global $product;
			}

			if ( apply_filters( 'ywdpd_exclude_products_from_discount', false, $product ) ) {
				return;
			}


			$table_rules = $this->get_table_rules( $product );

			echo ( $sh ) ? '<div class="ywdpd-table-discounts-wrapper-sh">' : '<div class="ywdpd-table-discounts-wrapper">';
			if ( $table_rules ) {

				foreach ( $table_rules as $rule ) {
					$showtable = isset( $rule['show_table_price'] ) && ywdpd_is_true( $rule['show_table_price'] );
					if ( ! $showtable  ) {
						continue;
					}
					$show_quantity_table_schedule = YITH_WC_Dynamic_Pricing()->get_option( 'show_quantity_table_schedule' );
					$args = array(
						'rules'          => $rule['rules'],
						'main_rule'      => $rule,
						'product'        => $product,
						'note'           => $rule['table_note'],
						'label_table'    => YITH_WC_Dynamic_Pricing()->get_option( 'show_quantity_table_label' ),
						'label_quantity' => YITH_WC_Dynamic_Pricing()->get_option( 'show_quantity_table_label_quantity' ),
						'label_price'    => YITH_WC_Dynamic_Pricing()->get_option( 'show_quantity_table_label_price' ),
						'until' => ( ywdpd_is_true( $show_quantity_table_schedule ) && $rule['schedule_to'] != '' ) ? sprintf( __( 'Offer ends: %s', 'ywdpd' ), date_i18n( wc_date_format(), strtotime( $rule['schedule_to'] ) ) ) : ''
					);

					wc_get_template( 'yith_ywdpd_table_pricing.php', $args, '', YITH_YWDPD_TEMPLATE_PATH );

				}

				add_filter( 'woocommerce_' . $this->get_product_filter . 'get_price', array( $this, 'get_price' ), 10, 2 );
			}
			echo '</div>';
		}

		/**
		 * @param $product
		 *
		 * @return string
		 */
		public function table_quantity( $product ) {
			ob_start();
			$this->show_table_quantity( $product );

			return ob_get_clean();
		}

		/**
		 * Table Quantity Shortcode
		 *
		 * @param      $atts
		 * @param null $content
		 *
		 * @return mixed
		 * @internal param $product
		 *
		 */
		public function table_quantity_shortcode( $atts, $content = null ) {

			$args = shortcode_atts( array(
				                        'product' => false
			                        ), $atts );

			$the_product = wc_get_product( $args['product'] );

			if ( ! $the_product || apply_filters( 'ywdpd_exclude_products_from_discount', false, $the_product ) ) {
				return '';
			}

			$this->show_table_quantity( $the_product, true );
		}

		/**
		 * Add action for single product page to display table pricing
		 *
		 * @since  1.0.0
		 * @author Emanuela Castorina
		 */
		function table_quantity_init() {
			//Table Pricing
			$position                    = YITH_WC_Dynamic_Pricing()->get_option( 'show_quantity_table_place' );
			$priority_single_add_to_cart = has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart' );
			$priority_single_excerpt     = has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt' );
			switch ( $position ) {
				case 'before_add_to_cart':
					if ( $priority_single_add_to_cart ) {
						add_action( 'woocommerce_single_product_summary', array(
							$this,
							'show_table_quantity'
						), $priority_single_add_to_cart - 1 );
					} else {
						add_action( 'woocommerce_single_product_summary', array( $this, 'show_table_quantity' ), 28 );
					}
					break;
				case 'after_add_to_cart':
					if ( $priority_single_add_to_cart ) {
						add_action( 'woocommerce_single_product_summary', array(
							$this,
							'show_table_quantity'
						), $priority_single_add_to_cart + 1 );
					} else {
						add_action( 'woocommerce_single_product_summary', array( $this, 'show_table_quantity' ), 32 );
					}
					break;
				case 'before_excerpt':
					if ( $priority_single_excerpt ) {
						add_action( 'woocommerce_single_product_summary', array(
							$this,
							'show_table_quantity'
						), $priority_single_excerpt - 1 );
					} else {
						add_action( 'woocommerce_single_product_summary', array( $this, 'show_table_quantity' ), 18 );
					}
					break;
				case 'after_excerpt':
					if ( $priority_single_excerpt ) {
						add_action( 'woocommerce_single_product_summary', array(
							$this,
							'show_table_quantity'
						), $priority_single_excerpt + 1 );
					} else {
						add_action( 'woocommerce_single_product_summary', array( $this, 'show_table_quantity' ), 22 );
					}
					break;
				case 'after_meta':
					$priority_after_meta = has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta' );
					if ( $priority_after_meta ) {
						add_action( 'woocommerce_single_product_summary', array(
							$this,
							'show_table_quantity'
						), $priority_after_meta + 1 );
					} else {
						add_action( 'woocommerce_single_product_summary', array( $this, 'show_table_quantity' ), 42 );
					}
					break;
				default:
					break;
			}
		}

		/**
		 * Add action for single product page to display table pricing
		 *
		 * @since  1.0.0
		 * @author Emanuela Castorina
		 */
		function note_on_products_init() {
			//Table Pricing
			$position                    = YITH_WC_Dynamic_Pricing()->get_option( 'show_note_on_products_place' );
			$priority_single_add_to_cart = has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart' );
			$priority_single_excerpt     = has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt' );
			switch ( $position ) {
				case 'before_add_to_cart':
					if ( $priority_single_add_to_cart ) {
						add_action( 'woocommerce_single_product_summary', array(
							$this,
							'show_note_on_products'
						), $priority_single_add_to_cart - 1 );
					} else {
						add_action( 'woocommerce_single_product_summary', array( $this, 'show_note_on_products' ), 28 );
					}
					break;
				case 'after_add_to_cart':
					if ( $priority_single_add_to_cart ) {
						add_action( 'woocommerce_single_product_summary', array(
							$this,
							'show_note_on_products'
						), $priority_single_add_to_cart + 1 );
					} else {
						add_action( 'woocommerce_single_product_summary', array( $this, 'show_note_on_products' ), 32 );
					}
					break;
				case 'before_excerpt':
					if ( $priority_single_excerpt ) {
						add_action( 'woocommerce_single_product_summary', array(
							$this,
							'show_note_on_products'
						), $priority_single_excerpt - 1 );
					} else {
						add_action( 'woocommerce_single_product_summary', array( $this, 'show_note_on_products' ), 18 );
					}
					break;
				case 'after_excerpt':
					if ( $priority_single_excerpt ) {
						add_action( 'woocommerce_single_product_summary', array(
							$this,
							'show_note_on_products'
						), $priority_single_excerpt + 1 );
					} else {
						add_action( 'woocommerce_single_product_summary', array( $this, 'show_note_on_products' ), 22 );
					}
					break;
				case 'after_meta':
					$priority_after_meta = has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta' );
					if ( $priority_after_meta ) {
						add_action( 'woocommerce_single_product_summary', array(
							$this,
							'show_note_on_products'
						), $priority_after_meta + 1 );
					} else {
						add_action( 'woocommerce_single_product_summary', array( $this, 'show_note_on_products' ), 42 );
					}
					break;
				default:
					break;
			}
		}
	}
}

/**
 * Unique access to instance of YITH_WC_Dynamic_Pricing_Frontend class
 *
 * @return \YITH_WC_Dynamic_Pricing_Frontend
 */
function YITH_WC_Dynamic_Pricing_Frontend() {
	return YITH_WC_Dynamic_Pricing_Frontend::get_instance();
}
