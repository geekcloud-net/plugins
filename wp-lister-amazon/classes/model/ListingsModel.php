<?php
/**
 * WPLA_ListingsModel class
 *
 * responsible for managing listings and talking to amazon
 * 
 */

class WPLA_ListingsModel extends WPLA_Model {

	const TABLENAME = 'amazon_listings';

	var $updated_count = 0;
	var $imported_count = 0;

	public function __construct() {
		global $wpdb;
		$this->tablename = $wpdb->prefix . self::TABLENAME;
	}


	function getPageItems( $current_page, $per_page, $mode = 'default' ) {
		global $wpdb;

		$orderby  = (!empty($_REQUEST['orderby'])) ? esc_sql( $_REQUEST['orderby'] ) : 'date_created desc, date_published desc, listing_title';
		$order    = (!empty($_REQUEST['order']))   ? esc_sql( $_REQUEST['order']   ) : 'asc';
		$offset   = ( $current_page - 1 ) * $per_page;
		$per_page = esc_sql( $per_page );

        $join_sql  = '';
        $where_sql = '';

        // filter listing_status
		$listing_status = isset($_REQUEST['listing_status']) ? esc_sql( $_REQUEST['listing_status'] ) : '';
		if ( $listing_status == 'no_asin' ) {
			$where_sql = "WHERE status = 'online'
				  			AND ( asin = '' OR asin IS NULL ) ";
		// } elseif ( $listing_status == 'is_in_stock' ) {
		// 	$where_sql = "WHERE (   quantity > 0
		// 		  			 OR fba_quantity > 0 ) ";
		// } elseif ( $listing_status == 'is_not_in_stock' ) {
		// 	$where_sql = "WHERE (   quantity < 1
		// 		  			AND ( fba_quantity < 1 OR fba_quantity IS NULL ) ) ";
		// } elseif ( $listing_status == 'is_fba' ) {
		// 	$where_sql = "WHERE fba_quantity > 0
		// 		  			 OR ( NOT fba_fcid = 'DEFAULT' AND NOT fba_fcid IS NULL ) ";
		// } elseif ( $listing_status == 'is_not_fba' ) {
		// 	$where_sql = "WHERE ( fba_fcid = 'DEFAULT' 
		// 					 OR fba_fcid IS NULL )
		// 					AND ( product_type <> 'variable' OR product_type IS NULL ) ";
		} elseif ( $listing_status == 'quality_alert' ) {
			$where_sql = "WHERE ( NOT quality_status = '' 
							AND   NOT quality_status IS NULL ) ";
		} elseif ( $listing_status != '' ) {
			$where_sql = "WHERE status = '".$listing_status."'";
		} else {
			$where_sql = "WHERE 1 = 1 ";
		} 

		// hide parent variations on repricing page
		if ( $mode == 'repricing' ) {
			$where_sql = "WHERE ( product_type <> 'variable' OR product_type IS NULL ) \n";
		}

        // filter repricing_status
		$repricing_status = isset($_REQUEST['repricing_status']) ? esc_sql( $_REQUEST['repricing_status'] ) : '';
		if ( $repricing_status == 'is_lowest_price' ) {
			$where_sql .= "
			AND ( min_price IS NOT NULL
			      AND price <= lowest_price 
			    )
			";
		} elseif ( $repricing_status == 'is_not_lowest_price' ) {
			$where_sql .= "
			AND min_price IS NOT NULL
			AND lowest_price > 0
			AND price > lowest_price
			";
		} elseif ( $repricing_status == 'no_price_range' ) {
			$where_sql .= "
			AND (   min_price = 0
				 OR min_price IS NULL 
				)
			";
		} elseif ( $repricing_status == 'pnq_in_process' ) {
			$where_sql .= "	AND pnq_status > 0 ";
		} elseif ( $repricing_status == 'pnq_failed' ) {
			$where_sql .= "	AND pnq_status < 0 
            			    AND NOT status = 'failed' ";
		} 


        // filter buybox_status
		$buybox_status = isset($_REQUEST['buybox_status']) ? esc_sql( $_REQUEST['buybox_status'] ) : '';
		if ( $buybox_status == 'has_buybox' ) {
			$where_sql .= "
			AND has_buybox = 1
            ";
		} elseif ( $buybox_status == 'no_buybox' ) {
			$where_sql .= "
			AND has_buybox <> 1
			";
		} 


        // filter fba_age
		$fba_age = isset($_REQUEST['fba_age']) ? esc_sql( $_REQUEST['fba_age'] ) : '';
		if ( $fba_age == 'age_90' ) {
			$where_sql .= "
			AND fba_inv_age_90 > 0
			AND fba_inv_age_90 IS NOT NULL
            ";
		} elseif ( $fba_age == 'age_180' ) {
			$where_sql .= "
			AND fba_inv_age_180 > 0
			AND fba_inv_age_180 IS NOT NULL
			";
		} elseif ( $fba_age == 'age_270' ) {
			$where_sql .= "
			AND fba_inv_age_270 > 0
			AND fba_inv_age_270 IS NOT NULL
			";
		} elseif ( $fba_age == 'age_365' ) {
			$where_sql .= "
			AND fba_inv_age_365 > 0
			AND fba_inv_age_365 IS NOT NULL
			";
		} elseif ( $fba_age == 'age_365_plus' ) {
			$where_sql .= "
			AND fba_inv_age_365_plus > 0
			AND fba_inv_age_365_plus IS NOT NULL
			";
		} 


        // filter stock_status
		$fallback_enabled = get_option('wpla_fba_enable_fallback');
		$stock_status = isset($_REQUEST['stock_status']) ? esc_sql( $_REQUEST['stock_status'] ) : '';

		// fallback enabled - either stock will make it in_stock (default)
		if ( $stock_status == 'is_in_stock' && $fallback_enabled ) {
			$where_sql .= "
			AND (   quantity     > 0
	  			 OR fba_quantity > 0 
	  			) 
            ";
		} elseif ( $stock_status == 'is_not_in_stock' && $fallback_enabled ) {
			$where_sql .= "
			AND quantity < 1
			AND ( fba_quantity < 1 OR fba_quantity IS NULL ) 
			";
		} 

		// fallback disabled - check FBA and local stock separately
		if ( $stock_status == 'is_in_stock' && ! $fallback_enabled ) {
			$where_sql .= "
			AND (   (     quantity > 0  AND ( fba_fcid = 'DEFAULT'    OR fba_fcid = ''           OR fba_fcid IS NULL        ) ) /* non-FBA */
	  			 OR ( fba_quantity > 0  AND ( fba_fcid = 'AMAZON_NA'  OR fba_fcid = 'AMAZON_EU'  OR fba_fcid = 'AMAZON_IN'  ) ) /*     FBA */
	  			) 
            ";
		} elseif ( $stock_status == 'is_not_in_stock' && ! $fallback_enabled ) {
			$where_sql .= "
			AND (   (       quantity < 1                            AND ( fba_fcid = 'DEFAULT'    OR fba_fcid = ''           OR fba_fcid IS NULL        ) ) /* non-FBA */
			     OR ( ( fba_quantity < 1 OR fba_quantity IS NULL )  AND ( fba_fcid = 'AMAZON_NA'  OR fba_fcid = 'AMAZON_EU'  OR fba_fcid = 'AMAZON_IN'  ) ) /*     FBA */
			    )
			";
		} 


        // filter fba_status
		$fba_status = isset($_REQUEST['fba_status']) ? esc_sql( $_REQUEST['fba_status'] ) : '';
		if ( $fba_status == 'is_fba' ) {
			$where_sql .= "
			AND (
				 fba_quantity > 0
			     OR ( NOT fba_fcid = 'DEFAULT' AND NOT fba_fcid = '' AND NOT fba_fcid IS NULL ) 
			    )
			";
		} elseif ( $fba_status == 'is_not_fba' ) {
			$where_sql .= "
			AND ( fba_fcid = 'DEFAULT' OR fba_fcid = '' OR fba_fcid IS NULL )
			AND ( product_type <> 'variable' OR product_type IS NULL ) ";
		} 

