<?php

$output = $style = $size = $count_md = $count_sm = $border = $space = $animation_type = $animation_duration = $animation_delay = $el_class = '';
extract(shortcode_atts(array(
    'style' => '',
    'size' => false,
    'count_md' => 4,
    'count_sm' => 2,
    'border' => true,
    'space' => false,
    'animation_type' => '',
    'animation_duration' => 1000,
    'animation_delay' => 0,
    'el_class' => ''
), $atts));

$el_class = porto_shortcode_extract_class( $el_class );

if (!$border)
    $el_class .= ' no-borders';

if ($space)
    $el_class .= ' spaced';

if ('sm' === $size)
    $el_class .= ' pricing-table-sm';

if ('flat' === $style)
    $el_class .= ' pricing-table-flat';

$output = '<div class="porto-price-boxes pricing-table ' . $el_class . '"';
if ($animation_type) {
    $output .= ' data-appear-animation="'.$animation_type.'"';
    if ($animation_delay)
        $output .= ' data-appear-animation-delay="'.$animation_delay.'"';
    if ($animation_duration && $animation_duration != 1000)
        $output .= ' data-appear-animation-duration="'.$animation_duration.'"';
}
$output .= '>';

$output .= '<div class="row">';

global $porto_price_boxes_count_md, $porto_price_boxes_count_sm;

$porto_price_boxes_count_md = $count_md;
$porto_price_boxes_count_sm = $count_sm;

$output .= do_shortcode($content);

$porto_price_boxes_count_md = $porto_price_boxes_count_sm = 0;

$output .= '</div>';

$output .= '</div>';

echo $output;