<?php
/**
 * WPLA_ListingsPage class
 * 
 */

class WPLA_ListingsPage extends WPLA_Page {

	const slug = 'listings';

	function config()
	{
		add_action( 'admin_menu', array( &$this, 'onWpTopAdminMenu' ), 10 );
		add_action( 'admin_menu', array( &$this, 'fixSubmenu' ), 30 );
		// add_action( 'network_admin_menu', array( &$this, 'onWpNetworkAdminMenu' ) ); 
	}
	
	public function onWpInit() {

		// Add custom screen options
		add_action( "load-toplevel_page_wpla", array( &$this, 'addScreenOptions' ) );
		
		$this->handleSubmitOnInit();
	}

	public function onWpTopAdminMenu() {
		// $page_id = add_menu_page( self::ParentTitle, __('Amazon','wpla'), self::ParentPermissions, 
		// 			   self::ParentMenuId, array( $this, 'displayListingsPage' ), $this->getImageUrl( 'amazon-16x16.png' ), 56 );

		$page_id = add_menu_page( $this->app_name, $this->main_admin_menu_label, self::ParentPermissions, 
					   self::ParentMenuId, array( $this, 'displayListingsPage' ), $this->getImageUrl( 'amazon-16x16.png' ), 57.21 );

		// $page_id: toplevel_page_wplister
	}

	function addScreenOptions() {
		
		if ( ( isset($_GET['action']) ) && ( $_GET['action'] == 'edit' ) ) {
			// on edit page render developers options
			add_screen_options_panel('wpla_developer_options', '', array( &$this, 'renderDeveloperOptions'), 'toplevel_page_wpla' );

		} else {

			// render table options
			$option = 'per_page';
			$args = array(
		    	'label' => 'Listings',
		        'default' => 20,
		        'option' => 'listings_per_page'
		        );
			add_screen_option( $option, $args );
			$this->listingsTable = new WPLA_ListingsTable();

		}

	    // add_thickbox();
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

		// enqueue ProfileSelector
		wp_register_script( 'wpla_profile_selector', self::$PLUGIN_URL.'js/classes/ProfileSelector.js?ver='.time(), array( 'jquery' ) );
		wp_enqueue_script( 'wpla_profile_selector' );
	}
	

	public function handleSubmitOnInit() {
		if ( ! current_user_can('manage_amazon_listings') ) return;

		// handle preview action
		if ( $this->requestAction() == 'wpla_preview_listing' ) {
		    check_admin_referer( 'wpla_preview_listing' );
			$this->previewListing( $_REQUEST['listing'] );
			exit();
		}

		// handle remove_from_amazon action (WooCommerce Products page)
		if ( $this->requestAction() == 'remove_from_amazon' ) {
		    check_admin_referer( 'bulk-posts' );

			$products =  is_array( $_REQUEST['post'] ) ? $_REQUEST['post'] : array( $_REQUEST['post'] );
			// WPLA()->logger->info('remove_from_amazon / products: '.print_r($products,1));
			if ( empty($products) ) return;

			foreach ( $products as $product_id ) {

				if ( ! $product_id ) continue; // prevent trashing all items with empty parent_id

				WPLA_ListingsModel::updateWhere( 
					array( 'post_id' => $product_id ),
					array( 'status' => 'trash' )
				);
				WPLA_ListingsModel::updateWhere( 
					array( 'parent_id' => $product_id ),
					array( 'status' => 'trash' )
				);

				WPLA()->logger->info('Changed status to TRASH for all listings for post_id '.$product_id);
			}

			wpla_show_message( __('Selected items have been scheduled to be removed from your Amazon account.','wpla') );
		}

	}

