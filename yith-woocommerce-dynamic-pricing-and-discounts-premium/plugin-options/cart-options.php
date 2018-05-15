<?php


$cart = array(

    'cart' => array(
        'home' => array(
            'type'   => 'custom_tab',
            'action' => 'ywdpd_cart_rules_tab'
        )
    )
);

return apply_filters( 'ywdpd_panel_cart_rules_tab', $cart );