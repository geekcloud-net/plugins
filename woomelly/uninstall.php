<?php

/**
 * 
 * This file runs when the plugin in uninstalled (deleted).
 * This will not run when the plugin is deactivated.
 * Ideally you will add all your clean-up scripts here
 * that will clean-up unused meta, options, etc. in the database.
 *
 */

// If plugin is not being uninstalled, exit (do nothing)
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/*
 * Only remove ALL product and page data if WM_REMOVE_ALL_DATA constant is set to true in user's
 * wp-config.php. This is to prevent data loss when deleting the plugin from the backend
 * and to ensure only the site owner can perform this action.
 */
if ( defined( 'WM_REMOVE_ALL_DATA' ) && WM_REMOVE_ALL_DATA == true ) {
	global $wpdb;
    
    $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "wm_templatesync" );
    $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "wm_templatesync_meta" );
	delete_option( 'woomelly_settings' );
    $upload_dir = wp_upload_dir( null, false );
    if ( file_exists( $upload_dir['basedir'] . '/woomelly_debug.log' ) )
    	@unlink( $upload_dir['basedir'] . '/woomelly_debug.log' );
    if ( file_exists( $upload_dir['basedir'] . '/woomelly_error.log' ) )
    	@unlink( $upload_dir['basedir'] . '/woomelly_error.log' );
    if ( file_exists( $upload_dir['basedir'] . '/woomelly_last_sync.log' ) )
    	@unlink( $upload_dir['basedir'] . '/woomelly_last_sync.log' );
    if ( file_exists( $upload_dir['basedir'] . '/woomelly_sync.log' ) )
    	@unlink( $upload_dir['basedir'] . '/woomelly_sync.log' );
    if ( file_exists( $upload_dir['basedir'] . '/woomelly_notification.log' ) )
        @unlink( $upload_dir['basedir'] . '/woomelly_notification.log' );
}