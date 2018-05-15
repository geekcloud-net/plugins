<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_TOC_Element extends TCB_Element_Abstract {

	/**
	 * @return string|void
	 */
	public function name() {
		return __( 'Table of Contents', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'index,content';
	}


	/**
	 * @return string
	 */
	public function icon() {
		return 'table_contents';
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.thrv_contents_table';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		return array(
			'toc'        => array(
				'config' => array(
					'HeaderColor'    => array(
						'config'  => array(
							'label' => __( 'Header Color', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
						'to'      => '.tve_ct_title',
					),
					'HeadBackground' => array(
						'config'  => array(
							'label' => __( 'Background Color', 'thrive-cb' ),
						),
						'to'      => '.tve_ct_title',
						'extends' => 'ColorPicker',
					),
					'Headings'       => array(
						'config' => array(
							'name'   => __( 'Headings', 'thrive-cb' ),
							'inputs' => array(
								array(
									'name'  => 'h1',
									'label' => 'H1',
								),
								array(
									'name'  => 'h2',
									'label' => 'H2',
								),
								array(
									'name'  => 'h3',
									'label' => 'H3',
								),
								array(
									'name'  => 'h4',
									'label' => 'H4',
								),
								array(
									'name'  => 'h5',
									'label' => 'H5',
								),
								array(
									'name'  => 'h6',
									'label' => 'H6',
								),
							),
						),
					),
					'Columns'        => array(
						'config'  => array(
							'name'        => __( 'Number of Columns', 'thrive-cb' ),
							'label_col_x' => 9,
							'options'     => array(
								array(
									'value' => '1',
									'name'  => '1',
								),
								array(
									'value' => '2',
									'name'  => '2',
								),
								array(
									'value' => '3',
									'name'  => '3',
								),
							),
						),
						'extends' => 'Select',
					),
					'MinWidth'       => array(
						'config'  => array(
							'default' => 'auto',
							'min'     => '0',
							'max'     => '2000',
							'label'   => __( 'Minimum Width', 'thrive-cb' ),
							'um'      => array( 'px', '%' ),
							'css'     => 'min-width',
						),
						'extends' => 'Slider',
					),
					'MaxWidth'       => array(
						'config' => array(
							'default' => 'auto',
							'min'     => '100',
							'max'     => '2000',
							'label'   => __( 'Maximum Width', 'thrive-cb' ),
							'um'      => array( 'px', '%' ),
							'css'     => 'max-width',
						),
					),
				),
			),
			'background' => array(
				'config' => array(
					'to' => '.tve_contents_table',
				),
			),
			'borders'    => array(
				'config' => array(
					'css_suffix' => ' .tve_contents_table',
					'Borders'    => array(),
					'Corners'    => array(),
				),
			),
			'animation'  => array(
				'hidden' => true,
			),
			'typography' => array(
				'disabled_controls' => array(
					'[data-value="tcb-typography-letter-spacing"]',
				),
				'config'            => array(
					'to'         => '.tve_ct_content',
					'FontColor'  => array(
						'css_suffix' => ' .ct_column a',
					),
					'TextAlign'  => array(
						'css_suffix' => ' .ct_column',
					),
					'FontSize'   => array(
						'css_suffix' => ' .ct_column a',
					),
					'TextStyle'  => array(
						'css_suffix' => ' .ct_column a',
					),
					'LineHeight' => array(
						'css_suffix' => ' .ct_column a',
					),
				),
			),
			'shadow'     => array(
				'config' => array(
					'disabled_controls' => array( 'inner' ),
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
