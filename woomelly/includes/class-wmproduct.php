<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( !class_exists( 'WMProduct' ) ) {
    /**
     * WMProduct Class.
     */
    class WMProduct {
    	private $product_id; 
    	private $woomelly_template_sync_id;
    	private $woomelly_status_meli_field;
    	private $woomelly_sync_status_field;
    	private $woomelly_code_meli_field;
    	private $woomelly_sales_meli_field;
    	private $woomelly_duration_start_meli_field;
    	private $woomelly_duration_end_meli_field;
    	private $woomelly_expiration_time_meli_field;
    	private $woomelly_url_meli_field;
    	private $woomelly_thumbnail_meli_field;
    	private $woomelly_status_field;
    	private $woomelly_substatus_field;
    	private $woomelly_created_meli_field;
    	private $woomelly_updated_meli_field;
    	private $woomelly_updated_field;
    	private $woomelly_parent_item_id_field;
    	private $woomelly_parent_product_field;
    	private $woomelly_updated_user_field;
    	private $woomelly_created_version_field;
    	private $woomelly_custom_title_field;
    	private $woomelly_description_meli_field;
    	private $woomelly_attribute_field;
    	private $woomelly_variation_field;
    	private $woomelly_variation_dimentions_field;
    	private $woomelly_variation_extra_img_field;
    	private $woomelly_sync_problem;
    	private $woomelly_last_notification;

        /**
         * Default constructor.
         */    	
		public function __construct ( $id = 0 ) {
			$id = absint( $id );
			if ( $id > 0 ) {
				$this->product_id = $id;
				$this->woomelly_template_sync_id = get_post_meta( $id, '_wm_template_sync_id', true );
				$this->woomelly_status_meli_field = get_post_meta( $id, '_wm_status_meli', true );
				if ( metadata_exists( 'post', $id, '_wm_sync_status' ) ) {
					$this->woomelly_sync_status_field = get_post_meta( $id, '_wm_sync_status', true );
				} else {
					$this->woomelly_sync_status_field = true;
				}
				$this->woomelly_code_meli_field = get_post_meta( $id, '_wm_code_meli', true );
				$this->woomelly_sales_meli_field = get_post_meta( $id, '_wm_sales_meli', true );
				$this->woomelly_duration_start_meli_field = get_post_meta( $id, '_wm_duration_start_meli', true );
				$this->woomelly_duration_end_meli_field = get_post_meta( $id, '_wm_duration_end_meli', true );
				$this->woomelly_expiration_time_meli_field = get_post_meta( $id, '_wm_expiration_time_meli', true );
				$this->woomelly_url_meli_field = get_post_meta( $id, '_wm_url_meli', true );
				$this->woomelly_thumbnail_meli_field = get_post_meta( $id, '_wm_thumbnail_meli', true );
				$this->woomelly_status_field = get_post_meta( $id, '_wm_status', true );
				$this->woomelly_substatus_field = get_post_meta( $id, '_wm_substatus', true );
				$this->woomelly_created_meli_field = get_post_meta( $id, '_wm_created_meli', true );
				$this->woomelly_updated_meli_field = get_post_meta( $id, '_wm_updated_meli', true );
				$this->woomelly_updated_field = get_post_meta( $id, '_wm_updated', true );
				$this->woomelly_parent_item_id_field = get_post_meta( $id, '_wm_parent_item_id', true );
				$this->woomelly_parent_product_field = get_post_meta( $id, '_wm_parent_product_field', true );
				$this->woomelly_updated_user_field = get_post_meta( $id, '_wm_updated_user', true );
				$this->woomelly_created_version_field = get_post_meta( $id, '_wm_created_version', true );
				$this->woomelly_custom_title_field = get_post_meta( $id, '_wm_custom_title_field', true );
				$this->woomelly_description_meli_field = get_post_meta( $id, '_wm_description_meli', true );
				$this->woomelly_attribute_field = get_post_meta( $id, '_wm_attribute', true );
				$this->woomelly_variation_field = get_post_meta( $id, '_wm_variation', true );
				$this->woomelly_variation_dimentions_field = get_post_meta( $id, '_wm_variation_dimentions', true );
				$this->woomelly_variation_extra_img_field = get_post_meta( $id, '_wm_variation_extra_img', true );
				$this->woomelly_sync_problem = get_post_meta( $id, '_wm_sync_problem', true );
				$this->woomelly_last_notification = get_post_meta( $id, '_wm_last_notification', true );
			}
			return null;
		} //End __construct()

		public function __clone () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), Woomelly()->get_version() );
		} //End __clone()

		public function __wakeup () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), Woomelly()->get_version() );
		} //End __wakeup()	

		/**
		 * get_id.
		 *
		 * @return int
		 */
		public function get_id () {
			return $this->product_id;
		} //End get_id()

		/**
		 * set_product_id.
		 *
		 * @return void
		 */
		public function set_product_id ( $value ) {
			$this->product_id = absint($value);
		} //End set_product_id()		
		
		/**
		 * get_woomelly_template_sync_id.
		 *
		 * @return int
		 */	
		public function get_woomelly_template_sync_id () {
			return $this->woomelly_template_sync_id;
		} //End get_woomelly_template_sync_id()
		
		/**
		 * set_woomelly_template_sync_id.
		 *
		 * @return void
		 */			
		public function set_woomelly_template_sync_id ( $value ) {
			$value = trim( $value );
			if ( $value == "-1" || $value=="" ) {
				$this->woomelly_template_sync_id = "";
				update_post_meta( $this->product_id, '_wm_template_sync_id', '' );
			} else if ( absint( $value ) > 0 ) {
				$this->woomelly_template_sync_id = $value;
				update_post_meta( $this->product_id, '_wm_template_sync_id', $this->woomelly_template_sync_id );
			}
		} //End set_woomelly_template_sync_id()

		/**
		 * get_woomelly_status_meli_field.
		 *
		 * @return string
		 */				
		public function get_woomelly_status_meli_field () {
			return $this->woomelly_status_meli_field;
		} //End get_woomelly_status_meli_field()		
		
		/**
		 * set_woomelly_status_meli_field.
		 *
		 * @return void
		 */			
		public function set_woomelly_status_meli_field ( $value ) {
			$value = trim($value);
			if ( $value != "" ) {
				$this->woomelly_status_meli_field = $value;
				update_post_meta( $this->product_id, '_wm_status_meli', $this->woomelly_status_meli_field );
			}
		} //End set_woomelly_status_meli_field()
		
		/**
		 * get_woomelly_sync_status_field.
		 *
		 * @return bool
		 */		
		public function get_woomelly_sync_status_field () {
			return boolval($this->woomelly_sync_status_field);
		} //End get_woomelly_sync_status_field()

		/**
		 * get_woomelly_status_meli_product.
		 *
		 * @return bool
		 */		
		public static function get_woomelly_status_meli_product ( $product_id ) {
			$wm_status_meli = get_post_meta( $product_id, '_wm_status_meli', true );
			if ( $wm_status_meli == "" ) {
				return false;
			} else {
				return true;
			}
		} //End get_woomelly_status_meli_product()		
		
		/**
		 * set_woomelly_sync_status_field.
		 *
		 * @return void
		 */	
		public function set_woomelly_sync_status_field ( $value ) {
			$this->woomelly_sync_status_field = boolval(trim($value));
			update_post_meta( $this->product_id, '_wm_sync_status', $this->woomelly_sync_status_field );
		} //End set_woomelly_sync_status_field()
		
		/**
		 * get_woomelly_code_meli_field.
		 *
		 * @return string
		 */	
		public function get_woomelly_code_meli_field () {
			return $this->woomelly_code_meli_field;
		} //End get_woomelly_code_meli_field()
		
		/**
		 * set_woomelly_code_meli_field.
		 *
		 * @return void
		 */	
		public function set_woomelly_code_meli_field ( $value ) {
			$this->woomelly_code_meli_field = trim($value);
			update_post_meta( $this->product_id, '_wm_code_meli', $this->woomelly_code_meli_field );
		} //End set_woomelly_code_meli_field()
		
		/**
		 * get_woomelly_sales_meli_field.
		 *
		 * @return int
		 */
		public function get_woomelly_sales_meli_field () {
			return intval($this->woomelly_sales_meli_field);
		} //End get_woomelly_sales_meli_field()
		
		/**
		 * set_woomelly_sales_meli_field.
		 *
		 * @return void
		 */
		public function set_woomelly_sales_meli_field ( $value ) {
			$this->woomelly_sales_meli_field = trim($value);
			update_post_meta( $this->product_id, '_wm_sales_meli', $this->woomelly_sales_meli_field );
		} //End set_woomelly_sales_meli_field()
		
		/**
		 * get_woomelly_duration_start_meli_field.
		 *
		 * @return string
		 */
		public function get_woomelly_duration_start_meli_field () {
			return $this->woomelly_duration_start_meli_field;
		} //End get_woomelly_duration_start_meli_field()
		
		/**
		 * set_woomelly_duration_start_meli_field.
		 *
		 * @return void
		 */		
		public function set_woomelly_duration_start_meli_field ( $value ) {
			$this->woomelly_duration_start_meli_field = trim($value);
			update_post_meta( $this->product_id, '_wm_duration_start_meli', $this->woomelly_duration_start_meli_field );
		} //End set_woomelly_duration_start_meli_field()
		
		/**
		 * get_woomelly_duration_end_meli_field.
		 *
		 * @return string
		 */	
		public function get_woomelly_duration_end_meli_field () {
			return $this->woomelly_duration_end_meli_field;
		} //End get_woomelly_duration_end_meli_field()
		
		/**
		 * set_woomelly_duration_end_meli_field.
		 *
		 * @return void
		 */			
		public function set_woomelly_duration_end_meli_field ( $value ) {
			$this->woomelly_duration_end_meli_field = trim($value);
			update_post_meta( $this->product_id, '_wm_duration_end_meli', $this->woomelly_duration_end_meli_field );
		} //End set_woomelly_duration_end_meli_field()
		
		/**
		 * get_woomelly_expiration_time_meli_field.
		 *
		 * @return string
		 */		
		public function get_woomelly_expiration_time_meli_field () {
			return $this->woomelly_expiration_time_meli_field;
		} //End get_woomelly_duration_end_meli_field()
		
		/**
		 * set_woomelly_expiration_time_meli_field.
		 *
		 * @return void
		 */				
		public function set_woomelly_expiration_time_meli_field ( $value ) {
			$this->woomelly_expiration_time_meli_field = trim($value);
			update_post_meta( $this->product_id, '_wm_expiration_time_meli', $this->woomelly_expiration_time_meli_field );
		} //End set_woomelly_expiration_time_meli_field()
		
		/**
		 * get_woomelly_url_meli_field.
		 *
		 * @return string
		 */				
		public function get_woomelly_url_meli_field () {
			return $this->woomelly_url_meli_field;
		} //End get_woomelly_url_meli_field()
		
		/**
		 * set_woomelly_url_meli_field.
		 *
		 * @return void
		 */
		public function set_woomelly_url_meli_field ( $value ) {
			$this->woomelly_url_meli_field = trim($value);
			update_post_meta( $this->product_id, '_wm_url_meli', $this->woomelly_url_meli_field );
		} //End set_woomelly_url_meli_field()

		/**
		 * get_woomelly_thumbnail_meli_field.
		 *
		 * @return string
		 */		
		public function get_woomelly_thumbnail_meli_field () {
			return $this->woomelly_thumbnail_meli_field;
		} //End get_woomelly_thumbnail_meli_field()
		/**
		 * set_woomelly_thumbnail_meli_field.
		 *
		 * @return void
		 */		
		public function set_woomelly_thumbnail_meli_field ( $value ) {
			$this->woomelly_thumbnail_meli_field = trim($value);
			update_post_meta( $this->product_id, '_wm_thumbnail_meli', $this->woomelly_thumbnail_meli_field );
		} //End set_woomelly_thumbnail_meli_field()
		/**
		 * get_woomelly_status_field.
		 *
		 * @return string
		 */		
		public function get_woomelly_status_field () {
			if ( $this->woomelly_status_field == "" ) {
				$this->woomelly_status_field = 'active';
			}
			return $this->woomelly_status_field;
		} //End get_woomelly_status_field()
		/**
		 * get_woomelly_status_name_field.
		 *
		 * @return string
		 */		
		public function get_woomelly_status_name_field () {
			$woomelly_status_field = '';
			switch ( $this->woomelly_status_field ) {
				case 'active':
					$woomelly_status_field = __("Active", "woomelly");
					break;
				case 'payment_required':
					$woomelly_status_field = __("Pending of Payment", "woomelly");
					break;
				case 'under_review':
					$woomelly_status_field = __("Under Review by Mercadolibre", "woomelly");
					break;
				case 'paused':
					$woomelly_status_field = __("Paused", "woomelly");
					break;
				case 'closed':
					$woomelly_status_field = __("Finished", "woomelly");
					break;
				case 'inactive':
					$woomelly_status_field = __("Inactive", "woomelly");
					break;
				case 'reclosed':
					$woomelly_status_field = __("Pending of Relist", "woomelly");
					break;
				default:
					$woomelly_status_field = __("Without connection", "woomelly");
					break;
			}
			return $woomelly_status_field;
		} //End get_woomelly_status_name_field()
		/**
		 * set_woomelly_status_field.
		 *
		 * @return void
		 */		
		public function set_woomelly_status_field ( $value ) {
			$this->woomelly_status_field = trim($value);
			update_post_meta( $this->product_id, '_wm_status', $this->woomelly_status_field );
		} //End set_woomelly_status_field()
		
		/**
		 * get_woomelly_substatus_field.
		 *
		 * @return array
		 */	
		public function get_woomelly_substatus_field () {
			if ( $this->woomelly_substatus_field == "" ) {
				return array();
			}
			return $this->woomelly_substatus_field;
		} //End get_woomelly_substatus_field()
		
		/**
		 * set_woomelly_substatus_field.
		 *
		 * @return void
		 */			
		public function set_woomelly_substatus_field ( $value ) {
			$this->woomelly_substatus_field = $value;
			update_post_meta( $this->product_id, '_wm_substatus', $this->woomelly_substatus_field );
		} //End set_woomelly_substatus_field()
		
		/**
		 * get_woomelly_created_meli_field.
		 *
		 * @return string
		 */			
		public function get_woomelly_created_meli_field () {
			return $this->woomelly_created_meli_field;
		} //End get_woomelly_created_meli_field()
		
		/**
		 * set_woomelly_created_meli_field.
		 *
		 * @return void
		 */			
		public function set_woomelly_created_meli_field ( $value ) {
			$this->woomelly_created_meli_field = trim($value);
			update_post_meta( $this->product_id, '_wm_created_meli', $this->woomelly_created_meli_field );
		} //End set_woomelly_created_meli_field()
		
		/**
		 * get_woomelly_updated_meli_field.
		 *
		 * @return string
		 */			
		public function get_woomelly_updated_meli_field () {
			return $this->woomelly_updated_meli_field;
		} //End get_woomelly_updated_meli_field()
		
		/**
		 * set_woomelly_updated_meli_field.
		 *
		 * @return void
		 */			
		public function set_woomelly_updated_meli_field ( $value ) {
			$this->woomelly_updated_meli_field = trim($value);
			update_post_meta( $this->product_id, '_wm_updated_meli', $this->woomelly_updated_meli_field );
		} //End set_woomelly_updated_meli_field()
		
		/**
		 * get_woomelly_updated_field.
		 *
		 * @return string
		 */			
		public function get_woomelly_updated_field () {
			return $this->woomelly_updated_field;
		} //End get_woomelly_updated_field()
		
		/**
		 * set_woomelly_updated_field.
		 *
		 * @return void
		 */			
		public function set_woomelly_updated_field ( $value ) {
			$this->woomelly_updated_field = trim($value);
			update_post_meta( $this->product_id, '_wm_updated', $this->woomelly_updated_field );
		} //End set_woomelly_updated_field()
		
		/**
		 * get_woomelly_parent_item_id_field.
		 *
		 * @return string
		 */			
		public function get_woomelly_parent_item_id_field () {
			return $this->woomelly_parent_item_id_field;
		} //End get_woomelly_parent_item_id_field()
		
		/**
		 * set_woomelly_parent_item_id_field.
		 *
		 * @return void
		 */			
		public function set_woomelly_parent_item_id_field ( $value ) {
			$this->woomelly_parent_item_id_field = trim($value);
			update_post_meta( $this->product_id, '_wm_parent_item_id', $this->woomelly_parent_item_id_field );
		} //End set_woomelly_parent_item_id_field()
		
		/**
		 * get_woomelly_parent_product_field.
		 *
		 * @return string
		 */			
		public function get_woomelly_parent_product_field () {
			return $this->woomelly_parent_product_field;
		} //End get_woomelly_parent_product_field()
		
		/**
		 * set_woomelly_parent_product_field.
		 *
		 * @return void
		 */			
		public function set_woomelly_parent_product_field ( $value ) {
			$this->woomelly_parent_product_field = trim($value);
			update_post_meta( $this->product_id, '_wm_parent_product_field', $this->woomelly_parent_product_field );
		} //End set_woomelly_parent_product_field()

		/**
		 * get_woomelly_updated_user_field.
		 *
		 * @return string
		 */			
		public function get_woomelly_updated_user_field () {
			return $this->woomelly_updated_user_field;
		} //End get_woomelly_updated_user_field()
		
		/**
		 * set_woomelly_updated_user_field.
		 *
		 * @return void
		 */			
		public function set_woomelly_updated_user_field ( $value ) {
			$this->woomelly_updated_user_field = trim($value);
			update_post_meta( $this->product_id, '_wm_updated_user', $this->woomelly_updated_user_field );
		} //End set_woomelly_updated_user_field()
		
		/**
		 * get_woomelly_created_version_field.
		 *
		 * @return string
		 */			
		public function get_woomelly_created_version_field () {
			return $this->woomelly_created_version_field;
		} //End get_woomelly_created_version_field()
		
		/**
		 * set_woomelly_created_version_field.
		 *
		 * @return void
		 */			
		public function set_woomelly_created_version_field ( $value ) {
			$this->woomelly_created_version_field = trim($value);
			update_post_meta( $this->product_id, '_wm_created_version', $this->woomelly_created_version_field );
		} //End set_woomelly_created_version_field()
		
		/**
		 * get_woomelly_description_meli_field.
		 *
		 * @return string
		 */			
		public function get_woomelly_description_meli_field () {
			return $this->woomelly_description_meli_field;
		} //End get_woomelly_description_meli_field()
		
		/**
		 * set_woomelly_description_meli_field.
		 *
		 * @return void
		 */			
		public function set_woomelly_description_meli_field ( $value ) {
			$this->woomelly_description_meli_field = trim($value);
			update_post_meta( $this->product_id, '_wm_description_meli', $this->woomelly_description_meli_field );
		} //End set_woomelly_description_meli_field()

		/**
		 * get_woomelly_custom_title_field.
		 *
		 * @return string
		 */			
		public function get_woomelly_custom_title_field () {
			return $this->woomelly_custom_title_field;
		} //End get_woomelly_custom_title_field()
		
		/**
		 * set_woomelly_custom_title_field.
		 *
		 * @return void
		 */			
		public function set_woomelly_custom_title_field ( $value ) {
			$this->woomelly_custom_title_field = trim($value);
			update_post_meta( $this->product_id, '_wm_custom_title_field', $this->woomelly_custom_title_field );
		} //End set_woomelly_custom_title_field()

		/**
		 * get_woomelly_attribute_field.
		 *
		 * @return array
		 */		
		public function get_woomelly_attribute_field () {
			$attribute_array = array();
			if ( !empty($this->woomelly_attribute_field) ) {
				foreach ( $this->woomelly_attribute_field as $key => $value ) {
					$data = explode('::', $value);
					if ( is_array($data) ) {
						if ( $data[0] == 'value_name' && $data[1] !="" ) {
							$attribute_array[] = array( 'id' => $key, 'value_name' => $data[1] );
						} else if ( $data[0] == 'value_id' && $data[1] !="" ) {
							$attribute_array[] = array( 'id' => $key, 'value_id' => $data[1] );
						} else if ( $data[0] == 'number_unit' && $data[1] !="" && $data[2] !="" ) {
							$attribute_array[] = array( 'id' => $key, 'value_name' => $data[1].$data[2], 'value_id' => $data[1].$data[2] );
						}
					}
					unset( $data );
				}
			}
			return $attribute_array;
		} //End get_woomelly_attribute_field()
		
		/**
		 * set_woomelly_attribute_field.
		 *
		 * @return void
		 */			
		public function set_woomelly_attribute_field ( $value ) {
			$this->woomelly_attribute_field = $value;
			update_post_meta( $this->product_id, '_wm_attribute', $this->woomelly_attribute_field );
		} //End set_woomelly_attribute_field()
		
		/**
		 * get_woomelly_variation_field.
		 *
		 * @return array
		 */	
		public function get_woomelly_variation_field () {
			if ( $this->woomelly_variation_field == "" ) {
				return array();
			}	
			return $this->woomelly_variation_field;
		} //End get_woomelly_variation_field()

		/**
		 * get_woomelly_clean_variation_field.
		 *
		 * @return array
		 */	
		public function get_woomelly_clean_variation_field () {
			$woomelly_variation_field = array();
			if ( $this->woomelly_variation_field == "" ) {
				return array();
			}
			if ( !empty($this->woomelly_variation_field) ) {
				foreach ( $this->woomelly_variation_field as $value ) {
					unset( $value['attribute_name'] );
					unset( $value['attribute_value'] );
					$woomelly_variation_field[] = $value;
				}
				return $woomelly_variation_field;
			}
			return $this->woomelly_variation_field;
		} //End get_woomelly_clean_variation_field()
		
		/**
		 * set_woomelly_variation_field.
		 *
		 * @return void
		 */			
		public function set_woomelly_variation_field ( $value ) {
			$this->woomelly_variation_field = $value;
			update_post_meta( $this->product_id, '_wm_variation', $this->woomelly_variation_field );
		} //End set_woomelly_variation_field()
		
		/**
		 * get_woomelly_variation_dimentions_field.
		 *
		 * @return bool
		 */	
		public function get_woomelly_variation_dimentions_field () {
			return boolval($this->woomelly_variation_dimentions_field);
		} //End get_woomelly_variation_dimentions_field()
		
		/**
		 * set_woomelly_variation_dimentions_field.
		 *
		 * @return void
		 */
		public function set_woomelly_variation_dimentions_field ( $value ) {
			$this->woomelly_variation_dimentions_field = boolval(trim($value));
			update_post_meta( $this->product_id, '_wm_variation_dimentions', $this->woomelly_variation_dimentions_field );
		} //End set_woomelly_variation_dimentions_field()
		
		/**
		 * get_woomelly_variation_extra_img_field.
		 *
		 * @return array
		 */
		public function get_woomelly_variation_extra_img_field () {
			if ( $this->woomelly_variation_extra_img_field == "" ) {
				return array();
			}
			return $this->woomelly_variation_extra_img_field;
		} //End get_woomelly_variation_extra_img_field()
		
		/**
		 * set_woomelly_variation_extra_img_field.
		 *
		 * @return void
		 */		
		public function set_woomelly_variation_extra_img_field ( $value ) {
			$images = array();
			if ( is_array($value) && !empty($value) ) {
				foreach ( $value as $v ) {
					if ( $v != "" ) {
						$images[] = trim($v);
					}
				}
				update_post_meta( $this->product_id, '_wm_variation_extra_img', $images );
			} else {
				update_post_meta( $this->product_id, '_wm_variation_extra_img', array() );
			}
		} //End set_woomelly_variation_extra_img_field()
		
		/**
		 * get_woomelly_sync_problem.
		 *
		 * @return bool
		 */
		public function get_woomelly_sync_problem () {
			return boolval($this->woomelly_sync_problem);
		} //End get_woomelly_sync_problem()
		
		/**
		 * set_woomelly_sync_problem.
		 *
		 * @return void
		 */		
		public function set_woomelly_sync_problem ( $value ) {
			$this->woomelly_sync_problem = boolval(trim($value));
			update_post_meta( $this->product_id, '_wm_sync_problem', $this->woomelly_sync_problem );
		} //End set_woomelly_sync_problem()

		/**
		 * get_woomelly_last_notification.
		 *
		 * @return bool
		 */
		public function get_woomelly_last_notification () {
			if ( $this->woomelly_last_notification != "" ) {
				return strtotime( '+3 MINUTE', $this->woomelly_last_notification );
			}
			return $this->woomelly_last_notification;
		} //End get_woomelly_last_notification()
		
		/**
		 * set_woomelly_last_notification.
		 *
		 * @return void
		 */		
		public function set_woomelly_last_notification ( $value ) {
			$this->woomelly_last_notification = trim($value);
			$this->woomelly_last_notification = strtotime($this->woomelly_last_notification);
			update_post_meta( $this->product_id, '_wm_last_notification', $this->woomelly_last_notification );
		} //End set_woomelly_last_notification()		

		/**
		 * get_value_attribute_field.
		 *
		 * @return string | array
		 */	
		public function get_value_attribute_field ( $value ) { 
			if ( !is_array($this->woomelly_attribute_field) ) {
				return "";
			}
			
			if ( isset($this->woomelly_attribute_field[$value]) ) {
				$data = explode('::', $this->woomelly_attribute_field[$value]);
				if ( count($data) > 2 ) {
					return array($data[1], $data[2]);
				} else {
					return $data[1];
				}
			}

			return "";
		} //End get_value_attribute_field()

		/**
		 * update_status_bulk_actions.
		 *
		 * @return void
		 */			
		public static function update_status_bulk_actions ( $product_id, $status ) {
			switch ($status) {
				case 'wm_without_connect':
					update_post_meta( $product_id, '_wm_sync_status', '' );					
					break;
				case 'wm_connect':
					update_post_meta( $product_id, '_wm_sync_status', true );
					break;
				case 'wm_active':
					update_post_meta( $product_id, '_wm_status', 'active' );
					break;
				case 'wm_paused':
					update_post_meta( $product_id, '_wm_status', 'paused' );
					break;
				case 'wm_finish':
					update_post_meta( $product_id, '_wm_status', 'closed' );
					break;
				case 'wm_republish':
					update_post_meta( $product_id, '_wm_status', 'reclosed' );
					break;
			}
		} //End update_status_bulk_actions()

		/**
		 * reset.
		 *
		 * @return void
		 */			
		public static function reset ( $product_id ) {
			update_post_meta( $product_id, '_wm_template_sync_id', "" );
			update_post_meta( $product_id, '_wm_status_meli', "" );
			update_post_meta( $product_id, '_wm_sync_status', true );
			update_post_meta( $product_id, '_wm_code_meli', "" );
			update_post_meta( $product_id, '_wm_sales_meli', "" );
			update_post_meta( $product_id, '_wm_duration_start_meli', "" );
			update_post_meta( $product_id, '_wm_duration_end_meli', "" );
			update_post_meta( $product_id, '_wm_expiration_time_meli', "" );
			update_post_meta( $product_id, '_wm_url_meli', "" );
			update_post_meta( $product_id, '_wm_thumbnail_meli', "" );
			update_post_meta( $product_id, '_wm_status', "" );
			update_post_meta( $product_id, '_wm_substatus', "" );
			update_post_meta( $product_id, '_wm_created_meli', "" );
			update_post_meta( $product_id, '_wm_updated_meli', "" );
			update_post_meta( $product_id, '_wm_updated', "" );
			update_post_meta( $product_id, '_wm_parent_item_id', "" );
			update_post_meta( $product_id, '_wm_parent_product_field', "" );
			update_post_meta( $product_id, '_wm_updated_user', "" );
			update_post_meta( $product_id, '_wm_created_version', "" );
			update_post_meta( $product_id, '_wm_custom_title_field', "" );
			update_post_meta( $product_id, '_wm_description_meli', "" );
			update_post_meta( $product_id, '_wm_attribute', "" );
			update_post_meta( $product_id, '_wm_variation', "" );
			update_post_meta( $product_id, '_wm_variation_dimentions', "" );
			update_post_meta( $product_id, '_wm_variation_extra_img', "" );
			update_post_meta( $product_id, '_wm_sync_problem', false );
			update_post_meta( $product_id, '_wm_last_notification', "" );			
			$wm_product = new WMProduct( $product_id );
			$product_children_sync = $wm_product->product_children_sync();
			if ( !empty($product_children_sync) ) {
				foreach ( $product_children_sync as $key => $value ) {
					update_post_meta( $key, '_wm_template_sync_id', "" );
					update_post_meta( $key, '_wm_status_meli', "" );
					update_post_meta( $key, '_wm_sync_status', true );
					update_post_meta( $key, '_wm_code_meli', "" );
					update_post_meta( $key, '_wm_sales_meli', "" );
					update_post_meta( $key, '_wm_duration_start_meli', "" );
					update_post_meta( $key, '_wm_duration_end_meli', "" );
					update_post_meta( $key, '_wm_expiration_time_meli', "" );
					update_post_meta( $key, '_wm_url_meli', "" );
					update_post_meta( $key, '_wm_thumbnail_meli', "" );
					update_post_meta( $key, '_wm_status', "" );
					update_post_meta( $key, '_wm_substatus', "" );
					update_post_meta( $key, '_wm_created_meli', "" );
					update_post_meta( $key, '_wm_updated_meli', "" );
					update_post_meta( $key, '_wm_updated', "" );
					update_post_meta( $key, '_wm_parent_item_id', "" );
					update_post_meta( $key, '_wm_parent_product_field', "" );
					update_post_meta( $key, '_wm_updated_user', "" );
					update_post_meta( $key, '_wm_created_version', "" );
					update_post_meta( $key, '_wm_custom_title_field', "" );
					update_post_meta( $key, '_wm_description_meli', "" );
					update_post_meta( $key, '_wm_attribute', "" );
					update_post_meta( $key, '_wm_variation', "" );
					update_post_meta( $key, '_wm_variation_dimentions', "" );
					update_post_meta( $key, '_wm_variation_extra_img', "" );
					update_post_meta( $key, '_wm_sync_problem', false );
					update_post_meta( $key, '_wm_last_notification', "" );
				}
			}
		} //End reset()

		/**
		 * success_sync.
		 *
		 * @return void
		 */			
		public function success_sync ( $result_meli = null, $parent_product = 0 ) {
			if ( is_object($result_meli) ) {
				$this->set_woomelly_code_meli_field( $result_meli->id );
				if ( isset($result_meli->sold_quantity) )
					$this->set_woomelly_sales_meli_field( $result_meli->sold_quantity );
				$this->set_woomelly_duration_start_meli_field( $result_meli->start_time );
				if ( isset($result_meli->stop_time) )
					$this->set_woomelly_duration_end_meli_field( $result_meli->stop_time );
				if ( isset($result_meli->expiration_time) )
					$this->set_woomelly_expiration_time_meli_field( $result_meli->expiration_time );
				if ( isset($result_meli->permalink) )
					$this->set_woomelly_url_meli_field( $result_meli->permalink );
				if ( isset($result_meli->thumbnail) )
					$this->set_woomelly_thumbnail_meli_field( $result_meli->thumbnail );
				$this->set_woomelly_status_field( $result_meli->status );
				if ( isset($result_meli->sub_status) )
					$this->set_woomelly_substatus_field( implode(", ", $result_meli->sub_status) );
				$this->set_woomelly_created_meli_field( $result_meli->date_created );
				$this->set_woomelly_updated_meli_field( $result_meli->last_updated );
				if ( isset($result_meli->parent_item_id) )
					$this->set_woomelly_parent_item_id_field( $result_meli->parent_item_id );
				if ( $parent_product > 0 ) {
					$this->set_woomelly_parent_product_field( $parent_product );
				}
			}
			$this->set_woomelly_status_meli_field( 'true' );
			$this->set_woomelly_sync_problem( false );
			$this->set_woomelly_updated_field( date( 'y-m-d h:i:s' ) );
			$current_user = wp_get_current_user();
			if ( $current_user instanceof WP_User && isset($current_user->user_login) && $current_user->user_login!="" ) {
				$this->set_woomelly_updated_user_field( $current_user->user_login );
			} else {
				$this->set_woomelly_updated_user_field( 'system' );
			}
			$this->get_woomelly_created_version_field( Woomelly()->get_version() );
		} //End success_sync()

		/**
		 * product_children_sync.
		 *
		 * @return array
		 */			
		public function product_children_sync ( $permalink = true ) {
			$woomelly_children = array();
			$_product = wc_get_product( $this->product_id );
			
			if ( is_object($_product) ) {
				$available_variations = wm_get_available_variations( $_product );								
				if ( !empty($available_variations) ) {
					foreach ( $available_variations as $available_variation ) {
						$wm_product_variation = new WMProduct( $available_variation['variation_id'] );
						$woomelly_status_meli_field = $wm_product_variation->get_woomelly_status_meli_field();
						$woomelly_code_meli_field = $wm_product_variation->get_woomelly_code_meli_field();
						if ( $permalink ) {
							$woomelly_url_meli_field = $wm_product_variation->get_woomelly_url_meli_field();
							if ( $woomelly_status_meli_field == "true" && $woomelly_code_meli_field!="" ) {
								$woomelly_children[$available_variation['variation_id']] = '<a style="vertical-align: middle;" href="'.$woomelly_url_meli_field.'" target="_blank">'.$woomelly_code_meli_field.'</a>';
							}							
						} else {
							if ( $woomelly_status_meli_field == "true" && $woomelly_code_meli_field!="" ) {
								$woomelly_children[$available_variation['variation_id']] = $woomelly_code_meli_field;
							}
						}
						unset( $wm_product_variation );
					}
				}
			}

			return $woomelly_children;
		} //End product_children_sync()
    }
}