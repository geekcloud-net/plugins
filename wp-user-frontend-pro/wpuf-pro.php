<?php
/*
Plugin Name: WP User Frontend Pro - personal
Plugin URI: https://wedevs.com/wp-user-frontend-pro/
Description: The paid module to add extra features on WP User Frontend.
Author: weDevs
Version: 2.8.0
Author URI: https://wedevs.com
License: GPL2
TextDomain: wpuf-pro
Domain Path: /languages/
*/

class WP_User_Frontend_Pro {

    /**
     * Package plan
     *
     * @since 2.6.1
     */
    private $plan = 'wpuf-personal';

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct() {
        if ( ! class_exists( 'WP_User_Frontend' ) ) {
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            add_action( 'admin_notices', array( $this, 'wpuf_activation_notice' ) );
            add_action( 'wp_ajax_wpuf_pro_install_wp_user_frontend', array( $this, 'install_wp_user_frontend' ) );
            return;
        }

        // Define constants
        $this->define_constants();

        // Include files
        $this->includes();

        // Instantiate classes
        $this->instantiate();

        // Initialize the action hooks
        $this->init_actions();
    }

    /**
     * Initializes the WP_User_Frontend_Pro() class
     *
     * Checks for an existing WeDevs_WeDevs_Dokan() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new WP_User_Frontend_Pro();
        }

        return $instance;
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public static function activate() {
        $this->maybe_activate_modules();
    }

    /**
    * Maybe Activate modules
    *
    * @since 1.0.0
    *
    * @return void
    **/
    public function maybe_activate_modules() {
        global $wpdb;

        $has_installed = $wpdb->get_row( "SELECT option_id FROM {$wpdb->options} WHERE option_name = 'wpuf_pro_active_modules'" );

        if ( $has_installed ) {
            return;
        }

        if ( ! function_exists( 'wpuf_pro_get_modules' ) ) {
            require_once WPUF_PRO_INCLUDES . '/modules.php';
        }

        $modules = wpuf_pro_get_modules();

        if ( $modules ) {
            foreach ($modules as $module_file => $data) {
                wpuf_pro_activate_module( $module_file );
            }
        }
    }

    /**
     * Show wordpress error notice if WP User Frontend not found
     *
     * @since 2.4.2
     */
    public function wpuf_activation_notice() {
        ?>
        <div class="updated" id="wpuf-pro-installer-notice" style="padding: 1em; position: relative;">
            <h2><?php _e( 'Your WP User Frontend Pro is almost ready!', 'wpuf-pro' ); ?></h2>

            <?php
                $plugin_file      = basename( dirname( __FILE__ ) ) . '/wpuf-pro.php';
                $core_plugin_file = 'wp-user-frontend/wpuf.php';
            ?>
            <a href="<?php echo wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . $plugin_file . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'deactivate-plugin_' . $plugin_file ); ?>" class="notice-dismiss" style="text-decoration: none;" title="<?php _e( 'Dismiss this notice', 'wpuf-pro' ); ?>"></a>

            <?php if ( file_exists( WP_PLUGIN_DIR . '/' . $core_plugin_file ) && is_plugin_inactive( 'wpuf-user-frontend' ) ): ?>
                <p><?php _e( 'You just need to activate the Core Plugin to make it functional.', 'wpuf-pro' ); ?></p>
                <p>
                    <a class="button button-primary" href="<?php echo wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $core_plugin_file . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'activate-plugin_' . $core_plugin_file ); ?>"  title="<?php _e( 'Activate this plugin', 'wpuf-pro' ); ?>"><?php _e( 'Activate', 'wpuf-pro' ); ?></a>
                </p>
            <?php else: ?>
                <p><?php echo sprintf( __( "You just need to install the %sCore Plugin%s to make it functional.", "wpuf-pro" ), '<a target="_blank" href="https://wordpress.org/plugins/wp-user-frontend/">', '</a>' ); ?></p>

                <p>
                    <button id="wpuf-pro-installer" class="button"><?php _e( 'Install Now', 'wpuf-pro' ); ?></button>
                </p>
            <?php endif; ?>

        </div>

        <script type="text/javascript">
            (function ($) {
                var wrapper = $('#wpuf-pro-installer-notice');

                wrapper.on('click', '#wpuf-pro-installer', function (e) {
                    var self = $(this);

                    e.preventDefault();
                    self.addClass('install-now updating-message');
                    self.text('<?php echo esc_js( 'Installing...', 'wpuf-pro' ); ?>');

                    var data = {
                        action: 'wpuf_pro_install_wp_user_frontend',
                        _wpnonce: '<?php echo wp_create_nonce('wpuf-pro-installer-nonce'); ?>'
                    };

                    $.post(ajaxurl, data, function (response) {
                        if (response.success) {
                            self.attr('disabled', 'disabled');
                            self.removeClass('install-now updating-message');
                            self.text('<?php echo esc_js( 'Installed', 'wpuf-pro' ); ?>');

                            window.location.reload();
                        }
                    });
                });
            })(jQuery);
        </script>
        <?php
    }

