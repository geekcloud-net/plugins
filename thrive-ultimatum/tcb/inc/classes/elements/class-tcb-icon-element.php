<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TCB_Icon_Element
 */
class TCB_Icon_Element extends TCB_Element_Abstract {

	/**
	 * @return string
	 */
	public function icon() {
		return 'icon';
	}

	/**
	 * @return string
	 */
	public function name() {
		return __( 'Icon', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'media,icon';
	}


	/**
	 * @return string
	 */
	public function identifier() {
		return '.thrv_icon:not(.tve_lg_input_container .thrv_icon)';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		return array(
			'icon'       => array(
				'config' => array(
					'ModalPicker' => array(
						'config' => array(
							'label' => __( 'Choose Icon', 'thrive-cb' ),
						),
					),
					'ColorPicker' => array(
						'css_prefix' => '#tve_editor ',
						'css_suffix' => ' > :first-child',
						'config'     => array(
							'label' => __( 'Icon color', 'thrive-cb' ),
						),
					),
					'Slider'      => array(
						'config' => array(
							'default' => '30',
							'min'     => '8',
							'max'     => '200',
							'label'   => __( 'Icon size', 'thrive-cb' ),
							'um'      => array( 'px' ),
							'css'     => 'fontSize',
						),
					),
				),
			),
			'typography' => array( 'hidden' => true ),
			'shadow'     => array(
				'config' => array(
					'disabled_controls' => array( 'text' ),
				),
			),
			'layout'     => array(
				'config'            => array(),
				'disabled_controls' => array(
					'MaxWidth',
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

	/**
	 * Element category that will be displayed in the sidebar
	 * @return string
	 */
	public function category() {
		return $this->get_thrive_advanced_label();
	}
}
