<?php
/**
 * Plugin Name: WooCommerce - ShipStation Integration
 * Plugin URI: https://woocommerce.com/products/shipstation-integration/
 * Version: 4.1.19
 * Description: Adds ShipStation label printing support to WooCommerce. Requires server DomDocument support.
 * Author: WooCommerce
 * Author URI: https://woocommerce.com/
 * Text Domain: woocommerce-shipstation
 * WC tested up to: 3.3
 * WC requires at least: 2.6
 *
 * @todo Investigate feasibility of line item tracking before marking order complete.
 *
 * Woo: 18734:9de8640767ba64237808ed7f245a49bb
 *
 * @package WC_Shipstation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Required functions.
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates.
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '9de8640767ba64237808ed7f245a49bb', '18734' );

// WC active check.
if ( ! is_woocommerce_active() ) {
	return;
}

/**
 * Include shipstation class.
 *
 * @since 1.0.0
 */
function woocommerce_shipstation_init() {
	define( 'WC_SHIPSTATION_VERSION', '4.1.19' );
	define( 'WC_SHIPSTATION_FILE', __FILE__ );

	if ( ! defined( 'WC_SHIPSTATION_EXPORT_LIMIT' ) ) {
		define( 'WC_SHIPSTATION_EXPORT_LIMIT', 100 );
	}

	load_plugin_textdomain( 'woocommerce-shipstation', false, basename( dirname( __FILE__ ) ) . '/languages' );

	include_once( 'includes/class-wc-shipstation-integration.php' );
}

add_action( 'plugins_loaded', 'woocommerce_shipstation_init' );

/**
 * Define integration.
 *
 * @since 1.0.0
 *
 * @param  array $integrations Integrations.
 * @return array Integrations.
 */
function woocommerce_shipstation_load_integration( $integrations ) {
	$integrations[] = 'WC_ShipStation_Integration';

	return $integrations;
}

add_filter( 'woocommerce_integrations', 'woocommerce_shipstation_load_integration' );

/**
 * Listen for API requests.
 *
 * @since 1.0.0
 */
function woocommerce_shipstation_api() {
	include_once( 'includes/class-wc-shipstation-api.php' );
}

add_action( 'woocommerce_api_wc_shipstation', 'woocommerce_shipstation_api' );

/**
 * Added ShipStation custom plugin action links.
 *
 * @since 4.1.17
 * @version 4.1.17
 *
 * @param array $links Links.
 *
 * @return array Links.
 */
function woocommerce_shipstation_api_plugin_action_links( $links ) {
	$setting_link = admin_url( 'admin.php?page=wc-settings&tab=integration&section=shipstation' );
	$plugin_links = array(
		'<a href="' . $setting_link . '">' . __( 'Settings', 'woocommerce-shipstation' ) . '</a>',
		'<a href="https://woocommerce.com/my-account/tickets">' . __( 'Support', 'woocommerce-shipstation' ) . '</a>',
		'<a href="https://docs.woocommerce.com/document/shipstation-for-woocommerce/">' . __( 'Docs', 'woocommerce-shipstation' ) . '</a>',
	);

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woocommerce_shipstation_api_plugin_action_links' );
