<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * WooCommerce WC_AJAX
 *
 * AJAX Event Handler
 *
 * @class     WC_POS_AJAX
 * @version   2.1.0
 * @package   WoocommercePointOfSale/Classes
 * @category  Class
 * @author    Actuality Extensions
 */
class WC_POS_AJAX
{

    /**
     * Hook into ajax events
     */
    public function __construct()
    {
        $this->increase_timeout();

        // woocommerce_EVENT => nopriv
        $ajax_events = array(
            'new_update_outlets_address' => false,
            'edit_update_outlets_address' => false,
            'add_products_to_register' => false,
            'update_product_quantity' => false,
            'remove_product_from_register' => false,
            'add_customer' => false,
            'loading_states' => false,
            'add_customers_to_register' => false,
            'search_variations_for_product' => false,
            'tile_ordering' => false,
            'json_search_usernames' => false,
            'search_variations_for_product_and_sku' => false,
            'check_shipping' => false,

            'stripe_get_outlet_address' => false,
            'json_search_products' => false,
            'json_search_products_all' => false,
            'find_variantion_by_attributes' => false,
            'add_product_grid' => false,
            'get_server_product_ids' => false,
            'json_search_customers' => false,
            'checkout' => false,
            'void_register' => false,
            'search_order_by_code' => false,
            'json_search_registers' => false,
            'json_search_outlet' => false,
            'json_search_cashier' => false,
            'update_customer_shipping_address' => false,
            'filter_product_barcode' => false,
            'change_stock' => false,
            'get_grid_options' => false,
            'can_user_open_register' => false,

            'add_product_for_barcode' => false,
            'get_product_variations_for_barcode' => false,
            'json_search_categories' => false,
            'get_products_by_categories' => false,
            'set_register_opening_cash' => false,
            'add_cash_management_action' => false,
            'get_user_avatars' => false,
            'set_register_actual_cash' => false,
            'refresh_bill_screen' => true,
            'get_default_variations' => true
        );

        foreach ($ajax_events as $ajax_event => $nopriv) {
            add_action('wp_ajax_wc_pos_' . $ajax_event, array($this, $ajax_event));

            if ($nopriv)
                add_action('wp_ajax_nopriv_wc_pos_' . $ajax_event, array($this, $ajax_event));
        }
    }

    /**
     * WC REST API can timeout on some servers
     * This is an attempt t o increase the timeout limit
     * TODO: is there a better way?
     */
    public function increase_timeout()
    {
        $timeout = 6000;
        if (!ini_get('safe_mode'))
            @set_time_limit($timeout);

        @ini_set('memory_limit', WP_MAX_MEMORY_LIMIT);
        @ini_set('max_execution_time', (int)$timeout);
    }

    /**
     * Output headers for JSON requests
     */
    private function json_headers()
    {
        header('Content-Type: application/json; charset=utf-8');
    }

    public function new_update_outlets_address()
    {
        check_ajax_referer('new-update-pos-outlets-address', 'security');
        WC_POS()->outlet()->display_outlet_form();
        die();
    }

    public function edit_update_outlets_address()
    {
        check_ajax_referer('edit-update-pos-outlets-address', 'security');
        WC_POS()->outlet()->display_edit_form();
        die();
    }

    /* change the state according country */

    public function loading_states()
    {
        $country = $_REQUEST['country'];
        $id = $_REQUEST['id'];
        $countries = new WC_Countries();
        $filds = $countries->get_address_fields($country, '');

        unset($filds['first_name']);
        unset($filds['last_name']);
        unset($filds['company']);
        $filds['country']['options'] = $countries->get_allowed_countries();
        $filds['country']['type'] = 'select';

        if ($country != '') {
            $filds['country']['value'] = $country;
            $states = $countries->get_allowed_country_states();
            if (!empty($states[$country])) {
                $filds['state']['options'] = $states[$country];
                $filds['state']['type'] = 'select';
            }
        }

        $statelabel = $filds['state']['label'];
        $postcodelabel = $filds['postcode']['label'];
        $citylabel = $filds['city']['label'];
        $html = array();
        $state_html = '';
        if ($id == 'shipping_country') {
            $dd = 'shipping_state';
        } else {
            $dd = 'billing_state';
        }
        if (isset($filds['state']['options']) && !empty($filds['state']['options'])) {
            $state_html .= '<select id="' . $dd . '" class="ajax_chosen_select_' . $dd . '" style="width: 100%;" name="' . $id . '_county">';
            foreach ($filds['state']['options'] as $key => $value) {
                $state_html .= '<option value = "' . $key . '"> ' . $value . '</option>';
            }
            $state_html .= '</select>';
        } else {
            $state_html .= '<input type="text" id="' . $dd . '" name="' . $dd . '" class="input" placeholder="' . $statelabel . '"/>';
        }
        $html['state_html'] = $state_html;
        $html['state_label'] = $statelabel;
        $html['zip_label'] = $postcodelabel;
        $html['city_label'] = $citylabel;
        echo(json_encode($html));
        die;
    }

