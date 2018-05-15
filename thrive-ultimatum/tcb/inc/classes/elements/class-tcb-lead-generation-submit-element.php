<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Lead_Generation_Submit_Element extends TCB_Element_Abstract {

	public function name() {
		return __( 'Lead Generation Submit', 'thrive-cb' );
	}

	public function identifier() {
		return '.tve_lg_submit';
	}

	public function hide() {
		return true;
	}

	public function own_components() {
		$controls_default_config = array(
			'css_suffix' => ' button',
			'css_prefix' => '#tve_editor ',
		);

		return array(
			'lead_generation_submit' => array(
				'config' => array(
					'ModalPicker' => array(
						'config' => array(
							'label' => __( 'Add Icon', 'thrive-cb' ),
						),
					),
					'icon_side'   => array(
						'css_suffix' => ' .thrv_icon',
						'css_prefix' => '#tve_editor ',
						'config'     => array(
							'name'    => __( 'Icon Side', 'thrive-cb' ),
							'buttons' => array(
								array(
									'value' => 'left',
									'text'  => __( 'Left', 'thrive-cb' ),
								),
								array(
									'value' => 'right',
									'text'  => __( 'Right', 'thrive-cb' ),
								),
							),
						),
					),
					'ButtonColor' => array(
						'css_suffix' => ' button',
						'css_prefix' => '#tve_editor ',
						'config'     => array(
							'default'   => 'f00',
							'label'     => __( 'Button Color', 'thrive-cb' ),
							'important' => true,
						),
						'extends'    => 'ColorPicker',
					),
					'ButtonWidth' => array(
						'css_prefix' => '#tve_editor ',
						'config'     => array(
							'default' => '100',
							'min'     => '10',
							'max'     => '100',
							'label'   => __( 'Button width', 'thrive-cb' ),
							'um'      => array( '%' ),
							'css'     => 'width',
						),
						'extends'    => 'Slider',
					),
					'ButtonAlign' => array(
						'config'  => array(
							'name'    => __( 'Button Align', 'thrive-cb' ),
							'buttons' => array(
								array(
									'icon'    => 'a_left',
									'text'    => '',
									'value'   => 'left',
									'default' => true,
								),
								array(
									'icon'  => 'a_center',
									'text'  => '',
									'value' => 'center',
								),
								array(
									'icon'  => 'a_right',
									'text'  => '',
									'value' => 'right',
								),
								array(
									'icon'  => 'a_full-width',
									'text'  => '',
									'value' => 'justify',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'style'       => array(
						'css_suffix' => ' button',
						'css_prefix' => '#tve_editor ',
						'config'     => array(
							'label'   => __( 'Style', 'thrive-cb' ),
							'items'   => array(
								'default'      => __( 'Default', 'thrive-cb' ),
								'ghost'        => __( 'Ghost', 'thrive-cb' ),
								'rounded'      => __( 'Rounded', 'thrive-cb' ),
								'full_rounded' => __( 'Full Rounded', 'thrive-cb' ),
								'gradient'     => __( 'Gradient', 'thrive-cb' ),
								'elevated'     => __( 'Elevated', 'thrive-cb' ),
								'border_1'     => __( 'Border 1', 'thrive-cb' ),
								'border_2'     => __( 'Border 2', 'thrive-cb' ),
							),
							'default' => 'default',
						),
					),
				),
			),
			'typography'             => array(
				'config' => array(
					'FontSize'      => $controls_default_config,
					'FontColor'     => $controls_default_config,
					'TextAlign'     => $controls_default_config,
					'TextStyle'     => $controls_default_config,
					'TextTransform' => $controls_default_config,
					'FontFace'      => $controls_default_config,
					'LineHeight'    => $controls_default_config,
					'LetterSpacing' => $controls_default_config,
				),
			),
			'layout'                 => array(
				'disabled_controls' => array(
					'MaxWidth',
					'Alignment',
					'.tve-advanced-controls',
					'hr',
				),
				'config'            => array(
					'MarginAndPadding' => $controls_default_config,
				),
			),
			'borders'                => array(
				'config' => array(
					'Borders' => $controls_default_config,
					'Corners' => $controls_default_config,
				),
			),
			'animation'              => array(
				'hidden' => true,
			),
			'background'             => array(
				'config' => array(
					'ColorPicker' => $controls_default_config,
					'PreviewList' => $controls_default_config,
				),
			),
			'shadow'                 => array(
				'config' => $controls_default_config,
			),
			'styles-templates'       => array(
				'config' => array(
					'to' => 'button',
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
