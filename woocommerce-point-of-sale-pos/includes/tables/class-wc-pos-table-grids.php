<?php
/**
 * WoocommercePointOfSale grids Table Class
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/grids
 * @category	Class
 * @since     0.1
 */


if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class WC_Pos_Table_Grids extends WP_List_Table {
  protected static $data;
  protected $found_data;

  function __construct(){
  global $status, $page;

      parent::__construct( array(
          'singular'  => 'grid-table',     //singular name of the listed records
          'plural'    => 'grids-table',   //plural name of the listed records
          'ajax'      => false        //does this table support ajax?
      ) );

  }

  public function get_data($ids = ''){
        global $wpdb;
        $filter = '';
        if( !empty($ids) ){
          if(is_array($ids)){
            $ids = implode(',', array_map('intval', $ids));
            $filter .= "WHERE ID IN  == ($ids)";
          }else{
            $filter .= "WHERE ID = $ids";
          }
        }
        $table_name = $wpdb->prefix . "wc_poin_of_sale_grids";
        $db_data = $wpdb->get_results("SELECT * FROM $table_name $filter");
        $data = array();

        foreach ($db_data as $value) {
          $data[] = get_object_vars($value);
        }
        return $data;
  }
  function no_items() {
    _e( 'No Product Grids currently exist.', 'wc_point_of_sale' );
  }
  function column_default( $item, $column_name ) {
    switch ( $column_name ) {
      case 'layouts_name':
      case 'label':
      case 'view_tiles':
        return $item[$column_name];
      default:
        return print_r( $item, true ); //Show the whole array for troubleshooting purposes
    }
  }
  function get_sortable_columns() {
    $sortable_columns = array(
      'layouts_name' => array('layouts_name', false),
    );
    return $sortable_columns;
  }
  function get_columns() {
    $columns = array(
      'cb' => '<input type="checkbox" />',
      'layouts_name' => __( 'Name', 'wc_point_of_sale' ),
      'label' => __( 'Slug', 'wc_point_of_sale' ),
      'tiles' => __( 'Tiles', 'wc_point_of_sale' ),
      'view_tiles' => '',
    );
    return $columns;
  }
  function usort_reorder( $a, $b ) {
    // If no sort, default to last purchase
    $orderby = ( !empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'ID';
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

  function get_bulk_actions() {
    $actions = apply_filters( 'wc_pos_grid_bulk_actions', array(
      'delete' => __( 'Delete', 'wc_point_of_sale' ),
    ) );
    return $actions;
  }

  function column_cb( $item ) {
    $d = '';
    if(!wc_pos_check_can_delete('grid', $item['ID']))
      $d = 'disabled="disabled"';
    return sprintf(
      '<input type="checkbox" name="id[]" value="%s" %s />', $item['ID'], $d
    );
  }

  function column_layouts_name( $item ) {
    $actions['edit'] = sprintf('<a href="?page=%s&edit=%s">Edit</a>','wc_pos_grids', $item['ID']);
    if(wc_pos_check_can_delete('grid', $item['ID']))
      $actions['delete'] = sprintf('<a class="delete" href="?page=%s&delete=%s">Delete</a>','wc_pos_grids', $item['ID']);


    return sprintf('<strong><a href="admin.php?page=%1$s&amp;grid_id=%2$s">%3$s</a></strong> %4$s', 'wc_pos_tiles', $item['ID'], $item['name'], $this->row_actions($actions) );
  }

  function column_tiles( $item ) {
    $grids = '';
    $tiles = wc_point_of_sale_get_tiles($item['ID']);
    if ($tiles) :
      foreach ($tiles as $tile) :
        if (!empty($grids))
          $grids .= ', '.get_the_title($tile->product_id);
        else
          $grids .= get_the_title($tile->product_id);
      endforeach;
    else :
      $grids = '<span class="na">&ndash;</span>';
    endif;

    return $grids;
  }

  function column_view_tiles( $item ) {
    return sprintf('<a href="admin.php?page=%s&amp;grid_id=%s" class="button alignright tips configure-terms" data-tip="%s">%s</a>', WC_POS()->id_tiles, $item['ID'], __( 'Configure Tiles', 'wc_point_of_sale' ),  __( 'Configure Tiles', 'wc_point_of_sale' ) );
  }

  function prepare_items() {
    $columns  = $this->get_columns();
    $hidden   = array();
    self::$data = $this->get_data();
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
    if( $_GET['page'] == 'wc_pos_grids' ){
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
  public function get_data_names(){
    $data = self::get_data();
    $names_list = array();
    foreach ($data as $value) {
      $names_list[$value['ID']] = $value['name'];
    }
    return $names_list;
  }

} //class