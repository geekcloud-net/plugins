<?php
if(!defined('ABSPATH')){
	exit;
}
if(!class_exists('YITH_WCDD_Shipping_Day_Product_Table')){
	
	class YITH_WCDD_Shipping_Day_Product_Table extends  WP_List_Table{
		
		public function __construct(){
			
			parent::__construct( array(
					
					'singular'  => 'shippingproductday',     //singular name of the listed records
					'plural'    => 'shippingproductdays',    //plural name of the listed records
					'ajax'      => false          //does this table support ajax?
			) );		
		}
		
		public function get_columns(){
			
			$columns = array(
					'product' => __('Product', 'yith-woocommerce-delivery-date'),
					'need_process_day' => __('Process Day','yith-woocommerce-delivery-date'),
					'actions' => ''
			);
			return $columns;
		}
		
		public function column_default($item, $column_name){
			
			$column='';

            $product = wc_get_product( $item['product'] );

            if( $product instanceof WC_Product ) {
                switch ( $column_name ) {

                    case 'product' :

                        $product_name = $product->get_formatted_name();
                        $edit_product_link = get_edit_post_link( $item[$column_name] );
                        $column = sprintf( '<a href="%s" class="row-title">%s</a><input type="hidden" class="ywcdd_product" value="%s" />', $edit_product_link, $product_name, $item[$column_name] );

                        break;
                    case 'need_process_day':
                        $column = sprintf( '<input type="number" min="0" step="1" class="ywcdd_product_day" value="%s"/>', $item[$column_name] );
                        break;
                    case 'actions':
                        $column = '';
                        $column .= sprintf( '<a href="#" class="button button-secondary yith_update_product_day" data-item_id="%s">%s</a>', $item['product'], __( 'Update', 'yith-woocommerce-delivery-date' ) );
                        $column .= ' ';
                        $column .= sprintf( '<a href="#" class="button button-secondary yith_delete_product_day" data-item_id="%s">%s</a>', $item['product'], __( 'Delete', 'yith-woocommerce-delivery-date' ) );

                        break;
                }
            }
			
			return $column;
		}
		
		public function prepare_items()
		{
			$productdays = get_option( 'yith_new_shipping_day_prod', array() );
			$per_page = 15;
			$columns = $this->get_columns();
			$hidden = array();
			$sortable = array();
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$current_page = $this->get_pagenum();
			$total_items = count( $productdays );
		
			$items = array();
		
			if( !empty( $productdays ) ){
		      $i=0;
				foreach( $productdays as $key=> $prod_day ){
		
					$new_item = array(
							'ID' => $i,
							'product' => $prod_day['product'],
							'need_process_day'  => $prod_day['need_process_day']
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