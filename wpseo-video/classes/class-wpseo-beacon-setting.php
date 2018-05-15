<?php
/**
 * @package WPSEO\Video
 */

/**
 * Implements the helpscout beacon suggestions for wpseo Video
 */
class WPSEO_Video_Beacon_Setting implements Yoast_HelpScout_Beacon_Setting {

	/**
	 * Returns a list of helpscout hashes to show the user for a certain page.
	 *
	 * @param string $page The current admin page we are on.
	 *
	 * @return array A list of suggestions for the beacon
	 */
	public function get_suggestions( $page ) {
		switch ( $page ) {
			case 'wpseo_video':
				return array(
					// See: http://kb.yoast.com/article/93-video-seo-plugin-configuration-guide.
					'537a1874e4b0feebb512e8e5',
					// See: http://kb.yoast.com/article/95-supported-video-hosting-platforms-for-video-seo-plugin.
					'537a1b2be4b0feebb512e90b',
					// See: http://kb.yoast.com/article/18-why-doesnt-my-video-seo-plugin-create-a-sitemap.
					'5375e2d2e4b0d833740d570d',
				);
		}

		return array();
	}

	/**
	 * Returns a product for a a certain admin page.
	 *
	 * @param string $page The current admin page we are on.
	 *
	 * @return Yoast_Product[] A product to use for sending data to helpscout
	 */
	public function get_products( $page ) {
		switch ( $page ) {
			case 'wpseo_video':
				return array( new Yoast_Product_WPSEO_Video() );
		}

		return array();
	}

	/**
	 * Returns a list of config values for a a certain admin page.
	 *
	 * @param string $page The current admin page we are on.
	 *
	 * @return array A list with configuration for the beacon
	 */
	public function get_config( $page ) {
		return array();
	}
}
