<?php
/* * class
 * Description of WPEAE_WooCommerce_OrderList
 *
 * @author Geometrix
 * 
 * @position: -1
 */
if (!class_exists('WPEAE_WooCommerce_OrderList')) :
 
	class WPEAE_WooCommerce_OrderList {
		
		public function __construct() {
			if(is_admin()) {        
				add_action('admin_enqueue_scripts', array($this, 'assets'));
				add_action('manage_shop_order_posts_custom_column', array($this, 'columns_data'), 100);
                
                add_action('admin_init', array($this, 'admin_init'));
                
			}
		}
        
        function admin_init(){
                if ( function_exists( 'WC' ) && ( version_compare( WC()->version, '3.3.0', ">=" ) ) ) 
                    add_filter('woocommerce_admin_order_actions', array($this, 'admin_order_actions'), 2, 100); 
        }
		
		function assets() {
					
			$plugin_data = get_plugin_data( WPEAE_FILE_FULLNAME );                 
			wp_enqueue_style('wpeae-wc-ol-style', plugins_url('assets/css/wc_ol_style.css', WPEAE_FILE_FULLNAME), array(), $plugin_data['Version']);
			wp_enqueue_script('jquery-ui-dialog');
			wp_enqueue_script('wpeae-wc-ol-script', plugins_url('assets/js/wc_ol_script.js', WPEAE_FILE_FULLNAME ), array(), $plugin_data['Version']);
			
			$lang_data = array(
				'please_wait_data_loads'=>_x('Please wait, data loads..','Status','wpeae'),
			);
			
			wp_localize_script('wpeae-wc-ol-script', 'wpeae_wc_ol_script', array('lang' => $lang_data));
		}
        
        function admin_order_actions ($actions, $object){

             $actions['wpeae'] = array(
                'group' => 'WPEAE',
                'actions' => array()
             );
             
             
             $actions['wpeae']['actions'][] = array (
                'url'    => '#'.$object->get_id(),
                'name'   => __( 'WooImporter Info', 'woocommerce' ),
                'action' => 'wpeae-order-info',
             );
     
             $actions = apply_filters('wpeae_order_actions', $actions, $object);           
             
             return $actions;
            
            
        } 
		
		function columns_data($column){
			 global $post;

			 $actions = array();
			 
			 if ($column == 'order_title'){ 
				$actions = array_merge( $actions, array(
					'wpeae_product_info' => sprintf( '<a class="wpeae-order-info" id="wpeae-%1$d" href="/">%2$s</a>', $post->ID, 'WooImporter Info' )
				) );
				 
			 }
			 
			 $actions = apply_filters('wpeae_wcol_row_actions', $actions, $column);
				 
			 if (count($actions)>0){
				echo implode($actions, ' | ');    
			 }
											
		}    
	}
	
 endif;
 new WPEAE_WooCommerce_OrderList();