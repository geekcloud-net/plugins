<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>
<div id="tve-social-component" class="tve-component" data-view="Social">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Social Sharing Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-key="type" data-view="ButtonGroup"></div>
		<hr>
		<div class="tve-control" data-key="style" data-initializer="style_control"></div>
		<hr>
		<div class="tve-control" data-key="orientation" data-view="ButtonGroup"></div>
		<hr>
		<div class="tve-control" data-key="size" data-view="Slider"></div>
		<hr>
		<div class="row middle-xs between-xs">
			<div class="col-xs-8">
				<span class="input-label"><?php echo __( 'Social Networks', 'thrive-cb' ) ?></span>
			</div>
			<div class="col-xs-4 tcb-text-right">
				<button class="blue tve-button click" data-fn="open_selector_panel"><?php echo __( 'Change', 'thrive-cb' ) ?></button>
			</div>
		</div>
		<div class="tve-control" data-key="selector" data-initializer="selector_control"></div>
		<div class="tve-control" data-key="preview" data-view="PreviewList"></div>
		<hr>
		<div class="tve-control" data-key="has_custom_url" data-view="Checkbox"></div>
		<div class="tve-control" data-key="custom_url" data-view="LabelInput"></div>
		<hr>
		<div class="row middle-xs between-xs">
			<div class="col-xs-9"><div class="tve-control" data-key="total_share" data-view="Checkbox"></div></div>
			<div class="col-xs-3" style="flex-basis: 22%;max-width: 22%"><div class="tve-control" data-key="counts" data-view="Input"></div></div>
		</div>
	</div>
</div>

