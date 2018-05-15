<?php
/**
 * Admin new order email
 *
 * @author WooThemes
 * @package WooCommerce/Templates/Emails/HTML
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<?php
	$mail_type = "new_order";
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

<p><?php printf( __( 'You have received an order from %s. The order is as follows:', 'woocommerce' ), $order->billing_first_name . ' ' . $order->billing_last_name ); ?></p>

<?php do_action( 'woocommerce_email_before_order_table', $order, true, false ); ?>

<h2><a href="<?php echo admin_url( 'post.php?post=' . $order->id . '&action=edit' ); ?>"><?php printf( __( 'Order #%s', 'woocommerce'), $order->get_order_number() ); ?></a> (<?php printf( '<time datetime="%s">%s</time>', date_i18n( 'c', strtotime( $order->order_date ) ), date_i18n( wc_date_format(), strtotime( $order->order_date ) ) ); ?>)</h2>

<table id="yith-wcet-order-items-table" cellspacing="0" cellpadding="6" style="width: 100%;">
	<thead>
		<tr>
			<th id="yith-wcet-th-title-product" class="yith-wcet-order-items-table-element" scope="col" ><?php _e( 'Product', 'woocommerce' ); ?></th>
			<th id="yith-wcet-th-title-quantity" class="yith-wcet-order-items-table-element" scope="col" ><?php _e( 'Quantity', 'woocommerce' ); ?></th>
			<th id="yith-wcet-th-title-price" class="yith-wcet-order-items-table-element table_element_price" scope="col" ><?php _e( 'Price', 'woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php echo $order->email_order_items_table( false, true, false, $show_thumbs ); ?>
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

<?php do_action( 'woocommerce_email_after_order_table', $order, true, false ); ?>

<?php do_action( 'woocommerce_email_order_meta', $order, true, false ); ?>

<?php do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text ); ?>

<?php do_action( 'yith_wcet_email_footer', $mail_type); ?>
