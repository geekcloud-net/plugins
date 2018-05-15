<?php
/*
Plugin Name: YITH WooCommerce Dynamic Pricing and Discounts Premium
Description: YITH WooCommerce Dynamic Pricing and Discounts offers a powerful tool to directly modify prices and discounts of your store
Version: 1.4.4
Author: YITHEMES
Author URI: http://yithemes.com/
Text Domain: ywdpd
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
WC requires at least: 3.0.0
WC tested up to: 3.2.5
*/

/*
 * @package YITH WooCommerce Dynamic Pricing and Discounts Premium
 * @since   1.0.0
 * @author  YITHEMES
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}


if ( ! defined( 'YITH_YWDPD_DIR' ) ) {
    define( 'YITH_YWDPD_DIR', plugin_dir_path( __FILE__ ) );
}

/* Plugin Framework Version Check */
if( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( YITH_YWDPD_DIR . 'plugin-fw/init.php' ) ) {
    require_once( YITH_YWDPD_DIR . 'plugin-fw/init.php' );
}
yit_maybe_plugin_fw_loader( YITH_YWDPD_DIR  );


// Free version deactivation if installed __________________

if( ! function_exists( 'yit_deactive_free_version' ) ) {
    require_once 'plugin-fw/yit-deactive-plugin.php';
}
yit_deactive_free_version( 'YITH_YWDPD_FREE_INIT', plugin_basename( __FILE__ ) );


// Define constants ________________________________________
if ( defined( 'YITH_YWDPD_VERSION' ) ) {
    return;
}else{
    define( 'YITH_YWDPD_VERSION', '1.4.4' );
}

if ( ! defined( 'YITH_YWDPD_PREMIUM' ) ) {
    define( 'YITH_YWDPD_PREMIUM', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_YWDPD_INIT' ) ) {
    define( 'YITH_YWDPD_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_YWDPD_FILE' ) ) {
    define( 'YITH_YWDPD_FILE', __FILE__ );
}

if ( ! defined( 'YITH_YWDPD_URL' ) ) {
    define( 'YITH_YWDPD_URL', plugins_url( '/', __FILE__ ) );
}

if ( ! defined( 'YITH_YWDPD_ASSETS_URL' ) ) {
    define( 'YITH_YWDPD_ASSETS_URL', YITH_YWDPD_URL . 'assets' );
}

if ( ! defined( 'YITH_YWDPD_TEMPLATE_PATH' ) ) {
    define( 'YITH_YWDPD_TEMPLATE_PATH', YITH_YWDPD_DIR . 'templates/' );
}

if ( ! defined( 'YITH_YWDPD_INC' ) ) {
    define( 'YITH_YWDPD_INC', YITH_YWDPD_DIR . '/includes/' );
}

if ( ! defined( 'YITH_YWDPD_SUFFIX' ) ) {
    $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
    define( 'YITH_YWDPD_SUFFIX', $suffix );
}


if ( ! defined( 'YITH_YWDPD_SLUG' ) ) {
    define( 'YITH_YWDPD_SLUG', 'yith-woocommerce-dynamic-pricing-and-discounts' );
}

if ( ! defined( 'YITH_YWDPD_SECRET_KEY' ) ) {
    define( 'YITH_YWDPD_SECRET_KEY', 'V1CV8DBllgRSoLqeoCKZ' );
}

if ( ! defined( 'YITH_YWDPD_DEBUG' ) ) {
    define( 'YITH_YWDPD_DEBUG', false );
}



if ( ! function_exists( 'yith_ywdpd_install' ) ) {
    function yith_ywdpd_install() {

        if ( !function_exists( 'WC' ) ) {
            add_action( 'admin_notices', 'yith_ywdpd_install_woocommerce_admin_notice' );
        } else {
            do_action( 'yith_ywdpd_init' );
        }

	    // check for update table
	    if( function_exists( 'yith_ywdpd_check_update_to_cpt' ) ) {
		    yith_ywdpd_check_update_to_cpt();
	    }
    }

    add_action( 'plugins_loaded', 'yith_ywdpd_install', 12 );
}

register_activation_hook( __FILE__, 'yith_ywdpd_reset_option_version' );
function yith_ywdpd_reset_option_version(){
	delete_option( 'yit_ywdpd_option_version' );
}

function yith_ywdpd_premium_constructor() {

    // Woocommerce installation check _________________________

    if ( !function_exists( 'WC' ) ) {
        function yith_ywdpd_install_woocommerce_admin_notice() {
            ?>
            <div class="error">
                <p><?php _e( 'YITH WooCommerce Dynamic Pricing and Discounts Premium is enabled but not effective. It requires WooCommerce in order to work.', 'ywdpd' ); ?></p>
            </div>
        <?php
        }

        add_action( 'admin_notices', 'yith_ywdpd_install_woocommerce_admin_notice' );
        return;
    }


    // Load YWDPD text domain ___________________________________
    load_plugin_textdomain( 'ywdpd', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	if( ! class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}

    require_once( YITH_YWDPD_INC . 'functions.yith-wc-dynamic-pricing.php' );
    require_once( YITH_YWDPD_INC . 'class-yith-wc-dynamic-pricing.php' );
    require_once( YITH_YWDPD_INC . 'class-yith-wc-dynamic-discounts.php' );
	require_once( YITH_YWDPD_INC . 'class-yith-wc-dynamic-pricing-helper.php' );
    require_once( YITH_YWDPD_INC . 'class-yith-wc-dynamic-pricing-admin.php' );
	require_once( YITH_YWDPD_INC . 'class-yith-wc-dynamic-pricing-frontend.php' );
	require_once( YITH_YWDPD_INC . 'admin/class.ywdpd-discount-list-table.php' );

    if( defined( 'YITH_WPV_PREMIUM') ){
        require_once( YITH_YWDPD_INC . 'compatibility/yith-woocommerce-product-vendors.php' );
    }
    if( defined( 'YITH_WCBR_PREMIUM_INIT') ){
        require_once( YITH_YWDPD_INC . 'compatibility/yith-woocommerce-brands-add-on-premium.php' );
    }

	if ( is_admin() ) {
		YITH_WC_Dynamic_Pricing_Admin();
	}

    YITH_WC_Dynamic_Pricing();
    YITH_WC_Dynamic_Pricing_Frontend();
    YITH_WC_Dynamic_Discounts();

}
add_action( 'yith_ywdpd_init', 'yith_ywdpd_premium_constructor' );