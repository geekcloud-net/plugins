<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Yoast_WCSEO_Local_Emails {

	public function __construct() {

		//filters
		add_filter( 'woocommerce_email_classes', array( $this, 'add_email_classes' ) );
		add_filter( 'woocommerce_email_actions', array( $this, 'add_email_actions' ) );

	}

	public function add_email_actions( $hooks ) {

		$hooks[] = 'woocommerce_order_status_processing_to_transporting';
		$hooks[] = 'woocommerce_order_status_transporting_to_ready-for-pickup';

		return $hooks;

	}

	public function add_email_classes( $email_classes ) {

		// include our custom email class
		require_once( 'class-wc-email-transporting.php' );
		require_once( 'class-wc-email-readyforpickup-order.php' );

		// add the email class to the list of email classes that WooCommerce loads
		$email_classes['WC_Email_Transporting_Order']   = new WC_Email_Transporting_Order();
		$email_classes['WC_Email_ReadyForPickup_Order'] = new WC_Email_ReadyForPickup_Order();

		return $email_classes;
	}

}

new Yoast_WCSEO_Local_Emails();