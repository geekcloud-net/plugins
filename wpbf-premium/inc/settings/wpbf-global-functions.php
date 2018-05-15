<?php
/**
 * Theme Settings Functions
 *
 * @package Page Builder Framework
 * @subpackage Settings
 */
 
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/* Responsive Breakpoints */

add_action( 'wp_enqueue_scripts', 'wpbf_dequeue_responsive_css', 100 );

function wpbf_dequeue_responsive_css() {

	// vars
	$wpbf_settings = get_option( 'wpbf_settings' );
	$breakpoint_medium = !empty( $wpbf_settings['wpbf_breakpoint_medium'] ) ? $wpbf_settings['wpbf_breakpoint_medium'] : false;
	$breakpoint_desktop = !empty( $wpbf_settings['wpbf_breakpoint_desktop'] ) ? $wpbf_settings['wpbf_breakpoint_desktop'] : false;

	if ( $breakpoint_medium || $breakpoint_desktop ) {

		wp_dequeue_style( 'wpbf-responsive' );
		wp_deregister_style( 'wpbf-responsive' );

		// WooCommerce
		if ( class_exists( 'WooCommerce' ) ) {

			wp_dequeue_style( 'wpbf-woocommerce-smallscreen' );
			wp_deregister_style( 'wpbf-woocommerce-smallscreen' );

		}

	}

}

/* White Label */
add_filter( 'wp_prepare_themes_for_js', 'wpbf_white_label' );
function wpbf_white_label( $themes ) {

	// vars
	$wpbf_settings = get_option( 'wpbf_settings' );

	$theme_data = array(
		'name' 				=> isset( $wpbf_settings['wpbf_theme_name'] ) ? $wpbf_settings['wpbf_theme_name'] : '',
		'description' 		=> isset( $wpbf_settings['wpbf_theme_description'] ) ? $wpbf_settings['wpbf_theme_description'] : '',
		'tags'				=> isset( $wpbf_settings['wpbf_theme_tags'] ) ? $wpbf_settings['wpbf_theme_tags'] : '',
		'company_name' 		=> isset( $wpbf_settings['wpbf_theme_company_name'] ) ? $wpbf_settings['wpbf_theme_company_name'] : '',
		'company_url' 		=> isset( $wpbf_settings['wpbf_theme_company_url'] ) ? $wpbf_settings['wpbf_theme_company_url'] : '',
		'screenshot_url' 	=> isset( $wpbf_settings['wpbf_theme_screenshot'] ) ? $wpbf_settings['wpbf_theme_screenshot'] : '',
	);
		
	if ( !empty( $theme_data['name'] ) ) {
		
		$themes['page-builder-framework']['name'] = $theme_data['name'];

		foreach ( $themes as $theme_key => $theme ) {
			if ( isset( $theme['parent'] ) && 'Page Builder Framework' == $theme['parent'] ) {
				$themes[ $theme_key ]['parent'] = $theme_data['name'];
			}
		}
		
	}

	if ( !empty( $theme_data['description'] ) ) {
		$themes['page-builder-framework']['description'] = $theme_data['description'];
	}

	if ( !empty( $theme_data['tags'] ) ) {
		$themes['page-builder-framework']['tags'] = $theme_data['tags'];
	}

	if ( !empty( $theme_data['company_name'] ) ) {
		$company_url = empty( $theme_data['company_url'] ) ? '#' : $theme_data['company_url'];
		$themes['page-builder-framework']['author'] = $theme_data['company_name'];
		$themes['page-builder-framework']['authorAndUri'] = '<a href="' . $company_url . '">' . $theme_data['company_name'] . '</a>';
	}

	if ( !empty( $theme_data['screenshot_url'] ) ) {
		$themes['page-builder-framework']['screenshot'] = array( $theme_data['screenshot_url'] );
	}
	
	return $themes;

}

/* Performance Settings */

// vars
$wpbf_settings = get_option( 'wpbf_settings' );

// helpers
function wpbf_disable_emojis_tinymce( $plugins ) {
	if( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	} else {
		return array();
	}
}

// embed tiny mce
function wpbf_disable_embeds_tiny_mce_plugins( $plugins ) {

	return array_diff( $plugins, array( 'wpembed' ) );

}

