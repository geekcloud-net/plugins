<?php

global $wp_version;

$user = wp_get_current_user();

$show_password_fields = apply_filters( 'show_password_fields', true );
?>

<div id="respond">

	<form action="" method="post">
		<?php wp_nonce_field( 'app-edit-profile' ); ?>
		<input type="hidden" name="action" value="app-edit-profile" />

		<input type="hidden" name="user_id" value="<?php echo (int) $user->ID; ?>" />

		<fieldset>

		<legend><?php _e( 'Profile', APP_TD ); ?></legend>

		<?php do_action( 'appthemes_notices' ); ?>

			<p id="user-display-name">
				<label for="display_name"><?php _e( 'Display Name:', APP_TD ); ?></label>
				<input name="display_name" type="text" value="<?php echo esc_attr( $user->display_name ); ?>" />
			</p>

			<p id="user-email">
				<label for="email"><?php _e( 'Email:', APP_TD ); ?></label>
				<input name="email" type="text" value="<?php echo esc_attr( $user->user_email ); ?>" />
			</p>

			<?php if ( $show_password_fields ) : ?>

				<?php if ( $wp_version < 4.3 ): ?>

					<p id="user-password">
						<label for="password"><?php _e( 'New Password:', APP_TD ); ?></label>
						<input type="password" name="pass1" id="pass1" size="16" value="" autocomplete="off" /> <span class="description"><?php _e( 'If you would like to change the password type a new one. Otherwise leave this blank.', APP_TD ); ?></span>
						<br class="clear" />
						<input type="password" name="pass2" id="pass2" size="16" value="" autocomplete="off" /> <span class="description"><?php _e( 'Type your new password again.', APP_TD ); ?></span><br />
					</p>

					<div id="pass-strength-result"><?php _e( 'Strength indicator', APP_TD ); ?></div>
					<p class="pass-strength-hint"><span class="description"><?php _e( 'Your password should be at least seven characters long.', APP_TD ); ?></span></p>

				<?php else: ?>

					<div class="user-pass1-wrap manage-password">
						<p id="user-password">
							<label for="pass1"><?php _e( 'New Password:', APP_TD ); ?></label>
							<button type="button" class="button secondary wp-generate-pw hide-if-no-js"><?php _e( 'Generate Password', APP_TD ); ?></button>

							<span class="wp-pwd hide-if-js">
								<?php $initial_password = wp_generate_password( 24 ); ?>
								<input type="password" id="pass1" name="pass1" class="regular-text" autocomplete="off" data-reveal="1" data-pw="<?php echo esc_attr( $initial_password ); ?>" aria-describedby="pass-strength-result" />
								<input type="text" style="display:none" name="pass2" id="pass2" autocomplete="off" />
							</span>
						</p>

						<p class="wp-pwd hide-if-js">
							<button type="button" class="button secondary wp-hide-pw hide-if-no-js" data-start-masked="<?php echo (int) isset( $_POST['pass1'] ); ?>" data-toggle="0" aria-label="<?php esc_attr_e( 'Hide password' ); ?>">
								<span class="dashicons dashicons-hidden"></span>
								<span class="text"><?php _e( 'Hide', APP_TD ); ?></span>
							</button>
							<button type="button" class="button secondary wp-cancel-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Cancel password change', APP_TD ); ?>">
								<span class="text"><?php _e( 'Cancel', APP_TD ); ?></span>
							</button>
						</p>

						<div class="pass-strenght-indicator wp-pwd hide-if-no-js">
							<div id="pass-strength-result"><?php _e( 'Strength indicator', APP_TD ); ?></div>
							<p class="pass-strength-hint"><span class="description"><?php _e( 'Your password should be at least seven characters long.', APP_TD ); ?></span></p>
						</div>
					</div>

				<?php endif; ?>

			<?php endif; ?>

			<?php
				do_action( 'profile_personal_options', $user );
				do_action( 'show_user_profile', $user );
			?>

		</fieldset>

		<p class="form-submit">
			<input type="submit" value="<?php _e( 'Update profile', APP_TD ); ?>" />
		</p>

		<input type="hidden" name="admin_color" value="<?php echo esc_attr( $user->admin_color ); ?>" />
		<input type="hidden" name="rich_editing" value="<?php echo esc_attr( $user->rich_editing ); ?>" />
		<input type="hidden" name="comment_shortcuts" value="<?php echo esc_attr( $user->comment_shortcuts ); ?>" />

		<?php if ( _get_admin_bar_pref( 'front', $user->ID ) ) { ?>
			<input type="hidden" name="admin_bar_front" value="true" />
		<?php } ?>

	</form>

</div>

