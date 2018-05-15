<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
* Mercado Envios Shipping Method for Mercado Pago.
*
* A simple shipping method allowing free pickup as a shipping method for Mercado Pago.
*
* @class 		WC_MercadoEnvios_Shipping_Normal
* @version		3.0.0
* @package		WooCommerce/Classes/Shipping
* @author 		Mercado Pago
*/

class WC_MercadoEnvios_Shipping_Normal extends WC_MercadoEnvios_Shipping {

	protected $shipments_id = array(
		'MLA' => 73328,
		'MLB' => 100009,
		'MLM' => 501245
	);

	/**
	 * Constructor.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id = 'woo-mercado-pago-me-normal';
		$this->method_title = 'Mercado Envios - Normal';
		parent::__construct( $instance_id );
	}
	
}
