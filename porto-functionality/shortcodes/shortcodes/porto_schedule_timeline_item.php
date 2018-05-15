<?php

// Porto Schedule Timeline Item
add_shortcode('porto_schedule_timeline_item', 'porto_shortcode_schedule_timeline_item');
add_action('vc_after_init', 'porto_load_schedule_timeline_item_shortcode');

function porto_shortcode_schedule_timeline_item($atts, $content = null) {
    ob_start();
    if ($template = porto_shortcode_template('porto_schedule_timeline_item'))
        include $template;
    return ob_get_clean();
}

function porto_load_schedule_timeline_item_shortcode() {
	$animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();

    vc_map( array(
        "name" => __("Schedule Timeline Item", 'porto-shortcodes'),
        "base" => "porto_schedule_timeline_item",
        "category" => __("Porto", 'porto-shortcodes'),
        "icon" => "porto_vc_schedule_timeline",
        "as_child" => array('only' => 'porto_schedule_timeline_container'),
        "params" => array(
            array(
                "type" => "textfield",
                "heading" => __("Subtitle/time", "porto-shortcodes"),
                "param_name" => "subtitle"
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
                "type" => "textfield",
                "heading" => __("Heading", "porto-shortcodes"),
                "param_name" => "heading",
				"admin_label" => true,
            ),
			 array(
                'type' => 'textarea_html',
                'heading' => __('Details', 'porto-shortcodes'),
                'param_name' => 'content',
            ),
			array(
                'type' => 'checkbox',
                'heading' => __("Shadow", 'porto-shortcodes'),
                'param_name' => 'shadow',
            ),
			array(
                'type' => 'label',
                'heading' => __('Heading Settings', 'porto-shortcodes'),
                'param_name' => 'label',
				'group' => 'Typography'
            ),
			array(
                'type' => 'colorpicker',
                'heading' => __('Color', 'porto-shortcodes'),
                'param_name' => 'heading_color',
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
			$custom_class,
			$animation_type,
            $animation_duration,
            $animation_delay
			
        )
    ) );

    if (!class_exists('WPBakeryShortCode_Porto_Schedule_Timeline_Item')) {
        class WPBakeryShortCode_Porto_Schedule_Timeline_Item extends WPBakeryShortCode {
        }
    }
}