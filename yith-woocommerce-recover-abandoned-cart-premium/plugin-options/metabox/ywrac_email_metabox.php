<?php


if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

return array(
    'label'    => __( 'Email Settings', 'yith-woocommerce-recover-abandoned-cart' ),
    'pages'    => 'ywrac_email', //or array( 'post-type1', 'post-type2')
    'context'  => 'normal', //('normal', 'advanced', or 'side')
    'priority' => 'default',
    'tabs'     => array(

        'settings' => array(
            'label'  => __( 'Settings', 'yith-woocommerce-recover-abandoned-cart' ),
            'fields' => apply_filters( 'ywrac_email_metabox', array(
                    'ywrac_email_active'  => array(
                        'label' => __( 'Active', 'yith-woocommerce-recover-abandoned-cart' ),
                        'desc'  => __( 'Choose if activate or deactivate this email', 'yith-woocommerce-recover-abandoned-cart' ),
                        'type'  => 'onoff',
                        'std'   => 'yes' ),
					//@since 1.1.0
                    'ywrac_email_type' => array(
	                    'label' => __( 'Email Type', 'yith-woocommerce-recover-abandoned-cart' ),
	                    'desc'  => __( 'Choose the type for this email', 'yith-woocommerce-recover-abandoned-cart' ),
	                    'type'  => 'select',
	                    'options' => array(
	                        'cart' => __('Abandoned Cart', 'yith-woocommerce-recover-abandoned-cart' ),
		                    'order'   => __('Pending Orders', 'yith-woocommerce-recover-abandoned-cart' )
	                    ),
	                    'std'   => 'abandoned' ),

                    'ywrac_email_subject' => array(
                        'label' => __( 'Email Subject', 'yith-woocommerce-recover-abandoned-cart' ),
                        'desc'  => __( 'Choose the subject for this email', 'yith-woocommerce-recover-abandoned-cart' ),
                        'type'  => 'text',
                        'std'   => '' ),

                    'ywrac_email_auto'  => array(
                        'label' => __( 'Automatic Delivery', 'yith-woocommerce-recover-abandoned-cart' ),
                        'desc'  => __( 'Choose if activate or deactivate automatic delivery for this template', 'yith-woocommerce-recover-abandoned-cart' ),
                        'type'  => 'onoff',
                        'std'   => 'yes' ),

                    'ywrac_type_time'     => array(
                        'label'   => __( 'Send after (unit of measure)', 'yith-woocommerce-recover-abandoned-cart' ),
                        'desc'    => __( 'Choose the unit of measure of the time that has to pass to send the email (e.g., Send after days)', 'yith-woocommerce-recover-abandoned-cart' ),
                        'type'    => 'select',
                        'options' => array(
                            'minutes' => __( 'Minutes', 'yith-woocommerce-recover-abandoned-cart' ),
                            'hours'   => __( 'Hours', 'yith-woocommerce-recover-abandoned-cart' ),
                            'days'    => __( 'Days', 'yith-woocommerce-recover-abandoned-cart' ),
                        ),
                        'std'     => 'hours',
                        'deps'  => array(
                            'ids'    => '_ywrac_email_auto',
                            'values' => 'yes'
                        )),

                    'ywrac_time'          => array(
                        'label' => __( 'Send after (value)', 'yith-woocommerce-recover-abandoned-cart' ),
                        'desc'  => __( 'Set the value of the previous option (e.g., Send after 2)', 'yith-woocommerce-recover-abandoned-cart' ),
                        'type'  => 'text',
                        'std'   => '',
                        'deps'  => array(
                            'ids'    => '_ywrac_email_auto',
                            'values' => 'yes'
                        )
                    ),
                )
            )

        )
    )
);