<?php
$cost_desc = __( 'Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>.', 'yoast-local-seo-woocommerce' ) . '<br/><br/>' . __( 'Use <code>[qty]</code> for the number of items, <br/><code>[cost]</code> for the total cost of items, and <code>[fee percent="10" min_fee="20" max_fee=""]</code> for percentage based fees.', 'yoast-local-seo-woocommerce' );
?>
<tr valign="top" class="wpseo_local_shipping_costs">
	<th scope="row" class="titledesc"><?php _e( 'Cost per category', 'yoast-local-seo-woocommerce' ); ?></th>
	<td class="forminp" id="<?php echo $this->id; ?>_locations">
		<table class="shippingrows widefat" cellspacing="0">
			<caption class="screen-reader-text"><?php _e( 'Cost per category', 'yoast-local-seo-woocommerce' ); ?></caption>
			<thead>
				<tr>
					<th scope="col" class="check-column"></th>
					<th scope="col"><?php _e( 'Location category', 'yoast-local-seo-woocommerce' ); ?></th>
					<th scope="col"><?php _e( 'Allow local pickup', 'yoast-local-seo-woocommerce' ); ?> <a class="tips" data-tip="<?php _e( 'Whether or not to allow local pickup from locations in this category.', 'yoast-local-seo-woocommerce' ); ?>">[?]</a></th>
					<th scope="col"><?php _e( 'Costs', 'yoast-local-seo-woocommerce' ); ?> <a class="tips" data-tip="<?php echo esc_attr( $cost_desc ); ?>">[?]</a></th>
				</tr>
			</thead>
			<tbody class="locations">
			<?php
			if ( ! empty( $this->location_categories ) ) {
				foreach ( $this->location_categories as $category ) {
					echo '<tr class="location">
							<th scope="row" class="check-column"></th>
							<td>' . $category->name . '</td>
							<td><label for="' . esc_attr( $this->id .'_cat_allowed[' . $category->term_id . ']' ) . '" class="screen-reader-text">' . sprintf( __( 'Allow pickup category: %s', 'yoast-local-seo-woocommerce' ) , $category->name ) . '</label><input type="checkbox" '. checked( true, $category->allowed, false ) .' name="' . esc_attr( $this->id .'_cat_allowed[' . $category->term_id . ']' ) . '" /></td>
							<td><label for="' . esc_attr( $this->id .'_cat_cost[' . $category->term_id . ']' ) . '" class="screen-reader-text">' . sprintf( __( 'Costs for pickup category: %s', 'yoast-local-seo-woocommerce' ) , $category->name ) . '</label><input type="text" value="' . esc_attr( $category->price ) . '" name="' . esc_attr( $this->id .'_cat_cost[' . $category->term_id . ']' ) . '" placeholder="' . esc_attr( $cost_desc ) . '" class="input-text regular-input" /></td>
						</tr>';
				}
			}
			?>
			</tbody>
			<tfoot>
			<tr>
				<th colspan="4"></th>
			</tr>
			</tfoot>
		</table>
	</td>
</tr>