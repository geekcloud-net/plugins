<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$product_link  = $_product ? admin_url( 'post.php?post=' . absint( $_product->id ) . '&action=edit' ) : '';
$thumbnail     = $_product ? apply_filters( 'woocommerce_admin_order_item_thumbnail', $_product->get_image( 'thumbnail', array( 'title' => '' ), false ), 0, null ) : '';
$item_id       = isset( $_product->variation_id ) ? $_product->variation_id : $_product->id;
$parent_id     = $_product->product_type == 'variation' ? $_product->parent->id : $_product->id;

?>
<tr class="item <?php echo $class; ?> item_<?php echo $item_id; ?>" data-prid="<?php echo $item_id; ?>" data-parentid="<?php echo $parent_id; ?>" >
	<td class="item_cost sortable">
		<?php
		$sku = '';
		if ( $_product && $_product->get_sku() ) {
			$sku = esc_html( $_product->get_sku() );
				echo '<div class="sku_text">' . esc_html( $_product->get_sku() ) . '</div>';
		}else{
			echo '<div class="wrong_sku"></div>';
		}

		?>
	</td>
	<td class="thumb">
		<?php
			echo '<div class="wc-order-item-thumbnail">' . wp_kses_post( $thumbnail ) . '</div>';
		?>
	</td>
	<td class="name">
		<?php
			echo $product_link ? '<a href="' . esc_url( $product_link ) . '" class="wc-order-item-name">' .  esc_html( $_product->get_title() ) . '</a>' : '<div class="class="wc-order-item-name"">' . esc_html( $_product->get_title() ) . '</div>';

			if ( isset( $_product->variation_id ) ) {
				echo '<div class="wc-order-item-variation"><strong>' . __( 'Variation ID:', 'woocommerce' ) . '</strong> ';
				if ( ! empty( $_product->variation_id ) && 'product_variation' === get_post_type( $_product->variation_id ) ) {
					echo esc_html( $_product->variation_id );
				} elseif ( ! empty( $_product->variation_id ) ) {
					echo esc_html( $_product->variation_id ) . ' (' . __( 'No longer exists', 'woocommerce' ) . ')';
				}
				echo '</div>';

				$variation = $_product->get_variation_attributes();
				if ( is_array( $variation ) ) {
					echo '<div class="view"><table cellspacing="0" class="display_meta"><tbody>';

					foreach ( $variation as $name => $value ) {
						if ( ! $value ) {
							continue;
						}

						// If this is a term slug, get the term's nice name
						if ( taxonomy_exists( esc_attr( str_replace( 'attribute_', '', $name ) ) ) ) {
							$term = get_term_by( 'slug', $value, esc_attr( str_replace( 'attribute_', '', $name ) ) );
							if ( ! is_wp_error( $term ) && ! empty( $term->name ) ) {
								$value = $term->name;
							}
						} else {
							$value = ucwords( str_replace( '-', ' ', $value ) );
						}
						
						echo'<tr><th>' . wc_attribute_label( str_replace( 'attribute_', '', $name ) ) . ':</th><td>' . rawurldecode( $value ) . '</td></tr>';
					}

					echo '</tbody></table></div>';

				}
			}

			
		?>
	</td>
	<td class="item_cost" width="1%">
		<div class="view product_price">
			<?php
					echo wc_price( $_product->get_price() );
			?>
		</div>
	</td>
	<td class="item_cost" width="1%">
		<?php
		if ( 'grouped' == $_product->product_type ) {
			echo '<span class="product-type tips grouped" data-tip="' . esc_attr__( 'Grouped', 'woocommerce' ) . '"></span>';
		} elseif ( 'external' == $_product->product_type ) {
			echo '<span class="product-type tips external" data-tip="' . esc_attr__( 'External/Affiliate', 'woocommerce' ) . '"></span>';
		} elseif ( 'simple' == $_product->product_type ) {

			if ( $_product->is_virtual() ) {
				echo '<span class="product-type tips virtual" data-tip="' . esc_attr__( 'Virtual', 'woocommerce' ) . '"></span>';
			} elseif ( $_product->is_downloadable() ) {
				echo '<span class="product-type tips downloadable" data-tip="' . esc_attr__( 'Downloadable', 'woocommerce' ) . '"></span>';
			} else {
				echo '<span class="product-type tips simple" data-tip="' . esc_attr__( 'Simple', 'woocommerce' ) . '"></span>';
			}

		} elseif ( 'variable' == $_product->product_type ) {
			echo '<span class="product-type tips variable" data-tip="' . esc_attr__( 'Variable', 'woocommerce' ) . '"></span>';
		} else {
			// Assuming that we have other types in future
			echo '<span class="product-type tips ' . $_product->product_type . '" data-tip="' . ucfirst( $_product->product_type ) . '"></span>';
		}
		?>
	</td>
	<td class="line_cost">
		<div class="barcode_border">
			<?php
			if ( $_product && $_product->get_sku() ) {
				$barcode_url = WC_POS()->barcode_url() . '&text=' . $sku;
			?>
			<img src="<?php echo $barcode_url . '&font_size=12'; ?>" alt="" data-barcode_url="<?php echo $barcode_url; ?>">
			<?php
			}
			?>
			<div class="barcode_text"></div>
		</div>									
	</td>
	<td class="quantity" width="1%">
		<div class="view">
			<?php
				echo '<small class="times">&times;</small> <span>1</span>';
			?>
		</div>
		<div class="edit" style="display: none;">
			<?php $item_qty = 1; ?>
			<input type="number" step="<?php echo apply_filters( 'woocommerce_quantity_input_step', '1', $_product ); ?>" min="1" autocomplete="off" placeholder="1" value="1" size="4" class="quantity" />
		</div>
	</td>
	<td class="wc-order-edit-line-item" width="1%">
		<div class="wc-order-edit-line-item-actions">
			<a class="edit-order-item tips" href="#" data-tip="<?php esc_attr_e( 'Edit item', 'woocommerce' ); ?>"></a>
			<a class="delete-order-item tips" href="#" data-tip="<?php esc_attr_e( 'Delete item', 'woocommerce' ); ?>"></a>
		</div>
	</th>
</tr>