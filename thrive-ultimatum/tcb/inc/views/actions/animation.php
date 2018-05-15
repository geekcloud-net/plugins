<div class="animation-container tve-select-arrow">
	<label for="anim-animation"><?php esc_html_e( 'Animation', 'thrive-cb' ) ?></label>
	<select class="change tcb-dark" data-fn="select" id="anim-animation">
		<?php foreach ( $data as $key => $group ) : ?>
			<optgroup label="<?php echo esc_attr( $group['title'] ) ?>">
				<?php foreach ( $group['items'] as $k => $item ) : ?>
					<option value="<?php echo esc_attr( $k ) ?>" label="<?php echo esc_attr( $item['title'] ) ?>"><?php echo esc_html( $item['title'] ) ?></option>
				<?php endforeach ?>
			</optgroup>
		<?php endforeach ?>
	</select>
</div>
<div class="sep"></div>
<div class="trigger-container tve-select-arrow" id="anim-trigger" style="display: none">
	<label for="animation-trigger"><?php echo __( 'Animation Trigger', 'thrive-cb' ) ?></label>
	<select id="animation-trigger" class="change tcb-select tcb-dark" data-fn="change_trigger"></select>
</div>

<label><input type="checkbox" class="anim-loop tcb-checkbox"><?php esc_html_e( 'Loop animation', 'thrive-cb' ) ?></label>

