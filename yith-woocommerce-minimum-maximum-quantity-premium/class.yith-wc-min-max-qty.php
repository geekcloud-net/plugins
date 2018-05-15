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
	exit;
} // Exit if accessed directly

/**
 * Main class
 *
 * @class   YITH_WC_Min_Max_Qty
 * @package Yithemes
 * @since   1.0.0
 * @author  Your Inspiration Themes
 */

if ( ! class_exists( 'YITH_WC_Min_Max_Qty' ) ) {

	class YITH_WC_Min_Max_Qty {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WC_Min_Max_Qty
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Panel object
		 *
		 * @var     /Yit_Plugin_Panel object
		 * @since   1.0.0
		 * @see     plugin-fw/lib/yit-plugin-panel.php
		 */
		protected $_panel = null;

		/**
		 * @var $_premium string Premium tab template file name
		 */
		protected $_premium = 'premium.php';

		/**
		 * @var string Premium version landing link
		 */
		protected $_premium_landing = 'http://yithemes.com/themes/plugins/yith-woocommerce-minimum-maximum-quantity/';

		/**
		 * @var string Plugin official documentation
		 */
		protected $_official_documentation = 'http://yithemes.com/docs-plugins/yith-woocommerce-minimum-maximum-quantity/';

		/**
		 * @var string YITH WooCommerce Minimum Maximum Quantity panel page
		 */
		protected $_panel_page = 'yith-wc-min-max-qty';

		/**
		 * @var bool Check if WooCommerce version is lower than 2.6
		 */
		public $is_wc_lower_2_6;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WC_Min_Max_Qty
		 * @since 1.0.0
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {

				self::$instance = new self;

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

			if ( ! function_exists( 'WC' ) ) {
				return;
			}

			//Load plugin framework
			add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 12 );
			add_filter( 'plugin_action_links_' . plugin_basename( YWMMQ_DIR . '/' . basename( YWMMQ_FILE ) ), array( $this, 'action_links' ) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );
			add_action( 'admin_menu', array( $this, 'add_menu_page' ), 5 );
			add_action( 'yith_minimum_maximum_quantity_premium', array( $this, 'premium_tab' ) );

			$this->is_wc_lower_2_6 = version_compare( WC()->version, '2.6.0', '<' );

			if ( ! is_admin() ) {
				add_action( 'wp', array( $this, 'ywmmq_cart_validation' ) );
			}

		}

		/**
		 * ADMIN FUNCTIONS
		 */

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 * @use     /Yit_Plugin_Panel class
		 * @see     plugin-fw/lib/yit-plugin-panel.php
		 */
		public function add_menu_page() {

			if ( ! empty( $this->_panel ) ) {
				return;
			}

			if ( defined( 'YWMMQ_PREMIUM' ) ) {
				$admin_tabs['premium-general'] = __( 'General Settings', 'yith-woocommerce-minimum-maximum-quantity' );
				$admin_tabs['messages']        = __( 'Message Settings', 'yith-woocommerce-minimum-maximum-quantity' );
				$admin_tabs['bulk']            = __( 'Bulk Actions', 'yith-woocommerce-minimum-maximum-quantity' );
				$admin_tabs['howto']           = __( 'How To', 'yith-woocommerce-minimum-maximum-quantity' );
			} else {
				$admin_tabs['general']         = __( 'General Settings', 'yith-woocommerce-minimum-maximum-quantity' );
				$admin_tabs['premium-landing'] = __( 'Premium Version', 'yith-woocommerce-minimum-maximum-quantity' );
			}

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'page_title'       => _x( 'Minimum Maximum Quantity', 'plugin name in admin page title', 'yith-woocommerce-minimum-maximum-quantity' ),
				'menu_title'       => _x( 'Minimum Maximum Quantity', 'plugin name in admin WP menu', 'yith-woocommerce-minimum-maximum-quantity' ),
				'capability'       => 'manage_options',
				'parent'           => '',
				'parent_page'      => 'yit_plugin_panel',
				'page'             => $this->_panel_page,
				'admin-tabs'       => $admin_tabs,
				'options-path'     => YWMMQ_DIR . 'plugin-options'
			);

			$this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );

		}

		/**
		 * FRONTEND FUNCTIONS
		 */

		/**
		 * Validates cart.
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywmmq_cart_validation() {

			if ( apply_filters( 'ywmmq_exclude_role_from_rules', false ) ) {
				return;
			}

			$on_cart_page     = is_page( wc_get_page_id( 'cart' ) );
			$on_checkout_page = is_checkout() && ! is_checkout_pay_page() && ! is_order_received_page();

			if ( $on_cart_page || $on_checkout_page ) {

				$cart_update_notice = __( 'Cart updated.', 'woocommerce' );
				$cart_update        = wc_has_notice( $cart_update_notice );
				wc_clear_notices();

				if ( WC()->cart->cart_contents ) {

					if ( $on_cart_page ) {
						$current_page = 'cart';
					} else {
						$current_page = '';
					}

					$errors = array();

					$is_premium = defined( 'YWMMQ_PREMIUM' ) && YWMMQ_PREMIUM;

					if ( $is_premium && get_option( 'ywmmq_product_quantity_limit' ) == 'yes' ) {

						$this->ywmmq_product_quantity_cart( $current_page, $on_cart_page, $errors );

					}

					if ( get_option( 'ywmmq_cart_quantity_limit', 'yes' ) == 'yes' ) {

						$this->ywmmq_check_validation_cart( $this->ywmmq_validate_cart_quantity( $current_page ), $on_cart_page, $errors );

					}

					if ( $is_premium && get_option( 'ywmmq_cart_value_limit' ) == 'yes' ) {

						$this->ywmmq_check_validation_cart( $this->ywmmq_validate_cart_value( $current_page ), $on_cart_page, $errors );

					}

					if ( $is_premium && get_option( 'ywmmq_category_quantity_limit' ) == 'yes' ) {

						$this->ywmmq_category_quantity_cart( $current_page, $on_cart_page, $errors );

					}

					if ( $is_premium && get_option( 'ywmmq_category_value_limit' ) == 'yes' ) {

						$this->ywmmq_category_value_cart( $current_page, $on_cart_page, $errors );

					}

					if ( $is_premium && get_option( 'ywmmq_tag_quantity_limit' ) == 'yes' ) {

						$this->ywmmq_tag_quantity_cart( $current_page, $on_cart_page, $errors );

					}

					if ( $is_premium && get_option( 'ywmmq_tag_value_limit' ) == 'yes' ) {

						$this->ywmmq_tag_value_cart( $current_page, $on_cart_page, $errors );

					}

					if ( $errors ) {

						ob_start();

						?>

						<ul>
							<?php foreach ( $errors as $error ): ?>
								<li><?php echo $error ?></li>
							<?php endforeach; ?>
							<?php echo apply_filters( 'ywmmq_additional_notification', '' ); ?>
						</ul>

						<?php

						$error_list = ob_get_clean();

						wc_add_notice( $error_list, 'error' );

					}

					if ( $cart_update ) {
						wc_add_notice( $cart_update_notice );
					}

				}

			}

		}

		/**
		 * Check the return value, if it is invalid returns an error message
		 *
		 * @since    1.0.0
		 *
		 * @param   $data
		 * @param   $on_cart_page
		 * @param   $errors
		 *
		 * @return   void
		 * @author   Alberto Ruggiero
		 */
		public function ywmmq_check_validation_cart( $data, $on_cart_page, &$errors ) {

			if ( ! $data['is_valid'] ) {

				if ( $on_cart_page ) {

					$errors[] = $data['message'];

				} else {

					if ( version_compare( WC_VERSION, '2.5.0', '<' ) ) {

						$cart_url = WC()->cart->get_cart_url();

					} else {

						$cart_url = wc_get_cart_url();

					}

					wp_safe_redirect( $cart_url, 302 );
					exit;

				}

			}

		}

		/**
		 * CART QUANTITY RULES FUNCTIONS
		 */

		/**
		 * Validate the cart quantity limit and return error messages
		 *
		 * @since   1.0.0
		 *
		 * @param   $current_page
		 * @param   $added_qty
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function ywmmq_validate_cart_quantity( $current_page = '', $added_qty = 0 ) {

			$return = array(
				'is_valid' => true
			);

			$cart_limit = $this->ywmmq_cart_limits( 'quantity' );

			$total_cart_qty = $this->ywmmq_cart_total_qty();
			$total_cart_qty += $added_qty;

			if ( (int) $cart_limit['min'] != 0 && $total_cart_qty < (int) $cart_limit['min'] ) {

				$return['is_valid'] = false;
				$return['limit']    = 'min';

				if ( $current_page ) {

					$return['message'] = apply_filters( 'ywmmq_cart_qty_error', sprintf( __( 'Your cart must contain at least %s products.', 'yith-woocommerce-minimum-maximum-quantity' ), $cart_limit['min'] ), 'min', $cart_limit['min'], $total_cart_qty, $current_page, 'quantity' );

				}

			} elseif ( (int) $cart_limit['max'] != 0 && $total_cart_qty > (int) $cart_limit['max'] ) {

				$return['is_valid'] = false;
				$return['limit']    = 'max';

				if ( $current_page ) {

					$return['message'] = apply_filters( 'ywmmq_cart_qty_error', sprintf( __( 'Your cart cannot contain more than %s products.', 'yith-woocommerce-minimum-maximum-quantity' ), $cart_limit['max'] ), 'max', $cart_limit['max'], $total_cart_qty, $current_page, 'quantity' );

				}

			}

			return $return;

		}

		/**
		 * Return the total quantity of all items in the cart
		 *
		 * @since   1.0.0
		 * @return  int
		 * @author  Alberto Ruggiero
		 */
		public function ywmmq_cart_total_qty() {

			$total_qty = 0;

			foreach ( WC()->cart->cart_contents as $item_id => $item ) {

				if ( ! isset( $item['product_id'] ) || $item_id == 'cart' ) {
					continue;
				}

				if ( apply_filters( 'ywmmq_bundle_check', false, $item ) ) {
					continue;
				}

				if ( apply_filters( 'ywmmq_check_exclusion', false, $item_id, $item['product_id'] ) ) {
					continue;
				}

				$total_qty += $item['quantity'];

			}

			return $total_qty;

		}

		/**
		 * Return quantity/value limits for specified category
		 *
		 * @since   1.0.0
		 *
		 * @param   $type
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function ywmmq_cart_limits( $type = 'quantity' ) {

			$limit = array(
				'min' => get_option( 'ywmmq_cart_minimum_' . $type ),
				'max' => get_option( 'ywmmq_cart_maximum_' . $type )
			);

			return $limit;

		}

		/**
		 * YITH FRAMEWORK
		 */

		/**
		 * Load plugin framework
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function plugin_fw_loader() {
			if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
				global $plugin_fw_data;
				if ( ! empty( $plugin_fw_data ) ) {
					$plugin_fw_file = array_shift( $plugin_fw_data );
					require_once( $plugin_fw_file );
				}
			}
		}

		/**
		 * Premium Tab Template
		 *
		 * Load the premium tab template on admin page
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function premium_tab() {
			$premium_tab_template = YWMMQ_TEMPLATE_PATH . '/admin/' . $this->_premium;
			if ( file_exists( $premium_tab_template ) ) {
				include_once( $premium_tab_template );
			}
		}

		/**
		 * Get the premium landing uri
		 *
		 * @since   1.0.0
		 * @return  string The premium landing link
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function get_premium_landing_uri() {
			return defined( 'YITH_REFER_ID' ) ? $this->_premium_landing . '?refer_id=' . YITH_REFER_ID : $this->_premium_landing;
		}

		/**
		 * Action Links
		 *
		 * add the action links to plugin admin page
		 * @since   1.0.0
		 *
		 * @param   $links | links plugin array
		 *
		 * @return  mixed
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use     plugin_action_links_{$plugin_file_name}
		 */
		public function action_links( $links ) {

			$links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'yith-woocommerce-minimum-maximum-quantity' ) . '</a>';

			if ( defined( 'YWMMQ_FREE_INIT' ) ) {
				$links[] = '<a href="' . $this->get_premium_landing_uri() . '" target="_blank">' . __( 'Premium Version', 'yith-woocommerce-minimum-maximum-quantity' ) . '</a>';
			}

			return $links;
		}

		/**
		 * Plugin row meta
		 *
		 * add the action links to plugin admin page
		 *
		 * @since   1.0.0
		 *
		 * @param   $plugin_meta
		 * @param   $plugin_file
		 * @param   $plugin_data
		 * @param   $status
		 *
		 * @return  Array
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use     plugin_row_meta
		 */
		public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
			if ( ( defined( 'YWMMQ_INIT' ) && ( YWMMQ_INIT == $plugin_file ) ) ||
			     ( defined( 'YWMMQ_FREE_INIT' ) && ( YWMMQ_FREE_INIT == $plugin_file ) )
			) {

				$plugin_meta[] = '<a href="' . $this->_official_documentation . '" target="_blank">' . __( 'Plugin Documentation', 'yith-woocommerce-minimum-maximum-quantity' ) . '</a>';
			}

			return $plugin_meta;
		}

	}

}