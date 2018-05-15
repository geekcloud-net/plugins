<?php

if (!defined('ABSPATH')) exit;

class WC_POS
{

    /**
     * The single instance of WC_POS.
     * @var     object
     * @access  private
     * @since 1.9
     */
    private static $_instance = null;

    /**
     * The version number.
     * @var     string
     * @access  public
     * @since    3.0.5
     */
    public $_version;

    /**
     * @var string
     */
    public $db_version = '3.2.1';

    /**
     * The token.
     * @var     string
     * @access  public
     * @since    3.0.5
     */
    public $_token;

    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since    3.0.5
     */
    public $file;

    /**
     * The main plugin directory.
     * @var     string
     * @access  public
     * @since    3.0.5
     */
    public $dir;

    /**
     * The plugin assets directory.
     * @var     string
     * @access  public
     * @since    3.0.5
     */
    public $assets_dir;

    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since    3.0.5
     */
    public $assets_url;

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     */
    public $script_suffix;

    /**
     * @var bool
     */
    public $is_pos = null;

    /**
     * @var bool
     */
    public $wc_api_is_active = false;

    /**
     * @var string
     */
    public $permalink_structure = '';

    public $users = null;
    /**
     * The plugin's ids
     * @var string
     */
    public $id = 'wc_point_of_sale';
    public $id_outlets = 'wc_pos_outlets';
    public $id_registers = 'wc_pos_registers';
    public $id_grids = 'wc_pos_grids';
    public $id_tiles = 'wc_pos_tiles';
    public $id_users = 'wc_pos_users';
    public $id_receipts = 'wc_pos_receipts';
    public $id_barcodes = 'wc_pos_barcodes';
    public $id_stock_c = 'wc_pos_stock_controller';
    public $id_settings = 'wc_pos_settings';
    public $id_session_reports = 'wc_pos_session_reports';

    /**
     * Constructor function.
     * @access  public
     * @return  void
     */
    public function __construct($file = '', $version = '1.0.0')
    {

        $this->tables = array();
        $this->_version = $version;
        $this->_token = 'wc_pos';

        // Load plugin environment variables
        $this->file = $file;
        $this->dir = dirname($this->file);
        $this->assets_dir = trailingslashit($this->dir) . 'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));

        //$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        $this->script_suffix = '';

        $this->define_constants();
        $this->load_plugin_textdomain();
        $this->includes();
        $this->init_hooks();

        $this->users = $this->user();
        do_action('woocommerce_poin_of_sale_loaded');
    } // End __construct () 

    /**
     * Define WC_POS Constants
     */
    private function define_constants()
    {
        $upload_dir = wp_upload_dir();

        $this->define('WC_POS_FILE', $this->file);
        $this->define('WC_POS_PLUGIN_FILE', $this->file);
        $this->define('WC_POS_BASENAME', plugin_basename($this->file));
        $this->define('WC_POS_DIR', $this->dir);
        $this->define('WC_POS_VERSION', $this->_version);
        $this->define('WC_POS_TOKEN', $this->_token);

    }

