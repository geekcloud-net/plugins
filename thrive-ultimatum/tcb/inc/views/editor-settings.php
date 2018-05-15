<div class="tve-more click" data-fn="open_panel" data-target=".tcb-more">
	<div data-tooltip="<?php echo __( 'More Options', 'thrive-cb' ) ?>">
		<?php tcb_icon( 'more' ); ?>
	</div>
	<div class="tcb-settings-panel tcb-absolute tcb-more">
		<a href="<?php echo tcb_get_preview_url() ?>" target="_blank" class="row middle-xs panel-item tcb-text-left preview-content">
			<span class="col-xs-2 tcb-text-center"><?php tcb_icon( 'preview' ) ?></span>
			<span class="col-xs-10"><?php echo __( 'Preview', 'thrive-cb' ) ?></span>
		</a>
		<?php if ( tcb_editor()->has_revision_manager() ) : ?>
			<span class="row middle-xs panel-item tcb-text-left click" data-fn="revisions">
				<span class="col-xs-2 tcb-text-center"><?php tcb_icon( 'revision_mng' ) ?></span>
				<span class="col-xs-10"><?php echo __( 'Revision Manager', 'thrive-cb' ) ?></span>
			</span>
		<?php endif; ?>
		<span id="tcb-undo" class="tve-disabled row middle-xs panel-item tcb-text-left click" data-fn="undo">
			<span class="col-xs-2 tcb-text-center"><?php tcb_icon( 'undo' ) ?></span>
			<span class="col-xs-10"><?php echo __( 'Undo (Ctrl + Z)', 'thrive-cb' ) ?></span>
		</span>
		<span id="tcb-redo" class="tve-disabled row middle-xs panel-item tcb-text-left click" data-fn="redo">
			<span class="col-xs-2 tcb-text-center"><?php tcb_icon( 'redo' ) ?></span>
			<span class="col-xs-10"><?php echo __( 'Redo (Ctrl + Y)', 'thrive-cb' ) ?></span>
		</span>
		<a href="<?php echo tcb_get_editor_close_url() ?>" class="row middle-xs panel-item tcb-text-left">
			<span class="col-xs-2 tcb-text-center"><?php tcb_icon( 'exit' ) ?></span>
			<span class="col-xs-10"><?php echo __( 'Exit Thrive Architect', 'thrive-cb' ) ?></span>
		</a>
	</div>
</div>
<div class="tve-responsive click" data-fn="open_panel" data-target=".tcb-responsive-options">
	<div class="selected-device" data-tooltip="<?php echo __( 'Responsive View', 'thrive-cb' ) ?>">
		<?php tcb_icon( 'responsive' ); ?>
	</div>
	<div class="tcb-responsive-options tcb-absolute tcb-settings-panel" style="display: none">
		<div class="row middle-xs click selected" data-fn="change_preview" data-device="desktop">
			<div class="col-xs-2 tcb-text-center">
				<?php tcb_icon( 'responsive' ); ?>
			</div>
			<div class="col-xs-5">
				<?php echo __( 'Desktop', 'thrive-cb' ) ?>
			</div>
			<div class="col-xs-5 tcb-text-right device-desc">
				<?php echo __( '100% width', 'thrive-cb' ) ?>
			</div>
		</div>
		<div class="row middle-xs click" data-fn="change_preview" data-device="tablet">
			<div class="col-xs-2 tcb-text-center">
				<?php tcb_icon( 'tablet2' ); ?>
			</div>
			<div class="col-xs-5">
				<?php echo __( 'Tablet', 'thrive-cb' ) ?>
			</div>
			<div class="col-xs-5 tcb-text-right device-desc">
				<?php echo __( '768px width', 'thrive-cb' ) ?>
			</div>
		</div>
		<div class="row middle-xs click" data-fn="change_preview" data-device="mobile">
			<div class="col-xs-2 tcb-text-center">
				<?php tcb_icon( 'mobile' ); ?>
			</div>
			<div class="col-xs-5">
				<?php echo __( 'Mobile', 'thrive-cb' ) ?>
			</div>
			<div class="col-xs-5 tcb-text-right device-desc">
				<?php echo __( '360px width', 'thrive-cb' ) ?>
			</div>
		</div>
	</div>
</div>
<div class="tve-templates-setup click" data-tooltip="<?php echo __( 'Page Setup', 'thrive-cb' ) ?>" data-fn="sidebar_settings"><?php tcb_icon( 'page_setup' ); ?></div>
<div>
	<div class="tve-save click" data-fn="save">
		<?php echo __( 'Save', 'thrive-cb' ); ?>
	</div>
</div>
