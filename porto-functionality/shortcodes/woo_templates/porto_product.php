<?php

$output = $title = $view = $column_width = $addlinks_pos = $id = $animation_type = $animation_duration = $animation_delay = $el_class = '';
extract( shortcode_atts( array(
    'title' => '',
    'view' => 'grid',
    'column_width' => '',
    'id' => '',
    'addlinks_pos' => '',
    'animation_type' => '',
    'animation_duration' => 1000,
    'animation_delay' => 0,
    'el_class' => ''
), $atts ) );

$el_class = porto_shortcode_extract_class( $el_class );

$output = '<div class="porto-products wpb_content_element' . $el_class . '"';
if ($animation_type) {
    $output .= ' data-appear-animation="'.$animation_type.'"';
    if ($animation_delay)
        $output .= ' data-appear-animation-delay="'.$animation_delay.'"';
    if ($animation_duration && $animation_duration != 1000)
        $output .= ' data-appear-animation-duration="'.$animation_duration.'"';
}
$output .= '>';

if ( $title ) {
    $output .= '<h2 class="section-title">'.$title.'</h2>';
}

global $porto_woocommerce_loop;

$porto_woocommerce_loop['view'] = $view;
$porto_woocommerce_loop['columns'] = 1;
$porto_woocommerce_loop['column_width'] = $column_width;
$porto_woocommerce_loop['addlinks_pos'] = $addlinks_pos;
$output .= do_shortcode('[product id="'.$id.'" columns="1"]');

$output .= '</div>';

echo $output;