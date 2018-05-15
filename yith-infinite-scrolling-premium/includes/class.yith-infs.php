<?php
/**
 * Main class
 *
 * @author Yithemes
 * @package YITH Infinite Scrolling
 * @version 1.0.0
 */


if ( ! defined( 'YITH_INFS' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_INFS' ) ) {
	/**
	 * YITH Infinite Scrolling
	 *
	 * @since 1.0.0
	 */
	class YITH_INFS {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_INFS
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Plugin version
		 *
		 * @var string
		 * @since 1.0.0
		 */
		public $version = YITH_INFS_VERSION;

		/**
		 * Plugin object
		 *
		 * @var string
		 * @since 1.0.0
		 */
		public $obj = null;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_INFS
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @return mixed YITH_INFS_Admin | YITH_INFS_Frontend
		 * @since 1.0.0
		 */
		public function __construct() {

			// Class admin
			if ( $this->is_admin() )  {

			    // require classes
                require_once( 'class.yith-infs-admin.php' );
                require_once( 'class.yith-infs-admin-premium.php' );

			    // Load Plugin Framework
				add_action('after_setup_theme', array($this, 'plugin_fw_loader'), 1);
				
				YITH_INFS_Admin_Premium();
			}
			elseif( $this->load_frontend() ){

			    // require classes
                require_once( 'class.yith-infs-frontend.php' );
                require_once( 'class.yith-infs-frontend-premium.php' );

				// Frontend class
				YITH_INFS_Frontend_Premium();
			}

			// register strings for WPML
			add_action( 'init', array( $this, 'register_wpml_strings' ) );
		}

        /**
         * Check if is admin
         *
         * @since 1.0.6
         * @author Francesco Licandro
         * @return boolean
         */
        public function is_admin(){
            $check_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;
            $check_context = isset( $_REQUEST['context'] ) && $_REQUEST['context'] == 'frontend';

            return is_admin() && ! ( $check_ajax && $check_context );
        }

        /**
         * Check if load frontend class
         *
         * @since 1.0.6
         * @author Francesco Licandro
         * @return boolean
         */
        public function load_frontend(){
            $enable = yinfs_get_option( 'yith-infs-enable', 'yes' ) == 'yes';
            $active_mobile = yinfs_get_option('yith-infs-enable-mobile', 'yes') == 'yes';

            return $enable && ( ! wp_is_mobile() || ( wp_is_mobile() && $active_mobile ) );
        }

		/**
		 * Load Plugin Framework
		 *
		 * @since  1.0.0
		 * @access public
		 * @return void
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function plugin_fw_loader() {
            if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
                global $plugin_fw_data;
                if( ! empty( $plugin_fw_data ) ){
                    $plugin_fw_file = array_shift( $plugin_fw_data );
                    require_once( $plugin_fw_file );
                }
            }
		}

		/**
		 * Register a string to be translated using WPML
		 *
		 * @since 1.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function register_wpml_strings(){
			$options = yinfs_get_option( 'yith-infs-section' );
			if( ! is_array( $options ) ) {
			    return;
            }
			foreach( $options as $section => $option ) {
				if ( isset( $option['buttonLabel'] ) ) {
					do_action( 'wpml_register_single_string', 'yith-infinite-scrolling', 'plugin_yit_infs_' . $section . '_buttonLabel', $option['buttonLabel'] );
				}
			}
		}
	}
}

/**
 * Unique access to instance of YITH_INFS class
 *
 * @return \YITH_INFS
 * @since 1.0.0
 */
function YITH_INFS(){
	return YITH_INFS::get_instance();
}