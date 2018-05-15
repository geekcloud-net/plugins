<?php

/**
* Plugin Name: Woo Facturante
* Description: Woo Facturante integrates WooCommerce with Facturante 
* Version: 0.1.53
* Author: Fuego Yámana
* Author URI: https://fuegoyamana.com
* Text Domain: woo-facturante
* Domain Path: /languages/
*
* @author Fuego Yámana
* @package Woo Facturante
* @version 0.1.3
*/
/*  Copyright 2016  

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

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-facturante.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.0.1
 */
function run_woo_facturante() {

	$plugin = new Woo_Facturante();
	$plugin->run();

}
run_woo_facturante();

?>