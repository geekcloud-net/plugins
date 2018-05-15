<?php
/**
 * WPLA_AmazonAccount class
 *
 */

// class WPLA_AmazonAccount extends WPLA_NewModel {
class WPLA_AmazonAccount {

	const TABLENAME = 'amazon_accounts';

	var $id;
	var $title;
	var $market_id;
	var $market_code;

	function __construct( $id = null ) {
		
		$this->init();

		if ( $id ) {
			$this->id = $id;
			
			$account = $this->getAccount( $id );
			if ( ! $account ) return false;

			// load data into object		
			foreach( $account AS $key => $value ){
			    $this->$key = $value;
			}

			return $this;
		}

	}

	function init()	{

		$this->fieldnames = array(
			'title',
			'merchant_id',
			'marketplace_id',
			'access_key_id',
			'secret_key',
			'market_id',
			'market_code',
			'allowed_markets',
			'active',
			'is_reg_brand',
			'config',
			'sync_orders',
			'sync_products',
			'last_orders_sync'
		);

	}

	// get single account
	static function getAccount( $id )	{
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;
		
		$item = $wpdb->get_row( $wpdb->prepare("
			SELECT *
			FROM $table
			WHERE id = %d
		", $id
		), OBJECT);

		// $item->allowed_markets = maybe_unserialize( $item->allowed_markets );
		return $item;
	}

	// get all accounts
	static function getAll( $include_inactive = false ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$where_sql = $include_inactive ? '' : 'WHERE active = 1';
		$items = $wpdb->get_results("
			SELECT *
			FROM $table
			$where_sql
			ORDER BY title ASC
		", OBJECT_K);

		return $items;
	}

	// get account title
	static function getAccountTitle( $id )	{
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;
		
		$account_title = $wpdb->get_var( $wpdb->prepare("
			SELECT title
			FROM $table
			WHERE id = %d
		", $id ));
		return $account_title;
	}

	// get this account's market
	function getMarket()	{

		return WPLA()->memcache->getMarket( $this->market_id );

	}

	static function getAccountLocale( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLENAME;

        $market = $wpdb->get_var( $wpdb->prepare("
			SELECT market_code
			FROM $table
			WHERE id = %d
		", $id ));

        switch ( $market ) {
            case 'US':
            case 'CA':
            case 'UK':
                $lang = 'en';
                break;

            default:
                $lang = strtolower( $market );
                break;
        }

        return apply_filters( 'wpla_account_locale', $lang, $id );
    }

	// check if there are active accounts using the same MerchantID
	static function getDuplicateMerchantIDs() {
		global $wpdb;	
		$table = $wpdb->prefix . self::TABLENAME;

		$items = $wpdb->get_results("
			SELECT merchant_id, COUNT(*) c
			FROM $table
			WHERE active = 1
			GROUP BY merchant_id 
			HAVING c > 1
		");		

		return $items;		
	}


	// save account
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
			return $wpdb->insert_id;		
		}

	}

	// update feed
	function update() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;
		if ( ! $this->id ) return;

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

	function updateMarketplaceParticipations() {
		if ( ! $this->id ) return;

		$api = new WPLA_AmazonAPI( $this->id );

		$result = $api->listMarketplaceParticipations();

		if ( @$result->success ) {
			$this->allowed_markets = maybe_serialize( $result->allowed_markets );
			$this->update();
		} elseif ( $result->ErrorMessage ) {
			wpla_show_message( $result->ErrorMessage, 'error' );
		}

		return $result;
	}


	// delete account
	function delete() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;
		if ( ! $this->id ) return;

		$result = $wpdb->delete( $table, array( 'id' => $this->id ) );
		echo $wpdb->last_error;
	}

	function getPageItems( $current_page, $per_page ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$orderby  = (!empty($_REQUEST['orderby'])) ? esc_sql( $_REQUEST['orderby'] ) : 'title'; //If no sort, default to title
		$order    = (!empty($_REQUEST['order']))   ? esc_sql( $_REQUEST['order']   ) : 'asc'; //If no order, default to asc
		$offset   = ( $current_page - 1 ) * $per_page;
		$per_page = esc_sql( $per_page );

        // get items
		$items = $wpdb->get_results("
			SELECT *
			FROM $table
			ORDER BY active desc, $orderby $order
            LIMIT $offset, $per_page
		", ARRAY_A);

		// get total items count - if needed
		if ( ( $current_page == 1 ) && ( count( $items ) < $per_page ) ) {
			$this->total_items = count( $items );
		} else {
			$this->total_items = $wpdb->get_var("
				SELECT COUNT(*)
				FROM $table
				ORDER BY $orderby $order
			");			
		}

		return $items;
	}


} // WPLA_AmazonAccount()


