<?php
/**
 * @package Yoast\VideoSEO
 */

// Avoid direct calls to this file.
if ( ! class_exists( 'WPSEO_Video_Sitemap' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'Yoast_Product_WPSEO_Video', false ) && class_exists( 'Yoast_Product' ) ) {

	/**
	 * Class Yoast_Product_WPSEO_Video
	 *
	 * Our Yoast_Product_WPSEO_Video class
	 */
	class Yoast_Product_WPSEO_Video extends Yoast_Product {

		/**
		 * Set up the WPSEO_Video product
		 */
		public function __construct() {
			$file = plugin_basename( WPSEO_VIDEO_FILE );
			$slug = dirname( $file );

			parent::__construct(
				'http://my.yoast.com/edd-sl-api',
				'Video SEO for WordPress',
				$slug,
				WPSEO_VIDEO_VERSION,
				'https://yoast.com/wordpress/plugins/video-seo/',
				'admin.php?page=wpseo_licenses#top#licenses',
				'yoast-video-seo',
				'Yoast',
				$file
			);
		}
	} /* End of class */

} /* End of class-exists wrapper */
