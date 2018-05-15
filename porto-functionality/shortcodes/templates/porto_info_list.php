<?php

$icon_color = $font_size_icon = $el_class = '';
extract( shortcode_atts(array(
    'icon_color' => '',
    'font_size_icon' => '',
    'el_class' => '',
), $atts) );
$style = '';
if ( $icon_color ) {
    $style .= 'color: '. esc_attr( $icon_color ) . ';';
}
if ( $font_size_icon ) {
    $style .= 'font-size: '. esc_attr( $font_size_icon ) . 'px;';
}
$uid = 'porto-info-list'. uniqid( rand() );

$html = '';
if ( $style ) {
    $html .= '<style>#'. esc_attr( $uid ) .' i { '. $style .' }</style>';
}
$html .= '<ul id="'. esc_attr( $uid ) .'" class="porto-info-list '. esc_attr( $el_class ) .'">';
$html .= do_shortcode( $content );
$html .= '</ul>';

echo $html;