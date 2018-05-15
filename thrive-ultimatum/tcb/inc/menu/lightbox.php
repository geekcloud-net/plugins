<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-lightbox-component" class="tve-component" data-view="Lightbox">
	<div class="action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo __( 'Thrive Lightbox options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>

		<div class="dropdown-content">
			<div class="tve-control" data-view="Switch"></div>
			<div class="close-controls">
				<hr>
				<div class="tve-control" data-view="CloseColor"></div>
				<div class="sep"></div>
				<div class="tve-control" data-view="BorderColor"></div>
				<div class="sep"></div>
				<div class="tve-control" data-view="IconBg"></div>
			</div>
			<hr>
			<div class="tve-control" data-view="OverlayColor"></div>
		</div>
	</div>
</div>
