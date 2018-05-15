<?php

/**
 * Description of WPEAE_AliexpressConfigurator
 *
 * @author Geometrix
 */
include_once(dirname(__FILE__) . '/functions.php');

if (!class_exists('WPEAE_AliexpressConfigurator')):

    class WPEAE_AliexpressConfigurator extends WPEAE_AbstractConfigurator {

        public function get_config() {
            return array(
                "version" => "1.1.9",
                "instaled" => true,
                "type" => "aliexpress",
                "menu_title" => "Aliexpress",
                "dashboard_title" => "Aliexpress",
                "account_class" => "WPEAE_AliexpressAccount",
                "loader_class" => "WPEAE_AliexpressLoader",
                "sort_columns" => array("price", "user_price", "validTime"),
                "promo_title" => 'Ebay & Aliexpress WooCommerce Importer',
                "promo_text" => '<p>It’s a plugin that used to import products from Ebay and Aliexpress to your Wordpress WooCommerce site.</p><p>The plugin is helpful to create a store with specific Ebay & Aliexpress products and use affiliate URLs.</p>',
                "promo_link" => 'http://codecanyon.net/item/ebay-aliexpress-woocommerce-importer/13388576'
            );
        }

        protected function init_module() {
            add_filter('wpeae_load_list_item_proc', array($this, 'before_display_search_results'), 10, 2);
            add_filter('wpeae_get_detail_proc', array($this, 'after_load_detail_proc'), 10, 2);
        }

        public function before_display_search_results($items, $filter) {

            $forbidden_words = get_option('wpeae_ali_forbidden_words', 'aliexpress,china');

            if (!empty($forbidden_words)) {

                foreach ($items as &$item) {
                    $item->title = wpeae_ali_forbidden_words($item->title);
                }
            }
            return $items;
        }

        public function after_load_detail_proc($goods, $params) {

            $forbidden_words = get_option('wpeae_ali_forbidden_words', 'aliexpress,china');

            if (!empty($forbidden_words)) {

                $goods->title = wpeae_ali_forbidden_words($goods->title);
                $goods->description = wpeae_ali_forbidden_words($goods->description);

                /*
                  if (!empty($goods->additional_meta['attribute'])){
                  foreach ($goods->additional_meta['attribute'] as $key => $value){

                  }
                  } */
            }

            return $goods;
        }

        public function save_setting($data) {
            if (isset($data['aliexpress-rebuild-categories'])) {
                $this->load_categories();
            } else {
                update_option('wpeae_ali_per_page', intval($data['wpeae_ali_per_page']) <= 40 ? intval($data['wpeae_ali_per_page']) : 40);
                update_option('wpeae_ali_links_to_affiliate', isset($data['wpeae_ali_links_to_affiliate']));
                update_option('wpeae_ali_https_image_url', isset($data['wpeae_ali_https_image_url']) ? 1 : 0);
                update_option('wpeae_ali_local_currency', wp_unslash($data['wpeae_ali_local_currency']));
                update_option('wpeae_ali_import_description', isset($data['wpeae_ali_import_description']) ? 1 : 0);

                if (isset($_POST['wpeae_ali_forbidden_words'])) {
                    $value = trim($_POST['wpeae_ali_forbidden_words']);

                    update_option('wpeae_ali_forbidden_words', wp_unslash($value));
                }
            }
        }

        public function modify_columns($columns, $api) {

            $columns = array('cb' => '<input type="checkbox" />',
                'image' => '', 'info' => 'Information',
                'price' => 'Source Price',
                'user_price' => 'Posted Price',
                'commission' => 'Commission (8%)',
                'curr' => 'Currency',
                'volume' => 'Тotal orders (last 30 days)',
                'rating' => 'Rating',
                'validTime' => 'validTime');


            return $columns;
        }

        public function modify_column_data($data, /* @var $item WPEAE_Goods */ $item, $column_name) {
            if ($column_name == 'validTime') {
                $data = $item->additional_meta['validTime'];
            }

            if ($column_name == 'commission') {
                $data = $item->additional_meta['commission'];
            }

            if ($column_name == 'volume') {
                $data = $item->additional_meta['volume'];
            }

            if ($column_name == 'rating') {
                $data = $item->additional_meta['rating'];
            }

            if ($column_name == 'info') {
                $data = "<div class='block_field'><label class='field_label'>External ID: </label><span class='field_text'>" . $item->external_id . "</span></div>" . $data;
            }
            return $data;
        }

        protected function configure_filters() {
            $this->add_filter("category_id", "category_id", 21, array("type" => "select",
                "label" => "Category",
                "class" => "category_list",
                "style" => "width:25em;",
                "data_source" => array($this, 'get_categories')));
            /*
              $this->add_filter("commisionRate", array("commission_rate_from", "commission_rate_to"), 31, array("type" => "edit",
              "label" => "Commision Rate",
              "description" => "from 0.01 to 0.51",
              "commission_rate_from" => array("label" => "from"),
              "commission_rate_to" => array("label" => " to")));
             */
            $this->add_filter("volume", array("volume_from", "volume_to"), 32, array("type" => "edit",
                "label" => "Тotal orders (last 30 days)",
                "description" => "from 1 to 100",
                "volume_from" => array("label" => "from"),
                "volume_to" => array("label" => " to")));

            $this->add_filter("feedback_score", array("min_feedback", "max_feedback"), 33, array("type" => "edit",
                "label" => "Seller Credit Score",
                "min_feedback" => array("label" => "min", "default" => "0"),
                "max_feedback" => array("label" => " max", "default" => "0")));

            $this->add_filter("high_quality_items", "high_quality_items", 34, array("type" => "checkbox",
                "label" => "High Quality items",
                "default" => "yes"));
        }

        protected function get_categories() {
            if (file_exists(dirname(__FILE__) . '/data/user_aliexpress_categories.json')) {
                $result = json_decode(file_get_contents(dirname(__FILE__) . '/data/user_aliexpress_categories.json'), true);
            } else {
                $result = json_decode(file_get_contents(dirname(__FILE__) . '/data/aliexpress_categories.json'), true);
            }
            $result = $result["categories"];
            array_unshift($result, array("id" => "", "name" => " - ", "level" => 1));
            return $result;
        }

        protected function load_categories() {
            $request_url = "https://www.aliexpress.com/all-wholesale-products.html";
            $request_url = apply_filters('wpeae_get_localized_url', $request_url, array('type' => 'aliexpress_categories'));

            $response = wpeae_remote_get($request_url, array('cookies' => WPEAE_AliexpressLoader::getRequestCookies()));

            //$desc_content = wp_remote_get( "http://en.aliexpress.com/getSubsiteDescModuleAjax.htm?productId=" . $this->id );
            if (!is_wp_error($response)) {

                $result = array('categories' => array());

                $html = $response['body'];
                if (function_exists('mb_convert_encoding')) {
                    $html = trim(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
                } else {
                    $html = htmlspecialchars_decode(utf8_decode(htmlentities($html, ENT_COMPAT, 'UTF-8', false)));
                }

                $dom = new DOMDocument();
                libxml_use_internal_errors(true);

                $dom->loadHTML($html);
                $dom->preserveWhiteSpace = false;

                $finder = new DOMXPath($dom);

                $rows = $finder->query("//*[@class='cg-main']/div");
                foreach ($rows as $row) {
                    $cats = $row->getElementsByTagName('a');
                    $parrent = '';
                    foreach ($cats as $c) {
                        $cat_id = 0;
                        $cat_name = strval($c->nodeValue);
                        preg_match("/.*\/([0-9]*)\/.*/", $c->getAttribute('href'), $output_array);
                        if ($output_array) {
                            $cat_id = strval($output_array[1]);
                        }

                        $result['categories'][] = array('id' => $cat_id, 'parent_id' => $parrent, 'name' => $cat_name, 'level' => ($parrent ? 2 : 1));

                        $parrent = $parrent ? $parrent : $cat_id;
                    }
                }
                if (!empty($result['categories']))
                    file_put_contents(dirname(__FILE__) . '/data/user_aliexpress_categories.json', json_encode($result));
            }
        }

        public function install() {
            add_option('wpeae_ali_per_page', 20, '', 'no');
            add_option('wpeae_ali_forbidden_words', 20, 'aliexpress,china', 'no');
            add_option('wpeae_ali_links_to_affiliate', true, '', 'no');
            add_option('wpeae_ali_local_currency', '', '', 'no');
        }

        public function uninstall() {
            delete_option('wpeae_ali_per_page');
            delete_option('wpeae_ali_forbidden_words');
            delete_option('wpeae_ali_links_to_affiliate');
            delete_option('wpeae_ali_local_currency');
        }

    }

    endif;
new WPEAE_AliexpressConfigurator();
