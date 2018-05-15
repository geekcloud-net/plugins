<?php

/**
 * Created by PhpStorm.
 * User: radu
 * Date: 11.08.2014
 * Time: 15:33
 */
class TCB_Event_Trigger_Viewport extends TCB_Event_Trigger_Abstract {
	/**
	 * should return the Event name
	 *
	 * @return mixed
	 */
	public function getName() {
		return __( 'Comes into viewport', 'thrive-cb' );
	}

	/**
	 * this needs to listen to window scroll and trigger events if an element enters the viewport
	 *
	 * @return mixed|void
	 */
	public function outputGlobalJavascript() {
		include dirname( dirname( dirname( __FILE__ ) ) ) . '/views/js/trigger_viewport.php';
	}

	public function get_options() {
		return array(
			'label' => __( 'Into view', 'thrive-cb' ),
			'name'  => $this->getName(),
		);
	}
}
