<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-fillcounter-component" class="tve-component" data-view="FillCounter">
	<div class="action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo __( 'Fill Counter Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="hide-states">
				<div class="tve-control" data-view="CounterSize"></div>
				<hr>
				<div class="tve-control" data-view="FillPercent"></div>
				<hr>
			</div>
			<div class="tve-control" data-view="CircleColor"></div>
			<div class="tve-control margin-top-10" data-view="FillColor"></div>
			<div class="tve-control margin-top-10" data-view="InnerColor"></div>
		</div>
	</div>
</div>
