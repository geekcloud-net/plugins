<?php global $qc_options; ?>

<div id="respond">

	<form id="create-ticket" action="" method="post" enctype="multipart/form-data">

		<?php do_action( 'qc_ticket_form_top' ); ?>

		<fieldset>

			<legend>
				<?php _e( 'Basic Information', APP_TD ); ?>
			</legend>

			<?php do_action( 'qc_ticket_form_before_basic_fields' ); ?>

			<p id="ticket-title">
				<label for="ticket_title"><?php _e( 'Title:', APP_TD ); ?></label>
				<input type="text" name="ticket_title" value="" class="required" />
			</p>

			<p id="ticket-description">
				<label for="comment"><?php _e( 'Description:', APP_TD ); ?></label>
			</p>
			<?php wp_editor( '', 'comment', qc_get_editor_settings( array( 'editor_class' => 'required' ) ) ); ?>

			<?php if ( current_theme_supports( 'ticket-tags' ) ) : ?>

				<p id="ticket-tags">
					<label for="ticket_tags"><?php _e( 'Tags: <em>(Optional)</em>', APP_TD ); ?></label>
					<input type="text" name="ticket_tags" value="" />
				</p>

			<?php endif; ?>

			<?php do_action( 'qc_ticket_form_after_basic_fields' ); ?>

		</fieldset>

		<fieldset>

			<legend>
				<?php _e( 'Ticket Properties', APP_TD ); ?>
			</legend>

			<?php do_action( 'qc_ticket_form_advanced_fields', 'create' ); ?>

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

		<p class="form-submit">
			<input type="submit" name="submit" value="<?php _e( 'Create Ticket', APP_TD ); ?>" />

			<input type="hidden" name="action" value="qc-create-ticket" />
			<?php wp_nonce_field( 'qc-create-ticket' ); ?>
		</p>

		<?php do_action( 'qc_ticket_form_bottom' ); ?>

	</form>

</div>