    public function add_customer()
    {
        global $wpdb, $user;
        $userdata = array();
        parse_str($_REQUEST['form_data'], $userdata);
        $email = $userdata['billing_email'];
        $useremail = sanitize_user($email);


        $nickname = str_replace(' ', '', ucfirst(strtolower($userdata['billing_first_name']))) . str_replace(' ', '', ucfirst(strtolower($userdata['billing_last_name'])));
        $username_opt = get_option('woocommerce_pos_end_of_sale_username_add_customer');
        switch ($username_opt) {
            case 2:
                $username = str_replace(' ', '', strtolower($userdata['billing_first_name'])) . '-' . str_replace(' ', '', strtolower($userdata['billing_last_name']));
                break;
            case 3:
                $username = $email;
                break;
            default:
                $username = strtolower($nickname);
                break;
        }
        $username = _truncate_post_slug($username, 60);
        $check_sql = "SELECT user_login FROM {$wpdb->users} WHERE user_login = '%s' LIMIT 1";

        $user_name_check = $wpdb->get_var($wpdb->prepare($check_sql, $username));


        if ($user_name_check) {
            $suffix = 1;
            do {
                $alt_user_name = _truncate_post_slug($username, 60 - (strlen($suffix) + 1)) . "-$suffix";
                $user_name_check = $wpdb->get_var($wpdb->prepare($check_sql, $alt_user_name));
                $suffix++;
            } while ($user_name_check);
            $username = $alt_user_name;
        }


        $id_user = username_exists($username);
        $user_id = $userdata['customer_details_id'];
        $new_user = false;
        // CREATES WP USER ACCOUNT
        if (empty($userdata['customer_details_id'])) {

            if (!$id_user and email_exists($useremail) == false) {


                add_filter('pre_option_woocommerce_registration_generate_password', 'pos_enable_generate_password');
                $user_id = wc_create_new_customer($useremail, $username);
                remove_filter('pre_option_woocommerce_registration_generate_password', 'pos_enable_generate_password');

                $new_user = true;
            } else {
                echo '<!--WC_POS_START-->' . json_encode(
                        array('success' => false, 'message' => __('User already exists.', 'wc_point_of_sale'))
                    ) . '<!--WC_POS_END-->';
                die();
            }
        }


        $phone = $userdata['billing_phone'];
        $billing_country = $userdata['billing_country'];
        $billing_firstname = $userdata['billing_first_name'];
        $billing_lastname = $userdata['billing_last_name'];
        $billing_company = $userdata['billing_company'];
        $billing_address = $userdata['billing_address_1'];
        $billing_address1 = $userdata['billing_address_2'];
        $billing_city = $userdata['billing_city'];
        $billing_state = '';
        if (isset($userdata['billing_state'])) {
            $billing_state = $userdata['billing_state'];
        } else if (isset($userdata['billing_country_county'])) {
            $billing_state = $userdata['billing_country_county'];
        }
        $billing_postcode = $userdata['billing_postcode'];

        if (isset($userdata['ship_to_different_address'])) {
            $shipping_country = $userdata['shipping_country'];
            $shipping_firstname = $userdata['shipping_first_name'];
            $shipping_lastname = $userdata['shipping_last_name'];
            $shipping_company = $userdata['shipping_company'];
            $shipping_address = $userdata['shipping_address_1'];
            $shipping_address1 = $userdata['shipping_address_2'];
            $shipping_city = $userdata['shipping_city'];
            $shipping_state = '';
            if (isset($userdata['shipping_state'])) {
                $shipping_state = $userdata['shipping_state'];
            } else if (isset($userdata['shipping_country_county'])) {
                $shipping_state = $userdata['shipping_country_county'];
            }
            $shipping_postcode = $userdata['shipping_postcode'];
        } else {
            $shipping_country = $billing_country;
            $shipping_firstname = $billing_firstname;
            $shipping_lastname = $billing_lastname;
            $shipping_company = $billing_company;
            $shipping_address = $billing_address;
            $shipping_address1 = $billing_address1;
            $shipping_city = $billing_city;
            $shipping_state = $billing_state;
            $shipping_postcode = $billing_postcode;
        }

        /* INSERT IN TO USER TABLE */
        $user_nicename = $username;
        $user_registered = date('Y-m-d h:i:s');
        $display_name = $billing_firstname . " " . $billing_lastname;

        if ($user_id) {
            // Use 'update_user_meta()' to add or update the user information fields.
            update_user_meta($user_id, 'user_nicename', $user_nicename);
            update_user_meta($user_id, 'user_registered', $user_registered);
            update_user_meta($user_id, 'display_name', $display_name);
            update_user_meta($user_id, 'first_name', $billing_firstname);
            update_user_meta($user_id, 'last_name', $billing_lastname);

            if ($new_user)
                wp_update_user(array('ID' => $user_id, 'role' => 'customer'));

            update_user_meta($user_id, 'billing_first_name', $billing_firstname);
            update_user_meta($user_id, 'billing_last_name', $billing_lastname);
            update_user_meta($user_id, 'billing_company', $billing_company);
            update_user_meta($user_id, 'billing_address_1', $billing_address);
            update_user_meta($user_id, 'billing_address_2', $billing_address1);
            update_user_meta($user_id, 'billing_city', $billing_city);
            update_user_meta($user_id, 'billing_postcode', $billing_postcode);
            update_user_meta($user_id, 'billing_state', $billing_state);
            update_user_meta($user_id, 'billing_country', $billing_country);
            update_user_meta($user_id, 'billing_phone', $phone);
            update_user_meta($user_id, 'billing_email', $email);
            update_user_meta($user_id, 'shipping_first_name', $shipping_firstname);
            update_user_meta($user_id, 'shipping_last_name', $shipping_lastname);
            update_user_meta($user_id, 'shipping_company', $shipping_company);
            update_user_meta($user_id, 'shipping_address_1', $shipping_address);
            update_user_meta($user_id, 'shipping_address_2', $shipping_address1);
            update_user_meta($user_id, 'shipping_city', $shipping_city);
            update_user_meta($user_id, 'shipping_postcode', $shipping_postcode);
            update_user_meta($user_id, 'shipping_state', $shipping_state);
            update_user_meta($user_id, 'shipping_country', $shipping_country);

            do_action('woocommerce_checkout_update_user_meta', $user_id, $_POST);

            $success = "success";

            ob_start();

            $out = pos_get_user_html($user_id);

            ob_end_clean();
            echo '<!--WC_POS_START-->' . json_encode(
                    array(
                        'success' => true,
                        'id' => $user_id,
                        'html' => $out
                    )
                ) . '<!--WC_POS_END-->';

        }

        die;
    }

    public function remove_product_from_register()
    {
        global $wpdb;
        check_ajax_referer('remove_product_from_register', 'security');
        $register_id = absint($_POST['register_id']);

        $id_product = absint($_POST['id_product']);
        $order_id = absint($_POST['order_id']);
        if (!is_numeric($id_product))
            die();
        wc_delete_order_item($id_product);
        die('Deleted');
    }

    public function update_product_quantity()
    {
        check_ajax_referer('add_product_to_register', 'security');
        $register_id = absint($_POST['register_id']);

        $item_order_id = absint($_POST['item_order_id']);
        $new_quantity = absint($_POST['new_quantity']);

        if (!is_numeric($item_order_id))
            die($item_order_id);
        if (!is_numeric($new_quantity))
            die();

        $order_id = absint($_POST['order_id']);
        $order = new WC_Order($order_id);

        $order_items = $order->get_items(apply_filters('woocommerce_admin_order_item_types', array('line_item', 'fee')));

        $_product = $order->get_product_from_item($order_items[$item_order_id]);

        $_tax = new WC_Tax();
        $price = $_product->get_price();
        $qty = 1;
        $line_tax = 0;

        if (!defined('WOOCOMMERCE_CHECKOUT'))
            define('WOOCOMMERCE_CHECKOUT', true);


        if ($_product->is_taxable()) {
            if (get_option('woocommerce_prices_include_tax') === 'no') {

                $tax_rates = $_tax->get_rates($_product->get_tax_class());
                $taxes = $_tax->calc_tax($price * $qty, $tax_rates, false);
                $tax_amount = $_tax->get_tax_total($taxes);
                $line_tax = round($tax_amount, absint(get_option('woocommerce_price_num_decimals')));
            } else {
                $tax_rates = $_tax->get_rates($_product->get_tax_class());
                $base_tax_rates = $_tax->get_shop_base_rate($_product->tax_class);
                $is_vat_exempt = false;
                if ($is_vat_exempt) {
                    $base_taxes = $_tax->calc_tax($price * $qty, $base_tax_rates, true);
                    $base_tax_amount = array_sum($base_taxes);
                    $line_tax = round($base_tax_amount, absint(get_option('woocommerce_price_num_decimals')));

                } elseif ($tax_rates !== $base_tax_rates) {
                    $base_taxes = $_tax->calc_tax($price * $qty, $base_tax_rates, true);
                    $modded_taxes = $_tax->calc_tax(($price * $qty) - array_sum($base_taxes), $tax_rates, false);
                    #$line_tax              = round( array_sum( $base_taxes ) + array_sum( $modded_taxes ), absint( get_option( 'woocommerce_price_num_decimals' ) ) );
                    $line_tax = round(array_sum($base_taxes), absint(get_option('woocommerce_price_num_decimals')));
                }

            }

        }
        wc_update_order_item_meta($item_order_id, '_qty', apply_filters('woocommerce_stock_amount', $new_quantity));
        wc_update_order_item_meta($item_order_id, '_line_tax', $line_tax);
        die('updated');

    }

