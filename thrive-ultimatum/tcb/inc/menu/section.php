<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-section-component" class="tve-component" data-view="Section">
	<div class="borders-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo __( 'Section Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tve-control" data-view="SectionFullWidth"></div>
			<hr>
			<div class="tve-control" data-view="ContentWidth"></div>
			<div class="tve-control" data-view="ContentFullWidth"></div>
			<hr>
			<div class="tve-control" data-view="SectionHeight"></div>
			<div class="tve-control" data-view="FullHeight"></div>
			<hr>
			<div class="row">
				<div class="col-xs-12">
					<div class="tve-control" data-view="VerticalPosition"></div>
				</div>
			</div>
		</div>
	</div>
</div>

