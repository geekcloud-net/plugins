<?php

$output = $title = $subtitle = $title_color = $subtitle_color = $el_class = '';

extract(shortcode_atts(array(
    'title' => '',
    'subtitle' => '',
    'circle_type' => '',
	'title_color' => '',
	'subtitle_color' => '',
    'el_class' => '',
), $atts));

if( $title || $subtitle ){
	
	$el_class = porto_shortcode_extract_class( $el_class );
	
	if( $circle_type == 'simple' ){
		$circle_type_classes = 'background-color-light circle-light text-color-dark';
	}else{
		$circle_type_classes = 'background-color-primary border-transparent no-box-shadow text-color-light';
	}

	$text_color = $circle_type == 'simple' ? 'dark' : 'light';

	$output .= '<div class="timeline-circle '.$circle_type_classes.' center m-b-lg ' .$el_class. ' ">';
		$output .= '<div class="circle-dotted">';
			$output .= '<div class="circle-center">';
				$output .= '<span'.($title_color?' style="color:'.esc_attr($title_color).' !important"':'').' class="text-color-'.$text_color.' font-weight-bold m-b-none">'.$title.'</span>
							<span'.($subtitle_color?' style="color:'.esc_attr($subtitle_color).' !important"':'').' class="text-color-'.$text_color.'">'.$subtitle.'</span>';
			$output .= '</div>';
		$output .= '</div>';
	$output .= '</div>';
}

$output .= do_shortcode($content);

echo $output;