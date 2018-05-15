<?php

// Porto Masonry Container
add_shortcode('porto_grid_container', 'porto_shortcode_grid_container');
add_action('vc_after_init', 'porto_load_grid_container_shortcode');

function porto_shortcode_grid_container($atts, $content = null) {
    ob_start();
    if ($template = porto_shortcode_template('porto_grid_container'))
        include $template;
    return ob_get_clean();
}

function porto_load_grid_container_shortcode() {
    $animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();

    vc_map( array(
        "name" => "Porto " . __("Masonry Container", 'porto-shortcodes'),
        "base" => "porto_grid_container",
        "category" => __("Porto", 'porto-shortcodes'),
        "icon" => "porto_vc_grid_container",
        "as_parent" => array('only' => 'porto_grid_item'),
        "content_element" => true,
        "controls" => "full",
        //'is_container' => true,
        "js_view" => 'VcColumnView',
        "params" => array(
            array(
                "type" => "textfield",
                "heading" => __("Gutter Size", 'porto-shortcodes'),
                "param_name" => "gutter_size",
                "value" => "2%"
            ),
            array(
                "type" => "textfield",
                "heading" => __("Max Width", 'porto-shortcodes'),
                "param_name" => "max_width",
                "description" => __("Will be show as grid only when window width > max width.", 'porto-shortcodes'),
                "value" => "767px"
            ),
            $custom_class,
            $animation_type,
            $animation_duration,
            $animation_delay
        )
    ) );

    if (!class_exists('WPBakeryShortCode_Porto_Grid_Container')) {
        class WPBakeryShortCode_Porto_Grid_Container extends WPBakeryShortCodesContainer {
        }
    }
}