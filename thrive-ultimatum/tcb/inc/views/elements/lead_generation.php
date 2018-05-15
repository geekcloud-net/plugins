<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

$errors = array(
	'email'            => __( 'Please enter a valid email address', 'thrive-cb' ),
	'phone'            => __( 'Please enter a valid phone number', 'thrive-cb' ),
	'password'         => __( 'Please enter a password', 'thrive-cb' ),
	'passwordmismatch' => __( 'The passwords do not match', 'thrive-cb' ),
	'required'         => __( 'Highlighted fields are required', 'thrive-cb' ),
);
?>
<?php if ( 0 ) : ?>
	<div class="thrv_wrapper thrv_lead_generation tcb-elem-placeholder">
	<span class="tcb-inline-placeholder-action with-icon">
		<?php tcb_icon( 'add', false, 'editor' ); ?>
		<?php echo __( 'Connect', 'thrive-cb' ); ?>
	</span>
	</div>
<?php else : ?>
	<div class="thrv_wrapper thrv_lead_generation tve-draggable tve-droppable edit_mode active_delete" data-connection="api"><input type="hidden" class="tve-lg-err-msg" value="{&quot;email&quot;:&quot;Email address invalid&quot;,&quot;phone&quot;:&quot;Phone number invalid&quot;,&quot;password&quot;:&quot;Password invalid&quot;,&quot;passwordmismatch&quot;:&quot;Password mismatch error&quot;,&quot;required&quot;:&quot;Required field missing&quot;}">
		<div class="thrv_lead_generation_container tve_clearfix">
			<form action="#" method="post" novalidate="">
				<div class="tve_lead_generated_inputs_container tve_clearfix">
					<div class="tve_lg_input_container tve_lg_input">
						<input type="text" data-field="name" name="name" placeholder="Name" data-placeholder="Name">
					</div>
					<div class="tve_lg_input_container tve_lg_input">
						<input type="email" data-field="email" data-required="1" data-validation="email" name="email" placeholder="Email" data-placeholder="Email">
					</div>
					<div class="tve_lg_input_container tve_submit_container tve_lg_submit">
						<button type="submit">Sign Up</button>
					</div>
				</div>
			</form>
		</div>
	</div>
<?php endif; ?>
