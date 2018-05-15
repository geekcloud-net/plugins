<?php

class WPLA_ProductBuilder {

	var $images_url;
	var $updated_count;
	var $images_hashmap = array();


	public function deleteProductsFromPreviousImport() {
		global $wpdb;

		$items = $wpdb->get_results(
			"
			SELECT post_id
			FROM {$wpdb->prefix}postmeta
			WHERE meta_key = '_amazon_item_source'
			"
		);

		if ( $items )
			foreach ( $items as $item ) {
				$this->deleteProduct( $item->post_id );
				WPLA()->logger->debug( 'deleting product from previous import: ' . $item->post_id );
			}
		return count( $items );

	} // deleteProductsFromPreviousImport()

	static public function deleteProduct( $id ) {
		global $wpdb;

		$result = $wpdb->get_results(
			"
			DELETE FROM {$wpdb->prefix}postmeta
			WHERE post_id = '$id'
			"
		);

		$result = $wpdb->get_results(
			"
			DELETE FROM {$wpdb->prefix}posts
			WHERE ID = '$id'
			"
		);

	} // deleteProduct()

	public function clearAttributeCache() {
	    delete_transient( 'wc_attribute_taxonomies' );
	    delete_transient( 'wc_term_counts' );
	}


	// deprecated?
	public function updateProducts( $products ) {

		foreach ( $products as $item ) {

			//if ($cat['parent_id']==0) # only top level for now
			$data = $this->mapAmazonListingToWoo( $item );
			$this->updateProduct( $data );

		} // foreach product

	} // updateProducts()


	public function importSingleProduct( $item, $product_node ) {
		$variation_type = is_string( $product_node->variation_type ) ? $product_node->variation_type : '_none_'; // convert empty object to string
		WPLA()->logger->info( "* importSingleProduct() - SKU ".$item['sku'] .' - type: '.$variation_type );

		$lm = new WPLA_ListingsModel();

		// // check if product already exists by ASIN (disabled)
		// $this->last_insert_id = $this->getProductIdByOriginalId( $item['asin'] );
		// if ( $this->last_insert_id ) WPLA()->logger->info('found existing product by ASIN '.$item['asin'].' - post_id: '.$this->last_insert_id );

		// if ( ! $this->last_insert_id ) {

		// 	$this->last_insert_id = self::getProductIdBySKU( $item['sku'] );
		// 	if ( $this->last_insert_id ) {
		// 		update_post_meta( $this->last_insert_id, '_wpla_asin', $item['asin'] );
		// 		WPLA()->logger->info('found existing product by SKU '.$item['sku'].'- post_id: '.$this->last_insert_id );	
		// 	} 

		// }


		// check if product already exists - by SKU
		$this->last_insert_id = self::getProductIdBySKU( $item['sku'] );

		// if a product exists, update ASIN
		if ( $this->last_insert_id ) {
			update_post_meta( $this->last_insert_id, '_wpla_asin', $item['asin'] );
			WPLA()->logger->info('Found existing product by SKU '.$item['sku'].' - post_id: '.$this->last_insert_id );	
		} 


		// if no product exists, import			
		if ( ! $this->last_insert_id ) {

			$data = $this->mapAmazonListingToWoo( $item, $product_node );
			$this->last_insert_id = $this->addProduct( $data );
			if ( ! $this->last_insert_id ) return; // failed to create product, leave in import queue

			// store invalid variation error message with created product
			if ( '_invalid_parent_' == $variation_type ) {
				update_post_meta( $this->last_insert_id, '_wpla_import_message', $product_node->variation_msg );
			}

		} else {

			// if a parent variation was found, import missing child variations
			if ( 'parent' == $product_node->variation_type ) {

				$data    = $this->mapAmazonListingToWoo( $item, $product_node );
				$post_id = $this->last_insert_id;

				// add each variation
				foreach ( $data['variations'] as $variation ) {

					// skip existing SKUs					
					if ( $variation_id = self::getProductIdBySKU( $variation->sku ) ) {
						WPLA()->logger->info('skipped existing variation '.$variation->sku.' - parent_id: '.$post_id );	
						continue;
					}

					// create child variation
					$this->addVariation( $post_id, $variation, $data );
					WPLA()->logger->info('ADDED missing variation '.$variation->sku.' - parent_id: '.$post_id );	

					// Update parent if variable so price sorting works and stays in sync with the cheapest child
					WC_Product_Variable::sync( $post_id );
					WC_Product_Variable::sync_stock_status( $post_id );
				}

			} // is parent variation

		} // product exists


		// update listing with new post_id
		$lm->updateListing( $item['id'], array(
			'post_id' => $this->last_insert_id, 
			'status'  => $item['source'] == 'imported' ? 'online' : 'matched' // foreign imports are matched at first
		) );

	} // importSingleProduct()



	// public function importProducts( $products ) {
	// 	foreach ( $products as $product ) {
	// 		$this->importSingleProduct( $product );
	// 	}
	// } // importProducts()







