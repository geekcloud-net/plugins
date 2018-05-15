<p><?php _e( 'Your order has been received and is now being processed. Your order details are shown below for your reference:', 'woocommerce-email-customizer' ); ?></p>

<a href="#"><?php _e( 'Order', 'woocommerce-email-customizer' ); ?> #2020</a>

<table>
	<thead>
		<tr>
			<th><?php _e( 'Product', 'woocommerce-email-customizer' ); ?></th>
			<th><?php _e( 'Quantity', 'woocommerce-email-customizer' ); ?></th>
			<th><?php _e( 'Price', 'woocommerce-email-customizer' ); ?></th>
		</tr>
	</thead>

	<tbody>
		<tr>
			<td>Ninja Silhouette<br /></td>
			<td>1</td>
			<td>
				<span>$20.00</span> <small><?php _e( '(ex. tax)', 'woocommerce-email-customizer' ); ?></small>
			</td>
		</tr>
	</tbody>

	<tfoot>
		<tr>
			<th colspan="2"><?php _e( 'Subtotal:', 'woocommerce-email-customizer' ); ?></th>
			<td>
				<span>$20.00</span> <small><?php _e( '(ex. tax)', 'woocommerce-email-customizer' ); ?></small>
			</td>
		</tr>

		<tr>
			<th colspan="2"><?php _e( 'Shipping:', 'woocommerce-email-customizer' ); ?></th>
			<td><?php _e( 'Free Shipping', 'woocommerce-email-customizer' ); ?></td>
		</tr>

		<tr>
			<th colspan="2"><?php _e( 'Tax:', 'woocommerce-email-customizer' ); ?></th>
			<td>
				<span>$2.00</span>
			</td>
		</tr>

		<tr>
			<th colspan="2"><?php _e( 'Payment Method:', 'woocommerce-email-customizer' ); ?></th>
			<td><?php _e( 'Direct Bank Transfer', 'woocommerce-email-customizer' ); ?></td>
		</tr>

		<tr>
			<th colspan="2"><?php _e( 'Total:', 'woocommerce-email-customizer' ); ?></th>
			<td>
				<span>$22.00</span>
			</td>
		</tr>
	</tfoot>
</table>
<br />
<table class="addresses">
	<tr>
		<td valign="top" width="50%">
			<h3><?php _e( 'Billing address', 'woocommerce-email-customizer' ); ?></h3>

			<p>
				John Doe<br />
				1234 Fake Street<br />
				WooVille, SA
			</p>
		</td>

		<td valign="top" width="50%">
			<h3><?php _e( 'Shipping address', 'woocommerce-email-customizer' ); ?></h3>

			<p>
				John Doe<br />
				1234 Fake Street<br />
				WooVille, SA
			</p>
		</td>
	</tr>
</table>