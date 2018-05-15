<?php
/*
	Plugin Name: Download Monitor - CSV Importer
	Plugin URI: https://www.download-monitor.com/extensions/csv-importer/
	Description: Mass import up to thousands of Downloads into Download Monitor with the CSV Importer.
	Version: 4.0.0
	Author: Never5
	Author URI: http://www.never5.com/
	License: GPL v3

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class DLM_CSV_Importer {

	const VERSION = '4.0.0';

	/**
	 * Get the plugin file
	 *
	 * @static
	 *
	 * @return String
	 */
	public static function get_plugin_file() {
		return __FILE__;
	}

	/**
	 * DLM_CSV_Importer constructor.
	 */
	public function __construct() {

		// Register importer in admin
		if ( is_admin() ) {

			$importer = new DLM_CI_WP_Importer();
			add_action( 'admin_init', array( $importer, 'register_importer' ) );

			// enqueue scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// add admin css
			add_action( 'admin_head', array( $this, 'admin_css' ) );

		}

		// Register Extension
		add_filter( 'dlm_extensions', array( $this, 'register_extension' ) );
	}

	/**
	 * Register this extension
	 *
	 * @param array $extensions
	 *
	 * @return array $extensions
	 */
	public function register_extension( $extensions ) {

		$extensions[] = array(
			'file'    => 'dlm-csv-importer',
			'version' => self::VERSION,
			'name'    => 'CSV Importer'
		);

		return $extensions;
	}

	/**
	 * Return if current page is importer page step 2
	 *
	 * @return bool
	 */
	private function is_importer_page() {
		global $pagenow;

		return ( $pagenow == 'admin.php' && isset( $_GET['import'] ) && $_GET['import'] === 'dlm_csv' && isset( $_GET['step'] ) && '2' == $_GET['step'] );
	}

	/**
	 * Include JS file on importer page
	 */
	public function enqueue_scripts() {

		// Post screen
		if ( $this->is_importer_page() ) {
			wp_enqueue_script(
				'dlm_ci_js',
				plugins_url( '/assets/js/dlm-csv-importer.js', self::get_plugin_file() ),
				array( 'jquery' ),
				self::VERSION
			);
		}
	}

	/**
	 * Add some CSS to importer page
	 */
	public function admin_css() {

		if ( $this->is_importer_page() ) {
			?>
			<style type="text/css">
				.dlm-ci-select-map-to {
					width: 100%;
				}
				.dlm-ci-select-map-to-meta-active, .dlm-ci-text-meta {
					width: 47%;
					float: left;
					-moz-box-sizing: border-box;
					-webkit-box-sizing: border-box;
					box-sizing: border-box;
				}
				.dlm-ci-text-meta {
					float: right;
				}
			</style>
		<?php
		}

	}

}

require_once dirname( __FILE__ ) . '/vendor/autoload_52.php';

function __dlm_csv_importer() {
	new DLM_CSV_Importer();
}

add_action( 'plugins_loaded', '__dlm_csv_importer' );