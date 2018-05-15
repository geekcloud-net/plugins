<?php
/**
 * Plugin Name: Page Builder Framework Premium Addon
 * Plugin URI: https://wp-pagebuilderframework.com/premium/
 * Description: Page Builder Framework Premium Addon
 * Version: 1.6.1
 * Author: MapSteps
 * Author URI: https://mapsteps.com
 * Text Domain: wpbfpremium
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'WPBF_PREMIUM_THEME_DIR', get_template_directory() );
define( 'WPBF_PREMIUM_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPBF_PREMIUM_TEMPLATES_DIR', plugin_dir_path( __FILE__ ) . 'inc/templates/' );
define( 'WPBF_PREMIUM_URI', plugin_dir_url( __FILE__ ) );
define( 'WPBF_PREMIUM_LICENSE_PAGE', 'wpbf-premium&tab=license' );
define( 'WPBF_PREMIUM_STORE_URL', 'https://wp-pagebuilderframework.com' );
define( 'WPBF_PREMIUM_PRODUCT_NAME', 'Page Builder Framework Premium Addon' );
define( 'WPBF_PREMIUM_ITEM_ID', 8707 );
define( 'WPBF_PREMIUM_VERSION', '1.6.1' );

// Load Plugin Updater if doesn't exist
if( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	include( dirname( __FILE__ ) . '/assets/edd/EDD_SL_Plugin_Updater.php' );
}

function wpbf_premium_plugin_updater() {

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'wpbf_premium_license_key' ) );

	// setup the updater
	$edd_updater = new EDD_SL_Plugin_Updater( WPBF_PREMIUM_STORE_URL, __FILE__, array(
			'version' 	=> '1.6.1',
			'license' 	=> $license_key,
			'item_id'	=> WPBF_PREMIUM_ITEM_ID,
			'author' 	=> 'MapSteps',
			'beta'		=> false,
		)
	);

}
add_action( 'admin_init', 'wpbf_premium_plugin_updater', 0 );

// Textdomain
add_action( 'plugins_loaded', 'wpbf_premium_textdomain' );
function wpbf_premium_textdomain() {
	load_plugin_textdomain( 'wpbfpremium', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}

// Set transient if Page Builder Framework is not active

$theme = wp_get_theme();

if ( 'Page Builder Framework' == $theme->name || 'Page Builder Framework' == $theme->parent_theme ) {

	delete_transient( 'wpbf_not_active' );

} else {

	 set_transient( 'wpbf_not_active', true );

}

// Set transient if old version of Page Builder Framework is being used
if ( 'wpbf' == $theme->get( 'TextDomain' ) || 'wpbf' == $theme->get( 'Template' ) ) {

	set_transient( 'wpbf_old_theme', true );

} else {

	delete_transient( 'wpbf_old_theme' );

}

// Get expiration date
function wpbf_premium_get_expiration_date() {

	$license_key = trim( get_option( 'wpbf_premium_license_key' ) );

	// return false if we don't have a license key
	if( !$license_key ) return false;

	$url = home_url();
	$api = "https://wp-pagebuilderframework.com/?edd_action=check_license&item_id=8707&license={$license_key}&url={$url}";

	$request = wp_remote_get( $api );

	// return false if we have an error
	if( is_wp_error( $request ) ) return false;

	$body = wp_remote_retrieve_body( $request );

	$data = json_decode( $body, true );

	$expiration = isset( $data['expires'] ) ? $data['expires'] : false;

	return $expiration;

}

// Save expiration date in a transient
if( !get_transient( 'wpbf_expiration_date' ) ) {

	$expiration_date = wpbf_premium_get_expiration_date();

	if( $expiration_date !== 'lifetime' && $expiration_date !== false ) {

		set_transient( 'wpbf_expiration_date', $expiration_date, 12 * HOUR_IN_SECONDS );

	}

}

// Admin Notices
function wpbf_premium_admin_notices() {

	if ( ( get_transient( 'wpbf_expiration_date' ) ) ) {

		$expiration_time = strtotime( get_transient( 'wpbf_expiration_date' ) );

		$notification_expiration_time = strtotime( '-28 days', $expiration_time );

		if ( $notification_expiration_time <= current_time( 'timestamp' ) ) {

			$class = 'notice notice-error';
			$license_key = trim( get_option( 'wpbf_premium_license_key' ) );
		    $renew_url = 'https://wp-pagebuilderframework.com/checkout/?edd_license_key='.$license_key.'&download_id=8707';
			$title = __( 'Page Builder Framework Premium Add-On.', 'wpbfpremium' );
			$description = sprintf( __( 'Your License expires in <strong>%1s</strong>. <a href="%2s" target="_blank">Renew your license</a> to keep getting feature updates & premium support.', 'wpbfpremium' ), human_time_diff( current_time( 'timestamp' ), $expiration_time ), $renew_url );

		printf( '<div class="%1s"><p><strong>%2s</strong><br>%3s</p></div>', $class, $title, $description );

		}

	}

	// Don't take this further if current user cannot manage options
	if( !current_user_can( 'manage_options' ) ) return;

	if ( ( get_transient( 'wpbf_not_active' ) ) ) {

		$class = 'notice notice-error';
		$message = __( 'You need to install/activate the Page Builder Framework Theme for the Premium Addon to work!', 'wpbfpremium' );

		printf( '<div class="%1s"><p>%2s</p></div>', esc_attr( $class ), esc_html( $message ) );

	}

	if ( ( get_transient( 'wpbf_old_theme' ) ) ) {

		$class = 'notice notice-error';
		$message = __( '<span style="text-transform:uppercase; background: #dc3232; border-radius:3px; color: #fff; padding: 10px 15px; font-size: 12px; margin-right: 5px; display: inline-block;">Caution!</span> Action required! Please update Page Builder Framework to the latest version. For help, please have a look at the <a href="https://wp-pagebuilderframework.com/docs/migration-guide/" target="_blank">migration guide</a>.', 'wpbfpremium' );

		printf( '<div class="%1s"><p>%2s</p></div>', esc_attr( $class ), $message );

	}

	$status = get_option( 'wpbf_premium_license_status' );

	if ( $status !== 'valid' ) {

		// stop here if we are on a multisite installation and not on the main site
		if( is_multisite() && !is_main_site() ) return;

		$class = 'notice notice-error';
		$message = __( 'Please <a href="'.get_admin_url().'themes.php?page='. WPBF_PREMIUM_LICENSE_PAGE .'">activate your license key</a> to receive updates for <strong>Page Builder Framework Premium Add-On.</strong> <a href="https://wp-pagebuilderframework.com/docs/installation/" target="_blank">Help</a>', 'wpbfpremium' );

		printf( '<div class="%1s"><p>%2s</p></div>', esc_attr( $class ), $message );

	}

}
add_action( 'admin_notices', 'wpbf_premium_admin_notices' );

/* Plugin Deactivation */

