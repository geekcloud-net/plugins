<?php
/**
 * Plugin Name: WooCommerce Email Customizer with Drag and Drop Email Builder
 * Description: Create awesome transactional emails with a drag and drop email builder
 * Author: Flycart Technologies LLP
 * Author URI: https://www.flycart.org
 * Version: 1.4.27
 * Text Domain: woo-email-customizer-page-builder
 * Domain Path: /i18n/languages/
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @author Flycart Technologies LLP
 * @license GNU GPL V3 or later
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * Define Constants
 */
define('WOO_ECPB_REQUIRED_WOOCOMMERCE_VERSION', '2.3');
define('WOO_ECPB_URI', untrailingslashit(plugin_dir_url(__FILE__)));
define('WOO_ECPB_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WOO_ECPB_LANG', get_locale());
define('WOO_ECPB_VERSION', '1.4.27');
define('WOO_ECPB_DIR', untrailingslashit(plugin_dir_path(__FILE__)));

include_once('includes/functions.php');
include_once('includes/settings.php');
include_once('includes/woo-template-woocommerce.php'); // Template
include_once('includes/activation-helper.php');

if (!function_exists('woo_mb_template_post_register')) {
    function woo_mb_template_post_register()
    {
        $labels = array(
            'name' => _x('Woo Mail Template', 'post type general name'),
            'singular_name' => _x('Woo Mail Template', 'post type singular name'),
            'add_new' => _x('Add New Woo Mail Template', 'Team item'),
            'add_new_item' => __('Add a new post of type Woo Mail Template'),
            'edit_item' => __('Edit Woo Mail Template'),
            'new_item' => __('New Woo Mail Template'),
            'view_item' => __('View Woo Mail Template'),
            'search_items' => __('Search Woo Mail Template'),
            'not_found' =>  __('No Woo Mail Template found'),
            'not_found_in_trash' => __('No Woo Mail Template currently trashed'),
            'parent_item_colon' => ''
        );

        $capabilities = array(
            // this is where the first code block from above goes
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => false,
            'query_var' => true,
            'rewrite' => true,
            'capability_type' => 'woo_mb_template',
            'capabilities' => $capabilities,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array( 'title', 'author', 'thumbnail' )
        );
        register_post_type( 'woo_mb_template' , $args );
    }

    add_action('init', 'woo_mb_template_post_register');
}

$woo_mb_templates = new WooMailBuilder();
// Register email templates.
add_action('init', array($woo_mb_templates, 'register_email_template'), 100);

/**
 * Triggering Plugin Setup Hooks
 */
register_activation_hook(__FILE__, 'WOOMBPBonActivatePlugin');
register_deactivation_hook(__FILE__, 'WOOMBPBonDeactivationPlugin');

add_action( 'wp_loaded', function () {
    include_once('loader.php');
    /**
     * Update Checker.
     */
    require 'includes/update-checker.php';
    $woo_email_builder_update = new UpdateChecker(__FILE__, 'woo-email-customizer-page-builder');
}, 0);

if ( !function_exists('wp_new_user_notification') ) :
    /**
     * Notify the blog admin of a new user, normally via email.
     *
     * @since 2.0
     *
     * @param int $user_id User ID
     * @param string $plaintext_pass Optional. The user's plaintext password
     */
    function wp_new_user_notification($user_id, $plaintext_pass = '') {
        $user = get_userdata( $user_id );
        if(class_exists('WC_Emails')){
            $wc = new WC_Emails();
            $wc->customer_new_account($user_id);
        }
    }
endif;