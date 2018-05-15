<?php
/**
 * YITH WooCommerce Recover Abandoned Cart Content metabox template
 *
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.0.0
 * @author  Yithemes
 */

?>
<table class="yith-ywrac-info-cart" cellspacing="20">
    <tbody>
        <tr>
            <th><?php _e('Cart Status:','yith-woocommerce-recover-abandoned-cart') ?></th>
            <td><span class="<?php echo $status ?>"><?php echo $status ?></span></td>
        </tr>

        <tr>
            <th><?php _e('Cart Last Update:','yith-woocommerce-recover-abandoned-cart') ?></th>
            <td><?php echo $last_update ?></td>
        </tr>


        <tr>
            <th><?php _e('User:','yith-woocommerce-recover-abandoned-cart') ?></th>
            <td><?php echo $user_first_name.' '.$user_last_name ?></td>
        </tr>

        <tr>
            <th><?php _e('User email:','yith-woocommerce-recover-abandoned-cart') ?></th>
            <td><?php echo '<a href="mailto:'.$user_email.'">'.$user_email.'</a>' ?></td>
        </tr>

        <tr>
            <th><?php _e('User phone:','yith-woocommerce-recover-abandoned-cart') ?></th>
            <td><?php echo $user_phone ?></td>
        </tr>


        <tr>
            <th><?php _e('Language:','yith-woocommerce-recover-abandoned-cart') ?></th>
            <td><?php echo $language ?></td>
        </tr>


        <tr>
            <th><?php _e('Currency:','yith-woocommerce-recover-abandoned-cart') ?></th>
            <td><?php echo $currency ?></td>
        </tr>


        <?php if( !empty( $history )): ?>
        <tr>
            <th><?php _e('History:','yith-woocommerce-recover-abandoned-cart') ?></th>
            <td>
                <table class="ywrac-history-table" cellpadding="5">
                    <tr>
                        <th><?php _e('Sending Date','yith-woocommerce-recover-abandoned-cart') ?></th>
                        <th><?php _e('Email Template','yith-woocommerce-recover-abandoned-cart') ?></th>
                        <th><?php _e('Link Clicked','yith-woocommerce-recover-abandoned-cart') ?></th>
                    </tr>
                <?php foreach( $history as $h ): ?>
                    <tr>
                        <td><?php echo $h['data_sent'] ?></td>
                        <td><?php echo $h['email_name'] ?></td>
                        <td><?php echo ( $h['clicked'] == 0 ) ? 'no' : 'yes'  ?></td>
                    </tr>

                <?php endforeach ?>
                </table>

            </td>
        </tr>
    <?php endif ?>


    </tbody>
</table>