    public function add_products_to_register()
    {
        global $wpdb;
        check_ajax_referer('add_product_to_register', 'security');
        $register_id = absint($_POST['register_id']);

        $item_to_add = sanitize_text_field($_POST['item_to_add']);
        $order_id = absint($_POST['order_id']);

// Find the item
        if (!is_numeric($item_to_add))
            die();

        $post = get_post($item_to_add);

        if (!$post || ($post->post_type !== 'product' && $post->post_type !== 'product_variation'))
            die();

        $_product = get_product($post->ID);
        $_product_id_var = $post->ID;

        $order = new WC_Order($order_id);
        $class = 'new_row product_id_' . $_product_id_var;

// Set values
        $item = array();

        $item['product_id'] = $_product->id;
        $item['variation_id'] = isset($_product->variation_id) ? $_product->variation_id : '';
        $item['variation_data'] = isset($_product->variation_data) ? $_product->variation_data : '';
        $item['name'] = $_product->get_title();
        $item['tax_class'] = $_product->get_tax_class();
        $item['qty'] = 1;
        $item['line_subtotal'] = wc_format_decimal($_product->get_price_excluding_tax());
        $item['line_subtotal_tax'] = '';
        $item['line_total'] = wc_format_decimal($_product->get_price_excluding_tax()) * $item['qty'];
        $item['line_tax'] = '';

        $_tax = new WC_Tax();
        $price = $_product->get_price();
        $qty = 1;
        $line_tax = 0;

        if (!defined('WOOCOMMERCE_CHECKOUT'))
            define('WOOCOMMERCE_CHECKOUT', true);


        if ($_product->is_taxable()) {
            if (get_option('woocommerce_prices_include_tax') === 'no') {

                $tax_rates = $_tax->get_rates($_product->get_tax_class());
                $taxes = $_tax->calc_tax($price * $qty, $tax_rates, false);
                $tax_amount = $_tax->get_tax_total($taxes);
                $line_tax = round($tax_amount, absint(get_option('woocommerce_price_num_decimals')));
            } else {
                $tax_rates = $_tax->get_rates($_product->get_tax_class());
                $base_tax_rates = $_tax->get_shop_base_rate($_product->tax_class);
                $is_vat_exempt = false;
                if ($is_vat_exempt) {
                    $base_taxes = $_tax->calc_tax($price * $qty, $base_tax_rates, true);
                    $base_tax_amount = array_sum($base_taxes);
                    $line_tax = round($base_tax_amount, absint(get_option('woocommerce_price_num_decimals')));

                } elseif ($tax_rates !== $base_tax_rates) {
                    $base_taxes = $_tax->calc_tax($price * $qty, $base_tax_rates, true);
                    $modded_taxes = $_tax->calc_tax(($price * $qty) - array_sum($base_taxes), $tax_rates, false);
                    #$line_tax              = round( array_sum( $base_taxes ) + array_sum( $modded_taxes ), absint( get_option( 'woocommerce_price_num_decimals' ) ) );
                    $line_tax = round(array_sum($base_taxes), absint(get_option('woocommerce_price_num_decimals')));
                }

            }

        }
        if ($line_tax) $item['line_tax'] = $line_tax;


// Add line item
        $item_id = wc_add_order_item($order_id, array(
            'order_item_name' => $item['name'],
            'order_item_type' => 'line_item'
        ));

// Add line item meta
        if ($item_id) {
            wc_add_order_item_meta($item_id, '_qty', $item['qty']);
            wc_add_order_item_meta($item_id, '_tax_class', $item['tax_class']);
            wc_add_order_item_meta($item_id, '_product_id', $item['product_id']);
            wc_add_order_item_meta($item_id, '_variation_id', $item['variation_id']);
            wc_add_order_item_meta($item_id, '_line_subtotal', $item['line_subtotal']);
            wc_add_order_item_meta($item_id, '_line_subtotal_tax', $item['line_subtotal_tax']);
            wc_add_order_item_meta($item_id, '_line_total', $item['line_total']);
            wc_add_order_item_meta($item_id, '_line_tax', $item['line_tax']);

// Store variation data in meta
            if ($item['variation_data'] && is_array($item['variation_data'])) {
                foreach ($item['variation_data'] as $key => $value) {
                    wc_add_order_item_meta($item_id, str_replace('attribute_', '', $key), $value);
                }
            }

            do_action('woocommerce_ajax_add_order_item_meta', $item_id, $item);
        }


        $item = apply_filters('woocommerce_ajax_order_item', $item, $item_id);

        require_once(dirname(realpath(dirname(__FILE__))) . '/views/html-admin-registers-product-item.php');

        die();
    }

    public function add_customers_to_register()
    {
        global $wpdb;
        check_ajax_referer('add_customers_to_register', 'security');

        $user_to_add = absint($_POST['user_to_add']);

        pos_get_user_html($user_to_add);
        die;
    }

