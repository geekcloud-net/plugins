<?php
/**
 * WooCommerce POS General Settings
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/settings
 * @category    Class
 * @since     0.1
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WC_POS_Admin_Settings_Register')) :

    /**
     * WC_POS_Admin_Settings_Layout
     */
    class WC_POS_Admin_Settings_Register extends WC_Settings_Page
    {

        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->id = 'register_pos';
            $this->label = __('Register', 'woocommerce');

            add_filter('wc_pos_settings_tabs_array', array($this, 'add_settings_page'), 20);
            add_action('wc_pos_settings_' . $this->id, array($this, 'output'));
            add_action('wc_pos_settings_save_' . $this->id, array($this, 'save'));
            add_action('wc_pos_sections_' . $this->id, array($this, 'output_sections'));

        }

        /**
         * Get sections.
         *
         * @return array
         */
        public function get_sections()
        {
            $sections = array(
                '' => __('Register', 'woocommerce'),
                'scanning' => __('Scanning', 'woocommerce'),
            );

            return apply_filters('woocommerce_sections_' . $this->id, $sections);
        }

        /**
         * Output sections.
         */
        public function output_sections()
        {
            global $current_section;

            $sections = $this->get_sections();

            if (empty($sections) || 1 === sizeof($sections)) {
                return;
            }

            echo '<ul class="subsubsub">';

            $array_keys = array_keys($sections);

            foreach ($sections as $id => $label) {
                echo '<li><a href="' . admin_url('admin.php?page=wc_pos_settings&tab=' . $this->id . '&section=' . sanitize_title($id)) . '" class="' . ($current_section == $id ? 'current' : '') . '">' . $label . '</a> ' . (end($array_keys) == $id ? '' : '|') . ' </li>';
            }

            echo '</ul><br class="clear" />';
        }

        /**
         * Get settings array
         *
         * @return array
         */
        public function get_settings()
        {
            global $woocommerce, $current_section;

            if ($current_section == 'scanning') {
                global $woocommerce, $wpdb;

                $barcode_fields = array(
                    '' => __('WooCommerce SKU', 'wc_point_of_sale'),
                );

                $pr_id = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' ORDER BY post_modified DESC LIMIT 1");
                if ($pr_id) {
                    $post_meta = get_post_meta($pr_id);
                    if ($post_meta) {
                        foreach ($post_meta as $key => $value) {
                            $barcode_fields[$key] = $key;
                        }
                    }
                }

                //

                return apply_filters('woocommerce_point_of_sale_general_settings_fields', array(

                    array('title' => __('Scanning Options', 'wc_point_of_sale'), 'desc' => __('The following options affect the use of scanning hardware such as barcode scanners and magnetic card readers.', 'wc_point_of_sale'), 'type' => 'title', 'id' => 'scanning_options'),

                    array(
                        'title' => __('Barcode Scanning', 'wc_point_of_sale'),
                        'id' => 'woocommerce_pos_register_ready_to_scan',
                        'std' => '',
                        'type' => 'checkbox',
                        'desc' => __('Enable barcode scanning', 'wc_point_of_sale'),
                        'desc_tip' => __('Listens to barcode scanners and adds item to basket. Carriage return in scanner recommended.', 'wc_point_of_sale'),
                        'default' => 'no',
                        'autoload' => false
                    ),

                    array(
                        'title' => __('Scanning Field', 'wc_point_of_sale'),
                        'desc_tip' => __('Control what field is used when using the scanner on the register. Default is SKU.', 'wc_point_of_sale'),
                        'id' => 'woocommerce_pos_register_scan_field',
                        'std' => '',
                        'class' => 'wc-enhanced-select',
                        'css' => 'min-width:300px;',
                        'type' => 'select',
                        'desc' => '',
                        'default' => '',
                        'autoload' => false,
                        'options' => $barcode_fields,
                    ),

                    array(
                        'name' => __('Credit/Debit Card Scanning', 'wc_point_of_sale'),
                        'id' => 'woocommerce_pos_register_cc_scanning',
                        'std' => '',
                        'type' => 'checkbox',
                        'desc' => __('Enable credit/debit card scanning', 'wc_point_of_sale'),
                        'desc_tip' => sprintf(__('Allows magnetic card readers to parse scanned output into checkout fields. Supported payment gateways can be found here %shere%s.', 'wc_point_of_sale'),
                            '<a href="http://actualityextensions.com/supported-payment-gateways/" target="_blank">', '</a>'),
                        'default' => 'no',
                        'autoload' => false
                    ),
                    array('type' => 'sectionend', 'id' => 'scanning_options'),

                )); // End general settings
            } else {
                return apply_filters('woocommerce_point_of_sale_general_settings_fields', array(

                    array('title' => __('Register Options', 'woocommerce'), 'type' => 'title', 'desc' => __('The following options affect the settings that are applied when loading all registers.', 'woocommerce'), 'id' => 'general_options'),

                    array(
                        'name' => __('Auto Update Stock', 'wc_point_of_sale'),
                        'id' => 'wc_pos_autoupdate_stock',
                        'type' => 'checkbox',
                        'desc' => __('Enable update stock automatically ', 'wc_point_of_sale'),
                        'desc_tip' => __('Updates the stock inventories for products automatically whilst running the register. Enabling this may hinder server performance. ', 'wc_point_of_sale'),
                        'default' => 'no',
                        'autoload' => true
                    ),
                    array(
                        'name' => __('Update Interval', 'wc_point_of_sale'),
                        'id' => 'wc_pos_autoupdate_interval',
                        'type' => 'number',
                        'desc_tip' => __('Enter the interval for auto-update in seconds.', 'wc_point_of_sale'),
                        'desc' => __('seconds', 'wc_point_of_sale'),
                        'default' => 240,
                        'autoload' => true,
                        'css' => 'width: 50px;'
                    ),
                    array(
                        'name' => __('Stock Quantity', 'wc_point_of_sale'),
                        'id' => 'wc_pos_show_stock',
                        'type' => 'checkbox',
                        'desc' => __('Enable stock quantity identifier', 'wc_point_of_sale'),
                        'desc_tip' => __('Shows the remaining stock when adding products to the basket.', 'wc_point_of_sale'),
                        'default' => 'yes',
                        'autoload' => true
                    ),
                    array(
                        'name' => __('Out of Stock', 'wc_point_of_sale'),
                        'id' => 'wc_pos_show_out_of_stock_products',
                        'type' => 'checkbox',
                        'desc' => __('Enable out of stock products', 'wc_point_of_sale'),
                        'desc_tip' => __('Shows out of stock products in the product grid.', 'wc_point_of_sale'),
                        'default' => 'yes',
                        'autoload' => true
                    ),
                    array(
                        'title' => __('Bill Screen', 'wc_point_of_sale'),
                        'desc' => __('Display bill screen', 'wc_point_of_sale'),
                        'desc_tip' => __('Allows you to display the order on a separate display i.e. pole display.', 'wc_point_of_sale'),
                        'id' => 'wc_pos_bill_screen',
                        'default' => 'no',
                        'type' => 'checkbox',
                        'checkboxgroup' => 'start',
                    ),
                    array(
                        'title' => __('Product Visiblity', 'wc_point_of_sale'),
                        'desc' => __('Enable product visibility control', 'wc_point_of_sale'),
                        'desc_tip' => __('Allows you to show and hide products from either the POS, web or both shops.', 'wc_point_of_sale'),
                        'id' => 'wc_pos_visibility',
                        'default' => 'no',
                        'type' => 'checkbox',
                        'checkboxgroup' => 'start',
                    ),
                    array(
                        'title' => __('Custom Fee', 'wc_point_of_sale'),
                        'desc' => __('Enable custom fee ', 'wc_point_of_sale'),
                        'desc_tip' => __('Allows you to add a fixed or percentage based value to the order.', 'wc_point_of_sale'),
                        'id' => 'wc_pos_custom_fee',
                        'default' => 'no',
                        'type' => 'checkbox',
                        'checkboxgroup' => 'start',
                    ),
                    array(
                        'title' => __('Use passPRNT', 'wc_point_of_sale'),
                        'desc' => __('Use passPRNT for printing receipts', 'wc_point_of_sale'),
                        'desc_tip' => __('Use passPRNT for printing receipts', 'wc_point_of_sale'),
                        'id' => 'wc_pos_passprnt',
                        'default' => 'no',
                        'type' => 'checkbox',
                        'checkboxgroup' => 'start',
                    ),
                    array(
                        'title' => __('passPRNT layout', 'wc_point_of_sale'),
                        'desc' => __('passPRNT layout', 'wc_point_of_sale'),
                        'desc_tip' => __('passPRNT layout', 'wc_point_of_sale'),
                        'id' => 'wc_pos_passprnt_size',
                        'default' => '2',
                        'type' => 'select',
                        'options' => array(
                            '2' => __('2 inch', 'wc_point_of_sale'),
                            '3' => __('3 inch', 'wc_point_of_sale')
                        ),
                    ),
                    array(
                        'id' => 'wc_pos_custom_fees',
                        'type' => 'custom_array',
                    ),
                    array('type' => 'sectionend', 'id' => 'checkout_pos_options'),
                )); // End general settings
            }
        }

        /**
         * Save settings
         */
        public function save()
        {
            $settings = $this->get_settings();
            WC_POS_Admin_Settings::save_fields($settings);
        }

        /**
         * Output the settings.
         */
        public function output()
        {
            $settings = $this->get_settings();
            WC_Admin_Settings::output_fields($settings);
            $custom_fees = unserialize(get_option('wc_pos_custom_fees'));
            if ($custom_fees) {
                $custom_fees = array_values($custom_fees);
            }
            include_once 'wc-pos-setting-register-fee-table.php';
        }
    }
endif;

return new WC_POS_Admin_Settings_Register();