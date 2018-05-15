<?php
/**
 * Install script to insert default data.
 */


// Installation procedures
add_action( 'appthemes_first_run', 'qc_install_settings' );
add_action( 'appthemes_first_run', 'qc_install_widgets' );
add_action( 'appthemes_first_run', 'qc_install_content' );


/**
 * Initialize settings
 */
function qc_install_settings() {

	// if fresh install
	if ( get_option( 'qc_version' ) == false ) {
		// set blog and tickets pages
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', QC_Ticket_Home::get_id() );
		update_option( 'page_for_posts', QC_Blog_Archive::get_id() );
	}

}


/**
 * Add default content if no content exists
 */
function qc_install_content() {
	global $qc_options;

	// Create default states
	if ( ! _tax_has_terms( 'ticket_status' ) ) {
		$status_term = wp_insert_term( __( 'New', APP_TD ), 'ticket_status' );
		$qc_options->ticket_status_new = $status_term['term_id'];

		$r = wp_insert_term( __( 'Closed', APP_TD ), 'ticket_status' );
		$qc_options->ticket_status_closed = $r['term_id'];
	}

	// Create default priorities
	if ( ! _tax_has_terms( 'ticket_priority' ) ) {
		$default_priorities = array(
			__( 'Low', APP_TD ),
			__( 'Medium', APP_TD ),
			__( 'High', APP_TD ),
		);

		foreach ( $default_priorities as $priority ) {
			$priority_term = wp_insert_term( $priority, 'ticket_priority' );
		}
	}

	// Create default milestones
	if ( ! _tax_has_terms( 'ticket_milestone' ) ) {
		$default_milestones = array(
			__( 'Awaiting Review', APP_TD ),
			__( 'Future Release', APP_TD ),
			'1.0',
		);

		foreach ( $default_milestones as $milestone ) {
			$milestone_term = wp_insert_term( $milestone, 'ticket_milestone' );
		}
	}

	// Create default categories
	if ( ! _tax_has_terms( 'ticket_category' ) && ! get_option( 'qc_version' ) ) {
		$default_categories = array(
			__( 'Bug', APP_TD ),
			__( 'Enhancement', APP_TD ),
			__( 'Feature', APP_TD ),
		);

		foreach ( $default_categories as $category ) {
			$category_term = wp_insert_term( $category, 'ticket_category' );
		}
	}

	// Create default ticket
	$tickets = get_posts( array( 'posts_per_page' => 1, 'post_type' => QC_TICKET_PTYPE, 'no_found_rows' => true ) );

	if ( empty( $tickets ) ) {

		$description = html( 'p', __( 'This is your first Quality Control ticket. It is a placeholder ticket just so you can see how it works. Delete this before launching your new issue tracking site.', APP_TD ) );

		$default_ticket = array(
			'post_title' => 'My First Ticket',
			'post_name' => 'my-first-ticket',
			'post_content' => $description,
			'post_status' => 'publish',
			'post_type' => QC_TICKET_PTYPE,
			'post_author' => 1,
		);

		$ticket_id = wp_insert_post( $default_ticket );

		// Assign some taxonomies
		if ( isset( $status_term ) && ! is_wp_error( $status_term ) ) {
			wp_set_post_terms( $ticket_id, array( (int) $status_term['term_id'] ), 'ticket_status', false );
		}

		if ( isset( $priority_term ) && ! is_wp_error( $priority_term ) ) {
			wp_set_post_terms( $ticket_id, array( (int) $priority_term['term_id'] ), 'ticket_priority', false );
		}

		if ( isset( $milestone_term ) && ! is_wp_error( $milestone_term ) ) {
			wp_set_post_terms( $ticket_id, array( (int) $milestone_term['term_id'] ), 'ticket_milestone', false );
		}

		if ( isset( $category_term ) && ! is_wp_error( $category_term ) ) {
			wp_set_post_terms( $ticket_id, array( (int) $category_term['term_id'] ), 'ticket_category', false );
		}

	}

}


/**
 * Initialize widgets
 */
function qc_install_widgets() {
	list( $args ) = get_theme_support( 'app-versions' );

	if ( ! get_option( $args['option_key'] ) && $args['current_version'] == get_transient( APP_UPDATE_TRANSIENT ) ) {

		$sidebars_widgets = array(
			// Primary Widget Area
			'primary-widget-area' => array(
				'search' => array(),
				'qc_project_team' => array(
					'title' => __( 'Project Team', APP_TD ),
				),
				'recent-tickets' => array(
					'title' => __( 'Recent Tickets', APP_TD ),
					'number' => 5,
					'show_date' => 1,
				),
				'cat-tax' => array(
					'title' => __( 'Milestones', APP_TD ),
					'taxonomy' => 'ticket_milestone',
					'show_rss' => 1,
					'show_count' => 1,
				),
				'recent-tickets-updates' => array(
					'title' => __( 'Recent Tickets Updates', APP_TD ),
					'number' => 5,
				),
			),
			// Blog
			'sidebar_blog' => array(
				'recent-posts' => array(
					'title' => __( 'Recent Posts', APP_TD ),
					'number' => 5,
					'show_date' => 1,
				),
				'recent-comments' => array(
					'title' => __( 'Recent Comments', APP_TD ),
					'number' => 5,
				),
				'categories' => array (
					'title' => __( 'Blog Categories', APP_TD ),
					'count' => 1,
				),
				'tag_cloud' => array (
					'title' => __( 'Tags', APP_TD ),
					'taxonomy' => 'post_tag',
				),
			),
			// Page
			'sidebar_page' => array(
				'search' => array(),
				'cat-tax' => array(
					'title' => __( 'Categories', APP_TD ),
					'taxonomy' => 'ticket_category',
					'show_rss' => 1,
					'show_count' => 1,
				),
				'tag_cloud' => array (
					'title' => __( 'Tags', APP_TD ),
					'taxonomy' => 'ticket_tag',
				),
			),
		);

		appthemes_install_widgets( $sidebars_widgets );

	}

}

