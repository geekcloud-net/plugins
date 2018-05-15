<?php


$pricing = array(

    'pricing' => array(
        'home' => array(
            'type'   => 'custom_tab',
            'action' => 'ywdpd_price_rules_tab'
        )
    )
);

return apply_filters( 'ywdpd_panel_price_rules_tab', $pricing );
