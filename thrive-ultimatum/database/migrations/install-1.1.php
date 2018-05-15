<?php
/**
 * Migration for creating required table for emails log
 */
defined( 'TVE_ULT_DB_UPGRADING' ) or exit( '1.0' );

/** @var $wpdb WP_Query */
global $wpdb;

$emails_table = tve_ult_table_name( 'emails' );

$sqls = array();

$sqls[] = "CREATE TABLE IF NOT EXISTS {$emails_table} (
    `id` BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `campaign_id` BIGINT(20) UNSIGNED NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `started` DATETIME NOT NULL,
    `type` VARCHAR(255) NOT NULL DEFAULT 'url',
    `end` TINYINT UNSIGNED NOT NULL DEFAULT 0
 )";

foreach ( $sqls as $sql ) {
	if ( $wpdb->query( $sql ) === false ) {
		return false;
	}
}

return true;
