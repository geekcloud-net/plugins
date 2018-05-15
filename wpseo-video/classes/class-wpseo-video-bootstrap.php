<?php
/**
 * @package Yoast\VideoSEO
 */

/**
 * Bootstraps the Video SEO Module.
 *
 * Makes sure the environment has all the elements we need to be able to run.
 * Notifies the user when certain elements are missing.
 */
class WPSEO_Video_Bootstrap {
	/** @var array Queued admin notices. */
	protected $admin_notices = array();

	/**
	 * Adds hooks to integrate with WordPress.
	 *
	 * @return void
	 */
	public function add_hooks() {
		// Always load the translations to make sure the notifications are translated as well.
		add_action( 'admin_init', array( 'WPSEO_Video_Sitemap', 'load_textdomain' ), 1 );

		$can_activate = $this->can_activate();
		if ( ! $can_activate ) {
			$this->add_admin_notices_hook();

			return;
		}

		$this->add_integration_hooks();
	}

	/**
	 * Shows any queued admin notices.
	 *
	 * @return void
	 */
	public function show_admin_notices() {
		if ( empty( $this->admin_notices ) ) {
			return;
		}

		if ( $this->is_iframe_request() ) {
			return;
		}

		foreach ( $this->admin_notices as $admin_notice ) {
			$this->display_admin_notice( $admin_notice );
		}
	}

	/**
	 * Adds hooks to load the video integrations.
	 *
	 * @return void
	 */
	protected function add_integration_hooks() {
		add_action( 'plugins_loaded', array( $this, 'load_metabox_integration' ), 10 );
		add_action( 'plugins_loaded', array( $this, 'load_sitemap_integration' ), 20 );
	}

	/**
	 * Loads the metabox integration.
	 *
	 * @return void
	 */
	public function load_metabox_integration() {
		WPSEO_Meta_Video::init();
	}

	/**
	 * Loads the sitemap integration.
	 *
	 * @return void
	 */
	public function load_sitemap_integration() {
		$GLOBALS['wpseo_video_xml'] = new WPSEO_Video_Sitemap();
	}

	/**
	 * Checks if the plugin can be activated.
	 *
	 * @return bool True if the plugin has the environment to work in.
	 */
	protected function can_activate() {
		if ( ! $this->is_spl_autoload_available() ) {
			$this->add_admin_notice(
				esc_html__(
					'The PHP SPL extension seems to be unavailable. Please ask your web host to enable it.',
					'yoast-video-seo'
				),
				true
			);
		}

		if ( ! $this->is_wordpress_up_to_date() ) {
			$this->add_admin_notice(
				esc_html__(
					'Please upgrade WordPress to the latest version to allow WordPress and the Video SEO module to work properly.',
					'yoast-video-seo'
				)
			);
		}

		if ( ! $this->is_yoast_seo_active() ) {
			$this->add_admin_notice( $this->get_wpseo_missing_error() );
		}

		// Allow beta version.
		if ( $this->is_yoast_seo_active() && ! $this->is_yoast_seo_up_to_date() ) {
			$this->add_admin_notice(
				sprintf(
					/* translators: $1$s expands to Yoast SEO. */
					esc_html__(
						'Please upgrade the %1$s plugin to the latest version to allow the Video SEO module to work.',
						'yoast-video-seo'
					),
					'Yoast SEO'
				)
			);
		}

		return empty( $this->admin_notices );
	}

	/**
	 * Retrieves the message to show to make sure Yoast SEO gets activated.
	 *
	 * @return string The message to present to the user.
	 */
	protected function get_wpseo_missing_error() {
		if ( ! $this->user_can_activate_plugins() ) {
			return $this->get_install_by_admin_message();
		}

		return $this->get_install_plugin_message();
	}

	/**
	 * Queues an admin notification.
	 *
	 * @param string $message    Message to be shown.
	 * @param bool   $use_prefix Optional. Use the default prefix or not.
	 *
	 * @return void
	 */
	protected function add_admin_notice( $message, $use_prefix = false ) {
		$prefix = '';

		if ( $use_prefix ) {
			$prefix = esc_html( $this->get_admin_notice_prefix() ) . ' ';
		}

		$this->admin_notices[] = $prefix . $message;
	}

	/**
	 * Registers the admin notices hooks to display messages.
	 *
	 * @return void
	 */
	protected function add_admin_notices_hook() {
		$hook = 'admin_notices';
		if ( $this->use_multisite_notifications() ) {
			$hook = 'network_' . $hook;
		}

		add_action( $hook, array( $this, 'show_admin_notices' ) );
	}

