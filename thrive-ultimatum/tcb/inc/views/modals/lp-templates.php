<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<h2 class="tcb-modal-title"><?php echo __( 'Choose Landing Page Template', 'thrive-cb' ) ?></h2>
<?php if ( ! empty( $GLOBALS['tcb_lp_cloud_error'] ) ) : ?>
	<?php $support_link = '<a href="https://thrivethemes.com/forums/forum/plugins/thrive-architect/" title="Support Forum">' . __( 'Support Forum', 'thrive-cb' ) . '</a>' ?>
	<div class="cloud-lp-error message-inline">
		<div class="tcb-notification">
			<div class="tcb-notification-icon tcb-notification-icon-error">
				<?php tcb_icon( 'close2' ) ?>
			</div>
			<div class="tcb-notification-text">
				<div>
					<?php echo sprintf( __( 'An error was encountered while fetching Cloud Landing Page Templates. Please contact our %s and provide the following error message:', 'thrive-cb' ), $support_link ) ?>
					<pre style="color: #e74c3c"><?php echo esc_html( $GLOBALS['tcb_lp_cloud_error'] ) ?></pre>
				</div>
			</div>
		</div>
	</div>
<?php else : ?>
	<div class="margin-top-20">
		<?php echo __( 'Any changes you’ve made to the current landing page will be lost when you select a new template. We recommend you to save your current template first.', 'thrive-cb' ) ?>
	</div>
<?php endif ?>

<div class="tve-templates-wrapper">
	<div class="tve-header-tabs">
		<div class="tab-item active" data-content="default"><?php echo __( 'Default Templates', 'thrive-cb' ); ?></div>
		<div class="tab-item" data-content="saved"><?php echo __( 'Saved Landing Pages', 'thrive-cb' ); ?></div>
		<div class="tags-filter">
			<div class="tags-title">
				<?php echo __( 'Filter templates by tags', 'thrive-cb' ); ?>
				<?php tcb_icon( 'a_down' ); ?>
			</div>
			<div class="tags-select">
				<input class="tags-search" placeholder="<?php echo __( 'Search for tags', 'thrive-cb' ); ?>">
				<div class="template-tags"></div>
			</div>
		</div>
	</div>
	<div class="tve-tabs-content">
		<div class="tve-tab-content active" data-content="default">
			<div class="tve-default-templates-list"></div>
		</div>
		<div class="tve-tab-content" data-content="saved">
			<div class="tve-saved-templates-list expanded-set"></div>
		</div>
		<div class="tve-template-preview"></div>
	</div>
</div>

<div class="tcb-modal-footer clearfix padding-top-20 row end-xs">
	<div class="col col-xs-12">
		<button type="button" class="tcb-right tve-button medium green tcb-modal-save tcb-disabled">
			<?php echo __( 'Choose Template', 'thrive-cb' ) ?>
		</button>
	</div>
</div>

