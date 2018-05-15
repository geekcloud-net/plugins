<?php

/**
 * @since             1.0
 *
 * @wordpress-plugin
 * Plugin Name:       WP Google Translate
 * Plugin URI:        http://mythemeshop.com/plugins/wp-google-translate/
 * Description:       WP Google Translate is the best plugin available for translating your blog into over 80 different languages, without the hassles of manual translation or confusing integration.
 * Version:           1.0.7
 * Author:            MyThemeShop
 * Author URI:        http://mythemeshop.com/
 * Text Domain:       wp-google-translate
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-google-translate.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_google_translate() {

	$plugin = new WP_Google_Translate();
	$plugin->run();

}
run_wp_google_translate();
