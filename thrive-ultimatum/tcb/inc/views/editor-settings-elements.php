<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 1/14/2018
 * Time: 11:40 AM
 */

?>

<div class="tve-sidebar-settings ">
    <div class="tabs-content row">
        <div class="col-xs-12 settings-list">
            <div class="tve-category" data-category="More Options">Found In More Options</div>
            <a href="<?php echo tcb_get_preview_url() ?>" target="_blank">
                <div class="setting-item " data-default-category="More Options" data-alternate="more">
					<?php tcb_icon( 'preview' ) ?>
                    <span class="tve-s-name">
					<?php echo __( 'Preview', 'thrive-cb' ) ?>
                    </span>
                </div>
            </a>
			<?php if ( tcb_editor()->has_revision_manager() ) : ?>
                <div class="setting-item click " data-default-category="More Options" data-fn="follow" data-func="revisions" data-alternate="more">
					<?php tcb_icon( 'revision_mng' ) ?>
                    <span class="tve-s-name">
					<?php echo __( 'Revision Manager', 'thrive-cb' ) ?>
                    </span>
                </div>
			<?php endif; ?>
            <div id="tcb-undo-sim" class="tve-disabled setting-item click" data-default-category="More Options" data-fn="follow" data-func="undo"
                 data-alternate="more">
				<?php tcb_icon( 'undo' ) ?>
                <span class="tve-s-name">
				<?php echo __( 'Undo (Ctrl + Z)', 'thrive-cb' ) ?>
                </span>
            </div>
            <div id="tcb-redo-sim" class="tve-disabled setting-item click" data-default-category="More Options" data-fn="follow" data-func="redo"
                 data-alternate="more">
				<?php tcb_icon( 'redo' ) ?>
                <span class="tve-s-name">
				<?php echo __( 'Redo (Ctrl + Y)', 'thrive-cb' ) ?>
                </span>
            </div>
            <a href="<?php echo tcb_get_editor_close_url() ?>">
                <div class="setting-item" data-default-category="More Options" data-alternate="more">
					<?php tcb_icon( 'exit' ) ?>
                    <span class="tve-s-name">
					<?php echo __( 'Exit Thrive Architect', 'thrive-cb' ) ?>
                    </span>
                </div>
            </a>

            <div class="tve-category" data-category="Responsive view">Found In Responsive view</div>
            <div class="setting-item click selected" data-fn="follow" data-default-category="Responsive view" data-func="change_preview" data-device="desktop"
                 data-alternate="responsive">
				<?php tcb_icon( 'responsive' ); ?>
                <span class="tve-s-name">
				<?php echo __( 'Desktop', 'thrive-cb' ) ?>
                </span>
            </div>
            <div class="setting-item click" data-fn="follow" data-default-category="Responsive view" data-func="change_preview" data-device="tablet"
                 data-alternate="responsive">
				<?php tcb_icon( 'tablet2' ); ?>
                <span class="tve-s-name">
				<?php echo __( 'Tablet', 'thrive-cb' ) ?>
                </span>
            </div>
            <div class="setting-item click" data-fn="follow" data-default-category="Responsive view" data-func="change_preview" data-device="mobile"
                 data-alternate="responsive">
				<?php tcb_icon( 'mobile' ); ?>
                <span class="tve-s-name">
				<?php echo __( 'Mobile', 'thrive-cb' ) ?>
                </span>
            </div>

        </div>
    </div>
</div>