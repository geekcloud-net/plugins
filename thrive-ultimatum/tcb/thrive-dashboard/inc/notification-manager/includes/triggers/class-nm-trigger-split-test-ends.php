<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TD_NM_Trigger_Split_Test_Ends extends TD_NM_Trigger_Abstract {

	public function applicable_on_data( $test_item ) {

		$applicable = false;

		if ( ! empty( $this->settings['tho'] ) && $this->in_list( $this->settings['tho'], $test_item->id ) ) {
			$applicable = true;
		} else if ( ! empty( $this->settings['tqb'] ) && $this->in_list( $this->settings['tqb'], $test_item->id ) ) {
			$applicable = true;
		} else if ( ! empty( $this->settings['tl'] ) && $this->in_list( $this->settings['tl'], $test_item->id ) ) {
			$applicable = true;
		} else if ( ! empty( $this->settings['tab'] ) && $this->in_list( $this->settings['tab'], $test_item->id ) ) {
			$applicable = true;
		}

		return $applicable;
	}

	/**
	 * split_test_ends trigger can be initiated by more plugins (TL, TQB, THO)
	 * and we need to check if the trigger is applicable for any of the plugin
	 *
	 * @return bool
	 */
	public function is_notification_applicable() {

		$applicable = parent::is_notification_applicable();

		return $applicable && ( $this->_is_tl_applicable() || $this->_is_tho_applicable() || $this->_is_tqb_applicable() || $this->_is_tab_applicable() );
	}

	protected function _is_tl_applicable() {

		$plugin_active = is_plugin_active( 'thrive-leads/thrive-leads.php' );

		return $plugin_active && ! empty( $this->settings['settings']['tl'] );
	}

	protected function _is_tho_applicable() {

		$plugin_active = is_plugin_active( 'thrive-headline-optimizer/thrive-headline-optimizer.php' );

		return $plugin_active && ! empty( $this->settings['settings']['tho'] );
	}

	protected function _is_tqb_applicable() {

		$plugin_active = is_plugin_active( 'thrive-quiz-builder/thrive-quiz-builder.php' );

		return $plugin_active && ! empty( $this->settings['settings']['tqb'] );
	}

	protected function _is_tab_applicable() {
		$plugin_active = is_plugin_active( 'thrive-ab-page-testing/thrive-ab-page-testing.php' );

		return $plugin_active && ! empty( $this->settings['settings']['tab'] );
	}

}
