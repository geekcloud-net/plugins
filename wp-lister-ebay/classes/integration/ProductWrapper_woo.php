<?php
/**
 * wrapper functions to access products on WooCommerce
 */

class ProductWrapper {
	
	const plugin = 'woo';
	const post_type = 'product';
	const taxonomy  = 'product_cat';
	const menu_page_position = '57.26';
	
	// get custom post type
	static function getPostType() {
		return self::post_type;
	}	
	// get product catrgories taxonomy
	static function getTaxonomy() {
		return self::taxonomy;
	}	
	
	// get product price
	static function getPrice( $post_id ) {
        $disable_sale_price = get_option('wplister_disable_sale_price');

        if ( $disable_sale_price ) {
		    WPLE()->logger->info('disable_sale_price is enabled - returning original price ('. self::getOriginalPrice( $post_id ) .')' );
            return self::getOriginalPrice( $post_id );
        }

		$_product = self::getProduct( $post_id );
		if ( ! $_product ) return get_post_meta( $post_id, '_price', true);

		// $sale_price = get_post_meta( $post_id, '_sale_price', true );
		$sale_price = $_product->get_sale_price();
		if ( floatval($sale_price) > 0 ) {
			$sale_price_dates_from = get_post_meta( $post_id, '_sale_price_dates_from', true );	// check if sale has started already
			if ( ! $sale_price_dates_from ) return $sale_price;									// return sale price if no schedule set
			if ( $sale_price_dates_from < current_time( 'timestamp' ) ) return $sale_price;		// return sale price if schedule has started
		}

		// return get_post_meta( $post_id, '_price', true);
		return $_product->get_price();
	}	
	static function getOriginalPrice( $post_id ) {
		$_product = self::getProduct( $post_id );
		if ( $_product ) return $_product->get_regular_price();
		return get_post_meta( $post_id, '_regular_price', true);
	}	
	
	// set product price
	static function setPrice( $post_id, $price ) {
		update_post_meta( $post_id, '_price', $price);
		update_post_meta( $post_id, '_regular_price', $price);
	}	

	// get product sku
	static function getSKU( $post_id ) {
		return get_post_meta( $post_id, '_sku', true);
	}	
	
	// set product sku
	static function setSKU( $post_id, $sku ) {
		return update_post_meta( $post_id, '_sku', $sku);
	}	

	// get product stock
	static function getStock( $post_id ) {
	    $product = $post_id;
	    if ( !is_object( $product ) ) {
	        $product = wc_get_product( $post_id );
        }

        if ( ! $product ) {
	        return 0;
        }

        $stock = is_callable( array( $product, 'get_stock_quantity' ) ) ? $product->get_stock_quantity() : $product->get_total_stock();
		return $stock;
	}	
	
	// set product stock (deprecated)
	static function setStock( $post_id, $stock ) {
		return update_post_meta( $post_id, '_stock', $stock);
	}	

	## BEGIN PRO ##

	// decrease product stock
	static function decreaseStockBy( $post_id, $quantity_purchased, $order_id, $variation_id = false) {

		// get product
		$product = self::getProduct( $post_id );
		// WPLE()->logger->debug('decreaseStockBy() - product: '.print_r($product,1));

		// or get variation
		if ( $variation_id ) {

			// $variation_id = self::findVariationID( $post_id, $VariationSpecifics, $sku );
			$product = self::getProduct( $variation_id, true );
			WPLE()->logger->debug('decreaseStockBy() - variable product: '.print_r($product,1));

			// add history record
			$om = new EbayOrdersModel();
			$history_message = "Stock reduced by $quantity_purchased for variation #$variation_id";
			$history_details = array( 'variation_id' => $variation_id );
			$success		 = $variation_id ? true : false;
			$om->addHistory( $order_id, 'reduce_stock', $history_message, $history_details, $success );			

		}
		if ( ! $product ) return false;

		$product_id = wple_get_product_meta( $product, 'id' );

		// patch backorders product config unless backorders were enabled in settings
		if ( $product->backorders_allowed() ) {
			if ( get_option( 'wplister_allow_backorders', 0 ) == 1 ) {

				// overwrite backorders status temporarily
                if ( is_callable( array( $product, 'set_backorders' ) ) ) {
                    $product->set_backorders( 'no' );
                } else {
                    $product->backorders = 'no';
                }

				$om = new EbayOrdersModel();
				$history_message = "Note: Product #$product_id has backorders enabled, but this setting was ignored since the 'Ignore backorders' option is active. When the stock level reaches zero, the stock status will be switched to 'out of stock'.";
				$history_details = array( 'post_id' => $product_id );
				$om->addHistory( $order_id, 'backorders_ignored', $history_message, $history_details );							
			} else {
				$om = new EbayOrdersModel();
				$history_message = "Warning: backorders are enabled for product #$product_id";
				$history_details = array( 'post_id' => $product_id );
				$om->addHistory( $order_id, 'backorders_allowed', $history_message, $history_details );							
			}
		}

		// check if stock management is enabled for product
		if ( $product->managing_stock() ) {		

			// if yes, call reduce_stock()
            if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
                $stock = $product->reduce_stock( $quantity_purchased );
            } else {
                // decrease the stock manually because there's a critical inconsistency with
                // wc_update_product_stock() where earlier versions doesn't support a 3rd parameter #15465 #15324
                //$stock = wc_update_product_stock( $product_id, $quantity_purchased, 'decrease' );

                $current_stock = $product->get_stock_quantity();
                $stock = $current_stock - $quantity_purchased;
                wc_update_product_stock( $product_id, $stock );
            }

			// stock status notifications
			$notification_sent = false;

            // pull a new instance of the updated product to get the correct number of stocks left
            // to be used in email notifications #14593
            $updated_product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : ProductWrapper::getProduct( $product_id );

			if ( 'yes' == get_option( 'woocommerce_notify_no_stock' ) && get_option( 'woocommerce_notify_no_stock_amount' ) >= $stock ) {
				do_action( 'woocommerce_no_stock', $updated_product );
				$notification_sent = true;
			}

			if ( ! $notification_sent && 'yes' == get_option( 'woocommerce_notify_low_stock' ) && get_option( 'woocommerce_notify_low_stock_amount' ) >= $stock ) {
				do_action( 'woocommerce_low_stock', $updated_product );
			}

		} else {

			// if not, just mark it as out of stock - or not
			// update_post_meta($product->id, '_stock_status', 'outofstock');
			$stock = false; // 'N/A' should work as well - and look better in the history

			// add history record if reduce_stock() wasn't called for any reason
			$om = new EbayOrdersModel();
			$history_message = "Product #$product_id was skipped - stock management is disabled.";
			$history_details = array( 'post_id' => $product_id );
			$om->addHistory( $order_id, 'not_managing_stock', $history_message, $history_details );							

		}

