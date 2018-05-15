<?php

require_once dirname( __FILE__ ) . '/testcase.php';

class QC_Archive_Tests_ReadOnly extends QC_UnitTestCase {

	function setUp() {
		parent::setUp();

		global $qc_options;

		$qc_options->assigned_perms = 'read-only';
	}

	function test_author() {
		$this->_pre_test( 'author' );

		// user dashboard
		$this->go_to( '/' );
		$this->assertHomeTicketCount( 4 );

		$this->assertAuthorArchive( 2 );
		$this->assertAssignedArchive( 2 );
	}

	function test_contributor() {
		$this->_pre_test( 'contributor', 'author' );

		// user dashboard
		$this->go_to( '/' );
		$this->assertHomeTicketCount( 4 );

		$this->assertAuthorArchive( 2 );
		$this->assertAssignedArchive( 2 );
	}

	function test_admininistrator() {
		$this->_pre_test( 'administrator' );

		// user dashboard
		$this->go_to( '/' );
		$this->assertHomeTicketCount( 4 );

		$this->assertAuthorArchive( 2 );
		$this->assertAssignedArchive( 2 );
	}

	function test_tag_archive() {
		$tickets = array();

		foreach ( array( 'author', 'contributor' ) as $role ) {
			$ticket_id = $this->create_ticket( $role );
			wp_set_post_terms( $ticket_id, 'test', QC_Ticket_Tags::TAXONOMY );

			$tickets[] = $ticket_id;
		}

		// Some unrelated tickets
		$this->create_ticket( 'contributor' );
		$this->create_ticket( 'administrator' );

		// Custom taxonomy accessible via ?term=..&=taxonomy..
		$this->go_to( add_query_arg( array( 'term' => 'test', 'taxonomy' => QC_Ticket_Tags::TAXONOMY ), '/' ) );

		$found_ids = wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' );

		$this->assertEqualSets( $found_ids, $tickets );
	}

	function test_status_archive() {
		$tickets = array();
		$term = get_term_by( 'slug', 'closed', QC_Ticket_Status::TAXONOMY );

		foreach ( array( 'author', 'contributor' ) as $role ) {
			$ticket_id = $this->create_ticket( $role );
			wp_set_post_terms( $ticket_id, array( (int) $term->term_id ), QC_Ticket_Status::TAXONOMY );

			$tickets[] = $ticket_id;
		}

		// Some unrelated tickets
		$this->create_ticket( 'contributor' );
		$this->create_ticket( 'administrator' );

		// Custom taxonomy accessible via ?term=..&=taxonomy..
		$this->go_to( add_query_arg( array( 'term' => 'closed', 'taxonomy' => QC_Ticket_Status::TAXONOMY ), '/' ) );

		$found_ids = wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' );

		$this->assertEqualSets( $found_ids, $tickets );
	}

}

