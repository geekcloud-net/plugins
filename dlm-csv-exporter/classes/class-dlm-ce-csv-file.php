<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class DLM_CE_CSV_File {

	/** @var array $headers */
	private $headers = array();

	/** @var array $rows */
	private $rows = array();

	/**
	 * Constructor
	 *
	 * @param $data
	 */
	public function __construct( $data ) {

		// Set headers
		$this->headers = $data['header'];

		// Set rows
		$this->rows = $data['data'];

	}

	/**
	 * Generate the CSV row
	 *
	 * @param array $row
	 *
	 * @return string
	 */
	private function build_csv_row( $row ) {

		// Base csv row
		$cr = '';

		// Check & Loop
		if ( count( $this->headers ) > 0 ) {
			foreach ( $this->headers as $header ) {

				// Check if this col is set in row
				if ( isset( $row[ $header ] ) ) {

					// The column
					$col = $row[ $header ];

					// Check if the column contains double quotes
					if ( false !== strpos( $col, '"' ) ) {
						// Replace double quotes with single quotes
						$col = str_ireplace( '"', "'", $col );
					}

					// Check if the column contains a comma
					if ( ! empty( $col ) && ( "description" == $header || "excerpt" == $header || false !== strpos( $col, ',' ) ) ) {
						// Wrap data in "
						$col = '"' . $col . '"';
					}

					// Add column to row
					$cr .= $col;
				}

				// End col with comma
				$cr .= ',';

			}
		}

		// Remove last comma \o/
		$cr = substr( $cr, 0, - 1 );

		// Return csv row
		return $cr . PHP_EOL;
	}

	/**
	 * Generate the CSV string
	 *
	 * @return String
	 */
	public function get_csv_string() {

		// Base
		$csv_string = '';

		// Headers
		$csv_string .= implode( ',', $this->headers ) . PHP_EOL;

		// Add data
		if ( count( $this->rows ) > 0 ) {
			foreach ( $this->rows as $row ) {
				$csv_string .= $this->build_csv_row( $row );
			}
		}

		// Return
		return $csv_string;
	}

}