    /**
     * Install the WP User Frontend plugin via ajax
     *
     * @since 2.4.2
     *
     * @return json
     */
    public function install_wp_user_frontend() {

        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wpuf-pro-installer-nonce' ) ) {
            wp_send_json_error( __( 'Error: Nonce verification failed', 'wpuf-pro' ) );
        }

        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $plugin = 'wp-user-frontend';
        $api    = plugins_api( 'plugin_information', array( 'slug' => $plugin, 'fields' => array( 'sections' => false ) ) );

        $upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
        $result   = $upgrader->install( $api->download_link );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result );
        }

        $result = activate_plugin( 'wp-user-frontend/wpuf.php' );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result );
        }

        wp_send_json_success();
    }

    /**
     * Define the constants
     *
     * @return void
     */
    private function define_constants() {
        define( 'WPUF_PRO_VERSION', '2.8.0' );
        define( 'WPUF_PRO_FILE', __FILE__ );
        define( 'WPUF_PRO_ROOT', dirname( __FILE__ ) );
        define( 'WPUF_PRO_INCLUDES', WPUF_PRO_ROOT . '/includes' );
        define( 'WPUF_PRO_MODULES', WPUF_PRO_ROOT . '/modules' );
        define( 'WPUF_PRO_ROOT_URI', plugins_url( '', __FILE__ ) );
        define( 'WPUF_PRO_ASSET_URI', WPUF_PRO_ROOT_URI . '/assets' );
    }

    /**
     * Include the files
     *
     * @return void
     */
    public function includes() {
        require_once WPUF_ROOT . '/class/render-form.php';

        if ( !class_exists('WPUF_Login')) {
            require_once WPUF_PRO_INCLUDES . '/login.php';
        }

        require_once WPUF_PRO_INCLUDES . '/frontend-form-profile.php';
        require_once WPUF_PRO_INCLUDES . '/updates.php';

        if ( is_admin() ) {
            require_once WPUF_ROOT . '/admin/posting.php';
            require_once WPUF_ROOT . '/admin/template.php';
            require_once WPUF_ROOT . '/admin/installer.php';
            require_once WPUF_ROOT . '/admin/template-post.php';
            require_once WPUF_ROOT . '/class/subscription.php';

            require_once WPUF_PRO_ROOT . '/admin/coupon.php';
            require_once WPUF_PRO_ROOT . '/admin/coupon-element.php';
            require_once WPUF_PRO_ROOT . '/admin/posting-profile.php';
            require_once WPUF_PRO_ROOT . '/admin/template-profile.php';
            require_once WPUF_PRO_ROOT . '/admin/pro-page-installer.php';
            require_once WPUF_PRO_ROOT . '/admin/profile-forms-list-table.php';

            require_once WPUF_PRO_ROOT . '/includes/class-whats-new.php';
        }

        require_once WPUF_ROOT . '/admin/template.php';
        require_once WPUF_ROOT . '/admin/template-post.php';
        require_once WPUF_PRO_ROOT . '/admin/profile-form-template.php';
        require_once WPUF_ROOT . '/class/subscription.php';

        //class files to include pro elements

        require_once WPUF_PRO_INCLUDES . '/class-invoice.php';
        require_once WPUF_PRO_INCLUDES . '/class-invoice-frontend.php';
        require_once WPUF_PRO_INCLUDES . '/class-render-form.php';
        require_once WPUF_PRO_INCLUDES . '/form.php';
        require_once WPUF_PRO_INCLUDES . '/profile-form.php';
        require_once WPUF_PRO_INCLUDES . '/form-element.php';
        require_once WPUF_PRO_INCLUDES . '/content-restriction.php';
        require_once WPUF_PRO_INCLUDES . '/class-subscription.php';
        require_once WPUF_PRO_INCLUDES . '/coupons.php';
        require_once WPUF_PRO_INCLUDES . '/render-form.php';
        require_once WPUF_PRO_INCLUDES . '/class-menu-restriction.php';
        require_once WPUF_PRO_INCLUDES . '/class-modules.php';
        require_once WPUF_PRO_INCLUDES . '/class-taxonomy-restriction.php';
        require_once WPUF_PRO_INCLUDES . '/class-new-user-approve.php';

        if ( ! function_exists( 'wpuf_pro_get_active_modules' ) ) {
            require_once WPUF_PRO_INCLUDES . '/modules.php';
        }

        // load all the active modules
        $modules = wpuf_pro_get_active_modules();

        if ( $modules ) {
            foreach ($modules as $module_file) {
                $module_path = WPUF_PRO_MODULES . '/' . $module_file;

                if ( file_exists( $module_path ) ) {
                    include_once $module_path;
                }
            }
        }
    }

    /**
     * Instantiate the classes
     *
     * @return void
     */
    public function instantiate(){

        if ( !class_exists( 'WPUF_Login' ) ) {
            WPUF_Login::init();
        }

        new WPUF_Menu_Restriction();
        new WPUF_Frontend_Form_Profile();
        new WPUF_Admin_Profile_Form_Template();
        new WPUF_Content_Restriction();
        new WPUF_Pro_Render_Form();
        new WPUF_Pro_invoice();
        new WPUF_Taxonomy_Restriction();
        new WPUF_Pro_Subscription();
        new WPUF_New_User_Approve();

        if ( is_admin() ) {
            new WPUF_Admin_Form_Pro();
            new WPUF_Admin_Profile_Form_Pro();
            new WPUF_Admin_Posting_Profile();
            new WPUF_Admin_Coupon();
            new WPUF_Pro_Modules();
            new WPUF_Pro_Whats_New();
            WPUF_Coupons::init();
        }

        new WPUF_Updates( $this->plan );
    }

    /**
     * Init the action/filter hooks
     *
     * @return void
     */
    public function init_actions() {
        add_action( 'init', array( $this, 'load_textdomain') );

        //coupon
        add_action( 'wpuf_coupon_settings_form', array($this, 'wpuf_coupon_settings_form_runner'),10,1 );
        add_action( 'wpuf_check_save_permission', array($this, 'wpuf_check_save_permission_runner'),10,2 );

        // admin menu
        add_action( 'wpuf_admin_menu_top', array($this, 'admin_menu_top') );
        add_action( 'wpuf_admin_menu', array($this, 'admin_menu') );
        add_action( 'wpuf_admin_menu_bottom', array($this, 'admin_menu_bottom') );

        // Pro settings
        add_filter( 'wpuf_options_others', array($this, 'wpuf_pro_settings_fields') );

        //page install
        add_filter( 'wpuf_pro_page_install' , array( $this, 'install_pro_pages' ), 10, 1 );

        //show custom html in frontend
        add_filter( 'wpuf_custom_field_render', array( $this, 'render_custom_fields' ), 99, 4 );

        // post form templates
        add_action( 'wpuf_get_post_form_templates', array($this, 'post_form_templates') );
    }


    /**
     * Load the translation file for current language.
     *
     * @since version 0.7
     * @author Tareq Hasan
     */
    function load_textdomain() {
        load_plugin_textdomain( 'wpuf-pro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Callback method for WP User Frontend submenu
     *
     * @since 2.5
     *
     * @return void
     */
    function admin_menu_top() {
        $capability = wpuf_admin_role();

        $profile_forms_page = add_submenu_page( 'wp-user-frontend', __( 'Registration Forms', 'wpuf-pro' ), __( 'Registration Forms' ), $capability, 'wpuf-profile-forms', array( $this, 'wpuf_profile_forms_page' ) );
        add_action( "load-$profile_forms_page", array( $this, 'footer_styles' ) );
    }

    /**
     * Callback method for WP User Frontend submenu
     *
     * @since 2.5
     *
     * @return void
     */
    function admin_menu() {
        if ( 'on' == wpuf_get_option( 'enable_payment', 'wpuf_payment', 'on' ) ) {
            $capability = wpuf_admin_role();
            add_submenu_page( 'wp-user-frontend', __( 'Coupons', 'wpuf-pro' ), __( 'Coupons', 'wpuf-pro' ), $capability, 'edit.php?post_type=wpuf_coupon' );
        }
    }

    /**
     * Callback method for WP User Frontend submenu
     *
     * @since 2.6
     *
     * @return void
     */
    function admin_menu_bottom() {
        $capability = wpuf_admin_role();

        $modules = add_submenu_page( 'wp-user-frontend', __( 'Modules', 'wpuf-pro' ), __( 'Modules', '' ), $capability, 'wpuf-modules', array( $this, 'modules_page' ) );
        add_action( $modules, array( $this, 'modules_scripts' ) );
    }

    /**
     * Callback method for Profile Forms submenu
     *
     * @since 2.5
     *
     * @return void
     */
    function wpuf_profile_forms_page() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : null;
        $add_new_page_url = admin_url( 'admin.php?page=wpuf-profile-forms&action=add-new' );

        switch ( $action ) {
            case 'edit':
                require_once WPUF_PRO_ROOT . '/views/profile-form.php';
                break;

            case 'add-new':
                require_once WPUF_PRO_ROOT . '/views/profile-form.php';
                break;

            default:
                require_once WPUF_PRO_ROOT . '/admin/profile-forms-list-table-view.php';
                break;
        }
    }

    function footer_styles() {
        echo '<style type="text/css">
            .column-user_role { width:12% !important; overflow:hidden }
        </style>';
    }

    /**
     * Modules Page
     *
     * @since 2.7
     *
     * @return void
     **/
    public function modules_page() {
        include dirname( __FILE__ ) . '/admin/modules.php';
    }

    /**
     * Modules Scripts
     *
     * @since 2.7
     *
     * @return void
     **/
    function modules_scripts() {
        wp_enqueue_style( 'wpuf-pro-modules', WPUF_PRO_ASSET_URI . '/css/wpuf-module.css' );
        wp_enqueue_script( 'jquery-blockui', WPUF_PRO_ASSET_URI . '/js/jquery.blockUI.min.js', array( 'jquery' ), null, true );
        wp_enqueue_script( 'wpuf_pro_admin', WPUF_PRO_ASSET_URI.'/js/wpuf-module.js', array( 'jquery', 'jquery-blockui' ) );

        $wpuf_module = apply_filters( 'wpuf_module_localize_param', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'wpuf-admin-nonce' ),
            'activating' => __( 'Activating', 'wpuf-pro' ),
            'deactivating' => __( 'Deactivating', 'wpuf-pro' )
        ) );

        wp_localize_script( 'wpuf_pro_admin', 'wpuf_module', $wpuf_module );
    }

    function wpuf_pro_settings_fields( $wpuf_general_fields ){

        $pro_settings_fields = array(
            array(
                'name'  => 'gmap_api_key',
                'label' => __( 'Google Map API', 'wpuf' ),
                'desc'  => __( '<a target="_blank" href="https://developers.google.com/maps/documentation/javascript">API</a> key is needed to render Google Maps', 'wpuf' ),
            )
        );

        $wpuf_general_settings = array_merge( $wpuf_general_fields, $pro_settings_fields );

        return $wpuf_general_settings;
    }

    //coupon
    public function wpuf_coupon_settings_form_runner( $obj ) {
        WPUF_pro_Coupon_Elements::add_coupon_elements( $obj );
    }

    public function wpuf_check_save_permission_runner( $post, $update ) {
        WPUF_pro_Coupon_Elements::check_saving_capability( $post, $update );
    }

    //install pro version page
    function install_pro_pages( $profile_options ) {
        $wpuf_pro_page_installer = new wpuf_pro_page_installer();
        return $wpuf_pro_page_installer->install_pro_version_pages( $profile_options );
    }

    /**
     * show custom html
     */
    function render_custom_fields( $html, $value, $attr, $form_settings ) {

        switch( $attr['input_type']) {
            case 'ratings':
                $ratings_html = '';

                $ratings_html .= '<select name="'.$attr['name'].'" class="wpuf-ratings">';
                    foreach( $attr['options'] as $key => $option ) :
                        $ratings_html .= '<option value="'.$key.'" ' .( in_array( $key, $value ) ? 'selected' : '' ) . '>' .$option. '</option>';
                    endforeach;
                $ratings_html .= '</select>';

                $html .= '<li><label>' . $attr['label'] . ': </label> ';
                $html .= ' '.$ratings_html.'</li>';

                $js = '<script type="text/javascript">';
                    $js .= 'jQuery(function($) {';
                        $js .= '$(".wpuf-ratings").barrating({';
                            $js .= 'theme: "css-stars",';
                            $js .= 'readonly: true';
                        $js .= '});';
                    $js .= '});';
                $js .= '</script>';

                $html .= $js;
                break;

            case 'repeat':
                $multiple = ( isset( $attr['multiple'] ) && $attr['multiple'] == 'true' ) ? true : false;

                if ( ! $multiple ) {
                    $value = isset( $value['0'] ) ? $value['0'] : '';
                    $value = explode( WPUF_Render_Form::$separator, $value );
                    $html .= sprintf( '<li><label>%s</label>: %s</li>', $attr['label'], implode( ', ', $value ) );
                }

                break;
        }

        return $html;
    }


    /**
     * Post form templates
     *
     * @since 2.4
     *
     * @param  array $integrations
     *
     * @return array
     */
    public function post_form_templates( $integrations ) {
        require_once WPUF_PRO_INCLUDES . '/post-form-templates/woocommerce.php';

        $integrations['WPUF_Post_Form_Template_WooCommerce'] = new WPUF_Post_Form_Template_WooCommerce();

        return $integrations;
    }

}

/**
 * Load WPUF Pro Plugin when all plugins loaded
 *
 * @return void
 */
function wpuf_pro_load_plugin() {
    WP_User_Frontend_Pro::init();
}

add_action( 'plugins_loaded', 'wpuf_pro_load_plugin' );
