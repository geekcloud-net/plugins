<?php
/**
 * @package WPSEO_Local\Admin\
 * @since   4.1
 * @ToDo    CHECK THE @SINCE VERSION NUMBER!!!!!!!!
 */

if ( ! defined( 'WPSEO_LOCAL_WOOCOMMERCE_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'WPSEO_Local_Admin_Woocommerce_Settings' ) ) {

	/**
	 * WPSEO_Local_Admin_API_Settings class.
	 *
	 * Build the WPSEO Local admin form.
	 *
	 * @since   4.1
	 */
	class WPSEO_Local_Admin_Woocommerce_Settings {

		/**
		 * @var string Holds the slug for this settings tab.
		 */
		private $slug = 'woocommerce';

		/**
		 * @var mixed Holds WPSEO Local Core instance.
		 */
		private $wpseo_local_core;

		/**
		 * @var mixed wpseo_local options.
		 */
		private $options;

		/**
		 * WPSEO_Local_Admin_API_Settings constructor.
		 */
		public function __construct() {
			add_filter( 'wpseo_local_admin_tabs', array( $this, 'create_tab' ), 99 );

			add_action( 'wpseo_local_admin_' . $this->slug . '_content', array( $this, 'tab_content' ), 10 );
		}

		/**
		 * @param array $tabs Array holding the tabs.
		 *
		 * @return mixed
		 */
		public function create_tab( $tabs ) {
			/* translators: %1$s expands to 'WooCommerce'. */
			$tabs[ $this->slug ] = sprintf( __( '%1$s settings', 'yoast-local-seo-woocommerce' ), 'WooCommerce' );

			return $tabs;
		}

		/**
		 * Create tab content for API Settings.
		 */
		public function tab_content() {
			/* translators: %1$s expands to 'Local SEO for WooCommerce'. */
			echo '<h2>' . sprintf( __( '%1$s  settings', 'yoast-local-seo-woocommerce' ), 'Local SEO for WooCommerce' ) . '</h2>';
			echo '<div>';
			/* translators: %1$s expands to '<a>' and . %2$s expands to '</a>' */
			echo    sprintf(
						__( '%1$sClick here%2$s for the specific WooCommerce settings', 'yoast-local-seo-woocommerce' ),
						'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=yoast_wcseo_local_pickup' ) . '">',
						'</a>'
					);
			echo '</div>';
		}
	}
}
