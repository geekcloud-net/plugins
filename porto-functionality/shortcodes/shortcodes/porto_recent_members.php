<?php





// Porto Recent Members


add_shortcode('porto_recent_members', 'porto_shortcode_recent_members');


add_action('vc_after_init', 'porto_load_recent_members_shortcode');





function porto_shortcode_recent_members($atts, $content = null) {


    ob_start();


    if ($template = porto_shortcode_template('porto_recent_members'))


        include $template;


    return ob_get_clean();


}





function porto_load_recent_members_shortcode() {


    $animation_type = porto_vc_animation_type();


    $animation_duration = porto_vc_animation_duration();


    $animation_delay = porto_vc_animation_delay();


    $custom_class = porto_vc_custom_class();





    vc_map( array(


        'name' => "Porto " . __('Recent Members', 'porto-shortcodes'),


        'base' => 'porto_recent_members',


        'category' => __('Porto', 'porto-shortcodes'),


        'icon' => 'porto_vc_recent_members',


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


                'std' => 'classic',


                "value" => porto_sh_commons('member_view'),


                "admin_label" => true


            ),
			
			array(


                "type" => "dropdown",


                "heading" => __("Hover Image Effect", 'porto-shortcodes'),


                "param_name" => "hover_image_effect",


                'std' => 'zoom',


                "value" => porto_sh_commons('custom_zoom'),


                "admin_label" => true


            ),


            array(


                'type' => 'checkbox',


                'heading' => __("Show Overview", 'porto-shortcodes'),


                'param_name' => 'overview',


                'std' => 'yes',


                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' )


            ),


            array(


                'type' => 'checkbox',


                'heading' => __("Show Social Links", 'porto-shortcodes'),


                'param_name' => 'socials',


                'std' => 'yes',


                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' )


            ),
			
			 array(


                'type' => 'checkbox',


                'heading' => __("Social Links Advance Style", 'porto-shortcodes'),


                'param_name' => 'socials_style',


                'std' => 'yes',
				
				'dependency' => array('element' => 'socials', 'not_empty' => true),

                //'value' => array( __( 'Yes', 'js_composer' ) => 'yes' )


            ),
			
			


            array(


                'type' => 'checkbox',


                'heading' => __("Enable Ajax Load", 'porto-shortcodes'),


                'param_name' => 'ajax_load',


                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' )


            ),
			
			array(
                'type' => 'checkbox',
                'heading' => __("Ajax Load on Modal", 'porto-shortcodes'),
                'param_name' => 'ajax_modal',
                'dependency' => array('element' => 'ajax_load', 'not_empty' => true),
                'value' => array( __( 'Yes', 'js_composer' ) => 'yes' )
            ),


            array(


                "type" => "textfield",


                "heading" => __("Members Count", 'porto-shortcodes'),


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





    if (!class_exists('WPBakeryShortCode_Porto_Recent_Members')) {


        class WPBakeryShortCode_Porto_Recent_Members extends WPBakeryShortCode {


        }


    }


}