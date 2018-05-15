<?php
// Porto fancytext

add_shortcode('porto_fancytext', 'porto_shortcode_fancytext');
add_action('vc_after_init', 'porto_load_fancytext_shortcode');

function porto_shortcode_fancytext( $atts, $content = null ) {

    ob_start();
    if  ($template = porto_shortcode_template( 'porto_fancytext' ) ) {
        include $template;
    }
    return ob_get_clean();
}

function porto_load_fancytext_shortcode() {

    $custom_class = porto_vc_custom_class();

    global $porto_settings;
    $custom_fonts = array(__("Default", "porto-shortcodes") => "");
    for ( $i = 1; $i <=3; $i++ ) {
        if ( isset( $porto_settings['custom'.$i.'-font'] ) && !empty( $porto_settings['custom'.$i.'-font']['font-family'] ) ) {
            $custom_fonts[$porto_settings['custom'.$i.'-font']['font-family']] = $porto_settings['custom'.$i.'-font']['font-family'];
        }
    }

    vc_map(
        array(
           "name" => __("Porto Fancy Text","porto-shortcodes"),
           "base" => "porto_fancytext",
           "class" => "porto_fancytext",
           "icon" => "porto4_vc_fancytext",
           "category" => __( 'Porto', 'porto-shortcodes' ),
           "description" => __("Fancy lines with animation effects.","porto-shortcodes"),
           "params" => array(
                array(
                    "type" => "textfield",
                    "param_name" => "fancytext_prefix",
                    "heading" => __("Prefix","porto-shortcodes"),
                    "value" => "",
                ),
                array(
                    'type' => 'textarea',
                    'heading' => __( 'Fancy Text', 'porto-shortcodes' ),
                    'param_name' => 'fancytext_strings',
                    'description' => __('Enter each string on a new line','porto-shortcodes'),
                    'admin_label' => true
                ),
                array(
                    "type" => "textfield",
                    "param_name" => "fancytext_suffix",
                    "heading" => __("Suffix","porto-shortcodes"),
                    "value" => "",
                ),
                array(
                    "type" => "dropdown",
                    "heading" => __("Alignment", "porto-shortcodes"),
                    "param_name" => "fancytext_align",
                    "value" => array(
                        __("Center","porto-shortcodes") => "center",
                        __("Left","porto-shortcodes") => "left",
                        __("Right","porto-shortcodes") => "right"
                    )
                ),
                array(
                    "type" => "number",
                    "heading" => __("Animation Speed", "porto-shortcodes"),
                    "param_name" => "strings_tickerspeed",
                    "min" => 0,
                    "value" => 200,
                    "suffix" => __("In Miliseconds","porto-shortcodes"),
                    "group" => "Advanced Settings",
                    "description" => __("Duration of 'Slide Up' animation", "porto-shortcodes")
                ),
                array(
                    "type" => "number",
                    "heading" => __("Pause Time", "porto-shortcodes"),
                    "param_name" => "ticker_wait_time",
                    "min" => 0,
                    "value" => "3000",
                    "suffix" => __("In Miliseconds","porto-shortcodes"),
                    "group" => "Advanced Settings",
                    "description" => __("How long the string should stay visible?","porto-shortcodes")
                ),
                array(
                    "type" => "dropdown",
                    "heading" => __("Pause on Hover","porto-shortcodes"),
                    "param_name" => "ticker_hover_pause",
                    "value" => array(
                        'No' => '',
                        'Yes' => 'true',
                    ),
                    "group" => "Advanced Settings",
                ),
                $custom_class,
                array(
                    "type" => "porto_param_heading",
                    "param_name" => "fancy_text_typography",
                    "text" => __("Fancy Text Settings","porto-shortcodes"),
                    "value" => "",
                    "group" => "Typography",
                    "class" => "porto-param-heading",
                    'edit_field_class' => 'porto-param-heading-wrapper no-top-margin vc_column vc_col-sm-12',
                ),
                array(
                    "type" => "dropdown",
                    "heading" => __("Text Font", "porto-shortcodes"),
                    "param_name" => "strings_font_family",
                    "value" => $custom_fonts,
                    "group" => "Typography"
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Font Weight", 'porto-shortcodes'),
                    "param_name" => "strings_font_style",
                    "group" => "Typography"
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Font Size", 'porto-shortcodes'),
                    "param_name" => "strings_font_size",
                    "group" => "Typography"
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Line Height", 'porto-shortcodes'),
                    "param_name" => "strings_line_height",
                    "group" => "Typography"
                ),
                array(
                    "type" => "colorpicker",
                    "heading" => __("Fancy Text Color","porto-shortcodes"),
                    "param_name" => "fancytext_color",
                    "group" => "Advanced Settings",
                    "group" => "Typography",
                ),
                array(
                    "type" => "colorpicker",
                    "heading" => __("Fancy Text Background","porto-shortcodes"),
                    "param_name" => "ticker_background",
                    "group" => "Advanced Settings",
                    "group" => "Typography",
                ),
                array(
                    "type" => "porto_param_heading",
                    "param_name" => "fancy_prefsuf_text_typography",
                    "text" => __("Prefix Suffix Text Settings","porto-shortcodes"),
                    "value" => "",
                    "group" => "Typography",
                    "class" => "porto-param-heading",
                    'edit_field_class' => 'porto-param-heading-wrapper no-top-margin vc_column vc_col-sm-12',
                ),
                array(
                    "type" => "dropdown",
                    "heading" => __("Prefix Suffix Text Font", "porto-shortcodes"),
                    "param_name" => "prefsuf_font_family",
                    "value" => $custom_fonts,
                    "group" => "Typography"
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Font Weight", 'porto-shortcodes'),
                    "param_name" => "prefsuf_font_style",
                    "group" => "Typography"
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Font Size", 'porto-shortcodes'),
                    "param_name" => "prefix_suffix_font_size",
                    "group" => "Typography"
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Line Height", 'porto-shortcodes'),
                    "param_name" => "prefix_suffix_line_height",
                    "group" => "Typography"
                ),
                array(
                    "type" => "colorpicker",
                    "class" => "",
                    "heading" => __("Prefix & Suffix Text Color", "porto-shortcodes"),
                    "param_name" => "sufpref_color",
                    "value" => "",
                    "group" => "Typography"
                ),
                array(
                    "type" => "colorpicker",
                    "class" => "",
                    "heading" => __("Prefix & Suffix Background Color", "porto-shortcodes"),
                    "param_name" => "sufpref_bg_color",
                    "value" => "",
                    "group" => "Typography"
                ),
                array(
                    "type" => "dropdown",
                    "heading" => __("Markup","porto-shortcodes"),
                    "param_name" => "fancytext_tag",
                    "value" => array(
                        __("div","porto-shortcodes") => "div",
                        __("H1","porto-shortcodes") => "h1",
                        __("H2","porto-shortcodes") => "h2",
                        __("H3","porto-shortcodes") => "h3",
                        __("H4","porto-shortcodes") => "h4",
                        __("H5","porto-shortcodes") => "h5",
                        __("H6","porto-shortcodes") => "h6",
                    ),
                    "std" => "h2",
                    "group" => "Typography",
                ),
                array(
                    'type' => 'css_editor',
                    'heading' => __( 'Css', 'porto-shortcodes' ),
                    'param_name' => 'css_fancy_design',
                    'group' => __( 'Design ', 'porto-shortcodes' ),
                    'edit_field_class' => 'vc_col-sm-12 vc_column no-vc-background no-vc-border creative_link_css_editor',
                ),
            )
        )
    );

    if(class_exists('WPBakeryShortCode'))
    {
        class WPBakeryShortCode_porto_fancytext extends WPBakeryShortCode {
        }
    }
}