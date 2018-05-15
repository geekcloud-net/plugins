<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_YWRBP_AeliaCS_Module' ) ) {

	class YITH_YWRBP_AeliaCS_Module {

		protected static $_instance;

		protected static $base_currency;

		public function __construct() {
			/**
			 * Aelia  Multi-currency support
			 */
			add_filter( 'wc_aelia_currencyswitcher_product_convert_callback', array(
				$this,
				'wc_aelia_currencyswitcher_product_convert_callback'
			), 10, 2 );

			add_filter( 'yith_ywrbp_price', array( $this, 'convert_base_currency_amount_to_user_currency' ), 10, 2 );
			add_filter( 'yith_ywrbp_regular_price', array(
				$this,
				'convert_base_currency_amount_to_user_currency'
			), 10, 2 );
			add_filter( 'yith_ywcrbp_sale_price', array(
				$this,
				'convert_base_currency_amount_to_user_currency'
			), 10, 2 );
		}

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Callback to support currency conversion of Gift Card products.
		 *
		 * @param callable $callback The original callback passed by the Currency
		 *                             Switcher.
		 * @param WC_Product $product The product to convers.
		 *
		 * @return callable The callback that will perform the conversion.
		 * @since  1.0.6
		 * @author Aelia <support@aelia.co>
		 */
		public function wc_aelia_currencyswitcher_product_convert_callback( $callback, $product ) {

			$product_id = yit_get_base_product_id( $product );

			if ( YITH_Role_Based_Prices_Product()->has_price_rule( $product_id ) ) {

				$callback = array( $this, 'convert_product_role_prices' );
			}

			return $callback;
		}

		/**
		 * @author YITHEMES
		 * @since 1.0.0
		 *
		 * @param WC_Product $product
		 * @param string $currency
		 *
		 * @return WC_Product
		 */
		public function convert_product_role_prices( $product, $currency ) {

			$product_id = yit_get_base_product_id( $product );
			$new_price  = YITH_Role_Based_Prices_Product()->get_role_based_price( $product );

			$product->set_price( $new_price );
			return $product;
		}

		/**
		 * Convenience method. Returns WooCommerce base currency.
		 *
		 * @return string
		 * @since 1.0.6
		 */
		public static function base_currency() {
			if ( empty( self::$base_currency ) ) {
				self::$base_currency = get_option( 'woocommerce_currency' );
			}

			return self::$base_currency;
		}

		/**
		 * Basic integration with WooCommerce Currency Switcher, developed by Aelia
		 * (https://aelia.co). This method can be used by any 3rd party plugin to
		 * return prices converted to the active currency.
		 *
		 * @param double $amount The source price.
		 * @param string $to_currency The target currency. If empty, the active currency
		 *                              will be taken.
		 * @param string $from_currency The source currency. If empty, WooCommerce base
		 *                              currency will be taken.
		 *
		 * @return double The price converted from source to destination currency.
		 * @author Aelia <support@aelia.co>
		 * @link   https://aelia.co
		 * @since  1.0.6
		 */
		public static function get_amount_in_currency( $amount, $to_currency = null, $from_currency = null ) {
			if ( empty( $from_currency ) ) {
				$from_currency = self::base_currency();
			}
			if ( empty( $to_currency ) ) {
				$to_currency = get_woocommerce_currency();
			}

			return apply_filters( 'wc_aelia_cs_convert', $amount, $from_currency, $to_currency );
		}

		/**
		 * Convert the amount from base currency to current currency
		 *
		 * @param float $amount
		 * @param WC_Product $product
		 *
		 * @return float
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function convert_base_currency_amount_to_user_currency( $amount, $product_id ) {

			$product = wc_get_product( $product_id );

			error_log('ciao' );
			if ( 'no_price' === $amount || '' === $amount ) {
				return $amount;
			}

			$currency = yit_get_prop( $product, 'currency', true );
			if ( ! empty( $currency ) ) {

				return self::get_amount_in_currency( $amount, null, $currency );
			}

			return self::get_amount_in_currency( $amount );
		}

		/**
		 * Convert the amount from current currency to base currency
		 *
		 * @param float $amount
		 *
		 * @return float
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function convert_user_currency_amount_to_base_currency( $amount ) {
			return self::get_amount_in_currency( $amount, self::base_currency(), get_woocommerce_currency() );
		}

	}
}

YITH_YWRBP_AeliaCS_Module::get_instance();