<?php

// Porto Recent Posts
add_shortcode('porto_recent_posts', 'porto_shortcode_recent_posts');
add_action('vc_after_init', 'porto_load_recent_posts_shortcode');

function porto_shortcode_recent_posts($atts, $content = null) {
    ob_start();
    if ($template = porto_shortcode_template('porto_recent_posts'))
        include $template;
    return ob_get_clean();
}

function porto_load_recent_posts_shortcode() {
    $animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();

    vc_map( array(
        'name' => "Porto " . __('Recent Posts', 'porto-shortcodes'),
        'base' => 'porto_recent_posts',
        'category' => __('Porto', 'porto-shortcodes'),
        'icon' => 'porto_vc_recent_posts',
        "params" => array(
            array(
                "type" => "textfield",
                "heading" => __("Title", 'porto-shortcodes'),
                "param_name" => "title",
                "admin_label" => true
            ),
            array(
                "type" => "dropdown",
                "heading" => __("View Type", 'porto-shortcodes'),
                "param_name" => "view",
                'std' => '',
                "value" => array(
                    __('Standard', 'porto-shortcodes' ) => '',
                    __('Read More Link', 'porto-shortcodes' ) => 'style-1',
                    __('Post Meta', 'porto-shortcodes' ) => 'style-2',
                    __('Read More Button', 'porto-shortcodes' ) => 'style-3',
                    __('Side Image', 'porto-shortcodes' ) => 'style-4',
                    __('Post Cats', 'porto-shortcodes' ) => 'style-5',
                ),
            ),
            array(
                "type" => "dropdown",
                "heading" => __("Author Name", 'porto-shortcodes'),
                "param_name" => "author",
                'dependency' => array('element' => 'view','value' => array( 'style-1', 'style-3' )),
                'std' => '',
                "value" => array(
                    __('Standard', 'porto-shortcodes' ) => '',
                    __('Show', 'porto-shortcodes' ) => 'show',
                    __('Hide', 'porto-shortcodes' ) => 'hide',
                ),
            ),
            array(
                "type" => "dropdown",
                "heading" => __("Button Style", 'porto-shortcodes'),
                "param_name" => "btn_style",
                'dependency' => array('element' => 'view','value' => array( 'style-3' )),
                'std' => '',
                "value" => array(
                    __('Standard', 'porto-shortcodes' ) => '',
                    __('Normal', 'porto-shortcodes' ) => 'btn-normal',
                    __('Borders', 'porto-shortcodes' ) => 'btn-borders',
                ),
            ),
            array(
                "type" => "dropdown",
                "heading" => __("Button Size", 'porto-shortcodes'),
                "param_name" => "btn_size",
                'dependency' => array('element' => 'view','value' => array( 'style-3' )),
                'std' => '',
                "value" => array(
                    __('Standard', 'porto-shortcodes' ) => '',
                    __('Normal', 'porto-shortcodes' ) => 'btn-normal',
                    __('Small', 'porto-shortcodes' ) => 'btn-sm',
                    __('Extra Small', 'porto-shortcodes' ) => 'btn-xs',
                ),
            ),
            array(
                "type" => "dropdown",
                "heading" => __("Button Color", 'porto-shortcodes'),
                "param_name" => "btn_color",
                'dependency' => array('element' => 'view','value' => array( 'style-3' )),
                'std' => '',
                "value" => array(
                    __('Standard', 'porto-shortcodes' ) => '',
                    __('Default', 'porto-shortcodes' ) => 'btn-default',
                    __('Primary', 'porto-shortcodes' ) => 'btn-primary',
                    __('Secondary', 'porto-shortcodes' ) => 'btn-secondary',
                    __('Tertiary', 'porto-shortcodes' ) => 'btn-tertiary',
                    __('Quaternary', 'porto-shortcodes' ) => 'btn-quaternary',
                    __('Dark', 'porto-shortcodes' ) => 'btn-dark',
                    __('Light', 'porto-shortcodes' ) => 'btn-light',
                ),
            ),
            array(
                "type" => "textfield",
                "heading" => __("Image Size", 'porto-shortcodes'),
                "param_name" => "image_size",
                'std' => '',
                'description' => __('Enter image size (Example: "thumbnail", "medium", "large", "full" or other sizes defined by theme). Alternatively enter size in pixels (Example: 200x100 (Width x Height)).', 'js_composer')
            ),
            array(
                "type" => "textfield",
                "heading" => __("Posts Count", 'porto-shortcodes'),
                "param_name" => "number",
                "value" => "8",
                "admin_label" => true
            ),
            array(
                "type" => "textfield",
                "heading" => __("Category IDs", 'porto-shortcodes'),
                "param_name" => "cats",
                "admin_label" => true
            ),
            array(
                'type' => 'checkbox',
                'heading' => __( 'Show Post Image', 'porto-shortcodes' ),
                'param_name' => 'show_image',
                'std' => 'yes',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' )
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Show Post Metas", 'porto-shortcodes'),
                'param_name' => 'show_metas',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                'std' => 'yes'
            ),
            array(
                "type" => "textfield",
                "heading" => __("Excerpt Length", 'porto-shortcodes'),
                "param_name" => "excerpt_length",
                "value" => "20"
            ),
            array(
                "type" => "textfield",
                "heading" => __("Items to show on Desktop", 'porto-shortcodes'),
                "param_name" => "items_desktop",
                "value" => "4"
            ),
            array(
                "type" => "textfield",
                "heading" => __("Items to show on Tablets", 'porto-shortcodes'),
                "param_name" => "items_tablets",
                "value" => "3"
            ),
            array(
                "type" => "textfield",
                "heading" => __("Items to show on Mobile", 'porto-shortcodes'),
                "param_name" => "items_mobile",
                "value" => "2"
            ),
            array(
                "type" => "textfield",
                "heading" => __("Items Row", 'porto-shortcodes'),
                "param_name" => "items_row",
                "value" => "1"
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Change Slider Config", 'porto-shortcodes'),
                'param_name' => 'slider_config',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                "group" => __('Slider Options', 'porto-shortcodes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Show Nav", 'porto-shortcodes'),
                'param_name' => 'show_nav',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                'dependency' => array('element' => 'slider_config', 'not_empty' => true),
                "group" => __('Slider Options', 'porto-shortcodes')
            ),
            array(
                'type' => 'dropdown',
                'heading' => __("Nav Position", 'porto-shortcodes'),
                'param_name' => 'nav_pos',
                'value' => array(
                    __( 'Middle', 'porto-shortcodes' ) => '',
                    __( 'Top', 'porto-shortcodes' ) => 'show-nav-title',
                    __( 'Bottom', 'porto-shortcodes' ) => 'nav-bottom'
                ),
                'dependency' => array('element' => 'show_nav', 'not_empty' => true),
                "group" => __('Slider Options', 'porto-shortcodes')
            ),
            array(
                'type' => 'dropdown',
                'heading' => __("Nav Type", 'porto-shortcodes'),
                'param_name' => 'nav_type',
                'value' => array(
                    __( 'Default', 'porto-shortcodes' ) => '',
                    __( 'Rounded', 'porto-shortcodes' ) => 'rounded-nav',
                    __( 'Big & Full Width', 'porto-shortcodes' ) => 'big-nav'
                ),
                'dependency' => array('element' => 'nav_pos', 'value' => array('', 'nav-bottom')),
                "group" => __('Slider Options', 'porto-shortcodes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Show Nav on Hover", 'porto-shortcodes'),
                'param_name' => 'show_nav_hover',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                'dependency' => array('element' => 'show_nav', 'not_empty' => true),
                "group" => __('Slider Options', 'porto-shortcodes')
            ),
            array(
                'type' => 'checkbox',
                'heading' => __("Show Dots", 'porto-shortcodes'),
                'param_name' => 'show_dots',
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' ),
                'dependency' => array('element' => 'slider_config', 'not_empty' => true),
                "group" => __('Slider Options', 'porto-shortcodes')
            ),
            $custom_class,
            $animation_type,
            $animation_duration,
            $animation_delay
        )
    ) );

    if (!class_exists('WPBakeryShortCode_Porto_Recent_Posts')) {
        class WPBakeryShortCode_Porto_Recent_Posts extends WPBakeryShortCode {
        }
    }
}