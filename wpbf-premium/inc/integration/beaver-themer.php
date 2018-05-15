<?php
/**
 * Beaver Themer
 *
 * @package Page Builder Framework
 * @subpackage Integration
 */
 
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Add BB Header/Footer Support
add_action( 'after_setup_theme', 'wpbf_bb_header_footer_support' );

function wpbf_bb_header_footer_support() {
	add_theme_support( 'fl-theme-builder-headers' );
	add_theme_support( 'fl-theme-builder-footers' );
	add_theme_support( 'fl-theme-builder-parts' );
}

// Remove Headers
add_action( 'wp', 'wpbf_header_footer_render' );

function wpbf_header_footer_render() {

	// Get the header ID.
	$header_ids = FLThemeBuilderLayoutData::get_current_page_header_ids();
	
	// If we have a header, remove the theme header and hook in Theme Builder's.
	if ( ! empty( $header_ids ) ) {
		remove_action( 'wpbf_header', 'wpbf_do_header' );
		add_action( 'wpbf_header', 'FLThemeBuilderLayoutRenderer::render_header' );
	}
	
	// Get the footer ID.
	$footer_ids = FLThemeBuilderLayoutData::get_current_page_footer_ids();
	
	// If we have a footer, remove the theme footer and hook in Theme Builder's.
	if ( ! empty( $footer_ids ) ) {
		remove_action( 'wpbf_footer', 'wpbf_do_footer' );
		add_action( 'wpbf_footer', 'FLThemeBuilderLayoutRenderer::render_footer' );
	}

}

// Parts Support
add_filter( 'fl_theme_builder_part_hooks', 'wpbf_register_part_hooks' );

function wpbf_register_part_hooks() {
	
	return array(
		array(
			'label' => 'Page',
			'hooks' => array(
				'wpbf_body_open' => 'Page Open',
				'wpbf_body_close'  => 'Page Close',
			)
		),
		array(
			'label' => 'Header',
			'hooks' => array(
				'wpbf_before_header' => 'Before Header',
				'wpbf_after_header'  => 'After Header',
				'wpbf_header_open' => 'Header Open',
				'wpbf_header_close'  => 'Header Close',
			)
		),
		array(
			'label' => 'Footer',
			'hooks' => array(
				'wpbf_before_footer' => 'Before Footer',
				'wpbf_after_footer'  => 'After Footer',
				'wpbf_footer_open' => 'Footer Open',
				'wpbf_footer_close'  => 'Footer Close',
			)
		),
	);
}