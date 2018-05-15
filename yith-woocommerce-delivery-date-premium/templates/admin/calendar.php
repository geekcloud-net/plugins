<?php
if(!defined('ABSPATH')){
    exit;
}



$args = array(
    'post_type' => 'yith_proc_method',
    'post_status' => 'publish'
);

$all_shipping_method = get_posts( $args );

$calendar_event = esc_attr( YITH_Delivery_Date_Calendar()->get_calendar_events( true ) );
?>
<div id="ywcdd_calendar_panel">
    <div class="ywcdd_tools">
        <form id="plugin-fw-wc" class="general-calendar-table" method="post">
            <div class="yith-wcdd-new-holiday-content">
                <h4><?php _e( 'Add new Holiday', 'yith-woocommerce-delivery-date' ) ?></h4>


                <select class="how_holiday wc-enhanced-select" multiple="multiple" placeholder="<?php _e( 'Add holiday to','yith-woocommerce-delivery-date') ;?>">
                    <optgroup label="<?php _e('Order Processing Method','yith-woocommerce-delivery-date');?>">
                    <?php foreach( $all_shipping_method as $method ):?>
                        <option value="<?php esc_attr_e( $method->ID );?>"><?php echo get_the_title( $method->ID );?></option>
                    <?php endforeach;?>
                    </optgroup>
                    <optgroup label="<?php _e('Carrier', 'yith-woocommerce-delivery-date');?>">
                    <?php
                     if( 'yes' == get_option('yith_delivery_date_enable_carrier_system' ) ){
                         $args = array(
                             'post_type' => 'yith_carrier',
                             'post_status' => 'publish'
                         );
                         $all_carrier = get_posts( $args );
                            foreach( $all_carrier as $carrier ):?>
                    <option value="<?php esc_attr_e( $carrier->ID );?>"><?php echo get_the_title( $carrier->ID );?></option>
                    <?php endforeach;
                     }
                    else{?>
                    <option value="carrier_default"><?php _e('Default Carrier (if carrier system is disabled)','yith-woocommerce-delivery-date')?></option>
                    <?php }?>
                    </optgroup>
                </select>
                <input type="text" id="yith_event_name" placeholder="<?php _e('Event name', 'yith-woocommerce-delivery-date' );?>"/>
                <input type="text" id="yith_datepicker_from" class="ywcdd_datepicker"  placeholder="<?php _e('Date From', 'yith-woocommerce-delivery-date' );?>" />
                <input type="text" id="yith_datepicker_to" class="ywcdd_datepicker"  placeholder="<?php _e('Date To', 'yith-woocommerce-delivery-date' );?>" />
                <input type="submit" class="yith-add-new-holiday button button-primary" value="<?php echo esc_attr( __( 'Add', 'yith-woocommerce-delivery-date' ) ) ?>" />
            </div>
        </form>
    </div>
    <div id="ywcdd_general_calendar" style="max-width: 80%;" data-ywcdd_events_json="<?php echo $calendar_event ;?>"></div>
</div>