	public function handleActions() {
		if ( ! current_user_can('manage_amazon_listings') ) return;
	
		// handle save listing
		if ( $this->requestAction() == 'wpla_save_listing' ) {
		    check_admin_referer( 'wpla_save_listing' );
			$this->saveListing();
		}

		// trigger create product
		if ( $this->requestAction() == 'wpla_create_product' ) {
		    check_admin_referer( 'wpla_create_product' );

			$lm = new WPLA_ListingsModel();
			$listing = $lm->getItem( $_REQUEST['listing'] );
			if ( ! $listing ) return;

			// create product
			$ProductsImporter = new WPLA_ProductsImporter();
			$success = $ProductsImporter->createProductFromAmazonListing( $listing );
			$error   = $ProductsImporter->lastError;
			$post_id = $ProductsImporter->lastPostID;
			$message = $ProductsImporter->message;

			if ( $success )  {

				// get parent post_id - for View Product button
				$_product = WPLA_ProductWrapper::getProduct( $post_id );
				if ( $_product->is_type( 'variation' ) ) {
					$post_id = WPLA_ProductWrapper::getVariationParent( $post_id );
				}

				$msg  = $message ? $message : sprintf( __('A new product (ID %s) was created for ASIN %s.','wpla'), $post_id, $listing['asin'] );
				$msg .= sprintf( '&nbsp;&nbsp;<a href="post.php?post=%s&action=edit" class="button button-small" target="_blank">%s</a>', $post_id, __('View product','wpla') );
				$this->showMessage( $msg );
			} else {
				$error_msg  = '<b>' . sprintf( __('Item %s could not be imported.','wpla'), $listing['asin'] ) .'</b><br>Error: '. $error;
				$this->showMessage( $error_msg, 1 );
			}

		}


		// handle update from Amazon action
		if ( $this->requestAction() == 'update' ) {
			// $this->showMessage( __('Selected items were updated from Amazon.','wpla') );
			$this->showMessage( __('Not implemented yet.','wpla') );
		}
		// handle delete action
		if ( $this->requestAction() == 'wpla_delete' ) {
		    check_admin_referer( 'bulk-listings' );

			$lm = new WPLA_ListingsModel();
			if ( is_array( $_REQUEST['listing'] ) ) {
				foreach ( $_REQUEST['listing'] as $id ) {
					$lm->deleteItem( $id );
				}
			} elseif ( is_numeric($_REQUEST['listing'] )) {
				$lm->deleteItem( $_REQUEST['listing'] );
			}
			$this->showMessage( __('Selected listings were removed from WP-Lister.','wpla') );
		}

		// handle trash_listing action
		if ( $this->requestAction() == 'wpla_trash_listing' ) {
		    check_admin_referer( 'bulk-listings' );
			$items =  is_array( $_REQUEST['listing'] ) ? $_REQUEST['listing'] : array( $_REQUEST['listing'] );
			$lm = new WPLA_ListingsModel();
			foreach ( $items as $id ) {
				$lm->updateWhere( 
					array( 'id' => $id ),
					array( 'status' => 'trash' )
				);
			}
			$this->showMessage( __('Selected items have been scheduled to be removed from your Amazon account.','wpla') );
		}

		// handle resubmit action
		if ( $this->requestAction() == 'wpla_resubmit' ) {
			$items =  is_array( $_REQUEST['listing'] ) ? $_REQUEST['listing'] : array( $_REQUEST['listing'] );
			$lm = new WPLA_ListingsModel();
			foreach ( $items as $id ) {
				$lm->resubmitItem( $id );
			}
			$this->showMessage( __('Selected items were prepared for resubmission.','wpla') );
		}

		if ( $this->requestAction() == 'wpla_resubmit_all_failed' ) {
		    check_admin_referer( 'wpla_listings_tools' );
			$lm = new WPLA_ListingsModel();
			$items = $lm->getWhere('status', 'failed');
			foreach ( $items as $item ) {
				$lm->resubmitItem( $item->id );
			}
			$this->showMessage( sprintf( __('%s failed items were prepared for resubmission.','wpla'), count($items) ) );
		}

		if ( $this->requestAction() == 'wpla_clear_import_queue' ) {
		    check_admin_referer( 'wpla_listings_tools' );

			$lm = new WPLA_ListingsModel();
			$items = $lm->getWhere('status', 'imported');
			foreach ( $items as $item ) {
				$lm->deleteItem( $item->id );
			}
			$this->showMessage( sprintf( __('%s items have been removed from the import queue.','wpla'), count($items) ) );
		}

		// handle toolbar action - prepare listing from product
		if ( $this->requestAction() == 'wpla_prepare_single_listing' ) {

		    check_admin_referer( 'wpla_prepare_single_listing' );

	        // get profile
	        $profile = isset( $_REQUEST['profile_id'] ) ? WPLA_AmazonProfile::getProfile( $_REQUEST['profile_id'] ) : false;

	        if ( $profile ) {
		
				// prepare product
				$listingsModel = new WPLA_ListingsModel();
		        $success = $listingsModel->prepareProductForListing( $_REQUEST['product_id'], $_REQUEST['profile_id'] );
		        // $listingsModel->applyProfileToNewListings( $profile );		      
		        if ( $success ) {
					$this->showMessage( __('New listing was prepared from product.','wpla') );
		        } else {
					$this->showMessage( join('<br>',$listingsModel->warnings ), 1 );
		        }

	        }

		}

		// handle bulk action - get_compet_price
		if ( $this->requestAction() == 'wpla_get_compet_price' ) {
		    check_admin_referer( 'bulk-listings' );

			$this->get_compet_price();
			$this->get_lowest_offers(); // do both
		}

		// handle bulk action - get_lowest_offers
		if ( $this->requestAction() == 'wpla_get_lowest_offers' ) {
		    check_admin_referer( 'bulk-listings' );
			$this->get_lowest_offers();
		}

		// handle wpla_dismiss_imported_products_notice action
		if ( $this->requestAction() == 'wpla_dismiss_imported_products_notice' ) {
			self::updateOption('dismiss_imported_products_notice','1');
		}

	}
	