// embed rewrite rules
function wpbf_disable_embeds_rewrite_rules( $rules ) {
	foreach( $rules as $rule => $rewrite ) {
		if( false !== strpos( $rewrite, 'embed=true' ) ) {
			unset( $rules[$rule] );
		}
	}
	return $rules;
}

// rss feed
function wpbf_disable_rss_feed() {

	wp_die( __( 'No feed available, please visit the <a href="'. esc_url( home_url( '/' ) ) .'">homepage</a>!' ) );

}

if( isset( $wpbf_settings['wpbf_clean_head'] ) ) {

	$wpbf_performance = $wpbf_settings['wpbf_clean_head'];

	// remove feed links
	if ( in_array( 'remove_feed', $wpbf_performance ) ) {

		remove_action( 'wp_head', 'feed_links', 2 );
		remove_action( 'wp_head', 'feed_links_extra', 3 );

	}

	// remove rsd link
	if ( in_array( 'remove_rsd', $wpbf_performance ) ) {

		// remove rsd link
		remove_action( 'wp_head', 'rsd_link' );

	}

	// remove wlwmanifest
	if ( in_array( 'remove_wlwmanifest', $wpbf_performance ) ) {

		remove_action( 'wp_head', 'wlwmanifest_link' );

	}

	// remove wp generator tag
	if ( in_array( 'remove_generator', $wpbf_performance ) ) {

		remove_action( 'wp_head', 'wp_generator' );

	}

	// remove shortlink
	if ( in_array( 'remove_shortlink', $wpbf_performance ) ) {

		remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
		remove_action( 'template_redirect', 'wp_shortlink_header', 11, 0 );

	}

	// disable rss
	if ( in_array( 'disable_rss_feed', $wpbf_performance ) ) {

		add_action( 'do_feed', 'wpbf_disable_rss_feed', 1 );
		add_action( 'do_feed_rdf', 'wpbf_disable_rss_feed', 1 );
		add_action( 'do_feed_rss', 'wpbf_disable_rss_feed', 1 );
		add_action( 'do_feed_rss2', 'wpbf_disable_rss_feed', 1 );
		add_action( 'do_feed_atom', 'wpbf_disable_rss_feed', 1 );
		add_action( 'do_feed_rss2_comments', 'wpbf_disable_rss_feed', 1 );
		add_action( 'do_feed_atom_comments', 'wpbf_disable_rss_feed', 1 );

	}

}

// disable emojis
function wpbf_disable_emojis() {

	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'tiny_mce_plugins', 'wpbf_disable_emojis_tinymce' );
	add_filter( 'emoji_svg_url', '__return_false' );

}

if( isset( $wpbf_settings['wpbf_clean_head'] ) ) {

	if ( in_array( 'disable_emojis', $wpbf_performance ) ) {

		add_action( 'init', 'wpbf_disable_emojis' );

	}

}

// disable embeds
function wpbf_disable_embeds() {

	global $wp;
	$wp->public_query_vars = array_diff( $wp->public_query_vars, array( 'embed' ) );

	remove_action( 'rest_api_init', 'wp_oembed_register_route' );
	add_filter( 'embed_oembed_discover', '__return_false' );
	remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );

	add_filter( 'tiny_mce_plugins', 'wpbf_disable_embeds_tiny_mce_plugins' );
	add_filter( 'rewrite_rules_array', 'wpbf_disable_embeds_rewrite_rules' );

}

if( isset( $wpbf_settings['wpbf_clean_head'] ) ) {

	if ( in_array( 'disable_embeds', $wpbf_performance ) ) {

		add_action( 'init', 'wpbf_disable_embeds', 9999 );

	}

}

// remove jQuery migrate
function wpbf_remove_jquery_migrate( &$scripts ) {

    if( !is_admin() ) {
        $scripts->remove( 'jquery' );
        $scripts->add( 'jquery', false, array( 'jquery-core' ), '1.12.4' );
    }

}

if( isset( $wpbf_settings['wpbf_clean_head'] ) ) {

	if ( in_array( 'remove_jquery_migrate', $wpbf_performance ) ) {

		add_filter( 'wp_default_scripts', 'wpbf_remove_jquery_migrate' );

	}

}