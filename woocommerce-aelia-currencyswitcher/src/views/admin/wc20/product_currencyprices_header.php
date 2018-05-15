<?php if(!defined('ABSPATH')) exit; // Exit if accessed directly

use Aelia\WC\CurrencySwitcher\Definitions;
use Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher;
//use \WC_Aelia_CurrencyPrices_Manager;

// This view is designed to be loaded by an instance of
// WC_Aelia_CurrencyPrices_Manager. Such instance is what "$this" and "self"
// refer to.
$currencyprices_manager = $this;
$post_id = $currencyprices_manager->current_post->ID;

// WooCommerce 2.2 and earlier contain bugs that don't allow to set the base
// currency for variations. On those versions, the feature is disabled to
// prevent issues
$show_variation_product_base_currency = aelia_wc_version_is('>=', '2.3');
?>
<div class="product_base_currency">
	<?php if(!$show_variation_product_base_currency):	?>
	<div class="feature_disabled show_if_variable">
		<h3>
			<?php
				$tooltip = __('Due to current restrictions in the architecture of WooCommerce ' .
											'2.2 and earlier, the <em>Product Base Currency</em> feature ' .
											'cannot work properly with variable products. To enable it, ' .
											'please upgrade WooCommerce to version 2.3 or later.',
											Definitions::TEXT_DOMAIN);

				echo __('Product Base Currency is disabled for variations', Definitions::TEXT_DOMAIN);
			?>
			<a href="#" class="tips" data-tip="<?php esc_attr_e($tooltip); ?>">[?]</a>
		</h3>
		<div class="description"><?php
			echo sprintf(__('The base currency for this variation will be set to %s.',
											Definitions::TEXT_DOMAIN),
									 $base_currency);
			?>
			<p><?php
			echo __('<strong>Note</strong>: such feature is still available for simple products.',
							Definitions::TEXT_DOMAIN);
			?></p>
		</div>
	</div>
	<?php endif; ?>
	<div class="<?php echo $show_variation_product_base_currency ? '' : 'show_if_simple'; ?>">
		<?php
			$product_base_currency_field = WC_Aelia_CurrencyPrices_Manager::FIELD_PRODUCT_BASE_CURRENCY;
			if(isset($currencyprices_manager->loop_idx)) {
				$product_base_currency_field .= "[$loop]";
			}

			$currency_options = array();
			$wc_currencies = get_woocommerce_currencies();
			foreach($enabled_currencies as $currency) {
				$currency_options[$currency] = $wc_currencies[$currency];
			}

			woocommerce_wp_select(array(
				'id' => $product_base_currency_field,
				'class' => '',
				'label' => __('Product base currency', Definitions::TEXT_DOMAIN),
				'value' => $currencyprices_manager->get_product_base_currency($post_id),
				'options' => $currency_options,
				'custom_attributes' => array(),
			));
		?>
		<div class="description"><?php
			echo __('Choose which currency should be used as the base currency for ' .
							'the product. This will be the currency from which all other prices will be ' .
							'calculated when left empty.',
							Definitions::TEXT_DOMAIN);
			?>
			<div class="warning"><?php
				echo __('<span class="important">Important: make sure that you enter product prices in the selected ' .
								'currency</span>. If the prices in product base currency are left empty, ' .
								'this setting will have no effect and the default base price (above) will be taken instead.',
								Definitions::TEXT_DOMAIN);
			?></div>
		</div>
	</div>
</div> <!-- Product base currency section - END -->
<div class="product_currency_prices_header">
	<h3><?php
		echo __('Price in specific currencies', Definitions::TEXT_DOMAIN);
	?></h3>
	<div>
		<div class="description"><?php
			echo __('Here you can manually specify prices for specific Currencies. If you do so, the prices ' .
							'entered will be used instead of converting the base price using exchange rates. To use ' .
							'exchange rates for one or more prices, simply leave the field empty (the "Auto" value will ' .
							'appear to indicate that price for that currency will be calculated automatically).',
						 Definitions::TEXT_DOMAIN);
		?></div>
	</div>
</div>