	public function get_lowest_offers() {
		if ( ! isset($_REQUEST['listing']) ) return;

        // get items
		$listing_ids = is_array( $_REQUEST['listing'] ) ? $_REQUEST['listing'] : array( $_REQUEST['listing'] );
        if ( ! empty($listing_ids) ) {

			$listingsModel = new WPLA_ListingsModel();
			$listings      = WPLA_ListingsModel::getItems( $listing_ids, OBJECT );
			$account_id    = $listings[0]->account_id;
			// echo "<pre>";print_r($listings);echo"</pre>";die();

			// build array of ASINs
			$listing_ASINs = array();
        	foreach ($listings as $listing) {

        		// prevent invalid marketplace errors
        		if ( $account_id != $listing->account_id ) {
					$this->showMessage( 'You can only fetch pricing information from one account at a time. Item '.$listing->asin.' was skipped.',1,1 );
        			continue;
        		}

        		$listing_ASINs[] = $listing->asin;
        	}

        	// limit to 20 ASINs at a time - for now
        	if ( sizeof($listing_ASINs) > 20 ) {
        		$listing_ASINs = array_splice($listing_ASINs, 0, 20);
				$this->showMessage( 'You can only fetch pricing information for up to 20 ASINs at a time.',2,1 );
        	}

        	if ( ! empty($listing_ASINs) ) {

				$api     = new WPLA_AmazonAPI( $account_id );
				$result  = $api->getLowestOfferListingsForASIN( $listing_ASINs );
				// echo "<pre>";print_r($result);echo"</pre>";die();

				if ( $result->success ) {
					$message = '';				
					foreach ( $result->products as $asin => $product ) {
						foreach ( $product->prices as $price ) {
							$lowest_price = $price->LandedPrice;
							$condition    = $price->condition;
							$subcondition = $price->subcondition;
							$shipping_fee = $price->Shipping;
							$shipping_msg = $shipping_fee ? "incl. $shipping_fee shipping" : 'free shipping';
							$lowest_price = number_format_i18n( floatval($lowest_price), 2 );
							$message .= sprintf( 'Lowest Offer for %s: %s ( %s / %s / %s )<br>', $asin, $lowest_price, $condition, $subcondition, $shipping_msg );
						}
						if ( empty($product->prices) ) {
							$message .= sprintf( 'No offers found for %s<br>', $asin );
						}
					}
					wpla_show_message( $message );
				}

				// process result
				$listingsModel->processLowestOfferPricingResult( $result, $account_id );
        	}

        }

	} // get_lowest_offers()
	


