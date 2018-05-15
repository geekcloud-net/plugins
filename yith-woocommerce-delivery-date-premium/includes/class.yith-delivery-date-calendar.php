<?php
if( !defined( 'ABSPATH' ) ) {
    exit;
}
if( !class_exists( 'YITH_Delivery_Date_Calendar' ) ) {

    class YITH_Delivery_Date_Calendar
    {

        protected static $_instance;

        protected $table_name;

        public function __construct()
        {
            global $wpdb;

            $this->table_name = $wpdb->prefix . 'ywcdd_calendar';

        }

        /**
         * @author YITHEMES
         * @since 1.0.0
         * @return YITH_Delivery_Date_Calendar unique access
         */
        public static function get_instance()
        {

            if( is_null( self::$_instance ) ) {

                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * @author YITHEMES
         * @since 1.0.0
         * create calendar table if not exist
         */
        public function install()
        {

            $db_version = get_option( 'ywcdd_db_version', '0' );

            if( version_compare( $db_version, '1.0.0', '<' ) ) {

                $this->create_table();
                update_option( 'ywcdd_db_version', YITH_DELIVERY_DATE_DB_VERSION );
            }
        }

        /**
         * create table
         * @author YITHEMES
         * @since 1.0.0
         */
        public function create_table()
        {


            $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
                    ID int(11) NOT NULL AUTO_INCREMENT,
                    post_type varchar(255) NOT NULL,
                    post_id int(11) NOT NULL,
                    event_type varchar(255) NOT NULL,
                    event_name varchar(255) ,
                    event_start DATE NOT NULL ,
                   	event_end DATE NOT NULL ,
                    order_id int(11)  ,
                    PRIMARY KEY ( ID ) ); DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

            if( !function_exists( 'dbDelta' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            }


            dbDelta( $sql );
        }

        public function get_event_type()
        {

            return apply_filters( 'ywcdd_calendar_event_type', array( 'shipping_to_carrier', 'delivery_day', 'holiday' ) );

        }

        /**
         * add new event into calendar
         * @author YITHEMES
         * @since 1.0.0
         * @param string $who_identifier
         * @param string $type_event
         * @param string $start_event
         * @param string $end_event
         * @return false|int
         */
        public function add_calendar_event( $post_id, $event_name, $type_event, $start_event, $end_event = '', $order_id = -1 )
        {

            if( in_array( $type_event, $this->get_event_type() ) ) {


                if( $order_id ==-1 || !$this->event_exists( $order_id, $type_event ) ) {
                   
                    global $wpdb;

                    $start_event = date( 'Y-m-d', strtotime( $start_event ) );
                    $end_event = empty( $end_event ) ? $start_event : date( 'Y-m-d', strtotime( $end_event ) );
                    $args = array(
                        'post_id' => $post_id,
                        'post_type' => $post_id != -1 ? get_post_type( $post_id ) : 'carrier_default',
                        'event_name' => $event_name,
                        'event_type' => $type_event,
                        'event_start' => $start_event,
                        'event_end' => $end_event,
                        'order_id' => $order_id,
                    );


	                $wpdb->show_errors();
	                $wpdb->insert( $this->table_name, $args );

                }
            }
        }

        /**
         * check if the event is already in the table
         * @author YITHEMES
         * @since 1.0.3
         * @param int $order_id
         * @param string $event_type
         * @return bool
         */
        public function event_exists( $order_id, $event_type ){

            global $wpdb;

            $query = "SELECT * FROM {$this->table_name} WHERE order_id = %d AND event_type LIKE %s LIMIT 1";

            $query = $wpdb->prepare( $query, $order_id, $event_type );

           $result = $wpdb->get_var( $query );
            return !empty( $result ) ;
        }

        public function get_calendar_all_events()
        {

            global $wpdb;

            $query =  "SELECT * FROM {$this->table_name} AS ca ORDER BY ca.post_id";

            return $wpdb->get_results( $query );
        }
        
        public function get_calendar_holiday_from( $post_id  ,$date_from  ){
        	
        	global $wpdb;

            $date_from = date('Y-m-d', strtotime( $date_from ) );
        	$query = $wpdb->prepare("SELECT  ca.event_start as start, ca.event_end as end FROM {$this->table_name} as ca WHERE ca.event_start <= %s AND ca.event_type LIKE %s AND ca.post_id = %d", $date_from, 'holiday', $post_id );
            
          
        	return $wpdb->get_results( $query, ARRAY_A );
        }

        public function get_calendar_holiday_to( $post_id  ,$date_to  ){

            global $wpdb;
            
            $date_to = date('Y-m-d', strtotime( $date_to ) );
            $query = $wpdb->prepare("SELECT ca.event_start as start, ca.event_end as end FROM {$this->table_name} as ca WHERE ca.event_end >= %s AND ca.event_type LIKE %s AND ca.post_id = %d", $date_to, 'holiday', $post_id );
           
            return $wpdb->get_results( $query, ARRAY_A );
        }

        /**
         * get all events for fullcalendar
         * @author YITHEMES
         * @since 1.0.0
         * @param bool $is_json
         * @return array|false|string
         */
        public function get_calendar_events( $is_json = false )
        {
            $all_holiday = $this->get_calendar_all_events();

            $json_holidays = array();

            foreach ( $all_holiday as $holiday ) {

                $event_type = $holiday->event_type;
                $event = array();

                if( isset( $holiday->order_id ) && -1 !=$holiday->order_id ){
                	$order = wc_get_order( $holiday->order_id );

                	if( !( $order instanceof  WC_Order )){

                		$this->delete_event_by_order_id( $holiday->order_id );
                		continue;
	                }
                }

                $id = $holiday->post_id;
               
                $who = $this->get_event_identifier( $id );
                $color = get_option('ywcdd_'.$event_type.'_color', '#1197C1' );
                $start_event = strtotime( $holiday->event_start );
                $start_event = date('Y-m-d', $start_event );
                $end_event = '';

                //if end event is set
                if( $holiday->event_start !== $holiday->event_end ) {
                    $end_event = strtotime( $holiday->event_end );
                    $end_event = date( 'Y-m-d', $end_event+DAY_IN_SECONDS );
                }

                switch ( $event_type ) {

                    case 'holiday':

                        $title= sprintf( '<strong>%s %s</strong><br/> %s', __( 'Holiday for','yith-woocommerce-delivery-date' ), $who, $holiday->event_name );
                        $content = '';
                        break;
                    case 'shipping_to_carrier':

                        $edit_order_link = admin_url('post.php');
                        $edit_order_link = esc_url( add_query_arg( array('action'=> 'edit','post'=> $holiday->order_id ), $edit_order_link ) );
                        $edit_order_link = sprintf('<a href="%s" class="order_link" target="_blank">%s #%s</a>', $edit_order_link,__('Order','yith-woocommerce-delivery-date' ) ,$holiday->order_id );
                        $title= sprintf( '<strong>%s</strong><br/>%s', __( 'Shipping day for','yith-woocommerce-delivery-date' ),$edit_order_link  );
                       // $content = sprintf('<p><strong>%s:</strong><a href="%s" target="_blank">#%s</a></p>',__('Order','yith-woocommerce-delivery-date'), $edit_order_link, $holiday->order_id );

                        break;

                    case 'delivery_day':
                    	$edit_order_link = admin_url('post.php');
                    	$edit_order_link = esc_url( add_query_arg( array('action'=> 'edit','post'=> $holiday->order_id ), $edit_order_link ) );
                    	$edit_order_link = sprintf('<a href="%s" class="order_link" target="_blank">%s #%s</a>', $edit_order_link, __('Order','yith-woocommerce-delivery-date' ),$holiday->order_id );
                        $title= sprintf( '<strong>%s</strong><br/>%s', __( 'Delivery day for','yith-woocommerce-delivery-date' ),$edit_order_link );
                        $time_from = get_post_meta( $holiday->order_id, 'ywcdd_order_slot_from', true );
                        $time_to = get_post_meta( $holiday->order_id, 'ywcdd_order_slot_to', true );


                        if( $time_from!='' && $time_to!='' ) {

                            if( !is_numeric( $time_from ) && !is_numeric( $time_to ) ){

                                $time_from = strtotime( $time_from );
                                $time_to = strtotime( $time_to );
                            }
                            $time_from = date( 'H:i:s',  $time_from  );
                            $time_to = date( 'H:i:s',  $time_to  );

                            $end_event = $start_event . 'T' . $time_to;
                            $start_event = $start_event . 'T' . $time_from;
                        }
                        break;
                    default:
                        $title = '';
                        $content = '';
                        break;
                }

                $event['id'] = $holiday->ID;
                $event['title'] = $title;
                $event['start'] = $start_event;

                if( $end_event!= '' ){
                    $event['end'] = $end_event;
                }
                $event['color'] = $color;
                $event['event_type'] = $event_type;

             //   $event['event_details'] = $content;
                $json_holidays[] = $event;

            }

            return $is_json ? wp_json_encode( $json_holidays ) : $json_holidays;
        }

        /**
         * return the event identifier : A Carrier name or a process order method name
         * @author YITHEMES
         * @since 1.0.0
         * @param string|int $holiday_id
         * @return string|void
         */
        private function get_event_identifier( $holiday_id ){

            if( -1 == $holiday_id ){

                return __('Carrier Default', 'yith-woocommerce-delivery-date');
            }
            elseif( is_numeric( $holiday_id )   ){

                return get_the_title( $holiday_id );
            }
            else{
                return '';
            }
        }

        /**
         * delete event by event id
         * @author YITHEMES
         * @since 1.0.0
         * @param $event_id
         * @return false|int
         */
        public function delete_event_by_id( $event_id ){
        	
        	global $wpdb;
        	
        	return $wpdb->delete( $this->table_name, array('ID'=> $event_id ) );
        	
        }

        /**
         * delete event by order id
         * @author YITHEMES
         * @since 1.0.0
         * @param $order_id
         * @return false|int
         */
        public function delete_event_by_order_id( $order_id ){
            global $wpdb;

            return $wpdb->delete( $this->table_name, array('order_id'=> $order_id ) );
        }
    }
}
/**
 * @return YITH_Delivery_Date_Calendar
 */
function YITH_Delivery_Date_Calendar()
{

    return YITH_Delivery_Date_Calendar::get_instance();
}