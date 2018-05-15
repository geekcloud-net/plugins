<?php

add_action( 'init', 'qc_register_post_type' );

/**
 * Register the Tickets post type, and remove the default "Post" type.
 *
 * @since Quality Control 0.1
 * @global array $wp_post_types The registered post types.
 * @uses register_post_type
 */
function qc_register_post_type() {

	// Register QC_TICKET_PTYPE post type
	$ticket_labels = array(
		'name'               => __( 'Tickets', APP_TD ),
		'singular_name'      => __( 'Ticket', APP_TD ),
		'add_new'            => __( 'Add New', APP_TD ),
		'add_new_item'       => __( 'Add New Ticket', APP_TD ),
		'edit_item'          => __( 'Edit Ticket', APP_TD ),
		'new_item'           => __( 'New Ticket', APP_TD ),
		'view'               => __( 'View Ticket', APP_TD ),
		'view_item'          => __( 'View Ticket', APP_TD ),
		'search_items'       => __( 'Search Tickets', APP_TD ),
		'not_found'          => __( 'No Tickets Found', APP_TD ),
		'not_found_in_trash' => __( 'No Tickets found in trash', APP_TD )
	);

	register_post_type( QC_TICKET_PTYPE, array(
		'labels'          => $ticket_labels,
		'rewrite'         => array( 'slug' => QC_TICKET_PTYPE, 'with_front' => false ),
		'supports'        => array( 'title', 'editor', 'author', 'comments'),
		'menu_position'   => 5,
		'menu_icon'       => appthemes_locate_template_uri( 'images/admin-menu.png' ),
		'public'          => true,
		'show_ui'         => true,
		'can_export'      => true,
		'capabilities'    => array(
			'edit_published_posts' => 'edit_posts'
		),
		'map_meta_cap'    => true,
		'query_var'       => true
	) );
}