	public function get_compet_price() {
		if ( ! isset($_REQUEST['listing']) ) return;

        // get items
		$listing_ids = is_array( $_REQUEST['listing'] ) ? $_REQUEST['listing'] : array( $_REQUEST['listing'] );
        if ( ! empty($listing_ids) ) {

			$listingsModel = new WPLA_ListingsModel();
			$listings      = WPLA_ListingsModel::getItems( $listing_ids, OBJECT );
			$account_id    = $listings[0]->account_id;
			// echo "<pre>";print_r($listings);echo"</pre>";die();

			// build array of ASINs
			$listing_ASINs = array();
        	foreach ($listings as $listing) {

        		// prevent invalid marketplace errors
        		if ( $account_id != $listing->account_id ) {
					$this->showMessage( 'You can only fetch pricing information from one account at a time. Item '.$listing->asin.' was skipped.',1,1 );
        			continue;
        		}

        		$listing_ASINs[] = $listing->asin;
        	}

        	// limit to 20 ASINs at a time - for now
        	if ( sizeof($listing_ASINs) > 20 ) {
        		$listing_ASINs = array_splice($listing_ASINs, 0, 20);
				$this->showMessage( 'You can only fetch pricing information for up to 20 ASINs at a time.',2,1 );
        	}

        	if ( ! empty($listing_ASINs) ) {

				$api     = new WPLA_AmazonAPI( $account_id );
				$result  = $api->getCompetitivePricingForId( $listing_ASINs );
				// echo "<pre>";print_r($result);echo"</pre>";

				if ( $result->success ) {
					$message = '';				
					foreach ( $result->products as $asin => $product ) {
						foreach ( $product->prices as $price ) {
							$lowest_price = $price->LandedPrice;
							$condition    = $price->condition;
							$subcondition = $price->subcondition;
							$shipping_fee = $price->Shipping;
							$shipping_msg = $shipping_fee ? "incl. $shipping_fee shipping" : 'free shipping';
							$lowest_price = number_format_i18n( floatval($lowest_price), 2 );
							$message .= sprintf( 'BuyBox price for %s: %s ( %s / %s / %s )<br>', $asin, $lowest_price, $condition, $subcondition, $shipping_msg );
						}
						if ( empty($product->prices) ) {
							$message .= sprintf( 'No Buy Box price found for %s<br>', $asin );
						}
					}
					wpla_show_message( $message );
				}

				// process result
				$listingsModel->processBuyBoxPricingResult( $result, $account_id );
        	}

        }

	} // get_compet_price()
	


