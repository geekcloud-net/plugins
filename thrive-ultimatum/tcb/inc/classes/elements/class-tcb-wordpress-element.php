<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 4/10/2017
 * Time: 10:15 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Wordpress_Element
 */
class TCB_Wordpress_Element extends TCB_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Wordpress Content', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'wp';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'wordpress';
	}

	/**
	 * Wordpress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve_wp_shortcode'; // For backwards compatibility
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'wordpress'  => array(
				'config' => array(),
			),
			'typography' => array( 'hidden' => true ),
			'borders'    => array( 'hidden' => true ),
			'animation'  => array( 'hidden' => true ),
			'background' => array( 'hidden' => true ),
			'shadow'     => array( 'hidden' => true ),
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
