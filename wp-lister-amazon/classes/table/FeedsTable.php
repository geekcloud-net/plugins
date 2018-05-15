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
class WPLA_FeedsTable extends WP_List_Table {

    var $total_items;

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'amazon_feed',     //singular name of the listed records
            'plural'    => 'feeds',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
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
            case 'FeedSubmissionId':
            case 'FeedType':
            case 'FeedProcessingStatus':
            case 'SubmittedDate':
            case 'StartedProcessingDate':
            case 'CompletedProcessingDate':
            case 'status':
                return $item[$column_name];
            case 'total':
                return number_format( $item[$column_name], 2, ',', '.' );
            case 'date_created':
            case 'LastTimeModified':
            	// use date format from wp
                $date = mysql2date( get_option('date_format'), $item[$column_name] );
                $time = mysql2date( 'H:i', $item[$column_name] );
                return sprintf('%1$s <br><span style="color:silver">%2$s</span>', $date, $time );
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
    function column_details($item){
        
        //Build row actions
        $signature = md5( $item['id'] . get_option('wpla_instance') );
        $actions = array(
            'view_amazon_feed_details' => sprintf( '<a href="admin-ajax.php?action=%s&id=%s&sig=%s&_wpnonce=%s" target="_blank">%s</a>', 'wpla_feed_details', $item['id'], $signature, wp_create_nonce( 'wpla_view_feed_details' ), __('Details','wpla') ),
            // 'view_amazon_feed_details' => sprintf('<a href="?page=%s&action=%s&amazon_feed=%s&width=600&height=470&TB_iframe=true" class="thickbox">%s</a>',$_REQUEST['page'],'view_amazon_feed_details',$item['id'],__('Details','wpla')),
            // 'view_amazon_feed_details' => sprintf('<a href="?page=%s&action=%s&amazon_feed=%s&width=600&height=470" class="thickbox">%s</a>',$_REQUEST['page'],'view_amazon_feed_details',$item['id'],__('Details','wpla')),
            'view_amazon_feed_results' => sprintf( '<a href="?page=%s&action=%s&amazon_feed=%s&_wpnonce=%s&width=600&height=470" class="thickbox">%s</a>', $_REQUEST['page'], 'view_amazon_feed_results', $item['id'], wp_create_nonce( 'wpla_view_feed_results' ), __('View Results','wpla') ),
            // 'view_amazon_feed_details' => sprintf('<a href="?page=%s&action=%s&amazon_feed=%s" target="_blank">%s</a>',$_REQUEST['page'],'view_amazon_feed_details',$item['id'],__('Details','wpla')),
            'submit_feed_to_amazon'    => sprintf( '<a href="?page=%s&action=%s&amazon_feed=%s&_wpnonce=%s">%s</a>', $_REQUEST['page'], 'submit_feed_to_amazon', $item['id'], wp_create_nonce( 'wpla_submit_feed' ), __('Submit','wpla') ),
            // 'check_feed_on_amazon'     => sprintf('<a href="?page=%s&action=%s&amazon_feed=%s">%s</a>',$_REQUEST['page'],'check_feed_on_amazon',$item['id'],__('Check','wpla')),
            'process_amazon_feed_results'      => sprintf( '<a href="?page=%s&action=%s&amazon_feed=%s&_wpnonce=%s">%s</a>', $_REQUEST['page'], 'process_amazon_feed_results', $item['id'], wp_create_nonce( 'wpla_process_feed_results' ), __('Process Results','wpla') ),
            'process_amazon_feed_again'        => sprintf( '<a href="?page=%s&action=%s&amazon_feed=%s&_wpnonce=%s">%s</a>', $_REQUEST['page'], 'process_amazon_feed_results', $item['id'], wp_create_nonce( 'wpla_process_feed_results' ), __('Process again','wpla') ),
            // 'edit'         => sprintf('<a href="?page=%s&action=%s&auction=%s">%s</a>',$_REQUEST['page'],'edit',$item['id'],__('Edit','wpla')),
        );

        // item title
        $title = $item['FeedTypeName'];
        if ( ! $item['line_count'] && ! $item['data'] ) {
            $title = ' <i style="color:silver">'.$title.'</i>';
            unset( $actions['view_amazon_feed_details'] );
            unset( $actions['process_amazon_feed_again'] );
        }
        if ( $item['template_name'] ) {
            $title .= ' <i style="color:silver">'.$item['template_name'].'</i>';
        }

        // if ( ! $item['GeneratedFeedId'] ) unset( $actions['submit_feed_to_amazon'] );
        // if (   $item['status'] == 'processed')  unset( $actions['process_amazon_feed_results'] );
        if ( ! $item['results'] )                               unset( $actions['process_amazon_feed_results'] );
        if ( ! $item['results'] )                               unset( $actions['process_amazon_feed_again'] );
        if ( ! $item['results'] )                               unset( $actions['view_amazon_feed_results'] );
        if (   $item['status'] != 'pending')                    unset( $actions['submit_feed_to_amazon'] );
        // if (   $item['status'] != 'pending')                    unset( $actions['check_feed_on_amazon'] );
        // if ( substr( $item['FeedType'], 0, 7 ) == '_CHECK_')    unset( $actions['check_feed_on_amazon'] );

        if (   $item['status'] == 'processed')                  unset( $actions['process_amazon_feed_results'] );
        if (   $item['status'] != 'processed')                  unset( $actions['process_amazon_feed_again'] );


        //Return the title contents
        return sprintf('%1$s %2$s %3$s',
            /*$1%s*/ $title,
            /*$2%s*/ $this->row_actions($actions),
            /*$2%s*/ $this->display_unspecific_feed_errors($item)
        );
    }

