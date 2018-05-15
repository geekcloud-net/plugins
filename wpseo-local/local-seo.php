<?php
/**
 * @package WPSEO_Local\Main
 */

/**
 * Plugin Name: Yoast SEO: Local
 * Version: 7.4
 * Plugin URI: https://yoast.com/wordpress/local-seo/
 * Description: This Local SEO module adds all the needed functionality to get your site ready for Local Search Optimization
 * Author: Team Yoast and Arjan Snaterse
 * Author URI: https://yoast.com
 *
 * Copyright 2012-2016 Joost de Valk & Arjan Snaterse
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * All functionality for fetching location data and creating an KML file with it.
 *
 * @package    Yoast SEO
 * @subpackage Yoast SEO Local
 */

if ( ! defined( 'WPSEO_LOCAL_VERSION' ) ) {
	define( 'WPSEO_LOCAL_VERSION', '7.4' );
}

if ( ! defined( 'WPSEO_LOCAL_PATH' ) ) {
	define( 'WPSEO_LOCAL_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WPSEO_LOCAL_FILE' ) ) {
	define( 'WPSEO_LOCAL_FILE', __FILE__ );
}

if ( file_exists( WPSEO_LOCAL_PATH . '/vendor/autoload_52.php' ) ) {
	require WPSEO_LOCAL_PATH . '/vendor/autoload_52.php';
}

if ( ! function_exists( 'yoast_wpseo_local_dectivate_sibling_plugins' ) ) {
	/**
	 * Deactivate our sibling plugin: "Yoast SEO: Local for WooCommerce", because they may not be active simultaneously
	 *
	 * @since 3.3.1
	 */
	function yoast_wpseo_local_dectivate_sibling_plugins() {

		if ( defined( 'WPSEO_LOCAL_WOOCOMMERCE_FILE' ) ) {
			deactivate_plugins( WPSEO_LOCAL_WOOCOMMERCE_FILE );
		}
	}
}

register_activation_hook( __FILE__, 'yoast_wpseo_local_dectivate_sibling_plugins' );

// Actions moved from includes/ajax-functions.php and includes/wpseo-local-functions.php
// so those files can be autoloaded (as they will contain just functions then).
if ( function_exists( 'wpseo_copy_location_callback' ) ) {
	add_action( 'wp_ajax_wpseo_copy_location', 'wpseo_copy_location_callback' );
	add_action( 'wp_ajax_nopriv_wpseo_copy_location', 'wpseo_copy_location_callback' );
}
if ( function_exists( 'wpseo_enqueue_geocoder' ) ) {
	add_action( 'wp_footer', 'wpseo_enqueue_geocoder' );
	add_action( 'admin_footer', 'wpseo_enqueue_geocoder' );
}

if ( ! function_exists( 'wpseo_local_seo_init' ) ) {
	/**
	 * Initialize the Local SEO module on plugins loaded, so WP SEO should have set its constants and loaded its main classes.
	 *
	 * @since 0.2
	 */
	function wpseo_local_seo_init() {
		global $wpseo_local_core;

		// Load text domain.
		load_plugin_textdomain( 'yoast-local-seo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		if ( defined( 'WPSEO_VERSION' ) ) {

			if ( version_compare( WPSEO_VERSION, '1.4.99', '>=' ) ) {
				$wpseo_local_core = new WPSEO_Local_Core();

				new WPSEO_Local_Admin();
				new WPSEO_Local_Metaboxes();
				new WPSEO_Local_Frontend();
				new WPSEO_Local_JSON_LD();
				new WPSEO_Local_Storelocator();
				new WPSEO_Local_Taxonomy();
				new WPSEO_Local_Sanitize_Options();
				new WPSEO_Local_Import_Export_Admin();
				new WPSEO_Local_Import();
				new WPSEO_Local_Export();
				new WPSEO_Local_Admin_Page();
				new WPSEO_Local_Admin_General_Settings();
				new WPSEO_Local_Admin_API_Settings();
				new WPSEO_Local_Search();
			}
			else {
				add_action( 'all_admin_notices', 'yoast_wpseo_local_upgrade_error' );
			}
		}
		else {
			add_action( 'all_admin_notices', 'wpseo_local_missing_error' );
		}
	}

	add_action( 'init', 'wpseo_local_seo_init' );
}

