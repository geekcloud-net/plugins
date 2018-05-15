<?php if ( ! empty( $data['show_migrate_button'] ) ) : ?>
	<br>
	<div class="postbox" style="text-align: center;">
		<div class="inside">
			<?php echo __( 'You can upgrade this post / page to Thrive Architect. Upgrading the content will disable the default WP editor for this post and activate the Thrive Architect editor for it. This will allow you to have your content (text and images) saved if you want to disable Thrive Architect for this post. You will not lose any content during this action - all of your current WP editor content will get saved as a "Wordpress Content" element and appended to the end of the Thrive Architect content', 'thrive-cb' ) ?>
			<br><br>
			<a class="thrive-architect-edit-link" href="javascript:void(0)" data-edit="<?php echo $data['edit_url'] ?>" id="tcb2-migrate-post">
				<div class="thrive-architect-admin-icon-holder">
					<div class="thrive-architect-admin-icon"></div>
				</div>
				<div class="thrive-architect-admin-text">
					<?php echo __( 'Upgrade to Thrive Architect', 'thrive-cb' ) ?>
				</div>
			</a>
			<br/>
		</div>
	</div>
	<br>
<?php endif; ?>
<?php tve_enqueue_style( 'tve_architect_edit_links', tve_editor_css() . '/thrive-architect-edit-links.css' ); ?>
<br/>
<?php if ( ! $data['landing_page'] && ! empty( $data['tcb_enabled'] ) ) : ?>
	<div class="postbox" style="text-align: center;">
		<div class="inside">
			<?php echo __( 'You are currently using Thrive Architect to edit this content. You can continue editing with Thrive Architect or return to the default Wordpress editor', 'thrive-cb' ) ?>
			<br><br>
			<a class="thrive-architect-edit-link tcb-enable-editor" data-id="<?php echo $data['post_id'] ?>" href="<?php echo $data['edit_url'] ?>" id="thrive_preview_button" target="_blank">
				<div class="thrive-architect-admin-icon-holder">
					<div class="thrive-architect-admin-icon"></div>
				</div>
				<div class="thrive-architect-admin-text">
					<?php echo __( 'Edit with Thrive Architect', 'thrive-cb' ) ?>
				</div>
			</a>
			<?php echo __( 'or', 'thrive-cb' ) ?> <a href="javascript:void(0)" class="tcb-disable" id="tcb2-show-wp-editor"><?php echo __( 'Return to the WP editor', 'thrive-cb' ) ?></a>
		</div>
	</div>
	<div class="tcb-flags">
		<input disabled="disabled" type="hidden" name="tcb_disable_editor" id="tcb_disable_editor" value="<?php echo wp_create_nonce( 'tcb_disable_editor' ) ?>">
	</div>
<?php else : ?>
	<a class="thrive-architect-edit-link tcb-enable-editor" data-id="<?php echo $data['post_id'] ?>" href="<?php echo $data['edit_url'] ?>" id="thrive_preview_button" target="_blank">
		<div class="thrive-architect-admin-icon-holder">
			<div class="thrive-architect-admin-icon"></div>
		</div>
		<div class="thrive-architect-admin-text">
			<?php echo __( 'Edit with Thrive Architect', 'thrive-cb' ) ?>
		</div>
	</a>
<?php endif ?>
<?php if ( ! empty( $data['landing_page'] ) ) : ?>
	<br/><br/>
	<script type="text/javascript">
		function tve_confirm_revert_to_theme() {
			if ( confirm( "<?php echo __( 'Are you sure you want to DELETE all of the content that was created in this landing page and revert to the theme page?\nIf you click OK, any custom content you added to the landing page will be deleted.', 'thrive-cb' ) ?>" ) ) {
				location.href = location.href + '&tve_revert_theme=1';
			}
			return false;
		}
	</script>
	<div class="postbox" style="text-align: center;">
		<div class="inside">
			<?php echo __( 'You are currently using a Thrive Architect landing page to display this piece of content.', 'thrive-cb' ) ?>
			<br/>
			<?php echo __( "If you'd like to revert back to your theme template then click the button below:", 'thrive-cb' ) ?>
			<br/><br/>
			<a href="javascript:void(0)" onclick="tve_confirm_revert_to_theme()"
			   class="button"><?php echo __( 'Revert to theme template', 'thrive-cb' ) ?></a>
		</div>
	</div>
<?php endif ?>
