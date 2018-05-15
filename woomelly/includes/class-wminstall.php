<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WMInstall Class.
 */
class WMInstall {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'woomelly_warnings_deactivate' ), 0 );
		register_activation_hook( Woomelly()->get_file(), array( __CLASS__, 'woomelly_install' ) );
		register_deactivation_hook( Woomelly()->get_file(), array( __CLASS__, 'woomelly_deactivate' ) );
		add_filter( 'cron_schedules', array( __CLASS__, 'wm_sync_automatic_time' ) );
		add_action( 'woomelly_refresh_token_event', array( __CLASS__, 'woomelly_do_refresh_token_event' ), 10 );
		add_action( 'woomelly_sync_automatic_event', array( __CLASS__, 'woomelly_do_sync_automatic_event' ), 10 );
		add_filter( 'plugin_action_links_' . 'woomelly/woomelly.php', array( __CLASS__, 'woomelly_settings_action_links' ), 10, 1 );
	}

	/**
	 * woomelly_warnings_deactivate.
	 *
	 * @return string
	 */
	public static function woomelly_warnings_deactivate () {
		$errors = array();
		$_print = '';
		
		if( !is_plugin_active('woocommerce/woocommerce.php') ) {
			$errors[] = '<li type="square" style="margin-left: 20px;">'.__( "Plugin \"Woocommerce\" must be installed and active.", "woomelly" ).'</li>';
		}
		
		if ( count( $errors ) > 0 ) {
			$_print .= '
				<div class="error active-plugin-woomelly">
					<h4>'.sprintf( __( "Plugin \"Woomelly v.%s\" can not be installed for these reasons:", "woomelly" ), Woomelly()->get_version() ).'</h4>
					<ul>';
					foreach ( $errors as $value ) {
						$_print .= $value;
					}
					$_print .= '</ul>';
					$_print .= '<p>'.__( "Solve these problems and try to install it again.", "woomelly" ).'</p>';
			$_print .= '</div>';
			echo $_print;
			deactivate_plugins( 'woomelly/woomelly.php' );
		}
	} //End woomelly_warnings_deactivate()

	/**
	 * woomelly_install.
	 *
	 * @return void
	 */
	public static function woomelly_install () {
		global $wpdb, $charset_collate;

	    if ( ! wp_next_scheduled ( 'woomelly_refresh_token_event' ) ) {
			wp_schedule_event( time(), 'hourly', 'woomelly_refresh_token_event' );
	    }
	    if ( ! wp_next_scheduled ( 'woomelly_sync_automatic_event' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'woomelly_sync_automatic_event' );
	    }
        $wpdb->query( 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'wm_templatesync' . ' (
        				id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        				date datetime NOT NULL,
        				PRIMARY KEY (id)
    					) ' . $charset_collate
       	);
        $wpdb->query( 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'wm_templatesync_meta' . ' (
        				id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        				templatesync_id INT UNSIGNED NOT NULL,
        				templatesync_key VARCHAR(255) NOT NULL,
        				templatesync_value VARCHAR(255) DEFAULT "",
        				PRIMARY KEY (id)
    					) ' . $charset_collate
       	);
	} //End woomelly_install()

	/**
	 * woomelly_deactivate.
	 *
	 * @return void
	 */
	public static function woomelly_deactivate () {
		wp_clear_scheduled_hook( 'woomelly_refresh_token_event' );
		wp_clear_scheduled_hook( 'woomelly_sync_automatic_event' );
		delete_transient( '_status_meli_info' );
		$revoke_permiso_app = array();
		$woomelly_settings = new WMSettings();
		$revoke_permiso_app = WMeli::unlink();		    
		$woomelly_settings->set_access_token( '' );
		$woomelly_settings->set_expires_in( '' );
		$woomelly_settings->set_refresh_token( '' );
		$woomelly_settings->set_user_id( '' );
		$woomelly_settings->set_permalink( '' );
		$woomelly_settings->save();
	}

	/**
	 * woomelly_do_refresh_token_event.
	 *
	 * @return void
	 */
    public static function woomelly_do_refresh_token_event () {
		wm_refresh_token();
    } //End woomelly_do_refresh_token_event()

	/**
	 * woomelly_do_sync_automatic_event.
	 *
	 * @return void
	 */
    public static function woomelly_do_sync_automatic_event () {
    	$all_products = array();
    	$success = 0;
    	$error = 0;
    	$woomelly_get_settings = new WMSettings();
    	$woomelly_alive = Woomelly()->woomelly_is_connect();
	    
    	if ( $woomelly_get_settings->get_settings_sync_automatic() == true ) {
    		if ( $woomelly_alive ) {
		    	$all_products = wm_get_all_product( 'templatesync' );
		    	if ( !empty($all_products) ) {
		    		$total = count($all_products);
		    		foreach ( $all_products as $value ) {
		    			$result = Woomelly()->woomelly_do_sync_automatic_product_function( $value );
		    			if ( $result == 'success' ) {
		    				$success++;
		    			} else {
		    				$error++;
		    			}
		    		}
		    		$eficacia = intval( ($success*100)/$total );
		    		Woomelly()->woomelly_email_notification( sprintf(__("Number of products to be synchronized: %s. <br> Number of Products synchronized successfully: %s. <br> Product Quantity with synchronization errors: %s. <br> Synchronization efficiency: %s&#37;", "woomelly"), $total, $success, $error, $eficacia), 'summary_sync' );
		    	} else {
		    		Woomelly()->woomelly_email_notification( __('The synchronization could not be performed since there are no products configured correctly.', 'woomelly'), 'summary_sync' );
		    	}
	    	} else {
	    		Woomelly()->woomelly_email_notification( __( 'Sorry, you have a problem with your license or in connection with Mercadolibre', 'woomelly'), 'summary_sync' );
	    	}
    	}
    } //End woomelly_do_sync_automatic_event()

	/**
	 * woomelly_settings_action_links.
	 *
	 * @return array
	 */
	public static function woomelly_settings_action_links ( $links ) {
		$plugin_links = array();
		
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=woomelly-menu' ) . '">' . __( 'Overview', 'woomelly' ) . '</a>',
			'<a href="' . admin_url( 'admin.php?page=woomelly-settings' ) . '">' . __( 'Settings', 'woomelly' ) . '</a>'
		);
		
		return array_merge( $plugin_links, $links );
	} //End woomelly_settings_action_links()


	public static function wm_sync_automatic_time ( $schedules ) {
	    $woomelly_settings = new WMSettings();
		$_time = $woomelly_settings->get_settings_sync_automatic_time();

	    if ( $_time > 0 ) {
	    	if ( isset( $schedules['wm_sync_automatic_time'] ) ) {
	    		unset( $schedules['wm_sync_automatic_time'] );
	    	}
	        $schedules['wm_sync_automatic_time'] = array( 'interval' => ( $_time * 60 * 60 ), 'display' => sprintf(__("Every %s hour", "woomelly"), $_time) );
	    }

	    return $schedules;
	}
}

WMInstall::init();