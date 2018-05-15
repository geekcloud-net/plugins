<?php

function porto_shortcode_template( $name = false ) {
    if (!$name)
        return false;

    if ( $overridden_template = locate_template( 'vc_templates/' . $name . '.php' ) ) {
        return $overridden_template;
    } else {
        // If neither the child nor parent theme have overridden the template,
        // we load the template from the 'templates' sub-directory of the directory this file is in
        return PORTO_SHORTCODES_TEMPLATES . $name . '.php';
    }
}

function porto_shortcode_woo_template( $name = false ) {
    if (!$name)
        return false;

    if ( $overridden_template = locate_template( 'vc_templates/' . $name . '.php' ) ) {
        return $overridden_template;
    } else {
        // If neither the child nor parent theme have overridden the template,
        // we load the template from the 'templates' sub-directory of the directory this file is in
        return PORTO_SHORTCODES_WOO_TEMPLATES . $name . '.php';
    }
}

function porto_shortcode_extract_class( $el_class ) {
    $output = '';
    if ( $el_class != '' ) {
        $output = " " . str_replace( ".", "", $el_class );
    }

    return $output;
}

function porto_shortcode_end_block_comment( $string ) {
    return WP_DEBUG ? '<!-- END ' . $string . ' -->' : '';
}

function porto_shortcode_js_remove_wpautop( $content, $autop = false ) {

    if ( $autop ) {
        $content = wpautop( preg_replace( '/<\/?p\>/', "\n", $content ) . "\n" );
    }

    return do_shortcode( shortcode_unautop( $content ) );
}

function porto_shortcode_image_resize( $attach_id = null, $img_url = null, $width, $height, $crop = false ) {
    // this is an attachment, so we have the ID
    $image_src = array();
    if ( $attach_id ) {
        $image_src = wp_get_attachment_image_src( $attach_id, 'full' );
        $actual_file_path = get_attached_file( $attach_id );
        // this is not an attachment, let's use the image url
    } else if ( $img_url ) {
        $file_path = parse_url( $img_url );
        $actual_file_path = $_SERVER['DOCUMENT_ROOT'] . $file_path['path'];
        $actual_file_path = ltrim( $file_path['path'], '/' );
        $actual_file_path = rtrim( ABSPATH, '/' ) . $file_path['path'];
        $orig_size = getimagesize( $actual_file_path );
        $image_src[0] = $img_url;
        $image_src[1] = $orig_size[0];
        $image_src[2] = $orig_size[1];
    }
    if (!empty($actual_file_path)) {
        $file_info = pathinfo( $actual_file_path );
        $extension = '.' . $file_info['extension'];

        // the image path without the extension
        $no_ext_path = $file_info['dirname'] . '/' . $file_info['filename'];

        $cropped_img_path = $no_ext_path . '-' . $width . 'x' . $height . $extension;

        // checking if the file size is larger than the target size
        // if it is smaller or the same size, stop right here and return
        if ( $image_src[1] > $width || $image_src[2] > $height ) {

            // the file is larger, check if the resized version already exists (for $crop = true but will also work for $crop = false if the sizes match)
            if ( file_exists( $cropped_img_path ) ) {
                $cropped_img_url = str_replace( basename( $image_src[0] ), basename( $cropped_img_path ), $image_src[0] );
                $vt_image = array(
                    'url' => $cropped_img_url,
                    'width' => $width,
                    'height' => $height
                );

                return $vt_image;
            }

            // $crop = false
            if ( $crop == false ) {
                // calculate the size proportionaly
                $proportional_size = wp_constrain_dimensions( $image_src[1], $image_src[2], $width, $height );
                $resized_img_path = $no_ext_path . '-' . $proportional_size[0] . 'x' . $proportional_size[1] . $extension;

                // checking if the file already exists
                if ( file_exists( $resized_img_path ) ) {
                    $resized_img_url = str_replace( basename( $image_src[0] ), basename( $resized_img_path ), $image_src[0] );

                    $vt_image = array(
                        'url' => $resized_img_url,
                        'width' => $proportional_size[0],
                        'height' => $proportional_size[1]
                    );

                    return $vt_image;
                }
            }

            // no cache files - let's finally resize it
            $img_editor = wp_get_image_editor( $actual_file_path );

            if ( is_wp_error( $img_editor ) || is_wp_error( $img_editor->resize( $width, $height, $crop ) ) ) {
                return array(
                    'url' => '',
                    'width' => '',
                    'height' => ''
                );
            }

            $new_img_path = $img_editor->generate_filename();

            if ( is_wp_error( $img_editor->save( $new_img_path ) ) ) {
                return array(
                    'url' => '',
                    'width' => '',
                    'height' => ''
                );
            }
            if ( ! is_string( $new_img_path ) ) {
                return array(
                    'url' => '',
                    'width' => '',
                    'height' => ''
                );
            }

            $new_img_size = getimagesize( $new_img_path );
            $new_img = str_replace( basename( $image_src[0] ), basename( $new_img_path ), $image_src[0] );

            // resized output
            $vt_image = array(
                'url' => $new_img,
                'width' => $new_img_size[0],
                'height' => $new_img_size[1]
            );

            return $vt_image;
        }

        // default output - without resizing
        $vt_image = array(
            'url' => $image_src[0],
            'width' => $image_src[1],
            'height' => $image_src[2]
        );

        return $vt_image;
    }
    return false;
}

function porto_shortcode_get_image_by_size(
    $params = array(
        'post_id' => null,
        'attach_id' => null,
        'thumb_size' => 'thumbnail',
        'class' => ''
    )
) {
    //array( 'post_id' => $post_id, 'thumb_size' => $grid_thumb_size )
    if ( ( ! isset( $params['attach_id'] ) || $params['attach_id'] == null ) && ( ! isset( $params['post_id'] ) || $params['post_id'] == null ) ) {
        return false;
    }
    $post_id = isset( $params['post_id'] ) ? $params['post_id'] : 0;

    if ( $post_id ) {
        $attach_id = get_post_thumbnail_id( $post_id );
    } else {
        $attach_id = $params['attach_id'];
    }

    $thumb_size = $params['thumb_size'];
    $thumb_class = ( isset( $params['class'] ) && $params['class'] != '' ) ? $params['class'] . ' ' : '';

    global $_wp_additional_image_sizes;
    $thumbnail = '';

    if ( is_string( $thumb_size ) && ( ( ! empty( $_wp_additional_image_sizes[ $thumb_size ] ) && is_array( $_wp_additional_image_sizes[ $thumb_size ] ) ) || in_array( $thumb_size, array(
                'thumbnail',
                'thumb',
                'medium',
                'large',
                'full'
            ) ) )
    ) {
        $thumbnail = wp_get_attachment_image( $attach_id, $thumb_size, false, array( 'class' => $thumb_class . 'attachment-' . $thumb_size ) );
    } elseif ( $attach_id ) {
        if ( is_string( $thumb_size ) ) {
            preg_match_all( '/\d+/', $thumb_size, $thumb_matches );
            if ( isset( $thumb_matches[0] ) ) {
                $thumb_size = array();
                if ( count( $thumb_matches[0] ) > 1 ) {
                    $thumb_size[] = $thumb_matches[0][0]; // width
                    $thumb_size[] = $thumb_matches[0][1]; // height
                } elseif ( count( $thumb_matches[0] ) > 0 && count( $thumb_matches[0] ) < 2 ) {
                    $thumb_size[] = $thumb_matches[0][0]; // width
                    $thumb_size[] = $thumb_matches[0][0]; // height
                } else {
                    $thumb_size = false;
                }
            }
        }
        if ( is_array( $thumb_size ) ) {
            // Resize image to custom size
            $p_img = porto_shortcode_image_resize( $attach_id, null, $thumb_size[0], $thumb_size[1], true );
            $alt = trim( strip_tags( get_post_meta( $attach_id, '_wp_attachment_image_alt', true ) ) );
            $attachment = get_post( $attach_id );
            if (!empty($attachment)) {
                $title = trim( strip_tags( $attachment->post_title ) );

                if ( empty( $alt ) ) {
                    $alt = trim( strip_tags( $attachment->post_excerpt ) ); // If not, Use the Caption
                }
                if ( empty( $alt ) ) {
                    $alt = $title;
                } // Finally, use the title
                if ( $p_img ) {
                    $img_class = '';
                    //if ( $grid_layout == 'thumbnail' ) $img_class = ' no_bottom_margin'; class="'.$img_class.'"
                    $thumbnail = '<img class="' . esc_attr( $thumb_class ) . '" src="' . esc_attr( $p_img['url'] ) . '" width="' . esc_attr( $p_img['width'] ) . '" height="' . esc_attr( $p_img['height'] ) . '" alt="' . esc_attr( $alt ) . '" title="' . esc_attr( $title ) . '" />';
                }
            }
        }
    }

    $p_img_large = wp_get_attachment_image_src( $attach_id, 'large' );

    return apply_filters( 'vc_wpb_getimagesize', array(
        'thumbnail' => $thumbnail,
        'p_img_large' => $p_img_large
    ), $attach_id, $params );
}

function porto_vc_animation_type() {
    return array(
        "type" => "porto_animation_type",
        "heading" => __("Animation Type", 'porto-shortcodes'),
        "param_name" => "animation_type",
        "group" => __('Animation', 'porto-shortcodes')
    );
}

