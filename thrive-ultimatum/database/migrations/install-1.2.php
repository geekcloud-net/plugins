<?php
/**
 * Migration for creating required table for emails log
 */
defined( 'TVE_ULT_DB_UPGRADING' ) or exit( '1.0' );

/** @var $wpdb WP_Query */
global $wpdb;

$emails_table = tve_ult_table_name( 'emails' );

$sqls = array();

$sqls[] = "ALTER TABLE {$emails_table} ADD COLUMN `has_impression` TINYINT UNSIGNED NOT NULL DEFAULT 0";

foreach ( $sqls as $sql ) {
	if ( $wpdb->query( $sql ) === false ) {
		return false;
	}
}

return true;
