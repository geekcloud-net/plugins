<?php
/**
 * Plugin Name: YITH Infinite Scrolling Premium
 * Plugin URI: https://yithemes.com/themes/plugins/yith-infinite-scrolling/
 * Description: YITH Infinite Scrolling add infinite scroll to your page.
 * Version: 1.1.3
 * Author: YITHEMES
 * Author URI: https://yithemes.com/
 * Text Domain: yith-infinite-scrolling
 * Domain Path: /languages/
 *
 * @author Yithemes
 * @package YITH Infinite Scrolling Premium
 * @version 1.1.3
 */
/*  Copyright 2015  Your Inspiration Themes  ( email: plugins@yithemes.com )

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

if ( ! function_exists( 'yit_deactive_free_version' ) ) {
	require_once 'plugin-fw/yit-deactive-plugin.php';
}
yit_deactive_free_version( 'YITH_INFS_FREE_INIT', plugin_basename( __FILE__ ) );

if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );


if ( ! defined( 'YITH_INFS_VERSION' ) ){
	define( 'YITH_INFS_VERSION', '1.1.3' );
}

if ( ! defined( 'YITH_INFS_PREMIUM' ) ) {
	define( 'YITH_INFS_PREMIUM', '1' );
}

if ( ! defined( 'YITH_INFS' ) ) {
	define( 'YITH_INFS', true );
}

if ( ! defined( 'YITH_INFS_FILE' ) ) {
	define( 'YITH_INFS_FILE', __FILE__ );
}

if ( ! defined( 'YITH_INFS_URL' ) ) {
	define( 'YITH_INFS_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'YITH_INFS_DIR' ) ) {
	define( 'YITH_INFS_DIR', plugin_dir_path( __FILE__ )  );
}

if ( ! defined( 'YITH_INFS_TEMPLATE_PATH' ) ) {
	define( 'YITH_INFS_TEMPLATE_PATH', YITH_INFS_DIR . 'templates' );
}

if ( ! defined( 'YITH_INFS_ASSETS_URL' ) ) {
	define( 'YITH_INFS_ASSETS_URL', YITH_INFS_URL . 'assets' );
}

if ( ! defined( 'YITH_INFS_INIT' ) ) {
	define( 'YITH_INFS_INIT', plugin_basename( __FILE__ ) );
}

if( ! defined( 'YITH_INFS_OPTION_NAME' ) ) {
    define( 'YITH_INFS_OPTION_NAME', 'yit_infs_options' );
}

if ( ! defined( 'YITH_INFS_SLUG' ) ) {
	define( 'YITH_INFS_SLUG', 'yith-infinite-scrolling' );
}

if ( ! defined( 'YITH_INFS_SECRET_KEY' ) ) {
	define( 'YITH_INFS_SECRET_KEY', 'eoTgIND9moLoW4mkCtdF' );
}

/* Plugin Framework Version Check */
if( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( YITH_INFS_DIR . 'plugin-fw/init.php' ) ) {
    require_once( YITH_INFS_DIR . 'plugin-fw/init.php' );
}
yit_maybe_plugin_fw_loader( YITH_INFS_DIR  );

function yith_infs_premium_init() {

	load_plugin_textdomain( 'yith-infinite-scrolling', false, dirname( plugin_basename( __FILE__ ) ). '/languages/' );

	// Load required classes and functions
    require_once( 'includes/functions.yith-infs.php' );
	require_once( 'includes/class.yith-infs.php' );

	// Let's start the game!
	YITH_INFS();
}
add_action( 'plugins_loaded', 'yith_infs_premium_init', 11 );
