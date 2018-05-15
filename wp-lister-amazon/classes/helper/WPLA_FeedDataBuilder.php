<?php

class WPLA_FeedDataBuilder {
	
	var $account;
	var $logger;
	public $result;
	static $locale = array();

	/**
	 * New products feed
	 */

	static function return_csv_object( $csv_body = '', $csv_header = '', $template_type = false ) {

		$csvObj = new stdClass();
		$csvObj->data          = $csv_header . $csv_body;
		$csvObj->template_type = $template_type;
		$csvObj->line_count    = substr_count( $csv_body, "\n" );

		return $csvObj;
	}

	// generate csv feed for prepared products
	static function buildNewProductsFeedData( $items, $account_id, $profile, $append_feed = false ) {

		if ( ! $profile || ! $profile->id ) {
			WPLA()->logger->info('no profile found, falling back to ListingLoader (Offer)');
			return self::buildListingLoaderFeedData( $items, $account_id, $append_feed );
		}

		$template = new WPLA_AmazonFeedTemplate( $profile->tpl_id );
		if ( ! $template || ! $template->id ) {
			WPLA()->logger->info('no template, falling back to ListingLoader (Offer) - tpl_id: '.$profile->tpl_id);
			return self::buildListingLoaderFeedData( $items, $account_id, $append_feed );
		}

		$columns = $template->getFieldNames();
		$profile_fields = maybe_unserialize( $profile->fields );

		// echo "<pre>";print_r($items);echo"</pre>";#die();
		// echo "<pre>";print_r($template);echo"</pre>";#die();
		// echo "<pre>";print_r($profile);echo"</pre>";#die();
		// echo "<pre>";print_r($columns);echo"</pre>";#die();
		// echo "<pre>";print_r($profile_fields);echo"</pre>";#die();

		if ( ! $columns ) {
			WPLA()->logger->error('no columns found in template - tpl_id: '.$profile->tpl_id);
			WPLA()->logger->info('profile: '.print_r($profile,1));
			WPLA()->logger->info('template: '.print_r($template,1));
			WPLA()->logger->info('columns: '.print_r($columns,1));
			WPLA()->logger->info('items: '.print_r($items,1));
			return '';
		}

		// add variation columns 
		// (not really needed - if the template doesn't already have them, variations are probably not allowed or the template is outdated)
		if ( $template->name != 'Offer' ) {

			// $profile_details = maybe_unserialize( $profile->details );
			// $variations_mode = isset( $profile_details['variations_mode'] ) ? $profile_details['variations_mode'] : 'default';

			// if ( $variations_mode != 'flat' ) {
			// 	$columns[] = 'parent-sku';
			// 	$columns[] = 'parentage';
			// 	$columns[] = 'relationship-type';
			// 	$columns[] = 'variation-theme';		
			// }

		}

		// header
		$csv_header  = 'TemplateType='. $template->name . "\t" . 'Version=' . $template->version . str_repeat("\t", sizeof($columns) - 2 ) . "\n";
		$csv_header .= join( "\t", $columns ) . "\n";
		if ($template->name != 'Offer') 
			$csv_header .= join( "\t", $columns ) . "\n";
		$csv_body = '';

		// loop products
		foreach ( $items as $item ) {

			// get WooCommerce product data
			$product_id = $item['post_id'];
			$product = WPLA_ProductWrapper::getProduct( $product_id );
			if ( ! $product ) continue;
			if ( ! $item['sku'] ) continue;
			WPLA()->logger->debug('processing item '.$item['sku'].' - ID '.$product_id);

			// reset row cache
			WPLA()->memcache->clearColumnCache();

			// process product
			foreach ( $columns as $col ) {
				$value = self::parseProductColumn( $col, $item, $product, $profile );
				$value = apply_filters( 'wpla_filter_listing_feed_column', $value, $col, $item, $product, $profile, $template->name );
				$value = str_replace( array("\t","\n","\r"), ' ', $value );	// make sure there are no tabs or line breaks in any field
				$csv_body .= $value . "\t";
				WPLA()->memcache->setColumnValue( wpla_get_product_meta( $product, 'sku' ), $col, $value );
			}
			$csv_body .= "\n";

		}

		// check if any rows were created
		if ( ! $csv_body ) return self::return_csv_object();

		// only return body when appending feed
		if ( $append_feed ) return self::return_csv_object( $csv_body, '', $template->name );

		// return csv object
		return self::return_csv_object( $csv_body, $csv_header, $template->name );

	} // buildNewProductsFeedData()


	static function parseProductColumn( $column, $item, $product, $profile ) {
		wpla_logger_start_timer('parseProductColumn');

		$profile_fields  = $profile ? maybe_unserialize( $profile->fields )  : array();
		$profile_details = $profile ? maybe_unserialize( $profile->details ) : array();
		$variations_mode = isset( $profile_details['variations_mode'] ) ? $profile_details['variations_mode'] : 'default';
		$value           = '';
		$product_id      = wpla_get_product_meta( $product, 'id' );
		$product_type    = wpla_get_product_meta( $product, 'product_type' );
		$product_sku     = wpla_get_product_meta( $product, 'sku' );
		$fba_enabled     = false;

		// handle FBA mode / fallback
        if ( get_option( 'wpla_fba_enabled', 0 ) ) {
            if ( get_option('wpla_fba_enable_fallback') == 1 ) {
                // fallback enabled
                // if there is no FBA qty, FBA will be disabled
                $fba_enabled = $item['fba_quantity'] > 0 ? true : false; // if there is FBA qty, always enable FBA
            } else {
                // fallback disabled
                $fba_enabled = $item['fba_fcid'] && ( $item['fba_fcid'] != 'DEFAULT' ) ; // regard fba_fcid column - ignore stock
            }
        }

		// if fulfillment_center_id is forced to AMAZON_NA in the listing profile,
		// make sure to set fba_enabled to regarding this overwrite in ListingLoader feeds as well
		if ( isset( $profile_fields['fulfillment_center_id'] ) && ! empty( $profile_fields['fulfillment_center_id'] ) ) {
			$fba_enabled = $profile_fields['fulfillment_center_id'] == 'DEFAULT' || $profile_fields['fulfillment_center_id'] == '[---]' ? false : true;
		}

        // set correct post_id for variations
        $post_id = $product_id;
        if ( $product_type == 'variation' ) {
            if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
                $post_id = $product->variation_id;
                $product_id = $product->id;
            } else {
                // set the $product_id to the parent's ID
                $product_id = WPLA_ProductWrapper::getVariationParent( $post_id );
            }
        }

        WPLA()->logger->debug('parseProductColumn ('. $column .') #'. $product_id .'/'. $post_id );

		// get custom product level feed columns - and merge with profile columns
		$custom_feed_columns = get_post_meta( $product_id, '_wpla_custom_feed_columns', true );
		if ( $custom_feed_columns && is_array( $custom_feed_columns ) && ! empty( $custom_feed_columns ) ) {
			$profile_fields = array_merge( $profile_fields, $custom_feed_columns );
		}


