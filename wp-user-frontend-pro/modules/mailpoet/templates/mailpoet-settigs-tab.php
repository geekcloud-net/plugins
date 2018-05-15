<?php
// If MailPoet is active, add as a provider.
if ( class_exists( 'WYSIJA' ) ) {
    global $post;

    $form_settings = get_post_meta( $post->ID, 'wpuf_form_settings', true );

    $enable_mailpoet = isset( $form_settings['enable_mailpoet'] ) ? $form_settings['enable_mailpoet'] : 'no';
    $list_selected = isset( $form_settings['mailpoet_list'] ) ? $form_settings['mailpoet_list'] : '';

    ?>

    <table class="form-table">

        <tr class="wpuf-post-type">
            <th><?php _e( 'Enable Mailpoet', 'wpuf-pro' ); ?></th>
            <td>
                <input type="checkbox" id="enable_mailpoet" name="wpuf_settings[enable_mailpoet]" value="yes" <?php echo ($enable_mailpoet=='yes') ? 'checked': '' ?> > <label for="enable_mailpoet"><?php  _e( 'Enable Mailpoet', 'wpuf-pro' ) ?></label>
            </td>
        </tr>

        <tr class="wpuf-redirect-to <?php echo ($enable_mailpoet=='yes') ? '': 'wpuf-hide' ?>">
            <th><?php _e( 'Select Preferred List', 'wpuf-pro' ); ?></th>
            <td>
                <?php
                $modelList = WYSIJA::get( 'list', 'model' );
                $wysijaLists = $modelList->get( array( 'name', 'list_id' ), array( 'is_enabled' => 1 ) );
                ?>
                <?php if ( $wysijaLists ): ?>
                    <select name="wpuf_settings[mailpoet_list]">
                        <?php
                        foreach ( $wysijaLists as $list ) : ?>
                            <?php printf('<option value="%s"%s>%s</option>', $list['list_id'], selected( $list_selected, $list['list_id'], false ), $list['name'] ); ?>
                        <?php
                        endforeach;
                        ?>
                    </select>
                <?php endif; ?>
                <div class="description">
                    <?php _e( 'Select your mailpoet list for subscriptions', 'wpuf-pro' ) ?>
                </div>
            </td>
        </tr>
    </table>
<?php
} else {
    echo sprintf( '<div style="margin: 15px 0 10px;"><p><strong>%s</strong></p></div>', __( 'You need to install and activate the <a href="http://wordpress.org/plugins/wysija-newsletters/" target="_blank">MailPoet (Wysija)</a> plugin for this option to be available.', 'wpuf-pro' ) );
}
