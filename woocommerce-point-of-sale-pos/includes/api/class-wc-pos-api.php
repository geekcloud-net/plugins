<?php

/**
 * API Class
 *
 * Handles the products
 *
 * @class      WC_Pos_API
 * @package   WooCommerce POS
 */
class WC_Pos_API
{

    public function __construct()
    {
        // try and increase server timeout
        $this->increase_timeout();

        // remove wc api authentication
        if (isset(WC()->api) && isset(WC()->api->authentication)) {
            remove_filter('woocommerce_api_check_authentication', array(WC()->api->authentication, 'authenticate'), 0);
        }

        // Compatibility for clients that can't use PUT/PATCH/DELETE
        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $_GET['_method'] = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
        }

        $this->init_hooks();
    }

    public function init_hooks()
    {
        remove_filter('comments_clauses', array('WC_Comments', 'exclude_order_comments'), 10, 1);

        // Add custom wc api authentication
        add_filter('woocommerce_api_check_authentication', array($this, 'wc_api_authentication'), 20, 1);
        // and we're going to filter on the way out
        add_filter('woocommerce_api_product_response', array($this, 'filter_product_response'), 99, 3);
        add_filter('woocommerce_api_customer_response', array($this, 'filter_customer_response'), 99, 4);
        add_action('woocommerce_api_coupon_response', array($this, 'api_coupon_response'), 99, 4);
        add_filter('woocommerce_api_order_response', array($this, 'filter_order_response'), 999, 4);
        add_filter('woocommerce_api_query_args', array($this, 'filter_api_query_args'), 99, 2);

        // modify WP_User_Query to support created_at date filtering
        add_action('pre_user_query', array($this, 'modify_user_query'));

        $params = array_merge($_GET, $_POST);
        if (isset($params['role']) && $params['role'] == 'all') {
            add_action('pre_get_users', array($this, 'pre_get_users'), 99, 1);
        }
    }

    /**
     * Bypass authenication for WC REST API
     * @return WP_User object
     */
    public function wc_api_authentication($user)
    {
        //if( $this->is_pos_referer() === true || is_pos() ){
        global $current_user;
        $user = $current_user;

        if (!user_can($user->ID, 'view_register'))
            $user = new WP_Error(
                'woocommerce_pos_authentication_error',
                __('User not authorized to access WooCommerce POS', 'wc_point_of_sale'),
                array('status' => 401)
            );

        //}

        return $user;

    }

    /**
     * WC REST API can timeout on some servers
     * This is an attempt t o increase the timeout limit
     */
    public function increase_timeout()
    {
        $timeout = 6000;
        if (!ini_get('safe_mode'))
            @set_time_limit($timeout);

        @ini_set('memory_limit', WP_MAX_MEMORY_LIMIT);
        @ini_set('max_execution_time', (int)$timeout);
    }

    public function pre_get_users($query)
    {
        $query->query_vars['role'] = '';
        return $query;
    }

    public function modify_user_query($query)
    {

        $args = array_merge($_GET, $_POST);
        $filter = array();
        if (!empty($args['filter'])) {
            $filter = $args['filter'];

            // Updated date
            if (!empty($filter['updated_at_min'])) {
                $updated_at_min = WC()->api->server->parse_datetime($filter['updated_at_min']);
                if ($updated_at_min) {
                    $query->query_where .= sprintf(" AND user_modified_gmt >= STR_TO_DATE( '%s', '%%Y-%%m-%%d %%H:%%i:%%s' )", esc_sql($updated_at_min));
                }
            }

            if (!empty($filter['updated_at_max'])) {
                $updated_at_max = WC()->api->server->parse_datetime($filter['updated_at_max']);
                if ($updated_at_max) {
                    $query->query_where .= sprintf(" AND user_modified_gmt <= STR_TO_DATE( '%s', '%%Y-%%m-%%d %%H:%%i:%%s' )", esc_sql($updated_at_max));
                }
            }

        }
    }


    /**
     * Filter product response from WC REST API for easier handling by backbone.js
     * @param  array $product_data
     * @return array modified data array $product_data
     */
    public function filter_product_response($product_data, $product, $fields)
    {
        // flatten variable data
        $product_data['categories_ids'] = wp_get_post_terms($product->get_id(), 'product_cat', array("fields" => "ids"));

        if (!empty($product_data['attributes'])) {

            foreach ($product_data['attributes'] as $attr_key => $attribute) {
                $slug = str_replace('attribute_', '', sanitize_title($attribute['slug']));

                $is_taxonomy = false;
                $taxonomy = $this->get_attribute_taxonomy_by_slug($attribute['slug']);
                if ($taxonomy) {
                    $is_taxonomy = true;
                }

                $product_data['attributes'][$attr_key]['slug'] = $slug;
                $product_data['attributes'][$attr_key]['is_taxonomy'] = $is_taxonomy;

                $options = array();
                foreach ($product_data['attributes'][$attr_key]['options'] as $opt) {

                    if ($is_taxonomy === true) {
                        // Don't use wc_clean as it destroys sanitized characters
                        $a = get_term_by('name', $opt, 'pa_' . $slug);
                        if ($a) {
                            $value = $a->slug;
                        } else {
                            $value = sanitize_title(stripslashes($opt));
                        }
                    } else {
                        $value = wc_clean(stripslashes($opt));
                    }

                    $options[] = array('slug' => $value, 'name' => $opt);

                }
                $product_data['attributes'][$attr_key]['options'] = $options;

            }

        }
        $parent_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_data['id']), 'shop_thumbnail');
        $product_data['thumbnail_src'] = $parent_image ? current($parent_image) : wc_placeholder_img_src();

        if ($product->get_type() == 'booking') {
            $product_data['booking'] = $this->get_booking($product);
        }
        if ($product->get_type() == 'subscription' || $product->get_type() == 'variable-subscription') {
            $product_data['subscription'] = $this->get_subscription($product->get_id());
        }

        $scan_field = get_option('woocommerce_pos_register_scan_field');
        if ($scan_field) {
            $product_data['post_meta'][$scan_field][] = get_post_meta($product->get_id(), $scan_field, true);
        }
        $product_data['post_meta']['product_addons'] = get_post_meta($product->get_id(), '_product_addons', true);

        $product_data['points_earned'] = '';
        $product_data['points_max_discount'] = '';
        if (isset($GLOBALS['wc_points_rewards'])) {
            $product_data['points_earned'] = self::get_product_points($product);
            $product_data['points_max_discount'] = self::get_product_max_discount($product);
        }

        if (isset($product_data['variations']) && !empty($product_data['variations'])) {
            foreach ($product_data['variations'] as $key => $variation) {
                $product_data['variations'][$key]['type'] = $product->get_type();

                $image = wp_get_attachment_image_src(get_post_thumbnail_id($variation['id']), 'shop_thumbnail');
                $product_data['variations'][$key]['thumbnail_src'] = $image ? current($image) : $product_data['thumbnail_src'];

                if ($scan_field) {
                    $product_data['variations'][$key]['post_meta'][$scan_field][] = get_post_meta($variation['id'], $scan_field, true);
                }

                $product_data['variations'][$key]['points_earned'] = '';
                if (isset($GLOBALS['wc_points_rewards'])) {
                    $variation_product = wc_get_product($variation['id']);
                    $product_data['variations'][$key]['points_earned'] = self::get_product_points($variation_product);
                    $product_data['variations'][$key]['points_max_discount'] = self::get_product_max_discount($variation_product);
                }

                if ($product->get_type() == 'subscription' || $product->get_type() == 'variable-subscription') {
                    $product_data['variations'][$key]['subscription'] = $this->get_subscription($variation['id']);
                }
            }
        }
        return $product_data;
    }

    private function get_subscription($product_id)
    {
        $subscription = array();
        $post_meta_keys = array(
            'trial_length' => '_subscription_trial_length',
            'sign_up_fee' => '_subscription_sign_up_fee',
            'period' => '_subscription_period',
            'period_interval' => '_subscription_period_interval',
            'length' => '_subscription_length',
            'trial_period' => '_subscription_trial_period',
            'limit' => '_subscription_limit',
            'one_time_shipping' => '_subscription_one_time_shipping',
            'payment_sync_date' => '_subscription_payment_sync_date',

        );
        foreach ($post_meta_keys as $key => $meta_value) {
            $subscription[$key] = get_post_meta($product_id, $meta_value, true);
        }
        return $subscription;
    }


    private function get_booking($product)
    {

        $post_meta_keys = array(
            'duration_type' => '_wc_booking_duration_type',
            'duration' => '_wc_booking_duration',
            'min_duration' => '_wc_booking_min_duration',
            'max_duration' => '_wc_booking_max_duration',
            'max_persons_group' => '_wc_booking_max_persons_group',
            'has_resources' => '_wc_booking_has_resources',
            'resources_assignment' => '_wc_booking_resources_assignment',
            'cost' => '_wc_booking_cost',
            'resouce_label' => '_wc_booking_resouce_label',
            'check_availability_against' => '_wc_booking_check_availability_against',
            'person_qty_multiplier' => '_wc_booking_person_qty_multiplier',

        );
        $person_types = $product->get_person_types();
        foreach ($person_types as $key => $person_type) {
            $person_types[$key]->min_person_type_persons = get_post_meta($person_type->ID, 'min', true);
            $person_types[$key]->max_person_type_persons = get_post_meta($person_type->ID, 'max', true);
            $person_types[$key]->post_title = $person_type->post_title;
            $person_types[$key]->post_excerpt = $person_type->post_excerpt;
        }

        $resources = $product->get_resources();
        foreach ($resources as $key => $resource) {
            $resources[$key]->base_cost = $resource->get_base_cost();
            $resources[$key]->block_cost = $resource->get_block_cost();
            $resources[$key]->ID = $resource->ID;
            $resources[$key]->post_title = $resource->post_title;
        }

        $booking = array(
            'base_cost' => $product->get_base_cost(),
            'duration_unit' => $product->get_duration_unit(),
            'has_persons' => $product->has_persons(),
            'has_person_types' => $product->has_person_types(),
            'person_types' => $person_types,
            'min_persons' => $product->get_min_persons(),
            'max_persons' => $product->get_max_persons(),
            'resources' => $resources,
            'min_date' => $product->get_min_date(),
            'max_date' => $product->get_max_date(),
            'default_availability' => $product->get_default_availability(),
            'is_range_picker_enabled' => $product->is_range_picker_enabled(),
            'is_customer_range_picker' => $product->get_duration_type() == 'customer' && $product->is_range_picker_enabled(),
        );

        $booking_form = new WC_Booking_Form($product);

        $bookings_path = untrailingslashit(plugin_dir_path(WC_BOOKINGS_MAIN_FILE)) . '/includes/booking-form/';

        switch ($booking['duration_unit']) {
            case 'month':
                include_once($bookings_path . 'class-wc-booking-form-month-picker.php');
                $month_picker = new WC_Booking_Form_Month_Picker($booking_form);
                $booking['Month_Picker'] = $month_picker->get_args();
                break;
            case 'day':
            case 'night':
                include_once($bookings_path . 'class-wc-booking-form-date-picker.php');
                $date_picker = new WC_Booking_Form_Date_Picker($booking_form);
                $booking['Date_Picker'] = $date_picker->get_args();
                break;
            case 'minute' :
            case 'hour' :
                include_once($bookings_path . 'class-wc-booking-form-datetime-picker.php');
                $datetime_picker = new WC_Booking_Form_Datetime_Picker($booking_form);
                $booking['Datetime_Picker'] = $datetime_picker->get_args();
                break;
        }


        foreach ($post_meta_keys as $key => $meta_value) {
            $booking[$key] = get_post_meta($product->id, $meta_value, true);
        }
        return $booking;
    }

    private static function get_product_max_discount($product)
    {

        if (empty($product->variation_id)) {

            // simple product
            $max_discount = (isset($product->wc_points_max_discount)) ? $product->wc_points_max_discount : '';

        } else {
            // variable product
            $points_max_discount = get_post_meta($product->variation_id, '_wc_points_max_discount', true);
            $max_discount = (isset($points_max_discount) ? $points_max_discount : '');
        }

        return $max_discount;
    }

    private static function get_product_points($product)
    {

        if (empty($product->variation_id)) {
            // simple or variable product, for variable product return the maximum possible points earned
            if (method_exists($product, 'get_variation_price')) {
                $points = (isset($product->wc_max_points_earned)) ? $product->wc_max_points_earned : '';
            } else {
                $points = (isset($product->wc_points_earned)) ? $product->wc_points_earned : '';

                // subscriptions integration - if subscriptions is active check if this is a renewal order
                if (class_exists('WC_Subscriptions_Renewal_Order') && is_object($order)) {
                    if (WC_Subscriptions_Renewal_Order::is_renewal($order)) {
                        $points = (isset($product->wc_points_rewnewal_points)) ? $product->wc_points_rewnewal_points : $points;
                    }
                }
            }
        } else {
            // variation product
            $points = get_post_meta($product->variation_id, '_wc_points_earned', true);

            // subscriptions integration - if subscriptions is active check if this is a renewal order
            if (class_exists('WC_Subscriptions_Renewal_Order') && is_object($order)) {
                if (WC_Subscriptions_Renewal_Order::is_renewal($order)) {
                    $renewal_points = get_post_meta($product->variation_id, '_wc_points_rewnewal_points', true);
                    $points = ('' === $renewal_points) ? $points : $renewal_points;
                }
            }

            // if points aren't set at variation level, use them if they're set at the product level
            if ('' === $points) {
                $points = (isset($product->parent->wc_points_earned)) ? $product->parent->wc_points_earned : '';

                // subscriptions integration - if subscriptions is active check if this is a renewal order
                if (class_exists('WC_Subscriptions_Renewal_Order') && is_object($order)) {
                    if (WC_Subscriptions_Renewal_Order::is_renewal($order)) {
                        $points = (isset($product->parent->wc_points_rewnewal_points)) ? $product->parent->wc_points_rewnewal_points : $points;
                    }
                }
            }
        }
        return $points;
    }

    public function filter_customer_response($customer_data, $customer, $fields, $server)
    {
        $customer_data['user_meta'] = get_user_meta($customer_data['id']);
        $customer_data['points_balance'] = 0;

        if (isset($GLOBALS['wc_points_rewards'])) {
            $customer_data['points_balance'] = WC_Points_Rewards_Manager::get_users_points($customer->get_id());
        }
        if (function_exists('get_avatar_url')) {
            $customer_data['avatar_url'] = get_avatar_url($customer->get_id(), array('size' => 64));
        }
        return $customer_data;
    }

    /**
     * Get attribute taxonomy by slug.
     */
    private function get_attribute_taxonomy_by_slug($slug)
    {
        $taxonomy = null;
        $attribute_taxonomies = wc_get_attribute_taxonomies();

        foreach ($attribute_taxonomies as $key => $tax) {
            if ($slug == $tax->attribute_name) {
                $taxonomy = 'pa_' . $tax->attribute_name;
                break;
            }
        }

        return $taxonomy;
    }


    public function filter_order_response($order_data, $the_order, $fields, $api)
    {
        global $wpdb;
        $post = $the_order->post;

        $order_data['order_status'] = sprintf('<mark class="%s tips" data-tip="%s">%s</mark>', sanitize_title($the_order->get_status()), wc_get_order_status_name($the_order->get_status()), wc_get_order_status_name($the_order->get_status()));

        $formatted_address = '';
        if ($f_address = $the_order->get_formatted_shipping_address()) {
            $formatted_address = '<a target="_blank" href="' . esc_url($the_order->get_shipping_address_map_url()) . '">' . esc_html(preg_replace('#<br\s*/?>#i', ', ', $f_address)) . '</a>';
        } else {
            $formatted_address = '<span>&ndash;</span>';
        }

        if ($the_order->get_shipping_method()) {
            $formatted_address .= '<small class="meta">' . __('Via', 'woocommerce') . ' ' . esc_html($the_order->get_shipping_method()) . '</small>';
        }

        $order_data['formatted_shipping_address'] = $formatted_address;

        if ('0000-00-00 00:00:00' == $post->post_date) {
            $t_time = $h_time = __('Unpublished', 'woocommerce');
        } else {
            $t_time = get_the_time(__('Y/m/d g:i:s A', 'woocommerce'), $post);
            $h_time = get_the_time(__('Y/m/d', 'woocommerce'), $post);
        }

        $order_data['order_date'] = '<abbr title="' . esc_attr($t_time) . '">' . esc_html(apply_filters('post_date_column_time', $h_time, $post)) . '</abbr>';

        if ($the_order->customer_message) {
            $order_data['customer_message'] = '<span class="note-on tips" data-tip="' . wc_sanitize_tooltip($the_order->customer_message) . '">' . __('Yes', 'woocommerce') . '</span>';
        } else {
            $order_data['customer_message'] = '<span class="na">&ndash;</span>';
        }

        $order_notes = '<span class="na">&ndash;</span>';

        if ($post->comment_count) {
            $comment_count = absint($post->comment_count);


            // check the status of the post
            $status = ('trash' !== $post->post_status) ? '' : 'post-trashed';

            $latest_notes = get_comments(array(
                'post_id' => $post->ID,
                'number' => 1,
                'status' => $status
            ));

            $latest_note = current($latest_notes);

            if (isset($latest_note->comment_content) && $comment_count == 1) {
                $order_notes = '<span class="note-on tips" data-tip="' . wc_sanitize_tooltip($latest_note->comment_content) . '">' . __('Yes', 'woocommerce') . '</span>';
            } elseif (isset($latest_note->comment_content)) {
                $order_notes = '<span class="note-on tips" data-tip="' . wc_sanitize_tooltip($latest_note->comment_content . '<br/><small style="display:block">' . sprintf(_n('plus %d other note', 'plus %d other notes', ($comment_count - 1), 'woocommerce'), $comment_count - 1) . '</small>') . '">' . __('Yes', 'woocommerce') . '</span>';
            } else {
                $order_notes = '<span class="note-on tips" data-tip="' . wc_sanitize_tooltip(sprintf(_n('%d note', '%d notes', $comment_count, 'woocommerce'), $comment_count)) . '">' . __('Yes', 'woocommerce') . '</span>';
            }
        }

        $order_data['order_notes'] = $order_notes;
        $order_data['order_total'] = $the_order->get_formatted_order_total();

        if ($the_order->payment_method_title) {
            $order_data['order_total'] .= '<small class="meta">' . __('Via', 'woocommerce') . ' ' . esc_html($the_order->payment_method_title) . '</small>';
        }

        if (sizeof($order_data['line_items']) > 0) {
            foreach ($order_data['line_items'] as $key => $item) {
                $parents = get_post_ancestors($item['product_id']);
                if ($parents && !empty($parents)) {
                    $order_data['line_items'][$key]['variation_id'] = $item['product_id'];
                    $order_data['line_items'][$key]['product_id'] = $parents[0];
                }

                $price = wc_get_order_item_meta($item['id'], '_price');
                if ($price) {
                    $order_data['line_items'][$key]['price'] = $price;
                } else {
                    $dp = (isset($filter['dp']) ? intval($filter['dp']) : 2);
                    $order_data['line_items'][$key]['price'] = wc_format_decimal($this->get_item_price($item), $dp);
                }


                $_product = wc_get_product($item['product_id']);

                if ($_product && $_product->is_type('booking')) {
                    $booking_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_booking_order_item_id' AND meta_value = %d;", $item['id']));
                    if ($booking_id) {
                        $order_data['line_items'][$key]['hidden_fields'] = array(
                            'booking' => 'booking_id=' . $booking_id
                        );
                    }
                }


            }
        }

        if (sizeof($order_data['coupon_lines']) > 0) {
            foreach ($order_data['coupon_lines'] as $key => $coupon) {
                if ($coupon['code'] == 'POS Discount') {
                    $pamount = wc_get_order_item_meta($coupon['id'], 'discount_amount_percent', true);
                    if ($pamount && !empty($pamount)) {
                        $order_data['coupon_lines'][$key]['percent'] = (float)$pamount;
                    }
                }

            }
        }


        $order_data['print_url'] = wp_nonce_url(admin_url('admin.php?print_pos_receipt=true&order_id=' . $the_order->get_id()), 'print_pos_receipt');
        $order_data['stock_reduced'] = get_post_meta($the_order->get_id(), '_order_stock_reduced', true) ? true : false;

        return $order_data;
    }

    public function get_item_price($item)
    {
        $round = false;
        $inc_tax = wc_prices_include_tax();

        $qty = (!empty($item['quantity'])) ? $item['quantity'] : 1;

        if ($inc_tax) {
            $price = ($item['subtotal'] + $item['subtotal_tax']) / max(1, $qty);
        } else {
            $price = $item['subtotal'] / max(1, $qty);
        }

        $price = $round ? round($price, wc_get_price_decimals()) : $price;

        return $price;
    }


    public function filter_api_query_args($args, $request_args)
    {
        if (!empty($request_args['meta_key'])) {
            $args['meta_key'] = $request_args['meta_key'];
            unset($request_args['meta_key']);
        }
        if (!empty($request_args['meta_value'])) {
            $args['meta_value'] = $request_args['meta_value'];
            unset($request_args['meta_value']);
        }
        if (!empty($request_args['meta_compare'])) {
            $args['meta_compare'] = $request_args['meta_compare'];
            unset($request_args['meta_compare']);
        }

        if (!empty($args['s'])) {
            global $wpdb;
            $search_fields = array_map('wc_clean', apply_filters('woocommerce_shop_order_search_fields', array(
                '_order_key',
                '_billing_company',
                '_billing_address_1',
                '_billing_address_2',
                '_billing_city',
                '_billing_postcode',
                '_billing_country',
                '_billing_state',
                '_billing_email',
                '_billing_phone',
                '_shipping_address_1',
                '_shipping_address_2',
                '_shipping_city',
                '_shipping_postcode',
                '_shipping_country',
                '_shipping_state'
            )));

            $search_order_id = str_replace('Order #', '', $args['s']);
            if (!is_numeric($search_order_id)) {
                $search_order_id = 0;
            }

            // Search orders
            $post_ids = array_unique(array_merge(
                $wpdb->get_col(
                    $wpdb->prepare("
						SELECT DISTINCT p1.post_id
						FROM {$wpdb->postmeta} p1
						INNER JOIN {$wpdb->postmeta} p2 ON p1.post_id = p2.post_id
						WHERE
							( p1.meta_key = '_billing_first_name' AND p2.meta_key = '_billing_last_name' AND CONCAT(p1.meta_value, ' ', p2.meta_value) LIKE '%%%s%%' )
						OR
							( p1.meta_key = '_shipping_first_name' AND p2.meta_key = '_shipping_last_name' AND CONCAT(p1.meta_value, ' ', p2.meta_value) LIKE '%%%s%%' )
						OR
							( p1.meta_key IN ('" . implode("','", $search_fields) . "') AND p1.meta_value LIKE '%%%s%%' )
						",
                        esc_attr($args['s']), esc_attr($args['s']), esc_attr($args['s'])
                    )
                ),
                $wpdb->get_col(
                    $wpdb->prepare("
						SELECT order_id
						FROM {$wpdb->prefix}woocommerce_order_items as order_items
						WHERE order_item_name LIKE '%%%s%%'
						",
                        esc_attr($args['s'])
                    )
                ),
                array($search_order_id)
            ));
            unset($args['s']);

            $args['shop_order_search'] = true;

            // Search by found posts
            if (!empty($args['post__in'])) {
                $args['post__in'] = array_merge($args['post__in'], $post_ids);
            } else {
                $args['post__in'] = $post_ids;
            }
        }
        return $args;
    }

    public function api_coupon_response($coupon_data, $coupon, $fields, $server)
    {
        if (!empty($coupon_data) && is_array($coupon_data)) {
            $used_by = get_post_meta($coupon_data['id'], '_used_by');
            if ($used_by)
                $coupon_data['used_by'] = (array)$used_by;
            else
                $coupon_data['used_by'] = null;

            if (!$coupon->expiry_date)
                $coupon_data['expiry_date'] = false;

            $coupon_data['maximum_amount'] = $coupon->maximum_amount;
            $coupon_data['limit_usage_to_x_items'] = !empty($coupon->limit_usage_to_x_items) ? absint($coupon->limit_usage_to_x_items) : $coupon->limit_usage_to_x_items;
            $coupon_data['coupon_custom_fields'] = get_post_meta($coupon_data['id']);
        }
        return $coupon_data;
    }


}