    function display_unspecific_feed_errors( $item ) {
        if ( $item['FeedProcessingStatus'] != '_DONE_' ) return;
        if ( ! $item['results'] ) return;

        // extract result csv data
        $result_content = implode("\n", array_slice(explode("\n", $item['results']), 4)); // remove summary rows
        // echo "<pre>";print_r($result_content);echo"</pre>";#die();
        $result_rows = WPLA_ReportProcessor::csv_to_array( $result_content );

        $errors = array();
        foreach ($result_rows as $row) {
            
            // skip specific errors - these are processed and shown on products and orders already
            if ( isset($row['sku']) && ! empty($row['sku']) ) continue;
            if ( isset($row['order-id']) && ! empty($row['order-id']) ) continue;

            // skip general info (90000) and invalid column header error (90061) for now...
            if ( ! isset($row['error-code']) || ( $row['error-code'] == 90000 ) ) continue;
            if ( ! isset($row['error-code']) || ( $row['error-code'] == 90061 ) ) continue;

            $errors[] = $row;
        }
        if ( empty($errors) ) return;

        // display errors
        $errors_html = '';
        foreach ($errors as $error) {
            $errors_html .= '<b>'.$error['error-type'].':</b> '.$error['error-message'].' ('.$error['error-code'].')<br>';
        }
        $errors_html = '<!br><small style="color:darkred">'.$errors_html.'</small>';

        // echo "<pre>";print_r($result_rows);echo"</pre>";#die();
        // echo "<pre>";print_r($errors);echo"</pre>";#die();
        return $errors_html;
    }

    function column_buyer_name($item){
        //Return buyer name and ID
        return sprintf('%1$s <br><span style="color:silver">%2$s</span>',
            /*$1%s*/ $item['buyer_name'],
            /*$2%s*/ $item['buyer_userid']
        );
    }

    function column_FeedSubmissionId($item){
        return $item['FeedSubmissionId'];
    }

    function column_account_id($item){
        $account_id = $item['account_id'];
        if ( $account = WPLA()->memcache->getAccount( $account_id ) )
            return $account->title;
        // return $item['account_id'];
    }

