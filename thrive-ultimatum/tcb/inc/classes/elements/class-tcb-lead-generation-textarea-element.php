<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Lead_Generation_Textarea_Element extends TCB_Element_Abstract {

	public function name() {
		return __( 'Lead Generation Textarea', 'thrive-cb' );
	}

	public function identifier() {
		return '.tve_lg_textarea';
	}

	public function hide() {
		return true;
	}

	public function own_components() {
		$controls_default_config = array(
			'css_suffix' => ' textarea',
			'css_prefix' => '#tve_editor ',
		);

		return array(
			'lead_generation_textarea' => array(
				'config' => array(
					'multiple_elements' => array(
						'config' => array(
							'default' => true,
							'label'   => __( 'Apply changes to similar elements', 'thrive-cb' ),
						),
					),
					'placeholder'       => array(
						'config' => array(
							'label_col_x' => 4,
							'label'       => __( 'Placeholder', 'thrive-cb' ),
						),
					),
					'icon_side'         => array(
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
					'ModalPicker'       => array(
						'config' => array(
							'label' => __( 'Add Icon', 'thrive-cb' ),
						),
					),
					'required'          => array(
						'config' => array(
							'default' => false,
							'label'   => __( 'Required field' ),
						),
					),
				),
			),
			'typography'               => array(
				'config' => array(
					'FontSize'      => $controls_default_config,
					'FontColor'     => $controls_default_config,
					'FontFace'      => $controls_default_config,
					'LetterSpacing' => $controls_default_config,
					'LineHeight'    => $controls_default_config,
					'TextAlign'     => $controls_default_config,
					'TextStyle'     => $controls_default_config,
					'TextTransform' => $controls_default_config,
				),
			),
			'layout'                   => array(
				'disabled_controls' => array(
					'MaxWidth',
					'Alignment',
					'.tve-advanced-controls',
					'hr',
				),
				'config'            => array(),
			),
			'borders'                  => array(
				'config' => array(
					'Borders' => $controls_default_config,
					'Corners' => $controls_default_config,
				),
			),
			'animation'                => array(
				'hidden' => true,
			),
			'background'               => array(
				'config' => array(
					'ColorPicker' => $controls_default_config,
					'PreviewList' => $controls_default_config,
				),
			),
			'shadow'                   => array(
				'config' => $controls_default_config,
			),
			'styles-templates'         => array(
				'config' => array(),
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
