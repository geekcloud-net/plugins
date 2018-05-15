<?php

if ( ! function_exists( 'porto_override_shortcodes' ) ) {
    function porto_override_shortcodes( $item_space, $item_animation ) {
        global $shortcode_tags, $_shortcode_tags;
        $_shortcode_tags = $shortcode_tags;
        $disabled_tags = array( '' );
        foreach ( $shortcode_tags as $tag => $cb ) {
            if ( in_array( $tag, $disabled_tags ) ) {
                continue;
            }
            $shortcode_tags[ $tag ]            = 'porto_wrap_shortcode_in_div';
            $_shortcode_tags["porto_item_space"] = $item_space;
            $_shortcode_tags["item_animation"] = $item_animation;
        }
    }
}

if ( ! function_exists( 'porto_wrap_shortcode_in_div' ) ) {
    function porto_wrap_shortcode_in_div( $attr, $content = null, $tag ) {
        global $_shortcode_tags;

        $attrs = $_shortcode_tags["item_animation"] ? ' data-appear-animation="' . esc_attr( $_shortcode_tags["item_animation"] ) . '"' : '';
        return '<div class="porto-item-wrap"'. $attrs .'>' . call_user_func( $_shortcode_tags[ $tag ], $attr, $content, $tag ) . '</div>';
    }
}
if ( ! function_exists( 'porto_restore_shortcodes' ) ) {
    function porto_restore_shortcodes() {
        global $shortcode_tags, $_shortcode_tags;
        // Restore the original callbacks
        if ( isset( $_shortcode_tags ) ) {
            $shortcode_tags = $_shortcode_tags;
        }
    }
}

$slides_on_desk = $slides_on_tabs = $slides_on_mob = $slide_to_scroll = $speed = $infinite_loop = $autoplay = $autoplay_speed = '';
$lazyload       = $arrows = $dots = $dots_icon = $next_icon = $prev_icon = $dots_color = $swipe = $touch_move = '';
$rtl            = $arrow_color = $arrow_size = $arrow_style = $arrow_border_color = $item_space = $el_class = '';
$item_animation = '';

wp_enqueue_style( 'font-awesome' );

extract( shortcode_atts( array(
    "slides_on_desk"     => "5",
    "slides_on_tabs"     => "3",
    "slides_on_mob"      => "2",
    "slide_to_scroll"    => "",
    "speed"              => "300",
    "infinite_loop"      => "on",
    "autoplay"           => "on",
    "autoplay_speed"     => "5000",
    "lazyload"           => "",
    "arrows"             => "show",
    "dots"               => "show",
    "icon_type"          => "fontawesome",
    "dots_icon_type"     => "fontawesome",
    "dots_icon"          => "fa fa-circle-o",
    "next_icon"          => "fa fa-chevron-right",
    "prev_icon"          => "fa fa-chevron-left",
    "next_icon_simpleline"  => "",
    "next_icon_porto"  => "",
    "prev_icon_simpleline"  => "",
    "prev_icon_porto"  => "",
    "dots_icon_simpleline"  => "",
    "dots_icon_porto"  => "",
    "dots_color"         => "#333333",
    "arrow_color"        => "#333333",
    "arrow_size"         => "20",
    "arrow_style"        => "default",
    "arrow_bg_color" => "",
    "swipe"              => "true",
    "touch_move"         => "on",
    "rtl"                => "",
    "item_space"         => "15",
    "el_class"           => "",
    "item_animation"     => "",
    "animation_type"     => "",
    "adaptive_height"    => "",
    "css_ad_caraousel"   => "",
), $atts ) );

if ( $animation_type ) {
    $item_animation = $animation_type;
}

$uid = uniqid( rand() );

$settings = $responsive = $infinite = $dot_display = $custom_dots = $arr_style = $wrap_data = $design_style = '';

switch ( $icon_type ) {
    case 'simpleline':
        $next_icon = $next_icon_simpleline;
        $prev_icon = $prev_icon_simpleline;
        break;
    case 'porto':
        $next_icon = $next_icon_porto;
        $prev_icon = $prev_icon_porto;
        break;
}
switch ( $dots_icon_type ) {
    case 'simpleline':
        $dots_icon = $dots_icon_simpleline;
        break;
    case 'porto':
        $dots_icon = $dots_icon_porto;
        break;
}

$desing_style = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css_ad_caraousel, ' ' ), "porto_ultimate_carousel", $atts );
$desing_style = esc_attr( $desing_style );
if ( $slide_to_scroll == "single" ) {
    $slide_to_scroll = 1;
} else {
    $slide_to_scroll = $slides_on_desk;
}

$arr_style .= 'color:' . $arrow_color . '; font-size:' . $arrow_size . 'px;';
if ( $arrow_style == "circle-bg" || $arrow_style == "square-bg" ) {
    $arr_style .= "background:" . esc_attr( $arrow_bg_color ) . ";";
}

