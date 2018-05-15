<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Shipping_EnvioPack class.
 *
 * @extends WC_Shipping_Method
 */
class WC_Shipping_EnvioPack extends WC_Shipping_Method {

	/**
	 * Constructor
	 */
	public function __construct( $instance_id = 0 ) {
		
		$this->id                   = 'enviopack_wanderlust';
		$this->instance_id 			 		= absint( $instance_id );
		$this->method_title         = __( 'EnvioPack', 'woocommerce-shipping-enviopack' );
 		$this->method_description   = __( 'EnvioPack te permite cotizar el valor de un envío con una amplia cantidad de empresas de correo de una forma simple y estandarizada.', 'woocommerce' );
		$this->supports             = array(
			'shipping-zones',
			'instance-settings',
		);

		$this->init();
		
 		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

	}

	/**
	 * init function.
	 */
	public function init() {
		// Load the settings.
		$this->init_form_fields = include( 'data/data-settings.php' );
		$this->init_settings();
		$this->instance_form_fields = include( 'data/data-settings.php' );
	 
		// Define user set variables
		$this->title           = $this->get_option( 'title', $this->method_title );
		$this->api_key    		 = $this->get_option( 'api_key' );
 		$this->secret_key    	 = $this->get_option( 'secret_key' );
		$this->origin_contacto = $this->get_option( 'origin_contacto' );
		$this->origin_email		 = $this->get_option( 'origin_email' );
		$this->usa_seguro			 = ( $bool = $this->get_option( 'usa_seguro' ) ) && $bool == 'yes' ? true : false;
		$this->origin_observaciones	 = $this->get_option( 'origin_observaciones' );
	}

	
	/**
	 * admin_options function.
	 */
	public function admin_options() {
		// Show settings
		parent::admin_options();
	} 
	
	/**
	 * generate_box_packing_html function.
	*/
	public function generate_service_html() {
		ob_start();
		include( 'data/services.php' );
		return ob_get_clean();
	}

