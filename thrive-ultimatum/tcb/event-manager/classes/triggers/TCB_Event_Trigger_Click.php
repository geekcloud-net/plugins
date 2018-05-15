<?php

/**
 * Created by PhpStorm.
 * User: radu
 * Date: 11.08.2014
 * Time: 15:25
 */
class TCB_Event_Trigger_Click extends TCB_Event_Trigger_Abstract {
	/**
	 * should return the Event name
	 *
	 * @return mixed
	 */
	public function getName() {
		return __( 'Click on element', 'thrive-cb' );
	}

	public function get_options() {
		return array(
			'label' => __( 'Click', 'thrive-cb' ),
			'name'  => $this->getName(),
		);
	}
}
