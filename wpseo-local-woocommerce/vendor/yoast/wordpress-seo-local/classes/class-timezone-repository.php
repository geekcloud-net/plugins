<?php
/**
 * @package WPSEO_Local\Main
 * @since   4.2
 */

if ( ! class_exists( 'WPSEO_Local_Timezone_Repository' ) ) {

	/**
	 * WPSEO_Local_Timezone_Repository class. Handles all basic needs for the plugin, like custom post_type/taxonomy.
	 */
	class WPSEO_Local_Timezone_Repository {

		/**
		 * @var array $options Stores the options for this plugin.
		 */
		var $options = array();

		/**
		 * @var Yoast_Plugin_License_Manager Holds an instance of the license manager class
		 */
		protected $license_manager = null;

		/**
		 * Constructor for the WPSEO_Local_Core class.
		 *
		 * @since 4.2
		 */
		public function __construct() {
			$this->options = get_option( 'wpseo_local' );
		}

		/**
		 * Check whether a location is currently open or closed.
		 *
		 * @param null $post A post ID.
		 *
		 * @return bool|WP_Error
		 */
		public function is_location_open( $post = null ) {
			$timezone = $this->get_location_timezone( $post );

			// If the timezone for a location isn't set, try to do so.
			if ( empty( $timezone ) || is_wp_error( $timezone ) ) {
				$timezone = $this->set_location_timezone( $post );
			}

			if ( isset( $timezone ) && ! empty( $timezone ) && ! is_wp_error( $timezone ) ) {

				$local_time = new DateTime( 'now', new DateTimeZone( $timezone ) );
				$local_day  = strtolower( $local_time->format( 'l' ) );

				if ( ! wpseo_has_multiple_locations() ) {
					$options = get_option( 'wpseo_local' );

					$open_from        = $options[ 'opening_hours_' . $local_day . '_from' ];
					$open_to          = $options[ 'opening_hours_' . $local_day . '_to' ];
					$open_second_from = $options[ 'opening_hours_' . $local_day . '_second_from' ];
					$open_second_to   = $options[ 'opening_hours_' . $local_day . '_second_to' ];
				}
				else {
					$post             = get_post( $post );
					$open_from        = get_post_meta( $post->ID, '_wpseo_opening_hours_' . $local_day . '_from', true );
					$open_to          = get_post_meta( $post->ID, '_wpseo_opening_hours_' . $local_day . '_to', true );
					$open_second_from = get_post_meta( $post->ID, '_wpseo_opening_hours_' . $local_day . '_second_from', true );
					$open_second_to   = get_post_meta( $post->ID, '_wpseo_opening_hours_' . $local_day . '_second_from', true );
				}

				if ( 'closed' != $open_from && ( ( $local_time->format( 'H:i' ) >= $open_from && $local_time->format( 'H:i' ) <= $open_to ) || ( $local_time->format( 'H:i' ) >= $open_second_from && $local_time->format( 'H:i' ) <= $open_second_to ) ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Returns the value for a timezone for a location.
		 *
		 * @param null $post Post ID or object.
		 *
		 * @return mixed
		 */
		private function get_location_timezone( $post = null ) {
			if ( ! wpseo_has_multiple_locations() ) {
				$options = get_option( 'wpseo_local' );

				return $options['location_timezone'];
			}
			else {
				$post = get_post( $post );

				return get_post_meta( $post->ID, '_wpseo_business_timezone', true );
			}
		}

		/**
		 * Set the timezone for a location and return the timezone value upon succes.
		 *
		 * @param null $post Post ID or object.
		 *
		 * @return mixed
		 */
		public function set_location_timezone( $post = null ) {
			$timezone = $this->get_coords_timezone( $post );

			if ( ! empty( $timezone ) && ! is_wp_error( $timezone ) ) {

				if ( ! wpseo_has_multiple_locations() ) {
					$this->options['location_timezone'] = $timezone;

					update_option( 'wpseo_local', $this->options );
				}
				else {
					$post = get_post( $post );
					update_post_meta( $post->ID, '_wpseo_business_timezone', $timezone );
				}

				return $timezone;
			}

			return false;
		}

		/**
		 * @param null $post Post ID or object.
		 *
		 * @return WP_Error
		 */
		public function get_coords_timezone( $post = null ) {
			if ( ! wpseo_has_multiple_locations() ) {
				$lat  = isset( $this->options['location_coords_lat'] ) ? $this->options['location_coords_lat'] : '';
				$long = isset( $this->options['location_coords_long'] ) ? $this->options['location_coords_long'] : '';
			}
			else {
				$post = get_post( $post );

				$lat  = get_post_meta( $post->ID, '_wpseo_coordinates_lat', true );
				$long = get_post_meta( $post->ID, '_wpseo_coordinates_long', true );
			}
			if ( empty( $lat ) || empty( $long ) ) {
				return new WP_Error( 'wpseo-no-lat-long', __( 'The lat or long for this location are not set correctly, there for the timezone cannot be determined.', 'yoast-local-seo' ) );
			}

			$timezone_url = 'https://maps.googleapis.com/maps/api/timezone/json?location=' . $lat . ',' . $long . '&timestamp=' . time() . '&key=' . yoast_wpseo_local_get_api_key_server();
			$response     = wp_remote_get( $timezone_url );

			if ( is_wp_error( $response ) || $response['response']['code'] != 200 || empty( $response['body'] ) ) {
				return new WP_Error( 'wpseo-no-response', __( 'No response from the Google Timezone API. Please check your API key and make sure the API is enabled in https://console.developers.google.com/apis/api/timezone_backend/overview', 'yoast-local-seo' ) );
			}

			$response_body = json_decode( $response['body'] );

			if ( 'OK' != $response_body->status ) {
				$error_code = 'wpseo-zero-results';
				if ( $response_body->status == 'OVER_QUERY_LIMIT' ) {
					$error_code = 'wpseo-query-limit';
				}

				return new WP_Error( $error_code, $response_body->status );
			}

			// @codingStandardsIgnoreLine
			return $response_body->timeZoneId;
		}
	}
}
