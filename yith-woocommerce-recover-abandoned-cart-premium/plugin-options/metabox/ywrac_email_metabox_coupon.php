<?php


if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly


return array(
    'label'    => __( 'Coupon Setting', 'yith-woocommerce-recover-abandoned-cart' ),
    'pages'    => 'ywrac_email', //or array( 'post-type1', 'post-type2')
    'context'  => 'normal', //('normal', 'advanced', or 'side')
    'priority' => 'default',
    'tabs'     => array(
        'coupons' => array(
            'label'  => __( 'Coupons', 'yith-woocommerce-recover-abandoned-cart' ),
            'fields' => apply_filters( 'ywrac_email_metabox_coupons', array(
                    'ywrac_coupon_value' => array(
                        'label' => __( 'Coupon Value', 'yith-woocommerce-recover-abandoned-cart' ),
                        'desc'  => '',
                        'type'  => 'text',
                        'std'   => '' ),

                    'ywrac_coupon_type' => array(
                        'label' => __( 'Coupon type', 'yith-woocommerce-recover-abandoned-cart' ),
                        'desc'  => '',
                        'type'  => 'select',
                        'options' => array(
                            'percent' => __( 'Percentage', 'yith-woocommerce-recover-abandoned-cart' ),
                            'fixed_cart'   => __( 'Amount', 'yith-woocommerce-recover-abandoned-cart' ),
                        ),
                        'std'   => 'percent' ),

                    'ywrac_coupon_validity' => array(
                        'label' => __( 'Validity in days', 'yith-woocommerce-recover-abandoned-cart' ),
                        'desc'  => '',
                        'type'  => 'text',
                        'std'   => '7' ),

                )

            )
        )
    )
);