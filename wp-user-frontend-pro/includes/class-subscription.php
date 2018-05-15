<?php

/**
 * Pro Subscription Class
 *
 * @package WPUF\Pro
 */
class WPUF_Pro_Subscription {

    public function __construct() {
        add_action( 'wpuf_admin_subscription_detail', array( $this, 'recurring_payment' ), 10, 4 );
        add_action( 'wpuf_admin_subscription_post_restriction', array( $this, 'post_rollback' ), 10 );
        add_action( 'wpuf_update_subscription_pack', array( $this, 'update_subcription_data' ), 10, 2 );
        add_filter( 'wpuf_get_subscription_meta' , array( $this, 'get_subscription_metadata' ), 10, 2 ) ;
        add_filter( 'wpuf_new_subscription', array( $this, 'set_subscription_meta_to_user' ), 10, 4 );

        add_action( 'trash_post', array( $this, 'restore_post_numbers' ) );

        //subscription notification mail hooks
        add_action( 'wpuf_remove_expired_post_hook', array( $this, 'wpuf_send_subs_notification' ) );
        add_filter( 'wpuf_mail_options', array( $this,'subs_notification_mail_options' ) );
    }

    public function recurring_payment( $sub_meta, $hidden_recurring_class, $hidden_trial_class, $obj ) {
        ?>

        <tr valign="top">
            <th><label><?php _e( 'Recurring', 'wpuf-pro' ); ?></label></th>
            <td>
                <label for="wpuf-recuring-pay">
                    <input type="checkbox" <?php checked( $sub_meta['recurring_pay'], 'yes' ); ?> size="20" style="" id="wpuf-recuring-pay" value="yes" name="recurring_pay" />
                    <?php _e( 'Enable Recurring Payment', 'wpuf-pro' ); ?>
                </label>
            </td>
        </tr>

        <tr valign="top" class="wpuf-recurring-child" style="display: <?php echo $hidden_recurring_class; ?>;">
            <th><label for="wpuf-billing-cycle-number"><?php _e( 'Billing cycle:', 'wpuf-pro' ); ?></label></th>
            <td>
                <select id="wpuf-billing-cycle-number" name="billing_cycle_number">
                    <?php echo $obj->lenght_type_option( $sub_meta['billing_cycle_number'] ); ?>
                </select>

                <select id="cycle_period" name="cycle_period">
                    <?php echo $obj->option_field( $sub_meta['cycle_period'] ); ?>
                </select>
                <div><span class="description"></span></div>
            </td>
        </tr>

        <tr valign="top" class="wpuf-recurring-child" style="display: <?php echo $hidden_recurring_class; ?>;">
            <th><label for="wpuf-billing-limit"><?php _e( 'Billing cycle stop', 'wpuf-pro' ); ?></label></td>
                <td>
                    <select id="wpuf-billing-limit" name="billing_limit">
                        <option value=""><?php _e( 'Never', 'wpuf-pro' ); ?></option>
                        <?php echo $obj->lenght_type_option( $sub_meta['billing_limit'] ); ?>
                    </select>
                    <div><span class="description"><?php _e( 'After how many cycles should billing stop?', 'wpuf-pro' ); ?></span></div>
                </td>
            </th>
        </tr>

        <tr valign="top" class="wpuf-recurring-child" style="display: <?php echo $hidden_recurring_class; ?>;">
            <th><label for="wpuf-trial-status"><?php _e( 'Trial', 'wpuf-pro' ); ?></label></th>
            <td>
                <label for="wpuf-trial-status">
                    <input type="checkbox" size="20" style="" id="wpuf-trial-status" <?php checked( $sub_meta['trial_status'], 'yes' ); ?> value="yes" name="trial_status" />
                    <?php _e( 'Enable trial period', 'wpuf-pro' ); ?>
                </label>
            </td>
        </tr>

        <tr class="wpuf-trial-child" style="display: <?php echo $hidden_trial_class; ?>;">
            <th><label for="wpuf-trial-duration"><?php _e( 'Trial period', 'wpuf-pro' ); ?></label></th>
            <td>
                <select id="wpuf-trial-duration" name="trial_duration">
                    <?php echo $obj->lenght_type_option( $sub_meta['trial_duration'] ); ?>
                </select>
                <select id="trial-duration-type" name="trial_duration_type">
                    <?php echo $obj->option_field( $sub_meta['trial_duration_type'] ); ?>
                </select>
                <span class="description"><?php _e( 'Define the trial period', 'wpuf-pro' ); ?></span>
            </td>
        </tr>
        <?php
    }

