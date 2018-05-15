<?php

class WPLA_FeedValidator {
	
	// check prepared products for missing SKU, price, qty
	static function checkPreparedProducts( $return_array = false ) {

		// check all prepared products
		$problems = self::checkAllProductsWithStatus( 'prepared' );

		if ( $return_array )
			return $problems;

		return self::returnHTML( $problems );
	} // checkPreparedProducts()


	// check changed products for missing SKU, price, qty
	static function checkChangedProducts( $return_array = false ) {

		// check all changed products
		$problems = self::checkAllProductsWithStatus( 'changed' );

		if ( $return_array )
			return $problems;

		return self::returnHTML( $problems );
	} // checkChangedProducts()


	// check products by status
	static function checkAllProductsWithStatus( $status ) {
		$problems = array();

		// get all prepared products
		$lm = new WPLA_ListingsModel();
		$listings = $lm->findAllListingsByColumn( $status, 'status' );
		// echo "<pre>";print_r($listings);echo"</pre>";#die();

		// get all post_ids
		global $wpdb;
		$post_ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' OR post_type = 'product_variation' ");

		foreach ($listings as $listing_id => $item) {
            $real_item_id = ( $item->parent_id ) ? $item->parent_id : $item->post_id;
			
			// check if product exists
			if ( ! in_array( $item->post_id, $post_ids ) ) {
				$problems[] = array(
					'msg'     => 'The product "'.$item->listing_title.'" does not exist in WooCommerce and will not be included in the next feed submission.',
					'post_id' => $real_item_id
				);
			}

			// check SKU - all products
			if ( $item->sku == '' ) {
				$problems[] = array(
					'msg'     => 'The product "'.$item->listing_title.'" has no SKU and will not be included in the next feed submission.',
					'post_id' => $real_item_id
				);
			}
			if ( strlen( $item->sku ) > 40 ) {
				$problems[] = array(
					'msg'     => 'The SKU <b>'.$item->sku.'</b> for product "'.$item->listing_title.'" is longer than 40 characters. Amazon requires SKUs to have 40 characters or less.',
					'post_id' => $real_item_id
				);
			}

			// run checks for variable or simple product
			if ( $item->product_type == 'variable' ) {
				// $problems = self::checkVariableProduct( $item, $problems );
			} elseif ( $item->product_type == 'variation' ) {
				// $problems = self::checkSimpleProduct( $item, $problems );
			} else {
				$problems = self::checkSimpleProduct( $item, $problems );
			}

		} // foreach listing

		return $problems;
	} // checkPreparedProducts()


	// check simple product details
	static function checkSimpleProduct( $item, $problems ) {

		// check price
		if ( ! $item->price ) {
			$problems[] = array(
				'msg'     => 'The product "'.$item->listing_title.'" ('.$item->sku.') has no price set and will not be available for sale on Amazon.',
				'post_id' => $item->post_id
			);
		}

		// don't warn about zero stock for changed items
		if ( $item->status == 'changed' ) return $problems;

		// check quantity
		if ( ! $item->quantity ) {
			$problems[] = array(
				'msg'     => 'The product "'.$item->listing_title.'" ('.$item->sku.') is not in stock and will not be available for sale on Amazon.',
				'post_id' => $item->post_id
			);
		}

		return $problems;
	} // checkSimpleProduct()


