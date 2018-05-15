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
class WPLA_OrdersTable extends WP_List_Table {

    var $total_items;

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'amazon_order',     //singular name of the listed records
            'plural'    => 'orders',    //plural name of the listed records
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
            case 'order_id':
            case 'buyer_userid':
            case 'buyer_name':
            case 'PaymentMethod':
            case 'AmazonPaymentStatus':
            case 'CheckoutStatus':
            case 'CompleteStatus':
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
        $actions = array(
            'view_amazon_order_details' => sprintf('<a href="?page=%s&action=%s&amazon_order=%s&_wpnonce=%s&width=600&height=470" class="thickbox">%s</a>',$_REQUEST['page'],'view_amazon_order_details',$item['id'], wp_create_nonce( 'wpla_view_order_details' ), __('Details','wpla')),
            // 'create_order' => sprintf('<a href="?page=%s&action=%s&amazon_order=%s">%s</a>',$_REQUEST['page'],'create_order',$item['id'],__('Create Order','wpla')),
            // 'edit'         => sprintf('<a href="?page=%s&action=%s&auction=%s">%s</a>',$_REQUEST['page'],'edit',$item['id'],__('Edit','wpla')),
        );

        // try to find created order
        $order_post_id = $item['post_id'];
        $order_exists  = false;
        $order_msg     = '';

        if ( $order_post_id ) {

            $order = wc_get_order( $order_post_id );
            if ( $order ) {

                // order exists - but might be trashed
                if ( wpla_get_order_meta( $order, 'status' ) == 'trash' ) {
                    $order_msg = '<br><small style="color:darkred;">Order #'.$order_post_id.' has been trashed.</small>';
                } else {
                    $order_exists = true;
                    $order_msg = '<br><small>Order '.$order->get_order_number().' is '.$order->get_status().'.</small>';
                }
            } else {
                // order does not exist - probably deleted
                $order_msg = '<br><small style="color:darkred;">Order #'.$order_post_id.' has been deleted.</small>';
            }

        }

        // create or edit order link
        if ( $order_exists ) {
            $actions['edit_order'] = sprintf('<a href="post.php?action=%s&post=%s">%s</a>','edit',$item['post_id'],__('View Order','wpla'));
        } else {
            ## BEGIN PRO ##
            $actions['create_order'] = sprintf('<a href="?page=%s&action=%s&amazon_order=%s&_wpnonce=%s">%s</a>',$_REQUEST['page'],'wpla_create_order',$item['id'], wp_create_nonce( 'wpla_create_order' ), __('Create Order','wpla'));
            ## END PRO ##
        }


        // if items haven't been loaded, show load items link
        if ( ! $item['items'] || strpos($item['items'], 'RequestThrottled') ) {
            $actions['load_order_items'] = sprintf('<a href="?page=%s&action=%s&amazon_order=%s&_wpnonce=%s">%s</a>',$_REQUEST['page'],'load_order_items',$item['id'], wp_create_nonce( 'wpla_load_order_items' ), __('Fetch Items','wpla'));
            unset( $actions['create_order'] );
        }

        // hide create order link for Pending orders
        if (  $item['status'] == 'Pending' ) {
            unset( $actions['create_order'] );
        }

        // item title
        $title = $item['buyer_name'] ? $item['buyer_name'] : '<i>Unknown Buyer</i>';
        if ( $item['buyer_userid'] ) {
            $title .= ' <i style="color:silver">'.$item['buyer_userid'].'</i>';
        }

        $order_details = json_decode( $item['details'] );
        if ( is_object( $order_details ) ) {
            if ( $order_details->SalesChannel ) {
                $title .= '<br><small style="color:gray;">Placed on '.$order_details->SalesChannel.'</small>';
            }
            if ( $order_details->FulfillmentChannel == 'AFN' ) {
                $title .= '<br><small style="color:gray;">Fulfilled by Amazon (FBA)</small>';
            }
        }

