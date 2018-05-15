<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WPEAE_VariationAttributes
 *
 * @author and
 */
if (!class_exists('WPEAE_VariationAttributes')):

	class WPEAE_VariationAttributes {

		function __construct() {
			if (!is_admin()) {
				add_action('wp_enqueue_scripts', array($this, 'add_assets'));
				add_filter('woocommerce_dropdown_variation_attribute_options_html', array($this, 'variation_attribute_options_html'), 10, 2);
			}
		}

		function add_assets() {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
			$plugin_data = get_plugin_data(__FILE__);

			wp_enqueue_style('wpeae-ali-variation-css', WPEAE_ALIEXPRESS_VARIATION_ROOT_URL . 'assets/css/style.css', array(), $plugin_data['Version']);

			wp_enqueue_script('wpeae-ali-variation-js', WPEAE_ALIEXPRESS_VARIATION_ROOT_URL . 'assets/js/script.js', array(), $plugin_data['Version'], true);
		}

		function variation_attribute_options_html($html, $args) {
			if(get_option('wpeae_aliexpress_variation_udav', false)){
				return $html;
			}else{
				if(is_wpeae_goods($args['product']->id)){
					$html = preg_replace('#\s(id|name)="[^"]+"#', '', $html);
					$html = '<div style="display:none;">' . $html . '</div>';
					return  $html . $this->variation_attribute_options($args);
				}else{
					return $html;
				}
			}
		}

		function variation_attribute_options($args = array()) {
			$options = $args['options'];
			$product = $args['product'];
			$attribute = $args['attribute'];
			$name = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title($attribute);
			$id = $args['id'] ? $args['id'] : sanitize_title($attribute);
			$class = $args['class'];
			
			if (empty($options) && !empty($product) && !empty($attribute)) {
				$attributes = $product->get_variation_attributes();
				$options = $attributes[$attribute];
			}
			/*
			$attributes = $product->get_variation_attributes();
		   
	  
				$options = $attributes[$attribute];
			$variation_dropdown_html = '';
			
			foreach ( $attributes as $attribute_name => $options ) :
				$selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( urldecode( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) : $product->get_variation_default_attribute( $attribute_name );
				
				ob_start();
								wc_dropdown_variation_attribute_options( array( 'options' => $options, 'attribute' => $attribute_name, 'product' => $product, 'selected' => $selected ) );
				$variation_dropdown_html .= ob_get_clean();
			 endforeach;            
			 */
			$html = '<div class="wpeae_variation_set">';
			$html .= '<input type="hidden" id="' . esc_attr($id) . '" class="wpeae_variation_attribute_val" name="' . esc_attr($name) . '">';
			$html .= '<input type="hidden" class="wpeae_variation_attribute_default_val" value="' . $args['selected'] . '">';

			if (!empty($options)) {
				if ($product && taxonomy_exists($attribute)) {
					// Get terms if this is a taxonomy - ordered. We need the names too.
					$terms = wc_get_product_terms($product->id, $attribute, array('fields' => 'all'));

					foreach ($terms as $term) {
						if (in_array($term->slug, $options)) {
							$image_meta_key = 'attr_' . sanitize_title($attribute) . '_' . sanitize_title($term->slug) . '_img';
							$image = get_post_meta($product->id, $image_meta_key, true);
							if(intval($image)>0){
								$tmp_img_src = wp_get_attachment_image_src(intval($image));
								$image = ($tmp_img_src && isset($tmp_img_src[0]))?$tmp_img_src[0]:"";
							}

							$color_meta_key = 'attr_' . sanitize_title($attribute) . '_' . sanitize_title($term->slug) . '_color';
							$color = get_post_meta($product->id, $color_meta_key, true);
							
							if ($image) {
								$html .= '<a href="#" class="wpeae_variation_select" data-attribute_name="attribute_' . esc_attr(sanitize_title($attribute)) . '" data-attribute_value="' . esc_attr($term->slug) . '" title="'.esc_html($term->name).'"><img src="' . $image . '" title="'.esc_html($term->name).'"/></a> ';
							}else if ($color) {
								$html .= '<a href="#" class="wpeae_variation_select" data-attribute_name="attribute_' . esc_attr(sanitize_title($attribute)) . '" data-attribute_value="' . esc_attr($term->slug) . '" title="'.esc_html($term->name).'"><span class="color" style="background: '.$color.'!important;"></span></a> ';
							} else {
								$html .= '<a href="#" class="wpeae_variation_select" data-attribute_name="attribute_' . esc_attr(sanitize_title($attribute)) . '" data-attribute_value="' . esc_attr($term->slug) . '">' . esc_html($term->name) . '</a> ';
							}
						}
					}
				} else {
					foreach ($options as $option) {
						$image_meta_key = 'attr_' . sanitize_title($attribute) . '_' . sanitize_title($option) . '_img';
						$image = get_post_meta($product->id, $image_meta_key, true);
						if(intval($image)>0){
							$tmp_img_src = wp_get_attachment_image_src(intval($image));
							$image = ($tmp_img_src && isset($tmp_img_src[0]))?$tmp_img_src[0]:"";
						}

						$color_meta_key = 'attr_' . sanitize_title($attribute) . '_' . sanitize_title($option) . '_color';
						$color = get_post_meta($product->id, $color_meta_key, true);

						// This handles < 2.4.0 bw compatibility where text attributes were not sanitized.
						//$selected = sanitize_title($args['selected']) === $args['selected'] ? selected($args['selected'], sanitize_title($option), false) : selected($args['selected'], $option, false);

						if ($image) {
							$html .= '<a href="#" class="wpeae_variation_select" data-attribute_name="attribute_' . esc_attr(sanitize_title($attribute)) . '" data-attribute_value="' . esc_attr($option) . '" title="'.esc_html($option).'"><img src="' . $image . '" title="'.esc_html($option).'"/></a> ';
						} else if ($color) {
							$html .= '<a href="#" class="wpeae_variation_select" data-attribute_name="attribute_' . esc_attr(sanitize_title($attribute)) . '" data-attribute_value="' . esc_attr($option) . '" title="'.esc_html($option).'"><span class="color" style="background: '.$color.'!important;"></span></a> ';
						} else {
							$html .= '<a href="#" class="wpeae_variation_select" data-attribute_name="attribute_' . esc_attr(sanitize_title($attribute)) . '" data-attribute_value="' . esc_attr($option) . '"><span>' . esc_html($option) . '</span></a> ';
						}
					}
				}
			}

			$html .= '</div>';

			echo $html;
		}

	}

endif;
