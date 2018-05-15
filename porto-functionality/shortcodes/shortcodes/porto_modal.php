<?php
// Porto modal

add_shortcode('porto_modal', 'porto_shortcode_modal');
add_action('vc_after_init', 'porto_load_modal_shortcode');

function porto_shortcode_modal( $atts, $content = null ) {

    ob_start();
    if  ($template = porto_shortcode_template( 'porto_modal' ) ) {
        include $template;
    }
    return ob_get_clean();
}

function porto_load_modal_shortcode() {

    $custom_class = porto_vc_custom_class();

    vc_map(
        array(
            "name"      => __("Porto Modal Box", "porto-shortcodes"),
            "base"      => "porto_modal",
            "icon"      => "porto4_vc_modal",
            "class"    => "porto_modal",
            "category"  => __( 'Porto', 'porto-shortcodes' ),
            "description" => __("Adds bootstrap modal box in your content","porto-shortcodes"),
            "controls" => "full",
            "show_settings_on_create" => true,
            "params" => array(
                // Add some description
                array(
                    "type" => "dropdown",
                    "heading" => __("What's in Modal Popup?", "porto-shortcodes"),
                    "param_name" => "modal_contain",
                    "value" => array(
                        __("Miscellaneous Things","porto-shortcodes") => "html",
                        __("Youtube Video","porto-shortcodes") => "youtube",
                        __("Vimeo Video","porto-shortcodes") => "vimeo",
                    ),
                    "description" => __("Please put the embed code in the content for videos, eg: <a href='http://bsf.io/kuv3-' target='_blank'>http://bsf.io/kuv3-</a><br>
                        For hosted video - Add any video with WordPress media uploader or with <a href='https://codex.wordpress.org/Video_Shortcode' target='_blank'>[video]</a> shortcode.", "porto-shortcodes"),
                    "group" => "General",
                ),
                array(
                    "type" => "textfield",
                    "heading" => __("Youtube URL", "porto-shortcodes"),
                    "param_name" => "youtube_url",
                    "value" => "",
                    "dependency"=>array("element"=>"modal_contain","value"=>array("youtube")),
                    "group" => "General",
                ),
                array(
                    "type" => "textfield",
                    "heading" => __("Vimeo URL", "porto-shortcodes"),
                    "param_name" => "vimeo_url",
                    "value" => "",
                    "dependency"=>array("element"=>"modal_contain","value"=>array("vimeo")),
                    "group" => "General",
                ),
                array(
                    "type" => "textarea_html",
                    "heading" => __("Modal Content", "porto-shortcodes"),
                    "param_name" => "content",
                    "value" => "",
                    "description" => __("Content that will be displayed in Modal Popup.", "porto-shortcodes"),
                    "group" => "General",
                    "edit_field_class" => "vc_col-xs-12 vc_column wpb_el_type_textarea_html vc_wrapper-param-type-textarea_html vc_shortcode-param",
                    "dependency" => array("element"=>"modal_contain","value"=>array("html"))
                ),
                array(
                    "type" => "dropdown",
                    "heading" => __("Display Modal On -", "porto-shortcodes"),
                    "param_name" => "modal_on",
                    "value" => array(
                        __("On Page Load","porto-shortcodes") => "onload",
                        __("Image","porto-shortcodes") => "image",
                        __("Selector","porto-shortcodes") => "custom-selector",
                    ),
                    "description" => __("When should the popup be initiated?", "porto-shortcodes"),
                    "group" => "General",
                ),
                array(
                    "type" => "textfield",
                    "heading" => __("Class and/or ID", "porto-shortcodes"),
                    "param_name" => "modal_on_selector",
                    "description" => __("Add .Class and/or #ID to open your modal. Multiple ID or Classes separated by comma","ultimate_vc"),
                    "value" => "",
                    "dependency"=>array("element"=>"modal_on","value"=>array("custom-selector")),
                    "group" => "General",
                ),
                array(
                    "type" => "attach_image",
                    "heading" => __("Upload Image", "porto-shortcodes"),
                    "param_name" => "btn_img",
                    "admin_label" => true,
                    "description" => __("Upload the custom image / image banner.", "porto-shortcodes"),
                    "dependency" => array("element" => "modal_on","value" => array("image")),
                    "group" => "General",
                ),
                // Modal Style
                array(
                    "type" => "dropdown",
                    "heading" => __("Modal Box Style","porto-shortcodes"),
                    "param_name" => "modal_style",
                    "value" => array(
                          __("Fade","porto-shortcodes") => "mfp-fade",
                        __("Zoom in","porto-shortcodes") => "my-mfp-zoom-in",
                    ),
                     "dependency" => array("element" => "modal_contain","value" => array("html")),
                    "group" => "General",
                ),
                array(
                    "type" => "colorpicker",
                    "heading" => __("Overlay Background Color", "porto-shortcodes"),
                    "param_name" => "overlay_bg_color",
                    "value" => "",
                    "group" => "General",
                ),
                array(
                    "type" => "number",
                    "heading" => __("Overlay Background Opacity", "porto-shortcodes"),
                    "param_name" => "overlay_bg_opacity",
                    "value" => 80,
                    "min" => 10,
                    "max" => 100,
                    "suffix" => "%",
                    "description" => __("Select opacity of overlay background.", "porto-shortcodes"),
                    "group" => "General",
                ),
                array(
                    "type" => "textfield",
                    "heading" => __("Extra Class (Button/Image)", "porto-shortcodes"),
                    "param_name" => "init_extra_class",
                    "admin_label" => true,
                    "value" => "",
                    "description" => __("Provide ex class for this button/image.", "porto-shortcodes"),
                    "dependency" => array("element" => "modal_on","value" => array("image")),
                    "group" => "General",
                ),
            ) // end params array
        ) // end vc_map array
    ); // end vc_map
    
    if ( class_exists( 'WPBakeryShortCode' ) ) {
        class WPBakeryShortCode_porto_modal extends WPBakeryShortCode {
        }
    }
}