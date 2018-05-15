<?php

defined( 'ABSPATH' ) or exit;

/**
 * @class      YITH_COG_Report_Links
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     Francisco Mendoza
 *
 */
if ( ! class_exists( 'WC_Admin_Reports' ) ) {
    require_once(ABSPATH . 'wp-content/plugins/woocommerce/includes/admin/class-wc-admin-reports.php');
}


/**
 * Class YITH_COG_Report_Links
 */
class YITH_COG_Report_Stock_Links extends WC_Admin_Reports {

    /**
     * Returns the definitions for the reports to show in admin.
     *
     * @return array
     */
    public static function get_reports() {
        $reports = array(
            'stock-reports'     => array(
                'title'  => '',
                'stock-reports' => array(
                    "all_stock" => array(
                        'title'       => __( 'All stock', 'yith-cost-of-goods-for-woocommerce' ),
                        'description' => '',
                        'hide_title'  => true,
                        'callback'    => array( __CLASS__, 'get_report' ),
                    ),
                    "stock_by_product" => array(
                        'title'       => __( 'Stock by product', 'yith-cost-of-goods-for-woocommerce' ),
                        'description' => '',
                        'hide_title'  => true,
                        'callback'    => array( __CLASS__, 'get_report' ),
                    ),
                    "stock_by_category" => array(
                        'title'       => __( 'Stock by category', 'yith-cost-of-goods-for-woocommerce' ),
                        'description' => '',
                        'hide_title'  => true,
                        'callback'    => array( __CLASS__, 'get_report' ),
                    ),
                ),
            ),

        );

        return $reports;
    }


}
