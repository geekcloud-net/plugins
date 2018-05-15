<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

 	$webhook = site_url() .'/wc-api/enviopack/'; 

	if($_POST['origin_contacto']){
		update_option( 'enviopack_origin_contacto', $_POST['origin_contacto'] );
	}
 
return array(
	'enabled'           => array(
		'title'           => __( 'Activar EnvioPack', 'woocommerce-shipping-enviopack' ),
		'type'            => 'checkbox',
		'label'           => __( 'Activar este método de envió', 'woocommerce-shipping-enviopack' ),
		'default'         => 'no'
	),

	'title'             => array(
		'title'           => __( 'Título', 'woocommerce-shipping-enviopack' ),
		'type'            => 'text',
		'description'     => __( 'Controla el título que el usuario ve durante el pago.', 'woocommerce-shipping-enviopack' ),
		'default'         => __( 'EnvioPack', 'woocommerce-shipping-enviopack' ),
		'desc_tip'        => true
	),
	
  'api'              => array(
		'title'           => __( 'Configuración de la API', 'woocommerce-shipping-enviopack' ),
		'type'            => 'title',
		'description'     => __( '', 'woocommerce-shipping-enviopack' ),
  ),
	
  'api_key'         => array(
		'title'           => __( 'API KEY', 'woocommerce-shipping-enviopack' ),
		'type'            => 'text',
		'description'     => __( 'Este valor representa un identificador único de tu cuenta en EnvioPack.', 'woocommerce-shipping-enviopack' ),
		'default'         => __( '', 'woocommerce-shipping-enviopack' ),
    'placeholder' => __( '', 'meta-box' ),
  ),
	
  'secret_key'     => array(
		'title'           => __( 'SECRET KEY', 'woocommerce-shipping-enviopack' ),
		'type'            => 'text',
		'description'     => __( 'Nunca expongas este valor al conocimiento público. Todo uso de API deberá realizarse con origen en tu servidor.', 'woocommerce-shipping-enviopack' ),
		'default'         => __( '', 'woocommerce-shipping-enviopack' ),
    'placeholder' => __( '', 'meta-box' ),
  ),	
	
  'url_key'     => array(
		'title'           => __( 'URL PARA NOTIFICACIONES', 'woocommerce-shipping-enviopack' ),
		'type'            => 'text',
		'description'     => __( 'URL para notificaciones, se accede desde aqui https://app.enviopack.com/configuraciones-api.', 'woocommerce-shipping-enviopack' ),
		'default'         => __( $webhook, 'woocommerce-shipping-enviopack' ),
    'placeholder' => __( '', 'meta-box' ),
  ),		
	
	
  'origen'           => array(
		'title'           => __( 'Detalles', 'woocommerce-shipping-enviopack' ),
		'type'            => 'title',
		'description'     => __( 'Todos los campos son obligatorios.', 'woocommerce-shipping-enviopack' ),
  ),

	'origin_contacto'   => array(
		'title'           => __( 'Dirección de origen', 'woocommerce-shipping-enviopack' ),
		'type'            => 'select',
		'default'         => '',
		'class'           => 'origin_contacto',
		'options'         => array(
			'per_item'       => __( 'Seleccionar', 'woocommerce-shipping-enviopack' ),
		),
	),	
	
	
	'origin_email' 	=> array(
		'title'           => __( 'Email', 'woocommerce-shipping-enviopack' ),
		'type'            => 'text',
		'description'     => __( '', 'woocommerce-shipping-enviopack' ),
		'default'         => '',
		'desc_tip'        => true
  ),	
	 	
	'usa_seguro' 	=> array(
		'title'           => __( 'Seguro', 'woocommerce-shipping-enviopack' ),
 		'label'           => __( 'Quiero asegurar mis envios.', 'woocommerce-shipping-enviopack' ),
		'type'            => 'checkbox',
		'default'         => 'no',
		'desc_tip'    => true,
		'description'     => __( 'Es importante que tengas presente que la póliza de seguro no es válida para productos con valor declarado mayor a $10.000, ni objetos tales como: botellas y productos de vidrio, cerámica, cuadros, productos de electrónica y cualquier otro producto de índole frágil.', 'woocommerce-shipping-enviopack' )
 	),	
		
	'origin_sucursal'   => array(
		'title'           => __( 'Manejo de Retiro/Despacho', 'woocommerce-shipping-enviopack' ),
		'type'            => 'select',
		'default'         => '',
		'class'           => 'origin_sucursal',
 		'description'     => __( 'Seleccionar si el correo va a retirar el paquete por su domicilio o lo despachan en una sucursal.', 'woocommerce-shipping-enviopack' ),
		'options'         => array(
			'retiro'       => __( 'Retiro por Domicilio', 'woocommerce-shipping-enviopack' ),
			'despacho'       => __( 'Despacho en Sucursal', 'woocommerce-shipping-enviopack' ),			
		),
	),	
	
	'origin_observaciones' 	=> array(
		'title'           => __( 'Observaciones', 'woocommerce-shipping-enviopack' ),
		'type'            => 'text',
		'description'     => __( '', 'woocommerce-shipping-enviopack' ),
		'default'         => '',
		'desc_tip'        => true
  ),	

  'packing'           => array(
		'title'           => __( 'Cótizacion de Precios', 'woocommerce-shipping-enviopack' ),
		'type'            => 'title',
		'description'     => __( 'Los siguientes ajustes determinan cómo se van a cotizar los valores de cada correo.', 'woocommerce-shipping-enviopack' ),
  ),

 	'services'  => array(
		'type'            => 'service'
	),
);