<?php
namespace Aelia\WC\CurrencySwitcher\WC26;
if(!defined('ABSPATH')) exit; // Exit if accessed directly

use \WC_Product;
use \WC_Product_Simple;
use \WC_Product_Variation;
use \WC_Product_External;
use \WC_Product_Grouped;
use \WC_Cache_Helper;
use \Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher;
use \Aelia\WC\CurrencySwitcher\Definitions;

interface IWC_Aelia_CurrencyPrices_Manager {
	public function convert_product_prices(WC_Product $product, $currency);
	public function convert_external_product_prices(WC_Product_External $product, $currency);
	public function convert_grouped_product_prices(WC_Product_Grouped $product, $currency);
	public function convert_simple_product_prices(WC_Product $product, $currency);
	public function convert_variable_product_prices(WC_Product $product, $currency);
	public function convert_variation_product_prices(WC_Product_Variation $product, $currency);
}

/**
 * Handles currency conversion for the various product types.
 * Due to its architecture, this class should not be instantiated twice. To get
 * the instance of the class, call WC_Aelia_CurrencyPrices_Manager::Instance().
 */
class WC_Aelia_CurrencyPrices_Manager implements IWC_Aelia_CurrencyPrices_Manager {
	protected $admin_views_path;

	// @var WC_Aelia_CurrencyPrices_Manager The singleton instance of the prices manager
	protected static $instance;

	// @var Shop's base currency. Used for caching.
	protected static $_base_currency;

	// @var string The text domain to use for messages
	protected static $text_domain = Definitions::TEXT_DOMAIN;

	// @var The instance of the logger used by the class
	protected $_logger;

	const FIELD_REGULAR_CURRENCY_PRICES = '_regular_currency_prices';
	const FIELD_SALE_CURRENCY_PRICES = '_sale_currency_prices';
	const FIELD_VARIABLE_REGULAR_CURRENCY_PRICES = 'variable_regular_currency_prices';
	const FIELD_VARIABLE_SALE_CURRENCY_PRICES = 'variable_sale_currency_prices';
	const FIELD_PRODUCT_BASE_CURRENCY = '_product_base_currency';
	const FIELD_COUPON_CURRENCY_DATA = '_coupon_currency_data';

	/**
	 * Convenience method. Returns the instance of the Currency Switcher.
	 *
	 * @return WC_Aelia_CurrencySwitcher
	 */
	protected function currencyswitcher() {
		return WC_Aelia_CurrencySwitcher::instance();
	}

	/**
	 * Returns a logger instance.
	 *
	 * @return Aelia\WC\Logger
	 * @since 4.4.2.170117
	 */
	protected function logger() {
		if(empty($this->_logger)) {
			$this->_logger = $this->currencyswitcher()->get_logger();
		}
		return $this->_logger;
	}

	/**
	 * Convenience method. Returns WooCommerce base currency.
	 *
	 * @return string
	 */
	public function base_currency() {
		if(empty(self::$_base_currency)) {
			self::$_base_currency = WC_Aelia_CurrencySwitcher::settings()->base_currency();
		}
		return self::$_base_currency;
	}

	/**
	 * Returns the active currency.
	 *
	 * @return string The code of currently selected currency.
	 * @since 3.7.9.150813
	 */
	public function get_selected_currency() {
		return $this->currencyswitcher()->get_selected_currency();
	}

	/**
	 * Converts an amount from base currency to another.
	 *
	 * @param float amount The amount to convert.
	 * @param string to_currency The destination Currency.
	 * @param int precision The precision to use when rounding the converted result.
	 * @return float The amount converted in the destination currency.
	 */
	public function convert_from_base($amount, $to_currency, $from_currency = null) {
		// If the amount is not numeric, then it cannot be converted reliably
		// (assuming that it's "zero" would be incorrect
		if(!is_numeric($amount)) {
			return $amount;
		}

		if(empty($from_currency)) {
			$from_currency = $this->base_currency();
		}

		return $this->currencyswitcher()->convert($amount, $from_currency, $to_currency);
	}

	/**
	 * Converts a product price from base currency to another. This method is
	 * equivalent to WC_Aelia_CurrencyPrices_Manager::convert_from_base(), the only
	 * difference is that it triggers an event to allow 3rd parties to alter the
	 * converted amount.
	 *
	 * @param float amount The amount to convert.
	 * @param string to_currency The destination Currency.
	 * @param int precision The precision to use when rounding the converted result.
	 * @param WC_Product The product whose price is being converted.
	 * @param string price_type The price being converted (e.g. regular_price,
	 * sale_price, signup_fee, etc).
	 * @return float The amount converted in the destination currency.
	 * @see WC_Aelia_CurrencyPrices_Manager::convert_from_base()
	 * @since 4.2.12.151105
	 */
	public function convert_product_price_from_base($amount, $to_currency, $from_currency = null, $product = null, $price_type = '') {
		// If the amount is not numeric, then it cannot be converted reliably.
		// Assuming that it's "zero" would be incorrect
		if(!is_numeric($amount)) {
			return $amount;
		}

		// Allow 3rd parties to modify the converted price
		return apply_filters('wc_aelia_cs_convert_product_price',
												 $this->convert_from_base($amount, $to_currency, $from_currency),
												 $amount,
												 $from_currency,
												 $to_currency,
												 WC_Aelia_CurrencySwitcher::settings()->price_decimals($to_currency),
												 $product,
												 $price_type);
	}

	/**
	 * Callback for array_filter(). Returns true if the passed value is numeric.
	 *
	 * @param mixed value The value to check.
	 * @return bool
	 */
	protected function keep_numeric($value) {
		return is_numeric($value);
	}

	/**
	 * Returns the minimum numeric value found in an array. Non numeric values are
	 * ignored. If no numeric value is passed in the array of values, then NULL is
	 * returned.
	 *
	 * @param array values An array of values.
	 * @return float|null
	 */
	public function get_min_value(array $values) {
		$values = array_filter($values, array($this, 'keep_numeric'));

		if(empty($values)) {
			return null;
		}

		return min($values);
	}

	/**
	 * Returns the maximum numeric value found in an array. Non numeric values are
	 * ignored. If no numeric value is passed in the array of values, then NULL is
	 * returned.
	 *
	 * @param array values An array of values.
	 * @return float|null
	 */
	public function get_max_value(array $values) {
		$values = array_filter($values, array($this, 'keep_numeric'));

		if(empty($values)) {
			return null;
		}

		return max($values);
	}

	/*** Hooks ***/
	/**
	 * Display Currency prices for Simple Products.
	 */
	public function woocommerce_product_options_pricing() {
		global $post;
		$this->current_post = $post;

		$file_to_load = apply_filters('wc_aelia_currencyswitcher_simple_product_pricing_view_load', 'simpleproduct_currencyprices_view.php', $post);
		$this->load_view($file_to_load);
	}

	/**
	 * Display Currency prices for Variable Products.
	 */
	public function woocommerce_product_after_variable_attributes($loop, $variation_data, $variation) {
		//var_dump($loop, $variation_data, $variation);
		$this->current_post = $variation;

		$this->loop_idx = $loop;

		$file_to_load = apply_filters('wc_aelia_currencyswitcher_variation_product_pricing_view_load', 'variation_currencyprices_view.php', $variation);
		$this->load_view($file_to_load);
	}

	/**
	 * Event handler fired when a Product is being saved. It processes and saves
	 * the Currency Prices associated with the Product.
	 *
	 * @param int post_id The ID of the Post (product) being saved.
	 */
	public function process_product_meta($post_id) {
		//var_dump($_POST);die();

		$product_regular_prices = isset($_POST[self::FIELD_REGULAR_CURRENCY_PRICES]) ? $_POST[self::FIELD_REGULAR_CURRENCY_PRICES] : array();
		$product_regular_prices = $this->sanitise_currency_prices($product_regular_prices);

		$product_sale_prices = isset($_POST[self::FIELD_SALE_CURRENCY_PRICES]) ? $_POST[self::FIELD_SALE_CURRENCY_PRICES] : array();
		$product_sale_prices = $this->sanitise_currency_prices($product_sale_prices);

		// D.Zanella - This code saves the product prices in the different Currencies
		update_post_meta($post_id, self::FIELD_REGULAR_CURRENCY_PRICES, json_encode($product_regular_prices));
		update_post_meta($post_id, self::FIELD_SALE_CURRENCY_PRICES, json_encode($product_sale_prices));

		$product_base_currency = isset($_POST[self::FIELD_PRODUCT_BASE_CURRENCY]) ? $_POST[self::FIELD_PRODUCT_BASE_CURRENCY] : '';
		update_post_meta($post_id, self::FIELD_PRODUCT_BASE_CURRENCY, $product_base_currency);
	}

