<?php
/**
 * Plugin Name: YITH WooCommerce Questions and Answers Premium
 * Plugin URI: http://yithemes.com/themes/plugins/yith-woocommerce-question-and-answer/
 * Description: YITH WooCommerce Questions And Answers offers a rapid way to manage dynamic discussions about the products of your shop.
 * Author: YITHEMES
 * Text Domain: yith-woocommerce-questions-and-answers
 * Version: 1.2.1
 * Author URI: http://yithemes.com/
 *
 * @author  Yithemes
 * @package YITH WooCommerce Questions and Answers Premium
 * @version 1.2.1
 * WC requires at least: 3.0.0
 * WC tested up to: 3.3.x
 */

/*  Copyright 2013-2018  Your Inspiration Themes  (email : plugins@yithemes.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}



/**
 * Check if a free version is currently active and try disabling before activating this one
 */
if ( ! function_exists( 'yit_deactive_free_version' ) ) {
	require_once 'plugin-fw/yit-deactive-plugin.php';
}
yit_deactive_free_version( 'YITH_YWQA_FREE_INIT', plugin_basename( __FILE__ ) );

if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );

//region    ****    Define constants  ****
defined( 'YITH_YWQA_INIT' ) || define( 'YITH_YWQA_INIT', plugin_basename( __FILE__ ) );
defined( 'YITH_YWQA_PREMIUM' ) || define( 'YITH_YWQA_PREMIUM', '1' );
defined( 'YITH_YWQA_SLUG' ) || define( 'YITH_YWQA_SLUG', 'yith-woocommerce-questions-and-answers' );
defined( 'YITH_YWQA_SECRET_KEY' ) || define( 'YITH_YWQA_SECRET_KEY', 'L7sOoHcJbJPedRBgfTd7' );
defined( 'YITH_YWQA_DB_VERSION' ) || define( 'YITH_YWQA_DB_VERSION', '1.0.1' );
defined( 'YITH_YWQA_VERSION' ) || define( 'YITH_YWQA_VERSION', '1.2.1' );
defined( 'YITH_YWQA_FILE' ) || define( 'YITH_YWQA_FILE', __FILE__ );
defined( 'YITH_YWQA_DIR' ) || define( 'YITH_YWQA_DIR', plugin_dir_path( __FILE__ ) );
defined( 'YITH_YWQA_URL' ) || define( 'YITH_YWQA_URL', plugins_url( '/', __FILE__ ) );
defined( 'YITH_YWQA_ASSETS_URL' ) || define( 'YITH_YWQA_ASSETS_URL', YITH_YWQA_URL . 'assets' );
defined( 'YITH_YWQA_ASSETS_DIR' ) || define( 'YITH_YWQA_ASSETS_DIR', YITH_YWQA_DIR . 'assets' );
defined( 'YITH_YWQA_TEMPLATES_DIR' ) || define( 'YITH_YWQA_TEMPLATES_DIR', YITH_YWQA_DIR . 'templates/' );
defined( 'YITH_YWQA_TEMPLATES_EMAIL_DIR' ) || define( 'YITH_YWQA_TEMPLATES_EMAIL_DIR', YITH_YWQA_TEMPLATES_DIR . 'yith-questions-and-answers/' );
defined( 'YITH_YWQA_ASSETS_IMAGES_URL' ) || define( 'YITH_YWQA_ASSETS_IMAGES_URL', YITH_YWQA_ASSETS_URL . '/images/' );
defined( 'YITH_YWQA_ASSETS_IMAGES_DIR' ) || define( 'YITH_YWQA_ASSETS_IMAGES_DIR', YITH_YWQA_ASSETS_DIR . '/images/' );
defined( 'YITH_YWQA_LIB_DIR' ) || define( 'YITH_YWQA_LIB_DIR', YITH_YWQA_DIR . 'lib/' );

//endregion

/* Plugin Framework Version Check */
if ( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( YITH_YWQA_DIR . 'plugin-fw/init.php' ) ) {
	require_once( YITH_YWQA_DIR . 'plugin-fw/init.php' );
}
yit_maybe_plugin_fw_loader( YITH_YWQA_DIR );

require_once( YITH_YWQA_DIR . 'functions.php' );

function yith_ywqa_premium_init() {

	/* Load YWQA text domain */
	load_plugin_textdomain( 'yith-woocommerce-questions-and-answers', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	// Load required classes and functions
	require_once( YITH_YWQA_LIB_DIR . 'class.yith-woocommerce-question-answer.php' );
	require_once( YITH_YWQA_LIB_DIR . 'class.yith-woocommerce-question-answer-premium.php' );
	require_once( YITH_YWQA_LIB_DIR . 'class.ywqa-plugin-fw-loader.php' );
	require_once( YITH_YWQA_LIB_DIR . 'class.ywqa-discussion.php' );
	require_once( YITH_YWQA_LIB_DIR . 'class.ywqa-question.php' );
	require_once( YITH_YWQA_LIB_DIR . 'class.ywqa-answer.php' );
	require_once( YITH_YWQA_LIB_DIR . 'recaptcha/src/autoload.php' );


	global $ywqa;
	$ywqa = YITH_YWQA();
	YITH_WooCommerce_Question_Answer_Premium::update();
}


add_action( 'yith_ywqa_premium_init', 'yith_ywqa_premium_init' );

