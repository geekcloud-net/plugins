<?php

$icon_type = $icon_img = $img_width = $icon = $icon_color = $icon_color_bg = $icon_size = $icon_style = $icon_border_style = $icon_border_radius = $icon_color_border = $icon_border_size = $icon_border_spacing = $el_class = $animation_type = $title = $link = $hover_effect = $pos = $read_more= $read_text = $box_border_style = $box_border_width =$box_border_color = $box_bg_color = $pos = $css_class = $desc_font_line_height = $title_font_line_height = $heading_tag = '';
$title_font = $title_font_style = $title_font_size = $title_font_color = $desc_font = $desc_font_style = $desc_font_size = $desc_font_color = $box_min_height = $target = $link_title  = $rel = '';
extract(shortcode_atts(array(
    'icon_type' => 'fontawesome',
    'icon' => 'none',
    'icon_simpleline' => 'none',
    'icon_porto' => 'none',
    'icon_img' => '',
    'img_width' => '48',
    'icon_size' => '32',
    'icon_color' => '#333',
    'icon_style' => 'none',
    'icon_color_bg' => '#ffffff',
    'icon_color_border' => '#333333',
    'icon_border_style' => '',
    'icon_border_size' => '1',
    'icon_border_radius' => '500',
    'icon_border_spacing' => '50',
    'icon_animation' => '',
    'title' => '',
    'subtitle' => '',
    'link'     => '',
    'hover_effect' => 'style_1',
    'pos'       => 'default',
    'read_more'  => 'none',
    'read_text'  => 'Read More',
    'heading_tag' => 'h3',
    'title_font' => '',
    'title_font_style' => '',
    'title_font_size' => '',
    'title_font_line_height'=> '',
    'title_font_color' => '',
    'subtitle_font_style' => '',
    'subtitle_font_size' => '',
    'subtitle_font_line_height'=> '',
    'subtitle_font_color' => '',
    'desc_font' => '',
    'desc_font_style' => '',
    'desc_font_size' => '',
    'desc_font_color' => '',
    'desc_font_line_height'=> '',
    'el_class'    => '',
    'css_info_box' => '',
    'animation_type' => '',
    ),$atts,'porto_info_box'));

switch ( $icon_type ) {
    case 'simpleline':
        $icon = $icon_simpleline;
        break;
    case 'porto':
        $icon = $icon_porto;
        break;
}

$html = $target = $suffix = $prefix = $title_style = $desc_style = $inf_design_style = '';
$inf_design_style = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css_info_box, ' ' ), "porto_info_box", $atts );
$box_icon = do_shortcode('[porto_icon icon_type="'.esc_attr($icon_type).'" icon="'.esc_attr($icon).'" icon_img="'.esc_attr($icon_img).'" img_width="'.esc_attr($img_width).'" icon_size="'.esc_attr($icon_size).'" icon_color="'.esc_attr($icon_color).'" icon_style="'.esc_attr($icon_style).'" icon_color_bg="'.esc_attr($icon_color_bg).'" icon_color_border="'.esc_attr($icon_color_border).'"  icon_border_style="'.esc_attr($icon_border_style).'" icon_border_size="'.esc_attr($icon_border_size).'" icon_border_radius="'.esc_attr($icon_border_radius).'" icon_border_spacing="'.esc_attr($icon_border_spacing).'" animation_type="'.esc_attr($animation_type).'"]');
$classes = 'porto-sicon-wrapper';
if ( $inf_design_style ) {
    $classes .= ' '. $inf_design_style;
}
if ( $css_class ) {
    $classes .= ' '. $css_class;
}
if ( $el_class ) {
    $classes .= ' '. $el_class;
}
if ( $hover_effect ) {
    $classes .= ' '. $hover_effect;
}
$prefix .= '<div class="'. esc_attr( $classes ) .'">';
$suffix .= '</div>';

if (empty($animation_type) && !empty($icon_animation)) {
    $animation_type = $icon_animation;
}

$ex_class = $ic_class = '';
if ( $pos ) {
    $ex_class .= esc_attr( $pos ).'-icon';
    $ic_class = 'porto-sicon-' . esc_attr( $pos );
}

/* title */
if ( $title_font ) {
    $title_style .= 'font-family:\''.esc_attr($title_font).'\';';
}
if ( $title_font_style ) {
    $title_style .= 'font-weight:'.esc_attr($title_font_style).';';
}
if ( !is_numeric( $title_font_size ) ) {
   $title_font_size = preg_replace( '/[^0-9]/', "", $title_font_size );
}
if ( !is_numeric( $title_font_line_height ) ) {
   $title_font_line_height = preg_replace( '/[^0-9]/', "", $title_font_line_height );
}
if ( $title_font_size ) {
    $title_style .= 'font-size:'.esc_attr($title_font_size).'px;';
}
if ( $title_font_line_height ) {
    $title_style .= 'line-height:'.esc_attr($title_font_line_height).'px;';
}
if ( $title_font_color ) {
    $title_style .= 'color:'.esc_attr($title_font_color).';';
}

