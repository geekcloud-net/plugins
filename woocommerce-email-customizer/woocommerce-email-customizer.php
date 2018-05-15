<?php
/**
 * Plugin Name: WooCommerce Email Customizer
 * Plugin URI: https://woocommerce.com/products/woocommerce-email-customizer/
 * Description: Customize your WooCommerce emails with the WordPress Customizer.
 * Author: WooCommerce
 * Author URI: https://woocommerce.com/
 * Version: 1.1.7
 * Text Domain: woocommerce-email-customizer
 * Domain Path: /languages
 * WC tested up to: 3.3
 * WC requires at least: 2.6
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * Woo: 853277:bd909fa97874d431f203b5336c7e8873
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

// Plugin updates
woothemes_queue_update( plugin_basename( __FILE__ ), 'bd909fa97874d431f203b5336c7e8873', '853277' );

register_activation_hook( __FILE__, 'wc_email_customizer_activation' );

register_deactivation_hook( __FILE__, 'wc_email_customizer_deactivation' );

/**
 * Activation tasks
 *
 * @since 1.1.0
 * @version 1.1.0
 * @return bool
 */
function wc_email_customizer_activation() {
	// save current email settings
	$header_image     = get_option( 'woocommerce_email_header_image', 'http://' );
	$footer_text      = get_option( 'woocommerce_email_footer_text', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) . ' - Powered by WooCommerce' );
	$base_color       = get_option( 'woocommerce_email_base_color', '#557da1' );
	$background_color = get_option( 'woocommerce_email_background_color', '#f5f5f5' );
	$body_bg_color    = get_option( 'woocommerce_email_body_background_color' , '#fdfdfd' );
	$text_color       = get_option( 'woocommerce_email_text_color', '#505050' );

	$settings = array(
		'woocommerce_email_header_image'          => $header_image,
		'woocommerce_email_footer_text'           => $footer_text,
		'woocommerce_email_base_color'            => $base_color,
		'woocommerce_email_background_color'      => $background_color,
		'woocommerce_email_body_background_color' => $body_bg_color,
		'woocommerce_email_text_color'            => $text_color,
	);

	update_option( 'wc_email_customizer_old_settings', $settings );

	return true;
}

/**
 * Deactivation tasks
 *
 * @since 1.1.0
 * @version 1.1.0
 * @return bool
 */
function wc_email_customizer_deactivation() {
	$settings = get_option( 'wc_email_customizer_old_settings' );

	foreach ( $settings as $setting => $value ) {
		update_option( $setting, $value );
	}

	return true;
}

if ( ! class_exists( 'WC_Email_Customizer' ) ) :

	/**
	 * WC Email Customizer class
	 */
	class WC_Email_Customizer {

		/**
		 * Constructor
		 */
		public function __construct() {

			define( 'WC_EMAIL_CUSTOMIZER_VERSION', '1.1.7' );
			define( 'WC_EMAIL_CUSTOMIZER_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
			define( 'WC_EMAIL_CUSTOMIZER_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

			if ( is_woocommerce_active() ) {

				require_once( dirname( __FILE__ ) . '/includes/admin/class-wc-email-customizer-api.php' );

				if ( is_admin() ) {
					require_once( dirname( __FILE__ ) . '/includes/admin/class-wc-email-customizer-admin.php' );
				}
			} else {

				add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );

			}

			return true;
		}

		/**
		 * load the plugin text domain for translation.
		 *
		 * @since 1.0.0
		 * @return bool
		 */
		public function load_plugin_textdomain() {
			$locale = apply_filters( 'wc_email_customizer_plugin_locale', get_locale(), 'woocommerce-email-customizer' );

			load_textdomain( 'woocommerce-email-customizer', trailingslashit( WP_LANG_DIR ) . 'woocommerce-email-customizer/woocommerce-email-customizer' . '-' . $locale . '.mo' );

			load_plugin_textdomain( 'woocommerce-email-customizer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

			return true;
		}

		/**
		 * WooCommerce fallback notice.
		 *
		 * @return string
		 */
		public function woocommerce_missing_notice() {
			echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Email Customizer Plugin requires WooCommerce to be installed and active. You can download %s here.', 'woocommerce-email-customizer' ), '<a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a>' ) . '</p></div>';

			return true;
		}
	}

	add_action( 'plugins_loaded', 'woocommerce_email_customizer_init', 0 );

	/**
	 * init function
	 *
	 * @package WC_Email_Customizer
	 * @since 1.0.0
	 * @return bool
	 */
	function woocommerce_email_customizer_init() {
		new WC_Email_Customizer();

		return true;
	}

endif;
