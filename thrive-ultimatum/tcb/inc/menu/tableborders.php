<div id="tve-tableborders-component" class="tve-component" data-view="TableBorders">
	<div class="borders-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo __( 'Table Borders', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tve-control" data-key="Borders" data-initializer="table_borders"></div>
			<div class="tve-control" data-key="InnerBorders" data-view="Checkbox"></div>
			<div class="padding-top-10 inner-border" style="display: none">
				<div id="h-border"><strong><?php echo __( 'Header border', 'thrive-cb' ) ?></strong></div>
				<div class="tve-control" data-key="border_th" data-initializer="table_borders"></div>
				<hr>
				<div id="c-border"><strong><?php echo __( 'Cell border', 'thrive-cb' ) ?></strong></div>
				<div class="tve-control" data-key="border_td" data-initializer="table_borders"></div>
			</div>
		</div>
	</div>
</div>
