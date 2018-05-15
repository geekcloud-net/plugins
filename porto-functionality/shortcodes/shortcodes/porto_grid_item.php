<?php

// Porto Masonry Item
add_shortcode('porto_grid_item', 'porto_shortcode_grid_item');
add_action('vc_after_init', 'porto_load_grid_item_shortcode');

function porto_shortcode_grid_item($atts, $content = null) {
    ob_start();
    if ($template = porto_shortcode_template('porto_grid_item'))
        include $template;
    return ob_get_clean();
}

function porto_load_grid_item_shortcode() {
    $custom_class = porto_vc_custom_class();

    vc_map( array(
        "name" => "Porto " . __("Masonry Item", 'porto-shortcodes'),
        "base" => "porto_grid_item",
        "category" => __("Porto", 'porto-shortcodes'),
        "icon" => "porto_vc_grid_item",
        "as_parent" => array('except' => 'porto_grid_item'),
        "as_child" => array('only' => 'porto_grid_container'),
        "content_element" => true,
        "controls" => "full",
        //'is_container' => true,
        'js_view' => 'VcColumnView',        'class' => 'vc_col-sm-12 vc_column',
        "params" => array(            array(                "type" => "textfield",                "heading" => __("Width", "porto-shortcodes"),                "param_name" => "width",            ),            $custom_class        )    ) );

    if (!class_exists('WPBakeryShortCode_Porto_Grid_Item')) {
        class WPBakeryShortCode_Porto_Grid_Item extends WPBakeryShortCodesContainer {
        }
    }
}