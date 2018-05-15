jQuery(document).ready(function($) {
	// Invalidate cache of WooCommerce minicart when Currency changes. This will
	// ensure that the minicart is updated correctly
	try {
		var supports_html5_storage = ('sessionStorage' in window && window['sessionStorage'] !== null);
		if(supports_html5_storage) {
			// The fragment name might be generated dynamically by WooCommerce, so
			// we have to retrieve it from the WC parameters
			// @since WC 3.1
			var fragment_name = 'wc_fragments';
			if(wc_cart_fragments_params && wc_cart_fragments_params.fragment_name) {
				fragment_name = wc_cart_fragments_params.fragment_name;
			}

			$('.widget_wc_aelia_currencyswitcher_widget, .widget_wc_aelia_billing_country_selector_widget').on('submit', 'form', function() {
				sessionStorage.removeItem(fragment_name, '');
			});
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

	// Hide the "Change Currency" button and submit the Widget form when Currency
	// changes
	$('.widget_wc_aelia_currencyswitcher_widget')
		.find('.change_currency')
		.hide()
		.end()
		.on('change', '#aelia_cs_currencies', function(event) {
			var currency_widget_form = $(this).closest('form');
			$(currency_widget_form).submit();
			event.stopPropagation();
			return false;
		});

	// Hide the "Change country" button and submit the Widget form when billing
	// country changes
	$('.currency_switcher.widget_wc_aelia_country_selector_widget')
		.find('.change_country')
		.hide()
		.end()
		.on('change', '.countries', function(event) {
			var widget_form = $(this).closest('form');
			$(widget_form).submit();
			event.stopPropagation();
			return false;
		});

});
