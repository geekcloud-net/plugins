<?php
/**
 * Versions
 *
 * @package Framework\Versions
 */

appthemes_init_redirect();

add_action( 'admin_init', 'appthemes_update_redirect' );
add_action( 'appthemes_first_run', 'appthemes_updated_version_notice', 999 );


define( 'APP_UPDATE_TRANSIENT', 'app_update_version' );

function appthemes_init_redirect() {
	// When the theme is activated, go straight to the settings page.
	if ( isset( $_GET['activated'] ) && 'themes.php' == $GLOBALS['pagenow'] ) {
		list( $args ) = get_theme_support( 'app-versions' );

		wp_redirect( admin_url( $args['update_page'] ) );
		exit;
	}
}

function appthemes_update_redirect() {
	if ( ! current_user_can( 'manage_options' ) || defined( 'DOING_AJAX' ) )
		return;

	list( $args ) = get_theme_support( 'app-versions' );

	if ( $args['current_version'] == get_option( $args['option_key'] ) )
		return;

	// prevents infinite redirect
	if ( $args['current_version'] == get_transient( APP_UPDATE_TRANSIENT ) )
		return;

	set_transient( APP_UPDATE_TRANSIENT, $args['current_version'] );

	wp_redirect( admin_url( $args['update_page'] ) );
	exit;
}

function appthemes_updated_version_notice() {
	list( $args ) = get_theme_support( 'app-versions' );

	if ( $args['current_version'] != get_transient( APP_UPDATE_TRANSIENT ) )
		return;

	update_option( $args['option_key'], $args['current_version'] );

	echo scb_admin_notice( sprintf(
		__( 'Successfully updated to version %s.', APP_TD ),
		$args['current_version']
	) );

	delete_transient( APP_UPDATE_TRANSIENT );
}

