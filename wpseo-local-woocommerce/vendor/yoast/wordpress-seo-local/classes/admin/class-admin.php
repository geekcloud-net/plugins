<?php
/**
 * @package WPSEO_LOCAL\Admin
 */

if ( ! defined( 'WPSEO_LOCAL_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'WPSEO_Local_Admin' ) ) {

	/**
	 * Class that holds most of the admin functionality for WP SEO Local.
	 */
	class WPSEO_Local_Admin {

		/**
		 * @var string    Group name for the options.
		 */
		var $group_name = 'yoast_wpseo_local_options';

		/**
		 * @var string    Option name.
		 */
		var $option_name = 'wpseo_local';

		/**
		 * Class constructor
		 */
		public function __construct() {

			add_action( 'admin_init', array( $this, 'options_init' ) );

			// Adds page to WP SEO menu.
			add_action( 'wpseo_submenu_pages', array( $this, 'register_settings_page' ), 20 );

			// Register local into admin_pages.
			$this->register_wpseo();

			// Add styles and scripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'config_page_scripts' ) );
			add_action( 'admin_print_styles', array( $this, 'config_page_styles' ) );
			add_action( 'admin_footer', array( $this, 'config_page_footer' ) );

			// Flush the rewrite rules after options change.
			add_action( 'update_option_wpseo_local', array( $this, 'update_multiple_locations' ), 10, 2 );
			add_action( 'admin_init', array( $this, 'flush_rewrite_rules' ) );

			// Only initialize the Helpscout Beacon when the License Manager is present.
			if ( class_exists( 'Yoast_Plugin_License_Manager' ) ) {
				add_action( 'admin_init', array( $this, 'init_beacon' ) );
			}

			// Only register the yoast i18n when the page is a Yoast SEO page.
			if ( $this->is_local_seo_page( filter_input( INPUT_GET, 'page' ) ) ) {
				$this->register_i18n_promo_class();
			}

			add_action( 'admin_init', array( $this, 'maps_api_browser_key_notification' ) );
			add_action( 'admin_init', array( $this, 'maps_api_server_key_notification' ) );

			add_action( 'admin_init', array( $this, 'maybe_set_api_keys_from_constant' ) );
		}

		/**
		 * Initializes the HelpScout beacon
		 */
		public function init_beacon() {
			$query_var = ( $page = filter_input( INPUT_GET, 'page' ) ) ? $page : '';
			// Only add the helpscout beacon on Yoast SEO pages.
			if ( $query_var === 'wpseo_local' ) {
				$beacon = yoast_get_helpscout_beacon( $query_var, Yoast_HelpScout_Beacon::BEACON_TYPE_NO_SEARCH );
				$beacon->add_setting( new WPSEO_Local_Beacon_Settings() );
				$beacon->register_hooks();
			}
		}

		/**
		 * Registers the wpseo_local setting for Settings API
		 *
		 * @since 1.0
		 */
		function options_init() {
			register_setting( 'yoast_wpseo_local_options', 'wpseo_local' );
		}

		/**
		 * Adds local page to admin_page variable of wpseo
		 */
		function register_wpseo() {
			add_filter( 'wpseo_admin_pages', array( $this, 'register_local_page' ) );
		}

		/**
		 * Registers local page
		 *
		 * @param array $pages Array of admin pages.
		 *
		 * @return array
		 */
		function register_local_page( $pages ) {
			$pages[] = 'wpseo_local';

			return $pages;
		}

		/**
		 * Registers the settings page in the WP SEO menu.
		 *
		 * @since 1.0
		 *
		 * @param array $submenu_pages Array of submenu pages for SEO admin menu item.
		 *
		 * @return array
		 */
		function register_settings_page( $submenu_pages ) {
			$submenu_pages[] = array(
				'wpseo_dashboard',
				'Yoast SEO: Local SEO',
				'Local SEO',
				apply_filters( 'wpseo_manage_options_capability', 'wpseo_manage_options' ),
				'wpseo_local',
				array( 'WPSEO_Local_Admin_Page', 'build_page' ),
			);

			return $submenu_pages;
		}

		/**
		 * Load the form for a WPSEO admin page.
		 */
		function load_page() {
			if ( isset( $_GET['page'] ) ) {
				require_once( WPSEO_LOCAL_PATH . 'admin/pages/local.php' );
			}
		}

		/**
		 * Loads some CSS
		 *
		 * @since 1.0
		 */
		function config_page_styles() {
			global $pagenow, $post;

			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$css_ext = '.css';
			}
			else {
				$css_ext = '.min.css';
			}

			if ( ( $pagenow == 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] == 'wpseo_local' ) || $pagenow == 'term.php' || ( in_array( $pagenow, array(
						'post.php',
						'post-new.php',
					) ) && $post->post_type == 'wpseo_locations' )
			) {
				wp_enqueue_style( 'wpseo-local-admin-css', plugins_url( '../styles/dist/admin-' . yoast_seo_local_flatten_version( WPSEO_LOCAL_VERSION ) . $css_ext, dirname( __FILE__ ) ), WPSEO_LOCAL_VERSION );
			}
			else {
				if ( $pagenow == 'post-new.php' || $pagenow == 'post.php' ) {
					wp_enqueue_style( 'wpseo-local-admin-css', plugins_url( '../styles/dist/admin-' . yoast_seo_local_flatten_version( WPSEO_LOCAL_VERSION ) . $css_ext, dirname( __FILE__ ) ), WPSEO_LOCAL_VERSION );
				}
			}
		}

		/**
		 * Enqueues the (tiny) global JS needed for the plugin.
		 */
		function config_page_scripts() {
			global $pagenow, $post;

			// Define to use minified script or not.
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$css_ext = '.css';
				$js_ext  = '.js';
			}
			else {
				$css_ext = '.min.css';
				$js_ext  = '.min.js';
			}

			wp_enqueue_script( 'wpseo-local-global-script', plugins_url( 'js/dist/wp-seo-local-global-' . yoast_seo_local_flatten_version( WPSEO_LOCAL_VERSION ) . $js_ext, WPSEO_LOCAL_FILE ), array( 'jquery' ), WPSEO_LOCAL_VERSION, true );
			wp_enqueue_script( 'wpseo-local-support', plugins_url( 'js/dist/wp-seo-local-support-' . yoast_seo_local_flatten_version( WPSEO_LOCAL_VERSION ) . $js_ext, WPSEO_LOCAL_FILE ), array( 'jquery' ), WPSEO_LOCAL_VERSION, true );

			// Enqueue media script for use one Local SEO pages.
			if ( ( $pagenow == 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] == 'wpseo_local' ) || ( in_array( $pagenow, array(
						'post.php',
						'post-new.php',
					) ) && $post->post_type == 'wpseo_locations' ) || ( 'edit-tags.php' == $pagenow )
			) {
				wp_enqueue_media();
			}
		}

		/**
		 * Print the required JavaScript in the footer
		 */
		function config_page_footer() {
			global $pagenow, $post;
			if ( ( $pagenow == 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] == 'wpseo_local' ) || ( in_array( $pagenow, array(
						'post.php',
						'post-new.php',
					) ) && $post->post_type == 'wpseo_locations' )
			) {
				// @codingStandardsIgnoreStart
				?>
                <script>
                    jQuery(document).ready(function ($) {
                        $("#business_type").select2({
                            placeholder: "<?php _e( 'Choose a business type', 'yoast-local-seo' ); ?>",
                            allowClear: true
                        });
                        $("#location_country, #default_country").select2({
                            placeholder: "<?php _e( 'Choose a country', 'yoast-local-seo' ); ?>",
                            allowClear: true
                        });
                    });
                </script>
				<?php
				// @codingStandardsIgnoreEnd
			}
		}

		/**
		 * Generates the import panel for importing locations via CSV
		 */
		function import_panel() {

			echo '<div id="local-seo-import" class="yoastbox">';
			echo '<h2>' . __( 'CSV import of locations for Local Search', 'yoast-local-seo' ) . '</h2>';

			echo '</div>';
		}

		/**
		 * Flushes the rewrite rules if multiple locations is turned on or off or the slug is changed.
		 *
		 * @since 1.3.1
		 *
		 * @param mixed $old_option_value Value of the current option.
		 * @param mixed $new_option_value Value of the new, currently saved option.
		 */
		public function update_multiple_locations( $old_option_value, $new_option_value ) {
			$old_value_exists = array_key_exists( 'use_multiple_locations', $old_option_value );
			$new_value_exists = array_key_exists( 'use_multiple_locations', $new_option_value );

			$old_option_value['locations_slug'] = isset( $old_option_value['locations_slug'] ) ? esc_attr( $old_option_value['locations_slug'] ) : '';
			$new_option_value['locations_slug'] = isset( $new_option_value['locations_slug'] ) ? esc_attr( $new_option_value['locations_slug'] ) : '';

			$old_option_value['locations_taxo_slug'] = isset( $old_option_value['locations_taxo_slug'] ) ? esc_attr( $old_option_value['locations_taxo_slug'] ) : '';
			$new_option_value['locations_taxo_slug'] = isset( $new_option_value['locations_taxo_slug'] ) ? esc_attr( $new_option_value['locations_taxo_slug'] ) : '';

			if ( ( false === $old_value_exists && true === $new_value_exists ) || ( $old_option_value['locations_slug'] != $new_option_value['locations_slug'] ) || ( $old_option_value['locations_taxo_slug'] != $new_option_value['locations_taxo_slug'] ) ) {
				set_transient( 'wpseo_local_permalinks_settings_changed', true, 60 );
			}
		}

		/**
		 * Flushes the rewrite rules if multiple locations is turned on or off or the slug is changed.
		 *
		 * @since 1.3.1
		 */
		public function flush_rewrite_rules() {
			if ( get_transient( 'wpseo_local_permalinks_settings_changed' ) == true ) {
				flush_rewrite_rules();

				delete_transient( 'plugin_settings_have_changed' );
			}
		}

		/**
		 * Registers a notification if the Google Maps API browser key has not yet been set.
		 */
		public function maps_api_browser_key_notification() {
			if ( ! class_exists( 'Yoast_Notification_Center' ) ) {
				return;
			}
			$api_key_browser     = yoast_wpseo_local_get_api_key_browser();
			$notification_center = Yoast_Notification_Center::get();
			$notification        = new Yoast_Notification( /* translators: %1$s expands to Yoast SEO: Local, %2$s expands to Google Maps,%3$s expands to a link open tag to the settings page, %4$s expands to the closing tag for the link(s) to the settings page and %5$s expands to the opening tag for the link to the knowledge base article */
				sprintf( __( '%1$s needs a %2$s browser key to show %2$s on your website. You haven\'t set a %2$s browser key yet. Go to the %3$s%1$s API keys page%4$s to set the key, or %5$svisit the knowledge base%4$s for more information.', 'yoast-local-seo' ), 'Yoast SEO: Local', 'Google Maps', '<a href="' . admin_url( 'admin.php?page=wpseo_local#top#api_keys' ) . '">', '</a>', '<a href="https://yoa.st/gm-api-browser-key" target="_blank">' ),
				array(
					'type' => Yoast_Notification::ERROR,
					'id'   => 'LocalSEOBrowserKey',
				)
			);

			if ( empty( $api_key_browser ) ) {
				$notification_center->add_notification( $notification );
			}
			else {
				$notification_center->remove_notification( $notification );
			}
		}

		/**
		 * Registers a notification if the Google Maps API server key has not yet been set.
		 */
		public function maps_api_server_key_notification() {
			if ( ! class_exists( 'Yoast_Notification_Center' ) ) {
				return;
			}
			$api_key_server      = yoast_wpseo_local_get_api_key_server();
			$notification_center = Yoast_Notification_Center::get();
			$notification        = new Yoast_Notification( /* translators: %1$s expands to Yoast SEO: Local, %2$s expands to Google Maps,%3$s expands to a link open tag to the settings page, %4$s expands to the closing tag for the link(s) to the settings page and %5$s expands to the opening tag for the link to the knowledge base article */
				sprintf( __( '%1$s needs a %2$s server key to calculate the geographical location of addresses. You haven\'t set a %2$s server key yet. Go to the %3$s%1$s API keys page%4$s to set the key, or %5$svisit the knowledge base%4$s for more information.', 'yoast-local-seo' ), 'Yoast SEO: Local', 'Google Maps', '<a href="' . admin_url( 'admin.php?page=wpseo_local#top#api_keys' ) . '">', '</a>', '<a href="https://yoa.st/gm-geocoding-api-server-key" target="_blank">' ),
				array(
					'type' => Yoast_Notification::WARNING,
					'id'   => 'LocalSEOServerKey',
				)
			);

			if ( empty( $api_key_server ) ) {
				$notification_center->add_notification( $notification );
			}
			else {
				$notification_center->remove_notification( $notification );
			}
		}

		/**
		 * A function to check if the api key in the options needs to be set from a constant
		 */
		public function maybe_set_api_keys_from_constant() {
			if ( $this->is_local_seo_page( filter_input( INPUT_GET, 'page' ) ) ) {
				$options = get_option( $this->option_name );
				$keys    = array(
					'WPSEO_LOCAL_API_KEY_BROWSER' => 'api_key_browser',
					'WPSEO_LOCAL_API_KEY_SERVER'  => 'api_key',
				);

				foreach ( $keys as $constant => $key_name ) {
					if ( defined( $constant ) ) {
						$constant_value = constant( $constant );
						if ( ! isset( $options[ $key_name ] ) || ( ! empty( $constant_value ) && $options[ $key_name ] !== $constant_value ) ) {
							$options[ $key_name ] = $constant_value;
						}
					}
				}

				update_option( $this->option_name, $options );
			}
		}

		/**
		 * Checks if the page is a local seo page.
		 *
		 * @param string $page The page that might be a local seo page.
		 *
		 * @return bool
		 */
		private function is_local_seo_page( $page ) {
			$pages = array( 'wpseo_local' );

			return in_array( $page, $pages );
		}

		/**
		 * Register the promotion class for our GlotPress instance
		 *
		 * @link https://github.com/Yoast/i18n-module
		 */
		private function register_i18n_promo_class() {
			new yoast_i18n( array(
				'textdomain'     => 'yoast-local-seo',
				'project_slug'   => 'yoast-seo-local',
				'plugin_name'    => 'Local SEO by Yoast',
				'hook'           => 'wpseo_admin_promo_footer',
				'glotpress_url'  => 'http://translate.yoast.com/gp/',
				'glotpress_name' => 'Yoast Translate',
				'glotpress_logo' => 'https://translate.yoast.com/gp-templates/images/Yoast_Translate.svg',
				'register_url'   => 'https://translate.yoast.com/gp/projects#utm_source=plugin&utm_medium=promo-box&utm_campaign=wpseo-i18n-promo',
			) );
		}
	} /* End of class */

} /* End of class-exists wrapper */
