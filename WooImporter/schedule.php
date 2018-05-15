<?php

if (!function_exists('wpeae_more_reccurences')) {

    function wpeae_more_reccurences($schedules) {
        $schedules['wpeae_5_mins'] = array('interval' => 5 * 60, 'display' => __('Every 5 Minutes', 'wpeae'));
        $schedules['wpeae_15_mins'] = array('interval' => 15 * 60, 'display' => __('Every 15 Minutes', 'wpeae'));
        return $schedules;
    }

}
add_filter('cron_schedules', 'wpeae_more_reccurences');

if (!function_exists('wpeae_schedule_proc')) {

    function wpeae_schedule_proc($show_trace = true) {

        set_error_handler("wpeae_error_handler");
        if ($show_trace) {
            echo "<br/>TARCE (" . date("Y-m-d H:i:s", time()) . "): posted schedule products<br/>";
        }
        $list = WPEAE_Goods::load_goods_list(1, 100, " AND NULLIF(NULLIF(user_schedule_time, '0000-00-00 00:00:00'), '') IS NOT NULL AND user_schedule_time < now()");

        if ($list["items"]) {
            foreach ($list["items"] as $goods) {

                try {
                    if ($show_trace) {
                        echo "TARCE (" . date("Y-m-d H:i:s", time()) . "): check date {$goods->user_schedule_time}<br/>";
                    }

                    if ($show_trace) {
                        echo "TARCE (" . date("Y-m-d H:i:s", time()) . "): posted...<br/>";
                    }

                    $loader = wpeae_get_loader($goods->type);

                    if ($loader) {
                        if ($goods->need_load_more_detail()) {
                            $result = $loader->load_detail_proc($goods);
                        }

                        if (!$goods->post_id && class_exists('WPEAE_WooCommerce')) {
                            $result = WPEAE_WooCommerce::add_post($goods);
                        }

                        $goods->save_field("user_schedule_time", NULL);

                        if ($show_trace) {
                            echo "TARCE (" . date("Y-m-d H:i:s", time()) . "): ok<br/>";
                        }
                    } else {
                        if ($show_trace) {
                            echo "TARCE (" . date("Y-m-d H:i:s", time()) . "): loader not found<br/>";
                        }
                    }
                } catch (Exception $e) {
                    echo $e->getMessage() . "<br/>";
                }
            }
        } else {
            if ($show_trace) {
                echo "TARCE (" . date("Y-m-d H:i:s", time()) . "): products to post not found<br/>";
            }
        }

        restore_error_handler();
    }

}
add_action('wpeae_schedule_post_event', 'wpeae_schedule_proc');


