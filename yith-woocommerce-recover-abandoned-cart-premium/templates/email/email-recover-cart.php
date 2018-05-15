<?php
/**
 * Admin new order email
 *
 * @author YITH
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

do_action( 'woocommerce_email_header', $email_heading, $email);
$order_id =  yit_get_order_id( $order );
$billing_first_name = yit_get_prop( $order, '_billing_first_name' );
$billing_last_name = yit_get_prop( $order, '_billing_last_name' );
$order_date       = strtotime( yit_get_prop( $order, 'date_created', true ) );
?>


<h3><?php  _e( 'Order Recovered','yith-woocommerce-recover-abandoned-cart') ?></h3>
<p><?php printf( __( 'You have received an order from %s. The order is as follows:', 'yith-woocommerce-recover-abandoned-cart' ), $billing_first_name . ' ' . $billing_last_name ); ?></p>

<?php do_action( 'woocommerce_email_before_order_table', $order, true, false ); ?>

<h2><a href="<?php echo admin_url( 'post.php?post=' . $order_id . '&action=edit' ); ?>"><?php printf( __( 'Order #%s', 'yith-woocommerce-recover-abandoned-cart'), $order->get_order_number() ); ?></a> (<?php printf( '<time datetime="%s">%s</time>', date_i18n( 'c', $order_date ), date_i18n( wc_date_format(), $order_date ) ); ?>)</h2>

<table cellspacing="0" cellpadding="6" style="width: 80%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<thead>
		<tr>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Product', 'yith-woocommerce-recover-abandoned-cart' ); ?></th>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Quantity', 'yith-woocommerce-recover-abandoned-cart' ); ?></th>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Price', 'yith-woocommerce-recover-abandoned-cart' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
			echo function_exists('wc_get_email_order_items') ? wc_get_email_order_items( $order, false ) : $order->email_order_items_table( false );
		?>
	</tbody>
	<tfoot>
		<?php
			if ( $totals = $order->get_order_item_totals() ) {
				$i = 0;
				foreach ( $totals as $total ) {
					$i++;
					?><tr>
						<th scope="row" colspan="2" style="text-align:left; border: 1px solid #eee; <?php if ( $i == 1 ) echo 'border-top-width: 4px;'; ?>"><?php echo $total['label']; ?></th>
						<td style="text-align:left; border: 1px solid #eee; <?php if ( $i == 1 ) echo 'border-top-width: 4px;'; ?>"><?php echo $total['value']; ?></td>
					</tr><?php
				}
			}
		?>
	</tfoot>
</table>

<?php do_action( 'woocommerce_email_after_order_table', $order, true, false ); ?>

<?php do_action( 'woocommerce_email_order_meta', $order, true, false ); ?>

<?php do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text ); ?>


<?php
do_action( 'woocommerce_email_footer', $email );
?>