<?php
/**
 * WPLA_ListingQueryHelper class
 *
 * provides static methods to query the amazon_listings table
 * 
 */

class WPLA_ListingQueryHelper {

    const TABLENAME = 'amazon_listings';



    // get all items eligible for having their price matched to the lowest price (up or down)
    // items need to have a min_price, max_price and a lowest price set
    static function getItemsWithMinMaxAndLowestPrice() {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLENAME;
        // $repricing_margin    = floatval( get_option('wpla_repricing_margin') );

        $items = $wpdb->get_results("
            SELECT id, post_id, price, min_price, max_price, lowest_price, compet_price, buybox_price, loffer_price, has_buybox, sku
            FROM $table
            WHERE min_price > 0
              AND max_price > 0
              AND lowest_price IS NOT NULL
              AND lowest_price > 0
        ");
        // AND price > ( lowest_price - $repricing_margin )

        return $items;
    }

    // get all items with min/max prices but without lowest price - where price is lower than max_price
    static function getItemsWithoutLowestPriceButPriceLowerThanMaxPrice() {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLENAME;

        $items = $wpdb->get_results("
            SELECT id, post_id, price, min_price, max_price, lowest_price, compet_price, buybox_price, loffer_price, has_buybox, sku
            FROM $table
            WHERE min_price > 0
              AND max_price > 0
              AND ( lowest_price = 0  OR  lowest_price IS NULL )
              AND price < max_price
        ");

        return $items;
    }






    // get all items due for a pricing update - by account_id
    // called by WPLA_CronActions::action_update_pricing_info()
    static function getItemsDueForPricingUpdateForAcccount( $account_id, $limit = 20 ) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLENAME;

        // check whether we should return out of stock items
        $process_oos = get_option( 'wpla_pricing_info_process_oos_items', 1 );
        $oos_sql     = $process_oos ? '' : "AND ( quantity > 0 OR fba_quantity > 0 )";

        // check expiry time - return empty array if updates are off
        $hours       = get_option( 'wpla_pricing_info_expiry_time', 24 );
        if ( ! $hours || ! is_numeric($hours) ) return array();

        $n_hours_ago = gmdate('Y-m-d H:i:s', time() - 3600 * $hours );
        $items = $wpdb->get_results( $wpdb->prepare("
            SELECT *
            FROM $table
            WHERE       account_id = %d
              AND           status = 'online'
              AND             asin IS NOT NULL
              AND ( product_type <> 'variable' OR product_type IS NULL )
              AND ( pricing_date  < %s         OR pricing_date IS NULL )
              $oos_sql
            ORDER BY pricing_date ASC
            LIMIT %d
        ", 
        $account_id,
        $n_hours_ago,
        $limit
        ), OBJECT_K);

            // doesn't work if PHP and MySQL use different time zones...
            //AND pricing_date < DATE_SUB( NOW(), INTERVAL 1 HOUR )

        return $items;
    }


    // find items which are linked to a product which does not exist in WooCommerce
    static function findMissingProducts() {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLENAME;

        $items = $wpdb->get_results("
            SELECT al.id, al.post_id, al.listing_title, al.sku, al.asin, al.price, al.quantity 
            FROM $table al
            LEFT JOIN {$wpdb->posts} p ON al.post_id = p.ID
            WHERE p.ID IS NULL
            ORDER BY id DESC
        ", OBJECT_K);
        // echo "<pre>";print_r($items);echo"</pre>";#die();

        return $items;
    } // findMissingProducts()




    // get all published items
    // called only by WPLA_InventoryCheck::checkProductInventory() for now
    static function getAllPublished( $limit = null, $offset = null ) {
        global $wpdb;   
        $table = $wpdb->prefix . self::TABLENAME;

        $limit  = intval($limit); 
        $offset = intval($offset);
        $limit_sql = $limit ? " LIMIT $limit OFFSET $offset" : '';

        $items = $wpdb->get_results("
            SELECT * 
            FROM $table
            WHERE status = 'online'
               OR status = 'changed'
            ORDER BY id DESC
            $limit_sql
        ", ARRAY_A);        

        return $items;      
    }

    // get all sold items
    // called only by WPLA_InventoryCheck::checkProductInventory() for now
    static function getAllSold( $limit = null, $offset = null ) {
        global $wpdb;   
        $table = $wpdb->prefix . self::TABLENAME;

        $limit  = intval($limit); 
        $offset = intval($offset);
        $limit_sql = $limit ? " LIMIT $limit OFFSET $offset" : '';

        $items = $wpdb->get_results("
            SELECT * 
            FROM $table
            WHERE status = 'sold'
            ORDER BY id DESC
            $limit_sql
        ", ARRAY_A);        

        return $items;      
    }

    /**
     * Return the number of products that are presently listed on Amazon (online, changed)
     * @return int
     */
    static function countProductsOnAmazon() {
        global $wpdb;

        return $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->posts}
            WHERE {$wpdb->posts}.post_type = 'product' 
            AND (
              {$wpdb->posts}.post_status = 'publish' 
              OR {$wpdb->posts}.post_status = 'future' 
              OR {$wpdb->posts}.post_status = 'draft' 
              OR {$wpdb->posts}.post_status = 'pending' 
              OR {$wpdb->posts}.post_status = 'private'
              )  
            AND {$wpdb->posts}.ID IN (
                SELECT {$wpdb->prefix}amazon_listings.post_id
                FROM {$wpdb->prefix}amazon_listings
                WHERE {$wpdb->prefix}amazon_listings.status IN ('online', 'changed')
            )
            OR {$wpdb->posts}.ID IN (
                SELECT {$wpdb->prefix}amazon_listings.parent_id
                FROM {$wpdb->prefix}amazon_listings
                WHERE {$wpdb->prefix}amazon_listings.status IN ('online', 'changed')
                AND {$wpdb->prefix}amazon_listings.parent_id IS NOT NULL
            )
        ");
    }

    /**
     * Return the number of products that are not yet listed on Amazon
     * @return int
     */
    static function countProductsNotOnAmazon() {
        global $wpdb;

        return $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->posts}
            WHERE {$wpdb->posts}.post_type = 'product' 
            AND (
              {$wpdb->posts}.post_status = 'publish' 
              OR {$wpdb->posts}.post_status = 'future' 
              OR {$wpdb->posts}.post_status = 'draft' 
              OR {$wpdb->posts}.post_status = 'pending' 
              OR {$wpdb->posts}.post_status = 'private'
              ) 
            AND {$wpdb->posts}.ID NOT IN (
                SELECT {$wpdb->prefix}amazon_listings.post_id
                FROM {$wpdb->prefix}amazon_listings
                WHERE {$wpdb->prefix}amazon_listings.status IN ('online', 'changed')
            )
            AND {$wpdb->posts}.ID NOT IN (
                SELECT {$wpdb->prefix}amazon_listings.parent_id
                FROM {$wpdb->prefix}amazon_listings
                WHERE {$wpdb->prefix}amazon_listings.status IN ('online', 'changed')
                AND {$wpdb->prefix}amazon_listings.parent_id IS NOT NULL
            )
        ");
    }


} // class WPLA_ListingQueryHelper
