<?php
/**
 * Description of WPEAE_AliexpressShippingImportAjax
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_AliexpressShippingImportAjax')):

	class WPEAE_AliexpressShippingImportAjax {

		function __construct() {
			add_action('wp_ajax_wpeae_aliship_get_products_by_filter', array($this, 'get_products_by_filter'));
			add_action('wp_ajax_wpeae_aliship_get_ids', array($this, 'get_ids'));
			add_action('wp_ajax_wpeae_aliship_load_and_import_goods', array($this, 'load_and_import_goods'));  
			
			add_action('wp_ajax_wpeae_get_shipping_method_data', array($this, 'get_shipping_method_data'));
			add_action('wp_ajax_nopriv_wpeae_get_shipping_method_data', array($this, 'get_shipping_method_data'));
			
			add_filter('wpeae_ajax_product_info', array($this, 'product_info'), 4, 9000);
		}
		
		function get_shipping_method_data(){
            
			$result = array("state" => "ok", "data" => array());
			
			$product_id = intval( $_POST['id'] );
			$ship_to = $_POST['country'];
			$quantity = intval( $_POST['quantity'] );
			
			$goods = wpeae_get_goods_by_post_id($_POST['id']);
		   
			$ali_shipping_loader = wpeae_ali_shipping_get_loader();
			$shipping_data = $ali_shipping_loader->load( new WPEAE_Shipping($goods, $ship_to, $quantity));
				
			$shipping_methods = $shipping_data['data']['ways'];
			
			if ( !empty($shipping_methods) ){
				foreach ($shipping_methods as $method){
				 
					$result['data'][$method['serviceName']] = $method['company'] . ", " . $method['time'] . " " . __('days','wpeae-ali-ship') . ", " . ($method['price'] > 0 ? $method['price'] . " " . $method['currency'] :  __('free shipping','wpeae-ali-ship'));   
				}
			} else {
				$result["state"] = "error";
				$result["message"] = __('The product can`t be delivered to this country or too large quantity was set.','wpeae-ali-ship');   
			}
			
			echo json_encode($result);
			wp_die();
		}
	
		function product_info($content, $post_id, $external_id, $source){
			         
			if ($source === "aliexpress") {
				$shipping_data = get_post_meta($post_id, 'wpeae_shipping_data', true);        
				
				if ($shipping_data){
				
					$shipping_data = json_decode($shipping_data);
				
					$tmp = array();
					foreach ($shipping_data->ways as $ship_way){
						$tmp[] = $ship_way->company . ' ' . 
										  ($ship_way->price > 0 ? $ship_way->price . " " . $ship_way->currency : 'free' ) . ' ' .
										  '(' . $ship_way->time . ' days)';    
					}
					$ships_html = implode(', ', $tmp);
					
					$content[] = __('Shipping to','wpeae-ali-ship') . " {$shipping_data->to_country_code} <span class='wpeae_value'>" . $ships_html . "</span>";
				}
					
			}
			
			return $content;
			
		}
		
		function get_products_by_filter(){
			$result = array("state" => "ok");
			
			$filter = array();
			parse_str($_POST['filter'], $filter);
			
			$current_page = (isset($_POST['page']) && intval($_POST['page']))?intval($_POST['page']):1;
			$page_on_query = 2;
			
			$loader = (isset($filter['type']) && $filter['type'])?wpeae_get_loader($filter['type']):false;
			
			if($loader){
				$upload_dir = wp_upload_dir();
				$file_url = $upload_dir['basedir']."/wpeae_aliship_import.csv";
				
				if($current_page == 1 && file_exists($file_url)){
					unlink($file_url);
				}
				
				$result['type'] = $loader->api->get_type();
				
				$filter = $loader->prepare_filter($filter);
				$result['filter'] = $filter;
				
				for($i=0;$i<$page_on_query;$i++){
					$link_category_id = (isset($filter['link_category_id']) && IntVal($filter['link_category_id'])) ? IntVal($filter['link_category_id']) : 0;
					
					$data = $loader->load_list_proc($filter, $current_page);
					if (!$data["error"]) {
						$total_items = IntVal($data['total']);
						$per_page = IntVal($data['per_page']);
						$pages = IntVal($total_items/$per_page) + IntVal($total_items%$per_page>0?1:0);
						
						$result['pages'] = $pages;
						$result['pages_loaded'] = $current_page;
						
						$items = "";
						foreach($data["items"] as $item){
							$items.=$item->external_id.";".$link_category_id.PHP_EOL;
						}
						file_put_contents($file_url, $items, FILE_APPEND | LOCK_EX);
						
						$current_page++;
					}
				}
				
				
			}
			echo json_encode($result);
			wp_die();
		}
		
		function get_ids(){
			$result = array("state" => "ok","ids"=>array());
			$upload_dir = wp_upload_dir();
			$file_dir = $upload_dir['basedir']."/wpeae_aliship_import.csv";
			if(file_exists($file_dir)){
				$result['ids'] = $this->parse_csv_file($file_dir);
				$result['ids'] = array_unique($result['ids']);
			}
			echo json_encode($result);
			wp_die();    
		}
		
		
		public static function parse_csv_file($url) {
			$ids = array();
			$csv = file_get_contents($url);
			$csv_rows = explode(PHP_EOL, $csv);
			foreach ($csv_rows as $row) {
				$tmp = explode(";", $row);
				if ($tmp && is_array($tmp) && $tmp[0]) {
					$ids[] = implode("#",$tmp);
				}
			}
			
			return array_unique($ids);
		}
		
		public function load_and_import_goods(){ 
			
			$result = array("state" => "ok", "message" => "");
			try {
				set_error_handler("wpeae_error_handler");

				$goods = new WPEAE_Goods(isset($_POST['id']) ? $_POST['id'] : "");
				$link_category_id = isset($_POST['link_category_id']) ? intval($_POST['link_category_id']) : 0;
				$import_status = isset($_POST['import_status']) ? $_POST['import_status'] : "";
				$ship_filter = isset($_POST['shipping_filter']) ? $_POST['shipping_filter'] : "free";
				
				$ship_to = get_option('wpeae_aliship_shipto', 'US');
				
				$ali_shipping_loader = wpeae_ali_shipping_get_loader();
				$shipping_data = $ali_shipping_loader->load( new WPEAE_Shipping($goods, $ship_to) );
                
				$shipping_check = $this->check_shipping_data($shipping_data, $ship_filter);
				
				if ($shipping_check){
                    
                    if ($shipping_check !== true){
                        $result['message'] = $shipping_check;
                        $result['state'] = 'skip';  
                        echo json_encode($result);
                        wp_die();   
                    }
					
					$goods->additional_meta['ship_to_locations'] = $shipping_data['html']; 
					$goods->additional_meta['shipping_data'] = json_encode( $shipping_data['data'] );
					 
					$goods->save("API"); 
						
					$loader = wpeae_get_loader($goods->type);
					
					if ($loader && class_exists('WPEAE_WooCommerce')) {
						$res = $loader->load_list_proc(array('wpeae_productId' => $goods->external_id, 'link_category_id' => $link_category_id));


						if (isset($res['error']) && $res['error']) {
							$result['state'] = 'error';
							$result['message'] = $res['error'];
						} else {
							if (count($res["items"]) > 0) {
								$goods = $res["items"][0];
								$goods->load();

								
								
								if ($goods->need_load_more_detail()) {
									$result = $loader->load_detail_proc($goods);
									/* continue with error!
									  if($res['state']=='error'){
									  $result['state']='error';
									  $result['message']=$res['message'];
									  } */
								}

								if ($result['state'] == 'ok') {
									$goods->save_field("user_schedule_time", NULL);

									if (!$goods->post_id) {
										$result = WPEAE_WooCommerce::add_post($goods, array("import_status" => $import_status));
										$result['goods'] = $goods;
									} else {
										$result['state'] = 'error';
										$result['message'] = 'Product already loaded';
									}
								}
							} else {
								$result['state'] = 'error';
								$result['message'] = 'Product not found';
							}
						}
					}

				} //shipping check if
				else {
					$result['state'] = 'skip'; 
				}
				
				restore_error_handler();
			} catch (Exception $e) {
				$result['state'] = 'error';
				$result['message'] = "Error: " . $e->getMessage();
			}

			echo json_encode($result);

			wp_die();
		
		}
		
		private function check_shipping_data($shipping_data, $ship_filter){
             
             //if ali ship data was not loaded, skip this product
             if (empty($shipping_data['data']['ways'])) return 'Aliexpress request error';
             
			 if (strpos($shipping_data['html'], $ship_filter) !== false) return true;
			
			 return false;    
		}



	}

	
endif;

new WPEAE_AliexpressShippingImportAjax();