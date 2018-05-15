<tr valign="top" id="packing_options">
	<th scope="row" class="titledesc">En base a</th>
	<td class="forminp">
		<style type="text/css">
 
		</style>
		<table class="wanderlust_boxes">
			<select class="select packing_method" name="woocommerce_enviopack_packing_method" id="woocommerce_enviopack_packing_method" style="">
 				<option value="per_item" selected="selected">Estándar (artículos individuales).</option>
			<?php
				$api_key= get_option( 'enviopack_api_key');
				$secret_key = get_option( 'enviopack_secret_key');
				if(!empty($secret_key)){
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

					$response = wp_remote_get( 'https://api.enviopack.com/tipos-de-paquetes?access_token='.$api_response->token );
					$enviopack_response = json_decode($response['body']);  	
					if($enviopack_response){
						foreach($enviopack_response as $caja){
							echo '<option value="'.$caja->alto.'x'.$caja->ancho.'x'.$caja->largo.'">'.$caja->alto.'x'.$caja->ancho.'x'.$caja->largo.'</option>';
						}					
					}					
				}
				?>
				
			</select>
			<p class="description">En caso de que un item no tenga dimensiones o peso, se va a calcular el costo del envio en base al paquete predeterminado dentro de "Mis Paquetes" en su cuenta de EnvioPack.</p>
		</table>
		
		<?php 
			$enviopack_origin_contacto = get_option('enviopack_origin_contacto'); 
			if(!empty($enviopack_origin_contacto)){
				echo '<input type="hidden" name="origin_contacto" id="origin_contacto" value="'.$enviopack_origin_contacto.'" >';
			} else {
				echo '<input type="hidden" name="origin_contacto" id="origin_contacto" >';
			}
		?>
		

		
		<script type="text/javascript">
 			jQuery(document).ready(function () {
				
				if(jQuery('#origin_contacto').val()){
					jQuery("#woocommerce_enviopack_wanderlust_origin_contacto").html('<option value="">'+jQuery('#origin_contacto').val() + '</option>');	
				}
				
				
 				jQuery('#woocommerce_enviopack_wanderlust_secret_key').focusout(function () {
					if( jQuery(this).val() ) {
						if( jQuery('#woocommerce_enviopack_wanderlust_api_key').val() ) {
 							jQuery('#save_keys').insertAfter( jQuery('#woocommerce_enviopack_wanderlust_secret_key').next('.description'));
							jQuery('#save_keys').fadeIn(300);

						}
						 
					}
				});
				
				
				jQuery("#woocommerce_enviopack_wanderlust_origin_contacto").click(function(){  
					var secret_key = jQuery('#woocommerce_enviopack_wanderlust_secret_key').val();
					var api_key = jQuery('#woocommerce_enviopack_wanderlust_api_key').val();
					if ( jQuery("#woocommerce_enviopack_wanderlust_origin_contacto").hasClass("result") ) {
 
					} else {
						var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
						jQuery.ajax({
										type: 'POST',
										cache: false,
										url: ajaxurl,
										data: {
											action: 'enviopack_origin_contacto',
											secret_key: secret_key,
											api_key: api_key,
										},
										success: function(data, textStatus, XMLHttpRequest){
											jQuery("#woocommerce_enviopack_wanderlust_origin_contacto").html(data);
											jQuery("#woocommerce_enviopack_wanderlust_origin_contacto").addClass('result');
 											jQuery("#origin_contacto").val(jQuery("#woocommerce_enviopack_wanderlust_origin_contacto").text());

											
										},
										error: function(MLHttpRequest, textStatus, errorThrown){
											console.log(errorThrown);
										}
								});						
					}
					return false;			
				});				
				

				jQuery("#save_keys").click(function(){  
					var secret_key = jQuery('#woocommerce_enviopack_wanderlust_secret_key').val();
					var api_key = jQuery('#woocommerce_enviopack_wanderlust_api_key').val();
					jQuery('#save_keys').html('Corroborando API..')
					var data = {
						'action': 'enviopack_save_keys',
						'secret_key': secret_key,
						'api_key': api_key,
					};

					jQuery.post(ajaxurl, data, function(response) {
						jQuery('#save_keys').html('OK').fadeOut(2000);
						jQuery("#woocommerce_enviopack_wanderlust_origin_contacto").click();
					});
					return false;			
				});
				
			});
		</script>
	</td>
</tr>

<div id="save_keys" class="button" style="display:none;">Confirmar Keys</div>