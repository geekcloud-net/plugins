<?php

class QC_Importer extends APP_Importer {

	function setup() {
		parent::setup();

		$this->args['admin_action_priority'] = 11;
		add_action( 'appthemes_after_import_upload_form', array( $this, 'example_csv_files' ) );
		add_action( 'appthemes_importer_import_row_after', array( $this, 'assign_users' ), 10, 2 );
	}

	/**
	 * Assign users to tickets.
	 */
	function assign_users( $ticket_id, $row ) {
		if ( ! empty( $row['assigned'] ) && current_theme_supports( 'ticket-attachments' ) ) {
			$users = QC_Assignment::get_users_from_str( $row['assigned'] );
			QC_Assignment::set_assigned( $users, $ticket_id );
		}
	}

	/**
	 * Inserts links to example CSV files into Importer page.
	 */
	function example_csv_files() {
		$link = html( 'a', array( 'href' => get_template_directory_uri() . '/examples/tickets.csv', 'title' => __( 'Download CSV file', APP_TD ) ), __( 'Tickets', APP_TD ) );

		echo html( 'p', sprintf( __( 'Download example CSV file: %s', APP_TD ), $link ) );
	}

}


function qc_csv_importer() {
	$fields = array(
		'title'       => 'post_title',
		'description' => 'post_content',
		'status'      => 'post_status',
		'author'      => 'post_author',
		'date'        => 'post_date',
		'slug'        => 'post_name'
	);

	$args = array(
		'taxonomies' => array( 'ticket_category', 'ticket_tag', 'ticket_milestone', 'ticket_priority', 'ticket_status' ),

		'custom_fields' => array(
			'assigned' => '_assigned_to',
		),

		'attachments' => true

	);

	$args = apply_filters( 'qc_csv_importer_args', $args );

	appthemes_add_instance( array( 'QC_Importer' => array( QC_TICKET_PTYPE, $fields, $args ) ) );
}
add_action( 'wp_loaded', 'qc_csv_importer' );