function porto_vc_animation_duration() {
    return array(
        "type" => "textfield",
        "heading" => __("Animation Duration", 'porto-shortcodes'),
        "param_name" => "animation_duration",
        "description" => __("numerical value (unit: milliseconds)", 'porto-shortcodes'),
        "value" => '1000',
        "group" => __('Animation', 'porto-shortcodes')
    );
}

function porto_vc_animation_delay() {
    return array(
        "type" => "textfield",
        "heading" => __("Animation Delay", 'porto-shortcodes'),
        "param_name" => "animation_delay",
        "description" => __("numerical value (unit: milliseconds)", 'porto-shortcodes'),
        "value" => '0',
        "group" => __('Animation', 'porto-shortcodes')
    );
}

function porto_vc_custom_class() {
    return array(
        'type' => 'textfield',
        'heading' => __( 'Extra class name', 'porto-shortcodes' ),
        'param_name' => 'el_class',
        'description' => __( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'porto-shortcodes' )
    );
}

if (!function_exists('porto_sh_commons')) {
    function porto_sh_commons($asset = '') {
        switch ($asset) {
            case 'toggle_type':       return Porto_ShSharedLibrary::getToggleType();
            case 'toggle_size':       return Porto_ShSharedLibrary::getToggleSize();
            case 'align':             return Porto_ShSharedLibrary::getTextAlign();
            case 'blog_layout':            return Porto_ShSharedLibrary::getBlogLayout();
            case 'blog_grid_columns':      return Porto_ShSharedLibrary::getBlogGridColumns();
            case 'portfolio_layout':       return Porto_ShSharedLibrary::getPortfolioLayout();
            case 'portfolio_grid_columns': return Porto_ShSharedLibrary::getPortfolioGridColumns();
            case 'portfolio_grid_view':    return Porto_ShSharedLibrary::getPortfolioGridView();
            case 'member_columns': return Porto_ShSharedLibrary::getMemberColumns();
            case 'member_view': return Porto_ShSharedLibrary::getMemberView();						case 'custom_zoom': return Porto_ShSharedLibrary::getCustomZoom();
            case 'products_view_mode':     return Porto_ShSharedLibrary::getProductsViewMode();
            case 'products_columns':       return Porto_ShSharedLibrary::getProductsColumns();
            case 'products_column_width':  return Porto_ShSharedLibrary::getProductsColumnWidth();
            case 'products_addlinks_pos':  return Porto_ShSharedLibrary::getProductsAddlinksPos();
            case 'product_view_mode':      return Porto_ShSharedLibrary::getProductViewMode();
            case 'content_boxes_bg_type':  return Porto_ShSharedLibrary::getContentBoxesBgType();
            case 'content_boxes_style':    return Porto_ShSharedLibrary::getContentBoxesStyle();
            case 'content_box_effect':     return Porto_ShSharedLibrary::getContentBoxEffect();
            case 'colors':                 return Porto_ShSharedLibrary::getColors();
            case 'testimonial_styles':     return Porto_ShSharedLibrary::getTestimonialStyles();
            case 'contextual':             return Porto_ShSharedLibrary::getContextual();
            case 'position':               return Porto_ShSharedLibrary::getPosition();
            case 'size':                   return Porto_ShSharedLibrary::getSize();
            case 'trigger':                return Porto_ShSharedLibrary::getTrigger();
            case 'bootstrap_columns':      return Porto_ShSharedLibrary::getBootstrapColumns();
            case 'price_boxes_style':      return Porto_ShSharedLibrary::getPriceBoxesStyle();
            case 'price_boxes_size':       return Porto_ShSharedLibrary::getPriceBoxesSize();
            case 'sort_style':             return Porto_ShSharedLibrary::getSortStyle();
            case 'sort_by':                return Porto_ShSharedLibrary::getSortBy();
            case 'grid_columns':           return Porto_ShSharedLibrary::getGridColumns();
            case 'preview_time':           return Porto_ShSharedLibrary::getPreviewTime();
            case 'preview_position':       return Porto_ShSharedLibrary::getPreviewPosition();
            case 'popup_action':           return Porto_ShSharedLibrary::getPopupAction();
            case 'feature_box_style':      return Porto_ShSharedLibrary::getFeatureBoxStyle();
            case 'feature_box_dir':        return Porto_ShSharedLibrary::getFeatureBoxDir();
            case 'section_skin':           return Porto_ShSharedLibrary::getSectionSkin();
            case 'section_color_scale':    return Porto_ShSharedLibrary::getSectionColorScale();
            case 'section_text_color':     return Porto_ShSharedLibrary::getSectionTextColor();
            case 'separator_icon_style':   return Porto_ShSharedLibrary::getSeparatorIconStyle();
            case 'separator_icon_size':    return Porto_ShSharedLibrary::getSeparatorIconSize();
            case 'separator_icon_pos':     return Porto_ShSharedLibrary::getSeparatorIconPosition();
            default: return array();
        }
    }
}

function porto_vc_woo_order_by() {
    return array(
        '',
        __( 'Date', 'js_composer' ) => 'date',
        __( 'ID', 'js_composer' ) => 'ID',
        __( 'Author', 'js_composer' ) => 'author',
        __( 'Title', 'js_composer' ) => 'title',
        __( 'Modified', 'js_composer' ) => 'modified',
        __( 'Random', 'js_composer' ) => 'rand',
        __( 'Comment count', 'js_composer' ) => 'comment_count',
        __( 'Menu order', 'js_composer' ) => 'menu_order',
    );
}

function porto_vc_woo_order_way() {
    return array(
        '',
        __( 'Descending', 'js_composer' ) => 'DESC',
        __( 'Ascending', 'js_composer' ) => 'ASC',
    );
}

