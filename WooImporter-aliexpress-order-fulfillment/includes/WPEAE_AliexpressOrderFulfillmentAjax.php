<?php

/**
 * Description of WPEAE_AliexpressOrderFulfillmentAjax
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_AliexpressOrderFulfillmentAjax')):

	class WPEAE_AliexpressOrderFulfillmentAjax {

		function __construct() {
		
			add_action('wp_ajax_wpeae_get_aliexpress_order_data', array($this, 'get_aliexpress_order_data'));
					
		}
		
		function get_aliexpress_order_data(){
                
			$result = array("state" => "ok", "data" => "", "action" => "");
			
			$post_id = isset($_POST['id']) ? $_POST['id'] : false;
			
			if (!$post_id) {
				$result['state'] = 'error';
				$result['error_message'] = 'Bad product ID';
				echo json_encode( $result );
				wp_die();
			}
			
			   
			$order = new WC_Order($post_id); 
			
			$def_wpeae_prefship = get_option('wpeae_aliorder_fulfillment_prefship', 'ePacket');
			
			$cur_wpeae_prefship = get_post_meta( $post_id, 'wpeae_shipping', true);
		
			$content = array('id' => $post_id, 
							 'defaultShipping' => $cur_wpeae_prefship ? $cur_wpeae_prefship : $def_wpeae_prefship, 
							 'note' => $order->customer_note,
							 'products' => array(),
							 
							 'countryRegion' => $order->shipping_country ? $this->format_field_country( $order->shipping_country ) : $this->format_field_country( $order->billing_country) , 
							
							 'region' => $order->shipping_state ? $this->format_field_state( $order->shipping_country, $order->shipping_state ) : $this->format_field_state( $order->billing_country, $order->billing_state ),
							
							 'city' => $order->shipping_city ? $this->format_field( $order->shipping_city ) : $this->format_field( $order->billing_city ),
							
							 'contactName' => $order->shipping_first_name ? $order->shipping_first_name . ' ' . $order->shipping_last_name : $order->billing_first_name . ' ' . $order->billing_last_name, 
							
							 'address1' => $order->shipping_address_1 ? $order->shipping_address_1 : $order->billing_address_1, 
							 
							 'address2' => $order->shipping_address_2 ? $order->shipping_address_2 : $order->billing_address_2, 
							
							 'mobile' => $order->billing_phone, 
							 
							 'zip' => $order->shipping_postcode ? $order->shipping_postcode : $order->billing_postcode
							 );
			
			$items = $order->get_items();

			$k = 0;
			$total = 0;
			foreach ($items as $item) {
				$wpeae_item = new WPEAE_WooCommerce_OrderItem($item);
				$product_id = $wpeae_item->getProductID();
				$quantity = $wpeae_item->getQuantity();
				
				$meta_external_id = get_post_meta($product_id, 'external_id', true);
				list($api_type, $external_id, $variation_id) = explode("#", $meta_external_id . "#-");
			
				if ($api_type === "aliexpress"){
					
					$skuArray = $this->getSkuArray($wpeae_item);
			
					if ($skuArray === false && $variation_id !== '-') {
						$result['error_message'] = 'Your product structure should be updated. Please update WooImporter Aliexpress Variations add-on, then update products and after that try to fulfill the order again.';
						$result['state'] = 'error';
						echo json_encode( $result ); 
						wp_die();       
					};
					
					$original_url = get_post_meta($product_id, 'product_url', true);
					
					if (empty($original_url)){
						$result['error_message'] = 'Your order has products with broken internal data, therefore it can`t be fulfilled. You can fulfill it by manual only. To prevent such case in the future, please update or reload all your broken products from Aliexpress';
						$result['state'] = 'error';
						echo json_encode( $result ); 
						wp_die();           
					}
					
					$content['products'][$k] = array(
					'url'=> $original_url,
					'productId' => $external_id,
					'qty' => $quantity,
					'sku' => $skuArray
					);
				
					$k++;        
				}
				$total++;    
			}
			
			if ($k < 1){
				$result['error_message'] = 'No Aliexpress products in selected order!';
				$result['state'] = 'error';
				echo json_encode( $result ); 
				wp_die();           
			}
			
			if ($k == $total) {
				$result['action'] = 'upd_ord_status'; 
			}  
			
			$result['data'] = array( 'content'=> $content, 'id'=> $post_id);
			
			echo json_encode( $result ); 
			wp_die();   
		}
		
		private function format_field($str){
			$str = trim($str);
			
			if (!empty($str))
				$str = ucwords(strtolower($str)); 
			
			return $str;   
		}
		
		private function format_field_country($str){
			$str = trim($str);
			
			if (!empty($str))
				$str = strtoupper($str); 
			
			return $str;     
		}
		
		private function format_field_state($country_code, $state_code){
			$state_name =  WC()->countries->states[$country_code][$state_code];
			return $this->format_field($state_name);
		}
		
		private function getSkuArray($item){
			$sku = array();
			
			if ($item->getVariationID() !== "0"){
				
				$variation_id = $item->getVariationID();
				$external_var_data = get_post_meta($variation_id, '_aliexpress_sku_props', true);
				
				if (empty($external_var_data)) return false;
				
				if ($external_var_data){
					$items = explode(';', $external_var_data);
					
					foreach($items as $item){
						list(,$sku[]) = explode(':', $item);    
					}
				}
			}
			return $sku;
		}

		
	}

	endif;

new WPEAE_AliexpressOrderFulfillmentAjax();
