<?php
/**
 * @package WPSEO_Local\Frontend
 */

if ( ! class_exists( 'WPSEO_Local_JSON_LD' ) ) {

	/**
	 * Class WPSEO_Local_JSON_LD
	 *
	 * Adds all functionality for the store locator
	 */
	class WPSEO_Local_JSON_LD {

		/**
		 * @var array $options Stores the options for this plugin.
		 */
		var $options = array();

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->options = get_option( 'wpseo_local' );

			$this->run();
		}

		/**
		 * Run all the needed actions.
		 */
		public function run() {
			// Add JSON+LD to every outputted location (by shortcode or widget).
			add_filter( 'wpseo_show_address_after', array( $this, 'add_json_ld_to_address' ), 10, 3 );
			add_action( 'wpseo_show_opening_hours_after', array( $this, 'add_json_ld_to_address' ), 10, 3 );

			add_action( 'wpseo_json_ld', array( $this, 'filter_json_ld' ) );
		}

		/**
		 * Call filter, to filter out the current existing output of Yoast SEO.
		 */
		public function filter_json_ld() {
			add_filter( 'wpseo_json_ld_output', array( $this, 'generate_json_ld' ), 10, 2 );
		}

		/**
		 * Generates the JSON+LD data.
		 *
		 * @param array  $data    Current JSON+LD data of Yoast SEO in array.
		 * @param string $context JSON+LD context. We only need the "company" context, so we can skip the others.
		 *
		 * @return array $data Returns the data in order to output it.
		 */
		public function generate_json_ld( $data, $context ) {
			if ( 'company' !== $context ) {
				return $data;
			}

			// Don't show json+ld in the header on any other page than the front-page.
			if ( ! is_front_page() && ( wpseo_has_multiple_locations() && ! is_singular( 'wpseo_locations' ) ) ) {
				return $data;
			}

			// If a multiple locations setup is used, also skip outputting on homepage.
			if ( is_front_page() && wpseo_has_multiple_locations() ) {
				return $data;
			}

			$json_ld_data = $this->get_output_for_location( $data );

			return ( false !== $json_ld_data ) ? $json_ld_data : $data;
		}

		/**
		 * Retrieves correct JSON+LD to add it to the output of wpseo_local_show_address().
		 *
		 * @param string  $output       Input for the filter.
		 * @param mixed   $location_id  ID of current location.
		 * @param integer $container_id The ID of the widget or shortcode container.
		 *
		 * @return string $output Returns the output for the filter.
		 */
		public function add_json_ld_to_address( $output, $location_id = false, $container_id = null ) {
			$data = $this->get_full_output_for_location( $location_id, false, $container_id );

			$output .= '<script type="application/ld+json">' . json_encode( $data ) . '</script>';

			return $output;
		}

		/**
		 * Generates JSON+LD complete output for locations.
		 *
		 * @param null|int $location_id  ID of the location.
		 * @param bool     $json_encoded Whether the returned data should be JSON encoded. Defaults to true.
		 * @param integer  $container_id The ID of the widget or shortcode container.
		 *
		 * @return string|array|bool Returns JSON encoded string or array with data. Returns false no valid location is found.
		 */
		public function get_full_output_for_location( $location_id = null, $json_encoded = true, $container_id = null ) {
			// Initialize default data array.
			$data = array(
				'@context' => 'http://schema.org',
			);

			$data = $this->get_output_for_location( $data, $location_id, $container_id );

			if ( true === $json_encoded ) {
				$data = json_encode( $data );
			}

			return $data;
		}

		/**
		 * Generates JSON+LD output for locations.
		 *
		 * @param array    $data         Base array with data, provided by Yoast SEO JSON+LD class.
		 * @param null|int $location_id  ID of the location.
		 * @param integer  $container_id The ID of the widget or shortcode container.
		 *
		 * @return bool|array Array with location data. Returns false no valid location is found.
		 */
		public function get_output_for_location( $data, $location_id = null, $container_id = null ) {
			if ( null == $location_id && true == wpseo_has_multiple_locations() ) {
				$location_id = get_queried_object_id();
			}

			$repo      = new WPSEO_Local_Locations_Repository();
			$locations = $repo->get( array(
				'id' => array( $location_id ),
			) );

			if ( count( $locations ) === 0 ) {
				return false;
			}

			$location = reset( $locations );

			if ( wpseo_has_multiple_locations() ) {
				$data_id = trailingslashit( get_permalink( $location_id ) );
			}
			else {
				$data_id = trailingslashit( get_home_url() );
			}

			if ( ! empty( $container_id ) ) {
				$data_id .= '#' . $container_id;
			}

			$data['@id']   = $data_id;
			$data['name']  = ! empty( $location['business_name'] ) ? esc_attr( $location['business_name'] ) : '';
			$data['url']   = ! empty( $location['business_url'] ) ? esc_attr( $location['business_url'] ) : trailingslashit( get_home_url() );
			$data['@type'] = ! empty( $location['business_type'] ) ? esc_attr( $location['business_type'] ) : 'LocalBusiness';

			// Define image.
			if ( ! empty( $location['business_image'] ) && absint( $location['business_image'] ) > 0 ) {
				$data['image'] = wp_get_attachment_url( $location['business_image'] );
			}
			else {
				$data['image'] = ( isset( $data['logo'] ) ? $data['logo'] : '' );
			}

			// Define logo.
			if ( ! empty( $location['business_logo'] ) && absint( $location['business_logo'] ) > 0 ) {
				$data['logo'] = wp_get_attachment_url( $location['business_logo'] );
			}

			// Add Address field.
			if ( ! empty( $location['business_address'] ) ) {
				$business_address[] = $location['business_address'];
			}
			if ( ! empty( $location['business_address_2'] ) ) {
				$business_address[] = $location['business_address_2'];
			}

			$data['address'] = array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => ( ! empty( $business_address ) ) ? esc_attr( join( ' ', $business_address ) ) : '',
				'addressLocality' => ( ! empty( $location['business_city'] ) ) ? esc_attr( $location['business_city'] ) : '',
				'postalCode'      => ( ! empty( $location['business_zipcode'] ) ) ? esc_attr( $location['business_zipcode'] ) : '',
				'addressRegion'   => ( ! empty( $location['business_state'] ) ) ? esc_attr( $location['business_state'] ) : '',
				'addressCountry'  => ( ! empty( $location['business_country'] ) ) ? esc_attr( $location['business_country'] ) : '',
			);

			// Add coordinates.
			if ( isset( $location['coords'] ) ) {
				$data['geo'] = array(
					'@type'     => 'GeoCoordinates',
					'latitude'  => ( ! empty( $location['coords']['lat'] ) ) ? esc_attr( $location['coords']['lat'] ) : '',
					'longitude' => ( ! empty( $location['coords']['long'] ) ) ? esc_attr( $location['coords']['long'] ) : '',
				);
			}

			// Add Opening Hours.
			if ( ! isset( $this->options['hide_opening_hours'] ) || ( isset( $this->options['hide_opening_hours'] ) && $this->options['hide_opening_hours'] != 'on' ) ) {
				$data['openingHours'] = array();
				$opening_hours_repo   = new WPSEO_Local_Opening_Hours_Repository();
				$days                 = $opening_hours_repo->get_days();

				foreach ( $days as $key => $day ) {
					$opening_hours = $opening_hours_repo->get_opening_hours( $key, ( ! empty( $location_id ) ? $location_id : 'options' ), $this->options );

					if ( $opening_hours['value_from'] == 'closed' ) {
						continue;
					}

					$data['openingHours'][] = $opening_hours['value_abbr'] . ' ' . $opening_hours['value_from_formatted'] . '-' . $opening_hours['value_to_formatted'];

					if ( isset( $opening_hours['use_multiple_times'] ) && true === $opening_hours['use_multiple_times'] && $opening_hours['value_second_from'] !== 'closed' ) {
						$data['openingHours'][] = $opening_hours['value_abbr'] . ' ' . $opening_hours['value_second_from_formatted'] . '-' . $opening_hours['value_second_to_formatted'];
					}
				}
			}

			// Add additional regular fields.
			$standard_fields = array(
				'email'      => 'business_email',
				'telePhone'  => 'business_phone',
				'faxNumber'  => 'business_fax',
				'priceRange' => 'business_price_range',
				'vatID'      => 'business_vat',
				'taxID'      => 'business_tax',
			);

			foreach ( $standard_fields as $data_key => $option_field ) {
				if ( ! empty( $location[ $option_field ] ) ) {
					$data[ $data_key ] = esc_attr( $location[ $option_field ] );
				}
			}

			return $data;
		}
	}
}
