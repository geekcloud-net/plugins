<?php
namespace Aelia\WC\CurrencySwitcher;
if(!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Helper class to handle installation and update of Currency Switcher plugin.
 */
class WC_Aelia_CurrencySwitcher_Install extends \Aelia\WC\Aelia_Install {
	// @var string The name of the lock that will be used by the installer to prevent race conditions.
	protected $lock_name = 'WC_AELIA_CURRENCY_SWITCHER';

	// @var Aelia\WC\CurrencySwitcher\Settings Settings controller instance.
	protected $settings;

	// @var array A list of exchange rates. Used for caching.
	protected $exchange_rates = array();

	// @var array A list of the currencies with invalid exchange rates.
	// @since 3.9.6.160408
	protected $invalid_fx_rates = array();

	// @var bool Indicates if we are running all updates in one go.
	// @since 4.5.3.171108
	protected $running_all_updates = false;

	// @var string The version associated to the last update method executed.
	// @since 4.5.3.171108
	protected $last_update_method_version = null;

	/**
	 * Returns current instance of the Currency Switcher.
	 *
	 * @return WC_Aelia_CurrencySwitcher
	 */
	protected function currency_switcher() {
		return WC_Aelia_CurrencySwitcher::instance();
	}

	public function __construct() {
		parent::__construct();

		$this->logger = WC_Aelia_CurrencySwitcher::instance()->get_logger();
		$this->settings = WC_Aelia_CurrencySwitcher::settings();
	}

	/**
	 * Overrides standard update method to ensure that requirements for update are
	 * in place.
	 *
	 * @param string plugin_id The ID of the plugin.
	 * @param string new_version The new version of the plugin, which will be
	 * stored after a successful update to keep track of the status.
	 * @return bool
	 */
	public function update($plugin_id, $new_version) {
		// Set time limit to 10 minutes (updates can take some time)
		@set_time_limit(10 * 60);

		// If updates should be forced, delete the plugin version from the database.
		// The update procedure will think that it's a first install an re-run all
		// updates
		if($this->force_all_updates()) {
			delete_option($plugin_id);
		}

		// We need the plugin to be configured before the updates can be applied. If
		// that is not the case, simply return true. The update will be called again
		// at next page load, until it will finally find settings and apply the
		// required changes
		if(!$this->currency_switcher()->plugin_configured()) {
			$this->logger->info(__('No settings found. This means that the plugin has just '.
														 'been installed. Update will run as soon as the settings ' .
														 'are saved.', Definitions::TEXT_DOMAIN));
			return true;
		}

		$result = parent::update($plugin_id, $new_version);

		// If we didn't run all updates, reset the "last update" version to the
		// last executed operation. This will ensure that the update process will
		// resume from the next step
		// @since 4.5.3.171108
		if(!$this->running_all_updates && !empty($this->last_update_method_version)) {
			update_option($plugin_id, $this->last_update_method_version);
		}
		return $result;
	}

	/**
	 * Indicates if all updates should be re-applied from the beginning. This
	 * should be done only in precise circumstances, and only by administrators.
	 *
	 * @return bool
	 */
	protected function force_all_updates() {
		return isset($_GET[Definitions::ARG_FORCE_ALL_UPDATES]) &&
					 ($_GET[Definitions::ARG_FORCE_ALL_UPDATES] == 'go') &&
					 is_admin() &&
					 current_user_can('manage_options');
	}

	/**
	 * Converts an amount from a Currency to another.
	 *
	 * @param float amount The amount to convert.
	 * @param string from_currency The source Currency.
	 * @param string to_currency The destination Currency.
	 * @separam int order The order from which the value was taken. Used mainly
	 * for logging purposes.
	 * @return float The amount converted in the destination currency.
	 */
	public function convert($amount, $from_currency, $to_currency, $order) {
		// If the exchange rate for either the order currency or the base currency
		// cannot be retrieved, it probably means that the plugin has just been
		// installed, or that it hasn't been configured correctly. In such case,
		// returning false will tag the update as "unsuccessful", and it will run
		// again at next page load
		$exchange_rate_msg_details = __('This usually occurs when the Currency Switcher ' .
																		'plugin has not yet been configured and exchange ' .
																		'rates have not been specified. <strong>Please refer to ' .
																		'our knowledge base to learn how to fix it</strong>: ' .
																		'<a href="https://aelia.freshdesk.com/solution/articles/3000017311-i-get-a-warning-saying-that-exchange-rate-could-not-be-retrieved-">I get a warning saying that "Exchange rate could not be retrieved" </a>.',
																		Definitions::TEXT_DOMAIN);

		// Fetch and store the exchange rate for the source currency
		if(!isset($this->exchange_rates[$from_currency])) {
			$this->exchange_rates[$from_currency] = $this->settings->get_exchange_rate($from_currency);
		}

		// If the exchange rate for the source currency is not valid, don't attempt a
		// conversion
		if(($this->exchange_rates[$from_currency] == false) ||
			 !is_numeric($this->exchange_rates[$from_currency]) ||
			 ($this->exchange_rates[$from_currency] <= 0)) {

			if(!in_array($from_currency, $this->invalid_fx_rates)) {
				$this->add_message(E_USER_WARNING,
													 sprintf(__('Exchange rate for currency "%s" could not be retrieved.', Definitions::TEXT_DOMAIN) .
																	 ' ' .
																	 $exchange_rate_msg_details,
																	 $from_currency));
				$this->invalid_fx_rates[] = $from_currency;
			}
			return false;
		}

		// Fetch and store the exchange rate for the target currency
		if(!isset($this->exchange_rates[$to_currency])) {
			$this->exchange_rates[$to_currency] = $this->settings->get_exchange_rate($to_currency);
		}

		// If the exchange rate for the target currency is not valid, don't attempt a
		// conversion
		if(($this->exchange_rates[$to_currency] == false) ||
			 !is_numeric($this->exchange_rates[$to_currency]) ||
			 ($this->exchange_rates[$to_currency] <= 0)) {

			if(!in_array($to_currency, $this->invalid_fx_rates)) {
				$this->add_message(E_USER_WARNING,
													 sprintf(__('Exchange rate for currency "%s" could not be retrieved.', Definitions::TEXT_DOMAIN) .
																	 ' ' .
																	 $exchange_rate_msg_details,
																	 $to_currency));
				$this->invalid_fx_rates[] = $to_currency;
			}
			return false;
		}

		return $this->currency_switcher()->convert($amount, $from_currency, $to_currency, null, false);
	}

	/**
	 * Sets custom meta with totals in base currency (i.e. <amount>_base_currency
	 * against orders that were placed in base currency before the Currency Switcher
	 * was installed.
	 *
	 * @return bool
	 */
	protected function update_to_3_2_10_1402126() {
		$base_currency = $this->settings->base_currency();

		// Process past orders that were placed in order currency. This will
		// automatically populate all the "_base_currency" fields without a need to
		// make manual recalculations, since the conversion rate would be 1:1 anyway
		$SQL = $this->wpdb->prepare("
			INSERT IGNORE INTO {$this->wpdb->prefix}postmeta	(
				post_id
				,meta_key
				,meta_value
			)
			SELECT
				meta_order.post_id
				,CONCAT(meta_order.meta_key, '_base_currency') as meta_key
				,meta_order.meta_value
			FROM
				{$this->wpdb->prefix}postmeta meta_order
				JOIN
				{$this->wpdb->prefix}postmeta meta_order_currency ON
					(meta_order_currency.post_id = meta_order.post_id) AND
					(meta_order_currency.meta_key = '_order_currency') AND
					(meta_order_currency.meta_value = %s)
				LEFT JOIN
				{$this->wpdb->prefix}postmeta AS meta_order_base_currency ON
					(meta_order_base_currency.post_id = meta_order.post_id) AND
					(meta_order_base_currency.meta_key = CONCAT(meta_order.meta_key, '_base_currency'))
			WHERE
				(meta_order.meta_key IN ('_order_total', '_order_discount', '_cart_discount', '_order_shipping', '_order_tax', '_order_shipping_tax')) AND
				(meta_order_base_currency.meta_value IS NULL);
		", $base_currency);

		$this->add_message(E_USER_NOTICE, __('Processing past orders that were placed in base currency...'));
		$rows_affected = $this->exec($SQL);
		$this->add_message(E_USER_NOTICE, sprintf(__('Done. %s rows affected.'), $rows_affected));

		// Debug
		//var_dump($SQL);die();
		return true;
	}

	/**
	 * Calculate order totals and taxes in base currency for Orders that have been
	 * generated before version 3.2.11.1402126. This method corrects the calculation
	 * of order totals in base currency, which were incorrectly made taking into
	 * account the exchange markup eventually specified in configuration.
	 * Note: recalculation is made from the 1st of the year onwards, as exchange
	 * rates have changed significantly in the past months and it's not currently
	 * possible to retrieve them at a specific point in time.
	 * @return bool
	 */
	protected function update_to_3_2_11_1402126() {
		$last_year = date('Y') - 1;
		$cs = $this->currency_switcher();
		$base_currency = $this->settings->base_currency();

		$this->add_message(E_USER_NOTICE, __('Calculating order totals in base currency for past orders...'));
		// Retrieve the exchange rates for the orders whose data already got
		// partially converted
		$SQL = "
			SELECT
				posts.ID AS order_id
				,posts.post_date AS post_date
				,meta_order.meta_key
				,meta_order.meta_value
				,CONCAT(meta_order.meta_key, '_base_currency') AS meta_key_base_currency
				,meta_order_base_currency.meta_value AS meta_value_base_currency
				,meta_order_currency.meta_value AS currency
			FROM
				{$this->wpdb->posts} AS posts
			JOIN
				{$this->wpdb->postmeta} AS meta_order ON
					(meta_order.post_id = posts.ID) AND
					(meta_order.meta_key IN ('_order_total', '_order_discount', '_cart_discount', '_order_shipping', '_order_tax', '_order_shipping_tax'))
			LEFT JOIN
				{$this->wpdb->postmeta} AS meta_order_base_currency ON
					(meta_order_base_currency.post_id = posts.ID) AND
					(meta_order_base_currency.meta_key = CONCAT(meta_order.meta_key, '_base_currency'))
			LEFT JOIN
				{$this->wpdb->postmeta} AS meta_order_currency ON
					(meta_order_currency.post_id = posts.ID) AND
					(meta_order_currency.meta_key = '_order_currency')
			WHERE
				(posts.post_type = 'shop_order') AND
				(meta_order.meta_value IS NOT NULL) AND
				(meta_order_base_currency.meta_value IS NULL) AND
				(post_date >= '{$last_year}-01-01 00:00:00')
			ORDER BY
				posts.ID ASC
		";

		$orders_to_update = $this->select($SQL);
		$total_orders_to_update = count($orders_to_update);

		// Limit the rows to update to a thousand at a time, to avoid timeouts
		$max_orders_to_update = !empty($_GET['aelia_cs_install_row_limit']) ? $_GET['aelia_cs_install_row_limit'] : 1000;
		$updated_orders = 0;

		// Debug
		//var_dump($orders_to_update); die();

		// Keep track of the orders without currency, so that we can report them
		// only once
		$orders_without_currency = array();
		foreach($orders_to_update as $order) {
			// If order currency is empty, for whatever reason, no conversion can be
			// performed (it's not possible to assume that a specific currency was
			// used)
			if(empty($order->currency)) {
				if(!in_array($order->order_id, $orders_without_currency)) {
					$orders_without_currency[] = $order->order_id;
					$this->logger->info(__('Order does not have a currency.' . ' ' .
																 'This may lead to imprecise results in the reports.', Definitions::TEXT_DOMAIN),
															array(
																'Order ID' => $order->order_id,
																'Base Currency' => $base_currency,
															));
				}

				continue;
			}

			// If the meta value is not numeric, assume that it's zero
			if(!is_numeric($order->meta_value)) {
				$order->meta_value = 0;
				$this->logger->info(__('Invalid value found for order meta. Value assumed to be zero', Definitions::TEXT_DOMAIN),
														array(
															'Order ID' => $order->order_id,
															'Meta Value' => $order->meta_value,
														));
			}

			// Try to retrieve the exchange rate used when the order was placed
			$value_in_base_currency = $this->convert($order->meta_value,
																							 $order->currency,
																							 $base_currency,
																							 $order);
			$value_in_base_currency = $cs->float_to_string($value_in_base_currency);

			try {
				update_post_meta($order->order_id,
												 $order->meta_key . '_base_currency',
												 $value_in_base_currency);
			}
			catch(Exception $e) {
				$this->add_message(E_USER_ERROR,
													 sprintf(__('Exception occurred updating base currency values for order %s. ' .
																			'Error: %s.'),
																	 $order->order_id,
																	 $e->getMessage()));
				return false;
			}

			// Stop after having reached the maximum amount of orders for one run
			$updated_orders++;
			if($updated_orders >= $max_orders_to_update) {
				break;
			}

			// If we haven't updated all orders, reset the progress version to the
			// previous one, so that this update can run again
			if($updated_orders < $total_orders_to_update) {
				$this->last_update_method_version = '3.2.10.1402126';
			}
		}

		// Inform the user of the progress
		$message = sprintf(__('Done. %s rows affected.'), $updated_orders);
		if($total_orders_to_update > $updated_orders) {
			$message .= ' ' . sprintf(__('%d rows remaining. The process will continue at the next page load.'), $total_orders_to_update - $updated_orders);
		}
		$this->add_message(E_USER_NOTICE, $message);

		// Flushing the cache will prevent memory issues. This is important, because
		// update_post_meta() tends to cache post meta after processing it
		wp_cache_flush();

		return true;
	}

	/**
	 * Calculate order items totals and taxes in base currency for all orders.
	 * This method adds the line totals in base currency for all the order items
	 * created before Currency Switcher 3.2.11.140227 was installed.
	 *
	 * @return bool
	 */
	protected function update_to_3_3_7_140611() {
		$price_decimals = WC_Aelia_CurrencySwitcher::settings()->price_decimals($this->settings->base_currency());

		// Enable big selects. This will MySQL from throwing an error because the
		// query we run contains too many JOIN clauses
		$SQL = 'SET SQL_BIG_SELECTS=1';
		$this->exec($SQL);

		// Retrieve the order items for which the values in base currencies will be
		// added/updated
		$SQL = $this->wpdb->prepare("
			INSERT INTO {$this->wpdb->prefix}woocommerce_order_itemmeta (
				order_item_id
				,meta_key
				,meta_value
			)
			SELECT
				LINE_ITEMS_DATA.order_item_id
				,LINE_ITEMS_DATA.order_item_meta_key_base_currency
				,LINE_ITEMS_DATA.meta_value_base_currency
			FROM (
				-- Fetch all line items for whom the totals in base currency have not been saved yet
				SELECT
					posts.ID AS order_id
					,meta_order_base_currency.meta_value / meta_order.meta_value as exchange_rate
					,WCOI.order_item_id
					,WCOI.order_item_type
					,WCOIM.meta_key
					,WCOIM.meta_value
					,CONCAT(WCOIM.meta_key, '_base_currency') AS order_item_meta_key_base_currency
					,ROUND(WCOIM.meta_value * (meta_order_base_currency.meta_value / meta_order.meta_value), %d) AS meta_value_base_currency
				FROM
					{$this->wpdb->posts} AS posts
				JOIN
					{$this->wpdb->postmeta} AS meta_order ON
						(meta_order.post_id = posts.ID)
				LEFT JOIN
					{$this->wpdb->postmeta} AS meta_order_base_currency ON
						(meta_order_base_currency.post_id = posts.ID) AND
						(meta_order_base_currency.meta_key = CONCAT(meta_order.meta_key, '_base_currency')) AND
						(meta_order_base_currency.meta_value > 0)
				LEFT JOIN
					{$this->wpdb->postmeta} AS meta_order_currency ON
						(meta_order_currency.post_id = posts.ID) AND
						(meta_order_currency.meta_key = '_order_currency')
				-- Order items
				JOIN
					{$this->wpdb->prefix}woocommerce_order_items WCOI ON
						(WCOI.order_id = posts.ID)
				JOIN
					{$this->wpdb->prefix}woocommerce_order_itemmeta WCOIM ON
						(WCOIM.order_item_id = WCOI.order_item_id) AND
						(WCOIM.meta_key IN ('_line_subtotal',
											'_line_subtotal_tax',
											'_line_tax',
											'_line_total',
											'tax_amount',
											'shipping_tax_amount'))
				LEFT JOIN
					{$this->wpdb->prefix}woocommerce_order_itemmeta WCOIM_TOUPDATE ON
						(WCOIM_TOUPDATE.order_item_id = WCOIM.order_item_id) AND
						(WCOIM_TOUPDATE.meta_key = CONCAT(WCOIM.meta_key, '_base_currency'))
				WHERE
					(WCOIM_TOUPDATE.meta_value IS NULL) AND
					(posts.post_type = 'shop_order') AND
					(meta_order.meta_key = '_order_total') AND
					(meta_order.meta_value IS NOT NULL) AND
					(meta_order_base_currency.meta_value IS NOT NULL)
			) AS LINE_ITEMS_DATA;
		", $price_decimals);

		//var_dump($SQL);die();

		$this->add_message(E_USER_NOTICE,
											 __('Recalculating line totals in base currency...'));
		$rows_affected = $this->exec($SQL);

		// Debug
		//var_dump($order_items_to_update);die();
		if($rows_affected === false) {
			$this->add_message(E_USER_ERROR,
												 __('Failed. Please check PHP error log for error messages ' .
														'related to the operation.'));
			return false;
		}
		else {
			$this->add_message(E_USER_NOTICE,
												 sprintf(__('Done. %s rows affected.'), $rows_affected));
		}

		return true;
	}

	/**
	 * Update plugin settings to version 4.0.
	 */
	protected function update_to_4_0_0_150311() {
		$settings = $this->settings->current_settings();

		// Replace the "force currency by billing country" with "force currency by
		// country" setting, maintaining existing setting
		$key = Settings::FIELD_CURRENCY_BY_BILLING_COUNTRY_ENABLED;
		if(!isset($settings[$key])) {
			return true;
		}
		if($settings[$key] == true) {
			$new_setting_value = Settings::OPTION_BILLING_COUNTRY;
		}
		else {
			$new_setting_value = Settings::OPTION_DISABLED;
		}
		$settings[Settings::FIELD_FORCE_CURRENCY_BY_COUNTRY] = $new_setting_value;

		unset($settings[$key]);

		$this->settings->save($settings);
		return true;
	}


	/**
	 * Calculate order items totals and taxes in base currency for all orders. This
	 * method patches the "discount amount" meta that might not have been calculated
	 * correctly.
	 *
	 * @return bool
	 * @since 4.0.6.150604
	 */
	protected function update_to_4_0_6_150604() {
		$price_decimals = WC_Aelia_CurrencySwitcher::settings()->price_decimals($this->settings->base_currency());

		// Retrieve the order items for which the values in base currencies will be
		// added/updated
		$SQL = $this->wpdb->prepare("
			INSERT INTO {$this->wpdb->prefix}woocommerce_order_itemmeta (
				order_item_id
				,meta_key
				,meta_value
			)
			SELECT
				LINE_ITEMS_DATA.order_item_id
				,LINE_ITEMS_DATA.order_item_meta_key_base_currency
				,LINE_ITEMS_DATA.meta_value_base_currency
			FROM (
				-- Fetch all line items for which the totals in base currency have not been saved yet
				SELECT
					posts.ID AS order_id
					,meta_order_base_currency.meta_value / meta_order.meta_value as exchange_rate
					,WCOI.order_item_id
					,WCOI.order_item_type
					,WCOIM.meta_key
					,WCOIM.meta_value
					,CONCAT(WCOIM.meta_key, '_base_currency') AS order_item_meta_key_base_currency
					,ROUND(WCOIM.meta_value * (meta_order_base_currency.meta_value / meta_order.meta_value), %d) AS meta_value_base_currency
				FROM
					{$this->wpdb->posts} AS posts
				JOIN
					{$this->wpdb->postmeta} AS meta_order ON
						(meta_order.post_id = posts.ID)
				LEFT JOIN
					{$this->wpdb->postmeta} AS meta_order_base_currency ON
						(meta_order_base_currency.post_id = posts.ID) AND
						(meta_order_base_currency.meta_key = CONCAT(meta_order.meta_key, '_base_currency')) AND
						(meta_order_base_currency.meta_value > 0)
				-- Order items
				JOIN
					{$this->wpdb->prefix}woocommerce_order_items WCOI ON
						(WCOI.order_id = posts.ID)
				JOIN
					{$this->wpdb->prefix}woocommerce_order_itemmeta WCOIM ON
						(WCOIM.order_item_id = WCOI.order_item_id) AND
						(WCOIM.meta_key IN (
							'discount_amount',
							'discount_amount_tax'
						))
				LEFT JOIN
					{$this->wpdb->prefix}woocommerce_order_itemmeta WCOIM_TOUPDATE ON
						(WCOIM_TOUPDATE.order_item_id = WCOIM.order_item_id) AND
						(WCOIM_TOUPDATE.meta_key = CONCAT(WCOIM.meta_key, '_base_currency'))
				WHERE
					(WCOIM_TOUPDATE.meta_value IS NULL) AND
					(posts.post_type = 'shop_order') AND
					(meta_order.meta_key = '_order_total') AND
					(meta_order.meta_value IS NOT NULL) AND
					(meta_order_base_currency.meta_value IS NOT NULL)
			) AS LINE_ITEMS_DATA;
		", $price_decimals);

		//var_dump($SQL);die();

		$this->add_message(E_USER_NOTICE,
											 __('Recalculating discounts in base currency...'));
		$rows_affected = $this->exec($SQL);

		// Debug
		//var_dump($order_items_to_update);die();
		if($rows_affected === false) {
			$this->add_message(E_USER_ERROR,
												 __('Failed. Please check PHP error log for error messages ' .
														'related to the operation.'));
			return false;
		}
		else {
			$this->add_message(E_USER_NOTICE,
												 sprintf(__('Done. %s rows affected.'), $rows_affected));
		}
		return true;
	}


	/**
	 * Calculates refund totals in base currency. This method cleans up past
	 * refund calculations, which might be incorrect, and uses the exchange rate
	 * applicable when the refund was created to recalculate the refund totals in
	 * base currency.
	 *
	 * @return bool
	 * @since 4.0.7.150604
	 */
	protected function update_to_4_0_7_150604() {
		$price_decimals = WC_Aelia_CurrencySwitcher::settings()->price_decimals($this->settings->base_currency());

		$px = $this->wpdb->prefix;

		// Retrieve the refunds items for which the values in base currencies will be
		// added/updated
		$SQL = $this->wpdb->prepare("
			SELECT
				REFUNDS_META.refund_id
				,REFUNDS_META.refund_meta_key_base_currency
				,REFUNDS_META.meta_value_base_currency
			FROM (
				-- Fetch all refunds for which the totals in base currency have not been saved yet
				SELECT
					refunds.ID AS refund_id
					,COALESCE(ORDER_META2.meta_value, 0) / COALESCE(ORDER_META1.meta_value, 1) as exchange_rate
					,CONCAT(OM_EXISTING.meta_key, '_base_currency') AS refund_meta_key_base_currency
					,ROUND(OM_EXISTING.meta_value *
								 COALESCE(EXCHANGE_RATES.exchange_rate,
												 (COALESCE(ORDER_META2.meta_value, 0) / COALESCE(ORDER_META1.meta_value, 1))),
								 %d) AS meta_value_base_currency
				FROM
					{$this->wpdb->posts} AS refunds
				-- Get the meta in order currency
				LEFT JOIN
					{$this->wpdb->postmeta} AS OM_EXISTING ON
						(OM_EXISTING.post_id = refunds.ID) AND
						(OM_EXISTING.meta_key IN ('_order_total', '_order_discount', '_cart_discount', '_order_shipping', '_order_tax', '_order_shipping_tax', '_refund_amount'))
				-- Meta from parent orders - START
				JOIN
					{$this->wpdb->posts} AS parent_orders ON
						(parent_orders.ID = refunds.post_parent)
				-- Get order total in order currency
				JOIN
					{$this->wpdb->postmeta} AS ORDER_META1 ON
						(ORDER_META1.post_id = parent_orders.ID) AND
						(ORDER_META1.meta_key = '_order_total')
				-- Get order total in base currency
				JOIN
					{$this->wpdb->postmeta} AS ORDER_META2 ON
						(ORDER_META2.post_id = parent_orders.ID) AND
						(ORDER_META2.meta_key = '_order_total_base_currency')
				-- Get order currency
				JOIN
					{$this->wpdb->postmeta} AS ORDER_META3 ON
						(ORDER_META3.post_id = parent_orders.ID) AND
						(ORDER_META3.meta_key = '_order_currency')
				-- Meta from parent orders - END

				-- Get the exchange rate to calculate the refund totals in base currency
				LEFT JOIN
					(
						SELECT
							DATE(FX_ORDERS.post_date) AS order_date
							,FX_ORDERS_META3.meta_value AS order_currency
							,AVG(FX_ORDERS_META2.meta_value / FX_ORDERS_META1.meta_value) AS exchange_rate
						FROM
							{$this->wpdb->posts} AS FX_ORDERS
							-- Get order total in order currency
							JOIN
							{$px}postmeta AS FX_ORDERS_META1 ON
								(FX_ORDERS_META1.post_id = FX_ORDERS.ID) AND
								(FX_ORDERS_META1.meta_key = '_order_total')
							-- Get order total in base currency
							JOIN
								{$px}postmeta AS FX_ORDERS_META2 ON
									(FX_ORDERS_META2.post_id = FX_ORDERS.ID) AND
									(FX_ORDERS_META2.meta_key = '_order_total_base_currency')
							-- Get order total in base currency
							JOIN
								{$px}postmeta AS FX_ORDERS_META3 ON
									(FX_ORDERS_META3.post_id = FX_ORDERS.ID) AND
									(FX_ORDERS_META3.meta_key = '_order_currency')
						GROUP BY
							DATE(FX_ORDERS.post_date)
							,FX_ORDERS_META3.meta_value
					) AS EXCHANGE_RATES ON
					(EXCHANGE_RATES.order_currency = ORDER_META3.meta_value) AND
					(EXCHANGE_RATES.order_date = DATE(refunds.post_date))
				WHERE
					(refunds.post_type = 'shop_order_refund')
			) AS REFUNDS_META;
		", $price_decimals);

		// Debug
		//var_dump($SQL);die();

		$this->add_message(E_USER_NOTICE,
											 __('Recalculating refunds totals in base currency...'));
		$dataset = $this->select($SQL);
		// Debug
		//var_dump($orders_to_update); die();

		$result = true;
		foreach($dataset as $refund_meta) {
			try {
				update_post_meta($refund_meta->refund_id,
												 $refund_meta->refund_meta_key_base_currency,
												 $refund_meta->meta_value_base_currency);
			}
			catch(Exception $e) {
				$this->add_message(E_USER_ERROR,
													 sprintf(__('Exception occurred updating base currency values for refund %s. ' .
																			'Data (JSON): "%s". Error: %s.'),
																	 $refund_meta->refund_id,
																	 json_encode($refund_meta),
																	 $e->getMessage()));
				$result = false;
			}
		}

		// Debug
		//var_dump($order_items_to_update);die();
		if($result === false) {
			$this->add_message(E_USER_ERROR,
												 __('Failed. Please check PHP error log for error messages ' .
														'related to the operation.'));
			return false;
		}
		else {
			$this->add_message(E_USER_NOTICE,
												 sprintf(__('Done. %s rows affected.'), count($dataset)));
		}
		return true;
	}

	/**
	 * Returns a list of the methods that will perform the updates. This method
	 * alters the list normally returned by the installation logic, so that methods
	 * are executed step by step (one per page load), rather than all together.
	 * This helps avoiding timeouts on servers with a strict execution limit.
	 *
	 * @param string current_version Current version of the plugin. This will
	 * determine which update methods still have to be executed.
	 * @return array
	 * @since 4.5.3.171108
	 */
	protected function get_update_methods($current_version) {
		$update_methods = parent::get_update_methods($current_version);
		$update_steps_count = count($update_methods);

		// If we are going step by step, only return the first element of the steps
		// to perform. At the next iteration, that element will be skipped, and the
		// next one will be returned
		if(empty($_GET['run_all_updates'])) {
			$update_methods = array_slice($update_methods, 0, 1, true);
		}

		$this->running_all_updates = ($update_steps_count === count($update_methods));
		$last_update_method = end($update_methods);
		$this->last_update_method_version = $this->extract_version_from_method($last_update_method);

		if(!empty($update_methods) && !$this->running_all_updates) {
			$this->add_message(E_USER_NOTICE,
												 '<strong>' .
												 __('The Currency Switcher is preparing the data for sales reports.', Definitions::TEXT_DOMAIN) .
												 '</strong>  ' .
												 __('You might see several update messages appearing ' .
														'after opening or refreshing admin pages.', Definitions::TEXT_DOMAIN) .
												 ' ' .
												 __('This is normal. The messages will stop appearing when the ' .
														'update process is completed.', Definitions::TEXT_DOMAIN));
		}
		return $update_methods;
	}
}
