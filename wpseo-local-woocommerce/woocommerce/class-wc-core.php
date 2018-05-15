<?php

class Yoast_WCSEO_Local_Core extends WPSEO_Local_Core {

	public function __construct() {
		add_action( 'update_option_wpseo_local', array( $this, 'maybe_flush_shipping_transients' ), 9, 2 );

		parent::__construct();
	}

	/**
	 * Returns an instance of the Yoast_Plugin_License_Manager class
	 * Takes care of remotely (de)activating licenses and plugin updates.
	 */
	protected function get_license_manager() {

		// We need WP SEO 1.5+ or higher but WP SEO Local doesn't have a version check.
		if( ! $this->license_manager ) {
			//@todo create another product for this plugin
			$this->license_manager = new Yoast_Plugin_License_Manager( new Yoast_WCSEO_Local_Product() );
			$this->license_manager->set_license_constant_name( 'WPSEO_LOCAL_LICENSE' );
		}

		return $this->license_manager;
	}

	/**
	 * Flushes the shipping transients if multiple locations is turned on or off or the slug is changed.
	 */
	public function maybe_flush_shipping_transients( $old_option_value, $new_option_value ) {
		$old_value_exists = array_key_exists( 'use_multiple_locations', $old_option_value );
		$new_value_exists = array_key_exists( 'use_multiple_locations', $new_option_value );

		if ( $old_value_exists !== $new_value_exists ) {
			global $wpdb;
			$wpdb->query("DELETE FROM ".$wpdb->prefix."options WHERE option_name LIKE '_transient_wc_ship%'");
		}
	}
}