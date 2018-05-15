<?php
/**
 * WPLA_AmazonFeed class
 *
 */

// class WPLA_AmazonFeed extends WPLA_NewModel {
class WPLA_AmazonFeed {

	const TABLENAME = 'amazon_feeds';

	var $id      = null;
	var $data    = null;
	var $results = null;
	var $types   = array();

	function __construct( $id = null ) {
		
		$this->init();

		if ( $id ) {
			$this->id = $id;
			
			// load data into object
			$feed = self::getFeed( $id );
			foreach( $feed AS $key => $value ){
			    $this->$key = $value;
			}

			return $this;
		}

	}

	function init()	{

		$this->types = array(
			'_POST_FLAT_FILE_PRICEANDQUANTITYONLY_UPDATE_DATA_' => 'Price and Quantity Update Feed',
			'_POST_FLAT_FILE_LISTINGS_DATA_'                 	=> 'Listings Data Feed',
			'_CHECK_FLAT_FILE_LISTINGS_DATA_'                 	=> 'Listings Data Feed (check only)',
			'_POST_FLAT_FILE_FULFILLMENT_DATA_'      			=> 'Order Fulfillment Feed',
			'_POST_FLAT_FILE_FULFILLMENT_ORDER_REQUEST_DATA_'   => 'FBA Shipment Fulfillment Feed',
			'_POST_FLAT_FILE_INVLOADER_DATA_'                   => 'Inventory Loader Feed',
		);

		$this->fieldnames = array(
			'FeedSubmissionId',
			'FeedType',
			'template_name',
			'FeedProcessingStatus',
			'results',
			'success',
			'status',
			'SubmittedDate',
			'StartedProcessingDate',
			'CompletedProcessingDate',
			'GeneratedFeedId',
			'date_created',
			'account_id',
			'line_count',
			'data'
		);

	} // init()

	// get single feed data
	static function getFeed( $id )	{
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;
		
		$item = $wpdb->get_row( $wpdb->prepare("
			SELECT *
			FROM $table
			WHERE id = %d
		", $id
		), OBJECT);

		return $item;
	}

	// get single feed by FeedSubmissionId
	static function getFeedBySubmissionId( $FeedSubmissionId )	{
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;
		
		$item = $wpdb->get_row( $wpdb->prepare("
			SELECT *
			FROM $table
			WHERE FeedSubmissionId = %s
		", $FeedSubmissionId
		), OBJECT);

		return $item;
	}

	// get all feeds
	static function getAll() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$items = $wpdb->get_results("
			SELECT *
			FROM $table
			ORDER BY sort_order ASC
		", OBJECT_K);

		return $items;
	}

	// get all submitted feeds that need to be checked and eventually processed
	static function getSubmittedFeedsForAccount( $account_id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$items = $wpdb->get_results( $wpdb->prepare("
			SELECT *
			FROM $table
			WHERE account_id = %s
			  AND ( FeedProcessingStatus = '_SUBMITTED_' 
			  	 OR FeedProcessingStatus = '_IN_PROGRESS_' 
			  	 OR status = 'submitted' )
			ORDER BY SubmittedDate DESC
		", $account_id
		), OBJECT_K);

		return $items;
	}

	// get pending feed
	static function getPendingFeedId( $feed_type, $template_name, $account_id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;
		$template_name = esc_sql( $template_name );
		$where_sql     = $template_name ? "AND template_name = '$template_name'" : '';

		$item = $wpdb->get_var( $wpdb->prepare("
			SELECT id
			FROM $table
			WHERE status     = 'pending'
			  AND account_id = %d
			  AND FeedType   = %s
			  $where_sql
		",
		$account_id,
		$feed_type
		));

		return $item;
	}

