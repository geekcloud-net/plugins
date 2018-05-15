<?php

if (!class_exists('WPEAE_EbayVariationImporter')) {

    class WPEAE_EbayVariationImporter {

        function __construct() {
            add_filter('wpeae_modify_goods_data', array($this, 'modify_goods_data'), 10, 3);

            add_filter('wpeae_woocommerce_after_addpost', array($this, 'variation_import'), 10, 3);
            add_filter('wpeae_woocommerce_update_price', array($this, 'variation_import'), 10, 3);
        }

        function modify_goods_data($goods, $data, $filter_type) {
            if ($goods && $goods->type == "ebay" && ($filter_type === "ebay_load_detail" || $filter_type === "ebay_get_detail")) {
                $goods->additional_meta['ebay_variations'] = $this->parse($data);
                $goods->save("API");
            }
            return $goods;
        }

        function variation_import($result, $post_id, $goods) {
            if ($goods->type === "ebay" && isset($goods->additional_meta['ebay_variations']) && $goods->additional_meta['ebay_variations']) {
                $tmp_result = $this->add_to_post($post_id, $goods, $goods->additional_meta['ebay_variations']);
                //$res - unused var? don't worry it's ok!
            }
            return $result;
        }

        public function parse($data) {
            $variations = array('attributes' => array(), 'variations' => array());
            if (isset($data->Item->Variations)) {
                foreach ($data->Item->Variations->VariationSpecificsSet->NameValueList as $attr) {
                    $attr_id = sanitize_title(strval($attr->Name));
                    $one_attr = array('id' => $attr_id, 'name' => strval($attr->Name), 'value' => array());
                    $ind = 0;
                    foreach ($attr->Value as $attr_val) {
                        $attr_val_id = $attr_id . $ind++;
                        $one_attr['value'][$attr_val_id] = array('id' => $attr_val_id, 'name' => strval($attr_val));

                        if (isset($data->Item->Variations->Pictures) && isset($data->Item->Variations->Pictures->VariationSpecificName) && strval($data->Item->Variations->Pictures->VariationSpecificName) === strval($attr->Name)) {
                            foreach ($data->Item->Variations->Pictures->VariationSpecificPictureSet as $img) {
                                if (strval($img->VariationSpecificValue) === strval($attr_val)) {
                                    if (isset($img->PictureURL)) {
                                        foreach ($img->PictureURL as $pic) {
                                            $one_attr['value'][$attr_val_id]['image'] = strval($pic);
                                            $one_attr['value'][$attr_val_id]['thumb'] = strval($pic);
                                            break; //take only one image!
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $variations['attributes'][] = $one_attr;
                }
                foreach ($data->Item->Variations->Variation as $var) {
                    $one_var = array('attributes' => array());
                    $one_var['regular_price'] = floatval($var->StartPrice);
                    $one_var['price'] = floatval($var->StartPrice);
                    $one_var['mc_regular_price'] = floatval($var->StartPrice);
                    $one_var['mc_price'] = floatval($var->StartPrice);
                    $tmp_quantity = IntVal($var->Quantity) - (isset($var->SellingStatus->QuantitySold) ? IntVal($var->SellingStatus->QuantitySold) : 0);
                    $tmp_quantity = $tmp_quantity >= 0 ? $tmp_quantity : 0;
                    $one_var['quantity'] = $tmp_quantity;
                    $one_var['inventory'] = $tmp_quantity;
                    $one_var['is_activity'] = $tmp_quantity > 0 ? 1 : 0;

                    foreach ($var->VariationSpecifics->NameValueList as $var_attr) {
                        foreach ($variations['attributes'] as $attr) {
                            if ($attr['name'] === strval($var_attr->Name)) {
                                foreach ($attr['value'] as $attr_val) {
                                    if ($attr_val['name'] === strval($var_attr->Value)) {
                                        $one_var['attributes'][] = $attr_val['id'];
                                        break;
                                    }
                                }
                                break;
                            }
                        }
                    }
                    $variations['variations'][] = $one_var;
                }
            }
            return $variations;
        }

        public function add_to_post($post_id, $goods, $variations) {
            $result = array('state' => 'ok', 'message' => '');
            if ($variations && $variations['attributes'] && $variations['variations']) {
                $tmp_product = wc_get_product($post_id);

                if ($tmp_product && ($tmp_product->is_type('variable') || $tmp_product->is_type('simple'))) {

                    if ($tmp_product->is_type('simple')) {
                        wp_set_object_terms($post_id, 'variable', 'product_type');
                    }


                    $currency_conversion_factor = floatval(get_option('wpeae_currency_conversion_factor', 1));


                    $attributes = get_post_meta($post_id, '_product_attributes', true);
                    if (!$attributes) {
                        $attributes = array();
                    }

                    foreach ($variations['attributes'] as $key => $attr) {
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
                            if (isset($val['image']) && $val['image']) {
                                update_post_meta($post_id, $image_meta, $val['image']);
                            }
                            $color_meta = 'attr_' . $attr_tax . '_' . sanitize_title($val['name']) . '_color';
                            if (isset($val['color']) && $val['color']) {
                                update_post_meta($post_id, $color_meta, $val['color']);
                            }
                        }
                        $attributes[$attr_tax]['value'] = implode("|", $attr_values);
                        update_post_meta($post_id, '_product_attributes', $attributes);
                    }
                    update_post_meta($post_id, '_product_attributes', $attributes);

                    $old_variations = get_posts(array('post_type' => 'product_variation', 'fields' => 'ids', 'numberposts' => 100, 'post_parent' => $post_id, 'meta_query' => array()));

                    $total_stock = 0;
                    $variation_images = array();
                    foreach ($variations['variations'] as $variation) {
                        $cur_variation_image = "";

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

                        $args = array('post_type' => 'product_variation', 'fields' => 'ids', 'numberposts' => 100, 'post_parent' => $post_id, 'meta_query' => array());
                        foreach ($variation_attribute_list as $tmp_va) {
                            $args['meta_query'][] = array('key' => $tmp_va['key'], 'value' => $tmp_va['value']);
                        }
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
                        } else {
                            $variation_id = $old_vid;
                        }

                        foreach ($old_variations as $k => $id) {
                            if (intval($id) == intval($variation_id)) {
                                unset($old_variations[$k]);
                            }
                        }

                        update_post_meta($variation_id, '_manage_stock', 'yes');
                        update_post_meta($variation_id, '_stock_status', intval($variation['quantity']) ? 'instock' : 'outofstock');
                        update_post_meta($variation_id, '_stock', intval($variation['quantity']));
                        $total_stock += intval($variation['quantity']);

                        $tmp_goods = new WPEAE_Goods();
                        $tmp_goods->type = $goods->type;
                        $tmp_goods->external_id = $goods->external_id;
                        $tmp_goods->link_category_id = $goods->link_category_id;

                        $tmp_goods->price = round($variation['price'], 2);
                        $tmp_goods->user_price = round($variation['price'] * $currency_conversion_factor, 2);
                        $tmp_goods->regular_price = round($variation['regular_price'], 2);
                        $tmp_goods->user_regular_price = round($variation['regular_price'] * $currency_conversion_factor, 2);

                        $tmp_goods->additional_meta = $goods->additional_meta;

                        $formulas = WPEAE_PriceFormula::get_goods_formula($tmp_goods);
                        if ($formulas) {
                            $tmp_goods->user_price = WPEAE_PriceFormula::apply_formula($tmp_goods->user_price, $formulas[0]);
                            $tmp_goods = WPEAE_PriceFormula::calc_regular_price($tmp_goods, $formulas[0]);
                        }
                        
                        $update_price = get_option('wpeae_regular_price_auto_update', false);
                        if(!$old_vid || $update_price){
                            WPEAE_WooCommerce::update_price($variation_id, $tmp_goods);
                        }

                        // if this is new variation, update variation atribute 
                        if (!$old_vid) {
                            foreach ($variation_attribute_list as $vai) {
                                update_post_meta($variation_id, $vai['key'], $vai['value']);
                            }
                        }

                        if (!has_post_thumbnail($variation_id)) {
                            require_once(ABSPATH . 'wp-admin/includes/file.php');
                            require_once(ABSPATH . 'wp-admin/includes/media.php');
                            if ($cur_variation_image) {
                                if (!$variation_images[$cur_variation_image]) {
                                    $thumb_id = WPEAE_WooCommerce::image_attacher($cur_variation_image, $variation_id);
                                    $variation_images[$cur_variation_image] = $thumb_id;
                                }
                                set_post_thumbnail($variation_id, $variation_images[$cur_variation_image]);
                            }
                        }
                    }

                    // delete old variations
                    foreach ($old_variations as $id) {
                        wp_delete_post($id);
                    }

                    // update total product stock
                    update_post_meta($post_id, '_manage_stock', 'yes');
                    update_post_meta($post_id, '_stock_status', $total_stock ? 'instock' : 'outofstock');
                    update_post_meta($post_id, '_stock', $total_stock);

                    wc_delete_product_transients($post_id);
                    WC_Product_Variable::sync($post_id);
                    WC_Product_Variable::sync_stock_status($post_id);
                }
            }
            return $result;
        }

    }

}
