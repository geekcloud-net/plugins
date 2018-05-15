<?php
$output = $stage_padding = $margin = $autoplay = $autoplay_timeout = $autoplay_hover_pause = $items = $items_lg = $items_md = $items_sm = $items_xs = $show_nav = $show_nav_hover = $nav_pos = $nav_type = $show_dots = $dots_pos = $dots_align = $animate_in = $animate_out = $loop = $center = $video = $lazyload = $merge = $mergeFit = $mergeFit_lg = $mergeFit_md = $mergeFit_sm = $animation_type = $animation_duration = $animation_delay = $el_class = '';
extract(shortcode_atts(array(
    'stage_padding' => 40,
    'margin' => 10,
    'autoplay' => false,
    'autoplay_timeout' => 5000,
    'autoplay_hover_pause' => false,
    'items' => 6,
    'items_lg' => 4,
    'items_md' => 3,
    'items_sm' => 2,
    'items_xs' => 1,
    'show_nav' => false,
    'show_nav_hover' => false,
    'nav_pos' => '',
    'nav_type' => '',
    'show_dots' => false,
    'dots_pos' => '',
    'dots_align' => '',
    'animate_in' => '',
    'animate_out' => '',
    'loop' => false,
    'center' => false,
    'video' => false,
    'lazyload' => false,
    'fullscreen' => false,
    'merge' => false,
    'mergeFit' => true,
    'mergeFit_lg' => true,
    'mergeFit_md' => true,
    'mergeFit_sm' => true,
    'mergeFit_xs' => true,
    'animation_type' => '',
    'animation_duration' => 1000,
    'animation_delay' => 0,
    'el_class' => ''
), $atts));

$el_class = porto_shortcode_extract_class( $el_class );

if ($stage_padding) $el_class .= ' stage-margin';

if ($show_nav) {
    if ($nav_pos) $el_class .= ' ' . $nav_pos;
    if ($nav_type) $el_class .= ' ' . $nav_type;
    if ($show_nav_hover) $el_class .= ' show-nav-hover';
}

if ($show_dots && $dots_pos) $el_class .= ' ' . $dots_pos . ' ' . $dots_align;

$options = array();
$options['stagePadding'] = (int)$stage_padding;
$options['margin'] = (int)$margin;
$options['autoplay'] = $autoplay;
$options['autoplayTimeout'] = (int)$autoplay_timeout;
$options['autoplayHoverPause'] = $autoplay_hover_pause;
$options['items'] = (int)$items;
$options['lg'] = (int)$items_lg;
$options['md'] = (int)$items_md;
$options['sm'] = (int)$items_sm;
$options['xs'] = (int)$items_xs;
$options['nav'] = $show_nav;
$options['dots'] = $show_dots;
$options['animateIn'] = $animate_in;
$options['animateOut'] = $animate_out;
$options['loop'] = $loop;
$options['center'] = $center;
$options['video'] = $video;
$options['lazyLoad'] = $lazyload;
$options['fullscreen'] = $fullscreen;

$GLOBALS['porto_carousel_lazyload'] = true;

if ($merge) {
    $options['merge'] = true;

    if ($mergeFit)
        $options['mergeFit'] = true;

    if ($mergeFit_lg)
        $options['mergeFit_lg'] = true;

    if ($mergeFit_md)
        $options['mergeFit_md'] = true;

    if ($mergeFit_sm)
        $options['mergeFit_sm'] = true;

    if ($mergeFit_xs)
        $options['mergeFit_xs'] = true;
}
$options = json_encode($options);

$output = '';
if ( $fullscreen ) {
    $output .= '<div class="fullscreen-carousel">';
}
$output .= '<div class="porto-carousel owl-carousel ' . $el_class . '"';
if ($animation_type) {
    $output .= ' data-appear-animation="'.$animation_type.'"';
    if ($animation_delay)
        $output .= ' data-appear-animation-delay="'.$animation_delay.'"';
    if ($animation_duration && $animation_duration != 1000)
        $output .= ' data-appear-animation-duration="'.$animation_duration.'"';
}
$output .= ' data-plugin-options="' . esc_attr($options) . '"';
$output .= '>';

$output .= do_shortcode($content);

$output .= '</div>';
if ( $fullscreen ) {
    $output .= '</div>';
}

$GLOBALS['porto_carousel_lazyload'] = false;
unset( $GLOBALS['porto_carousel_lazyload'] );

echo $output;