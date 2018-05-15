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
 * Class TCB_Landing_Page_Element
 */
class TCB_Landing_page_Element extends TCB_Element_Abstract {

	/**
	 * This is only available when editing landing pages
	 *
	 * @return bool
	 */
	public function is_available() {
		return tcb_post()->is_landing_page();
	}

	public function name() {
		return __( 'Thrive Landing Page', 'thrive-cb' );
	}

	/**
	 * Element identifier
	 *
	 * These settings apply directly on <body>, on landing pages
	 *
	 * @return string
	 */
	public function identifier() {
		return 'body.tve_lp';
	}

	/**
	 * Either to display or not the element in the sidebar menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * The HTML is generated from js
	 *
	 * @return string
	 */
	protected function html() {
		return '';
	}


	/**
	 * Hide all general components.
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'lp-base'          => array(
				'config' => array(
					'RemoveThemeCss'   => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Remove Theme CSS', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Checkbox',
					),
					'ContentWidth'     => array(
						'to'      => '#tve_editor',
						'config'  => array(
							'default' => '1080',
							'min'     => '100',
							'max'     => '2400',
							'label'   => __( 'Content Maximum Width', 'thrive-cb' ),
							'um'      => array( 'px', '%' ),
							'css'     => 'max-width',
						),
						'extends' => 'Slider',
					),
					'ContentFullWidth' => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Content covers entire screen width', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Checkbox',
					),
				),
			),
			'lp-fonts'         => array(
				'order' => 190,
			),
			'lp-scripts'       => array(
				'order' => 210,
			),
			'lp-advanced'      => array(
				'order' => 290,
			),
			'typography'       => array( 'hidden' => true ),
			'layout'           => array( 'hidden' => true ),
			'borders'          => array( 'hidden' => true ),
			'animation'        => array( 'hidden' => true ),
			'responsive'       => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
			'shadow'           => array( 'hidden' => true ),
		);
	}
}
