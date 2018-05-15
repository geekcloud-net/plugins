<?php

class WPUF_pro_Coupon_Elements {

    public static function add_coupon_elements( $obj ) {

        global $post;

        $coupon = WPUF_Coupons::init()->get_coupon_meta( $post->ID );

        $start_date = !empty( $coupon['start_date'] ) ? date_i18n( 'M j, Y', strtotime( $coupon['start_date'] ) ) : '';
        $end_date   = !empty( $coupon['end_date'] ) ? date_i18n( 'M j, Y', strtotime( $coupon['end_date'] ) ) : '';
        $access     = !empty( $coupon['access'] ) ? $coupon['access'] : array( );
        $access = implode( "\n", $access );
        ?>
        <style>
            .chosen-container-multi .chosen-choices {
                height: 30px !important;
            }
        </style>
        <table class="form-table" style="width: 100%">

            <tbody>
            <input type="hidden" name="wpuf_coupon" id="wpuf_coupon_editor" value="<?php echo wp_create_nonce( 'wpuf_coupon_editor' ); ?>" />

            <?php do_action( 'wpuf_admin_coupon_form_top', $post->ID, $coupon ); ?>

            <tr valign="top">
                <td scope="row" class="label" for="wpuf-type"><span><?php _e( 'Type', 'wpuf-pro' ); ?></span></td>

                <td>
                    <select id="wpuf-type" name="type">
                        <option value="amount" <?php selected( $coupon['type'], 'amount' ); ?>><?php _e( 'Fixed Price', 'wpuf-pro' ); ?></option>
                        <option value="percent" <?php selected( $coupon['type'], 'percent' ); ?>><?php _e( 'Percentage', 'wpuf-pro' ); ?></option>
                    </select>
                </td>
            </tr>

            <tr valign="top">
                <td scope="row" class="label"><label for="wpuf-amount"><?php _e( 'Amount', 'wpuf-pro' ); ?></label></td>
                <td>
                    <input type="text" size="25" id="wpuf-amount" value="<?php echo esc_attr( $coupon['amount'] ); ?>" name="amount" />

                    <p class="description"><?php _e( 'Amount without <code>%</code> or currency symbol', 'wpuf-pro' ); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <td scope="row" class="label"><label for="wpuf-content"><?php _e( 'Description', 'wpuf-pro' ); ?></label></td>
                <td>
                    <textarea cols="45" rows="3" id="wpuf-content" name="post_content"><?php echo esc_textarea( $post->post_content ); ?></textarea>

                    <p class="description"><?php _e( 'Give a description of this coupon', 'wpuf-pro' ); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <td scope="row" class="label"><label for="wpuf-package"><?php _e( 'Package', 'wpuf-pro' ); ?></label></td>
                <td>
                    <select id="wpuf-package" multiple name="package[]" style="height: 100px !important;"><?php echo $obj->get_pack_dropdown( $coupon['package'] ); ?></select>
                    <p class="description"><?php _e( 'Select one or more packages to apply coupon', 'wpuf-pro' ); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <td scope="row" class="label"><label for="wpuf-usage-limit"><?php _e( 'Usage Limit', 'wpuf-pro' ); ?></label></td>
                <td>
                    <input type="text" size="25" id="wpuf-usage-limit" value="<?php echo esc_attr( $coupon['usage_limit'] ); ?>" name="usage_limit" />

                    <p class="description"><?php _e( 'How many times the coupon can be used? Give a numeric value.', 'wpuf-pro' ); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <td scope="row" class="label"><label for="wpuf-validity"><?php _e( 'Validity', 'wpuf-pro' ); ?></label></td>
                <td>
                    <input type="text" class="wpuf-date-picker" placeholder="<?php _e( 'Start date', 'wpuf-pro' ); ?>" size="25" id="" value="<?php echo esc_attr( $start_date ); ?>" name="start_date" />
                    <input type="text" class="wpuf-date-picker" placeholder="<?php _e( 'End date', 'wpuf-pro' ); ?>" size="25" id="" value="<?php echo esc_attr( $end_date ); ?>" name="end_date" />
                    <span class="description"></span>
                </td>
            </tr>

            <tr valign="top">
                <td scope="row" class="label"><label for="wpuf-trial-priod"><?php _e( 'Email Restriction', 'wpuf-pro' ); ?></label></td>

                <td>
                    <textarea type="text" size="25" id="wpuf-trial-priod" name="access" /><?php echo esc_attr( $access ); ?></textarea>
                    <p class="description"><?php _e( 'Only users with these email addresses will be able to use this coupon. Enter Email addresses. One per each line.', 'wpuf-pro' ); ?></p>
                </td>
            </tr>

            <?php do_action( 'wpuf_admin_coupon_form_bottom', $post->ID, $coupon ); ?>
            </tbody>
        </table>

        <script type="text/javascript">
            jQuery( function($) {
                $('.wpuf-date-picker').datepicker();
                $('#wpuf-package').chosen({'width':'250px'});
            });
        </script>

        <?php
    }

    public static function check_saving_capability( $post, $update ) {
        return;
    }

}