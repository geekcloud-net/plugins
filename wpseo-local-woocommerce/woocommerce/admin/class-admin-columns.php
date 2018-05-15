<?php

if ( ! class_exists( 'Yoast_WCSEO_Local_Admin_Columns' ) ) {

	class Yoast_WCSEO_Local_Admin_Columns {

		private $_settings = null;

		public function __construct() {

			$this->_settings = get_option( 'woocommerce_yoast_wcseo_local_pickup_settings' );

			// Only proceed if the Shipping method is enabled
			if ( isset( $this->_settings['enabled'] ) && ( $this->_settings['enabled'] == 'yes' ) ) {
				$this->init();
			}

		}

		public function init() {

			// Filters
			add_filter( 'manage_wpseo_locations_posts_columns', array( $this, 'columns_head' ) );

			// Actions
			add_action( 'manage_wpseo_locations_posts_custom_column', array( $this, 'columns_content' ), 10, 2 );

		}

		public function columns_head( $defaults ) {

			// Add our custom column head
			$defaults['local_pickup_allowed'] = __( 'Local Pickup allowed?', 'yoast-local-seo-woocommerce' );

			return $defaults;

		}

		public function columns_content( $column_name, $post_ID ) {

			// Create custom column content
			if ( $column_name == 'local_pickup_allowed' ) {

				//First we check if this Location has been enabled via Location Specific settings
				if ( isset( $this->_settings['location_specific'][ $post_ID ]['allowed'] ) && ( $this->_settings['location_specific'][ $post_ID ]['allowed'] == 'yes' ) ) {
					_e( 'Yes', 'yoast-local-seo-woocommerce' );
					return;
				}

				//First we check if this Location has been enabled via Location Specific settings
				if ( isset( $this->_settings['location_specific'][ $post_ID ] ) && ( ! isset( $this->_settings['location_specific'][ $post_ID ]['allowed'] ) ) ) {
					_e( 'No', 'yoast-local-seo-woocommerce' );
					return;
				}

				// Otherwise check for an allowed category
				$terms = get_the_terms( $post_ID, 'wpseo_locations_category' );
				if ( $terms && ! is_wp_error( $terms ) ) {

					foreach ( $terms as $term ) {

						if ( isset( $this->_settings['category_specific'][ $term->term_id ]['allowed'] ) && ( $this->_settings['category_specific'][ $term->term_id ]['allowed'] == 'yes' ) ) {
							_e( 'Yes', 'yoast-local-seo-woocommerce' );
							return;
						}

					}

				}

				// echo a negative if nothing has been found
				_e( 'No', 'yoast-local-seo-woocommerce' );

			}
		}
	}

}

new Yoast_WCSEO_Local_Admin_Columns();