<?php

class TU_Request_Handler {
	/**
	 * Search the key in request and returns it
	 * If the key does not exists default value is returned
	 *
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	protected function param( $key, $default = null ) {
		return isset( $_POST[ $key ] ) ? $_POST[ $key ] : ( isset( $_REQUEST[ $key ] ) ? $_REQUEST[ $key ] : $default );
	}

	/**
	 * forward the call based on the $action parameter
	 * API entry-point for the template chooser lightbox (from the editor)
	 *
	 * @param string $action
	 */
	public function api( $action ) {
		$method = 'api_' . $action;

		$result = call_user_func( array( $this, $method ) );

		wp_send_json( $result );
	}
}
