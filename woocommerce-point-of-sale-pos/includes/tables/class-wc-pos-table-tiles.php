<?php
/**
 * WoocommercePointOfSale tiles Table Class
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/tiles
 * @category    Class
 * @since     0.1
 */


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class WC_Pos_Table_Tiles extends WP_List_Table
{
    protected static $data;
    protected $found_data;

    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'tiles_table',
            'plural' => 'tiles_tables',
            'ajax' => false        //does this table support ajax?
        ));
    }

    function no_items()
    {
        _e('Tiles not found.', 'wc_point_of_sale');
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'thumb':
            case 'product':
            case 'preview':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'thumb' => __('Image', 'wc_point_of_sale'),
            'product' => __('Product', 'wc_point_of_sale'),
        );
        return $columns;
    }

    function get_bulk_actions()
    {
        $actions = apply_filters('wc_pos_tile_bulk_actions', array(
            'delete' => __('Delete', 'wc_point_of_sale'),
        ));
        return $actions;
    }

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />', $item['ID']
        );
    }

    function column_thumb($item)
    {
        $image = '';
        $size = 'shop_thumbnail';
        $attr = array();
        $id_pr = $item['product_id'];
        if ($item['default_selection']) {
            $id_pr = $item['default_selection'];
        }
        if (has_post_thumbnail($id_pr)) {
            $image = get_the_post_thumbnail($id_pr, $size, $attr);
        } elseif (($parent_id = wp_get_post_parent_id($id_pr)) && has_post_thumbnail($parent_id)) {
            $image = get_the_post_thumbnail($parent_id, $size, $attr);
        } else {
            $image = wc_placeholder_img($size);
        }
        if (!$image || $image == NULL) $image = wc_placeholder_img($size);

        return $image;
    }

    function column_preview($item)
    {
        if ($item['style'] == 'image') {
            $image = '';
            $size = 'shop_thumbnail';
            $id_pr = $item['product_id'];
            if ($item['default_selection']) {
                $id_pr = $item['default_selection'];
            }
            if (has_post_thumbnail($id_pr)) {
                $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($id_pr), $size);
                $image = $thumbnail[0];
            } elseif (($parent_id = wp_get_post_parent_id($id_pr)) && has_post_thumbnail($parent_id)) {
                $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($parent_id), $size);
                $image = $thumbnail[0];
            } else {
                $image = wc_placeholder_img_src();
            }
            if (!$image || $image == NULL) $image = wc_placeholder_img_src();

            return sprintf(
                '<div class="tile-preview" style="background: url(\'%s\') center no-repeat; background-size: contain; background-color: #fff; padding: 10px;">&nbsp;</div>', $image
            );
        } else {
            $product = wc_get_product($item['product_id']);
            $name = $product->get_title();
            return sprintf(
                '<div class="tile-preview" style="background: #%s; color: #%s; padding: 10px;">%s</div>', $item['background'], $item['colour'], $name
            );
        }
    }

    function column_product($item)
    {
        $actions = array(
            'edit' => sprintf('<a href="?page=%s&grid_id=%s&action=%s&id=%s">Edit</a>', 'wc_pos_tiles', $item['grid_id'], 'edit', $item['ID']),
            'delete' => sprintf('<a href="?page=%s&grid_id=%s&action=%s&id=%s">Delete</a>', 'wc_pos_tiles', $item['grid_id'], 'delete', $item['ID']),
            'view' => sprintf('<a href="post.php?post=%s&action=edit">View Product</a>', $item['product_id']),
        );
        if (!current_user_can('edit_private_products')) {
            unset($actions['view']);
        }
        $product = wc_get_product($item['product_id']);

        if (!$product) {
            return false;
        }

        $variation_detail = '';
        if ($product->is_type(array('variable')) || $product->get_type() == 'variation') {
            if ($product->get_type() == 'variation') {
                $variation_detail = ' - ' . wc_get_formatted_variation($product->get_attributes());
            } elseif ($variation_data = $product->get_default_attributes()) {
                $variation_detail = ' - ' . wc_get_formatted_variation($variation_data, true);
            } else {
                $variation_detail = ' - <i>' . __('No Default Selection', 'wc_point_of_sale') . '</i>';
            }
        }


        $name = sprintf('<strong><a href="?page=%s&grid_id=%s&action=%s&id=%s">%s</a></strong>%s', 'wc_pos_tiles', $item['grid_id'], 'edit', $item['ID'], $product->get_title(), $variation_detail);

        return sprintf('%1$s %2$s', $name, $this->row_actions($actions));
    }

    function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        self::$data = WC_POS()->tile()->get_data();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $per_page = 25;
        $current_page = $this->get_pagenum();
        $total_items = count(self::$data);

        $this->found_data = array_slice(self::$data, (($current_page - 1) * $per_page), $per_page);
        $this->set_pagination_args(array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page' => $per_page                     //WE have to determine how many items to show on a page
        ));
        $this->items = $this->found_data;
    }

} //class