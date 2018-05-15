<?php
$output = $type = $shape = $link = $image_url = $image_id = $title = $sub_title = $view_type = $date = $img_width = $align = $hover_bg = $hover_img = $link_icon = $centered_icons = $icons = $noborders = $boxshadow = $show_socials = $socials = $animation_type = $animation_duration = $animation_delay = $el_class = '';
extract(shortcode_atts(array(
    'type' => '',
    'shape' => 'rounded',
    'link' => '',
    'image_url' => '',
    'image_id' => '',
    'title' => '',
    'sub_title' => '',
    'view_type' => '',
    'date' => '',
    'img_width' => 200,
    'align' => '',
    'hover_bg' => '',
    'hover_img' => '',
    'link_icon' => true,
    'centered_icons' => false,
    'icons' => '',
    'noborders' => false,
    'boxshadow' => false,
    'show_socials' => false,
    'socials' => '',
    'el_class' => '',
    'animation_type' => '',
    'animation_duration' => 1000,
    'animation_delay' => 0,
    'el_class' => '',
), $atts));

$el_class = porto_shortcode_extract_class( $el_class );

if (!$image_url && $image_id)
    $image_url = wp_get_attachment_url($image_id);

$image_url = str_replace(array('http:', 'https:'), '', $image_url);

if ($image_url) {

    $output = '<div class="porto-image-frame ' . $el_class . '"';
    if ($animation_type) {
        $output .= ' data-appear-animation="'.$animation_type.'"';
        if ($animation_delay)
            $output .= ' data-appear-animation-delay="'.$animation_delay.'"';
        if ($animation_duration && $animation_duration != 1000)
            $output .= ' data-appear-animation-duration="'.$animation_duration.'"';
    }
    $output .= '>';

    //parse link
    $link = ( '||' === $link ) ? '' : $link;
    $link = vc_build_link( $link );
    $use_link = false;
    if ( strlen( $link['url'] ) > 0 ) {
        $use_link = true;
        $a_href = $link['url'];
        $a_title = $link['title'];
        $a_target = strlen( $link['target'] ) > 0 ? $link['target'] : '_self';
    }

    $attributes = array();
    if ( $use_link ) {
        $attributes[] = 'href="' . esc_url( trim( $a_href ) ) . '"';
        $attributes[] = 'title="' . esc_attr( trim( $a_title ) ) . '"';
        $attributes[] = 'target="' . esc_attr( trim( $a_target ) ) . '"';
    }

    $attributes = implode( ' ', $attributes );

    if ($type == '') {
        if ( $use_link ) {
            $output .= '<a ' . $attributes . '>';
        }

        if ($shape !== 'thumbnail') {
            $output .= '<img alt="" src="' . $image_url . '" class="img-responsive img-' . $shape . ($boxshadow ? ' img-box-shadow': '') . '">';
        } else {
            $output .= '<span class="img-thumbnail">';
            $output .= '<img alt="" src="' . $image_url . '" class="img-responsive' . ($boxshadow ? ' img-box-shadow': '') . '">';
            $output .= '</span>';
        }

        if ( $use_link ) {
            $output .= '</a>';
        }
    } else if ($type == 'hover-style') {
        $view_class = '';
        switch ($view_type) {
            case 'centered-info': $view_class = 'thumb-info-centered-info'; break;
            case 'bottom-info': $view_class = 'thumb-info-bottom-info'; break;
            case 'bottom-info-dark': $view_class = 'thumb-info-bottom-info thumb-info-bottom-info-dark'; break;
            case 'hide-info-hover': $view_class = 'thumb-info-centered-info thumb-info-hide-info-hover'; break;
            case 'side-image': $view_class = 'thumb-info-side-image thumb-info-no-zoom thumb-info-centered-icons'; break;
            case 'side-image-right': $view_class = 'thumb-info-side-image thumb-info-side-image-right thumb-info-no-zoom thumb-info-centered-icons'; break;
        }
        $output .= '<span class="thumb-info' . ($align ? ' align-' . $align : '') . ($hover_bg ? ' thumb-info-' . $hover_bg : '') . ($hover_img ? ' thumb-info-' . $hover_img : '') . ($centered_icons ? ' thumb-info-centered-icons' : '') . ($view_class ? ' ' . $view_class : '') . ($noborders ? ' thumb-info-no-borders' : '') . ($boxshadow ? ' thumb-info-box-shadow' : '') . '">';
        if ( $use_link && !$centered_icons ) {
            $output .= '<a ' . $attributes . '>';
        }
        $output .= '<span class="' . (($view_type === 'side-image' || $view_type === 'side-image-right') ? 'thumb-info-side-image-wrapper' : 'thumb-info-wrapper') . '">';
        $output .= '<img alt="" src="' . $image_url . '" class="img-responsive"' . (($view_type === 'side-image' || $view_type === 'side-image-right') && $img_width ? ' style="max-width:' . (int)$img_width . 'px"' : '') . '>';

        if (!($view_type === 'side-image' || $view_type === 'side-image-right') && ($title || $date || $sub_title)) {
            if ( $use_link && $centered_icons ) {
                $output .= '<a ' . $attributes . '>';
            }
            $output .= '<span class="thumb-info-title">';
            if ($title || $date) $output .= '<span class="thumb-info-inner">' . $title . '<em>' . $date . '</em></span>';
            if ($sub_title) $output .= '<span class="thumb-info-type">' . $sub_title . '</span>';
            $output .= '</span>';
            if ( $use_link && $centered_icons ) {
                $output .= '</a>';
            }
        }

        if ( $use_link && $link_icon && !$centered_icons ) {
            $output .= '<span class="thumb-info-action"><span class="thumb-info-action-icon"><i class="fa fa-link"></i></span></span>';
        }

        if ($centered_icons) {
            $icons = vc_param_group_parse_atts($icons);
            $icons_html = '';
            foreach ($icons as $icon) {
                $i = '';
                switch ($icon['icon_type']) {
                    case 'fontawesome': $i = $icon['icon']; break;
                    case 'simpleline': $i = $icon['icon_simpleline']; break;
                    case 'image': $i = 'icon-image'; break;
                }
                $c = 'thumb-info-action-icon' . ($icon['skin'] !== 'custom' ? ' thumb-info-action-icon-' . $icon['skin'] : '');
                $a_style = ($icon['skin'] === 'custom' && isset($icon['bg_color']) && $icon['bg_color']) ? ' style="background:' . $icon['bg_color'] . '"' : '';
                $i_style = ($icon['skin'] === 'custom' && isset($icon['icon_color']) && $icon['icon_color']) ? ' style="color:' . $icon['icon_color'] . '"' : '';
                $i_html = '<i class="' . $i . '"' . $i_style . '>';
                if ($i == 'icon-image' && $i_image = $icon['icon_image']) {
                    $i_image = preg_replace('/[^\d]/', '', $i_image);
                    $i_url = wp_get_attachment_url($i_image);
                    $i_url = str_replace(array('http:', 'https:'), '', $i_url);
                    if ($i_url)
                        $i_html .= '<img alt="" src="' . esc_url($i_url) . '">';
                }
                $i_html .= '</i>';
                if ($icon['action'] === 'open_link') {
                    //parse link
                    $open_link = ( !isset($icon['open_link']) || '||' === $icon['open_link'] ) ? '' : $icon['open_link'];
                    $open_link = vc_build_link( $open_link );
                    $use_open_link = false;
                    if ( strlen( $open_link['url'] ) > 0 ) {
                        $use_open_link = true;
                        $a_href = $open_link['url'];
                        $a_title = $open_link['title'];
                        $a_target = strlen( $open_link['target'] ) > 0 ? $open_link['target'] : '_self';
                    }

                    $s_atts = array();

                    if ( $use_open_link ) {
                        $s_atts[] = 'href="' . esc_url( trim( $a_href ) ) . '"';
                        $s_atts[] = 'title="' . esc_attr( trim( $a_title ) ) . '"';
                        $s_atts[] = 'target="' . esc_attr( trim( $a_target ) ) . '"';

                        $s_atts = implode( ' ', $s_atts );
                        $icons_html .= '<a class="' . $c . '" ' . $s_atts . $a_style . '>' . $i_html . '</a>';
                    }
                } else if ($icon['action'] === 'popup_iframe') {
                    if ($icon['popup_iframe'])
                        $icons_html .= '<a class="' . $c . ' porto-popup-iframe" href="' . $icon['popup_iframe'] . '"' . $a_style . '>' . $i_html . '</a>';
                } else {
                    if ($icon['popup_block']) {
                        $id = 'popup' . rand();
                        $icons_html .= '<a class="' . $c . ' porto-popup-content" href="#' . $id . '" data-animation="' . esc_attr($icon['popup_animation']) . '"' . $a_style . '>' . $i_html . '</a>';
                        $icons_html .= '<div id="' . $id . '" class="dialog dialog-' . esc_attr($icon['popup_size']) . ' zoom-anim-dialog mfp-hide">' . do_shortcode( '[porto_block name="' . $icon['popup_block'] . '"]' ) . '</div>';
                    }
                }
            }

            if ($icons_html) {
                $output .= '<span class="thumb-info-action">';
                $output .= $icons_html;
                $output .= '</span>';
            }
        }
        $output .= '</span>';
        if ( $use_link && !$centered_icons ) {
            $output .= '</a>';
        }

        if ($content || $show_socials || (($view_type === 'side-image' || $view_type === 'side-image-right') && ($title || $date || $sub_title))) {
            $socials_html = '';
            if ($show_socials) {
                $socials = vc_param_group_parse_atts($socials);
                foreach ($socials as $social) {
                    $i = '';
                    switch ($social['icon_type']) {
                        case 'fontawesome': $i = $social['icon']; break;
                        case 'simpleline': $i = $social['icon_simpleline']; break;
                        case 'image': $i = 'icon-image'; break;
                    }
                    $c = $social['skin'] !== 'custom' ? 'thumb-info-social-links-' . $social['skin'] : '';
                    $a_style = ($social['skin'] === 'custom' && isset($social['bg_color']) && $social['bg_color']) ? ' style="background:' . $social['bg_color'] . '"' : '';
                    $i_style = ($social['skin'] === 'custom' && isset($social['icon_color']) && $social['icon_color']) ? ' style="color:' . $social['icon_color'] . '"' : '';
                    $i_html = '<i class="' . $i . '"' . $i_style . '>';
                    if ($i == 'icon-image' && $i_image = $social['icon_image']) {
                        $i_image = preg_replace('/[^\d]/', '', $i_image);
                        $i_url = wp_get_attachment_url($i_image);
                        $i_url = str_replace(array('http:', 'https:'), '', $i_url);
                        if ($i_url)
                            $i_html .= '<img alt="" src="' . esc_url($i_url) . '">';
                    }
                    $i_html .= '</i>';
                    if ($social['action'] === 'open_link') {
                        //parse link
                        $open_link = ( !isset($social['open_link']) || '||' === $social['open_link'] ) ? '' : $social['open_link'];
                        $open_link = vc_build_link( $open_link );
                        $use_open_link = false;
                        if ( strlen( $open_link['url'] ) > 0 ) {
                            $use_open_link = true;
                            $a_href = $open_link['url'];
                            $a_title = $open_link['title'];
                            $a_target = strlen( $open_link['target'] ) > 0 ? $open_link['target'] : '_self';
                        }

                        $i_atts = array();

                        if ( $use_open_link ) {
                            $i_atts[] = 'href="' . esc_url( trim( $a_href ) ) . '"';
                            $i_atts[] = 'title="' . esc_attr( trim( $a_title ) ) . '"';
                            $i_atts[] = 'target="' . esc_attr( trim( $a_target ) ) . '"';

                            $i_atts = implode( ' ', $i_atts );
                            $socials_html .= '<a class="' . $c . '" ' . $i_atts . $a_style . '>' . $i_html . '</a>';
                        }
                    } else if ($social['action'] === 'popup_iframe') {
                        if ($social['popup_iframe'])
                            $socials_html .= '<a class="' . $c . ' porto-popup-iframe" href="' . $social['popup_iframe'] . '"' . $a_style . '>' . $i_html . '</a>';
                    } else {
                        if ($social['popup_block']) {
                            $id = 'popup' . rand();
                            $socials_html .= '<a class="' . $c . ' porto-popup-content" href="#' . $id . '" data-animation="' . esc_attr($social['popup_animation']) . '"' . $a_style . '>' . $i_html . '</a>';
                            $socials_html .= '<div id="' . $id . '" class="dialog dialog-' . esc_attr($social['popup_size']) . ' zoom-anim-dialog mfp-hide">' . do_shortcode( '[porto_block name="' . $icon['popup_block'] . '"]' ) . '</div>';
                        }
                    }
                }
            }

            $output .= '<span class="thumb-info-caption">';
            if ($content || ($view_type === 'side-image' || $view_type === 'side-image-right') && ($title || $sub_title)) {
                $output .= '<span class="thumb-info-caption-text">';
                if (($view_type === 'side-image' || $view_type === 'side-image-right') && ($title || $date || $sub_title)) {
                    if ( $use_link && $centered_icons ) {
                        $output .= '<a ' . $attributes . '>';
                    }
                    if ($title) $output .= '<h2 class="font-weight-semibold m-b-xs">' . $title . '</h2>';
                    if ($date) $output .= '<em class="thumb-info-date m-b-xs">' . $date . '</em>';
                    if ($sub_title) $output .= '<h5 class="font-weight-semibold m-b-xs">' . $sub_title . '</h5>';
                    if ( $use_link && $centered_icons ) {
                        $output .= '</a>';
                    }
                }
                $output .= wpb_js_remove_wpautop($content);
                if ($socials_html && (($view_type === 'side-image' || $view_type === 'side-image-right') && ($title || $date || $sub_title))) {
                    $output .= '<span class="thumb-info-social-icons">';
                    $output .= $socials_html;
                    $output .= '</span>';
                }
                $output .= '</span>';
            }
            if ($socials_html && !(($view_type === 'side-image' || $view_type === 'side-image-right') && ($title || $date || $sub_title))) {
                $output .= '<span class="thumb-info-social-icons">';
                $output .= $socials_html;
                $output .= '</span>';
            }
            $output .= '</span>';
        }

        $output .= '</span>';
    }

    $output .= '</div>';
}

echo $output;