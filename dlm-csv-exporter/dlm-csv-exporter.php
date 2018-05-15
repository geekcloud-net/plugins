<?php
/*
	Plugin Name: Download Monitor - CSV Exporter
	Plugin URI: https://www.download-monitor.com/extensions/csv-exporter/
	Description: Easily export all your downloads to a CSV file.
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

class DLM_CSV_Exporter {

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

	public function __construct() {

		// Only in admin
		if ( is_admin() ) {

			// The Export Page
			$export_page = new DLM_CE_Export_Page();
			$export_page->register_hooks();

			// Catch the export request
			$export_manager = new DLM_CE_Export_Manager();
			add_action( 'admin_init', array( $export_manager, 'catch_export_request' ) );
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
			'file'    => 'dlm-csv-exporter',
			'version' => self::VERSION,
			'name'    => 'CSV Exporter'
		);

		return $extensions;
	}

}

require_once dirname( __FILE__ ) . '/vendor/autoload_52.php';

function __dlm_csv_exporter() {
	new DLM_CSV_Exporter();
}

add_action( 'plugins_loaded', '__dlm_csv_exporter' );