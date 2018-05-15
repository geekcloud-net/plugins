<?php
/**
 * WooCommerce Mail Builder and Customizer
 * Create awesome transactional emails with a drag and drop email builder
 * @author Flycart Technologies LLP
 * @license GNU GPL V3 or later
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if (!function_exists('woo_mb_get_settings')) {
    function woo_mb_get_settings($template_id = false, $filter_args = array())
    {
            return false;
    }
}

if (!function_exists('woo_mb_save_settings')) {
    function woo_mb_save_settings()
    {
        $returnVal = false;
        $isAdmin = is_admin();
        if($isAdmin){
            if(isset($_REQUEST["settings"])){
                $postValues = $_REQUEST["settings"];
                $woo_mb_settings = json_encode($postValues);
                $option = get_option('woo_mb_settings', '');
                if($option != ''){
                    update_option('woo_mb_settings', $woo_mb_settings);
                } else {
                    add_option('woo_mb_settings', $woo_mb_settings);
                }
                $returnVal = true;
            }
        }
        if($returnVal){
            $result['status'] = 'SUCCESS';
            $result['status_code'] = 200;
            $result['status_message'] = esc_html__('Saved successfully');
        } else {
            $result['status'] = 'FAILED';
            $result['status_code'] = 0;
            $result['status_message'] = esc_html__('Save Failed');
        }
        echo json_encode($result);
        die();
    }
}