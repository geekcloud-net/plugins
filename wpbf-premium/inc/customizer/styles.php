<?php
/**
 * Styles
 *
 * Holds Customizer CSS styles
 *
 * @package Page Builder Framework Premium Addon
 * @subpackage Customizer
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wpbf_before_customizer_css', 'wpbf_premium_before_customizer_css', 10 );
function wpbf_premium_before_customizer_css() {

	// Vars
	$wpbf_settings = get_option( 'wpbf_settings' );
	$breakpoint_medium = !empty( $wpbf_settings['wpbf_breakpoint_medium'] ) ? $wpbf_settings['wpbf_breakpoint_medium'] : '768px';
	$breakpoint_desktop = !empty( $wpbf_settings['wpbf_breakpoint_desktop'] ) ? $wpbf_settings['wpbf_breakpoint_desktop'] : '1024px';
	$important = is_plugin_active( 'elementor/elementor.php' ) ? '!important' : false;

	// Page Font Settings
	if( get_theme_mod( 'page_font_color' ) ) { ?>
	body {
		color: <?php echo esc_attr( get_theme_mod( 'page_font_color' ) ); ?>;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'page_line_height' ) ) { ?>
	input,
	optgroup,
	select,
	textarea,
	body {
		line-height: <?php echo esc_attr( get_theme_mod( 'page_line_height' ) ); ?>;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'page_bold_color' ) ) { ?>
	b,
	strong {
		color: <?php echo esc_attr( get_theme_mod( 'page_bold_color' ) ); ?>;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'page_font_size_desktop' ) ) { ?>
	body {
		font-size: <?php echo esc_attr( get_theme_mod( 'page_font_size_desktop' ) ); ?>;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'page_font_size_tablet' ) ) { ?>
	@media screen and (max-width:<?php echo esc_attr( $breakpoint_medium ); ?>) {
		body {
			font-size: <?php echo esc_attr( get_theme_mod( 'page_font_size_tablet' ) ); ?>;
		}
	}
	<?php } ?>

	<?php if( get_theme_mod( 'page_font_size_mobile' ) ) { ?>
	@media screen and (max-width:480px) {
		body {
			font-size: <?php echo esc_attr( get_theme_mod( 'page_font_size_mobile' ) ); ?>;
		}
	}
	<?php } ?>

	<?php // Menu Font Settings ?>

	<?php if( get_theme_mod( 'menu_letter_spacing' ) || get_theme_mod( 'menu_font_size' ) || get_theme_mod( 'menu_text_transform' ) ) { ?>

		.wpbf-menu, .wpbf-mobile-menu {

		<?php if( get_theme_mod( 'menu_font_size' ) ){ ?>
			font-size: <?php echo esc_attr( get_theme_mod( 'menu_font_size' ) ); ?>;
		<?php } ?>

		<?php if( get_theme_mod( 'menu_letter_spacing' ) ) { ?>
			letter-spacing: <?php echo esc_attr( get_theme_mod( 'menu_letter_spacing' ) ); ?>px;
		<?php } ?>

		<?php if( get_theme_mod( 'menu_text_transform' ) == 'uppercase' ) { ?>
			text-transform: <?php echo esc_attr( get_theme_mod( 'menu_text_transform' ) ); ?>;
		<?php } else { ?>
			text-transform: none;
		<?php } ?>

		}

	<?php } ?>

	<?php // H1 Font Settings ?>

	<?php if( get_theme_mod( 'page_h1_font_color' ) || get_theme_mod( 'page_h1_line_height' ) || get_theme_mod( 'page_h1_letter_spacing' ) || get_theme_mod( 'page_h1_text_transform' ) ) { ?>

		h1,
		h2,
		h3,
		h4,
		h5,
		h6 {

		<?php if( get_theme_mod( 'page_h1_font_color' ) ) { ?>
			color: <?php echo esc_attr( get_theme_mod( 'page_h1_font_color' ) ); ?>;
		<?php } ?>

		<?php if( get_theme_mod( 'page_h1_line_height' ) ) { ?>
			line-height: <?php echo esc_attr( get_theme_mod( 'page_h1_line_height' ) ), $important; ?>;
		<?php } ?>

		<?php if( get_theme_mod( 'page_h1_letter_spacing' ) ) { ?>
			letter-spacing: <?php echo esc_attr( get_theme_mod( 'page_h1_letter_spacing' ) ); ?>px;
		<?php } ?>

		<?php if( get_theme_mod( 'page_h1_text_transform' ) == 'uppercase' ) { ?>
			text-transform: <?php echo esc_attr( get_theme_mod( 'page_h1_text_transform' ) ); ?>;
		<?php } else { ?>
			text-transform: none;
		<?php } ?>

		}

	<?php } ?>

	<?php if( get_theme_mod( 'page_h1_font_size_desktop' ) ) { ?>
	h1 {
		font-size: <?php echo esc_attr( get_theme_mod( 'page_h1_font_size_desktop' ) ); ?>;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'page_h1_font_size_tablet' ) ) { ?>
	@media screen and (max-width:<?php echo esc_attr( $breakpoint_medium ); ?>) {
		h1 {
			font-size: <?php echo esc_attr( get_theme_mod( 'page_h1_font_size_tablet' ) ); ?>;
		}
	}
	<?php } ?>

	<?php if( get_theme_mod( 'page_h1_font_size_mobile' ) ) { ?>
	@media screen and (max-width:480px) {
		h1 {
			font-size: <?php echo esc_attr( get_theme_mod( 'page_h1_font_size_mobile' ) ); ?>;
		}
	}
	<?php } ?>

	<?php // H2 Font Settings ?>

	<?php

	$page_h2_toggle = get_theme_mod( 'page_h2_toggle' );

	if( $page_h2_toggle ) {

		if( get_theme_mod( 'page_h2_line_height' ) || get_theme_mod( 'page_h2_letter_spacing' ) || get_theme_mod( 'page_h2_text_transform' ) ) { ?>

			h2 {

			<?php if( get_theme_mod( 'page_h2_line_height' ) ) { ?>
				line-height: <?php echo esc_attr( get_theme_mod( 'page_h2_line_height' ) ), $important; ?>;
			<?php } ?>

			<?php if( get_theme_mod( 'page_h2_letter_spacing' ) ) { ?>
				letter-spacing: <?php echo esc_attr( get_theme_mod( 'page_h2_letter_spacing' ) ); ?>px;
			<?php } ?>

			<?php if( get_theme_mod( 'page_h2_text_transform' ) == 'uppercase' ) { ?>
				text-transform: <?php echo esc_attr( get_theme_mod( 'page_h2_text_transform' ) ); ?>;
			<?php } else { ?>
				text-transform: none;
			<?php } ?>

			}

		<?php } ?>

	<?php } ?>

	<?php if( get_theme_mod( 'page_h2_font_color' ) ) { ?>

		h2 {
			color: <?php echo esc_attr( get_theme_mod( 'page_h2_font_color' ) ); ?>;
		}

	<?php } ?>

	<?php if( get_theme_mod( 'page_h2_font_size_desktop' ) ) { ?>
	h2 {
		font-size: <?php echo esc_attr( get_theme_mod( 'page_h2_font_size_desktop' ) ); ?>;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'page_h2_font_size_tablet' ) ) { ?>
	@media screen and (max-width:<?php echo esc_attr( $breakpoint_medium ); ?>) {
		h2 {
			font-size: <?php echo esc_attr( get_theme_mod( 'page_h2_font_size_tablet' ) ); ?>;
		}
	}
	<?php } ?>

	<?php if( get_theme_mod( 'page_h2_font_size_mobile' ) ) { ?>
	@media screen and (max-width:480px) {
		h2 {
			font-size: <?php echo esc_attr( get_theme_mod( 'page_h2_font_size_mobile' ) ); ?>;
		}
	}
	<?php } ?>

	<?php // H3 Font Settings ?>

	<?php

	$page_h3_toggle = get_theme_mod( 'page_h3_toggle' );

	if ( $page_h3_toggle ) {

		if( get_theme_mod( 'page_h3_line_height' ) || get_theme_mod( 'page_h3_letter_spacing' ) || get_theme_mod( 'page_h3_text_transform' ) ) { ?>

			h3 {

			<?php if( get_theme_mod( 'page_h3_line_height' ) ) { ?>
				line-height: <?php echo esc_attr( get_theme_mod( 'page_h3_line_height' ) ), $important; ?>;
			<?php } ?>

			<?php if( get_theme_mod( 'page_h3_letter_spacing' ) ) { ?>
				letter-spacing: <?php echo esc_attr( get_theme_mod( 'page_h3_letter_spacing' ) ); ?>px;
			<?php } ?>

			<?php if( get_theme_mod( 'page_h3_text_transform' ) == 'uppercase' ) { ?>
				text-transform: <?php echo esc_attr( get_theme_mod( 'page_h3_text_transform' ) ); ?>;
			<?php } else { ?>
				text-transform: none;
			<?php } ?>

			}

		<?php } ?>

	<?php } ?>

	<?php if( get_theme_mod( 'page_h3_font_color' ) ) { ?>

		h3 {
			color: <?php echo esc_attr( get_theme_mod( 'page_h3_font_color' ) ); ?>;
		}

	<?php } ?>

	<?php if( get_theme_mod( 'page_h3_font_size_desktop' ) ) { ?>
	h3 {
		font-size: <?php echo esc_attr( get_theme_mod( 'page_h3_font_size_desktop' ) ); ?>;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'page_h3_font_size_tablet' ) ) { ?>
	@media screen and (max-width:<?php echo esc_attr( $breakpoint_medium ); ?>) {
		h3 {
			font-size: <?php echo esc_attr( get_theme_mod( 'page_h3_font_size_tablet' ) ); ?>;
		}
	}
	<?php } ?>

	<?php if( get_theme_mod( 'page_h3_font_size_mobile' ) ) { ?>
	@media screen and (max-width:480px) {
		h3 {
			font-size: <?php echo esc_attr( get_theme_mod( 'page_h3_font_size_mobile' ) ); ?>;
		}
	}
	<?php } ?>

	<?php // H4 Font Settings ?>

	<?php

	$page_h4_toggle = get_theme_mod( 'page_h4_toggle' );

	if ( $page_h4_toggle ) {

		if( get_theme_mod( 'page_h4_line_height' ) || get_theme_mod( 'page_h4_letter_spacing' ) || get_theme_mod( 'page_h4_text_transform' ) ) { ?>

			h4 {

			<?php if( get_theme_mod( 'page_h4_line_height' ) ) { ?>
				line-height: <?php echo esc_attr( get_theme_mod( 'page_h4_line_height' ) ), $important; ?>;
			<?php } ?>

			<?php if( get_theme_mod( 'page_h4_letter_spacing' ) ) { ?>
				letter-spacing: <?php echo esc_attr( get_theme_mod( 'page_h4_letter_spacing' ) ); ?>px;
			<?php } ?>

			<?php if( get_theme_mod( 'page_h4_text_transform' ) == 'uppercase' ) { ?>
				text-transform: <?php echo esc_attr( get_theme_mod( 'page_h4_text_transform' ) ); ?>;
			<?php } else { ?>
				text-transform: none;
			<?php } ?>

			}

		<?php } ?>

	<?php } ?>

	<?php if( get_theme_mod( 'page_h4_font_color' ) ) { ?>

		h4 {
			color: <?php echo esc_attr( get_theme_mod( 'page_h4_font_color' ) ); ?>;
		}

	<?php } ?>

	<?php if( get_theme_mod( 'page_h4_font_size_desktop' ) ) { ?>
	h4 {
		font-size: <?php echo esc_attr( get_theme_mod( 'page_h4_font_size_desktop' ) ); ?>;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'page_h4_font_size_tablet' ) ) { ?>
	@media screen and (max-width:<?php echo esc_attr( $breakpoint_medium ); ?>) {
		h4 {
			font-size: <?php echo esc_attr( get_theme_mod( 'page_h4_font_size_tablet' ) ); ?>;
		}
	}
	<?php } ?>

	<?php if( get_theme_mod( 'page_h4_font_size_mobile' ) ) { ?>
	@media screen and (max-width:480px) {
		h4 {
			font-size: <?php echo esc_attr( get_theme_mod( 'page_h4_font_size_mobile' ) ); ?>;
		}
	}
	<?php } ?>

	<?php // H5 Font Settings ?>

	<?php

	$page_h5_toggle = get_theme_mod( 'page_h5_toggle' );

	if ( $page_h5_toggle ) {

		if( get_theme_mod( 'page_h5_line_height' ) || get_theme_mod( 'page_h5_letter_spacing' ) || get_theme_mod( 'page_h5_text_transform' ) ) { ?>

			h5 {

			<?php if( get_theme_mod( 'page_h5_line_height' ) ) { ?>
				line-height: <?php echo esc_attr( get_theme_mod( 'page_h5_line_height' ) ), $important; ?>;
			<?php } ?>

			<?php if( get_theme_mod( 'page_h5_letter_spacing' ) ) { ?>
				letter-spacing: <?php echo esc_attr( get_theme_mod( 'page_h5_letter_spacing' ) ); ?>px;
			<?php } ?>

			<?php if( get_theme_mod( 'page_h5_text_transform' ) == 'uppercase' ) { ?>
				text-transform: <?php echo esc_attr( get_theme_mod( 'page_h5_text_transform' ) ); ?>;
			<?php } else { ?>
				text-transform: none;
			<?php } ?>

			}

		<?php } ?>

	<?php } ?>

	<?php if( get_theme_mod( 'page_h5_font_color' ) ) { ?>
		h5 {
			color: <?php echo esc_attr( get_theme_mod( 'page_h5_font_color' ) ); ?>;
		}
	<?php } ?>

	<?php if( get_theme_mod( 'page_h5_font_size_desktop' ) ) { ?>
	h5 {
		font-size: <?php echo esc_attr( get_theme_mod( 'page_h5_font_size_desktop' ) ); ?>;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'page_h5_font_size_tablet' ) ) { ?>
	@media screen and (max-width:<?php echo esc_attr( $breakpoint_medium ); ?>) {
		h5 {
			font-size: <?php echo esc_attr( get_theme_mod( 'page_h5_font_size_tablet' ) ); ?>;
		}
	}
	<?php } ?>

	<?php if( get_theme_mod( 'page_h5_font_size_mobile' ) ) { ?>
	@media screen and (max-width:480px) {
		h5 {
			font-size: <?php echo esc_attr( get_theme_mod( 'page_h5_font_size_mobile' ) ); ?>;
		}
	}
	<?php } ?>

	<?php // H6 Font Settings ?>

	<?php

	$page_h6_toggle = get_theme_mod( 'page_h6_toggle' );

	if ( $page_h6_toggle ) {

		if( get_theme_mod( 'page_h6_line_height' ) || get_theme_mod( 'page_h6_letter_spacing' ) || get_theme_mod( 'page_h6_text_transform' ) ) { ?>

			h6 {

			<?php if( get_theme_mod( 'page_h6_line_height' ) ) { ?>
				line-height: <?php echo esc_attr( get_theme_mod( 'page_h6_line_height' ) ), $important; ?>;
			<?php } ?>

			<?php if( get_theme_mod( 'page_h6_letter_spacing' ) ) { ?>
				letter-spacing: <?php echo esc_attr( get_theme_mod( 'page_h6_letter_spacing' ) ); ?>px;
			<?php } ?>

			<?php if( get_theme_mod( 'page_h6_text_transform' ) == 'uppercase' ) { ?>
				text-transform: <?php echo esc_attr( get_theme_mod( 'page_h6_text_transform' ) ); ?>;
			<?php } else { ?>
				text-transform: none;
			<?php } ?>

			}

		<?php } ?>

	<?php } ?>

	<?php if( get_theme_mod( 'page_h6_font_color' ) ) { ?>

		h6 {
			color: <?php echo esc_attr( get_theme_mod( 'page_h6_font_color' ) ); ?>;
		}

	<?php } ?>

	<?php if( get_theme_mod( 'page_h6_font_size_desktop' ) ) { ?>
	h6 {
		font-size: <?php echo esc_attr( get_theme_mod( 'page_h6_font_size_desktop' ) ); ?>;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'page_h6_font_size_tablet' ) ) { ?>
	@media screen and (max-width:<?php echo esc_attr( $breakpoint_medium ); ?>) {
		h6 {
			font-size: <?php echo esc_attr( get_theme_mod( 'page_h6_font_size_tablet' ) ); ?>;
		}
	}
	<?php } ?>

	<?php if( get_theme_mod( 'page_h6_font_size_mobile' ) ) { ?>
	@media screen and (max-width:480px) {
		h6 {
			font-size: <?php echo esc_attr( get_theme_mod( 'page_h6_font_size_mobile' ) ); ?>;
		}
	}
	<?php } ?>

<?php }

add_action( 'wpbf_after_customizer_css', 'wpbf_premium_after_customizer_css', 10 );
function wpbf_premium_after_customizer_css() {

	// Mobile Navigation
	if( get_theme_mod( 'mobile_menu_options' ) == 'menu-mobile-off-canvas' ) { ?>
	.wpbf-mobile-menu-off-canvas .wpbf-mobile-menu-container {
		width: <?php echo esc_attr( get_theme_mod( 'mobile_menu_width' ) ); ?>;
		right: -<?php echo esc_attr( get_theme_mod( 'mobile_menu_width' ) ); ?>;
		<?php if( get_theme_mod( 'mobile_menu_bg_color' ) ) { ?>
		background-color: <?php echo esc_attr( get_theme_mod( 'mobile_menu_bg_color' ) ) ?>;
		<?php } ?>
	}

	<?php } ?>

	<?php // Stacked Advanced ?>

	<?php if( get_theme_mod( 'menu_position' ) == 'menu-stacked-advanced' ) { ?>

		<?php if( get_theme_mod( 'menu_width' ) ) { ?>

		.wpbf-menu-stacked-advanced-wrapper .wpbf-container {

			<?php if( get_theme_mod( 'menu_width' ) ) { ?>
				max-width: <?php echo esc_attr( get_theme_mod( 'menu_width' ) ); ?>;
			<?php } ?>

		}

		<?php } ?>

		<?php if( get_theme_mod( 'menu_stacked_bg_color' ) ) { ?>
		.wpbf-menu-stacked-advanced-wrapper {
			background-color: <?php echo esc_attr( get_theme_mod( 'menu_stacked_bg_color' ) ); ?>;
		}
		<?php } ?>

		<?php if( get_theme_mod( 'menu_stacked_logo_height' ) ) { ?>
		.wpbf-menu-stacked-advanced-wrapper {
			padding-top: <?php echo esc_attr( get_theme_mod( 'menu_stacked_logo_height' ) ); ?>px;
			padding-bottom: <?php echo esc_attr( get_theme_mod( 'menu_stacked_logo_height' ) ); ?>px;
		}
		<?php } ?>

	<?php } ?>

	<?php // Off Canvas & Full Screen ?>

	<?php if( get_theme_mod( 'menu_padding' ) ) { ?>
	.wpbf-menu > .menu-item > a {
		<?php if( get_theme_mod( 'menu_position' ) == 'menu-off-canvas' || get_theme_mod( 'menu_position' ) == 'menu-off-canvas-left' ) { ?>
			padding-left: 0px;
			padding-right: 0px;
		<?php } ?>
	}
	<?php } ?>

	<?php if( get_theme_mod( 'menu_off_canvas_bg_color' ) && get_theme_mod( 'menu_position' ) == 'menu-off-canvas' || get_theme_mod( 'menu_position' ) == 'menu-off-canvas-left' || get_theme_mod( 'menu_position' ) == 'menu-full-screen' ) { ?>
	.wpbf-menu-off-canvas,
	.wpbf-menu-full-screen {
		background-color: <?php echo esc_attr( get_theme_mod( 'menu_off_canvas_bg_color' ) ); ?>;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'menu_off_canvas_hamburger_color' ) && get_theme_mod( 'menu_position' ) == 'menu-off-canvas' || get_theme_mod( 'menu_position' ) == 'menu-off-canvas-left' || get_theme_mod( 'menu_position' ) == 'menu-full-screen' ) { ?>
	.wpbf-menu-toggle {
		color: <?php echo esc_attr( get_theme_mod( 'menu_off_canvas_hamburger_color' ) ); ?>;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'menu_off_canvas_submenu_arrow_color' ) && get_theme_mod( 'menu_position' ) == 'menu-off-canvas' || get_theme_mod( 'menu_position' ) == 'menu-off-canvas-left' ) { ?>
	.wpbf-menu-off-canvas .wpbf-submenu-toggle {
		color: <?php echo esc_attr( get_theme_mod( 'menu_off_canvas_submenu_arrow_color' ) ); ?>;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'menu_off_canvas_width' ) && get_theme_mod( 'menu_position' ) == 'menu-off-canvas' ) { ?>
	.wpbf-menu-off-canvas {
		width: <?php echo esc_attr( get_theme_mod( 'menu_off_canvas_width' ) ); ?>px;
		right: -<?php echo esc_attr( get_theme_mod( 'menu_off_canvas_width' ) ); ?>px;
	}
	.wpbf-push-menu-right.active {
		left: -<?php echo esc_attr( get_theme_mod( 'menu_off_canvas_width' ) ); ?>px;
	}
	.wpbf-push-menu-right.active .wpbf-navigation-active {
		left: -<?php echo esc_attr( get_theme_mod( 'menu_off_canvas_width' ) ); ?>px !important;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'menu_off_canvas_width' ) && get_theme_mod( 'menu_position' ) == 'menu-off-canvas-left' ) { ?>
	.wpbf-menu-off-canvas {
		width: <?php echo esc_attr( get_theme_mod( 'menu_off_canvas_width' ) ); ?>px;
		left: -<?php echo esc_attr( get_theme_mod( 'menu_off_canvas_width' ) ); ?>px;
	}
	.wpbf-push-menu-left.active {
		left: <?php echo esc_attr( get_theme_mod( 'menu_off_canvas_width' ) ); ?>px;
	}
	.wpbf-push-menu-left.active .wpbf-navigation-active {
		left: <?php echo esc_attr( get_theme_mod( 'menu_off_canvas_width' ) ); ?>px !important;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'menu_padding' ) && get_theme_mod( 'menu_position' ) == 'menu-full-screen' ) { ?>
	.wpbf-menu > .menu-item > a {
		padding-top: <?php echo esc_attr( get_theme_mod( 'menu_padding' ) ); ?>px;
		padding-bottom: <?php echo esc_attr( get_theme_mod( 'menu_padding' ) ); ?>px;
	}
	<?php } ?>

	<?php // Transparent Header ?>

	<?php if( get_theme_mod( 'menu_transparent_background_color' ) ) { ?>
	.wpbf-transparent-header .wpbf-navigation,
	.wpbf-transparent-header .wpbf-mobile-nav-wrapper {
		background-color: <?php echo esc_attr( get_theme_mod( 'menu_transparent_background_color' ) ); ?>;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'menu_transparent_font_color' ) ) { ?>
	.wpbf-navigation-transparent .wpbf-menu > .menu-item > a {
		color: <?php echo esc_attr( get_theme_mod( 'menu_transparent_font_color' ) ); ?>;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'menu_transparent_font_color_alt' ) ) { ?>
	.wpbf-navigation-transparent .wpbf-menu > .menu-item > a:hover {
		color: <?php echo esc_attr( get_theme_mod( 'menu_transparent_font_color_alt' ) ); ?>;
	}
	.wpbf-navigation-transparent .wpbf-menu > .current-menu-item > a {
		color: <?php echo esc_attr( get_theme_mod( 'menu_transparent_font_color_alt' ) ); ?> !important;
	}
	<?php } ?>

	<?php // Sticky Navigation ?>

	<?php

	$sticky = get_theme_mod( 'menu_sticky' );
	$hide_logo = get_theme_mod( 'menu_active_hide_logo' );
	$stacked = get_theme_mod( 'menu_position' )  == "menu-stacked";
	$stacked_advanced = get_theme_mod( 'menu_position' )  == "menu-stacked-advanced";
	$centered = get_theme_mod( 'menu_position' )  == "menu-centered";

	if ( $sticky && $hide_logo && $stacked ) { ?>
		.wpbf-navigation-active .wpbf-logo {
			display: none;
		}
		.wpbf-navigation-active nav {
			margin-top: 0 !important;
		}

	<?php } elseif ( $sticky && $hide_logo && $stacked_advanced ) { ?>
		.wpbf-navigation-active .wpbf-menu-stacked-advanced-wrapper {
			display: none;
		}
	<?php } elseif ( $sticky && $hide_logo && $centered ) { ?>
		.wpbf-navigation-active .logo-container {
			display: none !important;
		}
	<?php } ?>

	<?php if( get_theme_mod( 'menu_active_logo_size' ) ) { ?>
	.wpbf-navigation-active .wpbf-logo img {
		height: <?php echo esc_attr( get_theme_mod( 'menu_active_logo_size' ) ); ?>px;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'menu_active_height' ) ) { ?>
	.wpbf-navigation-active .wpbf-nav-wrapper {
		padding-top: <?php echo esc_attr( get_theme_mod( 'menu_active_height' ) ); ?>px;
		padding-bottom: <?php echo esc_attr( get_theme_mod( 'menu_active_height' ) ); ?>px;
	}

	<?php if( get_theme_mod( 'menu_position' ) == 'menu-stacked' ) { ?>
	.wpbf-navigation-active .wpbf-menu-stacked nav {
		margin-top: <?php echo esc_attr( get_theme_mod( 'menu_active_height' ) ); ?>px;
	}
	<?php } ?>

	<?php } ?>

	<?php if( get_theme_mod( 'menu_active_stacked_bg_color' ) && get_theme_mod( 'menu_position' ) == 'menu-stacked-advanced' ) { ?>
	.wpbf-navigation-active .wpbf-menu-stacked-advanced-wrapper,
	.wpbf-transparent-header .wpbf-navigation-active .wpbf-menu-stacked-advanced-wrapper {
		background-color: <?php echo esc_attr( get_theme_mod( 'menu_active_stacked_bg_color' ) ); ?>;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'menu_active_bg_color' ) ) { ?>
	.wpbf-navigation-active,
	.wpbf-transparent-header .wpbf-navigation-active,
	.wpbf-navigation-active .wpbf-mobile-nav-wrapper {
		background-color: <?php echo esc_attr( get_theme_mod( 'menu_active_bg_color' ) ); ?>;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'menu_active_logo_color' ) && !has_custom_logo() ) { ?>
	.wpbf-navigation-active .wpbf-logo a,
	.wpbf-navigation-active .wpbf-mobile-logo {
		color: <?php echo esc_attr( get_theme_mod( 'menu_active_logo_color' ) ); ?>;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'menu_active_logo_color_alt' ) && !has_custom_logo() ) { ?>
	.wpbf-navigation-active .wpbf-logo a:hover,
	.wpbf-navigation-active .wpbf-mobile-logo:hover {
		color: <?php echo esc_attr( get_theme_mod( 'menu_active_logo_color_alt' ) ); ?>;
	}
	<?php } ?>
	
	<?php if( get_theme_mod( 'menu_active_font_color' ) ) { ?>
	.wpbf-navigation-active .wpbf-menu > .menu-item > a {
		color: <?php echo esc_attr( get_theme_mod( 'menu_active_font_color' ) ); ?>;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'menu_active_font_color_alt' ) ) { ?>
	.wpbf-navigation-active .wpbf-menu > .menu-item > a:hover {
		color: <?php echo esc_attr( get_theme_mod( 'menu_active_font_color_alt' ) ); ?>;
	}
	.wpbf-navigation-active .wpbf-menu > .current-menu-item > a {
		color: <?php echo esc_attr( get_theme_mod( 'menu_active_font_color_alt' ) ); ?> !important;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'menu_sticky' ) && get_theme_mod( 'menu_active_box_shadow' ) ) { ?>
	.wpbf-navigation.wpbf-navigation-active {
		box-shadow: 0px 0px <?php if ( get_theme_mod( 'menu_active_box_shadow_blur' ) ) : echo esc_attr( get_theme_mod( 'menu_active_box_shadow_blur' ) ) . 'px'; else : echo '5px'; endif; ?> 0px <?php if ( get_theme_mod( 'menu_active_box_shadow_color' ) ) : echo esc_attr( get_theme_mod( 'menu_active_box_shadow_color' ) ); else : echo 'rgba(0,0,0,.15)'; endif; ?>;
		-moz-box-shadow: 0px 0px <?php if ( get_theme_mod( 'menu_active_box_shadow_blur' ) ) : echo esc_attr( get_theme_mod( 'menu_active_box_shadow_blur' ) ) . 'px'; else : echo '5px'; endif; ?> 0px <?php if ( get_theme_mod( 'menu_active_box_shadow_color' ) ) : echo esc_attr( get_theme_mod( 'menu_active_box_shadow_color' ) ); else : echo 'rgba(0,0,0,.15)'; endif; ?>;
		-webkit-box-shadow: 0px 0px <?php if ( get_theme_mod( 'menu_active_box_shadow_blur' ) ) : echo esc_attr( get_theme_mod( 'menu_active_box_shadow_blur' ) ) . 'px'; else : echo '5px'; endif; ?> 0px <?php if ( get_theme_mod( 'menu_active_box_shadow_color' ) ) : echo esc_attr( get_theme_mod( 'menu_active_box_shadow_color' ) ); else : echo 'rgba(0,0,0,.15)'; endif; ?>;
	}
	<?php } ?>

	<?php // Sticky Off Canvas Navigation ?>

	<?php if( ( get_theme_mod( 'menu_position' ) == 'menu-off-canvas' || get_theme_mod( 'menu_position' ) == 'menu-off-canvas-left' || get_theme_mod( 'menu_position' ) == 'menu-full-screen' ) && get_theme_mod( 'menu_active_off_canvas_hamburger_color' ) ) { ?>
	.wpbf-navigation-active .wpbf-menu-toggle {
		color: <?php echo esc_attr( get_theme_mod( 'menu_active_off_canvas_hamburger_color' ) ); ?>;
	}
	<?php } ?>

	<?php // Mobile Sticky Navigation ?>

	<?php if( ( !get_theme_mod( 'mobile_menu_options' ) || get_theme_mod( 'mobile_menu_options' ) == 'menu-mobile-hamburger' || get_theme_mod( 'mobile_menu_options' ) == 'menu-mobile-off-canvas' ) && get_theme_mod( 'mobile_menu_active_hamburger_color' ) ) { ?>
	.wpbf-navigation-active .wpbf-mobile-menu-toggle {
		color: <?php echo esc_attr( get_theme_mod( 'mobile_menu_active_hamburger_color' ) ); ?>;
	}
	<?php } ?>

	<?php // Navigation Effects ?>

	<?php // Underline ?>

	<?php

	if( get_theme_mod( 'menu_effect' ) === 'underlined' ) { ?>

	<?php // Underline Fade ?>
	.wpbf-menu-effect-underlined.wpbf-menu-animation-fade > .menu-item > a:after {
		content: '';

		-moz-transition: opacity 0.3s;
		-o-transition: opacity 0.3s;
		-webkit-transition: opacity 0.3s;
		transition: opacity 0.3s;

		<?php if( get_theme_mod( 'menu_effect_underlined_size' ) ) { ?>	
		height: <?php echo esc_attr( get_theme_mod( 'menu_effect_underlined_size' ) ); ?>px;
		<?php } else { ?>
		height: 2px;
		<?php } ?>

		<?php if( get_theme_mod( 'menu_effect_color' ) ) { ?>
		background: <?php echo esc_attr( get_theme_mod( 'menu_effect_color' ) ); ?>;
		<?php } elseif( get_theme_mod( 'menu_font_color_alt' ) ) { ?>
		background: <?php echo esc_attr( get_theme_mod( 'menu_font_color_alt' ) ); ?>;
		<?php } else { ?>
		background: #79c4e0;
		<?php } ?>

		width: 100%;
		margin: 0;
		opacity: 0;
		display: block;
	}

	<?php // Underline Fade Hover ?>
	.wpbf-menu-effect-underlined.wpbf-menu-animation-fade .menu-item > a:hover:after {
		opacity: 1;
	}

	<?php // Underline Slide ?>
	.wpbf-menu-effect-underlined.wpbf-menu-animation-slide > .menu-item > a:after {
		content: '';

		-moz-transition: width 0.3s;
		-o-transition: width 0.3s;
		-webkit-transition: width 0.3s;
		transition: width 0.3s;

		<?php if( get_theme_mod( 'menu_effect_underlined_size' ) ) { ?>	
		height: <?php echo esc_attr( get_theme_mod( 'menu_effect_underlined_size' ) ); ?>px;
		<?php } else { ?>
		height: 2px;
		<?php } ?>

		<?php if( get_theme_mod( 'menu_effect_color' ) ) { ?>
		background: <?php echo esc_attr( get_theme_mod( 'menu_effect_color' ) ); ?>;
		<?php } elseif( get_theme_mod( 'menu_font_color_alt' ) ) { ?>
		background: <?php echo esc_attr( get_theme_mod( 'menu_font_color_alt' ) ); ?>;
		<?php } else { ?>
		background: #79c4e0;
		<?php } ?>

		width: 0;
		margin: 0 auto;
		display: block;
	}

	<?php // Underline Slide Align Left ?>
	.wpbf-menu-effect-underlined.wpbf-menu-align-left > .menu-item > a:after {
		margin: 0;
	}

	<?php // Underline Slide Align Right ?>
	.wpbf-menu-effect-underlined.wpbf-menu-align-right > .menu-item > a:after {
		margin: 0;
		float: right;
	}

	<?php // Underline Slide Hover ?>
	.wpbf-menu-effect-underlined.wpbf-menu-animation-slide .menu-item > a:hover:after {
		width: 100%;
	}

	<?php // Underline Grow ?>
	.wpbf-menu-effect-underlined.wpbf-menu-animation-grow > .menu-item > a:after {
		content: '';

		-moz-transition: all 0.3s;
		-o-transition: all 0.3s;
		-webkit-transition: all 0.3s;
		transition: all 0.3s;

		-moz-transform:scale(.85);
		-ms-transform:scale(.85);
		-o-transform:scale(.85);
		-webkit-transform:scale(.85);

		<?php if( get_theme_mod( 'menu_effect_underlined_size' ) ) { ?>	
		height: <?php echo esc_attr( get_theme_mod( 'menu_effect_underlined_size' ) ); ?>px;
		<?php } else { ?>
		height: 2px;
		<?php } ?>

		<?php if( get_theme_mod( 'menu_effect_color' ) ) { ?>
		background: <?php echo esc_attr( get_theme_mod( 'menu_effect_color' ) ); ?>;
		<?php } elseif( get_theme_mod( 'menu_font_color_alt' ) ) { ?>
		background: <?php echo esc_attr( get_theme_mod( 'menu_font_color_alt' ) ); ?>;
		<?php } else { ?>
		background: #79c4e0;
		<?php } ?>

		width: 100%;
		margin: 0;
		opacity: 0;
		display: block;
	}

	<?php // Underline Grow Hover ?>
	.wpbf-menu-effect-underlined.wpbf-menu-animation-grow .menu-item > a:hover:after {
		opacity: 1;
		-moz-transform:scale(1);
		-ms-transform:scale(1);
		-o-transform:scale(1);
		-webkit-transform:scale(1);
	}

	<?php // Underline Current Menu Item ?>
	.wpbf-menu-effect-underlined > .current-menu-item > a:after {
		width: 100% !important;
		opacity: 1 !important;
		-moz-transform:scale(1) !important;
		-ms-transform:scale(1) !important;
		-o-transform:scale(1) !important;
		-webkit-transform:scale(1) !important;
	}

	<?php // Boxed ?>

	<?php

	} elseif( get_theme_mod( 'menu_effect' ) === 'boxed' ) { ?>

	.wpbf-menu-effect-boxed > .menu-item > a {
		margin: 0 3px;
	}

	<?php // Boxed Fade ?>
	.wpbf-menu-effect-boxed.wpbf-menu-animation-fade > .menu-item > a:before {
		content: '';
		z-index: -1;

		-moz-transition: opacity 0.3s;
		-o-transition: opacity 0.3s;
		-webkit-transition: opacity 0.3s;
		transition: opacity 0.3s;

		<?php if( get_theme_mod( 'menu_effect_color' ) ) { ?>
		background: <?php echo esc_attr( get_theme_mod( 'menu_effect_color' ) ); ?>;
		<?php } else { ?>
		background: #eeeced;
		<?php } ?>

		<?php if ( get_theme_mod( 'menu_effect_boxed_radius' ) ) { ?>
		border-radius: <?php echo esc_attr( get_theme_mod( 'menu_effect_boxed_radius' ) ); ?>px;
		<?php } else { ?>
		border-radius: 0px;
		<?php } ?>

		top: 0;
		left: 0;
		opacity: 0;
		height: 100%;
		width: 100%;
		position: absolute;
	}

	<?php // Box Fade Hover ?>
	.wpbf-menu-effect-boxed.wpbf-menu-animation-fade .menu-item > a:hover:before {
		opacity: 1;
	}

	<?php // Boxed Slide ?>
	.wpbf-menu-effect-boxed.wpbf-menu-animation-slide > .menu-item > a:before {
		content: '';
		z-index: -1;

		-moz-transition: all 0.3s;
		-o-transition: all 0.3s;
		-webkit-transition: all 0.3s;
		transition: all 0.3s;

		<?php if( get_theme_mod( 'menu_effect_color' ) ) { ?>
		background: <?php echo esc_attr( get_theme_mod( 'menu_effect_color' ) ); ?>;
		<?php } else { ?>
		background: #eeeced;
		<?php } ?>

		height: 100%;
		position: absolute;
		top: 0;
		left: 50%;
		width: 0;
	}

	<?php // Box Slide Align Left ?>
	.wpbf-menu-effect-boxed.wpbf-menu-align-left > .menu-item > a:before {
		left: 0;
	}

	<?php // Box Slide Align Right ?>
	.wpbf-menu-effect-boxed.wpbf-menu-align-right > .menu-item > a:before {
		right: 0;
		left: auto;
	}

	<?php // Box Slide Hover ?>
	.wpbf-menu-effect-boxed.wpbf-menu-animation-slide .menu-item > a:hover:before {
		width: 100%;
	}

	.wpbf-menu-effect-boxed.wpbf-menu-align-center .menu-item > a:hover:before {
		left: 0;
	}

	<?php // Boxed Grow ?>
	.wpbf-menu-effect-boxed.wpbf-menu-animation-grow > .menu-item > a:before {
		content: '';
		z-index: -1;

		-moz-transition: all 0.3s;
		-o-transition: all 0.3s;
		-webkit-transition: all 0.3s;
		transition: all 0.3s;

		<?php if( get_theme_mod( 'menu_effect_color' ) ) { ?>
		background: <?php echo esc_attr( get_theme_mod( 'menu_effect_color' ) ); ?>;
		<?php } else { ?>
		background: #eeeced;
		<?php } ?>

		<?php if ( get_theme_mod( 'menu_effect_boxed_radius' ) ) { ?>
		border-radius: <?php echo esc_attr( get_theme_mod( 'menu_effect_boxed_radius' ) ); ?>px;
		<?php } else { ?>
		border-radius: 0px;
		<?php } ?>

		-moz-transform:scale(.85);
		-ms-transform:scale(.85);
		-o-transform:scale(.85);
		-webkit-transform:scale(.85);

		position: absolute;
		height: 100%;
		top: 0%;
		left: 0%;
		width: 100%;
		opacity: 0;
	}

	<?php // Box Grow Hover ?>
	.wpbf-menu-effect-boxed.wpbf-menu-animation-grow .menu-item > a:hover:before {
		opacity: 1;

		-moz-transform:scale(1);
		-ms-transform:scale(1);
		-o-transform:scale(1);
		-webkit-transform:scale(1);
	}

	<?php // Box Current Menu Item ?>
	.wpbf-menu-effect-boxed > .current-menu-item > a:before {
		opacity: 1 !important;
		width: 100% !important;
		left: 0 !important;
		-moz-transform:scale(1) !important;
		-ms-transform:scale(1) !important;
		-o-transform:scale(1) !important;
		-webkit-transform:scale(1) !important;
	}

	<?php // Modern ?>

	<?php

	} elseif( get_theme_mod( 'menu_effect' ) === 'modern' ) { ?>

	<?php // Underline ?>
	.wpbf-menu-effect-modern > .menu-item > a:after {
		content: '';
		z-index: -1;

		-moz-transition: width 0.3s;
		-o-transition: width 0.3s;
		-webkit-transition: width 0.3s;
		transition: width 0.3s;

		height:  15px;
		position: absolute;
		margin-left: -5px;
		bottom: 10px;
		width: 0;
		display: block;

		<?php if( get_theme_mod( 'menu_effect_color' ) ) { ?>
		background: <?php echo esc_attr( get_theme_mod( 'menu_effect_color' ) ); ?>;
		<?php } elseif( get_theme_mod( 'menu_font_color_alt' ) ) { ?>
		background: <?php echo esc_attr( get_theme_mod( 'menu_font_color_alt' ) ); ?>;
		opacity: .3;
		<?php } else { ?>
		background: #eeeced;
		<?php } ?>

	}

	<?php

		if ( get_theme_mod( 'menu_padding' ) ) {
			$padding = get_theme_mod( 'menu_padding' );
			$padding = $padding*2-10;
		} else {
			$padding = "30";
		}

	?>

	<?php // Underline Hover ?>
	.wpbf-menu-effect-modern > .menu-item > a:hover:after {
		width: -moz-calc(100% - <?php echo esc_attr( $padding ); ?>px);
		width: -webkit-calc(100% - <?php echo esc_attr( $padding ); ?>px);
		width: -o-calc(100% - <?php echo esc_attr( $padding ); ?>px);
		width: calc(100% - <?php echo esc_attr( $padding ); ?>px);
	}

	<?php // Underline Current Menu Item ?>
	.wpbf-menu-effect-modern > .current-menu-item > a:after {
		width: -moz-calc(100% - <?php echo esc_attr( $padding ); ?>px);
		width: -webkit-calc(100% - <?php echo esc_attr( $padding ); ?>px);
		width: -o-calc(100% - <?php echo esc_attr( $padding ); ?>px);
		width: calc(100% - <?php echo esc_attr( $padding ); ?>px);
	}

	<?php } ?>

	<?php // Footer ?>

	<?php if( get_theme_mod( 'footer_sticky' ) && !get_theme_mod( 'page_boxed' )  ) { ?>
	html{
		height: 100%;
	}

	body, #container{
		display: flex;
		flex-direction: column;
		height: 100%;
	}

	#content{
	   flex: 1 0 auto;
	}

	.wpbf-page-footer{
	   flex: 0 0 auto;
	}
	<?php } ?>

	<?php // Others ?>

	<?php if( get_theme_mod( 'social_font_size' ) ) { ?>
	.wpbf-social-icon {
		font-size: <?php echo esc_attr( get_theme_mod( 'social_font_size' ) ); ?>px;
	}
	<?php } ?>

	<?php // WooCommerce ?>

	<?php if( get_theme_mod( 'woocommerce_loop_content_alignment' ) ) { ?>
	.woocommerce ul.products li.product, .woocommerce-page ul.products li.product {
		text-align: <?php echo esc_attr( get_theme_mod( 'woocommerce_loop_content_alignment' ) ); ?>;
	}
	<?php if( get_theme_mod( 'woocommerce_loop_content_alignment' ) == 'center' ) { ?>
	.woocommerce .products .star-rating {
		margin: 0 auto 10px auto;
	}
	<?php } elseif( get_theme_mod( 'woocommerce_loop_content_alignment' ) == 'right' ) { ?>
	.woocommerce .products .star-rating {
		display: inline-block;
		text-align: right;
	}
	<?php } ?>
	<?php } ?>

	<?php if( get_theme_mod( 'woocommerce_loop_remove_button' ) ) { ?>
	.woocommerce ul.products li.product .button {
		display: none;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'woocommerce_loop_sale_alignment' ) == 'left' ) { ?>
	.woocommerce ul.products li.product .onsale {
		left: 0;
		right: auto;
		margin-left: -20px;
	}
	<?php } elseif( get_theme_mod( 'woocommerce_loop_sale_alignment' ) == 'center' ) { ?>
	.woocommerce ul.products li.product .onsale {
		right: auto;
		left: 50%;
		width: 90px;
		margin: 0 0 0 -45px;
		height: auto;
		line-height: 1;
		padding: 10px 20px;
		border-radius: 0px;
		border-bottom-left-radius: 6px;
		border-bottom-right-radius: 6px;
	}
	<?php } ?>

	<?php if( get_theme_mod( 'woocommerce_loop_sale_position' ) == 'inside' ) { ?>

	<?php if( !get_theme_mod( 'woocommerce_loop_sale_alignment' ) || get_theme_mod( 'woocommerce_loop_sale_alignment' ) == 'right' ) { ?>
	.woocommerce ul.products li.product .onsale {
		margin: 10px 10px 0 0;
	}
	<?php } elseif( get_theme_mod( 'woocommerce_loop_sale_alignment' ) == 'left' ) { ?>
	.woocommerce ul.products li.product .onsale {
		margin: 10px 0 0 10px;
	}
	<?php } ?>

	<?php } ?>

<?php } ?>