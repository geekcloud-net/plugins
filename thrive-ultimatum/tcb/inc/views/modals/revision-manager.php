<?php
$post_id        = get_the_ID();
$revisions      = wp_get_post_revisions( $post_id );
$first_revision = reset( $revisions );
?>
<h2 class="tcb-modal-title"><?php echo __( 'Revision Manager', 'thrive-cb' ) ?></h2>
<p><?php echo __( 'Use the revision manager to restore your page to a previous version:', 'thrive-cb' ); ?></p>
<div class="padding-top-40 row" id="tcb-revision-list"></div>
<div class="tcb-modal-footer clearfix padding-top-40 row">
	<div class="col col-xs-9">
		<?php if ( empty( $first_revision ) ) : ?>
			<?php echo __( 'The current post has no revisions!', 'thrive-cb' ); ?>
		<?php else : ?>
			<a href="<?php echo add_query_arg( array( 'revision' => $first_revision->ID ), admin_url( 'revision.php' ) ) ?>"
			   class="tcb-modal-lnk blue"
			   target="_blank"><?php tcb_icon( 'revision' ); ?>&nbsp;<?php echo __( 'Show the default Wordpress Revision Manager', 'thrive-cb' ); ?></a>
		<?php endif; ?>
	</div>
	<div class="col col-xs-3">
		<button type="button" class="tcb-right tve-button medium green tcb-modal-cancel"><?php echo __( 'Close', 'thrive-cb' ) ?></button>
	</div>
</div>
