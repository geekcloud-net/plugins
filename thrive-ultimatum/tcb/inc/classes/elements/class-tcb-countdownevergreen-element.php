<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/16/2017
 * Time: 12:38 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Countdownevergreen_Element
 */
class TCB_Countdownevergreen_Element extends TCB_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Countdown Evergreen', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'scarcity ';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'countdown_evergreen';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-countdown_timer_evergreen, .tve_countdown_timer_evergreen';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'countdownevergreen' => array(
				'config' => array(
					'CompleteText' => array(
						'config'  => array(
							'label'       => __( 'Text to show on complete', 'thrive-cb' ),
							'extra_attrs' => '',
							'label_col_x' => 12,
						),
						'extends' => 'TextInput',
					),
					'style'        => array(
						'config' => array(
							'label'   => __( 'Style', 'thrive-cb' ),
							'items'   => array(
								'tve_countdown_1' => array(
									'label'   => __( 'Simple', 'thrive-cb' ),
									'preview' => 'countdown_s1',
								),
								'tve_countdown_2' => array(
									'label'   => __( 'Rounded', 'thrive-cb' ),
									'preview' => 'countdown_s2',
								),
								'tve_countdown_3' => array(
									'label'   => __( 'Squared', 'thrive-cb' ),
									'preview' => 'countdown_s3',
								),
							),
							'default' => 'tve_countdown_1',
						),
					),
					'Color'        => array(
						'config'  => array(
							'default'             => 'f00',
							'label'               => __( 'Color', 'thrive-cb' ),
							'important'           => true,
							'style_default_color' => array(
								'.tve_countdown_2 .t-digits [class*="part-"]' => array( 'color' => '' ),
								'.tve_countdown_2 .tve_t_part'                => array( 'border-color' => '' ),
								'.tve_countdown_3 .t-digits'                  => array( 'background' => '' ),
							),
						),
						'extends' => 'ColorPicker',
					),
					'Day'          => array(
						'config'  => array(
							'name'      => __( 'Days', 'thrive-cb' ),
							'default'   => 10,
							'min'       => 0,
							'max'       => 366,
							'maxlength' => 3,
						),
						'extends' => 'Input',
					),
					'Hour'         => array(
						'config'  => array(
							'name'      => __( 'Hours', 'thrive-cb' ),
							'default'   => 10,
							'min'       => 0,
							'max'       => 23,
							'maxlength' => 2,
						),
						'extends' => 'Input',
					),
					'Minute'       => array(
						'config'  => array(
							'name'      => __( 'Minutes', 'thrive-cb' ),
							'default'   => 10,
							'min'       => 0,
							'max'       => 59,
							'maxlength' => 2,
						),
						'extends' => 'Input',
					),
					'Second'       => array(
						'config'  => array(
							'name'      => __( 'Seconds', 'thrive-cb' ),
							'default'   => 10,
							'min'       => 0,
							'max'       => 59,
							'maxlength' => 2,
						),
						'extends' => 'Input',
					),
					'ExpDay'       => array(
						'config'  => array(
							'name'      => __( 'Days', 'thrive-cb' ),
							'default'   => 10,
							'min'       => 0,
							'max'       => 366,
							'maxlength' => 3,
						),
						'extends' => 'Input',
					),
					'ExpHour'      => array(
						'config'  => array(
							'name'      => __( 'Hours', 'thrive-cb' ),
							'default'   => 10,
							'min'       => 0,
							'max'       => 23,
							'maxlength' => 2,
						),
						'extends' => 'Input',
					),
					'StartAgain'   => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Starts again after', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Checkbox',
					),
				),
			),
			'typography'         => array(
				'hidden' => true,
			),
			'shadow'             => array(
				'config' => array(
					'disabled_controls' => array( 'text' ),
				),
			),
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