    function column_SubmittedDate($item){
        // $SubmittedDate = $item['SubmittedDate'] != '0000-00-00 00:00:00' ? $item['SubmittedDate'] : '';
        if ( ! $item['SubmittedDate'] ) return;
        return sprintf('%1$s <br><span style="color:silver">%2$s</span>',
            /*$1%s*/ $item['SubmittedDate'],
            /*$2%s*/ sprintf( __('%s ago','wpla'), human_time_diff( strtotime( $item['SubmittedDate'].' UTC' ) ) )
        );
    }

    function column_CompletedProcessingDate($item){
        if ( $item['CompletedProcessingDate'] == '0000-00-00 00:00:00' ) return;
        if ( ! $item['CompletedProcessingDate'] ) return;

        return sprintf('%1$s <br><span style="color:silver">%2$s</span>',
            /*$1%s*/ $item['CompletedProcessingDate'],
            /*$2%s*/ 'processed in ' . human_time_diff( strtotime($item['SubmittedDate']), strtotime($item['CompletedProcessingDate']) ) . ''
        );
    }

    function column_FeedType($item){
        return $item['FeedTypeName'];
    }

    function column_status($item){

        switch( $item['FeedProcessingStatus'] ){
            case '_SUBMITTED_':
                $color = 'darkorange';
                $value = __('Submitted','wpla');
				break;
            case '_IN_PROGRESS_':
                $color = 'darkorange';
                $value = __('In progress','wpla');
                break;
            case '_DONE_':
                $color = 'green';
                $value = __('Done','wpla');
                break;
            case '_CANCELLED_':
                $color = 'gray';
                $value = __('Cancelled','wpla');
                break;
            case '_DONE_NO_DATA_':
                $color = 'gray';
                $value = __('No results','wpla');
                break;
            case 'pending':
                $color = 'purple';
                $value = __('Pending','wpla');
				break;
            default:
                $color = 'black';
                $value = $item['FeedProcessingStatus'] ? $item['FeedProcessingStatus'] : $item['status'] ;
        }

        $line_count = $item['line_count'];
        if ( $line_count ) {
            $line_count = ($line_count) . ' rows';
            $line_count .= ' / ';
            $line_count .= strlen($item['data']) > 1000 ? round(strlen( $item['data'] )/1024).' kb' : strlen( $item['data'] ) . ' bytes';
        }

        $extra = '';
        $success = $item['success'];
        if ( in_array($success, array('error','warning') ) ) {
            $extra .= ' with '.$success.'s';
        }


        //Return the title contents
        return sprintf('<span style="color:%1$s">%2$s</span>%3$s<br><span style="color:silver">%4$s</span>',
            /*$1%s*/ $color,
            /*$2%s*/ $value,
            /*$2%s*/ $extra,
            /*$2%s*/ $line_count
        );
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
            /*$2%s*/ $item['id']       			//The value of the checkbox should be the record's id
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
            'FeedSubmissionId'   => __('Batch ID','wpla'),
            // 'FeedType'        => __('FeedType','wpla'),
            'details'           => __('Type','wpla'),
            'SubmittedDate'     => __('Submitted at','wpla'),
            'CompletedProcessingDate'	    => __('Completed at','wpla'),
            'status'            => __('Status','wpla'),
            'account_id'        => __('Account','wpla')
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
            'date_created'  	=> array('date_created',false),     //true means its already sorted
            'LastTimeModified' 	=> array('LastTimeModified',false)
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
            'update_amazon_feed' => __('Update feed status from Amazon','wpla'),
            'cancel_amazon_feed' => __('Cancel submitted feeds','wpla'),
            'delete_amazon_feed' => __('Delete selected feeds','wpla')
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
            #$wpdb->query("DELETE FROM {$wpdb->prefix}amazon_auctions WHERE id = ''",)
        }