if (!class_exists('Porto_ShSharedLibrary')) {
    class Porto_ShSharedLibrary {

        public static function getTextAlign() {
            return array(
                __('None', 'porto-shortcodes') => '',
                __('Left', 'porto-shortcodes' ) => 'left',
                __('Right', 'porto-shortcodes' ) => 'right',
                __('Center', 'porto-shortcodes' ) => 'center',
                __('Justify', 'porto-shortcodes' ) => 'justify'
            );
        }

        public static function getToggleType() {
            return array(
                __('Default', 'porto-shortcodes' ) => '',
                __('Simple', 'porto-shortcodes' ) => 'toggle-simple'
            );
        }

        public static function getToggleSize() {
            return array(
                __('Default', 'porto-shortcodes' ) => '',
                __('Small', 'porto-shortcodes' ) => 'toggle-sm',
                __('Large', 'porto-shortcodes' ) => 'toggle-lg',
            );
        }

        public static function getBlogLayout() {
            return array(
                __('Full', 'porto-shortcodes' ) => 'full',
                __('Large', 'porto-shortcodes' ) => 'large',
                __('Large Alt', 'porto-shortcodes' ) => 'large-alt',
                __('Medium', 'porto-shortcodes' ) => 'medium',
                __('Medium Alt', 'porto-shortcodes' ) => 'medium-alt',
                __('Grid', 'porto-shortcodes' ) => 'grid',
                __('Timeline', 'porto-shortcodes' ) => 'timeline'
            );
        }

        public static function getBlogGridColumns() {
            return array(
                __('1', 'porto-shortcodes' ) => '1',
                __('2', 'porto-shortcodes' ) => '2',
                __('3', 'porto-shortcodes' ) => '3',
                __('4', 'porto-shortcodes' ) => '4'
            );
        }

        public static function getPortfolioLayout() {
            return array(
                __('Grid', 'porto-shortcodes' ) => 'grid',
                __('Masonry', 'porto-shortcodes' ) => 'masonry',
                __('Timeline', 'porto-shortcodes' ) => 'timeline',
                __('Medium', 'porto-shortcodes' ) => 'medium',
                __('Large', 'porto-shortcodes' ) => 'large',
                __('Full', 'porto-shortcodes' ) => 'full'
            );
        }

        public static function getPortfolioGridColumns() {
            return array(
                __('1', 'porto-shortcodes' ) => '1',
                __('2', 'porto-shortcodes' ) => '2',
                __('3', 'porto-shortcodes' ) => '3',
                __('4', 'porto-shortcodes' ) => '4',
                __('5', 'porto-shortcodes' ) => '5',
                __('6', 'porto-shortcodes' ) => '6'
            );
        }

        public static function getPortfolioGridView() {
            return array(
                __('Standard', 'porto-shortcodes' ) => 'classic',
                __('Default', 'porto-shortcodes' ) => 'default',
                __('No Margin', 'porto-shortcodes' ) => 'full',
                __('Out of Image', 'porto-shortcodes' ) => 'outimage',
            );
        }

        public static function getMemberView() {
            return array(
                __('Standard', 'porto-shortcodes' ) => 'classic',
                __('Type 1', 'porto-shortcodes' ) => 'onimage',
                __('Type 2', 'porto-shortcodes' ) => 'outimage',
                __('Type 3', 'porto-shortcodes' ) => 'outimage_cat'
            );
        }						public static function getCustomZoom() {            return array(                __('Zoom', 'porto-shortcodes' ) => 'zoom',                __('No_Zoom', 'porto-shortcodes' ) => 'no_zoom',            );        }

        public static function getMemberColumns() {
            return array(
                __('2', 'porto-shortcodes' ) => '2',
                __('3', 'porto-shortcodes' ) => '3',
                __('4', 'porto-shortcodes' ) => '4',
                __('5', 'porto-shortcodes' ) => '5',
                __('6', 'porto-shortcodes' ) => '6'
            );
        }

        public static function getProductsViewMode() {
            return array(
                __( 'Grid', 'porto-shortcodes' )=> 'grid',
                __( 'List', 'porto-shortcodes' ) => 'list',
                __( 'Slider', 'porto-shortcodes' )  => 'products-slider',
            );
        }

        public static function getProductsColumns() {
            return array(
                '1' => 1,
                '2' => 2,
                '3' => 3,
                '4' => 4,
                '5' => 5,
                '6' => 6,
                '7 ' . __( '(without sidebar)', 'porto-shortcodes' ) => 7,
                '8 ' . __( '(without sidebar)', 'porto-shortcodes' ) => 8
            );
        }

        public static function getProductsColumnWidth() {
            return array(
                __( 'Default', 'porto-shortcodes' ) => '',
                '1/1' . __( ' of content width', 'porto-shortcodes' ) => 1,
                '1/2' . __( ' of content width', 'porto-shortcodes' ) => 2,
                '1/3' . __( ' of content width', 'porto-shortcodes' ) => 3,
                '1/4' . __( ' of content width', 'porto-shortcodes' ) => 4,
                '1/5' . __( ' of content width', 'porto-shortcodes' ) => 5,
                '1/6' . __( ' of content width', 'porto-shortcodes' ) => 6,
                '1/7' . __( ' of content width (without sidebar)', 'porto-shortcodes' ) => 7,
                '1/8' . __( ' of content width (without sidebar)', 'porto-shortcodes' ) => 8
            );
        }

        public static function getProductsAddlinksPos() {
            return array(
                __( 'Default', 'porto-shortcodes' ) => '',
                __( 'Out of Image', 'porto-shortcodes' ) => 'outimage',
                __( 'On Image', 'porto-shortcodes' ) => 'onimage',
                __( 'Wishlist, Quick View On Image', 'porto-shortcodes' ) => 'wq_onimage',
				__( 'Out of Image, Quick View On Image', 'porto-shortcodes' ) => 'outimage_q_onimage',
				__( 'Out of Image, Quick View On Image Alt', 'porto-shortcodes' ) => 'outimage_q_onimage_alt',
            );
        }

        public static function getProductViewMode() {
            return array(
                __( 'Grid', 'porto-shortcodes' )=> 'grid',
                __( 'List', 'porto-shortcodes' ) => 'list',
            );
        }

        public static function getColors() {
            return array(
                '' => 'custom',
                __( 'Primary', 'porto-shortcodes' ) => 'primary',
                __( 'Secondary', 'porto-shortcodes' ) => 'secondary',
                __( 'Tertiary', 'porto-shortcodes' ) => 'tertiary',
                __( 'Quaternary', 'porto-shortcodes' ) => 'quaternary',
                __( 'Dark', 'porto-shortcodes' ) => 'dark',
                __( 'Light', 'porto-shortcodes' ) => 'light',
            );
        }

        public static function getContentBoxesBgType() {
            return array(
                __( 'Default', 'porto-shortcodes' )=> '',
                __( 'Flat', 'porto-shortcodes' ) => 'featured-boxes-flat',
                __( 'Custom', 'porto-shortcodes' ) => 'featured-boxes-custom',
            );
        }

        public static function getContentBoxesStyle() {
            return array(
                __('Default', 'porto-shortcodes' ) => '',
                __('Style 1', 'porto-shortcodes' ) => 'featured-boxes-style-1',
                __('Style 2', 'porto-shortcodes' ) => 'featured-boxes-style-2',
                __('Style 3', 'porto-shortcodes' ) => 'featured-boxes-style-3',
                __('Style 4', 'porto-shortcodes' ) => 'featured-boxes-style-4',
                __('Style 5', 'porto-shortcodes' ) => 'featured-boxes-style-5',
                __('Style 6', 'porto-shortcodes' ) => 'featured-boxes-style-6',
                __('Style 7', 'porto-shortcodes' ) => 'featured-boxes-style-7',
                __('Style 8', 'porto-shortcodes' ) => 'featured-boxes-style-8',
            );
        }

        public static function getContentBoxEffect() {
            return array(
                __('Default', 'porto-shortcodes' ) => '',
                __('Effect 1', 'porto-shortcodes' ) => 'featured-box-effect-1',
                __('Effect 2', 'porto-shortcodes' ) => 'featured-box-effect-2',
                __('Effect 3', 'porto-shortcodes' ) => 'featured-box-effect-3',
                __('Effect 4', 'porto-shortcodes' ) => 'featured-box-effect-4',
                __('Effect 5', 'porto-shortcodes' ) => 'featured-box-effect-5',
                __('Effect 6', 'porto-shortcodes' ) => 'featured-box-effect-6',
                __('Effect 7', 'porto-shortcodes' ) => 'featured-box-effect-7',
            );
        }

        public static function getTestimonialStyles() {
            return array(
                __('Style 1', 'porto-shortcodes' ) => '',
                __('Style 2', 'porto-shortcodes' ) => 'testimonial-style-2',
                __('Style 3', 'porto-shortcodes' ) => 'testimonial-style-3',
                __('Style 4', 'porto-shortcodes' ) => 'testimonial-style-4',
                __('Style 5', 'porto-shortcodes' ) => 'testimonial-style-5',
                __('Style 6', 'porto-shortcodes' ) => 'testimonial-style-6',
            );
        }

        public static function getContextual() {
            return array(
                __('None', 'porto-shortcodes' )    => '',
                __('Success', 'porto-shortcodes' ) => 'success',
                __('Info', 'porto-shortcodes' )    => 'info',
                __('Warning', 'porto-shortcodes' ) => 'warning',
                __('Danger', 'porto-shortcodes' )  => 'danger',
            );
        }

        public static function getPosition() {
            return array(
                __('Top', 'porto-shortcodes')     => 'top',
                __('Right', 'porto-shortcodes')   => 'right',
                __('Bottom', 'porto-shortcodes')  => 'bottom',
                __('Left', 'porto-shortcodes')    => 'left',
            );
        }

        public static function getSize() {
            return array(
                __('Normal', 'porto-shortcodes')      => '',
                __('Large', 'porto-shortcodes')       => 'lg',
                __('Small', 'porto-shortcodes')       => 'sm',
                __('Extra Small', 'porto-shortcodes') => 'xs',
            );
        }

        public static function getTrigger() {
            return array(
                __('Click', 'porto-shortcodes')      => 'click',
                __('Hover', 'porto-shortcodes')      => 'hover',
                __('Focus', 'porto-shortcodes')      => 'focus',
            );
        }

        public static function getBootstrapColumns() {
            return array(6, 4, 3, 2, 1);
        }

        public static function getPriceBoxesStyle() {
            return array(
                __('Default', 'porto-shortcodes')      => '',
                __('Alternative', 'porto-shortcodes')  => 'flat',
            );
        }

        public static function getPriceBoxesSize() {
            return array(
                __('Normal', 'porto-shortcodes')      => '',
                __('Small', 'porto-shortcodes')       => 'sm',
            );
        }

        public static function getSortStyle() {
            return array(
                __('Default', 'porto-shortcodes')      => '',
                __('Style 2', 'porto-shortcodes')      => 'style-2',
            );
        }

        public static function getSortBy() {
            return array(
                __('Original Order', 'porto-shortcodes')     => 'original-order',
                __('Popular Value', 'porto-shortcodes')      => 'popular',
            );
        }

        public static function getGridColumns() {
            return array(
                __('12 columns - 1/1', 'porto-shortcodes')   => '12',
                __('11 columns - 11/12', 'porto-shortcodes') => '11',
                __('10 columns - 5/6', 'porto-shortcodes')   => '10',
                __('9 columns - 3/4', 'porto-shortcodes')    => '9',
                __('8 columns - 2/3', 'porto-shortcodes')    => '8',
                __('7 columns - 7/12', 'porto-shortcodes')   => '7',
                __('6 columns - 1/2', 'porto-shortcodes')    => '6',
                __('5 columns - 5/12', 'porto-shortcodes')   => '5',
                __('4 columns - 1/3', 'porto-shortcodes')    => '4',
                __('3 columns - 1/4', 'porto-shortcodes')    => '3',
                __('2 columns - 1/6', 'porto-shortcodes')    => '2',
                __('1 columns - 1/12', 'porto-shortcodes')   => '1',
            );
        }

        public static function getPreviewTime() {
            return array(
                __('Normal', 'porto-shortcodes')   => '',
                __('Short', 'porto-shortcodes')    => 'short',
                __('Long', 'porto-shortcodes')     => 'long',
            );
        }

        public static function getPreviewPosition() {
            return array(
                __('Center', 'porto-shortcodes')   => '',
                __('Top', 'porto-shortcodes')    => 'top',
                __('Bottom', 'porto-shortcodes')     => 'bottom',
            );
        }

        public static function getPopupAction() {
            return array(
                __( 'Open URL (Link)', 'porto-shortcodes' )=> 'open_link',
                __( 'Popup Video or Map', 'porto-shortcodes' )=> 'popup_iframe',
                __( 'Popup Block', 'porto-shortcodes' )=> 'popup_block'
            );
        }

        public static function getFeatureBoxStyle() {
            return array(
                __('Style 1', 'porto-shortcodes' ) => '',
                __('Style 2', 'porto-shortcodes' ) => 'feature-box-style-2',
                __('Style 3', 'porto-shortcodes' ) => 'feature-box-style-3',
                __('Style 4', 'porto-shortcodes' ) => 'feature-box-style-4',
                __('Style 5', 'porto-shortcodes' ) => 'feature-box-style-5',
                __('Style 6', 'porto-shortcodes' ) => 'feature-box-style-6',
            );
        }

        public static function getFeatureBoxDir() {
            return array(
                __('Default', 'porto-shortcodes' ) => '',
                __('Reverse', 'porto-shortcodes' ) => 'reverse',
            );
        }

        public static function getSectionSkin() {
            return array(
                __('Default', 'porto-shortcodes')    => 'default',
                __('Transparent', 'porto-shortcodes')    => 'parallax',
                __('Primary', 'porto-shortcodes')    => 'primary',
                __('Secondary', 'porto-shortcodes')  => 'secondary',
                __('Tertiary', 'porto-shortcodes')   => 'tertiary',
                __('Quaternary', 'porto-shortcodes') => 'quaternary',
                __('Dark', 'porto-shortcodes')       => 'dark',
                __('Light', 'porto-shortcodes')      => 'light',
            );
        }

        public static function getSectionColorScale() {
            return array(
                __('Default', 'porto-shortcodes') => '',
                __('Scale 1', 'porto-shortcodes') => 'scale-1',
                __('Scale 2', 'porto-shortcodes') => 'scale-2',
                __('Scale 3', 'porto-shortcodes') => 'scale-3',
                __('Scale 4', 'porto-shortcodes') => 'scale-4',
                __('Scale 5', 'porto-shortcodes') => 'scale-5',
                __('Scale 6', 'porto-shortcodes') => 'scale-6',
                __('Scale 7', 'porto-shortcodes') => 'scale-7',
                __('Scale 8', 'porto-shortcodes') => 'scale-8',
                __('Scale 9', 'porto-shortcodes') => 'scale-9',
            );
        }

        public static function getSectionTextColor() {
            return array(
                __('Default', 'porto-shortcodes') => '',
                __('Dark', 'porto-shortcodes')    => 'dark',
                __('Light', 'porto-shortcodes')   => 'light',
            );
        }

        public static function getSeparatorIconStyle() {
            return array(
                __('Style 1', 'porto-shortcodes' ) => '',
                __('Style 2', 'porto-shortcodes' ) => 'style-2',
                __('Style 3', 'porto-shortcodes' ) => 'style-3',
                __('Style 4', 'porto-shortcodes' ) => 'style-4',
            );
        }

        public static function getSeparatorIconSize() {
            return array(
                __('Normal', 'porto-shortcodes' ) => '',
                __('Small', 'porto-shortcodes' )  => 'sm',
                __('Large', 'porto-shortcodes' )  => 'lg'
            );
        }

        public static function getSeparatorIconPosition() {
            return array(
                __('Center', 'porto-shortcodes' ) => '',
                __('Left', 'porto-shortcodes' )  => 'left',
                __('Right', 'porto-shortcodes' )  => 'right'
            );
        }
    }
}