/* sub title */
$subtitle_style = '';
if ( $subtitle_font_style ) {
    $subtitle_style .= 'font-weight:'.esc_attr($subtitle_font_style).';';
}
if ( !is_numeric( $subtitle_font_size ) ) {
   $subtitle_font_size = preg_replace( '/[^0-9]/', "", $subtitle_font_size );
}
if ( !is_numeric( $subtitle_font_line_height ) ) {
   $subtitle_font_line_height = preg_replace( '/[^0-9]/', "", $subtitle_font_line_height );
}
if ( $subtitle_font_size ) {
    $subtitle_style .= 'font-size:'.esc_attr($subtitle_font_size).'px;';
}
if ( $subtitle_font_line_height ) {
    $subtitle_style .= 'line-height:'.esc_attr($subtitle_font_line_height).'px;';
}
if ( $subtitle_font_color ) {
    $subtitle_style .= 'color:'.esc_attr($subtitle_font_color).';';
}

/* description */
if ( $desc_font ) {
    $desc_style .= 'font-family:\''.esc_attr($desc_font).'\';';
}
if ( $desc_font_style ) {
    $desc_style .= 'font-weight:'.esc_attr($desc_font_style).';';
}

if ( !is_numeric( $desc_font_size ) ) {
   $desc_font_size = preg_replace( '/[^0-9]/', "", $desc_font_size );
}
if ( !is_numeric( $desc_font_line_height ) ) {
   $desc_font_line_height = preg_replace( '/[^0-9]/', "", $desc_font_line_height );
}
if ( $desc_font_size ) {
    $desc_style .= 'font-size:'.esc_attr($desc_font_size).'px;';
}
if ( $desc_font_line_height ) {
    $desc_style .= 'line-height:'.esc_attr($desc_font_line_height).'px;';
}

if ( $desc_font_color ) {
    $desc_style .= 'color:'.esc_attr($desc_font_color).';';
}

$box_style = $box_style_data = '';
if ($pos == 'square_box' ) {
    if($box_min_height!='') {
        $box_style_data .="data-min-height='".esc_attr($box_min_height)."px'";
    }
    if( $box_border_color ) {
        $box_style .="border-color:".$box_border_color.";";
    }
    if( $box_border_style ) {
        $box_style .="border-style:".$box_border_style.";";
    }
    if ( $box_border_width ) {
        $box_style .="border-width:".$box_border_width."px;";
    }
    if ( $box_bg_color ) {
        $box_style .="background-color:".$box_bg_color.";";
    }
}
$html .= '<div class="porto-sicon-box '.esc_attr($ex_class).'" style="'.esc_attr($box_style).'" '.$box_style_data.' >';

