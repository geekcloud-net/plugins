<?php
/**
 * Update WC_POS to 3.0.9
 *
 * @author      Actuality Extensions
 * @category    Admin
 * @package     WC_CRM/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $wpdb;
$wpdb->hide_errors();
$orders = $wpdb->get_results("SELECT posts.ID FROM {$wpdb->posts} as posts
	INNER JOIN {$wpdb->postmeta} AS pos ON (posts.ID = pos.post_id AND  pos.meta_key = 'wc_pos_order_type' AND pos.meta_value = 'POS')	
	WHERE posts.post_type = 'shop_order'
 ");
if( $orders ){
	foreach ($orders as $order) {
		$tax = get_post_meta($order->ID, '_order_tax', true);
		if( empty($tax) && $tax == '' ){
			update_post_meta($order->ID, '_order_tax', 0);
			update_post_meta($order->ID, '_order_shipping_tax', 0);
		}
	}
}