function porto_shortcode_widget_title( $params = array( 'title' => '' ) ) {
    if ( $params['title'] == '' ) {
        return '';
    }

    $extraclass = ( isset( $params['extraclass'] ) ) ? " " . $params['extraclass'] : "";
    $output = '<h4 class="wpb_heading' . $extraclass . '">' . $params['title'] . '</h4>';

    return apply_filters( 'wpb_widget_title', $output, $params );
}

if (function_exists('vc_add_shortcode_param'))
    vc_add_shortcode_param('porto_animation_type', 'porto_vc_animation_type_field');

function porto_vc_animation_type_field($settings, $value) {
    $param_line = '<select name="' . $settings['param_name'] . '" class="wpb_vc_param_value dropdown wpb-input wpb-select ' . $settings['param_name'] . ' ' . $settings['type'] . '">';

    $param_line .= '<option value="">none</option>';

    $param_line .= '<optgroup label="' . __('Attention Seekers', 'porto-shortcodes') . '">';
    $options = array("bounce", "flash", "pulse", "rubberBand", "shake", "swing", "tada", "wobble");
    foreach ( $options as $option ) {
        $selected = '';
        if ( $option == $value ) $selected = ' selected="selected"';
        $param_line .= '<option value="' . $option . '"' . $selected . '>' . $option . '</option>';
    }
    $param_line .= '</optgroup>';

    $param_line .= '<optgroup label="' . __('Bouncing Entrances', 'porto-shortcodes') . '">';
    $options = array("bounceIn", "bounceInDown", "bounceInLeft", "bounceInRight", "bounceInUp");
    foreach ( $options as $option ) {
        $selected = '';
        if ( $option == $value ) $selected = ' selected="selected"';
        $param_line .= '<option value="' . $option . '"' . $selected . '>' . $option . '</option>';
    }
    $param_line .= '</optgroup>';

    $param_line .= '<optgroup label="' . __('Bouncing Exits', 'porto-shortcodes') . '">';
    $options = array("bounceOut", "bounceOutDown", "bounceOutLeft", "bounceOutRight", "bounceOutUp");
    foreach ( $options as $option ) {
        $selected = '';
        if ( $option == $value ) $selected = ' selected="selected"';
        $param_line .= '<option value="' . $option . '"' . $selected . '>' . $option . '</option>';
    }
    $param_line .= '</optgroup>';

    $param_line .= '<optgroup label="' . __('Fading Entrances', 'porto-shortcodes') . '">';
    $options = array("fadeIn", "fadeInDown", "fadeInDownBig", "fadeInLeft", "fadeInLeftBig", "fadeInRight", "fadeInRightBig", "fadeInUp", "fadeInUpBig");
    foreach ( $options as $option ) {
        $selected = '';
        if ( $option == $value ) $selected = ' selected="selected"';
        $param_line .= '<option value="' . $option . '"' . $selected . '>' . $option . '</option>';
    }
    $param_line .= '</optgroup>';

    $param_line .= '<optgroup label="' . __('Fading Exits', 'porto-shortcodes') . '">';
    $options = array("fadeOut", "fadeOutDown", "fadeOutDownBig", "fadeOutLeft", "fadeOutLeftBig", "fadeOutRight", "fadeOutRightBig", "fadeOutUp", "fadeOutUpBig");
    foreach ( $options as $option ) {
        $selected = '';
        if ( $option == $value ) $selected = ' selected="selected"';
        $param_line .= '<option value="' . $option . '"' . $selected . '>' . $option . '</option>';
    }
    $param_line .= '</optgroup>';

    $param_line .= '<optgroup label="' . __('Flippers', 'porto-shortcodes') . '">';
    $options = array("flip", "flipInX", "flipInY", "flipOutX", "flipOutY");
    foreach ( $options as $option ) {
        $selected = '';
        if ( $option == $value ) $selected = ' selected="selected"';
        $param_line .= '<option value="' . $option . '"' . $selected . '>' . $option . '</option>';
    }
    $param_line .= '</optgroup>';

    $param_line .= '<optgroup label="' . __('Lightspeed', 'porto-shortcodes') . '">';
    $options = array("lightSpeedIn", "lightSpeedOut");
    foreach ( $options as $option ) {
        $selected = '';
        if ( $option == $value ) $selected = ' selected="selected"';
        $param_line .= '<option value="' . $option . '"' . $selected . '>' . $option . '</option>';
    }
    $param_line .= '</optgroup>';

    $param_line .= '<optgroup label="' . __('Rotating Entrances', 'porto-shortcodes') . '">';
    $options = array("rotateIn", "rotateInDownLeft", "rotateInDownRight", "rotateInUpLeft", "rotateInUpRight");
    foreach ( $options as $option ) {
        $selected = '';
        if ( $option == $value ) $selected = ' selected="selected"';
        $param_line .= '<option value="' . $option . '"' . $selected . '>' . $option . '</option>';
    }
    $param_line .= '</optgroup>';

    $param_line .= '<optgroup label="' . __('Rotating Exits', 'porto-shortcodes') . '">';
    $options = array("rotateOut", "rotateOutDownLeft", "rotateOutDownRight", "rotateOutUpLeft", "rotateOutUpRight");
    foreach ( $options as $option ) {
        $selected = '';
        if ( $option == $value ) $selected = ' selected="selected"';
        $param_line .= '<option value="' . $option . '"' . $selected . '>' . $option . '</option>';
    }
    $param_line .= '</optgroup>';

    $param_line .= '<optgroup label="' . __('Sliding Entrances', 'porto-shortcodes') . '">';
    $options = array("slideInUp", "slideInDown", "slideInLeft", "slideInRight");
    foreach ( $options as $option ) {
        $selected = '';
        if ( $option == $value ) $selected = ' selected="selected"';
        $param_line .= '<option value="' . $option . '"' . $selected . '>' . $option . '</option>';
    }
    $param_line .= '</optgroup>';

    $param_line .= '<optgroup label="' . __('Sliding Exit', 'porto-shortcodes') . '">';
    $options = array("slideOutUp", "slideOutDown", "slideOutLeft", "slideOutRight");
    foreach ( $options as $option ) {
        $selected = '';
        if ( $option == $value ) $selected = ' selected="selected"';
        $param_line .= '<option value="' . $option . '"' . $selected . '>' . $option . '</option>';
    }
    $param_line .= '</optgroup>';

    $param_line .= '<optgroup label="' . __('Specials', 'porto-shortcodes') . '">';
    $options = array("hinge", "rollIn", "rollOut");
    foreach ( $options as $option ) {
        $selected = '';
        if ( $option == $value ) $selected = ' selected="selected"';
        $param_line .= '<option value="' . $option . '"' . $selected . '>' . $option . '</option>';
    }
    $param_line .= '</optgroup>';

    $param_line .= '</select>';

    return $param_line;
}

