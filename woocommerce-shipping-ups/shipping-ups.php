<?php
/**
 * Backwards compat.
 *
 * @since 3.2.0
 *
 * @package WC_Shipping_UPS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_plugins = get_option( 'active_plugins', array() );
foreach ( $active_plugins as $key => $active_plugin ) {
	if ( strstr( $active_plugin, '/shipping-ups.php' ) ) {
		$active_plugins[ $key ] = str_replace( '/shipping-ups.php', '/woocommerce-shipping-ups.php', $active_plugin );
	}
}
update_option( 'active_plugins', $active_plugins );
