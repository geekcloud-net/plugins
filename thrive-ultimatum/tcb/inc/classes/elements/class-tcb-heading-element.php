<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

require_once 'class-tcb-text-element.php';

/**
 * Class TCB_Heading_Element
 */
class TCB_Heading_Element extends TCB_Text_Element {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Heading', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'text';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'heading';
	}

	public function inherit_components_from() {
		return 'text';
	}

	/**
	 * Text element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_heading';
	}

	/**
	 * Element category that will be displayed in the sidebar
	 * @return string
	 */
	public function category() {
		return $this->get_thrive_basic_label();
	}
}
