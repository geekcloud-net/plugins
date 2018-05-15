<?php

    if ( !function_exists( 'porto_ultimate_heading_spacer' ) ) {
		function porto_ultimate_heading_spacer($wrapper_class, $wrapper_style, $icon_inline) {
			$spacer = '<div class="porto-u-heading-spacer '.$wrapper_class.'" style="'.$wrapper_style.'">'.$icon_inline.'</div>';
			return $spacer;
		}
	}
    $wrapper_style = $main_heading_style_inline = $sub_heading_style_inline = $line_style_inline = $icon_inline = $output = $el_class = $animation_type = '';
    extract(shortcode_atts(array(
        'main_heading' => '',
        "main_heading_font_family"    => "",
        "main_heading_font_size"    => "",
        "main_heading_font_weight"  => "",
        "main_heading_line_height"  => "",
        "main_heading_color"        => "",
        "main_heading_margin_bottom"=> "",
        "sub_heading_font_family"   => "",
        "sub_heading_font_size"     => "",
        "sub_heading_font_weight"   => "",
        "sub_heading_line_height"   => "",
        "sub_heading_color"         => "",
        "sub_heading_margin_bottom" => "",
        "spacer"                    => "no_spacer",
        "spacer_position"           => "top",
        "line_style"                => "solid",
        "line_width"                => "auto",
        "line_height"               => "1",
        "line_color"                => "#ccc",
        "alignment"                 => "center",
        "spacer_margin_bottom"      => "",
        "heading_tag"               => "",
        "animation_type"            => "",
        "el_class" => "",
    ),$atts));
    $wrapper_class = $spacer;

    if ( $heading_tag == '' ) {
        $heading_tag = 'h2';
    }
    if ( $main_heading_font_family ) {
        $main_heading_style_inline .= 'font-family: '. esc_attr( $main_heading_font_family ) . ';';
    }
    if ( $main_heading_font_weight ) {
        $main_heading_style_inline .= 'font-weight: '. esc_attr( $main_heading_font_weight ) . ';';
    }
    if ( $main_heading_color ) {
        $main_heading_style_inline .= 'color:'. esc_attr( $main_heading_color ).';';
    }
    if ( $main_heading_margin_bottom ) {
        $main_heading_style_inline .= 'margin-bottom: '. esc_attr( $main_heading_margin_bottom ) .'px;';
    }

    if ( $sub_heading_font_family ) {
        $sub_heading_style_inline .= 'font-family: '. esc_attr( $sub_heading_font_family ) . ';';
    }
    if ( $sub_heading_font_weight ) {
        $sub_heading_style_inline .= 'font-weight: '. esc_attr( $sub_heading_font_weight ) . ';';
    }
    if ( $sub_heading_color ) {
        $sub_heading_style_inline .= 'color: '. esc_attr( $sub_heading_color ).';';
    }
    if ( $sub_heading_margin_bottom ) {
        $sub_heading_style_inline .= 'margin-bottom: '. esc_attr( $sub_heading_margin_bottom ) .'px;';
    }

    if ( $spacer && $spacer_margin_bottom ) {
        $wrapper_style .= 'margin-bottom: '. esc_attr( $spacer_margin_bottom ) .'px;';
    }
    if ( $spacer == 'line_only' ) {
        $wrap_width = $line_width;
        $line_style_inline = 'border-style:'.$line_style.';';
        $line_style_inline .= 'border-bottom-width:'.$line_height.'px;';
        $line_style_inline .= 'border-color:'.$line_color.';';
        $line_style_inline .= 'width:'.$wrap_width.'px;';
        $wrapper_style .= 'height:'.$line_height.'px;';
        $line = '<span class="porto-u-headings-line" style="'.esc_attr( $line_style_inline ).'"></span>';
        $icon_inline = $line;
    } else if ( $spacer == 'image_only' ) {
        if ( !empty( $spacer_img_width ) ) {
            $siwidth = array($spacer_img_width, $spacer_img_width);
        } else {
            $siwidth = 'full';
        }
        $spacer_inline = '';
        if ( $spacer_img ) {
            $attachment = wp_get_attachment_image_src( $spacer_img, $siwidth );
            if ( isset( $attachment ) ) {
                $icon_inline = $attachment[0];
            }
        }
        $alt = '';
        if($spacer_img_width !== '')
            $spacer_inline = 'width:'.$spacer_img_width.'px';
        $icon_inline = '<img src="'.esc_url( $icon_inline ).'" class="ultimate-headings-icon-image" alt="'.esc_attr($alt).'" style="'.esc_attr($spacer_inline).'"/>';
    }

    if ( !is_numeric( $main_heading_font_size ) ) {
        $main_heading_font_size = preg_replace( '/[^0-9]/', "", $main_heading_font_size );
    }
    if ( !is_numeric( $main_heading_line_height ) ) {
        $main_heading_line_height = preg_replace( '/[^0-9]/', "", $main_heading_line_height );
    }
    if ( $main_heading_font_size ) {
        $main_heading_style_inline .= 'font-size: '. esc_attr( $main_heading_font_size ) .'px;';
    }
    if ( $main_heading_line_height ) {
        $main_heading_style_inline .= 'line-height: '. esc_attr( $main_heading_line_height ) .'px;';
    }

    if ( !is_numeric( $sub_heading_font_size ) ) {
        $sub_heading_font_size = preg_replace( '/[^0-9]/', "", $sub_heading_font_size );
    }
    if ( !is_numeric( $sub_heading_line_height ) ) {
        $sub_heading_line_height = preg_replace( '/[^0-9]/', "", $sub_heading_line_height );
    }
    if ( $sub_heading_font_size ) {
        $sub_heading_style_inline .= 'font-size: '. esc_attr( $sub_heading_font_size ) .'px;';
    }
    if ( $sub_heading_line_height ) {
        $sub_heading_style_inline .= 'line-height: '. esc_attr( $sub_heading_line_height ) .'px;';
    }


    $output = '<div class="porto-u-heading '.esc_attr( $el_class ).'" data-hspacer="'. esc_attr( $spacer ) .'" data-halign="'. esc_attr( $alignment ) .'" style="text-align:'. esc_attr( $alignment ) .'">';
        if ( $spacer_position == 'top' ) {
            $output .= porto_ultimate_heading_spacer( $wrapper_class, $wrapper_style, $icon_inline );
        }
        if ( $main_heading ) {
            $output .= '<div class="porto-u-main-heading"><'.$heading_tag.' style="'. esc_attr( $main_heading_style_inline ) .'">'. $main_heading .'</'. $heading_tag .'></div>';
        }
        if ( $spacer_position == 'middle' ) {
            $output .= porto_ultimate_heading_spacer( $wrapper_class, $wrapper_style, $icon_inline );
        }
        if ( $content ) {
            $output .= '<div class="porto-u-sub-heading" style="'. esc_attr( $sub_heading_style_inline ) .'">'. do_shortcode( $content ) .'</div>';
        }
        if ( $spacer_position == 'bottom' ) {
            $output .= porto_ultimate_heading_spacer( $wrapper_class, $wrapper_style, $icon_inline );
        }
    $output .= '</div>';

    echo $output;