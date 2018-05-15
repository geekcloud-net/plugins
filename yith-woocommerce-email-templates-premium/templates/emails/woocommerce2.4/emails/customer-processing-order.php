<?php
/**
 * Customer processing order email
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<?php
	$mail_type = "customer_processing_order";
	do_action( 'yith_wcet_email_header', $email_heading, $mail_type);

	if (defined('YITH_WCET_PREMIUM')){
        $template        = get_option( 'yith-wcet-email-template-' . $mail_type );
    }else{
        $template        = get_option( 'yith-wcet-email-template' );
    }
    $meta            = get_post_meta( $template, '_template_meta', true);
    $premium_mail_style =  ( !empty( $meta['premium_mail_style'] ) ) ? $meta['premium_mail_style'] : 0;
    $show_thumbs      = ( isset( $meta['show_prod_thumb'] ) ) ? $meta['show_prod_thumb'] : 0;
?>

<p><?php _e( "Your order has been received and is now being processed. Your order details are shown below for your reference:", 'woocommerce' ); ?></p>

<?php do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text ); ?>

<h2><?php printf( __( 'Order #%s', 'woocommerce' ), $order->get_order_number() ); ?></h2>

<table id="yith-wcet-order-items-table" cellspacing="0" cellpadding="6" style="width: 100%;">
	<thead>
		<tr>
			<th id="yith-wcet-th-title-product" class="yith-wcet-order-items-table-element" scope="col" style="text-align:left;"><?php _e( 'Product', 'woocommerce' ); ?></th>
			<th id="yith-wcet-th-title-quantity" class="yith-wcet-order-items-table-element" scope="col" style="text-align:left;"><?php _e( 'Quantity', 'woocommerce' ); ?></th>
			<th id="yith-wcet-th-title-price" class="yith-wcet-order-items-table-element" scope="col" style="text-align:left;"><?php _e( 'Price', 'woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php echo $order->email_order_items_table( $order->is_download_permitted(), true, $order->has_status( 'processing' ), $show_thumbs ); ?>
	</tbody>
	<?php if($premium_mail_style < 2){ ?>
		<tfoot>
			<?php
				if ( $totals = $order->get_order_item_totals() ) {
					$i = 0;
					foreach ( $totals as $total ) {
						$i++;
						?><tr>
							<th class="yith-wcet-order-items-table-element<?php if ( $i == 1 ) echo '-bigtop'; ?>" scope="row" colspan="2"><?php echo $total['label']; ?></th>
							<td class="yith-wcet-order-items-table-element<?php if ( $i == 1 ) echo '-bigtop'; ?>"><?php echo $total['value']; ?></td>
						</tr><?php
					}
				}
			?>
		</tfoot>
	<?php } ?>
</table>

<?php if($premium_mail_style > 1){ ?>
	<div>
		<table id= "yith-wcet-foot-price-list">
			<?php
				if ( $totals = $order->get_order_item_totals() ) {
					$i = 0;
					$t_count = count($totals);
					foreach ( $totals as $total ) {
						$i++;
						?><tr>
							<th <?php if ($i == $t_count){ echo 'id="yith-wcet-total-title"'; } ?> scope="row" colspan="2"><?php echo $total['label']; ?></th>
							<td <?php if ($i == $t_count){ echo 'id="yith-wcet-total-price"'; } ?>><?php echo $total['value']; ?></td>
						</tr><?php
					}
				}
			?>
		</table>
		</div>
	<?php } ?>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text ); ?>

<?php do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text ); ?>

<?php do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text ); ?>

<?php do_action( 'yith_wcet_email_footer', $mail_type); ?>

