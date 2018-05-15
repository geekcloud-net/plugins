<?php
namespace Aelia\WC\CurrencySwitcher;
if(!defined('ABSPATH')) exit; // Exit if accessed directly

use Aelia\WC\Aelia_Plugin;
use Aelia\WC\Aelia_SessionManager;
use Aelia\WC\IP2Location;
use Aelia\WC\CurrencySwitcher\Settings;
use Aelia\WC\CurrencySwitcher\Settings_Renderer;
use Aelia\WC\CurrencySwitcher\Messages;
use Aelia\WC\CurrencySwitcher\Logger;
use \Exception;
use \InvalidArgumentException;
use \WC_Product;

//define('SCRIPT_DEBUG', 1);
//error_reporting(E_ALL);

// Load plugin's definitions
require_once('lib/general_functions.php');
require_once('lib/classes/definitions/definitions.php');

interface IWC_Aelia_CurrencySwitcher {
	public function woocommerce_currency($currency);
}

/**
 * Allows to display prices and accept payments in multiple currencies.
 */
class WC_Aelia_CurrencySwitcher extends Aelia_Plugin implements IWC_Aelia_CurrencySwitcher {
	// @var string The plugin version
	public static $version = '4.5.17.180404';

	// @var string The plugin slug
	public static $plugin_slug = Definitions::PLUGIN_SLUG;
	public static $text_domain = Definitions::TEXT_DOMAIN;
	public static $plugin_name = 'Aelia Currency Switcher for WooCommerce';

	// @var WC_Aelia_Reporting_Manager The object that handles the recalculations needed for reporting
	private $_reporting_manager;
	// @var WC_Aelia_CS_Admin_Interface_Manager The object that handles the rendering of the admin interface components
	private $_admin_interface_manager;
	// @var WC_Aelia_CurrencyPrices_Manager The object that handles Currency Prices for the Products.
	private $_currencyprices_manager;

	// @var array Holds a list of integration classes that add or improve support for 3rd party plugins and themes
	private $_integration_classes = array();

	// @var array Holds a list of the errors related to missing requirements
	public static $requirements_errors = array();

	// @var array A list of orders corresponding to item IDs. Used to retrieve the order ID starting from one of the items it contains.
	private $items_orders = array();

	// @var string The currency that is currently active.
	protected $selected_currency;

	// @var int The ID of the order for which a notification (email) is being sent (if any)
	protected static $notification_order_id = null;

	/**
	 * A list of orders loaded by the plugin. Used for caching.
	 *
	 * @var array
	 * @since 4.4.7.170202
	 */
	protected $loaded_orders = array();

	/**
	 * Returns the instance of the logger used by the plugin.
	 *
	 * @return \Aelia\WC\Logger.
	 * @since 4.5.16.180307
	 */
	// TODO This method will be redundant with the AFC 1.9.x. Remove it when no longer needed.
	public function get_logger() {
		if(empty($this->logger)) {
			$this->logger = parent::get_logger();
			$this->logger->set_debug_mode($this->debug_mode());
		}
		return $this->logger;
	}

	/**
	 * Factory method.
	 */
	public static function factory() {
		// Load Composer autoloader
		require_once(__DIR__ . '/vendor/autoload.php');

		$settings_key = self::$plugin_slug;

		$settings_controller = null;
		$messages_controller = null;
		// Example on how to initialise a settings controller and a messages controller
		$settings_page_renderer = new Settings_Renderer();
		$settings_controller = new Settings(Settings::SETTINGS_KEY,
																				self::$text_domain,
																				$settings_page_renderer);
		$messages_controller = new Messages(self::$text_domain);

		$plugin_instance = new self($settings_controller, $messages_controller);
		return $plugin_instance;
	}

	/**
	 * Constructor.
	 *
	 * @param Aelia\WC\Settings settings_controller The controller that will handle
	 * the plugin settings.
	 * @param Aelia\WC\Messages messages_controller The controller that will handle
	 * the messages produced by the plugin.
	 */
	public function __construct($settings_controller,
															$messages_controller) {
		// Load Composer autoloader
		require_once(__DIR__ . '/vendor/autoload.php');
		require_once('lib/woocommerce-core-aux-functions.php');
		require_once('lib/backward-compatibility.php');

		parent::__construct($settings_controller, $messages_controller);
	}

	/**
	 * Indicates if debug mode is active.
	 *
	 * @return bool
	 */
	public function debug_mode() {
		return $this->_settings_controller->debug_mode();
	}

	/**
	 * Returns global instance of woocommerce.
	 *
	 * @return object The global instance of woocommerce.
	 * @since 3.3
	 */
	protected function wc() {
		global $woocommerce;
		return $woocommerce;
	}

	/**
	 * Returns the instance of the currency prices manager class loaded by the
	 * plugin.
	 *
	 * @return WC_Aelia_CurrencyPrices_Manager
	 * @since 3.7.9.150813
	 */
	public function currencyprices_manager() {
		if(empty($this->_currencyprices_manager)) {
			$this->load_currencyprices_manager();
		}
		return $this->_currencyprices_manager;
	}

	/**
	 * Returns the amount of decimals used by WooCommerce for prices.
	 *
	 * @param string currency A currency code.
	 * @return int
	 * @see Aelia\WC\CurrencySwitcher\Settings::price_decimals().
	 */
	protected function price_decimals($currency = null) {
		$currency = empty($currency) ? $this->get_selected_currency() : $currency;

		return self::settings()->price_decimals($currency);
	}

	/**
	 * Formats a raw price using WooCommerce settings.
	 *
	 * @param float raw_price The price to format.
	 * @param string currency The currency code. If empty, currently selected
	 * currency is taken.
	 * @return string
	 */
	public function format_price($raw_price, $currency = null) {
		// Prices may be left empty. In such case, there's no need to format them
		if(!is_numeric($raw_price)) {
			return $raw_price;
		}

		if(empty($currency)) {
			$currency = $this->get_selected_currency();
		}
		$settings = $this->settings_controller();
		$price = number_format($raw_price,
													 $this->price_decimals($currency),
													 $settings->get_currency_decimal_separator($currency),
													 $settings->get_currency_thousand_separator($currency));

		if((get_option('woocommerce_price_trim_zeros') == 'yes') &&
			 ($this->price_decimals() > 0)) {
			$price = $this->trim_zeroes($price);
		}

		$currency_symbol = get_woocommerce_currency_symbol($currency);

		return '<span class="amount">' . sprintf(get_woocommerce_price_format(), $currency_symbol, $price) . '</span>';
	}

	/**
	 * Trims the trailing zeroes from a formatted floating point value.
	 *
	 * @param string value The falue to format.
	 * @param string decimal_separator The decimal separator.
	 * @return string
	 */
	protected function trim_zeroes($value, $decimal_separator = null) {
		if(empty($decimal_separator)) {
			$decimal_separator = $this->settings_controller()->get_currency_decimal_separator($this->base_currency());
		}

		$trim_regex = '/' . preg_quote($decimal_separator, '/') . '0++$/';
		return preg_replace($trim_regex, '', $value);
	}

	/**
	 * Converts a float to a string without locale formatting which PHP adds when
	 * changing floats to strings.
	 *
	 * @param float value The value to convert.
	 * @return string
	 */
	public function float_to_string($value) {
		if(!is_float($value)) {
			return $value;
		}

		$locale = localeconv();
		$string = strval($value);
		$string = str_replace($locale['decimal_point'], '.', $string);

		return $string;
	}

	/**
	 * Returns the namespace to use to load the appropriate classes for the active
	 * WooCommerce version.
	 *
	 * @return string
	 * @since 4.4.0.161221
	 */
	public static function get_wc_namespace() {
		// @var array An array of WooCommerce version => namespace. The namespace
		// will be used to load the appropriate classes for the active WooCommerce
		// version
		$namespaces = array(
			// 2.6 and earlier can use the WC26 namespace
			'2.4' => 'WC26',
			'2.5' => 'WC26',
			'2.6' => 'WC26',
			// 2.7
			'2.7' => 'WC27',
			// 3.2
			// @since 4.5.0.170901
			'3.2' => 'WC32',
		);

		krsort($namespaces);

		$result = null;
		foreach($namespaces as $wc_version => $namespace) {
			if(aelia_wc_version_is('>=', $wc_version)) {
				$result = $namespace;
				break;
			}
		}
		return $result;
	}

	/**
	 * Loads the class that will handle prices in different currencies for each
	 * product.
	 */
	private function load_currencyprices_manager() {
		$namespace = self::get_wc_namespace();
		$class = '\\Aelia\\WC\\CurrencySwitcher\\' . $namespace . '\WC_Aelia_CurrencyPrices_Manager';
		// Use a class alias to expose the Currency Prices Manager in the root
		// namespace, for backward compatibility and ease of access from other plugins
		class_alias($class, 'WC_Aelia_CurrencyPrices_Manager');

		$this->_currencyprices_manager = $class::Instance();
	}

	/**
	 * Loads additional classes that implement integration with 3rd party
	 * plugins and themes.
	 */
	private function load_integration_classes() {
		$this->_integration_classes = array(
			'wc_cart_notices' => new WC_Aelia_CS_Cart_Notices_Integration(),
			'wc_kissmetrics' => new WC_Aelia_KISSMetrics_Integration(),
			'wc_be_table_rates' => new WC_Aelia_CS_BE_Table_Rates_Integration(),
		);
	}

	/**
	 * Loads the class that will handle reporting calls to recalculate sales
	 * totals in Base currency.
	 */
	private function load_reporting_manager() {
		$this->_reporting_manager = new WC_Aelia_Reporting_Manager();
	}

	/**
	 * Loads the components and widgets that will be applied to the admin
	 * interface.
	 */
	private function load_admin_interface_manager() {
		$this->_admin_interface_manager = new WC_Aelia_CS_Admin_Interface_Manager();
	}

