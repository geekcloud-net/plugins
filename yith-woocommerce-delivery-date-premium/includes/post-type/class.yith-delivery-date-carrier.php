<?php
if(!defined('ABSPATH')){
    exit;
}
if( !class_exists('YITH_Delivery_Date_Carrier')){

    class YITH_Delivery_Date_Carrier{

        protected static $_instance;
        protected $capability_name ;
        public function __construct()
        {
            $this->capability_name = 'delivery_date_carrier';
            add_action( 'init', array( $this, 'register_post_type'), 16 );
            add_action( 'admin_init', array( $this, 'add_capabilities' ) );
            add_action( 'admin_init', array( $this, 'add_meta_boxes' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'include_admin_scripts' ), 20 );
            add_filter( 'yit_fw_metaboxes_type_args', array( $this, 'add_custom_type_metaboxes' ) );
            add_action( 'save_post', array( $this, 'save_carrier_meta' ) );
            
            add_action( 'wp_ajax_add_carrier_time_slot', array( $this, 'add_carrier_time_slot' )) ;
            add_action( 'wp_ajax_update_carrier_time_slot', array( $this, 'update_carrier_time_slot' )) ;
            add_action( 'wp_ajax_delete_carrier_time_slot', array( $this, 'delete_carrier_time_slot' )) ;
        }

        /**
         * @author YITHEMES
         * @since 1.0.0
         * @return YITH_Delivery_Date_Carrier
         */
        public static function  get_instance()
        {

            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * get post_type capabilities
         * @author YITHEMES
         * @since 1.0.0
         * @return array
         */
        public function get_capability(){

            $caps = array(
                'edit_post' => "edit_{$this->capability_name}",
                'read_post' => "read_{$this->capability_name}",
                'delete_post' => "delete_{$this->capability_name}",
                'edit_posts' => "edit_{$this->capability_name}s",
                'edit_others_posts' => "edit_others_{$this->capability_name}s",
                'publish_posts' => "publish_{$this->capability_name}s",
                'read_private_posts' => "read_private_{$this->capability_name}s",
                'read' => "read",
                'delete_posts' => "delete_{$this->capability_name}s",
                'delete_private_posts' => "delete_private_{$this->capability_name}s",
                'delete_published_posts' => "delete_published_{$this->capability_name}s",
                'delete_others_posts' => "delete_others_{$this->capability_name}s",
                'edit_private_posts' => "edit_private_{$this->capability_name}s",
                'edit_published_posts' => "edit_published_{$this->capability_name}s",
                'create_posts' => "edit_{$this->capability_name}s",
                'manage_posts' => "manage_{$this->capability_name}s",
            );

            return apply_filters('yith_delivery_date_carrier_capability', $caps );
        }

        /**
         * register delivery date carrier post type
         * @author YITHEMES
         * @since 1.0.0
         */
        public function register_post_type(){

            $args = apply_filters( 'yith_delivery_date_carrier_post_type', array(
                    'label' => $this->get_taxonomy_label('name'),
                    'description' => '',
                    'labels' => $this->get_taxonomy_label(),
                    'supports' => array( 'title' ),
                    'hierarchical' => false,
                    'public' => false,
                    'show_ui' => true,
                    'show_in_menu' => true,
                    'menu_position' => 57,
                    'show_in_nav_menus' => false,
                    'show_in_admin_bar' => false,
                    'can_export' => false,
                    'has_archive' => false,
                    'exclude_from_search' => true,
                    'publicly_queryable' => false,
                    'capability_type' => $this->capability_name,
                    'capabilities' => $this->get_capability(),
                )
            );

            register_post_type( 'yith_carrier',  $args );
            flush_rewrite_rules();
        }

        /**
         * Get the taxonomy label
         * @param   $arg string The string to return. Default empty. If is empty return all taxonomy labels
         * @author YITHEMES
         * @since  1.0.0
         * @return array taxonomy label
         *
         */
        public function get_taxonomy_label( $arg = '' )
        {

            $label = apply_filters( 'yith_delivery_date_taxonomy_label', array(
                    'name' => _x( 'Carrier', 'post type general name', 'yith-woocommerce-delivery-date' ),
                    'singular_name' => _x( 'Carrier', 'post type singular name', 'yith-woocommerce-delivery-date' ),
                    'menu_name' => __( 'Carrier', 'yith-woocommerce-delivery-date' ),
                    'parent_item_colon' => __( 'Parent Item:', 'yith-woocommerce-delivery-date' ),
                    'all_items' => __( 'All carriers', 'yith-woocommerce-delivery-date' ),
                    'view_item' => __( 'View carrier', 'yith-woocommerce-delivery-date' ),
                    'add_new_item' => __( 'Add new carrier', 'yith-woocommerce-delivery-date' ),
                    'add_new' => __( 'Add new carrier', 'yith-woocommerce-delivery-date' ),
                    'edit_item' => __( 'Edit carrier', 'yith-woocommerce-delivery-date' ),
                    'update_item' => __( 'Update carrier', 'yith-woocommerce-delivery-date' ),
                    'search_items' => __( 'Search carrier', 'yith-woocommerce-delivery-date' ),
                    'not_found' => __( 'No carrier found', 'yith-woocommerce-delivery-date' ),
                    'not_found_in_trash' => __( 'No carrier found in Trash','yith-woocommerce-delivery-date' ),
                )
            );
            return !empty( $arg ) ? $label[ $arg ] : $label;
        }

        /**
         * add capabilities
         * @author YITHEMES
         * @since 1.0.0
         */
        public function add_capabilities()
        {


            $caps = $this->get_capability();

            // gets the admin and shop_mamager roles
            $admin = get_role( 'administrator' );
            $shop_manager = get_role( 'shop_manager' );

            foreach ( $caps as $key => $cap ) {

                $admin->add_cap( $cap );
                $shop_manager->add_cap( $cap );
            }

        }

        /**
         * add meta boxes for post type carrier
         * @author YITHEMES
         * @since 1.0.0
         */
        public function add_meta_boxes()
        {

           $post_id = isset( $_GET['post'] ) ? $_GET['post'] : false;


            if( ( $post_id && 'yith_carrier' === get_post_type( $post_id ) ) || ( isset( $_GET['post_type'] ) && 'yith_carrier' === $_GET['post_type'] ) ) {

                /**
                 * @var $metaboxes array metabox_id, metabox_opt
                 */
                $metaboxes = array(
                    'yit-carrier-metaboxes' => 'carrier-meta-boxes-options.php',
                );

                if( !function_exists( 'YIT_Metabox' ) ) {
                    require_once( YITH_DELIVERY_DATE_DIR . 'plugin-fw/yit-plugin.php' );
                }

                foreach ( $metaboxes as $key => $metabox ) {
                    $args = require_once( YITH_DELIVERY_DATE_TEMPLATE_PATH . '/meta-boxes/' . $metabox );
                    $box = YIT_Metabox( $key );
                    $box->init( $args );
                }
            }
        }

        /**
         * show custom metabox type
         * @author YITHEMES
         * @since 1.0.0
         * @param $args
         * @return mixed
         */
        public function add_custom_type_metaboxes( $args )
        {
            global $post;


            if ( isset( $post ) && 'yith_carrier' === $post->post_type ) {

                $custom_types = array( 'dayrange','multiselectday','addtimeslot' );
                if ( in_array( $args[ 'type' ], $custom_types ) ) {
                    $args[ 'basename' ] = YITH_DELIVERY_DATE_DIR;
                    $args[ 'path' ] = 'meta-boxes/types/';
                }

            }
            return $args;
        }


        /**
         * include admin scripts
         * @author YITHEMES
         * @since 1.0.0
         */
        public function include_admin_scripts(){

            global $post;


            if( ( isset( $post ) && 'yith_carrier' === get_post_type( $post->ID ) ) || ( isset( $_GET['post_type'] ) && 'yith_carrier' === $_GET['post_type'] ) ) {

                wp_enqueue_script('ywcdd_timepicker');
                wp_enqueue_style('ywcdd_timepicker_style');
                wp_enqueue_script('yith_wcdd_carrier');
                $params = array(
                    'ajax_url' => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
                    'actions' => array(
                        'add_carrier_time_slot' => 'add_carrier_time_slot',
                        'update_carrier_time_slot' => 'update_carrier_time_slot',
                        'delete_carrier_time_slot' => 'delete_carrier_time_slot',
                    ),
                    'empty_row' => sprintf( '<tr class="no-items"><td class="colspanchange" colspan="6">%s</td></tr>', __( 'No items found.', 'yith-woocommerce-delivery-date' ) ),
                    'timeformat' => 'H:i',
                    'timestep' => get_option( 'ywcdd_timeslot_step' ),
                    'dateformat' => get_option('date_format'),
                    'plugin_nonce' => YITH_DELIVERY_DATE_SLUG,

                );

                wp_localize_script( 'yith_wcdd_carrier', 'yith_delivery_parmas', $params );

                wp_enqueue_style( 'ywcdd_carrier_metaboxes' );


            }
        }

        /**
         * @author YITHEMES
         * @since 1.0.0
         * @param $post_id
         */
        public function save_carrier_meta( $post_id ){

            if( 'yith_carrier' === get_post_type( $post_id ) ){
                
               $dayrange = isset( $_POST['yit_metaboxes']['_ywcdd_dayrange'] ) ? $_POST['yit_metaboxes']['_ywcdd_dayrange']   : ''; 
               $workday = isset( $_POST['_ywcdd_workday'] ) ? $_POST['_ywcdd_workday']   : '';
               $select_day = isset( $_POST['yit_metaboxes']['_ywcdd_max_selec_orders'] ) ? $_POST['yit_metaboxes']['_ywcdd_max_selec_orders'] : 30;


                update_post_meta( $post_id, '_ywcdd_dayrange', $dayrange );
                update_post_meta( $post_id, '_ywcdd_workday', $workday );
                update_post_meta( $post_id, '_ywcdd_max_selec_orders', $select_day );

                if( isset( $_POST['_ywcdd_addtimeslot'] ) ){

                    update_post_meta( $post_id,'_ywcdd_addtimeslot', $_POST['_ywcdd_addtimeslot'] );

                }
            }
        }

        /**
         * add new carrier time slot via ajax
         * @author YITHEMES
         * @since 1.0.0
         */
        public function add_carrier_time_slot(){

            if( isset($_POST['ywcdd_post_id'] ) ){

                $carrier_id = $_POST['ywcdd_post_id'];
                $metakey = $_POST['ywcdd_metakey'];
                $timefrom = $_POST['ywcdd_time_from'];
                $timeto = $_POST['ywcdd_time_to'];
                $max_order = $_POST['ywcdd_max_order'];
                $fee = $_POST['ywcdd_fee'];
                $override_days = 'no';
                $days = array();

                $carrier_timeslot = get_post_meta( $carrier_id, $metakey, true );
                $carrier_timeslot = empty( $carrier_timeslot ) ? array() : $carrier_timeslot;
                $id = uniqid('ywcdd_carrier_'.$carrier_id.'_timeslot_');
                $newslot = array(
                    'timefrom' => $timefrom,
                    'timeto'    => $timeto,
                    'max_order' => $max_order,
                    'fee'   => $fee,
                    'override_days' => $override_days,
                    'day_selected' => $days
                );

                $carrier_timeslot[$id] = $newslot;
                update_post_meta( $carrier_id, $metakey, $carrier_timeslot );

                $template = '';
                ob_start();
                wc_get_template( '/meta-boxes/types/single-timeslot-row.php', array('slot_id' => $id, 'from' =>$timefrom, 'to' => $timeto,'max_order' => $max_order, 'fee' => $fee ),
                		YITH_DELIVERY_DATE_TEMPLATE_PATH,YITH_DELIVERY_DATE_TEMPLATE_PATH );
                $template = ob_get_contents();
                ob_end_clean();

                wp_send_json( array('template'=> $template ) );
                die();
            }
        }
        
        public function update_carrier_time_slot(){

            if( isset( $_POST['ywcdd_carrier_id'] ) && isset( $_POST['ywcdd_metakey'] ) ){

                $carrier_id = $_POST['ywcdd_carrier_id'];
                $meta_key = $_POST['ywcdd_metakey'];
                $time_from = $_POST['ywcdd_time_from'];
                $time_to = $_POST['ywcdd_time_to'];
                $max_order = $_POST['ywcdd_max_order'];
                $fee = $_POST['ywcdd_fee'];
                $item_id = $_POST['item_id'];
                $override_days = $_POST['override_days'];
                $days = isset( $_POST['ywcdd_day'] ) ? $_POST['ywcdd_day'] : array() ;

                $time_slots = get_post_meta( $carrier_id, $meta_key, true );
              
                if( !empty( $time_slots ) && isset( $time_slots[$item_id])) {

                    $single_slot =  $time_slots[$item_id];
                    $single_slot['timefrom'] =   $time_from;
                    $single_slot['timeto']   =   $time_to;
                    $single_slot['max_order'] =   $max_order;
                    $single_slot['fee'] =   $fee;
                    $single_slot['override_days'] =   $override_days;
                    $single_slot['day_selected'] = $days;
                    $time_slots[$item_id] = $single_slot;

                    update_post_meta( $carrier_id, $meta_key, $time_slots );
                }


                wp_send_json( array('result' => 'ok' ) );

            }

        }

        public function delete_carrier_time_slot(){

            if( isset( $_POST['ywcdd_carrier_id'] ) && isset( $_POST['ywcdd_metakey'] ) ){

                $carrier_id = $_POST['ywcdd_carrier_id'];
                $meta_key = $_POST['ywcdd_metakey'];
                $item_id = $_POST['item_id'];
                $time_slots = get_post_meta( $carrier_id, $meta_key, true );

                if( !empty( $time_slots ) && isset( $time_slots[$item_id])) {

                    $new_time_slots = array();
                    foreach( $time_slots as $key=> $slot ){
                        if( $key != $item_id ){

                            $new_time_slots[]=$slot;
                        }
                    }
                    update_post_meta( $carrier_id, $meta_key, $new_time_slots );
                    wp_send_json( array('result' => 'ok' ) );
                }
            }
        }
        
        public function get_timeslot( $carrier_id,$slot_id = false ){
            
            $all_slot = get_post_meta( $carrier_id ,'_ywcdd_addtimeslot',true );
            
            if( !$slot_id ){
               return  $all_slot;
            }else {
              
               return isset( $all_slot[$slot_id] ) ? $all_slot[$slot_id]  : false; 
            }
        }

        public function get_all_carrier( ){

            $args = array(
                'post_type' => 'yith_carrier',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids'
            );

            $carriers = get_posts( $args );
            
            return $carriers;
        }
    }
}

if( !function_exists('YITH_Delivery_Date_Carrier') ){
    
    function YITH_Delivery_Date_Carrier(){
        return YITH_Delivery_Date_Carrier::get_instance();
    }
}

YITH_Delivery_Date_Carrier();

