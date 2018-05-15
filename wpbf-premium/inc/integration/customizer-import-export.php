<?php
/**
 * Customizer Import Export
 *
 * @package Page Builder Framework
 * @subpackage Integration
 */
 
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function wpbf_export_option_keys( $keys ) {
	$keys[] = 'wpbf';
	$keys[] = 'wpbf_settings';
	return $keys;
}

add_filter( 'cei_export_option_keys', 'wpbf_export_option_keys' );