		// process hard coded fields
		switch ( $column ) {

			case 'external_product_id':
				$value = get_post_meta( $post_id, '_amazon_product_id', true );
				break;
			
			case 'external_product_id_type':
				$value = get_post_meta( $post_id, '_amazon_id_type', true );
				// // leave id type empty if there is no product id (parent variations) (incompatible with amazon.in)
				// $external_product_id = WPLA()->memcache->getColumnValue( $product->sku, 'external_product_id' );
				// if ( empty( $external_product_id ) ) $value = '[---]';
				break;
			
			case 'sku':			// update feed
			case 'item_sku':    // new items feed
				// $value = $product->sku;
				$value = $item['sku']; // we have to use the item SKU - or feed processing would fail if SKU is different in WooCommerce and WP-Lister
				break;

			case 'price':		// update feed
            case 'standard_price':
				// $value = $product->get_price();			// WC2.1+
				$value = wpla_get_product_meta( $post_id, 'regular_price' );			// WC2.0
				$value = $profile ? $profile->processProfilePrice( $value ) : $value;
				$value = apply_filters( 'wpla_filter_product_price', $value, $post_id, $product, $item, $profile );
				if ( ( $post_id != $product_id ) && ( $product_value = get_post_meta( $product_id, '_amazon_price', true ) ) ) {	// parent price
					if ( $product_value > 0 ) $value = $product_value;
				}
				if ( $product_value = get_post_meta( $post_id, '_amazon_price', true ) ) {											// variation price
					if ( $product_value > 0 ) $value = $product_value;
				}
				$value = $value ? number_format( $value, 2, null, '' ) : $value;

				// make sure price stays within min/max boundaries - prevent errors in PNQ feed
				if ( $item['min_price'] > 0 ) $value = max( $value, $item['min_price'] );
				if ( $item['max_price'] > 0 ) $value = min( $value, $item['max_price'] );

                // Use profile price if value is empty
				//if ( isset( $profile_fields[$column] ) && ! empty( $profile_fields[$column] ) ) $value = $profile_fields[$column];

                // Use profile price only if it exists and _amazon_price is not set #18878 (revised #17624)
                //if ( ! empty( $profile_fields[ $column ] ) && ! $product_value ) {
                //    $value = $profile_fields[ $column ];
                //}

                // Ugh, we've gone full circle now.
                // Revising again because apparently, profile fields need to have the highest priority #20404 #20390
                // So now, the regular price can be overridden by _amazon_price, and that too can be overridden by the profile field
                if ( isset( $profile_fields[$column] ) && ! empty( $profile_fields[$column] ) ) $value = $profile_fields[$column];
				break;

			case 'sale-price':			// update feed
			case 'sale_price':			// new items feed
				// $value = $product->get_sale_price();		// WC2.1+
				$value = wpla_get_product_meta( $post_id, 'sale_price' );				// WC2.0
				$value = $profile ? $profile->processProfilePrice( $value ) : $value;
				$value = apply_filters( 'wpla_filter_sale_price', $value, $post_id, $product, $item, $profile );
				$value = $value ? number_format($value,2, null, '' ) : $value;

				// make sure sale_price is not higher than standard_price / price - Amazon might silently ignore price updates otherwise
				$standard_price = self::getStandardPriceForRow( $product_sku );
				if ( $standard_price && ( $value > $standard_price ) ) $value = '';

				// if no sale price is set, send regular price with sale end date in the past to remove previously sent sale prices
				if ( empty($value) ) $value = $standard_price;

				// make sure price stays within min/max boundaries - prevent Amazon from throwing price alert / validation error (would make listing inactive)
				if ( $item['min_price'] > 0 ) $value = max( $value, $item['min_price'] );
				if ( $item['max_price'] > 0 ) $value = min( $value, $item['max_price'] );

				// Use profile price if set
				if ( isset( $profile_fields[$column] ) && ! empty( $profile_fields[$column] ) ) $value = $profile_fields[$column];

				break;

			case 'sale_from_date':		// new items feed
			case 'sale-start-date':		// update feed

				$date = get_post_meta( $post_id, '_sale_price_dates_from', true );
				if ( $date ) $value = date( 'Y-m-d', $date );

                $has_sale_price = self::hasActiveSalePrice( $product_sku );

				if ( ! $value && $has_sale_price ) {
                    // check for profile shortcodes/values before using filler dates
                    if ( isset( $profile_fields[$column] ) && ! empty( $profile_fields[$column] ) ) {
                        $value = $profile_fields[$column];
                    }
                }

				// if sale price exists but no start date, fill in 2011-01-01
				if ( ! $value && $has_sale_price ) $value = '2011-01-01';

				// fall back to default past date if standard price is set
				$standard_price = self::getStandardPriceForRow( $product_sku );
				if ( ! $value && $standard_price ) $value = '2000-01-01';  // default past date

                // if sale price is intentionally left blank by [---] shortcode, leave sale date blank as well
                $sale_price = self::getSalePriceForRow( $product_sku );

                // Sometimes, sale_from_date will be processed first before sale_price so sale_price
                // will never have a value in self::getSalePriceForRow. Run self::parseProductColumn for the 'sale_price' column once
                // to get the real sale price
                // #18443
                if ( ! $sale_price ) {
                    // try to get the quantity in case it hasn't been processed yet
                    $sale_price = self::parseProductColumn( 'sale_price', $item, $product, $profile );

                    // if it is still FALSE, leave empty
                    if ( ! $sale_price ) $value = '';
                }

				break;

			case 'sale_end_date':		// new items feed
			case 'sale-end-date':		// update feed

				$date = get_post_meta( $post_id, '_sale_price_dates_to', true );
				if ( $date ) $value = date( 'Y-m-d', $date );

                $has_sale_price = self::hasActiveSalePrice( $product_sku );

                if ( ! $value && $has_sale_price ) {
                    // check for profile shortcodes/values before using filler dates
                    if ( isset( $profile_fields[$column] ) && ! empty( $profile_fields[$column] ) ) {
                        $value = $profile_fields[$column];
                    }
                }

				// if sale price exists but no end date, fill in 2029-12-31
				if ( ! $value && $has_sale_price ) $value = '2029-12-31';

				// fall back to default past date if standard price is set
				$standard_price = self::getStandardPriceForRow( $product_sku );
				if ( ! $value && $standard_price ) $value = '2000-01-02';  // default past date

				// if sale price is intentionally left blank by [---] shortcode, leave sale date blank as well
                $sale_price = self::getSalePriceForRow( $product_sku );

                // Sometimes, sale_end_date will be processed first before sale_price so sale_price
                // will never have a value in self::getSalePriceForRow. Run self::parseProductColumn for the 'sale_price' column once
                // to get the real sale price
                // #18443
                if ( ! $sale_price ) {
                    // try to get the quantity in case it hasn't been processed yet
                    $sale_price = self::parseProductColumn( 'sale_price', $item, $product, $profile );

                    // if it is still FALSE, leave empty
                    if ( ! $sale_price ) $value = '';
                }

				break;

			case 'minimum-seller-allowed-price':
				$value = get_post_meta( $post_id, '_amazon_minimum_price', true );
				break;
			case 'maximum-seller-allowed-price':
				$value = get_post_meta( $post_id, '_amazon_maximum_price', true );
				break;

			case 'quantity':
				if ( ! $fba_enabled ) {
				    $parent_id = WPLA_ProductWrapper::getVariationParent( $post_id );
					if ( $product_type == 'variation' && empty( $parent_id ) ) {
						wpla_show_message('<b>Warning: The parent product for variation #'.$post_id.' (SKU '.$item['sku'].') does not exist!</b><br>Please remove that item from WP-Lister and check the integrity of your WooCommerce database.','warn');
						$value = '';
					} else {
					    $value = '';
					    if ( $product_type != 'variable' ) {
					        $value = intval( WPLA_ProductWrapper::getStock( $product ) );
                        }
					}
					// regard WooCommerce's Out Of Stock Threshold option - if enabled
					if ( $out_of_stock_threshold = get_option( 'woocommerce_notify_no_stock_amount' ) ) {
						if ( 1 == get_option( 'wpla_enable_out_of_stock_threshold' ) ) {
							$value = $value - $out_of_stock_threshold;
						}
					}
					if ( $value < 0 ) $value = 0; // amazon doesn't allow negative values

					// allow custom profile value to overwrite WooCommerce quantity
					if ( isset( $profile_fields[$column] ) && ( !empty( $profile_fields[$column] ) || $profile_fields[$column] == 0 ) ) {
					    $value = $profile_fields[$column];
                    }
				}
				break;

            case 'leadtime-to-ship': // For Price & Quantity feed
            case 'fulfillment_latency':
				// if qty is empty, make sure fulfillment_latency is empty as well (prevent error 99006)
                // similarly, unset leadtime-to-ship as well if qty is empty #19125
				$quantity = WPLA()->memcache->getColumnValue( $product_sku, 'quantity' );

				// Sometimes, fulfillment_latency will be processed first before quantity so quantity
                // will never have a value. Run self::parseProductColumn for the 'quantity' column once
                // to get the real quantity
                // #11781
                if ( $quantity === false ) {
                    // try to get the quantity in case it hasn't been processed yet
                    $quantity = self::parseProductColumn( 'quantity', $item, $product, $profile );

                    // if it is still FALSE, leave empty
                    if ( $quantity === false || $quantity === '' ) $value = '[---]';
                } elseif ( $quantity === '' ) {
                    $value = '[---]';
                }

				break;

			case 'bullet_point1':
				$value = self::doTranslate( get_post_meta( $product_id, '_amazon_bullet_point1', true ), $profile->account_id ); break;
			case 'bullet_point2':
				$value = self::doTranslate( get_post_meta( $product_id, '_amazon_bullet_point2', true ), $profile->account_id ); break;
			case 'bullet_point3':
				$value = self::doTranslate( get_post_meta( $product_id, '_amazon_bullet_point3', true ), $profile->account_id ); break;
			case 'bullet_point4':
				$value = self::doTranslate( get_post_meta( $product_id, '_amazon_bullet_point4', true ), $profile->account_id ); break;
			case 'bullet_point5':
				$value = self::doTranslate( get_post_meta( $product_id, '_amazon_bullet_point5', true ), $profile->account_id ); break;
		
			case 'generic_keywords1':
				$value = self::doTranslate( get_post_meta( $product_id, '_amazon_generic_keywords1', true ), $profile->account_id ); break;
			case 'generic_keywords2':
				$value = self::doTranslate( get_post_meta( $product_id, '_amazon_generic_keywords2', true ), $profile->account_id ); break;
			case 'generic_keywords3':
				$value = self::doTranslate( get_post_meta( $product_id, '_amazon_generic_keywords3', true ), $profile->account_id ); break;
			case 'generic_keywords4':
				$value = self::doTranslate( get_post_meta( $product_id, '_amazon_generic_keywords4', true ), $profile->account_id ); break;
			case 'generic_keywords5':
				$value = self::doTranslate( get_post_meta( $product_id, '_amazon_generic_keywords5', true ), $profile->account_id ); break;
		
			// case 'standard_price':
			// 	$value = $product->get_price();
			// 	break;
			
			// case 'sale_price':
			// 	$value = $product->get_sale_price();
			// 	break;

			case 'main_image_url':
			case 'main_offer_image': // BookLoader template
			case 'main-offer-image': // ListingLoader template

                // if gallery mode is set to ignore images, skip this process
                if ( get_option( 'wpla_product_gallery_fallback', 'none' ) == 'ignore' ) {
			        $value = '';
			        break;
                }

                // if offer images are disabled, skip this column
                if ( strstr($column,'offer-image') && get_option( 'wpla_enable_product_offer_images', 0 ) == 0 ) {
                    break;
                }

				// $value      = $product->get_image('full');
                $attachment_id = get_post_thumbnail_id( $post_id );
                $image_url     = wp_get_attachment_image_src( $attachment_id, 'full' );
                $value         = @$image_url[0];

                // maybe fall back to parent variation featured image (disable to avoid the same swatch image for all child variations - ticket #6662)
                if ( empty($value) && $product_type == 'variation' && get_option('wpla_variation_main_image_fallback','parent') == 'parent' ) {
                    $attachment_id = get_post_thumbnail_id( $product_id );
                    $image_url     = wp_get_attachment_image_src( $attachment_id, 'full' );
                    $value         = @$image_url[0];
                }

                // if main image is disabled, use first enabled gallery image
                $disabled_images = array_filter( explode( ',', get_post_meta( $product_id, '_wpla_disabled_gallery_images', true ) ) );

                if ( ! $value || in_array( $attachment_id, $disabled_images ) ) {
                    // $gallery_images = $product->get_gallery_attachment_ids();
                    $gallery_images = WPLA_ProductWrapper::getGalleryAttachmentIDs( $product );
                    $gallery_images = array_values( array_diff( $gallery_images, $disabled_images ) );
                    $gallery_images = apply_filters( 'wpla_product_gallery_attachment_ids', $gallery_images, $post_id );
                    if ( isset( $gallery_images[0] ) ) {
                        $image_url = wp_get_attachment_image_src( $gallery_images[0], 'full' );
                        $value = @$image_url[0];
                    }
                }

                // custom amazon image
                $custom_images = get_post_meta( $product_id, '_amazon_image_gallery', true );
                if ( !empty( $custom_images ) ) {
                    $custom_images = array_filter( array_map( 'trim', explode( ',', $custom_images ) ) );
                    $image_url = wp_get_attachment_image_src( $custom_images[ 0 ], 'full' );
                    $value = @$image_url[0];
                }

                // custom product level column overwrites WooCommerce image
                if ( isset( $profile_fields[$column] ) && ! empty( $profile_fields[$column] ) ) $value = $profile_fields[$column];

	            $value = apply_filters( 'wpla_product_main_image_url', $value, $post_id );
				$value = self::convertImageUrl( $value );
				break;
			
			case 'other_image_url1':
			case 'other_image_url2':
			case 'other_image_url3':
			case 'other_image_url4':
			case 'other_image_url5':
			case 'other_image_url6':
			case 'other_image_url7':
			case 'other_image_url8':
			case 'offer_image1': // BookLoader template
			case 'offer_image2':
			case 'offer_image3':
			case 'offer_image4':
			case 'offer_image5':
			case 'offer-image1': // ListingLoader template
			case 'offer-image2':
			case 'offer-image3':
			case 'offer-image4':
			case 'offer-image5':

                // if gallery mode is set to ignore images, skip this process
                if ( get_option( 'wpla_product_gallery_fallback', 'none' ) == 'ignore' ) {
                    $value = '';
                    break;
                }

                // if offer images are disabled, skip this column
                if ( strstr($column,'offer-image') && get_option( 'wpla_enable_product_offer_images', 0 ) == 0 ) {
                    break;
                }

				if ( 'skip' == get_option( 'wpla_product_gallery_first_image' )) {
					$image_index = substr($column, -1);		// skip first image
				} else {
					$image_index = substr($column, -1) - 1;	// include first image
				}

				// build list of enabled gallery images (attachment_ids)
				$disabled_images = explode( ',', get_post_meta( $product_id, '_wpla_disabled_gallery_images', true ) );
	            // $gallery_images = $product->get_gallery_attachment_ids();
	            $gallery_images = WPLA_ProductWrapper::getGalleryAttachmentIDs( $product );
	            $gallery_images = array_values( array_diff( $gallery_images, $disabled_images ) );
	            $gallery_images = apply_filters( 'wpla_product_gallery_attachment_ids', $gallery_images, $post_id );


	            if ( isset( $gallery_images[ $image_index ] ) ) {
					$image_url = wp_get_attachment_image_src( $gallery_images[ $image_index ], 'full' );
					$value = @$image_url[0];
					$value = self::convertImageUrl( $value );
	            }

                // custom amazon image
                $custom_images = get_post_meta( $product_id, '_amazon_image_gallery', true );
                if ( !empty( $custom_images ) ) {
                    $custom_images = array_filter( array_map( 'trim', explode( ',', $custom_images ) ) );

                    if ( !empty( $custom_images[ $image_index ] ) ) {
                        $image_url = wp_get_attachment_image_src( $custom_images[ $image_index ], 'full' );
                        $value = @$image_url[0];
                    }
                }

				// custom product level column overwrites WooCommerce image
				if ( isset( $profile_fields[$column] ) && ! empty( $profile_fields[$column] ) ) $value = $profile_fields[$column];
				break;


			/* Inventory Loader (delete) feed columns */
			case 'add-delete':
				$value = $item['status'] == 'trash' ? 'x' : 'a';
				break;

			/* Listing Loader feed columns */
			case 'product-id':
				$value = get_post_meta( $post_id, '_wpla_asin', true );
				break;
			case 'product-id-type':
				if ( $matched_asin = get_post_meta( $post_id, '_wpla_asin', true ) ) {
					$value = 'ASIN';
				} elseif ( $custom_id_type = get_post_meta( $post_id, '_amazon_id_type', true ) ) {
					$value = $custom_id_type;
				} else {
					$value = '';
				}
				break;
			case 'condition-type': // update feed (ListingLoader - no profile)
				$value = get_post_meta( $post_id, '_amazon_condition_type', true );

                // fallback to parent's condition type
                if ( ! $value ) {
                    $value = get_post_meta( $product_id, '_amazon_condition_type', true );
                }

				// if this item was imported but has no product level condition, use original report value
				if ( ! $value && $item['source'] == 'imported' ) {
					$report_row = json_decode( $item['details'], true );
					if ( is_array($report_row) && isset( $report_row['item-condition'] ) ) {
						$value = WPLA_ImportHelper::convertNumericConditionIdToType( $report_row['item-condition'] );
					}		
				}

				if ( ! $value && ! isset( $profile_fields[$column] ) ) {
					$value = 'New';	// avoid an empty value for Offer feeds without profile
				}
				break;
			case 'condition_type': // new items feed
				$value = get_post_meta( $post_id, '_amazon_condition_type', true );

			    // fallback to parent's condition type
			    if ( ! $value ) {
                    $value = get_post_meta( $product_id, '_amazon_condition_type', true );
                }
				// if ( ! $value ) $value = 'New';
				break;
			case 'condition-note':
			case 'condition_note': // new items feed
				$value = get_post_meta( $post_id, '_amazon_condition_note', true );

			    // fallback to parent's condition note
                if ( ! $value ) {
                    $value = get_post_meta( $product_id, '_amazon_condition_note', true );
                }

                $value = self::doTranslate( $value, $profile->account_id );
				break;

			/* FBA */
			case 'fulfillment-center-id': // ListingLoader
			case 'fulfillment_center_id': // Category Feed
				if ( $fba_enabled ) {
					$value = $item['fba_fcid'];
				}
				break;

			/* variation columns */
			case 'parent-sku':
			case 'parent_sku':
				if ( $item['parent_id'] ) {
					$parent_product = WPLA_ProductWrapper::getProduct( $item['parent_id'] );
					if ( $parent_product )	
						$value = wpla_get_product_meta( $parent_product, 'sku' );
				}
				if ( $variations_mode == 'flat' ) $value = '';
				break;
			case 'parentage':
			case 'parent_child':
				if ( $product_type == 'variable' ) {
					$value = 'parent';
				} elseif ( $product_type == 'variation' ) {
					$value = 'child';
				}
				if ( $variations_mode == 'flat' ) $value = '';
				break;
			case 'relationship-type':
			case 'relationship_type':
				if ( $product_type == 'variation' )
					$value = 'Variation';
				if ( $variations_mode == 'flat' ) $value = '';
				break;
			case 'variation-theme':
			case 'variation_theme':
				$value = $item['vtheme'];

				// handle empty vtheme for legacy items
				if ( empty( $value ) && in_array( $product_type, array( 'variation', 'variable' ) ) ) {
					$parent_id = $item['parent_id'] ? $item['parent_id'] : $item['post_id'];
					$value     = WPLA_ListingsModel::getVariationThemeForPostID( $parent_id );				
				}

				$value = str_replace('-', '', $value );
				$value = self::convertToEnglishAttributeLabel( $value );
				if ( strtolower($value) == 'colour' )     $value = 'Color';
				if ( strtolower($value) == 'colorsize' )  $value = 'SizeColor';
				if ( strtolower($value) == 'coloursize' ) $value = 'SizeColor';
				if ( $variations_mode == 'flat' )         $value = '';
				if ( isset( $profile_fields[$column] ) && ! empty( $profile_fields[$column] ) ) $value = $profile_fields[$column];

				// Set to empty on simple products #19914
                if ( $product->is_type( 'simple' ) ) {
                    $value = '[---]';
                }
				break;

			
			default:
				# code...
				break;
		}

