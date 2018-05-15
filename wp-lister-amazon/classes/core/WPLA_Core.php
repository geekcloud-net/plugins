<?php
/**
 * WPLA_Core
 *
 * This class contains methods that should be available for all classes
 * 
 */

if ( ! defined( 'DS' ) ) define( 'DS', DIRECTORY_SEPARATOR );

class WPLA_Core {
	
	static public $PLUGIN_URL;
	static public $PLUGIN_DIR;
	static public $PLUGIN_VERSION;

	const ParentTitle		= 'Amazon';
	const ParentName		= 'Amazon';
	const ParentPermissions	= 'manage_amazon_listings';
	const ParentMenuId		= 'wpla';
	
	const InputPrefix 		= 'wpla_';
	const OptionPrefix 		= 'wpla_';

	var $message;
	var $messages = array();
	var $EC;
	var $app_name;

	public function __construct() {

		$this->app_name = apply_filters( 'wpla_app_name', 'Amazon' );

		add_action( 'init', 				array( &$this, 'onWpInit' ), 5 ); // minimum priority is 5 (or saving profile will throw an error when fetching variation attributes)
		add_action( 'admin_init', 			array( &$this, 'onWpAdminInit' ) );

		$this->config();
	}

	// these methods can be overriden
	public function config() {
	}
	public function onWpInit() {
	}	
	public function onWpAdminInit() {
	}

	// Generic message display
	public function showMessage($message, $errormsg = false, $echo = false) {		
		if ( defined('WPLISTER_RESELLER_VERSION') ) $message = apply_filters( 'wpla_tooltip_text', $message );
		$class = ($errormsg) ? 'error' : 'updated fade';
		$class = ($errormsg == 2) ? 'update-nag' : $class; 	// top warning
		$message = '<div id="message" class="'.$class.'" style="display:block !important"><p>'.$message.'</p></div>';
		if ($echo) {
			echo $message;
		} else {
			$this->message .= $message;
		}
	}


	/* prefixed request handlers */
	protected function getAnswerFromPost( $insKey, $insPrefix = null ) {
		if ( is_null( $insPrefix ) ) {
			$insKey = self::InputPrefix.$insKey;
		}
		return ( isset( $_POST[$insKey] )? 'Y': 'N' );
	}
	
	protected function getValueFromPost( $insKey, $insPrefix = null ) {
		if ( is_null( $insPrefix ) ) {
			$insKey = self::InputPrefix.$insKey;
		}
		return ( isset( $_POST[$insKey] ) ? $_POST[$insKey] : false );
	}

	protected function requestAction() {
		if ( ( isset($_REQUEST['action']  ) ) && ( $_REQUEST['action']  != '' ) && ( $_REQUEST['action']  != '-1' ) ) return $_REQUEST['action'];
		if ( ( isset($_REQUEST['action2'] ) ) && ( $_REQUEST['action2'] != '' ) && ( $_REQUEST['action2'] != '-1' ) ) return $_REQUEST['action2'];
		return false;
	}

	
	/* prefixed option handlers */
	static public function getOption( $insKey, $default = null ) {
		return get_option( self::OptionPrefix.$insKey, $default );
	}
	
	static public function addOption( $insKey, $insValue ) {
		return add_option( self::OptionPrefix.$insKey, $insValue );
	}
	
	static public function updateOption( $insKey, $insValue ) {
		return update_option( self::OptionPrefix.$insKey, $insValue );
	}
	
	static public function deleteOption( $insKey ) {
		return delete_option( $insKey );
	}

	/* template methods */
	protected function getImageUrl( $insImage ) {
		return self::$PLUGIN_URL.'img/'.$insImage;
	}
	
	protected function getSubmenuPageTitle( $insTitle ) {
		return $insTitle.' - '.$this->app_name;
	}
	
	protected function getSubmenuId( $insId ) {
		return self::ParentMenuId.'-'.$insId;
	}

		
} // class WPLA_Core

