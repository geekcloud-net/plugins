<?php
/**
 * Handle attachments when creating a ticket, or updating one.
 *
 * @package Quality_Control
 * @subpackage Tickets
 * @since Quality Control 0.1
 */

class QC_Attachments {
	private static $new_attachment = false;

	public static function init() {
		add_action( 'qc_create_ticket', array( __CLASS__, 'save_attachment' ) );
		add_action( 'pre_comment_on_post', array( __CLASS__, 'update_attachment' ), 9 );
		add_action( 'wp_insert_comment', array( __CLASS__, 'store_attr_changes' ) );
	}

	public static function save_attachment( $ticket_id ) {
		require_once( ABSPATH . "wp-admin" . '/includes/admin.php' );

		$att = media_handle_upload( 'ticket_attachment', $ticket_id );
		if ( is_wp_error( $att ) ) {
			return;
		}

		self::$new_attachment = $att;

		add_filter( 'qc_did_change_ticket', '__return_true' );
	}

	public static function update_attachment( $ticket_id ) {
		$ticket = get_post( $ticket_id );

		if ( ! $ticket || $ticket->post_type != QC_TICKET_PTYPE ) {
			return;
		}

		self::save_attachment( $ticket_id );
	}

	public static function store_attr_changes( $comment_id ) {
		if ( self::$new_attachment ) {
			add_comment_meta( $comment_id, 'attachment_id', self::$new_attachment );
		}
	}
}

QC_Attachments::init();

function qc_get_attachment_link( $att_id ) {
	$url = wp_get_attachment_url( $att_id );

	return html_link( $url, basename( $url ) );
}

