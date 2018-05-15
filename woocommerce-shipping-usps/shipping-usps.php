<?php
/**
 * Backwards compat.
 *
 *
 * @since 4.4.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_plugins = get_option( 'active_plugins', array() );
foreach ( $active_plugins as $key => $active_plugin ) {
	if ( strstr( $active_plugin, '/shipping-usps.php' ) ) {
		$active_plugins[ $key ] = str_replace( '/shipping-usps.php', '/woocommerce-shipping-usps.php', $active_plugin );
	}
}
update_option( 'active_plugins', $active_plugins );
