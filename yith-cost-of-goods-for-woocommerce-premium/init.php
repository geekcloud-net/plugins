<?php
/*
Plugin Name: YITH Cost of Goods for WooCommerce
Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-cost-of-goods/
Description: YITH Cost of Goods for WooCommerce let you know the profits of your products.
Author: YITHEMES
Version: 1.0.6
Author URI: https://yithemes.com/
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
! defined( 'YITH_COG_VERSION' )       && define( 'YITH_COG_VERSION', '1.0.6' );
! defined( 'YITH_COG_INIT' )     && define( 'YITH_COG_INIT', plugin_basename( __FILE__ ) );
! defined( 'YITH_COG_SLUG' )         && define( 'YITH_COG_SLUG', 'yith-cost-of-goods-for-woocommerce' );
! defined( 'YITH_COG_FILE' )         && define( 'YITH_COG_FILE', __FILE__ );
! defined( 'YITH_COG_PATH' )         && define( 'YITH_COG_PATH', plugin_dir_path( __FILE__ ) );
! defined( 'YITH_COG_URL' )          && define( 'YITH_COG_URL', plugins_url( '/', __FILE__ ) );
! defined( 'YITH_COG_ASSETS_URL' )    && define( 'YITH_COG_ASSETS_URL', YITH_COG_URL . 'assets/' );
! defined( 'YITH_COG_TEMPLATE_PATH' ) && define( 'YITH_COG_TEMPLATE_PATH', YITH_COG_PATH . '/templates/' );
! defined( 'YITH_COG_OPTIONS_PATH' )  && define( 'YITH_COG_OPTIONS_PATH', YITH_COG_PATH . 'panel' );
! defined( 'YITH_COG_PREMIUM' )          && define( 'YITH_COG_PREMIUM', '1' );
! defined( 'YITH_COG_SECRETKEY' )        && define( 'YITH_COG_SECRETKEY', 'tybx6AqZnSmknZzQJ2xK' );


/* Plugin Framework Version Check */
! function_exists( 'yit_maybe_plugin_fw_loader' ) && require_once( YITH_COG_PATH . 'plugin-fw/init.php' );
yit_maybe_plugin_fw_loader( YITH_COG_PATH  );

/* Load text domain */
load_plugin_textdomain( 'yith-cost-of-goods-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

if ( ! function_exists( 'YITH_COG' ) ) {
    /**
     * Unique access to instance of YITH_COG class
     *
     * @return YITH_COG | YITH_COG_Premium
     * @since 1.0.0
     */
    function YITH_COG() {
        // Load required classes and functions
        require_once( YITH_COG_PATH . 'includes/class.yith-cog.php' );
        if ( defined( 'YITH_COG_PREMIUM' ) && file_exists( YITH_COG_PATH
                . 'includes/class.yith-cog-premium.php') ) {
            require_once( YITH_COG_PATH . 'includes/class.yith-cog-premium.php' );
            return YITH_COG_Premium::instance();
        }
        return YITH_COG::instance();
    }
}

/**
 * Instance main plugin class
 */
YITH_COG();





