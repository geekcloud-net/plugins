<?php
if( !defined('ABSPATH')){
    exit;
}

$meta_boxes_options = array(
    'label' => __( 'Processing Method settings', 'yith-woocommerce-delivery-date' ),
    'pages' => 'yith_proc_method', //or array( 'post-type1', 'post-type2')
    'context' => 'normal', //('normal', 'advanced', or 'side')
    'priority' => 'default',
    'tabs' => array(
        'carrier_settings' => array(
            'label' => __( 'Settings', 'yith-woocommerce-delivery-date' ),
            'fields' => array(
                'ywcdd_minworkday' => array(
                    'label' => __( 'Workdays', 'yith-woocommerce-delivery-date' ),
                    'desc' => __('Set the minimum number of days required to process an order ( Set 0 if you can ship the package on the same day )','yith-woocommerce-delviery-date'),
                    'type' => 'number',
                    'std' => 3,
                    'min' => 0
                ),
                'ywcdd_list_day' => array(
                    'label' => __('Shipping Day','yith-woocommerce-delivery-date'),
                    'desc' => __('Select the days on which you ship', 'yith-woocommerce-delivery-date'),
                    'type' => 'check_list_day'
                ),

                'ywcdd_carrier' => array(
                    'label' => _x('Select Carrier','allows to select the created carriers','yith-woocommerce-delivery-date'),
                    'type' => 'select_carrier',
                    'desc' => __('If the carriers system has been enabled, select the carriers for this method', 'yith-woocommerce-delivery-date'),
                    'placeholder' => __('Select carriers', 'yith-woocommerce-delivery-date')
                )
            )
        ),
    )
);

return $meta_boxes_options;