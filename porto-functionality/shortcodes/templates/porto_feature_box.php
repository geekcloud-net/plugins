<?php
$output = $skin = $show_icon = $icon_type = $icon_porto = $icon_image = $icon = $icon_simpleline = $box_style = $box_dir = $animation_type = $animation_duration = $animation_delay = $el_class = $icon_circle_style = '';
extract(shortcode_atts(array(
    'skin' => 'custom',
    'show_icon' => false,
    'icon_type' => 'fontawesome',
    'icon' => '',
    'icon_simpleline' => '',
    'icon_porto' => '',
    'icon_image' => '',

    'icon_size' => '14',
    'box_style' => '',
    'box_dir' => '',
    'animation_type' => '',
    'animation_duration' => 1000,
    'animation_delay' => 0,
    'el_class' => ''
), $atts));

$el_class = porto_shortcode_extract_class( $el_class );

switch ($icon_type) {
    case 'simpleline': $icon_class = $icon_simpleline; break;
    case 'porto': $icon_class = $icon_porto; break;
    case 'image': $icon_class = 'icon-image'; break;
    default: $icon_class = $icon;
}
if (!$show_icon)
    $icon_class = '';
if( $box_style == 'feature-box-style-1' && $box_style == 'feature-box-style-3' && $box_style == 'feature-box-style-6' ) {
    if( $icon_class != 'icon-image' && $icon_size > 20 ){
       $num = $icon_size + 15;
       $icon_circle_style = ' style="width:'.$num.'px;height:'.$num.'px;line-height:'.($num-2).'px;"';
    }
}
$output = '<div class="porto-feature-box wpb_content_element ' . $el_class .'"';
if ($animation_type) {
    $output .= ' data-appear-animation="'.$animation_type.'"';
    if ($animation_delay)
        $output .= ' data-appear-animation-delay="'.$animation_delay.'"';
    if ($animation_duration && $animation_duration != 1000)
        $output .= ' data-appear-animation-duration="'.$animation_duration.'"';
}
$output .= '>';
$output .= '<div class="feature-box' . ($skin != 'custom' ? ' feature-box-' . $skin : '') . ($box_style ? ' ' . $box_style : '') . ($box_dir ? ' ' . $box_dir : '') . '">';

if ($icon_class) {
    $output .= '<div class="feature-box-icon"'.$icon_circle_style.'><i class="' . $icon_class . '" style="font-size:'.$icon_size.'px">';
    if ($icon_class == 'icon-image' && $icon_image) {
        $icon_image = preg_replace('/[^\d]/', '', $icon_image);
        $image_url = wp_get_attachment_url($icon_image);
        $image_url = str_replace(array('http:', 'https:'), '', $image_url);
        if ($image_url)
            $output .= '<img alt="" src="' . esc_url($image_url) . '">';
    }
    $output .= '</i></div>';
}
$output .= '<div class="feature-box-info' . ($icon_class ? '' : ' p-none') . '">' . do_shortcode($content) . '</div>';
$output .= '</div>';

$output .= '</div>';

echo $output;