if ( ! function_exists( 'wpseo_local_seo_init_widgets' ) ) {
	/**
	 * Register all widgets used for Local SEO plugin
	 *
	 * @since 3.1
	 */
	function wpseo_local_seo_init_widgets() {
		$widgets = array(
			'WPSEO_Show_Address',
			'WPSEO_Show_Map',
			'WPSEO_Show_OpeningHours',
			'WPSEO_Show_Open_Closed',
		);

		if ( wpseo_has_multiple_locations() ) {
			$widgets[] = 'WPSEO_Storelocator_Form';
			$widgets[] = 'WPSEO_Show_Locations_By_Category';
		}

		foreach ( $widgets as $widget ) {
			register_widget( $widget );
		}
	}

	add_action( 'widgets_init', 'wpseo_local_seo_init_widgets' );
}


if ( ! function_exists( 'wpseo_local_missing_error' ) ) {
	/**
	 * Throw an error if Yoast SEO is not installed.
	 *
	 * @since 0.2
	 */
	function wpseo_local_missing_error() {

		$message = sprintf( /* translators: %1$s resolves to the link to search the plugin directory for Yoast SEO, %2$s resolves to the closing tag for this link */
			__( 'Please %1$sinstall &amp; activate Yoast SEO%2$s and then go to the plugins section to enable this plugin again.', 'yoast-local-seo' ), '<a href="' . admin_url( 'plugin-install.php?tab=search&type=term&s=yoast+seo&plugin-search-input=Search+Plugins' ) . '">', '</a>' );

		echo '<div class="error"><p>' . $message . '</p></div>';
	}
}

if ( ! function_exists( 'yoast_wpseo_local_upgrade_error' ) ) {
	/**
	 * Throw an error if Yoast SEO is out of date.
	 *
	 * @since 1.5.4
	 */
	function yoast_wpseo_local_upgrade_error() {
		/* translators: %1$s resolves to Yoast SEO, %2$s resolves to Local SEO */
		echo '<div class="error"><p>' . sprintf( __( 'Please upgrade the %1$s plugin to the latest version to allow the %2$s module to work.', 'yoast-local-seo' ), 'Yoast SEO', 'Local SEO' ) . '</p></div>';
	}
}

if ( ! function_exists( 'yoast_wpseo_local_activate_license' ) ) {
	/**
	 * Instantiate the plugin license manager for the current plugin and activate it's license.
	 */
	function yoast_wpseo_local_activate_license() {

		if ( ! class_exists( 'Yoast_Plugin_License_Manager' ) ) {
			return;
		}

		$license_manager = new Yoast_Plugin_License_Manager( new Yoast_Product_WPSEO_Local() );
		$license_manager->activate_license();
	}
}

/* ***************************** PLUGIN (DE-)ACTIVATION *************************** */
if ( ! function_exists( 'yoast_wpseo_local_activate' ) ) {
	/**
	 * Run single site / network-wide activation of the plugin.
	 *
	 * @param bool $networkwide Whether the plugin is being activated network-wide.
	 */
	function yoast_wpseo_local_activate( $networkwide = false ) {
		if ( ! is_multisite() || ! $networkwide ) {
			_yoast_wpseo_local_activate();
		}
		else {
			/* Multi-site network activation - activate the plugin for all blogs */
			yoast_wpseo_local_network_activate_deactivate( true );
		}
	}
}

if ( ! function_exists( 'yoast_wpseo_local_deactivate' ) ) {
	/**
	 * Run single site / network-wide de-activation of the plugin.
	 *
	 * @param bool $networkwide Whether the plugin is being de-activated network-wide.
	 */
	function yoast_wpseo_local_deactivate( $networkwide = false ) {
		if ( ! is_multisite() || ! $networkwide ) {
			_yoast_wpseo_local_deactivate();
		}
		else {
			/* Multi-site network activation - de-activate the plugin for all blogs */
			yoast_wpseo_local_network_activate_deactivate( false );
		}

		// Clean up notifications set bij Local SEO from Yoast Notification Center.
		if ( class_exists( 'Yoast_Notification_Center' ) ) {
			$notifications = array(
				'LocalSEOBrowserKey',
				'LocalSEOServerKey',
				'PersonOrCompanySettingUpdate',
				'PersonOrCompanySettingError',
			);

			$notification_center = Yoast_Notification_Center::get();

			foreach ( $notifications as $notification_id ) {
				$notification = $notification_center->get_notification_by_id( $notification_id );

				if ( $notification instanceof Yoast_Notification ) {
					$notification_center->remove_notification( $notification );
				}
			}
		}
	}
}

