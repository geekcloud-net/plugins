<?php
/**
 * Plugin Name: WooCommerce Point of Sale
 * Plugin URI: http://codecanyon.net/item/woocommerce-point-of-sale-pos/7869665&ref=actualityextensions/
 * Description: WooCommerce Point of Sale is an extension which allows you to place orders through a Point of Sale interface swiftly using the WooCommerce products and orders database. This extension is most suitable for retailers who have both an online and offline store.
 * Version: 4.2.7.2
 * Author: Actuality Extensions
 * Author URI: http://actualityextensions.com/
 * Tested up to: 4.9.4
 *
 * Text Domain: wc_point_of_sale
 * Domain Path: /lang/
 *
 * Copyright: (c) 2013-2018 Actuality Extensions (info@actualityextensions.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package     WC-Point-Of-Sale
 * @author      Actuality Extensions
 * @category    Plugin
 * @copyright   Copyright (c) 2013-2018, Actuality Extensions
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 * WC requires at least: 3.3.1
 * WC tested up to: 3.3.1
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (function_exists('is_multisite') && is_multisite()) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    if (!is_plugin_active('woocommerce/woocommerce.php'))
        return;
} else {
    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))))
        return; // Check if WooCommerce is active    
}

// Load plugin class files
require_once('includes/class-wc-pos.php');

require 'updater/updater.php';
global $aebaseapi;
$aebaseapi->add_product(__FILE__);
/**
 * Returns the main instance of WC_POS to prevent the need to use globals.
 *
 * @since    3.0.5
 * @return object WC_POS
 */
add_filter('woocommerce_stock_amount', 'floatval', 1);
function WC_POS()
{
    $instance = WC_POS::instance(__FILE__, '4.2.7');
    return $instance;
}

// Global for backwards compatibility.
global $wc_point_of_sale, $wc_pos_db_version;

$wc_pos_db_version = get_option('wc_pos_db_version');
$wc_point_of_sale = WC_POS();
$GLOBALS['wc_pos'] = WC_POS();

register_deactivation_hook(__FILE__,  'wc_pos_deactivate');

function wc_pos_deactivate(){
    wp_delete_post((int)get_option('wc_pos_custom_product_id'), true);
}