	public function mapAmazonListingToWoo( $listing, $product_node = null ) {
		// echo "<pre>listing data: ";print_r($listing);echo"</pre>";#die();

		// Woo Product 		   		    # Amazon Listing
		$data['asin']                   = $listing['asin'];
		$data['name']                   = $listing['listing_title'];
		$data['description']            = $listing['description'];
		$data['image']                  = @$listing['attributes']->SmallImage->URL;
		$data['weight']                 = @$listing['attributes']->ItemDimensions->Weight;
		$data['quantity']               = $listing['quantity'];
		$data['price']                  = $listing['price'];
		$data['sku']                    = $listing['sku'];
		$data['additional_description'] = '';
		$data['products_date_added']    = $listing['date_published'];
		$data['categories']             = array();
		$data['variations']             = array();
		$data['attributes']             = array();
		$data['dimensions']             = false;
		$data['images']                 = isset( $listing['additional_images'] ) ? json_decode( $listing['additional_images'] ) : array();
		$data['_meta_fields']           = array();
		$data['condition_type']         = '';
		$data['condition_note']         = '';

		// get item condition from report details
		$report_row = json_decode( $listing['details'], true );
		// echo "<pre>";print_r($report_row);echo"</pre>";#die();
		if ( is_array($report_row) && isset( $report_row['item-condition'] ) ) {

			$amazon_condition_type  = WPLA_ImportHelper::convertNumericConditionIdToType( $report_row['item-condition'] );
			$data['condition_type'] = $amazon_condition_type;
			$data['condition_note'] = $report_row['item-note'];

		}

		// use PackageDimensions if ItemDimensions are not set
		if ( ! $data['weight'] ) {
			$data['weight']             = @$listing['attributes']->PackageDimensions->Weight;
		}

		// dimensions
		if ( @$listing['attributes']->ItemDimensions ) {
			$data['dimensions'] = array(
				'width'  => @$listing['attributes']->ItemDimensions->Width,
				'length' => @$listing['attributes']->ItemDimensions->Length,
				'height' => @$listing['attributes']->ItemDimensions->Height,
			);
		} else {
			$data['dimensions'] = array(
				'width'  => @$listing['attributes']->PackageDimensions->Width,
				'length' => @$listing['attributes']->PackageDimensions->Length,
				'height' => @$listing['attributes']->PackageDimensions->Height,
			);			
		}

		// default values 
		$data['post_status']  			= 'publish';

		// not used
		$data['special_price']          = '';
		$data['weight_unit']            = 'kilogram';

		// get 600px image instead of 75px
		$data['image'] = str_replace('_SL75_', '_SL600_', $data['image'] );


		// fetch additional / highres product images
		$webHelper = new WPLA_AmazonWebHelper();
		$webHelper->loadListingDetails( $listing['id'] );
		$product_images = $webHelper->getImages();

		if ( ! empty( $product_images ) ) {
			
			// $data['image']  = array_shift( $product_images );
			$data['image']  = $product_images[0];

			// regard option to import only main image
			if ( get_option('wpla_enable_gallery_images_import',1) == 1 ) {
				$data['images'] = $product_images;
			}
		}


		// parse bullet points
		if ( isset( $listing['attributes']->Feature ) && is_array( $listing['attributes']->Feature ) ) {

			// add post meta fields
			$bp_index = 1;
			foreach ( $listing['attributes']->Feature as $feature ) {
				$data['_meta_fields']['_amazon_bullet_point'.$bp_index] = $feature;
				$bp_index++;
			}

			// add list of features to description
			$featuresHtml = '<ul class="amazon_features">'."\n";
			foreach ( $listing['attributes']->Feature as $feature ) {
				$featuresHtml .= "\t".'<li>'.$feature.'</li>'."\n";
			}
			$featuresHtml .= '</ul>'."\n";
			$data['description'] .= "\n".$featuresHtml;
	
		}

		// add condition info to description
		if ( $data['condition_note'] ) {

			$condition_type = wpla_spacify( $data['condition_type'] );
			$conditionHtml  = '<div class="amazon_condition">';
			$conditionHtml .= '<span class="amazon_condition_label">';
			$conditionHtml .= __('Item Condition','wpla') . ': ';
			$conditionHtml .= '</span>';
			$conditionHtml .= $condition_type . '. '.$data['condition_note'];
			$conditionHtml .= '</div>' . "\n";
			$conditionHtml = apply_filters( 'wpla_filter_imported_condition_html', $conditionHtml, $data );
			$data['description'] .= "\n" . $conditionHtml;
	
		}

		// parse Book specific attributes like author, binding and date published
		if ( isset( $listing['attributes']->ProductGroup ) && in_array( $listing['attributes']->ProductGroup, array('Book','Libro') ) ) {

			if ( isset( $listing['attributes']->Publisher ) ) {
				$attrib                            = new stdClass();
				$attrib->name                      = 'Publisher';
				$attrib->value                     = is_array( $listing['attributes']->Publisher ) ? join('|', $listing['attributes']->Publisher) : $listing['attributes']->Publisher;
				$data['attributes'][$attrib->name] = $attrib;
			}

			if ( isset( $listing['attributes']->Binding ) ) {
				$attrib                            = new stdClass();
				$attrib->name                      = 'Binding';
				$attrib->value                     = $listing['attributes']->Binding;
				$data['attributes'][$attrib->name] = $attrib;
			}

			if ( isset( $listing['attributes']->Edition ) ) {
				$attrib                            = new stdClass();
				$attrib->name                      = 'Edition';
				$attrib->value                     = $listing['attributes']->Edition;
				$data['attributes'][$attrib->name] = $attrib;
			}

			if ( isset( $listing['attributes']->PublicationDate ) ) {
				$attrib                            = new stdClass();
				$attrib->name                      = 'Publication date';
				$attrib->value                     = $listing['attributes']->PublicationDate;
				$data['attributes'][$attrib->name] = $attrib;
			}

			if ( isset( $listing['attributes']->NumberOfPages ) ) {
				$attrib                            = new stdClass();
				$attrib->name                      = 'Number of pages';
				$attrib->value                     = $listing['attributes']->NumberOfPages;
				$data['attributes'][$attrib->name] = $attrib;
			}

			if ( isset( $listing['attributes']->Creator ) ) {
				$attrib                            = new stdClass();
				$attrib->name                      = 'Author';
				$attrib->value                     = is_array( $listing['attributes']->Creator ) ? join('|', $listing['attributes']->Creator) : $listing['attributes']->Creator;
				$data['attributes'][$attrib->name] = $attrib;
			}

			if ( isset( $listing['attributes']->Author ) ) {
				$attrib                            = new stdClass();
				$attrib->name                      = 'Author';
				$attrib->value                     = is_array( $listing['attributes']->Author  ) ? join('|', $listing['attributes']->Author ) : $listing['attributes']->Author;
				$data['attributes'][$attrib->name] = $attrib;
			}

		}

		// include variations from product node
		if ( $product_node ) {
			if ( isset( $product_node->variations ) && is_array( $product_node->variations ) ) {
				$data['variations'] = $product_node->variations;

				// fetch highres product images
				foreach ($data['variations'] as &$var) {
					if ( ! $var->listing_id ) continue;

					$webHelper->loadListingDetails( $var->listing_id );
					$var_images = $webHelper->getImages();

					if ( ! empty( $var_images ) ) {
						$var->variation_image  = $var_images[0];
						WPLA()->logger->info( "found HighRes var image: ".$var->variation_image);
					}

				} // each var

			}
		}

		// allow other plugins to modify $data
		$data = apply_filters( 'wpla_filter_imported_product_data', $data, $listing, $report_row );

		return $data;

	} // mapAmazonListingToWoo()


