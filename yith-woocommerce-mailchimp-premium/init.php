<?php
/**
 * Plugin Name: YITH WooCommerce Mailchimp Premium
 * Plugin URI: http://yithemes.com/themes/plugins/yith-woocommerce-mailchimp/
 * Description: YITH WooCommerce Mailchimp allows you to integrate the most popular newsletter campaign manager on your ecommerce.
 * Version: 1.1.2
 * Author: Yithemes
 * Author URI: http://yithemes.com/
 * Text Domain: yith-wcmc
 * Domain Path: /languages/
 * WC requires at least: 2.5.0
 * WC tested up to: 3.3.0
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Mailchimp
 * @version 1.0.0
 */

/*  Copyright 2013  Your Inspiration Themes  (email : plugins@yithemes.com)

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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );

if ( ! defined( 'YITH_WCMC' ) ) {
	define( 'YITH_WCMC', true );
}

if ( ! defined( 'YITH_WCMC_VERSION' ) ) {
	define( 'YITH_WCMC_VERSION', '1.1.2' );
}

if ( ! defined( 'YITH_WCMC_URL' ) ) {
	define( 'YITH_WCMC_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'YITH_WCMC_DIR' ) ) {
	define( 'YITH_WCMC_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'YITH_WCMC_INC' ) ) {
	define( 'YITH_WCMC_INC', YITH_WCMC_DIR . 'includes/' );
}

if ( ! defined( 'YITH_WCMC_INIT' ) ) {
	define( 'YITH_WCMC_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_WCMC_SECRET_KEY' ) ) {
	define( 'YITH_WCMC_SECRET_KEY', 'GBrhnaYKS9ij30SDIOs5' );
}

if ( ! defined( 'YITH_WCMC_SLUG' ) ) {
	define( 'YITH_WCMC_SLUG', 'yith-woocommerce-mailchimp' );
}

if ( ! defined( 'YITH_WCMC_PREMIUM' ) ) {
	define( 'YITH_WCMC_PREMIUM', 1 );
}

if ( ! defined( 'YITH_WCMC_PREMIUM_INIT' ) ) {
	define( 'YITH_WCMC_PREMIUM_INIT', plugin_basename( __FILE__ ) );
}

/* Plugin Framework Version Check */
if ( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( YITH_WCMC_DIR . 'plugin-fw/init.php' ) ) {
	require_once( YITH_WCMC_DIR . 'plugin-fw/init.php' );
}
yit_maybe_plugin_fw_loader( YITH_WCMC_DIR );

if ( ! function_exists( 'yith_mailchimp_constructor' ) ) {
	function yith_mailchimp_constructor() {
		load_plugin_textdomain( 'yith-woocommerce-mailchimp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		if ( ! class_exists( 'Mailchimp' ) ) {
			require_once( YITH_WCMC_INC . 'mailchimp/Mailchimp.php' );
		}
		require_once( YITH_WCMC_INC . 'functions.yith-wcmc.php' );
		require_once( YITH_WCMC_INC . 'class.yith-wcmc.php' );
		require_once( YITH_WCMC_INC . 'class.yith-wcmc-premium.php' );
		require_once( YITH_WCMC_INC . 'class.yith-wcmc-widget.php' );

		// Let's start the game
		YITH_WCMC_Premium();

		if ( is_admin() ) {
			require_once( YITH_WCMC_INC . 'class.yith-wcmc-admin.php' );
			require_once( YITH_WCMC_INC . 'class.yith-wcmc-admin-premium.php' );

			YITH_WCMC_Admin_Premium();
		}
	}
}
add_action( 'yith_wcmc_init', 'yith_mailchimp_constructor' );

if ( ! function_exists( 'yith_mailchimp_install' ) ) {
	function yith_mailchimp_install() {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		if ( ! function_exists( 'yit_deactive_free_version' ) ) {
			require_once 'plugin-fw/yit-deactive-plugin.php';
		}
		yit_deactive_free_version( 'YITH_WCMC_FREE_INIT', plugin_basename( __FILE__ ) );

		if ( function_exists( 'yith_deactive_jetpack_module' ) ) {
			global $yith_jetpack_1;
			yith_deactive_jetpack_module( $yith_jetpack_1, 'YITH_WCMC_PREMIUM_INIT', plugin_basename( __FILE__ ) );
		}

		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', 'yith_wcmc_install_woocommerce_admin_notice' );
		} else {
			do_action( 'yith_wcmc_init' );
		}
	}
}
add_action( 'plugins_loaded', 'yith_mailchimp_install', 11 );

if ( ! function_exists( 'yith_wcmc_install_woocommerce_admin_notice' ) ) {
	function yith_wcmc_install_woocommerce_admin_notice() {
		?>
		<div class="error">
			<p><?php _e( 'YITH WooCommerce MailChimp is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-mailchimp' ); ?></p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'yith_wcmc_install_free_admin_notice' ) ) {
	function yith_wcmc_install_free_admin_notice() {
		?>
		<div class="error">
			<p><?php _e( 'You can\'t activate the free version of YITH WooCommerce MailChimp while you are using the premium one.', 'yith-woocommerce-mailchimp' ); ?></p>
		</div>
		<?php
	}
}