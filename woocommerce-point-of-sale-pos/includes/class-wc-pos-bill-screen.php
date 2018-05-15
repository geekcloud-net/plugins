<?php
/**
 * Add extra profile fields for users in admin.
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/profile
 * @category    Class
 * @since     3.2
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('WC_Pos_Bill_Screen')) :

    /**
     * WC_Pos_Float_Cash
     */
    class WC_Pos_Bill_Screen
    {

        public $register_id;

        public function __construct($register_id)
        {
            if (get_option('wc_pos_bill_screen', 'no') == 'no') {
                exit('Please enable the Bill Screen feature from the Settings page.');
            }
            self::register_styles();
            self::register_scripts();
            $this->register_id = $register_id;
        }

        private function register_scripts()
        {
            wp_enqueue_script('bill-screen', WC_POS()->plugin_url() . '/assets/js/register/bill-screen.js', array('jquery'));
            wp_localize_script('bill-screen', 'bill-screen_ajax_object',
                array('ajax_url' => admin_url('admin-ajax.php')));
        }

        private function register_styles()
        {
            wp_enqueue_style('bill-screen', WC_POS()->plugin_url() . '/assets/css/register/bill-screen.css');
        }

        private function print_inline_scripts()
        {
            echo "<script>
                        var reg_id = {$this->register_id}
                        var ajaxurl = '" . admin_url('admin-ajax.php') . "'
                  </script>";
        }

        public function display()
        {
            $this->print_inline_scripts();
            include_once('views/html-admin-bill-screen.php');
            exit;
        }
    }

endif;