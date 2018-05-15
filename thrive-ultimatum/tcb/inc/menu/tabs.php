<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-tabs-component" class="tve-component" data-view="TabContent" >
	<div class="action-group" >
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo __( 'Tabs Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tve-control" data-view="TabLayout"></div>
			<hr>
			<div class="tve-control margin-top-10" data-view="TabsWidth"></div>
			<hr>
			<div class="tve-control margin-top-10" data-view="DefaultTab"></div>
			<div class="tcb-text-center margin-top-20">
				<button class="tve-button grey long click" data-fn="addTabs"><?php echo __( 'Add New Tabs', 'thrive-cb' ) ?></button>
			</div>
			<hr>
			<div class="tve-control margin-top-10" data-view="EditTabs"></div>
			<div class="tve-control margin-top-10" data-view="TabBackground"></div>
			<div class="tve-control margin-top-10" data-view="TabBorder"></div>
			<div class="tve-advanced-controls extend-grey">
				<div class="dropdown-header" data-prop="advanced">
				<span>
					<?php echo __( 'Advanced', 'thrive-cb' ); ?>
				</span>
					<i></i>
				</div>

				<div class="dropdown-content clear-top">
					<div class="tve-control margin-top-20" data-view="ContentColor"></div>
					<div class="tve-control margin-top-10" data-view="ContentBorder"></div>
				</div>
			</div>
		</div>
	</div>
</div>