if (!function_exists('wpeae_update_price_proc')) {

    function wpeae_update_price_proc($productId = false, $show_trace = true) {
        $result = array("state" => "ok", "message" => "");

        if (!get_option('wpeae_price_auto_update', false)) {
            return;
        }
        $update_price = get_option('wpeae_regular_price_auto_update', false);


        set_error_handler("wpeae_error_handler");
        try {
            if ($show_trace) {
                echo "TARCE (" . date("Y-m-d H:i:s", time()) . "): update stock availability<br/>";
            }

            if ($productId) {
                $posts_by_time = array($productId);
            } else {
                $cnt = get_option('wpeae_update_per_schedule', 20);
                echo "products in work: $cnt<br/>";
                $posts_by_time = wpeae_get_sorted_products_ids("price_last_update", get_option('wpeae_update_per_schedule', 20));
            }


            $cur_wpeae_not_available_product_status = get_option('wpeae_not_available_product_status', 'trash');

            foreach ($posts_by_time as $post_id) {
                $external_id = get_post_meta($post_id, "external_id", true);
                if ($external_id) {
                    $goods = new WPEAE_Goods($external_id);
                    /* @var $loader WPEAE_AbstractLoader */
                    $loader = wpeae_get_loader($goods->type);

                    if ($loader) {
                        $filters = get_post_meta($post_id, "_wpeae_filters", true);
                        $result = $loader->get_detail_proc($goods->external_id, array_merge(array('wc_product_id' => $post_id), is_array($filters) ? $filters : array()));
                        if ($result['state'] == "ok") {
                            $goods = $result['goods'];
                            
                            if(!$goods->type ||  !$goods->external_id){
                                $goods = wpeae_get_goods_by_post_id($post_id);
                                $goods->availability = $result['goods']->availability;
                            }

                            // check availability
                            update_post_meta($post_id, '_wpeae_availability', $goods->availability?'yes':'no');
                            
                            if (!$goods->availability) {
                                if ($show_trace) {
                                    echo "TARCE (" . date("Y-m-d H:i:s", time()) . "):move to trash {$post_id}<br>";
                                }
                                if ($cur_wpeae_not_available_product_status == "trash") {
                                    wp_trash_post($post_id);
                                } else if ($cur_wpeae_not_available_product_status == "outofstock") {
                                    update_post_meta($post_id, '_manage_stock', 'yes');
                                    update_post_meta($post_id, '_stock_status', 'outofstock');
                                    update_post_meta($post_id, '_stock', 0);
                                } else if ($cur_wpeae_not_available_product_status == "instock") {
                                    update_post_meta($post_id, '_manage_stock', 'no');
                                    update_post_meta($post_id, '_stock_status', 'instock');
                                    update_post_meta($post_id, '_stock', 100);
                                    //delete_post_meta($post_id, '_stock');
                                }
                                
                                /*if (get_option('woocommerce_manage_stock', 'no') !== 'yes') {
                                    delete_post_meta($post_id, '_manage_stock', 'no');
                                    delete_post_meta($post_id, '_stock');
                                }*/
                            } else {
                                wp_untrash_post($post_id);
                                
                                
                                if (get_option('woocommerce_manage_stock', 'no') === 'yes') {
                                    if (isset($goods->additional_meta['quantity'])) {
                                        update_post_meta($post_id, '_manage_stock', 'yes');
                                        update_post_meta($post_id, '_stock', intval($goods->additional_meta['quantity']));
                                        update_post_meta($post_id, '_stock_status', intval($goods->additional_meta['quantity']) ? 'instock' : 'outofstock');
                                    } else {
                                        $min_q = intval(get_option('wpeae_min_product_quantity', 5));
                                        $max_q = intval(get_option('wpeae_max_product_quantity', 10));
                                        $min_q = $min_q ? $min_q : 1;
                                        $max_q = ($max_q && $max_q > $min_q) ? $max_q : $min_q;
                                        $quantity = rand($min_q, $max_q);

                                        if ($max_q > 1) {
                                            update_post_meta($post_id, '_manage_stock', 'yes');
                                            update_post_meta($post_id, '_stock', $quantity);
                                            update_post_meta($post_id, '_stock_status', $quantity ? 'instock' : 'outofstock');
                                        }
                                    }
                                } else {
                                    delete_post_meta($post_id, '_manage_stock');
                                    delete_post_meta($post_id, '_stock');
                                    update_post_meta($post_id, '_stock_status', 'instock');
                                }
                                
                                if ($show_trace) {
                                    echo "TARCE (" . date("Y-m-d H:i:s", time()) . "): product {$post_id} OK<br>";
                                }

                                if ($update_price) {
                                    if ($post_id && class_exists('WPEAE_WooCommerce')) {
                                        WPEAE_WooCommerce::update_price($post_id, $goods);
                                        if(isset($goods->additional_meta['discount_perc'])){
                                            update_post_meta($post_id, 'discount_perc', $goods->additional_meta['discount_perc']);
                                        }
                                    } else {
                                        echo "TARCE (" . date("Y-m-d H:i:s", time()) . "): product {$post_id} Update price error!<br>";
                                    }


                                    if ($show_trace) {
                                        echo "TARCE (" . date("Y-m-d H:i:s", time()) . "): update regular price for {$goods->getId()}: {$goods->user_price}<br>";
                                    }
                                }
                            }

                            //update filters
                            if (isset($goods->additional_meta['filters']) && $goods->additional_meta['filters']) {
                                update_post_meta($post_id, '_wpeae_filters', $goods->additional_meta['filters']);
                            }

                            /* update URLs*/
                            update_post_meta($post_id, '_product_url', $goods->detail_url);
                            update_post_meta($post_id, 'product_url', $goods->detail_url);
                            if (isset($goods->additional_meta['detail_url']) && $goods->additional_meta['detail_url']) {
                                update_post_meta($post_id, 'original_product_url', $goods->additional_meta['detail_url']);
                            }
                            
                            

                            $result = apply_filters('wpeae_woocommerce_update_price', $result, $post_id, $goods);
                        } else {
                            if ($show_trace) {
                                echo "TARCE (" . date("Y-m-d H:i:s", time()) . "): error while update price for {$post_id}: {$result['message']}<br>";
                            }
                        }

                        update_post_meta($post_id, 'price_last_update', time());
                    }
                }
            }
        } catch (Exception $e) {
            $result = array("state" => "error", "message" => $e->getMessage());
            if ($show_trace) {
                echo $e->getMessage() . "<br/>";
            }
        }

        restore_error_handler();

        return $result;
    }

}
add_action('wpeae_update_price_event', 'wpeae_update_price_proc');
