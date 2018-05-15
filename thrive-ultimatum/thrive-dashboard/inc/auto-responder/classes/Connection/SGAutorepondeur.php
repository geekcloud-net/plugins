<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_Dash_List_Connection_SGAutorepondeur extends Thrive_Dash_List_Connection_Abstract {
	/**
	 * Return the connection type
	 * @return String
	 */
	public static function getType() {
		return 'autoresponder';
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return 'SG Autorepondeur';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function outputSetupForm() {
		$this->_directFormHtml( 'sg-autorepondeur' );
	}

	/**
	 * just save the key in the database
	 *
	 * @return mixed|void
	 */
	public function readCredentials() {

		if ( empty( $_POST['connection']['memberid'] ) ) {
			return $this->error( __( 'You must provide a valid SG-Autorepondeur Member ID', TVE_DASH_TRANSLATE_DOMAIN ) );
		}

		if ( empty( $_POST['connection']['key'] ) ) {
			return $this->error( __( 'You must provide a valid SG-Autorepondeur key', TVE_DASH_TRANSLATE_DOMAIN ) );
		}

		$this->setCredentials( $_POST['connection'] );

		$result = $this->testConnection();

		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Could not connect to SG-Autorepondeur using the provided key (<strong>%s</strong>)', TVE_DASH_TRANSLATE_DOMAIN ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		/** @var Thrive_Dash_List_Connection_Mandrill $related_api */
		$related_api = Thrive_Dash_List_Manager::connectionInstance( 'mandrill' );

		if ( ! empty( $mandrill_key ) ) {
			/**
			 * Try to connect to the email service too
			 */

			$related_api = Thrive_Dash_List_Manager::connectionInstance( 'mandrill' );
			$r_result    = true;
			if ( ! $related_api->isConnected() ) {
				$r_result = $related_api->readCredentials();
			}

			if ( $r_result !== true ) {
				$this->disconnect();

				return $this->error( $r_result );
			}
		} else {
			/**
			 * let's make sure that the api was not edited and disconnect it
			 */
			$related_api->setCredentials( array() );
			Thrive_Dash_List_Manager::save( $related_api );
		}

		return $this->success( __( 'Mailchimp connected successfully', TVE_DASH_TRANSLATE_DOMAIN ) );
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function testConnection() {
		/**
		 * just try getting a list as a connection test
		 */

		try {
			/** @var Thrive_Dash_Api_SGAutorepondeur $sg */
			$sg = $this->getApi();
			$sg->call( 'get_list' );

		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		return true;
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return mixed
	 */
	protected function _apiInstance() {
		return new Thrive_Dash_Api_SGAutorepondeur( $this->param( 'memberid' ), $this->param( 'key' ) );
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array|bool
	 */
	protected function _getLists() {

		try {
			/** @var Thrive_Dash_Api_SGAutorepondeur $sg */
			$sg = $this->getApi();

			$sg->set( 'limite', array( 0, 9999 ) );
			$raw = $sg->call( 'get_list' );

			$lists = array();

			if ( empty( $raw->reponse ) ) {
				return array();
			}
			foreach ( $raw->reponse as $item ) {
				$lists [] = array(
					'id'   => $item->listeid,
					'name' => $item->nom,
				);
			}

			return $lists;
		} catch ( Exception $e ) {
			$this->_error = $e->getMessage() . ' ' . __( "Please re-check your API connection details.", TVE_DASH_TRANSLATE_DOMAIN );

			return false;
		}
	}

	/**
	 * add a contact to a list
	 *
	 * @param mixed $list_identifier
	 * @param array $arguments
	 *
	 * @return bool|string true for success or string error message for failure
	 */
	public function addSubscriber( $list_identifier, $arguments ) {
		list( $first_name, $last_name ) = $this->_getNameParts( $arguments['name'] );

		/** @var Thrive_Dash_Api_SGAutorepondeur $api */
		$api = $this->getApi();

		$email = strtolower( $arguments['email'] );

		$api->set( 'listeid', $list_identifier );
		$api->set( 'email', $email );

		/**
		 * The names are inversed for a reason, SG will not accept
		 * sending only the first_name, so the first name needs to be set as the name
		 */
		if ( ! empty( $first_name ) && empty( $last_name ) ) {
			$api->set( 'name', $first_name );
		} elseif ( ! empty( $first_name ) && ! empty( $last_name ) ) {
			$api->set( 'first_name', $first_name );
			$api->set( 'name', $last_name );
		}

		if ( isset( $arguments['phone'] ) && ! empty( $arguments['phone'] ) ) {
			$api->set( 'telephone', $arguments['phone'] );
		}

		try {
			$api->call( 'set_subscriber' );

			return true;
		} catch ( Exception $e ) {
			return $e->getMessage() ? $e->getMessage() : __( 'Unknown SG-Autorepondeur Error', TVE_DASH_TRANSLATE_DOMAIN );
		}

	}

	/**
	 * Return the connection email merge tag
	 * @return String
	 */
	public static function getEmailMergeTag() {
		return '++email++';
	}

	/**
	 * disconnect (remove) this API connection
	 */
	public function disconnect() {

		$this->setCredentials( array() );
		Thrive_Dash_List_Manager::save( $this );

		return $this;
	}

}