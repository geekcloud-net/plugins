<?php
/**
 * Setup menus in WP admin.
 *
 * @version        1.0
 * @category    Class
 * @author      Actuality Extensions
 * @package     WC_POS/Classes
 * @since       2.7.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WC_POS_Admin_Menus')) :

    /**
     * WC_POS_Admin_Menus Class
     */
    class WC_POS_Admin_Menus
    {

        /**
         * Hook in tabs.
         */
        public function __construct()
        {
            // Add menus
            add_filter('set-screen-option', array(&$this, 'set_screen'), 10, 3);
            add_action('admin_menu', array($this, 'add_menu'));
            add_filter('admin_head', array($this, 'menu_highlight'));
            add_filter('admin_print_footer_scripts', array($this, 'highlight_menu_item'));
        }

        public static function set_screen($status, $option, $value)
        {
            return $value;
        }

        /**
         * Add the menu item
         */
        public function add_menu()
        {
            $hook = add_menu_page(
                __('Point of Sale', 'wc_point_of_sale'), // page title
                __('Point of Sale', 'wc_point_of_sale'), // menu title
                'view_register', // capability
                WC_POS()->id, // unique menu slug
                array($this, 'render_registers'),
                null, '55.8'
            );
            $registers_hook = add_submenu_page(WC_POS()->id,
                __("Registers", 'wc_point_of_sale'),
                __("Registers", 'wc_point_of_sale'),
                'view_register',
                WC_POS()->id_registers,
                array($this, 'render_registers')
            );
            $outlets_hook = add_submenu_page(WC_POS()->id,
                __("Outlets", 'wc_point_of_sale'),
                __("Outlets", 'wc_point_of_sale'),
                'manage_wc_point_of_sale',
                WC_POS()->id_outlets,
                array($this, 'render_outlets')
            );
            $grids_hook = add_submenu_page(WC_POS()->id,
                __("Product Grids", 'wc_point_of_sale'),
                '<span id="wc_pos_grids">' . __("Product Grids", 'wc_point_of_sale') . '</span>',
                'manage_wc_point_of_sale',
                WC_POS()->id_grids,
                array($this, 'render_grids')
            );
            // add submenu page or permission allow this page action
            $tiles_page_title = '';
            if (isset($_GET['page']) && $_GET['page'] == WC_POS()->id_tiles && isset($_GET['grid_id']) && !empty($_GET['grid_id'])) {
                $grid_id = $_GET['grid_id'];
                $grids_single_record = wc_point_of_sale_tile_record($grid_id);
                $tiles_page_title = $grids_single_record[0]->name . ' Layout';
            }

            $tiles_hook = add_submenu_page(WC_POS()->id_grids,
                sprintf(__("Tiles - %s", 'wc_point_of_sale'), $tiles_page_title),
                sprintf(__("Tiles - %s", 'wc_point_of_sale'), $tiles_page_title),
                'manage_wc_point_of_sale',
                WC_POS()->id_tiles,
                array($this, 'render_tiles')
            );
            $receipt_hook = add_submenu_page(WC_POS()->id,
                __("Receipts", 'wc_point_of_sale'),
                __("Receipts", 'wc_point_of_sale'),
                'manage_wc_point_of_sale',
                WC_POS()->id_receipts,
                array($this, 'render_receipts')
            );
            $users_hook = add_submenu_page(WC_POS()->id,
                __("Cashiers", 'wc_point_of_sale'),
                __("Cashiers", 'wc_point_of_sale'),
                'view_register',
                WC_POS()->id_users,
                array($this, 'render_users')
            );

            $cash_management_hook = add_submenu_page(WC_POS()->id,
                __("Cash Management", 'wc_point_of_sale'),
                __("Cash Management", 'wc_point_of_sale'),
                'view_register',
                'wc_pos_cash_management',
                array($this, 'render_cash_management')
            );

            $session_reports = add_submenu_page(WC_POS()->id,
                __("Sales by sessions", 'wc_point_of_sale'),
                __("Sales by sessions", 'wc_point_of_sale'),
                'view_register',
                'wc_pos_session_reports',
                array($this, 'render_session_reports')
            );

            add_submenu_page(WC_POS()->id,
                __("Barcode", 'wc_point_of_sale'),
                __("Barcode", 'wc_point_of_sale'),
                'manage_wc_point_of_sale',
                WC_POS()->id_barcodes,
                array($this, 'render_barcodes')
            );
            add_submenu_page(WC_POS()->id,
                __("Stock", 'wc_point_of_sale'),
                __("Stock", 'wc_point_of_sale'),
                'manage_wc_point_of_sale',
                WC_POS()->id_stock_c,
                array($this, 'render_stocks_controller')
            );

            add_submenu_page(WC_POS()->id,
                __("Settings", 'wc_point_of_sale'),
                __("Settings", 'wc_point_of_sale'),
                'manage_wc_point_of_sale',
                WC_POS()->id_settings,
                array($this, 'render_settings')
            );

            $update_log = add_submenu_page(null,
                __("Update log", 'wc_point_of_sale'),
                __("Update log", 'wc_point_of_sale'),
                'view_register',
                'wc_pos_update_log',
                array($this, 'render_update_log')
            );

            add_action("load-$hook", array($this, 'rerister_screen_option'));
            add_action("load-$registers_hook", array($this, 'rerister_screen_option'));
            add_action("load-$outlets_hook", array($this, 'outlet_screen_option'));
            add_action("load-$grids_hook", array($this, 'grids_screen_option'));
            add_action("load-$tiles_hook", array($this, 'tiles_screen_option'));
            add_action("load-$receipt_hook", array($this, 'receipt_screen_option'));
            add_action("load-$users_hook", array($this, 'users_screen_option'));
            add_action("load-$session_reports", array($this, 'sessions_screen_option'));

            if (isset($_GET['page'])) {
                $curent_screen = $rest = substr($_GET['page'], 0, 7);
                if ($curent_screen == 'wc_pos_')
                    add_filter('screen_options_show_screen', '__return_false');
            }
        }

        public function render_registers()
        {
            if (isset($_GET['action']) && isset($_GET['id']) && $_GET['action'] == 'edit' && $_GET['id'] != '')
                WC_POS()->register()->display_edit_form($_GET['id']);
            elseif (isset($_GET['action']) && $_GET['action'] == 'add_new') {
                WC_POS()->register()->display_register_form();
            } else {
                WC_POS()->register()->display();
            }
        }

        public function render_outlets()
        {
            if (isset($_GET['action']) && isset($_GET['id']) && $_GET['action'] == 'edit' && $_GET['id'] != '')
                WC_POS()->outlet()->display_edit_form($_GET['id']);
            else
                WC_POS()->outlet()->display();
        }

        public function render_grids()
        {
            WC_POS()->grid()->output();
        }

        public function render_tiles()
        {
            if (isset($_GET['action']) && isset($_GET['id']) && $_GET['action'] == 'edit' && $_GET['id'] != '')
                WC_POS()->tile()->display_edit_form($_GET['id']);
            else
                WC_POS()->tile()->output();
        }

        public function render_receipts()
        {
            if (isset($_GET['action']) && $_GET['action'] == 'add')
                WC_POS()->receipt()->display_single_receipt_page();

            elseif (isset($_GET['action']) && $_GET['action'] == 'edit')
                WC_POS()->receipt()->display_single_receipt_page();

            else
                WC_POS()->receipt()->display_receipt_table();
        }

        public function render_users()
        {
            WC_POS()->user()->display();
        }

        public function render_barcodes()
        {
            WC_POS()->barcode()->display_single_barcode_page();
        }

        public function render_stocks_controller()
        {
            WC_POS()->stock()->display_single_stocks_page();
        }

        public function render_settings()
        {
            WC_POS_Admin_Settings::output();
        }

        public function rerister_screen_option()
        {
            $option = 'per_page';
            $args = array(
                'label' => __('Registers', 'wc_point_of_sale'),
                'default' => 10,
                'option' => 'registers_per_page'
            );
            add_screen_option($option, $args);
            WC_POS()->tables['registers'] = WC_POS()->registers_table();
        }

        public function outlet_screen_option()
        {
            $option = 'per_page';
            $args = array(
                'label' => __('Outlets', 'wc_point_of_sale'),
                'default' => 10,
                'option' => 'outlets_per_page'
            );
            add_screen_option($option, $args);

            WC_POS()->tables['outlets'] = WC_POS()->outlet_table();
        }

        public function grids_screen_option()
        {
            $option = 'per_page';
            $args = array(
                'label' => __('Layouts', 'wc_point_of_sale'),
                'default' => 10,
                'option' => 'grids_per_page'
            );
            add_screen_option($option, $args);

            WC_POS()->tables['grids'] = WC_POS()->grids_table();
        }

        public function tiles_screen_option()
        {
            $option = 'per_page';
            $args = array(
                'label' => __('Tiles', 'wc_point_of_sale'),
                'default' => 10,
                'option' => 'tiles_per_page'
            );
            add_screen_option($option, $args);

            WC_POS()->tables['tiles'] = WC_POS()->tiles_table();
        }

        public function receipt_screen_option()
        {
            $option = 'per_page';
            $args = array(
                'label' => __('Receipts', 'wc_point_of_sale'),
                'default' => 10,
                'option' => 'receipt_per_page'
            );
            add_screen_option($option, $args);
            WC_POS()->tables['receipts'] = WC_POS()->receipts_table();
        }

        public function users_screen_option()
        {
            $option = 'per_page';
            $args = array(
                'label' => __('Cashiers', 'wc_point_of_sale'),
                'default' => 10,
                'option' => 'users_per_page'
            );
            add_screen_option($option, $args);

            WC_POS()->tables['users'] = WC_POS()->users_table();
        }


        function menu_highlight()
        {
            global $submenu;
            if (isset($submenu[WC_POS()->id]) && isset($submenu[WC_POS()->id][1])) {
                $submenu[WC_POS()->id][0] = $submenu[WC_POS()->id][1];
                unset($submenu[WC_POS()->id][1]);
                unset($submenu[WC_POS()->id][7]);
                unset($submenu[WC_POS()->id][6]);
            }
        }

        function highlight_menu_item()
        {
            if (isset($_GET['page']) && $_GET['page'] == WC_POS()->id_tiles) {

                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        jQuery('#wc_pos_grids').parent().addClass('current').parent().addClass('current');
                        jQuery('#toplevel_page_wc_point_of_sale').addClass('wp-has-current-submenu wp-menu-open').removeClass('wp-not-current-submenu');
                        jQuery('#toplevel_page_wc_point_of_sale > a').addClass('wp-has-current-submenu wp-menu-open').removeClass('wp-not-current-submenu');
                    });
                </script>
                <?php
            }
        }

        //TODO: Change to WP_list_table view
        public function render_cash_management()
        {
            $cash_management = new WC_Pos_Float_Cash($_GET['register']);
            $cash_management->render_page();
        }

        public function render_session_reports()
        {
            WC_POS()->session_reports()->display();
        }

        public function sessions_screen_option()
        {
            WC_POS()->tables['sessions'] = WC_POS()->sessions_table();
        }

        public function render_update_log()
        {
            $changes = array();
            //$matches = array();
            $cur_ver = WC_POS()->_version;
            $version = $cur_ver[0] . $cur_ver[2] . $cur_ver[4];
            $txt_file = file_get_contents(WC_POS()->dir . '/readme.txt');
            preg_match_all('/== Changelog ==(.*)+/s', $txt_file, $matches);
            $rows = explode("\n", $matches[0][0]);
            foreach ($rows as $row) {
                if (strpos($row, '=') === 0) {
                    preg_match('/([0-9.]+)/', $row, $res);
                    if (isset($res[0])) {
                        $ver = $res[0];
                        $upd_v = $ver[0] . $ver[2] . $ver[4];
                        if ($version - $upd_v > 1) {
                            break;
                        }
                    }
                    continue;
                }
                $changes[$ver][] = $row;
            }
            include_once 'views/html-update-log.php';
        }
    }

endif;

return new WC_POS_Admin_Menus();
