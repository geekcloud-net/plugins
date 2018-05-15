<?php

class WPLA_ProductsImporter {
	
	var $account;

	public $result;
	public $message = '';
	public $lastError;
	public $lastPostID;
	public $updated_count = 0;
	public $imported_count = 0;
	public $request_count = 0;

	const TABLENAME = 'amazon_listings';


	public function createProductFromAmazonListing( $listing ) {

		$lm = new WPLA_ListingsModel();
		if ( is_numeric( $listing ) ) {
			$listing = $lm->getItem( $listing );
		}
		if ( ! $listing ) return false;
		$listing_id = $listing['id'];
		WPLA()->logger->info('--- createProductFromAmazonListing() - ID: '.$listing['id'].' / ASIN '.$listing['asin'] );

		$account = WPLA_AmazonAccount::getAccount( $listing['account_id'] );
		if ( ! $account ) return false;

		// init api
		$api = new WPLA_AmazonAPI( $account->id );

		// get product details from amazon
		if ( $listing['asin'] ) {
			$result = $api->getMatchingProductForId( $listing['asin'], 'ASIN' );
		} else {
			$result = $api->getMatchingProductForId( $listing['sku'], 'SellerSKU' );			
			WPLA()->logger->warn('No ASIN found! Importing product by SKU '.$listing['sku']);
		}
		$this->request_count++;
		// echo "<pre>getMatchingProductForId() returned: ";print_r($result);echo"</pre>";#die();

		// handle Import Variations As Simple option
		if ( $result->success && get_option( 'wpla_import_variations_as_simple', 0 ) ) {
			$result->product->variation_type = '_single_';
		}


		// handle empty result error
		if ( $result->success && empty( $result->product->AttributeSets->ItemAttributes->Title ) ) {
			if ( ! empty( $result->product->GetMatchingProductForIdResult->Error->Message ) ) {
				$this->lastError = sprintf( __('There was a problem fetching product details for %s.','wpla'), $listing['asin'] ) . '<br><code>' . $result->product->GetMatchingProductForIdResult->Error->Message . '</code>';
			} else {
				$this->lastError = sprintf( __('There was a problem fetching product details for %s.','wpla'), $listing['asin'] ) . ' The product data received from Amazon was empty.';
			}
			return false;
		}

		// if we're importing a product without ASIN, set ASIN from result before continuing
		if ( ! $listing['asin'] ) {
			$asin = $result->product->ASIN;
			$data = array(
				'asin' => $asin,
			);
			$lm->updateListing( $listing_id, $data );				
			$listing['asin'] = $asin;
			WPLA()->logger->info('Fixed missing ASIN for SKU '.$listing['sku'].' - new ASIN: '.$asin );
		}

		// first check if product already exists in WooCommerce...
		$post_id = WPLA_ProductBuilder::getProductIdBySKU( $listing['sku'] );
		if ( $post_id ) {
			WPLA()->logger->info('found existing product by SKU '.$listing['sku'].' - post_id: '.$post_id );
			$this->message = "Found existing product for SKU ".$listing['sku'];

			// if this SKU exists, check whether it is a variation or not
			$_product = WPLA_ProductWrapper::getProduct( $post_id );
			// echo "<pre>";print_r($_product);echo"</pre>";#die();

			// handle child variations
			if ( $_product->is_type( 'variation' ) ) {
				// set parent_id
                if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
                    $data = array(
                        'post_id'      => $_product->variation_id,
                        'parent_id'    => $_product->parent->id,
                        'product_type' => wpla_get_product_meta( $_product, 'product_type' ),
                    );
                } else {
                    $data = array(
                        'post_id'      => $_product->get_id(),
                        'parent_id'    => $_product->get_parent_id(),
                        'product_type' => $_product->get_type()
                    );
                }

				$lm->updateListing( $listing_id, $data );				
				$this->message = "Found existing variation for SKU ".$listing['sku'];
			}

		} else {
			// SKU does not exist in WC...
			$variation_type = $result->success ? $result->product->variation_type : '_unknown_';
			$variation_type = is_string( $variation_type ) ? $variation_type : '_none_'; // convert empty object to string
			WPLA()->logger->info('no WC product found for SKU '.$listing['sku'].' - type: '.$variation_type );

			// process child variation - fetch parent item instead
			if ( $result->success && $result->product->variation_type == 'child' ) {

				// foreign imports should have their title updated first
				// $data = array();
				// $data['listing_title'] = $result->product->AttributeSets->ItemAttributes->Title;
				// $lm->updateListing( $listing_id, $data );

				// update listing attributes and title from result
				$lm->updateItemAttributes( $result->product->AttributeSets->ItemAttributes, $listing_id );

				// get parent item - new request 
				$parent_asin   = $result->product->VariationParentASIN;
				$api           = new WPLA_AmazonAPI( $account->id ); // new log record
				$parent_result = $api->getMatchingProductForId( $parent_asin, 'ASIN' );
				$parent_node   = $parent_result->success ? $parent_result->product : false;
				$this->request_count++;
				
				// check for "variations without attributes" 
				// if there are no variation attributes on the parent, fall back to creating a simple product instead
				if ( 0 == self::countVariationChildNodes( $parent_node ) ) {
					$msg = $listing['asin']." seems to be a child of parent ASIN $parent_asin - but that parent has no variation attributes set, so it's imported as a simple product.";
					WPLA()->logger->warn( $msg );
					wpla_show_message( $msg, 'warn' );
					$result->product->variation_type = '_invalid_parent_';
					$result->product->variation_msg  = $msg;
				} else {
					WPLA()->logger->info( $listing['asin']." is a child of parent ASIN $parent_asin" );
					$result = $parent_result;
				}

			}

			// process parent variation
			if ( $result->success && $result->product->variation_type == 'parent' ) {

				// get parent listing - or create if it doesn't exist yet
				$parent_listing = $this->getOrCreateParentVariation( $result, $listing, $account );

				// all further processing should be using the parent variation - including children
				$result->product = $this->parseVariationChildNodes( $result->product, $parent_listing, $account );
				// $listing      = $parent_listing; // $listing is supposed to be an array, $parent listing is an object
				$listing_id      = $parent_listing->id;
				$listing         = $lm->getItem( $listing_id );
			}

		} // SKU does not exist in WC


