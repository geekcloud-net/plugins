<?php if(!defined('ABSPATH')) exit; // Exit if accessed directly

use Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher;
use Aelia\WC\CurrencySwitcher\Definitions;

// $widget_args is passed when widget is initialised
echo get_value('before_widget', $widget_args);

// This wrapper is needed for widget JavaScript to work correctly
echo '<div class="widget_wc_aelia_currencyswitcher_widget">';

// Title is set in WC_Aelia_CurrencySwitcher_Widget::widget()
$currency_switcher_widget_title = get_value('title', $widget_args);
if(!empty($currency_switcher_widget_title)) {
	echo get_value('before_title', $widget_args);
	echo apply_filters('widget_title', __($currency_switcher_widget_title, $this->text_domain));
	echo get_value('after_title', $widget_args);
}

// Trigger an action to allow rendering elements before the selector form
// (e.g. to show error messages)
// @since 4.5.7.171124
do_action('wc_aelia_cs_widget_before_currency_selector_form', $this);

echo '<!-- Currency Switcher v.' . WC_Aelia_CurrencySwitcher::$version . ' - Currency Selector Widget -->';
echo '<form method="post" class="currency_switch_form">';
echo '<select id="aelia_cs_currencies" name="' . Definitions::ARG_CURRENCY . '">';
foreach($widget_args['currency_options'] as $currency_code => $currency_name) {
	$selected_attr = '';
	if($currency_code === $widget_args['selected_currency']) {
		$selected_attr = 'selected="selected"';
	}
	echo '<option value="' . $currency_code . '" ' . $selected_attr . '>' . $currency_name. '</option>';
}
echo '</select>';

// Display the "change currency" button only when JavaScript is disabled. When it's enabled, selecting a
// currency in the dropdown will automatically trigger the currency switch
echo '<button type="submit" class="button change_currency">' . __('Change Currency', Definitions::TEXT_DOMAIN) . '</button>';
echo '</form>';

echo '</div>';

echo get_value('after_widget', $widget_args);
