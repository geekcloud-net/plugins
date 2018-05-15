<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TD_NM_Trigger_Quiz_Completion extends TD_NM_Trigger_Abstract {

	public function applicable_on_data( $quiz ) {

		return $this->in_list( $this->settings, $quiz->ID );
	}
}
