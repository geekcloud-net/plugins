<?php

class WPLA_ProductWrapper {
	
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
		$sale_price = get_post_meta( $post_id, '_sale_price', true);
		if ( floatval($sale_price) > 0 ) return $sale_price;
		return get_post_meta( $post_id, '_price', true);
	}	
	static function getOriginalPrice( $post_id ) {
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

        if ( !$product ) {
            WPLA()->logger->debug( 'getStock: Product not found from #'. print_r( $post_id, true ) );
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
	static function decreaseStockBy( $post_id, $by, $order_id = false ) {

		// get WC product
		$product = self::getProduct( $post_id );
		if ( ! $product ) return false;

		// patch backorders product config unless backorders were enabled in settings
		// ...

		// check if stock management is enabled for product
		if ( $product->managing_stock() ) {

            // if yes, call reduce_stock()
            if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
                $stock = $product->reduce_stock( $by );
            } else {
                // decrease the stock manually because there's a critical inconsistency with
                // wc_update_product_stock() where earlier versions doesn't support a 3rd parameter #15465 #15324
                //$stock = wc_update_product_stock( $product_id, $quantity_purchased, 'decrease' );
                $current_stock = $product->get_stock_quantity();
                $stock = $current_stock - $by;
                wc_update_product_stock( $post_id, $stock );
            }

			// stock status notifications
			$notification_sent = false;

			if ( 'yes' == get_option( 'woocommerce_notify_no_stock' ) && get_option( 'woocommerce_notify_no_stock_amount' ) >= $stock ) {
				do_action( 'woocommerce_no_stock', $product );
				$notification_sent = true;
			}

			if ( ! $notification_sent && 'yes' == get_option( 'woocommerce_notify_low_stock' ) && get_option( 'woocommerce_notify_low_stock_amount' ) >= $stock ) {
				do_action( 'woocommerce_low_stock', $product );
			}

		}

		// // check if stock management is enabled for product
		// if ( ! $product->managing_stock() && ! $product->backorders_allowed() ) {		
		// 	// if not, just mark it as out of stock
		// 	update_post_meta($product->id, '_stock_status', 'outofstock');
		// 	$stock = 0;
		// } else {
		// 	// if yes, call reduce_stock()
		// 	$stock = $product->reduce_stock( $by );
		// }

		return $stock;
	}	
	// increase product stock
	static function increaseStockBy( $post_id, $by, $order_id = false ) {
		$product = self::getProduct( $post_id );
		if ( ! $product ) return false;
		
		// check if stock management is enabled for product
		if ( ! $product->managing_stock() ) return;		

		// call increase_stock()
		$stock = $product->increase_stock( $by );
		
		return $stock;
	}	
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

	// get name of main product category
	static function getProductCategoryName( $post_id ) {
		$terms = get_the_terms($post_id, "product_cat");
		if ( ! $terms || ! is_array($terms) ) return '';
		$category_name = $terms[0]->name;
		return $category_name;
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

	/**
	 * get product gallery images - as attachment IDs
	 * @param WC_Product $_product
	 * @return array
	 */
	static function getGalleryAttachmentIDs( $_product ) {

        // if the product is a variation, try to load the gallery from 3rd-party plugins
        $product_id = wpla_get_product_meta( $_product, 'id' );
        if ( $_product->is_type('variation') ) {

            // WooCommerce Additional Variation Images plugin
            if ( class_exists( 'WC_Additional_Variation_Images' ) ) {
                $additional_images = get_post_meta( $product_id, '_wc_additional_variation_images', true );

                if ( $additional_images ) {
                    $gallery_images = explode( ',', $additional_images );
                    return $gallery_images;
                }
            }

            // WooThumbs (iconic)
            if ( class_exists( 'Iconic_WooThumbs' ) ) {
                $additional_images = get_post_meta( $product_id, 'variation_image_gallery', true );

                if ( $additional_images ) {
                    $gallery_images = explode( ',', $additional_images );
                    return $gallery_images;
                }
            }
        }

		// use WooCommerce Product Gallery images
		$gallery_images = is_callable( array( $_product, 'get_gallery_image_ids' ) ) ? $_product->get_gallery_image_ids() : $_product->get_gallery_attachment_ids();
		if ( ! empty($gallery_images) ) return $gallery_images;

        // In WC 3.0+, we need to load the parent product of a variation and get its gallery images #15121
        if ( $_product->is_type( 'variation' ) && version_compare( WC_VERSION, '3.0', '>=' ) ) {
            // get the parent product's gallery
            $parent_id = $_product->get_parent_id();

            if ( $parent_id ) {
                $_parent = self::getProduct( $parent_id );
                $gallery_images = is_callable( array( $_parent, 'get_gallery_image_ids' ) ) ? $_parent->get_gallery_image_ids() : $_parent->get_gallery_attachment_ids();

                if ( ! empty($gallery_images) ) return $gallery_images;
            }
        }

		// if gallery is empty and fallback disabled, return empty array
		if ( get_option( 'wpla_product_gallery_fallback', 'none' ) == 'none' ) return array();

		// if gallery is empty and fallback enabled, use images attached to post ID
		global $wpdb;
    	$attachment_ids = $wpdb->get_col( $wpdb->prepare(" 
			SELECT id
			FROM {$wpdb->prefix}posts
			WHERE post_type = 'attachment' 
			  AND post_parent = %s
			ORDER BY menu_order
		", $product_id ) );
		// WPLA()->logger->info( "getGalleryAttachmentIDs( $_product->id ) : " . print_r($attachment_ids,1) );

		if ( ! is_array($attachment_ids) ) return array();
		return $attachment_ids;
	}	
	
	// get all product attributes
	static function getAttributes( $post_id, $use_label_as_key = false ) {
		$attributes = array();

		$product = self::getProduct( $post_id );
		if ( ! $product ) return array();
		
		$attribute_taxnomies = $product->get_attributes();	
		// WPLA()->logger->info("attribute_taxnomies ($post_id): ".print_r($attribute_taxnomies,1));

		foreach ($attribute_taxnomies as $attribute) {
		    // Fix for warning generated when WC would return attributes that aren't arrays #16821 #16741
            if ( !is_array( $attribute ) && ! is_a( $attribute, 'WC_Product_Attribute' ) ) {
                continue;
            }

			if ( $attribute['is_taxonomy'] ) {

				// handle taxonomy attributes
				$terms = wp_get_post_terms( $post_id, $attribute['name'] );
				// WPLA()->logger->info('terms: '.print_r($terms,1));

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
					if ( ! $use_label_as_key ) $attribute_name = $attribute['name'];
					$attributes[ $attribute_name ] = $terms[0]->name;
				}
	
			} else {

				// handle custom product attributes
				$attribute_name = $attribute['name'];
				$attribute_name = html_entity_decode( $attribute_name, ENT_QUOTES, 'UTF-8' ); // US Shoe Size (Men&#039;s) => US Shoe Size (Men's)
				if ( ! $use_label_as_key ) $attribute_name = $attribute['name'];
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
		if ( $product && wpla_get_product_meta( $product, 'product_type' ) == 'variable' ) return true;

		// $variations = $product->get_available_variations();
		// if ( ! is_array($variations) ) return false;
		// if ( 0 == count($variations) ) return false;

		return false;

	}	

	// get all product addons (requires Product Add-Ons extension)
	static function getAddons( $post_id ) {

		$addons = array();
		// WPLA()->logger->info('getAddons() for post_id '.print_r($post_id,1));

		// check if addons are enabled
		$product_addons = get_post_meta( $post_id, '_product_addons', true );
		if ( ! is_array($product_addons) ) return array();
		if ( 0 == sizeof($product_addons) ) return array();

		// get available addons for prices
		// $available_addons = shopp_product_addons( $post_id );
		// $meta = shopp_product_meta($post_id, 'options');
		// $a = $meta['a'];
		// WPLA()->logger->info('a:'.print_r($a,1));

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
		WPLA()->logger->info('addons:'.print_r($options,1));

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

	// get all product variations
	static function getVariations( $post_id ) {
		global $product; // make $product globally available for some badly coded themes...		

		$product = self::getProduct( $post_id );
		if ( ! $product || wpla_get_product_meta( $product, 'product_type' ) != 'variable' ) return array();

		// force all variations to show, regardless if woocommerce_hide_out_of_stock_items is yes or no
		// by forcing visibility to true - doesn't work with WC2.2 :-(
		add_filter( 'woocommerce_product_is_visible', array( 'WPLA_ProductWrapper', 'returnTrue' ), 999, 2 );
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
		$default_attributes    = is_callable( array( $product, 'get_default_attributes' ) ) ? $product->get_default_attributes() : $product->get_variation_default_attributes();
		$has_default_variation = false;

		// remove filter again
		remove_filter( 'woocommerce_product_is_visible', array( 'WPLA_ProductWrapper', 'returnTrue' ), 999, 2 );
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
				$term = get_term_by('slug', $value, $taxonomy );
				// echo "<pre>key  : ";print_r($key);echo"</pre>";
				// echo "<pre>term : ";print_r($term);echo"</pre>";
				// echo "<pre>value: ";print_r($value);echo"</pre>";

				// try to fetch term by name - required for values like "0" or "000"
				if ( ! $term ) {
					$term = get_term_by('name', $value, $taxonomy );
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
			
			$newvar['price']      = self::getPrice( $var_id );
			$newvar['stock']      = self::getStock( $var_id );
			$newvar['sku']        = self::getSKU( $var_id );
			$newvar['weight']     = self::getWeight( $var_id );
			$newvar['dimensions'] = self::getDimensions( $var_id );

			// check parent if variation has no dimensions
			// if ( ($newvar['dimensions']['length'] == 0) && ($newvar['dimensions']['width'] == 0) ) {
			// 	$newvar['dimensions'] = self::getDimensions( $post_id );
			// }

			$var_image 		  = self::getImageURL( $var_id );
			$newvar['image']  = ($var_image == '') ? self::getImageURL( $post_id ) : $var_image;

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
						$term = get_term_by('slug', $value, $taxonomy );
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

		}


		// if no default variation was found, make the first on default
		if ( ! $has_default_variation && sizeof($variations) ) {
			$variations[0]['is_default'] = true;
		}
			
		// handle attribute merging
		$variations = self::mergeVariationAttributes( $variations );

        // WPLA()->logger->info( 'getVariations() result: '.print_r($variations,1));

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

	static function mergeVariationAttributes( $variations ) {
		$variation_merger_map = get_option( 'wpla_variation_merger_map', array() );
		if ( empty($variation_merger_map) || ! is_array($variation_merger_map) ) return $variations;

		// each variation
		foreach ($variations as & $var) {
			$variation_attributes = $var['variation_attributes'];

			// each merge rule
			foreach ($variation_merger_map as $rule) {
				$woo1 = $rule['woo1'];
				$woo2 = $rule['woo2'];
				$amaz = $rule['amaz'];
				$glue = $rule['glue'];

				// check if rule matches attributes
				if ( isset( $variation_attributes[ $woo1 ] ) &&
					 isset( $variation_attributes[ $woo2 ] ) ) {
					// echo "<pre>";print_r($rule);echo"</pre>";#die();

					// build combined value
					$glue = trim($glue) ? " $glue " : ' ';
					$new_attribute_value = $variation_attributes[ $woo1 ] . $glue . $variation_attributes[ $woo2 ];

					// rewrite attributes
					unset( $var['variation_attributes'][ $woo1 ] );
					unset( $var['variation_attributes'][ $woo2 ] );
					$var['variation_attributes'][ $amaz ] = $new_attribute_value;

				} // if match
					
			} // each rule

		} // each variation

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
			// $attrib->name  = html_entity_decode( self::getAttributeLabel( $taxonomy_name ), ENT_QUOTES, 'UTF-8' );
			$attrib->name  = $taxonomy_name;
			$attrib->label = html_entity_decode( self::getAttributeLabel( $taxonomy_name ), ENT_QUOTES, 'UTF-8' );

			$attributes[]  = $attrib;
		}
		// print_r($attributes);die();

        // WPLA()->logger->info( 'getAttributeTaxonomies() result: '.print_r($attributes,1));

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

	// get post ID of variation parent
	// cache enabled wrapper for fetchVariationParent()
	static function getVariationParent( $post_id ) {
		return WPLA()->memcache->getProductVariationParent( $post_id );
	}	
	
	// get post ID of variation parent (private)
	static function loadVariationParent( $post_id ) {
        $product = self::getProduct( $post_id );

        if ( $product && wpla_get_product_meta( $post_id, 'product_type' ) == 'variation' ) {
            if ( version_compare( WC_VERSION, '3.0', '>=' ) ) {
                return $product->get_parent_id();
            } else {
                return $product->parent->id;
            }
        }

        return false;

        /*if ( ! $post_id ) return false;
        $post = get_post( $post_id );

        if ( empty( $post->post_parent ) || $post->post_parent == $post->ID )
                return false;

        return $post->post_parent;*/
	}	
	
	// find variation by attributes (private)
	static function findVariationID( $parent_id, $VariationSpecifics ) {

		$variations = self::getVariations( $parent_id );
		foreach ($variations as $var) {
			$diffs = array_diff_assoc( $var['variation_attributes'], $VariationSpecifics );
			if ( count($diffs) == 0 ) {
				WPLA()->logger->info('findVariationID('.$parent_id.') found: '.$var['post_id']);
				WPLA()->logger->info('VariationSpecifics: '.print_r($VariationSpecifics,1));
				return $var['post_id'];
			}
		}
		return false;
	}	
	
	// get WooCommerce product object (private)
	static function getProduct( $post_id, $is_variation = false ) {

		// use wc_get_product() on WC 2.1+
		if ( function_exists('wc_get_product') ) {
			return wc_get_product( $post_id );
		// use get_product() on WC 2.0+
		} elseif ( function_exists('get_product') ) {
			return get_product( $post_id );
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
	
	
	// get WooCommerce attribute value
	static function getAttributeValueFromSlug( $taxonomy_name, $value ) {

		$taxonomy = str_replace('attribute_', '', $taxonomy_name); // attribute_pa_color -> pa_color
		$term     = get_term_by('slug', $value, $taxonomy );
		// echo "<pre>key  : ";print_r($taxonomy_name);echo"</pre>";
		// echo "<pre>term : ";print_r($term);echo"</pre>";
		// echo "<pre>value: ";print_r($value);echo"</pre>";

		// try to fetch term by name - required for values like "0" or "000"
		if ( ! $term ) {
			$term = get_term_by('name', $value, $taxonomy );
		}

		if ( $term ) {
			// handle proper attribute taxonomies
			// $value = html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ); // US Shoe Size (Men&#039;s) => US Shoe Size (Men's)
			$value = $term->name;
		}

		return $value;
	} // getAttributeValue()
	
	
}
