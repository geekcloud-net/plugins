<?php
if(!class_exists('Ultimate_Number_Param'))
{
	class Ultimate_Number_Param
	{
		function __construct()
		{
			if(defined('WPB_VC_VERSION') && version_compare(WPB_VC_VERSION, 4.8) >= 0) {
				if(function_exists('vc_add_shortcode_param'))
				{
					vc_add_shortcode_param('number' , array(&$this, 'number_settings_field' ));
				}
			}
			else {
				if(function_exists('add_shortcode_param'))
				{
					add_shortcode_param('number' , array(&$this, 'number_settings_field' ));
				}
			}
		}

		function number_settings_field($settings, $value)
		{
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

	}
}

if(class_exists('Ultimate_Number_Param'))
{
	$Ultimate_Number_Param = new Ultimate_Number_Param();
}
