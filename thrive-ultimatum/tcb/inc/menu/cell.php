<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-cell-component" class="tve-component" data-view="Cell">
	<div class="table-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo __( 'Table Cell Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>

		<div class="dropdown-content">
			<div class="tve-control" data-key="width"></div>
			<div class="tve-control" data-key="height"></div>
			<div class="tve-control" data-key="valign"></div>
			<hr>
		</div>
	</div>
</div>
