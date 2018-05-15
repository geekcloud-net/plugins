<?php

class WPLA_SkuGenerator {
	
    var $total_items;

    static function generateNewSKU( $post_id ) {

        $skugen_mode_simple    = get_option( 'wpla_skugen_mode_simple' );
        $skugen_mode_variation = get_option( 'wpla_skugen_mode_variation' );
        $skugen_mode_case      = get_option( 'wpla_skugen_mode_case' );

        // return existing SKU (?)
        // $old_sku = get_post_meta( $post_id, '_sku', true );
        // if ( $old_sku ) return $old_sku;

        // get wc product and title
        $product = wc_get_product( $post_id );
        $title   = empty($product->post) ? get_the_title( $post_id ) : $product->post->post_title;

        // handle simple / parent product
        if ( $skugen_mode_simple == 2 ) {

            // simple - mode 2
            // get first 2 letters of each word
            preg_match_all('/\b\w\w/', $title, $matches);
            $new_sku = join( $matches[0] );

        } else {

            // simple - mode 1
            // get first letter of each word
            preg_match_all('/\b\w/', $title, $matches);
            $new_sku = join( $matches[0] );

        }

        // handle variation
        if ( $product->is_type( 'variation' ) ) {

            // get parent product and SKU
            $parent_id  = WPLA_ProductWrapper::getVariationParent( $post_id );
            $parent_sku = get_post_meta( $parent_id, '_sku', true );
            if ( $parent_sku ) $new_sku = $parent_sku;


            if ( $skugen_mode_variation == 9 ) {
        
                // mode 9
                if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
                    $new_sku .= '-' . $product->variation_id;
                } else {
                    $new_sku .= '-' . $product->get_id();
                }

            } else {

                // get variation attribute values
                $attributes = $product->get_variation_attributes();
                // echo "<pre>";print_r($attributes);echo"</pre>";

                foreach ($attributes as $key => $value) {
               
                    $value = str_replace('-','',$value);
                    if ( $skugen_mode_variation == 1 ) $value = substr( $value, 0, 1);
                    if ( $skugen_mode_variation == 2 ) $value = substr( $value, 0, 2);
                    if ( $skugen_mode_variation == 3 ) $value = substr( $value, 0, 3);

                    $new_sku .= '-'.$value;
                }

            }

        } // is variation


        // handle case conversion
        if ( $skugen_mode_case == 1 ) $new_sku = strtoupper( $new_sku );
        if ( $skugen_mode_case == 2 ) $new_sku = strtolower( $new_sku );

