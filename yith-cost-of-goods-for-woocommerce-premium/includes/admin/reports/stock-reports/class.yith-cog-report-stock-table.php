<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * @class      YITH_COG_Report_Stock
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     Francisco Mendoza
 *
 */
class YITH_COG_Report_Stock extends WP_List_Table {


    /**
     * Max items.
     *
     * @var int
     */
    protected $max_items;

    /**
     * Construct
     *
     * @since 1.0
     */
    public function __construct() {

        parent::__construct();
    }


    /**
     * No items found text.
     */
    public function no_items() {
        _e( 'No products found.', 'yith-cost-of-goods-for-woocommerce' );
    }


    /**
     * Table position
     */
    public function display_tablenav( $position ) {

        if ( 'top' !== $position ) {
            parent::display_tablenav( $position );
        }
    }


    /**
     * Output the report.
     */
    public function output_report() {

        $this->prepare_items();

        echo '<div id="table-content" style="float: right;">';
        $this->display();
        echo '</div>';
    }


    /**
     * Get column value.
     *
     * @param mixed $item
     * @param string $column_name
     */
    public function column_default( $item, $column_name ) {

        global $product;


        if ( ! $product || $product->get_id() !== $item->id ) {
            $product = wc_get_product( $item->id );
        }
        if ( ! $product ) {
            return ;
        }

        //Columns content in the Report
        switch ( $column_name ) {

            case 'product' :

                if ($product->is_type('variable')) {
                    update_post_meta($product->get_id(), 'yith_product_name', $product->get_name());

                    $url = $product->get_permalink();
                    ?><a href="<?php echo $url ?>"><?php echo $product->get_name() . ' ' ?></a></a><span class="dashicons dashicons-arrow-down desplegable"></span><?php
                    ?><br><p></p><?php
                    $product_variations = $product->get_available_variations();
                    foreach ($product_variations as $variation) {
                        $variation_name = $variation['image']['title'];

                        ?><div class="childs" style="display: none"><?php echo $variation_name ?></div><?php
                    }
                }
                else{
                    update_post_meta($product->get_id(), 'yith_product_name', $product->get_name());

                    $url = $product->get_permalink();
                    ?><a href="<?php echo $url ?>"><?php echo $product->get_name() . ' ' ?></a><?php
                }


                break;


            case 'tag':

                $terms = get_the_terms( $product->get_id(), 'product_tag' );
                $tag_id_array = array();
                $term_array = array();
                if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                    foreach ($terms as $term) {
                        $term_array[] = $term->name;
                        $tag_id_array[] = $term->term_id;
                    }
                    $tag_data_array = array_combine($tag_id_array, $term_array);

                    foreach ($tag_data_array as $tag_id => $tag){
                        ?>
                        <a class="tag_link" href="<?php echo esc_url( add_query_arg( 'product_tag', $tag_id ) )?>"><?php echo $tag ?></a>
                        <?php

                    }
                }
                break;


            case 'stock_status' :

                if ($product->is_type('variable')) {
                    $product_variations = $product->get_available_variations();
                    $product_stock = 0;

                    foreach ($product_variations as $variation) {
                        $variation_stock = $variation['max_qty'];
                        if ( !is_numeric($variation_stock) ){
                            $variation_stock = 0;
                        }
                        $product_stock += $variation_stock;
                    }

                    if ($product_stock > 0) {
                        $stock_html = '<mark class="instock">' . __('In stock', 'yith-cost-of-goods-for-woocommerce') . '</mark>';
                    } else {
                        $stock_html = '<mark class="outofstock">' . __('Out of stock', 'yith-cost-of-goods-for-woocommerce') . '</mark>';
                    }
                    echo apply_filters('yith_cog_report_stock_status_variable', $stock_html, $product);


                    $product->set_stock_quantity($product_stock);
                    update_post_meta($product->get_id(), 'yith_stock', $product->get_stock_quantity());
                    echo ' (' . $product_stock . ')';

                    foreach ($product_variations as $variation) {
                        $variation_stock = $variation['max_qty'];
                        if ( !is_numeric($variation_stock) ){
                            $variation_stock = 0;
                        }

                        if ($variation_stock > 0) {
                            $stock_html = '<mark class="instock">' . __('In stock', 'yith-cost-of-goods-for-woocommerce') . '</mark>';
                        } else {
                            $stock_html = '<mark class="outofstock">' . __('Out of stock', 'yith-cost-of-goods-for-woocommerce') . '</mark>';
                        }
                        ?><div class="childs" style="display: none"> <?php echo $stock_html . ' (' . $variation_stock . ')' ?></div><?php
                    }
                }
                else {
                    if ($product->is_in_stock()) {
                        $stock_html = '<mark class="instock">' . __('In stock', 'yith-cost-of-goods-for-woocommerce') . '</mark>';
                    } else {
                        $stock_html = '<mark class="outofstock">' . __('Out of stock', 'yith-cost-of-goods-for-woocommerce') . '</mark>';
                    }
                    update_post_meta($product->get_id(), 'yith_stock', $product->get_stock_quantity());


                    echo apply_filters('yith_cog_report_stock_status', $stock_html, $product);

                    echo ' (' . $product->get_stock_quantity() . ')';

                }

                break;

            case 'product_price' :

                if ($product->is_type('variable')) {
                    $product_variations = $product->get_available_variations();

                    update_post_meta($product->get_id(), 'yith_price', $product->get_price());

                    echo $product->get_price_html();

                    foreach ($product_variations as $variation) {
                        $variation_price = $variation['display_price'];
                        ?><div class="childs" style="display: none"> <?php echo wc_price($variation_price) ?></div><?php
                    }
                }
                else{
                    update_post_meta($product->get_id(), 'yith_price', $product->get_price());
                    echo $product->get_price_html();
                }

                break;


            case 'product_total_price' :

                if ($product->is_type('variable')) {
                    $product_variations = $product->get_available_variations();
                    $var_price = 0;
                    foreach ($product_variations as $variation) {
                        if ($variation['is_in_stock'] == 1) {
                            $variation_price = $variation['display_price'];
                            $variation_stock = $variation['max_qty'];
                            $var_price += ($variation_price * $variation_stock);
                        } else
                            $var_price = $product->get_price();
                    }
                    update_post_meta($product->get_id(), 'yith_total_price', $var_price);

                    echo wc_price($var_price);

                    foreach ($product_variations as $variation) {
                        $variation_price = $variation['display_price'];
                        $variation_stock = $variation['max_qty'];

                        if ( !is_numeric($variation_stock) ){
                            $variation_stock = 0;
                        }

                        $total_var_price = $variation_price * $variation_stock;

                        ?><div class="childs" style="display: none"> <?php echo wc_price($total_var_price) ?></div><?php
                    }
                }
                else{
                    $total_price = $product->get_price() * $product->get_stock_quantity();
                    update_post_meta($product->get_id(), 'yith_total_price', $total_price);
                    echo wc_price($total_price);
                }


                break;

            case 'product_cost' :

                if ($product->is_type('variable')) {
                    $product_variations = $product->get_available_variations();

                    $cost = YITH_COG_Product::get_cost_html($product);
                    echo $cost;

                    foreach ($product_variations as $variation) {
                        $variation_id = $variation['variation_id'];
                        $variation_obj = wc_get_product($variation_id);
                        $variation_cost = YITH_COG_Product::get_cost($variation_obj);


                        ?><div class="childs" style="display: none"> <?php echo wc_price($variation_cost) ?></div><?php
                    }
                }
                else {
                    $cost = YITH_COG_Product::get_cost_html($product);

                    if (!empty($cost)){
                        echo $cost;
                    }
                    else{
                        _e( 'No cost is set for this product', 'yith-cost-of-goods-for-woocommerce' );
                    }
                }

                break;

            case 'product_total_cost' :

                if ($product->is_type('variable')) {
                    $product_variations = $product->get_available_variations();
                    $var_cost_total = 0;
                    foreach ($product_variations as $variation) {
                        if ($variation['is_in_stock'] == 1) {
                            $variation_stock = $variation['max_qty'];
                            if ( !is_numeric($variation_stock) ){
                                $variation_stock = 0;
                            }
                            $variation_id = $variation['variation_id'];
                            $variation_obj = wc_get_product($variation_id);
                            $var_cost = YITH_COG_Product::get_cost($variation_obj);

                            $var_cost_total += ($var_cost * $variation_stock);
                        } else
                            $variation_id = $variation['variation_id'];
                            $variation_obj = wc_get_product($variation_id);
                            $var_cost = YITH_COG_Product::get_cost($variation_obj);
                            $var_cost_total = $var_cost;
                    }
                    update_post_meta($product->get_id(), 'yith_total_cost', $var_cost_total);

                    echo wc_price($var_cost_total);

                    foreach ($product_variations as $variation) {
                        $variation_stock = $variation['max_qty'];
                        $variation_id = $variation['variation_id'];
                        $variation_obj = wc_get_product($variation_id);
                        $var_cost = YITH_COG_Product::get_cost($variation_obj);

                        if ( !is_numeric($variation_stock) ){
                            $variation_stock = 0;
                        }

                        $total_var_cost = $var_cost * $variation_stock;

                        ?><div class="childs" style="display: none"> <?php echo wc_price($total_var_cost) ?></div><?php
                    }
                }
                else {

                    $cost = YITH_COG_Product::get_cost($product);
                    if (!empty($cost)){
                        $total_cost = $cost * $product->get_stock_quantity();
                        update_post_meta($product->get_id(), 'yith_total_cost', $total_cost);
                        echo wc_price( $total_cost );                    }
                    else{
                        update_post_meta($product->get_id(), 'yith_total_cost', 0);
                        _e( 'No cost is set for this product', 'yith-cost-of-goods-for-woocommerce' );
                    }
                }

                break;


            case 'potential_profit' :

                $cost = (float) YITH_COG_Product::get_cost( $product );

                $total_profit = ($product->get_price() - $cost) * $product->get_stock_quantity();


                if ( $product->is_type( 'variable' ) ) {

                    $product_variations = $product->get_available_variations();
                    $var_profit = 0;

                    foreach ( $product_variations as $variation){
                        if ($variation[ 'is_in_stock' ] == 1 ) {
                            $variation_stock = $variation['max_qty'];
                            $variation_price = $variation['display_price'];
                            $variation_id = $variation['variation_id'];
                            $variation_obj = wc_get_product($variation_id);
                            $variation_cost = YITH_COG_Product::get_cost($variation_obj);
                            $variation_profit = ( $variation_price - $variation_cost);
                            $var_profit += ( $variation_profit  * $variation_stock );
                        }
                        else
                            $var_profit = $total_profit;
                    }
                    update_post_meta($product->get_id(), 'yith_profit', $var_profit);
                    echo  wc_price( $var_profit );

                    foreach ( $product_variations as $variation) {
                        $variation_stock = $variation['max_qty'];
                        $variation_price = $variation['display_price'];
                        $variation_id = $variation['variation_id'];
                        $variation_obj = wc_get_product($variation_id);
                        $variation_cost = YITH_COG_Product::get_cost($variation_obj);
                        $variation_profit = ( $variation_price - $variation_cost);

                        if ( !is_numeric($variation_stock) ){
                            $variation_stock = 0;
                        }

                        $total_var_profit = ($variation_profit * $variation_stock);

                        ?><div class="childs" style="display: none"> <?php echo wc_price( $total_var_profit ) ?></div><?php
                    }
                }
                else{
                    update_post_meta($product->get_id(), 'yith_profit', $total_profit);
                    echo wc_price( $total_profit );
                }


                break;

            case 'wc_actions' :
                ?><p><?php
                $actions = array();
                $action_id = $product->is_type( 'variation' ) ? $item->parent : $item->id;

                $actions['edit'] = array(
                    'url'       => admin_url( 'post.php?post=' . $action_id . '&action=edit' ),
                    'name'      => __( 'Edit', 'yith-cost-of-goods-for-woocommerce' ),
                    'action'    => "edit",
                );

                if ( $product->is_visible() ) {
                    $actions['view'] = array(
                        'url'       => get_permalink( $action_id ),
                        'name'      => __( 'View', 'yith-cost-of-goods-for-woocommerce' ),
                        'action'    => "view",
                    );
                }
                $actions = apply_filters( 'yith_cog_admin_stock_report_product_actions', $actions, $product );

                foreach ( $actions as $action ) {
                    printf(
                        '<a class="button tips %1$s" href="%2$s" data-tip="%3$s">%4$s</a>',
                        esc_attr( $action['action'] ),
                        esc_url( $action['url'] ),
                        sprintf( esc_attr__( '%s product', 'yith-cost-of-goods-for-woocommerce' ), $action['name'] ),
                        esc_html( $action['name'] )
                    );
                }
                ?></p><?php

                break;

            default:
                apply_filters( 'yith_columns_switch_stock' , $column_name );
        }
    }


    /**
     * Get columns.
     */
    public function get_columns() {

        $columns = array(
            'product'               => __( 'Product', 'yith-cost-of-goods-for-woocommerce' ),
            'stock_status'          => __( 'Stock status', 'yith-cost-of-goods-for-woocommerce' ),
            'product_price'         => __( 'Product Price', 'yith-cost-of-goods-for-woocommerce' ),
            'product_total_price'   => __( 'Product Total Price', 'yith-cost-of-goods-for-woocommerce' ),
            'product_cost'          => __( 'Product Cost', 'yith-cost-of-goods-for-woocommerce' ),
            'product_total_cost'    => __( 'Product Total Cost', 'yith-cost-of-goods-for-woocommerce' ),
            'potential_profit'      => __( 'Potential Profit', 'yith-cost-of-goods-for-woocommerce' ),
        );

        //Filter to add more columns to the table.
        $columns = apply_filters( 'yith_add_custom_columns_stock', $columns );

        //Set the Actions column to the final.
        $columns['wc_actions'] = __( 'Actions', 'yith-cost-of-goods-for-woocommerce' );

        return $columns;
    }


    //Desactivated in this version
