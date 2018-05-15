<?php
/**
 * Description of WPEAE_AliexpressShippingFrontend
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_AliexpressShippingFrontend')):

    class WPEAE_AliexpressShippingFrontend {


        function __construct() {
      
            add_action('woocommerce_before_add_to_cart_button', array($this, 'display_shipping_html'));
        
            add_action('wp_enqueue_scripts', array($this, 'assets'));
            
            add_action( 'woocommerce_add_to_cart_validation', array($this, 'product_shipping_fields_validation'), 10, 5 ); 
            
            add_action( 'woocommerce_after_shop_loop_item', array($this, 'remove_add_to_cart_buttons'), 1 );
            
            add_action( 'woocommerce_checkout_update_order_meta', array($this, 'custom_checkout_field_update_order_meta') );
            
            //++
            add_filter('woocommerce_get_discounted_price', array($this, 'get_discounted_price'), 10, 3);
            add_filter('woocommerce_cart_item_subtotal', array($this, 'cart_item_subtotal'), 10, 3);
           // add_filter('woocommerce_cart_subtotal', array($this, 'cart_subtotal'), 10, 3);
            
            //cart
            add_filter( 'woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 10, 2);  //+
            add_filter( 'woocommerce_add_cart_item', array($this, 'add_cart_item'), 10, 2);  //+
            
            add_filter( 'woocommerce_get_cart_item_from_session', array($this,'get_cart_item_from_session'), 1, 3 );
            
            add_filter( 'woocommerce_get_item_data', array($this, 'show_shipping_in_cart_item'), 10, 2);
            

            //order
            add_action('woocommerce_add_order_item_meta', array($this, 'add_order_item_data'), 10, 2);
                
            //checkout
             if ( function_exists( 'WC' ) && ( version_compare( WC()->version, '3.0.1', "<" ) ) ) 
                    add_filter( 'default_checkout_country', array($this, 'change_default_checkout_country') ); 
             else 
                    add_filter( 'default_checkout_billing_country', array($this, 'change_default_checkout_country') );  
                    
             add_filter( 'woocommerce_checkout_fields', array($this, 'freeze_checkout_country_value') );
        }
        
        function get_discounted_price( $price, $value, $cart ){
          
            $cart_item = $value;
            
            if (isset($cart_item['wpeae_shipping_data'])){
                
                return $price + $cart_item['wpeae_shipping_data']['price']; 
             
            }
            
            return $price;
            
          
        }
        
        function cart_item_subtotal($cart_item_subtotal, $cart_item, $cart_item_key ){
            
            if (!isset($cart_item['wpeae_shipping_data'])) return $cart_item_subtotal;
             
            $product = $cart_item['data'];
            $quantity = $cart_item['quantity'];
            
            $price   = $product->get_price();
            $taxable = $product->is_taxable();
            
            $shipping_price = $cart_item['wpeae_shipping_data']['price'];
            
           // $shipping_price = $this->get_discounted_price(0, $cart_item);

            // Taxable
            if ( $taxable ) {

                if ( 'excl' === WC()->cart->tax_display_cart ) {

                    if ( function_exists( 'WC' ) && ( version_compare( WC()->version, '3.0.0', "<" ) ) )
                        $row_price        = $product->get_price_excluding_tax( $quantity ) + $shipping_price; 
                        
                    else $row_price        = wc_get_price_excluding_tax( $product, array( 'qty' => $quantity ) ) + $shipping_price;   
                   
                    
                    $product_subtotal = wc_price( $row_price );

                    if ( WC()->cart->prices_include_tax && WC()->cart->tax_total > 0 ) {
                        $product_subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
                    }
                    
                    $product_subtotal .= ' <small class="tax_label">' . '(incl. shipping cost)' . '</small>';
                } else {
                    if ( function_exists( 'WC' ) && ( version_compare( WC()->version, '3.0.0', "<" ) ) ) 
                        $row_price        = $product->get_price_including_tax( $quantity ) + + $shipping_price;
                    else
                       $row_price        = wc_get_price_including_tax( $product, array( 'qty' => $quantity ) ) + $shipping_price; 
                      
                    $product_subtotal = wc_price( $row_price );

                    if ( ! WC()->cart->prices_include_tax && WC()->cart->tax_total > 0 ) {
                        $product_subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
                        
                    }
                    
                    $product_subtotal .= ' <small class="tax_label">' . '(incl. shipping cost)' . '</small>';
                }

            // Non-taxable
            } else {

                $row_price        = $price * $quantity + $shipping_price;
                $product_subtotal = wc_price( $row_price );
                $product_subtotal .= ' <small class="tax_label">' . '(incl. shipping cost)' . '</small>';

            }

            return $product_subtotal;    
        }
      /*  
        function cart_subtotal($cart_subtotal, $compound, $cart){
            $cart_subtotal = wc_price( $cart->cart_contents_total + $cart->shipping_total + $cart->get_taxes_total( false, false ) );
            $cart_subtotal .= ' <small class="tax_label">' . '(incl. shipping cost)' . '</small>';
            return $cart_subtotal;
        } */
        
        function assets(){
            
            if ( is_singular( 'product' ) ){
                
                if( !function_exists('get_plugin_data') ){
                    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                }

                $plugin_data = get_plugin_data(__FILE__);

                wp_enqueue_script('wpeae-aliexpress-shipping-product-script', WPEAE_ALIEXPRESS_SHIPPING_ROOT_URL . 'assets/js/wpeae-aliexpress-shipping-product.js', array(), $plugin_data['Version'], true);
                
                wp_enqueue_style('wpeae-aliexpress-shipping-product-style', WPEAE_ALIEXPRESS_SHIPPING_ROOT_URL . 'assets/css/wpeae-aliexpress-shipping-product.css', array(), $plugin_data['Version']);
            }
        }
        
        function remove_add_to_cart_buttons() {
            if( is_product_category() || is_shop()) { 
                remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );
            }
        }
            
        function add_cart_item_data($cart_item_meta, $product_id){
            
            if (isset($_REQUEST['wpeae_shipping_field']))
                $cart_item_meta['wpeae_shipping_field'] = $_REQUEST['wpeae_shipping_field'];
     
            return $cart_item_meta;     
        }
        
        function add_cart_item($cart_item_data, $cart_item_key){
        
            if (!isset($cart_item_data['wpeae_shipping_field'])) return $cart_item_data;
            
            $goods = wpeae_get_goods_by_post_id($cart_item_data['product_id']);
             
            if ($goods && $goods->type === "aliexpress"){
                    
                 $ali_shipping_loader = wpeae_ali_shipping_get_loader();
                      
                 $shipping_data = $ali_shipping_loader->load( new WPEAE_Shipping($goods, $this->get_order_country(), $cart_item_data['quantity']) );
                           
                 $shipping_methods = $shipping_data['data']['ways'];
                        
                 if ( !empty($shipping_methods) ){
                    foreach ($shipping_methods as $m){
                        if ($m['serviceName'] === $cart_item_data['wpeae_shipping_field']){
                             $cart_item_data['wpeae_shipping_data'] = $m;
                   
                             return $cart_item_data;
                        }
                    }
                 }
             }
             
             return $cart_item_data;
               
        }
        
      
        
        public function add_order_item_data ($item_id, $values){
            if (function_exists('woocommerce_add_order_item_meta')) {
                
                $service_name = $values['wpeae_shipping_field'];
                $shipping_title = WPEAE_ShippingPage::get_item(false, $service_name);
                $shipping_title = is_array($shipping_title) ? $shipping_title['title'] : '';
                     
                woocommerce_add_order_item_meta($item_id, 'Shipping', $shipping_title);
            }
    
        }
        
        function get_cart_item_from_session($item, $values, $key){
    
            if ( array_key_exists( 'wpeae_shipping_field', $values ) )
                $item[ 'wpeae_shipping_field' ] = $values['wpeae_shipping_field'];
            
            return $item;    
        }


        
        function show_shipping_in_cart_item( $item_data, $cart_item ){
       
             if (isset($cart_item['wpeae_shipping_data'])){
        
                $value = $this->format_cart_item_shipping_html( $cart_item['wpeae_shipping_data'] );
                $item_data[] = array( 'key'=>__('Shipping', 'wpeae-ali-ship'), 'value'=> $value );
            }
                        
            return $item_data;     
        }
        
        function display_shipping_html(){

            global $post;
    
            if ( $this->aliexpress_product($post->ID)){
                global $woocommerce;
                
                //do not show this html for external product type
                $_product = wc_get_product( $post->ID ); 
                
                if ( function_exists( 'WC' ) && ( version_compare( WC()->version, '3.0.0', "<" ) ) && ( 'external' == $_product->product_type ) )
                     return;
                else if( 'external' == $_product->get_type() ) return;
                
                $countries   = array_merge(array(''=> __('Select a Country','wpeae-ali-ship')),  $this->get_countries());
                    
                $script_data = array(
                    'lang' => array(
                        'select_shipping_method'=>__('Select a Shipping method...','wpeae-ali-ship'),
                        'shipping_country_should_be_the_same'=>__('Shipping country should be the same for all items in your order. You can reset it, if clear your shopping cart.','wpeae-ali-ship')),
                    'ajaxurl' => admin_url( 'admin-ajax.php' ), 
                );
                wp_localize_script( 'wpeae-aliexpress-shipping-product-script', 'wpeae_ali_ship_data', $script_data );

                //remove old data (country and shipping) from session if the cart is empty
                if ( $woocommerce->cart->is_empty() ){
                    WC()->session-> __unset( 'wpeae_to_country_field');
                    WC()->session-> __unset( 'wpeae_shipping_field');  
                  //  $this->clear_shipping_data();  
                } 
                include
                     WPEAE_ALIEXPRESS_SHIPPING_ROOT_PATH . '/view/product_shipping_html.php';
            }
        }
        
        
        function product_shipping_fields_validation() {
            $passed = true;
    
            if ( $this->aliexpress_product($_REQUEST['add-to-cart'])){
                if ( !WC()->session->get( 'wpeae_to_country_field' ) && empty( $_REQUEST['wpeae_to_country_field'] )) {
                    wc_add_notice( __( 'Please select the country where you would like your items to be delivered', 'woocommerce' ), 'error' );
                    $passed = false;    
                }
                
                if ( empty( $_REQUEST['wpeae_shipping_field'] )) {
                    wc_add_notice( __( 'Please select the shipping method', 'woocommerce' ), 'error' );
                    $passed = false;    
                }
                
                if ($passed)
                    WC()->session->set( 'wpeae_to_country_field', $this->get_order_country());
                
            }
            return $passed;
        }
        
        function change_default_checkout_country(){
            return  WC()->session->get( 'wpeae_to_country_field' );
        }
        
        function freeze_checkout_country_value($fields){
           
            $c_code = WC()->session->get( 'wpeae_to_country_field' );
            
            if ($c_code){
                $c_name = WC()->countries->countries[ $c_code ];
                            
                foreach ($fields as &$fieldset) {
                     foreach ($fieldset as $key => &$field) {
                         if ($key == "billing_country" || $key == "shipping_country"){
                            $field['type'] = 'select'; 
                           
                            $field['options'] = array( $c_code => $c_name);   
                         }
                         
                     }
                }
            }
            
            return $fields;
        }
        
        function get_order_country(){
            $cur_wpeae_aliship_shipto = get_option('wpeae_aliship_shipto', 'US');
            return (isset($_REQUEST['wpeae_to_country_field']) ? $_REQUEST['wpeae_to_country_field'] : (WC()->session->get( 'wpeae_to_country_field' ) ? WC()->session->get( 'wpeae_to_country_field' ) : $cur_wpeae_aliship_shipto));    
        }
    
        
        function custom_checkout_field_update_order_meta( $order_id ) {
        
            $wpeae_to_country_field = WC()->session->get( 'wpeae_to_country_field' );
            
            if ( $wpeae_to_country_field ) {
                update_post_meta( $order_id, '_billing_country', esc_attr($wpeae_to_country_field) );
                update_post_meta( $order_id, '_shipping_country', esc_attr($wpeae_to_country_field) );
            }
        }
        
        
        
        //Private functions:

        private function aliexpress_product($product_id){
            $result = false;
            
            $external_info = get_post_meta( $product_id, 'external_id', true );
            
            if ($external_info){
                list($api_type, $external_id, ) = explode('#', $external_info);  
            
                if ($api_type == "aliexpress") $result = true; 
            }
            
            
            return $result;
        }
        
        private function get_countries(){
            $countries_obj   = new WC_Countries();
            $countries   = array_merge( WC()->countries->get_allowed_countries(), WC()->countries->get_shipping_countries() );  
            
            return $countries;  
        }
        
        private function format_cart_item_shipping_html($params){
            return $params['company'] . ", " . $params['time'] . " " . __('days', 'wpeae-ali-ship') . ", " . ($params['price'] > 0 ? $params['price'] . " " . $params['currency'] :  __('free shipping', 'wpeae-ali-ship'));    
        }


    }

    
endif;

new WPEAE_AliexpressShippingFrontend();