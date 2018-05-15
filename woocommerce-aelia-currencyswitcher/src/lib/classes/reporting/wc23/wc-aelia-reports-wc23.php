<?php
namespace Aelia\WC\CurrencySwitcher\WC23;
if(!defined('ABSPATH')) exit; // Exit if accessed directly

use \WC_Aelia_CurrencySwitcher;
use \Aelia_Order;
use \Aelia\CurrencySwitcher\Logger as Logger;

/**
 * Overrides the reports for WooCommerce 2.3. This class uses the Reports
 * Manager written for WooCommerce 2.2, as it's compatible with WC2.3 as well.
 *
 * @since 4.0.6.150604
 */
class Reports extends \Aelia\WC\CurrencySwitcher\WC22\Reports {
	// @var The WooCommerce version for which this reports class has been implemented
	protected $wc_version = '23';
	// @var string The namespace of the classes that override standard reports.
	protected $report_overrides_namespace = 'WC23';

	/**
	 * Replaces the query for the dashboard widget.
	 *
	 * @param array query The original query.
	 * @return array The query with the "totals" fields replaced by their base
	 * currency counterpart.
	 * @since 4.0.9.150619
	 */
	public function woocommerce_dashboard_status_widget_sales_query($query) {
		global $wpdb;
		// Replace query to one that returns the totals in base currency
		$query            = array();
		$query['fields']  = "SELECT SUM( postmeta.meta_value ) FROM {$wpdb->posts} as posts";
		$query['join']    = "INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id LEFT JOIN {$wpdb->posts} AS parent ON posts.post_parent = parent.ID";
		$query['where']   = "WHERE posts.post_type IN ( '" . implode( "','", array_merge( wc_get_order_types( 'sales-reports' ), array( 'shop_order_refund' ) ) ) . "' ) ";
		$query['where']  .= "AND posts.post_status IN ( 'wc-" . implode( "','wc-", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "' ) ";
		$query['where']  .= "AND ( parent.post_status IN ( 'wc-" . implode( "','wc-", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "' ) OR parent.ID IS NULL ) ";
		$query['where']  .= "AND postmeta.meta_key   = '_order_total_base_currency' ";
		$query['where']  .= "AND posts.post_date >= '" . date( 'Y-m-01', current_time( 'timestamp' ) ) . "' ";
		$query['where']  .= "AND posts.post_date <= '" . date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) . "' ";

		return $query;
	}
}
