<?php
if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class WC_Pos_Payment_Gateways{

	public static function init()
	{
		#add_filter('wc_pos_enqueue_scripts',   array(__CLASS__, 'pos_enqueue_scripts'), 10, 1);
		add_action('pos_admin_enqueue_scripts',   array(__CLASS__, 'admin_enqueue_scripts'));
		add_filter('woocommerce_is_checkout',   array(__CLASS__, 'woocommerce_is_checkout'));
		
		add_action( 'option_woocommerce_securesubmit_settings', array(__CLASS__, 'woocommerce_securesubmit_settings'), 100, 1 );
	}

	public static function woocommerce_is_checkout($is_checkout)
	{
		if( is_pos() ) {
    		$is_checkout = true;
		}
		return $is_checkout;
	}

	public static function woocommerce_securesubmit_settings($value)
	{
		if( is_pos() ) {
    		$value['use_iframes'] = 'no';			
		}
    	return $value;
	}

	public static function pos_enqueue_scripts($sctipts){
		if( class_exists('WooCommerceSecureSubmitGateway') ){
			$sctipts['WooCommerceSecureSubmitGateway'] = WC_POS()->plugin_url() . '/assets/js/register/subscriptions.js';			
		}
		return $sctipts;
	}
	public static function admin_enqueue_scripts($sctipts){
		if( class_exists('WC_Gateway_SecureSubmit') ){
			$ss = new WC_Gateway_SecureSubmit();
			$ss->payment_scripts();
		}
	}
}

WC_Pos_Payment_Gateways::init();