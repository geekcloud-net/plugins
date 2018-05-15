<?php
/**
 * Kirki
 *
 * @package Page Builder Framework Premium Addon
 * @subpackage Customizer
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wpbf_kirki_premium', 'wpbf_extending_kirki' );

function wpbf_extending_kirki() {

	/* Panels */

	// Scripts
	Kirki::add_panel( 'scripts_panel', array(
		'priority'			=>		6,
		'title'				=>		__( 'Scripts & Styles', 'wpbf' ),
	) );

	/* Sections – Scripts */

	// Header
	Kirki::add_section( 'wpbf_header_scripts', array(
		'title'				=>			esc_attr__( 'Header', 'wpbfpremium' ),
		'panel'				=>			'scripts_panel',
		'priority'			=>			100,
	) );

	// Footer
	Kirki::add_section( 'wpbf_footer_scripts', array(
		'title'				=>			esc_attr__( 'Footer', 'wpbfpremium' ),
		'panel'				=>			'scripts_panel',
		'priority'			=>			200,
	) );

	/* Sections – Typography */

	// Typekit
	Kirki::add_section( 'wpbf_typekit_options', array(
		'title'				=>			esc_attr__( 'Typekit (beta)', 'wpbfpremium' ),
		'panel'				=>			'typo_panel',
		'priority'			=>			800,
	) );

	/* Sections – General */

	// 404
	Kirki::add_section( 'wpbf_404_options', array(
		'title'				=>			esc_attr__( '404 Layout', 'wpbfpremium' ),
		'panel'				=>			'layout_panel',
		'priority'			=>			550,
	) );

	// Social Media Links
	Kirki::add_section( 'wpbf_social_links_options', array(
		'title'				=>			esc_attr__( 'Social Media Links', 'wpbfpremium' ),
		'panel'				=>			'layout_panel',
		'priority'			=>			1000,
	) );

	// Social Media Icons
	Kirki::add_section( 'wpbf_social_icons_options', array(
		'title'				=>			esc_attr__( 'Social Media Icons', 'wpbfpremium' ),
		'panel'				=>			'layout_panel',
		'priority'			=>			1100,
	) );

	/* Sections – Navigation */

	// Sticky Navigation
	Kirki::add_section( 'wpbf_sticky_menu_options', array(
		'title'				=>			esc_attr__( 'Sticky Navigation', 'wpbfpremium' ),
		'panel'				=>			'header_panel',
		'priority'			=>			300,
	) );

	// Navigation Effects
	Kirki::add_section( 'wpbf_menu_effect_options', array(
		'title'				=>			esc_attr__( 'Navigation Hover Effects', 'wpbfpremium' ),
		'panel'				=>			'header_panel',
		'priority'			=>			400,
	) );

	// Transparent Header
	Kirki::add_section( 'wpbf_transparent_header_options', array(
		'title'				=>			esc_attr__( 'Transparent Header', 'wpbfpremium' ),
		'panel'				=>			'header_panel',
		'priority'			=>			500,
	) );

	/* Sections – WooCommerce */

	// if( class_exists( 'WooCommerce' ) ) {

	// 	// Menu Item
	// 	Kirki::add_section( 'wpbf_woocommerce_menu_item_options', array(
	// 		'title'				=>			__( 'Menu Item', 'wpbfpremium' ),
	// 		'panel'				=>			'woocommerce',
	// 		'priority'			=>			20,
	// 	) );

	// }

	/* Fields – General */

	// 404
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'code',
		'label'				=>			esc_attr__( 'Custom 404 Page', 'wpbfpremium' ),
		'description'		=>			__( 'Replace the default 404 page with your custom layout. <br><br>Example:<br>[elementor-template id="xxx"]<br>[fl_builder_insert_layout id="xxx"]', 'wpbfpremium' ),
		'settings'			=>			'404_custom',
		'section'			=>			'wpbf_404_options',
		'priority'			=>			1,
	) );

	// Social Sortable
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'sortable',
		'settings'			=>			'social_sortable',
		'label'				=>			esc_attr__( 'Social Media Icons', 'wpbfpremium' ),
		'description'		=>			esc_attr__( 'Display social media icons in your pre-header, footer or template file by using the [social] shortcode.', 'wpbfpremium' ),
		'section'			=>			'wpbf_social_icons_options',
		'choices'			=> array(
			'facebook'		=>			esc_attr__( 'Facebook', 'wpbfpremium' ),
			'twitter'		=>			esc_attr__( 'Twitter', 'wpbfpremium' ),
			'google'		=>			esc_attr__( 'Google+', 'wpbfpremium' ),
			'pinterest'		=>			esc_attr__( 'Pinterest', 'wpbfpremium' ),
			'youtube'		=>			esc_attr__( 'Youtube', 'wpbfpremium' ),
			'instagram'		=>			esc_attr__( 'Instagram', 'wpbfpremium' ),
			'vimeo'			=>			esc_attr__( 'Vimeo', 'wpbfpremium' ),
			'soundcloud'	=>			esc_attr__( 'Soundcloud', 'wpbfpremium' ),
			'linkedin'		=>			esc_attr__( 'LinkedIn', 'wpbfpremium' ),
			'yelp'			=>			esc_attr__( 'Yelp', 'wpbfpremium' ),
		),
		'priority'			=>			1,
	) );

	// Social Shapes
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'select',
		'settings'			=>			'social_shapes',
		'label'				=>			esc_attr__( 'Style', 'wpbfpremium' ),
		'section'			=>			'wpbf_social_icons_options',
		'default'			=>			' none',
		'priority'			=>			2,
		'multiple'			=>			1,
		'choices'			=>			array(
			' wpbf-social-shape-plain'		=>			esc_attr__( 'Plain', 'wpbfpremium' ),
			' wpbf-social-shape-rounded'	=>			esc_attr__( 'Rounded', 'wpbfpremium' ),
			' wpbf-social-shape-boxed'		=>			esc_attr__( 'Boxed', 'wpbfpremium' ),
		),
	) );

	// Social Styles
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'select',
		'settings'			=>			'social_styles',
		'label'				=>			esc_attr__( 'Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_social_icons_options',
		'default'			=>			' wpbf-social-style-default',
		'priority'			=>			3,
		'multiple'			=>			1,
		'choices'			=>			array(
			' wpbf-social-style-default'	=>			esc_attr__( 'Default', 'wpbfpremium' ),
			' wpbf-social-style-grey'		=>			esc_attr__( 'Grey', 'wpbfpremium' ),
			' wpbf-social-style-brand'		=>			esc_attr__( 'Brand Colors', 'wpbfpremium' ),
			' wpbf-social-style-filled'		=>			esc_attr__( 'Filled', 'wpbfpremium' ),
		),
	) );

	// Social Size
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'select',
		'settings'			=>			'social_sizes',
		'label'				=>			esc_attr__( 'Size', 'wpbfpremium' ),
		'section'			=>			'wpbf_social_icons_options',
		'default'			=>			' wpbf-social-size-small',
		'priority'			=>			4,
		'multiple'			=>			1,
		'choices'			=>			array(
			' wpbf-social-size-small'		=>			esc_attr__( 'Small', 'wpbfpremium' ),
			' wpbf-social-size-large'		=>			esc_attr__( 'Large', 'wpbfpremium' ),
		),
	) );

	// Social Font Size
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'settings'			=>			'social_font_size',
		'label'				=>			esc_attr__( 'Font Size', 'wpbfpremium' ),
		'section'			=>			'wpbf_social_icons_options',
		'priority'			=>			5,
		'default'			=>			14,
		'transport'			=>			'postMessage',
		'choices'			=>			array(
			'min'			=>			'12',
			'max'			=>			'32',
			'step'			=>			'1',
		),
	) );

	// Facebook
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'text',
		'settings'			=>			'facebook_link',
		'transport'			=>			'postMessage',
		'label'				=>			esc_attr__( 'Facebook', 'wpbfpremium' ),
		'section'			=>			'wpbf_social_links_options',
		'priority'			=>			1,
	) );

	// Twitter
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'text',
		'settings'			=>			'twitter_link',
		'transport'			=>			'postMessage',
		'label'				=>			esc_attr__( 'Twitter', 'wpbfpremium' ),
		'section'			=>			'wpbf_social_links_options',
		'priority'			=>			1,
	) );

	// Google
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'text',
		'settings'			=>			'google_link',
		'transport'			=>			'postMessage',
		'label'				=>			esc_attr__( 'Google+', 'wpbfpremium' ),
		'section'			=>			'wpbf_social_links_options',
		'priority'			=>			1,
	) );

	// Pinterest
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'text',
		'settings'			=>			'pinterest_link',
		'transport'			=>			'postMessage',
		'label'				=>			esc_attr__( 'Pinterest', 'wpbfpremium' ),
		'section'			=>			'wpbf_social_links_options',
		'priority'			=>			1,
	) );

	// Youtube
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'text',
		'settings'			=>			'youtube_link',
		'transport'			=>			'postMessage',
		'label'				=>			esc_attr__( 'Youtube', 'wpbfpremium' ),
		'section'			=>			'wpbf_social_links_options',
		'priority'			=>			1,
	) );

	// Instagram
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'text',
		'settings'			=>			'instagram_link',
		'transport'			=>			'postMessage',
		'label'				=>			esc_attr__( 'Instagram', 'wpbfpremium' ),
		'section'			=>			'wpbf_social_links_options',
		'priority'			=>			1,
	) );

	// Vimeo
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'text',
		'settings'			=>			'vimeo_link',
		'transport'			=>			'postMessage',
		'label'				=>			esc_attr__( 'Vimeo', 'wpbfpremium' ),
		'section'			=>			'wpbf_social_links_options',
		'priority'			=>			1,
	) );

	// Soundcloud
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'text',
		'settings'			=>			'soundcloud_link',
		'transport'			=>			'postMessage',
		'label'				=>			esc_attr__( 'Soundcloud', 'wpbfpremium' ),
		'section'			=>			'wpbf_social_links_options',
		'priority'			=>			1,
	) );

	// LinkedIn
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'text',
		'settings'			=>			'linkedin_link',
		'transport'			=>			'postMessage',
		'label'				=>			esc_attr__( 'LinkedIn', 'wpbfpremium' ),
		'section'			=>			'wpbf_social_links_options',
		'priority'			=>			1,
	) );

	// Yelp
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'text',
		'settings'			=>			'yelp_link',
		'transport'			=>			'postMessage',
		'label'				=>			esc_attr__( 'Yelp', 'wpbfpremium' ),
		'section'			=>			'wpbf_social_links_options',
		'priority'			=>			1,
	) );

	/* Fields – Typography (Page) */

	// Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'page_font_color',
		'label'				=>			esc_attr__( 'Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_font_options',
		'default'			=>			'#6D7680',
		'priority'			=>			2,
		'choices'			=>			array(
			'alpha'			=>			true,
		)
	) );

	// Bold Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'page_bold_color',
		'label'				=>			esc_attr__( 'Bold Text Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_font_options',
		'priority'			=>			3,
		'transport'			=>			'postMessage',
		'choices'			=>			array(
			'alpha'			=>			true,
		)
	) );

	// Line Height
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'settings'			=>			'page_line_height',
		'label'				=>			esc_attr__( 'Line Height', 'wpbfpremium' ),
		'section'			=>			'wpbf_font_options',
		'priority'			=>			4,
		'default'			=>			'1.7',
		'transport'			=>			'postMessage',
		'choices'			=>			array(
			'min'			=>			'1',
			'max'			=>			'3',
			'step'			=>			'.1',
		),
	) );

	/* Fields – Typography (Menu) */

	// Font Size
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'dimension',
		'label'				=>			esc_attr__( 'Font Size', 'wpbfpremium' ),
		'settings'			=>			'menu_font_size',
		'section'			=>			'wpbf_menu_font_options',
		'priority'			=>			2,
		'default'			=>			'16px',
		'transport'			=>			'postMessage',
	) );

	// Letter Spacing
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'settings'			=>			'menu_letter_spacing',
		'label'				=>			esc_attr__( 'Letter Spacing', 'wpbfpremium' ),
		'section'			=>			'wpbf_menu_font_options',
		'priority'			=>			3,
		'default'			=>			'0',
		'transport'			=>			'postMessage',
		'choices'			=>			array(
			'min'			=>			'-2',
			'max'			=>			'5',
			'step'			=>			'.5',
		),
	) );

	// Text Transform
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'select',
		'settings'			=>			'menu_text_transform',
		'label'				=>			esc_attr__( 'Text transform', 'wpbfpremium' ),
		'section'			=>			'wpbf_menu_font_options',
		'default'			=>			'none',
		'priority'			=>			4,
		'multiple'			=>			1,
		'choices'			=>			array(
			'none'			=>			esc_attr__( 'None', 'wpbfpremium' ),
			'uppercase'		=>			esc_attr__( 'Uppercase', 'wpbfpremium' ),
		),
	) );

	/* Fields – Typography (H1) */

	// Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'page_h1_font_color',
		'label'				=>			esc_attr__( 'Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_h1_options',
		'priority'			=>			3,
		'choices'			=>			array(
			'alpha'			=>			true,
		)
	) );

	// Line Height
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'settings'			=>			'page_h1_line_height',
		'label'				=>			esc_attr__( 'Line Height', 'wpbfpremium' ),
		'section'			=>			'wpbf_h1_options',
		'priority'			=>			4,
		'default'			=>			'1.2',
		'transport'			=>			'postMessage',
		'choices'			=>			array(
			'min'			=>			'1',
			'max'			=>			'3',
			'step'			=>			'.1',
		),
	) );

	// Letter Spacing
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'settings'			=>			'page_h1_letter_spacing',
		'label'				=>			esc_attr__( 'Letter Spacing', 'wpbfpremium' ),
		'section'			=>			'wpbf_h1_options',
		'priority'			=>			5,
		'default'			=>			'0',
		'transport'			=>			'postMessage',
		'choices'			=>			array(
			'min'			=>			'-2',
			'max'			=>			'5',
			'step'			=>			'.5',
		),
	) );

	// Text Transform
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'select',
		'settings'			=>			'page_h1_text_transform',
		'label'				=>			esc_attr__( 'Text transform', 'wpbfpremium' ),
		'section'			=>			'wpbf_h1_options',
		'default'			=>			'none',
		'priority'			=>			6,
		'multiple'			=>			1,
		'choices'			=>			array(
			'none'			=>			esc_attr__( 'None', 'wpbfpremium' ),
			'uppercase'		=>			esc_attr__( 'Uppercase', 'wpbfpremium' ),
		),
	) );

	/* Fields – Typography (H2) */

	// Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'page_h2_font_color',
		'label'				=>			esc_attr__( 'Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_h2_options',
		'priority'			=>			3,
		'choices'			=>			array(
			'alpha'			=>			true,
		)
	) );

	// Line Height
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'settings'			=>			'page_h2_line_height',
		'label'				=>			esc_attr__( 'Line Height', 'wpbfpremium' ),
		'section'			=>			'wpbf_h2_options',
		'priority'			=>			4,
		'default'			=>			'1.2',
		'transport'			=>			'postMessage',
		'choices'			=>			array(
			'min'			=>			'1',
			'max'			=>			'3',
			'step'			=>			'.1',
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'page_h2_toggle',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		),
	) );

	// Letter Spacing
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'settings'			=>			'page_h2_letter_spacing',
		'label'				=>			esc_attr__( 'Letter Spacing', 'wpbfpremium' ),
		'section'			=>			'wpbf_h2_options',
		'priority'			=>			5,
		'default'			=>			'0',
		'transport'			=>			'postMessage',
		'choices'			=>			array(
			'min'			=>			'-2',
			'max'			=>			'5',
			'step'			=>			'.5',
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'page_h2_toggle',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		),
	) );

	// Text Transform
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'select',
		'settings'			=>			'page_h2_text_transform',
		'label'				=>			esc_attr__( 'Text transform', 'wpbfpremium' ),
		'section'			=>			'wpbf_h2_options',
		'default'			=>			'none',
		'priority'			=>			6,
		'multiple'			=>			1,
		'choices'			=>			array(
			'none'			=>			esc_attr__( 'None', 'wpbfpremium' ),
			'uppercase'		=>			esc_attr__( 'Uppercase', 'wpbfpremium' ),
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'page_h2_toggle',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		),
	) );

	/* Fields – Typography (H3) */

	// Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'page_h3_font_color',
		'label'				=>			esc_attr__( 'Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_h3_options',
		'priority'			=>			3,
		'choices'			=>			array(
			'alpha'			=>			true,
		)
	) );

	// Line Height
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'settings'			=>			'page_h3_line_height',
		'label'				=>			esc_attr__( 'Line Height', 'wpbfpremium' ),
		'section'			=>			'wpbf_h3_options',
		'priority'			=>			4,
		'default'			=>			'1.2',
		'transport'			=>			'postMessage',
		'choices'			=>			array(
			'min'			=>			'1',
			'max'			=>			'3',
			'step'			=>			'.1',
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'page_h3_toggle',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		),
	) );

	// Letter Spacing
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'settings'			=>			'page_h3_letter_spacing',
		'label'				=>			esc_attr__( 'Letter Spacing', 'wpbfpremium' ),
		'section'			=>			'wpbf_h3_options',
		'priority'			=>			5,
		'default'			=>			'0',
		'transport'			=>			'postMessage',
		'choices'			=>			array(
			'min'			=>			'-2',
			'max'			=>			'5',
			'step'			=>			'.5',
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'page_h3_toggle',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		),
	) );

	// Text Transform
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'select',
		'settings'			=>			'page_h3_text_transform',
		'label'				=>			esc_attr__( 'Text transform', 'wpbfpremium' ),
		'section'			=>			'wpbf_h3_options',
		'default'			=>			'none',
		'priority'			=>			6,
		'multiple'			=>			1,
		'choices'			=>			array(
			'none'			=>			esc_attr__( 'None', 'wpbfpremium' ),
			'uppercase'		=>			esc_attr__( 'Uppercase', 'wpbfpremium' ),
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'page_h3_toggle',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		),
	) );

	/* Fields – Typography (H4) */

	// Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'page_h4_font_color',
		'label'				=>			esc_attr__( 'Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_h4_options',
		'priority'			=>			3,
		'choices'			=>			array(
			'alpha'			=>			true,
		)
	) );

	// Line Height
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'settings'			=>			'page_h4_line_height',
		'label'				=>			esc_attr__( 'Line Height', 'wpbfpremium' ),
		'section'			=>			'wpbf_h4_options',
		'priority'			=>			4,
		'default'			=>			'1.2',
		'transport'			=>			'postMessage',
		'choices'			=>			array(
			'min'			=>			'1',
			'max'			=>			'3',
			'step'			=>			'.1',
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'page_h4_toggle',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		),
	) );

	// Letter Spacing
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'settings'			=>			'page_h4_letter_spacing',
		'label'				=>			esc_attr__( 'Letter Spacing', 'wpbfpremium' ),
		'section'			=>			'wpbf_h4_options',
		'priority'			=>			5,
		'default'			=>			'0',
		'transport'			=>			'postMessage',
		'choices'			=>			array(
			'min'			=>			'-2',
			'max'			=>			'5',
			'step'			=>			'.5',
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'page_h4_toggle',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		),
	) );

	// Text Transform
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'select',
		'settings'			=>			'page_h4_text_transform',
		'label'				=>			esc_attr__( 'Text transform', 'wpbfpremium' ),
		'section'			=>			'wpbf_h4_options',
		'default'			=>			'none',
		'priority'			=>			6,
		'multiple'			=>			1,
		'choices'			=>			array(
			'none'			=>			esc_attr__( 'None', 'wpbfpremium' ),
			'uppercase'		=>			esc_attr__( 'Uppercase', 'wpbfpremium' ),
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'page_h4_toggle',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		),
	) );

	/* Fields – Typography (H5) */

	// Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'page_h5_font_color',
		'label'				=>			esc_attr__( 'Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_h5_options',
		'priority'			=>			3,
		'choices'			=>			array(
			'alpha'			=>			true,
		)
	) );

	// Line Height
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'settings'			=>			'page_h5_line_height',
		'label'				=>			esc_attr__( 'Line Height', 'wpbfpremium' ),
		'section'			=>			'wpbf_h5_options',
		'priority'			=>			4,
		'default'			=>			'1.2',
		'transport'			=>			'postMessage',
		'choices'			=>			array(
			'min'			=>			'1',
			'max'			=>			'3',
			'step'			=>			'.1',
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'page_h5_toggle',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		),
	) );

	// Letter Spacing
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'settings'			=>			'page_h5_letter_spacing',
		'label'				=>			esc_attr__( 'Letter Spacing', 'wpbfpremium' ),
		'section'			=>			'wpbf_h5_options',
		'priority'			=>			5,
		'default'			=>			'0',
		'transport'			=>			'postMessage',
		'choices'			=>			array(
			'min'			=>			'-2',
			'max'			=>			'5',
			'step'			=>			'.5',
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'page_h5_toggle',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		),
	) );

	// Text Transform
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'select',
		'settings'			=>			'page_h5_text_transform',
		'label'				=>			esc_attr__( 'Text transform', 'wpbfpremium' ),
		'section'			=>			'wpbf_h5_options',
		'default'			=>			'none',
		'priority'			=>			6,
		'multiple'			=>			1,
		'choices'			=>			array(
			'none'			=>			esc_attr__( 'None', 'wpbfpremium' ),
			'uppercase'		=>			esc_attr__( 'Uppercase', 'wpbfpremium' ),
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'page_h5_toggle',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		),
	) );

	/* Fields – Typography (H6) */

	// Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'page_h6_font_color',
		'label'				=>			esc_attr__( 'Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_h6_options',
		'priority'			=>			3,
		'choices'			=>			array(
			'alpha'			=>			true,
		)
	) );

	// Line Height
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'settings'			=>			'page_h6_line_height',
		'label'				=>			esc_attr__( 'Line Height', 'wpbfpremium' ),
		'section'			=>			'wpbf_h6_options',
		'priority'			=>			4,
		'default'			=>			'1.2',
		'transport'			=>			'postMessage',
		'choices'			=>			array(
			'min'			=>			'1',
			'max'			=>			'3',
			'step'			=>			'.1',
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'page_h6_toggle',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		),
	) );

	// Letter Spacing
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'settings'			=>			'page_h6_letter_spacing',
		'label'				=>			esc_attr__( 'Letter Spacing', 'wpbfpremium' ),
		'section'			=>			'wpbf_h6_options',
		'priority'			=>			5,
		'default'			=>			'0',
		'transport'			=>			'postMessage',
		'choices'			=>			array(
			'min'			=>			'-2',
			'max'			=>			'5',
			'step'			=>			'.5',
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'page_h6_toggle',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		),
	) );

	// Text Transform
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'select',
		'settings'			=>			'page_h6_text_transform',
		'label'				=>			esc_attr__( 'Text transform', 'wpbfpremium' ),
		'section'			=>			'wpbf_h6_options',
		'default'			=>			'none',
		'priority'			=>			6,
		'multiple'			=>			1,
		'choices'			=>			array(
			'none'			=>			esc_attr__( 'None', 'wpbfpremium' ),
			'uppercase'		=>			esc_attr__( 'Uppercase', 'wpbfpremium' ),
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'page_h6_toggle',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		),
	) );

	/* Fields – Typekit */

	// Toggle
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'toggle',
		'settings'			=>			'enable_typekit',
		'label'				=>			esc_attr__( 'Enable Typekit', 'wpbfpremium' ),
		'section'			=>			'wpbf_typekit_options',
		'default'			=>			'0',
		'priority'			=>			'1'
	));

	// ID
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'text',
		'settings'			=>			'typekit_id',
		'label'				=>			esc_attr__( 'Typekit ID', 'wpbfpremium' ),
		'section'			=>			'wpbf_typekit_options',
		'default'			=>			'iel4zhm',
		'priority'			=>			'2',
		'active_callback'	=>			array(
			array(
			'setting'		=>			'enable_typekit',
			'operator'		=>			'==',
			'value'			=>			'1',
			)
		),
	));

	// Fonts
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'repeater',
		'label'				=>			esc_attr__( 'Typekit Fonts', 'wpbfpremium' ),
		'description'		=>			esc_attr__( 'Here you can add typekit fonts', 'wpbfpremium' ),
		'settings'			=>			'typekit_fonts',
		'priority'			=>			'3',
		'section'			=>			'wpbf_typekit_options',
		'row_label'			=>			array(
			'type'			=>			'text',
			'value'			=>			esc_attr__( 'Typekit Font', 'wpbfpremium' ),
			),
		'default'			=>			array(
			array(
			'font_name'		=>			'Sofia Pro',
			'font_css_name'	=>			'sofia-pro',
			'font_variants' =>			array( 'regular', 'italic', '700', '700italic' ),
			),
		),
		'fields'			=>			array(
			'font_name'		=>			array(
				'type'		=>			'text',
				'label'		=>			esc_attr__( 'Name', 'wpbfpremium' ),
			),
			'font_css_name'	=>			array(
				'type'		=>			'text',
				'label'		=>			esc_attr__( 'Font Family', 'wpbfpremium' ),
			),
			'font_variants'	=>			array(
				'type'		=>			'select',
				'label'		=>			esc_attr__( 'Variants', 'wpbfpremium' ),
				'multiple'	=>			18,
				'choices'	=>			array(
					'100'		=>		esc_attr__( '100', 'wpbfpremium' ),
					'100italic'	=>		esc_attr__( '100italic', 'wpbfpremium' ),
					'200'		=>		esc_attr__( '200', 'wpbfpremium' ),
					'200italic'	=>		esc_attr__( '200italic', 'wpbfpremium' ),
					'300'		=>		esc_attr__( '300', 'wpbfpremium' ),
					'300italic'	=>		esc_attr__( '300italic', 'wpbfpremium' ),
					'regular'	=>		esc_attr__( 'regular', 'wpbfpremium' ),
					'italic'	=>		esc_attr__( 'italic', 'wpbfpremium' ),
					'500'		=>		esc_attr__( '500', 'wpbfpremium' ),
					'500italic'	=>		esc_attr__( '500italic', 'wpbfpremium' ),
					'600'		=>		esc_attr__( '600', 'wpbfpremium' ),
					'600italic'	=>		esc_attr__( '600italic', 'wpbfpremium' ),
					'700'		=>		esc_attr__( '700', 'wpbfpremium' ),
					'700italic'	=>		esc_attr__( '700italic', 'wpbfpremium' ),
					'800'		=>		esc_attr__( '800', 'wpbfpremium' ),
					'800italic'	=>		esc_attr__( '800italic', 'wpbfpremium' ),
					'900'		=>		esc_attr__( '900', 'wpbfpremium' ),
					'900italic'	=>		esc_attr__( '900italic', 'wpbfpremium' ),
				)
			),
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'enable_typekit',
			'operator'		=>			'==',
			'value'			=>			'1'
			)
		)
	));

	/* Fields – Sticky Navigation */

	$i = 0;

	// Toggle
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'toggle',
		'settings'			=>			'menu_sticky',
		'label'				=>			esc_attr__( 'Sticky Navigation', 'wpbfpremium' ),
		'section'			=>			'wpbf_sticky_menu_options',
		'default'			=>			'0',
		'priority'			=>			$i++,
	) );

	// Logo
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'image',
		'settings'			=>			'menu_active_logo',
		'label'				=>			esc_attr__( 'Logo', 'wpbfpremium' ),
		'section'			=>			'wpbf_sticky_menu_options',
		'priority'			=>			$i++,
		'active_callback'	=>			array(
			array(
			'setting'		=>			'custom_logo',
			'operator'		=>			'!=',
			'value'			=>			'',
			),
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		)
	) );

	// Hide Logo
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'toggle',
		'settings'			=>			'menu_active_hide_logo',
		'label'				=>			esc_attr__( 'Hide Logo', 'wpbfpremium' ),
		'description'		=>			esc_attr__('Hides the logo from the sticky navigation.', 'wpbfpremium'),
		'section'			=>			'wpbf_sticky_menu_options',
		'default'			=>			'0',
		'priority'			=>			$i++,
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			true,
			),
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'in',
			'value'			=>			array( 'menu-stacked', 'menu-stacked-advanced', 'menu-centered' ),
			),
		)
	) );

	// Logo Size
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'settings'			=>			'menu_active_logo_size',
		'label'				=>			esc_attr__( 'Logo Size', 'wpbfpremium' ),
		'section'			=>			'wpbf_sticky_menu_options',
		'priority'			=>			$i++,
		'default'			=>			'48',
		'choices'			=>			array(
			'min'			=>			'15',
			'max'			=>			'150',
			'step'			=>			'1',
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			1,
			),
			array(
			'setting'		=>			'custom_logo',
			'operator'		=>			'!=',
			'value'			=>			'',
			),
		)
	) );

	// Height
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'label'				=>			esc_attr__( 'Menu Height', 'wpbfpremium' ),
		'settings'			=>			'menu_active_height',
		'section'			=>			'wpbf_sticky_menu_options',
		'priority'			=>			$i++,
		'default'			=>			'20',
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		),
		'choices'			=>			array(
			'min'			=>			'10',
			'max'			=>			'80',
			'step'			=>			'1',
		),
	) );

	// Delay
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'dimension',
		'label'				=>			esc_attr__( 'Delay', 'wpbfpremium' ),
		'settings'			=>			'menu_active_delay',
		'section'			=>			'wpbf_sticky_menu_options',
		'priority'			=>			$i++,
		'default'			=>			'',
		'description'		=>			esc_attr__( 'Set a delay after the sticky navigation should appear. Default: 300px', 'wpbfpremium' ),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		)
	) );

	// Logo Background Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'menu_active_stacked_bg_color',
		'label'				=>			esc_attr__( 'Logo Area Background Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_sticky_menu_options',
		'default'			=>			'#ffffff',
		'priority'			=>			$i++,
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			true,
			),
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'==',
			'value'			=>			'menu-stacked-advanced',
			),
			array(
			'setting'		=>			'menu_active_hide_logo',
			'operator'		=>			'==',
			'value'			=>			false,
			)
		),
		'choices'			=>			array(
			'alpha'			=>			true,
		),
	) );

	// Background Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'menu_active_bg_color',
		'label'				=>			esc_attr__( 'Background Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_sticky_menu_options',
		'default'			=>			'#f5f5f7',
		'priority'			=>			$i++,
		'choices'			=>			array(
			'alpha'			=>			true,
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		)
	) );

	// Font Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'menu_active_font_color',
		'label'				=>			esc_attr__( 'Font Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_sticky_menu_options',
		'priority'			=>			$i++,
		'choices'			=>			array(
			'alpha'			=>			true,
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		)
	) );

	// Font Color Alt
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'menu_active_font_color_alt',
		'label'				=>			esc_attr__( 'Hover', 'wpbfpremium' ),
		'section'			=>			'wpbf_sticky_menu_options',
		'priority'			=>			$i++,
		'choices'			=>			array(
			'alpha'			=>			true,
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		)
	) );

	// Logo Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'menu_active_logo_color',
		'label'				=>			esc_attr__( 'Logo Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_sticky_menu_options',
		'priority'			=>			$i++,
		'choices'			=>			array(
			'alpha'			=>			true,
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'custom_logo',
			'operator'		=>			'==',
			'value'			=>			'',
			),
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		)
	) );

	// Logo Color Alt
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'menu_active_logo_color_alt',
		'label'				=>			esc_attr__( 'Hover', 'wpbfpremium' ),
		'section'			=>			'wpbf_sticky_menu_options',
		'priority'			=>			$i++,
		'choices'			=>			array(
			'alpha'			=>			true,
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'custom_logo',
			'operator'		=>			'==',
			'value'			=>			'',
			),
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		)
	) );

	// Animation
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'radio-buttonset',
		'settings'			=>			'menu_active_animation',
		'label'				=>			esc_attr__( 'Animation', 'wpbfpremium' ),
		'section'			=>			'wpbf_sticky_menu_options',
		'default'			=>			'none',
		'priority'			=>			$i++,
		'choices'			=>			array(
			'none'			=>			esc_attr__( 'None', 'wpbfpremium' ),
			'fade'			=>			esc_attr__( 'Fade In', 'wpbfpremium' ),
			'slide'			=>			esc_attr__( 'Slide Down', 'wpbfpremium' ),
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		)
	) );

	// Animation Duration
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'label'				=>			esc_attr__( 'Animation Duration', 'wpbfpremium' ),
		'settings'			=>			'menu_active_animation_duration',
		'section'			=>			'wpbf_sticky_menu_options',
		'priority'			=>			$i++,
		'default'			=>			'200',
		'choices'			=>			array(
			'min'			=>			'50',
			'max'			=>			'1000',
			'step'			=>			'10',
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			true,
			),
			array(
			'setting'		=>			'menu_active_animation',
			'operator'		=>			'!==',
			'value'			=>			'none',
			),
		)
	) );

	// Box Shadow
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'toggle',
		'settings'			=>			'menu_active_box_shadow',
		'label'				=>			esc_attr__( 'Box Shadow', 'wpbfpremium' ),
		'section'			=>			'wpbf_sticky_menu_options',
		'default'			=>			0,
		'priority'			=>			$i++,
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			true,
			),
		),
	) );

	// Box Shadow Blur
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'settings'			=>			'menu_active_box_shadow_blur',
		'label'				=>			esc_attr__( 'Blur', 'wpbfpremium' ),
		'section'			=>			'wpbf_sticky_menu_options',
		'priority'			=>			$i++,
		'default'			=>			5,
		'choices'			=>			array(
			'min'			=>			'0',
			'max'			=>			'50',
			'step'			=>			'1',
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			true,
			),
			array(
			'setting'		=>			'menu_active_box_shadow',
			'operator'		=>			'==',
			'value'			=>			1,
			),
		),
	) );

	// Box Shadow Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'menu_active_box_shadow_color',
		'label'				=>			esc_attr__( 'Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_sticky_menu_options',
		'default'			=>			'rgba(0,0,0,.15)',
		'priority'			=>			$i++,
		'choices'			=>			array(
			'alpha'			=>			true,
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			true,
			),
			array(
			'setting'		=>			'menu_active_box_shadow',
			'operator'		=>			'==',
			'value'			=>			1,
			),
		),
	) );

	// Off Canvas Headline
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'custom',
		'settings'			=>			'active-off-canvas-headline',
		'section'			=>			'wpbf_sticky_menu_options',
		'default'			=>			'<h3 style="padding:15px 10px; background:#fff; margin:0;">'. __( 'Off Canvas Settings', 'wpbfpremium' ) .'</h3>',
		'priority'			=>			$i++,
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			true,
			),
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'in',
			'value'			=>			array( 'menu-off-canvas', 'menu-off-canvas-left' ),
			)
		)
	) );

	// Full Screen Headline
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'custom',
		'settings'			=>			'active-full-screen-headline',
		'section'			=>			'wpbf_sticky_menu_options',
		'default'			=>			'<h3 style="padding:15px 10px; background:#fff; margin:0;">'. __( 'Full Screen Menu Settings', 'wpbfpremium' ) .'</h3>',
		'priority'			=>			$i++,
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			true,
			),
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'==',
			'value'			=>			'menu-full-screen',
			)
		)
	) );

	// Off Canvas Hamburger Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'menu_active_off_canvas_hamburger_color',
		'label'				=>			esc_attr__( 'Hamburger Icon Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_sticky_menu_options',
		'priority'			=>			$i++,
		'choices'			=>			array(
			'alpha'			=>			true,
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			true,
			),
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'in',
			'value'			=>			array( 'menu-off-canvas', 'menu-off-canvas-left', 'menu-full-screen' ),
			),
		)
	) );

	// Mobile Menu Headline
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'custom',
		'settings'			=>			'active-mobile-menu-headline',
		'section'			=>			'wpbf_sticky_menu_options',
		'default'			=>			'<h3 style="padding:15px 10px; background:#fff; margin:0;">'. __( 'Mobile Menu Settings', 'wpbfpremium' ) .'</h3>',
		'priority'			=>			$i++,
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			true,
			),
			array(
			'setting'		=>			'mobile_menu_options',
			'operator'		=>			'!=',
			'value'			=>			'menu-mobile-default',
			)
		)
	) );

	// Mobile Menu Hamburger Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'mobile_menu_active_hamburger_color',
		'label'				=>			esc_attr__( 'Hamburger Icon Color', 'page-builder-framework' ),
		'section'			=>			'wpbf_sticky_menu_options',
		'priority'			=>			$i++,
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_sticky',
			'operator'		=>			'==',
			'value'			=>			true,
			),
			array(
			'setting'		=>			'mobile_menu_options',
			'operator'		=>			'!=',
			'value'			=>			'menu-mobile-default',
			)
		)
	) );

	/* Fields – Transparent Header */

	// Logo
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'image',
		'settings'			=>			'menu_transparent_logo',
		'label'				=>			esc_attr__( 'Logo', 'wpbfpremium' ),
		'section'			=>			'wpbf_transparent_header_options',
		'priority'			=>			0,
		'active_callback'	=>			array(
			array(
			'setting'		=>			'custom_logo',
			'operator'		=>			'!=',
			'value'			=>			'',
			)
		)
	) );

	// Background Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'menu_transparent_background_color',
		'label'				=>			esc_attr__( 'Background Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_transparent_header_options',
		'priority'			=>			1,
		'choices'			=>			array(
			'alpha'			=>			true,
		),
	) );

	// Font Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'menu_transparent_font_color',
		'label'				=>			esc_attr__( 'Font Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_transparent_header_options',
		'priority'			=>			2,
		'choices'			=>			array(
			'alpha'			=>			true,
		),
	) );

	// Font Color Alt
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'menu_transparent_font_color_alt',
		'label'				=>			esc_attr__( 'Hover', 'wpbfpremium' ),
		'section'			=>			'wpbf_transparent_header_options',
		'priority'			=>			3,
		'choices'			=>			array(
			'alpha'			=>			true,
		),
	) );

	/* Fields – Sub Menu */

	Kirki::add_field( 'wpbf', array(
		'type'				=>			'custom',
		'settings'			=>			'separator-99985',
		'section'			=>			'wpbf_sub_menu_options',
		'default'			=>			'<hr style="border-top: 1px solid #ccc; border-bottom: 1px solid #f8f8f8">',
		'priority'			=>			7,
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'!=',
			'value'			=>			'menu-off-canvas',
			),
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'!=',
			'value'			=>			'menu-off-canvas-left',
			),
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'!=',
			'value'			=>			'menu-full-screen',
			),
		)
	) );

	// Animation
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'select',
		'settings'			=>			'sub_menu_animation',
		'label'				=>			esc_attr__( 'Sub Menu Animation', 'wpbfpremium' ),
		'section'			=>			'wpbf_sub_menu_options',
		'default'			=>			'fade',
		'priority'			=>			7,
		'multiple'			=>			1,
		'choices'			=>			array(
			'fade'			=>			esc_attr__( 'Fade', 'wpbfpremium' ),
			'down'			=>			esc_attr__( 'Down', 'wpbfpremium' ),
			'up'			=>			esc_attr__( 'Up', 'wpbfpremium' ),
			'zoom-in'		=>			esc_attr__( 'Zoom In', 'wpbfpremium' ),
			'zoom-out'		=>			esc_attr__( 'Zoom Out', 'wpbfpremium' ),
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'!=',
			'value'			=>			'menu-off-canvas',
			),
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'!=',
			'value'			=>			'menu-off-canvas-left',
			),
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'!=',
			'value'			=>			'menu-full-screen',
			),
		)
	) );

	// Animation Duration
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'label'				=>			esc_attr__( 'Sub Menu Animation Duration', 'wpbf' ),
		'settings'			=>			'sub_menu_animation_duration',
		'section'			=>			'wpbf_sub_menu_options',
		'priority'			=>			8,
		'default'			=>			'250',
		'choices'			=>			array(
			'min'			=>			'50',
			'max'			=>			'1000',
			'step'			=>			'10',
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'!=',
			'value'			=>			'menu-off-canvas',
			),
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'!=',
			'value'			=>			'menu-off-canvas-left',
			),
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'!=',
			'value'			=>			'menu-full-screen',
			),
		)
	) );

	/* Fields – Mobile Menu */

	// Off Canvas Width
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'dimension',
		'label'				=>			esc_attr__( 'Menu Width', 'wpbfpremium' ),
		'description'		=>			esc_attr__( 'Default: 320px', 'wpbfpremium' ),
		'settings'			=>			'mobile_menu_width',
		'section'			=>			'wpbf_mobile_menu_options',
		'priority'			=>			7,
		'active_callback'	=>			array(
			array(
			'setting'		=>			'mobile_menu_options',
			'operator'		=>			'==',
			'value'			=>			'menu-mobile-off-canvas',
			),
		)
	) );

	/* Fields – Custom Menu */

	if ( is_plugin_active( 'bb-plugin/fl-builder.php' ) || is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {

		Kirki::add_field( 'wpbf', array(
			'type'				=>			'custom',
			'settings'			=>			'separator-61123',
			'section'			=>			'wpbf_menu_options',
			'default'			=>			'<hr style="border-top: 1px solid #ccc; border-bottom: 1px solid #f8f8f8">',
			'priority'			=>			999998,
		) );

		// Custom Menu
		Kirki::add_field( 'wpbf', array(
			'type'				=>			'code',
			'label'				=>			esc_attr__( 'Custom Menu', 'wpbfpremium' ),
			'description'		=>			__( 'Paste your shortcode to replace the default menu with your Custom Menu. <br><br>Example:<br>[elementor-template id="xxx"]<br>[fl_builder_insert_layout id="xxx"]', 'wpbfpremium' ), //esc_html maybe
			'settings'			=>			'menu_custom',
			'section'			=>			'wpbf_menu_options',
			'priority'			=>			999999,
		) );

	} 

	/* Fields – Stacked (Advanced) */

	// Headline
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'custom',
		'settings'			=>			'stacked-advanced-headline',
		'section'			=>			'wpbf_menu_options',
		'default'			=>			'<h3 style="padding:15px 10px; background:#fff; margin:0;">'. __( 'Advanced Settings', 'wpbfpremium' ) .'</h3>',
		'priority'			=>			100,
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'==',
			'value'			=>			'menu-stacked-advanced',
			)
		)
	) );

	// Alignment
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'radio-image',
		'settings'			=>			'menu_alignment',
		'label'				=>			esc_attr__( 'Menu Alignment', 'wpbfpremium' ),
		'section'			=>			'wpbf_menu_options',
		'default'			=>			'left',
		'priority'			=>			110,
		'multiple'			=>			1,
		'choices'			=>			array(
			'left'			=>			WPBF_PREMIUM_URI . '/inc/customizer/img/align-left.jpg',
			'center'		=>			WPBF_PREMIUM_URI . '/inc/customizer/img/align-center.jpg',
			'right'			=>			WPBF_PREMIUM_URI . '/inc/customizer/img/align-right.jpg',
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'==',
			'value'			=>			'menu-stacked-advanced',
			)
		)
	) );

	// WYSIWYG
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'wysiwyg',
		'settings'			=>			'menu_stacked_wysiwyg',
		'label'				=>			esc_attr__( 'Content beside Logo', 'wpbfpremium' ),
		'section'			=>			'wpbf_menu_options',
		'default'			=>			'',
		'priority'			=>			120,
		'transport'			=>			'postMessage',
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'==',
			'value'			=>			'menu-stacked-advanced',
			)
		),
	) );

	// Logo Height
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'label'				=>			esc_attr__( 'Logo Area Height', 'wpbf' ),
		'settings'			=>			'menu_stacked_logo_height',
		'section'			=>			'wpbf_menu_options',
		'priority'			=>			130,
		'default'			=>			'20',
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'==',
			'value'			=>			'menu-stacked-advanced',
			)
		),
		'choices'			=>			array(
			'min'			=>			'5',
			'max'			=>			'80',
			'step'			=>			'1',
		),
	) );

	// Background Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'menu_stacked_bg_color',
		'label'				=>			esc_attr__( 'Logo Area Background Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_menu_options',
		'default'			=>			'#ffffff',
		'priority'			=>			140,
		'transport'			=>			'postMessage',
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'==',
			'value'			=>			'menu-stacked-advanced',
			)
		),
		'choices'			=>			array(
			'alpha'			=>			true,
		),
	) );

	/* Fields – Off Canvas */

	Kirki::add_field( 'wpbf', array(
		'type'				=>			'custom',
		'settings'			=>			'off-canvas-headline',
		'section'			=>			'wpbf_menu_options',
		'default'			=>			'<h3 style="padding:15px 10px; background:#fff; margin:0;">'. __( 'Off Canvas Settings', 'wpbfpremium' ) .'</h3>',
		'priority'			=>			200,
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'in',
			'value'			=>			array( 'menu-off-canvas', 'menu-off-canvas-left' ),
			)
		)
	) );

	// Headline
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'custom',
		'settings'			=>			'full-screen-headline',
		'section'			=>			'wpbf_menu_options',
		'default'			=>			'<h3 style="padding:15px 10px; background:#fff; margin:0;">'. __( 'Full Screen Menu Settings', 'wpbfpremium' ) .'</h3>',
		'priority'			=>			200,
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'==',
			'value'			=>			'menu-full-screen',
			)
		)
	) );

	// Push Menu
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'toggle',
		'settings'			=>			'menu_off_canvas_push',
		'label'				=>			esc_attr__( 'Push Menu', 'wpbfpremium' ),
		'section'			=>			'wpbf_menu_options',
		'priority'			=>			210,
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'in',
			'value'			=>			array( 'menu-off-canvas', 'menu-off-canvas-left' ),
			),
		)
	) );

	// Menu Width
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'label'				=>			esc_attr__( 'Menu Width', 'wpbfpremium' ),
		'settings'			=>			'menu_off_canvas_width',
		'section'			=>			'wpbf_menu_options',
		'priority'			=>			220,
		'default'			=>			'400',
		'choices'			=>			array(
			'min'			=>			'300',
			'max'			=>			'500',
			'step'			=>			'10',
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'in',
			'value'			=>			array( 'menu-off-canvas', 'menu-off-canvas-left' ),
			),
		)
	) );

	// Off Canvas Hamburger Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'menu_off_canvas_hamburger_color',
		'label'				=>			esc_attr__( 'Hamburger Icon Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_menu_options',
		'default'			=>			'#6D7680',
		'priority'			=>			230,
		'transport'			=>			'postMessage',
		'choices'			=>			array(
			'alpha'			=>			true,
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'in',
			'value'			=>			array( 'menu-off-canvas', 'menu-off-canvas-left', 'menu-full-screen' ),
			),
		)
	) );

	// Off Canvas Background Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'menu_off_canvas_bg_color',
		'label'				=>			esc_attr__( 'Off Canvas Menu Background Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_menu_options',
		'default'			=>			'#ffffff',
		'priority'			=>			240,
		'choices'			=>			array(
			'alpha'			=>			true,
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'in',
			'value'			=>			array( 'menu-off-canvas', 'menu-off-canvas-left', 'menu-full-screen' ),
			),
		)
	) );

	// Off Canvas Submenu Arrow Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'menu_off_canvas_submenu_arrow_color',
		'label'				=>			esc_attr__( 'Submenu Arrow Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_menu_options',
		'priority'			=>			260,
		'transport'			=>			'postMessage',
		'choices'			=>			array(
			'alpha'			=>			true,
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_position',
			'operator'		=>			'in',
			'value'			=>			array( 'menu-off-canvas', 'menu-off-canvas-left' ),
			),
		)
	) );

	/* Fields – Navigation Effects */

	// Effect
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'select',
		'settings'			=>			'menu_effect',
		'label'				=>			esc_attr__( 'Hover Effect', 'wpbfpremium' ),
		'section'			=>			'wpbf_menu_effect_options',
		'default'			=>			'none',
		'priority'			=>			1,
		'multiple'			=>			1,
		'choices'			=>			array(
			'none'			=>			esc_attr__( 'None', 'wpbfpremium' ),
			'underlined'	=>			esc_attr__( 'Underline', 'wpbfpremium' ),
			'boxed'			=>			esc_attr__( 'Box', 'wpbfpremium' ),
			'modern'		=>			esc_attr__( 'Modern', 'wpbfpremium' ),
		),
	) );

	// Animation
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'select',
		'settings'			=>			'menu_effect_animation',
		'label'				=>			esc_attr__( 'Animation', 'wpbfpremium' ),
		'section'			=>			'wpbf_menu_effect_options',
		'default'			=>			'fade',
		'priority'			=>			1,
		'multiple'			=>			1,
		'choices'			=>			array(
			'fade'			=>			esc_attr__( 'Fade', 'wpbfpremium' ),
			'slide'			=>			esc_attr__( 'Slide', 'wpbfpremium' ),
			'grow'			=>			esc_attr__( 'Grow', 'wpbfpremium' ),
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_effect',
			'operator'		=>			'!=',
			'value'			=>			'none',
			),
			array(
			'setting'		=>			'menu_effect',
			'operator'		=>			'!=',
			'value'			=>			'modern',
			)
		)
	) );

	// Alignment
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'radio-image',
		'settings'			=>			'menu_effect_alignment',
		'label'				=>			esc_attr__( 'Alignment', 'wpbfpremium' ),
		'section'			=>			'wpbf_menu_effect_options',
		'default'			=>			'center',
		'priority'			=>			2,
		'choices'			=>			array(
			'left'			=>			WPBF_PREMIUM_URI . '/inc/customizer/img/align-left.jpg',
			'center'		=>			WPBF_PREMIUM_URI . '/inc/customizer/img/align-center.jpg',
			'right'			=>			WPBF_PREMIUM_URI . '/inc/customizer/img/align-right.jpg',
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_effect_animation',
			'operator'		=>			'==',
			'value'			=>			'slide',
			),
			array(
			'setting'		=>			'menu_effect',
			'operator'		=>			'!=',
			'value'			=>			'modern',
			),
			array(
			'setting'		=>			'menu_effect',
			'operator'		=>			'!=',
			'value'			=>			'none',
			)
		)
	) );

	// Color
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'color',
		'settings'			=>			'menu_effect_color',
		'label'				=>			esc_attr__( 'Color', 'wpbfpremium' ),
		'section'			=>			'wpbf_menu_effect_options',
		'priority'			=>			3,
		'choices'			=>			array(
			'alpha'			=>			true,
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_effect',
			'operator'		=>			'!=',
			'value'			=>			'none',
			),
		)
	) );

	// Size (Underlined)
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'settings'			=>			'menu_effect_underlined_size',
		'label'				=>			esc_attr__( 'Size', 'wpbfpremium' ),
		'section'			=>			'wpbf_menu_effect_options',
		'priority'			=>			4,
		'default'			=>			'2',
		'choices'			=>			array(
			'min'			=>			'1',
			'max'			=>			'5',
			'step'			=>			'1',
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_effect',
			'operator'		=>			'==',
			'value'			=>			'underlined',
			),
		)
	) );

	// Border Radius (Boxed)
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'slider',
		'settings'			=>			'menu_effect_boxed_radius',
		'label'				=>			esc_attr__( 'Border Radius', 'wpbfpremium' ),
		'section'			=>			'wpbf_menu_effect_options',
		'priority'			=>			5,
		'default'			=>			'0',
		'choices'			=>			array(
			'min'			=>			'0',
			'max'			=>			'50',
			'step'			=>			'1',
		),
		'active_callback'	=>			array(
			array(
			'setting'		=>			'menu_effect',
			'operator'		=>			'==',
			'value'			=>			'boxed',
			),
			array(
			'setting'		=>			'menu_effect_animation',
			'operator'		=>			'!=',
			'value'			=>			'slide',
			)
		)
	) );

	/* Fields – Footer */

	// Sticky
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'toggle',
		'settings'			=>			'footer_sticky',
		'label'				=>			esc_attr__( 'Sticky Footer', 'wpbfpremium' ),
		'section'			=>			'wpbf_footer_options',
		'default'			=>			'0',
		'priority'			=>			1,
		'active_callback'	=>			array(
			array(
			'setting'		=>			'page_boxed',
			'operator'		=>			'!=',
			'value'			=>			true,
			),
		)
	) );

	// Layout
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'radio-buttonset',
		'settings'			=>			'footer_layout',
		'label'				=>			esc_attr__( 'Footer', 'wpbfpremium' ),
		'section'			=>			'wpbf_footer_options',
		'default'			=>			'two',
		'priority'			=>			1,
		'choices'			=>			array(
			'none'			=>			esc_attr__( 'None', 'wpbfpremium' ),
			'one'			=>			esc_attr__( 'One Column', 'wpbfpremium' ),
			'two'			=>			esc_attr__( 'Two Columns', 'wpbfpremium' ),
		),
	) );

	// Column One
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'textarea',
		'settings'			=>			'footer_column_one',
		'label'				=>			esc_attr__( 'Column 1', 'wpbfpremium' ),
		'section'			=>			'wpbf_footer_options',
		'default'			=>			esc_html__( '&copy; [year] - [blogname] | All rights reserved', 'wpbfpremium' ),
		'priority'			=>			2,
		'active_callback'	=>			array(
			array(
			'setting'		=>			'footer_layout',
			'operator'		=>			'!=',
			'value'			=>			'none',
			),
		)
	) );

	// Column Two
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'textarea',
		'settings'			=>			'footer_column_two',
		'label'				=>			esc_attr__( 'Column 2', 'wpbfpremium' ),
		'section'			=>			'wpbf_footer_options',
		'default'			=>			__( 'Powered by [credit url="https://wp-pagebuilderframework.com" name="Page Builder Framework"]', 'wpbfpremium' ),
		'priority'			=>			3,
		'active_callback'	=>			array(
			array(
			'setting'		=>			'footer_layout',
			'operator'		=>			'==',
			'value'			=>			'two',
			),
		)
	) );

	if ( is_plugin_active( 'bb-plugin/fl-builder.php' ) || is_plugin_active( 'elementor-pro/elementor-pro.php' ) || is_plugin_active( 'divi-builder/divi-builder.php' ) ) {

		Kirki::add_field( 'wpbf', array(
			'type'				=>			'custom',
			'settings'			=>			'separator-41749',
			'section'			=>			'wpbf_footer_options',
			'default'			=>			'<hr style="border-top: 1px solid #ccc; border-bottom: 1px solid #f8f8f8">',
			'priority'			=>			9,
		) );

		// Custom Footer
		Kirki::add_field( 'wpbf', array(
			'type'				=>			'code',
			'label'				=>			esc_attr__( 'Custom Footer', 'wpbfpremium' ),
			'description'		=>			__( 'Paste your shortcode to populate a saved row/template throughout your website. <br><br>Examples:<br>[elementor-template id="xxx"]<br>[fl_builder_insert_layout id="xxx"]', 'wpbfpremium' ), //esc_html maybe
			'settings'			=>			'footer_custom',
			'section'			=>			'wpbf_footer_options',
			'priority'			=>			9,
		) );

	}

	/* Fields – Scripts & Styles */

	// Head
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'code',
		'settings'			=>			'head_scripts',
		'section'			=>			'wpbf_header_scripts',
		'label'				=>			esc_attr__( 'Head Code', 'wpbfpremium' ),
		'description'		=>			esc_attr__( 'Runs inside the head tag.', 'wpbfpremium' ),
		'priority'			=>			1,
	) );

	// Header
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'code',
		'settings'			=>			'header_scripts',
		'section'			=>			'wpbf_header_scripts',
		'label'				=>			esc_attr__( 'Header Code', 'wpbfpremium' ),
		'description'		=>			esc_attr__( 'Runs after the opening body tag.', 'wpbfpremium' ),
		'priority'			=>			2,
	) );

	// Footer
	Kirki::add_field( 'wpbf', array(
		'type'				=>			'code',
		'settings'			=>			'footer_scripts',
		'section'			=>			'wpbf_footer_scripts',
		'label'				=>			esc_attr__( 'Footer Code', 'wpbfpremium' ),
		'description'		=>			esc_attr__( 'Add Scripts (Google Analytics, etc.) here. Runs before the closing body tag (wp_footer).', 'wpbfpremium' ),
		'priority'			=>			1,
	) );


	/* Fields – WooCommerce */
	// if( class_exists( 'WooCommerce' ) ) {

	// 	// Menu Item
	// 	Kirki::add_field( 'wpbf', array(
	// 		'type'				=>			'select',
	// 		'settings'			=>			'woocommerce_menu_item',
	// 		'label'				=>			esc_attr__( 'WooCommerce Menu Item', 'wpbfpremium' ),
	// 		'description'		=>			__( 'Displays the cart amount & counter in the main menu.<br><br> <strong>Note: You need to clear your browser cache for the changes below to take affect.</strong>', 'wpbfpremium' ),
	// 		'section'			=>			'wpbf_woocommerce_menu_item_options',
	// 		'default'			=>			'show',
	// 		'priority'			=>			0,
	// 		'multiple'			=>			1,
	// 		'choices'			=>			array(
	// 			'show'			=>			esc_attr__( 'Show', 'wpbfpremium' ),
	// 			'hide'			=>			esc_attr__( 'Hide', 'wpbfpremium' ),
	// 		),
	// 	) );

	// 	// Menu Item Icon
	// 	Kirki::add_field( 'wpbf', array(
	// 		'type'				=>			'select',
	// 		'settings'			=>			'woocommerce_menu_item_icon',
	// 		'label'				=>			esc_attr__( 'Icon', 'wpbfpremium' ),
	// 		'section'			=>			'wpbf_woocommerce_menu_item_options',
	// 		'default'			=>			'cart',
	// 		'priority'			=>			0,
	// 		'multiple'			=>			1,
	// 		'choices'			=>			array(
	// 			'cart'			=>			esc_attr__( 'Cart', 'wpbfpremium' ),
	// 			'basket'		=>			esc_attr__( 'Basket', 'wpbfpremium' ),
	// 		),
	// 	) );

	// 	// Menu Item Text
	// 	Kirki::add_field( 'wpbf', array(
	// 		'type'				=>			'checkbox',
	// 		'settings'			=>			'woocommerce_menu_item_text',
	// 		'label'				=>			esc_attr__( 'Display Text', 'wpbfpremium' ),
	// 		'section'			=>			'wpbf_woocommerce_menu_item_options',
	// 		'priority'			=>			2,
	// 	) );

	// 	// Menu Item Amount
	// 	Kirki::add_field( 'wpbf', array(
	// 		'type'				=>			'checkbox',
	// 		'settings'			=>			'woocommerce_menu_item_amount',
	// 		'label'				=>			esc_attr__( 'Display Amount', 'wpbfpremium' ),
	// 		'section'			=>			'wpbf_woocommerce_menu_item_options',
	// 		'default'			=>			1,
	// 		'priority'			=>			3,
	// 	) );

	// 	// Menu Item Count
	// 	Kirki::add_field( 'wpbf', array(
	// 		'type'				=>			'checkbox',
	// 		'settings'			=>			'woocommerce_menu_item_count',
	// 		'label'				=>			esc_attr__( 'Hide Count', 'wpbfpremium' ),
	// 		'section'			=>			'wpbf_woocommerce_menu_item_options',
	// 		'priority'			=>			4,
	// 	) );

	// 	// Menu Item Dropdown
	// 	Kirki::add_field( 'wpbf', array(
	// 		'type'				=>			'checkbox',
	// 		'settings'			=>			'woocommerce_menu_item_dropdown',
	// 		'label'				=>			esc_attr__( 'Hide Dropdown', 'wpbfpremium' ),
	// 		'section'			=>			'wpbf_woocommerce_menu_item_options',
	// 		'priority'			=>			5,
	// 	) );

	// 	Kirki::add_field( 'wpbf', array(
	// 		'type'				=>			'custom',
	// 		'settings'			=>			'separator-50938',
	// 		'section'			=>			'wpbf_woocommerce_menu_item_options',
	// 		'default'			=>			'<hr style="border-top: 1px solid #ccc; border-bottom: 1px solid #f8f8f8">',
	// 		'priority'			=>			6,
	// 	) );

	// 	/* Shop Pages & Archives (Products) */

	// 	Kirki::add_field( 'wpbf', array(
	// 		'type'				=>			'custom',
	// 		'settings'			=>			'separator-56123',
	// 		'section'			=>			'woocommerce_product_catalog',
	// 		'default'			=>			'<hr style="border-top: 1px solid #ccc; border-bottom: 1px solid #f8f8f8">',
	// 		'priority'			=>			11,
	// 	) );

	// 	// Remove Page Title
	// 	Kirki::add_field( 'wpbf', array(
	// 		'type'				=>			'toggle',
	// 		'settings'			=>			'woocommerce_loop_show_page_title',
	// 		'label'				=>			esc_attr__( 'Show Page Title', 'wpbfpremium' ),
	// 		'section'			=>			'woocommerce_product_catalog',
	// 		'default'			=>			0,
	// 		'priority'			=>			11,
	// 	) );

	// 	// Remove Breadcrumbs
	// 	Kirki::add_field( 'wpbf', array(
	// 		'type'				=>			'toggle',
	// 		'settings'			=>			'woocommerce_loop_show_breadcrumbs',
	// 		'label'				=>			esc_attr__( 'Show Breadcrumbs', 'wpbfpremium' ),
	// 		'section'			=>			'woocommerce_product_catalog',
	// 		'default'			=>			0,
	// 		'priority'			=>			11,
	// 	) );

	// 	// Remove Result Count
	// 	Kirki::add_field( 'wpbf', array(
	// 		'type'				=>			'toggle',
	// 		'settings'			=>			'woocommerce_loop_remove_result_count',
	// 		'label'				=>			esc_attr__( 'Hide Result Count', 'wpbfpremium' ),
	// 		'section'			=>			'woocommerce_product_catalog',
	// 		'default'			=>			0,
	// 		'priority'			=>			11,
	// 	) );

	// 	// Remove Ordering
	// 	Kirki::add_field( 'wpbf', array(
	// 		'type'				=>			'toggle',
	// 		'settings'			=>			'woocommerce_loop_remove_ordering',
	// 		'label'				=>			esc_attr__( 'Hide Ordering', 'wpbfpremium' ),
	// 		'section'			=>			'woocommerce_product_catalog',
	// 		'default'			=>			0,
	// 		'priority'			=>			11,
	// 	) );

	// 	Kirki::add_field( 'wpbf', array(
	// 		'type'				=>			'custom',
	// 		'settings'			=>			'separator-72124',
	// 		'section'			=>			'woocommerce_product_catalog',
	// 		'default'			=>			'<hr style="border-top: 1px solid #ccc; border-bottom: 1px solid #f8f8f8">',
	// 		'priority'			=>			11,
	// 	) );

	// 	// Content Alignment
	// 	Kirki::add_field( 'wpbf', array(
	// 		'type'				=>			'radio-image',
	// 		'settings'			=>			'woocommerce_loop_content_alignment',
	// 		'label'				=>			esc_attr__( 'Content Alignment', 'wpbfpremium' ),
	// 		'section'			=>			'woocommerce_product_catalog',
	// 		'default'			=>			'left',
	// 		'priority'			=>			11,
	// 		'multiple'			=>			1,
	// 		'choices'			=>			array(
	// 			'left'			=>			WPBF_PREMIUM_URI . '/inc/customizer/img/align-left.jpg',
	// 			'center'		=>			WPBF_PREMIUM_URI . '/inc/customizer/img/align-center.jpg',
	// 			'right'			=>			WPBF_PREMIUM_URI . '/inc/customizer/img/align-right.jpg',
	// 		),
	// 	) );

	// 	// Remove Star Rating
	// 	Kirki::add_field( 'wpbf', array(
	// 		'type'				=>			'toggle',
	// 		'settings'			=>			'woocommerce_loop_remove_star_rating',
	// 		'label'				=>			esc_attr__( 'Hide Star Rating', 'wpbfpremium' ),
	// 		'section'			=>			'woocommerce_product_catalog',
	// 		'default'			=>			0,
	// 		'priority'			=>			12,
	// 	) );

	// 	// Remove Add to Cart Button
	// 	Kirki::add_field( 'wpbf', array(
	// 		'type'				=>			'toggle',
	// 		'settings'			=>			'woocommerce_loop_remove_button',
	// 		'label'				=>			esc_attr__( 'Hide "Add to cart" Button', 'wpbfpremium' ),
	// 		'section'			=>			'woocommerce_product_catalog',
	// 		'default'			=>			0,
	// 		'priority'			=>			13,
	// 	) );

	// 	Kirki::add_field( 'wpbf', array(
	// 		'type'				=>			'custom',
	// 		'settings'			=>			'separator-56377',
	// 		'section'			=>			'woocommerce_product_catalog',
	// 		'default'			=>			'<hr style="border-top: 1px solid #ccc; border-bottom: 1px solid #f8f8f8">',
	// 		'priority'			=>			14,
	// 	) );

	// 	// Sale Position
	// 	Kirki::add_field( 'wpbf', array(
	// 		'type'				=>			'select',
	// 		'settings'			=>			'woocommerce_loop_sale_position',
	// 		'label'				=>			esc_attr__( 'Sale Badge', 'wpbfpremium' ),
	// 		'section'			=>			'woocommerce_product_catalog',
	// 		'default'			=>			'outside',
	// 		'priority'			=>			15,
	// 		'multiple'			=>			1,
	// 		'choices'			=>			array(
	// 			'outside'		=>			esc_attr__( 'Outside', 'wpbfpremium' ),
	// 			'inside'		=>			esc_attr__( 'Inside', 'wpbfpremium' ),
	// 			'none'			=>			esc_attr__( 'Hide', 'wpbfpremium' ),
	// 		),
	// 	) );

	// 	// Sale Alignment
	// 	Kirki::add_field( 'wpbf', array(
	// 		'type'				=>			'radio-image',
	// 		'settings'			=>			'woocommerce_loop_sale_alignment',
	// 		'label'				=>			esc_attr__( 'Sale Badge Alignment', 'wpbfpremium' ),
	// 		'section'			=>			'woocommerce_product_catalog',
	// 		'default'			=>			'right',
	// 		'priority'			=>			16,
	// 		'multiple'			=>			1,
	// 		'choices'			=>			array(
	// 			'left'			=>			WPBF_PREMIUM_URI . '/inc/customizer/img/align-left.jpg',
	// 			'center'		=>			WPBF_PREMIUM_URI . '/inc/customizer/img/align-center.jpg',
	// 			'right'			=>			WPBF_PREMIUM_URI . '/inc/customizer/img/align-right.jpg',
	// 		),
	// 		'active_callback'	=>			array(
	// 			array(
	// 			'setting'		=>			'woocommerce_loop_sale_position',
	// 			'operator'		=>			'!=',
	// 			'value'			=>			'none',
	// 			),
	// 		)
	// 	) );

	// }

	/* Custom Controls */
	add_action( 'customize_register' , 'wpbf_custom_controls' );

	function wpbf_custom_controls( $wp_customize ) {

		// Responsive Font Sizes (Text)
		$wp_customize->add_setting( 'page_font_size_desktop',
			array(
				'default' => '16px'
			)
		); 

		$wp_customize->add_setting( 'page_font_size_tablet',
			array()
		); 

		$wp_customize->add_setting( 'page_font_size_mobile',
			array()
		); 

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_font_size', 
			array(
				'label'	=> esc_attr__( 'Desktop', 'wpbfpremium' ),
				'section' => 'wpbf_font_options',
				'settings' => 'page_font_size_desktop',
				'priority' => 2,
			) 
		));

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_font_size', 
			array(
				'label'	=> esc_attr__( 'Tablet', 'wpbfpremium' ),
				'section' => 'wpbf_font_options',
				'settings' => 'page_font_size_tablet',
				'priority' => 2,
			) 
		));

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_font_size', 
			array(
				'label'	=> esc_attr__( 'Mobile', 'wpbfpremium' ),
				'section' => 'wpbf_font_options',
				'settings' => 'page_font_size_mobile',
				'priority' => 2,
			) 
		));

		// Responsive Font Sizes (H1)
		$wp_customize->add_setting( 'page_h1_font_size_desktop',
			array(
				'default' => '32px'
			)
		); 

		$wp_customize->add_setting( 'page_h1_font_size_tablet',
			array()
		); 

		$wp_customize->add_setting( 'page_h1_font_size_mobile',
			array()
		); 

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_h1_font_size', 
			array(
				'label'	=> esc_attr__( 'Desktop', 'wpbfpremium' ),
				'section' => 'wpbf_h1_options',
				'settings' => 'page_h1_font_size_desktop',
				'priority' => 2,
			) 
		));

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_h1_font_size', 
			array(
				'label'	=> esc_attr__( 'Tablet', 'wpbfpremium' ),
				'section' => 'wpbf_h1_options',
				'settings' => 'page_h1_font_size_tablet',
				'priority' => 2,
			) 
		));

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_h1_font_size', 
			array(
				'label'	=> esc_attr__( 'Mobile', 'wpbfpremium' ),
				'section' => 'wpbf_h1_options',
				'settings' => 'page_h1_font_size_mobile',
				'priority' => 2,
			) 
		));

		// Responsive Font Sizes (H2)
		$wp_customize->add_setting( 'page_h2_font_size_desktop',
			array(
				'default' => '28px'
			)
		); 

		$wp_customize->add_setting( 'page_h2_font_size_tablet',
			array()
		); 

		$wp_customize->add_setting( 'page_h2_font_size_mobile',
			array()
		); 

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_h2_font_size', 
			array(
				'label'	=> esc_attr__( 'Desktop', 'wpbfpremium' ),
				'section' => 'wpbf_h2_options',
				'settings' => 'page_h2_font_size_desktop',
				'priority' => 2,
			) 
		));

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_h2_font_size', 
			array(
				'label'	=> esc_attr__( 'Tablet', 'wpbfpremium' ),
				'section' => 'wpbf_h2_options',
				'settings' => 'page_h2_font_size_tablet',
				'priority' => 2,
			) 
		));

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_h2_font_size', 
			array(
				'label'	=> esc_attr__( 'Mobile', 'wpbfpremium' ),
				'section' => 'wpbf_h2_options',
				'settings' => 'page_h2_font_size_mobile',
				'priority' => 2,
			) 
		));

		// Responsive Font Sizes (H3)
		$wp_customize->add_setting( 'page_h3_font_size_desktop',
			array(
				'default' => '24px'
			)
		); 

		$wp_customize->add_setting( 'page_h3_font_size_tablet',
			array()
		); 

		$wp_customize->add_setting( 'page_h3_font_size_mobile',
			array()
		); 

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_h3_font_size', 
			array(
				'label'	=> esc_attr__( 'Desktop', 'wpbfpremium' ),
				'section' => 'wpbf_h3_options',
				'settings' => 'page_h3_font_size_desktop',
				'priority' => 2,
			) 
		));

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_h3_font_size', 
			array(
				'label'	=> esc_attr__( 'Tablet', 'wpbfpremium' ),
				'section' => 'wpbf_h3_options',
				'settings' => 'page_h3_font_size_tablet',
				'priority' => 2,
			) 
		));

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_h3_font_size', 
			array(
				'label'	=> esc_attr__( 'Mobile', 'wpbfpremium' ),
				'section' => 'wpbf_h3_options',
				'settings' => 'page_h3_font_size_mobile',
				'priority' => 2,
			) 
		));

		// Responsive Font Sizes (H4)
		$wp_customize->add_setting( 'page_h4_font_size_desktop',
			array(
				'default' => '20px'
			)
		); 

		$wp_customize->add_setting( 'page_h4_font_size_tablet',
			array()
		); 

		$wp_customize->add_setting( 'page_h4_font_size_mobile',
			array()
		); 

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_h4_font_size', 
			array(
				'label'	=> esc_attr__( 'Desktop', 'wpbfpremium' ),
				'section' => 'wpbf_h4_options',
				'settings' => 'page_h4_font_size_desktop',
				'priority' => 2,
			) 
		));

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_h4_font_size', 
			array(
				'label'	=> esc_attr__( 'Tablet', 'wpbfpremium' ),
				'section' => 'wpbf_h4_options',
				'settings' => 'page_h4_font_size_tablet',
				'priority' => 2,
			) 
		));

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_h4_font_size', 
			array(
				'label'	=> esc_attr__( 'Mobile', 'wpbfpremium' ),
				'section' => 'wpbf_h4_options',
				'settings' => 'page_h4_font_size_mobile',
				'priority' => 2,
			) 
		));

		// Responsive Font Sizes (H5)
		$wp_customize->add_setting( 'page_h5_font_size_desktop',
			array(
				'default' => '16px'
			)
		); 

		$wp_customize->add_setting( 'page_h5_font_size_tablet',
			array()
		); 

		$wp_customize->add_setting( 'page_h5_font_size_mobile',
			array()
		); 

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_h5_font_size', 
			array(
				'label'	=> esc_attr__( 'Desktop', 'wpbfpremium' ),
				'section' => 'wpbf_h5_options',
				'settings' => 'page_h5_font_size_desktop',
				'priority' => 2,
			) 
		));

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_h5_font_size', 
			array(
				'label'	=> esc_attr__( 'Tablet', 'wpbfpremium' ),
				'section' => 'wpbf_h5_options',
				'settings' => 'page_h5_font_size_tablet',
				'priority' => 2,
			) 
		));

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_h5_font_size', 
			array(
				'label'	=> esc_attr__( 'Mobile', 'wpbfpremium' ),
				'section' => 'wpbf_h5_options',
				'settings' => 'page_h5_font_size_mobile',
				'priority' => 2,
			) 
		));

		// Responsive Font Sizes (H6)
		$wp_customize->add_setting( 'page_h6_font_size_desktop',
			array(
				'default' => '16px'
			)
		); 

		$wp_customize->add_setting( 'page_h6_font_size_tablet',
			array()
		); 

		$wp_customize->add_setting( 'page_h6_font_size_mobile',
			array()
		); 

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_h6_font_size', 
			array(
				'label'	=> esc_attr__( 'Desktop', 'wpbfpremium' ),
				'section' => 'wpbf_h6_options',
				'settings' => 'page_h6_font_size_desktop',
				'priority' => 2,
			) 
		));

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_h6_font_size', 
			array(
				'label'	=> esc_attr__( 'Tablet', 'wpbfpremium' ),
				'section' => 'wpbf_h6_options',
				'settings' => 'page_h6_font_size_tablet',
				'priority' => 2,
			) 
		));

		$wp_customize->add_control( new WPBF_Customize_Font_Size_Control( 
			$wp_customize, 
			'page_h6_font_size', 
			array(
				'label'	=> esc_attr__( 'Mobile', 'wpbfpremium' ),
				'section' => 'wpbf_h6_options',
				'settings' => 'page_h6_font_size_mobile',
				'priority' => 2,
			) 
		));

	}

}