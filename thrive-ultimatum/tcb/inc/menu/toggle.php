<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-toggle-component" class="tve-component" data-view="ContentToggle" >
	<div class="action-group" >
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo __( 'Toggle Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tve-control" data-view="HoverColor"></div>
			<hr>
			<div class="tve-control tve-toggle-functions" data-key="Toggle" data-initializer="toggle"></div>
		</div>
	</div>
</div>

