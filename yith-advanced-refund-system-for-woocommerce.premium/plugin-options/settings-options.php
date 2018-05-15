<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

return array(

    'settings' => apply_filters( 'yith_wcars_settings_options', array(

            'general_start'    => array(
                'type' => 'sectionstart',
                'id'   => 'yith_wcars_settings_general_start'
            ),

            'general_title'    => array(
                'title' => __( 'General settings', 'yith-advanced-refund-system-for-woocommerce' ),
                'type'  => 'title',
                'desc'  => '',
                'id'    => 'yith_wcars_settings_general_title'
            ),

            'general_allow_refunds' => array(
                'title'             => __( 'Allow refunds', 'yith-advanced-refund-system-for-woocommerce' ),
                'type'              => 'checkbox',
                'desc'              => '',
                'id'                => 'yith_wcars_allow_refunds',
                'default'           => 'yes'
            ),

            'general_ndays_refund' => array(
                'title'             => __( 'Number of days for refunds', 'yith-advanced-refund-system-for-woocommerce' ),
                'type'              => 'number',
                'desc'              => __( 'Maximum time (in days) to allow refunds. 0 means that users can always ask for refunds',
                    'yith-advanced-refund-system-for-woocommerce' ),
                'id'                => 'yith_wcars_ndays_refund',
                'custom_attributes' => array(
                    'step' => '1',
                    'min'  => '0'
                ),
                'default'           => '30'
            ),

            'general_enable_taxes' => array(
	            'title'   => __( 'Enable taxes', 'yith-advanced-refund-system-for-woocommerce' ),
	            'type'    => 'checkbox',
	            'desc'    => __( 'Enable this if you want to refund taxes.', 'yith-advanced-refund-system-for-woocommerce' ),
	            'id'      => 'yith_wcars_enable_taxes',
	            'default' => 'yes'
            ),

            'general_end' => array(
                'type' => 'sectionend',
                'id'   => 'yith_wcars_settings_general_end'
            ),

        )
    )
);