<?php
if( !defined('ABSPATH')){
    exit;
}
if( !class_exists( 'YITH_WCDD_Time_Slot_Table')){
    
    class YITH_WCDD_Time_Slot_Table extends WP_List_Table{

        protected $option_id;
        public function __construct( $option_id )
        {
            $this->option_id = $option_id;
            parent::__construct( array(
                'singular'  => 'timeslot',     //singular name of the listed records
                'plural'    => 'timeslots',    //plural name of the listed records
                'ajax'      => false          //does this table support ajax?
            ) );

        }

        /**
         * @author YITHEMES
         * @since 1.0.0
         * @return array
         */
        public function get_columns()
        {
            $fee_label = sprintf('%s (%s)', __('Fee','yith-woocommerce-delivery-date'), get_woocommerce_currency_symbol() );
            $columns = array(
              'timefrom'    =>  __('From', 'yith-woocommerce-delivery-date'),
              'timeto'      =>  __('To', 'yith-woocommerce-delivery-date'),
              'max_order'   =>  __('Lockout after', 'yith-woocommerce-delivery-date'),
              'fee'         =>  $fee_label,
              'override_days'      => __('Workdays','yith-woocommerce-delivery-date'),
               'actions' => ''
            );
            
            return $columns;
        }
        
        public function prepare_items()
        {
            $timeslots = get_option( $this->option_id, array() );
            $per_page = 15;
            $columns = $this->get_columns();
            $hidden = array();
            $sortable = array();
            $this->_column_headers = array( $columns, $hidden, $sortable );
            $current_page = $this->get_pagenum();
            $total_items = count( $timeslots );

            $items = array();

            if( !empty( $timeslots ) ){

                foreach( $timeslots as $key=> $slot ){

                    $new_item = array(
                      'ID' => $key,
                      'timefrom' => $slot['timefrom'],
                      'timeto'  => $slot['timeto'],
                      'max_order'   => $slot['max_order'],
                      'fee'        =>  $slot['fee'],
                      'override_days'   => $slot['override_days'],
                       'day_selected' => $slot['day_selected']

                    );

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

        public function column_default( $item, $column_name )
        {
            $column = '';
            
            switch ( $column_name ){
                
                case 'timefrom' :
                case 'timeto':
                    $column = sprintf('<input type="text" class="yith_timepicker timepicker_%1$s"  value="%2$s"/>',  $column_name, $item[$column_name] );
                    break;
                case 'max_order':
                case 'fee' :
                    $column = sprintf('<input type="number" min="0" class="yith_%1$s"step="any" value="%2$s"/>', $column_name, $item[$column_name] );
                    break;
                case 'override_days':
                    $column = sprintf('<input type="checkbox" class="yith_override_day" value="yes" %s /><span class="description">%s</span>', checked('yes', $item[$column_name], false ), __('Override work days','yith-woocommerce-delivery-date') );
                    
                    $days = yith_get_worksday();
                    
                    $div_workdays = '<div class="yith_single_multiworkday">';
                    $div_workdays.=     sprintf('<select multiple="multiple" class="wc-enhanced-select yith_dayworkselect">', $this->option_id, $item['ID'], 'day_selected' );

                   $item['day_selected'] = !is_array( $item['day_selected'] ) ? array() : $item['day_selected'];
                    foreach ( $days as $key_day => $day ) {
                            $div_workdays .= sprintf( '<option value="%s" %s>%s</option>', $key_day, selected( true, in_array( $key_day, $item['day_selected'] ), false ), $day );

                    }
                    $div_workdays.='</select>';
                    $div_workdays.= '</div>';

                    $column.=$div_workdays;
                    break;
                case 'actions' :
                    $column = '';
                    $column .= sprintf( '<a href="#" class="button button-secondary yith_update_time_slot" data-item_id="%s">%s</a>',$item['ID'], __( 'Update', 'yith-woocommerce-delivery-date' ) );
                    $column .= ' ';
                    $column .= sprintf( '<a href="#" class="button button-secondary yith_delete_time_slot" data-item_id="%s">%s</a>',$item['ID'], __( 'Delete', 'yith-woocommerce-delivery-date' ) );

                    break;
            }
            return $column;
        }
    }
}