	/**
	 * Returns the Exchange Rate to convert the default woocommerce currency into
	 * the one currently selected by the User.
	 *
	 * @param string selected_currency The code of the Currency selected by the
	 * User.
	 * @return double The Exchange Rate to convert the default woocommerce
	 * currency into the one currently selected by the User.
	 */
	private function _get_exchange_rate($selected_currency) {
		// Retrieve exchange rates from the configuration
		$exchange_rates = $this->_settings_controller->get_exchange_rates();

		$result = isset($exchange_rates[$selected_currency]) ? $exchange_rates[$selected_currency] : null;
		if(empty($result)) {
			$this->trigger_error(Definitions::ERR_INVALID_CURRENCY, E_USER_WARNING, array($selected_currency));
		}

		return $result;
	}

	/**
	 * Returns the Exchange Rate currently applied, based on selected currency.
	 *
	 * @return float An exchange rate.
	 */
	public function current_exchange_rate() {
		return $this->_get_exchange_rate($this->get_selected_currency());
	}

	/**
	 * Converts an amount from a Currency to another.
	 *
	 * @param float amount The amount to convert.
	 * @param string from_currency The source Currency.
	 * @param string to_currency The destination Currency.
	 * @param int price_decimals The amount of decimals to use when rounding the
	 * converted result.
	 * @param bool include_markup Indicates if the exchange rates used for conversion
	 * should include the markup (if one was specified).
	 * @return float The amount converted in the destination currency.
	 */
	public function convert($amount, $from_currency, $to_currency, $price_decimals = null, $include_markup = true) {
		// No need to try converting an amount that is not numeric. This can happen
		// quite easily, as "no value" is passed as an empty string
		if(!is_numeric($amount)) {
			return $amount;
		}

		// No need to convert a zero amount, it will stay zero
		if($amount == 0) {
			return $amount;
		}

		// No need to spend time converting a currency to itself
		if($from_currency == $to_currency) {
			return $amount;
		}

		if(!is_numeric($price_decimals)) {
			$price_decimals = $this->price_decimals($to_currency);
		}

		// Retrieve exchange rates from the configuration
		$exchange_rates = $this->_settings_controller->get_exchange_rates($include_markup);
		//var_dump($exchange_rates);

		try {
			$from_currency_rate = $this->_settings_controller->get_exchange_rate($from_currency, $include_markup);
			if(empty($from_currency_rate)) {
				throw new InvalidArgumentException(sprintf($this->get_error_message(Definitions::ERR_INVALID_SOURCE_CURRENCY),
																									 $from_currency));
			}

			$to_currency_rate = $this->_settings_controller->get_exchange_rate($to_currency, $include_markup);
			if(empty($to_currency_rate)) {
				throw new InvalidArgumentException(sprintf($this->get_error_message(Definitions::ERR_INVALID_DESTINATION_CURRENCY),
																									 $to_currency));
			}

			$exchange_rate = $to_currency_rate / $from_currency_rate;
		}
		catch(Exception $e) {
			$full_message = $e->getMessage() .
											sprintf(__('Stack trace: %s', Definitions::TEXT_DOMAIN),
															$e->getTraceAsString());
			trigger_error($full_message, E_USER_ERROR);
		}

		return apply_filters('wc_aelia_cs_converted_amount',
												 round($amount * $exchange_rate, $price_decimals),
												 $amount,
												 $from_currency,
												 $to_currency,
												 $price_decimals,
												 $include_markup);
	}

	/**
	 * Returns a value indicating if user is currently paying for an order.
	 *
	 * @return bool
	 */
	protected function user_is_paying_existing_order() {
		global $post;

		$paying_for_order = isset($_GET['pay_for_order']) ? $_GET['pay_for_order'] : false;

		// As of WooCommerce 2.0.14, checking if we are on the "pay" page is the only
		// way to determine if the user is paying for an order
		if($paying_for_order != false) {
			// Paying for existing order
			global $wp;
			// WC 2.1 - Order ID is in "order-pay" query var
			$order_id = isset($wp->query_vars['order-pay']) ? $wp->query_vars['order-pay'] : false;
			return $order_id;
		}
		else {
			// NOT paying for existing order
			return false;
		}
	}

	/**
	 * Overrides the currency symbol by loading the one configured in the settings.
	 *
	 * @param string currency_symbol The symbol passed by WooCommerce.
	 * @param string currency The currency for which the symbol is requested.
	 * @return string
	 */
	public function woocommerce_currency_symbol($currency_symbol, $currency) {
		if(defined('AELIA_CS_SETTINGS_PAGE')) {
			return $currency_symbol;
		}
		return self::settings()->get_currency_symbol($currency, $currency_symbol);
	}

	/**
	 * Adds more currencies to the list of the available ones.
	 *
	 * @param array currencies The list of currencies passed by WooCommerce.
	 * @return array
	 */
	public function woocommerce_currencies($currencies) {
		return array_merge(WC_Aelia_Currencies_Manager::world_currencies(), $currencies);
	}

	/**
	 * Returns the currently selected currency.
	 *
	 * @param string currency The currency used by default by WooCommerce.
	 * @return string The symbol of the currency selected by the User.
	 */
	public function woocommerce_currency($currency) {
		$order_id = false;
		// If user is paying for a previously placed, but unpaid, order, then we have
		// to return the currency in which the order was placed
		if(isset($this->wc()->session)) {
			// Check if user is paying for an existing order
			$order_id = $this->user_is_paying_existing_order();
			// If user is not paying for an existing order, check if he is reviewing an order
			if(!is_numeric($order_id)) {
				// Set the active currency to order currency when reviewing an order
				global $wp;
				if(!empty($wp->query_vars['view-order'])) {
					$order_id = $wp->query_vars['view-order'];
				}
			}
		}

		if($order_id == false) {
			// If user is adding or editing an order in the backend, then we have to
			// return the order currency
			// @since 4.0.10.150625
			// @see Aelia_Plugin::editing_order()
			$order_id = self::editing_order();
		}

		if($order_id == false) {
			// If WooCommerce is sending a notification about an existing order, then
			// we have to return the order currency
			// @since 4.2.9.150930
			$order_id = self::sending_order_notification();
		}

		if(is_numeric($order_id)) {
			$this->set_active_currency_to_order_currency($order_id);
		}

		$selected_currency = $this->get_selected_currency();
		// Debug
		//var_dump("SELECTED", $selected_currency);

		return $selected_currency;
	}

	/**
	 * Replaces the active currency with the one used by currency in which an
	 * order was placed.
	 *
	 * @param int order_id The ID of the order from which to retrieve the currency.
	 */
	private function set_active_currency_to_order_currency($order_id) {
		// Debug
		//var_dump($order);
		$order = $this->get_order($order_id);
		$order_currency = $order->get_currency();

		$valid_currency = $this->is_valid_currency($order_currency);
		// Do not attempt to re-format totals in currencies that are no longer enabled,
		// as they would not have configuration settings
		if($valid_currency) {
			$this->selected_currency = $order_currency;
			//$this->save_user_selected_currency($this->selected_currency);
		}
		return $valid_currency;
	}

	/**
	 * Overrides the number of decimals used to format prices.
	 *
	 * @param int decimals The number of decimals passed by WooCommerce.
	 * @return int
	 */
	public function pre_option_woocommerce_price_num_decimals($decimals) {
		return $this->price_decimals(get_woocommerce_currency());
	}

	/**
	 * Sets the currency symbol position for the active currency.
	 *
	 * @param string position The default position used by WooCommerce.
	 * @return string The position to use for the active currency.
	 */
	public function pre_option_woocommerce_currency_pos($position) {
		return $this->settings_controller()->get_currency_symbol_position(get_woocommerce_currency());
	}

	/**
	 * Sets the thousdand separator for the active currency.
	 *
	 * @param string position The default position used by WooCommerce.
	 * @return string The thousand separator.
	 */
	public function pre_option_woocommerce_price_thousand_sep($thousand_separator) {
		return $this->settings_controller()->get_currency_thousand_separator(get_woocommerce_currency());
	}

	/**
	 * Sets the thousdand separator for the active currency.
	 *
	 * @param string position The default position used by WooCommerce.
	 * @return string The decimal separator.
	 */
	public function pre_option_woocommerce_price_decimal_sep($decimal_separator) {
		return $this->settings_controller()->get_currency_decimal_separator(get_woocommerce_currency());
	}