if ( $pos == "heading-right" || $pos == "right" ) {
        if ( $pos == "right" ) {
            $html .= '<div class="porto-sicon-body" >';
        }
        if ( $title ) {
            $html .= '<div class="porto-sicon-header" >';
            $link_prefix = $link_sufix = '';
            if ( $link !== 'none' ) {
                if ( $read_more == 'title' ) {
                    $href           = vc_build_link($link);
                    $url            = ( isset( $href['url'] ) && $href['url'] !== '' ) ? $href['url']  : '';
                    $target         = ( isset( $href['target'] ) && $href['target'] !== '' ) ? "target='" . esc_attr(trim( $href['target'] )) . "'" : '';
                    $link_title     = ( isset( $href['title'] ) && $href['title'] !== '' ) ? "title='".esc_attr($href['title'])."'" : '';
                    $rel            = ( isset( $href['rel'] ) && $href['rel'] !== '' ) ? "rel='".esc_attr($href['rel'])."'" : '';
                    $link_prefix = '<a class="porto-sicon-box-link" href="'. esc_url($url) .'" '. $target .' '. $rel .' '. $link_title .'>';
                    $link_sufix = '</a>';
                }
            }
            $html .= $link_prefix.'<'. $heading_tag .' class="porto-sicon-title" style="'.esc_attr($title_style).'">'. esc_html( $title ).'</'. $heading_tag .'>'.$link_sufix;
            if ( $subtitle ) {
                $html .= '<p'. ( $subtitle_style ? ' style="'. $subtitle_style .'"' : '' ) .'>'. esc_html( $subtitle ) .'</p>';
            }
            $html .= '</div> <!-- header -->';
        }
        if ( $pos !== "right" ) {
            if($icon !== 'none' || $icon_img !== '')
                $html .= '<div class="'.esc_attr($ic_class).'" >'.$box_icon.'</div>';
        }
        if ( $content ) {
            $html .= '<div class="porto-sicon-description" style="'.esc_attr($desc_style).'">';
            $html .= do_shortcode($content);
            if( $link !== 'none' ) {
                if( $read_more == 'more' ) {
                    $href           = vc_build_link($link);
                    $url            = ( isset( $href['url'] ) && $href['url'] !== '' ) ? $href['url']  : '';
                    $target         = ( isset( $href['target'] ) && $href['target'] !== '' ) ? "target='" . esc_attr(trim( $href['target'] )) . "'" : '';
                    $link_title     = ( isset( $href['title'] ) && $href['title'] !== '' ) ? "title='".esc_attr($href['title'])."'" : '';
                    $rel            = ( isset( $href['rel'] ) && $href['rel'] !== '' ) ? "rel='".esc_attr($href['rel'])."'" : '';

                    $more_link = '<a class="porto-sicon-read" href="'. esc_url($url) .'" '. $target .' '. $rel .' '. $link_title .'>';
                    $more_link .= $read_text;
                    $more_link .= '&nbsp;&raquo;';
                    $more_link .= '</a>';
                    $html .= $more_link;
                }
            }
            $html .= '</div>';
        }
        if ( $pos == "right" ) {
            $html .= '</div>';
            if( $icon !== 'none' || $icon_img !== '' )
                $html .= '<div class="'.esc_attr($ic_class).'">'.$box_icon.'</div>';
        }

    } else {
        if ( $icon !== 'none' || $icon_img != '' )
            $html .= '<div class="'.esc_attr($ic_class).'">'.$box_icon.'</div>';
        if ( $pos == "left" )
            $html .= '<div class="porto-sicon-body">';
        if ( $title ) {
            $html .= '<div class="porto-sicon-header" >';
            $link_prefix = $link_sufix = '';
            if ( $link !== 'none' ){
                if ( $read_more == 'title' ) {
                    $href = vc_build_link($link);
                    $url    = ( isset( $href['url'] ) && $href['url'] !== '' ) ? $href['url']  : '';
                    $target = ( isset( $href['target'] ) && $href['target'] !== '' ) ? "target='" . esc_attr(trim( $href['target'] )) . "'" : '';
                    $link_title     = ( isset( $href['title'] ) && $href['title'] !== '' ) ? "title='".esc_attr($href['title'])."'" : '';
                    $rel    = ( isset( $href['rel'] ) && $href['rel'] !== '' ) ? "rel='".esc_attr($href['rel'])."'" : '';

                    $link_prefix = '<a class="porto-sicon-box-link" href="'. esc_url($url) .'" '. $target .' '. $rel .' '. $link_title .'>';
                    $link_sufix = '</a>';
                }
            }
            $html .= $link_prefix.'<'. $heading_tag .' class="porto-sicon-title" style="'.esc_attr($title_style).'">'.$title.'</'. $heading_tag .'>'.$link_sufix;
            if ( $subtitle ) {
                $html .= '<p'. ( $subtitle_style ? ' style="'. $subtitle_style .'"' : '' ) .'>'. esc_html( $subtitle ) .'</p>';
            }
            $html .= '</div> <!-- header -->';
        }
        if ( $content ) {
            $html .= '<div class="porto-sicon-description" style="'.esc_attr($desc_style).'">';
            $html .= do_shortcode( $content );
            if ( $link !== 'none' ) {
                if( $read_more == 'more' ) {
                    $href = vc_build_link($link);
                    $url            = ( isset( $href['url'] ) && $href['url'] !== '' ) ? $href['url']  : '';
                    $target         = ( isset( $href['target'] ) && $href['target'] !== '' ) ? "target='" . esc_attr(trim( $href['target'] )) . "'" : '';
                    $link_title     = ( isset( $href['title'] ) && $href['title'] !== '' ) ? "title='".esc_attr($href['title'])."'" : '';
                    $rel            = ( isset( $href['rel'] ) && $href['rel'] !== '' ) ? "rel='".esc_attr($href['rel'])."'" : '';

                    $more_link = '<a class="porto-sicon-read xx" href="'. esc_url($url) .'" '. $target .' '. $rel .' '. $link_title .'>';
                    $more_link .= $read_text;
                    $more_link .= '&nbsp;&raquo;';
                    $more_link .= '</a>';
                    $html .= $more_link;
                }
            }
            $html .= '</div> <!-- description -->';
        }
        if( $pos == "left" )
            $html .= '</div>';

    }


$html .= '</div> <!-- porto-sicon-box -->';
if ( $link !== 'none' ) {
    if ( $read_more == 'box' ) {
        $href = vc_build_link($link);
        $url            = ( isset( $href['url'] ) && $href['url'] !== '' ) ? $href['url']  : '';
        $target         = ( isset( $href['target'] ) && $href['target'] !== '' ) ? "target='" . esc_attr(trim( $href['target'] )) . "'" : '';
        $link_title     = ( isset( $href['title'] ) && $href['title'] !== '' ) ? "title='".esc_attr($href['title'])."'" : '';
        $rel            = ( isset( $href['rel'] ) && $href['rel'] !== '' ) ? "rel='".esc_attr($href['rel'])."'" : '';

        $output = $prefix.'<a class="porto-sicon-box-link" href="'. esc_attr($url) .'" '. $target .' '. $rel .' '. $link_title .'>'.$html.'</a>'.$suffix;
    } else {
        $output = $prefix.$html.$suffix;
    }
} else {
    $output = $prefix.$html.$suffix;
}

echo $output;