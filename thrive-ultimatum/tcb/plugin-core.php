<?php
/* global constants */
defined( 'TVE_VERSION' ) || DEFINE( "TVE_VERSION", '2.0.29' );
defined( 'TVE_TCB_DB_VERSION' ) || define( 'TVE_TCB_DB_VERSION', '1.1' );
defined( 'TVE_LANDING_PAGE_TEMPLATE' ) || DEFINE( "TVE_LANDING_PAGE_TEMPLATE", plugins_url() . '/thrive-visual-editor/landing-page/templates' );
defined( 'TVE_LANDING_PAGE_TEMPLATE_DOWNLOADED' ) || DEFINE( "TVE_LANDING_PAGE_TEMPLATE_DOWNLOADED", plugins_url() . '/../uploads/tcb_lp_templates/templates' );
/* will we need another key for Thrive Leads ? */
defined( 'TVE_EDITOR_FLAG' ) || define( 'TVE_EDITOR_FLAG', 'tve' );
defined( 'TVE_FRAME_FLAG' ) || define( 'TVE_FRAME_FLAG', 'tcbf' );
defined( 'TVE_TCB_CORE_INCLUDED' ) || define( 'TVE_TCB_CORE_INCLUDED', true );
defined( 'TVE_TCB_ROOT_PATH' ) || define( 'TVE_TCB_ROOT_PATH', plugin_dir_path( __FILE__ ) );
define( 'TCB_CT_POST_TYPE', 'tcb_content_template' );
define( 'TVE_CLOUD_TEMPLATES_FOLDER', 'tcb_content_templates' );


// global options
// all style sheet families listed below will be added to the editor.
global $tve_style_family_classes;
global $tve_thrive_shortcodes;
// append version to dynamically changed stylesheets, because browsers will cache them
$_version = get_bloginfo( 'version' );

$tve_style_family_classes = array(
	"Flat"    => 'tve_flt',
	"Classy"  => 'tve_clsy',
	"Minimal" => 'tve_min'
);

/* theme shortcodes available in TCB */
// list of shortcode identifier => callback function
/*
 * the callback function will be called with an array of attributes and must return a html code to be inserted into the DOM
 */
$tve_thrive_shortcodes = array(
	'optin'                               => 'tve_do_optin_shortcode',
	'posts_list'                          => 'tve_do_posts_list_shortcode',
	'custom_menu'                         => 'tve_do_custom_menu_shortcode',
	'custom_phone'                        => 'tve_do_custom_phone_shortcode',
	'post_grid'                           => 'tve_do_post_grid_shortcode',
	'widget_menu'                         => 'tve_render_widget_menu',
	'leads_shortcode'                     => 'tve_do_leads_shortcode',
	'tve_leads_additional_fields_filters' => 'tve_leads_additional_fields_filters',
	'social_default'                      => 'tve_social_render_default',
	'tvo_shortcode'                       => 'tvo_render_shortcode',
	'ultimatum_shortcode'                 => 'tve_ult_render_shortcode',
	'quiz_shortcode'                      => 'tqb_render_shortcode',
);

require_once plugin_dir_path( __FILE__ ) . 'inc/compat.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/helpers/social.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/functions.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/classes/class-tcb-editor-ajax.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/classes/class-tcb-editor.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/classes/class-tcb-elements.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/classes/class-tcb-font-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/classes/class-tcb-icon-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/classes/class-tcb-post.php';

/* init the Event Manager */
require_once plugin_dir_path( __FILE__ ) . 'event-manager/init.php';

/* include the admin menu settings page for the font manager */
if ( is_admin() && ! defined( 'TCB_ADMIN_INIT' ) ) {

	/* using the admin_menu hook to add links to the side admin menu */
	add_action( 'admin_menu', 'tcb_admin_init' );

	/* on ajax calls, we should also load the functionality for the Font Manager */
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		add_action( 'admin_init', 'tcb_admin_init' );
	}
// TODO: I think this the whole if can be deleted
	function tcb_admin_init() {
		/* init the font manager admin stuff. TODO: this will be moved also to the themes */
// Moved to class-tcb-admin
//		require_once plugin_dir_path( __FILE__ ) . 'inc/TCB_Product.php';
	}

	define( 'TCB_ADMIN_INIT', true );
}

add_action( 'admin_init', 'tve_revert_page_to_theme' );

/* ajax calls through WP API */
add_action( 'wp_ajax_tve_ajax_load', 'tve_ajax_load' );
add_action( 'wp_ajax_tve_change_style_family', 'tve_change_style_family' );
add_action( 'wp_ajax_tve_load_user_template', 'tve_load_user_template' );
add_action( 'wp_ajax_load_element_from_api', 'tve_load_element_from_api' );
add_action( 'wp_ajax_tve_landing_pages_load', 'tve_landing_pages_load' );
add_action( 'wp_ajax_tve_do_post_grid_shortcode', 'tve_do_post_grid_shortcode' );
add_action( 'wp_ajax_tve_ajax_update_option', 'tve_ajax_update_option' );
add_action( 'wp_ajax_tve_social_count', 'tve_social_ajax_count' );
add_action( 'wp_ajax_nopriv_tve_social_count', 'tve_social_ajax_count' );

add_action( 'wp_enqueue_scripts', 'tve_enqueue_editor_scripts' );

/**
 * always enqueue the dash frontend script
 */
