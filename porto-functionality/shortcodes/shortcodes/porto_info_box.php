<?php
// Porto Info Box

add_shortcode('porto_info_box', 'porto_shortcode_info_box');
add_action('vc_after_init', 'porto_load_info_box_shortcode');

function porto_shortcode_info_box( $atts, $content = null ) {

    ob_start();
    if  ($template = porto_shortcode_template( 'porto_info_box' ) ) {
        include $template;
    }
    return ob_get_clean();
}

function porto_load_info_box_shortcode() {

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
            "name"      => __("Porto Info Box", "porto-shortcodes"),
            "base"      => "porto_info_box",
            "icon"      => "porto4_vc_info_box",
            "class"    => "porto_info_box",
            "category" => __("Porto", 'porto-shortcodes'),
            "description" => __("Adds icon box with custom font icon","porto-shortcodes"),
            "controls" => "full",
            "show_settings_on_create" => true,
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
                        __("Custom Image Icon","porto-shortcodes") => "custom",
                    ),
                    "description" => __("Use an existing font icon or upload a custom image.", "porto-shortcodes")
                ),
                array(
                    "type" => "iconpicker",
                    "class" => "",
                    "heading" => __("Icon ","porto-shortcodes"),
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
                    "heading" => __("Upload Image Icon:", "porto-shortcodes"),
                    "param_name" => "icon_img",
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
                    "dependency" => array("element" => "icon_type","value" => array("fontawesome", "simpleline", "porto")),
                ),
                array(
                    "type" => "colorpicker",
                    "class" => "",
                    "heading" => __("Color", "porto-shortcodes"),
                    "param_name" => "icon_color",
                    "value" => "#333333",
                    "dependency" => array("element" => "icon_type","value" => array("fontawesome", "simpleline", "porto")),
                ),
                array(
                    "type" => "dropdown",
                    "class" => "",
                    "heading" => __("Icon Style", "porto-shortcodes"),
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
                    "dependency" => array("element" => "icon_style", "value" => array("circle","circle_img","square","advanced")),
                ),
                array(
                    "type" => "dropdown",
                    "class" => "",
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
                    "dependency" => Array("element" => "icon_style", "value" => array("circle_img", "advanced")),
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
                    "min" => 0,
                    "max" => 500,
                    "suffix" => "px",
                    "description" => __("Spacing from center of the icon till the boundary of border / background", "porto-shortcodes"),
                    "dependency" => array("element" => "icon_style", "value" => array("circle_img", "advanced")),
                ),
                $animation_type,
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Title", "porto-shortcodes"),
                    "param_name" => "title",
                    "admin_label" => true,
                    "value" => "",
                    "description" => __("Provide the title for this icon box.", "porto-shortcodes"),
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Sub title", "porto-shortcodes"),
                    "param_name" => "subtitle",
                    "admin_label" => true,
                    "value" => "",
                    "description" => __("Provide the sub title for this icon box.", "porto-shortcodes"),
                    "dependency" => array("element" => "title", "not_empty" => true),
                ),
                array(
                    "type" => "textarea_html",
                    "class" => "",
                    "heading" => __("Description", "porto-shortcodes"),
                    "param_name" => "content",
                    "value" => "",
                    "description" => __("Provide the description for this icon box.", "porto-shortcodes"),
                    "edit_field_class" => "vc_col-xs-12 vc_column wpb_el_type_textarea_html vc_wrapper-param-type-textarea_html vc_shortcode-param",
                ),
                array(
                    "type" => "dropdown",
                    "class" => "",
                    "heading" => __("Apply link to:", "porto-shortcodes"),
                    "param_name" => "read_more",
                    "value" => array(
                        __("No Link","porto-shortcodes") => "none",
                        __("Complete Box","porto-shortcodes") => "box",
                        __("Box Title","porto-shortcodes") => "title",
                        __("Display Read More","porto-shortcodes") => "more",
                    ),
                ),
                array(
                    "type" => "vc_link",
                    "class" => "",
                    "heading" => __("Add Link", "porto-shortcodes"),
                    "param_name" => "link",
                    "value" => "",
                    "description" => __("Add a custom link or select existing page.", "porto-shortcodes"),
                    "dependency" => array("element" => "read_more", "value" => array("box","title","more")),
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Read More Text", "porto-shortcodes"),
                    "param_name" => "read_text",
                    "value" => "Read More",
                    "description" => __("Customize the read more text.", "porto-shortcodes"),
                    "dependency" => array("element" => "read_more","value" => array("more")),
                ),
                array(
                    "type" => "dropdown",
                    "class" => "",
                    "heading" => __("Select Hover Effect type", "porto-shortcodes"),
                    "param_name" => "hover_effect",
                    "value" => array(
                        __("No Effect","porto-shortcodes") => "style_1",
                        __("Icon Zoom","porto-shortcodes") => "style_2",
                        __("Icon Bounce Up","porto-shortcodes") => "style_3",
                    ),
                    "description" => __("Select the type of effct you want on hover", "porto-shortcodes")
                ),
                array(
                    "type" => "dropdown",
                    "class" => "",
                    "heading" => __("Box Style", "porto-shortcodes"),
                    "param_name" => "pos",
                    "value" => array(
                        __("Icon at Left with heading","porto-shortcodes") => "default",
                        __("Icon at Right with heading","porto-shortcodes") => "heading-right",
                        __("Icon at Left","porto-shortcodes") => "left",
                        __("Icon at Right","porto-shortcodes") => "right",
                        __("Icon at Top","porto-shortcodes") => "top",
                        __("Boxed Style","porto-shortcodes") => "square_box",
                    ),
                    "description" => __("Select icon position. Icon box style will be changed according to the icon position.", "porto-shortcodes")
                ),
                $custom_class,
                array(
                    "type" => "porto_param_heading",
                    "param_name" => "title_text_typography",
                    "heading" => __("Title settings","porto-shortcodes"),
                    "value" => "",
                    "group" => "Typography",
                    'edit_field_class' => 'no-top-margin vc_column vc_col-sm-12',
                ),
                array(
                    "type" => "dropdown",
                    "heading" => __("Tag","porto-shortcodes"),
                    "param_name" => "heading_tag",
                    "value" => array(
                        __("Default","porto-shortcodes") => "h3",
                        __("H1","porto-shortcodes") => "h1",
                        __("H2","porto-shortcodes") => "h2",
                        __("H4","porto-shortcodes") => "h4",
                        __("H5","porto-shortcodes") => "h5",
                        __("H6","porto-shortcodes") => "h6",
                    ),
                    "description" => __("Default is H3", "porto-shortcodes"),
                    "group" => "Typography"
                ),
                array(
                    "type" => "dropdown",
                    "heading" => __("Font Family","porto-shortcodes"),
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
                    "class" => "",
                    "heading" => __("Font size", 'porto-shortcodes'),
                    "param_name" => "title_font_size",
                    "unit" => "px",
                    "media" => array(
                        "Desktop" => '',
                        "Tablet" => '',
                        "Tablet Portrait" => '',
                        "Mobile Landscape" => '',
                        "Mobile" => '',
                    ),
                    "group" => "Typography",
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Line Height", 'porto-shortcodes'),
                    "param_name" => "title_font_line_height",
                    "unit" => "px",
                    "media" => array(
                        "Desktop" => '',
                        "Tablet" => '',
                        "Tablet Portrait" => '',
                        "Mobile Landscape" => '',
                        "Mobile" => '',
                    ),
                    "group" => "Typography",
                ),
                array(
                    "type" => "colorpicker",
                    "param_name" => "title_font_color",
                    "heading" => __("Color","porto-shortcodes"),
                    "group" => "Typography"
                ),
                array(
                    "type" => "porto_param_heading",
                    "param_name" => "subtitle_text_typography",
                    "heading" => __("Sub title settings","porto-shortcodes"),
                    "value" => "",
                    "group" => "Typography",
                    'edit_field_class' => 'no-top-margin vc_column vc_col-sm-12',
                ),
                array(
                    "type" => "textfield",
                    "heading" => __("Font Weight","porto-shortcodes"),
                    "param_name" => "subtitle_font_style",
                    "value" => "",
                    "group" => "Typography"
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Font size", 'porto-shortcodes'),
                    "param_name" => "subtitle_font_size",
                    "unit" => "px",
                    "media" => array(
                        "Desktop" => '',
                        "Tablet" => '',
                        "Tablet Portrait" => '',
                        "Mobile Landscape" => '',
                        "Mobile" => '',
                    ),
                    "group" => "Typography",
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Line Height", 'porto-shortcodes'),
                    "param_name" => "subtitle_font_line_height",
                    "unit" => "px",
                    "media" => array(
                        "Desktop" => '',
                        "Tablet" => '',
                        "Tablet Portrait" => '',
                        "Mobile Landscape" => '',
                        "Mobile" => '',
                    ),
                    "group" => "Typography",
                ),
                array(
                    "type" => "colorpicker",
                    "param_name" => "subtitle_font_color",
                    "heading" => __("Color","porto-shortcodes"),
                    "group" => "Typography"
                ),
                array(
                    "type" => "porto_param_heading",
                    "param_name" => "desc_text_typography",
                    "heading" => __("Description settings","porto-shortcodes"),
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
                    "class" => "",
                    "heading" => __("Font size", 'porto-shortcodes'),
                    "param_name" => "desc_font_size",
                    "unit" => "px",
                    "media" => array(
                        "Desktop" => '',
                        "Tablet" => '',
                        "Tablet Portrait" => '',
                        "Mobile Landscape" => '',
                        "Mobile" => '',
                    ),
                    "group" => "Typography",
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Line Height", 'porto-shortcodes'),
                    "param_name" => "desc_font_line_height",
                    "unit" => "px",
                    "media" => array(
                        "Desktop" => '',
                        "Tablet" => '',
                        "Tablet Portrait" => '',
                        "Mobile Landscape" => '',
                        "Mobile" => '',
                    ),
                    "group" => "Typography",
                ),
                array(
                    "type" => "colorpicker",
                    "param_name" => "desc_font_color",
                    "heading" => __("Color","porto-shortcodes"),
                    "group" => "Typography"
                ),
               array(
                    'type' => 'css_editor',
                    'heading' => __( 'Css', 'porto-shortcodes' ),
                    'param_name' => 'css_info_box',
                    'group' => __( 'Design ', 'porto-shortcodes' ),
                    'edit_field_class' => 'vc_col-sm-12 vc_column no-vc-background no-vc-border creative_link_css_editor',
                ),
            )
        )
    );
}