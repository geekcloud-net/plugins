jQuery(document).ready(function($) {
	/**
	 * Updates the Price Filter Slider widget to reflect the price ranges using
	 * selected Currency.
	 */
	function update_price_filter_slider() {
		var $ = jQuery;
		if($('.price_slider').length <= 0) {
			return;
		}
		var $price_slider = $('.price_slider');
		var $price_slider_amount = $('.price_slider_amount');

		// Store the Currency used by the Price Filter. This will be used to determine
		// how to convert filter range into base Currency
		var selected_currency = wc_aelia_currency_switcher_params.selected_currency;
		var exchange_rate_from_base = wc_aelia_currency_switcher_params.current_exchange_rate_from_base;

		var price_filter_currency_field = $('<input>')
			.attr('id', 'price_filter_currency')
			.attr('name', 'price_filter_currency')
			.attr('value', selected_currency)
			.hide();
		$('.price_slider_amount').append(price_filter_currency_field);

		// Min and Max prices for the Slider are always in Base Currency and must be converted
		var min_price = $price_slider_amount.find('#min_price').attr('data-min');
		var max_price = $price_slider_amount.find('#max_price').attr('data-max');

		// Convert data-min and data-max to selected currency
		min_price = Math.floor(min_price * exchange_rate_from_base);
		max_price = Math.ceil(max_price * exchange_rate_from_base);

		$price_slider_amount.find('#min_price').attr('data-min', min_price);
		$price_slider_amount.find('#max_price').attr('data-max', max_price);

		// Convert actual ranges
		var current_min_price = parseFloat($price_slider_amount.find('#min_price').val());
		var current_max_price = parseFloat($price_slider_amount.find('#max_price').val());

		if(!isNaN(current_min_price)) {
			// Convert current minimum value to selected currency
			current_min_price = Math.floor(current_min_price * exchange_rate_from_base);
			$price_slider_amount.find('#min_price').val(current_min_price);
		}
		if(!isNaN(current_max_price)) {
			// Convert current maximum value to selected currency
			current_max_price = Math.ceil(current_max_price * exchange_rate_from_base);
			$price_slider_amount.find('#max_price').val(current_max_price);
		}


		if(typeof woocommerce_price_slider_params != 'undefined') {
			// Slider Min and Max values are also in Base Currency
			if(woocommerce_price_slider_params.min_price) {
				woocommerce_price_slider_params.min_price = Math.floor(woocommerce_price_slider_params.min_price * exchange_rate_from_base);
			}
			if(woocommerce_price_slider_params.max_price) {
				woocommerce_price_slider_params.max_price = Math.ceil(woocommerce_price_slider_params.max_price * exchange_rate_from_base);
			}
		}
	}

	update_price_filter_slider();

	/**
	 * Returns the value of a parameter passed via the URL.
	 *
	 * @param string param_name
	 * @return string|false The value of the parameter, or false if the parameter
	 * was not found.
	 * @since 4.4.15.170421
	 */
	function get_url_param(param_name){
		var results = new RegExp('[\?&]' + param_name + '=([^&#]*)').exec(window.location.href);
		return results ? results[1] : false;
	}

	// Check if the currency was set via the URL
	var currency_set_by_url = get_url_param('aelia_cs_currency');

	try {
		// Invalidate cache of WooCommerce minicart when the currency is selected via
		// the URL. This will ensure that the minicart is updated correctly
		var supports_html5_storage = ('sessionStorage' in window && window['sessionStorage'] !== null);
		if(supports_html5_storage && currency_set_by_url) {
			// The fragment name might be generated dynamically by WooCommerce, so
			// we have to retrieve it from the WC parameters
			// @since WC 3.1
			var fragment_name = 'wc_fragments';
			if(wc_cart_fragments_params && wc_cart_fragments_params.fragment_name) {
				fragment_name = wc_cart_fragments_params.fragment_name;
			}

			sessionStorage.removeItem(fragment_name, '');
		}
	}
	catch(exception) {
		var error_msg = 'Aelia - Exception occurred while accessing window.sessionStorage. ' +
										'This could be caused by the browser disabling cookies. ' +
										'COOKIES MUST BE ENABLED for the site to work correctly. ' +
										'Exception details below.';
		console.log(error_msg);
		console.log(exception);
	}
});
