<?php

	add_filter('manage_edit-shop_order_columns', 'cw_add_order_enviopack_column_header');
	add_action( 'manage_shop_order_posts_custom_column', 'cw_add_order_enviopack_column_content' );
  add_filter( 'bulk_actions-edit-shop_order', 'register_enviopack_bulk_actions' );
  add_filter( 'handle_bulk_actions-edit-shop_order', 'enviopack_action_handler', 10, 3 );
  add_action( 'admin_notices', 'enviopack_action_admin_notice' );


	function cw_add_order_enviopack_column_header($columns){
			$new_columns = array();
			foreach ($columns as $column_name => $column_info) {
					$new_columns[$column_name] = $column_info;
					if ('order_total' === $column_name) {
							$new_columns['order_enviopack'] = 'EnvioPack';
					}
			}
			return $new_columns;
	}


	function cw_add_order_enviopack_column_content( $column ) {
			global $post;

			if ( 'order_enviopack' === $column ) {
		    $order = wc_get_order( $post->ID );
				$etiqueta_enviopack = get_post_meta($post->ID, 'etiqueta_enviopack', true);	
				$opciones_enviopack = get_post_meta($post->ID, 'opciones_enviopack', true);	
        $opcion_selected = get_post_meta($post->ID, 'bulk_enviopack', true);
				$enviopack_estado_numeroenvio = get_post_meta($post->ID, 'enviopack_estado_numeroenvio', true);

				if (empty($etiqueta_enviopack)){ 
					if(!empty($opciones_enviopack)){
						$opciones_enviopack = json_decode($opciones_enviopack);
						echo '<select style="max-width: 100%;" id="bulk_enviopack">';
							echo '<option value="regla_enviopack" data-order="'.$post->ID.'">Procesar por Regla</option>';
						echo '<pre>';print_r($opciones_enviopack);echo' $opciones_enviopack</pre>'; 
							if(!empty($opciones_enviopack)){
								foreach($opciones_enviopack as $opciones){
									$chosen = explode('+', $opciones);
									$clean_text =  str_replace("u00e1","á",$chosen[1]);
									if($chosen[0] == $opcion_selected){
										echo '<option value="'. $chosen[0].'" data-order="'.$post->ID.'" selected>'.$clean_text.'</option>';
									} else {
										echo '<option value="'. $chosen[0].'" data-order="'.$post->ID.'">'.$clean_text.'</option>';
									}
								}								
							}
						echo '</select>';
 					}
				} else {  
					echo '<a id="imprimir_etiqueta" data-order="'.$post->ID.'" data-id="'.$enviopack_estado_numeroenvio.'" class="button" href="#">Imprimir Etiqueta</a>'; 
				}  
			}
	}

  /**
   * Adds a new item into the Bulk Actions dropdown.
   */
  function register_enviopack_bulk_actions( $bulk_actions ) {
    $bulk_actions['wc-generar-enviopack'] = __( 'Generar Etiqueta', 'domain' );
    return $bulk_actions;
  }
  /**
   * Handles the bulk action.
   */
  function enviopack_action_handler( $redirect_to, $action, $post_ids ) {
    if ( ! in_array($action, array('wc-generar-enviopack'))) {
      return $redirect_to;
    }
    $enviopack_response = array();
    foreach ( $post_ids as $post_id ) {
      $enviopack_response[] = purchase_order_wanderlust_enviopack($post_id);
    }
        
    $redirect_to = add_query_arg( array(
			'bulk_enviopack_success' => count( $enviopack_response ),
			'bulk_enviopack_etiquetas' => $enviopack_response,
			'bulk_enviopack_error' => '',
    ), $redirect_to );
      
    return $redirect_to;
  }
  /**
   * Shows a notice in the admin once the bulk action is completed.
   */
  function enviopack_action_admin_notice() {
    if ( ! empty( $_REQUEST['bulk_enviopack_success'] ) ) {
      $drafts_count = intval( $_REQUEST['bulk_enviopack_success'] );
      printf('<div id="message" class="updated fade">'.$drafts_count.' etiqueta/s generadas!</div>');
    }  
    
 
    if ( ! empty( $_REQUEST['bulk_enviopack_error'] ) ) {
      $error = $_REQUEST['bulk_enviopack_error']; 
      printf('<div id="message" class="updated fade">'.$error.'</div>');
    }
    
    if ( ! empty( $_REQUEST['bulk_enviopack_etiquetas'] ) ) {
      $etis = $_REQUEST['bulk_enviopack_etiquetas']; 
      $list_ids = implode(', ',$etis);
      $list_ids = str_replace(' ', '', $list_ids);
      
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
			 
 			$response = wp_remote_get( 'https://api.enviopack.com/envios/etiquetas?access_token='. $api_response->token .'&ids='.$list_ids ); // OBTENER TOKEN		
			$resp = $response['body']; 				
			
			if($response['response']['code'] == 400 || $response['response']['code'] == 404){
				$resp = json_decode($resp);
				return $resp->message;
			} else {
				$save_path = plugin_dir_path ( __FILE__ ) . 'etiquetas/';
				$save_url = plugin_dir_url(dirname(__FILE__)) . 'includes/etiquetas/';
			
 				$file = date('c').'.pdf';
				$fp = fopen($save_path.$file, 'wb');
				fwrite($fp, $resp); 
				fclose($fp);	

				if(!empty($resp)){
 					printf('<div id="message" class="updated fade"><a href="'. $save_url .$file.'" target="_blank">Imprimir Etiquetas</a></div>');		
				}				
			}


    
       
    }    

  }

  function enviopack_bulk_save(){
    $envioid = $_POST['envioid']; //
    $dataid = $_POST['dataid'];
    update_post_meta($dataid, 'bulk_enviopack', $envioid);
    echo 'OK';
    die();
  }

?>