<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/10/2017
 * Time: 2:31 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Styledlistitem_Element
 *
 * This is a default element used for displaying only default menus for a component
 * It is not displayed in the sidebar elements
 */
class TCB_Styledlistitem_Element extends TCB_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'List Item', 'thrive-cb' );
	}

	/**
	 * Default element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-styled-list-item';
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
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'typography'       => array( 'hidden' => true ),
			'animation'        => array( 'hidden' => true ),
			'responsive'       => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
			'shadow'           => array(
				'config' => array(
					'disabled_controls' => array( 'text' ),
				),
			),
		);
	}
}
