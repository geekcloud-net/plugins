<?php
namespace Aelia\WC\CurrencySwitcher;
if(!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Implements support for BolderElements Table Rates Shipping plugin.
 *
 * @since 4.3.4.160527
 */
class WC_Aelia_CS_BE_Table_Rates_Integration {
	/**
	 * Returns the instance of the Currency Switcher plugin.
	 *
	 * @return WC_Aelia_CurrencySwitcher
	 */
	protected function currency_switcher() {
		return WC_Aelia_CurrencySwitcher::instance();
	}

	/**
	 * Returns the instance of the settings controller loaded by the plugin.
	 *
	 * @return Aelia\WC\CurrencySwitcher\Settings
	 */
	protected function settings_controller() {
		return WC_Aelia_CurrencySwitcher::settings();
	}

	/**
	 * Returns the base currency.
	 *
	 * @return string
	 */
	protected function get_base_currency() {
		if(empty($this->base_currency)) {
			$this->base_currency = $this->settings_controller()->base_currency();
		}
		return $this->base_currency;
	}

	/**
	 * Returns the active currency.
	 *
	 * @return string
	 */
	protected function get_selected_currency() {
		if(empty($this->selected_currency)) {
			$this->selected_currency = $this->currency_switcher()->get_selected_currency();
		}
		return $this->selected_currency;
	}

	/**
	 * Converts an amount from one currency to another.
	 *
	 * @param float amount The amount to convert.
	 * @param string from_currency The source currency. If empty, the base currency
	 * is taken.
	 * @param string to_currency The destination currency. If empty, the currently
	 * selected currency is taken.
	 * @return float The amount converted in the destination currency.
	 */
	protected function convert($amount, $from_currency = null, $to_currency = null) {
		if(empty($from_currency)) {
			$from_currency = $this->get_base_currency();
		}

		if(empty($to_currency)) {
			$to_currency = $this->get_selected_currency();
		}

		return $this->currency_switcher()->convert($amount,
																							 $from_currency,
																							 $to_currency);
	}

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->set_hooks();
	}

	protected function logger() {
		return $this->currency_switcher()->get_logger();
	}

	/**
	 * Set the hooks required by the class.
	 */
	protected function set_hooks() {
		if(WC_Aelia_CurrencySwitcher::is_frontend()) {
			// WC 2.5 and earlier
			add_filter('woocommerce_shipping_methods', array($this, 'woocommerce_shipping_methods'), 50);
			// WC 2.6 and later
			add_filter('woocommerce_shipping_zone_shipping_methods', array($this, 'woocommerce_shipping_zone_shipping_methods'), 50, 4);
		}
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
		// If shipping prices are not already set in the active currency, convert
		// them using exchange rates
		if(empty($shipping_method->shipping_prices_in_currency)) {
			// Filter the "subtotal" condition, to ensure it's converted to the active
			// currency
			// @since 4.5.8.171127
			add_filter('betrs_condition_tertiary_subtotal', array($this, 'betrs_condition_tertiary_subtotal'), 10, 2);

			// The logic below is deprecated since BE Table Rates 4.0 and should no
			// longer be used. Unfortunately, the BE Table Rates plugin doesn't expose
			// the version number, so we can only "dismiss" older versions by disabling
			// the code (fetching the version number using get_plugin_data() would be
			// to "heavy" and not worth it).
			// @deprecated since 4.5.8.171127
			// @deprecated since BE Table Rates 4.0
			//// "Global" threshold for free shipping
			//$shipping_method->ship_free = $this->convert($shipping_method->ship_free);
			//if(!empty($shipping_method->table_rates) && is_array($shipping_method->table_rates)) {
			//
			//	foreach($shipping_method->table_rates as $shipping_id => $values) {
			//		$this->logger()->debug('Processing BE Table Rate Shipping cost', array(
			//			'Shipping ID' => $shipping_id,
			//			'Values' => $values,
			//		));
			//
			//		// Table rate shipping thresholds
			//		if($values['cond'] === 'price') {
			//			$values['min'] = $this->convert($values['min']);
			//			$values['max'] = $this->convert($values['max']);
			//
			//			$this->logger()->debug('Condition "price" found. Shipping conditions (min/max) converted.', array(
			//				'Values' => $values,
			//			));
			//
			//		}
			//		$shipping_method->table_rates[$shipping_id] = $values;
			//	}
			//}
		}
		return $shipping_method;
	}

	/**
	 * Checks if the current version of the BE Table Rates Shipping is supported.
	 *
	 * @return bool
	 * @since 4.4.5.170118
	 */
	protected function be_table_rates_version_supported() {
		return class_exists('\BE_Table_Rate_Method');
	}

	/**
	 * Determines if a shipping method should be processed. This function identifies
	 * the BE Table Rates shipping methods and skips the others.
	 *
	 * @param string|object method The shipping method to check.
	 * @return bool
	 * @since 4.4.5.170118
	 */
	protected function should_process_shipping_method($method) {
		return (is_string($method) && ($method === 'BE_Table_Rate_Shipping')) ||
					 (is_object($method) && ($method instanceof \BE_Table_Rate_Shipping));
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
		// Check that the installed BE Table Rates version is supported
		if(!$this->be_table_rates_version_supported()) {
			return $shipping_methods;
		}

		foreach($shipping_methods as $key => $method) {
			if($this->should_process_shipping_method($method)) {
				if(!is_object($method)) {
					$method = new $method();
				}
				$shipping_methods[$key] = $this->set_shipping_method_params_in_currency($method);
			}
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
		// Check that the installed BE Table Rates version is supported
		if(!$this->be_table_rates_version_supported()) {
			return $shipping_methods;
		}

		foreach($shipping_methods as $key => $method) {
			if($method instanceof \BE_Table_Rate_Method) {
				$shipping_methods[$key] = $this->set_shipping_method_params_in_currency($method);
			}
		}
		return $shipping_methods;
	}

	/**
	 * Converts the value of the "subtotal" condition to the active currency.
	 *
	 * @param float value The value to convert to the active currency.
	 * @param array condition The parameter describing the condition.
	 * @return float The converted condition amount.
	 * @since 4.5.8.171127
	 */
	public function betrs_condition_tertiary_subtotal($value, $condition) {
		if(is_numeric($value)) {
			$value = $this->convert($value);
		}

		return $value;
	}
}
