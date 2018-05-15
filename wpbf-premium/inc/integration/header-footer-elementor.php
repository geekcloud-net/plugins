<?php
/**
 * Header Footer Elementor
 *
 * @package Page Builder Framework
 * @subpackage Integration
 */
 
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/* Render Header if HFE Header Template Exists */
function wpbf_render_hfe_header() {

	if ( function_exists( 'hfe_render_header' ) && hfe_header_enabled() ) {

		hfe_render_header();

	}

}

add_action( 'wpbf_header', 'wpbf_render_hfe_header' );

/* Render Footer if HFE Footer Template Exists */
function wpbf_render_hfe_footer() {
	
	if ( function_exists( 'hfe_render_footer' ) && hfe_footer_enabled() ) {

		hfe_render_footer();

	}

}

add_action( 'wpbf_footer', 'wpbf_render_hfe_footer' );

/* Remove Theme Header/Footer if the respective HFE Template is present */
function wpbf_hfe_remove_header_footer() {

	if ( function_exists( 'hfe_render_header' ) && hfe_header_enabled() ) {

		remove_action( 'wpbf_header', 'wpbf_do_header' );

	}

	if ( function_exists( 'hfe_render_footer' ) && hfe_footer_enabled() ) {

		remove_action( 'wpbf_footer', 'wpbf_do_footer' );

	}

}

add_action( 'wp', 'wpbf_hfe_remove_header_footer' );

/* Add HFE Theme Support */
function wpbf_header_footer_elementor_support() {

	add_theme_support( 'header-footer-elementor' );

}

add_action( 'after_setup_theme', 'wpbf_header_footer_elementor_support' );