//    /**
//     * Get sortable columns.
//     */
//    protected function get_sortable_columns() {
//
//        return array(
//            'product'               => array( 'yith_product_name', true ),
//            'stock_status'          => array( 'yith_stock', true ),
//            'product_price'         => array( 'yith_price', true ),
//            'product_total_price'   => array( 'yith_total_price', true ),
//            'product_cost'          => array( 'yith_cost', true ),
//            'product_total_cost'    => array( 'yith_total_cost', true ),
//            'potential_profit'      => array( 'yith_profit', true ),
//        );
//    }


    /**
     * Get items from Query.
     */
    public function get_items( $current_page, $per_page ){

        global $wpdb;

        $this->max_items = 0;
        $this->items = array();

        if (isset($_GET['order'])){
            $order = $_GET['order'];
        }
        else{
            $order = 'ASC';
        }
        if (isset($_GET['orderby'])){
            $orderby = $_GET['orderby'];
        }
        else{
            $orderby = 'yith_stock';
        }

        $data_product = new YITH_COG_Report_Stock_Data_Product();
        $data_category = new YITH_COG_Report_Stock_Data_Category();
        $data_all_stock = new YITH_COG_Report_Stock_Data_All_Stock();

        if ( isset( $_GET['report'] ) ){
            $report_name = $_GET['report'];
        }
        else{
            $report_name = 'all_stock';
        }


        if ( $report_name == 'all_stock' ){

            $data_all_stock->output_report();

            $query_from = "FROM {$wpdb->posts} as posts
			INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
			INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
		
			WHERE 1=1
			AND posts.post_type IN ( 'product' )
			AND posts.post_status = 'publish'
			AND postmeta.meta_key = '_manage_stock' AND postmeta.meta_value = 'yes'
			";
//          AND postmeta2.meta_key = '{$orderby}'


            $query_from = apply_filters('yith_cog_report_stock_all_stock', $query_from);

            $this->items = $wpdb->get_results($wpdb->prepare("SELECT posts.ID as id, posts.post_parent as parent {$query_from} GROUP BY posts.ID ORDER BY CAST(postmeta2.meta_value AS SIGNED) {$order} LIMIT %d, %d;", ($current_page - 1) * $per_page, $per_page));

            $this->max_items = $wpdb->get_var("SELECT COUNT( DISTINCT posts.ID ) {$query_from};");

            $this->filter_by_tag( $current_page, $per_page, $orderby, $order );

        }


        if ( $report_name == 'stock_by_product' ){

            $data_product->output_report();

            $product_id_array = $data_product->product_ids;
            $product_ids = join(",", $product_id_array);

            if ( empty($product_ids)){
                return;
            }

            $query_from = "FROM {$wpdb->posts} as posts
			    INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
			    INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
		
			    WHERE 1=1
			    AND posts.post_type IN ( 'product' )
			    AND posts.post_status = 'publish'
			    AND postmeta.meta_key = '_manage_stock' AND postmeta.meta_value = 'yes'
			    AND postmeta.post_id IN ( {$product_ids} )
			    ";

            $query_from = apply_filters('yith_cog_report_stock_by_product', $query_from);

            $this->items = $wpdb->get_results($wpdb->prepare("SELECT posts.ID as id, posts.post_parent as parent {$query_from} GROUP BY posts.ID ORDER BY CAST(postmeta2.meta_value AS SIGNED) {$order} LIMIT %d, %d;", ($current_page - 1) * $per_page, $per_page));
            
            $this->max_items = $wpdb->get_var("SELECT COUNT( DISTINCT posts.ID ) {$query_from};");

            $this->filter_by_tag( $current_page, $per_page, $orderby, $order );

        }


        if ( $report_name == 'stock_by_category' ){

            $data_category->output_report();

            $category_array = $data_category->category_ids;
            $get_products_in_categories = $data_category->get_product_ids_in_category($category_array);
            $product_ids = join(",", $get_products_in_categories);

            if ( empty($category_array)){
                return;
            }

            $query_from = "FROM {$wpdb->posts} as posts
			    INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
			    INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
		
			    WHERE 1=1
			    AND posts.post_type IN ( 'product' )
			    AND posts.post_status = 'publish'
			    AND postmeta.meta_key = '_manage_stock' AND postmeta.meta_value = 'yes'
			    AND postmeta.post_id IN ( {$product_ids} )

			    ";


            $query_from = apply_filters('yith_cog_report_stock_by_category', $query_from);

            $this->items = $wpdb->get_results($wpdb->prepare("SELECT posts.ID as id, posts.post_parent as parent {$query_from} GROUP BY posts.ID ORDER BY CAST(postmeta2.meta_value AS SIGNED) {$order} LIMIT %d, %d;", ($current_page - 1) * $per_page, $per_page));

            $this->max_items = $wpdb->get_var("SELECT COUNT( DISTINCT posts.ID ) {$query_from};");

            $this->filter_by_tag( $current_page, $per_page, $orderby, $order );
        }

    }


    /**
     * Filter the report by Tag
     */
    public function filter_by_tag( $current_page, $per_page , $orderby, $order ){

        global $wpdb;

        $data_by_tag = new YITH_COG_Report_Data_Tag();

        if ( isset($_GET['product_tag'] ) ){
            $data_by_tag->output_report();

            $get_products_in_tag = $data_by_tag->get_product_ids_in_tag($data_by_tag->tag_id);
            $product_ids = join(",", $get_products_in_tag);


            if ( empty($get_products_in_tag)){
                return;
            }

            $query_from = "FROM {$wpdb->posts} as posts
			    INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
			    INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
		
			    WHERE 1=1
			    AND posts.post_type IN ( 'product' )
			    AND posts.post_status = 'publish'
			    AND postmeta.meta_key = '_manage_stock' AND postmeta.meta_value = 'yes'
			    AND postmeta.post_id IN ( {$product_ids} )
			    
			    ";

            $query_from = apply_filters('yith_cog_report_stock_by_tag', $query_from);

            $this->items = $wpdb->get_results($wpdb->prepare("SELECT posts.ID as id, posts.post_parent as parent {$query_from} GROUP BY posts.ID ORDER BY CAST(postmeta2.meta_value AS SIGNED) {$order} LIMIT %d, %d;", ($current_page - 1) * $per_page, $per_page));

            $this->max_items = $wpdb->get_var("SELECT COUNT( DISTINCT posts.ID ) {$query_from};");
        }

    }



    /**
     * Prepare list items.
     */
    public function prepare_items() {

        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
        $current_page          = absint( $this->get_pagenum() );
        $per_page              = apply_filters( 'yith_cog_admin_stock_report_products_per_page', 20 );



        $this->get_items( $current_page, $per_page );

        $this->set_pagination_args( array(
            'total_items' => $this->max_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $this->max_items / $per_page ),
        ) );
    }
}
