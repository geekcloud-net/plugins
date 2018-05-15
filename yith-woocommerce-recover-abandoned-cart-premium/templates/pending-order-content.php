<?php
/**
 * YITH WooCommerce Recover Abandoned Cart Pending Order Content
 *
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.1.0
 * @author  Yithemes
 */

$order_items = $order->get_items();

?>

<table class="shop_table cart" id="yith-ywrac-table-list" cellspacing="0">
	<thead>
	<tr>
		<th class="product-thumbnail"><?php echo ( function_exists( 'icl_t' ) ) ? icl_t( 'yith-woocommerce-recover-abandoned-cart', 'ywrac_cart_template_thumbnail', 'Thumbnail', $has_translation, false, $lang ) : __( 'Thumbnail', 'yith-woocommerce-recover-abandoned-cart' ) ?></th>
		<th class="product-name"><?php echo ( function_exists( 'icl_t' ) ) ? icl_t( 'yith-woocommerce-recover-abandoned-cart', 'ywrac_cart_template_product', 'Product', $has_translation, false, $lang ) : __( 'Product', 'yith-woocommerce-recover-abandoned-cart' ) ?></th>
		<th class="product-single"><?php echo ( function_exists( 'icl_t' ) ) ? icl_t( 'yith-woocommerce-recover-abandoned-cart', 'ywrac_cart_template_product_price', 'Product Price', $has_translation, false, $lang ) : __( 'Product Price', 'yith-woocommerce-recover-abandoned-cart' ) ?></th>
		<th class="product-quantity"><?php echo ( function_exists( 'icl_t' ) ) ? icl_t( 'yith-woocommerce-recover-abandoned-cart', 'ywrac_cart_template_quantity', 'Quantity', $has_translation, false, $lang ) : __( 'Quantity', 'yith-woocommerce-recover-abandoned-cart' ) ?></th>
		<th class="product-subtotal"><?php echo ( function_exists( 'icl_t' ) ) ? icl_t( 'yith-woocommerce-recover-abandoned-cart', 'ywrac_cart_template_cart_subtotal', 'Total', $has_translation, false, $lang ) : __( 'Total', 'yith-woocommerce-recover-abandoned-cart' ) ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ( $order_items as $order_item ):
		$product_id = ( isset( $order_item['variation_id'] ) && $order_item['variation_id'] ) ? $order_item['variation_id'] : $order_item['product_id'];
		$_product = wc_get_product( $product_id );
		if ( $_product ):
			?>
			<tr class="cart_item">
				<td class="product-thumbnail">
					<?php

					$image = '';

					if ( has_post_thumbnail( yit_get_product_id( $_product ) ) ) {

						$product_image = wp_get_attachment_image_src( get_post_thumbnail_id( yit_get_product_id( $_product ) ), 'shop_thumbnail' );
						list( $src, $width, $height ) = $product_image;

						$image = $src;

					} elseif ( wc_placeholder_img_src() ) {

						$image = wc_placeholder_img_src();

					}

					?>

					<a style="width:50px;height:auto; display: inline-block;" class="product-image" href="<?php echo $_product->get_permalink() ?>"><img style=" width: 100%; height: auto;" src="<?php echo $image ?>" /></a>
				</td>

				<td class="product-name">
					<a href="<?php echo $_product->get_permalink() ?>"><?php echo $order_item['name'] ?></a>
					<?php
					// Meta data
					$item_data = array();

					// Variation data
					if ( $order_item['variation_id'] && isset( $order_item['variation'] ) && is_array( $order_item['variation'] ) ) {
						foreach ( $order_item['variation'] as $name => $value ) {
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
					echo wc_price( $order_item['line_subtotal'] / $order_item['qty'], array( 'currency' => $currency ) );
					?>
				</td>

				<td class="product-quantity">
					<?php echo $order_item['qty'] ?>
				</td>

				<td class="product-subtotal">
					<?php
					echo wc_price( $order_item['line_subtotal'], array( 'currency' => $currency ) );
					?>
				</td>
			</tr>

			<?php
		endif;
	endforeach ?>

	<?php


	foreach ( $order->get_order_item_totals() as $key => $total ) {
		?>
		<tr>
			<th scope="col" colspan="4" style="text-align:right;border: 1px solid #eee;"><?php echo $total['label']; ?></th>
			<td scope="col" style="text-align:right;border: 1px solid #eee;"><?php echo $total['value']; ?></td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>