	/**
	 * addProduct, adds a new wpsc_product
	 *
	 * @param unknown $products_name
	 * @return $post_id
	 */
	public function addProduct( $data ) {
		WPLA()->logger->info( "==============================================================================" );
		WPLA()->logger->info( "addProduct() - data: ".print_r($data,1) );

		// some shortcuts
		// $products_id  = 0;
		$asin   		= $data['asin'];
		$products_name  = $data['name'];

		$status = esc_attr( $data['post_status'] );

		// set creation date
		$post_date_gmt = date_i18n( 'Y-m-d H:i:s', strtotime($data['products_date_added']), true );
		$post_date     = date_i18n( 'Y-m-d H:i:s', strtotime($data['products_date_added']), false );

		// handle description
		$store_description = get_option('wpla_import_store_description', 'content');

		if ( ( $store_description == 'content' ) || ( $store_description == 'both' ) ) {
			$post_content = $data['description'];
		} else {
			$post_content = '';
		}

		if ( ( $store_description == 'excerpt' ) || ( $store_description == 'both' ) ) {
			$post_excerpt = $data['description'];
		} else {
			$post_excerpt = $data['additional_description'];
		}

		// Create post object
		$post_data = array(
			'post_title'     => esc_attr( trim( strip_tags( $data['name'] ) ) ),
			'post_content'   => $post_content ? $post_content : '',
			'post_excerpt'   => $post_excerpt ? $post_excerpt : '',
			'post_type'      => 'product',
			'post_date' 	 => $post_date, //The time post was made.
			'post_date_gmt'  => $post_date_gmt, //The time post was made, in GMT.
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_status'    => 'publish'
		);

		// Insert the post into the database
		$post_id = wp_insert_post( $post_data, $wp_error = true );
		if ( ! $post_id || is_wp_error( $post_id ) ) {
			// echo "Failed to create product: ".$post_id->get_error_message();
			wpla_show_message( "Failed to create product: ".$post_id->get_error_message(), 'error' );
	        WPLA()->logger->error( "Failed to create product: ".$post_id->get_error_message() );
			return false;
		}


		// mark product as imported
		update_post_meta( $post_id, '_amazon_item_source', 'imported' );
		update_post_meta( $post_id, '_wpla_asin', $asin );


		// ______ PRICE ______
		$regular_price = str_replace( '$', '', $data['price'] );

		// variations parent has no price
		if ( count($data['variations']) > 0 ) $regular_price = '';

		update_post_meta( $post_id, '_regular_price', $regular_price );
		$sale_price = $data['special_price'];
		if ( intval( $sale_price ) != 0 && $sale_price != $regular_price ) {
			update_post_meta( $post_id, '_price', $sale_price );
			update_post_meta( $post_id, '_sale_price', $sale_price );
		} else {
			update_post_meta( $post_id, '_price', $regular_price );
			update_post_meta( $post_id, '_sale_price', '' );
		}
		// ______________________________


		// ______ INVENTORY ______
		$stock = $data['quantity'];
		if ( $stock != '' ) {
			$manage_stock = 'yes';
			$backorders = 'no';
			if ( (int)$stock > 0 ) {
				$stock_status = 'instock';
			} else {
				$stock_status = 'outofstock';
			}
		} else {
			// $manage_stock = 'no';
			// $backorders = 'yes';
			// $stock_status = 'instock';

			// backorders should not be allowed for imported products
			// and stock managments should always be enabled
			$manage_stock = 'yes';
			$backorders = 'no';
			$stock_status = 'outofstock';
		}

		// parent variations should better be instock
		if ( count($data['variations']) > 0 ) $stock_status = 'instock';

		// stock qty
		update_post_meta( $post_id, '_stock', $stock );
		// stock status
		update_post_meta( $post_id, '_stock_status', $stock_status );
		// manage stock
		update_post_meta( $post_id, '_manage_stock', $manage_stock );
		// backorders
		update_post_meta( $post_id, '_backorders', $backorders );

		// create total_sales field - required for order by popularity feature
		update_post_meta( $post_id, 'total_sales', isset( $data['quantity_sold'] ) ? intval( $data['quantity_sold'] ) : 0 );

		// ______________________________


		// ______ PRODUCT TYPE AND VISIBILITY ______
		// setting all products to simple		
		$product_type = 'simple';

		// except for variations
		if ( count($data['variations']) > 0 ) $product_type = 'variable';

		wp_set_object_terms( $post_id, $product_type, 'product_type' );

		// if ( $stock_status == 'instock' ) {
		// 	$visibility = 'visible';
		// }else {
		// 	$visibility = 'hidden';
		// }
		// visibility
		// make sure that imported products are always visible
		// to hide out of stock products, the WooCommerce setting option should be used
		$visibility = 'visible'; 
		update_post_meta( $post_id, '_visibility', $visibility );
		// ______________________________


		// ______ OTHER PRODUCT DATA ______
		// sku code
		$sku = $data['sku'];
		update_post_meta( $post_id, '_sku', $sku );

		// tax status
		$tax_status = 'taxable';
		update_post_meta( $post_id, '_tax_status', $tax_status );
		// // tax class empty sets it to stndard
		$tax_class = '';
		update_post_meta( $post_id, '_tax_class', $tax_class );

		// weight
		$weight = $data['weight'];
		update_post_meta( $post_id, '_weight', $weight );

		// dimensions
		$dimensions = $data['dimensions'];
		if ( $dimensions && is_array($dimensions) ) {
			$width  = $dimensions['width'];
			$height = $dimensions['height'];
			$length = $dimensions['length'];
		} else {
			$width  = '';
			$height = '';
			$length = '';
		}
		update_post_meta( $post_id, '_width',  $width  );
		update_post_meta( $post_id, '_height', $height );
		update_post_meta( $post_id, '_length', $length );

		/* woocommerce option update, weight unit and dimentions unit */
		// if( $count == 1 ){
		if ( ! get_option( 'wpla_disable_unit_conversion', 0 ) ) {
			
			// amazon stores weight unit and dimentions on a per product basis
			// as i expect most shops will use the same values for all products we can just take a single product
			// and just use those values for the global values used store wide in woocommerce
           
			// $weight_unit = $_wpsc_product_metadata['weight_unit'];
			// $dimentions_unit = $dimensions['height_unit'];
			$weight_unit = 'pound';
			$dimentions_unit = 'in';

			if ( $weight_unit == "pound" || $weight_unit == "ounce" || $weight_unit == "gram" ) {
				$weight_unit = "lbs";
			}else {
				$weight_unit = "kg";
			}
			if ( $dimentions_unit == "cm" || $dimentions_unit == "meter" ) {
				$dimentions_unit = "cm";
			}else {
				$dimentions_unit = "in";
			}
			update_option( 'woocommerce_weight_unit', $weight_unit );
			update_option( 'woocommerce_dimension_unit', $dimentions_unit );
		}


		// featured?
		// if (in_array($post_id, $featured_products)) {
		if ( false ) {
			$featured = 'yes';
		}else {
			$featured = 'no';
		}
		update_post_meta( $post_id, '_featured', $featured );
		// ______________________________


		// add custom meta fields (like bullet points)
		if ( isset( $data['_meta_fields'] ) && is_array( $data['_meta_fields'] ) ) {
			foreach ( $data['_meta_fields'] as $meta_key => $meta_value ) {
				update_post_meta( $post_id, $meta_key, $meta_value );
			}
		}

		// set amazon item condition
		if ( isset( $data['condition_type'] ) && ! empty( $data['condition_type'] ) ) {
			update_post_meta( $post_id, '_amazon_condition_type', $data['condition_type'] );
			update_post_meta( $post_id, '_amazon_condition_note', $data['condition_note'] );
		}

		// assign global import parent category
		$term_id = get_option('wpla_import_parent_category_id' );
		if ( $term_id ) {
			WPLA()->logger->info( "Adding default category {$term_id} to product id {$post_id}" );
			$result = wp_set_object_terms( $post_id, intval( $term_id ), 'product_cat', true );
		}

		// assign product categories (not used yet)
		foreach ( $data['categories'] as $category_id ) {
			$term_id = $this->getCategoryIdByOriginalId( $category_id );
			if ( $term_id ) {
				WPLA()->logger->info( "Adding category {$term_id} ({$category_id}) to productid {$post_id}" );
				$result = wp_set_object_terms( $post_id, intval( $term_id ), 'product_cat', true );
				WPLA()->logger->info( "wp_set_object_terms( $post_id, $term_id, 'product_cat' )" );
				WPLA()->logger->info( "wp_set_object_terms result:".print_r($result,1) );			
			} else {
				WPLA()->logger->error( "failed to find match for store category {$category_id} - productid {$post_id}" );				
			}
		}


		// add product attributes
		foreach ( $data['attributes'] as $attrib ) {

			if ( $this->isTextAttribute( $attrib->name ) ) {

				// add 'text' attribute
				WPLA()->logger->info( "adding TEXT attribute: ".$attrib->name );			
				$attribute_name = $this->addAttribute( $attrib->name, false );
				if ($attribute_name) $this->addProductAttribute( $post_id, $attribute_name, $attrib->value, $attrib->name );

			} else {

				// add 'select' attribute
				WPLA()->logger->info( "adding SELECT attribute: ".$attrib->name );			
				$attribute_name = $this->addAttribute( $attrib->name, true );
				if ($attribute_name) {
					$attribute_values = explode( '|', $attrib->value );
					foreach ( $attribute_values as $value) {
						$this->addVariationAttribute( $post_id, $attribute_name, $value );	
					}
				} 

			}
		}


		// download and attach product image
		$image_attachment_ids = array();
		$attachment_id = $this->addProductImage( $post_id, $data['image'], $data['name'], $data['asin'] );
		if ( $attachment_id ) $image_attachment_ids[] = $attachment_id;
		if ( count($data['images']) > 1 ) {
			for ($i=1; $i < count($data['images']) ; $i++) { 

				// check if safe import mode is enabled
				if ( get_option('wpla_import_safe_import_mode', 0) == 0 ) {
	
					# add additional image - without setting it as featured image
					$suffix = $i + 1;
					$attachment_id = $this->addProductImage( $post_id, $data['images'][$i], $data['name'], $data['asin'], false, $suffix );
					if ( $attachment_id ) $image_attachment_ids[] = $attachment_id;

				} else {

					// safe mode - add additional images to sub tasks
					if ( ! isset( $this->subtasks ) ) $this->subtasks = array();
					$title = 'Processing additional image '.$i.' / '. ( count($data['images']) - 1 );
					$subtask = array( 
						'task'        => 'importProductImage', 
						'displayName' => $title, 
						'post_id'     => $post_id,
						'image'       => $data['images'][$i],
						'name'        => $data['name'],
						'asin'     => $data['asin'],
						'counter'     => $i
					);
					$this->subtasks[] = $subtask;

					// add first image to Product Gallery (safe mode on)
					update_post_meta( $post_id, '_product_image_gallery', $image_attachment_ids[0] );
					
				}

			}
		}

		// add WooCommerce 2.0 Product Gallery - with safe mode off
		if ( get_option('wpla_import_safe_import_mode', 0) == 0 ) {
			if ( sizeof( $image_attachment_ids ) > 1 ) {
				update_post_meta( $post_id, '_product_image_gallery', implode( ',', $image_attachment_ids ) );
			}
		}


		// add variations
		if ( ( is_array($data['variations']) ) && ( sizeof($data['variations']) > 0 ) ) {

			// variations are not supposed to have a stock at the product level
			update_post_meta( $post_id, '_stock', '' );

			// add each variation
			foreach ( $data['variations'] as $variation ) {
				$this->addVariation( $post_id, $variation, $data );
			}

			// Update parent if variable so price sorting works and stays in sync with the cheapest child
			WC_Product_Variable::sync( $post_id );
			WC_Product_Variable::sync_stock_status( $post_id );
		}

		WPLA()->logger->info( "added product $post_id ($asin): $products_name " );

		// fire action to allow 3rd-party to add meta and attributes to the newly created product #14097
		do_action( 'wpla_added_product_from_listing', $post_id, $data );

		return $post_id;
	} // addProduct()



