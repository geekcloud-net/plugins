/**
 * Scripts for Product Edit page.
 *
 * @since WooCommerce 2.4
 * @since 4.2.5.150907
 */
jQuery(document).ready(function($) {
	/**
	 * Prompts for a price and sets it on all variations.
	 *
	 * @param string price_type The price type (regular or sale).
	 * @param string currency The currency for which the price has been specified.
	 * @return object An object containing the new variation prices to be applied.
	 * @since 3.8.5.150907
	 */
	function NewVariationPrice(price_type, currency) {
		var data = {
			'currency': currency,
			'price_type': price_type
		};
		data.value = window.prompt(woocommerce_admin_meta_boxes_variations.i18n_enter_a_value);
		return data;
	}

	// Set JS hooks to bulk-set prices for variations
	var enabled_currencies = aelia_cs_woocommerce_writepanel_params['enabled_currencies'] || [];
	for(var idx = 0; idx < enabled_currencies.length; idx++) {
		var currency = enabled_currencies[idx];
		// No need to add an option for the base currency, it already exists in standard WooCommerce menu
		if(currency == aelia_cs_woocommerce_writepanel_params['base_currency']) {
			continue;
		}

		// Hook for variations regular prices
		$('select#field_to_edit').on('variable_regular_currency_prices_' + currency + '_ajax_data', function(elem, data) {
			return NewVariationPrice('regular', $(this).find("option:selected").attr('currency'));
		});
		// Hook for variations sale prices
		$('select#field_to_edit').on('variable_sale_currency_prices_' + currency + '_ajax_data', function() {
			return NewVariationPrice('sale', $(this).find("option:selected").attr('currency'));
		});
	}
});
