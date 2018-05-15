<?php
/**
 * Body Classes
 *
 * @package Page Builder Framework Premium Addon
 */
 
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function wpbf_premium_body_classes( $classes ) {

	$push_menu = get_theme_mod( 'menu_off_canvas_push' );
	$menu_position = get_theme_mod( 'menu_position' );

	if( $push_menu == true && $menu_position == 'menu-off-canvas' ) {
		$classes[] = 'wpbf-push-menu-right';
	} elseif ( $push_menu == true && $menu_position == 'menu-off-canvas-left' ) {
		$classes[] = 'wpbf-push-menu-left';
	}

	$wpbf_settings = get_option( 'wpbf_settings' );
	$breakpoint_medium = !empty( $wpbf_settings['wpbf_breakpoint_medium'] ) ? $wpbf_settings['wpbf_breakpoint_medium'] : false;
	$breakpoint_desktop = !empty( $wpbf_settings['wpbf_breakpoint_desktop'] ) ? $wpbf_settings['wpbf_breakpoint_desktop'] : false;

	if( $breakpoint_medium || $breakpoint_desktop ) {

		$classes[] = 'wpbf-responsive-breakpoints';

		if( $breakpoint_medium ) $classes[] = 'wpbf-medium-breakpoint-' . (int) $breakpoint_medium;
		if( $breakpoint_desktop ) $classes[] = 'wpbf-desktop-breakpoint-' . (int) $breakpoint_desktop;

	}

	return $classes;

}
add_filter( 'body_class', 'wpbf_premium_body_classes' );