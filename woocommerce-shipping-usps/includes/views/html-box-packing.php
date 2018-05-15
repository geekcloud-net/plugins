<tr valign="top" id="packing_options">
	<th scope="row" class="titledesc">
		<?php _e( 'Box Sizes', 'woocommerce-shipping-usps' ); ?>

		<img class="help_tip" data-tip="<?php _e( 'Items will be packed into these boxes based on item dimensions and volume. Outer dimensions will be passed to USPS, whereas inner dimensions will be used for packing. Items not fitting into boxes will be packed individually.', 'woocommerce-shipping-usps' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
	</th>
	<td class="forminp">
		<style type="text/css">
			.usps_boxes td, .usps_services td, .usps_boxes th, .usps_services th {
				vertical-align: middle;
				padding: 4px 7px;
			}
			.usps_boxes td input {
				margin-right: 4px;
			}
			.usps_boxes .check-column {
				vertical-align: middle;
				text-align: left;
				padding: 0 7px;
			}
			.usps_services th.sort {
				width: 16px;
			}
			.usps_services td.sort {
				cursor: move;
				width: 16px;
				padding: 0;
				cursor: move;
				background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAICAYAAADED76LAAAAHUlEQVQYV2O8f//+fwY8gJGgAny6QXKETRgEVgAAXxAVsa5Xr3QAAAAASUVORK5CYII=) no-repeat center;					}
		</style>
		<table class="usps_boxes widefat">
			<thead>
				<tr>
					<th class="check-column"><input type="checkbox" /></th>
					<th><?php _e( 'Name', 'woocommerce-shipping-usps' ); ?></th>
					<th><?php _e( 'L', 'woocommerce-shipping-usps' ); ?> (in)</th>
					<th><?php _e( 'W', 'woocommerce-shipping-usps' ); ?> (in)</th>
					<th><?php _e( 'H', 'woocommerce-shipping-usps' ); ?> (in)</th>
					<th><?php _e( 'Inner L', 'woocommerce-shipping-usps' ); ?> (in)</th>
					<th><?php _e( 'Inner W', 'woocommerce-shipping-usps' ); ?> (in)</th>
					<th><?php _e( 'Inner H', 'woocommerce-shipping-usps' ); ?> (in)</th>
					<th><?php _e( 'Weight of Box', 'woocommerce-shipping-usps' ); ?> (lbs)</th>
					<th><?php _e( 'Max Weight', 'woocommerce-shipping-usps' ); ?> (lbs)</th>
					<th><?php _e( 'Letter', 'woocommerce-shipping-usps' ); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th colspan="3">
						<a href="#" class="button plus insert"><?php _e( 'Add Box', 'woocommerce-shipping-usps' ); ?></a>
						<a href="#" class="button minus remove"><?php _e( 'Remove selected box(es)', 'woocommerce-shipping-usps' ); ?></a>
					</th>
					<th colspan="8">
						<small class="description"><?php esc_html_e( 'Note: If you are using regional rates, A1, A2, B1, and B2 sizes will be defined for you.', 'woocommerce-shipping-usps' ); ?></small>
					</th>
				</tr>
			</tfoot>
			<tbody id="rates">
				<?php
					if ( $this->boxes ) {
						foreach ( $this->boxes as $key => $box ) {
							?>
							<tr>
								<td class="check-column"><input type="checkbox" /></td>
								<td><input type="text" size="10" name="boxes_name[<?php echo $key; ?>]" value="<?php echo isset( $box['name'] ) ? esc_attr( $box['name'] ) : ''; ?>" /></td>
								<td><input type="text" size="5" name="boxes_outer_length[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['outer_length'] ); ?>" /></td>
								<td><input type="text" size="5" name="boxes_outer_width[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['outer_width'] ); ?>" /></td>
								<td><input type="text" size="5" name="boxes_outer_height[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['outer_height'] ); ?>" /></td>
								<td><input type="text" size="5" name="boxes_inner_length[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['inner_length'] ); ?>" /></td>
								<td><input type="text" size="5" name="boxes_inner_width[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['inner_width'] ); ?>" /></td>
								<td><input type="text" size="5" name="boxes_inner_height[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['inner_height'] ); ?>" /></td>
								<td><input type="text" size="5" name="boxes_box_weight[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['box_weight'] ); ?>" /></td>
								<td><input type="text" size="5" name="boxes_max_weight[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['max_weight'] ); ?>" /></td>
								<td><input type="checkbox" name="boxes_is_letter[<?php echo $key; ?>]" <?php checked( isset( $box['is_letter'] ) && $box['is_letter'] == true, true ); ?> /></td>
							</tr>
							<?php
						}
					}
				?>
			</tbody>
		</table>
		<script type="text/javascript">

			jQuery(window).load(function(){

				jQuery('#woocommerce_usps_enable_standard_services').change(function(){
					if ( jQuery(this).is(':checked') ) {
						jQuery('#woocommerce_usps_mediamail_restriction').closest('tr').show();
						jQuery('#service_options, #packing_options').show();
						jQuery('#woocommerce_usps_packing_method, #woocommerce_usps_shippingrates, #woocommerce_usps_origin').closest('tr').show();
						jQuery('#woocommerce_usps_packing_method').change();
					} else {
						jQuery('#woocommerce_usps_mediamail_restriction').closest('tr').hide();
						jQuery('#service_options, #packing_options').hide();
						jQuery('#woocommerce_usps_packing_method, #woocommerce_usps_shippingrates, #woocommerce_usps_origin').closest('tr').hide();
					}
				}).change();

				jQuery('#woocommerce_usps_packing_method').change(function(){

					if ( jQuery('#woocommerce_usps_enable_standard_services').is(':checked') ) {

						if ( jQuery(this).val() === 'box_packing' ) {
							jQuery('#packing_options').show();
							jQuery('#woocommerce_usps_unpacked_item_handling').closest('tr').show();
						} else {
							jQuery('#packing_options').hide();
							jQuery('#woocommerce_usps_unpacked_item_handling').closest('tr').hide();
						}

						if ( jQuery(this).val() === 'weight' ) {
							jQuery('#woocommerce_usps_max_weight').closest('tr').show();
						} else {
							jQuery('#woocommerce_usps_max_weight').closest('tr').hide();
						}

					}

				}).change();

				jQuery('#woocommerce_usps_enable_flat_rate_boxes').change(function(){

					if ( jQuery(this).val() === 'yes' ) {
						jQuery('#woocommerce_usps_flat_rate_express_title').closest('tr').show();
						jQuery('#woocommerce_usps_flat_rate_priority_title').closest('tr').show();
						jQuery('#woocommerce_usps_flat_rate_fee').closest('tr').show();
					} else if ( jQuery(this).val() === 'no' ) {
						jQuery('#woocommerce_usps_flat_rate_express_title').closest('tr').hide();
						jQuery('#woocommerce_usps_flat_rate_priority_title').closest('tr').hide();
						jQuery('#woocommerce_usps_flat_rate_fee').closest('tr').hide();
					} else if ( jQuery(this).val() === 'priority' ) {
						jQuery('#woocommerce_usps_flat_rate_express_title').closest('tr').hide();
						jQuery('#woocommerce_usps_flat_rate_priority_title').closest('tr').show();
						jQuery('#woocommerce_usps_flat_rate_fee').closest('tr').show();
					} else if ( jQuery(this).val() === 'express' ) {
						jQuery('#woocommerce_usps_flat_rate_express_title').closest('tr').show();
						jQuery('#woocommerce_usps_flat_rate_priority_title').closest('tr').hide();
						jQuery('#woocommerce_usps_flat_rate_fee').closest('tr').show();
					}

				}).change();

				jQuery('.usps_boxes .insert').click( function() {
					var $tbody = jQuery('.usps_boxes').find('tbody');
					var size = $tbody.find('tr').size();
					var code = '<tr class="new">\
							<td class="check-column"><input type="checkbox" /></td>\
							<td><input type="text" size="10" name="boxes_name[' + size + ']" /></td>\
							<td><input type="text" size="5" name="boxes_outer_length[' + size + ']" /></td>\
							<td><input type="text" size="5" name="boxes_outer_width[' + size + ']" /></td>\
							<td><input type="text" size="5" name="boxes_outer_height[' + size + ']" /></td>\
							<td><input type="text" size="5" name="boxes_inner_length[' + size + ']" /></td>\
							<td><input type="text" size="5" name="boxes_inner_width[' + size + ']" /></td>\
							<td><input type="text" size="5" name="boxes_inner_height[' + size + ']" /></td>\
							<td><input type="text" size="5" name="boxes_box_weight[' + size + ']" /></td>\
							<td><input type="text" size="5" name="boxes_max_weight[' + size + ']" /></td>\
							<td><input type="checkbox" name="boxes_is_letter[' + size + ']" /></td>\
						</tr>';

					$tbody.append( code );

					return false;
				} );

				jQuery('.usps_boxes .remove').click(function() {
					var $tbody = jQuery('.usps_boxes').find('tbody');

					$tbody.find('.check-column input:checked').each(function() {
						jQuery(this).closest('tr').hide().find('input').val('');
					});

					return false;
				});

				// Ordering
				jQuery('.usps_services tbody').sortable({
					items:'tr',
					cursor:'move',
					axis:'y',
					handle: '.sort',
					scrollSensitivity:40,
					forcePlaceholderSize: true,
					helper: 'clone',
					opacity: 0.65,
					placeholder: 'wc-metabox-sortable-placeholder',
					start:function(event,ui){
						ui.item.css('baclbsround-color','#f6f6f6');
					},
					stop:function(event,ui){
						ui.item.removeAttr('style');
						usps_services_row_indexes();
					}
				});

				function usps_services_row_indexes() {
					jQuery('.usps_services tbody tr').each(function(index, el){
						jQuery('input.order', el).val( parseInt( jQuery(el).index('.usps_services tr') ) );
					});
				};

			});

		</script>
	</td>
</tr>
