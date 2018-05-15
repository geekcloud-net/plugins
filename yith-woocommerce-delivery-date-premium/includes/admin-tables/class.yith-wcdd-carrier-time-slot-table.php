<?php
if( !defined('ABSPATH')){
    exit;
}
if( !class_exists( 'YITH_WCDD_Carrier_Time_Slot_Table')){
    
    class YITH_WCDD_Carrier_Time_Slot_Table extends WP_List_Table{

        protected $post_id;
        protected $metakey;
        public function __construct( $post_id, $metakey )
        {
            $this->post_id = $post_id;
            $this->metakey = $metakey;
            parent::__construct( array(
                'singular'  => 'carriertimeslot',     //singular name of the listed records
                'plural'    => 'carriertimeslots',    //plural name of the listed records
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
            $timeslots = get_post_meta( $this->post_id, $this->metakey, true );
            $timeslots = empty( $timeslots ) ? array() : $timeslots;
            $per_page = 3;
            $columns = $this->get_columns();
            $hidden = array();
            $sortable = array();
            $this->_column_headers = array( $columns, $hidden, $sortable );
            $current_page = $this->get_pagenum();
            $total_items = count( $timeslots );

            $items = array();

            if( !empty( $timeslots ) ){
                $i = 0 ;
                foreach( $timeslots as $key=> $slot ){

                    $new_item = array(
                      'ID' => $key,
                      'timefrom' => $slot['timefrom'],
                      'timeto'  => $slot['timeto'],
                      'max_order'   => $slot['max_order'],
                      'fee'        =>  $slot['fee'],
                      'override_days'   => $slot['override_days'],
                       'day_selected' => isset( $slot['day_selected'] ) ? $slot['day_selected'] : array()

                    );

                    $items[] = $new_item;
                    $i++;
                }
            }
            // retrieve data for table
            $this->items = $items;

            // sets pagination args
            $this->set_pagination_args( array(
                'total_items' => $total_items,
                'per_page'    => 0,
                'total_pages' => 0
            ) );
        }

        public function column_default( $item, $column_name )
        {
            $column = '';
            
            switch ( $column_name ){
                
                case 'timefrom' :
                case 'timeto':
                    $column = sprintf('<input type="text" name="%1$s[%2$s][%3$s]" class="yith_timepicker timepicker_%3$s"  value="%4$s"/>',$this->metakey,$item['ID'], $column_name, $item[$column_name] );
                    break;
                case 'max_order':
                case 'fee' :
                    $column = sprintf('<input type="number"  name="%1$s[%2$s][%3$s]" min="0" class="yith_%3$s"step="any" value="%4$s"/>',$this->metakey,$item['ID'], $column_name, $item[$column_name] );
                    break;
                case 'override_days':

                    $column = sprintf('<input type="checkbox" class="yith_override_day" value="yes" %s /><span class="description">%s</span>', checked('yes', $item[$column_name], false ), __('Override workdays','yith-woocommerce-delivery-date') );
                    $column .= sprintf('<input type="hidden"  class="yith_over_day" name="%1$s[%2$s][%3$s]" value="%4$s" />',$this->metakey,$item['ID'] ,$column_name, $item[$column_name]  );

                    $days = yith_get_worksday();
                    
                    $div_workdays = '<div class="yith_single_multiworkday">';
                    $div_workdays.=     sprintf('<select multiple="multiple" name="%s[%s][%s][]" class="wc-enhanced-select yith_dayworkselect">', $this->metakey, $item['ID'], 'day_selected' );
                    $item['day_selected'] = !is_array( $item['day_selected'] ) ? array() : $item['day_selected'];
                            foreach( $days as $key_day => $day ){
                                $div_workdays.= sprintf('<option value="%s" %s>%s</option>', $key_day, selected( true, in_array( $key_day, $item['day_selected'] ), false ), $day );
                            }
                    $div_workdays.='</select>';
                    $div_workdays .= sprintf('<a href="" class="yith_timeslot_all_day">%s</a>',__('Select all','yith-woocommerce-delivery-date' ) );
                     $div_workdays .= sprintf('<a href="" class="yith_timeslot_clear">%s</a>',__('Clear','yith-woocommerce-delivery-date' ) );
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

        protected function display_tablenav( $which ){
            global $pagenow;

            if ( (  $pagenow != 'post.php' && $pagenow!='post-new.php'  && !( defined('DOING_AJAX') && DOING_AJAX ) ) && 'top' === $which ) {
                wp_nonce_field( 'bulk-' . $this->_args['plural'] );
            }
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>">

                <?php if ( $this->has_items() ): ?>
                    <div class="alignleft actions bulkactions">
                        <?php $this->bulk_actions( $which ); ?>
                    </div>
                <?php endif;
                $this->extra_tablenav( $which );
                $this->pagination( $which );
                ?>

                <br class="clear" />
            </div>
            <?php
        }
        
        
    }
}