    public function post_rollback( $sub_meta ) {
        ?>
        <tr valign="top">
            <th><label><?php _e( 'Post Number Rollback', 'wpuf-pro' ); ?></label></th>
            <td>
                <label>
                    <input type="checkbox" size="20" style="" id="wpuf-postnum-rollback" <?php checked( $sub_meta['postnum_rollback_on_delete'], 'yes' ); ?> value="yes" name="postnum_rollback_on_delete" />
                    <?php _e( 'If enabled, number of posts will be restored if the post is deleted.', 'wpuf-pro' ); ?>
                </label>
            </td>
        </tr>
        <?php
    }

    /**
     * update the meta data of subscription pack
     */
    public function update_subcription_data( $subscription_id, $post ) {
        update_post_meta( $subscription_id, 'postnum_rollback_on_delete', ( isset( $post['postnum_rollback_on_delete'] ) ? $post['postnum_rollback_on_delete'] : '' ) );
    }

    /**
     * get subscription meta data
     */
    public function get_subscription_metadata( $meta, $subscription_id ) {
        $meta['postnum_rollback_on_delete'] = get_post_meta( $subscription_id, 'postnum_rollback_on_delete', true );

        return $meta;
    }


    /**
     * restore number of posts allowed to post when the post is deleted
     */
    function restore_post_numbers( $post_id ) {
        global $current_user;

        $post_type = get_post_type($post_id);
        $post_to_delete = get_post( $post_id );

        if ( in_array( 'administrator', $current_user->roles ) || get_post_field( 'post_author' , $post_id ) == $current_user->ID ) {

            $user_subpack_data = get_user_meta( $post_to_delete->post_author, '_wpuf_subscription_pack', true );

            if ( isset ( $user_subpack_data['postnum_rollback_on_delete'] ) && $user_subpack_data['postnum_rollback_on_delete'] == 'yes'  ) {

                $main_subpack_data = WPUF_Subscription::get_subscription( $user_subpack_data['pack_id'] );

                if ( isset ( $main_subpack_data->meta_value['post_type_name'][ $post_type ] )
                    && isset ( $user_subpack_data['posts'][ $post_type ] )
                    &&  $user_subpack_data['posts'][ $post_type ] < $main_subpack_data->meta_value['post_type_name'][ $post_type ] ) {

                    $user_subpack_data['posts'][ $post_type ]++;
                    update_user_meta( $post_to_delete->post_author, '_wpuf_subscription_pack', $user_subpack_data );
                }
            }
        }
    }


    /**
     * Update meta of user from the data of pack he has been assigned to
     *
     * @param $user_meta
     * @param $user_id
     * @param $pack_id
     * @param $recurring
     *
     * @return mixed
     */
    public static function set_subscription_meta_to_user( $user_meta, $user_id, $pack_id, $recurring ) {

        $subscription = WPUF_Subscription::get_subscription( $pack_id );
        $user_meta['postnum_rollback_on_delete'] = isset( $subscription->meta_value['postnum_rollback_on_delete'] ) ? $subscription->meta_value['postnum_rollback_on_delete'] : '';

        return $user_meta;
    }

    /**
     * Send subscription notification
     */
    function wpuf_send_subs_notification() {
        $users = get_users( array(
            'meta_key' => ' _wpuf_subscription_pack',
        ));

        $date_before =  wpuf_get_option( 'pre_sub_notification_date', 'wpuf_mails', 7 );
        $date_after  =  wpuf_get_option( 'post_sub_notification_date', 'wpuf_mails', 3 );

        foreach ( $users as $user ) {
            $sub       = get_user_meta( $user->ID ,'_wpuf_subscription_pack', true );
            $exp_time  = date_create( $sub['expire'] );
            $curr_time = date_create( date('Y-m-d h:i:s', time()) );
            $time_diff = date_diff( $curr_time, $exp_time );

            if ( ( $curr_time < $exp_time ) && ( $time_diff->d < $date_before ) ) {
                $this->wpuf_pre_sub_exp_notification( $user->user_email );
            } elseif ( ( $curr_time > $exp_time ) && ( $time_diff->d > $date_after ) ) {
                $this->wpuf_post_sub_exp_notification( $user->user_email );
            }
        }

    }

