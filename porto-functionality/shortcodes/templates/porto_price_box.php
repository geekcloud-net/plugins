<?php
$output = $title = $desc = $is_popular = $popular_label = $price = $skin = $show_btn = $btn_label = $btn_action = $popup_iframe = $popup_block = $popup_size = $popup_animation = $btn_link = $btn_size = $btn_pos = $btn_skin = $animation_type = $animation_duration = $animation_delay = $el_class = '';
extract(shortcode_atts(array(
    'title' => '',
    'desc' => '',
    'is_popular' => false,
    'popular_label' => '',
    'price' => '',
    'skin' => 'custom',
    'show_btn' => false,
    'btn_label' => '',
    'btn_action' => 'open_link',
    'btn_link' => '',
    'popup_iframe' => '',
    'popup_block' => '',
    'popup_size' => 'md',
    'popup_animation' => 'mfp-fade',
    'btn_size' => '',
    'btn_pos' => '',
    'btn_skin' => 'custom',
    'animation_type' => '',
    'animation_duration' => 1000,
    'animation_delay' => 0,
    'el_class' => ''
), $atts));

$el_class = porto_shortcode_extract_class( $el_class );

if ($is_popular)
    $el_class .= ' most-popular';

if ($skin)
    $el_class .= ' plan-' . $skin;

$btn_class = 'btn';
$btn_html = '';
if ($btn_size)
    $btn_class .= ' btn-' . $btn_size;
if ('custom' !== $btn_skin)
    $btn_class .= ' btn-' . $btn_skin;
else
    $btn_class .= ' btn-default';
if ('bottom' !== $btn_pos)
    $btn_class .= ' btn-top';
else
    $btn_class .= ' btn-bottom';

if ($btn_action === 'open_link') {
    $link = ( '||' === $btn_link ) ? '' : $btn_link;
    $link = vc_build_link( $link );
    $use_link = false;
    if ( strlen( $link['url'] ) > 0 ) {
        $use_link = true;
        $a_href = $link['url'];
        $a_title = $link['title'];
        $a_target = strlen( $link['target'] ) > 0 ? $link['target'] : '_self';
    } else {
        $link = 'url:' . urlencode($btn_link) . '||';
        $link = vc_build_link( $link );
        if ( strlen( $link['url'] ) > 0 ) {
            $use_link = true;
            $a_href = $link['url'];
            $a_title = $link['title'];
            $a_target = strlen( $link['target'] ) > 0 ? $link['target'] : '_self';
        }
    }

    $attributes = array();
    if ( $use_link ) {
        $attributes[] = 'href="' . esc_url( trim( $a_href ) ) . '"';
        $attributes[] = 'title="' . esc_attr( trim( $a_title ) ) . '"';
        $attributes[] = 'target="' . esc_attr( trim( $a_target ) ) . '"';
    }

    $attributes = implode( ' ', $attributes );

    if ($use_link) {
        $btn_html .= '<a ' . $attributes . ' class="' . $btn_class . '">' . $btn_label . '</a>';
    } else {
        $btn_html .= '<span class="' . $btn_class . '">' . $btn_label . '</span>';
    }
} else if ($btn_action === 'popup_iframe') {
    if ($popup_iframe)
        $btn_html .= '<a class="' . $btn_class . ' porto-popup-iframe" href="' . $popup_iframe . '">' . $btn_label . '</a>';
} else {
    if ($popup_block) {
        $id = 'popup' . rand();
        $btn_html .= '<a class="' . $btn_class . ' porto-popup-content" href="#' . $id . '" data-animation="' . esc_attr($popup_animation) . '">' . $btn_label . '</a>';
        $btn_html .= '<div id="' . $id . '" class="dialog dialog-' . esc_attr($popup_size) . ' zoom-anim-dialog mfp-hide">' . do_shortcode( '[porto_block name="' . $popup_block . '"]' ) . '</div>';
    }
}

if ($btn_html) {
    if ('bottom' === $btn_pos) {
        $el_class .= ' plan-btn-bottom';
    } else {
        $el_class .= ' plan-btn-top';
    }
}

global $porto_price_boxes_count_md, $porto_price_boxes_count_sm;

if (false === $porto_price_boxes_count_md)
    $porto_price_boxes_count_md = 4;

if (false === $porto_price_boxes_count_sm)
    $porto_price_boxes_count_sm = 2;

$css_class = ' col-lg-' . (12 / $porto_price_boxes_count_md);
$css_class .= ' col-md-' . (12 / $porto_price_boxes_count_sm);

$output = '<div class="' . $css_class . '"><div class="porto-price-box plan ' . $el_class . '"';
if ($animation_type) {
    $output .= ' data-appear-animation="'.$animation_type.'"';
    if ($animation_delay)
        $output .= ' data-appear-animation-delay="'.$animation_delay.'"';
    if ($animation_duration && $animation_duration != 1000)
        $output .= ' data-appear-animation-duration="'.$animation_duration.'"';
}
$output .= '>';

if ($is_popular && $popular_label) {
    $output .= '<div class="plan-ribbon-wrapper"><div class="plan-ribbon">' . $popular_label . '</div></div>';
}

if ($title || $price || $desc) {
    $output .= '<h3>';
    if ($title)
        $output .= $title;
    if ($desc)
        $output .= '<em class="desc">' . $desc . '</em>';
    if ($price)
        $output .= '<span>' . $price . '</span>';
    $output .= '</h3>';
}

if ($show_btn && 'bottom' !== $btn_pos) {
    $output .= $btn_html;
}

$output .= porto_shortcode_js_remove_wpautop($content, true);

if ($show_btn && 'bottom' === $btn_pos) {
    $output .= $btn_html;
}

$output .= '</div></div>';

echo $output;