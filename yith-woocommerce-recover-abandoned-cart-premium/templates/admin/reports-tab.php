<div class="wrap">
    <h2><?php _e('Reports', 'yith-woocommerce-recover-abandoned-cart') ?> </h2>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
                <table class="ywrac-reports" cellpadding="10" cellspacing="0">
                    <tbody>
                    <tr>
                        <th width="20%"><?php _e( 'Abandoned Cart and Pending Orders', 'yith-woocommerce-recover-abandoned-cart' ) ?></th>
                        <td><?php echo $abandoned_carts_counter ?></td>
                    </tr>

                    <tr>
                        <th width="20%"><?php _e( 'Abandoned Carts', 'yith-woocommerce-recover-abandoned-cart' ) ?></th>
                        <td><?php echo $total_abandoned_carts ?></td>
                    </tr>

                    <tr>
                        <th width="20%"><?php _e( 'Order Pending', 'yith-woocommerce-recover-abandoned-cart' ) ?></th>
                        <td><?php echo $total_pending_orders ?> </td>
                    </tr>
                    </tbody>
                </table>
                <table class="ywrac-reports" cellpadding="10" cellspacing="0">
                    <tbody>
                        <tr>
                            <th width="20%"><?php _e('Emails Sent','yith-woocommerce-recover-abandoned-cart') ?></th>
                            <td><?php printf( __('%d (%d Clicks)','yith-woocommerce-recover-abandoned-cart'), $email_sent_counter, $email_clicks_counter)?></td>
                        </tr>
                        <tr>
                            <th width="20%"><?php _e('Emails for Abandoned Carts Sent','yith-woocommerce-recover-abandoned-cart') ?></th>
                            <td><?php printf( __('%d (%d Clicks)','yith-woocommerce-recover-abandoned-cart'), $email_sent_cart_counter, $email_cart_clicks_counter)?></td>
                        </tr>
                        <tr>
                            <th width="20%"><?php _e('Emails for Pending Orders Sent','yith-woocommerce-recover-abandoned-cart') ?></th>
                            <td><?php printf( __('%d (%d Clicks)','yith-woocommerce-recover-abandoned-cart'), $email_sent_order_counter, $email_order_clicks_counter)?></td>
                        </tr>

					</tbody>
				</table>
				<table class="ywrac-reports" cellpadding="10" cellspacing="0">
					<tbody>
                        <tr>
                            <th width="20%"><?php _e('Recovered Carts & Pending Orders','yith-woocommerce-recover-abandoned-cart') ?></th>
                            <td><?php echo $recovered_carts ?></td>
                        </tr>

						<tr>
							<th><?php _e('Recovered Carts','yith-woocommerce-recover-abandoned-cart') ?></th>
							<td><?php echo $total_recovered_carts ?></td>
						</tr>

						<tr>
                            <th><?php _e('Pending Orders Recovered','yith-woocommerce-recover-abandoned-cart') ?></th>
                            <td><?php echo $total_recovered_pending_orders ?></td>
                        </tr>
					</tbody>
				</table>
				<table class="ywrac-reports" cellpadding="10" cellspacing="0">
					<tbody>
                        <tr>
                            <th width="20%"><?php _e('Total Amount Recovered Cart and Pending Orders','yith-woocommerce-recover-abandoned-cart') ?></th>
                            <td><?php echo wc_price($total_amount) ?></td>
                        </tr>

                        <tr>
                            <th><?php _e('Total Amount Recovered Cart','yith-woocommerce-recover-abandoned-cart') ?></th>
                            <td><?php echo wc_price($total_cart_amount) ?></td>
                        </tr>

                        <tr>
                            <th><?php _e('Total Amount Recovered Pending Orders','yith-woocommerce-recover-abandoned-cart') ?></th>
                            <td><?php echo wc_price($total_order_amount) ?></td>
                        </tr>
					</tbody>
				</table>
				<table class="ywrac-reports" cellpadding="10" cellspacing="0">
					<tbody>
					<tr>
						<th width="20%"><?php _e( 'Rate Conversion', 'yith-woocommerce-recover-abandoned-cart' ) ?></th>
						<td><?php echo $rate_conversion ?> %</td>
					</tr>

					<tr>
						<th><?php _e( 'Rate Cart Conversion', 'yith-woocommerce-recover-abandoned-cart' ) ?></th>
						<td><?php echo $rate_cart_conversion ?> %</td>
					</tr>

					<tr>
						<th><?php _e( 'Rate Pending Order Conversion', 'yith-woocommerce-recover-abandoned-cart' ) ?></th>
						<td><?php echo $rate_order_conversion ?> %</td>
					</tr>

					</tbody>
				</table>
            </div>
        </div>
        <br class="clear">
    </div>
</div>