	// get all pending feeds for account
	static function getAllPendingFeedsForAccount( $account_id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$item_ids = $wpdb->get_col( $wpdb->prepare("
			SELECT id
			FROM $table
			WHERE status     = 'pending'
			  AND account_id = %d
		", $account_id ));

		$feeds = array();
		foreach ( $item_ids as $feed_id ) {
			$feeds[] = new WPLA_AmazonFeed( $feed_id );
		}

		return $feeds;
	}

	// get pending feeds summary
	static function getAllPendingFeeds() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$item_ids = $wpdb->get_col("
			SELECT id
			FROM $table
			WHERE status = 'pending'
		");

		return $item_ids;
	}

	// get all Price&Quantity feeds for a specific SKU
	static function getAllPnqFeedsForSKU( $sku ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$item_ids = $wpdb->get_col( $wpdb->prepare("
			SELECT id
			FROM $table
			WHERE FeedType = '_POST_FLAT_FILE_PRICEANDQUANTITYONLY_UPDATE_DATA_'
			  AND data LIKE %s
			ORDER BY id DESC
			LIMIT 20
		", '%'.$sku.'%' ) );

		return $item_ids;
	}


	function getDataArray() {
		if ( ! $this->data || empty( $this->data ) ) return array();

		$feed_data = $this->data;
		if ( in_array( $this->FeedType, array('_POST_FLAT_FILE_LISTINGS_DATA_','_CHECK_FLAT_FILE_LISTINGS_DATA_') ) ) {
			// remove first two rows - headers are in 3rd row
			$rows_to_remove = 2;
			if ( $this->template_name == 'Offer' ) $rows_to_remove = 1;
			$feed_data = implode("\n", array_slice(explode("\n", $feed_data), $rows_to_remove ));
		}

		$rows = WPLA_ReportProcessor::csv_to_array( $feed_data );
		return $rows;		
	}

	function getDataRowForSKU( $sku ) {
		if ( ! $this->data || empty( $this->data ) ) return false;

		$data_rows = $this->getDataArray();
		foreach ( $data_rows as $row ) {
			if ( $row['sku'] == $sku )
				return $row;
		}

		return false;
	}


	function createCheckFeed() {
		if ( ! $this->id ) return;
		if ( ! $this->data ) return;

		// clone feed
		$this->id = null;
		$this->FeedType = str_replace( '_POST_', '_CHECK_', $this->FeedType );
		$this->add();
		
		// submit cloned feed
		$result = $this->submit();

		return $result;
	}

	function isCheckFeed() {
		if ( ! $this->FeedType ) return false;
		if ( substr( $this->FeedType, 0, 7 ) == '_CHECK_' ) return true;
		return false;
	}


	function cancel() {
		if ( ! $this->id ) return;

		$api = new WPLA_AmazonAPI( $this->account_id );
		$result = $api->cancelFeedSubmission( $this->FeedSubmissionId );
		// echo "<pre>";print_r($result);echo"</pre>";die();

		if ( $result->success ) {
			
			// update feed status
			// $this->FeedSubmissionId     = $result->FeedSubmissionId;
			// $this->FeedProcessingStatus = $result->FeedProcessingStatus;
			// $this->SubmittedDate        = $result->SubmittedDate;
			// $this->status 		    	= 'cancelled';
			// $this->update();

		} // success

		return $result;
	} // cancel()

	function submit() {
		if ( ! $this->id ) return;
		if ( ! $this->data ) return;
        if ( $this->status != 'pending' ) return;

		$api = new WPLA_AmazonAPI( $this->account_id );

		// adjust feed encoding
		$feed_content = $this->data;
		if ( get_option( 'wpla_feed_encoding' ) != 'UTF-8' ) {
			$feed_content = utf8_decode( $feed_content );
		}

		$result = $api->submitFeed( $this->FeedType, $feed_content );
		// echo "<pre>";print_r($result);echo"</pre>";die();

		if ( $result->success ) {
			
			// update feed status
			$this->FeedSubmissionId     = $result->FeedSubmissionId;
			$this->FeedProcessingStatus = $result->FeedProcessingStatus;
			$this->SubmittedDate        = $result->SubmittedDate;
			$this->status 		    	= 'submitted';
			$this->update();

			// increase feeds in progress
		    $feeds_in_progress = get_option( 'wpla_feeds_in_progress', 0 );
			update_option( 'wpla_feeds_in_progress', $feeds_in_progress + 1 );


			// update status of submitted products - except for check feeds
			if ( ! $this->isCheckFeed() ) {

				$lm = new WPLA_ListingsModel();
				// $rows = WPLA_ReportProcessor::csv_to_array( $this->data );
				$rows = $this->getDataArray();
				foreach ($rows as $row) {

					$listing_sku = isset( $row['sku'] ) ? $row['sku'] : $row['item_sku'];
					$listing_item = $lm->getItemBySKU( $listing_sku );

					if ( $listing_item ) {

						// check feed type
						switch ($this->FeedType) {

							// Listing Data feed
							case '_POST_FLAT_FILE_LISTINGS_DATA_':

								$listing_data = array();
								$listing_data['status']  = 'submitted';
								$listing_data['history'] = '';
								WPLA()->logger->info('changing status to submitted for SKU '.$listing_sku);

								// update date_published - only if not set
								if ( ! $listing_item->date_published )
									$listing_data['date_published'] = gmdate('Y-m-d H:i:s');

								break;
							
							// Price And Quantity feed
							case '_POST_FLAT_FILE_PRICEANDQUANTITYONLY_UPDATE_DATA_':

								$listing_data = array();
								$listing_data['pnq_status'] = '2'; // submitted
								WPLA()->logger->info('changing PNQ status to 2 (submitted) for SKU '.$listing_sku);

								break;
							
							// Inventory Loader (delete) feed
							case '_POST_FLAT_FILE_INVLOADER_DATA_':

								$listing_data = array();
								$listing_data['status'] = 'trashed'; // submitted for deletion
								WPLA()->logger->info('changing status to trashed for SKU '.$listing_sku);

								break;
							
							default:
								WPLA()->logger->warn('nothing to process for feed type '.$this->FeedType.' - SKU '.$listing_sku);
								break;
						}

						// update database
						$where_array = array( 'sku' => $listing_sku, 'account_id' => $this->account_id );
						$lm->updateWhere( $where_array, $listing_data );				

					} else {
						WPLA()->logger->warn('no listing found for SKU '.$listing_sku);
					} // if $listing_item
					
				} // for each row

			} // not check feed

		} // success

		return $result;
	} // submit()

	function loadSubmissionResult() {
		if ( ! $this->id ) return;
		if ( ! $this->FeedSubmissionId ) return;
		if ( $this->FeedProcessingStatus != '_DONE_' ) return;

		$api = new WPLA_AmazonAPI( $this->account_id );

		$result = $api->getFeedSubmissionResult( $this->FeedSubmissionId );

		if ( $result && $result->success ) {
			$this->results = utf8_encode( $result->content ); // required for amazon.fr
			$this->update();
		}

		return $result;
	} // loadSubmissionResult()

	function processSubmissionResult() {
		WPLA()->logger->info('processSubmissionResult() - feed '.$this->id);
		if ( ! $this->id ) return;
		if ( ! $this->results ) return;

		$this->errors   = array();
		$this->warnings = array();

		// fetch list of submitted product SKUs
		$feed_rows = $this->getDataArray();
		WPLA()->logger->info('data rows   for feed '.$this->FeedSubmissionId.' ('.$this->id.'): '.sizeof($feed_rows));

		// extract result csv data
		$result_content = implode("\n", array_slice(explode("\n", $this->results), 4)); // remove summary rows
		$result_rows = WPLA_ReportProcessor::csv_to_array( $result_content );
		WPLA()->logger->info('result rows for feed '.$this->FeedSubmissionId.' ('.$this->id.'): '.sizeof($result_rows));
		WPLA()->logger->info('result rows '.print_r($result_rows,1));

		// process results
		if ( $this->FeedType == '_POST_FLAT_FILE_FULFILLMENT_DATA_' ) {
			$this->processOrderFulfillmentResults( $feed_rows, $result_rows );
		} elseif ( $this->FeedType == '_POST_FLAT_FILE_FULFILLMENT_ORDER_REQUEST_DATA_' ) {
			$this->processOrderFbaResults( $feed_rows, $result_rows );
		} elseif ( $this->FeedType == '_POST_FLAT_FILE_PRICEANDQUANTITYONLY_UPDATE_DATA_' ) {
			$this->processListingPnqResults( $feed_rows, $result_rows );
		} else {
			$this->processListingDataResults( $feed_rows, $result_rows );			
		}

		// update feed status
		$this->success = sizeof( $this->warnings ) > 0 ? 'warning' : 'success';
		$this->success = sizeof( $this->errors ) > 0 ? 'error' : $this->success;
		$this->status = 'processed';
		$this->update();
		WPLA()->logger->info('feed has been processed');

		return true;
	} // processSubmissionResult()

	public function processListingDataResults( $feed_rows, $result_rows ) {

		$lm = new WPLA_ListingsModel();

		// index results by SKU
		$results = array();
		foreach ( $result_rows as $r ) {
			if ( ! isset( $r['sku'] ) && isset( $r['SKU'] ) ) $r['sku'] = $r['SKU']; // translate column SKU -> sku
			if ( ! isset( $r['sku'] ) || empty( $r['sku'] ) ) continue;
			$results[ $r['sku'] ][] = $r;
			WPLA()->logger->info('result sku: '.$r['sku']);
		}

		// process each result row
		foreach ($feed_rows as $row) {
			$listing_data = array();

			$row_sku = isset( $row['item_sku'] ) ? $row['item_sku'] : $row['sku'];
			if ( ! $row_sku ) {
				WPLA()->logger->warn('skipping row without SKU: '.print_r($row,1));
				continue;
			}

			$row_results = isset( $results[ $row_sku ] ) ? $results[ $row_sku ] : false;
			WPLA()->logger->info('processing feed sku: '.$row_sku);

			// check if this is a delete feed (Inventory Loader)
			$add_delete_column = isset($row['add-delete']) ? $row['add-delete'] : '';
			$is_delete_feed = $add_delete_column == 'x' ? true : false;

			// if there are no result rows for this SKU, set status to 'online'
			if ( ! $row_results ) {

				if ( $is_delete_feed ) {
					$listing = $lm->getItemBySKU( $row_sku );
					if ( ! $listing ) continue;
					if ( $listing->status == 'trashed' ) {
						$lm->deleteItem( $listing->id );
						WPLA()->logger->info('DELETED listing ID '.$listing->id.' SKU: '.$row_sku);
					} else {					
						WPLA()->logger->warn('INVALID listing status for deletion - ID '.$listing->id.' / SKU: '.$row_sku.' / status: '.$listing->status);
					}
					continue;
				}

				$listing_data['status']  = 'online';
				$listing_data['history'] = '';
				$lm->updateWhere( array( 'sku' => $row_sku, 'account_id' => $this->account_id ), $listing_data );
				WPLA()->logger->info('changed status to online: '.$row_sku);
				continue;

			}

			// handle errors and warnings
			$errors         = array();
			$warnings       = array();
			$processed_keys = array();
			foreach ($row_results as $row_result) {

				// translate error-type
				if ( $row_result['error-type'] == 'Fehler' ) 		$row_result['error-type'] = 'Error';	// amazon.de
				if ( $row_result['error-type'] == 'Warnung' ) 		$row_result['error-type'] = 'Warning';
				if ( $row_result['error-type'] == 'Erreur' ) 		$row_result['error-type'] = 'Error';	// amazon.fr
				if ( $row_result['error-type'] == 'Avertissement' ) $row_result['error-type'] = 'Warning';

				// compute hash to identify duplicate errors
				$row_key = md5( $row_result['sku'] . $row_result['error-code'] . $row_result['error-type'] . $row_result['original-record-number'] );

				// store feed id in error array
				$row_result['feed_id'] = $this->id;

				if ( 'Error' == $row_result['error-type'] ) {

					WPLA()->logger->info('error: '.$row_sku.' - '.$row_key.' - '.$row_result['error-message']);
					if ( ! in_array($row_key, $processed_keys) ) {
						$errors[]         = $row_result;
						$processed_keys[] = $row_key;
					}

				} elseif ( 'Warning' == $row_result['error-type'] ) {

					WPLA()->logger->info('warning: '.$row_sku.' - '.$row_key.' - '.$row_result['error-message']);
					if ( ! in_array($row_key, $processed_keys) ) {
						$warnings[]       = $row_result;
						$processed_keys[] = $row_key;
					}

				}

			} // foreach result row

			// update listing
			if ( ! empty( $errors ) ) {

				$listing_data['status']  = 'failed';
				$listing_data['history'] = serialize( array( 'errors' => $errors, 'warnings' => $warnings ) );
				$lm->updateWhere( array( 'sku' => $row_sku, 'account_id' => $this->account_id ), $listing_data );				
				WPLA()->logger->info('changed status to FAILED: '.$row_sku);

				$this->errors   = array_merge( $this->errors, $errors);
				$this->warnings = array_merge( $this->warnings, $warnings);

			} elseif ( ! empty( $warnings ) ) {

				$listing_data['status']  = $is_delete_feed ? 'trashed' : 'online';
				$listing_data['history'] = serialize( array( 'errors' => $errors, 'warnings' => $warnings ) );
				$lm->updateWhere( array( 'sku' => $row_sku, 'account_id' => $this->account_id ), $listing_data );				

				WPLA()->logger->info('changed status to online: '.$row_sku);
				$this->warnings = array_merge( $this->warnings, $warnings);

			}

		} // foreach row

	} // processListingDataResults()

	public function processListingPnqResults( $feed_rows, $result_rows ) {

		$lm = new WPLA_ListingsModel();

		// index results by SKU
		$results = array();
		foreach ( $result_rows as $r ) {
			if ( ! isset( $r['sku'] ) || empty( $r['sku'] ) ) continue;
			$results[ $r['sku'] ][] = $r;
			WPLA()->logger->info('result sku: '.$r['sku']);
		}

		// process each result row
		foreach ($feed_rows as $row) {
			$listing_data = array();

			$row_sku = $row['sku'];
			if ( ! $row_sku ) {
				WPLA()->logger->warn('skipping row without SKU: '.print_r($row,1));
				continue;
			}

			$row_results = isset( $results[ $row_sku ] ) ? $results[ $row_sku ] : false;
			WPLA()->logger->info('processing feed sku: '.$row_sku);

			// if there are no result rows for this SKU, set status to 'online'
			if ( ! $row_results ) {

				$listing_data['pnq_status']  = '0';
				$lm->updateWhere( array( 'sku' => $row_sku, 'pnq_status' => '2', 'account_id' => $this->account_id ), $listing_data );
				WPLA()->logger->info('changed status to online: '.$row_sku);
				continue;

			}

			// handle errors and warnings
			$errors         = array();
			$warnings       = array();
			$processed_keys = array();
			foreach ($row_results as $row_result) {

				// translate error-type
				if ( $row_result['error-type'] == 'Fehler' ) 		$row_result['error-type'] = 'Error';	// amazon.de
				if ( $row_result['error-type'] == 'Warnung' ) 		$row_result['error-type'] = 'Warning';
				if ( $row_result['error-type'] == 'Erreur' ) 		$row_result['error-type'] = 'Error';	// amazon.fr
				if ( $row_result['error-type'] == 'Avertissement' ) $row_result['error-type'] = 'Warning';

				// compute hash to identify duplicate errors
				$row_key = md5( $row_result['sku'] . $row_result['error-code'] . $row_result['error-type'] . $row_result['original-record-number'] );

				if ( 'Error' == $row_result['error-type'] ) {

					WPLA()->logger->info('error: '.$row_sku.' - '.$row_key.' - '.$row_result['error-message']);
					if ( ! in_array($row_key, $processed_keys) ) {
						$errors[]         = $row_result;
						$processed_keys[] = $row_key;
					}

				} elseif ( 'Warning' == $row_result['error-type'] ) {

					WPLA()->logger->info('warning: '.$row_sku.' - '.$row_key.' - '.$row_result['error-message']);
					if ( ! in_array($row_key, $processed_keys) ) {
						$warnings[]       = $row_result;
						$processed_keys[] = $row_key;
					}

				}

			} // foreach result row

			// update listing
			if ( ! empty( $errors ) ) {

				$listing_data['pnq_status']  = '-1';
				$lm->updateWhere( array( 'sku' => $row_sku, 'pnq_status' => '2', 'account_id' => $this->account_id ), $listing_data );
				WPLA()->logger->info('changed PNQ status to FAILED (-1): '.$row_sku);

				$this->errors   = array_merge( $this->errors, $errors);
				$this->warnings = array_merge( $this->warnings, $warnings);

			} elseif ( ! empty( $warnings ) ) {

				$listing_data['pnq_status']  = '0';
				$lm->updateWhere( array( 'sku' => $row_sku, 'pnq_status' => '2', 'account_id' => $this->account_id ), $listing_data );

				WPLA()->logger->info('changed PNQ status to 0: '.$row_sku);
				$this->warnings = array_merge( $this->warnings, $warnings);

			}

		} // foreach row

	} // processListingPnqResults()

	public function processOrderFulfillmentResults( $feed_rows, $result_rows ) {

		$om = new WPLA_OrdersModel();

		// index results by OrderID
		$results = array();
		foreach ( $result_rows as $r ) {
			if ( ! isset( $r['order-id'] ) || empty( $r['order-id'] ) ) continue;
			$results[ $r['order-id'] ][] = $r;
			WPLA()->logger->info('result order_id: '.$r['order-id']);
		}

		// process each result row
		foreach ($feed_rows as $row) {
			$order_data = array();

			$row_order_id = $row['order-id'];
			if ( ! $row_order_id ) {
				WPLA()->logger->warn('skipping row without OrderID: '.print_r($row,1));
				continue;
			}

			$row_results = isset( $results[ $row_order_id ] ) ? $results[ $row_order_id ] : false;
			WPLA()->logger->info('processing feed OrderID: '.$row_order_id);

			$order = $om->getOrderByOrderID( $row_order_id );
			$post_id = $order->post_id;

			// if there are no result rows for this OrderID, set status to 'Shipped'
			if ( ! $row_results ) {

				$order_data['status']  = 'Shipped';
				// $order_data['history'] = '';
				// $om->updateWhere( array( 'order_id' => $row_order_id, 'account_id' => $this->account_id ), $order_data );				
				WPLA()->logger->info('changed status to Shipped: '.$row_order_id);
				if ( $post_id ) update_post_meta( $post_id, '_wpla_submission_result', 'success' );
				continue;

			}

			// handle errors and warnings
			$errors = array();
			$warnings = array();
			WPLA()->logger->info('processing row results: '.print_r($row_results,1));
			foreach ($row_results as $row_result) {

				if ( 'Error' == $row_result['error-type'] ) {

					WPLA()->logger->info('error: '.$row_order_id.' - '.$row_result['error-message']);
					$errors[] = $row_result;

				} elseif ( 'Warning' == $row_result['error-type'] ) {

					WPLA()->logger->info('warning: '.$row_order_id.' - '.$row_result['error-message']);
					$warnings[] = $row_result;

				}

			} // foreach result row

			// update order
			if ( ! empty( $errors ) ) {

				$order_data['status']  = 'failed';
				// $order_data['history'] = serialize( array( 'errors' => $errors, 'warnings' => $warnings ) );
				// $om->updateWhere( array( 'order_id' => $row_order_id, 'account_id' => $this->account_id ), $order_data );				
				if ( $post_id ) update_post_meta( $post_id, '_wpla_submission_result', serialize( array( 'errors' => $errors, 'warnings' => $warnings ) ) );

				WPLA()->logger->info('changed status to FAILED: '.$row_order_id);
				$this->errors   = array_merge( $this->errors, $errors);
				$this->warnings = array_merge( $this->warnings, $warnings);

			} elseif ( ! empty( $warnings ) ) {

				$order_data['status']  = 'Shipped';
				// $order_data['history'] = serialize( array( 'errors' => $errors, 'warnings' => $warnings ) );
				// $om->updateWhere( array( 'order_id' => $row_order_id, 'account_id' => $this->account_id ), $order_data );				
				if ( $post_id ) update_post_meta( $post_id, '_wpla_submission_result', serialize( array( 'errors' => $errors, 'warnings' => $warnings ) ) );

				WPLA()->logger->info('changed status to Shipped: '.$row_order_id);
				$this->warnings = array_merge( $this->warnings, $warnings);

			}

		} // foreach row

	} // processOrderFulfillmentResults()

	public function processOrderFbaResults( $feed_rows, $result_rows ) {
		$om = new WPLA_OrdersModel();

		// index results by "original-record-number" (feed row index)
		$results = array();
		foreach ( $result_rows as $r ) {
			if ( ! isset( $r['original-record-number'] ) || empty( $r['original-record-number'] ) ) continue;
			$results[ $r['original-record-number'] ][] = $r;
			WPLA()->logger->info('result row found for row: '.$r['original-record-number']);
		}

		// process each result row
		$row_index = 0;
		foreach ($feed_rows as $row) {
			$order_data = array();
			$row_index++;

			$row_order_id = $row['MerchantFulfillmentOrderID'];
			if ( ! $row_order_id ) {
				WPLA()->logger->warn('skipping row without OrderID: '.print_r($row,1));
				continue;
			}

			// find order's $post_id based on MerchantFulfillmentOrderID - required if this site uses custom order numbers
			if ( $post_id = WPLA_OrdersModel::getWooOrderIdByMerchantFulfillmentOrderID( $row_order_id ) ) {
				WPLA()->logger->info('found order post_id '.$post_id.' for Order '.$row_order_id);
			} else {
				$post_id = str_replace( '#', '', $row_order_id ); // fall back to old behavior
			}

			$row_results = isset( $results[ $row_index ] ) ? $results[ $row_index ] : false;
			WPLA()->logger->info('processing feed row '.$row_index.' for Order #'.$post_id);

			// if there are no result rows for this OrderID, set FBA submission status to 'success'
			if ( ! $row_results ) {

				$submission_status = 'success';

				// if the order status is on-hold, set submission status to 'hold'
				$_order = wc_get_order( $post_id );
				if ( $_order->get_status() == 'on-hold' ) $submission_status = 'hold';

				WPLA()->logger->info('changed FBA submission status to '.$submission_status.': '.$row_order_id);
				update_post_meta( $post_id, '_wpla_fba_submission_status', $submission_status );
				continue;
			}

			// handle errors and warnings
			$errors = array();
			$warnings = array();
			WPLA()->logger->info('processing row results: '.print_r($row_results,1));
			foreach ($row_results as $row_result) {

				if ( 'Error' == $row_result['error-type'] ) {

					WPLA()->logger->info('error: '.$row_order_id.' - '.$row_result['error-message']);
					$errors[] = $row_result;

				} elseif ( 'Warning' == $row_result['error-type'] ) {

					WPLA()->logger->info('warning: '.$row_order_id.' - '.$row_result['error-message']);
					$warnings[] = $row_result;

				}

			} // foreach result row

			// update order
			if ( ! empty( $errors ) ) {

				update_post_meta( $post_id, '_wpla_fba_submission_status', 'failed' );
				update_post_meta( $post_id, '_wpla_fba_submission_result', array( 'errors' => $errors, 'warnings' => $warnings ) );
				WPLA()->logger->info("changed FBA submission status to FAILED: $row_order_id (ID $post_id)");

				$this->errors   = array_merge( $this->errors, $errors);
				$this->warnings = array_merge( $this->warnings, $warnings);

			} elseif ( ! empty( $warnings ) ) {

				update_post_meta( $post_id, '_wpla_fba_submission_status', 'success' );
				update_post_meta( $post_id, '_wpla_fba_submission_result', array( 'errors' => $errors, 'warnings' => $warnings ) );
				WPLA()->logger->info("changed FBA submission status to success: $row_order_id (ID $post_id)");

				$this->warnings = array_merge( $this->warnings, $warnings);
			}


		} // foreach row

	} // processOrderFbaResults()

	static public function processFeedsSubmissionList( $feeds, $account ) {
		WPLA()->logger->info( 'processFeedsSubmissionList() - processing '.sizeof($feeds).' feeds for account '.$account->id) ;

		$feeds_in_progress = 0;

		foreach ($feeds as $feed) {
			
			// check if feed exists
			$existing_record = WPLA_AmazonFeed::getFeedBySubmissionId( $feed->FeedSubmissionId );
			if ( $existing_record ) {

				// skip existing feed if it was submitted using another "account" (different marketplace using the same account)
				if ( $existing_record->account_id != $account->id ) {
					WPLA()->logger->info('skipped existing feed '.$existing_record->id.' for account '.$existing_record->account_id);
					continue;
				}

				$new_feed = new WPLA_AmazonFeed( $existing_record->id );

				$new_feed->FeedSubmissionId        = $feed->FeedSubmissionId;
				$new_feed->FeedType                = $feed->FeedType;
				$new_feed->FeedProcessingStatus    = $feed->FeedProcessingStatus;
				$new_feed->SubmittedDate           = $feed->SubmittedDate;
				$new_feed->CompletedProcessingDate = isset( $feed->CompletedProcessingDate ) ? $feed->CompletedProcessingDate : '';
				// $new_feed->results                 = maybe_serialize( $feed );

				// save new record
				$new_feed->update();

			} else {

				// add new record
				$new_feed = new WPLA_AmazonFeed();
				$new_feed->FeedSubmissionId        = $feed->FeedSubmissionId;
				$new_feed->FeedType                = $feed->FeedType;
				$new_feed->FeedProcessingStatus    = $feed->FeedProcessingStatus;
				$new_feed->SubmittedDate           = $feed->SubmittedDate;
				$new_feed->CompletedProcessingDate = isset( $feed->CompletedProcessingDate ) ? $feed->CompletedProcessingDate : '';
				$new_feed->date_created            = $feed->SubmittedDate;
				$new_feed->account_id              = $account->id;
				// $new_feed->results                 = maybe_serialize( $feed );

				// save new record
				$new_feed->add();
			}

			if ( ! $new_feed->results ) {
				$new_feed->loadSubmissionResult();
				$new_feed->processSubmissionResult();				
			}

			// check if feed is in progress
			if ( in_array( $feed->FeedProcessingStatus, array('_SUBMITTED_','_IN_PROGRESS_') ) ) {
				$feeds_in_progress++;
			}			

		}

		// // update feed progress status
		// update_option( 'wpla_feeds_in_progress', $feeds_in_progress );

		return $feeds_in_progress;
	} // static processFeedsSubmissionList()


	static function updatePendingFeeds() {
		WPLA()->logger->info('updatePendingFeeds()');

		$accounts = WPLA_AmazonAccount::getAll();
		// WPLA()->logger->info('found accounts: '.print_r($accounts,1));

		foreach ($accounts as $account ) {
			self::updatePendingFeedForAccount( $account );
		}

	} // updatePendingFeeds()


	static function updatePendingFeedForAccount( $account ) {
		WPLA()->logger->info('updatePendingFeedForAccount('.$account->id.') - '.$account->title);
		WPLA()->logger->info('------------------------------');
		$lm = new WPLA_ListingsModel();

		// build feed(s) for updated (changed,prepared,matched) products
		WPLA()->logger->start('getGroupedPendingProductsForAccount');
		$grouped_items = $lm->getPendingProductsForAccount_GroupedByTemplateType( $account->id );
	   	WPLA()->logger->logTime('getGroupedPendingProductsForAccount');
		WPLA()->logger->info('found '.sizeof($grouped_items).' different templates to process...');
		// WPLA()->logger->info('grouped items: '.print_r($grouped_items,1));
		// echo "<pre>";print_r($grouped_items);echo"</pre>";#die();

		// each template
		$processed_tpl_types = array();
		foreach ( $grouped_items as $tpl_id => $grouped_inner_items ) {

			// get template
			$template      = $tpl_id ? new WPLA_AmazonFeedTemplate( $tpl_id ) : false;
			$template_type = $template ? $template->name : 'Offer';

			// each profile
			foreach ( $grouped_inner_items as $profile_id => $items ) {

				WPLA()->logger->info('building listing items feed for profile_id: '.$profile_id);
				WPLA()->logger->info('TemplateType: '.$template_type.' - tpl_id: '.$tpl_id);
				WPLA()->logger->info('number of items: '.sizeof($items));

				// get profile
				$profile  = new WPLA_AmazonProfile( $profile_id );

				// append if a feed with the same template type has been generated just now
				$append_feed = in_array( $template_type, $processed_tpl_types );

				// build Listing Data or ListingLoader feed
				WPLA()->logger->start('buildFeed');
				$success = WPLA_AmazonFeed::buildFeed( '_POST_FLAT_FILE_LISTINGS_DATA_', $items, $account, $profile, $append_feed );
			   	WPLA()->logger->logTime('buildFeed');
				
				// if a feed was created, add template type to list of processed templates
				if ( $success ) $processed_tpl_types[] = $template_type;				

			}

			// WPLA()->logger->logSpentTime('parseProductColumn');
			// WPLA()->logger->logSpentTime('parseProfileShortcode');
			// WPLA()->logger->logSpentTime('parseVariationAttributeColumn');
			// WPLA()->logger->logSpentTime('processAttributeShortcodes');
			// WPLA()->logger->logSpentTime('processCustomMetaShortcodes');

		} // foreach $grouped_items


		// build Price and Quantity feed for this account
		$items = $lm->getAllProductsForAccountByPnqStatus( $account->id, 1 );
		WPLA()->logger->info('number of PNQ items: '.sizeof($items));
		WPLA_AmazonFeed::buildFeed( '_POST_FLAT_FILE_PRICEANDQUANTITYONLY_UPDATE_DATA_', $items, $account );


		// build delete products feed for this account
		$items = $lm->getAllProductsInTrashForAccount( $account->id );
		WPLA()->logger->info('listings in trash: '.sizeof($items));
		WPLA_AmazonFeed::buildFeed( '_POST_FLAT_FILE_INVLOADER_DATA_', $items, $account );


	} // updatePendingFeedForAccount()


	// build feed for updated products
	static function buildFeed( $feed_type, $items, $account, $profile = false, $append_feed = false ) {
		WPLA()->logger->info('buildFeed() '.$feed_type.' - account id: '.$account->id);
		WPLA()->logger->info('items count: '.sizeof($items));
		// WPLA()->logger->info('items: '.print_r($items,1));

        // run 3rd-party code prior to building feeds (added for #17160)
        do_action( 'wpla_build_feed', $feed_type, $items, $account );

		// limit feed size to prevent timeout
		$max_feed_size = get_option( 'wpla_max_feed_size', 1000 );
		if ( sizeof($items) > $max_feed_size ) {
			$items = array_slice( $items, 0, $max_feed_size );
		}
	
		// generate CSV data
		switch ( $feed_type ) {
			case '_POST_FLAT_FILE_PRICEANDQUANTITYONLY_UPDATE_DATA_':
				# price and quantity feed
				WPLA()->logger->info('building price and quantity feed...');			
				WPLA()->logger->start('buildPriceAndQuantityFeedData');
				$csv_object = WPLA_FeedDataBuilder::buildPriceAndQuantityFeedData( $items, $account->id );
			   	WPLA()->logger->logTime('buildPriceAndQuantityFeedData');
				break;
			
			case '_POST_FLAT_FILE_LISTINGS_DATA_':
				# new products feed
				WPLA()->logger->info('building new products feed...');			
				WPLA()->logger->start('buildNewProductsFeedData');
				$csv_object = WPLA_FeedDataBuilder::buildNewProductsFeedData( $items, $account->id, $profile, $append_feed );
			   	WPLA()->logger->logTime('buildNewProductsFeedData');
				break;
			
			case '_POST_FLAT_FILE_INVLOADER_DATA_':
				# delete products feed (Inventory Loader)
				WPLA()->logger->info('building delete products feed...');			
				WPLA()->logger->start('buildInventoryLoaderFeedData');
				$csv_object = WPLA_FeedDataBuilder::buildInventoryLoaderFeedData( $items, $account->id, $profile );
			   	WPLA()->logger->logTime('buildInventoryLoaderFeedData');
				break;
			
			default:
				# default
				WPLA()->logger->error('unsupported feed type '.$feed_type);
				$csv_object = false;
				break;
		}

		if ( ! $csv_object || empty( $csv_object->data ) ) {
			WPLA()->logger->warn('no feed data - not creating feed');
			return false;
		}
		// WPLA()->logger->info('CSV: '.$csv_object->data);

		// // extract TemplateType from listing data feed
		// $template_name = '';
		// if ( preg_match('/TemplateType=(.*)\t/U', $csv_object->data, $matches) ) {
		// 	$template_name = $matches[1];
		// 	WPLA()->logger->info('TemplateType: '.$template_name);
		// }

		// get template name / type from CSV object
		$template_name = '';
		if ( '_POST_FLAT_FILE_LISTINGS_DATA_' == $feed_type ) {
			$template_name = $csv_object->template_type;
			WPLA()->logger->info('TemplateType: '.$template_name);
		}
		if ( '_POST_FLAT_FILE_INVLOADER_DATA_' == $feed_type ) {
			$template_name = 'Product Removal';
		}

		// set feed properties (required since $this is recycled here...)
		$new_feed = new WPLA_AmazonFeed();
		$new_feed->data                 = $csv_object->data;
		// $new_feed->line_count           = sizeof( $items );
		$new_feed->line_count           = $csv_object->line_count;
		$new_feed->FeedType             = $feed_type;
		$new_feed->template_name        = $template_name;
		$new_feed->FeedProcessingStatus = 'pending';
		$new_feed->status               = 'pending';
		$new_feed->account_id           = $account->id;
		$new_feed->date_created         = gmdate('Y-m-d H:i:s');

		// check if a pending feed of this type already exists
		$existing_feed_id = self::getPendingFeedId( $feed_type, $template_name, $account->id );
		// echo "<pre>template name: ";print_r($template_name);echo"</pre>";
		// echo "<pre>existing feed: ";print_r($existing_feed_id);echo"</pre>";

		if ( $existing_feed_id && $append_feed ) {

			// update existing feed (append)
			$existing_feed           = self::getFeed( $existing_feed_id );
			$new_feed->data          = $existing_feed->data ."\n" . $csv_object->data;
			$new_feed->id            = $existing_feed_id;
			$new_feed->template_name = $existing_feed->template_name;
			$new_feed->line_count   += $existing_feed->line_count;
			$new_feed->update();
			WPLA()->logger->info('appended content to existing feed '.$new_feed->id);			

		} elseif ( $existing_feed_id && ! $append_feed ) {

			// update existing feed (replace)
			$new_feed->id = $existing_feed_id;
			$new_feed->update();
			WPLA()->logger->info('updated existing feed '.$new_feed->id);			

		} else {

			// add new feed
			$new_feed->id = null;
			$new_feed->add();
			WPLA()->logger->info('added NEW feed - id '.$new_feed->id);

		}

		WPLA()->logger->info('feed was built - '.$new_feed->id);	
		WPLA()->logger->info('------');

		return true;
	} // buildFeed()



	// build feed for shipped orders - $post_id refers to the internal WooCommerce order ID
	function updateShipmentFeed( $post_id ) {

		$feed_type = '_POST_FLAT_FILE_FULFILLMENT_DATA_';
		$order_id  = get_post_meta( $post_id, '_wpla_amazon_order_id', true );

		$om        = new WPLA_OrdersModel();
		$order     = $om->getOrderByOrderID( $order_id );

		$account   = new WPLA_AmazonAccount( $order->account_id );
		// echo "<pre>";print_r($account);echo"</pre>";die();

		WPLA()->logger->info('updateShipmentFeed() '.$feed_type.' - order id: '.$order_id);
		WPLA()->logger->info('updateShipmentFeed() - post id: '.$post_id.' - account id: '.$account->id);
	
		// create pending feed if it doesn't exist
		if ( ! $this->id = self::getPendingFeedId( $feed_type, null, $account->id ) ) {

			# build feed data
			WPLA()->logger->info('building shipment data feed...');			
			$csv = WPLA_FeedDataBuilder::buildShippingFeedData( $post_id, $order_id, $account->id, true );

			if ( ! $csv ) {
				WPLA()->logger->warn('no feed data - not creating feed');
				return;
			}

			// add new feed
			$this->FeedType      = $feed_type;
			$this->status        = 'pending';
			$this->account_id    = $account->id;
			$this->date_created  = gmdate('Y-m-d H:i:s');
			$this->data          = $csv;
			$this->add();
			WPLA()->logger->info('added NEW feed - id '.$this->id);

		} else {
			WPLA()->logger->info('found existing feed '.$this->id);			
			$existing_feed = new WPLA_AmazonFeed( $this->id );

			# append feed data
			WPLA()->logger->info('updating shipment data feed...');			
			$csv = WPLA_FeedDataBuilder::buildShippingFeedData( $post_id, $order_id, $account->id, false );
			$this->data          = $existing_feed->data . $csv;

		}

		// update feed
		$this->line_count           = sizeof( $csv );
		$this->FeedProcessingStatus = 'pending';
		$this->date_created         = gmdate('Y-m-d H:i:s');
		$this->update();
		WPLA()->logger->info('feed was built and updated - '.$this->id);			

		// add history record
		$shipping_date   = get_post_meta( $post_id, '_wpla_date_shipped', true );
		$history_message = "Added to Order Fulfillment feed - shipment date: $shipping_date";
		$history_details = array( 'post_id' => $post_id, 'shipping_date' => $shipping_date, 'feed_id' => $this->id, 'user_id' => get_current_user_id() );
		WPLA_OrdersImporter::addHistory( $order_id, 'marked_as_shipped', $history_message, $history_details );

	} // updateShipmentFeed()




	// build feed for shipping WooCommerce orders via FBA - $order_post_id refers to the internal WooCommerce order ID
	function updateFbaSubmissionFeed( $order_post_id ) {

		// get order and items
		$_order      = wc_get_order( $order_post_id );
		$order_items = $_order->get_items();
		WPLA()->logger->info('updateFbaSubmissionFeed() - no. of items: '.count($order_items) );

		foreach ( $order_items as $order_item ) {
			$this->processFbaSubmissionOrderItem( $order_item, $_order );
		}

	} // updateFbaSubmissionFeed()

	function processFbaSubmissionOrderItem( $order_item, $_order ) {

		// Flat File FBA Shipment Injection Fulfillment Feed
		$feed_type = '_POST_FLAT_FILE_FULFILLMENT_ORDER_REQUEST_DATA_'; 

		// use account from first order item (for now)
		$lm = new WPLA_ListingsModel();
		$post_id    = $order_item['variation_id'] ? $order_item['variation_id'] : $order_item['product_id'];
		$listing    = $lm->getItemByPostID( $post_id );
		$account_id = $listing->account_id;
		$account    = new WPLA_AmazonAccount( $account_id );

		WPLA()->logger->info('updateFbaSubmissionFeed() '.$feed_type.' - post id: '.$post_id.' - account id: '.$account->id);

		// create pending feed if it doesn't exist
		if ( ! $this->id = self::getPendingFeedId( $feed_type, null, $account->id ) ) {

			# build feed data
			WPLA()->logger->info('building FBA submission feed...');			
			$csv = WPLA_FeedDataBuilder::buildFbaSubmissionFeedData( $post_id, $_order, $order_item, $listing, $account->id, true );

			if ( ! $csv ) {
				WPLA()->logger->warn('no feed data - not creating feed');
				return;
			}

			// add new feed
			$this->FeedType      = $feed_type;
			$this->status        = 'pending';
			$this->account_id    = $account->id;
			$this->date_created  = gmdate('Y-m-d H:i:s');
			$this->data          = $csv;
			$this->add();
			WPLA()->logger->info('added NEW feed - id '.$this->id);

		} else {
			WPLA()->logger->info('found existing feed '.$this->id);			
			$existing_feed = new WPLA_AmazonFeed( $this->id );

			# append feed data
			WPLA()->logger->info('updating FBA submission feed...');			
			$csv = WPLA_FeedDataBuilder::buildFbaSubmissionFeedData( $post_id, $_order, $order_item, $listing, $account->id, false );
			$this->data          = $existing_feed->data . $csv;

		}

		// update feed
		$this->line_count           = sizeof( $csv );
		$this->FeedProcessingStatus = 'pending';
		$this->date_created         = gmdate('Y-m-d H:i:s');
		$this->update();
		WPLA()->logger->info('feed was built and updated - '.$this->id);			

	} // processFbaSubmissionOrderItem()



	// add feed
	function add() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$data = array();
		foreach ( $this->fieldnames as $key ) {
			if ( isset( $this->$key ) ) {
				$data[ $key ] = $this->$key;
			} 
		}

		if ( sizeof( $data ) > 0 ) {
			$result = $wpdb->insert( $table, $data );
			echo $wpdb->last_error;

			$this->id = $wpdb->insert_id;
			return $this->id;		
		}

	}

	// update feed
	function update() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$data = array();
		foreach ( $this->fieldnames as $key ) {
			if ( isset( $this->$key ) ) {
				$data[ $key ] = $this->$key;
			} 
		}

		if ( sizeof( $data ) > 0 ) {
			$result = $wpdb->update( $table, $data, array( 'id' => $this->id ) );
			echo $wpdb->last_error;
			// echo "<pre>";print_r($wpdb->last_query);echo"</pre>";#die();

			// return $wpdb->insert_id;		
		}

	}


	function delete() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		if ( ! $this->id ) return;

		$wpdb->delete( $table, array( 'id' => $this->id ), array( '%d' ) );
		echo $wpdb->last_error;
	}


	function getRecordTypeName( $type ) {
		if ( isset( $this->types[$type] ) ) {
			return $this->types[$type];
		}
		return $type;
	}



	function getPageItems( $current_page, $per_page ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$orderby  = (!empty($_REQUEST['orderby'])) ? esc_sql( $_REQUEST['orderby'] ) : 'date_created DESC, SubmittedDate'; //If no sort, default to title
		$order    = (!empty($_REQUEST['order']))   ? esc_sql( $_REQUEST['order']   ) : 'desc'; //If no order, default to asc
		$offset   = ( $current_page - 1 ) * $per_page;
		$per_page = esc_sql( $per_page );

        // handle filters
        $where_sql = ' WHERE 1 = 1 ';

        // views
        if ( isset( $_REQUEST['feed_status'] ) ) {
            $status = esc_sql( $_REQUEST['feed_status'] );
            // if ( in_array( $status, array('Success','Error','pending','unknown') ) ) {
            if ( $status ) {
                if ( $status == 'unknown' ) {
                    $where_sql .= " AND status IS NULL ";
                } else {
                    $where_sql .= " AND status = '$status' ";
                }
            }
        }

        // filter account_id
		$account_id = ( isset($_REQUEST['account_id']) ? esc_sql( $_REQUEST['account_id'] ) : false);
		if ( $account_id ) {
			$where_sql .= "
				 AND account_id = '".$account_id."'
			";
		} 

        // search box
        if ( isset( $_REQUEST['s'] ) ) {
            $query = esc_sql( $_REQUEST['s'] );
            $where_sql .= " AND ( 
                                    ( FeedSubmissionId = '$query' ) OR 
                                    ( FeedType = '$query' ) OR
                                    ( data LIKE '%$query%' ) OR
                                    ( results LIKE '%$query%' ) OR
                                    ( FeedProcessingStatus LIKE '%$query%' ) OR
                                    ( success LIKE '%$query%' ) 
                                )
                            /* AND NOT amazon_id = 0 */
                            ";
        }

        // get items
		$items = $wpdb->get_results("
			SELECT *
			FROM $table
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
				FROM $table
	            $where_sql
				ORDER BY $orderby $order
			");			
		}

		foreach( $items as &$pfeed ) {
			$pfeed['FeedTypeName'] = $this->getRecordTypeName( $pfeed['FeedType'] );
		}

		return $items;
	} // getPageItems()

	static function getStatusSummary() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$result = $wpdb->get_results("
			SELECT status, count(*) as total
			FROM $table
			GROUP BY status
		");

		$summary = new stdClass();
		foreach ($result as $row) {
            $status = $row->status ? $row->status : 'unknown';
			$summary->$status = $row->total;
		}

		// count total items as well
		$total_items = $wpdb->get_var("
			SELECT COUNT( id ) AS total_items
			FROM $table
		");
		$summary->total_items = $total_items;

		return $summary;
	} // getStatusSummary()


} // WPLA_AmazonFeed()


