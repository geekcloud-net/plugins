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
 * Class TCB_Menu_Element
 */
class TCB_Menu_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Custom Menu', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'navigation menu, nav, nav menu';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'menu';
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_widget_menu';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'menu'       => array(
				'config' => array(
					'MainColor'            => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Main Color', 'thrive-cb' ),
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
					'ChildColor'           => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Child Color', 'thrive-cb' ),
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
					'ChildBackground'      => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Child Background', 'thrive-cb' ),
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
					'HoverMainColor'       => array(
						'config'  => array(
							'default' => 'CE271B',
							'label'   => __( 'Main Color', 'thrive-cb' ),
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
					'HoverMainBackground'  => array(
						'config'  => array(
							'default' => 'CE271B',
							'label'   => __( 'Main Background', 'thrive-cb' ),
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
					'HoverChildColor'      => array(
						'config'  => array(
							'default' => 'fff',
							'label'   => __( 'Child Color', 'thrive-cb' ),
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
					'HoverChildBackground' => array(
						'config'  => array(
							'default' => 'CE271B',
							'label'   => __( 'Child Background', 'thrive-cb' ),
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
					'TriggerColor'         => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Menu Icon Color', 'thrive-cb' ),
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
					'SelectMenu'           => array(),
					'MenuDirection'        => array(),
					'MakePrimary'          => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Make this primary menu', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Checkbox',
					),
				),
			),
			'typography' => array(
				'disabled_controls' => array(
					'FontColor',
					'.typography-text-align-style-hr',
					'.tve-advanced-controls',
				),
			),
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
