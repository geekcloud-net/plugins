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
class WPLA_LogTable extends WP_List_Table {

    const TABLENAME = 'amazon_log';
    var $total_items;

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'log',     //singular name of the listed records
            'plural'    => 'logs',    //plural name of the listed records
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
            case 'timestamp':
                #return mysql2date( get_option('date_format'), $item[$column_name] );
            case 'callname':
            case 'amazon_id':
            case 'success':
                return $item[$column_name];
            case 'user':
                return $item['user_id'];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_success($item){
        if ( $item['success'] == 'Success' ) {
            return '<span style="color:green">Success</span> ('.$item['http_code'].')';
        }
        if ( $item['success'] == 'Warning' ) {
            return '<span style="color:darkorange">Warning</span>';
        }
        if ( $item['success'] == 'Error' ) {

            $details = '';
            // if ( preg_match("/<Message>(.*)<\/Message>/", $item['response'], $matches) ) {
            //     $Message = $matches[1];
            //     $details .= ': <span style="color:#555">'.$Message.'</span>';
            // }

            return '<span style="color:#B00">Error</span>'.$details.' ('.$item['http_code'].')';
        }
        return $item['success'];
    }    

    function column_account($item){        
        if ( ! $item['account_id'] ) return;
        $account = new WPLA_AmazonAccount( $item['account_id'] );
        if ( ! $account ) return 'unknown';
        return $account->title . ' (' . $account->market_code . ')';
    }

    function column_user($item){
        if ( ! $item['user_id'] ) return '<i>cron</i>';
        $user_info = get_userdata($item['user_id']);
        if ( $user_info ) return $user_info->user_login;
        return false;
    }
        
