<?php
/*
 * Plugin Name: Sincroniza Woocommerce con Mercadolibre: Woomelly
 * Version: 1.2.0
 * Plugin URI:
 * Description: Powerful plugin that allows you to synchronize all your woocommerce products with your mercadolibre store in an easy and fast way.
 * Author: MakePlugins
 * Author URI: https://codecanyon.net/user/makeplugins
 * Requires at least: 4.0
 * Tested up to: 4.9.5
 * WC requires at least: 3.0.0
 * WC tested up to: 3.3.5
 *
 * Text Domain: woomelly
 * Domain Path: /languages/
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Include the main class.
if ( ! class_exists( 'Woomelly' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-woomelly.php';
}

// Define WM_PLUGIN_FILE.
if ( ! defined( 'WM_PLUGIN_FILE' ) ) {
	define( 'WM_PLUGIN_FILE', dirname( __FILE__ ) );
}

/**
 * Main instance of Woomelly.
 *
 * Returns the main instance of Woomelly to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return Woomelly
 */
function Woomelly() {
	return Woomelly::instance( __FILE__, '1.2.0' );
}

Woomelly();