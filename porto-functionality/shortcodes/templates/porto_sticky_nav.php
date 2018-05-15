<?php
$output = $container = $min_width = $bg_color = $skin = $link_color = $link_bg_color = $link_acolor = $link_abg_color = $animation_type = $animation_duration = $animation_delay = $el_class = '';
extract(shortcode_atts(array(
    'container' => false,
    'min_width' => 991,
    'bg_color' => '',
    'skin' => 'custom',
    'link_color' => '',
    'link_bg_color' => '',
    'link_acolor' => '',
    'link_abg_color' => '',
    'animation_type' => '',
    'animation_duration' => 1000,
    'animation_delay' => 0,
    'el_class' => ''
), $atts));

$el_class = porto_shortcode_extract_class( $el_class );

$style = '';
if ($bg_color) {
    $style = 'background-color:'.$bg_color.';';
}

if ($skin == 'custom' && ($link_color || $link_bg_color || $link_acolor || $link_abg_color)) {
    $sc_class = 'porto-sticky-nav'.rand();
    $el_class .= ' '.$sc_class;
    ?>
    <style type="text/css">
    <?php if ($link_color) : ?>.<?php echo $sc_class ?> .nav-pills > li > a { color: <?php echo $link_color ?> !important; }<?php endif; ?>
    <?php if ($link_bg_color) : ?>.<?php echo $sc_class ?> .nav-pills > li > a { background-color: <?php echo $link_bg_color ?> !important; }<?php endif; ?>
    <?php if ($link_acolor) : ?>.<?php echo $sc_class ?> .nav-pills > li.active > a { color: <?php echo $link_acolor ?> !important; }<?php endif; ?>
    <?php if ($link_abg_color) : ?>.<?php echo $sc_class ?> .nav-pills > li.active > a { background-color: <?php echo $link_abg_color ?> !important; }<?php endif; ?>
    </style><?php
}

$options = array();
$options['minWidth'] = (int)$min_width;
$options = json_encode($options);

$output .= '<div class="sticky-nav-wrapper"><div class="porto-sticky-nav nav-secondary ' . $el_class . '" data-plugin-options="' . esc_attr($options) . '"';
if ($style)
    $output .= ' style="'.$style.'"';
if ($animation_type) {
    $output .= ' data-appear-animation="'.$animation_type.'"';
    if ($animation_delay)
        $output .= ' data-appear-animation-delay="'.$animation_delay.'"';
    if ($animation_duration && $animation_duration != 1000)
        $output .= ' data-appear-animation-duration="'.$animation_duration.'"';
}
$output .= '>';

if ($container)
    $output .= '<div class="container">';

$output .= '<ul class="nav nav-pills' . ($skin == 'custom' ? '' : ' nav-pills-'. $skin) . '">';

$output .= do_shortcode($content);

$output .= '</ul>';

if ($container)
    $output .= '</div>';

$output .= '</div></div>';

echo $output;