<?php
if(!defined('ABSPATH')){
    exit;
}

return apply_filters(
    'yith_wcdd_time_slot_options',
    array(
        'delivery-time-slot' => array(
            'timeslot' => array(
                'type' => 'custom_tab',
                'action' => 'yith_wcdd_timeslot_panel',
            	'hide_sidebar'  => true
            )
        		
        )
    )
);