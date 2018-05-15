<?php

/**
 * Get the term associated to a ticket
 *
 * @since Quality Control 0.1
 * @uses get_the_terms
 */
function qc_taxonomy( $taxonomy, $format = 'term_id', $post_id = null ) {
	if ( empty( $post_id ) ) {
		$post_id = get_the_ID();
	}

	$terms = get_the_terms( $post_id, $taxonomy );

	if ( empty( $terms ) ) {
		return false;
	}

	$term = reset( $terms );

	if ( $format ) {
		return $term->$format;
	}

	return $term;
}

/**
 * Show ticket status label
 */
function qc_status_label() {
	if ( $ticket_status = qc_taxonomy( 'ticket_status', false ) ) {
		echo html( 'a', array(
			'href' => get_term_link( $ticket_status ),
			'class' => 'ticket-status ' . $ticket_status->slug,
		), $ticket_status->name );
	}
}

/**
 * Does the query produce more than 1 page?
 *
 * @return boolean If pagination needs to be shown or not.
 */
function qc_show_pagination() {
	$qc_query = qc_get_query();

	return ( $qc_query->max_num_pages > 1 );
}

/**
 * Conditional tag to check if current page is the tickets home
 *
 * @return boolean
 */
function qc_is_home() {
	$qc_ticket_home = appthemes_get_instance( 'QC_Ticket_Home' );

	return (bool) $qc_ticket_home->condition();
}

/**
 * Returns a URL to the tickets home
 *
 * @return string
 */
function qc_home_url() {
	return get_permalink( QC_Ticket_Home::get_id() );
}

/**
 * Create a comma separated flat list of the current
 * tags.
 *
 * @since Quality Control 0.1.5
 * @uses get_the_tags
 */
function qc_get_ticket_tags( $post_id = null, $separator = ', ', $taxonomy = 'ticket_tag' ) {
	global $post;

	if ( null == $post_id ) {
		$post_id = $post->ID;
	}

	if ( ! $post_id ) {
		return false;
	}

	$tags = wp_get_post_terms( $post_id, $taxonomy, array() );

	if ( ! $tags ) {
		return false;
	}

	if ( is_wp_error( $tags ) ) {
		return $tags;
	}

	foreach ( $tags as $tag ) {
		$tag_names[] = $tag->name;
	}

	$tags_to_edit = join( ', ', $tag_names );
	$tags_to_edit = esc_attr( $tags_to_edit );
	$tags_to_edit = apply_filters( 'terms_to_edit', $tags_to_edit, $taxonomy );

	return $tags_to_edit;
}

function qc_can_create_ticket() {
	return current_user_can( 'edit_posts' );
}

function qc_can_view_all_tickets() {
	global $qc_options;

	$can_view = ( 'protected' != $qc_options->assigned_perms || current_user_can( 'edit_others_posts' ) );

	return apply_filters( 'qc_can_view_all_tickets', $can_view );
}

/**
 * Checks to see if the current user has permission to view the ticket.
 *
 * @param int $ticket_id
 *
 * @return bool
 */
function qc_can_view_ticket( $ticket_id ) {
	if ( qc_can_view_all_tickets() ) {
		return true;
	}

	if ( qc_can_edit_ticket( $ticket_id ) ) {
		return true;
	}

	return false;
}

/**
 * Go through a series of checks to see if the current
 * user has permission to update the ticket.
 *
 * @param int $ticket_id
 *
 * @return false if no other conditions are true.
 */
function qc_can_edit_ticket( $ticket_id ) {
	global $qc_options;

	if ( ! is_user_logged_in() ) {
		return false;
	}

	$ticket = get_post( $ticket_id );
	if ( ! $ticket || $ticket->post_type != QC_TICKET_PTYPE ) {
		return false;
	}

	if ( current_user_is_assigned_to_ticket( $ticket_id ) ) {
		return true;
	}

	if ( 'read-write' == $qc_options->assigned_perms || current_user_can( 'edit_post', $ticket_id ) ) {
		return true;
	}

	return false;
}

/**
 * Returns link to delete ticket
 *
 * @param int $ticket_id
 *
 * @return string
 */
function qc_get_delete_ticket_link( $ticket_id ) {

	if ( ! current_user_can( 'delete_post', $ticket_id ) ) {
		return;
	}

	$delete_link = add_query_arg( array( 'delete-ticket' => $ticket_id ), qc_home_url() );
	return wp_nonce_url( $delete_link, 'delete-ticket-' . $ticket_id );
}

/**
 * Returns url to filtered page by assigned user
 *
 * @param string $user_login
 * @param string $url
 *
 * @return string|bool A URL or bool false if assignment not supported
 */
function qc_get_assigned_to_url( $user_login, $url = false ) {
	if ( ! current_theme_supports( 'ticket-assignment' ) )
		return false;

	if ( ! $url && ( is_singular( QC_TICKET_PTYPE ) || is_single() || is_category() || is_tag() || is_page() || is_404() ) ) {
		$url = home_url( '/' );
	}

	return add_query_arg( 'assigned', $user_login, $url );
}

/**
 * Check wether the current user is assigned to particular ticket
 *
 * @param int $ticket_id
 */
function current_user_is_assigned_to_ticket( $ticket_id ) {
	if ( ! current_theme_supports( 'ticket-assignment' ) ) {
		return false;
	}

	return p2p_connection_exists( 'qc_ticket_assigned', array(
		'from' => get_current_user_id(),
		'to' => $ticket_id
	) );
}

/**
 * Conditional tag to check if current page is filtered by assigned user
 *
 * @uses qc_is_assigned filter
 */
function qc_is_assigned() {
	$condition = (bool) get_query_var( 'assigned' );

	if ( ! current_theme_supports( 'ticket-assignment' ) ) {
		$condition = false;
	} else if ( is_single() || is_category() || is_tag() || is_404() ) {
		$condition = false;
	}

	return apply_filters( 'qc_is_assigned', $condition );
}

/**
 * Conditional tag to check if home page is filtered by assigned user
 *
 */
function qc_is_home_assigned() {
	global $wp_query;

	return (bool) $wp_query->get( 'home_assigned' );
}

/**
 * Outputs post tags, categories and author
 */
function qc_post_entry_meta() {
	// Translators: used between list items, there is a space after the comma.
	$categories_list = get_the_category_list( ', ' );

	// Translators: used between list items, there is a space after the comma.
	$tag_list = get_the_tag_list( '', ', ' );

	$date = sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date updated" datetime="%3$s" itemprop="dateCreated">%4$s</time></a>',
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	$author = sprintf( '<span class="author vcard" itemprop="name"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', APP_TD ), get_the_author() ) ),
		get_the_author()
	);

	// Translators: 1 is category, 2 is tag, 3 is the date and 4 is the author's name.
	if ( $tag_list ) {
		$utility_text = __( 'Posted in %1$s and tagged %2$s on %3$s<span class="by-author"> by %4$s</span>.', APP_TD );
	} elseif ( $categories_list ) {
		$utility_text = __( 'Posted in %1$s on %3$s<span class="by-author"> by %4$s</span>.', APP_TD );
	} else {
		$utility_text = __( 'Posted on %3$s<span class="by-author"> by %4$s</span>.', APP_TD );
	}

	printf(
		$utility_text,
		$categories_list,
		$tag_list,
		$date,
		$author
	);
}