    /**
     * Define constant if not already set
     * @param  string $name
     * @param  string|bool $value
     */
    private function define($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
     * What type of request is this?
     * string $type ajax, frontend or admin
     * @return bool
     */
    private function is_request($type)
    {
        switch ($type) {
            case 'admin' :
                return is_admin();
            case 'ajax' :
                return defined('DOING_AJAX');
            case 'cron' :
                return defined('DOING_CRON');
            case 'frontend' :
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
        }
    }

    /**
     * Include required core files used in admin and on the frontend.
     */
    public function includes()
    {
        include_once('class-wc-pos-autoloader.php');
        include_once('core-functions.php');
        include_once('grids-functions.php');
        include_once('tiles-functions.php');
        include_once('class-wc-pos-install.php');
        include_once('admin/class-wc-pos-admin.php');
        include_once('class-wc-pos-float-cash.php');
        include_once('class-wc-pos-bill-screen.php');
        include_once('cache/class-wc-pos-cache.php');

        // frontend only
        if (!is_admin()) {
            include_once('class-wc-pos-sell.php');
        }
        if (defined('DOING_AJAX')) {
            $this->ajax_includes();
        }
    }

    /**
     * Include required ajax files.
     */
    public function ajax_includes()
    {
        include_once('class-wc-pos-ajax.php');         // Ajax functions for admin and the front-end
    }

    /**
     * Hook into actions and filters
     */
    private function init_hooks()
    {

        $this->wc_api_is_active = $this->check_api_active();
        $this->permalink_structure = get_option('permalink_structure');


        register_activation_hook($this->file, array($this, 'install'));
        add_action('init', array($this, 'load_localisation'), 0);
        add_action('admin_init', array($this, 'print_report'), 100);
        add_action('init', array($this, 'check_pos_custom_product_exists'));
        add_action('init', array($this, 'check_pos_visibility_products'));
        //add_action('init', array($this, 'check_db_updates'));
        add_action('init', array($this, 'check_connection_status_option'));
        //Pos only products
        add_action('init', array($this, 'wc_pos_visibility_action'));
        add_action('woocommerce_loaded', array($this, 'change_stock_amount'), 0);

        add_action('admin_notices', array($this, 'admin_notices'), 20);

        if ((isset($_POST['register_id']) && !empty($_POST['register_id'])) || (isset($_GET['page']) && $_GET['page'] == 'wc_pos_registers' && isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id']) && !empty($_GET['action']))) {
            add_filter('woocommerce_customer_taxable_address', array($this, 'set_outlet_taxable_address'));
        }

        add_filter('woocommerce_attribute_label', array($this, 'tile_attribute_label'));
        add_filter('woocommerce_get_checkout_order_received_url', array($this, 'order_received_url'));
        add_filter('woocommerce_email_actions', array($this, 'woocommerce_email_actions'), 150, 1);

        add_filter('request', array($this, 'orders_by_order_type'));

        add_filter('woocommerce_admin_order_actions', array($this, 'order_actions_reprint_receipts'), 2, 20);
        add_filter('woocommerce_order_number', array($this, 'add_prefix_suffix_order_number'), 10, 2);

        add_action('woocommerce_loaded', array($this, 'woocommerce_delete_shop_order_transients'));
        add_action('admin_init', array($this, 'add_caps'), 20, 4);

        add_action('woocommerce_hidden_order_itemmeta', array($this, 'hidden_order_itemmeta'), 150, 1);

        //WC_Subscriptions Compatibility
        if (in_array('woocommerce-subscriptions/woocommerce-subscriptions.php', get_option('active_plugins'))) {
            add_filter('woocommerce_subscription_payment_method_to_display', array($this, 'get_subscription_payment_method'), 10, 2);
        }
        //Pos custom product
        add_action('pre_get_posts', array($this, 'hide_pos_custom_product'), 15, 1);


        // Load admin JS & CSS
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 10, 1);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_styles'), 10, 1);
        add_action('admin_print_scripts', array($this, 'admin_inline_js'));
        add_filter('woocommerce_pos_register_discount_presets', array($this, 'add_custom_discounts'));
    }

    function wc_pos_visibility_action()
    {
        if (get_option('wc_pos_visibility', 'no') == 'yes') {
            add_action('pre_get_posts', array($this, 'pos_only_products'), 15, 1);
            add_filter('views_edit-product', array($this, 'add_pos_only_filter'));
        }
    }

    function admin_inline_js()
    {
        echo "<script type='text/javascript'>\n";
        echo 'var wc_version = ' . intval(WC_VERSION) . ';';
        echo "\n</script>";
    }

    public function hide_pos_custom_product($query)
    {
        $query->set('post__not_in', array((int)get_option('wc_pos_custom_product_id')));
    }

    public function pos_only_products($query)
    {
        if (!isset($_GET['filter']['updated_at_min']) && !is_admin()
            && (isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'product')
            || (is_product_category() && !isset($query->query_vars['post_type']))
            || is_product_tag()
        ) {
            $meta_query = array(
                'relation' => 'OR',
                array(
                    'key' => '_pos_visibility',
                    'value' => 'pos',
                    'compare' => '!=',
                ),
                //UberMenu conflict - query with NOT EXISTS statements very slow. Added check_pos_visibility_products() function to improve speed.
                /*array(
                    'key' => '_pos_visibility',
                    'compare' => 'NOT EXISTS',
                ),*/
            );
            $query->set('meta_query', $meta_query);
        }
        if (isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'product' && isset($_GET['pos_only'])) {
            $meta_query = array(
                'relation' => 'OR',
                array(
                    'key' => '_pos_visibility',
                    'value' => 'pos',
                    'compare' => '=',
                ),
            );
            $query->set('meta_query', $meta_query);
        }
        if (isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'product' && isset($_GET['online_only'])) {
            $meta_query = array(
                'relation' => 'OR',
                array(
                    'key' => '_pos_visibility',
                    'value' => 'online',
                    'compare' => '=',
                ),
            );
            $query->set('meta_query', $meta_query);
        }
    }

    function add_pos_only_filter($views)
    {
        global $post_type_object;
        $post_type = $post_type_object->name;
        global $wpdb;
        //Pos only count
        $sql = "SELECT COUNT(post_id) FROM $wpdb->postmeta WHERE meta_key = '_pos_visibility' AND meta_value = 'pos'";
        $count = ($count = $wpdb->get_var($sql)) ? $count : 0;
        if ($count) {
            $class = (isset($_GET['pos_only'])) ? 'current' : '';
            $views['pos_only'] = "<a href='edit.php?post_type=$post_type&pos_only=1' class='$class'>" . __('POS Only', 'wc_point_of_sale') . " ({$count}) " . "</a>";
        }
        //Online only count
        $sql = "SELECT COUNT(post_id) FROM $wpdb->postmeta WHERE meta_key = '_pos_visibility' AND meta_value = 'online'";
        $count = ($count = $wpdb->get_var($sql)) ? $count : 0;
        if ($count) {
            $class = (isset($_GET['online_only'])) ? 'current' : '';
            $views['online_only'] = "<a href='edit.php?post_type=$post_type&online_only=1' class='$class'>" . __('Online Only', 'wc_point_of_sale') . " ({$count}) " . "</a>";
        }
        return $views;
    }

    /**
     * Load admin CSS.
     * @access  public
     * @return  void
     */
    public function admin_enqueue_styles($hook = '')
    {

        $wc_pos_version = $this->_version;
        wp_enqueue_style('wc-pos-fonts', $this->plugin_url() . '/assets/css/fonts.css', array(), $wc_pos_version);
        if (pos_admin_page()) {
            /****** START STYLE *****/
            wp_enqueue_style('thickbox');
            wp_enqueue_style('jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

            wp_enqueue_style('woocommerce_frontend_styles', WC()->plugin_url() . '/assets/css/admin.css');

            wp_enqueue_style('woocommerce-style', WC()->plugin_url() . '/assets/css/woocommerce-layout.css', array(), $wc_pos_version);
            wp_enqueue_style('wc-pos-style', $this->plugin_url() . '/assets/css/admin.css', array(), $wc_pos_version);
        }
        if (pos_barcodes_admin_page()) {
            wp_enqueue_style('wc-pos-barcode-options', $this->plugin_url() . '/assets/css/barcode-options.css', array(), $wc_pos_version);
        }
        if (pos_shop_order_page()) {
            wp_enqueue_style('wc-pos-style', $this->plugin_url() . '/assets/css/admin.css', array(), $wc_pos_version);
        }
        if (pos_receipts_admin_page() && isset($_GET['action']) && ($_GET['action'] == 'edit' || $_GET['action'] == 'add')) {
            wp_enqueue_style('codemirror-css', $this->plugin_url() . '/assets/plugins/codemirror/codemirror.css', array(), $wc_pos_version);
        }
        wp_enqueue_style('wc-pos-print', $this->plugin_url() . '/assets/css/print.css', array(), $wc_pos_version);

    } // End admin_enqueue_styles ()

    /**
     * Load admin Javascript.
     * @access  public
     * @return  void
     */
    public function admin_enqueue_scripts($hook = '')
    {
        global $post_type;

        $wc_pos_version = $this->_version;
        $scripts = array('jquery', 'wc-enhanced-select', 'jquery-blockui', 'jquery-tiptip', 'woocommerce_admin');
        if (pos_admin_page()) {
            wp_enqueue_script(array('jquery', 'editor', 'thickbox', 'jquery-ui-core', 'jquery-ui-datepicker'));

            wp_enqueue_script('postbox_', admin_url() . '/js/postbox.min.js', array(), '2.66');

            if (pos_tiles_admin_page()) {
                wp_enqueue_media();
                wp_enqueue_script('custom-background');
                wp_enqueue_style('wp-color-picker');
                wp_enqueue_script('jquery_cycle', $this->plugin_url() . '/assets/plugins/jquery.cycle.all.js', array('jquery'), $wc_pos_version);
                wp_enqueue_script('pos-colormin', $this->plugin_url() . '/assets/js/colormin.js', array('jquery'), $wc_pos_version);

                wp_enqueue_script('pos-script-tile-ordering', $this->plugin_url() . '/assets/js/tile-ordering.js', array('jquery'), $wc_pos_version);

            }

            if (pos_receipts_admin_page() && isset($_GET['action']) && ($_GET['action'] == 'edit' || $_GET['action'] == 'add')) {
                wp_enqueue_media();
                wp_enqueue_script('postbox');

                $deps = array('jquery', 'codemirror', 'codemirror-css');

                wp_register_script('codemirror', $this->plugin_url() . '/assets/plugins/codemirror/codemirror.js', array(), $wc_pos_version);
                wp_register_script('codemirror-css', $this->plugin_url() . '/assets/plugins/codemirror/css.js', array(), $wc_pos_version);

                wp_enqueue_script('pos-script-receipt_options', $this->plugin_url() . '/assets/js/receipt_options.js', $deps, $wc_pos_version);
                wp_localize_script('pos-script-receipt_options', 'wc_pos_receipt', array(
                    'pos_receipt_style' => $this->receipt()->get_style_templates()
                ));

            }
            if (pos_barcodes_admin_page()) {
                wp_enqueue_script('pos-script-barcode_options', $this->plugin_url() . '/assets/js/barcode-options.js', array('jquery'), $wc_pos_version);
                wp_localize_script('pos-script-barcode_options', 'wc_pos_barcode', array(
                    'ajax_url' => WC()->ajax_url(),
                    'barcode_url' => $this->barcode_url(),
                    'product_for_barcode_nonce' => wp_create_nonce('product_for_barcode'),
                    'remove_item_notice' => __('Are you sure you want to remove the selected items?', 'wc_point_of_sale'),
                    'select_placeholder_category' => __('Search for a category&hellip;', 'wc_point_of_sale'),
                ));
            }
            if (pos_settings_admin_page()) {
                wp_enqueue_media();
            }

            wp_enqueue_script('wc-pos-handlebars-admin', $this->plugin_url() . '/assets/js/register/handlebars/handlebars.min.js', $scripts, $wc_pos_version);
            wp_enqueue_script('wc-pos-script-admin', $this->plugin_url() . '/assets/js/admin.js', $scripts, $wc_pos_version);
            pos_localize_script('wc-pos-script-admin');

        }
        if (pos_shop_order_page()) {
            if (!wp_script_is('jquery', 'enqueued'))
                wp_enqueue_script('jquery');

            wp_enqueue_script('jquery_barcodelistener', $this->plugin_url() . '/assets/plugins/anysearch.js', array('jquery'), $wc_pos_version);
            wp_enqueue_script('wc-pos-script-admin', $this->plugin_url() . '/assets/js/admin.js', $scripts, $wc_pos_version); // R1 Software - Scan Orders Fix 
            pos_localize_script('wc-pos-script-admin');
            wp_enqueue_script('wc-pos-shop-order-page-script', $this->plugin_url() . '/assets/js/shop-order-page-script.js', array('jquery'), $wc_pos_version);
        }
        if (isset($_GET['page']) && $_GET['page'] == $this->id_stock_c) {
            wp_enqueue_script('jquery_barcodelistener', $this->plugin_url() . '/assets/plugins/anysearch.js', array('jquery'), $wc_pos_version);
        }

    } // End admin_enqueue_scripts ()

    /**
     * Load plugin localisation
     * @access  public
     * @return  void
     */
    public function load_localisation()
    {
        load_plugin_textdomain('wc_point_of_sale', false, dirname(plugin_basename($this->file)) . '/lang/');
    } // End load_localisation ()

    /**
     * Load plugin textdomain
     * @access  public
     * @return  void
     */
    public function load_plugin_textdomain()
    {
        $domain = 'wc_point_of_sale';

        $locale = apply_filters('plugin_locale', get_locale(), $domain);

        load_textdomain($domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo');
        load_plugin_textdomain($domain, false, dirname(plugin_basename($this->file)) . '/lang/');
    } // End load_plugin_textdomain ()

    /**
     * Main WC_POS Instance
     *
     * Ensures only one instance of WC_POS is loaded or can be loaded.
     *
     * @static
     * @see WC_POS()
     * @return Main WC_POS instance
     * @since 1.9
     */
    public static function instance($file = '', $version = '1.0.0')
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }
        return self::$_instance;
    } // End instance ()

    /**
     * Cloning is forbidden.
     *
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    } // End __clone ()

    /**
     * Unserializing instances of this class is forbidden.
     *
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    } // End __wakeup ()

    /**
     * Installation. Runs on activation.
     * @access  public
     * @return  void
     */
    public function install($networkwide)
    {
        global $wpdb;

        if (function_exists('is_multisite') && is_multisite()) {
            // check if it is a network activation - if so, run the activation function for each blog id
            if ($networkwide) {
                $old_blog = $wpdb->blogid;
                // Get all blog ids
                $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
                foreach ($blogids as $blog_id) {
                    switch_to_blog($blog_id);
                    WC_POS_Install::install();
                }
                switch_to_blog($old_blog);
                return;
            }
        } else {
            WC_POS_Install::install();
        }
    } // End install ()


    /**
     * Log the plugin version number.
     * @access  public
     * @return  void
     */
    private function _log_version_number()
    {
        update_option($this->_token . '_version', $this->_version);
    } // End _log_version_number ()


    /**
     * Check if current page is pos screen
     *
     * @return boolean
     */
    public function is_pos_page()
    {
        global $post_type;
        if ($post_type == 'product')
            return true;
        if (isset($_GET['page']) && (
                $_GET['page'] == 'wc_pos_settings' ||
                $_GET['page'] == 'wc_pos_barcodesr' ||
                $_GET['page'] == 'wc_pos_receipts' ||
                $_GET['page'] == 'wc_pos_users' ||
                $_GET['page'] == 'wc_pos_tiles' ||
                $_GET['page'] == 'wc_pos_grids' ||
                $_GET['page'] == 'wc_pos_outlets' ||
                $_GET['page'] == 'wc_pos_registers' ||
                $_GET['page'] == 'wc_pos_stock_controller' ||
                $_GET['page'] == 'wc_pos_cash_management' ||
                $_GET['page'] == 'wc_pos_bill_screen'
            )
        ) {
            return true;
        }
        return false;
    }

    public function change_stock_amount()
    {
        $decimal_quantity = get_option('wc_pos_decimal_quantity');

        if ($decimal_quantity == 'yes') {
            remove_filter('woocommerce_stock_amount', 'intval');
            add_filter('woocommerce_stock_amount', 'floatval');
            add_filter('woocommerce_quantity_input_step', array($this, 'quantity_input_step'), 80, 2);
        }
    }

    public function quantity_input_step($step, $_product)
    {
        return 'any';
    }

    /**
     * Check API is active
     * @return boolean
     */
    public function check_api_active()
    {
        $api_access = false;
        if (get_option('woocommerce_api_enabled') == 'yes') {
            $api_access = true;
        }
        return $api_access;
    }

    function admin_notices()
    {
        if (!$this->wc_api_is_active) {
            ?>
            <div class="error">
                <p><?php _e('The WooCommerce API is disabled on this site.', 'wc_point_of_sale'); ?> <a
                            href="<?php echo admin_url('admin.php?page=wc-settings'); ?>"><?php _e('Enable the REST API', 'wc_point_of_sale'); ?></a>
                </p>
            </div>
            <?php
        }
        if ($this->permalink_structure == '') {
            ?>
            <div class="error">
                <p><?php _e('Incorrect Permalinks Structure.', 'wc_point_of_sale'); ?> <a
                            href="<?php echo admin_url('options-permalink.php'); ?>"><?php _e('Change Permalinks', 'wc_point_of_sale'); ?></a>
                </p>
            </div>
            <?php
        }
    }

    function tile_attribute_label($label)
    {
        if (isset($_GET['page']) && $_GET['page'] == $this->id_tiles && isset($_GET['grid_id']))
            return '<strong>' . $label . '</strong>';
        else return $label;
    }

    function order_received_url($order_received_url)
    {
        if (isset($_GET['page']) && $_GET['page'] == 'wc_pos_registers' && isset($_GET['reg']) && !empty($_GET['reg']) && isset($_GET['outlet']) && !empty($_GET['outlet'])) {
            $register = $_GET['reg'];
            $outlet = $_GET['outlet'];

            setcookie("wc_point_of_sale_register", $register, time() - 3600 * 24 * 120, '/');
            $register_url = get_home_url() . "/point-of-sale/$outlet/$register";

            if (is_ssl() || get_option('woocommerce_pos_force_ssl_checkout') == 'yes') {
                $register_url = str_replace('http:', 'https:', $register_url);
            }

            return $register_url;
        } else {
            return $order_received_url;
        }
    }

    public function orders_by_order_type($vars)
    {
        global $typenow, $wp_query;
        if ($typenow == 'shop_order') {

            if (isset($_GET['shop_order_wc_pos_order_type']) && $_GET['shop_order_wc_pos_order_type'] != '') {

                if ($_GET['shop_order_wc_pos_order_type'] == 'POS') {
                    $vars['meta_query'][] = array(
                        'key' => 'wc_pos_order_type',
                        'value' => 'POS',
                        'compare' => '=',
                    );
                } elseif ($_GET['shop_order_wc_pos_order_type'] == 'online') {
                    $vars['meta_query'][] = array(
                        'key' => 'wc_pos_order_type',
                        'compare' => 'NOT EXISTS'
                    );
                }

            }

            if (isset($_GET['shop_order_wc_pos_filter_register']) && $_GET['shop_order_wc_pos_filter_register'] != '') {
                $vars['meta_query'][] = array(
                    'key' => 'wc_pos_id_register',
                    'value' => $_GET['shop_order_wc_pos_filter_register'],
                    'compare' => '=',
                );

            }
            if (isset($_GET['shop_order_wc_pos_filter_outlet']) && $_GET['shop_order_wc_pos_filter_outlet'] != '') {
                $registers = pos_get_registers_by_outlet($_GET['shop_order_wc_pos_filter_outlet']);
                $vars['meta_query'][] = array(
                    'key' => 'wc_pos_id_register',
                    'value' => $registers,
                    'compare' => 'IN',
                );

            }

        }

        return $vars;
    }

    function order_actions_reprint_receipts($actions, $the_order)
    {
        $amount_change = get_post_meta($the_order->get_id(), 'wc_pos_order_type', true);
        $id_register = get_post_meta($the_order->get_id(), 'wc_pos_id_register', true);
        if ($amount_change && $id_register) {
            $data = $this->register()->get_data($id_register);
            if (!empty($data) && !empty($data[0])) {
                $data = $data[0];
                $actions['reprint_receipts'] = array(
                    'url' => wp_nonce_url(admin_url('admin.php?print_pos_receipt=true&order_id=' . $the_order->get_id()), 'print_pos_receipt'),
                    'name' => __('Reprint receipts', 'wc_point_of_sale'),
                    'action' => "reprint_receipts"
                );
            }

        }

        return $actions;
    }

    function add_prefix_suffix_order_number($order_id, $order)
    {
        if (!$order instanceof WC_Order) {
            return $order_id;
        }
        $redister_id = get_post_meta($order->get_id(), 'wc_pos_id_register', true);

        if ($redister_id) {

            $_order_id = get_post_meta($order->get_id(), 'wc_pos_prefix_suffix_order_number', true);
            if ($_order_id == '') {
                $reg = $this->register()->get_data($redister_id);
                if ($reg) {
                    $reg = $reg[0];
                    $_order_id = $reg['detail']['prefix'] . $order->get_id() . $reg['detail']['suffix'];
                    add_post_meta($order->get_id(), 'wc_pos_prefix_suffix_order_number', $_order_id, true);
                    add_post_meta($order->get_id(), 'wc_pos_order_tax_number', $reg['detail']['tax_number'], true);
                }
            }
            $order_id = str_replace('#', '', $_order_id);
        }
        return $order_id;
    }


    function print_report()
    {
        if (isset($_GET['print_pos_receipt']) && !empty($_GET['print_pos_receipt']) && isset($_GET['order_id']) && !empty($_GET['order_id'])) {

            $nonce = $_REQUEST['_wpnonce'];
            if (!wp_verify_nonce($nonce, 'print_pos_receipt') || !is_user_logged_in()) die('You are not allowed to view this page.');
            $order_id = $_GET['order_id'];
            $register_ID = get_post_meta($order_id, 'wc_pos_id_register', true);

            $register = $this->register()->get_data($register_ID);
            $register = $register[0];
            $register_name = $register['name'];

            $receipt_ID = $register['detail']['receipt_template'];
            $outlet_ID = $register['outlet'];

            $preview = false;

            $order = new WC_Order($order_id);
            $receipt_options = WC_POS()->receipt()->get_data($receipt_ID);
            $receipt_style = WC_POS()->receipt()->get_style_templates();
            $receipt_options = $receipt_options[0];
            $attachment_image_logo = wp_get_attachment_image_src($receipt_options['logo'], 'full');


            $outlet = $this->outlet()->get_data($outlet_ID);
            $outlet = $outlet[0];
            $address = $outlet['contact'];
            $address['first_name'] = '';
            $address['last_name'] = '';
            $address['company'] = '';
            $outlet_address = WC()->countries->get_formatted_address($address);

            remove_action('wp_footer', 'wp_admin_bar_render', 1000);
            include_once($this->plugin_views_path() . '/html-print-receipt.php');
        }
    }

    /**
     * Check if page is POS Register
     * @since 1.9
     * @return bool
     */
    function is_pos()
    {
        global $wp_query;
        if (isset($this->is_pos) && !is_null($this->is_pos)) {
            return $this->is_pos;
        } else {
            $q = $wp_query->query;
            if (isset($q['page']) && $q['page'] == 'wc_pos_registers' && isset($q['action']) && $q['action'] == 'view') {
                $this->is_pos = true;
            } else {
                $this->is_pos = false;
            }
            return $this->is_pos;
        }
    }

    public function woocommerce_delete_shop_order_transients()
    {
        $transients_to_clear = array(
            'wc_pos_report_sales_by_register',
            'wc_pos_report_sales_by_outlet',
            'wc_pos_report_sales_by_cashier'
        );
        // Clear transients where we have names
        foreach ($transients_to_clear as $transient) {
            delete_transient($transient);
        }
    }

    public function add_caps()
    {
        $role = get_role('shop_manager');
        $role->add_cap('read_private_products');
    }


    public function hidden_order_itemmeta($meta_keys = array())
    {
        $meta_keys[] = '_pos_custom_product';
        $meta_keys[] = '_price';
        return $meta_keys;
    }

    public function woocommerce_email_actions($email_actions)
    {
        if (is_pos_referer() === true || is_pos()) {
            foreach ($email_actions as $key => $action) {
                if (strpos($action, 'woocommerce_order_status_') === 0) {
                    unset($email_actions[$key]);
                }
            }
            $aenc = get_option('wc_pos_automatic_emails');
            if ($aenc != 'yes') {
                $new_actions = array();
                foreach ($email_actions as $action) {
                    if ($action == 'woocommerce_created_customer')
                        continue;

                    $new_actions[] = $action;
                }
                $email_actions = $new_actions;
            }
        }
        return $email_actions;
    }


    /** Helper functions ******************************************************/

    /**
     * Get WooCommerce API endpoint.
     *
     * @return string
     */
    public function wc_api_url()
    {
        return get_woocommerce_api_url('');
    }

    /**
     * Get the plugin file.
     *
     * @return string
     */
    public function plugin_file()
    {
        return WC_POS_FILE;
    }


    /**
     * Get the plugin url.
     *
     * @return string
     */
    public function plugin_url()
    {
        return untrailingslashit(plugins_url('/', WC_POS_FILE));
    }

    /**
     * Get the plugin barcode url.
     *
     * @return string
     */
    public function barcode_url()
    {
        return untrailingslashit(plugins_url('includes/lib/barcode/image.php', WC_POS_FILE) . '?filetype=PNG&dpi=72&scale=1&rotation=0&font_family=Arial.ttf&thickness=60&start=NULL&code=BCGcode128');
    }

    /**
     * Get the plugin path.
     *
     * @return string
     */
    public function plugin_path()
    {
        return untrailingslashit(plugin_dir_path(WC_POS_FILE));
    }


    /**
     * Get the plugin path.
     *
     * @return string
     */
    public function plugin_views_path()
    {
        return untrailingslashit(plugin_dir_path(WC_POS_FILE) . 'includes/views');
    }

    /**
     * Get the plugin assets path.
     *
     * @return string
     */
    public function plugin_assets_path()
    {
        return untrailingslashit(plugin_dir_path(WC_POS_FILE) . 'assets');
    }

    /**
     * Get the sound url.
     *
     * @return string
     */
    public function plugin_sound_url()
    {
        return untrailingslashit(plugins_url('/assets/plugins/ion.sound/sounds', WC_POS_FILE));
    }

    /**
     * Get Outlets class
     *
     * @since 1.9
     * @return WC_Pos_Outlets
     */
    public function outlet()
    {
        return WC_Pos_Outlets::instance();
    }

    /**
     * Get Outlets table class
     *
     * @since 1.9
     * @return WC_Pos_Table_Outlets
     */
    public function outlet_table()
    {
        return new WC_Pos_Table_Outlets;
    }

    /**
     * Get Registers class
     *
     * @since 1.9
     * @return WC_Pos_Registers
     */
    public function register()
    {
        return WC_Pos_Registers::instance();
    }

    /**
     * Get Registers Table class
     *
     * @since 1.9
     * @return WC_Pos_Table_Registers
     */
    public function registers_table()
    {
        return new WC_Pos_Table_Registers;
    }


    /**
     * Get Grids class
     *
     * @since 1.9
     * @return WC_Pos_Grids
     */
    public function grid()
    {
        return WC_Pos_Grids::instance();
    }

    /**
     * Get Grids Table class
     *
     * @since 1.9
     * @return WC_Pos_Table_Grids
     */
    public function grids_table()
    {
        return new WC_Pos_Table_Grids;
    }

    /**
     * Get Tiles class
     *
     * @since 1.9
     * @return WC_Pos_Tiles
     */
    public function tile()
    {
        return WC_Pos_Tiles::instance();
    }

    /**
     * Get Tiles Table class
     *
     * @since 1.9
     * @return WC_Pos_Table_Tiles
     */
    public function tiles_table()
    {
        return new WC_Pos_Table_Tiles;
    }

    /**
     * Get Users class
     *
     * @since 1.9
     * @return WC_Pos_Users
     */
    public function user()
    {
        return WC_Pos_Users::instance();
    }

    /**
     * Get Users Table class
     *
     * @since 1.9
     * @return WC_Pos_Table_Users
     */
    public function users_table()
    {
        return new WC_Pos_Table_Users;
    }

    /**
     * Get Receipts class
     *
     * @since 1.9
     * @return WC_Pos_Receipts
     */
    public function receipt()
    {
        return WC_Pos_Receipts::instance();
    }

    /**
     * Get Receipts Table class
     *
     * @since 1.9
     * @return WC_Pos_Table_Receipts
     */
    public function receipts_table()
    {
        return new WC_Pos_Table_Receipts();
    }

    /**
     * Get Session Reports class
     *
     * @since 1.9
     * @return WC_Pos_Session_Reports
     */
    public function session_reports()
    {
        return new WC_Pos_Session_Reports;
    }

    /**
     * Get Session Reports class
     *
     * @since 1.9
     * @return WC_Pos_Bill_Screen
     */
    public function bill_screen($reg_id)
    {
        return new WC_Pos_Bill_Screen($reg_id);
    }

    /**
     * Get Sessions Table class
     *
     * @since 1.9
     * @return WC_Pos_Table_Sessions
     */
    public function sessions_table()
    {
        return new WC_Pos_Table_Sessions;
    }

    /**
     * Get Barcodes class
     *
     * @since 1.9
     * @return WC_Pos_Barcodes
     */
    public function barcode()
    {
        return WC_Pos_Barcodes::instance();
    }

    /**
     * Get Stock class
     *
     * @since 3.0.0
     * @return WC_Pos_Stock
     */
    public function stock()
    {
        return WC_Pos_Stocks::instance();
    }

    /**
     * Get Float_cash class
     *
     * @since 3.1.8.1
     * @return WC_Float_Cash
     */
    public function float_cash()
    {
        return WC_Float_Cash::instance();
    }

    public function get_subscription_payment_method($payment_method, $subscription)
    {
        if (get_post_meta($subscription->order->id, 'wc_pos_order_type', true) == 'POS') {
            $payment_method = get_post_meta($subscription->order->id, '_payment_method_title', true);
        }
        return $payment_method;
    }

    public function check_pos_custom_product_exists()
    {
        $pos_prod_id = get_option('wc_pos_custom_product_id');
        $post = get_post($pos_prod_id);

        if (!$post) {
            WC_POS_Install::create_product();
        }
    }

    /*public function check_db_updates()
    {
        WC_POS_Install::update();
    }*/

    public function check_pos_visibility_products()

    {
        global $wpdb;
        //get products without pos_visibility
        $sql = "SELECT DISTINCT p.`ID` FROM {$wpdb->posts} p
                    WHERE `post_type` = 'product'
                    AND NOT EXISTS (
                    SELECT * FROM {$wpdb->postmeta} WHERE `meta_key` = '_pos_visibility' 
                    AND `post_id` = p.`ID`
                    )";
        $result = $wpdb->get_results($sql);
        if ($result) {
            foreach ($result as $res) {
                $wpdb->insert(
                    $wpdb->postmeta,
                    array(
                        'post_id' => $res->ID,
                        'meta_key' => '_pos_visibility',
                        'meta_value' => 'pos_online'
                    )
                );
            }
        }
    }

    public function check_connection_status_option()
    {
        if (get_option('wc_pos_disable_connection_status', 'yes') == 'no' && get_option('wc_pos_connection_status_checked', 'no') == 'no') {
            update_option('wc_pos_disable_connection_status', 'yes');
            add_option('wc_pos_connection_status_checked', 'yes');
        }
    }

    public function add_custom_discounts($default)
    {
        $discounts = get_option('woocommerce_pos_register_discount_presets');
        foreach ($discounts as $key => $value) {
            if (array_key_exists($value, $default)) {
                continue;
            }
            $default[$value] = $value . __('%', 'wc_point_of_sale');
        }
        return $default;
    }
}