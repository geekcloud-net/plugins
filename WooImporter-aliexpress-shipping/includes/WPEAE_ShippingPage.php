<?php
/**
 * Description of WPEAE_Shipping
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_ShippingPage')):

	class WPEAE_ShippingPage {
		
		
		public function __construct() {
		    add_action( 'init', array($this, 'init') );
            add_action( 'admin_init', array($this, 'admin_init') );
            add_action ( 'manage_wpeae_shipping_posts_columns', array($this, 'get_columns'));
            add_action( 'manage_wpeae_shipping_posts_custom_column', array($this, "edit_columns") );
            add_action( 'save_post', array($this, 'save_details') );
		}
        
       
        public function get_columns($columns){
             $columns = array(
                "cb" => '<input type="checkbox">',
                "title" => 'Shipping name',
                "initial_name" => "Initial name",
                "service_name" => "Service name"
              );
             
              return $columns;    
        }
        
        public function edit_columns($column){
              global $post;
 
              switch ($column) {
                    case "initial_name":
                      echo get_post_meta($post->ID, 'wpeae_text_initial_name', true);
                      break;
                    case "service_name":
                      echo get_post_meta($post->ID, 'wpeae_service_name', true);
                      break;
              }    
              
              return $column;
        }
                                                                  
        public function init() {

              register_post_type( 'wpeae_shipping',
                array(
                  'labels' => array(
                    'name' => __( 'Aliexpress Shipping' ),
                    'singular_name' => __( 'Shipping' )
                  ),
                  'public' => false,
                  'publicly_queriable' => true,
                  'show_ui' => true,
                  'exclude_from_search' => true,
                  'has_archive' => false,
                  'show_in_menu'  => 'wpeae-dashboard-aliexpress',
                  'supports'           => array( 'title',),
                  'rewrite'            => false,
                   'capabilities' => array(
                        'create_posts' => 'do_not_allow', // false < WP 4.5, credit @Ewout
                      ),
                   'map_meta_cap' => true, // Set to `false`, if users are not allowed to edit/delete existing posts
              
                )
              );
              
        }
        
  
        
        public function admin_init(){
              add_meta_box("wpeae_shipping_settings_metabox", "Shipping settings", array($this,"settings_metabox"), "wpeae_shipping", "normal", "high");    
        }
        
        public function settings_metabox(){
              global $post;
              $custom = get_post_custom($post->ID);
              
              $initial_name = $custom["wpeae_text_initial_name"][0];
              $service_name = $custom["wpeae_service_name"][0];
              ?>
              <table class="form-table">
              <tr><th><label>Initial Name</label></th><td><input type="text" value="<?php echo $initial_name;?>" readonly><br/><em>readonly</em></td></tr>
               <tr><th><label>Service Name</label></th><td><input type="text" value="<?php echo $service_name;?>" readonly><br/><em>readonly</em></td></tr>
              </table>
              <?php
        } 
  
        public function save_details(){
              global $post;
 
             //save some fields..
      
        }
        
        //get shipping data by initial shipping name or by service name
        static public function get_item($shipping_name = false, $service_name = false){
            if ( $shipping_name )
                $args = array(
                    'meta_key' => 'wpeae_text_initial_name',
                    'meta_value' => $shipping_name,
                    'post_type' => 'wpeae_shipping',
                    'post_status' => 'publish',
                    'posts_per_page' => -1
                );
            
            if ($service_name)
                $args = array(
                    'meta_key' => 'wpeae_service_name',
                    'meta_value' => $service_name,
                    'post_type' => 'wpeae_shipping',
                    'post_status' => 'publish',
                    'posts_per_page' => -1
                );
                
            $posts = get_posts($args);  
            
            if (count($posts)<1) return false;
            
            $post = $posts[0];
            
            if ( $shipping_name ) return array('id'=>$post->ID, 'title'=>$post->post_title, 'init_name'=>$shipping_name );
            if ( $service_name ) return array('id'=>$post->ID, 'title'=>$post->post_title, 'service_name'=>$service_name );
            
            return false;
        }
	
        //add new shipping data
        static public function add_item($shipping_name, $service_name){
            $id = wp_insert_post(array('post_title'=>$shipping_name, 'post_type'=>'wpeae_shipping', 'post_status' => 'publish'));
            add_post_meta($id, 'wpeae_text_initial_name', $shipping_name);
            add_post_meta($id, 'wpeae_service_name', $service_name);
            
            return $id;    
        }
    }

	
endif;

new WPEAE_ShippingPage();