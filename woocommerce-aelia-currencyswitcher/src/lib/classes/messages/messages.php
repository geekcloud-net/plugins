<?php
namespace Aelia\WC\CurrencySwitcher;
if(!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Stores and handles the messages returned by the EDD Currency Switcher plugin.
 */
class Messages extends \Aelia\WC\Messages {
	/**
	 * Loads all the error message used by the plugin.
	 */
	public function load_error_messages() {
		parent::load_error_messages();

		$settings_page_url = admin_url('admin.php?page=' . Definitions::MENU_SLUG);
		$support_url = 'https://aelia.co/contact';

		$this->add_error_message(Definitions::ERR_FILE_NOT_FOUND, __('File not found: "%s".', Definitions::TEXT_DOMAIN));
		$this->add_error_message(Definitions::ERR_INVALID_CURRENCY, __('Currency not valid: "%s".', Definitions::TEXT_DOMAIN));
		$this->add_error_message(Definitions::ERR_MISCONFIGURED_CURRENCIES,
														 __('One or more Currencies are not configured correctly (e.g. ' .
																'exchange rates may be missing, incorrect, or set to zero).', Definitions::TEXT_DOMAIN) .
																' ' .
																sprintf(__('Please <a href="%1$s">check the Currency Switcher settings</a> '.
																					 'and make sure that all enabled currencies have been configured ' .
																					 'correctly.', Definitions::TEXT_DOMAIN),
																				$settings_page_url) .
																' ' .
																sprintf(__('If the issue persists, please <a href="%1$s">contact the Aelia ' .
																					 'Support Team</a>.', Definitions::TEXT_DOMAIN),
																				$support_url));
		$this->add_error_message(Definitions::ERR_INVALID_SOURCE_CURRENCY,
														 __('Currency Conversion - Source Currency not valid or exchange rate ' .
																'not found for: "%s". Please make sure that the Currency '.
																'Switcher plugin is configured correctly and that an Exchange ' .
																'Rate has been specified for each of the enabled currencies.',
																Definitions::TEXT_DOMAIN));
		$this->add_error_message(Definitions::ERR_INVALID_DESTINATION_CURRENCY,
														 __('Currency Conversion - Destination Currency not valid or exchange rate ' .
																'not found for: "%s". Please make sure that the Currency '.
																'Switcher plugin is configured correctly and that an Exchange ' .
																'Rate has been specified for each of the enabled currencies.',
																Definitions::TEXT_DOMAIN));
		$this->add_error_message(Definitions::ERR_INVALID_TEMPLATE,
														 __('Rendering - Requested template could not be found in either plugin\'s ' .
																'folders, nor in your theme. Plugin slug: "%s". Template name: "%s".'.
																Definitions::TEXT_DOMAIN));
		$this->add_error_message(Definitions::ERR_MANUAL_CURRENCY_SELECTION_DISABLED,
														 sprintf(__('The option "force currency by country" is enabled in the ' .
																				'<a href="%1$s">Currency Switcher settings</a>.', Definitions::TEXT_DOMAIN),
																		 $settings_page_url) .
														 ' ' .
														 __('Due to that, the currency selection performed using this widget ' .
																'will have no effect.', Definitions::TEXT_DOMAIN) .
														 ' ' .
														 __('If you wish to enable the manual selection of the currency ' .
																'using this widget, please disable the "force currency by country" ' .
																'option.', Definitions::TEXT_DOMAIN));

		// Add message to inform merchants about the new Dynamic Pricing addon
		$this->add_message(
			Definitions::WARN_DYNAMIC_PRICING_INTEGRATION,
			'<strong>' .
			__('Changes in the integration with the WooCommerce Dynamic Pricing plugin', Definitions::TEXT_DOMAIN) .
			'</strong><br>' .
			__('The Aelia Currency Switcher no longer includes an integration with the WooCommerce ' .
				 'Dynamic Pricing plugin.', Definitions::TEXT_DOMAIN) .
			' ' .
			sprintf(__('The integration is now in a separate plugin, which you can ' .
								 'get from our website, free of charge: <a target="_blank" href="%1$s">%1$s</a>.',
								 Definitions::TEXT_DOMAIN),
							'https://aelia.co/shop/woocommerce-dynamic-pricing-integration-currency-switcher/') .
			'<br><br>' .
			__('If you are not using the Dynamic Pricing plugin, please disregard this message. Thanks.',
				 Definitions::TEXT_DOMAIN)
		);

		// Add message to inform merchants that Yahoo! Finance has been discontinued
		// @since 4.5.14.180122
		$this->add_message(
			Definitions::WARN_YAHOO_FINANCE_DISCONTINUED,
			'<strong>' .
			__('The Yahoo! Finance service has been discontinued', Definitions::TEXT_DOMAIN) .
			'</strong><br>' .
			' ' .
			__('The Yahoo! Finance is no longer available, and can no longer be used to fetch ' .
				 'exchange rates.', Definitions::TEXT_DOMAIN) .
			' ' .
			sprintf(__('If you are using Yahoo! Finance to update the exchange rates, please ' .
								 '<a href="%1$s">review your Currency Switcher settings</a> and ' .
								 'select another provider.',
								 Definitions::TEXT_DOMAIN),
							$settings_page_url) .
			' ' .
			sprintf(__('Open Exchange Rates is an excellent alternative. You can get a ' .
								 'free API key for their service here: ' .
								 '<a href="%1$s" target="_blank">%1$s</a>.',
								 Definitions::TEXT_DOMAIN),
							'https://openexchangerates.org/signup/free') .
			'<br><br>' .
			__('If you are not using Yahoo! Finance as the exchange rates provider, please disregard this message. Thanks.',
				 Definitions::TEXT_DOMAIN)
		);

		// Add message to inform customers about the integration addons
		$this->add_message(
			Definitions::NOTICE_INTEGRATION_ADDONS,
			'<strong>' .
			sprintf(__('Are you using 3rd party plugins such as <a target="_blank" href="%s">Bundles</a>, ' .
								 '<a target="_blank" href="%s">Dynamic Pricing</a>, <a target="_blank" href="%s">Composite Products</a>, ' .
								 '<a target="_blank" href="%s">Product Add-ons </a>, <a target="_blank" href="%s">Subscriptions</a>?',
								 Definitions::TEXT_DOMAIN),
							'https://woocommerce.com/products/product-bundles/?ref=108',
							'https://woocommerce.com/products/composite-products/?ref=108',
							'https://woocommerce.com/products/dynamic-pricing/?ref=108',
							'https://woocommerce.com/products/product-add-ons/?ref=108',
							'https://woocommerce.com/products/woocommerce-subscriptions/?ref=108'
						 ) .
			'</strong><br>' .
			__('The listed plugins are not yet compatible with multi-currency sites, out of the box.', Definitions::TEXT_DOMAIN) .
			' ' .
			__('We are keeping in contact with their developers, to encourage them to add native ' .
				 'multi-currency support to their plugins as soon as possible.', Definitions::TEXT_DOMAIN) .
			'<br /><br />' .
			__('In the meantime, we developed some integration add-ons that will work as a ' .
				 '"<em><strong>temporary </strong>patch</em>", and compensate for the lack ' .
				 'of multi-currency capabilities in 3rd party plugins.', Definitions::TEXT_DOMAIN) .
			' ' .
			sprintf(__('You can find the addons on our website: <a target="_blank" href="%s">Aelia Currency ' .
								 'Switcher Addons</a>.', Definitions::TEXT_DOMAIN),
							'https://aelia.co/shop/product-category/woocommerce-currency-switcher-addons/') .
			'<br /><br />' .
			__('If you are not planning to use any of the listed addons, you can disregard this message. Thanks.',
				 Definitions::TEXT_DOMAIN)
		);
	}
}
