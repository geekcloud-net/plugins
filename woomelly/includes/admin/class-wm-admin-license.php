<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WMAdminLicense', false ) ) {
	return;
}

/**
 * WMAdminDashboard Class.
 */
class WMAdminLicense {
	
	public static function output() {
		$form = Woomelly()->woomelly_get_form_license();		
		
		include_once( Woomelly()->get_dir() . '/template/admin/license.php' );
    }
    
}
