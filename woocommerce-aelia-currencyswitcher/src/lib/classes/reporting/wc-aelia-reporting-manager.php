<?php
namespace Aelia\WC\CurrencySwitcher;
if(!defined('ABSPATH')) exit; // Exit if accessed directly

interface IWC_Aelia_Reporting_Manager  {

}

class WC_Aelia_Reporting_Manager implements IWC_Aelia_Reporting_Manager {
	// @var Aelia\CurrencySwitcher\Reports An instance of the class that will override the reports
	protected $reports;

	/**
	 * Tracks the currencies in which orders were placed. Used for caching.
	 *
	 * @var array
	 * @since 4.4.14.170415
	 */
	protected static $currencies_from_sales;

	// @var array An array of WooCommerce version => Namespace pairs. The namespace will be used to load the appropriate class to override reports
	protected $reports_classes = array(
		'2.2' => 'WC22',
		'2.3' => 'WC23',
		// WC 2.4 and later can use the same reports as WC 2.3
		'2.4' => 'WC23',
		'2.5' => 'WC23',
	);

	/**
	 * Loads the class that will override the reports.
	 *
	 * @param string class_namespace The namespace from which the class will be
	 * loaded. All classes share the same name, and they are separated in different
	 * namespaces.
	 * @return bool True if the class was loaded correctly, false otherwise.
	 */
	protected function load_reports($class_namespace) {
		if(empty($class_namespace)) {
			return false;
		}

		$reports_class = 'Aelia\\WC\\CurrencySwitcher\\' . $class_namespace . '\\Reports';
		if(class_exists($reports_class)) {
			$this->reports = new $reports_class();
			return true;
		}

		return false;
	}

	/**
	 * WC Reports assume Order Totals to be in base currency and simply sum them
	 * together. This is incorrect when Currency Switcher is installed, as each
	 * order total is saved in the currency in which the transaction was completed.
	 * It's therefore necessary, during reporting, to convert all order totals into
	 * the base currency.
	 */
	public function __construct() {
		$woocommerce = WC();
		krsort($this->reports_classes);

		$class_namespace = null;
		foreach($this->reports_classes as $supported_version => $namespace) {
			if(aelia_wc_version_is('>=', $supported_version)) {
				$class_namespace = $namespace;
				break;
			}
		}

		if(!$this->load_reports($class_namespace)) {
			trigger_error(sprintf(__('Reports could not be found for this WooCommerce version (%s). ' .
															 'Supported versions are from %s to: %s.', Definitions::TEXT_DOMAIN),
														$woocommerce->version,
														min(array_keys($this->reports_classes)),
														max(array_keys($this->reports_classes))),
										E_USER_WARNING);
		}

		$this->set_hooks();
	}

	/**
	 * Sets the actions and filters required by the class.
	 *
	 * @since 4.4.14.170415
	 */
	protected function set_hooks() {
		add_action('woocommerce_delete_shop_order_transients', array($this, 'woocommerce_delete_shop_order_transients'), 10, 1);
	}

	/**
	 * Deletes the transients related to orders used by this class.
	 *
	 * @since 4.4.14.170415
	 */
	public function woocommerce_delete_shop_order_transients() {
		delete_transient(Definitions::TRANSIENT_SALES_CURRENCIES);
	}

	/**
	 * Returns the list of all the currencies in which sales were placed.
	 *
	 * @return array An array of currency code => currency name pairs.
	 * @since 4.1.0.150701
	 */
	public static function get_currencies_from_sales() {
		if(empty(self::$currencies_from_sales)) {
			// Use the transients only when NOT in debug mode
			if(!WC_Aelia_CurrencySwitcher::settings()->debug_mode()) {
				$currencies = get_transient(Definitions::TRANSIENT_SALES_CURRENCIES);
			}
			if(empty($currencies)) {
				global $wpdb;
				$SQL = "
					SELECT DISTINCT
						meta_value AS currency
					FROM
						{$wpdb->postmeta} OM
					WHERE
						(OM.meta_key = '_order_currency')
				";

				$currencies = array_merge($wpdb->get_col($SQL),
																	WC_Aelia_CurrencySwitcher::settings()->get_enabled_currencies());
				$currencies = WC_Aelia_Currencies_Manager::get_currency_names($currencies);
				// Cache the list of currencies for one day. The list will also be cleared
				// when the order transients are deleted by WooCommerce
				// @see WC_Aelia_Reporting_Manager::woocommerce_delete_shop_order_transients()
				set_transient(Definitions::TRANSIENT_SALES_CURRENCIES, $currencies, DAY_IN_SECONDS);
			}

			self::$currencies_from_sales = $currencies;
		}

		return self::$currencies_from_sales;
	}
}