        return $new_sku;
    }

    function getPageItems( $current_page, $per_page ) {
        global $wpdb;

        $orderby  = (!empty($_REQUEST['orderby'])) ? esc_sql( $_REQUEST['orderby'] ) : 'post_title';
        $order    = (!empty($_REQUEST['order']))   ? esc_sql( $_REQUEST['order']   ) : 'asc';
        $offset   = ( $current_page - 1 ) * $per_page;
        $per_page = esc_sql( $per_page );

        $where_sql = '';

        // filter sku_status
        $sku_status = isset($_REQUEST['sku_status']) ? esc_sql( $_REQUEST['sku_status'] ) : '';
        if ( $sku_status == 'missing_sku' ) {
            $where_sql = "AND ( pm.meta_value = '' OR pm.meta_value IS NULL ) ";
        } elseif ( $sku_status == 'long_sku' ) {
            $where_sql = "AND LENGTH(pm.meta_value) > 40 ";
        } 

        // filter search_query
        $search_query = isset($_REQUEST['s']) ? esc_sql( $_REQUEST['s'] ) : false;
        if ( $search_query ) {
            $where_sql .= "
                 AND ( al.listing_title LIKE '%".$search_query."%'
                    OR p.post_title     LIKE '%".$search_query."%'
                    OR pm.meta_value    LIKE '%".$search_query."%'
                    OR al.sku           LIKE '%".$search_query."%'
                    OR al.asin              = '".$search_query."'
                    OR al.status            = '".$search_query."'
                    OR al.post_id           = '".$search_query."'
                    OR al.parent_id         = '".$search_query."'
                     )
            ";
        } 

        // get items
        // $items = $wpdb->get_results("
        //     SELECT *
        //     FROM $this->tablename l
        //     $where_sql
        //     ORDER BY $orderby $order
        //     LIMIT $offset, $per_page
        // ", ARRAY_A);
        $items = $wpdb->get_results("
            SELECT 
                p.ID          AS id,
                pm.meta_value AS sku,
                al.asin       AS asin,
                al.status     AS status,
                p.post_parent AS parent_id,
                p.post_title  AS title,
                p.post_type   AS product_type
            FROM 
                {$wpdb->prefix}posts p
            LEFT JOIN
                {$wpdb->prefix}postmeta pm ON (p.ID = pm.post_id AND pm.meta_key = '_sku')
            LEFT JOIN
                {$wpdb->prefix}amazon_listings al ON (p.ID = al.post_id)
            WHERE 
                p.post_status = 'publish' AND
                ( p.post_type = 'product' OR p.post_type = 'product_variation' )
                $where_sql
            ORDER BY $orderby $order
            LIMIT $offset, $per_page
        ", ARRAY_A);

        // get total items count - if needed
        if ( ( $current_page == 1 ) && ( count( $items ) < $per_page ) ) {
            $this->total_items = count( $items );
        } else {
            $this->total_items = $wpdb->get_var("
                SELECT COUNT(p.ID)
                FROM 
                    {$wpdb->prefix}posts p
                LEFT JOIN
                    {$wpdb->prefix}postmeta pm ON (p.ID = pm.post_id AND pm.meta_key = '_sku')
                LEFT JOIN
                    {$wpdb->prefix}amazon_listings al ON (p.ID = al.post_id)
                WHERE 
                    p.post_status = 'publish' AND
                    ( p.post_type = 'product' OR p.post_type = 'product_variation' )
                    $where_sql
                ORDER BY $orderby $order
            ");         
        }

        return $items;
    } // getPageItems()

    public function getSkuGenStatusSummary() {

        $summary = new stdClass();
        $summary->missing_sku = $this->countByStatus( 'missing_sku' );
        $summary->long_sku    = $this->countByStatus( 'long_sku' );
        $summary->total_items = $this->countByStatus();

        return $summary;
    } // getSkuGenStatusSummary()


    public function countByStatus( $sku_status = false ) {
        global $wpdb;

        $where_sql = '';
        if ( $sku_status == 'missing_sku' ) {
            $where_sql = "AND ( pm.meta_value = '' OR pm.meta_value IS NULL ) ";
        } elseif ( $sku_status == 'long_sku' ) {
            $where_sql = "AND LENGTH(pm.meta_value) > 40 ";
        } 

        $items_count = $wpdb->get_var("
            SELECT COUNT(p.ID)
            FROM 
                {$wpdb->prefix}posts p
            LEFT JOIN
                {$wpdb->prefix}postmeta pm ON (p.ID = pm.post_id AND pm.meta_key = '_sku')
            LEFT JOIN
                {$wpdb->prefix}amazon_listings al ON (p.ID = al.post_id)
            WHERE 
                p.post_status = 'publish' AND
                ( p.post_type = 'product' OR p.post_type = 'product_variation' )
                $where_sql
        ");

        return $items_count;
    } // countByStatus()


    static public function getAllProductIDsWithoutSKU() {
        global $wpdb;

        $items = $wpdb->get_col("
            SELECT 
                p.ID          AS id
            FROM 
                {$wpdb->prefix}posts p
            LEFT JOIN
                {$wpdb->prefix}postmeta pm ON (p.ID = pm.post_id AND pm.meta_key = '_sku')
            LEFT JOIN
                {$wpdb->prefix}amazon_listings al ON (p.ID = al.post_id)
            WHERE 
                ( pm.meta_value = '' OR pm.meta_value IS NULL ) AND
                p.post_status = 'publish' AND
                ( p.post_type = 'product' OR p.post_type = 'product_variation' )
            ORDER BY p.ID ASC
            LIMIT 1000
        ");

        return $items;
    } // getAllProductIDsWithoutSKU()
    

    public function getAllExistingSKUs() {
        global $wpdb;

        $items = $wpdb->get_col("
            SELECT meta_value 
              FROM {$wpdb->prefix}postmeta pm 
             WHERE pm.meta_key = '_sku'
               AND pm.meta_value <> ''
            ");

        return $items;
    } // getAllExistingSKUs()
    

    public function getProductSkuData() {
        global $wpdb;

        $items = $wpdb->get_results("
            SELECT 
                p.ID          AS id,
                pm.meta_value AS sku,
                al.asin       AS asin,
                al.status     AS status,
                p.post_title  AS title
            FROM 
                {$wpdb->prefix}posts p
            LEFT JOIN
                {$wpdb->prefix}postmeta pm ON (p.ID = pm.post_id AND pm.meta_key = '_sku')
            LEFT JOIN
                {$wpdb->prefix}amazon_listings al ON (p.ID = al.post_id)
            WHERE 
                p.post_status = 'publish' AND
                ( p.post_type = 'product' OR p.post_type = 'product_variation' )
        ", ARRAY_A);

        return $items;
    } // getProductSkuData()
    

} // class WPLA_SkuGenerator
