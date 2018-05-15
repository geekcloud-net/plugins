<?php
/**
 * Customizer Functions
 *
 * @package Page Builder Framework Premium Addon
 * @subpackage Customizer
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if( !function_exists('is_plugin_active') ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

// Post Message
add_action( 'customize_preview_init' , 'wpbf_premium_customizer_js' );
function wpbf_premium_customizer_js() {
	wp_enqueue_script('wpbf-premium-postmessage', WPBF_PREMIUM_URI . 'inc/customizer/js/postmessage.js', array(  'jquery', 'customize-preview' ), '', true );
}

// Customizer Scripts & Styles
add_action( 'customize_controls_print_styles' , 'wpbf_premium_customizer_scripts_styles' );

function wpbf_premium_customizer_scripts_styles() {
	wp_enqueue_script( 'wpbf-premium-customizer', WPBF_PREMIUM_URI . '/inc/customizer/js/wpbf-customizer.js', array( 'jquery' ), false, true );
}

// Menu's
add_filter( 'wpbf_menu_position', function( $choices ) {
	$choices['menu-stacked-advanced'] = esc_attr__( 'Stacked (advanced)', 'wpbfpremium' );
	$choices['menu-off-canvas'] = esc_attr__( 'Off Canvas (right)', 'wpbfpremium' );
	$choices['menu-off-canvas-left'] = esc_attr__( 'Off Canvas (left)', 'wpbfpremium' );
	$choices['menu-full-screen'] = esc_attr__( 'Full Screen', 'wpbfpremium' );

	if ( is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {
		$choices['menu-elementor'] = esc_attr__( 'Custom Menu', 'wpbfpremium' );
	}

	return $choices;
});

// Mobile Menu's
add_filter( 'wpbf_mobile_menu_options', function( $choices ) {
	$choices['menu-mobile-off-canvas'] = esc_attr__( 'Off Canvas', 'wpbfpremium' );

	if ( is_plugin_active( 'bb-plugin/fl-builder.php' ) || is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {
		$choices['menu-mobile-elementor'] = esc_attr__( 'Custom Menu', 'wpbfpremium' );
	}

	return $choices;
});