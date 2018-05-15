<?php
/**
 * @package WPSEO_Local/Deprecated
 *
 * Contains all the deprecated functions.
 */

/**
 * @deprecated Deprecated since version 3.3.1. Uses the new WPSEO_Local_Address_Format class now.
 *
 * @param string $business_address The address of the business.
 * @param bool   $oneline          Whether to show the address on one line or not.
 * @param string $business_zipcode The business zipcode.
 * @param string $business_city    The business city.
 * @param string $business_state   The business state.
 * @param bool   $show_state       Whether to show the state or not.
 * @param bool   $escape_output    Whether to escape the output or not.
 * @param bool   $use_tags         Whether to use HTML tags in the outpput or not.
 *
 * @return string
 */
function wpseo_local_get_address_format( $business_address = '', $oneline = false, $business_zipcode = '', $business_city = '', $business_state = '', $show_state = false, $escape_output = false, $use_tags = true ) {
	_deprecated_function( 'wpseo_local_get_address_format', '3.3.1', 'WPSEO_Local_Address_Format->get_address_format()' );
	$options        = get_option( 'wpseo_local' );
	$address_format = ! empty( $options['address_format'] ) ? $options['address_format'] : 'address-state-postal';

	$format = new WPSEO_Local_Address_Format();
	$output = $format->get_address_format( $address_format, array(
		'business_address' => $business_address,
		'oneline'          => $oneline,
		'business_zipcode' => $business_zipcode,
		'business_city'    => $business_city,
		'business_state'   => $business_state,
		'show_state'       => $show_state,
		'escape_output'    => $escape_output,
		'use_tags'         => $use_tags,
	) );

	return trim( $output );
}


function wpseo_local_get_custom_marker( $post_id = null, $taxonomy = '' ) {
	_deprecated_function( 'wpseo_local_get_custom_marker', '4.5', 'WPSEO_Local_Locations_Repository->get_custom_marker()' );

	$repo = new WPSEO_Local_Locations_Repository();
	return $repo->cb_postmeta_custom_marker( $post_id );
}

/**
 * Get the location details
 *
 * @param string $location_id Optional. Only use this when multiple locations are enabled in the website.
 *
 * @return array|bool Array with location details.
 */
function wpseo_get_location_details( $location_id = '' ) {
	_deprecated_function( 'wpseo_get_location_details', '4.5', 'WPSEO_Local_Locations_Repository->get()' );
	$options          = get_option( 'wpseo_local' );
	$location_details = array();

	if ( wpseo_has_multiple_locations() && $location_id == '' ) {
		return false;
	}
	else if ( wpseo_has_multiple_locations() ) {
		if ( $location_id == null ) {
			return false;
		}

		$location_details = array(
			'business_address'     => get_post_meta( $location_id, '_wpseo_business_address', true ),
			'business_city'        => get_post_meta( $location_id, '_wpseo_business_city', true ),
			'business_state'       => get_post_meta( $location_id, '_wpseo_business_state', true ),
			'business_zipcode'     => get_post_meta( $location_id, '_wpseo_business_zipcode', true ),
			'business_country'     => get_post_meta( $location_id, '_wpseo_business_country', true ),
			'business_phone'       => get_post_meta( $location_id, '_wpseo_business_phone', true ),
			'business_phone_2nd'   => get_post_meta( $location_id, '_wpseo_business_phone_2nd', true ),
			'business_coords_lat'  => get_post_meta( $location_id, '_wpseo_coordinates_lat', true ),
			'business_coords_long' => get_post_meta( $location_id, '_wpseo_coordinates_long', true ),
		);
	}
	else if ( wpseo_has_multiple_locations() ) {
		$location_details = array(
			'business_address'     => $options['location_address'],
			'business_city'        => $options['location_city'],
			'business_state'       => $options['location_state'],
			'business_zipcode'     => $options['location_zipcode'],
			'business_country'     => $options['location_country'],
			'business_phone'       => $options['location_phone'],
			'business_phone_2nd'   => isset( $options['location_phone_2nd'] ) ? $options['location_phone_2nd'] : '',
			'business_coords_lat'  => $options['location_coords_lat'],
			'business_coords_long' => $options['location_coords_long'],
		);
	}

	return $location_details;
}