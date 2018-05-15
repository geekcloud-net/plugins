<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Callaction_Element
 */
class TCB_Callaction_Element extends TCB_Cloud_Template_Element_Abstract {

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'box, template, button, content';
	}


	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Call to Action', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'call_2_action';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array();
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return $this->get_thrive_advanced_label();
	}
}
