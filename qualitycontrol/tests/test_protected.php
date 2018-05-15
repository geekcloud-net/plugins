<?php

require_once dirname( __FILE__ ) . '/testcase.php';

class QC_Archive_Tests_Protected extends QC_UnitTestCase {

	function setUp() {
		parent::setUp();

		global $qc_options;

		$qc_options->assigned_perms = 'protected';
	}

	function test_author() {
		$this->_pre_test( 'author' );

		// user dashboard
		$this->go_to( '/' );
		$this->assertHomeTicketCount( 3 );

		$this->assertAuthorArchive( 2 );
		$this->assertAssignedArchive( 2 );
	}

	function test_contributor() {
		$this->_pre_test( 'contributor', 'author' );

		// user dashboard
		$this->go_to( '/' );
		$this->assertHomeTicketCount( 3 );

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
}

