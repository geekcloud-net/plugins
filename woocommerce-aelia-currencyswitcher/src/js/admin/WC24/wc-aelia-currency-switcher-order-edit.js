/**
 * Scripts for Order Edit page.
 *
 * @since 4.5.5.171114
 */
jQuery(document).ready(function($) {
	/**
	 * Adds the currency to a URL.
	 *
	 * @param string url
	 * @param string currency
	 * @return string
	 */
	function add_currency_to_url(url, currency) {
		if(url.indexOf('?admin_currency=') > 0 || url.indexOf('&admin_currency=') > 0) {
			return url.replace(/admin_currency=[^&]+/, 'admin_currency=' + currency);
		}

		if(url.indexOf('?') > 0) {
			return url + '&admin_currency=' + currency;
		}

		return url + '?admin_currency=' + currency;
	}

	var aelia_cs_params = aelia_cs_woocommerce_writepanel_params ||{};
	var currency = aelia_cs_params.order_currency || aelia_cs_params.base_currency;

	if(currency && woocommerce_admin_meta_boxes) {
		// Append the admin currency to the URL used for the order meta boxes. This
		// will allow to override the active currency for all order-specific operations
		woocommerce_admin_meta_boxes.ajax_url = add_currency_to_url(woocommerce_admin_meta_boxes.ajax_url, currency);
	}
});
