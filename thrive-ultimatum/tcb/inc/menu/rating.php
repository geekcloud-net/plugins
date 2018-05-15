<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>
<div id="tve-rating-component" class="tve-component" data-view="Rating">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Star Rating Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="hide-states">
			<div class="tve-control" data-key="ratingValue" data-initializer="rating_value_control"></div>
			<hr>
			<div class="tve-control" data-key="style" data-initializer="rating_style_control"></div>
			<hr>
			<div class="tve-control" data-key="size" data-view="Slider"></div>
			<hr>
		</div>
		<div class="tve-control" data-key="background" data-view="ColorPicker"></div>
		<hr>
		<div class="tve-control" data-key="fill" data-view="ColorPicker"></div>
		<hr>
		<div class="tve-control" data-key="outline" data-view="ColorPicker"></div>
	</div>
</div>

