<?php
/**
 * Custom code shortcode
 *
 * This template can be overridden by copying it to yourtheme/plugin-folder-name/woo_mail/custom_code.php.
 * @var $order WooCommerce order
 * @var $email_id WooCommerce email id (new_order, cancelled_order)
 * @var $sent_to_admin WooCommerce email send to admin
 * @var $plain_text WooCommerce email format
 * @var $email WooCommerce email object
 * @var $attr array custom code attributes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Example for the short code [woo_mb_custom_code type="pre-order-link"]
//if(isset($attr['type']) && $attr['type'] == 'pre-order-link'){
//    printf( __( "Your pre-order is now available, but requires payment. %sPlease pay for your pre-order now.%s", 'wc-pre-orders' ), '<a href="' . $order->get_checkout_payment_url() . '">', '</a>' );
//}