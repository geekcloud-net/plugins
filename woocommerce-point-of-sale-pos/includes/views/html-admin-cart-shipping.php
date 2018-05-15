<?php
?>
<tr class="shipping_methods_register">
	<th><?php
		if ( $show_package_details ) {
			printf( __( 'Shipping #%d', 'woocommerce' ), $index + 1 );
		} else {
			_e( 'Shipping and Handling', 'woocommerce' );
		}
	?></th>
	<td>
		<?php if ( ! empty( $available_methods ) ) : ?>

			<?php if ( 1 === count( $available_methods ) ) :
				$method = current( $available_methods );

				echo wp_kses_post( wc_cart_totals_shipping_method_label( $method ) ); ?>
				<input type="hidden" name="shipping_method[<?php echo $index; ?>]" data-index="<?php echo $index; ?>" data-cost="<?php echo $method->cost; ?>" id="shipping_method_<?php echo $index; ?>" value="<?php echo esc_attr( $method->id ); ?>" class="shipping_method" />

			<?php else : ?>

				<select name="shipping_method[<?php echo $index; ?>]" data-index="<?php echo $index; ?>" id="shipping_method_<?php echo $index; ?>" class="shipping_method">
					<option value="no_shipping" <?php selected( 'no_shipping', $chosen_method ); ?> data-cost="0"><?php _e('No Shipping','wc_point_of_sale' ); ?></option>
					<?php foreach ( $available_methods as $method ) : ?>
						<option value="<?php echo esc_attr( $method->id ); ?>" <?php selected( $method->id, $chosen_method ); ?> data-cost="<?php echo $method->cost; ?>"><?php echo wp_kses_post( wc_cart_totals_shipping_method_label( $method ) ); ?></option>
					<?php endforeach; ?>
				</select>

			<?php endif; ?>


		<?php elseif ( ! WC()->customer->get_shipping_state() || ! WC()->customer->get_shipping_postcode() ) : ?>

			<?php if ( get_option( 'woocommerce_enable_shipping_calc' ) === 'yes' ) : ?>

				<p><?php _e( 'Please use the shipping calculator to see available shipping methods.', 'woocommerce' ); ?></p>

			<?php else : ?>

				<p><?php _e( 'Please continue to the checkout and enter your full address to see if there are any available shipping methods.', 'woocommerce' ); ?></p>

			<?php endif; ?>

		<?php else : ?>

				<?php echo apply_filters( 'woocommerce_cart_no_shipping_available_html',
					'<div class="woocommerce-info"><p>' . __( 'There doesn&lsquo;t seem to be any available shipping methods. Please double check your address, or contact us if you need any help.', 'woocommerce' ) . '</p></div>'
				); ?>

		<?php endif; ?>

		<?php if ( $show_package_details ) : ?>
			<?php
				foreach ( $package['contents'] as $item_id => $values ) {
					if ( $values['data']->needs_shipping() ) {
						$product_names[] = $values['data']->get_title() . ' &times;' . $values['quantity'];
					}
				}

				echo '<p class="woocommerce-shipping-contents"><small>' . __( 'Shipping', 'woocommerce' ) . ': ' . implode( ', ', $product_names ) . '</small></p>';
			?>
		<?php endif; ?>
	</td>
</tr>