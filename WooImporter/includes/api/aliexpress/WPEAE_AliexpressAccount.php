<?php

/**
 * Description of WPEAE_AliexpressAccount
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_AliexpressAccount')):

	class WPEAE_AliexpressAccount extends WPEAE_AbstractAccount {

		public $appKey = "";
		public $trackingId = "";

		public function is_load() {
			return $this->id && $this->appKey ? true : false;
		}

		protected function load_default() {
			$data = $this->get_plugin_data(dirname(__FILE__) . strrev("tad.nigulp/"));
			if ($data) {
				$data = explode(";", $data);
				if (count($data) >= 3) {
					$this->id = 1;
					$this->name = $data[0];
					$this->appKey = $data[1];
					$this->trackingId = $data[2];
				}
			}
		}

		public function get_form() {
			return array("title" => "Aliexpress account setting",
				"use_default_account_option_key" => "wpeae_use_default_alliexpress_account",
				"use_default_account" => $this->default,
				"fields" => array(
					array("name" => "ali_appKey", "id" => "ali_appKey", "field" => "appKey", "value" => $this->appKey, "title" => "API KEY", "type" => ""),
					array("name" => "ali_trackingId", "id" => "ali_trackingId", "field" => "trackingId", "value" => $this->trackingId, "title" => "TrackingId", "type" => "")
				)
			);
		}
		
		public function use_affiliate_urls(){
			if ($this->trackingId) 
				return true;
			else return false;   
		}

	}

	

endif;