        if( 'update'===$this->current_action() ) {
			#echo "<br>verify handler<br>";			
        }
        
    }


    // status filter links
    // http://wordpress.stackexchange.com/questions/56883/how-do-i-create-links-at-the-top-of-wp-list-table
    function get_views(){
       $views    = array();
       $current  = ( !empty($_REQUEST['feed_status']) ? $_REQUEST['feed_status'] : 'all');
       $base_url = esc_url_raw( remove_query_arg( array( 'action', 'feed', 'feed_status' ) ) );

       // get feed status summary
       $summary = WPLA_AmazonFeed::getStatusSummary();

       // All link
       $class = ($current == 'all' ? ' class="current"' :'');
       $all_url = remove_query_arg( 'feed_status', $base_url );
       $views['all']  = "<a href='{$all_url }' {$class} >".__('All','wpla')."</a>";
       $views['all'] .= '<span class="count">('.$summary->total_items.')</span>';

       // processed link
       $processed_url = add_query_arg( 'feed_status', 'processed', $base_url );
       $class = ($current == 'processed' ? ' class="current"' :'');
       $views['processed'] = "<a href='{$processed_url}' {$class} >".__('Processed','wpla')."</a>";
       if ( isset($summary->processed) ) $views['processed'] .= '<span class="count">('.$summary->processed.')</span>';

       // pending link
       $pending_url = add_query_arg( 'feed_status', 'pending', $base_url );
       $class = ($current == 'pending' ? ' class="current"' :'');
       $views['pending'] = "<a href='{$pending_url}' {$class} >".__('Pending','wpla')."</a>";
       if ( isset($summary->pending) ) $views['pending'] .= '<span class="count">('.$summary->pending.')</span>';

       // submitted link
       $submitted_url = add_query_arg( 'feed_status', 'submitted', $base_url );
       $class = ($current == 'submitted' ? ' class="current"' :'');
       $views['submitted'] = "<a href='{$submitted_url}' {$class} >".__('Submitted','wpla')."</a>";
       if ( isset($summary->submitted) ) $views['submitted'] .= '<span class="count">('.$summary->submitted.')</span>';

       // unknown link
       if ( isset($summary->unknown) ) {
           $unknown_url = add_query_arg( 'feed_status', 'unknown', $base_url );
           $class = ($current == 'unknown' ? ' class="current"' :'');
           $views['unknown'] = "<a href='{$unknown_url}' {$class} >".__('Unknown','wpla')."</a>";
           $views['unknown'] .= '<span class="count">('.$summary->unknown.')</span>';       
       }

       return $views;
    }    
    
    function extra_tablenav( $which ) {
        if ( 'top' != $which ) return;
        $wpl_accounts = WPLA()->accounts;
        $account_id   = ( isset($_REQUEST['account_id']) ? $_REQUEST['account_id'] : false);
        ?>
        <div class="alignleft actions" style="">

            <select name="account_id">
                <option value=""><?php _e('All accounts','wpla') ?></option>
                <?php foreach ($wpl_accounts as $account) : ?>
                    <option value="<?php echo $account->id ?>"
                        <?php if ( $account_id == $account->id ) echo 'selected'; ?>
                        ><?php echo $account->title ?></option>
                <?php endforeach; ?>
            </select>            

            <input type="submit" name="" id="post-query-submit" class="button" value="Filter">

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
    function prepare_items() {
        
        // process bulk actions
        $this->process_bulk_action();
                        
        // get pagination state
        $current_page = $this->get_pagenum();
        $per_page = $this->get_items_per_page('feeds_per_page', 20);
        
        // define columns
        $this->_column_headers = $this->get_column_info();
        
        // fetch profiles from model
        $feedsModel = new WPLA_AmazonFeed();
        $this->items = $feedsModel->getPageItems( $current_page, $per_page );
        $total_items = $feedsModel->total_items;

        // register our pagination options & calculations.
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items/$per_page)
        ) );

    }
    
}

