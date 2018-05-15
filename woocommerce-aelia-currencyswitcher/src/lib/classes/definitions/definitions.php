<?php
namespace Aelia\WC\CurrencySwitcher;
if(!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Implements a base class to store and handle the messages returned by the
 * plugin. This class is used to extend the basic functionalities provided by
 * standard WP_Error class.
 */
class Definitions {
	// @var string The menu slug for plugin's settings page.
	const MENU_SLUG = 'aelia_cs_options_page';
	// @var string The plugin slug
	const PLUGIN_SLUG = 'woocommerce-aelia-currencyswitcher';
	// @var string The plugin text domain
	const TEXT_DOMAIN = 'woocommerce-aelia-currencyswitcher';

	// Get/Post Arguments
	const ARG_CURRENCY = 'aelia_cs_currency';
	const ARG_PRICE_FILTER_CURRENCY = 'price_filter_currency';
	const ARG_FORCE_ALL_UPDATES = 'aelia_cs_fau';
	const ARG_DEBUG_GEOLOCATION_DETECTION = 'aelia_cs_dgd';

	const ARG_CHECKOUT_BILLING_COUNTRY = 'country';
	const ARG_CHECKOUT_SHIPPING_COUNTRY = 's_country';
	const ARG_CUSTOMER_COUNTRY = 'aelia_customer_country';
	const ARG_REPORT_CURRENCY = 'report_currency';

	// The currency used during an admin operation
	// @since 4.5.5.171114
	const ARG_ADMIN_CURRENCY = 'admin_currency';

	/**
	 * Obsolete key. Used to store customer's country.
	 * @var string
	 * @deprecated since 4.0.0.150311. Replaced by ARG_CUSTOMER_COUNTRY.
	 */
	const ARG_BILLING_COUNTRY = 'aelia_billing_country';

	// Defaults
	const DEF_REPORT_CURRENCY = 'base';

	// Session constants
	const SESSION_CUSTOMER_COUNTRY = 'aelia_customer_country';
	/**
	 * Obsolete key. Used to store customer's country.
	 * @var string
	 * @deprecated since 4.0.0.150311. Replaced by SESSION_CUSTOMER_COUNTRY.
	 */
	const SESSION_BILLING_COUNTRY = 'aelia_billing_country';

	// Slugs
	const SLUG_OPTIONS_PAGE = 'aelia_cs_options_page';

	// Error codes
	const OK = 0;
	const ERR_FILE_NOT_FOUND = 1100;
	const ERR_INVALID_CURRENCY = 1101;
	const ERR_MISCONFIGURED_CURRENCIES = 1102;
	const ERR_INVALID_SOURCE_CURRENCY = 1103;
	const ERR_INVALID_DESTINATION_CURRENCY = 1104;
	const ERR_INVALID_TEMPLATE = 1105;
	const ERR_INVALID_WIDGET_CLASS = 1106;
	const ERR_MANUAL_CURRENCY_SELECTION_DISABLED = 1107;

	const WARN_DYNAMIC_PRICING_INTEGRATION = 2001;
	const NOTICE_INTEGRATION_ADDONS = 2002;
	const WARN_YAHOO_FINANCE_DISCONTINUED = 2003;

	// Session/User Keys
	const USER_CURRENCY = 'aelia_cs_selected_currency';
	const RECALCULATE_CART_TOTALS = 'aelia_cs_recalculate_cart_totals';

	// Transients
	const TRANSIENT_SALES_CURRENCIES = 'aelia_cs_sales_currencies';
}
