<?php
/**
 * @package WPSEO_Local\Main
 */

if ( ! class_exists( 'Yoast_Product_WPSEO_Local', false ) && class_exists( 'Yoast_Product' ) ) {

	/**
	 * Class Yoast_Product_WPSEO_Local
	 */
	class Yoast_Product_WPSEO_Local extends Yoast_Product {

		/**
		 * Yoast_Product_WPSEO_Local constructor.
		 */
		public function __construct() {
			$file = plugin_basename( WPSEO_LOCAL_FILE );
			$slug = dirname( $file );

			parent::__construct(
				'http://my.yoast.com/edd-sl-api',
				'Local SEO for WordPress',
				$slug,
				WPSEO_LOCAL_VERSION,
				'https://yoast.com/wordpress/plugins/local-seo/',
				'admin.php?page=wpseo_licenses#top#licenses',
				'yoast-local-seo',
				'Yoast',
				$file
			);
		}
	}

}
