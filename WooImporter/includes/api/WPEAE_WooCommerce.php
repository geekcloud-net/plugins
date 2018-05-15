<?php

/**
 * Description of WPEAE_WooCommerce
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_WooCommerce')) {

    class WPEAE_WooCommerce {

        public static function add_post($goods, $params = array()) {
            try {
                set_time_limit(500);
            } catch (Exception $e) {
                
            }

            do_action('wpeae_woocommerce_before_addpost', $goods);

            $result = array("state" => "ok", "message" => "");
            global $wpdb;

            $categories = WPEAE_WooCommerce::build_categories($goods);

            $product_type = get_option('wpeae_default_type', 'simple');

            $product_status = get_option('wpeae_default_status', 'publish');
            $product_status = isset($params['import_status']) && $params['import_status'] ? $params['import_status'] : $product_status;

            $post = array(
                'post_title' => $goods->get_prop('title'),
                'post_status' => $product_status,
                'post_name' => $goods->get_prop('title'),
                'post_type' => 'product',
                'tax_input' => array('product_cat' => $categories, 'product_type' => $product_type)
            );
            $product_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='external_id' AND meta_value='%s' LIMIT 1", $goods->getId()));
            if (!$product_id) {
                $post_id = wp_insert_post($post);
            } else {
                $post_id = $product_id;
            }

            update_post_meta($post_id, '_visibility', 'visible');
            update_post_meta($post_id, '_stock_status', 'instock');
            update_post_meta($post_id, '_sku', (string) $goods->external_id);
            update_post_meta($post_id, '_product_url', $goods->detail_url);

            update_post_meta($post_id, 'import_type', $goods->type);
            update_post_meta($post_id, 'external_id', (string) $goods->getId());
            update_post_meta($post_id, 'seller_url', $goods->seller_url);
            update_post_meta($post_id, 'product_url', $goods->detail_url);
            update_post_meta($post_id, 'total_sales', '0');

            WPEAE_WooCommerce::update_price($post_id, $goods);

            $additional_meta = WPEAE_Goods::get_normalized_value($goods, "additional_meta");

            if (isset($additional_meta['dimensions']['height'])) {
                update_post_meta($post_id, '_height', $additional_meta['dimensions']['height']);
            }
            if (isset($additional_meta['dimensions']['width'])) {
                update_post_meta($post_id, '_width', $additional_meta['dimensions']['width']);
            }
            if (isset($additional_meta['dimensions']['length'])) {
                update_post_meta($post_id, '_length', $additional_meta['dimensions']['length']);
            }
            if (isset($additional_meta['dimensions']['weight'])) {
                update_post_meta($post_id, '_weight', $additional_meta['dimensions']['weight']);
            }

            if (get_option('woocommerce_manage_stock', 'no') === 'yes') {
                if (isset($additional_meta['quantity'])) {
                    update_post_meta($post_id, '_manage_stock', 'yes');
                    update_post_meta($post_id, '_stock_status', intval($additional_meta['quantity']) ? 'instock' : 'outofstock');
                    update_post_meta($post_id, '_stock', intval($additional_meta['quantity']));
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

            if (isset($additional_meta['filters']) && $additional_meta['filters']) {
                update_post_meta($post_id, '_wpeae_filters', $additional_meta['filters']);
            }

            if (isset($additional_meta['detail_url']) && $additional_meta['detail_url']) {
                update_post_meta($post_id, 'original_product_url', $additional_meta['detail_url']);
            }

            if (isset($additional_meta['ship']) && WPEAE_Goods::normalized($additional_meta['ship'])) {
                update_post_meta($post_id, 'ship_price', $additional_meta['ship']);
            }

            if ($additional_meta && is_array($additional_meta)) {
                if (isset($additional_meta['attribute']) && $additional_meta['attribute']) {
                    WPEAE_WooCommerce::set_attributes($post_id, $additional_meta['attribute']);
                }
                if (isset($additional_meta['discount_perc']) && strlen(trim((string) $additional_meta['discount_perc'])) > 0) {
                    update_post_meta($post_id, 'discount_perc', $additional_meta['discount_perc']);
                }

                /*
                  unset($additional_meta['attribute']);
                  foreach($additional_meta as $key=>$val){
                  update_post_meta($post_id, 'wpeae_am_'.$key, serialize($val));
                  }
                 */
            }

            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $thumb_url = $goods->get_prop('image');
            if ($thumb_url) {
                try {
                    
                    if (0 === strpos($thumb_url, '//')) {
                        $thumb_url = "https:".$thumb_url;
                    }
                    if (0 === strpos($thumb_url, 'http')) {
                        $thumb_id = WPEAE_WooCommerce::image_attacher($thumb_url, $post_id, $goods->get_prop('title'));
                        set_post_thumbnail($post_id, $thumb_id);
                    }
                } catch (Exception $e) {
                    error_log($e);
                }
            }

            $images_url = $goods->getAllPhotos();

            $images_limit = intval(get_option('wpeae_import_product_images_limit'));

            $image_gallery_ids = '';
            $cnt = 0;
            foreach (array_slice($images_url, 1) as $image_url) {
                if ($thumb_url != $image_url) {
                    if (($cnt++) < $images_limit || !$images_limit) {
                        try {
                            if (0 === strpos($image_url, '//')) {
                                $image_url = "https:".$image_url;
                            }
                            if (0 === strpos($image_url, 'http')) {
                                $image_gallery_ids .= WPEAE_WooCommerce::image_attacher($image_url, $post_id, $goods->get_prop('title') . ' ' . $cnt) . ',';
                            }
                        } catch (Exception $e) {
                            error_log($e);
                            $result['state'] = "warn";
                            $result['message'] = "\nimg_warn: $image_url";
                        }
                    }
                }
            }
            update_post_meta($post_id, '_product_image_gallery', $image_gallery_ids);

            $post_content = '';

            if (get_option('wpeae_import_load_image_from_descr', false)) {
                $post_content = WPEAE_WooCommerce::build_description($goods, $post_id);
            } else {
                $post_content = WPEAE_Goods::normalized($goods->get_prop('description'));
            }

            $post_content = WPEAE_WooCommerce::seo_description($post_content, $goods);

            wp_update_post(array('ID' => $post_id, 'post_content' => $post_content, 'tax_input' => array('product_cat' => $categories)));

            wc_delete_product_transients($post_id);

            return apply_filters('wpeae_woocommerce_after_addpost', $result, $post_id, $goods);
        }

        public static function seo_description($html, $goods) {

            if (!empty($html)) {

                if (function_exists('mb_convert_encoding')) {
                    $html = trim(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
                } else {
                    $html = htmlspecialchars_decode(utf8_decode(htmlentities($html, ENT_COMPAT, 'UTF-8', false)));
                }

                $dom = new DOMDocument();
                @$dom->loadHTML($html);
                $dom->formatOutput = true;
                $tags = $dom->getElementsByTagName('img');

                $title = $goods->get_prop('title');
                $k = 1;
                foreach ($tags as $tag) {

                    $tag->setAttribute('alt', $title . ' ' . $k);
                    $tag->setAttribute('title', $title . ' ' . $k);
                    $k++;
                }

                $html = $dom->saveHTML();
            }

            return $html;
        }

        public static function build_description($goods, $post_id) {
            $html = $goods->get_prop('description');

            if (function_exists('mb_convert_encoding')) {
                $html = trim(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
            } else {
                $html = htmlspecialchars_decode(utf8_decode(htmlentities($html, ENT_COMPAT, 'UTF-8', false)));
            }

            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $src_result = array();
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $dom->formatOutput = true;
            $tags = $dom->getElementsByTagName('img');

            foreach ($tags as $tag) {
                $attr_image = $tag->getAttribute('src');
                if (0 === strpos($attr_image, '//')) {
                    $attr_image = "https:".$attr_image;
                }
                
                if (0 === strpos($attr_image, 'http')) {
                    $attachment_id = WPEAE_WooCommerce::image_attacher($attr_image, $post_id, $goods->get_prop('title'));
                    $attachment_url = wp_get_attachment_url($attachment_id);

                    $tag->setAttribute('src', $attachment_url);
                }
            }


            $html = $dom->saveHTML();

            return WPEAE_Goods::normalized($html);
        }

        public static function update_price($post_id, $goods) {
            if (!$goods->user_regular_price) {
                update_post_meta($post_id, '_price', $goods->user_price);
                update_post_meta($post_id, '_regular_price', $goods->user_price);
                delete_post_meta($post_id, '_sale_price');
            } else {
                if (abs($goods->user_price - $goods->user_regular_price) < 0.001) {
                    update_post_meta($post_id, '_price', $goods->user_price);
                    update_post_meta($post_id, '_regular_price', $goods->user_price);
                    delete_post_meta($post_id, '_sale_price');
                } else {
                    update_post_meta($post_id, '_regular_price', $goods->user_regular_price);
                    update_post_meta($post_id, '_sale_price', $goods->user_price);
                    update_post_meta($post_id, '_price', $goods->user_price);
                }
            }
        }

        public static function update_post($goods) {
            $result = array("state" => "ok", "message" => "");

            return $result;
        }

        public static function build_categories($goods) {
            if ($goods->link_category_id) {
                return array(IntVal($goods->link_category_id));
            } else {
                $category_name = wp_unslash(WPEAE_Goods::get_normalized_value($goods, "category_name"));
                if ($category_name) {
                  //  $cat = get_term_by('name', $category_name, 'product_cat');
                    $cat = get_terms( 'product_cat', array(
                                        'name' => $category_name,
                                        'hide_empty' => false,
                                    ) );
                
                    if (empty($cat)) {
                  
                        $cat = wp_insert_term($category_name, 'product_cat');
                 
                        $cat_id = $cat['term_id'];
                    } else {
                        $cat_id = $cat[0]->term_id;
                    }
                    return array($cat_id);
                }
            }
            return array();
        }

        public static function image_attacher($image_url, $post_id, $desc = null) {
            $image = WPEAE_WooCommerce::download_url($image_url);
            if ($image) {
                $file_array = array(
                    'name' => basename($image),
                    'size' => filesize($image),
                    'tmp_name' => $image
                );
                return media_handle_sideload($file_array, $post_id, $desc);
            } else {
                return false;
            }
        }

        public static function download_url($url) {
            $wp_upload_dir = wp_upload_dir();
            $parsed_url = parse_url($url);
            $pathinfo = pathinfo($parsed_url['path']);
            if (!$pathinfo || !isset($pathinfo['extension'])) {
                return false;
            }
            $dest_filename = wp_unique_filename($wp_upload_dir['path'], mt_rand() . "." . (in_array(strtolower($pathinfo['extension']), array("jpg","png","jpeg"))?$pathinfo['extension']:"jpg" ));

            $dest_path = $wp_upload_dir['path'] . '/' . $dest_filename;

            $response = wpeae_remote_get($url);
            if (is_wp_error($response)) {
                $file_content = file_get_contents($url);
                if($file_content){
                    file_put_contents($dest_path, $file_content);
                }else{
                    return false;
                }
            } elseif (!in_array($response['response']['code'], array(404, 403))) {
                file_put_contents($dest_path, $response['body']);
            }

            if (!file_exists($dest_path)) {
                return false;
            } else {
                return $dest_path;
            }
        }

        public static function set_attributes($post_id, $attributes) {
            $extended_attribute = get_option('wpeae_import_extended_attribute', false);
            if ($extended_attribute) {
                $helper = new WPEAE_Helper();
                $helper->set_woocommerce_attributes($attributes, $post_id);
            } else {
                $name = array_column($attributes, 'name');
                $count = array_count_values($name);
                $duplicate = array_unique(array_diff_assoc($name, array_unique($name)));
                $product_attributes = array();

                foreach ($attributes as $name => $value) {

                    if (isset($duplicate[$name + 1])) {
                        $val = array();
                        for ($i = 0; $i < $count[$value['name']]; $i++) {
                            $val[] = $attributes[$name + $i]['value'];
                        }
                        $product_attributes[str_replace(' ', '-', $value['name'])] = array(
                            'name' => $value['name'],
                            'value' => implode(', ', $val),
                            'position' => 0,
                            'is_visible' => 1,
                            'is_variation' => 0,
                            'is_taxonomy' => 0
                        );
                    } elseif (!array_search($value['name'], $duplicate)) {
                        $product_attributes[str_replace(' ', '-', $value['name'])] = array(
                            'name' => $value['name'],
                            'value' => $value['value'],
                            'position' => 0,
                            'is_visible' => 1,
                            'is_variation' => 0,
                            'is_taxonomy' => 0
                        );
                    }
                }

                update_post_meta($post_id, '_product_attributes', $product_attributes);
            }
        }

        private static function save_one_attribute($attr_data) {
            global $wpdb;

            $attribute = array(
                'attribute_label' => wc_clean(stripslashes($attr_data['name'])),
                'attribute_name' => wc_sanitize_taxonomy_name(stripslashes($attr_data['name'])),
                'attribute_type' => 'select',
                'attribute_orderby' => '',
                'attribute_public' => 0
            );

            if (taxonomy_exists(wc_attribute_taxonomy_name($attribute['attribute_name']))) {
                
            } else {
                $wpdb->insert($wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute);
            }



            flush_rewrite_rules();

            delete_transient('wc_attribute_taxonomies');
        }

        public static function random_quantity() {
            $min_q = intval(get_option('wpeae_min_product_quantity', 5));
            $max_q = intval(get_option('wpeae_max_product_quantity', 10));
            $min_q = $min_q ? $min_q : 1;
            $max_q = ($max_q && $max_q > $min_q) ? $max_q : $min_q;
            $quantity = rand($min_q, $max_q);
            return $quantity;
        }

    }

}

