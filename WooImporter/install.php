<?php

if (!defined('WPEAE_DB_VERSION')) {
    define('WPEAE_DB_VERSION', 14);
}

if (!defined('WPEAE_DEACTIVATE_IF_WOOCOMERCE_NOT_FOUND')) {
    define('WPEAE_DEACTIVATE_IF_WOOCOMERCE_NOT_FOUND', false);
}

if (!defined('WPEAE_NOTCLEAN_AFTER_DEACTIVATE')) {
    define('WPEAE_NOTCLEAN_AFTER_DEACTIVATE', true);
}

if (!function_exists('wpeae_check_db_update')) {

    function wpeae_check_db_update() {
        if (get_option('wpeae_db_version', 0) < WPEAE_DB_VERSION) {
            wpeae_uninstall();
            wpeae_install();
            update_option('wpeae_db_version', WPEAE_DB_VERSION);
        }
    }

}

if (!function_exists('wpeae_install')) {

    function wpeae_install() {
        add_option('wpeae_default_type', 'simple', '', 'no');
        add_option('wpeae_default_status', 'publish', '', 'no');
        add_option('wpeae_price_auto_update', false, '', 'no');

        add_option('wpeae_regular_price_auto_update', false, '', 'no');

        add_option('wpeae_price_auto_update_period', 'daily', '', 'no');
        add_option('wpeae_currency_conversion_factor', '1', '', 'no');
        add_option('wpeae_not_available_product_status', 'trash', '', 'no');
        add_option('wpeae_remove_link_from_desc', false, '', 'no');
        add_option('wpeae_remove_img_from_desc', false, '', 'no');
        add_option('wpeae_update_per_schedule', 20, '', 'no');
        add_option('wpeae_import_product_images_limit', '', '', 'no');
        add_option('wpeae_min_product_quantity', 5, '', 'no');
        add_option('wpeae_max_product_quantity', 10, '', 'no');
        add_option('wpeae_use_proxy', false, '', 'no');
        add_option('wpeae_proxies_list', '', '', 'no');

        $price_auto_update = get_option('wpeae_price_auto_update', false);
        if ($price_auto_update) {
            wp_schedule_event(time(), get_option('wpeae_price_auto_update_period', 'daily'), 'wpeae_update_price_event');
        } else {
            wp_clear_scheduled_hook('wpeae_update_price_event');
        }
        wp_schedule_event(time(), 'hourly', 'wpeae_schedule_post_event');

        wpeae_install_db();

        foreach (wpeae_get_api_list() as /* @var $api WPEAE_AbstractConfigurator */ $api) {
            $api->install();
        }

        do_action('wpeae_install_action');
    }

}
if (!function_exists('wpeae_install_db')) {

    function wpeae_install_db() {
        /** @var wpdb $wpdb */
        global $wpdb;

        include_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $charset_collate = '';
        if (!empty($wpdb->charset)) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }
        if (!empty($wpdb->collate)) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }

        $table_name = $wpdb->prefix . WPEAE_TABLE_GOODS;
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (" .
                "`type` VARCHAR(50) NOT NULL," .
                "`external_id` VARCHAR(50) NOT NULL," .
                "`variation_id` VARCHAR(50) NOT NULL," .
                "`image` VARCHAR(1024) NULL DEFAULT NULL," .
                "`detail_url` VARCHAR(1024) NULL DEFAULT NULL," .
                "`seller_url` VARCHAR(1024) NULL DEFAULT NULL," .
                "`photos` TEXT NULL," .
                "`title` VARCHAR(1024) NULL DEFAULT NULL," .
                "`subtitle` VARCHAR(1024) NULL DEFAULT NULL," .
                "`description` MEDIUMTEXT NULL," .
                "`keywords` VARCHAR(1024) NULL DEFAULT NULL," .
                "`price` VARCHAR(50) NULL DEFAULT NULL," .
                "`regular_price` VARCHAR(50) NULL DEFAULT NULL," .
                "`curr` VARCHAR(50) NULL DEFAULT NULL," .
                "`category_id` INT NULL DEFAULT NULL," .
                "`category_name` VARCHAR(1024) NULL DEFAULT NULL," .
                "`link_category_id` INT NULL DEFAULT NULL," .
                "`additional_meta` TEXT NULL," .
                "`user_image` VARCHAR(1024) NULL DEFAULT NULL," .
                "`user_photos` TEXT NULL," .
                "`user_title` VARCHAR(1024) NULL DEFAULT NULL," .
                "`user_subtitle` VARCHAR(1024) NULL DEFAULT NULL," .
                "`user_description` MEDIUMTEXT NULL," .
                "`user_keywords` VARCHAR(1024) NULL DEFAULT NULL," .
                "`user_price` VARCHAR(1024) NULL DEFAULT NULL," .
                "`user_regular_price` VARCHAR(1024) NULL DEFAULT NULL," .
                "`user_schedule_time` DATETIME NULL DEFAULT NULL," .
                "PRIMARY KEY (`type`, `external_id`, `variation_id`)" .
                ") {$charset_collate};";

        dbDelta($sql);

        $table_name = $wpdb->prefix . WPEAE_TABLE_ACCOUNT;
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (" .
                "`id` int(20) unsigned NOT NULL AUTO_INCREMENT," .
                "`name` VARCHAR(1024) NOT NULL," .
                "`data` text DEFAULT NULL," .
                "PRIMARY KEY (`id`)" .
                ") {$charset_collate};";
        dbDelta($sql);

        $table_name = $wpdb->prefix . WPEAE_TABLE_PRICE_FORMULA;
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (" .
                "`id` int(20) unsigned NOT NULL AUTO_INCREMENT," .
                "`pos` INT(20) NOT NULL DEFAULT 0," .
                "`formula` TEXT NOT NULL," .
                "PRIMARY KEY (`id`)" .
                ") {$charset_collate};";
        dbDelta($sql);

        $table_name = $wpdb->prefix . WPEAE_TABLE_LOG;
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (" .
                "`id` int(20) unsigned NOT NULL AUTO_INCREMENT," .
                "`text` VARCHAR(1024) NULL DEFAULT NULL," .
                "`type` VARCHAR(50) NOT NULL," .
                "`module` VARCHAR(50) NOT NULL," .
                "`time` DATETIME NULL DEFAULT NULL," .
                "PRIMARY KEY (`id`)" .
                ") {$charset_collate};";
        dbDelta($sql);
    }

}

if (!function_exists('wpeae_uninstall')) {

    function wpeae_uninstall() {
        if(defined('WPEAE_NOTCLEAN_AFTER_DEACTIVATE') && WPEAE_NOTCLEAN_AFTER_DEACTIVATE){
            return;
        }
        
        delete_option('wpeae_default_type');
        delete_option('wpeae_default_status');
        delete_option('wpeae_price_auto_update');

        delete_option('wpeae_regular_price_auto_update');

        delete_option('wpeae_price_auto_update_period');
        delete_option('wpeae_currency_conversion_factor');
        delete_option('wpeae_not_available_product_status');
        delete_option('wpeae_remove_link_from_desc');
        delete_option('wpeae_remove_img_from_desc');
        delete_option('wpeae_update_per_schedule');
        delete_option('wpeae_import_product_images_limit');
        delete_option('wpeae_min_product_quantity');
        delete_option('wpeae_max_product_quantity');
        delete_option('wpeae_use_proxy');
        delete_option('wpeae_proxies_list');

        wp_clear_scheduled_hook('wpeae_schedule_post_event');
        wp_clear_scheduled_hook('wpeae_update_price_event');

        wpeae_uninstall_db();
         
        foreach (wpeae_get_api_list() as /* @var $api WPEAE_AbstractConfigurator */ $api) {
            $api->uninstall();
        }

        do_action('wpeae_uninstall_action');
    }

}

if (!function_exists('wpeae_uninstall_db')) {

    function wpeae_uninstall_db() {
        /** @var wpdb $wpdb */
        global $wpdb;

        $sql = "DROP TABLE IF EXISTS " . $wpdb->prefix . WPEAE_TABLE_GOODS . ";";
        $wpdb->query($sql);

        $sql = "DROP TABLE IF EXISTS " . $wpdb->prefix . WPEAE_TABLE_ACCOUNT . ";";
        $wpdb->query($sql);

        $sql = "DROP TABLE IF EXISTS " . $wpdb->prefix . WPEAE_TABLE_PRICE_FORMULA . ";";
        $wpdb->query($sql);

        $sql = "DROP TABLE IF EXISTS " . $wpdb->prefix . WPEAE_TABLE_LOG . ";";
        $wpdb->query($sql);

        foreach (wpeae_get_api_list() as /* @var $api WPEAE_AbstractConfigurator */ $api) {
            $api->uninstall();
        }
    }

}