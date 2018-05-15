<?php
// Porto Stat Counter

add_shortcode('porto_stat_counter', 'porto_shortcode_stat_counter');
add_action('vc_after_init', 'porto_load_stat_counter_shortcode');

function porto_shortcode_stat_counter( $atts, $content = null ) {

    ob_start();
    if  ($template = porto_shortcode_template( 'porto_stat_counter' ) ) {
        include $template;
    }
    return ob_get_clean();
}

function porto_load_stat_counter_shortcode() {

    $animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
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
           "name" => __("Porto Counter","porto-shortcodes"),
           "base" => "porto_stat_counter",
           "class" => "porto_stat_counter",
           "icon" => "porto4_vc_stat_counter",
           "category" => __("Porto", 'porto-shortcodes'),
           "description" => __("Your milestones, achievements, etc.","porto-shortcodes"),
           "params" => array(
                array(
                    "type" => "dropdown",
                    "heading" => __("Icon to display:", "porto-shortcodes"),
                    "param_name" => "icon_type",
                    "value" => array(
                        __( 'Font Awesome', 'porto-shortcodes' ) => 'fontawesome',
                        __( 'Simple Line Icon', 'porto-shortcodes' ) => 'simpleline',
                        __( 'Porto Icon', 'porto-shortcodes' ) => 'porto',
                        __("Custom Image Icon","porto-shortcodes") => "custom",
                    ),
                    "description" => __("Use an existing font icon or upload a custom image.", "porto-shortcodes")
                ),
                array(
                    "type" => "iconpicker",
                    "heading" => __("Icon ","porto-shortcodes"),
                    "param_name" => "icon",
                    "value" => "",
                    'dependency' => array('element' => 'icon_type', 'value' => 'fontawesome'),
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
                    "heading" => __("Upload Image Icon:", "porto-shortcodes"),
                    "param_name" => "icon_img",
                    "value" => "",
                    "description" => __("Upload the custom image icon.", "porto-shortcodes"),
                    "dependency" => array("element" => "icon_type","value" => array("custom")),
                ),
                array(
                    "type" => "number",
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
                    "heading" => __("Icon Size", "porto-shortcodes"),
                    "param_name" => "icon_size",
                    "value" => 32,
                    "min" => 12,
                    "max" => 72,
                    "suffix" => "px",
                    "dependency" => array("element" => "icon_type","value" => array("fontawesome", "simpleline", "porto")),
                ),
                array(
                    "type" => "colorpicker",
                    "heading" => __("Color", "porto-shortcodes"),
                    "param_name" => "icon_color",
                    "value" => "#333333",
                    "dependency" => array("element" => "icon_type","value" => array("fontawesome", "simpleline", "porto")),
                ),
                array(
                    "type" => "dropdown",
                    "heading" => __("Icon Style", "porto-shortcodes"),
                    "param_name" => "icon_style",
                    "value" => array(
                        __("Simple","porto-shortcodes") => "none",
                        __("Circle Background","porto-shortcodes") => "circle",
                        __("Square Background","porto-shortcodes") => "square",
                        __("Advanced","porto-shortcodes") => "advanced",
                    ),
                ),
                array(
                    "type" => "colorpicker",
                    "heading" => __("Background Color", "porto-shortcodes"),
                    "param_name" => "icon_color_bg",
                    "value" => "#ffffff",
                    "description" => __("Select background color for icon.", "porto-shortcodes"),
                    "dependency" => array("element" => "icon_style", "value" => array("circle","square","advanced")),
                ),
                array(
                    "type" => "dropdown",
                    "heading" => __("Icon Border Style", "porto-shortcodes"),
                    "param_name" => "icon_border_style",
                    "value" => array(
                        __("None","porto-shortcodes") => "",
                        __("Solid","porto-shortcodes") => "solid",
                        __("Dashed","porto-shortcodes") => "dashed",
                        __("Dotted","porto-shortcodes") => "dotted",
                        __("Double","porto-shortcodes") => "double",
                        __("Inset","porto-shortcodes") => "inset",
                        __("Outset","porto-shortcodes") => "outset",
                    ),
                    "description" => __("Select the border style for icon.","porto-shortcodes"),
                    "dependency" => array("element" => "icon_style", "value" => array("advanced")),
                ),
                array(
                    "type" => "colorpicker",
                    "heading" => __("Border Color", "porto-shortcodes"),
                    "param_name" => "icon_color_border",
                    "value" => "#333333",
                    "description" => __("Select border color for icon.", "porto-shortcodes"),
                    "dependency" => array("element" => "icon_border_style", "not_empty" => true),
                ),
                array(
                    "type" => "number",
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
                    "heading" => __("Background Size", "porto-shortcodes"),
                    "param_name" => "icon_border_spacing",
                    "value" => 50,
                    "min" => 0,
                    "max" => 500,
                    "suffix" => "px",
                    "description" => __("Spacing from center of the icon till the boundary of border / background", "porto-shortcodes"),
                    "dependency" => array("element" => "icon_style", "value" => array("advanced")),
                ),
                $animation_type,
                array(
                    "type" => "dropdown",
                    "heading" => __("Icon Position", "porto-shortcodes"),
                    "param_name" => "icon_position",
                    "value" => array(
                        __('Top','porto-shortcodes') => 'top',
                        __('Right','porto-shortcodes') => 'right',
                        __('Left','porto-shortcodes') => 'left',
                    ),
                    "description" => __("Enter Position of Icon", "porto-shortcodes")
                ),
                array(
                    "type" => "textfield",
                    "heading" => __("Counter Title ", "porto-shortcodes"),
                    "param_name" => "counter_title",
                    "admin_label" => true,
                    "value" => "",
                    "description" => __("Enter title for stats counter block", "porto-shortcodes")
                ),
                array(
                 "type" => "textfield",
                 "class" => "",
                 "heading" => __("Counter Value", "porto-shortcodes"),
                 "param_name" => "counter_value",
                 "value" => "1250",
                 "description" => __("Enter number for counter without any special character. You may enter a decimal number. Eg 12.76", "porto-shortcodes")
                ),
              array(
                 "type" => "textfield",
                 "class" => "",
                 "heading" => __("Thousands Separator", "porto-shortcodes"),
                 "param_name" => "counter_sep",
                 "value" => ",",
                 "description" => __("Enter character for thousanda separator. e.g. ',' will separate 125000 into 125,000", "porto-shortcodes")
              ),
              array(
                 "type" => "textfield",
                 "class" => "",
                 "heading" => __("Replace Decimal Point With", "porto-shortcodes"),
                 "param_name" => "counter_decimal",
                 "value" => ".",
                 "description" => __("Did you enter a decimal number (Eg - 12.76) The decimal point '.' will be replaced with value that you will enter above.", "porto-shortcodes"),
              ),
              array(
                 "type" => "textfield",
                 "class" => "",
                 "heading" => __("Counter Value Prefix", "porto-shortcodes"),
                 "param_name" => "counter_prefix",
                 "value" => "",
                 "description" => __("Enter prefix for counter value", "porto-shortcodes")
              ),
              array(
                 "type" => "textfield",
                 "class" => "",
                 "heading" => __("Counter Value Suffix", "porto-shortcodes"),
                 "param_name" => "counter_suffix",
                 "value" => "",
                 "description" => __("Enter suffix for counter value", "porto-shortcodes")
              ),
              array(
                    "type" => "number",
                    "heading" => __("Counter rolling time", "porto-shortcodes"),
                    "param_name" => "speed",
                    "value" => 3,
                    "min" => 1,
                    "max" => 10,
                    "suffix" => "seconds",
                    "description" => __("How many seconds the counter should roll?", "porto-shortcodes")
                ),
                array(
                    "type" => "textfield",
                    "heading" => __("Extra Class",  "porto-shortcodes"),
                    "param_name" => "el_class",
                    "value" => "",
                    "description" => __("Add extra class name that will be applied to the icon process, and you can use this class for your customizations.",  "porto-shortcodes"),
                ),
                array(
                    "type" => "porto_param_heading",
                    "param_name" => "title_text_typography",
                    "heading" => __("Counter Title settings","porto-shortcodes"),
                    "value" => "",
                    "group" => "Typography",
                    'edit_field_class' => 'no-top-margin vc_column vc_col-sm-12',
                ),
                array(
                    "type" => "dropdown",
                    "heading" => __("Font family", 'porto-shortcodes'),
                    "param_name" => "title_font",
                    "description" => __("You can add new font using 'Custom font' section in Appearance->Theme options->Skin->Typography", "porto-shortcodes"),
                    "value" => $custom_fonts,
                    "group" => "Typography"
                ),
                array(
                    "type" => "textfield",
                    "heading" => __("Font Weight","porto-shortcodes"),
                    "param_name" => "title_font_style",
                    "value" => "",
                    "group" => "Typography"
                ),
                array(
                    "type" => "textfield",
                    "heading" => __("Font size", 'porto-shortcodes'),
                    "param_name" => "title_font_size",
                    "group" => "Typography",
                ),
                array(
                    "type" => "textfield",
                    "heading" => __("Line Height", 'porto-shortcodes'),
                    "param_name" => "title_font_line_height",
                    "group" => "Typography",
                ),
                array(
                    "type" => "colorpicker",
                    "heading" => __("Color", "porto-shortcodes"),
                    "param_name" => "counter_color_txt",
                    "value" => "",
                    "description" => __("Select text color for counter title.", "porto-shortcodes"),
                    'group' => "Typography"
                ),
                array(
                    "type" => "porto_param_heading",
                    "param_name" => "desc_text_typography",
                    "heading" => __("Counter Value settings","porto-shortcodes"),
                    "value" => "",
                    "group" => "Typography",
                    'edit_field_class' => 'vc_column vc_col-sm-12',
                ),
                array(
                    "type" => "dropdown",
                    "heading" => __("Font Family","porto-shortcodes"),
                    "param_name" => "desc_font",
                    "description" => __("You can add new font using 'Custom font' section in Appearance->Theme options->Skin->Typography", "porto-shortcodes"),
                    "value" => $custom_fonts,
                    "group" => "Typography"
                ),
                array(
                    "type" => "textfield",
                    "heading" => __("Font Weight","porto-shortcodes"),
                    "param_name" => "desc_font_style",
                    "value" => "",
                    "group" => "Typography"
                ),
                array(
                    "type" => "textfield",
                    "heading" => __("Font size", 'porto-shortcodes'),
                    "param_name" => "desc_font_size",
                    "group" => "Typography",
                ),
                array(
                    "type" => "textfield",
                    "heading" => __("Line Height", 'porto-shortcodes'),
                    "param_name" => "desc_font_line_height",
                    "group" => "Typography",
                ),
                array(
                    "type" => "colorpicker",
                    "param_name" => "desc_font_color",
                    "heading" => __("Color","porto-shortcodes"),
                    "description" => __("Select text color for counter digits.", "porto-shortcodes"),
                    "group" => "Typography"
                ),
                array(
                    "type" => "porto_param_heading",
                    "param_name" => "suf_pref_typography",
                    "heading" => __("Counter suffix-prefix Value settings","porto-shortcodes"),
                    "value" => "",
                    "group" => "Typography",
                    'edit_field_class' => 'vc_column vc_col-sm-12',
                ),
                array(
                    "type" => "dropdown",
                    "heading" => __("Font Family","porto-shortcodes"),
                    "param_name" => "suf_pref_font",
                    "description" => __("You can add new font using 'Custom font' section in Appearance->Theme options->Skin->Typography", "porto-shortcodes"),
                    "value" => $custom_fonts,
                    "group" => "Typography"
                ),
                array(
                    "type" => "textfield",
                    "heading" => __("Font Weight","porto-shortcodes"),
                    "param_name" => "suf_pref_font_style",
                    "value" => "",
                    "group" => "Typography"
                ),
                array(
                    "type" => "textfield",
                    "heading" => __("Font size", 'porto-shortcodes'),
                    "param_name" => "suf_pref_font_size",
                    "group" => "Typography",
                ),
                array(
                    "type" => "textfield",
                    "heading" => __("Line Height", 'porto-shortcodes'),
                    "param_name" => "suf_pref_line_height",
                    "group" => "Typography",
                ),
                array(
                    "type" => "colorpicker",
                    "param_name" => "suf_pref_font_color",
                    "heading" => __("Color","porto-shortcodes"),
                    "description" => __("Select text color for counter prefix and suffix.", "porto-shortcodes"),
                    "group" => "Typography"
                ),
                array(
                    'type' => 'css_editor',
                    'heading' => __( 'Css', 'porto-shortcodes' ),
                    'param_name' => 'css_stat_counter',
                    'group' => __( 'Design ', 'porto-shortcodes' ),
                    'edit_field_class' => 'vc_col-sm-12 vc_column no-vc-background no-vc-border creative_link_css_editor',
                ),
            ),
        )
    );

    if ( class_exists( 'WPBakeryShortCode' ) ) {
        class WPBakeryShortCode_porto_stat_counter extends WPBakeryShortCode {
        }
    }
}