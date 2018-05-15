<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if (!defined('YITH_WCARS_VERSION')) {
    exit('Direct access forbidden.');
}

/**
 *
 *
 * @class      YITH_YWARS_Plugin_FW_Loader
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     Carlos Mora <carlos.eugenio@yourinspiration.it>
 *
 */

if ( ! class_exists( 'YITH_YWARS_Plugin_FW_Loader' ) ) {
    /**
     * Class YITH_YWARS_Plugin_FW_Loader
     *
     * @author Carlos Mora <carlos.eugenio@yourinspiration.it>
     */
    class YITH_YWARS_Plugin_FW_Loader {

        /**
         * @var Panel object
         */
        protected $_panel = null;


        /**
         * @var Panel page
         */
        protected $_panel_page = 'yith_wcars_panel';

        /**
         * @var bool Show the premium landing page
         */
        public $show_premium_landing = true;

        /**
         * @var string Official plugin documentation
         */
        protected $_official_documentation = 'http://yithemes.com/docs-plugins/yith-advanced-refund-system-for-woocommerce';

        /**
         * @var string Official plugin landing page
         */
        protected $_premium_landing = 'https://yithemes.com/themes/plugins/yith-advanced-refund-system-for-woocommerce';

        /**
         * @var string Official plugin landing page
         */
        protected $_premium_live = 'http://plugins.yithemes.com/yith-advanced-refund-system-for-woocommerce/';

        /**
         * Single instance of the class
         *
         * @since 1.0.0
         */
        protected static $instance;

        /**
         * Returns single instance of the class
         *
         * @since 1.0.0
         */
        public static function get_instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Construct
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         */
        public function __construct() {
            /* === Register Panel Settings === */
            add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
            add_filter( 'plugin_action_links_' . plugin_basename( YITH_WCARS_PATH . '/' . basename( YITH_WCARS_FILE ) ), array(
                $this,
                'action_links'
            ) );
            add_action( 'yith_ywars_advanced_refund_system_premium_tab', array( $this, 'premium_tab' ) );

            add_filter ( 'plugin_row_meta', array ( $this, 'plugin_row_meta' ), 10, 4 );


            $this->plugin_fw_loader();

            /**
             * register plugin to licence/update system
             */
            $this->licence_activation ();

        }

        /**
         * plugin_row_meta
         *
         * add the action links to plugin admin page
         *
         * @param $plugin_meta
         * @param $plugin_file
         * @param $plugin_data
         * @param $status
         *
         * @return   Array
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use      plugin_row_meta
         */
        public function plugin_row_meta ( $plugin_meta, $plugin_file, $plugin_data, $status ) {

            if ( ( defined ( 'YITH_WCARS_INIT' ) && ( YITH_WCARS_INIT == $plugin_file ) ) ||
                ( defined ( 'YITH_WCARS_FREE_INIT' ) && ( YITH_WCARS_FREE_INIT == $plugin_file ) )
            ) {
                $plugin_meta[] = '<a href="' . $this->_official_documentation . '" target="_blank">' . __ ( 'Plugin Documentation', 'yith-advanced-refund-system-for-woocommerce' ) . '</a>';
            }

            return $plugin_meta;
        }


        public function action_links( $links ) {
            $links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'yith-advanced-refund-system-for-woocommerce' ) . '</a>';
            $premium_live_text = defined( 'YITH_WCARS_FREE_INIT' ) ? __( 'Premium live demo', 'yith-advanced-refund-system-for-woocommerce' ) : __( 'Live demo', 'yith-advanced-refund-system-for-woocommerce' );
            $links[]           = '<a href="' . $this->_premium_live . '" target="_blank">' . $premium_live_text . '</a>';

            if ( defined( 'YITH_WCARS_FREE_INIT' ) ) {
                $links[] = '<a href="' . $this->get_premium_landing_uri() . '" target="_blank">' . __( 'Premium version',
                        'yith-advanced-refund-system-for-woocommerce' ) . '</a>';
            }

            return $links;
        }

        /**
         * Get the premium landing uri
         *
         * @since   1.0.0
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  string The premium landing link
         */
        public function get_premium_landing_uri () {
            return defined ( 'YITH_REFER_ID' ) ? $this->_premium_landing . '?refer_id=' . YITH_REFER_ID : $this->_premium_landing . '?refer_id=1030585';
        }

        /**
         * Load plugin framework
         *
         * @author Andrea Gr  illo <andrea.grillo@yithemes.com>
         * @since  1.0
         * @return void
         */
        public function plugin_fw_loader() {
            if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
                global $plugin_fw_data;
                if ( ! empty( $plugin_fw_data ) ) {
                    $plugin_fw_file = array_shift( $plugin_fw_data );
                    require_once( $plugin_fw_file );
                }
            }
        }

        /**
         * Add a panel under YITH Plugins tab
         *
         * @return   void
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use     /Yit_Plugin_Panel class
         * @see      plugin-fw/lib/yit-plugin-panel.php
         */
        public function register_panel() {

            if ( ! empty( $this->_panel ) ) {
                return;
            }

            $menu_title = __( 'Advanced Refund System', 'yith-advanced-refund-system-for-woocommerce' );

            $admin_tabs = apply_filters( 'yith_wcars_admin_tabs', array(
                    'settings' => __( 'Settings', 'yith-advanced-refund-system-for-woocommerce' ),
                )
            );

            if ( ! defined( 'YITH_WCARS_PREMIUM' ) ) {
                $admin_tabs['premium-landing'] = __( 'Premium version', 'yith-advanced-refund-system-for-woocommerce' );
            }

            $args = array(
                'create_menu_page' => true,
                'parent_slug' => '',
                'page_title' => $menu_title,
                'menu_title' => $menu_title,
                'capability' => 'manage_options',
                'parent' => '',
                'parent_page' => 'yit_plugin_panel',
                'page' => $this->_panel_page,
                'admin-tabs' => $admin_tabs,
                'options-path' => YITH_WCARS_OPTIONS_PATH,
                'links' => $this->get_sidebar_link()
            );


            /* === Fixed: not updated theme/old plugin framework  === */
            if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
                require_once( YITH_WCARS_PATH . '/plugin-fw/lib/yit-plugin-panel-wc.php' );
            }

            $this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );
        }

        /**
         * Sidebar links
         *
         * @return   array The links
         * @since    1.2.1
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function get_sidebar_link() {
            $links = array(
                array(
                    'title' => __( 'Plugin documentation', 'yith-advanced-refund-system-for-woocommerce' ),
                    'url' => $this->_official_documentation,
                ),
                array(
                    'title' => __( 'Help Center', 'yith-advanced-refund-system-for-woocommerce' ),
                    'url' => 'http://support.yithemes.com/hc/en-us/categories/202568518-Plugins',
                ),
            );

            if (defined('YITH_WCARS_FREE_INIT')) {
                $links[] = array(
                    'title' => __( 'Discover the premium version', 'yith-advanced-refund-system-for-woocommerce' ),
                    'url' => $this->_premium_landing,
                );

                $links[] = array(
                    'title' => __( 'Free vs Premium', 'yith-advanced-refund-system-for-woocommerce' ),
                    'url' => 'https://yithemes.com/themes/plugins/yith-woocommerce-pre-order/#tab-free_vs_premium_tab',
                );

                $links[] = array(
                    'title' => __( 'Premium live demo', 'yith-advanced-refund-system-for-woocommerce' ),
                    'url' => $this->_premium_live
                );

                $links[] = array(
                    'title' => __( 'WordPress support forum', 'yith-advanced-refund-system-for-woocommerce' ),
                    'url' => 'https://wordpress.org/plugins/yith-woocommerce-pre-order/',
                );

                $links[] = array(
                    'title' => sprintf( '%s (%s %s)', __( 'Changelog', 'yith-advanced-refund-system-for-woocommerce' ), __( 'current version', 'yith-advanced-refund-system-for-woocommerce' ), YITH_WCARS_VERSION ),
                    'url' => 'https://yithemes.com/docs-plugins/yith-woocommerce-pre-order/06-changelog-free.html',
                );
            }

            if ( defined( 'YITH_WCARS_PREMIUM' ) ) {
                $links[] = array(
                    'title' => __( 'Support platform', 'yith-advanced-refund-system-for-woocommerce' ),
                    'url' => 'https://yithemes.com/my-account/support/dashboard/',
                );

                $links[] = array(
                    'title' => sprintf( '%s (%s %s)', __( 'Changelog', 'yith-advanced-refund-system-for-woocommerce' ), __( 'current version', 'yith-advanced-refund-system-for-woocommerce' ), YITH_WCARS_VERSION ),
                    'url' => 'https://yithemes.com/docs-plugins/yith-woocommerce-role-changer/07-changelog-premium.html',
                );
            }

            return $links;
        }

        //region    ****    licence related methods ****

        /**
         * Add actions to manage licence activation and updates
         */
        public function licence_activation () {
            if ( ! defined ( 'YITH_WCARS_PREMIUM' ) ) {
                return;
            }

            add_action ( 'wp_loaded', array ( $this, 'register_plugin_for_activation' ), 99 );
            add_action ( 'admin_init', array ( $this, 'register_plugin_for_updates' ) );
        }

        /**
         * Register plugins for activation tab
         *
         * @return void
         * @since    2.0.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function register_plugin_for_activation () {

            if ( ! class_exists ( 'YIT_Plugin_Licence' ) ) {
                require_once 'plugin-fw/lib/yit-plugin-licence.php';
            }

            YIT_Plugin_Licence ()->register ( YITH_WCARS_INIT, YITH_WCARS_SECRETKEY, YITH_WCARS_SLUG );
        }

        /**
         * Register plugins for update tab
         *
         * @return void
         * @since    2.0.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function register_plugin_for_updates () {
            if ( ! class_exists ( 'YIT_Upgrade' ) ) {
                require_once 'plugin-fw/lib/yit-upgrade.php';
            }
            YIT_Upgrade ()->register ( YITH_WCARS_SLUG, YITH_WCARS_INIT );
        }
        //endregion


        /**
         * Premium Tab Template
         *
         * Load the premium tab template on admin page
         *
         * @return   void
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @return void
         */
        public function premium_tab() {
            $premium_tab_template = YITH_WCARS_TEMPLATE_PATH . 'admin/premium_tab.php';
            if ( file_exists( $premium_tab_template ) ) {
                include_once( $premium_tab_template );
            }
        }
    }
}