<tr valign="top" id="service_options">
	<th scope="row" class="titledesc"><?php _e( 'Services', 'woocommerce-shipping-fedex' ); ?></th>
	<td class="forminp">
		<table class="fedex_services widefat">
			<thead>
				<th class="sort">&nbsp;</th>
				<th><?php _e( 'Service Code', 'woocommerce-shipping-fedex' ); ?></th>
				<th><?php _e( 'Name', 'woocommerce-shipping-fedex' ); ?></th>
				<th><?php _e( 'Enabled', 'woocommerce-shipping-fedex' ); ?></th>
				<th><?php echo sprintf( __( 'Price Adjustment (%s)', 'woocommerce-shipping-fedex' ), get_woocommerce_currency_symbol() ); ?></th>
				<th><?php _e( 'Price Adjustment (%)', 'woocommerce-shipping-fedex' ); ?></th>
			</thead>
			<tbody>
				<?php
					$sort = 0;
					$this->ordered_services = array();

					foreach ( $this->services as $code => $name ) {

						if ( isset( $this->custom_services[ $code ]['order'] ) ) {
							$sort = $this->custom_services[ $code ]['order'];
						}

						while ( isset( $this->ordered_services[ $sort ] ) )
							$sort++;

						$this->ordered_services[ $sort ] = array( $code, $name );

						$sort++;
					}

					ksort( $this->ordered_services );

					foreach ( $this->ordered_services as $value ) {
						$code = $value[0];
						$name = $value[1];
						?>
						<tr>
							<td class="sort"><input type="hidden" class="order" name="fedex_service[<?php echo $code; ?>][order]" value="<?php echo isset( $this->custom_services[ $code ]['order'] ) ? $this->custom_services[ $code ]['order'] : ''; ?>" /></td>
							<td><strong><?php echo $code; ?></strong></td>
							<td><input type="text" name="fedex_service[<?php echo $code; ?>][name]" placeholder="<?php echo $name; ?>" value="<?php echo isset( $this->custom_services[ $code ]['name'] ) ? $this->custom_services[ $code ]['name'] : ''; ?>" size="50" /></td>
							<td><input type="checkbox" name="fedex_service[<?php echo $code; ?>][enabled]" <?php checked( ( ! isset( $this->custom_services[ $code ]['enabled'] ) || ! empty( $this->custom_services[ $code ]['enabled'] ) ), true ); ?> /></td>
							<td><input type="text" name="fedex_service[<?php echo $code; ?>][adjustment]" placeholder="N/A" value="<?php echo isset( $this->custom_services[ $code ]['adjustment'] ) ? $this->custom_services[ $code ]['adjustment'] : ''; ?>" size="4" /></td>
							<td><input type="text" name="fedex_service[<?php echo $code; ?>][adjustment_percent]" placeholder="N/A" value="<?php echo isset( $this->custom_services[ $code ]['adjustment_percent'] ) ? $this->custom_services[ $code ]['adjustment_percent'] : ''; ?>" size="4" /></td>
						</tr>
						<?php
					}
				?>
			</tbody>
		</table>
	</td>
</tr>