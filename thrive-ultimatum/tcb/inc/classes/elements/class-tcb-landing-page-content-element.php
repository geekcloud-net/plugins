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
 * Handles backwards-compatibility functionality for landing pages - main content area
 */
class TCB_Landing_Page_Content_Element extends TCB_Element_Abstract {

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve_lp_content';
	}

	/**
	 * This is only available on landing pages
	 *
	 * @return false|string
	 */
	public function is_available() {
		return tcb_post()->is_landing_page();
	}

	public function name() {
		return __( 'Landing Page Content', 'thrive-cb' );
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
			'typography'       => array( 'hidden' => true ),
			'animation'        => array( 'hidden' => true ),
			'responsive'       => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
			'layout'           => array(
				'disabled_controls' => array(
					'.tve-advanced-controls'
				),
				'config' => array(
					'MaxWidth' => array(
						'important' => true,
					),
				),
			),
			'borders'          => array(
				'config' => array(
					'Borders' => array(
						'important' => true,
					),
					'Corners' => array(
						'important' => true,
					),
				),
			),
			'shadow'           => array(
				'config' => array(
					'important'      => true,
					'default_shadow' => 'none',
				),
			),
		);
	}
}
