<?php
if( !defined('ABSPATH')){
	exit;
}
$product_categories = get_terms( 'product_cat', array('fields' =>'ids') );

?>
<?php
/**
 * Deposits Admin Panel
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Deposits and Down Payments
 * @version 1.0.0
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly
?>

<div id="yith_wcdd_panel_shipping_day">
    <form id="plugin-fw-wc" class="shipping-day-table" method="post">

        <h3><?php _e( 'Shipping Day for Product Category', 'yith-woocommerce-delivery-date' ) ?></h3>
        <div class="yith-wcdd-new-shipping-day-cat">
            <h4><?php _e( 'Add new', 'yith-woocommerce-delivery-date' ) ?></h4>
            <?php
            $args = array(
                'id' => 'ywcdd_product_cat_search',
                'class' => 'wc-product-search',
                'name' => 'yith_new_shipping_day_cat[category]',
                'data-action' => 'ywcdd_search_product_category',
                'data-multiple' => false,
                'data-placeholder' => __( 'Search for a category&hellip;', 'yith-woocommerce-delivery-date' ),
                'style' => 'width:300px;'

            );

            yit_add_select2_fields( $args );
            ?>
            <input type="number" name="yith_new_shipping_day_cat[day]" class="ywcdd_day_cat" min="0" step="1" value="" style="max-width: 100px;" placeholder="<?php _e('Day', 'yith-woocommerce-delivery.day');?>" />
            <input type="submit" class="yith-add-new-cat-day button button-primary" value="<?php echo esc_attr( __( 'Add', 'yith-woocommerce-delivery-date' ) ) ?>" />
        </div>
         <?php 
     	require_once( YITH_DELIVERY_DATE_INC.'admin-tables/class.yith-wcdd-shipping-day-category-table.php');
     	$category_table = new YITH_WCDD_Shipping_Day_Category_Table();
     	$category_table->prepare_items();
     	$category_table->display();
     ?>   

        <h3><?php _e( 'Shipping Day for Product', 'yith-woocommerce-delivery-date' ) ?></h3>
        <div class="yith-wcdd-new-shipping-day-prod">
            <h4><?php _e( 'Add new', 'yith-woocommerce-delivery-date' ) ?></h4>
            <?php
                $args = array(
                'id' => 'ywcdd_product_search',
                'class' => 'wc-product-search',
                'name' => 'yith_new_shipping_day_prod[product]',
                'data-multiple' => false,
                'data-placeholder' => __( 'Search for a product&hellip;', 'yith-woocommerce-delivery-date' ),
                 'style' => 'width:300px;'

            );

            yit_add_select2_fields( $args );
            ?>
            <input type="number" name="yith_new_shipping_day_prod[day]" class="ywcdd_day_prod" min="0" step="1" value="" style="max-width: 100px;" placeholder="<?php _e('Day', 'yith-woocommerce-delivery.day');?>" />
            <input type="submit" class="yith-add-new-prod-day button button-primary" value="<?php echo esc_attr( __( 'Add', 'yith-woocommerce-delivery-date' ) ) ?>" />
        </div>
        
         <?php 
     	require_once( YITH_DELIVERY_DATE_INC.'admin-tables/class.yith-wcdd-shipping-day-product-table.php');
     	$product_table = new YITH_WCDD_Shipping_Day_Product_Table();
     	$product_table->prepare_items();
     	$product_table->display();
     ?>   
    
    <input type="button" class="yith-update-all button button-primary" value="<?php _e('Update All','yith-woocommerce-delivery-date')?>">
    </form>
</div>
