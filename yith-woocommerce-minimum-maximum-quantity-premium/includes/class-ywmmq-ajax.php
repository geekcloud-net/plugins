<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'YWMMQ_Ajax' ) ) {

	/**
	 * Implements AJAX for YWMMQ plugin
	 *
	 * @class   YWMMQ_Ajax
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 *
	 */
	class YWMMQ_Ajax {

		/**
		 * Single instance of the class
		 *
		 * @var \YWMMQ_Ajax
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YWMMQ_Ajax
		 * @since 1.0.0
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {

				self::$instance = new self( $_REQUEST );

			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since   1.0.0
		 * @return  mixed
		 * @author  Alberto Ruggiero
		 */
		public function __construct() {

			add_action( 'wp_ajax_ywmmq_get_rules', array( $this, 'get_rules' ) );
			add_action( 'wp_ajax_nopriv_ywmmq_get_rules', array( $this, 'get_rules' ) );

		}

		/**
		 * Send a test mail from option panel
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function get_rules() {

			$response = array();

			try {

				ob_start();
				YITH_WMMQ()->ywmmq_add_rules_text( $_POST['product_id'], $_POST['variation_id'] );

				$response['status'] = 'success';
				$response['rules']  = ob_get_clean();

				$product_id = $_POST['product_id'];

				global $sitepress;
				$has_wpml = ! empty( $sitepress ) ? true : false;

				if ( $has_wpml && apply_filters( 'ywmmq_wpml_use_default_language_settings', false ) ) {
					$product_id = yit_wpml_object_id( $product_id, 'product', true, wpml_get_default_language() );
				}

				$product             = wc_get_product( $product_id );
				$set_quantity_locked = apply_filters( 'ywmmq_set_variation_quantity_locked', true );

				if ( yit_get_prop( $product, '_ywmmq_product_quantity_limit_variations_override' ) == 'yes' && $set_quantity_locked ) {

					$response['limits'] = YITH_WMMQ()->ywmmq_product_limits( $_POST['product_id'], $_POST['variation_id'] );

				} else {

					$response['limits'] = array(
						'min'  => 0,
						'max'  => 0,
						'step' => 1
					);

				}

			} catch ( Exception $e ) {

				if ( ! empty( $e ) ) {
					$response['status'] = 'failure';
				}

			}

			wp_send_json( $response );

			exit;

		}

	}

	/**
	 * Unique access to instance of YWMMQ_Ajax class
	 *
	 * @return \YWMMQ_Ajax
	 */
	function YWMMQ_Ajax() {

		return YWMMQ_Ajax::get_instance();

	}

	new YWMMQ_Ajax();

}