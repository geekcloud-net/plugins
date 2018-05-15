<?php
/*
Plugin Name: Yoast SEO: Local for WooCommerce
Version: 7.4
Plugin URI: https://yoast.com/wordpress/local-seo-for-woocommerce/
Description: This Local SEO module adds all the needed functionality to get your site ready for Local Search Optimization and create Pick-up-locations for WooCommerce
Author: Team Yoast and Arjan Snaterse
Author URI: https://yoast.com

Copyright 2012-2014 Joost de Valk & Arjan Snaterse

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * All functionality for fetching location data and creating an KML file with it.
 *
 * @package    WordPress SEO
 * @subpackage WordPress SEO Local for WooCommerce
 */

if ( ! defined( 'WPSEO_LOCAL_WOOCOMMERCE_VERSION' ) ) {
	define( 'WPSEO_LOCAL_WOOCOMMERCE_VERSION', '7.4' );
}

if ( ! defined( 'WPSEO_LOCAL_WOOCOMMERCE_PATH' ) ) {
	define( 'WPSEO_LOCAL_WOOCOMMERCE_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WPSEO_LOCAL_WOOCOMMERCE_URI' ) ) {
	define( 'WPSEO_LOCAL_WOOCOMMERCE_URI', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'WPSEO_LOCAL_WOOCOMMERCE_FILE' ) ) {
	define( 'WPSEO_LOCAL_WOOCOMMERCE_FILE', __FILE__ );
}

// Define Yoast SEO Local paths, which is loaded via composer
if ( ! defined( 'WPSEO_LOCAL_PATH' ) ) {
	define( 'WPSEO_LOCAL_PATH', plugin_dir_path( __FILE__ ) . 'vendor/yoast/wordpress-seo-local/' );
}
if ( ! defined( 'WPSEO_LOCAL_FILE' ) ) {
	define( 'WPSEO_LOCAL_FILE', WPSEO_LOCAL_PATH . 'local-seo.php' );
}

