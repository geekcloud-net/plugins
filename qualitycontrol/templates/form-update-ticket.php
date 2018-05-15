<?php
/**
 * @package Quality_Control
 * @since Quality Control 0.2
 */

global $qc_options; ?>

<?php do_action( 'qc_ticket_before' ); ?>

<?php appthemes_before_comments_form(); ?>

<div id="respond">

	<form id="update-ticket" action="<?php echo site_url( 'wp-comments-post.php' ); ?>" method="post" name="add-ticket" enctype="multipart/form-data">

		<?php do_action( 'qc_ticket_form_top' ); ?>

		<?php do_action( 'qc_ticket_form_before_fields' ); ?>

		<fieldset>

			<legend>
				<?php _e( 'Leave a comment', APP_TD ); ?>
			</legend>

			<?php do_action( 'qc_ticket_form_before_basic_fields' ); ?>

			<?php wp_editor( '', 'comment', qc_get_editor_settings() ); ?>

			<?php do_action( 'qc_ticket_form_after_basic_fields' ); ?>

		</fieldset>

		<fieldset>

			<legend>
				<?php _e( 'Update ticket properties', APP_TD ); ?>
			</legend>

			<?php if ( current_theme_supports( 'ticket-tags' ) ) : ?>

				<p id="ticket-tags">
					<label for="ticket_tags"><?php _e( 'Tags: <em>(Optional) Separated multiple tags with commas.</em>', APP_TD ); ?></label>
					<input type="text" name="ticket_tags" value="<?php echo qc_get_ticket_tags( $post->ID ); ?>" />
				</p>

			<?php endif; ?>

			<?php do_action( 'qc_ticket_form_advanced_fields', 'update' ); ?>

		</fieldset>

		<?php if ( current_theme_supports( 'ticket-attachments' ) ) : ?>

			<fieldset>

				<legend>
					<?php _e( 'Attach a File', APP_TD ) ; ?>
				</legend>

				<p id="ticket-attachment">
					<input type="file" name="ticket_attachment" id="ticket_attachment"/>
				</p>

			</fieldset>

		<?php endif; ?>

		<?php do_action( 'qc_ticket_form_after_fields' ); ?>

		<?php do_action( 'comment_form', $post->ID ); ?>

		<?php comment_id_fields(); ?>

		<p class="form-submit">
			<input type="submit" name="submit" value="<?php _e( 'Submit', APP_TD ); ?>" />
		</p>

		<?php do_action( 'qc_ticket_form_bottom' ); ?>
	</form>
</div>

<?php appthemes_after_comments_form(); ?>
