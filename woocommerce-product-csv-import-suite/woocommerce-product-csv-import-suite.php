<?php
/*
 * Plugin Name: WooCommerce Product CSV Import Suite
 * Plugin URI: https://woocommerce.com/products/product-csv-import-suite/
 * Description: Import and export products and variations straight from WordPress admin. Go to WooCommerce > CSV Import Suite to get started. Supports post fields, product data, custom post types, taxonomies, and images.
 * Author: WooCommerce
 * Author URI: https://woocommerce.com
 * Version: 1.10.17
 * WC requires at least: 2.6
 * WC tested up to: 3.3
 * Text Domain: woocommerce-product-csv-import-suite
 * Domain Path: /languages
 *
 * Copyright: © 2009-2017 WooCommerce.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Adapted from the WordPress post importer by the WordPress team
 *
 * Woo: 18680:7ac9b00a1fe980fb61d28ab54d167d0d
 */

if ( ! defined( 'ABSPATH' ) || ! is_admin() ) {
	return;
}

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '7ac9b00a1fe980fb61d28ab54d167d0d', '18680' );

/**
 * Check WooCommerce exists
 */
if ( ! is_woocommerce_active() ) {
	return;
}

if ( ! class_exists( 'WC_Product_CSV_Import_Suite' ) ) :

/**
 * Main CSV Import class
 */
class WC_Product_CSV_Import_Suite {

	/**
	 * Logging class
	 */
	private static $logger = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		define( 'WC_PCSVIS_FILE', __FILE__ );

		add_filter( 'woocommerce_screen_ids', array( $this, 'woocommerce_screen_ids' ) );
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'init', array( $this, 'catch_export_request' ), 20 );
		add_action( 'admin_init', array( $this, 'register_importers' ) );

		include_once( 'includes/wc-pcsvis-functions.php' );
		include_once( 'includes/class-wc-pcsvis-system-status-tools.php' );
		include_once( 'includes/class-wc-pcsvis-admin-screen.php' );
		include_once( 'includes/importer/class-wc-pcsvis-importer.php' );

		if ( defined('DOING_AJAX') ) {
			include_once( 'includes/class-wc-pcsvis-ajax-handler.php' );
		}
	}

	/**
	 * Add screen ID
	 */
	public function woocommerce_screen_ids( $ids ) {
		$ids[] = 'admin'; // For import screen
		return $ids;
	}

	/**
	 * Handle localisation
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-product-csv-import-suite', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Catches an export request and exports the data. This class is only loaded in admin.
	 */
	public function catch_export_request() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( ! empty( $_GET['action'] ) && ! empty( $_GET['page'] ) && 'woocommerce_csv_import_suite' === $_GET['page'] ) {
			switch ( $_GET['action'] ) {
				case 'export' :
					include_once( 'includes/exporter/class-wc-pcsvis-exporter.php' );
					WC_PCSVIS_Exporter::do_export( 'product' );
				break;
				case 'export_variations' :
					include_once( 'includes/exporter/class-wc-pcsvis-exporter.php' );
					WC_PCSVIS_Exporter::do_export( 'product_variation' );
				break;
			}
		}
	}

	/**
	 * Register importers for use
	 */
	public function register_importers() {
		register_importer( 'woocommerce_csv', 'WooCommerce Products (CSV)', __('Import <strong>products</strong> to your store via a csv file.', 'woocommerce-product-csv-import-suite'), 'WC_PCSVIS_Importer::product_importer' );
		register_importer( 'woocommerce_variation_csv', 'WooCommerce Product Variations (CSV)', __('Import <strong>product variations</strong> to your store via a csv file.', 'woocommerce-product-csv-import-suite'), 'WC_PCSVIS_Importer::variation_importer' );
	}

	/**
	 * Get meta data direct from DB, avoiding get_post_meta and caches
	 * @return string
	 */
	public static function log( $message ) {
		if ( ! self::$logger ) {
			self::$logger = new WC_Logger();
		}
		self::$logger->add( 'csv-import', $message );
	}

	/**
	 * Get meta data direct from DB, avoiding get_post_meta and caches
	 * @return string
	 */
	public static function get_meta_data( $post_id, $meta_key ) {
		global $wpdb;
		$value = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value from $wpdb->postmeta WHERE post_id = %d and meta_key = %s", $post_id, $meta_key ) );
		return maybe_unserialize( $value );
	}
}
endif;

new WC_Product_CSV_Import_Suite();