		// handle variation attribute values / attribute columns
		if ( in_array( $product_type, array('variation','variable') ) ) {
			// if ( ( strpos( $column, '_name') > 0 ) || ( strpos( $column, '_type') > 0 ) ) {
			if ( substr( $column, -5 ) == '_name' || substr( $column, -5 ) == '_type' ) {
				wpla_logger_start_timer('parseVariationAttributeColumn');
				$value = self::parseVariationAttributeColumn( $value, $column, $item, $product );
				wpla_logger_end_timer('parseVariationAttributeColumn');
			}
		}

		// forced empty value (fulfillment_latency)
		// (why is '[---]' == 0 true? should be false - be careful...)
		if ( '[---]' === $value )
			return '';

		// process profile fields - if not empty
		if ( ! isset( $profile_fields[$column] ) || empty( $profile_fields[$column] ) ) {
            return $value;
        }


		// empty shortcode overrides default value
		if ( '[---]' === $profile_fields[$column] )
			return '';

		// use profile value as it is - if $value is still empty (ie. there is no product level value for this column)
		if ( empty($value) )
			$value = $profile_fields[$column];

		// find and parse all placeholders
		if ( preg_match_all( '/\[([^\]]+)\]/', $value, $matches ) ) {
			foreach ($matches[0] as $placeholder) {
				// echo "<pre>processing ";print_r($placeholder);echo"</pre>";
				wpla_logger_start_timer('parseProfileShortcode');
				$value = self::parseProfileShortcode( $value, $placeholder, $item, $product, $post_id, $profile );
				wpla_logger_end_timer('parseProfileShortcode');
				// echo "<pre>";print_r($value);echo"</pre>";#die();
			}
		}

