<?php
// Porto Portfolios
add_shortcode('porto_portfolios', 'porto_shortcode_portfolios');
add_action('vc_after_init', 'porto_load_portfolios_shortcode');
function porto_shortcode_portfolios($atts, $content = null) {
    ob_start();
    if ($template = porto_shortcode_template('porto_portfolios'))
        include $template;
    return ob_get_clean();
}
function porto_load_portfolios_shortcode() {
    $animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();
    vc_map( array(
        'name' => "Porto " . __('Portfolios', 'porto-shortcodes'),
        'base' => 'porto_portfolios',
        'category' => __('Porto', 'porto-shortcodes'),
        'icon' => 'porto_vc_portfolios',
        "params" => array(
            array(
                "type" => "textfield",
                "heading" => __("Title", 'porto-shortcodes'),
                "param_name" => "title",
                "admin_label" => true
            ),
            array(
                "type" => "dropdown",
                "heading" => __("Portfolio Layout", 'porto-shortcodes'),
                "param_name" => "portfolio_layout",
                'std' => 'timeline',
                "value" => porto_sh_commons('portfolio_layout'),
                "admin_label" => true
            ),
            array(
                "type" => "dropdown",
                "heading" => __("Columns", 'porto-shortcodes'),
                "param_name" => "columns",
                'dependency' => Array('element' => 'portfolio_layout', 'value' => array( 'grid', 'masonry' )),
                'std' => '3',
                "value" => porto_sh_commons('portfolio_grid_columns')
            ),
            array(
                "type" => "dropdown",
                "heading" => __("View Type", 'porto-shortcodes'),
                "param_name" => "view",
                'dependency' => Array('element' => 'portfolio_layout', 'value' => array( 'grid', 'masonry' )),
                'std' => 'classic',
                "value" => porto_sh_commons('portfolio_grid_view')
            ),
            array(
                "type" => "dropdown",
                "heading" => __("Info View Type", 'porto-shortcodes'),
                "param_name" => "info_view",
                'dependency' => Array('element' => 'portfolio_layout', 'value' => array( 'grid', 'masonry', 'timeline' )),
                'std' => '',
                "value" => array(
                    __('Standard', 'porto-shortcodes' ) => '',
                    __('Left Info', 'porto-shortcodes' ) => 'left-info',
                    __('Centered Info', 'porto-shortcodes' ) => 'centered-info',
                    __('Bottom Info', 'porto-shortcodes' ) => 'bottom-info',
                    __('Bottom Info Dark', 'porto-shortcodes' ) => 'bottom-info-dark',
                    __('Hide Info Hover', 'porto-shortcodes' ) => 'hide-info-hover',
					__('Plus Icon', 'porto-shortcodes' ) => 'plus-icon',
                ),
            ),
			 array(
                "type" => "dropdown",
                "heading" => __("Info View Type Style", 'porto-shortcodes'),
                "param_name" => "info_view_type_style",
                'dependency' => Array('element' => 'portfolio_layout', 'value' => array( 'grid', 'masonry', 'timeline' )),
                'std' => '',
                "value" => array(
                    __('Standard', 'porto-shortcodes' ) => '',
					__('Alternate', 'porto-shortcodes' ) => 'alternate-info',
					__('Alternate with Plus', 'porto-shortcodes' ) => 'alternate-with-plus',
					__('No Style', 'porto-shortcodes' ) => 'no-style',
                ),
            ),
            array(
                "type" => "dropdown",
                "heading" => __("Image Overlay Background", 'porto-shortcodes'),
                "param_name" => "thumb_bg",
                'std' => '',
                "value" => array(
                    __('Standard', 'porto-shortcodes' ) => '',
                    __('Darken', 'porto-shortcodes' ) => 'darken',
                    __('Lighten', 'porto-shortcodes' ) => 'lighten',
                    __('Transparent', 'porto-shortcodes' ) => 'hide-wrapper-bg'
                ),
            ),
            array(
                "type" => "dropdown",
                "heading" => __("Hover Image Effect", 'porto-shortcodes'),
                "param_name" => "thumb_image",
                'std' => '',
                "value" => array(
                    __('Standard', 'porto-shortcodes' ) => '',
                    __('Zoom', 'porto-shortcodes' ) => 'zoom',
					__('Slow Zoom', 'porto-shortcodes' ) => 'slow-zoom',
                    __('No Zoom', 'porto-shortcodes' ) => 'no-zoom'
                ),
            ),
			array(
                "type" => "dropdown",
                "heading" => __("Image Counter", 'porto-shortcodes'),
                "param_name" => "image_counter",
				'dependency' => Array('element' => 'portfolio_layout', 'value' => array( 'grid', 'masonry', 'timeline')),
                'std' => '',
                "value" => array(
                    __('Default', 'porto-shortcodes' ) => '',
                    __('Show', 'porto-shortcodes' ) => 'show',
                    __('Hide', 'porto-shortcodes' ) => 'hide'
                ),
            ),
            array(
                "type" => "textfield",
                "heading" => __("Category IDs", 'porto-shortcodes'),
                "description" => __("comma separated list of category ids", 'porto-shortcodes'),
                "param_name" => "cats",
                "admin_label" => true
            ),
            array(
                "type" => "textfield",
                "heading" => __("Portfolio IDs", 'porto-shortcodes'),
                "description" => __("comma separated list of portfolio ids", 'porto-shortcodes'),
                "param_name" => "post_in"
            ),
			array(
                "type" => "textfield",
                "heading" => __("Slider on Portfolio", 'porto-shortcodes'),
				"description" => __("comma separated list of portfolio ids. <br /> Will Only work with ajax on page settings", 'porto-shortcodes'),
                "param_name" => "slider"
            ),
            array(
                "type" => "textfield",
                "heading" => __("Portfolios Count", 'porto-shortcodes'),
                "param_name" => "number",
                "value" => '8'
            ),
			array(
                "type" => "dropdown",
                "heading" => __("Load More Posts", 'porto-shortcodes'),
                "param_name" => "load_more_posts",
				'std' => '',
                "value" => array(
                    __('Select', 'porto-shortcodes' ) => '',
                    __('Pagination', 'porto-shortcodes' ) => 'pagination',
                    __('Load More (Button)', 'porto-shortcodes' ) => 'load-more-btn'
                ),
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Show Archive Link", 'porto-shortcodes'),
                'param_name' => 'view_more',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' )
            ),
            array(
                'type' => 'textfield',
                'heading' => __("Extra class name for Archive Link", 'porto-shortcodes'),
                'param_name' => 'view_more_class',
                'dependency' => array('element' => 'view_more', 'not_empty' => true),
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Show Filter", 'porto-shortcodes'),
                'param_name' => 'filter',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' )
            ),
            /*array(
                'type' => 'checkbox',
                'heading' => __("Show Pagination", 'porto-shortcodes'),
                'param_name' => 'pagination',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' )
            ),*/
			
            array(
                'type' => 'checkbox',
                'heading' => __("Enable Ajax Load", 'porto-shortcodes'),
                'param_name' => 'ajax_load',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' )
            ),
			
            array(
                'type' => 'checkbox',
                'heading' => __("Ajax Load on Modal", 'porto-shortcodes'),
                'param_name' => 'ajax_modal',
                'dependency' => array('element' => 'ajax_load', 'not_empty' => true),
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' )
            ),
			
            $custom_class,
            $animation_type,
            $animation_duration,
            $animation_delay
        )
    ) );
    if (!class_exists('WPBakeryShortCode_Porto_Portfolios')) {
        class WPBakeryShortCode_Porto_Portfolios extends WPBakeryShortCode {
        }
    }
}