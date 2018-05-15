<?php

/**
 * Plugin Name: Treggo Shipping
 * Plugin URI: http://www.treggocity.com
 * Description: Custom Shipping Method for Treggo for WooCommerce
 * Version: 1.8
 * Author: Torchio Nicolas
 * Author URI: http://www.treggocity.com
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /lang
 * Text Domain: treggo
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    function postToTreggo($endpoint,$curl_post_data){
        $service_url = 'http://empresas.treggocity.com'.$endpoint;
        $curl = curl_init($service_url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($curl_post_data));
        $curl_response = curl_exec($curl);
        if ($curl_response === false) {
            $info = curl_getinfo($curl);
            curl_close($curl);
            wc_add_notice( __( "No se puede comunicar con el servidor de TREGGO por favor controle la conectividad entre servidores", 'woocommerce' ), 'error' );
        }
        curl_close($curl);
        $decoded = json_decode($curl_response);
        if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
            wc_add_notice( __( "No se puede comunicar con el servidor de TREGGO por favor controle la conectividad entre servidores", 'woocommerce' ), 'error' );
        }
        return $decoded;
    }

    function treggo_shipping_method() {
        if ( ! class_exists( 'Treggo_Shipping_Method' ) ) {
            class Treggo_Shipping_Method extends WC_Shipping_Method {
                public function __construct() {
                    $this->id                 = 'treggo';
                    $this->method_title       = __( 'Treggo Shipping', 'treggo' );
                    $this->method_description = __( 'Custom Shipping Method for Treggo', 'treggo' );
                    $this->availability = 'including';
                    $this->countries = array('AR');
                    $this->init();
                    $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                    $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Treggo Shipping', 'treggo' );
                }

                function init() {
                    $this->init_form_fields();
                    $this->init_settings();
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                }

                function init_form_fields() {
                    $this->form_fields = array(
                      'key' => array(
                         'title' => __( 'API Key', 'treggo' ),
                           'type' => 'text',
                           'description' => __( 'Key de API de Treggo', 'treggo' ),
                           'default' => __( '', 'treggo' )
                           ),
                     'enabled' => array(
                          'title' => __( 'Habilitado', 'treggo' ),
                          'type' => 'checkbox',
                          'description' => __( 'Habilitar la opcion de treggo..', 'treggo' ),
                          'default' => 'yes'
                          ),
                     'title' => array(
                        'title' => __( 'Descripcion', 'treggo' ),
                          'type' => 'text',
                          'description' => __( 'Texto a mostrar como opcion de envio', 'treggo' ),
                          'default' => __( 'Envio en 2 horas por Treggo', 'treggo' )
                        ),
                        'prefijo' => array(
                           'title' => __( 'Texto de orden', 'treggo' ),
                             'type' => 'text',
                             'description' => __( 'Prefijo que tendra la descripcion de la orden ej "Compra numero "', 'treggo' ),
                             'default' => __( 'Compra numero ', 'treggo' )
                           ),
                           'direccion' => array(
                              'title' => __( 'Direccion', 'treggo' ),
                                'type' => 'text',
                                'description' => __( 'Direccion de donde salen los paquetes que se compren', 'treggo' ),
                                'default' => __( 'Av Corrientes 1578', 'treggo' )
                              ),
                     'multiplicador' => array(
                          'title' => __( 'Porcentaje del importe', 'treggo' ),
                          'type' => 'number',
                          'description' => __( 'Afectar la cotizacion por el procentaje para agregar o quitar comisiones', 'treggo' ),
                          'default' => '100'
                          )
                     );
                }

                public function calculate_shipping( $package ) {
                    $weight = 0;
                    $country = $package["destination"]["country"];
                    foreach ( $package['contents'] as $item_id => $values )
                    {
                        $_product = $values['data'];
                        $weight = $weight + $_product->get_weight() * $values['quantity'];
                    }
                    $weight = wc_get_weight( $weight, 'kg' );

                    $data = array(
                      "api" => $this->settings['key'],
                      "via" => "WooCommerce_1.8",
                      "tipo" => "ecommerce",
                      "peso" => $weight,
                      "destinos" => array (
                        array (
                          "direccion" => $this->settings['direccion']
                        ),
                        array (
                          "direccion" => $package["destination"]["address"]." ".$package["destination"]["address_2"].", ".$package["destination"]["city"]
                        )
                      )
                    );
                    $response = postToTreggo("/api/1/cotizacion",$data);
                    error_log("OUT:");
                    error_log(print_r($response, TRUE));
                    if(isset($response->pedido->errores)){
                      foreach ( $response->pedido->errores as $error )
                    {
                        wc_add_notice( __( "Envio por Treggo: ".$error , 'treggo' ), 'error' );
                    }
                    $rate = array(
                        'id' => $this->id,
                        'label' => $this->title,
                        'cost' => 0
                    );
                  }else{
                  	if($response->pedido->importe == ''){
						$rate = array(
                        'id' => $this->id,
                        'label' => $this->title,
                        'cost' =>  "Debe ingresar"
                        );
                  	}else{
                  		$rate = array(
                        'id' => $this->id,
                        'label' => $this->title,
                        'cost' =>  $response->pedido->importe * ($this->settings['multiplicador'] / 100)
	                    );
                  	}
                  }
                    $this->add_rate( $rate );
                }
            }
        }
    }

	function sv_wc_add_order_meta_box_action( $actions ) {
	    global $theorder;
	    if ( ! $theorder->is_paid() || get_post_meta( $theorder->id, '_wc_order_marked_printed_for_packaging', true ) ) {
	        return $actions;
	    }
		$actions['wc_custom_order_action'] = __( 'Solicitar TREGGO para esta compra', 'my-textdomain' );
	    return $actions;
	}
	add_action( 'woocommerce_order_actions', 'sv_wc_add_order_meta_box_action' );

	function sv_wc_process_order_meta_box_action( $order_id ) {
    treggo_shipping_method();
    $Treggo_Shipping_Method = new Treggo_Shipping_Method();  
    $Treggo_Shipping_Method->init();

	  $order = new WC_Order( $order_id );
      foreach($order->get_items( 'shipping' ) as $el){
        if($el['method_id'] == 'treggo'){
            $key = get_post_meta( $order->id, 'treggo_key', true );
            $descripcion = get_post_meta( $order->id, 'treggo_descripcion', true );
            $direccion = get_post_meta( $order->id, 'treggo_direccion', true );
            $key = $Treggo_Shipping_Method->settings['key'];
            $descripcion = $Treggo_Shipping_Method->settings['descripcion'];
            $direccion = $Treggo_Shipping_Method->settings['direccion'];

            $data = array(
              "api" => $key,
              "via" => "WooCommerce_1.8",
              "version" => "1.8",
              "tipo" => "ecommerce",
              "destinos" => array (
                array (
                  "direccion" => $direccion,
                  "contacto" => $descripcion
                ),
                array (
                  "direccion" => $order->shipping_address_1.", ".$order->shipping_city,
                  "contacto" => $order->get_formatted_shipping_full_name(),
                  "puerta" => $order->shipping_address_2
                )
              )
            );
          $response = postToTreggo("/api/1/alta",$data);
          if(isset($response->pedido->errores)){

                  foreach ( $response->pedido->errores as $error )
                {
                    $order->add_order_note( "Problema de la orden en Treggo: ".$error);
                }
              }else if(isset($response->error)){
                $order->add_order_note( "Problema de la orden en Treggo: ".$response->error);
            }else{
            	$order->add_order_note( "Se ha solicitado una moto a Treggo correctamente" );
            }
        }else{
            $order->add_order_note( "Envio no es de Treggo; ".$el['method_id'] );
        }
      }
	}

	add_action( 'woocommerce_order_action_wc_custom_order_action', 'sv_wc_process_order_meta_box_action' );

    function add_treggo_shipping_method( $methods ) {
        $methods[] = 'Treggo_Shipping_Method';
        return $methods;
    }

    function treggo_validate_order( $posted )   {
        $packages = WC()->shipping->get_packages();
        $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
        if( is_array( $chosen_methods ) && in_array( 'treggo', $chosen_methods ) ) {
            foreach ( $packages as $i => $package ) {
                if ( $chosen_methods[ $i ] != "treggo" ) {
                    continue;
                }
                $Treggo_Shipping_Method = new Treggo_Shipping_Method();

                $Treggo_Shipping_Method->calculate_shipping( $package );
            }
        }
    }

    add_action( 'woocommerce_review_order_before_cart_contents', 'treggo_validate_order' , 10 );
    add_action( 'woocommerce_after_checkout_validation', 'treggo_validate_order' , 10 );
    add_action( 'woocommerce_shipping_init', 'treggo_shipping_method' );
    add_filter( 'woocommerce_shipping_methods', 'add_treggo_shipping_method' );
}
