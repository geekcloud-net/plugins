<?php
// Porto Ultimate Content Box

add_shortcode('porto_ultimate_content_box', 'porto_shortcode_ultimate_content_box');
add_action('vc_after_init', 'porto_load_ultimate_content_box_shortcode');

function porto_shortcode_ultimate_content_box( $atts, $content = null ) {

    ob_start();
    if  ($template = porto_shortcode_template( 'porto_ultimate_content_box' ) ) {
        include $template;
    }
    return ob_get_clean();
}

function porto_load_ultimate_content_box_shortcode() {

    $custom_class = porto_vc_custom_class();

    vc_map( array(
        "name" => __("Porto Content Box", "porto-shortcodes"),
        "base" => "porto_ultimate_content_box",
        "icon" => "porto4_vc_ultimate_content_box",
        "class" => "porto_ultimate_content_box",
        "as_parent" => array('except' => 'porto_ultimate_content_box'),
        "controls" => "full",
        "show_settings_on_create" => true,
        "category" => __("Porto", 'porto-shortcodes'),
        "description" => __("Content Box.","porto-shortcodes"),
        "js_view" => 'VcColumnView',
        "params" => array(
            array(
                "type" => "dropdown",
                "heading" => __("Background Type","porto-shortcodes"),
                "param_name" => "bg_type",
                "value" => array(
                    __("Background Color","porto-shortcodes") => "bg_color",
                    __("Background Image","porto-shortcodes") => "bg_image",
                ),
            ),
            array(
                "type" => "colorpicker",
                "heading" => __("Background Color","porto-shortcodes"),
                "param_name" => "bg_clr",
                "dependency" => Array("element" => "bg_type", "value" => "bg_color" ),
            ),
            array(
                "type" => "attach_image",
                "heading" => __("Background Image", 'porto-shortcodes'),
                "param_name" => "bg_img",
                "description" => __("Set background image for content box.", 'porto-shortcodes'),
                "dependency" => Array("element" => "bg_type", "value" => "bg_image" ),
            ),
            array(
                "type" => "number",
                "heading" => __("Border Width","porto-shortcodes"),
                "param_name" => "border",
                "value" => '',
                "min" => 1,
                "max" => 10,
                "suffix" => "px",
            ),
            array(
                "type" => "porto_boxshadow",
                "heading" => __("Box Shadow", "porto-shortcodes"),
                "param_name" => "box_shadow",
                "unit" => "px",
                "positions" => array(
                    __("Horizontal","porto-shortcodes")     => "",
                    __("Vertical","porto-shortcodes")   => "",
                    __("Blur","porto-shortcodes")  => "",
                    __("Spread","porto-shortcodes")    => ""
                ),
            ),
            array(
                "type" => "vc_link",
                "heading" => __("Content Box Link","porto-shortcodes"),
                "param_name" => "link",
            ),
            array(
                "type" => "number",
                "heading" => __("Min Height", "porto-shortcodes"),
                "param_name" => "min_height",
                "suffix"=>"px",
                "min"=>"0",
            ),
            $custom_class,

            //  Background
            array(
                "type" => "dropdown",
                "heading" => __("Background Image Repeat","porto-shortcodes"),
                "param_name" => "bg_repeat",
                "value" => array(
                    __("Repeat", "porto-shortcodes") => 'repeat',
                    __("Repeat X", "porto-shortcodes") => 'repeat-x',
                    __("Repeat Y", "porto-shortcodes") => 'repeat-y',
                    __("No Repeat", "porto-shortcodes") => 'no-repeat',
                ),
                "group" => "Background",
                "dependency" => Array("element" => "bg_type", "value" => "bg_image" ),
            ),
            array(
                "type" => "dropdown",
                "heading" => __("Background Image Size","porto-shortcodes"),
                "param_name" => "bg_size",
                "value" => array(
                    __("Cover - Image to be as large as possible", "porto-shortcodes") => 'cover',
                    __("Contain - Image will try to fit inside the container area", "porto-shortcodes") => 'contain',
                    __("Initial", "porto-shortcodes") => 'initial',
                ),
                "group" => "Background",
                "dependency" => Array("element" => "bg_type", "value" => "bg_image" ),
            ),
            array(
                "type" => "textfield",
                "heading" => __("Background Image Position", "porto-shortcodes"),
                "param_name" => "bg_position",
                "description" => __("You can use any number with px, em, %, etc. Example- 100px 100px.", "porto-shortcodes"),
                "group" => "Background",
                "dependency" => Array("element" => "bg_type", "value" => "bg_image" ),
            ),

            //  Hover
            array(
                "type" => "porto_boxshadow",
                "heading" => __("Box Shadow", "porto-shortcodes"),
                "param_name" => "hover_box_shadow",
                "unit"     => "px",
                "positions" => array(
                    __("Horizontal","porto-shortcodes")     => "",
                    __("Vertical","porto-shortcodes")   => "",
                    __("Blur","porto-shortcodes")  => "",
                    __("Spread","porto-shortcodes")    => ""
                ),
                "label_color"   => __("Shadow Color","porto-shortcodes"),
                "group" => "Hover",
            ),
            array(
                'type' => 'css_editor',
                'heading' => __( 'Css', 'porto-shortcodes' ),
                'param_name' => 'css_contentbox',
                'group' => __( 'Design', 'porto-shortcodes' ),
                'edit_field_class' => 'vc_col-sm-12 vc_column no-vc-background no-vc-border creative_link_css_editor',
            ),
        ),
    ) );

    if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
        class WPBakeryShortCode_porto_ultimate_content_box extends WPBakeryShortCodesContainer {
        }
    }
}