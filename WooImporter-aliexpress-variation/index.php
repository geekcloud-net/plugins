<?php
/*
  Plugin Name: WooImporter Aliexpress Variations
  Description: Add-on for WooImporter. WooImporter Aliexpress Variations.
  Version: 1.2.8
  Author: Geometrix
  License: GPLv2+
  Author URI: http://gmetrixteam.com
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!defined('WPEAE_ALIEXPRESS_VARIATION_ROOT_URL')) {
    define('WPEAE_ALIEXPRESS_VARIATION_ROOT_URL', plugin_dir_url(__FILE__));
}
if (!defined('WPEAE_ALIEXPRESS_VARIATION_ROOT_PATH')) {
    define('WPEAE_ALIEXPRESS_VARIATION_ROOT_PATH', plugin_dir_path(__FILE__));
}

include_once dirname(__FILE__) . '/include/WPEAE_VariationAttributes.php';

if (!class_exists('WooImporter_AliexpressVariation')) {

    class WooImporter_AliexpressVariation {

        function __construct() {
            register_activation_hook(__FILE__, array($this, 'install'));
            register_deactivation_hook(__FILE__, array($this, 'uninstall'));

            add_filter('wpeae_woocommerce_after_addpost', array($this, 'aliexpress_variation_import'), 10, 3);

            add_filter('wpeae_woocommerce_update_price', array($this, 'aliexpress_variation_import'), 10, 3);

            add_action('wpeae_print_api_setting_page', array($this, 'setting_page'), 12, 1);

            add_action('wpeae_save_module_settings', array($this, 'save_settings'), 11, 2);

            add_action('admin_enqueue_scripts', array($this, 'add_assets'));

            add_action('wp_ajax_wpeae_aliexpress_variation_add_to_post', array($this, 'ajax_aliexpress_variation_add_to_post'));

            new WPEAE_VariationAttributes();
        }

        function install() {
            add_option('wpeae_aliexpress_varioation_load_image', true, '', 'no');
            add_option('wpeae_aliexpress_variation_use_chrome_extension', true, '', 'no');
            add_option('wpeae_aliexpress_variation_disable', false, '', 'no');
        }

        function uninstall() {
            /* delete_option('wpeae_aliexpress_varioation_load_image');
              delete_option('wpeae_aliexpress_variation_use_chrome_extension');
              delete_option('wpeae_aliexpress_variation_disable'); */
        }

        function add_assets() {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            $plugin_data = get_plugin_data(__FILE__);

            wp_enqueue_style('wpeae-ali-variation-admin-css', WPEAE_ALIEXPRESS_VARIATION_ROOT_URL . 'assets/css/admin_style.css', array(), $plugin_data['Version']);

            wp_enqueue_script('wpeae-ali-variation-ce-js', WPEAE_ALIEXPRESS_VARIATION_ROOT_URL . 'assets/js/chrome_extension.js', array('jquery'), $plugin_data['Version']);
        }

        function ajax_aliexpress_variation_add_to_post() {
            $result = array("state" => "ok", "message" => "");
            if (isset($_REQUEST['post_id']) && $_REQUEST['post_id'] && isset($_REQUEST['encode_html']) && $_REQUEST['encode_html']) {
                $post_id = $_REQUEST['post_id'];
                $goods = wpeae_get_goods_by_post_id($post_id);
                if ($goods) {
                    $html = $_REQUEST['encode_html'];
                    $html = base64_decode($html);

                    if ($this->is_aliexpress_sec_page($html)) {
                        $result = array("state" => "warning", "message" => "Banned by aliexpress");
                    } else {
                        $variations = $this->parse_aliexpress_variation_page($html);
                        $result = $this->add_variation_to_post($post_id, $goods, $variations);
                        //$result['variations']=$variations;
                    }
                }
            }

            echo json_encode($result);

            wp_die();
        }

        function aliexpress_variation_import($result, $post_id, $goods) {
            if ($goods->type === "aliexpress"/* && !get_option('wpeae_aliexpress_variation_disable', false) */) {
                //$url  = "http://" . $lang . ".aliexpress.com/item//" . $goods->external_id . ".html";
                $url = isset($goods->additional_meta['detail_url']) ? $goods->additional_meta['detail_url'] : '';
                if (get_option('wpeae_aliexpress_variation_use_chrome_extension', false)) {
                    wpeae_add_js_hook($result, 'wpeae_get_ali_page_html', array('url' => $url, 'post_id' => $post_id));
                } else {
                    if ($url) {
                        $proxy = wpeae_proxy_get();
                        $response = wpeae_remote_get($url, array('proxy' => $proxy, 'cookies_file' => wpeae_cookies_file_path($proxy)));
                        if (!is_wp_error($response)) {
                            $html = $response['body'];

                            $variations = $this->parse_aliexpress_variation_page($html);

                            $res = $this->add_variation_to_post($post_id, $goods, $variations);
                        }
                    }
                }
            }
            return $result;
        }

        private function add_variation_to_post($post_id, $goods, $variations) {
            $result = array('state' => 'ok', 'message' => '');
            if ($variations) {
                $tmp_product = wc_get_product($post_id);

                $wpeae_aliexpress_varioation_load_image = get_option('wpeae_aliexpress_varioation_load_image', false);

                if ($variations && $variations['attributes'] && ($tmp_product->is_type('variable') || $tmp_product->is_type('simple'))) {

                    if ($tmp_product->is_type('simple')) {
                        wp_set_object_terms($post_id, 'variable', 'product_type');
                    }

                    $localCurrency = strtoupper(get_option('wpeae_ali_local_currency', ''));

                    if ($localCurrency === 'USD') {
                        $localCurrency = '';
                    }

                    if ($localCurrency) {
                        $currency_conversion_factor = 1;
                    } else {
                        $currency_conversion_factor = floatval(get_option('wpeae_currency_conversion_factor', 1));
                    }

                    $attributes = array();
                    $tmp_attributes = get_post_meta($post_id, '_product_attributes', true);
                    if (!$tmp_attributes) {
                        $tmp_attributes = array();
                    }
                    foreach ($tmp_attributes as $attr) {
                        if (!intval($attr['is_variation'])) {
                            $attributes[] = $attr;
                        }
                    }

                    $attribute_ind = 0;
                    foreach ($variations['attributes'] as $key => $attr) {
                        $attribute_ind++;
                        $attr_tax = sanitize_title($attr['name']);
                        $variations['attributes'][$key]['tax'] = $attr_tax;
                        $attributes[$attr_tax] = array(
                            'name' => $attr['name'],
                            'value' => '',
                            'is_visible' => '0',
                            'is_variation' => '1',
                            'is_taxonomy' => '0'
                        );

                        $attr_values = array();
                        foreach ($attr['value'] as $val) {
                            $attr_values[] = $val['name'];
                            $image_meta = 'attr_' . $attr_tax . '_' . sanitize_title($val['name']) . '_img';

                            $attr_image = "";
                            if (isset($val['thumb']) && $val['thumb']) {
                                $attr_image = $val['thumb'];
                            } else if (isset($val['image']) && $val['image']) {
                                $attr_image = $val['image'];
                            }

                            if ($wpeae_aliexpress_varioation_load_image && $attr_image) {
                                $old_attachment_id = get_post_meta($post_id, $image_meta, true);
                                if ($old_attachment_id && intval($old_attachment_id) > 0) {
                                    wp_delete_attachment($old_attachment_id, true);
                                }

                                require_once(ABSPATH . 'wp-admin/includes/file.php');
                                require_once(ABSPATH . 'wp-admin/includes/media.php');
                                $attachment_id = WPEAE_WooCommerce::image_attacher($attr_image, $post_id, $goods->title.' Attribute '+$attribute_ind);
                                update_post_meta($post_id, $image_meta, $attachment_id);
                            }


                            $color_meta = 'attr_' . $attr_tax . '_' . sanitize_title($val['name']) . '_color';
                            if (isset($val['color']) && $val['color']) {
                                update_post_meta($post_id, $color_meta, $val['color']);
                            }
                        }
                        $attributes[$attr_tax]['value'] = implode("|", $attr_values);
                    }

                    update_post_meta($post_id, '_product_attributes', $attributes);

                    $old_variations = get_posts(array('post_type' => 'product_variation', 'fields' => 'ids', 'numberposts' => 100, 'post_parent' => $post_id, 'meta_query' => array()));

                    $cur_wpeae_not_available_product_status = get_option('wpeae_not_available_product_status', 'trash');

                    $total_stock = 0;
                    $variation_images = array();
                    $variation_ind = 0;
                    foreach ($variations['variations'] as $variation) {
                        $variation_ind++;
                        $cur_variation_image = "";
                        $aliexpress_sku_props_id = implode(";", $variation['attributes']);

                        $variation_attribute_list = array();
                        foreach ($variation['attributes'] as $va) {
                            $attr_tax = "";
                            $attr_value = "";
                            foreach ($variations['attributes'] as $attr) {
                                foreach ($attr['value'] as $val) {
                                    if ($val['id'] == $va) {
                                        $attr_tax = $attr['tax'];
                                        $attr_value = $val['name'];
                                        if (isset($val['image'])) {
                                            if (!isset($variation_images[$val['image']])) {
                                                $variation_images[$val['image']] = 0;
                                            }
                                            $cur_variation_image = $val['image'];
                                        }

                                        break;
                                    }
                                }
                                if ($attr_tax && $attr_value) {
                                    break;
                                }
                            }

                            if ($attr_tax && $attr_value) {
                                $variation_attribute_list[] = array('key' => ('attribute_' . $attr_tax), 'value' => $attr_value);
                            }
                        }

                        $args = array('post_type' => 'product_variation', 'fields' => 'ids', 'numberposts' => 100, 'post_parent' => $post_id, 'meta_query' => array(array('key' => '_aliexpress_sku_props', 'value' => $aliexpress_sku_props_id)));
                        $old_vid = get_posts($args);
                        $old_vid = $old_vid ? $old_vid[0] : false;

                        if (!$old_vid) {
                            $tmp_variation = array(
                                'post_title' => 'Product #' . $post_id . ' Variation',
                                'post_content' => '',
                                'post_status' => 'publish',
                                'post_parent' => $post_id,
                                'post_type' => 'product_variation'
                            );
                            $variation_id = wp_insert_post($tmp_variation);

                            update_post_meta($variation_id, '_aliexpress_sku_props', $aliexpress_sku_props_id);
                            update_post_meta($variation_id, '_stock_status', 'instock');

                            if ($wpeae_aliexpress_varioation_load_image) {
                                // upload set variation image
                                require_once(ABSPATH . 'wp-admin/includes/file.php');
                                require_once(ABSPATH . 'wp-admin/includes/media.php');
                                if ($cur_variation_image) {
                                    if (!$variation_images[$cur_variation_image]) {
                                        $thumb_id = WPEAE_WooCommerce::image_attacher($cur_variation_image, $variation_id, $goods->title.' Variation '.$variation_ind);
                                        $variation_images[$cur_variation_image] = $thumb_id;
                                    }
                                    set_post_thumbnail($variation_id, $variation_images[$cur_variation_image]);
                                }
                            }
                        } else {
                            $variation_id = $old_vid;
                        }

                        // update dimensions
                        if (isset($variations['additional_meta']['dimensions']['height'])) {
                            update_post_meta($variation_id, '_height', $variations['additional_meta']['dimensions']['height']);
                        }
                        if (isset($variations['additional_meta']['dimensions']['width'])) {
                            update_post_meta($variation_id, '_width', $variations['additional_meta']['dimensions']['width']);
                        }
                        if (isset($variations['additional_meta']['dimensions']['length'])) {
                            update_post_meta($variation_id, '_length', $variations['additional_meta']['dimensions']['length']);
                        }
                        if (isset($variations['additional_meta']['dimensions']['weight'])) {
                            update_post_meta($variation_id, '_weight', $variations['additional_meta']['dimensions']['weight']);
                        }

                        foreach ($variation_attribute_list as $vai) {
                            update_post_meta($variation_id, $vai['key'], $vai['value']);
                        }

                        foreach ($old_variations as $k => $id) {
                            if (intval($id) == intval($variation_id)) {
                                unset($old_variations[$k]);
                            }
                        }


                        if (!$goods->availability) {
                            if (get_option('woocommerce_manage_stock', 'no') === 'yes') {
                                if ($cur_wpeae_not_available_product_status == "outofstock") {
                                    update_post_meta($variation_id, '_manage_stock', 'yes');
                                    update_post_meta($variation_id, '_stock_status', 'outofstock');
                                    update_post_meta($variation_id, '_stock', 0);
                                } else if ($cur_wpeae_not_available_product_status == "instock") {
                                    update_post_meta($variation_id, '_manage_stock', 'no');
                                    update_post_meta($variation_id, '_stock_status', 'instock');
                                    delete_post_meta($variation_id, '_stock');
                                }
                            }else{
                                delete_post_meta($variation_id, '_manage_stock');
                                delete_post_meta($variation_id, '_stock');
                                update_post_meta($variation_id, '_stock_status', 'outofstock');
                            }
                        } else {
                            $tmp_quantity = intval($variation['quantity']);
                            if ($tmp_quantity && get_option('wpeae_aliexpress_variation_default_product_quantity', false)) {
                                $tmp_quantity = WPEAE_WooCommerce::random_quantity();
                            }

                            if (get_option('woocommerce_manage_stock', 'no') === 'yes') {
                                update_post_meta($variation_id, '_manage_stock', 'yes');
                                update_post_meta($variation_id, '_stock_status', $tmp_quantity ? 'instock' : 'outofstock');
                                update_post_meta($variation_id, '_stock', $tmp_quantity);
                            } else {
                                delete_post_meta($variation_id, '_manage_stock');
                                delete_post_meta($variation_id, '_stock');
                                update_post_meta($variation_id, '_stock_status', 'instock');
                            }
                            $total_stock += $tmp_quantity;
                        }


                        $tmp_goods = new WPEAE_Goods();
                        $tmp_goods->type = $goods->type;
                        $tmp_goods->external_id = $goods->external_id;
                        $tmp_goods->link_category_id = $goods->link_category_id;


                        //$user_page_currency = get_option('wpeae_aliexpress_variation_user_page_currency', false);

                        if ($localCurrency && isset($variation['mc_regular_price']) && isset($variation['mc_price'])) {
                            $tmp_goods->price = round($variation['mc_price'], 2);
                            $tmp_goods->user_price = round($variation['mc_price'], 2);
                            $tmp_goods->regular_price = round($variation['mc_regular_price'], 2);
                            $tmp_goods->user_regular_price = round($variation['mc_regular_price'], 2);
                        } else {
                            $tmp_goods->price = round($variation['price'], 2);
                            $tmp_goods->user_price = round($variation['price'] * $currency_conversion_factor, 2);
                            $tmp_goods->regular_price = round($variation['regular_price'], 2);
                            $tmp_goods->user_regular_price = round($variation['regular_price'] * $currency_conversion_factor, 2);
                        }
                        $tmp_goods->additional_meta = $goods->additional_meta;

                        $formulas = WPEAE_PriceFormula::get_goods_formula($tmp_goods);
                        if ($formulas) {
                            $tmp_goods->user_price = WPEAE_PriceFormula::apply_formula($tmp_goods->user_price, $formulas[0]);
                            $tmp_goods = WPEAE_PriceFormula::calc_regular_price($tmp_goods, $formulas[0]);
                        }

                        $update_price = get_option('wpeae_regular_price_auto_update', false);
                        if (!$old_vid || $update_price) {
                            WPEAE_WooCommerce::update_price($variation_id, $tmp_goods);
                        }
                    }

                    // delete old variations
                    foreach ($old_variations as $id) {
                        wp_delete_post($id);
                    }

                    // update total product stock
                    if ($goods->availability) {
                        if (get_option('woocommerce_manage_stock', 'no') === 'yes') {
                            update_post_meta($post_id, '_manage_stock', 'yes');
                            update_post_meta($post_id, '_stock_status', $total_stock ? 'instock' : 'outofstock');
                            update_post_meta($post_id, '_stock', $total_stock);
                        } else {
                            delete_post_meta($post_id, '_manage_stock');
                            delete_post_meta($post_id, '_stock');
                            update_post_meta($post_id, '_stock_status', 'instock');
                        }
                    }

                    // update dimensions
                    if (isset($variations['additional_meta']['dimensions']['height'])) {
                        update_post_meta($post_id, '_height', $variations['additional_meta']['dimensions']['height']);
                    }
                    if (isset($variations['additional_meta']['dimensions']['width'])) {
                        update_post_meta($post_id, '_width', $variations['additional_meta']['dimensions']['width']);
                    }
                    if (isset($variations['additional_meta']['dimensions']['length'])) {
                        update_post_meta($post_id, '_length', $variations['additional_meta']['dimensions']['length']);
                    }
                    if (isset($variations['additional_meta']['dimensions']['weight'])) {
                        update_post_meta($post_id, '_weight', $variations['additional_meta']['dimensions']['weight']);
                    }

                    wc_delete_product_transients($post_id);
                    WC_Product_Variable::sync($post_id);
                    WC_Product_Variable::sync_stock_status($post_id);
                }
            }
            return $result;
        }

        private function parse_aliexpress_variation_page($html) {
            $wpeae_ali_https_image_url = get_option('wpeae_ali_https_image_url', false);

            if (function_exists('mb_convert_encoding')) {
                $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
            } else {
                $html = htmlspecialchars_decode(utf8_decode(htmlentities($html, ENT_COMPAT, 'UTF-8', false)));
            }

            preg_match("/var[ ]*skuProducts[ ]*=[ ]*\[{([\s\S]*)}\];/", $html, $output_array);

            if (!$output_array)
                return false;

            $sky = "[{" . $output_array[1] . "}]";
            $tmp = json_decode($sky, true);

            $props = array();
            $tmp_name_cash = array();
            foreach ($tmp as $key => $val) {
                if (isset($val['skuAttr']) && $val['skuAttr']) {
                    $vg = explode(";", $val['skuAttr']);
                    foreach ($vg as $g) {
                        $tmp_v = explode("#", $g);
                        $ids = explode(":", $tmp_v[0]);

                        if (!isset($props[$ids[0]])) {
                            $props[$ids[0]] = array();
                            $props[$ids[0]]['id'] = $ids[0];
                            $props[$ids[0]]['name'] = '';
                            $props[$ids[0]]['value'] = array();
                        }

                        $vav_id = $ids[0] . ":" . $ids[1];
                        //$vav_id = $ids[1];
                        if (!isset($props[$ids[0]]['value'][$vav_id])) {
                            $props[$ids[0]]['value'][$vav_id] = array();
                            $props[$ids[0]]['value'][$vav_id]['id'] = $vav_id;
                            $props[$ids[0]]['value'][$vav_id]['name'] = $this->clean_name_value(isset($tmp_v[1]) ? str_replace(":", "", $tmp_v[1]) : '');

                            if ($props[$ids[0]]['value'][$vav_id]['name']) {
                                if (isset($tmp_name_cash[$props[$ids[0]]['value'][$vav_id]['name']])) {
                                    $props[$ids[0]]['value'][$vav_id]['name'] = $this->clean_name_value($props[$ids[0]]['value'][$vav_id]['name'] . " " . $props[$ids[0]]['value'][$vav_id]['id']);
                                }
                                $tmp_name_cash[$props[$ids[0]]['value'][$vav_id]['name']] = $this->clean_name_value($props[$ids[0]]['value'][$vav_id]['name']);
                            }
                        }
                    }
                }
            }

            $vars = array();

            foreach ($tmp as $v) {
                if (isset($v['skuPropIds']) && $v['skuPropIds'] && isset($v['skuVal']) && $v['skuVal']) {
                    $regular_price = isset($v['skuVal']['skuPrice']) ? $v['skuVal']['skuPrice'] : (isset($v['skuVal']['skuCalPrice']) ? $v['skuVal']['skuCalPrice'] : 0);
                    $price = isset($v['skuVal']['actSkuPrice']) ? $v['skuVal']['actSkuPrice'] : (isset($v['skuVal']['actSkuCalPrice']) ? $v['skuVal']['actSkuCalPrice'] : $regular_price);
                    $mc_regular_price = isset($v['skuVal']['skuMultiCurrencyCalPrice']) ? $v['skuVal']['skuMultiCurrencyCalPrice'] : $regular_price;
                    $mc_price = isset($v['skuVal']['actSkuMultiCurrencyCalPrice']) ? $v['skuVal']['actSkuMultiCurrencyCalPrice'] : $mc_regular_price;

                    $aa = array();
                    if (isset($v['skuAttr']) && $v['skuAttr']) {
                        $sky_attrs = explode(";", $v['skuAttr']);
                        foreach ($sky_attrs as $sky_attr) {
                            $tmp_v = explode("#", $sky_attr);
                            $aa[] = $tmp_v[0];
                        }
                    }

                    $vars[] = array(
                        //"attributes_old" => isset($v['skuPropIds']) ? array_merge(explode(",", $v['skuPropIds'])) : array(),
                        "attributes" => $aa,
                        "regular_price" => $regular_price,
                        "price" => $price,
                        "mc_regular_price" => $mc_regular_price,
                        "mc_price" => $mc_price,
                        "quantity" => isset($v['skuVal']['availQuantity']) ? $v['skuVal']['availQuantity'] : 0,
                        "inventory" => isset($v['skuVal']['inventory']) ? $v['skuVal']['inventory'] : 0,
                        "is_activity" => isset($v['skuVal']['isActivity']) ? $v['skuVal']['isActivity'] : "",
                    );
                }
            }

            $variation = array('src' => $tmp, 'attributes' => array_values($props), 'variations' => $vars);

            try {

                $dom = new DOMDocument();
                libxml_use_internal_errors(true);

                $dom->loadHTML($html);
                $dom->preserveWhiteSpace = false;

                if (isset($variation['attributes']) && $variation['attributes']) {

                    foreach ($variation['attributes'] as $key => $var) {
                        $finder = new DOMXPath($dom);
                        $rows = $finder->query("//*[contains(@data-sku-prop-id, '" . $var['id'] . "')]");
                        foreach ($rows as $row) {
                            $variation['attributes'][$key]['name'] = $this->clean_name_value(str_replace(":", "", $row->parentNode->parentNode->getElementsByTagName('dt')->item(0)->nodeValue));

                            foreach ($var['value'] as $sk => $s) {
                                $tmp = explode(":", $s['id']);
                                if (!isset($tmp[1])) {
                                    continue;
                                }

                                $attr_val_id = $tmp[1];

                                $finder = new DOMXPath($dom);
                                $skus = $finder->query("//*[@data-sku-id='" . $attr_val_id . "']/img");
                                if ($skus && $skus->length) {
                                    if ($wpeae_ali_https_image_url) {
                                        $variation['attributes'][$key]['value'][$sk]['thumb'] = $this->image_http_to_https($skus->item(0)->getAttribute('src'));
                                        $variation['attributes'][$key]['value'][$sk]['image'] = $this->image_http_to_https($skus->item(0)->getAttribute('bigpic'));
                                    } else {
                                        $variation['attributes'][$key]['value'][$sk]['thumb'] = $this->image_https_to_http($skus->item(0)->getAttribute('src'));
                                        $variation['attributes'][$key]['value'][$sk]['image'] = $this->image_https_to_http($skus->item(0)->getAttribute('bigpic'));
                                    }
                                    if (!$variation['attributes'][$key]['value'][$sk]['name']) {
                                        $variation['attributes'][$key]['value'][$sk]['name'] = $this->clean_name_value(str_replace(":", "", $skus->item(0)->getAttribute('title')));

                                        if ($variation['attributes'][$key]['value'][$sk]['name']) {
                                            if (isset($tmp_name_cash[$variation['attributes'][$key]['value'][$sk]['name']])) {
                                                $variation['attributes'][$key]['value'][$sk]['name'] = $this->clean_name_value($variation['attributes'][$key]['value'][$sk]['name'] . " " . $variation['attributes'][$key]['value'][$sk]['id']);
                                            }
                                            $tmp_name_cash[$variation['attributes'][$key]['value'][$sk]['name']] = $this->clean_name_value($variation['attributes'][$key]['value'][$sk]['name']);
                                        }
                                    }
                                }

                                $finder = new DOMXPath($dom);
                                $skus = $finder->query("//*[@data-sku-id='" . $attr_val_id . "']/span");
                                if ($skus && $skus->length) {
                                    if (!$variation['attributes'][$key]['value'][$sk]['name']) {
                                        $variation['attributes'][$key]['value'][$sk]['name'] = str_replace(":", "", $skus->item(0)->nodeValue);

                                        if ($variation['attributes'][$key]['value'][$sk]['name']) {
                                            if (isset($tmp_name_cash[$variation['attributes'][$key]['value'][$sk]['name']])) {
                                                $variation['attributes'][$key]['value'][$sk]['name'] = $this->clean_name_value($variation['attributes'][$key]['value'][$sk]['name'] . " " . $variation['attributes'][$key]['value'][$sk]['id']);
                                            }
                                            $tmp_name_cash[$variation['attributes'][$key]['value'][$sk]['name']] = $this->clean_name_value($variation['attributes'][$key]['value'][$sk]['name']);
                                        }
                                    }

                                    if (!$variation['attributes'][$key]['value'][$sk]['name']) {
                                        $variation['attributes'][$key]['value'][$sk]['name'] = $this->clean_name_value(str_replace(":", "", $skus->item(0)->getAttribute('title')));

                                        if ($variation['attributes'][$key]['value'][$sk]['name']) {
                                            if (isset($tmp_name_cash[$variation['attributes'][$key]['value'][$sk]['name']])) {
                                                $variation['attributes'][$key]['value'][$sk]['name'] = $this->clean_name_value($variation['attributes'][$key]['value'][$sk]['name'] . " " . $variation['attributes'][$key]['value'][$sk]['id']);
                                            }
                                            $tmp_name_cash[$variation['attributes'][$key]['value'][$sk]['name']] = $this->clean_name_value($variation['attributes'][$key]['value'][$sk]['name']);
                                        }
                                    }

                                    $class = $skus->item(0)->getAttribute('class');

                                    if ($class) {
                                        $res = $this->get_aliexpress_css($html);
                                        $color = $this->get_color_by_class($res, $class);

                                        if ($color) {
                                            if ($color['type'] == "image") {
                                                if ($wpeae_ali_https_image_url) {
                                                    $variation['attributes'][$key]['value'][$sk]['thumb'] = $this->image_http_to_https($color['value']);
                                                    $variation['attributes'][$key]['value'][$sk]['image'] = $this->image_http_to_https($color['value']);
                                                } else {
                                                    $variation['attributes'][$key]['value'][$sk]['thumb'] = $this->image_https_to_http($color['value']);
                                                    $variation['attributes'][$key]['value'][$sk]['image'] = $this->image_https_to_http($color['value']);
                                                }
                                            } else {
                                                $variation['attributes'][$key]['value'][$sk]['color'] = $color['value'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // get Packaging Details
                $finder = new DOMXPath($dom);
                $pack_details = $finder->query("//*[@class='packaging-des']");
                if ($pack_details && $pack_details->length) {
                    foreach ($pack_details as $pd) {
                        $value = trim(strval($pd->getAttribute('rel')));
                        if ($value) {
                            $parts = explode("|", $value);
                            if (count($parts) == 3) {
                                $variation['additional_meta']['dimensions']['length'] = floatval($parts[0]);
                                $variation['additional_meta']['dimensions']['width'] = floatval($parts[1]);
                                $variation['additional_meta']['dimensions']['height'] = floatval($parts[2]);
                            } else if (floatval($value) > 0) {
                                $variation['additional_meta']['dimensions']['weight'] = floatval($value);
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                error_log($e->getMessage());
            }

            return $variation;
        }

        private function get_aliexpress_css($html) {
            $result = array();
            try {
                if (!is_dir(WP_CONTENT_DIR . "/wpeae_cache/")) {
                    mkdir(WP_CONTENT_DIR . "/wpeae_cache/");
                }

                $dom = new DOMDocument();
                libxml_use_internal_errors(true);

                $dom->loadHTML($html);
                $dom->preserveWhiteSpace = false;

                $finder = new DOMXPath($dom);

                $styles = $finder->query('//link[@rel="stylesheet"]');
                foreach ($styles as $style) {
                    $href = $style->getAttribute('href');
                    $css_path = WP_CONTENT_DIR . "/wpeae_cache/" . md5($href) . ".css";
                    if (!file_exists($css_path)) {
                        $href = (strrpos($href, "http", -strlen($href)) === false) ? ("http:" . $href) : $href;
                        $css_response = wpeae_remote_get($href);
                        if (!is_wp_error($css_response)) {
                            $fp = fopen($css_path, 'w');
                            fwrite($fp, $css_response['body']);
                            fclose($fp);
                            $result[] = $css_path;
                        }
                    } else {
                        $result[] = $css_path;
                    }
                }
            } catch (Exception $e) {
                
            }

            return $result;
        }

        private function is_aliexpress_sec_page($html) {
            try {
                $dom = new DOMDocument();
                libxml_use_internal_errors(true);

                $dom->loadHTML($html);
                $dom->preserveWhiteSpace = false;

                $finder = new DOMXPath($dom);

                $els = $finder->query('//link[@rel="dns-prefetch"]');
                foreach ($els as $e) {
                    if ($e->getAttribute('href') === "//sec.aliexpress.com") {
                        return true;
                    }
                }

                $tmp = $dom->getElementById("mini-login-body");
                if ($tmp) {
                    return true;
                }
            } catch (Exception $e) {
                
            }
            return false;
        }

        private function get_color_by_class($files, $class) {
            if (!$files) {
                return false;
            }

            if (!is_array($files)) {
                $files = array($files);
            }

            foreach ($files as $f) {
                $c = file_get_contents($f);
                preg_match("/\." . $class . "[ ]*{background[ ]*:[ ]*url\(([\s\S]*)[ ]*\) 0 0 no-repeat!important[ ]*}/U", $c, $output_array);
                if ($output_array) {
                    return array("type" => "image", "value" => $output_array[1]);
                } else {
                    preg_match("/\." . $class . "[ ]*{background[ ]*:[ ]*([\s\S]*)[ ]*!important[ ]*}/U", $c, $output_array);
                    if ($output_array) {
                        return array("type" => "color", "value" => $output_array[1]);
                    }
                }
            }

            return false;
        }

        private function clean_name_value($value) {
            return str_replace("  ", " ", $value);
        }

        public function setting_page($api) {
            if ($api->get_type() === "aliexpress") {
                ?>
                <div class="aliexpress-variation-settings">
                    <h3>Variations setting</h3>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row" class="titledesc"><label for="wpeae_aliexpress_variation_udav">Use default variation attribute template</label></th>
                            <td class="forminp forminp-text"><input type="checkbox" id="wpeae_aliexpress_variation_udav" name="wpeae_aliexpress_variation_udav" value="yes" <?php if (get_option('wpeae_aliexpress_variation_udav', false)): ?>checked<?php endif; ?>/></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc"><label for="wpeae_aliexpress_varioation_load_image">Load variations images</label></th>
                            <td class="forminp forminp-text"><input type="checkbox" id="wpeae_aliexpress_varioation_load_image" name="wpeae_aliexpress_varioation_load_image" value="yes" <?php if (get_option('wpeae_aliexpress_varioation_load_image', false)): ?>checked<?php endif; ?>/></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc"><label for="wpeae_aliexpress_variation_use_chrome_extension">Use Chrome extension</label></th>
                            <td class="forminp forminp-text"><input type="checkbox" id="wpeae_aliexpress_variation_use_chrome_extension" name="wpeae_aliexpress_variation_use_chrome_extension" value="yes" <?php if (get_option('wpeae_aliexpress_variation_use_chrome_extension', false)): ?>checked<?php endif; ?>/></td>
                        </tr>
                        <!--
                        <tr valign="top">
                                <th scope="row" class="titledesc"><label for="wpeae_aliexpress_variation_disable">Disable loading variations</label></th>
                                <td class="forminp forminp-text"><input type="checkbox" id="wpeae_aliexpress_variation_disable" name="wpeae_aliexpress_variation_disable" value="yes" <?php if (get_option('wpeae_aliexpress_variation_disable', false)): ?>checked<?php endif; ?>/></td>
                        </tr>
                        -->
                        <tr valign="top">
                            <th scope="row" class="titledesc"><label for="wpeae_aliexpress_variation_default_product_quantity">Use default product quantity</label></th>
                            <td class="forminp forminp-text"><input type="checkbox" id="wpeae_aliexpress_variation_default_product_quantity" name="wpeae_aliexpress_variation_default_product_quantity" value="yes" <?php if (get_option('wpeae_aliexpress_variation_default_product_quantity', false)): ?>checked<?php endif; ?>/></td>
                        </tr>
                        <!--
                        <tr valign="top">
                                <th scope="row" class="titledesc"><label for="wpeae_aliexpress_variation_user_page_currency">Use page currency</label></th>
                                <td class="forminp forminp-text"><input type="checkbox" id="wpeae_aliexpress_variation_user_page_currency" name="wpeae_aliexpress_variation_user_page_currency" value="yes" <?php if (get_option('wpeae_aliexpress_variation_user_page_currency', false)): ?>checked<?php endif; ?>/></td>
                        </tr>
                        -->
                    </table>
                </div>

                <?php
            }
        }

        public function save_settings($api, $data) {
            if ($api->get_type() === "aliexpress") {
                update_option('wpeae_aliexpress_varioation_load_image', isset($data['wpeae_aliexpress_varioation_load_image']));
                update_option('wpeae_aliexpress_variation_use_chrome_extension', isset($data['wpeae_aliexpress_variation_use_chrome_extension']));
                update_option('wpeae_aliexpress_variation_disable', isset($data['wpeae_aliexpress_variation_disable']));
                update_option('wpeae_aliexpress_variation_udav', isset($data['wpeae_aliexpress_variation_udav']));

                update_option('wpeae_aliexpress_variation_default_product_quantity', isset($data['wpeae_aliexpress_variation_default_product_quantity']) ? 1 : 0);



                //update_option('wpeae_aliexpress_variation_user_page_currency', isset($data['wpeae_aliexpress_variation_user_page_currency'])?1:0);
            }
        }

        public static function image_http_to_https($image_url) {
            return preg_replace("/http:\/\/g(\d+)\.a\./i", "https://ae$1.", strval($image_url));
        }

        public static function image_https_to_http($image_url) {
            return preg_replace("/https:\/\/ae(\d+)\./i", "http://g$1.a.", strval($image_url));
        }

    }

}

new WooImporter_AliexpressVariation();
