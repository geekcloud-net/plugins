<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WMAdminExtensions', false ) ) {
	return;
}

/**
 * WMAdminExtensions Class.
 */
class WMAdminExtensions {
	
	public static function output() {
		$settings_extensions = array();
		$receive_data = false;
		$wm_settings_page = new WMSettings();

		$l_is_ok = Woomelly()->woomelly_status();
		$settings_extensions = $wm_settings_page->get_settings_extensions();

		include_once( Woomelly()->get_dir() . '/template/admin/extensions.php' );
    }
    
}
