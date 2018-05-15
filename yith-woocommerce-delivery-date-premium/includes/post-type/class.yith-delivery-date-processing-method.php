<?php
if( !defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'YITH_Delivery_Date_Processing_Method' ) ) {

    class YITH_Delivery_Date_Processing_Method
    {

        protected static $_instance;
        protected $capability_name;

        public function __construct()
        {
            $this->capability_name = 'delivery_date_processing_method';
            add_action( 'init', array( $this, 'register_post_type' ), 16 );
            add_action( 'admin_init', array( $this, 'add_capabilities' ) );
            add_action( 'admin_init', array( $this, 'add_meta_boxes' ) );
            add_filter( 'yit_fw_metaboxes_type_args', array( $this, 'add_custom_type_metaboxes' ) );

            add_action( 'admin_enqueue_scripts', array( $this, 'include_admin_scripts' ), 20 );
            add_action( 'save_post', array( $this, 'save_processing_meta' ) );
            add_action( 'add_meta_boxes', array( $this, 'add_side_meta_boxes' ), 15 );

            add_action( 'wp_ajax_update_shipping_methods', array( $this, 'update_shipping_methods' ) );

            add_filter('manage_edit-yith_proc_method_columns', array($this, 'edit_columns'));
            add_action('manage_yith_proc_method_posts_custom_column', array($this, 'custom_columns'), 10, 2);
        }

        /**
         * @author YITHEMES
         * @since 1.0.0
         * @return YITH_Delivery_Date_Processing_Method
         */
        public static function get_instance()
        {

            if( is_null( self::$_instance ) ) {
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
        public function get_capability()
        {

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

            return apply_filters( 'yith_delivery_date_processing_method_capability', $caps );
        }

        /**
         * register delivery date carrier post type
         * @author YITHEMES
         * @since 1.0.0
         */
        public function register_post_type()
        {

            $args = apply_filters( 'yith_delivery_date_processing_method_post_type', array(
                    'label' => $this->get_taxonomy_label( 'name' ),
                    'description' => '',
                    'labels' => $this->get_taxonomy_label(),
                    'supports' => array( 'title' ),
                    'hierarchical' => false,
                    'public' => false,
                    'show_ui' => true,
                    'show_in_menu' => true,
                    'menu_position' => 56,
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

            register_post_type( 'yith_proc_method', $args );
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

            $label = apply_filters( 'yith_delivery_date_processing_method_taxonomy_label', array(
                    'name' => _x( 'Order Processing Method', 'post type general name', 'yith-woocommerce-delivery-date' ),
                    'singular_name' => _x( 'Order Processing Method', 'post type singular name', 'yith-woocommerce-delivery-date' ),
                    'menu_name' => __( 'Order Processing Method', 'yith-woocommerce-delivery-date' ),
                    'parent_item_colon' => __( 'Parent Item:', 'yith-woocommerce-delivery-date' ),
                    'all_items' => __( 'All methods', 'yith-woocommerce-delivery-date' ),
                    'view_item' => __( 'View method', 'yith-woocommerce-delivery-date' ),
                    'add_new_item' => __( 'Add new Processing Method', 'yith-woocommerce-delivery-date' ),
                    'add_new' => __( 'Add new Processing Method', 'yith-woocommerce-delivery-date' ),
                    'edit_item' => __( 'Edit Processing Method', 'yith-woocommerce-delivery-date' ),
                    'update_item' => __( 'Update Processing Method', 'yith-woocommerce-delivery-date' ),
                    'search_items' => __( 'Search Processing Method', 'yith-woocommerce-delivery-date' ),
                    'not_found' => __( 'No method found', 'yith-woocommerce-delivery-date' ),
                    'not_found_in_trash' => __( 'No method found in Trash', 'yith-woocommerce-delivery-date' ),
                )
            );
            return !empty( $arg ) ? $label[$arg] : $label;
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
         * add meta boxes for post type processing method
         * @author YITHEMES
         * @since 1.0.0
         */
        public function add_meta_boxes()
        {

            $post_id = isset( $_GET['post'] ) ? $_GET['post'] : false;


            if( ( $post_id && 'yith_proc_method' === get_post_type( $post_id ) ) || ( isset( $_GET['post_type'] ) && 'yith_proc_method' === $_GET['post_type'] ) ) {

                /**
                 * @var $metaboxes array metabox_id, metabox_opt
                 */
                $metaboxes = array(
                    'yith-processing-method-metaboxes' => 'processing-method-meta-boxes-options.php',

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

        public function add_side_meta_boxes()
        {

            add_meta_box( 'yith-wc-select-shipping-method-delivery-date-metabox', __( 'Shipping Method', 'yith-woocommerce-delivery-date' ), array( $this, 'shipping_method_delivery_date_meta_box_content' ), 'yith_proc_method', 'side', 'core' );
        }

        public function shipping_method_delivery_date_meta_box_content()
        {

            wc_get_template( '/meta-boxes/shipping-method-info.php', array(), YITH_DELIVERY_DATE_TEMPLATE_PATH, YITH_DELIVERY_DATE_TEMPLATE_PATH );
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


            if( isset( $post ) && 'yith_proc_method' === $post->post_type ) {

                $custom_types = array( 'select_carrier', 'check_list_day' );
                if( in_array( $args['type'], $custom_types ) ) {
                    $args['basename'] = YITH_DELIVERY_DATE_DIR;
                    $args['path'] = 'meta-boxes/types/';
                }

            }
            return $args;
        }

        /**
         * include admin scripts
         * @author YITHEMES
         * @since 1.0.0
         */
        public function include_admin_scripts()
        {

            global $post;


            if( ( isset( $post ) && 'yith_proc_method' === get_post_type( $post->ID ) ) || ( isset( $_GET['post_type'] ) && 'yith_proc_method' === $_GET['post_type'] ) ) {

                wp_enqueue_script( 'ywcdd_timepicker' );
                wp_enqueue_style( 'ywcdd_timepicker_style' );
                wp_enqueue_script( 'yith_wcdd_processing_method' );
                wp_enqueue_style( 'ywcdd_processing_method_metaboxes' );

                $params = array(
                    'ajax_url' => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
                    'timeformat' => 'H:i',
                    'timestep' => get_option( 'ywcdd_timeslot_step' ),
                    'dateformat' => get_option( 'date_format' ),
                    'actions' => array( 'update_shipping_methods' => 'update_shipping_methods' ),
                    'plugin_nonce' => YITH_DELIVERY_DATE_SLUG,

                );

                wp_localize_script( 'yith_wcdd_processing_method', 'yith_delivery_parmas', $params );
            }
        }

        public function save_processing_meta( $post_id )
        {

            if( 'yith_proc_method' === get_post_type( $post_id ) ) {

                $min_days = isset( $_POST['yit_metaboxes']['_ywcdd_minworkday'] ) ? $_POST['yit_metaboxes']['_ywcdd_minworkday'] : false;
                $enable_day = isset( $_POST['ywcdd_enable_day'] ) ? $_POST['ywcdd_enable_day'] : false;
                $timelimit = isset( $_POST['ywcdd_timelimit'] ) ? $_POST['ywcdd_timelimit'] : false;
                $carrier_select = isset( $_POST['yit_metaboxes']['_ywcdd_carrier'] ) ? $_POST['yit_metaboxes']['_ywcdd_carrier'] : array();


                update_post_meta( $post_id, '_ywcdd_minworkday', $min_days );
                update_post_meta( $post_id, '_ywcdd_carrier', $carrier_select );


                if( $enable_day && $timelimit ) {

                    $select_day = array();

                    foreach ( $enable_day as $key => $enable ) {

                        $new_opt = array( 'day' => $key, 'timelimit' => $timelimit[$key], 'enabled' => $enable );
                        $select_day[] = $new_opt;
                    }

                    update_post_meta( $post_id, '_ywcdd_list_day', $select_day );
                }
                
                $all_day = isset( $_POST['ywcdd_all_day'] )? $_POST['ywcdd_all_day']: 'no';
                update_post_meta( $post_id, '_ywcdd_all_day', $all_day );


            }
        }

        public function set_wc_shipping_method( $shipping_id, $method_id='', $is_mandatory='no' ){

            $shipping_setting = get_option( 'woocommerce_' . $shipping_id. '_settings' );
            $shipping_setting['select_process_method'] = $method_id;
            $shipping_setting['set_method_as_mandatory'] = $is_mandatory;

            update_option( 'woocommerce_' . $shipping_id. '_settings', $shipping_setting );

        }

        /**
         * @param $post_id
         * @return mixed
         */
        public function get_carriers( $post_id )
        {

            $carriers = get_post_meta( $post_id, '_ywcdd_carrier', true );

            return $carriers;
        }

        public function update_shipping_methods()
        {

            if( isset( $_REQUEST['ywcdd_zone_id'] ) ) {

                $zone_id = $_REQUEST['ywcdd_zone_id'];
                $shipping_id = isset( $_REQUEST['ywcdd_shipping_id'] ) ? $_REQUEST['ywcdd_shipping_id'] : '';
                $av_methods = array();

                if( $zone_id != '' ) {

                    $zone = new WC_Shipping_Zone( $zone_id );
                    $shipping_methods = $zone->get_shipping_methods( true );


                    foreach ( $shipping_methods as $id => $method ) {

                        $method_id = $method->id . '_' . $method->instance_id;
                        $method_name = $method->method_title;
                        $is_selected = ( !empty( $shipping_id ) && $shipping_id == $method_id );

                        $obj = array(
                            'key' => $method_id,
                            'value' => $method_name,
                            'selected' => $is_selected
                        );
                        $av_methods[] = $obj;
                    }
                }

                wp_send_json( array( 'shipping_methods' => $av_methods ) );
            }
        }

        public function edit_columns( $columns ){


           $mycolumns = array( 'worksday' => __('Workdays','yith-woocommerce-delivery-date' ),
                                'shippingday' => __('Shipping Day','yith-woocommerce-delivery-date' )
           );
            $who_position = array_search( 'date', array_keys( $columns ) );
            $before = array_slice( $columns, 0, $who_position );
            $after  = array_slice( $columns, $who_position );
            return array_merge( $before,$mycolumns,$after);
        }


        public function custom_columns( $column_name, $order_proc_id ){


            if( 'worksday' == $column_name ){

                $worksday = get_post_meta( $order_proc_id, '_ywcdd_minworkday', true );

                echo $worksday;
            }
            if( 'shippingday' == $column_name ){

                $shippingday = get_post_meta( $order_proc_id, '_ywcdd_list_day', true );
                $value='';
                if( $shippingday ){
                    
                    $value = array();
                    foreach( $shippingday as $key => $day ){

                        if( $day['enabled'] == 'yes' ){
                            $value[]= $day['day'];
                        }
                    }
                    if( count( $value )>0 ){
                    $value = implode(',', $value );
                    }
                    else{
                        $value = '';
                    }
                }

                echo $value;
            }

        }
    }
}


if( !function_exists( 'YITH_Delivery_Date_Processing_Method' ) ) {

    function YITH_Delivery_Date_Processing_Method()
    {
        return YITH_Delivery_Date_Processing_Method::get_instance();
    }
}

YITH_Delivery_Date_Processing_Method();
