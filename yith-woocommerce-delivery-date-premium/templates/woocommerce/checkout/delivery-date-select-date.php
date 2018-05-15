<?php
if(!defined('ABSPATH')){
    exit;
}

$class_req = 'yes' === $is_mandatory ? 'validate-required' : '';
$abbr_span = 'yes' === $is_mandatory ? '<abbr class="required" title="required">*</abbr>' : '';
$carrier_system = get_option('yith_delivery_date_enable_carrier_system', 'no') === 'yes';
if( $carrier_system ){
$carriers = YITH_Delivery_Date_Processing_Method()->get_carriers( $processing_method );
$carriers = count( $carriers ) > 1 ? $carriers : $carriers[0];
}
else{
	$carriers = -1;
}

$delivery_date = count( $available_days )> 0 ? min( $available_days ) : '';
$delivery_date_format = !empty( $delivery_date ) ? ywcdd_get_date_by_format( $delivery_date ) : '';
$class_hide = 'ywcdd_hide';

$carrier_form_field = apply_filters( 'ywcdd_change_carrier_label', __( 'Carrier','yith-woocommerce-delivery-date' ) );
$carrier_form_default = apply_filters( 'ywcdd_change_carrier_default_option_label', __( 'Select Carrier','yith-woocommerce-delivery-date' ) );
if( is_array( $carriers ) ):?>
<div class="ywcdd_carrier_content">
    <p class="form-row form-row-wide <?php esc_attr_e( $class_req );?>" >
        <label for="ywcdd_carrier"><?php echo $carrier_form_field;?><?php echo $abbr_span ;?></label>
       <select id="ywcdd_carrier" name="ywcdd_carrier">
           <option value=""><?php echo $carrier_form_default; ?></option>
           <?php foreach( $carriers as $carrier ):
                $carrier_label = get_the_title( $carrier );
               ?>
              <option value="<?php esc_attr_e($carrier);?>"><?php echo $carrier_label;?></option>
            <?php endforeach;?>
       </select>
    </p>
</div>
<?php else:?>
    <input type="hidden" name="ywcdd_carrier" id="ywcdd_carrier" value="<?php echo $carriers;?>"/>
<?php endif;?>
<div class="ywcdd_info_content <?php echo ( $carrier_system || $delivery_date=='' ) ? $class_hide :'';?>" >
    <?php $text = sprintf('%s <strong>%s</strong> <a href="" class="ywcdd_edit_date">%s</a>',__('Your order will be shipped on','yith-woocommerce-delivery-date' ), $delivery_date_format, __('Edit date','yith-woocommerce-delivery-date') );?>
    <span class="ywcdd_message"><?php echo $text;?></span>
</div>
<div class="ywcdd_datepicker_content <?php echo  $class_hide ;?>" >
    <p class="form-row form-row-wide <?php esc_attr_e( $class_req );?>" >
        <label for="ywcdd_datepicker"><?php echo apply_filters( 'ywcdd_change_datepicker_label', __('Delivery Date','yith-woocommerce-delivery-date' ) );?><?php echo $abbr_span ;?></label>
        <input type="text" id="ywcdd_datepicker" name="ywcdd_datepicker" <?php echo !empty( $available_days ) ? 'data-available_days="'.esc_attr( json_encode($available_days)).'"': '';?> value="<?php esc_attr_e( $delivery_date );?>" class="input-text"/>
        <input type="hidden" id="ywcdd_process_method" name="ywcdd_process_method" value="<?php esc_attr_e( $processing_method );?>">
        <input type="hidden" name="ywcdd_is_mandatory" value="<?php esc_attr_e( $is_mandatory );?>">
        <input type="hidden" name="ywcdd_shipping_date" class="ywcdd_shipping_date" />
    </p>
</div>
<?php
wc_get_template('/woocommerce/checkout/delivery-date-select-timeslot.php', array('delivery_date' => $delivery_date, 'is_mandatory'=> $is_mandatory ,'carrier_system_enabled' => $carrier_system), YITH_DELIVERY_DATE_TEMPLATE_PATH, YITH_DELIVERY_DATE_TEMPLATE_PATH );