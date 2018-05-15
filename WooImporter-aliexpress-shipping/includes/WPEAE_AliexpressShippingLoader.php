<?php
/**
 * Description of WPEAE_AliexpressShippingLoader
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_AliexpressShippingLoader')):

	class WPEAE_AliexpressShippingLoader {
	
		public function __construct() {
			   
		}
		
		public static function get_shipping_countries() {
			$result = json_decode(file_get_contents(WPEAE_ALIEXPRESS_SHIPPING_ROOT_PATH . 'data/countries.json'), true);
			$result = $result["countries"];
			
			$countries = array();
			
			foreach ($result as $country){
				$countries[$country['c']] = $country['n'];       
			}
			
			unset($countries['split'], $countries['Other']);
			
			return $countries;
		}

		private function normalize_country($country){
			if ($country == "GB") $country = "UK"; 
			return $country;  
		}
		
		public function load($shipping){
		    $shipping->quantity = 1; //TODO: sometime when quntity > 1, some shipping methods become hidden
			$response_body = "";
			
			$to_country = $this->normalize_country( $shipping->to_country ); 
		 
			$result_data = array('data'
									=>array('ways'=>array(), 'to_country_code'=>''), 
								  'html'=>'');
			
						  
			if ( $shipping->load() ){
				$response_body = json_decode($shipping->data);
			}
			else { 
				
				$external_id = $shipping->external_id; 
							  
				$request_url = "https://m.aliexpress.com/freight/ajaxapi/calculate.htm?productId={$external_id}&quantity={$shipping->quantity}&countryCode={$to_country}&fromCountryCode=CN";
				
				$ship_data = array();
				
				$response = wp_remote_get($request_url);
				if (!is_wp_error($response)) {
					
					//Internal shipping table keeps shipping rate for items   
					$shipping->save_data($response['body']);
					
					$response_body = json_decode($response['body']);
				}     
			} 
				
			$result_data['data']['to_country_code'] = $to_country;
			
            if ($response_body) {
			    foreach ($response_body as $ship_way){
				    
				    if(function_exists('wpeae_ali_forbidden_words')){
					    $ship_way->company = wpeae_ali_forbidden_words($ship_way->company);
				    } 
						    
				    $result_data['html'] .= "<strong>" .  $ship_way->company . "</strong>";
		    
                    $local_values = $this->get_company_local_values($ship_way->company);
                 
			        $ship_data['company'] = $local_values ? $local_values['title'] : $ship_way->company;
                    
				    $ship_data['serviceName'] = $ship_way->serviceName;
				    
				    if ($ship_way->discount < 100) { 
					    
					    $currency_conversion_factor = floatval(
					    get_option('wpeae_currency_conversion_factor', 1)); 
			    
					    $ship_price = round($ship_way->freightAmount->value * $currency_conversion_factor, 2);
					    
					    $ship_currency = $currency_conversion_factor !== 1 ? get_option( 'woocommerce_currency', 'GBP' ) : $ship_way->freightAmount->currency; 
					    
					    $result_data['html'] .= " " . $ship_price . " " . $ship_currency;
				    
					    $ship_data['price'] = $ship_price;
					    $ship_data['currency'] = $ship_currency;
				    } 
				    else {
					    $result_data['html'] .= " " . __("free", 'wpeae-ali-ship');
					    $ship_data['price'] = 0;
				    }
                    
                    if (!$local_values)
                        $this->set_company_local_data($ship_data['company'], $ship_data['serviceName']);
                                                       	                                                                                   
				    $result_data['html'] .= " ({$ship_way->time} " . __("days", 'wpeae-ali-ship') . ")<br/>"; 
				    $ship_data['time'] = $ship_way->time;
									    
				    $result_data['data']['ways'][] = $ship_data;
							       
			    }
			}
			
			return $result_data; 
		}
        
        private function get_company_local_values($company){
            $data = WPEAE_ShippingPage::get_item($company);
            return $data;
        }
		
        private function set_company_local_data($company, $service_name){
            WPEAE_ShippingPage::add_item($company, $service_name);  
        }
	}

	
endif;