	/**
	 * Displays an admin notice.
	 *
	 * @param string $admin_notice Notice to display.
	 *
	 * @return void
	 */
	protected function display_admin_notice( $admin_notice ) {
		echo '<div class="error"><p>' . $admin_notice . '</p></div>';
	}

	/**
	 * Checks if the user can install or activate plugins.
	 *
	 * @return bool True if the user can activate plugins.
	 */
	protected function user_can_activate_plugins() {
		return current_user_can( 'install_plugins' ) || current_user_can( 'activate_plugins' );
	}

	/**
	 * Checks if the current request is an iFrame request.
	 *
	 * @return bool True if this request is an iFrame request.
	 */
	protected function is_iframe_request() {
		return defined( 'IFRAME_REQUEST' ) && IFRAME_REQUEST !== false;
	}

	/**
	 * Checks whether we should use multisite notifications or not.
	 *
	 * @return bool True if we want to use multisite notifications.
	 */
	protected function use_multisite_notifications() {
		return is_multisite() && is_network_admin();
	}

	/**
	 * Retrieves the plugin page URL to use.
	 *
	 * @return string Plugin page URL to use.
	 */
	protected function get_plugin_page_url() {
		$page_slug = 'plugin-install.php';

		if ( $this->use_multisite_plugin_page() ) {
			return network_admin_url( $page_slug );
		}

		return admin_url( $page_slug );
	}

	/**
	 * Checks if we should use the multisite plugin page.
	 *
	 * @return bool True if we are on multisite and super admin.
	 */
	protected function use_multisite_plugin_page() {
		return is_multisite() === true && is_super_admin();
	}

	/**
	 * Checks if SPL Autoload is available.
	 *
	 * @return bool True if SPL Autoload is available.
	 */
	protected function is_spl_autoload_available() {
		return function_exists( 'spl_autoload_register' );
	}

	/**
	 * Checks if WordPress is at a mimimal required version.
	 *
	 * @return bool True if WordPress is at a minimal required version.
	 */
	protected function is_wordpress_up_to_date() {
		return version_compare( $GLOBALS['wp_version'], '4.8', '>=' );
	}

	/**
	 * Checks if Yoast SEO is active.
	 *
	 * @return bool True if Yoast SEO is active.
	 */
	public function is_yoast_seo_active() {
		return defined( 'WPSEO_VERSION' );
	}

	/**
	 * Checks if Yoast SEO is at a minimum required version.
	 *
	 * @return bool True if Yoast SEO is at a minimal required version.
	 */
	protected function is_yoast_seo_up_to_date() {
		// At least 7.0, but including RC versions, so bigger than 6.9.
		return $this->is_yoast_seo_active() && version_compare( WPSEO_VERSION, '6.9', '>' );
	}

	/**
	 * Returns the admin notice prefix string.
	 *
	 * @return string The string to prefix the admin notice with.
	 */
	protected function get_admin_notice_prefix() {
		return __( 'Activation of Video SEO failed:', 'yoast-video-seo' );
	}

	/**
	 * Generates the message to display to install Yoast SEO by proxy.
	 *
	 * @return string The message to show to inform the user to install Yoast SEO.
	 */
	protected function get_install_by_admin_message() {
		return sprintf(
			/* translators: %1$s expands to Yoast SEO. */
			esc_html__(
				'Please ask the (network) admin to install & activate %1$s and then enable its XML sitemap functionality to allow the Video SEO module to work.',
				'yoast-video-seo'
			),
			'Yoast SEO'
		);
	}

	/**
	 * Generates the message to display to install Yoast SEO.
	 *
	 * @return string The message to show to inform the user to install Yoast SEO.
	 */
	protected function get_install_plugin_message() {
		$url = add_query_arg(
			array(
				'tab'                 => 'search',
				'type'                => 'term',
				's'                   => 'wordpress+seo',
				'plugin-search-input' => 'Search+Plugins',
			),
			$this->get_plugin_page_url()
		);

		return sprintf(
			/* translators: %1$s and %3$s expand to anchor tags with a link to the download page for Yoast SEO . %2$s expands to Yoast SEO. */
			esc_html__(
				'Please %1$sinstall & activate %2$s%3$s and then enable its XML sitemap functionality to allow the Video SEO module to work.',
				'yoast-video-seo'
			),
			'<a href="' . esc_url( $url ) . '">',
			'Yoast SEO',
			'</a>'
		);
	}
}
