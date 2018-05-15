<?php
if( !defined( 'ABSPATH' ) ) {
    exit;
}
if( !class_exists( 'YITH_Delivery_Date_Order_Manager' ) ) {

    class YITH_Delivery_Date_Order_Manager
    {

        protected static $_instance;

        public function __construct()
        {
            add_action( 'add_meta_boxes', array( $this, 'add_order_delivery_date_meta_boxes' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'include_scripts' ) );
            add_filter('manage_edit-shop_order_columns', array($this, 'edit_columns'));
            add_filter('manage_edit-shop_order_sortable_columns', array($this, 'edit_sortable_columns'));
            add_action('manage_shop_order_posts_custom_column', array( $this, 'custom_columns') );
            add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_order_meta' ),99 );

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
         * @author YITHEMES
         * @since 1.0.0
         */
        public function add_order_delivery_date_meta_boxes(){

            add_meta_box( 'yith-wc-order-delivery-date-metabox', __( 'Delivery Details', 'yith-woocommerce-delivery-date' ), array( $this, 'order_delivery_date_meta_box_content' ), 'shop_order', 'side', 'core' );

        }

        /**
         * @author YITHEMES
         * @since 1.0.0
         */
        public function order_delivery_date_meta_box_content(){

            wc_get_template('meta-boxes/order-delivery-details-meta-box.php', array(), '', YITH_DELIVERY_DATE_TEMPLATE_PATH );
        }

        /**
         * @author YITHEMES
         * @since 1.0.0
         * @param $post_id
         */
        public function save_order_meta( $post_id ){

            $post_type = get_post_type( $post_id );

            if( 'shop_order' == $post_type && isset( $_POST['ywcdd_has_date'] ) && 'yes' == $_POST['ywcdd_has_date'] ){

                $order = wc_get_order( $post_id ); 
                $shipped = isset( $_POST['ywcdd_order_shipped'] ) ? 'yes' : 'no';

                yit_save_prop( $order, 'ywcdd_order_shipped', $shipped );

                $email_is_sent = yit_get_prop( $order, '_ywcdd_email_sent' );
                
                 if( 'yes' === $shipped && empty( $email_is_sent ) ) {

                    WC()->mailer();
                    do_action( 'yith_advise_user_delivery_email_notification', $order );
                }
                
                
                do_action( 'yith_delivery_date_suborders_shipped', $post_id, $shipped );
                
            }
        }

        public function edit_columns( $columns ){

            $columns['shipping_date'] = __('Shipping date','yith-woocommerce-delivery-date' );
            return $columns;
        }

        public function edit_sortable_columns( $sortable_columns ){

            $sortable_columns['shipping_date'] = 'shipping_date';

            return $sortable_columns;
        }

        public function custom_columns( $column_name ){
            global $post, $the_order;

            if( empty( $the_order ) ){
                $the_order = $post;
            }
            $order_id = yit_get_prop( $the_order, 'id' );
            if ( empty( $the_order ) || $order_id !== $post->ID ) {
                $the_order = wc_get_order( $post->ID );
            }

            if( 'shipping_date' == $column_name ){

                $ship_date = yit_get_prop( $the_order, 'ywcdd_order_shipping_date', true );

                $value = __('No shipping date', 'yith-woocommerce-delivery-date');

                if( !empty( $ship_date ) ){

                	
                    $value = date('Y/m/d',strtotime( $ship_date ));
                   
                }

                echo $value;
            }

        }


        public function include_scripts()
        {

            $current_screen = get_current_screen();
            if( $current_screen->id == 'shop_order' ) {
                wp_enqueue_style( 'delivery_date_order_metabox', YITH_DELIVERY_DATE_ASSETS_URL . 'css/yith_order_metaboxes.css', array(), YITH_DELIVERY_DATE_VERSION );
            }
        }
    }
}

if( !function_exists( 'YITH_Delivery_Date_Order_Manager' ) ) {

    function YITH_Delivery_Date_Order_Manager()
    {
        YITH_Delivery_Date_Order_Manager::get_instance();
    }
}

YITH_Delivery_Date_Order_Manager();