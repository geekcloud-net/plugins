<?php

// Porto Experience Timeline Item
add_shortcode('porto_experience_timeline_item', 'porto_shortcode_experience_timeline_item');
add_action('vc_after_init', 'porto_load_experience_timeline_item_shortcode');

function porto_shortcode_experience_timeline_item($atts, $content = null) {
    ob_start();
    if ($template = porto_shortcode_template('porto_experience_timeline_item'))
        include $template;
    return ob_get_clean();
}

function porto_load_experience_timeline_item_shortcode() {
	$animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();

    vc_map( array(
        "name" => __("Experience Timeline Item", 'porto-shortcodes'),
        "base" => "porto_experience_timeline_item",
        "category" => __("Porto", 'porto-shortcodes'),
        "icon" => "porto_vc_experience_timeline",
        "as_child" => array('only' => 'porto_experience_timeline_container'),
        "params" => array(
            array(
                "type" => "textfield",
                "heading" => __("From", "porto-shortcodes"),
                "param_name" => "from"
            ),
            array(
                "type" => "textfield",
                "heading" => __("To", "porto-shortcodes"),
                "param_name" => "to"
            ),
			array(
                "type" => "textfield",
                "heading" => __("Duration", "porto-shortcodes"),
                "param_name" => "duration"
            ),
			array(
                "type" => "textfield",
                "heading" => __("Company", "porto-shortcodes"),
                "param_name" => "company"
            ),
			array(
                "type" => "textfield",
                "heading" => __("Location", "porto-shortcodes"),
                "param_name" => "location"
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
			$custom_class,
			array(
                'type' => 'label',
                'heading' => __('From, To, Duration & Location Settings', 'porto-shortcodes'),
                'param_name' => 'label',
				'group' => 'Typography'
            ),
			array(
                'type' => 'colorpicker',
                'heading' => __('Color', 'porto-shortcodes'),
                'param_name' => 'color',
				'group' => 'Typography'
            ),
			array(
                'type' => 'label',
                'heading' => __('Company Settings', 'porto-shortcodes'),
                'param_name' => 'label',
				'group' => 'Typography'
            ),
			array(
                'type' => 'colorpicker',
                'heading' => __('Color', 'porto-shortcodes'),
                'param_name' => 'company_color',
				'group' => 'Typography'
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
			
			
        )
    ) );

    if (!class_exists('WPBakeryShortCode_Porto_Experience_Timeline_Item')) {
        class WPBakeryShortCode_Porto_Experience_Timeline_Item extends WPBakeryShortCode {
			
        }
    }
}