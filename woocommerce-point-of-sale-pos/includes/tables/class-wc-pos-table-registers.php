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

class WC_Pos_Table_Registers extends WP_List_Table
{
    protected static $data;
    protected $found_data;

    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'registers_table',     //singular name of the listed records
            'plural' => 'registers_tables',   //plural name of the listed records
            'ajax' => false        //does this table support ajax?
        ));

    }

    function no_items()
    {
        _e('Registers not found. Try to adjust the filter.', 'wc_point_of_sale');
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'status_reg':
            case 'name':
            case 'change_user':
            case 'email_receipt':
            case 'gift_receipt':
            case 'print_receipt':
            case 'note_request':
            case 'access':
            case 'cash_management':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'name' => array('name', false),
            'grid' => array('grid', false),
            'receipt' => array('receipt', false),
        );
        return $sortable_columns;
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'name' => __('Register', 'wc_point_of_sale'),
            'status_reg' => '<span class="status_head tips" data-tip="' . esc_attr__('Status', 'wc_point_of_sale') . '">' . esc_attr__('Status', 'wc_point_of_sale') . '</span>',
            'change_user' => '<span class="change_user_head tips" data-tip="' . esc_attr__('User Change', 'wc_point_of_sale') . '">' . esc_attr__('User Change', 'wc_point_of_sale') . '</span>',
            'email_receipt' => '<span class="email_receipt_head tips" data-tip="' . esc_attr__('Email Receipt', 'wc_point_of_sale') . '">' . esc_attr__('Email Receipt', 'wc_point_of_sale') . '</span>',
            'gift_receipt' => '<span class="gift_receipt_head tips" data-tip="' . esc_attr__('Gift Receipt', 'wc_point_of_sale') . '">' . esc_attr__('Gift Receipt', 'wc_point_of_sale') . '</span>',
            'print_receipt' => '<span class="print_receipt_head tips" data-tip="' . esc_attr__('Print Receipt', 'wc_point_of_sale') . '">' . esc_attr__('Print Receipt', 'wc_point_of_sale') . '</span>',
            'note_request' => '<span class="note_request_head tips" data-tip="' . esc_attr__('Note Request', 'wc_point_of_sale') . '">' . esc_attr__('Note Request', 'wc_point_of_sale') . '</span>',
            'cash_management' => '<span class="cash_management_head tips" data-tip="' . esc_attr__('Cash Management', 'wc_point_of_sale') . '">' . esc_attr__('Cash Management', 'wc_point_of_sale') . '</span>',
            'access' => __('Access', 'wc_point_of_sale'),
        );
        if (!current_user_can('manage_wc_point_of_sale')) {
            unset($columns['cb']);
        }
        return $columns;
    }

    function usort_reorder($a, $b)
    {
        // If no sort, default to last purchase
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'name';
        // If no order, default to desc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'desc';
        // Determine sort order
        if ($orderby == 'order_value') {
            $result = $a[$orderby] - $b[$orderby];
        } else {
            $result = strcmp($a[$orderby], $b[$orderby]);
        }
        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
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
            '<input type="checkbox" name="id[]" value="%s" />', $item['ID']
        );
    }

    function column_name($item)
    {
        $outlets_name = WC_POS()->outlet()->get_data_names();
        $actions = array();
        if (current_user_can('manage_wc_point_of_sale')) {
            $actions = array(
                'edit' => sprintf('<a href="?page=%s&action=%s&id=%s">Edit</a>', WC_POS()->id_registers, 'edit', $item['ID']),
                'delete' => sprintf('<a href="?page=%s&action=%s&id=%s">Delete</a>', WC_POS()->id_registers, 'delete', $item['ID']),
            );
        }
        if (current_user_can('manage_wc_point_of_sale')) {
            $outlet_string = sprintf('<a class="pos_outlet_name" href="?page=%s&action=%s&id=%s">%s</a><br>', WC_POS()->id_outlets, 'edit', $item['outlet'], $outlets_name[$item['outlet']]);
        } else {
            $outlet_string = $outlets_name[$item['outlet']];
        }

        $detail_fields = WC_Pos_Registers::$register_detail_fields;
        $detail_data = $item['detail'];

        if (isset($detail_fields['grid_template']['options'][$detail_data['grid_template']]))
            $grid_template = $detail_fields['grid_template']['options'][$detail_data['grid_template']];
        else
            $grid_template = '';

        $receipt_template = $detail_fields['receipt_template']['options'][$detail_data['receipt_template']];

        $detail_string_grid = '<small class="meta detail_string_grid">' . $grid_template . '</small>';
        $detail_string_receipt = '<small class="meta detail_string_receipt">' . $receipt_template . '</small>';

        if (!empty($country)) {
            $address_string .= $country;
            $address_url .= $country . ', ';
        }

        if ($outlets_name[$item['outlet']] && pos_check_user_can_open_register($item['ID']) && !pos_check_register_lock($item['ID']) && WC_POS()->wc_api_is_active) {
            $outlet = sanitize_title($outlets_name[$item['outlet']]);
            $register = $item['slug'];
            if (!$register) {
                $register = wc_sanitize_taxonomy_name($item['name']);
                global $wpdb;
                $table_name = $wpdb->prefix . "wc_poin_of_sale_registers";
                $data['slug'] = $register;
                $rows_affected = $wpdb->update($table_name, $data, array('ID' => $item['ID']));
            }

            if (class_exists('SitePress')) {
                $settings = get_option('icl_sitepress_settings');
                if ($settings['urls']['directory_for_default_language'] == 1) {
                    $register_url = get_home_url() . '/' . ICL_LANGUAGE_CODE . "/point-of-sale/$outlet/$register";
                } else {
                    $register_url = get_home_url() . "/point-of-sale/$outlet/$register";
                }
            } else {
                $register_url = get_home_url() . "/point-of-sale/$outlet/$register";
            }

            if (is_ssl() || get_option('woocommerce_pos_force_ssl_checkout') == 'yes') {
                $register_url = str_replace('http:', 'https:', $register_url);
            }
            $name = sprintf(
                '<strong><a style="font-size: 14px;" href="%s">%s</a></strong>', $register_url, $item['name']
            );

        } else {
            $name = sprintf(
                '<strong>%s</strong>', $item['name']
            );
        }

        return sprintf('%1$s ' . __('located in', 'wc_point_of_sale') . ' %2$s %3$s %4$s %5$s', $name, $outlet_string, $detail_string_grid, $detail_string_receipt, $this->row_actions($actions));
    }

    function column_change_user($item)
    {
        $end_sale_fields = WC_Pos_Registers::$register_end_of_sale_fields;
        $settings_data = $item['settings'];

        if (isset($end_sale_fields['change_user']['options'][$settings_data['change_user']]) && $end_sale_fields['change_user']['options'][$settings_data['change_user']] == 'Yes') {
            $change_user = '<span style="color: #ad74a2;" class="woocommerce_pos_register_table_icons_yes tips" data-tip="' . esc_attr__('User Changes After Sale', 'wc_point_of_sale') . '"></span>';
        } else {
            $change_user = '<span style="color: #999;" class="woocommerce_pos_register_table_icons_no tips" data-tip="' . esc_attr__('User Does Not Change After Sale', 'wc_point_of_sale') . '"></span>';
        };

        return sprintf('%1$s', $change_user);
    }

    function column_email_receipt($item)
    {

        $settings_data = $item['settings'];

        $opt = (int)$settings_data['email_receipt'];
        switch ($opt) {
            case 1:
                $email_receipt = '<span style="color: #ad74a2;" class="woocommerce_pos_register_table_icons_yes tips" data-tip="' . esc_attr__('Receipt Is Emailed To All Customers', 'wc_point_of_sale') . '"></span>';
                break;
            case 2:
                $email_receipt = '<span style="color: #ad74a2;" class="woocommerce_pos_register_table_icons_yes tips" data-tip="' . esc_attr__('Receipt Is Emailed To Non-guest Customers Only', 'wc_point_of_sale') . '"></span>';
                break;
            default:
                $email_receipt = '<span style="color: #999;" class="woocommerce_pos_register_table_icons_no tips" data-tip="' . esc_attr__('Receipt Is Not Emailed', 'wc_point_of_sale') . '"></span>';
                break;
        }
        return sprintf('%1$s', $email_receipt);
    }

    function column_gift_receipt($item)
    {
        $settings_data = $item['settings'];
        $opt = 0;
        if (isset($settings_data['gift_receipt'])) {
            $opt = (int)$settings_data['gift_receipt'];
        }
        switch ($opt) {
            case 1:
                $gift_receipt = '<span style="color: #ad74a2;" class="woocommerce_pos_register_table_icons_yes tips" data-tip="' . esc_attr__('Gift Receipt Is Printed', 'wc_point_of_sale') . '"></span>';
                break;

            default:
                $gift_receipt = '<span style="color: #999;" class="woocommerce_pos_register_table_icons_no tips" data-tip="' . esc_attr__('Gift Receipt Is Not Printed', 'wc_point_of_sale') . '"></span>';
                break;
        }

        return sprintf('%1$s', $gift_receipt);
    }

    function column_print_receipt($item)
    {
        $settings_data = $item['settings'];
        $opt = (int)$settings_data['print_receipt'];

        switch ($opt) {
            case 1:
                $print_receipt = '<span style="color: #ad74a2;" class="woocommerce_pos_register_table_icons_yes tips" data-tip="' . esc_attr__('Receipt Is Printed', 'wc_point_of_sale') . '"></span>';
                break;

            default:
                $print_receipt = '<span style="color: #999;" class="woocommerce_pos_register_table_icons_no tips" data-tip="' . esc_attr__('Receipt Is Not Printed', 'wc_point_of_sale') . '"></span>';
                break;
        }

        return sprintf('%1$s', $print_receipt);
    }

    function column_note_request($item)
    {
        $end_sale_fields = WC_Pos_Registers::$register_end_of_sale_fields;
        $settings_data = $item['settings'];

        if (!isset($end_sale_fields['note_request']['options'][$settings_data['note_request']]) || !$end_sale_fields['note_request']['options'][$settings_data['note_request']] || $end_sale_fields['note_request']['options'][$settings_data['note_request']] == 'None') {
            $note_request = '<span style="color: #999;" class="woocommerce_pos_register_table_icons_no tips" data-tip="' . esc_attr__('Note Is Not Taken', 'wc_point_of_sale') . '"></span>';
        } else if ($end_sale_fields['note_request']['options'][$settings_data['note_request']] == 'On Save') {
            $note_request = '<span style="color: #ad74a2;" class="woocommerce_pos_register_table_icons_yes tips" data-tip="' . esc_attr__('Note Is Taken On Save', 'wc_point_of_sale') . '"></span>';
        } else {
            $note_request = '<span style="color: #ad74a2;" class="woocommerce_pos_register_table_icons_yes tips" data-tip="' . esc_attr__('Note Is Taken On All Sales', 'wc_point_of_sale') . '"></span>';
        };

        return sprintf('%1$s', $note_request);
    }

    function column_access($item)
    {
        $error_string = '';
        $detail_fields = WC_Pos_Registers::$register_detail_fields;
        $detail_data = $item['detail'];
        if (isset($detail_fields['grid_template']['options'][$detail_data['grid_template']]))
            $grid_template = $detail_fields['grid_template']['options'][$detail_data['grid_template']];
        else
            $grid_template = '';

        $receipt_template = $detail_fields['receipt_template']['options'][$detail_data['receipt_template']];

        $outlets_name = WC_POS()->outlet()->get_data_names();

        if (!$grid_template)
            $error_string = '<b>' . $detail_fields['grid_template']['label'] . '</b> is required';
        if (!$receipt_template)
            $error_string .= '<b>' . $detail_fields['receipt_template']['label'] . ' </b> is required';
        if (!$outlets_name[$item['outlet']])
            $error_string .= '<b>Outlet </b> is required';

        if (!empty($error_string)) {
            return '<a class="button tips closed-register" data-tip="' . $error_string . '" class="register_not_full" >Closed Register</button> <span style="display: none;">' . $error_string . '</span>';
        } elseif (pos_check_user_can_open_register($item['ID']) && !pos_check_register_lock($item['ID']) && WC_POS()->wc_api_is_active) {

            $btn_text = __('Open', 'wc_point_of_sale');
            if (pos_check_register_is_open($item['ID'])) {
                $btn_text = __('Enter', 'wc_point_of_sale');
            }
            $outlet = sanitize_title($outlets_name[$item['outlet']]);
            $register = $item['slug'];

            if (class_exists('SitePress')) {
                $settings = get_option('icl_sitepress_settings');
                if ($settings['urls']['directory_for_default_language'] == 1) {
                    $register_url = get_home_url() . '/' . ICL_LANGUAGE_CODE . "/point-of-sale/$outlet/$register";
                } else {
                    $register_url = get_home_url() . "/point-of-sale/$outlet/$register";
                }
            } else {
                $register_url = get_home_url() . "/point-of-sale/$outlet/$register";
            }

            if (is_ssl() || get_option('woocommerce_pos_force_ssl_checkout') == 'yes') {
                $register_url = str_replace('http:', 'https:', $register_url);
            }
            return '<a class="button tips ' . $btn_text . '-register" href="' . $register_url . '" data-tip="' . $btn_text . ' Register" >' . $btn_text . '</a>';

        } else {
            if (!WC_POS()->wc_api_is_active) {
                $btn_text = __('Open', 'wc_point_of_sale');
                return '<a class="button tips open-register" data-tip="' . __('The WooCommerce API is disabled on this site.', 'wc_point_of_sale') . '" disabled>' . $btn_text . '</button>';
            } else {
                $userid = pos_check_register_lock($item['ID']);
                $user = get_userdata($userid);
                $btn_text = __('Open', 'wc_point_of_sale');
                if ($user) {
                    $name = trim($user->first_name . ' ' . $user->last_name);
                    if ($name == '')
                        $name = $user->user_nicename;
                    return '<a class="button tips open-register" data-tip="' . $name . ' is currently logged on this register." disabled>' . $btn_text . '</button>';
                } else {
                    return '<a class="button tips open-register" data-tip="You are not assigned to this outlet" disabled>' . $btn_text . '</button>';
                }
            }
        }
    }

    function column_cash_management($item)
    {
        $settings_data = $item['detail'];
        $opt = 0;
        if (isset($settings_data['float_cash_management'])) {
            $opt = (int)$settings_data['float_cash_management'];
        }
        switch ($opt) {
            case 1:
                $cash_management = '<span style="color: #ad74a2;" class="woocommerce_pos_register_table_icons_yes tips" data-tip="' . esc_attr__('Cash Is Managed', 'wc_point_of_sale') . '"></span>';
                break;

            default:
                $cash_management = '<span style="color: #999;" class="woocommerce_pos_register_table_icons_no tips" data-tip="' . esc_attr__('Cash Is Not Managed', 'wc_point_of_sale') . '"></span>';
                break;
        }

        return sprintf('%1$s', $cash_management);
    }

    function column_status_reg($item)
    {
        if (pos_check_register_is_open($item['ID']) && WC_POS()->wc_api_is_active) {
            $btn_text = __('Open', 'wc_point_of_sale');
            return '<span class="register-status-open tips" data-tip=' . $btn_text . '></span>';
        } else {
            $btn_text = __('Closed', 'wc_point_of_sale');
            return '<span class="register-status-closed tips" data-tip=' . $btn_text . '></span>';
        }
    }

    function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        self::$data = WC_POS()->register()->get_data();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        usort(self::$data, array(&$this, 'usort_reorder'));
        //TODO: Maybe optimize it by SQL query
        if ((isset($_GET['status']) && !empty($_GET['status'])) || (isset($_GET['outlet']) && !empty($_GET['outlet']))) {
            foreach (self::$data as $key => $register) {
                if (isset($_GET['status'])) {
                    switch ($_GET['status']) {
                        case 'open':
                            if (strtotime($register['closed']) > strtotime($register['opened'])) {
                                unset(self::$data[$key]);
                            }
                            break;
                        case 'close':
                            if (strtotime($register['opened']) > strtotime($register['closed'])) {
                                unset(self::$data[$key]);
                            }
                            break;
                    }
                }
                if (isset($_GET['outlet'])) {
                    if ($register['outlet'] != $_GET['outlet']) {
                        unset(self::$data[$key]);
                    }
                }
            }
        }
        $user = get_current_user_id();
        $screen = get_current_screen();
        $option = $screen->get_option('per_page', 'option');
        $per_page = get_user_meta($user, $option, true);
        if (empty ($per_page) || $per_page < 1) {
            $per_page = $screen->get_option('per_page', 'default');
        }

        $current_page = $this->get_pagenum();

        $total_items = count(self::$data);
        if ($_GET['page'] == WC_POS()->id_registers) {
            // only ncessary because we have sample data
            $this->items = array_slice(self::$data, (($current_page - 1) * $per_page), $per_page);

            $this->set_pagination_args(array(
                'total_items' => $total_items,                  //WE have to calculate the total number of items
                'per_page' => $per_page                     //WE have to determine how many items to show on a page
            ));

        } else {
            $this->items = self::$data;
        }
    }

    //TODO: Maybe optimize it by SQL query
    protected function get_views()
    {
        $registers = WC_POS()->register()->get_data();
        $opened = 0;
        $closed = 0;
        foreach ($registers as $register) {
            if (strtotime($register['opened']) > strtotime($register['closed'])) {
                $opened = $opened + 1;
            } else {
                $closed = $closed + 1;
            }
        }
        $total_items = count($registers);
        $class = (isset($_GET['status']) && !$_GET['status']) ? 'current' : '';
        $url = '';
        if (isset($_GET['outlet']) && !empty($_GET['outlet'])) {
            $url = '&outlet=' . $_GET['outlet'];
        }
        $views = array(
            'all' => '<a href="' . admin_url('admin.php?page=wc_pos_registers') . $url . '" class="' . $class . '" aria-current="page">' . __('All') . ' <span class="count">(' . $total_items . ')</span></a>',
        );
        $class = (isset($_GET['status']) && $_GET['status'] == 'open') ? 'current' : '';
        if ($opened) {
            $views['open'] = '<a href="' . admin_url('admin.php?page=wc_pos_registers&status=open') . $url . '"  class="' . $class . '" aria-current="page">' . __('Open') . ' <span class="count">(' . $opened . ')</span></a>';
        }
        $class = (isset($_GET['status']) && $_GET['status'] == 'close') ? 'current' : '';
        if ($closed) {
            $views['close'] = '<a href="' . admin_url('admin.php?page=wc_pos_registers&status=close') . $url . '" class="' . $class . '" aria-current="page">' . __('Close') . ' <span class="count">(' . $closed . ')</span></a>';
        }
        return $views;
    }

    public function extra_tablenav($which)
    {
        $outlets = WC_POS()->outlet()->get_data();
        ?>
        <div class="alignleft actions">
            <label class="screen-reader-text"
                   for="outlet"><?php _e('Filter by outlet', 'woocommerce_point_of_sale') ?></label>
            <select name="outlet" id="outlet" class="outlet_select postform">
                <option value="0"><?php _e('All outlets', 'woocommerce_point_of_sale') ?></option>
                <?php foreach ($outlets as $outlet) { ?>
                    <option value="<?php echo $outlet['ID'] ?>" <?php echo (isset($_GET['outlet']) && $_GET['outlet'] == $outlet['ID']) ? 'selected' : '' ?>><?php echo $outlet['name'] ?></option>
                <?php } ?>
            </select>
            <?php
            $cur_outlet = (isset($_GET['outlet'])) ? '&outlet=' . $_GET['outlet'] : '';
            $cur_status = '';
            if (isset($_GET['status']) && !empty($_GET['status'])) {
                $cur_status = '&status=' . $_GET['status'];
            }
            ?>
            <a href="<?php echo admin_url('admin.php?page=wc_pos_registers') . $cur_outlet . $cur_status; ?>"
               class="button filter"
               data-url="<?php echo admin_url('admin.php?page=wc_pos_registers') . $cur_status; ?>"> <?php _e('Filter', 'woocommerce_point_of_sale') ?></a>
            <?php


            ?>
        </div>
    <?php }

} //class