<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( !class_exists( 'WMOrder' ) ) {
    /**
     * WMProduct Class.
     */
    class WMOrder {
    	private $order_id;
    	private $woomelly_buyer;
    	private $woomelly_code_meli_field;
    	private $woomelly_billing_info;
    	private $woomelly_shipping_id;
    	private $woomelly_shipping_tracking_method;
    	private $woomelly_shipping_tracking_number;
    	private $woomelly_shipping_comments;
    	private $woomelly_shipping_mode;
    	private $woomelly_shipping_option_id;
    	
    	/**
         * Default constructor.
         */    	
		public function __construct ( $id = 0 ) {
			$id = absint( $id );
			
			if ( $id > 0 ) {
				$this->order_id = $id;
				$this->woomelly_buyer = get_post_meta( $id, '_wm_buyer', true );
				$this->woomelly_code_meli_field = get_post_meta( $id, '_wm_code_meli_order', true );
				$this->woomelly_billing_info = get_post_meta( $id, '_wm_billing_info', true );
				$this->woomelly_shipping_id = get_post_meta( $id, '_wm_shipping_id', true );
				$this->woomelly_shipping_tracking_method = get_post_meta( $id, '_wm_shipping_tracking_method', true );
				$this->woomelly_shipping_tracking_number = get_post_meta( $id, '_wm_shipping_tracking_number', true );
				$this->woomelly_shipping_comments = get_post_meta( $id, '_wm_shipping_comments', true );
				$this->woomelly_shipping_mode = get_post_meta( $id, '_wm_shipping_mode', true );
				$this->woomelly_shipping_option_id = get_post_meta( $id, '_wm_shipping_option_id', true );
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
			return $this->order_id;
		} //End get_id()

		/**
		 * set_product_id.
		 *
		 * @return void
		 */
		public function set_product_id ( $value ) {
			$this->order_id = absint($value);
		} //End set_product_id()

		/**
		 * get_woomelly_buyer.
		 *
		 * @return string
		 */
		public function get_woomelly_buyer () {
			return $this->woomelly_buyer;
		} //End get_woomelly_buyer()
		
		/**
		 * set_woomelly_buyer.
		 *
		 * @return void
		 */		
		public function set_woomelly_buyer ( $value ) {
			$this->woomelly_buyer = trim($value);
			update_post_meta( $this->order_id, '_wm_buyer', $this->woomelly_buyer );
		} //End set_woomelly_buyer()	

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
			update_post_meta( $this->order_id, '_wm_code_meli_order', $this->woomelly_code_meli_field );
		} //End set_woomelly_code_meli_field()

		/**
		 * get_woomelly_billing_info.
		 *
		 * @return string
		 */	
		public function get_woomelly_billing_info () {
			return $this->woomelly_billing_info;
		} //End get_woomelly_billing_info()
		
		/**
		 * set_woomelly_billing_info.
		 *
		 * @return void
		 */	
		public function set_woomelly_billing_info ( $value ) {
			if ( !is_array($value) ) {
				$this->woomelly_billing_info = array( 'doc_type' => '', 'doc_number' => '' );
			} else {
				$this->woomelly_billing_info = $value;
			}
			update_post_meta( $this->order_id, '_wm_billing_info', $this->woomelly_billing_info );
		} //End set_woomelly_billing_info()

		/**
		 * get_woomelly_shipping_id.
		 *
		 * @return string
		 */	
		public function get_woomelly_shipping_id () {
			return $this->woomelly_shipping_id;
		} //End get_woomelly_shipping_id()
		
		/**
		 * set_woomelly_shipping_id.
		 *
		 * @return void
		 */	
		public function set_woomelly_shipping_id ( $value ) {
			$this->woomelly_shipping_id = trim($value);
			update_post_meta( $this->order_id, '_wm_shipping_id', $this->woomelly_shipping_id );
		} //End set_woomelly_shipping_id()

		/**
		 * get_woomelly_shipping_tracking_method.
		 *
		 * @return string
		 */	
		public function get_woomelly_shipping_tracking_method () {
			return $this->woomelly_shipping_tracking_method;
		} //End get_woomelly_shipping_tracking_method()
		
		/**
		 * set_woomelly_shipping_tracking_method.
		 *
		 * @return void
		 */	
		public function set_woomelly_shipping_tracking_method ( $value ) {
			$this->woomelly_shipping_tracking_method = trim($value);
			update_post_meta( $this->order_id, '_wm_shipping_tracking_method', $this->woomelly_shipping_tracking_method );
		} //End set_woomelly_shipping_tracking_method()

		/**
		 * get_woomelly_shipping_tracking_number.
		 *
		 * @return string
		 */	
		public function get_woomelly_shipping_tracking_number () {
			return $this->woomelly_shipping_tracking_number;
		} //End get_woomelly_shipping_tracking_number()
		
		/**
		 * set_woomelly_shipping_tracking_number.
		 *
		 * @return void
		 */	
		public function set_woomelly_shipping_tracking_number ( $value ) {
			$this->woomelly_shipping_tracking_number = trim($value);
			update_post_meta( $this->order_id, '_wm_shipping_tracking_number', $this->woomelly_shipping_tracking_number );
		} //End set_woomelly_shipping_tracking_number()

		/**
		 * get_woomelly_shipping_comments.
		 *
		 * @return string
		 */	
		public function get_woomelly_shipping_comments () {
			return $this->woomelly_shipping_comments;
		} //End get_woomelly_shipping_comments()
		
		/**
		 * set_woomelly_shipping_comments.
		 *
		 * @return void
		 */	
		public function set_woomelly_shipping_comments ( $value ) {
			$this->woomelly_shipping_comments = trim($value);
			update_post_meta( $this->order_id, '_wm_shipping_comments', $this->woomelly_shipping_comments );
		} //End set_woomelly_shipping_comments()

		/**
		 * get_woomelly_shipping_mode.
		 *
		 * @return string
		 */	
		public function get_woomelly_shipping_mode () {
			return $this->woomelly_shipping_mode;
		} //End get_woomelly_shipping_mode()
		
		/**
		 * set_woomelly_shipping_mode.
		 *
		 * @return void
		 */	
		public function set_woomelly_shipping_mode ( $value ) {
			$this->woomelly_shipping_mode = trim($value);
			update_post_meta( $this->order_id, '_wm_shipping_mode', $this->woomelly_shipping_mode );
		} //End set_woomelly_shipping_mode()

		/**
		 * get_woomelly_shipping_option_id.
		 *
		 * @return string
		 */	
		public function get_woomelly_shipping_option_id () {
			return $this->woomelly_shipping_option_id;
		} //End get_woomelly_shipping_option_id()
		
		/**
		 * set_woomelly_shipping_option_id.
		 *
		 * @return void
		 */	
		public function set_woomelly_shipping_option_id ( $value ) {
			$this->woomelly_shipping_option_id = trim($value);
			update_post_meta( $this->order_id, '_wm_shipping_option_id', $this->woomelly_shipping_option_id );
		} //End set_woomelly_shipping_option_id()
	}
}