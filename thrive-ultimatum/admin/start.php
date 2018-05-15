<?php
/**
 * Define Admin hooks and include required files
 */

require_once plugin_dir_path( __FILE__ ) . 'functions.php';

add_action( 'admin_init', 'tve_ult_admin_init' );
add_filter( 'tve_dash_admin_product_menu', 'tve_ult_admin_menu' );
add_action( 'admin_enqueue_scripts', 'tve_ult_admin_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'tve_ult_remove_conflicting_scripts', 10000 );
add_action( 'wp_ajax_tve_ult_admin_ajax_controller', 'tve_ult_admin_ajax_controller' );

if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	add_filter( 'thrive_display_options_get_templates', 'tve_ult_filter_display_settings_templates' );
	add_filter( 'thrive_display_options_get_template', 'tve_ult_filter_display_settings_get_template', 10, 2 );
}