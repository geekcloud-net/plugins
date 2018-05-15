<?php

if (!defined('WPEAE_TABLE_SHIPPING')) {
	define('WPEAE_TABLE_SHIPPING', 'wpeae_shipping');
}

include_once(dirname(__FILE__) . '/includes/WPEAE_ShippingPage.php');
include_once(dirname(__FILE__) . '/includes/WPEAE_Shipping.php');
include_once(dirname(__FILE__) . '/includes/WPEAE_AliexpressShippingLoader.php');
include_once(dirname(__FILE__) . '/includes/WPEAE_AliexpressShippingImportAjax.php');

if (get_option('wpeae_aliship_frontend')) :
	include_once(dirname(__FILE__) . '/includes/WPEAE_AliexpressShippingFrontend.php');
endif;

if (!function_exists('wpeae_ali_shipping_get_loader')) {

	function wpeae_ali_shipping_get_loader() {
		return new WPEAE_AliexpressShippingLoader();
	}

}