	function addVariation( $post_id, $variation, $data ) {
		global $woocommerce, $wpdb;

		WPLA()->logger->info( "--------------------------------" );
		WPLA()->logger->info( "addVariation for {$post_id} " );
		WPLA()->logger->info( "variation attributes: ".print_r($variation->attributes,1) );
		// WPLA()->logger->info( "variation attributes: ".print_r($variation->attributes[0]->value,1) );
		// echo "<pre>VAR: ";print_r($variation);echo"</pre>";#die();

		// $variable_post_id 			= $_POST['variable_post_id'];
		// $variable_sku 				= $_POST['variable_sku'];
		// $variable_stock 			= $_POST['variable_stock'];
		// $variable_price 			= $_POST['variable_price'];

		// $attributes = (array) maybe_unserialize( get_post_meta($post_id, '_product_attributes', true) );
		$attributes = $variation->attributes;

		// $max_loop = max( array_keys( $_POST['variable_post_id'] ) );
		// for ( $i=0; $i <= $max_loop; $i++ ) :
		// if ( ! isset( $variable_post_id[$i] ) ) continue;

		// $variation_id = (int) $variable_post_id[$i];

		// TODO: check for existing variation by SKU!
		$variation_id = false;

		// Generate a useful post title
		$variation_post_title = sprintf(__('Variation %s of %s', 'wpla'), $variation->sku, get_the_title($post_id));

		// Update or Add post
		if (!$variation_id) :

			$variation_post = array(
				'post_title' => $variation_post_title,
				'post_content' => '',
				'post_status' => 'publish',
				'post_author' => get_current_user_id(),
				'post_parent' => $post_id,
				'post_type' => 'product_variation',
				'menu_order' => ''
			);
			$variation_id = wp_insert_post( $variation_post );

		else :

			$wpdb->update( $wpdb->posts, array( 'post_status' => $post_status, 'post_title' => $variation_post_title, 'menu_order' => $variable_menu_order[$i] ), array( 'ID' => $variation_id ) );

		endif;

		// mark product as imported
		update_post_meta( $variation_id, '_amazon_item_source', 'imported' );
		update_post_meta( $variation_id, '_wpla_asin', $variation->asin );

		// Update post meta
		update_post_meta( $variation_id, '_stock', $variation->qty );
		update_post_meta( $variation_id, '_price', $variation->price );
		update_post_meta( $variation_id, '_regular_price', $variation->price );	// WC2.2
		update_post_meta( $variation_id, '_sale_price', '' );
		WPLA()->logger->info( "set price for variation {$variation_id} / {$post_id} to ".$variation->price );

		update_post_meta( $variation_id, '_sku', $variation->sku );
		update_post_meta( $variation_id, '_weight', $data['weight'] );
		// update_post_meta( $variation_id, '_length', $data['length'] );
		// update_post_meta( $variation_id, '_width', $data['width'] );
		// update_post_meta( $variation_id, '_height', $data['height'] );

		update_post_meta( $variation_id, '_thumbnail_id', '' );
		update_post_meta( $variation_id, '_virtual', 'no' );
		update_post_meta( $variation_id, '_downloadable', 'no' );
		update_post_meta( $variation_id, '_download_limit', '' );
		update_post_meta( $variation_id, '_file_path', '' );
        update_post_meta( $variation_id, '_manage_stock', 'yes' );    // WC2.2
        update_post_meta( $variation_id, '_backorders', 'no' );
		update_post_meta( $variation_id, '_stock_status', $variation->qty > 0 ? 'instock' : 'outofstock' );

		// Remove old taxonomies attributes so data is kept up to date
		if ($variation_id) $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key LIKE 'attribute_%' AND post_id = $variation_id;");

		// Update taxonomies
		foreach ($attributes as $attribute) {
			// $value = esc_attr(trim($_POST[ 'attribute_' . sanitize_title($attribute['name']) ][$i]));
			// update_post_meta( $variation_id, 'attribute_' . sanitize_title($attribute['name']), $value );

			// create the attribute if it does not exist
			$attribute_name = $this->addAttribute( $attribute->name, true );
			
			// create and assign new term
			$term_taxonomy_id = $this->addVariationAttribute( $variation_id, $attribute_name, $attribute->value, $post_id );

			$attribute_value = esc_attr( $attribute->value );
			// $attribute_value = htmlspecialchars( $attribute->value );

			// update product meta
 			// $term = get_term_by( 'id', $term_id, $attribute_name);
 			$term = get_term_by( 'term_taxonomy_id', $term_taxonomy_id, $attribute_name);

 			// fall back to old behaviour
 			if ( ! $term ) {
				WPLA()->logger->info( "fallback - search for attribute value {$attribute->value} by name..." );
	 			$term = get_term_by( 'name', $attribute_value, $attribute_name);
 			}

 			// add attribute to variation
			if ( $term ) {

				WPLA()->logger->info( "attribute_$attribute_name: ".$term->slug );
				update_post_meta( $variation_id, 'attribute_' . sanitize_title($attribute_name), $term->slug );
				WPLA()->logger->info( "added attribute {$attribute_name} / {$attribute->value} to variation {$variation_id}" );

				// Update default attribute options setting
				$value = esc_attr(trim( @$term->slug ));
				if ($value) :
					$default_attributes[ sanitize_title($attribute_name) ] = $value;
					WPLA()->logger->info( "-- added default attribute {$attribute_name} / {$attribute->value} to parent product {$post_id}" );
				endif;

			} else {
				WPLA()->logger->error( "could not find attribute term for {$attribute_name} : '{$attribute_value}'" );				
				WPLA()->logger->error( "get_term_by() returned: ".print_r($term,1) );				
			}

		}

		// endfor;


		// Update parent if variable so price sorting works and stays in sync with the cheapest child
		$post_parent = $post_id;

		$children = get_posts( array(
			'post_parent' 	=> $post_parent,
			'posts_per_page'=> -1,
			'post_type' 	=> 'product_variation',
			'fields' 		=> 'ids',
			'post_status'	=> 'publish'
		));

		$lowest_price = $lowest_regular_price = $lowest_sale_price = $highest_price = $highest_regular_price = $highest_sale_price = '';

		if ($children) {
			foreach ($children as $child) {

				$child_price 		= get_post_meta($child, '_price', true);
				$child_sale_price 	= get_post_meta($child, '_sale_price', true);

				// Low price
				if (!is_numeric($lowest_regular_price) || $child_price < $lowest_regular_price) $lowest_regular_price = $child_price;
				if ($child_sale_price!=='' && (!is_numeric($lowest_sale_price) || $child_sale_price < $lowest_sale_price)) $lowest_sale_price = $child_sale_price;

				// High price
				if (!is_numeric($highest_regular_price) || $child_price > $highest_regular_price) $highest_regular_price = $child_price;
				if ($child_sale_price!=='' && (!is_numeric($highest_sale_price) || $child_sale_price > $highest_sale_price)) $highest_sale_price = $child_sale_price;
			}

	    	$lowest_price = ($lowest_sale_price==='' || $lowest_regular_price < $lowest_sale_price) ? $lowest_regular_price : $lowest_sale_price;
			$highest_price = ($highest_sale_price==='' || $highest_regular_price > $highest_sale_price) ? $highest_regular_price : $highest_sale_price;
		}

		update_post_meta( $post_parent, '_price', $lowest_price );
		update_post_meta( $post_parent, '_min_variation_price', $lowest_price );
		update_post_meta( $post_parent, '_max_variation_price', $highest_price );
		update_post_meta( $post_parent, '_min_variation_regular_price', $lowest_regular_price );
		update_post_meta( $post_parent, '_max_variation_regular_price', $highest_regular_price );
		update_post_meta( $post_parent, '_min_variation_sale_price', $lowest_sale_price );
		update_post_meta( $post_parent, '_max_variation_sale_price', $highest_sale_price );

		// // Update default attribute options setting
		$default_attributes = array();

		// foreach ($attributes as $attribute) :
		// 	if ( $attribute['is_variation'] ) :
		// 		$value = esc_attr(trim($_POST[ 'default_attribute_' . sanitize_title($attribute['name']) ]));
		// 		if ($value) :
		// 			$default_attributes[sanitize_title($attribute['name'])] = $value;
		// 		endif;
		// 	endif;
		// endforeach;

		update_post_meta( $post_parent, '_default_attributes', $default_attributes );


		// handle variation image
		if ( $variation->variation_image ) {

			# add feature image to product variation
			$suffix = $variation_id;
			$attachment_id = $this->addProductImage( $variation_id, $variation->variation_image, $variation_post_title, $data['asin'], true, $suffix );

			if ( get_option( 'wpla_variation_image_to_gallery', 1 ) ) {
                // add to WooCommerce 2.0 Product Gallery of parent product - unless attachment_id already exists
                $image_attachment_ids = explode(',', get_post_meta( $post_parent, '_product_image_gallery', true ) );
                if ( $attachment_id && ! in_array( $attachment_id, $image_attachment_ids ) ) {
                    $image_attachment_ids[] = $attachment_id;
                    update_post_meta( $post_parent, '_product_image_gallery', implode( ',', $image_attachment_ids ) );
                    WPLA()->logger->info( "added GALLERY image $attachment_id for variation $variation_id / parent product {$post_parent}" );
                }
            }

		}


	} // addVariation