	/**
	 * Adds more scheduling options to WordPress Cron.
	 *
	 * @param array schedules Existing Cron scheduling options.
	 */
	public function cron_schedules($schedules) {
		// Adds "weekly" to the existing schedules
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display' => __('Weekly', Definitions::TEXT_DOMAIN),
		);
		// Adds "monthly" to the existing schedules
		$schedules['monthly'] = array(
			'interval' => 2592000,
			'display' => __('Monthly (every 30 days)', Definitions::TEXT_DOMAIN),
		);
		return $schedules;
	}

	/**
	 * Reformats Tax Totals printed on Order Receipts by replacing the
	 * currency symbol with the one for the currency used to place the order.
	 *
	 * @param array tax_totals An array of Tax totals.
	 * @param WC_Order order The order for which the receipt is being generated.
	 * @return array
	 */
	public function woocommerce_order_tax_totals($tax_totals, $order) {
		$order_id = aelia_get_order_id($order);
		$order_currency = $this->get_order_currency($order_id);

		// No need to re-format if if the currency in use is the base one
		if($order_currency == $this->base_currency()) {
			return $tax_totals;
		}

		foreach($tax_totals as $tax_id => $tax_details) {
			$tax_amount = get_value('amount', $tax_details);
			if(is_numeric($tax_amount)) {
				$tax_details->formatted_amount = $this->format_price($tax_amount, $order_currency);
			}
		}

		return $tax_totals;
	}

	/**
	 * Fired after an order is saved. It checks that the order currency has been
	 * stored against the post, adding it if it's missing. This method is needed
	 * because, for some reason, WooCommerce does not store the order currency when
	 * an order is created from the backend.
	 *
	 * @param int post_id The post (order) ID.
	 * @param WC_Order The order that has just been saved.
	 */
	public function woocommerce_process_shop_order_meta($post_id, $post) {
		$order = $this->get_order($post_id);
		// Check if order currency is saved. If not, set it to currently selected currency
		$order_currency = $order->get_currency();

		if(empty($order_currency)) {
			$order->set_order_currency($this->get_selected_currency());
		}

		// Set the active currency to the one from the order. This will ensure that
		// elements like the decimal separator will be taken into account, and prevent
		// WooCommerce from setting item prices to zero.
		// @link https://aelia.freshdesk.com/helpdesk/tickets/6815
		// @see WC_Order_Item_Product::set_total()
		// @since 4.5.17.180404
		$this->set_active_currency_to_order_currency($post_id);
		add_filter('woocommerce_currency', array($this, 'woocommerce_currency'), 5);
	}

	/**
	 * Loads the settings for the currency used when an order was placed. They
	 * will then be used to reconfigure the JavaScript used in Order edit page.
	 *
	 * @param int order_id The Order ID.
	 * @param array woocommerce_admin_params An array of parameters to pass to the
	 * admin scripts.
	 * @return array
	 */
	public function load_order_currency_settings($order_id, array $woocommerce_admin_params = array()) {
		// Add filter to retrieve the currently selected currency. This will be used
		// when creating a new order, to associate the proper currency to it
		add_filter('woocommerce_currency', array($this, 'woocommerce_currency'), 5);

		// Extract the currency from the order
		$order_currency = $this->get_order_currency($order_id);

		$woocommerce_writepanel_params = array(
			'currency_format_symbol' => get_woocommerce_currency_symbol($order_currency),
			'order_currency' => $order_currency,
			// TODO Load the decimal places from the Currency Switcher settings
			// TODO Load the thousand separator from the Currency Switcher settings
		);

		$woocommerce_admin_params = array_merge($woocommerce_admin_params, $woocommerce_writepanel_params);

		return $woocommerce_admin_params;
	}

	/**
	 * Indicates if the scripts to extend the Order Edit page should be loaded.
	 *
	 * @param object post
	 * @return bool
	 * @since 4.5.15.180222
	 */
	protected function should_load_order_edit_scripts($post) {
		$post_type = is_object($post) ? $post->post_type : null;

		return apply_filters('wc_aelia_cs_load_order_edit_scripts', $post_type === 'shop_order', $post);
	}

	/**
	 * Loads the localization for the scripts in the Admin section.
	 */
	protected function localize_admin_scripts() {
		global $post;
		// Prepare parameters for common admin scripts
		$woocommerce_admin_params = array(
			'base_currency' => $this->base_currency(),
			'enabled_currencies' => $this->enabled_currencies(),
		);

		$post_type = is_object($post) ? $post->post_type : null;
		// When viewing an Order, load the settings for the currency used when it
		// was placed
		if($this->should_load_order_edit_scripts($post)) {
			$woocommerce_admin_params = $this->load_order_currency_settings($post->ID, $woocommerce_admin_params);
			wp_enqueue_script('wc-aelia-currency-switcher-order-edit');
		}

		// When viewing a product, load the script to handle the currency-specific
		// data
		if($post_type === 'product') {
			wp_enqueue_script('wc-aelia-currency-switcher-product-edit');
		}

		wp_localize_script('wc-aelia-currency-switcher-admin-overrides',
											 'aelia_cs_woocommerce_writepanel_params',
											 $woocommerce_admin_params);

		// Prepare parameters for Reports
		if(self::doing_reports()) {
			$reports_admin_scripts_params = array(
				// Localisation parameters for reports
			);
			wp_localize_script(static::$plugin_slug . '-admin-reports',
												 'aelia_cs_reports_params',
												 $reports_admin_scripts_params);
		}
	}

	/**
	 * Sets hooks related to shipping methods.
	 */
	protected function set_shipping_methods_hooks() {
		add_filter('woocommerce_evaluate_shipping_cost_args', array($this, 'woocommerce_evaluate_shipping_cost_args'), 10, 3);

		// WC 2.5 and earlier
		add_filter('woocommerce_shipping_methods', array($this, 'woocommerce_shipping_methods'), 50);
		// WC 2.6 and later
		add_filter('woocommerce_shipping_zone_shipping_methods', array($this, 'woocommerce_shipping_zone_shipping_methods'), 50, 4);
	}

	/**
	 * Sets hooks related to scheduled tasks.
	 */
	protected function set_scheduled_tasks_hooks() {
		// Add hooks to automatically update Exchange Rates
		add_filter('cron_schedules', array($this, 'cron_schedules'));
		add_action($this->_settings_controller->exchange_rates_update_hook(),
							 array($this->_settings_controller, 'scheduled_update_exchange_rates'));
	}

	/**
	 * Sets hooks related to cart.
	 */
	protected function set_cart_hooks() {
		// Add the hooks to recalculate cart total when needed
		add_action('woocommerce_cart_contents_total', array($this, 'woocommerce_cart_contents_total'));
		add_action('woocommerce_before_cart_table', array($this, 'woocommerce_before_cart_table'), 10);
		add_filter('woocommerce_calculated_total', array($this, 'reset_recalculate_cart_flag'), 10, 2);
		add_filter('woocommerce_add_cart_item', array($this, 'woocommerce_add_cart_item'), 15, 1);
		add_filter('woocommerce_get_cart_item_from_session', array($this, 'woocommerce_get_cart_item_from_session'), 15, 3);

		/* Recalculate cart totals for the mini cart. The recalculate_totals() method
		 * has a safeguard to prevent multiple recalculations.
		 */
		add_action('woocommerce_before_mini_cart', array($this, 'recalculate_cart_totals'));

		// Add the hook to be fired when cart is loaded from session
		// @deprecated since 4.0.2.150324
		//add_action('woocommerce_cart_loaded_from_session', array($this, 'woocommerce_cart_loaded_from_session'), 1);
	}

	/**
	 * Set hooks related to orders.
	 */
	protected function set_order_hooks() {
		// @since 4.4.11.170405
		if(aelia_wc_version_is('>=', '3.0')) {
			add_filter('woocommerce_order_get_tax_totals ', array($this, 'woocommerce_order_tax_totals'), 10, 2);
		}
		else{
			add_filter('woocommerce_order_tax_totals', array($this, 'woocommerce_order_tax_totals'), 10, 2);
		}
		add_action('woocommerce_process_shop_order_meta', array($this, 'woocommerce_process_shop_order_meta'), 5, 2);

		// Hook each notification email, so that the order currency can be used when
		// they are sent
		// @since 4.2.9.150930
		$order_status_events = array(
			'woocommerce_order_status_pending_to_processing',
			'woocommerce_order_status_pending_to_completed',
			'woocommerce_order_status_pending_to_cancelled',
			'woocommerce_order_status_pending_to_on-hold',
			'woocommerce_order_status_failed_to_processing',
			'woocommerce_order_status_failed_to_completed',
			'woocommerce_order_status_on-hold_to_processing',
			'woocommerce_order_status_on-hold_to_cancelled',
			'woocommerce_order_status_completed',
			'woocommerce_order_fully_refunded',
			'woocommerce_order_partially_refunded',
		);
		foreach($order_status_events as $hook) {
			add_action($hook . '_notification', array($this, 'track_order_notification'), 5, 1);
		}
	}

	/**
	 * Sets hooks to register shortcodes.
	 */
	protected function set_shortcodes_hooks() {
		// Shortcode to render the currency selector
		add_shortcode('aelia_currency_selector_widget', array('Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher_Widget', 'render_currency_selector'));
		// Shortcode to render the country selector
		add_shortcode('aelia_cs_country_selector_widget',
									array('Aelia\WC\CurrencySwitcher\WC_Aelia_Customer_Country_Selector_Widget', 'render_customer_country_selector'));

		/* Shortcode maintained for backward compatibility.
		 * @deprecated since 4.0.0.150311
		 */
		add_shortcode('aelia_cs_billing_country_selector_widget',
									array('Aelia\WC\CurrencySwitcher\WC_Aelia_Customer_Country_Selector_Widget', 'render_customer_country_selector'));

		// Shortcode to render a product price
		add_shortcode('aelia_cs_product_price', array($this, 'render_shortcode_product_price'));
		add_shortcode('aelia_cs_currency_amount', array($this, 'render_shortcode_currency_amount'));
	}

	/**
	 * Enables or disables the hooks used to format the currency prices.
	 *
	 * @param bool enable Indicates if the hooks should be enabled or disabled.
	 * @since 4.4.7.170202
	 */
	protected function set_currency_formatting_hooks($enable = true) {
		if($enable) {
			// Display prices with the amount of decimals configured for the active currency
			add_filter('pre_option_woocommerce_price_num_decimals', array($this, 'pre_option_woocommerce_price_num_decimals'), 10, 1);
			add_filter('pre_option_woocommerce_currency_pos', array($this, 'pre_option_woocommerce_currency_pos'), 10, 1);
			add_filter('pre_option_woocommerce_price_thousand_sep', array($this, 'pre_option_woocommerce_price_thousand_sep'), 10, 1);
			add_filter('pre_option_woocommerce_price_decimal_sep', array($this, 'pre_option_woocommerce_price_decimal_sep'), 10, 1);
		}
		else {
			// Display prices with the amount of decimals configured for the active currency
			remove_filter('pre_option_woocommerce_price_num_decimals', array($this, 'pre_option_woocommerce_price_num_decimals'), 10, 1);
			remove_filter('pre_option_woocommerce_currency_pos', array($this, 'pre_option_woocommerce_currency_pos'), 10, 1);
			remove_filter('pre_option_woocommerce_price_thousand_sep', array($this, 'pre_option_woocommerce_price_thousand_sep'), 10, 1);
			remove_filter('pre_option_woocommerce_price_decimal_sep', array($this, 'pre_option_woocommerce_price_decimal_sep'), 10, 1);
		}
	}

	/**
	 * Retrieves the order to which an order item belongs.
	 *
	 * @param int order_item_id The order item.
	 * @return Aelia_Order
	 */
	protected function get_order_from_item($order_item_id) {
		// Check if the order is stored in the internal list
		if(empty($this->items_orders[$order_item_id])) {
			// Cache the order after retrieving it. This will reduce the amount of queries executed
			$this->items_orders[$order_item_id] = Aelia_Order::get_by_item_id($order_item_id);
		}

		return $this->items_orders[$order_item_id];
	}

	/**
	 * Returns an order instance.
	 *
	 * @param int order_id An order ID.
	 * @param bool force_load Indicates if the load should be forcibly loaded. If
	 * set to false, the method will return the cached instance of an order, if
	 * it exists.
	 * @return Aelia_Order
	 * @since 4.4.7.170202
	 */
	public function get_order($order_id, $force_load = false) {
		if($force_load || empty($this->loaded_orders[$order_id])) {
			/* Remove the hooks used to set the price format values (decimals, decimal
			 * separators, etc). This is necessary since WC 2.7, as the new logic they
			 * introduced loads these parameters prematurely, as soon as an order is
			 * instantiated. Such premature initialisation triggers the related filters,
			 * causing an infinite recursion.
			 *
			 * @since WC 2.7
			 */
			$this->set_currency_formatting_hooks(false);

			// Cache the loaded order
			$this->loaded_orders[$order_id] = new Aelia_Order($order_id);

			// Restore the filters that were previously removed
			$this->set_currency_formatting_hooks(true);
		}
		return $this->loaded_orders[$order_id];
	}

	/**
	 * Returns the currency from an order.
	 *
	 * @param int order_id An order ID.
	 * @param bool force_load Indicates if the load should be forcibly loaded. If
	 * set to false, the method will return the cached instance of an order, if
	 * it exists.
	 * @return string The order currency
	 * @since 4.4.7.170202
	 */
	public function get_order_currency($order_id, $force_load = false) {
		$order = $this->get_order($order_id, $force_load);
		return is_object($order) ? $order->get_currency() : null;
	}

	/**
	 * Adds line totals in base currency for each product in an order.
	 *
	 * @param null $check
	 * @param int $order_item_id The ID of the order item.
	 * @param string $meta_key The meta key being saved.
	 * @param mixed $meta_value The value being saved.
	 * @return null|bool
	 *
	 * @see update_metadata().
	 */
	public function update_order_item_metadata($check, $order_item_id, $meta_key, $meta_value) {
		// Convert line totals into base Currency (they are saved in the currency used
		// to complete the transaction)
		if(in_array($meta_key, array(
														'_line_subtotal',
														'_line_subtotal_tax',
														'_line_tax',
														'_line_total',
														'tax_amount',
														'shipping_tax_amount',
														'discount_amount',
														'discount_amount_tax',
														)
								)
			 ) {

			$order = $this->get_order_from_item($order_item_id);

			$order_id = aelia_get_order_id($order);
			if(empty($order_id)) {
				// An empty order id indicates that something is not right. Without it,
				// we cannot calculate the amounts in base currency
				$this->get_logger()->info(__('Order not found. Calculation of metadata in base ' .
																		 'currency skipped.', self::$text_domain),
																	array(
																		'Order Item ID' => $order_item_id,
																		'Meta Key' => $meta_key,
																	));
			}
			else {
				// Retrieve the order currency
				// If Order Currency is empty, it means that we are in checkout phase.
				// WooCommerce saves the Order Currency AFTER the Order Total (a bit
				// nonsensical, but that's the way it is). In such case, we can take the
				// currency currently selected to place the Order and set it as the default
				$order_currency = $order->get_currency();
				if(empty($order_currency)) {
					$order_currency = get_woocommerce_currency();
				}

				// Save the amount in base currency. This will be used to correct the reports
				$amount_in_base_currency = $this->convert($meta_value,
																									$order_currency,
																									$this->settings_controller()->base_currency(),
																									null,
																									false);
				$amount_in_base_currency = $this->float_to_string($amount_in_base_currency);
				// Update meta value with new string
				update_metadata('order_item', $order_item_id, $meta_key . '_base_currency', $amount_in_base_currency);
			}
		}

		return $check;
	}

	/**
	 * Add custom item metadata added by the plugin.
	 *
	 * @param array item_meta The original metadata to hide.
	 * @return array
	 */
	public function woocommerce_hidden_order_itemmeta($item_meta) {
		$custom_order_item_meta = array(
			'_line_subtotal_base_currency',
			'_line_subtotal_tax_base_currency',
			'_line_tax_base_currency',
			'_line_total_base_currency',
			'tax_amount_base_currency',
			'shipping_tax_amount_base_currency',
			'discount_amount_base_currency',
			'discount_amount_tax_base_currency',
		);
		return array_merge($item_meta, $custom_order_item_meta);
	}

	/**
	 * Filters the available payment gateways based on the selected currency.
	 *
	 * @param array available_gateways A list of the available gateways to filter.
	 * @return array
	 */
	public function woocommerce_available_payment_gateways($available_gateways) {
		global $wp;

		$payment_currency = null;
		// If customer is paying for an existing order, take its currency
		$order_id = $this->user_is_paying_existing_order();
		if(is_numeric($order_id)) {
			// Debug
			//var_dump("PAYING ORDER " . $order_id);
			$order = $this->get_order($order_id);
			$payment_currency = $order->get_currency();
		}

		// If payment currency is empty, then customer is paying for a new order. In
		// such case, take the active currency
		if(empty($payment_currency)) {
			$payment_currency = $this->get_selected_currency();
		}

		// Debug
		//var_dump($payment_currency);

		$currency_gateways = self::settings()->currency_payment_gateways($payment_currency);

		// If no payment gateway has been enabled for a currency, it most probably
		// means that the Currency Switcher has not been configured properly. In such
		// case, return all payment gateways originally passed by WooCommerce, to
		// allow the Customer to complete the order.
		if(empty($currency_gateways)) {
			return $available_gateways;
		}

		//var_dump($currency_gateways, $available_gateways);
		foreach($available_gateways as $gateway_id => $gateway) {
			if(!in_array($gateway_id, $currency_gateways)) {
				unset($available_gateways[$gateway_id]);
			}
		}

		return $available_gateways;
	}

	/**
	 * Returns the base currency.
	 *
	 * @return string
	 * @since 3.4.15.140828
	 */
	public function base_currency() {
		return self::settings()->base_currency();
	}

	/**
	 * Returns a list of enabled currencies.
	 *
	 * @return array
	 */
	public function enabled_currencies() {
		return self::settings()->get_enabled_currencies();
	}

	/**
	 * Indicates if the plugin has been configured.
	 *
	 * @return bool
	 * @since 4.4.8.170306
	 */
	public function plugin_configured() {
		$current_settings = $this->_settings_controller->current_settings();
		return !empty($current_settings);
	}

	/**
	 * Displays a notice when the plugin has not yet been configured.
	 *
	 * @since 4.4.8.170306
	 */
	public function settings_notice() {
		?>
		<div id="message" class="updated woocommerce-message">
			<p>
				<strong><?php echo __('The Currency Switcher', self::$text_domain); ?></strong>
				<?php
					echo ' ' . __('is almost ready! Please go to <code>WooCommerce > Currency ' .
												'Switcher</code> settings page to complete the ' .
											  'configuration and start selling in multiple currencies.', self::$text_domain);
				?>
			</p>
			<p>
				<strong><?php echo __('Need help?', self::$text_domain); ?></strong><br />
				<?php
					echo sprintf(__('Our <a href="%s" target="_blank">Getting Started Guide</a> will show you how ' .
													'to configure the Currency Switcher and open your shop to an ' .
													'international audience in just a couple of minutes.',
													self::$text_domain),
											 'https://aelia.freshdesk.com/solution/articles/3000063641-how-to-configure-the-aelia-currency-switcher');
				?>
			</p>
			<p class="submit">
				<a href="<?php echo admin_url('admin.php?page=' . Definitions::MENU_SLUG); ?>"
					 class="button-primary"><?php echo __('Go to Currency Switcher settings page', self::$text_domain); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Sets the hook handlers for WooCommerce and WordPress.
	 */
	protected function set_hooks() {
		parent::set_hooks();

		if(is_admin() && !$this->plugin_configured()) {
			add_action('admin_notices', array($this, 'settings_notice'));
			// Don't set any hook until the plugin has been configured
			return;
		}

		add_action('woocommerce_integrations_init', array($this, 'woocommerce_integrations_init'), 1);
		add_action('woocommerce_integrations', array($this, 'woocommerce_integrations_override'), 20);

		// Override currency symbol
		add_action('woocommerce_currency_symbol', array($this, 'woocommerce_currency_symbol'), 5, 2);
		add_action('woocommerce_currencies', array($this, 'woocommerce_currencies'), 5, 1);

		// Product prices should not be converted in the Admin section
		if(self::is_frontend()) {
			// Add filter to display selected currency
			add_filter('woocommerce_currency', array($this, 'woocommerce_currency'), 5);

			// Set the hooks used to format the currency prices
			// @since 4.4.7.170202
			$this->set_currency_formatting_hooks(true);
		}

		// Add cart hooks
		$this->set_cart_hooks();

		// Add hooks for shipping methods
		$this->set_shipping_methods_hooks();

		// Add hooks for scheduled tasks
		$this->set_scheduled_tasks_hooks();

		// Add hooks for shortcodes
		$this->set_shortcodes_hooks();

		// Handle totals in base currency for order items
		add_filter('update_order_item_metadata', array($this, 'update_order_item_metadata'), 10, 4);
		add_filter('add_order_item_metadata', array($this, 'update_order_item_metadata'), 10, 4);
		add_filter('woocommerce_hidden_order_itemmeta', array($this, 'woocommerce_hidden_order_itemmeta'), 10, 1);

		// Add hooks to filter payment gateways based on the selected currency
		add_filter('woocommerce_available_payment_gateways', array($this, 'woocommerce_available_payment_gateways'), 20);

		// Add a filter for 3rd parties
		// Filter to retrieve the base currency
		add_filter('wc_aelia_cs_base_currency', array($this, 'base_currency'));
		// Filter to retrieve the list of enabled currencies
		add_filter('wc_aelia_cs_enabled_currencies', array($this, 'enabled_currencies'));
		// Filter to allow 3rd parties to convert a value from one currency to another
		add_filter('wc_aelia_cs_convert', array($this, 'convert'), 10, 5);

		// Filter to allow 3rd parties to retrieve a product's base currency
		// @since 4.4.19.170602
		add_filter('wc_aelia_cs_get_product_base_currency', array($this, 'wc_aelia_cs_get_product_base_currency'), 10, 2);

		// Admin backend
		add_action('admin_init', array($this, 'admin_init'));
	}

	/**
	 * Run the updates required by the plugin. This method runs at every load, but
	 * the updates are executed only once. This allows the plugin to run the
	 * updates automatically, without requiring deactivation and rectivation.
	 *
	 * @return bool
	 */
	public function run_updates() {
		/* This value identifies the plugin and it's used to determine the currently
		 * installed version. Normally, self::$plugin_slug is used, but we cannot use
		 * it here because the plugin slug changed in v.3.3. Using the plugin slug
		 * would make the installer think that the Currency Switcher was not installed
		 * and make it run a whole lot of updates that may not be required. Sticking
		 * to the old plugin ID, just for version checking, is an acceptable compromise.
		 */
		$plugin_id = 'wc_aelia_currencyswitcher';

		$installer_class = get_class($this) . '_Install';
		if(!class_exists($installer_class)) {
			return;
		}

		$installer = new $installer_class();
		return $installer->update($plugin_id, static::$version);
	}

	/**
	 * Checks that a Currency is valid.
	 *
	 * @param string currency The currency to check.
	 * @return bool True, if the Currency is valid, False otherwise.
	 */
	public function is_valid_currency($currency) {
		if(empty($currency)) {
			return false;
		}

		// Retrieve enabled currencies from settings
		$valid_currencies = $this->_settings_controller->get_enabled_currencies();

		// To be valid, a Currency must be amongst the enabled ones and have an
		// Exchange Rate greater than zero
		$is_valid = in_array($currency, $valid_currencies) &&
								($this->settings_controller()->get_exchange_rate($currency) > 0);
		return $is_valid;
	}

	/**
	 * Saves the Currency selected by the User against his profile, if he is
	 * logged in, and stores such Currency in User's session.
	 *
	 * @param string selected_currency The selected Currency code.
	 */
	private function save_user_selected_currency($selected_currency) {
		// Reset the selected currency
		$this->selected_currency = $selected_currency;
		$user_id = get_current_user_id();
		if(!empty($user_id)) {
			update_user_meta($user_id, Definitions::USER_CURRENCY, $selected_currency);
		}

		// Store the selected currency in a cookie for 48 hours
		Aelia_SessionManager::set_cookie(Definitions::USER_CURRENCY,
																		 $selected_currency,
																		 time() + DAY_IN_SECONDS);
	}

	/**
	 * Returns the visitor's IP address, handling the case in which a standard
	 * reverse proxy is used. This method is maintained for backward compatibility,
	 * just to allow firing "wc_aelia_currencyswitcher_visitor_ip" filter, but it
	 * should not be used anymore.
	 *
	 * @return string
	 * @deprecated since 3.3
	 */
	protected function get_visitor_ip_address() {
		$forwarded_for = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		$visitor_ip = IP2Location::factory()->get_visitor_ip_address();

		// Filter "wc_aelia_currencyswitcher_visitor_ip" is a legacy filter, maintained
		// for backward compatibility
		$visitor_ip = apply_filters('wc_aelia_currencyswitcher_visitor_ip', $visitor_ip, $forwarded_for);

		return $visitor_ip;
	}

	/**
	 * Returns the Currency selected by the User, returned as the first valid value
	 * from the following:
	 * - Currency stored in session
	 * - User's last selected currency
	 * - Main WooCommerce currency
	 *
	 * @return string The code of currently selected Currency.
	 */
	public function get_selected_currency() {
		if(empty($this->selected_currency)) {
			// If debugging of geolocation feature is disabled, take the currency from
			// user's section. If it is enabled, ignore any user or session currency and
			// allow it to be detected
			if(!self::settings()->debug_geolocation_currency_detection()) {
				$user_id = get_current_user_id();
				$user_currency = !empty($user_id) ? get_user_meta($user_id, Definitions::USER_CURRENCY, true) : null;

				$base_currency = $this->base_currency();
				// Try to get the Currency that User selected manually
				$this->selected_currency = coalesce(Aelia_SessionManager::get_cookie(Definitions::USER_CURRENCY),
																						$user_currency);
			}

			if(empty($this->selected_currency)) {
				if(self::settings()->currency_geolocation_enabled()) {
					// Try to set the Currency to the one used in the Country from which the
					// User is connecting
					$this->selected_currency = WC_Aelia_Currencies_Manager::factory()->get_currency_by_host($this->get_visitor_ip_address(),
																																														self::settings()->default_geoip_currency());
					//var_dump($this->selected_currency);die();

					// Save the currency detected by geolocation
					if($this->is_valid_currency($this->selected_currency)) {
						$this->save_user_selected_currency($this->selected_currency);
					}
				}
				else {
					// If everything else fails, use a default base currency, allowing 3rd
					// parties to override it as needed
					$this->selected_currency = apply_filters('wc_aelia_cs_default_selected_currency', $base_currency, $this);
				}
			}

			// Currently selected currency may be invalid for a number of reasons (e.g.
			// User had selected a Currency in the past, and it's no longer supported).
			// In such case, revert to WooCommerce base currency
			if(!$this->is_valid_currency($this->selected_currency)) {
				$this->selected_currency = $base_currency;
			}
		}
		return apply_filters('wc_aelia_cs_selected_currency', $this->selected_currency);
	}

	/**
	 * Recalculates the Cart totals, if the appropriate flag is set.
	 *
	 * @param bool force_recalculation Forces the recalculation of the cart totals,
	 * no matter what the value of the "Recalculate Totals" flag is.
	 */
	public function recalculate_cart_totals($force_recalculation = false) {
		// If the cart is empty, there is no need to recalculate the totals. They
		// would be zero, anyway
		if(empty($this->wc()->cart->cart_contents)) {
			return;
		}

		if($force_recalculation ||
			 (Aelia_SessionManager::get_value(Definitions::RECALCULATE_CART_TOTALS, 0, true) === 1)) {
			do_action('wc_aelia_currencyswitcher_recalculate_cart_totals_before');
			$this->wc()->cart->calculate_totals();
			do_action('wc_aelia_currencyswitcher_recalculate_cart_totals_after');
		}
	}

	/**
	 * Hook invoked before the cart table is displayed on the cart page.
	 */
	public function woocommerce_before_cart_table() {
		$this->recalculate_cart_totals();
	}

	/**
	 * Hook invoked when using a Menu Cart or some 3rd party themes. It will
	 * trigger the recalculation of Cart Totals before displayin the menu cart.
	 */
	public function woocommerce_cart_contents_total($cart_contents_total) {
		$this->recalculate_cart_totals();
		return $cart_contents_total;
	}

	/**
	 * Resets the "Recalculate Cart Totals" flag. It's called after the
	 * recalculation to avoid it from happening multiple times.
	 */
	public function reset_recalculate_cart_flag($order_total, $cart) {
		Aelia_SessionManager::delete_value(Definitions::RECALCULATE_CART_TOTALS);
		return $order_total;
	}

	/**
	 * Converts product prices when they are added to the cart. This is required
	 * for compatibility with some 3rd party plugins, which will need this
	 * information to perform their duty.
	 *
	 * @param array cart_item The cart item, which contains, amongst other things,
	 * the product added to cart.
	 * @return array The processed cart item, with the product prices converted in
	 * the selected currency.
	 */
	public function woocommerce_add_cart_item($cart_item) {
		// $cart_item['data'] contains the product added to the cart.
		$product = $cart_item['data'];

		if(is_object($product) && $product instanceof WC_Product) {
			$cart_item['data'] = $this->_currencyprices_manager->convert_product_prices($product, $this->get_selected_currency());
		}

		return $cart_item;
	}

	/**
	 * Converts product prices when they are loaded from the user's session.
	 * This is required for compatibility with some 3rd party plugins, which will
	 * need this information to perform their duty.
	 *
	 * @param array cart_item The cart item, which contains, amongst other things,
	 * the product added to cart.
	 * @param array values The values associated to the cart item key.
	 * @param string key The cart item key.
	 * @return array The processed cart item, with the product prices converted in
	 * the selected currency.
	 */
	public function woocommerce_get_cart_item_from_session($cart_item, $values, $key) {
		return $this->woocommerce_add_cart_item($cart_item);
	}

	/**
	 * It executes some actions as soon as the cart is loaded from the session.
	 * This filter was introduced for compatibility with the Cart Notices plugin.
	 *
	 * Note: the recalculation of totals after the cart has been loaded from
	 * session seems to cause undesired side effects in WooCommerce 2.3 and later,
	 * and should no longer be performed at this stage.
	 *
	 * @deprecated since 4.0.2.150324
	 */
	//public function woocommerce_cart_loaded_from_session() {
	//	$this->recalculate_cart_totals(true);
	//}


	/**
	 * Processes the arguments that will be used in the formulas entered for the
	 * calculation of shipping. When such formulas involve the cart total, such
	 * amount must be converted to base currency before it can be used in any
	 * calculation.
	 * The filter doesn't take action if the Shipping Pricing plugin is installed,
	 * as such plugin can override the shipping methods and perform the required
	 * calculations correctly.
	 *
	 * @param array args The arguments for the calculation of shipping.
	 * @param string sum The formula used for shipping calculation.
	 * @param WC_Shipping shipping_method The shipping method that is going to
	 * calculate the shipping.
	 * @return array The processed arguments.
	 *
	 * @since 4.2.18.160114
	 */
	public function woocommerce_evaluate_shipping_cost_args($args, $sum, $shipping_method) {
		if(empty($shipping_method->shipping_prices_in_currency)) {
			$selected_currency = $this->get_selected_currency();
			$base_currency = $this->base_currency();

			$args['cost'] = $this->convert($args['cost'], $selected_currency, $base_currency);
		}
		return $args;
	}

	/**
	 * Reconfigures a shipping method, converting its parameters (e.g. minimum
	 * amount for free shipping) to the active currency.
	 *
	 * @param WC_Shipping_Method shipping_method A shipping method.
	 * @return WC_Shipping_Method The shipping method with its parameters converted
	 * to the active currency.
	 * @since 3.9.4.160210
	 */
	protected function set_shipping_method_params_in_currency($shipping_method) {
		$base_currency = $this->settings_controller()->base_currency();
		$selected_currency = $this->get_selected_currency();

		// If shipping prices are not already set in the active currency, convert
		// them using exchange rates
		if(empty($shipping_method->shipping_prices_in_currency)) {
			$params_to_convert = array(
				'min_amount',
			);

			foreach($params_to_convert as $shipping_param) {
				if(!empty($shipping_method->$shipping_param)) {
					$shipping_method->$shipping_param = $this->convert($shipping_method->$shipping_param,
																														 $base_currency,
																														 $selected_currency);
				}
			}
		}
		return $shipping_method;
	}

	/**
	 * Loads the shipping methods. This hook handler is implemented to make sure
	 * that all shipping methods' parameters related to pricing (e.g. the minimum
	 * purchase order) are properly converted into selected currency.
	 *
	 * @param array shipping_methods_to_load An array of Shipping Methods class
	 * names or object instances.
	 * @return array
	 */
	public function woocommerce_shipping_methods($shipping_methods) {
		// This filter is no longer requires since WooCommerce 2.6
		if(aelia_wc_version_is('>=', '2.6')) {
			return $shipping_methods;
		}

		foreach($shipping_methods as $key => $method) {
			if(!is_object($method)) {
				$method = new $method();
			}
			$shipping_methods[$key] = $this->set_shipping_method_params_in_currency($method);
		}
		return $shipping_methods;
	}

	/**
	 * Loads the shipping methods (WC 2.6 and later).
	 * This hook handler is implemented to make sure that all shipping methods'
	 * parameters related to pricing (e.g. the minimum purchase order) are
	 * properly converted into selected currency.
	 *
	 * @param array shipping_methods An array of shipping methods instances.
	 * @return array
	 * @since 3.9.4.160210
	 * @since WooCommerce 2.6
	 */
	public function woocommerce_shipping_zone_shipping_methods($shipping_methods, $raw_methods, $allowed_classes, $shipping_zone) {
		foreach($shipping_methods as $key => $method) {
			$shipping_methods[$key] = $this->set_shipping_method_params_in_currency($method);
		}
		return $shipping_methods;
	}

	/**
	 * Intercepts the titles to apply to the Widgets.
	 *
	 * @param string title
	 * @return string The widget title
	 */
	public function widget_title($title) {
		// When displaying the shopping cart widget, recalculate the totals using
		// currently selected Currency
		if($id == 'shopping_cart') {
			$this->recalculate_cart_totals();
		}

		return $title;
	}

	/**
	 * Converts into the Base Currency the price range passed by the Price Filter
	 * widget.
	 */
	protected function convert_price_filter_amounts() {
    if(isset($_GET['max_price']) && isset($_GET['min_price'])) {
			$base_currency = $this->settings_controller()->base_currency();
			$price_filter_currency = get_value(Definitions::ARG_PRICE_FILTER_CURRENCY, $_GET, $base_currency);

			$_GET['max_price'] = floor($this->convert($_GET['max_price'],
																								$price_filter_currency,
																								$base_currency));
			$_GET['min_price'] = ceil($this->convert($_GET['min_price'],
																							 $price_filter_currency,
																							 $base_currency));
		}
	}

	/**
	 * Performs operations when WooCommerce is initialising its integration classes.
	 */
	public function woocommerce_integrations_init() {
		// Load the classes that will add or improve support for 3rd party plugins and themes
		$this->load_integration_classes();
	}

	/**
	 * Overrides WooCommerce integration classes.
	 *
	 * @param array integrations An array of integrations to be loaded by WooCommerce
	 * @return array
	 */
	public function woocommerce_integrations_override($integrations) {
		return $integrations;
	}

	/**
	 * Performs operations when woocommerce has been loaded.
	 */
	public function woocommerce_loaded() {
		// Load the class that will handle currency prices for current WooCommerce version
		$this->currencyprices_manager();

		// Reporting Manager will handle calculations for WooCommerce reports
		$this->load_reporting_manager();

		// Admin Interface Manager will handle the components and widgets for the WP admin pages
		$this->load_admin_interface_manager();

		// If we are on the frontend, and the "force currency by customer country"
		// option is enabled, take the currency based on customer's country
		// @since 4.5.7.171124
		if(self::is_frontend() && !self::editing_order() && !$this->admin_currency_override() &&
			 ($this->force_currency_by_country() != Settings::OPTION_DISABLED)) {
			$selected_currency = $this->get_currency_by_customer_country();
		}

		// Check if user explicitly selected a currency
		if(empty($selected_currency)) {
			$selected_currency = isset($_POST[Definitions::ARG_CURRENCY]) ? $_POST[Definitions::ARG_CURRENCY] : null;
		}

		// Check if currency was passed via the URL
		if(empty($selected_currency) &&
			 self::settings()->get(Settings::FIELD_CURRENCY_VIA_URL_ENABLED)) {
			$selected_currency = isset($_GET[Definitions::ARG_CURRENCY]) ? $_GET[Definitions::ARG_CURRENCY] : null;
		}

		// Update selected Currency
		if(!empty($selected_currency)) {
			// If the selected currency is not valid, go back to WooCommerce base currency
			if(!$this->is_valid_currency($selected_currency)) {
				// Debug
				//$this->trigger_error(Definitions::ERR_INVALID_CURRENCY, E_USER_NOTICE, array($selected_currency));

				$selected_currency = $this->settings_controller()->base_currency();
			}

			$this->save_user_selected_currency($selected_currency);
			/* Set a flag that will trigger the recalculation of the cart totals using
			 * the new currency. This operation cannot be performed right now because
			 * we might not be on a cart page, in which case the cart would not be
			 * available.
			 */
			Aelia_SessionManager::set_value(Definitions::RECALCULATE_CART_TOTALS, 1);
		}

		// Convert Price Filters amounts into base currency
		$this->convert_price_filter_amounts();

		// Hooks that must be set after WooCommerce loaded
		// Add order hooks
		$this->set_order_hooks();
	}

	/**
	 * Registers all the Widgets used by the plugin.
	 */
	public function register_widgets() {
		$this->register_widget('\Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher_Widget');

		// Register the country selector widget
		if($this->force_currency_by_country() != Settings::OPTION_DISABLED) {
			$this->register_widget('\Aelia\WC\CurrencySwitcher\WC_Aelia_Customer_Country_Selector_Widget');
		}
	}

	/**
	 * Returns the path/subfolder from where JavaScript files should be loaded,
	 * depending on WooCommerce version.
	 *
	 * WHY
	 * This method is used to load different versions of JavaScript files when the
	 * changes in new WooCommerce releases require a logic that is not backward
	 * compatible with previous versions. Unfortunately, this happens too often.
	 *
	 * @return string The path from where the JS files should be loaded.
	 * @since 4.2.4.150824
	 * @since WC 2.4
	 */
	protected function get_js_path() {
		global $woocommerce;
		// @var array An array of WooCommerce version => path pairs. The path will
		// be used to load the appropriate scripts for the active WooCommerce version
		$js_paths = array(
			'2.3' => 'WC23',
			'2.4' => 'WC24',
		);

		krsort($js_paths);

		$result = null;
		foreach($js_paths as $wc_version => $path) {
			if(version_compare($woocommerce->version, $wc_version, '>=')) {
				$result = $path;
				break;
			}
		}
		return $result;
	}

	/**
	 * Registers the script and style files required in the backend (even outside
	 * of plugin's pages).
	 */
	protected function register_common_admin_scripts() {
		// Get the path to the JavaScript files specific to this WooCommerce version
		$js_path = $this->get_js_path();

		// Scripts
		wp_register_script('wc-aelia-currency-switcher-admin-overrides',
											 $this->url('plugin') . '/js/admin/wc-aelia-currency-switcher-overrides.js',
											 array(),
											 self::$version,
											 true);

		// Script for Edit Product page
		// @since 4.2.5.150907
		wp_register_script('wc-aelia-currency-switcher-product-edit',
											 $this->url('plugin') . '/js/admin/' . $js_path . '/wc-aelia-currency-switcher-product-edit.js',
											 array('jquery', 'wc-aelia-currency-switcher-admin-overrides'),
											 self::$version,
											 true);

		// Script for Edit Order page
		// @since 4.5.5.171114
		wp_register_script('wc-aelia-currency-switcher-order-edit',
											 $this->url('plugin') . '/js/admin/' . $js_path . '/wc-aelia-currency-switcher-order-edit.js',
											 array('jquery', 'wc-aelia-currency-switcher-admin-overrides'),
											 self::$version,
											 true);

		// Styles
		wp_register_style('wc-aelia-cs-admin',
											$this->url('plugin') . '/design/css/admin.css',
											array(),
											self::$version,
											'all');

		// Load JavaScript for reports
		$reports_to_extend = apply_filters('wc_aelia_cs_reports_to_extend', array(
			'sales_by_date',
			'sales_by_product',
			'sales_by_category',
			'coupon_usage',
			'taxes_by_code',
			'taxes_by_date',
		));

		if(aelia_wc_version_is('>=', '2.2') && self::doing_reports($reports_to_extend)) {
			wp_enqueue_script(static::$plugin_slug . '-jquery-bbq',
												$this->url('js') . '/admin/jquery.ba-bbq.min.js',
												array('jquery'),
												null,
												true);
			wp_enqueue_script(static::$plugin_slug . '-admin-reports',
												$this->url('js') . '/admin/admin-reports.js',
												array('jquery'),
												null,
												true);

			add_action('admin_footer', array($this, 'reports_admin_footer'));
		}
	}

	/**
	 * Adds elements to the report pages. The elements will be used to display
	 * additional filters and options for the reports.
	 *
	 * @since 4.1.0.150701
	 */
	public function reports_admin_footer() {
		include(self::instance()->path('views') . '/admin/reports/report-options.php');
	}

	/**
	 * Registers the script and style files required in the frontend (even outside
	 * of plugin's pages).
	 */
	protected function register_common_frontend_scripts() {
		// Scripts
		wp_register_script('wc-aelia-currency-switcher-widget',
											 $this->url('plugin') . '/js/frontend/wc-aelia-currency-switcher-widget.js',
											 array('jquery'),
											 self::$version,
											 false);
		wp_register_script('wc-aelia-currency-switcher',
											 $this->url('plugin') . '/js/frontend/wc-aelia-currency-switcher.js',
											 array('jquery'),
											 self::$version,
											 true);
		// Styles
		wp_register_style('wc-aelia-cs-frontend',
											$this->url('plugin') . '/design/css/frontend.css',
											array(),
											self::$version,
											'all');
	}

	/**
	 * Registers the script and style files needed by the admin pages of the
	 * plugin.
	 */
	protected function register_plugin_admin_scripts() {
		// Scripts
		wp_register_script('chosen',
											 '//cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.jquery.min.js',
											 array('jquery'),
											 null,
											 true);

		// Styles
		wp_register_style('chosen',
												'//cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.min.css',
												array(),
												null,
												'all');

		// WordPress already includes jQuery UI script, but no CSS for it. Therefore,
		// we need to load it from an external source
		wp_register_style('jquery-ui',
											'//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css',
											array(),
											null,
											'all');

		wp_enqueue_style('jquery-ui');
		wp_enqueue_style('chosen');

		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('jquery-ui-tooltip');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('chosen');

		parent::register_plugin_admin_scripts();
	}

	/**
	 * Determines if one of plugin's admin pages is being rendered.
	 *
	 * @return bool
	 */
	protected function rendering_plugin_admin_page() {
		$screen = get_current_screen();
		$page_id = $screen->id;

		// If page id matches the plugin's slug, then we are in plugin's admin page
		return ($page_id == 'woocommerce_page_' . Definitions::MENU_SLUG);
	}

	/**
	 * Loads some JS and CSS that are required in WooCommerce Admin, even outside
	 * the plugin's settings page.
	 */
	protected function load_common_admin_scripts() {
		wp_enqueue_style('wc-aelia-cs-admin');
		// Load overrides. These must be loaded every time in Admin, as they will alter
		// WooCommerce behaviour by making its pages aware of multi-currency orders
		wp_enqueue_script('wc-aelia-currency-switcher-admin-overrides');

		// Add localization for admin scripts
		$this->localize_admin_scripts();
	}

	/**
	 * Loads Styles and JavaScript for the Admin pages.
	 */
	public function load_admin_scripts() {
		parent::load_admin_scripts();

		$this->load_common_admin_scripts();
	}

	/**
	 * Loads Styles and JavaScript for the Frontend.
	 */
	public function load_frontend_scripts() {
		// Styles
		wp_enqueue_style('wc-aelia-cs-frontend');

		// Scripts
		wp_enqueue_script('wc-aelia-currency-switcher');
		wp_localize_script('wc-aelia-currency-switcher',
											 'wc_aelia_currency_switcher_params',
											 array(
												// Set the Exchange Rate to convert from Base Currency to currently selected Currency
												'current_exchange_rate_from_base' => $this->current_exchange_rate(),
												// Set currently selected currency
												'selected_currency' => $this->get_selected_currency()
											 ));
	}

	/**
	 * Setup function. Called when plugin is enabled.
	 */
	public function setup() {
	}

	/**
	 * Returns the suffix to be appended to a product price. Used when displaying
	 * products on the front page.
	 *
	 * @param WC_Product product The product for which the price suffix should be
	 * returned.
	 * @return string
	 * @since WooCommerce 2.1
	 */
	protected function get_price_suffix(WC_Product $product) {
		if(method_exists($product, 'get_price_suffix')) {
			return $product->get_price_suffix();
		}

		return '';
	}

	/**
	 * Indicates if we are sending an order notification.
	 *
	 * @return int|null An order ID, if a notification is being sent. Null in all
	 * other cases.
	 * @since 4.2.9.150930
	 */
	public static function sending_order_notification() {
		return self::$notification_order_id;
	}

	/**
	 * Returns the instance of a product.
	 *
	 * @param int product_id A product ID.
	 * @return WC_Product|false
	 * @since 4.2.12.151105
	 */
	public static function get_product($product_id) {
		return wc_get_product($product_id);
	}

	/**
	 * Returns the country code for the user, detecting it using the IP Address,
	 * if needed.
	 *
	 * @return string
	 * @deprecated since 4.0.0.150311
	 */
	public function get_billing_country() {
		return $this->get_customer_country();
	}

	/**
	 * Stores customer's country in the session.
	 *
	 * @param string customer_country A country code.
	 * @since 4.0.0.150311
	 */
	protected function store_customer_country($customer_country) {
		Aelia_SessionManager::set_cookie(Definitions::SESSION_CUSTOMER_COUNTRY, $customer_country);
	}

	/**
	 * Returns the country code for the user, detecting it using the IP Address,
	 * if needed.
	 *
	 * @return string
	 * @since 4.0.0.150311
	 */
	public function get_customer_country() {
		if(!empty($this->customer_country)) {
			return $this->customer_country;
		}

		$woocommerce = $this->wc();
		$result = null;
		if(self::doing_ajax() &&
			 (isset($_POST['action']) && ($_POST['action'] === 'woocommerce_update_order_review')) ||
			 // Fix WC 2.4. They silently removed the "action" parameter from the POST
			 // and added a "wc-ajax" in the GET. Yet one more nonsensical change from
			 // the WooCommerce "ninjas"
			 (isset($_REQUEST['wc-ajax']) && ($_REQUEST['wc-ajax'] === 'update_order_review'))) {
			// If user is on checkout page and changes the billing country, get the
			// country code and store it in the session
			if(check_ajax_referer('update-order-review', 'security', false)) {
				// Determine if the prices should be based on billing or shipping country,
				// and check if such country has changed
				if($this->force_currency_by_country() == Settings::OPTION_SHIPPING_COUNTRY) {
					$argument_to_check = Definitions::ARG_CHECKOUT_SHIPPING_COUNTRY;
				}
				else {
					$argument_to_check = Definitions::ARG_CHECKOUT_BILLING_COUNTRY;
				}
				if(isset($_POST[$argument_to_check])) {
					$result = $_POST[$argument_to_check];
				}
			}
		}

		// If changed the country on the cart, take the newly selected country
		if(empty($result) && ($this->force_currency_by_country() === Settings::OPTION_SHIPPING_COUNTRY)) {
			if(!empty($_POST['calc_shipping']) && wp_verify_nonce($_POST['_wpnonce'], 'woocommerce-cart')) {
				$result = wc_clean($_POST['calc_shipping_country']);
			}
		}

		// Check if "customer country" argument was posted
		if(empty($result)) {
			if(isset($_POST[Definitions::ARG_CUSTOMER_COUNTRY])) {
				$result = $_POST[Definitions::ARG_CUSTOMER_COUNTRY];
			}
		}

		// If no billing country was posted, check if one was stored in the session
		if(empty($result)) {
			$result = Aelia_SessionManager::get_cookie(Definitions::SESSION_CUSTOMER_COUNTRY);
		}

		// If no country was passed, and the customer is logged in, take the country
		// from his profile
		if(empty($result)) {
			if(is_user_logged_in() && isset($woocommerce->customer)) {
				if($this->force_currency_by_country() === Settings::OPTION_SHIPPING_COUNTRY) {
					$result = $woocommerce->customer->get_shipping_country();
				}
				else {
					// Use new WC_Customer::get_billing_country() in WC2.7 and later
					// @since 1.7.9.170214
					if(aelia_wc_version_is('>=', '2.7')) {
						$result = $woocommerce->customer->get_billing_country();
					}
					else {
						$result = $woocommerce->customer->get_country();
					}
				}
			}
		}

		// If the country could not be retrieved from customer's details, and
		// geolocation is enabled, detect the country using visitor's IP address
		if(empty($result) && self::settings()->currency_geolocation_enabled()) {
			$result = IP2Location::factory()->get_visitor_country();
		}

		// If everything fails, take WooCommerce base country
		if(empty($result)) {
			$result = $woocommerce->countries->get_base_country();
		}

		$this->store_customer_country($result);

		$this->customer_country = $result;
		return apply_filters('wc_aelia_cs_customer_country', $result, $this);
	}

	/**
	 * Gets the active currency using customer's country.
	 *
	 * @return string
	 */
	protected function get_currency_by_customer_country() {
		$customer_country = $this->get_customer_country();

		$currency = WC_Aelia_Currencies_Manager::factory()->get_country_currency($customer_country);

		// If currency used in the billing country is not enabled, take the default
		// used for GeoIP
		if(!$this->is_valid_currency($currency)) {
			$currency = self::settings()->default_geoip_currency();
		}

		// Debug
		//var_dump("CURRENCY BY CUSTOMER COUNTRY: $currency");

		return $currency;
	}

	/**
	 * Indicates if currency selection by customer country has been enabled.
	 *
	 * @return string The method can return one of the following values:
	 * - disabled: currency by country is disabled.
	 * - billing_country: currency should be determined by billing country.
	 * - shipping_country: currency should be determined by shipping country.
	 */
	protected function force_currency_by_country() {
		// Allow 3rd parties to change the setting on the fly.
		// @since 4.5.16.180307
		return apply_filters('wc_aelia_cs_force_currency_by_country', self::settings()->current_settings(Settings::FIELD_FORCE_CURRENCY_BY_COUNTRY,
																																																		 Settings::OPTION_DISABLED));
	}

	/**
	 * Invalidate product cache. This will ensure that the product prices are
	 * recalculated using the latest exchange rates and settings.
	 *
	 * @since 4.2.8.150917
	 */
	public static function clear_products_cache() {
		global $woocommerce;
		if(version_compare($woocommerce->version, '2.4', '>=')) {
			\WC_Cache_Helper::get_transient_version('product', true);
		}
	}

	/**
	 * When an order notification is being sent, keeps track of the order ID.
	 *
	 * @param int order_id The ID of the order for which the notification is being
	 * sent.
	 * @since 4.2.9.150930
	 */
	public function track_order_notification($order_id = null) {
		// Debug
		//$this->log("TRACKING ORDER NOTIFICATION. ORDER ID: {$order_id}. FILTER: " . current_filter());
		self::$notification_order_id = $order_id;
	}

	/**
	 * Renders the shortcode to display a product price.
	 *
	 * @param array shortcode_args The arguments passed to the shortcode.
	 * @since 4.2.12.151105
	 */
	public function render_shortcode_product_price($shortcode_args) {
		if(!is_array($shortcode_args)) {
			$shortcode_args = array();
		}

		$args = array_merge(array(
			'price_type' => '',
			'formatted' => '1',
			'currency' => get_woocommerce_currency(),
			'show_full_html_price' => '0',
		), $shortcode_args);

		$error_wrapper = '<span class="shortcode error">%s</span>';
		if(empty($args['product_id'])) {
			return sprintf($error_wrapper, __('Product price shortcode - Product ID is required, but none was specified.', self::$text_domain));
		}

		// Retrieve the product for which the prices are being requested
		$product = self::get_product($args['product_id']);
		if(!$product instanceof WC_Product) {
			$error_msg = sprintf(__('Product price shortcode - Invalid Product ID specified: "%s".',
															self::$text_domain),
													 $args['product_id']);
			return sprintf($error_wrapper, $error_msg);
		}

		// Temporarily override the active currency. This will allow to retrieve the
		// product price in the desired currency
		$original_selected_currency = $this->selected_currency;
		$this->selected_currency = $args['currency'];

		// The "show full HTML price" argument allows to show the full product price,
		// inclusive of striked out regular price and sale price, as it's displayed
		// by WooCommerce on the product page
		if($args['show_full_html_price']) {
			$price = $product->get_price_html();
		}
		else {
			// If we just need to show a single price, determine which one should be
			// displayed
			switch($args['price_type']) {
				case 'regular':
					$price = $product->get_regular_price();
					break;
				case 'sale':
					$price = $product->get_sale_price();
					break;
				default:
					$price = $product->get_price();
			}

			// Allow 3rd parties to replace the price
			$price = apply_filters('aelia_cs_pp_shortcode_price', $price, $product, $args['price_type']);

			// Format price, if requested
			if($args['formatted'] == 1) {
				$price = wc_price($price, array('currency' => $args['currency']));
			}
		}
		$this->selected_currency = $original_selected_currency;

		return $price;
	}

	/**
	 * Renders the shortcode to display a product price.
	 *
	 * @param array shortcode_args The arguments passed to the shortcode.
	 * @since 4.3.0.160302
	 */
	public function render_shortcode_currency_amount($shortcode_args) {
		if(!is_array($shortcode_args)) {
			$shortcode_args = array();
		}
		$args = array_merge(array(
			'formatted' => '1',
			'from_currency' => $this->base_currency(),
			'to_currency' => get_woocommerce_currency(),
			'include_markup' => '1',
			'decimals' => null,
		), $shortcode_args);

		$error_wrapper = '<span class="shortcode error">%s</span>';
		if(empty($args['amount'])) {
			return sprintf($error_wrapper, __('Currency amount shortcode - Amount is required, but none was specified.', self::$text_domain));
		}

		if(!is_numeric($args['amount'])) {
			$error_msg = sprintf(__('Currency amount shortcode - Amount is not numeric: "%s".',
															self::$text_domain),
													 $args['amount']);
			return sprintf($error_wrapper, $error_msg);
		}

		$target_currency = $args['to_currency'];
		$decimals = is_numeric($args['decimals']) ? $args['decimals'] : $this->price_decimals($target_currency);

		// The argument keys are forced to lower case, therefore we must use the
		// lower case currency code to find explicit prices
		if(isset($args[strtolower($target_currency)])) {
			$converted_amount = $args[strtolower($target_currency)];
		}
		else {
			$include_markup = (bool)($args['include_markup'] == '1');
			$converted_amount = $this->convert($args['amount'],
																				 $args['from_currency'],
																				 $args['to_currency'],
																				 $decimals,
																				 $include_markup);
		}

		// Format price, if requested
		if($args['formatted'] == 1) {
			$converted_amount = wc_price($converted_amount, array(
				'currency' => $target_currency,
				'decimals' => $decimals,
			));
		}

		return apply_filters('wc_aelia_cs_shortcode_currency_amount', $converted_amount, $shortcode_args);
	}

	/**
	 * Registers the plugin for automatic updates.
	 *
	 * @param array The array of the plugins to update, structured as follows:
	 * array(
	 *   'free' => <Array of free plugins>,
	 *   'premium' => <Array of premium plugins, which require licence activation>,
	 * )
	 * @return array The array of plugins to update, with the details of this
	 * plugin added to it.
	 * @since 4.4.0.161221
	 */
	public function wc_aelia_afc_register_plugins_to_update(array $plugins_to_update) {
		// Add this plugins to the list of the plugins to update automatically
		$plugins_to_update['premium'][self::$plugin_slug] = $this;
		return $plugins_to_update;
	}

	/**
	 * Shows messages to the site administrators.
	 *
	 * @since 4.4.6.170120
	 */
	protected function show_admin_messages() {
		// Inform admins that the Dynamic Pricing integration has been moved to an
		// external plugin
		// @since 4.4.6.170120
		Messages::admin_message(
			$this->_messages_controller->get_message(Definitions::NOTICE_INTEGRATION_ADDONS),
			array(
				'level' => E_USER_NOTICE,
				'code' => Definitions::NOTICE_INTEGRATION_ADDONS,
				'dismissable' => true,
				'permissions' => 'manage_woocommerce',
				'message_header' => __('Good to know', self::$text_domain),
		));

		// Inform admins that the Dynamic Pricing integration has been moved to an
		// external plugin
		// @since 4.4.6.170120
		Messages::admin_message(
			$this->_messages_controller->get_message(Definitions::WARN_DYNAMIC_PRICING_INTEGRATION),
			array(
				'level' => E_USER_WARNING,
				'code' => Definitions::WARN_DYNAMIC_PRICING_INTEGRATION,
				'dismissable' => true,
				'permissions' => 'manage_woocommerce',
				'message_header' => __('Important change', self::$text_domain),
		));

		// Inform admins that the Dynamic Pricing integration has been moved to an
		// external plugin
		// @since 4.5.14.180122
		Messages::admin_message(
			$this->_messages_controller->get_message(Definitions::WARN_YAHOO_FINANCE_DISCONTINUED),
			array(
				'level' => E_USER_WARNING,
				'code' => Definitions::WARN_YAHOO_FINANCE_DISCONTINUED,
				'dismissable' => true,
				'permissions' => 'manage_woocommerce',
				'message_header' => __('Important change', self::$text_domain),
		));
	}

	/**
	 * Triggers actions when the admin section is initialised.
	 *
	 * @since 1.8.6.151105
	 */
	public function admin_init() {
		$this->show_admin_messages();

		// Add filter to ovveride selected currency in the admin area, regardless
		// of any other settings
		// @since 4.5.5.171114
		if($this->admin_currency_override()) {
			add_filter('woocommerce_currency', array($this, 'admin_woocommerce_currency'), 6);
		}
	}

	/**
	 * Returns a product's base currency.
	 *
	 * @param string base_currency The original base currency passed to the filter.
	 * @param int product_id
	 * @return string
	 * @since 4.4.19.170602
	 */
	public function wc_aelia_cs_get_product_base_currency($base_currency, $product_id) {
		return $this->currencyprices_manager()->get_product_base_currency($product_id);
	}

	/**
	 * Indicates if the currency was overridden by an Admin operation.
	 *
	 * @return bool
	 * @since 4.5.5.171114
	 */
	protected function admin_currency_override() {
		return is_admin() && !empty($_GET[Definitions::ARG_ADMIN_CURRENCY]) && current_user_can('manage_woocommerce');
	}

	/**
	 * Replaces the selected currency with the currency selected explicly during
	 * an Admin operation.
	 *
	 * @param string currency
	 * @return string
	 * @since 4.5.5.171114
	 */
	public function admin_woocommerce_currency($currency) {
		// If a currency was explicitly selected, replace any other currency with it
		if(!empty($_GET[Definitions::ARG_ADMIN_CURRENCY])) {
			$this->selected_currency = $_GET[Definitions::ARG_ADMIN_CURRENCY];
		}

		return apply_filters('wc_aelia_cs_admin_selected_currency', $this->get_selected_currency());
	}
}

// Instantiate plugin and add it to the set of globals
$GLOBALS[WC_Aelia_CurrencySwitcher::$plugin_slug] = WC_Aelia_CurrencySwitcher::factory();
