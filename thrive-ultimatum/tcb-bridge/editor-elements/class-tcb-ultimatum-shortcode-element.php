<?php
/**
 * Created by PhpStorm.
 * User: ovidi
 * Date: 7/22/2017
 * Time: 6:24 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TCB_Ultimatum_Shortcode_Element extends TCB_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Ultimatum Shortcode', TVE_Ult_Const::T );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return '';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_ult_shortcode';
	}

	/**
	 * Hidden element
	 *
	 * @return string
	 */
	public function hide() {
		return true;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'ultimatum_shortcode' => array(
				'config' => array(),
			),
			'typography'          => array( 'hidden' => true ),
			'animation'           => array( 'hidden' => true ),
			'styles-templates'    => array( 'hidden' => true ),
			'responsive'          => array( 'hidden' => true ),
		);
	}
}
