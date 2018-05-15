<?php

add_filter( 'manage_edit-ticket_columns', 'qc_manage_column_titles' );
add_filter( 'manage_edit-ticket_columns', 'qc_manage_column_titles_date', 100, 1 );
add_filter( 'manage_users_columns', 'qc_manage_users_columns' );
add_filter( 'manage_users_custom_column', 'qc_manage_users_custom_column', 10, 3 );
add_action( 'admin_print_styles', 'qc_admin_styles' );


/**
 * Add Extra columns to the ticket overview.
 */
function qc_manage_column_titles( $columns ) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Ticket', APP_TD ),
	);

	return $columns;
}

/**
 * Add Extra columns to the ticket overview.
 *
 * Delay this one until the end, so we can put the creation date
 * at the end of the table.
 */
function qc_manage_column_titles_date( $columns ) {
	$columns['date'] = __( 'Created', APP_TD );

	return $columns;
}

/**
 * Add Extra columns to the users overview.
 */
function qc_manage_users_columns( $columns ) {
	$columns['tickets'] = __( 'Tickets', APP_TD );
	unset( $columns['posts'] );

	return $columns;
}

/**
 * Display Extra columns in the users overview.
 */
function qc_manage_users_custom_column( $r, $column_name, $user_id ) {
	global $wp_list_table, $ticket_counts;

	if ( $column_name == 'tickets' ) {
		if ( ! isset( $ticket_counts ) )
			$ticket_counts = count_many_users_posts( array_keys( $wp_list_table->items ), QC_TICKET_PTYPE );

		if ( ! isset( $ticket_counts[ $user_id ] ) )
			$ticket_counts[ $user_id ] = 0;

		if ( $ticket_counts[ $user_id ] > 0 ) {
			$url = add_query_arg( array( 'post_type' => QC_TICKET_PTYPE, 'author' => $user_id ), admin_url( 'edit.php' ) );
			$r .= html( 'a', array( 'href' => $url, 'class' => 'edit', 'title' => esc_attr__( 'View tickets by this author', APP_TD ) ), $ticket_counts[ $user_id ] );
		} else {
			$r .= 0;
		}
	}

	return $r;
}


function qc_admin_styles() {
	appthemes_menu_sprite_css( array(
		'#toplevel_page_app-dashboard',
		'#adminmenu #menu-posts-ticket',
		'#adminmenu #menu-posts-changeset'
	) );
}