if ( ! function_exists( 'yoast_wpseo_local_network_activate_deactivate' ) ) {
	/**
	 * Run network-wide (de-)activation of the plugin
	 *
	 * @param bool $activate True for plugin activation, false for de-activation.
	 */
	function yoast_wpseo_local_network_activate_deactivate( $activate = true ) {
		global $wpdb;
		$network_blogs = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs WHERE site_id = %d", $wpdb->siteid ) );
		if ( is_array( $network_blogs ) && $network_blogs !== array() ) {
			foreach ( $network_blogs as $blog_id ) {
				switch_to_blog( $blog_id );
				if ( $activate === true ) {
					_yoast_wpseo_local_activate();
				}
				else {
					_yoast_wpseo_local_deactivate();
				}
				restore_current_blog();
			}
		}
	}
}

if ( ! function_exists( '_yoast_wpseo_local_activate' ) ) {
	/**
	 * Runs on activation of the plugin.
	 */
	function _yoast_wpseo_local_activate() {
		/*
		 * When the plugin is deactivated and activated again, the license have to be activated. This is mostly the case
		 * during a update of the plugin. To solve this, we hook into the activation process by calling a method that will
		 * activate the license.
		 */
		yoast_wpseo_local_activate_license();

		WPSEO_Local_Core::set_defaults();
		WPSEO_Local_Core::update_sitemap();
		WPSEO_Local_Core::update_yoast_seo_settings();

		if ( method_exists( 'WPSEO_Sitemaps_Cache', 'clear' ) ) {
			WPSEO_Sitemaps_Cache::clear( array( WPSEO_Sitemaps::SITEMAP_INDEX_TYPE ) );
		}
	}
}

if ( ! function_exists( '_yoast_wpseo_local_deactivate' ) ) {
	/**
	 * Runs on deactivation of the plugin.
	 */
	function _yoast_wpseo_local_deactivate() {
		WPSEO_Local_Core::update_sitemap();

		if ( method_exists( 'WPSEO_Sitemaps_Cache', 'clear' ) ) {
			WPSEO_Sitemaps_Cache::clear( array( WPSEO_Sitemaps::SITEMAP_INDEX_TYPE ) );
		}
	}
}

if ( ! function_exists( 'yoast_wpseo_local_on_activate_blog' ) ) {
	/**
	 * Run yoast wpseo local activation routine on creation / activation of a multisite blog if Yoast WPSEO Local is activated
	 * network-wide.
	 *
	 * Will only be called by multisite actions.
	 *
	 * @internal Unfortunately will fail if the plugin is in the must-use directory
	 * @see      https://core.trac.wordpress.org/ticket/24205
	 *
	 * @param int $blog_id Blog ID.
	 */
	function yoast_wpseo_local_on_activate_blog( $blog_id ) {
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}

		if ( is_plugin_active_for_network( plugin_basename( WPSEO_FILE ) ) ) {
			switch_to_blog( $blog_id );
			yoast_wpseo_local_activate( false );
			restore_current_blog();
		}
	}
}

// Activation and de-activation hooks.
if ( function_exists( 'yoast_wpseo_local_activate' ) ) {
	register_activation_hook( WPSEO_LOCAL_FILE, 'yoast_wpseo_local_activate' );
}

if ( function_exists( 'yoast_wpseo_local_deactivate' ) ) {
	register_deactivation_hook( WPSEO_LOCAL_FILE, 'yoast_wpseo_local_deactivate' );
}

if ( function_exists( 'yoast_wpseo_local_on_activate_blog' ) ) {
	add_action( 'wpmu_new_blog', 'yoast_wpseo_local_on_activate_blog' );
	add_action( 'activate_blog', 'yoast_wpseo_local_on_activate_blog' );
}