        // filter profile_id
		$profile_id = isset($_REQUEST['profile_id']) ? esc_sql( $_REQUEST['profile_id'] ) : false;
		if ( $profile_id ) {
			if ( $profile_id == '_NONE_' ) $profile_id = '0';
			$where_sql .= "
				 AND l.profile_id = '".$profile_id."'
			";
		} 

        // filter account_id
        $mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : 'list';
		if ( $mode == 'excerpt' ) {
			$where_sql .= "
				 AND ( l.product_type <> 'variation' OR l.product_type IS NULL )
			";
		} 

        // filter view_switcher mode
		$account_id = isset($_REQUEST['account_id']) ? esc_sql( $_REQUEST['account_id'] ) : false;
		if ( $account_id ) {
			$where_sql .= "
				 AND l.account_id = '".$account_id."'
			";
		} 

        // filter search_query
		$search_query = isset($_REQUEST['s']) ? esc_sql( $_REQUEST['s'] ) : false;
		if ( $search_query ) {
			$where_sql .= "
				 AND ( l.listing_title LIKE '%".$search_query."%'
					OR l.sku           LIKE '%".$search_query."%'
					OR l.asin              = '".$search_query."'
					OR l.status            = '".$search_query."'
					OR ( l.post_id         = '".$search_query."' AND l.post_id   <> 0 )
					OR ( l.parent_id       = '".$search_query."' AND l.parent_id <> 0 )
					 )
			";
		} 

		// handle sort by quantity column (FBA / non-FBA)
		if ( $orderby == 'quantity' && $fba_status == 'is_fba' ) {
			$orderby = 'fba_quantity';
		} 

