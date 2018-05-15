<?php
/*
Plugin Name: YITH Advanced Refund System for WooCommerce Premium
Plugin URI: http://yithemes.com/themes/plugins/yith-advanced-refund-system-for-woocommerce/
Description: YITH Advanced Refund System for WooCommerce makes refund requests accessible and easily manageable both from the user’s and the customer’s side.
Author: YITHEMES
Text Domain: yith-advanced-refund-system-for-woocommerce
Version: 1.0.8
Author URI: http://yithemes.com/
WC requires at least: 3.0.0
WC tested up to: 3.3.0
*/

/*
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


/* === DEFINE === */
! defined( 'YITH_WCARS_VERSION' )          && define( 'YITH_WCARS_VERSION', '1.0.8' );
! defined( 'YITH_WCARS_INIT' )             && define( 'YITH_WCARS_INIT', plugin_basename( __FILE__ ) );
! defined( 'YITH_WCARS_SLUG' )             && define( 'YITH_WCARS_SLUG', 'yith-advanced-refund-system-for-woocommerce' );
! defined( 'YITH_WCARS_SECRETKEY' )        && define( 'YITH_WCARS_SECRETKEY', 'VUeAMbd9Y0WQAiaoPNED' );
! defined( 'YITH_WCARS_FILE' )             && define( 'YITH_WCARS_FILE', __FILE__ );
! defined( 'YITH_WCARS_PATH' )             && define( 'YITH_WCARS_PATH', plugin_dir_path( __FILE__ ) );
! defined( 'YITH_WCARS_URL' )              && define( 'YITH_WCARS_URL', plugins_url( '/', __FILE__ ) );
! defined( 'YITH_WCARS_ASSETS_URL' )       && define( 'YITH_WCARS_ASSETS_URL', YITH_WCARS_URL . 'assets/' );
! defined( 'YITH_WCARS_ASSETS_JS_URL' )    && define( 'YITH_WCARS_ASSETS_JS_URL', YITH_WCARS_URL . 'assets/js/' );
! defined( 'YITH_WCARS_TEMPLATE_PATH' )    && define( 'YITH_WCARS_TEMPLATE_PATH', YITH_WCARS_PATH . 'templates/' );
! defined( 'YITH_WCARS_WC_TEMPLATE_PATH' ) && define( 'YITH_WCARS_WC_TEMPLATE_PATH', YITH_WCARS_PATH . 'templates/woocommerce/' );
! defined( 'YITH_WCARS_OPTIONS_PATH' )     && define( 'YITH_WCARS_OPTIONS_PATH', YITH_WCARS_PATH . 'plugin-options' );
! defined( 'YITH_WCARS_PREMIUM' )          && define( 'YITH_WCARS_PREMIUM', '1' );
! defined( 'YITH_WCARS_CUSTOM_POST_TYPE' ) && define( 'YITH_WCARS_CUSTOM_POST_TYPE', 'yith_refund_request' );

$wp_upload_dir = wp_upload_dir ();

! defined ( 'YITH_WCARS_UPLOADS_DIR' )                   && define ( 'YITH_WCARS_UPLOADS_DIR', $wp_upload_dir[ 'basedir' ] . '/ywcars/' );
! defined ( 'YITH_WCARS_UPLOADS_URL' )                   && define ( 'YITH_WCARS_UPLOADS_URL', $wp_upload_dir[ 'baseurl' ] . '/ywcars/' );
! defined ( 'YITH_WCARS_ONE_KILOBYTE_IN_BYTES' )         && define ( 'YITH_WCARS_ONE_KILOBYTE_IN_BYTES', 1024 );
! defined ( 'YITH_WCARS_UPLOAD_ERR_ALL_FILES_OK' )       && define ( 'YITH_WCARS_UPLOAD_ERR_ALL_FILES_OK', 20 );
! defined ( 'YITH_WCARS_UPLOAD_ERR_NOT_A_IMAGE' )        && define ( 'YITH_WCARS_UPLOAD_ERR_NOT_A_IMAGE', 21 );
! defined ( 'YITH_WCARS_UPLOAD_ERR_WRONG_IMAGE_FORMAT' ) && define ( 'YITH_WCARS_UPLOAD_ERR_WRONG_IMAGE_FORMAT', 22 );


require_once YITH_WCARS_PATH . '/functions.php';

/* Initialize */

yith_initialize_plugin_fw( plugin_dir_path( __FILE__ ) );

yit_deactive_free_version( 'YITH_WCARS_FREE_INIT', plugin_basename( __FILE__ ) );

/* Plugin Framework Version Check */
yit_maybe_plugin_fw_loader( plugin_dir_path( __FILE__ ) );

/* Register the plugin when activated */
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );
register_activation_hook( __FILE__, 'flush_rewrite_rules' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

/* Start the plugin on plugins_loaded */
add_action( 'plugins_loaded', 'yith_ywars_install', 11 );