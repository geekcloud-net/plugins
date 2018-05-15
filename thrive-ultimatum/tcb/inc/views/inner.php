<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
/**
 * Stuff to be included in the inner frame
 */
?>
<div id="tcb-inner-actions">
	<div id="tcb-el-icons">
		<?php tcb_icon( 'trash', false, 'editor', 'no-blur' ); ?>
		<?php tcb_icon( 'copy', false, 'editor', 'no-blur' ); ?>
	</div>
	<div id="tcb-el-group-editing-icons" style="display: none;">
		<?php tcb_icon( 'lock-outline', false, 'editor', 'no-blur' ); ?>
		<?php tcb_icon( 'lock-open-outline', false, 'editor', 'no-blur' ); ?>
	</div>
	<span id="tcb-el-name" class="no-blur"></span>

	<div id="tcb-table-panel">
		<div class="tcb-btn-row above-element">
			<button class="tcb-table-btn" data-fn="addColumn"><?php tcb_icon( 'add', false, 'editor' ) ?><?php echo __( 'Add column', 'thrive-cb' ) ?></button>
			<button class="tcb-table-btn" data-fn="addRow"><?php tcb_icon( 'add', false, 'editor' ) ?><?php echo __( 'Add row', 'thrive-cb' ) ?></button>
			<button disabled class="tcb-table-btn m-disable m-enable-one" data-fn="split"><?php tcb_icon( 'split', false, 'editor' ) ?><?php echo __( 'Split', 'thrive-cb' ) ?></button>
			<button disabled class="tcb-table-btn m-disable m-enable-more" data-fn="merge"><?php tcb_icon( 'merge', false, 'editor' ) ?><?php echo __( 'Merge', 'thrive-cb' ) ?></button>
			<div class="tcb-panel-right">
				<button disabled class="tcb-table-btn tcb-btn-red m-disable m-enable-one"
						data-fn="removeColumn"><?php tcb_icon( 'delete', false, 'editor' ) ?><?php echo __( 'Remove column', 'thrive-cb' ) ?></button>
				<button disabled class="tcb-table-btn tcb-btn-red m-disable m-enable-one"
						data-fn="removeRow"><?php tcb_icon( 'delete', false, 'editor' ) ?><?php echo __( 'Remove row', 'thrive-cb' ) ?></button>
			</div>
		</div>
		<div class="tcb-btn-row below-element">
			<button disabled class="tcb-table-btn m-disable m-enable-one m-enable-more" data-fn="insertColumn" data-arg="after"><?php echo __( 'Insert column after', 'thrive-cb' ) ?></button>
			<button disabled class="tcb-table-btn m-disable m-enable-one m-enable-more" data-fn="insertColumn" data-arg="before"><?php echo __( 'Insert column before', 'thrive-cb' ) ?></button>
			<button disabled class="tcb-table-btn m-disable m-enable-one m-enable-more" data-fn="insertRow" data-arg="after"><?php echo __( 'Insert row after', 'thrive-cb' ) ?></button>
			<button disabled class="tcb-table-btn m-disable m-enable-one m-enable-more" data-fn="insertRow" data-arg="before"><?php echo __( 'Insert row before', 'thrive-cb' ) ?></button>
			<div class="tcb-panel-right">
				<button class="tcb-table-btn tcb-btn-green" data-fn="cancel"><?php echo __( 'Close', 'thrive-cb' ) ?></button>
			</div>
		</div>
	</div>
	<div id="tcb-toggle-panel" class="tve-remove-auxiliary-content">
		<div class="tcb-btn-row below-element">
			<div class="tcb-panel-right">
				<button class="tcb-toggle-btn tcb-btn-green tcb-click" data-fn="Components.toggle.close_editor"><?php echo __( 'Done', 'thrive-cb' ) ?></button>
			</div>
		</div>
	</div>
	<div id="tcb-lg-close" style="display: none;position:absolute">
		<button class="tcb-table-btn tcb-btn-green"><?php echo __( 'Save & Close', 'thrive-cb' ) ?></button>
	</div>
	<div id="tcb-postgrid-panel">
		<div class="tcb-panel-right">
			<button class="tcb-click tcb-postgrid-btn tcb-close-postgrid-btn tcb-btn-green" data-fn="Components.postgrid.close_grid_options"><?php echo __( 'Close', 'thrive-cb' ) ?></button>
		</div>
	</div>
	<img src="<?php echo tve_editor_css() ?>/images/drag-img.png" width="20" height="20" id="tcb-drag-img">

	<div id="tve-fr-toolbar"></div>
</div>