        //Return the title contents
        return sprintf('%1$s %2$s',
            /*$1%s*/ $title . $order_msg,
            /*$2%s*/ $this->row_actions($actions)
        );
    }

    function column_total($item){
        
        // count purchased items
        $items = maybe_unserialize( $item['items'] );
        if ( is_array($items) ) {

            $item_count = 0;
            foreach ($items as $line_item) {
                $item_count += $line_item->QuantityOrdered;
            }
            $item_count .= $item_count == 1 ? ' item' : ' items';

            foreach ($items as $line_item) {
                if ( isset($line_item->GiftWrapLevel) ) {
                    $item_count .= '<br><small style="color:pink;">Gift wrap: '.$line_item->GiftWrapLevel.'</small>';
                }
            }

        } elseif ( is_object($items) ) {
            if ( isset($items->Error->Message) ) {
                $item_count = '<small style="color:darkred;">Error: '.$items->Error->Message.'</small>';
            }
        } else {
            $item_count = '&mdash;';
        }

        if ( $item['currency'] ) {
            $display_price = number_format_i18n( floatval( $item['total'] ), 2 ) .' '. $item['currency'];
        } else {
            $display_price = wc_price( $item['total'] );
        }

        return sprintf('%1$s <br><span style="color:silver">%2$s</span>',
            /*$1%s*/ $display_price,
            /*$2%s*/ $item_count
        );
    }
    function column_buyer_name($item){
        //Return buyer name and ID
        return sprintf('%1$s <br><span style="color:silver">%2$s</span>',
            /*$1%s*/ $item['buyer_name'],
            /*$2%s*/ $item['buyer_userid']
        );
    }
    function column_order_id($item){
        // $account = new WPLA_AmazonAccount( $item['account_id'] );
        $account = WPLA()->accounts[ $item['account_id'] ];
        return sprintf('%1$s <br><span style="color:silver">%2$s</span>',
            /*$1%s*/ $item['order_id'],
            /*$2%s*/ $account->title . ' (' . $account->market_code . ')'
        );
    }
    // function column_PaymentMethod($item){
    //     //Return buyer name and ID
    //     return sprintf('%1$s <br><span style="color:silver">%2$s</span>',
    //         /*$1%s*/ $item['PaymentMethod'],
    //         /*$2%s*/ $item['AmazonPaymentStatus']
    //     );
    // }

    function column_status($item){

        switch( $item['status'] ){
            case 'Pending':
                $color = 'darkorange';
                $value = __('Pending','wpla');
                break;
            case 'Canceled':
                $color = 'silver';
                $value = __('Canceled','wpla');
				break;
            case 'Shipped':
                $color = 'green';
                $value = __('Shipped','wpla');
				break;
            default:
                $color = 'black';
                $value = $item['status'];
        }

        //Return the title contents
        return sprintf('<span style="color:%1$s">%2$s</span><br><span style="color:silver">%3$s</span>',
            /*$1%s*/ $color,
            /*$2%s*/ $value,
            /*$2%s*/ ''
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
            'date_created'		=> __('Created at','wpla'),
            // 'item_id'  			=> __('Amazon ID','wpla'),
            // 'item_title'  		=> __('Product','wpla'),
            'details'           => __('Buyer','wpla'),
            'total'				=> __('Total','wpla'),
            // 'buyer_name'		=> __('Name','wpla'),
            'PaymentMethod'		=> __('Payment method','wpla'),
            'status'	        => __('Status','wpla'),
            'order_id'          => __('Order ID','wpla'),
            // 'status'		 	=> __('Status','wpla'),
            'LastTimeModified'	=> __('Last change','wpla')
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
            'wpla_create_orders'    => __('Create selected orders in WooCommerce', 'wpla'),
            'wpla_update_orders' 	=> __('Update selected orders from Amazon','wpla'),
            'wpla_delete_orders'    => __('Delete selected orders','wpla')
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
       $views      = array();
       $current    = ( !empty($_REQUEST['order_status']) ? $_REQUEST['order_status'] : 'all');
       $current_wc = ( !empty($_REQUEST['has_wc_order']) ? $_REQUEST['has_wc_order'] : '');
       $base_url   = esc_url_raw( remove_query_arg( array( 'action', 'order', 'account_id', 'order_status', 'has_wc_order' ) ) );

       // handle search query
       if ( isset($_REQUEST['s']) && $_REQUEST['s'] ) {
           $base_url = add_query_arg( 's', $_REQUEST['s'], $base_url );
       }
       // handle account_id query
       if ( isset($_REQUEST['account_id']) && $_REQUEST['account_id'] ) {
           $base_url = add_query_arg( 'account_id', $_REQUEST['account_id'], $base_url );
       }
       // handle order_status query
       if ( isset($_REQUEST['order_status']) && $_REQUEST['order_status'] ) {
           $base_url = add_query_arg( 'order_status', $_REQUEST['order_status'], $base_url );
       }
       // handle has_wc_order query
       if ( isset($_REQUEST['has_wc_order']) && $_REQUEST['has_wc_order'] ) {
           $base_url = add_query_arg( 'has_wc_order', $_REQUEST['has_wc_order'], $base_url );
       }

       // get order status summary
       $summary = WPLA_OrdersModel::getStatusSummary();

       // All link
       $class = ($current == 'all' ? ' class="current"' :'');
       $all_url = remove_query_arg( array( 'order_status', 'has_wc_order', 'account_id' ), $base_url );
       $views['all']  = "<a href='{$all_url }' {$class} >".__('All','wpla')."</a>";
       $views['all'] .= '<span class="count">('.$summary->total_items.')</span>';

       // Shipped link
       $Shipped_url = add_query_arg( 'order_status', 'Shipped', $base_url );
       $class = ($current == 'Shipped' ? ' class="current"' :'');
       $views['Shipped'] = "<a href='{$Shipped_url}' {$class} >".__('Shipped','wpla')."</a>";
       if ( isset($summary->Shipped) ) $views['Shipped'] .= '<span class="count">('.$summary->Shipped.')</span>';

       // Unshipped link
       $Unshipped_url = add_query_arg( 'order_status', 'Unshipped', $base_url );
       $class = ($current == 'Unshipped' ? ' class="current"' :'');
       $views['Unshipped'] = "<a href='{$Unshipped_url}' {$class} >".__('Unshipped','wpla')."</a>";
       if ( isset($summary->Unshipped) ) $views['Unshipped'] .= '<span class="count">('.$summary->Unshipped.')</span>';

       // Canceled link
       $Canceled_url = add_query_arg( 'order_status', 'Canceled', $base_url );
       $class = ($current == 'Canceled' ? ' class="current"' :'');
       $views['Canceled'] = "<a href='{$Canceled_url}' {$class} >".__('Canceled','wpla')."</a>";
       if ( isset($summary->Canceled) ) $views['Canceled'] .= '<span class="count">('.$summary->Canceled.')</span>';

       // Pending link
       $Pending_url = add_query_arg( 'order_status', 'Pending', $base_url );
       $class = ($current == 'Pending' ? ' class="current"' :'');
       $views['Pending'] = "<a href='{$Pending_url}' {$class} >".__('Pending','wpla')."</a>";
       if ( isset($summary->Pending) ) $views['Pending'] .= '<span class="count">('.$summary->Pending.')</span>';

       ## BEGIN PRO ##

       // In WooCommerce link
       $has_wc_order_url = add_query_arg( 'has_wc_order', 'yes', $base_url );
       $class = ($current_wc == 'yes' ? ' class="current"' :'');
       $views['has_wc_order'] = "<a href='{$has_wc_order_url}' {$class} >".__('In WooCommerce','wpla')."</a>";
       $views['has_wc_order'] .= '<span class="count">('.$summary->has_wc_order.')</span>';

       // Not in WooCommerce link
       $has_no_wc_order_url = add_query_arg( 'has_wc_order', 'no', $base_url );
       $class = ($current_wc == 'no' ? ' class="current"' :'');
       $views['has_no_wc_order'] = "<a href='{$has_no_wc_order_url}' {$class} >".__('Not in WooCommerce','wpla')."</a>";
       $views['has_no_wc_order'] .= '<span class="count">('.$summary->has_no_wc_order.')</span>';

       ## END PRO ##

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
        $per_page = $this->get_items_per_page('orders_per_page', 20);
        
        // define columns
        $this->_column_headers = $this->get_column_info();
        
        // fetch profiles from model
        $ordersModel = new WPLA_OrdersModel();
        $this->items = $ordersModel->getPageItems( $current_page, $per_page );
        $total_items = $ordersModel->total_items;

        // register our pagination options & calculations.
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items/$per_page)
        ) );

    }
    
}

