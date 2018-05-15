<?php

// Porto Section
add_shortcode('porto_section', 'porto_shortcode_section');
add_action('vc_after_init', 'porto_load_section_shortcode');

function porto_shortcode_section($atts, $content = null) {
    ob_start();
    if ($template = porto_shortcode_template('porto_section'))
        include $template;
    return ob_get_clean();
}

function porto_load_section_shortcode() {
    $animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();

    vc_map( array(
        "name" => "Porto " . __("Section", 'porto-shortcodes'),
        "base" => "porto_section",
        "category" => __("Porto", 'porto-shortcodes'),
        "icon" => "porto_vc_section",
        "as_parent" => array('except' => 'porto_section'),
        "content_element" => true,
        "controls" => "full",
        //'is_container' => true,
        'js_view' => 'VcColumnView',
        "params" => array(
            array(
                "type" => "textfield",
                "heading" => __("Anchor Name", 'porto-shortcodes'),
                "param_name" => "anchor",
                "admin_label" => true
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Wrap as Container", 'porto-shortcodes'),
                'param_name' => 'container',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' )
            ),
            array(
                'type' => 'dropdown',
                'heading' => __('Section & Parallax Text Color', 'porto-shortcodes'),
                'param_name' => 'section_text_color',
                'value' => porto_sh_commons('section_text_color')
            ),
            array(
                'type' => 'dropdown',
                'heading' => __('Text Align', 'porto-shortcodes'),
                'param_name' => 'text_align',
                'value' => porto_sh_commons('align')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __('Is Section?', 'porto-shortcodes'),
                'param_name' => 'is_section',
                'value' => array(__('Yes, please', 'js_composer') => 'yes'),
                'admin_label' => true,
            ),
            array(
                'type' => 'dropdown',
                'heading' => __('Section Skin Color', 'porto-shortcodes'),
                'param_name' => 'section_skin',
                'value' => porto_sh_commons('section_skin'),
                'dependency' => array('element' => 'is_section', 'not_empty' => true)
            ),
            array(
                'type' => 'dropdown',
                'heading' => __('Section Default Color Scale', 'porto-shortcodes'),
                'param_name' => 'section_color_scale',
                'value' => porto_sh_commons('section_color_scale'),
                'dependency' => array('element' => 'section_skin', 'value' => array('default'))
            ),
            array(
                'type' => 'dropdown',
                'heading' => __('Section Color Scale', 'porto-shortcodes'),
                'param_name' => 'section_skin_scale',
                'dependency' => array('element' => 'section_skin', 'value' => array('primary','secondary','tertiary','quaternary','dark','light')),
                'value' => array(
                    __( 'Default', 'porto-shortcodes' ) => '',
                    __( 'Scale 2', 'porto-shortcodes' ) => 'scale-2'
                )
            ),
            array(
                'type' => 'checkbox',
                'heading' => __('Remove Margin Top', 'porto-shortcodes'),
                'param_name' => 'remove_margin_top',
                'value' => array(__('Yes, please', 'js_composer') => 'yes'),
                'dependency' => array('element' => 'is_section', 'not_empty' => true)
            ),
            array(
                'type' => 'checkbox',
                'heading' => __('Remove Margin Bottom', 'porto-shortcodes'),
                'param_name' => 'remove_margin_bottom',
                'value' => array(__('Yes, please', 'js_composer') => 'yes'),
                'dependency' => array('element' => 'is_section', 'not_empty' => true)
            ),
            array(
                'type' => 'checkbox',
                'heading' => __('Remove Padding Top', 'porto-shortcodes'),
                'param_name' => 'remove_padding_top',
                'value' => array(__('Yes, please', 'js_composer') => 'yes'),
                'dependency' => array('element' => 'is_section', 'not_empty' => true)
            ),
            array(
                'type' => 'checkbox',
                'heading' => __('Remove Padding Bottom', 'porto-shortcodes'),
                'param_name' => 'remove_padding_bottom',
                'value' => array(__('Yes, please', 'js_composer') => 'yes'),
                'dependency' => array('element' => 'is_section', 'not_empty' => true)
            ),
            array(
                'type' => 'checkbox',
                'heading' => __('Remove Border', 'porto-shortcodes'),
                'param_name' => 'remove_border',
                'value' => array(__('Yes, please', 'js_composer') => 'yes'),
                'dependency' => array('element' => 'is_section', 'not_empty' => true)
            ),
            array(
                'type' => 'checkbox',
                'heading' => __('Show Divider', 'porto-shortcodes'),
                'param_name' => 'show_divider',
                'value' => array(__('Yes, please', 'js_composer') => 'yes'),
                'dependency' => array('element' => 'is_section', 'not_empty' => true)
            ),
            array(
                'type' => 'dropdown',
                'heading' => __('Divider Position', 'porto-shortcodes'),
                'param_name' => 'divider_pos',
                'value' => array(
                    __('Top', 'porto-shortcodes') => '',
                    __('Bottom', 'porto-shortcodes') => 'bottom',
                ),
                'dependency' => array('element' => 'show_divider', 'not_empty' => true)
            ),
            array(
                'type' => 'colorpicker',
                'heading' => __( 'Divider Color', 'porto-shortcodes' ),
                'param_name' => 'divider_color',
                'dependency' => array('element' => 'show_divider', 'not_empty' => true)
            ),
            array(
                'type' => 'textfield',
                'heading' => __('Divider Height', 'porto-shortcodes'),
                'param_name' => 'divider_height',
                'dependency' => array('element' => 'show_divider', 'not_empty' => true)
            ),
            array(
                'type' => 'checkbox',
                'heading' => __('Show Divider Icon', 'porto-shortcodes'),
                'param_name' => 'show_divider_icon',
                'value' => array(__('Yes, please', 'js_composer') => 'yes'),
                'dependency' => array('element' => 'show_divider', 'not_empty' => true)
            ),
            array(
                'type' => 'dropdown',
                'heading' => __( 'Icon library', 'js_composer' ),
                'value' => array(
                    __( 'Font Awesome', 'porto-shortcodes' ) => 'fontawesome',
                    __( 'Simple Line Icon', 'porto-shortcodes' ) => 'simpleline',
                    __( 'Custom Image Icon', 'porto-shortcodes' ) => 'image'
                ),
                'param_name' => 'divider_icon_type',
                'dependency' => array('element' => 'show_divider_icon', 'not_empty' => true)
            ),
            array(
                'type' => 'attach_image',
                'heading' => __('Select Icon', 'porto-shortcodes'),
                'dependency' => array('element' => 'divider_icon_type', 'value' => 'image'),
                'param_name' => 'divider_icon_image'
            ),
            array(
                'type' => 'iconpicker',
                'heading' => __('Select Icon', 'porto-shortcodes'),
                'param_name' => 'divider_icon',
                'dependency' => array('element' => 'divider_icon_type', 'value' => 'fontawesome')
            ),
            array(
                'type' => 'iconpicker',
                'heading' => __('Select Icon', 'porto-shortcodes'),
                'param_name' => 'divider_icon_simpleline',
                'value' => '',
                'settings' => array(
                    'type' => 'simpleline',
                    'iconsPerPage' => 4000,
                ),
                'dependency' => array('element' => 'divider_icon_type', 'value' => 'simpleline')
            ),
            array(
                'type' => 'dropdown',
                'heading' => __('Icon Skin Color', 'porto-shortcodes'),
                'param_name' => 'divider_icon_skin',
                'std' => 'custom',
                'value' => porto_sh_commons('colors'),
                'dependency' => array('element' => 'show_divider_icon', 'not_empty' => true)
            ),
            array(
                'type' => 'colorpicker',
                'heading' => __('Icon Color', 'porto-shortcodes'),
                'param_name' => 'divider_icon_color',
                'dependency' => array('element' => 'divider_icon_skin', 'value' => array('custom'))
            ),
            array(
                'type' => 'colorpicker',
                'heading' => __('Icon Background Color', 'porto-shortcodes'),
                'param_name' => 'divider_icon_bg_color',
                'dependency' => array('element' => 'divider_icon_skin', 'value' => array('custom'))
            ),
            array(
                'type' => 'colorpicker',
                'heading' => __('Icon Border Color', 'porto-shortcodes'),
                'param_name' => 'divider_icon_border_color',
                'dependency' => array('element' => 'divider_icon_skin', 'value' => array('custom'))
            ),
            array(
                'type' => 'colorpicker',
                'heading' => __('Icon Wrap Border Color', 'porto-shortcodes'),
                'param_name' => 'divider_icon_wrap_border_color',
                'dependency' => array('element' => 'divider_icon_skin', 'value' => array('custom'))
            ),
            array(
                'type' => 'dropdown',
                'heading' => __('Icon Style', 'porto-shortcodes'),
                'param_name' => 'divider_icon_style',
                'value' => porto_sh_commons('separator_icon_style'),
                'dependency' => array('element' => 'show_divider_icon', 'not_empty' => true)
            ),
            array(
                'type' => 'dropdown',
                'heading' => __('Icon Position', 'porto-shortcodes'),
                'param_name' => 'divider_icon_pos',
                'value' => porto_sh_commons('separator_icon_pos'),
                'dependency' => array('element' => 'show_divider_icon', 'not_empty' => true)
            ),
            array(
                'type' => 'dropdown',
                'heading' => __('Icon Size', 'porto-shortcodes'),
                'param_name' => 'divider_icon_size',
                'value' => porto_sh_commons('separator_icon_size'),
                'dependency' => array('element' => 'show_divider_icon', 'not_empty' => true)
            ),
            $custom_class,
            $animation_type,
            $animation_duration,
            $animation_delay
        )
    ) );

    if (!class_exists('WPBakeryShortCode_Porto_Section')) {
        class WPBakeryShortCode_Porto_Section extends WPBakeryShortCodesContainer {
        }
    }
}