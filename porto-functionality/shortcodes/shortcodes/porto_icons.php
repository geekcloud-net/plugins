<?php
// Porto Icons

add_shortcode('porto_icons', 'porto_shortcode_icons');
add_action('vc_after_init', 'porto_load_icons_shortcode');

function porto_shortcode_icons( $atts, $content = null ) {

    ob_start();
    if  ($template = porto_shortcode_template( 'porto_icons' ) ) {
        include $template;
    }
    return ob_get_clean();
}

function porto_load_icons_shortcode() {

    $animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();

    vc_map(
        array(
            "name" => __("Porto Icons","porto-shortcodes"),
            "base" => "porto_icons",
            "class" => "porto_icons",
            "icon" => "porto4_vc_icons",
            "category" => __( 'Porto', 'porto-shortcodes' ),
            "description" => __("Add a set of multiple icons and give some custom style.","porto-shortcodes"),
            "as_parent" => array('only' => 'porto_single_icon'), 
            "content_element" => true,
            "show_settings_on_create" => true,
            "js_view" => 'VcColumnView',
            "params" => array(
                array(
                    "type" => "dropdown",
                    "class" => "",
                    "heading" => __("Alignment","porto-shortcodes"),
                    "param_name" => "align",
                    "value" => array(
                        __("Left Align","porto-shortcodes") => "porto-icons-left",
                        __("Right Align","porto-shortcodes") => "porto-icons-right",
                        __("Center Align","porto-shortcodes") => "porto-icons-center"
                    ),
                ),
                $custom_class,
                array(
                    'type' => 'css_editor',
                    'heading' => __( 'Css', 'porto-shortcodes' ),
                    'param_name' => 'css_icon',
                    'group' => __( 'Design', 'porto-shortcodes' ),
                    'edit_field_class' => 'vc_col-sm-12 vc_column no-vc-background no-vc-border creative_link_css_editor',
                ),
            )
        )
    );

    if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
        class WPBakeryShortCode_porto_icons extends WPBakeryShortCodesContainer {
        }
    }
}