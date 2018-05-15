<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/6/2017
 * Time: 5:27 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

require_once 'class-tcb-label-element.php';

/**
 * Class TCB_Label_Advanced_Element
 *
 * Basically is the label element with more features unlocked such as: layout, borders, background
 */
class TCB_Label_Advanced_Element extends TCB_Label_Element {

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-advanced-inline-text';
	}

	/**
	 * Removes the unnecessary components from the element json string
	 *
	 * @return array
	 */
	protected function general_components() {
		$general_components = parent::general_components();

		unset( $general_components['typography'], $general_components['animation'], $general_components['responsive'], $general_components['styles-templates'] );

		return $general_components;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['layout'] = array(
			'disabled_controls' => array(
				'MaxWidth',
				'Alignment',
				'.tve-advanced-controls',
				'hr',
			),
			'config'            => array(),
		);

		/**
		 * We remove all this indexes from the components array.
		 * The functionality from here will be handled in general_components function
		 * Reason: not to be added in the element json string
		 */
		unset( $components['typography'], $components['borders'], $components['animation'], $components['background'], $components['responsive'], $components['styles-templates'] );

		return $components;
	}
}
