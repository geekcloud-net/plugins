<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


/**
 * @class      YITH_COG_Report
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     Francisco Mendoza
 *
 */
class YITH_COG_Report extends WP_List_Table {

    /**
     * Max items.
     *
     * @var int
     */
    protected $max_items;

    /**
     * Array with the products ids.
     */
    protected $product_ids_array = array();

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
     * Display the table
     */
    public function display() {

        $singular = $this->_args['singular'];

        $this->display_tablenav( 'top' );

        $this->screen->render_screen_reader_content( 'heading_list' );
        ?>
        <table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
            <thead>
            <tr>
                <?php $this->print_column_headers(); ?>
            </tr>
            </thead>

            <tbody id="the-list"<?php
            if ( $singular ) {
                echo " data-wp-lists='list:$singular'";
            } ?>>
            <?php $this->display_rows_or_placeholder(); ?>
            </tbody>

            <tfoot>
            <tr>
                <?php $this->print_column_headers( false ); ?>
            </tr>
            </tfoot>
        </table>
        <?php
    }

    /**
     * Output the report.
     */
    public function output_report() {

        $totals = new YITH_COG_Report_Totals();

        $this->prepare_items();

        echo '<div id="table-content" style="float: right;">';
        $this->display();
        $totals->output_report();
        $this->display_tablenav('bottom');
        echo '</div>';
    }

