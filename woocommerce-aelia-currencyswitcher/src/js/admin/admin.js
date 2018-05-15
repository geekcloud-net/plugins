jQuery(document).ready(function($) {
	/**
	 * Displays the configuration section related to the selected Exchange Rates
	 * provider.
	 *
	 * @param string provider_id The ID of the selected Provider.
	 */
	function show_exchange_rates_provider_section(provider_id) {
		$('.exchange_rate_model_settings').each(function() {
			var anchor_element = $(this);

			/* Hiding and showing of Providers' specific sections requires a bit of a
			 * hack. WordPress renders sections without wrapping them in a DIV, or any
			 * container of sort. Therefore, to hide a section, it's necessary to
			 * identify one element within it, and then hide the other elements belonging
			 * to the same section. In this case, the key element is a paragraph added
			 * immediately after the section title.
			 * Once such key element is found, all we have to do is hiding the previous
			 * sibling (section title) and the next sibling (form-table).
			 */
			if(anchor_element.hasClass(provider_id)) {
				anchor_element
					.show()
					.prev().show().end()
					.next().show();
			}
			else {
				anchor_element
					.hide()
					.prev().hide().end()
					.next().hide();
			}
		});
	}

	var $wc_aelia_currency_switcher_form = $('#wc_aelia_currency_switcher_form');

	// Display tabbed interface
	$wc_aelia_currency_switcher_form.find('.tabs').tabs();

	// Use Chosen plugin to replace standard multiselect
	if(jQuery().chosen) {
		// Multiselect for enabled currencies
		$wc_aelia_currency_switcher_form
			.find('.enabled_currencies')
			.chosen();

		// Multiselect for payment gateways enabled for each currency
		$wc_aelia_currency_switcher_form
			.find('.currency_payment_gateways')
			.chosen();
	}

	// Add event handler on "Set All to Manual" checkbox
	var $exchange_rates_settings_table = $('#exchange_rates_settings');
	$exchange_rates_settings_table.on('click', '.set_manually .select_all', function() {
		$exchange_rates_settings_table
			.find('.exchange_rate_set_manually')
			.prop('checked', true);
	});
	$exchange_rates_settings_table.on('click', '.set_manually .deselect_all', function() {
		$exchange_rates_settings_table
			.find('.exchange_rate_set_manually')
			.prop('checked', false);
	});

	// Add event handler on Exchange Provider dropdown, to only display sections
	// related to it
	var $selected_exchange_rates_provider = $('#wc_aelia_currency_switcher\\[exchange_rates_provider\\]').val();
	show_exchange_rates_provider_section($selected_exchange_rates_provider);
	$('#wc_aelia_currency_switcher\\[exchange_rates_provider\\]').change(function() {
		show_exchange_rates_provider_section($(this).val());
	});

	// Open tooltips on click
	$('#wc_aelia_currency_switcher_form .help-icon')
  .tooltip({
    content: function() {
			return $(this).attr('tooltip');
		},
    items: 'span'
    })
  .off('mouseover')
  .on('click', function(){
      $(this).tooltip('open');
      return false;
    })
  .attr('tooltip', function() {
		var value = $(this).attr('title');
		$(this).attr('title', '');
		return value;
	})
	.css({ cursor: 'pointer' });

// Allow to sort the elements
	$('#exchange_rates_settings tbody').sortable({
		handle: '.sort',
		items: 'tr',
		cursor: 'move',
		axis: 'y',
		scrollSensitivity:40,
		forcePlaceholderSize: true,
		helper: 'clone',
		opacity: 0.65,
		placeholder: 'wc-metabox-sortable-placeholder',
		start:function(event,ui){
			ui.item.css('background-color','#f6f6f6');
		},
		stop:function(event,ui){
			ui.item.removeAttr('style');
		}
	});
});
