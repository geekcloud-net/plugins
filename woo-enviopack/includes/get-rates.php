<?php
	add_action( 'woocommerce_order_status_processing', 'opciones_enviopack');
	function opciones_enviopack($order_id){
			$order = wc_get_order( $order_id );	
			foreach( $order->get_items( 'shipping' ) as $item_id => $shipping_item_obj ){
				$shipping_item_data = $shipping_item_obj->get_data();
			}
			$chosen_shipping = $shipping_item_data['method_id'];
			$enviopack_settings = get_post_meta($order_id, 'enviopack_settings', true);		
			$order_totals  = get_post_meta($order_id, '_order_total', true ); 
			$chosen_shipping = str_replace('"','',$chosen_shipping);
			$chosen = explode('-', $chosen_shipping);
			if($chosen[1] == 'SS'){ // A SUCURSAL
				$id        =  $chosen[0];
				$modalidad = 'S';
				$servicio  = $chosen[2];
				$correo		 = $chosen[3];
 				$cpostal   = $chosen[4];
			} else { // A DOMICILIO
				$despacho  = $chosen[2];
				$modalidad = 'D';
				$servicio  = $chosen[1];
			}

			$enviopack_settings = base64_decode($enviopack_settings);
			$enviopack_settings = json_decode($enviopack_settings);

			$method_id = $enviopack_settings[0]->settings;//62
			$settings = get_option( 'woocommerce_enviopack_wanderlust_'.$method_id.'_settings' );
			$direccion_envio = $settings['origin_contacto'];		
			$usa_seguro = $settings['usa_seguro'];		
			$origin_sucursal = $settings['origin_sucursal'];		

			$altura_depto = $order->get_shipping_address_2();
			$altura_depto = explode(',', $altura_depto);
		
 			$codigo_postal = $order->get_shipping_postcode();
			if(!empty($cpostal)){
				$codigo_postal = $cpostal;
			}
 
			$body = array(
				'api-key' => $settings['api_key'],
				'secret-key' => $settings['secret_key'],
			);

			$args = array(
					'body' => $body,
					'timeout' => '5',
					'redirection' => '5',
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(),
					'cookies' => array()
			);

			$response = wp_remote_post( 'https://api.enviopack.com/auth', $args ); // OBTENER TOKEN
			$api_response = json_decode($response['body']); 
		
			/* COTIZAR COSTO DE ENVIO */
			$url = 'https://api.enviopack.com/cotizar/costo';
		
	 		$body = array(
				'provincia' =>  $order->get_shipping_state(),
				'codigo_postal' => $codigo_postal,
				'peso' => $enviopack_settings[0]->enviopack_paquetes[0]->peso,    
				'servicio' => $servicio,
				'modalidad' => $modalidad,          
			);
		 
			$args = array(
					'body' => $body,
					'timeout' => '5',
					'redirection' => '5',
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array('Authorization' => 'Bearer ' . $api_response->token),
					'cookies' => array()
			);
		 
			$response = wp_remote_get( $url, $args );
		
			$opciones_envio = json_decode($response['body']);
		 		
			$opciones_envio_ok = array();
  		if($origin_sucursal == 'retiro'){
				$origin_sucursal = 'D';
			} else {
				$origin_sucursal = 'S';
			}
		
 			foreach($opciones_envio as $opciones){
					 
					if($origin_sucursal != $opciones->despacho){
						continue;
					}
					if(!empty($correo)){
						if($correo != $opciones->correo->id){
							continue;
						}
					}					

					if($opciones->modalidad == 'S'){
						$modalidad = ' - a Sucursal';
					} else {
						$modalidad = ' - a Domicilio';					
					}			

					if($opciones->servicio == 'N'){
						$servicio = ' - Estándar';
					} else if($opciones->servicio == 'P'){
						$servicio = ' - Prioritario';					
					}	else if($opciones->servicio == 'X'){
						$servicio = ' - Express';			
					} else if($opciones->servicio == 'R'){
						$servicio = ' - Devolucion';			
					}

					if($opciones->despacho == 'D'){
						$despacho = 'retiro por domicilio';
					} else {
						$despacho = 'despacho desde sucursal';
					}

					$id_envio = $opciones->correo->id . '-' . $opciones->modalidad . '-' . $opciones->servicio . '-' . $opciones->despacho;
					$nombre_envio = $opciones->correo->nombre . $modalidad. $servicio. '-' . $despacho. ' $'.$opciones->valor;
					$opciones_envio_ok[] = $id_envio .'+'. $nombre_envio;

				}
 
				if(!empty($opciones_envio_ok)){
					$opciones_envio_ok = json_encode($opciones_envio_ok);
 					update_post_meta( $order_id, 'opciones_enviopack', $opciones_envio_ok );							
				} else {
					if($modalidad == 'S'){
						$body = array(
							'provincia' =>  $order->get_shipping_state(),
							'codigo_postal' => $codigo_postal,
							'peso' => $enviopack_settings[0]->enviopack_paquetes[0]->peso,    
							'modalidad' => $modalidad,          
						);
						$args = array(
								'body' => $body,
								'timeout' => '5',
								'redirection' => '5',
								'httpversion' => '1.0',
								'blocking' => true,
								'headers' => array('Authorization' => 'Bearer ' . $api_response->token),
								'cookies' => array()
						);
						$response = wp_remote_get( $url, $args );
						$opciones_envio = json_decode($response['body']);		
						foreach($opciones_envio as $opciones){
							if($opciones->modalidad == 'S'){
								$modalidad = ' - a Sucursal';
							} else {
								$modalidad = ' - a Domicilio';					
							}			

							if($opciones->servicio == 'N'){
								$servicio = ' - Estándar';
							} else if($opciones->servicio == 'P'){
								$servicio = ' - Prioritario';					
							}	else if($opciones->servicio == 'X'){
								$servicio = ' - Express';			
							} else if($opciones->servicio == 'R'){
								$servicio = ' - Devolucion';			
							}

							if($opciones->despacho == 'D'){
								$despacho = 'retiro por domicilio';
							} else {
								$despacho = 'despacho desde sucursal';
							}

							$id_envio = $opciones->correo->id . '-' . $opciones->modalidad . '-' . $opciones->servicio . '-' . $opciones->despacho;
							$nombre_envio = $opciones->correo->nombre . $modalidad. $servicio. '-' . $despacho. ' $'.$opciones->valor;
							$opciones_envio_ok[] = $id_envio .'+'. $nombre_envio;		
						}
						if(!empty($opciones_envio_ok)){
							$opciones_envio_ok = json_encode($opciones_envio_ok);
							update_post_meta( $order_id, 'opciones_enviopack', $opciones_envio_ok );	
						}
					}
				}
	}

	add_action( 'wp_ajax_nopriv_obtener_wanderlust_enviopack', 'obtener_wanderlust_enviopack', 10);
 	add_action( 'wp_ajax_obtener_wanderlust_enviopack', 'obtener_wanderlust_enviopack', 10);


	/* OBTENER COSTOS */
	function obtener_wanderlust_enviopack(){
		global $woocommerce, $post, $wp_session;
		$order_id  = $_POST['dataid'];
	
		$order = wc_get_order( $order_id );	
		foreach( $order->get_items( 'shipping' ) as $item_id => $shipping_item_obj ){
			$shipping_item_data = $shipping_item_obj->get_data();
		}
 		$chosen_shipping = $shipping_item_data['method_id'];
		$enviopack_settings = get_post_meta($order_id, 'enviopack_settings', true);		
 		$order_totals  = get_post_meta($order_id, '_order_total', true ); 
		$chosen_shipping = str_replace('"','',$chosen_shipping);
		$chosen = explode('-', $chosen_shipping);
		if($chosen[1] == 'SS'){ // A SUCURSAL
			$id        =  $chosen[0];
			$modalidad = 'S';
			$servicio  = $chosen[2];
			$correo		 = $chosen[3];
			$cpostal   = $chosen[4];
		} else { // A DOMICILIO
			$despacho  = $chosen[2];
			$modalidad = 'D';
			$servicio  = $chosen[1];
		}
		$enviopack_settings = base64_decode($enviopack_settings);
		$enviopack_settings = json_decode($enviopack_settings);
 
		$method_id = $enviopack_settings[0]->settings;//62
		$settings = get_option( 'woocommerce_enviopack_wanderlust_'.$method_id.'_settings' );
 		$direccion_envio = $settings['origin_contacto'];		
 		$usa_seguro = $settings['usa_seguro'];		
 		$origin_sucursal = $settings['origin_sucursal'];	
		$altura_depto = $order->get_shipping_address_2();
 		$altura_depto = explode(',', $altura_depto);
		
				 				

 		$codigo_postal = $order->get_shipping_postcode();
 		if(!empty($cpostal)){
 			$codigo_postal = $cpostal;
 		}
  
 	 	$body = array(
 			'api-key' => $settings['api_key'],
 			'secret-key' => $settings['secret_key'],
		);

		$args = array(
				'body' => $body,
				'timeout' => '5',
				'redirection' => '5',
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'cookies' => array()
		);

		$response = wp_remote_post( 'https://api.enviopack.com/auth', $args ); // OBTENER TOKEN
		$api_response = json_decode($response['body']); 

		/* COTIZAR COSTO DE ENVIO */
 		$url = 'https://api.enviopack.com/cotizar/costo';
 
 	 	$body = array(
 			'provincia' =>  $order->get_shipping_state(),
 			'codigo_postal' => $codigo_postal,
 			'peso' => $enviopack_settings[0]->enviopack_paquetes[0]->peso,    
			'servicio' => $servicio,
			'modalidad' => $modalidad,         
		);
		 
		$args = array(
			'body' => $body,
			'timeout' => '5',
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array('Authorization' => 'Bearer ' . $api_response->token),
			'cookies' => array()
		);
	
		$response = wp_remote_get( $url, $args );
 		$opciones_envio = json_decode($response['body']);
  
 		if($origin_sucursal == 'retiro'){
			$origin_sucursal = 'D';
		} else {
			$origin_sucursal = 'S';
		}
		
		echo '<select id="opciones_envio" name="opciones_envio">';
			echo '<option value="">Seleccionar Envio</option>';
			foreach($opciones_envio as $opciones){
				
				if($origin_sucursal != $opciones->despacho){
					continue;
				}
 				if(!empty($correo)){
					if($correo != $opciones->correo->id){
						continue;
					}
				}
				
 				if($opciones->modalidad == 'S'){
					$modalidad = ' - a Sucursal';
				} else {
					$modalidad = ' - a Domicilio';					
				}			
			
				if($opciones->servicio == 'N'){
					$servicio = ' - Estándar';
				} else if($opciones->servicio == 'P'){
					$servicio = ' - Prioritario';					
				}	else if($opciones->servicio == 'X'){
					$servicio = ' - Express';			
				} else if($opciones->servicio == 'R'){
					$servicio = ' - Devolucion';			
				}
				
				if($opciones->despacho == 'D'){
					$despacho = 'retiro por domicilio';
				} else {
						$despacho = 'despacho desde sucursal';				
				}
			
				$id_envio = $opciones->correo->id . '-' . $opciones->modalidad . '-' . $opciones->servicio . '-' . $opciones->despacho;
				$nombre_envio = $opciones->correo->nombre . $modalidad. $servicio. '-' . $despacho. ' $'.$opciones->valor;
				echo '<option value="'.$id_envio.'">'.$nombre_envio.'</option>';
 
			}
 		echo '</select>';					
		
		die();
	}