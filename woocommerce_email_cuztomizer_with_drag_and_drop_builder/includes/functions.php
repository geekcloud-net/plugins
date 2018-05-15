<?php
/**
 * WooCommerce Email Customizer with Drag and Drop Email Builder
 * Create awesome transactional emails with a drag and drop email builder
 * @author Flycart Technologies LLP
 * @license GNU GPL V3 or later
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Register Email Templates.
 */

if ( !function_exists('woo_mb_register_email_template') ) {
	function woo_mb_register_email_template( $template_id, $args ) {

		global $woo_mb_email_templates;

		if ( !is_array( $woo_mb_email_templates ) )
			$woo_mb_email_templates = array();

		$defaults = array(
			'name'                	=> $template_id,
			'description'           => '',
			'settings'           	=> false,
		);
		$args = wp_parse_args( $args, $defaults );

		if ( strlen( $template_id ) > 40 ) {
			_doing_it_wrong( __FUNCTION__, __( 'Template IDs cannot exceed 20 characters in length', 'woo-email-customizer-page-builder' ) );
			return new WP_Error( 'template_id_too_long', __( 'Template IDs cannot exceed 20 characters in length', 'woo-email-customizer-page-builder' ) );
		}

		$woo_mb_email_templates[ $template_id ] = $args;

		return $args;
	}
}


if ( !function_exists( 'mb_convert_encoding' ) ) {
	function mb_convert_encoding ( $string, $type = 'HTML-ENTITIES', $encoding = 'utf-8' ) {
		return $string;
	}

}

function woo_mb_get_option( $key, $autop = FALSE ) {

	$return = '';

	// We're in customier preview so just return the posted value.
	if ( isset( $_REQUEST[$key] ) ) {

		$return = stripslashes( $_REQUEST[$key] );
	}
	else {

		$woo_mb_template_selected = false;
			$woo_mb_template_selected = 'woocommerce';

		$settings = woo_mb_get_settings( $woo_mb_template_selected );

		$default = FALSE;
		if ( isset( $settings[$key]['default'] ) ) $default = $settings[$key]['default'];

		$return = get_option( $key, $default );
	}

	$return = __( $return, 'woo-email-customizer-page-builder' );
	$return = do_shortcode( $return );

	if ( $autop ) {
		$return = wptexturize( $return );
		$return = wpautop( $return );
	}

	// stylise certain content types, eg textarea
	if ( 'textarea' == woo_mb_Settings::get_option_array( $key, 'type' ) ) {
		$return = wptexturize( $return );
		$return = wpautop( $return );
	}

	// Return the option.
	return __( $return, 'woo-email-customizer-page-builder' );
}

function woo_mb_check_template_version( $template_id ) {
	global $woo_mb_email_templates;

	if ( ! isset( $woo_mb_email_templates[$template_id] ) ) return true;

	$woocommerce_required_version = ( isset( $woo_mb_email_templates[$template_id]['woocoomerce_required_version'] ) ) ? $woo_mb_email_templates[$template_id]['woocoomerce_required_version'] : WOO_ECPB_REQUIRED_WOOCOMMERCE_VERSION ;
	return version_compare( get_option( 'woocommerce_version' ), $woocommerce_required_version, '>' );
}