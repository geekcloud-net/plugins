<?php
// Porto Schedule Timeline Container
add_shortcode('porto_schedule_timeline_container', 'porto_shortcode_schedule_timeline_container');
add_action('vc_after_init', 'porto_load_schedule_timeline_container_shortcode');
function porto_shortcode_schedule_timeline_container($atts, $content = null) {ob_start();
    if ($template = porto_shortcode_template('porto_schedule_timeline_container'))
        include $template;
    return ob_get_clean();
}
function porto_load_schedule_timeline_container_shortcode() {
    $custom_class = porto_vc_custom_class();
    vc_map( array(
        "name" => "Porto " . __("Schedule Timeline Container", 'porto-shortcodes'),
        "base" => "porto_schedule_timeline_container",
        "category" => __("Porto", 'porto-shortcodes'),
        "icon" => "porto_vc_schedule_timeline",
        "as_parent" => array('only' => 'porto_schedule_timeline_item'),
        "content_element" => true,
        "controls" => "full",
        'js_view' => 'VcColumnView',
        "params" => array(
            array(
                "type" => "textfield",
                "heading" => __("Title", "porto-shortcodes"),
                "param_name" => "title",
                "admin_label" => true,
            ),
            array(
                "type" => "textfield",
                "heading" => __("Subtitle", "porto-shortcodes"),
                "param_name" => "subtitle",
            ),
            array(
                'type' => 'dropdown',
                'heading' => __( 'Circle Type', 'porto-shortcodes' ),
                'param_name' => 'circle_type',
                'value' => array(
                    __( 'Filled', 'porto-shortcodes' ) => 'filled',
                    __( 'Simple', 'porto-shortcodes' ) => 'simple'
                )
            ),
            array(
                'type' => 'label',
                'heading' => __('Title Settings', 'porto-shortcodes'),
                'param_name' => 'label',
                'group' => 'Typography'
            ),
            array(
                'type' => 'colorpicker',
                'heading' => __('Color', 'porto-shortcodes'),
                'param_name' => 'title_color',
                'group' => 'Typography'
            ),
            array(
                'type' => 'label',
                'heading' => __('Subtitle Settings', 'porto-shortcodes'),
                'param_name' => 'label',
                'group' => 'Typography'
            ),
            array(
                'type' => 'colorpicker',
                'heading' => __('Color', 'porto-shortcodes'),
                'param_name' => 'subtitle_color',
                'group' => 'Typography'
            ),
            $custom_class
        )
    ) );
    if (!class_exists('WPBakeryShortCode_Porto_Schedule_Timeline_Container')) {
        class WPBakeryShortCode_Porto_Schedule_Timeline_Container extends WPBakeryShortCodesContainer {
            
        }
    }
}