	public function addVariationAttribute( $post_id, $attribute_name, $attribute_value, $parent_id = false ) {
		global $wpdb;
		global $woocommerce;

		// $attribute_value = array( $val );
		// $attribute_value = $attrib->value;
		$attribute_value = htmlspecialchars( $attribute_value );

		$is_variation = $parent_id ? true : false;
		if ( ! $parent_id ) $parent_id = $post_id;

		// Save Attributes
		$attributes = array();
		$attributes = get_post_meta( $parent_id, '_product_attributes', true );

		$is_visible = true;
		// $is_variation = true;
		$is_taxonomy = true;
		$attribute_position = 2;

 		// Update post terms
 		if ( taxonomy_exists( $attribute_name ) ) {
 			// $term = get_term_by( 'name', $attribute_value, $attribute_name);
			// WPLA()->logger->info( "term object:".print_r($term,1) );
 			// $result = wp_set_object_terms( $parent_id, $term->term_id, $attribute_name, true );
 			$result = wp_set_object_terms( $parent_id, $attribute_value, $attribute_name, true );
			WPLA()->logger->info( "wp_set_object_terms( $parent_id, $attribute_value, $attribute_name )" );
			
			// wp_set_object_terms() returns the term_taxonomy_id(s) as an array if successful
			$term_taxonomy_id = false;
			if ( is_array($result) ) {
				$term_taxonomy_id = $result[0];	
				WPLA()->logger->info( "term_taxonomy_id: ".print_r($term_taxonomy_id,1) );
			} else {
				WPLA()->logger->info( "wp_set_object_terms result:".print_r($result,1) );
			}

 		} else {
			WPLA()->logger->error( "taxonomy {$attribute_name} does not exist! ( addVariationAttribute() )" );
			WPLA()->logger->info( "attribute_value:".print_r($attribute_value,1) );
 		}

 		// Add attribute to array, but don't set values
 		$attributes[ sanitize_title( $attribute_name ) ] = array(
	 		'name' 			=> htmlspecialchars(stripslashes($attribute_name)),
	 		'value' 		=> '',
	 		'position' 		=> $attribute_position,
	 		'is_visible' 	=> $is_visible,
	 		'is_variation' 	=> $is_variation,
	 		'is_taxonomy' 	=> $is_taxonomy
	 	);
		update_post_meta( $parent_id, '_product_attributes', $attributes );

		// WPLA()->logger->info( "added attribute {$attribute_name} / {$attribute_value} to variation $post_id ($parent_id)" );

		return $term_taxonomy_id;
	} // addVariationAttribute

	public function addProductAttribute( $post_id, $attribute_name, $attribute_value, $attribute_label ) {
		global $wpdb;
		global $woocommerce;

		// $attribute_value = array( $val );
		// $attribute_value = $attrib->value;

		// Save Attributes
		$attributes = array();
		$attributes = get_post_meta( $post_id, '_product_attributes', true );


		$is_visible         = true;
		$is_variation       = false;
		$is_taxonomy        = false;
		$attribute_position = 0;

		if ( $is_taxonomy ) {

			if ( isset( $attribute_value ) ) {

		 		// Format values
		 		if ( is_array( $attribute_value ) ) {
			 		$values = array_map('htmlspecialchars', array_map('stripslashes', $attribute_value));
			 	} else {
			 		$values = htmlspecialchars(stripslashes($attribute_value));
			 		// Text based, separate by pipe
			 		$values = explode('|', $values);
			 		$values = array_map('trim', $values);
			 	}

			 	// Remove empty items in the array
			 	$values = array_filter( $values );

		 	} else {
		 		$values = array();
		 	}

	 		// Update post terms
	 		if ( taxonomy_exists( $attribute_name ) ) {
	 			wp_set_object_terms( $post_id, $values, $attribute_name );
	 		} else {
				WPLA()->logger->error( "taxonomy {$attribute_name} does not exist!" );
	 		}

	 		if ( $values ) {
		 		// Add attribute to array, but don't set values
		 		$attributes[ sanitize_title( $attribute_name ) ] = array(
			 		'name' 			=> htmlspecialchars(stripslashes($attribute_label)),
			 		'value' 		=> '',
			 		'position' 		=> $attribute_position,
			 		'is_visible' 	=> $is_visible,
			 		'is_variation' 	=> $is_variation,
			 		'is_taxonomy' 	=> $is_taxonomy
			 	);
		 	}

	 	} else {

	 		if ( ! $attribute_value ) {
				WPLA()->logger->info( "skipped attribute with empty value: {$attribute_name} / productid {$post_id}" );
				return;
	 		}

	 		// Format values
	 		$values = esc_html(stripslashes($attribute_value));

	 		// Text based, separate by pipe
	 		$values = explode('|', $values);
	 		$values = array_map('trim', $values);
	 		$values = implode('|', $values);

	 		// Custom attribute - Add attribute to array and set the values
		 	$attributes[ sanitize_title( $attribute_name ) ] = array(
		 		'name' 			=> htmlspecialchars(stripslashes($attribute_label)),
		 		'value' 		=> $values,
		 		'position' 		=> $attribute_position[$i],
		 		'is_visible' 	=> $is_visible,
		 		'is_variation' 	=> $is_variation,
		 		'is_taxonomy' 	=> $is_taxonomy
		 	);

	 	} // $is_taxonomy


		// if (!function_exists('attributes_cmp')) {
		// 	function attributes_cmp($a, $b) {
		// 	    if ($a['position'] == $b['position']) return 0;
		// 	    return ($a['position'] < $b['position']) ? -1 : 1;
		// 	}
		// }
		// uasort($attributes, 'attributes_cmp');

		update_post_meta( $post_id, '_product_attributes', $attributes );

	
		WPLA()->logger->info( "added attribute {$attribute_name} / {$attribute_value} to productid {$post_id}" );

	} // addProductAttribute()



	public function addAttribute( $attribute_label, $for_variation = false ) {
		global $wpdb;

		$attribute_name 	= sanitize_title( esc_attr( $attribute_label ) );
		$attribute_label 	= esc_attr( $attribute_label );
		$attribute_type 	= 'text';
		if ( $for_variation ) $attribute_type = 'select'; 

		if ( ! $attribute_label )
			$attribute_label = ucwords( $attribute_name );

		// if ( ! $attribute_name )
		// 	$attribute_name = sanitize_title( $attribute_label );

		if ( $attribute_name && strlen( $attribute_name ) < 30 && $attribute_type ) {

			WPLA()->logger->info( "checking if attribute exists: ". $attribute_name );			
			if ( taxonomy_exists( wc_attribute_taxonomy_name( $attribute_name ) ) ) {
				
				$attribute_name = wc_attribute_taxonomy_name( $attribute_name );
				WPLA()->logger->info( "using existing attribute: ". $attribute_name );			

			} else {

				$wpdb->insert(
					$wpdb->prefix . "woocommerce_attribute_taxonomies",
					array(
						'attribute_name' 	=> $attribute_name,
						'attribute_label' 	=> $attribute_label,
						'attribute_type' 	=> $attribute_type
					)
				);

				// Register the taxonomy now so that the import works!
				$attribute_name = 'pa_'.$attribute_name;
				register_taxonomy( $attribute_name,
			        array('product'),
			        array(
			            'hierarchical' => true,
			            'show_ui' => false,
			            'query_var' => true,
			            'rewrite' => false,
			        )
			    );

				$this->clearAttributeCache();

				WPLA()->logger->info( "added new attribute {$attribute_name} - {$attribute_label}" );
			}

		} else {
			WPLA()->logger->error( "there was a problem adding attribute {$attribute_name} !" );			
		}

		return $attribute_name;
	}


