<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * wm_refresh_token.
 *
 * @return bool
 */
if ( !function_exists('wm_refresh_token') ) {
	function wm_refresh_token ( $woomelly_get_settings = array() ) {
		return WMeli::refresh_token( $woomelly_get_settings );
	}
} //End wm_refresh_token()

/**
 * wm_memory_num.
 *
 * @return float
 */
if ( !function_exists('wm_memory_num') ) {
	function wm_memory_num ( $size ) {
		if ( function_exists('wc_let_to_num') ) {
			return wc_let_to_num( $size );
		}
		$l = substr($size, -1);
		$ret = substr($size, 0, -1);
		switch( strtoupper( $l ) ) {
			case 'P' :
				$ret *= 1024;
			case 'T' :
				$ret *= 1024;
			case 'G' :
				$ret *= 1024;
			case 'M' :
				$ret *= 1024;
			case 'K' :
				$ret *= 1024;
		}
		return $ret;
	}
} //End wm_memory_num()

/**
 * wm_print_alert.
 *
 * @return string
 */
if ( ! function_exists( 'wm_print_alert' ) ) {
    function wm_print_alert ( $msg, $type = 'primary', $link = true ) {
    	switch ( $type ) {
    		case 'primary':
    			echo '
					<div class="uk-alert-primary" uk-alert>
						'.( ($link)? '<a class="uk-alert-close" uk-close></a>' : '' ). '
						<p>'.$msg.'</p>
					</div>';
    		break;
    		case 'success':
    			echo '
					<div class="uk-alert-success" uk-alert>
						'.( ($link)? '<a class="uk-alert-close" uk-close></a>' : '' ). '
						<p>'.$msg.'</p>
					</div>';
    		break;
    		case 'warning':
    			echo '
					<div class="uk-alert-warning" uk-alert>
						'.( ($link)? '<a class="uk-alert-close" uk-close></a>' : '' ). '
						<p>'.$msg.'</p>
					</div>';
    		break;
    		case 'danger':
    			echo '
					<div class="uk-alert-danger" uk-alert>
						'.( ($link)? '<a class="uk-alert-close" uk-close></a>' : '' ). '
						<p>'.$msg.'</p>
					</div>';
    		break;
    	}
    }
} //End wm_print_alert()

/**
 * wm_replace_tags.
 *
 * @return string
 */
if ( ! function_exists( 'wm_replace_tags' ) ) {
    function wm_replace_tags ( $text, $tags ) {
    	if ( $text != "" && !empty($tags) ) {
    		foreach ( $tags as $key => $value ) {
    			$text = str_replace( $key, $value, $text );
    		}
    	}
    	return $text;
    }
} //End wm_replace_tags()

/**
 * wm_get_url.
 *
 * @return string
 */
if ( ! function_exists( 'wm_get_url' ) ) {
	function wm_get_url ( $url ) {
		if ( is_ssl() ) {
			$url = str_replace( 'http:', 'https:', $url );
		}
		return $url;
	}
} //End wm_get_url()

/**
 * wm_get_template_sync.
 *
 * @return class WMTemplateSync | false
 */
if ( ! function_exists( 'wm_get_template_sync' ) ) {
	function wm_get_template_sync ( $template_sync_id ) {
		$template_sync_id = absint($template_sync_id);
		$wm_template = new WMTemplateSync( $template_sync_id );
		if ( $wm_template->get_id() > 0 ) {
			return $wm_template;
		} else {
			return false;
		}
	}
} //End wm_get_template_sync()

/**
 * wm_get_product_by_code.
 *
 * @return class WMProduct | false
 */
