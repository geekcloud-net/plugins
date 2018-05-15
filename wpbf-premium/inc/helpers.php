<?php
/**
 * Helpers
 *
 * Collection of helper functions
 *
 * @package Page Builder Framework
 */
 
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/* Shortcodes */

// Credit
function wpbf_footer_credit( $atts ) {

    extract( shortcode_atts( array(
        'url' => 'https://wp-pagebuilderframework.com/',
        'name' => 'Page Builder Framework',
    ), $atts ) );

	$credit = '';
	$credit .= '<a href="'. esc_url( $url ) .'">'. esc_html( $name ) .'</a>';
	return $credit;

}
add_shortcode( 'credit', 'wpbf_footer_credit' );

// Social
function wpbf_social() {

	$active_networks = get_theme_mod( 'social_sortable', array() );
	$social_shape = get_theme_mod( 'social_shapes' );
	$social_style = get_theme_mod( 'social_styles' );
	$social_size = get_theme_mod( 'social_sizes' );

	ob_start();

	if ( ! empty( $active_networks ) && is_array( $active_networks ) ) : ?>
	<div class="wpbf-social-icons<?php echo esc_attr( $social_shape . $social_style . $social_size ); ?>">
		<?php foreach ( $active_networks as $social_network_label ) : ?>
			<a class="wpbf-social-icon wpbf-social-<?php echo esc_attr( $social_network_label ); ?>" target="_blank" href="<?php echo esc_url( get_theme_mod( $social_network_label . '_link', '' ) ); ?>">
				<i class="fab fa-<?php echo esc_attr( $social_network_label ); ?>" aria-hidden="true"></i>
			</a>
		<?php endforeach; ?>
	</div>
	<?php endif;

	return ob_get_clean();

}

add_shortcode( 'social', 'wpbf_social' );

// Youtube & Vimeo Video's
function wpbf_responsive_video( $atts ) {

	extract( shortcode_atts( array(
		'src' => 'https://www.youtube.com/embed/GH28y-XjHdo',
	), $atts ) );

	$yt_video = '';
	$yt_video .= '<div class="wpbf-video">';
	$yt_video .= '<iframe width="1600" height="900" src="'. esc_url( $src ) .'" frameborder="0" webkitAllowFullScreen
mozallowfullscreen allowFullScreen></iframe>';
	$yt_video .= '</div>';
	return $yt_video;

}
add_shortcode( 'wpbf-responsive-video', 'wpbf_responsive_video' );

function wpbf_sticky_navigation() {

	// vars
	$menu_sticky = get_theme_mod( 'menu_sticky' );
	$menu_active_delay = get_theme_mod( 'menu_active_delay' );
	$menu_active_animation = get_theme_mod( 'menu_active_animation' );
	$menu_active_animation_duration = get_theme_mod( 'menu_active_animation_duration' );

	if ( $menu_sticky ) {

		$sticky_navigation = "";
		$sticky_navigation .= 'data-sticky="true"';
		$sticky_navigation .= $menu_active_delay ? ' data-sticky-delay="' . esc_attr( $menu_active_delay ) . '"' : ' data-sticky-delay="300px"';
		$sticky_navigation .= $menu_active_animation ? ' data-sticky-animation="' . esc_attr( $menu_active_animation ) . '"' : false;
		$sticky_navigation .= $menu_active_animation_duration ? ' data-sticky-animation-duration="' . esc_attr( $menu_active_animation_duration ) . '"' : ' data-sticky-animation-duration="200"';

		echo $sticky_navigation;

	}


}

// Transparent Header
function wpbf_transparent_header() {

	$classes = get_body_class();
	if ( in_array( 'wpbf-transparent-header', $classes ) ) {

		$transparent_nav_class = " wpbf-navigation-transparent";
		echo esc_attr( $transparent_nav_class );

	}

}

// Transparent Header Body Class
function wpbf_transparent_header_body_class_test( $classes ) {

	// don't take it further if we're on archives
	if( !is_singular() ) return $classes;

	// get options
	$options = get_post_meta( get_the_ID(), 'wpbf_premium_options', true );

	// checking if transparent header is checked (returns true if so)
	$transparent_header = $options ? in_array( 'transparent-header', $options ) : false;

	if( $transparent_header ) {
		$classes[] = 'wpbf-transparent-header';
	}

	return $classes;
}
add_filter( 'body_class', 'wpbf_transparent_header_body_class_test' );

// Transparent Header Logo
function wpbf_transparent_header_logo( $custom_logo_url ) {

	// get body classes
	$classes = get_body_class();

	// check if header is set to transparent on our template
	if( in_array( 'wpbf-transparent-header', $classes ) && get_theme_mod( 'menu_transparent_logo' ) ) {
		$custom_logo_url = get_theme_mod( 'menu_transparent_logo' );
	}

	return $custom_logo_url;

}
add_filter( 'wpbf_logo', 'wpbf_transparent_header_logo' );
add_filter( 'wpbf_logo_mobile', 'wpbf_transparent_header_logo' );