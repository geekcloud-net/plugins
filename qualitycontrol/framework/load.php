<?php
/**
 * AppThemes Framework load
 *
 * @package Framework
 */

define( 'APP_FRAMEWORK_DIR', dirname( __FILE__ ) );
if ( ! defined( 'APP_FRAMEWORK_DIR_NAME' ) )
	define( 'APP_FRAMEWORK_DIR_NAME', 'framework' );

if ( ! defined( 'APP_FRAMEWORK_URI' ) )
	define( 'APP_FRAMEWORK_URI', get_template_directory_uri() . '/' . APP_FRAMEWORK_DIR_NAME );

// scbFramework
require_once dirname( __FILE__ ) . '/scb/load.php';

require_once dirname( __FILE__ ) . '/kernel/functions.php';

// Theme specific items
if ( appthemes_in_template_directory() ) {
	// Default filters
	add_filter( 'wp_title', 'appthemes_title_tag', 9 );
	add_action( 'wp_head', 'appthemes_favicon' );
	add_action( 'admin_head', 'appthemes_favicon' );

	appthemes_load_textdomain();
}

require_once dirname( __FILE__ ) . '/kernel/hook-deprecator.php';
require_once dirname( __FILE__ ) . '/kernel/deprecated.php';
require_once dirname( __FILE__ ) . '/kernel/hooks.php';

require_once dirname( __FILE__ ) . '/kernel/view-types.php';
require_once dirname( __FILE__ ) . '/kernel/view-edit-profile.php';

require_once dirname( __FILE__ ) . '/kernel/mail-from.php';

require_once dirname( __FILE__ ) . '/kernel/social.php';

if ( defined( 'WP_DEBUG' ) && WP_DEBUG )
	require_once dirname( __FILE__ ) . '/kernel/debug.php';

require_once dirname( __FILE__ ) . '/kernel/notices.php';

// Breadcrumbs plugin
if ( !is_admin() && !function_exists( 'breadcrumb_trail' ) ) {
	require_once dirname( __FILE__ ) . '/kernel/breadcrumb-trail.php';
}

function _appthemes_after_scb_loaded() {
	if ( is_admin() ) {
		require_once dirname( __FILE__ ) . '/admin/functions.php';

		require_once dirname( __FILE__ ) . '/admin/class-dashboard.php';
		require_once dirname( __FILE__ ) . '/admin/class-tabs-page.php';
		require_once dirname( __FILE__ ) . '/admin/class-system-info.php';
		require_once dirname( __FILE__ ) . '/admin/class-meta-box.php';
		require_once dirname( __FILE__ ) . '/admin/class-attachments-metabox.php';

	}
}
scb_init( '_appthemes_after_scb_loaded' );

function _appthemes_load_features() {

	if ( current_theme_supports( 'app-wrapping' ) )
		require_once dirname( __FILE__ ) . '/includes/wrapping.php';

	if ( current_theme_supports( 'app-geo' ) )
		require_once dirname( __FILE__ ) . '/includes/geo.php';

	if ( current_theme_supports( 'app-login' ) ) {
		require_once dirname( __FILE__ ) . '/includes/views-login.php';

		list( $templates ) = get_theme_support( 'app-login' );

		new APP_Login( $templates['login'] );
		new APP_Registration( $templates['register'] );
		new APP_Password_Recovery( $templates['recover'] );
		new APP_Password_Reset( $templates['reset'] );
	}

	if ( current_theme_supports( 'app-feed' ) )
		add_filter( 'request', 'appthemes_modify_feed_content' );

	if ( current_theme_supports( 'app-stats' ) ) {
		require_once dirname( __FILE__ ) . '/includes/stats.php';
		APP_Post_Statistics::init();
	}

	if ( current_theme_supports( 'app-open-graph' ) ) {
		require_once dirname( __FILE__ ) . '/includes/open-graph.php';

		list( $args ) = get_theme_support( 'app-open-graph' );
		new APP_Open_Graph( (array) $args );
	}

	if ( is_admin() && current_theme_supports( 'app-versions' ) )
		require_once dirname( __FILE__ ) . '/admin/versions.php';

	if ( current_theme_supports( 'app-search-index' ) )
		require_once dirname( __FILE__ ) . '/includes/search-index.php';

	if ( current_theme_supports( 'app-comment-counts' ) )
		require_once dirname( __FILE__ ) . '/includes/comment-counts.php';

	if ( current_theme_supports( 'app-term-counts' ) )
		require_once dirname( __FILE__ ) . '/includes/term-counts.php';

	if ( current_theme_supports( 'app-plupload' ) )
		require_once dirname( __FILE__ ) . '/app-plupload/app-plupload.php';

	if ( current_theme_supports( 'app-slider' ) )
		require_once dirname( __FILE__ ) . '/includes/slider/slider.php';

	if ( current_theme_supports( 'app-media-manager' ) ) {
		require_once dirname( __FILE__ ) . '/media-manager/media-manager.php';

		// init media manager
		new APP_Media_Manager;
	}

	// init notices
	APP_Notices::init();

	do_action( 'appthemes_framework_loaded' );
}
add_action( 'after_setup_theme', '_appthemes_load_features', 999 );


function _appthemes_register_scripts() {

	require_once APP_FRAMEWORK_DIR . '/js/localization.php';

	wp_register_style( 'jquery-ui-style', APP_FRAMEWORK_URI . '/styles/jquery-ui/jquery-ui.css', false, '1.10.3' );

	wp_register_script( 'colorbox', APP_FRAMEWORK_URI . '/js/colorbox/jquery.colorbox.min.js', array( 'jquery' ), '1.5.3' );
	wp_register_style( 'colorbox', APP_FRAMEWORK_URI . '/js/colorbox/colorbox.css', false, '1.5.3' );

	wp_register_script( 'validate', APP_FRAMEWORK_URI . '/js/validate/jquery.validate.min.js', array( 'jquery' ), '1.11.1' );

	wp_register_script( 'footable', APP_FRAMEWORK_URI . '/js/footable/jquery.footable.min.js', array( 'jquery' ), '2.0.1.2' );
	wp_register_script( 'footable-sort', APP_FRAMEWORK_URI . '/js/footable/jquery.footable.sort.min.js', array( 'footable' ), '2.0.1.2' );
	wp_register_script( 'footable-filter', APP_FRAMEWORK_URI . '/js/footable/jquery.footable.filter.min.js', array( 'footable' ), '2.0.1.2' );
	wp_register_script( 'footable-paginate', APP_FRAMEWORK_URI . '/js/footable/jquery.footable.paginate.min.js', array( 'footable' ), '2.0.1.2' );

	_appthemes_localize_scripts();
}
add_action( 'wp_enqueue_scripts', '_appthemes_register_scripts' );
add_action( 'admin_enqueue_scripts', '_appthemes_register_scripts' );


