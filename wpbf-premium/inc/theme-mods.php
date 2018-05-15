<?php
/**
 * Theme Mods
 *
 * @package Page Builder Framework
 */
 
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Custom 404
add_action( 'wp', 'wpbf_remove_404' );
function wpbf_remove_404() {

	if ( get_theme_mod( '404_custom' ) ) {

		remove_action( 'wpbf_404', 'wpbf_do_404' );

	}
}

add_action( 'wpbf_404', 'wpbf_custom_404' );
function wpbf_custom_404() {

	if ( get_theme_mod( '404_custom' ) ) {

		$custom_404 = get_theme_mod( '404_custom' );

		echo do_shortcode( $custom_404 );

	}

}

// Custom Footer
add_action( 'wpbf_before_footer', 'wpbf_custom_footer' );
function wpbf_custom_footer() {

	if ( get_theme_mod( 'footer_custom' ) ) {

		$custom_footer = get_theme_mod( 'footer_custom' );

		echo do_shortcode( $custom_footer );

	}

}

// Head Scripts
add_action( 'wp_head', 'wpbf_custom_head_scripts_823932' );
function wpbf_custom_head_scripts_823932() {

	if ( get_theme_mod( 'head_scripts' ) ) {

		echo get_theme_mod( 'head_scripts' );

	}

}

// Header Scripts
add_action( 'wpbf_body_open', 'wpbf_custom_header_scripts_103802138' );
function wpbf_custom_header_scripts_103802138() {

	if ( get_theme_mod( 'header_scripts' ) ) {

		echo get_theme_mod( 'header_scripts' );

	}

}

// Footer Scripts
add_action( 'wp_footer', 'wpbf_custom_footer_scripts_0848420' );
function wpbf_custom_footer_scripts_0848420() {

	if ( get_theme_mod( 'footer_scripts' ) ) {

		echo get_theme_mod( 'footer_scripts' );

	}

}