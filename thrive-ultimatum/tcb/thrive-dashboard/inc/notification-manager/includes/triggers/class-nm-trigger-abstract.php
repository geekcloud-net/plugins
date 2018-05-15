<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

abstract class TD_NM_Trigger_Abstract {

	protected $settings;

	protected $_types;

	public function __construct( $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Check if item exists in list
	 *
	 * @param $list
	 * @param $id
	 *
	 * @return bool
	 */
	protected function in_list( $list, $id ) {
		foreach ( $list as $key => $item ) {
			if ( $item['value'] == $id ) {
				return true;
			}
		}

		return false;
	}

	public function get_types() {

		if ( ! $this->_types ) {
			$types = TD_NM()->get_trigger_types();
			foreach ( $types as $type ) {
				$this->_types[] = $type['key'];
			}
		}

		return $this->_types;
	}


	/**
	 * Check if this trigger is applicable based on its type
	 * and if the corresponding plugin is loaded
	 * and if corresponding data is set
	 *
	 * @see td_nm_get_notifications()
	 *
	 * @return bool
	 */
	public function is_notification_applicable() {

		$types      = $this->get_types();
		$applicable = false;

		if ( is_array( $types ) && in_array( $this->settings['type'], $types ) ) {
			$applicable = true;
		}

		return $applicable;
	}

	/**
	 * Check if settings are applicable for that item
	 *
	 * @param mixed $item
	 *
	 * @return mixed
	 */
	abstract public function applicable_on_data( $item );
}
