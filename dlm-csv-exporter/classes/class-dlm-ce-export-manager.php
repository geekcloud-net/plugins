<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class DLM_CE_Export_Manager {

	/**
	 * Get terms from $taxanomy and format them in csv string
	 *
	 * @param $download_id
	 * @param $taxonomy
	 *
	 * @return String
	 */
	private function get_terms_string( $download_id, $taxonomy ) {

		// Get terms
		$db_terms = get_the_terms( $download_id, $taxonomy );

		// Vars
		$terms        = array();
		$terms_string = '';

		// Check & Loop
		if ( false !== $db_terms && count( $db_terms ) > 0 ) {
			foreach ( $db_terms as $db_term ) {
				$terms[] = $db_term->name;
			}

			// Implode to string
			$terms_string = implode( '|', $terms );
		}

		// \o/
		return $terms_string;
	}

	/**
	 * Get download array
	 *
	 * @return array
	 */
	public function get_csv_data() {
		// Build array
		$csv_data = array();

		/**
		 * Allow the addition of extra export fields.
		 *
		 * The following filters are available:
		 *
		 * dlm_ce_extra_fields_download         - Add extra Download fields
		 * dlm_ce_extra_fields_download_meta    - Add extra Download meta fields
		 * dlm_ce_extra_fields_version          - Add extra Version fields
		 * dlm_ce_extra_fields_version_meta     - Add extra Version meta fields
		 */
		$extra_fields = array(
			'download'      => apply_filters( 'dlm_ce_extra_fields_download', array() ),
			'download_meta' => apply_filters( 'dlm_ce_extra_fields_download_meta', array() ),
			'version'       => apply_filters( 'dlm_ce_extra_fields_version', array() ),
			'version_meta'  => apply_filters( 'dlm_ce_extra_fields_version_meta', array() ),
		);

		// Add CSV header
		$csv_data['header'] = array(
			'type',
			'download_id',
			'title',
			'description',
			'excerpt',
			'categories',
			'tags',
			'featured',
			'members_only',
			'redirect',
			'version',
			'download_count',
			'file_date',
			'file_urls',
			'version_order'
		);

		// Add extra fields to header
		if ( count( $extra_fields ) > 0 ) {
			foreach ( $extra_fields as $extra_field_type ) {
				foreach ( $extra_field_type as $extra_field ) {
					$csv_data['header'][] = $extra_field;
				}
			}
		}

		// Get all downloads
		$downloads = download_monitor()->service( 'download_repository' )->retrieve();

		// Check
		if ( count( $downloads ) > 0 ) {

			// Loop
			/** @var DLM_Download $download_object */
			foreach ( $downloads as $download_object ) {

				// Build CSV row
				$csv_row = array(
					'type'        => 'download',
					'download_id' => $download_object->get_id(),
					'title'       => $download_object->get_title(),
					'description' => $download_object->get_description(),
					'excerpt'     => $download_object->get_excerpt(),
				);

				// Featured
				$csv_row['featured'] = ( $download_object->is_featured() ? 1 : 0 );

				// Members only
				$csv_row['members_only'] = ( $download_object->is_members_only() ? 1 : 0 );

				// Redirect
				$csv_row['redirect'] = ( $download_object->is_redirect_only() ? 1 : 0 );

				// Categories
				$csv_row['categories'] = $this->get_terms_string( $download_object->get_id(), 'dlm_download_category' );

				// Tags
				$csv_row['tags'] = $this->get_terms_string( $download_object->get_id(), 'dlm_download_tag' );

				// Add extra download fields
				if ( count( $extra_fields['download'] ) > 0 ) {
					foreach ( $extra_fields['download'] as $extra_field_key => $extra_field_label ) {
						// Check if the field exists in the Download object
						if ( isset( $download_object->$extra_field_key ) ) {
							$csv_row[ $extra_field_label ] = $download_object->$extra_field_key;
						}
					}
				}

				// Add extra download meta fields
				if ( count( $extra_fields['download_meta'] ) > 0 ) {
					foreach ( $extra_fields['download_meta'] as $extra_field_key => $extra_field_label ) {
						// Get the download meta
						$extra_field_value = get_post_meta( $download_object->get_id(), $extra_field_key, true );

						// Check if the field exists
						if ( '' !== $extra_field_value ) {
							$csv_row[ $extra_field_label ] = $extra_field_value;
						}
					}
				}

				// Add download row to array
				$csv_data['data'][] = $csv_row;

				// Get versions
				$versions = $download_object->get_versions();

				// Check && Loop
				if ( count( $versions ) > 0 ) {
					/** @var DLM_Download_Version $version */
					foreach ( $versions as $version ) {

						// The version post
						$version_post = get_post( $version->get_id() );

						// The version row
						$version_row = array(
							'type'           => 'version',
							'version'        => $version->get_version(),
							'download_count' => $version->get_download_count(),
							'file_date'      => $version_post->post_date,
							'file_urls'      => implode( '|', $version->get_mirrors() ),
							'version_order'  => $version_post->menu_order
						);

						// Add extra version fields
						if ( count( $extra_fields['version'] ) > 0 ) {
							foreach ( $extra_fields['version'] as $extra_field_key => $extra_field_label ) {
								// Check if the field exists in the Version object
								if ( isset( $version->$extra_field_key ) ) {
									$version_row[ $extra_field_label ] = $version->$extra_field_key;
								}
							}
						}

						// Add extra version meta fields
						if ( count( $extra_fields['version_meta'] ) > 0 ) {
							foreach ( $extra_fields['version_meta'] as $extra_field_key => $extra_field_label ) {
								// Get the Version meta
								$extra_field_value = get_post_meta( $version->get_id(), $extra_field_key, true );

								// Check if the field exists
								if ( '' !== $extra_field_value ) {
									$version_row[ $extra_field_label ] = $extra_field_value;
								}
							}
						}

						// Add version to CSV data
						$csv_data['data'][] = $version_row;
					}
				}

			}
		}

		//  \o/
		return $csv_data;

	}

	/**
	 * Output the CSV headers
	 */
	public function output_headers() {
		header( "Content-type: text/csv" );
		header( "Content-Disposition: attachment; filename=download-monitor-export.csv" );
		header( "Pragma: no-cache" );
		header( "Expires: 0" );
	}

	/**
	 * Catch export request
	 */
	public function catch_export_request() {
		if ( isset( $_GET['dlm-ce-do-export'] ) ) {

			// Check user rights
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'Aborted export request, insufficient user permission to export download data.' );
			}

			// Check nonce
			if ( ! wp_verify_nonce( $_GET['nonce'], 'dlm-csv-export-super-secret' ) ) {
				wp_die( 'Aborted export request, nonce check failed.' );
			}

			// Export Manager
			$export_manager = new DLM_CE_Export_Manager();

			// CSV Data
			$csv_data = $export_manager->get_csv_data();

			// The CSV File
			$csv_file = new DLM_CE_CSV_File( $csv_data );


			// Get the CSV string
			$csv_string = $csv_file->get_csv_string();

			// Check
			if ( '' !== $csv_data ) {

				// Ouput the CSV headers
				$export_manager->output_headers();

				// Output the string
				echo $csv_string;

				//
				exit;

			} else {
				wp_die( 'Download Monitor export failed, no data in CSV string found.' );
			}

		}
	}

}