		// // check if stock management is enabled for product
		// if ( ! $product->managing_stock() && ! $product->backorders_allowed() ) {		
		// 	// if not, just mark it as out of stock
		// 	update_post_meta($product->id, '_stock_status', 'outofstock');
		// 	$stock = 0;

		// 	// add history record if reduce_stock() wasn't called for any reason
		// 	$om = new EbayOrdersModel();
		// 	$history_message = "Product #$post_id was marked as out of stock - stock management is disabled.";
		// 	$history_details = array( 'post_id' => $post_id );
		// 	$om->addHistory( $order_id, 'out_of_stock', $history_message, $history_details );							

		// } else {

		// 	// if yes, call reduce_stock()
		// 	$stock = $product->reduce_stock( $quantity_purchased );

		// }

		return $stock;
	} // decreaseStockBy()

	// increase product stock
	static function increaseStockBy( $post_id, $by, $VariationSpecifics = array(), $sku = false ) {
		if ( count( $VariationSpecifics ) == 0 ) {
			$product = self::getProduct( $post_id );
		} else {
			$variation_id = self::findVariationID( $post_id, $VariationSpecifics, $sku );
			$product = self::getProduct( $variation_id, true );
		}
		if ( ! $product ) return false;
		
		// check if stock management is enabled for product
		if ( ! $product->managing_stock() ) return;		

		// call increase_stock()
		$stock = $product->increase_stock( $by );
		
		return $stock;
	} // increaseStockBy()

	## END PRO ##
	
	// get product weight
	static function getWeight( $post_id, $include_weight_unit = false ) {

		$weight = get_post_meta( $post_id, '_weight', true);

		// check parent if variation has no weight
		if ( $weight == '' ) {
			$parent_id = self::getVariationParent( $post_id );
			if ( $parent_id ) $weight = self::getWeight( $parent_id );
		}

		return $weight;
	}	

	// get product weight as major weight and minor
	static function getEbayWeight( $post_id ) {
		$weight_value = self::getWeight( $post_id );
		$weight_unit  = get_option( 'woocommerce_weight_unit' );

        // No need to continue if weight is empty #20696
        if ( empty( $weight_value ) ) {
            return array( 0, 0 );
        }

		// convert value to major and minor if unit is gram or ounces
		if ( 'g' == $weight_unit ) {
			$kg = intval( $weight_value / 1000 );
			$g = $weight_value - $kg * 1000 ;
			$weight_major = $kg;
			$weight_minor = $g;
		} elseif ( 'kg' == $weight_unit ) {
			$kg = intval( $weight_value );
			$g = ($weight_value - $kg) * 1000 ;
			$weight_major = $kg;
			$weight_minor = $g;
		} elseif ( 'lbs' == $weight_unit ) {
			$lbs = intval( $weight_value );
			$oz = ($weight_value - $lbs) * 16 ;
			$weight_major = $lbs;
			$weight_minor = $oz;
		} elseif ( 'oz' == $weight_unit ) {
			$lbs = intval( $weight_value / 16 );
			$oz = $weight_value - $lbs * 16 ;
			$weight_major = $lbs;
			$weight_minor = $oz;
		} else {
			$weight_major = $weight_value;
			$weight_minor = 0;
		}
		return array( $weight_major, $weight_minor );
	}	

	// get name of main product category
	static function getProductCategoryName( $post_id ) {
		$terms = get_the_terms($post_id, "product_cat");
		if ( ! $terms || ! is_array($terms) ) return '';
		$category_name = $terms[0]->name;
		return $category_name;
	}

	// get the product tags in CSV format
	static function getProductTagsCsv( $post_id ) {
	    $tags = get_the_terms( $post_id, 'product_tag' );

	    if ( ! $tags || ! is_array( $tags ) ) {
	        return '';
        }

        $tag_names = wp_list_pluck( $tags, 'name' );

        if ( ! $tag_names || ! is_array( $tag_names ) ) {
            return '';
        }

        return implode( ', ', $tag_names );
    }
	
	// get product dimensions array
	static function getDimensions( $post_id ) {
		$dimensions = array();
		$unit = get_option( 'woocommerce_dimension_unit' );
		$dimensions['length'] = get_post_meta( $post_id, '_length', true);
		$dimensions['height'] = get_post_meta( $post_id, '_height', true);
		$dimensions['width']  = get_post_meta( $post_id, '_width',  true);
		$dimensions['length_unit'] = $unit;
		$dimensions['height_unit'] = $unit;
		$dimensions['width_unit']  = $unit;

		// unit conversion
		if ( $conversion_mode = get_option( 'wplister_convert_dimensions' ) ) {

			switch ($conversion_mode) {
				case 'mm-cm':
					# convert mm to cm
					$dimensions['length'] = $dimensions['length'] / 10;
					$dimensions['height'] = $dimensions['height'] / 10;
					$dimensions['width']  = $dimensions['width']  / 10;
					$dimensions['length_unit'] = 'cm';
					$dimensions['height_unit'] = 'cm';
					$dimensions['width_unit']  = 'cm';
					break;
				
				case 'in-cm':
					# convert in to cm
					$dimensions['length'] = $dimensions['length'] * 2.54;
					$dimensions['height'] = $dimensions['height'] * 2.54;
					$dimensions['width']  = $dimensions['width']  * 2.54;
					$dimensions['length_unit'] = 'cm';
					$dimensions['height_unit'] = 'cm';
					$dimensions['width_unit']  = 'cm';
					break;
				
				default:
					# code...
					break;
			}

		} // if convert dimensions

		// check parent if variation has no dimensions
		if ( ($dimensions['length'] == '') && ($dimensions['width'] == '') ) {
			$parent_id = self::getVariationParent( $post_id );
			if ( $parent_id ) $dimensions = self::getDimensions( $parent_id );
		}

		return $dimensions;
	}	
	
	// get product featured image
	static function getImageURL( $post_id ) {

		// this seems to be neccessary for listing previews on some installations 
		if ( ! function_exists('get_post_thumbnail_id')) 
		require_once( ABSPATH . 'wp-includes/post-thumbnail-template.php');

		// fetch images using default size
		$size = get_option( 'wplister_default_image_size', 'full' );
		$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $size );
		return $large_image_url[0];
	}	
	
	// get all product attributes
	static function getAttributes( $post_id ) {
		$attributes = array();

		$product = self::getProduct( $post_id );
		if ( ! $product ) return array();
		
		$attribute_taxnomies = $product->get_attributes();
		
		WPLE()->logger->debug('attribute_taxnomies: '.print_r($attribute_taxnomies,1));

		foreach ($attribute_taxnomies as $attribute) {

			if ( $attribute['is_taxonomy'] ) {

				// handle taxonomy attributes
				$terms = wp_get_post_terms( $post_id, $attribute['name'] );
				WPLE()->logger->debug('terms: '.print_r($terms,1));
				if ( is_wp_error($terms) ) {
					// echo "post id: $post_id <br>";
					// echo "attribute name: " . $attribute['name']."<br>";
					// echo "attribute: " . print_r( $attribute )."<br>";
					// echo "error: " . $terms->get_error_message();
					continue;
				}
				if ( count( $terms ) > 0 ) {
					$attribute_name = self::getAttributeLabel( $attribute['name'] );
					$attribute_name = html_entity_decode( $attribute_name, ENT_QUOTES, 'UTF-8' ); // US Shoe Size (Men&#039;s) => US Shoe Size (Men's)
					// $attributes[ $attribute_name ] = $terms[0]->name;

					// support for multi value attributes
					$values = array();
					foreach ($terms as $term) {
						$values[] = $term->name;
					}
                    $attributes[ $attribute_name ] = join( '|', apply_filters( 'wple_product_attribute_values', $values, $attribute_name, $attributes ) );
				}
	
			} else {

				// handle custom product attributes
				$attribute_name = $attribute['name'];
				$attribute_name = html_entity_decode( $attribute_name, ENT_QUOTES, 'UTF-8' ); // US Shoe Size (Men&#039;s) => US Shoe Size (Men's)
				$attributes[ $attribute_name ] = $attribute['value'];

			}

		}

		return $attributes;
		// Array
		// (
		//     [Platform] => Nintendo DS
		//     [Genre] => Puzzle
		// )
	}	
	
	// check if product is a single variation (that has been split)
	// static function isVariation( $post_id ) {

	// 	$product = self::getProduct( $post_id );
	// 	if ( $product->product_type == 'variation' ) return true;

	// 	return false;

	// }	
	// get parent post_id for a single variation
	// static function getParentID( $post_id ) {

	// 	$product = self::getProduct( $post_id );
	// 	if ( $product->product_type == 'variation' ) return $product->post->parent_id;

	// 	return false;

	// }	

	// check if product has variations
	static function hasVariations( $post_id ) {

		$product = self::getProduct( $post_id );
		if ( $product && wple_get_product_meta( $post_id, 'product_type' ) == 'variable' ) return true;

		// $variations = $product->get_available_variations();
		// if ( ! is_array($variations) ) return false;
		// if ( 0 == count($variations) ) return false;

		return false;

	}	

	// get all product addons (requires Product Add-Ons extension)
	static function getAddons( $post_id ) {

		$addons = array();
		// WPLE()->logger->info('getAddons() for post_id '.print_r($post_id,1));

		// check if addons are enabled
		$product_addons = get_post_meta( $post_id, '_product_addons', true );
		if ( ! is_array($product_addons) ) return array();
		if ( 0 == sizeof($product_addons) ) return array();

		// get available addons for prices
		// $available_addons = shopp_product_addons( $post_id );
		// $meta = shopp_product_meta($post_id, 'options');
		// $a = $meta['a'];
		// WPLE()->logger->info('a:'.print_r($a,1));

		// build clean options array
		$options = array();
		foreach ( $product_addons as $product_addon ) {
			$addonGroup = new stdClass();
			$addonGroup->name    = $product_addon['name'];
			$addonGroup->options = array();

			foreach ( $product_addon['options'] as $option ) {
				$addonObj = new stdClass();
				$addonObj->id    = sanitize_key( $option['label'] );
				$addonObj->name  = $option['label'];
				$addonObj->price = $option['price'];				

				$addonGroup->options[] = $addonObj;
			}
			$options[] = $addonGroup;
		}
		WPLE()->logger->info('addons:'.print_r($options,1));

		return $options;
	}

	// sort variation attributes according to _product_attributes post meta field
	static function sortVariationAttributes( $variation_attributes, $_product_attributes ) {
		if ( empty($_product_attributes) ) return $variation_attributes;

		$attributes = array();
		foreach ( $_product_attributes as $term_key => $product_attribute ) {
			if ( isset( $variation_attributes['attribute_'.$term_key] ) ) {
				$attributes['attribute_'.$term_key] = $variation_attributes['attribute_'.$term_key];
			}
		}

		return $attributes;
	} // sortVariationAttributes()

    /**
     * Get all product variations
     * @param int  $post_id
     * @param bool $short_result
     * @param null $account_id Provide a WPLE account ID to translate variation attributes where applicable
     *
     * @return array
     */
	static function getVariations( $post_id, $short_result = false, $account_id = null ) {
		global $product; // make $product globally available for some badly coded themes...		
		WPLE()->logger->startTimer('getVariations');

		$product = self::getProduct( $post_id );
		if ( ! $product || wple_get_product_meta( $post_id, 'product_type' ) != 'variable' ) return array();

		// force all variations to show, regardless if woocommerce_hide_out_of_stock_items is yes or no
		// by forcing visibility to true - doesn't work with WC2.2 :-(
		add_filter( 'woocommerce_product_is_visible', array( 'ProductWrapper', 'returnTrue' ), 999, 2 );
		// this works for WC2.2 as well:
		// TODO: implement an alternative get_available_variations() method for better performance
		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			update_option( 'woocommerce_hide_out_of_stock_items', 'no' );
			$reenable_woocommerce_hide_out_of_stock_items = true;
		}

		// fix bug in woocommerce-woowaitlist (codecanyon version)
		if ( class_exists('Woocommerce_Waitlist') ) remove_all_filters( 'woocommerce_get_availability' );

		$available_variations  = $product->get_available_variations();
		$variation_attributes  = $product->get_variation_attributes();
        $default_attributes     = is_callable( array( $product, 'get_default_attributes' ) ) ? $product->get_default_attributes() : $product->get_variation_default_attributes();
		$has_default_variation = false;

		// remove filter again
		remove_filter( 'woocommerce_product_is_visible', array( 'ProductWrapper', 'returnTrue' ), 999, 2 );
		// reset wc option
		if ( isset( $reenable_woocommerce_hide_out_of_stock_items ) ) {
			update_option( 'woocommerce_hide_out_of_stock_items', 'yes' );
		}


		// echo "<pre>default_attributes: ";print_r($default_attributes);echo"</pre>";
		// echo "<pre>available_variations: ";print_r($available_variations);echo"</pre>";
		// echo "<pre>variation_attributes: ";print_r($variation_attributes);echo"</pre>";
		// (
		//     [pa_size] => Array
		//         (
		//             [0] => x-large
		//             [1] => large
		//             [2] => medium
		//             [3] => small
		//         )

		//     [pa_colour] => Array
		//         (
		//             [0] => yellow
		//             [1] => orange
		//         )

		// ) 

		// build array of attribute labels
		$attribute_labels = array();
		foreach ( $variation_attributes as $name => $options ) {

			$label = self::getAttributeLabel($name); 
			if ($label == '') $label = $name;
			$label = html_entity_decode( $label, ENT_QUOTES, 'UTF-8' ); // US Shoe Size (Men&#039;s) => US Shoe Size (Men's)

            // attempt to translate
            if ( function_exists( 'qtranxf_use' ) && $account_id ) {
                $lang = WPLE_eBayAccount::getAccountLocale( $account_id );

                $label = qtranxf_use( $lang, $label );
                $name = qtranxf_use( $lang, $name );
            }
			
			$id   = "attribute_".sanitize_title($name);
			$attribute_labels[ $id ] = $label;

		} // foreach $variation_attributes

		// echo "<pre>attribute_labels: ";print_r($attribute_labels);echo"</pre>";#die();
		// (
		//     [attribute_pa_size] => Size
		//     [attribute_pa_colour] => Colour
		// )		

		// loop variations
		$variations = array();
		foreach ($available_variations as $var) {
			
			// find child post_id for this variation
			$var_id = $var['variation_id'];

			// ignore hidden variations
			if ( get_post_meta( $var_id, '_ebay_is_disabled', true ) == 'on' ) continue;

			// build variation array for wp-lister
			$newvar = array();
			$newvar['post_id'] = $var_id;
			// $newvar['term_id'] = $var->term_id;

			// sort variation attributes according to _product_attributes
			if ( sizeof( $var['attributes'] ) > 1 ) {
				$_product_attributes = (array) maybe_unserialize( get_post_meta( $post_id, '_product_attributes', true ) );
				$var['attributes']   = self::sortVariationAttributes( $var['attributes'], $_product_attributes );
			}		
			
			$attributes = $var['attributes'];
			$newvar['variation_attributes'] = array();
			$attributes_without_values = array();
			foreach ($attributes as $key => $value) {	// this loop will only run once for one dimensional variations
				// $newvar['name'] = $value; #deprecated
				// v2
				$taxonomy = str_replace('attribute_', '', $key); // attribute_pa_color -> pa_color
				// $term = get_term_by('slug', $value, $taxonomy );
				$term = WPLE()->memcache->getTermBy( 'slug', $value, $taxonomy );
				// echo "<pre>key  : ";print_r($key);echo"</pre>";
				// echo "<pre>term : ";print_r($term);echo"</pre>";
				// echo "<pre>value: ";print_r($value);echo"</pre>";

				// try to fetch term by name - required for values like "0" or "000"
				if ( ! $term ) {
					$term = WPLE()->memcache->getTermBy( 'name', $value, $taxonomy );
				}

				// get attribute label
				$attribute_label = isset( $attribute_labels[ $key ] ) ? $attribute_labels[ $key ] : false;
				if ( ! $attribute_label ) continue;

				if ( $term ) {
					// handle proper attribute taxonomies
					$term_name = html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ); // US Shoe Size (Men&#039;s) => US Shoe Size (Men's)
					$newvar['variation_attributes'][ @$attribute_labels[ $key ] ] = $term_name;
					$value = $term->slug;
				} elseif ( isset( $variation_attributes[ $attribute_label ] ) ) {
					// handle fake custom product attributes with custom values red|green|blue
					$custom_value = $value;
					foreach ($variation_attributes[ $attribute_label ] as $custom_name ) {
						if ( $value == sanitize_title($custom_name) ) $custom_value = $custom_name;
					}
					$newvar['variation_attributes'][ @$attribute_labels[ $key ] ] = $custom_value;
					// echo "no term* found for $key<br>";
					// echo "no term* found for $value<br>";
				} elseif ( $value ) {
					// handle fake custom product attributes
					$newvar['variation_attributes'][ @$attribute_labels[ $key ] ] = $value;
					// echo "no term found for $key<br>";
					// echo "no term found for $value<br>";
				} elseif ( isset( $attribute_labels[ $key ] ) && ( $attribute_labels[ $key ] != '' ) ) {
					// handle product attributes without value ("all Colors")
					$newvar['variation_attributes'][ @$attribute_labels[ $key ] ] = '_ALL_';
					$attributes_without_values[] = $key;
					// echo "no value found for $key<br>";
				}

				// check for default variation
				if ( isset( $default_attributes[ $taxonomy ] ) && $default_attributes[ $taxonomy ] == $value ) {
					$newvar['is_default']  = true;
					$has_default_variation = true;
				} else {
					$newvar['is_default']  = false;
				}

			}
			// $newvar['group_name'] = $attribute_labels[ $key ]; #deprecated

			// skip excluded variation attribute values
			$exclude_variation_values = get_option( 'wplister_exclude_variation_values' );
			if ( ! empty( $exclude_variation_values ) ) {

				$excluded_values = explode( ',', $exclude_variation_values );
				$skip_variation  = false;

				foreach( $newvar['variation_attributes'] as $attribute_value ) {
					if ( in_array( $attribute_value, $excluded_values ) ) $skip_variation = true;
				}
				if ( $skip_variation ) continue;
			}	


			$newvar['price']      = self::getPrice( $var_id );
			$newvar['stock']      = self::getStock( $var_id );
			$newvar['sku']        = self::getSKU( $var_id );

			// omit some data in short result for increased performance on listings page
			if ( ! $short_result ) {

				// image thumbnail
				$var_image 		  = self::getImageURL( $var_id );
				$newvar['image']  = ($var_image == '') ? self::getImageURL( $post_id ) : $var_image;

				// weight / dimensions
				$newvar['weight']     = self::getWeight( $var_id );
				$newvar['dimensions'] = self::getDimensions( $var_id );
	
				// ebay weight
				list( $weight_major, $weight_minor ) = self::getEbayWeight( $var_id );
				$newvar['weight_major']     = $weight_major;
				$newvar['weight_minor']     = $weight_minor;

			}

			// regard custom eBay price for (locked) parent variation
			if ( $ebay_start_price = get_post_meta( $post_id, '_ebay_start_price', true ) ) {
				$newvar['price'] = $ebay_start_price;
			}
			// regard custom eBay price for (locked) child variation
			if ( $ebay_start_price = get_post_meta( $var_id, '_ebay_start_price', true ) ) {
				$newvar['price'] = $ebay_start_price;
			}


			// do we have some attributes without values that need post-processing?
			if ( sizeof($attributes_without_values) > 0 ) {

				// echo "<pre>";print_r($attributes_without_values);echo"</pre>";die();
				foreach ($attributes_without_values as $key) {	

					// v2
					$taxonomy = str_replace('attribute_', '', $key); // attribute_pa_color -> pa_color

					$all_values = $variation_attributes[ $taxonomy ];
					// echo "<pre>all values for $taxonomy: ";print_r($all_values);echo"</pre>";#die();

					// create a new variation for each value
					if ( is_array( $all_values ) )
					foreach ($all_values as $value) {
						$term = WPLE()->memcache->getTermBy( 'slug', $value, $taxonomy );
						// echo "<pre>";print_r($term);echo"</pre>";#die();
	
						if ( $term ) {
							// handle proper attribute taxonomies
							$term_name = html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ); // US Shoe Size (Men&#039;s) => US Shoe Size (Men's)
							$newvar['variation_attributes'][ @$attribute_labels[ $key ] ] = $term_name;
							$variations[] = $newvar;			
						}

					}

				}

			} else {

				// add single variation to collection
				$variations[] = $newvar;			
				// echo "<pre>";print_r($newvar);echo"</pre>";die();

			}
		
		} // foreach $available_variations
		

		// if no default variation was found, make the first one default
		if ( ! $has_default_variation && sizeof($variations) ) {
			$variations[0]['is_default'] = true;
		}

        // WPLE()->logger->info( 'getVariations() result: '.print_r($variations,1));
		WPLE()->logger->endTimer('getVariations');

		return $variations;

		// echo "<pre>";print_r($variations);die();echo"</pre>";

		/* the returned array looks like this:
		    
		    [0] => Array
		        (
		            [post_id] => 1126
					[variation_attributes] => Array
	                (
	                    [Size] => large
	                    [Colour] => yellow
	                )
		            [price] => 
		            [stock] => 
		            [weight] => 
		            [sku] => 
		            [is_default] => true
		            [image] => http://www.example.com/wp-content/uploads/2011/09/days-end.jpg
		        )

		    [1] => Array
		        (
		            [post_id] => 1253
					[variation_attributes] => Array
	                (
	                    [Size] => large
	                    [Colour] => orange
	                )
		            [price] => 
		            [stock] => 
		            [weight] => 
		            [sku] => 
		            [is_default] => false
		            [image] => http://www.example.com/wp-content/uploads/2011/09/days-end.jpg
		        )

		*/

	}

	/**
	 * Get all product variations for the listings table. This method will be merged with the getVariations method eventually
	 * @param int $post_id
	 * @return array
	 */
	public static function getListingVariations( $post_id ) {
		global $product; // make $product globally available for some badly coded themes...
		WPLE()->logger->startTimer('getListingVariations');

		$product = self::getProduct( $post_id );
		if ( ! $product ||  wple_get_product_meta( $post_id, 'product_type' ) != 'variable' ) {
			return array();
		}

		// force all variations to show, regardless if woocommerce_hide_out_of_stock_items is yes or no
		// by forcing visibility to true - doesn't work with WC2.2 :-(
		add_filter( 'woocommerce_product_is_visible', array( 'ProductWrapper', 'returnTrue' ), 999, 2 );
		// this works for WC2.2 as well:
		// TODO: implement an alternative get_available_variations() method for better performance
		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			update_option( 'woocommerce_hide_out_of_stock_items', 'no' );
			$reenable_woocommerce_hide_out_of_stock_items = true;
		}

		// fix bug in woocommerce-woowaitlist (codecanyon version)
		if ( class_exists('Woocommerce_Waitlist') ) {
			remove_all_filters( 'woocommerce_get_availability' );
		}

		$children               = $product->get_children();
		$variation_attributes   = $product->get_variation_attributes();
		$default_attributes     = is_callable( array( $product, 'get_default_attributes' ) ) ? $product->get_default_attributes() : $product->get_variation_default_attributes();
		$has_default_variation  = false;

		// remove filter again
		remove_filter( 'woocommerce_product_is_visible', array( 'ProductWrapper', 'returnTrue' ), 999, 2 );
		// reset wc option
		if ( isset( $reenable_woocommerce_hide_out_of_stock_items ) ) {
			update_option( 'woocommerce_hide_out_of_stock_items', 'yes' );
		}

		// build array of attribute labels
		$attribute_labels = array();
		foreach ( $variation_attributes as $name => $options ) {

			$label = self::getAttributeLabel($name);

			if ($label == '') {
				$label = $name;
			}
			$label = html_entity_decode( $label, ENT_QUOTES, 'UTF-8' ); // US Shoe Size (Men&#039;s) => US Shoe Size (Men's)

			$id   = "attribute_".sanitize_title($name);
			$attribute_labels[ $id ] = $label;

		} // foreach $variation_attributes

		// loop variations
		$variations = array();

		foreach ( $children as $child_id ) {
			$var = array();

			// find child post_id for this variation
			$var_id = $child_id;
			$var['attributes'] = self::getVariationAttributes( $var_id );

			// ignore hidden variations
			if ( get_post_meta( $var_id, '_ebay_is_disabled', true ) == 'on' ) {
				continue;
			}

			// build variation array for wp-lister
			$newvar = array();
			$newvar['post_id'] = $var_id;

			// sort variation attributes according to _product_attributes
			if ( sizeof( $var['attributes'] ) > 1 ) {
				$_product_attributes = (array) maybe_unserialize( get_post_meta( $post_id, '_product_attributes', true ) );
				$var['attributes']   = self::sortVariationAttributes( $var['attributes'], $_product_attributes );
			}

			$attributes = $var['attributes'];
			$newvar['variation_attributes'] = array();
			$attributes_without_values = array();
			foreach ($attributes as $key => $value) {	// this loop will only run once for one dimensional variations
				// v2
				$taxonomy = str_replace('attribute_', '', $key); // attribute_pa_color -> pa_color
				$term = WPLE()->memcache->getTermBy( 'slug', $value, $taxonomy );

				// try to fetch term by name - required for values like "0" or "000"
				if ( ! $term ) {
					$term = WPLE()->memcache->getTermBy( 'name', $value, $taxonomy );
				}

				// get attribute label
				$attribute_label = isset( $attribute_labels[ $key ] ) ? $attribute_labels[ $key ] : false;
				if ( ! $attribute_label ) continue;

				if ( $term ) {
					// handle proper attribute taxonomies
					$term_name = html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ); // US Shoe Size (Men&#039;s) => US Shoe Size (Men's)
					$newvar['variation_attributes'][ @$attribute_labels[ $key ] ] = $term_name;
					$value = $term->slug;
				} elseif ( isset( $variation_attributes[ $attribute_label ] ) ) {
					// handle fake custom product attributes with custom values red|green|blue
					$custom_value = $value;
					foreach ($variation_attributes[ $attribute_label ] as $custom_name ) {
						if ( $value == sanitize_title($custom_name) ) $custom_value = $custom_name;
					}
					$newvar['variation_attributes'][ @$attribute_labels[ $key ] ] = $custom_value;
				} elseif ( $value ) {
					// handle fake custom product attributes
					$newvar['variation_attributes'][ @$attribute_labels[ $key ] ] = $value;
				} elseif ( isset( $attribute_labels[ $key ] ) && ( $attribute_labels[ $key ] != '' ) ) {
					// handle product attributes without value ("all Colors")
					$newvar['variation_attributes'][ @$attribute_labels[ $key ] ] = '_ALL_';
					$attributes_without_values[] = $key;
				}

				// check for default variation
				if ( isset( $default_attributes[ $taxonomy ] ) && $default_attributes[ $taxonomy ] == $value ) {
					$newvar['is_default']  = true;
					$has_default_variation = true;
				} else {
					$newvar['is_default']  = false;
				}

			}

			// skip excluded variation attribute values
			$exclude_variation_values = get_option( 'wplister_exclude_variation_values' );
			if ( ! empty( $exclude_variation_values ) ) {

				$excluded_values = explode( ',', $exclude_variation_values );
				$skip_variation  = false;

				foreach( $newvar['variation_attributes'] as $attribute_value ) {
					if ( in_array( $attribute_value, $excluded_values ) ) $skip_variation = true;
				}
				if ( $skip_variation ) continue;
			}

			$newvar['price']      = self::getPrice( $var_id );
			$newvar['stock']      = self::getStock( $var_id );
			$newvar['sku']        = self::getSKU( $var_id );

			// regard custom eBay price for (locked) parent variation
			if ( $ebay_start_price = get_post_meta( $post_id, '_ebay_start_price', true ) ) {
				$newvar['price'] = $ebay_start_price;
			}
			// regard custom eBay price for (locked) child variation
			if ( $ebay_start_price = get_post_meta( $var_id, '_ebay_start_price', true ) ) {
				$newvar['price'] = $ebay_start_price;
			}

			// do we have some attributes without values that need post-processing?
			if ( sizeof($attributes_without_values) > 0 ) {
				foreach ($attributes_without_values as $key) {
					// v2
					$taxonomy = str_replace('attribute_', '', $key); // attribute_pa_color -> pa_color
					$all_values = $variation_attributes[ $taxonomy ];

					// create a new variation for each value
					if ( is_array( $all_values ) ) {
						foreach ($all_values as $value) {
							$term = WPLE()->memcache->getTermBy( 'slug', $value, $taxonomy );

							if ( $term ) {
								// handle proper attribute taxonomies
								$term_name = html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ); // US Shoe Size (Men&#039;s) => US Shoe Size (Men's)
								$newvar['variation_attributes'][ @$attribute_labels[ $key ] ] = $term_name;
								$variations[] = $newvar;
							}
						}
					}
				}
			} else {
				// add single variation to collection
				$variations[] = $newvar;
			}
		} // foreach $available_variations


		// if no default variation was found, make the first one default
		if ( ! $has_default_variation && sizeof($variations) ) {
			$variations[0]['is_default'] = true;
		}

		WPLE()->logger->endTimer('getListingVariations');

		return $variations;
	}

	static function returnTrue( $param1, $param2 = false ) {
		return true;
	}

	// get a list of all available attribute names
	static function getAttributeTaxonomies() {
		global $woocommerce;

		if ( function_exists('wc_get_attribute_taxonomy_names') ) {
			$attribute_taxonomies = wc_get_attribute_taxonomy_names();	// WC2.2+
		} else {
			$attribute_taxonomies = $woocommerce->get_attribute_taxonomy_names(); // legacy support for WC2.0
		}
		// print_r($attribute_taxonomies);
		
		$attributes = array();
		foreach ( $attribute_taxonomies as $taxonomy_name ) {
			$attrib = new stdClass();

			// US Shoe Size (Men&#039;s) => US Shoe Size (Men's)
			$attrib->name  = html_entity_decode( self::getAttributeLabel( $taxonomy_name ), ENT_QUOTES, 'UTF-8' );
			$attrib->label = $attrib->name;

			$attributes[]  = $attrib;
		}
		// print_r($attributes);die();

        // WPLE()->logger->info( 'getAttributeTaxonomies() result: '.print_r($attributes,1));

		return $attributes;
	}	

	// check if current page is products list page
	static function isProductsPage() {
		global $pagenow;

		if ( ( isset( $_GET['post_type'] ) ) &&
		     ( $_GET['post_type'] == self::getPostType() ) &&
			 ( $pagenow == 'edit.php' ) ) {
			return true;
		}
		return false;
	}	

	// check if product is single variation
	static function isSingleVariation( $post_id ) {
        return self::getVariationParent( $post_id ) ? true : false;
	}	
	

	/*
	 * private functions (WooCommerce only)
	 */

	// check if product is single variation
	static function getVariationParent( $post_id ) {

		$product = ProductWrapper::getProduct( $post_id );
		if ( $product && wple_get_product_meta( $post_id, 'product_type' ) == 'variation' ) {
		    if ( version_compare( WC_VERSION, '3.0', '>=' ) ) {
                return $product->get_parent_id();
            } else {
                return $product->parent->id;
            }
        }

		return false;

		// // the code below isn't specific enough - it treats grouped child products as variations as well:
        // if ( ! $post_id ) return false;
        // $post = get_post( $post_id );

        // if ( empty( $post->post_parent ) || $post->post_parent == $post->ID )
        //         return false;

        // return $post->post_parent;
	}	
	
	// find variation by attributes (private)
	static function findVariationID( $parent_id, $VariationSpecifics, $sku ) {

		$variations = self::getVariations( $parent_id );
		WPLE()->logger->info('findVariationID('.$parent_id.','.$sku.') checking '.count($variations).' variations...');

		// search variations for matching attributes
		foreach ($variations as $var) {
			$diffs = array_diff_assoc( $var['variation_attributes'], $VariationSpecifics );
			if ( count($diffs) == 0 ) {
				WPLE()->logger->info('findVariationID('.$parent_id.') found: '.$var['post_id']);
				WPLE()->logger->info('VariationSpecifics: '.print_r($VariationSpecifics,1));
				return $var['post_id'];
			}
		}

		// fall back to search for SKU
		if ( $sku ) {	
			foreach ($variations as $var) {
				if ( $sku == $var['sku'] ) {
					WPLE()->logger->info('findVariationID('.$parent_id.','.$sku.') found SKU match: '.$var['post_id']);
					return $var['post_id'];				
				}
			}
		}

		// if still nothing found, try a more fuzzy (case insensitive) search for attributes
		// (this will find the right variation when "Size" has been imported as "size" for example)
		foreach ($variations as $var) {
			$diffs = array_udiff( $var['variation_attributes'], $VariationSpecifics, 'strcasecmp' );
			if ( count($diffs) == 0 ) {
				WPLE()->logger->info('findVariationID('.$parent_id.') found fuzzy match: '.$var['post_id']);
				WPLE()->logger->info('VariationSpecifics: '.print_r($VariationSpecifics,1));
				return $var['post_id'];
			}
		}

		WPLE()->logger->info('findVariationID('.$parent_id.','.$sku.') found nothing...');
		return false;
	}	
	
	// get WooCommerce product object
	static function getProduct( $post_id, $is_variation = false ) {

		// use get_product() on WC 2.0+
        if ( function_exists('wc_get_product') || function_exists('get_product') ) {
			// return get_product( $post_id );
			return WPLE()->memcache->getProductObject( $post_id );
		} else {
			// instantiate WC_Product on WC 1.x
			return $is_variation ? new WC_Product_Variation( $post_id ) : new WC_Product( $post_id );
		}

	}	
	
	// get WooCommerce product title
	static function getProductTitle( $post_id ) {

		$product    = self::getProduct( $post_id );
		if ( ! $product ) return 'PRODUCT_MISSING';

		return $product->get_title();
	}	
	
	// get WooCommerce attribute name (private)
	static function getAttributeLabel( $name ) {

		// use get_product() on WC 2.1+
		if ( function_exists('wc_attribute_label') ) {
			return wc_attribute_label( $name );
		} else {
			// use WC 2.0 method
			global $woocommerce;
			return $woocommerce->attribute_label( $name );
		}

	}

	/**
	 * Get attibutes/data for an individual variation from the database and maintain it's integrity.
	 * @param  int $variation_id
	 * @return array
	 */
	public static function getVariationAttributes( $variation_id ) {
		if ( function_exists('wc_get_variation_product_attributes' ) ) {
			return wc_get_product_variation_attributes( $variation_id );
		}

		// Build variation data from meta
		$all_meta                = get_post_meta( $variation_id );
		$parent_id               = wp_get_post_parent_id( $variation_id );
		$parent_attributes       = array_filter( (array) get_post_meta( $parent_id, '_product_attributes', true ) );
		$found_parent_attributes = array();
		$variation_attributes    = array();

		// Compare to parent variable product attributes and ensure they match
		foreach ( $parent_attributes as $attribute_name => $options ) {
			$attribute                 = 'attribute_' . sanitize_title( $attribute_name );
			$found_parent_attributes[] = $attribute;
			if ( ! empty( $options['is_variation'] ) && ! array_key_exists( $attribute, $variation_attributes ) ) {
				$variation_attributes[ $attribute ] = ''; // Add it - 'any' will be asumed
			}
		}

		// Get the variation attributes from meta
		foreach ( $all_meta as $name => $value ) {
			// Only look at valid attribute meta, and also compare variation level attributes and remove any which do not exist at parent level
			if ( 0 !== strpos( $name, 'attribute_' ) || ! in_array( $name, $found_parent_attributes ) ) {
				unset( $variation_attributes[ $name ] );
				continue;
			}
			/**
			 * Pre 2.4 handling where 'slugs' were saved instead of the full text attribute.
			 * Attempt to get full version of the text attribute from the parent.
			 */
			if ( sanitize_title( $value[0] ) === $value[0] && version_compare( get_post_meta( $parent_id, '_product_version', true ), '2.4.0', '<' ) ) {
				foreach ( $parent_attributes as $attribute ) {
					if ( $name !== 'attribute_' . sanitize_title( $attribute['name'] ) ) {
						continue;
					}
					$text_attributes = array_map( 'trim', explode( WC_DELIMITER, html_entity_decode( $attribute['value'], ENT_QUOTES, get_bloginfo( 'charset' ) ) ) );

					foreach ( $text_attributes as $text_attribute ) {
						if ( sanitize_title( $text_attribute ) === $value[0] ) {
							$value[0] = $text_attribute;
							break;
						}
					}
				}
			}

			$variation_attributes[ $name ] = $value[0];
		}

		return $variation_attributes;
	}
	
	
	
}


