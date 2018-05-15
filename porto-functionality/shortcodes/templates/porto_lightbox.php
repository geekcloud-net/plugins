<?php
$output = $prefix = $text = $suffix = $display = $type = $btn_size = $btn_skin = $btn_context = $lightbox_type = $iframe_url = $ajax_url = $lightbox_animation = $lightbox_size = $animation_type = $animation_duration = $animation_delay = $el_class = '';
extract(shortcode_atts(array(
    'prefix' => '',
    'text' => '',
    'suffix' => '',
    'display' => '',
    'type' => '',
    'btn_size' => '',
    'btn_skin' => '',
    'btn_context' => '',
    'lightbox_type' => '',
    'iframe_url' => '',
    'ajax_url' => '',
    'lightbox_animation' => '',
    'lightbox_size' => '',
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

$output = '<div class="porto-lightbox ' . $el_class . '"';
if ($animation_type) {
    $output .= ' data-appear-animation="'.$animation_type.'"';
    if ($animation_delay)
        $output .= ' data-appear-animation-delay="'.$animation_delay.'"';
    if ($animation_duration && $animation_duration != 1000)
        $output .= ' data-appear-animation-duration="'.$animation_duration.'"';
}
$output .= '>';

$output .= $prefix;

$link = '';
$class = '';
$validCharacters = 'abcdefghijklmnopqrstuvwxyz0123456789';
$rand = '';
$length = 32;
for ($n = 1; $n < $length; $n++) {
    $whichCharacter = rand(0, strlen($validCharacters)-1);
    $rand .= $validCharacters{$whichCharacter};
}

switch ($lightbox_type) {
    case 'iframe':
        $class .= 'porto-popup-iframe';
        $link .= $iframe_url;
        break;
    case 'ajax':
        $class .= 'porto-popup-ajax';
        $link .= $ajax_url;
        break;
    default:
        $class .= 'porto-popup-content';
        $link .= '#' . $rand;
        break;
}

if ($type == 'btn') {
    $class .= ' btn';
    if ($btn_size)
        $class .= ' btn-'.$btn_size;
    if ($btn_skin != 'custom')
        $class .= ' btn-'.$btn_skin;
    if ($btn_context)
        $class .= ' btn-'.$btn_context;
    if ($btn_skin == 'custom' && !$btn_context)
        $class .= ' btn-default';
}

$output .= ' <a href="' . esc_url($link ? $link : 'javascript:;') . '" title="' . esc_attr($text) . '" class="' . $class . '"';
if ($lightbox_type == '' && $lightbox_animation) {
    if ($lightbox_animation == 'zoom-anim')
        $output .= ' data-animation="my-mfp-zoom-in"';
    if ($lightbox_animation == 'move-anim')
        $output .= ' data-animation="my-mfp-slide-bottom"';
}
$output .= '>';
$output .= $text;
$output .= '</a> ';

$output .= $suffix;

if ($lightbox_type == '') {
    $output .= '<div id="' . $rand . '" class="dialog' . ($lightbox_size ? ' dialog-' . $lightbox_size : '') . ($lightbox_animation ? ' ' . $lightbox_animation . '-dialog' : '') . ' mfp-hide">';
    $output .= do_shortcode($content);
    $output .= '</div>';
}

$output .= '</div>';

echo $output;