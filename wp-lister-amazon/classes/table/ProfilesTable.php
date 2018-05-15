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
class WPLA_ProfilesTable extends WP_List_Table {

    var $total_items;

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'amazon_profile',     //singular name of the listed records
            'plural'    => 'profiles',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }
    
    function no_items() {
        _e( 'No profiles found.','wpla' );
    }
    
    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
            case 'status':
            case 'template':
            case 'account_id':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    
        
    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (profile title only)
     **************************************************************************/
    function column_profile_name($item){
        
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&profile=%s">%s</a>',$_REQUEST['page'],'edit',$item['profile_id'],__('Edit','wpla')),
            'duplicate' => sprintf('<a href="?page=%s&action=%s&profile=%s&_wpnonce=%s">%s</a>',$_REQUEST['page'],'wpla_duplicate_profile',$item['profile_id'], wp_create_nonce( 'wpla_duplicate_profile' ), __('Duplicate','wpla')),
            'download'  => sprintf('<a href="?page=%s&action=%s&profile=%s&_wpnonce=%s">%s</a>',$_REQUEST['page'],'wpla_download_listing_profile',$item['profile_id'], wp_create_nonce( 'wpla_download_listing_profile' ), __('Download','wpla')),
            'delete'    => sprintf('<a href="?page=%s&action=%s&amazon_profile=%s&_wpnonce=%s">%s</a>',$_REQUEST['page'],'wpla_delete_profile',$item['profile_id'], wp_create_nonce( 'bulk-profiles' ), __('Delete','wpla')),
        );

        // make title link to edit page
        $title = sprintf('<a href="?page=%s&action=%s&profile=%s" class="title_link">%s</a>', $_REQUEST['page'], 'edit', $item['profile_id'], $item['profile_name'] );
        
        //Return the title contents
        return sprintf('%1$s <br><span style="color:silver">%2$s</span>%3$s',
            /*$1%s*/ $title,
            /*$2%s*/ $item['profile_description'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }

    function column_template($item){

        $fields         = maybe_unserialize( $item['fields'] );
        $template       = WPLA_AmazonFeedTemplate::getFeedTemplate( $item['tpl_id'] );
        $template_title = $template ? $template->title : '<i>'.__('no feed template selected','wpla').'</i>';
        if ( $template_title == 'Offer' ) $template_title = 'Listing Loader';

        // show warning if template is meant for a different marketplace than this profile's account is linked to #20604
        $account = WPLA()->memcache->getAccount( $item['account_id'] );
        if ( $account->market_id != $template->site_id ) {
            $template_title .= '<br><small style="color:darkred">'.__('Warning: This listing template can not be used on Amazon','wpla').' '.$account->market_code.'</small>';
        }

        return sprintf('%1$s <br><span style="color:silver">%2$s</span>',
            /*$1%s*/ $template_title,
            /*$2%s*/ isset( $fields['feed_product_type'] ) ? wpla_spacify( $fields['feed_product_type'] ) : ''
        );
    }

    function column_account($item){
        $account = WPLA()->memcache->getAccount( $item['account_id'] );
        return $account->title . ' (' . $account->market_code. ')';
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
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("profile")
            /*$2%s*/ $item['profile_id']       			//The value of the checkbox should be the record's id
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
            'profile_name'      => __('Profile','wpla'),
            'template'          => __('Template','wpla'),
            'account'           => __('Account','wpla')
        );
        return $columns;
    }
    
    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'profile_name'  	=> array('profile_name',false),     //true means its already sorted
            // 'LastTimeModified' 	=> array('LastTimeModified',false)
        );
        return $sortable_columns;
    }
    
    
    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'wpla_delete_profile' => __('Delete selected profiles','wpla')
        );
        return $actions;
    }
    
    
    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
        global $wbdb;
        
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            #wp_die('Items deleted (or they would be if we had items to delete)!');
            #$wpdb->query("DELETE FROM {$wpdb->prefix}amazon_profiles WHERE id = ''",)
        }
        
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
    function prepare_items() {
        
        // process bulk actions
        $this->process_bulk_action();
                        
        // get pagination state
        $current_page = $this->get_pagenum();
        $per_page = $this->get_items_per_page('profiles_per_page', 20);
        
        // define columns
        $this->_column_headers = $this->get_column_info();
        
        // fetch profiles from model
        $profilesModel = new WPLA_AmazonProfile();
        $this->items = $profilesModel->getPageItems( $current_page, $per_page );
        $total_items = $profilesModel->total_items;

        // register our pagination options & calculations.
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items/$per_page)
        ) );

    }
    
}