	public function showNotifications() {

	    if ( get_option( 'wpla_validate_sku', 1 ) ) {
            self::checkForInvalidSkus();
        }

		self::checkForSpacesInASINs();
		self::checkForDeletedProducts();

        // get listing status summary
		$listingsModel = new WPLA_ListingsModel();
        $summary = WPLA_ListingsModel::getStatusSummary();
        $no_asin = $listingsModel->getAllOnlineWithoutASIN();

        // check for changed, matched and prepared items - and show message
        $is_feed_page = isset($_GET['page']) && ($_GET['page'] == 'wpla-feeds');
        if ( isset($summary->changed) ||  isset($summary->prepared) ||  isset($summary->matched) ) {
        	$next_schedule = $this->print_schedule_info( 'wpla_update_schedule' );

        	// build nice combined message
        	$summary_msg = '';
        	$summary_array = array();
        	foreach ( array('changed','prepared','matched') as $status) {
        		if ( ! isset($summary->$status) ) continue;
        		$link_url   = 'admin.php?page=wpla&listing_status='.$status;
        		$link_title = $summary->$status . ' ' . $status;
        		$summary_array[] = '<a href="'.$link_url.'">'.$link_title.'</a>';
        	}
        	$summary_msg = join(' and ', $summary_array);

			$msg  = '<p>';
			$msg .= sprintf( __('%s product(s) will be submitted to Amazon %s.','wpla'), $summary_msg, $next_schedule );
			$msg .= '&nbsp;&nbsp;';

			if ( $is_feed_page ) {
				$msg .= '<a href="admin.php?page=wpla-feeds&action=submit_pending_feeds_to_amazon&_wpnonce='. wp_create_nonce( 'wpla_submit_pending_feeds' ) .'" id="" class="button button-small wpl_job_button">' . __('Submit pending feeds','wpla') . '</a>';
			} else {
				$msg .= '<a href="admin.php?page=wpla-feeds" id="" class="button button-small wpl_job_button">' . __('Visit feeds','wpla') . '</a>';				
			}

			$msg .= '</p>';
			$this->showMessage( $msg );		
        }

        // check for prepared items and display info
        if ( isset($summary->prepared) ) {
        	// $next_schedule = $this->print_schedule_info( 'wpla_update_schedule' );
			// $msg  = '<p>';
			// $msg .= sprintf( __('%d %s product(s) will be submitted to Amazon %s.','wpla'), $summary->prepared, 'prepared', $next_schedule );
			// $msg .= '&nbsp;&nbsp;';
			// $msg .= '<a href="admin.php?page=wpla&listing_status=prepared" id="" class="button button-small wpl_job_button">' . __('Show products','wpla') . '</a>';
			// $msg .= '&nbsp;&nbsp;';
			// $msg .= '<a href="admin.php?page=wpla-feeds" id="" class="button button-small wpl_job_button">' . __('Visit feeds','wpla') . '</a>';
			// $msg .= '</p>';
			// $this->showMessage( $msg );		

			// check prepared products for problems
			$problems = WPLA_FeedValidator::checkPreparedProducts();
			if ( $problems ) $this->showMessage( $problems, 1 );		
        }

		// check changed products for problems
        if ( isset($summary->changed) ) {
			$problems = WPLA_FeedValidator::checkChangedProducts();
			if ( $problems ) $this->showMessage( $problems, 1 );		
        }

        // check for new online items without ASIN
        if ( sizeof($no_asin) ) {
			$msg  = '<p>';
			$msg .= sprintf( __('There are %s newly added product(s) which need to be updated from Amazon.','wpla'), sizeof($no_asin) );
			$msg .= '&nbsp;&nbsp;';
			$msg .= '<a id="btn_batch_update_no_asin" class="btn_batch_update_no_asin button button-primary button-small wpl_job_button">' . __('Update products','wpla') . '</a>';
			$msg .= '&nbsp;&nbsp;';
			$msg .= '<a href="admin.php?page=wpla&listing_status=no_asin" id="" class="button button-small wpl_job_button">' . __('Show products','wpla') . '</a>';
			$msg .= '<br><!br>';
			$msg .= '<small>This step is required to fetch the ASIN and other details that were assigned by Amazon when the product was created.</small>';
			$msg .= '</p>';
			$this->showMessage( $msg );				
        }

        // check for imported items and display reminder
        $dismiss_imported_products_notice = self::getOption('dismiss_imported_products_notice');
        $is_imported_page = isset($_GET['listing_status']) && ($_GET['listing_status'] == 'imported');
        if ( $is_imported_page ) $dismiss_imported_products_notice = false;
        if ( isset($summary->imported) && ! $dismiss_imported_products_notice ) {
			$msg  = '<p>';
			$msg .= sprintf( __('There are %s imported item(s) which can be created in WooCommerce.','wpla'), $summary->imported );
			$msg .= '&nbsp;&nbsp;';
			$msg .= '<a id="btn_batch_create_products_reminder" class="button button-primary button-small wpl_job_button">' . __('Create products','wpla') . '</a>';
			if ( ! $is_imported_page ) {
				$msg .= '&nbsp;&nbsp;';
				$msg .= '<a href="admin.php?page=wpla&action=wpla_dismiss_imported_products_notice" class="button button-small wpl_job_button">' . __('Dismiss','wpla') . '</a>';
			}
			$msg .= '<br>';
			$msg .= '<small>';
			$msg .= __('If a product with a matching SKU exists in WooCommerce it will be linked to the product on Amazon.','wpla');
			$msg .= '</small>';
			$msg .= '</p>';
			$this->showMessage( $msg );				
        }

	} // showNotifications()
	

