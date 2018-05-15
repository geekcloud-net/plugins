<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined ( 'ABSPATH' ) ) {
    exit( 'Direct access forbidden.' );
}

if( ! class_exists( 'YITH_Frontend_Manager_Admin' ) ){

    class YITH_Frontend_Manager_Admin {

        /**
         * @var YIT_Plugin_Panel_Woocommerce instance
         */
        protected $_panel;

        /**
         * @var YIT_Plugin_Panel_Woocommerce instance
         */
        protected $_panel_page = 'yith_wcfm_panel';

        /**
         * @var string Official plugin documentation
         */
        protected $_official_documentation = 'http://docs.yithemes.com/yith-frontend-manager-for-woocommerce';

        /**
         * @var string Official plugin landing page
         */
        protected $_premium_landing = 'https://yithemes.com/themes/plugins/yith-woocommerce-frontend-manager/';

        /**
         * @var string Official plugin landing page
         */
        protected $_premium_live = '';

        /**
         * YITH_Frontend_Manager_Admin constructor.
         */
        public function __construct(){
            
            /* Action links and Row meta */
            add_filter( 'plugin_action_links_' . plugin_basename( YITH_WCFM_PATH . '/' . basename( YITH_WCFM_FILE ) ), array( $this, 'action_links' ) );
            add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

            /* Panel Settings */
            add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
            add_action( 'update_option', array( $this, 'check_save_endpoint' ), 10, 3 );

            /* Premium Tab */
            add_action( 'yith_wcfm_premium_tab', array( $this, 'show_premium_tab' ) );

            /* Style & Scripts */
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

            /* Remove the wp admin bar if the user check the "remove" option */
            add_action( 'admin_bar_menu', array( $this, 'admin_bar_menus' ), 35 );

	        add_action( 'woocommerce_admin_field_yith_wcfm_button', array( $this, 'admin_field_button' ), 10, 1 );
        }

        /**
         * Add Scripts & Styles
         *
         * @return   void
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         *
         */
        public function enqueue_scripts() {
            global $pagenow;
            if( 'admin.php' == $pagenow && ! empty( $_GET['page'] ) && $this->_panel_page == $_GET['page'] ){
                wp_enqueue_style( 'yith_wcfm_admin', YITH_WCFM_STYLE_URL . 'admin.css', array(), YITH_WCFM_VERSION );
            }

            $allowed_tabs = array( 'settings' );
            $is_allowed_tab = empty( $_GET['tab'] ) || ( ! empty( $_GET['tab'] ) && in_array( $_GET['tab'], $allowed_tabs ) );
            if( 'admin.php' == $pagenow && ! empty( $_GET['page'] ) && $this->_panel_page == $_GET['page'] && $is_allowed_tab ){
                wp_enqueue_script( 'yith_wcfm_admin_script', YITH_WCFM_SCRIPT_URL . 'admin.js', array( 'jquery' ), YITH_WCFM_VERSION, true );
                $script_args = array(
                    'tab' => ! empty( $_GET['tab'] ) ? $_GET['tab'] : 'settings',
                    'flush_confirm_message' => __( 'Are you sure you want to flush permalink settings?', 'yith-frontend-manager-for-woocommerce' ),
                    'flushed_message'       => __( 'Flushed!', 'yith-frontend-manager-for-woocommerce' )
                );

                wp_localize_script( 'yith_wcfm_admin_script', 'yith_wcfm', $script_args );
            }

            do_action( 'yith_wcfm_admin_enqueue_scripts' );
        }

        /**
         * Add a panel under YITH Plugins tab
         *
         * @return   void
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use      /Yit_Plugin_Panel class
         * @see      plugin-fw/lib/yit-plugin-panel.php
         */
        public function register_panel() {

            if ( ! empty( $this->_panel ) ) {
                return;
            }

            $admin_tabs = apply_filters( 'yith_wcfm_admin_tabs', array(
                'settings'      => __( 'General Settings', 'yith-frontend-manager-for-woocommerce' ),
                'endpoints'     => __( 'Endpoints', 'yith-frontend-manager-for-woocommerce' ),
                'premium'       => __( 'Premium Version', 'yith-frontend-manager-for-woocommerce' ),
            ) );

            $args = array(
                'create_menu_page' => true,
                'parent_slug'      => '',
                'page_title'       => __( 'Frontend Manager', 'yith-frontend-manager-for-woocommerce' ),
                'menu_title'       => __( 'Frontend Manager', 'yith-frontend-manager-for-woocommerce' ),
                'capability'       => apply_filters( 'yit_wcfm_plugin_options_capability', 'manage_options' ),
                'parent'           => '',
                'parent_page'      => 'yit_plugin_panel',
                'page'             => $this->_panel_page,
                'admin-tabs'       => $admin_tabs,
                'options-path'     => YITH_WCFM_PATH . 'settings',
                'links'            => $this->get_sidebar_link()
            );

            $this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );
        }

        /**
         * Sidebar links
         *
         * @return   array The links
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function get_sidebar_link(){
            $links =  array(
                array(
                    'title' => __( 'Plugin documentation', 'yith-frontend-manager-for-woocommerce' ),
                    'url'   => $this->_official_documentation,
                ),
            );

            if( defined( 'YITH_WCFM_FREE_INIT' ) ){
                $links[] = array(
                    'title' => __( 'Discover the premium version', 'yith-frontend-manager-for-woocommerce' ),
                    'url'   => $this->_premium_landing,
                );

                $links[] = array(
                    'title' => __( 'Free vs Premium', 'yith-frontend-manager-for-woocommerce' ),
                    'url'   => 'https://yithemes.com/themes/plugins/yith-woocommerce-frontend-manager/#tab-free_vs_premium_tab',
                );

                $links[] = array(
                    'title' => __( 'Premium live demo', 'yith-frontend-manager-for-woocommerce' ),
                    'url'   => $this->_premium_live
                );

                $links[] =  array(
                    'title' => __( 'WordPress support forum', 'yith-frontend-manager-for-woocommerce' ),
                    'url'   => 'https://wordpress.org/support/plugin/yith-woocommerce-frontend-manager',
                );

                $links[] =  array(
                    'title' => __( 'Changelog', 'yith-frontend-manager-for-woocommerce' ),
                    'url'   => 'http://yithemes.com/docs-plugins/yith-woocommerce-frontend-manager/14-changelog.html',
                );
            }

            if( defined( 'YITH_WCFM_PREMIUM' ) ){
                $links[] =  array(
                    'title' => __( 'Support platform', 'yith-frontend-manager-for-woocommerce' ),
                    'url'   => 'https://yithemes.com/my-account/support/dashboard/',
                );

                $links[] =  array(
                    'title' => sprintf( '%s (%s %s)', __( 'Changelog', 'yith-frontend-manager-for-woocommerce' ), __( 'current version','yith-frontend-manager-for-woocommerce' ), YITH_WCFM_VERSION ),
                    'url'   => 'http://yithemes.com/docs-plugins/yith-woocommerce-frontend-manager/15-changelog-premium.html',
                );;
            }

            return $links;
        }


        /**
         * Action Links
         *
         * add the action links to plugin admin page
         *
         * @param $links | links plugin array
         *
         * @return   mixed Array
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @return mixed
         * @use      plugin_action_links_{$plugin_file_name}
         */
        public function action_links( $links ) {
            $links[]           = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'yith-frontend-manager-for-woocommerce' ) . '</a>';
            //$premium_live_text = defined( 'YITH_WPV_FREE_INIT' ) ? __( 'Premium live demo', 'yith-frontend-manager-for-woocommerce' ) : __( 'Live demo', 'yith-frontend-manager-for-woocommerce' );
            //$links[]           = '<a href="' . $this->_premium_live . '" target="_blank">' . $premium_live_text . '</a>';

            if ( defined( 'YITH_WCFM_FREE_INIT' ) ) {
                $links[] = '<a href="' . $this->get_premium_landing_uri() . '" target="_blank">' . __( 'Premium Version', 'yith-frontend-manager-for-woocommerce' ) . '</a>';
            }

            return $links;
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
        public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {

            if ( ( defined( 'YITH_WCFM_INIT' ) && YITH_WCFM_INIT == $plugin_file ) || ( defined( 'YITH_WCFM_FREE_INIT' ) && YITH_WCFM_FREE_INIT == $plugin_file ) ) {
                $plugin_meta[] = '<a href="' . $this->_official_documentation . '" target="_blank">' . __( 'Plugin Documentation', 'yith_wc_product_vendors' ) . '</a>';
            }

            return $plugin_meta;
        }

        /**
         * Get the premium landing uri
         *
         * @since   1.0.0
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  string The premium landing link
         */
        public function get_premium_landing_uri() {
            return defined( 'YITH_REFER_ID' ) ? $this->_premium_landing . '?refer_id=' . YITH_REFER_ID : $this->_premium_landing;
        }

        /**
         * Show the premium tabs
         *
         * @since   1.0.0
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  string The premium landing link
         */
        public function show_premium_tab() {
            yith_wcfm_get_template( 'premium', array(), 'admin' );
        }

        /**
         * Set a transient if the admin change the frontend manager endpoint
         *
         * @since   1.0.0
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  string The premium landing link
         */
        public function check_save_endpoint( $option, $old_value, $new_value ){
            //@TODO: Check if transient works fine
            $regex = '/yith_wcfm_.*_section_slug/';
            $endpoint_is_changed = get_site_transient( YITH_Frontend_Manager()->get_rewrite_rules_transient() );
            if( ! $endpoint_is_changed && $old_value !== $new_value && preg_match( $regex, $option ) ){
                set_site_transient( YITH_Frontend_Manager()->get_rewrite_rules_transient(), true );
            }
        }

        /**
         * Add admin bar menu item
         *
         * @since   1.0.0
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  bool false or nothing
         */
        public function admin_bar_menus( $wp_admin_bar ){
	        /**
	         * if is on frontend or user not logged in: Stop!
	         */
            if ( ! is_admin() || ! is_user_logged_in() ) {
	            return false;
            }

	        /**
	         * Show only when the user is a member of this site, or they're a super admin.
	         */
            if ( ! is_user_member_of_blog() && ! is_super_admin() ) {
	            return false;
            }

            $main_page_url = yith_wcfm_get_main_page_url();

	        /**
	         * is Frontend Manager main page url set
	         */
            if( empty( $main_page_url ) ){
	            return false;
            }

            if( ! YITH_Frontend_Manager()->current_user_can_manage_woocommerce_on_front() ){
                return false;
            }

            // Add an option to visit frontend manager page
            $wp_admin_bar->add_node( array(
                'parent' => 'site-name',
                'id'     => 'view-frontend-manager',
                'title'  => __( 'Frontend Manager', 'yith-frontend-manager-for-woocommerce' ),
                'href'   => $main_page_url,
            ) );
        }

        /**
         * Add the custom typoe option "button"
         *
         * @param $value field value
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0
         * @return void
         */
        public function admin_field_button( $value ) {
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                </th>
                <td class="forminp">
                    <input type="button" name="force_review" id="<?php echo $value['id'] ?>" value="<?php echo $value['name'] ?>" class="button-secondary" />
                    <span class="description with-spinner">
                        <?php echo $value['desc']; ?>
                    </span>
                    <span class="spinner"></span>
                </td>
            </tr>
            <?php
        }
    }
}