<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/9/2017
 * Time: 10:20 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-styledlist-component" class="tve-component" data-view="StyledList">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Styled List Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control tcb-text-center" data-view="ModalPicker"></div>
		<hr>
		<div class="tve-control" data-view="item_spacing"></div>
		<hr>
		<div class="tve-control" data-key="preview" data-initializer="list_preview_control"></div>
		<div class="tve-button click whitey dashed" data-fn-click="add_list_item">
			<?php echo __( 'Add new', 'thrive-cb' ); ?>
		</div>
	</div>
</div>
