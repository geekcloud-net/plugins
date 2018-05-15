<?php
/**
 * Migration for creating required database tables
 */
defined( 'TVE_ULT_DB_UPGRADING' ) or exit( '1.0' );

/** @var $wpdb WP_Query */
global $wpdb;

$events_table_name       = tve_ult_table_name( 'events' );
$designs_table_name      = tve_ult_table_name( 'designs' );
$templates_table         = tve_ult_table_name( 'settings_templates' );
$campaign_settings_table = tve_ult_table_name( 'settings_campaign' );

$sqls = array();

$sqls[] = "CREATE TABLE IF NOT EXISTS " . tve_ult_table_name( 'events' ) . "(
		`id` BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`campaign_id` BIGINT(20) UNSIGNED NOT NULL,
		`days` INT(5) UNSIGNED NOT NULL DEFAULT 0,
		`hours` INT(5) UNSIGNED NOT NULL DEFAULT 0,
		`trigger_options` TEXT NULL COLLATE 'utf8_general_ci',
		`actions` TEXT NULL COLLATE 'utf8_general_ci',
		`type` ENUM('time','conv', 'start') NULL DEFAULT 'time'
	) COLLATE 'utf8_general_ci'";

$sqls[] = "CREATE TABLE IF NOT EXISTS {$designs_table_name} (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `post_parent` BIGINT(20) NOT NULL,
    `post_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
    `post_type` VARCHAR(20) NOT NULL,
    `post_title` TEXT COLLATE 'utf8_general_ci' NOT NULL,
    `content` LONGTEXT COLLATE 'utf8_general_ci' NULL DEFAULT NULL,
    `tcb_fields` LONGTEXT NULL DEFAULT NULL,
    parent_id INT( 11 ) NULL DEFAULT '0'
) COLLATE = 'utf8_general_ci'";

$sqls[] = "CREATE TABLE IF NOT EXISTS {$templates_table} (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `show_options` LONGTEXT NULL,
    `hide_options` LONGTEXT NULL,
    PRIMARY KEY (`id`)
 )";

$sqls[] = "CREATE TABLE IF NOT EXISTS {$campaign_settings_table} (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `campaign_id` BIGINT(20) NOT NULL,
    `description` VARCHAR(255),
    `show_options` LONGTEXT NULL,
    `hide_options` LONGTEXT NULL,
    PRIMARY KEY (`id`)
)";

$sqls[] = 'CREATE TABLE IF NOT EXISTS ' . tve_ult_table_name( 'event_log' ) . "(
		`id` BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`campaign_id` BIGINT(20) UNSIGNED NOT NULL,
		`date` DATETIME NULL DEFAULT NULL,
		`type` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0
	) COLLATE 'utf8_general_ci'";

foreach ( $sqls as $sql ) {
	if ( $wpdb->query( $sql ) === false ) {
		return false;
	}
}

return true;
