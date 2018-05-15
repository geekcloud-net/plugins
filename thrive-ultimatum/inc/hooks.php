<?php
/**
 * Use this file to declare front-end hooks only
 */

global $tve_ult_frontend;

/**
 * Register required post types
 */
add_action( 'init', 'tve_ult_init' );

/**
 * initialize the update checker here because the required classes are loaded by dashboard at plugins_loaded
 */
add_action( 'init', 'tve_ult_update_checker' );

/**
 * init the shortcodes that need to be rendered
 */
add_action( 'init', array( 'TU_Shortcodes', 'init' ) );

add_action( 'widgets_init', 'tve_ult_register_widget' );

/**
 * Load text domain used for translations
 */
add_action( 'init', 'tve_ult_load_plugin_textdomain' );

/**
 * After plugin is loaded load ThriveDashboard Section
 */
add_action( 'plugins_loaded', 'tve_ult_load_dash_version' );

/**
 * logic to be applied on form conversion (successful submit) - TU will check if the conversion should start any campaign
 */
add_action( 'tve_leads_form_conversion', 'tve_ult_check_campaign_trigger', 10, 6 );

/**
 * add close button to editor
 */
add_action( 'admin_bar_menu', 'tve_ult_admin_bar', 100 );

/**
 * Add TU Product to Thrive Dashboard
 */
add_filter( 'tve_dash_installed_products', 'tve_ult_add_to_dashboard' );

/**
 * remove the white padding added by Thrive Themes surrounding the widget
 */
add_action( 'dynamic_sidebar_params', 'tve_ult_dynamic_sidebar_params' );

if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	/**
	 * Frontend handler - Ajax request - output campaign designs, if any
	 */
	add_action( 'wp_ajax_' . $tve_ult_frontend->ajax_load_action(), array( $tve_ult_frontend, 'ajax_load' ) );
	add_action( 'wp_ajax_nopriv_' . $tve_ult_frontend->ajax_load_action(), array( $tve_ult_frontend, 'ajax_load' ) );

	add_action( 'wp_ajax_' . $tve_ult_frontend->conversion_events_action(), array( $tve_ult_frontend, 'ajax_conversion_event_check' ) );
	add_action( 'wp_ajax_nopriv_' . $tve_ult_frontend->conversion_events_action(), array( $tve_ult_frontend, 'ajax_conversion_event_check' ) );

	add_filter( 'tve_dash_main_ajax_tu_lazy_load', array( $tve_ult_frontend, 'ajax_load' ), 10, 2 );
	add_filter( 'tve_dash_main_ajax_tu_conversion_events', array( $tve_ult_frontend, 'ajax_conversion_event_check' ), 10, 2 );

	/**
	 * register an impression for a campaign
	 */
	add_action( 'tve_ult_action_impression', 'tve_ult_register_impression' );
}

/**
 * Starting point for frontend logic:
 *
 * we use the wp_enqueue_scripts hook to check if a campaign should be displayed
 */
if ( is_admin() ) {
	/**
	 * Add features to the dashboard
	 */
	add_filter( 'tve_dash_features', 'tvu_dash_add_features' );
} else {

	//TODO: remove THIS
	add_filter( 'tcb_editor_javascript_params', 'tve_ult_append_shortcode_campaigns' );

	add_action( 'wp_enqueue_scripts', array( $tve_ult_frontend, 'hook_enqueue_scripts' ) );
	add_action( 'wp_footer', array( $tve_ult_frontend, 'hook_print_footer_scripts' ) );

	add_action( 'template_redirect', array( $tve_ult_frontend, 'hook_template_redirect' ), 2 );
}
