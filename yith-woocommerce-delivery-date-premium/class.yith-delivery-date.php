<?php
if( !defined( 'ABSPATH' ) ) {
    exit;
}
if( !class_exists( 'YITH_Delivery_Date' ) ) {

    class YITH_Delivery_Date
    {

        /**
         * @var YITH_Delivery_Date unique instance
         */
        protected static $_instance;
        /**
         * @var YIT_Plugin_Panel_WooCommerce
         */
        protected $_panel;
        /**
         * @var string official documentation
         */
        protected $_official_documentation = '//yithemes.com/docs-plugins/yith-woocommerce-delivery-date/';
        /**
         * @var string landing page
         */
        protected $_plugin_landing_url = '//yithemes.com/themes/plugins/yith-woocommerce-delivery-date/';

        /**
         * @var string plugin official live demo
         */
        protected $_premium_live_demo = '//plugins.yithemes.com/yith-woocommerce-delivery-date/';
        /**
         * @var string panel page
         */
        protected $_panel_page = 'yith_delivery_date_panel';

        public function __construct()
        {
            /* === Main Classes to Load === */
            $require = apply_filters( 'yith_wcdd_require_class',
                array(
                    'common' => array(
                        'includes/functions.yith-delivery-date.php',
                        'includes/post-type/class.yith-delivery-date-carrier.php',
                        'includes/post-type/class.yith-delivery-date-processing-method.php',
                        'includes/shipping/class.yith-delivery-date-shipping-manager.php',
                    	'includes/emails/class.yith-delivery-date-emails.php'

                    ),
                    'admin' => array(
                        'includes/class.yith-delivery-date-admin.php',
                        'includes/class.yith-delivery-date-order-manager.php'
                    )
                )
            );

            $this->_require( $require );

            // Load Plugin Framework
            add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );
            //Add action links
            add_filter( 'plugin_action_links_' . plugin_basename( YITH_DELIVERY_DATE_DIR . '/' . basename( YITH_DELIVERY_DATE_FILE ) ), array( $this, 'action_links' ) );
            //Add row meta
            add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

            //Add action for register and update plugin
            add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
            add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );
            

            //Add YITH DELIVERY DATE menu
            add_action( 'admin_menu', array( $this, 'add_menu' ), 5 );

            
        }

        /**
         * @author YITHEMES
         * @since 1.0.0
         * @return YITH_Delivery_Date
         */
        public static function get_instance()
        {

            if( is_null( self::$_instance ) ) {

                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Add the main classes file
         *
         * Include the admin and frontend classes
         *
         * @param $main_classes array The require classes file path
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0
         *
         * @return void
         * @access protected
         */
        protected function _require( $main_classes ) {
            foreach ( $main_classes as $section => $classes ) {
                foreach ( $classes as $class ) {
                    if ( ( 'common' == $section || ( 'frontend' == $section && ! is_admin() ) || ( 'admin' == $section && is_admin() ) ) && file_exists( YITH_DELIVERY_DATE_DIR . $class ) ) {
                        require_once( YITH_DELIVERY_DATE_DIR . $class );
                    }
                }
            }
        }

        /* load plugin fw
        * @author YITHEMES
        * @since 1.0.0
        */
        public function plugin_fw_loader()
        {

            if( !defined( 'YIT_CORE_PLUGIN' ) ) {
                global $plugin_fw_data;
                if( !empty( $plugin_fw_data ) ) {
                    $plugin_fw_file = array_shift( $plugin_fw_data );
                    require_once( $plugin_fw_file );
                }
            }
        }

        /**
         * add custom action links
         * @author YITHEMES
         * @since 1.0.0
         * @param $links
         * @return array
         */
        public function action_links( $links )
        {

            $links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'yith-woocommerce-delivery-date' ) . '</a>';

            $premium_live_text = __( 'Live demo', 'yith-woocommerce-delivery-date' );

            $links[] = '<a href="' . $this->_premium_live_demo . '" target="_blank">' . $premium_live_text . '</a>';


            return $links;
        }

        /**
         * add custom plugin meta
         * @author YITHEMES
         * @since 1.0.0
         * @param $plugin_meta
         * @param $plugin_file
         * @param $plugin_data
         * @param $status
         * @return array
         */
        public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status )
        {

            if( defined( 'YITH_DELIVERY_DATE_INIT' ) && YITH_DELIVERY_DATE_INIT === $plugin_file ) {


                $plugin_meta[] = '<a href="' . $this->_official_documentation . '" target="_blank">' . __( 'Plugin documentation', 'yith-woocommerce-delivery-date' ) . '</a>';
            }

            return $plugin_meta;
        }

        /** Register plugins for activation tab
         * @return void
         * @since    1.0.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function register_plugin_for_activation()
        {
            if( !class_exists( 'YIT_Plugin_Licence' ) ) {
                require_once YITH_DELIVERY_DATE_DIR . 'plugin-fw/licence/lib/yit-licence.php';
                require_once YITH_DELIVERY_DATE_DIR . 'plugin-fw/licence/lib/yit-plugin-licence.php';
            }
            YIT_Plugin_Licence()->register( YITH_DELIVERY_DATE_INIT, YITH_DELIVERY_DATE_SECRET_KEY, YITH_DELIVERY_DATE_SLUG );
        }

        /**
         * Register plugins for update tab
         *
         * @return void
         * @since    1.0.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function register_plugin_for_updates()
        {
            if( !class_exists( 'YIT_Upgrade' ) ) {
                require_once( YITH_DELIVERY_DATE_DIR . 'plugin-fw/lib/yit-upgrade.php' );
            }
            YIT_Upgrade()->register( YITH_DELIVERY_DATE_SLUG, YITH_DELIVERY_DATE_INIT );
        }

        /**
         * add YITH Delivery_Date menu under YITH_Plugins
         * @author YITHEMES
         * @since 1.0.0
         */
        public function add_menu()
        {

            if( !empty( $this->_panel ) ) {
                return;
            }

            $admin_tabs = apply_filters( 'yith_delivery_date_add_tab', array(
                'general-settings' => __( 'Delivery', 'yith-woocommerce-delivery-date' ),
                'delivery-time-slot' => __('Delivery Time Slot', 'yith-woocommerce-delivery-date' ),
            	'custom-shipping-day'	=> __('Custom Shipping Day','yith-woocommerce-delivery-date'),
                'general-calendar' => __( 'Calendar', 'yith-woocommerce-delivery-date' ),
            	'email-settings' => __('Email', 'yith-woocommerce-delivery-date'),
            	'colors-labels' => __('Settings', 'yith-woocommerce-delivery-date')	
            ) );

            $args = array(
                'create_menu_page' => true,
                'parent_slug' => '',
                'page_title' => __( 'Delivery Date', 'yith-woocommerce-delivery-date' ),
                'menu_title' => __( 'Delivery Date', 'yith-woocommerce-delivery-date' ),
                'capability' => 'manage_options',
                'parent' => '',
                'parent_page' => 'yit_plugin_panel',
                'page' => $this->_panel_page,
                'admin-tabs' => $admin_tabs,
                'options-path' => YITH_DELIVERY_DATE_DIR . '/plugin-options'
            );

            if( !class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
                require_once( YITH_DELIVERY_DATE_DIR . 'plugin-fw/lib/yith-plugin-panel-wc.php' );
            }

            $this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );
        }


        
    }
}