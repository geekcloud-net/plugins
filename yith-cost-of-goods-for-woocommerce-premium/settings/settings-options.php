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

    'settings' => apply_filters( 'yith_cog_settings_options', array(

            /* YITH CoG Settings Section. */
            'settings_tab_settings_options_start'    => array(
                'type' => 'sectionstart',
                'id'   => 'yith_settings_tab_start'
            ),

            'settings_tab_settings_options_title'    => array(
                'title' => __( 'General settings', 'Panel: page title', 'yith-cost-of-goods-for-woocommerce' ),
                'type'  => 'title',
                'desc'  => '',
                'id'    => 'yith_cog_settings_tab_title'
            ),
            'settings_tab_fees' => array(
                'title'   => __( 'Include total fees', 'yith-cost-of-goods-for-woocommerce' ),
                'type'    => 'checkbox',
                'desc'    => __( 'The cost related to the payment gateway used will be included in the total product cost', 'Admin option description: ', 'yith-cost-of-goods-for-woocommerce' ),
                'id'      => 'yith_cog_settings_tab_fees',
                'default' => 'no'
            ),
            'settings_tab_shipping' => array(
                'title'   => __( 'Include shipping total cost', 'yith-cost-of-goods-for-woocommerce' ),
                'type'    => 'checkbox',
                'desc'    => __( 'Shipping costs will be included in the total product cost', 'Admin option description: ', 'yith-cost-of-goods-for-woocommerce' ),
                'id'      => 'yith_cog_settings_tab_shipping',
                'default' => 'no'
            ),

            'settings_tab_tax' => array(
                'title'   => __( 'Include taxes cost for each product', 'yith-cost-of-goods-for-woocommerce' ),
                'type'    => 'checkbox',
                'desc'    => __( 'Tax costs will be included in the total product cost', 'Admin option description: ', 'yith-cost-of-goods-for-woocommerce' ),
                'id'      => 'yith_cog_settings_tab_tax',
                'default' => 'no'
            ),

            'settings_tab_settings_options_end'      => array(
                'type' => 'sectionend',
                'id'   => 'yith_settings_tab_end'
            ),



            /* Apply Costs to Previous Orders Section. */
            'previous_orders_tab_start'    => array(
                'type' => 'sectionstart',
                'id'   => 'yith_previous_orders_settings_tab_start'
            ),
            'previous_orders_tab_title'    => array(
                'title' => __( 'Apply costs to previous orders', 'Panel: page title', 'yith-cost-of-goods-for-woocommerce' ),
                'type'  => 'title',
                'desc'  => '',
                'id'    => 'yith_cog_previous_orders_tab'
            ),
            'previous_orders_tab_no_costs_set' => array(
                'title'   => '',
                'desc'    => '',
                'id'      => '',
                'type'  => 'yith_cog_apply_cost_html',
                'html'  => '',
            ),
            'previous_orders_tab_end'      => array(
                'type' => 'sectionend',
                'id'   => 'yith_settings_tab_end'
            ),

            /* Import Costs from WooCommerce Section. */
            'import_cost_tab_start'    => array(
                'type' => 'sectionstart',
                'id'   => 'yith_previous_orders_settings_tab_start'
            ),
            'import_cost_tab_title'    => array(
                'title' => __( 'Import Cost of Goods from WooCommerce', 'Panel: page title', 'yith-cost-of-goods-for-woocommerce' ),
                'type'  => 'title',
                'desc'  => '',
                'id'    => 'yith_cog_import_cost_tab'
            ),
            'import_cost_tab_button' => array(
                'title'   => '',
                'desc'    => '',
                'id'      => '',
                'type'  => 'yith_cog_import_cost_html',
                'html'  => '',
            ),
            'import_cost_tab_end'      => array(
                'type' => 'sectionend',
                'id'   => 'yith_settings_tab_end'
            ),




            /* Add columns settings. */
            'add_columns_tab_start'    => array(
                'type' => 'sectionstart',
                'id'   => 'yith_add_columns_settings_tab_start'
            ),
            'add_columns_tab_title'    => array(
                'title' => __( 'Add custom columns to the report', 'Panel: page title', 'yith-cost-of-goods-for-woocommerce' ),
                'type'  => 'title',
                'desc'  => '',
                'id'    => 'yith_cog_add_columns_tab'
            ),
            'add_columns_custom_fields' => array(
                'title'   => __( 'Add custom field', 'yith-cost-of-goods-for-woocommerce' ),
                'type'    => 'text',
                'desc'    => __( 'Add a custom field to the report table (Separate them with commas for different columns).', 'Admin option description: ', 'yith-cost-of-goods-for-woocommerce' ),
                'id'      => 'yith_cog_add_columns',
                'default' => ''
            ),
            'settings_tab_tag_column' => array(
                'title'   => __( 'Add a tag column to report.', 'yith-cog-for-woocommerce' ),
                'type'    => 'checkbox',
                'desc'    => __( 'Show a tag column for products in the report table', 'Admin option description: ', 'yith-cost-of-goods-for-woocommerce' ),
                'id'      => 'yith_cog_tag_column',
                'default' => 'no'
            ),
            'add_columns_tab_end'      => array(
                'type' => 'sectionend',
                'id'   => 'yith_settings_tab_end'
            ),


        )
    )
);