add_filter( 'tve_dash_enqueue_frontend', '__return_true' );

/**
 * hook for social share counts via ajax
 */
add_filter( 'tve_dash_main_ajax_tcb_social', 'tve_social_dash_ajax_share_counts', 10, 2 );

/**
 * Autoresponder APIs AJAX calls
 */
if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || apply_filters( 'tve_leads_include_auto_responder', false ) ) {
	add_action( 'wp_ajax_tve_api_editor_actions', 'tve_api_editor_actions' );

	/**
	 * submit Lead Generation form element via AJAX
	 */
	add_action( 'wp_ajax_nopriv_tve_api_form_submit', 'tve_api_form_submit' );
	add_action( 'wp_ajax_tve_api_form_submit', 'tve_api_form_submit' );

	add_action( 'wp_ajax_nopriv_tve_custom_form_submit', 'tve_custom_form_submit' );
	add_action( 'wp_ajax_tve_custom_form_submit', 'tve_custom_form_submit' );
}

/** CONTENT REVISION HOOKS */
/**
 * Append fields to be tracked of changes
 * This filter is called in revisions view
 */
add_filter( '_wp_post_revision_fields', 'tve_post_revision_fields', 10, 1 );
/** Restore content to revision */
add_action( 'wp_restore_post_revision', 'tve_restore_post_to_revision', 11, 2 );
/** Decide if post has changed and save a revision for it */
add_filter( 'wp_save_post_revision_post_has_changed', 'tve_post_has_changed', 10, 3 );

add_action( 'wp_enqueue_scripts', 'tve_remove_conflicting_scripts', 100 );

// add TCB buttons to admin post/page listing screen
add_filter( 'page_row_actions', 'thrive_page_row_buttons', 10, 2 );
add_filter( 'post_row_actions', 'thrive_page_row_buttons', 10, 2 );

/* we need to always load this into the head section, because some themes styles will overwrite the font settings */
add_action( 'wp_head', 'tve_load_font_css' );

/* load meta tags so scrapers can find them */
add_action( 'wp_head', 'tve_load_meta_tags' );

// add thrive edit link to admin bar
add_action( 'admin_bar_menu', 'thrive_editor_admin_bar', 100 );

// To fight against themes creating custom wpautop scripts and injecting rogue <br/> and <p> tags into content we have to apply shortcodes early, then add our content to the page
// at priority 101, hence the two separate "the_content" actions
add_action( 'wp', 'tve_wp_action' );
function tve_wp_action() {
	add_filter( 'the_content', 'tve_clean_wp_editor_content', - 100 );
	add_filter( 'the_content', 'tve_editor_content', is_editor_page() ? PHP_INT_MAX : 10 );
}

// this is a fix for the "Pitch" theme that tries to use a backend function get_current_screen() in a media library filter that we run in the front end and therefore breaks the page.
add_action( "after_setup_theme", "tve_turn_off_get_current_screen" );

// manipulate social sharing hooks so that they work with TCB
if ( has_filter( "dd_hook_wp_content" ) ) {
	remove_filter( 'the_content', 'dd_hook_wp_content' );
	add_filter( 'the_content', 'dd_hook_wp_content', 103 );
}

// make sure WP editor page doesn't overwrite TCB content
add_filter( 'is_protected_meta', 'tve_hide_custom_fields', 10, 2 );

// use settings API to store non post-level settings
add_action( 'init', 'tve_global_options_init' );

/* hook to fix various conflicts that might appear. first one: YARPP */
add_action( 'init', 'tve_fix_plugin_conflicts', PHP_INT_MAX );

/* hook to defined location of translations files */
add_action( 'init', 'tve_load_plugin_textdomain' );

/* hook for displaying the main editor page ( control panel + content frame ) - only if the tve param is present */
if ( ! empty( $_REQUEST[ TVE_EDITOR_FLAG ] ) ) {
	add_action( 'template_redirect', array( tcb_editor(), 'hook_template_redirect' ), 0 );
}

// hook for detecting if a post is setup as a Custom Editable piece of content
add_action( 'template_redirect', 'tcb_custom_editable_content', 9 );

/**
 * filter used to clean meta-data stuff from the content, when displaying it on frontend, e.g.: lead generation code being saved in the HTML causes SEO issues
 */
add_filter( 'tcb_clean_frontend_content', 'tcb_clean_frontend_content' );

/**
 * init the Pinterest SDK
 */
add_action( 'tve_socials_init_pinterest', 'tve_socials_init_pinterest' );

add_filter( 'tve_filter_custom_fonts_for_enqueue_in_editor', 'tve_filter_custom_fonts_for_enqueue_in_editor' );

/**
 * shows a message in the main media uploader window that states: "Only .xxx files are allowed"
 */
add_action( 'post-upload-ui', 'tve_media_restrict_filetypes' );

/* only TCB-specific classes should be loaded here */
add_action( 'init', 'tve_load_tcb_classes' );

add_action( 'wp_footer', array( tcb_editor(), 'inner_frame_menus' ), 100 );
add_action( 'wp', array( tcb_editor(), 'clean_inner_frame' ) );

/**
 * Actions used for handling the interim login ( login via popup in TCB editor page )
 */
add_action( 'login_footer', 'tcb_interim_login_footer' );
add_action( 'set_logged_in_cookie', 'tcb_store_interim_login_id', 10, 4 );
