<label for="e-tooltip-text" class="panel-text"><?php echo __( 'Show Tooltip on Hover', 'thrive-cb' ) ?></label>
<input type="text" class="change" data-fn="text" placeholder="<?php echo __( 'Tooltip text', 'thrive-cb' ) ?>" id="e-tooltip-text">
<div class="tve-select-arrow mt-5">
	<label for="e-tooltip-position" class="panel-text"><?php echo __( 'Tooltip direction', 'thrive-cb' ) ?></label>
	<select id="e-tooltip-position" class="change tcb-dark" data-fn="pos">
		<?php foreach ( $data['positions'] as $direction => $title ) : ?>
			<option label="<?php echo esc_attr( $title ) ?>" value="<?php echo esc_attr( $direction ) ?>"><?php echo esc_html( $title ) ?></option>
		<?php endforeach; ?>
	</select>
</div>
<div class="tve-select-arrow mt-5">
	<label for="tooltip-style" class="panel-text"><?php echo __( 'Style', 'thrive-cb' ) ?></label>
	<select class="change t-style tcb-dark" data-fn="style" id="tooltip-style">
		<?php foreach ( $data['styles'] as $k => $s ) : ?>
			<option value="<?php echo esc_attr( $k ) ?>"><?php echo esc_html( $s ) ?></option>
		<?php endforeach ?>
	</select>
</div>
