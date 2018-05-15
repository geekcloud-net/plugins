<?php
if(!defined('ABSPATH')){
    exit;
}

$checkout_message= sprintf('%s {ywcdd_shipping_date}','' );
$settings = array(
    'colors-labels' => array(
    	'delivery_mode_section_start' => array(
    		'name' => __('Delivery Settings', 'yith-woocommerce-delivery-date' ),
    		'type' => 'title'	
    	),	
    	'delivery_mode' => array(
    			'name' => __('Show DatePicker', 'yith-woocommerce-delivery-date' ),
    			'type' => 'checkbox',
    			'id' => 'ywcdd_delivery_mode',
    			'desc' => __('If checked, the datepicker is always shown in frontend', 'yith-woocommerce-delivery-date' ),
    			'default' => 'no',
    			
    	)	,
    	'time_step' => array(
    	    'name' => __( 'Time increments', 'yith-woocommerce-delivery-date' ),
		    'type' => 'number',
		    'id' => 'ywcdd_timeslot_step',
		    'desc' => __( 'Set how users will choose the delivery time: let them choose any type of increments, from 1 minute to 1 hour increments.', 'yith-woocommerce-delivery-date' ),
		    'custom_attributes'=> array('min' => 1,'max' => 60 ),
		    'default' => 30
	    ),
    	'delivery_mode_section_end' => array(
    			'type' => 'sectionend'
    	)	,
        'color_label_section_start' => array(
            'name' => __('Calendar Colors', 'yith-woocommerce-delivery-date'),
            'type' => 'title',
        ),
    'calendar_color_shipp' => array(
    		'name'=>__('Shipping Event Color','yith-woocommerce-delivery-date'),
    		'type'=> 'color',
    		'id' => 'ywcdd_shipping_to_carrier_color',
    		'default' => '#ff643e'
    )		,
    		'calendar_color_delivery' => array(
    				'name'=>__('Delivery Event Color','yith-woocommerce-delivery-date'),
    				'type'=> 'color',
    				'id' => 'ywcdd_delivery_day_color',
    				'default' => '#a3c401'
    		)		,
    		'calendar_color_holiday' => array(
    				'name'=>__('Holiday Event Color','yith-woocommerce-delivery-date'),
    				'type'=> 'color',
    				'id' => 'ywcdd_holiday_color',
    				'default' => '#1197C1'
    		),
        'color_label_section_end' =>    array(
            'type' => 'sectionend'
        ),

        'add_event_into_calendar_start' => array(
            'type' => 'title',
            'name' => __('Event Calendar settings', 'yith-woocommerce-delivery-date')
        ),
        'add_event_into_calendar' => array(
            'type' => 'select_order_status',
            'name'  => __( 'Order status', 'yith-woocommerce-delivery-date' ),
            'desc' => __('Add events to the calendar when the order is marked with one or more of the following statuses', 'yith-woocommerce-delivery-date' ),
            'default' => array( 'completed', 'processing' ),
            'id' => 'ywcdd_add_event_into_calendar'

        ),
        'add_event_into_calendar_end' =>    array(
            'type' => 'sectionend'
        ),



    )
);

return $settings;