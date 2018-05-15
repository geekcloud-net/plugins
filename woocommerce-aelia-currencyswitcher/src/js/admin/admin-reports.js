/**
 * Scripts related to Reports
 */
jQuery(document).ready(function($) {
	var main = this;
	this.$report_header_elem = $('.wc_aelia_currencyswitcher.report.header');
	if(this.$report_header_elem.length <= 0) {
		return;
	}
	this.params = aelia_cs_reports_params;


	$('#poststuff').before(this.$report_header_elem);
	this.$report_header_elem.show();

	var $currency_field = $('<input type="hidden" id="report_currency" name="report_currency">');
	// Add extra options to the report form
	var $form = $('#poststuff').find('form');
	$form.append($currency_field);

	// Update the form parameters when the options change
	$form.on('submit', function() {
		main.$report_header_elem.find('.options input:checked, .options select').each(function() {
			var $selected_field = $(this);
			var $target_field = $('#' + $selected_field.attr('target_field'));
			if($target_field.length > 0) {
				$target_field.val($selected_field.val());
			}
		})
	});

	$('#poststuff').find('.stats_range ul li > a, .chart-sidebar .chart-widget a').on('click', function(e) {
		var url_params = {};
		main.$report_header_elem.find('.options input:checked, .options select').each(function() {
			var $option = $(this);
			url_params[$option.attr('name')] = $option.val();
		});

		var url =	$.param.querystring($(this).attr('href'), url_params);
		window.location = url;
		return false;
	});

	$('#aelia_cs_report_header').find('.tips').on('click', function() {
		var $more_info_target = $('#' + $(this).attr('more_info_target'));
		if($more_info_target.length > 0) {
			$more_info_target.slideToggle();
		}
	});
});
