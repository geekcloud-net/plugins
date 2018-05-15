<?php

$icon_type = $icon_img = $img_width = $icon = $icon_color = $icon_color_bg = $icon_size = $icon_style = $icon_border_style = $icon_border_radius = $icon_color_border = $icon_border_size = $icon_border_spacing = $icon_link = $el_class = $icon_animation = $animation_type = $counter_title = $counter_value = $icon_position = $counter_style = $font_size_title = $font_size_counter = $counter_font = $title_font = $speed = $counter_sep = $counter_suffix = $counter_prefix = $counter_decimal = $counter_color_txt = $desc_font_line_height = $title_font_line_height = '';
$title_font = $title_font_style = $title_font_size = $title_font_color = $desc_font = $desc_font_style = $desc_font_size = $desc_font_color = $suf_pref_typography = $suf_pref_font = $suf_pref_font_style = $suf_pref_font_color = $suf_pref_font_size = $suf_pref_line_height = '';
extract(shortcode_atts( array(
    'icon_type' => 'fontawesome',
    'icon' => '',
    'icon_simpleline' => '',
    'icon_porto' => '',
    'icon_img' => '',
    'img_width' => '48',
    'icon_size' => '32',
    'icon_color' => '#333333',
    'icon_style' => 'none',
    'icon_color_bg' => '#ffffff',
    'icon_color_border' => '#333333',
    'icon_border_style' => '',
    'icon_border_size' => '1',
    'icon_border_radius' => '500',
    'icon_border_spacing' => '50',
    'icon_link' => '',
    'icon_animation' => '',
    'animation_type',
    'counter_title' => '',
    'counter_value' => '1250',
    'counter_sep' => ',',
    'counter_suffix' => '',
    'counter_prefix' => '',
    'counter_decimal' => '.',
    'icon_position'=>'top',
    'counter_style'=>'',
    'speed'=>'3',
    'font_size_title' => '18',
    'font_size_counter' => '28',
    'counter_color_txt' => '',
    'title_font' => '',
    'title_font_style' => '',
    'title_font_size' => '',
    'title_font_line_height'=> '',
    'desc_font' => '',
    'desc_font_style' => '',
    'desc_font_size' => '',
    'desc_font_color' => '',
    'desc_font_line_height'=> '',
    'el_class'=>'',
    'suf_pref_font' =>'',
    'suf_pref_font_color' =>'',
    'suf_pref_font_size' =>'',
    'suf_pref_line_height' =>'',
    'suf_pref_font_style' =>'',
    'css_stat_counter' => '',
),$atts));

switch ( $icon_type ) {
    case 'simpleline':
        $icon = $icon_simpleline;
        break;
    case 'porto':
        $icon = $icon_porto;
        break;
}

$css_stat_counter = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css_stat_counter, ' ' ), "stat_counter", $atts );
$css_stat_counter = esc_attr( $css_stat_counter );
$class = $style = $title_style = $desc_style = $suf_pref_style = '';
$stats_icon = do_shortcode('[porto_icon icon_type="'.esc_attr($icon_type).'" icon="'.esc_attr($icon).'" icon_img="'.esc_attr($icon_img).'" img_width="'.esc_attr($img_width).'" icon_size="'.esc_attr($icon_size).'" icon_color="'.esc_attr($icon_color).'" icon_style="'.esc_attr($icon_style).'" icon_color_bg="'.esc_attr($icon_color_bg).'" icon_color_border="'.esc_attr($icon_color_border).'"  icon_border_style="'.esc_attr($icon_border_style).'" icon_border_size="'.esc_attr($icon_border_size).'" icon_border_radius="'.esc_attr($icon_border_radius).'" icon_border_spacing="'.esc_attr($icon_border_spacing).'" icon_link="'.esc_attr($icon_link).'" icon_animation="'.esc_attr($icon_animation).'"]');

/* title */
if ( $title_font ) {
    $title_style .= 'font-family:\''.esc_attr($title_font).'\';';
}
if ( $title_font_style ) {
    $title_style .= 'font-weight:'.esc_attr($title_font_style).';';
}
if ( $title_font_size ) {
    $font_size_title ='';
}
if (!is_numeric($title_font_size)){
    $title_font_size = preg_replace( '/[^0-9]/', "", $title_font_size );
}
if (!is_numeric($title_font_line_height)){
    $title_font_line_height = preg_replace( '/[^0-9]/', "", $title_font_line_height );
}
if ( $title_font_size ) {
    $title_style .= 'font-size:'.esc_attr($title_font_size).'px;';
}
if ( $title_font_line_height ) {
    $title_style .= 'line-height:'.esc_attr($title_font_line_height).'px;';
}

if ( $desc_font ) {
    $desc_style .= 'font-family:\''.esc_attr($desc_font).'\';';
}
if ( $desc_font_style != '') {
    $desc_style .= 'font-weight:'.esc_attr( $desc_font_style).';';
}


