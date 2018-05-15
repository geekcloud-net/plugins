<?php
// Porto interactive_banner

add_shortcode('porto_interactive_banner', 'porto_shortcode_interactive_banner');
add_action('vc_after_init', 'porto_load_interactive_banner_shortcode');

function porto_shortcode_interactive_banner( $atts, $content = null ) {

    ob_start();
    if  ($template = porto_shortcode_template( 'porto_interactive_banner' ) ) {
        include $template;
    }
    return ob_get_clean();
}

function porto_load_interactive_banner_shortcode() {

    $animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();

    vc_map(
        array(
           "name" => __("Porto Interactive Banner","porto-shortcodes"),
           "base" => "porto_interactive_banner",
           "class" => "porto_interactive_banner",
           "icon" => "porto4_vc_interactive_banner",
           "category" => __( 'Porto', 'porto-shortcodes' ),
           "description" => __("Displays the banner image with Information","porto-shortcodes"),
           "params" => array(
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Title ","porto-shortcodes"),
                    "param_name" => "banner_title",
                    "admin_label" => true,
                    "value" => "",
                    "description" => __("Give a title to this banner","porto-shortcodes")
                ),
                array(
                    "type" => "textarea_html",
                    "class" => "",
                    "heading" => __("Description","porto-shortcodes"),
                    "param_name" => "content",
                    "value" => "",
                    "description" => __("Text that comes on mouse hover if you select banner style.","porto-shortcodes")
                ),
                array(
                    "type" => "attach_image",
                    "class" => "",
                    "heading" => __("Banner Image","porto-shortcodes"),
                    "param_name" => "banner_image",
                    "value" => "",
                    "description" => __("Upload the image for this banner","porto-shortcodes")
                ),
                array(
                    "type" => "checkbox",
                    "heading" => "",
                    "param_name" => "lazyload",
                    "value" => array(
                        __("Lazy Load Image", "porto-shortcodes") => "enable",
                    ),
                    "dependency" => array("element" => "banner_image", "not_empty" => true),
                ),
                array(
                    "type" => "vc_link",
                    "class" => "",
                    "heading" => __("Link ","porto-shortcodes"),
                    "param_name" => "banner_link",
                    "value" => "",
                    "description" => __("Add link / select existing page to link to this banner","porto-shortcodes"),
                ),
                array(
                    "type" => "dropdown",
                    "class" => "",
                    "heading" => __("Styles ","porto-shortcodes"),
                    "param_name" => "banner_style",
                    "value" => array(
                        __( '', 'porto-shortcodes' ) => 'Default',
                        __( 'Style 1', 'porto-shortcodes' ) => 'style1',
                        __( 'Style 2', 'porto-shortcodes' ) => 'style2',
                    ),
                    "description" => "",
                ),
                array(
                    "type" => "colorpicker",
                    "class" => "",
                    "heading" => __("Title Background Color","porto-shortcodes"),
                    "param_name" => "banner_title_bg",
                    "value" => "",
                    "description" => "",
                    "dependency" => array("element" => "banner_style", "value" => array('style2')),
                ),
                $custom_class,
                array(
                    "type" => "number",
                    "class" => "",
                    "heading" => __("Font Size", "porto-shortcodes"),
                    "param_name" => "banner_title_font_size",
                    "min" => 12,
                    "suffix" => "px",
                    "dependency" => array("element" => "banner_title", "not_empty" => true),
                    "group" => "Typography",
                ),
                array(
                    "type" => "colorpicker",
                    "class" => "",
                    "heading" => __("Title Color","porto-shortcodes"),
                    "param_name" => "banner_color_title",
                    "value" => "",
                    "description" => "",
                    "group" => "Color Settings",
                ),
                array(
                    "type" => "colorpicker",
                    "class" => "",
                    "heading" => __("Description Color","porto-shortcodes"),
                    "param_name" => "banner_color_desc",
                    "value" => "",
                    "description" => "",
                    "group" => "Color Settings",
                ),
                array(
                    "type" => "colorpicker",
                    "class" => "",
                    "heading" => __("Background Color","porto-shortcodes"),
                    "param_name" => "banner_color_bg",
                    "value" => "",
                    "description" => "",
                    "group" => "Color Settings",
                ),
                array(
                    "type" => "number",
                    "class" => "",
                    "heading" => __("Image Opacity", "porto-shortcodes"),
                    "param_name" => "image_opacity",
                    "value" => 1,
                    "min" => 0.0,
                    "max" => 1.0,
                    "step" => 0.1,
                    "suffix" => "",
                    "description" => __("Enter value between 0.0 to 1 (0 is maximum transparency, while 1 is lowest)","porto-shortcodes"),
                    "group" => "Color Settings",
                ),
                array(
                    "type" => "number",
                    "class" => "",
                    "heading" => __("Image Opacity on Hover", "porto-shortcodes"),
                    "param_name" => "image_opacity_on_hover",
                    "value" => 1,
                    "min" => 0.0,
                    "max" => 1.0,
                    "step" => 0.1,
                    "suffix" => "",
                    "description" => __("Enter value between 0.0 to 1 (0 is maximum transparency, while 1 is lowest)","porto-shortcodes"),
                    "group" => "Color Settings",
                ),
                array(
                    'type' => 'css_editor',
                    'heading' => __( 'Css', 'porto-shortcodes' ),
                    'param_name' => 'css_ibanner',
                    'group' => __( 'Design ', 'porto-shortcodes' ),
                    'edit_field_class' => 'vc_col-sm-12 vc_column no-vc-background no-vc-border creative_link_css_editor',
                ),
            ),
        )
    );

    if ( class_exists( 'WPBakeryShortCode' ) ) {
        class WPBakeryShortCode_porto_interactive_banner extends WPBakeryShortCode {
        }
    }

}