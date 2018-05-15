<?php
/**
 * Plugin's main class.
 *
 * @package WooCommerce_Instagram
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WooCommerce Instagram Class
 *
 * @package WordPress
 * @subpackage Woocommerce_Instagram
 * @category Core
 * @author WooThemes
 * @since 1.0.0
 */
class Woocommerce_Instagram {
	/**
	 * Token as plugin identification.
	 *
	 * @var string
	 */
	private $_token;

	/**
	 * Plugin's main file.
	 *
	 * @var string
	 */
	private $_file;

	/**
	 * Handler context.
	 *
	 * @var object Woocommerce_Instagram_Admin or Woocommerce_Instagram_Frontend.
	 */
	public $context;

	/**
	 * API instance.
	 *
	 * @var Woocommerce_Instagram_API
	 */
	public $api;

	/**
	 * Constructor function.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file Plugin's main file.
	 *
	 * @return  void
	 */
	public function __construct( $file ) {
		$this->_token     = 'woocommerce-instagram';
		$this->_file      = $file;
		$this->_has_video = false;

		add_action( 'plugins_loaded', array( $this, 'init' ), 0 );
	}

	/**
	 * Initialize the plugin, check the environment and make sure we can act.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		$this->load_plugin_textdomain();

		// Make sure WooCommerce is active.
	    if ( is_woocommerce_active() ) {
		    // Setup the API object.
		    require_once( 'class-woocommerce-instagram-api.php' );
		    $this->api = new Woocommerce_Instagram_API( $this->_file );
		    // Setup the context, based on admin/frontend.
		    if ( is_admin() ) {
			    require_once( 'class-woocommerce-instagram-admin.php' );
			    $this->context = new Woocommerce_Instagram_Admin( $this->_file, $this->api );
		    } else {
			    require_once( 'class-woocommerce-instagram-frontend.php' );
			    $this->context = new Woocommerce_Instagram_Frontend( $this->_file, $this->api );
		    }
	    }
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-instagram' );

		load_textdomain( 'woocommerce-instagram', trailingslashit( WP_LANG_DIR ) . 'woocommerce-instagram/woocommerce-instagram-' . $locale . '.mo' );
		load_plugin_textdomain( 'woocommerce-instagram', false, dirname( plugin_basename( $this->_file ) ) . '/languages/' );
	}
}
