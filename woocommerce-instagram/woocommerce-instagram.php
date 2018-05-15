<?php
/**
 * Plugin Name: WooCommerce Instagram
 * Plugin URI: https://woocommerce.com/products/woocommerce-instagram/
 * Description: Connect your Instagram account with WooCommerce. Showcase Instagrams from all over the world, showing visitors how your customers are showcasing your products.
 * Author: WooCommerce
 * Author URI: https://woocommerce.com/
 * Text Domain: woocommerce-instagram
 * Domain Path: /languages
 * Version: 1.0.15
 * Stable tag: 1.0.15
 * License: GPL v3 or later - http://www.gnu.org/licenses/old-licenses/gpl-3.0.html
 * WC tested up to: 3.3
 * WC requires at least: 2.6
 *
 * Copyright (c) 2017 WooCommerce
 *
 * @package WooCommerce_Instagram
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Required functions.
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

// Plugin updates.
woothemes_queue_update( plugin_basename( __FILE__ ), 'ecaa2080668997daf396b8f8a50d891a', 260061 );

global $woocommerce_instagram;
require_once( 'classes/class-woocommerce-instagram.php' );

define( 'WOOCOMMERCE_INSTAGRAM_VERSION', '1.0.15' );
$woocommerce_instagram = new Woocommerce_Instagram( __FILE__ );
$woocommerce_instagram->version = WOOCOMMERCE_INSTAGRAM_VERSION;
