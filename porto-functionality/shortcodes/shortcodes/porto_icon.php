<?php
// Porto Icon

add_shortcode('porto_icon', 'porto_shortcode_icon');
add_action('vc_after_init', 'porto_load_icon_shortcode');

function porto_shortcode_icon( $atts, $content = null ) {

    ob_start();
    if  ($template = porto_shortcode_template( 'porto_icon' ) ) {
        include $template;
    }
    return ob_get_clean();
}

function porto_load_icon_shortcode() {

    $animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();

    vc_map(
        array(
           "name" => __("Porto Icon","porto-shortcodes"),
           "base" => "porto_icon",
           "icon" => "porto4_vc_icon",
           "category" => __("Porto", 'porto-shortcodes'),
           "description" => __("Add a custom icon.","porto-shortcodes"),
           "params" => array(
                array(
                    "type" => "dropdown",
                    "class" => "",
                    "heading" => __("Icon to display:", "porto-shortcodes"),
                    "param_name" => "icon_type",
                    "value" => array(
                        __( 'Font Awesome', 'porto-shortcodes' ) => 'fontawesome',
                        __( 'Simple Line Icon', 'porto-shortcodes' ) => 'simpleline',
                        __( 'Porto Icon', 'porto-shortcodes' ) => 'porto',
                        __( 'Custom Image Icon', 'porto-shortcodes' ) => 'custom',
                    ),
                ),
                array(
                    "type" => "iconpicker",
                    "class" => "",
                    "heading" => __("Icon","porto-shortcodes"),
                    "param_name" => "icon",
                    "value" => "",
                    "dependency" => array("element" => "icon_type","value" => array("fontawesome")),
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
                ),
                array(
                    "type" => "attach_image",
                    "class" => "",
                    "heading" => __("Upload Image Icon", "porto-shortcodes"),
                    "param_name" => "icon_img",
                    "admin_label" => true,
                    "value" => "",
                    "description" => __("Upload the custom image icon.", "porto-shortcodes"),
                    "dependency" => array("element" => "icon_type","value" => array("custom")),
                ),
                array(
                    "type" => "number",
                    "class" => "",
                    "heading" => __("Image Width", "porto-shortcodes"),
                    "param_name" => "img_width",
                    "value" => 48,
                    "min" => 16,
                    "max" => 512,
                    "suffix" => "px",
                    "description" => __("Provide image width", "porto-shortcodes"),
                    "dependency" => array("element" => "icon_type","value" => array("custom")),
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
                    "dependency" => array( "element" => "icon_type","value" => array( "fontawesome", "simpleline", "porto" ) ),
                ),
                array(
                    "type" => "colorpicker",
                    "class" => "",
                    "heading" => __("Color", "porto-shortcodes"),
                    "param_name" => "icon_color",
                    "value" => "#333333",
                    "dependency" => array( "element" => "icon_type","value" => array( "fontawesome", "simpleline", "porto" ) ),
                ),
                array(
                    "type" => "dropdown",
                    "class" => "",
                    "heading" => __("Icon or Image Style", "porto-shortcodes"),
                    "param_name" => "icon_style",
                    "value" => array(
                        __("Simple","porto-shortcodes") => "none",
                        __("Circle Background","porto-shortcodes") => "circle",
                        __("Circle Image","porto-shortcodes") => "circle_img",
                        __("Square Background","porto-shortcodes") => "square",
                        __("Design your own","porto-shortcodes") => "advanced",
                    ),
                ),
                array(
                    "type" => "colorpicker",
                    "class" => "",
                    "heading" => __("Background Color", "porto-shortcodes"),
                    "param_name" => "icon_color_bg",
                    "value" => "#ffffff",
                    "description" => __("Select background color for icon.", "porto-shortcodes"),
                    "dependency" => array("element" => "icon_style", "value" => array("circle","square","advanced", "circle_img" )),
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
                    "dependency" => array("element" => "icon_style", "value" => array("circle_img", "advanced")),
                ),
                array(
                    "type" => "colorpicker",
                    "class" => "",
                    "heading" => __("Border Color", "porto-shortcodes"),
                    "param_name" => "icon_color_border",
                    "value" => "#333333",
                    "description" => __("Select border color for icon.", "porto-shortcodes"),
                    "dependency" => array("element" => "icon_border_style", "not_empty" => true),
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
                ),
                array(
                    "type" => "number",
                    "class" => "",
                    "heading" => __("Background Size", "porto-shortcodes"),
                    "param_name" => "icon_border_spacing",
                    "value" => 50,
                    "min" => 1,
                    "max" => 500,
                    "suffix" => "px",
                    "description" => __("Spacing from center of the icon till the boundary of border / background", "porto-shortcodes"),
                    "dependency" => array("element" => "icon_border_style", "not_empty" => true),
                ),
                array(
                    "type" => "vc_link",
                    "class" => "",
                    "heading" => __("Link ","porto-shortcodes"),
                    "param_name" => "icon_link",
                    "value" => "",
                    "description" => __("Add a custom link or select existing page.", "porto-shortcodes")
                ),
                $animation_type,
                array(
                    "type" => "dropdown",
                    "class" => "",
                    "heading" => __("Alignment", "porto-shortcodes"),
                    "param_name" => "icon_align",
                    "value" => array(
                        __("Center","porto-shortcodes") =>  "center",
                        __("Left","porto-shortcodes") =>  "left",
                        __("Right","porto-shortcodes") =>  "right"
                    )
                ),
                $custom_class,
                array(
                    'type' => 'css_editor',
                    'heading' => __( 'Css', 'porto-shortcodes' ),
                    'param_name' => 'css_porto_icon',
                    'group' => __( 'Design ', 'porto-shortcodes' ),
                    'edit_field_class' => 'vc_col-sm-12 vc_column no-vc-background no-vc-border creative_link_css_editor',
                ),
            ),
        )
    );

    if ( class_exists( 'WPBakeryShortCode' ) ) {
        class WPBakeryShortCode_porto_icon extends WPBakeryShortCode {
        }
    }
}