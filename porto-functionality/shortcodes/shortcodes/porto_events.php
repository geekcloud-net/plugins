<?php
// Porto Events
add_shortcode('porto_events', 'porto_shortcode_events');
add_action('vc_after_init', 'porto_load_events_shortcode');
function porto_shortcode_events($atts, $content = null) {
    ob_start();
    if ($template = porto_shortcode_template('porto_events'))
        include $template;
    return ob_get_clean();
}

function porto_load_events_shortcode() {
    vc_map( array(
        'name' 		=> "Porto " . __('Events', 'porto-shortcodes'),
        'base' 		=> 'porto_events',
        'category' 	=> __('Porto', 'porto-shortcodes'),
        'icon' 		=> 'porto_vc_blockquote',
        'params' 	=> array(
            array(
                'type' => 'dropdown',
                'heading' => __( 'Event Type', 'porto-shortcodes' ),
                'param_name' => 'event_type',
                'value' => array(
                    __( 'Default', 'porto-shortcodes' )=> '',
                    __( 'Next', 'porto-shortcodes' )=> 'next',										
					__( 'Upcoming', 'porto-shortcodes' )=> 'upcoming',									
					__( 'Past', 'porto-shortcodes' )=> 'past'
                ),
            ),						
			array(                
				'type' => 'textfield',                
				'heading' => __('Number of Events', 'porto-shortcodes'),               
				'param_name' => 'event_numbers',            
				'dependency' => array('element' => 'event_type', 'value' => array('upcoming', 'past', 'next')),
			),
			array(                
				'type' => 'textfield',                
				'heading' => __('Skip Number of Events', 'porto-shortcodes'),               
				'param_name' => 'event_skip',            
				'dependency' => array('element' => 'event_type', 'value' => array('upcoming')),
			),
			array(
                'type' => 'dropdown',
                'heading' => __( 'Numbers of Columns', 'porto-shortcodes' ),
                'param_name' => 'event_column',
                'value' => array(
                    __( '1', 'porto-shortcodes' )=> '1',										
					__( '2', 'porto-shortcodes' )=> '2',									
                ),
				'dependency' => array('element' => 'event_type', 'value' => array('upcoming', 'past', 'next')),
            ),	
			array(
                'type' => 'dropdown',
                'heading' => __( 'Display Countdown', 'porto-shortcodes' ),
                'param_name' => 'event_countdown',
                'value' => array(
                    __( 'YES', 'porto-shortcodes' )=> 'show',										
					__( 'NO', 'porto-shortcodes' )=> 'hide',									
                ),
				'dependency' => array('element' => 'event_type', 'value' => array('next')),
            ),
			 array(
                'type' => 'textfield',
                'heading' => __("Extra class name", 'porto-shortcodes'),
                'param_name' => 'el_class',
				'description' => 'Style particular content element differently - add a class name and refer to it in custom CSS.',
            ),	
        ),
		
    ) );

    if (!class_exists('WPBakeryShortCode_Porto_Events')) {
        class WPBakeryShortCode_Porto_Events extends WPBakeryShortCode {
        }
    }
}