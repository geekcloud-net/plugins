<?php
/**
 * Init
 *
 * @package Page Builder Framework Premium Addon
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Settings Page
function wpbf_premium_menu() {

	add_theme_page( __( 'Theme Settings', 'wpbfpremium' ), __( 'Theme Settings', 'wpbfpremium' ), 'manage_options', 'wpbf-premium', 'wpbf_premium_settings' );

}
add_action( 'admin_menu', 'wpbf_premium_menu' );

function wpbf_premium_settings() {
	require_once WPBF_PREMIUM_DIR . 'inc/settings/wpbf-settings-template.php';
}

// Admin Scripts & Styles
function wpbf_premium_admin_scripts() {

	// Image Upload
	wp_enqueue_script( 'wpbf-premium', WPBF_PREMIUM_URI . 'js/wpbf-premium.js', array( 'jquery' ), false, true );

}
add_action( 'admin_enqueue_scripts', 'wpbf_premium_admin_scripts' );

// add inline styles to new location
function wpbf_change_inline_style_location() {
    return 'wpbf-premium';
}
add_filter( 'wpbf_add_inline_style', 'wpbf_change_inline_style_location' );

// Kirki
require_once( WPBF_PREMIUM_DIR . 'inc/customizer/wpbf-kirki.php' );

// Typekit Integration
require_once( WPBF_PREMIUM_DIR . 'inc/customizer/typekit.php' );

// Custom Controls
require_once( WPBF_PREMIUM_DIR . 'inc/customizer/custom-controls.php' );

// Customizer Functions
require_once( WPBF_PREMIUM_DIR . 'inc/customizer/customizer-functions.php' );

// Styles
require_once( WPBF_PREMIUM_DIR . 'inc/customizer/styles.php' );

// Styles
require_once( WPBF_PREMIUM_DIR . 'inc/customizer/responsive.php' );

// Options
require_once( WPBF_PREMIUM_DIR . 'inc/settings/options.php' );

// Premium Settings
require_once( WPBF_PREMIUM_DIR . 'inc/settings/wpbf-global-settings.php' );

// Settings Output
require_once( WPBF_PREMIUM_DIR . 'inc/settings/wpbf-global-functions.php' );

// Premium Page Templates
require_once( WPBF_PREMIUM_DIR . 'inc/page-templates.php' );

// Body Classes
require_once( WPBF_PREMIUM_DIR . 'inc/body-classes.php' );

// Helpers
require_once( WPBF_PREMIUM_DIR . 'inc/helpers.php' );

// Theme Mods
require_once( WPBF_PREMIUM_DIR . 'inc/theme-mods.php' );

// Customizer Import Export
require_once( WPBF_PREMIUM_DIR . 'inc/integration/customizer-import-export.php' );

// Beaver Themer
if( class_exists( 'FLThemeBuilderLoader' ) && class_exists( 'FLBuilderLoader' ) ) {

	require_once( WPBF_PREMIUM_DIR . 'inc/integration/beaver-themer.php' );

}

// Elementor Pro 2.0
if( function_exists( 'elementor_pro_load_plugin' ) ) {
	require_once( WPBF_PREMIUM_DIR . 'inc/integration/elementor.php' );
}

// Header Footer Elementor
require_once( WPBF_PREMIUM_DIR . 'inc/integration/header-footer-elementor.php' );

// WooCommerce
// if ( class_exists( 'WooCommerce' ) ) {
// 	require_once( WPBF_PREMIUM_DIR . '/inc/woocommerce.php' );
// }