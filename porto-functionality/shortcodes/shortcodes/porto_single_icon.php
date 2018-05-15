<?php
// Porto Single Icon

add_shortcode('porto_single_icon', 'porto_shortcode_single_icon');
add_action('vc_after_init', 'porto_load_single_icon_shortcode');

function porto_shortcode_single_icon( $atts, $content = null ) {

    ob_start();
    if  ($template = porto_shortcode_template( 'porto_single_icon' ) ) {
        include $template;
    }
    return ob_get_clean();
}

function porto_load_single_icon_shortcode() {

    $animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();

    vc_map(
        array(
           "name" => __("Icon Item"),
           "base" => "porto_single_icon",
           "class" => "porto_simple_icon",
           "icon" => "porto4_vc_simple_icon",
           "category" => __("Porto","porto-shortcodes"),
           "description" => __("Add a set of multiple icons and give some custom style.","porto-shortcodes"),
           "as_child" => array('only' => 'porto_icons'),
           "show_settings_on_create" => true,
           "is_container"    => false,
           "params" => array(
                array(
                    "type" => "dropdown",
                    "heading" => __("Icon to display:", "porto-shortcodes"),
                    "param_name" => "icon_type",
                    "value" => array(
                        __( 'Font Awesome', 'porto-shortcodes' ) => 'fontawesome',
                        __( 'Simple Line Icon', 'porto-shortcodes' ) => 'simpleline',
                        __( 'Porto Icon', 'porto-shortcodes' ) => 'porto',
                    ),
                    "group"=> "Select Icon",
                ),
                array(
                    "type" => "iconpicker",
                    "class" => "",
                    "heading" => __("Icon","porto-shortcodes"),
                    "param_name" => "icon",
                    "value" => "",
                    'dependency' => array('element' => 'icon_type', 'value' => 'fontawesome'),
                    "group"=> "Select Icon",
                ),
                array(
                    "type"       => "iconpicker",
                    "heading"    => __( "Icon", "porto-shortcodes" ),
                    "param_name" => "icon_simpleline",
                    'settings' => array(
                        'type' => 'simpleline',
                        'iconsPerPage' => 4000,
                    ),
                    'dependency' => array('element' => 'icon_type', 'value' => 'simpleline'),
                    "group"=> "Select Icon",
                ),
                array(
                    "type"       => "iconpicker",
                    "heading"    => __( "Icon", "porto-shortcodes" ),
                    "param_name" => "icon_porto",
                    'settings' => array(
                        'type' => 'porto',
                        'iconsPerPage' => 4000,
                    ),
                    'dependency' => array('element' => 'icon_type', 'value' => 'porto'),
                    "group"=> "Select Icon",
                ),
                array(
                    "type" => "number",
                    "class" => "",
                    "heading" => __("Icon Size", "porto-shortcodes"),
                    "param_name" => "icon_size",
                    "value" => 32,
                    "min" => 12,
                    "max" => 72,
                    "suffix" => "px",
                    "group"=> "Select Icon",
                ),
                array(
                    "type" => "number",
                    "class" => "",
                    "heading" => __("Space after Icon", "porto-shortcodes"),
                    "param_name" => "icon_margin",
                    "value" => 5,
                    "min" => 0,
                    "max" => 100,
                    "suffix" => "px",
                    "group" => "Other Settings"
                ),
                array(
                    "type" => "colorpicker",
                    "class" => "",
                    "heading" => __("Color", "porto-shortcodes"),
                    "param_name" => "icon_color",
                    "value" => "#333333",
                    "group"=> "Select Icon",
                ),
                array(
                    "type" => "dropdown",
                    "class" => "",
                    "heading" => __("Icon Style", "porto-shortcodes"),
                    "param_name" => "icon_style",
                    "value" => array(
                        __("Simple","porto-shortcodes") => "none",
                        __("Circle Background","porto-shortcodes") => "circle",
                        __("Square Background","porto-shortcodes") => "square",
                        __("Design your own","porto-shortcodes") => "advanced",
                    ),
                    "group" => "Select Icon"
                ),
                array(
                    "type" => "colorpicker",
                    "class" => "",
                    "heading" => __("Background Color", "porto-shortcodes"),
                    "param_name" => "icon_color_bg",
                    "value" => "#ffffff",
                    "description" => __("Select background color for icon.", "porto-shortcodes"),
                    "dependency" => array("element" => "icon_style", "value" => array("circle","square","advanced")),
                    "group" => "Select Icon"
                ),
                array(
                    "type" => "dropdown",
                    "class" => "",
                    "heading" => __("Icon Border Style", "porto-shortcodes"),
                    "param_name" => "icon_border_style",
                    "value" => array(
                        __("None","porto-shortcodes")=> "",
                        __("Solid","porto-shortcodes")=> "solid",
                        __("Dashed","porto-shortcodes") => "dashed",
                        __("Dotted","porto-shortcodes") => "dotted",
                        __("Double","porto-shortcodes") => "double",
                        __("Inset","porto-shortcodes") => "inset",
                        __("Outset","porto-shortcodes") => "outset",
                    ),
                    "description" => __("Select the border style for icon.","porto-shortcodes"),
                    "dependency" => array("element" => "icon_style", "value" => array("advanced")),
                    "group" => "Select Icon"
                ),
                array(
                    "type" => "colorpicker",
                    "class" => "",
                    "heading" => __("Border Color", "porto-shortcodes"),
                    "param_name" => "icon_color_border",
                    "value" => "#333333",
                    "description" => __("Select border color for icon.", "porto-shortcodes"),
                    "dependency" => array("element" => "icon_border_style", "not_empty" => true),
                    "group" => "Select Icon"
                ),
                array(
                    "type" => "number",
                    "class" => "",
                    "heading" => __("Border Width", "porto-shortcodes"),
                    "param_name" => "icon_border_size",
                    "value" => 1,
                    "min" => 1,
                    "max" => 10,
                    "suffix" => "px",
                    "description" => __("Thickness of the border.", "porto-shortcodes"),
                    "dependency" => array("element" => "icon_border_style", "not_empty" => true),
                    "group" => "Select Icon"
                ),
                array(
                    "type" => "number",
                    "class" => "",
                    "heading" => __("Border Radius", "porto-shortcodes"),
                    "param_name" => "icon_border_radius",
                    "value" => 500,
                    "min" => 1,
                    "max" => 500,
                    "suffix" => "px",
                    "dependency" => array("element" => "icon_border_style", "not_empty" => true),
                    "group" => "Select Icon"
                ),
                array(
                    "type" => "number",
                    "class" => "",
                    "heading" => __("Background Size", "porto-shortcodes"),
                    "param_name" => "icon_border_spacing",
                    "value" => 50,
                    "min" => 30,
                    "max" => 500,
                    "suffix" => "px",
                    "description" => __("Spacing from center of the icon till the boundary of border / background", "porto-shortcodes"),
                    "dependency" => array("element" => "icon_border_style", "not_empty" => true),
                    "group" => "Select Icon"
                ),
                array(
                    "type" => "vc_link",
                    "class" => "",
                    "heading" => __("Link ","porto-shortcodes"),
                    "param_name" => "icon_link",
                    "value" => "",
                    "description" => __("Add a custom link or select existing page.","porto-shortcodes"),
                    "group" => "Other Settings"
                ),
                $animation_type,
                $custom_class,
            ),
        )
    );

    if ( class_exists( 'WPBakeryShortCode' ) ) {
        class WPBakeryShortCode_porto_single_icon extends WPBakeryShortCode {
        }
    }

}