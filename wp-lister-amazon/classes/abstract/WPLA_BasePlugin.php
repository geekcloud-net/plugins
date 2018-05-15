<?php
/**
 * WPLA_BasePlugin
 *
 * This class contains general purpose methods that are useful for most plugins.
 * (most methods were moved to WPLA_Core...)
 */

class WPLA_BasePlugin extends WPLA_Core {
	
	public function __construct() {
		parent::__construct();

		self::$PLUGIN_URL = WPLA_URL;
		self::$PLUGIN_DIR = WPLA_PATH;

		// add link to settings on plugins page
		add_action( 'plugin_action_links', array( &$this, 'onWpPluginActionLinks' ), 10, 4 );

		// required for saving custom screen options 
		add_filter('set-screen-option', array( &$this, 'set_screen_option_handler' ), 100, 3);
	}
	
	
	
	// add link to settings on plugins page
	public function onWpPluginActionLinks( $inaLinks, $insFile ) {

		if ( $insFile == 'wp-lister-amazon/wp-lister-amazon.php' ) {
			$sSettingsLink = '<a href="'.admin_url( "admin.php" ).'?page=wpla-settings">' . __( 'Settings', 'wpla' ) . '</a>';
			array_unshift( $inaLinks, $sSettingsLink );
		}
		return $inaLinks;
	}
	
	// required for saving custom screen options 
	function set_screen_option_handler($status, $option, $value) {
  		return $value;
	}


	// check if permissions require updating 
	function checkPermissions() {

		$role = get_role( 'administrator' );

		// check if admin has manage_amazon_options capability
		if ( ! isset( $role->capabilities['manage_amazon_options'] ) ) {

			$this->updatePermissions();

		}

	} // checkPermissions()

	// update permissions
	public function updatePermissions() {

		$roles = array('administrator', 'shop_manager', 'super_admin');
		foreach ($roles as $role) {
			$role = get_role($role);
			if ( empty($role) )
				continue;
	 
			$role->add_cap('manage_amazon_listings');
			$role->add_cap('manage_amazon_options');
			// $role->add_cap('prepare_amazon_listings');
			// $role->add_cap('publish_amazon_listings');

		}

	}


} // class WPLA_BasePlugin
