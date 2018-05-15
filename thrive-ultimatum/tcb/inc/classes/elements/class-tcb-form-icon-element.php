<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

require_once 'class-tcb-icon-element.php';

class TCB_Form_Icon_Element extends TCB_Icon_Element {

	protected $_tag = 'icon';

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'icon,media';
	}


	/**
	 * @return string
	 */
	public function identifier() {
		return '.tve_lg_input_container .thrv_icon';
	}

	/**
	 * @return bool
	 */
	public function hide() {
		return true;
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
						'css_prefix' => '#tve_editor ',
						'config'     => array(
							'default' => '30',
							'min'     => '10',
							'max'     => '200',
							'label'   => __( 'Icon size', 'thrive-cb' ),
							'um'      => array( 'px' ),
							'css'     => 'fontSize',
						),
					),
				),
			),
			'typography' => array(
				'hidden' => true,
			),
			'layout'     => array(
				'config'            => array(
					'MarginAndPadding' => array(
						'css_prefix' => '#tve_editor ',
					),
				),
				'disabled_controls' => array(
					'MaxWidth',
					'Alignment',
					'.tve-advanced-controls',
				),
			),
			'borders'    => array(
				'config' => array(
					'Borders' => array(
						'css_prefix' => '#tve_editor ',
					),
					'Corners' => array(
						'css_prefix' => '#tve_editor ',
					),
				),
			),
			'animation'  => array(
				'hidden' => true,
			),
		);
	}
}
