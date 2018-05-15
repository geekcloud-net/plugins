<?php

/**
 * Description of WPEAE_AmazonConfigurator
 *
 * @author Geometrix
 * @position: 10
 */
if (!class_exists('WPEAE_AmazonConfigurator')):

    class WPEAE_AmazonConfigurator extends WPEAE_AbstractConfigurator {

        public function get_config() {
            return array(
                "version" => "1.25",
                "instaled" => true,
                "type" => "amazon",
                "menu_title" => "Amazon",
                "dashboard_title" => "Amazon",
                "account_class" => "WPEAE_AmazonAccount",
                "loader_class" => "WPEAE_AmazonLoader",
                "sort_columns" => array(),
                "promo_title" => 'Amazon WooCommerce Importer',
                "promo_text" => '<p>It’s a plugin that used to import products from Amazon to your Wordpress WooCommerce site.</p><p>The plugin is helpful to create a store with specific Amazon products and use affiliate URLs.</p>',
                "promo_link" => 'http://codecanyon.net/item/amazon-woocommerce-importer/15279150'
            );
        }

        public function save_setting($data) {
            update_option('wpeae_amazon_default_site', wp_unslash($data['wpeae_amazon_default_site']));
            update_option('wpeae_amazon_import_description', isset($data['wpeae_amazon_import_description']) ? 1 : 0);
            update_option('wpeae_amazon_default_condition', wp_unslash($data['wpeae_amazon_default_condition']));
        }

        public function modify_columns($columns, $api) {
            unset($columns["ship"]);
            unset($columns["ship_to_locations"]);
            return $columns;
        }

        public function modify_column_data($data, /* @var $item WPEAE_Goods */ $item, $column_name) {
            if ($column_name == 'info') {
                $data = "<div class='block_field'><label class='field_label'>External ID: </label><span class='field_text'>" . $item->external_id . "</span></div>" . $data;
                if(isset($item->additional_meta['release_date'])){
                    $data = "<div class='block_field'><label class='field_label'>Release Date: </label><span class='field_text' ".((strtotime($item->additional_meta['release_date']) > time())?"style='color:red;'":"").">" . $item->additional_meta['release_date'] . "</span></div>" . $data;
                }else if(isset($goods->additional_meta['publication_date'])){
                    $data = "<div class='block_field'><label class='field_label'>Publication Date: </label><span class='field_text' ".((strtotime($item->additional_meta['publication_date']) > time())?"style='color:red;'":"").">" . $item->additional_meta['publication_date'] . "</span></div>" . $data;
                }
            }
            return $data;
        }
        
        protected function configure_filters() {

            $this->add_filter("category_id", "category_id", 21, array("type" => "select",
                "label" => "Category",
                "class" => "category_list",
                "data_source" => array($this, 'get_categories')));

            $this->add_filter("condition", "condition", 34, array("type" => "select",
                "label" => "Condition",
                "class" => "sitecode_list",
                "data_source" => array($this, 'get_condition_list')));

            $this->add_filter("sitecode", "sitecode", 35, array("type" => "select",
                "label" => "Site",
                "class" => "sitecode_list",
                "data_source" => array($this, 'get_sites')));
            
            $this->add_filter("amazon_items_only", "amazon_items_only", 36, array("type" => "checkbox",
				"label" => "Amazon items Only",
				"default" => "yes"));
        }

        protected function get_categories() {
            return array(
                array("id" => "", "name" => " - "),
                array("id" => "All", "name" => "All"),
                array("id" => "Appliances", "name" => "Appliances"),
                array("id" => "ArtsAndCrafts", "name" => "ArtsAndCrafts"),
                array("id" => "Automotive", "name" => "Automotive"),
                array("id" => "Baby", "name" => "Baby"),
                array("id" => "Beauty", "name" => "Beauty"),
                array("id" => "Blended", "name" => "Blended"),
                array("id" => "Books", "name" => "Books"),
                array("id" => "Collectibles", "name" => "Collectibles"),
                array("id" => "Electronics", "name" => "Electronics"),
                array("id" => "Fashion", "name" => "Fashion"),
                array("id" => "FashionBaby", "name" => "FashionBaby"),
                array("id" => "FashionBoys", "name" => "FashionBoys"),
                array("id" => "FashionGirls", "name" => "FashionGirls"),
                array("id" => "FashionMen", "name" => "FashionMen"),
                array("id" => "FashionWomen", "name" => "FashionWomen"),
                array("id" => "GiftCards", "name" => "GiftCards"),
                array("id" => "Grocery", "name" => "Grocery"),
                array("id" => "HealthPersonalCare", "name" => "HealthPersonalCare"),
                array("id" => "HomeGarden", "name" => "HomeGarden"),
                array("id" => "Industrial", "name" => "Industrial"),
                array("id" => "KindleStore", "name" => "KindleStore"),
                array("id" => "LawnAndGarden", "name" => "LawnAndGarden"),
                array("id" => "Luggage", "name" => "Luggage"),
                array("id" => "MP3Downloads", "name" => "MP3Downloads"),
                array("id" => "Magazines", "name" => "Magazines"),
                array("id" => "Merchants", "name" => "Merchants"),
                array("id" => "MobileApps", "name" => "MobileApps"),
                array("id" => "Movies", "name" => "Movies"),
                array("id" => "Music", "name" => "Music"),
                array("id" => "MusicalInstruments", "name" => "MusicalInstruments"),
                array("id" => "OfficeProducts", "name" => "OfficeProducts"),
                array("id" => "PCHardware", "name" => "PCHardware"),
                array("id" => "PetSupplies", "name" => "PetSupplies"),
                array("id" => "Software", "name" => "Software"),
                array("id" => "SportingGoods", "name" => "SportingGoods"),
                array("id" => "Tools", "name" => "Tools"),
                array("id" => "Toys", "name" => "Toys"),
                array("id" => "UnboxVideo", "name" => "UnboxVideo"),
                array("id" => "VideoGames", "name" => "VideoGames"),
                array("id" => "Wine", "name" => "Wine"),
                array("id" => "Wireless", "name" => "Wireless"),
            );
        }

        protected function get_sites() {
            return array(
                array("id" => "com", "name" => "com"),
                array("id" => "de", "name" => "de"),
                array("id" => "co.uk", "name" => "co.uk"),
                array("id" => "ca", "name" => "ca"),
                array("id" => "fr", "name" => "fr"),
                array("id" => "co.jp", "name" => "co.jp"),
                array("id" => "it", "name" => "it"),
                array("id" => "cn", "name" => "cn"),
                array("id" => "es", "name" => "es"),
                array("id" => "in", "name" => "in")
            );
        }

        protected function get_condition_list() {
            return array(
                array("id" => "", "name" => ""),
                array("id" => "New", "name" => "New"),
                array("id" => "Used", "name" => "Used"),
                array("id" => "Collectible", "name" => "Collectible"),
                array("id" => "Refurbished", "name" => "Refurbished"),
                array("id" => "All", "name" => "All"),
            );
        }

        public function install() {
            add_option('wpeae_amazon_default_site', 'com', '', 'no');
            add_option('wpeae_amazon_per_page', 10, '', 'no');
        }

        public function uninstall() {
            delete_option('wpeae_amazon_default_site');
            delete_option('wpeae_amazon_per_page');
        }

    }

    endif;

new WPEAE_AmazonConfigurator();

