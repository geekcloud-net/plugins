<?php
if( !defined( 'ABSPATH' ) ) {
    exit;
}
if( !class_exists( 'YITH_Delivery_Date_Admin' ) ) {

    class YITH_Delivery_Date_Admin
    {

        protected static $_instance;


        public function __construct()
        {
        	
            add_action( 'woocommerce_admin_field_dayrange', array( $this, 'add_dayrange_field' ) );
            add_action( 'woocommerce_admin_field_multiselectday', array( $this, 'add_multiselectday_field' ) );
            add_action( 'woocommerce_admin_field_deliverytimeslot', array( $this, 'add_deliverytimeslot_field' ) );
            add_action( 'woocommerce_admin_field_select_order_status', array( $this, 'add_select_order_status_field' ) );
            
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

            //manage time slot, priority 15 after woocommerce init option
            add_action( 'admin_init', array( $this, 'add_time_slot' ), 15 );
            add_action( 'admin_init', array( $this, 'add_processing_category_day' ), 15 );
            add_action( 'admin_init', array( $this, 'add_processing_product_day' ), 15 );
            add_action( 'wp_ajax_update_time_slot', array( $this, 'update_time_slot' ) );
            add_action( 'wp_ajax_delete_time_slot', array( $this, 'delete_time_slot' ) );
            // manage custom product shipping day
            add_action( 'wp_ajax_update_category_day', array( $this, 'update_category_day' ) );
            add_action( 'wp_ajax_delete_category_day', array( $this, 'delete_category_day' ) );
            add_action( 'wp_ajax_update_product_day', array( $this, 'update_product_day' ) );
            add_action( 'wp_ajax_delete_product_day', array( $this, 'delete_product_day' ) );

            //manage calendar ( custom holidays )
            add_action( 'wp_ajax_add_holidays', array( $this, 'add_holidays' ) );
            add_action( 'wp_ajax_delete_holidays', array( $this, 'delete_holidays'));

            //add custom tab in plugin panel
            add_action( 'yith_wcdd_timeslot_panel', array( $this, 'add_timeslot_table_field' ) );
            add_action( 'yith_wcdd_shippingday_panel', array( $this, 'show_shippingday_panel' ) );
            add_action( 'yith_wcdd_general_calendar_tab', array( $this, 'show_calendar_panel' ) );
            
            
            //add admin notices
            add_action('admin_notices', array( $this, 'show_admin_notices' ) );
        }

        /**
         * @author YITHEMES
         * @since 1.0.0
         * @return YITH_Delivery_Date_Admin
         */
        public static function get_instance()
        {

            if( is_null( self::$_instance ) ) {

                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * @param $option
         * @author YITHEMES
         * @since 1.0.0
         */
        public function add_dayrange_field( $option )
        {

            $option['option'] = $option;

            wc_get_template( 'dayrange.php', $option, '', YITH_DELIVERY_DATE_TEMPLATE_PATH . 'admin/' );
        }

        /**
         * @param $option
         * @author YITHEMES
         * @since 1.0.0
         */
        public function add_multiselectday_field( $option )
        {
            $option['option'] = $option;

            wc_get_template( 'multiselectday.php', $option, '', YITH_DELIVERY_DATE_TEMPLATE_PATH . 'admin/' );
        }

        /**
         * @param $option
         * @author YITHEMES
         * @since 1.0.0
         */
        public function add_deliverytimeslot_field( $option )
        {
            $option['option'] = $option;

            wc_get_template( 'deliverytimeslot.php', $option, '', YITH_DELIVERY_DATE_TEMPLATE_PATH . 'admin/' );
        }

        /**
         * add timeslot table template
         * @author YITHEMES
         * @since 1.0.0
         */
        public function add_timeslot_table_field()
        {

            wc_get_template( 'timeslot_table.php', array(), '', YITH_DELIVERY_DATE_TEMPLATE_PATH . 'admin/' );
        }

        /**
         * @author YITHEMES
         * @since 1.0.0
         */
        public function show_shippingday_panel()
        {
            wc_get_template( 'custom-shipping-day.php', array(), '', YITH_DELIVERY_DATE_TEMPLATE_PATH . 'admin/' );
        }

        /**
         * @author YITHEMES
         * @since 1.0.0
         */
        public function show_calendar_panel()
        {

            wc_get_template( 'calendar.php', array(), '', YITH_DELIVERY_DATE_TEMPLATE_PATH . 'admin/' );
        }



        /**
         * add style and script in admin
         * @author YITHEMES
         * @since 1.0.0
         */
        public function enqueue_admin_scripts()
        {

            global $post;
            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';


            $is_delivery_panel_page = ( isset( $_GET['page'] ) && 'yith_delivery_date_panel' === $_GET['page'] );
            $is_carrier_post_type = ( isset( $post ) && 'yith_carrier' === get_post_type( $post->ID ) ) || ( isset( $_GET['post_type'] ) && 'yith_carrier' === $_GET['post_type'] );
            $is_processing_method_post_type = ( isset( $post ) && 'yith_proc_method' === get_post_type( $post->ID ) ) || ( isset( $_GET['post_type'] ) && 'yith_proc_method' === $_GET['post_type'] );

            if( $is_delivery_panel_page || $is_carrier_post_type || $is_processing_method_post_type ) {

                wp_register_script( 'ywcdd_timepicker', YITH_DELIVERY_DATE_ASSETS_URL . 'js/timepicker/jquery.timepicker' . $suffix . '.js', array( 'jquery' ), YITH_DELIVERY_DATE_VERSION, true );
                wp_register_style( 'ywcdd_timepicker_style', YITH_DELIVERY_DATE_ASSETS_URL . 'css/timepicker/jquery.timepicker.css', array(), YITH_DELIVERY_DATE_VERSION );
                wp_register_script( 'moment', YITH_DELIVERY_DATE_ASSETS_URL . 'js/fullcalendar/moment.min.js', array( 'jquery' ), '2.7.3', true );
                wp_register_script( 'ywcdd_fullcalendar', YITH_DELIVERY_DATE_ASSETS_URL . 'js/fullcalendar/fullcalendar.min.js', array( 'jquery', 'moment' ), '2.7.3', true );
                wp_register_style( 'ywcdd_fullcalender_style', YITH_DELIVERY_DATE_ASSETS_URL . 'css/fullcalendar/fullcalendar.min.css', array(), '2.7.3' );

                if( !wp_script_is('select2')){
	                wp_enqueue_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2.full' . $suffix . '.js', array( 'jquery' ), '4.0.3' );
                }
            }
            
            if( $is_delivery_panel_page ) {

                wp_enqueue_script( 'ywcdd_timepicker' );
                wp_enqueue_style( 'ywcdd_timepicker_style' );
                wp_enqueue_script( 'ywcdd_fullcalendar' );
                wp_enqueue_style( 'ywcdd_fullcalender_style' );


                wp_register_script( 'yith_delivery_date_panel', YITH_DELIVERY_DATE_ASSETS_URL . 'js/yith_deliverydate_admin' . $suffix . '.js', array( 'jquery' ), YITH_DELIVERY_DATE_VERSION, true );

                $params = array(
                    'ajax_url' => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
                    'actions' => array(
                        'update_time_slot' => 'update_time_slot',
                        'delete_time_slot' => 'delete_time_slot',
                        'update_category_day' => 'update_category_day',
                        'delete_category_day' => 'delete_category_day',
                        'update_product_day' => 'update_product_day',
                        'delete_product_day' => 'delete_product_day'
                    ),
                    'empty_row' => sprintf( '<tr class="no-items"><td class="colspanchange" colspan="6">%s</td></tr>', __( 'No items found.', 'yith-woocommerce-delivery-date' ) ),
                    'timeformat' => 'H:i',
                    'timestep' => get_option( 'ywcdd_timeslot_step' ),
                    'dateformat' => get_option( 'date_format' ),
                    'plugin_nonce' => YITH_DELIVERY_DATE_SLUG,
                );
                wp_enqueue_script( 'yith_delivery_date_panel' );
                wp_localize_script( 'yith_delivery_date_panel', 'yith_delivery_parmas', $params );

                wp_enqueue_style( 'yith_delivery_date_panel_css', YITH_DELIVERY_DATE_ASSETS_URL . 'css/yith_delivery_date_admin.css', array(), YITH_DELIVERY_DATE_VERSION );

                wp_enqueue_script( 'yith_wcdd_calendar', YITH_DELIVERY_DATE_ASSETS_URL . 'js/yith_deliverydate_calendar' . $suffix . '.js', array( 'jquery' ), YITH_DELIVERY_DATE_VERSION, true );

                $timezone_format = 'Y-m-d H:i:s';

                $now = strtotime( date_i18n( $timezone_format ) );
                $now = strtotime( 'midnight',  $now  );
                $params = array(
                    'starday' => date( 'Y-m-d', $now ),
                    'dateformat' => 'yy-mm-dd',
                    'ajax_url' => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
                    'actions' => array(
                        'add_holidays' => 'add_holidays',
                    	'delete_holidays' => 'delete_holidays'
                    )
                );

                wp_localize_script( 'yith_wcdd_calendar', 'ywcdd_calendar_params', $params );

            }
            if( $is_carrier_post_type ) {

                wp_register_script( 'yith_wcdd_carrier', YITH_DELIVERY_DATE_ASSETS_URL . 'js/yith_deliverydate_carrier' . $suffix . '.js', array( 'jquery', 'jquery-blockui' ), YITH_DELIVERY_DATE_VERSION );
                wp_register_style( 'ywcdd_carrier_metaboxes', YITH_DELIVERY_DATE_ASSETS_URL . 'css/yith_carrier_metaboxes.css', array(), YITH_DELIVERY_DATE_VERSION );
            }

            if( $is_processing_method_post_type ) {

                wp_register_script( 'yith_wcdd_processing_method', YITH_DELIVERY_DATE_ASSETS_URL . 'js/yith_deliverydate_processing_method' . $suffix . '.js', array( 'jquery', 'jquery-blockui' ), YITH_DELIVERY_DATE_VERSION );
                wp_register_style( 'ywcdd_processing_method_metaboxes', YITH_DELIVERY_DATE_ASSETS_URL . 'css/yith_processing_method_metaboxes.css', array(), YITH_DELIVERY_DATE_VERSION );

            }
        }

        /**
         * add time slot
         * @author YITHEMES
         * @since 1.0.0
         */
        public function add_time_slot()
        {


            if( isset( $_POST['yith_new_timeslot'] ) ) {

                $timefrom = $_POST['yith_new_timeslot']['timefrom'];
                $timeto = $_POST['yith_new_timeslot']['timeto'];
                $max_order = $_POST['yith_new_timeslot']['max_order'];
                $fee = $_POST['yith_new_timeslot']['fee'];
                $override = 'no';
                $days = array();

                if( $timefrom !== '' && $timeto !== '' ) {

                    $timeslots = get_option( 'yith_delivery_date_time_slot', array() );

                    $id = uniqid( 'ywcdd_gen_timeslot_' );
                    $newslot = array(
                        'timefrom' => $timefrom,
                        'timeto' => $timeto,
                        'max_order' => $max_order,
                        'fee' => $fee,
                        'override_days' => $override,
                        'day_selected' => $days
                    );

                    $timeslots[$id] = $newslot;

                    update_option( 'yith_delivery_date_time_slot', $timeslots );
                }
            }
        }

        /**
         * update time slot via ajax
         * @author YITHEMES
         * @since 1.0.0
         */
        public function update_time_slot()
        {

            if( isset( $_POST['plugin_nonce'] ) && YITH_DELIVERY_DATE_SLUG === $_POST['plugin_nonce'] && isset( $_POST['slot_action'] ) && 'update_slot' === $_POST['slot_action'] ) {

                $time_from = $_POST['ywcdd_time_from'];
                $time_to = $_POST['ywcdd_time_to'];
                $max_order = $_POST['ywcdd_max_order'];
                $fee = $_POST['ywcdd_fee'];
                $item_id = $_POST['item_id'];
                $override_days = $_POST['override_days'];
                $days = isset( $_POST['ywcdd_day'] ) ? $_POST['ywcdd_day'] : array();


                $time_slots = get_option( 'yith_delivery_date_time_slot' );

                if( !empty( $time_slots ) && isset( $time_slots[$item_id] ) ) {

                    $single_slot = $time_slots[$item_id];
                    $single_slot['timefrom'] = $time_from;
                    $single_slot['timeto'] = $time_to;
                    $single_slot['max_order'] = $max_order;
                    $single_slot['fee'] = $fee;
                    $single_slot['override_days'] = $override_days;
                    $single_slot['day_selected'] = $days;
                    $time_slots[$item_id] = $single_slot;

                    update_option( 'yith_delivery_date_time_slot', $time_slots );
                }


                wp_send_json( array( 'result' => 'ok' ) );
            }
        }

        /**
         * delete time slot via ajax
         * @author YITHEMES
         * @since 1.0.0
         */
        public function delete_time_slot()
        {

            if( isset( $_POST['plugin_nonce'] ) && YITH_DELIVERY_DATE_SLUG === $_POST['plugin_nonce'] && isset( $_POST['slot_action'] ) && 'delete_slot' === $_POST['slot_action'] ) {

                $item_id = $_POST['item_id'];
                $time_slots = get_option( 'yith_delivery_date_time_slot' );

                if( !empty( $time_slots ) && isset( $time_slots[$item_id] ) ) {

                    $new_time_slots = array();
                    foreach ( $time_slots as $key => $slot ) {
                        if( $key != $item_id ) {

                            $new_time_slots[$key] = $slot;
                        }
                    }
                    update_option( 'yith_delivery_date_time_slot', $new_time_slots );
                }

            }
            wp_send_json( array( 'result' => 'ok' ) );

        }

        /**
         * add processing category day
         * @author YITHEMES
         * @since 1.0.0
         */
        public function add_processing_category_day()
        {

            if( isset( $_POST['yith_new_shipping_day_cat']['category'] ) ) {

                $category_id = $_POST['yith_new_shipping_day_cat']['category'];
                $days = $_POST['yith_new_shipping_day_cat']['day'];

                if( empty( $category_id ) || empty( $days ) ) {
                    return;
                }

                $category_day = get_option( 'yith_new_shipping_day_cat', array() );
                if( !isset( $category_day[$category_id] ) ) {
                    $new_catday = array(
                        'category' => $category_id,
                        'need_process_day' => $days
                    );

                    $category_day[$category_id] = $new_catday;
                    update_option( 'yith_new_shipping_day_cat', $category_day );
                }
            }
        }

        /**
         * update process category day
         * @author YITHEMES
         * @since 1.0.0
         *
         */
        public function update_category_day()
        {
            if( isset( $_POST['plugin_nonce'] ) && YITH_DELIVERY_DATE_SLUG === $_POST['plugin_nonce'] ) {

                $category_id = $_POST['ywcdd_category_id'];
                $catday = $_POST['ywcdd_category_day'];

                $category_day = get_option( 'yith_new_shipping_day_cat', array() );

                if( isset( $category_day[$category_id] ) ) {
                    $category_day[$category_id]['need_process_day'] = $catday;
                    update_option( 'yith_new_shipping_day_cat', $category_day );
                }
            }
        }

        /**
         * delete process category day
         * @author YITHEMES
         * @since 1.0.0
         */
        public function delete_category_day()
        {

            if( isset( $_POST['plugin_nonce'] ) && YITH_DELIVERY_DATE_SLUG === $_POST['plugin_nonce'] ) {

                $category_id = $_POST['item_id'];
                $category_day = get_option( 'yith_new_shipping_day_cat', array() );

                if( isset( $category_day[$category_id] ) ) {
                    unset( $category_day[$category_id] );
                    update_option( 'yith_new_shipping_day_cat', $category_day );
                }
            }
        }

        /**
         * add process product day
         * @author YITHEMES
         * @since 1.0.0
         */
        public function add_processing_product_day()
        {

            if( isset( $_POST['yith_new_shipping_day_prod']['product'] ) ) {

                $product_id = $_POST['yith_new_shipping_day_prod']['product'];
                $days = $_POST['yith_new_shipping_day_prod']['day'];

                if( empty( $product_id ) || empty( $days ) ) {
                    return;
                }

                $product_day = get_option( 'yith_new_shipping_day_prod', array() );
                if( !isset( $product_day[$product_id] ) ) {
                    $new_prodday = array(
                        'product' => $product_id,
                        'need_process_day' => $days
                    );

                    $product_day[$product_id] = $new_prodday;
                    update_option( 'yith_new_shipping_day_prod', $product_day );
                }
            }
        }

        /**
         * update process product day
         * @author YITHEMES
         * @since 1.0.0
         */
        public function update_product_day()
        {

            if( isset( $_POST['plugin_nonce'] ) && YITH_DELIVERY_DATE_SLUG === $_POST['plugin_nonce'] ) {

                $product_id = $_POST['ywcdd_product_id'];
                $prodday = $_POST['ywcdd_product_day'];

                $product_day = get_option( 'yith_new_shipping_day_prod', array() );

                if( isset( $product_day[$product_id] ) ) {
                    $product_day[$product_id]['need_process_day'] = $prodday;
                    update_option( 'yith_new_shipping_day_prod', $product_day );
                }
            }
        }

        /**
         * delete process product day
         * @author YITHEMES
         * @since 1.0.0
         */
        public function delete_product_day()
        {


            if( isset( $_POST['plugin_nonce'] ) && YITH_DELIVERY_DATE_SLUG === $_POST['plugin_nonce'] ) {

                $product_id = $_POST['item_id'];
                $product_day = get_option( 'yith_new_shipping_day_prod', array() );

                if( isset( $product_day[$product_id] ) ) {
                    unset( $product_day[$product_id] );
                    update_option( 'yith_new_shipping_day_prod', $product_day );
                }
            }

        }

        //CALENDAR
        /**
         * add new holidays to calendar
         */
        public function add_holidays(){


            if( isset( $_POST['ywcdd_add_holidays'] ) && 'add_new_holidays' == $_POST['ywcdd_add_holidays'] ){

                $color_ship_method = '#ff0000';
                $color_carrier = '#ffff00';
                $order_color = '#1197C1';

                $how_add_holiday = isset( $_POST['ywcdd_how_add'] ) ?  $_POST['ywcdd_how_add']: array();
                $event_name = isset( $_POST['ywcdd_event_name'] ) ? $_POST['ywcdd_event_name'] :'';
                $start_event = isset( $_POST['ywcdd_start_event'] ) ? $_POST['ywcdd_start_event'] :'';
                $end_event = isset( $_POST['ywcdd_end_event'] ) ? $_POST['ywcdd_end_event'] :'';

                foreach( $how_add_holiday as $who ){
                	
                	if( $who == 'carrier_default' ){
                		$who = -1;
                	}
                   
                	YITH_Delivery_Date_Calendar()->add_calendar_event($who,$event_name, 'holiday', $start_event, $end_event );
                }
                
               $all_holiday = YITH_Delivery_Date_Calendar()->get_calendar_events();
              
               
                wp_send_json( array( 'result' => $all_holiday ) );
            }
        }
        
        public function delete_holidays(){
        	
        	if( isset( $_POST['ywcdd_event_id'] ) ){
        		
        		$event_id = $_POST['ywcdd_event_id'];
        	 $res = 	YITH_Delivery_Date_Calendar()->delete_event_by_id($event_id);
        	 
        	 $result = $res ? 'deleted' :'error';
        	 
        	 wp_send_json( array( 'result' => $result ));
        	}
        }
        
        public function show_admin_notices(){
        	
        	$messages = array();
        	if( isset( $_GET['page'] ) && 'yith_delivery_date_panel' == $_GET['page'] ){
        		
        		$tot_post = wp_count_posts( 'yith_proc_method' );
        	    $tot_post = $tot_post->publish;
        	    
        	    if( $tot_post ==  0 ){
        	    	$post_url = admin_url('post-new.php');
        	    	$params = array( 'post_type' => 'yith_proc_method' );
        	    	$new_post_url = esc_url(add_query_arg( $params, $post_url ) );
        	    	$message = sprintf('%s <a href="%s" target="_blank" class="page-title-action" style="top:0;font-size:11px;">%s</a>', __('In order to use the plugin, it is essential to create at least an Order Processing Method','yith-woocommercde-delivery-date'),
        	    					$new_post_url, __('Add new method','yith-woocommerce-delivery-date')	);
        	    	
        	    	$message = array( 'type' => 'warning', 'message' => $message, 'url' => '' );
        	    	
        	    	$messages[] = $message;
        	    	}
        	    	
        	    	if( isset( $_GET['tab'] )  && 'delivery-time-slot' == $_GET['tab'] ){
        	    		
        	    		$carrier_system_enabled = get_option('yith_delivery_date_enable_carrier_system');
        	    		
        	    		if( 'yes' == $carrier_system_enabled ){
        	    			
        	    			$new_post_url = admin_url('admin.php');
        	    			$params = array( 'page' => 'yith_delivery_date_panel', 'tab' =>'general-settings' );
        	    			$new_post_url = esc_url(add_query_arg( $params, $new_post_url ) );
        	    			$message = sprintf('%s', __('These options are disabled since you enabled the carriers system','yith-woocommercde-delivery-date') );
        	    			$message .= sprintf(' <a href="%s">%s</a>', $new_post_url, __('Go here to disable','yith-woocommerce-delivery-date'));
        	    			
        	    			$new_post_url = admin_url('admin.php');
        	    			$params['tab'] = 'delivery-time-slot';
        	    			$params['dismiss_delivery_date_notice'] = 'carrier_enabled_notices';
        	    			$new_post_url = esc_url( add_query_arg( $params, $new_post_url ));
        	    			$message = array( 'type' => 'info', 'message' => $message, 'url' => $new_post_url  );
        	    			
        	    			$messages[] = $message;
        	    		}
        	    	}
        	    	
        	}
        	
        	if( count( $messages ) > 0 ){
        		
        		foreach( $messages as $message ){
        			
        			wc_get_template('/admin/notices/admin-notice-'.$message['type'].'.php', array( 'message' => $message['message'], 'url' => $message['url'] ) ,
        					
        					YITH_DELIVERY_DATE_TEMPLATE_PATH, YITH_DELIVERY_DATE_TEMPLATE_PATH );
        		}
        	}
        }

        /**
         * add select order status field
         * @author YYITHEMES
         * @since 1.0.3
         * @param $option
         */
        public function add_select_order_status_field( $option ) {

            $option['option'] = $option;

            wc_get_template( 'select_order_status.php', $option, '', YITH_DELIVERY_DATE_TEMPLATE_PATH . 'admin/' );
        }

        
    }
}
/**
 * @return YITH_Delivery_Date_Admin
 */
function YITH_Delivery_Date_Admin()
{
    return YITH_Delivery_Date_Admin::get_instance();
}

YITH_Delivery_Date_Admin();