function porto_getCategoryChildsFull( $parent_id, $pos, $array, $level, &$dropdown ) {

    for ( $i = $pos; $i < count( $array ); $i ++ ) {
        if ( $array[ $i ]->category_parent == $parent_id ) {
            $name = str_repeat( "- ", $level ) . $array[ $i ]->name;
            $value = $array[ $i ]->slug;
			$dropdown[] = array(
				'label' => $name,
				'value' => $value,
			);
            porto_getCategoryChildsFull( $array[ $i ]->term_id, $i, $array, $level + 1, $dropdown );
        }
    }
}

// Add simple line icon font
if (!function_exists('vc_iconpicker_type_simpleline')) {
    add_filter( 'vc_iconpicker-type-simpleline', 'vc_iconpicker_type_simpleline' );

    function vc_iconpicker_type_simpleline( $icons ) {
        $simpleline_icons = array(
            array( 'Simple-Line-Icons-user' => 'User' ),
            array( 'Simple-Line-Icons-people' => 'People' ),
            array( 'Simple-Line-Icons-user-female' => 'User Female' ),
            array( 'Simple-Line-Icons-user-follow' => 'User Follow' ),
            array( 'Simple-Line-Icons-user-following' => 'User Following' ),
            array( 'Simple-Line-Icons-user-unfollow' => 'User Unfollow' ),
            array( 'Simple-Line-Icons-login' => 'Login' ),
            array( 'Simple-Line-Icons-logout' => 'Logout' ),
            array( 'Simple-Line-Icons-emotsmile' => 'Emotsmile' ),
            array( 'Simple-Line-Icons-phone' => 'Phone' ),
            array( 'Simple-Line-Icons-call-end' => 'Call End' ),
            array( 'Simple-Line-Icons-call-in' => 'Call In' ),
            array( 'Simple-Line-Icons-call-out' => 'Call Out' ),
            array( 'Simple-Line-Icons-map' => 'Map' ),
            array( 'Simple-Line-Icons-location-pin' => 'Location Pin' ),
            array( 'Simple-Line-Icons-direction' => 'Direction' ),
            array( 'Simple-Line-Icons-directions' => 'Directions' ),
            array( 'Simple-Line-Icons-compass' => 'Compass' ),
            array( 'Simple-Line-Icons-layers' => 'Layers' ),
            array( 'Simple-Line-Icons-menu' => 'Menu' ),
            array( 'Simple-Line-Icons-list' => 'List' ),
            array( 'Simple-Line-Icons-options-vertical' => 'Options Vertical' ),
            array( 'Simple-Line-Icons-options' => 'Options' ),
            array( 'Simple-Line-Icons-arrow-down' => 'Arrow Down' ),
            array( 'Simple-Line-Icons-arrow-left' => 'Arrow Left' ),
            array( 'Simple-Line-Icons-arrow-right' => 'Arrow Right' ),
            array( 'Simple-Line-Icons-arrow-up' => 'Arrow Up' ),
            array( 'Simple-Line-Icons-arrow-up-circle' => 'Arrow Up Circle' ),
            array( 'Simple-Line-Icons-arrow-left-circle' => 'Arrow Left Circle' ),
            array( 'Simple-Line-Icons-arrow-right-circle' => 'Arrow Right Circle' ),
            array( 'Simple-Line-Icons-arrow-down-circle' => 'Arrow Down Circle' ),
            array( 'Simple-Line-Icons-check' => 'Check' ),
            array( 'Simple-Line-Icons-clock' => 'Clock' ),
            array( 'Simple-Line-Icons-plus' => 'Plus' ),
            array( 'Simple-Line-Icons-minus' => 'Minus' ),
            array( 'Simple-Line-Icons-close' => 'Close' ),
            array( 'Simple-Line-Icons-event' => 'Event' ),
            array( 'Simple-Line-Icons-exclamation' => 'Exclamation' ),
            array( 'Simple-Line-Icons-organization' => 'Organization' ),
            array( 'Simple-Line-Icons-trophy' => 'Trophy' ),
            array( 'Simple-Line-Icons-screen-smartphone' => 'Smartphone' ),
            array( 'Simple-Line-Icons-screen-desktop' => 'Desktop' ),
            array( 'Simple-Line-Icons-plane' => 'Plane' ),
            array( 'Simple-Line-Icons-notebook' => 'Notebook' ),
            array( 'Simple-Line-Icons-mustache' => 'Mustache' ),
            array( 'Simple-Line-Icons-mouse' => 'Mouse' ),
            array( 'Simple-Line-Icons-magnet' => 'Magnet' ),
            array( 'Simple-Line-Icons-energy' => 'Energy' ),
            array( 'Simple-Line-Icons-disc' => 'Disc' ),
            array( 'Simple-Line-Icons-cursor' => 'Cursor' ),
            array( 'Simple-Line-Icons-cursor-move' => 'Cursor Move' ),
            array( 'Simple-Line-Icons-crop' => 'Crop' ),
            array( 'Simple-Line-Icons-chemistry' => 'Chemistry' ),
            array( 'Simple-Line-Icons-speedometer' => 'Speedometer' ),
            array( 'Simple-Line-Icons-shield' => 'Shield' ),
            array( 'Simple-Line-Icons-screen-tablet' => 'Tablet' ),
            array( 'Simple-Line-Icons-magic-wand' => 'Magic Wand' ),
            array( 'Simple-Line-Icons-hourglass' => 'Hourglass' ),
            array( 'Simple-Line-Icons-graduation' => 'Graduation' ),
            array( 'Simple-Line-Icons-ghost' => 'Ghost' ),
            array( 'Simple-Line-Icons-game-controller' => 'Game Controller' ),
            array( 'Simple-Line-Icons-fire' => 'Fire' ),
            array( 'Simple-Line-Icons-eyeglass' => 'Eyeglass' ),
            array( 'Simple-Line-Icons-envelope-open' => 'Envelope Open' ),
            array( 'Simple-Line-Icons-envelope-letter' => 'Envelope Letter' ),
            array( 'Simple-Line-Icons-bell' => 'Bell' ),
            array( 'Simple-Line-Icons-badge' => 'Badge' ),
            array( 'Simple-Line-Icons-anchor' => 'Anchor' ),
            array( 'Simple-Line-Icons-wallet' => 'Wallet' ),
            array( 'Simple-Line-Icons-vector' => 'Vector' ),
            array( 'Simple-Line-Icons-speech' => 'Speech' ),
            array( 'Simple-Line-Icons-puzzle' => 'Puzzle' ),
            array( 'Simple-Line-Icons-printer' => 'Printer' ),
            array( 'Simple-Line-Icons-present' => 'Present' ),
            array( 'Simple-Line-Icons-playlist' => 'Playlist' ),
            array( 'Simple-Line-Icons-pin' => 'Pin' ),
            array( 'Simple-Line-Icons-picture' => 'Picture' ),
            array( 'Simple-Line-Icons-handbag' => 'Handbag' ),
            array( 'Simple-Line-Icons-globe-alt' => 'Globe Alt' ),
            array( 'Simple-Line-Icons-globe' => 'Globe' ),
            array( 'Simple-Line-Icons-folder-alt' => 'Folder Alt' ),
            array( 'Simple-Line-Icons-folder' => 'Folder' ),
            array( 'Simple-Line-Icons-film' => 'Film' ),
            array( 'Simple-Line-Icons-feed' => 'Feed' ),
            array( 'Simple-Line-Icons-drop' => 'Drop' ),
            array( 'Simple-Line-Icons-drawer' => 'Drawer' ),
            array( 'Simple-Line-Icons-docs' => 'Docs' ),
            array( 'Simple-Line-Icons-doc' => 'Doc' ),
            array( 'Simple-Line-Icons-diamond' => 'Diamond' ),
            array( 'Simple-Line-Icons-cup' => 'Cup' ),
            array( 'Simple-Line-Icons-calculator' => 'Calculator' ),
            array( 'Simple-Line-Icons-bubbles' => 'Bubbles' ),
            array( 'Simple-Line-Icons-briefcase' => 'Briefcase' ),
            array( 'Simple-Line-Icons-book-open' => 'Book Open' ),
            array( 'Simple-Line-Icons-basket-loaded' => 'Basket Loaded' ),
            array( 'Simple-Line-Icons-basket' => 'Basket' ),
            array( 'Simple-Line-Icons-bag' => 'Bag' ),
            array( 'Simple-Line-Icons-action-undo' => 'Action Undo' ),
            array( 'Simple-Line-Icons-action-redo' => 'Action Redo' ),
            array( 'Simple-Line-Icons-wrench' => 'Wrench' ),
            array( 'Simple-Line-Icons-umbrella' => 'Umbrella' ),
            array( 'Simple-Line-Icons-trash' => 'Trash' ),
            array( 'Simple-Line-Icons-tag' => 'Tag' ),
            array( 'Simple-Line-Icons-support' => 'Support' ),
            array( 'Simple-Line-Icons-frame' => 'Frame' ),
            array( 'Simple-Line-Icons-size-fullscreen' => 'Size Fullscreen' ),
            array( 'Simple-Line-Icons-size-actual' => 'Size Actual' ),
            array( 'Simple-Line-Icons-shuffle' => 'Shuffle' ),
            array( 'Simple-Line-Icons-share-alt' => 'Share Alt' ),
            array( 'Simple-Line-Icons-share' => 'Share' ),
            array( 'Simple-Line-Icons-rocket' => 'Rocket' ),
            array( 'Simple-Line-Icons-question' => 'Question' ),
            array( 'Simple-Line-Icons-pie-chart' => 'Pie Chart' ),
            array( 'Simple-Line-Icons-pencil' => 'Pencil' ),
            array( 'Simple-Line-Icons-note' => 'Note' ),
            array( 'Simple-Line-Icons-loop' => 'Loop' ),
            array( 'Simple-Line-Icons-home' => 'Home' ),
            array( 'Simple-Line-Icons-grid' => 'Grid' ),
            array( 'Simple-Line-Icons-graph' => 'Graph' ),
            array( 'Simple-Line-Icons-microphone' => 'Microphone' ),
            array( 'Simple-Line-Icons-music-tone-alt' => 'Music Tone Alt' ),
            array( 'Simple-Line-Icons-music-tone' => 'Music Tone' ),
            array( 'Simple-Line-Icons-earphones-alt' => 'Earphones Alt' ),
            array( 'Simple-Line-Icons-earphones' => 'Earphones' ),
            array( 'Simple-Line-Icons-equalizer' => 'Equalizer' ),
            array( 'Simple-Line-Icons-like' => 'Like' ),
            array( 'Simple-Line-Icons-dislike' => 'Dislike' ),
            array( 'Simple-Line-Icons-control-start' => 'Control Start' ),
            array( 'Simple-Line-Icons-control-rewind' => 'Control Rewind' ),
            array( 'Simple-Line-Icons-control-play' => 'Control Play' ),
            array( 'Simple-Line-Icons-control-pause' => 'Control Pause' ),
            array( 'Simple-Line-Icons-control-forward' => 'Control Forward' ),
            array( 'Simple-Line-Icons-control-end' => 'Control End' ),
            array( 'Simple-Line-Icons-volume-1' => 'Volume 1' ),
            array( 'Simple-Line-Icons-volume-2' => 'Volume 2' ),
            array( 'Simple-Line-Icons-volume-off' => 'Volume Off' ),
            array( 'Simple-Line-Icons-calendar' => 'Calendar' ),
            array( 'Simple-Line-Icons-bulb' => 'Bulb' ),
            array( 'Simple-Line-Icons-chart' => 'Chart' ),
            array( 'Simple-Line-Icons-ban' => 'Ban' ),
            array( 'Simple-Line-Icons-bubble' => 'Bubble' ),
            array( 'Simple-Line-Icons-camcorder' => 'Camcorder' ),
            array( 'Simple-Line-Icons-camera' => 'Camera' ),
            array( 'Simple-Line-Icons-cloud-download' => 'Cloud Download' ),
            array( 'Simple-Line-Icons-cloud-upload' => 'Cloud Upload' ),
            array( 'Simple-Line-Icons-envelope' => 'Envelope' ),
            array( 'Simple-Line-Icons-eye' => 'Eye' ),
            array( 'Simple-Line-Icons-flag' => 'Flag' ),
            array( 'Simple-Line-Icons-heart' => 'Heart' ),
            array( 'Simple-Line-Icons-info' => 'Info' ),
            array( 'Simple-Line-Icons-key' => 'Key' ),
            array( 'Simple-Line-Icons-link' => 'Link' ),
            array( 'Simple-Line-Icons-lock' => 'Lock' ),
            array( 'Simple-Line-Icons-lock-open' => 'Lock Open' ),
            array( 'Simple-Line-Icons-magnifier' => 'Magnifier' ),
            array( 'Simple-Line-Icons-magnifier-add' => 'Magnifier Add' ),
            array( 'Simple-Line-Icons-magnifier-remove' => 'Magnifier Remove' ),
            array( 'Simple-Line-Icons-paper-clip' => 'Paper Clip' ),
            array( 'Simple-Line-Icons-paper-plane' => 'Paper Plane' ),
            array( 'Simple-Line-Icons-power' => 'Power' ),
            array( 'Simple-Line-Icons-refresh' => 'Refresh' ),
            array( 'Simple-Line-Icons-reload' => 'Reload' ),
            array( 'Simple-Line-Icons-settings' => 'Settings' ),
            array( 'Simple-Line-Icons-star' => 'Star' ),
            array( 'Simple-Line-Icons-symbol-female' => 'Symbol Female' ),
            array( 'Simple-Line-Icons-symbol-male' => 'Symbol Male' ),
            array( 'Simple-Line-Icons-target' => 'Target' ),
            array( 'Simple-Line-Icons-credit-card' => 'Credit Card' ),
            array( 'Simple-Line-Icons-paypal' => 'Paypal' ),
            array( 'Simple-Line-Icons-social-tumblr' => 'Tumblr' ),
            array( 'Simple-Line-Icons-social-twitter' => 'Twitter' ),
            array( 'Simple-Line-Icons-social-facebook' => 'Facebook' ),
            array( 'Simple-Line-Icons-social-instagram' => 'Instagram' ),
            array( 'Simple-Line-Icons-social-linkedin' => 'Linkedin' ),
            array( 'Simple-Line-Icons-social-pinterest' => 'Pinterest' ),
            array( 'Simple-Line-Icons-social-github' => 'Github' ),
            array( 'Simple-Line-Icons-social-google' => 'Google' ),
            array( 'Simple-Line-Icons-social-reddit' => 'Reddit' ),
            array( 'Simple-Line-Icons-social-skype' => 'Skype' ),
            array( 'Simple-Line-Icons-social-dribbble' => 'Dribbble' ),
            array( 'Simple-Line-Icons-social-behance' => 'Behance' ),
            array( 'Simple-Line-Icons-social-foursqare' => 'Foursqare' ),
            array( 'Simple-Line-Icons-social-soundcloud' => 'Soundcloud' ),
            array( 'Simple-Line-Icons-social-spotify' => 'Spotify' ),
            array( 'Simple-Line-Icons-social-stumbleupon' => 'Stumbleupon' ),
            array( 'Simple-Line-Icons-social-youtube' => 'Youtube' ),
            array( 'Simple-Line-Icons-social-dropbox' => 'Dropbox' ),
            array( 'Simple-Line-Icons-social-vkontakte' => 'Vkontakte' ),
            array( 'Simple-Line-Icons-social-steam' => 'Steam' ),
            array( 'Simple-Line-Icons-moustache' => 'Moustache' ),
            array( 'Simple-Line-Icons-bar-chart' => 'Bar Chart' ),
            array( 'Simple-Line-Icons-pointer' => 'Pointer' ),
            array( 'Simple-Line-Icons-users' => 'Users' ),
            array( 'Simple-Line-Icons-eyeglasses' => 'Eyeglasses' ),
            array( 'Simple-Line-Icons-symbol-fermale' => 'Symbol Fermale' ),
        );

        return array_merge( $icons, $simpleline_icons );
    }
}

