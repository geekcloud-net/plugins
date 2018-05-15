<?php

$output = $subtitle = $image_url = $image_id = $heading = $color = $heading_color = $company_color = $animation_type = $animation_duration = $animation_delay = $el_class = '';

extract(shortcode_atts(array(
    'from' => '',
    'to' => '',
    'duration' => '',
    'company' => '',
    'location' => '',
    'heading' => '',
    'color' => '',
    'heading_color' => '',
    'company_color' => '',
    'el_class' => '',
), $atts));

$el_class = porto_shortcode_extract_class( $el_class );

$output .= '<article class="timeline-box right ' .$el_class.'">';
	$output .= '<div class="experience-info col-lg-3 col-md-5 match-height background-color-primary">';
		$output .= '<span class="from text-color-dark text-uppercase">';
			$output .= __( 'From', 'porto') . '<span'.($color?' style="color:'.esc_attr($color).' !important"':'').' class="font-weight-semibold">'.$from.'</span>';
		$output .= '</span>';
		$output .= '<span class="to text-color-dark text-uppercase">';
			$output .= __( 'To', 'porto') . '<span'.($color?' style="color:'.esc_attr($color).' !important"':'').' class="font-weight-semibold">'.$to.'</span>';
		$output .= '</span>';
		$output .= '<p'.($color?' style="color:'.esc_attr($color).' !important"':'').' class="text-color-dark">'.$duration.'</p>';
		$output .= '<span'.($company_color?' style="color:'.esc_attr($company_color).' !important"':'').' class="company text-color-dark font-weight-semibold">';
			$output .= $company;
			$output .= '<span'.($color?' style="color:'.esc_attr($color).' !important"':'').' class="company-location text-color-dark font-weight-normal text-uppercase">'.$location.'</span>';
		$output .= '</span>';
	$output .= '</div>';
	$output .= '<div class="experience-description col-lg-9 col-md-7 match-height background-color-light">';
		$output .= '<h4'.($heading_color?' style="color:'.esc_attr($heading_color).' !important"':'').' class="text-color-dark font-weight-semibold">'.$heading.'</h4>';
		$output .= '<p class="custom-text-color-2">'.$content.'</p>';
	$output .= '</div>';
$output .= '</article>';

echo $output;