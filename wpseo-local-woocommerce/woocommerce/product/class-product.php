<?php

if ( ! class_exists( 'Yoast_Product_WPSEO_Local' ) && class_exists( 'Yoast_Product' ) ) {

	/**
	 * Class Yoast_Product_WPSEO_Local ( this needs to be named this way! To override the Local-SEO product class )
	 */
	class Yoast_Product_WPSEO_Local extends Yoast_Product {

		/**
		 * Yoast_Product_WPSEO_Local constructor.
		 */
		public function __construct() {
			$file = plugin_basename( WPSEO_LOCAL_WOOCOMMERCE_FILE );
			$slug = dirname( $file );

			parent::__construct(
				'http://my.yoast.com/edd-sl-api',
				'Local SEO for WooCommerce',
				$slug,
				WPSEO_LOCAL_WOOCOMMERCE_VERSION,
				'https://yoast.com/wordpress/plugins/local-seo-for-woocommerce/',
				'admin.php?page=wpseo_licenses#top#licenses',
				'yoast-seo-local-seo-for-woocommerce',
				'Yoast',
				$file
			);
		}
	}
}