// Add porto icon font
if (!function_exists('vc_iconpicker_type_porto')) {
    add_filter( 'vc_iconpicker-type-porto', 'vc_iconpicker_type_porto' );

    function vc_iconpicker_type_porto( $icons ) {
        $porto_icons = array(
            array( 'porto-icon-spin1' => 'Spin1' ),
			array( 'porto-icon-spin2' => 'Spin2' ),
			array( 'porto-icon-spin3' => 'Spin3' ),
			array( 'porto-icon-spin4' => 'Spin4' ),
			array( 'porto-icon-spin5' => 'Spin5' ),
			array( 'porto-icon-spin6' => 'Spin6' ),
			array( 'porto-icon-firefox' => 'Firefox' ),
			array( 'porto-icon-chrome' => 'Chrome' ),
			array( 'porto-icon-opera' => 'Opera' ),
			array( 'porto-icon-ie' => 'Ie' ),
			array( 'porto-icon-phone' => 'Phone' ),
			array( 'porto-icon-down-dir' => 'Down Dir' ),
			array( 'porto-icon-cart' => 'Cart' ),
			array( 'porto-icon-up-dir' => 'Up Dir' ),
			array( 'porto-icon-mode-grid' => 'Mode Grid' ),
			array( 'porto-icon-mode-list' => 'Mode List' ),
			array( 'porto-icon-compare' => 'Compare' ),
			array( 'porto-icon-wishlist' => 'Wishlist' ),
			array( 'porto-icon-search' => 'Search' ),
			array( 'porto-icon-left-dir' => 'Left Dir' ),
			array( 'porto-icon-right-dir' => 'Right Dir' ),
			array( 'porto-icon-down-open' => 'Down Open' ),
			array( 'porto-icon-left-open' => 'Left Open' ),
			array( 'porto-icon-right-open' => 'Right Open' ),
			array( 'porto-icon-up-open' => 'Up Open' ),
			array( 'porto-icon-angle-left' => 'Angle Left' ),
			array( 'porto-icon-angle-right' => 'Angle Right' ),
			array( 'porto-icon-angle-up' => 'Angle Up' ),
			array( 'porto-icon-angle-down' => 'Angle Down' ),
			array( 'porto-icon-down' => 'Down' ),
			array( 'porto-icon-left' => 'Left' ),
			array( 'porto-icon-right' => 'Right' ),
			array( 'porto-icon-up' => 'Up' ),
			array( 'porto-icon-angle-double-left' => 'Angle Double Left' ),
			array( 'porto-icon-angle-double-right' => 'Angle Double Right' ),
			array( 'porto-icon-angle-double-up' => 'Angle Double Up' ),
			array( 'porto-icon-angle-double-down' => 'Angle Double Down' ),
			array( 'porto-icon-mail' => 'Mail' ),
			array( 'porto-icon-location' => 'Location' ),
			array( 'porto-icon-skype' => 'Skype' ),
			array( 'porto-icon-right-open-big' => 'Right Open Big' ),
			array( 'porto-icon-left-open-big' => 'Left Open Big' ),
			array( 'porto-icon-down-open-big' => 'Down Open Big' ),
			array( 'porto-icon-up-open-big' => 'Up Open Big' ),
			array( 'porto-icon-cancel' => 'Cancel' ),
			array( 'porto-icon-user' => 'User' ),
			array( 'porto-icon-mail-alt' => 'Mail Alt' ),
			array( 'porto-icon-fax' => 'Fax' ),
			array( 'porto-icon-lock' => 'Lock' ),
			array( 'porto-icon-company' => 'Company' ),
			array( 'porto-icon-city' => 'City' ), 
			array( 'porto-icon-post' => 'Post' ), 
			array( 'porto-icon-country' => 'Country' ), 
			array( 'porto-icon-calendar' => 'Calendar' ), 
			array( 'porto-icon-doc' => 'Doc' ), 
			array( 'porto-icon-mobile' => 'Mobile' ), 
			array( 'porto-icon-clock' => 'Clock' ), 
			array( 'porto-icon-chat' => 'Chat' ), 
			array( 'porto-icon-tag' => 'Tag' ), 
			array( 'porto-icon-folder' => 'Folder' ), 
			array( 'porto-icon-folder-open' => 'Folder Open' ), 
			array( 'porto-icon-forward' => 'Forward' ), 
			array( 'porto-icon-reply' => 'Reply' ), 
			array( 'porto-icon-cog' => 'Cog' ), 
			array( 'porto-icon-cog-alt' => 'Cog Alt' ), 
			array( 'porto-icon-wrench' => 'Wrench' ), 
			array( 'porto-icon-quote-left' => 'Quote Left' ), 
			array( 'porto-icon-quote-right' => 'Quote Right' ), 
			array( 'porto-icon-gift' => 'Gift' ), 
			array( 'porto-icon-dollar' => 'Dollar' ), 
			array( 'porto-icon-euro' => 'Euro' ), 
			array( 'porto-icon-pound' => 'Pound' ), 
			array( 'porto-icon-rupee' => 'Rupee' ), 
			array( 'porto-icon-yen' => 'Yen' ), 
			array( 'porto-icon-rouble' => 'Rouble' ), 
			array( 'porto-icon-try' => 'Try' ),  
			array( 'porto-icon-won' => 'Won' ),  
			array( 'porto-icon-bitcoin' => 'Bitcoin' ),  
			array( 'porto-icon-ok' => 'Ok' ),  
			array( 'porto-icon-chevron-left' => 'Chevron Left' ),  
			array( 'porto-icon-chevron-right' => 'Chevron Right' ),  
			array( 'porto-icon-export' => 'Export' ), 
			array( 'porto-icon-star' => 'Star' ), 
			array( 'porto-icon-star-empty' => 'Star Empty' ), 
			array( 'porto-icon-plus-squared' => 'Plus Squared' ),  
			array( 'porto-icon-minus-squared' => 'Minus Squared' ),  
			array( 'porto-icon-plus-squared-alt' => 'Plus Squared Alt' ),  
			array( 'porto-icon-minus-squared-alt' => 'Minus Squared Alt' ),  
			array( 'porto-icon-truck' => 'Truck' ),  
			array( 'porto-icon-lifebuoy' => 'Lifebuoy' ),  
			array( 'porto-icon-pencil' => 'Pencil' ),  
			array( 'porto-icon-users' => 'Users' ),  
			array( 'porto-icon-video' => 'Video' ),  
			array( 'porto-icon-menu' => 'Menu' ),  
			array( 'porto-icon-desktop' => 'Desktop' ),  
			array( 'porto-icon-doc-inv' => 'Doc Inv' ),  
			array( 'porto-icon-circle' => 'Circle' ),  
			array( 'porto-icon-circle-empty' => 'Circle Empty' ),  
			array( 'porto-icon-circle-thin' => 'Circle Thin' ),  
			array( 'porto-icon-mini-cart' => 'Mini Cart' ),  
			array( 'porto-icon-paper-plane' => 'Paper Plane' ),  
			array( 'porto-icon-attention-alt' => 'Attention Alt' ),  
			array( 'porto-icon-info' => 'Info' ),  
			array( 'porto-icon-compare-link' => 'Compare Link' ),  
			array( 'porto-icon-cat-default' => 'Cat Default' ),  
			array( 'porto-icon-cat-computer' => 'Cat Computer' ),  
			array( 'porto-icon-cat-couch' => 'Cat Couch' ),  
			array( 'porto-icon-cat-garden' => 'Cat Garden' ),  
			array( 'porto-icon-cat-gift' => 'Cat Gift' ),  
			array( 'porto-icon-cat-shirt' => 'Cat Shirt' ),  
			array( 'porto-icon-cat-sport' => 'Cat Sport' ),  
			array( 'porto-icon-cat-toys' => 'Cat Toys' ),  
			array( 'porto-icon-tag-line' => 'Tag L`ine' ),  
			array( 'porto-icon-bag' => 'Bag' ),  
			array( 'porto-icon-search-1' => 'Search-1' ),  
			array( 'porto-icon-plus' => 'Plus' ),  
			array( 'porto-icon-minus' => 'Minus' ),  
			array( 'porto-icon-search-2' => 'Search-2' ),  
			array( 'porto-icon-bag-1' => 'Bag-1' ),  
			array( 'porto-icon-online-support' => 'Online Support' ),  
			array( 'porto-icon-shopping-bag' => 'Shopping Bag' ),  
			array( 'porto-icon-us-dollar' => 'Us Dollar' ),  
			array( 'porto-icon-shipped' => 'Shipped' ),  
			array( 'porto-icon-list' => 'List' ),  
			array( 'porto-icon-money' => 'Money' ),  
			array( 'porto-icon-shipping' => 'Shipping' ),  
			array( 'porto-icon-support' => 'Support' ),  
			array( 'porto-icon-bag-2' => 'Bag-2' ),  
			array( 'porto-icon-grid' => 'Grid' ),  
			array( 'porto-icon-bag-3' => 'Bag-3' ),  
			array( 'porto-icon-direction' => 'Direction' ),  
			array( 'porto-icon-home' => 'Home' ),  
			array( 'porto-icon-magnifier' => 'Magnifier' ),  
			array( 'porto-icon-magnifier-add' => 'Magnifier Add' ),  
			array( 'porto-icon-magnifier-remove' => 'Magnifier Remove' ),  
			array( 'porto-icon-phone-1' => 'Phone-1' ),  
			array( 'porto-icon-clock-1' => 'Clock-1' ),  
			array( 'porto-icon-heart' => 'Heart' ),  
			array( 'porto-icon-heart-1' => 'Heart-1' ),  
			array( 'porto-icon-earphones-alt' => 'Earphones Alt' ),
			array( 'porto-icon-credit-card' => 'Credit Card' ),
			array( 'porto-icon-action-undo' => 'Action Undo' ),
			array( 'porto-icon-envolope' => 'Envolope' ),
			array( 'porto-icon-twitter' => 'Twitter' ),
			array( 'porto-icon-facebook' => 'Facebook' ),
			array( 'porto-icon-spinner' => 'Spinner' ),
			array( 'porto-icon-instagram' => 'Instagram' ),
			array( 'porto-icon-check-empty' => 'Check Empty' ),
			array( 'porto-icon-check' => 'Check' ),
        );

        return array_merge( $icons, $porto_icons );
    }
}

