<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class DLM_CI_Import_Manager {

	/**
	 * Attach terms of given taxonomy to given download id
	 *
	 * @param $terms
	 * @param $taxonomy
	 * @param $download_id
	 *
	 * @return bool
	 */
	private function attach_terms_to_download( $terms, $taxonomy, $download_id ) {

		if ( is_array( $terms ) && count( $terms ) > 0 ) {

			// Array to contain all term ids
			$term_ids = array();

			// Loop through tags
			foreach ( $terms as $term ) {

				// Get term
				$wp_term = term_exists( $term, $taxonomy );

				// Check if term exists
				if ( 0 == $wp_term ) {

					// Term doesn't exist, create it
					$wp_term = wp_insert_term( $term, $taxonomy );

				}

				// Check if not WP ERROR
				if ( ! is_wp_error( $wp_term ) ) {
					// Add term id to array
					$term_ids[] = intval( $wp_term['term_id'] );
				}


			}

			// Attach terms to download
			wp_set_object_terms( $download_id, $term_ids, $taxonomy, false );

		}

		return true;

	}

	/**
	 * Import downloads
	 *
	 * @param $downloads
	 *
	 * @return bool
	 */
	public function import_downloads( $downloads ) {

		if ( count( $downloads ) > 0 ) {

			// Current User
			$current_user = wp_get_current_user();

			// Transient Manager so we can remove cache after download import
			$transient_manager = new DLM_Transient_Manager();

			/** @var DLM_Download_Repository $download_repository */
			$download_repository = download_monitor()->service( 'download_repository' );

			/** @var DLM_Version_Repository $version_repository */
			$version_repository = download_monitor()->service( 'version_repository' );

			/** @var DLM_CI_Import_Download $import_download */
			foreach ( $downloads as $import_download ) {

				/**
				 * @var DLM_CI_Import_Download $download
				 */

				// check if we're updating or adding a new download
				if ( $import_download->get_id() > 0 ) {

					try {
						$new_download = $download_repository->retrieve_single( $import_download->get_id() );
					} catch ( Exception $exception ) {
						// no download with this ID found, creating a new one
						$new_download = new DLM_Download();
					}

				} else {
					$new_download = new DLM_Download();
				}

				// set basic data
				$new_download->set_status( 'publish' );
				$new_download->set_title( $import_download->get_title() );
				$new_download->set_description( $import_download->get_content() );
				$new_download->set_excerpt( $import_download->get_short_description() );

				// set author, is filterable via dlm_csv_importer_author_id
				$new_download->set_author( apply_filters( 'dlm_csv_importer_download_author_id', $current_user->ID, $new_download->get_id() ) );

				// Featured
				$new_download->set_featured( $import_download->is_featured() );

				// Members Only
				$new_download->set_members_only( $import_download->is_members_only() );

				// Redirect
				$new_download->set_redirect_only( $import_download->is_redirect() );

				// Persist new download
				if ( ! $download_repository->persist( $new_download ) ) {
					error_log( sprintf( "Error on download import %s", $import_download->get_title() ), 0 );
					continue;
				}

				// Custom meta
				if ( count( $import_download->get_meta() ) > 0 ) {
					foreach ( $import_download->get_meta() as $meta_key => $meta_value ) {
						// add custom meta
						add_post_meta( $new_download->get_id(), $meta_key, $meta_value );
					}
				}

				// Tags
				$this->attach_terms_to_download( $import_download->get_tags(), 'dlm_download_tag', $new_download->get_id() );

				// Categories
				$this->attach_terms_to_download( $import_download->get_categories(), 'dlm_download_category', $new_download->get_id() );

				$total_download_count = 0;

				// Create versions
				if ( count( $import_download->get_versions() ) > 0 ) {

					// get current versions
					$current_versions = $new_download->get_versions();

					// check if there are current versions
					if ( count( $current_versions ) > 0 ) {

						// loop
						foreach ( $current_versions as $current_version ) {

							// delete current version
							wp_delete_post( $current_version->ID, true );
						}
					}

					// Loop
					/** @var DLM_CI_Import_Version $version */
					foreach ( $import_download->get_versions() as $version ) {

						// create version object
						$new_version = new DLM_Download_Version();
						$new_version->set_author( apply_filters( 'dlm_csv_importer_version_author_id', $current_user->ID, $version, $new_download->get_id() ) );
						$new_version->set_download_id( $new_download->get_id() );
						$new_version->set_menu_order( $version->get_order() );
						$new_version->set_date( $version->get_date() );
						$new_version->set_version( $version->get_version() );
						$new_version->set_download_count( absint( $version->get_download_count() ) );
						$new_version->set_mirrors( explode( '|', $version->get_url() ) );

						// persist version
						$version_repository->persist( $new_version );

						// up total download count with version download count
						$total_download_count += absint( $version->get_download_count() );
					}
				}

				// set total download count
				update_post_meta( $new_download->get_id(), '_download_count', $total_download_count );

				// remove old transient
				$transient_manager->clear_versions_transient( $new_download->get_id() );

			}
		}

		return true;

	}

}