<?php
/**
 * Post Types Admin
 *
 * @author   Actuality Extensions
 * @category Admin
 * @package  WC_POS_Admin/Admin
 * @version  1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WC_POS_Admin_Post_Actions')) :

    /**
     * WC_POS_Admin_Post_Actions Class
     *
     * Handles the edit posts views and some functionality on the edit post screen for WC post types.
     */
    class WC_POS_Admin_Post_Actions
    {

        private static $saved_meta_boxes = false;

        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('admin_init', array($this, 'save_barcode'));
            add_action('admin_init', array($this, 'actions_grids'));
            add_action('admin_init', array($this, 'actions_outlets'));
            add_action('admin_init', array($this, 'actions_receipts'));
            add_action('admin_init', array($this, 'actions_registers'));
            add_action('admin_init', array($this, 'actions_tiles'));
            add_action('admin_init', array($this, 'actions_session_reports'));
        }

        public function save_barcode()
        {
            if (isset($_GET['page']) && $_GET['page'] != WC_POS()->id_barcodes) return;

            if (isset($_POST['action']) && $_POST['action'] == 'save_barcode') {
                WC_POS()->barcode()->save_barcode();
            }
        }

        public function actions_grids()
        {
            if (isset($_GET['page']) && $_GET['page'] != WC_POS()->id_grids) return;

            if (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id']) && !empty($_POST['id'])) {
                WC_POS()->grid()->delete_grids();
            } else if (isset($_POST['action2']) && $_POST['action2'] == 'delete' && isset($_POST['id']) && !empty($_POST['id'])) {
                WC_POS()->grid()->delete_grids();
            }
        }

        public function actions_outlets()
        {
            if (!isset($_GET['page'])) return;
            if (!isset($_POST['action']) && !isset($_GET['action'])) return;
            if (isset($_GET['page']) && $_GET['page'] != 'wc_pos_outlets') return;

            if (isset($_POST['action']) && $_POST['action'] == 'add-wc-pos-outlets') {
                WC_POS()->outlet()->save_outlet();
            } else if (isset($_GET['action']) && isset($_GET['id']) && $_GET['action'] == 'delete' && $_GET['id'] != '') {
                WC_POS()->outlet()->delete_outlet($_GET['id']);
            } else if ((isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id']) && !empty($_POST['id'])) || (isset($_POST['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && !empty($_GET['id']))) {
                WC_POS()->outlet()->delete_outlet();
            } else if (isset($_POST['action2']) && $_POST['action2'] == 'delete' && isset($_POST['id']) && !empty($_POST['id'])) {
                WC_POS()->outlet()->delete_outlet();
            } else if (isset($_POST['action']) && $_POST['action'] == 'edit-wc-pos-outlets' && isset($_POST['id']) && !empty($_POST['id'])) {
                WC_POS()->outlet()->save_outlet();
            }
        }

        public function actions_receipts()
        {
            if (isset($_GET['page']) && $_GET['page'] != WC_POS()->id_receipts) return;

            if (isset($_POST['action']) && $_POST['action'] == 'save_receipt') {
                WC_POS()->receipt()->save_receipt();
            } elseif (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && !empty($_GET['id']))
                WC_POS()->receipt()->delete_receipt($_GET['id']);
            elseif (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id']) && !empty($_POST['id']))
                WC_POS()->receipt()->delete_receipt($_POST['id']);
            elseif (isset($_POST['action2']) && $_POST['action2'] == 'delete' && isset($_POST['id']) && !empty($_POST['id']))
                WC_POS()->receipt()->delete_receipt($_POST['id']);
        }

        public function actions_registers()
        {
            global $wpdb;
            if (isset($_GET['page']) && $_GET['page'] != WC_POS()->id_registers) return;

            if (isset($_GET['logout']) && !empty($_GET['logout'])) {
                setcookie("wc_point_of_sale_register", $_GET['logout'], time() - 3600 * 24 * 120, '/');

                $table_name = $wpdb->prefix . "wc_poin_of_sale_registers";
                $register_id = $_GET['logout'];
                $db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = $register_id");

                if ($db_data && 0 != ($user_id = get_current_user_id())) {
                    $row = $db_data[0];

                    $lock_user = $row->_edit_last;
                    if ($lock_user == $user_id) {
                        $now = current_time('mysql');
                        $data['closed'] = $now;
                        $data['_edit_last'] = $user_id;
                        $rows_affected = $wpdb->update($table_name, $data, array('ID' => $register_id));

                        wp_redirect(wp_login_url(get_admin_url(get_current_blog_id(), '/') . 'admin.php?page=wc_pos_registers'));
                    }
                }

            } elseif (isset($_GET['close']) && !empty($_GET['close'])) {
                setcookie("wc_point_of_sale_register", $_GET['close'], time() - 3600 * 24 * 120, '/');

                $register_id = $_GET['close'];

                if (pos_close_register($register_id)) {
                    $display_reports = get_option('wc_pos_display_reports');

                    $url = get_admin_url(get_current_blog_id(), '/') . 'admin.php?page=wc_pos_registers';
                    if ($display_reports == 'yes') {
                        $url .= '&report=' . $register_id;
                    }
                    wp_redirect($url);
                }

            }
            if (isset($_POST['action']) && $_POST['action'] == 'add-wc-pos-registers') {
                WC_POS()->register()->save_register();
            } else if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && !empty($_GET['id'])) {
                WC_POS()->register()->delete_register();
            } else if (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id']) && !empty($_POST['id'])) {
                WC_POS()->register()->delete_register();
            } else if (isset($_POST['action2']) && $_POST['action2'] == 'delete' && isset($_POST['id']) && !empty($_POST['id'])) {
                WC_POS()->register()->delete_register();
            } else if (isset($_POST['action']) && $_POST['action'] == 'edit-wc-pos-registers' && isset($_POST['id']) && !empty($_POST['id'])) {
                WC_POS()->register()->save_register();
            } else if (isset($_POST['action']) && $_POST['action'] == 'save-wc-pos-registers-as-order' && isset($_POST['id']) && !empty($_POST['id'])) {
                WC_POS()->register()->save_register_as_order();
            } else if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['reg']) && !empty($_GET['reg'])) {
                setcookie("wc_point_of_sale_register", $_GET['reg'], time() + 3600 * 24 * 120, '/');
            }
        }

        public function actions_tiles()
        {
            if (isset($_GET['page']) && $_GET['page'] != WC_POS()->id_tiles) return;

            if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && !empty($_GET['id'])) {
                WC_POS()->tile()->delete_tiles($_GET['id']);
            } else if (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id']) && !empty($_POST['id'])) {
                WC_POS()->tile()->delete_tiles($_POST['id']);
            } else if (isset($_POST['action2']) && $_POST['action2'] == 'delete' && isset($_POST['id']) && !empty($_POST['id'])) {
                WC_POS()->tile()->delete_tiles($_POST['id']);
            } else if (isset($_POST['action']) && $_POST['action'] == 'wc_pos_edit_update_tiles' && isset($_POST['id']) && !empty($_POST['id'])) {
                WC_POS()->tile()->update_tile();
            }
        }

        public function actions_session_reports()
        {
            if ((isset($_POST['action']) && $_POST['action'] == 'delete') || (isset($_POST['action2']) && $_POST['action2'] == 'delete') && isset($_POST['id']) && !empty($_POST['id'])) {
                WC_POS()->session_reports()->delete_session_report();
            }
        }

    }

    new WC_POS_Admin_Post_Actions();

endif;