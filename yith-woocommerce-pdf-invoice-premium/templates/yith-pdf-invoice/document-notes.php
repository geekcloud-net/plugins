<?php
/**
 * Override this template by copying it to [your theme folder]/woocommerce/yith-pdf-invoice
 *
 * @author        Yithemes
 * @package       yith-woocommerce-pdf-invoice-premium/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( YITH_PDF_Invoice()->is_visible_document_notes( $document ) ) {
	$notes = apply_filters( 'yith_ywpi_print_document_notes', YITH_PDF_Invoice()->get_document_notes( $document ), $document );
	if ( $notes ) {
		?>
		<div class="ywpi-section-notes">
			<span class="notes-title"><?php _e( "Notes", "yith-woocommerce-pdf-invoice" ); ?></span>
			<div class="notes">
				<span><?php echo nl2br( $notes ); ?></span>
                <?php do_action( 'yith_ywpi_after_document_notes', $document );?>
			</div>
		</div>
		<?php
	}
}