<?php

class WPLA_UpgradeHelper {
	

	// upgrade db
	static public function upgradeDB() {
		global $wpdb;

		$db_version = get_option('wpla_db_version', 0);
		$hide_message = $db_version == 0 ? true : false;
		$msg = false;

		// initialize db with version 1
		if ( 1 > $db_version ) {
			$new_db_version = 1;

			// set some defaults for new installations
			update_option('wpla_update_channel', 'stable');
			update_option('wpla_enable_missing_details_warning', '1');
			
			// set update interal to 5min by default
			update_option('wpla_cron_schedule', 'five_min');
			wp_schedule_event( time(), 'five_min', 'wpla_update_schedule' );

			// set admin_email as default license_email
			// update_option('wpla_license_email', get_bloginfo('admin_email') );

			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 2  (0.1)
		if ( 2 > $db_version ) {
			$new_db_version = 2;
		

			// create table: amazon_listings
			$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}amazon_listings` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `asin` varchar(64) DEFAULT NULL,
			  `sku` varchar(64) DEFAULT NULL,
			  `listing_title` varchar(255) DEFAULT NULL,
			  `listing_type` varchar(255) DEFAULT NULL,
			  `date_created` datetime DEFAULT NULL,
			  `date_published` datetime DEFAULT NULL,
			  `price` float DEFAULT NULL,
			  `quantity` int(11) DEFAULT NULL,
			  `quantity_sold` int(11) DEFAULT NULL,
			  `status` varchar(50) DEFAULT NULL,
			  `details` text,
			  `attributes` text,
			  `history` text,
			  `url` varchar(255) DEFAULT NULL,
			  `description` text,
			  `post_id` int(11) DEFAULT NULL,
			  `profile_id` int(11) DEFAULT NULL,
			  `fees` float DEFAULT NULL,
			  PRIMARY KEY  (`id`)
			) DEFAULT CHARSET=utf8 ;";
			#dbDelta($sql);
			$wpdb->query($sql);
						
			// create table: amazon_categories
			$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}amazon_categories` (
			  `cat_id` bigint(16) DEFAULT NULL,
			  `parent_cat_id` bigint(11) DEFAULT NULL,
			  `level` int(11) DEFAULT NULL,
			  `leaf` tinyint(4) DEFAULT NULL,
			  `version` int(11) DEFAULT NULL,
			  `cat_name` varchar(255) DEFAULT NULL,
			  `wp_term_id` int(11) DEFAULT NULL,
			  KEY `cat_id` (`cat_id`),
			  KEY `parent_cat_id` (`parent_cat_id`)		
			) DEFAULT CHARSET=utf8 ;";
			$wpdb->query($sql);
						
			// create table: amazon_payment
			$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}amazon_payment` (
			  `payment_name` varchar(255) DEFAULT NULL,
			  `payment_description` varchar(255) DEFAULT NULL,
			  `version` int(11) DEFAULT NULL	
			) DEFAULT CHARSET=utf8 ;";
			$wpdb->query($sql);
						
			// create table: amazon_profiles
			// $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}amazon_profiles` (
			//   `profile_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			//   `profile_name` varchar(255) DEFAULT NULL,
			//   `profile_description` varchar(255) DEFAULT NULL,
			//   `listing_duration` varchar(255) DEFAULT NULL,
			//   `type` varchar(255) DEFAULT NULL,
			//   `details` text,
			//   `conditions` text,
			//   PRIMARY KEY  (`profile_id`)	
			// ) DEFAULT CHARSET=utf8 ;";
			// $wpdb->query($sql);
						
			// create table: amazon_shipping
			$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}amazon_shipping` (
			  `service_id` int(11) DEFAULT NULL,
			  `service_name` varchar(255) DEFAULT NULL,
			  `service_description` varchar(255) DEFAULT NULL,
			  `carrier` varchar(255) DEFAULT NULL,
			  `international` tinyint(4) DEFAULT NULL,
			  `version` int(11) DEFAULT NULL	
			) DEFAULT CHARSET=utf8 ;";
			$wpdb->query($sql);
			
			// create table: amazon_log
			$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}amazon_log` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `timestamp` datetime DEFAULT NULL,
			  `request_url` text,
			  `request` text,
			  `response` text,
			  `result` text,
			  `callname` varchar(64) DEFAULT NULL,
			  `success` varchar(16) DEFAULT NULL,
			  `amazon_id` bigint(255) DEFAULT NULL,
			  `user_id` int(11) DEFAULT NULL,
			  `curl` text,
			  `parameters` text,
			  `account_id` int(11) DEFAULT NULL,
			  `market_id` int(11) DEFAULT NULL,
			  `http_code` varchar(11) DEFAULT NULL,
			  PRIMARY KEY (`id`)	
			) DEFAULT CHARSET=utf8 ;";
			$wpdb->query($sql);

