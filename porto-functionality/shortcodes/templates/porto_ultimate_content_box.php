<?php

extract( shortcode_atts( array(
    'bg_type' => 'bg_color',
    'bg_img' => '',
    'bg_clr' => '',
    'bg_repeat' => 'repeat',
    'bg_size' => 'cover',
    'bg_position' => 'center center',
    'border' => '',
    'box_shadow' => '',
    'box_shadow_color' => '',
    'link' => '',
    'hover_box_shadow' => '',
    'min_height' => '',
    'padding' => '',
    'margin' => '',
    'css_contentbox' => '',
    'el_class' => '',
), $atts ) );

$style = $url = $link_title = $target = $hover = $shadow = $data_attr = $target = $link_title  = $rel = '';

$content_box_design_style = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css_contentbox, ' ' ), "porto_ultimate_content_box", $atts );

if ( $bg_type ) {
    switch ($bg_type) {
        case 'bg_color':
            if( $bg_clr ) {
                $style .= 'background-color:'.$bg_clr.';';
                $data_attr .= ' data-bg="'.esc_attr( $bg_clr ).'" ';
            }
            break;
        case 'bg_image':
            if( $bg_img ) {
                $img = wp_get_attachment_image_src( $bg_img, 'large');
                $style .= "background-image:url('".esc_url($img[0])."');";
                $style .= 'background-size: '.esc_attr($bg_size).';';
                $style .= 'background-repeat: '.esc_attr($bg_repeat).';';
                $style .= 'background-position: '.esc_attr($bg_position).';';
                $style .= 'background-color: rgba(0, 0, 0, 0);';
            }
        break;
    }
}


/*  box shadow  */
if ( $box_shadow ) {
    $style .= porto_get_box_shadow( $box_shadow, 'css' );
}

/*  box shadow on hover */
if ( $hover_box_shadow ) {

    $data = porto_get_box_shadow( $hover_box_shadow, 'data' );

    if ( strpos($data,'none') !== false ) {
        $data = 'none';
    }
    if ( strpos($data,'inherit') !== false ) {
        if($box_shadow!='') {
            $data = porto_get_box_shadow( $box_shadow, 'data' );
        }
    }

    $hover .= ' data-hover_box_shadow="'. esc_attr($data) .'" ';
}


/* border */
if ( $border ) {
    $style .= 'border-width:'. esc_attr( $border ) .'px;';
}

/* link */
if ( $link ) {
    $href           =   vc_build_link($link);
    $url            = ( isset( $href['url'] ) && $href['url'] !== '' ) ? $href['url']  : '';
    $target         = ( isset( $href['target'] ) && $href['target'] !== '' ) ? "target='" . esc_attr( trim( $href['target'] ) ) . "'" : '';
    $link_title     = ( isset( $href['title'] ) && $href['title'] !== '' ) ? "title='".esc_attr($href['title'])."'" : '';
    $rel            = ( isset( $href['rel'] ) && $href['rel'] !== '' ) ? "rel='".esc_attr($href['rel'])."'" : '';
}

if ( $min_height ) {
    $style .= 'min-height:'. esc_attr( $min_height ) .'px;';
}
if ( $padding ) {
    $style .= 'padding:'. esc_attr( $padding );
}
if ( $margin ) {
    $style .= 'margin:'. esc_attr( $margin );
}

$output = '<div class="porto-ultimate-content-box-container '.esc_attr($el_class).'" >';
if ( $link ) {
    $output .= '<a class="porto-ultimate-content-box-anchor" href="'.esc_url($url).'" '. $link_title .' '. $target .' '. $rel .'>';
}
    $output .= '<div class="porto-ultimate-content-box '. esc_attr( $content_box_design_style ) .'" style="'.esc_attr($style).'" '.$hover.' '.$data_attr.'>';
    $output .= do_shortcode( $content );
    $output .= '</div>';
if ( $link ) {
    $output .= '</a>';
}
$output .= '</div>';

echo $output;