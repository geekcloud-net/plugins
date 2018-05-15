<?php

if (!function_exists('wpeae_aliship_install')) {
	function wpeae_aliship_install() {	
		add_option( 'wpeae_aliship_shipto', 'US', '', 'no' );
		add_option( 'wpeae_aliship_frontend', true, '', 'no' );
		  
		wpeae_aliship_install_db();
		
		do_action('wpeae_aliship_install_action');
	}
}

if (!function_exists('wpeae_aliship_uninstall')) {
	function wpeae_aliship_uninstall() {
		delete_option('wpeae_aliship_shipto' );
		delete_option('wpeae_aliship_frontend' );
			
		wpeae_aliship_uninstall_db();
		
		do_action('wpeae_aliship_uninstall_action');
	}
}

if (!function_exists('wpeae_aliship_install_db')) {

	function wpeae_aliship_install_db() {
		/** @var wpdb $wpdb */
		global $wpdb;

		$charset_collate = '';
		if (!empty($wpdb->charset)) {
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
		if (!empty($wpdb->collate)) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}

		$table_name = $wpdb->prefix . WPEAE_TABLE_SHIPPING;
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (" .
		"`type` VARCHAR(50) NOT NULL," .
		"`external_id` VARCHAR(50) NOT NULL," .
		"`to_country` VARCHAR(50) NOT NULL," .
		"`quantity` VARCHAR(50) NOT NULL," .
		"`data` TEXT NULL," .
		"`time` DATETIME NULL DEFAULT NULL," .                            
		"PRIMARY KEY (`type`, `external_id`, `to_country`, `quantity`)" .
		") {$charset_collate} ENGINE=InnoDB;";
		
		if( !function_exists('dbDelta') ){
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		}
				
		dbDelta($sql);
  
	}

}


if (!function_exists('wpeae_aliship_uninstall_db')) {

	function wpeae_aliship_uninstall_db() {
		/** @var wpdb $wpdb */
		global $wpdb;

		$sql = "DROP TABLE IF EXISTS " . $wpdb->prefix . WPEAE_TABLE_SHIPPING . ";";
		$wpdb->query($sql);

	}

}




