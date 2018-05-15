<?php

class Yoast_WCSEO_Local_Product extends Yoast_Product {
	public function __construct() {
		parent::__construct(
			'https://yoast.com/edd-sl-api',
			'Local SEO for WooCommerce',
			plugin_basename( WPSEO_LOCAL_WOOCOMMERCE_FILE ),
			WPSEO_LOCAL_WOOCOMMERCE_VERSION,
			'https://yoast.com/wordpress/plugins/local-seo-woocommerce/',
			'admin.php?page=wpseo_licenses#top#licenses',
			'yoast-local-seo-woocommerce',
			'Yoast'
		);
	}
}