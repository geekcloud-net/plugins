<?php
/**
 * @package WPSEO_LOCAL\Import_Export_Admin
 */

if ( ! defined( 'WPSEO_LOCAL_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'WPSEO_Local_Import_Export_Admin' ) ) {

	/**
	 * Class that holds the functionality for the WPSEO Local Import and Export functions
	 *
	 * @since 3.9
	 */
	class WPSEO_Local_Import_Export_Admin {


		/**
		 * WPSEO_Local_Import_Export_Admin constructor.
		 */
		public function __construct() {
			if ( wpseo_has_multiple_locations() ) {
				if ( version_compare( WPSEO_VERSION, '2', '>=' ) ) {
					add_action( 'wpseo_import_tab_header', array( $this, 'create_import_tab_header' ) );
					add_action( 'wpseo_import_tab_content', array( $this, 'create_import_tab_content_wrapper' ) );
				}
				else {
					add_action( 'wpseo_import', array( $this, 'import_panel' ), 10, 1 );
				}
			}
		}

		/**
		 * Creates new import tab
		 *
		 * @since 1.3.5
		 */
		function create_import_tab_header() {
			echo '<a class="nav-tab" id="local-seo-tab" href="#top#local-seo">Local SEO</a>';
		}

		/**
		 * Creates content wrapper for Local SEO import tab
		 *
		 * @since 1.3.5
		 */
		function create_import_tab_content_wrapper() {
			echo '<div id="local-seo" class="wpseotab">';
			do_action( 'wpseo_import_tab_content_inner' );
			echo '</div>';
		}
	}
}
