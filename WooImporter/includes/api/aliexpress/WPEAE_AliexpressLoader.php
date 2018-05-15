<?php

/**
 * Description of WPEAE_AliexpressLoader
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_AliexpressLoader')):

    class WPEAE_AliexpressLoader extends WPEAE_AbstractLoader {

        static $request_cookies = '';

        static function getRequestCookies() {

            if (!empty(WPEAE_AliexpressLoader::$request_cookies))
                return WPEAE_AliexpressLoader::$request_cookies;

            $initial_cookies = array();

            $cookie = new WPEAE_Http_Cookie('xman_us_f');
            $cookie->name = 'xman_us_f';

            $cookie->value = apply_filters('wpeae_get_localized_cookies', 'x_l=0&x_locale=en_US', array('type' => 'aliexpress_' . $cookie->name));

            $cookie->expires = mktime(0, 0, 0, date('m'), date('d') + 7, date('Y')); // expires in 7 days
            $cookie->path = '/';
            $cookie->domain = '.aliexpress.com';
            $cookie->set_flags(array('host-only' => false,));

            $initial_cookies[] = $cookie;

            $cookie2 = new WPEAE_Http_Cookie('aep_usuc_f');
            $cookie2->name = 'aep_usuc_f';

            $cookie2->value = apply_filters('wpeae_get_localized_cookies', 'b_locale=en_US&site=glo', array('type' => 'aliexpress_' . $cookie2->name));

            $cookie2->expires = mktime(0, 0, 0, date('m'), date('d') + 7, date('Y')); // expires in 7 days
            $cookie2->path = '/';
            $cookie2->domain = '.aliexpress.com';
            $cookie2->set_flags(array('host-only' => false,));

            $initial_cookies[] = $cookie2;

            WPEAE_AliexpressLoader::$request_cookies = $initial_cookies;


            return WPEAE_AliexpressLoader::$request_cookies;
        }

        public function load_list($filter, $page = 1) {
          
            $per_page = get_option('wpeae_ali_per_page', 20);
            $result = array("total" => 0, "per_page" => $per_page, "items" => array(), "error" => "");

            $link_category_id = (isset($filter['link_category_id']) && IntVal($filter['link_category_id'])) ? IntVal($filter['link_category_id']) : 0;

            //if(!$link_category_id){
            //    $result["error"] = 'Please select some woocomerce category!';
            //    return $result;
            //}

            if ($link_category_id && ((isset($filter['wpeae_productId']) && !empty($filter['wpeae_productId'])) || (isset($filter['wpeae_query']) && !empty($filter['wpeae_query'])) || (isset($filter['category_id']) && $filter['category_id'] != 0))) {
                $single_product_id = (isset($filter['wpeae_productId']) && $filter['wpeae_productId']) ? $filter['wpeae_productId'] : "";

                $query = (isset($filter['wpeae_query'])) ? urlencode(utf8_encode($filter['wpeae_query'])) : "";
                $category_id = (isset($filter['category_id']) && $filter['category_id']) ? $filter['category_id'] : "";
                $link_category_id = (isset($filter['link_category_id']) && IntVal($filter['link_category_id'])) ? IntVal($filter['link_category_id']) : 0;

                $priceFrom = (isset($filter['wpeae_min_price']) && !empty($filter['wpeae_min_price']) && floatval($filter['wpeae_min_price']) > 0.009) ? "&originalPriceFrom={$filter['wpeae_min_price']}" : '';
                $priceTo = (isset($filter['wpeae_max_price']) && !empty($filter['wpeae_max_price']) && floatval($filter['wpeae_max_price']) > 0.009) ? "&originalPriceTo={$filter['wpeae_max_price']}" : '';

                $commissionRateFrom = (isset($filter['commission_rate_from']) && !empty($filter['commission_rate_from']) && floatval($filter['commission_rate_from']) > 0.009) ? "&commissionRateFrom={$filter['commission_rate_from']}" : '';
                $commissionRateTo = (isset($filter['commission_rate_to']) && !empty($filter['commission_rate_to']) && floatval($filter['commission_rate_to']) > 0.009) ? "&commissionRateTo={$filter['commission_rate_to']}" : '';

                $volumeFrom = (isset($filter['volume_from']) && !empty($filter['volume_from']) && intval($filter['volume_from']) > 0) ? "&volumeFrom={$filter['volume_from']}" : '';
                $volumeTo = (isset($filter['volume_to']) && !empty($filter['volume_to']) && intval($filter['volume_to']) > 0) ? "&volumeTo={$filter['volume_to']}" : '';

                $highQualityItems = isset($filter['high_quality_items']) ? "&highQualityItems=true" : '';

                $feedback_min = (isset($filter['min_feedback']) && intval($filter['min_feedback']) > 0) ? intval($filter['min_feedback']) : 0;
                $feedback_max = (isset($filter['max_feedback']) && intval($filter['max_feedback']) > 0) ? intval($filter['max_feedback']) : 0;
                if ($feedback_max < $feedback_min) {
                    $feedback_max = 0;
                }

                $startCredit = ($feedback_min) ? "&startCreditScore={$feedback_min}" : '';
                $endCredit = ($feedback_max) ? "&endCreditScore={$feedback_max}" : '';

                $localCurrency = strtoupper(get_option('wpeae_ali_local_currency', ''));
                if ($localCurrency) {
                    $localCurrencyReq = "&localCurrency=$localCurrency";
                    $currency_conversion_factor = 1;
                } else {
                    $localCurrencyReq = "";
                    $currency_conversion_factor = floatval(get_option('wpeae_currency_conversion_factor', 1));
                }



                // NOT USED in AliExpress
                // $available_to = (isset($filter['available_to']) && $filter['available_to']) ? $filter['available_to'] : "";

                $request_sort = '';
                if (isset($filter['orderby'])) {
                    $request_sort = '&sort=';
                    switch ($filter['orderby']) {
                        case 'price':
                            if ($filter['order'] == 'asc') {
                                $request_sort .= "orignalPriceUp";
                            } elseif ($filter['order'] == 'desc') {
                                $request_sort .= "orignalPriceDown";
                            }

                            break;
                        case 'validTime':
                            if ($filter['order'] == 'asc') {
                                $request_sort .= "validTimeUp";
                            } elseif ($filter['order'] == 'desc') {
                                $request_sort .= "validTimeDown";
                            }
                            break;
                        default:
                            $request_sort = '';
                    }
                }
                // <---------------------------

                if ($single_product_id) {
                    // search by product id
                    $request_url = "http://gw.api.alibaba.com/openapi/param2/2/portals.open/api.getPromotionProductDetail/{$this->account->appKey}";
                    $request_param = "?fields=productId,productTitle,productUrl,imageUrl,originalPrice,salePrice,discount,evaluateScore,commission,commissionRate,30daysCommission,volume,packageType,lotNum,validTime,storeName,storeUrl,localPrice,allImageUrls";
                    $request_param .= "&productId=$single_product_id";
                    $request_param .= $localCurrencyReq;
                    $request_sort = "";
                } else {
                    // search by query and params
                    $request_url = "http://gw.api.alibaba.com/openapi/param2/2/portals.open/api.listPromotionProduct/{$this->account->appKey}";
                    $request_param = "?fields=totalResults,productId,productTitle,productUrl,imageUrl,originalPrice,salePrice,discount,evaluateScore,commission,commissionRate,30daysCommission,volume,packageType,lotNum,validTime,localPrice,allImageUrls";
                    $request_param .= "&categoryId={$category_id}&pageNo={$page}&keywords={$query}&pageSize={$per_page}";
                    $request_param .= $localCurrencyReq . $commissionRateFrom . $commissionRateTo . $volumeFrom . $volumeTo . $priceFrom . $priceTo . $startCredit . $endCredit . $highQualityItems;
                }

                $full_request_url = apply_filters('wpeae_get_localized_url', $request_url . $request_param . $request_sort, array('type' => 'aliexpress_request'));

                $request = wpeae_remote_get($full_request_url);
                //echo $full_request_url."<br/>";
                //echo "<pre>";print_r($request);echo "</pre>";
                //$result["call"] = $request_url . $request_param . $request_sort;

                $error_code = '';

                if (is_wp_error($request)) {
                    $result["error"] = 'alibaba.com not response! ' . $request->get_error_message();
                } else {
                    $items = json_decode($request['body'], true);
                    $error_code = (isset($items['errorCode'])) ? $items['errorCode'] : '';
                    //echo "<pre>";print_r($request);echo "</pre>";
                }

                if ($single_product_id && isset($items['result']) && $items['result']) {
                    $items['result'] = array('products' => array($items['result']));
                }

                //echo "<pre>";print_r($items);echo "</pre>";

                if ($error_code == 20010000 && isset($items['result']['products']) && !empty($items['result']['products'])) {
                    $data = $items['result']['products'];
                    $wpeae_ali_https_image_url = get_option('wpeae_ali_https_image_url', false);
                    foreach ($data as $item) {
                        //echo "<pre>";print_r($item);echo "</pre>";

                        $goods = new WPEAE_Goods();
                        $goods->type = "aliexpress";
                        $goods->external_id = $item["productId"];
                        $goods->load();

                        $goods->link_category_id = $link_category_id;

                        if ($wpeae_ali_https_image_url) {
                            $goods->image = (isset($item["imageUrl"])) ? $this->image_http_to_https($item["imageUrl"]) : WPEAE_NO_IMAGE_URL;
                        } else {
                            $goods->image = (isset($item["imageUrl"])) ? $this->image_https_to_http($item["imageUrl"]) : WPEAE_NO_IMAGE_URL;
                        }


                        if (isset($item['allImageUrls'])) {
                            $photos = explode(",", $item['allImageUrls']);
                            foreach ($photos as $k => $p) {
                                if ($wpeae_ali_https_image_url) {
                                    $photos[$k] = $this->image_http_to_https($p);
                                } else {
                                    $photos[$k] = $this->image_https_to_http($p);
                                }
                            }
                            $goods->photos = implode(",", $photos);
                        }



                        $goods->detail_url = $item["productUrl"];
                        $goods->additional_meta['detail_url'] = $item["productUrl"];

                        $goods->title = strip_tags($item["productTitle"]);
                        $goods->subtitle = "#notuse#";
                        $goods->additional_meta['validTime'] = $item["validTime"];
                        $goods->category_id = 0;



                        if (strlen(trim($goods->category_name)) == 0) {
                            $goods->category_name = "#needload#";
                        }

                        if (strlen(trim($goods->keywords)) == 0) {
                            $goods->keywords = "#needload#";
                        }

                        if (strlen(trim($goods->description)) == 0) {
                            $goods->description = "#needload#";
                        }

                        $goods->additional_meta['discount'] = $item["discount"];

                        //    $goods->additional_meta['condition'] = "New";

                        $local_price = $localCurrency ? WPEAE_Goods::get_normalize_price($item['localPrice']) : WPEAE_Goods::get_normalize_price($item['salePrice']);
                        $sale_price = WPEAE_Goods::get_normalize_price($item['salePrice']);
                        $usd_course = round($local_price / $sale_price, 2);

                        $goods->price = round($local_price, 2);

                        $originalPrice = WPEAE_Goods::get_normalize_price($item['originalPrice']);
                        $goods->regular_price = round($originalPrice * $usd_course, 2);

                        $goods->additional_meta['original_discount'] = 100 - round($sale_price * 100 / $originalPrice);

                        //course
                        //$goods->additional_meta['ship'] = '8%';
                        $commission_rate = 8;
                        $goods->additional_meta['commission'] = round($local_price * ($commission_rate / 100), 2);

                        $goods->additional_meta['volume'] = $item['volume'];
                        $goods->additional_meta['rating'] = $item['evaluateScore'];

                        /* this is for one addon -----> */
                        $goods->additional_meta['regular_price'] = round(WPEAE_Goods::get_normalize_price($item['originalPrice']) * $currency_conversion_factor, 2);
                        $goods->additional_meta['sale_price'] = round(WPEAE_Goods::get_normalize_price($item['salePrice']) * $currency_conversion_factor, 2);
                        /* <--------------------------- */
                        if ($localCurrency) {
                            $goods->curr = strtoupper($localCurrency);
                        } else {
                            $goods->curr = $currency_conversion_factor > 1 ? "CUSTOM (*$currency_conversion_factor)" : "USD (Default)";
                        }

                        $goods->save("API");

                        //if (strlen(trim((string) $goods->user_price)) == 0) {
                        $goods->user_price = round($goods->price * $currency_conversion_factor, 2);
                        $goods->save_field("user_price", sprintf("%01.2f", $goods->user_price));

                        $goods->user_regular_price = round($goods->regular_price * $currency_conversion_factor, 2);
                        $goods->save_field("user_regular_price", sprintf("%01.2f", $goods->user_regular_price));
                        //}

                        if (strlen(trim((string) $goods->user_image)) == 0) {
                            $goods->save_field("user_image", $goods->image);
                        }
                        $result["items"][] = apply_filters('wpeae_modify_goods_data', $goods, $item, "aliexpress_load_list");
                    }

                    //if (get_option('wpeae_default_type', 'simple')=="external") {
                    $result["items"] = $this->get_affiliate_goods($result["items"]);

                    if (isset($result["items"]["errorCode"])) {
                        if ($result["items"]["errorCode"] == 20030060)
                            $result["error"] = 'Tracking ID input parameter error. Please input correct Tracking ID in the WooImporter Settings.';

                        unset($result["items"]["errorCode"]);
                    }
                    //}

                    if (isset($items['result']['totalResults']))
                        $result["total"] = IntVal($items['result']['totalResults']) > 10240 ? 10240 : $items['result']['totalResults'];
                }if ($error_code == 20010000 && empty($items['result']['products'])) {
                    $result["error"] = 'There is no product to display!';
                } elseif ($error_code == 400) {
                    $result["error"] = $items['error_message'];
                } elseif ($error_code == 20030000) {
                    $result["error"] = 'Required parameters';
                } elseif ($error_code == 20030010) {
                    $result["error"] = 'Keyword input parameter error';
                } elseif ($error_code == 20030020) {
                    $result["error"] = 'Category ID input parameter error or formatting errors';
                } elseif ($error_code == 20030030) {
                    $result["error"] = 'Commission rate input parameter error or formatting errors';
                } elseif ($error_code == 20030040) {
                    $result["error"] = 'Unit input parameter error or formatting errors';
                } elseif ($error_code == 20030050) {
                    $result["error"] = '30 days promotion amount input parameter error or formatting errors';
                } elseif ($error_code == 20030060) {
                    $result["error"] = 'Tracking ID input parameter error or limited length';
                } elseif ($error_code == 20030070) {
                    $result["error"] = 'Unauthorized transfer request';
                } elseif ($error_code == 20020000) {
                    $result["error"] = 'System Error';
                } elseif ($error_code == 20030100) {
                    $result["error"] = 'Error! Input parameter Product ID';
                }
            } else {
                if ((isset($filter['wpeae_productId']) && !empty($filter['wpeae_productId'])) || (isset($filter['wpeae_query']) && !empty($filter['wpeae_query'])) || (isset($filter['category_id']) && $filter['category_id'] != 0)) {
                    $result["error"] = 'Please set "Link to category" field before searching';
                } else {
                    $result["error"] = 'Please enter some search keywords or select item from category list!';
                }
            }



            return $result;
        }

        public function load_detail(/* @var $goods WPEAE_Goods */ $goods, $params = array()) {

            $tmp_res = $this->get_detail($goods->external_id);

            if ($tmp_res['state'] !== 'ok') {
                return array('state' => 'error', 'message' => $tmp_res['message']);
            }

            $tmp_goods = $tmp_res['goods'];

            $data = $this->tmp_ali_product_info($goods);
            if ($data['state'] !== 'ok') {
                return array('state' => 'error', 'message' => $data['message']);
            }

            $goods->image = $tmp_goods->image;
            $goods->photos = $tmp_goods->photos;

            // IMPORTANT! For some reason use descriptoin form mobile version first!
            //$goods->description = get_option('wpeae_ali_import_description', true) ? ($data['m_description'] ? $data['m_description'] : $data['description']) : '';
            // IMPORTANT! Switched to a descriptoin description because coockies were added to a request
            //$goods->description = get_option('wpeae_ali_import_description', true) ? ($data['description'] ? $data['description'] : $data['m_description']) : '';
            $goods->description = get_option('wpeae_ali_import_description', true) && isset($data['description']) ? $data['description'] : '';

            //$goods->description = apply_filters('wpeae_get_localized_text', $goods->description, 'aliexpress');

            $goods->keywords = $data['keywords'];

            $goods->category_id = 0;
            $goods->category_name = "";

            if (is_array($data["attribute"])) {
                $data["attribute"] = apply_filters('wpeae_get_localized_attributes', $data["attribute"], 'aliexpress');
                /*
                  foreach ($data["attribute"] as $attr_key => $attr_val){
                  $data["attribute"][$attr_key] = apply_filters('wpeae_get_localized_text',                                   $attr_val, 'aliexpress');
                  } */
            }

            $goods->additional_meta['attribute'] = $data["attribute"];

            $goods->seller_url = $tmp_goods->seller_url;

            //$goods->photos = implode(",", $data['images']);
            /* try {
              $images = $this->get_images($tmp_goods);
              $goods->photos = implode(",", $images);
              } catch (Exception $e) {

              } */

            $goods->save("API");

            return array("state" => "ok", "message" => "", "goods" => $goods);

            /* OLD VERSION with api_1.0
              $request_url = "http://gw.api.alibaba.com/openapi/param2/1/portals.open/api.getPromotionProductDetail/{$this->account->appKey}?trackingId={$this->account->trackingId}";
              $request_param = "&productId=$goods->external_id";
              $request_param .= "&fields=productId,discount,productTitle,imageUrl,attribute,salePrice";

              $request = wpeae_remote_get($request_url . $request_param);
              if (is_wp_error($request)) {
              return array('state' => 'error', 'message' => 'alibaba.com not response!');
              }

              //echo $request_url . $request_param."<br/>";
              //echo "<pre>";print_r($request);echo "</pre>";
              $data = json_decode($request['body'], true);

              // DEBUG
              //$data['result']['description'] = "#debug_hide#";
              //echo "<pre>";print_r($data);echo "</pre>";

              if (isset($data['errorCode']) && $data['errorCode'] == 20010000 && $data['result']['productId'] == $goods->external_id) {
              $goods->image = $data['result']['imageUrl'];
              $goods->description = $data['result']['description'];
              //$goods->description = preg_replace("/<script.*?\/script>/s", "", $goods->description);
              //$goods->description = preg_replace("/<SCRIPT.*?\/SCRIPT>/s", "", $goods->description);
              //$goods->description = preg_replace("/<noscript.*?\/noscript>/s", "", $goods->description);
              //$goods->description = preg_replace("/<NOSCRIPT.*?\/NOSCRIPT>/s", "", $goods->description);

              $goods->description = WPEAE_Utils::remove_tags($goods->description);
              if (get_option('wpeae_ali_links_to_affiliate', false)) {
              $goods->description = $this->links_to_affiliate($goods->description);
              }

              $tmp_p = "";
              foreach ($data['result']['subImageUrl'] as $img_url) {
              $tmp_p .= ($tmp_p ? "," : "") . $img_url;
              }
              $goods->photos = $tmp_p;

              $goods->keywords = (isset($data['result']['keywords']) && $data['result']['keywords']) ? $data['result']['keywords'] : "#empty#";

              $goods->category_id = $data['result']['categoryId'];
              $goods->category_name = $data['result']['categoryName'];

              $goods->additional_meta['attribute'] = (isset($data['result']['attribute']) && is_array($data['result']['attribute']) && $data['result']['attribute']) ? $data['result']['attribute'] : array();

              $goods->seller_url = $this->load_detail_store($goods->external_id);

              $goods->save("API");
              //echo "<pre>";print_r($goods);echo "</pre>";
              return array("state" => "ok", "message" => "", "goods" => $goods);
              } elseif (isset($data['errorCode']) && $data['errorCode'] == 20010000 && $data['result']['productId'] != $goods->external_id) {
              return array('state' => 'error', 'message' => 'System Error');
              } elseif (isset($data['errorCode']) && ($data['errorCode'] == 20130000 || $data['errorCode'] == 20030100)) {
              return array('state' => 'error', 'message' => 'Input parameter Product ID is error');
              } elseif (isset($data['error_code']) && $data['error_code'] == 400) {
              return array('state' => 'error', 'message' => "{$data['error_message']}");
              } elseif (isset($data['errorCode']) && $data['errorCode'] == 20030000) {
              return array('state' => 'error', 'message' => 'Required parameters');
              } elseif (isset($data['errorCode']) && $data['errorCode'] == 20030010) {
              return array('state' => 'error', 'message' => 'Keyword input parameter error');
              } elseif (isset($data['errorCode']) && $data['errorCode'] == 20030020) {
              return array('state' => 'error', 'message' => 'Category ID input parameter error or formatting errors');
              } elseif (isset($data['errorCode']) && $data['errorCode'] == 20030030) {
              return array('state' => 'error', 'message' => 'Commission rate input parameter error or formatting errors');
              } elseif (isset($data['errorCode']) && $data['errorCode'] == 20030040) {
              return array('state' => 'error', 'message' => 'Unit input parameter error or formatting errors');
              } elseif (isset($data['errorCode']) && $data['errorCode'] == 20030050) {
              return array('state' => 'error', 'message' => '30 days promotion amount input parameter error or formatting errors');
              } elseif (isset($data['errorCode']) && $data['errorCode'] == 20030060) {
              return array('state' => 'error', 'message' => 'Tracking ID input parameter error or limited length');
              } elseif (isset($data['errorCode']) && $data['errorCode'] == 20030070) {
              return array('state' => 'error', 'message' => 'Unauthorized transfer request');
              } elseif (isset($data['errorCode']) && $data['errorCode'] == 20020000) {
              return array('state' => 'error', 'message' => 'System Error');
              } else {
              return array('state' => 'error', 'message' => 'Aliexpress API Error');
              }

             */
        }

        public function get_detail($productId, $params = array()) {
            $localCurrency = strtoupper(get_option('wpeae_ali_local_currency', ''));
            if ($localCurrency) {
                $currency_conversion_factor = 1;
            } else {
                $currency_conversion_factor = floatval(get_option('wpeae_currency_conversion_factor', 1));
            }

            $request_url = "http://gw.api.alibaba.com/openapi/param2/2/portals.open/api.getPromotionProductDetail/{$this->account->appKey}";
            $request_url .= "?fields=productId,productTitle,productUrl,imageUrl,originalPrice,salePrice,discount,evaluateScore,commission,commissionRate,30daysCommission,volume,packageType,lotNum,validTime,storeName,storeUrl,localPrice,allImageUrls";
            $request_url .= "&productId=$productId";
            if ($localCurrency) {
                $request_url .= "&localCurrency=$localCurrency";
            }

            $full_request_url = apply_filters('wpeae_get_localized_url', $request_url, array('type' => 'aliexpress_request'));

            $request = wpeae_remote_get($full_request_url);

            if (is_wp_error($request)) {
                return array('state' => 'error', 'message' => 'alibaba.com not response!');
            }
            //DEBUG

            $data = json_decode($request['body'], true);

            //$data['result']['description'] = "#debug_hide#";
            //echo $full_request_url."<br/>";
            //echo "<pre>";print_r($data);echo "</pre>";

            if (isset($data['errorCode']) && $data['errorCode'] == 20010000) {
                if (isset($data['result']['productId']) && $data['result']['productId'] == $productId) {
                    $goods = new WPEAE_Goods("aliexpress#" . $productId);

                    $local_price = $localCurrency ? WPEAE_Goods::get_normalize_price($data['result']['localPrice']) : WPEAE_Goods::get_normalize_price($data['result']['salePrice']);
                    $sale_price = WPEAE_Goods::get_normalize_price($data['result']['salePrice']);
                    $usd_course = round($local_price / $sale_price, 2);

                    $goods->price = round($local_price, 2);

                    $originalPrice = WPEAE_Goods::get_normalize_price($data['result']['originalPrice']);
                    $goods->regular_price = round($originalPrice * $usd_course, 2);

                    $goods->user_price = round($goods->price * $currency_conversion_factor, 2);
                    $goods->user_regular_price = round($goods->regular_price * $currency_conversion_factor, 2);

                    $commission_rate = 8;
                    $goods->additional_meta['commission'] = round($local_price * ($commission_rate / 100), 2);

                    $goods->additional_meta['regular_price'] = round(WPEAE_Goods::get_normalize_price($data['result']['originalPrice']) * $currency_conversion_factor, 2);
                    $goods->additional_meta['sale_price'] = round(WPEAE_Goods::get_normalize_price($data['result']['salePrice']) * $currency_conversion_factor, 2);

                    $goods->detail_url = strval($data['result']["productUrl"]);
                    $goods->additional_meta['detail_url'] = strval($data['result']["productUrl"]);

                    // build affiliate url
                    $promotionUrls = $this->get_affiliate_urls($goods->detail_url);
                    if($promotionUrls && is_array($promotionUrls)){
                        foreach ($promotionUrls as $pu) {
                            if ($pu['url'] === $goods->detail_url) {
                                $goods->detail_url = $pu['promotionUrl'];
                                break;
                            }
                        }
                    }else{
                        error_log($promotionUrls);
                    }
                    

                    $goods->seller_url = isset($data['result']["storeUrl"]) ? $data['result']["storeUrl"] : "";

                    $wpeae_ali_https_image_url = get_option('wpeae_ali_https_image_url', false);

                    if ($wpeae_ali_https_image_url) {
                        $goods->image = (isset($data['result']['imageUrl'])) ? $this->image_http_to_https($data['result']['imageUrl']) : WPEAE_NO_IMAGE_URL;
                    } else {
                        $goods->image = (isset($data['result']['imageUrl'])) ? $this->image_https_to_http($data['result']['imageUrl']) : WPEAE_NO_IMAGE_URL;
                    }

                    if (isset($data['result']['allImageUrls'])) {
                        $photos = explode(",", $data['result']['allImageUrls']);
                        foreach ($photos as $k => $p) {
                            if ($wpeae_ali_https_image_url) {
                                $photos[$k] = $this->image_http_to_https($p);
                            } else {
                                $photos[$k] = $this->image_https_to_http($p);
                            }
                        }
                        $goods->photos = implode(",", $photos);
                    }

                    return array("state" => "ok", "message" => "", "goods" => apply_filters('wpeae_modify_goods_data', $goods, $data, "aliexpress_get_detail"));
                } else {
                    $goods = new WPEAE_Goods();
                    $goods->availability = false;
                    return array("state" => "ok", "message" => "", "goods" => $goods);
                }
            } elseif (isset($data['errorCode']) && $data['errorCode'] == 20010000 && isset($data['result']['productId']) && $data['result']['productId'] != $goods->external_id) {
                return array('state' => 'error', 'message' => 'System Error');
            } elseif (isset($data['errorCode']) && ($data['errorCode'] == 20130000 || $data['errorCode'] == 20030100)) {
                return array('state' => 'error', 'message' => 'Input parameter Product ID is error');
            } elseif (isset($data['error_code']) && $data['error_code'] == 400) {
                return array('state' => 'error', 'message' => "{$data['error_message']}");
            } elseif (isset($data['errorCode']) && $data['errorCode'] == 20030000) {
                return array('state' => 'error', 'message' => 'Required parameters');
            } elseif (isset($data['errorCode']) && $data['errorCode'] == 20030010) {
                return array('state' => 'error', 'message' => 'Keyword input parameter error');
            } elseif (isset($data['errorCode']) && $data['errorCode'] == 20030020) {
                return array('state' => 'error', 'message' => 'Category ID input parameter error or formatting errors');
            } elseif (isset($data['errorCode']) && $data['errorCode'] == 20030030) {
                return array('state' => 'error', 'message' => 'Commission rate input parameter error or formatting errors');
            } elseif (isset($data['errorCode']) && $data['errorCode'] == 20030040) {
                return array('state' => 'error', 'message' => 'Unit input parameter error or formatting errors');
            } elseif (isset($data['errorCode']) && $data['errorCode'] == 20030050) {
                return array('state' => 'error', 'message' => '30 days promotion amount input parameter error or formatting errors');
            } elseif (isset($data['errorCode']) && $data['errorCode'] == 20030060) {
                return array('state' => 'error', 'message' => 'Tracking ID input parameter error or limited length');
            } elseif (isset($data['errorCode']) && $data['errorCode'] == 20030070) {
                return array('state' => 'error', 'message' => 'Unauthorized transfer request');
            } elseif (isset($data['errorCode']) && $data['errorCode'] == 20020000) {
                return array('state' => 'error', 'message' => 'System Error');
            } else {
                return array('state' => 'error', 'message' => 'Unknown Error');
            }
        }

        private function get_affiliate_urls($urls) {
            $urls_str = "";
            if (is_array($urls)) {
                foreach ($urls as $url) {
                    $urls_str.=($urls ? "," : "") . $url;
                }
            } else {
                $urls_str = strval($urls);
            }

            $request_url = "http://gw.api.alibaba.com/openapi/param2/2/portals.open/api.getPromotionLinks/{$this->account->appKey}?fields=&trackingId={$this->account->trackingId}&urls={$urls_str}";

            $request = wpeae_remote_get($request_url);
            if (!is_wp_error($request)) {
                $data = json_decode($request['body'], true);
                if (isset($data['errorCode']) && $data['errorCode'] == 20010000) {
                    return $data['result']['promotionUrls'];
                } else {
                    return "get_affiliate_goods: error " . $data['errorCode'];
                }
            } else {
                return "get_affiliate_goods: error (" . $request->get_error_code() . ") " . $request->get_error_message($request->get_error_code());
            }
        }

        private function get_affiliate_goods($goods_list) {
            $result = $goods_list;


            $urls = "";
            foreach ($result as $goods) {
                $urls.=($urls ? "," : "") . $goods->detail_url;
            }

            $promotionUrls = $this->get_affiliate_urls($urls);

            if ($promotionUrls && is_array($promotionUrls)) {
                foreach ($result as $key => $goods) {
                    $new_promo_url = "";
                    foreach ($promotionUrls as $pu) {
                        if ($pu['url'] == $result[$key]->detail_url) {
                            $new_promo_url = $pu['promotionUrl'];
                            break;
                        }
                    }
                    if ($new_promo_url) {
                        $result[$key]->detail_url = $new_promo_url;
                        $result[$key]->save("API");
                    }
                }
            } else {
                $result['errorCode'] = $promotionUrls;
            }

            return $result;
        }

        private function load_detail_store($id) {
            $request_url = "http://gw.api.alibaba.com/openapi/param2/2/portals.open/api.getPromotionProductDetail/{$this->account->appKey}?trackingId={$this->account->trackingId}&fields=storeUrl&productId={$id}";

            $full_request_url = apply_filters('wpeae_get_localized_url', $request_url, array('type' => 'aliexpress_request'));

            $request = wpeae_remote_get($full_request_url);
            if (is_wp_error($request)) {
                return;
            }

            $data = json_decode($request['body'], true);

            if (isset($data['result']['storeUrl'])) {
                return $data['result']['storeUrl'];
            } else {
                return "";
            }
        }

        public function check_availability(/* @var $goods WPEAE_Goods */ $goods) {
            $request_url = "http://gw.api.alibaba.com/openapi/param2/2/portals.open/api.getPromotionProductDetail/{$this->account->appKey}";
            $request_url .= "?fields=productId,productTitle,productUrl,imageUrl,originalPrice,salePrice,discount,evaluateScore,commission,commissionRate,30daysCommission,volume,packageType,lotNum,validTime,storeName,storeUrl";
            $request_url .= "&productId=$goods->external_id";

            /* $request_url = "http://gw.api.alibaba.com/openapi/param2/1/portals.open/";
              $request_api = "api.getPromotionProductDetail/{$this->account->appKey}?trackingId={$this->account->trackingId}";
              $request_param = "&productId=$goods->external_id"; */

            $full_request_url = apply_filters('wpeae_get_localized_url', $request_url, array('type' => 'aliexpress_request'));

            $request = wpeae_remote_get($full_request_url);

            if (is_wp_error($request)) {
                return array('state' => 'error', 'message' => 'alibaba.com not response!');
            }

            $data = json_decode($request['body'], true);
            //echo $request_url . $request_api . $request_param."<br/>";
            //$data['result']['description'] = "#debug_hide#";
            //echo "<pre>";print_r($data);echo "</pre>";

            if ($data['errorCode'] == 20010000 && $data['result']['productId'] == $goods->external_id && isset($data['result']['availability'])) {
                return $data['result']['availability'] ? true : false;
            } else if ($data['errorCode'] == 20010000 && !isset($data['result']['productId'])) {
                return false;
            }

            return true;
        }

        private function links_to_affiliate($content) {
            $hrefs = array();
            $dom = new DOMDocument();
            @$dom->loadHTML($content);
            $dom->formatOutput = true;
            $tags = $dom->getElementsByTagName('a');
            foreach ($tags as $tag) {
                $hrefs[] = $tag->getAttribute('href');
            }

            $request_url = "http://gw.api.alibaba.com/openapi/param2/2/portals.open/api.getPromotionLinks/{$this->account->appKey}?trackingId={$this->account->trackingId}&fields=promotionUrl";
            $request_url .= "&urls=" . implode(',', $hrefs);

            $request = wp_remote_get($request_url);
            if (!is_wp_error($request)) {
                $body = json_decode($request['body'], true);
                if ($body != '' && isset($body['result'])) {
                    foreach ($body['result']['promotionUrls'] as $link) {
                        $content = str_replace($link['url'], $link['promotionUrl'], $content);
                    }
                }
            }
            return $content;
        }

        public function tmp_ali_product_info($goods) {

            $result = array("state" => "ok");
            if ($goods->external_id) {
                $result["description"] = "";
                $result["description_images"] = array();
                $result["m_description"] = "";
                $result["images"] = array();
                $result["attribute"] = array();
                $result["keywords"] = "#empty#";
                $result["quantity"] = "";

                //aliexpress_desc2

                /*
                  https://www.aliexpress.com/getDescModuleAjax.htm?productId=32736444953
                  $request_url = "http://desc.aliexpress.com/getDescModuleAjax.htm?productId=" . $goods->external_id . "&t=";
                  $desc_url = apply_filters('wpeae_get_localized_url', $request_url, array('type' => 'aliexpress_desc', 'external_id' => $goods->external_id));
                 */
                $request_url = "https://www.aliexpress.com/getDescModuleAjax.htm?productId=" . $goods->external_id . "&t=";
                $desc_url = apply_filters('wpeae_get_localized_url', $request_url, array('type' => 'aliexpress_desc2', 'external_id' => $goods->external_id));
                $desc_content = wpeae_remote_get($desc_url, array('cookies' => $this->getRequestCookies()));

                //$desc_content = wp_remote_get( "http://en.aliexpress.com/getSubsiteDescModuleAjax.htm?productId=" . $this->id );
                if (!is_wp_error($desc_content)) {
                    $desc_content = str_replace(array("window.productDescription='", "';"), '', $desc_content['body']);
                    if (function_exists('mb_convert_encoding')) {
                        $desc_content = trim(mb_convert_encoding($desc_content, 'HTML-ENTITIES', 'UTF-8'));
                    } else {
                        $desc_content = htmlspecialchars_decode(utf8_decode(htmlentities($desc_content, ENT_COMPAT, 'UTF-8', false)));
                    }

                    $desc_content = WPEAE_Utils::remove_tags($desc_content);
                    if (get_option('wpeae_ali_links_to_affiliate', false)) {
                        $desc_content = $this->links_to_affiliate($desc_content);
                    }
                    $desc_content = $this->clear_html($desc_content);

                    $result["description"] = $desc_content;
                }


                $request_url = 'http://m.aliexpress.com/item-desc/' . $goods->external_id . '.html?site=en';
                $request_url = apply_filters('wpeae_get_localized_url', $request_url, array('type' => 'aliexpress_desc', 'external_id' => $goods->external_id));

                $response = wp_remote_get($request_url);
                if (!is_wp_error($response)) {
                    $html_str = $response['body'];
                    if (function_exists('mb_convert_encoding')) {
                        $html_str = trim(mb_convert_encoding($html_str, 'HTML-ENTITIES', 'UTF-8'));
                    } else {
                        $html_str = htmlspecialchars_decode(utf8_decode(htmlentities($html_str, ENT_COMPAT, 'UTF-8', false)));
                    }

                    $dom = new DOMDocument();
                    libxml_use_internal_errors(true);
                    $dom->loadHTML($html_str);
                    libxml_use_internal_errors(false);
                    $finder = new DOMXPath($dom);

                    //get attributes
                    $rows = $finder->query("//*[contains(@class, 'prop-table')]/tbody/tr");
                    foreach ($rows as $row) {
                        $key = $value = "";
                        foreach ($row->childNodes as $td) {
                            if (XML_ELEMENT_NODE == $td->nodeType) {
                                if ("key" === $td->getAttribute('class')) {
                                    $key = $td->nodeValue;
                                } else if ("value" === $td->getAttribute('class')) {
                                    $value = $td->nodeValue;
                                }
                            }
                        }
                        if ($value && $value !== "NA" && $value !== "None") {
                            $result["attribute"][] = array("name" => $key, "value" => $value);
                        }
                    }

                    $items = $finder->query('//div[@style="max-width: 650.0px;overflow: hidden;font-size: 0;clear: both;"]');
                    foreach ($items as $item) {
                        $item->parentNode->removeChild($item);
                    }

                    $mdesc = $finder->query("//section[contains(@class, 'descriptions')]");
                    if ($mdesc->length > 0) {
                        //$html_str = str_replace(chr(194)," ",$html_str);
                        //$html_str = str_replace('Â','',$html_str);
                        $tmp_desc = $mdesc->item(0)->C14N();

                        $tmp_desc = WPEAE_Utils::remove_tags($tmp_desc);
                        if (get_option('wpeae_ali_links_to_affiliate', false)) {
                            $tmp_desc = $this->links_to_affiliate($tmp_desc);
                        }

                        $tmp_desc = $this->clear_html($tmp_desc);
                        $result["m_description"] = $tmp_desc;
                    }
                }

                //$url  = "http://" . $lang . ".aliexpress.com/item//" . $goods->external_id . ".html";
                //$url = isset($goods->additional_meta['detail_url'])?$goods->additional_meta['detail_url']:'';
                //$tmp_desc = $html->find('section.descriptions', 0);
                //$result["description"] = $tmp_desc?$tmp_desc->innertext:"";

                /* $images = array();

                  $html = str_get_html(file_get_contents('http://m.aliexpress.com/item/' . $goods->external_id . '.html'));
                  $tmp_img = $html->find('ul.img-list', 0);

                  if($tmp_img){
                  $images_list = $tmp_img->find("img");
                  if($images_list){
                  foreach ($images_list as $image) {
                  $img = $image->getAttribute("img-src");
                  $img = str_replace("_640x640.jpg", "", $img);
                  $images[] = $img;
                  }
                  }
                  }

                  $result["images"] = $images;
                 */
            } else {
                $result["state"] = "error";
                $result["message"] = "Product ID is empty";
            }
            return $result;
        }

        private function clear_html($in_html) {
            if (!$in_html)
                return "";
            $html = preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $in_html);
            $html = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $html);
            $html = preg_replace('/(<[^>]+) class=".*?"/i', '$1', $html);
            $html = preg_replace('/(<[^>]+) width=".*?"/i', '$1', $html);
            $html = preg_replace('/(<[^>]+) height=".*?"/i', '$1', $html);
            $html = preg_replace('/(<[^>]+) alt=".*?"/i', '$1', $html);
            $html = preg_replace('/^<!DOCTYPE.+?>/', '$1', str_replace(array('<html>', '</html>', '<body>', '</body>'), '', $html));
            $html = preg_replace("/<\/?div[^>]*\>/i", "", $html);

            $html = preg_replace('#(<a.*?>).*?(</a>)#', '$1$2', $html);
            $html = preg_replace('/<a[^>]*>(.*)<\/a>/iU', '', $html);
            $html = preg_replace("/<\/?h1[^>]*\>/i", "", $html);
            $html = preg_replace("/<\/?strong[^>]*\>/i", "", $html);
            $html = preg_replace("/<\/?span[^>]*\>/i", "", $html);

            //$html = str_replace(' &nbsp; ', '', $html);
            $html = str_replace('&nbsp;', ' ', $html);
            $html = str_replace('\t', ' ', $html);
            $html = str_replace('  ', ' ', $html);


            $html = preg_replace("/http:\/\/g(\d+)\.a\./i", "https://ae$1.", $html);

            $pattern = "/<[^\/>]*>([\s]?)*<\/[^>]*>/";
            $html = preg_replace($pattern, '', $html);

            $html = str_replace(array('<img', '<table'), array('<img class="img-responsive"', '<table class="table table-bordered'), $html);
            $html = force_balance_tags($html);

            return $html;
        }

        public function get_images($goods) {
            $images = array();

            $image_page = str_replace("/item/", "/item-img/", $goods->detail_url);
            $image_page = preg_replace("/\/\/[a-z]{2,3}\.aliexpress/i", "//www.aliexpress", $image_page);

            $response = wpeae_remote_get($image_page);
            if (!is_wp_error($response)) {
                $html_str = $response['body'];

                $dom = new DOMDocument();
                libxml_use_internal_errors(true);
                $dom->loadHTML($html_str);
                libxml_use_internal_errors(false);

                $finder = new DOMXPath($dom);
                $items = $finder->query("//*[contains(@class, 'image')]/ul/li/a/img");
                $ind = 0;

                $wpeae_ali_https_image_url = get_option('wpeae_ali_https_image_url', false);
                foreach ($items as $item) {
                    $ind++;
                    $url_info = parse_url($item->getAttribute("src"));
                    $path_info = pathinfo($url_info['path']);

                    $tmp_img_url = $url_info['scheme'] . '://' . $url_info['host'] . $path_info['dirname'] . "/" . $ind . "." . $path_info['extension'];

                    if ($wpeae_ali_https_image_url) {
                        $images[$path_info['dirname']] = $this->image_http_to_https($tmp_img_url);
                    } else {
                        $images[$path_info['dirname']] = $this->image_https_to_http($tmp_img_url);
                    }
                }
            } else {
                wpeae_write_log($response);
            }

            return $images;
        }

        public static function image_http_to_https($image_url) {
            return preg_replace("/http:\/\/g(\d+)\.a\./i", "https://ae$1.", strval($image_url));
        }

        public static function image_https_to_http($image_url) {
            return preg_replace("/https:\/\/ae(\d+)\./i", "http://g$1.a.", strval($image_url));
        }
    }

    

    

    

    

	

	

	

	

	

	

	

	

endif;