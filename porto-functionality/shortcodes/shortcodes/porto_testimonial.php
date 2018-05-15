<?php
// Porto Testimonial
add_shortcode('porto_testimonial', 'porto_shortcode_testimonial');
add_action('vc_after_init', 'porto_load_testimonial_shortcode');
function porto_shortcode_testimonial($atts, $content = null) {
    ob_start();
    if ($template = porto_shortcode_template('porto_testimonial'))
        include $template;
    return ob_get_clean();
}
function porto_load_testimonial_shortcode() {
    $animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();
    vc_map( array(
        'name' => "Porto " . __('Testimonial', 'porto-shortcodes'),
        'base' => 'porto_testimonial',
        'category' => __('Porto', 'porto-shortcodes'),
        'icon' => 'porto_vc_testimonial',
        'params' => array(
            array(
                'type' => 'textfield',
                'heading' => __('Name', 'porto-shortcodes'),
                'param_name' => 'name'
            ),
            array(
                'type' => 'colorpicker',
                'heading' => __('Name Color', 'porto-shortcodes'),
                'param_name' => 'name_color'
            ),
            array(
                'type' => 'textfield',
                'heading' => __('Role', 'porto-shortcodes'),
                'param_name' => 'role'
            ),
            array(
                'type' => 'textfield',
                'heading' => __('Company', 'porto-shortcodes'),
                'param_name' => 'company'
            ),
            array(
                'type' => 'colorpicker',
                'heading' => __('Role & Company Color', 'porto-shortcodes'),
                'param_name' => 'role_company_color'
            ),
            array(
                'type' => 'textfield',
                'heading' => __('Author Link', 'porto-shortcodes'),
                'param_name' => 'author_url'
            ),
            array(
                'type' => 'label',
                'heading' => __('Input Photo URL or Select Photo.', 'porto-shortcodes'),
                'param_name' => 'label'
            ),
            array(
                'type' => 'textfield',
                'heading' => __('Photo URL', 'porto-shortcodes'),
                'param_name' => 'photo_url'
            ),
            array(
                'type' => 'attach_image',
                'heading' => __('Photo', 'porto-shortcodes'),
                'param_name' => 'photo_id'
            ),
            array(
                'type' => 'textarea_html',
                'heading' => __('Quote', 'porto-shortcodes'),
                'param_name' => 'content',
                'admin_label' => true,
            ),
            array(
                'type' => 'colorpicker',
                'heading' => __('Quote Color', 'porto-shortcodes'),
                'param_name' => 'quote_color'
            ),
            array(
                'type' => 'dropdown',
                'heading' => __( 'View Type', 'porto-shortcodes' ),
                'param_name' => 'view',
                'value' => array(
                    __( 'Default', 'porto-shortcodes' )=> 'default',
                    __( 'Default - Author on top', 'porto-shortcodes' )=> 'default2',
                    __( 'Simple', 'porto-shortcodes' )=> 'simple',
                    __( 'Advance', 'porto-shortcodes' )=> 'advance',
                    __( 'With Quotes', 'porto-shortcodes' ) => 'transparent'
                )
            ),
            array(
                'type' => 'dropdown',
                'heading' => __( 'Style Type', 'porto-shortcodes' ),
                'param_name' => 'style',
                'std' => '',
                'value' => porto_sh_commons('testimonial_styles'),
                'dependency' => array(
                    'element' => 'view',
                    'value' => array( 'default', 'default2', 'transparent' )
                )
            ),
            array(
                'type' => 'checkbox',
                'heading' => __('Remove Border', 'porto-shortcodes'),
                'param_name' => 'remove_border',
                'value' => array(__('Yes, please', 'js_composer') => 'yes'),
            ),
            array(
                'type' => 'checkbox',
                'heading' => __('Remove Background', 'porto-shortcodes'),
                'param_name' => 'remove_bg',
                'value' => array(__('Yes, please', 'js_composer') => 'yes'),
                'dependency' => array(
                    'element' => 'view',
                    'value' => array( 'default', 'default2' )
                ),
            ),
            array(
                'type' => 'checkbox',
                'heading' => __('Show with Alternative Font', 'porto-shortcodes'),
                'param_name' => 'alt_font',
                'value' => array(__('Yes, please', 'js_composer') => 'yes'),
                'dependency' => array(
                    'element' => 'view',
                    'value' => array( 'default', 'default2' )
                ),
            ),
            array(
                'type' => 'dropdown',
                'heading' => __( 'Skin Color', 'porto-shortcodes' ),
                'param_name' => 'skin',
                'std' => 'custom',
                'value' => porto_sh_commons('colors'),
                'dependency' => array(
                    'element' => 'style',
                    'value' => array( '' )
                )
            ),
            array(
                'type' => 'dropdown',
                'heading' => __( 'Skin Color', 'porto-shortcodes' ),
                'param_name' => 'color',
                'value' => array(
                    __( 'Normal', 'porto-shortcodes' )=> '',
                    __( 'White', 'porto-shortcodes' ) => 'white'
                ),
                'dependency' => array(
                    'element' => 'view',
                    'value' => array( 'transparent', 'simple' )
                )
            ),
            $custom_class,
            $animation_type,
            $animation_duration,
            $animation_delay
        )
    ) );
    if (!class_exists('WPBakeryShortCode_Porto_Testimonial')) {
        class WPBakeryShortCode_Porto_Testimonial extends WPBakeryShortCode {
        }
    }
}