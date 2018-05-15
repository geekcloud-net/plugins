<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class DLM_CI_CSV_Handler {

	const TRA_IMPORT = 'dlm_ci_current_import';

	/**
	 * Move uploaded file to media lib
	 *
	 * @param $file
	 *
	 * @return array
	 */
	public function move_file_to_media_lib( $file ) {

		// Check if functions exists
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		return wp_handle_upload( $file, array( 'test_form' => false ) );
	}

	/**
	 * Get the current import data
	 *
	 * @return array
	 */
	public function get_current_import() {
		// Get transient
		$data = get_transient( self::TRA_IMPORT );

		// Data must be array
		if ( ! is_array( $data ) ) {
			$data = array();
		}

		// Return data
		return $data;
	}

	/**
	 * Set current import data
	 *
	 * @param $data
	 */
	public function set_current_import( $data ) {
		set_transient( self::TRA_IMPORT, $data, DAY_IN_SECONDS );
	}

	/**
	 * Unset the current import data
	 */
	public function unset_current_import() {
		delete_transient( self::TRA_IMPORT );
	}

}