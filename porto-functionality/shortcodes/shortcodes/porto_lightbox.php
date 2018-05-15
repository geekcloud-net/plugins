<?php

// Porto Lightbox
add_shortcode('porto_lightbox', 'porto_shortcode_lightbox');
add_action('vc_after_init', 'porto_load_lightbox_shortcode');

function porto_shortcode_lightbox($atts, $content = null) {
    ob_start();
    if ($template = porto_shortcode_template('porto_lightbox'))
        include $template;
    return ob_get_clean();
}

function porto_load_lightbox_shortcode() {
    $animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();

    vc_map( array(
        'name' => "Porto " . __('Lightbox', 'porto-shortcodes'),
        'base' => 'porto_lightbox',
        'category' => __('Porto', 'porto-shortcodes'),
        'icon' => 'porto_vc_lightbox',
        "content_element" => true,
        "controls" => "full",
        'is_container' => true,
        'js_view' => 'VcColumnView',
        'params' => array(
            array(
                'type' => 'textfield',
                'heading' => __('Prefix', 'porto-shortcodes'),
                'param_name' => 'prefix'
            ),
            array(
                'type' => 'textfield',
                'heading' => __('Text', 'porto-shortcodes'),
                'param_name' => 'text',
                'admin_label' => true,
            ),
            array(
                'type' => 'textfield',
                'heading' => __('Suffix', 'porto-shortcodes'),
                'param_name' => 'suffix'
            ),
            array(
                'type' => 'dropdown',
                'heading' => __( 'Display Type', 'porto-shortcodes' ),
                'param_name' => 'display',
                'value' => array(
                    __( 'Inline', 'porto-shortcodes' )=> '',
                    __( 'Block', 'porto-shortcodes' )=> 'block'
                )
            ),
            array(
                'type' => 'dropdown',
                'heading' => __( 'Text Type', 'porto-shortcodes' ),
                'param_name' => 'type',
                'value' => array(
                    __( 'Link', 'porto-shortcodes' )=> '',
                    __( 'Button', 'porto-shortcodes' )=> 'btn'
                )
            ),
            array(
                'type' => 'dropdown',
                'heading' => __( 'Button Size', 'porto-shortcodes' ),
                'param_name' => 'btn_size',
                'value' => porto_sh_commons('size'),
                'dependency' => array('element' => 'type', 'value' => array('btn'))
            ),
            array(
                'type' => 'dropdown',
                'heading' => __( 'Button Skin Color', 'porto-shortcodes' ),
                'param_name' => 'btn_skin',
                'value' => porto_sh_commons('colors'),
                'dependency' => array('element' => 'type', 'value' => array('btn'))
            ),
            array(
                'type' => 'dropdown',
                'heading' => __( 'Button Contextual Class', 'porto-shortcodes' ),
                'param_name' => 'btn_context',
                'value' => porto_sh_commons('contextual'),
                'dependency' => array('element' => 'type', 'value' => array('btn'))
            ),
            array(
                'type' => 'dropdown',
                'heading' => __( 'Lightbox Type', 'porto-shortcodes' ),
                'param_name' => 'lightbox_type',
                'value' => array(
                    __( 'Content', 'porto-shortcodes' )=> '',
                    __( 'Video or Google Map', 'porto-shortcodes' )=> 'iframe',
                    __( 'Ajax', 'porto-shortcodes' )=> 'ajax',
                )
            ),
            array(
                'type' => 'textfield',
                'heading' => __( 'Video or Google Map Url', 'porto-shortcodes' ),
                'param_name' => 'iframe_url',
                'dependency' => array('element' => 'lightbox_type', 'value' => array('iframe'))
            ),
            array(
                'type' => 'textfield',
                'heading' => __( 'Ajax Url', 'porto-shortcodes' ),
                'param_name' => 'ajax_url',
                'dependency' => array('element' => 'lightbox_type', 'value' => array('ajax'))
            ),
            array(
                'type' => 'dropdown',
                'heading' => __( 'Lightbox Animation', 'porto-shortcodes' ),
                'param_name' => 'lightbox_animation',
                'dependency' => array('element' => 'lightbox_type', 'value' => array('')),
                'value' => array(
                    __( 'None', 'porto-shortcodes' )=> '',
                    __( 'Fade Zoom', 'porto-shortcodes' )=> 'zoom-anim',
                    __( 'Fade Slide', 'porto-shortcodes' )=> 'move-anim',
                )
            ),
            array(
                'type' => 'dropdown',
                'heading' => __( 'Lightbox Size', 'porto-shortcodes' ),
                'param_name' => 'lightbox_size',
                'dependency' => array('element' => 'lightbox_type', 'value' => array('')),
                'value' => array(
                    __( 'Normal', 'porto-shortcodes' )=> '',
                    __( 'Large', 'porto-shortcodes' )=> 'lg',
                    __( 'Small', 'porto-shortcodes' )=> 'sm',
                    __( 'Extra Small', 'porto-shortcodes' )=> 'xs',
                )
            ),
            $custom_class,
            $animation_type,
            $animation_duration,
            $animation_delay
        )
    ) );

    if (!class_exists('WPBakeryShortCode_Porto_Lightbox')) {
        class WPBakeryShortCode_Porto_Lightbox extends WPBakeryShortCodesContainer {
        }
    }
}