/* 4.0 */
// extra shortcodes added in 4.0
if ( function_exists( 'vc_add_shortcode_param' ) ) {
    vc_add_shortcode_param('porto_param_heading' , 'porto_param_heading_callback');
    if ( !class_exists( 'Ultimate_VC_Addons' ) ) {
        vc_add_shortcode_param('number' , 'porto_number_settings_field' );
        vc_add_shortcode_param('datetimepicker', 'porto_datetimepicker', plugins_url('../assets/js/bootstrap-datetimepicker.min.js',__FILE__));
    }
    vc_add_shortcode_param('porto_boxshadow', 'porto_boxshadow_callback', plugins_url('../assets/js/box-shadow-param.js',__FILE__));
}
function porto_param_heading_callback($settings, $value) {
    $dependency = '';
    $param_name = isset($settings['param_name']) ? $settings['param_name'] : '';
    $class = isset($settings['class']) ? $settings['class'] : '';
    $text = isset($settings['text']) ? $settings['text'] : '';
    $output = '<h4 '.$dependency.' class="wpb_vc_param_value '.esc_attr( $class ).'">'.$text.'</h4>';
    $output .= '<input type="hidden" name="'.esc_attr( $settings['param_name'] ).'" class="wpb_vc_param_value porto-param-heading '.esc_attr( $settings['param_name'] ).' '. esc_attr( $settings['type'] ).'_field" value="'.esc_attr( $value ).'" '.$dependency.'/>';
    return $output;
}
function porto_number_settings_field($settings, $value) {
    $dependency = '';
    $param_name = isset($settings['param_name']) ? $settings['param_name'] : '';
    $type       = isset($settings['type']) ? $settings['type'] : '';
    $min        = isset($settings['min']) ? $settings['min'] : '';
    $max        = isset($settings['max']) ? $settings['max'] : '';
    $step       = isset($settings['step']) ? $settings['step'] : '';
    $suffix     = isset($settings['suffix']) ? $settings['suffix'] : '';
    $class      = isset($settings['class']) ? $settings['class'] : '';
    $output = '<input type="number" min="'.esc_attr( $min ).'" max="'.esc_attr( $max ).'" step="'. esc_attr( $step ).'" class="wpb_vc_param_value ' . esc_attr( $param_name ) . ' ' . esc_attr( $type ) . ' ' . esc_attr( $class ) . '" name="' . esc_attr( $param_name ) . '" value="'.esc_attr( $value ).'" style="max-width:100px; margin-right: 10px;" />'.esc_html($suffix);
    return $output;
}
function porto_boxshadow_callback($settings, $value) {
    $dependency = '';
    $positions = $settings['positions'];
    $enable_color = isset($settings['enable_color']) ? $settings['enable_color'] : true;
    $unit = isset($settings['unit']) ? $settings['unit'] : 'px';

    $uid = 'porto-boxshadow-'. rand(1000, 9999);
    $html  = '<div class="porto-boxshadow" id="'.esc_attr( $uid ).'" data-unit="'.esc_attr( $unit ).'" >';

    $label = "Shadow Style";
    if ( isset( $settings['label_style'] ) && $settings['label_style'] ) {
        $label = $settings['label_style'];
    }
    $html .= '<div class="porto-bs-select-block">';
        $html .= '<div class="porto-bs-select-wrap">';
            $html .= '<select class="porto-bs-select" >';
                $html .= '<option value="none">'. esc_html__( 'None','porto-shortcodes' ) .'</option>';
                $html .= '<option value="inherit">'. esc_html__( 'Inherit','porto-shortcodes' ) .'</option>';
                $html .= '<option value="inset">'. esc_html__( 'Inset','porto-shortcodes' ) .'</option>';
                $html .= '<option value="outset">'. esc_html__( 'Outset','porto-shortcodes' ) .'</option>';
            $html .= '</select>';
        $html .= '</div>';
    $html .= '</div>';

    $html .= '<div class="porto-bs-input-block" >';
    foreach( $positions as $key => $default_value ) {
        switch ($key) {
            case 'Horizontal':
                $dashicon = 'dashicons dashicons-leftright';
                $html .= porto_boxshadow_param_item($dashicon, $unit, $default_value, $key);
            break;
        case 'Vertical':
                $dashicon = 'dashicons dashicons-sort';
                $html .= porto_boxshadow_param_item($dashicon, $unit, $default_value, $key);
            break;
        case 'Blur':
                $dashicon = 'dashicons dashicons-visibility';
                $html .= porto_boxshadow_param_item($dashicon, $unit, $default_value, $key);
            break;
        case 'Spread':
                $dashicon = 'dashicons dashicons-location';
                $html .= porto_boxshadow_param_item($dashicon, $unit, $default_value, $key);
            break;
      }
    }
    $html .= porto_bs_get_units($unit);
    $html .= '</div>';


    if ($enable_color) {
        $label = __( "Box Shadow Color", "porto-shortcodes" );
        if ( isset($settings['label_color']) && $settings['label_color']!='' ) {
            $label = $settings['label_color'];
        }
        $html .= '<div class="porto-bs-colorpicker-block">';
            $html .= '<div class="label wpb_element_label">';
                $html .=  esc_html( $label );
            $html .= '</div>';
            $html .= '<div class="porto-bs-colorpicker-wrap">';
                $html .= '<input name="" class="porto-bs-colorpicker cs-wp-color-picker" type="text" value="" />';
            $html .= '</div>';
        $html .= '</div>';
    }

    $html .= '  <input type="hidden" data-unit="'. esc_attr( $unit ) .'" name="'. esc_attr( $settings['param_name'] ).'" class="wpb_vc_param_value porto-bs-result-value '. esc_attr( $settings['param_name'] ) .' '. esc_attr( $settings['type'] ) .'_field" value="'. esc_attr( $value ) .'" '.$dependency.' />';
    $html .= '</div>';
  return $html;
}
function porto_boxshadow_param_item($dashicon, $unit, $default_value, $key) {
    $html  = '<div class="porto-bs-input-wrap">';
        $html .= '<span class="porto-bs-icon">';
            $html .= '<span class="porto-bs-tooltip">'.esc_html( $key ).'</span>';
            $html .= '<i class="'.esc_attr($dashicon).'"></i>';
        $html .= '</span>';
        $html .= '<input type="number" class="porto-bs-input" data-unit="'. esc_attr( $unit ) .'" data-id="'.strtolower( esc_attr( $key ) ).'" data-default="'. esc_attr( $default_value ) .'" placeholder="'. esc_attr( $key ) .'" />';
    $html .= '</div>';
    return $html;
}
function porto_bs_get_units($unit) {
    $html  = '<div class="porto-bs-unit">';
        $html .= '<label>'.esc_html( $unit ).'</label>';
    $html .= '</div>';
    return $html;
}

