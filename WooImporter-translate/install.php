<?php

if (!function_exists('wpeae_translate_install')) {
	function wpeae_translate_install() {	
		add_option( 'wpeae_aliexpress_language', 'en', '', 'no' );
		add_option( 'wpeae_aliexpress_bing_secret', '', '', 'no' );
	
		do_action('wpeae_translate_install_action');
	}
}


if (!function_exists('wpeae_translate_uninstall')) {
	function wpeae_translate_uninstall() {
		delete_option('wpeae_aliexpress_language' );
		delete_option('wpeae_aliexpress_bing_secret' );
		
		do_action('wpeae_translate_uninstall_action');
	}
}

