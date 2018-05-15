<?php
/**
 * @package Yoast\VideoSEO
 */

/**
 * Initializes the Video SEO module on plugins loaded.
 *
 * This way WordPress SEO should have set its constants and loaded its main classes.
 *
 * @since 0.2
 */
function yoast_wpseo_video_seo_init() {
	$bootstrap = new WPSEO_Video_Bootstrap();
	$bootstrap->add_hooks();
}

/**
 * Executes option cleanup actions on activate.
 *
 * There are a couple of things being done on activation:
 * - Cleans up the options to be sure it's set well.
 * - Activates the license, because updating the plugin results in deactivating the license.
 * - Clears the sitemap cache to rebuild the sitemap.
 */
function yoast_wpseo_video_activate() {
	WPSEO_Video_Sitemap::load_textdomain();

	$bootstrap = new WPSEO_Video_Bootstrap();
	if ( ! $bootstrap->is_yoast_seo_active() ) {
		return;
	}

	$option_instance = WPSEO_Option_Video::get_instance();
	$option_instance->clean();

	yoast_wpseo_video_clear_sitemap_cache();

	if ( class_exists( 'Yoast_Plugin_License_Manager' ) ) {
		// Activate the license.
		$license_manager = new Yoast_Plugin_License_Manager( new Yoast_Product_WPSEO_Video() );
		$license_manager->activate_license();
	}
}

/**
 * Empties sitemap cache on plugin deactivate.
 *
 * @since 3.8.0
 */
function yoast_wpseo_video_deactivate() {
	yoast_wpseo_video_clear_sitemap_cache();
}

/**
 * Clears the sitemap index.
 *
 * @since 3.8.0
 */
function yoast_wpseo_video_clear_sitemap_cache() {
	$bootstrap = new WPSEO_Video_Bootstrap();
	if ( ! $bootstrap->is_yoast_seo_active() ) {
		return;
	}

	$sitemap_instance = new WPSEO_Video_Sitemap();
	$sitemap_basename = $sitemap_instance->video_sitemap_basename();

	WPSEO_Video_Wrappers::invalidate_sitemap( $sitemap_basename );
}

/**
 * Throws an error if WordPress SEO is not installed.
 *
 * @since      0.2
 *
 * @deprecated 6.1
 */
function yoast_wpseo_missing_error() {
	_deprecated_function( __FUNCTION__, '6.1', 'WPSEO_Video_Bootstrap::get_wpseo_missing_error' );

	if ( current_user_can( 'install_plugins' ) || current_user_can( 'activate_plugins' ) ) {
		$page_slug = 'plugin-install.php';
		if ( is_multisite() === true && is_super_admin() ) {
			$base_url = network_admin_url( $page_slug );
		}
		else {
			$base_url = admin_url( $page_slug );
		}

		$url = add_query_arg(
			array(
				'tab'                 => 'search',
				'type'                => 'term',
				's'                   => 'wordpress+seo',
				'plugin-search-input' => 'Search+Plugins',
			),
			$base_url
		);

		/* translators: %1$s and %3$s expand to anchor tags with a link to the download page for Yoast SEO . %2$s expands to Yoast SEO.*/
		$message = sprintf( esc_html__( 'Please %1$sinstall & activate %2$s%3$s and then enable its XML sitemap functionality to allow the Video SEO module to work.', 'yoast-video-seo' ), '<a href="' . esc_url( $url ) . '">', 'Yoast SEO', '</a>' );
	}
	else {
		/* translators: %1$s expands to Yoast SEO.*/
		$message = sprintf( esc_html__( 'Please ask the (network) admin to install & activate %1$s and then enable its XML sitemap functionality to allow the Video SEO module to work.', 'yoast-video-seo' ), 'Yoast SEO' );
	}

	yoast_wpseo_video_seo_self_deactivate( $message, false );
}


/**
 * Throws an error if WordPress is out of date.
 *
 * @since      1.5.4
 * @deprecated 6.1
 */
