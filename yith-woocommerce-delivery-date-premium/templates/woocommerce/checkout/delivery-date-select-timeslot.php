<?php
if( !defined('ABSPATH') ){
    exit;
}
$class_hide = 'ywcdd_hide';
$json_slot = array();
$json_slot_count = 0;

if( $delivery_date != '' && !$carrier_system_enabled ){
    $available_timeslot = YITH_Delivery_Date_Shipping_Manager()->get_available_timeslot( -1, $delivery_date );
    $json_slot = YITH_Delivery_Date_Shipping_Manager()->format_timeslot( $available_timeslot );
    $json_slot_count = count( $json_slot );
}

$class_req = 'yes' === $is_mandatory && $json_slot_count > 0 ? 'validate-required' : '';
$abbr_span = 'yes' === $is_mandatory && $json_slot_count > 0 ? '<abbr class="required" title="required">*</abbr>' : '';

?>

<div class="ywcdd_timeslot_content <?php echo ( $carrier_system_enabled || $delivery_date=='' ) ? $class_hide :'';?>">

    <div class="ywcdd_timeslot_info <?php echo count( $json_slot ) > 0 ? '' : $class_hide;?>">
        <?php echo sprintf('%s %s <a href="" class="ywcdd_show_timeslot">%s</a>',__('You can select a time slot for this day','yith-woocommerce-delivery-date'),$abbr_span,  __('Add','yith-woocommerce-delivery-date') );?>
    </div>
    <p class="form-row form-row-wide form-row-slot ywcdd_hide <?php echo $class_req;?>" >
        <label for="ywcdd_timeslot"><?php echo apply_filters( 'ywcdd_change_timeslot_label', __('Time Slot','yith-woocommerce-delivery-date' ) ); ?><?php echo $abbr_span;?></label>

        <select id="ywcdd_timeslot" name="ywcdd_timeslot">
            <option value=""><?php _e('Select time slot','yith-woocommerce-delivery-date' );?></option>
            <?php
            if( $json_slot_count > 0 ):
                foreach( $json_slot as $key=>$timeslot ):?>
                    <option value="<?php esc_attr_e($key);?>"><?php echo $timeslot ;?></option>
                <?php endforeach;endif;?>
        </select>
        <input type="hidden" name="ywcdd_timeslot_av" class="ywcdd_timeslot_av" />
    </p>
</div>
