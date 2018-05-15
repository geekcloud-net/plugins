<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/27/2017
 * Time: 10:13 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-postgrid-component" class="tve-component" data-view="PostGrid">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Post Grid Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tcb-text-center">
			<button class="tve-button grey long click" data-fn="edit_grid_options"><?php echo __( 'EDIT GRID OPTIONS', 'thrive-cb' ); ?></button>
		</div>
		<hr>
		<div class="tve-control" data-view="img_height"></div>
		<hr>
		<div class="tve-control" data-view="title_font_size"></div>
		<hr>
		<div class="tve-control" data-view="title_line_height"></div>
		<hr>
		<div class="tve-control" data-view="read_more"></div>
		<div class="tve-control margin-top-10" data-view="read_more_color"></div>
	</div>
</div>