	static function checkForDeletedProducts() {
		global $wpdb;

        $items = $wpdb->get_var("
            SELECT count(a.id)
            FROM {$wpdb->prefix}amazon_listings a
            LEFT JOIN {$wpdb->posts} p ON a.post_id = p.ID
            WHERE a.post_id <> 0
              AND p.ID IS NULL
            ORDER BY a.post_id
        ");

		if ( ! empty($items) ) {
			$link_url    = wp_nonce_url( 'admin.php?page=wpla-tools&tab=developer&action=wpla_fix_deleted_products', 'wpla_tools_page' );
			$link_button = '&nbsp;&nbsp;<a href="'.$link_url.'" class="button button-small button-primary">Fix It Now</a>';
			wpla_show_message( sprintf('Warning: There are %s listings linked to missing WooCommerce products.<br>These items need to be removed from WP-Lister to be able to list or import them again.', $items ) . $link_button, 'error' );
		}

	} // checkForDeletedProducts()
	

	static function checkForSpacesInASINs() {
		global $wpdb;

        $items = $wpdb->get_var("
            SELECT count(id)
            FROM {$wpdb->prefix}amazon_listings
            WHERE asin LIKE '% %';
        ");

		if ( ! empty($items) ) {
			$link_url    = wp_nonce_url( 'admin.php?page=wpla-tools&tab=developer&action=wpla_fix_spaces_in_asins', 'wpla_tools_page' );
			$link_button = '&nbsp;&nbsp;<a href="'.$link_url.'" class="button button-small button-primary">Fix It Now</a>';
			wpla_show_message( sprintf('Warning: There are %s listings where the ASIN contains one or more spaces.<br>These space characters need to be removed to allow WP-Lister to work properly.', $items ) . $link_button, 'error' );
		}

	} // checkForSpacesInASINs()

    public static function checkForInvalidSkus() {
        global $wpdb;

        $items = $wpdb->get_var("
            SELECT count(id)
            FROM {$wpdb->prefix}amazon_listings
            WHERE sku LIKE '0%';
        ");

        if ( ! empty($items) ) {
            wpla_show_message( sprintf('Warning: There are %s listings where the SKU starts with a 0 which is invalid. Amazon ignores all zeroes to the left of the first alphanumeric value.', $items ) , 'error' );
        }
    }

	public function displayListingsPage() {
		$this->check_wplister_setup();

		// edit listing
		if ( $this->requestAction() == 'edit' ) {
			return $this->displayEditPage();
		}
	
		// handle actions and show notes
		$this->handleActions();
		$this->showNotifications();

		// show warning if duplicate products found
		$this->checkForDuplicates();

	    // create table and fetch items to show
	    // $this->listingsTable = new WPLA_ListingsTable();
	    $this->listingsTable->prepare_items();

		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			'listingsTable'				=> $this->listingsTable,
			'preview_html'				=> isset($preview_html) ? $preview_html : '',
		
			'form_action'				=> 'admin.php?page='.self::ParentMenuId
		);
		$this->display( 'listings_page', $aData );

	}


	public function displayEditPage() {

		// get item
		$listingsModel = new WPLA_ListingsModel();
		$item = $listingsModel->getItem( $_REQUEST['listing'] );

		// get other data
		$profiles = WPLA_AmazonProfile::getAll();

		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			'item'						=> $item,
			'feed_profiles'				=> $profiles,
			
			'form_action'				=> 'admin.php?page='.self::ParentMenuId . ( isset($_REQUEST['paged']) ? '&paged='.$_REQUEST['paged'] : '' )
		);
		$this->display( 'listings_edit_page', array_merge( $aData, $item ) );

	}



	private function saveListing() {
		global $wpdb;	
		if ( ! current_user_can('manage_amazon_listings') ) return;

		// sql columns
		$item = array();
		$item['id'] 						= $this->getValueFromPost( 'listing_id' );
		$item['listing_title'] 				= stripslashes( $this->getValueFromPost( 'listing_title' ) );
		$item['price'] 						= trim( $this->getValueFromPost( 'price'    ) );
		$item['quantity'] 					= trim( $this->getValueFromPost( 'quantity' ) );
		$item['profile_id']					= $this->getValueFromPost( 'profile_id' );
		$item['quantity_sold'] 				= $this->getValueFromPost( 'quantity_sold' );

		// if item is online change status to changed
		// if ( 'online' == $this->getValueFromPost( 'status' ) ) {
		// 	$item['status'] = 'changed';
		// }

		// handle "mark as changed" checkbox
		if ( 'yes' == $this->getValueFromPost( 'mark_as_changed_on_save' ) ) {
			$item['status'] = 'changed';			
		}

		// handle developer settings
		if ( $this->getValueFromPost( 'enable_dev_mode' ) == '1' ) {
			$item['status']        = $this->getValueFromPost( 'listing_status' );
			$item['asin']          = trim( $this->getValueFromPost( 'asin'    ) );
			$item['sku']           = trim( $this->getValueFromPost( 'sku'     ) );
			$item['post_id']       = trim( $this->getValueFromPost( 'post_id' ) );
			$item['source']        = trim( $this->getValueFromPost( 'source'  ) );
			$item['fba_fcid']      = $this->getValueFromPost( 'fba_fcid' );
			$item['fba_quantity']  = $this->getValueFromPost( 'fba_quantity' );
			$item['pnq_status']    = $this->getValueFromPost( 'pnq_status' );
		}

		// update listing
		$result = $wpdb->update( $wpdb->prefix.'amazon_listings', $item, 
			array( 'id' => $item['id'] ) 
		);

		// proper error handling
		if ($result===false) {
			$this->showMessage( "There was a problem saving your listing.<br>SQL:<pre>".$wpdb->last_query.'</pre>', true );	
			return;
		} else {
			$this->showMessage( __('Listing updated.','wpla') );
		}
		
	} // saveListing()

	public function checkForDuplicates() {

		// show warning if duplicate products found
		$listingsModel     = new WPLA_ListingsModel();
		$duplicateProducts = $listingsModel->getAllDuplicateProducts();
		// $duplicateASINs    = $listingsModel->getAllDuplicateASINs();
		$duplicateSKUs     = $listingsModel->getAllDuplicateSKUs();
		$msg               = '';

		// if ( ! empty($duplicateProducts) || ! empty($duplicateASINs) || ! empty($duplicateSKUs) ) {
		if ( ! empty($duplicateProducts) || ! empty($duplicateSKUs) ) {

			$duplicates_total = max( count($duplicateProducts), count($duplicateSKUs) );
			$msg .= '<p><b>' . sprintf( __('Warning: There are %s duplicate listings. Your action is required.','wpla'), $duplicates_total ) . '</b>';
			$msg .= '&nbsp; <a href="#" onclick="jQuery(\'#wpla_dupe_details\').toggle();return false;" class="button button-small">'.__('Show duplicates','wpla').'</a></p>';


			$msg .= '<div id="wpla_dupe_details" style="display:none"><p>';
			$msg .= __('To list on Amazon it is important for each product to have a unique SKU.','wpla');
			$msg .= ' ';
			$msg .= __('Additionally, there can be only one listing per product per account.','wpla');
			$msg .= '<br>';
			$msg .= '<br>';
			$msg .= __('Please keep only one listing and remove all other duplicates from the database.','wpla');
			$msg .= '<br><br>';

			$msg .= $this->renderDupeTable( $duplicateSKUs, 'sku' );
			// $msg .= $this->renderDupeTable( $duplicateASINs, 'asin' );
			$msg .= $this->renderDupeTable( $duplicateProducts, 'post_id' );

			// $msg .= __('If you are not planning to use the synchronize sales option, you can hide this warning in settings.','wpla');
			$msg .= '</p></div>';
			$this->showMessage( $msg, 2 );				
		}
	}

	public function renderDupeTable( $listings, $column = 'post_id' ) {
		if ( empty($listings) ) return '';

        // get current page with paging as url param
        $page = $_REQUEST['page'];
        if ( isset( $_REQUEST['paged'] )) $page .= '&paged='.$_REQUEST['paged'];

		$listingsModel = new WPLA_ListingsModel();
		$msg           = '';

		foreach ($listings as $dupe) {

			$account_title = WPLA_AmazonAccount::getAccountTitle( $dupe->account_id );

			$msg .= '<b>'.__('Listings for','wpla').' '.strtoupper($column).' '.$dupe->$column.' ('.$account_title.'):</b>';
			$msg .= '<br>';

			$duplicateListings = $listingsModel->findAllListingsByColumn( $dupe->$column, $column, $dupe->account_id );
			
			$msg .= '<table style="width:100%">';
			foreach ( $duplicateListings as $listing ) {

				$color = $listing->status == 'archived' ? 'silver' : '';

				// check if WooCommerce SKU matches Amazon SKU
				$woo_sku   = get_post_meta( $listing->post_id, '_sku', true );
				$sku_label = $listing->sku == $woo_sku ? $woo_sku : '<span style="color:darkred">'.$woo_sku.' / '.$listing->sku.'</span>';

				$msg .= '<tr><td style="width:40%;">';					
				$msg .= '<span style="color:'.$color.'">';					
				$msg .= $listing->listing_title;
				$msg .= '</span>';

				$msg .= '</td><td style="width:10%;">';
				$msg .= '<i style="color:silver">'.$listing->product_type.'</i>';					

				$msg .= '</td><td style="width:10%;">';
				$msg .= '<a href="admin.php?page=wpla&s='.$listing->sku.'" title="SKU" target="_blank">';
				$msg .= $sku_label.'</a>';

				$msg .= '</td><td style="width:10%;">';
				$msg .= '<a href="admin.php?page=wpla&s='.$listing->asin.'" title="ASIN" target="_blank">';
				$msg .= $listing->asin.'</a>';

				$msg .= '</td><td style="width:10%;">';
				$msg .= '<a href="admin.php?page=wpla&s='.$listing->post_id.'" title="Product ID" target="_blank">';
				$msg .= 'ID '.$listing->post_id.'</a>';

				$msg .= '</td><td style="width:10%;">';
				$msg .= '<i>'.$listing->status.'</i>';					

				// if ( in_array( $listing->status, array( 'prepared', 'verified', 'ended', 'sold' ) ) ) {
				// 	$archive_link = sprintf('<a class="archive button button-small" href="?page=%s&action=%s&listing=%s">%s</a>',$page,'archive',$listing->id,__('Click to move to archive','wpla'));
				// 	$msg .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$archive_link;
				// 	$msg .= '<br>';
				// }

				$msg .= '</td><td align="right" style="width:10%;">';
				$delete_btn = sprintf('<a class="delete button button-small button-secondary" href="?page=%s&action=%s&listing=%s">%s</a>',$page,'delete',$listing->id,__('Remove from database','wpla'));
				$msg .= $delete_btn;

				$msg .= '</td></tr>';
			}
			$msg .= '</table>';
			$msg .= '<br>';

		}

		return $msg;
	}


	public function previewListing( $id ) {
	
		// init model
		$ibm = new ItemBuilderModel();
		$preview_html = $ibm->getFinalHTML( $id );
		echo $preview_html;
		exit();		

	}

	public function fixSubmenu() {
		global $submenu;
		if ( isset( $submenu[self::ParentMenuId] ) ) {
			$submenu[self::ParentMenuId][0][0] = __('Listings','wpla');
		}
	}


	public function renderDeveloperOptions() {
		?>
		<div class="hidden" id="screen-options-wrap" style="display: block;">
			<form method="post" action="" id="dev-settings">
				<h5>Show on screen</h5>
				<div class="metabox-prefs">
						<label for="dev-hide">
							<input type="checkbox" onclick="jQuery('#DeveloperToolBox').toggle();return false;" value="dev" id="dev-hide" name="dev-hide" class="hide-column-tog" checked>
							Developer options
						</label>
					<br class="clear">
				</div>
			</form>
		</div>
		<?php
	}


}