function porto_datetimepicker($settings, $value) {
    $dependency = '';
    $param_name = isset($settings['param_name']) ? $settings['param_name'] : '';
    $type = isset($settings['type']) ? $settings['type'] : '';
    $class = isset($settings['class']) ? $settings['class'] : '';
    $uni = uniqid('datetimepicker-'.rand());
    $output = '<div id="porto-date-time'.esc_attr( $uni ).'" class="porto-datetime"><input data-format="yyyy/MM/dd hh:mm:ss" readonly class="wpb_vc_param_value ' . esc_attr( $param_name ) . ' ' . esc_attr( $type ) . ' ' . esc_attr( $class ) . '" name="' . esc_attr( $param_name ) . '" style="width:258px;" value="'. esc_attr( $value ).'" '.$dependency.'/><div class="add-on" > <i data-time-icon="fa fa-calendar-o" data-date-icon="fa fa-calendar-o"></i></div></div>';
    $output .= '<script type="text/javascript"></script>';
    return $output;
}

// functions used in extra shortcodes 
function porto_get_box_shadow( $content = null, $data = '' ) {

    $result = '';
    if ( $content ) {
        $mainStr = explode('|', $content);
        $string = '';
        $mainArr = array();
        if ( !empty($mainStr) && is_array($mainStr) ) {
            foreach ($mainStr as $key => $value) {
                if ( !empty( $value ) ) {
                    $string = explode(":",$value);
                    if ( is_array( $string ) ) {
                        if ( !empty($string[1]) && $string[1] != 'outset' ) {
                            $mainArr[$string[0]] = $string[1];
                        }
                    }
                }
            }
        }

        $rm_bar = str_replace("|","",$content);
        $rm_colon = str_replace(":"," ",$rm_bar);
        $strKeys = str_replace("horizontal","",$rm_colon);
        $strKeys = str_replace("vertical","",$strKeys);
        $strKeys = str_replace("blur","",$strKeys);
        $strKeys = str_replace("spread","",$strKeys);
        $strKeys = str_replace("color","",$strKeys);
        $strKeys = str_replace("style","",$strKeys);
        $strKeys = str_replace("outset","",$strKeys);

        if ( $data ) {
            switch ($data) {
                case 'data':
                    $result  = $strKeys;
                    break;
                case 'array':
                    $result = $mainArr;
                    break;
                case 'css':
                default:
                    $result  = 'box-shadow:'.$strKeys.';';
                break;
            }
        } else {
            $result  = 'box-shadow:'.$strKeys.';';
        }
    }

    return $result;
}

function porto_parse_google_font( $google_fonts_data ) {
    $styles = array();
    //$google_fonts_obj = new Vc_Google_Fonts();
    if ( ! empty( $google_fonts_data ) ) {
        $google_fonts_family = explode( ':', $google_fonts_data['values']['font_family'] );
        $styles[] = 'font-family:' . $google_fonts_family[0];
        if ( isset( $google_fonts_data['values']['font_style'] ) ) {
        $google_fonts_styles = explode( ':', $google_fonts_data['values']['font_style'] );
            $styles[] = 'font-weight:' . $google_fonts_styles[1];
            if ( isset( $google_fonts_styles[2] ) ) {
                $styles[] = 'font-style:' . $google_fonts_styles[2];
            }
        }
    }

    return $styles;
}