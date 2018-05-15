<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Role_Based_Prices_Product' ) ) {

	class YITH_Role_Based_Prices_Product {
		protected static $instance;

		/**
		 * YITH_Role_Based_Prices_Product constructor
		 */
		public function __construct() {

			$this->post_type = YITH_Role_Based_Type();
			//$this->rule      = $this->post_type->get_price_rule();

			add_action( 'admin_enqueue_scripts', array( $this, 'include_admin_product_style_script' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'include_frontend_product_style_script' ) );

			add_action( 'init', array( $this, 'init_user_info' ), 10 );

			add_action( 'woocommerce_single_product_summary', array(
				$this,
				'remove_add_to_cart_with_request_a_quote'
			), 10 );
			add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'hide_add_to_cart_loop' ), 10, 2 );
			add_filter( 'option_woocommerce_tax_display_shop', array( $this, 'show_price_incl_excl_tax' ), 10, 2 );
			add_filter( 'option_woocommerce_tax_display_cart', array( $this, 'show_price_incl_excl_tax' ), 10, 2 );
			add_filter( 'woocommerce_is_purchasable', array( $this, 'is_purchasable' ), 15, 2 );
			add_filter( 'woocommerce_variation_is_purchasable', array( $this, 'is_purchasable' ), 15, 2 );
			add_action( 'woocommerce_product_options_general_product_data', array( $this, 'show_product_price_rule' ) );
			add_action( 'woocommerce_variation_options_pricing', array(
				$this,
				'show_product_variation_price_rule'
			), 10, 3 );
			add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_meta' ), 25, 2 );
			add_action( 'woocommerce_save_product_variation', array( $this, 'save_product_variation_meta' ), 25, 2 );
			add_action( 'woocommerce_single_product_summary', array( $this, 'single_product_summary' ), 5 );
			add_action( 'woocommerce_variable_product_sync_data', array( $this, 'variable_product_sync' ), 20, 1 );

			add_filter( 'woocommerce_product_get_price', array( $this, 'get_price' ), 20, 2 );
			add_filter( 'woocommerce_product_variation_get_price', array( $this, 'get_price' ), 20, 2 );

			// support to WooCommerce Product Bundles
			add_filter( 'woocommerce_bundle_get_base_price', array( $this, 'get_price' ), 5, 2 );

			add_filter( 'woocommerce_get_price_html', array( $this, 'get_price_html' ), 11, 2 );
			add_filter( 'woocommerce_get_variation_price_html', array( $this, 'get_price_html' ), 11, 2 );

			add_filter( 'yith_wcpb_ajax_get_bundle_total_price', array( $this, 'get_bundle_total_price_html' ) );
			add_filter( 'woocommerce_show_variation_price', array( $this, 'show_variation_price' ), 5, 3 );

			$hook = $this->get_hook_position( 'ywcrbp_position_user_txt' );
			add_action( 'woocommerce_single_product_summary', array( $this, 'print_custom_message' ), $hook );
			add_action( 'yith_wcqv_product_summary', array( $this, 'print_custom_message' ), $hook );
			add_action( 'wp_ajax_add_new_price_role', array( $this, 'add_new_price_role' ) );
			add_action( 'wp_ajax_add_new_variation_price_role', array( $this, 'add_new_variation_price_role' ) );



			if( defined('YITH_YWRAQ_PREMIUM' ) ){

				add_action( 'wp_loaded', array( $this, 'validate_add_to_cart_action' ), 15 );
			}

		}

		/**
		 * @return YITH_Role_Based_Prices_Product
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * include style and script in admin
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 */
		public function include_admin_product_style_script() {

			if ( ! isset( $_GET['post'] ) ) {
				global $post;
			} else {
				$post = $_GET['post'];
			}

			$right_post_type = ( isset( $post ) && get_post_type( $post ) == 'product' );
			$suffix          = ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.min' : '';

			if ( $right_post_type ) {

				wp_enqueue_script( 'ywcrbp_product_admin', YWCRBP_ASSETS_URL . 'js/ywcrbp_product_admin' . $suffix . '.js', array( 'jquery' ), YWCRBP_VERSION, true );
				wp_enqueue_style( 'ywcrbp_product_admin_style', YWCRBP_ASSETS_URL . 'css/ywcrbp_product_admin.css', array(), YWCRBP_VERSION );

				$params = array(
					'admin_url' => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
					'actions'   => array(
						'add_new_price_role'           => 'add_new_price_role',
						'add_new_variation_price_role' => 'add_new_variation_price_role'
					),
					'plugin'    => YWCRBP_SLUG
				);

				wp_localize_script( 'ywcrbp_product_admin', 'ywcrbp_prd', $params );
			}
		}

		/**
		 * include style and script in frontend
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 */
		public function include_frontend_product_style_script() {

			if ( ! isset( $_GET['post'] ) ) {
				global $post;
			} else {
				$post = $_GET['post'];
			}

			$right_post_type = ( isset( $post ) && get_post_type( $post ) == 'product' );

			if ( $right_post_type ) {

				wp_enqueue_style( 'ywcrbp_product_frontend_style', YWCRBP_ASSETS_URL . 'css/ywcrbp_product_frontend.css', array(), YWCRBP_VERSION );
			}
		}

		/**
		 * show product metaboxes
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 */
		public function show_product_price_rule() {

			wc_get_template( 'metaboxes/product-rules.php', array(), '', YWCRBP_TEMPLATE_PATH );
		}

		/** show product variation metaboxes
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 *
		 * @param $loop
		 * @param $variation_data
		 * @param $variation
		 */
		public function show_product_variation_price_rule( $loop, $variation_data, $variation ) {
			wc_get_template( 'metaboxes/product-variation-rules.php', array(
				'loop'           => $loop,
				'variation_data' => $variation_data,
				'variation'      => $variation
			), '', YWCRBP_TEMPLATE_PATH );

		}

		/**
		 * save product meta
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 *
		 * @param $product_id
		 * @param $product
		 */
		public function save_product_meta( $product_id, $product ) {

			if ( isset( $_REQUEST['type_price_rule'] ) ) {

				$product_rules = isset( $_REQUEST['_product_rules'] ) ? $_REQUEST['_product_rules'] : '';
				$how_apply     = isset( $_REQUEST['how_apply_product_rule'] ) ? $_REQUEST['how_apply_product_rule'] : 'only_this';

				$product = wc_get_product( $product_id );
				yit_save_prop( $product, 'how_apply_product_rule', $how_apply );
				yit_save_prop( $product, '_product_rules', $product_rules );


			}
		}


		/**
		 * save variation meta
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 *
		 * @param $variation_id
		 * @param $loop
		 */
		public function save_product_variation_meta( $variation_id, $loop ) {

			if ( isset( $_REQUEST['type_price_rule'] ) ) {

				$variation_rule = isset( $_REQUEST['_product_variable_rule'][ $loop ] ) ? $_REQUEST['_product_variable_rule'][ $loop ] : '';
				$how_apply      = isset( $_REQUEST['how_apply_product_rule'][ $loop ] ) ? $_REQUEST['how_apply_product_rule'][ $loop ] : 'only_this';

				$product = wc_get_product( $variation_id );
				yit_save_prop( $product, 'how_apply_product_rule', $how_apply );
				yit_save_prop( $product, '_product_rules', $variation_rule );

			}

			delete_site_transient( 'ywcrb_rolebased_prices' );
		}


		/**
		 * @author YITHEMES
		 * @since  1.0.0
		 * delete wc_var_prices for variable product
		 */
		public function single_product_summary() {

			global $post;

			if ( isset( $post ) && 'product' == $post->post_type ) {

				$product_id = $post->ID;
				$product    = wc_get_product( $product_id );

				if ( $product->is_type( 'variable' ) ) {

					delete_transient( 'wc_var_prices_' . $product_id );
				}

			}
		}

		/**
		 * if a product has user role set as non sale
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 *
		 * @param bool $is_on_sale
		 * @param WC_Product $product
		 *
		 * @return bool
		 */
		public function product_is_on_sale( $is_on_sale, $product ) {


			if( $product->is_type( 'variable' ) ){

			    $role_base_price = $this->get_variation_new_prices( $product );
			    $is_on_sale = empty( $role_base_price ) ? $is_on_sale  :false;
            }else {
				$role_base_price = $this->get_role_based_price( $product );
				$is_on_sale = ( $role_base_price == 'no_price' ) ? $is_on_sale : false;
			}
			return  $is_on_sale;
		}

		/**
		 * get a new price for user role
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 *
		 * @param string $price
		 * @param WC_Product $product
		 *
		 * @return mixed
		 */
		public function get_price( $price, $product ) {

		    if( !$product ){
		        return $price;
            }
			$return_original_price = apply_filters( 'yith_wcrbp_return_original_price', false, $price, $product );
			$is_custom_price       = $this->is_custom_price( $product );


			if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || $return_original_price || $is_custom_price ) {
				return $price;
			}

			if ( $price !== '' && ! is_null( $product ) && !$product->is_type('variable') ) {

				$role_price = $this->get_role_based_price( $product );

				if ( $role_price !== 'no_price' ) {

					$price = $role_price;

					$price = apply_filters( 'yith_wcrbp_get_role_based_price', $price, $product );
				}
			}

			return $price;
		}

		/**
		 * @param WC_Product $product
		 *
		 * @return bool
		 */
		public function is_custom_price( $product ) {

			$has_dynamic_price             = yit_get_prop( $product, 'has_dynamic_price' );
			$yith_wapo_adjust_price        = yit_get_prop( $product, 'yith_wapo_price' );
			$ywcp_composite_info           = yit_get_prop( $product, 'ywcp_composite_info' );
			$ywcpb_bundled_item_price_zero = yit_get_prop( $product, 'bundled_item_price_zero' );
			$is_gift_card                  = $product->is_type( 'gift-card' );

			return $has_dynamic_price || $yith_wapo_adjust_price || is_array( $ywcp_composite_info ) || $is_gift_card || $ywcpb_bundled_item_price_zero;
		}

		/**
		 * @param WC_Product $product
		 */
		public function get_role_based_price( $product ) {

			global $woocommerce_wpml, $sitepress;


			if( $product->is_type('variation')){
			    $product_id = is_callable( array( $product, 'get_id' ) ) ? $product->get_id(): $product->variation_id;
            }else {
				$product_id = yit_get_product_id( $product );
			}


			$role_based_price = yit_get_prop( $product, 'ywcrp_role_based_price' );

			$current_rule = $this->user_role['role'];

			if ( empty( $role_based_price ) || 'no_price' == $role_based_price ) {


			    $all_role_based_prices = get_site_transient( 'ywcrb_rolebased_prices' );

			    $all_role_based_prices = empty( $all_role_based_prices ) ? array() : $all_role_based_prices;
			    if( empty( $all_role_based_prices ) || (  !isset( $all_role_based_prices[$product_id][$current_rule] ) ) ) {


				    $global_rule = YITH_Role_Based_Type()->get_price_rule_by_user_role( $current_rule, $product_id );

				    $role_based_price = ywcrbp_calculate_product_price_role( $product, $global_rule, $current_rule );


				    $all_role_based_prices[$product_id][$current_rule] =  $role_based_price;
				    set_site_transient( 'ywcrb_rolebased_prices', $all_role_based_prices );
			    }else{
			        $role_based_price = $all_role_based_prices[$product_id][$current_rule];

                }
				yit_set_prop( $product, array( 'ywcrp_role_based_price' => $role_based_price ) );

			}
			return apply_filters( 'yith_ywrbp_price', $role_based_price, $product_id );

		}

		/**
		 * @author YITHEMES
		 * @since  1.0.0
		 *
		 * @param string $price
		 * @param WC_Product $product
		 *
		 * @return string
		 */
		public function get_price_html( $price, $product ) {

			$is_custom_price = $this->is_custom_price( $product );

			if ( $is_custom_price  ) {
				return $price;
			}


			$product_has_some_role_price = $this->has_price_rule( $product );


			if ( ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) ) {

				if ( $product_has_some_role_price ) {
					$price = sprintf( '%s <p class="ywcrbp_admin_product_has_rule">%s</p>', $price, __( 'There are some price rules for this product', 'yith-woocommerce-role-based-prices' ) );
				}
			} else {

				$product_type = $product->get_type();


				switch ( $product_type ) {

					case 'simple':
                    case 'ticket-event':
					case 'variation':
					case 'yith-composite':
						$price = $this->get_simple_price_html( $product );
						break;
					case 'grouped':
						$price = $this->get_grouped_price_html( $product );
						break;
					case 'variable':
						$price = $this->get_variable_price_html( $product );
						break;

				}

				if ( $product->is_type( 'yith_bundle' ) ) {
					$per_items_pricing = yit_get_prop( $product, '_yith_wcpb_per_item_pricing' );


					if ( $per_items_pricing !== 'yes' ) {
						$price = $this->get_simple_price_html( $product );
					}
				}

			}


			return apply_filters( 'ywcrbp_get_price_html', $price, $product );
		}


		public function get_bundle_total_price_html( $price ) {

			if ( isset( $_POST['bundle_id'] ) ) {

				/**
				 * @var WC_Product_Yith_Bundle $product
				 */
				$product = wc_get_product( $_POST['bundle_id'] );
				if ( ! $product->is_type( 'yith_bundle' ) ) {
					die();
				}
				$wpml_parent_id = $product->get_wpml_parent_id();
				if ( $wpml_parent_id != $_POST['bundle_id'] ) {
					$product = wc_get_product( $wpml_parent_id );
				}

				$show_regular_price = $this->user_role['show_regular_price'];

				$regular_price_html = '';
				$your_price_html    = '';
				$your_price         = $this->get_role_based_price( $product );
				$per_items_pricing  = yit_get_prop( $product, '_yith_wcpb_per_item_pricing' );


				if ( $your_price !== 'no_price' && $per_items_pricing === 'yes' ) {
					if ( $show_regular_price ) {

						if ( ! function_exists( 'YITH_WCPB_Role_Based_Compatibility' ) ) {
							include_once( YITH_WCPB_INCLUDES_PATH . '/compatibility/class.yith-wcpb-role-based-compatibility.php' );
						}

						YITH_WCPB_Role_Based_Compatibility()->remove_regular_price_and_variations_regular_price_actions();
						remove_filter( 'woocommerce_product_get_price', array( $this, 'get_price' ), 20 );
						remove_filter( 'woocommerce_product_variation_get_price', array( $this, 'get_price' ), 20 );
						add_filter( 'yith_wcrbp_return_original_price', '__return_true', 999 );
						$array_qty     = isset( $_POST['array_qty'] ) ? $_POST['array_qty'] : array();
						$array_opt     = isset( $_POST['array_opt'] ) ? $_POST['array_opt'] : array();
						$array_var     = isset( $_POST['array_var'] ) ? $_POST['array_var'] : array();
						$regular_price = $product->get_per_item_price_tot_with_params( $array_qty, $array_opt, $array_var, false, 'edit' );

						$regular_price_html = wc_price( yit_get_display_price( $product, $regular_price ) ) . $this->get_price_suffix( $product, $regular_price );
						$regular_price_txt  = get_option( 'ywcrbp_regular_price_txt' );

						$regular_price_html = sprintf( '<span class="ywcrbp_regular_price"><del>%s %s</del></span>', $regular_price_txt, $regular_price_html );

						add_filter( 'woocommerce_product_get_price', array( $this, 'get_price' ), 20, 2 );
						add_filter( 'woocommerce_product_variation_get_price', array( $this, 'get_price' ), 20, 2 );
						YITH_WCPB_Role_Based_Compatibility()->add_regular_price_and_variations_regular_price_actions();
					}

					$show_your_price = $this->user_role['show_your_price'];


					if ( $show_your_price ) {


						$your_price_txt  = get_option( 'ywcrbp_your_price_txt' );
						$your_price_html = sprintf( '<span class="ywcrbp_your_price">%s %s</span>', $your_price_txt, $price );


						echo $regular_price_html . $your_price_html;
					} else {
						echo '';
					}
				} else {
					echo $price;
				}
			}
		}

		/**
		 * @param WC_Product $product
		 * @param string $price
		 * @param int $qty
		 *
		 * @return mixed|null|void
		 */
		public function get_price_suffix( $product, $price = '', $qty = 1 ) {

			if ( $price == '' ) {
				$price = $product->get_price();
			}
			$how_show = isset( $this->user_role['how_show_price'] ) ? $this->user_role['how_show_price'] : get_option( 'woocommerce_tax_display_shop' );;

			$price_display_suffix = get_option( "ywcrbp_price_{$how_show}_suffix" );

			if ( $price_display_suffix ) {

				$price_display_suffix = ' <small class="woocommerce-price-suffix">' . $price_display_suffix . '</small>';

			} else {

				$price_display_suffix = $product->get_price_suffix();
			}

			return apply_filters( 'yith_role_based_prices_get_price_suffix', $price_display_suffix, $this );
		}

		/**
		 * get simple price html
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 *
		 * @param WC_Product $product_id
		 *
		 * @return string
		 */
		public function get_simple_price_html( $product ) {


			$regular_price      = apply_filters( 'yith_ywrbp_regular_price', yit_get_prop( $product, 'regular_price', true, 'edit' ), $product );
			$your_price         = $this->get_role_based_price( $product );
			$show_regular_price = $this->user_role['show_regular_price'];
			$show_on_sale_price = $this->user_role['show_on_sale_price'];
			$show_your_price    = $this->user_role['show_your_price'];

			remove_filter( 'woocommerce_product_is_on_sale', array( $this, 'product_is_on_sale' ), 20 );
			$is_on_sale = $product->is_on_sale();
			$price      = '';

			if ( $regular_price === '' ) {
				$price = '';
			} elseif ( ! $show_regular_price && ! $show_on_sale_price && ! $show_your_price ) {
				$price = '';
			} elseif ( $your_price === 'no_price' ) {

				if ( $show_regular_price ) {

					$price .= $this->get_regular_price_html( $product, false );
				}

				if ( $show_on_sale_price && $is_on_sale ) {

					$price .= $this->get_sale_price_html( $product, false );
				}

			} elseif ( $your_price === 0 ) {

				if ( $show_regular_price ) {

					$price .= $this->get_regular_price_html( $product );
				}
				if ( $show_on_sale_price ) {

					$price .= $this->get_sale_price_html( $product );
				}

				if ( $show_your_price ) {

					$price .= $this->get_your_price_html( $your_price, $product );
				}
			} else {
				if ( $show_regular_price ) {

					$price .= $this->get_regular_price_html( $product );
				}
				if ( $show_on_sale_price && $is_on_sale ) {

					$price .= $this->get_sale_price_html( $product );
				}

				if ( $show_your_price ) {

					$price .= $this->get_your_price_html( $your_price, $product );

					if ( $this->user_role['show_percentage'] && is_product() ) {

						$price .= $this->get_total_discount_markup_formatted( $product );
					}
				}
			}
			add_filter( 'woocommerce_product_is_on_sale', array( $this, 'product_is_on_sale' ), 20, 2 );
			return $price;
		}

		/**
		 * @author YITHEMES
		 * @since 1.0.0
		 *
		 * @param WC_Product_Variable $product
		 *
		 * @return string
		 */
		public function get_variable_price_html(   $product ){

			$price = '';
			$variation_prices         = $product->get_variation_prices( true );
			$variation_regular_prices = $variation_prices['regular_price'];
			$variation_sale_prices    = $variation_prices['sale_price'];

			$variation_your_prices = $this->get_variation_new_prices( $product );

			$min_regular_price = current( $variation_regular_prices );
			$max_regular_price = end( $variation_regular_prices );
			$min_sale_price = current( $variation_sale_prices );
			$max_sale_price = end( $variation_sale_prices );

			$min_your_price = floatval( current( $variation_your_prices ) );
			$max_your_price = floatval( end( $variation_your_prices ) );


			$your_price_txt    = get_option( 'ywcrbp_your_price_txt' );
			$regular_price_txt = get_option( 'ywcrbp_regular_price_txt' );
			$sale_price_txt    = get_option( 'ywcrbp_sale_price_txt' );

			$show_your_price    = $this->user_role['show_your_price'];
			$show_regular_price = $this->user_role['show_regular_price'];
			$show_on_sale_price = $this->user_role['show_on_sale_price'];


			$regular_price_html = '';
			$sale_price_html = '';
			$your_price_html = '';

			if( $show_regular_price ){

				if( $min_regular_price!== $max_regular_price ){
					$regular_price_html = ywcrbp_get_format_price_from_to( $product, $min_regular_price, $max_regular_price );
				}else {
					$regular_price_html = wc_price( $min_regular_price );
				}

				$regular_price_html .=  $this->get_price_suffix( $product );
				$regular_price_html =   apply_filters( 'ywcrbp_variable_regular_price_html', $regular_price_html, $product, $min_regular_price, $max_regular_price );
			}

			if( $show_on_sale_price && $product->is_on_sale() ){
				if( $min_sale_price!== $max_sale_price ){
					$sale_price_html = ywcrbp_get_format_price_from_to( $product, $min_sale_price, $max_sale_price );
				}else {
					$sale_price_html = wc_price( $min_sale_price );
				}

				$sale_price_html .=  $this->get_price_suffix( $product );
				$sale_price_html =   apply_filters( 'ywcrbp_variable_sale_price_html', $sale_price_html, $product, $min_sale_price, $max_sale_price );
			}

			if( $show_your_price && !empty( $variation_your_prices ) ){

				if( ( ( $min_your_price == $min_regular_price ) && ( $max_your_price == $max_regular_price ) ) ) {
					$regular_price_html = '';
				}

				if( ( ( $min_your_price == $min_sale_price ) && ( $max_your_price == $max_sale_price ) ) ) {
					$sale_price_html = '';
					if( $product->is_on_sale() ){
						$regular_price_html = '';
					}
				}

				if( $min_your_price !== $max_your_price ){
					$your_price_html = ywcrbp_get_format_price_from_to($product, $min_your_price, $max_your_price );
				}else{

					$your_price_html = wc_price( $min_your_price );
				}
				$your_price_html .= $this->get_price_suffix( $product );

				$your_price_html = apply_filters( 'ywcrbp_variable_your_price_html', $your_price_html, $product, $min_your_price, $max_your_price );
			}

			if ( ! empty( $regular_price_html ) ) {

				if ( empty( $sale_price_html ) && empty( $your_price_html ) ) {
					$regular_price_html = sprintf( '<span class="%s">%s %s</span>', 'ywcrbp_regular_price', $regular_price_txt, $regular_price_html );
				} else {
					$regular_price_html = sprintf( '<span class="%s"><del>%s %s</del></span>', 'ywcrbp_regular_price', $regular_price_txt, $regular_price_html );
				}
			}

			if ( ! empty( $sale_price_html ) ) {
				if ( empty( $your_price_html ) ) {
					$sale_price_html = sprintf( '<span class="%s">%s %s</span>', 'ywcrbp_sale_price', $sale_price_txt, $sale_price_html );
				} else {
					$sale_price_html = sprintf( '<span class="%s"><del>%s %s</del></span>', 'ywcrbp_sale_price', $sale_price_txt, $sale_price_html );
				}
			}
			if ( ! empty( $your_price_html ) ) {

				$your_price_html = sprintf( '<span class="%s">%s %s</span>', 'ywcrbp_your_price', $your_price_txt, $your_price_html );
			}

			return $regular_price_html.$sale_price_html.$your_price_html;
		}

		/**
		 * @param WC_Product $product
		 *
		 * @return string
		 */
		public function get_grouped_price_html( $product ) {

			$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
			$child_prices     = array();
			$price            = '';

			foreach ( $product->get_children() as $child_id ) {

				$single_prod_group = wc_get_product( $child_id );
				$price_child       = $this->get_role_based_price( $single_prod_group );
				$price_child       = ( 'no_price' === $price_child ) ? $single_prod_group->get_price('edit') : $price_child;
				$child_prices[]    = $price_child;
			}

			$child_prices     = array_unique( $child_prices );
			$get_price_method = 'yit_get_price_' . $tax_display_mode . 'uding_tax';

			if ( ! empty( $child_prices ) ) {
				$min_price = min( $child_prices );
				$max_price = max( $child_prices );
			} else {
				$min_price = '';
				$max_price = '';
			}


			if ( $min_price ) {
				if ( $min_price == $max_price ) {
					$display_price = wc_price( $get_price_method( $product, 1, $min_price ) );
				} else {
					$display_price = ywcrbp_get_format_price_from_to( $product, $min_price, $max_price );
				}

				$price = $display_price . $this->get_price_suffix( $product );
			}

			return $price;
		}


		public function get_variation_role_price( $price , $product, $min_or_max, $for_display ){

		        $prices = $this->get_variation_new_prices( $product );

		        if( 'min' == $min_or_max ){
		            $price = min( $prices );
                }else{
		            $price = max( $prices );
                }

                return $price;
        }
		/**
		 * @param WC_Product_Variable $product
		 *
		 * @return array
		 */
		public function get_variation_new_prices( $product ) {

			$new_prices = array();

			$variation_ids = version_compare( WC()->version, '2.7.0', '>=' ) ? $product->get_visible_children() : $product->get_children( true );

			foreach ( $variation_ids as $variation_id ) {

				$variation = wc_get_product( $variation_id );
				if ( $variation instanceof WC_Product_Variation ) {

					$new_price = $this->get_role_based_price( $variation );

					if ( 'no_price' !== $new_price ) {

						if ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) {
							$new_prices[ $variation_id ] = yit_get_price_including_tax( $product, 1, $new_price );
						} else {
							$new_prices[ $variation_id ] = yit_get_price_excluding_tax( $product, 1, $new_price );
						}

					}
				}
				asort( $new_prices );
			}

			return $new_prices;
		}


		/**
		 * @author YITHEMES
		 * @since  1.0.0
		 *
		 * @param bool $has_your_price
		 * @param WC_Product $product
		 *
		 * @return string
		 */
		public function get_regular_price_html( $product, $has_your_price = true ) {

			$regular_price      = apply_filters( 'yith_ywrbp_regular_price', yit_get_prop( $product, 'regular_price', true, 'edit' ), $product );
			$sale_price         = apply_filters( 'yith_ywcrbp_sale_price', yit_get_prop( $product, 'sale_price', true, 'edit' ), $product );
			$your_price         = $this->get_role_based_price( $product );
			$regular_html       = '';
			$regular_price_html = wc_price( yit_get_display_price( $product, $regular_price ) ) . $this->get_price_suffix( $product, $regular_price );
			$regular_price_txt  = get_option( 'ywcrbp_regular_price_txt' );

			if( $regular_price!==$your_price ) {
				if ( $has_your_price || ! empty( $sale_price ) && $regular_price !== $sale_price && $product->is_on_sale() ) {
					$regular_html = sprintf( '<span class="ywcrbp_regular_price"><del>%s %s</del></span>', $regular_price_txt, $regular_price_html );
				} else {
					$regular_html = sprintf( '<span class="ywcrbp_regular_price">%s %s</span>', $regular_price_txt, $regular_price_html );
				}
			}

			return apply_filters( 'ywcrbp_get_regular_price_html', $regular_html, $product, $has_your_price );
		}


		/**
		 * @author YITHEMES
		 * @since  1.0.0
		 *
		 * @param WC_Product $product
		 * @param bool $has_your_price
		 *
		 * @return string
		 */
		public function get_sale_price_html( $product, $has_your_price = true ) {

			$sale_price     = apply_filters( 'yith_ywcrbp_sale_price', yit_get_prop( $product, 'sale_price', true, 'edit' ), $product );
			$your_price         = $this->get_role_based_price( $product );
			$sale_html      = '';
			$sale_price_txt = get_option( 'ywcrbp_sale_price_txt' );
			if ( ! empty( $sale_price ) && $sale_price!= $your_price ) {
				$sale_price_html = wc_price( yit_get_display_price( $product, $sale_price ) ) . $this->get_price_suffix( $product, $sale_price );

				if ( $has_your_price ) {
					$sale_html = sprintf( '<span class="ywcrbp_sale_price"><del>%s %s</del></span>', $sale_price_txt, $sale_price_html );
				} else {
					$sale_html = sprintf( '<spn class="ywcrbp_sale_price">%s %s</spn>', $sale_price_txt, $sale_price_html );
				}
			}

			return apply_filters( 'ywcrbp_get_sale_price_html', $sale_html, $product, $has_your_price );
		}

		/**
		 * @author YITHEMES
		 * @since  1.0.0
		 *
		 * @param            $price
		 * @param WC_Product $product
		 *
		 * @return string
		 */
		public function get_your_price_html( $price, $product ) {

			$your_price_html = wc_price( yit_get_display_price( $product, $price ) ) . $this->get_price_suffix( $product, $price );
			$your_price_txt  = get_option( 'ywcrbp_your_price_txt' );
			if ( $price === 0 ) {
				$your_price_html = sprintf( '<span class="ywcrbp_your_price">%s <span class="amount">%s</span></span>', $your_price_txt, __( 'Free!', 'woocommerce' ) );
			} else {
				$your_price_html = sprintf( '<span class="ywcrbp_your_price">%s %s</span>', $your_price_txt, $your_price_html );
			}

			return apply_filters( 'ywcrbp_get_your_price_html', $your_price_html, $product );
		}

		/**
		 * @param WC_Product_Yith_Bundle $product
		 */
		public function get_bundle_price_html( $product, $price ) {


			$show_your_price = $this->user_role['show_regular_price'];

			if ( $show_your_price ) {

				return $price;
			}

			return '';


		}


		/**
		 * set user info
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 */
		public function init_user_info() {


			if ( ! is_user_logged_in() ) {

				$this->user_role['show_add_to_cart']   = $this->user_can_show_add_to_cart();
				$this->user_role['show_regular_price'] = $this->user_can_show_prices( 'guest', 'regular' );
				$this->user_role['show_on_sale_price'] = $this->user_can_show_prices( 'guest', 'on_sale' );
				$this->user_role['show_your_price']    = $this->user_can_show_prices( 'guest', 'your_price' );
				$this->user_role['role']               = 'guest';
				$this->user_role['how_show_price']     = $this->how_show_price();
				$this->user_role['show_percentage']    = $this->user_can_show_tot_discount();
			} else {

				$user_id   = get_current_user_id();
				$user      = get_user_by( 'id', $user_id );
				$user_role = apply_filters( 'yith_wcrbp_get_user_role', get_first_user_role( $user->roles ), $user_id );


				$this->user_role['show_add_to_cart']   = $this->user_can_show_add_to_cart( $user_role );
				$this->user_role['show_regular_price'] = $this->user_can_show_prices( $user_role, 'regular' );
				$this->user_role['show_on_sale_price'] = $this->user_can_show_prices( $user_role, 'on_sale' );
				$this->user_role['show_your_price']    = $this->user_can_show_prices( $user_role, 'your_price' );
				$this->user_role['role']               = $user_role;
				$this->user_role['how_show_price']     = $this->how_show_price( $user_role );
				$this->user_role['show_percentage']    = $this->user_can_show_tot_discount( $user_role );

			}

		}


		public function how_show_price( $user_role = 'guest' ) {

			$option = get_option( 'ywcrbp_show_prices_for_role' );

			return isset( $option[ $user_role ]['how_show_price'] ) ? 'incl' : 'excl';
		}

		/**
		 * check if user can show add to cart
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 *
		 * @param string $user_role
		 *
		 * @return bool
		 */
		public function user_can_show_add_to_cart( $user_role = 'guest' ) {

			$option = get_option( 'ywcrbp_show_prices_for_role' );

			return isset( $option[ $user_role ]['add_to_cart'] ) ? true : false;
		}

		/**
		 * check if user can show price
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 *
		 * @param string $user_role
		 * @param        $price_type
		 *
		 * @return bool
		 */
		public function user_can_show_prices( $user_role = 'guest', $price_type ) {

			$option = get_option( 'ywcrbp_show_prices_for_role' );

			return isset( $option[ $user_role ][ $price_type ] ) ? true : false;
		}

		/**
		 * check if user can show tot discount/markup
		 * @author YITHEMES
		 * @since 1.0.11
		 *
		 * @param string $user_role
		 *
		 * @return bool
		 */
		public function user_can_show_tot_discount( $user_role = 'guest' ) {

			$option = get_option( 'ywcrbp_show_prices_for_role' );

			return isset( $option[ $user_role ]['show_percentage'] ) ? true : false;
		}

		/**
		 * return price user role
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 *
		 * @param $product_id
		 *
		 * @return string
		 */
		public function get_new_price( $product_id ) {

			global $woocommerce_wpml, $sitepress;

			if ( isset( $sitepress ) ) {

				$product_id = apply_filters( 'translate_object_id', $product_id, get_post_type( $product_id ), false, $sitepress->get_default_language() );
			}
			$prices = get_post_meta( $product_id, '_product_prices', true );

			$current_rule = $this->user_role['role'];

			$new_price = isset( $prices[ $current_rule ] ) ? $prices[ $current_rule ] : 'no_price';

			/* if ( 'no_price' !== $new_price && isset( $woocommerce_wpml ) ) {
				 $new_price = apply_filters( 'wcml_raw_price_amount', $new_price );
			 }*/

			return apply_filters( 'yith_ywrbp_price', $new_price, $product_id );
		}

		/**
		 * @param WC_Product $product
		 *
		 * @return bool
		 */
		public function has_price_rule( $product ) {

			$product_id = yit_get_base_product_id( $product );

			$product_rule = get_post_meta( $product_id, '_product_rules', true );

			if ( ! empty( $product_rule ) ) {
				return true;
			} else {

				$global_rule   = YITH_Role_Based_Type()->get_global_price_rule( true, $product_id );
				$filtered_rule = get_global_rule_for_product( $product_id, $product, $global_rule, '' );

				if ( ! empty( $filtered_rule ) ) {
					return true;
				}

				return false;
			}

		}




		/**
		 * syncronize variable product prices
		 *
		 * @author Salvatore Strano
		 * @since  1.0.0
		 *
		 * @param WC_Product_Variable $product
		 *
		 */
		public function variable_product_sync( $product ) {

		    $children = $product->get_visible_children();

		    delete_post_meta( $product->get_id(), '_price' );

		    foreach( $children as $child ){
		        $variation = wc_get_product( $child );
				$child_price = $this->get_role_based_price( $variation );

				if( 'no_price' !== $child_price ){
				    add_post_meta( $product->get_id(), '_price', $child_price );
                }else{
				    add_post_meta( $product->get_id(), '_price', $product->get_price('edit' ) );
                }
			}

		}

		/**
		 * set regular price and price for product variation
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 *
		 * @param $price
		 * @param $variation
		 * @param $product
		 *
		 * @return float|int|mixed|null
		 */
		public function variation_prices_price( $price, $variation, $product ) {


			if ( $price !== '' ) {
				$new_price = $this->get_role_based_price( $variation );

				if ( 'no_price' === $new_price ) {
					return $price;
				} else {
					return $new_price;
				}
			}

			return $price;

		}

		/**
		 * show variation price in frontend
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 *
		 * @param $show
		 * @param $product
		 * @param $variation
		 *
		 * @return string
		 */
		public function show_variation_price( $show, $product, $variation ) {

		    return true;
		}

		/**
		 * add new price role in admin
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 */
		public function add_new_price_role() {

			if ( isset( $_REQUEST['ywcrbp_plugin'] ) && YWCRBP_SLUG === $_REQUEST['ywcrbp_plugin'] ) {

				if ( isset( $_REQUEST['ywcrbp_index'] ) && isset( $_REQUEST['ywcrbp_type'] ) ) {

					$index = $_REQUEST['ywcrbp_index'];
					$type  = $_REQUEST['ywcrbp_type'];
					$args  = array(
						'index' => $index,
						'rule'  => array( 'rule_type' => $type )
					);

					$args['args'] = $args;

					ob_start();
					wc_get_template( 'metaboxes/view/product-single-rule.php', $args, '', YWCRBP_TEMPLATE_PATH );
					$template = ob_get_contents();
					ob_end_clean();

					wp_send_json( array( 'result' => $template ) );
				}
			}
		}

		/**
		 * add new price rule in product variation
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 */
		public function add_new_variation_price_role() {
			if ( isset( $_REQUEST['ywcrbp_plugin'] ) && YWCRBP_SLUG === $_REQUEST['ywcrbp_plugin'] ) {

				if ( isset( $_REQUEST['ywcrbp_index'] ) && isset( $_REQUEST['ywcrbp_type'] ) && isset( $_REQUEST['ywcrbp_loop'] ) ) {

					$index = $_REQUEST['ywcrbp_index'];
					$type  = $_REQUEST['ywcrbp_type'];
					$loop  = $_REQUEST['ywcrbp_loop'];
					$args  = array(
						'index' => $index,
						'loop'  => $loop,
						'rule'  => array( 'rule_type' => $type )
					);

					$args['args'] = $args;

					ob_start();
					wc_get_template( 'metaboxes/view/product-variation-single-rule.php', $args, '', YWCRBP_TEMPLATE_PATH );
					$template = ob_get_contents();
					ob_end_clean();

					wp_send_json( array( 'result' => $template ) );
				}
			}
		}


		public function show_price_incl_excl_tax( $value, $option ) {

			if ( ! isset( $this->user_role ) ) {
				$this->init_user_info();
			}

			if ( isset( $this->user_role['how_show_price'] ) && ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) ) {
				$value = maybe_unserialize( $this->user_role['how_show_price'] );
			}


			return $value;
		}

		/**
		 * if the add to cart is hide, the products are unpurchasable
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 *
		 * @param $purchasable
		 * @param $product
		 *
		 * @return bool
		 */
		public function is_purchasable( $purchasable, $product ) {

			if ( ! $this->user_role['show_add_to_cart'] && ! defined( 'YITH_YWRAQ_PREMIUM' ) ) {
				return false;
			}

			return $purchasable;
		}

		/**
		 * remove add to cart in loop and in single product
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 */
		public function remove_add_to_cart() {

			if ( ! $this->user_role['show_add_to_cart'] ) {

				$priority = has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart' );

				if ( false !== $priority ) {
					remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', $priority );
				}

				$priority = has_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );

				if ( false !== $priority ) {
					remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', $priority );
					add_filter( 'woocommerce_loop_add_to_cart_link', '__return_empty_string', 10 );
				}
			}
		}

		public function remove_add_to_cart_with_request_a_quote() {

			if ( ! $this->user_role['show_add_to_cart'] ) {

				global $product;
				if ( isset( $product ) && $product->is_type('variable' ) ) {

					$hide_quantity = defined( 'YITH_YWRAQ_PREMIUM' ) ? '' : "$('.single_variation_wrap .variations_button .quantity' ).hide();";
					$inline_js
					               = "
                        $( '.single_variation_wrap .variations_button button' ).hide();" .
					                 $hide_quantity .
					                 "$( document).on( 'woocommerce_variation_has_changed', function() {
                         $( '.single_variation_wrap .variations_button button' ).hide();"
					                 . $hide_quantity .
					                 "});";

					wc_enqueue_js( $inline_js );

				} else {

					$inline_js = "$( '.cart button.single_add_to_cart_button' ).hide();";

					wc_enqueue_js( $inline_js );

				}
			}
		}

		/**
		 * @param $link
		 * @param WC_Product $product
		 *
		 * @return string
		 */
		public function hide_add_to_cart_loop( $link, $product ) {

			if ( ! $this->user_role['show_add_to_cart'] && !$product->is_type('variable' ) ) {
				return '';
			}

			return $link;
		}


		/**
		 * return priority hook
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 *
		 * @param $option_name
		 *
		 * @return int
		 */
		private function get_hook_position( $option_name ) {

			$woocommerce_hook = get_option( $option_name );

			switch ( $woocommerce_hook ) {

				case 'template_single_title':
					return 4;
					break;
				case 'template_single_price':
					return 9;
					break;
				case 'template_single_excerpt':
					return 19;
					break;
				case 'template_single_add_to_cart':
					return 29;
					break;
				case 'template_single_meta':
					return 39;
					break;
				case 'template_single_sharing':
					return 49;
					break;
			}
		}

		/**
		 * print custom message
		 *
		 * @author YITHEMES
		 * @since  1.0.0
		 */
		public function print_custom_message() {
			if ( ( ! $this->user_role['show_regular_price'] && ! $this->user_role['show_on_sale_price'] && ! $this->user_role['show_your_price'] ) ) {

				$custom_message = '';
				$class_message  = apply_filters( 'ywcrbp_add_custom_message_class', 'ywcrbp_custom_message' );
				$custom_message =   apply_filters( 'ywcrbp_get_user_message', get_option( 'ywcrbp_message_user' ) );
				$color_message  = get_option( 'ywcrbp_message_color_user' );
				$message        = sprintf( '<p class="%s">%s</p>', $class_message, $custom_message );
				?>
                <style type="text/css">
                    p.<?php echo $class_message;?> {
                        color: <?php echo $color_message;?>;
                    }
                </style>
				<?php echo $message;

			}
		}

		/**
		 * @param WC_Product $product
		 *
		 * @return float
		 */
		public function calculate_total_discount_markup( $product ) {

			$role_price = $this->get_role_based_price( $product );

			$how_price  = get_option( 'ywcrbp_apply_rule', 'regular' );

			/* if( $product->sale_price > 0 && 'on_sale' == $how_price ){
				 $product_price = $product->sale_price;
			 }else {

				 $product_price = $product->regular_price;
			 }
 */
			$regular_price = apply_filters( 'yith_ywrbp_regular_price',yit_get_prop( $product, 'regular_price' ), $product );

			$percentage = 1-( $role_price / $regular_price );

			return $percentage;
		}

		/**
		 * @param WC_Product $product
		 *
		 * @return string
		 */
		public function get_total_discount_markup_formatted( $product ) {

			$discount = $this->calculate_total_discount_markup( $product );

			if ( $discount > 0 ) {

				$discount_formatted = sprintf( '%s', round( $discount * 100, 2 ) . '%' );
				$discount_class     = 'ywcrpb_discount';
				$discount_text      = get_option( 'ywcrbp_total_discount_mess' );
				$discount_text      = str_replace( '{ywcrbp_total_discount}', $discount_formatted, $discount_text );
				$filter_id          = 'discount';

			} else {

				$discount_formatted = sprintf( '%s', round( abs( $discount * 100 ), 2 ) . '%' );
				$discount_class     = 'ywcrpb_markup';
				$discount_text      = get_option( 'ywcrbp_total_markup_mess' );
				$discount_text      = str_replace( '{ywcrbp_total_markup}', $discount_formatted, $discount_text );
				$filter_id          = 'markup';
			}

			$discount_html = sprintf( '<span class="%s">%s</span>', $discount_class, $discount_text );

			return apply_filters( 'ywcrbp_get_total_' . $filter_id . '_html', $discount_html, $discount, $product );
		}

		/**
		 * check if the add to cart query string is valid
         * @author Salvatore Strano
		 */
		public function validate_add_to_cart_action() {

			if( !empty( $_GET['add-to-cart'] ) ){

				$this->init_user_info();

				if( !$this->user_role['show_add_to_cart'] ){
					$url = remove_query_arg( array('add-to-cart','variation_id' ) );
					wc_add_notice( __( 'Sorry, this product cannot be purchased.', 'woocommerce' ), 'error' );

					wp_safe_redirect( $url );
					exit;
				}
			}
		}


	}
}
/**
 * @author YITHEMES
 * @since  1.0.0
 * @return YITH_Role_Based_Prices_Product
 */
function YITH_Role_Based_Prices_Product() {

	return YITH_Role_Based_Prices_Product::get_instance();
}