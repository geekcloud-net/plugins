<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 7/20/2017
 * Time: 8:31 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TCB_Ultimatum_Countdown_Element extends TCB_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Ultimatum Countdown', TVE_Ult_Const::T );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'thrive, scarcity';
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'ultimatum';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrive_ultimatum_shortcode'; //For backwards compatibility
	}

	/**
	 * This is only a placeholder element
	 *
	 * @return bool
	 */
	public function is_placeholder() {
		return false;
	}

	/**
	 * Element HTML
	 *
	 * @return string
	 */
	public function html() {
		$content = '';
		ob_start();
		include TVE_Ult_Const::plugin_path( 'tcb-bridge/editor-layouts/elements/ultimatum-countdown.php' );
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return $this->get_thrive_integrations_label();
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'ultimatum_countdown' => array(
				'config' => array(),
			),
			'typography'          => array( 'hidden' => true ),
			'layout'              => array( 'hidden' => true ),
			'borders'             => array( 'hidden' => true ),
			'animation'           => array( 'hidden' => true ),
			'background'          => array( 'hidden' => true ),
			'styles-templates'    => array( 'hidden' => true ),
			'shadow'              => array( 'hidden' => true ),
		);
	}
}