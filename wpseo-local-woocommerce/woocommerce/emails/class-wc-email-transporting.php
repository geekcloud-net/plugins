<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( 'abstract-wc-email.php' );

/**
 * A custom Transporting Order WooCommerce Email class
 *
 * @since 0.1
 * @extends \WPSEO_Local_WooCommerce_Email
 */
class WC_Email_Transporting_Order extends WPSEO_Local_WooCommerce_Email {


	/**
	 * Set email defaults
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// set ID, this simply needs to be a unique name
		$this->id = 'wc_transporting_order';

		// this is the title in WooCommerce Email settings
		$this->title = __('Transporting order', 'yoast-local-seo-woocommerce');

		// this is the description in WooCommerce email settings
		$this->description = __('Transporting order notification emails are sent when an order has been sent to the local pickup store', 'yoast-local-seo-woocommerce' );

		// these are the default heading and subject lines that can be overridden using the settings
		/* translators: Transporting shipping order = the title and subject for an email-notification that is being sent to the customer when an order is being shipped to the local store */
		$this->heading = __('Transporting shipping order', 'yoast-local-seo-woocommerce');
		$this->subject = __('Transporting shipping order', 'yoast-local-seo-woocommerce');

		// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar
		$this->template_html  = 'emails/customer-transporting-order.php';
		$this->template_plain = 'emails/plain/customer-transporting-order.php';

		// Trigger on orders put to transporting
		add_action( 'woocommerce_order_status_processing_to_transporting_notification', array( $this, 'trigger' ) );

		// Call parent constructor to load any other defaults not explicity defined here
		parent::__construct();

	}

}