	// check variable product details
	static function checkVariableProduct( $item, $problems ) {

		// get variations
		$product                  = WPLA_ProductWrapper::getProduct( $item->post_id );
		$variation_ids            = $product->get_children();
		$missing_variation_fields = array();
		// foreach ( $variation_ids as $variation_id ) {
		// 	$_product = get_product( $variation_id );
		// 	$var_info = " (#$variation_id)";

		// 	// Sale Price Dates
		// 	if ( $_product->sale_price ) {
		// 		if ( ! get_post_meta( $variation_id, '_sale_price_dates_from', true ) )
		// 			$missing_variation_fields[] = __('Sale start date','wpla') . $var_info;
		// 		if ( ! get_post_meta( $variation_id, '_sale_price_dates_to', true ) )
		// 			$missing_variation_fields[] = __('Sale end date','wpla') . $var_info;
		// 	}

		// } // foreach variation

		if ( ! empty( $missing_variation_fields) ) {
			$problems[] = array(
				'msg'     => 'Some variations for "'.$item->listing_title.'" ('.$item->sku.') are missing the following fields required to be listed on Amazon: '.' <b>'. join($missing_variation_fields, ', ') . '</b>',
				'post_id' => $item->post_id
			);
		}

		return $problems;
	} // checkVariableProduct()


	// prepare HTML message from array
	static function returnHTML( $problems ) {
		if ( empty($problems) ) return '';

		$msg = sprintf( __('Warning: There are %s problem(s) with your prepared products:','wpla'), count($problems) ) . '<br><br>';
		foreach ($problems as $problem) {
			$msg .= $problem['msg'] . ' &nbsp; ';
			$msg .= sprintf('<a href="post.php?post=%s&action=edit" class="button button-small">%s</a>', $problem['post_id'], 'Edit product' ) . '<br>';
		}

		return $msg;
	} // returnHTML()


	static function formatAmazonFeedError( $feed_error ) {

        $error_message = WPLA_FeedValidator::explainAmazonError( $feed_error['error-code'], $feed_error['error-message'] );
		$error_message = '<b>'.$feed_error['error-type'].' '.$feed_error['error-code'].':</b> '.self::convert_links( $error_message );

		if ( isset($feed_error['feed_id']) ) {
		    // get feed permalink
			$feed_id        = $feed_error['feed_id'];
			$feed_permalink = admin_url( 'admin-ajax.php?action=wpla_feed_details' ) . '&id='.$feed_id.'&sig='.md5( $feed_id . get_option('wpla_instance') );
			$error_message .= '&nbsp;<a href="'.$feed_permalink.'" title="inspect feed details" target="_blank">[&raquo]</a>';
		}

		return $error_message;
	} // formatAmazonFeedError()


	static function explainAmazonError( $error_code, $error_message ) {

		switch ( $error_code ) {

			// Error 8560
			// SKU ########, Missing Attributes standard_product_id,missing_keyset_reason. SKU ###### does not match any ASIN. 
			// Creation of a new ASIN requires the following missing attributes: standard_product_id,missing_keyset_reason.
			case 8560:

				// $extra_msg = 'This message is Amazon\'s way of telling you that your UPC / EAN is incorrect.';
				$extra_msg = 'Please check if the UPC / EAN is invalid or missing.';
				$error_message .= ' <br><b>'.$extra_msg.'</b>';
				break;
			
			// Error 8541
			// SKU SKU123, ASIN B00327P2O2, ('item_name' Merchant: 'Green Hexagon Ceramic Garden Stool' / Amazon: 'Hexagon Garden Stool Lattice Design - Seaweed', 'manufacturer' Merchant: 'Emissary' / Amazon: '', 'part_number' Merchant: 'SKU123' / Amazon: '', 'brand' Merchant: 'Emissary' / Amazon: 'Lattice'). 
			// The product_id provided with SKU123 corresponds to ASIN B00327P2O2, but some of the information submitted contradicts information in the Amazon catalog. 
			// If your product is the same as this ASIN, please modify your product data to reflect the following Amazon catalog values and resubmit. 
			// If your product is different than the ASIN, please check that the product_id is correct. If it is correct, please contact Seller Support for proper resolution.
			// Feed ID: 0 For details, see http://sellercentral.amazon.com/gp/errorcode/8541
			case 8541:

				$extra_msg = '';

				if ( preg_match('/\((.*)\)\./', $error_message, $matches ) ) {
					$rows = explode( ', ', $matches[1] );
					$extra_msg = '<b>Contradicting product details on Amazon</b><hr>';

					foreach ($rows as $row) {
						$row = str_replace("'", '"', $row);
						// echo "<pre>";print_r($row);echo"</pre>";#die();
	
						if ( preg_match('/"(.*)" Merchant: "(.*)" \/ Amazon: "(.*)"/', $row, $matches ) ) {
							$extra_msg .= '<b>Field: '.$matches[1].'</b><br>';
							$extra_msg .= 'You: '.$matches[2].'<br>';
							$extra_msg .= 'Amz: '.$matches[3].'<hr>';
						}
						// echo "<pre>";print_r($matches);echo"</pre>";die();
					}
				}

				$error_message = $extra_msg . $error_message;
				break;
			
		}

		return $error_message;
	} // explainAmazonError()


