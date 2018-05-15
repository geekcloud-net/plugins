<?php
namespace Aelia\WC\CurrencySwitcher;
if(!defined('ABSPATH')) exit; // Exit if accessed directly

class Reports {
	protected $reports_views_path;

	// @var bool Indicates if reports are being generated
	public static $running_reports = false;

	// @var The WooCommerce version for which this reports class has been implemented
	protected $wc_version = '20';

	/**
	 * Sets the flag that indicates if reports are being generated. This information
	 * will be used to determine the actions to take regarding currency conversion
	 * (reports should be prepared in base currency).
	 *
	 * @param bool enabled The value to which the flag should be set.
	 * @return bool
	 */
	protected function set_reporting_flag($enabled = true) {
		self::$running_reports = $enabled;
	}

	/**
	 * Returns the path to WooCommerce plugin.
	 *
	 * @return string
	 */
	protected function woocommerce_path() {
		global $woocommerce;
		return $woocommerce->plugin_path();
	}

	/**
	 * Overrides the WooCommerce dashboard reports.
	 */
	public function override_dashboard_reports() {
	}

	/**
	 * Sets the hooks required by the class.
	 */
	protected function set_hooks() {
		// Dashboard reports
		add_action('wp_dashboard_setup', array($this, 'override_dashboard_reports'));

		if(WC_Aelia_CurrencySwitcher::doing_reports()) {
			add_filter('woocommerce_currency', array($this, 'woocommerce_currency'), 20);
		}
	}

	/**
	 * Loads (includes) a View file.
	 *
	 * @param string view_file_name The name of the view file to include.
	 */
	protected function load_view($view_file_name) {
		$file_to_load = $this->get_view($view_file_name);
		include($file_to_load);
	}

	protected function get_view($view_file_name) {
		return $this->reports_views_path . '/' . $view_file_name;
	}

	/* WC Reports assume Order Totals to be in base currency and simply sum them
	 * together. This is incorrect when Currency Switcher is installed, as each
	 * order total is saved in the currency in which the transaction was completed.
	 * It's therefore necessary, during reporting, to convert all order totals into
	 * the base currency.
	 */
	public function __construct() {
		global $wpdb;
		//$wpdb->show_errors();

		// TODO Determine Views Path dynamically, depending on WooCommerce version
		$this->reports_views_path = WC_Aelia_CurrencySwitcher::instance()->path('views') . '/admin/' . $this->wc_version . '/reports';

		$this->set_hooks();
	}

	/**
	 * Returns the currency selected for the reports.
	 *
	 * @param string default The currency code to return by default, if none was
	 * explicitly specified.
	 * @return string The code of the currency for the reports.
	 * @since 4.1.0.150701
	 */
	protected function get_report_currency($default = Definitions::DEF_REPORT_CURRENCY) {
		return get_arr_value(Definitions::ARG_REPORT_CURRENCY, $_REQUEST, $default);
	}

	/**
	 * Override the currency on reports pages.
	 *
	 * @param string currency A currency code.
	 * @return string The code of the currency for the reports.
	 * @since 4.1.0.150701
	 */
	public function woocommerce_currency($currency) {
		$report_currency = $this->get_report_currency(Definitions::DEF_REPORT_CURRENCY);
		if($report_currency != Definitions::DEF_REPORT_CURRENCY) {
			$currency = $report_currency;
		}
		// Debug
		//var_dump("REPORT CURRENCY: $report_currency");
		return $currency;
	}
}
