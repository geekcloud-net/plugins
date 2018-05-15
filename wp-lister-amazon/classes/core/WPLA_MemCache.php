<?php

class WPLA_MemCache {

	// in memory caches
	private $product_attributes = array();
    private $product_variations = array();
    private $product_varparents = array();
    private $feed_row_id        = null;
    private $feed_row_data      = array();
    private $account_data       = array();
	private $market_data        = array();


	// get product attributes
	public function getProductAttributes( $post_id ) {

        // update cache if required
        if ( ! array_key_exists( $post_id, $this->product_attributes ) ) {
            $this->product_attributes[ $post_id ] = WPLA_ProductWrapper::getAttributes( $post_id );
        }

        return $this->product_attributes[ $post_id ];
	}


    // get product variations
    public function getProductVariations( $post_id ) {

        // update cache if required
        if ( ! array_key_exists( $post_id, $this->product_variations ) ) {
            $this->product_variations[ $post_id ] = WPLA_ProductWrapper::getVariations( $post_id );
        }

        return $this->product_variations[ $post_id ];
    }


    // get product variation parents
    public function getProductVariationParent( $post_id ) {

        // update cache if required
        if ( ! array_key_exists( $post_id, $this->product_varparents ) ) {
            $this->product_varparents[ $post_id ] = WPLA_ProductWrapper::loadVariationParent( $post_id );
        }

        return $this->product_varparents[ $post_id ];
    }


    // get single account
    public function getAccount( $account_id ) {

        // update cache if required
        if ( ! array_key_exists( $account_id, $this->account_data ) ) {
            $this->account_data[ $account_id ] = WPLA_AmazonAccount::getAccount( $account_id );
        }

        return $this->account_data[ $account_id ];
    }


    // get single market
    public function getMarket( $market_id ) {

        // update cache if required
        if ( ! array_key_exists( $market_id, $this->market_data ) ) {
            $this->market_data[ $market_id ] = WPLA_AmazonMarket::getMarket( $market_id );
        }

        return $this->market_data[ $market_id ];
    }


    // store a single column value for the current row
    public function setColumnValue( $id, $key, $value ) {
        if ( $value === '' ) return;
        $this->feed_row_id = $id;
        $this->feed_row_data[ $key ] = $value;
    }

    // get a single column value for the current row
    public function getColumnValue( $id, $key ) {
        if ( $id != $this->feed_row_id ) return false;
        if ( ! isset( $this->feed_row_data[ $key ] ) ) return false;
        return $this->feed_row_data[ $key ];
    }

    // clear key value store
    public function clearColumnCache() {
        $this->feed_row_id   = false;
        $this->feed_row_data = array();
    }



} // class WPLA_MemCache
