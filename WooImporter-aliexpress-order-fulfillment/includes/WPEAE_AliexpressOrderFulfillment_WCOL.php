<?php

/**
 * Description of WPEAE_AliexpressOrderFulfillment_WCOL
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_AliexpressOrderFulfillment_WCOL')):
class WPEAE_AliexpressOrderFulfillment_WCOL {
    
        private $upd_rvws_task_id = "wpeae_product_update_reviews_manual";
        
        public function __construct() {
            if(is_admin()) {
                add_action('admin_enqueue_scripts', array($this, 'assets'));
                add_filter('wpeae_wcol_row_actions', array($this, 'row_actions_init'), 2, 10);         
            }
            
            add_filter('wpeae_order_actions', array($this, 'order_actions_init'), 2, 10);  
               
        }
        
    
        function assets() {
                    
            $plugin_data = get_plugin_data( WPEAE_FILE_FULLNAME );   
            
            wp_enqueue_script('wpeae-ali-orderfulfill-js', WPEAE_ALIEXPRESS_ORDER_FULFILLMENT_ROOT_URL . 'assets/js/script.js', array(), $plugin_data['Version'], true);
         
        }
        
        function order_actions_init($actions, $object){
       
             $actions['wpeae']['actions'][] = array (
                'url'    => '#'.$object->get_id(),
                'name'   => __( 'Aliexpress Order fulfillment', 'woocommerce' ),
                'action' => 'wpeae_aliexpress_order_fulfillment',
             );
             
            return $actions;     
        }
        
        function row_actions_init($actions, $column){
            
             if ($column == 'order_title'){ 
                 global $post;
                      
                 $actions['wpeae_aliexpress_order_fulfillment'] = 
                    sprintf( '<a class="wpeae_aliexpress_order_fulfillment" id="wpeae-%1$d" href="/">%2$s</a>', $post->ID, 'Aliexpress Order Fulfillment' );
            
             }
  
            return $actions;    
        }
            
}
endif;

new WPEAE_AliexpressOrderFulfillment_WCOL();