<?php
/**
 * Admin Settings Class.
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/profile
 * @category    Class
 * @since     0.1
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('WC_POS_Admin_Settings')) :

    /**
     * WC_POS_Admin_Settings
     */
    include_once(WC()->plugin_path() . '/includes/admin/class-wc-admin-settings.php');

    class WC_POS_Admin_Settings extends WC_Admin_Settings
    {

        private static $settings = array();
        private static $errors = array();
        private static $messages = array();

        /**
         * Include the settings page classes
         */
        public static function get_settings_pages()
        {
            if (empty(self::$settings)) {
                $settings = array();

                include_once(WC()->plugin_path() . '/includes/admin/settings/class-wc-settings-page.php');

                $settings[] = include(WC_POS()->plugin_path() . '/includes/admin/settings/wc-pos-settings-general.php');
                $settings[] = include(WC_POS()->plugin_path() . '/includes/admin/settings/wc-pos-settings-register.php');
                $settings[] = include(WC_POS()->plugin_path() . '/includes/admin/settings/wc-pos-settings-checkout.php');
                $settings[] = include(WC_POS()->plugin_path() . '/includes/admin/settings/wc-pos-settings-tiles.php');
                $settings[] = include(WC_POS()->plugin_path() . '/includes/admin/settings/wc-pos-settings-tax.php');
                $settings[] = include(WC_POS()->plugin_path() . '/includes/admin/settings/wc-pos-settings-layout.php');
                $settings[] = include(WC_POS()->plugin_path() . '/includes/admin/settings/wc-pos-settings-nominal.php');
                $settings[] = include(WC_POS()->plugin_path() . '/includes/admin/settings/wc-pos-settings-system-status.php');

                self::$settings = apply_filters('wc_pos_get_settings_pages', $settings);
            }
            return self::$settings;
        }

        /**
         * Save the settings
         */
        public static function save()
        {
            global $current_section, $current_tab;

            if (empty($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'wc-pos-settings'))
                die(__('Action failed. Please refresh the page and retry.', 'woocommerce'));

            // Trigger actions
            do_action('wc_pos_settings_save_' . $current_tab);
            do_action('wc_pos_update_options_' . $current_tab);
            do_action('wc_pos_update_options');

            self::add_message(__('Your settings have been saved.', 'woocommerce'));
            WC_Admin_Settings::check_download_folder_protection();

            // Re-add endpoints and flush rules
            WC()->query->init_query_vars();
            WC()->query->add_endpoints();
            flush_rewrite_rules();

            do_action('wc_pos_settings_saved');
        }

        /**
         * Add a message
         * @param string $text
         */
        public static function add_message($text)
        {
            self::$messages[] = $text;
        }

        /**
         * Add an error
         * @param string $text
         */
        public static function add_error($text)
        {
            self::$errors[] = $text;
        }

        /**
         * Output messages + errors
         */
        public static function show_messages()
        {
            if (sizeof(self::$errors) > 0) {
                foreach (self::$errors as $error)
                    echo '<div id="message" class="error fade"><p><strong>' . esc_html($error) . '</strong></p></div>';
            } elseif (sizeof(self::$messages) > 0) {
                foreach (self::$messages as $message)
                    echo '<div id="message" class="updated fade"><p><strong>' . esc_html($message) . '</strong></p></div>';
            }
        }

        /**
         * Settings page.
         *
         * Handles the display of the main pos settings page in admin.
         *
         * @access public
         * @return void
         */
        public static function output()
        {
            global $current_section, $current_tab;

            // Include settings pages
            self::get_settings_pages();

            // Get current tab/section
            $current_tab = empty($_GET['tab']) ? 'general_pos' : sanitize_title($_GET['tab']);
            $current_section = empty($_REQUEST['section']) ? '' : sanitize_title($_REQUEST['section']);

            // Save settings if data has been posted
            if (!empty($_POST))
                self::save();

            // Add any posted messages
            if (!empty($_GET['wc_error']))
                self::add_error(stripslashes($_GET['wc_error']));

            if (!empty($_GET['wc_message']))
                self::add_message(stripslashes($_GET['wc_message']));

            if ($current_tab == 'tax_pos') {
                $enable_taxes = get_option('woocommerce_calc_taxes', 'no');
                if ($enable_taxes == 'yes')
                    self::add_message(__('Good news, the tax configuration of WooCommerce is set up, you can use automated tax calculation for the POS system.', 'wc_point_of_sale'));
                else
                    self::add_error(__('Unfortunately, the tax configuration of WooCommerce is not set up/enabled, tax calculation features are limited.', 'wc_point_of_sale'));
            }


            self::show_messages();

            // Get tabs for the settings page
            $tabs = apply_filters('wc_pos_settings_tabs_array', array());

            include_once(WC_POS()->plugin_path() . '/includes/admin/views/html-admin-settings.php');

        }
    }
endif;