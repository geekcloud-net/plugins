<?php
/**
 * WoocommercePointOfSale Receipts Table Class
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/Receipts
 * @category  Class
 * @since     0.1
 */


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class WC_Pos_Table_Receipts extends WP_List_Table
{
    protected static $data;
    protected $found_data;

    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'receipts_table',
            'plural' => 'receipts_tables',
            'ajax' => false        //does this table support ajax?
        ));

    }


    function no_items()
    {
        _e('Receipts not found. Try to adjust the filter.', 'wc_point_of_sale');
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'name_receipt':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'name_receipt' => array('name_receipt', false),
        );
        return $sortable_columns;
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'name_receipt' => __('Receipt', 'wc_point_of_sale'),
            'copies_number' => __('Copies', 'wc_point_of_sale'),
            'receipt_width' => __('Width', 'wc_point_of_sale'),
            'tax_summary' => __('Tax Summary', 'wc_point_of_sale'),
        );
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
        $actions = apply_filters('wc_pos_receipt_bulk_actions', array(
            'delete' => __('Delete', 'wc_point_of_sale'),
        ));
        return $actions;
    }

    function column_cb($item)
    {
        $d = '';
        if (!wc_pos_check_can_delete('receipt', $item['ID']))
            $d = 'disabled="disabled"';
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" %s />', $item['ID'], $d
        );
    }

    function column_name_receipt($item)
    {

        $actions['edit'] = sprintf('<a href="?page=%s&action=%s&id=%s">Edit</a>', 'wc_pos_receipts', 'edit', $item['ID']);
        if (wc_pos_check_can_delete('receipt', $item['ID']))
            $actions['delete'] = sprintf('<a href="?page=%s&action=%s&id=%s">Delete</a>', 'wc_pos_receipts', 'delete', $item['ID']);

        $name = sprintf('<strong><a href="?page=%s&action=%s&id=%s">%s</a></strong>', 'wc_pos_receipts', 'edit', $item['ID'], $item['name']);

        return sprintf('%1$s %2$s', $name, $this->row_actions($actions));
    }

    function column_copies_number($item)
    {
        return $item['print_copies_count'];
    }

    function column_receipt_width($item)
    {
        return ($item['receipt_width'] == 0) ? __('Dynamic', 'wc_point_of_sale') : $item['receipt_width'].'mm';
    }

    function column_tax_summary($item)
    {
        return ($item['tax_summary'] == 'yes') ? __('Yes', 'wc_point_of_sale') : __('No', 'wc_point_of_sale');
    }

    /**
     * Display the search box.
     *
     */
    function search_box($text, $input_id)
    {

        $input_id = $input_id . '-search-input';

        if (!empty($_REQUEST['orderby']))
            echo '<input type="hidden" name="orderby" value="' . esc_attr($_REQUEST['orderby']) . '" />';
        if (!empty($_REQUEST['order']))
            echo '<input type="hidden" name="order" value="' . esc_attr($_REQUEST['order']) . '" />';
        ?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
            <input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>"/>
            <?php submit_button($text, 'button', false, false, array('id' => 'search-submit')); ?>
        </p>
        <?php
    }

    function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        self::$data = WC_POS()->receipt()->get_data();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        usort(self::$data, array(&$this, 'usort_reorder'));

        $user = get_current_user_id();
        $screen = get_current_screen();
        $option = $screen->get_option('per_page', 'option');
        $per_page = get_user_meta($user, $option, true);
        if (empty ($per_page) || $per_page < 1) {
            $per_page = $screen->get_option('per_page', 'default');
        }

        $current_page = $this->get_pagenum();

        $total_items = count(self::$data);
        if ($_GET['page'] == 'wc_pos_receipts') {
            // only ncessary because we have sample data
            $this->found_data = array_slice(self::$data, (($current_page - 1) * $per_page), $per_page);

            $this->set_pagination_args(array(
                'total_items' => $total_items,                  //WE have to calculate the total number of items
                'per_page' => $per_page                     //WE have to determine how many items to show on a page
            ));
            $this->items = $this->found_data;
        } else {
            $this->items = self::$data;
        }
    }

} //class