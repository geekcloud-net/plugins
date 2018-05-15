<?php
/**
 * WooCommerce Email Customizer with Drag and Drop Email Builder
 * Create awesome transactional emails with a drag and drop email builder
 * @author Flycart Technologies LLP
 * @license GNU GPL V3 or later
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Includes
 */
class WooEmailCustomizerCommon
{
    /**
     * Get additional css
     * */
    public static function getAdditionalCSS(){
        $additionalStyles = '';
        $css = self::getCSSFromSettings();
        global $wpdb;
        $mailTemplates = $wpdb->get_col( "SELECT post_content FROM $wpdb->posts WHERE post_type = 'woo_mb_template'" );
        foreach ($mailTemplates as $mailTemplate){
            if($mailTemplate != '' && trim($mailTemplate) != ''){
                $templateContent = json_decode($mailTemplate);
                if(isset($templateContent->additionalstyles) && $templateContent->additionalstyles != ''){
                    $additionalStyles .= $templateContent->additionalstyles;
                }
            }
        }
        return $additionalStyles.$css;
    }

    /**
     * Get css from settings
     * */
    public static function getCSSFromSettings(){
        $woo_mb_settings = get_option('woo_mb_settings', '');
        if ($woo_mb_settings != ''){
            $woo_mb_settings = json_decode($woo_mb_settings);
        }
        $maxWidth = isset($woo_mb_settings->container_width)? $woo_mb_settings->container_width: '';
        if($maxWidth == ''){
            $maxWidth = 640;
        }
        $order_item_table_border_color = isset($woo_mb_settings->order_item_table_border_color)? $woo_mb_settings->order_item_table_border_color: '#dddddd';
        $custom_css = isset($woo_mb_settings->custom_css)? $woo_mb_settings->custom_css: '';
        if($custom_css != ''){
            $custom_css = strip_tags($custom_css);
            $custom_css = str_replace('\n', '', $custom_css);
        }
        $product_image_height = isset($woo_mb_settings->product_image_height)? $woo_mb_settings->product_image_height: 32;
        $product_image_width = isset($woo_mb_settings->product_image_width)? $woo_mb_settings->product_image_width: 32;
        $css = "table.email_builder_table_items{border-collapse: collapse !important;width: 100%; border: 1px solid ".$order_item_table_border_color." !important;}";
        $css .= "table.email_builder_table_items tbody tr,
                table.email_builder_table_items tbody tr td,
                table.email_builder_table_items thead tr,
                table.email_builder_table_items thead tr th,
                table.email_builder_table_items thead tr td,
                table.email_builder_table_items tfoot tr,
                table.email_builder_table_items tfoot tr th,
                table.email_builder_table_items tfoot tr td
                    {border: 1px solid ".$order_item_table_border_color." !important;}";
        $css .= ".builder .email-container{max-width: ".$maxWidth."px}";
        $css .= "table.em-main { width: 100% !important; max-width: ".$maxWidth."px}";
        $css .= "table.em-image-caption-column { width: 50% !important; max-width: ".$maxWidth."px}";

        $css .= "@media only screen and (max-width: 640px) {";
        $css .= "table.em-image-caption-column { width: 100% !important; max-width: ".$maxWidth."px}}";
        $css .= "tr.order_item td img{
                    height: ".$product_image_height."px !important;
                    width: ".$product_image_width."px !important;
                 }";
        return $css.$custom_css;
    }

    /**
     * get custom fields of flexible checkout fields
     * */
    public static function getCustomFieldsOfFlexibleCheckoutFields(){
        global $flexible_checkout_fields;
        $fields = array();
        if(self::hasFlexibleCheckoutFieldsPlugin()){
            $fields = array();
            if(method_exists($flexible_checkout_fields, 'get_settings')){
                $field_settings = $flexible_checkout_fields->get_settings();
                $custom_fields['billing'] = (isset($field_settings['billing']))? $field_settings['billing']: array();
                $custom_fields['shipping'] = (isset($field_settings['shipping']))? $field_settings['shipping']: array();
                $custom_fields['order'] = (isset($field_settings['order']))? $field_settings['order']: array();
                foreach ($custom_fields as $custom_field){
                    if(!empty($custom_field))
                        foreach ($custom_field as $field_data){
                            if(isset($field_data['custom_field']) && $field_data['custom_field'] == 1){
                                if(isset($field_data['name']) && $field_data['name']){
                                    $fields['_'.$field_data['name']] = $field_data['label'];
                                }
                            }
                        }
                }
            }
        }

        return $fields;
    }

    /**
     * Check flexible checkout fields plugin loaded/function exists
     * */
    public static function hasFlexibleCheckoutFieldsPlugin(){
        if(function_exists('wpdesk_get_order_meta')) return true;
        return false;
    }
}