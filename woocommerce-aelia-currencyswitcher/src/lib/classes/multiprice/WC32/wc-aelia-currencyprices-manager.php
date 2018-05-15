<?php
namespace Aelia\WC\CurrencySwitcher\WC32;
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
 *
 * @since WooCommerce 3.2
 * @since 4.5.0.170901
 */
class WC_Aelia_CurrencyPrices_Manager extends \Aelia\WC\CurrencySwitcher\WC27\WC_Aelia_CurrencyPrices_Manager {
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
			$cost = $shipping_method->get_cost();
			if(!is_array($cost)) {
				// Convert a simple total cost into currency
				$shipping_method->set_cost($this->currencyswitcher()->convert($cost,
																																			$base_currency,
																																			$selected_currency));
			}
			else {
				// Based on documentation, class can contain an array of costs in case
				// of shipping costs applied per item. In such case, each one has to
				// be converted
				foreach($cost as $cost_key => $cost_value) {
					$cost[$cost_key] = $this->currencyswitcher()->convert($cost_value,
																																$base_currency,
																																$selected_currency);
				}
				$shipping_method->set_cost($cost);
			}

			// Convert shipping taxes
			$taxes = $shipping_method->get_taxes();
			if(!is_array($taxes)) {
				// Convert a simple total taxes into currency
				$shipping_method->set_taxes($this->currencyswitcher()->convert($taxes,
																																			 $base_currency,
																																			 $selected_currency));
			}
			else {
				// Based on documentation, class can contain an array of taxes in case
				// of shipping taxes applied per item. In such case, each one has to
				// be converted
				foreach($taxes as $taxes_key => $taxes_value) {
					$taxes[$taxes_key] = $this->currencyswitcher()->convert($taxes_value,
																																	$base_currency,
																																	$selected_currency);
				}
				$shipping_method->set_taxes($taxes);
			}

			// Flag the shipping method to keep track of the fact that its costs have
			// been converted into selected Currency. This is necessary because this
			// is often called multiple times within the same page load, passing the
			// same data that was already processed
			$shipping_method->shipping_prices_in_currency = true;
		}

		return $available_shipping_methods;
	}
}
