<?php
if(!defined('ABSPATH')){
    exit;
}

$settings = array(
        'general-settings' => array(
                'general_section_start' => array(
                    'name' => __('Delivery Settings', 'yith-woocommerce-delivery-date'),
                    'type' => 'title',
                   
                ),
                'general_enable_carrier_system' => array(
                  'name' => __('Carrier system', 'yith-woocommerce-delivery-date'),
                  'desc' => __('Enable the carriers system (N.B. by enabling this option, those already set will be overriden)','yith-woocomerce-delivery-date' ),
                  'type' => 'checkbox',
                  'id' => 'yith_delivery_date_enable_carrier_system',
                  'default' => 'yes'
                ),
                'general_range_day_for_delivery' => array(
                    'name' => __('Estimated Delivery Day', 'yith-woocommerce-delivery-date'),
                    'desc' => __('Set the  number of days essential for your carrier to deliver the order (Set 0 as value if the delivery occurs within the day)','yith-woocommerce-delviery-date'),
                    'type' => 'number',
					'min' => 0,
					'default' => 3,
                 //   'default' => array( 'min' => 1, 'max' => 10 ),
                    'id' => 'yith_delivery_date_range_day'
                ),
                'general_workday' => array(
                  'name' => __('Workday', 'yith-woocomerce-delivery-date' ),
                  'type' =>  'multiselectday',
                  'desc' => __('Select the days on which there are deliveries', 'yith-woocommerce-delivery-date'),
                   'id' => 'yith_delivery_date_workday',
                    'default' => '',
                  'placeholder' => __('Select day', 'yith-woocommerce-delivery-date')
                ),
        		'general_max_range_date' => array(
        			'name' => __('Maximum days that can be selected','yith-woocommerce-delivery-date'),
        			'id' => 'yith_delivery_date_max_range',
        			'desc' => __('Days that can be selected since the first valid date for the delivery','yith-woocommerce-delivery-date'),
        			'type' => 'number',
        			'min' => 0,
        			'default' => 30	
        		),


                'general_section_end' =>    array(
                    'type' => 'sectionend'
                ),
            

        )
);

return $settings;