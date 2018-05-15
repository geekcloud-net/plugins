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
class WPLA_SkuGenTable extends WP_List_Table {

    var $last_product_id         = 0;
    var $last_product_object     = array();
    var $total_items;

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'product',     //singular name of the listed records
            'plural'    => 'products',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
        // get array of profile names
        // $this->profiles  = WPLA_AmazonProfile::getAllNames();
        // $this->templates = WPLA_AmazonProfile::getAllTemplateNames();
    }
    
    
    function column_default($item, $column_name){
        switch($column_name){
            // case 'profile':
            //     return isset($item['profile_id']) ? $this->profiles[ $item['profile_id'] ] : '';
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_title($item){

        $title = ( $item['parent_id'] ) ? WPLA_ProductWrapper::getProductTitle( $item['parent_id'] ) : $item['title'];

        // make title link to products edit page
        if ( $item['id'] ) {
            $post_id = $item['parent_id'] ? $item['parent_id'] : $item['id'];
            $title = '<a class="product_title_link" href="post.php?post='.$post_id.'&action=edit">'.$title.'</a>';
        }

        return $title;
    }    

    function column_sku($item){
        return $item['sku'];
    }    

    function column_sku_preview($item){

        $new_sku = WPLA_SkuGenerator::generateNewSKU( $item['id'] );
        $color = '';
        if ( $item['sku'] != '' ) $color = 'silver';

        return sprintf('<span style="color:%1$s">%2$s</span>',
            /*$1%s*/ $color,
            /*$2%s*/ $new_sku
        );
    }    

    function column_status($item){
        return $item['status'];
    }    
        
    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (profile title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("product")
            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
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
            'title' 	        => __('Product Title','wpla'),
            'sku'               => __('Current SKU','wpla'),
            'sku_preview'       => __('New SKU','wpla'),
            // 'price'             => __('Price','wpla'),
            // 'quantity'          => __('Quantity','wpla'),
            // 'profile'           => __('Profile','wpla'),
            // 'account'           => __('Account','wpla'),
            'status'		 	=> __('Listing Status','wpla')
        );

        if ( ! get_option( 'wpla_enable_thumbs_column' ) )
            unset( $columns['img'] );

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
            // 'date_published'  	=> array('date_published',false),     //true means its already sorted
            // 'listing_title'     => array('listing_title',false),
            // 'status'            => array('status',false)
        );
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
            'wpla_bulk_generate_skus' => __('Generate new SKUs','wpla'),
            // 'resubmit'             => __('Resubmit items','wpla'),
        );
        return $actions;
    }
    
   
    // status filter links
    // http://wordpress.stackexchange.com/questions/56883/how-do-i-create-links-at-the-top-of-wp-list-table
    function get_views(){
        $views    = array();
        $current  = ( !empty($_REQUEST['sku_status']) ? $_REQUEST['sku_status'] : 'all');
        $base_url = esc_url_raw( remove_query_arg( array( 'action', 'listing', 'sku_status' ) ) );

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
        $helper = new WPLA_SkuGenerator();
        $summary = $helper->getSkuGenStatusSummary();

        // All link
        $class = ($current == 'all' ? ' class="current"' :'');
        $all_url = remove_query_arg( 'sku_status', $base_url );
        $views['all']  = "<a href='{$all_url }' {$class} >".__('All','wpla')."</a>";
        $views['all'] .= '<span class="count">('.$summary->total_items.')</span>';

        // missing_sku link
        $missing_sku_url = add_query_arg( 'sku_status', 'missing_sku', $base_url );
        $class = ($current == 'missing_sku' ? ' class="current"' :'');
        $views['missing_sku'] = "<a href='{$missing_sku_url}' {$class} >".__('Missing SKU','wpla')."</a>";
        if ( isset($summary->missing_sku) ) $views['missing_sku'] .= '<span class="count">('.$summary->missing_sku.')</span>';

        // long_sku link
        $long_sku_url = add_query_arg( 'sku_status', 'long_sku', $base_url );
        $class = ($current == 'long_sku' ? ' class="current"' :'');
        $views['long_sku'] = "<a href='{$long_sku_url}' {$class} >".__('Long SKU','wpla')."</a>";
        if ( isset($summary->long_sku) ) $views['long_sku'] .= '<span class="count">('.$summary->long_sku.')</span>';

        return $views;
    }    

    function extra_tablenav( $which ) {
        if ( 'top' != $which ) return;
        $base_url = esc_url_raw( remove_query_arg( array( 'action' ) ) );
        $btn_url  = add_query_arg( array(
            'action'    => 'wpla_generate_all_missing_skus',
            '_wpnonce'  => wp_create_nonce( 'wpla_generate_all_missing_skus' )
        ), $base_url );
        ?>
        <div class="alignleft actions" style="">

            <a href="<?php echo $btn_url ?>" id="btn_generate_all_missing_skus" class="button"><?php echo __('Generate all missing SKUs','wpla') ?></a>

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
        // $this->process_bulk_action();
                        
        // get pagination state
        $current_page = $this->get_pagenum();
        $per_page = $this->get_items_per_page('listings_per_page', 20);
        
        // define columns
        $this->_column_headers = $this->get_column_info();
        
        // fetch items
        $helper            = new WPLA_SkuGenerator();
        $this->items       = $helper->getPageItems( $current_page, $per_page );
        $this->total_items = $helper->total_items;


        // register our pagination options & calculations.
        $this->set_pagination_args( array(
            'total_items' => $this->total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($this->total_items/$per_page)
        ) );

    }

   
    
}