    public function check_shipping()
    {
        global $wpdb;

        check_ajax_referer('check_shipping', 'security');

        $register_id = absint($_POST['register_id']);

        $user_id = isset($_POST['user_to_add']) ? absint($_POST['user_to_add']) : 0;
        if (!$user_id) die();


        $products_ids = $_POST['products_ids'];
        parse_str($products_ids, $ids);
        $ids = $ids['product_item_id'];

        $products_qt = $_POST['products_qt'];
        parse_str($products_qt, $qty);
        $qty = $qty['order_item_qty'];

        $session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');
        WC()->cart = new WC_Cart();
        WC()->customer = new WC_Customer();
        WC()->shipping = new WC_Shipping();
        WC()->session = new $session_class();

        $user_info = get_user_meta($user_id);

        $country = isset($user_info['billing_country']) ? $user_info['billing_country'][0] : '';
        $state = isset($user_info['billing_state']) ? $user_info['billing_state'][0] : '';
        $postcode = isset($user_info['billing_postcode']) ? $user_info['billing_postcode'][0] : '';
        $city = isset($user_info['billing_city']) ? $user_info['billing_city'][0] : '';

        if (isset($user_info['shipping_country']) && $s_country = $user_info['shipping_country'][0]) {
            $s_state = isset($user_info['shipping_state']) ? $user_info['shipping_state'][0] : '';
            $s_postcode = isset($user_info['shipping_postcode']) ? $user_info['shipping_postcode'][0] : '';
            $s_city = isset($user_info['shipping_city']) ? $user_info['shipping_city'][0] : '';
        } else {
            $s_country = $country;
            $s_state = $state;
            $s_postcode = $postcode;
            $s_city = $city;
        }

        WC()->customer->set_location($country, $state, $postcode, $city);
        WC()->customer->set_shipping_location($s_country, $s_state, $s_postcode, $s_city);


        foreach ($ids as $key => $id) {
            $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($id));
            $quantity = empty($qty[$key]) ? 1 : apply_filters('woocommerce_stock_amount', $qty[$key]);
            $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $id, $quantity);
            if ($passed_validation) {
                WC()->cart->add_to_cart($id, $quantity);
            }
        }

        if (!defined('WOOCOMMERCE_CART'))
            define('WOOCOMMERCE_CART', true);
        WC()->cart->calculate_totals();
        WC()->cart->calculate_shipping();

        if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) :
            $packages = WC()->shipping->get_packages();

            foreach ($packages as $i => $package) {
                $chosen_method = 'no_shipping';
                $available_methods = $package['rates'];
                $show_package_details = (sizeof($packages) > 1);
                $index = $i;
                require(dirname(realpath(dirname(__FILE__))) . '/views/html-admin-cart-shipping.php');
            }

        endif;
        // Remove cart
        WC()->cart->empty_cart();

        die();
    }

    public function search_variations_for_product()
    {
        global $wpdb;
        check_ajax_referer('search_variations_for_product', 'security');
        $id_product = absint($_POST['id_product']);
        $args = array(
            'post_type' => array('product_variation'),
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'order' => 'ASC',
            'orderby' => 'parent title',
            'post_parent' => $id_product,
        );

        $posts = get_posts($args);
        $found_products = array();

        if ($posts) {
            foreach ($posts as $post) {
                $product = get_product($post->ID);
                $image = '';
                $size = 'shop_thumbnail';
                if (has_post_thumbnail($post->ID)) {
                    $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), $size);
                    $image = $thumbnail[0];
                } elseif (($parent_id = wp_get_post_parent_id($post->ID)) && has_post_thumbnail($parent_id)) {
                    $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($parent_id), $size);
                    $image = $thumbnail[0];
                } else {
                    $image = wc_placeholder_img_src();
                }
                if (!$image || $image == NULL) $image = wc_placeholder_img_src();

                $found_products[$post->ID]['formatted_name'] = $product->get_formatted_name();
                $found_products[$post->ID]['image'] = $image;
            }
        }
        if (!empty($found_products))
            echo json_encode($found_products);
        die();
    }

    public function search_variations_for_product_and_sku()
    {
        global $wpdb;
        check_ajax_referer('search_variations_for_product_and_sku', 'security');
        $id_product = absint($_POST['id_product']);
        $__product = get_product($id_product);
        $sku = $__product->get_sku();
        $price = woocommerce_price($__product->get_price());
        $args = array(
            'post_type' => array('product_variation'),
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'order' => 'ASC',
            'orderby' => 'parent title',
            'post_parent' => $id_product,
        );

        $posts = get_posts($args);
        $variation = array();

        if ($posts) {
            foreach ($posts as $post) {
                $product = get_product($post->ID);
                $variation[$post->ID] = array(
                    'name' => $product->get_formatted_name(),
                    'sku' => $product->get_sku(),
                );
            }
        }
        echo json_encode(array('sku' => $sku, 'price' => $price, 'variation' => $variation));
        die();
    }

    /**
     * Ajax request handling for tiles ordering
     */
    public function tile_ordering()
    {
        global $wpdb;

        $id = (int)$_POST['id'];
        $grid_id = (int)$_POST['grid_id'];
        $next_id = isset($_POST['nextid']) && (int)$_POST['nextid'] ? (int)$_POST['nextid'] : null;

        if (!$id || !$grid_id) die(0);
        $index = 0;
        $table_name = $wpdb->prefix . 'wc_poin_of_sale_tiles';
        $all_tiles = $tiles = $wpdb->get_results("SELECT * FROM  $table_name  WHERE grid_id = $grid_id ORDER BY order_position ASC");

        if (empty($all_tiles)) die($index);

        foreach ($all_tiles as $tile) {

            if ($tile->ID == $id) { // our tile to order, we skip
                continue; // our tile to order, we skip
            }
            // the nextid of our tile to order, lets move our tile here
            if (null !== $next_id && $tile->ID == $next_id) {
                $index++;
                $wpdb->update($table_name, array('order_position' => $index), array('ID' => $id));
            }

            // set order
            $index++;
            $wpdb->update($table_name, array('order_position' => $index), array('ID' => $tile->ID));
        }
        if (null === $next_id) {
            $index++;
            $wpdb->update($table_name, array('order_position' => $index), array('ID' => $id));
        }
        die($index);
    }

    /**
     * Search for customers and return json
     */
    public static function json_search_customers()
    {
        ob_start();

        check_ajax_referer('search-customers', 'security');

        $term = wc_clean(stripslashes($_GET['term']));

        if (empty($term)) {
            die();
        }

        $default = isset($_GET['default']) ? $_GET['default'] : __('Guest', 'woocommerce');

        $found_customers = array('' => $default);

        add_action('pre_user_query', array(__CLASS__, 'json_search_customer_name'));

        $customers_query = new WP_User_Query(apply_filters('woocommerce_json_search_customers_query', array(
            'fields' => 'all',
            'orderby' => 'display_name',
            'search' => '*' . $term . '*',
            'search_columns' => array('ID', 'user_login', 'user_email', 'user_nicename')
        )));

        remove_action('pre_user_query', array(__CLASS__, 'json_search_customer_name'));

        $customers = $customers_query->get_results();

        if ($customers) {
            foreach ($customers as $customer) {
                $found_customers[$customer->ID] = $customer->first_name . ' ' . $customer->last_name . ' &ndash; ' . sanitize_email($customer->user_email);
            }
        }

        wp_send_json($found_customers);

    }

    /**
     * When searching using the WP_User_Query, search names (user meta) too
     * @param  object $query
     * @return object
     */
    public static function json_search_customer_name($query)
    {
        global $wpdb;

        $term = wc_clean(stripslashes($_GET['term']));
        if (method_exists($wpdb, 'esc_like')) {
            $term = $wpdb->esc_like($term);
        } else {
            $term = like_escape($term);
        }

        $query->query_from .= " INNER JOIN {$wpdb->usermeta} AS user_name ON {$wpdb->users}.ID = user_name.user_id AND ( user_name.meta_key = 'first_name' OR user_name.meta_key = 'last_name' ) ";
        $query->query_where .= $wpdb->prepare(" OR user_name.meta_value LIKE %s ", '%' . $term . '%');
    }

    public function json_search_usernames()
    {
        global $wpdb;

        check_ajax_referer('search-usernames', 'security');

        header('Content-Type: application/json; charset=utf-8');

        $term = urldecode(stripslashes(strip_tags($_GET['term'])));

        if (empty($term))
            die();

        $found_users = array();

        $data = WC_POS()->user()->get_data($term);

        if ($data) {
            foreach ($data as $userid => $user) {
                $found_users[$userid] = $user['username'];
            }
        }

        echo json_encode($found_users);
        die();
    }

    function stripe_get_outlet_address()
    {
        global $wpdb;
        $outlet_id = $_POST['outlet_id'];
        $table_name = $wpdb->prefix . "wc_poin_of_sale_outlets";
        $db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = $outlet_id");
        $data;

        foreach ($db_data as $value) {
            $value->contact = (array)json_decode($value->contact);
            $data = get_object_vars($value);
        }
        die(json_encode($data));
    }

    public function json_search_variation_pr($parent_id, $v_id)
    {
        if (!$parent_id || empty($parent_id)) return false;
        if (!$v_id || empty($v_id)) return false;

        $found_products = array();

        $product = get_product($v_id);
        $id = $v_id;

        $title = "";
        $f_title = "";

        $tips = '<strong>' . __('Product ID:', 'woocommerce') . '</strong> ' . absint($parent_id);
        $tips .= '<br/><strong>' . __('Variation ID:', 'woocommerce') . '</strong> ' . absint($id);

        $sku = '';
        if ($product && $product->get_sku()) {
            $tips .= '<br/><strong>' . __('Product SKU:', 'woocommerce') . '</strong> ' . esc_html($product->get_sku());
            $title .= esc_html($product->get_sku()) . ' &ndash; ';
            $f_title .= esc_html($product->get_sku()) . ' &ndash; ';
            $sku = esc_html($product->get_sku());
        }
        $title .= '<a target="_blank" href="' . esc_url(admin_url('post.php?post=' . absint($id) . '&action=edit')) . '">' . esc_html($product->post->post_title) . '</a>';
        $f_title .= esc_html($product->post->post_title);


        $variation_data = array();
        if ($product && isset($product->variation_data)) {
            $f_title .= ' &ndash; ';
            $f_var = '';
            $tips .= '<br/>' . wc_get_formatted_variation($product->variation_data, true);
            $i = 0;

            $attributes = (array)maybe_unserialize(get_post_meta($parent_id, '_product_attributes', true));


            foreach ($product->variation_data as $names => $value) {
                if (!$value) {
                    continue;
                }
                $name = str_replace('attribute_', '', $names);
                if (isset($attributes[$name])) {

                    if ($attributes[$name]['is_taxonomy']) {

                        $rental_features = get_taxonomy($name);
                        $variation_data[$i][1] = $rental_features->label;

                        $post_terms = wp_get_post_terms($parent_id, $attributes[$name]['name']);

                        foreach ($post_terms as $term) {
                            if ($term->slug == $value) {
                                $variation_data[$i][2] = $term->name;
                                break;
                            }
                        }
                    } else {
                        $variation_data[$i][1] = $attributes[$name]['name'];
                        $variation_data[$i][2] = '';
                        $options = array_map('trim', explode(WC_DELIMITER, $attributes[$name]['value']));
                        foreach ($options as $option) {
                            if (sanitize_title($option) == $value) {
                                $variation_data[$i][2] = $option;
                                break;
                            }
                        }
                    }
                }

                if (!empty($f_var)) $f_var .= ', ';
                $f_var .= $variation_data[$i][2];
                $i++;
            }
            $f_title .= $f_var . ' &ndash; ';
            $f_title .= wc_price($product->get_price());
        }
        $image = '';
        $size = 'shop_thumbnail';
        if (has_post_thumbnail($id)) {
            $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($id), $size);
            $image = $thumbnail[0];
        } elseif (($parent_id = wp_get_post_parent_id($id)) && has_post_thumbnail($parent_id)) {
            $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($parent_id), $size);
            $image = $thumbnail[0];
        } else {
            $image = wc_placeholder_img_src();
        }
        if (!$image || $image == NULL) $image = wc_placeholder_img_src();

        $found_products['pid'] = $id;
        $found_products['title'] = $title;
        $found_products['f_title'] = $f_title;
        $found_products['stock'] = $product->get_stock_quantity();
        $found_products['sku'] = $sku;
        $found_products['price'] = $product->get_price();
        $found_products['f_price'] = wc_price($product->get_price());
        $found_products['tax'] = 0;
        $found_products['pr_inc_tax'] = $product->get_price_including_tax();
        $found_products['pr_excl_tax'] = $product->get_price_excluding_tax();
        $found_products['tip'] = $tips;
        $found_products['variation'] = json_encode($variation_data);
        $found_products['image'] = $image;
        return $found_products;
    }

    public function json_search_products_all()
    {
        check_ajax_referer('search-products', 'security');
        $this->json_headers();
        $args = array(
            'post_type' => array('product'),
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'order' => 'ASC',
            'orderby' => 'ID'
        );

        $found_products = array();
        $posts = get_posts($args);
        if ($posts) {
            foreach ($posts as $post) {
                $product = get_product($post->ID);

                $id = $product->id;

                if ($product->product_type == 'variable') {
                    $variations = $product->get_available_variations();

                    foreach ($variations as $key => $variation_value) {
                        if ($variation_pr = $this->json_search_variation_pr($id, $variation_value['variation_id'])) {
                            $found_products[$id]['children'][] = $variation_value['variation_id'];
                            $found_products[$variation_value['variation_id']] = $variation_pr;
                        }
                    }
                }

                $title = "";
                $f_title = "";
                $tips = '<strong>' . __('Product ID:', 'woocommerce') . '</strong> ' . absint($id);

                $sku = '';
                if ($product && $product->get_sku()) {
                    $tips .= '<br/><strong>' . __('Product SKU:', 'woocommerce') . '</strong> ' . esc_html($product->get_sku());
                    $title .= esc_html($product->get_sku()) . ' &ndash; ';
                    $f_title .= esc_html($product->get_sku()) . ' &ndash; ';
                    $sku = esc_html($product->get_sku());
                }


                $title .= '<a target="_blank" href="' . esc_url(admin_url('post.php?post=' . absint($id) . '&action=edit')) . '">' . esc_html($product->post->post_title) . '</a>';
                $f_title .= esc_html($product->post->post_title);

                $variation_data = array();


                $image = '';
                $size = 'shop_thumbnail';
                if (has_post_thumbnail($id)) {
                    $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($id), $size);
                    $image = $thumbnail[0];
                } else {
                    $image = wc_placeholder_img_src();
                }
                if (!$image || $image == NULL) $image = wc_placeholder_img_src();

                $found_products[$id]['pid'] = $id;
                $found_products[$id]['title'] = $title;
                $found_products[$id]['f_title'] = $f_title;
                $found_products[$id]['stock'] = $product->get_stock_quantity();
                $found_products[$id]['sku'] = $sku;
                $found_products[$id]['price'] = $product->get_price();
                $found_products[$id]['f_price'] = wc_price($product->get_price());
                $found_products[$id]['tax'] = 0;
                $found_products[$id]['pr_inc_tax'] = $product->get_price_including_tax();
                $found_products[$id]['pr_excl_tax'] = $product->get_price_excluding_tax();
                $found_products[$id]['tip'] = $tips;
                $found_products[$id]['variation'] = json_encode($variation_data);
                $found_products[$id]['image'] = $image;

                $attributes = (array)maybe_unserialize(get_post_meta($id, '_product_attributes', true));
                $default_attributes = maybe_unserialize(get_post_meta($id, '_default_attributes', true));

                if (!empty($attributes)) {
                    $found_products[$id]['all_var'] = '';
                    foreach ($attributes as $attribute) {

                        if (empty($attribute))
                            continue;

                        // Only deal with attributes that are variations
                        if (!$attribute['is_variation'])
                            continue;


                        // Get terms for attribute taxonomy or value if its a custom attribute
                        if ($attribute['is_taxonomy']) {

                            $rental_features = get_taxonomy($attribute['name']);

                            $found_products[$id]['all_var'] .= '<select data-label="' . $rental_features->label . '" ><option value="">' . __('No default', 'woocommerce') . ' ' . esc_html(wc_attribute_label($attribute['name'])) . '&hellip;</option>';

                            $post_terms = wp_get_post_terms($post->ID, $attribute['name']);

                            foreach ($post_terms as $term)
                                $found_products[$id]['all_var'] .= '<option  value="' . esc_attr($term->name) . '">' . esc_attr($term->name) . '</option>';

                        } else {

                            $found_products[$id]['all_var'] .= '<select data-label="' . $attribute['name'] . '" ><option value="">' . __('No default', 'woocommerce') . ' ' . esc_html(wc_attribute_label($attribute['name'])) . '&hellip;</option>';

                            $options = array_map('trim', explode(WC_DELIMITER, $attribute['value']));

                            foreach ($options as $option)
                                $found_products[$id]['all_var'] .= '<option  value="' . esc_attr($option) . '">' . esc_attr($option) . '</option>';

                        }

                        $found_products[$id]['all_var'] .= '</select>';
                    }

                }
                //{"pid" : 83, "title" : "Some text", "stock" : 15, "price": 3.5, "tax": 3.5, "image": "", "variation": "", "tip" : "" },
            }
        }

        $found_products = apply_filters('wc_pos_json_search_found_products', $found_products);

        echo json_encode($found_products);

        die();
    }

    /**
     * Search for products and echo json
     *
     * @param string $x (default: '')
     * @param string $post_types (default: array('product'))
     */
    public function json_search_products($x = '', $post_types = array('product'))
    {

        check_ajax_referer('search-products', 'security');

        $this->json_headers();

        $term = (string)wc_clean(stripslashes($_GET['term']));

        if (empty($term)) {
            die();
        }

        if (is_numeric($term)) {

            $args = array(
                'post_type' => $post_types,
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'post__in' => array(0, $term),
                'fields' => 'ids'
            );

            $args2 = array(
                'post_type' => $post_types,
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'post_parent' => $term,
                'fields' => 'ids'
            );

            $args3 = array(
                'post_type' => $post_types,
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_sku',
                        'value' => $term,
                        'compare' => 'LIKE'
                    )
                ),
                'fields' => 'ids'
            );

            $posts = array_unique(array_merge(get_posts($args), get_posts($args2), get_posts($args3)));

        } else {

            $args = array(
                'post_type' => $post_types,
                'post_status' => 'publish',
                'posts_per_page' => -1,
                's' => $term,
                'fields' => 'ids'
            );

            $args2 = array(
                'post_type' => $post_types,
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_sku',
                        'value' => $term,
                        'compare' => 'LIKE'
                    )
                ),
                'fields' => 'ids'
            );

            $posts = array_unique(array_merge(get_posts($args), get_posts($args2)));

        }

        $found_products = array();

        if ($posts) {
            foreach ($posts as $post) {
                $product = get_product($post);

                $image = '';
                $size = 'shop_thumbnail';
                if (has_post_thumbnail($post)) {
                    $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($post), $size);
                    $image = $thumbnail[0];
                } elseif (($parent_id = wp_get_post_parent_id($post)) && has_post_thumbnail($parent_id)) {
                    $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($parent_id), $size);
                    $image = $thumbnail[0];
                } else {
                    $image = wc_placeholder_img_src();
                }
                if (!$image || $image == NULL) $image = wc_placeholder_img_src();

                $found_products[$post]['formatted_name'] = $product->get_formatted_name();
                $found_products[$post]['name'] = $product->post->post_title;
                $found_products[$post]['image'] = $image;
            }
        }

        $found_products = apply_filters('wc_pos_json_search_found_products', $found_products);

        echo json_encode($found_products);

        die();
    }

    public function find_variantion_by_attributes()
    {

        check_ajax_referer('search-products', 'security');

        $this->json_headers();

        $attributes = $_POST['attributes'];
        $register_id = absint($_POST['register_id']);
        $parent = absint($_POST['parent']);

        if (empty($attributes)) {
            die();
        }
        $new_attr = array();
        foreach ($attributes as $value) {
            $new_attr['attribute_' . sanitize_title($value['name'])] = $value['option'];
        }

        $args = array(
            'post_type' => array('product_variation'),
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'order' => 'ASC',
            'orderby' => 'parent title',
            'post_parent' => $parent,
        );

        $posts = get_posts($args);

        $found_products = array();

        if ($posts) {
            foreach ($posts as $post) {
                $product = get_product($post);

                if ($new_attr == $product->variation_data) {
                    $found_products['id'] = $post->ID;
                }

            }
        }


        echo json_encode($found_products);
        die();
    }

    function add_product_grid()
    {
        check_ajax_referer('add-product_grid', 'security');
        global $wpdb;
        $grid_name = $_POST['term'];
        $grid_label = sanitize_title($grid_name);

        $grid_label = _truncate_post_slug($grid_label, 255);
        $check_sql = "SELECT label FROM {$wpdb->prefix}wc_poin_of_sale_grids WHERE label = '%s' LIMIT 1";

        $grid_label_check = $wpdb->get_var($wpdb->prepare($check_sql, $grid_label));


        if ($grid_label_check) {
            $suffix = 1;
            do {
                $alt_grid_label = _truncate_post_slug($grid_label, 255 - (strlen($suffix) + 1)) . "-$suffix";
                $grid_label_check = $wpdb->get_var($wpdb->prepare($check_sql, $alt_grid_label));
                $suffix++;
            } while ($grid_label_check);
            $grid_label = $alt_grid_label;
        }

        $grid = array(
            'label' => $grid_label,
            'name' => $grid_name
        );
        // insert gird layout data  its table "wp_wc_poin_of_sale_grids"
        if ($wpdb->insert($wpdb->prefix . 'wc_poin_of_sale_grids', $grid)) {
            do_action('woocommerce_grid_added', $wpdb->insert_id, $grid);
            echo $wpdb->insert_id;
            die();
        }

    }

    /**
     * Get all the product ids
     * @return json
     */
    public function get_server_product_ids()
    {

        $args = array(
            'post_type' => array('product', 'product_variation'),
            'post_status' => array('publish'),
            'posts_per_page' => -1,
            'fields' => 'ids'
        );

        $query = new WP_Query($args);
        $ids = array_map('intval', $query->posts);

        $this->json_headers();
        echo json_encode($ids);
        die();
    }

    function checkout()
    {
        if (!defined('WOOCOMMERCE_CHECKOUT')) {
            define('WOOCOMMERCE_CHECKOUT', true);
        }
        if (!defined('WC_POS_CHECKOUT')) {
            define('WC_POS_CHECKOUT', true);
        }
        $id = 0;

        $id_register = $_POST['id_register'];
        if (isset($_POST['user_id'])) {
            $user_id = $_POST['user_id'];
        } else {
            $user_id = 0;
        }

        $enabled_gateways = get_option('pos_enabled_gateways', array());
        $pos_exist_gateways = get_option('pos_exist_gateways', array());

        foreach ($pos_exist_gateways as $gateway_id) {
            if (!in_array($gateway_id, $enabled_gateways)) {
                add_filter('option_woocommerce_' . $gateway_id . '_settings', array(WC_POS(), 'disable_gateway'));
            } else {
                add_filter('option_woocommerce_' . $gateway_id . '_settings', array(WC_POS(), 'enable_gateway'));
            }

        }

        if (isset($_POST['id']) && $_POST['id'] != '') {
            $id = $_POST['id'];
            new WC_Pos_Checkout($id, $user_id);
            #new WC_Pos_Registers_Orders($id , $user_id);
        } else {
            wc_add_notice('<strong>Register id </strong> ' . __('is a required field.', 'woocommerce'), 'error');
        }

        die(0);
    }

    public function void_register()
    {
        check_ajax_referer('void_register', 'security');

        if (!isset($_POST['register_id']) || !isset($_POST['register_id'])) {
            echo json_encode(array('result' => 'error'));
            die;
        }
        $order_id = $_POST['order_id'];
        $register_id = $_POST['register_id'];
        $order_type = get_post_type($order_id);
        $result = array('result' => 'ok', 'order_id' => $order_id);

        if ($order_type == 'shop_order') {
            $status = get_post_status($order_id);
            $order = new WC_Order($order_id);
            if ($order)
                $order->update_status('cancelled');
            $register = wc_pos_get_register($register_id);
            if ($register) {
                $order_id = (int)$register->order_id;
                $order_type = get_post_type($order_id);
                if ($order_type == 'pos_temp_register_or') {
                    $result['order_id'] = $order_id;
                } else {
                    $order_id = WC_POS()->register()->crate_order_id($register_id);
                    $result['order_id'] = $order_id;
                }
            }
        }
        echo json_encode($result);
        die;
    }

    public function search_order_by_code()
    {

        global $wpdb;
        if (!isset($_GET['code']) || empty($_GET['code'])) {
            echo 'error';
            die;
        }
        $code = $_GET['code'];

        $result = $wpdb->get_var("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'wc_pos_prefix_suffix_order_number' AND ( meta_value = '{$code}' OR meta_value = '#{$code}') LIMIT 1");
        if (!$result) {
            $int = intval(preg_replace('/[^0-9]+/', '', $code), 10);
            $result = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE ID = {$int} LIMIT 1");
        }

        if ($result)
            echo get_edit_post_link($result);
        else
            echo 'error';

        die;

    }


    /**
     * Search for registers and echo json
     *
     */
    public function json_search_registers()
    {
        ob_start();

        check_ajax_referer('search-products', 'security');

        $term = (string)wc_clean(stripslashes($_GET['term']));

        if (empty($term)) {
            die();
        }

        global $wpdb;
        $table_name = $wpdb->prefix . "wc_poin_of_sale_registers";
        $registers = $wpdb->get_results("SELECT * FROM $table_name WHERE name LIKE '%{$term}%' OR slug LIKE '%{$term}%'");

        $found = array();

        if ($registers) {
            foreach ($registers as $register) {
                $found[$register->ID] = rawurldecode($register->name);
            }
        }

        $found = apply_filters('wc_pos_json_search_registers', $found);

        wp_send_json($found);
    }


    /**
     * Search for outlet and echo json
     *
     */
    public function json_search_outlet()
    {
        ob_start();

        check_ajax_referer('search-products', 'security');

        $term = (string)wc_clean(stripslashes($_GET['term']));

        if (empty($term)) {
            die();
        }

        global $wpdb;
        $table_name = $wpdb->prefix . "wc_poin_of_sale_outlets";
        $registers = $wpdb->get_results("SELECT * FROM $table_name WHERE name LIKE '%{$term}%' ");

        $found = array();

        if ($registers) {
            foreach ($registers as $register) {
                $found[$register->ID] = rawurldecode($register->name);
            }
        }

        $found = apply_filters('wc_pos_json_search_outlet', $found);

        wp_send_json($found);
    }

    /**
     * Search for outlet and echo json
     *
     */
    public function json_search_cashier()
    {
        //ob_start();

        check_ajax_referer('search-products', 'security');

        $term = (string)wc_clean(stripslashes($_GET['term']));

        if (empty($term)) {
            die();
        }
        $found = array();

        $user_query = WC_POS()->user()->get_data();
        if ($user_query) {
            foreach ($user_query as $user) {
                $term = strtolower($term);
                $name = strtolower($user['name']);
                $username = strtolower($user['username']);
                if (strpos($name, $term) !== false || strpos($username, $term) !== false)
                    $found[$user['ID']] = $user['name'] . ' (' . $user['username'] . ')';
            }
        }

        $found = apply_filters('wc_pos_json_search_cashier', $found);

        wp_send_json($found);
    }

    public function update_customer_shipping_address()
    {
        global $wpdb, $user;
        $userdata = array();
        parse_str($_REQUEST['form_data'], $userdata);
        $user_id = $userdata['customer_details_id'];
        $shipping_country = $userdata['custom_shipping_country'];
        $shipping_firstname = $userdata['custom_shipping_first_name'];
        $shipping_lastname = $userdata['custom_shipping_last_name'];
        $shipping_company = $userdata['custom_shipping_company'];
        $shipping_address = $userdata['custom_shipping_address_1'];
        $shipping_address1 = $userdata['custom_shipping_address_2'];
        $shipping_city = $userdata['custom_shipping_city'];
        $shipping_state = $userdata['custom_shipping_state'];
        $shipping_postcode = $userdata['custom_shipping_postcode'];

        if ($user_id) {
            update_user_meta($user_id, 'shipping_first_name', $shipping_firstname);
            update_user_meta($user_id, 'shipping_last_name', $shipping_lastname);
            update_user_meta($user_id, 'shipping_company', $shipping_company);
            update_user_meta($user_id, 'shipping_address_1', $shipping_address);
            update_user_meta($user_id, 'shipping_address_2', $shipping_address1);
            update_user_meta($user_id, 'shipping_city', $shipping_city);
            update_user_meta($user_id, 'shipping_postcode', $shipping_postcode);
            update_user_meta($user_id, 'shipping_state', $shipping_state);
            update_user_meta($user_id, 'shipping_country', $shipping_country);

            do_action('woocommerce_checkout_update_user_meta', $user_id, $_POST);

            $success = "success";

            $user_to_add = $user_id;
            $s_addr = array(
                'first_name' => $shipping_lastname,
                'last_name' => $shipping_firstname,
                'company' => $shipping_company,
                'address_1' => $shipping_address,
                'address_2' => $shipping_address1,
                'city' => $shipping_city,
                'postcode' => $shipping_postcode,
                'state' => $shipping_state,
                'country' => $shipping_country
            );
            echo '<!--WC_POS_START-->' . json_encode(
                    array(
                        'success' => true,
                        'id' => $user_id,
                        's_addr' => $s_addr
                    )
                ) . '<!--WC_POS_END-->';

        }

        die;
    }

    public function filter_product_barcode()
    {
        global $wpdb;
        $barcode = $_POST['barcode'];
        $product_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $barcode));

        $result = array();
        if ($product_id) {

            $result['status'] = 'success';
            $result['response'] = $this->get_sku_controller_product($product_id);

        } else {
            $result['response'] = '<h2>No product found</h2>';
            $result['status'] = '404';
        }

        $result = json_encode($result);
        echo $result;

        die();
    }

    public function change_stock()
    {
        global $wpdb;

        $product_id = $_POST['id'];
        $operation = $_POST['operation'];
        $value = $_POST['value'];

        $result = array();
        if ($product_id) {
            $product = wc_get_product($product_id);
            $product->manage_stock = 'yes';
            $stock = $product->get_stock_quantity();

            if ($operation == 'increase') {
                $stock += $value;
            } else {
                $stock -= $value;
                if ($stock < 0) {
                    $stock = 0;
                }
            }


            $product->set_stock($stock);

            $post_modified = current_time('mysql');
            $post_modified_gmt = current_time('mysql', 1);

            wp_update_post(array(
                'ID' => $product_id,
                'post_modified' => $post_modified,
                'post_modified_gmt' => $post_modified_gmt
            ));

            if ($product->product_type == 'variation' && $product->parent && $product->parent->id > 0) {
                wp_update_post(array(
                    'ID' => $product->parent->id,
                    'post_modified' => $post_modified,
                    'post_modified_gmt' => $post_modified_gmt
                ));
            }

            $result['status'] = 'success';
            $result['response'] = $this->get_sku_controller_product($product_id);

        } else {
            $result['status'] = '404';
        }

        $result = json_encode($result);
        echo $result;

        die();
    }

    public function get_sku_controller_product($product_id = 0)
    {
        $product_data = array();
        if ($product_id) {
            $post = get_post($product_id);
            if ($post->post_type == 'product') {
                $product = new WC_Product($product_id);
                $product_data['id'] = $product_id;
                $product_data['name'] = $product->get_title();
                $product_data['sku'] = $product->get_sku();
                $product_data['image'] = $product->get_image(array(85, 85));
                $product_data['price'] = $product->get_price_html();
                $product_data['stock'] = wc_stock_amount($product->stock);
                $product_data['stock_status'] = '';
                if ($product->is_in_stock()) {
                    $product_data['stock_status'] = '<mark class="instock">' . __('In stock', 'woocommerce') . '</mark>';
                } else {
                    $product_data['stock_status'] = '<mark class="outofstock">' . __('Out of stock', 'woocommerce') . '</mark>';
                }
                $product_data['stock_status'] .= ' &times; ' . wc_stock_amount($product->stock);
            } elseif ($post->post_type = 'product_variation') {
                $product = new WC_Product_Variation($product_id);
                $product_data['id'] = $product_id;
                $product_data['name'] = $post->post_title;
                $product_data['sku'] = $product->get_name();
                $product_data['image'] = $product->get_image(array(85, 85));
                $product_data['price'] = $product->get_price();
                $product_data['stock'] = $product->get_stock_quantity();
                $product_data['stock_status'] = '';
                if ($product_data['stock']) {
                    $product_data['stock_status'] = '<mark class="instock">' . __('In stock', 'woocommerce') . '</mark>';
                } else {
                    $product_data['stock_status'] = '<mark class="outofstock">' . __('Out of stock', 'woocommerce') . '</mark>';
                }
                $product_data['stock_status'] .= ' &times; ' . number_format($product_data['stock'], 2);
            }
        }
        return $product_data;
    }

    public function get_grid_options()
    {
        $this->json_headers();
        $pos = new WC_Pos_Sell(true);
        $pos->getRegisted(intval($_GET['reg']));
        echo $pos->getGrid();
        die();
    }

    public function add_product_for_barcode()
    {
        check_ajax_referer('product_for_barcode', 'security');

        if (!current_user_can('manage_wc_point_of_sale')) {
            die(-1);
        }

        $item_to_add = sanitize_text_field($_POST['item_to_add']);

        // Find the item
        if (!is_numeric($item_to_add)) {
            die();
        }

        $post = get_post($item_to_add);

        if (!$post || ('product' !== $post->post_type && 'product_variation' !== $post->post_type)) {
            die();
        }

        $_product = wc_get_product($post->ID);
        $class = 'new_row ' . $_product->product_type;

        include 'views/html-admin-barcode-item.php';
        // Quit out
        die();
    }

    public function get_product_variations_for_barcode()
    {
        check_ajax_referer('product_for_barcode', 'security');

        if (!current_user_can('manage_wc_point_of_sale')) {
            die(-1);
        }

        $prid = $_POST['prid'];

        // Find the item
        if (!is_array($prid)) {
            die();
        }
        $variations = array();
        foreach ($prid as $id) {
            $args = array(
                'post_parent' => $id,
                'post_type' => 'product_variation',
                'numberposts' => -1,
                'fields' => 'ids',
            );
            $children_array = get_children($args, ARRAY_A);
            if ($children_array) {

                $variations = array_merge($variations, $children_array);
            }
        }
        wp_send_json($variations);
        // Quit out
        die();
    }

    public function can_user_open_register()
    {
        $response = array('result' => 'denied');

        if (isset($_POST['register_id']) && pos_check_user_can_open_register($_POST['register_id'])) {

            if ($user_id = pos_check_register_lock($_POST['register_id'])) {
                $user = get_userdata($user_id);
                $response = array(
                    'result' => 'locked',
                    'user_id' => $user_id,
                    'message' => sprintf(__('This register is currently opened by %s.', 'wc_point_of_sale'), $user->display_name),
                    'avatar' => get_avatar_url($user_id, array('size' => 64))
                );
            } else {
                $current_user = wp_get_current_user();
                $response = array(
                    'result' => 'success',
                    'user_id' => $current_user->ID,
                    'name' => $current_user->display_name,
                    'avatar' => get_avatar_url($current_user->ID, array('size' => 64))
                );
            }
        }

        wp_send_json($response);
        die();
    }

    public function json_search_categories()
    {
        global $wpdb;

        ob_start();

        check_ajax_referer('search-products', 'security');

        $term = wc_clean(stripslashes($_GET['term']));

        if (empty($term)) {
            die();
        }
        $like_term = '%' . $wpdb->esc_like($term) . '%';

        $query = $wpdb->prepare("
                SELECT terms.term_id FROM {$wpdb->terms} terms LEFT JOIN {$wpdb->term_taxonomy} taxonomy ON terms.term_id = taxonomy.term_id
                WHERE taxonomy.taxonomy = 'product_cat'
                AND terms.name LIKE %s
            ", $like_term);

        $categories = array_unique($wpdb->get_col($query));
        $found_categories = array();

        if (!empty($categories)) {
            foreach ($categories as $term_id) {
                $category = get_term($term_id);

                if (is_wp_error($category) || !$category) {
                    continue;
                }

                $found_categories[$term_id] = rawurldecode($category->name);
            }
        }

        $found_categories = apply_filters('wc_pos_json_search_categories', $found_categories);

        wp_send_json($found_categories);
    }

    public function get_products_by_categories()
    {
        check_ajax_referer('product_for_barcode', 'security');


        if (!current_user_can('manage_wc_point_of_sale') || !isset($_POST['categories'])) {
            die(-1);
        }

        $categories = $_POST['categories'];

        // Find the item
        if (!is_array($categories)) {
            die();
        }

        $args = array(
            'post_type' => 'product',
            'numberposts' => -1,
            'fields' => 'ids',
            'tax_query' => array(
                array(
                    'terms' => $categories,
                    'taxonomy' => 'product_cat'
                )
            )
        );
        $products = array();
        $posts = get_posts($args, ARRAY_A);

        if ($posts) {
            $products = $posts;
        }

        wp_send_json($products);
    }

    public function set_register_opening_cash()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . "wc_poin_of_sale_registers";

        $db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = {$_POST['register_id']}");
        $detail = json_decode($db_data[0]->detail, true);
        $detail['opening_cash_amount'] = array('status' => true, 'amount' => $_POST['amount'], 'note' => $_POST['note'], 'user' => get_current_user_id(), 'time' => current_time('mysql'));
        $data['detail'] = json_encode($detail);
        $wpdb->update($table_name, $data, array('ID' => $_POST['register_id']));
    }

    public function add_cash_management_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "wc_poin_of_sale_registers";
        $data['detail'] = $_POST['register']['detail'];
        $row = array(
            'amount' => $_POST['amount'],
            'note' => $_POST['note'],
            'time' => current_time('mysql'),
            'type' => $_POST['action_type'],
            'user' => get_current_user_id()
        );
        switch ($_POST['action_type']) {
            case 'add-cash':
                $row['title'] = __('Cash in', 'wc_point_of_sale');
                break;
            case 'remove-cash':
                $row['title'] = __('Cash out', 'wc_point_of_sale');
                break;
        }
        $data['detail']['cash_management_actions'][] = $row;
        $data['detail'] = json_encode($data['detail']);
        if ($wpdb->update($table_name, $data, array('ID' => $_POST['register']['ID']))) {
            $author = get_user_by('id', $row['user']);
            wp_die(include('views/html-float-cash-management-table-row.php'));
        }
    }

    public function get_user_avatars()
    {
        if (is_plugin_active('wp-user-avatar/wp-user-avatar.php') && has_wp_user_avatar($_POST['userdata']['id'])) {
            $_POST['userdata']['avatar_url'] = get_wp_user_avatar_src($_POST['userdata']['id'], 'thumbnail');
        }
        wp_die(json_encode($_POST['userdata']));
    }

    public function set_register_actual_cash()
    {
        WC_Pos_Float_Cash::set_actual_cash($_POST['register_id'], $_POST['sum']);
    }

    public function refresh_bill_screen()
    {
        if ($_POST['register_status'] == 'open') {
            $register_cart = $_POST['register_cart'];
            include('views/html-bill-screen-content.php');
        } elseif ($_POST['register_status'] == 'close') {
            echo "<span class='receipt_closed'>";
            _e('This Register Is Closed', 'wc_point_of_sale');
            echo "</span>";
        }
        die();
    }

    public function get_default_variations()
    {
        wp_die(json_encode(get_post_meta($_GET['product_id'], '_default_attributes', true)));
    }
}

new WC_POS_AJAX();
