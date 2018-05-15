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
$result = array(
    'show_sku' => 'OK',
    'show_outlet' => 'OK',
    'show_register' => 'OK',
    'show_site_name' => 'OK',
    'gift_receipt_title' => 'OK'
);
if (!($wpdb->get_results("SELECT *
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '{$wpdb->prefix}wc_poin_of_sale_receipts'
            AND table_schema = '{$wpdb->dbname}'
            AND column_name = 'show_sku'"))) {
    $result['show_sku'] = $wpdb->query("ALTER TABLE {$wpdb->prefix}wc_poin_of_sale_receipts ADD `show_sku` varchar(255) NOT NULL DEFAULT '' ");
}
if (!($wpdb->get_results("SELECT *
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '{$wpdb->prefix}wc_poin_of_sale_receipts'
            AND table_schema = '{$wpdb->dbname}'
            AND column_name = 'show_outlet'"))) {
    $result['show_outlet'] = $wpdb->query("ALTER TABLE {$wpdb->prefix}wc_poin_of_sale_receipts ADD `show_outlet` varchar(255) NOT NULL DEFAULT '' ");
}
if (!($wpdb->get_results("SELECT *
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '{$wpdb->prefix}wc_poin_of_sale_receipts'
            AND table_schema = '{$wpdb->dbname}'
            AND column_name = 'show_register'"))) {
    $result['show_register'] = $wpdb->query("ALTER TABLE {$wpdb->prefix}wc_poin_of_sale_receipts ADD `show_register` varchar(255) NOT NULL DEFAULT '' ");
}
if (!($wpdb->get_results("SELECT *
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '{$wpdb->prefix}wc_poin_of_sale_receipts'
            AND table_schema = '{$wpdb->dbname}'
            AND column_name = 'show_site_name'"))) {
    $result['show_site_name'] = $wpdb->query("ALTER TABLE {$wpdb->prefix}wc_poin_of_sale_receipts ADD `show_site_name` varchar(255) NOT NULL DEFAULT '' ");
}
if (!($wpdb->get_results("SELECT *
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '{$wpdb->prefix}wc_poin_of_sale_receipts'
            AND table_schema = '{$wpdb->dbname}'
            AND column_name = 'gift_receipt_title'"))) {
    $result['gift_receipt_title'] = $wpdb->query("ALTER TABLE {$wpdb->prefix}wc_poin_of_sale_receipts ADD `gift_receipt_title` varchar(255) NOT NULL DEFAULT 'Gift receipt' ");
}