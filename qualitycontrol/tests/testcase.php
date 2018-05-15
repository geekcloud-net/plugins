<?php

require_once APP_TESTS_LIB . '/testcase.php';

abstract class QC_UnitTestCase extends APP_UnitTestCase {

	protected $roles = array( 'contributor', 'author', 'administrator' );
	protected $users = array();

	function setUp() {
		parent::setUp();

		$this->create_users();
		$this->create_terms();
		$this->setup_pages();
	}

	protected function create_users() {
		foreach ( $this->roles as $role ) {
			$user_id = $this->factory->user->create( array(
				'role' => $role,
				'user_login' => 'an-' . $role
			) );

			if ( is_wp_error( $user_id ) ) {
				throw new Exception( $user_id->get_error_message() );
			}

			$this->users[ $role ] = $user_id;
		}
	}

	protected function create_terms() {
		foreach ( array( 'new', 'closed' ) as $term ) {
			$status_term = wp_insert_term( $term, QC_Ticket_Status::TAXONOMY );
		}
	}

	protected function setup_pages() {
		// set blog and tickets pages
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', QC_Ticket_Home::get_id() );
		update_option( 'page_for_posts', QC_Blog_Archive::get_id() );
	}

	protected function create_ticket( $author_role, $assignee_role = false ) {
		$ticket_id = $this->factory->post->create( array(
			'post_type' => QC_TICKET_PTYPE,
			'post_author' => $this->users[ $author_role ]
		) );

		if ( $assignee_role ) {
			QC_Assignment::set_assigned(
				array( $this->users[ $assignee_role ] ),
				$ticket_id
			);
		}

		$term = get_term_by( 'slug', 'new', QC_Ticket_Status::TAXONOMY );
		wp_set_post_terms( $ticket_id, array( (int) $term->term_id ), QC_Ticket_Status::TAXONOMY );

		return $ticket_id;
	}

	protected function _pre_test( $role, $other_role = 'contributor' ) {
		$this->create_ticket( $other_role, $role );
		$this->create_ticket( $role, $other_role );
		$this->create_ticket( $role, $role );
		$this->create_ticket( $other_role, $other_role );

		wp_set_current_user( $this->users[ $role ] );
	}

	protected function assertAuthorArchive( $post_count ) {
		$this->go_to( add_query_arg( 'author', get_current_user_id(), '/' ) );

		$constraint = $this->logicalAnd(
			$this->authorArchive(),
			$this->postCount( $post_count )
		);

		self::assertThat( $GLOBALS['wp_query'], $constraint );

		$this->assertSingularTicket( $GLOBALS['wp_query']->posts[0]->ID );
	}

	protected function assertAssignedArchive( $post_count ) {
		$user_slug = wp_get_current_user()->user_login;
		$this->go_to( add_query_arg( 'assigned', $user_slug, '/' ) );

		$constraint = $this->logicalAnd(
			$this->assignedTicketsArchive(),
			$this->postCount( $post_count )
		);

		self::assertThat( $GLOBALS['wp_query'], $constraint );

		$this->assertSingularTicket( $GLOBALS['wp_query']->posts[0]->ID );
	}

	protected function assertSingular( $post_id ) {
		$this->go_to( add_query_arg( 'p', $post_id, '/' ) );

		self::assertThat( $GLOBALS['wp_query'], $this->postCount( 1 ) );
	}

	protected function assertSingularTicket( $post_id ) {
		// Custom post type accessible via ?p=..&post_type=..
		$this->go_to( add_query_arg( array( 'p' => $post_id, 'post_type' => QC_TICKET_PTYPE ), '/' ) );

		self::assertThat( $GLOBALS['wp_query'], $this->postCount( 1 ) );
	}

	protected function assertHomeTicketCount( $expected ) {
		// tickets home is a static page which have separate query
		$qc_query = new WP_Query( QC_Ticket_Home::get_query_args() );

		self::assertThat( $qc_query, $this->postCount( $expected ) );
	}

	protected function authorArchive() {
		return new APP_Constraint_WP_Query( 'is author archive', array( __CLASS__, '_is_author_archive_cb' ) );
	}

	protected function assignedTicketsArchive() {
		return new APP_Constraint_WP_Query( 'is assigned tickets archive', array( __CLASS__, '_is_assigned_tickets_archive_cb' ) );
	}

	public static function _is_author_archive_cb( $post ) {
		return get_current_user_id() == $post->post_author;
	}

	public static function _is_assigned_tickets_archive_cb( $post ) {
		return current_user_is_assigned_to_ticket( $post->ID );
	}

}