		// parent variations should only have certain columns
		// these three seem to work on Amazon CA / Automotive: item_sku, parent_child, variation_theme
		// but on US and DE, more columns are required:
		// $parent_var_columns = array('item_sku','parent_child','variation_theme'); // CA
		$parent_var_columns = array(
			'item_sku',
			'parent_child',
			'variation_theme',
			'brand_name',
			'item_name',
			'department_name',
			'product_description',
			'item_type',
			'feed_product_type',
			'bullet_point1',		// bullet points should be set for parent variations (confirmed by amazon)
			'bullet_point2',
			'bullet_point3',
			'bullet_point4',
			'bullet_point5',
			'special_features1',
			'special_features2',
			'special_features3',
			'special_features4',
			'special_features5',
			'main_image_url',
			'manufacturer',
			'style_name',
			'closure_type',
			'lifestyle',
			'material_type',
			'material_type1',
			'pattern_type',
			'model_year',
			'shoe_dimension_unit_of_measure',
			'target_audience_keyword',
			'target_audience_keywords1',
			'target_audience_keywords2',
			'target_audience_keywords3',
            'binding',
            'condition_type',
            'publication_date',
            'author',
            'part_number',
		);
		if ( $product_type == 'variable' && ! in_array( $column, $parent_var_columns ) ) {
			$value = '';
		}

