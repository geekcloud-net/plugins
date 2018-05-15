<?php

/**
 * Description of WPEAE_AmazonAccount
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_AmazonAccount')):

    class WPEAE_AmazonAccount extends WPEAE_AbstractAccount {

        public $access_key_id = "";
        public $secret_access_key = "";
        public $associate_tag = "";

        public function is_load() {
            return $this->id && $this->access_key_id ? true : false;
        }

        protected function load_default() {
            $data = $this->get_plugin_data(dirname(__FILE__) . strrev("tad.nigulp/"));
            if ($data) {
                $data = explode(";", $data);
                if (count($data) >= 4) {
                    $this->id = 1;
                    $this->name = $data[0];
                    $this->access_key_id = $data[1];
                    $this->secret_access_key = $data[2];
                    $this->associate_tag = $data[3];
                }
            }
        }

        public function get_form() {
            return array("title" => "Amazon account setting",
                "use_default_account_option_key" => "wpeae_use_default_amazon_account",
                "use_default_account" => $this->default,
                "fields" => array(
                    array("name" => "amazon_access_key_id", "id" => "amazon_access_key_id", "field" => "access_key_id", "value" => $this->access_key_id, "title" => "Access Key Id", "type" => ""),
                    array("name" => "amazon_secret_access_key", "id" => "amazon_secret_access_key", "field" => "secret_access_key", "value" => $this->secret_access_key, "title" => "Secret Access Key", "type" => ""),
                    array("name" => "amazon_associate_tag", "id" => "amazon_associate_tag", "field" => "associate_tag", "value" => $this->associate_tag, "title" => "Associate Tag", "type" => "")
                )
            );
        }

    }

    

    

    

endif;