    // convert URLs to links
    static function convert_links($text) {
        // http://stackoverflow.com/questions/1960461/convert-plain-text-urls-into-html-hyperlinks-in-php
        $text = preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $text);
        return $text;
    }


    // check if a number is a valid SKU (only letters, numbers and ._-/ for now)
    static function isValidSKU( $value ) {
		if ( preg_match( '/[^a-z_\-\.\/0-9]/i', $value ) ) {
			return false;
		}

		// and SKUs cannot start with a 0 #15171
        if ( strpos( $value, '0' ) === 0 ) {
		    return false;
        }
    	return true;
    }


    // check if a number is a valid UPC or EAN
    static function isValidEANorUPC( $value ) {

        // Must consist of 12 (UPC) or 13 (EAN) digits
        if ( ! preg_match( '/^\d{12,13}$/', $value ) ) {
            return;
        }

	    // validate EAN
	    if ( preg_match('/^[0-9]{13}$/', $value) ) {
	        return self::validate_EAN13( $value );
	    }

	    // validate UPC
		$lastDigitIndex = strlen($value) - 1;
		$accumulator    = 0;
		$checkDigit     = (int) $value[ $lastDigitIndex ];

        // reverse the actual digits (excluding the check digit)
        $str = strrev( substr( $value, 0, $lastDigitIndex ) );

        /**
         *  Moving from right to left
         *  Even digits are just added
         *  Odd digits are multiplied by three
         */
        $accumulator = 0;
        for ( $i = 0; $i < $lastDigitIndex; $i++ ) {
            $accumulator += $i % 2 ? (int) $value[$i] : (int) $value[$i] * 3;
        }

        $checksum = ( 10 - ($accumulator % 10) ) % 10;

        if ( $checksum !== $checkDigit ) {
            return false;
        }

        return true;
    } // isValidEANorUPC()

	static function validate_EAN13($digits) {

	    // check to see if barcode is 13 digits long
	    if (!preg_match("/^[0-9]{13}$/", $digits)) {
	        return false;
	    }

	    // 1. Add the values of the digits in the 
	    // even-numbered positions: 2, 4, 6, etc.
	    $even_sum = $digits[1] + $digits[3] + $digits[5] +
	                $digits[7] + $digits[9] + $digits[11];

	    // 2. Multiply this result by 3.
	    $even_sum_three = $even_sum * 3;

	    // 3. Add the values of the digits in the 
	    // odd-numbered positions: 1, 3, 5, etc.
	    $odd_sum = $digits[0] + $digits[2] + $digits[4] +
	               $digits[6] + $digits[8] + $digits[10];

	    // 4. Sum the results of steps 2 and 3.
	    $total_sum = $even_sum_three + $odd_sum;

	    // 5. The check character is the smallest number which,
	    // when added to the result in step 4, produces a multiple of 10.
	    $next_ten = (ceil($total_sum / 10)) * 10;
	    $check_digit = $next_ten - $total_sum;

	    // if the check digit and the last digit of the 
	    // barcode are OK return true;
	    if ($check_digit == $digits[12]) {
	        return true;
	    }

	    return false;
	}



} // class WPLA_FeedValidator
