<?php

/*
  Plugin Name: WooImporter
  Description: It`s a plugin that used to import products from eBay, Aliexpress, Amazon, Walmart into Wordpress website. The plugin is helpful to create a store with specific products and use affiliate URLs.
  Version: 2.8.5
  Author: Geometrix
  License: GPLv2+
  Author URI: http://gmetrixteam.com
  Text Domain: wpeae
  Domain Path: /languages
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!defined('WPEAE_PLUGIN_NAME')) {
    define('WPEAE_PLUGIN_NAME', plugin_basename(__FILE__));
}

if (!defined('WPEAE_ROOT_URL')) {
    define('WPEAE_ROOT_URL', plugin_dir_url(__FILE__));
}
if (!defined('WPEAE_ROOT_PATH')) {
    define('WPEAE_ROOT_PATH', plugin_dir_path(__FILE__));
}

if (!defined('WPEAE_FILE_FULLNAME')) {
    define('WPEAE_FILE_FULLNAME', __FILE__);
}
if (!defined('WPEAE_ROOT_MENU_ID')) {
    define('WPEAE_ROOT_MENU_ID', "wpeae-dashboard");
}

include_once(dirname(__FILE__) . '/include.php');
include_once(dirname(__FILE__) . '/schedule.php');
include_once(dirname(__FILE__) . '/install.php');

if (!class_exists('Requests')) {
    include_once (dirname(__FILE__) . '/libs/Requests/Requests.php');
    Requests::register_autoloader();
}

if (!class_exists('WooImporter')):

    class WooImporter {

        function __construct() {

            register_activation_hook(__FILE__, array($this, 'install'));
            register_deactivation_hook(__FILE__, array($this, 'uninstall'));

            add_action('admin_init', array($this, 'init'));

            add_action('admin_menu', array($this, 'add_menu'), 9);
            add_action('admin_enqueue_scripts', array($this, 'add_assets'));

            add_action("before_delete_post", array($this, 'delete_post_images'), 10, 1);
        }

        public function init() {

            if (is_plugin_active(WPEAE_PLUGIN_NAME)) {
                if (!is_plugin_active('woocommerce/woocommerce.php')) {

                    add_action('admin_notices', array($this, 'woocomerce_check_error'));

                    if (WPEAE_DEACTIVATE_IF_WOOCOMERCE_NOT_FOUND) {
                        deactivate_plugins(WPEAE_PLUGIN_NAME);
                        if (isset($_GET['activate'])) {
                            unset($_GET['activate']);
                        }
                    }
                }

                if (is_plugin_active(WPEAE_PLUGIN_NAME)) {
                    add_filter('plugin_action_links_' . WPEAE_PLUGIN_NAME, array($this, 'action_links'));

                    wpeae_check_db_update();

                    /* Auto update */
                    if (class_exists('WPEAE_Update')) {
                        $plugin_data = get_plugin_data(__FILE__);
                        $license_user = 'user';
                        $license_key = 'key';
                        new WPEAE_Update($plugin_data['Version'], WPEAE_ROOT_URL . 'update.php', WPEAE_PLUGIN_NAME, $license_user, $license_key);
                    }
                }
            }
        }

        function woocomerce_check_error() {
            $class = 'notice notice-error';
            $message = _x('WooImporter notice! Please install the Woocommerce plugin first.', 'error', 'wpeae');
            printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
        }

        public function add_assets($page) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            $plugin_data = get_plugin_data(__FILE__);

            wp_enqueue_style('wpeae-style', plugins_url('assets/css/style.css', __FILE__), array(), $plugin_data['Version']);
            wp_enqueue_style('wpeae-font-style', plugins_url('assets/css/font-awesome.min.css', __FILE__), array(), $plugin_data['Version']);
            wp_enqueue_style('wpeae-dtp-style', plugins_url('assets/js/datetimepicker/jquery.datetimepicker.css', __FILE__), array(), $plugin_data['Version']);
            wp_enqueue_style('wpeae-lighttabs-style', plugins_url('assets/js/lighttabs/lighttabs.css', __FILE__), array(), $plugin_data['Version']);

            wp_enqueue_script('wpeae-sprintf-script', plugins_url('assets/js/sprintf.js', __FILE__), array(), $plugin_data['Version']);

            wp_enqueue_script('wpeae-script', plugins_url('assets/js/script.js', __FILE__), array(), $plugin_data['Version']);
            wp_enqueue_script('wpeae-dtp-script', plugins_url('assets/js/datetimepicker/jquery.datetimepicker.js', __FILE__), array('jquery'), $plugin_data['Version']);
            wp_enqueue_script('wpeae-lighttabs-script', plugins_url('assets/js/lighttabs/lighttabs.js', __FILE__), array('jquery'), $plugin_data['Version']);

            $lang_data = array(
                'value_is_required' => _x('Value is required', 'Field validation', 'wpeae'),
                'min_price_or_max_price_is_required' => _x('Min price or Max price is required', 'Field validation', 'wpeae'),
            );

            wp_localize_script('wpeae-script', 'WPURLS', array('siteurl' => site_url(), 'lang' => $lang_data));
        }

        function add_menu() {
            new WPEAE_Goods();
            $api_list = wpeae_get_api_list();

            $root_menu_id_top = wpeae_get_root_menu_id();

            add_menu_page(WPEAE_NAME, WPEAE_NAME, 'manage_options', $root_menu_id_top, '', plugins_url('assets/img/small_logo.png', __FILE__));

            // include installed api
            foreach ($api_list as $api) {
                if ($api->is_instaled()) {
                    $title = $api->get_config_value("menu_title") ? $api->get_config_value("menu_title") : $api->get_type();
                    add_submenu_page($root_menu_id_top, $title, $title, 'manage_options', WPEAE_ROOT_MENU_ID . "-" . $api->get_type(), array(new WPEAE_DashboardPage($api->get_type()), 'render'));
                }
            }

            add_submenu_page($root_menu_id_top, WPEAE_NAME . ' Settings', 'Settings', 'manage_options', 'wpeae-settings', array(new WPEAE_SettingsPage(), 'render'));

            $addons_page = new WPEAE_AddonsPage();
            $new_addons_cnt = $addons_page->get_new_addons_count();
            add_submenu_page($root_menu_id_top, WPEAE_NAME . __(' Add-ons', 'wpeae'), __(' Add-ons', 'wpeae') . ($new_addons_cnt ? ' <span class="update-plugins count-' . $new_addons_cnt . '"><span class="plugin-count">' . $new_addons_cnt . '</span></span>' : ''), 'manage_options', 'wpeae-addons', array($addons_page, 'render'));

            do_action("wpeae_admin_menu");
        }

        function action_links($links) {
            return array_merge(array('<a href="' . admin_url('admin.php?page=wpeae-settings') . '">' . __('Settings', 'wpeae') . '</a>'), $links);
        }

        function install() {
            include_once dirname(__FILE__) . '/install.php';
            wpeae_install();
        }

        function uninstall() {
            include_once dirname(__FILE__) . '/install.php';
            wpeae_uninstall();
        }

        public function delete_post_images($post_id) {
            global $wpdb;
            $external_id = get_post_meta($post_id, 'external_id', true);
            if ($external_id || get_post_type($post_id) == 'product_variation') {
                $args = array('post_parent' => $post_id, 'post_type' => 'attachment', 'numberposts' => -1, 'post_status' => 'any');
                $childrens = get_children($args);
                if ($childrens) {
                    foreach ($childrens as $attachment) {
                        wp_delete_attachment($attachment->ID, true);
                        $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id = " . $attachment->ID);
                        wp_delete_post($attachment->ID, true);
                    }
                }
                $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
                if ($thumbnail_id) {
                    wp_delete_attachment($thumbnail_id);
                    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id = " . $thumbnail_id);
                    wp_delete_post($thumbnail_id, true);
                }
            }
        }

    }

    endif;

new WooImporter();
