<?php

/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary.
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}




/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 * 
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 * 
 * Our theme for this list table is going to be profiles.
 */
class WPLA_RepricingTable extends WPLA_ListingsTable {

    var $last_product_id         = 0;
    var $last_product_object     = array();
    var $last_product_var_id     = 0;
    var $last_product_variations = array();
    var $last_profile_id         = 0;
    var $last_profile_object     = array();
    var $total_items;

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'listing',     //singular name of the listed records
            'plural'    => 'listings',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
        // get array of profile names
        // $this->profiles  = WPLA_AmazonProfile::getAllNames();
        // $this->templates = WPLA_AmazonProfile::getAllTemplateNames();
    }
    
    
    function column_default($item, $column_name){
        switch($column_name){
            case 'fba_inv_age_90':
            case 'fba_inv_age_180':
            case 'fba_inv_age_270':
            case 'fba_inv_age_365':
            case 'fba_inv_age_365_plus':
                return $item[$column_name] ? $item[$column_name] : '&mdash;';
            // case 'fba_fee_ltsf_12':
            //     return isset($item[$column_name]) ? $this->profiles[ $item[$column_name] ] : '&mdash;';
            // case 'profile':
            //     return isset($item['profile_id']) ? $this->profiles[ $item['profile_id'] ] : '';
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    
        
  

    
    function column_lowest_price($item){

        $lowest_price = $item['lowest_price'] ? $this->number_format( $item['lowest_price'], 2 ) : '&mdash;';
        $last_updated = $item['pricing_date'] ? human_time_diff( strtotime($item['pricing_date'].' UTC') ) . ' ago' : '';
        $has_buybox   = $item['has_buybox'];

        // if we have the BuyBox, use buybox_price instead of lowest price (to avoid confusion)
        if ( $has_buybox ) $lowest_price = $this->number_format( $item['buybox_price'], 2 );

        // get prices
        $regular_price    = $this->getPriceForItem( $item );
        $sale_price       = $this->getSalePriceForItem( $item );
        $price_to_compare = $sale_price ? $sale_price : $regular_price;

        $lowest_price_color = 'darkred';
        if ( $item['lowest_price'] >= $item['min_price'] )
            $lowest_price_color = 'orange';
        if ( $item['lowest_price'] >= $price_to_compare )
            $lowest_price_color = 'green';
        if ( $item['has_buybox'] )
            $lowest_price_color = 'green';
        if ( ! $item['lowest_price'] )
            $lowest_price_color = '';

        if ( $item['lowest_price'] ) {
            // $lowest_price_link = sprintf('<a href="#" onclick="wpla_use_lowest_price(%3$s);return false;" style="color:%1$s">%2$s</a>', $lowest_price_color, $lowest_price, $item['id'] );
            $lowest_price_link = sprintf('<a href="#" data-id="%3$s" style="color:%1$s">%2$s</a>', $lowest_price_color, $lowest_price, $item['id'] );
        } else {
            $lowest_price_link = sprintf('<span style="color:%1$s">%2$s</span>', $lowest_price_color, $lowest_price );
        }

        if ( $has_buybox ) {
            $tip_msg = 'The Buy Box shows your offer.';
            $img_url = WPLA_URL . '/img/icon-success-32x32.png';
            $tip_msg = '&nbsp;<img src="'.$img_url.'" style="height:12px; padding:0;" class="tips" data-tip="'.$tip_msg.'"/>';
            $lowest_price_link .= $tip_msg;
        }

        return sprintf('%1$s<br><small style="color:silver">%2$s</small>',
            /*$2%s*/ $lowest_price_link,
            /*$3%s*/ $last_updated
        );
    }
    

    function column_msrp($item){
        $msrp_price = $this->getMSRPriceForItem($item);
        $msrp_price = $msrp_price ? $this->number_format( $msrp_price, 2 ) : '&mdash;';
        return $msrp_price;
    }
    

    function column_compet_price($item){

        $compet_price = $item['compet_price'] ? $this->number_format( $item['compet_price'], 2 ) : '&mdash;';

        $listing_url     = WPLA_ListingsModel::getUrlForItemObj( $item );
        $view_offers_url  = str_replace( '/dp/', '/gp/offer-listing/', $listing_url );
        $view_offers_link = '<a href="'.$view_offers_url.'" target="_blank">&raquo;&nbsp;view</a>';

        return sprintf('%1$s<br><small style="color:silver">%2$s</small>',
            /*$2%s*/ $compet_price,
            /*$3%s*/ $view_offers_link
        );
    }
    

    function column_min_price($item){

        $display_price = $item['min_price'] ? $this->number_format( $item['min_price'], 2 ) : '&mdash;';
        $form_field    = $this->get_edit_price_field( 'min_price', $item['min_price'], $item );
        $extra_css     = $item['min_price'] && $item['max_price'] && $item['min_price'] > $item['max_price'] ? 'font-weight:bold;color:red;' : ''; 

        $html  = '<div class="display_price" style="'.$extra_css.'">'.$display_price.'</div>';
        $html .= '<div class="edit_price" style="display:none">'.$form_field.'</div>';

        if ( $item['min_price'] && $msrp_price = $this->getMSRPriceForItem($item) ) {
            $percent = 100 - round( $item['min_price'] / $msrp_price * 100 );
            $html   .= '<small style="color:silver">'.$percent.'%&nbsp;off</small>';
        }

        return $html;
    }


    function column_price($item){
        
        // get prices
        $regular_price = $this->getPriceForItem( $item );

        $display_price = $regular_price ? $this->number_format( $regular_price, 2 ) : '&mdash;';
        $form_field    = $this->get_edit_price_field( 'price', $regular_price, $item );

        $html  = '<div class="display_price">'.$display_price.'</div>';
        $html .= '<div class="edit_price" style="display:none">'.$form_field.'</div>';

        if ( $regular_price && $msrp_price = $this->getMSRPriceForItem($item) ) {
            $percent = 100 - round( $regular_price / $msrp_price * 100 );
            $html   .= '<div><small style="color:silver">'.$percent.'%&nbsp;off</small></div>';
        }

        if ( $item['pnq_status'] == 1 )
            $html .= '<small style="color:silver">'.'pending'.'</small>';
        if ( $item['pnq_status'] == 2 )
            $html .= '<small style="color:silver">'.'submitted'.'</small>';
        if ( $item['pnq_status'] == -1 )
            $html .= '<small style="color:darkred">'.'failed'.'</small>';

        return $html;
    }

    function column_sale_price($item){
        
        // get prices
        $sale_price = $this->getSalePriceForItem( $item );

        $display_price = $sale_price ? $this->number_format( $sale_price, 2 ) : '&mdash;';
        $form_field    = $sale_price ? $this->get_edit_price_field( 'sale_price', $sale_price, $item ) : '';

        $html  = '<div class="display_price">'.$display_price.'</div>';
        $html .= '<div class="edit_price" style="display:none">'.$form_field.'</div>';
        return $html;
    }

    function column_ebay_price($item){
        
        // get prices
        $ebay_price = get_post_meta( $item['post_id'], '_ebay_start_price', true );

        $display_price = $ebay_price ? $this->number_format( $ebay_price, 2 ) : '&mdash;';
        $form_field    = $ebay_price ? $this->get_edit_price_field( 'ebay_price', $ebay_price, $item ) : '';

        $html  = '<div class="display_price">'.$display_price.'</div>';
        $html .= '<div class="edit_price" style="display:none">'.$form_field.'</div>';

        if ( $ebay_price && $msrp_price = $this->getMSRPriceForItem($item) ) {
            $percent = 100 - round( $ebay_price / $msrp_price * 100 );
            $html   .= '<div><small style="color:silver">'.$percent.'%&nbsp;off</small></div>';
        }

        return $html;
    }

    function column_max_price($item){

        $display_price = $item['max_price'] ? $this->number_format( $item['max_price'], 2 ) : '&mdash;';
        $form_field    = $this->get_edit_price_field( 'max_price', $item['max_price'], $item );
        $extra_css     = $item['min_price'] && $item['max_price'] && $item['min_price'] > $item['max_price'] ? 'font-weight:bold;color:red;' : ''; 

        $html  = '<div class="display_price" style="'.$extra_css.'">'.$display_price.'</div>';
        $html .= '<div class="edit_price" style="display:none">'.$form_field.'</div>';

        if ( $item['max_price'] && $msrp_price = $this->getMSRPriceForItem($item) ) {
            $percent = 100 - round( $item['max_price'] / $msrp_price * 100 );
            $html   .= '<small style="color:silver">'.$percent.'%&nbsp;off</small>';
        }

        return $html;
    }
    
    function get_edit_price_field( $col, $price, $item ){

        $price = is_numeric( $price ) ? number_format( $price, 2, '.', '' ) : $price;

        $form_field  = '<input type="text" ';
        $form_field .= 'value="'.$price.'" ';
        $form_field .= 'data-id="'.$item['id'].'" ';
        $form_field .= 'data-col="'.$col.'" ';
        $form_field .= 'tabindex="1" ';
        $form_field .= 'style="width:100%"/>';

        return $form_field;
    }  

    function column_fba_fee_ltsf_12($item){

        $fba_fee_ltsf_12 = $item['fba_fee_ltsf_12'] ? $this->number_format( $item['fba_fee_ltsf_12'], 2 ) : '&mdash;';

        return sprintf('%1$s<br><small style="color:silver">%2$s</small>',
            /*$2%s*/ $fba_fee_ltsf_12,
            /*$3%s*/ $item['fba_qty_ltsf_12']
        );
    }
    
    
    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'        		=> '<input type="checkbox" />', //Render a checkbox instead of text
            // 'img'               => __('Image','wpla'),
            'sku'               => __('SKU','wpla'),
            'listing_title' 	=> __('Title','wpla'),
            'msrp'              => __('MSRP','wpla'),
            'price'             => __('Price','wpla'),
            'sale_price'        => __('Sale Price','wpla'),
            'ebay_price'        => __('eBay Price','wpla'),
            'min_price'         => __('Min. Price','wpla'),
            'max_price'         => __('Max. Price','wpla'),
            'lowest_price'		=> str_replace(' ', '&nbsp;', __('Buy Box','wpla') ),
            'loffer_price'      => __('Lowest Offer','wpla'),
            'compet_price'      => __('Other Sellers','wpla'),
            'quantity'          => __('Stock','wpla'),
            'fba_inv_age_90'       => 'Age<br>0-90',
            'fba_inv_age_180'      => '91-180',
            'fba_inv_age_270'      => '181-270',
            'fba_inv_age_365'      => '271-365',
            'fba_inv_age_365_plus' => '365+',
            'fba_fee_ltsf_12'      => __('Est. LTSF','wpla'),
            // 'date_published'    => str_replace(' ', '&nbsp;', __('Created at','wpla') ),
            // 'profile'           => __('Profile','wpla'),
            // 'account'           => __('Account','wpla'),
            'status'		 	=> __('Status','wpla')
        );

        if ( ! get_option( 'wpla_enable_thumbs_column' ) )
            unset( $columns['img'] );

        if ( ! isset($_REQUEST['fba_status']) || $_REQUEST['fba_status'] != 'is_fba' ) {
            unset( $columns['fba_inv_age_90'] );
            unset( $columns['fba_inv_age_180'] );
            unset( $columns['fba_inv_age_270'] );
            unset( $columns['fba_inv_age_365'] );
            unset( $columns['fba_inv_age_365_plus'] );
            unset( $columns['fba_fee_ltsf_12'] );
        }

        if ( ! defined('WPLE_VERSION') )
            unset( $columns['ebay_price'] );

        return $columns;
    }
    
    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'date_published'  	=> array('date_published',false),     //true means its already sorted
            'listing_title'     => array('listing_title',false),
            'status'            => array('status',false),
            'fba_inv_age_90'       => array('fba_inv_age_90',false),
            'fba_inv_age_180'      => array('fba_inv_age_180',false),
            'fba_inv_age_270'      => array('fba_inv_age_270',false),
            'fba_inv_age_365'      => array('fba_inv_age_365',false),
            'fba_inv_age_365_plus' => array('fba_inv_age_365_plus',false),
            'fba_fee_ltsf_12'      => array('fba_fee_ltsf_12',false),
            'quantity'             => array('quantity',false),
            'price'                => array('price',false),
        );

        if ( ! isset($_REQUEST['fba_status']) ) {
            unset( $sortable_columns['quantity'] );
        }

        return $sortable_columns;
    }
    
    
    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'minmax_price_wiz'              => __('Set min./max. prices','wpla'),
            'wpla_get_compet_price'         => __('Fetch latest prices from Amazon','wpla'),
            'wpla_bulk_apply_lowest_prices' => __('Apply lowest price to selected items','wpla'),
            'wpla_resubmit_pnq_update'      => __('Resubmit price update for selected items','wpla'),
        );
        return $actions;
    }
    
   
    // status filter links
    // http://wordpress.stackexchange.com/questions/56883/how-do-i-create-links-at-the-top-of-wp-list-table
    function get_views(){
        $views    = array();
        $current_repricing_status = ( ! empty($_REQUEST['repricing_status'] ) ? $_REQUEST['repricing_status'] : 'all');
        $current_buybox_status    = ( ! empty($_REQUEST['buybox_status']    ) ? $_REQUEST['buybox_status']    : 'all');
        $current_stock_status     = ( ! empty($_REQUEST['stock_status']     ) ? $_REQUEST['stock_status']     : 'all');
        $current_fba_status       = ( ! empty($_REQUEST['fba_status']       ) ? $_REQUEST['fba_status']       : 'all');
        $current_fba_age          = ( ! empty($_REQUEST['fba_age']          ) ? $_REQUEST['fba_age']          : 'all');
        // $base_url = remove_query_arg( array( 'action', 'listing', 'repricing_status' ) );
        $base_url = esc_url_raw( remove_query_arg( array( 'action', 'listing' ) ) );

        // handle search query
        if ( isset($_REQUEST['s']) && $_REQUEST['s'] ) {
            $base_url = add_query_arg( 's', $_REQUEST['s'], $base_url );
        }
        // handle profile_id query
        if ( isset($_REQUEST['profile_id']) && $_REQUEST['profile_id'] ) {
            $base_url = add_query_arg( 'profile_id', $_REQUEST['profile_id'], $base_url );
        }
        // handle account_id query
        if ( isset($_REQUEST['account_id']) && $_REQUEST['account_id'] ) {
            $base_url = add_query_arg( 'account_id', $_REQUEST['account_id'], $base_url );
        }


        // get listing status summary
        $summary = WPLA_ListingsModel::getRepricingStatusSummary();

        // All link
        $class = ($current_repricing_status == 'all' && $current_buybox_status == 'all' ? ' class="current"' :'');
        $all_url = remove_query_arg( array('repricing_status','buybox_status'), $base_url );
        $views['all']  = "<a href='{$all_url }' {$class} >".__('All','wpla')."</a>";
        $views['all'] .= '<span class="count">('.$summary->total_items.')</span>';


        // Has BuyBox
        $has_buybox_url = add_query_arg( 'buybox_status', 'has_buybox', $base_url );
        $class = ($current_buybox_status == 'has_buybox' ? ' class="current"' :'');
        $views['has_buybox'] = "<a href='{$has_buybox_url}' {$class} >".__('BuyBox','wpla')."</a>";
        if ( isset($summary->has_buybox) ) $views['has_buybox'] .= '<span class="count">('.$summary->has_buybox.')</span>';

        // No BuyBox
        $no_buybox_url = add_query_arg( 'buybox_status', 'no_buybox', $base_url );
        $class = ($current_buybox_status == 'no_buybox' ? ' class="current"' :'');
        $views['no_buybox'] = "<a href='{$no_buybox_url}' {$class} >".__('No BuyBox','wpla')."</a>";
        if ( isset($summary->no_buybox) ) $views['no_buybox'] .= '<span class="count">('.$summary->no_buybox.')</span>';


        // is_lowest_price link
        $is_lowest_price_url = add_query_arg( 'repricing_status', 'is_lowest_price', $base_url );
        $class = ($current_repricing_status == 'is_lowest_price' ? ' class="current"' :'');
        $views['is_lowest_price'] = "<a href='{$is_lowest_price_url}' {$class} >".__('Lowest Price','wpla')."</a>";
        if ( isset($summary->is_lowest_price) ) $views['is_lowest_price'] .= '<span class="count">('.$summary->is_lowest_price.')</span>';

        // is_not_lowest_price link
        $is_not_lowest_price_url = add_query_arg( 'repricing_status', 'is_not_lowest_price', $base_url );
        $class = ($current_repricing_status == 'is_not_lowest_price' ? ' class="current"' :'');
        $views['is_not_lowest_price'] = "<a href='{$is_not_lowest_price_url}' {$class} >".__('Not Lowest Price','wpla')."</a>";
        if ( isset($summary->is_not_lowest_price) ) $views['is_not_lowest_price'] .= '<span class="count">('.$summary->is_not_lowest_price.')</span>';

        // no_price_range link
        $no_price_range_url = add_query_arg( 'repricing_status', 'no_price_range', $base_url );
        $class = ($current_repricing_status == 'no_price_range' ? ' class="current"' :'');
        $views['no_price_range'] = "<a href='{$no_price_range_url}' {$class} >".__('No Min. Price','wpla')."</a>";
        if ( isset($summary->no_price_range) ) $views['no_price_range'] .= '<span class="count">('.$summary->no_price_range.')</span>';

        // pnq_in_process link
        $pnq_in_process_url = add_query_arg( 'repricing_status', 'pnq_in_process', $base_url );
        $class = ($current_repricing_status == 'pnq_in_process' ? ' class="current"' :'');
        $views['pnq_in_process'] = "<a href='{$pnq_in_process_url}' {$class} >".__('In Progress','wpla')."</a>";
        if ( isset($summary->pnq_in_process) ) $views['pnq_in_process'] .= '<span class="count">('.$summary->pnq_in_process.')</span>';

        // pnq_failed link
        $pnq_failed_url = add_query_arg( 'repricing_status', 'pnq_failed', $base_url );
        $class = ($current_repricing_status == 'pnq_failed' ? ' class="current"' :'');
        $views['pnq_failed'] = "<a href='{$pnq_failed_url}' {$class} >".__('Failed','wpla')."</a>";
        if ( isset($summary->pnq_failed) ) $views['pnq_failed'] .= '<span class="count">('.$summary->pnq_failed.')</span>';


        // In Stock
        $is_in_stock_url = add_query_arg( 'stock_status', 'is_in_stock', $base_url );
        $class = ($current_stock_status == 'is_in_stock' ? ' class="current"' :'');
        $views['is_in_stock'] = "<a href='{$is_in_stock_url}' {$class} >".__('In Stock','wpla')."</a>";

        // No Stock
        $is_not_in_stock_url = add_query_arg( 'stock_status', 'is_not_in_stock', $base_url );
        $class = ($current_stock_status == 'is_not_in_stock' ? ' class="current"' :'');
        $views['is_not_in_stock'] = "<a href='{$is_not_in_stock_url}' {$class} >".__('No Stock','wpla')."</a>";


        if ( ! get_option( 'wpla_fba_enabled' ) )
            return $views;


        // FBA
        $is_fba_url = add_query_arg( 'fba_status', 'is_fba', $base_url );
        $class = ($current_fba_status == 'is_fba' ? ' class="current"' :'');
        $views['is_fba'] = "<a href='{$is_fba_url}' {$class} >".__('FBA','wpla')."</a>";

        // Non-FBA
        $is_not_fba_url = add_query_arg( 'fba_status', 'is_not_fba', $base_url );
        $class = ($current_fba_status == 'is_not_fba' ? ' class="current"' :'');
        $views['is_not_fba'] = "<a href='{$is_not_fba_url}' {$class} >".__('Non-FBA','wpla')."</a>";


        if ( ! isset($_REQUEST['fba_status']) || $_REQUEST['fba_status'] != 'is_fba' )
            return $views;


        // FBA Age 0-90
        $fba_age_url = add_query_arg( 'fba_age', 'age_90', $base_url );
        $class = ($current_fba_age == 'age_90' ? ' class="current"' :'');
        $views['age_90'] = "<a href='{$fba_age_url}' {$class} >".__('Age 0-90','wpla')."</a>";

        // FBA Age 91-180
        $fba_age_url = add_query_arg( 'fba_age', 'age_180', $base_url );
        $class = ($current_fba_age == 'age_180' ? ' class="current"' :'');
        $views['age_180'] = "<a href='{$fba_age_url}' {$class} >".__('91-180','wpla')."</a>";

        // FBA Age 181-270
        $fba_age_url = add_query_arg( 'fba_age', 'age_270', $base_url );
        $class = ($current_fba_age == 'age_270' ? ' class="current"' :'');
        $views['age_270'] = "<a href='{$fba_age_url}' {$class} >".__('181-270','wpla')."</a>";

        // FBA Age 271-365
        $fba_age_url = add_query_arg( 'fba_age', 'age_365', $base_url );
        $class = ($current_fba_age == 'age_365' ? ' class="current"' :'');
        $views['age_365'] = "<a href='{$fba_age_url}' {$class} >".__('271-365','wpla')."</a>";

        // FBA Age 365+
        $fba_age_url = add_query_arg( 'fba_age', 'age_365_plus', $base_url );
        $class = ($current_fba_age == 'age_365_plus' ? ' class="current"' :'');
        $views['age_365_plus'] = "<a href='{$fba_age_url}' {$class} >".__('365+','wpla')."</a>";


        return $views;
    }    

    function extra_tablenav( $which ) {
        if ( 'top' != $which ) return;

        $base_url = esc_url_raw( remove_query_arg( array( 'action' ) ) );
        if ( isset( $_REQUEST['s'] ) )                  $base_url = add_query_arg( 's'               , $_REQUEST['s']               , $base_url );
        if ( isset( $_REQUEST['repricing_status'] ) )   $base_url = add_query_arg( 'repricing_status', $_REQUEST['repricing_status'], $base_url );
        if ( isset( $_REQUEST['buybox_status'] ) )      $base_url = add_query_arg( 'buybox_status'   , $_REQUEST['buybox_status']   , $base_url );
        if ( isset( $_REQUEST['stock_status'] ) )       $base_url = add_query_arg( 'stock_status'    , $_REQUEST['stock_status']    , $base_url );
        if ( isset( $_REQUEST['fba_status'] ) )         $base_url = add_query_arg( 'fba_status'      , $_REQUEST['fba_status']      , $base_url );
        if ( isset( $_REQUEST['fba_age'] ) )            $base_url = add_query_arg( 'fba_age'         , $_REQUEST['fba_age']         , $base_url );

        $btn_url  = add_query_arg( array(
            'action'    => 'wpla_apply_lowest_price_to_all_items',
            '_wpnonce'  => wp_create_nonce( 'wpla_apply_lowest_price_to_all_items' )
        ), $base_url );
        ?>
        <div class="alignleft actions" style="">

            <a href="<?php echo $btn_url ?>" class="button" style="display: inline-block; margin: 2px 12px 0 0;"><?php echo __('Apply lowest price to all items','wpla') ?></a>

            <a href="#" id="btn_toggle_price_editor" class="button" style="display: inline-block; margin: 2px 12px 0 0;"><?php echo __('Edit Prices','wpla') ?></a>

        </div>
        <?php
    }

    
    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items( $items = false ) {
        
        // process bulk actions
        $this->process_bulk_action();
                        
        // get pagination state
        $current_page = $this->get_pagenum();
        $per_page = $this->get_items_per_page('listings_per_page', 20);
        
        // define columns
        $this->_column_headers = $this->get_column_info();
        
        // fetch listings from model - if no parameter passed
        if ( ! $items ) {

            $listingsModel = new WPLA_ListingsModel();
            $this->items = $listingsModel->getPageItems( $current_page, $per_page, 'repricing' );
            $this->total_items = $listingsModel->total_items;

        } else {

            $this->items = $items;
            $this->total_items = count($items);

        }

        // register our pagination options & calculations.
        $this->set_pagination_args( array(
            'total_items' => $this->total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($this->total_items/$per_page)
        ) );

    }

   
    
}