function yoast_wordpress_upgrade_error() {
	_deprecated_function( __FUNCTION__, '6.1', 'WPSEO_Video_Bootstrap::can_activate' );

	$message = esc_html__( 'Please upgrade WordPress to the latest version to allow WordPress and the Video SEO module to work properly.', 'yoast-video-seo' );
	yoast_wpseo_video_seo_self_deactivate( $message );
}


/**
 * Throws an error if WordPress SEO is out of date.
 *
 * @since      1.5.4
 * @deprecated 6.1
 */
function yoast_wpseo_upgrade_error() {
	_deprecated_function( __FUNCTION__, '6.1', 'WPSEO_Video_Bootstrap::can_activate' );

	/* translators: $1$s expands to Yoast SEO.*/
	$message = sprintf( esc_html__( 'Please upgrade the %1$s plugin to the latest version to allow the Video SEO module to work.', 'yoast-video-seo' ), 'Yoast SEO' );
	yoast_wpseo_video_seo_self_deactivate( $message );
}

/**
 * Throws an error if the PHP SPL extension is disabled (prevent white screens)
 *
 * @since      1.7
 * @deprecated 6.1
 */
function yoast_phpspl_missing_error() {
	_deprecated_function( __FUNCTION__, '6.1', 'WPSEO_Video_Bootstrap::can_activate' );

	$message = esc_html__( 'The PHP SPL extension seems to be unavailable. Please ask your web host to enable it.', 'yoast-video-seo' );
	yoast_wpseo_video_seo_self_deactivate( $message );
}

/**
 * Initializes the video metadata class
 *
 * @deprecated 6.1
 */
function yoast_wpseo_video_seo_meta_init() {
	_deprecated_function( __FUNCTION__, '6.1', 'WPSEO_Video_Bootstrap::load_metabox_integration' );

	WPSEO_Meta_Video::init();
}

/**
 * Initializes the main plugin class
 *
 * @deprecated 6.1
 */
function yoast_wpseo_video_seo_sitemap_init() {
	_deprecated_function( __FUNCTION__, '6.1', 'WPSEO_Video_Bootstrap::load_sitemap_integration' );

	$GLOBALS['wpseo_video_xml'] = new WPSEO_Video_Sitemap();
}

/**
 * Self-deactivates plugin
 *
 * @since      1.7
 * @deprecated 6.1
 *
 * @param string $message    Error message.
 * @param bool   $use_prefix Prefix the text with Activation.
 */
function yoast_wpseo_video_seo_self_deactivate( $message, $use_prefix = true ) {
	_deprecated_function( __FUNCTION__, '6.1', 'WPSEO_Video_Bootstrap::show_admin_notices' );

	if ( ! is_admin() ) {
		return;
	}

	if ( defined( 'IFRAME_REQUEST' ) && IFRAME_REQUEST !== false ) {
		return;
	}

	$prefix  = ( $use_prefix ) ? __( 'Activation of Video SEO failed:', 'yoast-video-seo' ) : '';
	$file    = plugin_basename( WPSEO_VIDEO_FILE );
	$ms_hook = ( is_multisite() && is_network_admin() ) ? 'network_' : '';

	$function_code = <<<EO_FUNCTION
echo '<div class="error"><p>{$prefix} {$message}</p></div>';
EO_FUNCTION;

	// PHP 7.2 deprecates `create_function`, this method has been deprecated and can be removed in due time.
	// @codingStandardsIgnoreLine
	add_action( $ms_hook . 'admin_notices', @create_function( '', $function_code ) );

	// Add to recently active plugins list.
	if ( is_network_admin() ) {
		update_site_option( 'recently_activated', ( array( $file => time() ) + (array) get_site_option( 'recently_activated' ) ) );
	}
	else {
		update_option( 'recently_activated', ( array( $file => time() ) + (array) get_option( 'recently_activated' ) ) );
	}

	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}
