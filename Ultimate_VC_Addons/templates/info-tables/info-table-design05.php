<?php
/*
* Add-on Name: Info Tables for WPBakery Page Builder
* Template : Design layout 05
*/
if(!function_exists('ult_info_table_generate_design05')) {
	function ult_info_table_generate_design05($atts,$content = null){
		$icon_type = $icon_img = $img_width = $icon = $icon_color = $icon_color_bg = $icon_size = $icon_style = $icon_border_style = $icon_border_radius = $icon_color_border = $icon_border_size = $icon_border_spacing = $el_class = $package_heading = $heading_tag = $package_sub_heading = $sub_heading_tag = $package_price = $package_unit = $package_btn_text = $package_link = $package_featured = $color_bg_main = $color_txt_main = $color_bg_highlight = $color_txt_highlight = $color_scheme = $use_cta_btn = $target = $link_title  = $rel = '';
		extract(shortcode_atts(array(
			'color_scheme' => 'black',
			'package_heading' => '',
			'heading_tag' =>'h3',
			'package_sub_heading' => '',
			'sub_heading_tag' => 'h5',
			'icon_type' => 'none',
			'icon' => '',
			'icon_img' => '',
			'img_width' => '48',
			'icon_size' => '32',
			'icon_color' => '#333333',
			'icon_style' => 'none',
			'icon_color_bg' => '#ffffff',
			'icon_color_border' => '#333333',
			'icon_border_style' => '',
			'icon_border_size' => '1',
			'icon_border_radius' => '500',
			'icon_border_spacing' => '50',
			'use_cta_btn' => '',
			'package_btn_text' => '',
			'package_link' => '',
			'package_featured' => '',
			'color_bg_main' => '',
			'color_txt_main' => '',
			'color_bg_highlight' => '',
			'color_txt_highlight' => '',
			'heading_font_family' => '',
			'heading_font_style' => '',
			'heading_font_size' => '',
			'heading_font_color' => '',
			'heading_line_height' => '',
			'subheading_font_family' => '',
			'subheading_font_style' => '',
			'subheading_font_size' => '',
			'subheading_font_color' => '',
			'subheading_line_height' => '',
			'features_font_family' => '',
			'features_font_style' => '',
			'features_font_size' => '',
			'features_font_color' => '',
			'features_line_height' => '',
			'button_font_family' => '',
			'button_font_style' => '',
			'button_font_size' => '',
			'button_font_color' => '',
			'button_line_height' => '',
			'el_class' => '',
			'features_min_ht'=>'',
			'css_info_tables' => '',
		),$atts));
		$css_info_tables = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css_info_tables, ' ' ), "ultimate_info_table", $atts );
		$css_info_tables = esc_attr( $css_info_tables );
		$output = $link = $target = $featured = $featured_style = $normal_style = $dynamic_style = $box_icon = '';
		if($icon_type !== "none"){
			$box_icon = do_shortcode('[just_icon icon_type="'.esc_attr($icon_type).'" icon="'.esc_attr($icon).'" icon_img="'.esc_attr($icon_img).'" img_width="'.esc_attr($img_width).'" icon_size="'.esc_attr($icon_size).'" icon_color="'.esc_attr($icon_color).'" icon_style="'.esc_attr($icon_style).'" icon_color_bg="'.esc_attr($icon_color_bg).'" icon_color_border="'.esc_attr($icon_color_border).'"  icon_border_style="'.esc_attr($icon_border_style).'" icon_border_size="'.esc_attr($icon_border_size).'" icon_border_radius="'.esc_attr($icon_border_radius).'" icon_border_spacing="'.esc_attr($icon_border_spacing).'"]');
		}
		if($color_scheme == "custom"){
			if($color_bg_main !== ""){
				$normal_style .= 'background:'.$color_bg_main.';';
			}
			if($color_txt_main !== ""){
				$normal_style .= 'color:'.$color_txt_main.';';
			}
			if($color_bg_highlight!== ""){
				$featured_style .= 'background:'.$color_bg_highlight.';';
			}
			if($color_txt_highlight !== ""){
				$featured_style .= 'color:'.$color_txt_highlight.';';
			}
		}
		if($package_link !== ""){
			$href 			= vc_build_link($package_link);

			$link 			= ( isset( $href['url'] ) && $href['url'] !== '' ) ? $href['url']  : '';
			$target 		= ( isset( $href['target'] ) && $href['target'] !== '' ) ? esc_attr( trim( $href['target'] ) ) : '';
			$link_title 	= ( isset( $href['title'] ) && $href['title'] !== '' ) ? esc_attr($href['title']) : '';
			$rel 			= ( isset( $href['rel'] ) && $href['rel'] !== '' ) ? esc_attr($href['rel']) : '';
		} else {
			$link = "#";
		}
		if($package_featured !== ""){
			$featured = "ult_featured";
		}
		if($use_cta_btn == "box"){
			$output .= '<a '. Ultimate_VC_Addons::uavc_link_init($link, $target, $link_title, $rel ).' class="ult_price_action_button" style="'.esc_attr($featured_style).'">'.$package_btn_text;
		}

		/* typography */

		$heading_style_inline = $sub_heading_inline = $features_inline = $button_inline = '';

		// heading
		if($heading_font_family != '')
		{
			$hdfont_family = get_ultimate_font_family($heading_font_family);
			if($hdfont_family !== '')
				$heading_style_inline .= 'font-family:\''.$hdfont_family.'\';';
		}

		if('span' == $heading_tag){
			$heading_style_inline .= 'display:block;';
		}

		$heading_style_inline .= get_ultimate_font_style($heading_font_style);

		// if($heading_font_size != '')
		// 	$heading_style_inline .= 'font-size:'.$heading_font_size.'px;';

		// if($heading_line_height != '')
		// 	$heading_style_inline .= 'line-height:'.$heading_line_height.'px;';

		if($heading_font_color != '')
			$heading_style_inline .= 'color:'.$heading_font_color.';';

		if(is_numeric($heading_font_size)){
				$heading_font_size = 'desktop:'.$heading_font_size.'px;';
			}
			
		if(is_numeric($heading_line_height)){
				$heading_line_height = 'desktop:'.$heading_line_height.'px;';
			}

			$info_table_id = 'Info-table-wrap-'.rand(1000, 9999);
			$info_table_args = array(
                'target' => '#'.$info_table_id.' '.$heading_tag, // set targeted element e.g. unique class/id etc.
                'media_sizes' => array(
                    'font-size' => $heading_font_size, // set 'css property' & 'ultimate_responsive' sizes. Here $title_responsive_font_size holds responsive font sizes from user input.
                   	'line-height' => $heading_line_height
                ),
            );
            $info_table_data_list = get_ultimate_vc_responsive_media_css($info_table_args);

		// sub heading
		if($subheading_font_family != '')
		{
			$shfont_family = get_ultimate_font_family($subheading_font_family);
			if($shfont_family !== '')
				$sub_heading_inline .= 'font-family:\''.$shfont_family.'\';';
		}

		if('span' == $sub_heading_tag){
			$sub_heading_inline .= 'display:block;';
		}

		$sub_heading_inline .= get_ultimate_font_style($subheading_font_style);

		// if($subheading_font_size != '')
		// 	$sub_heading_inline .= 'font-size:'.$subheading_font_size.'px;';

		// if($subheading_line_height != '')
		// 	$sub_heading_inline .= 'line-height:'.$subheading_line_height.'px;';

		if($subheading_font_color != '')
			$sub_heading_inline .= 'color:'.$subheading_font_color.';';

		if(is_numeric($subheading_font_size)){
				$subheading_font_size = 'desktop:'.$subheading_font_size.'px;';
			}
			
		if(is_numeric($subheading_line_height)){
				$subheading_line_height = 'desktop:'.$subheading_line_height.'px;';
			}

			$info_table_sub_head_args = array(
                'target' => '#'.$info_table_id.' '.$sub_heading_tag, // set targeted element e.g. unique class/id etc.
                'media_sizes' => array(
                    'font-size' => $subheading_font_size, // set 'css property' & 'ultimate_responsive' sizes. Here $title_responsive_font_size holds responsive font sizes from user input.
                   	'line-height' => $subheading_line_height
                ),
            );
            $info_table_sub_head_data_list = get_ultimate_vc_responsive_media_css($info_table_sub_head_args);

		// features
		if($features_font_family != '')
		{
			$featuresfont_family = get_ultimate_font_family($features_font_family);
			if($featuresfont_family !== '')
				$features_inline .= 'font-family:\''.$featuresfont_family.'\';';
		}

		$features_inline .= get_ultimate_font_style($features_font_style);

		// if($features_font_size != '')
		// 	$features_inline .= 'font-size:'.$features_font_size.'px;';

		// if($features_line_height != '')
		// 	$features_inline .= 'line-height:'.$features_line_height.'px;';

		if($features_font_color != '')
			$features_inline .= 'color:'.$features_font_color.';';

		if(is_numeric($features_font_size)){
				$features_font_size = 'desktop:'.$features_font_size.'px;';
			}
			
		if(is_numeric($features_line_height)){
				$features_line_height = 'desktop:'.$features_line_height.'px;';
			}

			$info_table_features_id= 'info_table_features_wrap-'.rand(1000, 9999);
			
			$info_table_features_args = array(
                'target' => '#'.$info_table_features_id.'.ult_price_features', // set targeted element e.g. unique class/id etc.
                'media_sizes' => array(
                    'font-size' => $features_font_size, // set 'css property' & 'ultimate_responsive' sizes. Here $title_responsive_font_size holds responsive font sizes from user input.
                   	'line-height' => $features_line_height
                ),
            );
            $info_table_features_data_list = get_ultimate_vc_responsive_media_css($info_table_features_args);

		/*---min ht style---*/
		$info_tab_ht='';$info_tab_ht_style='';
		if($features_min_ht !== ""){
			    $info_tab_ht='info_min_ht';
				$info_tab_ht_style .= 'min-height:'.$features_min_ht.'px;';
			}

		// button
		if($button_font_family != '')
		{
			$buttonfont_family = get_ultimate_font_family($button_font_family);
			if($buttonfont_family !== '')
				$button_inline .= 'font-family:\''.$buttonfont_family.'\';';
		}

		$button_inline .= get_ultimate_font_style($button_font_style);

		// if($button_font_size != '')
		// 	$button_inline .= 'font-size:'.$button_font_size.'px;';

		// if($button_line_height != '')
		// 	$button_inline .= 'line-height:'.$button_line_height.'px;';		

		if($button_font_color != '')
			$button_inline .= 'color:'.$button_font_color.';';

		if(is_numeric($button_font_size)){
				$button_font_size = 'desktop:'.$button_font_size.'px;';
			}
			
		if(is_numeric($button_line_height)){
				$button_line_height = 'desktop:'.$button_line_height.'px;';
			}

			$info_table_btn_id= 'info_table_btn_wrap-'.rand(1000, 9999);
			
			$info_table_btn_args = array(
                'target' => '#'.$info_table_btn_id.' .ult_price_action_button', // set targeted element e.g. unique class/id etc.
                'media_sizes' => array(
                    'font-size' => $button_font_size, // set 'css property' & 'ultimate_responsive' sizes. Here $title_responsive_font_size holds responsive font sizes from user input.
                   	'line-height' => $button_line_height
                ),
            );
            $info_table_btn_data_list = get_ultimate_vc_responsive_media_css($info_table_btn_args);

		$output .= '<div class="ult_pricing_table_wrap ult_info_table ult_design_5 '.esc_attr($featured).' ult-cs-'.esc_attr($color_scheme).' '.esc_attr($el_class).' '.esc_attr($css_info_tables).'">
					<div class="ult_pricing_table '.esc_attr($info_tab_ht).'" style="'.esc_attr($normal_style).' '.esc_attr($info_tab_ht_style).'">';
			$output .= '<div id="'.esc_attr($info_table_id).'" class="ult_pricing_heading" style="'.esc_attr($featured_style).'">
							<'.esc_attr($heading_tag).' class="ult-responsive" '.$info_table_data_list.' style="'.esc_attr($heading_style_inline).'">'.$package_heading.'</'.esc_attr($heading_tag).'>';
						if($package_sub_heading !== ''){
							$output .= '<'.esc_attr($sub_heading_tag).' class="ult-responsive" '.$info_table_sub_head_data_list.' style="'.esc_attr($sub_heading_inline).'">'.$package_sub_heading.'</'.esc_attr($sub_heading_tag).'>';
						}
			$output .= '</div><!--ult_pricing_heading-->';
			if(isset($box_icon) && $box_icon != '') :
			$output .= '<div class="ult_price_body_block" style="'.esc_attr($featured_style).'">
							<div class="ult_price_body">
								<div class="ult_price">
									'.$box_icon.'
								</div>
							</div>
						</div><!--ult_price_body_block-->';
			endif;
			$output .= '<div id="'.esc_attr($info_table_features_id).'" '.$info_table_features_data_list.' class="ult-responsive ult_price_features" style="'.esc_attr($features_inline).'">
							'.wpb_js_remove_wpautop(do_shortcode($content), true).'
						</div><!--ult_price_features-->';
			if($use_cta_btn == "true"){
				$output .= '<div id="'.esc_attr($info_table_btn_id).'"  class="ult_price_link" style="'.esc_attr($normal_style).'">
							<a '. Ultimate_VC_Addons::uavc_link_init($link, $target, $link_title, $rel ).'  '.$info_table_btn_data_list.' class="ult-responsive ult_price_action_button" style="'.esc_attr($featured_style).' '.esc_attr($button_inline).'">'.$package_btn_text.'</a>
						</div><!--ult_price_link-->';
			}
			$output .= '<div class="ult_clr"></div>
			</div><!--pricing_table-->
		</div><!--pricing_table_wrap-->';
		if($use_cta_btn == "box"){
			$output .= '</a>';
		}
		return $output;
	}
}