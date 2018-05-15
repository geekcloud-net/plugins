<?php

if ( !defined( 'ABSPATH' ) || !defined( 'YITH_YWDPD_VERSION' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Implements features of YITH WooCommerce Dynamic Pricing and Discounts
 *
 * @class   YITH_WC_Dynamic_Discounts
 * @package YITH WooCommerce Dynamic Pricing and Discounts
 * @since   1.0.0
 * @author  Yithemes
 */
if ( !class_exists( 'YITH_WC_Dynamic_Discounts' ) ) {

	/**
	 * Class YITH_WC_Dynamic_Discounts
	 */
	class YITH_WC_Dynamic_Discounts {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WC_Dynamic_Discounts
		 */

		protected static $instance;

		/**
		 * Plugin option name
		 * @var string
		 */
		public $plugin_options = 'yit_ywdpd_options';

		/**
		 * Array with discount rules
		 * @var array
		 */
		public $discount_rules = array();

		/**
		 * Discount amount for dynami coupon
		 * @var int
		 */
		public $discount_amount = 0;

		/**
		 * Label of coupon
		 * @var string
		 */
		public $label_coupon = 'discount';

		public $current_coupon_code = '';


		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WC_Dynamic_Discounts
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
			$label = preg_replace('/\s+/', '', YITH_WC_Dynamic_Pricing()->get_option( 'coupon_label' ));
			$this->label_coupon = strtolower($label);


			add_action( 'woocommerce_removed_coupon', array( $this, 'apply_discount' ) );
			add_filter( 'woocommerce_coupon_message', array( $this, 'coupon_cart_discount_message' ), 10, 3 );
			add_filter( 'woocommerce_cart_totals_coupon_label', array( $this, 'dynamic_label_coupon' ), 10, 2 );

			if( defined( 'ICL_SITEPRESS_VERSION' ) && apply_filters( 'ywdpd_wpml_use_default_language_settings', true ) ){
				add_filter( 'ywdpd_dynamic_discount_rules_filtered', array( $this, 'adjust_rules_for_wpml') );
			}

			add_action( 'wp_loaded', array( $this, 'ywdpd_set_cron' ) );
			add_action( 'ywdpd_clean_cron', array( $this, 'clear_coupons') );
			if ( version_compare( WC()->version, '2.7', '>' ) ) {
				add_action( 'woocommerce_checkout_create_order', array( $this, 'clear_ywdpd_coupon_after_create_order' ) );
			}
		}


		/**
		 * @param $order
		 *
		 * @throws Exception
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		public function clear_ywdpd_coupon_after_create_order( $order ) {
			$coupon_used = $order->get_used_coupons();
			if ( $coupon_used ) {
				foreach ( $coupon_used as $coupons_code ) {
					$coupon = new WC_Coupon( $coupons_code );
					$valid  = ywdpd_coupon_is_valid( $coupon, $order );
					if ( $this->check_coupon_is_ywdpd( $coupon ) && $valid ) {
						$coupon->delete();
					}
				}
			}
		}


		/**
		 *
		 */
		public function ywdpd_set_cron() {
			if ( ! wp_next_scheduled( 'ywdpd_clean_cron' ) ) {
				$duration = apply_filters('ywdpd_set_cron_time', 'daily' );
				wp_schedule_event( time(), $duration, 'ywdpd_clean_cron' );
			}
		}

		/**
		 * Return pricing rules filtered and validates
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0.0
		 * @author Emanuela Castorina
		 */
		function get_discount_rules() {
			if( empty( $this->discount_rules)  ){

				$this->discount_rules = $this->filter_valid_rules( $this->recover_cart_rules() );
			}

			return $this->discount_rules;
		}

		/**
		 * @return array
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		function recover_cart_rules(){
			$update_cpt = get_option( 'ywdpd_updated_to_cpt' );
			if( ! ywdpd_is_true( $update_cpt ) ){
				$cart_rules  = YITH_WC_Dynamic_Pricing()->get_option( 'cart-rules' );
			}else{
				$cart_rules = ywdpd_recover_rules( 'cart' );
			}

			return $cart_rules;
		}

		/**
		 * Filter valid cart discount rules
		 * @param $cart_rules
		 *
		 * @return array
		 */
		function filter_valid_rules( $cart_rules ){

			$valid_rules = array();

			if( !$cart_rules || empty( $cart_rules ) || ! array($cart_rules) || $cart_rules=='no' ){
				return $valid_rules;
			}

			// check if cart have coupon
			$cart_have_coupon = ywdpd_check_cart_coupon();

			$wpml_extend_to_translated_object = YITH_WC_Dynamic_Pricing()->get_option( 'wpml_extend_to_translated_object' );

			foreach( $cart_rules as $key=>$cart_rule ){

				if ( ! ywdpd_is_true( $cart_rule['active'] ) ) {
					continue;
				}

				if( isset( $cart_rule['discount_amount'] ) && $cart_rule['discount_amount'] == '' ){
					continue;
				}elseif( isset( $cart_rule['discount_type'] ) && $cart_rule['discount_type'] == 'percentage'){
					$cart_rule['discount_amount'] = ( $cart_rule['discount_amount'] > 1) ? $cart_rule['discount_amount']/100 : $cart_rule['discount_amount'];
				}

				//DATE SCHEDULE VALIDATION
				if ( $cart_rule['schedule_from'] != '' || $cart_rule['schedule_to'] != '' ) {
					if (!YITH_WC_Dynamic_Pricing_Helper()->validate_schedule($cart_rule['schedule_from'], $cart_rule['schedule_to'])) {
						continue;
					}
				}

				// DISCOUNT CAN BE COMBINED WITH COUPON
				$discount_combined = isset( $cart_rule['discount_combined'] )  && ywdpd_is_true( $cart_rule['discount_combined'] );
				if ( ! $discount_combined && $cart_have_coupon ) {
					continue;
				}


				$sub_rules_valid = true;

				if ( !empty( $cart_rule['rules'] ) ) {

					foreach ( $cart_rule['rules'] as $index => $r ) {

						if ( !$sub_rules_valid ) {

							break;
						}

						$discount_type = $r['rules_type'];

						switch ( $discount_type ) {
							case '':
								continue;
								break;
							case 'customers_list':
							case 'customers_list_excluded':
							case 'role_list':
							case 'role_list_excluded':

								if ( !isset( $r['rules_type_' . $discount_type] ) || ! YITH_WC_Dynamic_Pricing_Helper()->validate_user( $discount_type, $r['rules_type_' . $discount_type] ) ) {
									$sub_rules_valid = false;
									continue;
								}

								break;
							case 'products_list':
							case 'products_list_and':
							case 'products_list_excluded':
							case 'categories_list':
							case 'categories_list_and':
							case 'categories_list_excluded':
							case 'tags_list':
							case 'tags_list_and':
							case 'tags_list_excluded':
							case 'brand_list':
							case 'brand_list_and':
							case 'brand_list_excluded':
								if( isset( $r['rules_type_' . $discount_type] )  && defined( 'ICL_SITEPRESS_VERSION' ) && ywdpd_is_true( $wpml_extend_to_translated_object ) ) {
									$r[ 'rules_type_' . $discount_type ] = YITH_WC_Dynamic_Pricing_Helper()->wpml_product_list_adjust( $r[ 'rules_type_' . $discount_type ], $discount_type );
								}
								if ( ! isset( $r['rules_type_' . $discount_type] ) || empty( $r['rules_type_' . $discount_type] ) || !YITH_WC_Dynamic_Pricing_Helper()->validate_product_in_cart( $discount_type, $r['rules_type_' . $discount_type] ) ) {
									$sub_rules_valid = false;
									continue;
								}

								break;
							case 'num_of_orders':
							case 'max_num_of_orders':
							case 'amount_spent':
							case 'max_amount_spent':
							case 'sum_item_quantity':
							case 'sum_item_quantity_less':
							case 'count_cart_items_less':
							case 'count_cart_items_at_least':
							case 'subtotal_at_least':
							case 'subtotal_less':
								$s = 'valid_' . $discount_type;

								if ( !isset( $r['rules_type_' . $discount_type] ) || $r['rules_type_' . $discount_type] == '' || !YITH_WC_Dynamic_Pricing_Helper()->$s( $r['rules_type_' . $discount_type] ) ) {
									$sub_rules_valid = false;

									continue;
								}

								break;
							default:

						}

						$sub_rules_valid = apply_filters( 'yit_ywdpd_sub_rules_valid', $sub_rules_valid, $discount_type, $r, $key );
					}
				}


				if( $sub_rules_valid ){
					$valid_rules[$key] = $cart_rule;
				}

			}

			return $valid_rules;
		}

		/**
		 * Apply discount to cart items
		 * @return void
		 * @throws Exception
		 */
		public function apply_discount() {

			$rules = $this->get_discount_rules();
			$discount = $this->get_discount_amount();

			if ( ! empty ( $rules ) && $discount > 0 ) {

				if ( version_compare( WC()->version, '2.7', '<' ) ) {
					add_filter( 'woocommerce_get_shop_coupon_data', array( $this, 'create_coupon_cart_discount' ), 10, 2 );
				} else {
					add_filter( 'woocommerce_cart_totals_coupon_label', array( $this, 'dynamic_label_coupon' ), 10, 2 );

				}

				add_action( 'woocommerce_cart_updated', array( $this, 'apply_coupon_cart_discount' ) );
				add_filter( 'woocommerce_cart_totals_coupon_html', array( $this, 'coupon_cart_html' ), 10, 2 );


			} else {
				if ( version_compare( WC()->version, '2.7', '>' ) ) {
					$coupon = $this->get_current_coupon( );
					ywdpd_coupon_is_valid( $coupon, WC()->cart ) && $coupon->delete();
				}
			}
		}

		/**
		 * @param $string
		 * @param $coupon WC_Coupon
		 *
		 * @return string
		 */
		public function dynamic_label_coupon( $string, $coupon ) {

			if ( is_null( $coupon ) || ! is_object($coupon) ) {
				return $string;
			}


			if ( version_compare( WC()->version, '2.7', '>' ) ) {
				$coupon->get_amount();
				$is_ywdpd = $coupon->get_meta( 'ywdpd_coupon', true );

				$coupon_label = YITH_WC_Dynamic_Pricing()->get_option( 'coupon_label' ) . ':';
			} else {
				$is_ywdpd     = ( $coupon->code == $this->label_coupon );
				$coupon_label = YITH_WC_Dynamic_Pricing()->get_option( 'coupon_label' );
			}

			$coupon_label = apply_filters( 'ywdpd_dynamic_label_coupon', $coupon_label, $coupon );

			return $is_ywdpd ? esc_html( __( $coupon_label, 'ywdpd' ) ) : $string;
		}

		/**
		 * Create coupon cart discount for WooCommerce < 3.0.0
		 * @param $args
		 * @param $code
		 *
		 * @return array
		 */
		function create_coupon_cart_discount( $args, $code ) {

			if ( $code == $this->label_coupon ) {

				$args = array(
					'amount'           => $this->discount_amount,
					'apply_before_tax' => 'yes',
					'type'             => 'fixed_cart',
					'free_shipping'    => 'no',
					'individual_use'   => false,
					'usage_limit'      => 0
				);
			}

			return $args;
		}

		/**
		 * Apply coupon cart discount to the cart
		 *
		 */
		function apply_coupon_cart_discount() {

			$rules        = $this->get_discount_rules();
			$discount     = $this->get_discount_amount();
			$coupon       = false;
			$coupon_label = $this->label_coupon;

			if ( empty ( $rules ) || $discount <= 0 ) {
				return;
			}

			if ( version_compare( WC()->version, '2.7', '<' ) ) {
				$coupon                = new WC_Coupon( $coupon_label );
				$coupon->coupon_amount = $this->get_discount_amount();
			} else {

				$coupon = $this->get_current_coupon();
				$valid = ywdpd_coupon_is_valid( $coupon, WC()->cart );
				if ( $valid ) {
					$coupon->set_amount( $discount );
				} else {
					$args = array(
						'id'             => false,
						'discount_type'  => 'fixed_cart',
						'amount'         => $discount,
						'individual_use' => false,
						'free_shipping'  => false,
						'usage_limit'    => 0,
					);

					$coupon->add_meta_data( 'ywdpd_coupon', 1 );
					$coupon->read_manual_coupon( $coupon->get_code(), $args );
				}

				$coupon->save();
				$coupon_label = $coupon->get_code();
			}

			if ( ywdpd_coupon_is_valid( $coupon, WC()->cart ) && ! WC()->cart->has_discount( $coupon_label ) ) {
				WC()->cart->add_discount( $coupon_label );
			}

		}


		/**
		 * Return the coupon to apply
		 * @return WC_Coupon
		 */
		public function get_current_coupon() {

			if ( empty( $this->current_coupon_code ) ) {
				//check if in the cart
				$coupons_in_cart = WC()->cart->get_applied_coupons();

				foreach ( $coupons_in_cart as $coupon_in_cart_code ) {
					try{
						$coupon_in_cart = new WC_Coupon( $coupon_in_cart_code );
						$meta           = $coupon_in_cart->get_meta( 'ywdpd_coupon' );
						if ( ! empty( $meta ) ) {
							$this->current_coupon_code = $coupon_in_cart_code;
							break;
						}
					}catch ( WC_API_Exception $e ) {
						continue;
					}
				}
			}

			if ( empty( $this->current_coupon_code ) ) {
				if ( is_user_logged_in() ) {
					$this->current_coupon_code = apply_filters( 'ywdpd_coupon_code', $this->label_coupon . '_' . get_current_user_id(), $this->label_coupon );
				} else {
					$session = WC()->session->get( 'ywdpd_coupon_code', $this->current_coupon_code );
					if( $session == '' ){
						$this->current_coupon_code = apply_filters( 'ywdpd_coupon_code', uniqid( strtolower( $this->label_coupon ) ), $this->label_coupon );
						WC()->session->set( 'ywdpd_coupon_code', $this->current_coupon_code );
						WC()->session->save_data();
					}else{
						$this->current_coupon_code = $session;
					}
				}
			}

			return empty( $this->current_coupon_code ) ? false: new WC_Coupon( $this->current_coupon_code);
		}

		/**
		 * Change the label of coupon
		 *
		 * @since   1.0.0
		 * @author  Emanuela Castorina
		 *
		 * @param $string
		 * @param $coupon
		 *
		 * @return string
		 */
		public function label_coupon( $string, $coupon ) {

			//change the label if the order is generated from a quote
			if ( $coupon->code != $this->label_coupon ) {
				return $string;
			}

			return $this->label_coupon;
		}

		/**
		 * @param $value
		 * @param $coupon WC_Coupon
		 *
		 * @return string
		 */
		function coupon_cart_html( $value, $coupon ) {

			if ( version_compare( WC()->version, '2.7', '<' ) ) {
				if ( $coupon->code == $this->label_coupon ) {
					$amount = WC()->cart->get_coupon_discount_amount( $coupon->code, WC()->cart->display_cart_ex_tax );
					$value  = '-' . wc_price( $amount );
				}
			} else {
				$is_ywdpd = $coupon->get_meta( 'ywdpd_coupon', true );
				if ( $is_ywdpd ) {
					$amount = WC()->cart->get_coupon_discount_amount( $coupon->get_code(), WC()->cart->display_cart_ex_tax );
					$value  = '-' . wc_price( $amount );
				}
			}

			return $value;
		}

		/**
		 * @param $msg
		 * @param $msg_code
		 * @param $coupon WC_Coupon
		 *
		 * @return string
		 */
		function coupon_cart_discount_message( $msg, $msg_code, $coupon ) {

			if ( version_compare( WC()->version, '2.7', '>' ) ) {
				$is_ywdpd = $coupon->get_meta( 'ywdpd_coupon', true );
			} else {
				$is_ywdpd     = ( $coupon->code == $this->label_coupon);
			}

			return $is_ywdpd ? '' : $msg;
		}


		/**
		 * Check id a YWDPD is in the list
		 * @param  WC_Coupon
		 *
		 * @return bool
		 */
		public function check_coupon_is_ywdpd( $coupon ) {

			if ( version_compare( WC()->version, '2.7', '>' ) ) {
				$is_ywdpd = $coupon->get_meta( 'ywdpd_coupon', true );
			} else {
				$is_ywdpd     = ( $coupon->code == $this->label_coupon);
			}
			return $is_ywdpd;
		}

		/**
		 * @return float|int
		 */
		function get_discount_amount(){
			$discount = 0;

			if ( ! empty( $this->discount_rules ) ) {
				if( method_exists( WC()->cart, 'get_subtotal') ){
					$subtotal = apply_filters( 'ywdpd_get_subtotal', YITH_WC_Dynamic_Pricing()->get_option( 'calculate_discounts_tax' ) == 'tax_excluded' ?  WC()->cart->get_subtotal() : WC()->cart->get_subtotal() + WC()->cart->get_subtotal_tax() );
				}else{
					$subtotal = apply_filters( 'ywdpd_get_subtotal', YITH_WC_Dynamic_Pricing()->get_option( 'calculate_discounts_tax' ) == 'tax_excluded' ?  WC()->cart->subtotal_ex_tax : WC()->cart->subtotal );
				}

				foreach ( $this->discount_rules as $rule ) {
					if ( $rule['discount_type'] == 'percentage' ) {
						$discount += $subtotal * $rule['discount_amount'];
					} elseif ( $rule['discount_type'] == 'price' ) {
						$discount += $rule['discount_amount'];
					}elseif ( $rule['discount_type'] == 'fixed-price' ) {
						$discount += ( $subtotal - $rule['discount_amount'] ) > 0 ? ($subtotal - $rule['discount_amount']) : 0 ;
					}
				}
			}

			$this->discount_amount = $discount;


			return $discount;
		}

		/**
		 * Clear coupons after use
		 *
		 * @since  1.0.0
		 * @author Emanuela Castorina
		 */
		function clear_coupons() {

			$args = array(
				'post_type'       => 'shop_coupon',
				'posts_per_page' => - 1,
				'meta_key'        => 'ywdpd_coupon',
				'meta_value'      => 1,
				'date_query'      => array(
					array(
						'column' => 'post_date_gmt',
						'before' => '1 day ago',
					),
				),
			);

			$coupons = get_posts( $args );

			if ( ! empty( $coupons ) ) {
				foreach ( $coupons as $coupon ) {
					wp_delete_post( $coupon->ID, true );
				}
			}
		}


	}
}

/**
 * Unique access to instance of YITH_WC_Dynamic_Pricing class
 *
 * @return \YITH_WC_Dynamic_Discounts
 */
function YITH_WC_Dynamic_Discounts() {
	return YITH_WC_Dynamic_Discounts::get_instance();
}

