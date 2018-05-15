<?php

if ( ! class_exists( 'UltimateBrandingPublic' ) ) {

	class UltimateBrandingPublic {

		// The modules in the public class are only those that need to be loaded on the public side of the site as well
		var $modules = array(
			'admin-bar-logo.php' => 'admin-bar-logo.php',
			'author-box/author-box.php' => 'author-box/author-box.php',
			'custom-admin-bar.php' => 'custom-admin-bar.php',
			'custom-email-from.php' => 'custom-email-from/custom-email-from.php',
			'custom-login-css.php' => 'custom-login-css.php',
			'custom-login-screen.php' => 'custom-login-screen.php',
			'custom-ms-register-emails.php' => 'custom-ms-register-emails.php',
			'favicons.php' => 'favicons.php',
			'global-footer-content.php' => 'global-footer-content/global-footer-content.php',
			'global-header-content.php' => 'global-header-content/global-header-content.php',
			'htmlemail.php' => 'htmlemail.php',
			'image-upload-size.php' => 'image-upload-size.php',
			'login-image.php' => 'login-image/login-image.php',
			'maintenance/maintenance.php' => 'maintenance/maintenance.php',
			'rebranded-meta-widget.php' => 'rebranded-meta-widget/rebranded-meta-widget.php',
			'signup-blog-description.php' => 'signup-blog-description.php',
			'site-generator-replacement.php' => 'site-generator-replacement/site-generator-replacement.php',
			'site-wide-text-change.php' => 'site-wide-text-change/site-wide-text-change.php',
			'ultimate-color-schemes.php' => 'ultimate-color-schemes.php',
		);

		var $plugin_msg = array();

		public function __construct() {
			ub_set_ub_version();
			add_action( 'plugins_loaded', array( $this, 'load_modules' ) );
		}

		/**
		 * 	Check plugins those will be used if they are active or not
		 */
		public function load_modules() {
			// Load our remaining modules here
			foreach ( $this->modules as $module => $plugin ) {
				if ( ub_is_active_module( $module ) ) {
					ub_load_single_module( $module );
				}
			}
		}
	}

}