<div class="row middle-xs tve-header-tabs">
    <div class="col-xs-6 tve-tab active click" data-fn="tab_click" data-list="settings">
		<span>
			<?php echo __( 'Settings', 'thrive-cb' ); ?>
		</span>
    </div>
	<?php if ( tcb_editor()->has_templates_tab() ) : ?>
        <div class="col-xs-6 tve-tab click" data-fn="tab_click" data-list="templates">
			<span>
				<?php echo apply_filters( 'tcb_templates_tab_name', __( 'Template Setup', 'thrive-cb' ) ); ?>
			</span>
        </div>
	<?php endif ?>
</div>

<div class="tabs-content row">

    <div class="settings-list col-xs-12" data-list="settings">
        <div class="tve-category" data-category="settings">Found in Settings</div>
		<?php if ( TCB_Editor::instance()->can_use_page_events() ) : ?>
            <div class="setting-item click" data-default-category="settings" data-fn="setting" data-setting="page_events" data-alternate="lightbox, thrivebox">
				<?php tcb_icon( 'event_manager' ); ?>
                <span class="tve-s-name">
				<?php echo __( 'Setup Page Events', 'thrive-cb' ); ?>
                </span>
            </div>
		<?php endif ?>
        <div class="setting-item click" data-default-category="settings" data-fn="setting" data-setting="edit_html" data-alternate="code">
			<?php tcb_icon( 'custom_html2' ); ?>
            <span class="tve-s-name">
			<?php echo __( 'Edit HTML', 'thrive-cb' ); ?>
            </span>
        </div>
        <div class="setting-item click" data-default-category="settings" data-fn="setting" data-setting="custom_css" data-alternate="code">
			<?php tcb_icon( 'css' ); ?>
            <span class="tve-s-name">
			<?php echo __( 'Custom CSS', 'thrive-cb' ); ?>
            </span>
        </div>
        <div class="setting-item click" data-default-category="settings" data-fn="setting" data-setting="reminders" data-alternate="">
			<?php tcb_icon( 'notif_off' ); ?>
            <span class="tve-s-name"></span>

        </div>
        <div class="setting-item click" data-default-category="settings" data-fn="setting" data-setting="editor_side" data-alternate="">
			<?php tcb_icon( 'switch_side' ); ?>
            <span class="tve-s-name">
			<?php echo __( 'Switch Editor Side', 'thrive-cb' ); ?>
            </span>
        </div>
    </div>
    <div class="settings-list col-xs-12" data-list="templates" style="display: none;">
        <div class="tve-category" data-category="template">Found in Template Setup</div>
		<?php if ( tcb_editor()->can_use_landing_pages() ) : ?>
            <div class="setting-item click lp-only" data-default-category="template" data-fn="setting" data-setting="lp_settings" data-alternate="">
				<?php tcb_icon( 'lp_settings' ); ?>
                <span class="tve-s-name">
				<?php echo __( 'Landing Page Settings', 'thrive-cb' ); ?>
                </span>
            </div>
            <div class="setting-item click lp-only" data-default-category="template" data-fn="setting" data-setting="save_template_lp" data-alternate="">
				<?php tcb_icon( 'save_usertemp' ); ?>
                <span class="tve-s-name">
				<?php echo __( 'Save Landing Page', 'thrive-cb' ); ?>
                </span>
            </div>
            <div class="setting-item click" data-default-category="template" data-fn="setting" data-setting="change_lp" data-alternate="">
				<?php tcb_icon( 'change_lp' ); ?>
                <span class="tve-s-name">
                </span>
            </div>
            <div class="setting-item click" data-default-category="template" data-fn="setting" data-setting="import_lp" data-alternate="">
				<?php tcb_icon( 'import_lp' ); ?>
                <span class="tve-s-name">
				<?php echo __( 'Import Landing Page', 'thrive-cb' ); ?>
                </span>
            </div>
            <div class="setting-item click lp-only" data-default-category="template" data-fn="setting" data-setting="export_lp" data-alternate="">
				<?php tcb_icon( 'export_lp' ); ?>
                <span class="tve-s-name">
				<?php echo __( 'Export Landing Page', 'thrive-cb' ); ?>
                </span>
            </div>
            <div class="setting-item click lp-only" data-default-category="template" data-fn="setting" data-setting="revert" data-alternate="">
				<?php tcb_icon( 'revert2theme' ); ?>
                <span class="tve-s-name">
				<?php echo __( 'Revert to Theme', 'thrive-cb' ); ?>
                </span>
            </div>
            <div class="setting-item click lp-only" data-default-category="template" data-fn="setting" data-setting="reset" data-alternate="">
				<?php tcb_icon( 'reset_2default' ); ?>
                <span class="tve-s-name">
				<?php echo __( 'Reset to Default', 'thrive-cb' ); ?>
                </span>
            </div>
		<?php endif ?>
		<?php if ( tcb_editor()->is_lightbox() ) : ?>
            <div class="setting-item click" data-default-category="template" data-fn="lightbox_settings" data-alternate="">
				<?php tcb_icon( 'lp_settings' ); ?>
                <span class="tve-s-name">
				<?php echo __( 'Thrive Lightbox Settings', 'thrive-cb' ); ?>
                </span>
            </div>
		<?php endif ?>
        <div class="setting-item click not-lp" data-default-category="template" data-fn="save_template" data-alternate="">
			<?php tcb_icon( 'content_templates' ); ?>
            <span class="tve-s-name">
			<?php echo __( 'Save Content as Template', 'thrive-cb' ); ?>
            </span>
        </div>
		<?php
		/**
		 * Action hook. Allows injecting custom menu options under the "Templates Setup" tab
		 */
		do_action( 'tcb_templates_setup_menu_items' )
		?>
    </div>
</div>

<div class="tve-custom-code-wrapper">
    <pre id="tve-custom-css-code"></pre>
    <div class="code-expand"><?php tcb_icon( 'a_up' ); ?></div>
    <div class="code-close"><?php tcb_icon( 'close' ); ?></div>
</div>
<div class="tve-editor-html-wrapper full-width">
    <pre id="tve-custom-html-code"></pre>
    <div class="tve-code-buttons-wrapper">
        <div class="code-button-check"><?php tcb_icon( 'check' ); ?></div>
        <div class="code-button-close"><?php tcb_icon( 'close' ); ?></div>
    </div>
</div>
