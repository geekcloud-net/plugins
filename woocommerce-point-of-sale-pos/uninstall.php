<?php
if( ! defined('WP_UNINSTALL_PLUGIN') )
	exit;

global $wpdb;

$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "wc_poin_of_sale_outlets" );
$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "wc_poin_of_sale_registers" );
$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "wc_poin_of_sale_tiles" );
$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "wc_poin_of_sale_grids" );
$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "wc_poin_of_sale_receipts" );
$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "wc_poin_of_sale_reports" );

$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'wc_pos_%';");
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'woocommerce_pos_%';");


include_once( 'includes/class-wc-pos-install.php' );
	WC_POS_Install::remove_roles();