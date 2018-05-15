<?php

if ( ! function_exists( 'yoast_seo_local_woocommerce_get_address_for_method_id' ) ) {

	function yoast_seo_local_woocommerce_get_address_for_method_id( $method_id ) {

		//only alter the shipping address when local shipping has been selected
		if ( false === (strstr( $method_id, 'yoast_wcseo_local_pickup' ) ) ) {
			return '';
		}

		//get the specific post id for this location
		$location_id = intval( str_replace( 'yoast_wcseo_local_pickup_', '', $method_id ) );

		//store the specs we want as an array
		$address_array = array(
			get_post_meta( $location_id, '_wpseo_business_address', true ),
			get_post_meta( $location_id, '_wpseo_business_zipcode', true ),
			get_post_meta( $location_id, '_wpseo_business_city', true ),
			get_post_meta( $location_id, '_wpseo_business_country', true ),
		);

		//clear empty values
		$address_array = array_filter( $address_array );

		//return als a comma seperated string
		return implode( ', ', $address_array );
	}
}