		// import product...
		if ( $result->success ) {

			// update price for foreign imports (imported by ASIN)
			if ( ( 'foreign_import' == $listing['source'] ) && ! $listing['price'] ) {
				$this->updateListingWithLowestPrice( $listing, $account );
			}

			// update listing attributes and title from result
			$lm->updateItemAttributes( $result->product->AttributeSets->ItemAttributes, $listing_id );
			$listing = $lm->getItem( $listing_id );

			// create product
			$woo = new WPLA_ProductBuilder();
			$woo->importSingleProduct( $listing, $result->product );

			// post-process variations - add post_id
			if ( $result->product->variation_type == 'parent' && isset( $result->product->variations ) ) {
				$this->fixNewVariationListings( $result->product->variations, $parent_listing );
			}

			$success = true;
			$errors  = '';
			$this->lastPostID = $woo->last_insert_id;

		} elseif ( @$result->Error->Message ) {
			$errors  = sprintf( __('There was a problem fetching product details for %s.','wpla'), $listing['asin'] ) .'<br>Error: '. $result->Error->Message;
			$success = false;
		} elseif ( @$result->ErrorMessage ) {
			$errors  = sprintf( __('There was a problem fetching product details for %s.','wpla'), $listing['asin'] ) .'<br>Error: '. $result->ErrorMessage;
			$success = false;
		} else {
			$errors  = sprintf( __('There was a problem fetching product details for %s.','wpla'), $listing['asin'] );
			$success = false;
		}

