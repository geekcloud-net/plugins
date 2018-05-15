<?php if(!defined('ABSPATH')) exit; // Exit if accessed directly
/*
Plugin Name: Aelia Currency Switcher for WooCommerce
Plugin URI: https://aelia.co/shop/currency-switcher-woocommerce/
Description: WooCommerce Currency Switcher. Allows to switch currency on the fly and perform all transactions in such currency.
Author: Aelia
Author URI: https://aelia.co
Version: 4.5.17.180404
License: GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
Text Domain: woocommerce-aelia-currencyswitcher
Domain Path: /languages
WC requires at least: 2.4
WC tested up to: 3.3.4
*/

require_once(dirname(__FILE__) . '/src/lib/classes/install/aelia-wc-currencyswitcher-requirementscheck.php');
// If requirements are not met, deactivate the plugin
if(Aelia_WC_CurrencySwitcher_RequirementsChecks::factory()->check_requirements()) {
	require_once dirname(__FILE__) . '/src/plugin-main.php';

	// Register this plugin file for auto-updates, if such capability exists
	if(!empty($GLOBALS['woocommerce-aelia-currencyswitcher']) && method_exists($GLOBALS['woocommerce-aelia-currencyswitcher'], 'set_main_plugin_file')) {
		// Set the path and name of the main plugin file (i.e. this file), for update
		// checks. This is needed because this is the main plugin file, but the updates
		// will be checked from within plugin-main.php
		$GLOBALS['woocommerce-aelia-currencyswitcher']->set_main_plugin_file(__FILE__);
	}
}
