<?php
/*
* Add-on Name: Adjustable Spacer for WPBakery Page Builder
* Add-on URI: http://dev.brainstormforce.com
*/
if(!class_exists("Ultimate_Spacer")){
	class Ultimate_Spacer{
		function __construct(){
			if ( Ultimate_VC_Addons::$uavc_editor_enable ) {
				add_action("init",array($this,"ultimate_spacer_init"));
			}
			add_shortcode("ultimate_spacer",array($this,"ultimate_spacer_shortcode"));
		}
		function ultimate_spacer_init(){
			if(function_exists("vc_map")){
				vc_map(
					array(
					   "name" => __("Spacer / Gap","ultimate_vc"),
					   "base" => "ultimate_spacer",
					   "class" => "vc_ultimate_spacer",
					   "icon" => "vc_ultimate_spacer",
					   "category" => "Ultimate VC Addons",
					   "description" => __("Adjust space between components.","ultimate_vc"),
					   "params" => array(
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("<i class='dashicons dashicons-desktop'></i> Desktop", "ultimate_vc"),
								"param_name" => "height",
								"admin_label" => true,
								"value" => 10,
								"min" => 1,
								"max" => 500,
								"suffix" => "px",
								"description" => __("Enter value in pixels", "ultimate_vc"),
								//"edit_field_class" => "vc_col-sm-4 vc_column"
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("<i class='dashicons dashicons-tablet' style='transform: rotate(90deg);'></i> Tabs", "ultimate_vc"),
								"param_name" => "height_on_tabs",
								"admin_label" => true,
								"value" => '',
								"min" => 1,
								"max" => 500,
								"suffix" => "px",
								//"description" => __("Enter value in pixels", "ultimate_vc"),
								"edit_field_class" => "vc_col-sm-3 vc_column"
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("<i class='dashicons dashicons-tablet'></i> Tabs", "ultimate_vc"),
								"param_name" => "height_on_tabs_portrait",
								"admin_label" => true,
								"value" => '',
								"min" => 1,
								"max" => 500,
								"suffix" => "px",
								//"description" => __("Enter value in pixels", "ultimate_vc"),
								"edit_field_class" => "vc_col-sm-3 vc_column"
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("<i class='dashicons dashicons-smartphone' style='transform: rotate(90deg);'></i> Mobile", "ultimate_vc"),
								"param_name" => "height_on_mob_landscape",
								"admin_label" => true,
								"value" => '',
								"min" => 1,
								"max" => 500,
								"suffix" => "px",
								//"description" => __("Enter value in pixels", "ultimate_vc"),
								"edit_field_class" => "vc_col-sm-3 vc_column"
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("<i class='dashicons dashicons-smartphone'></i> Mobile", "ultimate_vc"),
								"param_name" => "height_on_mob",
								"admin_label" => true,
								"value" => '',
								"min" => 1,
								"max" => 500,
								"suffix" => "px",
								//"description" => __("Enter value in pixels", "ultimate_vc"),
								"edit_field_class" => "vc_col-sm-3 vc_column"
							),
						)
					)
				);
			}
		}
		function ultimate_spacer_shortcode($atts){
			//wp_enqueue_script('ultimate-custom');
			$height = $output = $height_on_tabs = $height_on_mob = '';
			extract(shortcode_atts(array(
				"height" => "",
				"height_on_tabs" => "",
				"height_on_tabs_portrait" => "",
				"height_on_mob" => "",
				"height_on_mob_landscape" => ""
			),$atts));
			if($height_on_mob == "" && $height_on_tabs == "")
				$height_on_mob = $height_on_tabs = $height;
			$style = 'clear:both;';
			$style .= 'display:block;';
			$uid = uniqid();
			$output .= '<div class="ult-spacer spacer-'.esc_attr($uid).'" data-id="'.esc_attr($uid).'" data-height="'.esc_attr($height).'" data-height-mobile="'.esc_attr($height_on_mob).'" data-height-tab="'.esc_attr($height_on_tabs).'" data-height-tab-portrait="'.esc_attr($height_on_tabs_portrait).'" data-height-mobile-landscape="'.esc_attr($height_on_mob_landscape).'" style="'.esc_attr($style).'"></div>';
			return $output;
		}
	} // end class
	new Ultimate_Spacer;
	if ( class_exists( 'WPBakeryShortCode' ) && !class_exists( 'WPBakeryShortCode_ultimate_spacer' ) ) {
	    class WPBakeryShortCode_ultimate_spacer extends WPBakeryShortCode {
	    }
	}
}