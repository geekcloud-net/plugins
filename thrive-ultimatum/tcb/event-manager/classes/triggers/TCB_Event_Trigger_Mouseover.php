<?php

/**
 * Created by PhpStorm.
 * User: radu
 * Date: 11.08.2014
 * Time: 15:31
 */
class TCB_Event_Trigger_Mouseover extends TCB_Event_Trigger_Abstract {
	/**
	 * should return the Event name
	 *
	 * @return mixed
	 */
	public function getName() {
		return __( 'Mouse over element', 'thrive-cb' );
	}

	public function get_options() {
		return array(
			'label' => __( 'Hover', 'thrive-cb' ),
			'name'  => $this->getName(),
		);
	}
}
