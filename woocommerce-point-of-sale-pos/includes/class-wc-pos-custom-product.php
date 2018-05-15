<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Custom Product Class
 *
 * The custom product type at the Register.
 *
 * @class 		WC_POS_Custom_Product
 * @version		2.0.0
 * @package		WoocommercePointOfSale/Classes/Products
 * @category	Class
 * @author 		Actuality Extensions
 */
class WC_POS_Custom_Product extends WC_Product_Simple {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @param mixed $product
	 */
	public function __construct( $product ) {
		$this->product_type = 'simple';
		parent::__construct( $product );
	}
}
