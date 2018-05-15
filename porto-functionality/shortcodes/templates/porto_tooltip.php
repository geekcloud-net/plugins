<?php
$output = $prefix = $text = $suffix = $display = $type = $link = $btn_size = $btn_skin = $btn_context = $tooltip_text = $tooltip_position = $animation_type = $animation_duration = $animation_delay = $el_class = '';
extract(shortcode_atts(array(
    'prefix' => '',
    'text' => '',
    'suffix' => '',
    'display' => '',
    'type' => '',
    'link' => '',
    'btn_size' => '',
    'btn_skin' => 'custom',
    'btn_context' => '',
    'tooltip_text' => '',
    'tooltip_position' => 'top',
    'animation_type' => '',
    'animation_duration' => 1000,
    'animation_delay' => 0,
    'el_class' => '',
), $atts));

$el_class = porto_shortcode_extract_class( $el_class );

if ($display == 'block')
    $el_class .= ' wpb_content_element';
else
    $el_class .= ' inline';

$output = '<div class="porto-tooltip ' . $el_class . '"';
if ($animation_type) {
    $output .= ' data-appear-animation="'.$animation_type.'"';
    if ($animation_delay)
        $output .= ' data-appear-animation-delay="'.$animation_delay.'"';
    if ($animation_duration && $animation_duration != 1000)
        $output .= ' data-appear-animation-duration="'.$animation_duration.'"';
}
$output .= '>';

$output .= $prefix;

if ($type == 'btn' || $type == 'btn-link') {
    $btn_class = 'btn';
    if ($btn_size)
        $btn_class .= ' btn-'.$btn_size;
    if ($btn_skin != 'custom')
        $btn_class .= ' btn-'.$btn_skin;
    if ($btn_context)
        $btn_class .= ' btn-'.$btn_context;
    if ($btn_skin == 'custom' && !$btn_context)
        $btn_class .= ' btn-default';
    if ($type == 'btn') {
        $output .= ' <button type="button" data-toggle="tooltip" title="' . esc_attr($tooltip_text) . '" data-placement="' . esc_attr($tooltip_position) . '" class="' . esc_attr($btn_class) . '">';
        $output .= $text;
        $output .= '</button> ';
    } else {
        $output .= ' <a href="' . esc_url($link ? $link : 'javascript:;') . '" data-toggle="tooltip" title="' . esc_attr($tooltip_text) . '" data-placement="' . esc_attr($tooltip_position) . '" class="' . esc_attr($btn_class) . '">';
        $output .= $text;
        $output .= '</a> ';
    }
} else {
    $output .= ' <a href="' . esc_url($link ? $link : 'javascript:;') . '" data-toggle="tooltip" title="' . esc_attr($tooltip_text) . '" data-placement="' . esc_attr($tooltip_position) . '">';
    $output .= $text;
    $output .= '</a> ';
}

$output .= $suffix;

$output .= '</div>';

echo $output;