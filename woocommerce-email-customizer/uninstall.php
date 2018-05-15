<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// if uninstall not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

// remove added options
delete_option( 'woocommerce_email_header_background_color' );
delete_option( 'woocommerce_email_header_text_color' );
delete_option( 'woocommerce_email_header_text_color' );
delete_option( 'woocommerce_email_header_font_size' );
delete_option( 'woocommerce_email_body_text_color' );
delete_option( 'woocommerce_email_body_font_size' );
delete_option( 'woocommerce_email_link_color' );
delete_option( 'woocommerce_email_width' );
delete_option( 'woocommerce_email_footer_font_size' );
delete_option( 'woocommerce_email_footer_text_color' );
delete_option( 'woocommerce_email_font_family' );
delete_option( 'wc_email_customizer_old_settings' );