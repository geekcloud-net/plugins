<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-columns-component" class="tve-component" data-view="Columns">
	<div class="action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo __( 'Column Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>

		<div class="dropdown-content">
			<div class="row">
				<div class="col-xs-12">
					<div class="tve-control" data-view="GutterWidth"></div>
				</div>
			</div>
			<hr>
			<div class="tve-control" data-view="MinHeight"></div>
			<hr>
			<div class="row">
				<div class="col-xs-12">
					<div class="tve-control" data-view="VerticalPosition"></div>
				</div>
				<hr>
				<div class="col-xs-12">
					<div class="tve-control" data-view="ColumnsOrder"></div>
				</div>
				<div class="col-xs-12 hide-desktop hide-mobile margin-bottom-5">
					<hr>
					<div class="">
						<div class="tve-control" data-view="MediumWrap"></div>
					</div>
				</div>
				<div class="col-xs-12 hide-mobile">
					<div class="tve-control" data-view="ColumnWidth"></div>
				</div>
				<div class="col-xs-12">
					<hr>
					<div class="tve-control" data-view="FullWidth"></div>
				</div>
			</div>
		</div>
	</div>
</div>
