<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Amazon Product Class
 *
 * An amazon listing which doesn't exist in WooCommerce...
 *
 */
if ( class_exists('WC_Product') ) {

	class WC_Product_Amazon extends WC_Product {

		/**
		 * __construct function.
		 *
		 * @access public
		 * @param mixed $product
		 */
		public function __construct( $product ) {
			$this->product_type = 'amazon_listing';
			
			$this->id                = 0;
			$this->asin              = $product;
			$this->sku               = $product; 		// this will show the ASIN on the generated email
			$this->post              = new stdClass(); 	// prevent non-object warning in /woocommerce/includes/abstracts/abstract-wc-product.php:693
			$this->post->post_status = 'publish'; 		// make product purchasable for WooCommerce (?)

			parent::__construct( $product );
			// parent::__construct( 0 );
		}

	}

}
