<?php

// Porto Carousel
add_shortcode('porto_carousel', 'porto_shortcode_carousel');
add_action('vc_after_init', 'porto_load_carousel_shortcode');

function porto_shortcode_carousel($atts, $content = null) {
    ob_start();
    if ($template = porto_shortcode_template('porto_carousel'))
        include $template;
    return ob_get_clean();
}

function porto_load_carousel_shortcode() {
    $animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();

    vc_map( array(
        "name" => "Porto " . __("Carousel", 'porto-shortcodes'),
        "base" => "porto_carousel",
        "category" => __("Porto", 'porto-shortcodes'),
        "icon" => "porto_vc_carousel",
        "as_parent" => array('except' => 'porto_carousel'),
        "content_element" => true,
        "controls" => "full",
        //'is_container' => true,
        "js_view" => 'VcColumnView',
        "params" => array(
            array(
                'type' => 'textfield',
                'heading' => __("Stage Padding", 'porto-shortcodes'),
                'param_name' => 'stage_padding',
                'value' => 40
            ),
            array(
                'type' => 'textfield',
                'heading' => __("Item Margin", 'porto-shortcodes'),
                'param_name' => 'margin',
                'value' => 10
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Auto Play", 'porto-shortcodes'),
                'param_name' => 'autoplay',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' )
            ),
            array(
                'type' => 'textfield',
                'heading' => __("Auto Play Timeout", 'porto-shortcodes'),
                'param_name' => 'autoplay_timeout',
                'dependency' => array('element' => 'autoplay', 'not_empty' => true),
                'value' => 5000
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Pause on Mouse Hover", 'porto-shortcodes'),
                'param_name' => 'autoplay_hover_pause',
                'dependency' => array('element' => 'autoplay', 'not_empty' => true),
            ),
            array(
                'type' => 'textfield',
                'heading' => __("Items", 'porto-shortcodes'),
                'param_name' => 'items',
                'value' => 6
            ),
            array(
                'type' => 'textfield',
                'heading' => __( 'Items on Desktop', 'porto-shortcodes' ),
                'param_name' => 'items_lg',
                'value' => 4
            ),
            array(
                'type' => 'textfield',
                'heading' => __( 'Items on Tablet', 'porto-shortcodes' ),
                'param_name' => 'items_md',
                'value' => 3
            ),
            array(
                'type' => 'textfield',
                'heading' => __( 'Items on Mobile', 'porto-shortcodes' ),
                'param_name' => 'items_sm',
                'value' => 2
            ),
            array(
                'type' => 'textfield',
                'heading' => __( 'Items on Mini', 'porto-shortcodes' ),
                'param_name' => 'items_xs',
                'value' => 1
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Show Nav", 'porto-shortcodes'),
                'param_name' => 'show_nav',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                "group" => __('Navigation', 'porto-shortcodes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Show Nav on Hover", 'porto-shortcodes'),
                'param_name' => 'show_nav_hover',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                'dependency' => array('element' => 'show_nav', 'not_empty' => true),
                "group" => __('Navigation', 'porto-shortcodes')
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
                "group" => __('Navigation', 'porto-shortcodes')
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
                "group" => __('Navigation', 'porto-shortcodes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Show Dots", 'porto-shortcodes'),
                'param_name' => 'show_dots',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                "group" => __('Navigation', 'porto-shortcodes')
            ),
            array(
                'type' => 'dropdown',
                'heading' => __("Dots Position", 'porto-shortcodes'),
                'param_name' => 'dots_pos',
                'value' => array(
                    __( 'Outside', 'porto-shortcodes' ) => '',
                    __( 'Inside', 'porto-shortcodes' ) => 'nav-inside'
                ),
                'dependency' => array('element' => 'show_dots', 'not_empty' => true),
                "group" => __('Navigation', 'porto-shortcodes')
            ),
            array(
                'type' => 'dropdown',
                'heading' => __("Dots Align", 'porto-shortcodes'),
                'param_name' => 'dots_align',
                'value' => array(
                    __( 'Right', 'porto-shortcodes' ) => '',
                    __( 'Center', 'porto-shortcodes' ) => 'nav-inside-center',
                    __( 'Left', 'porto-shortcodes' ) => 'nav-inside-left'
                ),
                'dependency' => array('element' => 'dots_pos', 'value' => array('nav-inside')),
                "group" => __('Navigation', 'porto-shortcodes')
            ),
            array(
                'type' => 'porto_animation_type',
                'heading' => __("Item Animation In", 'porto-shortcodes'),
                'param_name' => 'animate_in',
                "group" => __('Animation', 'porto-shortcodes')
            ),
            array(
                'type' => 'porto_animation_type',
                'heading' => __("Item Animation Out", 'porto-shortcodes'),
                'param_name' => 'animate_out',
                "group" => __('Animation', 'porto-shortcodes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Infinity loop", 'porto-shortcodes'),
                'param_name' => 'loop',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                "group" => __('Advanced', 'porto-shortcodes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Full Screen", 'porto-shortcodes'),
                'param_name' => 'fullscreen',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                "group" => __('Advanced', 'porto-shortcodes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Center Item", 'porto-shortcodes'),
                'param_name' => 'center',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                "group" => __('Advanced', 'porto-shortcodes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Fetch Videos", 'porto-shortcodes'),
                'param_name' => 'video',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                "description" => __('Please edit video items using porto carousel item element.', 'porto-shortcodes'),
                "group" => __('Advanced', 'porto-shortcodes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Lazy Load", 'porto-shortcodes'),
                'param_name' => 'lazyload',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                "description" => __('Please edit lazy load images using porto carousel item element.', 'porto-shortcodes'),
                "group" => __('Advanced', 'porto-shortcodes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Merge Items", 'porto-shortcodes'),
                'param_name' => 'merge',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                "description" => __('Please edit merge items using porto carousel item element.', 'porto-shortcodes'),
                "group" => __('Advanced', 'porto-shortcodes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Merge Fit", 'porto-shortcodes'),
                'param_name' => 'mergeFit',
                'std' => 'yes',
                'dependency' => array('element' => 'merge', 'not_empty' => true),
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                "group" => __('Advanced', 'porto-shortcodes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Merge Fit on Desktop", 'porto-shortcodes'),
                'param_name' => 'mergeFit_lg',
                'std' => 'yes',
                'dependency' => array('element' => 'merge', 'not_empty' => true),
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                "group" => __('Advanced', 'porto-shortcodes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Merge Fit on Tablet", 'porto-shortcodes'),
                'param_name' => 'mergeFit_md',
                'std' => 'yes',
                'dependency' => array('element' => 'merge', 'not_empty' => true),
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                "group" => __('Advanced', 'porto-shortcodes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Merge Fit on Mobile", 'porto-shortcodes'),
                'param_name' => 'mergeFit_sm',
                'std' => 'yes',
                'dependency' => array('element' => 'merge', 'not_empty' => true),
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                "group" => __('Advanced', 'porto-shortcodes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Merge Fit on Mini", 'porto-shortcodes'),
                'param_name' => 'mergeFit_xs',
                'std' => 'yes',
                'dependency' => array('element' => 'merge', 'not_empty' => true),
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                "group" => __('Advanced', 'porto-shortcodes')
            ),
            $custom_class,
            $animation_type,
            $animation_duration,
            $animation_delay
        )
    ) );

    if (!class_exists('WPBakeryShortCode_Porto_Carousel')) {
        class WPBakeryShortCode_Porto_Carousel extends WPBakeryShortCodesContainer {
        }
    }
}