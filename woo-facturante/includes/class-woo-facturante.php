<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.0.1
 * @package    Woo Facturante
 * @subpackage woo-facturante/includes
 * @author     Hernán Galván <hernan@fuegoyamana.com>
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 
class Woo_Facturante {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      Plugin_Name_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.0.1
	 */
	public function __construct() {

		$this->plugin_name = 'WooCommerce Facturante';
		
		$this->version = '0.1.53';

		$this->load_dependencies();
		
		$this->set_locale();
		
		$this->define_admin_hooks();
		
		$this->define_public_hooks();
		
		add_action('woocommerce_api_'.strtolower(get_class($this)), array(&$this, 'handle_callback'));

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woo_Facturante_Loader. Orchestrates the hooks of the plugin.
	 * - Woo_Facturante_i18n. Defines internationalization functionality.
	 * - Woo_Facturante_Admin. Defines all hooks for the admin area.
	 * - Woo_Facturante_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-facturante-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-facturante-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woo-facturante-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-plugin-name-public.php';

		$this->loader = new Woo_Facturante_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Woo_Facturante_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.1.44
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Woo_Facturante_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
		$this->loader->add_filter( 'woocommerce_settings_tabs_array', $plugin_admin, 'add_settings_tab',50 );
		
		$this->loader->add_action( 'woocommerce_settings_tabs_settings_tab_woo_facturante',$plugin_admin,'settings_tab' );
		
		$this->loader->add_action( 'woocommerce_update_options_settings_tab_woo_facturante',$plugin_admin,'update_settings' );
		
		$this->loader->add_filter( 'woocommerce_admin_order_actions', $plugin_admin, 'woo_facturante_order_actions',10,2 );
		
		$this->loader->add_action( 'wp_ajax_woo_facturante_do_ajax_request', $plugin_admin, 'woo_facturante_invoice' );
		
		$this->loader->add_action( 'wp_ajax_woo_facturante_view_ajax_request', $plugin_admin, 'woo_facturante_view_invoice' );
		
		$this->loader->add_action( 'woocommerce_created_customer' ,$plugin_admin, 'woo_facturante_save_DNI' );
		
		$this->loader->add_action( 'woocommerce_edit_account_form',$plugin_admin, 'woo_facturante_add_dni_field_to_my_account');
		
		$this->loader->add_action( 'woocommerce_save_account_details',$plugin_admin, 'woo_facturante_save_DNI' );
		
		$this->loader->add_action( 'woocommerce_checkout_fields' ,$plugin_admin, 'woo_facturante_dni_checkout_field' );
		
		$this->loader->add_action( 'woocommerce_checkout_process', $plugin_admin, 'woo_facturante_checkout_field_process' );
		
		$this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_admin, 'woo_facturante_update_order_meta' );
		
		$this->loader->add_action( 'woocommerce_admin_order_data_after_billing_address', $plugin_admin, 'woo_facturante_display_admin_order_meta');
		
		$this->loader->add_filter( 'woocommerce_email_order_meta_keys', $plugin_admin, 'woo_facturante_display_dni_in_email_fields' ); 
		
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'woo_facturante_add_metaboxes' );
		
		$this->loader->add_action('admin_notices', $plugin_admin, 'woo_warning');
		
				
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_public_hooks() {

		//$plugin_public = new Plugin_Name_Public( $this->get_plugin_name(), $this->get_version() );

		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.0.1
	 */
	public function run() {
		
		$this->loader->run();
	
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.0.1
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		
		return $this->plugin_name;
	
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.0.1
	 * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		
		return $this->loader;
	
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.0.1
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		
		return $this->version;
	
	}
	
	public function handle_callback(){
		
		global $wpdb;
		
		ob_start();
		
		if ( ! isset( $_GET['order'] ) || ! isset($_POST['DetalleComprobante']) )  {
			
			echo 'error (order o detalleComprobante inválidos) ';
		
		}	
		else	
		{
			
				$hash = get_option( 'wc_settings_tab_woo_facturante_hash' );
		
				if ( $hash !== $_GET['h'] )  {
					
					echo 'error (invalid hash)';
					
				}
				
				$order_id = intval( $_GET['order'] );
				
				$xml = simplexml_load_string(preg_replace('/(<\?xml[^?]+?)utf-16/i', '$1utf-8',stripslashes($_POST["DetalleComprobante"])));
				
				switch($xml->EstadoComprobante){
					
					case 4:
				
						$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->postmeta." SET meta_value=2 WHERE post_id = %d AND meta_key = '_estado_facturante' ", $order_id) );
						
						break;
					
					case 6:
						
						$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->postmeta." SET meta_value=3 WHERE post_id = %d AND meta_key = '_estado_facturante' ", $order_id ) );
						
						break;
					
					case 10:
					
						$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->postmeta." SET meta_value=1 WHERE post_id = %d AND meta_key = '_estado_facturante' ", $order_id) );
					
					default:
					 
							echo 'Estado no contemplado '.var_dump($xml);
					break; 
					
				}
		
		}
		
		/*
		
		var_dump($xml);
		
		$content = ob_get_contents();
		
		$f = fopen("log-".time().".txt", "w");
		fwrite($f, $content);
		fclose($f); 
		*/

		exit;
	}

}