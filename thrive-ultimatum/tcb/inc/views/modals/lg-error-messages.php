<h2 class="tcb-modal-title"><?php echo __( 'Set error message' ) ?></h2>

<div class="tcb-fields-error row"></div>

<div class="clearfix padding-bottom-10 row">
	<div class="col col-xs-12">
		<button type="button" class="medium grey text-only tve-button click" data-fn="restore_defaults">
			<?php tcb_icon( 'close' ) ?>
			<?php echo __( 'Restore errors to default' ) ?>
		</button>
	</div>
</div>

<div class="tcb-gray" id="tcb-signup-error-wrapper" style="display: none">
	<div class="row">
		<div class="col-xs-12">
			<label class="tcb-checkbox padding-bottom-10">
				<input type="checkbox" id="tcb-sign-up-error-enabled">
				<span><?php echo __( "Add 'Signup failed' error message", 'thrive-cb' ) ?></span>
			</label>
		</div>
	</div>
	<div class="row">
		<div class="col col-xs-12">
			<p><?php echo __( "This error message is shown in the rare case that the signup fails. This can happen when your connected email marketing service can't be reached.", 'thrive-cb' ) ?></p>
		</div>
	</div>
	<div class="row" id="tcb-lg-signup-error-editor" style="display: none;">
		<div class="col-xs-12">
			<?php wp_editor( '', 'tcb_lg_error', array( 'quicktags' => false, 'media_buttons' => false ) ); ?>
		</div>
	</div>
</div>

<div class="tcb-modal-footer clearfix padding-top-20 row">
	<div class="col col-xs-6">
		<button type="button" class="tcb-left tve-button medium text-only grey tcb-modal-cancel">
			<?php echo __( 'Cancel', 'thrive-cb' ) ?>
		</button>
	</div>
	<div class="col col-xs-6">
		<button type="button" class="tcb-right tve-button medium green tcb-modal-save">
			<?php echo __( 'Save', 'thrive-cb' ) ?>
		</button>
	</div>
</div>