if ( $dots !== "off" ) {
    $settings .= 'dots: true,';
} else {
    $settings .= 'dots: false,';
}
if ( $autoplay !== 'off' ) {
    $settings .= 'autoplay: true,';
}
if ( $autoplay_speed !== '' ) {
    $settings .= 'autoplaySpeed: ' . $autoplay_speed . ',';
}
if ( $speed !== '' ) {
    $settings .= 'speed: ' . $speed . ',';
}
if ( $infinite_loop === 'off' ) {
    $settings .= 'infinite: false,';
} else {
    $settings .= 'infinite: true,';
}
if ( $lazyload !== 'off' && $lazyload !== '' ) {
    $settings .= 'lazyLoad: true,';
}

if ( is_rtl() ) {
    if ( $arrows !== 'off' ) {
        $settings .= 'arrows: true,';
        $settings .= 'nextArrow: \'<button type="button" role="button" aria-label="Next" style="' . esc_attr($arr_style) . '" class="slick-next ' . esc_attr($arrow_style) . '"><i class="' . esc_attr($prev_icon) . '"></i></button>\',';
        $settings .= 'prevArrow: \'<button type="button" role="button" aria-label="Previous" style="' . esc_attr($arr_style) . '" class="slick-prev ' . esc_attr($arrow_style) . '"><i class="' . esc_attr($next_icon) . '"></i></button>\',';
    } else {
        $settings .= 'arrows: false,';
    }
} else {
    if ( $arrows !== 'off' ) {
        $settings .= 'arrows: true,';
        $settings .= 'nextArrow: \'<button type="button" role="button" aria-label="Next" style="' . esc_attr($arr_style) . '" class="slick-next ' . esc_attr($arrow_style) . '"><i class="' . esc_attr($next_icon) . '"></i></button>\',';
        $settings .= 'prevArrow: \'<button type="button" role="button" aria-label="Previous" style="' . esc_attr($arr_style) . '" class="slick-prev ' . esc_attr($arrow_style) . '"><i class="' . esc_attr($prev_icon) . '"></i></button>\',';
    } else {
        $settings .= 'arrows: false,';
    }
}


if ( $slide_to_scroll !== '' ) {
    $settings .= 'slidesToScroll:' . $slide_to_scroll . ',';
}
if ( $slides_on_desk !== '' ) {
    $settings .= 'slidesToShow:' . $slides_on_desk . ',';
}
if ( $slides_on_mob == '' ) {
    $slides_on_mob = $slides_on_desk;
}
if ( $slides_on_tabs == '' ) {
    $slides_on_tabs = $slides_on_desk;
}

    $settings .= 'swipe: true,';
    $settings .= 'draggable: true,';

if ( $touch_move == "on" ) {
    $settings .= 'touchMove: true,';
} else {
    $settings .= 'touchMove: false,';
}

if ( $rtl !== "off" && $rtl !== "" ) {
    $settings .= 'rtl: true,';
    $wrap_data = 'dir="rtl"';
}

$site_rtl = 'false';
if ( is_rtl() ) {
    $site_rtl = 'true';
}


if ( is_rtl() ) {
    $settings .= 'rtl: true,';
}

$settings .= 'pauseOnHover: true,';


if ( $adaptive_height === 'on' ) {
    $settings .= 'adaptiveHeight: true,';
}

$settings .= 'responsive: [
                {
                  breakpoint: 1025,
                  settings: {
                    slidesToShow: ' . $slides_on_desk . ',
                    slidesToScroll: ' . $slide_to_scroll . ', ' . $infinite . ' ' . $dot_display . '
                  }
                },
                {
                  breakpoint: 769,
                  settings: {
                    slidesToShow: ' . $slides_on_tabs . ',
                    slidesToScroll: ' . $slides_on_tabs . '
                  }
                },
                {
                  breakpoint: 481,
                  settings: {
                    slidesToShow: ' . $slides_on_mob . ',
                    slidesToScroll: ' . $slides_on_mob . '
                  }
                }
            ],';
$settings .= 'pauseOnDotsHover: true,';

if ( $dots_icon !== 'off' && $dots_icon !== '' ) {
    if ( $dots_color !== 'off' && $dots_color !== '' ) {
        $custom_dots = 'style="color:' . esc_attr( $dots_color ) . ';"';
    }
    $settings .= 'customPaging: function(slider, i) {
       return \'<i type="button" ' . $custom_dots . ' class="' . esc_attr( $dots_icon ) . '" data-role="none"></i>\';
    },';
}

ob_start();
$uniqid = uniqid( rand() );

echo '<div id="porto-carousel-' . esc_attr( $uniqid ) . '" class="porto-carousel-wrapper ' . esc_attr( $desing_style ) . ' ' . esc_attr($el_class) . '" data-gutter="' . esc_attr($item_space) . '" data-rtl="' . esc_attr($site_rtl) . '" >';
echo '<div class="porto-ultimate-carousel porto-carousel-' . esc_attr($uid) . ' " ' . $wrap_data . '>';
porto_override_shortcodes( $item_space, $item_animation );
echo do_shortcode( $content );
porto_restore_shortcodes();
echo '</div>';
echo '</div>';
?>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('.porto-carousel-<?php echo $uid; ?>').slick({<?php echo $settings; ?>});
    });
</script>
<?php
echo ob_get_clean();