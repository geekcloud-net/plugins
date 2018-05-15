<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WMAdminConnection', false ) ) {
	return;
}

/**
 * WMAdminConnection Class.
 */
class WMAdminConnection {
	
	public static function output() {
		$file_last_sync_log = __("There is no record.", "woomelly");
		$l_is_ok = Woomelly()->woomelly_status();
		$woomelly_alive = Woomelly()->woomelly_is_connect();
		$all_products = wm_get_all_product( 'templatesync' );
        /*if ( file_exists( WM_LAST_SYNC_LOG ) ) {
            $file_last_sync_log = file_get_contents( WM_LAST_SYNC_LOG );
        }*/
        
		include_once( Woomelly()->get_dir() . '/template/admin/connection.php' );
    }
    
}
