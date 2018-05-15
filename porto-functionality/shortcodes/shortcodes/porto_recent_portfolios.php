<?php

// Porto Recent Portfolios
add_shortcode('porto_recent_portfolios', 'porto_shortcode_recent_portfolios');
add_action('vc_after_init', 'porto_load_recent_portfolios_shortcode');

function porto_shortcode_recent_portfolios($atts, $content = null) {
    ob_start();
    if ($template = porto_shortcode_template('porto_recent_portfolios'))
        include $template;
    return ob_get_clean();
}

function porto_load_recent_portfolios_shortcode() {
    $animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();

    vc_map( array(
        'name' => "Porto " . __('Recent Portfolios', 'porto-shortcodes'),
        'base' => 'porto_recent_portfolios',
        'category' => __('Porto', 'porto-shortcodes'),
        'icon' => 'porto_vc_recent_portfolios',
        "params" => array(
            array(
                "type" => "textfield",
                "heading" => __("Title", 'porto-shortcodes'),
                "param_name" => "title",
                "admin_label" => true
            ),
            array(
                "type" => "dropdown",
                "heading" => __("View Type", 'porto-shortcodes'),
                "param_name" => "view",
                'std' => 'classic',
                "value" => porto_sh_commons('portfolio_grid_view')
            ),
            array(
                "type" => "dropdown",
                "heading" => __("Info View Type", 'porto-shortcodes'),
                "param_name" => "info_view",
                'std' => '',
                "value" => array(
                    __('Standard', 'porto-shortcodes' ) => '',
                    __('Left Info', 'porto-shortcodes' ) => 'left-info',
                    __('Centered Info', 'porto-shortcodes' ) => 'centered-info',
                    __('Bottom Info', 'porto-shortcodes' ) => 'bottom-info',
                    __('Bottom Info Dark', 'porto-shortcodes' ) => 'bottom-info-dark',
                    __('Hide Info Hover', 'porto-shortcodes' ) => 'hide-info-hover'
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
                    __('No Zoom', 'porto-shortcodes' ) => 'no-zoom'
                ),
            ),
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
            array(
                "type" => "textfield",
                "heading" => __("Portfolios Count", 'porto-shortcodes'),
                "param_name" => "number",
                "value" => "8",
                "admin_label" => true
            ),
            array(
                "type" => "textfield",
                "heading" => __("Category IDs", 'porto-shortcodes'),
                "param_name" => "cats",
                "admin_label" => true
            ),
            array(
                "type" => "textfield",
                "heading" => __("Items to show on Desktop", 'porto-shortcodes'),
                "param_name" => "items_desktop",
                "value" => "4"
            ),
            array(
                "type" => "textfield",
                "heading" => __("Items to show on Tablets", 'porto-shortcodes'),
                "param_name" => "items_tablets",
                "value" => "3"
            ),
            array(
                "type" => "textfield",
                "heading" => __("Items to show on Mobile", 'porto-shortcodes'),
                "param_name" => "items_mobile",
                "value" => "2"
            ),
            array(
                "type" => "textfield",
                "heading" => __("Items Row", 'porto-shortcodes'),
                "param_name" => "items_row",
                "value" => "1"
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Change Slider Config", 'porto-shortcodes'),
                'param_name' => 'slider_config',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                "group" => __('Slider Options', 'porto-shortcodes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Show Nav", 'porto-shortcodes'),
                'param_name' => 'show_nav',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                'dependency' => array('element' => 'slider_config', 'not_empty' => true),
                "group" => __('Slider Options', 'porto-shortcodes')
            ),
            array(
                'type' => 'dropdown',
                'heading' => __("Nav Position", 'porto-shortcodes'),
                'param_name' => 'nav_pos',
                'value' => array(
                    __( 'Middle', 'porto-shortcodes' ) => '',
                    __( 'Top', 'porto-shortcodes' ) => 'show-nav-title',
                    __( 'Bottom', 'porto-shortcodes' ) => 'nav-bottom'
                ),
                'dependency' => array('element' => 'show_nav', 'not_empty' => true),
                "group" => __('Slider Options', 'porto-shortcodes')
            ),
            array(
                'type' => 'dropdown',
                'heading' => __("Nav Type", 'porto-shortcodes'),
                'param_name' => 'nav_type',
                'value' => array(
                    __( 'Default', 'porto-shortcodes' ) => '',
                    __( 'Rounded', 'porto-shortcodes' ) => 'rounded-nav',
                    __( 'Big & Full Width', 'porto-shortcodes' ) => 'big-nav'
                ),
                'dependency' => array('element' => 'nav_pos', 'value' => array('', 'nav-bottom')),
                "group" => __('Slider Options', 'porto-shortcodes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Show Nav on Hover", 'porto-shortcodes'),
                'param_name' => 'show_nav_hover',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                'dependency' => array('element' => 'show_nav', 'not_empty' => true),
                "group" => __('Slider Options', 'porto-shortcodes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Show Dots", 'porto-shortcodes'),
                'param_name' => 'show_dots',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                'dependency' => array('element' => 'slider_config', 'not_empty' => true),
                "group" => __('Slider Options', 'porto-shortcodes')
            ),
            $custom_class,
            $animation_type,
            $animation_duration,
            $animation_delay
        )
    ) );

    if (!class_exists('WPBakeryShortCode_Porto_Recent_Portfolios')) {
        class WPBakeryShortCode_Porto_Recent_Portfolios extends WPBakeryShortCode {
        }
    }
}