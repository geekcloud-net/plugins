<?php
$output = $container_selector = $min_width = $top = $bottom = $active_class = $animation_type = $animation_duration = $animation_delay = $el_class = '';
extract(shortcode_atts(array(
    'container_selector' => '',
    'min_width' => 767,
    'top' => 110,
    'bottom' => 0,
    'active_class' => 'sticky-active',
    'animation_type' => '',
    'animation_duration' => 1000,
    'animation_delay' => 0,
    'el_class' => ''
), $atts));

$el_class = porto_shortcode_extract_class( $el_class );

$options = array();
$options['containerSelector'] = $container_selector;
$options['minWidth'] = (int)$min_width;
$options['padding']['top'] = (int)$top;
$options['padding']['bottom'] = (int)$bottom;
$options['activeClass'] = $active_class;
$options = json_encode($options);

$output .= '<div class="porto-sticky ' . $el_class . '" data-plugin-options="' . esc_attr($options) . '"';
if ($animation_type) {
    $output .= ' data-appear-animation="'.$animation_type.'"';
    if ($animation_delay)
        $output .= ' data-appear-animation-delay="'.$animation_delay.'"';
    if ($animation_duration && $animation_duration != 1000)
        $output .= ' data-appear-animation-duration="'.$animation_duration.'"';
}
$output .= '>';

$output .= do_shortcode($content);

$output .= '</div>';

echo $output;