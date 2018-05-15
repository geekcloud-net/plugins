<?php
/**
 * @package WPSEO_Local\Frontend
 */

if ( ! class_exists( 'WPSEO_Local_Address_Format' ) ) {

	/**
	 * Class WPSEO_Local_Address_Format
	 *
	 * This class handles the formatting of address output.
	 */
	class WPSEO_Local_Address_Format {

		/**
		 * WPSEO_Local_Address_Format constructor.
		 */
		public function __construct() {
		}

		/**
		 * Generates output for formatted address.
		 *
		 * @param string $address_format  Address format from the options for the current address.
		 * @param array  $address_details Array with all location data.
		 *
		 * @return string
		 */
		public function get_address_format( $address_format, $address_details ) {
			$output = '';

			$show_logo          = isset( $address_details['show_logo'] ) ? $address_details['show_logo'] : false;
			$business_address   = $address_details['business_address'];
			$business_address_2 = ( isset( $address_details['business_address_2'] ) ? $address_details['business_address_2'] : '' );
			$oneline            = $address_details['oneline'];
			$business_zipcode   = $address_details['business_zipcode'];
			$business_city      = $address_details['business_city'];
			$business_state     = $address_details['business_state'];
			$show_state         = $address_details['show_state'];
			$escape_output      = $address_details['escape_output'];
			$use_tags           = $address_details['use_tags'];

			$business_city_string = $business_city;
			if ( $use_tags ) {
				$business_city_string = '<span class="locality"> ' . esc_html( $business_city ) . '</span>';
			}

			$business_state_string = $business_state;
			if ( $use_tags ) {
				$business_state_string = '<span  class="region">' . esc_html( $business_state ) . '</span>';
			}

			$business_zipcode_string = $business_zipcode;
			if ( $use_tags ) {
				$business_zipcode_string = '<span class="postal-code">' . esc_html( $business_zipcode ) . '</span>';
			}

			if ( in_array( $address_format, array(
				'',
				'address-state-postal',
				'address-state-postal-comma',
				'address-postal-city-state',
				'address-postal',
				'address-postal-comma',
				'address-city',
			) ) ) {
				if ( ! empty( $business_address ) ) {
					$output .= ( ( $oneline && ! $show_logo ) ? ', ' : '' );

					if ( $use_tags ) {
						$output .= '<' . ( ( $oneline ) ? 'span' : 'div' ) . ' class="street-address">';
						$output .= esc_html( $business_address );
						if ( ! empty( $business_address_2 ) ) {
							$output .= ( ( $oneline ) ? ', ' : '<br>' ) . esc_attr( $business_address_2 );
						}
						$output .= '</' . ( ( $oneline ) ? 'span' : 'div' ) . '>';
					}
					else {
						$output .= esc_html( $business_address ) . ' ';
					}
				}

				if ( $address_format == 'address-postal-city-state' && ! empty( $business_zipcode ) ) {
					$output .= ( ( $oneline ) ? ', ' : '' );
					$output .= $business_zipcode_string;
				}

				if ( ! empty( $business_city ) ) {
					$output .= ( ( $oneline ) ? ', ' : '' );
					$output .= $business_city_string;

					if ( in_array( $address_format, array(
						'address-state-postal',
						'address-state-postal-comma',
						'address-postal-city-state',
					) ) ) {
						if ( true === $show_state && ! empty( $business_state ) ) {
							$output .= ',';
						}
					}
				}

				if ( in_array( $address_format, array(
					'address-state-postal',
					'address-state-postal-comma',
					'address-postal-city-state',
				) ) ) {
					if ( $show_state && ! empty( $business_state ) ) {
						$output .= ' ' . $business_state_string;
					}
				}

				if ( ! empty( $business_zipcode_string ) && ! in_array( $address_format, array(
						'address-postal-city-state',
						'address-city',
					) )
				) {
					$output .= ' ' . $business_zipcode_string;
				}
			}
			else {
				if ( ! empty( $business_zipcode ) ) {
					$output .= ( ( $oneline ) ? ', ' : '' );
					$output .= $business_zipcode_string;
				}
				if ( $show_state && ! empty( $business_state ) ) {
					$output .= ( ( $oneline ) ? ', ' : ' ' );
					$output .= $business_state_string;
				}
				if ( ! empty( $business_city ) ) {
					$output .= ( ( $oneline ) ? ' ' : '' );
					$output .= $business_city_string;
				}

				if ( ! empty( $business_address ) ) {
					$output .= ( ( $oneline ) ? ', ' : '' );

					if ( $use_tags ) {
						$output .= '<' . ( ( $oneline ) ? 'span' : 'div' ) . ' class="street-address">' . $business_address . '</' . ( ( $oneline ) ? 'span' : 'div' ) . '>';
					}
					else {
						$output .= esc_html( $business_address );
					}
				}
			}

			if ( $escape_output ) {
				$output = addslashes( $output );
			}

			return $output;
		}
	}
}
