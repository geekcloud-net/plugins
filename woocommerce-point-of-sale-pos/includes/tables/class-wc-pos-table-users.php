<?php
/**
 * WoocommercePointOfSale Users Table Class
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/Users
 * @category	Class
 * @since     0.1
 */


if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
$GLOBALS['hook_suffix'] = '';
class WC_Pos_Table_Users extends WP_List_Table {
  protected static $data;
  protected $found_data;

  function __construct(){
  global $status, $page;

      parent::__construct( array(
          'singular'  => 'users_table',
          'plural'    => 'users_tables',
          'ajax'      => false        //does this table support ajax?
      ) );

  }


  function no_items() {
    _e( 'Cashiers not found.', 'wc_point_of_sale' );
  }
  function column_default( $item, $column_name ) {
    switch ( $column_name ) {
      case 'name':
      case 'username':
      case 'outlet':
      case 'orders':
      case 'sales':
      case 'last_login':
      case 'logged_in':
        return $item[$column_name];
      default:
        return print_r( $item, true ); //Show the whole array for troubleshooting purposes
    }
  }
   function column_outlet( $item ) {
    $outlet_arr = WC_POS()->outlet()->get_data_names($item['outlet']);;
    if(!empty($item['outlet']) && isset($outlet_arr[$item['outlet']])){
      if ( current_user_can( 'manage_wc_point_of_sale' ) ) {
        return '<a href="admin.php?page=wc_pos_outlets&action=edit&id='.$item['outlet'].'" target="_blank">'.$outlet_arr[$item['outlet']].'</a>';
      }else{
        return $outlet_arr[$item['outlet']];
      }
    }
    else{
      return '-';
    }
  }
  function column_username( $item ) {
    if ( current_user_can( 'edit_user',  $item['ID'] ) ) {
      return '<a href="user-edit.php?user_id='.$item['ID'].'" target="_blank">'.$item['username'].'</a>';  
    }
    return $item['username'];
  }
  function column_sales( $item ) {
    return wc_price($item['sales']);
  }

  function get_sortable_columns() {
    $sortable_columns = array(
      'name'     => array('name', false),
      'username' => array('username', false),
      'outlet'   => array('outlet', false),
    );
    return $sortable_columns;
  }
  function get_columns() {
    $columns = array(
      'name'       => __( 'Display Name', 'wc_point_of_sale' ),
      'username'   => __( 'Username', 'wc_point_of_sale' ),
      'outlet'     => __( 'Outlet', 'wc_point_of_sale' ),
      'sales'      => __( 'Sales', 'wc_point_of_sale' ),
      'orders'     => __( 'Orders', 'wc_point_of_sale' ),
      'last_login' => __( 'Last Login', 'wc_point_of_sale' ),
      'logged_in'  => __( 'Logged In', 'wc_point_of_sale' ),
    );
    return $columns;
  }
  function usort_reorder( $a, $b ) {
    // If no sort, default to last purchase
    $orderby = ( !empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'name';
    // If no order, default to desc
    $order = ( !empty( $_GET['order'] ) ) ? $_GET['order'] : 'desc';
    // Determine sort order
    if ( $orderby == 'order_value' ) {
      $result = $a[$orderby] - $b[$orderby];
    } else {
      $result = strcmp( $a[$orderby], $b[$orderby] );
    }
    // Send final sort direction to usort
    return ( $order === 'asc' ) ? $result : -$result;
  }

  /**
   * Display the search box.
   *
   */
    function search_box( $text, $input_id ) {

      $input_id = $input_id . '-search-input';

      if ( ! empty( $_REQUEST['orderby'] ) )
        echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
      if ( ! empty( $_REQUEST['order'] ) )
        echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
     ?>
      <p class="search-box">
        <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
        <input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
        <?php submit_button( $text, 'button', false, false, array('id' => 'search-submit') ); ?>
      </p>
      <?php
    }


  function prepare_items() {
    $columns  = $this->get_columns();
    $hidden   = array();
    self::$data = WC_POS()->user()->get_data();
    $sortable = $this->get_sortable_columns();
    $this->_column_headers = array( $columns, $hidden, $sortable );
    usort( self::$data, array( &$this, 'usort_reorder' ) );

    $user = get_current_user_id();
    $screen = get_current_screen();
    $option = $screen->get_option('per_page', 'option');
    $per_page = get_user_meta($user, $option, true);
    if ( empty ( $per_page) || $per_page < 1 ) {
        $per_page = $screen->get_option( 'per_page', 'default' );
    }

    $current_page = $this->get_pagenum();

    $total_items = count( self::$data );
    if( $_GET['page'] == 'wc_pos_users' ){
      // only ncessary because we have sample data
      $this->found_data = array_slice( self::$data,( ( $current_page-1 )* $per_page ), $per_page );

      $this->set_pagination_args( array(
        'total_items'   => $total_items,                  //WE have to calculate the total number of items
        'per_page' => $per_page                     //WE have to determine how many items to show on a page
      ) );
      $this->items = $this->found_data;
    }else{
      $this->items = self::$data;
    }
  }
  function extra_tablenav( $which ) {
    if ( $which == 'top' ) {
      do_action( 'wc_pos_restrict_list_users' );
    }
  }

} //class