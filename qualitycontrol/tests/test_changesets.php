<?php

require_once dirname( __FILE__ ) . '/testcase.php';

class QC_Changesets_Tests extends QC_UnitTestCase {

	function setUp() {
		parent::setUp();

	}

	function test_parse_commit_messages() {
		// GH Formatting Style
		// Test 1
		$message = 'Test commit #123';
		$expected = array(
			123 => array(),
		);
		$actual = QC_Changesets::parse_commit_message( $message );
		$this->assertEquals( $actual, $expected );

		// Test 2
		$message = 'Test commit Closes #123';
		$expected = array(
			123 => array( 'ticket_status' => 'closes' ),
		);
		$actual = QC_Changesets::parse_commit_message( $message );
		$this->assertEquals( $actual, $expected );

		// Test 3
		$message = 'Test commit Fixes #123';
		$expected = array(
			123 => array( 'ticket_status' => 'closes' ),
		);
		$actual = QC_Changesets::parse_commit_message( $message );
		$this->assertEquals( $actual, $expected );

		// Test 4
		$message = 'Test commit Fixes #123 #321';
		$expected = array(
			123 => array( 'ticket_status' => 'closes' ),
			321 => array( 'ticket_status' => 'closes' ),
		);
		$actual = QC_Changesets::parse_commit_message( $message );
		$this->assertEquals( $actual, $expected );

		// LH Formatting Style
		// Test 5
		$message = 'Test commit #123 status:closed';
		$expected = array(
			123 => array( 'ticket_status' => 'closed' ),
		);
		$actual = QC_Changesets::parse_commit_message( $message );
		$this->assertEquals( $actual, $expected );

		// Test 6
		$message = 'Test commit [#123 status:closed]';
		$expected = array(
			123 => array( 'ticket_status' => 'closed' ),
		);
		$actual = QC_Changesets::parse_commit_message( $message );
		$this->assertEquals( $actual, $expected );

		// Test 7
		$message = 'Test commit [#123 #321 status:closed]';
		$expected = array(
			123 => array( 'ticket_status' => 'closed' ),
			321 => array( 'ticket_status' => 'closed' ),
		);
		$actual = QC_Changesets::parse_commit_message( $message );
		$this->assertEquals( $actual, $expected );

		// Test 8
		$message = 'Test commit [#123 status:closed] [#321 status:new]';
		$expected = array(
			123 => array( 'ticket_status' => 'closed' ),
			321 => array( 'ticket_status' => 'new' ),
		);
		$actual = QC_Changesets::parse_commit_message( $message );
		$this->assertEquals( $actual, $expected );

		// Test 9
		$message = 'Test commit [#123 tagged:dev]';
		$expected = array(
			123 => array( 'ticket_tags' => array( 'dev' ) ),
		);
		$actual = QC_Changesets::parse_commit_message( $message );
		$this->assertEquals( $actual, $expected );

		// Test 10
		$message = 'Test commit [#123 tagged:dev tagged:test]';
		$expected = array(
			123 => array( 'ticket_tags' => array( 'dev', 'test' ) ),
		);
		$actual = QC_Changesets::parse_commit_message( $message );
		$this->assertEquals( $actual, $expected );

		// Test 11
		$message = 'Test commit [#123 assigned:admin]';
		$expected = array(
			123 => array( 'ticket_assigned' => array( 'admin' ) ),
		);
		$actual = QC_Changesets::parse_commit_message( $message );
		$this->assertEquals( $actual, $expected );

		// Test 12
		$message = 'Test commit [#123 assigned:admin assigned:appthemes]';
		$expected = array(
			123 => array( 'ticket_assigned' => array( 'admin', 'appthemes' ) ),
		);
		$actual = QC_Changesets::parse_commit_message( $message );
		$this->assertEquals( $actual, $expected );

		// Test 13
		$message = 'Test commit [#123 category:misc]';
		$expected = array(
			123 => array( 'ticket_category' => 'misc' ),
		);
		$actual = QC_Changesets::parse_commit_message( $message );
		$this->assertEquals( $actual, $expected );

		// Test 14
		$message = 'Test commit [#123 priority:medium]';
		$expected = array(
			123 => array( 'ticket_priority' => 'medium' ),
		);
		$actual = QC_Changesets::parse_commit_message( $message );
		$this->assertEquals( $actual, $expected );

		// Test 15
		$message = 'Test commit [#123 status:closed milestone:misc tagged:dev]';
		$expected = array(
			123 => array( 'ticket_status' => 'closed', 'ticket_milestone' => 'misc', 'ticket_tags' => array( 'dev' ) ),
		);
		$actual = QC_Changesets::parse_commit_message( $message );
		$this->assertEquals( $actual, $expected );


	}

}

