<?php

class QC_Blog_Archive extends APP_View_Page {

	function __construct() {
		parent::__construct( 'index.php', __( 'Blog', APP_TD ) );
	}

	static function get_id() {
		return parent::_get_id( __CLASS__ );
	}
}


class QC_Ticket_View extends APP_View {

	function condition() {
		if ( is_page() ) {
			return false;
		}

		return in_array( get_query_var( 'post_type' ), array( QC_TICKET_PTYPE ) ) || is_author();
	}

	function parse_query( $wp_query ) {
		$wp_query->set( 'post_type', QC_TICKET_PTYPE );
		$wp_query->set( 'orderby', 'modified' );
	}
}


abstract class QC_Ticket_Archive extends APP_View {

	abstract protected function do_filtering( $wp_query );

	function parse_query( $wp_query ) {
		$wp_query->set( 'orderby', 'modified' );
		if ( ! qc_can_view_all_tickets() ) {
			if ( ! is_user_logged_in() ) {
				$wp_query->set( 'year', 3333 );
			} else {
				$this->do_filtering( $wp_query );
			}
		}
	}

	static function check_slug( $qv ) {
		return get_query_var( $qv ) == wp_get_current_user()->user_nicename;
	}
}


class QC_Ticket_Archive_Taxonomy extends QC_Ticket_Archive {

	function condition() {
		return is_tax( array( 'ticket_category', 'ticket_tag', 'ticket_milestone', 'ticket_priority', 'ticket_status' ) );
	}

	protected function do_filtering( $wp_query ) {
		$wp_query->set( '_assigned_or_author', true );
	}

	function template_include( $template ) {
		return locate_template( 'archive-ticket.php' );
	}

}


class QC_Ticket_Search extends QC_Ticket_Archive {

	function condition() {
		return is_search();
	}

	protected function do_filtering( $wp_query ) {
		$wp_query->set( '_assigned_or_author', true );

		$wp_query->set( 'post_type', QC_TICKET_PTYPE );
		$wp_query->set( 'orderby', 'modified' );
	}

	function template_include( $template ) {
		return locate_template( 'archive-ticket.php' );
	}

}


class QC_Ticket_Home extends APP_View_Page {

	function __construct() {
		parent::__construct( 'tickets-home.php', __( 'Tickets', APP_TD ) );
	}

	static function get_id() {
		return parent::_get_id( __CLASS__ );
	}

	static function get_query_args() {
		global $wp_query;

		$args = $wp_query->query;
		$args['post_type'] = QC_TICKET_PTYPE;
		$args['orderby'] = 'modified';

		$remove_args = array( 'pagename', 'page_id' );
		$args = array_diff_key( $args, array_flip( $remove_args ) );

		if ( ! qc_can_view_all_tickets() ) {
			if ( ! is_user_logged_in() ) {
				$args['year'] = 3333;
			} else {
				$args['_assigned_or_author'] = true;
			}
		}

		return $args;
	}

	function template_redirect() {
		global $qc_query, $wp_query;

		// if page on front, set back paged parameter
		if ( self::get_id() == get_option( 'page_on_front' ) ) {
			$paged = get_query_var( 'page' );
			$wp_query->set( 'paged', $paged );
		}

		if ( isset( $_GET['delete-ticket'] ) ) {
			$this->delete_ticket();
		}

		// setup new query
		$qc_query = new WP_Query( QC_Ticket_Home::get_query_args() );

		add_action( 'appthemes_notices', array( $this, 'show_notice' ) );
	}

	function show_notice() {
		if ( isset( $_GET['deleted-ticket'] ) ) {
			appthemes_display_notice( 'success', __( 'Ticket has been deleted.', APP_TD ) );
		}
	}

	function delete_ticket() {
		if ( ! isset( $_GET['delete-ticket'] ) || ! is_numeric( $_GET['delete-ticket'] ) ) {
			return;
		}

		$ticket_id = (int) $_GET['delete-ticket'];

		if ( ! current_user_can( 'delete_post', $ticket_id ) ) {
			return;
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'delete-ticket-' . $ticket_id ) ) {
			return;
		}

