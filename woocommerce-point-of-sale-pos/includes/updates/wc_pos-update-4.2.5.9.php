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
            AND column_name = 'show_facebook'"))) {
    $result['show_facebook'] = $wpdb->query("ALTER TABLE {$wpdb->prefix}wc_poin_of_sale_receipts ADD `show_facebook` VARCHAR (11) NOT NULL DEFAULT 'no' ");
}
if (!($wpdb->get_results("SELECT *
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '{$wpdb->prefix}wc_poin_of_sale_receipts'
            AND table_schema = '{$wpdb->dbname}'
            AND column_name = 'show_twitter'"))) {
    $result['show_twitter'] = $wpdb->query("ALTER TABLE {$wpdb->prefix}wc_poin_of_sale_receipts ADD `show_twitter` VARCHAR (11) NOT NULL DEFAULT 'no' ");
}
if (!($wpdb->get_results("SELECT *
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '{$wpdb->prefix}wc_poin_of_sale_receipts'
            AND table_schema = '{$wpdb->dbname}'
            AND column_name = 'show_instagram'"))) {
    $result['show_instagram'] = $wpdb->query("ALTER TABLE {$wpdb->prefix}wc_poin_of_sale_receipts ADD `show_instagram` VARCHAR (11) NOT NULL DEFAULT 'no' ");
}
if (!($wpdb->get_results("SELECT *
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '{$wpdb->prefix}wc_poin_of_sale_receipts'
            AND table_schema = '{$wpdb->dbname}'
            AND column_name = 'show_snapchat'"))) {
    $result['show_snapchat'] = $wpdb->query("ALTER TABLE {$wpdb->prefix}wc_poin_of_sale_receipts ADD `show_snapchat` VARCHAR (11) NOT NULL DEFAULT 'no' ");
}
if (!($wpdb->get_results("SELECT *
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '{$wpdb->prefix}wc_poin_of_sale_receipts'
            AND table_schema = '{$wpdb->dbname}'
            AND column_name = 'socials_display_option'"))) {
    $result['socials_display_option'] = $wpdb->query("ALTER TABLE {$wpdb->prefix}wc_poin_of_sale_receipts ADD `socials_display_option` VARCHAR (11) NOT NULL DEFAULT 'none' ");
}
