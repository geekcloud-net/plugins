<?php

if( !defined('ABSPATH')){
    exit;
}

$label_fee = sprintf('%s (%s)',__('Fee','yith-woocommerce-delivery-date' ), get_woocommerce_currency_symbol() );
$carrier_system = get_option('yith_delivery_date_enable_carrier_system', 'no') === 'yes';
$block_class = $carrier_system ? 'ywcdd_block' :'';
?>
<div id="yith_wcdd_panel_timeslot" class="<?php esc_attr_e( $block_class );?>">
    <form id="plugin-fw-wc" class="general-timeslot-table" method="post">
        <h3><?php _e('Time Slot Table', 'yith-woocommerce-delivery-date' );?></h3>
        <div class="yith-new-time-slot">
            <h4><?php _e('Add new time slot','yith-woocommerce-delivery-date' );?></h4>
            <input type="text" id="yith_timepicker_from" class="yith_timepicker" name="yith_new_timeslot[timefrom]" placeholder="<?php _e('Time From', 'yith-woocommerce-delivery-date' );?>" />
            <input type="text" id="yith_timepicker_to" class="yith_timepicker" name="yith_new_timeslot[timeto]" placeholder="<?php _e('Time To', 'yith-woocommerce-delivery-date' );?>" />
            <input type="number" id="yith_max_tot_order" min="0" step="1" name="yith_new_timeslot[max_order]" placeholder="<?php _e('Lockout', 'yith-woocommerce-delivery-date' );?>" />
            <input type="number" id="yith_fee" min="0" step="any" name="yith_new_timeslot[fee]" placeholder="<?php echo $label_fee;?>" />
            <input type="submit" class="button button-primary"  value="<?php _e('Add','yith-woocommerce-delivery-date' );?>"/>
           <!-- <input type="hidden" name="plugin_nonce" value="<?php echo YITH_DELIVERY_DATE_SLUG;?>"/>-->
        </div>
        <?php
            require_once( YITH_DELIVERY_DATE_INC.'admin-tables/class.yith-wcdd-time-slot-table.php' );
            $timeslot_table = new YITH_WCDD_Time_Slot_Table( 'yith_delivery_date_time_slot' );
            $timeslot_table->prepare_items();
            $timeslot_table->display();
        ?>
    </form>
</div>
