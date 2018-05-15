<?php

$banner_title = $banner_desc = $banner_image = $banner_link = $banner_style = $el_class = '';
$banner_title_font_size = '';
$banner_title_style_inline = $banner_desc_style_inline = $banner_color_bg = $banner_color_title = $banner_color_desc = $banner_title_bg = '';
$image_opacity = $image_opacity_on_hover = $target = $link_title  = $rel = '';
extract(shortcode_atts( array(
    'banner_title' => '',
    'banner_desc' => '',
    'banner_image' => '',
    'lazyload' => '',
    'image_opacity' => '1',
    'image_opacity_on_hover' => '1',
    'banner_style' => '',
    'banner_title_font_size' => '',
    'banner_color_bg' => '',
    'banner_color_title' => '',
    'banner_color_desc' => '',
    'banner_title_bg' => '',
    'banner_link' => '',
    'el_class' =>'',
    'css_ibanner' => '',
),$atts));

$css_ib_styles = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css_ibanner, ' ' ), "porto_interactive_banner", $atts );
$css_ib_styles = esc_attr( $css_ib_styles );

$output = $style = $target = $link = $banner_style_inline = $title_bg = $img_style = $responsive = $target ='';

if($banner_title_bg !== '' && $banner_style == "style2"){
    $title_bg .= 'background:'.esc_attr( $banner_title_bg ).';';
}

$img = $alt = $img_width = $img_height = '';
if ( is_numeric( $banner_image ) ) {
    $img = wp_get_attachment_image_src( $banner_image, 'full');
    if ( $img ) {
        $img_width = $img[1];
        $img_height = $img[2];
        $img = $img[0];
    }
} else if ( $banner_image ) {
    $img = $banner_image;
}

if ( $banner_link ) {
    $href = vc_build_link($banner_link);
    if ( !empty( $href['url'] ) ) {
        $link           = ( isset( $href['url'] ) && $href['url'] !== '' ) ? $href['url']  : '';
        $target         = ( isset( $href['target'] ) && $href['target'] !== '' ) ? "target='" . esc_attr(trim( $href['target'] )) . "'" : '';
        $link_title     = ( isset( $href['title'] ) && $href['title'] !== '' ) ? "title='".esc_attr($href['title'])."'" : '';
        $rel            = ( isset( $href['rel'] ) && $href['rel'] !== '' ) ? "rel='".esc_attr($href['rel'])."'" : '';
    } else {
        $link = "#";
    }
} else {
    $link = "#";
}

if ( !is_numeric( $banner_title_font_size ) ) {
    $banner_title_font_size = preg_replace( '/[^0-9]/', "", $banner_title_font_size );
}
if ( $banner_title_font_size ) {
    $banner_title_style_inline .= 'font-size: '. esc_attr( $banner_title_font_size ) .'px;';
}

$interactive_banner_id = 'interactive-banner-wrap-'.rand(1000, 9999);

if ( $banner_color_bg ) {
    $banner_style_inline .= 'background:'. esc_attr( $banner_color_bg ) .';';
}

if ( $banner_color_title ) {
    $banner_title_style_inline .= 'color:'. esc_attr( $banner_color_title ) .';';
}

if ( $banner_color_desc ) {
    $banner_desc_style_inline .= 'color:'. esc_attr( $banner_color_desc ) .';';
}

if ( $image_opacity ) {
    $img_style .= 'opacity:'.esc_attr( $image_opacity ).';';
}
if ( $link !== "#" ) {
    $href = 'href="'. esc_url($link) .'"';
} else {
    $href = '';
}

$heading_tag = 'h2';

$output .= '<div class="porto-ibanner '. ( $banner_style ? 'porto-ibanner-effect-'.esc_attr($banner_style) : '' ) .' '.esc_attr($el_class).' '.esc_attr($css_ib_styles).'" '.$responsive.' style="'.esc_attr($banner_style_inline).'" data-opacity="'.esc_attr($image_opacity).'" data-hover-opacity="'.esc_attr($image_opacity_on_hover).'">';
if ( $img ) {
    global $porto_carousel_lazyload;
    if ( 'enable' === $lazyload ) {
        if ( isset( $porto_carousel_lazyload ) && $porto_carousel_lazyload === true ) {
            $img_class = 'porto-ibanner-img owl-lazy';
            $img_src_attr = 'data-src="'. esc_url( $img ) .'"';
        } else {
            $img_class = 'porto-ibanner-img lazy';
            $img_src_attr = 'data-original="'. esc_url( $img ) .'" src="" data-plugin-lazyload';
        }
    } else {
        $img_class = 'porto-ibanner-img';
        $img_src_attr = 'src="'. esc_url( $img ) .'"';
    }
    if ( $img_width ) {
        $img_src_attr .= ' width="'. esc_attr( $img_width ) .'"';
    }
    if ( $img_height ) {
        $img_src_attr .= ' height="'. esc_attr( $img_height ) .'"';
    }
    $output .= '<img class="'. $img_class .'" style="'. esc_attr( $img_style ) .'" alt="'. esc_attr( $alt ) .'" '. $img_src_attr .' />';
}
if ( $banner_title || $banner_desc || $content ) {
    $output .= '<div id="'. esc_attr( $interactive_banner_id ) .'" class="porto-ibanner-desc" style="'. esc_attr( $title_bg ) .'">';
    $output .= '<'.$heading_tag.' class="porto-ibanner-title" style="'. esc_attr( $banner_title_style_inline ) .'">'. do_shortcode( $banner_title ) .'</'.$heading_tag.'>';
    $output .= '<div class="porto-ibanner-content" style="'. esc_attr( $banner_desc_style_inline ) .'">'. do_shortcode( $banner_desc ? $banner_desc : $content ) .'</div>';
    $output .= '</div>';
}
if ( $href ) {
    $output .= '<a class="porto-ibanner-link" '.$href.' '.$target.' '. $link_title .' '. $rel .'></a>';
}
$output .= '</div>';

echo $output;