		$this->lastError = $errors;
		return $success;
	} // createProductFromAmazonListing()


	public function updateListingWithLowestPrice( $listing, $account ) {

		// get listing object
		$lm = new WPLA_ListingsModel();
		$listing = $lm->getItem( $listing['id'], OBJECT);

		// fetch pricing info
		$api     = new WPLA_AmazonAPI( $account->id );
		$result  = $api->getCompetitivePricingForId( array( $listing->asin ) );
		if ( empty($result) || empty($result->products) || ! is_array($result->products) ) return;

		// find lowest New price
		$product      = reset( $result->products ); // get first product
		$lowest_price = PHP_INT_MAX;
		foreach ( $product->prices as $price ) {

			if ( $price->LandedPrice < $lowest_price ) {
				if ( $price->condition == 'New' ) {
					$lowest_price = $price->LandedPrice;
				}
			}

		} // each pricing node
		if ( $lowest_price == PHP_INT_MAX ) return;

		// update listing
		$lm->updateListing( $listing->id, array( 'price' => $lowest_price ) );

	} // updateListingWithLowestPrice()


	public function getOrCreateParentVariation( $result, $listing, $account ) {
		$lm = new WPLA_ListingsModel();

		$parent_asin = $result->product->ASIN;
		WPLA()->logger->info( "processing parent variation $parent_asin" );

		// find existing parent variation listing
		if ( $parent_listing = $lm->getItemByASIN( $parent_asin ) ) {
			WPLA()->logger->info( "Found existing parent for ASIN $parent_asin" );
			$this->message = "Found existing variable parent listing for ASIN $parent_asin";
			$parent_id = $parent_listing->id;
		} else {
			// create parent listing
			$data = array(
				'asin'         => $parent_asin,
				'sku'          => $parent_asin,
				'product_type' => 'variable',
				'source'       => $listing['source'],
				'status'       => 'matched', 	// or else?
				'account_id'   => $account->id,
                'description'  => $listing['description'],
			);

            // extract and store the item description into the parent listing #15467
            $details = json_decode( $listing['details'], true );
            if ( !empty( $details['item-description'] ) ) {
                $data['description'] = $details['item-description'];
            }

			$parent_id = $lm->insertListingData( $data );

			// update listing attributes
			$lm->updateItemAttributes( $result->product->AttributeSets->ItemAttributes, $parent_id );

			$parent_listing = $lm->getItem( $parent_id, OBJECT ); // load listing object
			WPLA()->logger->info( "Created new parent for ASIN $parent_asin with ID $parent_id");
			$this->message = "Created new variable product for ASIN $parent_asin with ID $parent_id";
		}

		return $parent_listing;
	} // getOrCreateParentVariation( )


	// count max of variation attributes in a given parent product node
	static public function countVariationChildNodes( $product_node ) {
		WPLA()->logger->info( "countVariationChildNodes()" );
		if ( !    isset( $product_node->Relationships->VariationChild ) ) return 0;

        // if only a single variation is returned, convert VariationChild node to array
		if (  is_object( $product_node->Relationships->VariationChild ) ) {
			$product_node->Relationships->VariationChild = array( $product_node->Relationships->VariationChild );			
		}

		// count max number of attributes
		$number_of_attributes = 0;
		foreach ( $product_node->Relationships->VariationChild as $VariationChild ) {
			$attribute_count = 0;
			foreach ( $VariationChild as $attribute_name => $attribute_value ) {
				if ( ! is_object( $attribute_value ) ) $attribute_count++;
			}
			$number_of_attributes = max( $number_of_attributes, $attribute_count );
		}
		WPLA()->logger->info( "number of attributes: ".$number_of_attributes );

		return $number_of_attributes;
	} // countVariationChildNodes( )


	public function parseVariationChildNodes( $product_node, $parent_listing, $account ) {
		WPLA()->logger->info( "parseVariationChildNodes()" );

		if ( !    isset( $product_node->Relationships->VariationChild ) ) return $product_node;
		// if ( ! is_array( $product_node->Relationships->VariationChild ) ) return $product_node;

        // if only a single variation is returned, convert VariationChild node to array
		if (  is_object( $product_node->Relationships->VariationChild ) ) {
			$product_node->Relationships->VariationChild = array( $product_node->Relationships->VariationChild );			
		}

		// count number of attributes
		$number_of_attributes = self::countVariationChildNodes( $product_node );

		// loop variations
		$lm = new WPLA_ListingsModel();
		$variations      = array();
		foreach ( $product_node->Relationships->VariationChild as $VariationChild ) {
			
			// build simple variation object
			$newvar = new stdClass();
			$newvar->asin = $VariationChild->Identifiers->MarketplaceASIN->ASIN;
			$newvar->sku  = $VariationChild->Identifiers->MarketplaceASIN->ASIN;
			unset( $VariationChild->Identifiers );

			$newvar->vtheme           = array();
			$newvar->attributes       = array();
			$newvar->attribute_values = array();
			foreach ( $VariationChild as $attribute_name => $attribute_value ) {
				$attrib = new stdClass();
				$attrib->name  = $attribute_name;
				$attrib->value = $attribute_value;
				$newvar->attributes[] = $attrib;
				$newvar->vtheme[] = $attribute_name;
				$newvar->attribute_values[] = $attribute_value;
			}
			$newvar->vtheme = join('-',$newvar->vtheme);

			// skip variations with missing attributes
			if ( sizeof($newvar->attributes) < $number_of_attributes ) {
				WPLA()->logger->info( "skipped variation {$newvar->asin} because it has less than the required number of attributes: ".$number_of_attributes );
				continue;
			}

			// check if this variation exist in listing table
			// (using getItemByASIN() does no harm here - parseVariationChildNodes() is only called when no SKU was found)
			if ( $var_item = $lm->getItemByASIN( $newvar->asin ) ) {

				// use SKU, price and quantity from listing (usually imported from report)
				$newvar->sku 	         = $var_item->sku;
				$newvar->qty             = $var_item->quantity;
				$newvar->price           = $var_item->price;
				$newvar->variation_image = '';

				WPLA()->logger->info( "Found existing variation child for ASIN {$newvar->asin} - id: ".$var_item->id );

				// convert items from report to real variation listing
				$data = array();
				$data['post_id']       = ''; // filled in after creating the product
				$data['parent_id']     = $parent_listing->post_id;
				$data['vtheme']        = $newvar->vtheme;
				$data['product_type']  = 'variation';
				$data['date_created']  = gmdate( 'Y-m-d H:i:s', time() ); // this is to fix the sort order only
				$data['status']  	   = $var_item->source == 'foreign_import' ? 'matched' : 'online';

				$lm->updateListing( $var_item->id, $data );
				$newvar->listing_id = $var_item->id;

				// TODO: variation image
				// $var_item->attributes = maybe_unserialize( $var_item->attributes )
				// $newvar->variation_image = $var_item->attributes-> ... ->URL; // ?

			} else {

				// variation (ASIN) does not exist in listings table - which means it does not exist in the inventory report / ASIN list
				// unless "create all variations" is enabled, skip this variation
				if ( get_option('wpla_import_creates_all_variations') != 1 ) {
					WPLA()->logger->info( "SKIPPED foreign variation - ASIN {$newvar->asin}");
					continue;
				}

				$newvar->qty             = '';
				$newvar->price           = '';
				$newvar->variation_image = '';

				$id = $this->insertVariationListing( $newvar, $product_node, $parent_listing, $account );
				WPLA()->logger->info( "Created new variation child for ASIN {$newvar->asin} - id: $id");
				$newvar->listing_id = $id;
			}

			// fetch variation details from Amazon - set variation_image
			if ( get_option( 'wpla_enable_variation_image_import', 1 ) ) {
				$variation_image = $this->getVariationImageForASIN( $newvar->asin, $account->id );
				$newvar->variation_image = $variation_image;
			}

			$variations[] = $newvar;
		}

		// add vtheme to parent listing
		$lm->updateListing( $parent_listing->id, array( 'vtheme' => $newvar->vtheme ) );

		// echo "<pre>";print_r($variations);echo"</pre>";die();
		$product_node->variations = $variations;
		return $product_node;
	} // parseVariationChildNodes()


	public function getVariationImageForASIN( $asin, $account_id ) {

		// init api
		$api = new WPLA_AmazonAPI( $account_id );

		// get product details from amazon
		$result = $api->getMatchingProductForId( $asin, 'ASIN' );
		$this->request_count++;
		WPLA()->logger->debug( 'getMatchingProductForId() returned:'.print_r($result,1));

		// handle empty result error
		if ( $result->success && ! empty( $result->product->AttributeSets->ItemAttributes->SmallImage->URL ) ) {

			if ( ! empty( $result->product->GetMatchingProductForIdResult->Error->Message ) ) {
				$this->lastError = sprintf( __('There was a problem fetching product details for %s.','wpla'), $listing['asin'] ) . '<br><code>' . $result->product->GetMatchingProductForIdResult->Error->Message . '</code>';
			}

			// fetch image URL
			$img_url = $result->product->AttributeSets->ItemAttributes->SmallImage->URL;

			// get 600px image instead of 75px
			$img_url = str_replace('_SL75_', '_SL600_', $img_url );
			WPLA()->logger->info( "variation image for ASIN {$asin}: $img_url");

			return $img_url;
		}
		WPLA()->logger->warn( "no variation image found for ASIN {$asin}");

		return '';
	} // getVariationImageForASIN()


	public function fixNewVariationListings( $variations, $parent_listing ) {
		WPLA()->logger->info( "fixNewVariationListings()" );

		$lm = new WPLA_ListingsModel();

		// get post_id of parent which has been created by now
		// $parent_listing = $lm->getItemByASIN( $parent_listing->asin );
		$parent_listing = $lm->getItemBySKU( $parent_listing->sku );

		// catch Invalid argument error
		if ( ! is_array($variations) ) {
			WPLA()->logger->error( "no variations found for parent variable ASIN {$parent_listing->asin} (not checked)");
			WPLA()->logger->error( "no variations found for parent variable SKU  {$parent_listing->sku}");
			WPLA()->logger->error( 'variations:'.print_r($variations,1));
			// echo 'Error: no variations found for variable ASIN '.$parent_listing->asin.'<br>';
			// echo "<pre>variations: ";print_r($variations);echo"</pre>";#die();
			return;
		}

		foreach ($variations as $var) {
			$post_id = WPLA_ProductBuilder::getProductIdBySKU( $var->sku );
			if ( ! $post_id ) {
				WPLA()->logger->warn( "fixing SKU {$var->sku} ... no product found for this SKU!!" );
				continue;	
			} 

			$data = array(
				'post_id'   => $post_id,
				'parent_id' => $parent_listing->post_id,
			);
			$lm->updateListing( $var->listing_id, $data );
			WPLA()->logger->info( "fixed SKU {$var->sku} - post_id: $post_id" );
		}

	} // fixNewVariationListings()


	public function insertVariationListing( $var, $product_node, $parent_listing, $account ) {

		// get variation product data
		// $variation_id         = $var['post_id'];
		// echo "<pre>";print_r($var);echo"</pre>";#die();

		// generate title suffix from attribute values
		$suffix = join( ', ', $var->attribute_values );

		// build single variation listing item
		$data = array();
		$data['post_id']       = ''; // filled in after creating the product
		$data['parent_id']     = $parent_listing->post_id;
		$data['vtheme']        = $var->vtheme;
		$data['listing_title'] = $product_node->AttributeSets->ItemAttributes->Title . ' - ' . $suffix;

		// $data['post_content']  = $post->post_content;
		$data['price']         = $var->price;
		$data['quantity']      = $var->qty;
		$data['sku']           = $var->sku;
		$data['asin']          = $var->asin;
		$data['date_created']  = gmdate( 'Y-m-d H:i:s', time() );
		$data['status']        = 'matched';
		$data['source']        = 'foreign_import';
		$data['account_id']    = $account->id;
		$data['product_type']  = 'variation';
		// $data['profile_id']    = '';

		$lm = new WPLA_ListingsModel();
		$variation_listing_id = $lm->insertListingData( $data );

		return $variation_listing_id;
	} // insertVariationListing()





} // class WPLA_ProductsImporter
