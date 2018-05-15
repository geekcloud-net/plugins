<?php
/*
  Plugin Name: WooImporter Aliexpress Order fulfillment
  Description: Add-on for WooImporter to fulfill the order automatically 
  Version: 1.1.8
  Author: Geometrix
  License: GPLv2+
  Author URI: http://gmetrixteam.com
 */

if (!defined('WPEAE_ALIEXPRESS_ORDER_FULFILLMENT_ROOT_URL')) {
	define('WPEAE_ALIEXPRESS_ORDER_FULFILLMENT_ROOT_URL', plugin_dir_url(__FILE__));
}
if (!defined('WPEAE_ALIEXPRESS_ORDER_FULFILLMENT_ROOT_PATH')) {
	define('WPEAE_ALIEXPRESS_ORDER_FULFILLMENT_ROOT_PATH', plugin_dir_path(__FILE__));
}

include_once dirname(__FILE__) . '/includes/WPEAE_AliexpressOrderFulfillment_WCOL.php';
include_once dirname(__FILE__) . '/includes/WPEAE_AliexpressOrderFulfillmentAjax.php';

include_once dirname(__FILE__) . '/install.php';

if (!class_exists('WooImporter_AliexpressOrderFulfillment')) {

	class WooImporter_AliexpressOrderFulfillment {

		function __construct() {
	
			register_activation_hook(__FILE__, array($this, 'install'));
			register_deactivation_hook(__FILE__, array($this, 'uninstall'));
		
			add_action('wpeae_print_api_setting_page', array($this, 'print_api_setting_page'), 1000);
			
			add_action('wpeae_save_module_settings', array($this, 'save_module_settings'), 1000, 2);
		}
		
		function print_api_setting_page($api){
			if ($api->get_type() == "aliexpress"){
				include(WPEAE_ALIEXPRESS_ORDER_FULFILLMENT_ROOT_PATH . '/view/settings.php' );     
			}
		}
		
		function save_module_settings($api, $data){
		
			if ($api->get_type() == "aliexpress"){    
				update_option('wpeae_aliorder_fulfillment_prefship', $data['wpeae_aliorder_fulfillment_prefship']);
			}
		}
		
		function install() {
			wpeae_aliexpress_order_fulfillment_install();
		}

		function uninstall() {
			wpeae_aliexpress_order_fulfillment_uninstall();
		}
		
	}

}

new WooImporter_AliexpressOrderFulfillment();