<?php
/**
 * Print Report Class
 *
 *
 * @author      Actuality Extensions
 * @category    Admin
 * @package     WC_POS/Admin
 * @version     1.0.0
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WC_POS_Admin_Print_Report class
 */
class WC_POS_Admin_Print_Report
{

    /** @var string Current Type */
    private $type = '';

    /** @var array types for the print */
    private $types = array();

    /**
     * Hook in tabs.
     */
    public function __construct()
    {
        if (current_user_can('view_register')) {
            add_action('admin_menu', array($this, 'admin_menus'));
            add_action('admin_init', array($this, 'setup_print'));
        }
    }

    /**
     * Add admin menus/screens.
     */
    public function admin_menus()
    {
        add_dashboard_page('', '', 'view_register', WC_POS_TOKEN . '-print', '');
    }

    /**
     * Show the print page
     */
    public function setup_print()
    {
        if (empty($_GET['page']) || WC_POS_TOKEN . '-print' !== $_GET['page']) {
            return;
        }
        $this->types = array(
            'report' => array(
                'title' => __('Print Report', 'wc_point_of_sale'),
                'header' => '',
                'view' => array($this, 'print_report'),
            ),
        );
        if (empty($_GET['print']) || !isset($this->types[$_GET['print']])) {
            return;
        }
        $this->type = isset($_GET['print']) ? sanitize_key($_GET['print']) : current(array_keys($this->types));

        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_style(WC_POS_TOKEN . '-setup', esc_url(WC_POS()->assets_url) . 'css/wc-pos-setup.css', array('dashicons', 'install'), WC_VERSION);
        wp_enqueue_style(WC_POS_TOKEN . '-print', esc_url(WC_POS()->assets_url) . 'css/wc-pos-print.css', array(), WC_POS()->_version);

        header('Content-Type: text/html; charset=utf-8');
        ob_start();
        $this->print_header();
        $this->print_content();
        $this->print_footer();
        exit;
    }

    /**
     * Print Header
     */
    public function print_header()
    {
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta name="viewport" content="width=device-width"/>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <title><?php printf(__('WooCommerce Point of Sale &rsaquo; %s', 'wc_point_of_sale'), $this->types[$this->type]['title']); ?></title>
            <?php wp_print_scripts(WC_POS_TOKEN . '-setup'); ?>
            <?php wp_print_scripts('wc-pos-print'); ?>
            <?php wp_print_scripts(WC_POS_TOKEN . '-print'); ?>
            <?php do_action('admin_print_styles'); ?>
        </head>
        <body class="wc-setup wp-core-ui">
        <?php
        if (isset($this->types[$this->type]['header']) && !empty($this->types[$this->type]['header'])) {
            call_user_func($this->types[$this->type]['header']);
        }
    }

    /**
     * Print Footer
     */
    public function print_footer()
    {
        ?>
        </body>
        </html>
        <?php
    }


    /**
     * Output the content for the current type
     */
    public function print_content()
    {
        echo '<div class="wc-setup-content">';
        call_user_func($this->types[$this->type]['view']);
        echo '</div>';
    }

    public function print_report()
    {
        $nonce = $_REQUEST['_wpnonce'];
        if (!wp_verify_nonce($nonce, 'print_pos_report') || !is_user_logged_in()) die('You are not allowed to view this page.');
        if (isset($_GET['report'])) {
            $rg_id = $_GET['report'];
            $data = WC_POS()->register()->get_data($rg_id);
            $data = $data[0];
            $outlets_name = WC_POS()->outlet()->get_data_names();
            $outlet = $outlets_name[$data['outlet']];
            if (isset($_GET['session'])) {
                $session_data = WC_POS()->session_reports()->get_session_by_id($_GET['session']);
                $data['name'] = $session_data->register_name;
                $data['opened'] = $session_data->opened;
                $data['closed'] = $session_data->closed;
                $data['detail']['opening_cash_amount'] = $session_data->report_data->opening_cash_amount;
                $data['detail']['cash_management_actions'] = $session_data->report_data->cash_management_actions;
                $data['detail']['actual_cash'] =  $session_data->report_data->actual_cash;
            }
            include_once(WC_POS()->plugin_views_path() . '/html-admin-registers-sale_report_overlay.php');
        }
    }
}

new WC_POS_Admin_Print_Report();
