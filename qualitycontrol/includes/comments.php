<?php

add_filter( 'map_meta_cap', 'qc_restrict_comment_editing', 10, 4 );
add_action( 'pre_comment_on_post', 'qc_handle_comments' );


/**
 * The comments, or "updates" for each ticket.
 *
 * @since Quality Control 0.1
 */
function qc_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;

	$updates = get_comment_meta( get_comment_ID(), 'ticket_updates' );

	if ( $att_id = get_comment_meta( get_comment_ID(), 'attachment_id', true ) ) {
		$updates[] = sprintf( __( 'uploaded %s', APP_TD ), qc_get_attachment_link( $att_id ) );
	}

	$i = 0;
?>
	<li <?php comment_class( 'ticket' . ( $i % 2 ? '' : ' alt' ) ); ?> id="comment-<?php comment_ID(); ?>">

		<div class="ticket-gravatar">
			<a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>"><?php echo get_avatar( $comment, 29 ); ?></a>
		</div>

		<div class="ticket-info">

			<p class="ticket-author">
				<strong><?php comment_author_link(); ?></strong><br />
				<a href="<?php echo get_comment_link(); ?>"><?php printf( __( 'about <em title="%s">%s</em> ago', APP_TD ),
					esc_attr( get_the_date() . __( ' at ', APP_TD ) . get_the_time() ),
					human_time_diff( get_comment_time( 'U' ), current_time( 'timestamp' ) )
				); ?></a>
			</p>

			<div class="reply">

				<?php if ( get_comment_text() != '&nbsp;' ) : comment_text(); endif; ?>

				<?php if ( $updates ) : ?>

					<ol class="update-list<?php if ( get_comment_text() == '&nbsp;' ) :?> single<?php endif; ?>">
						<?php foreach ( $updates as $update ) : ?>
							<li><?php echo $update; ?></li>
						<?php endforeach; ?>
					</ol>

				<?php endif; ?>

			</div>

		</div>
<?php
	$i++;
}

// http://scribu.net/wordpress/prevent-blog-authors-from-editing-comments.html
function qc_restrict_comment_editing( $caps, $cap, $user_id, $args ) {
	if ( 'edit_comment' == $cap ) {
		$comment = get_comment( $args[0] );

		if ( $comment->user_id != $user_id ) {
			$caps[] = 'moderate_comments';
		}
	}

	return $caps;
}

/**
 * Handle ticket attribute changes
 */
function qc_handle_comments( $comment_post_ID ) {
	$comment_content = ( isset( $_POST['comment'] ) ) ? trim( $_POST['comment'] ) : null;

	if ( empty( $comment_content ) && apply_filters( 'qc_did_change_ticket', false ) ) {
		// Create an empty comment ourselves, to bypass WP's checks
		$defaults = array(
			'comment_post_ID' => $comment_post_ID,
			'comment_content' => $comment_content,
			'user_id' => get_current_user_id()
		);
		$comment_data = wp_parse_args( $defaults, wp_get_current_commenter() );
		$comment_id = wp_insert_comment( $comment_data );

		$comment = get_comment( $comment_id );

		$location = empty( $_POST['redirect_to'] ) ? get_comment_link( $comment_id ) : $_POST['redirect_to'] . '#comment-' . $comment_id;
		$location = apply_filters( 'comment_post_redirect', $location, $comment );

		wp_redirect( $location );
		exit;
	}
}

