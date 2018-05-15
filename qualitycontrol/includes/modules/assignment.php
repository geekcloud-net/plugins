<?php

add_action( 'init', array( 'QC_Assignment', 'init' ) );
add_action( 'appthemes_first_run', array( 'QC_Assignment', 'upgrade' ) );


class QC_Assignment {
	private static $msg;

	static function init() {
		p2p_register_connection_type( array(
			'name' => 'qc_ticket_assigned',
			'from' => 'user',
			'to' => QC_TICKET_PTYPE
		) );

		if ( current_user_can( 'edit_posts' ) ) {
			add_action( 'qc_ticket_form_advanced_fields', array( __CLASS__, 'add_assign_field' ), 100, 1 );
			add_action( 'qc_create_ticket', array( __CLASS__, 'assign_user' ), 10, 2 );

			add_action( 'pre_comment_on_post', array( __CLASS__, 'update_ticket_owners' ), 9 );
			add_action( 'wp_insert_comment', array( __CLASS__, 'store_attr_changes' ) );
		}

		add_filter( 'posts_clauses', array( __CLASS__, 'posts_clauses' ), 10, 2 );

		add_filter( 'wp_ajax_qc-user-search', array( __CLASS__, 'user_search' ) );
	}

	static function upgrade() {
		global $wpdb;

		$rows = $wpdb->get_results( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_assigned_to'" );

		$ctype = p2p_type( 'qc_ticket_assigned' )->set_direction( 'from' );

		foreach ( $rows as $row ) {
			$user_id = $row->meta_value;
			$ticket_id = $row->post_id;

			$ctype->connect( $user_id, $ticket_id );
		}

		$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_assigned_to'" );
	}

	static function add_assign_field( $context ) {
?>
		<p id="ticket-assign">
			<label for="ticket_assign"><?php _e( 'Assigned To: <em>(Optional) Separated multiple usernames with commas.</em>', APP_TD ); ?></label>
			<input type="text" name="ticket_assign" value="<?php echo ( 'update' == $context ? qc_assigned_to_flat() : '' ); ?>" />
		</p>
<?php
	}

	/**
	 * Assign users to a ticket when one is created.
	 */
	static function assign_user( $ticket_id, $ticket ) {
		self::set_assigned( self::get_users_from_str( $ticket['ticket_assign'] ), $ticket_id );
	}

	/**
	 * When a comment has been created, check to see if the assigned
	 * users have been changed. If so, see if you can find the difference.
	 */
	static function update_ticket_owners( $ticket_id = false, $users = false ) {
		$ticket_id = ( $ticket_id ) ? $ticket_id : $GLOBALS['post']->ID;

		$ticket = get_post( $ticket_id );

		if ( ! $ticket || $ticket->post_type != QC_TICKET_PTYPE ) {
			return;
		}

		$old = get_users( array(
			'connected_type' => 'qc_ticket_assigned',
			'connected_items' => $ticket_id,
			'fields' => 'ids',
		) );

		$users = ( $users ) ? $users : $_POST['ticket_assign'];
		$new = self::get_users_from_str( $users );

		$added = array_diff( $new, $old );
		$deleted = array_diff( $old, $new );

		self::set_assigned( $added, $ticket_id );

		p2p_delete_connections( 'qc_ticket_assigned', array(
			'from' => $deleted,
			'to' => $ticket_id
		) );

		$added = self::ids_to_names( $added );
		$deleted = self::ids_to_names( $deleted );

		$msg = _qc_get_message_diff( $added, $deleted );

		if ( empty( $msg ) ) {
			return;
		}

		$msg = '<strong>' . __( 'Assignment', APP_TD ) . '</strong> ' . implode( '; ', $msg ) . '.';

		self::$msg = apply_filters( 'qc_ticket_update_assignment', $msg, $old, $new );

		add_filter( 'qc_did_change_ticket', '__return_true' );
	}

	static function store_attr_changes( $comment_id ) {
		if ( self::$msg ) {
			add_comment_meta( $comment_id, 'ticket_updates', self::$msg );
		}
	}

	private static function ids_to_names( $list ) {
		$names = array();

		foreach ( $list as $user_id )
			$names[] = get_userdata( $user_id )->user_login;

		return $names;
	}

	public static function get_users_from_str( $string ) {
		$users = array();
		foreach ( explode( ',', $string ) as $login ) {
			$user = get_user_by( 'login', $login );
			if ( $user ) {
				$users[] = $user->ID;
			}
		}

		return $users;
	}

	public static function set_assigned( $user_ids, $ticket_id ) {
		$ctype = p2p_type( 'qc_ticket_assigned' )->set_direction( 'from' );

		foreach ( $user_ids as $user_id ) {
			$ctype->connect( $user_id, $ticket_id );

			self::notify( $user_id, $ticket_id );
		}
	}

	private static function notify( $user_id, $ticket_id ) {
		$owner = get_userdata( $user_id );

		if ( current_theme_supports( 'ticket-notifications' ) ) {
			$to = $owner->user_email;
			$subject = apply_filters( 'qc_ticket_create_subject', sprintf( __( 'Updated Ticket on %s', APP_TD ), get_bloginfo( 'name' ) ) );
			$message = apply_filters( 'qc_ticket_create_message', sprintf( __( 'A new ticket has been updated on %1$s, and you are currently assigned to it: %2$s', APP_TD ), get_bloginfo( 'name' ), get_permalink( $ticket_id ) ) );
			$headers = apply_filters( 'qc_ticket_create_headers', sprintf( __( 'From: %1$s <%2$s>', APP_TD ), get_bloginfo( 'name' ), get_bloginfo( 'admin_email' ) ) );

			@wp_mail( $to, $subject, $message, $headers );
		}
	}

	static function posts_clauses( $clauses, $wp_query ) {
		global $wpdb;

		// TODO: UNION
		if ( $wp_query->get( '_assigned_or_author' ) ) {
			$user_id = get_current_user_id();

			$clauses['join'] .= " LEFT JOIN $wpdb->p2p AS p2p_aoa ON ($wpdb->posts.ID = p2p_aoa.p2p_to)";

			$clauses['where'] .= $wpdb->prepare( " AND (
				(p2p_aoa.p2p_type = 'qc_ticket_assigned' AND p2p_aoa.p2p_from = %d)
				OR $wpdb->posts.post_author = %d
			)", $user_id, $user_id );

			if ( empty( $clauses['groupby'] ) )
				$clauses['groupby'] = "$wpdb->posts.ID";
		}

		return $clauses;
	}

	/**
	 * Handle assignment autosuggest
	 */
	static function user_search() {
		$users = get_users( array(
			'search' => $_GET['q'] . '*',
			'fields' => array( 'user_login' ),
		) );

		echo implode( "\n", wp_list_pluck( $users, 'user_login' ) );
		exit;
	}
}

/**
 * Create a comma separated flat list of the current
 * owners. The owners are not linked (see qc_assigned_to_list)
 */
function qc_assigned_to_flat( $post_id = null, $separator = ', ' ) {
	return implode( $separator, wp_list_pluck( _qc_assigned_to_users( $post_id ), 'user_login' ) );
}

/**
 * Create a list of linked owners. Links to a page
 * showing all tickets assigned to that user.
 */
function qc_assigned_to_linked( $post_id = null, $separator = ', ' ) {
	$links = array();
	foreach ( _qc_assigned_to_users( $post_id ) as $user ) {
		$links[] = sprintf(
			'<a href="%1$s" title="%2$s">%3$s</a>',
			qc_get_assigned_to_url( $user->user_login ),
			esc_attr( sprintf( __( 'Tickets assigned to %s', APP_TD ), $user->display_name ) ),
			$user->display_name
		);
	}

	if ( empty( $links ) )
		return '&mdash;';

	return implode( $separator, $links );
}

function _qc_assigned_to_users( $ticket_id ) {
	if ( null == $ticket_id )
		$ticket_id = get_the_ID();

	return get_users( array(
		'connected_type' => 'qc_ticket_assigned',
		'connected_items' => $ticket_id,
	) );
}