			// create table: amazon_jobs
			$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}amazon_jobs` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `job_key` varchar(64) DEFAULT NULL,
			  `job_name` varchar(64) DEFAULT NULL,
			  `tasklist` text DEFAULT NULL,
			  `results` text DEFAULT NULL,
			  `success` varchar(16) DEFAULT NULL,
			  `date_created` datetime DEFAULT NULL,
			  `date_finished` datetime DEFAULT NULL,
			  `user_id` int(11) DEFAULT NULL,	
			  PRIMARY KEY (`id`)	
			) DEFAULT CHARSET=utf8 ;";
			$wpdb->query($sql);	echo $wpdb->last_error;

			// create table: amazon_orders
			$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}amazon_orders` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `order_id` varchar(128) DEFAULT NULL,
			  `date_created` datetime DEFAULT NULL,
			  `total` float DEFAULT NULL,
			  `status` varchar(50) DEFAULT NULL,
			  `post_id` int(11) DEFAULT NULL,
			  `items` text,
			  `details` text,
			  `history` text,
			  `buyer_userid` varchar(255) DEFAULT NULL,
			  `buyer_name` varchar(255) DEFAULT NULL,
			  `buyer_email` varchar(255) DEFAULT NULL,
			  `CheckoutStatus` varchar(50) DEFAULT NULL,
			  `ShippingService` varchar(50) DEFAULT NULL,
			  `PaymentMethod` varchar(50) DEFAULT NULL,
			  `ShippingAddress_City` varchar(50) DEFAULT NULL,
			  `CompleteStatus` varchar(50) DEFAULT NULL,
			  `LastTimeModified` datetime DEFAULT NULL,
			  `account_id` int(11) DEFAULT NULL,
			  PRIMARY KEY (`id`)
	  		) DEFAULT CHARSET=utf8 ;";
			$wpdb->query($sql);


			// $db_version = $new_db_version;
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}
		

		// upgrade to version 3  (0.1)
		if ( 3 > $db_version ) {
			$new_db_version = 3;
		
			// create table: amazon_markets
			$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}amazon_markets` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `developer_id` varchar(32) NOT NULL,
			  `title` varchar(255) NOT NULL,
			  `code` varchar(255) NOT NULL,
			  `url` varchar(255) NOT NULL,
			  `enabled` tinyint(2) unsigned NOT NULL DEFAULT '0',
			  `sort_order` int(11) unsigned NOT NULL DEFAULT '0',
			  `group_title` varchar(255) NOT NULL,
			  PRIMARY KEY  (`id`)
			) DEFAULT CHARSET=utf8 ;";
			$wpdb->query($sql);

			// create table: amazon_accounts
			$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}amazon_accounts` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `title` varchar(128) NOT NULL,
			  `merchant_id` varchar(32) NOT NULL DEFAULT '',
			  `marketplace_id` varchar(32) NOT NULL DEFAULT '',
			  `access_key_id` varchar(32) NOT NULL DEFAULT '',
			  `secret_key` varchar(64) NOT NULL DEFAULT '',
			  `market_id` int(11) DEFAULT NULL,
			  `market_code` varchar(16) DEFAULT NULL,
			  `config` text NOT NULL,
			  `sync_orders` int(11) DEFAULT NULL,
			  `sync_products` int(11) DEFAULT NULL,
			  `last_orders_sync` datetime DEFAULT NULL,
			  PRIMARY KEY  (`id`)
			) DEFAULT CHARSET=utf8 ;";
			$wpdb->query($sql);
						

			// $db_version = $new_db_version;
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}
		
		// upgrade to version 4  (0.2)
		if ( 4 > $db_version ) {
			$new_db_version = 4;
		
			// create table: amazon_markets
			$sql = "INSERT INTO `{$wpdb->prefix}amazon_markets` 
				(`id`, `developer_id`, `title`, `code`, `url`, `enabled`, `sort_order`, `group_title`)
				VALUES
					(29,'','United States','US','amazon.com',1,1,'America'),
					(28,'','United Kingdom','UK','amazon.co.uk',1,2,'Europe'),
					(24,'','Canada','CA','amazon.ca',1,3,'America'),
					(25,'','Germany','DE','amazon.de',1,4,'Europe'),
					(26,'','France','FR','amazon.fr',1,5,'Europe'),
					(31,'','Italy','IT','amazon.it',1,6,'Europe'),
					(30,'','Spain','ES','amazon.es',1,7,'Europe'),
					(27,'','Japan','JP','amazon.co.jp',0,8,'Asia / Pacific'),
					(32,'','China','CN','amazon.cn',0,9,'Asia / Pacific');
			";
			$wpdb->query($sql);
						
			// $db_version = $new_db_version;
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}
		
		// upgrade to version 5  (0.2)
		if ( 5 > $db_version ) {
			$new_db_version = 5;

			// create table: amazon_reports
			$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}amazon_reports` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `ReportRequestId` varchar(64) DEFAULT NULL,
			  `ReportType` varchar(64) DEFAULT NULL,
			  `ReportProcessingStatus` varchar(64) DEFAULT '',
			  `results` text,
			  `success` varchar(16) DEFAULT NULL,
			  `SubmittedDate` datetime DEFAULT NULL,
			  `StartedProcessingDate` datetime DEFAULT NULL,
			  `CompletedDate` datetime DEFAULT NULL,
			  `GeneratedReportId` varchar(64) DEFAULT NULL,
			  `account_id` int(11) DEFAULT NULL,
			  `line_count` int(11) DEFAULT NULL,
			  `data` longblob,
			  PRIMARY KEY  (`id`)
			) DEFAULT CHARSET=utf8 ;";
			$wpdb->query($sql);						

			// add column to amazon_listings table
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings`
			        ADD COLUMN `account_id` int(11) DEFAULT NULL AFTER `profile_id`
			";
			$wpdb->query($sql);						

			// $db_version = $new_db_version;
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 6  (0.3.1)
		if ( 6 > $db_version ) {
			$new_db_version = 6;

			// create table: amazon_feeds
			$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}amazon_feeds` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `FeedSubmissionId` varchar(64) DEFAULT NULL,
			  `FeedType` varchar(64) DEFAULT NULL,
			  `FeedProcessingStatus` varchar(64) DEFAULT '',
			  `results` text,
			  `success` varchar(16) DEFAULT NULL,
			  `status` varchar(16) DEFAULT NULL,
			  `SubmittedDate` datetime DEFAULT NULL,
			  `CompletedProcessingDate` datetime DEFAULT NULL,
			  `date_created` datetime DEFAULT NULL,
			  `MarketplaceIdList` varchar(255) DEFAULT NULL,
			  `account_id` int(11) DEFAULT NULL,
			  `line_count` int(11) DEFAULT NULL,
			  `data` mediumblob,
			  PRIMARY KEY  (`id`)
			) DEFAULT CHARSET=utf8 ;";
			$wpdb->query($sql);						

			// $db_version = $new_db_version;
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 7  (0.5.2)
		if ( 7 > $db_version ) {
			$new_db_version = 7;

			// add column to amazon_accounts table
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_accounts`
			        ADD COLUMN `allowed_markets` TEXT AFTER `market_code`
			";
			$wpdb->query($sql);						

			// $db_version = $new_db_version;
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 8  (0.5.5)
		if ( 8 > $db_version ) {
			$new_db_version = 8;

			// set default charset to utf8
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_accounts` CHARACTER SET utf8 " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_categories` CHARACTER SET utf8 " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_feeds` CHARACTER SET utf8 " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_jobs` CHARACTER SET utf8 " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_listings` CHARACTER SET utf8 " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_log` CHARACTER SET utf8 " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_markets` CHARACTER SET utf8 " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_orders` CHARACTER SET utf8 " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_payment` CHARACTER SET utf8 " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_profiles` CHARACTER SET utf8 " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_reports` CHARACTER SET utf8 " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_shipping` CHARACTER SET utf8 " );

			// convert individual columns
			// http://codex.wordpress.org/Converting_Database_Character_Sets#Converting_columns_to_blob.2C_then_back_to_original_format_with_new_charset

			// amazon_log
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_log` CHANGE callname callname VARBINARY(64); " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_log` CHANGE callname callname VARCHAR(64) CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_log` CHANGE success success VARBINARY(16); " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_log` CHANGE success success VARCHAR(16) CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_log` CHANGE http_code http_code VARBINARY(16); " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_log` CHANGE http_code http_code VARCHAR(16) CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_log` CHANGE parameters parameters BLOB; " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_log` CHANGE parameters parameters TEXT CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_log` CHANGE curl curl BLOB; " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_log` CHANGE curl curl TEXT CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_log` CHANGE result result BLOB; " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_log` CHANGE result result TEXT CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_log` CHANGE response response BLOB; " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_log` CHANGE response response TEXT CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_log` CHANGE request request BLOB; " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_log` CHANGE request request TEXT CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_log` CHANGE request_url request_url BLOB; " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_log` CHANGE request_url request_url TEXT CHARACTER SET utf8; " );


			// amazon_feeds
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_feeds` CHANGE FeedSubmissionId FeedSubmissionId VARBINARY(64); " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_feeds` CHANGE FeedSubmissionId FeedSubmissionId VARCHAR(64) CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_feeds` CHANGE FeedType FeedType VARBINARY(64); " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_feeds` CHANGE FeedType FeedType VARCHAR(64) CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_feeds` CHANGE FeedProcessingStatus FeedProcessingStatus VARBINARY(64); " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_feeds` CHANGE FeedProcessingStatus FeedProcessingStatus VARCHAR(64) CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_feeds` CHANGE MarketplaceIdList MarketplaceIdList VARBINARY(255); " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_feeds` CHANGE MarketplaceIdList MarketplaceIdList VARCHAR(255) CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_feeds` CHANGE status status VARBINARY(64); " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_feeds` CHANGE status status VARCHAR(64) CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_feeds` CHANGE success success VARBINARY(16); " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_feeds` CHANGE success success VARCHAR(16) CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_feeds` CHANGE results results BLOB; " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_feeds` CHANGE results results TEXT CHARACTER SET utf8; " );


			// amazon_reports
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_reports` CHANGE ReportRequestId ReportRequestId VARBINARY(64); " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_reports` CHANGE ReportRequestId ReportRequestId VARCHAR(64) CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_reports` CHANGE ReportType ReportType VARBINARY(64); " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_reports` CHANGE ReportType ReportType VARCHAR(64) CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_reports` CHANGE ReportProcessingStatus ReportProcessingStatus VARBINARY(64); " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_reports` CHANGE ReportProcessingStatus ReportProcessingStatus VARCHAR(64) CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_reports` CHANGE GeneratedReportId GeneratedReportId VARBINARY(64); " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_reports` CHANGE GeneratedReportId GeneratedReportId VARCHAR(64) CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_reports` CHANGE success success VARBINARY(16); " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_reports` CHANGE success success VARCHAR(16) CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_reports` CHANGE results results BLOB; " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_reports` CHANGE results results TEXT CHARACTER SET utf8; " );


			// amazon_shipping
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_shipping` CHANGE service_name service_name VARBINARY(255); " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_shipping` CHANGE service_name service_name VARCHAR(255) CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_shipping` CHANGE service_description service_description VARBINARY(255); " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_shipping` CHANGE service_description service_description VARCHAR(255) CHARACTER SET utf8; " );

			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_shipping` CHANGE carrier carrier VARBINARY(255); " );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}amazon_shipping` CHANGE carrier carrier VARCHAR(255) CHARACTER SET utf8; " );

			echo $wpdb->last_error;

			// $db_version = $new_db_version;
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 9  (0.5.7)
		if ( 9 > $db_version ) {
			$new_db_version = 9;

			// create table: amazon_btg (browse tree guide)
			$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}amazon_btg` (
			  `id`    	   int(11)      NOT NULL AUTO_INCREMENT,
			  `node_id`    bigint(16)   DEFAULT NULL,
			  `parent_id`  bigint(16)   DEFAULT NULL,
			  `top_id`     bigint(16)   DEFAULT NULL,
			  `level`      int(11)      DEFAULT NULL,
			  `leaf`       tinyint(4)   DEFAULT NULL,
			  `keyword`    varchar(255) DEFAULT NULL,
			  `node_name`  varchar(255) DEFAULT NULL,
			  `node_path`  varchar(255) DEFAULT NULL,
			  `site_id`    int(11)      DEFAULT NULL,
			  KEY `node_id`   (`node_id`),
			  KEY `parent_id` (`parent_id`),
			  KEY `top_id`    (`top_id`),
			  KEY `keyword`   (`keyword`),
			  PRIMARY KEY  (`id`)
			) DEFAULT CHARSET=utf8 ;";
			$wpdb->query($sql);						

			// create table: amazon_feed_templates (feed templates overview)
			$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}amazon_feed_templates` (
			  `id`         int(11)      NOT NULL AUTO_INCREMENT,
			  `name`       varchar(255) DEFAULT NULL,
			  `title`      varchar(255) DEFAULT NULL,
			  `version`    varchar(32)  DEFAULT NULL,
			  `site_id`    int(11)      DEFAULT NULL,
			  PRIMARY KEY  (`id`)
			) DEFAULT CHARSET=utf8 ;";
			$wpdb->query($sql);						

			// create table: amazon_feed_tpl_data (feed template data defintions)
			$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}amazon_feed_tpl_data` (
			  `id`         int(11)      NOT NULL AUTO_INCREMENT,
			  `field`      varchar(255) DEFAULT NULL,
			  `label`      varchar(255) DEFAULT NULL,
			  `definition` varchar(255) DEFAULT NULL,
			  `accepted`   varchar(255) DEFAULT NULL,
			  `example`    varchar(255) DEFAULT NULL,
			  `group_id`   varchar(32)  DEFAULT NULL,
			  `required`   varchar(32)  DEFAULT NULL,
			  `group`      varchar(255) DEFAULT NULL,
			  `tpl_id`     int(11)      DEFAULT NULL,
			  `site_id`    int(11)      DEFAULT NULL,
			  KEY `id`        (`id`),
			  KEY `field`     (`field`),
			  KEY `required`  (`required`),
			  KEY `group_id`  (`group_id`),
			  KEY `tpl_id`    (`tpl_id`),
			  PRIMARY KEY  (`id`)
			) DEFAULT CHARSET=utf8 ;";
			$wpdb->query($sql);						

			// create table: amazon_feed_tpl_values (feed template valid values)
			$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}amazon_feed_tpl_values` (
			  `id`         int(11)      NOT NULL AUTO_INCREMENT,
			  `field`      varchar(255) DEFAULT NULL,
			  `label`      varchar(255) DEFAULT NULL,
			  `type`       varchar(255) DEFAULT NULL,
			  `values`     text         DEFAULT NULL,
			  `tpl_id`     int(11)      DEFAULT NULL,
			  `site_id`    int(11)      DEFAULT NULL,
			  KEY `id`        (`id`),
			  KEY `field`     (`field`),
			  KEY `tpl_id`    (`tpl_id`),
			  PRIMARY KEY  (`id`)
			) DEFAULT CHARSET=utf8 ;";
			$wpdb->query($sql);						

			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}


		// upgrade to version 10  (0.5.7)
		if ( 10 > $db_version ) {
			$new_db_version = 10;

			// re-create table: amazon_profiles
			$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}amazon_profiles`;";
			$wpdb->query($sql);
			$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}amazon_profiles` (
			  `profile_id`          int(11)      NOT NULL AUTO_INCREMENT,
			  `profile_name`        varchar(255) DEFAULT NULL,
			  `profile_description` varchar(255) DEFAULT NULL,
			  `feed_type`           varchar(255) DEFAULT NULL,
			  `details`             text         DEFAULT NULL,
			  `fields`              text         DEFAULT NULL,
			  `tpl_id`              int(11)      DEFAULT NULL,
			  `account_id`          int(11)      DEFAULT NULL,
			  PRIMARY KEY  (`profile_id`)	
			) DEFAULT CHARSET=utf8 ;";
			$wpdb->query($sql);
						

			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 11  (0.6)
		if ( 11 > $db_version ) {
			$new_db_version = 11;

			// add column to amazon_accounts table
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_accounts`
			        ADD COLUMN `active` int(11) DEFAULT 1 AFTER `allowed_markets`
			";
			$wpdb->query($sql);						
						
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}


		// upgrade to version 12  (0.6.9)
		if ( 12 > $db_version ) {
			$new_db_version = 12;

			// add column to amazon_listings table
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings`
			        ADD COLUMN `source` varchar(32) DEFAULT NULL AFTER `status`
			";
			$wpdb->query($sql);						
						
			// add column to amazon_feeds table
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_feeds`
			        ADD COLUMN `template_name` varchar(64) DEFAULT NULL AFTER `FeedType`
			";
			$wpdb->query($sql);						
						
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 13  (0.7.3.1)
		if ( 13 > $db_version ) {
			$new_db_version = 13;
		
			// enable amazon.it
			$sql = "UPDATE `{$wpdb->prefix}amazon_markets` 
				SET `enabled` = 1
				WHERE `url` = 'amazon.it'
			";
			$wpdb->query($sql);
						
			// $db_version = $new_db_version;
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';
		}
		
		// upgrade to version 14  (0.8.1)
		if ( 14 > $db_version ) {
			$new_db_version = 14;

			// add column to amazon_listings table
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings`
			        ADD COLUMN `product_type` varchar(32) DEFAULT NULL AFTER `post_id`,
			        ADD COLUMN `vtheme` varchar(128) DEFAULT NULL AFTER `post_id`,
			        ADD COLUMN `parent_id` bigint(16) DEFAULT NULL AFTER `post_id`
			";
			$wpdb->query($sql);						
						
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 15  (0.8.7)
		if ( 15 > $db_version ) {
			$new_db_version = 15;
		
			// enable amazon.ca
			$sql = "UPDATE `{$wpdb->prefix}amazon_markets` 
				SET `enabled` = 1
				WHERE `url` = 'amazon.ca'
			";
			$wpdb->query($sql);
						
			// $db_version = $new_db_version;
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';
		}
		
		// upgrade to version 16  (0.8.8)
		if ( 16 > $db_version ) {
			$new_db_version = 16;
		
			// disallow NULL value for profile_id column
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings` 
					CHANGE profile_id profile_id INT(11) NOT NULL;
			";
			$wpdb->query($sql);
						
			// $db_version = $new_db_version;
			// update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';
		}
		
		// upgrade to version 17  (0.8.8.5)
		if ( 17 > $db_version ) {
			$new_db_version = 17;
		
			// allow longer field definitions
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_feed_tpl_data` 
					CHANGE definition definition VARCHAR(511) DEFAULT NULL;
			";
			$wpdb->query($sql);
						
			// $db_version = $new_db_version;
			// update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';
		}

		// upgrade to version 18  (0.8.9.5)
		if ( 18 > $db_version ) {
			$new_db_version = 18;
		
			// enable amazon.es
			$sql = "UPDATE `{$wpdb->prefix}amazon_markets` 
				SET `enabled` = 1
				WHERE `url` = 'amazon.es'
			";
			$wpdb->query($sql);
						
			// $db_version = $new_db_version;
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';
		}

		// upgrade to version 19  (0.8.1)
		if ( 19 > $db_version ) {
			$new_db_version = 19;

			// add column to amazon_listings table
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings`
			        ADD COLUMN `lowest_price` float DEFAULT NULL AFTER `history`,
			        ADD COLUMN `pricing_date` datetime DEFAULT NULL AFTER `history`,
			        ADD COLUMN `pricing_info` text AFTER `history`
			";
			$wpdb->query($sql);						
						
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 20  (0.9.0.2)
		if ( 20 > $db_version ) {
			$new_db_version = 20;

			// add column to amazon_listings table
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings`
			        ADD COLUMN `fba_fcid` varchar(16) DEFAULT NULL AFTER `source`,
			        ADD COLUMN `fba_quantity` int(11) DEFAULT NULL AFTER `source`
			";
			$wpdb->query($sql);						
						
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 21  (0.9.0.6)
		if ( 21 > $db_version ) {
			$new_db_version = 21;

			// add column to amazon_listings table
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings`
			        ADD COLUMN `quality_status` varchar(16) DEFAULT NULL AFTER `history`,
			        ADD COLUMN `quality_info` text AFTER `history`
			";
			$wpdb->query($sql);						
						
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 22  (0.9.1.1)
		if ( 22 > $db_version ) {
			$new_db_version = 22;
		
			// enable amazon.ca
			$sql = "UPDATE `{$wpdb->prefix}amazon_markets` 
				SET `enabled` = 1
				WHERE `url` = 'amazon.fr'
			";
			$wpdb->query($sql);
						
			// $db_version = $new_db_version;
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';
		}
		
		// upgrade to version 23  (0.9.2.0)
		if ( 23 > $db_version ) {
			$new_db_version = 23;

			// add column to amazon_listings table
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings`
			        ADD COLUMN `pnq_status` int(11) AFTER `lowest_price`,
			        ADD COLUMN `max_price` float DEFAULT NULL AFTER `lowest_price`,
			        ADD COLUMN `min_price` float DEFAULT NULL AFTER `lowest_price`
			";
			$wpdb->query($sql);						
						
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 24  (0.9.2.1)
		if ( 24 > $db_version ) {
			$new_db_version = 24;

			// enable ISO feed encoding for all sites
			update_option('wpla_feed_encoding', 'ISO-8859-1');
						
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 25  (0.9.2.3)
		if ( 25 > $db_version ) {
			$new_db_version = 25;

			// add column to amazon_orders table
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_orders`
			        ADD COLUMN `currency` varchar(16) AFTER `total`
			";
			$wpdb->query($sql);						
						
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 26  (0.9.5.3)
		if ( 26 > $db_version ) {
			$new_db_version = 26;

			// add column to amazon_listings table
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings`
			        ADD COLUMN `loffer_data`  TEXT               AFTER `lowest_price`,
			        ADD COLUMN `buybox_data`  TEXT               AFTER `lowest_price`,
			        ADD COLUMN `compet_price` float DEFAULT NULL AFTER `lowest_price`,
			        ADD COLUMN `loffer_price` float DEFAULT NULL AFTER `lowest_price`,
			        ADD COLUMN `buybox_price` float DEFAULT NULL AFTER `lowest_price`,
			        ADD COLUMN `has_buybox`   int(11)            AFTER `lowest_price`
			";
			$wpdb->query($sql);						

			// add indices to amazon_listings table
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings` ADD INDEX `asin` (`asin`) ";
			$wpdb->query($sql);	echo $wpdb->last_error;
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings` ADD INDEX `sku` (`sku`) ";
			$wpdb->query($sql);	echo $wpdb->last_error;
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings` ADD INDEX `post_id` (`post_id`) ";
			$wpdb->query($sql);	echo $wpdb->last_error;
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings` ADD INDEX `parent_id` (`parent_id`) ";
			$wpdb->query($sql);	echo $wpdb->last_error;
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings` ADD INDEX `status` (`status`) ";
			$wpdb->query($sql);	echo $wpdb->last_error;
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings` ADD INDEX `quality_status` (`quality_status`) ";
			$wpdb->query($sql);	echo $wpdb->last_error;
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings` ADD INDEX `pnq_status` (`pnq_status`) ";
			$wpdb->query($sql);	echo $wpdb->last_error;
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings` ADD INDEX `has_buybox` (`has_buybox`) ";
			$wpdb->query($sql);	echo $wpdb->last_error;
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings` ADD INDEX `product_type` (`product_type`) ";
			$wpdb->query($sql);	echo $wpdb->last_error;
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings` ADD INDEX `fba_fcid` (`fba_fcid`) ";
			$wpdb->query($sql);	echo $wpdb->last_error;
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings` ADD INDEX `profile_id` (`profile_id`) ";
			$wpdb->query($sql);	echo $wpdb->last_error;
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings` ADD INDEX `account_id` (`account_id`) ";
			$wpdb->query($sql);	echo $wpdb->last_error;

						
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 27  (0.9.5.8)
		if ( 27 > $db_version ) {
			$new_db_version = 27;
		
			// enable amazon.it
			$sql = "UPDATE `{$wpdb->prefix}amazon_markets` 
				SET `enabled` = 1
				WHERE `url` = 'amazon.co.jp'
			";
			$wpdb->query($sql);
						
			// $db_version = $new_db_version;
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';
		}
		
		// upgrade to version 28  (0.9.2.11)
		if ( 28 > $db_version ) {
			$new_db_version = 28;

			// add column to amazon_listings table
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings`
			        ADD COLUMN `vattributes` TEXT AFTER `product_type`
			";
			$wpdb->query($sql);						
						
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 29  (0.9.6.1)
		if ( 29 > $db_version ) {
			$new_db_version = 29;

			// allow longer listing titles
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings` 
					CHANGE listing_title listing_title VARCHAR(511) DEFAULT NULL;
			";
			$wpdb->query($sql);
						
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 30  (0.9.6.3)
		if ( 30 > $db_version ) {
			$new_db_version = 30;

			// add column to amazon_accounts table
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_accounts`
			        ADD COLUMN `is_reg_brand` int(11) NOT NULL AFTER `active`
			";
			$wpdb->query($sql);						
						
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 31  (0.9.6.5)
		if ( 31 > $db_version ) {
			$new_db_version = 31;

			// add column to amazon_btg table
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_btg`
			        ADD COLUMN `tpl_id` int(11) NOT NULL AFTER `node_path`
			";
			$wpdb->query($sql);						
						
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 32  (0.9.6.10)
		if ( 32 > $db_version ) {
			$new_db_version = 32;

			// add row to amazon_markets
			$sql = "INSERT INTO `{$wpdb->prefix}amazon_markets` 
				(`id`, `developer_id`, `title`, `code`, `url`, `enabled`, `sort_order`, `group_title`)
				VALUES
					(33,'','India','IN','amazon.in',1,8,'Asia / Pacific');
			";
			$wpdb->query($sql);
						
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 33  (0.9.6.15)
		if ( 33 > $db_version ) {
			$new_db_version = 33;

			// add indices to amazon_log table
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_log` ADD INDEX `timestamp` (`timestamp`) ";
			$wpdb->query($sql);	echo $wpdb->last_error;
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_log` ADD INDEX `callname` (`callname`) ";
			$wpdb->query($sql);	echo $wpdb->last_error;
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_log` ADD INDEX `success` (`success`) ";
			$wpdb->query($sql);	echo $wpdb->last_error;

			// add indices to amazon_orders table
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_orders` ADD INDEX `order_id` (`order_id`) ";
			$wpdb->query($sql);	echo $wpdb->last_error;
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_orders` ADD INDEX `post_id` (`post_id`) ";
			$wpdb->query($sql);	echo $wpdb->last_error;
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_orders` ADD INDEX `status` (`status`) ";
			$wpdb->query($sql);	echo $wpdb->last_error;
						
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 34  (0.9.6.19)
		if ( 34 > $db_version ) {
			$new_db_version = 34;

			// add columns to amazon_listings table
			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_listings`
			        ADD COLUMN `fba_fee_ltsf_12`        float DEFAULT NULL AFTER `fba_fcid`,
			        ADD COLUMN `fba_qty_ltsf_12`        int(11)            AFTER `fba_fcid`,
			        ADD COLUMN `fba_inv_age_365_plus`   int(11)            AFTER `fba_fcid`,
			        ADD COLUMN `fba_inv_age_365`        int(11)            AFTER `fba_fcid`,
			        ADD COLUMN `fba_inv_age_270`        int(11)            AFTER `fba_fcid`,
			        ADD COLUMN `fba_inv_age_180`        int(11)            AFTER `fba_fcid`,
			        ADD COLUMN `fba_inv_age_90`         int(11)            AFTER `fba_fcid`
			";
			$wpdb->query($sql);						
						
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 35  (0.9.6.19)
		if ( 35 > $db_version ) {
			$new_db_version = 35;

			// set column type to mediumtext in table: amazon_feeds
 			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_feeds`
 			        CHANGE results results MEDIUMTEXT ";
 			$wpdb->query($sql);	echo $wpdb->last_error;
						
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 36  (0.9.6.19)
		if ( 36 > $db_version ) {
			$new_db_version = 36;

			// increase field size for varchar columns in table: amazon_feed_tpl_data
 			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_feed_tpl_data`
					CHANGE definition definition VARCHAR(1023) DEFAULT NULL; ";
 			$wpdb->query($sql);	echo $wpdb->last_error;
 			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_feed_tpl_data`
					CHANGE accepted accepted VARCHAR(511) DEFAULT NULL; ";
 			$wpdb->query($sql);	echo $wpdb->last_error;
 			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_feed_tpl_data`
					CHANGE example example VARCHAR(511) DEFAULT NULL; ";
 			$wpdb->query($sql);	echo $wpdb->last_error;
 			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_feed_tpl_data`
					CHANGE `group` `group` VARCHAR(511) DEFAULT NULL; ";
 			$wpdb->query($sql);	echo $wpdb->last_error;
 			$sql = "ALTER TABLE `{$wpdb->prefix}amazon_feed_tpl_data`
					CHANGE group_id group_id VARCHAR(64) DEFAULT NULL; ";
 			$wpdb->query($sql);	echo $wpdb->last_error;
						
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 37  (0.9.6.19)
		if ( 37 > $db_version ) {
			$new_db_version = 37;

			// create table: amazon_stock_log
			$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}amazon_stock_log` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `timestamp` datetime DEFAULT NULL,
			  `product_id` int(11) DEFAULT NULL,
			  `sku` varchar(32) DEFAULT NULL,
			  `old_stock` int(11) DEFAULT NULL,
			  `new_stock` int(11) DEFAULT NULL,
			  `caller` varchar(64) DEFAULT NULL,
			  `method` varchar(128) DEFAULT NULL,
			  `user_id` int(11) DEFAULT NULL,
			  `backtrace` text,
			  PRIMARY KEY (`id`)	
			) DEFAULT CHARSET=utf8 ;";
			$wpdb->query($sql);
						
			update_option('wpla_db_version', $new_db_version);
			$msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';

		}

		// upgrade to version 38 (0.9.6.40)
        if ( 38 > $db_version ) {
		    $new_db_version = 38;

            $sql = "ALTER TABLE `{$wpdb->prefix}amazon_reports`
					CHANGE `data` `data` longblob;";
            $wpdb->query($sql);	echo $wpdb->last_error;

            update_option('wpla_db_version', $new_db_version);
            $msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';
        }

        // upgrade to version 39
        if ( 39 > $db_version ) {
            $new_db_version = 39;

            $sql = "ALTER TABLE `{$wpdb->prefix}amazon_markets`
					ADD COLUMN `marketplace_id` VARCHAR(32) DEFAULT NULL AFTER `url`";
            $wpdb->query($sql);	echo $wpdb->last_error;

            $marketplaces = array(
                'CA' => 'A2EUQ1WTGCTBG2',
                'DE' => 'A1PA6795UKMFR9',
                'FR' => 'A13V1IB3VIYZZH',
                'JP' => 'A1VC38T7YXB528',
                'UK' => 'A1F83G8C2ARO7P',
                'US' => 'ATVPDKIKX0DER',
                'ES' => 'A1RKKUPIHCS9HS',
                'IT' => 'APJ6JRA9NG5V4',
                'CN' => 'AAHKV2X7AFYLW',
                'IN' => 'A21TJRUUN4KGV'
            );

            foreach ( $marketplaces as $code => $marketplace ) {
                $sql = "UPDATE `{$wpdb->prefix}amazon_markets` SET marketplace_id = '{$marketplace}' WHERE `code` = '{$code}';";
                $wpdb->query($sql);	echo $wpdb->last_error;
            }

            update_option('wpla_db_version', $new_db_version);
            $msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';
        }

        if ( 40 > $db_version ) {
		    $new_db_version = 40;

		    // Update tax settings
            $tax_mode       = 'none';
            $autodetect     = get_option( 'wpla_orders_autodetect_tax_rates', 0 );
            $tax_rate_id    = get_option( 'wpla_orders_tax_rate_id' );
            $tax_rate       = get_option( 'wpla_orders_fixed_vat_rate' );
            $import_tax     = get_option( 'wpla_record_item_tax', 0 );

            if ( $autodetect ) {
                $tax_mode = 'autodetect';
            } elseif ( $tax_rate_id && $tax_rate ) {
                $tax_mode = 'fixed';
            } elseif ( $import_tax ) {
                $tax_mode = 'import';
            }

            update_option( 'wpla_orders_tax_mode', $tax_mode );

            update_option( 'wpla_db_version', $new_db_version );
            $msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';
        }

        // upgrade to version 41 - Add BR marketplace
        if ( 41 > $db_version ) {
            $new_db_version = 41;

            $wpdb->insert( $wpdb->prefix .'amazon_markets', array(
                'title'     => 'Brazil',
                'code'      => 'BR',
                'url'       => 'amazon.com.br',
                'marketplace_id'    => 'A2Q3Y263D00KWC',
                'enabled'           => 1,
                'sort_order'        => 10,
                'group_title'       => 'America'
            ));

            update_option('wpla_db_version', $new_db_version);
            $msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';
        }

        // upgrade to version 42 - Add AU marketplace
        if ( 42 > $db_version ) {
            $new_db_version = 42;

            $wpdb->insert( $wpdb->prefix .'amazon_markets', array(
                'title'     => 'Australia',
                'code'      => 'AU',
                'url'       => 'amazon.com.au',
                'marketplace_id'    => 'A39IBJ37TRP1C6',
                'enabled'           => 1,
                'sort_order'        => 8,
                'group_title'       => 'Asia / Pacific'
            ));

            update_option('wpla_db_version', $new_db_version);
            $msg  = __('WP-Lister database was upgraded to version', 'wpla') .' '. $new_db_version . '.';
        }

		// show update message
		if ( $msg && ! $hide_message ) self::showMessage( $msg );

		#debug: update_option('wpla_db_version', 0);
		
	} // upgradeDB()


	static function showMessage($message, $errormsg = false, $echo = true) {		

		// don't output message when doing cron
		if ( defined('DOING_CRON') && DOING_CRON ) return;

		if ( defined('WPLISTER_RESELLER_VERSION') ) $message = apply_filters( 'wpla_tooltip_text', $message );

		$class = ($errormsg) ? 'error' : 'updated fade';
		$class = ($errormsg == 2) ? 'updated update-nag' : $class; 	// warning
		$message = '<div id="message" class="'.$class.'" style="display:block !important"><p>'.$message.'</p></div>';
		if ($echo) {
			echo $message;
		}
	}

	/**
	 * If a table only contains utf8 or utf8mb4 or latin1 columns, convert it to utf8mb4.
	 * (modified version of maybe_convert_table_to_utf8mb4() in wp core)
	 *
	 * @since 0.9.6.5
	 *
	 * @param string $table The table to convert.
	 * @return bool true if the table was converted, false if it wasn't.
	 */
	static function convert_custom_table_to_utf8mb4( $table ) {
		global $wpdb;
		global $wp_version;

		// do nothing before wp42
		if ( version_compare( $wp_version, '4,2', '<') ) {
			wpla_show_message('WordPress 4.2 or better required - your version is '.$wp_version, 'error');
			return false;
		}

		// get column information
		$results = $wpdb->get_results( "SHOW FULL COLUMNS FROM `$table`" );
		if ( ! $results ) {
			wpla_show_message("no columns found for $table",'error');
			return false;
		}

		// check charset for each column
		foreach ( $results as $column ) {
			if ( $column->Collation ) {
				list( $charset ) = explode( '_', $column->Collation );
				$charset = strtolower( $charset );
				if ( 'utf8' !== $charset && 'utf8mb4' !== $charset && 'latin1' !== $charset ) {
					// Don't upgrade tables that have non-utf8 and non-latin1 columns.
					wpla_show_message("skipped column $column in table $table with charset: $charset",'error');
					return false;
				}
			}
		}

		// convert
		return $wpdb->query( "ALTER TABLE $table CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci" );
	}

} // class WPLA_UpgradeHelper
