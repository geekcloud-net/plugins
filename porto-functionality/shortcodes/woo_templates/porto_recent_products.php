<?php

$output = $title = $view = $per_page = $columns = $column_width = $addlinks_pos = $orderby = $order = $pagination = $navigation = $animation_type = $animation_duration = $animation_delay = $el_class = '';
extract( shortcode_atts( array(
    'title' => '',
    'view' => 'grid',
    'per_page' => 12,
    'columns' => 4,
    'columns_mobile' => '',
    'column_width' => '',
    'orderby' => 'date',
    'order' => 'desc',
    'addlinks_pos' => '',
    'navigation' => 1,
    'pagination' => 0,
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
    if ($view == 'products-slider')
        $output .= '<h2 class="slider-title"><span class="inline-title">'.$title.'</span><span class="line"></span></h2>';
    else
        $output .= '<h2 class="section-title">'.$title.'</h2>';
}

if ($view == 'products-slider')
    $output .= '<div class="slider-wrapper">';

global $porto_woocommerce_loop;

$porto_woocommerce_loop['view'] = $view;
$porto_woocommerce_loop['columns'] = $columns;
if ( $columns_mobile ) {
    $porto_woocommerce_loop['columns_mobile'] = $columns_mobile;
}
$porto_woocommerce_loop['column_width'] = $column_width;
$porto_woocommerce_loop['pagination'] = $pagination;
$porto_woocommerce_loop['navigation'] = $navigation;
$porto_woocommerce_loop['addlinks_pos'] = $addlinks_pos;

$output .= do_shortcode('[recent_products per_page="'.$per_page.'" columns="'.$columns.'" orderby="'.$orderby.'" order="'.$order.'"]');

if ($view == 'products-slider')
    $output .= '</div>';

$output .= '</div>';

echo $output;