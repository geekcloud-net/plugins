<?php
if( !defined( 'ABSPATH' ) ) {
    exit;
}

global $post;

$order_id = $post->ID;

$carrier_label = get_post_meta( $order_id, 'ywcdd_order_carrier', true );
$shipping_date = get_post_meta( $order_id, 'ywcdd_order_shipping_date', true );
$delivery_date = get_post_meta( $order_id, 'ywcdd_order_delivery_date', true );
$time_from = get_post_meta( $order_id, 'ywcdd_order_slot_from', true );
$time_to = get_post_meta( $order_id, 'ywcdd_order_slot_to', true );
$date_format = get_option( 'date_format' );
$carrier_id = get_post_meta( $order_id, 'ywcdd_order_carrier_id', true );
$order_has_child =  apply_filters( 'yith_delivery_date_order_has_child', false, $order_id );
$disable_option = $order_has_child ? 'disabled' : '';
$fields = array(
    'carrier' => array(
        'label' => __( 'Carrier', 'yith-woocommerce-delivery-date' ),
        'value' => $carrier_label
    ),
    'shipping_date' => array(
        'label' => __( 'Shipping Date', 'yith-woocommerce-delivery-date' ),
        'value' => sprintf( '%s', ywcdd_get_date_by_format( $shipping_date, $date_format ) ),
    ),
    'delivery_date' => array(
        'label' => __( 'Delivery Date', 'yith-woocommerce-delivery-date' ),
        'value' => ywcdd_get_date_by_format( $delivery_date, $date_format ),
    ),
    'timeslot' => array(
        'label' => __( 'Time Slot', 'yith-woocommerce-delivery-date' ),
        'value' => ( empty( $time_from ) || empty( $time_to ) ) ? '' : sprintf( '%s - %s', ywcdd_display_timeslot( $time_from ),ywcdd_display_timeslot( $time_to ) )
    )
);

$order_shipped = get_post_meta( $order_id, 'ywcdd_order_shipped', true );
$order_shipped = empty( $order_shipped ) ? 'no' : $order_shipped;
?>
<div id="ywcdd_delivery_order_metabox">
    <?php if( $delivery_date != '' ): ?>
        <div id="ywcdd_delivery_details">
            <?php foreach ( $fields as $key => $field ) {

                if( !empty( $field['value'] ) ) {

                    if( 'shipping_date' == $key ) {

                      //  $now = strtotime( 'now midnight' );
                        $timezone_format = 'Y-m-d H:i:s';
                        $now = strtotime( date_i18n( $timezone_format ) );
                        $now = strtotime( 'midnight',  $now  );
                        $to = strtotime( $shipping_date );
                        $days = intval( ( $to-$now ) / DAY_IN_SECONDS );
                        $shipping_message = '';
                        $color_class = '';
                        $message = '';

                     
                        if( 'no' === $order_shipped ) {

                            if( $days >= 0 ) {

                                $color_class = 'ywcdd_advise';
                                $message = __( 'Please, ship this order to the carrier within this date', 'yith-woocommerce-delivery-date' );
                            }
                            else {
                                $color_class = 'ywcdd_error';
                                $message = __( 'You haven\'t shipped the order in time!', 'yith-woocommerce-delivery-date' );
                            }


                        }
                        else {
                            $color_class = 'ywcdd_shipped';
                            $message = __( 'Shipped to carrier', 'yith-woocommerce-delivery-date' );
                        }

                        $shipping_message = sprintf( '<span class="woocommerce-help-tip ywcdd-icon-warning %s" data-tip="%s"></span>', $color_class, $message );

                        echo sprintf( '<p class="%s"><strong>%s:</strong>%s<br/>%s</p>', $key, $field['label'], $shipping_message, $field['value'] );
                    }
                    else {
                        echo sprintf( '<p class="%s"><strong>%s:</strong><br/>%s</p>', $key, $field['label'], $field['value'] );
                    }
                }
            }
            ?>
            <p>
                <label
                    for="ywcdd_order_shipped"><strong><?php _e( 'Shipped to carrier', 'yith-woocommerce-delivery-date' ); ?></strong></label>
                <input type="checkbox" id="ywcdd_order_shipped" name="ywcdd_order_shipped"
                       value="1" <?php checked( 'yes', $order_shipped ); ?> <?php esc_attr_e( $disable_option );?>/>
            </p>
        </div>
        <input type="hidden" name="ywcdd_has_date" value="yes"/>
    <?php endif; ?>
</div>
