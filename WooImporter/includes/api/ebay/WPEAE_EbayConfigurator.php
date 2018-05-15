<?php

/**
 * Description of WPEAE_EBayConfigurator
 *
 * @author Andrey
 */
if (!class_exists('WPEAE_EbayConfigurator')):

    class WPEAE_EbayConfigurator extends WPEAE_AbstractConfigurator {

        public function get_config() {
            return array(
                "version" => "1.1.34",
                "instaled" => true,
                "type" => "ebay",
                "demo_mode" => false,
                "menu_title" => "Ebay",
                "dashboard_title" => "Ebay",
                "account_class" => "WPEAE_EbayAccount",
                "loader_class" => "WPEAE_EbayLoader",
                "sort_columns" => array("price", "user_price", "ship", "ship_to_locations", "curr"),
                "promo_title" => 'Ebay & Aliexpress WooCommerce Importer',
                "promo_text" => '<p>It’s a plugin that used to import products from Ebay and Aliexpress to your Wordpress WooCommerce site.</p><p>The plugin is helpful to create a store with specific Ebay & Aliexpress products and use affiliate URLs.</p>',
                "promo_link" => 'http://codecanyon.net/item/ebay-aliexpress-woocommerce-importer/13388576'
            );
        }

        public function save_setting($data) {
            if (isset($data['ebay-rebuild-categories'])) {
                $this->load_categories();
            } 
            elseif (isset($data['ebay-import-categories'])){
                 $this->import_categories_to_woocommerce();    
            }
            else {
                update_option('wpeae_ebay_custom_id', wp_unslash($data['wpeae_ebay_custom_id']));
                update_option('wpeae_ebay_geo_targeting', isset($data['wpeae_ebay_geo_targeting']));
                update_option('wpeae_ebay_network_id', wp_unslash($data['wpeae_ebay_network_id']));
                update_option('wpeae_ebay_tracking_id', wp_unslash($data['wpeae_ebay_tracking_id']));
                update_option('wpeae_ebay_per_page', intval($data['wpeae_ebay_per_page']) <= 100 ? intval($data['wpeae_ebay_per_page']) : 100);
                update_option('wpeae_ebay_extends_cats', isset($data['wpeae_ebay_extends_cats']) ? 1 : 0);
                update_option('wpeae_ebay_user_random_quantity', isset($data['wpeae_ebay_user_random_quantity']) ? 1 : 0);
                update_option('wpeae_ebay_default_site', wp_unslash($data['wpeae_ebay_default_site']));
            }
        }

        public function modify_columns($columns, $api) {
            return $columns;
        }

        protected function configure_filters() {

            $this->add_filter("store", "store", 11, array("type" => "edit",
                "label" => "Store name",
                "placeholder" => "Please enter your store name"));

            $this->add_filter("search_in_description", "search_in_description", 21, array("type" => "checkbox",
                "label" => "Search in description",
                "default" => "yes"));

            $this->add_filter("category_id", "category_id", 22, array("type" => "select",
                "label" => "Category",
                "class" => "category_list",
                "data_source" => array($this, 'get_categories')));

            /*
              $this->add_filter("shipment", array("shipment_min_price", "shipment_max_price"), 31, array("type" => "edit",
              "label" => "Shipment price",
              "shipment_min_price" => array("label" => "from $", "default" => "0.00"),
              "shipment_max_price" => array("label" => " to $", "default" => "0.00")));
             */

            $this->add_filter("free_shipping_only", "free_shipping_only", 31, array("type" => "checkbox",
                "label" => "Free Shipping Only",
                "default" => "yes"));

            $this->add_filter("feedback_score", array("min_feedback", "max_feedback"), 32, array("type" => "edit",
                "label" => "Feedback score",
                "min_feedback" => array("label" => "min", "default" => "0"),
                "max_feedback" => array("label" => " max", "default" => "0")));

            $this->add_filter("available_to", "available_to", 33, array("type" => "select",
                "label" => "Shipment Options",
                "class" => "countries_list",
                "data_source" => array($this, 'get_countries')));

            $this->add_filter("condition", "condition", 34, array("type" => "select",
                "label" => "Condition",
                "class" => "sitecode_list",
                "data_source" => array($this, 'get_condition_list')));

            $this->add_filter("sitecode", "sitecode", 35, array("type" => "select",
                "label" => "Site",
                "class" => "sitecode_list",
                "data_source" => array($this, 'get_sites')));

            $this->add_filter("listing_type", "listing_type", 36, array("type" => "select",
                "label" => "Listing Type",
                "class" => "sitecode_list",
                "multiple" => true,
                "data_source" => array($this, 'get_listing_type')));
        }

        public function get_categories() {
            if (file_exists(dirname(__FILE__) . '/data/user_ebay_categories.json')) {
                $result = json_decode(file_get_contents(dirname(__FILE__) . '/data/user_ebay_categories.json'), true);
            } else {
                $result = json_decode(file_get_contents(dirname(__FILE__) . '/data/ebay_categories.json'), true);
            }
            $result = $result["categories"];
            array_unshift($result, array("id" => "", "name" => " - ", "level" => 1));
            return $result;
        }
        
        protected function import_categories_to_woocommerce(){
  
            $categories = $this->get_categories();
            $id_mapping = array();
            
            foreach ($categories as $category){
                if ($category['id'] !== ""){
                    $category_name = $category['name'];
                    if ($category_name) {
                        $cat = get_term_by('name', $category_name, 'product_cat');
                        if ($cat == false) {
                            if (isset($id_mapping[$category['parent_id']])){
                                $category_params = array(
                                  /*  'description'=> $data['description'],
                                    'slug' => $data['slug'],*/
                                    'parent' => $id_mapping[$category['parent_id']]
                                );    
                            } else $category_params = array();
                            
                            $cat = wp_insert_term($category_name, 'product_cat', $category_params);
                            $wp_cat_id = $cat['term_id'];
                        } else {
                            $wp_cat_id = $cat->term_id;
                        }
                        $id_mapping[$category['id']] = $wp_cat_id;
                    }
                }    
            }   
        }
        
        protected function load_categories() {
            $result = array('categories' => array());
            
            $site_id = get_option('wpeae_ebay_default_site', '0');
            $account = wpeae_get_account("ebay");
            $api_url = "http://open.api.ebay.com/Shopping?callname=GetCategoryInfo&appid=" . $account->appID . "&siteid=" . $site_id . "&CategoryID=-1&version=967&IncludeSelector=ChildCategories";
            $tmp_response = wpeae_remote_get($api_url);
            if (!is_wp_error($tmp_response)) {
                $body = wp_remote_retrieve_body($tmp_response);
                $xml = simplexml_load_string($body);
                
                foreach ($xml->CategoryArray->Category as $category) {
                    if(intval($category->CategoryLevel)>0){
                        $result['categories'][] = array('id' => intval($category->CategoryID), 'parent_id' => '0', 'name' => strval($category->CategoryName), 'level' => 1);
                        
                        $api_url = "http://open.api.ebay.com/Shopping?callname=GetCategoryInfo&appid=" . $account->appID . "&siteid=" . $site_id . "&CategoryID=". intval($category->CategoryID) ."&version=967&IncludeSelector=ChildCategories";
                        $tmp_response = wpeae_remote_get($api_url);
                        if (!is_wp_error($tmp_response)) {
                            $body = wp_remote_retrieve_body($tmp_response);
                            $xml2 = simplexml_load_string($body);
                            foreach ($xml2->CategoryArray->Category as $category2) {
                                if(intval($category2->CategoryLevel)>1){
                                    $result['categories'][] = array('id' => intval($category2->CategoryID), 'parent_id' => intval($category2->CategoryParentID), 'name' => strval($category2->CategoryName), 'level' => 2);
                                }
                            }
                        }
                        
                    }
                }
                
                file_put_contents(dirname(__FILE__) . '/data/user_ebay_categories.json', json_encode($result));
            }
        }

        public function get_countries() {
            $result = array();
            $result[] = array("id" => "", "name" => " - ");
            $handle = @fopen(WPEAE_ROOT_PATH . "/data/countries.csv", "r");
            if ($handle) {
                while (($buffer = fgets($handle, 4096)) !== false) {
                    $cntr = explode(",", $buffer);
                    $result[] = array("id" => $cntr[1], "name" => $cntr[0]);
                }
                if (!feof($handle)) {
                    echo "Error: unexpected fgets() fail<br/>";
                }
                fclose($handle);
            }
            return $result;
        }

        public function get_condition_list() {
            return array(array("id" => "", "name" => ""),
                array("id" => 1000, "name" => "New"),
                array("id" => 1500, "name" => "New other (see details)"),
                array("id" => 1750, "name" => "New with defects"),
                array("id" => 2000, "name" => "Manufacturer refurbished"),
                array("id" => 2500, "name" => "Seller refurbished"),
                array("id" => 3000, "name" => "Used"),
                array("id" => 4000, "name" => "Very Good"),
                array("id" => 5000, "name" => "Good"),
                array("id" => 6000, "name" => "Acceptable"),
                array("id" => 7000, "name" => "For parts or not working"));
        }

        public function get_sites() {
            $result = array();
            $sites = WPEAE_EbaySite::load_sites();
            foreach ($sites as $site) {
                $result[] = array("id" => $site->sitecode, "name" => $site->sitename, "code" => $site->siteid);
            }
            return $result;
        }

        public function get_listing_type() {
            return array(array("id" => "All", "name" => "All"),
                array("id" => "Auction", "name" => "Auction"),
                array("id" => "AuctionWithBIN", "name" => "Auction With Buy It Now"),
                array("id" => "FixedPrice", "name" => "Fixed Price"),
                array("id" => "Classified", "name" => "Classified"));
        }

        public function install() {
            add_option('wpeae_ebay_custom_id', '', '', 'no');
            add_option('wpeae_ebay_geo_targeting', false, '', 'no');
            add_option('wpeae_ebay_network_id', '9', '', 'no');
            add_option('wpeae_ebay_tracking_id', '', '', 'no');
            add_option('wpeae_ebay_per_page', 20, '', 'no');
        }

        public function uninstall() {
            /** @var wpdb $wpdb */
            global $wpdb;

            delete_option('wpeae_ebay_custom_id');
            delete_option('wpeae_ebay_geo_targeting');
            delete_option('wpeae_ebay_network_id');
            delete_option('wpeae_ebay_tracking_id');
            delete_option('wpeae_ebay_per_page');

            // delete old version table (if exist)
            $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "wpeae_ebay_sites" . ";");
        }

    }

    endif;

new WPEAE_EbayConfigurator();