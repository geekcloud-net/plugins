<?php
/**
 * @package WPSEO_Local\Admin\
 * @since   4.1
 * @ToDo    CHECK THE @SINCE VERSION NUMBER!!!!!!!!
 */

if ( ! defined( 'WPSEO_LOCAL_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'WPSEO_Local_Admin_Page' ) ) {

	/**
	 * WPSEO_Local_Admin_Page class.
	 *
	 * Build the WPSEO Local admin form.
	 *
	 * @since   4.0
	 */
	class WPSEO_Local_Admin_Page {

		/**
		 * @var Array containing the tabs for the WPSEO Local Admin Page
		 */
		public static $tabs;

		/**
		 * @var Array containing help center videos
		 */
		public static $videos;

		/**
		 * WPSEO_Local_Admin_Page constructor.
		 */
		function __construct() {
			add_action( 'admin_init', array( $this, 'set_tabs' ) );
			add_action( 'admin_init', array( $this, 'set_videos' ) );
		}

		/**
		 * Apply filters on array holding the tabs.
		 */
		public function set_tabs() {
			self::$tabs = apply_filters( 'wpseo_local_admin_tabs', self::$tabs );
		}

		/**
		 * Apply filters on array holding the help center videos.
		 */
		public function set_videos() {
			self::$videos = apply_filters( 'wpseo_local_admin_help_center_video', self::$videos );
		}

		/**
		 * Build the WPSEO Local Admin page.
		 */
		public static function build_page() {
			// Admin header.
			WPSEO_Local_Admin_Wrappers::admin_header( true, 'yoast_wpseo_local_options', 'wpseo_local' );

			// Adding tabs.
			self::create_tabs();
			self::tab_content();

			// Admin footer.
			WPSEO_Local_Admin_Wrappers::admin_footer();
		}

		/**
		 * Function to create tabs for general and API settings.
		 */
		private static function create_tabs() {
			// @ToDo Remove this part if the Local SEO WooCommerce plugin had this filter added.
			// @codingStandardsIgnoreLine
			if ( defined( 'WPSEO_LOCAL_WOOCOMMERCE_VERSION' ) ) {
				/* translators: %1$s expands to WooCommerce. */
			}
			echo '<h2 class="nav-tab-wrapper" id="wpseo-tabs">';
			foreach ( self::$tabs as $slug => $title ) {
				echo '<a class="nav-tab" id="' . $slug . '-tab" href="#top#' . $slug . '">' . $title . '</a>';
			}
			echo '</h2>';
		}

		/**
		 * Add content to the admin tabs.
		 */
		private static function tab_content() {
			if ( class_exists( 'WPSEO_Help_Center' ) && version_compare( WPSEO_VERSION, '5.6', '>=' ) ) {
				$videos = apply_filters( 'wpseo_local_help_center_videos', self::$videos );

				$tabs = new WPSEO_Option_Tabs( '', '' );
				foreach ( self::$tabs as $slug => $title ) {
					$tab = new WPSEO_Option_Tab( $slug, $title, array( 'video_url' => isset( $videos[ $slug ] ) ? $videos[ $slug ] : '' ) );
					$tabs->add_tab( $tab );
				}

				$help_center = new WPSEO_Help_Center( '', $tabs, true );
				$help_center->localize_data();
				$help_center->mount();
			}

			foreach ( self::$tabs as $slug => $title ) {
				self::section_before( $slug, null, 'wpseotab ' . ( $slug === current( array_keys( self::$tabs ) ) ? 'active' : '' ) );
				self::help_center( $slug, $title );
				self::section_before( 'local-' . $slug, null, 'yoastbox' );
				do_action( 'wpseo_local_admin_' . $slug . '_before_title', $slug );
				echo '<h2>' . esc_attr( WPSEO_Local_Admin_Page::$tabs[ $slug ] ) . '</h2>';
				do_action( 'wpseo_local_admin_' . $slug . '_content', $slug );
				self::section_after(); // End webseo tab section.
				self::section_after(); // End yoastbox.
			}
		}

		/**
		 * Show help center on WPSEO Local Admin tabs.
		 *
		 * @param string $slug  Slug of the tab.
		 * @param string $title Title of the tab.
		 */
		private static function help_center( $slug, $title ) {
			if ( class_exists( 'WPSEO_Help_Center' ) && version_compare( WPSEO_VERSION, '5.6', '<' ) ) {
				$videos = apply_filters( 'wpseo_local_help_center_videos', self::$videos );

				$tab         = new WPSEO_Option_Tab( $slug, $title, array( 'video_url' => isset( $videos[ $slug ] ) ? $videos[ $slug ] : '' ) );
				$help_center = new WPSEO_Help_Center( $slug, $tab );
				$help_center->output_help_center();
			}
		}

		/**
		 * Use this function to create sections between settings.
		 *
		 * @param string $id    ID of the section.
		 * @param string $style Styling for the section.
		 * @param string $class Class names for the section.
		 */
		public static function section_before( $id = '', $style = '', $class = '' ) {
			echo '<div' . ( isset( $id ) ? ' id="' . $id . '"' : '' ) . '' . ( ! empty( $style ) ? ' style="' . $style . '"' : '' ) . '' . ( ! empty( $class ) ? ' class="' . $class . '"' : '' ) . '>';
		}

		/**
		 * Use this function to close a section.
		 */
		public static function section_after() {
			echo '</div>';
		}
	}
}