    /**
     * Pre-subscription expiration notification mail
     */
    function wpuf_pre_sub_exp_notification( $user_mail ) {
        $to         = $user_mail;

        $subj       = wpuf_get_option( 'pre_sub_exp_subject', 'wpuf_mails');
        $text_body  = wpautop( wpuf_get_option( 'pre_sub_exp_body', 'wpuf_mails' ) );
        $text_body  = get_formatted_mail_body( $text_body, $subj );

        $headers    = 'Content-Type: text/html; charset=UTF-8';

        wp_mail( $to, $subj, $text_body, $headers );
    }

    /**
     * Post-subscription expiration notification mail
     */
    function wpuf_post_sub_exp_notification( $user_mail ) {
        $to         = $user_mail;

        $subj       = wpuf_get_option( 'post_sub_exp_subject', 'wpuf_mails');
        $text_body  = wpautop( wpuf_get_option( 'post_sub_exp_body', 'wpuf_mails' ) );
        $text_body  = get_formatted_mail_body( $text_body, $subj );

        $headers    = 'Content-Type: text/html; charset=UTF-8';

        wp_mail( $to, $subj, $text_body, $headers );
    }

    function subs_notification_mail_options( $mail_options ) {

        $new_options = array(
            array(
                'name'    => 'subscription_setting',
                'label'   => __( '<span class="dashicons dashicons-money"></span> Subscription', 'wpuf-pro' ),
                'type'    => 'html',
                'class'   => 'subscription-setting',
            ),
            array(
                'name'     => 'enable_subs_notification',
                'class'    => 'wpuf-sub-notification-enabled subscription-setting-option',
                'label'    => __( 'Subscription Notification', 'wpuf-pro' ),
                'desc'     => __( 'Enable Subscription Notification.', 'wpuf-pro' ),
                'default'  => 'no',
                'type'     => 'checkbox',
            ),
            array(
                'name'     => 'pre_sub_notification_date',
                'class'    => 'pre-sub-exp-notify-date',
                'label'    => __( 'Send Notification Before', 'wpuf-pro' ),
                'desc'     => __( 'Send Pre-subscription expiration notice before days', 'wpuf-pro' ),
                'default'  => 7,
                'type'     => 'number',
            ),
            array(
                'name'     => 'post_sub_notification_date',
                'class'    => 'post-sub-exp-notify-date',
                'label'    => __( 'Send Notification After', 'wpuf-pro' ),
                'desc'     => __( 'Send Post-subscription expiration notice after days', 'wpuf-pro' ),
                'default'  => 3,
                'type'     => 'number',
            ),
            array(
                'name'     => 'pre_sub_exp_subject',
                'class'    => 'pre-sub-exp-sub',
                'label'    => __( 'Subscription pre-expiration mail subject', 'wpuf-pro' ),
                'desc'     => __( 'This sets the subject of the emails sent to users before the subscription pack is expired.', 'wpuf-pro' ),
                'default'  => __( 'Your Subscription Pack is expiring!', 'wpuf-pro' ),
                'type'     => 'text',
            ),
            array(
                'name'     => 'pre_sub_exp_body',
                'class'    => 'pre-sub-exp-body',
                'label'    => __( 'Subscription pre-expiration mail body', 'wpuf-pro' ),
                'desc'     => __( "This sets the body of the emails sent to users before the subscription pack is expired.", 'wpuf-pro' ),
                'default'  => __( "Dear Subscriber, \r\n\r\nYour Subscription Pack is expiring! Please buy a new subscription pack.", 'wpuf-pro' ),
                'type'     => 'wysiwyg',
            ),
            array(
                'name'     => 'post_sub_exp_subject',
                'class'    => 'post-sub-exp-sub',
                'label'    => __( 'Subscription post-expiration mail subject', 'wpuf-pro' ),
                'desc'     => __( 'This sets the subject of the emails sent to users after the subscription pack is expired.', 'wpuf-pro' ),
                'default'  => __( 'Your Subscription Pack is expired!', 'wpuf-pro' ),
                'type'     => 'text',
            ),
            array(
                'name'     => 'post_sub_exp_body',
                'class'    => 'post-sub-exp-body',
                'label'    => __( 'Subscription post-expiration mail body', 'wpuf-pro' ),
                'desc'     => __( "This sets the body of the emails sent to users after the subscription pack is expired.", 'wpuf-pro' ),
                'default'  => __( "Dear Subscriber, \r\n\r\nYour Subscription Pack is expired! Please buy a new subscription pack.", 'wpuf-pro' ),
                'type'     => 'wysiwyg',
            )
        );

        return array_merge( $mail_options, $new_options );
    }

}
