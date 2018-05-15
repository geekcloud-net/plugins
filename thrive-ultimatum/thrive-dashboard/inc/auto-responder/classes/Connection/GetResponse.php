<?php

/**
 * Created by PhpStorm.
 * User: radu
 * Date: 03.04.2015
 * Time: 19:44
 */
class Thrive_Dash_List_Connection_GetResponse extends Thrive_Dash_List_Connection_Abstract {
	/**
	 * Return the connection type
	 *
	 * @return String
	 */
	public static function getType() {
		return 'autoresponder';
	}

	/**
	 * @return string the API connection title
	 */
	public function getTitle() {
		return 'GetResponse';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function outputSetupForm() {
		$this->_directFormHtml( 'get-response' );
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 *
	 * on error, it should register an error message (and redirect?)
	 *
	 * @return mixed
	 */
	public function readCredentials() {
		$key     = ! empty( $_POST['connection']['key'] ) ? $_POST['connection']['key'] : '';
		$url     = ! empty( $_POST['connection']['url'] ) ? $_POST['connection']['url'] : '';
		$version = ! empty( $_POST['connection']['version'] ) ? $_POST['connection']['version'] : '';

		if ( empty( $key ) ) {
			return $this->error( __( 'You must provide a valid GetResponse key', TVE_DASH_TRANSLATE_DOMAIN ) );
		}

		if ( $version && $version == 3 && empty( $url ) ) {
			return $this->error( __( 'You must provide a valid GetResponse V3 API URL', TVE_DASH_TRANSLATE_DOMAIN ) );
		}

		$this->setCredentials( $_POST['connection'] );

		$result = $this->testConnection();

		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Could not connect to GetResponse using the provided key (<strong>%s</strong>)', TVE_DASH_TRANSLATE_DOMAIN ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		return $this->success( __( 'GetResponse connected successfully', TVE_DASH_TRANSLATE_DOMAIN ) );
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function testConnection() {
		$gr = $this->getApi();
		/**
		 * just try getting a list as a connection test
		 */
		$credentials = $this->getCredentials();

		try {
			if ( ! $credentials['version'] || $credentials['version'] == 2 ) {
				/** @var Thrive_Dash_Api_GetResponse $gr */
				$gr->getCampaigns();
			} else {
				/** @var Thrive_Dash_Api_GetResponseV3 $gr */
				$gr->ping();
			}
		} catch ( Thrive_Dash_Api_GetResponse_Exception $e ) {
			return $e->getMessage();
		}

		return true;
	}

	/**
	 * Instantiate the API code required for this connection
	 *
	 * @return Thrive_Dash_Api_GetResponse|Thrive_Dash_Api_GetResponseV3
	 */
	protected function _apiInstance() {
		if ( ! $this->param( 'version' ) || $this->param( 'version' ) == 2 ) {
			return new Thrive_Dash_Api_GetResponse( $this->param( 'key' ) );
		} else {
			$getresponse = new Thrive_Dash_Api_GetResponseV3( $this->param( 'key' ), $this->param( 'url' ) );

			$enterprise_param = $this->param( 'enterprise' );
			if ( ! empty( $enterprise_param ) ) {
				$getresponse->enterprise_domain = $this->param( 'enterprise' );

			}

			return $getresponse;
		}
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array
	 */
	protected function _getLists() {
		/** @var Thrive_Dash_Api_GetResponse $gr */
		$gr = $this->getApi();

		try {
			$lists       = array();
			$items       = $gr->getCampaigns();
			$credentials = $this->getCredentials();

			if ( ! $credentials['version'] || $credentials['version'] == 2 ) {
				foreach ( $items as $key => $item ) {
					$lists [] = array(
						'id'   => $key,
						'name' => $item->name,
					);
				}
			} else {
				foreach ( $items as $item ) {
					$lists [] = array(
						'id'   => $item->campaignId,
						'name' => $item->name,
					);
				}
			}

			return $lists;
		} catch ( Exception $e ) {
			$this->_error = $e->getMessage();

			return false;
		}
	}

	/**
	 * add a contact to a list
	 *
	 * @param string $list_identifier
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function addSubscriber( $list_identifier, $arguments ) {

		$api         = $this->getApi();
		$credentials = $this->getCredentials();

		try {
			if ( ! $credentials['version'] || $credentials['version'] == 2 ) {
				if ( empty( $arguments['name'] ) ) {
					$arguments['name'] = ' ';
				}
				/** @var Thrive_Dash_Api_GetResponse $api */
				$api->addContact( $list_identifier, $arguments['name'], $arguments['email'], 'standard', (int) $arguments['get-response_cycleday'] );
			} else {


				if ( empty( $arguments['name'] ) ) {
					$arguments['name'] = ' ';
				}

				if ( empty( $arguments['phone'] ) ) {
					$arguments['phone'] = ' ';
				}

				$params = array(
					'name'       => $arguments['name'],
					'email'      => $arguments['email'],
					'dayOfCycle' => $arguments['get-response_cycleday'],
					'campaign'   => array(
						'campaignId' => $list_identifier,
					),
				);
				/** @var Thrive_Dash_Api_GetResponseV3 $api */
				$api->addContact( $params );
			}
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		return true;
	}

	/**
	 * Render extra html API setup form
	 *
	 * @see api-list.php
	 *
	 * @param array $params
	 */
	public
	function get_extra_settings(
		$params = array()
	) {
		return $params;
	}

	/**
	 * Render extra html API setup form
	 *
	 * @see api-list.php
	 *
	 * @param array $params
	 */
	public
	function renderExtraEditorSettings(
		$params = array()
	) {
		$this->_directFormHtml( 'getresponse/cycleday', $params );
	}

	/**
	 * Return the connection email merge tag
	 *
	 * @return String
	 */
	public
	static function getEmailMergeTag() {
		return '[[email]]';
	}
}
