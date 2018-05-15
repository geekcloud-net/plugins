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
 * Class TCB_Fillcounter_Element
 */
class TCB_Fillcounter_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Fill Counter', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'progress,fill';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'fill_counter';
	}

	/**
	 * Fillcounter element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_fill_counter, .thrv-fill-counter';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'fillcounter' => array(
				'config' => array(
					'CounterSize' => array(
						'config'  => array(
							'default' => '202',
							'min'     => '0',
							'max'     => '2000',
							'label'   => __( 'Counter Size', 'thrive-cb' ),
							'um'      => array( 'px' ),
						),
						'extends' => 'Slider',
					),
					'FillPercent' => array(
						'to'      => '.tve_fill_counter_n',
						'config'  => array(
							'default' => '75',
							'min'     => '0',
							'max'     => '100',
							'label'   => __( 'Fill Percentage', 'thrive-cb' ),
							'um'      => array( '%' ),
						),
						'extends' => 'Slider',
					),
					'FillColor'   => array(
						'to'      => '.tve_fill_counter_n',
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Fill Color', 'thrive-cb' ),
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
					'CircleColor' => array(
						'to'      => '.tve_fill_counter_n',
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Circle Color', 'thrive-cb' ),
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
					'InnerColor'  => array(
						'to'      => '.tve_fill_counter_n',
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Inner Color', 'thrive-cb' ),
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
				),
			),
			'layout'      => array(
				'disabled_controls' => array( 'MaxWidth', 'hr' ),
			),
			'typography'  => array( 'hidden' => true ),
			'background'  => array( 'hidden' => true ),
			'shadow'      => array(
				'config' => array(
					'disabled_controls' => array( 'inner', 'text' ),
				),
			),
			'animation'   => array( 'hidden' => true ),
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
