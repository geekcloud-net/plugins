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
class WPLA_StockLogTable extends WP_List_Table {

    const TABLENAME = 'amazon_stock_log';
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
            case 'caller':
            case 'method':
            case 'product_id':
            case 'old_stock':
            case 'new_stock':
            case 'sku':
            case 'success':
                return $item[$column_name];
            case 'user':
                return $item['user_id'];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_product_id($item){
        // link to products edit page
        $post_id = $item['product_id'];
        $title   = $item['product_id'];
        $product = wc_get_product( $item['product_id'] );
        if ( is_object($product) && wpla_get_product_meta( $product, 'product_type' ) == 'variation' ) {
            if ( is_callable( array( $product, 'get_parent_id' ) ) ) {
                $title .= ' ('. $product->get_parent_id() .')';
            } else {
                $title .= ' (' . wpla_get_product_meta( $product, 'id' ) . ')';
            }
        }

        $listing_title = '<a class="product_title_link" href="post.php?post='.wpla_get_product_meta( $product, 'id' ).'&action=edit">'.$title.'</a>';
        return $listing_title;        
    }
        
    function column_method($item){
        $value = $item['method'];
        if ( $item['backtrace'] ) {
            $divId  = 'sl-bt-'.$item['id'];
            $value .= ' [<a href="#" onclick="jQuery(\'#'.$divId.'\').toggle();return false;">BT</a>]';
            $value .= '<br>';
            $value .= '<small style="display:none;" id="'.$divId.'">';
            $value .= nl2br( $item['backtrace'] );
            $value .= '</small>';
        }
        return $value;
    }
        
    function column_user($item){
        if ( ! $item['user_id'] ) return '<i>cron</i>';
        $user_info = get_userdata($item['user_id']);
        if ( $user_info ) return $user_info->user_login;
        return false;
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
            'product_id'        => __('Product ID','wpla'),
            'sku'               => __('SKU','wpla'),
            'old_stock'         => __('Old Stock','wpla'),
            'new_stock'         => __('New Stock','wpla'),
            'caller'            => __('Caller','wpla'),
            'method'			=> __('Method','wpla'),
            'user'              => __('User','wpla'),
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
            'delete'    => __('Delete','wpla')
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

    function extra_tablenav( $which ) {
        if ( 'top' != $which ) return;
        $usertype = ( isset($_REQUEST['usertype']) ? $_REQUEST['usertype'] : false);
        $wpl_usertypes = array(
            'cron' => 'Background actions',
            'not_cron' => 'Manual actions',
        );
        ?>
        <div class="alignleft actions" style="">

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

        // search box
        if ( isset( $_REQUEST['s'] ) ) {
            $query = esc_sql( $_REQUEST['s'] );
            $where_sql .= " AND ( 
                                    ( sku = '$query' ) OR 
                                    ( product_id = '$query' ) OR
                                    ( caller LIKE '%$query%' ) OR
                                    ( method LIKE '%$query%' ) 
                                )
                            ";
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

