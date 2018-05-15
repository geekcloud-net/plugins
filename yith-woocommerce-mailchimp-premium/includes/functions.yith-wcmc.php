<?php
/**
 * General functions
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Mailchimp
 * @version 2.0.10
 */

if ( ! defined( 'YITH_WCMC' ) ) {
	exit;
} // Exit if accessed directly

if( ! function_exists( 'yith_wcmc_include_available_translations' ) ){
	function yith_wcmc_include_available_translations(){
		$translations = array(
			// general section
			'-50' => _x( 'You have made too many connections on the MailChimp server.', 'Mailchimp error translation (-50)', 'yith-woocommerce-mailchimp' ),
			'101' => _x( 'You cannot use your APIs, because your user profile has been deactivated.', 'MailChimp error translation (101)', 'yith-woocommerce-mailchimp' ),
			'104' => _x( 'Your API key may be invalid, or you\'ve attempted to access the wrong datacenter.', 'MailChimp error translation (104)', 'yith-woocommerce-mailchimp' ),
			'105' => _x( 'You cannot use your APIs, because your user profile is under maintenance.', 'MailChimp error translation (105)', 'yith-woocommerce-mailchimp' ),
			'109' => _x( 'You cannot complete your operation, because you don\'t have the necessary authorization.', 'MailChimp error translation (109)', 'yith-woocommerce-mailchimp' ),
			'120' => _x( 'You have requested an invalid operation.', 'MailChimp error translation (120)', 'yith-woocommerce-mailchimp' ),
			'232' => _x( 'The email you have written is not valid, please try to write it again.', 'MailChimp error translation (232)', 'yith-woocommerce-mailchimp' ),
			'500' => _x( 'The sent information are not valid, please double check them.', 'MailChimp error translation (500)', 'yith-woocommerce-mailchimp' ),

			// list/subscribe section
			'-99' => _x( 'You submitted an invalid email address that cannot be imported.', 'MailChimp error translation (-99)', 'yith-woocommerce-mailchimp' ),
			'200' => _x( 'Invalid MailChimp List ID.', 'MailChimp error translation (200)', 'yith-woocommerce-mailchimp' ),
			'214' => _x( 'You submitted an email address that is already subscribed to the list.', 'MailChimp error translation (214)', 'yith-woocommerce-mailchimp' )

		);

		return apply_filters( 'yith_wcmc_available_translations', $translations );
	}
}

