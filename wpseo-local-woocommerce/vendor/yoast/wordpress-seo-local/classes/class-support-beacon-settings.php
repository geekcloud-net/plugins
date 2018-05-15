<?php
/**
 * @package WPSEO\Video
 */

/**
 * Implements the helpscout beacon suggestions for Local SEO
 */
class WPSEO_Local_Beacon_Settings implements Yoast_HelpScout_Beacon_Setting {
	/**
	 * Returns a list of helpscout hashes to show the user for a certain page.
	 *
	 * @param string $page The current admin page we are on.
	 *
	 * @return array A list of suggestions for the beacon.
	 */
	public function get_suggestions( $page ) {
		switch ( $page ) {
			case 'wpseo_local':
				return array(
					// See: http://kb.yoast.com/article/104-installation-guide-for-the-local-seo-plugin.
					'537dbcf4e4b0fe61cc351f68',
					// See: http://kb.yoast.com/article/114-configuration-guide-for-local-seo.
					'53832990e4b0fe61cc352319',
					// See: http://kb.yoast.com/article/102-template-files-for-local-seo.
					'537db0dee4b0fe61cc351f3e',
				);
				break;
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
			case 'wpseo_local':
				return array( new Yoast_Product_WPSEO_Local() );
				break;
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
		return array( 'modal' => true );
	}
}
