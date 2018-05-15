<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-contentbox-component" class="tve-component" data-view="ContentBox" >
	<div class="action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo __( 'Content Box Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tve-control" data-view="BoxWidth"></div>
			<hr>
			<div class="tve-control" data-view="BoxHeight"></div>
			<hr>
			<div class="row">
				<div class="col-xs-12">
					<div class="tve-control" data-view="VerticalPosition"></div>
				</div>
			</div>
		</div>
	</div>
</div>

