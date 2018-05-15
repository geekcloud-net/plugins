<?php
/**
 * Update WC_POS to 4.0.0
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
$result['pos_custom_product'] = $wpdb->query("UPDATE $wpdb->posts SET post_type = 'product' WHERE post_type = 'pos_custom_product' ");

