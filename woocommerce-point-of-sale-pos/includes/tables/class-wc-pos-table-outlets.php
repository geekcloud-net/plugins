<?php
/**
 * WoocommercePointOfSale Outlets Table Class
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/Outlets
 * @category	Class
 * @since     0.1
 */


if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class WC_Pos_Table_Outlets extends WP_List_Table {
  protected static $data;
  protected $found_data;

  function __construct(){
  global $status, $page;

      parent::__construct( array(
          'singular'  => 'outlets_table',
          'plural'    => 'outlets_tables',
          'ajax'      => false        //does this table support ajax?
      ) );

  }

  function no_items() {
    _e( 'Outlets not found. Try to adjust the filter.', 'wc_point_of_sale' );
  }
  function column_default( $item, $column_name ) {
    switch ( $column_name ) {
      case 'name':
      case 'contact':
      case 'registers':
        return $item[$column_name];
      default:
        return print_r( $item, true ); //Show the whole array for troubleshooting purposes
    }
  }
  function get_sortable_columns() {
    $sortable_columns = array(
      'name' => array('name', false),
      'registers' => array('registers', false),
    );
    return $sortable_columns;
  }
  function get_columns() {
    $columns = array(
      'cb' => '<input type="checkbox" />',
      'name' => __( 'Outlet', 'wc_point_of_sale' ),
      'contact' => __( 'Contact & Social Details', 'wc_point_of_sale' ),
      'registers' => __( 'Registers', 'wc_point_of_sale' ),
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

  function get_bulk_actions() {
    $actions = apply_filters( 'wc_pos_outlet_bulk_actions', array(
      'delete' => __( 'Delete', 'wc_point_of_sale' ),
    ) );
    return $actions;
  }

  function column_cb( $item ) {
    $d = '';
    if(!wc_pos_check_can_delete('outlet', $item['ID'])){
      $d = 'disabled="disabled"';
    }
    return sprintf(
      '<input type="checkbox" name="id[]" value="%s" %s />', $item['ID'], $d
    );
  }

  function column_name( $item ) {
    
    $actions['edit'] = sprintf('<a href="?page=%s&action=%s&id=%s">Edit</a>','wc_pos_outlets','edit', $item['ID']);
    if(wc_pos_check_can_delete('outlet', $item['ID'])){
      $actions['delete'] = sprintf('<a href="?page=%s&action=%s&id=%s">Delete</a>','wc_pos_outlets','delete', $item['ID']);
    }
    $item['contact']['first_name'] = '';
    $item['contact']['last_name'] = '';
    $item['contact']['company'] = '';
    
    $formatted_address = WC()->countries->get_formatted_address( $item['contact'] );


    $address_string = sprintf(
        '<a style="display: block;" href="https://maps.google.com/?q=%s" target="_blank">%s %s</a>', strip_tags(str_replace('#', '', $formatted_address)),'', $formatted_address
    );

    $name =  sprintf(
        '<strong><a href="?page=%s&action=%s&id=%s">%s</a></strong>','wc_pos_outlets', 'edit', $item['ID'], $item['name']
    ) . '<br>' .$address_string;



    return sprintf('%1$s %2$s', $name, $this->row_actions($actions) );
  }

  function column_contact( $item ) {
    $social_data = $item['social'];
    $social_string = '';

    $o = new WC_Pos_Outlets();
    $o->init_form_fields();
    $social = $o->outlet_social_fields;
    $contact = $o->outlet_contact_fields;

    foreach ($contact as $key => $value) {
      if(isset($social_data[$key]) && !empty($social_data[$key]) )
        if( isset( $value['url'] ) && $value['url'] != '' )
          $social_string .= '<b class="' . $value['label'] . '-icon"></b>' . sprintf($value['url'], str_replace('http://', '', str_replace('https://', '', $social_data[$key]) ), $social_data[$key]) . '<br />';
        else
          $social_string .= '<b class="' . $value['label'] . '-icon"></b>' . $social_data[$key] . '<br />';
    }
    foreach ($social as $key => $value) {
      if(isset($social_data[$key]) && !empty($social_data[$key]) )
        if( isset( $value['url'] ) && $value['url'] != '' )
          $social_string .= '<b class="' . $value['label'] . '-icon"></b>'. sprintf($value['url'], str_replace('http://', '', str_replace('https://', '', $social_data[$key]) ), $social_data[$key]) . '</a><br />';
        else
          $social_string .= '<b class="' . $value['label'] . '-icon"></b>' . $social_data[$key] . '<br />';

    }
    return $social_string;
  }

  function column_registers( $item ) {
    global $wpdb;
    $table_name = $wpdb->prefix . "wc_poin_of_sale_registers";
    $oid = $item['ID'];
    $results = $wpdb->get_results("SELECT ID FROM $table_name WHERE outlet = $oid");
    $registers = count( $results );

    return '<span class="register-numbers">'.$registers.'</span>';
  }

  function prepare_items() {
    $columns  = $this->get_columns();
    $hidden   = array();
    self::$data = WC_POS()->outlet()->get_data();
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
    if( $_GET['page'] == 'wc_pos_outlets' ){
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

} //class