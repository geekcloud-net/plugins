<?php

$output = $subtitle = $image_url = $image_id = $heading = $shadow = $heading_color = $subtitle_color = $animation_type = $animation_duration = $animation_delay = $el_class = '';

extract(shortcode_atts(array(
    'subtitle' => '',
    'image_url' => '',
    'image_id' => '',
    'heading' => '',
	'shadow' => '',
	'heading_color' => '',
	'subtitle_color' => '',
	'animation_type' => '',
    'animation_duration' => 1000,
    'animation_delay' => 0,
    'el_class' => '',
), $atts));

$el_class = porto_shortcode_extract_class( $el_class );

if (!$image_url && $image_id)
    $image_url = wp_get_attachment_url($image_id);

$image_url = str_replace(array('http:', 'https:'), '', $image_url);

$output .= '<div class="timeline-balloon p-b-lg m-b-sm ' . $el_class . '">';
	$output .= '<div class="balloon-cell balloon-time">';
		$output .= '<span'.($subtitle_color?' style="color:'.esc_attr($subtitle_color).' !important"':'').' class="time-text text-color-dark font-weight-bold font-size-sm">'.$subtitle.'</span>';
		$output .= '<div class="time-dot background-color-light"></div>';
	$output .= '</div>';
	$output .= '<div class="balloon-cell"';
		if ($animation_type) {
			$output .= ' data-appear-animation="'.$animation_type.'"';
			if ($animation_delay)
				$output .= ' data-appear-animation-delay="'.$animation_delay.'"';
			if ($animation_duration && $animation_duration != 1000)
				$output .= ' data-appear-animation-duration="'.$animation_duration.'"';
		}
	$output .= '>';
		$output .= '<div class="balloon-content '; 
		if( $shadow ) $output .= ' balloon-shadow '; 
		$output .= 'background-color-light">';
			$output .= '<span class="balloon-arrow background-color-light"></span>';
			if( $image_url ){
				$output .= '<div class="balloon-photo">';
					$output .= '<img src="'.$image_url.'" class="img-responsive img-circle" alt="'.$heading.'">';
				$output .= '</div>';
			}
			$output .= '<div class="balloon-description">';
				if( $heading )
				$output .= '<h5'.($heading_color?' style="color:'.esc_attr($heading_color).' !important"':'').' class="text-color-dark font-weight-bold p-t-xs m-none">'.$heading.'</h5>';
			
				if( $content )
				$output .= '<p class="font-weight-normal m-t-sm m-b-xs">'.$content.'</p>';
			$output .= '</div>';
		$output .= '</div>';
	$output .= '</div>';
$output .= '</div>';

echo $output;