	/**
	 * Event handler fired when a Product is being saved. It processes and saves
	 * the Currency Prices associated with the Product.
	 *
	 * @param int post_id The ID of the Post (product) being saved.
	 */
	public function woocommerce_process_product_meta_variable($post_id) {
		$shop_base_currency = $this->base_currency();

		// Retrieve all IDs, regular prices and sale prices for all variations. The
		// "all_" prefix has been added to easily distinguish these variables from
		// the ones containing the data of a single variation, whose names would
		// be otherwise very similar
		$all_variations_ids = isset($_POST['variable_post_id']) ? $_POST['variable_post_id'] : array();

		$all_variations_regular_currency_prices = isset($_POST[self::FIELD_VARIABLE_REGULAR_CURRENCY_PRICES]) ? $_POST[self::FIELD_VARIABLE_REGULAR_CURRENCY_PRICES] : array();
		$all_variations_sale_currency_prices = isset($_POST[self::FIELD_VARIABLE_SALE_CURRENCY_PRICES]) ? $_POST[self::FIELD_VARIABLE_SALE_CURRENCY_PRICES] : array();
		$all_variations_base_currencies = isset($_POST[self::FIELD_PRODUCT_BASE_CURRENCY]) ? $_POST[self::FIELD_PRODUCT_BASE_CURRENCY] : array();

		foreach($all_variations_ids as $variation_idx => $variation_id) {
			$variation_regular_currency_prices = isset($all_variations_regular_currency_prices[$variation_idx]) ? $all_variations_regular_currency_prices[$variation_idx] : null;
			$variation_regular_currency_prices = $this->sanitise_currency_prices($variation_regular_currency_prices);

			$variation_sale_currency_prices = isset($all_variations_sale_currency_prices[$variation_idx]) ? $all_variations_sale_currency_prices[$variation_idx] : null;
			$variation_sale_currency_prices = $this->sanitise_currency_prices($variation_sale_currency_prices);

			$variation_base_currency = isset($all_variations_base_currencies[$variation_idx]) ? $all_variations_base_currencies[$variation_idx] : $shop_base_currency;

			// D.Zanella - This code saves the variation prices in the different Currencies
			update_post_meta($variation_id, self::FIELD_VARIABLE_REGULAR_CURRENCY_PRICES, json_encode($variation_regular_currency_prices));
			update_post_meta($variation_id, self::FIELD_VARIABLE_SALE_CURRENCY_PRICES, json_encode($variation_sale_currency_prices));
			update_post_meta($variation_id, self::FIELD_PRODUCT_BASE_CURRENCY, $variation_base_currency);
		}
	}

	/**
	 * Handles the saving of variations data using the new logic introduced in
	 * WooCommerce 2.4.
	 *
	 * @param int product_id The ID of the variable product whose variations are
	 * being saved.
	 * @since 4.1.3.150730
	 */
	public function woocommerce_ajax_save_product_variations($product_id) {
		$this->woocommerce_process_product_meta_variable($product_id);
	}

/**
	 * Handles the bulk edit of variations data using the new logic introduced in
	 * WooCommerce 2.4.
	 *
	 * @param string bulk_action The action to be performed on te variations.
	 * @param mixed data The data passed with the action.
	 * @param int product_id The ID of the variable product whose variations are
	 * being saved.
	 * @param array variations An array of the variations IDs against which the
	 * action is going to be performed.
	 * @since 4.2.5.150907
	 * @since WC 2.4
	 */
	public function woocommerce_bulk_edit_variations($bulk_action, $data, $product_id, $variations) {
		$prices_type = '';
		// Check if the action is to set variations' regular prices
		if(stripos($bulk_action, 'variable_regular_currency_prices') === 0) {
			$prices_type = self::FIELD_VARIABLE_REGULAR_CURRENCY_PRICES;
		}
		// Check if the action is to set variations' sale prices
		if(stripos($bulk_action, 'variable_sale_currency_prices') === 0) {
			$prices_type = self::FIELD_VARIABLE_SALE_CURRENCY_PRICES;
		}

		if(!empty($prices_type)) {
			$this->bulk_set_variations_prices($variations, $prices_type, $data['currency'], $data['value']);
		}
	}

	/**
	 * Sets a price for a list of variations.
	 *
	 * @param array variations An array of variations to update.
	 * @param string prices_type The type of price to update.
	 * @param string currency The currency in which the price is being set.
	 * @param float price The price to set.
	 * @since 4.2.5.150907
	 * @since WC 2.4
	 */
	protected function bulk_set_variations_prices($variations, $prices_type, $currency, $price) {
		if(!is_array($variations) || empty($variations)) {
			return;
		}

		foreach($variations as $variation_id) {
			// Retrieve the existing prices
			$prices = $this->get_product_currency_prices($variation_id, $prices_type);
			// Set the new price on the variation
			$prices[$currency] = $price;
			update_post_meta($variation_id, $prices_type, json_encode($prices));
		}
	}

