<?php
/**
 * @package WPSEO_Local\Main
 */

if ( ! class_exists( 'WPSEO_Local_Opening_Hours_Repository' ) ) {

	/**
	 * Class WPSEO_Local_Opening_Hours_Repository
	 *
	 * This class handles the querying of all locations
	 */
	class WPSEO_Local_Opening_Hours_Repository {

		/**
		 * @var array $days Contains array for days with its translations and notations.
		 */
		protected $days;

		/**
		 * WPSEO_Local_Opening_Hours_Repository constructor.
		 */
		public function __construct() {
			$this->run();
		}

		/**
		 * Runs default actions when instantiating the class.
		 */
		public function run() {
			$days = new ArrayIterator( array(
				'sunday'    => __( 'Sunday', 'yoast-local-seo' ),
				'monday'    => __( 'Monday', 'yoast-local-seo' ),
				'tuesday'   => __( 'Tuesday', 'yoast-local-seo' ),
				'wednesday' => __( 'Wednesday', 'yoast-local-seo' ),
				'thursday'  => __( 'Thursday', 'yoast-local-seo' ),
				'friday'    => __( 'Friday', 'yoast-local-seo' ),
				'saturday'  => __( 'Saturday', 'yoast-local-seo' ),
			) );

			$days = new InfiniteIterator( $days );
			$this->days = new LimitIterator( $days, get_option( 'start_of_week' ), 7 );
		}

		/**
		 * Returns an array of days.
		 *
		 * @return LimitIterator
		 */
		public function get_days() {
			return $this->days;
		}

		/**
		 * @TODO: passing through the $post_id should be solved in a nicer way, since when using a single-location setup, it doesn't need a post ID.
		 *
		 * @param string          $day     Lowercase key of the day (in english).
		 * @param null|int|string $post_id Use 'option' when using single-location setup. Use the Post ID (int) when using multiple locations setup.
		 * @param array           $options Optional options array.
		 *
		 * @return array Array of opening hours in all needed formats.
		 */
		public function get_opening_hours( $day, $post_id = null, $options = array() ) {
			if ( wpseo_has_multiple_locations() ) {
				if ( null === $post_id ) {
					$post_id = get_the_ID();
				}

				$field_name = '_wpseo_opening_hours_' . $day;
				$value_from = get_post_meta( $post_id, $field_name . '_from', true );
				$value_to = get_post_meta( $post_id, $field_name . '_to', true );
				$value_second_from = get_post_meta( $post_id, $field_name . '_second_from', true );
				$value_second_to = get_post_meta( $post_id, $field_name . '_second_to', true );
			}
			else {
				$field_name = 'opening_hours_' . $day;
				$value_from = isset( $options[ $field_name . '_from' ] ) ? esc_attr( $options[ $field_name . '_from' ] ) : '';
				$value_to = isset( $options[ $field_name . '_to' ] ) ? esc_attr( $options[ $field_name . '_to' ] ) : '';
				$value_second_from = isset( $options[ $field_name . '_second_from' ] ) ? esc_attr( $options[ $field_name . '_second_from' ] ) : '';
				$value_second_to = isset( $options[ $field_name . '_second_to' ] ) ? esc_attr( $options[ $field_name . '_second_to' ] ) : '';
			}

			$value_from_formatted = $value_from;
			$value_to_formatted = $value_to;
			$value_second_from_formatted = $value_second_from;
			$value_second_to_formatted = $value_second_to;

			if ( ! isset( $options['opening_hours_24h'] ) || $options['opening_hours_24h'] != 'on' ) {
				$value_from_formatted = date( 'g:i A', strtotime( $value_from ) );
				$value_to_formatted = date( 'g:i A', strtotime( $value_to ) );
				$value_second_from_formatted = date( 'g:i A', strtotime( $value_second_from ) );
				$value_second_to_formatted = date( 'g:i A', strtotime( $value_second_to ) );
			}

			if ( true === wpseo_has_multiple_locations() ) {
				$multiple_opening_hours = get_post_meta( $post_id, '_wpseo_multiple_opening_hours', true );
				$use_multiple_times = ! empty( $multiple_opening_hours );
			}
			else {
				$use_multiple_times = isset( $options['multiple_opening_hours'] ) && $options['multiple_opening_hours'] == 'on';
			}

			return array(
				'value_abbr'                  => ucfirst( substr( $day, 0, 2 ) ),
				'value_from'                  => $value_from,
				'value_to'                    => $value_to,
				'value_second_from'           => $value_second_from,
				'value_second_to'             => $value_second_to,
				'value_from_formatted'        => $value_from_formatted,
				'value_to_formatted'          => $value_to_formatted,
				'value_second_from_formatted' => $value_second_from_formatted,
				'value_second_to_formatted'   => $value_second_to_formatted,
				'use_multiple_times'          => $use_multiple_times,
			);
		}
	}
}
