<?php
/**
 * WPLA_AmazonMarket class
 *
 */

// class WPLA_AmazonMarket extends WPLA_NewModel {
class WPLA_AmazonMarket {

	const TABLENAME = 'amazon_markets';

	function __construct( $id = null ) {
		
		$this->init();

		if ( $id ) {
			$this->id = $id;
			
			// load data into object
			$market = $this->getMarket( $id );
			foreach( $market AS $key => $value ){
			    $this->$key = $value;
			}

			return $this;
		}

	}

	function init()	{
	}

	// get single market
	static function getMarket( $id )	{
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

	// get single market by country code (US)
	static function getMarketByCountyCode( $code )	{
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;
		
		$item = $wpdb->get_row( $wpdb->prepare("
			SELECT *
			FROM $table
			WHERE code = %s
		", $code
		), OBJECT);

		return $item;
	}

	// get all markets
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

	// get market code
	static function getMarketCode( $id )	{
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;
		
		$item = $wpdb->get_var( $wpdb->prepare("
			SELECT code
			FROM $table
			WHERE id = %d
		", $id ));

		return $item;
	}

	// get url
	static function getUrl( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$domain = $wpdb->get_var( $wpdb->prepare("
			SELECT domain
			FROM $table
			WHERE id = %d
		", $id ));

		return $domain;
	}

	// get url
	function getSignInUrl() {

        $applicationName = 'WP-Lister for Amazon';
        $applicationName = 'TEST';

        // $url = 'https://sellercentral.' . $this->url.
        //         '/gp/mws/registration/register.html?ie=UTF8&*Version*=1&*entries*=0' .
        //         '&applicationName=' . rawurlencode( $applicationName) .
        //         '&appDevMWSAccountId=' . $this->developer_id;

        //$url = 'https://sellercentral.' . $this->url.
        //        '/gp/mws/registration/register.html?ie=UTF8&*Version*=1&*entries*=0';

        // Use the new User Permissions Page as the signin redirect URL
        $url = 'https://sellercentral.'. $this->url .'/gp/account-manager/home.html';

        return $url;

	}


} // WPLA_AmazonMarket()

