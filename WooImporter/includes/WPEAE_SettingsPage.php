<?php

/**
 * Description of WPEAE_SettingsPage
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_SettingsPage')):

    class WPEAE_SettingsPage {

        function render() {
            do_action('wpeae_before_settings_page');

            if (isset($_POST['setting_form'])) {
                $current_api_module = (isset($_POST['module']) && $_POST['module']) ? $_POST['module'] : "";

                if ($current_api_module == "common") {
                    
                    update_option('wpeae_currency_conversion_factor', isset($_POST['wpeae_currency_conversion_factor']) ? wp_unslash($_POST['wpeae_currency_conversion_factor']) : 1);
                    update_option('wpeae_per_page', isset($_POST['wpeae_per_page']) ? wp_unslash($_POST['wpeae_per_page']) : 1);
                    update_option('wpeae_default_type', isset($_POST['wpeae_default_type']) ? wp_unslash($_POST['wpeae_default_type']) : 1);
                    update_option('wpeae_import_attributes', isset($_POST['wpeae_import_attributes']));
                    update_option('wpeae_not_available_product_status', isset($_POST['wpeae_not_available_product_status']) ? wp_unslash($_POST['wpeae_not_available_product_status']) : 'trash');
                    update_option('wpeae_remove_link_from_desc', isset($_POST['wpeae_remove_link_from_desc']));
                    update_option('wpeae_remove_img_from_desc', isset($_POST['wpeae_remove_img_from_desc']));
                    update_option('wpeae_import_extended_attribute', isset($_POST['wpeae_import_extended_attribute']));
                    update_option('wpeae_import_load_image_from_descr', isset($_POST['wpeae_import_load_image_from_descr']));
                    update_option('wpeae_update_per_schedule', isset($_POST['wpeae_update_per_schedule']) ? wp_unslash($_POST['wpeae_update_per_schedule']) : 20);
                    update_option('wpeae_import_product_images_limit', isset($_POST['wpeae_import_product_images_limit']) ? wp_unslash($_POST['wpeae_import_product_images_limit']) : '');
                    update_option('wpeae_regular_price_auto_update', isset($_POST['wpeae_regular_price_auto_update']));
                    update_option('wpeae_min_product_quantity', isset($_POST['wpeae_min_product_quantity']) ? wp_unslash($_POST['wpeae_min_product_quantity']) : 5);
                    update_option('wpeae_max_product_quantity', isset($_POST['wpeae_max_product_quantity']) ? wp_unslash($_POST['wpeae_max_product_quantity']) : 10);

                    update_option('wpeae_use_proxy', isset($_POST['wpeae_use_proxy']));
                    update_option('wpeae_proxies_list', isset($_POST['wpeae_proxies_list']) ? wp_unslash($_POST['wpeae_proxies_list']) : '');

                    if (isset($_POST['wpeae_default_status'])) {
                        update_option('wpeae_default_status', wp_unslash($_POST['wpeae_default_status']));
                    }

                    update_option('wpeae_price_auto_update', isset($_POST['wpeae_price_auto_update']));
                    if (isset($_POST['wpeae_price_auto_update_period'])) {
                        update_option('wpeae_price_auto_update_period', wp_unslash($_POST['wpeae_price_auto_update_period']));
                    }


                    $price_auto_update = get_option('wpeae_price_auto_update', false);
                    if ($price_auto_update) {
                        wp_clear_scheduled_hook('wpeae_update_price_event');
                        wp_schedule_event(time(), get_option('wpeae_price_auto_update_period', 'daily'), 'wpeae_update_price_event');
                    } else {
                        wp_clear_scheduled_hook('wpeae_update_price_event');
                    }
                    do_action('wpeae_save_common_settings', $_POST);
                } else {
                    $api_account = wpeae_get_account($current_api_module);
                    if ($api_account) {
                        $api_account->save($_POST);
                    }
                    $api = wpeae_get_api($current_api_module);
                    if ($api) {
                        $api->save_setting($_POST);
                        do_action('wpeae_save_module_settings', $api, $_POST);
                    }
                }
            }

            include(WPEAE_ROOT_PATH . '/view/settings.php' );
        }

    }

    

	

	

endif;
