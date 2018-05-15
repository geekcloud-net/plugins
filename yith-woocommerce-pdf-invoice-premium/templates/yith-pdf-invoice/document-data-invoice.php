<?php /* @var YITH_Invoice $document */

$current_order   = $document->order;
$invoice_details = new YITH_Invoice_Details( $document );
?>

<div class="invoice-data-content">
	<table>
		<tr class="ywpi-invoice-number">
			<td class="ywpi-invoice-number-title" colspan="2" >
				<?php echo apply_filters('ywpi_invoice_number_label',__( "INVOICE NUMBER", 'yith-woocommerce-pdf-invoice' ),$document); ?>
			</td>
		</tr>

		<tr class="ywpi-invoice-number">
			<td class="ywpi-invoice-number-title" colspan="2">
				<?php echo $document->get_formatted_document_number(); ?>
			</td>
		</tr>

		<tr class="ywpi-order-number">
			<td class="left-content">
				<?php _e( "Order No.", 'yith-woocommerce-pdf-invoice' ); ?>
			</td>
			<td class="right-content">
				<?php echo $document->order->get_order_number(); ?>
				<?php do_action( 'yith_ywpi_template_order_number', $document ); ?>
			</td>
		</tr>

		<tr class="ywpi-invoice-date">
			<td class="left-content">
				<?php _e( "Invoice date", 'yith-woocommerce-pdf-invoice' ); ?>
			</td>
			<td class="right-content">
				<?php echo apply_filters( 'ywpi_template_invoice_data_table_invoice_date', $document->get_formatted_document_date(), $document ); ?>
			</td>
		</tr>

		<?php if ( apply_filters( 'ywpi_template_invoice_data_table_order_amount_visible', true ) ) : ?>
			<tr class="invoice-amount">
				<td class="left-content">
					<?php echo apply_filters( 'ywpi_invoice_amount_label',__( "Amount", 'yith-woocommerce-pdf-invoice' )); ?>
				</td>
				<td class="right-content">
					<?php echo $invoice_details->get_order_currency( $current_order, $document->order->get_total() ); ?>
				</td>
			</tr>

		<?php endif; ?>
	</table>
</div>