	/**
	 * calculate_shipping function.
	 *
	 * @param mixed $package
	 */
	public function calculate_shipping( $package = array() ) {
		global $wp_session, $woocommerce;
			$cart = $woocommerce->cart;
			$items = $woocommerce->cart->get_cart();  				
			$productosConVolumenCompleto = 0;
			$cantidadProductos = $cart->get_cart_contents_count();
			$volumenTotal = 0;
			$altoTotal    = 0;
			$anchoTotal   = 0;
			$largoTotal   = 0;
			$pesoTotal   = 0;
 
			foreach($items as $item => $values) {

				$_product = wc_get_product( $values['product_id'] );
				$productidweights = $_product->get_weight() * $values['quantity'];
				$cantidad = $values['quantity'];

				$product['weight'] = round($_product->get_weight(), 2);
				$product['height'] = $_product->get_height();
				$product['width']  = $_product->get_width();
				$product['depth']  = $_product->get_length();

				if ( $product['height'] > 0 && $product['width'] > 0 && $product['depth'] > 0 && $product['weight'] > 0 ) {
					$productosConVolumenCompleto +=  $cantidad;
					$volumenTotal += $product['height'] *  $product['width'] *  $product['depth'] * $cantidad;
					$altoTotal    += $product['height'] * $cantidad;
					$anchoTotal   += $product['width'];
					$largoTotal   += $product['depth'];
					$pesoTotal    += $product['weight'];
				}
			}
		
			if ( $cantidadProductos == $productosConVolumenCompleto ) {
				if ( $cantidadProductos == 1 ) {
					$alto  = $altoTotal;
					$ancho = $anchoTotal;
					$largo = $largoTotal;
				} else {
					$lado = ceil( pow( $volumenTotal, 1/3 ) );
					$alto  = $lado;
					$ancho = $lado;
					$largo = $lado;
				}

				$paquete = array(
					'alto'                      => $alto,
					'ancho'                     => $ancho,
					'largo'                     => $largo,
					'peso'                      => $pesoTotal,
					'descripcion_primera_linea' => '',
					'descripcion_segunda_linea' => ''
				);
				$dataEnvio['paquetes'] = array( $paquete );
			}
		
 			$volumen = $volumenTotal / 10000;
 
			$body = array(
					'api-key' => $this->api_key,
					'secret-key' => $this->secret_key,
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

			$response = wp_remote_post( 'https://api.enviopack.com/auth', $args );
			$api_response = json_decode($response['body']);
 		
			/* COTIZAR COSTO DE ENVIO */
			$url = 'https://api.enviopack.com/cotizar/precio/a-domicilio';

			$body = array(
				'provincia' => $package['destination']['state'],
				'codigo_postal' => $package['destination']['postcode'],
				'peso' => $volumen,   
 				'paquetes'=> $dataEnvio['paquetes'][0]['alto'] .'x'. $dataEnvio['paquetes'][0]['ancho'] .'x'. $dataEnvio['paquetes'][0]['largo'] ,    
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
 
			$origen_datos[] = array (
				'enviopack_paquetes' => $dataEnvio['paquetes'],
				'settings' => $this->instance_id,
			);
			$origen_datos = json_encode($origen_datos);
			$enviopack_settings = base64_encode($origen_datos);
			if (headers_sent()) {

			}	else {
				setcookie('enviopack_settings', $enviopack_settings, time() + (86400 * 30), "/"); // 86400 = 1 day
				setcookie('final_weight', $dataEnvio['paquetes'][0]['peso'], time() + (86400 * 30), "/"); // 86400 = 1 day
			}
							
		foreach($opciones_envio as $envios) {
 				
				$precio = $envios->valor;
			
				$dias_entrega = $envios->horas_entrega / 24 ;
				$dias_entrega = ' / Entrega en ' . $dias_entrega . ' días';
			
				if($envios->modalidad == 'S'){
					$modalidad = ' a Sucursal';
					continue;
				} else {
					$modalidad = ' a Domicilio';					
				}			
			
				if($envios->servicio == 'N'){
					$servicio = ' - Estándar';
				} else if($envios->servicio == 'P'){
					$servicio = ' - Prioritario';					
				}	else if($envios->servicio == 'X'){
					$servicio = ' - Express';			
				} else if($envios->servicio == 'R'){
					$servicio = ' - Devolucion';			
				}
			
				$id_envio = 'Envio ' . $envios->modalidad . '-' . $envios->servicio . '-' . $envios->despacho;
				$nombre_envio = 'Envio ' . $modalidad. $servicio . $dias_entrega;
																
					$rate = array(
						'id' => sprintf("%s", $id_envio),
						'label' => sprintf("%s", $nombre_envio),
						'cost' => $precio,
						'calc_tax' => 'per_item',
						'package' => $package,
					);	
			
					if($precio!='0'){
						$this->add_rate( $rate );
					}
						

		}	
		
		
		if (isset($_COOKIE['enviopack_sucursales'])) {
			
			$enviopack_sucursales = base64_decode($_COOKIE['enviopack_sucursales']);
			
		} else {

			$url = 'https://api.enviopack.com/cotizar/precio/a-sucursal'; //OBTENER COSTOS DE SUCURSAL
 
	 		$body = array(
				'provincia' => $package['destination']['state'],
				'localidad' => '',
				'codigo_postal' => $package['destination']['postcode'],
				'peso' => $dataEnvio['paquetes'][0]['peso'],          
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
			$enviopack_sucursales = $response['body'];
  	}
			
		if(!empty($enviopack_sucursales)){
			
 				$enviopack_sucursales = json_decode($enviopack_sucursales);
				$contar = 0;
				foreach($enviopack_sucursales as $sucursales){ 
					$contar++;
					$precio = $sucursales->valor;
					$modalidad = ' Sucursal ';

					if($sucursales->servicio == 'N'){
						$servicio = ' - Estándar';
					} else if($sucursales->servicio == 'P'){
						$servicio = ' - Prioritario';					
					}	else if($sucursales->servicio == 'X'){
						$servicio = ' - Express';			
					} else if($sucursales->servicio == 'R'){
						$servicio = ' - Devolucion';			
					}
					if(empty($package['destination']['city'])){
						$nombre_envio = 'Envio a Sucursal '. $servicio;
					} else {
 						$dias_entrega = $envios->horas_entrega / 24 ;
						$dias_entrega = ' / Entrega en ' . $dias_entrega . ' días';
						$nombre_envio = $modalidad. $sucursales->sucursal->correo->nombre .' - ' . $sucursales->sucursal->calle . ' ' . $sucursales->sucursal->numero. $servicio . $dias_entrega;
					}
 
 					$id_envio = $sucursales->sucursal->id . '-SS-' . $sucursales->servicio . '-' . $sucursales->sucursal->correo->id . '-' . $sucursales->sucursal->codigo_postal;
					
					if(empty($package['destination']['city']) AND $contar >=2){
 
 					} else {
						$rate = array(
							'id' => sprintf("%s", $id_envio),
							'label' => sprintf("%s", $nombre_envio),
							'cost' => $precio,
							'calc_tax' => 'per_item',
							'package' => $package,
						);			
						if($precio!='0'){
							$this->add_rate( $rate );
						}						
					}
				}
 		}  	
 	}
}