// delete transients on deactivation to clean up behind us
register_deactivation_hook( __FILE__, 'wpbf_premium_deactivation' );
function wpbf_premium_deactivation() {

	delete_transient( 'wpbf_not_active' );
	delete_transient( 'wpbf_old_theme' );
	delete_transient( 'wpbf_expiration_date' );

}

// Don't take it further if page builder framework isn't active
if( get_transient( 'wpbf_not_active' ) ) return;

// Required Files
require_once WPBF_PREMIUM_DIR . 'inc/init.php';
require_once WPBF_PREMIUM_DIR . 'assets/edd/license.php';

// Styles & Scripts
add_action( 'wp_enqueue_scripts', 'wpbf_premium_scripts', 11 );

function wpbf_premium_scripts() {

	// wpbf premium style
	wp_enqueue_style( 'wpbf-premium', WPBF_PREMIUM_URI . 'css/wpbf-premium.css', '', WPBF_PREMIUM_VERSION );

	// wpbf premium-scripts
	wp_enqueue_script( 'wpbf-premium', WPBF_PREMIUM_URI . 'js/site.js', array( 'jquery' ), WPBF_PREMIUM_VERSION, true );


	if ( get_theme_mod( 'menu_sticky' ) ) {

		// sticky navigation
		wp_enqueue_script( 'wpbf-sticky-navigation', WPBF_PREMIUM_URI . 'assets/js/sticky-navigation.js', array( 'jquery', 'wpbf-site' ), WPBF_PREMIUM_VERSION, true );

	}

	if( get_theme_mod( 'menu_position' ) == 'menu-off-canvas' || get_theme_mod( 'menu_position' ) == 'menu-off-canvas-left' ) {

		// off canvas
		wp_enqueue_script( 'wpbf-menu-off-canvas', WPBF_PREMIUM_URI . 'assets/js/off-canvas.js', array( 'jquery', 'wpbf-site' ), WPBF_PREMIUM_VERSION, true );

	}

	if( get_theme_mod( 'menu_position' ) == 'menu-full-screen' ) {

		// full screen
		wp_enqueue_script( 'wpbf-menu-full-screen', WPBF_PREMIUM_URI . 'assets/js/full-screen.js', array( 'jquery', 'wpbf-site' ), WPBF_PREMIUM_VERSION, true );

	}

	if( get_theme_mod( 'mobile_menu_options' ) == 'menu-mobile-off-canvas' ) {

		// full screen
		wp_enqueue_script( 'wpbf-mobile-menu-off-canvas', WPBF_PREMIUM_URI . 'assets/js/mobile-off-canvas.js', array( 'jquery', 'wpbf-site' ), WPBF_PREMIUM_VERSION, true );

	}

	if( get_theme_mod( 'sub_menu_animation' ) == 'zoom-in' || get_theme_mod( 'sub_menu_animation' ) == 'zoom-out' ) {

		// jQuery Transit
		wp_enqueue_script( 'wpbf-sub-menu-animation', WPBF_PREMIUM_URI . 'assets/js/jquery.transit.min.js', array( 'jquery', 'wpbf-site' ), '0.9.12', true );

	}	

}