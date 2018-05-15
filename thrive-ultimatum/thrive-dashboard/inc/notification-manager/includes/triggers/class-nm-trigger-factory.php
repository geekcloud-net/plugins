<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TD_NM_Trigger_Factory {

	/**
	 * @param array $trigger
	 *
	 * @return TD_NM_Trigger_Abstract
	 */
	public static function get_instance( $trigger ) {

		if ( is_array( $trigger ) && ! empty( $trigger['type'] ) ) {
			$class_name = 'TD_NM_Trigger_' . self::prepare_class_name( $trigger['type'] );
		}

		return isset( $class_name ) && class_exists( $class_name, false ) ? new $class_name( $trigger ) : null;
	}

	/**
	 * @param string $type
	 *
	 * @return string
	 */
	private static function prepare_class_name( $type ) {

		$chunks = explode( '_', $type );
		$chunks = array_map( 'ucfirst', $chunks );

		return implode( '_', $chunks );
	}
}