        // get items
		$items = $wpdb->get_results("
			SELECT *
			FROM $this->tablename l
            $join_sql 
            $where_sql
			ORDER BY $orderby $order
            LIMIT $offset, $per_page
		", ARRAY_A);

		// get total items count - if needed
		if ( ( $current_page == 1 ) && ( count( $items ) < $per_page ) ) {
			$this->total_items = count( $items );
		} else {
			$this->total_items = $wpdb->get_var("
				SELECT COUNT(*)
				FROM $this->tablename l
	            $join_sql
	            $where_sql
				ORDER BY $orderby $order
			");			
		}

		return $items;
	} // getPageItems()



	/* the following methods could go into another class, since they use wpdb */

	function getAll() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$items = $wpdb->get_results("
			SELECT *
			FROM $table
			ORDER BY id DESC
		", ARRAY_A);

		return $items;
	}

	function getItem( $id, $format = ARRAY_A ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$item = $wpdb->get_row( $wpdb->prepare("
			SELECT *
			FROM $table
			WHERE id = %d
		", $id
		), $format);

		// $item['details'] = $this->decodeObject( $item['details'] );
		if ( $format == ARRAY_A )
			$item['attributes'] = maybe_unserialize( $item['attributes'] );

		return $item;
	}

	// get an array of listing - called by get_compet_price action
	static function getItems( $ids, $format = OBJECT_K ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;


		// sanitize input
		$id_list = implode( ',', esc_sql( $ids ) ); 
		// $id_list == array();
		// foreach ($ids as $id) {
		// 	$id_list[] = esc_sql( $id );
		// }
		// $id_list = implode(',',$id_list); 
		
		$items = $wpdb->get_results("
			SELECT *
			FROM $table
			WHERE id IN ( $id_list )
			ORDER BY id DESC
		", $format);

		return $items;
	}

	function getItemByPostID( $post_id, $format = OBJECT ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$item = $wpdb->get_row( $wpdb->prepare("
			SELECT *
			FROM $table
			WHERE post_id = %d
		", $post_id 
		), $format);
		if ( empty($item) ) return false;

		// $item['details'] = $this->decodeObject( $item['details'] );
		$item->attributes = maybe_unserialize( $item->attributes );

		return $item;
	}

	function getAllItemsByPostID( $post_id, $format = OBJECT ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$items = $wpdb->get_results( $wpdb->prepare("
			SELECT *
			FROM $table
			WHERE post_id   = %d
		", $post_id
		), $format);

		return $items;
	}

	function getAllItemsByPostOrParentID( $post_id, $format = OBJECT ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$items = $wpdb->get_results( $wpdb->prepare("
			SELECT *
			FROM $table
			WHERE post_id   = %d
			   OR parent_id = %d
		", $post_id, $post_id 
		), $format);

		return $items;
	}

	static function deleteItem( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$wpdb->delete( $table, array( 'id' => $id ) );
	}

	function getItemByASIN( $asin, $decode_details = true ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$item = $wpdb->get_row( $wpdb->prepare("
			SELECT *
			FROM $table
			WHERE asin = %s
		", $asin ) );
		if (!$item) return false;
		if (!$decode_details) return $item;

		$item->details = $this->decodeObject( $item->details );

		return $item;
	}

	function getItemBySKU( $sku, $decode_details = true ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		if ( empty($sku) ) return false;
		$item = $wpdb->get_row( $wpdb->prepare("
			SELECT *
			FROM $table
			WHERE sku = %s
		", $sku ) );
		if (!$item) return false;
		if (!$decode_details) return $item;

		$item->details = $this->decodeObject( $item->details );

		return $item;
	}

	function getItemBySkuAndAccount( $sku, $account_id, $decode_details = true ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		if ( empty($sku) ) return false;
		$item = $wpdb->get_row( $wpdb->prepare("
			SELECT *
			FROM $table
			WHERE sku = %s
			  AND account_id = %s
		", $sku, $account_id ) );
		if (!$item) return false;
		if (!$decode_details) return $item;

		$item->details = $this->decodeObject( $item->details );

		return $item;
	}

	function getASINFromPostID( $post_id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$item = $wpdb->get_var( $wpdb->prepare("
			SELECT asin
			FROM $table
			WHERE post_id = %d
		", $post_id ) );
		return $item;
	}
	function getStatus( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$item = $wpdb->get_var( $wpdb->prepare("
			SELECT status
			FROM $table
			WHERE id = %d
		", $id ) );
		return $item;
	}
	function getListingIDFromPostID( $post_id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$item = $wpdb->get_var( $wpdb->prepare("
			SELECT id
			FROM $table
			WHERE post_id = %d
			ORDER BY id DESC
		", $post_id ) );
		return $item;
	}
	function getStatusFromPostID( $post_id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$item = $wpdb->get_var( $wpdb->prepare("
			SELECT status
			FROM $table
			WHERE post_id = %d
			ORDER BY id DESC
		", $post_id ) );
		return $item;
	}

	static function getStatusSummary() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$result = $wpdb->get_results("
			SELECT status, count(*) as total
			FROM $table
			GROUP BY status
		");

		$summary = new stdClass();
		// $summary->prepared = false;
		// $summary->changed = false;
		foreach ($result as $row) {
			$status = $row->status;
			$summary->$status = $row->total;
		}

		// count items with quality alerts
		$quality_alert = $wpdb->get_var("
			SELECT COUNT( id ) AS quality_alert
			FROM $table
			WHERE ( NOT quality_status = '' 
			  AND   NOT quality_status IS NULL ) 
		");
		$summary->quality_alert = $quality_alert;


		// count total items as well
		$total_items = $wpdb->get_var("
			SELECT COUNT( id ) AS total_items
			FROM $table
		");
		$summary->total_items = $total_items;


        if ( ! get_option( 'wpla_fba_enabled' ) )
            return $summary;

		// count FBA items
		$is_fba = $wpdb->get_var("
			SELECT COUNT( id ) AS is_fba
			FROM $table
			WHERE fba_quantity > 0
			   OR ( NOT fba_fcid = 'DEFAULT' AND NOT fba_fcid = '' AND NOT fba_fcid IS NULL ) 
		");
		$summary->is_fba = $is_fba;

		// count non-FBA items
		$is_not_fba = $wpdb->get_var("
			SELECT COUNT( id ) AS is_not_fba
			FROM $table
			WHERE ( fba_fcid = 'DEFAULT' OR fba_fcid = '' OR fba_fcid IS NULL )
			AND   ( product_type <> 'variable' OR product_type IS NULL )
		");
		$summary->is_not_fba = $is_not_fba;


		return $summary;
	}

	static function getRepricingStatusSummary() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$summary = new stdClass();

		// count items which already have the BuyBox
		$total_items = $wpdb->get_var("
			SELECT COUNT( id ) AS total_items
			FROM $table
			WHERE ( product_type <> 'variable' OR product_type IS NULL ) 
			  AND has_buybox = 1
		");
		$summary->has_buybox = $total_items;

		// count items which don't have the BuyBox
		$total_items = $wpdb->get_var("
			SELECT COUNT( id ) AS total_items
			FROM $table
			WHERE ( product_type <> 'variable' OR product_type IS NULL ) 
			  AND has_buybox <> 1
		");
		$summary->no_buybox = $total_items;

		// count items which already have the lowest price
		$total_items = $wpdb->get_var("
			SELECT COUNT( id ) AS total_items
			FROM $table
			WHERE ( product_type <> 'variable' OR product_type IS NULL ) 
			  AND min_price IS NOT NULL
			  AND price <= lowest_price
		");
		$summary->is_lowest_price = $total_items;

		// count items which currently do NOT have the lowest price
		$total_items = $wpdb->get_var("
			SELECT COUNT( id ) AS total_items
			FROM $table
			WHERE ( product_type <> 'variable' OR product_type IS NULL ) 
			  AND min_price IS NOT NULL
			  AND lowest_price > 0
			  AND price > lowest_price
		");
		$summary->is_not_lowest_price = $total_items;

		// count items without min_price
		$total_items = $wpdb->get_var("
			SELECT COUNT( id ) AS total_items
			FROM $table
			WHERE ( product_type <> 'variable' OR product_type IS NULL ) 
			  AND ( min_price = 0
			   OR min_price IS NULL )
		");
		$summary->no_price_range = $total_items;

		// count items scheduled for PNQ update
		$total_items = $wpdb->get_var("
			SELECT COUNT( id ) AS total_items
			FROM $table
			WHERE ( product_type <> 'variable' OR product_type IS NULL ) 
			  AND pnq_status > 0
		");
		$summary->pnq_in_process = $total_items;

		// count items where the PNQ update failed
		$total_items = $wpdb->get_var("
			SELECT COUNT( id ) AS total_items
			FROM $table
			WHERE ( product_type <> 'variable' OR product_type IS NULL ) 
			  AND pnq_status < 0
			  AND NOT status = 'failed'
		");
		$summary->pnq_failed = $total_items;

		// count total items as well
		$total_items = $wpdb->get_var("
			SELECT COUNT( id ) AS total_items
			FROM $table
			WHERE ( product_type <> 'variable' OR product_type IS NULL ) 
		");
		$summary->total_items = $total_items;

		return $summary;
	}


	
	// process pricing information for products
	function processBuyBoxPricingResult( $result, $account_id ) {
		// echo "<pre>processBuyBoxPricingResult() ";print_r($result);echo"</pre>";

		if ( ! $result->success ) return;
		if ( ! is_array( $result->products ) ) return;

		foreach ( $result->products as $asin => $product ) {
			$this->updateBuyBoxPricingInfoForASIN( $asin, $product, $account_id );
		}

	} // processBuyBoxPricingResult()

	// process pricing information for products
	function processLowestOfferPricingResult( $result, $account_id ) {
		// echo "<pre>processLowestOfferPricingResult() ";print_r($result);echo"</pre>";

		if ( ! $result->success ) return;
		if ( ! is_array( $result->products ) ) return;

		foreach ( $result->products as $asin => $product ) {
			$this->updateLowestOfferPricingInfoForASIN( $asin, $product, $account_id );
		}

	} // processLowestOfferPricingResult()

	// update pricing information for single product
	function updateBuyBoxPricingInfoForASIN( $asin, $product, $account_id ) {
		// echo "<pre>updateBuyBoxPricingInfoForASIN( $asin ) ";print_r($product);echo"</pre>";
		// if ( empty($product->prices) ) return;
		// echo "<pre>";print_r($product->prices);echo"</pre>";#die();

		$lowest_buybox_price = PHP_INT_MAX;
		$lowest_compet_price = PHP_INT_MAX;
		$seller_has_buybox   = false;

		foreach ( $product->prices as $price ) {
			// $lowest_buybox_price = $price->LandedPrice;
			// $condition    = $price->condition;
			// $subcondition = $price->subcondition;
			// $shipping_fee = $price->Shipping;

			if ( $price->LandedPrice < $lowest_buybox_price ) {

				// ignore seller's own price - to allow automatic price increase
				if ( $price->belongsToRequester ) {
					$seller_has_buybox   = true;
					$lowest_buybox_price = $price->LandedPrice;
					continue;
				}

				// TODO: check actual item condition!
				if ( $price->condition == 'New' ) {
					$lowest_buybox_price = $price->LandedPrice;
					$lowest_compet_price = $price->LandedPrice;
				} else {
					wpla_show_message('Skipped listing at '.$price->LandedPrice.' with condition '.$price->condition);					
				}

			}

		} // each pricing node

		// if no price for "New" condition was found, use the first one which is not our own
		if ( $lowest_buybox_price == PHP_INT_MAX ) {

			foreach ( $product->prices as $price ) {
				if ( $price->LandedPrice < $lowest_buybox_price ) {

					// ignore seller's own price - to allow automatic price increase
					if ( $price->belongsToRequester ) {
						$seller_has_buybox   = true;
						$lowest_buybox_price = $price->LandedPrice;
						continue;
					}

					$lowest_buybox_price = $price->LandedPrice;
					$lowest_compet_price = $price->LandedPrice;
					break;
				}
			} // each pricing node

		}

		// if nothing found so far, there is no competitor's price
		if ( $lowest_buybox_price == PHP_INT_MAX )	$lowest_buybox_price = false;
		if ( $lowest_compet_price == PHP_INT_MAX )	$lowest_compet_price = false;
		

		$data = array();
		$data['pricing_date'] 	  = gmdate('Y-m-d H:i:s', time());

		if ( ! empty($product->prices) ) {
			$data['lowest_price'] = $lowest_compet_price ? $lowest_compet_price : $lowest_buybox_price;
			$data['compet_price'] = $lowest_compet_price;
			$data['buybox_price'] = $lowest_buybox_price;
			$data['has_buybox']   = $seller_has_buybox ? 1 : 0;
			$data['buybox_data']  = serialize( $product->prices );
			$data['pricing_info'] = serialize( $product->prices ); // deprecated
		} else {
			$data['lowest_price'] = '';
			$data['compet_price'] = '';
			$data['buybox_price'] = '';
			$data['buybox_data']  = '';
			$data['pricing_info'] = '';
			$data['has_buybox']   = 0;
		}

		$this->updateWhere( array('asin' => $asin, 'account_id' => $account_id), $data );
		// echo "<pre>asin: ";print_r($asin);echo"</pre>";
		// echo "<pre>data: ";print_r($data);echo"</pre>";

	} // updateBuyBoxPricingInfoForASIN()


	// update pricing information for single product
	function updateLowestOfferPricingInfoForASIN( $asin, $product, $account_id ) {
		// echo "<pre>updateLowestOfferPricingInfoForASIN( $asin ) ";print_r($product);echo"</pre>";
		// if ( empty($product->prices) ) return;

		$listing = $this->getItemByASIN( $asin );

		$lowest_price = PHP_INT_MAX;
		$compet_price = PHP_INT_MAX;
		foreach ( $product->prices as $price ) {

			// check for lowest price of all
			// (used to include sellers own price, but since we use ExcludeMe parameter now, it's more or less the same as compet_price...)
			if ( $price->LandedPrice < $lowest_price ) {

				// TODO: check actual item condition!
				if ( $price->condition == 'New' ) {
					$lowest_price = $price->LandedPrice;
				} else {
					wpla_show_message('Skipped offer at '.$price->LandedPrice.' with condition '.$price->condition);
				}

			}

			// check for lowest competitor price - to allow automatic price increase
			if ( $price->LandedPrice < $compet_price ) {

				// // this check should not be required anymore, since we use GetLowestOfferListingsForASIN with ExcludeMe=true now!
				// // skip if this is the seller's own price
				// // - and there are NO other offers "tied" to it
				// if ( ( $price->LandedPrice == $listing->price       ) && 
				// 	 ( $price->NumberOfOfferListingsConsidered == 1 ) ) continue;

				// use as competitor price / lowest offer
				// it might still be the sellers own price, but if there are other offers we should not go higher...
	
				if ( $price->condition == 'New' ) {
					$compet_price = $price->LandedPrice;
				}

			}

		} // each pricing node

		// if nothing found so far, there is no competitor's price
		if ( $lowest_price == PHP_INT_MAX )	$lowest_price = false;
		if ( $compet_price == PHP_INT_MAX )	$compet_price = false;
		
		// init data array
		$data = array();

		// check listing - only set lowest price if empty! (no Buy Box price)
		if ( ! $listing->lowest_price ) {
			$data['lowest_price'] = $lowest_price;
		}
		// only set competitor price if empty, or if new competitor price is lower
		if ( ! $listing->compet_price || ( $compet_price && $compet_price < $listing->compet_price ) ) {
			$data['compet_price'] = $compet_price;
		}

		if ( ! empty($product->prices) ) {
			$data['loffer_price'] = $lowest_price;
			$data['loffer_data']  = serialize( $product->prices );
		} else {
			$data['loffer_price'] = '';
			$data['loffer_data']  = '';
		}

		$this->updateWhere( array('asin' => $asin, 'account_id' => $account_id), $data );
		// echo "<pre>asin: ";print_r($asin);echo"</pre>";
		// echo "<pre>data: ";print_r($data);echo"</pre>";

	} // updateLowestOfferPricingInfoForASIN()


	function updateItemAttributes( $ItemAttributes, $id )
	{
		global $wpdb;
		
		// skip if no product...
		if ( empty( $ItemAttributes->Title ) ) {
			WPLA()->logger->error( 'could not update ItemAttributes: '.print_r($ItemAttributes,1) );
			return false;
		}

		$data = array();
		$data['listing_title'] = $ItemAttributes->Title;
		$data['attributes']    = maybe_serialize( $ItemAttributes );

		// update listing
		$wpdb->update( $this->tablename, $data, array( 'id' => $id ) );
		echo $wpdb->last_error;

		#WPLA()->logger->info('sql: '.$wpdb->last_query );
		#WPLA()->logger->info( $wpdb->last_error );

		return true;
	}

	function insertMatchedProduct( $post_id, $asin, $account_id )
	{
		global $wpdb;

		$product = WPLA_ProductWrapper::getProduct( $post_id );
		if ( ! $product || ! $product->exists() ) {
			$this->lastError = 'Product could not be found.';
			return false;
		}
		
		// create DB columns for matched listing item
		$data = array();
		$data['asin']           = trim( $asin );
		$data['post_id']        = $post_id;
		$data['account_id']     = $account_id;
		$data['date_created']   = gmdate('Y-m-d H:i:s', time() );
		// $data['date_published'] = gmdate('Y-m-d H:i:s', time() ); // should be set when actually published

		$data['sku']            = $product->get_sku();
		// $data['price']          = $product->get_price();
		$data['price']          = wpla_get_product_meta( $product, 'regular_price' );
		$data['quantity']       = $product->get_stock_quantity();
		$data['listing_title']  = $product->post->post_title;
		$data['product_type']   = wpla_get_product_meta( $product, 'product_type' );

		// handle single variations
		if ( $product->is_type( 'variation' ) ) {

			// get attributes values and append to listing title
			$variation_attributes   = $product->get_variation_attributes();
			$attribute_values	    = array_values( $variation_attributes );
			$data['listing_title'] .= ' - ' . join(', ', $attribute_values );		

			// set parent
			$data['parent_id']  	= WPLA_ProductWrapper::getVariationParent( $post_id );

			// try to find parent listing to copy some values
    		$parent_listing = $this->getItemByPostID( WPLA_ProductWrapper::getVariationParent( $post_id ) );
    		if ( $parent_listing ) {
				$data['profile_id']	= $parent_listing->profile_id;
				$data['vtheme']		= $parent_listing->vtheme;
    		}

		}

		// WPLA()->logger->info('prod: '.print_r($product,1) );
		WPLA()->logger->info('data: '.print_r($data,1) );
		$this->lastError = false;

		// // skip parent variations (?)
		// if ( $product->product_type == 'variation') {
		// 	$this->lastError = 'Skipped variable parent product #'.$post_id.'. Parent variations have no inventory and do not need to be matched.';
		// 	return false;
		// }

		// check SKU
		if ( empty( $data['sku'] ) ) {
			$this->lastError = 'Skipped product #'.$post_id.' without SKU.';
			return false;
		}

		// check if SKU already exists
		if ( $this->getItemBySKU( $data['sku'], false ) ) {

			// $wpdb->update( $this->tablename, $data, array( 'sku' => $data['sku'] ) );
			// $this->updated_count++;
			$this->lastError = 'SKU already exists';
			return false;

		// check if $post_id already exists
		} elseif ( $this->getItemByPostID( $post_id ) ) {

			$this->lastError = '<br>A listing for this product already exists, so no "matched" listing was created.';
			return true;

		} else {

			$data['status'] = 'matched';
			$data['source'] = 'matched';
			$wpdb->insert( $this->tablename, $data );
			$this->last_insert_id = $wpdb->insert_id;
			$this->imported_count++;
			$this->lastError = 'Matched listing created for ASIN '.$asin.' (#'.$post_id.')';
		}

		echo $wpdb->last_error;
		#WPLA()->logger->info('sql: '.$wpdb->last_query );
		#WPLA()->logger->info( $wpdb->last_error );

		return true;
	} // insertMatchedProduct()

	function updateItemFromReportCSV( $csv, $account_id )
	{
		global $wpdb;

		// skip if $csv is not the right format - seller-sku is required,
		// localized report headers would insert empty rows in listings table
		if ( ! isset( $csv['seller-sku'] ) || empty( $csv['seller-sku'] ) ) {
			wpla_show_message('Error: Could not parse report row. Make sure to disable localized column headers in seller central.','error');
			return false;
		}
		
		// map CSV Report Row to DB columns
		$data = self::mapMerchantReportItemCSVToDB( $csv );
		$data['account_id'] = $account_id;
		// WPLA()->logger->debug('data: '.print_r($data,1) );

		// check if SKU already exists - for this account
		$existing_item = $this->getItemBySkuAndAccount( $data['sku'], $account_id, false );
		if ( $existing_item ) {

			// // foreign ASINs should not be updated when they already exist (disabled - SKUs should be updated)
			// if ( $data['source'] != 'foreign_import') {
			// 	$wpdb->update( $this->tablename, $data, array( 'sku' => $data['sku'] ) );
			// }

			// update found SKU
			$wpdb->update( $this->tablename, $data, array( 'sku' => $data['sku'] ) );
			$this->updated_count++;

			// refresh $existing_item so it holds the updated data (fix issue where qty/price was only updated when an inventory report was processed twice)
			$existing_item = $this->getItemBySkuAndAccount( $data['sku'], $account_id, false );

		} else {

			$data['status'] = 'imported';
			$wpdb->insert( $this->tablename, $data );
			$this->imported_count++;
		}

		echo $wpdb->last_error;
		#WPLA()->logger->info('sql: '.$wpdb->last_query );
		#WPLA()->logger->info( $wpdb->last_error );

		return $existing_item;
	} // updateItemFromReportCSV()

	static function mapMerchantReportItemCSVToDB( $csv ) {

		// special treatment for amazon.ca
		$row_asin = false;
		$row_asin = isset( $csv['asin1'] ) ? $csv['asin1'] : $row_asin;
		$row_asin = isset( $csv['asin']  ) ? $csv['asin']  : $row_asin;
		if ( ! $row_asin && isset($csv['product-id']) ) {
			if ( $csv['product-id-type'] == 1 ) {
				$row_asin = $csv['product-id'];
			}
		}
		// fix missing fulfullment channel by checking for empty quantity
		$fba_default_fcid = get_option( 'wpla_fba_fulfillment_center_id', 'AMAZON_NA' );
		$fba_fcid         = $csv['quantity'] === ''            ? $fba_default_fcid           : 'DEFAULT';
		$fba_fcid         = isset($csv['fulfillment-channel']) ? $csv['fulfillment-channel'] : $fba_fcid;
		$open_date        = str_replace( '/', '-', $csv['open-date'] );
		// replace MEST with CEST so PHP can parse it #17698
        $open_date        = str_replace( 'MEST', 'CEST', $open_date );

		$data = array();
		$data['asin'] 				= $row_asin;
		$data['price'] 				= $csv['price'];
		$data['quantity']			= $csv['quantity'];
		$data['sku'] 				= $csv['seller-sku'];
		$data['fba_fcid'] 			= $fba_fcid;
		// $data['listing_title'] 	= utf8_encode( $csv['item-name'] );
		$data['listing_title'] 		= self::convertToUTF8( $csv['item-name'] );
		$data['description']		= isset($csv['item-description']) ? utf8_encode( $csv['item-description'] ) : '';
		$data['date_published']		= gmdate('Y-m-d H:i:s', strtotime( $open_date ) );
		$data['source']             = isset($csv['source']) ? $csv['source'] : 'imported';

		// store report_row for later reference - and convert all text columns to UTF8
		$csv['item-name'] 			= self::convertToUTF8(  $csv['item-name'] );
		$csv['item-note'] 			= self::convertToUTF8( @$csv['item-note'] );
		$data['details'] 			= self::encodeObject( $csv );

		return $data;
	} // mapMerchantReportItemCSVToDB()

	static function convertToUTF8( $string ) {
		return self::detectUTF8( $string ) ? $string : utf8_encode( $string );
	}

	static function detectUTF8( $string ) {
	    return preg_match('%(?:
	        [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
	        |\xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
	        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
	        |\xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
	        |\xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
	        |[\xF1-\xF3][\x80-\xBF]{3}         # planes 4-15
	        |\xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
	        )+%xs', 
	    $string);
	}

	static function getUrlForItemObj( $item ) {

		$item = (array) $item;
		$listing_url = 'http://www.amazon.com/dp/'.$item['asin'].'/'; // default to US

        if ( $item['account_id'] ) {
            $account     = WPLA()->memcache->getAccount( $item['account_id'] );
            $market      = $account ? WPLA()->memcache->getMarket( $account->market_id ) : false;
            $listing_url = $market  ? 'http://www.'.$market->url.'/dp/'.$item['asin'].'/' : $listing_url;
        }

        return $listing_url;
	}


	public function updateListing( $id, $data ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		// update
		$wpdb->update( $table, $data, array( 'id' => $id ) );

		#WPLA()->logger->info('sql: '.$wpdb->last_query );
		#WPLA()->logger->info( $wpdb->last_error );
	}

	static public function updateWhere( $where, $data ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		// update
		$wpdb->update( $table, $data, $where );
	}

	function getAllImported( $source = false ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$source = esc_sql( $source );
		$where_sql = $source ? " AND source = '$source' " : '';
		$items = $wpdb->get_results("
			SELECT id, sku, asin, listing_title 
			FROM $table
			WHERE status = 'imported'
			$where_sql
			ORDER BY id DESC
		", ARRAY_A);		

		return $items;		
	}

	// 
	// old version - group pending items by profile id
	// 

	function getAllPendingProductsForAccount( $account_id ) {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;

		$items = $wpdb->get_results( $wpdb->prepare("
			SELECT * 
			FROM $table
			WHERE account_id = %d
			  AND ( status = 'changed'
			   OR 	status = 'prepared'
			   OR   status = 'matched' )
			ORDER BY profile_id, id DESC
		", $account_id
		), ARRAY_A);		

		return $items;		
	}

	function getGroupedPendingProductsForAccount( $account_id ) {

		$items = $this->getAllPendingProductsForAccount( $account_id );

		// group by profile_id
		$grouped_items = array();
		foreach ( $items as $item ) {
			$grouped_items[ $item['profile_id'] ][] = $item;
		}

		return $grouped_items;		
	}

	// 
	// new version - group pending items by template id instead of profile id
	// 

	function getAllPendingProductsForAccount_TemplateType( $account_id ) {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;

		$items = $wpdb->get_results( $wpdb->prepare("
			SELECT l.*, p.tpl_id 
			FROM 
				{$wpdb->prefix}amazon_listings l
            LEFT JOIN
                {$wpdb->prefix}amazon_profiles p ON ( l.profile_id = p.profile_id )
			WHERE l.account_id = %d
			  AND ( l.status = 'changed'
			   OR 	l.status = 'prepared'
			   OR   l.status = 'matched' )
			ORDER BY p.tpl_id, l.profile_id, l.id DESC
		", $account_id
		), ARRAY_A);		

		return $items;		
	}

	function getPendingProductsForAccount_GroupedByTemplateType( $account_id ) {

		$items = $this->getAllPendingProductsForAccount_TemplateType( $account_id );

		// group by profile_id
		$grouped_items = array();
		foreach ( $items as $item ) {
			$tpl_id     = $item['tpl_id'] ? $item['tpl_id'] : 0;
			$profile_id = $item['profile_id'];
			$grouped_items[ $tpl_id ][ $profile_id ][] = $item;
		}

		return $grouped_items;		
	}

	// get products for Price and Quantity feed
	function getAllProductsForAccountByPnqStatus( $account_id, $pnq_status = 1 ) {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;

		$items = $wpdb->get_results( $wpdb->prepare("
			SELECT * 
			FROM $table
			WHERE pnq_status = %s
			  AND account_id = %d
			ORDER BY profile_id, id DESC
		", 
		$pnq_status,
		$account_id
		), ARRAY_A);		

		return $items;		
	}

	// get products for delete feed
	function getAllProductsInTrashForAccount( $account_id ) {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;

		$items = $wpdb->get_results( $wpdb->prepare("
			SELECT * 
			FROM $table
			WHERE status     = 'trash'
			  AND account_id = %d
			ORDER BY id DESC
		", $account_id
		), ARRAY_A);		

		return $items;		
	}

	// deprecated
	function getGroupedProductsByStatus( $account_id, $status ) {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;

		$items = $wpdb->get_results( $wpdb->prepare("
			SELECT * 
			FROM $table
			WHERE status     = %s
			  AND account_id = %d
			ORDER BY profile_id, id DESC
		", 
		$status,
		$account_id
		), ARRAY_A);		

		// group by profile_id
		$grouped_items = array();
		foreach ( $items as $item ) {
			$grouped_items[ $item['profile_id'] ][] = $item;
		}

		return $grouped_items;		
	}

	function findAllListingsByColumn( $value, $column, $account_id = false ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$account_sql = $account_id ? "AND account_id = $account_id" : '';
		$items = $wpdb->get_results( $wpdb->prepare("
			SELECT *
			FROM $table
			WHERE $column = %s
			      $account_sql
			ORDER BY id DESC
		", $value 
		), OBJECT_K);

		return $items;		
	}

	// should be merged with getItems()
	function getItemsByIdArray( $listing_ids ) {
		if ( ! is_array( $listing_ids )  ) return array();
		if ( sizeof( $listing_ids ) == 0 ) return array();

		return self::getItems( $listing_ids, ARRAY_A );
	}

	function getAllDuplicateProducts() {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;

		$items = $wpdb->get_results("
			SELECT post_id, account_id, COUNT(*) c
			FROM $table
			WHERE post_id IS NOT NULL
			  AND NOT post_id = 0
			GROUP BY post_id, account_id 
			HAVING c > 1
		");		

		return $items;		
	}

	function getAllDuplicateASINs() {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;

		$items = $wpdb->get_results("
			SELECT asin, account_id, COUNT(*) c
			FROM $table
			WHERE asin IS NOT NULL
			GROUP BY asin, account_id 
			HAVING c > 1
		");		

		return $items;		
	}

	function getAllDuplicateSKUs() {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;

		$items = $wpdb->get_results("
			SELECT sku, account_id, COUNT(*) c
			FROM $table
			WHERE NOT sku = ''
			GROUP BY sku, account_id
			HAVING c > 1
		");		

		return $items;		
	}

	function getAllOnlineWithoutASIN( $account_id = false, $limit = false, $format = ARRAY_A ) {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;

		$account_id = esc_sql( $account_id );
		$where_sql = $account_id ? "AND account_id = '$account_id'" : '';
		$limit_sql = $limit      ? "LIMIT ".esc_sql( $limit )       : '';
		$items = $wpdb->get_results("
			SELECT *
			FROM $table
			WHERE status = 'online'
			  AND ( asin = '' OR asin IS NULL )
			  $where_sql
			  $limit_sql
		", $format);		

		return $items;		
	}

	function getAllItemsByParentID( $parent_id ) {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;

		$items = $wpdb->get_results( $wpdb->prepare("
			SELECT *
			FROM $table
			WHERE parent_id = %d
		", $parent_id 
		), OBJECT_K);		

		return $items;
	}

	function getAllItemsWithStockInFBA( $limit = null, $offset = null ) {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;

        $limit  = intval($limit); 
        $offset = intval($offset);
        $limit_sql = $limit ? " LIMIT $limit OFFSET $offset" : '';

		$items = $wpdb->get_results("
			SELECT *
			FROM $table
			WHERE fba_quantity > 0
            $limit_sql
		", OBJECT_K);		

		return $items;
	}

	function getAllItemsUsingFBA( $limit = null, $offset = null ) {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;

        $limit  = intval($limit); 
        $offset = intval($offset);
        $limit_sql = $limit ? " LIMIT $limit OFFSET $offset" : '';

		$items = $wpdb->get_results("
			SELECT *
			FROM $table
			WHERE NOT fba_fcid = 'DEFAULT' 
			  AND NOT fba_fcid = '' 
			  AND NOT fba_fcid IS NULL
			  AND status = 'online'
            $limit_sql
		", OBJECT_K);		

		return $items;
	}

	function getWhere( $column, $value ) {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;

		$items = $wpdb->get_results( $wpdb->prepare("
			SELECT *
			FROM $table
			WHERE $column = %s
		", $value 
		), OBJECT_K);		

		return $items;
	}

	function productExistsInAccount( $post_id, $account_id ) {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;

		$item = $wpdb->get_row( $wpdb->prepare("
			SELECT *
			FROM $table
			WHERE post_id    = %d
			  AND account_id = %d
		", 
		$post_id, 
		$account_id 
		), OBJECT);

		return $item;
	}

	function insertListingData( $data ) {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;


		// defaults
		if ( ! isset( $data['listing_title'] ) ) 	$data['listing_title'] 	= '__unknown__';
		if ( ! isset( $data['product_type'] ) ) 	$data['product_type'] 	= 'simple';
		if ( ! isset( $data['date_created'] ) ) 	$data['date_created'] 	= gmdate( 'Y-m-d H:i:s', time() );
		if ( ! isset( $data['account_id'] ) ) 		$data['account_id'] 	= get_option('wpla_default_account_id');
		if ( ! isset( $data['status'] ) ) 			$data['status'] 		= '__unknown__';
		if ( ! isset( $data['source'] ) ) 			$data['source'] 		= '__unknown__';

		// insert in listings table
		$wpdb->insert( $this->tablename, $data );
		echo $wpdb->last_error;

		// return listing id
		$listing_id = $wpdb->insert_id;
		return $listing_id;
	}

	public function setListingQuantity( $post_id, $quantity ) {
		global $wpdb;	
		$wpdb->update( $this->tablename, array( 'quantity' => $quantity ), array( 'post_id' => $post_id ) );
	}

	public function markItemAsModified( $post_id, $skip_updating_feeds = false ) {
		global $wpdb;	
		if ( ! $post_id ) return;

		WPLA()->logger->info("markItemAsModified() - post_id: $post_id");
		// WPLA()->logger->callStack( debug_backtrace() );

		// $listingsModel  = new WPLA_ListingsModel();
		// $listing_id     = $this->getListingIDFromPostID( $post_id );
		// $listing_status = $this->getStatusFromPostID( $post_id );
        // $this->reapplyProfileToItem( $listing_id );

		// get all matching items - parent and child variations
		$listings = $this->getAllItemsByPostOrParentID( $post_id );
		$there_were_changes = false;

		// if no listings found, return log message
		if ( empty( $listings ) ) return 'no listing found for product ID '.$post_id;

		foreach ($listings as $item) {

			$post_id     	= $item->post_id;
			$listing_id     = $item->id;
			$listing_status = $item->status;

            // skip trashed listings #19812
            if ( $listing_status == 'trash' ) {
                continue;
            }

			// get product
			$product = WPLA_ProductWrapper::getProduct( $post_id );
			if ( ! $product ) continue;

			// product price
			// $product_price = $product->get_price();
			// $product_price = WPLA_ProductWrapper::getPrice( $post_id );
			$product_price = wpla_get_product_meta( $product, 'regular_price' );

			// use sale price if set
			if ( wpla_get_product_meta( $product, 'sale_price' ) ) {
				$product_price = wpla_get_product_meta( $product, 'sale_price' );
			}

			// load profile - if profile exists, re-apply profile price
			$profile = $item->profile_id ? new WPLA_AmazonProfile( $item->profile_id ) : false;
			if ( $profile ) {
				$product_price = $profile->processProfilePrice( $product_price );
			}

	        // check for custom product price
	        $custom_price = get_post_meta( $post_id, '_amazon_price', true );
	        if ( $custom_price > 0 ) $product_price = $custom_price;

			switch ( $listing_status ) {

				// prepared and sold listings keep their status
				case 'matched':		// matched items
				case 'prepared':	// new items
				// case 'submitted':
				case 'sold':
					$new_status = $listing_status;
					break;
				
				case 'online':
				case 'submitted':	// allow another feed to be submitted, even when there is a submitted feed already
									// (make sure the latest changes are submitted - even if a feed is "stuck" as submitted for some reason)
					$new_status = 'changed';
					break;
				
				case 'failed':
					// listings with ASIN exist, so they are marked as changed to be updated
					// listings without ASIN were submitted as new products, so they are marked as prepared
					// matched listings are marked as matched again...
					$new_status = $item->asin ? 'changed' : 'prepared';
					if ( $item->source == 'matched' ) $new_status = 'matched';
					if ( $item->source == 'foreign_import' ) $new_status = 'matched';
					break;
				
				default:
					# code...
					$new_status = 'changed';
			}

			// collect listing data
			$listing_data = array( 
				'quantity' => WPLA_ProductWrapper::getStock( $product ),
				// 'sku'      => $product->get_sku(),
				'price'    => $product_price,
				'status'   => $new_status 
			);

			// update SKU only if currently empty / prevent changing the SKU on existing listings
			// (this is especially important for child variations where updating all child variations would lead to duplicate SKU listings! #21639)
			if ( empty( $item->sku ) ) {
				$listing_data['sku'] = $product->get_sku();
			}

			// update listing
			$wpdb->update( $this->tablename, $listing_data, array( 'id' => $listing_id ) );

			if ( $new_status == 'changed' ) $there_were_changes = true;

		} // each listing

		// update pending feed
		if ( $there_were_changes && ! $skip_updating_feeds ) {
			WPLA_AmazonFeed::updatePendingFeeds();
		}

		return $listing_data;
	} // markItemAsModified()

	public function resubmitItem( $id ) {
		$listing = $this->getItem( $id );
		if ( ! in_array( $listing['status'], array('online','failed','submitted') ) ) return;

		if ( 'online' == $listing['status'] ) {
			// set status to changed for items which are already online
			$new_status = 'changed';
		} elseif ( 'submitted' == $listing['status'] ) {
			// set status to changed to re-submit stuck item
			$new_status = 'changed';
		} elseif ( $listing['asin'] ) {
			// items with ASIN are updated
			$new_status = 'matched';
		} else {
			// items without ASIN are new
			$new_status = 'prepared';
		}

		// update status
		$this->updateWhere( array( 'id' => $id ), array( 'status' => $new_status ) );

	}



	public function processSingleVariationTitle( $title, $variation_attributes ) {
    	
    	$title = trim( $title );
    	if ( ! is_array( $variation_attributes ) ) return $title;

    	foreach ( $variation_attributes as $attrib_name => $attrib_value ) { // wpec?
    		$title .= ' - ' . $attrib_value;
    	}

    	return $title;
	}

	public function prepareListings( $ids, $profile_id ) {
		$prepared_count = 0;
		$skipped_count  = 0;
		$this->errors   = array();
		$this->warnings = array();

		foreach( $ids as $id ) {
			$result = $this->prepareProductForListing( $id, $profile_id );
			if ( $result ) {
				$prepared_count++;	
			} else {
				$skipped_count++;
			}			
		}

		// build response
		$response = new stdClass();
		// $response->success     = $prepared_count ? true : false;
		$response->success        = true;
		$response->prepared_count = $prepared_count;
		$response->skipped_count  = $skipped_count;
		$response->profile_id     = $profile_id;
		$response->errors         = $this->errors;
		$response->warnings       = $this->warnings;

		return $response;
	}

	public function prepareProductForListing( $post_id, $profile_id ) {
		global $wpdb;
		
		// get wp post and profile
		$post = get_post( $post_id );
		$profile = new WPLA_AmazonProfile( $profile_id );
		// if ( ! $profile ) return false;

		// skip duplicates
		// $product = get_product( $post_id );
		if ( $item = $this->productExistsInAccount( $post_id, $profile->account_id ) ) {
			$this->warnings[] = sprintf( __('"%s" already exists in account %s and has been skipped.','wpla'), get_the_title($post_id), $profile->account_id );
			return false;
		}

		// skip drafts
		// if ( $post->post_status != 'published' ) return;

		// support for qTranslate
		// if ( function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage') ) {
		// 	$post_title   = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $post_title );
		// 	$post_content = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $post_content );			
		// }

		// gather product data
		$product = WPLA_ProductWrapper::getProduct( $post_id );
		if ( ! $product || ! $product->exists() ) {
			$this->errors[] = "Product $post_id could not be found.";
			return false;
		}

		// handle custom listing title
		$listing_title = $post->post_title;
		if ( $product_value = get_post_meta( $post_id, '_amazon_title', true ) )
			$listing_title = $product_value;

		// trim title to 500 characters - longer titles will break $wpdb->insert() on varchar(500) column
		$listing_title = strlen( $listing_title ) < 500 ? $listing_title : $this->mb_substr( $listing_title, 0, 500 ); // Amazon titles can not be longer than 500 characters

		// build listing item
		$data = array();
		$data['post_id']       = $post_id;
		$data['listing_title'] = $listing_title;
		// $data['post_content']  = $post->post_content;
		$data['price']         = WPLA_ProductWrapper::getPrice( $post_id );
		$data['quantity']      = WPLA_ProductWrapper::getStock( $post_id );
		$data['sku']           = WPLA_ProductWrapper::getSKU( $post_id );
		$data['date_created']  = gmdate( 'Y-m-d H:i:s', time() );
		$data['status']        = 'prepared';
		$data['source']        = 'woo';
		$data['profile_id']    = $profile->profile_id;
		$data['account_id']    = $profile->account_id;
		$data['product_type']  = wpla_get_product_meta( $product, 'product_type' );
		
		// handle variable products
		if ( wpla_get_product_meta( $product, 'product_type' ) == 'variable' ) {
			$last_variation_data = $this->prepareVariations( $post_id, $profile, $post, $data );
			$data['vtheme']      = $last_variation_data['vtheme'];
		}
		
		WPLA()->logger->info('insert new listing '.$post_id.' - title: '.$data['listing_title']);
		// WPLA()->logger->debug( print_r($post,1) );
		WPLA()->logger->debug( print_r($data,1) );
		
		// insert in listings table
		$wpdb->insert( $this->tablename, $data );
		echo $wpdb->last_error;

		WPLA()->logger->debug('insert_id: '.$wpdb->insert_id );
		WPLA()->logger->debug('sql: '.$wpdb->last_query );
		WPLA()->logger->debug( $wpdb->last_error );

		// apply profile (price)
		$listing_id = $wpdb->insert_id;
		$this->applyProfileToItem( $profile, $listing_id );

		// update / create pending feed
		WPLA_AmazonFeed::updatePendingFeeds();

		return $listing_id;
		
	} // prepareProductForListing()

	public function prepareVariations( $post_id, $profile, $post, $data ) {
		global $wpdb;

		// get variations
		$variations = WPLA_ProductWrapper::getVariations( $post_id );

		// process variations (childs)
		foreach ( $variations as $var ) {
			
			// get variation product data
			$variation_id         = $var['post_id'];
			$variation_attributes = $var['variation_attributes'];
			$variable_product     = WPLA_ProductWrapper::getProduct( $variation_id );
			if ( ! $variable_product ) continue;
			// echo "<pre>";print_r($var);echo"</pre>";#die();

			// skip hidden variations
			if ( get_post_meta( $variation_id, '_amazon_is_disabled', true ) == 'on' ) continue;

			// compile variation-theme from attribute names
			$attribute_names = array_keys( $variation_attributes );
			// foreach ($attribute_names as &$name) {
			// 	$name = WPLA_FeedDataBuilder::convertToEnglishAttributeLabel( $name );
			// }
			$vtheme = join( '-', $attribute_names );

			// generate title suffix from attribute values
			$attribute_values = array_values( $variation_attributes );
			$suffix = join( ', ', $attribute_values );

			// handle custom listing title
			$listing_title = $post->post_title;
			if ( $product_value = get_post_meta( $post_id, '_amazon_title', true ) )
				$listing_title = $product_value;

			// build single variation listing item
			$data = array();
			$data['post_id']       = $variation_id;
			$data['parent_id']     = $post_id;
			$data['vtheme']        = $vtheme;
			// $data['listing_title'] = $listing_title . ', ' . $suffix;
			$data['listing_title'] = strpos( $listing_title, '%%%' ) ? str_replace( '%%%', $suffix, $listing_title ) : $listing_title . ', ' . $suffix;

			// $data['post_content']  = $post->post_content;
			$data['price']         = WPLA_ProductWrapper::getPrice( $variation_id );
			$data['quantity']      = WPLA_ProductWrapper::getStock( $variation_id );
			$data['sku']           = WPLA_ProductWrapper::getSKU( $variation_id );
			$data['date_created']  = gmdate( 'Y-m-d H:i:s', time() );
			$data['status']        = 'prepared';
			$data['source']        = 'woo';
			$data['profile_id']    = $profile->profile_id;
			$data['account_id']    = $profile->account_id;
			$data['product_type']  = wpla_get_product_meta( $variable_product, 'product_type' );
			
			WPLA()->logger->info('insert new variation '.$variation_id.' - title: '.$data['listing_title']);
			WPLA()->logger->debug( print_r($post,1) );
			
			// insert in listings table
			$wpdb->insert( $this->tablename, $data );
			echo $wpdb->last_error;

			// apply profile (price)
			$listing_id = $wpdb->insert_id;
			$this->applyProfileToItem( $profile, $listing_id );

		}
	
		// return $wpdb->insert_id;
		return $data;
		
	} // prepareVariations()



	public function insertMissingVariation( $variation_id, $sku, $parent_listing ) {
		global $wpdb;

		// get variation product data
		// $variation_id         = $var['post_id'];
		// $variation_attributes = $var['variation_attributes'];
		$variable_product     = WPLA_ProductWrapper::getProduct( $variation_id );
		if ( ! $variable_product ) return false;
		// echo "<pre>";print_r($var);echo"</pre>";#die();


		// compile variation-theme from attribute names
		// $attribute_names = array_keys( $variation_attributes );
		// $vtheme = join( '-', $attribute_names );

		// generate title suffix from attribute values
		// $attribute_values = array_values( $variation_attributes );
		// $suffix = join( ', ', $attribute_values );

		// get attributes values and append to listing title
		$variation_attributes   = $variable_product->get_variation_attributes();
		$attribute_values	    = array_values( $variation_attributes );
		$listing_title          = $parent_listing->listing_title;
		$listing_title         .= ' - ' . join(', ', $attribute_values );		

		// handle custom listing title
		// $listing_title = $parent_listing->listing_title;
		// if ( $product_value = get_post_meta( $post_id, '_amazon_title', true ) )
		// 	$listing_title = $product_value;

		// build single variation listing item
		$data = array();
		$data['post_id']       = $variation_id;
		$data['parent_id']     = $parent_listing->post_id;
		$data['vtheme']        = $parent_listing->vtheme;
		$data['listing_title'] = $listing_title;
		$data['price']         = WPLA_ProductWrapper::getPrice( $variation_id );
		$data['quantity']      = WPLA_ProductWrapper::getStock( $variation_id );
		// $data['sku']           = WPLA_ProductWrapper::getSKU( $variation_id );
		$data['sku']           = $sku;
		$data['date_created']  = gmdate( 'Y-m-d H:i:s', time() );
		$data['status']        = 'prepared';
		$data['source']        = 'woo';
		$data['profile_id']    = $parent_listing->profile_id;
		$data['account_id']    = $parent_listing->account_id;
		$data['product_type']  = wpla_get_product_meta( $variable_product, 'product_type' );
		
		WPLA()->logger->info('insert new variation '.$variation_id.' - title: '.$data['listing_title']);
		
		// insert in listings table
		$wpdb->insert( $this->tablename, $data );
		echo $wpdb->last_error;

		// apply profile (price)
		$listing_id = $wpdb->insert_id;
		$profile = new WPLA_AmazonProfile( $parent_listing->profile_id );
		if ( $profile ) $this->applyProfileToItem( $profile, $listing_id );


		// return success
		$success = $listing_id ? true : false;
		return $success;
	} // insertMissingVariation()



	public function updateCustomListingTitle( $post_id ) {

		// get custom listing title
		$custom_title = get_post_meta( $post_id, '_amazon_title', true );
		// if ( ! $custom_title ) return; // disabled to update title when post_title changed

		// get product
		$product = WPLA_ProductWrapper::getProduct( $post_id );
		if ( ! $product ) return;

		// use post_title if no custom title found
		if ( empty($custom_title) ) {
			$custom_title = $product->get_title();
		} 

		// update simple listing or parent variation
		$data = array( 'listing_title' => str_replace( '%%%', '', $custom_title ) );
		$this->updateWhere( array('post_id' => $post_id ), $data );

		// update variable listings
		if ( wpla_get_product_meta( $product, 'product_type' ) == 'variable' ) {
	
			// get variations
			$variations = WPLA_ProductWrapper::getVariations( $post_id );

			// process variations (childs)
			foreach ( $variations as $var ) {
				
				// get variation product data
				$variation_id         = $var['post_id'];
				$variation_attributes = $var['variation_attributes'];

				// generate title suffix from attribute values
				$attribute_values = array_values( $variation_attributes );
				$suffix = join( ', ', $attribute_values );

				// update variation listing title - and attributes
				$data = array();
				$data['listing_title'] = ( strpos( $custom_title, '%%%' ) !== false ) ? str_replace( '%%%', $suffix, $custom_title ) : $custom_title . ', ' . $suffix;
				$data['vattributes']   = serialize( $variation_attributes );
				$this->updateWhere( array('post_id' => $variation_id ), $data );
			}

		} // if variable

	} // updateCustomListingTitle()

	// get variation_theme for a particular product
	static public function getVariationThemeForPostID( $post_id ) {

		// find variation
		$variations = WPLA()->memcache->getProductVariations( $post_id );
		foreach ($variations as $var) {

			// compile variation-theme from attribute names
			$variation_attributes = $var['variation_attributes'];
			$attribute_names = array_keys( $variation_attributes );
			$vtheme = join( '-', $attribute_names );

			return $vtheme;
		}

		return '';
	} // getVariationThemeForPostID()


	public function applyProfileToItem( $profile, $item ) {
		global $wpdb;

		// allow to pass a listing_id instead of item object
		if ( ! is_object( $item ) ) $item = $this->getItem( $item, OBJECT );
		// echo "<pre>";print_r($item);echo"</pre>";#die();

		// get item data
		$id 		= $item->id;
		$post_id 	= $item->post_id;
		$status 	= $item->status;
		$asin 		= $item->asin;

		// gather profile data
		$data = array();
		$data['profile_id'] = $profile->id;
		$data['account_id'] = $profile->account_id;

		// apply profile price
		$data['price'] = WPLA_ProductWrapper::getPrice( $post_id );
		$data['price'] = $profile->processProfilePrice( $data['price'] );		


		// update vtheme for child and parent variations
		if ( in_array( $item->product_type, array( 'variation', 'variable' ) ) ) {
			// $data['vtheme'] == $item->vtheme; ? // check for actual change

			// check profile for variation_theme
			$profile_fields = maybe_unserialize( $profile->fields );
			if ( is_array($profile_fields) && isset($profile_fields['variation_theme']) && ! empty($profile_fields['variation_theme']) ) {

				// use variation theme from profile
				$data['vtheme'] = $profile_fields['variation_theme'];

			} else {

				// update variation theme from product (parent variation)
				$parent_id = $item->parent_id ? $item->parent_id : $item->post_id;
				$data['vtheme'] = self::getVariationThemeForPostID( $parent_id );

			}

		} // if variable product


		// default new status is 'changed'
		$data['status'] = 'changed';
		if ( $status == 'failed' ) 			$data['status'] = 'changed';
		if ( $status == 'online' ) 			$data['status'] = 'changed';

		// except for matched or imported products
		if ( $status == 'matched' ) 		$data['status'] = $status;
		if ( $status == 'imported' ) 		$data['status'] = $status;
		if ( $status == 'prepared' ) 		$data['status'] = $status;
		
		// submitted items stay 'submitted' and archived items stay archived
		if ( $status == 'submitted' ) 		$data['status'] = $status;
		if ( $status == 'archived' ) 		$data['status'] = $status;
		if ( $status == 'trash' ) 			$data['status'] = $status;
		if ( $status == 'trashed' ) 		$data['status'] = $status;

		// debug
		if ( $status != $data['status'] ) {
			WPLA()->logger->info('applyProfileToItem('.$id.') old status: '.$status );
			WPLA()->logger->info('applyProfileToItem('.$id.') new status: '.$data['status'] );
		}

		// update auctions table
		$wpdb->update( $this->tablename, $data, array( 'id' => $id ) );

		// WPLA()->logger->info('updating listing ID '.$id);
		// WPLA()->logger->info('data: '.print_r($data,1));
		// WPLA()->logger->info('sql: '.$wpdb->last_query);
		// WPLA()->logger->info('error: '.$wpdb->last_error);


	} // applyProfileToItem()

	public function applyProfileToListings( $profile, $items ) {

		// apply profile to all items
		foreach( $items as $item ) {
			$this->applyProfileToItem( $profile, $item );			
		}

		return $items;		
	}

	public function removeProfileFromListings( $listing_ids ) {

		// apply profile to all listing_ids
		foreach( $listing_ids as $id ) {
			$data = array(
				'profile_id' => '',
			);
			$this->updateWhere( array( 'id' => $id ), $data);			
		}

	}



} // class WPLA_ListingsModel
