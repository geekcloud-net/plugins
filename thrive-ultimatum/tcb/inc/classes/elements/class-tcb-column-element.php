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
 * Class TCB_Column_Element
 */
class TCB_Column_Element extends TCB_Element_Abstract {

	/**
	 * Not directly available from the menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Column', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'column';
	}

	/**
	 * Text element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return 'div.tcb-col';
	}

	/**
	 * @return string
	 */
	protected function html() {
		return '';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		return array(
			'responsive'       => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
			'layout'           => array(
				'disabled_controls' => array(
					'.tve-advanced-controls',
					'MaxWidth',
					'Alignment',
				),
			),
		);
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}
}
