<?php
/**
 * Description of WPEAE_Shipping
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_Shipping')):

	class WPEAE_Shipping {
		
		public $type = "";
		public $external_id = "";
		public $to_country = "";
		public $quantity = 1;
		public $data = "";
		public $time = "";
		
		public $loaded = false;
		
		public function __construct($goods, $to_country, $quantity=1) {
			      
			$this->type = $goods->type;
			$this->external_id = $goods->external_id;
			$this->to_country = $to_country;
			$this->quantity = $quantity;
		}
		
		private function is_serialized($str) {
			return ($str == serialize(false) || @unserialize($str) !== false);
		}
		
		public function load() {
			/** @var wpdb $wpdb */
			global $wpdb;
			$this->loaded = false;
			if ($this->type && $this->external_id && $this->to_country && $this->quantity) {
				$results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . WPEAE_TABLE_SHIPPING . " WHERE type='$this->type' and external_id='$this->external_id' and to_country='$this->to_country' and quantity='$this->quantity'");
				if ($results) {
					foreach ($results[0] as $key => $val) {
						if ($this->is_serialized($val)) {
							$this->$key = unserialize($val);
						} else {
							$this->$key = $val;
						}
					}
					$this->loaded = true;
				}
			}
			return $this->loaded;
		}
		
		
		public function save_data($value = "") {
			/** @var wpdb $wpdb */
			global $wpdb;

			if (!$this->type || !$this->external_id || !$this->to_country || !$this->quantity)
				return;

			$this->data = $value;
			$data = array('type' => $this->type, 'external_id' => $this->external_id, 'to_country' => $this->to_country, 'quantity' => $this->quantity,  'data' => trim($value), 'time'=> date("Y-m-d H:i:s", time()));
			if ($this->loaded) {
				$wpdb->update($wpdb->prefix . WPEAE_TABLE_SHIPPING, $data, array('type' => $this->type, 'external_id' => $this->external_id, 'to_country' => $this->to_country, 'quantity' => $this->quantity));
			} else {
				$wpdb->insert($wpdb->prefix . WPEAE_TABLE_SHIPPING, $data);
			}
		}

	}

	
endif;