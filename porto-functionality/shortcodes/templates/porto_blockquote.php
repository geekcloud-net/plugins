<?php
$output = $footer_before = $footer_after = $view = $dir = $skin = $color = $animation_duration = $animation_delay = $el_class = '';
extract(shortcode_atts(array(
    'footer_before' => '',
    'footer_after' => '',
    'view' => '',
    'dir' => '',
    'skin' => 'custom',
    'color' => '',
    'animation_type' => '',
    'animation_duration' => 1000,
    'animation_delay' => 0,
    'el_class' => '',
), $atts));

$el_class = porto_shortcode_extract_class( $el_class );

if (!$view && $skin == 'custom' && $color) {
    $sc_class = 'porto-blockquote'.rand();
    $el_class .= ' '.$sc_class;
    ?>
    <style type="text/css">
        .<?php echo $sc_class ?> blockquote { border-color: <?php echo $color ?>; }
    </style><?php
}

$output = '<div class="porto-blockquote wpb_content_element ' . $el_class . '"';
if ($animation_type) {
    $output .= ' data-appear-animation="'.$animation_type.'"';
    if ($animation_delay)
        $output .= ' data-appear-animation-delay="'.$animation_delay.'"';
    if ($animation_duration && $animation_duration != 1000)
        $output .= ' data-appear-animation-duration="'.$animation_duration.'"';
}
$output .= '>';

$output .= '<blockquote class="'. $view . ' ' . $dir . ' ' . (!$view && $skin != 'custom' ? 'blockquote-'.$skin : '') .'">';
$output .= '<p>'.do_shortcode($content).'</p>';
if ($footer_before || $footer_after) {
    $output .= '<footer>'. $footer_before .' <cite title="'. $footer_after .'">'. $footer_after .'</cite></footer>';
}
$output .= '</blockquote>';

$output .= '</div>';

echo $output;