	public function isTextAttribute( $attribute_name ) {

		$attributes_as_text = get_option('wpla_import_attrib_as_text', array() );
		foreach ($attributes_as_text as $searchstring) {
			if ( stripos( $attribute_name, $searchstring) !== false ) {
				WPLA()->logger->info( "isTextAttribute( $attribute_name ) found match for $searchstring" );
				return true;
			}
		}
		return false;
	}

    // get image upload path
    public function getImagesDir( $image = false, $asin = false ) {
		$upload_dir   = wp_upload_dir();
		$basedir_name = get_option('wpla_import_images_basedir_name', 'imported/');
		$folder_level = get_option('wpla_import_images_subfolder_level', 0);
		$images_dir   = $upload_dir['basedir'].'/'.$basedir_name;

		if ( ! $asin ) return $images_dir;

		// add subfolders
		for ($i=1; $i <= $folder_level; $i++) { 
			$images_dir .= substr( $asin, 0 - $i ) . '/';
			if ( !is_dir( $images_dir ) ) mkdir( $images_dir ); // TODO: check permissions
		}

		if ( !is_dir( $images_dir ) ) mkdir( $images_dir ); // TODO: check permissions
        return $images_dir;

    }

    // get image upload url
    public function getImagesUrl( $image = false, $asin = false ) {
		$upload_dir   = wp_upload_dir();
		$basedir_name = get_option('wpla_import_images_basedir_name', 'imported/');
		$folder_level = get_option('wpla_import_images_subfolder_level', 0);
		$images_url   = $upload_dir['baseurl'].'/'.$basedir_name;

		if ( ! $asin ) return $images_url;

		// add subfolders
		for ($i=1; $i <= $folder_level; $i++) { 
			$images_url .= substr( $asin, 0 - $i ) . '/';
		}
        return $images_url;
        
    }

	public function addProductImage( $post_id, $image, $title = 'default', $asin = false, $is_featured_image = true, $suffix = false ) {

		// skip invalid $post_id's
        if ( ! $post_id || is_wp_error( $post_id ) ) return false; 

		// set image upload dir
		$imported_images_dir = $this->getImagesDir( basename($image), $asin );
		$imported_images_url = $this->getImagesUrl( basename($image), $asin );

		if ( $suffix )
			$asin = $asin . '-' . $suffix;

		// skip empty image
		if ( trim( $image ) == '' ) return false;
		
		// full url to source image
		$img_url = $image; 

		// limit title to 120 characters - filenames/guids can only have 255 chars
		$title = strlen($title) > 120 ? trim(substr($title, 0, 120)) : $title;

		// append amazon id to listing title
		if ( $asin ) $title .= '-'.$asin;

		// build local image path - based on title
		$imgslug = sanitize_file_name( $title ); 						// sanitize listing title
		$imgslug = str_replace( '%20', '-', $imgslug );  				// replace %20 with dash
        $imgslug = preg_replace( '/[^A-Za-z0-9_\-]/', '', $imgslug ); 	// allow only alphanumeric chars, dashes and underscores
		$imgfile = sanitize_file_name( $imgslug.'.jpg' ); 				// sanitize listing title

		// copy remote image
		$img_local_path = $imported_images_dir . $imgfile;     // full path to destination image
		$img_local_url  = $imported_images_url . $imgfile;
		// $is_new_image   = $this->copyRemoteImage( $img_url, $img_local_path );
		$copy_result    = $this->copyRemoteImage( $img_url, $img_local_path );
		// return values:
		// 	true   - new image downloaded
		// 	false  - filename already exists
		// 	string - matching image found by MD5 hash

		if ( is_wp_error( $copy_result ) ) {
			WPLA()->logger->error('image download failed: '.$img_url);
			// TODO: notify user when image download failed
			return false;
		}

		// if an existing image was found by MD5 hash, try to find its attachment_id
		if ( is_string( $copy_result ) ) {

			// get new image file name
			$img_local_path = $copy_result;
			$imgfile        = basename( $img_local_path );

			$attachment_id = $this->get_attachment_id_for_filename( $imgfile );

			if ( $attachment_id ) {
				WPLA()->logger->info( 'found existing attachment_id (md5): '.$attachment_id );

				// set post thumbnail
				if ( $is_featured_image ) set_post_thumbnail( $post_id, $attachment_id );
				WPLA()->logger->info( "set_post_thumbnail( $post_id, $attachment_id )" );

				return $attachment_id;	
			} 
		}

		// if image file already exists, try to find attachment_id
		if ( $copy_result === false ) {

			$attachment_id = $this->get_attachment_id_for_filename( $imgfile );

			if ( $attachment_id ) {
				WPLA()->logger->info( 'found existing attachment_id: '.$attachment_id );

				// set post thumbnail
				if ( $is_featured_image ) set_post_thumbnail( $post_id, $attachment_id );
				WPLA()->logger->info( "set_post_thumbnail( $post_id, $attachment_id )" );

				return $attachment_id;	
			} 
		}


		// generate name from filename
		$name_parts = pathinfo( $imgfile );
		$name = trim( substr( $imgfile, 0, -( 1 + strlen( $name_parts['extension'] ) ) ) );

		// Construct the attachment array
		$wp_filetype = wp_check_filetype( basename( $imgfile ), null );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'guid' => $img_local_url,
			'post_parent' => $post_id,
			'post_title' => $name,
			'post_content' => '',
			'post_status' => 'inherit',
		);

