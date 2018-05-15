<?php

class Thrive_Dash_Api_SGAutorepondeur {

	private $_membreid;
	private $_codeactivation;
	private $_datas = array();
	private $_apiUrl = 'https://sg-autorepondeur.com/API_V2/';

	public function __construct( $membreid, $codeactivation ) {
		$this->_membreid       = $membreid;
		$this->_codeactivation = $codeactivation;

		$this->_datas['membreid']       = $this->_membreid;
		$this->_datas['codeactivation'] = $this->_codeactivation;
	}

	public function set( $name, $value = '' ) {
		if ( is_array( $name ) ) {
			foreach ( $name as $id => $value ) {
				$this->set( $id, $value );
			}
		} else {
			$this->_datas[ $name ] = $value;
		}

		return $this;
	}

	public function call( $action ) {
		$this->_datas['action'] = $action;

		$result = tve_dash_api_remote_post( $this->_apiUrl, array(
			'body'      => $this->_datas,
			'timeout'   => 15,
			'sslverify' => false,
		) );

		if ( is_wp_error( $result ) ) {
			$error_message = $result->get_error_message();
			throw new Exception( $error_message );
		} else {
			$body = wp_remote_retrieve_body($result);
			$decoded = json_decode($body);

			if($decoded->valid == false) {
				throw new Exception( $decoded->reponse[0] );
			}
			return $decoded;
		}
	}
}