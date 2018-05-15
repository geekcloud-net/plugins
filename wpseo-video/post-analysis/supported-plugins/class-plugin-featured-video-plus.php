<?php
/**
 * @package Internals
 * @since   3.5.0
 * @version 3.5.0
 */

// Avoid direct calls to this file.
if ( ! class_exists( 'WPSEO_Video_Sitemap' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 *****************************************************************
 * Add support for ahoereth's Featured Video Plus plugin.
 *
 * @see      https://github.com/ahoereth/featured-video-plus
 * @see      https://wordpress.org/plugins/featured-video-plus/
 *
 * {@internal Last update: August 2016 based upon v2.3.}}
 */
if ( ! class_exists( 'WPSEO_Video_Plugin_Featured_Video_Plus' ) ) {

	/**
	 * Class WPSEO_Video_Plugin_Featured_Video_Plus
	 */
	class WPSEO_Video_Plugin_Featured_Video_Plus extends WPSEO_Video_Supported_Plugin {


		/**
		 * Conditionally add plugin features to analyse for video content
		 */
		public function __construct() {
			if ( class_exists( 'Featured_Video_Plus' ) ) {
				$this->meta_keys[] = '_fvp_video';
			}
		}


		/**
		 * Analyse a specific post meta field for usable video information
		 *
		 * @param	string $meta_value The value to analyse.
		 * @param	string $meta_key   The associated meta key.
		 * @param	int    $post_id    The id of the post this meta value applies to.
		 *
		 * @return array An array with the usable information found.
		 */
		public function get_info_from_post_meta( $meta_value, $meta_key, $post_id ) {
			return array(
				'url' => $meta_value,
			);
		}
	} /* End of class */

} /* End of class-exists wrapper */