if ( $desc_font_size !='' || $suf_pref_font_size !='') {
    $font_size_counter ='';
}

if ( !is_numeric( $desc_font_size ) ) {
    $desc_font_size = preg_replace( '/[^0-9]/', "", $desc_font_size );
}
if ( !is_numeric( $desc_font_line_height ) ){
    $desc_font_line_height = preg_replace( '/[^0-9]/', "", $desc_font_line_height );
}
if ( $desc_font_size ) {
    $desc_style .= 'font-size:'.esc_attr( $desc_font_size).'px;';
}
if ( $desc_font_line_height != '') {
    $desc_style .= 'line-height:'.esc_attr( $desc_font_line_height).'px;';
}

if ( $desc_font_color ) {
    $desc_style .= 'color:'.esc_attr( $desc_font_color).';';
}

if ( $counter_color_txt ) {
    $counter_color = 'color:'.esc_attr($counter_color_txt).';';
} else {
    $counter_color = '';
}
if ( $icon_color ) {
    $style.='color:'.esc_attr($icon_color).';';
}
if ( $animation_type !== 'none') {
    $css_trans = 'data-appear-animation="'.esc_attr($animation_type).'"';
}
if ( $font_size_counter )
    $counter_font = 'font-size:'.esc_attr($font_size_counter).'px;';

if ( $font_size_title ) {
    $title_font = 'font-size:'.esc_attr($font_size_title).'px;';
}


if ( $suf_pref_font ) {
    $suf_pref_style .= 'font-family:\''.esc_attr($suf_pref_font).'\';';
}
if ($suf_pref_font_style ) {
    $suf_pref_style .= 'font-weight:'.esc_attr($suf_pref_font_style).';';
}

if ( !is_numeric( $suf_pref_font_size) ){
    $suf_pref_font_size = preg_replace( '/[^0-9]/', "", $suf_pref_font_size );
}
if ( !is_numeric( $suf_pref_line_height ) ){
    $suf_pref_line_height = preg_replace( '/[^0-9]/', "", $suf_pref_line_height );
}
if ( $suf_pref_font_size ) {
    $suf_pref_style .= 'font-size:'.esc_attr($suf_pref_font_size).'px;';
}
if ( $suf_pref_line_height ) {
    $suf_pref_style .= 'line-height:'.esc_attr($suf_pref_line_height).'px;';
}

$suf_pref_style .= 'color:'.esc_attr($suf_pref_font_color);


if ( $counter_style ) {
    $class = $counter_style;
    if ( strpos( $counter_style, 'no_bg' ) ) {
        $style.= "border:2px solid ".$counter_icon_bg_color.';';
    } elseif ( strpos( $counter_style, 'with_bg' ) && $counter_icon_bg_color ) {
        $style.='background:'.$counter_icon_bg_color.';';
    }
}
if ( $el_class ) {
    $class.= ' '.$el_class;
}
$ic_position = 'stats-'.$icon_position;
$ic_class = 'porto-sicon-'.$icon_position;
$output = '<div class="stats-block '.esc_attr($ic_position).' '.esc_attr($class).' '.esc_attr($css_stat_counter).'">';
    $id = 'counter_'.uniqid(rand());
    if ($counter_sep == ""){
        $counter_sep = 'none';
    }
    if ($counter_decimal == ""){
        $counter_decimal = 'none';
    }
    if ($icon_position !== "right")
        $output .= '<div class="'.esc_attr($ic_class).'">'.$stats_icon.'</div>';
    $output .= '<div class="stats-desc">';
        if ($counter_prefix !== ''){
            $output .= '<div class="counter_prefix mycust" style="'.esc_attr($counter_font).' '.esc_attr($suf_pref_style).'">'.$counter_prefix.'</div>';
        }
        $output .= '<div id="'.esc_attr($id).'" data-id="'.esc_attr($id).'" class="stats-number" style="'.esc_attr($counter_font).' '.esc_attr($counter_color).' '.esc_attr($desc_style).'" data-speed="'.esc_attr($speed).'" data-counter-value="'.esc_attr($counter_value).'" data-separator="'.esc_attr($counter_sep).'" data-decimal="'.esc_attr($counter_decimal).'">0</div>';
        if ($counter_suffix !== ''){
            $output .= '<div class="counter_suffix mycust" style="'.esc_attr($counter_font).' '.esc_attr($suf_pref_style).'">'.$counter_suffix.'</div>';
        }
        $output .= '<div class="stats-text" style="'.esc_attr($title_font).' '.esc_attr($counter_color).' '.esc_attr($title_style).'">'.$counter_title.'</div>';
    $output .= '</div>';
    if ($icon_position == "right")
        $output .= '<div class="'.esc_attr($ic_class).'">'.$stats_icon.'</div>';
$output .= '</div>';

echo $output;