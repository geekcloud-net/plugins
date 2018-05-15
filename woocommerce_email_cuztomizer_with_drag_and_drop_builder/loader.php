<?php
/**
 * WooCommerce Email Customizer with Drag and Drop Email Builder
 * Create awesome transactional emails with a drag and drop email builder
 * @author Flycart Technologies LLP
 * @license GNU GPL V3 or later
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $has_template_in_woo_email_customizer_page_builder;

$has_template_in_woo_email_customizer_page_builder = 0;
/**
 * check WooCommerce version
 */
if (!function_exists('woo_email_customizer_checkWooCommerceVersion3')) {
    function woo_email_customizer_checkWooCommerceVersion3($version = "3.0")
    {
        // If get_plugins() isn't available, require it
        if ( ! function_exists( 'get_plugins' ) )
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        // Create the plugins folder and file variables
        $plugin_folder = get_plugins( '/' . 'woocommerce' );
        $plugin_file = 'woocommerce.php';

        // If the plugin version number is set, return it
        if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
            $woocommerce_version = $plugin_folder[$plugin_file]['Version'];

        } else {
            // Otherwise return null
            $woocommerce_version = null;
        }
        define('WOO_ECPB_WOOCOMMERCE_VERSION', $woocommerce_version);
        $wooVersion3 = version_compare( $woocommerce_version, $version, ">=" );
        if( $wooVersion3 ) {            
            return true;
        }
    }
}
$wooVersion3 = woo_email_customizer_checkWooCommerceVersion3();

/**
 * Includes
 */

if($wooVersion3){
    include_once('includes/email-base-3.php');
    include_once('includes/helper-3.php');
} else {
    include_once('includes/email-base.php');
    include_once('includes/helper.php');
}

include_once('includes/common.php');

/**
 * Instantiate plugin.
 */
$woo_mb_email = new WooMbHelper();
$woo_mb_base = new WC_Email_Base();

if (!function_exists('woo_mb_head_scripts')) {
    function woo_mb_head_scripts()
    {

        global $woocommerce, $wp_scripts, $current_screen, $pagenow;
        // Css rules for Color Picker
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wooemail-color-picker-handle', WOO_ECPB_URI . '/assets/js/colorpicker.js', array( 'wp-color-picker' ), false, true );

        wp_enqueue_style( 'woocommerce_admin_styles' );

        wp_enqueue_script('woocommerce_admin');

        // Woo Mail Builder - Admin page only
        if (
            (isset($_REQUEST["page"]) && sanitize_text_field($_REQUEST["page"]) == 'woo_email_customizer_page_builder')
            ||
            (isset($_REQUEST["page"]) && sanitize_text_field($_REQUEST["page"]) == "wc-settings")
            ||
            (isset($_REQUEST["woo_mb_render_email"]))
            ||
            (isset($current_screen->id) && $current_screen->id == "shop_order")
            ||
            ('plugins.php' == $pagenow)
        ) {

            wp_register_script('common-builder', WOO_ECPB_URI . '/assets/js/common.min.js', array('jquery'));
            wp_enqueue_script('common-builder');

            wp_register_script('alertify', '//cdn.rawgit.com/alertifyjs/alertify.js/v1.0.10/dist/js/alertify.js');
            wp_enqueue_script('alertify');

            wp_register_style('app-min', WOO_ECPB_URI . '/assets/css/app.min.css', array(), WOO_ECPB_VERSION, 'screen');
            wp_enqueue_style('app-min');

            // Woo Mail Builder Custom Scripts
            wp_register_style('woo-email', WOO_ECPB_URI . '/assets/css/woo-email.css', array(), WOO_ECPB_VERSION, 'screen');
            wp_enqueue_style('woo-email');

            wp_register_style('woo-email-fontello', WOO_ECPB_URI . '/assets/css/fontello/css/woombtrl-icon-font-embedded.css', array(), WOO_ECPB_VERSION, 'screen');
            wp_enqueue_style('woo-email-fontello');

            wp_register_script('woo-email', WOO_ECPB_URI . '/assets/js/woo-mail.js', array('common-builder', 'alertify'), WOO_ECPB_VERSION);

            wp_localize_script('woo-email', 'woo_email_customizer_page_builder', array(
                'home_url' => get_home_url(),
                'admin_url' => admin_url(),
                'plugin_url' => WOO_ECPB_URI,
                'ajaxurl' => admin_url('admin-ajax.php')
            ));
        }

        wp_enqueue_style('open-sans');

        // Woo Mail Builder - Template page only
        if ((isset($_REQUEST["page"]) && sanitize_text_field($_REQUEST["page"]) == 'woo_email_customizer_page_builder') && isset($_REQUEST["woo_mb_render_email"])) {

            // Load jQuery
            wp_enqueue_script('jquery');

            // Load Dashicons
            wp_enqueue_style('dashicons');

            // Woo Mail Builder Custom Scripts
            wp_register_style('woo-email', WOO_ECPB_URI . '/assets/css/woo-email.css', array(), WOO_ECPB_VERSION, 'screen');
            wp_enqueue_style('woo-email');

        }

        if ('plugins.php' == $pagenow) {
            //
        }
    }
}

