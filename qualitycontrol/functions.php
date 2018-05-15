<?php
/**
 * Theme functions file
 *
 * DO NOT MODIFY THIS FILE. Make a child theme instead: http://codex.wordpress.org/Child_Themes
 *
 * @package Quality Control
 * @author AppThemes
 */

// Constants
define( 'QC_VERSION', '0.8.1' );
define( 'QC_TICKET_PTYPE', 'ticket' );

define( 'APP_TD', 'qualitycontrol' );

global $qc_options;

// Framework
require_once( dirname( __FILE__ ) . '/framework/load.php' );
require_once( dirname( __FILE__ ) . '/framework/load-p2p.php' );

$load_files = array(
	'utils.php',
	'options.php',
	'core.php',
	'comments.php',
	'class-qc-taxonomy.php',
	'modules/states.php',
	'template-tags.php',
	'tickets.php',
	'views.php',
	'widgets.php',
);
appthemes_load_files( dirname( __FILE__ ) . '/includes/', $load_files );

$load_classes = array(
	'APP_User_Profile',
	'QC_Blog_Archive',
	'QC_Ticket_Create',
	'QC_Ticket_Single',
	'QC_Ticket_Search',
	'QC_Ticket_View',
	'QC_Ticket_Archive_Assigned',
	'QC_Ticket_Archive_Taxonomy',
	'QC_Ticket_Archive_Author',
	'QC_Ticket_Home', // needs to be last, for custom queries to have time to unset is_home
);
appthemes_add_instance( $load_classes );

// Admin only
if ( is_admin() ) {
	require_once( APP_FRAMEWORK_DIR . '/admin/importer.php' );

	$load_files = array(
		'admin.php',
		'dashboard.php',
		'install.php',
		'importer.php',
		'settings.php',
		'updates.php',
	);
	appthemes_load_files( dirname( __FILE__ ) . '/includes/admin/', $load_files );

	$load_classes = array(
		'QC_Dashboard',
		'QC_Options_Page' => $qc_options,
		'APP_System_Info',
	);
	appthemes_add_instance( $load_classes );

}


add_theme_support( 'app-versions', array(
	'update_page' => 'admin.php?page=app-settings&firstrun=1',
	'current_version' => QC_VERSION,
	'option_key' => 'qc_version',
) );

add_theme_support( 'app-wrapping' );

add_theme_support( 'app-login', array(
	'login' => 'form-login.php',
	'register' => 'form-registration.php',
	'recover' => 'form-password-recovery.php',
	'reset' => 'form-password-reset.php',
) );

add_action( 'after_setup_theme', 'qc_setup', 10 );

add_action( 'after_setup_theme', 'qc_theme_support_files', 12 );

add_filter( 'query_vars', 'qc_query_vars' );
add_filter( 'request', 'qc_search_shortcut' );

add_action( 'template_redirect', 'qc_scripts', 9 );
add_action( 'appthemes_before_login_template', 'qc_add_login_style' );
add_action( 'wp_head', 'qc_status_colors_css' );

add_filter( 'the_author', 'qc_the_author' );
add_filter( 'get_the_author_display_name', 'qc_the_author' );

add_filter( 'get_the_content', 'qc_create_ticket_link' );
add_filter( 'get_comment_text', 'qc_create_ticket_link' );

add_action( 'wp_insert_comment', '_qc_touch_post', 10, 2 );

add_action( 'add_admin_bar_menus', 'qc_admin_bar' );

add_action( 'appthemes_first_run', 'flush_rewrite_rules', 20 );

appthemes_init();
