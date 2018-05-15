<?php
/*
  Plugin Name: WooImporter Language Translate
  Description: Add-on for WooImporter translating different WooImporter content to specific language (several languages are available). 
  Version: 1.3.5
  Author: Geometrix
  License: GPLv2+
  Author URI: http://gmetrixteam.com
 */

if (!defined('WPEAE_TRANSLATE_ROOT_URL')) {
    define('WPEAE_TRANSLATE_ROOT_URL', plugin_dir_url(__FILE__));
}
if (!defined('WPEAE_TRANSLATE_ROOT_PATH')) {
    define('WPEAE_TRANSLATE_ROOT_PATH', plugin_dir_path(__FILE__));
}



include_once(dirname(__FILE__) . '/includes/WPEAE_TranslateContent.php');
include_once dirname(__FILE__) . '/install.php';

if (!class_exists('WooImporter_Translate')) {

    class WooImporter_Translate {

        function __construct() {
    
            register_activation_hook(__FILE__, array($this, 'install'));
            register_deactivation_hook(__FILE__, array($this, 'uninstall'));
            
            add_action('wpeae_print_api_setting_page', array($this, 'print_api_setting_page'), 1000);
            
            add_action('wpeae_save_module_settings', array($this, 'save_module_settings'), 1000, 2);
            
        }
        
        function print_api_setting_page($api){
            if ($api->get_type() == "aliexpress"){
                
                include_once(dirname(__FILE__) . '/includes/WPEAE_BingTranslateService.php');
                    
                $inputStrArr  = array("Цвет - белый", "Размер большой", "Тестовая строка");
                    
                $bing_client_secret = get_option('wpeae_aliexpress_bing_secret', '');
                
                //if Bing is enabled
                if ($bing_client_secret) {    
                    $translateService = new WPEAE_BingTranslateService($bing_client_secret);
                    
                    //check translate service right in the Setting
                    $translateService->translateArray($inputStrArr, 'en');
                }
                    include(WPEAE_TRANSLATE_ROOT_PATH . '/view/settings.php' );     
            }
        }
        
        function save_module_settings($api, $data){
        
            if ($api->get_type() == "aliexpress"){ 
                
                update_option('wpeae_aliexpress_language', $_POST['wpeae_aliexpress_language']);
                
                if (!defined('WPEAE_DEMO_MODE') || !WPEAE_DEMO_MODE) 
                update_option('wpeae_aliexpress_bing_secret', $_POST['wpeae_aliexpress_bing_secret']);
    
            }
        }
        
        
        function install() {
            wpeae_translate_install();
        }

        function uninstall() {
            wpeae_translate_uninstall();
        }
        
    }

}

new WooImporter_Translate();