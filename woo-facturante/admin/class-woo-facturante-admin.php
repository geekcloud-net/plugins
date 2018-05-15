<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://fuegoyamana.com
 * @since      0.1.3
 *
 * @package    Woo_Facturante
 * @subpackage Woo_Facturante/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_Facturante
 * @subpackage Woo_Facturante/admin
 * @author     Hernán Galván <hernan@fuegoyamana.com>
 */
 
/* 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
*/

class Woo_Facturante_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	private $soap_url;
	
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_styles() {


		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-facturante-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-facturante-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script($this->plugin_name, 'fyFacturanteAdminVars', array('facturanteUrl' => plugin_dir_url(__FILE__ ) ));

	}
	
	public function add_settings_tab( $settings_tabs ) {
        
		$settings_tabs['settings_tab_woo_facturante'] = __( 'Facturante Settings', 'woo-facturante' );
        
		return $settings_tabs;
    
	}
	
	 /**
     * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
     *
     * @uses woocommerce_admin_fields()
     * @uses self::get_settings()
     */	 
    public static function settings_tab() {
       
	   woocommerce_admin_fields( self::get_settings() );
    
	}


    /**
     * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
     *
     * @uses woocommerce_update_options()
     * @uses self::get_settings()
     */ 
    public static function update_settings() {
        
		woocommerce_update_options( self::get_settings() );
    
	}
	
	/**
	* Show warnings if taxes are ON.
	*
	**/
	function woo_warning(){
		 
		 global $current_screen;
		 
		 if ( $current_screen->base == 'woocommerce_page_wc-settings' ){
				
				if('yes'==get_option('woocommerce_calc_taxes')){
					 echo '<div class="notice notice-error"><p>Atención: El cálculo de impuestos debe estar desactivado para que Woo Facturante funcione correctamente en esta versión. </p></div>';
				}
				
				if (!extension_loaded('soap')) {
				    echo '<div class="notice notice-error"><p>Atención: Debe habilitar la extensión SOAP en su servidor para que Woo Facturante funcione correctamente. </p></div>';
				}
		 
		 }
		 
	}
	
	/**
     * Gets order id in WooCommerce 3.0 and older versions
     *
     * @since 0.0.3
     */ 
	
	public function get_order_id($order){
		
		global $woocommerce;
		
		if($woocommerce->version >= '3.0'){
			
			$order_id = $order->get_id();
			
		}else{
			
			$order_id = $order->id;
			
		}
		
		return $order_id;
		
	}
	
	/**
	* Adds DNI to register form
	*
	*/
	public static function woo_facturante_add_dni_field_to_register(){
			
			?>
			<p class="form-row form-row-first">
				<label for="woo_facturante_billing_dni"><?php _e( 'DNI', 'woo-facturante' ); ?> <span class="required">*</span></label>
				<input type="text" class="input-text" name="billing_dni_facturante" id="reg_billing_dni_facturante" value="<?php if ( ! empty( $_POST['billing_dni_facturante'] ) ) esc_attr_e( $_POST['billing_dni_facturante'] ); ?>" />
			</p>
			<?php
	
	}
	
	/**
	* Validates DNI in register form
	*
	*/
	public static function woo_facturante_validate_extra_register_fields( $errors, $username, $email ){
		
		if ( isset( $_POST['billing_dni_facturante'] ) && empty( $_POST['billing_dni_facturante'] ) ) {
			
			if ( !preg_match('/^[0-9]*$/', $_POST['billing_dni_facturante']) ){
				
				$errors->add( 'billing_dni_facturante_error', __( '<strong>Error</strong>: DNI must be a numeric value!.', 'woo-facturante' ) );
				
			}
			
			$errors->add( 'billing_dni_facturante_error', __( '<strong>Error</strong>: DNI is required!.', 'woo-facturante' ) );
		
		}
		
		return $errors;
		
	}
	
	
	/**
	* Adds DNI to my account form
	*
	*/
	public static function woo_facturante_add_dni_field_to_my_account(){
			
			$user_id = get_current_user_id();
			
			$user = get_userdata( $user_id );
 
			if ( !$user )
				return;
			
			?>
			  <p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide"> 
				  <label for="woo_facturante_billing_dni"><?php _e( 'CUIT/CUIL/DNI', 'woo-facturante' ); ?> <span class="required">*</span></label> 
				  <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_dni_facturante" id="ma_billing_dni_facturante" value="<?php echo esc_attr( $user->billing_dni_facturante ); ?>" /> 
			  </p> 
			<?php
	}
	
	/**
	* Saves DNI in my account and register page
	*
	*
	*/
	public function woo_facturante_save_DNI( $user_id ) {
		
		if(isset($_POST[ 'billing_dni_facturante' ])){
		
			update_user_meta( $user_id, 'billing_dni_facturante', htmlentities( $_POST[ 'billing_dni_facturante' ] ) ); 
		
		}
	
	}
	
	/**
	* Add DNI field to checkout form
	*
	*/
	public static function woo_facturante_dni_checkout_field( $checkout_fields ){
		
		 $user_meta  =  get_user_meta ( get_current_user_id() );
		 
		 $billing_dni =  $user_meta['billing_dni_facturante']['0'];
		 
		 $checkout_fields['billing']['billing_dni_facturante']  =  array(
            'label'          => __('CUIT/CUIL/DNI', 'woocommerce'),
            'placeholder'    => _x('enter your dni', 'placeholder', 'woo-facturante'),
            'required'       => true,
            'clear'          => false,
            'type'           => 'text',
            'class'          => array('form-row-wide'),
		);
		
		echo '<pre style="display:none">';
		var_dump($checkout_fields);
		echo '</pre>';
		
		return $checkout_fields;
		
	}
	
	/**
	* Validates DNI field in checkout proccess
	*
	*/
	public static function woo_facturante_checkout_field_process() {
	
		if ( ! $_POST['billing_dni_facturante'] ||  !preg_match('/^[0-9]*$/', $_POST['billing_dni_facturante']) )
			
			wc_add_notice( __( 'Your DNI is invalid.','woo-facturante' ), 'error' );
	
	}
	
	/**
	* Saves user DNI field to order
	*
	*/
	public static function woo_facturante_update_order_meta( $order_id ) {
		
		if ( ! empty( $_POST['billing_dni_facturante'] ) ) {
		
			update_post_meta( $order_id, 'DNI', sanitize_text_field( $_POST['billing_dni_facturante'] ) );
		
		}
	
	}
	
	/*
	* Shows customer DNI in order page
	*
	*/
	public function woo_facturante_display_admin_order_meta($order){
    
		echo '<p><strong>'.__('DNI').':</strong> ' . get_post_meta( $this->get_order_id($order), 'DNI', true ) . '</p>';

	}
	
	/*
	* Shows customer DNI in email
	*
	*
	*/
	
	public static function woo_facturante_display_dni_in_email_fields( $keys ){		
		 
        $keys['DNI'] = 'DNI';
        
        return $keys;
		
	}
	
	
	/*
	* Add facturante metabox to the order screen
	*
	*/
	public function woo_facturante_add_metaboxes(){
        
		add_meta_box( 'facturante_metabox', __('Facturante','woo-facturante'), array( $this, 'woo_facturante_order_buttons' ), 'shop_order', 'side', 'core' );
		
	}
	
	/*
	* Add facturante buttons to order metabox
	*
	*/
	public function woo_facturante_order_buttons(){
		
		global $post;
		
		$the_order = new WC_Order($post->ID);
		
		
		if ( ! $the_order->has_status( array( 'cancelled' ) ) && ( $the_order->has_status( array( 'processing' ) ) || $the_order->has_status( array( 'completed' ) ) ) ){ 
		
			$invoice_id = get_post_meta( $this->get_order_id($the_order),'_invoice_id',true );
			
			$estado_facturante = get_post_meta( $this->get_order_id($the_order), '_estado_facturante', true );
			
			?>
			<p>
			<?php
		
			if($invoice_id==''){
			
				do_action('before_invoice_button',$args=array());
				
				?>
				
					<a class="button fy-invoice-button"  title="<?php _e('create invoice','woo-facturante') ?>"><?php __('Invoice','woo-facturante') ?></a>
				
				<?php
			
			}else{
				
				
				switch($estado_facturante){
					
					case 1:
					
						/* 1: awaiting invoice */
						?>
						
							<a class="button fy-awaiting-button"  title="<?php _e('awaiting invoice','woo-facturante') ?>"><?php __('awaiting invoice','woo-facturante') ?></a>
						
						<?php
						
						break;
					
					case 2:
						
						/* 2: invoice ready! */
						?>
						
							<a class="button fy-view-invoice-button"  title="<?php _e('view invoice','woo-facturante') ?>"><?php __('view invoice','woo-facturante') ?></a>
							
						<?php
						
						break;
					
					default:
					
					    /* 3: error (should have a re-send button?) */
						
						?>
						
							<a class="button fy-invoice-button"  title="<?php _e('create invoice','woo-facturante') ?>"><?php __('Invoice','woo-facturante') ?></a>
						
						<?php
						
						break;
				
				}
			}
			?>
			</p>
			<?php
		}
	
	}
	
    /**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
     * @return array Array of settings for @see woocommerce_admin_fields() function.
     */
    public static function get_settings() {

        $settings = array(
            'section_title' => array(
                'name'     => __( 'Input parameters', 'woo-facturante' ),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'wc_settings_tab_woo_facturante_section_title'
            ),
			'testmode' => array(
                'name' => __( 'Activate test mode', 'woo-facturante' ),
                'type' => 'checkbox',
                'desc' => __( 'Activate sandbox mode.', 'woo-facturante' ),
                'id'   => 'wc_settings_tab_woo_facturante_testmode'
            ),
            'user' => array(
                'name' => __( 'User', 'woo-facturante' ),
                'type' => 'text',
                'desc' => __( 'User provided by Facturante', 'woo-facturante' ),
                'id'   => 'wc_settings_tab_woo_facturante_user'
            ),
            'hash' => array(
                'name' => __( 'Hash', 'woo-facturante' ),
                'type' => 'password',
                'desc' => __( 'Password provided by Facturante', 'woo-facturante' ),
                'id'   => 'wc_settings_tab_woo_facturante_hash'
            ),
			'organization_ID' => array(
                'name' => __( 'Organization ID', 'woo-facturante' ),
                'type' => 'text',
                'desc' => __( 'Organization number provided by Facturante', 'woo-facturante'),
                'id'   => 'wc_settings_tab_woo_facturante_organization_ID'
            ),
			/*
			'invoice_type' => array(
                'name' => __( 'Invoice Type', 'woo-facturante' ),
                'type' => 'select',
                'desc' => __( 'Type of invoice to submit to customer. ', 'woo-facturante' ),
                'id'   => 'wc_settings_tab_woo_facturante_invoice_type',
				'default' => 'FC',
				'options' => array(
					  //'FA' => 'Factura A',
					  //'FA CBU INF' => 'Factura con CBU informado',
					  //'NCA' => 'Nota de crédito A',
					  //'NDA' => 'Nota de débito A',
					  //'RA' => 'Recibo A',
					  'FB' => 'Factura B',
					  //'FB8001' => 'Factura B a RI con informe 8001',
					  //'NCB' => 'Nota de crédito B',
					  //'NCB8001' => 'Nota de crédito B a RI con informe 8001',
					  //'NDB' => 'Nota de débito B',
					  //'RB' => 'Recibo B',
					  //'FC' => 'Factura C',
					  //'NCC' => 'Nota de crédito C',
					  //'NDC' => 'Nota de débito C',
					  //'RC' => 'Recibo C',
					  //'FM' => 'Factura M',
					  //'NCM' => 'Nota de crédito M',
					  //'NDM' => 'Nota de débito M',
					  //'RM' => 'Recibo M',
					  //'PF' => 'Proforma'
					  
				 )
            ),*/
			'prefix' => array(
                'name' => __( 'Point of sale', 'woo-facturante' ),
                'type' => 'text',
                'desc' => __( 'Point of sale for invoices, i.e: 0004, must be filled with zeros.', 'woo-facturante' ),
                'id'   => 'wc_settings_tab_woo_facturante_prefix'
            ),
			
			'impositive_treatment' => array(
				'name' => __( 'Tratamiento impositivo', 'woo-facturante' ),
                'type' => 'select',
                'desc' => '',
                'id'   => 'wc_settings_tab_woo_facturante_impositive_treatment',
				'default' => '3',
				'options' => array(
					  //'1' => 'Incluyo el IVA en los precios de los productos.',
					  //'2' => 'No incluyo el IVA en los precios de los productos.',
					  '3' => 'Consumidor final',
					  '1' => 'Monotributista',
					  '2' => 'Responsable inscripto',
					  '4' => 'IVA exento',
					  '5' => 'IVA no responsable'
				 )
			),
            'sectionend' => array(
                 'type' => 'sectionend',
                 'id' => 'wc_settings_tab_woo_facturante_section_title'
            ),
			'section_contacto' => array(
                'name'     => __( 'Contact', 'woo-facturante' ),
                'type'     => 'title',
                'desc' => 'Soporte: <a href="https://fuegoyamana.com/soporte">https://fuegoyamana.com/soporte</a> <br>Web: <a href="https://fuegoyamana.com" target="_blank">https://fuegoyamana.com</a>',
                'id'       => 'wc_settings_tab_woo_facturante_section_contacto'
            ),
			'sectionend_contacto' => array(
                 'type' => 'sectionend',
                 'id' => 'wc_settings_tab_woo_facturante_section_contacto'
            ),
        );

        return apply_filters( 'wc_settings_tab_woo_facturante', $settings );
    }
	
	/**
	* Buttons for Facturante actions 
	*/
	
	public function woo_facturante_order_actions($actions,$the_order){

		
		if ( ! $the_order->has_status( array( 'cancelled' ) ) && ( $the_order->has_status( array( 'processing' ) ) || $the_order->has_status( array( 'completed' ) ) ) ) { 
		
			$invoice_id = get_post_meta($this->get_order_id($the_order),'_invoice_id',true);
			
			$estado_facturante = get_post_meta( $this->get_order_id($the_order), '_estado_facturante', true );
		
			if($invoice_id==''){
			
				$actions['invoice'] = array(
					
					'url'       => '#',
					
					'name'      => __( 'Invoice', 'woo-facturante' ),
					
					'action'    => "fy-invoice-button", 
				
				);
			
			}else{
				
				switch($estado_facturante){
					
					case 1:
					
						/* 1: awaiting invoice */
						$actions['awaiting_invoice'] = array(
					
								'url'       => '',
								
								'name'      => __( 'awaiting invoice', 'woo-facturante' ),
								
								'action'    => "fy-awaiting-button",
							
						);
						
						break;
					
					case 2:
						
						/* 2: invoice ready! */
						$actions['view_invoice'] = array(
				
							'url'       => '#',
							
							'name'      => __( 'view invoice', 'woo-facturante' ),
							
							'action'    => "fy-view-invoice-button",
						
						);
						
						break;
					
					default:
					
					    /* 3: error (should have a re-send button?) */
						$actions['invoice'] = array(
					
							'url'       => '#',
							
							'name'      => __( 'Invoice', 'woo-facturante' ),
							
							'action'    => "fy-invoice-button", 
						
						);
						
						break;
				
				}
				
				
				
			}
		
		}
		
		return $actions;
	
	}
	
	public function woo_item_price($impositive_treatment,$price,$iva=''){
		
		if($iva=='') $iva=21;
		
		$divisor = ($iva/100) + 1;
		
		if( in_array($impositive_treatment, array(3,2,5) ) ){
			
			$price = $price/$divisor;
			
		}
		
		return $price;
	}
	
	/**
	* Process invoice request
	*
	**/
	
	public function woo_facturante_invoice(){
		
		global $woocommerce;
		
		//Obtain and prepare order data
		
		$order_id = intval($_POST["order"]);
		
		$order = wc_get_order( $order_id );
		
		$coupons = $order->get_used_coupons();
		
		if($woocommerce->version >= "3.0"){
		
			$discount = $order->discount_total;
		
		}else{
		
			$discount = $order->get_total_discount();
		
		}
		
		$shipping_data = $order->get_items( 'shipping' );
		
		$shipping_methods = array();
		
		$total = 0;
		
		$precio_sin_iva = 0;
		
		//impositive_treatment
		$it = get_option('wc_settings_tab_woo_facturante_impositive_treatment');	
		
		if(!$it) die("sin tratamiento impositivo!");
		
		$option_taxes = get_option( 'woocommerce_calc_taxes' );
		
		
		/** Shipping **/
		
		if(is_array($shipping_data)):
		
			foreach($shipping_data as $k=>$sm){
				
				/** TAXES shipping **/
				
				//if( false !== get_option('wc_settings_tab_woo_facturante_IVA') ){
				if( is_plugin_active('woo-facturante-iva/woo-facturante-iva.php') ){
					
					if($sm['total_tax']==0 && $option_taxes == 'no'){
						
						$iva = get_option('wc_settings_tab_woo_facturante_IVA');
						
						if($woocommerce->version >= "3.0"){
							
							$precio = $this->woo_item_price( $it, $sm->get_total(), $iva );
						
						}else{
							
							$precio  = $this->woo_item_price( $it, $sm['item_meta']['cost'][0], $iva );
						
						}
					
					}else{
						
						if($sm['total_tax']==0){
							$iva = 0;
						}else{
							$iva = (($sm['total_tax']*100)/$sm['total']);
						}
						
						
						if($woocommerce->version >= "3.0"){
							
							$precio = $sm->get_total();
						
						}else{
							
							$precio  = $sm['item_meta']['cost'][0];
						
						}
					}
				
				}else{
					
					$iva = 21;
					
					if($woocommerce->version >= "3.0"){
						
						$precio = $this->woo_item_price( $it, $sm->get_total(), $iva );
					
					}else{
						
						$precio  = $this->woo_item_price( $it, $sm['item_meta']['cost'][0], $iva );
					
					}
				}
				
				
				if($woocommerce->version >= "3.0"){
					
					$shipping_name = $sm->get_name();
					
				}else{
				
					$shipping_name = $sm['name'];
				
				}
				
				
				array_push($shipping_methods,
				
					array(
					
						'Bonificacion' => 0,
						
						'Cantidad' => 1,
						
						'Codigo' => '',
						
						'Detalle' => $shipping_name,
						
						'Gravado' => true,
						
						'IVA' => $iva,
						
						'PrecioUnitario' => $precio
						
					)
					
				);
				
			}
			
			$total+=$precio;
		
		endif;
		
		$fees = $order->get_fees();
		
		$order_fees = array();
	
		if(is_array($fees)){
			
			foreach($fees as $k=>$v){
				
				array_push($order_fees,
				
					array(
					
						'Bonificacion' => 0,
						
						'Cantidad' => 1,
						
						'Codigo' => '',
						
						'Detalle' => $v->get_name(),
						
						'Gravado' => true,
						
						'IVA' => 21,
						
						'PrecioUnitario' => $this->woo_item_price( $it, $v->get_total() )
						
					)
					
				);
			}
			
		}
		
		
		$order_meta = get_post_meta($order_id);
		
		$items = $order->get_items();
		
		$billing_currency = $order_meta["_order_currency"][0];
		
		$billing_first_name = $order_meta["_billing_first_name"][0];
		
		$billing_last_name = $order_meta["_billing_last_name"][0];
		
		$billing_email = $order_meta["_billing_email"][0];
		
		$billing_postcode = $order_meta["_billing_postcode"][0];
		
		$payment_method = $order_meta["_payment_method"][0];
		
		$billing_address_1 = $order_meta["_billing_address_1"][0];
		
		$billing_address_2 = $order_meta["_billing_address_2"][0];
		
		$billing_city = $order_meta["_billing_city"][0];
		
		$billing_phone = $order_meta["_billing_phone"][0];
		
		$billing_company = $order_meta["_billing_company"][0];
		
		
		if(class_exists("WC_Countries")){
			
			/* WC > 2.3.0 */
			
			$countries = new WC_Countries();
			
			$states = $countries->get_states("AR");
			
			$billing_state = $states[$order_meta["_billing_state"][0]]; //Facturante only works in Argentina (AR)


		}else{
			
			global $states;
			
			$billing_state = $states["AR"][$order_meta["_billing_state"][0]]; //Facturante only works in Argentina (AR)
		
		}
		
		
		$customer_user = $order_meta["_customer_user"][0];
		
		/*Patch for DOS61*/
		
		
		if(isset($order_meta['_billing_street'])){		
			
			$billing_address_1 = $order_meta['_billing_street'][0].' '.$order_meta['_billing_number'][0];
			$billing_address_2 = $order_meta['_billing_floor'][0].' '.$order_meta['_billing_apartment'][0];
	
		}
		
		
		$time = new DateTime;
		
		$today_atom = $time->format(DateTime::ATOM);
		
		/** Products **/
		
		//Bienes	
		$Bienes = array();
		
		foreach( $items as $k=>$item ){
			
			
			$product_id = $item['product_id'];
			
			$item_quantity = $order->get_item_meta($k, '_qty', true);
			
			$item_total = $order->get_item_meta($k, '_line_total', true);
			
			
			if($item['variation_id']>0){
				
				$product_id = $item['variation_id'];
				
			}
			
			$product = wc_get_product($product_id);

			$price = $item_total/$item_quantity;
			
			$sku = $product->get_sku();
			
			if($sku == ''){
				$sku = $product_id;
			}
			
			/* TAXES para productos */
			
			/* Si existe el plugin utiliza taxes, sino el iva es del 21 siempre */
			
			if( is_plugin_active('woo-facturante-iva/woo-facturante-iva.php') ){
				
				$item_meta = $item->get_data();
			
				if($item_meta['total_tax']==0 && $option_taxes == 'no'){
					
					
					$iva = get_option('wc_settings_tab_woo_facturante_IVA');
					
					$precio = $this->woo_item_price( $it, $price, $iva );
					
				}else{
					
					
					$tax = new WC_Tax();
				
					$taxes = array_shift($tax->get_rates($item_meta['tax_class']));
					
					if($item_meta['total_tax']==0){
						$iva = 0;
					}else{
						$iva = $taxes['rate'];
					}
					
					/* Cuando se utiliza los impuestos de woocommerce no resto el IVA */
					
					$precio = $price;
					
				}
			
			
			}else{
				
				
				$iva = 21;
				
				$precio = $this->woo_item_price( $it, $price, $iva );
				
			}
			
			
			array_push($Bienes,array(
			
				'Bonificacion' => 0,
				
				'Cantidad' => $item_quantity,
				
				'Codigo' => $sku,
				
				'Detalle' => $item['name'],
				
				'Gravado' => true,
				
				'IVA' => $iva,
				
				'PrecioUnitario' => $precio
				
			));
			
			$total+= $item['qty'] * $precio;
			
		}
		
		/*
		
		if( $discount > 0 ){
			
			$precio = $this->woo_item_price( $it, abs($discount) );
			
			array_push($Bienes,array(
			
				'Bonificacion' => 0,
				
				'Cantidad' => 1,
				
				'Codigo' => "",
				
				'Detalle' => "Descuento",
				
				'Gravado' => true,
				
				'IVA' => 21,
				
				'PrecioUnitario' => -$precio
				
			));
			
			$total-= $discount;
			
		}
		*/
		
		
		/*Agregar el shipping si existe, como un item mas, el total ya esta a gregado en L:521*/
		
		
		if(is_array($shipping_methods) && count($shipping_methods)>0 ){
			
			foreach($shipping_methods as $k=>$v){
				array_push($Bienes,$v);
			}
		
		}
		
		if(count($order_fees)>0){
			foreach($order_fees as $k=>$v){
				array_push($Bienes,$v);
			}
		}
		
		
		$testmode = get_option('wc_settings_tab_woo_facturante_testmode');
		
		if( !$testmode || $testmode == 'no' ){
			
				$url = 'http://www.facturante.com/api/comprobantes.svc?wsdl';
		
		}else{
				
				$url = 'http://testing.facturante.com/api/Comprobantes.svc?wsdl';
		
		}
		
		
		$client = new SoapClient($url);
		
		//Autentication
		
		
		$auth = array(	
		
					"Empresa" => get_option( 'wc_settings_tab_woo_facturante_organization_ID' ),
					"Hash" => get_option( 'wc_settings_tab_woo_facturante_hash' ),
					"Usuario" => get_option( 'wc_settings_tab_woo_facturante_user' )
		
		);
		
		(isset($billing_company) && $billing_company!='')? $razonsoc = $billing_company : $razonsoc = $billing_first_name . ' ' . $billing_last_name;
		
	    //Customer	
		
		$cliente = array(
					
					"CodigoPostal" => $billing_postcode,
					
					"CondicionPago" => 1,
					
					"DireccionFiscal" =>  $billing_address_1.' '.$billing_address_2,
					
					"Localidad" => $billing_city,
					
					"Provincia" => $billing_state,
					
					"MailFacturacion" => $billing_email, 
					
					"NroDocumento" => get_post_meta($order_id,'DNI',true),
					
					"PercibeIVA" => false, ////True: Si la empresa emisora es Agente de Retención de ARBA
					
					"PercibeIIBB" => false, ////Si la empresa emisora es Agente de Retención de IVA

					"RazonSocial" => $razonsoc,
					
					/*
					"TipoDocumento" => 1,//DNI
					
					"TratamientoImpositivo" => get_option('wc_settings_tab_woo_facturante_impositive_treatment'),
					
					"EnviarComprobante" => true,
					
					"MailContacto" => $billing_email,
					
					"Contacto" => $billing_first_name . ' ' . $billing_last_name,
					
					"Telefono" => $billing_phone
					*/
		);
		
		//Invoice header
		
		( false !== get_option('wc_settings_tab_woo_facturante_condicion_venta') ) ? $cv = get_option('wc_settings_tab_woo_facturante_condicion_venta') : $cv = 4;
		
		if(isset($_POST['pm'])){
			
			$cv = intval($_POST['pm']);
		
		}
		
		$encabezado = array(
						"Bienes" => 1, /**
								
								1: Bienes
								2: Servicios
								3: Productos y Servicios.

								**/
						"CondicionVenta" => $cv,
								/**						
									1= Contado
									2= Cuenta Corriente
									3= Tarjeta de Debito
									4= Tarjeta de Credito
									5= Cheque
									6= Ticket
									7= Otro
									8= MercadoPago
									9= Cobro Digital
									10= DineroMail
									11= Decidir
									12= Todo Pago
								**/
								
						"EnviarComprobante" => true,
						
						"FechaHora" => $today_atom,
						
						"FechaServDesde" => $today_atom,
						
						"FechaServHasta" => $today_atom,
						
						"FechaVtoPago" => $today_atom,
						
						"Moneda" => 2, // PESOS ARGENTINOS (2 ARS, 3 DOLAR USA)
							
						"Prefijo" => get_option('wc_settings_tab_woo_facturante_prefix'),
					
						"TipoComprobante" => 'F',
						
						"TipoDeCambio" => 1,
						
						/*
						"TotalNeto" => $total,
						
						"TotalConDescuento" => 0,
						
						"PercepcionIVA" => 0,
						
						"PercepcionIIBB" => 0,
						
						"Total" => $total,
						
						"SubTotal" => $total,
						
						"ImporteImpuestosInternos" => 0,
						
						"ImportePercepcionesMunic" => 0,
						
						"SubTotalNoAlcanzado" => 0,
						
						"SubTotalExcento" => 0,
						
						"PorcentajeIIBB" => 0,
						*/
						
						"WebHook" => array(
		
							//'Url' => plugins_url().'/woo-facturante/webhook/wh.php?order='.$order_id, //Url a la que Facturante enviará por POST el campo DetalleComprobante (xml) luego que reciba la respuesta de AFIP (debe incluir el protocolo, por ejemplo: https://myapp.com/webhook?id=1234)
						
							'Url' => site_url().'/?wc-api=Woo_Facturante&order='.$order_id.'&h='.get_option( 'wc_settings_tab_woo_facturante_hash' ), //Url a la que Facturante enviará por POST el campo DetalleComprobante (xml) luego que reciba la respuesta de AFIP (debe incluir el protocolo, por ejemplo: https://myapp.com/webhook?id=1234)
						
						)
						
		);
	
		
		//Parametros	
		$param = array(
						"Autenticacion" => $auth,
						"Cliente" => $cliente,
						"Encabezado" => $encabezado,
						"Items" => $Bienes,
					);				
		
		//Request
		$request = array("request" => $param);
		
		//Response
		try {
			
			 
		  
			  $response = $client->CrearComprobanteSinImpuestos($request);
			  
			  
			  if ( isset( $response->CrearComprobanteSinImpuestosResult->IdComprobante ) ) {
				  
				update_post_meta( $order_id, '_invoice_id', $response->CrearComprobanteSinImpuestosResult->IdComprobante );
				
				//El botón queda en estado "esperando" 1: esperando, 2: imprimir, 3:error
				update_post_meta( $order_id, '_estado_facturante', 1 );
			  
			  }
			  
			  echo json_encode($response);
			  
			  exit;
		
		} catch (Exception $e)  {
		  
			  echo json_encode($e->getMessage());
			  
			  //var_dump($client->__getLastRequest());
			  
			  //var_dump($client->__getLastResponse());
		
		}

		
	}
	
	public function woo_facturante_view_invoice(){
		
		$order_id = intval($_POST["order"]);
		
		$invoice_id = get_post_meta($order_id,'_invoice_id',true);
		
		$testmode = get_option('wc_settings_tab_woo_facturante_testmode');
		
		if(!$testmode || $testmode=='no'){
				
				$url = 'http://www.facturante.com/api/comprobantes.svc?wsdl';
		
		}else{
				
				$url = 'http://testing.facturante.com/api/Comprobantes.svc?wsdl';
		
		}
		
		
		$client = new SoapClient($url);
		
		//Autentication
		
		$auth = array(	
					"Empresa" => get_option( 'wc_settings_tab_woo_facturante_organization_ID' ),
					"Hash" => get_option( 'wc_settings_tab_woo_facturante_hash' ),
					"Usuario" => get_option( 'wc_settings_tab_woo_facturante_user' )
		);
		
		//Parametros	
		$param = array(
						"Autenticacion" => $auth,
						"IdComprobante" => $invoice_id
					);				
		
		//Request
		$request = array("request" => $param);
		
		
		try {
		  
			  $response = $client->DetalleComprobante($request);
			  
			  echo json_encode($response);
			  
			  exit;
		
		} catch (Exception $e)  {
		  
			  echo json_encode($e->getMessage());
		
		}
	
	}
	

}