	/**
	 * Returns the HTML to display minimum price for a grouped product, in
	 * currently selected currency. This method replaces the logic of
	 * WC_Product_Grouped::get_price_html() and takes into account exchange rates
	 * and manually entered product prices.
	 *
	 * @param float price The product price.
	 * @param WC_Product_Grouped product The grouped product.
	 * @return string
	 */
	public function woocommerce_grouped_price_html($price, $product) {
		$child_prices = array();

		foreach($product->get_children(false) as $child_id) {
			// Price must be converted to currently selected currency. To do so, a
			// Product must be instantiated, so that we can find out if there are
			// manually entered prices, or if the exchange rate should be used
			$product = $this->get_product($child_id);
			$child_prices[] = $product->get_price();
		}

		$child_prices = array_unique($child_prices);

		if(!empty($child_prices)) {
			$min_price = min($child_prices);
			$max_price = max($child_prices);
		}
		else {
			$min_price = '';
			$max_price = '';
		}

		$price = '';
		if($min_price) {
			$tax_display_mode = get_option('woocommerce_tax_display_shop');
			$get_price_method = 'get_price_' . $tax_display_mode . 'uding_tax';

			if($min_price == $max_price) {
				$display_price = wc_price($product->$get_price_method(1, $min_price));
			}
			else {
				$from = wc_price($product->$get_price_method(1, $min_price));
				$to = wc_price($product->$get_price_method(1, $max_price));
				$display_price = sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), $from, $to);
			}
			$price .= $display_price . $product->get_price_suffix();
		}
		return $price;
	}

	/**
	 * Processes an array of Currency => Price values, ensuring that they contain
	 * valid data, and returns the sanitised array.
	 *
	 * @param array currency_prices An array of Currency => Price pairs.
	 * @return array
	 */
	public function sanitise_currency_prices($currency_prices) {
		if(!is_array($currency_prices)) {
			return array();
		}

		$result = array();
		foreach($currency_prices as $currency => $price) {
			// To be valid, the Currency must have been enabled in the configuration
			if(!WC_Aelia_CurrencySwitcher::settings()->is_currency_enabled($currency)) {
				continue;
			}

			// To be valid, the Currency must be a number
			if(!is_numeric($price)) {
				continue;
			}

			$result[$currency] = $price;
		}

		return $result;
	}

	/**
	 * Convenience method. Returns an array of the Enabled Currencies.
	 *
	 * @param bool include_base currency Indicates if the base currency should be
	 * included in the result.
	 * @return array
	 */
	public function enabled_currencies($include_base_currency = true) {
		$enabled_currencies = WC_Aelia_CurrencySwitcher::settings()->get_enabled_currencies();
		if(($include_base_currency == false) &&
			 ($key = array_search($this->base_currency(), $enabled_currencies)) !== false) {
			unset($enabled_currencies[$key]);
		}
		return $enabled_currencies;
 	}

	/**
	 * Returns an array of Currency => Price values containing the Currency Prices
	 * of the specified type (e.g. Regular, Sale, etc).
	 *
	 * @param int post_id The ID of the Post (product).
	 * @param string prices_type The type of prices to return.
	 * @return array
	 */
	public function get_product_currency_prices($post_id, $prices_type) {
		$result = json_decode(get_post_meta($post_id, $prices_type, true), true);

		if(!is_array($result)) {
			$result = array();
		}

		$prices_type_field_map = array(
			self::FIELD_REGULAR_CURRENCY_PRICES => '_regular_price',
			self::FIELD_VARIABLE_REGULAR_CURRENCY_PRICES => '_regular_price',
			self::FIELD_SALE_CURRENCY_PRICES => '_sale_price',
			self::FIELD_VARIABLE_SALE_CURRENCY_PRICES => '_sale_price',
		);
		$prices_type_field_map = apply_filters('wc_aelia_currencyswitcher_prices_type_field_map', $prices_type_field_map);

		// If a price in base currency was not loaded from the metadata added by the
		// Currency Switcher, take the one from the product metadata
		if(!isset($result[$this->base_currency()]) &&
			 isset($prices_type_field_map[$prices_type])) {
			$result[$this->base_currency()] = get_post_meta($post_id, $prices_type_field_map[$prices_type], true);
		}

		$result = apply_filters('wc_aelia_currencyswitcher_product_currency_prices', $result, $post_id, $prices_type);
		return $result;
	}

	/**
	 * Returns an array of Currency => Price values containing the Regular
	 * Currency Prices a Product.
	 *
	 * @param int post_id The ID of the Post (product).
	 * @return array
	 */
	public function get_product_regular_prices($post_id) {
		$prices =  $this->get_product_currency_prices($post_id,
																									self::FIELD_REGULAR_CURRENCY_PRICES);
		return $prices;
	}

	/**
	 * Returns an array of Currency => Price values containing the Sale Currency
	 * Prices a Product.
	 *
	 * @param int post_id The ID of the Post (product).
	 * @return array
	 */
	public function get_product_sale_prices($post_id) {
		$prices = $this->get_product_currency_prices($post_id,
																								 self::FIELD_SALE_CURRENCY_PRICES);
		return $prices;
	}

	/**
	 * Returns an array of Currency => Price values containing the Regular
	 * Currency Prices a Product Variation.
	 *
	 * @param int post_id The ID of the Post (product).
	 * @return array
	 */
	public function get_variation_regular_prices($post_id) {
		$prices = $this->get_product_currency_prices($post_id,
																								 self::FIELD_VARIABLE_REGULAR_CURRENCY_PRICES);
		return $prices;
	}

	/**
	 * Returns an array of Currency => Price values containing the Sale Currency
	 * Prices a Product Variation.
	 *
	 * @param int post_id The ID of the Post (product).
	 * @return array
	 */
	public function get_variation_sale_prices($post_id) {
		$prices = $this->get_product_currency_prices($post_id,
																								 self::FIELD_VARIABLE_SALE_CURRENCY_PRICES);
		return $prices;
	}

	/**
	 * Returns the base currency associated to a product. Prices in such currency
	 * will be used to calculate the prices in other currencies (unless they have
	 * been entered explicitly).
	 *
	 * @param int post_id The product ID.
	 * @return string
	 */
	public function get_product_base_currency($post_id) {
		$result = get_post_meta($post_id, self::FIELD_PRODUCT_BASE_CURRENCY, true);
		if(empty($result) || !$this->currencyswitcher()->is_valid_currency($result)) {
			$result = $this->base_currency();
		}
		return apply_filters('wc_aelia_currencyswitcher_product_base_currency', $result, $post_id);
	}

	/**
	 * Loads (includes) a View file.
	 *
	 * @param string view_file_name The name of the view file to include.
	 */
	private function load_view($view_file_name) {
		$file_to_load = $this->admin_views_path . '/' . $view_file_name;
		$file_to_load = apply_filters('wc_aelia_currencyswitcher_product_pricing_view_load', $file_to_load);

		if(!empty($file_to_load) && is_readable($file_to_load)) {
			include($file_to_load);
		}
	}

	/**
	 * Sets the hooks required by the class.
	 */
	private function set_hooks() {
		// Hooks for simple, external and grouped products
		add_action('woocommerce_product_options_pricing', array($this, 'woocommerce_product_options_pricing'));
		add_action('woocommerce_process_product_meta_simple', array($this, 'process_product_meta'));
		add_action('woocommerce_process_product_meta_external', array($this, 'process_product_meta'));

		// Hooks for variable products
		add_action('woocommerce_product_after_variable_attributes', array($this, 'woocommerce_product_after_variable_attributes'), 5, 3);
		add_action('woocommerce_process_product_meta_variable', array($this, 'woocommerce_process_product_meta_variable'));

		// Hooks for grouped products
		add_action('woocommerce_process_product_meta_grouped', array($this, 'process_product_meta'));
		add_action('woocommerce_grouped_price_html', array($this, 'woocommerce_grouped_price_html'), 10, 2);

		// WooCommerce 2.1
		add_filter('woocommerce_get_variation_regular_price', array($this, 'woocommerce_get_variation_regular_price'), 20, 4);
		add_filter('woocommerce_get_variation_sale_price', array($this, 'woocommerce_get_variation_sale_price'), 20, 4);
		add_filter('woocommerce_get_variation_price', array($this, 'woocommerce_get_variation_price'), 20, 4);

		// WooCommerce 2.3+
		add_filter('woocommerce_product_is_on_sale', array($this, 'woocommerce_product_is_on_sale'), 7, 2);

		// Bulk edit of variations
		add_action('woocommerce_variable_product_bulk_edit_actions', array($this, 'woocommerce_variable_product_bulk_edit_actions'));
		// Bulk edit of products
		// @since 4.3.5.160610
		add_action('woocommerce_product_bulk_edit_save', array($this, 'woocommerce_product_bulk_edit_save'), 10, 1);

		// Filters for 3rd party integration
		add_filter('wc_aelia_cs_product_base_currency', array($this, 'wc_aelia_cs_product_base_currency'), 10, 2);

		// WooCommerce 2.4+
		// @since 4.1.3.150730
		add_action('woocommerce_ajax_save_product_variations', array($this, 'woocommerce_ajax_save_product_variations'));
		add_action('woocommerce_bulk_edit_variations', array($this, 'woocommerce_bulk_edit_variations'), 10, 4);

		// Transient keys
		add_filter('woocommerce_get_variation_prices_hash', array($this, 'woocommerce_get_variation_prices_hash'), 10, 3);
		add_filter('woocommerce_variable_children_args', array($this, 'woocommerce_variable_children_args'), 5, 3);

		// Ensure that the variation prices are the ones in the correct currency.
		// These filters fix the issue caused by the new price caching logic introduced
		// in WooCommerce 2.4.7, which further complicates things (unnecessarily)
		// @since 2.4.7
		add_filter('woocommerce_variation_prices_price', array($this, 'woocommerce_variation_prices_price'), 5, 3);
		add_filter('woocommerce_variation_prices_regular_price', array($this, 'woocommerce_variation_prices_regular_price'), 5, 3);
		add_filter('woocommerce_variation_prices_sale_price', array($this, 'woocommerce_variation_prices_sale_price'), 5, 3);

		// Coupons
		add_action('woocommerce_coupon_options_save', array($this, 'woocommerce_coupon_options_save'), 10, 1);

		if(WC_Aelia_CurrencySwitcher::is_frontend()) {
			// Add filters to convert product prices, based on selected currency
			add_filter('woocommerce_get_price', array($this, 'woocommerce_get_price'), 5, 2);
			add_filter('woocommerce_get_regular_price', array($this, 'woocommerce_get_regular_price'), 5, 2);
			add_filter('woocommerce_get_sale_price', array($this, 'woocommerce_get_sale_price'), 5, 2);
		}

		// Coupon hooks
		$this->set_coupon_hooks();

		// Shipping hooks
		// @since 4.4.21.170830
		$this->set_shipping_methods_hooks();

		// Order hooks
		// @since x.x
		$this->set_order_hooks();
	}

	/**
	 * Sets hooks related to discount coupons.
	 *
	 * @since 4.4.1.170108
	 */
	protected function set_coupon_hooks() {
		add_action('woocommerce_coupon_loaded', array($this, 'woocommerce_coupon_loaded'));
	}

	/**
	 * Sets hooks related to shipping methods.
	 *
	 * @since 4.4.21.170830
	 */
	protected function set_shipping_methods_hooks() {
		add_filter('woocommerce_package_rates', array($this, 'woocommerce_package_rates'));
	}

	/**
	 * Sets hooks related to orders.
	 *
	 * @since x.x
	 */
	protected function set_order_hooks() {
		// Add hooks to handle Order totals in base currency
		add_filter('update_post_metadata', array($this, 'update_post_metadata'), 1, 4);
		add_action('woocommerce_resume_order', array($this, 'woocommerce_resume_order'), 10, 1);
	}

	/**
	 * Processes a coupon before it's passed to WooCommerce. Used to convert fixed
	 * amount coupons into the selected Currency.
	 *
	 * @param WC_Coupon coupon The coupon to process.
	 * @return WC_Coupon
	 * @since 4.4.1.170108
	 */
	public function woocommerce_coupon_loaded($coupon) {
		$this->set_coupon_amounts($coupon);
		return $coupon;
	}

	/**
	 * Processes shipping methods before they are used by WooCommerce. Used to
	 * convert shipping costs into the selected Currency.
	 *
	 * @param array An array of WC_Shipping_Method classes.
	 * @return array An array of WC_Shipping_Method classes, with their costs
	 * converted into Currency.
	 * @since 4.4.21.170830
	 */
	public function woocommerce_package_rates($available_shipping_methods) {
		$selected_currency = $this->get_selected_currency();
		$base_currency = $this->base_currency();

		foreach($available_shipping_methods as $shipping_method) {
			if(!empty($shipping_method->shipping_prices_in_currency)) {
				continue;
			}

			// Convert shipping cost
			if(!is_array($shipping_method->cost)) {
				// Convert a simple total cost into currency
				$shipping_method->cost = $this->currencyswitcher()->convert($shipping_method->cost,
																																		$base_currency,
																																		$selected_currency);
			}
			else {
				// Based on documentation, class can contain an array of costs in case
				// of shipping costs applied per item. In such case, each one has to
				// be converted
				foreach($shipping_method->cost as $cost_key => $cost_value) {
					$shipping_method->cost[$cost_key] = $this->currencyswitcher()->convert($cost_value,
																																								 $base_currency,
																																								 $selected_currency);
				}
			}

			// Convert shipping taxes
			if(!is_array($shipping_method->taxes)) {
				// Convert a simple total taxes into currency
				$shipping_method->taxes = $this->currencyswitcher()->convert($shipping_method->taxes,
																																		 $base_currency,
																																		 $selected_currency);
			}
			else {
				// Based on documentation, class can contain an array of taxes in case
				// of shipping taxes applied per item. In such case, each one has to
				// be converted
				foreach($shipping_method->taxes as $taxes_key => $taxes_value) {
					$shipping_method->taxes[$taxes_key] = $this->currencyswitcher()->convert($taxes_value,
																																									 $base_currency,
																																									 $selected_currency);
				}
			}

			// Flag the shipping method to keep track of the fact that its costs have
			// been converted into selected Currency. This is necessary because this
			// is often called multiple times within the same page load, passing the
			// same data that was already processed
			$shipping_method->shipping_prices_in_currency = true;
		}

		return $available_shipping_methods;
	}

	/**
	 * Returns the method to be used to convert the prices of a product. The
	 * method depends on the class of the product instance.
	 *
	 * @param WC_Product product An instance of a product.
	 * @return string|null The method to use to process the product, or null if
	 * product type is unsupported.
	 */
	protected function get_convert_callback(WC_Product $product) {
		$method_keys = array(
			'WC_Product' => 'legacy',
			'WC_Product_Simple' => 'simple',
			'WC_Product_Variable' => 'variable',
			'WC_Product_Variation' => 'variation',
			'WC_Product_External' => 'external',
			'WC_Product_Grouped' => 'grouped',
		);

		$product_class = get_class($product);
		$method_key = isset($method_keys[$product_class]) ? $method_keys[$product_class] : '';
		// Determine the method that will be used to convert the product prices
		$convert_method = 'convert_' . $method_key . '_product_prices';
		$convert_callback = method_exists($this, $convert_method) ? array($this, $convert_method) : null;

		// Allow external classes to alter the callback, if needed
		$convert_callback = apply_filters('wc_aelia_currencyswitcher_product_convert_callback', $convert_callback, $product);
		if(!is_callable($convert_callback)) {
			$this->logger()->info(
				__('Attempted to convert an unsupported product object. This usually happens when a ' .
					 '3rd party plugin adds custom product types, of which the Currency Switcher is ' .
					 'not aware. Product prices will not be converted. Please report the issue to ' .
					 'support as a compatibility request'),
				array(
					'Product Class' => $product_class,
					'Product Type' => $product->product_type,
				));
		}
		return $convert_callback;
	}

	/**
	 * Indicates if the product is on sale. A product is considered on sale if:
	 * - Its "sale end date" is empty, or later than today.
	 * - Its sale price in the active currency is lower than its regular price.
	 *
	 * @param WC_Product product The product to check.
	 * @return bool
	 */
	protected function product_is_on_sale(WC_Product $product) {
		$sale_price_dates_from = $product->sale_price_dates_from;
		$sale_price_dates_to = $product->sale_price_dates_to;

		$is_on_sale = false;
		$today = date('Ymd');
		if((empty($sale_price_dates_from) ||
				$today >= date('Ymd', $sale_price_dates_from)) &&
			 (empty($sale_price_dates_to) ||
				date('Ymd', $sale_price_dates_to) > $today)) {
			$sale_price = $product->get_sale_price();
			$is_on_sale = is_numeric($sale_price) && ($sale_price < $product->get_regular_price());
		}
		return $is_on_sale;
	}

	/**
	 * Converts a product or variation prices to the specific currency, taking
	 * into account manually entered prices.
	 *
	 * @param WC_Product product The product whose prices should be converted.
	 * @param string currency A currency code.
	 * @param array product_regular_prices_in_currency An array of manually entered
	 * product prices (one for each currency).
	 * @param array product_sale_prices_in_currency An array of manually entered
	 * product prices (one for each currency).
	 * @return WC_Product
	 */
	protected function convert_to_currency(WC_Product $product, $currency,
																				 array $product_regular_prices_in_currency,
																				 array $product_sale_prices_in_currency) {
		$product_id = aelia_get_product_id($product);

		$shop_base_currency = $this->base_currency();
		$product_base_currency = $this->get_product_base_currency($product_id);

		// Take regular price in the specific product base currency
		$product_base_regular_price = isset($product_regular_prices_in_currency[$product_base_currency]) ? $product_regular_prices_in_currency[$product_base_currency] : null;

		// If a regular price was not entered for the selected product base currency,
		// take the one in shop base currency
		if(!is_numeric($product_base_regular_price)) {
			$product_base_regular_price = isset($product_regular_prices_in_currency[$shop_base_currency]) ? $product_regular_prices_in_currency[$shop_base_currency] : null;

			// If a product doesn't have a price in the product-specific base currency,
			// then that base currency is not valid. In such case, shop's base currency
			// should be used instead
			$product_base_currency = $shop_base_currency;
		}

		// Take sale price in the specific product base currency
		$product_base_sale_price = isset($product_sale_prices_in_currency[$product_base_currency]) ? $product_sale_prices_in_currency[$product_base_currency] : null;

		// If a sale price was not entered for the selected product base currency,
		// take the one in shop base currency
		if(!is_numeric($product_base_sale_price)) {
			$product_base_sale_price = isset($product_sale_prices_in_currency[$shop_base_currency]) ? $product_sale_prices_in_currency[$shop_base_currency] : null;
		}

		$product->regular_price = isset($product_regular_prices_in_currency[$currency]) ? $product_regular_prices_in_currency[$currency] : null;
		if(($currency != $product_base_currency) && !is_numeric($product->regular_price)) {
			$product->regular_price = $this->convert_product_price_from_base($product_base_regular_price, $currency, $product_base_currency, $product, 'regular_price');
		}

		$product->sale_price = isset($product_sale_prices_in_currency[$currency]) ? $product_sale_prices_in_currency[$currency] : null;
		if(($currency != $product_base_currency) && !is_numeric($product->sale_price)) {
			$product->sale_price = $this->convert_product_price_from_base($product_base_sale_price, $currency, $product_base_currency, $product, 'sale_price');
		}

		// Debug
		//var_dump(
		//	"PRODUCT CLASS: " . get_class($product),
		//	"PRODUCT ID: {$product_id}",
		//	"BASE CURRENCY $product_base_currency",
		//	$product_regular_prices_in_currency,
		//	$product->regular_price,
		//	$product->sale_price
		//);

		if($this->product_is_on_sale($product) &&
			 is_numeric($product->sale_price)) {
			$product->price = $product->sale_price;
		}
		else {
			$product->price = $product->regular_price;
		}
		return $product;
	}

	/**
	 * Convert the prices of a product in the destination currency.
	 *
	 * @param WC_Product product A product (simple, variable, variation).
	 * @param string currency A currency code.
	 * @return WC_Product The product with converted prices.
	 */
	public function convert_product_prices(WC_Product $product, $currency) {
		// If the product is already in the target currency, return it as it is
		if(!empty($product->currency) || ($product->currency === $currency)) {
			return $product;
		}

		// Since WooCommerce 2.1, this method can be triggered recursively due to
		// a (not so wise) change in WC architecture. It's therefore necessary to keep
		// track of when the conversion started, to prevent infinite recursion
		if(!empty($product->aelia_cs_conversion_in_progress)) {
			return $product;
		}

		// If product has a "currencyswitcher_original_product" attribute, it means
		// that it was already processed by the Currency Switcher. In such case, it
		// has to be reverted to the original status before being processed again
		if(!empty($product->currencyswitcher_original_product)) {
			$product = $product->currencyswitcher_original_product;
		}
		// Take a copy of the original product before processing
		$original_product = clone $product;

		// Flag the product to keep track that conversion is in progress
		$product->aelia_cs_conversion_in_progress = true;

		// Get the method to use to process the product
		$convert_callback = $this->get_convert_callback($product);
		if(!empty($convert_callback) && is_callable($convert_callback)) {
			// Invoke the callback directly, rather than using call_user_func(), for
			// better performance
			// @since 4.2.11.151028
			if(is_array($convert_callback)) {
				$object = array_shift($convert_callback);
				$method = array_shift($convert_callback);
				$product = $object->$method($product, $currency);
			}
			else {
				$product = $convert_callback($product, $currency);
			}
			//$product = call_user_func($convert_callback, $product, $currency);
		}
		else {
			// If no conversion function is found, use the generic one
			$product = $this->convert_generic_product_prices($product, $currency);
		}

		// Assign the original product to the processed one
		$product->currencyswitcher_original_product = $original_product;
		// Remove "conversion is in progress" flag from the original product, in case
		// it was left there
		if(!empty($product->currencyswitcher_original_product->aelia_cs_conversion_in_progress)) {
			unset($product->currencyswitcher_original_product->aelia_cs_conversion_in_progress);
		}

		// Tag the product as being already in the selected currency
		$product->currency = $currency;

		// Remove "conversion is in progress" flag when the operation is complete
		unset($product->aelia_cs_conversion_in_progress);

		return $product;
	}

	/**
	 * Converts the prices of a variable product to the specified currency.
	 *
	 * @param WC_Product_Variable product A variable product.
	 * @param string currency A currency code.
	 * @return WC_Product_Variable The product with converted prices.
	 */
	// TODO Reove method, as it might no longer be required
	public function convert_variable_product_prices(WC_Product $product, $currency) {
		$product_children = $product->get_children();
		if(empty($product_children)) {
			return $product;
		}

		$variation_regular_prices = array();
		$variation_sale_prices = array();
		$variation_prices = array();

		foreach($product_children as $variation_id) {
			$variation = $this->load_variation_in_currency($variation_id, $currency);
			if(empty($variation)) {
				continue;
			}

			$variation_regular_prices[$variation_id] = $variation->regular_price;
			$variation_sale_prices[$variation_id] = $variation->sale_price;
			$variation_prices[$variation_id] = $variation->price;
		}

		$product->min_variation_regular_price = $this->get_min_value($variation_regular_prices);
		$product->max_variation_regular_price = $this->get_max_value($variation_regular_prices);

		$product->min_variation_sale_price = $this->get_min_value($variation_sale_prices);
		$product->max_variation_sale_price = $this->get_max_value($variation_sale_prices);


		$product->min_variation_price = $this->get_min_value($variation_prices);
		$product->max_variation_price = $this->get_max_value($variation_prices);

		// Keep track of the variation IDs from which the minimum and maximum prices
		// were taken
		$product->min_regular_price_variation_id = array_search($product->min_variation_regular_price, $variation_regular_prices);
		$product->max_regular_price_variation_id = array_search($product->max_variation_regular_price, $variation_regular_prices);

		$product->min_sale_price_variation_id = array_search($product->min_variation_sale_price, $variation_sale_prices);
		$product->max_sale_price_variation_id = array_search($product->max_variation_sale_price, $variation_sale_prices);

		$product->min_price_variation_id = array_search($product->min_variation_price, $variation_prices);
		$product->max_price_variation_id = array_search($product->max_variation_price, $variation_prices);

		$product->regular_price = $product->min_variation_regular_price;
		$product->sale_price = $product->min_variation_price;
		$product->price = $product->min_variation_price;

		return $product;
	}

	/**
	 * Converts the product prices of a variation.
	 *
	 * @param WC_Product_Variation $product A product variation.
	 * @param string currency A currency code.
	 * @return WC_Product_Variation The variation with converted prices.
	 */
	public function convert_variation_product_prices(WC_Product_Variation $product, $currency) {
		$product_id = aelia_get_product_id($product);
		$product = $this->convert_to_currency($product,
																					$currency,
																					$this->get_variation_regular_prices($product_id),
																					$this->get_variation_sale_prices($product_id));

		return $product;
	}

	/**
	 * Given a Variation ID, it loads the variation and returns it, with its
	 * prices converted into the specified currency.
	 *
	 * @param int variation_id The ID of the variation.
	 * @param string currency A currency code.
	 * @return WC_Product_Variation
	 */
	// TODO Reove method, as it might no longer be required
	public function load_variation_in_currency($variation_id, $currency) {
		try {
			$variation = new WC_Product_Variation($variation_id);
		}
		catch(\Exception $e) {
			$variation = null;
			$this->logger()->error(
				__('Exception occurred while loading variation in currency. Variation ' .
					 'will be ignored.', self::$text_domain),
				array(
					'Variation ID' => $variation_id,
					'Error Message' => $e->getMessage(),
				));
		}

		if(empty($variation)) {
			return false;
		}

		$variation = $this->convert_variation_product_prices($variation, $currency);
		return $variation;
	}

	/**
	 * Converts the prices of a generic product to the specified currency. This
	 * method is a fallback, in case no specific conversion function was found by
	 * the pricing manager.
	 *
	 * @param WC_Product product A simple product.
	 * @param string currency A currency code.
	 * @return WC_Product The simple product with converted prices.
	 */
	public function convert_generic_product_prices(WC_Product $product, $currency) {
		return $this->convert_simple_product_prices($product, $currency);
	}

	/**
	 * Converts the prices of a simple product to the specified currency.
	 *
	 * @param WC_Product product A simple product.
	 * @param string currency A currency code.
	 * @return WC_Product_Variable The simple product with converted prices.
	 */
	public function convert_simple_product_prices(WC_Product $product, $currency) {
		$product_id = aelia_get_product_id($product);
		$product = $this->convert_to_currency($product,
																					$currency,
																					$this->get_product_regular_prices($product_id),
																					$this->get_product_sale_prices($product_id));

		return $product;
	}

	/**
	 * Converts the prices of an external product to the specified currency.
	 *
	 * @param WC_Product_External product An external product.
	 * @param string currency A currency code.
	 * @return WC_Product_Variable The external product with converted prices.
	 */
	public function convert_external_product_prices(WC_Product_External $product, $currency) {
		return $this->convert_simple_product_prices($product, $currency);
	}

	/**
	 * Converts the prices of a grouped product to the specified currency.
	 *
	 * @param WC_Product_Grouped product A grouped product.
	 * @param string currency A currency code.
	 * @return WC_Product_Grouped
	 */
	public function convert_grouped_product_prices(WC_Product_Grouped $product, $currency) {
		// Grouped products don't have a price. Prices can be found in child products
		// which belong to the grouped product. Such child products are processed
		// independently, therefore no further action is needed
		return $product;
	}

	/**
	 * Checks that the price type specified is "min" or "max".
	 *
	 * @param string price_type The price type.
	 * @return bool
	 */
	protected function is_min_max_price_type_valid($price_type) {
		$valid_price_types = array(
			'min',
			'max'
		);

		return in_array($price_type, $valid_price_types);
	}

	/**
	 * Process a variation price, recalculating it depending if it already
	 * includes taxes and/or if prices should be displayed with our without taxes.
	 *
	 * @param string price The product price passed by WooCommerce.
	 * @param WC_Product product The product for which the price is being retrieved.
	 * @param string min_or_max The type of price to retrieve. It can be 'min' or 'max'.
	 * @param boolean display Whether the value is going to be displayed
	 * @return float
	 * @since 3.2
	 */
	public function process_product_price_tax($product, $price) {
		$tax_display_mode = get_option('woocommerce_tax_display_shop');
		if($tax_display_mode == 'incl') {
			$price = $product->get_price_including_tax(1, $price);
		}
		else {
			$price = $product->get_price_excluding_tax(1, $price);
		}

		return $price;
	}

	/**
	 * Process a variation price, recalculating it depending if it already
	 * includes taxes and/or if prices should be displayed with our without taxes.
	 *
	 * @param string price The product price passed by WooCommerce.
	 * @param WC_Product product The product for which the price is being retrieved.
	 * @param string min_or_max The type of price to retrieve. It can be 'min' or 'max'.
	 * @param boolean display Whether the value is going to be displayed
	 * @return float
	 * @since 3.2
	 */
	public function process_variation_price_tax($price, $product, $min_or_max, $display) {
		if($display) {
			$field_name = $min_or_max . '_price_variation_id';
			$variation_id = $product->$field_name;
			$variation = $product->get_child($variation_id);

			if(!is_object($variation)) {
				return $price;
			}

			$tax_display_mode = get_option('woocommerce_tax_display_shop');
			if($tax_display_mode == 'incl') {
				$price = $variation->get_price_including_tax(1, $price);
			}
			else {
				$price = $variation->get_price_excluding_tax(1, $price);
			}
		}
		return $price;
	}

	/**
	 * Get the minimum or maximum variation regular price.
	 *
	 * @param string price The product price passed by WooCommerce.
	 * @param WC_Product product The product for which the price is being retrieved.
	 * @param string min_or_max The type of price to retrieve. It can be 'min' or 'max'.
	 * @param boolean display Whether the value is going to be displayed
	 * @return float
	 */
	public function woocommerce_get_variation_regular_price($price, $product, $min_or_max, $display) {
		// If we are in the backend, no conversion takes place, therefore we can return
		// the original value, in base currency
		if(is_admin() && !WC_Aelia_CurrencySwitcher::doing_ajax()) {
			return $price;
		}

		if(!$this->is_min_max_price_type_valid($min_or_max)) {
			trigger_error(sprintf(__('Invalid variation regular price type specified: "%s".'),
														$min_or_max),
										E_USER_WARNING);
			return $price;
		}

		// Retrieve the price in the selected currency
		$price_property = $min_or_max . '_variation_regular_price';
		// Process the price, recalculating it depending if it already includes tax or not
		$price = $this->process_variation_price_tax($product->$price_property, $product, $min_or_max, $display);

		return $price;
	}

	/**
	 * Get the minimum or maximum variation sale price.
	 *
	 * @param string price The product price passed by WooCommerce.
	 * @param WC_Product product The product for which the price is being retrieved.
	 * @param string min_or_max The type of price to retrieve. It can be 'min' or 'max'.
	 * @param boolean display Whether the value is going to be displayed
	 * @return float
	 */
	public function woocommerce_get_variation_sale_price($price, $product, $min_or_max, $display) {
		// If we are in the backend, no conversion takes place, therefore we can return
		// the original value, in base currency
		if(is_admin() && !WC_Aelia_CurrencySwitcher::doing_ajax()) {
			return $price;
		}

		if(!$this->is_min_max_price_type_valid($min_or_max)) {
			trigger_error(sprintf(__('Invalid variation sale price type specified: "%s".'),
														$min_or_max),
										E_USER_WARNING);
			return $price;
		}

		// Retrieve the price in the selected currency
		$sale_price_property = $min_or_max . '_variation_sale_price';
		// Process the price, recalculating it depending if it already includes tax or not
		$sale_price = $this->process_variation_price_tax($product->$sale_price_property, $product, $min_or_max, $display);

		return $sale_price;
	}

	/**
	 * Get the minimum or maximum variation price.
	 *
	 * @param string price The product price passed by WooCommerce.
	 * @param WC_Product product The product for which the price is being retrieved.
	 * @param string min_or_max The type of price to retrieve. It can be 'min' or 'max'.
	 * @param boolean display Whether the value is going to be displayed
	 * @return float
	 */
	public function woocommerce_get_variation_price($price, $product, $min_or_max, $display) {
		// If we are in the backend, no conversion takes place, therefore we can return
		// the original value, in base currency
		if(is_admin() && !WC_Aelia_CurrencySwitcher::doing_ajax() || !is_object($product)) {
			return $price;
		}

		if(!$this->is_min_max_price_type_valid($min_or_max)) {
			trigger_error(sprintf(__('Invalid variation sale price type specified: "%s".'),
														$min_or_max),
										E_USER_WARNING);
			return $price;
		}

		// Retrieve the price in the selected currency
		$price_property = $min_or_max . '_variation_price';

		//var_dump($price, $product->min_price_variation_id);die();
		// Process the price, recalculating it depending if it already includes tax or not
		$price = $this->process_variation_price_tax($product->$price_property, $product, $min_or_max, $display);

		return $price;
	}

	/**
	 * Indicates if a product is on sale. The method takes into account the product
	 * prices in each currency.
	 *
	 * @param bool is_on_sale The original value passed by WooCommerce.
	 * @param WC_Product product The product to check.
	 * @return bool
	 * @since 3.7.22.140227
	 */
	public function woocommerce_product_is_on_sale($is_on_sale, $product) {
		return $this->product_is_on_sale($product);
	}

	/**
	 * Alters the bulk edit actions for the current product.
	 */
	public function woocommerce_variable_product_bulk_edit_actions() {
		$enabled_currencies = $this->enabled_currencies();
		if(empty($enabled_currencies)) {
			return;
		}

		$text_domain = self::$text_domain;
		echo '<optgroup label="' . __('Currency prices', $text_domain) . '">';
		foreach($enabled_currencies as $currency) {
			// No need to add an option for the base currency, it already exists in standard WooCommerce menu
			if($currency == $this->base_currency()) {
				continue;
			}

			// Display entry for variation's regular prices
			echo "<option value=\"variable_regular_currency_prices_{$currency}\" currency=\"{$currency}\">";
			printf(__('Regular prices (%s)', $text_domain),
						 $currency);
			echo '</option>';

			// Display entry for variation's sale prices
			echo "<option value=\"variable_sale_currency_prices_{$currency}\"  currency=\"{$currency}\">";
			printf(__('Sale prices (%s)', $text_domain),
						 $currency);
			echo '</option>';
		}
		echo '</optgroup>';
	}

	/**
	 * Indicates if a product can be bulk-edited. Normally, only simple and
	 * external products can be bulk-edited.
	 *
	 * @param WC_Product product The product to check.
	 * @return bool
	 * @since 4.3.5.160610
	 */
	protected function can_bulk_edit_prices($product) {
		$change_price_product_types = apply_filters('woocommerce_bulk_edit_save_price_product_types', array('simple', 'external'));
		$can_product_type_change_price = false;
		foreach($change_price_product_types as $product_type) {
			if($product->is_type($product_type)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Alters all the product prices, in each currency, by a given percentage.
	 *
	 * @param WC_Product product The product to alter.
	 * @param string price_type The type of price to alter (regular or sale).
	 * @param float percent The percentage to apply.
	 * @param string change_type The type of change to apply.
	 * @since 4.3.5.160610
	 * @see WC_Admin_Post_Types::bulk_edit_save().
	 */
	protected function alter_all_product_prices_percent($product, $price_type, $percent, $change_type) {
		$prices_meta_key = '';
		$product_id = aelia_get_product_id($product);
		if($product->is_type('variation')) {
			$regular_prices = $this->get_variation_regular_prices($product_id);
			$sale_prices = $this->get_variation_sale_prices($product_id);

			// Determine the product meta to update
			switch($price_type) {
				case 'regular':
					$prices_meta_key = self::FIELD_VARIABLE_REGULAR_CURRENCY_PRICES;
					$product_prices_to_alter = $regular_prices;
					break;
				case 'sale':
					$prices_meta_key = self::FIELD_VARIABLE_SALE_CURRENCY_PRICES;
					$product_prices_to_alter = $sale_prices;
					break;
			}
		}
		else {
			$regular_prices = $this->get_product_regular_prices($product_id);
			$sale_prices = $this->get_product_sale_prices($product_id);

			// Determine the product meta to update
			switch($price_type) {
				case 'regular':
					$prices_meta_key = self::FIELD_REGULAR_CURRENCY_PRICES;
					$product_prices_to_alter = $regular_prices;
					break;
				case 'sale':
					$prices_meta_key = self::FIELD_SALE_CURRENCY_PRICES;
					$product_prices_to_alter = $sale_prices;
					break;
			}
		}

		// If no valid meta key could be determined, it means that the price type to
		// update was invalid. In this case, there's no action to take.
		if(empty($prices_meta_key)) {
			return;
		}

		// Initialise the array of the prices to be altered. If empty, create a new
		// array
		if(empty($product_prices_to_alter)) {
			$product_prices_to_alter = array();
		}

		// We don't need to alter the prices of shop's base currency, as they are
		// already processed by WooCommerce
		unset($product_prices_to_alter[$this->base_currency()]);

		// Debug
		//var_dump("BULK EDIT - BEFORE", $product_prices_to_alter);
		foreach($this->enabled_currencies(false) as $currency) {
			$price = isset($product_prices_to_alter[$currency]) ? $product_prices_to_alter[$currency] : '';
			$decimals = WC_Aelia_CurrencySwitcher::settings()->price_decimals($currency);

			$new_price = null;
			switch($change_type) {
				case 'plus':
					if(is_numeric($price)) {
						$new_price = $price + (round($price * $percent, $decimals));
					}
					break;
				case 'minus':
					if(is_numeric($price)) {
						$new_price = $price - (round($price * $percent, $decimals));
					}
					break;
				case 'decrease_regular_price':
					if(!empty($regular_prices[$currency]) && is_numeric($regular_prices[$currency])) {
						$new_price = max(0, $regular_prices[$currency] - ($regular_prices[$currency] * $percent ));
					}
					break;
			}

			if(is_numeric($new_price)) {
				$product_prices_to_alter[$currency] = $new_price;
			}
		}
		// Debug
		//var_dump("BULK EDIT - AFTER", $product_prices_to_alter);die();

		update_post_meta($product_id, $prices_meta_key, json_encode($product_prices_to_alter));
	}

	/**
	 * Updated a product after a bulk edit.
	 *
	 * @param WC_Product product The product to edit.
	 * @since 4.3.5.160610
	 */
	public function woocommerce_product_bulk_edit_save($product) {
		// If product cannot be bulk-edited, just skip it
		if(!$this->can_bulk_edit_prices($product)) {
			return;
		}

		// Update regular prices in currency
		if(!empty($_REQUEST['change_regular_price'])) {
			$regular_price = esc_attr(stripslashes($_REQUEST['_regular_price']));
			if(strstr($regular_price, '%')) {
				$percent = str_replace('%', '', $regular_price) / 100;
				$change_regular_price = absint($_REQUEST['change_regular_price']);

				switch($change_regular_price) {
					case 1:
						// Not supported
						break;
					case 2:
						$change_type = 'plus';
						break;
					case 3:
						$change_type = 'minus';
						break;
				}

				$this->alter_all_product_prices_percent($product, 'regular', $percent, $change_type);
			}
		}

		// Update sale prices in currency
		if(!empty($_REQUEST['change_sale_price'])) {
			$sale_price = esc_attr(stripslashes($_REQUEST['_sale_price']));
			if(strstr($sale_price, '%')) {
				$percent = str_replace('%', '', $sale_price) / 100;
				$change_sale_price = absint($_REQUEST['change_sale_price']);

				switch($change_sale_price) {
					case 1:
						// Not supported
						break;
					case 2:
						$change_type = 'plus';
						break;
					case 3:
						$change_type = 'minus';
						break;
					case 4:
						$change_type = 'decrease_regular_price';
						break;
				}

				if(!empty($change_type)) {
					$this->alter_all_product_prices_percent($product, 'sale', $percent, $change_type);
				}
			}
		}
	}

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->admin_views_path = $this->currencyswitcher()->path('views') . '/admin/wc20';

		$this->set_hooks();
	}

	/**
	 * Returns the singleton instance of the prices manager.
	 *
	 * @return WC_Aelia_CurrencyPrices_Manager
	 */
	public static function instance() {
		if(empty(self::$instance)) {
			self::$instance = new static();
		}
		return self::$instance;
	}

	/**
	 * Filter for wc_aelia_cs_product_base_currency hook. Returns the base currency
	 * associated to a product.
	 *
	 * @param string product_base_currency The currency passed when the filter was
	 * called.
	 * @return string The product base currency, or shop's base currency if none
	 * was assigned to the product.
	 * @since 4.1.4.150810
	 */
	public function wc_aelia_cs_product_base_currency($product_base_currency, $product_id) {
		return $this->get_product_base_currency($product_id);
	}

	/**
	 * Alters the transient key to retrieve the prices of variable products,
	 * ensuring that the currency is taken into account.
	 *
	 * @param array cache_key_args The arguments that form the cache key.
	 * @param WC_Product product The product for which the key is being generated.
	 * @param bool display Indicates if the prices are being retrieved for display
	 * purposes.
	 * @return array
	 * @since WC 2.4
	 * @since 4.1.4.150810
	 */
	public function woocommerce_get_variation_prices_hash($cache_key_args, $product, $display) {
		// Debug
		//$cache_key = 'wc_var_prices' . md5(json_encode($cache_key_args));
		//delete_transient($cache_key);

		// Add transient version to prices hash. When the version changes, the hash
		// will change as it should
		// @since 4.4.0.161221
		$cache_key_args[] = WC_Cache_Helper::get_transient_version('product');

		$cache_key_args[] = get_woocommerce_currency();
		return $cache_key_args;
	}

	/**
	 * Ensures that the price of a variation being stored in the cache is the one
	 * in the active currency.
	 *
	 * WHY
	 * In WooCommerce 2.4.7, the already convoluted price caching logic has been
	 * made more complicated. The latest implementation loads variation prices
	 * directly from the database (bad idea), skipping all filters that are
	 * associated to prices. This causes the wrong prices to be loaded, as any
	 * calculation is skipped. This filter fixes the issue, by replacing the wrong
	 * prices with the correct ones.
	 *
	 * @param float price The original variation price, retrieved by WC from the
	 * database.
	 * @param WC_Product_Variation variation The variation for which the price is retrieved.
	 * @param WC_Product_Variable The parent product to which the variation belongs.
	 * @return float The variation price, in the active currency.
	 *
	 * @since 4.2.7.150914
	 * @since WC 2.4.7
	 */
	public function woocommerce_variation_prices_price($price, $variation, $parent_product) {
		return $variation->get_price();
	}

	/**
	 * Ensures that the regular price of a variation being stored in the cache is
	 * the one in the active currency.
	 *
	 * @param float price The original variation price, retrieved by WC from the
	 * database.
	 * @param WC_Product_Variation variation The variation for which the price is retrieved.
	 * @param WC_Product_Variable The parent product to which the variation belongs.
	 * @return float The variation price, in the active currency.
	 *
	 * @see WC_Aelia_CurrencyPrices_Manager::woocommerce_variation_prices_price()
	 * @since 4.2.7.150914
	 * @since WC 2.4.7
	 */
	public function woocommerce_variation_prices_regular_price($price, $variation, $parent_product) {
		return $variation->get_regular_price();
	}

	/**
	 * Ensures that the sale price of a variation being stored in the cache is the
	 * one in the active currency.
	 *
	 * @param float price The original variation price, retrieved by WC from the
	 * database.
	 * @param WC_Product_Variation variation The variation for which the price is retrieved.
	 * @param WC_Product_Variable The parent product to which the variation belongs.
	 * @return float The variation price, in the active currency.
	 *
	 * @see WC_Aelia_CurrencyPrices_Manager::woocommerce_variation_prices_price()
	 * @since 4.2.7.150914
	 * @since WC 2.4.7
	 */
	public function woocommerce_variation_prices_sale_price($price, $variation, $parent_product) {
		return $variation->get_sale_price();
	}

	/**
	 * WooCommerce 2.4+
	 * Alters the query used to retrieve the children of a variable product,
	 * removing the filters for "_price" meta.
	 *
	 * @param array args The original arguments used for the query.
	 * @param WC_Product The variable product.
	 * @param bool visible_only Indicates if only the visible (purchasable) child
	 * products should be retrieved.
	 * @return array The modified array of arguments.
	 * @since 3.9.8.160530
	 * @since WC 2.4.4
	 */
	public function woocommerce_variable_children_args($args, $product, $visible_only) {
		if($visible_only && !empty($args['meta_query'])) {
			foreach($args['meta_query'] as $key => $value) {
				/* Remove the filter for empty "_price" meta.
				 *
				 * WHY
				 * WooCommerce 2.4 introduced a filter to exclude products that have an
				 * empty "_price" metadata, as it assumes that such products are "not
				 * purchasable". With the Currency Switcher, even if that meta is empty,
				 * the product can still be purchasable, as its price is calculated on
				 * the fly.
				 */
				if(is_array($value) && !empty($value['key']) && ($value['key'] === '_price')) {
					unset($args['meta_query'][$key]);
				}
			}
		}
		return $args;
	}

	/**
	 * Saves the multi-currency data for a coupon.
	 *
	 * @param int coupon_id The coupon ID.
	 * @since 3.8.0.150813
	 */
	public function woocommerce_coupon_options_save($coupon_id) {
		// Debug
		//var_dump($_POST);die();
		$coupon_currency_data = isset($_POST[self::FIELD_COUPON_CURRENCY_DATA]) ? $_POST[self::FIELD_COUPON_CURRENCY_DATA] : array();
		update_post_meta($coupon_id, self::FIELD_COUPON_CURRENCY_DATA, $coupon_currency_data);
	}

	/**
	 * Replaces the amounts of a coupon with the one applicable for the active
	 * currency. The conversion takes into account values that might have been
	 * explicitly set for the active currency, and applies FX conversion for the
	 * ones set to "Auto".
	 *
	 * @param WC_Coupon coupon The coupon to process.
	 * @since 3.8.0.150813
	 */
	public function set_coupon_amounts($coupon) {
		$selected_currency = $this->get_selected_currency();
		// If we are working with base currency, do not perform any conversion (it
		// would not make any difference, anyway)
		if($selected_currency == $this->base_currency()) {
			return;
		}

		$coupon_types_to_convert = array('fixed_cart', 'fixed_product');
		$coupon_types_to_convert = apply_filters('wc_aelia_cs_coupon_types_to_convert', $coupon_types_to_convert);

		$coupon_id = aelia_get_coupon_id($coupon);
		$coupon_data = get_post_meta($coupon_id, WC_Aelia_CurrencyPrices_Manager::FIELD_COUPON_CURRENCY_DATA, true);

		$currency_data = isset($coupon_data[$selected_currency]) ? $coupon_data[$selected_currency] : array();

		/* When a different value is explicitly specified for the active currency,
		 * that will replace the coupon amount. If no value has been specified for
		 * the active currency, then a default one has to be used, as follows:
		 * - If the coupon is a "fixed price" one, then its default amount is the
		 *   original value, converted to the active currency.
		 * - If the coupon is a percentage one, then its default amount is the same
		 *   entered for the base currency.
		 *
		 * Example
		 * When active currency is EUR, the above will work as follows:
		 * - Coupon value for USD: 100 -> Value in EUR: 89.95
		 * - Coupon value for USD: 10% -> Value in EUR: still 10% (no conversion)
		 * - Coupon value for USD: 100, for EUR: 90 -> Value in EUR: 90 (i.e. explicit
		 *   coupon value takes precedence).
		 */
		if(in_array($coupon->discount_type, $coupon_types_to_convert)) {
			$default_coupon_amount = $this->convert_from_base($coupon->coupon_amount, $this->get_selected_currency());
		}
		else {
			$default_coupon_amount = $coupon->coupon_amount;
		}

		$coupon->coupon_amount = !empty($currency_data['coupon_amount']) ? $currency_data['coupon_amount'] : $default_coupon_amount;

		/* Since WooCommerce 2.3, the "amount" property, which is an alias of
		 * coupon_amount, should no longer be used. However, since we deal with
		 * 2.2 and 2.1 as well, it's a good idea to keep it up to date.
		 * @link http://docs.woothemes.com/wc-apidocs/class-WC_Coupon.html
		 */
		$coupon->amount = $coupon->coupon_amount;

		// Convert minimum amount to the selected currency
		if(isset($currency_data['minimum_amount']) && is_numeric($currency_data['minimum_amount'])) {
			$coupon->minimum_amount = $currency_data['minimum_amount'];
		}
		else{
			// If no minimum amount was explicitly specified for the active currency,
			// but there is a minimum amount for the base currency, convert that
			// value using FX rates
			$coupon->minimum_amount = $this->convert_from_base($coupon->minimum_amount, $this->get_selected_currency());
		}

		// Convert maximum amount to the selected currency
		if(isset($currency_data['maximum_amount']) && is_numeric($currency_data['maximum_amount'])) {
			$coupon->maximum_amount = $currency_data['maximum_amount'];
		}
		else{
			// If no maximum amount was explicitly specified for the active currency,
			// but there is a maximum amount for the base currency, convert that
			// value using FX rates
			$coupon->maximum_amount = $this->convert_from_base($coupon->maximum_amount, $this->get_selected_currency());
		}

		// Debug
		//var_dump($coupon); die();
	}

	/**
	 * Returns the instance of a product.
	 *
	 * @param int product_id A product ID.
	 * @return WC_Product
	 * @since 4.2.15.151214
	 */
	protected function get_product($product_id) {
		if(aelia_wc_version_is('>=', '2.2')) {
			$product = wc_get_product($product_id);
		}
		else {
			$product = get_product($product_id);
		}
		return $product;
	}

	/**
	 * Converts a product price in the currently selected currency.
	 *
	 * @param double price The original product price.
	 * @param WC_Product product The product to process.
	 * @return double The price, converted in the currency selected by the User.
	 */
	public function woocommerce_get_price($price, $product = null) {
		$product = $this->convert_product_prices($product, $this->get_selected_currency());
		return $product->price;
	}

	/**
	 * Converts a product's regular price in the currently selected currency.
	 *
	 * @param double price The original regular price.
	 * @param WC_Product product The product to process.
	 * @return double The price, converted in the currency selected by the User.
	 * @since 4.0.9.150619
	 */
	public function woocommerce_get_regular_price($price, $product) {
		$product = $this->convert_product_prices($product, $this->get_selected_currency());
		return $product->regular_price;
	}

	/**
	 * Converts a product's sale price in the currently selected currency.
	 *
	 * @param double price The original price.
	 * @param WC_Product product The product to process.
	 * @return double The price, converted in the currency selected by the User.
	 * @since 4.0.9.150619
	 */
	public function woocommerce_get_sale_price($price, $product) {
		$product = $this->convert_product_prices($product, $this->get_selected_currency());
		return $product->sale_price;
	}

	/**
	 * Filters Post metadata being saved before it's returned to caller.
	 *
	 * @param mixed metadata The original metadata.
	 * @param int object_id The post ID.
	 * @param meta_key The metadata to be saved.
	 * @param meta_value The value to be saved.
	 * @return mixed The metadata value.
	 * @since x.x
	 */
	public function update_post_metadata($metadata, $object_id, $meta_key, $meta_value) {
		// Convert  totals into base Currency (they are saved in the currency used
		// to complete the transaction)
		if(in_array($meta_key,
								array('_order_total',
											'_order_discount',
											'_cart_discount',
											'_order_shipping',
											'_order_tax',
											'_order_shipping_tax',
											'_refund_amount',
											)
								)
			) {

			$order = $this->currencyswitcher()->get_order($object_id);

			// Add the metadata required to handle refunds correctly
			if(get_post_type($object_id) == 'shop_order_refund') {
				$this->set_refund_metadata($order);
			}

			// If Order Currency is empty, it means that we are in checkout phase.
			// WooCommerce saves the Order Currency AFTER the Order Total (a bit
			// nonsensical, but that's the way it is). In such case, we can take the
			// currency currently selected to place the Order and set it as the default
			$order_currency = $order->get_currency();
			if(empty($order_currency)) {
				$order_currency = get_woocommerce_currency();
			}

			// Save the amount in base currency. This will be used to correct the reports
			$amount_in_base_currency = $this->currencyswitcher()->convert($meta_value,
																																		$order_currency,
																																		$this->base_currency(),
																																		null,
																																		false);
			$order->set_meta($meta_key . '_base_currency', $amount_in_base_currency);
		}

		return $metadata;
	}

	/**
	 * Sets additional metadata against a refund.
	 *
	 * @param WC_Order refund The target refund.
	 * @since x.x
	 */
	protected function set_refund_metadata($refund) {
		$refund_currency = $refund->get_currency();
		// Set refund currency, if it's missing
		if(empty($refund_currency)) {
			$refund_id = aelia_get_order_id($refund);
			$parent_order_id = wp_get_post_parent_id($refund_id);
			// Set the currency against the refund. Such information it vital to be able
			// to calculate the refund amounts in base currency
			if(is_numeric($parent_order_id)) {
				$order_currency = $this->currencyswitcher()->get_order_currency($parent_order_id);
			}
			else {
				$order_currency = get_woocommerce_currency();
			}
			// Debug
			//var_dump($parent_order_id, $order_currency);
			update_post_meta($refund_id, '_order_currency', $order_currency);
		}
	}

	/**
	 * Updates the order currency when a suspended order is resumed.
	 *
	 * See support request #1214
	 * This action covers  the edge case in which an order was saved in one currency,
	 * then resumed after switching currency. Since WooCommerce saves the currency
	 * AFTER the order amounts, the conversion hooks pick up the previous order currency
	 * from the order and perform an incorrect conversion. By updating the currency
	 * as soon as an order is resumed, the conversion will work again.
	 *
	 * @param int order_id The ID of the order being resumed.
	 * @link https://aelia.freshdesk.com/helpdesk/tickets/1214
	 * @since x.x
	 */
	public function woocommerce_resume_order($order_id) {
		update_post_meta($order_id, '_order_currency', get_woocommerce_currency());
	}
}
