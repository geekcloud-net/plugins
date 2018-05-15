<?php
/**
 * Update WC_POS to 3.2.1
 *
 * @author      Actuality Extensions
 * @category    Admin
 * @package     WC_POS/Admin
 * @version     1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $wpdb;
$wpdb->hide_errors();
if (!($wpdb->get_results("SELECT *
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '{$wpdb->prefix}wc_poin_of_sale_receipts'
            AND table_schema = '{$wpdb->dbname}'
            AND column_name = 'print_customer_phone'"))
) {
    $result['print_customer_phone'] = $wpdb->query("ALTER TABLE {$wpdb->prefix}wc_poin_of_sale_receipts ADD `print_customer_phone` VARCHAR (11) NOT NULL DEFAULT 'yes' ");
}
if (!($wpdb->get_results("SELECT *
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '{$wpdb->prefix}wc_poin_of_sale_receipts'
            AND table_schema = '{$wpdb->dbname}'
            AND column_name = 'customer_phone_label'"))
) {
    $result['customer_phone_label'] = $wpdb->query("ALTER TABLE {$wpdb->prefix}wc_poin_of_sale_receipts ADD `customer_phone_label` VARCHAR (11) NOT NULL DEFAULT 'Telephone' ");
}
