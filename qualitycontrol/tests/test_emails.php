<?php

require_once dirname( __FILE__ ) . '/testcase.php';

class QC_Test_Emails extends QC_UnitTestCase {

	function assertMailSentTo( $roles ) {
		$addresses = array();

		foreach ( (array) $roles as $role ) {
			$user = new WP_User( $this->users[ $role ] );
			$addresses[] = $user->user_email;
		}

		parent::assertMailSentTo( $addresses );
	}

	function test_assignment_mail() {
		$this->create_ticket( 'administrator', 'author' );

		$this->assertMailSentTo( 'author' );
	}
}

