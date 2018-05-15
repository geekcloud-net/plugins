<?php
/*
 * Plugin Name: WooImporter Aliexpress Shipping
 * Description: Add-on for WooImporter getting the shipping information of Aliexpress products ergards with chosen country. Shipping data is attached to Woocommerce product and update pereodically. Also the add-on highlights e-Packet and free-shipping products in the search results in the backend. 
 * Version: 2.5.3
 * Author: Geometrix
 * License: GPLv2+
 * Author URI: http://gmetrixteam.com
 * Text Domain: wpeae-ali-ship
 * Domain Path: /languages
 */

if (!defined('WPEAE_ALIEXPRESS_SHIPPING_ROOT_URL')) {
	define('WPEAE_ALIEXPRESS_SHIPPING_ROOT_URL', plugin_dir_url(__FILE__));
}
if (!defined('WPEAE_ALIEXPRESS_SHIPPING_ROOT_PATH')) {
	define('WPEAE_ALIEXPRESS_SHIPPING_ROOT_PATH', plugin_dir_path(__FILE__));
}

include_once dirname(__FILE__) . '/install.php';
include_once(dirname(__FILE__) . '/include.php');

if (!class_exists('WooImporter_AliexpressShipping')) {

	class WooImporter_AliexpressShipping {

		private $ship_to = "";
		
		function __construct() {
		
			register_activation_hook(__FILE__, array($this, 'install'));
			register_deactivation_hook(__FILE__, array($this, 'uninstall'));
			
			add_action( 'plugins_loaded', array($this, 'load_textdomain') );
			
			$this->ship_to = get_option('wpeae_aliship_shipto', 'US');
			
			add_action('wpeae_print_api_setting_page', array($this, 'print_api_setting_page'), 1000);
			
			add_action('wpeae_save_module_settings', array($this, 'save_module_settings'), 1000, 2);
			
			add_filter('wpeae_get_dashboard_columns', array($this, 'modify_columns'), 20, 2);
			add_filter('wpeae_dashboard_column_default', array($this, 'modify_column_data'), 20, 3);
			
			add_action('wpeae_before_product_list', array($this, 'search_dashboard_actions'));
			
			add_filter('wpeae_get_detail_proc', array($this, 'get_detail'), 20, 2);
			
			add_action('admin_enqueue_scripts', array($this, 'assets'));
			
			add_filter('wpeae_woocommerce_after_addpost', array($this, 'after_addpost'), 20, 3);
            
            add_filter('wpeae_get_order_content', array($this, 'get_order_content'), 10, 2);
            
		}
		
		
		function load_textdomain() {
			load_plugin_textdomain( 'wpeae-ali-ship', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
		}

		function after_addpost($result, $post_id, $goods){
			if ($goods->type == "aliexpress" && isset($goods->additional_meta['shipping_data'])){
				update_post_meta($post_id, 'wpeae_shipping_data', $goods->additional_meta['shipping_data']);  
			}
						return $result;
		}
		
		function assets() {
			$plugin_data = get_plugin_data(__FILE__);
			
			wp_enqueue_script('wpeae-aliexpress-shipping-script', plugins_url('assets/js/script.js', __FILE__), array(), $plugin_data['Version']);
			
			$script_data = array(
				'lang' => array(
						'search_for_free_shipping_items'=>__('Search for free-shipping items...','wpeae-ali-ship'),
						'search_for_epacket_items'=>__('Search for ePacket items...','wpeae-ali-ship'),
						'build_product_list_to_import'=>__('Build product list to import:','wpeae-ali-ship'),
						'build_product_list_to_import_100'=>__('Build product list to import: 100%','wpeae-ali-ship'),
				));
				wp_localize_script( 'wpeae-aliexpress-shipping-script', 'wpeae_ali_ship_script', $script_data );
		}
		
		function search_dashboard_actions($dashboard){
			 if($dashboard->items){
		?>
				<div style="padding-bottom:15px;">
				<input type="button" id="wpeae-import-free" class="button button-primary" value="<?php _e('Post Free shipping items','wpeae-ali-ship'); ?>"/>
				<input type="button" id="wpeae-import-epacket" class="button button-primary" value="<?php _e('Post ePacket items','wpeae-ali-ship'); ?>"/>
				</div>
		<?php
			}    
		}
		
		function modify_columns($columns, $api){
		
			if ($api->get_type() == "aliexpress"){ 
				 /* translators: Name of the column in Aliexpress search results in the backend */ 
				$columns['ship_to_locations'] = __('Ship to','wpeae-ali-ship') . ' ' . $this->ship_to;
			}
			
			return $columns;    
		}
		
		function modify_column_data($result_data, $item, $column_name){
		
			if ($column_name === 'ship_to_locations'){
				$result_data = '<div class="block_field"><input type="hidden" class="meta_field_code" value="ship_to_locations">'
								. '<span class="field_text"><font style="color:red">' . __('Need to load more details','wpeae-ali-ship') . '</font></span></div>';  
			}
			return $result_data;    
		}
		
		function get_detail($goods, $params){
				      
			if ($goods->type === "aliexpress"){
		
				if (!isset($goods->additional_meta['ship_to_locations'] )){
					
					$ali_shipping_loader = wpeae_ali_shipping_get_loader();
					$shipping_data = $ali_shipping_loader->load( new WPEAE_Shipping($goods, $this->ship_to) );
						
					$goods->additional_meta['ship_to_locations'] = $shipping_data['html']; 
					$goods->additional_meta['shipping_data'] = json_encode( $shipping_data['data'] );
					
					$goods->save("API");
				}        
			
			}
			return $goods;
		}	
		
		function print_api_setting_page($api){
			if ($api->get_type() == "aliexpress"){
				include(WPEAE_ALIEXPRESS_SHIPPING_ROOT_PATH . '/view/settings.php' );     
			}
		}
		
		function save_module_settings($api, $data){
		
			if ($api->get_type() == "aliexpress"){ 
				
				update_option('wpeae_aliship_shipto', $_POST['wpeae_aliship_shipto']);
				update_option('wpeae_aliship_frontend', $_POST['wpeae_aliship_frontend']);
			
			}
		}
        
        public function get_order_content($content, $order_id){
        
            $order = new WC_Order($order_id); 
                
            $items = $order->get_items();

            $k = 0;
        
            foreach ($items as $item) {
                 
                 
                 if (isset($item['item_meta']['Shipping'])){
                    
                     if (is_array($item['item_meta']['Shipping']))
                        $shipping_title = $item['item_meta']['Shipping'][0]; 
                     else $shipping_title = $item['item_meta']['Shipping'];
                     
                     if (isset($content[$k])) $content[$k] = str_replace('</span>', ' | ' . $shipping_title . '</span>', $content[$k]);
                 }
                 $k++;    
            }
            
            return $content;
        }
		
		
		function install() {
			wpeae_aliship_install();
		}

		function uninstall() {
			wpeae_aliship_uninstall();
		}
		
	}

}

new WooImporter_AliexpressShipping();