		// Save the attachment data
		// $attachment_id = wp_insert_attachment( $attachment, $img_local_path, $post_id );
		$attachment_id = self::wp_insert_attachment_with_error_handling( $attachment, $img_local_path, $post_id );
		if ( ! is_wp_error( $attachment_id ) ) {


			// if ( $is_new_image ) {
			// 	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $img_local_path ) );
			// 	WPLA()->logger->info( 'wp_update_attachment_metadata()' );
			// }

			// if ( $is_new_image ) ... removed because it would leave image meta data empty, resulting a in 1px image in admin
			// WPLA()->logger->info( 'wp_update_attachment_metadata()' );
			WPLA()->logger->info( 'new attachment_id: ' . $attachment_id );

			// make sure we have wp_generate_attachment_metadata() available
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			// generate and update attachment meta data - will generate thumbnails as well
			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $img_local_path ) );

			// set post thumbnail
			if ( $is_featured_image ) set_post_thumbnail( $post_id, $attachment_id );
			WPLA()->logger->info( 'set_post_thumbnail()' );

			// mark attachment as imported
			update_post_meta( $attachment_id, '_wpla_asin', $post_id );

			WPLA()->logger->info( 'product image: ' . $img_local_url );

		} else {
			wpla_show_message( 'Failed to create attachment from image ' . $img_local_path . '<br>Error: '.$attachment_id->get_error_message(), 'warn' );
			WPLA()->logger->error( 'Failed to create attachment from image ' . $img_local_path . '<br>Error: '.$attachment_id->get_error_message() );
		}
		return $attachment_id;
	} // addProductImage()

	static function wp_insert_attachment_with_error_handling( $args, $file = false, $parent = 0 ) {
	    $defaults = array(
	        'file'        => $file,
	        'post_parent' => 0
	    );
	 
	    $data = wp_parse_args( $args, $defaults );
	 
	    if ( ! empty( $parent ) ) {
	        $data['post_parent'] = $parent;
	    }
	 
	    $data['post_type'] = 'attachment';
	 
	    return wp_insert_post( $data, true ); // allow WP_Error object to be returned
	} // wp_insert_attachment_with_error_handling()

	public function get_attachment_id_for_filename( $imgfile ) {
		global $wpdb;

		$attachment_id = $wpdb->get_var(
			"
			SELECT post_id
			FROM {$wpdb->prefix}postmeta
			WHERE meta_key = '_wp_attached_file'
			  AND meta_value LIKE '%$imgfile%'
			"
		);
		// WPLA()->logger->info( "get_attachment_id_for_filename( $imgfile ) - attachment_id: $attachment_id" );
		// echo "<pre>";print_r($wpdb->last_query);echo"</pre>";
		// echo mysql_error();

		// $attachment_id = $wpdb->get_var( $wpdb->prepare(
		// 	"
		// 	SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta 
		// 	WHERE wposts.ID            = wpostmeta.post_id 
		// 	  AND wpostmeta.meta_key   = '_wp_attached_file' 
		// 	  AND wpostmeta.meta_value LIKE '%%s%' 
		// 	  AND wposts.post_type     = 'attachment'
		// 	", $imgfile ) );
		// echo $wpdb->last_query;
		// echo mysql_error();

		// check if attachment actually exists
		if ( ! $attachment_id ) return false;
		if ( ! wp_get_attachment_url( $attachment_id ) ) return false;

		return $attachment_id;
	} // get_attachment_id_for_filename()


	public function updateProduct( $product_id, $data ) {
		global $wpdb;
		global $woocommerce;
		WPLA()->logger->info( "==============================================================================" );
		//WPLA()->logger->info( "updateProduct() - ID: ".$data['asin'] );

		// some shortcuts
		$asin              = $data['asin'];
		$products_name     = $data['auction_title'];
		$products_price    = $data['price'];
		$products_quantity = $data['quantity'];
		$updated = false;

		// find WP product ID
		$product_id  = $this->getProductIdByOriginalId( $asin );
		if ( intval( $product_id ) == 0 ) {
			return false;
		}

		// get WC product for reference
		$product = $this->getProduct( $product_id );
		// echo "<pre>";print_r($product);echo"</pre>";#die();


		// update price
		if ( wpla_get_product_meta( $product, 'price' ) != $products_price ) {
			update_post_meta( $product_id, '_price', $products_price);
			update_post_meta( $product_id, '_regular_price', $products_price);
			WPLA()->logger->info( "updated price for product $product_id - new price: ".$products_price );
			$updated = true;
		}

		// update stock - except for parent variations which should be synced instead...
		if ( ( wpla_get_product_meta( $product, 'stock' ) != $products_quantity ) && ( wpla_get_product_meta( $product, 'product_type' ) != 'variable' ) ) {

			update_post_meta( $product_id, '_stock', $products_quantity);

			// Out of stock attribute
			if ( $products_quantity <= 0 ) :
				update_post_meta( $product_id, '_stock_status', 'outofstock' );
			endif;

			WPLA()->logger->info( "updated stock for product $product_id - new stock: ".$products_quantity );
			$updated = true;
		}

		// update creation date
		// $post_date_gmt = date_i18n( 'Y-m-d H:i:s', strtotime($data['products_date_added']), true );
		// $post_date     = date_i18n( 'Y-m-d H:i:s', strtotime($data['products_date_added']), false );
		// $post_data = array(
		// 	'post_date' 	=> $post_date, //The time post was made.
		// 	'post_date_gmt' => $post_date_gmt, //The time post was made, in GMT.
		// );
		// $result = $wpdb->update($wpdb->prefix.'posts', $post_data, array('ID' => $product_id ));
		// echo $wpdb->last_error;

	
		if ( $updated ) {
			// $woocommerce->clear_product_transients( $product_id );
			if ( function_exists('wc_delete_product_transients') )
				wc_delete_product_transients( $product_id );

			WPLA()->logger->info( "updated product $product_id ($asin): $products_name " );
			$this->updated_count++;
		}

		return $product_id;
	} // updateProduct()


	public function updateProductFromItem( $item, $report_row ) {
		global $woocommerce;
		WPLA()->logger->info( "==============================================================================" );
		//WPLA()->logger->info( "updateProductFromItem() - ID: ".$data['asin'] );

		// some shortcuts
		$asin            = $item->asin;
		$product_id      = $item->post_id;
		$amazon_name     = $item->listing_title;
		$amazon_price    = $item->price;
		$amazon_quantity = $item->quantity;
		$report_quantity = $report_row['quantity'];
		$updated         = false;

		// get WC product for reference
		$product = $this->getProduct( $product_id );
		if ( ! $product ) return;
		// echo "<pre>";print_r($product);echo"</pre>";#die();

		// get options
		$reports_update_woo_stock     = get_option( 'wpla_reports_update_woo_stock'    , 1 ) == 1 ? true : false;
		$reports_update_woo_price     = get_option( 'wpla_reports_update_woo_price'    , 1 ) == 1 ? true : false;
		$reports_update_woo_condition = get_option( 'wpla_reports_update_woo_condition', 1 ) == 1 ? true : false;


		// 
		// update item-condition - if enabled
		// 
		if ( $reports_update_woo_condition ) {

			$amazon_condition_type = WPLA_ImportHelper::convertNumericConditionIdToType( $report_row['item-condition'] );
			update_post_meta( $product_id, '_amazon_condition_type', $amazon_condition_type);

			$amazon_condition_note = WPLA_ListingsModel::convertToUTF8( $report_row['item-note'] );
			update_post_meta( $product_id, '_amazon_condition_note', $amazon_condition_note );
			WPLA()->logger->info( "updated condition for product $product_id: $amazon_condition_type / ".$amazon_condition_note );
			// WPLA()->logger->info( "stored condition note: " . get_post_meta( $product_id, '_amazon_condition_note', true ) );

		}


		// 
		// update price - if enabled
		// 
		if ( $reports_update_woo_price ) {

			// if this item has a profile, we need to apply the price modifiers to the product price
			$product_price = wpla_get_product_meta( $product, 'price' );
			$profile = $item->profile_id ? new WPLA_AmazonProfile( $item->profile_id ) : false;
			if ( $profile ) {
				$product_price = $profile->processProfilePrice( $product_price );
				$amazon_price  = $profile->reverseProfilePrice( $amazon_price );
			}

			// update price - unless custom amazon price is set
			if ( $product_price != $amazon_price ) {
				if ( ! get_post_meta( $product_id, '_amazon_price', true ) ) {
				    // Don't set the _price meta because it is a calculated or dynamic field #16756
                    // - see https://wordpress.org/support/topic/difference-between-_price-_regular_price-and-_sale_price-meta-keys/
                    // EDIT: Update the _price meta if NO sale price is present #21405
                    if ( ! get_post_meta( $product_id, '_sale_price', true ) ) {
                        update_post_meta( $product_id, '_price', $amazon_price);
                    }

					update_post_meta( $product_id, '_regular_price', $amazon_price);
					WPLA()->logger->info( "updated price for product $product_id - new price: ".$amazon_price );
					$updated = true;			
				}
			}

		} // if update price


		// 
		// - update stock - if enabled and the report quantity column is not empty
        // - only update the stock if the item's status is not 'changed' or 'submitted'
		//
        $skip_status = array( 'changed', 'submitted' );
		if ( !in_array( $item->status, $skip_status ) && $reports_update_woo_stock && $report_quantity !== '' && $report_quantity !== false ) {

			if ( wpla_get_product_meta( $product, 'stock' ) != $amazon_quantity ) {
				update_post_meta( $product_id, '_stock', $amazon_quantity);
				WPLA()->logger->info( "updated stock for product $product_id - new stock: ".$amazon_quantity );
				$updated = true;
			}

			// update out of stock attribute
			if ( $amazon_quantity > 0 ) {
				$stock_status = 'instock';
			} elseif ( $item->product_type == 'variable' ) {
				$stock_status = 'instock';
			} else {
				$stock_status = 'outofstock';
			}
			update_post_meta( $product_id, '_stock_status', $stock_status );

		}


		if ( $updated ) {
			// $woocommerce->clear_product_transients( $product_id );
			if ( function_exists('wc_delete_product_transients') )
				wc_delete_product_transients( $product_id );

			WPLA()->logger->info( "updated product $product_id ($asin): $amazon_name " );
			$this->updated_count++;
		}

		return $product_id;
	} // updateProductFromItem()


	// get WooCommerce product object (private)
	public function getProduct( $post_id, $is_variation = false ) {

		// use get_product() on WC 2.0+
		if ( function_exists('get_product') ) {
			return get_product( $post_id );
		} else {
			// instantiate WC_Product on WC 1.x
			return $is_variation ? new WC_Product_Variation( $post_id ) : new WC_Product( $post_id );
		}

	}	
	





	public function copyRemoteImage( $url, $file ) {
		WPLA()->logger->info( 'import img: ' . $url );
		if ( file_exists( $file ) ) {
			$this->images_hashmap[ md5( file_get_contents($file) ) ] = $file;
			WPLA()->logger->info( 'skipped image' );
			return false;
		}

		// try to get full size image from EPS - if enabled
		if ( get_option('wpla_import_load_highres_eps', 1) == 1 ) {
			if ( strpos( $url, 'amazonimg.com') > 0 ) {
				$url = str_replace( '_1.JPG', '_57.JPG', $url );
				WPLA()->logger->info( '*** HighRes EPS URL: '.$url );
			} 
		}

		$url = str_replace( ' ', '%20', $url );
		$response = wp_remote_get( $url, array( 'timeout' => 15 ) );

		if ( is_wp_error( $response ) ) {
			WPLA()->logger->warn( 'error downloading file: ' . print_r( $response, 1 ) );
			return false;
		} else {

			// check MD5 cache - maybe this same images has been already downloaded from a different URL
			$this_hash = md5( wp_remote_retrieve_body( $response ) );
			foreach ($this->images_hashmap as $cached_hash => $cached_image_path) {
				if ( $this_hash == $cached_hash ) {
					WPLA()->logger->info( 'FOUND IMAGE by MD5: '.$cached_image_path );
					return $cached_image_path;
				}
			}

			//print_r( $response );
			WPLA()->logger->info( 'copied to : ' . $file );
			$this->images_hashmap[ $this_hash ] = $file;

			// v1
			// $bytes_written = file_put_contents( $file, $response['body'] );

			// v2
			$data = wp_remote_retrieve_body( $response );
			$mode = 'w';

			// show all errors for debugging
			// error_reporting(E_ALL);
			// ini_set('display_errors', 1);
			ini_set('track_errors', 1); 
			global $php_errormsg;

			$f = fopen( $file, $mode );
			if ( $f === false ) {
				WPLA()->logger->error( 'error on fopen() ' . basename($file) );	
				$bytes_written = 0;
				WPLA()->logger->error( 'php_errormsg: ' . $php_errormsg );	
				echo '<div class="error"><p><b>There was an error when saving an image:</b><br>' . htmlspecialchars($php_errormsg) . '</p></div>'; 

				$filecount = count(scandir( dirname($file) ));
				echo '<div class="updated"><p>Number of files in directory: ' . $filecount . '</p></div>'; 

				// return 0;
			} else {
				if ( is_array($data) ) $data = implode($data);
				// $bytes_written = fwrite($f, $data);
				$bytes_written = fwrite($f, $data, strlen($data) );
				fclose($f);
				// return $bytes_written;
			}

			// reset error reporting 
			// error_reporting( E_ERROR );
			ini_set('track_errors', 0); 

			// log possible error reasons
			if ( $bytes_written > 0) {
				WPLA()->logger->info( $bytes_written . ' bytes written to file ' . basename($file) );
			} else {
				WPLA()->logger->error( $bytes_written . ' (zero) bytes written to file ' . basename($file) );	
				WPLA()->logger->error( strlen( wp_remote_retrieve_body( $response ) ) . ' bytes SHOULD be written' );
				
				if ( ! is_writable( dirname( $file ) ) ) {
					WPLA()->logger->error( 'folder ' . dirname($file) . ' is not writable!' );	
					echo '<div class="error"><p><b>The folder is not writable:</b><br>' . dirname($file) . '</p></div>'; 
				}

				if ( file_exists( $file ) ) {
					WPLA()->logger->error( 'file already exists: ' . ($file) );	
					if ( ! is_writable( $file ) ) {
						WPLA()->logger->error( 'but it is not writable!!');	
						echo '<div class="error"><p><b>The file already exists but is not writable:</b><br>' . basename($file) . '</p></div>'; 
					}
				}
			} // if $bytes_written

		}

		return true;

	} // copyRemoteImage()

	public function makeSlug( $title ) {
		// return sanitize_title( $title, '', 'save' );
		return $this->sanitize_slug( $title );
	}

	function sanitize_slug( $slug )
	{
	     // replace spaces with dashes
	     $slug = str_replace(' ', '-', $slug);

	     // remove everything except letters, numbers and -
	     $slug = preg_replace( '~([^a-z0-9\-])~i', '', $slug );
	     
	     // when more than one - , replace it with one only
	     $slug = preg_replace( '~\-\-+~', '-', $slug );
	     
	     return $slug;
	}

	public function getCategoryById( $category_id ) {
		$category = get_term_by( 'id', $category_id, 'product_cat' );
		return $category;
	}

	public function getCategoryIdByOriginalId( $category_id ) {
		global $wpdb;

		$woocommerce_term_id = $wpdb->get_var(
			"
			SELECT woocommerce_term_id
			FROM {$wpdb->prefix}woocommerce_termmeta
			WHERE meta_key = '_amazon_category_id'
			  AND meta_value = '$category_id'
			"
		);
		WPLA()->logger->info( "getCategoryIdByOriginalId( $category_id ) : " . $woocommerce_term_id );

		# *****
		// $woocommerce_term_id = get_option('wpla_import_category_id', false);
		return $woocommerce_term_id;
	}

	public function loadProductsLookUpCache() {
		global $wpdb;

		$lookup_table = array();
		// // this version doesn't check whether the product actually exists - so it might fail when there is stale postmeta data
		// $results = $wpdb->get_results(
		// 	"
		// 	SELECT post_id, meta_value as asin
		// 	FROM {$wpdb->prefix}postmeta
		// 	WHERE meta_key = '_wpla_asin'
		// 	");

		// load ASINs and IDs for existing products
		$results = $wpdb->get_results(
			"
			SELECT pm.post_id, pm.meta_value as asin
			FROM {$wpdb->prefix}postmeta pm
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE pm.meta_key = '_wpla_asin'
			  AND p.ID IS NOT NULL
			");

		foreach ( $results as $result ) {
			$lookup_table[ $result->asin ] = $result->post_id;
		}
		$this->amazonID_to_postID = $lookup_table;
		// echo "<pre>";print_r($lookup_table);echo"</pre>";die();

	}

	public function getProductIdByOriginalId( $asin, $use_cache = false ) {
		global $wpdb;

		if ( $use_cache ) {

			// check if cache has been initialized
			if ( ! is_array( $this->amazonID_to_postID ) ) $this->loadProductsLookUpCache();

			// find and return cache result
			if ( isset( $this->amazonID_to_postID[ $asin ] ) )
				return $this->amazonID_to_postID[ $asin ];

			return false;
		}

		// get a single result from postmeta
		$woocommerce_product_id = $wpdb->get_var(
			"
			SELECT post_id
			FROM {$wpdb->prefix}postmeta
			WHERE meta_key = '_wpla_asin'
			  AND meta_value = '$asin'
			"
		);
		WPLA()->logger->debug( "getProductIdByOriginalId( $asin ) : " . $woocommerce_product_id );

		// make sure the product / post exists
		$post_status = $wpdb->get_var(
			"
			SELECT post_status
			FROM {$wpdb->prefix}posts
			WHERE ID = '$woocommerce_product_id'
			"
		);
		if ( empty($post_status) ) return false;

		return $woocommerce_product_id;
	}

	// check for an existing WooCommerce product for a given SKU
	// (called when importing products)
	static public function getProductIdBySKU( $sku, $use_cache = false ) {
		global $wpdb;
		if ( empty($sku) ) return false;

		// get a single result from postmeta
		// TODO: check all results - and show warning if there are more than one
		$woocommerce_product_id = $wpdb->get_var( $wpdb->prepare(
			"
			SELECT post_id
			FROM {$wpdb->prefix}postmeta
			WHERE meta_key = '_sku'
			  AND meta_value = %s
			",
			$sku
		) );

		// make sure the product / post exists
		$post_status = $wpdb->get_var( $wpdb->prepare(
			"
			SELECT post_status
			FROM {$wpdb->prefix}posts
			WHERE ID = %d
			",
			$woocommerce_product_id
		) );
		if ( empty($post_status) ) return false;

		// WPLA()->logger->info( "getProductIdBySKU( $sku ) : " . $woocommerce_product_id );
		return $woocommerce_product_id;
	}

	public function getProductImagesFilenames( $id ) {
		global $wpdb;

		$results = $wpdb->get_col(
			"
			SELECT guid
			FROM {$wpdb->prefix}posts
			WHERE post_type = 'attachment'
			  AND post_parent = '$id'
			"
		);
		WPLA()->logger->debug( "getProductImagesFilenames( $id ) : " . print_r( $results, 1 ) );

		$filenames = array();
		foreach ( $results as $row ) {
			$filenames[] = basename( $row );
		}

		return $filenames;
	}


} // class WPLA_ProductBuilder
