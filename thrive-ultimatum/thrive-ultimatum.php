<?php
/*
Plugin Name: Thrive Ultimatum
Plugin URI: https://thrivethemes.com
Version: 2.0.29
Author: <a href="https://thrivethemes.com">Thrive Themes</a>
Description: The ultimate scarcity plugin for Wordpress
Text Domain: thrive-ult
*/

require_once rtrim( dirname( __FILE__ ), '/\\' ) . '/class-tve-ult-const.php';

/**
 * This helps to display the errors on ajax requests too
 */
if ( defined( 'TVE_DEBUG' ) && TVE_DEBUG === true ) {
	ini_set( 'display_errors', 1 );
}

/**
 * At this point we need to either hook into an existing Content Builder plugin or use the copy we store in the tcb folder
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( ! file_exists( dirname( dirname( __FILE__ ) ) . '/thrive-visual-editor/thrive-visual-editor.php' ) || ! is_plugin_active( 'thrive-visual-editor/thrive-visual-editor.php' ) ) {
	include_once TVE_Ult_Const::plugin_path() . 'tcb-bridge/init.php';
	defined( 'EXTERNAL_TCB' ) || define( 'EXTERNAL_TCB', 0 );
} else {
	defined( 'EXTERNAL_TCB' ) || define( 'EXTERNAL_TCB', 1 );
}

/**
 * Bootstrap everything
 */
require_once TVE_Ult_Const::plugin_path() . 'start.php';

/**
 * Admin entry point
 */
if ( is_admin() ) {
	require_once TVE_Ult_Const::plugin_path() . 'admin/start.php';
}