if (!function_exists('array_column')) {

    /**
     * Returns the values from a single column of the input array, identified by
     * the $columnKey.
     *
     * Optionally, you may provide an $indexKey to index the values in the returned
     * array by the values from the $indexKey column in the input array.
     *
     * @param array $input A multi-dimensional array (record set) from which to pull
     *                     a column of values.
     * @param mixed $columnKey The column of values to return. This value may be the
     *                         integer key of the column you wish to retrieve, or it
     *                         may be the string key name for an associative array.
     * @param mixed $indexKey (Optional.) The column to use as the index/keys for
     *                        the returned array. This value may be the integer key
     *                        of the column, or it may be the string key name.
     * @return array
     */
    function array_column($input = null, $columnKey = null, $indexKey = null) {
        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $argc = func_num_args();
        $params = func_get_args();

        if ($argc < 2) {
            trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
            return null;
        }

        if (!is_array($params[0])) {
            trigger_error('array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given', E_USER_WARNING);
            return null;
        }

        if (!is_int($params[1]) && !is_float($params[1]) && !is_string($params[1]) && $params[1] !== null && !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {
            trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
            return false;
        }

        if (isset($params[2]) && !is_int($params[2]) && !is_float($params[2]) && !is_string($params[2]) && !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {
            trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
            return false;
        }

        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;

        $paramsIndexKey = null;
        if (isset($params[2])) {
            if (is_float($params[2]) || is_int($params[2])) {
                $paramsIndexKey = (int) $params[2];
            } else {
                $paramsIndexKey = (string) $params[2];
            }
        }

        $resultArray = array();

        foreach ($paramsInput as $row) {

            $key = $value = null;
            $keySet = $valueSet = false;

            if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                $keySet = true;
                $key = (string) $row[$paramsIndexKey];
            }

            if ($paramsColumnKey === null) {
                $valueSet = true;
                $value = $row;
            } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                $valueSet = true;
                $value = $row[$paramsColumnKey];
            }

            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }
        }

        return $resultArray;
    }

}