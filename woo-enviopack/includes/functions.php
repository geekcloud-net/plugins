<?php
	add_action('wp_ajax_enviopack_check_sucursales', 'enviopack_check_sucursales', 1);
	add_action('wp_ajax_nopriv_enviopack_check_sucursales', 'enviopack_check_sucursales', 1);
	add_action('wp_ajax_enviopack_save_keys', 'enviopack_save_keys', 1);
	add_action('wp_ajax_nopriv_enviopack_save_keys', 'enviopack_save_keys', 1);
	add_action('wp_ajax_enviopack_origin_contacto', 'enviopack_origin_contacto', 1);
	add_action('wp_ajax_nopriv_enviopack_origin_contacto', 'enviopack_origin_contacto', 1);
  add_action('wp_footer', 'enviopack_only_numbers_enviopacks');
	add_action('woocommerce_checkout_update_order_meta', 'order_sucursal_main_update_order_meta_enviopack', 10);
	add_action('add_meta_boxes', 'woocommerce_enviopack_box_add_box');
	add_action('admin_footer', 'woocommerce_enviopack_orders_pageb', 1);
	add_action('wp_ajax_enviopack_bulk_save', 'enviopack_bulk_save', 1);
	add_action('wp_ajax_nopriv_enviopack_bulk_save', 'enviopack_bulk_save', 1);
	add_action('woocommerce_checkout_process', 'checkout_enviopack_fields_process');
	add_action('wp_ajax_enviopack_get_etiqueta', 'enviopack_get_etiqueta', 1);
	add_action('wp_ajax_nopriv_enviopack_get_etiqueta', 'enviopack_get_etiqueta', 1);

	function enviopack_save_keys(){
		if(!empty($_POST['secret_key'])){
			$secret_key = sanitize_text_field( $_POST['secret_key'] );
			update_option( 'enviopack_secret_key', $secret_key );
		}
		if(!empty($_POST['api_key'])){
			$api_key = sanitize_text_field( $_POST['api_key'] );
			update_option( 'enviopack_api_key', $api_key );
		}

		exit();
	}

  function enviopack_origin_contacto(){
		if(!empty($_POST['secret_key'])){
			$secret_key = sanitize_text_field( $_POST['secret_key'] );
		} else {
			exit();
		}
		if(!empty($_POST['api_key'])){
			$api_key = sanitize_text_field( $_POST['api_key'] );
		} else {
			exit();
		} 
		
 		$body = array(
 			'api-key' => $api_key,
 			'secret-key' => $secret_key,
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
 
		$response = wp_remote_get( 'https://api.enviopack.com/direcciones-de-envio?access_token='.$api_response->token );
 		$enviopack_response = json_decode($response['body']);  
		
 		echo '<select id="woocommerce_enviopack_wanderlust_origin_contacto" name="woocommerce_enviopack_wanderlust_origin_contacto">';

		foreach($enviopack_response as $domicilios){
			echo '<option value="'. $domicilios->id.'">'. $domicilios->calle .' ' . $domicilios->numero.' ' . $domicilios->piso .' ' . $domicilios->depto.' - ' . $domicilios->localidad.'</option>';
		}
 		echo '</select>';
 
		die();
	}
 
	function enviopack_handle_callback(){ // ESCUCHA API POR NOTIFICACIONES
			global $woocommerce;

 			http_response_code(200);	
 			header("HTTP/1.1 200 OK");
 			header("Status: 200 All rosy");
		exit();
	}


	add_filter( 'woocommerce_default_address_fields' , 'enviopack_default_address_customization' );
	function enviopack_default_address_customization( $address_fields ) {
			$address_fields['address_1']['label'] = 'Calle';
			$address_fields['address_1']['maxlength'] = 30;
			$address_fields['address_1']['placeholder'] = 'Nombre de la Calle';
			$address_fields['address_2']['label'] = 'Número, Piso, Unidad';
			$address_fields['address_2']['placeholder'] = '1151, 3, B';
			$address_fields['address_2']['required'] = true;
			$address_fields['address_1']['class'] = array( 'form-row-first' );
			$address_fields['address_2']['class'] = array( 'form-row-last' );
			$address_fields['city']['type'] = 'select';
			$address_fields['city']['label'] = 'Localidad';
			$address_fields['city']['options'] = array(
					'nada' => 'Seleccionar...',
			);
			ksort($address_fields['city']['options']);
			$address_fields['city']['options']['otra'] = 'Otra...';
			$address_fields['city']['class'] = array( 'form-row-last' );
			$address_fields['state']['class'] = array( 'form-row-first' );
			return $address_fields;
	}

	function enviopack_check_sucursales() {
		global $woocommerce;
		$api_key= get_option( 'enviopack_api_key');
 		$secret_key = get_option( 'enviopack_secret_key');
 	 	$body = array(
 			'api-key' => $api_key,
 			'secret-key' => $secret_key,
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

 		if (isset($_POST['localidad_id'])) {
			$url = 'https://api.enviopack.com/cotizar/precio/a-sucursal'; //OBTENER COSTOS DE SUCURSAL
 
	 		$body = array(
 				'provincia' => $_POST['provincia_id'],
 				'localidad' => $_POST['localidad_id'],
 				'peso' => $_COOKIE['final_weight'],           
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
			$output = base64_encode($response['body']);
 	 		setcookie('enviopack_sucursales', $output, time() + (86400 * 30), "/"); // 86400 = 1 day
			die();
		}
		
		
		if (isset($_POST['provincia'])) {
				$response = wp_remote_get( 'https://api.enviopack.com/localidades?id_provincia='.$_POST['provincia'].'&access_token=' . $api_response->token );
        $enviopack_response = json_decode($response['body']);
 				echo '<select id="billing_city" name="billing_city">';			
					foreach($enviopack_response as $sucursales){
						echo '<option value="'.$sucursales->nombre.'" data-id="'.$sucursales->id.'">'. $sucursales->nombre .'</option>';
					}
				echo '</select>';
 			die();
		}
	}


	function enviopack_only_numbers_enviopacks(){ 
		if ( is_checkout() ) { ?>
 			<script type="text/javascript">
 				jQuery(document).ready(function () {  
					
				jQuery(document).on('change', '#billing_city', function() {
						if(jQuery(this).val() == 'otra'){
							jQuery('#billing_city').replaceWith('<input type="text" name="billing_city" id="billing_city">');
						} else {
							jQuery.ajax({
									type: 'POST',
									cache: false,
									url: wc_checkout_params.ajax_url,
									data: {
										action: 'enviopack_check_sucursales',
										localidad_id: jQuery('#billing_city').find(':selected').attr('data-id'),
										provincia_id: jQuery('#billing_state').val(),
										cp: jQuery('#billing_postcode').val(),
									},
									success: function(data, textStatus, XMLHttpRequest){

  									jQuery('body').trigger('update_checkout');


									},
									error: function(MLHttpRequest, textStatus, errorThrown){
										console.log(errorThrown);
									}
							});
							return false;								
						}
				});

				jQuery('#billing_state').change(function(){
 					jQuery('#billing_city').replaceWith('<select name="billing_city" id="billing_city" class="select " autocomplete="address-level2" data-allow_clear="true" data-placeholder="Choose an option"><option value="0">Cargando Localidades...</option></select>');
 					
					jQuery.ajax({
				    		type: 'POST',
				    		cache: false,
				    		url: wc_checkout_params.ajax_url,
				    		data: {
 									action: 'enviopack_check_sucursales',
									provincia: jQuery(this).val(),
				    		},
				    		success: function(data, textStatus, XMLHttpRequest){
									
									if (!data.length){
										jQuery('#billing_city').replaceWith('<input type="text" name="billing_city" id="billing_city">');	 
									} else {
 											jQuery('#billing_city').html(data);
											jQuery("#billing_city").prepend("<option value='otra'>Otra...</option>");
 											jQuery("#billing_city").prepend("<option value='nada' selected='selected'>Seleccionar...</option>");										
									}
									

 
 								},
								error: function(MLHttpRequest, textStatus, errorThrown){
									console.log(errorThrown);
								}
					});
				  return false;					
				});				
					
 					
				jQuery('#calc_shipping_postcode').attr({ maxLength : 4 });
				jQuery('#billing_postcode').attr({ maxLength : 4 });
				jQuery('#shipping_postcode').attr({ maxLength : 4 });

		          jQuery("#calc_shipping_postcode").keypress(function (e) {
		          if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
		          	return false;
		          }
		          });
		          jQuery("#billing_postcode").keypress(function (e) { 
		          if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) { 
		          return false;
		          }
		          });
		          jQuery("#shipping_postcode").keypress(function (e) {  
		          if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
		          return false;
		          }
		          });
					
				});

			</script>

		<?php }
	}	//ends only_numbers_enviopacks


	 /**
	 * Update the order meta with field value
	 */
	function order_sucursal_main_update_order_meta_enviopack( $order_id ) {
		global $woocommerce, $post;
 			if (isset($_COOKIE['enviopack_settings'])) {
				update_post_meta( $order_id, 'enviopack_settings', $_COOKIE['enviopack_settings'] );	
			}
 	}

 
	
	/**
	 * Show info at order
	*/
	function woocommerce_enviopack_box_add_box() {
		add_meta_box( 'woocommerce-enviopack-box', __( 'EnvioPack Etiquetas', 'woocommerce-enviopack' ), 'woocommerce_enviopack_box_create_box_content', 'shop_order', 'side', 'default' );
	}
	function woocommerce_enviopack_box_create_box_content() {
		global $post;
			$site_url = get_site_url();
		  $order = wc_get_order( $post->ID );
			$shipping = $order->get_items( 'shipping' );
		
  		
			echo '<div class="enviopack-single">';
			echo '<strong>Modalidad</strong></br>';
			foreach($shipping as $method){
				echo $method['name'];
			}
			echo '</div>';
		
		//ETIQUETA
		$enviopack_shipping_label_tracking = get_post_meta($post->ID, '_tracking_number', true);
		$etiqueta = get_post_meta($post->ID, 'etiqueta_enviopack', true);
		$enviopack_estado_numeroenvio = get_post_meta($post->ID, '_tracking_number', true);
		if(empty($enviopack_estado_numeroenvio)){
			$enviopack_estado_numeroenvio = get_post_meta($post->ID, 'enviopack_estado_numeroenvio', true);
		}
		
 
 			if(!empty($etiqueta)){
				echo  '<div style="position: relative; width: 100%; height: 60px;"><a id="imprimir_etiqueta" data-order="'.$post->ID.'" data-id="'.$enviopack_estado_numeroenvio.'" style=" width: 225px;text-align: center;background: #643494;color: white;padding: 10px;margin: 10px;float: left;text-decoration: none;" href="#">IMPRIMIR ETIQUETA</a></div>';			
			}
		
			if(!empty($etiqueta)){
				echo  '<div style="position: relative; width: 100%; height: 60px;" ><a style=" width: 225px; text-align: center;background: #643494;color: white;padding: 10px;margin: 10px;float: left;text-decoration: none;" href="https://seguimiento.enviopack.com/'. $enviopack_estado_numeroenvio .'" target="_blank">Seguir Paquete</a></div>';
 				echo  '<div style="position: relative; width: 100%; height: 60px;" >Nro. Seguimiento: '.$enviopack_estado_numeroenvio.'</div>';
			}		 			
		
		if (empty($etiqueta)){ ?>

			<style type="text/css">
				#generar-enviopack, #editar-enviopack, #manual-enviopack-generar, #obtener-enviopack  {
					background: #643494;
					color: white;
					width: 100%;
					text-align: center;
					height: 40px;
					padding: 0px;
					line-height: 37px;
					margin-top: 20px;
					clear:both;
				}
				#editar-enviopack {
					background: #d24040;
				}
				#manual-enviopack {
					display:none;
				}
				 
			</style>
			<div class="enviopack-single-label"> </div>	

			<div id="obtener-enviopack" class="button" data-id="<?php echo $post->ID; ?>">Obtener Costos</div>
			<div id="generar-enviopack" style="display:none;" class="button" data-id="<?php echo $post->ID; ?>">Generar Etiqueta</div>
			<div id="editar-enviopack" style="display:none;"  class="button" data-id="<?php echo $post->ID; ?>">Generar Manualmente</div>

			<div id="manual-enviopack">
				<h4>Detalles Paquete</h4>
 				<p class="form-field" style="width: 45%;float: left;margin-top: 0px;">
					<label for="peso">Peso KG</label>
					<input type="text" class="short" style="" name="peso" id="peso" value="" placeholder=""> 
				</p>  
				
 				<p class="form-field" style="width: 45%;float: right;margin-top: 0px;">
					<label for="alto">Alto CM</label>
					<input type="text" class="short" style="" name="alto" id="alto" value="" placeholder=""> 
				</p>  
 				<p class="form-field" style="width: 45%;float: left;margin-top: 0px;">
					<label for="largo">Largo CM</label>
					<input type="text" class="short" style="" name="largo" id="largo" value="" placeholder=""> 
				</p>  
 				<p class="form-field" style="width: 45%;float: right;margin-top: 0px;">
					<label for="ancho">Ancho CM</label>
					<input type="text" class="short" style="" name="ancho" id="ancho" value="" placeholder=""> 
				</p>  
				<p class="form-field">
					<label for="operativa">Operativa</label>
					<?php
						echo '<select id="operativas_ok" name="operativas_ok">';
						
								echo '<option value="285538">Envío a Domicilio - enviopack </option>';
								echo '<option value="285539">Envío a Sucursal - enviopack</option>';
						echo '</select>';																					 
					?>
				</p>  
 				<div id="manual-enviopack-generar"  class="button" data-id="<?php echo $post->ID; ?>">Generar Etiqueta Manual</div>

			</div>


			<script type="text/javascript">
				jQuery('body').on('change', '#opciones_envio',function(e){
					jQuery('#generar-enviopack').fadeIn();	
				});						
					
				jQuery('body').on('click', '#generar-enviopack',function(e){ 
					e.preventDefault();
					var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
					var dataid = jQuery(this).data("id");
					var opciones_envio = jQuery('#opciones_envio').val();
					jQuery.ajax({
						type: 'POST',
						cache: false,
						url: ajaxurl,
						data: {
							action: 'purchase_order_wanderlust_enviopack',
							dataid: dataid,
							opciones_envio: opciones_envio,
						},
						success: function(data, textStatus, XMLHttpRequest){ 
							jQuery(".enviopack-single-label").fadeIn(400);
							jQuery(".enviopack-single-label").html('');
							jQuery(".enviopack-single-label").append(data);
						},
						error: function(MLHttpRequest, textStatus, errorThrown){ }
					});
				});	
				
				jQuery('body').on('click', '#obtener-enviopack',function(e){ 
					e.preventDefault();
					var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
					var dataid = jQuery(this).data("id");
					jQuery(this).hide();
					jQuery.ajax({
						type: 'POST',
						cache: false,
						url: ajaxurl,
						data: {action: 'obtener_wanderlust_enviopack',dataid: dataid,},
						success: function(data, textStatus, XMLHttpRequest){ 
							jQuery(".enviopack-single-label").fadeIn(400);
							jQuery(".enviopack-single-label").html('');
							jQuery(".enviopack-single-label").append(data);
						},
						error: function(MLHttpRequest, textStatus, errorThrown){ }
					});
				});					
				
 
				jQuery('body').on('click', '#editar-enviopack',function(e){ 
					e.preventDefault();
					var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
					var dataid = jQuery(this).data("id");
					jQuery('#manual-enviopack').fadeIn();
					
 
				});			
				
				jQuery('body').on('click', '#manual-enviopack-generar',function(e){ 
					e.preventDefault();
					var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
					var dataid = jQuery('#editar-enviopack').data("id");
					var sucursal_destino = jQuery('#sucursal_destino').val();
					var operativa = jQuery('#operativas_ok').val();
					var largo = jQuery('#largo').val();
					var ancho = jQuery('#ancho').val();
					var alto = jQuery('#alto').val();
					var peso = jQuery('#peso').val();
	 
					jQuery.ajax({
						type: 'POST',
						cache: false,
						url: ajaxurl,
						data: {
							action: 'purchase_order_wanderlust_enviopack_manual',
							dataid: dataid,
							sucursal_destino: sucursal_destino,
							operativa: operativa,
							largo: largo,
							ancho: ancho,
							alto: alto,
							peso: peso,						
						},
						success: function(data, textStatus, XMLHttpRequest){ 
							jQuery(".enviopack-single-label").fadeIn(400);
							jQuery(".enviopack-single-label").html('');
							jQuery(".enviopack-single-label").append(data);
							//lenviopacktion.reload();
						},
						error: function(MLHttpRequest, textStatus, errorThrown){ }
					});
				});				
				
			</script>
		<?php }  
	}

 
	function enviopack_admin_notice() {
		global $wp_session;
			?>
			<div class="notice error my-acf-notice is-dismissible" >
					<p><?php print_r($wp_session['enviopack_notice'] ); ?></p>
			</div>
			<?php
	}


	function woocommerce_enviopack_orders_pageb() { ?>
		<script type="text/javascript"> 
				jQuery('body').on('change', '#bulk_enviopack',function(e){ 
					e.preventDefault();
					var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
					var objeto = jQuery(this);
					var envioid = objeto.find(":selected").val();
 					var dataid = objeto.find(":selected").data("order");
					
					jQuery.ajax({
						type: 'POST',
						cache: false,
						url: ajaxurl,
						data: {action: 'enviopack_bulk_save',envioid: envioid,dataid:dataid,},
						success: function(data, textStatus, XMLHttpRequest){ 
							console.log(data);
						},
						error: function(MLHttpRequest, textStatus, errorThrown){ }
					});
				});			
  			jQuery('body').on('click', '#imprimir_etiqueta',function(e){ 
					e.preventDefault();
					var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
					var dataid = jQuery(this).data("id");
					var order = jQuery(this).data("order");
					jQuery.ajax({
						type: 'POST',
						cache: false,
						url: ajaxurl,
						data: {
							action: 'enviopack_get_etiqueta',
							dataid: dataid,
							order: order,
						},
						success: function(data, textStatus, XMLHttpRequest){ 
 							window.open(data, '_blank');
 						},
						error: function(MLHttpRequest, textStatus, errorThrown){ }
					});
				});			
			</script>
	<?php
	}

	function checkout_enviopack_fields_process() {
		global $woocommerce, $wp_session;
		
		if(!empty($_POST['billing_address_2'])){
			$datos_calle = explode(',', $_POST['billing_address_2']);
			$numero = preg_match('/\s/',$datos_calle[0]);
 			if($numero == 1){
				wc_add_notice( __( 'Por favor, ingresar el siguiente formato (Número, Piso, Unidad).' ), 'error' );
			} else {
				$validar_numero = preg_replace('/\s+/', '', $datos_calle[0]);
				if ( strlen( $validar_numero ) <= 5 ) {
					
				} else {
					wc_add_notice( __( 'La altura de la calle, debe ser menor a 5 caracteres. Ej: 6818' ), 'error' );
				}
			}
			
			if(!empty($datos_calle[1])){
				$validar_piso = preg_replace('/\s+/', '', $datos_calle[1]);
				if ( strlen( $validar_piso ) <= 6 ) { 
					
				} else {
					wc_add_notice( __( 'El piso, debe ser menor a 6 caracteres. Ej: 8' ), 'error' );
				}
				
			}
			if(!empty($datos_calle[2])){
 				$validar_depto = preg_replace('/\s+/', '', $datos_calle[2]);
				if ( strlen( $validar_depto ) <= 4 ) {
					
				} else {
					wc_add_notice( __( 'El piso, debe ser menor a 4 caracteres. Ej: B' ), 'error' );
				}
				
			}			
		} else {
			wc_add_notice( __( 'Por favor, ingresar número de la calle en el siguiente formato (6818,4,D)' ), 'error' );
		}
 
		if($_POST['billing_city'] == 'nada'){
		 wc_add_notice( __( 'Por favor, seleccionar una Localidad válida.' ), 'error' ); 
		}
	}

	function enviopack_get_etiqueta(){
      $api_key= get_option( 'enviopack_api_key');
      $secret_key = get_option( 'enviopack_secret_key');
      $body = array(
        'api-key' => $api_key,
        'secret-key' => $secret_key,
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
			$save_path = plugin_dir_path ( __FILE__ ) . 'etiquetas/';
			$save_url = plugin_dir_url(dirname(__FILE__)) . 'includes/etiquetas/';
			
			$order_id = $_POST['order'];
 
			$response = wp_remote_get( 'https://api.enviopack.com/envios/etiquetas?access_token='. $api_response->token .'&ids='.$_POST['dataid']);
			$etiquetas = $response['body']; 
   
			$fp = fopen($save_path . $_POST['dataid'] . '.pdf', 'wb');
			fwrite($fp, $etiquetas); 
			fclose($fp);


 			//echo $etiquetas;
 			 			echo $save_url . $_POST['dataid'] .'.pdf';
			
 		
		die();
	}

?>