//Woo Mail Builder - Admin and Template pages only
if (isset($_REQUEST["page"]) && sanitize_text_field($_REQUEST["page"]) == 'woo_email_customizer_page_builder') {

    // Remove all notifications
    remove_all_actions('admin_notices');

    if (!isset($_REQUEST["woo_mb_render_email"])) {

        //Woo Mail Builder - Admin Page only
        add_action('in_admin_header', array($woo_mb_email, 'woo_mb_render_admin_page'));
    } else {

        //Woo Mail Builder - Template page only
        add_action('wp_print_scripts', 'woo_mb_head_scripts', 102);
        add_action('admin_init', array($woo_mb_email, 'woo_mb_render_template_page'));
    }
}

// ----------------------------------------- General Scripts -----------------------------------------------------------


// Enqueue Scripts/Styles - in head of admin page
add_action('admin_enqueue_scripts', 'woo_mb_head_scripts');

// Enqueue Scripts/Styles - in head of email template page
add_action('woo_mb_render_template_head_scripts', 'woo_mb_head_scripts', 102);

// ----------------------------------------- Woo Mail ------------------------------------------------------------------

function woo_mb_email_styles( $css ) {
    global $has_template_in_woo_email_customizer_page_builder;
    if($has_template_in_woo_email_customizer_page_builder){
        $additional_css = WooEmailCustomizerCommon::getAdditionalCSS();
        return $additional_css;
    }
    return $css;
}

add_filter( 'woocommerce_email_styles', 'woo_mb_email_styles' );

// Add menu item
add_action('admin_menu', array($woo_mb_email, 'admin_menu'));

// Check Templates
//add_filter('wc_get_template', array($woo_mb_email, 'woo_mb_get_template'), 10, 5);

add_filter('wc_get_template', array($woo_mb_email, 'woo_mb_get_new_template'), 10, 5);

// Add Button in WooCommerce->Settings->Email
add_action('woocommerce_settings_tabs_email', array($woo_mb_email, 'woocommerce_settings_button'));


//Re-Defining E-Mail Template Process.
add_action('wp_ajax_nopriv_ajaxWooProcess', array($woo_mb_base, 'email_template_parser'));
add_action('wp_ajax_ajaxWooProcess', array($woo_mb_base, 'email_template_parser'));

add_action('wp_ajax_nopriv_ajaxSaveTemplate', array($woo_mb_base, 'save_email_template'));
add_action('wp_ajax_ajaxSaveTemplate', array($woo_mb_base, 'save_email_template'));

add_action('wp_ajax_nopriv_ajaxResetTemplate', array($woo_mb_base, 'reset_email_templates'));
add_action('wp_ajax_ajaxResetTemplate', array($woo_mb_base, 'reset_email_templates'));

add_action('wp_ajax_nopriv_ajaxSaveEmailCustomizerSettings', 'woo_mb_save_settings');
add_action('wp_ajax_ajaxSaveEmailCustomizerSettings', 'woo_mb_save_settings');

add_action('wp_ajax_ajaxWooTestMail', array($woo_mb_base, 'sendTestMail'));

add_action('wp_ajax_ajaxWooSaveCSS', array($woo_mb_base, 'cssConfig'));

add_action('wp_ajax_ajaxLangSwitch', array($woo_mb_base, 'switchLanguage'));

// Ajax send email
add_action('wp_ajax_woo_mb_send_email', array($woo_mb_email, 'send_email'));
add_action('wp_ajax_nopriv_woo_mb_send_email', array($woo_mb_email, 'nopriv_send_email'));

// Ajax send email
add_action('wp_ajax_ajaxWooEmailCopyTemplateFromAnother', array($woo_mb_base, 'copyTemplateFromAnother'));
add_action('wp_ajax_nopriv_ajaxWooEmailCopyTemplateFromAnother', array($woo_mb_base, 'copyTemplateFromAnother'));
// ----------------------------------------- Woo Mail END --------------------------------------------------------------