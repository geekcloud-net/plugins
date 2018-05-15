<?php
/*
	Plugin Name: WooCommerce EnvioPack
	Plugin URI: http://wanderlust-webdesign.com/
	Description: EnvioPack te permite cotizar el valor de un envío con una amplia cantidad de empresas de correo de una forma simple y estandarizada.
	Version: 0.7
	Author: Wanderlust Web Design
	Author URI: https://wanderlust-webdesign.com
	WC tested up to: 3.3.3
	Copyright: 2007-2018 wanderlust-webdesign.com.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

require_once( 'includes/functions.php' );
require_once( 'includes/bulk.php' );
require_once( 'includes/shipment-tracking.php' );
require_once( 'includes/get-rates.php' );
require_once( 'includes/generate-labels.php' );

/**
 * Plugin page links
*/
function woocommerce_enviopack_plugin_links( $links ) {

	$plugin_links = array(
		'<a href="http://wanderlust-webdesign.com/">' . __( 'Soporte', 'woocommerce-shipping-enviopack' ) . '</a>',
	);

	return array_merge( $plugin_links, $links );
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woocommerce_enviopack_plugin_links' );

/**
 * WooCommerce is active
*/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	/**
	 * woocommerce_init_shipping_table_rate function.
	 */
	function woocommerce_enviopack_init() {
		include_once( 'includes/class-wc-shipping-enviopack.php' );
	}
  add_action( 'woocommerce_shipping_init', 'woocommerce_enviopack_init' ); 

	/**
	 * woocommerce_enviopack_add_method function.
	 */
	function woocommerce_enviopack_add_method( $methods ) {
		$methods[ 'enviopack_wanderlust' ] = 'WC_Shipping_EnvioPack';
		return $methods;
	}

	add_filter( 'woocommerce_shipping_methods', 'woocommerce_enviopack_add_method' );

	/**
	 * woocommerce_enviopack_scripts function.
	 */
	function woocommerce_enviopack_scripts() {
		wp_enqueue_script( 'jquery-ui-sortable' );
	}

	add_action( 'admin_enqueue_scripts', 'woocommerce_enviopack_scripts' );
	//add_action( 'woocommerce_api_enviopack', 'enviopack_handle_callback' );
	
	$enviopack_settings = get_option( 'woocommerce_enviopack_settings', array() );
	
}