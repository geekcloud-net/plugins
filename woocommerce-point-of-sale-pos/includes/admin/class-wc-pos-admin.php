<?php
/**
 * WC_POS Admin.
 *
 * @class       WC_POS_Admin
 * @author      Actuality Extensions
 * @category    Admin
 * @package     WC_POS/Admin
 * @version     1.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * WC_POS_Admin class.
 */
class WC_POS_Admin
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('init', array($this, 'includes'));
        add_action('admin_init', array($this, 'admin_redirects'));
        add_action('admin_init', array($this, 'preview_receipt'));
        add_action('admin_footer', 'wc_print_js', 25);

        add_action('init', array($this, 'register_post_types'), 5);
        add_filter('woocommerce_product_class', array($this, 'pos_custom_product_class'), 9999, 4);
        add_filter('woocommerce_reports_charts', array($this, 'pos_reports_charts'), 20, 1);

        if (isset($_GET['page']) && $_GET['page'] == 'wc_pos_registers' && isset($_GET['reg']) && $_GET['reg'] != '') {
            $company_logo = get_option('woocommerce_pos_register_layout_admin_bar', 'yes');
            if ($company_logo == 'yes') {
                add_filter('init', array($this, 'hide_admin_bar'), 9);
                add_action('admin_head', array($this, 'hide_admin_bar_css'));
            }
        }

        if (class_exists('SitePress')) {
            $settings = get_option('icl_sitepress_settings');
            if ($settings['urls']['directory_for_default_language'] == 1) {
                add_action('generate_rewrite_rules', array(__CLASS__, 'create_rewrite_rules_wpml'), 9);
            } else {
                add_filter('rewrite_rules_array', array(__CLASS__, 'create_rewrite_rules'), 11, 1);
            }
        } else {
            add_filter('rewrite_rules_array', array(__CLASS__, 'create_rewrite_rules'), 11, 1);
        }
        add_action('init', array(__CLASS__, 'on_rewrite_rule'));
        add_filter('query_vars', array(__CLASS__, 'add_query_vars'));
        add_action('parse_request', array(__CLASS__, 'parse_request'));
        add_filter('admin_init', array(__CLASS__, 'flush_rewrite_rules'));
        add_filter('plugin_action_links_' . plugin_basename(WC_POS_PLUGIN_FILE), array(__CLASS__, 'plugin_action_links'));
        add_filter('plugin_row_meta', array(__CLASS__, 'plugin_row_meta'), 10, 2);

        add_filter('woocommerce_prevent_admin_access', array(__CLASS__, 'prevent_admin_access'), 10, 2);

        add_action('add_meta_boxes', array(WC_POS()->grid(), 'add_meta_box'), 40, 1);
        add_action('save_post', array(WC_POS()->grid(), 'save_meta_box'), 40, 1);
        add_action('save_post', array($this, 'save_order_rounding_amount'), 50, 3);
        add_filter('woocommerce_get_formatted_order_total', array($this, 'get_rounding_total'), 50, 2);

        add_action('wc_pos_restrict_list_users', array($this, 'restrict_list_users'));

        /******* product_grid *********/
        add_filter('manage_edit-product_columns', array($this, 'add_product_grid_column'), 9999);
        add_action('manage_product_posts_custom_column', array($this, 'display_product_grid_column'), 2);
        add_action('admin_footer', array($this, 'product_grid_bulk_actions'), 11);
        add_action('load-edit.php', array($this, 'product_grid_bulk_actions_handler'));
        /******* end product_grid *********/

        add_action('untrashed_post', array($this, 'update_removed_posts_ids'));
        add_action('before_delete_post', array($this, 'save_removed_posts_ids'));
        add_action('wp_trash_post', array($this, 'save_removed_posts_ids'));
        add_action('delete_user', array($this, 'save_delete_user_ids'));
        if (get_option('wc_pos_visibility', 'no') == 'yes') {
            add_action('post_submitbox_misc_actions', array($this, 'product_pos_visibility'));
        }
        $this->init_users_hooks();
    }

    public function product_pos_visibility()
    {
        global $post;
        if ('product' != $post->post_type) {
            return;
        }
        $pos_visibility = ($pos_visibility = get_post_meta($post->ID, '_pos_visibility', true)) ? $pos_visibility : 'pos_online';
        $visibility_options = apply_filters('woocommerce_pos_visibility_options', array(
            'pos_online' => __('POS & Online', 'wc_point_of_sale'),
            'pos' => __('POS Only', 'wc_point_of_sale'),
            'online' => __('Online Only', 'wc_point_of_sale'),
        )); ?>
        <div class="misc-pub-section" id="pos-visibility">
            <?php _e('POS visibility:', 'wc_point_of_sale'); ?> <strong id="pos-visibility-display"><?php
                echo isset($visibility_options[$pos_visibility]) ? esc_html($visibility_options[$pos_visibility]) : esc_html($pos_visibility);
                ?></strong>

            <a href="#pos-visibility"
               class="edit-pos-visibility hide-if-no-js"><?php _e('Edit', 'wc_point_of_sale'); ?></a>

            <div id="pos-visibility-select" class="hide-if-js">

                <input type="hidden" name="current_pos_visibility" id="current_visibility"
                       value="<?php echo esc_attr($pos_visibility); ?>"/>
                <?php
                foreach ($visibility_options as $name => $label) {
                    echo '<input type="radio" name="_pos_visibility" id="pos_visibility_' . esc_attr($name) . '" value="' . esc_attr($name) . '" ' . checked($pos_visibility, $name, false) . ' data-label="' . esc_attr($label) . '" /> <label for="_visibility_' . esc_attr($name) . '" class="selectit">' . esc_html($label) . '</label><br />';
                }
                ?>
                <p>
                    <a href="#pos-visibility"
                       class="save-post-visibility hide-if-no-js button"><?php _e('OK', 'wc_point_of_sale'); ?></a>
                    <a href="#pos-visibility"
                       class="cancel-post-visibility hide-if-no-js"><?php _e('Cancel', 'wc_point_of_sale'); ?></a>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Include any classes we need within admin.
     */
    public function includes()
    {
        include_once('class-wc-pos-admin-post-actions.php');
        include_once('class-wc-pos-admin-menus.php');

        if (version_compare(WC_VERSION, '2.6', '>=')) {
            include_once('class-wc-pos-admin-notices.php');
        } else {
            include_once('class-wc-pos-admin-notices-v2.5.5.php');
        }
        include_once('class-wc-pos-admin-orders-page.php');


        /***********************/

        // Setup/welcome
        if (!empty($_GET['page'])) {
            switch ($_GET['page']) {
                case WC_POS_TOKEN . '-setup' :
                    include_once('class-wc-pos-admin-setup-wizard.php');
                    break;
                case WC_POS_TOKEN . '-about' :
                    include_once('class-wc-pos-admin-welcome.php');
                    break;
                case WC_POS_TOKEN . '-print' :
                    include_once('class-wc-pos-admin-print-report.php');
                    break;
            }
        }

    }

    public function init_users_hooks()
    {
        add_action('show_user_profile', array($this, 'add_customer_meta_fields'));
        add_action('edit_user_profile', array($this, 'add_customer_meta_fields'));

        add_action('personal_options_update', array($this, 'save_customer_meta_fields'));
        add_action('edit_user_profile_update', array($this, 'save_customer_meta_fields'));
    }

    /**
     * Handle redirects to setup/welcome page after install and updates.
     *
     * Transient must be present, the user must have access rights, and we must ignore the network/bulk plugin updaters.
     */
    public function admin_redirects()
    {
        if (!get_transient('_wc_pos_activation_redirect') || is_network_admin() || isset($_GET['activate-multi']) || !current_user_can('manage_woocommerce')) {
            return;
        }

        delete_transient('_wc_pos_activation_redirect');

        if (!empty($_GET['page']) && in_array($_GET['page'], array(WC_POS_TOKEN . '-setup', WC_POS_TOKEN . '-about'))) {
            return;
        }

        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }

        // If the user needs to install, send them to the setup wizard
        if (WC_POS_Admin_Notices::has_notice('pos_install')) {
            wp_safe_redirect(admin_url('index.php?page=' . WC_POS_TOKEN . '-setup'));
            exit;
            // Otherwise, the welcome page
        } else {

            /*wp_safe_redirect(admin_url('admin.php?page=' . WC_POS_TOKEN . '-about'));
            exit;*/

        }
    }

    /**
     * Register core post types
     */
    function register_post_types()
    {
        do_action('wc_pos_register_post_type');

        if (!post_type_exists('pos_temp_register_or')) {

            wc_register_order_type(
                'pos_temp_register_or',
                apply_filters('wc_pos_register_post_type_pos_temp_register_or',
                    array(
                        'label' => __('POS temp orders', 'wc_point_of_sale'),
                        'capability_type' => 'shop_order',
                        'public' => false,
                        'hierarchical' => false,
                        'supports' => false,
                        'exclude_from_orders_screen' => false,
                        'add_order_meta_boxes' => false,
                        'exclude_from_order_count' => true,
                        'exclude_from_order_views' => true,
                        'exclude_from_order_reports' => true,
                        'exclude_from_order_sales_reports' => true,
                        //'class_name'                       => ''
                    )
                )
            );
        }
        if (!post_type_exists('pos_custom_product')) {
            register_post_type('pos_custom_product',
                apply_filters('wc_pos_register_post_type_pos_custom_product',
                    array(
                        'label' => __('POS custom product', 'wc_point_of_sale'),
                        'public' => false,
                        'hierarchical' => false,
                        'supports' => false
                    )
                )
            );
        }
    }

    function pos_custom_product_class($classname, $product_type, $post_type, $product_id)
    {
        if ($product_id == get_option('wc_pos_custom_product_id')) {
            include_once WC_POS()->plugin_path() . '/includes/class-wc-pos-custom-product.php';
            $classname = 'WC_POS_Custom_Product';
        }
        return $classname;
    }

    function pos_reports_charts($reports)
    {
        $reports['pos'] = array(
            'title' => __('POS', 'wc_point_of_sale'),
            'reports' => array(
                "sales_by_register" => array(
                    'title' => __('Sales by register', 'wc_point_of_sale'),
                    'description' => '',
                    'hide_title' => true,
                    'callback' => array($this, 'get_report')
                ),
                "sales_by_outlet" => array(
                    'title' => __('Sales by outlet', 'wc_point_of_sale'),
                    'description' => '',
                    'hide_title' => true,
                    'callback' => array($this, 'get_report')
                ),
                "sales_by_cashier" => array(
                    'title' => __('Sales by cashier', 'wc_point_of_sale'),
                    'description' => '',
                    'hide_title' => true,
                    'callback' => array($this, 'get_report')
                ),
                "sales_by_session" => array(
                    'title' => __('Sales by session', 'wc_point_of_sale'),
                    'description' => '',
                    'hide_title' => true,
                    'callback' => array($this, 'get_sessions_table')
                ),
            )
        );
        return $reports;
    }

    public static function get_sessions_table()
    {
        WC_POS()->session_reports()->display();
    }

    /**
     * Get a report from our reports subfolder
     */
    public static function get_report($name)
    {
        $name = sanitize_title(str_replace('_', '-', $name));
        $class = 'WC_POS_Report_' . str_replace('-', '_', $name);

        include_once(apply_filters('wc_pos_admin_reports_path', WC_POS()->plugin_path() . '/includes/reports/class-wc-pos-report-' . $name . '.php', $name, $class));

        if (!class_exists($class))
            return;

        $report = new $class();
        $report->output_report();
    }

    public function hide_admin_bar()
    {
        add_filter('show_admin_bar', '__return_false');
        add_filter('wp_admin_bar_class', '__return_false');
    }

    public function hide_admin_bar_css()
    {
        ?>
        <style>
            html {
                padding-top: 0 !important;
            }
        </style>
        <?php
    }

    /**
     * Show Address Fields on edit user pages.
     *
     * @param mixed $user User (object) being displayed
     */
    public function add_customer_meta_fields($user)
    {

        if (!current_user_can('manage_wc_point_of_sale'))
            return;

        $show_fields = $this->get_customer_meta_fields();


        foreach ($show_fields as $fieldset) :
            ?>
            <h3><?php echo $fieldset['title']; ?></h3>
            <table class="form-table">
                <?php
                foreach ($fieldset['fields'] as $key => $field) :
                    ?>
                    <tr>
                        <th><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?></label>
                        </th>
                        <td>
                            <?php if (isset($field['type']) && $field['type'] == 'select') {
                                $value_user_meta = esc_attr(get_user_meta($user->ID, $key, true));
                                ?>
                                <select name="<?php echo esc_attr($key); ?>" id="<?php echo esc_attr($key); ?>"
                                        style="width: 100%; max-width: 15em;">
                                    <?php if ($key == 'outlet') { ?>
                                        <option value=""><?php _e('No Outlet', 'wc_point_of_sale'); ?></option>
                                    <?php } ?>
                                    <?php foreach ($field['options'] as $label_value => $label) {
                                        echo '<option value="' . $label_value . '" ' . (($label_value == $value_user_meta) ? 'selected' : '') . ' >' . $label . '</option>';
                                    } ?>
                                </select>
                            <?php } else { ?>
                                <input type="text" name="<?php echo esc_attr($key); ?>"
                                       id="<?php echo esc_attr($key); ?>"
                                       value="<?php echo esc_attr(get_user_meta($user->ID, $key, true)); ?>"
                                       class="regular-text"/><br/>
                            <?php } ?>
                            <br>
                            <span class="description"><?php echo wp_kses_post($field['description']); ?></span>
                        </td>
                    </tr>
                    <?php
                endforeach;
                ?>
            </table>
            <?php
        endforeach;
    }

    /**
     * Save Fields on edit user pages
     *
     * @param mixed $user_id User ID of the user being saved
     */
    public function save_customer_meta_fields($user_id)
    {
        $save_fields = $this->get_customer_meta_fields();

        foreach ($save_fields as $fieldset)
            foreach ($fieldset['fields'] as $key => $field)
                if (isset($_POST[$key]))
                    update_user_meta($user_id, $key, wc_clean($_POST[$key]));
    }

    /**
     * Get Fields for the edit user pages.
     *
     * @return array Fields to display which are filtered through wc_pos_customer_meta_fields before being returned
     */
    public function get_customer_meta_fields()
    {

        $show_fields = apply_filters('wc_pos_customer_meta_fields', array(
            'outlet_filds' => array(
                'title' => __('Point of Sale', 'wc_point_of_sale'),
                'fields' => array(
                    'outlet' => array(
                        'label' => __('Outlet', 'wc_point_of_sale'),
                        'type' => 'select',
                        'options' => WC_POS()->outlet()->get_data_names(),
                        'description' => __('Ensure the user is logged out before changing the outlet.', 'wc_point_of_sale')
                    ),
                    'discount' => array(
                        'label' => __('Discount', 'wc_point_of_sale'),
                        'type' => 'select',
                        'options' => array(
                            'enable' => 'Enable',
                            'disable' => 'Disable'
                        ),
                        'description' => ''
                    ),
                )
            ),
        ));
        return $show_fields;
    }

    /**
     * Show action links on the plugin screen.
     *
     * @param mixed $links Plugin Action links
     * @return  array
     */
    public static function plugin_action_links($links)
    {
        $action_links = array(
            'settings' => '<a href="' . admin_url('admin.php?page=wc_pos_settings') . '" title="' . esc_attr(__('View Settings', 'wc_point_of_sale')) . '">' . __('Settings', 'wc_point_of_sale') . '</a>',
        );

        return array_merge($action_links, $links);
    }

    /**
     * Show row meta on the plugin screen.
     *
     * @param mixed $links Plugin Row Meta
     * @param mixed $file Plugin Base file
     * @return  array
     */
    public static function plugin_row_meta($links, $file)
    {
        if ($file == plugin_basename(WC_POS_PLUGIN_FILE)) {
            $row_meta = array(
                'docs' => '<a href="' . esc_url(apply_filters('wc_pos_docs_url', 'http://actualityextensions.com/documentation/woocommerce-point-of-sale/')) . '" title="' . esc_attr(__('View Documentation', 'wc_point_of_sale')) . '">' . __('Docs', 'wc_point_of_sale') . '</a>',
                'support' => '<a href="' . esc_url(apply_filters('wc_pos_docs_url', 'http://actualityextensions.com/contact/')) . '" title="' . esc_attr(__('Visit Support', 'wc_point_of_sale')) . '">' . __('Support', 'wc_point_of_sale') . '</a>',
            );

            return array_merge($links, $row_meta);
        }

        return (array)$links;
    }

    public static function create_rewrite_rules($rules)
    {
        global $wp_rewrite;
        $newRule = array(
            '^point-of-sale/([^/]+)/([^/]+)/?$' => 'index.php?page=wc_pos_registers&action=view&outlet=$matches[1]&reg=$matches[2]',
            '^bill-screen/([0-9]+)/?$' => 'index.php?page=wc_pos_bill_screen&reg=$matches[1]'
        );
        $newRules = $newRule + $rules;
        return $newRules;
    }

    public static function create_rewrite_rules_wpml()
    {
        global $wp_rewrite;
        $newRule = array('point-of-sale/([^/]+)/([^/]+)/?$' => 'index.php?page=wc_pos_registers&action=view&outlet=$matches[1]&reg=$matches[2]');

        $wp_rewrite->rules = $newRule + $wp_rewrite->rules;
    }

    public static function flush_rewrite_rules()
    {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }

    public static function on_rewrite_rule()
    {
        add_rewrite_rule('^point-of-sale/([^/]+)/([^/]+)/?$', 'index.php?page=wc_pos_registers&action=view&outlet=$matches[1]&reg=$matches[2]', 'top');
        add_rewrite_rule('^bill-screen/([0-9]+)/?$', 'index.php?page=wc_pos_bill_screen', 'top');
    }

    public static function add_query_vars($public_query_vars)
    {
        $public_query_vars[] = 'page';
        $public_query_vars[] = 'action';
        $public_query_vars[] = 'outlet';
        $public_query_vars[] = 'reg';
        return $public_query_vars;
    }

    public static function parse_request($wp)
    {
        if (isset($wp->query_vars['page']) && $wp->query_vars['page'] == 'wc_pos_registers' && isset($wp->query_vars['action']) && $wp->query_vars['action'] == 'view') {
            WC_POS()->is_pos = true;
        }
        if (isset($wp->query_vars['page']) && $wp->query_vars['page'] == 'wc_pos_bill_screen' && isset($wp->query_vars['reg'])) {
            wp_enqueue_script('bill-screen', WC_POS()->plugin_url() . '/assets/js/register/bill-screen.js', array('jquery'));
            WC_POS()->bill_screen($wp->query_vars['reg'])->display();
        }
    }

    public static function prevent_admin_access($prevent_access)
    {
        if (current_user_can('view_register')) {
            $prevent_access = false;
        }
        return $prevent_access;
    }

    function restrict_list_users()
    {
        $wc_pos_filters = array('outlets', 'usernames');
        ?>
        <div class="alignleft actions">
            <?php
            foreach ($wc_pos_filters as $value) {
                add_action('wc_pos_add_filters_users', array($this, 'wc_pos_' . $value . '_filter'));
            }
            do_action('wc_pos_add_filters_users');
            ?>
            <input type="submit" id="post-query-submit" class="button action" value="Filter"/>
        </div>
        <?php
        $js = "
         if( jQuery().select2 ){
            var $ = jQuery;
            jQuery('select#dropdown_outlets').css('width', '150px').select2();
            jQuery('select#dropdown_usernames').each(function() {
                var v,t;
                $(this).find('option:selected').each(function(index, el) {
                    v = $(el).val();
                    t = $(el).text();
                });
                var _id = $(this).attr('id');
                var _class = $(this).attr('class');
                var _name = $(this).attr('name');
                $(this).replaceWith('<input type=\"text\" id=\"'+_id+'\" class=\"'+_class+'\" name=\"'+_name+'\" />');
                $('input#'+_id).select2({
                    allowClear:  $( this ).data( 'allow_clear' ) ? true : false,
                    placeholder: $( this ).data( 'placeholder' ) ? $( this ).data( 'placeholder' ) : 'Search a customer',
                    minimumInputLength: $( this ).data( 'minimum_input_length' ) ? $( this ).data( 'minimum_input_length' ) : '3',
                    escapeMarkup: function( m ) {
                        return m;
                    },
                    ajax: {
                        url:         wc_pos_params.ajax_url,
                        dataType:    'json',
                        quietMillis: 250,
                        data: function( term, page ) {
                            return {
                                term    : term,
                                action  : 'wc_pos_json_search_customers',
                                security: wc_pos_params.search_customers
                            };
                        },
                        results: function( data, page ) {
                            var terms = [];
                            if ( data ) {
                                        $.each( data, function( id, text ) {
                                            terms.push( { id: id, text: text } );
                                        });
                                    }
                          return { results: terms };
                        },
                        cache: true
                    },
                });
                if(typeof v != 'undefined'){
                    var preselect = {id: v, text: t};
                    $('input#'+_id).select2('data', preselect);
                }
                
            });
         }else{
            jQuery('select#dropdown_outlets').css('width', '150px').chosen();

            jQuery('select#dropdown_usernames').css('width', '200px').ajaxChosen({
                method:         'GET',
                url:            '" . admin_url('admin-ajax.php') . "',
                dataType:       'json',
                afterTypeDelay: 100,
                minTermLength:  2,
                data:       {
                    action:     'wc_pos_json_search_usernames',
                    security:   '" . wp_create_nonce("search-usernames") . "',
                    default:    '" . __('Show all cashiers ', 'wc_point_of_sale') . "',
                }
            }, function (data) {

                var terms = {};

                $.each(data, function (i, val) {
                    terms[i] = val;
                });

                return terms;
            });            
         }
        ";
        if (class_exists('WC_Inline_Javascript_Helper')) {
            $woocommerce->get_helper('inline-javascript')->add_inline_js($js);
        } elseif (function_exists('wc_enqueue_js')) {
            wc_enqueue_js($js);
        } else {
            $woocommerce->add_inline_js($js);
        }
    }

    function wc_pos_outlets_filter()
    {
        $outlet_arr = WC_POS()->outlet()->get_data_names();
        if (isset($_POST['_outlets_filter']) && !empty($_POST['_outlets_filter'])) {
            $outlet_id = $_POST['_outlets_filter'];
        } else {
            $outlet_id = 0;
        }
        ?>
        <select id="dropdown_outlets" name="_outlets_filter">
            <option value=""><?php _e('Show all outlets', 'wc_point_of_sale') ?></option>
            <?php
            foreach ($outlet_arr as $key => $value) {
                if ($outlet_id) {
                    echo '<option value="' . $key . '" ';
                    selected(1, 1);
                    echo '>' . $value . '</option>';
                } else {
                    echo '<option value="' . $key . '" >' . $value . '</option>';
                }
            }
            ?>
        </select>
        <?php
    }

    function wc_pos_usernames_filter()
    {
        ?>
        <select id="dropdown_usernames" name="_usernames_filter">
            <option value=""><?php _e('Show all cashiers', 'wc_point_of_sale') ?></option>
            <?php
            if (!empty($_POST['_usernames_filter'])) {
                $user_id = $_POST['_usernames_filter'];
                $userdata = get_userdata($user_id);

                echo '<option value="' . $user_id . '" ';
                selected(1, 1);
                echo '>' . $userdata->user_nicename . '</option>';
            }
            ?>
        </select>
        <?php
    }

    /******* product_grid *********/
    function add_product_grid_column($columns)
    {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key == 'product_tag')
                $new_columns['wc_pos_product_grid'] = __('Product Grid', 'wc_point_of_sale');
        }
        return $new_columns;
    }

    function display_product_grid_column($column)
    {
        global $post, $woocommerce;
        if ($column == 'wc_pos_product_grid') {
            $product_id = $post->ID;
            $grids = wc_point_of_sale_get_grids_names_for_product($product_id);
            $links = array();
            if (!empty($grids)) {
                foreach ($grids as $id => $name) {
                    $url = admin_url('admin.php?page=wc_pos_tiles&grid_id=') . $id;
                    $links[] = '<a href="' . $url . '">' . $name . '</a>';
                }
                echo implode(', ', $links);
            } else {
                echo '<span class="na">–</span>';
            }
        }
    }

    function product_grid_bulk_actions()
    {
        global $post_type;
        if ('product' == $post_type) {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function () {
                    <?php
                    $grids = wc_point_of_sale_get_grids();
                    if(!empty($grids)){
                    foreach($grids as $grid){ ?>
                    jQuery('<option>').val('wc_pos_add_to_grid_<?php echo $grid->ID; ?>')
                        .text('<?php printf(__("Add to %s", "wc_point_of_sale"), $grid->name); ?>').appendTo('select[name=action]');
                    jQuery('<option>').val('wc_pos_add_to_grid_<?php echo $grid->ID; ?>')
                        .text('<?php printf(__("Add to %s", "wc_point_of_sale"), $grid->name); ?>').appendTo('select[name=action2]');
                    <?php
                    }
                    }
                    ?>
                });
            </script>
            <?php
        }
    }

    function product_grid_bulk_actions_handler()
    {
        if (!isset($_REQUEST['post'])) {
            return;
        }
        $wp_list_table = _get_list_table('WP_Posts_List_Table');
        $action = $wp_list_table->current_action();

        global $wpdb;
        $changed = 0;
        $post_ids = array_map('absint', (array)$_REQUEST['post']);
        if (strstr($action, 'wc_pos_add_to_grid_')) {
            $grid_id = (int)substr($action, strlen('wc_pos_add_to_grid_'));
            $report_action = "products_added_to_grid";
            foreach ($post_ids as $post_id) {
                if (!product_in_grid($post_id, $grid_id)) {
                    $order_position = 1;
                    $position = get_last_position_of_tile($grid_id);
                    if (!empty($position->max)) $order_position = $position->max + 1;
                    $data = array(
                        'grid_id' => $grid_id,
                        'product_id' => $post_id,
                        'colour' => 'ffffff',
                        'background' => '8E8E8E',
                        'default_selection' => 0,
                        'order_position' => $order_position,
                        'style' => 'image'
                    );
                    $wpdb->insert($wpdb->prefix . 'wc_poin_of_sale_tiles', $data);
                    $changed++;
                }
            }
        } else {
            return;
        }
        $sendback = esc_url_raw(add_query_arg(array('post_type' => 'product', $report_action => $changed, 'ids' => join(',', $post_ids)), ''));
        wp_redirect($sendback);
        exit();
    }
    /******* end product_grid *********/

    /****/
    public function update_removed_posts_ids($post_id)
    {

        $posts_ids = get_option('pos_removed_posts_ids', array());
        $key = array_search($post_id, $posts_ids);
        if ($key !== false && isset($posts_ids[$key])) {
            unset($posts_ids[$key]);
        }
        update_option('pos_removed_posts_ids', $posts_ids);
    }

    public function save_removed_posts_ids($post_id)
    {

        $posts_ids = get_option('pos_removed_posts_ids', array());
        if (!in_array($post_id, $posts_ids)) {
            $posts_ids[] = $post_id;
        }
        update_option('pos_removed_posts_ids', $posts_ids);
    }

    /****/
    public function save_delete_user_ids($user_id)
    {

        $user_ids = get_option('pos_removed_user_ids', array());
        if (!in_array($user_id, $user_ids)) {
            $user_ids[] = $user_id;
        }
        update_option('pos_removed_user_ids', $user_ids);
    }

    public function save_order_rounding_amount($post_id, $post, $update)
    {
        $post_type = get_post_type($post_id);
        $order = wc_get_order($post_id);
        if ($post_type == 'shop_order' && $update) {
            $rounding_total = get_post_meta($post_id, 'wc_pos_rounding_total', true);
            if ($rounding_total) {
                $order->set_total($rounding_total);
                $order->save();
            }
        }
    }

    public function get_rounding_total($formatted_total, $instance)
    {
        $rounding_total = get_post_meta($instance->get_id(), 'wc_pos_rounding_total', true);
        if ($rounding_total) {
            return $formatted_total . '<span class="woocommerce-help-tip" data-tip="Cash Rounding"></span>';
        } else {
            return $formatted_total;
        }

    }

    public function preview_receipt()
    {
        if (isset($_GET['action']) && $_GET['action'] == 'receipt_preview' && $_GET['receipt_id']) {
            if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'preview_receipt')) {
                return false;
            }
            $receipt_options = WC_POS()->receipt()->get_data($_GET['receipt_id']);
            $receipt_options = $receipt_options[0];
            $attachment_image_logo = wp_get_attachment_image_src($receipt_options['logo'], 'full');
            ob_start();
            require_once(WC_POS()->plugin_path() . '/includes/views/html-print-receipt-preview.php');
            $output = ob_get_contents();
            ob_end_clean();
            echo '<div class="content" style="width: 50%; padding-left: 25%;">' . $output . '</div>';
        }
    }

}

return new WC_POS_Admin();
