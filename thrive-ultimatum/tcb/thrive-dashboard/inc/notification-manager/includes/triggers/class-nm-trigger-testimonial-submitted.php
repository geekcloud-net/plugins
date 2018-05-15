<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TD_NM_Trigger_Testimonial_Submitted extends TD_NM_Trigger_Abstract {

	public function applicable_on_data( $testimonial ) {

		if ( defined( 'TVO_SOURCE_DIRECT_CAPTURE' ) && ! empty( $testimonial['source'] ) && TVO_SOURCE_DIRECT_CAPTURE == $testimonial['source'] ) {
			return true;
		}

		return false;
	}

}
