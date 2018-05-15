<?php
/**
 * WPLA_AmazonProfile class
 *
 */

// class WPLA_AmazonProfile extends WPLA_NewModel {
class WPLA_AmazonProfile {

	const TABLENAME = 'amazon_profiles';

	var $id;
	var $data;
	var $fieldnames;

	function __construct( $id = null ) {
		
		$this->init();

		if ( $id ) {

			$this->id = $id;
			
			// load data into object
			$profile = self::getProfile( $id );
			foreach( $profile AS $key => $value ){
			    $this->$key = $value;
			}

			$this->fields = maybe_unserialize( $this->fields );
			if ( empty( $this->fields ) )
				$this->initDefaultFields();

			return $this;

		} else {

			foreach( $this->fieldnames AS $key ){
			    $this->$key = null;
			}
			$this->initDefaultFields();

		}

	}

	function init()	{

		$this->fieldnames = array(
			'profile_id',
			'profile_name',
			'profile_description',
			'feed_type',
			'details',
			'fields',
			'tpl_id',
			'account_id'
		);

	}

	// set default fields for new profiles
	function initDefaultFields() {
		if ( ! empty( $this->fields ) ) return;

		$this->fields = array(
			// category feeds
			'external_product_id' => '[amazon_product_id]',
			'item_name'           => '[product_title]',
			'product_description' => '[product_content]',
			'standard_price'      => '[product_price]',
			'sale_price'          => '[product_sale_price]',
			'sale_from_date'      => '[product_sale_start]',
			'sale_end_date'       => '[product_sale_end]',
			'item_length'         => '[product_length]',
			'item_width'          => '[product_width]',
			'item_height'         => '[product_height]',
			'item_weight'         => '[product_weight]',
			// ListingLoader
			'product-id'          => '[amazon_product_id]',
			'title'               => '[product_title]',
			'price'      		  => '[product_price]',
			'sale-price'          => '[product_sale_price]',
			'sale-start-date'     => '[product_sale_start]',
			'sale-end-date'       => '[product_sale_end]',
		);

	}

	// get single profile
	static function getProfile( $id )	{
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;
		
		$item = $wpdb->get_row( $wpdb->prepare("
			SELECT *
			FROM $table
			WHERE profile_id = %d
		", $id
		), OBJECT);

		return $item;
	}

	// get all profiles
	static function getAll() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$items = $wpdb->get_results("
			SELECT *
			FROM $table
			ORDER BY profile_name ASC
		", OBJECT_K);

		return $items;
	}

