<?php

$logo_img = $logo_hover_img = $el_class = '';
extract( shortcode_atts(array(
    'logo_img' => '',
    'logo_hover_img' => '',
    'el_class' => '',
), $atts) );

if ( is_numeric( $logo_img ) ) {
    $attachment = wp_get_attachment_image_src( $logo_img, 'full' );
    if ( isset( $attachment ) ) {
        $logo_img = $attachment[0];
    }
}
if ( is_numeric( $logo_hover_img ) ) {
    $attachment = wp_get_attachment_image_src( $logo_hover_img, 'full' );
    if ( isset( $attachment ) ) {
        $logo_hover_img = $attachment[0];
    }
}

$html = '';
$html .= '<div class="carousel-logo-item background-color-light '. esc_attr( $el_class ) .'">';
    $html .= '<div class="carousel-logo-pannel carousel-logo-pb center">';
    if ( $logo_img ) {
        $html .= '<img src="'. esc_url( $logo_img ) .'" class="img-responsive" alt="">';
    }
    $html .= '</div>';
    $html .= '<div class="carousel-logo-pannel carousel-logo-hover pt-xlg pl-md pr-md pb-sm ">';
        if ( $logo_hover_img ) {
            $html .= '<div class="carousel-logo-hover-img">';
                $html .= '<img src="'. esc_url( $logo_hover_img ) .'" class="img-responsive" alt="">';
            $html .= '</div>';
        }
        $html .= '<div class="carousel-logo-description font-weight-normal">';
            $html .= do_shortcode( $content );
        $html .= '</div>';
    $html .= '</div>';
$html .= '</div>';

echo $html;
