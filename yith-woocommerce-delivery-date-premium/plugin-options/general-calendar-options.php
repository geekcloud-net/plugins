<?php
if(!defined('ABSPATH')){
    exit;
}

return apply_filters(
    'yith_wcdd_general_calendar_options',
    array(
        'general-calendar' => array(
            'general_calendar' => array(
                'type' => 'custom_tab',
                'action' => 'yith_wcdd_general_calendar_tab',
                'hide_sidebar'  => true
            )

        )
    )
);