		wpla_logger_end_timer('parseProductColumn');
		return $value;
	} // parseProductColumn()


	static public function parseVariationAttributeColumn( $value, $column, $item, $product ) {

		// skip if this is not an actual attribute column (like size_name or color_name)
		if ( in_array( $column, array( 'item_name', 'external_product_id_type', 'feed_product_type', 'brand_name' ) ) ) return $value;

		// adjust some incompatible vtheme values
		$vtheme = $item['vtheme'];
		$vtheme = str_replace( 'Name', '', $vtheme ); 							// ColorName -> Color
		$vtheme = strtolower($vtheme) == 'sizecolor' ? 'Size-Color' : $vtheme; 	// sizecolor -> Size-Color
		$vtheme = strtolower($vtheme) == 'colorsize' ? 'Color-Size' : $vtheme; 	// colorsize -> Color-Size

		$vtheme_array   = explode( '-', $vtheme );
		$col_slug       = str_replace('_name', '', $column);
		$col_slug       = str_replace('_type', '', $col_slug);
		$attribute_name = false;

		// filter attributes used in variation-theme - maybe this should be moved to parseProductColumn() above...
		foreach ($vtheme_array as $vtheme_attribute) {
			$vtheme_attribute = self::convertToEnglishAttributeLabel( $vtheme_attribute );
			if ( $col_slug == strtolower($vtheme_attribute) ) 
				$attribute_name = $vtheme_attribute;
		}
		if ( ! $attribute_name ) return $value;

		// parent product should have empty attributes
		if ( wpla_get_product_meta( $product, 'product_type' ) == 'variable' ) return '';

		// find variation
		// $variations = WPLA_ProductWrapper::getVariations( $product->id );
        $parent_id = WPLA_ProductWrapper::getVariationParent( wpla_get_product_meta( $product, 'id' ) );
        $variations = WPLA()->memcache->getProductVariations( $parent_id );

		foreach ($variations as $var) {
			if ( $var['sku'] == $item['sku'] ) {
				// find attribute value 
				foreach ( $var['variation_attributes'] as $attribute_label => $attribute_value ) {
					$translated_label = self::convertToEnglishAttributeLabel( $attribute_label );
					if ( $translated_label == $attribute_name ) {
						// $value = utf8_decode( $attribute_value ); // Amazon is supposed to use UTF, but de facto accepts only ISO-8859-1/15
						$value = $attribute_value;
					}
				}
				// // find attribute value - doesn't work for non-english attributes
				// if ( isset( $var['variation_attributes'][$attribute_name] ) ) {
				// 	$value = $var['variation_attributes'][$attribute_name];
				// }
			}
		}

		return $value;
	} // parseVariationAttributeColumn()

    static public function doTranslate( $value, $account_id = null ) {
        // qTranslate support
        if ( is_null( $account_id ) ) {
            return $value;
        }

        if ( function_exists( 'qtranxf_use' ) ) {
            if ( !isset( self::$locale[ $account_id ] ) ) {
                $lang = WPLA_AmazonAccount::getAccountLocale( $account_id );
                self::$locale[ $account_id ] = $lang;
            }
            $lang  = self::$locale[ $account_id ];

            $value = qtranxf_use( $lang, $value );
        }

        return $value;
    }


	static public function convertToEnglishAttributeLabel( $value ) {

		// process variation attributes map
		$variation_attribute_map = get_option( 'wpla_variation_attribute_map', array() );
		if ( is_array($variation_attribute_map) ) {
			foreach ( $variation_attribute_map as $woocom_attribute => $amazon_attribute ) {
				$value = str_replace( $woocom_attribute, $amazon_attribute, $value );
			}			
		}

		// translate common attributes
		$value = str_replace('Farbe',  		'Color', $value ); // amazon.de
		$value = str_replace('Größe',  		'Size',  $value );
		$value = str_replace('Grösse', 		'Size',  $value );
		$value = str_replace('Colore', 		'Color', $value ); // amazon.it
		$value = str_replace('Dimensione', 	'Size',  $value );
		$value = str_replace('Misura', 		'Size',  $value );

		return $value;
	} // convertToEnglishAttributeLabel()


	static public function parseProfileShortcode( $original_value, $placeholder, $item, $product, $post_id, $profile ) {
	    // set correct post_id for variations
        $product_id = $post_id;
        if ( wpla_get_product_meta( $product, 'product_type' ) == 'variation' ) {
            if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
                $post_id = $product->variation_id;
                $product_id = $product->id;
            } else {
                // set the $product_id to the parent's ID
                $product_id = WPLA_ProductWrapper::getVariationParent( $post_id );
            }
        }

		switch ( $placeholder ) {
			case '[product_sku]':
				$value = wpla_get_product_meta( $post_id, 'sku' );
				break;
			
			case '[amazon_product_id]':
				$value = get_post_meta( $post_id, '_amazon_product_id', true );
				break;
			
			case '[product_title]':
				$value = $product->get_title();
				if ( $product_value = get_post_meta( $product_id, '_amazon_title', true ) ) {
					$value = str_replace( '%%%', '', $product_value );
				}
				if ( wpla_get_product_meta( $product, 'product_type' ) == 'variation' ) {
					$value = $item['listing_title'];
					if ( 'parent' == get_option('wpla_variation_title_mode') ) {
						$value = $product->get_title();
					}
				}

				$value = self::doTranslate( self::convertTitle( strip_tags( $value ) ), $profile->account_id ); // fix some special characters
				break;
			
			case '[product_price]':
				// $value = $product->get_price();			// WC2.1+
				$value = wpla_get_product_meta( $product, 'regular_price' );			// WC2.0
				$value = $profile ? $profile->processProfilePrice( $value ) : $value;
				$value = apply_filters( 'wpla_filter_product_price', $value, $post_id, $product, $item, $profile );
				if ( ( $post_id != wpla_get_product_meta( $product, 'id' ) ) && ( $product_value = get_post_meta( wpla_get_product_meta( $product, 'id' ), '_amazon_price', true ) ) ) {	// parent price
					if ( $product_value > 0 ) $value = $product_value;
				}
				if ( $product_value = get_post_meta( $post_id, '_amazon_price', true ) ) {											// variation price
					if ( $product_value > 0 ) $value = $product_value;
				}
				$value = $value ? number_format($value,2, null, '' ) : $value;

				// make sure price stays within min/max boundaries - prevent errors in PNQ feed
				if ( $item['min_price'] > 0 ) $value = max( $value, $item['min_price'] );
				if ( $item['max_price'] > 0 ) $value = min( $value, $item['max_price'] );

				break;
			
			case '[product_sale_price]':
				// $value = $product->get_sale_price();		// WC2.1+
				$value = wpla_get_product_meta( $product, 'sale_price' );				// WC2.0
				$value = $profile ? $profile->processProfilePrice( $value ) : $value;
				$value = apply_filters( 'wpla_filter_sale_price', $value, $post_id, $product, $item, $profile );
				$value = $value ? number_format($value,2, null, '' ) : $value;

				// make sure sale_price is not higher than standard_price / price - Amazon might silently ignore price updates otherwise
				$standard_price = self::getStandardPriceForRow( wpla_get_product_meta( $product, 'sku' ) );
				if ( $standard_price && ( $value > $standard_price ) ) $value = '';

				// if no sale price is set, send regular price with sale end date in the past to remove previously sent sale prices
				if ( empty($value) ) $value = $standard_price;

				// make sure price stays within min/max boundaries - prevent Amazon from throwing price alert / validation error (would make listing inactive)
				if ( $item['min_price'] > 0 ) $value = max( $value, $item['min_price'] );
				if ( $item['max_price'] > 0 ) $value = min( $value, $item['max_price'] );

				break;

			case '[product_sale_start]':
				$date = get_post_meta( $post_id, '_sale_price_dates_from', true );
				$value = $date ? date( 'Y-m-d', $date ) : '';

				// if sale price exists but no start date, fill in 2010-01-01
				$has_sale_price = self::hasActiveSalePrice( wpla_get_product_meta( $product, 'sku' ) );
				if ( ! $value && $has_sale_price ) $value = '2010-01-01';
				break;
			case '[product_sale_end]':
				$date = get_post_meta( $post_id, '_sale_price_dates_to', true );
				$value = $date ? date( 'Y-m-d', $date ) : '';

				// if sale price exists but no end date, fill in 2019-01-01
				$has_sale_price = self::hasActiveSalePrice( wpla_get_product_meta( $product, 'sku' ) );
				if ( ! $value && $has_sale_price ) $value = '2019-01-01';
				break;
			
			case '[product_msrp]':
				$value = '';
				if ( $product_value = get_post_meta( $post_id, '_msrp_price', true ) )	// simple product
					$value = $product_value;
				if ( $product_value = get_post_meta( $post_id, '_msrp', true ) )	// variation
					$value = $product_value;
				$value = $value ? number_format($value,2, null, '' ) : $value;
				break;

			case '[product_content]':
				$the_post = get_post( $product_id );
				$value = $the_post->post_content;
				if ( $product_value = trim( get_post_meta( $product_id, '_amazon_product_description', true ) ) ) {
					$value = $product_value;					
				}

				$value = self::doTranslate( self::convertContent( $value ), $profile->account_id );
				break;
			
			case '[product_excerpt]':
				$the_post = get_post( $product_id );
				$value = $the_post->post_excerpt;

                $value = self::doTranslate( self::convertContent( $value ), $profile->account_id );
				break;

			case '[product_weight]':
				$value = wpla_get_product_meta( $post_id, 'weight' );
				$value = $value ? number_format($value,2, null, '' ) : $value;
				break;
			
			case '[product_length]':
				$value = wpla_get_product_meta( $post_id, 'length' );
				break;
			case '[product_width]':
				$value = wpla_get_product_meta( $post_id, 'width' );
				break;
			case '[product_height]':
				$value = wpla_get_product_meta( $post_id, 'height' );
				break;
			
			case '[---]':
				$value = '';
				break;			

			
			default:

				// check for attributes
				if ( substr( $placeholder, 0, 11 ) == '[attribute_' ) {
					wpla_logger_start_timer('processAttributeShortcodes');
					// $value = self::processAttributeShortcodes( $product->id, $placeholder );
					$value = self::processAttributeShortcodes( $product, $placeholder );
					wpla_logger_end_timer('processAttributeShortcodes');
					// $value = utf8_decode( $value ); 						// convert WP UTF-8 to Amazon ISO...
				// check for custom meta shortcodes
				} elseif ( substr( $placeholder, 0, 5 ) == '[meta' ) {
					wpla_logger_start_timer('processCustomMetaShortcodes');
					$value = self::processCustomMetaShortcodes( $product_id, $placeholder, $post_id );
					wpla_logger_end_timer('processCustomMetaShortcodes');
					// $value = utf8_decode( $value ); 						// convert WP UTF-8 to Amazon ISO...
				} else {

					// unregognized shortcodes will use their value
					$value = $placeholder;

					// handle custom shortcodes
					foreach (WPLA()->getShortcodes() as $key => $custom_shortcode) {
						if ( $placeholder != "[$key]") continue;

						if ( isset($custom_shortcode['callback']) && is_callable( $custom_shortcode['callback'] ) ) {

							// handle callback shortcodes registered by wpla_register_profile_shortcode()
							$value = call_user_func( $custom_shortcode['callback'], $post_id, $product, $item, $profile );

						} elseif ( isset($custom_shortcode['content']) && $custom_shortcode['content'] ) {

							// handle custom shortcodes created in advanced settings
							$value = self::convertContent( $custom_shortcode['content'] );

						} 
					}

				}
				$value = self::doTranslate( $value, $profile->account_id );
				break;
		}

		// replace placeholder with value
		$value = str_replace( $placeholder, $value, $original_value );

		return $value;
	} // parseProfileShortcode()


	// get the standard price for current row (by SKU)
	static public function getStandardPriceForRow( $product_sku ) {

		// listing data feed
		$standard_price = WPLA()->memcache->getColumnValue( $product_sku, 'standard_price' );
		if ( $standard_price ) return $standard_price;

		// ListingLoader feed
		$standard_price = WPLA()->memcache->getColumnValue( $product_sku, 'price' );
		if ( $standard_price ) return $standard_price;

		return '';
	} // getStandardPriceForSKU()

	// get the sale price for current row (by SKU)
	static public function getSalePriceForRow( $product_sku ) {

		// listing data feed
		$sale_price = WPLA()->memcache->getColumnValue( $product_sku, 'sale_price' );
		if ( $sale_price ) return $sale_price;

		// ListingLoader feed
		$sale_price = WPLA()->memcache->getColumnValue( $product_sku, 'sale-price' );
		if ( $sale_price ) return $sale_price;

		return '';
	} // getStandardPriceForSKU()


	// check if there is an active sale price (different from the standard price) for current row / SKU
	static public function hasActiveSalePrice( $product_sku ) {

		// check if there is a sale price for this row
		$sale_price = self::getSalePriceForRow( $product_sku );
		if ( ! $sale_price ) return false;

		// if there is a sale price, check if it's different from the standard price
		if ( $sale_price == self::getStandardPriceForRow( $product_sku ) ) return false;

		// yes, there is a sale price
		return true;
	} // hasActiveSalePrice()


	static public function convertTitle( $value ) {

		// convert special / UTF-8 characters
		// $value = htmlentities( $value, ENT_QUOTES, 'UTF-8', false );
		// example: &#039; to '
		$value = htmlspecialchars_decode( $value, ENT_QUOTES );

		return $value;
	} // convertTitle()


	static public function convertContent( $value ) {

		// convert UTF-8 characters
		$value = htmlentities( $value, ENT_QUOTES, 'UTF-8', false );
		$value = htmlspecialchars_decode( $value, ENT_QUOTES );
		// $value = html_entity_decode( $value, ENT_QUOTES, 'UTF-8' );
		// $value = utf8_decode( $value ); 						// convert WP UTF-8 to Amazon ISO...

		// convert some stubborn UTF-8 characters
		// $utf8_char = chr(226) . chr(151) . chr(143); // bullet point (dec)
		$utf8_char = chr(0xE2) . chr(0x97) . chr(0x8F); // bullet point (hex)
		$html_char = '&bull;';
		$value     = str_replace($utf8_char, $html_char, $value);

 		// fixed whitespace pasted from ms word
 		// details: http://stackoverflow.com/questions/1431034/can-anyone-tell-me-what-this-ascii-character-is
		$whitespace = chr(194).chr(160);
		$value      = str_replace( $whitespace, ' ', $value );

		// remove ALL links from post content by default
 		if ( 'default' == get_option( 'wpla_remove_links', 'default' ) ) {
			$value = preg_replace('#<a.*?>(.*?)</a>#i', ' $1 ', $value );
 		}

		// process shortcodes
		$value = self::processShortcodesInContent( $value );

		// clean HTML
		$allowed_tags = get_option('wpla_allowed_html_tags','<b><i>');
        $value = nl2br( trim( strip_tags( $value, $allowed_tags ) ) ); 	// strip html tags, trim excess whitespace, and convert line breaks to <br>
		$value = str_replace( array("\n","\r"), '', $value );	// remove line breaks to keep CSV intact
		$value = str_replace( array("\t"),     ' ', $value );	// replace tabs       to keep CSV intact

		// limit product_description to 2000 characters
		if ( strlen($value) > 2000 ) $value = substr($value, 0, 2000);

		return $value;
	} // convertContent()


	static public function processShortcodesInContent( $html_content ) {

		// process shortcodes in main content
		$process_shortcodes = get_option( 'wpla_process_shortcodes', 'off' );
 		if ( 'off' == $process_shortcodes ) {

 			// off - do nothing, except wpautop() for proper paragraphs
	 		$html_content = wpautop( $html_content );

 		} elseif ( 'do_shortcode' == $process_shortcodes ) {

			// process all wp shortcodes
 			$html_content = do_shortcode( $html_content );

 		} elseif ( 'remove_all' == $process_shortcodes ) {

 			// remove all shortcodes from product description
 			$post_content = $html_content;

			// find and remove all placeholders
			if ( preg_match_all( '/\[([^\]]+)\]/', $post_content, $matches ) ) {
				foreach ($matches[0] as $placeholder) {
			 		$post_content = str_replace( $placeholder, '', $post_content );
				}
			}

			// insert content into template html
	 		$html_content = wpautop( $post_content );

 		} elseif ( 'the_content' == $process_shortcodes ) {

 			// make sure, WooCommerce template functions are loaded (WC2.2)
 			if ( ! function_exists('woocommerce_product_loop_start') && version_compare( WC_VERSION, '2.2', '>=' ) ) {
 				// WC()->include_template_functions(); // won't work unless is_admin() == true
				include_once( dirname( WC_PLUGIN_FILE) . '/includes/wc-template-functions.php' );
 			}

 			// apply the_content filter to make description look the same as in WP
	 		$html_content = apply_filters('the_content', $html_content );

 		}

		return $html_content;
	} // processShortcodesInContent()


	static public function convertImageUrl( $url ) {

		// urlencode utf8 characters in image filename
		$filename_before = basename( $url );
		$filename_after  = rawurlencode( $filename_before );
		$url = str_replace( $filename_before, $filename_after, $url );
		$url = self::removeHttpsFromUrl( $url );

		return $url;
	} // convertImageUrl()


	// Amazon doesn't accept image urls using https
	static function removeHttpsFromUrl( $url ) {

		// fix relative urls
		if ( '/wp-content/' == substr( $url, 0, 12 ) ) {
			$url = str_replace('/wp-content', content_url(), $url);
		}

		// fix https urls
		$url = str_replace( 'https://', 'http://', $url );
		$url = str_replace( ':443', '', $url );

		return $url;
	}


	static public function processCustomMetaShortcodes( $post_id, $field_value, $real_post_id ) {

		// custom meta shortcodes i.e. [meta_Name]
		if ( preg_match_all("/\\[meta_(.*)\\]/uUsm", $field_value, $matches ) ) {

			foreach ( $matches[1] as $meta_name ) {

				$meta_value = get_post_meta( $post_id, $meta_name, true );

				if ( ! $meta_value ) {
					// try real post_id for single variation
					$meta_value = get_post_meta( $real_post_id, $meta_name, true );
				}
				if ( ! $meta_value ) {
					// try with _ prefix if nothing found - prevent user error
					$meta_value = get_post_meta( $post_id, '_'.$meta_name, true );
				}

				$field_value = str_replace( '[meta_'.$meta_name.']', $meta_value,  $field_value );		
			}

		}

		return $field_value;
	} // processCustomMetaShortcodes()


	static public function processAttributeShortcodes( $product, $field_value, $max_length = false ) {

		// child variations: check variation attributes first
        $product_type = wpla_get_product_meta( $product, 'product_type' );
		if ( $product_type == 'variation' ) {

			// match shortcodes - exit if none are found
			if ( ! preg_match_all("/\\[attribute_(.*)\\]/uUsm", $field_value, $matches ) ) return $field_value;

			// get variation attributes
			$variation_attributes = $product->get_variation_attributes();

			foreach ( $matches[1] as $attribute ) {

				$taxonomy_name = 'attribute_pa_'.$attribute;
				if ( isset( $variation_attributes[ $taxonomy_name ] ) && $variation_attributes[ $taxonomy_name ] !== '' ){
					$attribute_slug  = $variation_attributes[ $taxonomy_name ];
					$attribute_value = WPLA_ProductWrapper::getAttributeValueFromSlug( $taxonomy_name, $attribute_slug );
					$field_value     = str_replace( '[attribute_'.$attribute.']', $attribute_value,  $field_value );
				}

			}

		} // if child variation

        $post_id = wpla_get_product_meta( $product, 'id' );
        if ( $product_type == 'variation' ) {
		    // pull the parent ID
            $post_id = WPLA_ProductWrapper::getVariationParent( $post_id );
        }


		// match shortcodes (again, because they may already been processed)
		if ( preg_match_all("/\\[attribute_(.*)\\]/uUsm", $field_value, $matches ) ) {

			// $product_attributes = WPLA_ProductWrapper::getAttributes( $post_id );
			// WPLA()->logger->debug('processAttributeShortcodes() - product_attributes: '.print_r($product_attributes,1));
			// WPLA()->logger->debug('called getAttributes() for post_id '.$post_id.' - field: '.$field_value);

			// process attribute shortcodes i.e. [attribute_Brand]
			$product_attributes = WPLA()->memcache->getProductAttributes( $post_id );

			foreach ( $matches[1] as $attribute ) {

				if ( isset( $product_attributes[ 'pa_'.$attribute ] )){
					$attribute_value = $product_attributes[ 'pa_'.$attribute ];
				} else {					
					$attribute_value = '';
				}
				$processed_html = str_replace( '[attribute_'.$attribute.']', $attribute_value,  $field_value );

				// check if string exceeds max_length after processing shortcode
				// if ( $max_length && ( $this->mb_strlen( $processed_html ) > $max_length ) ) {
				// 	$attribute_value = '';
				// 	$processed_html = str_replace( '[attribute_'.$attribute.']', $attribute_value,  $field_value );
				// }

				$field_value = $processed_html;

			}

		}
		// WPLA()->logger->info('processAttributeShortcodes() - return value: '.print_r($field_value,1));

		return $field_value;
	} // processAttributeShortcodes()



	/**
	 * Price and Quantity update feed
	 */

	// generate csv feed for updated products
	static function buildPriceAndQuantityFeedData( $items, $account_id ) {
		// echo "<pre>";print_r($items);echo"</pre>";#die();

		// build csv
		$columns = array( 
			'sku', 
			'price', 
			'minimum-seller-allowed-price', 
			'maximum-seller-allowed-price', 
			'quantity', 
			'leadtime-to-ship' 
		);
		$csv_header = join( "\t", $columns ) . "\n";
		$csv_body = '';

		$feed_currency_format = get_option( 'wpla_feed_currency_format', 'auto' );
		$account    		  = new WPLA_AmazonAccount( $account_id );
		$account_market_code  = $account->market_code;

		foreach ( $items as $item ) {

			// get WooCommerce product data
			$product_id = $item['post_id'];
			$product = WPLA_ProductWrapper::getProduct( $product_id );
			if ( ! $product ) continue;
			if ( ! $item['sku'] ) continue;
			if ( wpla_get_product_meta( $product, 'product_type' ) == 'variable' ) {
				WPLA_ListingsModel::updateWhere( array( 'id' => $item['id'] ), array( 'pnq_status' => 0 ) );
				continue; // skip parent variations in P&Q feed
			}
			// echo "<pre>";print_r($product);echo"</pre>";#die();

			// load profile fields
			$profile  		= new WPLA_AmazonProfile( $item['profile_id'] );
			$profile_fields = $profile ? maybe_unserialize( $profile->fields ) : array();

			foreach ( $columns as $col ) {
				$value = self::parseProductColumn( $col, $item, $product, $profile );
				$value = self::convertCurrencyFormat( $value, $col, $feed_currency_format, $account_market_code );
				$value = apply_filters( 'wpla_filter_listing_feed_column', $value, $col, $item, $product, $profile, false );
				$csv_body .= $value . "\t";
			}
			$csv_body .= "\n";

			// $csv_body .= $item['sku'] . "\t";
			// // $csv_body .= 'TEST_NO_SKU' . "\t";
			// $csv_body .= $item['price'] . "\t";
			// $csv_body .= "\t";
			// $csv_body .= "\t";
			// $csv_body .= $item['quantity'] . "\t";
			// $csv_body .= "\t";
			// $csv_body .= "\n"; // EOL
		}

		// check if any rows were created
		if ( ! $csv_body ) return self::return_csv_object();

		// return csv object
		return self::return_csv_object( $csv_body, $csv_header );
	} // buildPriceAndQuantityFeedData()


	// convert prices to use decimal comma
	static function convertCurrencyFormat( $price, $col, $feed_currency_format, $account_market_code ) {

		// convert if auto mode is enabled and this is a price column
		if ( $feed_currency_format != 'auto' ) return $price;
		if ( ! in_array( $col, array('price','minimum-seller-allowed-price','maximum-seller-allowed-price') ) ) return $price;
		if ( ! in_array( $account_market_code, array('DE','FR','IT','ES') ) ) return $price;

		// convert to decimal comma
		$price = str_replace( '.', ',', $price );

		return $price;
	} // convertCurrencyFormat()


	/**
	 * Inventory Loader feed (for trashed listings)
	 */

	// generate csv feed for trashed products
	static function buildInventoryLoaderFeedData( $items, $account_id ) {
		// echo "<pre>";print_r($items);echo"</pre>";#die();

		// build csv
		$columns = array( 
			'sku', 
			'product-id', 
			'product-id-type', 
			'price', 
			'minimum-seller-allowed-price', 
			'maximum-seller-allowed-price', 
			'item-condition', 
			'quantity', 
			'add-delete', 
			'will-ship-internationally', 
			'expedited-shipping', 
			// 'standard-plus', 
			'item-note', 
			'fulfillment-center-id', 
			// 'product-tax-code', 
			// 'leadtime-to-ship'
		);
		$csv_header = join( "\t", $columns ) . "\n";
		$csv_body = '';

		foreach ( $items as $item ) {

			// get WooCommerce product data
			$product_id = $item['post_id'];
			$product = WPLA_ProductWrapper::getProduct( $product_id );
			if ( ! $product ) continue;
			if ( ! $item['sku'] ) continue;
			// echo "<pre>";print_r($product);echo"</pre>";#die();

			// load profile fields
			$profile  		= new WPLA_AmazonProfile( $item['profile_id'] );
			$profile_fields = $profile ? maybe_unserialize( $profile->fields ) : array();

			foreach ( $columns as $col ) {
				$value = self::parseProductColumn( $col, $item, $product, $profile );
				// $value = apply_filters( 'wpla_filter_listing_feed_column', $value, $col, $item, $product, $profile, false );
				$csv_body .= $value . "\t";
			}
			$csv_body .= "\n";

		}

		// check if any rows were created
		if ( ! $csv_body ) return self::return_csv_object();

		// return csv object
		return self::return_csv_object( $csv_body, $csv_header );
	} // buildInventoryLoaderFeedData()



	/**
	 * Listing Loader update feed
	 */

	// generate csv feed for updated products
	static function buildListingLoaderFeedData( $items, $account_id, $append_feed = false ) {

		// build csv
		$columns = array( 
			'sku', 				// required columns
			'price', 
			'quantity', 
			'product-id', 
			'product-id-type', 
			'condition-type', 
			'condition-note', 
			'ASIN-hint', 		// optional columns
			'title', 
			'product-tax-code', 
			'operation-type', 
			'sale-price', 
			'sale-start-date', 
			'sale-end-date', 
			'leadtime-to-ship', 
			'launch-date', 
			'is-giftwrap-available', 
			'is-gift-message-available', 
			'fulfillment-center-id', 
			'main-offer-image', 
			'offer-image1', 
			'offer-image2', 
			'offer-image3', 
			'offer-image4', 
			'offer-image5', 
		);

		$template_name    = 'Offer';
		$template_version = '1.4';
		$csv_header       = 'TemplateType='. $template_name . "\t" . 'Version=' . $template_version . str_repeat("\t", sizeof($columns) - 2 ) . "\n";
		$csv_header       .= join( "\t", $columns ) . "\n";
		$csv_body         = '';

		foreach ( $items as $item ) {

			// get WooCommerce product data
			$product_id = $item['post_id'];
			$product = WPLA_ProductWrapper::getProduct( $product_id );
			if ( ! $product ) continue;
			if ( ! $item['sku'] ) continue;
			// echo "<pre>";print_r($product);echo"</pre>";#die();
			WPLA()->logger->debug('processing ListingLoader item '.$item['sku'].' - ID '.$product_id);

			// load profile fields
			$profile  		= new WPLA_AmazonProfile( $item['profile_id'] );
			$profile_fields = $profile ? maybe_unserialize( $profile->fields ) : array();

			// reset row cache
			WPLA()->memcache->clearColumnCache();

			foreach ( $columns as $col ) {
				$value = self::parseProductColumn( $col, $item, $product, $profile );
				$value = apply_filters( 'wpla_filter_listing_feed_column', $value, $col, $item, $product, $profile, $template_name );
				$value = str_replace( array("\t","\n","\r"), ' ', $value );	// make sure there are no tabs or line breaks in any field
				$csv_body .= $value . "\t";
				WPLA()->memcache->setColumnValue( wpla_get_product_meta( $product, 'sku' ), $col, $value );
			}
			$csv_body .= "\n";

		}

		// check if any rows were created
		if ( ! $csv_body ) return self::return_csv_object();

		// only return body when appending feed
		if ( $append_feed ) return self::return_csv_object( $csv_body, '', $template_name );

		// return csv object
		return self::return_csv_object( $csv_body, $csv_header, $template_name );

	} // buildListingLoaderFeedData()







	/**
	 * Order Fulfillment feed
	 */

	// generate csv feed for shipped order
	static function buildShippingFeedData( $post_id, $order_id, $account_id, $include_header = true ) {

		// build csv
		$columns = array( 
			'order-id', 		// required
			'order-item-id', 
			'quantity', 
			'ship-date', 		// required
			'carrier-code', 
			'carrier-name', 
			'tracking-number', 
			'ship-method'
		);
		$csv_header = join( "\t", $columns ) . "\n";
		$csv_body = '';

		// reset row cache
		WPLA()->memcache->clearColumnCache();

		foreach ( $columns as $col ) {
			$value = self::parseOrderColumn( $col, $post_id );
			$csv_body .= $value . "\t";
			WPLA()->memcache->setColumnValue( 'shipfeed_oid_'.$post_id, $col, $value );
		}
		$csv_body .= "\n";

		// check if header should be included
		if ( $include_header && $csv_body )
			return $csv_header . $csv_body;

		return $csv_body;
	} // buildShippingFeedData()


	static function parseOrderColumn( $column, $post_id ) {
		$value = '';

		switch ( $column ) {

			case 'order-id':
				$value = get_post_meta( $post_id, '_wpla_amazon_order_id', true );
				break;
			
			case 'ship-date':
				// $value = get_post_meta( $post_id, '_wpla_date_shipped', true );
				// $value .= ' 00:01:23'; // without this, amazon would use 07:00:00 UTC by default

				$date = get_post_meta( $post_id, '_wpla_date_shipped', true );
				$time = get_post_meta( $post_id, '_wpla_time_shipped', true );

				if ( DateTime::createFromFormat('Y-m-d H:i:s', $date.' '.$time) ) {

					// convert date/time from UTC to local timezone
					$tz = WPLA_DateTimeHelper::getLocalTimeZone();
					$dt = new DateTime( $date.' '.$time, new DateTimeZone( 'UTC' ) );
					$dt->setTimeZone( new DateTimeZone( $tz ) );
					$date = $dt->format('Y-m-d');
					$time = $dt->format('H:i:s');

				} else {

					// get current date in local timezone
					$tz   = WPLA_DateTimeHelper::getLocalTimeZone();
					$dt   = new DateTime( 'now', new DateTimeZone( $tz ) );
					$date = $dt->format('Y-m-d'); // add current date in local timezone					

				}

                $value = $date;

                if ( 1 == get_option( 'wpla_feed_include_shipment_time', 0 ) ) {
                    $value = $date . ' ' . $time . 'Z';
                }

				/*
				$dt   = new DateTime( 'now', new DateTimeZone('UTC') );

				if ( ! $date ) {
					$date = $dt->format('Y-m-d'); // add current date in UTC
				}
				if ( ! $time ) {
					$time = $dt->format('H:i:s'); // add current time in UTC
					// $time .= '+00:00' 	      // UTC (works as well as 'Z')
					// $time .= 'Z'; 			  // Z stands for UTC timezone
				}

				$value = $date . ' ' . $time . 'Z';
				*/
				break;
			
			case 'carrier-code':
				$value = get_post_meta( $post_id, '_wpla_tracking_provider', true );

				if ( empty( $value ) && $tracking = self::getThirdPartyPluginTrackingData( $post_id ) ) {
					$value = 'Other';
				}
				break;
			
			case 'carrier-name':
				$carrier = WPLA()->memcache->getColumnValue( 'shipfeed_oid_'.$post_id, 'carrier-code' );				
				$value   = get_post_meta( $post_id, '_wpla_tracking_service_name', true );

				if ( empty( $value ) && $tracking = self::getThirdPartyPluginTrackingData( $post_id ) ) {
					$value = $tracking->provider;
				}

				if ( empty($value) && $carrier == 'Other' ) {
					$value = get_option( 'wpla_default_shipping_service_name', '' );
					if ( empty($value) ) $value = 'N/A'; // we can't leave carrier-name empty if carrier-code is 'Other'
				}
				break;
			
			case 'tracking-number':
				$value = get_post_meta( $post_id, '_wpla_tracking_number', true );

				if ( empty( $value ) && $tracking = self::getThirdPartyPluginTrackingData( $post_id ) ) {
					$value = $tracking->number;
				}
				break;
			
			default:
				# code...
				break;
		}

		return $value;
	} // parseOrderColumn()


	// helper method to determine if tracking data was set by 3rd party plugins
	static function getThirdPartyPluginTrackingData( $post_id ) {

        // check meta fields used by WooCommerce Shipment Tracking plugin and Shipstation plugin
		$_tracking_number   = get_post_meta( $post_id, '_tracking_number', true );
		$_tracking_provider = get_post_meta( $post_id, '_tracking_provider', true );

		// check custom carrier code used by Shipment Tracking
		if ( empty( $_tracking_provider ) ) {
			$_tracking_provider = get_post_meta( $post_id, '_custom_tracking_provider', true );
		}

		// return false unless both number and provider are set
		if ( empty( $_tracking_number   ) ) return false;
		if ( empty( $_tracking_provider ) ) return false;

		// return value pair as object
		$tracking = new stdClass();
		$tracking->number   = $_tracking_number;
		$tracking->provider = $_tracking_provider;

		return $tracking;
	}


	/**
	 * Flat File FBA Shipment Injection Fulfillment Feed	
	 */

	// generate csv feed for shipped order
	static function buildFbaSubmissionFeedData( $post_id, $_order, $order_item, $listing, $account_id, $include_header = true ) {

		// build csv
		$columns = array( 
			'MerchantFulfillmentOrderID', 		// required
			'DisplayableOrderID', 				// required
			'DisplayableOrderDate', 			// required
			'MerchantSKU', 						// required
			'Quantity', 						// required
			'MerchantFulfillmentOrderItemID', 	// required
			'GiftMessage', 
			'DisplayableComment', 
			'PerUnitDeclaredValue', 
			'DisplayableOrderComment', 			// required
			'DeliverySLA', 						// required
			'AddressName', 						// required
			'AddressFieldOne', 					// required
			'AddressFieldTwo', 
			'AddressFieldThree', 
			'AddressCity', 						// required
			'AddressCountryCode', 				// required
			'AddressStateOrRegion', 			// required
			'AddressPostalCode', 				// required
			'AddressPhoneNumber', 
			'NotificationEmail', 
			'FulfillmentAction', 
		);
		$csv_header = join( "\t", $columns ) . "\n";
		$csv_body = '';

		foreach ( $columns as $col ) {
			$value = self::parseFbaSubmissionColumn( $col, $post_id, $_order, $order_item, $listing );
			$csv_body .= $value . "\t";
		}
		$csv_body .= "\n";

		// check if header should be included
		if ( $include_header && $csv_body )
			return $csv_header . $csv_body;

		return $csv_body;
	} // buildFbaSubmissionFeedData()


	static function parseFbaSubmissionColumn( $column, $post_id, $_order, $order_item, $listing ) {
		$value      = '';
		$order_id   = wpla_get_order_meta( $_order, 'id' );
        $company    = wpla_get_order_meta( $_order, 'shipping_company' );

		switch ( $column ) {

			case 'MerchantFulfillmentOrderID':
				// $value = $_order->id;
				$value = $_order->get_order_number();
				update_post_meta( $order_id, '_wpla_fba_MerchantFulfillmentOrderID', $value ); // store custom order number for later
				break;

			case 'DisplayableOrderID':
				$value = $_order->get_order_number();
				break;
			
			case 'DisplayableOrderDate':
				$value = wpla_get_order_meta( $_order, 'order_date' );
				$value = str_replace( ' ', 'T', $value ); 
				break;
			
			case 'MerchantSKU':
				$value = $listing->sku;
				break;
			
			case 'Quantity':
				$value = $order_item['qty'];
				break;
			
			case 'MerchantFulfillmentOrderItemID':
				$value = $post_id; // or use order line item id
				break;
			
			case 'PerUnitDeclaredValue':
				$value = $order_item['line_total'];
				break;
			
			case 'DisplayableOrderComment':
				$value = get_post_meta( $order_id, '_wpla_DisplayableOrderComment', true );
				if ( empty( $value ) ) $value = get_option( 'wpla_fba_default_order_comment' );
				if ( empty( $value ) ) $value = 'Thank you for your purchase.';
				break;
			
			case 'DeliverySLA':
				$value = get_post_meta( $order_id, '_wpla_DeliverySLA', true );
				if ( empty( $value ) ) $value = get_option( 'wpla_fba_default_delivery_sla' );
				if ( empty( $value ) ) $value = 'Standard';
				break;
			
			case 'AddressName':
				$value = wpla_get_order_meta( $_order, 'shipping_first_name' ) . ' ' . wpla_get_order_meta( $_order, 'shipping_last_name' );
				break;
			
			case 'AddressFieldOne':
				$value = !empty( $company ) ? $company : wpla_get_order_meta( $_order, 'shipping_address_1' );
				break;
			
			case 'AddressFieldTwo':
                $value =  !empty( $company ) ? wpla_get_order_meta( $_order, 'shipping_address_1' ) : wpla_get_order_meta( $_order, 'shipping_address_2' );
				break;
			
			case 'AddressFieldThree':
				$value = !empty( $company ) ? wpla_get_order_meta( $_order, 'shipping_address_2' ) : '';
				break;
			
			case 'AddressCity':
				$value = wpla_get_order_meta( $_order, 'shipping_city' );
				break;
			
			case 'AddressCountryCode':
				$value = wpla_get_order_meta( $_order, 'shipping_country' );
				break;
			
			case 'AddressStateOrRegion':
				$value = wpla_get_order_meta( $_order, 'shipping_state' );
				break;
			
			case 'AddressPostalCode':
				$value = wpla_get_order_meta( $_order, 'shipping_postcode' );
				break;
			
			case 'AddressPhoneNumber':
				$value = wpla_get_order_meta( $_order, 'billing_phone' );
				break;
			
			case 'NotificationEmail':
				// $value = get_post_meta( $_order->id, '_billing_email', true );
				$value = get_post_meta( $order_id, '_wpla_NotificationEmail', true );
				if ( empty( $value ) && ( get_option('wpla_fba_default_notification') ) ) {
					$value = wpla_get_order_meta( $_order, 'billing_email' );
				}
				break;
			
			case 'FulfillmentAction':
				$status = $_order->get_status();
				$value = ''; // default value: Ship
				if ( $status == 'on-hold' ) $value = 'Hold';
				break;
			
			default:
				# code...
				break;
		}

		return $value;
	} // parseFbaSubmissionColumn()


} // class WPLA_FeedDataBuilder
