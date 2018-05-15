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
 * Class TCB_Toggle_Element
 */
class TCB_Toggle_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Toggle', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'toggle';
	}

	/**
	 * Toggle extra sidebar state - used in Ordering toggles mode.
	 *
	 * @return null|string
	 */
	public function get_sidebar_extra_state() {
		return tcb_template( 'sidebars/toggle-edit-state', null, true );
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_toggle_shortcode';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'toggle' => array(
				'config' => array(
					'HoverColor' => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Hover Color', 'thrive-cb' ),
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
					'Toggle'     => array(),
				),
			),

			'shadow'     => array(
				'config' => array(
					'disabled_controls' => array( 'inner', 'text' ),
				),
			),
			'borders'    => array(
				'disabled_controls' => array( 'Corners', 'hr' ),
				'config'            => array(),
			),
			'typography' => array( 'hidden' => true ),
			'background' => array( 'hidden' => true ),
			'animation'  => array( 'hidden' => true ),
		);
	}

	/**
	 * Element category that will be displayed in the sidebar
	 * @return string
	 */
	public function category() {
		return $this->get_thrive_advanced_label();
	}
}
