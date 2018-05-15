<?php
namespace Aelia\WC\CurrencySwitcher\WC22;
if(!defined('ABSPATH')) exit; // Exit if accessed directly

use \WC_Aelia_CurrencySwitcher;
use \Aelia_Order;
use \Aelia\CurrencySwitcher\Logger as Logger;
use \Aelia\WC\CurrencySwitcher\WC_Aelia_Reporting_Manager;

/**
 * Overrides the reports for WooCommerce 2.2.
 */
class Reports extends \Aelia\WC\CurrencySwitcher\Reports {
	// @var The WooCommerce version for which this reports class has been implemented
	protected $wc_version = '22';

	/**
	 * Sets the hooks required by the class.
	 */
	protected function set_hooks() {
		parent::set_hooks();

		// Dashboard reports
		add_action('woocommerce_dashboard_status_widget_sales_query', array($this, 'woocommerce_dashboard_status_widget_sales_query'), 10, 1);

		// General reports
		add_action('woocommerce_reports_get_order_report_data_args', array($this, 'woocommerce_reports_get_order_report_data_args'), 10, 1);
	}

	public function woocommerce_dashboard_status_widget_sales_query($query) {
		global $wpdb;
		// Replace query to one that returns the totals in base currency
		$query            = array();
		$query['fields']  = "SELECT SUM( postmeta.meta_value ) FROM {$wpdb->posts} as posts";
		$query['join']    = "INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id ";
		$query['where']   = "WHERE posts.post_type IN ( '" . implode( "','", wc_get_order_types( 'reports' ) ) . "' ) ";
		$query['where']  .= "AND posts.post_status IN ( 'wc-" . implode( "','wc-", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "' ) ";
		$query['where']  .= "AND postmeta.meta_key   = '_order_total_base_currency' ";
		$query['where']  .= "AND posts.post_date >= '" . date( 'Y-m-01', current_time( 'timestamp' ) ) . "' ";
		$query['where']  .= "AND posts.post_date <= '" . date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) . "' ";

		return $query;
	}

	/**
	 * Replaces report fields with their base currency equivalents. This will
	 * ensure that reports will always show consistent totals, without mixing
	 * amounts in different currencies.
	 *
	 * @param array report_args The original report arguments.
	 * @since 4.0.6.150604
	 */
	public function woocommerce_reports_get_order_report_data_args($report_args) {
		// Debug
		//var_dump("BEFORE", $report_args);

		$report_currency = $this->get_report_currency();

		//$report_args['debug'] = true;

		// If a valid currency was selected for the reports, only show data for
		// orders in that currency
		if(in_array($report_currency, array_keys(WC_Aelia_Reporting_Manager::get_currencies_from_sales()))) {
			if(empty($report_args['where_meta']) || !is_array($report_args['where_meta'])) {
				$report_args['where'] = array();
			}

			$report_args['data']['_order_currency'] = array(
				'type' => 'meta',
				'function' => '',
				'name' => '_order_currency'
			);
			$report_args['where'][] = array(
				'key' => 'meta__order_currency.meta_value',
				'value' => $report_currency,
				'operator' => '='
			);
		}
		else {
			// If reports are in default currency, collect all data, but take totals in
			// base currency
			$fields_to_replace = array(
				// Order meta
				'_order_total',
				'_order_discount',
				'_cart_discount',
				'_order_shipping',
				'_order_tax',
				'_order_shipping_tax',
				'_refund_amount',

				// Order items meta
				'_line_subtotal',
				'_line_subtotal_tax',
				'_line_tax',
				'_line_total',
				'tax_amount',
				'shipping_tax_amount',
				'discount_amount',
				'discount_amount_tax',
			);

			$new_fields = array();
			foreach($report_args['data'] as $field_name => $params) {
				if(in_array($field_name, $fields_to_replace)) {
					$field_name .= '_base_currency';
				}
				$new_fields[$field_name] = $params;
			}
			$report_args['data'] = $new_fields;
		}

		// Debug
		//var_dump("AFTER", $report_args);

		return $report_args;
	}
}
