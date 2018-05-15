<?php
/**
 * WPLA_OrdersModel class
 *
 * responsible for managing orders and talking to amazon
 * 
 */

class WPLA_OrdersModel extends WPLA_Model {

	const TABLENAME = 'amazon_orders';

	var $_session;
	var $_cs;

	var $count_total    = 0;
	var $count_skipped  = 0;
	var $count_updated  = 0;
	var $count_inserted = 0;
	var $count_failed   = 0;
	var $report         = array();
	var $ModTimeTo      = false;
	var $ModTimeFrom    = false;
	var $NumberOfDays   = false;

	var $total_items;
	var $total_pages;
	var $current_page;
	var $current_lastdate;

	public function __construct() {
		global $wpdb;
		$this->tablename = $wpdb->prefix . self::TABLENAME;
	}


	/* the following methods could go into another class, since they use wpdb */

	function getAll() {
		global $wpdb;
		$profiles = $wpdb->get_results( "
			SELECT *
			FROM $this->tablename
			ORDER BY id DESC
		", ARRAY_A );

		return $profiles;
	}

	function getItem( $id ) {
		global $wpdb;

		$item = $wpdb->get_row( $wpdb->prepare("
			SELECT *
			FROM $this->tablename
			WHERE id = %d
		", $id ), ARRAY_A );

		// decode OrderType object with eBay classes loaded
		$item['details'] = $this->decodeObject( $item['details'], false, true );
		$item['history'] = maybe_unserialize( $item['history'] );
		$item['items']   = maybe_unserialize( $item['items'] );

		return $item;
	}

	static function getWhere( $column, $value ) {
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

	function getOrderByOrderID( $order_id ) {
		global $wpdb;

		$order = $wpdb->get_row( $wpdb->prepare("
			SELECT *
			FROM $this->tablename
			WHERE order_id = %s
		", $order_id ), OBJECT );

		return $order;
	}

	function getDateOfLastOrder( $account_id ) {
		global $wpdb;
		$lastdate = $wpdb->get_var( $wpdb->prepare("
			SELECT LastTimeModified
			FROM $this->tablename
			WHERE account_id = %s
			ORDER BY LastTimeModified DESC LIMIT 1
		", $account_id ) );
		return $lastdate;
	}

	function deleteItem( $id ) {
		global $wpdb;

		$wpdb->delete( $this->tablename, array( 'id' => $id ) );
		echo $wpdb->last_error;
	}

	function updateWpOrderID( $id, $wp_order_id ) {
		global $wpdb;
		$data = array(
			'post_id' => $wp_order_id
		);
		$wpdb->update( $this->tablename, $data, array( 'id' => $id ) );
	}

	public function updateOrder( $id, $data ) {
		global $wpdb;
		$wpdb->update( $this->tablename, $data, array( 'id' => $id ) );
	}

	public function updateWhere( $where, $data ) {
		global $wpdb;
		$wpdb->update( $this->tablename, $data, $where );
	}

	static function getWooOrderIdByMerchantFulfillmentOrderID( $MerchantFulfillmentOrderID ) {
		global $wpdb;	
		$table = $wpdb->prefix . 'postmeta';

		$post_id = $wpdb->get_var( $wpdb->prepare("
			SELECT post_id
			FROM $table
			WHERE meta_key   = '_wpla_fba_MerchantFulfillmentOrderID'
			  AND meta_value = %s
		", $MerchantFulfillmentOrderID 
		));

		return $post_id;
	}

	public function updateFromAmazon( $id ) {

		// get order
		$order = $this->getItem( $id );
		if ( ! $order ) return false;

		// get account
		$account = new WPLA_AmazonAccount( $order['account_id'] );
		if ( ! $account ) return false;

		// init API
		$this->api = new WPLA_AmazonAPI( $account->id );
		$importer  = new WPLA_OrdersImporter();

		// update order details
		// echo "<pre>fetching details for ";print_r($order['order_id']);echo"</pre>";
		$orders = $this->api->getOrder( $order['order_id'] );
		if ( is_array($orders) && ! empty($orders) ) {
			$importer->importOrder( $orders[0], $account ); // import will update existing order automatically - but not order line items
		} elseif ( is_object($orders) && ! empty($orders->Error->Code) && ( $orders->Error->Code == 'RequestThrottled' ) ) {
			$this->lastOrderID = $order['order_id'];
			return 'RequestThrottled';
		} else {
			// TODO: use showMessage()
			echo "There was a problem fetching order details for order {$order['order_id']} from Amazon.";
			echo "<pre>";print_r($orders);echo"</pre>";#die();			
		}

		// update order line items
		// echo "<pre>fetching items for ";print_r($order['order_id']);echo"</pre>";
		$this->api = new WPLA_AmazonAPI( $account->id ); // init API again to allow to log the second request as well
		$items = $this->api->getOrderLineItems( $order['order_id'] );
		if ( is_array($items) && ! empty($items) ) {
			$importer->importOrderItems( $items, $order['order_id'] );
		} elseif ( is_object($items) && ! empty($items->Error->Code) && ( $items->Error->Code == 'RequestThrottled' ) ) {
			$this->lastOrderID = $order['order_id'];
			return 'RequestThrottled';
		} else {
			// TODO: use showMessage()
			echo "There was a problems fetching order line items for order {$order['order_id']} from Amazon.";
			echo "<pre>";print_r($items);echo"</pre>";#die();			
		}
		// echo "<pre>";print_r($items);echo"</pre>";#die();

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
		foreach ($result as $row) {
			$status = $row->status;
			$summary->$status = $row->total;
		}

		// count total items as well
		$total_items = $wpdb->get_var("
			SELECT COUNT( id ) AS total_items
			FROM $table
		");
		$summary->total_items = $total_items;

		// count orders which do (not) exist in WooCommerce
		$total_items = $wpdb->get_var("
			SELECT COUNT( o.id ) AS total_items
			FROM $table o
			LEFT JOIN {$wpdb->prefix}posts p ON o.post_id = p.ID 
			WHERE p.ID IS NOT NULL
		");
		$summary->has_wc_order    = $total_items;
		$summary->has_no_wc_order = $summary->total_items - $total_items;

		return $summary;
	}

	function getPageItems( $current_page, $per_page ) {
		global $wpdb;

		$orderby  = (!empty($_REQUEST['orderby'])) ? esc_sql( $_REQUEST['orderby'] ) : 'date_created';	//If no sort, default to title
		$order    = (!empty($_REQUEST['order']))   ? esc_sql( $_REQUEST['order']   ) : 'desc'; 			//If no order, default to asc
		$offset   = ( $current_page - 1 ) * $per_page;
		$per_page = esc_sql( $per_page );

        $join_sql  = '';
        $where_sql = 'WHERE 1 = 1 ';

        // filter order_status
		$order_status = isset($_REQUEST['order_status']) ? esc_sql( $_REQUEST['order_status'] ) : 'all';
		if ( $order_status && $order_status != 'all' ) {
			$where_sql .= "AND o.status = '".$order_status."' ";
		} 

        // filter has_wc_order
		$has_wc_order = isset($_REQUEST['has_wc_order']) ? esc_sql( $_REQUEST['has_wc_order'] ) : '';
		if ( $has_wc_order ) {
			// $where_sql .= $has_wc_order == 'yes' ? "AND o.post_id IS NOT NULL " : "AND o.post_id IS NULL ";
			$join_sql  .= "LEFT JOIN {$wpdb->prefix}posts p ON o.post_id = p.ID ";
			$where_sql .= $has_wc_order == 'yes' ? "AND p.ID IS NOT NULL " : "AND p.ID IS NULL ";
		} 

        // filter account_id
		$account_id = isset($_REQUEST['account_id']) ? esc_sql( $_REQUEST['account_id'] ) : false;
		if ( $account_id ) {
			$where_sql .= "
				 AND o.account_id = '".$account_id."'
			";
		} 

        // filter search_query
		$search_query = isset($_REQUEST['s']) ? esc_sql( $_REQUEST['s'] ) : false;
		if ( $search_query ) {
			$where_sql .= "
				AND  ( o.buyer_name   LIKE '%".$search_query."%'
					OR o.items        LIKE '%".$search_query."%'
					OR o.buyer_userid     = '".$search_query."'
					OR o.buyer_email      = '".$search_query."'
					OR o.order_id         = '".$search_query."'
					OR o.post_id          = '".$search_query."'
					OR o.ShippingAddress_City LIKE '%".$search_query."%' )
			";
		} 


        // get items
		$items = $wpdb->get_results("
			SELECT *
			FROM $this->tablename o
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
				FROM $this->tablename o
	            $join_sql 
    	        $where_sql
				ORDER BY $orderby $order
			");			
		}

		return $items;
	} // getPageItems()

} // class WPLA_OrdersModel
