<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div id="tve-lead_generation-component" class="tve-component" data-view="LeadGeneration">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Form Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">

		<div class="no-api tve-control api-connections-list" data-view="ApiConnections"></div>

		<div class="no-api tcb-text-center margin-top-15">
			<button class="tve-button grey long click" data-fn="edit_form_elements">
				<?php echo __( 'Edit Form Elements', 'thrive-cb' ) ?>
			</button>
		</div>

		<hr>

		<div class="tve-control" data-view="Captcha"></div>
		<a class="tcb-hidden info-link toggle-control" target="_blank" href="<?php echo admin_url( 'admin.php?page=tve_dash_api_connect' ) ?>">
			<span class="blue-text"><?php tcb_icon( 'info' ) ?></span>
			<span class="info-text"><?php echo __( 'Requires integration with Google ReCaptcha', 'thrive-cb' ) ?></span>
		</a>

		<div id="tcb-lg-captcha-controls" class="row middle-xs padding-top-10 tcb-hidden">
			<div class="col-xs-12">
				<div class="tve-control" data-view="CaptchaTheme"></div>
			</div>
			<div class="col-xs-12">
				<div class="tve-control padding-top-10" data-view="CaptchaType"></div>
			</div>
			<div class="col-xs-12">
				<div class="tve-control padding-top-10" data-view="CaptchaSize"></div>
			</div>
		</div>

		<hr class="no-api">

		<?php do_action( 'tcb_lead_generation_menu' ); ?>

		<div class="no-api tve-advanced-controls extend-grey">
			<div class="dropdown-header" data-prop="advanced">
				<span>
					<?php echo __( 'Advanced', 'thrive-cb' ); ?>
				</span>
				<i></i>
			</div>
			<div class="dropdown-content clear-top">
				<div class="no-api tcb-text-center">
					<button class="tve-button blue long click" data-fn="manage_error_messages">
						<?php echo __( 'Edit error messages', 'thrive-cb' ) ?>
					</button>
					<button class="tve-button click long blue multiple-services-connect margin-top-10" data-fn="mServiceConnect">
						<?php echo __( 'Connect to multiple services', 'thrive-cb' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
