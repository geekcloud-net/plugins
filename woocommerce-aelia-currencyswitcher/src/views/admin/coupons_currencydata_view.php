<?php if(!defined('ABSPATH')) exit; // Exit if accessed directly

use Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher;
use Aelia\WC\CurrencySwitcher\WC_Aelia_Currencies_Manager;

$currencyprices_manager = WC_Aelia_CurrencySwitcher::instance()->currencyprices_manager();
$enabled_currencies = $currencyprices_manager->enabled_currencies(false);
$base_currency = $currencyprices_manager->base_currency();
$text_domain = WC_Aelia_CurrencySwitcher::$text_domain;

global $post;
$coupon_data = !empty($post) ? get_post_meta($post->ID, WC_Aelia_CurrencyPrices_Manager::FIELD_COUPON_CURRENCY_DATA, true) : array();

?>
<div id="multi_currency_coupon_data" class="panel woocommerce_options_panel">
	<p><?php
		echo __('Here you can configure the coupon parameters for each of the ' .
						'available currencies. If you leave the fields empty, their value '.
						'will be populated automatically, by converting the default values ' .
						'entered in "General" and "Usage Restrictions" tabs.', $text_domain);
	?></p>
	<p><?php
		echo __('Please note that the coupon amounts you enter for each currency will ' .
						'apply whether you are configuring a fixed price or a percentage coupon. ' .
						'For example, you could add a discount of 10% when the currency is USD, ' .
						'and 15% when the currency is EUR. The Currency Switcher will take care ' .
						'of selecting the appropriate amount when the coupon is applied to the ' .
						'cart and to the order.', $text_domain);
	?></p>
	<?php
	foreach($enabled_currencies as $currency) {
		$currency_data = get_value($currency, $coupon_data, array());
		?>
		<div class="section">
			<h3 class="title"><?php
				echo sprintf('%s (%s)',
										 WC_Aelia_Currencies_Manager::get_currency_name($currency),
										 $currency);
			?></h3>
		<?php
			woocommerce_wp_text_input(array(
				'id' => sprintf('_coupon_currency_data[%s][coupon_amount]', $currency),
				'label' => __('Coupon amount', $text_domain),
				'placeholder' => __('Auto', $text_domain),
				'description' => __('Value of the coupon.', $text_domain),
				'data_type' => 'price',
				'desc_tip' => true,
				'value' => get_value('coupon_amount', $currency_data),
			));
			woocommerce_wp_text_input(array(
				'id' => sprintf('_coupon_currency_data[%s][minimum_amount]', $currency),
				'label' => __('Minimum spend', $text_domain),
				'placeholder' => __('Auto', $text_domain),
				'description' => __('This field allows you to set the minimum subtotal needed to use the coupon.', $text_domain),
				'data_type' => 'price',
				'desc_tip' => true,
				'value' => get_value('minimum_amount', $currency_data),
			));
			woocommerce_wp_text_input(array(
				'id' => sprintf('_coupon_currency_data[%s][maximum_amount]', $currency),
				'label' => __('Maximum spend', $text_domain),
				'placeholder' => __('Auto', $text_domain),
				'description' => __('This field allows you to set the maximum subtotal needed to use the coupon.', $text_domain),
				'data_type' => 'price',
				'desc_tip' => true,
				'value' => get_value('maximum_amount', $currency_data),
			));
		?></div>

		<?php
	}
	?>
</div>
