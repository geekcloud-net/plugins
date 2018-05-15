<?php

/**
 * Description of WPEAE_EbayLoader
 *
 * @author Geometrix
 */
include_once(dirname(__FILE__) . '/WPEAE_EbaySession.php');

if (!class_exists('WPEAE_EbayLoader')):

    class WPEAE_EbayLoader extends WPEAE_AbstractLoader {

        public function load_list($filter, $page = 1) {
            $per_page = get_option('wpeae_ebay_per_page', 20);
            $result = array("total" => 0, "per_page" => $per_page, "items" => array(), "error" => "");

            if ((isset($filter['wpeae_productId']) && !empty($filter['wpeae_productId'])) || ( isset($filter['wpeae_query']) && !empty($filter['wpeae_query']) ) || ( isset($filter['store']) && !empty($filter['store']) ) || (isset($filter['category_id']) && $filter['category_id'] != 0)) {
                $endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';
                $responseEncoding = 'XML';

                $productId = isset($filter['wpeae_productId']) ? $filter['wpeae_productId'] : "";

                //echo "wpeae_query: ".$filter['wpeae_query']."<br/>";
                $safeQuery = isset($filter['wpeae_query']) ? urlencode(utf8_encode($filter['wpeae_query'])) : "";
                //echo "safeQuery: ".$safeQuery."<br/>";
                
                $site = isset($filter['sitecode']) ? $filter['sitecode'] : "EBAY-US";

                $price_min = (isset($filter['wpeae_min_price']) && floatval($filter['wpeae_min_price']) > 0.009) ? floatval($filter['wpeae_min_price']) : 0;
                $price_max = (isset($filter['wpeae_max_price']) && floatval($filter['wpeae_max_price']) > 0.009) ? floatval($filter['wpeae_max_price']) : 0;

                $feedback_min = (isset($filter['min_feedback']) && intval($filter['min_feedback']) > 0) ? intval($filter['min_feedback']) : 0;
                $feedback_max = (isset($filter['max_feedback']) && intval($filter['max_feedback']) > 0) ? intval($filter['max_feedback']) : 0;
                if ($feedback_max < $feedback_min) {
                    $feedback_max = 0;
                }

                $available_to = (isset($filter['available_to']) && $filter['available_to']) ? $filter['available_to'] : "";

                $condition = (isset($filter['condition']) && $filter['condition']) ? $filter['condition'] : "";
                
                $free_shipping_only = (isset($filter['free_shipping_only']) && $filter['free_shipping_only']);
                
                $search_in_description = (isset($filter['search_in_description']) && $filter['search_in_description']);

                //$shipment_price_min = (isset($filter['shipment_min_price']) && floatval($filter['shipment_min_price']) > 0.009) ? floatval($filter['shipment_min_price']) : 0;
                //$shipment_price_max = (isset($filter['shipment_max_price']) && floatval($filter['shipment_max_price']) > 0.009) ? floatval($filter['shipment_max_price']) : 0;

                $category_id = (isset($filter['category_id']) && IntVal($filter['category_id'])) ? IntVal($filter['category_id']) : 0;
                $link_category_id = (isset($filter['link_category_id']) && IntVal($filter['link_category_id'])) ? IntVal($filter['link_category_id']) : 0;

                $store_name = isset($filter['store']) ? $filter['store'] : ""; //urlencode(utf8_encode($filter['store'])) : "";            

                $listing_type = isset($filter['listing_type']) && is_array($filter['listing_type']) ? $filter['listing_type'] : array();

                $pagenum = (intval($page)) ? $page : 1;

                if ($productId) {
                    $tmp_res = $this->load_detail(new WPEAE_Goods("ebay#$productId"), array("init_load" => true, "link_category_id" => $link_category_id, "site_code" => $site));
                    if ($tmp_res['state'] == 'ok') {
                        $result["total"] = 1;
                        $result["items"] = array($tmp_res['goods']);
                    } else {
                        $result["error"] = $tmp_res['message'];
                    }
                } else {
                    $apicall = "$endpoint?OPERATION-NAME=".($store_name?"findItemsIneBayStores":"findItemsAdvanced")
                    //$apicall = "$endpoint?OPERATION-NAME=".($store_name?"findItemsIneBayStores":"findItemsByKeywords")
                            . "&SERVICE-VERSION=1.13.0"
                            . "&GLOBAL-ID=$site"
                            . "&SECURITY-APPNAME=" . $this->account->appID
                            . "&RESPONSE-DATA-FORMAT=$responseEncoding"
                            . ($safeQuery ? "&keywords=" . $safeQuery : "")
                            . ($store_name ? "&storeName=" . $store_name : "")
                            . ($search_in_description ? "&descriptionSearch=true" : "")
                            . "&paginationInput.entriesPerPage=$per_page"
                            . "&paginationInput.pageNumber=$pagenum"
                            . "&sortOrder=BestMatch"
                            . "&outputSelector(0)=SellerInfo"
                            . "&outputSelector(1)=StoreInfo"
                            . "&descriptionSearch(1)=true"
                            . ($category_id ? "&categoryId=" . $category_id : "");


                    /*if ($this->account->use_affiliate_urls()) {
                        if (get_option('wpeae_ebay_custom_id')) {
                            $apicall.="&affiliate.customId=" . get_option('wpeae_ebay_custom_id');
                        }
                        if (get_option('wpeae_ebay_geo_targeting', false)) {
                            $apicall.="&affiliate.geoTargeting=true";
                        }else{
                            $apicall.="&affiliate.geoTargeting=false";
                        }
                        if (get_option('wpeae_ebay_network_id')) {
                            $apicall.="&affiliate.networkId=" . get_option('wpeae_ebay_network_id');
                        }
                        if (get_option('wpeae_ebay_tracking_id')) {
                            $apicall.="&affiliate.trackingId=" . get_option('wpeae_ebay_tracking_id');
                        }
                    }*/

                    $filter_index = 0;

                    $apicall.="&itemFilter($filter_index).name=HideDuplicateItems&itemFilter($filter_index).value=true";
                    $filter_index++;
                    
                    if ($feedback_min) {
                        $apicall.="&itemFilter($filter_index).name=FeedbackScoreMin&itemFilter($filter_index).value=$feedback_min";
                        $filter_index++;
                    }
                    if ($feedback_max) {
                        $apicall.="&itemFilter($filter_index).name=FeedbackScoreMax&itemFilter($filter_index).value=$feedback_max";
                        $filter_index++;
                    }
                    if ($price_min) {
                        $apicall.="&itemFilter($filter_index).name=MinPrice&itemFilter($filter_index).value=$price_min";
                        $filter_index++;
                    }
                    if ($price_max) {
                        $apicall.="&itemFilter($filter_index).name=MaxPrice&itemFilter($filter_index).value=$price_max";
                        $filter_index++;
                    }

                    if ($available_to) {
                        $apicall.="&itemFilter($filter_index).name=AvailableTo&itemFilter($filter_index).value=$available_to";
                        $filter_index++;
                    }

                    if ($condition) {
                        $apicall.="&itemFilter($filter_index).name=Condition&itemFilter($filter_index).value=$condition";
                        $filter_index++;
                    }
                    
                    /* show only USD
                    if (true) {
                        $apicall.="&itemFilter($filter_index).name=Currency&itemFilter($filter_index).value=USD";
                        $filter_index++;
                    }*/
                    
                    if ($free_shipping_only) {
                        $apicall.="&itemFilter($filter_index).name=FreeShippingOnly&itemFilter($filter_index).value=true";
                        $filter_index++;
                    }

                    if ($listing_type) {
                        $apicall.="&itemFilter($filter_index).name=ListingType";
                        for ($i = 0; $i < count($listing_type); $i++) {
                            $apicall.="&itemFilter($filter_index).value($i)=" . $listing_type[$i];
                        }
                        $filter_index++;
                    } else {
                        $apicall.="&itemFilter($filter_index).name=ListingType&itemFilter($filter_index).value=FixedPrice";
                    }

                    if (isset($_GET['orderby'])) {
                        switch ($_GET['orderby']) {
                            case 'price':
                                if ($_GET['order'] == 'asc') {
                                    $apicall.="&sortOrder=PricePlusShippingLowest";
                                } elseif ($_GET['order'] == 'desc') {
                                    $apicall.="&sortOrder=CurrentPriceHighest";
                                    //$apicall.="&sortOrder=PricePlusShippingHighest";
                                }
                                break;
                            default:
                                break;
                        }
                    }
                    
                    //echo $apicall;
     
                    $tmp_response = wpeae_remote_get($apicall);
                    if (is_wp_error($tmp_response)) {
                        $result["error"] = 'eBay api not response! Error: ['.$tmp_response->get_error_code().'] '.$tmp_response->get_error_message();
                    } else {
                        $body = wp_remote_retrieve_body($tmp_response);
                        $resp = simplexml_load_string($body);

                        if (isset($resp->errorMessage->error)) {
                            $result["error"] = "Error code: " . strval($resp->errorMessage->error->errorId) . ". " . strval($resp->errorMessage->error->message);
                        } else {
                            if ($resp && $resp->paginationOutput->totalEntries > 0) {
                                $result["total"] = (IntVal($resp->paginationOutput->totalEntries) > ($per_page * 100)) ? ($per_page * 100) : IntVal($resp->paginationOutput->totalEntries);
                                //$result["total"] = IntVal($resp->paginationOutput->totalEntries);

                                $currency_conversion_factor = floatval(str_replace(",", ".", strval(get_option('wpeae_currency_conversion_factor', 1))));
                                $tmp_variation_cnt = array();
                                foreach ($resp->searchResult->item as $item) {
                                    //echo "<pre>";print_r($item);echo "</pre>";

                                    $goods = new WPEAE_Goods();
                                    $goods->type = "ebay";
                                    $goods->external_id = strval($item->itemId);

                                    //set var=xxx params for use variation!!!
                                    $goods->detail_url = str_replace("item=0", "item=" . $item->itemId, $item->viewItemURL);

                                    $goods->load();

                                    $goods->link_category_id = $link_category_id;

                                    $goods->image = ($item->galleryURL) ? strval($item->galleryURL) : WPEAE_NO_IMAGE_URL;
                                    
                                    if(isset($item->storeInfo->storeURL)){
                                        $goods->seller_url = strval($item->storeInfo->storeURL);
                                    }else if(isset($item->sellerInfo->sellerUserName)){
                                        $goods->seller_url = "http://www.ebay.com/usr/" . $item->sellerInfo->sellerUserName;
                                    }

                                    $goods->title = strval($item->title);
                                    $goods->subtitle = strval($item->subtitle);

                                    $goods->category_id = strval($item->primaryCategory->categoryId);
                                    $goods->category_name = strval($item->primaryCategory->categoryName);

                                    if (strlen(trim($goods->keywords)) == 0) {
                                        $goods->keywords = "#needload#";
                                    }

                                    if (strlen(trim($goods->description)) == 0) {
                                        $goods->description = "#needload#";
                                    }

                                    if (strlen(trim($goods->photos)) == 0) {
                                        $goods->photos = "#needload#";
                                    }

                                    $goods->additional_meta['filters'] = array('site_code' => $site);

                                    $goods->additional_meta['condition'] = strval($item->condition->conditionDisplayName);
                                    $goods->additional_meta['ship'] = WPEAE_Goods::get_normalize_price($item->shippingInfo->shippingServiceCost);
                                    $goods->additional_meta['ship_to_locations'] = "";
                                    foreach ($item->shippingInfo->shipToLocations as $sl) {
                                        $goods->additional_meta['ship_to_locations'] .= (strlen($goods->additional_meta['ship_to_locations']) > 0 ? ", " : "") . $sl;
                                    }

                                    $goods->price = round(WPEAE_Goods::get_normalize_price($item->sellingStatus->convertedCurrentPrice), 2);

                                    //$priceCurr = trim(strval($item->sellingStatus->convertedCurrentPrice['currencyId']));
                                    //$shipCurr = trim(strval($item->shippingInfo->shippingServiceCost['currencyId']));
                                    //$goods->curr = ($priceCurr == $shipCurr) ? $priceCurr : ($priceCurr . (strlen($priceCurr) > 0 ? " / " : "") . $shipCurr);

                                    if (get_option('wpeae_ebay_using_woocommerce_currency', false) && get_woocommerce_currency() === trim(strval($item->sellingStatus->currentPrice['currencyId']))) {
                                        $goods->price = round(WPEAE_Goods::get_normalize_price($item->sellingStatus->currentPrice), 2);
                                        $goods->curr = trim(strval($item->sellingStatus->currentPrice['currencyId']));
                                    } else {
                                        $goods->price = round(WPEAE_Goods::get_normalize_price($item->sellingStatus->convertedCurrentPrice), 2);
                                        $goods->curr = trim(strval($item->sellingStatus->convertedCurrentPrice['currencyId']));
                                    }

                                    $goods->save("API");

                                    //if (strlen(trim(strval($goods->user_price))) == 0) {
                                        $goods->user_price = round($goods->price * $currency_conversion_factor, 2);
                                        $goods->save_field("user_price", sprintf("%01.2f", $goods->user_price));
                                    //}

                                    if (strlen(trim(strval($goods->user_image))) == 0) {
                                        $goods->save_field("user_image", $goods->image);
                                    }

                                    //$result["items"][$goods->getId()] = $goods;
                                    $result["items"][] = apply_filters('wpeae_modify_goods_data', $goods, $item, "ebay_load_list");
                                }
                            }
                        }
                    }
                }
            } else {
                $result["error"] = 'Please enter some search keywords or input specific prodcutId or specifc store name or select item from category list!';
            }

            return $result;
        }

        public function load_detail(/* @var $goods WPEAE_Goods */ $goods, $params = array()) {

            
            //$goods->additional_meta['filters']['site_code']
            $site_code = isset($params["site_code"])?$params["site_code"]:(isset($goods->additional_meta['filters']['site_code'])?$goods->additional_meta['filters']['site_code']:'');
            $site_id = 0;
            if ($site_code) {
                $sites_list = $this->api->get_sites();
                foreach ($sites_list as $s) {
                    if ($s['id'] === $site_code) {
                        $site_id = $s['code'];
                        break;
                    }
                }
            }

            $init_load = isset($params["init_load"]) ? $params["init_load"] : false;

            $api_url = "http://open.api.ebay.com/shopping?callname=GetSingleItem&responseencoding=XML&appid=" . $this->account->appID .
                    "&siteid=" . $site_id .
                    //"&version=515".
                    "&version=889" .
                    "&ItemID=" . $goods->external_id . "&IncludeSelector=ItemSpecifics,Description,Details,Variations,StoreInfo";
            
            if ($this->account->use_affiliate_urls()) {
                if (get_option('wpeae_ebay_tracking_id')) {
                    $api_url.="&trackingid=" . urlencode(utf8_encode(get_option('wpeae_ebay_tracking_id')));
                }
                if (get_option('wpeae_ebay_network_id')) {
                    $api_url.="&trackingpartnercode=" . urlencode(utf8_encode(get_option('wpeae_ebay_network_id')));
                }
                if (get_option('wpeae_ebay_custom_id')) {
                    $api_url.="&affiliateuserid=" . urlencode(utf8_encode(get_option('wpeae_ebay_custom_id')));
                }
            }
            
            //echo $api_url;
            
            $tmp_response = wpeae_remote_get($api_url);
            if (is_wp_error($tmp_response)) {
                $result = array("state" => "error", "message" => "eBay api not response! Error: [".$tmp_response->get_error_code()."] ".$tmp_response->get_error_message());
            } else {
                $body = wp_remote_retrieve_body($tmp_response);

                $detail_xml = simplexml_load_string($body);
                // debug
                //$detail_xml->Item->Description="";
                //echo "<pre>";var_dump($detail_xml);echo "</pre>";
                //echo "<pre>";print_r($detail_xml);echo "</pre>";
                //echo "<pre>"; print_r($detail_xml->Item->Variations); echo "</pre>"; 

                if (!isset($detail_xml->Errors)) {
                    if ($init_load) {
                        
                        $currency_conversion_factor = floatval(str_replace(",", ".", strval(get_option('wpeae_currency_conversion_factor', 1))));

                        $goods->type = "ebay";
                        $goods->external_id = strval($detail_xml->Item->ItemID);
                        $goods->load();
                        
                        // Important! update detail url for get correct affiliate link
                        $goods->detail_url = $detail_xml->Item->ViewItemURLForNaturalSearch;

                        $goods->link_category_id = isset($params["link_category_id"]) ? $params["link_category_id"] : 0;

                        $goods->image = ($detail_xml->Item->GalleryURL) ? strval($detail_xml->Item->GalleryURL) : WPEAE_NO_IMAGE_URL;

                        if(isset($detail_xml->Item->Storefront->StoreURL)){
                            $goods->seller_url = $detail_xml->Item->Storefront->StoreURL;
                        }else if(isset($detail_xml->Item->Seller->UserID)){
                            $goods->seller_url = "http://www.ebay.com/usr/" . $detail_xml->Item->Seller->UserID;
                        }

                        $goods->title = strval($detail_xml->Item->Title);
                        $goods->subtitle = strval($detail_xml->Item->Subtitle);

                        $goods->category_id = strval($detail_xml->Item->PrimaryCategoryID);
                        $goods->category_name = strval($detail_xml->Item->PrimaryCategoryName);

                        if (strlen(trim($goods->keywords)) == 0) {
                            $goods->keywords = "#needload#";
                        }

                        if (strlen(trim($goods->description)) == 0) {
                            $goods->description = "#needload#";
                        }

                        if (strlen(trim($goods->photos)) == 0) {
                            $goods->photos = "#needload#";
                        }

                        if (isset($params["site_code"])) {
                            $goods->additional_meta['filters'] = array('site_code' => $params["site_code"]);
                        }

                        $goods->additional_meta['condition'] = "";
                        $goods->additional_meta['ship'] = "0.00";

                        $goods->additional_meta['ship_to_locations'] = "";
                        foreach ($detail_xml->Item->ShipToLocations as $sl) {
                            $goods->additional_meta['ship_to_locations'] .= (strlen($goods->additional_meta['ship_to_locations']) > 0 ? ", " : "") . $sl;
                        }

                        if (get_option('wpeae_ebay_using_woocommerce_currency', false) && get_woocommerce_currency() === trim(strval($detail_xml->Item->CurrentPrice['currencyID']))) {
                            $goods->price = round(WPEAE_Goods::get_normalize_price($detail_xml->Item->CurrentPrice), 2);
                            $goods->curr = trim(strval($detail_xml->Item->CurrentPrice['currencyID']));
                        } else {
                            $goods->price = round(WPEAE_Goods::get_normalize_price($detail_xml->Item->ConvertedCurrentPrice), 2);
                            $goods->curr = trim(strval($detail_xml->Item->ConvertedCurrentPrice['currencyID']));
                        }

                        $goods->save("API");

                        if (strlen(trim(strval($goods->user_price))) == 0) {
                            $goods->user_price = round($goods->price * $currency_conversion_factor, 2);
                            $goods->save_field("user_price", sprintf("%01.2f", $goods->user_price));
                        }

                        if (strlen(trim(strval($goods->user_image))) == 0) {
                            $goods->save_field("user_image", $goods->image);
                        }
                    }
                    
                    // Important! update detail url for get correct affiliate link
                    $goods->detail_url = strval($detail_xml->Item->ViewItemURLForNaturalSearch);
                    
                    $goods->description = $detail_xml->Item->Description;
                    $goods->description = $this->clear_html($goods->description);
                    $goods->description = WPEAE_Utils::remove_tags($goods->description);

                    $attr_list = array();
                    if (isset($detail_xml->Item->ItemSpecifics)) {
                        foreach ($detail_xml->Item->ItemSpecifics->NameValueList as $attr) {
                            $value = "";
                            foreach ($attr->Value as $v) {
                                $value.=($value == "" ? "" : ", ") . $v;
                            }
                            $attr_list[] = array("name" => strval($attr->Name), "value" => $value);
                        }
                    }

                    $goods->additional_meta['attribute'] = $attr_list ? $attr_list : array();

                    if (isset($detail_xml->Item->Quantity) && !get_option('wpeae_ebay_user_random_quantity', false)) {
                        $quantity = intval($detail_xml->Item->Quantity);
                        if (isset($detail_xml->Item->QuantitySold) && intval($detail_xml->Item->QuantitySold)) {
                            $quantity -= intval($detail_xml->Item->QuantitySold);
                        }
                        $goods->additional_meta['quantity'] = $quantity;
                    }

                    $tmp_p = "";
                    $new_prew = "";
                    foreach ($detail_xml->Item->PictureURL as $img_url) {
                        $img_url = preg_replace('/\$\_(\d+)\.JPG/i', '$_10.JPG', $img_url);
                        if (!$new_prew) {
                            $new_prew = strval($img_url);
                        }
                        $tmp_p .= ($tmp_p ? "," : "") . $img_url;
                    }
                    $goods->photos = $tmp_p;

                    if ($goods->detail_url) {
                        try {
                            $page_meta = get_meta_tags($goods->detail_url);
                            $goods->keywords = (isset($page_meta["keywords"]) ? $page_meta["keywords"] : "");
                        } catch (Exception $e) {
                            
                        }
                    }

                    $goods->save("API");

                    if ($new_prew && (strlen(trim(strval($goods->user_image))) == 0 || trim(strval($goods->user_image)) === trim(strval($goods->image)))) {
                        if($goods->image === WPEAE_NO_IMAGE_URL){
                            $goods->image = $new_prew;
                        }
                        $goods->save_field("user_image", $new_prew);
                    }

                    $result = array("state" => "ok", "message" => "", "goods" => apply_filters('wpeae_modify_goods_data', $goods, $detail_xml, "ebay_load_detail"));
                } else {
                    $result = array("state" => "error", "message" => "" . "Error code: ".$detail_xml->Errors->ErrorCode.". ".$detail_xml->Errors->LongMessage, "goods" => $goods);
                }
            }

            return $result;
        }

        public function check_availability(/* @var $goods WPEAE_Goods */ $goods) {
            $site_id = 0;

            $api_url = "http://open.api.ebay.com/shopping?callname=GetSingleItem&responseencoding=XML&appid=" . $this->account->appID . "&siteid=" . $site_id . "&version=515&ItemID=" . $goods->external_id . "&IncludeSelector=Description,Details,Variations";

            $tmp_response = wpeae_remote_get($api_url);
            if (is_wp_error($tmp_response)) {
                // if ebay is not response? just return true (product availabile)
                return true;
            }
            $body = wp_remote_retrieve_body($tmp_response);
            $detail_xml = simplexml_load_string($body);
            
            $end_time = isset($detail_xml->Item->EndTime) ? strtotime($detail_xml->Item->EndTime) : (time() + 60);
            
            return $end_time > time();
        }

        public function get_detail($productId, $params = array()) {
            $site_id = 0;
            if (isset($params["site_code"])) {
                $sites_list = $this->api->get_sites();
                foreach ($sites_list as $s) {
                    if ($s['id'] === $params["site_code"]) {
                        $site_id = $s['code'];
                        break;
                    }
                }
            }

            $api_url = "http://open.api.ebay.com/shopping?callname=GetSingleItem&responseencoding=XML&appid=" . $this->account->appID . "&siteid=" . $site_id . "&version=515&ItemID=" . $productId . "&IncludeSelector=Description,Details,ShippingCosts,Variations";
            if ($this->account->use_affiliate_urls()) {
                if (get_option('wpeae_ebay_tracking_id')) {
                    $api_url.="&trackingid=" . urlencode(utf8_encode(get_option('wpeae_ebay_tracking_id')));
                }
                if (get_option('wpeae_ebay_network_id')) {
                    $api_url.="&trackingpartnercode=" . urlencode(utf8_encode(get_option('wpeae_ebay_network_id')));
                }
                if (get_option('wpeae_ebay_custom_id')) {
                    $api_url.="&affiliateuserid=" . urlencode(utf8_encode(get_option('wpeae_ebay_custom_id')));
                }
            }

            $tmp_response = wpeae_remote_get($api_url);
            if (is_wp_error($tmp_response)) {
                $result = array("state" => "error", "message" => "eBay api not response! Error: [".$tmp_response->get_error_code()."] ".$tmp_response->get_error_message());
            } else {
                $body = wp_remote_retrieve_body($tmp_response);
                $detail_xml = simplexml_load_string($body);
                
                if (!isset($detail_xml->Errors)) {
                    $goods = new WPEAE_Goods("ebay#" . $detail_xml->Item->ItemID);

                    // check_availability
                    $end_time = isset($detail_xml->Item->EndTime) ? strtotime(strval($detail_xml->Item->EndTime)) : (time() + 60);                    
                    $goods->availability = $end_time > time();

                    $goods->image = ($detail_xml->Item->GalleryURL) ? strval($detail_xml->Item->GalleryURL) : WPEAE_NO_IMAGE_URL;
                    $goods->detail_url = strval($detail_xml->Item->ViewItemURLForNaturalSearch);
                    $goods->seller_url = "http://www.ebay.com/usr/" . $detail_xml->Item->Seller->UserID;
                    $goods->title = strval($detail_xml->Item->Title);
                    $goods->subtitle = strval($detail_xml->Item->Subtitle);
                    $goods->category_id = strval($detail_xml->Item->PrimaryCategoryID);
                    $goods->category_name = strval($detail_xml->Item->PrimaryCategoryName);
                    $goods->keywords = "#needload#";
                    $goods->description = "#needload#";
                    $goods->photos = "#needload#";
                    $goods->additional_meta['condition'] = "";
                    $currency_conversion_factor = floatval(str_replace(",", ".", strval(get_option('wpeae_currency_conversion_factor', 1))));

                    $goods->additional_meta['ship'] = WPEAE_Goods::get_normalize_price(strval($detail_xml->Item->ShippingCostSummary->ShippingServiceCost));
                    
                    if (isset($params["site_code"])) {
                        $goods->additional_meta['filters'] = array('site_code' => $params["site_code"]);
                    }

                    if (get_option('wpeae_ebay_using_woocommerce_currency', false) && get_woocommerce_currency() === trim(strval($detail_xml->Item->CurrentPrice['currencyID']))) {
                        $goods->price = round(WPEAE_Goods::get_normalize_price(strval($detail_xml->Item->CurrentPrice)), 2);
                        $goods->curr = trim(strval($detail_xml->Item->CurrentPrice['currencyID']));
                    } else {
                        $goods->price = round(WPEAE_Goods::get_normalize_price(strval($detail_xml->Item->ConvertedCurrentPrice)), 2);
                        $goods->curr = trim(strval($detail_xml->Item->ConvertedCurrentPrice['currencyID']));
                    }

                    $goods->additional_meta['ship_to_locations'] = "";
                    foreach ($detail_xml->Item->ShipToLocations as $sl) {
                        $goods->additional_meta['ship_to_locations'] .= (strlen($goods->additional_meta['ship_to_locations']) > 0 ? ", " : "") . $sl;
                    }

                    if (isset($detail_xml->Item->Quantity) && !get_option('wpeae_ebay_user_random_quantity', false)) {
                        $quantity = intval($detail_xml->Item->Quantity);
                        if (isset($detail_xml->Item->QuantitySold) && intval($detail_xml->Item->QuantitySold)) {
                            $quantity -= intval($detail_xml->Item->QuantitySold);
                        }
                        $goods->additional_meta['quantity'] = $quantity;
                    }

                    $goods->user_price = round($goods->price * $currency_conversion_factor, 2);
                    
                    
                    $result = array("state" => "ok", "message" => "", "goods" => apply_filters('wpeae_modify_goods_data', $goods, $detail_xml, "ebay_get_detail"));
                } else {
                    $result = array("state" => "error", "message" => "" . $detail_xml->Errors->LongMessage);
                }
            }
            return $result;
        }

        private function clear_html($in_html) {
            if (!$in_html)
                return "";
            $html = $in_html;
            $html = preg_replace('/<span class="ebay"[^>]*?>.*?<\/span>/i', '', $html);
            $html = preg_replace("/<\/?h[1-9]{1}[^>]*\>/i", "", $html);
            $html = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $html);
            $html = preg_replace('/(<[^>]+) class=".*?"/i', '$1', $html);
            $html = preg_replace('/(<[^>]+) width=".*?"/i', '$1', $html);
            $html = preg_replace('/(<[^>]+) height=".*?"/i', '$1', $html);
            $html = preg_replace('/(<[^>]+) alt=".*?"/i', '$1', $html);

            $html = force_balance_tags($html);
            return $html;
        }

    }

    

    

    

    

endif;