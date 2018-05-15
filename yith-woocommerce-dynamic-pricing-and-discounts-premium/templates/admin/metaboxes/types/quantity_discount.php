<?php
if(!defined('ABSPATH')){
	exit;
}
global $post;
extract( $args );
$db_value =  get_post_meta( $post->ID, $id, true );
//$db_value = $db_value === '' ? array() : $db_value;
$limit = empty( $db_value ) ? 1 : count( $db_value ) ;
$pricing_rules_options = YITH_WC_Dynamic_Pricing()->pricing_rules_options;

?>

<?php if ( function_exists( 'yith_field_deps_data' ) ) : ?>
<div id="<?php esc_attr_e( $id ); ?>-container" <?php echo yith_field_deps_data( $args ); ?> class="yith-plugin-fw-metabox-field-row">
	<?php else: ?>
    <div id="<?php esc_attr_e( $id ); ?>-container" <?php if ( isset( $deps ) ): ?> data-field="<?php echo $id ?>" data-dep="<?php echo $deps['ids'] ?>" data-value="<?php echo $deps['values'] ?>" <?php endif ?>>
		<?php endif; ?>
	<label for="<?php esc_attr_e( $id  );?>"><?php echo( $label);?></label>

	<div class="discount-table-rules-wrapper">
		<table class="discount-rules">
			<tr>
				<th><?php _e( 'Minimum Quantity', 'ywdpd' ) ?></th>
				<th><?php _e( 'Maximum Quantity', 'ywdpd' ) ?></th>
				<th><?php _e( 'Type of Discount', 'ywdpd' ) ?></th>
				<th><?php _e( 'Discount Amount', 'ywdpd' ) ?></th>
				<th></th>
			</tr>
			<?php

			for ( $i = 1; $i <= $limit; $i ++ ):
				$hide_first_remove = ( $i == 1 ) ? ' hide-remove' : '';
				if ( isset( $db_value[ $i ] ) ):
					?>
					<tr data-index="<?php echo $i ?>">
						<td>
							<input type="text"
							       name="<?php echo $name . "[{$i}][min_quantity]" ?>"
							       id="<?php echo $id . "[{$i}][min_quantity]" ?>"
							       value="<?php echo isset( $db_value[ $i ]['min_quantity'] ) ? $db_value[ $i ]['min_quantity'] : '' ?>"
							       placeholder="<?php _e( 'e.g. 5', 'ywdpd' ) ?>">
						</td>
						<td>
							<input type="text"
							       name="<?php echo $name . "[{$i}][max_quantity]" ?>"
							       id="<?php echo $id . "[{$i}][max_quantity]" ?>"
							       value="<?php echo isset( $db_value[ $i ]['max_quantity'] ) ? $db_value[ $i ]['max_quantity'] : '' ?>"
							       placeholder="<?php _e( 'e.g. 10 - * for unlimited items', 'ywdpd' ) ?>">
						</td>
						<td>
							<select
								name="<?php echo $name . "[{$i}][type_discount]" ?>"
								id="<?php echo $id . "[{$i}][type_discount]" ?>">
								<?php foreach ( $pricing_rules_options['type_of_discount'] as $key_type => $type ): ?>
									<option
										value="<?php echo $key_type ?>" <?php selected( $db_value[ $i ]['type_discount'], $key_type ) ?>><?php echo $type ?></option>
								<?php endforeach ?>
							</select>
						</td>
						<td>
							<input type="text"
							       name="<?php echo $name . "[{$i}][discount_amount]" ?>"
							       id="<?php echo $id . "[{$i}][discount_amount]" ?>"
							       value="<?php echo isset( $db_value[ $i ]['discount_amount'] ) ? $db_value[ $i ]['discount_amount'] : '' ?>"
							       placeholder="<?php _e( 'e.g. 50', 'ywdpd' ) ?>">
						</td>
						<td>
							<span class="add-row"></span><span
								class="remove-row <?php echo $hide_first_remove ?>"></span>
						</td>
					</tr>
					<?php
				else: ?>
					<tr data-index="1">
						<td>
							<input type="text" name="<?php echo $name . '[1][min_quantity]' ?>" id="<?php echo $id . '[1][min_quantity]' ?>" value="" placeholder="<?php _e( 'e.g. 5', 'ywdpd' ) ?>">
						</td>
						<td>
							<input type="text" name="<?php echo $name . '[1][max_quantity]' ?>" id="<?php echo $id . '[1][max_quantity]' ?>" value="" placeholder="<?php _e( 'e.g. 10 - * for unlimited items', 'ywdpd' ) ?>">
						</td>
						<td>
							<select name="<?php echo $name . '[1][type_discount]' ?>" id="<?php echo $id . '[1][type_discount]' ?>">
								<?php foreach ( $pricing_rules_options['type_of_discount'] as $key => $type ): ?>
									<option value="<?php echo $key ?>"><?php echo $type ?></option>
								<?php endforeach ?>
							</select>
						</td>
						<td>
							<input type="text" name="<?php echo $name . '[1][discount_amount]' ?>" id="<?php echo $id . '[1][discount_amount]' ?>" value="" placeholder="<?php _e( 'e.g. 50', 'ywdpd' ) ?>">
						</td>
						<td><span class="add-row"></span><span class="remove-row hide-remove"></span></td>
					</tr>
				<?php 
					endif;
			endfor; ?>
		</table>

	</div>

</div>