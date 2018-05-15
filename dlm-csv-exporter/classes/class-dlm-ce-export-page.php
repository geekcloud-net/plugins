<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class DLM_CE_Export_Page {


	/**
	 * Register Hooks
	 */
	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'add_submenu_page' ), 13 );
	}

	/**
	 * Add admin page
	 */
	public function add_submenu_page() {
		// Settings page
		add_submenu_page( 'edit.php?post_type=dlm_download', __( 'Export to CSV', 'dlm-csv-exporter' ), __( 'Export to CSV', 'dlm-csv-exporter' ), 'manage_options', 'dlm-csv-exporter', array(
			$this,
			'export_page'
		) );
	}

	/**
	 * The actual export page
	 */
	public function export_page() {

		// Title
		echo '<h2>' . __( 'Download Monitor - Export to CSV', 'dlm-csv-exporter' ) . '</h2>' . PHP_EOL;

		// Welcome screen
		echo "<p> " . sprintf( __( "We've found %s to be exported! Click the <strong>Start Export</strong> button below to start.", 'dlm-csv-exporter' ), '<strong>' . wp_count_posts( 'dlm_download' )->publish . ' downloads</strong>' ) . "</p>" . PHP_EOL;
		echo '<a href="' . admin_url( 'edit.php?post_type=dlm_download&page=dlm-csv-exporter&dlm-ce-do-export=1&nonce=' . wp_create_nonce( 'dlm-csv-export-super-secret' ) ) . '" class="button button-primary">Start Export</a>' . PHP_EOL;

	}

}