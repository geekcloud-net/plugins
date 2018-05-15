<?php

/**
 * Generates a message used when ticket attributes are changed.
 */
function _qc_get_message_diff( $added, $deleted ) {
		$msg = array();

		if ( !empty( $added ) ) {
			$list = '<em>' . implode( '</em>, <em>', $added ) . '</em>';
			$msg[] = sprintf( __( '%s added', APP_TD ), $list );
		}

		if ( !empty( $deleted ) ) {
			$list = '<em>' . implode( '</em>, <em>', $deleted ) . '</em>';
			$msg[] = sprintf( __( '%s removed', APP_TD ), $list );
		}

		return $msg;
}

/**
 * Deletes ticket together with associated attachments
 *
 * @param int $ticket_id
 *
 * @return bool
 */
function qc_delete_ticket( $ticket_id ) {
	global $wpdb;

	$attachments_query = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_parent = %d AND post_type='attachment'", $ticket_id );
	$attachments = $wpdb->get_results( $attachments_query );

	// delete all associated attachments
	if ( $attachments ) {
		foreach( $attachments as $attachment )
			wp_delete_attachment( $attachment->ID, true );
	}

	// delete post and it's revisions, comments, meta
	if ( wp_delete_post( $ticket_id, true ) )
		return true;
	else
		return false;
}

/**
 * Returns ticket taxonomy class instance
 *
 * @param string $tax_name
 *
 * @return object|bool
 */
function qc_get_tax_instance( $tax_name ) {
	$classes = array(
		'ticket_status' => 'QC_Ticket_Status',
		'ticket_priority' => 'QC_Ticket_Priority',
		'ticket_milestone' => 'QC_Ticket_Milestone',
		'ticket_category' => 'QC_Ticket_Category',
		'ticket_tags' => 'QC_Ticket_Tags',
	);

	if ( isset( $classes[ $tax_name ] ) ) {
		return appthemes_get_instance( $classes[ $tax_name ] );
	} else {
		return false;
	}
}

/**
 * Returns an array of settings for WP Editor used on the frontend.
 * @since 0.8
 *
 * @param array $settings (optional)
 *
 * @return array An array of WP Editor settings.
 */
function qc_get_editor_settings( $settings = array() ) {
	$defaults = array(
		'media_buttons' => false,
		'textarea_rows' => 10,
		'tabindex' => '',
		'teeny' => false,
		'tinymce' => false,
		'quicktags' => array(
			'buttons' => 'strong,em,ul,ol,li,link,block,code,close',
		),
	);
	$settings = wp_parse_args( $settings, $defaults );

	return $settings;
}


function qc_get_query() {
	global $qc_query, $wp_query;

	return ( ! empty( $qc_query ) ) ? $qc_query : $wp_query;
}