    /**
     * Get column values.
     */
    public function column_default( $item, $column_name ) {

        global $product;

        if ( ! $product || $product->get_id() !== $item['prod_id']) {
            $product = wc_get_product( $item['prod_id'] );
        }

        if ( $product->is_type( 'gift-card' ) ) {
            return;
        }

        if ( ! $product ) {
            return;
        }

        //Columns content in the Report
        switch ( $column_name ) {


             case 'product' :

                if ( $product->is_type( 'variable' ) ) {

                    $item_id_array = $item['item_id'];

                    foreach ($item_id_array as $item_id) {
                        $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                        if ( $refund_id <= 0) {
                            $product_name = $item['item_name'];
                        }
                    }
                    if (isset($product_name)){
                        $url = $product->get_permalink();
                    ?><a href="<?php echo $url ?>"><?php echo $product_name . ' ' ?></a><span class="dashicons dashicons-arrow-down desplegable"></span><?php
                    ?><br><p></p><?php
                    }

                    $variation_id_array = array_unique($item['var_id']);

                    foreach ($variation_id_array as $var_id){
                        $item_id_array = $item['item_id'][$var_id];

                        foreach ($item_id_array as $item_id) {
                            $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                            if ( $refund_id <= 0) {

                                $variation_name = wc_get_order_item_meta($item_id, '_yith_cog_item_name_sortable', true) ;
                            }
                        }
                        if (isset($variation_name) ) {
                            ?><div class="childs" style="display: none"> <?php echo $variation_name; ?></div><?php
                        }
                    }
                }
                else{
                    $item_id_array = $item['item_id'];

                    foreach ($item_id_array as $item_id) {
                        $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                        if ( $refund_id <= 0) {
                            $product_name = $item['item_name'];
                        }
                    }
                    if (isset($product_name)){

                        $url = $product->get_permalink();
                        ?><a href="<?php echo $url ?>"><?php echo $product_name . ' ' ?><?php
                    }
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


            case 'total_sales':

                if ( $product->is_type( 'variable' ) ) {

                    $variation_id_array = array_unique($item['var_id']);
                    //total sales
                    $total_quantity = 0;
                    foreach ($variation_id_array as $var_id) {
                        //variation sales
                        $quantity = $item['var_qty'][$var_id];
                        $total_quantity += $quantity;
                    }
                    ?><div ><p><?php echo $total_quantity ?></p></div><?php

                    if ($total_quantity > 0 ) {
                        foreach ($variation_id_array as $var_id) {
                            //variation sales
                            $quantity = $item['var_qty'][$var_id];
                            ?><div class="childs" style="display: none"> <?php echo $quantity ?></div><?php
                        }
                    }
                }
                else{
                    $item_id_array = $item['item_id'];

                    $total_qty = 0;
                    foreach ($item_id_array as $item_id) {
                        $item_qty = $item['item_qty'][$item_id];
                        $total_qty += $item_qty;
                    }
                    if ( isset($total_qty) ) {
                        ?><div><p><?php echo $total_qty ?></p></div><?php
                    }
                }

                break;


            case 'product_price' :

                if ( $product->is_type( 'variable' ) ) {

                    $variation_id_array = array_unique($item['var_id']);
                    $min_max_array = array();

                    foreach ($variation_id_array as $var_id){
                        $item_id_array = $item['item_id'][$var_id];

                        foreach ($item_id_array as $item_id) {
                            $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                            if ( $refund_id <= 0) {
                                $variation_price = $item['item_price'][$var_id][$item_id] ;
                                $min_max_array[] = $variation_price;
                            }
                        }
                    }
                    if (isset($variation_price)) {
                        if (min($min_max_array) == max($min_max_array)){
                            ?><div ><p><?php echo wc_price(min($min_max_array)) ?></p></div><?php
                        }
                        else{
                            ?><div ><p><?php echo wc_price(min($min_max_array)) . ' – ' . wc_price(max($min_max_array)) ?></p></div><?php
                        }
                    }

                    foreach ($variation_id_array as $var_id){
                        $item_id_array = $item['item_id'][$var_id];

                        foreach ($item_id_array as $item_id) {
                            $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                            if ( $refund_id <= 0) {
                                $variation_price = $item['item_price'][$var_id][$item_id] ;
                            }
                        }
                        if (isset($variation_price) ) {
                            ?><div class="childs" style="display: none"> <?php echo wc_price($variation_price) . ' (p/u)' ?></div><?php
                        }
                    }
                }
                else{
                    $item_id_array = $item['item_id'];

                    foreach ($item_id_array as $item_id) {
                        $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                        if ( $refund_id <= 0) {
                            $product_price = $item['item_price'][$item_id];
                        }
                    }
                    if (isset($product_price)){
                        echo wc_price($product_price);
                    }
                }

                break;


            case 'product_total_price' :

                if ( $product->is_type( 'variable' ) ) {

                    $variation_id_array = array_unique($item['var_id']);

                    $total_price = 0;
                    foreach ($variation_id_array as $var_id) {
                        $item_id_array = $item['item_id'][$var_id];
                        $quantity = $item['var_qty'][$var_id];

                        foreach ($item_id_array as $item_id) {
                            $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                            if ( $refund_id <= 0) {
                                $variation_price = $item['item_price'][$var_id][$item_id] ;
                                $variation_total_price = $quantity * $variation_price;
                            }
                        }
                        if (isset($variation_total_price)){
                            $total_price += $variation_total_price;
                        }
                    }
                    //total prices
                    ?><div><p><?php echo wc_price($total_price) ?></p></div><?php

                    foreach ($variation_id_array as $var_id) {
                        $item_id_array = $item['item_id'][$var_id];
                        $quantity = $item['var_qty'][$var_id];

                        $variation_total_price = 0;
                        foreach ($item_id_array as $item_id) {
                            $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                            if ( $refund_id <= 0) {
                                $variation_price = $item['item_price'][$var_id][$item_id] ;
                                $variation_total_price = $quantity * $variation_price;
                            }
                        }
                        //variation total price
                        if (isset($variation_total_price)) {
                            ?><div class="childs" style="display: none"> <?php echo wc_price($variation_total_price) ?></div><?php
                        }
                    }
                }
                else {
                    $item_id_array = $item['item_id'];

                    $total_price = 0;
                    foreach ($item_id_array as $item_id) {
                        $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                        if ( $refund_id <= 0) {
                            $product_price = $item['item_price'][$item_id];
                        }
                        if ( isset($product_price) ) {
                            $item_qty = $item['item_qty'][$item_id];
                            $total_price += $item_qty * $product_price;
                        }
                    }
                    if ( isset($total_price) ) {
                        ?><div><p><?php echo wc_price($total_price) ?></p></div><?php
                    }
                }

                break;


            case 'product_cost' :

                if ( $product->is_type( 'variable' ) ) {

                    $variation_id_array = array_unique($item['var_id']);
                    $min_max_array = array();

                    foreach ($variation_id_array as $var_id) {
                        $item_id_array = $item['item_id'][$var_id];

                        foreach ($item_id_array as $item_id) {
                            $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                            if ($refund_id <= 0) {
                                $variation_cost = $item['item_cost'][$var_id][$item_id];
                                $min_max_array[] = $variation_cost;
                            }
                        }
                    }
                    if (isset($variation_cost)) {
                        if (min($min_max_array) == max($min_max_array)){
                            ?><div ><p><?php echo wc_price(min($min_max_array)) ?></p></div><?php
                        }
                        else{
                            ?><div ><p><?php echo wc_price(min($min_max_array)) . ' – ' . wc_price(max($min_max_array)) ?></p></div><?php
                        }
                    }

                    foreach ($variation_id_array as $var_id){
                        $item_id_array = $item['item_id'][$var_id];

                        foreach ($item_id_array as $item_id) {
                            $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                            if ( $refund_id <= 0) {
                                $variation_cost = $item['item_cost'][$var_id][$item_id];
                            }
                        }
                        if (isset($variation_cost) ) {
                            ?><div class="childs" style="display: none"> <?php echo wc_price($variation_cost) . ' (p/u)' ?></div><?php
                        }
                    }
                }
                else{
                    $item_id_array = $item['item_id'];

                    foreach ($item_id_array as $item_id) {
                        $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                        if ( $refund_id <= 0) {
                            $product_cost = $item['item_cost'][$item_id];
                        }
                    }
                    if (isset($product_cost)) {
                        echo wc_price($product_cost);
                    }
                }

                break;


            case 'product_total_cost' :

                if ( $product->is_type( 'variable' ) ) {

                    $variation_id_array = array_unique($item['var_id']);

                    $total_cost = 0;
                    foreach ($variation_id_array as $var_id) {
                        $item_id_array = $item['item_id'][$var_id];
                        $quantity = $item['var_qty'][$var_id];

                        foreach ($item_id_array as $item_id) {
                            $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                            if ($refund_id <= 0) {
                                $variation_cost = $item['item_cost'][$var_id][$item_id];
                                $variation_total_cost = $quantity * $variation_cost;
                            }
                        }
                        if (isset($variation_total_cost)) {
                            $total_cost += $variation_total_cost;
                        }
                    }
                    if ( isset($total_cost) ){
                        //total cost
                        ?><div ><p><?php echo wc_price($total_cost) ?></p></div><?php
                    }
                    foreach ($variation_id_array as $var_id){
                        $item_id_array = $item['item_id'][$var_id];
                        $quantity = $item['var_qty'][$var_id];

                        $total_cost = 0;
                        foreach ($item_id_array as $item_id) {
                            $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                            if ( $refund_id <= 0) {
                                $variation_cost = $item['item_cost'][$var_id][$item_id] ;
                                $total_cost = $quantity * $variation_cost;
                            }
                        }
                        if ( isset($total_cost) ) {
                            //variation total cost
                            ?><div class="childs" style="display: none"> <?php echo wc_price($total_cost) ?></div><?php
                        }
                    }
                }
                else{
                    $item_id_array = $item['item_id'];

                    $total_cost = 0;
                    foreach ($item_id_array as $item_id) {
                        $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                        if ( $refund_id <= 0) {
                            $product_cost = $item['item_cost'][$item_id];
                        }
                        if (isset($product_cost)) {
                            $item_qty = $item['item_qty'][$item_id];
                            $total_cost += $item_qty * $product_cost;
                        }
                    }
                    if ( isset($total_cost) ) {
                        ?><div ><p><?php echo wc_price($total_cost) ?></p></div><?php
                    }
                }

                break;


            case 'product_profit' :

                if ( $product->is_type( 'variable' ) ) {

                    $variation_id_array = array_unique($item['var_id']);
                    $min_max_array = array();

                    foreach ($variation_id_array as $var_id){
                        $item_id_array = $item['item_id'][$var_id];

                        foreach ($item_id_array as $item_id) {
                            $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                            if ( $refund_id <= 0) {
                                $variation_cost = $item['item_cost'][$var_id][$item_id] ;
                                $variation_price = $item['item_price'][$var_id][$item_id] ;
                                $variation_profit = $variation_price - $variation_cost ;
                                $min_max_array[] = $variation_profit;
                            }
                        }
                    }
                    if (isset($variation_profit)) {
                        if (min($min_max_array) == max($min_max_array)){
                            ?><div ><p><?php echo wc_price(min($min_max_array)) ?></p></div><?php
                        }
                        else{
                            ?><div ><p><?php echo wc_price(min($min_max_array)) . ' – ' . wc_price(max($min_max_array)) ?></p></div><?php
                        }
                    }

                    foreach ($variation_id_array as $var_id){
                        $item_id_array = $item['item_id'][$var_id];

                        foreach ($item_id_array as $item_id) {
                            $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                            if ( $refund_id <= 0) {
                                $variation_cost = $item['item_cost'][$var_id][$item_id] ;
                                $variation_price = $item['item_price'][$var_id][$item_id] ;
                                $variation_profit = $variation_price - $variation_cost ;
                            }
                        }
                        if (isset($variation_profit)) {
                            ?><div class="childs" style="display: none"> <?php echo wc_price($variation_profit) . ' (p/u)' ?></div><?php
                        }
                    }
                }
                else{
                    $item_id_array = $item['item_id'];

                    foreach ($item_id_array as $item_id) {
                        $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                        if ( $refund_id <= 0) {
                            $product_cost = $item['item_cost'][$item_id];
                            $product_price = $item['item_price'][$item_id];
                        }
                        if (isset($product_price) and isset($product_cost)){
                            $product_profit = $product_price - $product_cost;
                        }
                    }
                    if (isset($product_profit)){
                        echo wc_price($product_profit);
                    }
                }
                break;


            case 'product_total_profit' :

                if ( $product->is_type( 'variable' ) ) {

                    $variation_id_array = array_unique($item['var_id']);

                    $total_profit = 0;
                    foreach ($variation_id_array as $var_id){
                        $item_id_array = $item['item_id'][$var_id];
                        $quantity = $item['var_qty'][$var_id];

                        foreach ($item_id_array as $item_id) {
                            $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                            if ( $refund_id <= 0) {
                                $variation_cost = $item['item_cost'][$var_id][$item_id] ;
                                $variation_price = $item['item_price'][$var_id][$item_id] ;
                                $variation_profit = $variation_price - $variation_cost ;
                                $variation_total_profit = $quantity * $variation_profit;
                            }
                        }
                        if (isset($variation_total_profit)){
                            $total_profit += $variation_total_profit;
                        }
                    }
                    if ( isset($total_profit) ) {
                        //total profit
                        ?><div><p><?php echo wc_price($total_profit) ?></p></div><?php
                    }
                    foreach ($variation_id_array as $var_id){
                        $item_id_array = $item['item_id'][$var_id];
                        $quantity = $item['var_qty'][$var_id];

                        $variation_total_profit = 0;
                        foreach ($item_id_array as $item_id) {
                            $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                            if ( $refund_id <= 0) {
                                $variation_cost = $item['item_cost'][$var_id][$item_id] ;
                                $variation_price = $item['item_price'][$var_id][$item_id] ;
                                $variation_profit = $variation_price - $variation_cost ;
                                $variation_total_profit = $quantity * $variation_profit;
                            }
                        }
                        if ( isset($variation_total_profit) ) {
                            //variation profit
                            ?><div class="childs" style="display: none"> <?php echo wc_price($variation_total_profit) ?></div><?php
                        }
                    }
                }
                else {
                    $item_id_array = $item['item_id'];

                    $total_profit = 0;
                    foreach ($item_id_array as $item_id) {
                        $refund_id = wc_get_order_item_meta($item_id, '_refunded_item_id', true);
                        if ( $refund_id <= 0) {
                            $product_cost = $item['item_cost'][$item_id];
                            $product_price = $item['item_price'][$item_id];
                        }
                        if ( isset($product_price) and isset($product_cost) ) {
                            $item_qty = $item['item_qty'][$item_id];
                            $product_profit = $product_price - $product_cost;
                            $total_profit += $item_qty * $product_profit;
                        }
                    }
                    if ( isset($total_profit) ) {
                        ?><div><p><?php echo wc_price($total_profit) ?></p></div><?php
                    }
                }

                break;


            case 'wc_actions' :

                ?><p><?php
                $actions = array();
                $action_id = $product->is_type( 'variation' ) ? $item->parent : $item['prod_id'];

                $actions['edit'] = array(
                    'url'       => admin_url( 'post.php?post=' . $action_id . '&action=edit' ),
                    'name'      => __( 'Edit', 'woocommerce' ),
                    'action'    => "edit",
                );

                if ( $product->is_visible() ) {
                    $actions['view'] = array(
                        'url'       => get_permalink( $action_id ),
                        'name'      => __( 'View', 'woocommerce' ),
                        'action'    => "view",
                    );
                }

                foreach ( $actions as $action ) {
                    printf(
                        '<a class="button tips %1$s" href="%2$s" data-tip="%3$s">%4$s</a>',
                        esc_attr( $action['action'] ),
                        esc_url( $action['url'] ),
                        sprintf( esc_attr__( '%s product', 'woocommerce' ), $action['name'] ),
                        esc_html( $action['name'] )
                    );
                }
                ?></p><?php
                break;

            default:
                apply_filters( 'yith_columns_switch' , $column_name );

        }
    }


    /**
     * Get columns.
     */
    public function get_columns() {

        $columns = array(
            'product'               => __( 'Product', 'yith-cost-of-goods-for-woocommerce' ),
            'total_sales'           => __( 'Total Sales', 'yith-cost-of-goods-for-woocommerce' ),
            'product_price'         => __( 'Product Prices', 'yith-cost-of-goods-for-woocommerce' ),
            'product_total_price'   => __( 'Total Price', 'yith-cost-of-goods-for-woocommerce' ),
            'product_cost'          => __( 'Product Cost', 'yith-cost-of-goods-for-woocommerce' ),
            'product_total_cost'    => __( 'Total Cost', 'yith-cost-of-goods-for-woocommerce' ),
            'product_profit'        => __( 'Product Profit', 'yith-cost-of-goods-for-woocommerce' ),
            'product_total_profit'  => __( 'Total Profit', 'yith-cost-of-goods-for-woocommerce' ),
        );

        //Filter to add more columns to the table.
        $columns = apply_filters( 'yith_add_custom_columns', $columns );

        //Set the Actions column to the final.
        $columns['wc_actions'] = __( 'Actions', 'yith-cost-of-goods-for-woocommerce' );

        return $columns;
    }


    /**
     * Get sortable columns.
     */
    protected function get_sortable_columns() {

        return array(
            'product'               => array( 'item_name', true ),
            'total_sales'           => array( 'product_total_qty', true ),
            'product_total_price'   => array( 'product_total_price_sortable', true ),
            'product_total_cost'    => array( 'product_total_cost_sortable', true ),
            'product_total_profit'  => array( 'product_total_profit_sortable', true ),
        );
    }


    /**
     * Get items from Query.
     */
    public function get_items( $current_page, $per_page )
    {

        $this->max_items = 0;
        $this->items = array();

        $report = new YITH_COG_Report_Data();
        $report_cat = new YITH_COG_Report_Data_Category();
        $report_prod = new YITH_COG_Report_Data_Product();


        if ( isset( $_GET['report'] ) ){
            $report_name = $_GET['report'];
        }
        else{
            $report_name = 'sales_by_date';
        }


        // Get items depending of the report *****************************

        // Sales by date Report ***************
        if ( $report_name == 'sales_by_date' ) {

            $report->output_report();
            $data = $report->get_report_data();

            $item_taxes_array = $data->item_tax;
            $product_id_array = $data->product_ids;
            $variation_id_array = $data->variation_ids;
            $item_qty_array = $data->item_count;
            $item_id_array = $data->item_id;
            $item_price_array = $data->item_price;
            $item_cost_array = $data->item_cost;

            // $item data array
            $item = array(
                'prod_id' => $product_id_array,
                'var_id' => $variation_id_array,
                'qty' => $item_qty_array,
                'item_id' => $item_id_array,
                'item_price' => $item_price_array,
                'item_cost' => $item_cost_array,
                'item_tax' => $item_taxes_array,
            );

            /*IMPORTANT:
             *   In the following lines we have to structure the data for pass it to each column correctly as an item
             *   Recommended to use a log to se the structure and know how the data is structured
             */
            $array_by_product_id = array();
            $total_quantity = 0;
            $total_price = 0;
            $total_cost = 0;
            $total_var_quantity = 0;
            for ($i = 0; $i < count($item['var_id']); $i++) {

                //variable product item structure
                if ($item['var_id'][$i] != 0) {
                    $total_var_quantity += $item['qty'][$i];
                    $total_price += $item['qty'][$i] * $item['item_price'][$i];
                    $total_cost += $item['qty'][$i] * $item['item_cost'][$i];
                    $total_profit = $total_price - $total_cost;
                    $product = wc_get_product($item['prod_id'][$i]);
                    $product_name = $product->get_name();

                    $array_by_product_id[$item['prod_id'][$i]]['prod_id'] = $item['prod_id'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['var_id'][] = $item['var_id'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['total_qty'] = $total_var_quantity;
                    $array_by_product_id[$item['prod_id'][$i]]['var_qty'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['qty'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['item_id'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['item_id'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['item_price'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['item_price'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['item_name'] = $product_name;
                    $array_by_product_id[$item['prod_id'][$i]]['product_total_price_sortable'] = $total_price;
                    $array_by_product_id[$item['prod_id'][$i]]['product_total_qty'] = $total_var_quantity;

                    // change item cost value if exclude taxes
                    if ($report->exclude_taxes() == true) {
                        $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['item_cost'][$i];
                        $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = $total_cost;
                        $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = $total_profit;

                    } else {
                        if (isset($item['item_tax'][$i])) {
                            $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['item_cost'][$i] + $item['item_tax'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = $total_cost + $item['item_tax'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = $total_profit - $item['item_tax'][$i];

                        } else {
                            $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['item_cost'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = $total_cost;
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = $total_profit;
                        }
                    }
                }
                //simple product item structure
                else {
                    $total_quantity += $item['qty'][$i];
                    if ($total_quantity <= 0) {
                        $array_by_product_id[$item['prod_id'][$i]]['prod_id'] = 0;
                    } else {
                        $array_by_product_id[$item['prod_id'][$i]]['prod_id'] = $item['prod_id'][$i];
                    }

                    $product = wc_get_product($item['prod_id'][$i]);
                    $product_name = $product->get_name();

                    $array_by_product_id[$item['prod_id'][$i]]['item_id'][$i] = $item['item_id'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['total_qty'] = $total_quantity;
                    $array_by_product_id[$item['prod_id'][$i]]['item_qty'][$item['item_id'][$i]] = $item['qty'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['item_price'][$item['item_id'][$i]] = $item['item_price'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['item_name'] = $product_name;
                    $array_by_product_id[$item['prod_id'][$i]]['product_total_price_sortable'] = $item['item_price'][$i] * $item['qty'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['product_total_qty'] = $item['qty'][$i];

                    // change item cost value if exclude taxes
                    if ($report->exclude_taxes() == true) {
                        $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['item_id'][$i]] = $item['item_cost'][$i];
                        $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = $item['item_cost'][$i] * $item['qty'][$i];
                        $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = ( $item['item_price'][$i] * $item['qty'][$i] ) - ( $item['item_cost'][$i] * $item['qty'][$i] ) ;
                    }
                    else {
                        if (isset($item['item_tax'][$i])) {
                            $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['item_id'][$i]] = $item['item_cost'][$i] + $item['item_tax'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = ( $item['item_cost'][$i] + $item['item_tax'][$i] ) * $item['qty'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = ( $item['item_price'][$i] * $item['qty'][$i] ) - ( ( $item['item_cost'][$i] + $item['item_tax'][$i] ) * $item['qty'][$i] );
                        } else {
                            $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['item_id'][$i]] = $item['item_cost'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = $item['item_cost'][$i] * $item['qty'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = ( $item['item_price'][$i] * $item['qty'][$i] ) - ( $item['item_cost'][$i] * $item['qty'][$i] ) ;
                        }
                    }
                }
            }

            // calculate the total quantity of each variation
            foreach ($array_by_product_id as $prod_id => &$prod) {
                if (isset($prod['var_id'])) {
                    foreach ($prod['var_qty'] as $var_id => $var) {
                        $prod['var_qty'][$var_id] = array_sum($var);
                    }
                }
            }

            //In this foreach, unset the refunded items because they appear hidden in the table.
            $items_array = array();
            foreach ($array_by_product_id as $prod_id => &$prod) {
                if ($prod['total_qty'] <= 0) {
                    unset($prod);
                } else {
                    $items_array[$prod_id] = $prod;
                }
            }

            //$item array for the column_default function.
            $this->items = array_slice( $items_array,( ( $current_page - 1 ) * $per_page ), $per_page );

            //Sortable method for the columns
            usort( $this->items, array( $this, 'sort_data' ) );

            //item count for the table
            $cnt = count($items_array);
            $this->max_items = $cnt ;


            $this->filter_by_tag( $report, $current_page, $per_page );

        }


        // Sales by product Report ****************
        if ( $report_name == 'sales_by_product' ) {

            $report_prod->output_report();
            $data = $report_prod->get_report_data();
            $prod_ids = $report_prod->product_ids;

            if ( empty($prod_ids)){
                return;
            }

            $item_taxes_array = $data->item_tax;
            $product_id_array = $data->product_ids;
            $variation_id_array = $data->variation_ids;
            $item_qty_array = $data->item_count;
            $item_id_array = $data->item_id;
            $item_price_array = $data->item_price;
            $item_cost_array = $data->item_cost;

            // $item data array
            $item = array(
                'prod_id' => $product_id_array,
                'var_id' => $variation_id_array,
                'qty' => $item_qty_array,
                'item_id' => $item_id_array,
                'item_price' => $item_price_array,
                'item_cost' => $item_cost_array,
                'item_tax' => $item_taxes_array,
            );

            /*IMPORTANT:
             *   In the following lines we have to structure the data for pass it to each column correctly as an item
             *   Recommended to use a log to se the structure and know how the data is structured
             */
            $array_by_product_id = array();
            $total_quantity = 0;
            $total_price = 0;
            $total_cost = 0;
            $total_var_quantity = 0;

            for ($i = 0; $i < count($item['var_id']); $i++) {

                //variable product item structure
                if ($item['var_id'][$i] != 0) {
                    $total_var_quantity += $item['qty'][$i];
                    $total_price += $item['qty'][$i] * $item['item_price'][$i];
                    $total_cost += $item['qty'][$i] * $item['item_cost'][$i];
                    $total_profit = $total_price - $total_cost;
                    $product = wc_get_product($item['prod_id'][$i]);
                    $product_name = $product->get_name();

                    $array_by_product_id[$item['prod_id'][$i]]['prod_id'] = $item['prod_id'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['var_id'][] = $item['var_id'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['total_qty'] = $total_var_quantity;
                    $array_by_product_id[$item['prod_id'][$i]]['var_qty'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['qty'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['item_id'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['item_id'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['item_price'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['item_price'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['item_name'] = $product_name;
                    $array_by_product_id[$item['prod_id'][$i]]['product_total_price_sortable'] = $total_price;
                    $array_by_product_id[$item['prod_id'][$i]]['product_total_qty'] = $total_var_quantity;

                    // change item cost value if exclude taxes
                    if ($report->exclude_taxes() == true) {
                        $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['item_cost'][$i];
                        $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = $total_cost;
                        $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = $total_profit;
                    }
                    else {
                        if (isset($item['item_tax'][$i])) {
                            $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['item_cost'][$i] + $item['item_tax'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = $total_cost + $item['item_tax'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = $total_profit - $item['item_tax'][$i];
                        }
                        else {
                            $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['item_cost'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = $total_cost;
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = $total_profit;
                        }
                    }
                }
                //simple product item structure
                else {
                    $total_quantity += $item['qty'][$i];
                    if ($total_quantity <= 0) {
                        $array_by_product_id[$item['prod_id'][$i]]['prod_id'] = 0;
                    } else {
                        $array_by_product_id[$item['prod_id'][$i]]['prod_id'] = $item['prod_id'][$i];
                    }

                    $product = wc_get_product($item['prod_id'][$i]);
                    $product_name = $product->get_name();

                    $array_by_product_id[$item['prod_id'][$i]]['item_id'][$i] = $item['item_id'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['total_qty'] = $total_quantity;
                    $array_by_product_id[$item['prod_id'][$i]]['item_qty'][$item['item_id'][$i]] = $item['qty'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['item_price'][$item['item_id'][$i]] = $item['item_price'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['item_name'] = $product_name;
                    $array_by_product_id[$item['prod_id'][$i]]['product_total_price_sortable'] = $item['item_price'][$i] * $item['qty'][$i] ;
                    $array_by_product_id[$item['prod_id'][$i]]['product_total_qty'] = $item['qty'][$i];

                    // change item cost value if exclude taxes
                    if ($report->exclude_taxes() == true) {
                        $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['item_id'][$i]] = $item['item_cost'][$i];
                        $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = $item['item_cost'][$i] * $item['qty'][$i];
                        $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = ( $item['item_price'][$i] * $item['qty'][$i] ) - ( $item['item_cost'][$i] * $item['qty'][$i] ) ;

                    }
                    else {
                        if (isset($item['item_tax'][$i])) {
                            $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['item_id'][$i]] = $item['item_cost'][$i] + $item['item_tax'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = ( $item['item_cost'][$i] + $item['item_tax'][$i] ) * $item['qty'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = ( $item['item_price'][$i] * $item['qty'][$i] ) - ( ( $item['item_cost'][$i] + $item['item_tax'][$i] ) * $item['qty'][$i] );
                        }
                        else {
                            $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['item_id'][$i]] = $item['item_cost'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = $item['item_cost'][$i] * $item['qty'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = ( $item['item_price'][$i] * $item['qty'][$i] ) - ( $item['item_cost'][$i] * $item['qty'][$i] ) ;
                        }
                    }
                }
            }

            // calculate the total quantity of each variation
            foreach ($array_by_product_id as $prod_id => &$prod) {
                if (isset($prod['var_id'])) {
                    foreach ($prod['var_qty'] as $var_id => $var) {
                        $prod['var_qty'][$var_id] = array_sum($var);
                    }
                }
            }

            //In this foreach, unset the refunded items because they appear hidden in the table.
            $items_array = array();
            foreach ($array_by_product_id as $prod_id => &$prod) {
                if ($prod['total_qty'] <= 0) {
                    unset($prod);
                } else {
                    $items_array[$prod_id] = $prod;
                }
            }

            //$item array for the column_default function.
            $this->items =  $this->items = array_slice( $items_array,( ( $current_page - 1 ) * $per_page ), $per_page );

            //Sortable method for the columns
            usort( $this->items, array( $this, 'sort_data' ) );

            //item count for the table
            $cnt = count($items_array);
            $this->max_items = $cnt;

            $this->filter_by_tag( $report, $current_page, $per_page );
        }


        // Sales by category Report ****************
        if ( $report_name == 'sales_by_category' ) {

            $report_cat->output_report();
            $data = $report_cat->get_report_data();
            $cat_ids = $report_cat->category_ids;

            if ( empty($cat_ids)){
                return;
            }

            $item_taxes_array = $data->item_tax;
            $product_id_array = $data->product_ids;
            $variation_id_array = $data->variation_ids;
            $item_qty_array = $data->item_count;
            $item_id_array = $data->item_id;
            $item_price_array = $data->item_price;
            $item_cost_array = $data->item_cost;

            // $item data array
            $item = array(
                'prod_id' => $product_id_array,
                'var_id' => $variation_id_array,
                'qty' => $item_qty_array,
                'item_id' => $item_id_array,
                'item_price' => $item_price_array,
                'item_cost' => $item_cost_array,
                'item_tax' => $item_taxes_array,
            );

            /*IMPORTANT:
             *   In the following lines we have to structure the data for pass it to each column correctly as an item
             *   Recommended to use a log to se the structure and know how the data is structured
             */
            $array_by_product_id = array();
            $total_quantity = 0;
            $total_price = 0;
            $total_cost = 0;
            $total_var_quantity = 0;
            for ($i = 0; $i < count($item['var_id']); $i++) {

                //variable product item structure
                if ($item['var_id'][$i] != 0) {
                    $total_var_quantity += $item['qty'][$i];
                    $total_price += $item['qty'][$i] * $item['item_price'][$i];
                    $total_cost += $item['qty'][$i] * $item['item_cost'][$i];
                    $total_profit = $total_price - $total_cost;
                    $product = wc_get_product($item['prod_id'][$i]);
                    $product_name = $product->get_name();

                    $array_by_product_id[$item['prod_id'][$i]]['prod_id'] = $item['prod_id'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['var_id'][] = $item['var_id'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['total_qty'] = $total_var_quantity;
                    $array_by_product_id[$item['prod_id'][$i]]['var_qty'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['qty'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['item_id'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['item_id'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['item_price'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['item_price'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['item_name'] = $product_name;
                    $array_by_product_id[$item['prod_id'][$i]]['product_total_price_sortable'] = $total_price;
                    $array_by_product_id[$item['prod_id'][$i]]['product_total_qty'] = $total_var_quantity;

                    // change item cost value if exclude taxes
                    if ($report->exclude_taxes() == true) {
                        $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['item_cost'][$i];
                        $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = $total_cost;
                        $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = $total_profit;

                    }
                    else {
                        if (isset($item['item_tax'][$i])) {
                            $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['item_cost'][$i] + $item['item_tax'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = $total_cost + $item['item_tax'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = $total_profit - $item['item_tax'][$i];
                        }
                        else {
                            $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['item_cost'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = $total_cost;
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = $total_profit;
                        }
                    }
                }
                //simple product item structure
                else {
                    $total_quantity += $item['qty'][$i];
                    if ($total_quantity <= 0) {
                        $array_by_product_id[$item['prod_id'][$i]]['prod_id'] = 0;
                    }
                    else {
                        $array_by_product_id[$item['prod_id'][$i]]['prod_id'] = $item['prod_id'][$i];
                    }

                    $product = wc_get_product($item['prod_id'][$i]);
                    $product_name = $product->get_name();

                    $array_by_product_id[$item['prod_id'][$i]]['item_id'][$i] = $item['item_id'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['total_qty'] = $total_quantity;
                    $array_by_product_id[$item['prod_id'][$i]]['item_qty'][$item['item_id'][$i]] = $item['qty'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['item_price'][$item['item_id'][$i]] = $item['item_price'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['item_name'] = $product_name;
                    $array_by_product_id[$item['prod_id'][$i]]['product_total_price_sortable'] = $item['item_price'][$i] * $item['qty'][$i] ;
                    $array_by_product_id[$item['prod_id'][$i]]['product_total_qty'] = $item['qty'][$i];

                    // change item cost value if exclude taxes
                    if ($report->exclude_taxes() == true) {
                        $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['item_id'][$i]] = $item['item_cost'][$i];
                        $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = $item['item_cost'][$i] * $item['qty'][$i];
                        $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = ( $item['item_price'][$i] * $item['qty'][$i] ) - ( $item['item_cost'][$i] * $item['qty'][$i] ) ;
                    }
                    else {
                        if (isset($item['item_tax'][$i])) {
                            $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['item_id'][$i]] = $item['item_cost'][$i] + $item['item_tax'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = ( $item['item_cost'][$i] + $item['item_tax'][$i] ) * $item['qty'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = ( $item['item_price'][$i] * $item['qty'][$i] ) - ( ( $item['item_cost'][$i] + $item['item_tax'][$i] ) * $item['qty'][$i] );
                        }
                        else {
                            $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['item_id'][$i]] = $item['item_cost'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = $item['item_cost'][$i] * $item['qty'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = ( $item['item_price'][$i] * $item['qty'][$i] ) - ( $item['item_cost'][$i] * $item['qty'][$i] ) ;
                        }
                    }
                }
            }

            // calculate the total quantity of each variation
            foreach ($array_by_product_id as $prod_id => &$prod) {
                if (isset($prod['var_id'])) {
                    foreach ($prod['var_qty'] as $var_id => $var) {
                        $prod['var_qty'][$var_id] = array_sum($var);
                    }
                }
            }

            //In this foreach, unset the refunded items because they appear hidden in the table.
            $items_array = array();
            foreach ($array_by_product_id as $prod_id => &$prod) {
                if ($prod['total_qty'] <= 0) {
                    unset($prod);
                }
                else {
                    $items_array[$prod_id] = $prod;
                }
            }

            //$item array for the column_default function.
            $this->items = array_slice( $items_array,( ( $current_page - 1 ) * $per_page ), $per_page );

            //Sortable method for the columns
            usort( $this->items, array( $this, 'sort_data' ) );

            //item count for the table
            $cnt = count($items_array);
            $this->max_items = $cnt;

            $this->filter_by_tag( $report, $current_page, $per_page );

        }
    }


    /**
     * Allows you to sort the data by the variables set in the $_GET
     */
    private function sort_data( $a, $b ){
        $orderby = 'item_name';
        $order = 'asc';

        if(!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }

        if(!empty($_GET['order'])) {
            $order = $_GET['order'];
        }

//        $result = strnatcasecmp( $a[$orderby], $b[$orderby] );
        $result = strnatcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc') {
            return $result;
        }
        else{
            return -$result;
        }
    }


    /**
     * Filter the report by Tag
     */
    public function filter_by_tag( $report, $current_page, $per_page ){


        $report_tag = new YITH_COG_Report_Data_Tag();

        if ( isset($_GET['product_tag'] ) ){
            $report_tag->output_report();
            $data = $report_tag->get_report_data();

            $item_taxes_array = $data->item_tax;
            $product_id_array = $data->product_ids;
            $variation_id_array = $data->variation_ids;
            $item_qty_array = $data->item_count;
            $item_id_array = $data->item_id;
            $item_price_array = $data->item_price;
            $item_cost_array = $data->item_cost;

            // $item data array
            $item = array(
                'prod_id' => $product_id_array,
                'var_id' => $variation_id_array,
                'qty' => $item_qty_array,
                'item_id' => $item_id_array,
                'item_price' => $item_price_array,
                'item_cost' => $item_cost_array,
                'item_tax' => $item_taxes_array,
            );

            /*IMPORTANT:
             *   In the following lines we have to structure the data for pass it to each column correctly as an item
             *   Recommended to use a log to se the structure and know how the data is structured
             */
            $array_by_product_id = array();
            $total_quantity = 0;
            $total_price = 0;
            $total_cost = 0;
            $total_var_quantity = 0;
            for ($i = 0; $i < count($item['var_id']); $i++) {

                //variable product item structure
                if ($item['var_id'][$i] != 0) {
                    $total_var_quantity += $item['qty'][$i];
                    $total_price += $item['qty'][$i] * $item['item_price'][$i];
                    $total_cost += $item['qty'][$i] * $item['item_cost'][$i];
                    $total_profit = $total_price - $total_cost;
                    $product = wc_get_product($item['prod_id'][$i]);
                    $product_name = $product->get_name();

                    $array_by_product_id[$item['prod_id'][$i]]['prod_id'] = $item['prod_id'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['var_id'][] = $item['var_id'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['total_qty'] = $total_var_quantity;
                    $array_by_product_id[$item['prod_id'][$i]]['var_qty'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['qty'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['item_id'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['item_id'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['item_price'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['item_price'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['item_name'] = $product_name;
                    $array_by_product_id[$item['prod_id'][$i]]['product_total_price_sortable'] = $total_price;
                    $array_by_product_id[$item['prod_id'][$i]]['product_total_qty'] = $total_var_quantity;


                    // change item cost value if exclude taxes
                    if ($report->exclude_taxes() == true) {
                        $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['item_cost'][$i];
                        $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = $total_cost;
                        $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = $total_profit;

                    }
                    else {
                        if (isset($item['item_tax'][$i])) {
                            $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['item_cost'][$i] + $item['item_tax'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = $total_cost + $item['item_tax'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = $total_profit - $item['item_tax'][$i];

                        }
                        else {
                            $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['var_id'][$i]][$item['item_id'][$i]] = $item['item_cost'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = $total_cost;
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = $total_profit;
                        }
                    }
                }
                //simple product item structure
                else {
                    $total_quantity += $item['qty'][$i];
                    if ($total_quantity <= 0) {
                        $array_by_product_id[$item['prod_id'][$i]]['prod_id'] = 0;
                    }
                    else {
                        $array_by_product_id[$item['prod_id'][$i]]['prod_id'] = $item['prod_id'][$i];
                    }

                    $product = wc_get_product($item['prod_id'][$i]);
                    $product_name = $product->get_name();

                    $array_by_product_id[$item['prod_id'][$i]]['item_id'][$i] = $item['item_id'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['total_qty'] = $total_quantity;
                    $array_by_product_id[$item['prod_id'][$i]]['item_qty'][$item['item_id'][$i]] = $item['qty'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['item_price'][$item['item_id'][$i]] = $item['item_price'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['item_name'] = $product_name;
                    $array_by_product_id[$item['prod_id'][$i]]['product_total_price_sortable'] = $item['item_price'][$i] * $item['qty'][$i];
                    $array_by_product_id[$item['prod_id'][$i]]['product_total_qty'] = $item['qty'][$i];

                    // change item cost value if exclude taxes
                    if ($report->exclude_taxes() == true) {
                        $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['item_id'][$i]] = $item['item_cost'][$i];
                        $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = $item['item_cost'][$i] * $item['qty'][$i];
                        $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = ( $item['item_price'][$i] * $item['qty'][$i] ) - ( $item['item_cost'][$i] * $item['qty'][$i] ) ;
                    }
                    else {
                        if (isset($item['item_tax'][$i])) {
                            $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['item_id'][$i]] = $item['item_cost'][$i] + $item['item_tax'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = ( $item['item_cost'][$i] + $item['item_tax'][$i] ) * $item['qty'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = ( $item['item_price'][$i] * $item['qty'][$i] ) - ( ( $item['item_cost'][$i] + $item['item_tax'][$i] ) * $item['qty'][$i] );
                        }
                        else {
                            $array_by_product_id[$item['prod_id'][$i]]['item_cost'][$item['item_id'][$i]] = $item['item_cost'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_cost_sortable'] = $item['item_cost'][$i] * $item['qty'][$i];
                            $array_by_product_id[$item['prod_id'][$i]]['product_total_profit_sortable'] = ( $item['item_price'][$i] * $item['qty'][$i] ) - ( $item['item_cost'][$i] * $item['qty'][$i] ) ;
                        }
                    }
                }
            }

            // calculate the total quantity of each variation
            foreach ($array_by_product_id as $prod_id => &$prod) {
                if (isset($prod['var_id'])) {
                    foreach ($prod['var_qty'] as $var_id => $var) {
                        $prod['var_qty'][$var_id] = array_sum($var);
                    }
                }
            }

            //In this foreach, unset the refunded items because they appear hidden in the table.
            $items_array = array();
            foreach ($array_by_product_id as $prod_id => &$prod) {
                if ($prod['total_qty'] <= 0) {
                    unset($prod);
                } else {
                    $items_array[$prod_id] = $prod;
                }
            }

            //Sortable method for the columns
            usort( $items_array, array( &$this, 'sort_data' ) );

            //$item array for the column_default function.
            $this->items = array_slice( $items_array,( ( $current_page - 1 ) * $per_page ), $per_page );

            //item count for the table
            $cnt = count($items_array);
            $this->max_items = $cnt ;

        }
    }


    /**
     * Prepare list items.
     */
    public function prepare_items() {

        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
        $per_page = apply_filters( 'yith_cog_report_by_date_products_per_page', 20);
        $current_page = absint( $this->get_pagenum() );

        $this->get_items( $current_page, $per_page );

        /**
         * Pagination.
         */
        $this->set_pagination_args( array(
            'total_items' => $this->max_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $this->max_items / $per_page ),
        ) );
    }

}