if ( ! function_exists( 'wm_get_product_by_code' ) ) {
	function wm_get_product_by_code ( $code ) {
		global $wpdb;
		$_result = null;

		$wmproduct = $wpdb->get_row( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wm_code_meli' AND meta_value = '".$code."';", OBJECT );
		if ( is_object($wmproduct) ) {
			$wm_product = new WMProduct( $wmproduct->post_id );
			if ( $wm_product ) {
				return $wm_product;
			}
		}
		return $_result;
	}
} //End wm_get_product_by_code ()

/**
 * wm_get_order_by_code.
 *
 * @return class WMOrder | false
 */
if ( ! function_exists( 'wm_get_order_by_code' ) ) {
	function wm_get_order_by_code ( $code ) {
		global $wpdb;
		$_result = null;

		$wmorder = $wpdb->get_row( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wm_code_meli_order' AND meta_value = '".$code."';", OBJECT );
		if ( is_object($wmorder) ) {
			$wm_order = new WMOrder( $wmorder->post_id );
			if ( $wm_order ) {
				return $wm_order;
			}
		}
		return $_result;
	}
} //End wm_get_order_by_code ()

/**
 * wm_get_templatesync_meta.
 *
 * @return string
 */
if ( ! function_exists( 'wm_get_templatesync_meta' ) ) {
	function wm_get_templatesync_meta ( $meta_key, $templatesync_id ) {
		global $wpdb;
		$_result = '';
		$original_meta_key = $meta_key;

		$meta_key = WMTemplateSync::get_name_meta_key( $meta_key );
		$templatesync = $wpdb->get_row( "SELECT templatesync_value FROM {$wpdb->prefix}wm_templatesync_meta WHERE templatesync_id = '".$templatesync_id."' AND templatesync_key = '".$meta_key."';", OBJECT );
		if ( is_object($templatesync) ) {
			$_result = $templatesync->templatesync_value;
			if ( $original_meta_key == 'woomelly_name_template_field' ) {
				if ( $_result == "" ) {
					$_result = '#' . str_pad($templatesync_id, 6, "0", STR_PAD_LEFT);
				}
			}
		} else if ( $original_meta_key == 'woomelly_name_template_field' ) {
			$_result = '#' . str_pad($templatesync_id, 6, "0", STR_PAD_LEFT);
		}

		return $_result;
	}
} //End wm_get_templatesync_meta()

/**
 * wm_get_select_attributes.
 *
 * @return string
 */
if ( ! function_exists( 'wm_get_select_attributes' ) ) {
	function wm_get_select_attributes( $attributes, $data_to_send, $loop ) {
		if ( !isset($data_to_send['value_id']) && !isset($data_to_send['id']) ) {
			$data_to_send['ml_attribute_id'] = '-1';
		} else if ( isset($data_to_send['value_id']) ) {
			$data_to_send['ml_attribute_id'] = $data_to_send['value_id'];
		} else {
			$data_to_send['ml_attribute_id'] = $data_to_send['id'];
		}
		/* else if (isset($data_to_send['id']) ) {
			$data_to_send['ml_attribute_id'] = $data_to_send['id'];
		} else {
			$data_to_send['attribute_value'] = $data_to_send['value_id'];
			$data_to_send['ml_attribute_id'] = '';
		}*/
		$select_allow_variations = '';
		if ( !empty($attributes) ) {
			foreach ( $attributes as $value_attribute ) {
				if ( isset($value_attribute->tags->allow_variations) ) {
					if ( $value_attribute->value_type == 'list' || $value_attribute->value_type == 'string' ) {
						if ( $select_allow_variations == "" ) {
							$select_allow_variations .= '<select name="woomelly_attribute_field_'.$loop.'[]" id="woomelly_attribute_field" class="postform">';
							$select_allow_variations .= '<option value="">'.__("- Select -", "woomelly").'</option>';
							$select_allow_variations .= '<option value="'.$data_to_send["attribute_name"].'::'.$data_to_send["attribute_value"].'::-1::-1" ' . ( ($data_to_send["ml_attribute_id"]=="-1")? "selected=\"selected\"" : "" ) . '>'.__("— Custom —", "woomelly").'</option>';
						}
						$select_allow_variations .= '<optgroup label="' . ucfirst( $value_attribute->name ) . '">';
						$select_allow_variations .= '<option value="'.$data_to_send["attribute_name"].'::'.$data_to_send["attribute_value"].'::-1::'.$value_attribute->id.'" ' . ( ($data_to_send["ml_attribute_id"]==$value_attribute->id)? "selected=\"selected\"" : "" ) . ' >'.sprintf(__("— Custom %s —", "woomelly"), ucfirst( $value_attribute->name )).'</option>';
						if ( !empty($value_attribute->values) ) {
							foreach ( $value_attribute->values as $value ) {
								$select_allow_variations .= '<option value="'.$data_to_send["attribute_name"].'::'.$data_to_send["attribute_value"].'::'. $value->id .'::'. $value_attribute->id .'" ' . ( ($data_to_send["ml_attribute_id"]==$value->id)? "selected=\"selected\"" : "" ) . '>'. $value->name .'</option>';
							}
						}
					}
				}
			}
			if ( $select_allow_variations != "" ) {
				$select_allow_variations .= '</select>';
			}
		}
		return $select_allow_variations;
	}
} //End wm_get_select_attributes()

/**
 * wm_delete_templatesync_product.
 *
 * @return void
 */
if ( ! function_exists( 'wm_delete_templatesync_product' ) ) {
	function wm_delete_templatesync_product ( $template_id ) {
		global $wpdb;

		$all_product_query = $wpdb->get_results( "SELECT DISTINCT A.ID FROM {$wpdb->posts} AS A INNER JOIN {$wpdb->postmeta} AS B ON A.ID=B.post_id WHERE A.post_type='product' AND B.meta_key='_wm_template_sync_id' AND B.meta_value='" . $template_id . "';", OBJECT );
		if ( !empty($all_product_query) ) {
			foreach ( $all_product_query as $value ) {
				update_post_meta( $value->ID, '_wm_template_sync_id', '' );
			}
		}
	}
} //End wm_delete_templatesync_product()

/**
 * wm_get_all_product.
 *
 * @return array
 */
if ( ! function_exists( 'wm_get_all_product' ) ) {
	function wm_get_all_product( $report ) {
		global $wpdb;
		$all_product = array();

		switch ($report) {		
			case 'templatesync':
				$all_product_query = $wpdb->get_results( "SELECT DISTINCT A.ID FROM {$wpdb->posts} AS A INNER JOIN {$wpdb->postmeta} AS B ON A.ID=B.post_id WHERE A.post_type='product' AND A.post_status='publish' AND B.meta_key='_wm_template_sync_id' AND B.meta_value <> '';", OBJECT );
				if ( !empty($all_product_query) ) {
					foreach ( $all_product_query as $value ) {
						$all_product[] = $value->ID;
					}
				}
				break;
		}

		return $all_product;
	}
} //End wm_get_all_product()

/**
 * wm_get_available_variations.
 *
 * @return array
 */
if ( ! function_exists( 'wm_get_available_variations' ) ) {
	function wm_get_available_variations ( $product ) {
		$available_variations = array();
		$available_variation = array();

		foreach ( $product->get_children() as $child_id ) {
			$variation = wc_get_product( $child_id );

			// Hide out of stock variations if 'Hide out of stock items from the catalog' is checked
			if ( ! $variation || ! $variation->exists() || ! $variation->is_in_stock() ) {
				continue;
			}

			// Filter 'woocommerce_hide_invisible_variations' to optionally hide invisible variations (disabled variations and variations with empty price).
			if ( apply_filters( 'woocommerce_hide_invisible_variations', true, $product->get_id(), $variation ) && ! $variation->variation_is_visible() ) {
				continue;
			}

			$available_variation = $product->get_available_variation( $variation );
			$woomelly_stock = 0;
			if ( $variation->managing_stock() ) {
				$woomelly_stock = $variation->get_stock_quantity();
			} else {
				if ( $variation->is_in_stock() ) {
					$woomelly_stock = 1;
				}
			}
			if ( $woomelly_stock < 0 ) {
				$woomelly_stock = 0;
			}
			$available_variation['availability'] = $woomelly_stock;
			$available_variation['length'] = $variation->get_length();
			$available_variation['width'] = $variation->get_width();
			$available_variation['height'] = $variation->get_height();
			$available_variations[] = $available_variation;
		}

		return $available_variations;
	}
} //End wm_get_available_variations()

/**
 * wm_get_name_attribute.
 *
 * @return string
 */
if ( ! function_exists( 'wm_get_name_attribute' ) ) {
	function wm_get_name_attribute ( $text ) {
		$text = substr( $text, 13 );
		return ucfirst( str_replace( "_", " " , $text ) );
	}
} //End wm_get_name_attribute()

/**
 * wm_clean_item_with_sales.
 *
 * @return array
 */
if ( ! function_exists( 'wm_clean_item_with_sales' ) ) {
	function wm_clean_item_with_sales ( $item ) {
		if ( is_array($item) ) {
			if ( isset($item['condition']) ) {
				unset( $item['condition'] );
			}
			if ( isset($item['buying_mode']) ) {
				unset( $item['buying_mode'] );
			}
			if ( isset($item['shipping']['dimensions']) ) {
				unset( $item['shipping']['dimensions'] );
			}
		}

		return $item;
	}
} //End wm_clean_item_with_sales()

/**
 * get_woomelly_diff_time
 *
 * @return 1 | 0 | -1
 */
if ( ! function_exists( 'wm_diff_time' ) ) {
	function wm_diff_time ( $date1, $date2 ) {
		$d1 = new DateTime( $date1 );
		$d2 = new DateTime( $date2 );
		$d2->modify('+6 seconds');
		if ( $d1 == $d2 ) {
			return 0;
		} else if ( $d1 < $d2 ) {
			return -1;
		} else {
			return 1;
		}
	} //End wm_diff_time()
}