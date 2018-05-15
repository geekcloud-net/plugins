<?php
if(!defined('ABSPATH')){
	exit;
}
if(!class_exists('YITH_WCDD_Shipping_Day_Category_Table')){
	
	class YITH_WCDD_Shipping_Day_Category_Table extends  WP_List_Table{
		
		public function __construct(){
			
			parent::__construct( array(
					
					'singular'  => 'shippingcategoryday',     //singular name of the listed records
					'plural'    => 'shippingcategorydays',    //plural name of the listed records
					'ajax'      => false          //does this table support ajax?
			) );		
		}
		
		public function get_columns(){
			
			$columns = array(
					'category' => __('Category', 'yith-woocommerce-delivery-date'),
					'need_process_day' => __('Process Day','yith-woocommerce-delivery-date'),
					'actions' => ''
			);
			return $columns;
		}
		
		public function column_default($item, $column_name){
			
			$column='';
			
			switch( $column_name ){
				
				case 'category' :
				
					$category = get_term_by('id', $item[$column_name], 'product_cat' );
				
					$column= sprintf('<span class="category_label">#%s-%s</span><input type="hidden" class="ywcdd_category" value="%s" />',$item[$column_name],$category->name, $item[$column_name] );
					break;
				case 'need_process_day':
					$column = sprintf('<input type="number" min="0" step="1" class="ywcdd_category_day" value="%s"/>', $item[$column_name] );
					break;
				case 'actions':
					$column = '';
					$column .= sprintf( '<a href="#" class="button button-secondary yith_update_category_day" data-item_id="%s">%s</a>',$item['category'], __( 'Update', 'yith-woocommerce-delivery-date' ) );
					$column .= ' ';
					$column .= sprintf( '<a href="#" class="button button-secondary yith_delete_category_day" data-item_id="%s">%s</a>',$item['category'], __( 'Delete', 'yith-woocommerce-delivery-date' ) );
						
					break;
			}
			
			return $column;
		}
		
		public function prepare_items()
		{
			$categorydays = get_option( 'yith_new_shipping_day_cat', array() );
			$per_page = 15;
			$columns = $this->get_columns();
			$hidden = array();
			$sortable = array();
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$current_page = $this->get_pagenum();
			$total_items = count( $categorydays );
		
			$items = array();
		
			if( !empty( $categorydays ) ){
		      $i=0;
				foreach( $categorydays as $key=> $cat_day ){
		
					$new_item = array(
							'ID' => $i,
							'category' => $cat_day['category'],
							'need_process_day'  => $cat_day['need_process_day']
					);
					$i++;
					$items[] = $new_item;
				}
			}
			// retrieve data for table
			$this->items = $items;
		
			// sets pagination args
			$this->set_pagination_args( array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
					'total_pages' => ceil( $total_items / $per_page )
			) );
		}
		
		
	}
}