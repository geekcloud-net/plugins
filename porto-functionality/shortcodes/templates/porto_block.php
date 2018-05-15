<?php
$output = $id = $name = $animation_type = $animation_duration = $animation_delay = $el_class = '';
extract(shortcode_atts(array(
    'id' => '',
    'name' => '',
    'animation_type' => '',
    'animation_duration' => 1000,
    'animation_delay' => 0,
    'el_class' => '',
), $atts));

if (!post_type_exists('block'))
    return;

if ($id || $name) {
    $el_class = porto_shortcode_extract_class( $el_class );

    if ($id)
        $posts = new WP_Query(array( 'include' => $id, 'post_type' => 'block' ));

    if ($name)
        $posts = new WP_Query(array( 'name' => $name, 'post_type' => 'block' ));

    if ($posts->have_posts()) {
        $posts->the_post();

        global $post;

        $post_content = $post->post_content;
        $use_google_map = get_post_meta( $post->ID, 'porto_page_use_google_map_api', true );

        if ( '1' === $use_google_map || stripos( $post_content, '[porto_google_map') ) {
            wp_enqueue_script( 'googleapis' );
        }
        if ( class_exists( 'Ultimate_VC_Addons' ) ) {
            $isAjax = false;
            $ultimate_ajax_theme = get_option('ultimate_ajax_theme');
            if($ultimate_ajax_theme == 'enable')
                $isAjax = true;
            $dependancy = array('jquery');

            $bsf_options = get_option('bsf_options');
            $ultimate_global_scripts = (isset($bsf_options['ultimate_global_scripts'])) ? $bsf_options['ultimate_global_scripts'] : false;

            if ($ultimate_global_scripts !== 'enable') {
                if(stripos($post_content, 'font_call:'))
                {
                    preg_match_all('/font_call:(.*?)"/',$post_content, $display);
                    enquque_ultimate_google_fonts_optimzed($display[1]);
                }

                $ultimate_js = get_option('ultimate_js', 'disable');
                $bsf_dev_mode = (isset($bsf_options['dev_mode'])) ? $bsf_options['dev_mode'] : false;

                if(($ultimate_js == 'enable' || $isAjax == true) && ($bsf_dev_mode != 'enable') )
                {
                    if(
                        stripos( $post_content, '[swatch_container')
                        || stripos( $post_content, '[ultimate_modal')
                    )
                    {
                        wp_enqueue_script('ultimate-modernizr');
                    }

                    if( stripos( $post_content, '[ultimate_exp_section') ||
                        stripos( $post_content, '[info_circle') ) {
                        wp_enqueue_script('jquery_ui');
                    }

                    if( stripos( $post_content, '[icon_timeline') ) {
                        wp_enqueue_script('masonry');
                    }

                    if($isAjax == true) { // if ajax site load all js
                        wp_enqueue_script('masonry');
                    }

                    if( stripos( $post_content, '[ultimate_google_map') ) {
                        if(defined('DISABLE_ULTIMATE_GOOGLE_MAP_API') && (DISABLE_ULTIMATE_GOOGLE_MAP_API == true || DISABLE_ULTIMATE_GOOGLE_MAP_API == 'true'))
                            $load_map_api = false;
                        else
                            $load_map_api = true;
                        if($load_map_api)
                            wp_enqueue_script('googleapis');
                    }

                    if( stripos( $post_content, '[ultimate_modal') ) {
                        //$modal_fixer = get_option('ultimate_modal_fixer');
                        //if($modal_fixer === 'enable')
                        //wp_enqueue_script('ultimate-modal-all-switched');
                        //else
                        wp_enqueue_script('ultimate-modal-all');
                    }
                }
                else if($ultimate_js == 'disable')
                {
                    wp_enqueue_script('ultimate-vc-params');

                    if(
                        stripos( $post_content, '[ultimate_spacer')
                        || stripos( $post_content, '[ult_buttons')
                        || stripos( $post_content, '[ult_team')
                        || stripos( $post_content, '[ultimate_icon_list')
                    ) {
                        wp_enqueue_script('ultimate-custom');
                    }
                    if(
                        stripos( $post_content, '[just_icon')
                        || stripos( $post_content, '[ult_animation_block')
                        || stripos( $post_content, '[icon_counter')
                        || stripos( $post_content, '[ultimate_google_map')
                        || stripos( $post_content, '[icon_timeline')
                        || stripos( $post_content, '[bsf-info-box')
                        || stripos( $post_content, '[info_list')
                        || stripos( $post_content, '[ultimate_info_table')
                        || stripos( $post_content, '[interactive_banner_2')
                        || stripos( $post_content, '[interactive_banner')
                        || stripos( $post_content, '[ultimate_pricing')
                        || stripos( $post_content, '[ultimate_icons')
                    ) {
                        wp_enqueue_script('ultimate-appear');
                        wp_enqueue_script('ultimate-custom');
                    }
                    if( stripos( $post_content, '[ultimate_heading') ) {
                        wp_enqueue_script("ultimate-headings-script");
                    }
                    if( stripos( $post_content, '[ultimate_carousel') ) {
                        wp_enqueue_script('ult-slick');
                        wp_enqueue_script('ultimate-appear');
                        wp_enqueue_script('ult-slick-custom');
                    }
                    if( stripos( $post_content, '[ult_countdown') ) {
                        wp_enqueue_script('jquery.timecircle');
                        wp_enqueue_script('jquery.countdown');
                    }
                    if( stripos( $post_content, '[icon_timeline') ) {
                        wp_enqueue_script('masonry');
                    }
                    if( stripos( $post_content, '[ultimate_info_banner') ) {
                        wp_enqueue_script('ultimate-appear');
                        wp_enqueue_script('utl-info-banner-script');
                    }
                    if( stripos( $post_content, '[ultimate_google_map') ) {
                        if(defined('DISABLE_ULTIMATE_GOOGLE_MAP_API') && (DISABLE_ULTIMATE_GOOGLE_MAP_API == true || DISABLE_ULTIMATE_GOOGLE_MAP_API == 'true'))
                            $load_map_api = false;
                        else
                            $load_map_api = true;
                        if($load_map_api)
                            wp_enqueue_script('googleapis');
                    }
                    if( stripos( $post_content, '[swatch_container') ) {
                        wp_enqueue_script('ultimate-modernizr');
                        wp_enqueue_script('swatchbook-js');
                    }
                    if( stripos( $post_content, '[ult_ihover') ) {
                        wp_enqueue_script('ult_ihover_js');
                    }
                    if( stripos( $post_content, '[ult_hotspot') ) {
                        wp_enqueue_script('ult_hotspot_tooltipster_js');
                        wp_enqueue_script('ult_hotspot_js');
                    }
                    if( stripos( $post_content, '[ult_content_box') ) {
                        wp_enqueue_script('ult_content_box_js');
                    }
                    if( stripos( $post_content, '[bsf-info-box') ) {
                        wp_enqueue_script('info_box_js');
                    }
                    if( stripos( $post_content, '[icon_counter') ) {
                        wp_enqueue_script('flip_box_js');
                    }
                    if( stripos( $post_content, '[ultimate_ctation') ) {
                        wp_enqueue_script('utl-ctaction-script');
                    }
                    if( stripos( $post_content, '[stat_counter') ) {
                        wp_enqueue_script('ultimate-appear');
                        wp_enqueue_script('ult-stats-counter-js');
                        //wp_enqueue_script('ult-slick-custom');
                        wp_enqueue_script('ultimate-custom');
                        array_push($dependancy,'stats-counter-js');
                    }
                    if( stripos( $post_content, '[ultimate_video_banner') ) {
                        wp_enqueue_script('ultimate-video-banner-script');
                    }
                    if( stripos( $post_content, '[ult_dualbutton') ) {
                        wp_enqueue_script('jquery.dualbtn');

                    }
                    if( stripos( $post_content, '[ult_createlink') ) {
                        wp_enqueue_script('jquery.ult_cllink');
                    }
                    if( stripos( $post_content, '[ultimate_img_separator') ) {
                        wp_enqueue_script('ultimate-appear');
                        wp_enqueue_script('ult-easy-separator-script');
                        wp_enqueue_script('ultimate-custom');
                    }

                    if( stripos( $post_content, '[ult_tab_element') ) {
                        wp_enqueue_script('ultimate-appear');
                        wp_enqueue_script('ult_tabs_rotate');
                        wp_enqueue_script('ult_tabs_acordian_js');
                    }
                    if( stripos( $post_content, '[ultimate_exp_section') ) {
                        wp_enqueue_script('jquery_ui');
                        wp_enqueue_script('jquery_ultimate_expsection');
                    }

                    if( stripos( $post_content, '[info_circle') ) {
                        wp_enqueue_script('jquery_ui');
                        wp_enqueue_script('ultimate-appear');
                        wp_enqueue_script('info-circle');
                        //wp_enqueue_script('info-circle-ui-effect');
                    }

                    if( stripos( $post_content, '[ultimate_modal') ) {
                        wp_enqueue_script('ultimate-modernizr');
                        //$modal_fixer = get_option('ultimate_modal_fixer');
                        //if($modal_fixer === 'enable')
                        //wp_enqueue_script('ultimate-modal-all-switched');
                        //else
                        wp_enqueue_script('ultimate-modal-all');
                    }

                    if( stripos( $post_content, '[ult_sticky_section') ) {
                        wp_enqueue_script('ult_sticky_js');
                        wp_enqueue_script('ult_sticky_section_js');
                    }

                    if( stripos( $post_content, '[ult_team') ) {
                        wp_enqueue_script('ultimate-team');
                    }
                }

                $ultimate_css = get_option('ultimate_css');

                if($ultimate_css == "enable"){
                    if( stripos( $post_content, '[ultimate_carousel') ) {
                        wp_enqueue_style("ult-icons");
                    }
                } else {

                    $ib_2_found = $ib_found = false;

                    if( stripos( $post_content, '[ult_animation_block') ) {
                        wp_enqueue_style('ultimate-animate');
                    }
                    if( stripos( $post_content, '[icon_counter') ) {
                        wp_enqueue_style('ultimate-animate');
                        wp_enqueue_style('ult-flip-style');
                    }
                    if( stripos( $post_content, '[ult_countdown') ) {
                        wp_enqueue_style('ult-countdown');
                    }
                    if( stripos( $post_content, '[ultimate_icon_list') ) {
                        wp_enqueue_style('ultimate-animate');
                        wp_enqueue_style('ultimate-tooltip');
                    }
                    if( stripos( $post_content, '[ultimate_carousel') ) {
                        wp_enqueue_style("ult-slick");
                        wp_enqueue_style("ult-icons");
                        wp_enqueue_style("ultimate-animate");
                    }
                    if( stripos( $post_content, '[ultimate_fancytext') ) {
                        wp_enqueue_style('ultimate-fancytext-style');
                    }
                    if( stripos( $post_content, '[ultimate_ctation') ) {
                        wp_enqueue_style('utl-ctaction-style');
                    }
                    if( stripos( $post_content, '[ult_buttons') ) {
                        wp_enqueue_style( 'ult-btn' );
                    }
                    if( stripos( $post_content, '[ultimate_heading') ) {
                        wp_enqueue_style("ultimate-headings-style");
                    }
                    if( stripos( $post_content, '[ultimate_icons') || stripos( $post_content, '[single_icon')) {
                        wp_enqueue_style('ultimate-animate');
                        wp_enqueue_style('ultimate-tooltip');
                    }
                    if( stripos( $post_content, '[ult_ihover') ) {
                        wp_enqueue_style( 'ult_ihover_css' );
                    }
                    if( stripos( $post_content, '[ult_hotspot') ) {
                        wp_enqueue_style( 'ult_hotspot_css' );
                        wp_enqueue_style( 'ult_hotspot_tooltipster_css' );
                    }
                    if( stripos( $post_content, '[ult_content_box') ) {
                        wp_enqueue_style('ult_content_box_css');
                    }
                    if( stripos( $post_content, '[bsf-info-box') ) {
                        wp_enqueue_style('ultimate-animate');
                        wp_enqueue_style('info-box-style');
                    }
                    if( stripos( $post_content, '[info_circle') ) {
                        wp_enqueue_style('ultimate-animate');
                        wp_enqueue_style('info-circle');
                    }
                    if( stripos( $post_content, '[ultimate_info_banner') ) {
                        wp_enqueue_style('utl-info-banner-style');
                        wp_enqueue_style('ultimate-animate');
                    }
                    if( stripos( $post_content, '[icon_timeline') ) {
                        wp_enqueue_style('ultimate-animate');
                        wp_enqueue_style('ultimate-timeline-style');
                    }
                    if( stripos( $post_content, '[just_icon') ) {
                        wp_enqueue_style('ultimate-animate');
                        wp_enqueue_style('ultimate-tooltip');
                    }
                    if( stripos( $post_content, '[interactive_banner_2') ) {
                        $ib_2_found = true;
                    }
                    if(stripos( $post_content, '[interactive_banner') && !stripos( $post_content, '[interactive_banner_2')) {
                        $ib_found = true;
                    }
                    if(stripos( $post_content, '[interactive_banner ') && stripos( $post_content, '[interactive_banner_2')) {
                        $ib_found = true;
                        $ib_2_found = true;
                    }

                    if( $ib_found && !$ib_2_found ) {
                        wp_enqueue_style('ultimate-animate');
                        wp_enqueue_style('ult-interactive-banner');
                    }
                    else if( !$ib_found && $ib_2_found ) {
                        wp_enqueue_style('ult-ib2-style');
                    }
                    else if($ib_found && $ib_2_found) {
                        wp_enqueue_style('ultimate-animate');
                        wp_enqueue_style('ult-interactive-banner');
                        wp_enqueue_style('ult-ib2-style');
                    }
                    if( stripos( $post_content, '[info_list') ) {
                        wp_enqueue_style('ultimate-animate');
                    }
                    if( stripos( $post_content, '[ultimate_modal') ) {
                        wp_enqueue_style('ultimate-animate');
                        wp_enqueue_style('ultimate-modal');
                    }
                    if( stripos( $post_content, '[ultimate_info_table') ) {
                        wp_enqueue_style('ultimate-animate');
                        wp_enqueue_style("ultimate-pricing");
                    }
                    if( stripos( $post_content, '[ultimate_pricing') ) {
                        wp_enqueue_style('ultimate-animate');
                        wp_enqueue_style("ultimate-pricing");
                    }
                    if( stripos( $post_content, '[swatch_container') ) {
                        wp_enqueue_style('swatchbook-css');
                    }
                    if( stripos( $post_content, '[stat_counter') ) {
                        wp_enqueue_style('ultimate-animate');
                        wp_enqueue_style('ult-stats-counter-style');
                    }
                    if( stripos( $post_content, '[ultimate_video_banner') ) {
                        wp_enqueue_style('ultimate-video-banner-style');
                    }
                    if( stripos( $post_content, '[ult_dualbutton') ) {
                        wp_enqueue_style('ult-dualbutton');
                    }
                    if( stripos( $post_content, '[ult_createlink') ) {
                        wp_enqueue_style('ult_cllink');
                    }
                    if( stripos( $post_content, '[ultimate_img_separator') ) {
                        wp_enqueue_style('ultimate-animate');
                        wp_enqueue_style('ult-easy-separator-style');
                    }
                    if( stripos( $post_content, '[ult_tab_element') ) {
                        wp_enqueue_style('ult_tabs');
                        wp_enqueue_style('ult_tabs_acordian');
                    }
                    if( stripos( $post_content, '[ultimate_exp_section') ) {
                        wp_enqueue_style('style_ultimate_expsection');
                    }
                    if( stripos( $post_content, '[ult_sticky_section') ) {
                        wp_enqueue_style('ult_sticky_section_css');
                    }
                    if( stripos( $post_content, '[ult_team') ) {
                        wp_enqueue_style('ultimate-team');
                    }
                }

                if( stripos( $post_content, '[ultimate_google_map') ) {
                    if (!wp_script_is('googleapis', 'done')) {
                        $api = 'https://maps.googleapis.com/maps/api/js';
                        $map_key = bsf_get_option('map_key');
                        if($map_key != false) {
                            $arr_params = array(
                                'key' => $map_key
                            );
                            $api = esc_url( add_query_arg( $arr_params , $api ));
                        }
                        echo "<script type='text/javascript' src='" . $api . "'></script>";
                        wp_dequeue_script( 'googleapis' );
                    }
                }
            }
        }

        $shortcodes_custom_css = get_post_meta( get_the_ID(), '_wpb_shortcodes_custom_css', true );
        $shortcodes_custom_css .= get_post_meta( get_the_ID(), 'custom_css', true );
        if ( ! empty( $shortcodes_custom_css ) ) {
            $output .= '<style type="text/css">';
            $output .= $shortcodes_custom_css;
            $output .= '</style>';
        }

        $output .= '<div class="porto-block '. $el_class . '"';
        if ($animation_type) {
            $output .= ' data-appear-animation="'.$animation_type.'"';
            if ($animation_delay)
                $output .= ' data-appear-animation-delay="'.$animation_delay.'"';
            if ($animation_duration && $animation_duration != 1000)
                $output .= ' data-appear-animation-duration="'.$animation_duration.'"';
        }
        $output .= '>';

        if (get_post_meta( get_the_ID(), 'container', true ) == 'yes')
            $output .= '<div class="container">';

        $output .= do_shortcode($post_content);

        if (get_post_meta( get_the_ID(), 'container', true ) == 'yes')
            $output .= '</div>';

        $output .= '</div>';

        $shortcodes_custom_js = get_post_meta( get_the_ID(), 'custom_js_body', true );
        if ( ! empty( $shortcodes_custom_js ) ) {
            $output .= '<script type="text/javascript">';
            $output .= $shortcodes_custom_js;
            $output .= '</script>';
        }

        echo $output;
    }

    wp_reset_postdata();
}