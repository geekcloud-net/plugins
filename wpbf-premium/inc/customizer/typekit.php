<?php
/**
 * Typekit Integration
 *
 * @package Page Builder Framework Premium Addon
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Kirki Typekit Fonts
function wpbf_load_typekit() {

	$typekit_id = get_theme_mod( 'typekit_id' );
	$typekit_enable = get_theme_mod( 'enable_typekit' );

	if ( !empty( $typekit_id ) && $typekit_enable ){

		wp_enqueue_style( 'wpbf-typekit', 'https://use.typekit.net/' . esc_attr( preg_replace('/[^0-9a-z]+/', '', $typekit_id ) ) .'.css', '', WPBF_PREMIUM_VERSION );

	}
}
add_action( 'wp_enqueue_scripts', 'wpbf_load_typekit', 0 );

// add typekit fonts to typography dropdown
function wpbf_typekit_group_1903( $custom_choice = array() ) {

	$typekit_id = get_theme_mod( 'typekit_id' );
	$typekit_enable = get_theme_mod( 'enable_typekit' );
	$typekit_fonts = get_theme_mod( 'typekit_fonts' );

	$variants = array();

	if ( $typekit_enable && ! empty( $typekit_id ) && ! empty( $typekit_fonts ) ) {

		foreach( $typekit_fonts as $key => $typekit_font ){
			$children[] = array(
				'id' => $typekit_font['font_css_name'],
				'text' => $typekit_font['font_name'],
			);
			$variants[ $typekit_font['font_css_name'] ] = $typekit_font['font_variants'];

		}

		$choices = array(
				'families' => array(
					'custom' => array(
						'text' => esc_html__( 'Typekit Fonts', 'norfolk' ),
						'children' => $children,
					),
				),
				'variants' => $variants,
			);

		$choices = array_merge( $choices, $custom_choice );

		return $choices;

		}
	
}
// add_filter( 'wpbf_kirki_font_choices', 'wpbf_typekit_group_1903', 20 );

// add typekit to standard fonts
function wpbf_typekit_fonts_temp( $standard_fonts ){

	$typekit_id = get_theme_mod( 'typekit_id' );
	$typekit_enable = get_theme_mod( 'enable_typekit' );
	$typekit_fonts = get_theme_mod( 'typekit_fonts' );

	$my_typekit_fonts = array();

	if ( $typekit_enable && ! empty( $typekit_id ) && ! empty( $typekit_fonts ) ) {

		foreach( $typekit_fonts as $key=>$typekit_font ){
			$my_typekit_fonts[$typekit_font['font_css_name']] = array(
				'label' => $typekit_font['font_name'],
				'variants' => $typekit_font['font_variants'],
				'stack' => $typekit_font['font_css_name'],
			);

		}

	return array_merge_recursive( $my_typekit_fonts, $standard_fonts );

	} else {

		return $standard_fonts;

	}
	
}
add_filter( 'kirki/fonts/standard_fonts', 'wpbf_typekit_fonts_temp', 10 );