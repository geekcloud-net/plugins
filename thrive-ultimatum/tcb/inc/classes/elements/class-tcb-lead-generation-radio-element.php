<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Lead_Generation_Radio_Element extends TCB_Element_Abstract {

	public function name() {
		return __( 'Lead Generation Radio', 'thrive-cb' );
	}

	public function identifier() {
		return '.tve_lg_radio';
	}

	public function hide() {
		return true;
	}

	public function own_components() {
		$controls_default_config = array(
			'css_suffix' => ' label',
			'css_prefix' => '#tve_editor ',
		);

		$columns = array();
		for ( $i = 1; $i <= 10; $i ++ ) {
			$col = array(
				'value' => $i,
				'name'  => sprintf( __( '%d column', 'thrive-cb' ), $i )
			);

			$columns[] = $col;
		}

		return array(
			'lead_generation_radio' => array(
				'config' => array(
					'columns_number' => array(
						'config'  => array(
							'options'     => $columns,
							'label_col_x' => 6,
							'name'        => 'Columns'
						),
						'extends' => 'Select',
					),
					'required'       => array(
						'config' => array(
							'default' => false,
							'label'   => __( 'Required field' ),
						),
					),
				),
			),
			'typography'            => array(
				'disabled_controls' => array(
					'TextAlign',
				),
				'config'            => array(
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
			'layout'                => array(
				'disabled_controls' => array(
					'MaxWidth',
					'Alignment',
					'.tve-advanced-controls',
					'hr',
				),
				'config'            => array(),
			),
			'borders'               => array(
				'config' => array(),
			),
			'animation'             => array(
				'hidden' => true,
			),
			'background'            => array(
				'config' => array(),
			),
			'shadow'                => array(
				'hidden' => true,
			),
			'styles-templates'      => array(
				'config' => array(),
			),
		);
	}
}
