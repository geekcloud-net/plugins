<?php

// Porto Image Frame
add_shortcode('porto_image_frame', 'porto_shortcode_image_frame');
add_action('vc_after_init', 'porto_load_image_frame_shortcode');

function porto_shortcode_image_frame($atts, $content = null) {
    ob_start();
    if ($template = porto_shortcode_template('porto_image_frame'))
        include $template;
    return ob_get_clean();
}

function porto_load_image_frame_shortcode() {
    $animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();

    vc_map( array(
        "name" => "Porto " . __("Image Frame", 'porto-shortcodes'),
        "base" => "porto_image_frame",
        "category" => __("Porto", 'porto-shortcodes'),
        "icon" => "porto_vc_image_frame",
        "params" => array(
            array(
                'type' => 'dropdown',
                'heading' => __( 'Type', 'porto-shortcodes' ),
                'param_name' => 'type',
                'value' => array(
                    __( 'Default', 'porto-shortcodes' )=> '',
                    __( 'Hover Style', 'porto-shortcodes' )=> 'hover-style'
                )
            ),
            array(
                'type' => 'dropdown',
                'heading' => __( 'Shape', 'porto-shortcodes' ),
                'param_name' => 'shape',
                'dependency' => array(
                    'element' => 'type',
                    'value' => array( '' )
                ),
                'value' => array(
                    __( 'Rounded', 'porto-shortcodes' )=> 'rounded',
                    __( 'Circle', 'porto-shortcodes' )=> 'circle',
                    __( 'Thumbnail', 'porto-shortcodes' )=> 'thumbnail'
                )
            ),
            array(
                'type' => 'vc_link',
                'heading' => __( 'URL (Link)', 'porto-shortcodes' ),
                'param_name' => 'link'
            ),
            array(
                'type' => 'label',
                'heading' => __('Input Image URL or Select Image.', 'porto-shortcodes'),
                'param_name' => 'label'
            ),
            array(
                'type' => 'textfield',
                'heading' => __('Image URL', 'porto-shortcodes'),
                'param_name' => 'image_url'
            ),
            array(
                'type' => 'attach_image',
                'heading' => __('Image', 'porto-shortcodes'),
                'param_name' => 'image_id'
            ),
            array(
                'type' => 'textfield',
                'heading' => __('Title', 'porto-shortcodes'),
                'param_name' => 'title',
                'dependency' => array(
                    'element' => 'type',
                    'value' => array( 'hover-style' )
                ),
                'admin_label' => true
            ),
            array(
                'type' => 'textfield',
                'heading' => __('Sub Title', 'porto-shortcodes'),
                'param_name' => 'sub_title',
                'dependency' => array(
                    'element' => 'type',
                    'value' => array( 'hover-style' )
                ),
                'admin_label' => true
            ),
            array(
                'type' => 'textfield',
                'heading' => __('Date', 'porto-shortcodes'),
                'param_name' => 'date',
                'dependency' => array(
                    'element' => 'type',
                    'value' => array( 'hover-style' )
                )
            ),
            array(
                'type' => 'textarea_html',
                'heading' => __('Description', 'porto-shortcodes'),
                'param_name' => 'content',
                'dependency' => array(
                    'element' => 'type',
                    'value' => array( 'hover-style' )
                )
            ),
            array(
                'type' => 'dropdown',
                'heading' => __('View Type', 'porto-shortcodes'),
                'param_name' => 'view_type',
                'dependency' => array(
                    'element' => 'type',
                    'value' => array( 'hover-style' )
                ),
                'value' => array(
                    __( 'Left Info', 'porto-shortcodes' )=> '',
                    __( 'Centered Info', 'porto-shortcodes' )=> 'centered-info',
                    __( 'Bottom Info', 'porto-shortcodes' )=> 'bottom-info',
                    __( 'Bottom Info Dark', 'porto-shortcodes' )=> 'bottom-info-dark',
                    __( 'Hide Info Hover', 'porto-shortcodes' )=> 'hide-info-hover',
                    __( 'Side Image Left', 'porto-shortcodes' ) => 'side-image',
                    __( 'Side Image Right', 'porto-shortcodes' ) => 'side-image-right'
                )
            ),
            array(
                'type' => 'textfield',
                'heading' => __('Image Max Width (unit: px)', 'porto-shortcodes'),
                'param_name' => 'img_width',
                'value' => '200',
                'dependency' => array('element' => 'view_type', 'value' => array('side-image', 'side-image-right'))
            ),
            array(
                "type" => "dropdown",
                "heading" => __("Align", 'porto-shortcodes'),
                "param_name" => "align",
                "value" => porto_sh_commons('align'),
                'dependency' => array(
                    'element' => 'type',
                    'value' => array( 'hover-style' )
                )
            ),
            array(
                'type' => 'dropdown',
                'heading' => __('Hover Background', 'porto-shortcodes'),
                'param_name' => 'hover_bg',
                'dependency' => array(
                    'element' => 'type',
                    'value' => array( 'hover-style' )
                ),
                'value' => array(
                    __( 'Darken', 'porto-shortcodes' )=> '',
                    __( 'Lighten', 'porto-shortcodes' )=> 'lighten',
                    __( 'Transparent', 'porto-shortcodes' )=> 'hide-wrapper-bg'
                )
            ),
            array(
                'type' => 'dropdown',
                'heading' => __('Hover Image Effect', 'porto-shortcodes'),
                'param_name' => 'hover_img',
                'dependency' => array(
                    'element' => 'type',
                    'value' => array( 'hover-style' )
                ),
                'value' => array(
                    __( 'Zoom', 'porto-shortcodes' )=> '',
                    __( 'No Zoom', 'porto-shortcodes' )=> 'no-zoom',
                    __( 'Push Horizontally', 'porto-shortcodes' )=> 'push-hor'
                )
            ),
            array(
                'type' => 'checkbox',
                'heading' => __('Disable Border', 'porto-shortcodes'),
                'param_name' => 'noborders',
                'dependency' => array(
                    'element' => 'type',
                    'value' => array( 'hover-style' )
                ),
                'value' => array(__('Yes, please', 'js_composer') => 'yes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __('Enable Box Shadow', 'porto-shortcodes'),
                'param_name' => 'boxshadow',
                'value' => array(__('Yes, please', 'js_composer') => 'yes'),
            ),
            array(
                'type' => 'checkbox',
                'heading' => __('Show URL (Link) Icon', 'porto-shortcodes'),
                'param_name' => 'link_icon',
                'dependency' => array(
                    'element' => 'type',
                    'value' => array( 'hover-style' )
                ),
                'std' => 'yes',
                'value' => array(__('Yes, please', 'js_composer') => 'yes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __('Show Centered Links', 'porto-shortcodes'),
                'param_name' => 'centered_icons',
                'dependency' => array(
                    'element' => 'type',
                    'value' => array( 'hover-style' )
                ),
                'value' => array(__('Yes, please', 'js_composer') => 'yes')
            ),
            array(
                'type' => 'param_group',
                'param_name' => 'icons',
                'dependency' => array('element' => 'centered_icons', 'not_empty' => true),
                'params' => array(
                    array(
                        'type' => 'dropdown',
                        'heading' => __( 'Icon library', 'js_composer' ),
                        'value' => array(
                            __( 'Font Awesome', 'porto-shortcodes' ) => 'fontawesome',
                            __( 'Simple Line Icon', 'porto-shortcodes' ) => 'simpleline',
                            __( 'Custom Image Icon', 'porto-shortcodes' ) => 'image'
                        ),
                        'param_name' => 'icon_type'
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
                        'value' => porto_sh_commons('colors')
                    ),
                    array(
                        'type' => 'colorpicker',
                        'heading' => __( 'Background Color', 'porto-shortcodes' ),
                        'param_name' => 'bg_color',
                        'dependency' => array(
                            'element' => 'skin',
                            'value' => array( 'custom' )
                        )
                    ),
                    array(
                        'type' => 'colorpicker',
                        'heading' => __( 'Icon Color', 'porto-shortcodes' ),
                        'param_name' => 'icon_color',
                        'dependency' => array(
                            'element' => 'skin',
                            'value' => array( 'custom' )
                        )
                    ),
                    array(
                        'type' => 'dropdown',
                        'heading' => __('Action Type', 'porto-shortcodes'),
                        'param_name' => 'action',
                        'value' => porto_sh_commons('popup_action')
                    ),
                    array(
                        'type' => 'vc_link',
                        'heading' => __( 'URL (Link)', 'porto-shortcodes' ),
                        'param_name' => 'open_link',
                        'dependency' => array('element' => 'action', 'value' => array( 'open_link' )),
                    ),
                    array(
                        'type' => 'textfield',
                        'heading' => __('Video or Map URL (Link)', 'porto-shortcodes'),
                        'param_name' => 'popup_iframe',
                        'dependency' => array('element' => 'action', 'value' => array( 'popup_iframe' )),
                    ),
                    array(
                        'type' => 'textarea',
                        'heading' => __('Popup Block', 'porto-shortcodes'),
                        'param_name' => 'popup_block',
                        'description' => __('Please add block slug name.', 'porto-shortcodes'),
                        'dependency' => array('element' => 'action', 'value' => array( 'popup_block' )),
                    ),
                    array(
                        'type' => 'dropdown',
                        'heading' => __('Popup Size', 'porto-shortcodes'),
                        'param_name' => 'popup_size',
                        'dependency' => array('element' => 'action', 'value' => array( 'popup_block' )),
                        'value' => array(
                            __( 'Medium', 'porto-shortcodes' )=> 'md',
                            __( 'Large', 'porto-shortcodes' )=> 'lg',
                            __( 'Small', 'porto-shortcodes' )=> 'sm',
                            __( 'Extra Small', 'porto-shortcodes' )=> 'xs'
                        )
                    ),
                    array(
                        'type' => 'dropdown',
                        'heading' => __('Popup Animation', 'porto-shortcodes'),
                        'param_name' => 'popup_animation',
                        'dependency' => array('element' => 'action', 'value' => array( 'popup_block' )),
                        'value' => array(
                            __( 'Fade', 'porto-shortcodes' )=> 'mfp-fade',
                            __( 'Zoom', 'porto-shortcodes' )=> 'mfp-with-zoom',
                            __( 'Fade Zoom', 'porto-shortcodes' )=> 'my-mfp-zoom-in',
                            __( 'Fade Slide', 'porto-shortcodes' )=> 'my-mfp-slide-bottom'
                        )
                    ),
                )
            ),
            array(
                'type' => 'checkbox',
                'heading' => __('Show Social Links', 'porto-shortcodes'),
                'param_name' => 'show_socials',
                'dependency' => array(
                    'element' => 'type',
                    'value' => array( 'hover-style' )
                ),
                'value' => array(__('Yes, please', 'js_composer') => 'yes')
            ),
            array(
                'type' => 'param_group',
                'param_name' => 'socials',
                'dependency' => array('element' => 'show_socials', 'not_empty' => true),
                'params' => array(
                    array(
                        'type' => 'dropdown',
                        'heading' => __( 'Icon library', 'js_composer' ),
                        'value' => array(
                            __( 'Font Awesome', 'porto-shortcodes' ) => 'fontawesome',
                            __( 'Simple Line Icon', 'porto-shortcodes' ) => 'simpleline',
                            __( 'Custom Image Icon', 'porto-shortcodes' ) => 'image'
                        ),
                        'param_name' => 'icon_type'
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
                        'value' => porto_sh_commons('colors')
                    ),
                    array(
                        'type' => 'colorpicker',
                        'heading' => __( 'Background Color', 'porto-shortcodes' ),
                        'param_name' => 'bg_color',
                        'dependency' => array(
                            'element' => 'skin',
                            'value' => array( 'custom' )
                        )
                    ),
                    array(
                        'type' => 'colorpicker',
                        'heading' => __( 'Icon Color', 'porto-shortcodes' ),
                        'param_name' => 'icon_color',
                        'dependency' => array(
                            'element' => 'skin',
                            'value' => array( 'custom' )
                        )
                    ),
                    array(
                        'type' => 'dropdown',
                        'heading' => __('Action Type', 'porto-shortcodes'),
                        'param_name' => 'action',
                        'value' => porto_sh_commons('popup_action')
                    ),
                    array(
                        'type' => 'vc_link',
                        'heading' => __( 'URL (Link)', 'porto-shortcodes' ),
                        'param_name' => 'open_link',
                        'dependency' => array('element' => 'action', 'value' => array( 'open_link' )),
                    ),
                    array(
                        'type' => 'textfield',
                        'heading' => __('Video or Map URL (Link)', 'porto-shortcodes'),
                        'param_name' => 'popup_iframe',
                        'dependency' => array('element' => 'action', 'value' => array( 'popup_iframe' )),
                    ),
                    array(
                        'type' => 'textarea',
                        'heading' => __('Popup Block', 'porto-shortcodes'),
                        'param_name' => 'popup_block',
                        'description' => __('Please add block slug name.', 'porto-shortcodes'),
                        'dependency' => array('element' => 'action', 'value' => array( 'popup_block' )),
                    ),
                    array(
                        'type' => 'dropdown',
                        'heading' => __('Popup Size', 'porto-shortcodes'),
                        'param_name' => 'popup_size',
                        'dependency' => array('element' => 'action', 'value' => array( 'popup_block' )),
                        'value' => array(
                            __( 'Medium', 'porto-shortcodes' )=> 'md',
                            __( 'Large', 'porto-shortcodes' )=> 'lg',
                            __( 'Small', 'porto-shortcodes' )=> 'sm',
                            __( 'Extra Small', 'porto-shortcodes' )=> 'xs'
                        )
                    ),
                    array(
                        'type' => 'dropdown',
                        'heading' => __('Popup Animation', 'porto-shortcodes'),
                        'param_name' => 'popup_animation',
                        'dependency' => array('element' => 'action', 'value' => array( 'popup_block' )),
                        'value' => array(
                            __( 'Fade', 'porto-shortcodes' )=> 'mfp-fade',
                            __( 'Zoom', 'porto-shortcodes' )=> 'mfp-with-zoom',
                            __( 'Fade Zoom', 'porto-shortcodes' )=> 'my-mfp-zoom-in',
                            __( 'Fade Slide', 'porto-shortcodes' )=> 'my-mfp-slide-bottom'
                        )
                    ),
                )
            ),
            $custom_class,
            $animation_type,
            $animation_duration,
            $animation_delay
        )
    ) );

    if (!class_exists('WPBakeryShortCode_Porto_Image_Frame')) {
        class WPBakeryShortCode_Porto_Image_Frame extends WPBakeryShortCode {
        }
    }
}