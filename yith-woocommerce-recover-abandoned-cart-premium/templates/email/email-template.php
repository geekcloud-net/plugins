<?php
/**
 * HTML Template Email Recover Abandoned Cart
 *
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.0.0
 * @author  Yithemes
 */

extract($args);

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<?php echo $email_content ?>

<?php
    do_action( 'woocommerce_email_footer', $email );
?>
