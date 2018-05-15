<?php
/**
 * Update WC_POS to 3.1.4
 *
 * @author      Actuality Extensions
 * @category    Admin
 * @package     WC_POS/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $wpdb;
$wpdb->hide_errors();
$result = $wpdb->query("ALTER TABLE {$wpdb->users} ADD user_modified_gmt DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER user_registered");
$result = $wpdb->query("UPDATE {$wpdb->users} SET user_modified_gmt=user_registered");