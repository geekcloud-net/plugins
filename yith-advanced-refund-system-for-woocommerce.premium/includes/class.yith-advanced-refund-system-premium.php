<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'YITH_WCARS_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 *
 *
 * @class      YITH_Advanced_Refund_System_Premium
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     Your Inspiration Themes
 *
 */

if ( ! class_exists( 'YITH_Advanced_Refund_System_Premium' ) ) {
	/**
	 * Class YITH_Advanced_Refund_System_Premium
	 *
	 * @author Carlos Mora <carlos.eugenio@yourinspiration.it>
	 */
	class YITH_Advanced_Refund_System_Premium extends YITH_Advanced_Refund_System {

        /**
         * Construct
         *
         * @author Carlos Mora <carlos.eugenio@yourinspiration.it>
         * @since 1.0
         */
        protected function __construct(){

            parent::__construct();

			/* === Require Main Files === */
			require_once( YITH_WCARS_PATH . 'includes/class.yith-advanced-refund-system-admin-premium.php' );
			require_once( YITH_WCARS_PATH . 'includes/class.yith-advanced-refund-system-frontend-premium.php' );
	        require_once( YITH_WCARS_PATH . 'includes/class.yith-advanced-refund-system-request-manager-premium.php' );
	        require_once( YITH_WCARS_PATH . 'includes/class.yith-advanced-refund-system-my-account-premium.php' );

	        add_filter( 'ywcars_request_statuses', array( $this, 'add_coupon_status' ) );
	        add_filter( 'ywcars_finished_request', array( $this, 'finished_request' ), 10, 2 );

        }

        /**
		 * Main plugin Instance
		 *
		 * @return YITH_Advanced_Refund_System_Premium Main instance
		 * @author Carlos Mora <carlos.eugenio@yourinspiration.it>
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}



        /**
		 * Class Initialization
		 *
		 * Instance the admin or frontend classes
		 *
		 * @author Carlos Mora <carlos.eugenio@yourinspiration.it>
		 * @since  1.0
		 * @return void
		 * @access protected
		 */

		public function init() {
            $this->ywcars_init_post_type();
            $this->ywcars_init_post_statuses();
            $this->check_uploads_folder();

			register_activation_hook( YITH_WCARS_FILE, array( 'YITH_Advanced_Refund_System_My_Account', 'install' ) );

			$this->my_account      = new YITH_Advanced_Refund_System_My_Account_Premium();
			$this->request_manager = new YITH_Advanced_Refund_System_Request_Manager_Premium();

            if ( is_admin() ) {
				$this->admin = new YITH_Advanced_Refund_System_Admin_Premium();
			}

			if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				$this->frontend = new YITH_Advanced_Refund_System_Frontend_Premium();
			}
		}

		public function check_uploads_folder() {
			if ( ! file_exists( YITH_WCARS_UPLOADS_DIR ) ) {
				wp_mkdir_p( YITH_WCARS_UPLOADS_DIR );
			}
		}

		// Coupon Offered status is only available on Premium version
		public function add_coupon_status( $request_statuses ) {
			$request_statuses['ywcars-coupon'] = _x( 'Coupon offered', 'Request status', 'yith-advanced-refund-system-for-woocommerce' );

			return $request_statuses;
		}

		public function finished_request( $bool, $request ) {
			return 'ywcars-approved' == $request->status || 'ywcars-rejected' == $request->status || 'trash' == $request->status || 'ywcars-coupon' == $request->status;
		}
		
    }
}