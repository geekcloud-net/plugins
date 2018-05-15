<table class="items-table" cellpadding="0" cellspacing="0">
    <thead>
        <tr class="items-list-header">
            <?php echo '<th>' . __( 'Invoice', 'wpuf-pro' ) . '</th>'; ?>
            <?php echo '<th>' . __( 'Download Link', 'wpuf-pro' ) . '</th>'; ?>
        </tr>
    </thead>
    <tbody>
        <tr>
        <?php
        global $wpdb;
        $user_id = get_current_user_id();
        $sql = $wpdb->prepare( "SELECT transaction_id
            FROM " . $wpdb->prefix . "wpuf_transaction
            WHERE user_id = %s", $user_id );

        $results = $wpdb->get_results( $sql );

        if ( !empty( $results ) ) {
            foreach ( $results as $result) {
                $t_id = (array) $result;
                ?>
                <td>
                    <h4><?php echo $t_id['transaction_id'] ?></h4>
                </td>
                <td>
                    <?php $var =  get_user_meta ( $user_id, '_invoice_link' . $t_id['transaction_id'], true );  ?>
                    <a href="<?php echo $var ?>"><?php _e( 'Download', 'wpuf-pro' ); ?></a>

                </td>
                </tr>
                <?php
            }
        }
        ?>

    </tbody>
</table>
