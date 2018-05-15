<?php
/**
 * WoocommercePointOfSale Registers Table Class
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/Registers
 * @category    Class
 * @since     0.1
 */


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class WC_Pos_Table_Sessions extends WP_List_Table
{
    protected static $data;
    protected $found_data;

    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'session_reports_table',     //singular name of the listed records
            'plural' => 'session_reports_table',   //plural name of the listed records
            'ajax' => false        //does this table support ajax?
        ));

    }

    function no_items()
    {
        _e('Session reports not found.', 'wc_point_of_sale');
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'register_name':
            case 'outlet_name':
            case 'opened':
            case 'closed':
            case 'cashier':
            case 'total_sales':
            case 'print':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'register_name' => array('register_name', false),
            'outlet_name' => array('outlet_name', false),
            'opened' => array('opened', false),
            'closed' => array('closed', false),
            'cashier' => array('cashier', false),
            'total_sales' => array('total_sales', false),
        );
        return $sortable_columns;
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'register_name' => __('Register', 'wc_point_of_sale'),
            'outlet_name' => __('Outlet', 'wc_point_of_sale'),
            'opened' => __('Opened', 'wc_point_of_sale'),
            'closed' => __('Closed', 'wc_point_of_sale'),
            'cashier' => __('Cashier', 'wc_point_of_sale'),
            'total_sales' => __('Total Sales', 'wc_point_of_sale'),
            'print' => __('Print', 'wc_point_of_sale'),
        );

        if (!current_user_can('manage_wc_point_of_sale')) {
            unset($columns['cb']);
        }
        return $columns;
    }

    function get_bulk_actions()
    {
        $actions = array();
        if (current_user_can('manage_wc_point_of_sale')) {
            $actions = apply_filters('wc_pos_register_bulk_actions', array(
                'delete' => __('Delete', 'wc_point_of_sale'),
            ));
        }
        return $actions;
    }

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />', $item['id']
        );
    }

    function column_total_sales($item)
    {
        return wc_price($item['total_sales']);
    }

    function column_print($item)
    {
        $url = wp_nonce_url(admin_url('admin.php?page=' . WC_POS_TOKEN . '-print&print=report&report=' . $item['register_id'] . '&session=' . $item['id']), 'print_pos_report');
        return '<a href="' . $url . '" class="button action">' . __('Print', 'wc_point_of_sale') . '</a>';
    }

    function prepare_items()
    {
        $page = (isset($_GET['paged'])) ? $_GET['paged'] : 1;
        $per_page = 50;//TODO: add this setting to table page
        $columns = $this->get_columns();
        $hidden = array();
        self::$data = WC_POS()->session_reports()->get_data($page, $per_page);
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->sort_columns();

        $total_items = WC_POS()->session_reports()->get_total_items();

        $this->set_pagination_args(array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page' => $per_page                     //WE have to determine how many items to show on a page
        ));

        $this->items = self::$data;
    }

    private function sort_columns()
    {
        if (isset($_GET['orderby']) && isset($_GET['order'])) {
            $array_order = array();

            foreach (self::$data as $value) {
                $array_order[] = $value[$_GET['orderby']];
            }
            switch ($_GET['order']) {
                case 'asc':
                    if ($_GET['orderby'] == 'total_sales') {
                        array_multisort($array_order, SORT_ASC, SORT_NUMERIC, self::$data);
                    } else {
                        array_multisort($array_order, SORT_ASC, self::$data);
                    }
                    break;
                case 'desc':
                    if ($_GET['orderby'] == 'total_sales') {
                        array_multisort($array_order, SORT_DESC, SORT_NUMERIC, self::$data);
                    } else {
                        array_multisort($array_order, SORT_DESC, self::$data);
                    }
                    break;
            }

        }
    }

} //class