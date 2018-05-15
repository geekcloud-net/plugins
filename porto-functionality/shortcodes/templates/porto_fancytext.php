<?php

$output = $fancytext_strings = $fancytext_prefix = $fancytext_suffix = $strings_tickerspeed = $fancytext_align = $strings_font_family = $strings_font_style = $strings_font_size = $sufpref_color = $strings_line_height = $ticker_wait_time = $ticker_hover_pause = $el_class = '';
$prefsuf_font_family = $prefsuf_font_style = $prefix_suffix_font_size = $prefix_suffix_line_height = $sufpref_bg_color ='';
$id = uniqid(rand());

extract( shortcode_atts(array(
    'fancytext_strings' => '',
    'fancytext_prefix' => '',
    'fancytext_suffix' => '',
    'strings_tickerspeed' => '200',
    'fancytext_tag' => 'h2',
    'fancytext_align' => 'center',
    'strings_font_family' => '',
    'strings_font_style' => '',
    'strings_font_size' => '',
    'sufpref_color' => '',
    'strings_line_height' => '',
    'ticker_wait_time' => '3000',
    'ticker_hover_pause' => '',
    'ticker_background' => '',
    'fancytext_color' => '',
    'prefsuf_font_family' => '',
    'prefsuf_font_style' => '',
    'prefix_suffix_font_size' =>'',
    'prefix_suffix_line_height' =>'',
    'sufpref_bg_color' => '',
    'el_class' => '',
    'css_fancy_design' =>'',
), $atts ) );

$string_inline_style = $word_rotate_inline = $prefsuf_style = $css_design_style = '';

$css_design_style = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css_fancy_design, ' ' ), "porto_fancytext", $atts );

$css_design_style = esc_attr( $css_design_style );

if ( $strings_font_family ) {
    $string_inline_style .= 'font-family:\''.esc_attr( $strings_font_family ).'\';';
}
if ( $strings_font_style ) {
    $string_inline_style .= 'font-weight:'. esc_attr( $strings_font_style ) .';';
}

if ( $prefsuf_font_family ){
    $prefsuf_style .= 'font-family:\''.esc_attr( $prefsuf_font_family ).'\';';
}
if ( $prefsuf_font_style ) {
    $prefsuf_style .= 'font-weight:'. esc_attr( $prefsuf_font_style ) .';';
}

if ( !is_numeric( $strings_font_size ) ) {
    $strings_font_size = preg_replace( '/[^0-9]/', "", $strings_font_size );
}
if ( !is_numeric( $strings_line_height ) ) {
    $strings_line_height = preg_replace( '/[^0-9]/', "", $strings_line_height );
}
if ( $strings_font_size ) {
    $string_inline_style .= 'font-size:'.esc_attr( $strings_font_size ).'px;';
}
if( $strings_line_height ) {
    $string_inline_style .= 'line-height:'.esc_attr( $strings_line_height ).'px;';
}


if ( !is_numeric( $prefix_suffix_font_size ) ) {
    $prefix_suffix_font_size = preg_replace( '/[^0-9]/', "", $prefix_suffix_font_size );
}
if ( !is_numeric( $prefix_suffix_line_height ) ) {
    $prefix_suffix_line_height = preg_replace( '/[^0-9]/', "", $prefix_suffix_line_height );
}
if ( $prefix_suffix_font_size ) {
    $prefsuf_style .= 'font-size:'.esc_attr( $prefix_suffix_font_size ).'px !important;';
}
if ( $prefix_suffix_line_height ) {
    $prefsuf_style .= 'line-height:'.esc_attr( $prefix_suffix_line_height ).'px !important;';
}


if ( $sufpref_color ) {
    $prefsuf_style .= 'color:'. esc_attr( $sufpref_color ) .';';
}
if ( $sufpref_bg_color ) {
    $prefsuf_style .= 'background :'. esc_attr( $sufpref_bg_color ) .';';
}
if ( $fancytext_align ) {
    $string_inline_style .= 'text-align:'. esc_attr( $fancytext_align ) .';';
}


$order   = array( "\r\n", "\n", "\r", "<br/>", "<br>" );
$replace = '|';

$str = str_replace( $order, $replace, $fancytext_strings );

$lines = explode( "|", $str );

$count_lines = count( $lines );


if ( $fancytext_color ) {
    $word_rotate_inline .= 'color:'. esc_attr( $fancytext_color ) .';';
}
if ( $ticker_background ) {
    $word_rotate_inline .= 'background:'. esc_attr( $ticker_background ) .';';
}

$classes = 'word-rotator-title';
if ( $css_design_style ) {
    $classes .= ' ' . esc_attr($css_design_style);
}
if ( $el_class ) {
    $classes .= ' ' . esc_attr($el_class);
}
if ( $ticker_hover_pause != 'true' ) {
    $ticker_hover_pause = 'false';
}
$plugin_options = "{'delay': ". esc_attr( $ticker_wait_time ) .", 'animDelay': ". esc_attr( $strings_tickerspeed ) .", 'pauseOnHover': ". esc_attr( $ticker_hover_pause ) ."}";
$output = '<'.esc_html( $fancytext_tag ) .' class="'. esc_attr( $classes ) .'" style="'. esc_attr( $string_inline_style ) .'">';

    if ( trim( $fancytext_prefix ) ) {
        $output .= '<span class="word-rotate-prefix" style="'. esc_attr( $prefsuf_style ) .'">'. esc_html( ltrim( $fancytext_prefix ) ) .'</span>';
    }
    $output .= '<strong'. ( $ticker_background ? ' class="inverted"' : '' ) .' style="'. esc_attr( $word_rotate_inline ) .'"><span class="word-rotate" data-plugin-options="'. $plugin_options .'"><span class="word-rotate-items">';
        foreach( $lines as $key => $line ) {
            $output .= '<span>'. strip_tags( $line ).'</span>';
        }
    $output .= '</span></span></strong>';
    if ( trim( $fancytext_suffix ) ) {
        $output .= '<span class="word-rotate-suffix" style="'. esc_attr( $prefsuf_style ) .'">'. esc_html( rtrim( $fancytext_suffix ) ) .'</span>';
    }
$output .= '</'. esc_html( $fancytext_tag ) .'>';

echo $output;