if ( ! class_exists( 'WPSEO_Local_WooCommerce' ) ) {

	class WPSEO_Local_WooCommerce {

		private $_min_wpseo_version = '3.3.2';
		private $_min_woocommerce_version = '2.6';
		private $_plugin_name = 'Yoast SEO: Local for WooCommerce';

		public function __construct() {

			//activation
			register_activation_hook( __FILE__, array( $this, 'deactivate_sibling_plugins' ) );

			//deactivation
			register_deactivation_hook( __FILE__, array( $this, 'flush_transient_cache_for_shipping_methods' ) );

			//pre-load product to override the product defined inside Local-SEO plugin
			require_once( WPSEO_LOCAL_WOOCOMMERCE_PATH .'/woocommerce/product/class-product.php' );

			// Load SEO Local
			if ( get_option( 'wordpress-seo-local-deactivated' ) ) {

				if ( file_exists( WPSEO_LOCAL_WOOCOMMERCE_PATH . '/vendor/autoload_52.php' ) ) {
					require_once WPSEO_LOCAL_WOOCOMMERCE_PATH . '/vendor/autoload_52.php';
				}

				if ( file_exists( WPSEO_LOCAL_FILE ) ) {
					require_once( WPSEO_LOCAL_PATH . 'local-seo.php' );
				}

			}

			// Show notice when Local SEO is still activated
			add_action( 'admin_init', array( $this, 'check_local_seo_is_activated' ) );

			// Actions
			add_action( 'init', array( $this, 'load_textdomain_local_seo_woocommerce' ) );
			add_action( 'plugins_loaded', array( $this, 'init_local_seo_woocommerce' ), 11 );

			// Filters
			add_filter( 'woocommerce_locate_template', array( $this, 'woocommerce_locate_template' ), 10, 3 );

			//Hide WooCommerce' own Local Pickup routine, if our shipping method is enabled
			$settings = get_option('woocommerce_yoast_wcseo_local_pickup_settings');
			if( isset( $settings['enabled'] ) && ( $settings['enabled'] == 'yes' ) ) {
				add_filter( 'woocommerce_shipping_zone_shipping_methods',
					array( $this, 'shipping_zone_shipping_methods' ), 10, 4 );
				add_filter( 'woocommerce_shipping_method_description',
					array( $this, 'alter_shipping_method_description' ), 10, 2 );
			}

		}

		public function woocommerce_locate_template( $template, $template_name, $template_path ) {
			global $woocommerce;

			$_template = $template;

			if ( ! $template_path ) {
				$template_path = $woocommerce->template_url;
			}

			// Look within passed path within the theme - this is priority
			$template = locate_template(
				array(
					$template_path . $template_name,
					$template_name
				)
			);

			// Get the template from this plugin, if it exists
			$plugin_path  = WPSEO_LOCAL_WOOCOMMERCE_PATH . '/woocommerce/templates/';
			if ( ! $template && file_exists( $plugin_path . $template_name ) ) {
				$template = $plugin_path . $template_name;
			}

			// Use default template
			if ( ! $template ) {
				$template = $_template;
			}

			// Return what we found
			return $template;

		}

		public function check_local_seo_is_activated() {
			if ( is_plugin_active( 'wordpress-seo-local/local-seo.php' ) || is_plugin_active( 'wpseo-local/local-seo.php' ) ) {
				add_action( 'admin_init', array( $this, 'show_disable_local_seo_message' ), 1 );
			}
		}

		public function show_disable_local_seo_message() {
			if ( is_admin() ) {
				add_action( 'admin_notices', array( $this, 'disable_local_seo_message' ) );

				$this->self_deactivate();
			}
		}

		public function disable_local_seo_message() {
			/* translators: %1$s expands to Yoast Local SEO, %2$s expands to Yoast Local SEO for WooCommerce. */
			$message = esc_html__( 'The %1$s plugin is still active. In order to let the %2$s plugin work correctly, you need to disable the %1$s plugin.', 'yoast-local-seo-woocommerce' );
			$message = sprintf( $message, 'Yoast SEO: Local', 'Yoast SEO: Local for WooCommerce' );
			yoast_wpseo_activation_failed_notice( $message );
		}

		public function self_deactivate() {
			static $is_deactivated;

			if ( $is_deactivated === null ) {
				$is_deactivated = true;
				deactivate_plugins( plugin_basename( WPSEO_LOCAL_WOOCOMMERCE_FILE ) );
				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
			}
		}

		public function deactivate_sibling_plugins() {
			deactivate_plugins( 'wordpress-seo-local/local-seo.php' );
			update_option( 'wordpress-seo-local-deactivated', true, false );
		}

		public function alter_shipping_method_description( $method_description, $instance ) {

			if (
				is_a( $instance, 'WC_Shipping_Local_Pickup' ) ||
				is_a( $instance, 'WC_Shipping_Legacy_Local_Pickup' ) ||
				is_a( $instance, 'WC_Shipping_Legacy_Local_Delivery' ) ) {
					/* translators: %s expands to "Yoast SEO: Local SEO for WooCommerce". */
					$method_description = $method_description . ' ' . sprintf( __('%s disabled this shipping method. To configure local pickup, go to the Local Store Pickup settings.', 'yoast-local-seo-woocommerce' ), 'Yoast SEO: Local SEO for WooCommerce' );

			}

			return $method_description;

		}

		public function shipping_zone_shipping_methods($methods, $raw_methods, $allowed_classes, $instance) {

			$local_pickup_found = false;

			if ( is_array( $methods ) && ( ! empty( $methods )) ) {
				foreach( $methods as $index => $method ) {

					// Woo's Local Pickup has been found, issue a warning for the user
					if ( is_a( $method,  'WC_Shipping_Local_Pickup' ) ) {
						$local_pickup_found = true;
						/* translators: %s expands to "Yoast SEO: Local SEO for WooCommerce". */
						$method->method_description = $method->method_description . ' ' . sprintf( __('%s disabled this shipping method. To configure local pickup, go to the Local Store Pickup settings.', 'yoast-local-seo-woocommerce' ), 'Yoast SEO: Local SEO for WooCommerce' );
						$method->enabled = 'no';

						$methods[ $index ] = $method;
					}
				}

			}

			// Woo'c local pickup has not been found,... so deactivate it before someone decides to use it
			if ( !$local_pickup_found ) {
				add_filter( 'woocommerce_shipping_methods', array( $this, 'hide_woos_local_pickup' ), 10, 1 );
			}

			return $methods;

		}

		public function hide_woos_local_pickup( $available_methods ) {

			unset( $available_methods['local_pickup'] );

			return $available_methods;

		}


		public function flush_transient_cache_for_shipping_methods() {

			global $wpdb;
			$wpdb->query("DELETE FROM ".$wpdb->prefix."options WHERE option_name LIKE '_transient_wc_ship%'");

			delete_option( 'wordpress-seo-local-deactivated' );
		}

		public function load_textdomain_local_seo_woocommerce() {
			load_plugin_textdomain( 'yoast-local-seo-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Initialize the WooCommerce specific classes
		 */
		public function init_local_seo_woocommerce() {

			// Check if WooCommerce is active
			if ( in_array( 'woocommerce/woocommerce.php',
				apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

				$version = $this->_get_woocommerce_version_number();

				if ( version_compare( $version, $this->_min_woocommerce_version, '>=' ) ) {

					//@todo: we can do better than all these 'requires'

					// We have the right WooCommerce version, go gadget go...
					require_once 'woocommerce/includes/class-wc-post-types.php';
					$wpseo_local_woocommerce_post_types = new Yoast_WCSEO_Local_Post_Types();
					$wpseo_local_woocommerce_post_types->init();

					require_once 'woocommerce/shipping/class-wc-shipping.php';
					require_once 'woocommerce/shipping/class-wc-shipping-method.php';
					$wpseo_local_woocommerce_shipping = new Yoast_WCSEO_Local_Shipping();
					$wpseo_local_woocommerce_shipping->init();

					require_once 'woocommerce/admin/class-wc-transport.php';
					require_once 'woocommerce/admin/class-wc-transport-list.php';
					$wpseo_local_woocommerce_transport = new Yoast_WCSEO_Local_Transport();
					$wpseo_local_woocommerce_transport->init();

					require_once 'woocommerce/admin/class-admin-columns.php';
					require_once 'woocommerce/emails/class-wc-emails.php';
					require_once 'woocommerce/includes/wpseo-local-woocommerce-functions.php';

					require_once 'woocommerce/admin/class-woocommerce-settings.php';
					new WPSEO_Local_Admin_Woocommerce_Settings();

				}
				else {
					// User has an old WooCommerce version
					add_action( 'all_admin_notices', array( $this, 'error_outdated_woocommerce' ) );
				}
			}
			else {
				// User does not have WooCommerce installed
				add_action( 'all_admin_notices', array( $this, 'error_missing_woocommerce' ) );
			}
		}

		private function _get_woocommerce_version_number() {

			// If get_plugins() isn't available, require it
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}

			// Create the plugins folder and file variables
			$plugin_folder = get_plugins( '/' . 'woocommerce' );
			$plugin_file   = 'woocommerce.php';

			// If the plugin version number is set, return it
			if ( isset( $plugin_folder[ $plugin_file ]['Version'] ) ) {
				return $plugin_folder[ $plugin_file ]['Version'];

			}
			else {
				// Otherwise return null
				return null;
			}
		}

		/**
		 * Throw an error if WooCommerce is not installed.
		 *
		 */
		public function error_missing_woocommerce() {
			$this->_admin_message( sprintf( __( 'Please <a href="%s">install and activate WooCommerce</a> and then go to the Shipping Methods to enable the shipping methods from the "%s" plugin',
				'yoast-local-seo-woocommerce' ),
				admin_url( 'plugin-install.php?tab=search&type=term&s=woocommerce&plugin-search-input=Search+Plugins' ),
				$this->_plugin_name ), 'error' );
		}

		/**
		 * Throw an error if WooCommerce is out of date.
		 *
		 */
		public function error_outdated_woocommerce() {
			$this->_admin_message( sprintf( __( 'Please upgrade the WooCommerce plugin to the latest version to allow the "%s" plugin to work.',
				'yoast-local-seo-woocommerce' ), $this->_plugin_name ), 'error' );

		}

		/**
		 * Generic admin message
		 *
		 */
		private function _admin_message( $message, $class ) {
			echo '<div class="' . esc_attr( $class ) . '"><p>' . $message . '</p></div>';
		}
	}
}

//start the engine...
global $wordpress_seo_local_woocommerce;
$wordpress_seo_local_woocommerce = new WPSEO_Local_WooCommerce();
