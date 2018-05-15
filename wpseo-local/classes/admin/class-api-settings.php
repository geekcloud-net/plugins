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

if ( ! class_exists( 'WPSEO_Local_Admin_API_Settings' ) ) {

	/**
	 * WPSEO_Local_Admin_API_Settings class.
	 *
	 * Build the WPSEO Local admin form.
	 *
	 * @since   4.0
	 */
	class WPSEO_Local_Admin_API_Settings {

		/**
		 * @var string Holds the slug for this settings tab.
		 */
		private $slug = 'api_keys';

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
			add_filter( 'wpseo_local_admin_tabs', array( $this, 'create_tab' ) );
			add_filter( 'wpseo_local_admin_help_center_video', array( $this, 'set_video' ) );

			add_action( 'wpseo_local_admin_' . $this->slug . '_content', array( $this, 'tab_content' ), 10 );
		}

		/**
		 * @param array $tabs Array holding the tabs.
		 *
		 * @return mixed
		 */
		public function create_tab( $tabs ) {
			$tabs[ $this->slug ] = __( 'API keys', 'yoast-local-seo' );

			return $tabs;
		}

		/**
		 * @param array $videos Array holding the videos for the help center.
		 *
		 * @return mixed
		 */
		public function set_video( $videos ) {
			$videos[ $this->slug ] = 'https://yoa.st/screencast-local-settings-api-keys';

			return $videos;
		}

		/**
		 * Create tab content for API Settings.
		 */
		public function tab_content() {
			echo '<h3>API key for Google Maps</h3>';
			/* translators: %1$s extends to the anchor opening tag '<a href="https://yoa.st/gm-api-browser-key" target="_blank">', %2$s closes that tag. */
			echo '<p>' . sprintf( __( 'A Google Maps browser key is required to show Google Maps and make use of the Store Locator. For more information on how to create and set your Google Maps browser key, open the help center or %1$scheck our knowledge base%2$s.', 'yoast-local-seo' ), '<a href="https://yoa.st/gm-api-browser-key" target="_blank">', '</a>' ) . '</p>';
			WPSEO_Local_Admin_Wrappers::textinput( 'api_key_browser', __( 'Google Maps API browser key (required)', 'yoast-local-seo' ) );
			if ( defined( 'WPSEO_LOCAL_API_KEY_BROWSER' ) ) {
				/* translators: %s extends to the API Key constant name */
				echo '<p class="help">' . sprintf( __( 'You defined your API key using the %s PHP constant.', 'yoast-local-seo' ), '<code>WPSEO_LOCAL_API_KEY_BROWSER</code>' ) . '</p>';
			}

			echo '<h3>API key for geocoding</h3>';
			/* translators: %1$s extends to a number; %2$s opens a link tag to the Yoast knowledge base; %3$s closes that link. */
			echo '<p>' . sprintf( __( 'A Google Maps Geocoding server key will calculate the latitude and longitude of an address. With this key, you can retrieve the geographical location of up to %1$s addresses per 24 hours. For more information on how to create and set your Google Maps Geocoding server key, open the help center or %2$scheck our knowledge base%3$s. Note, that it is not required to output a Google Map.', 'yoast-local-seo' ), number_format_i18n( 2500, 0 ), '<a href="https://yoa.st/gm-geocoding-api-server-key" target="_blank">', '</a>' ) . '</p>';
			WPSEO_Local_Admin_Wrappers::textinput( 'api_key', __( 'Google Maps API server key<br>(not required)', 'yoast-local-seo' ) );
			if ( defined( 'WPSEO_LOCAL_API_KEY_SERVER' ) ) {
				/* translators: %s extends to the API Key constant name */
				echo '<p class="help">' . sprintf( __( 'You defined your API key using the %s PHP constant.', 'yoast-local-seo' ), '<code>WPSEO_LOCAL_API_KEY_SERVER</code>' ) . '</p>';
			}
		}
	}
}
