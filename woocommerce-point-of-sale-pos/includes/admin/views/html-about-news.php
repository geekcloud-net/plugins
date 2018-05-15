<?php
/**
 * Admin View: Page - About
 *
 * @var string $view
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<br><br><br>
<hr>
<div class="feature-section one-col">
	<p class="lead-description">
		<?php 
			$major_version = substr( WC_POS()->_version, 0, 5 );
			printf( __( 'This update brings together our features while revamping our design. Cash float management, bill screen display, inline product discount, keyboard shortcuts and many more.', 'wc_point_of_sale' ), $major_version ); ?></p>
</div>
<div class="feature-section two-col">
	<div class="col">
		<h3><?php _e( 'Cash Float Management', 'wc_point_of_sale' ); ?></h3>
		<img id="ae-logo" src="<?php echo esc_url( WC_POS()->assets_url ); ?>images/about-images/cash.png" alt="Actuality Extensions" />
		<p><?php _e( 'Manage the cash in and out of your register with our easy to use cash management feature. Simply enable it for the register you want to open, an initial float is then set and from the Cash Management button, you can simply add or remove. When closing the register, you can set how many of each currency denomination is left.', 'wc_point_of_sale' ); ?></p>
	</div>
	<div class="col">
		<h3><?php _e( 'Revamped Design', 'wc_point_of_sale' ); ?></h3>
		<img id="ae-logo" src="<?php echo esc_url( WC_POS()->assets_url ); ?>images/about-images/new-design.png" alt="Actuality Extensions" />
		<p><?php _e( 'Our design team has worked hard in cleaning the CSS code and making it more in line with the industry POS systems. While doing this, our register is now more adaptable with different desktop screen sizes. ', 'wc_point_of_sale' ); ?></p>
	</div>
</div>
<div class="feature-section two-col">
	<div class="col">
		<h3><?php _e( 'Multiple Receipt Copies', 'wc_point_of_sale' ); ?></h3>
		<img id="ae-logo" src="<?php echo esc_url( WC_POS()->assets_url ); ?>images/about-images/receipt.png" alt="Actuality Extensions" />
		<p><?php _e( 'Having to print multiple copies of the receipt? Perhaps a customer copy and a merchant copy? You can now easily define how many prints to print. This will also apply to those receipt printers that require a tear off. Each copy is a tear off.', 'wc_point_of_sale' ); ?></p>
	</div>
	<div class="col">
		<h3><?php _e( 'Bill Screen', 'wc_point_of_sale' ); ?></h3>
		<img id="ae-logo" src="<?php echo esc_url( WC_POS()->assets_url ); ?>images/about-images/bill.png" alt="Actuality Extensions" />
		<p><?php _e( 'Displaying the order to your customers has never been easier. Simply open another tab on an external pole display, you can display what is currently in your register, total and tax. Making the experience even more professional to your liking.', 'wc_point_of_sale' ); ?></p>
	</div>
</div>
<div class="feature-section two-col">
	<div class="col">
		<h3><?php _e( 'Product Inline % Discount', 'wc_point_of_sale' ); ?></h3>
		<img id="ae-logo" src="<?php echo esc_url( WC_POS()->assets_url ); ?>images/about-images/inline-discount.jpg" alt="Actuality Extensions" />
		<p><?php _e( 'We have added the ability to define a custom fixed discount or a percentage discount to each product.  You can also reset to the original price of the product at any time. Pre-defined discount presets can also be configured from the Point of Sale > Settings page.', 'wc_point_of_sale' ); ?></p>
	</div>
	<div class="col">
		<h3><?php _e( 'Grid Position', 'wc_point_of_sale' ); ?></h3>
		<img id="ae-logo" src="<?php echo esc_url( WC_POS()->assets_url ); ?>images/about-images/switch.png" alt="Actuality Extensions" />
		<p><?php _e( 'Switch the product grid with the basket to make the interface more familiar to the other POS systems out there. Simply go to Point of Sale > Settings > Layout and chose the switch register position from there.', 'wc_point_of_sale' ); ?></p>
	</div>
</div>
<div class="feature-section two-col">
	<div class="col">
		<h3><?php _e( 'Guest Checkout Compulsory', 'wc_point_of_sale' ); ?></h3>
		<img id="ae-logo" src="<?php echo esc_url( WC_POS()->assets_url ); ?>images/about-images/guest-checkout.jpg" alt="Actuality Extensions" />
		<p><?php _e( 'Make the guest checkout compulsory for those guests you want to book in or products that require custom information. Uncheck this box to make the guest checkout no longer compulsory and a customers information has to be saved.', 'wc_point_of_sale' ); ?></p>
	</div>
	<div class="col">
		<h3><?php _e( 'Gift Receipts', 'wc_point_of_sale' ); ?></h3>
		<img id="ae-logo" src="<?php echo esc_url( WC_POS()->assets_url ); ?>images/about-images/gift-receipt.jpg" alt="Actuality Extensions" />
		<p><?php _e( 'Print gift receipts for your customers during the festive seasons. With this feature, the receipt will print without any price values. You can also toggle this from the payment screen at any time.', 'wc_point_of_sale' ); ?></p>
	</div>
</div>
<div class="feature-section two-col">
	<div class="col">
		<h3><?php _e( 'Product Add-ons Integration', 'wc_point_of_sale' ); ?></h3>
		<img id="ae-logo" src="<?php echo esc_url( WC_POS()->assets_url ); ?>images/about-images/product-addons.jpg" alt="Actuality Extensions" />
		<p><?php _e( 'For the shops that sell customised products like pizza, clothes, etc. We have listened and we have integrated our powerful plugin with a powerful product add-on plugin. You can purchase this plugin from WooCommerce.com, this only supports the official product add-ons plugin.', 'wc_point_of_sale' ); ?></p>
	</div>
	<div class="col">
		<h3><?php _e( 'Keyboard Shortcuts', 'wc_point_of_sale' ); ?></h3>
		<img id="ae-logo" src="<?php echo esc_url( WC_POS()->assets_url ); ?>images/about-images/short.png" alt="Actuality Extensions" />
		<p><?php _e( 'We have made it easier for our users to action common commands using the keyboard shortcuts available. You can visit the page for keyboard shortcuts from Point of Sale > Settings. We hope this makes the process of selling in your store even quicker. ', 'wc_point_of_sale' ); ?></p>
	</div>
</div>