	static function getAllNames() {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;

		$results = $wpdb->get_results("
			SELECT profile_id, profile_name 
			FROM $table
			ORDER BY profile_name ASC
		");		

		$profiles = array();
		foreach( $results as $result ) {
			$profiles[ $result->profile_id ] = $result->profile_name;
		}

		return $profiles;		
	}

	static function getAllTemplateNames() {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;

		$results = $wpdb->get_results("
			SELECT profile_id, tpl_id 
			FROM $table
		");		

		$templates = array();
		foreach( $results as $result ) {
			$template                         = WPLA_AmazonFeedTemplate::getFeedTemplate( $result->tpl_id );
			$templates[ $result->profile_id ] = $template ? $template->title : false;
		}

		return $templates;		
	}

	// count items using profile and status (optimized version of the above methods)
	static function countProfilesUsingTemplate( $tpl_id ) {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;

		$item_count = $wpdb->get_var( $wpdb->prepare("
			SELECT count(profile_id) 
			FROM $table
			WHERE tpl_id = %s
		", $tpl_id ));

		return $item_count;
	}

	static function duplicateProfile($id) {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;

		// get raw db content
		$data = $wpdb->get_row( $wpdb->prepare("
			SELECT * 
			FROM $table
			WHERE profile_id = %d
		", $id ), ARRAY_A);
				
		// adjust duplicate
		$data['profile_name'] = $data['profile_name'] .' ('. __('duplicated','wpla').')';
		unset( $data['profile_id'] );				

		// insert record				
		$wpdb->insert( $table, $data );

		return $wpdb->insert_id;		
	}

	// add profile
	function add() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;
		// echo "<pre>";print_r($this);echo"</pre>";die();

		$data = array();
		foreach ( $this->fieldnames as $key ) {
			if ( isset( $this->$key ) && ! is_null( $this->$key ) ) {
				$data[ $key ] = $this->$key;
			} 
		}

		if ( sizeof( $data ) > 0 ) {
			$result = $wpdb->insert( $table, $data );
			echo $wpdb->last_error;

			return $wpdb->insert_id;		
		}

	} // add()

	// update profile
	function update() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$data = array();
		foreach ( $this->fieldnames as $key ) {
			if ( isset( $this->$key ) && ! is_null( $this->$key ) ) {
				$data[ $key ] = $this->$key;
			} 
		}

		// check if MySQL server has gone away and reconnect if required - WP 3.9+
		if ( method_exists( $wpdb, 'check_connection') ) $wpdb->check_connection();
		

		if ( sizeof( $data ) > 0 ) {
			$result = $wpdb->update( $table, $data, array( 'profile_id' => $this->id ) );
			echo $wpdb->last_error;
		}

	} // update()



	// populate profile fields from data array
	function fillFromArray( $data ) {

		foreach ( $this->fieldnames as $key ) {
			if ( isset( $data[$key] ) ) {
				$this->$key = $data[ $key ];
			} 
		}

	} // fillFromArray()


	function delete() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		if ( ! $this->id ) return;

		$wpdb->delete( $table, array( 'profile_id' => $this->id ), array( '%d' ) );
		echo $wpdb->last_error;

	} // delete()



	function processProfilePrice( $price ) {
		if ( ! $this->id ) return $price;
		if ( ! $price ) return false;

		$details              = maybe_unserialize( $this->details );
		$price_add_percentage = isset( $details['price_add_percentage'] ) ? $details['price_add_percentage'] : false;
		$price_add_amount     = isset( $details['price_add_amount'] ) ? $details['price_add_amount'] : false;

		if ( $price_add_percentage ) {
			$price += $price * floatval( $price_add_percentage ) / 100;
		}

		if ( $price_add_amount ) {
			$price += floatval( $price_add_amount );
		}

		$price = number_format( $price, 2, null, '' );
		return $price;
	} // processProfilePrice()

	function reverseProfilePrice( $price ) {
		if ( ! $this->id ) return $price;
		if ( ! $price ) return false;

		$details              = maybe_unserialize( $this->details );
		$price_add_percentage = isset( $details['price_add_percentage'] ) ? $details['price_add_percentage'] : false;
		$price_add_amount     = isset( $details['price_add_amount'] ) ? $details['price_add_amount'] : false;

		if ( $price_add_amount ) {
			$price -= floatval( $price_add_amount );
		}

		if ( $price_add_percentage ) {
			// reversed: $newprice = $price + $price * $price_add_percentage / 100;
			$net_price = $price - $price / ( 1 + ( 1 / ( floatval($price_add_percentage) / 100 ) ) );	// calc net from gross amount
			$price = $net_price;
		}

		$price = round($price,2);
		return $price;
	} // reverseProfilePrice()



	function getPageItems( $current_page, $per_page ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$orderby  = (!empty($_REQUEST['orderby'])) ? esc_sql( $_REQUEST['orderby'] ) : 'profile_name'; //If no sort, default to title
		$order    = (!empty($_REQUEST['order']))   ? esc_sql( $_REQUEST['order']   ) : 'asc'; //If no order, default to asc
		$offset   = ( $current_page - 1 ) * $per_page;
		$per_page = esc_sql( $per_page );

        $join_sql  = '';
        $where_sql = 'WHERE 1 = 1 ';

        // filter search_query
		$search_query = isset($_REQUEST['s']) ? esc_sql( $_REQUEST['s'] ) : false;
		if ( $search_query ) {
			$where_sql .= "
				AND  ( profile_name        LIKE '%".$search_query."%'
					OR profile_description LIKE '%".$search_query."%' )
			";
		} 

        // get items
		$items = $wpdb->get_results("
			SELECT *
			FROM $table
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
				FROM $table
	            $join_sql 
    	        $where_sql
				ORDER BY $orderby $order
			");			
		}

		return $items;
	} // getPageItems()


} // WPLA_AmazonProfile()

