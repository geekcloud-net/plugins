<?php
/**
 * Uninstall WPLA
 *
 * Uninstalling deletes options, tables, and product meta.
 */
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

global $wpdb;

// check if "Uninstall on removal" is enabled
if ( get_option( 'wpla_uninstall', 0 ) == 1 ) {

	// drop tables
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "amazon_accounts" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "amazon_btg" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "amazon_categories" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "amazon_feed_templates" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "amazon_feed_tpl_data" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "amazon_feed_tpl_values" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "amazon_feeds" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "amazon_jobs" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "amazon_listings" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "amazon_log" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "amazon_markets" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "amazon_orders" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "amazon_payment" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "amazon_profiles" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "amazon_reports" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "amazon_shipping" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "amazon_stock_log" );

	// if a license is currently activated, deactivate it before deleting options
	if ( get_option( 'wpla_license_activated' ) ) {

		$args = array(
			'email'       => get_option( 'wpla_activation_email' ),
			'licence_key' => get_option( 'wpla_api_key' ),
			);
		WPLAUP()->key()->deactivate( $args ); // reset license key activation
		// $deactivate_results = json_decode( WPLAUP()->key()->deactivate( $args ), true ); // reset license key activation

	}

	// delete options
	$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'wpla_%';");

}
