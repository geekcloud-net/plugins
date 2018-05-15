<?php
	add_action( 'wp_ajax_nopriv_purchase_order_wanderlust_enviopack', 'purchase_order_wanderlust_enviopack', 10);
 	add_action( 'wp_ajax_purchase_order_wanderlust_enviopack', 'purchase_order_wanderlust_enviopack', 10);

	/* GENERAR ETIQUETA */
	function purchase_order_wanderlust_enviopack($order_id) { 
		global $woocommerce, $post, $wp_session;
		if(empty($order_id)){
			$order_id  = $_POST['dataid'];
		}

		$chosen_shipping = $_POST['opciones_envio'];
		
		$enviopack_estado_numeroenvio = get_post_meta($order_id, 'enviopack_estado_numeroenvio', true);
		if(empty($enviopack_estado_numeroenvio)){

			if(empty($chosen_shipping)){
				$chosen_shipping = get_post_meta($order_id, 'bulk_enviopack', true);
			}

			$order = wc_get_order( $order_id );	
			foreach( $order->get_items( 'shipping' ) as $item_id => $shipping_item_obj ){
				$shipping_item_data = $shipping_item_obj->get_data();
			}

			$chosen_shipping_id = $shipping_item_data['method_id'];
			$enviopack_settings = get_post_meta($order_id, 'enviopack_settings', true);		
			$order_totals  = get_post_meta($order_id, '_order_total', true ); 

			$chosen_shipping = str_replace('"','',$chosen_shipping);

			$chosen = explode('-', $chosen_shipping);
			$chosensuc = explode('-', $chosen_shipping_id);
			if($chosensuc[1] == 'SS'){ // A SUCURSAL
				$modalidad = 'S';
				$servicio  = $chosen[2];
				$correo		 = $chosen[3];
				$id =  $chosensuc[0];
			} else { // A DOMICILIO
				$despacho  = $chosen[3];
				$modalidad = $chosen[1];
				$servicio  = $chosen[2];
				$correo		 = $chosen[0];			
			}		

			if($chosen_shipping == 'regla_enviopack' || empty($chosen_shipping)){
				$confirmado = false;
				if($chosensuc[0] == 'Envio D'){
					$modalidad = 'D';
				} else {
					$modalidad = 'S';
				}
				$servicio  = $chosensuc[1];
			} else {
				$confirmado = true;
			}

			$enviopack_settings = base64_decode($enviopack_settings);
			$enviopack_settings = json_decode($enviopack_settings);

			$method_id = $enviopack_settings[0]->settings;//62
			$settings = get_option( 'woocommerce_enviopack_wanderlust_'.$method_id.'_settings' );
			$direccion_envio = $settings['origin_contacto'];		
			$usa_seguro = $settings['usa_seguro'];		
			
 
			$altura_depto = $order->get_shipping_address_2();
			$altura_depto = explode(',', $altura_depto);

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

			/* GENERAR PEDIDO */
			$body_ok = array(
				'id_externo' => $order_id,
				'plataforma' => 'woocommerce',
				'nombre' => $order->get_shipping_first_name(),
				'apellido' => $order->get_shipping_last_name(),
				'email' => $order->get_billing_email(),
				'telefono' => $order->get_billing_phone(),
				'celular' => $order->get_billing_phone(),
				'monto' => $order_totals,
				'fecha_alta' => date('c'),
				'pagado' => false,
				'provincia' => $order->get_shipping_state(),
				'localidad' => $order->get_shipping_city(),	
			);
			$body_ok = json_encode($body_ok);
			$args_ok = array(
					'body' => $body_ok,
					'timeout' => '30',
					'redirection' => '5',
					'blocking' => true,
					'headers' => array(   
						'Accept' => 'application/json',
						'Content-Type' => 'application/json',
						'Authorization' => 'Bearer ' . $api_response->token
					),
					'cookies' => array()
			);

			$response = wp_remote_post( 'https://api.enviopack.com/pedidos', $args_ok ); // OBTENER TOKEN		

			update_post_meta($order_id, 'enviopack_pedido', $response['body']);
			$pedido = json_decode($response['body']);

			$pedido_id = get_post_meta($order_id, 'enviopack_pedido_id', true);
			if(empty($pedido_id)){
				update_post_meta($order_id, 'enviopack_pedido_id', $pedido->id);
			}

			if($pedido->code == 400 || $etiquetas->code == 404){
				update_post_meta($order_id, 'enviopack_error_pedidos', $pedido->message);
			}  

			if(empty($pedido->id)){
				$pedido_id = get_post_meta($order_id, 'enviopack_pedido_id', true);
			} else {
				$pedido_id = $pedido->id;
			}
			/* GENERAR ENVIO */
			$url = 'https://api.enviopack.com/envios'; 
			if($modalidad == 'S'){
				$auth = array(
					'pedido' => $pedido_id,
					'direccion_envio' => $direccion_envio,
					'destinatario' => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
					'observaciones' => '',
					'usa_seguro' => $usa_seguro,
					'confirmado' => $confirmado,
					'paquetes' => array(
						array(
							'alto'  => $enviopack_settings[0]->enviopack_paquetes[0]->alto,
							'ancho' => $enviopack_settings[0]->enviopack_paquetes[0]->ancho,
							'largo' => $enviopack_settings[0]->enviopack_paquetes[0]->largo,
							'peso'  => $enviopack_settings[0]->enviopack_paquetes[0]->peso,
						),
					),
					'despacho' => $despacho,
					'modalidad' => $modalidad,
					'servicio' => $servicio,		
					'sucursal' => $id,
				); 
			} else if($modalidad == 'D'){
				$auth = array(
					'pedido' => $pedido_id,
					'direccion_envio' => $direccion_envio,
					'destinatario' => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
					'observaciones' => '',
					'usa_seguro' => $usa_seguro,
					'confirmado' => $confirmado,
					'paquetes' => array(
						array(
							'alto'  => $enviopack_settings[0]->enviopack_paquetes[0]->alto,
							'ancho' => $enviopack_settings[0]->enviopack_paquetes[0]->ancho,
							'largo' => $enviopack_settings[0]->enviopack_paquetes[0]->largo,
							'peso'  => $enviopack_settings[0]->enviopack_paquetes[0]->peso,
						),
					),
					'despacho' => $despacho,
					'modalidad' => $modalidad,
					'servicio' => $servicio,
					'correo' => $correo,						
					'calle' => $order->get_shipping_address_1(),
					'numero' => $altura_depto[0],				
					'piso' => $altura_depto[1],				
					'depto' => $altura_depto[2],				
					'codigo_postal' => $order->get_shipping_postcode(),				
					'provincia' => $order->get_shipping_state(),				
					'localidad' => $order->get_shipping_city(),			
				); 			
			} else if($confirmado == false){
				$auth = array(
					'pedido' => $pedido_id,
					'direccion_envio' => $direccion_envio,
					'destinatario' => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
					'observaciones' => '',
					'usa_seguro' => $usa_seguro,
					'confirmado' => $confirmado,
					'paquetes' => array(
						array(
							'alto'  => $enviopack_settings[0]->enviopack_paquetes[0]->alto,
							'ancho' => $enviopack_settings[0]->enviopack_paquetes[0]->ancho,
							'largo' => $enviopack_settings[0]->enviopack_paquetes[0]->largo,
							'peso'  => $enviopack_settings[0]->enviopack_paquetes[0]->peso,
						),
					),
					'modalidad' => $modalidad,
					'calle' => $order->get_shipping_address_1(),
					'numero' => $altura_depto[0],				
					'piso' => $altura_depto[1],				
					'depto' => $altura_depto[2],				
					'codigo_postal' => $order->get_shipping_postcode(),				
					'provincia' => $order->get_shipping_state(),				
					'localidad' => $order->get_shipping_city(),			
				); 				
			}

			$args = array(
					'body' => json_encode($auth),
					'timeout' => '5',
					'redirection' => '5',
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(   
						'Accept' => 'application/json',
						'Content-Type' => 'application/json',
						'Authorization' => 'Bearer ' . $api_response->token
					),
					'cookies' => array()
			);


			$response = wp_remote_post( $url, $args );		
			$enviopack_response = $response['body'];					
			update_post_meta($order_id, 'enviopack_envio', $enviopack_response);
			$enviopack_response = json_decode($enviopack_response);		

			if($enviopack_response->code == 400 || $etiquetas->code == 404){
				update_post_meta($order_id, 'enviopack_error_envios', $enviopack_response->message);
			}		
			/* GENERAR ETIQUETAS */
			$save_path = plugin_dir_path ( __FILE__ ) . 'etiquetas/';
			$save_url = plugin_dir_url(dirname(__FILE__)) . 'includes/etiquetas/';
			
 
			$response = wp_remote_get( 'https://api.enviopack.com/envios/etiquetas?access_token='. $api_response->token .'&ids='.$enviopack_response->id );
			$etiquetas = $response['body']; 

			if($response['response']['code'] == 400 || $response['response']['code'] == 404){
				update_post_meta($order_id, 'enviopack_error_etiquetas', $response['response']['message']);
				return $response['response']['message']; 
			} else {
				$fp = fopen($save_path . $enviopack_response->id . '.pdf', 'wb');
				fwrite($fp, $etiquetas); 
				fclose($fp);


				$date = strtotime( date('Y-m-d') );

				update_post_meta($order_id, '_tracking_number',  $enviopack_response->id);
				update_post_meta($order_id, '_custom_tracking_provider', $enviopack_response->correo); 
				update_post_meta($order_id, '_custom_tracking_link', 'https://seguimiento.enviopack.com/');
				update_post_meta($order_id, '_date_shipped', $date);
				update_post_meta( $order_id, 'etiqueta_enviopack', $save_url . $enviopack_response->id .'.pdf');
				update_post_meta( $order_id, 'enviopack_estado_numeroenvio', $enviopack_response->id);

				if(empty($_POST['dataid']) && !empty($enviopack_response->id)){
					$order->update_status('completed', 'order_note'); //check this
					return $enviopack_response->id;
				} else {
					echo  '<div  style="position: relative; width: 100%; height: 60px;" ><a style=" width: 225px; text-align: center;background: #643494;color: white;padding: 10px;margin: 10px;float: left;text-decoration: none;" href="'. $save_url . $enviopack_response->id .'.pdf" target="_blank">IMPRIMIR ETIQUETA</a></div>';
					echo  '<div  style="position: relative; width: 100%; height: 60px;" ><a style=" width: 225px; text-align: center;background: #643494;color: white;padding: 10px;margin: 10px;float: left;text-decoration: none;" href="#" target="_blank">'.$enviopack_response->tracking_number.'</a></div>';
				}			
			}
 		}
		die();
	}