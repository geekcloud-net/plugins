<?php
/**
 * Backwards compat.
 *
 *
 * @since 2.1.6
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_plugins = get_option( 'active_plugins', array() );
foreach ( $active_plugins as $key => $active_plugin ) {
	if ( strstr( $active_plugin, '/amazon-s3-storage.php' ) ) {
		$active_plugins[ $key ] = str_replace( '/amazon-s3-storage.php', '/woocommerce-amazon-s3-storage.php', $active_plugin );
	}
}
update_option( 'active_plugins', $active_plugins );
