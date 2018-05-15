<?php
/**
 * Yoast SEO: News plugin file.
 *
 * @package WPSEO_News
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

/**
 * Represents the update routine when a newer version has been installed.
 */
class WPSEO_News_Upgrade_Manager {

	/**
	 * Check if there's a plugin update.
	 */
	public function check_update() {

		// Get options.
		$options = WPSEO_News::get_options();

		// Check if update is required.
		if ( version_compare( WPSEO_News::VERSION, $options['version'], '>' ) ) {

			// Do update.
			$this->do_update( $options['version'] );

			// Update version code.
			$this->update_current_version_code();

		}

	}

	/**
	 * An update is required, do it.
	 *
	 * @param string $current_version The current version.
	 */
	private function do_update( $current_version ) {

		// Upgrade to version 2.0.4.
		if ( version_compare( $current_version, '2.0.4', '<' ) ) {

			// Remove unused option.
			$news_options = WPSEO_News::get_options();
			unset( $news_options['ep_image_title'] );

			// Update options.
			update_option( 'wpseo_news', $news_options );

			// Reset variable.
			$news_options = null;

		}

		// Update to version 2.0.
		if ( version_compare( $current_version, '2.0', '<' ) ) {

			// Get current options.
			$current_options = get_option( 'wpseo_news' );

			// Set new options.
			$new_options = array(
				'name'             => ( ( isset( $current_options['newssitemapname'] ) ) ? $current_options['newssitemapname'] : '' ),
				'default_genre'    => ( ( isset( $current_options['newssitemap_default_genre'] ) ) ? $current_options['newssitemap_default_genre'] : '' ),
			);

			// Save new options.
			update_option( 'wpseo_news', $new_options );

		}

	}

	/**
	 * Update the current version code.
	 */
	private function update_current_version_code() {
		$options            = WPSEO_News::get_options();
		$options['version'] = WPSEO_News::VERSION;
		update_option( 'wpseo_news', $options );
	}
}
