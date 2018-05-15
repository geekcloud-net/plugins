<?php

// Porto Sticky Nav Link
add_shortcode('porto_sticky_nav_link', 'porto_shortcode_sticky_nav_link');
add_action('vc_after_init', 'porto_load_sticky_nav_link_shortcode');

function porto_shortcode_sticky_nav_link($atts, $content = null) {
    ob_start();
    if ($template = porto_shortcode_template('porto_sticky_nav_link'))
        include $template;
    return ob_get_clean();
}

function porto_load_sticky_nav_link_shortcode() {
    $custom_class = porto_vc_custom_class();

    vc_map( array(
        "name" => "Porto " . __("Sticky Nav Link", 'porto-shortcodes'),
        "base" => "porto_sticky_nav_link",
        "category" => __("Porto", 'porto-shortcodes'),
        "icon" => "porto_vc_sticky_nav_link",
        "as_child" => array('only' => 'porto_sticky_nav'),
        "params" => array(
            array(
                "type" => "textfield",
                "heading" => __("Label", 'porto-shortcodes'),
                "param_name" => "label",
                "admin_label" => true
            ),
            array(
                "type" => "textfield",
                "heading" => __("Link", 'porto-shortcodes'),
                "param_name" => "link",
                "admin_label" => true
            ),
            array(
                'type' => 'checkbox',
                'heading' => __('Show Icon', 'porto-shortcodes'),
                'param_name' => 'show_icon',
                'value' => array(__('Yes, please', 'js_composer') => 'yes')
            ),
            array(
                'type' => 'dropdown',
                'heading' => __( 'Icon library', 'js_composer' ),
                'value' => array(
                    __( 'Font Awesome', 'porto-shortcodes' ) => 'fontawesome',
                    __( 'Simple Line Icon', 'porto-shortcodes' ) => 'simpleline',
                    __( 'Custom Image Icon', 'porto-shortcodes' ) => 'image'
                ),
                'param_name' => 'icon_type',
                'dependency' => array('element' => 'show_icon', 'not_empty' => true)
            ),
            array(
                'type' => 'attach_image',
                'heading' => __('Select Icon', 'porto-shortcodes'),
                'dependency' => array('element' => 'icon_type', 'value' => 'image'),
                'param_name' => 'icon_image'
            ),
            array(
                'type' => 'iconpicker',
                'heading' => __('Select Icon', 'porto-shortcodes'),
                'param_name' => 'icon',
                'dependency' => array('element' => 'icon_type', 'value' => 'fontawesome')
            ),
            array(
                'type' => 'iconpicker',
                'heading' => __('Select Icon', 'porto-shortcodes'),
                'param_name' => 'icon_simpleline',
                'value' => '',
                'settings' => array(
                    'type' => 'simpleline',
                    'iconsPerPage' => 4000,
                ),
                'dependency' => array('element' => 'icon_type', 'value' => 'simpleline')
            ),
            array(
                'type' => 'dropdown',
                'heading' => __('Skin Color', 'porto-shortcodes'),
                'param_name' => 'skin',
                'std' => 'custom',
                'value' => porto_sh_commons('colors')
            ),
            array(
                'type' => 'colorpicker',
                'heading' => __('Link Color', 'porto-shortcodes'),
                'param_name' => 'link_color',
                'dependency' => array('element' => 'skin', 'value' => array( 'custom' ))
            ),
            array(
                'type' => 'colorpicker',
                'heading' => __('Link Background Color', 'porto-shortcodes'),
                'param_name' => 'link_bg_color',
                'dependency' => array('element' => 'skin', 'value' => array( 'custom' ))
            ),
            array(
                'type' => 'colorpicker',
                'heading' => __('Link Active Color', 'porto-shortcodes'),
                'param_name' => 'link_acolor',
                'dependency' => array('element' => 'skin', 'value' => array( 'custom' ))
            ),
            array(
                'type' => 'colorpicker',
                'heading' => __('Link Active Background Color', 'porto-shortcodes'),
                'param_name' => 'link_abg_color',
                'dependency' => array('element' => 'skin', 'value' => array( 'custom' ))
            ),
            $custom_class
        )
    ) );

    if (!class_exists('WPBakeryShortCode_Porto_Sticky_Nav_Link')) {
        class WPBakeryShortCode_Porto_Sticky_Nav_Link extends WPBakeryShortCode {
        }
    }
}