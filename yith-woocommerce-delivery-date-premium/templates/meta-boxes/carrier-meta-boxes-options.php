<?php
if( !defined('ABSPATH')){
    exit;
}

$meta_boxes_options = array(
    'label' => __( 'Carrier settings', 'yith-woocommerce-delivery-date' ),
    'pages' => 'yith_carrier', //or array( 'post-type1', 'post-type2')
    'context' => 'normal', //('normal', 'advanced', or 'side')
    'priority' => 'default',
    'tabs' => array(
        'carrier_settings' => array(
            'label' => __( 'Settings', 'yith-woocommerce-delivery-date' ),
            'fields' => array(
                'ywcdd_dayrange' => array(
                    'label' => __( 'Estimated Delivery Day', 'yith-woocommerce-delivery-date' ),
                    'desc' => __('Set the number of days essential to your carrier to deliver the order (Set 0 as value if the deliver occurs within the day)','yith-woocommerce-delviery-date'),
                    'type' => 'number',
                    'min'   => 0,
                    'std' => 3
                ),
                'ywcdd_workday' => array(
                    'label' => _x('Workday', 'workdays', 'yith-woocommerce-delivery-date' ),
                    'desc' => __('Choose the carriers workdays', 'yith-woocommerce-delivery-date' ),
                    'type' => 'multiselectday',
                    'placeholder' => __('Select a day','yith-woocommerce-delivery-date')
                ),
                'ywcdd_max_selec_orders' => array(
                    'label' => _x('Maximum days for selection','days that can be selected starting from', 'yith-woocommerce-delivery-date'),
                     'desc' => __('Days that can be selected since the first valid date for the delivery'),
                    'type' => 'number',
                    'std'   =>  30,
                    'min'   =>  0,
                )
            )
        ),
        'time_slot_settings' => array(
            'label' => __('Time Slot', 'yith-woocommerce-delivery-date'),
            'fields' => array(
                'ywcdd_addtimeslot' => array(
                    'label' => __('Add time slot', 'yith-woocommerce-delivery-date'),
                    'type' => 'addtimeslot',
                    'desc' => ''
                    ),
                )

        )
    )
);

return $meta_boxes_options;