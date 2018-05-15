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
 * Class TCB_Tabs_Element
 */
class TCB_Tabs_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Tabs', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'tabs';
	}

	/**
	 * Tabs element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_tabs_shortcode, .thrv-tabbed-content';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'tabs'       => array(
				'config' => array(
					'TabLayout'     => array(
						'config'  => array(
							'name'        => __( 'Tabs layout', 'thrive-cb' ),
							'label_col_x' => 5,
							'options'     => array(
								array(
									'value' => 'horizontal',
									'name'  => __( 'Horizontal', 'thrive-cb' ),
								),
								array(
									'value' => 'vertical',
									'name'  => __( 'Vertical', 'thrive-cb' ),
								),
							),
						),
						'extends' => 'Select',
					),
					'TabsWidth'     => array(
						'config'  => array(
							'default' => '200',
							'min'     => '100',
							'max'     => '1000',
							'label'   => __( 'Tabs Width', 'thrive-cb' ),
							'um'      => array( 'px', '%' ),
							'css'     => 'width',
						),
						'extends' => 'Slider',
					),
					'DefaultTab'    => array(
						'config'  => array(
							'name'        => __( 'Default Tab', 'thrive-cb' ),
							'label_col_x' => 5,
							'options'     => array(),
						),
						'extends' => 'Select',
					),
					'EditTabs'      => array(
						'config'  => array(
							'name'        => __( 'Select which tabs are being edited', 'thrive-cb' ),
							'label_col_x' => 12,
							'extra_class' => 'margin-top-10',
							'options'     => array(
								array(
									'value'   => 'active',
									'name'    => __( 'Active Tabs', 'thrive-cb' ),
									'default' => true,
								),
								array(
									'value' => 'inactive',
									'name'  => __( 'Inactive Tabs', 'thrive-cb' ),
								),
							),
						),
						'extends' => 'Select',
					),
					'TabBackground' => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Tab Background', 'thrive-cb' ),
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
					'TabBorder'     => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Tab Border', 'thrive-cb' ),
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
					'ContentColor'  => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Content Background', 'thrive-cb' ),
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
					'ContentBorder' => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Content Border', 'thrive-cb' ),
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
				),
			),
			'typography' => array(
				'config' => array(
					'TextStyle' => array(
						'css_suffix' => ' .tve_scT .thrv-inline-text, .tve_scT p',
					),
				),
			),
			'animation'  => array( 'hidden' => true ),
			'background' => array( 'hidden' => true ),
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
