<?php
$output = $title = $number = $show = $orderby = $order = $hide_free = $show_hidden = $animation_type = $animation_duration = $animation_delay = $el_class = '';
extract( shortcode_atts( array(
    'title' => '',
    'number' => 5,
    'show' => '',
    'orderby' => 'date',
    'order' => 'desc',
    'hide_free' => 0,
    'show_hidden' => 0,
    'animation_type' => '',
    'animation_duration' => 1000,
    'animation_delay' => 0,
    'el_class' => ''
), $atts ) );
$atts['show'] = $show;

$el_class = porto_shortcode_extract_class( $el_class );

$output = '<div class="vc_widget_woo_products wpb_content_element' . $el_class . '"';
if ($animation_type) {
    $output .= ' data-appear-animation="'.$animation_type.'"';
    if ($animation_delay)
        $output .= ' data-appear-animation-delay="'.$animation_delay.'"';
    if ($animation_duration && $animation_duration != 1000)
        $output .= ' data-appear-animation-duration="'.$animation_duration.'"';
}
$output .= '>';

$type = 'WC_Widget_Products';
$args = array('widget_id' => 'woocommerce_products_'.$show.'_'.$number.'_'.$orderby.'_'.$order.'_'.$hide_free.'_'.$show_hidden);

ob_start();
the_widget( $type, $atts, $args );
$output .= ob_get_clean();

$output .= '</div>';

echo $output;