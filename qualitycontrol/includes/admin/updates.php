<?php
/**
 * Update script for version changes
 */


/**
 * Check version and run updating functions
 */
function qc_init_updates() {

	list( $args ) = get_theme_support( 'app-versions' );
	$previous_version = get_option( $args['option_key'] );

	if ( ! $previous_version ) {
		return;
	}

	// upgrade to version 0.8
	if ( version_compare( $previous_version, '0.8', '<' ) ) {
		qc_version_0_8_update();
	}

}
add_action( 'appthemes_first_run', 'qc_init_updates' );


/**
 * Execute changes made in Quality Control 0.8.
 *
 * @since 0.8
 */
function qc_version_0_8_update() {
	global $qc_options, $wpdb;

	// move repository settings if set
	if ( ! empty( $qc_options->repository['type'] ) && ! empty( $qc_options->repository['details'] ) ) {
		$type = $qc_options->repository['type'];
		$details = $qc_options->repository['details'];
		$old_options = array(
			'type' => $type,
			'details' => array( $type => $details ),
		);
		$qc_options->repository = $old_options;
	}

	// set blog and tickets pages
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', QC_Ticket_Home::get_id() );
	update_option( 'page_for_posts', QC_Blog_Archive::get_id() );

	// collect posts info about terms
	$args = array(
		'post_type' => 'post',
		'posts_per_page' => -1,
		'fields' => 'ids',
		'no_found_rows' => true,
	);
	$posts = new WP_Query( $args );

	$posts_info = array();
	if ( ! empty( $posts->posts ) && is_array( $posts->posts ) ) {
		foreach ( $posts->posts as $post_id ) {
			$posts_info[ $post_id ]['category'] = get_the_terms( $post_id, 'category' );
			$posts_info[ $post_id ]['post_tag'] = get_the_terms( $post_id, 'post_tag' );
		}
	}

	// change post categories into ticket categories
	$categories = get_terms( 'category', array( 'hide_empty' => false, 'fields' => 'ids' ) );
	if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
		foreach ( $categories as $category_id ) {
			$wpdb->update( $wpdb->term_taxonomy, array( 'taxonomy' => 'ticket_category' ), array( 'term_id' => $category_id ) );
		}
	}

	// change post tags into ticket tags
	$tags = get_terms( 'post_tag', array( 'hide_empty' => false, 'fields' => 'ids' ) );
	if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
		foreach ( $tags as $tag_id ) {
			$wpdb->update( $wpdb->term_taxonomy, array( 'taxonomy' => 'ticket_tag' ), array( 'term_id' => $tag_id ) );
		}
	}

	foreach ( $posts_info as $post_id => $tax_array ) {
		foreach ( $tax_array as $taxonomy => $terms_array ) {
			if ( ! $terms_array || is_wp_error( $terms_array ) ) {
				continue;
			}

			$post_terms = array();
			foreach ( $terms_array as $term ) {
				$t = appthemes_maybe_insert_term( $term->name, $taxonomy );
				if ( ! is_wp_error( $t ) ) {
					$post_terms[] = (int) $t['term_id'];
				}
			}

			if ( ! empty( $post_terms ) ) {
				wp_set_object_terms( $post_id, $post_terms, $taxonomy, false );
			}
		}
	}

	// update default post category option
	$categories = get_terms( 'category', array( 'hide_empty' => false ) );
	if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
		$category_id = (int) $categories[0]->term_id;
	} else {
		$t = appthemes_maybe_insert_term( 'Uncategorized', 'category' );
		$category_id = (int) $t['term_id'];
	}
	update_option( 'default_category', $category_id );
}

