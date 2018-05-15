<?php
/**
 * YITH WooCommerce Recover Abandoned Cart Content metabox template
 *
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.0.0
 * @author  Yithemes
 */

if ( empty( $cart_content['cart'] ) ) {
	return;
}
$tax_display_cart = get_option( 'woocommerce_tax_display_cart' );
$total            = $subtotal;
?>
<table class="shop_table cart" id="yith-ywrac-table-list" cellspacing="0">
    <thead>
    <tr>
        <th class="product-thumbnail"><?php _e( 'Thumbnail', 'yith-woocommerce-recover-abandoned-cart' ) ?></th>
        <th class="product-name"><?php _e( 'Product', 'yith-woocommerce-recover-abandoned-cart' ) ?></th>
        <th class="product-single"><?php _e( 'Product Price', 'yith-woocommerce-recover-abandoned-cart' ) ?></th>
        <th class="product-quantity"><?php _e( 'Quantity', 'yith-woocommerce-recover-abandoned-cart' ) ?></th>
        <th class="product-subtotal"><?php _e( 'Total', 'yith-woocommerce-recover-abandoned-cart' ); ?></th>
    </tr>
    </thead>
    <tbody>
	<?php foreach ( $cart_content['cart'] as $cart_item_key => $cart_item ):
		$_product = wc_get_product( ( isset( $cart_item['variation_id'] ) && $cart_item['variation_id'] != '' ) ? $cart_item['variation_id'] : $cart_item['product_id'] );
		?>
        <tr class="cart_item">
            <td class="product-thumbnail">
				<?php $thumbnail = $_product->get_image();

				if ( ! $_product->is_visible() ) {
					echo $thumbnail;
				} else {
					printf( '<a href="%s">%s</a>', $_product->get_permalink(), $thumbnail );
				}
				?>
            </td>

            <td class="product-name">
                <a href="<?php echo $_product->get_permalink() ?>"><?php echo $_product->get_title() ?></a>
				<?php
				// Meta data
				$item_data = array();

				// Variation data
				if ( isset( $cart_item['variation_id'] ) && isset( $cart_item['variation'] ) && ! empty( $cart_item['variation_id'] ) && is_array( $cart_item['variation'] ) ) {
					foreach ( $cart_item['variation'] as $name => $value ) {
						$label = '';
						if ( '' === $value ) {
							continue;
						}
						$taxonomy = wc_attribute_taxonomy_name( str_replace( 'pa_', '', urldecode( $name ) ) );

						// If this is a term slug, get the term's nice name
						if ( taxonomy_exists( $taxonomy ) ) {
							$term = get_term_by( 'slug', $value, $taxonomy );
							if ( ! is_wp_error( $term ) && $term && $term->name ) {
								$value = $term->name;
							}
							$label = wc_attribute_label( $taxonomy );

						} else {

							if ( strpos( $name, 'attribute_' ) !== false ) {
								$custom_att = str_replace( 'attribute_', '', $name );

								if ( $custom_att != '' ) {
									$label = wc_attribute_label( $custom_att );
								} else {
									$label = $name;
								}
							}

						}

						$item_data[] = array(
							'key'   => $label,
							'value' => $value
						);
					}
				}

				// Output flat or in list format
				if ( sizeof( $item_data ) > 0 ) {
					foreach ( $item_data as $data ) {
						echo esc_html( $data['key'] ) . ': ' . wp_kses_post( $data['value'] ) . "\n";
					}
				}
				?>
            </td>
            <td class="product-price">
				<?php
				//echo apply_filters( 'woocommerce_cart_item_price', $newcart->get_product_price( $_product ), $cart_item, $cart_item_key );
				echo apply_filters( 'woocommerce_cart_item_price', ywrac_get_product_price( $_product ), $cart_item, $cart_item_key );
				?>
            </td>

            <td class="product-quantity">
				<?php echo $cart_item['quantity'] ?>
            </td>

            <td class="product-subtotal">
				<?php
				echo apply_filters( 'woocommerce_cart_item_subtotal', ywrac_get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
				// echo wc_price($_product->get_price() * $cart_item['quantity']);
				?>
            </td>
        </tr>

	<?php endforeach ?>

    <tr>
        <td scope="col" colspan="4" style="text-align: right">
            <strong><?php _e( 'Cart Subtotal', 'yith-woocommerce-recover-abandoned-cart' ) ?></strong></td>
		<?php if ( $tax_display_cart == 'excl' ) :
			$product_subtotal = wc_price( $subtotal ) . ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
			?>
            <td scope="col"><?php echo $product_subtotal ?></td>
		<?php endif; ?>
    </tr>
	<?php if ( $subtotal_tax ) :
		$total += $subtotal_tax;
		if ( $tax_display_cart == 'excl' ) ?>
            <tr>
            <td scope="col" colspan="4" style="text-align: right"><strong><?php echo esc_html( WC()->countries->tax_or_vat() ) ?></strong>
        </td>
        <td scope="col"><?php echo wc_price( $subtotal_tax ) ?></td>
        </tr>
	<?php endif ?>
	<?php if ( $total ) : ?>
        <tr>
            <td scope="col" colspan="4" style="text-align: right">
                <strong><?php _e( 'Cart Total', 'yith-woocommerce-recover-abandoned-cart' ) ?></strong></td>
            <td scope="col"><?php echo wc_price( $total ) ?></td>
        </tr>
	<?php endif ?>
    </tbody>
</table>