    function column_callname($item){        
        //Build row action
        $link = sprintf('<a href="?page=%s&action=%s&log_id=%s&_wpnonce=%s&width=820&height=550" class="thickbox">%s</a>',$_REQUEST['page'],'wpla_display_log_entry',$item['id'], wp_create_nonce( 'wpla_display_log_entry' ), $item['callname']);

        // GetMatchingProductForId - show IdList.Id.1
        if ( 'GetMatchingProductForId' == $item['callname'] ) {
            $parameters = maybe_unserialize( $item['parameters'] );
            if ( isset( $parameters['IdList.Id.1'] ) ) {
                $link .= ' - ' . $parameters['IdList.Id.1'];
                $link .= ' (' . $parameters['IdType'] . ')';
            }
        }

        // GetCompetitivePricingForASIN - show ASINList.ASIN.1
        if ( 'GetCompetitivePricingForASIN' == $item['callname'] ) {
            $parameters = maybe_unserialize( $item['parameters'] );
            if ( isset( $parameters['ASINList.ASIN.1'] ) ) {
                $link .= ' - ' . $parameters['ASINList.ASIN.1'];
            }
            if ( isset( $parameters['ASINList.ASIN.2'] ) ) {
                $link .= ', ' . $parameters['ASINList.ASIN.2'];
            }
            if ( isset( $parameters['ASINList.ASIN.3'] ) ) {
                $link .= ', ...';
            }
        }

        // GetLowestOfferListingsForASIN - show ASINList.ASIN.1
        if ( 'GetLowestOfferListingsForASIN' == $item['callname'] ) {
            $parameters = maybe_unserialize( $item['parameters'] );
            if ( isset( $parameters['ASINList.ASIN.1'] ) ) {
                $link .= ' - ' . $parameters['ASINList.ASIN.1'];
            }
            if ( isset( $parameters['ASINList.ASIN.2'] ) ) {
                $link .= ', ' . $parameters['ASINList.ASIN.2'];
            }
            if ( isset( $parameters['ASINList.ASIN.3'] ) ) {
                $link .= ', ...';
            }
        }

        // GetFulfillmentPreview - show SKUs and address
        if ( 'GetFulfillmentPreview' == $item['callname'] ) {
            $parameters = maybe_unserialize( $item['parameters'] );
            if ( isset( $parameters['Items.member.1.SellerSKU'] ) ) {
                $link .= ' - ' . $parameters['Items.member.1.SellerSKU'];
            }
            if ( isset( $parameters['Items.member.2.SellerSKU'] ) ) {
                $link .= ', ' . $parameters['Items.member.2.SellerSKU'];
            }
            if ( isset( $parameters['Items.member.3.SellerSKU'] ) ) {
                $link .= ', ...';
            }
            if ( isset( $parameters['Address.City'] ) ) {
                $link .= ' - ' . $parameters['Address.City'];
            }
            if ( isset( $parameters['Address.PostalCode'] ) ) {
                $link .= ', ' . $parameters['Address.PostalCode'];
            }
            if ( isset( $parameters['Address.StateOrProvinceCode'] ) ) {
                $link .= '  ' . $parameters['Address.StateOrProvinceCode'];
            }
            if ( isset( $parameters['Address.CountryCode'] ) ) {
                $link .= ', ' . $parameters['Address.CountryCode'];
            }
        }

        // GetFeedSubmissionResult - show FeedSubmissionId
        if ( 'GetFeedSubmissionResult' == $item['callname'] ) {
            $request = maybe_unserialize( $item['request'] );
            if ( isset( $request['PARAMETERS']['FeedSubmissionId'] ) ) {
                $link .= ' - ' . $request['PARAMETERS']['FeedSubmissionId'];
            }
        }

        // GetFeedSubmissionList - show FeedSubmissionId
        if ( 'GetFeedSubmissionList' == $item['callname'] ) {
            $request = maybe_unserialize( $item['request'] );
            if ( isset( $request['PARAMETERS']['FeedSubmissionIdList.Id.1'] ) ) {
                $link .= ' - ' . $request['PARAMETERS']['FeedSubmissionIdList.Id.1'];
            }
        }

        // GetOrder - show AmazonOrderId
        if ( 'GetOrder' == $item['callname'] ) {
            $parameters = maybe_unserialize( $item['parameters'] );
            if ( isset( $parameters['AmazonOrderId.Id.1'] ) ) {
                $link .= ' - ' . $parameters['AmazonOrderId.Id.1'];
            }
        }

        // RequestReport - show ReportType
        if ( 'RequestReport' == $item['callname'] ) {
            $parameters = maybe_unserialize( $item['parameters'] );
            if ( isset( $parameters['ReportType'] ) ) {
                $link .= ' <small>' . $parameters['ReportType'] . '</small>';
            }
            if ( isset( $parameters['StartDate'] ) ) {
                $link .= '<br><small>' . str_replace( 'T00:00:00+00:00', '', $parameters['StartDate'] ) . '</small>';
            }
            if ( isset( $parameters['EndDate'] ) ) {
                $link .= ' - <small>' . $parameters['EndDate'] . '</small>';
            }
        }

        // GetReport - show ReportId
        if ( 'GetReport' == $item['callname'] ) {
            $parameters = maybe_unserialize( $item['parameters'] );
            if ( isset( $parameters['ReportId'] ) ) {
                $link .= ' <small>' . $parameters['ReportId'] . '</small>';
            }
        }

        // GetReportRequestList - show MarketplaceId (unused)
        if ( 'GetReportRequestList' == $item['callname'] ) {
            $parameters = maybe_unserialize( $item['parameters'] );
            if ( isset( $parameters['MarketplaceId'] ) ) {
                $link .= ' <small>' . $parameters['MarketplaceId'] . '</small>';
            }
        }

        // ListOrderItems - show AmazonOrderId
        if ( 'ListOrderItems' == $item['callname'] ) {
            $parameters = maybe_unserialize( $item['parameters'] );
            if ( isset( $parameters['AmazonOrderId'] ) ) {
                $link .= ' - ' . $parameters['AmazonOrderId'];
            }
        }

        // ListOrders - show LastUpdatedAfter
        if ( 'ListOrders' == $item['callname'] ) {
            $parameters = maybe_unserialize( $item['parameters'] );
            if ( isset( $parameters['LastUpdatedAfter'] ) ) {
                $link .= ' - ' . str_replace(array('T','.000Z'), ' ', $parameters['LastUpdatedAfter'] );
            }
        }

        // ListMatchingProducts - show Query
        if ( 'ListMatchingProducts' == $item['callname'] ) {
            $parameters = maybe_unserialize( $item['parameters'] );
            if ( isset( $parameters['Query'] ) ) {
                $link .= ' - ' . $parameters['Query'];
            }
        }

        // SubmitFeed - show FeedSubmissionId
        if ( 'SubmitFeed' == $item['callname'] ) {
            $result = json_decode( $item['result'] );
            if ( is_object( $result ) && isset( $result->FeedSubmissionId ) ) {
                $link .= ' - ' . $result->FeedSubmissionId;
            }
        }

        // wplister_inventory_status_changed - show parameters
        if ( 'wplister_inventory_status_changed' == $item['callname'] ) {
            $parameters = maybe_unserialize( $item['parameters'] );
            if ( isset( $parameters ) ) {
                $link .= ' - ' . $parameters;
            }
        }


        // add error details
        if ( $item['success'] == 'Error' ) {

            $details = '';
            if ( preg_match("/<Message>(.*)<\/Message>/", $item['response'], $matches) ) {
                $Message = $matches[1];
                $details .= ': <span style="color:#555">'.$Message.'</span>';
            }

            // $link .= '<br><span style="color:#B00">Error</span>'.$details.' ('.$item['http_code'].')';
            $link .= '<br><span style="color:#B00">Error</span>'.$details;
        }

        // if ( 'GetAmazonDetails' == $item['callname'] ) {
        //     if ( preg_match("/<DetailName>(.*)<\/DetailName>/", $item['request'], $matches) ) {
        //         $match = str_replace('<![CDATA[', '', $matches[1] );
        //         $match = str_replace(']]>', '', $match );
        //         $link .= ' - ' . strip_tags( $match );
        //     }
        // }

        // if ( preg_match("/<ShortMessage>(.*)<\/ShortMessage>/", $item['response'], $matches) ) {
        //     $ShortMessage = $matches[1];
        //     if ( $item['success'] == 'Warning' ) {
        //         $link .= '<br><span style="color:darkorange">Warning: '.$ShortMessage.'</span>';
        //     } else {
        //         $link .= '<br><span style="color:#B00">Error: '.$ShortMessage.'</span>';               
        //     }
        // }

        return $link;
    }

    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("listing")
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
            'cb'                => '<input type="checkbox" />', //Render a checkbox instead of text
            'timestamp'      	=> __('Date','wpla'),
            'callname'			=> __('Request','wpla'),
            // 'amazon_id'			=> __('Item ID','wpla'),
            'account'           => __('Account','wpla'),
            'user'              => __('User','wpla'),
            'success'           => __('Status','wpla')
        );
        return $columns;
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
            'wpla_delete_logs'    => __('Delete','wpla')
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
        
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            #wp_die('Items deleted (or they would be if we had items to delete)!');
        }
        
    }

    // status filter links
    // http://wordpress.stackexchange.com/questions/56883/how-do-i-create-links-at-the-top-of-wp-list-table
    function get_views(){
       $views = array();
       $current = ( !empty($_REQUEST['log_status']) ? $_REQUEST['log_status'] : 'all');
       $base_url = esc_url_raw( remove_query_arg( array( 'action', 'log', 'log_status' ) ) );

       // get status summary
       $summary = self::getStatusSummary();

       // All link
       $class = ($current == 'all' ? ' class="current"' :'');
       $all_url = remove_query_arg( 'log_status', $base_url );
       $views['all']  = "<a href='{$all_url }' {$class} >".__('All','wpla')."</a>";
       $views['all'] .= '<span class="count">('.$summary->all_status_count.')</span>';

       // Success link
       $Success_url = add_query_arg( 'log_status', 'Success', $base_url );
       $class = ($current == 'Success' ? ' class="current"' :'');
       $views['Success'] = "<a href='{$Success_url}' {$class} >".__('Successful','wpla')."</a>";
       if ( isset($summary->Success) ) $views['Success'] .= '<span class="count">('.$summary->Success.')</span>';

       // Error link
       $Error_url = add_query_arg( 'log_status', 'Error', $base_url );
       $class = ($current == 'Error' ? ' class="current"' :'');
       $views['Error'] = "<a href='{$Error_url}' {$class} >".__('Error','wpla')."</a>";
       if ( isset($summary->Error) ) $views['Error'] .= '<span class="count">('.$summary->Error.')</span>';

       // pending link
       $pending_url = add_query_arg( 'log_status', 'pending', $base_url );
       $class = ($current == 'pending' ? ' class="current"' :'');
       $views['pending'] = "<a href='{$pending_url}' {$class} >".__('Pending','wpla')."</a>";
       if ( isset($summary->pending) ) $views['pending'] .= '<span class="count">('.$summary->pending.')</span>';

       // unknown link
       if ( isset($summary->unknown) ) {
           $unknown_url = add_query_arg( 'log_status', 'unknown', $base_url );
           $class = ($current == 'unknown' ? ' class="current"' :'');
           $views['unknown'] = "<a href='{$unknown_url}' {$class} >".__('Unknown','wpla')."</a>";
           $views['unknown'] .= '<span class="count">('.$summary->unknown.')</span>';       
       }

       return $views;
    }    
        
    static function getStatusSummary() {
        global $wpdb;
        $result = $wpdb->get_results("
            SELECT success as status, count(*) as total
            FROM {$wpdb->prefix}amazon_log
            GROUP BY status
        ");
        // echo "<pre>";print_r($result);echo"</pre>";die();

        $summary = new stdClass();
        $summary->all_status_count = 0;
        foreach ($result as $row) {
            $status = $row->status ? $row->status : 'unknown';
            $summary->$status = $row->total;
            $summary->all_status_count += $row->total;
        }

        return $summary;
    }

    function extra_tablenav( $which ) {
        if ( 'top' != $which ) return;
        $callname = ( isset($_REQUEST['callname']) ? $_REQUEST['callname'] : false);
        $wpl_callnames = array(
            'GetMatchingProductForId',
            'ListMatchingProducts',
            'ListOrders',
            'GetOrder',
            'ListOrderItems',
            'SubmitFeed',
            'GetFeedSubmissionList',
            'GetFeedSubmissionResult',
            'GetCompetitivePricingForASIN',
            'GetLowestOfferListingsForASIN',
            'GetFulfillmentPreview',
        );
        $usertype = ( isset($_REQUEST['usertype']) ? $_REQUEST['usertype'] : false);
        $wpl_usertypes = array(
            'cron' => 'Background requests',
            'not_cron' => 'Manual requests',
        );
        ?>
        <div class="alignleft actions" style="">

            <select name="callname">
                <option value=""><?php _e('All requests','wpla') ?></option>
                <?php foreach ($wpl_callnames as $call) : ?>
                    <option value="<?php echo $call ?>"
                        <?php if ( $callname == $call ) echo 'selected'; ?>
                        ><?php echo $call ?></option>
                <?php endforeach; ?>
            </select>            

            <select name="usertype">
                <option value=""><?php _e('All users','wpla') ?></option>
                <?php foreach ($wpl_usertypes as $type => $label) : ?>
                    <option value="<?php echo $type ?>"
                        <?php if ( $usertype == $type ) echo 'selected'; ?>
                        ><?php echo $label ?></option>
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
    function prepare_items( $data = false ) {                
        
        // process bulk actions
        $this->process_bulk_action();
                        
        // get pagination state
        $current_page = $this->get_pagenum();
        $per_page = $this->get_items_per_page('logs_per_page', 20);
        
        // define columns
        $this->_column_headers = $this->get_column_info();
        
        // fetch logs
        $this->items = $this->getPageItems( $current_page, $per_page );
        $total_items = $this->total_items;

        // register our pagination options & calculations.
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items/$per_page)
        ) );

    }


    function getPageItems( $current_page, $per_page ) {
        global $wpdb;

        $table = $wpdb->prefix . self::TABLENAME;

        $orderby  = (!empty($_REQUEST['orderby'])) ? esc_sql( $_REQUEST['orderby'] ) : 'id';
        $order    = (!empty($_REQUEST['order']))   ? esc_sql( $_REQUEST['order']   ) : 'desc';
        $offset   = ( $current_page - 1 ) * $per_page;
        $per_page = esc_sql( $per_page );

        // handle filters
        $where_sql = ' WHERE 1 = 1 ';

        // views
        if ( isset( $_REQUEST['log_status'] ) ) {
            $status = $_REQUEST['log_status'];
            if ( in_array( $status, array('Success','Error','pending','unknown') ) ) {
                if ( $status == 'unknown' ) {
                    $where_sql .= " AND success IS NULL ";
                } else {
                    $where_sql .= " AND success = '$status' ";
                }
            }
        }

        // search box
        if ( isset( $_REQUEST['s'] ) ) {
            $query = esc_sql( $_REQUEST['s'] );
            $where_sql .= " AND ( 
                                    ( callname = '$query' ) OR 
                                    ( amazon_id = '$query' ) OR
                                    ( request LIKE '%$query%' ) OR
                                    ( response LIKE '%$query%' ) 
                                )
                            /* AND NOT amazon_id = 0 */
                            ";
        }

        // callname
        if ( isset( $_REQUEST['callname'] ) && $_REQUEST['callname'] ) {
            $callname = $_REQUEST['callname'];
            $where_sql .= " AND callname = '$callname' ";
        }

        // usertype
        if ( isset( $_REQUEST['usertype'] ) && $_REQUEST['usertype'] ) {
            $usertype = $_REQUEST['usertype'];
            if ( in_array( $usertype, array('cron','not_cron') ) ) {
                if ( $usertype == 'cron' ) {
                    $where_sql .= " AND ( user_id IS NULL OR user_id = '0' ) ";
                } else {
                    $where_sql .= " AND ( user_id IS NOT NULL AND NOT user_id = '0' ) ";
                }
            }
        }



        // get items
        $items = $wpdb->get_results("
            SELECT *
            FROM $table
            $where_sql
            ORDER BY $orderby $order
            LIMIT $offset, $per_page
        ", ARRAY_A);
        
        // get total items count - if needed
        if ( ( $current_page == 1 ) && ( count( $items ) < $per_page ) ) {
            $this->total_items = count( $items );
        } else {
            $this->total_items = $wpdb->get_var("
                SELECT COUNT(*)
                FROM $table
                $where_sql
                ORDER BY $orderby $order
            ");         
        }

        return $items;
    }


    
}

