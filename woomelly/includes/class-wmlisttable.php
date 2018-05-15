<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( !class_exists( 'WMListTable' ) ) {
    /**
     * WMListTable Class.
     */
    class WMListTable extends WP_List_Table {
    
        /**
         * Default constructor.
         */
        public function __construct() {
            global $status, $page;
                    
            parent::__construct( array(
                'singular'  => 'template',
                'plural'    => 'templates',
                'ajax'      => false
            ) );        
        } //End __construct()

        public function column_default ( $item, $column_name ) {
            switch($column_name){
                case 'category':
                    return $item[$column_name];
                default:
                    return print_r($item,true);
            }
        } //End column_default()

        public function column_title ( $item ) {
            if ( isset($_REQUEST['page']) && isset($item['ID']) ) {
                $actions = array(
                    'edit' => sprintf('<a href="?page=%s&action=%s&woomelly_template_id=%s">' . __("Edit", "woomelly") . '</a>',$_REQUEST['page'],'edit',$item['ID']),
                );
                return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
                    /*$1%s*/ $item['title'],
                    /*$2%s*/ $item['ID'],
                    /*$3%s*/ $this->row_actions($actions)
                );
            }
        } //End column_title()

        public function column_cb ( $item ) {
            if ( isset($this->_args['singular']) && isset($item['ID']) ) {
                return sprintf(
                    '<input type="checkbox" name="%1$s[]" value="%2$s" />',
                    /*$1%s*/ $this->_args['singular'],
                    /*$2%s*/ $item['ID']
                );
            }
        } //End column_cb()

        public function get_columns () {
            $columns = array(
                'cb'        => '<input type="checkbox" />',
                'title'     => __('# Template', 'woomelly'),
                'category'    => __('Cod. Category', 'woomelly'),
            );
            
            return $columns;
        } //End get_columns()

        public function get_sortable_columns () {
            $sortable_columns = array(
                'title'     => array( 'title', false ),
                'category'    => array( 'category', false )
            );
            
            return $sortable_columns;
        } //End get_sortable_columns()

        public function get_bulk_actions () {
            $actions = array(
                'delete' => __('Delete', 'woomelly')
            );
            
            return $actions;
        } //End get_bulk_actions()

        public function process_bulk_action () {
            switch ( $this->current_action() ) {
                case 'delete':
                    if ( isset($_GET['template']) && !empty($_GET['template']) ) {
                        $xx = 0;
                        foreach ( $_GET['template'] as $value ) {
                            WMTemplateSync::delete( $value );
                            $xx++;
                        }
                        wm_print_alert( sprintf(__("Templates successfully deleted. Total: %d", "woomelly"), $xx) );
                    }
                    break;
            }
        } //End process_bulk_action()

        public function prepare_items () {
            global $wpdb;
            
            $per_page = 10;
            $columns = $this->get_columns();
            $hidden = array();
            $sortable = $this->get_sortable_columns();
            $this->_column_headers = array($columns, $hidden, $sortable);
            $this->process_bulk_action();
            $data = WMTemplateSync::get_all();
            usort($data, array($this, 'usort_reorder' ) );
            $current_page = $this->get_pagenum();
            $total_items = count($data);
            $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
            $this->items = $data;
            $this->set_pagination_args( array(
                'total_items' => $total_items,
                'per_page'    => $per_page,
                'total_pages' => ceil($total_items/$per_page)
            ) );
        } //End prepare_items()

        public function usort_reorder ( $a, $b ) {
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title';
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc';
            $result = strcmp($a[$orderby], $b[$orderby]);
            
            return ($order==='asc') ? $result : -$result;
        } //End usort_reorder()
    }
}