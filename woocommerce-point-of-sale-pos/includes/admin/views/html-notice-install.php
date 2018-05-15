<?php
/**
 * Admin View: Notice - Install
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="message" class="updated woocommerce-message">
	<p><?php _e( '<strong>Welcome to WooCommerce Point of Sale</strong> &#8211; You&lsquo;re almost ready to start selling :)', 'wc_point_of_sale' ); ?></p>
	<p class="submit"><a href="<?php echo esc_url( admin_url( 'admin.php?page=wc_pos-setup' ) ); ?>" class="button-primary"><?php _e( 'Run the Setup Wizard', 'wc_point_of_sale' ); ?></a></p>
</div>
