<?php

$icon_type = $icon_img = $img_width = $icon = $icon_color = $icon_color_bg = $icon_size = $icon_style = $icon_border_style = $icon_border_radius = $icon_color_border = $icon_border_size = $icon_border_spacing = $icon_link = $el_class = $animation_type = $icon_margin = $target = $link_title  = $rel = $css_trans = '';
extract(shortcode_atts( array(
    'icon_type' => 'fontawesome',
    'icon' => '',
    'icon_simpleline' => '',
    'icon_porto' => '',
    'icon_size' => '',
    'icon_color' => '',
    'icon_style' => '',
    'icon_color_bg' => '',
    'icon_color_border' => '',
    'icon_border_style' => '',
    'icon_border_size' => '',
    'icon_border_radius' => '',
    'icon_border_spacing' => '',
    'icon_link' => '',
    'icon_margin' => '',
    'animation_type' => '',
    'el_class'=>'',
),$atts));

switch ( $icon_type ) {
    case 'simpleline':
        $icon = $icon_simpleline;
        break;
    case 'porto':
        $icon = $icon_porto;
        break;
}

if ( $animation_type !== 'none' &&  $animation_type ) {
    $css_trans = 'data-appear-animation="'. esc_attr( $animation_type ) .'"';
}
$output = $style = $link_sufix = $link_prefix = $target = $href = $icon_align_style = '';
$uniqid = uniqid();
if($icon_link !== ''){
    $href           = vc_build_link($icon_link);
    $url            = ( isset( $href['url'] ) && $href['url'] !== '' ) ? $href['url']  : '';
    $target         = ( isset( $href['target'] ) && $href['target'] !== '' ) ? "target='" . esc_attr(trim( $href['target'] )) . "'" : '';
    $link_title     = ( isset( $href['title'] ) && $href['title'] !== '' ) ? "title='".esc_attr($href['title'])."'" : '';
    $rel            = ( isset( $href['rel'] ) && $href['rel'] !== '' ) ? "rel='".esc_attr($href['rel'])."'" : '';
    $link_prefix .= '<a class="'.esc_attr($uniqid).'" href = "' . esc_url($url) . '" '.$target.' '. $link_title .' '. $rel . '>';
    $link_sufix .= '</a>';
}

if($icon_color !== '')
    $style .= 'color:'. esc_attr( $icon_color ).';';
if($icon_style !== 'none'){
    if($icon_color_bg !== '')
        $style .= 'background:'. esc_attr( $icon_color_bg ).';';
}
if($icon_style == 'advanced'){
    $style .= 'border-style:'. esc_attr( $icon_border_style ). ';';
    $style .= 'border-color:'. esc_attr( $icon_color_border ). ';';
    $style .= 'border-width:'. esc_attr( $icon_border_size ). 'px;';
    $style .= 'width:'. esc_attr( $icon_border_spacing ). 'px;';
    $style .= 'height:'. esc_attr( $icon_border_spacing ). 'px;';
    $style .= 'line-height:'. esc_attr( $icon_border_spacing ). 'px;';
    $style .= 'border-radius:'. esc_attr( $icon_border_radius ). 'px;';
}
if($icon_size !== '')
    $style .='font-size:'.$icon_size.'px;';

if($icon_margin !== '')
    $style .= 'margin-right:'.$icon_margin.'px;';

if($icon !== ""){
    $output .= "\n".$link_prefix.'<div class="porto-icon '.esc_attr($icon_style).' '.esc_attr($el_class).'" '.$css_trans.' style="'.esc_attr($style).'">';
    $output .= "\n\t".'<i class="'.esc_attr($icon).'"></i>';
    $output .= "\n".'</div>'.$link_sufix;
}
echo $output;