		qc_delete_ticket( $ticket_id );
		$redirect_url = add_query_arg( array( 'deleted-ticket' => 'true' ), qc_home_url() );
		wp_redirect( $redirect_url );
		exit();
	}

}


class QC_Ticket_Archive_Assigned extends APP_View {

	function condition() {
		return qc_is_assigned();
	}

	function parse_query( $wp_query ) {
		if ( ! qc_can_view_all_tickets() ) {
			if ( ! is_user_logged_in() || ! QC_Ticket_Archive::check_slug( 'assigned' ) ) {
				$wp_query->set( 'year', 3333 );
				return;
			}
		}

		$user = get_user_by( 'login', get_query_var( 'assigned' ) );

		if ( empty( $user ) ) {
			$wp_query->set( 'year', 3333 );
			return;
		}

		$wp_query->set( 'post_type', QC_TICKET_PTYPE );
		$wp_query->set( 'orderby', 'modified' );

		$wp_query->set( 'connected_type', 'qc_ticket_assigned' );
		$wp_query->set( 'connected_items', $user );

		if ( is_home() ) {
			$wp_query->is_home = false;
			$wp_query->set( 'home_assigned', true );
		}

	}

	function title_parts( $title ) {

		if ( empty( $title ) ) {
			$title = array(
				sprintf( __( 'Assigned to %s', APP_TD ), get_query_var( 'assigned' ) ),
			);
		}

		return $title;
	}
}


class QC_Ticket_Archive_Author extends QC_Ticket_Archive {

	function condition() {
		return is_author();
	}

	protected function do_filtering( $wp_query ) {
		$wp_query->set( '_assigned_or_author', true );
	}

	function template_include( $template ) {
		return locate_template( 'archive-ticket.php' );
	}

}


class QC_Ticket_Single extends APP_View {

	function condition() {
		return is_singular( QC_TICKET_PTYPE );
	}

	function template_redirect() {
		global $wp_query, $post;

		if ( ! qc_can_view_ticket( $post->ID ) ) {
			$wp_query->set_404();
		}

	}

	function title_parts( $title ) {
		global $post;

		$title = array(
			'#' . $post->ID . ' ' . $post->post_title,
			get_bloginfo( 'name' ),
		);

		return $title;
	}
}


class QC_Ticket_Create extends APP_View_Page {

	function __construct() {
		parent::__construct( 'create-ticket.php', __( 'Create Ticket', APP_TD ) );
		add_action( 'init', array( __CLASS__, 'handle_form' ), 11 );
	}

	static function get_id() {
		return parent::_get_id( __CLASS__ );
	}

	/**
	 * Create a fresh ticket via the front-end form submission.
	 * Checks for valid permissions, then gathers
	 * the information, and creates a new post.
	 *
	 * @since Quality Control 0.1
	 */
	static function handle_form() {
		global $qc_options;

		if ( empty( $_POST['action'] ) || 'qc-create-ticket' != $_POST['action'] ) {
			return;
		}

		check_admin_referer( 'qc-create-ticket' );

		$ticket = array();

		foreach ( $_POST as $key => $value ) {
			$ticket[ $key ] = isset( $value ) ? $value : "";
		}

		$ticket['ticket_author'] = get_current_user_id();

		if ( empty( $ticket['ticket_title'] ) || empty( $ticket['comment'] ) ) {
			return;
		}

		$args = array(
			'post_type' => QC_TICKET_PTYPE,
			'post_status' => 'publish',
			'comment_status' => 'open',
			'post_content' => $ticket['comment'],
			'post_title' => $ticket['ticket_title'],
			'post_author' => $ticket['ticket_author'],
		);

		$args = apply_filters( 'qc_ticket_args', $args );

		$ticket_id = wp_insert_post( $args );

		if ( ! empty( $ticket_id ) && ! is_wp_error( $ticket_id ) ) {
			do_action( 'qc_create_ticket', $ticket_id, $ticket );

			wp_redirect( get_permalink( $ticket_id ) );
			exit();
		}

	}

}

