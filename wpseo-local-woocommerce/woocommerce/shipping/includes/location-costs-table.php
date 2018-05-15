<?php
$cost_desc = __( 'Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>.', 'yoast-local-seo-woocommerce' ) . '<br/><br/>' . __( 'Use <code>[qty]</code> for the number of items, <br/><code>[cost]</code> for the total cost of items, and <code>[fee percent="10" min_fee="20" max_fee=""]</code> for percentage based fees.', 'yoast-local-seo-woocommerce' );
?>
<tr valign="top" class="wpseo_local_shipping_costs">
	<th scope="row" class="titledesc">
		<?php _e( 'Cost per location', 'yoast-local-seo-woocommerce' ); ?>
		<?php if( is_array( $this->location_categories ) && count( $this->location_categories ) > 0 ) {
			echo '<p>' . __( 'These settings will override any category specific settings made above.', 'yoast-local-seo-woocommerce' ) . '</p>';
		}
		?>
	</th>
	<td class="forminp" id="<?php echo $this->id; ?>_locations">
		<table class="shippingrows widefat" cellspacing="0">
			<caption class="screen-reader-text"><?php _e( 'Cost per location', 'yoast-local-seo-woocommerce' ); ?></caption>
			<thead>
			<tr>
				<th scope="col" class="check-column"></th>
				<th scope="col"><?php _e( 'Location', 'yoast-local-seo-woocommerce' ); ?></th>
				<th scope="col"><?php _e( 'Allow local pickup', 'yoast-local-seo-woocommerce' ); ?> <a class="tips" data-tip="<?php _e( 'Whether or not to allow local pickup from this location.', 'yoast-local-seo-woocommerce' ); ?>">[?]</a></th>
				<th scope="col"><?php _e( 'Costs', 'yoast-local-seo-woocommerce' ); ?> <a class="tips" data-tip="<?php echo esc_attr( $cost_desc );  ?>">[?]</a></th>
				<th scope="col"></th>
			</tr>
			</thead>
			<tbody id="shipping_locations" class="locations">
			<?php
			if ( ! empty( $this->saved_locations ) ) {
				foreach ( $this->saved_locations as $location ) {
					$defaults = $this->resolve_defaults( $location );
					echo '<tr class="location" data-id="' . $location->ID . '" data-title="' . esc_attr( $location->post_title ) . '" data-defaults=\'' . json_encode( $defaults )  . '\' >
							<th scope="row" class="check-column"></th>
							<td>' . $location->post_title . '</td>
							<td><label for="' . esc_attr( $this->id .'_location_allowed[' . $location->ID . ']' ) . '" class="screen-reader-text">' . sprintf( __( 'Allow pickup location: %s', 'yoast-local-seo-woocommerce' ) , $location->post_title ) . '</label><input type="checkbox" '. checked( true, $location->allowed, false ) .' name="' . esc_attr( $this->id .'_location_allowed[' . $location->ID . ']' ) . '" /> <small>' . $defaults['status'] . '</small></td>
							<td><label for="' . esc_attr( $this->id .'_location_cost[' . $location->ID . ']' ) . '" class="screen-reader-text">' . sprintf( __( 'Costs for pickup location: %s', 'yoast-local-seo-woocommerce' ) , $location->post_title ) . '</label><input type="text" value="' . esc_attr( $location->price ) . '" name="' . esc_attr( $this->id .'_location_cost[' . $location->ID . ']' ) . '" placeholder="' . esc_attr( $cost_desc ) . '" class="input-text regular-input" /> <small>' . $defaults['price'] . '</small></td>
							<td><input class="location_rule_remove" type="button" class="button" value="' . __( 'Remove', 'yoast-local-seo-woocommerce' ) . '"></td>
						</tr>';
				}
			}
			?>
			</tbody>
			<tfoot>
			<tr>
				<td colspan="5">
					<?php _e( 'New location specific settings for:', 'yoast-local-seo-woocommerce' ) ?>
					<select id="location_setting_select">
						<option value="0"><?php _e( 'Select a location to add', 'yoast-local-seo-woocommerce' ) ?></option>';
					<?php
					if ( ! empty( $this->available_locations ) ) {
						foreach ( $this->available_locations as $location ) {
							$defaults = $this->resolve_defaults( $location );
							echo '<option value="' . $location->ID . '" data-defaults=\'' . json_encode( $defaults )  . '\'>' . $location->post_title . '</option>';
						}
					}
					?>
					</select>
					<input id="location_setting_add" type="button" class="button" value="<?php _e( 'Add', 'yoast-local-seo-woocommerce' ) ?>">
				</td>
			</tr>
			</tfoot>
		</table>
	</td>
</tr>