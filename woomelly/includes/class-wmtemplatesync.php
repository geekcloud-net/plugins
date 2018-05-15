<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( !class_exists( 'WMTemplateSync' ) ) {
    /**
     * WMTemplateSync Class.
     */
    class WMTemplateSync {
    	private $id = 0;
    	private $woomelly_name_template_field = '';
    	private $woomelly_category_field = '';
    	private $woomelly_category_name_field = '';
    	private $woomelly_buying_mode_field = '';
    	private $woomelly_listing_type_id_field = '';
    	private $woomelly_condition_field = '';
    	private $woomelly_accepts_mercadopago_field = true;
    	private $woomelly_shipping_mode_field = '';
    	private $woomelly_shipping_accepted_methods_field = '';
    	private $woomelly_shipping_local_pick_up_field = true;
    	private $woomelly_shipping_free_shipping_field = true;
    	private $woomelly_shipping_dimensions_field = true;
    	private $woomelly_status_field = '';
    	private $woomelly_title_field = '';
    	private $woomelly_official_store_id_field = '';
    	private $woomelly_seller_custom_field = '';
    	private $woomelly_video_id_field = '';
    	private $woomelly_warranty_field = '';
    	private $woomelly_price_field = '';
    	private $woomelly_stock_field = '';
    	private $woomelly_custom_shipping_cost_field = '';
        private $woomelly_location_country_field = '';
        private $woomelly_location_state_field = '';
        private $woomelly_location_city_field = '';
        private $woomelly_price_variations_field = 'higher';
        private $woomelly_separate_variations_field = 'inactive';

        /**
         * Default constructor.
         */    	
		public function __construct ( $id = 0 ) {
			global $wpdb;
			
			$id = absint($id);

			if ( $id > 0 ) {
				$templatesync = $wpdb->get_results( "SELECT templatesync_key, templatesync_value FROM {$wpdb->prefix}wm_templatesync_meta WHERE templatesync_id = '".$id."';", OBJECT );
				if ( !empty($templatesync) ) {
					$this->id = $id;
					foreach ( $templatesync as $value ) {
						switch ( $value->templatesync_key ) {
							case '_wm_name_template':
								$this->woomelly_name_template_field = $value->templatesync_value;
								break;
							case '_wm_category':
								$this->woomelly_category_field = $value->templatesync_value;
								break;
							case '_wm_category_name':
								$this->woomelly_category_name_field = $value->templatesync_value;
								break;
							case '_wm_buying_mode':
								$this->woomelly_buying_mode_field = $value->templatesync_value;
								break;
							case '_wm_listing_type_id':
								$this->woomelly_listing_type_id_field = $value->templatesync_value;
								break;
							case '_wm_condition':
								$this->woomelly_condition_field = $value->templatesync_value;
								break;
							case '_wm_accepts_mercadopago':
								$this->woomelly_accepts_mercadopago_field = boolval($value->templatesync_value);
								break;
							case '_wm_shipping_mode':
								$this->woomelly_shipping_mode_field = $value->templatesync_value;
								break;
							case '_wm_shipping_accepted_methods':
								$this->woomelly_shipping_accepted_methods_field = $value->templatesync_value;
								break;
							case '_wm_shipping_local_pick_up':
								$this->woomelly_shipping_local_pick_up_field = boolval($value->templatesync_value);
								break;
							case '_wm_shipping_free_shipping':
								$this->woomelly_shipping_free_shipping_field = boolval($value->templatesync_value);
								break;
							case '_wm_shipping_dimensions':
								$this->woomelly_shipping_dimensions_field = boolval($value->templatesync_value);
								break;
							case '_wm_status':
								$this->woomelly_status_field = $value->templatesync_value;
								break;
							case '_wm_title':
								$this->woomelly_title_field = $value->templatesync_value;
								break;
							case '_wm_official_store_id':
								$this->woomelly_official_store_id_field = $value->templatesync_value;
								break;
							case '_wm_seller_custom':
								$this->woomelly_seller_custom_field = $value->templatesync_value;
								break;
							case '_wm_video_id':
								$this->woomelly_video_id_field = $value->templatesync_value;
								break;
							case '_wm_warranty':
								$this->woomelly_warranty_field = $value->templatesync_value;
								break;
							case '_wm_price':
								$this->woomelly_price_field = $value->templatesync_value;
								break;
							case '_wm_stock':
								$this->woomelly_stock_field = $value->templatesync_value;
								break;
							case '_wm_custom_shipping_cost':
								$this->woomelly_custom_shipping_cost_field = $value->templatesync_value;
								break;
							case '_wm_location_country':
								$this->woomelly_location_country_field = $value->templatesync_value;
								break;
							case '_wm_location_state':
								$this->woomelly_location_state_field = $value->templatesync_value;
								break;
							case '_wm_location_city':
								$this->woomelly_location_city_field = $value->templatesync_value;
								break;
							case '_wm_price_variations':
								$this->woomelly_price_variations_field = $value->templatesync_value;
								break;
							case '_wm_separate_variations':
								$this->woomelly_separate_variations_field = $value->templatesync_value;
								break;
								
						}
					}
				}
			}
			return false;
		} //End __construct()
		
		public function __clone () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), Woomelly()->get_version() );
		} //End __clone()

		public function __wakeup () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), Woomelly()->get_version() );
		} //End __wakeup()

		/**
		 * get_all.
		 *
		 * @return array
		 */
		static function get_all () {
			global $wpdb;			
			$tsync = array();
			
			$templatesync = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}wm_templatesync;", OBJECT );
			if ( !empty($templatesync) ) {
				foreach ( $templatesync as $value ) {
					$woomelly_name_template_field = wm_get_templatesync_meta( 'woomelly_name_template_field', $value->id );
					if ( $woomelly_name_template_field == "" ) {
						$woomelly_name_template_field = '#' . str_pad($value->id, 6, "0", STR_PAD_LEFT);
					}
					$woomelly_category_field = wm_get_templatesync_meta( 'woomelly_category_name_field', $value->id );
					if ( $woomelly_category_field != "" ) {
						$tsync[] = array( 'ID' => $value->id, 'title' => $woomelly_name_template_field, 'category' => $woomelly_category_field );
					}
				}
			}

			return $tsync;
		} //End get_all()

		/**
		 * get_all_select.
		 *
		 * @return array
		 */
		static function get_all_select () {
			global $wpdb;			
			$tsync = array();
			
			$templatesync = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}wm_templatesync;", OBJECT );
			if ( !empty($templatesync) ) {
				foreach ( $templatesync as $value ) {
					$woomelly_name_template_field = wm_get_templatesync_meta( 'woomelly_name_template_field', $value->id );
					if ( $woomelly_name_template_field == "" ) {
						$woomelly_name_template_field = '#' . str_pad($value->id, 6, "0", STR_PAD_LEFT);
					}					
					$tsync[] = $arrayName = array( 'ID' => $value->id, 'title' => $woomelly_name_template_field );
				}
			}

			return $tsync;
		} //End get_all_select()

		/**
		 * get_name_meta_key.
		 *
		 * @return string
		 */
		static function get_name_meta_key ( $meta_key ) {
			$_return = '';
			
			switch ( $meta_key ) {
				case 'woomelly_name_template_field':
					$_return = '_wm_name_template';
					break;
				case 'woomelly_category_field':
					$_return = '_wm_category';
					break;
				case 'woomelly_category_name_field':
					$_return = '_wm_category_name';
					break;
				case 'woomelly_buying_mode_field':
					$_return = '_wm_buying_mode';
					break;
				case 'woomelly_listing_type_id_field':
					$_return = '_wm_listing_type_id';
					break;
				case 'woomelly_condition_field':
					$_return = '_wm_condition';
					break;
				case 'woomelly_accepts_mercadopago_field':
					$_return = '_wm_accepts_mercadopago';
					break;
				case 'woomelly_shipping_mode_field':
					$_return = '_wm_shipping_mode';
					break;
				case 'woomelly_shipping_accepted_methods_field':
					$_return = '_wm_shipping_accepted_methods';
					break;
				case 'woomelly_shipping_local_pick_up_field':
					$_return = '_wm_shipping_local_pick_up';
					break;
				case 'woomelly_shipping_free_shipping_field':
					$_return = '_wm_shipping_free_shipping';
					break;
				case 'woomelly_shipping_dimensions_field':
					$_return = '_wm_shipping_dimensions';
					break;
				case 'woomelly_status_field':
					$_return = '_wm_status';
					break;
				case 'woomelly_title_field':
					$_return = '_wm_title';
					break;
				case 'woomelly_official_store_id_field':
					$_return = '_wm_official_store_id';
					break;
				case 'woomelly_seller_custom_field':
					$_return = '_wm_seller_custom';
					break;
				case 'woomelly_video_id_field':
					$_return = '_wm_video_id';
					break;
				case 'woomelly_warranty_field':
					$_return = '_wm_warranty';
					break;
				case 'woomelly_price_field':
					$_return = '_wm_price';
					break;
				case 'woomelly_stock_field':
					$_return = '_wm_stock';
					break;
				case 'woomelly_custom_shipping_cost_field':
					$_return = '_wm_custom_shipping_cost';
					break;
				case 'woomelly_location_country_field':
					$_return = '_wm_location_country';
					break;
				case 'woomelly_location_state_field':
					$_return = '_wm_location_state';
					break;
				case 'woomelly_location_city_field':
					$_return = '_wm_location_city';
					break;
				case 'woomelly_price_variations_field':
					$_return = '_wm_price_variations';
					break;
				case 'woomelly_separate_variations_field':
					$_return = '_wm_separate_variations';
					break;
			}

			return $_return;
		} //End get_name_meta_key()

		/**
		 * get_id.
		 *
		 * @return int
		 */
		public function get_id () {
			return intval($this->id);
		} //End get_id()

		/**
		 * get_format_id.
		 *
		 * @return string
		 */
		public function get_format_id () {
			return '#' . str_pad($this->id, 6, "0", STR_PAD_LEFT);
		} //End get_format_id()

		/**
		 * get_woomelly_name_template_field.
		 *
		 * @return string
		 */
		public function get_woomelly_name_template_field () {
			if ( $this->woomelly_name_template_field == "" ) {
				$this->woomelly_name_template_field = $this->get_format_id();
			}
			return $this->woomelly_name_template_field;
		} //End get_woomelly_name_template_field()
		
		/**
		 * set_woomelly_name_template_field.
		 *
		 * @return void
		 */
		public function set_woomelly_name_template_field ( $value ) {
			$this->woomelly_name_template_field = trim($value);			
		} //End set_woomelly_name_template_field()

		/**
		 * get_woomelly_category_field.
		 *
		 * @return string
		 */
		public function get_woomelly_category_field () {
			return $this->woomelly_category_field;
		} //End get_woomelly_category_field()
		
		/**
		 * set_woomelly_category_field.
		 *
		 * @return void
		 */
		public function set_woomelly_category_field ( $value ) {
			$this->woomelly_category_field = trim($value);			
		} //End set_woomelly_category_field()

		/**
		 * get_woomelly_category_name_field.
		 *
		 * @return string
		 */
		public function get_woomelly_category_name_field () {
			return $this->woomelly_category_name_field;
		} //End get_woomelly_category_name_field()
		
		/**
		 * set_woomelly_category_name_field.
		 *
		 * @return void
		 */
		public function set_woomelly_category_name_field ( $value ) {
			$this->woomelly_category_name_field = trim($value);			
		} //End set_woomelly_category_name_field()

		/**
		 * get_woomelly_buying_mode_field.
		 *
		 * @return string
		 */
		public function get_woomelly_buying_mode_field () {
			return $this->woomelly_buying_mode_field;
		} //End get_woomelly_buying_mode_field()

		/**
		 * set_woomelly_buying_mode_field.
		 *
		 * @return void
		 */
		public function set_woomelly_buying_mode_field ( $value ) {
			$this->woomelly_buying_mode_field = trim($value);			
		} //End set_woomelly_buying_mode_field()

		/**
		 * get_woomelly_listing_type_id_field.
		 *
		 * @return string
		 */
		public function get_woomelly_listing_type_id_field () {
			return $this->woomelly_listing_type_id_field;
		} //End get_woomelly_listing_type_id_field()

		/**
		 * set_woomelly_listing_type_id_field.
		 *
		 * @return void
		 */
		public function set_woomelly_listing_type_id_field ( $value ) {
			$this->woomelly_listing_type_id_field = trim($value);			
		} //End set_woomelly_listing_type_id_field()

		/**
		 * get_woomelly_condition_field.
		 *
		 * @return string
		 */
		public function get_woomelly_condition_field () {
			return $this->woomelly_condition_field;
		} //End get_woomelly_condition_field()

		/**
		 * set_woomelly_condition_field.
		 *
		 * @return void
		 */
		public function set_woomelly_condition_field ( $value ) {
			$this->woomelly_condition_field = trim($value);			
		} //End set_woomelly_condition_field()

		/**
		 * get_woomelly_accepts_mercadopago_field.
		 *
		 * @return bool
		 */
		public function get_woomelly_accepts_mercadopago_field () {
			return boolval($this->woomelly_accepts_mercadopago_field);
		} //End get_woomelly_accepts_mercadopago_field()

		/**
		 * set_woomelly_accepts_mercadopago_field.
		 *
		 * @return void
		 */
		public function set_woomelly_accepts_mercadopago_field ( $value ) {
			$this->woomelly_accepts_mercadopago_field = boolval( $value );			
		} //End set_woomelly_accepts_mercadopago_field()

		/**
		 * get_woomelly_shipping_mode_field.
		 *
		 * @return string
		 */
		public function get_woomelly_shipping_mode_field () {
			return $this->woomelly_shipping_mode_field;
		} //End get_woomelly_shipping_mode_field()

		/**
		 * set_woomelly_shipping_mode_field.
		 *
		 * @return void
		 */
		public function set_woomelly_shipping_mode_field ( $value ) {
			$this->woomelly_shipping_mode_field = trim($value);			
		} //End set_woomelly_shipping_mode_field()

		/**
		 * get_woomelly_shipping_accepted_methods_field.
		 *
		 * @return string
		 */
		public function get_woomelly_shipping_accepted_methods_field () {
			return $this->woomelly_shipping_accepted_methods_field;
		} //End get_woomelly_shipping_accepted_methods_field()

		/**
		 * set_woomelly_shipping_accepted_methods_field.
		 *
		 * @return void
		 */
		public function set_woomelly_shipping_accepted_methods_field ( $value ) {
			$this->woomelly_shipping_accepted_methods_field = trim($value);			
		} //End set_woomelly_shipping_accepted_methods_field()

		/**
		 * get_woomelly_shipping_local_pick_up_field.
		 *
		 * @return bool
		 */
		public function get_woomelly_shipping_local_pick_up_field () {
			return boolval($this->woomelly_shipping_local_pick_up_field);
		} //End get_woomelly_shipping_local_pick_up_field()

		/**
		 * set_woomelly_shipping_local_pick_up_field.
		 *
		 * @return void
		 */
		public function set_woomelly_shipping_local_pick_up_field ( $value ) {
			$this->woomelly_shipping_local_pick_up_field = boolval( $value );			
		} //End set_woomelly_shipping_local_pick_up_field()

		/**
		 * get_woomelly_shipping_free_shipping_field.
		 *
		 * @return bool
		 */
		public function get_woomelly_shipping_free_shipping_field () {
			return boolval($this->woomelly_shipping_free_shipping_field);
		} //End get_woomelly_shipping_free_shipping_field()

		/**
		 * set_woomelly_shipping_free_shipping_field.
		 *
		 * @return void
		 */
		public function set_woomelly_shipping_free_shipping_field ( $value ) {
			$this->woomelly_shipping_free_shipping_field = boolval( $value );			
		} //End set_woomelly_shipping_free_shipping_field()

		/**
		 * get_woomelly_shipping_dimensions_field.
		 *
		 * @return bool
		 */
		public function get_woomelly_shipping_dimensions_field () {
			return boolval($this->woomelly_shipping_dimensions_field);
		} //End get_woomelly_shipping_dimensions_field()

		/**
		 * set_woomelly_shipping_dimensions_field.
		 *
		 * @return void
		 */
		public function set_woomelly_shipping_dimensions_field ( $value ) {
			$this->woomelly_shipping_dimensions_field = boolval( $value );			
		} //End set_woomelly_shipping_dimensions_field()

		/**
		 * get_woomelly_title_field.
		 *
		 * @return string
		 */
		public function get_woomelly_title_field () {
			if ( $this->woomelly_title_field == '' ) {
				$this->woomelly_title_field = '{name}';
			}
			return $this->woomelly_title_field;
		} //End get_woomelly_title_field()

		/**
		 * set_woomelly_title_field.
		 *
		 * @return string
		 */
		public function set_woomelly_title_field ( $value ) {
			$value = trim($value);
			if ( $value == "" ) {
				$value = '{name}';
			}
			$this->woomelly_title_field = $value;
		} //End set_woomelly_title_field()

		/**
		 * get_woomelly_status_field.
		 *
		 * @return string
		 */
		public function get_woomelly_status_field () {
			return $this->woomelly_status_field;
		} //End get_woomelly_status_field()

		/**
		 * set_woomelly_status_field.
		 *
		 * @return void
		 */
		public function set_woomelly_status_field ( $value ) {
			$this->woomelly_status_field = trim($value);			
		} //End set_woomelly_status_field()

		/**
		 * get_woomelly_official_store_id_field.
		 *
		 * @return string
		 */
		public function get_woomelly_official_store_id_field () {
			return $this->woomelly_official_store_id_field;
		} //End get_woomelly_official_store_id_field()

		/**
		 * set_woomelly_official_store_id_field.
		 *
		 * @return void
		 */
		public function set_woomelly_official_store_id_field ( $value ) {
			$this->woomelly_official_store_id_field = trim($value);			
		} //End set_woomelly_official_store_id_field()

		/**
		 * get_woomelly_seller_custom_field.
		 *
		 * @return string
		 */
		public function get_woomelly_seller_custom_field () {
			if ( $this->woomelly_seller_custom_field == '' ) {
				$this->woomelly_seller_custom_field = '{sku}';
			}
			return $this->woomelly_seller_custom_field;
		} //End get_woomelly_seller_custom_field()

		/**
		 * set_woomelly_seller_custom_field.
		 *
		 * @return void
		 */
		public function set_woomelly_seller_custom_field ( $value ) {
			$value = trim($value);
			if ( $value == "" ) {
				$value = '{sku}';
			}			
			$this->woomelly_seller_custom_field = $value;			
		} //End set_woomelly_seller_custom_field()

		/**
		 * get_woomelly_video_id_field.
		 *
		 * @return string
		 */
		public function get_woomelly_video_id_field () {
			return $this->woomelly_video_id_field;
		} //End get_woomelly_video_id_field()

		/**
		 * set_woomelly_video_id_field.
		 *
		 * @return void
		 */
		public function set_woomelly_video_id_field ( $value ) {
			$this->woomelly_video_id_field = trim($value);			
		} //End set_woomelly_video_id_field()

		/**
		 * get_woomelly_warranty_field.
		 *
		 * @return string
		 */
		public function get_woomelly_warranty_field () {
			return $this->woomelly_warranty_field;
		} //End get_woomelly_warranty_field()

		/**
		 * set_woomelly_warranty_field.
		 *
		 * @return void
		 */
		public function set_woomelly_warranty_field ( $value ) {
			$this->woomelly_warranty_field = trim($value);			
		} //End set_woomelly_warranty_field()

		/**
		 * get_woomelly_price_field.
		 *
		 * @return string
		 */
		public function get_woomelly_price_field () {
			return $this->woomelly_price_field;
		} //End get_woomelly_price_field()

		/**
		 * get_woomelly_price_product.
		 *
		 * @return float
		 */
		public function get_woomelly_price_product ( $price_product ) {
			$woomelly_price_field_array = "";
			$woomelly_price_field_array = $this->woomelly_price_field;
			if ( $woomelly_price_field_array != "" ) {
				$woomelly_price_field_array = explode('::', $woomelly_price_field_array);
				if ( !empty($woomelly_price_field_array) && isset($woomelly_price_field_array[0]) && isset($woomelly_price_field_array[1]) && isset($woomelly_price_field_array[2]) ) {
					$woomelly_price_one_field = $woomelly_price_field_array[0];
					$woomelly_price_two_field = floatval($woomelly_price_field_array[1]);
					$woomelly_price_three_field = $woomelly_price_field_array[2];
					switch ( $woomelly_price_one_field ) {
						case '+':
							if ( $woomelly_price_three_field == '%' ) {
								$price_product = $price_product + ( ($price_product*$woomelly_price_two_field) / 100 );
							} else {
								$price_product = $price_product + $woomelly_price_two_field;
							}
						break;
						case '-':
							if ( $woomelly_price_three_field == '%' ) {
								$price_product = $price_product - ( ($price_product*$woomelly_price_two_field) / 100 );
							} else {
								$price_product = $price_product - $woomelly_price_two_field;
							}
						break;
						case '/':
							if ( $woomelly_price_three_field == '%' ) {
								$price_product = $price_product / ( ($price_product*$woomelly_price_two_field) / 100 );
							} else {
								$price_product = $price_product / $woomelly_price_two_field;
							}
						break;
						case '*':
							if ( $woomelly_price_three_field == '%' ) {
								$price_product = $price_product * ( ($price_product*$woomelly_price_two_field) / 100 );
							} else {
								$price_product = $price_product * $woomelly_price_two_field;
							}
						break;
					}
				}
			}
			$price_product = round( wc_format_decimal( $price_product ), 2 );
			return $price_product;
		} //End get_woomelly_price_product()		

		/**
		 * set_woomelly_price_field.
		 *
		 * @return void
		 */
		public function set_woomelly_price_field ( $value ) {
			$this->woomelly_price_field = trim($value);			
		} //End set_woomelly_price_field()

		/**
		 * get_woomelly_stock_field.
		 *
		 * @return string
		 */
		public function get_woomelly_stock_field () {
			return $this->woomelly_stock_field;
		} //End get_woomelly_stock_field()

		/**
		 * get_woomelly_stock_product.
		 *
		 * @return int
		 */
		public function get_woomelly_stock_product ( $_product, $stock_quantity = false ) {
			$woomelly_stock_field_array = $this->woomelly_stock_field;
			$woomelly_stock_field = 0;
			if ( $_product->managing_stock() ) {
				if ( !$stock_quantity ) {
					$woomelly_stock_field = $_product->get_stock_quantity();
				} else {
					$woomelly_stock_field = $stock_quantity;
				}
			} else {
				if ( $_product->is_in_stock() ) {
					$woomelly_stock_field = 1;
				}
			}
			if ( $woomelly_stock_field < 0 ) {
				$woomelly_stock_field = 0;
			}
			if ( $woomelly_stock_field_array != "" ) {
				if 	( !empty($woomelly_stock_field_array) && isset($woomelly_stock_field_array[0]) && isset($woomelly_stock_field_array[1]) && isset($woomelly_stock_field_array[2]) ) {
					$woomelly_stock_field_array = explode('::', $woomelly_stock_field_array);
					$woomelly_stock_one_field = $woomelly_stock_field_array[0];
					$woomelly_stock_two_field = absint($woomelly_stock_field_array[1]);
					$woomelly_stock_three_field = $woomelly_stock_field_array[2];
					switch ( $woomelly_stock_one_field ) {
						case '+':
							if ( $woomelly_stock_three_field == '%' ) {
								$woomelly_stock_field = $woomelly_stock_field + ( ($woomelly_stock_field*$woomelly_stock_two_field) / 100 );
							} else {
								$woomelly_stock_field = $woomelly_stock_field + $woomelly_stock_two_field;
							}
						break;
						case '-':
							if ( $woomelly_stock_three_field == '%' ) {
								$woomelly_stock_field = $woomelly_stock_field - ( ($woomelly_stock_field*$woomelly_stock_two_field) / 100 );
							} else {
								$woomelly_stock_field = $woomelly_stock_field - $woomelly_stock_two_field;
							}
						break;
						case '/':
							if ( $woomelly_stock_three_field == '%' ) {
								$woomelly_stock_field = $woomelly_stock_field / ( ($woomelly_stock_field*$woomelly_stock_two_field) / 100 );
							} else {
								$woomelly_stock_field = $woomelly_stock_field / $woomelly_stock_two_field;
							}
						break;
						case '*':
							if ( $woomelly_stock_three_field == '%' ) {
								$woomelly_stock_field = $woomelly_stock_field * ( ($woomelly_stock_field*$woomelly_stock_two_field) / 100 );
							} else {
								$woomelly_stock_field = $woomelly_stock_field * $woomelly_stock_two_field;
							}
						break;
					}
				}
			}
			$woomelly_stock_field = absint($woomelly_stock_field);
			return $woomelly_stock_field;
		} //End get_woomelly_stock_product()

		/**
		 * set_woomelly_stock_field.
		 *
		 * @return void
		 */
		public function set_woomelly_stock_field ( $value ) {
			$this->woomelly_stock_field = trim($value);			
		} //End set_woomelly_stock_field()

		/**
		 * get_woomelly_custom_shipping_cost_field.
		 *
		 * @return array
		 */
		public function get_woomelly_custom_shipping_cost_field () {
			return unserialize( $this->woomelly_custom_shipping_cost_field );
		} //End get_woomelly_custom_shipping_cost_field()

		/**
		 * set_woomelly_custom_shipping_cost_field.
		 *
		 * @return void
		 */
		public function set_woomelly_custom_shipping_cost_field ( $value ) {
			$this->woomelly_custom_shipping_cost_field = serialize( $value );			
		} //End set_woomelly_custom_shipping_cost_field()

		/**
		 * get_woomelly_location_country_field.
		 *
		 * @return string
		 */
		public function get_woomelly_location_country_field () {
			return $this->woomelly_location_country_field;
		} //End get_woomelly_location_country_field()
		
		/**
		 * set_woomelly_location_country_field.
		 *
		 * @return void
		 */
		public function set_woomelly_location_country_field ( $value ) {
			$this->woomelly_location_country_field = trim( $value );			
		} //End set_woomelly_location_country_field()

		/**
		 * get_woomelly_location_state_field.
		 *
		 * @return string
		 */		
		public function get_woomelly_location_state_field () {
			return $this->woomelly_location_state_field;
		} //End get_woomelly_location_state_field()

		/**
		 * set_woomelly_location_state_field.
		 *
		 * @return void
		 */	
		public function set_woomelly_location_state_field ( $value ) {
			$this->woomelly_location_state_field = trim( $value );			
		} //End set_woomelly_location_state_field()

		/**
		 * get_woomelly_location_city_field.
		 *
		 * @return string
		 */			
		public function get_woomelly_location_city_field () {
			return $this->woomelly_location_city_field;
		} //End get_woomelly_location_city_field()

		/**
		 * set_woomelly_location_city_field.
		 *
		 * @return void
		 */				
		public function set_woomelly_location_city_field ( $value ) {
			$this->woomelly_location_city_field = trim( $value );			
		} //End set_woomelly_location_city_field()

		/**
		 * get_woomelly_price_variations_field.
		 *
		 * @return string
		 */		
		public function get_woomelly_price_variations_field () {
			return $this->woomelly_price_variations_field;
		} //End get_woomelly_price_variations_field()
		
		/**
		 * set_woomelly_price_variations_field.
		 *
		 * @return void
		 */				
		public function set_woomelly_price_variations_field ( $value ) {
			$this->woomelly_price_variations_field = trim( $value );			
		} //End set_woomelly_price_variations_field()

		/**
		 * get_woomelly_separate_variations_field.
		 *
		 * @return string
		 */		
		public function get_woomelly_separate_variations_field () {
			if ( $this->woomelly_separate_variations_field == "" ) {
				$this->woomelly_separate_variations_field = 'inactive';
			}
			return $this->woomelly_separate_variations_field;
		} //End get_woomelly_separate_variations_field()
		
		/**
		 * set_woomelly_separate_variations_field.
		 *
		 * @return void
		 */				
		public function set_woomelly_separate_variations_field ( $value ) {
			$this->woomelly_separate_variations_field = trim( $value );
		} //End set_woomelly_separate_variations_field()

		/**
		 * save.
		 *
		 * @return void
		 */	
		public function save () {
        	global $wpdb;
        	$info_ok = null;

        	if ( $this->get_id() > 0 ) {

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_name_template'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_name_template_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_name_template' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_name_template', 'templatesync_value' => $this->woomelly_name_template_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_category'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_category_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_category' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_category', 'templatesync_value' => $this->woomelly_category_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_category_name'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_category_name_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_category_name' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_category_name', 'templatesync_value' => $this->woomelly_category_name_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );	

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_buying_mode'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_buying_mode_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_buying_mode' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_buying_mode', 'templatesync_value' => $this->woomelly_buying_mode_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );	

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_listing_type_id'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_listing_type_id_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_listing_type_id' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_listing_type_id', 'templatesync_value' => $this->woomelly_listing_type_id_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_condition'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_condition_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_condition' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_condition', 'templatesync_value' => $this->woomelly_condition_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_accepts_mercadopago'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_accepts_mercadopago_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_accepts_mercadopago' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_accepts_mercadopago', 'templatesync_value' => $this->woomelly_accepts_mercadopago_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_shipping_mode'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_shipping_mode_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_shipping_mode' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_shipping_mode', 'templatesync_value' => $this->woomelly_shipping_mode_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_shipping_accepted_methods'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_shipping_accepted_methods_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_shipping_accepted_methods' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_shipping_accepted_methods', 'templatesync_value' => $this->woomelly_shipping_accepted_methods_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_shipping_local_pick_up'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_shipping_local_pick_up_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_shipping_local_pick_up' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_shipping_local_pick_up', 'templatesync_value' => $this->woomelly_shipping_local_pick_up_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_shipping_free_shipping'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_shipping_free_shipping_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_shipping_free_shipping' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_shipping_free_shipping', 'templatesync_value' => $this->woomelly_shipping_free_shipping_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_shipping_dimensions'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_shipping_dimensions_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_shipping_dimensions' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_shipping_dimensions', 'templatesync_value' => $this->woomelly_shipping_dimensions_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_status'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_status_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_status' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_status', 'templatesync_value' => $this->woomelly_status_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_title'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_title_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_title' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_title', 'templatesync_value' => $this->woomelly_title_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_official_store_id'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_official_store_id_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_official_store_id' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_official_store_id', 'templatesync_value' => $this->woomelly_official_store_id_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_seller_custom'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_seller_custom_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_seller_custom' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_seller_custom', 'templatesync_value' => $this->woomelly_seller_custom_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_video_id'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_video_id_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_video_id' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_video_id', 'templatesync_value' => $this->woomelly_video_id_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_warranty'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_warranty_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_warranty' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_warranty', 'templatesync_value' => $this->woomelly_warranty_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_price'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_price_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_price' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_price', 'templatesync_value' => $this->woomelly_price_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_stock'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_stock_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_stock' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_stock', 'templatesync_value' => $this->woomelly_stock_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_custom_shipping_cost'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_custom_shipping_cost_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_custom_shipping_cost' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_custom_shipping_cost', 'templatesync_value' => $this->woomelly_custom_shipping_cost_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_location_country'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_location_country_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_location_country' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_location_country', 'templatesync_value' => $this->woomelly_location_country_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_location_state'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_location_state_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_location_state' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_location_state', 'templatesync_value' => $this->woomelly_location_state_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_location_city'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_location_city_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_location_city' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_location_city', 'templatesync_value' => $this->woomelly_location_city_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_price_variations'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_price_variations_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_price_variations' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_price_variations', 'templatesync_value' => $this->woomelly_price_variations_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				$info_ok = $wpdb->get_row( "SELECT templatesync_id FROM ".$wpdb->prefix . "wm_templatesync_meta WHERE templatesync_id = '".$this->get_id()."' AND templatesync_key = '_wm_separate_variations'" );
				if ( is_object($info_ok) ) {
					$results = $wpdb->update(
						$wpdb->prefix . 'wm_templatesync_meta', 
						array( 'templatesync_value' => $this->woomelly_separate_variations_field ),
						array( 'templatesync_id' => $this->get_id(), 'templatesync_key' => '_wm_separate_variations' ), 
						array( '%s' ),
						array( '%d', '%s' ) 
					);
				} else {
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_separate_variations', 'templatesync_value' => $this->woomelly_separate_variations_field, 'templatesync_id' => $this->get_id() ),
		            	array( '%s', '%s', '%d' )
		        	);
				}
				unset( $info_ok );

				return $this->id;
        	} else {
	        	$results = $wpdb->insert(
	            	$wpdb->prefix . 'wm_templatesync',
	            	array( 'date' => current_time( 'mysql' ) ),
	            	array( '%s' )
	        	);
	        	if ( !is_wp_error( $results ) ) {
	        		$wm_templatesync = $wpdb->insert_id;
		        	unset( $results );
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_name_template', 'templatesync_value' => $this->woomelly_name_template_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_category', 'templatesync_value' => $this->woomelly_category_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_category_name', 'templatesync_value' => $this->woomelly_category_name_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_buying_mode', 'templatesync_value' => $this->woomelly_buying_mode_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_listing_type_id', 'templatesync_value' => $this->woomelly_listing_type_id_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_condition', 'templatesync_value' => $this->woomelly_condition_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_accepts_mercadopago', 'templatesync_value' => $this->woomelly_accepts_mercadopago_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_shipping_mode', 'templatesync_value' => $this->woomelly_shipping_mode_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_shipping_accepted_methods', 'templatesync_value' => $this->woomelly_shipping_accepted_methods_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_shipping_local_pick_up', 'templatesync_value' => $this->woomelly_shipping_local_pick_up_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_shipping_free_shipping', 'templatesync_value' => $this->woomelly_shipping_free_shipping_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_shipping_dimensions', 'templatesync_value' => $this->woomelly_shipping_dimensions_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_status', 'templatesync_value' => $this->woomelly_status_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_title', 'templatesync_value' => $this->woomelly_title_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_official_store_id', 'templatesync_value' => $this->woomelly_official_store_id_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_seller_custom', 'templatesync_value' => $this->woomelly_seller_custom_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_video_id', 'templatesync_value' => $this->woomelly_video_id_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_warranty', 'templatesync_value' => $this->woomelly_warranty_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_price', 'templatesync_value' => $this->woomelly_price_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_stock', 'templatesync_value' => $this->woomelly_stock_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_custom_shipping_cost', 'templatesync_value' => $this->woomelly_custom_shipping_cost_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_location_country', 'templatesync_value' => $this->woomelly_location_country_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_location_state', 'templatesync_value' => $this->woomelly_location_state_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_location_city', 'templatesync_value' => $this->woomelly_location_city_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_price_variations', 'templatesync_value' => $this->woomelly_price_variations_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);
		        	$results = $wpdb->insert(
		            	$wpdb->prefix . 'wm_templatesync_meta',
		            	array( 'templatesync_key' => '_wm_separate_variations', 'templatesync_value' => $this->woomelly_separate_variations_field, 'templatesync_id' => $wm_templatesync ),
		            	array( '%s', '%s', '%d' )
		        	);

		        	return $wm_templatesync;
	        	} else {
	        		return false;
	        	}
        	}
		} //End save()

		/**
		 * delete.
		 *
		 * @return void
		 */	
		public static function delete ( $id ) {
            global $wpdb;
            if ( absint($id ) > 0 ) {
	            $wpdb->delete(
	                $wpdb->prefix . 'wm_templatesync_meta',
	                array(
	                    'templatesync_id' => $id,
	                )
	            );
	            $wpdb->delete(
	                $wpdb->prefix . 'wm_templatesync',
	                array(
	                    'id' => $id,
	                )
	            );
            }
            wm_delete_templatesync_product( $id );
		}  //End delete()

		/**
		 * get_products.
		 *
		 * @return array
		 */	
		public function get_products () {
            global $wpdb;
            $products = array();
            $all_products = array();

            $all_products = $wpdb->get_results( "SELECT DISTINCT A.ID FROM {$wpdb->posts} AS A INNER JOIN {$wpdb->postmeta} AS B ON A.ID=B.post_id WHERE A.post_type='product' AND A.post_status='publish' AND B.meta_key='_wm_template_sync_id' AND B.meta_value = '".$this->get_id()."';", OBJECT );
			if ( !empty($all_products) ) {
				foreach ( $all_products as $value ) {
					$products[] = $value->ID;
				}
			}

			return $products;
		}  //End get_products()
    }
}