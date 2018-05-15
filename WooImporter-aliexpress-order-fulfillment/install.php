<?php

if (!function_exists('wpeae_aliexpress_order_fulfillment_install')) {
	function wpeae_aliexpress_order_fulfillment_install() {
		add_option( 'wpeae_aliorder_fulfillment_prefship', 'ePacket', '', 'no' );
			
		do_action('wpeae_aliexpress_order_fulfillment_install_action');
	}
}


if (!function_exists('wpeae_aliexpress_order_fulfillment_uninstall')) {
	function wpeae_aliexpress_order_fulfillment_uninstall() {
		delete_option( 'wpeae_aliorder_fulfillment_prefship' );
		
		do_action('wpeae_aliexpress_order_fulfillment_uninstall_action');
	}
}

