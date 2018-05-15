<?php
/**
 * YITH WooCommerce Recover Abandoned Cart Content metabox template
 *
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.0.0
 * @author  Yithemes
 */
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
	<?php foreach ( $cart_content['cart'] as $key => $raq ):
		$_product = wc_get_product( ( isset( $raq['variation_id'] ) && $raq['variation_id'] != '' ) ? $raq['variation_id'] : $raq['product_id'] );
		if ( $_product ):
			?>
			<tr class="cart_item">
				<td class="product-thumbnail">

					<?php

					$dimensions = wc_get_image_size( 'shop_thumbnail' );
					$height     = esc_attr( $dimensions['height'] );
					$width      = esc_attr( $dimensions['width'] );
					$src        = ( $_product->get_image_id() ) ? current( wp_get_attachment_image_src( $_product->get_image_id(), 'shop_thumbnail' ) ) : wc_placeholder_img_src();

					?>
					<a style="width:50px;height:auto; display: inline-block;" class="product-image" href="<?php echo $_product->get_permalink() ?>"><img style=" width: 100%; height: auto;" src="<?php echo $src ?>" /></a>
				</td>
				<td class="product-name">
					<a href="<?php echo $_product->get_permalink() ?>"><?php echo $_product->get_title() ?></a>
					<?php
					// Meta data
					$item_data = array();

					// Variation data
					if ( ! empty( $raq['variation_id'] ) && is_array( $raq['variation'] ) ) {
						foreach ( $raq['variation'] as $name => $value ) {
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
					echo wc_price( $_product->get_price(), array( 'currency' => $currency ) );
					?>
				</td>

				<td class="product-quantity">
					<?php echo $raq['quantity'] ?>
				</td>

				<td class="product-subtotal">
					<?php
					echo wc_price( $_product->get_price() * $raq['quantity'], array( 'currency' => $currency ) );
					?>
				</td>
			</tr>

			<?php
		endif;
	endforeach ?>
	<tr>
		<td scope="col" colspan="4" style="text-align:right;"><strong><?php echo ( function_exists( 'icl_t' ) ) ? icl_t( 'yith-woocommerce-recover-abandoned-cart', 'ywrac_cart_template_cart_subtotal', 'Cart Subtotal', $has_translation, false, $lang ) : __( 'Cart Subtotal', 'yith-woocommerce-recover-abandoned-cart' ) ?></strong></td>
		<td scope="col" style="text-align:right;"><?php echo wc_price( $subtotal, array( 'currency' => $currency ) ) ?></td>
	</tr>
	</tbody>
</table>
