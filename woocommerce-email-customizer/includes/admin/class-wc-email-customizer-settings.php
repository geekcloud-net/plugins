<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Email_Customizer_Settings {

	private function __construct() {}

	public static function add_settings() {
		global $wp_customize;

		$wp_customize->add_setting( 'woocommerce_email_background_color', array(
			'type'      => 'option',
			'default'   => '#f5f5f5',
			'transport' => 'postMessage',
		) );

		$wp_customize->add_setting( 'woocommerce_email_body_background_color', array(
			'type'      => 'option',
			'default'   => '#fdfdfd',
			'transport' => 'postMessage',
		) );

		$wp_customize->add_setting( 'woocommerce_email_header_background_color', array(
			'type'      => 'option',
			'default'   => '#557da1',
			'transport' => 'postMessage',
		) );

		$wp_customize->add_setting( 'woocommerce_email_header_text_color', array(
			'type'      => 'option',
			'default'   => '#ffffff',
			'transport' => 'postMessage',
		) );

		$wp_customize->add_setting( 'woocommerce_email_header_font_size', array(
			'type'      => 'option',
			'default'   => '30',
			'transport' => 'postMessage',
		) );

		$wp_customize->add_setting( 'woocommerce_email_body_text_color', array(
			'type'      => 'option',
			'default'   => '#505050',
			'transport' => 'postMessage',
		) );

		$wp_customize->add_setting( 'woocommerce_email_body_font_size', array(
			'type'      => 'option',
			'default'   => '12',
			'transport' => 'postMessage',
		) );

		$wp_customize->add_setting( 'woocommerce_email_rounded_corners', array(
		'type'      => 'option',
		'default'   => '6',
		'transport' => 'postMessage',
		) );

		// add a select box for the box shadow
		$wp_customize->add_setting( 'woocommerce_email_box_shadow_spread', array(
		'type'      => 'option',
		'default'   => '1',
		'transport' => 'postMessage',
		) );

		$wp_customize->add_setting( 'woocommerce_email_font_family', array(
			'type'      => 'option',
			'default'   => 'sans-serif',
			'transport' => 'postMessage',
		) );

		$wp_customize->add_setting( 'woocommerce_email_link_color', array(
			'type'      => 'option',
			'default'   => '#214cce',
			'transport' => 'postMessage',
		) );

		$wp_customize->add_setting( 'woocommerce_email_header_image', array(
			'type'      => 'option',
			'transport' => 'postMessage',
		) );

		$wp_customize->add_setting( 'woocommerce_email_width', array(
			'type'      => 'option',
			'default'   => '600',
			'transport' => 'postMessage',
		) );

		$wp_customize->add_setting( 'woocommerce_email_footer_text', array(
			'type'      => 'option',
			'default'   => __( 'WooCommece Email Customizer - Powered by WooCommerce and WordPress', 'woocommerce-email-customizer' ),
			'transport' => 'postMessage',
		) );

		$wp_customize->add_setting( 'woocommerce_email_footer_font_size', array(
			'type'      => 'option',
			'default'   => '12',
			'transport' => 'postMessage',
		) );

		$wp_customize->add_setting( 'woocommerce_email_footer_text_color', array(
			'type'      => 'option',
			'default'   => '#202020',
			'transport' => 'postMessage',
		) );

		$wp_customize->add_setting( 'woocommerce_email_send', array(
			'type'      => 'option',
			'default'   => '',
		) );
	}
}