<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/3/2017
 * Time: 10:03 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-numberedlist-component" class="tve-component" data-view="NumberedList">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Number List Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="starting_number"></div>
		<hr>
		<div class="tve-control" data-view="increment_number"></div>
		<hr>
		<div class="row tve-control" data-view="FontFace">
			<div class="col-xs-12">
				<span class="input-label"><?php echo __( 'Number Font', 'thrive-cb' ); ?></span>
			</div>
			<div class="col-xs-12 tcb-input-button-wrapper">
				<div class="col-sep click" data-fn="openFonts"></div>
				<input type="text" class="font-face-input click" data-fn="openFonts" readonly>
				<?php tcb_icon( 'edit', false, 'sidebar', 'tcb-input-button click', array( 'data-fn' => 'openFonts' ) ) ?>
			</div>
		</div>
		<hr>
		<div class="tve-control" data-view="item_spacing"></div>
		<hr>
		<div class="tve-control" data-key="preview" data-initializer="list_preview_control"></div>
		<div class="tve-button click whitey dashed" data-fn-click="add_list_item">
			<?php echo __( 'Add new', 'thrive-cb' ); ?>
		</div>
	</div>
</div>
