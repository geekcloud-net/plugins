<div class="row">
	<div class="col-xs-2">
		<label for="v-v-url">URL</label>
	</div>
	<div class="col-xs-10">
		<input type="text" data-setting="url" class="v-url tcb-dark" id="v-v-url">
	</div>
</div>
<div class="inline-message"></div>
<div class="extra-settings">
	<div class="row middle-xs">
		<div class="col col-xs-8">
			<?php echo __( 'Player color', 'thrive-cb' ) ?>
		</div>
		<div class="col-xs-4 tcb-text-right v-setting-color"></div>
	</div>
	<div class="inline-checkboxes">
		<label class="tcb-checkbox"><input type="checkbox" data-setting="a" value="1" checked="checked"><span><?php echo __( 'Autoplay', 'thrive-cb' ) ?></span></label>
		<label class="tcb-checkbox"><input type="checkbox" data-setting="l" value="1"><span><?php echo __( 'Loop', 'thrive-cb' ) ?></span></label>
		<label class="tcb-checkbox"><input type="checkbox" data-setting="p" value="1"><span><?php echo __( 'Show portrait', 'thrive-cb' ) ?></span></label>
		<label class="tcb-checkbox"><input type="checkbox" data-setting="t" value="1"><span><?php echo __( 'Show title', 'thrive-cb' ) ?></span></label>
		<label class="tcb-checkbox"><input type="checkbox" data-setting="b" value="1"><span><?php echo __( 'Show byline', 'thrive-cb' ) ?></span></label>
	</div>
	<div class="small-margin-top">
		<span class="info-text-white"><?php echo tcb_icon( 'info' ) ?></span>
		<span class="info-text-white">
			<?php echo __( 'Title, portrait and byline are visible only when the video is stopped.' ); ?>
		</span>
	</div>
</div>