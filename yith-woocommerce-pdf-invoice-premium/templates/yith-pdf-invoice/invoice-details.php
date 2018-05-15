<?php
/**
 * Override this template by copying it to [your theme folder]/woocommerce/yith-pdf-invoice
 *
 * @author        Yithemes
 * @package       yith-woocommerce-pdf-invoice-premium/Templates
 * @version       1.0.0
 */

if ( ! defined ( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


/** @var YITH_Document $document */

$current_order   = $document->order;
$invoice_details = new YITH_Invoice_Details( $document );
?>

<table class="invoice-details">
	<thead>
	<tr>
		<?php if ( ywpi_is_enabled_column_picture ( $document ) ) : ?>
			<th class="column-picture"></th>
		<?php endif; ?>

		<th class="column-product"><?php _e ( 'Product', 'yith-woocommerce-pdf-invoice' ); ?></th>

		<?php if ( ywpi_is_enabled_column_quantity ( $document ) ) : ?>
			<th class="column-quantity"><?php _e ( 'Qty', 'yith-woocommerce-pdf-invoice' ); ?></th>
		<?php endif; ?>

		<?php if ( ywpi_is_enabled_column_product_price ( $document ) ) : ?>
			<th class="column-price"><?php _e ( 'Product price', 'yith-woocommerce-pdf-invoice' ); ?></th>
		<?php endif; ?>

		<?php if ( ywpi_is_enabled_column_regular_price ( $document ) ) : ?>
			<th class="column-price"><?php _e ( 'Price', 'yith-woocommerce-pdf-invoice' ); ?></th>
		<?php endif; ?>

		<?php if ( ywpi_is_enabled_column_sale_price ( $document ) ) : ?>
			<th class="column-price"><?php _e ( 'Sale price', 'yith-woocommerce-pdf-invoice' ); ?></th>
		<?php endif; ?>

		<?php if ( ywpi_is_enabled_column_percentage ( $document ) ) : ?>
			<th class="column-discount-percentage"><?php _e ( 'Discount percentage', 'yith-woocommerce-pdf-invoice' ); ?></th>
		<?php endif; ?>

		<?php if ( ywpi_is_enabled_column_line_total ( $document ) ) : ?>
			<th class="column-price"><?php _e ( 'Line total', 'yith-woocommerce-pdf-invoice' ); ?></th>
		<?php endif; ?>

		<?php if ( ywpi_is_enabled_column_tax ( $document ) ) : ?>
			<th class="column-price"><?php _e ( 'Tax', 'yith-woocommerce-pdf-invoice' ); ?></th>
		<?php endif; ?>

		<?php if ( ywpi_is_enabled_column_percentage_tax ( $document ) ) : ?>
			<th class="column-price"><?php _e ( 'Percentage tax', 'yith-woocommerce-pdf-invoice' ); ?></th>
		<?php endif; ?>

		<?php if ( ywpi_is_enabled_column_total_taxed ( $document ) ) : ?>
			<th class="column-price"><?php _e ( 'Total (inc. tax)', 'yith-woocommerce-pdf-invoice' ); ?></th>
		<?php endif; ?>
	</tr>
	</thead>
	<tbody>
	<?php

	/** @var WC_Product $_product */
	foreach ( $invoice_details->get_order_items () as $item_id => $item ) {
		$_product = $invoice_details->get_item_product ( $item );
		?>
		<tr>
			<!-- Show picture if related option is enabled -->
			<?php if ( ywpi_is_enabled_column_picture ( $document ) ): ?>
				<td class="column-picture">
					<?php $image_path = apply_filters ( 'yith_ywpi_image_path', $invoice_details->get_product_image ( $item ) );
					if ( $image_path ): ?>
						<img class="product-image" src="<?php echo $image_path; ?>" />
					<?php endif; ?>
				</td>
			<?php endif; ?>

			<td class="column-product">
				<!-- Show product title -->
				<?php echo apply_filters ( 'woocommerce_order_product_title', $item['name'], $_product ); ?>
				<br>

				<?php if ( ywpi_is_enabled_column_variation ( $document ) ) : ?>
					<?php echo urldecode($invoice_details->get_variation_text ( $item_id, $_product )); ?>
				<?php endif; ?>

				<?php if ( ywpi_is_enabled_column_sku ( $document ) ) : ?>
					<?php echo $invoice_details->get_sku_text ( $item ); ?>
				<?php endif; ?>

				<?php if ( ywpi_is_enabled_column_short_description ( $document ) ):
					$_product_id = yit_get_prop ( $_product, 'id' );
					if( $_product->is_type('variation') ){
						if( version_compare( WC()->version, '3.0', '<' ) ){
							$post_excerpt = $_product->get_variation_description();
						}else{
							$post_excerpt = $_product->get_description();
						}
					}else{
						$post_excerpt = get_post_field ( 'post_excerpt', $_product_id );
					}

					if ( ywpi_is_enabled_column_short_description ( $document ) && ( ! empty( $post_excerpt ) ) ) : ?>
						<div class="product-short-description"><?php echo $post_excerpt; ?></div>
					<?php endif; ?>
				<?php endif; ?>

				<?php do_action ( 'yith_ywpi_column_product_after_content', $document, $_product, $item_id ); ?>
			</td>
			<?php if ( ywpi_is_enabled_column_quantity ( $document ) ) : ?>
				<td class="column-quantity">
					<?php echo ( isset( $item['qty'] ) ) ? esc_html ( $item['qty'] ) : ''; ?>
				</td>
			<?php endif; ?>

			<?php if ( ywpi_is_enabled_column_product_price ( $document ) ) : ?>
				<td class="column-price">
					<?php echo $invoice_details->get_order_currency( $current_order, $invoice_details->get_item_price_per_unit ( $item ) ); ?>
				</td>
			<?php endif; ?>

			<?php if ( ywpi_is_enabled_column_regular_price ( $document ) ) : ?>
				<td class="column-price">
					<?php echo wc_price( $invoice_details->get_item_product_regular_price ( $item ) ); ?>
				</td>
			<?php endif; ?>

			<?php if ( ywpi_is_enabled_column_sale_price ( $document ) ) : ?>
				<td class="column-price">
					<?php echo $invoice_details->get_order_currency( $current_order, $invoice_details->get_item_price_per_unit_sale ( $item ) ); ?>
				</td>
			<?php endif; ?>

			<?php if ( ywpi_is_enabled_column_percentage ( $document ) ) : ?>
				<td class="column-discount-percentage">
					<?php echo $invoice_details->get_item_percentage_discount ( $item ); ?>
				</td>
			<?php endif; ?>

			<?php if ( ywpi_is_enabled_column_line_total ( $document ) ) : ?>
				<td class="column-price">
					<?php echo $invoice_details->get_order_currency( $current_order, $item["line_total"] ); ?>
				</td>
			<?php endif; ?>

			<?php if ( ywpi_is_enabled_column_tax ( $document ) ) : ?>
				<td class="column-price">
					<?php echo $invoice_details->get_order_currency( $current_order, $item["line_tax"] ); ?>
				</td>
			<?php endif; ?>

			<?php if ( ywpi_is_enabled_column_percentage_tax ( $document ) && isset($item['line_tax']) && isset($item['line_total']) ) : ?>
				<td class="column-price">
					<?php if( $item['line_total'] != 0 && $item['line_total'] != '' ): ?>
						<?php $tax_percentage = $item['line_tax']*100/$item['line_total']; ?>
						<?php echo round($tax_percentage,2) . '%'; ?>
					<?php else: ?>
						<?php echo '0%'; ?>
					<?php endif; ?>
				</td>
			<?php endif; ?>

			<?php if ( ywpi_is_enabled_column_total_taxed ( $document ) ) : ?>
				<td class="column-price">
					<?php echo $invoice_details->get_order_currency( $current_order, $item["line_tax"] + $item["line_total"] ); ?>
				</td>
			<?php endif; ?>
		</tr>

		<?php
	} // foreach;

	if ( apply_filters ( 'ywpi_is_visible_fee_details_section', true, $document ) ) :

		foreach ( $invoice_details->get_order_fees () as $item_id => $item ) {
			?>

			<tr class="border-top">
				<?php if ( ywpi_is_enabled_column_picture ( $document ) ) : ?>
					<td class="column-picture">
					</td>
				<?php endif; ?>

				<td class="column-product">
					<?php echo ! empty( $item['name'] ) ? esc_html ( $item['name'] ) : __ ( 'Fee', 'yith-woocommerce-pdf-invoice' ); ?>
				</td>

				<?php if ( ywpi_is_enabled_column_quantity ( $document ) ) : ?>
					<td class="column-quantity">
					</td>
				<?php endif; ?>

				<?php if ( ywpi_is_enabled_column_product_price ( $document ) ) : ?>
					<td class="column-price">
						<?php echo $invoice_details->get_order_currency( $current_order, $item['line_total'] ); ?>
					</td>
				<?php endif; ?>

				<?php if ( ywpi_is_enabled_column_regular_price ( $document ) ) : ?>
					<td class="column-price">
					</td>
				<?php endif; ?>

				<?php if ( ywpi_is_enabled_column_sale_price ( $document ) ) : ?>
					<td class="column-price">
					</td>
				<?php endif; ?>

				<?php if ( ywpi_is_enabled_column_percentage ( $document ) ) : ?>
					<td class="column-discount-percentage"></td>
				<?php endif; ?>

				<?php if ( ywpi_is_enabled_column_line_total ( $document ) ) : ?>
					<td class="column-price">
						<?php echo $invoice_details->get_order_currency( $current_order, $item['line_total'] ); ?>
					</td>
				<?php endif; ?>

				<?php if ( ywpi_is_enabled_column_tax ( $document ) ) : ?>
					<td class="column-price">
						<?php echo $invoice_details->get_order_currency( $current_order, $item['line_tax'] ); ?>
					</td>
				<?php endif; ?>

				<?php if ( ywpi_is_enabled_column_percentage_tax ( $document ) && isset($item['line_tax']) && isset($item['line_total']) ) : ?>
					<td class="column-price">
						<?php if( $item['line_total'] != 0 && $item['line_total'] != '' ): ?>
							<?php $tax_percentage = $item['line_tax']*100/$item['line_total']; ?>
							<?php echo round($tax_percentage,2) . '%'; ?>
						<?php else: ?>
							<?php echo '0%'; ?>
						<?php endif; ?>
					</td>
				<?php endif; ?>

				<?php if ( ywpi_is_enabled_column_total_taxed ( $document ) ) : ?>
					<td class="column-price">
						<?php echo $invoice_details->get_order_currency( $current_order, $item["line_tax"] + $item["line_total"] ); ?>
					</td>
				<?php endif; ?>

			</tr>

			<?php
		}   // foreach
	endif;

	if ( apply_filters ( 'ywpi_is_visible_shipping_details_section', true, $document ) ) :

		foreach ( $invoice_details->get_order_shipping () as $item_id => $item ) {
			?>

			<tr>
				<?php if ( ywpi_is_enabled_column_picture ( $document ) ) : ?>
					<td class="column-picture">
					</td>
				<?php endif; ?>

				<td class="column-product">
					<?php echo ! empty( $item['name'] ) ? esc_html ( $item['name'] ) : __ ( 'Shipping', 'yith-woocommerce-pdf-invoice' ); ?>
				</td>

				<?php if ( ywpi_is_enabled_column_quantity ( $document ) ) : ?>
					<td class="column-quantity">
					</td>
				<?php endif; ?>

				<?php if ( ywpi_is_enabled_column_product_price ( $document ) ) : ?>

					<td class="column-price">
						<?php echo ( isset( $item['cost'] ) ) ? $invoice_details->get_order_currency( $current_order, wc_round_tax_total ( $item['cost']) ) : ''; ?>
					</td>
				<?php endif; ?>

				<?php if ( ywpi_is_enabled_column_regular_price ( $document ) ) : ?>
					<td class="column-price">
					</td>
				<?php endif; ?>

				<?php if ( ywpi_is_enabled_column_sale_price ( $document ) ) : ?>
					<td class="column-price">
					</td>
				<?php endif; ?>

				<?php if ( ywpi_is_enabled_column_percentage ( $document ) ) : ?>
					<td class="column-discount-percentage"></td>
				<?php endif; ?>

				<?php if ( ywpi_is_enabled_column_line_total ( $document ) ) : ?>
					<td class="column-price">
						<?php echo ( isset( $item['cost'] ) ) ? $invoice_details->get_order_currency( $current_order, $item['cost'] ) : ''; ?>
					</td>
				<?php endif; ?>

				<?php

				if ( ywpi_is_enabled_column_tax ( $document ) ) : ?>
					<td class="column-price">
						<?php
						echo ( $invoice_details->get_order_currency( $current_order, wc_round_tax_total ( $invoice_details->get_item_shipping_taxes ( $item ) ) ) );
						?>
					</td>
				<?php endif; ?>

                <?php if ( ywpi_is_enabled_column_percentage_tax ( $document ) && isset($item['cost']) ) : ?>
                    <td class="column-price">
                        <?php if( $item['cost'] != 0 && $item['cost'] != '' ): ?>
                            <?php $tax_percentage = ( ( $invoice_details->get_item_shipping_taxes ( $item ) ) * 100 ) / $item["cost"]; ?>
                            <?php echo round( $tax_percentage,2 ) . '%'; ?>
                        <?php else: ?>
                            <?php echo '0%'; ?>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>

				<?php if ( ywpi_is_enabled_column_total_taxed ( $document ) ) : ?>
					<td class="column-price">
						<?php echo $invoice_details->get_order_currency( $current_order, $item["cost"] + $invoice_details->get_item_shipping_taxes ( $item ) ); ?>
					</td>
				<?php endif; ?>
			</tr>
			<?php
		};
	endif;

	?>
	</tbody>
</table>