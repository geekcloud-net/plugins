<div id="lb-container"></div>

<div class="tve-select-arrow pt-10">
	<select class="tcb-dark" id="lb-animation">
		<?php foreach ( $data as $k => $s ) : ?>
			<option value="<?php echo esc_attr( $k ) ?>"><?php echo esc_html( $s ) ?></option>
		<?php endforeach ?>
	</select>
</div>
