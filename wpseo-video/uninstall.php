<?php
/**
 * Code used when the plugin is removed (not just deactivated but actively deleted through the WordPress Admin).
 *
 * @package Video SEO for WordPress SEO by Yoast
 * @subpackage Uninstall
 * @since 1.6.4
 */

if ( ! current_user_can( 'activate_plugins' ) || ( ! defined( 'ABSPATH' ) || ! defined( 'WP_UNINSTALL_PLUGIN' ) ) ) {
	exit();
}

delete_option( 'wpseo_video' );
