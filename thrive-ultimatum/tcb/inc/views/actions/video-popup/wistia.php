<div class="row">
	<div class="col-xs-2">
		<label for="v-w-url">URL</label>
	</div>
	<div class="col-xs-10">
		<input type="text" data-setting="url" class="w-url tcb-dark" id="v-w-url">
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
		<label class="tcb-checkbox"><input type="checkbox" data-setting="p" value="1"><span><?php echo __( 'Playbar', 'thrive-cb' ) ?></span></label>
		<label class="tcb-checkbox"><input type="checkbox" data-setting="hfs" value="1"><span><?php echo __( 'Hide full-screen button', 'thrive-cb' ) ?></span></label>
	</div>
</div>