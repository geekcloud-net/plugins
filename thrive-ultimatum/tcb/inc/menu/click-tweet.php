<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-click-tweet-component" class="tve-component" data-view="Tweet">
	<div class="borders-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo __( 'Click to Tweet Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tve-control" data-view="LabelText"></div>
			<div class="tve-control margin-top-15" data-view="TweetText"></div>
			<div class="tve-twitter-count"></div>
			<hr>
			<div class="tve-control" data-view="ShareUrlCheckbox"></div>
			<div class="tve-control" data-view="ShareUrlInput"></div>
			<div class="tve-control margin-top-10 tve-tweet-via-control" data-view="ViaUsername"></div>
			<div class="row middle-xs  margin-top-10">
				<div class="col-xs-8"></div>
				<div class="col-xs-4 tcb-text-right">
					<button class="tve-button click blue" data-fn="preview"><?php echo __( 'Preview', 'thrive-cb' ); ?></button>
				</div>
			</div>
		</div>
	</div>
</div>
