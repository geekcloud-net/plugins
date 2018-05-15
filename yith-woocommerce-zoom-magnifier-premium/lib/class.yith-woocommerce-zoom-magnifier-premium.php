<?php
/**
 * Main class
 *
 * @author  Your Inspiration Themes
 * @package YITH WooCommerce Zoom Magnifier
 * @version 1.1.2
 */

if ( ! defined( 'YITH_WCMG' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WooCommerce_Zoom_Magnifier_Premium' ) ) {
	/**
	 * YITH WooCommerce Zoom Magnifier Premium
	 *
	 * @since 1.0.0
	 */
	class YITH_WooCommerce_Zoom_Magnifier_Premium extends YITH_WooCommerce_Zoom_Magnifier {

		/**
		 * Constructor
		 *
		 * @return mixed|YITH_WCMG_Admin|YITH_WCMG_Frontend
		 * @since 1.0.0
		 */
		public function __construct() {
			// actions
			add_action( 'init', array( $this, 'init' ) );

			if ( is_admin() && ( ! isset( $_REQUEST['action'] ) || ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] != 'yith_load_product_quick_view' ) ) ) {

				$this->obj = new YITH_WCMG_Admin();
			} else {

				/** Stop the plugin on mobile devices */
				if ( ( 'yes' != get_option( 'yith_wcmg_enable_mobile' ) ) && wp_is_mobile() ) {

					return;
				}

				$this->obj = new YITH_WCMG_Frontend_Premium();
			}

			$this->set_plugin_options();

			add_action( 'ywzm_products_exclusion', array( $this, 'show_products_exclusion_table' ) );

			add_action( 'woocommerce_admin_field_ywzm_category_exclusion', array(
				$this,
				'show_product_category_exclusion_table',
			) );

			return $this->obj;
		}


		public function show_product_category_exclusion_table( $args = array() ) {
			if ( ! empty( $args ) ) {
				$args['value'] = ( get_option( $args['id'] ) ) ? get_option( $args['id'] ) : $args['default'];
				extract( $args );

				$exclusion_list = get_option( 'ywzm_category_exclusion' );

				?>
				<tr valign="top">
					<th scope="row" class="image_upload">
						<label for="<?php echo $id ?>"><?php echo $name ?></label>
					</th>
					<td class="forminp forminp-color plugin-option">
						<div class="categorydiv">
							<div class="tabs-panel">
								<ul id="product_catchecklist" data-wp-lists="list:product_cat"
								    class="categorychecklist form-no-clear">
									<input value="-1" type="hidden" name="ywzm_category_exclusion[]">
									<?php


									/** Check the WP version for calling get_terms in the right way
									 *
									 * Prior to 4.5.0, the first parameter of `get_terms()` was a taxonomy or list of taxonomies:
									 *
									 *     $terms = get_terms( 'post_tag', array(
									 *         'hide_empty' => false,
									 *     ) );
									 *
									 * Since 4.5.0, taxonomies should be passed via the 'taxonomy' argument in the `$args` array:
									 *
									 *     $terms = get_terms( array(
									 *         'taxonomy' => 'post_tag',
									 *         'hide_empty' => false,
									 *     ) ); */
									$terms = $this->wp_prior_4_5
										? get_terms( 'product_cat', array(
											'hide_empty' => false,
										) )
										: get_terms( array(
											'taxonomy'   => 'product_cat',
											'hide_empty' => false,
										) );

									if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
										foreach ( $terms as $term ) {

											/* Retrieve the correct term id to search for*/
											$term_id        = $this->wc_prior_2_6 ? $term->woocommerce_term_id : $term->term_id;
											$checked_status = is_array( $exclusion_list ) && in_array( $term_id, $exclusion_list ) ? 'checked = checked' : '';

											echo '<li><label class="selectit"><input value="' . $term_id . '" type="checkbox" ' . $checked_status . ' name="ywzm_category_exclusion[]" id="in-product_cat-' . $term_id . '">' . $term->name . '</label></li>';
										}
									}

									?>
								</ul>
							</div>
						</div>
					</td>
				</tr>
				<?php
			}
		}

		public function show_products_exclusion_table() {


			YWZM_Products_Exclusion::output();
		}

		public function set_plugin_options() {
			add_filter( 'yith_ywzm_general_settings', array( $this, 'add_product_category_exclusion_list' ) );
			add_filter( 'yith_ywzm_magnifier_settings', array( $this, 'set_zoom_box_options' ) );
		}

		public function add_product_category_exclusion_list( $args ) {
			$new_item = array(
				'id'   => 'ywzm_category_exclusion',
				'type' => 'ywzm_category_exclusion',
				'name' => __( 'Exclude product categories', 'yith-woocommerce-zoom-magnifier' ),
			);

			$args = array_slice( $args, 0, count( $args ) - 1, true ) +
			        array( 'category_exclusion' => $new_item ) +
			        array_slice( $args, 3, count( $args ) - 1, true );

			return $args;
		}

		public function set_zoom_box_options( $args ) {
			if ( isset( $args['zoom_box_position'] ) ) {
				$box_position = &$args['zoom_box_position'];

				$box_position['options'] = array(
					'top'    => __( 'Top', 'yith-woocommerce-zoom-magnifier' ),
					'right'  => __( 'Right', 'yith-woocommerce-zoom-magnifier' ),
					'bottom' => __( 'Bottom', 'yith-woocommerce-zoom-magnifier' ),
					'left'   => __( 'Left', 'yith-woocommerce-zoom-magnifier' ),
					'inside' => __( 'Inside', 'yith-woocommerce-zoom-magnifier' ),
				);

			}

			return $args;
		}

		/**
		 * Check if current product have to be ignored by the plugin.
		 * We want to be alerted only if we are working on a valid product on which a product rule or catefory rule is active.
		 *
		 * @return bool product should be ignored
		 */
		public function is_product_excluded() {
			global $post;

			//  if current post is not a product, there is nothing to report.
			if ( ! is_product() ) {
				return false;
			}

			//  Check single product exclusion rule
			$is_excluded = yit_get_prop( wc_get_product($post->ID), '_ywzm_exclude', true );

			if ( 'yes' != $is_excluded ) {
                $is_excluded = $this->is_product_category_excluded();
			}

			return $is_excluded;
		}

		/**
		 * Check if current product is associated with a product category excluded by plugin option
		 */
		public function is_product_category_excluded() {
			global $post;

			//  if current post is not a product, there is nothing to report.
			if ( ! is_product() ) {
				return false;
			}

			$exclusion_list = get_option( 'ywzm_category_exclusion' );
			if ( ! $exclusion_list ) {
				return false;
			}

			$terms = get_the_terms( $post->ID, 'product_cat' );

			if ( $terms && ! is_wp_error( $terms ) ) {

				foreach ( $terms as $term ) {

					if ( in_array( $term->term_id, $exclusion_list ) ) {

						return true;
					}
				}
			}

			return false;
		}
	}
}