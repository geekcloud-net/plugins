<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

return array(

    'general' => array(

        'ywmmq_main_section_title'    => array(
            'name' => __( 'Minimum Maximum Quantity settings', 'yith-woocommerce-minimum-maximum-quantity' ),
            'type' => 'title',
        ),
        'ywmmq_enable_plugin'         => array(
            'name'    => __( 'Enable YITH WooCommerce Minimum Maximum Quantity', 'yith-woocommerce-minimum-maximum-quantity' ),
            'type'    => 'checkbox',
            'desc'    => '',
            'id'      => 'ywmmq_enable_plugin',
            'default' => 'yes',
        ),
        'ywmmq_main_section_end'      => array(
            'type' => 'sectionend',
        ),

        'ywmmq_cart_section_title'    => array(
            'name' => __( 'Cart restrictions', 'yith-woocommerce-minimum-maximum-quantity' ),
            'type' => 'title',
        ),
        'ywmmq_cart_minimum_quantity' => array(
            'name'                => __( 'Minimum quantity restriction', 'yith-woocommerce-minimum-maximum-quantity' ),
            'type'                => 'number',
            'desc'                => __( 'Minimum number of items in cart. Set zero for no restrictions.' ),
            'id'                  => 'ywmmq_cart_minimum_quantity',
            'default'             => '0'
            , 'custom_attributes' => array(
                'min'      => 0,
                'required' => 'required'
            )
        ),
        'ywmmq_cart_maximum_quantity' => array(
            'name'              => __( 'Maximum quantity restriction', 'yith-woocommerce-minimum-maximum-quantity' ),
            'type'              => 'number',
            'desc'              => __( 'Maximum number of items in cart. Set zero for no restrictions.' ),
            'id'                => 'ywmmq_cart_maximum_quantity',
            'default'           => '0',
            'custom_attributes' => array(
                'min'      => 0,
                'required' => 'required'
            )
        ),
        'ywmmq_cart_section_end'      => array(
            'type' => 'sectionend',
        ),

    )

);