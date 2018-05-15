<?php
namespace Aelia\WC\CurrencySwitcher;
if(!defined('ABSPATH')) exit; // Exit if accessed directly

use Aelia\WC\IP2Location;

/**
 * Implements a class that will render the settings page.
 */
class Settings_Renderer extends \Aelia\WC\Settings_Renderer {
	// @var string The URL to the support portal.
	const SUPPORT_URL = 'https://aelia.freshdesk.com/support/home';
	// @var string The URL to the contact form for general enquiries.
	const CONTACT_URL = 'https://aelia.co/contact/';

	/*** Settings Tabs ***/
	const TAB_GENERAL = 'general';
	const TAB_GEOLOCATION = 'geolocation';
	const TAB_PAYMENT_GATEWAYS = 'paymentgateways';
	const TAB_CURRENCY_SELECTION = 'currencyselection';
	const TAB_DOCUMENTATION = 'documentation';
	const TAB_SUPPORT = 'support';

	/*** Settings sections ***/
	// @var string The ID of the Section containing Enabled Currencies settings
	const SECTION_CURRENCIES = 'currencies_section';
	// @var string The ID of the Section containing Exchange Rates settings
	const SECTION_EXCHANGE_RATES = 'exchange_rates_section';
	// @var string The ID of the Section containing Exchange Rates Auto Update settings
	const SECTION_EXCHANGE_RATES_UPDATE = 'exchange_rates_section_update';

	const SECTION_OPENEXCHANGERATES_SETTINGS = 'openexchangerates_section';
	const SECTION_IPGEOLOCATION_SETTINGS = 'ipgeolocation_section';
	const SECTION_PAYMENT_GATEWAYS_SETTINGS = 'paymentgateways_section';
	const SECTION_CURRENCY_SELECTION_WIDGETS = 'currencyselection_widgets_section';
	const SECTION_USAGE = 'usage_section';
	const SECTION_SUPPORT = 'support_section';

	/**
	 * Event handler, fired when setting page is loaded.
	 */
	public function options_page_load() {
		if(!WC_Aelia_CurrencySwitcher::doing_ajax() && get_value('settings-updated', $_GET)) {
      // Plugin settings have been saved. Display a message, or do anything you like.
			do_action('wc_aelia_currencyswitcher_settings_saved');
		}
	}

	/**
	 * Sets the tabs to be used to render the Settings page.
	 */
	protected function add_settings_tabs() {
		// General settings
		$this->add_settings_tab($this->_settings_key,
														self::TAB_GENERAL,
														__('General', $this->_textdomain));
		// Geolocation settings
		$this->add_settings_tab($this->_settings_key,
														self::TAB_GEOLOCATION,
														__('Geolocation', $this->_textdomain));
		// Payment gateways filtering settings
		$this->add_settings_tab($this->_settings_key,
														self::TAB_PAYMENT_GATEWAYS,
														__('Payment Gateways', $this->_textdomain));
		// Currency selection settings
		$this->add_settings_tab($this->_settings_key,
														self::TAB_CURRENCY_SELECTION,
														__('Currency Selection', $this->_textdomain));
		// Documentation tab
		$this->add_settings_tab($this->_settings_key,
														self::TAB_DOCUMENTATION,
														__('Documentation', $this->_textdomain));
		// Support tab
		$this->add_settings_tab($this->_settings_key,
														self::TAB_SUPPORT,
														__('Support', $this->_textdomain));
	}

	/**
	 * Configures the plugin settings sections.
	 */
	protected function add_settings_sections() {
		// Add Currencies section
		$this->add_settings_section(
				self::SECTION_CURRENCIES,
				__('Enabled Currencies', $this->_textdomain),
				array($this, 'enabled_currencies_section_callback'),
				$this->_settings_key,
				self::TAB_GENERAL
		);

		// Add Exchange Rates section
		$this->add_settings_section(
				self::SECTION_EXCHANGE_RATES,
				__('Currency Settings', $this->_textdomain),
				array($this, 'exchange_rates_settings_section_callback'),
				$this->_settings_key,
				self::TAB_GENERAL
		);

		// Add Exchange Rates section
		$this->add_settings_section(
				self::SECTION_EXCHANGE_RATES_UPDATE,
				__('Exchange Rates - Automatic Update Settings', $this->_textdomain),
				array($this, 'exchange_rates_update_section_callback'),
				$this->_settings_key,
				self::TAB_GENERAL
		);

		// Add Exchange Rates section
		$this->add_settings_section(
				self::SECTION_OPENEXCHANGERATES_SETTINGS,
				__('Open Exchange Rates Settings', $this->_textdomain),
				array($this, 'openexchangerates_settings_section_callback'),
				$this->_settings_key,
				self::TAB_GENERAL
		);

		// Add IP Geolocation section
		$this->add_settings_section(
				self::SECTION_IPGEOLOCATION_SETTINGS,
				__('Currency Geolocation Settings', $this->_textdomain),
				array($this, 'ipgeolocation_settings_section_callback'),
				$this->_settings_key,
				self::TAB_GEOLOCATION
		);

		// Add Payment Gateways section
		$this->add_settings_section(
				self::SECTION_PAYMENT_GATEWAYS_SETTINGS,
				__('Payment Gateways Settings', $this->_textdomain),
				array($this, 'paymentgateways_settings_section_callback'),
				$this->_settings_key,
				self::TAB_PAYMENT_GATEWAYS
		);

		// Add Currency Selection section
		$this->add_settings_section(
				self::SECTION_CURRENCY_SELECTION_WIDGETS,
				__('Currency selection widgets', $this->_textdomain),
				array($this, 'currencyselection_widgets_settings_section_callback'),
				$this->_settings_key,
				self::TAB_CURRENCY_SELECTION
		);

		// Add Documentation section
		$this->add_settings_section(
				self::SECTION_USAGE,
				'', // Title is not needed in this case, it will be displayed in the section callback
				array($this, 'usage_section_callback'),
				$this->_settings_key,
				self::TAB_DOCUMENTATION
		);

		// Add Support section
		$this->add_settings_section(
				self::SECTION_SUPPORT,
				__('Support Information', $this->_textdomain),
				array($this, 'support_section_callback'),
				$this->_settings_key,
				self::TAB_SUPPORT
		);
	}

	/**
	 * Transforms an array of currency codes into an associative array of
	 * currency code => currency description entries. Currency labels are retrieved
	 * from the list of currencies available in WooCommerce.
	 *
	 * @param array currencies An array of currency codes.
	 * @return array
	 */
	protected function add_currency_labels(array $currencies) {
		$woocommerce_currencies = get_woocommerce_currencies();
		$result = array();
		foreach($currencies as $currency_code) {
			$result[$currency_code] = get_value($currency_code,
																					$woocommerce_currencies,
																					sprintf(__('Label not found for currency "%s"', $this->_textdomain),
																									$currency_code));
		}

		return $result;
	}

	/**
	 * Configures the plugin settings fields.
	 */
	protected function add_settings_fields() {
		// Load currently enabled currencies. WooCommerce default currency is
		// forcibly enabled
		$enabled_currencies = array_unique(array_merge($this->_settings_controller->get_enabled_currencies(),
																									 array($this->_settings_controller->base_currency())));

		//var_dump($enabled_currencies);die();

		// Add "Enabled Currencies" field
		$enabled_currencies_field_id = Settings::FIELD_ENABLED_CURRENCIES;
		$this->render_dropdown_field(self::SECTION_CURRENCIES,
																 Settings::FIELD_ENABLED_CURRENCIES,
																 __('Select the Currencies that you would like to accept. ' .
																		'After saving, the options for the exchange rates will ' .
																		'be displayed.', $this->_textdomain),
																 $this->_settings_controller->woocommerce_currencies(),
																 sprintf(__('<strong>Note</strong>: WooCommerce base currency (%s) ' .
																						'will be enabled automatically.',
																						$this->_textdomain),
																				 $this->_settings_controller->base_currency()),
																 '',
																 array('multiple' => 'multiple'));


		// Prepare fields to display the Exchange Rate options for each selected currency
		$exchange_rates_field_id = Settings::FIELD_EXCHANGE_RATES;
		$exchange_rates = $this->current_settings($exchange_rates_field_id, $this->default_settings($exchange_rates_field_id, array()));
		// Add "Exchange Rates" table
		add_settings_field(
			$exchange_rates_field_id,
			__('Set the Exchange Rates for each Currency.', $this->_textdomain),
			array($this, 'render_exchange_rates_options'),
			$this->_settings_key,
			self::SECTION_EXCHANGE_RATES,
			array(
				'settings_key' => $this->_settings_key,
				'enabled_currencies' => $enabled_currencies,
				'exchange_rates' => $exchange_rates,
				'id' => $exchange_rates_field_id,
				'label_for' => $exchange_rates_field_id,
				// Input field attributes
				'attributes' => array(
					'class' => $exchange_rates_field_id,
				),
			)
		);

		$this->render_checkbox_field(
			self::SECTION_EXCHANGE_RATES_UPDATE,
			Settings::FIELD_EXCHANGE_RATES_UPDATE_ENABLE,
	    __('Tick this box to enable automatic updating of exchange rates.', $this->_textdomain),
			'',
			''
		);

		// Add "Exchange Rates Schedule" field
		// Retrieve the timestamp of next scheduled Exchange Rates update
		if(wp_get_schedule($this->_settings_controller->exchange_rates_update_hook()) === false) {
			$next_update_schedule = __('Not Scheduled', $this->_textdomain);
		}
		else {
			$next_update_schedule = date_i18n(get_datetime_format(), wp_next_scheduled($this->_settings_controller->exchange_rates_update_hook()));
		}

		// Retrieve the timestamp of last update
		if(($last_update_timestamp = $this->current_settings(Settings::FIELD_EXCHANGE_RATES_LAST_UPDATE)) != null) {
			$last_update_timestamp_fmt = date_i18n(get_datetime_format(), $last_update_timestamp);
		}
		else {
			$last_update_timestamp_fmt = __('Never updated', $this->_textdomain);
		}

		// Prepare select to allow choosing how often to update the exchange rates
		$this->render_dropdown_field(self::SECTION_EXCHANGE_RATES_UPDATE,
																 Settings::FIELD_EXCHANGE_RATES_UPDATE_SCHEDULE,
																 __('Select how often you would like to update the exchange rates.', $this->_textdomain),
																 $this->_settings_controller->get_schedule_options(),
																 sprintf(__('Last update: <span id="last_exchange_rates_update">' .
																						'%s</span>.', $this->_textdomain),
																				 $last_update_timestamp_fmt) .
																 '</p><p>' .
																 sprintf(__('Next update: <span id="next_exchange_rates_update">' .
																						'%s</span>.',
																						$this->_textdomain),
																				 $next_update_schedule) .
																 '</p>',
																 '');

		// Load available Exchange Rates models
		$exchange_rates_providers = $this->_settings_controller->exchange_rates_providers_options();
		asort($exchange_rates_providers);

		// Add "Exchange Rates Providers" field
		$exchange_rates_provider_field_id = Settings::FIELD_EXCHANGE_RATES_PROVIDER;

		$exchange_rates_keys = array_keys($exchange_rates_providers);
		$default_selected_provider = array_shift($exchange_rates_keys);
		$selected_provider = $this->current_settings($exchange_rates_provider_field_id, $default_selected_provider);

		// Prepare multi-select to allow choosing the Exchange Rates Provider
		add_settings_field(
			$exchange_rates_provider_field_id,
	    __('Select the Provider from which the exchange rates will be fetched.',
				 $this->_textdomain),
	    array($this, 'render_dropdown'),
	    $this->_settings_key,
	    self::SECTION_EXCHANGE_RATES_UPDATE,
	    array(
				'settings_key' => $this->_settings_key,
				'id' => $exchange_rates_provider_field_id,
				'label_for' => $exchange_rates_provider_field_id,
				'options' => $exchange_rates_providers,
				'selected' => $selected_provider,
				// Input field attributes
				'attributes' => array(
					'class' => $exchange_rates_provider_field_id,
				),
			)
		);

		/*** Settings for Open Exchange Rates ***/
		$this->render_text_field(
			self::SECTION_OPENEXCHANGERATES_SETTINGS,
			Settings::FIELD_OPENEXCHANGE_API_KEY,
	    __('Open Exchange Rates API Key', $this->_textdomain),
			'<strong>' .
			__('An API key is required to use the Open Exchange Rates service.', $this->_textdomain) .
			'</strong></br>' .
			sprintf(__('If you do not have an API Key, please visit <a href="%1$s">%1$s</a> ' .
								 'to register and get a free one.', $this->_textdomain),
							'https://openexchangerates.org/signup') .
			'<br/>' .
			__('Alternatively, please select a different exchange rates provider, ' .
				 'such as Yahoo! Finance, which does not require an API key.', $this->_textdomain),
			''
		);

		/*** IP Geolocation API Settings ***/
		$this->render_checkbox_field(
			self::SECTION_IPGEOLOCATION_SETTINGS,
			Settings::FIELD_IPGEOLOCATION_ENABLED,
	    __('Enable automatic selection of Currency depending on Visitors\' location.', $this->_textdomain)
		);

		// Add "Enabled Currencies" field
		$this->render_dropdown_field(
			self::SECTION_IPGEOLOCATION_SETTINGS,
			Settings::FIELD_IPGEOLOCATION_DEFAULT_CURRENCY,
			__('Default Geolocation currency', $this->_textdomain),
			$this->add_currency_labels($enabled_currencies),
	    __('Select the currency to use by default when a visitor comes from a ' .
				 'country whose currency is not supported by your site, or when ' .
				 'geolocation resolution fails.', $this->_textdomain)
		);

		/*** Payment Gateways Settings ***/
		// Prepare fields to display the Exchange Rate options for each selected currency
		$payment_gateways_field_id = Settings::FIELD_PAYMENT_GATEWAYS;
		//$payment_gateways = $this->current_settings($payment_gateways_field_id, $this->default_settings($payment_gateways_field_id, array()));
		$payment_gateways = $this->current_settings($payment_gateways_field_id);
		// Add "Exchange Rates" table
		add_settings_field(
			$payment_gateways_field_id,
			__('Set the payment gateways available when paying in each currency.', $this->_textdomain),
			array($this, 'render_payment_gateways_options'),
			$this->_settings_key,
			self::SECTION_PAYMENT_GATEWAYS_SETTINGS,
			array(
				'settings_key' => $this->_settings_key,
				'enabled_currencies' => $enabled_currencies,
				'payment_gateways' => $payment_gateways,
				'id' => $payment_gateways_field_id,
				'label_for' => $payment_gateways_field_id,
				// Input field attributes
				'attributes' => array(
					'class' => $payment_gateways_field_id,
				),
			)
		);

		/*** Currency selection options ***/
		// Prepare select to enable selection of the currency via a URL argument

		$shop_page_id = aelia_wc_version_is('>=', '3.0') ? wc_get_page_id('shop') : woocommerce_get_page_id('shop');
		$shop_url = get_permalink($shop_page_id);
		if(strpos($shop_url, '?') !== false) {
			$shop_url .= '&';
		}
		else {
			$shop_url .= '?';
		}
		$shop_url .= Definitions::ARG_CURRENCY . '=USD';
		$shop_url = "<a href=\"$shop_url\">$shop_url</a>";
		$this->render_checkbox_field(
			self::SECTION_CURRENCY_SELECTION_WIDGETS,
			Settings::FIELD_CURRENCY_VIA_URL_ENABLED,
			__('Allow to select a currency via the page URL', $this->_textdomain),
			sprintf(__('When enabled, it allows to select a currency by passing it via the URL. '.
								 'For example, %s would select USD.',
								 $this->_textdomain),
							$shop_url
		));

		$country_selection_options = array(
			Settings::OPTION_DISABLED => __('Disabled', $this->_textdomain),
			Settings::OPTION_BILLING_COUNTRY => __('Billing country', $this->_textdomain),
			Settings::OPTION_SHIPPING_COUNTRY => __('Shipping country', $this->_textdomain),
		);
		$this->render_dropdown_field(
			self::SECTION_CURRENCY_SELECTION_WIDGETS,
			Settings::FIELD_FORCE_CURRENCY_BY_COUNTRY,
			__('Force currency selection by customer country', $this->_textdomain),
			$country_selection_options,
			__('When enabled, it forces the shop currency to the one in use in the country '.
				 'selected by the customer. This option also adds a new widget that allows ' .
				 'customers to choose the country before reaching the checkout page, ' .
				 'showing them the prices in the appropriate currency while they browse the site ',
				 $this->_textdomain) .
			'<br />' .
			__('<strong>Important</strong>: if you enable this option, <strong>do not use the currency ' .
				 'selector widget</strong>, as any currency selected by the customer will be ignored and ' .
				 'overridden by this feature.',
				 $this->_textdomain)
		);

		/*** Support and troubleshooting settings ***/
		$log_file_path = \Aelia\WC\Logger::get_log_file_name(Definitions::PLUGIN_SLUG);

		$this->render_checkbox_field(
			self::SECTION_SUPPORT,
			Settings::FIELD_DEBUG_MODE_ENABLED,
			__('Enable debug mode', $this->_textdomain),
			sprintf(__('When debug mode is enabled, the plugin will log events to file ' .
								 '<code>%s</code>', $this->_textdomain),
							$log_file_path)
		);
	}

	/**
	 * Renders the Options page for the plugin.
	 */
	public function render_options_page() {
		if(!defined('AELIA_CS_SETTINGS_PAGE')) {
			define('AELIA_CS_SETTINGS_PAGE', true);
		}
		// Prepare settings page for rendering
		$this->init_settings_page();

		echo '<div class="wrap">';
		echo '<div class="icon32" id="icon-options-general"></div>';
		echo '<h2>';
		echo __('WooCommerce Currency Switcher', $this->_textdomain);
		printf('&nbsp;(v. %s)', WC_Aelia_CurrencySwitcher::$version);
		echo '</h2>';
		echo
			'<p>' .
			__('In this page, you can configure the parameters for the WooCommerce Currency Switcher. '.
				 'To get started, please select the Currencies that you would like to enable on your ' .
				 'website. Your Customers will then be able to select one of those Currencies to buy ' .
				 'products from your shop.',
				 $this->_textdomain) .
			'</p>';
		echo
			'<p>' .
				 __('<strong>Important</strong>: when Customers will place an order, the transaction will be completed ' .
				 '<strong>using the currency they selected</strong>. Please make sure that your Payment Gateways ' .
				 'are configured to accept the Currencies you enable.',
				 $this->_textdomain) .
			'</p>';

		settings_errors();
		echo '<form id="' . $this->_settings_key . '_form" method="post" action="options.php">';
		settings_fields($this->_settings_key);
		//do_settings_sections($this->_settings_key);
		$this->render_settings_sections($this->_settings_key);
		echo '<div class="buttons">';
		submit_button(__('Save Changes', $this->_textdomain),
									'primary',
									'submit',
									false);
		submit_button(__('Save and Update Exchange Rates', $this->_textdomain),
									'secondary',
									$this->_settings_key . '[update_exchange_rates_button]',
									false);
		echo '</div>';
		echo '</form>';
	}

	/**
	 * Adds a link to Settings Page in WooCommerce Admin menu.
	 *
	 * @deprecated since 4.4.10.170316
	 */
	//public function add_settings_page() {
	//	$aelia_cs_options_page = add_submenu_page(
	//		'woocommerce',
	//    	__('Currency Switcher', $this->_textdomain),
	//    	__('Currency Switcher', $this->_textdomain),
	//		'manage_options',
	//		Definitions::SLUG_OPTIONS_PAGE,
	//		array($this, 'render_options_page')
	//	);
	//
	//	add_action('load-' . $aelia_cs_options_page, array($this, 'options_page_load'));
	//}

	/**
	 * Returns the title for the menu item that will bring to the plugin's
	 * settings page.
	 *
	 * @return string
	 * @since 4.4.10.170316
	 */
	protected function menu_title() {
		return __('Currency Switcher', $this->_textdomain);
	}

	/**
	 * Returns the slug for the menu item that will bring to the plugin's
	 * settings page.
	 *
	 * @return string
	 * @since 4.4.10.170316
	 */
	protected function menu_slug() {
		return Definitions::MENU_SLUG;
	}

	/**
	 * Returns the title for the settings page.
	 *
	 * @return string
	 * @since 4.4.10.170316
	 */
	protected function page_title() {
		return __('Currency Switcher - Settings', $this->_textdomain) .
					 sprintf('&nbsp;(v. %s)', WC_Aelia_CurrencySwitcher::$version);
	}


	/*** Settings sections callbacks ***/
	public function enabled_currencies_section_callback() {
		// Dummy
	}

	public function exchange_rates_settings_section_callback() {
		echo
			'<p>' .
			__('In this section you can enter the Exchange Rates which you would like to use to convert prices '.
				 'from your base Currency to other Currencies. If you enable Automatic Updates, ' .
				 'Exchange Rates will be fetched on a regular basis from the Provider of your choice. ' .
				 'If you wish to lock an Exchange Rate to a specific value, and not have it updated ' .
				 'automatically, simply tick the corresponding box in the <strong>Set Manually</strong> column.',
				 $this->_textdomain) .
			'</p>';
		echo
			'<p class="notice-warning">' .
			__('<strong>Important</strong>: you must enter an exchange rate for every enabled '.
				 'currency, even if you plan to only use prices entered manually. The exchange ' .
				 'rates are also used to calculate an estimate of order amounts in base currency, ' .
				 'for reporting purposes, therefore it is important that they contain sensible values. ' .
				 '<strong>Currencies with an invalid exchange rate will be considered disabled '.
				 'and will not be used by the Currency Switcher</strong>.',
				 $this->_textdomain) .
			'</p>';
	}

	public function exchange_rates_update_section_callback() {
		?>
		<div><?php
			echo __('In this section you can configure the frequency of automatic updates for the exchange rates.',
			 $this->_textdomain);
		?></div>
		<div class="notice-warning">
			<p>
				<strong><?php echo __('Important', $this->_textdomain); ?>: </strong>
				<span><?php
					echo __('The first time you save the settings, the rates will fetched from the selected provider.',
									$this->_textdomain) .
							 ' ' .
							 __('You will then be able to alter them manually, or schedule them to be updated automatically.',
									$this->_textdomain);
				?></span>
			</p>
		</div>
		<?php
	}

	public function openexchangerates_settings_section_callback() {
		$model_key = $this->_settings_controller->get_exchange_rates_model_key('Aelia\WC\CurrencySwitcher\Exchange_Rates_OpenExchangeRates_Model');
		?>
		<div class="exchange_rate_model_settings <?php echo $model_key; ?>">
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					/**
					 * Ensure that the settings can't be saved if Open Exchange Rates is
					 * the selected provider, but no API key was entered.
					 *
					 * @since 4.5.2.171019
					 */
					$('#wc_aelia_currency_switcher_form > .buttons .button[type="submit"]').on('click', function() {
						var $api_key_field = $('#wc_aelia_currency_switcher\\[openexchange_api_key\\]');
						$api_key_field.prop('required', $api_key_field.is(':visible'));
					});
					return true;
				});
			</script>
			<p><?php
				__('In this section you can configure the parameters to connect to Open Exchange ' .
					 'Rates website.',
					 $this->_textdomain);
			?></p>
		</div>
		<?php
	}

	public function ipgeolocation_settings_section_callback() {
		echo
			'<p class="ipgeolocation_api_settings">' .
			__('In this section you can enable the GeoLocation feature, which tries to ' .
				 'select a Currency automatically, depending on visitors\' IP Address the ' .
				 'first time they visit the shop. This feature uses GeoLite data created ' .
				 'by MaxMind, available from <a href="http://www.maxmind.com">http://www.maxmind.com</a>.',
				 $this->_textdomain).
			'</p>';
		echo
			'<p class="ipgeolocation_api_settings">' .
			sprintf(__('GeoLite database in use: <code>%s</code>.',
								 $this->_textdomain),
						 IP2Location::geoip_db_file()).
			'</p>';
	}

	public function paymentgateways_settings_section_callback() {
		echo
			'<p class="paymentgateways_settings">' .
			__('In this section you can indicate which payment gateways can be used to ' .
				 'pay in each of the enabled currencies. This feature is useful when you ' .
				 'have one or more gateways that do not accept one of the currencies that ' .
				 'you wish to accept, or that are restricted to transactions in a single ' .
				 'currency (e.g. Mijireh).',
				 $this->_textdomain).
			'</p>';
	}

	public function currencyselection_widgets_settings_section_callback() {
		echo
			'<p class="currencyselection_widget_settings">' .
			__('In this section you can specify how your visitors will be able ' .
				 'to select the currency in which they will see prices and complete orders.',
				 $this->_textdomain).
			'</p>';
	}

	public function usage_section_callback() {
		echo '<h3>';
		echo __('How to display the currency selector widget', $this->_textdomain);
		echo '</h3>';
		echo '<p>';
		echo __('The currency selector widget allows your visitors to choose the currency ' .
						'they prefer to browse your site and place an order. To display the widget, ' .
						'you have the following options:', $this->_textdomain);
		echo '</p>';

		echo '<ol>';
		echo '<li>';
		echo '<h4>' . __('Using WordPress Widgets', $this->_textdomain) . '</h4>';
		echo '</h4>';
		echo '<p>';
		echo __('Go to <i>Appearance > Widgets</i>. There you will see a widget named ' .
						'"<strong>WooCommerce Currency Switcher - Currency Selector</strong>". Drag and drop it ' .
						'in a widget area, select a title and a widget type and click on "Save". ' .
						'The widget will now appear on the frontend of your shop, in the area where ' .
						'you dropped it.', $this->_textdomain);
		echo '</p>';
		echo '</li>';
		echo '<li>';

		echo '<h4>' . __('Using a shortcode', $this->_textdomain) . '</h4>';
		echo '</h4>';
		echo '<p>';
		echo __('You can display the currency selector widget using the following shortcode ' .
						'anywhere in your pages: ', $this->_textdomain);
		echo '</p>';
		echo '<code>[aelia_currency_selector_widget title="Widget title (optional)" widget_type="dropdown|buttons"]</code>';
		echo '<p>';
		echo __('The shortcode accepts the following parameters:', $this->_textdomain);
		echo '</p>';

		// Shortcode parameters
		echo '<ul>';
		echo '<li>';
		echo '<span class="label"><code>title</code></span>&nbsp;';
		echo '<span>' . __('The widget title (optional)', $this->_textdomain) . '</span>';
		echo '</li>';
		echo '<li>';
		echo '<span class="label"><code>widget_type</code></span>&nbsp;';
		echo '<span>' . __('The widget type. The Currency Switcher supports either <code>dropdown</code> ' .
											 'or <code>buttons</code>. Further types can be added by implementing a filter ' .
											 'in your theme for <code>wc_aelia_cs_currency_selector_widget_types</code> hook. ' .
											 'If this parameter is not specified, <code>dropdown</code> widget type will be ' .
											 'rendered by default.', $this->_textdomain) . '</span>';
		echo '</li>';
		echo '</ul>';

		echo '</li>';
		echo '</ol>';

		echo '<h3>';
		echo __('How to customise the look and feel of the currency selector widget', $this->_textdomain);
		echo '</h3>';
		echo '<p>';
		echo __('The currency selector widget is rendered using template files that can be ' .
						'found in <code>' . WC_Aelia_CurrencySwitcher::instance()->path('plugin') . '/views</code> folder. The following ' .
						'standard templates are available:',
						$this->_textdomain);
		echo '</p>';

		echo '<ul>';
		echo '<li>';
		echo '<code>currency-selector-widget-dropdown.php</code>: ' . __('displays "dropdown" style selector.', $this->_textdomain);
		echo '</li>';
		echo '<li>';
		echo '<code>currency-selector-widget-buttons.php</code>: ' . __('displays "buttons" style selector.', $this->_textdomain);
		echo '</li>';
		echo '</ul>';

		echo '<p>';
		echo __('If you wish to alter the templates, simply copy them in your theme. ' .
						'They should be put in <code>{your theme folder}/' . WC_Aelia_CurrencySwitcher::$plugin_slug .
						'/</code> and have the same name of the original files. The Currency Switcher ' .
						'will then load them automatically instead of the default ones.', $this->_textdomain);
		echo '</p>';
		echo '<p>';
		echo __('The CSS styles that apply to the standard layouts for the currency selector ' .
						'widget can be found in our knowledge base: ' .
						'<a href="https://aelia.freshdesk.com/support/solutions/articles/121622-how-can-i-customise-the-look-and-feel-of-the-currency-selector">' .
						'How can I customise the look and feel of the Currency Selector widget?</a>.',
						$this->_textdomain);
		echo '</p>';

		echo '<div class="Separator"></div>';

		echo '<h3>';
		echo __('How to display the country selector widget', $this->_textdomain);
		echo '</h3>';
		echo '<h4>' . __('Important', $this->_textdomain) . '</h4>';
		echo '<p>';
		echo __('To use the country selector, you must tick the <i>Force currency ' .
						'selection by country</i> box, which you can find in the <i>Currency ' .
						'selection tab</i>, above.', $this->_textdomain);
		echo '</p>';
		echo '<p>';
		echo __('The country selector widget allows your visitors to choose their ' .
						'country before they reach the checkout. The Tax Display by Country plugin will ' .
						'detect the choice and display the prices with our without tax, depending on the ' .
						'setting you entered. To display the widget, ' .
						'you have the following options:', $this->_textdomain);
		echo '</p>';

		echo '<ol>';
		echo '<li>';
		echo '<h4>' . __('Using WordPress Widgets', $this->_textdomain) . '</h4>';
		echo '</h4>';
		echo '<p>';
		echo __('Go to <i>Appearance > Widgets</i>. There you will see a widget named ' .
						'"<strong>WooCommerce Currency Switcher - Country Selector'.
						'</strong>". Drag and drop it in a widget area, select a title and a ' .
						'widget type and click on "Save". ' .
						'The widget will now appear on the frontend of your shop, in the area where ' .
						'you dropped it.', $this->_textdomain);
		echo '</p>';
		echo '</li>';
		echo '<li>';

		echo '<h4>' . __('Using a shortcode', $this->_textdomain) . '</h4>';
		echo '</h4>';
		echo '<p>';
		echo __('You can display the country selector widget using the following shortcode ' .
						'anywhere in your pages: ', $this->_textdomain);
		echo '</p>';
		echo '<code>[aelia_cs_country_selector_widget title="Widget title (optional)" widget_type="dropdown"]</code>';
		echo '<p>';
		echo __('The shortcode accepts the following parameters:', $this->_textdomain);
		echo '</p>';

		// Shortcode parameters
		echo '<ul>';
		echo '<li>';
		echo '<span class="label"><code>title</code></span>&nbsp;';
		echo '<span>' . __('The widget title (optional)', $this->_textdomain) . '</span>';
		echo '</li>';
		echo '<li>';
		echo '<span class="label"><code>widget_type</code></span>&nbsp;';
		echo '<span>' . __('The widget type. Out of the box, the widget supports only <code>dropdown</code>. ' .
											 'Further types can be added by implementing a filter ' .
											 'in your theme for <code>wc_aelia_cs_country_selector_widget_types</code> hook. ' .
											 'If this parameter is not specified, <code>dropdown</code> widget type will be ' .
											 'rendered by default.', $this->_textdomain) . '</span>';
		echo '</li>';
		echo '</ul>';

		echo '</li>';
		echo '</ol>';

		echo '<h3>';
		echo __('How to customise the look and feel of the country selector widget', $this->_textdomain);
		echo '</h3>';
		echo '<p>';
		echo __('The country selector widget is rendered using template files that can be ' .
						'found in <code>' . WC_Aelia_CurrencySwitcher::instance()->path('plugin') . '/views</code> folder. The following standard templates are available:',
						$this->_textdomain);
		echo '</p>';

		echo '<ul>';
		echo '<li>';
		echo '<code>billing-country-selector-widget-dropdown.php</code>: ' . __('displays "dropdown" style selector.', $this->_textdomain);
		echo '</li>';
		echo '</ul>';

		echo '<p>';
		echo __('If you wish to alter the templates, simply copy them in your theme. ' .
						'They should be put in <code>{your theme folder}/' . WC_Aelia_CurrencySwitcher::$plugin_slug .
						'/</code> and have the same name of the original files. The Tax Display by Country ' .
						'plugin will then load them automatically instead of the default ones.', $this->_textdomain);
		echo '</p>';
		echo '<p>';
		echo __('The CSS styles that apply to the standard layouts for the Country Selector ' .
						'widget can be found in our knowledge base: ' .
						'<a href="https://aelia.freshdesk.com/solution/categories/120257/folders/197723/articles/3000010678-how-can-i-customise-the-look-and-feel-of-the-billing-country-selector-widget-">' .
						'How can I customise the look and feel of the country selector widget?</a>.',
						$this->_textdomain);
		echo '</p>';
	}

	public function support_section_callback() {
		echo '<div class="support_information">';
		echo '<p>';
		echo __('We designed the Currency Switcher plugin to be robust and effective, ' .
						'as well as intuitive and easy to use. However, we are aware that, despite ' .
						'all best efforts, issues can arise and that there is always room for ' .
						'improvement.',
						$this->_textdomain);
		echo '</p>';
		echo '<p>';
		echo __('Should you need assistance, or if you just would like to get in touch ' .
						'with us, please use one of the links below.',
						$this->_textdomain);
		echo '</p>';

		// Support links
		echo '<ul id="contact_links">';
		echo '<li>' . sprintf(__('<span class="label">To request support</span>, please use our <a href="%s">Support portal</a>. ' .
														 'The portal also contains a Knowledge Base, where you can find the ' .
														 'answers to the most common questions related to our products.',
														 $this->_textdomain),
													self::SUPPORT_URL) . '</li>';
		echo '<li>' . sprintf(__('<span class="label">To send us general feedback</span>, suggestions, or enquiries, please use ' .
														 'the <a href="%s">contact form on our website.</a>',
														 $this->_textdomain),
													self::CONTACT_URL) . '</li>';
		echo '</ul>';
		echo '</div>';
	}

	/*** Rendering methods ***/
	/**
	 * Renders a table containing several fields that Admins can use to configure
	 * the Exchange Rates for the enabled Currencies.
	 *
	 * @param array args An array of arguments passed by add_settings_field().
	 * @see add_settings_field().
	 */
	public function render_exchange_rates_options($args) {
		$this->get_field_ids($args, $base_field_id, $base_field_name);

		//var_dump($args);die();
		// Retrieve the enabled currencies
		$enabled_currencies = array_filter($args[Settings::FIELD_ENABLED_CURRENCIES]);
		if(!is_array($enabled_currencies)) {
			throw new InvalidArgumentException(__('Argument "enabled_currencies" must be an array.', $this->_textdomain));
		}

		// If array contains only one element, it must be the base currency. In
		// such case, simply display a message
		if(count($enabled_currencies) <= 1) {
			echo '<p>' . __('Only the base WooCommerce currency has been enabled, therefore there are ' .
											'no Exchange Rates to be configured', $this->_textdomain) . '</p>';
			return;
		}

		// Retrieve the exchange rates
		$exchange_rates = get_value(Settings::FIELD_EXCHANGE_RATES, $args, array());
		if(!is_array($exchange_rates)) {
			//var_dump($exchange_rates);return;
			throw new InvalidArgumentException(__('Argument "exchange_rates" must be an array.', $this->_textdomain));
		}

		// Retrieve the Currency used internally by WooCommerce
		$woocommerce_base_currency = $this->_settings_controller->base_currency();

		$html = '<table id="exchange_rates_settings">';
		// Table header
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="sort">' . __('Sort', $this->_textdomain);
		$html .= '<span class="help-icon" title="' .
				 __('Drag and drop the placeholder next to each currency to reorder them',
						$this->_textdomain) .
				 '"></span>';
		$html .= '</th>';
		$html .= '<th class="currency_name">' . __('Currency', $this->_textdomain) . '</th>';
		$html .= '<th class="exchange_rate">';
		$html .= __('Exchange Rate', $this->_textdomain);
		$html .= '<span class="help-icon" title="' .
						 __('Enter the exchange rate that you would like to use for this currency. The value ' .
								'must use the point as a decimal separator, and it must not include any thousand ' .
								'separator. Example: 123.456',
								$this->_textdomain) .
						 '"></span>';
		$html .= '</th>';
		$html .= '<th class="set_manually">' .
						 __('Set Manually', $this->_textdomain) .
						 '<span class="help-icon" title="' .
						 __('Tick the box next to a currency if you would like to enter its ' .
								'exchange rate manually. By doing that, the rate you enter for ' .
								'that currency will not change, even if you have enabled the automatic ' .
								'update of exchange rates',
								$this->_textdomain) .
						 '"></span>' .
						 '<div class="selectors">' .
						 '<span class="select_all">' . __('Select', $this->_textdomain) . '</span>' .
						 '/' .
						 '<span class="deselect_all">' . __('Deselect', $this->_textdomain) . '</span>' .
						 __('all', $this->_textdomain) .
						 '</div>' .
						 '</th>';
		$html .= '<th class="rate_markup">';
		$html .= __('Rate Markup', $this->_textdomain);
		$html .= '<span class="help-icon" title="' .
						 __('If specified, this markup will be added to the standard ' .
								'exchange rate. Markup must be an absolute value. Example: ' .
								'if you have an exchange rate of 1.34 enter a markup of 0.05. ' .
								'The final exchange rate will then be (1.34 + 0.05) = 1.39',
								$this->_textdomain) .
						 '"></span>';
		$html .= '</th>';

		$html .= '<th class="thousand_separator">';
		$html .= __('Thousand sep.', $this->_textdomain);
		$html .= '<span class="help-icon"
							title="' . __('Enter the thousand separator that you would like to use when ' .
														'this currency is active', $this->_textdomain) .
							'"></span>';
		$html .= '</th>';

		$html .= '<th class="decimal_separator">';
		$html .= __('Decimal sep.', $this->_textdomain);
		$html .= '<span class="help-icon"
							title="' . __('Enter the decimal separator that you would like to use when ' .
														'this currency is active', $this->_textdomain) .
							'"></span>';
		$html .= '</th>';

		$html .= '<th class="decimals">';
		$html .= __('Decimals', $this->_textdomain);
		$html .= '<span class="help-icon" title="' .
						 __('The number of decimals will be used to round ALL figures. '.
								'Rounding will be mathematical, with halves rounded up. ' .
								'IMPORTANT: this setting affects PRICES and TAXES, which will be rounded to ' .
								'the specified amount of decimals. Do not set the value to zero unless you ' .
								'have a good reason, as that could result in an incorrect rounding of taxes', $this->_textdomain) .
						 '"></span>';
		$html .= '</th>';

		$html .= '<th class="symbol">';
		$html .= __('Symbol', $this->_textdomain);
		$html .= '<span class="help-icon" title="' .
						 __('The symbol that will be used to represent the currency. You can use this ' .
								'settings to distinguish currencies more easily, for example by displaying ' .
								'US$, AU$, NZ$ and so on.', $this->_textdomain) .
						 '"></span>';
		$html .= '</th>';

		$html .= '<th class="symbol_position">';
		$html .= __('Symbol position', $this->_textdomain);
		$html .= '</th>';

		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';


		foreach($exchange_rates as $currency => $currency_settings) {
			if($currency == $woocommerce_base_currency) {
				// Render a special line to display settings for base currency
				$html .= $this->render_settings_for_base_currency($woocommerce_base_currency,
																													$exchange_rates,
																													$base_field_id,
																													$base_field_name);

				continue;
			}

			// Discard currencies that are no longer enabled
			if(!in_array($currency, $enabled_currencies)) {
				return;
			}

			$currency_field_id = $this->group_field($currency, $base_field_id);
			$currency_field_name = $this->group_field($currency, $base_field_name);
			$html .= '<tr>';
			$html .= '<td class="sort handle">&nbsp;</td>';
			// Output currency label
			$html .= '<td class="currency_name">';
			$html .= '<span>' . $this->_settings_controller->get_currency_description($currency) . '</span>';
			$html .= '</td>';

			//$currency_settings = get_value($currency, $exchange_rates, array());
			$currency_settings = array_merge($this->_settings_controller->default_currency_settings(), $currency_settings);
			//var_dump($currency_settings);

			// Render exchange rate field
			$html .= '<td>';
			$field_args = array(
				'id' => $currency_field_id . '[rate]',
				'value' => get_value('rate', $currency_settings, ''),
				'attributes' => array(
					'class' => 'numeric',
				),
			);
			ob_start();
			$this->render_textbox($field_args);
      $field_html = ob_get_contents();
      ob_end_clean();
			$html .= $field_html;
			$html .= '</td>';

			//var_dump($currency_settings);
			// Render "Set Manually" checkbox
			$html .= '<td class="set_manually">';
			$field_args = array(
				'id' => $currency_field_id . '[set_manually]',
				'value' => 1,
				'attributes' => array(
					'class' => 'exchange_rate_set_manually',
					'checked' => get_value('set_manually', $currency_settings),
				),
			);
			ob_start();
			$this->render_checkbox($field_args);
			$field_html = ob_get_contents();
			ob_end_clean();
			$html .= $field_html;
			$html .= '</td>';

			// Render exchange rate markup field
			$html .= '<td>';
			$field_args = array(
				'id' => $currency_field_id . '[rate_markup]',
				'value' => get_value('rate_markup', $currency_settings, ''),
				'attributes' => array(
					'class' => 'numeric',
				),
			);
			ob_start();
			$this->render_textbox($field_args);
			$field_html = ob_get_contents();
			ob_end_clean();
			$html .= $field_html;
			$html .= '</td>';

			// Render thousand separator field
			$thousand_separator = get_value('thousand_separator', $currency_settings,
																		 $this->_settings_controller->woocommerce_price_thousand_sep);

			$html .= '<td class="thousand_separator">';
			$field_args = array(
				'id' => $currency_field_id . '[thousand_separator]',
				'value' => $thousand_separator,
				'attributes' => array(
					'class' => 'text',
				),
			);
			ob_start();
			$this->render_textbox($field_args);
      $field_html = ob_get_contents();
      ob_end_clean();
			$html .= $field_html;
			$html .= '</td>';

			// Render decimal separator field
			$decimal_separator = get_value('decimal_separator', $currency_settings,
																		 $this->_settings_controller->woocommerce_price_decimal_sep);

			$html .= '<td class="decimal_separator">';
			$field_args = array(
				'id' => $currency_field_id . '[decimal_separator]',
				'value' => $decimal_separator,
				'attributes' => array(
					'class' => 'text',
				),
			);
			ob_start();
			$this->render_textbox($field_args);
      $field_html = ob_get_contents();
      ob_end_clean();
			$html .= $field_html;
			$html .= '</td>';

			// Render decimals field
			$default_currency_decimals = default_currency_decimals($currency, $this->_settings_controller->woocommerce_currency_decimals);
			$currency_decimals = get_value('decimals', $currency_settings, null);
			if(!is_numeric($currency_decimals)) {
				$currency_decimals = $default_currency_decimals;
			}

			$html .= '<td class="decimals">';
			$field_args = array(
				'id' => $currency_field_id . '[decimals]',
				'value' => $currency_decimals,
				'attributes' => array(
					'class' => 'numeric',
				),
			);
			ob_start();
			$this->render_textbox($field_args);
      $field_html = ob_get_contents();
      ob_end_clean();
			$html .= $field_html;
			$html .= '</td>';

			// Render currency symbol field
			$currency_symbol = get_value('symbol', $currency_settings, get_woocommerce_currency_symbol($currency));
			$html .= '<td class="symbol">';
			$field_args = array(
				'id' => $currency_field_id . '[symbol]',
				'value' => $currency_symbol,
				'attributes' => array(
					'class' => 'text',
				),
			);
			ob_start();
			$this->render_textbox($field_args);
      $field_html = ob_get_contents();
      ob_end_clean();
			$html .= $field_html;
			$html .= '</td>';

			// Render currency symbol position field
			$html .= '<td class="symbol_position">';
			$field_args = array(
				'id' => $currency_field_id . '[symbol_position]',
				'options' => array(
					'left' => __( 'Left', 'woocommerce' ) . ' (' . $currency_symbol . '99.99)',
					'right' => __( 'Right', 'woocommerce' ) . ' (99.99' . $currency_symbol . ')',
					'left_space' => __( 'Left with space', 'woocommerce' ) . ' (' . $currency_symbol . ' 99.99)',
					'right_space' => __( 'Right with space', 'woocommerce' ) . ' (99.99 ' . $currency_symbol . ')'
				),
				'selected' => get_value('symbol_position', $currency_settings, get_option('woocommerce_currency_pos')),
				'attributes' => array(
					'class' => 'currency_symbol_position',
				),
			);
			ob_start();
			$this->render_dropdown($field_args);
      $field_html = ob_get_contents();
      ob_end_clean();
			$html .= $field_html;
			$html .= '</td>';

			$html .= '</tr>';
		}

		$html .= '</tbody>';
		$html .= '</table>';

		echo $html;
	}

	/**
	 * Renders a "special" row on the exchange rates table, which contains the
	 * settings for the base currency.
	 *
	 * @param string currency The currency to display on the row.
	 * @param string exchange_rates An array of currency settings.
	 * @param string base_field_id The base ID that will be assigned to the
	 * fields in the row.
	 * @param string base_field_id The base name that will be assigned to the
	 * fields in the row.
	 * @return string The HTML for the row.
	 */
	protected function render_settings_for_base_currency($currency, $exchange_rates, $base_field_id, $base_field_name) {
		$currency_field_id = $this->group_field($currency, $base_field_id);
		$currency_field_name = $this->group_field($currency, $base_field_name);

		$html = '<tr>';
		$html .= '<td class="sort handle">&nbsp;</td>';
		// Output currency label
		$html .= '<td class="currency_name">';
		$html .= '<span>' . $this->_settings_controller->get_currency_description($currency) . '</span>';
		$html .= '</td>';

		$currency_settings = get_value($currency, $exchange_rates, array());
		$currency_settings = array_merge($this->_settings_controller->default_currency_settings(), $currency_settings);
		//var_dump($currency_settings);

		// Render exchange rate field
		$html .= '<td class="numeric">';
		$html .= '1'; // Exchange rate for base currency is always 1
		$html .= '</td>';

		// Render "Set Manually" checkbox
		$html .= '<td>';
		$html .= '</td>';

		// Render exchange rate markup field
		$html .= '<td>';
		$html .= '</td>';

		// Render thousand separator field
		$currency_decimals = get_value('thousand_separator', $currency_settings,
																	 $this->_settings_controller->woocommerce_price_thousand_sep);

		$html .= '<td class="thousand_separator">';
		$field_args = array(
			'id' => $currency_field_id . '[thousand_separator]',
			'value' => $currency_decimals,
			'attributes' => array(
				'class' => 'text',
			),
		);
		ob_start();
		$this->render_textbox($field_args);
		$field_html = ob_get_contents();
		ob_end_clean();
		$html .= $field_html;
		$html .= '</td>';

		// Render decimal separator field
		$decimal_separator = get_value('decimal_separator', $currency_settings,
																	 $this->_settings_controller->woocommerce_price_decimal_sep);

		$html .= '<td class="decimal_separator">';
		$field_args = array(
			'id' => $currency_field_id . '[decimal_separator]',
			'value' => $decimal_separator,
			'attributes' => array(
				'class' => 'text',
			),
		);
		ob_start();
		$this->render_textbox($field_args);
		$field_html = ob_get_contents();
		ob_end_clean();
		$html .= $field_html;
		$html .= '</td>';

		// Render decimals field
		$default_currency_decimals = default_currency_decimals($currency, $this->_settings_controller->woocommerce_currency_decimals);
		$currency_decimals = get_value('decimals', $currency_settings, null);
		if(!is_numeric($currency_decimals)) {
			$currency_decimals = $default_currency_decimals;
		}

		$html .= '<td class="decimals">';
		$field_args = array(
			'id' => $currency_field_id . '[decimals]',
			'value' => $currency_decimals,
			'attributes' => array(
				'class' => 'numeric',
			),
		);
		ob_start();
		$this->render_textbox($field_args);
		$field_html = ob_get_contents();
		ob_end_clean();
		$html .= $field_html;
		$html .= '</td>';

		// Render currency symbol field
		$currency_symbol = get_value('symbol', $currency_settings, get_woocommerce_currency_symbol($currency));
		$html .= '<td class="symbol">';
		$field_args = array(
			'id' => $currency_field_id . '[symbol]',
			'value' => $currency_symbol,
			'attributes' => array(
				'class' => 'text',
			),
		);
		ob_start();
		$this->render_textbox($field_args);
		$field_html = ob_get_contents();
		ob_end_clean();
		$html .= $field_html;
		$html .= '</td>';

		// Render currency symbol position field
		$html .= '<td class="symbol_position">';
		$field_args = array(
			'id' => $currency_field_id . '[symbol_position]',
			'options' => array(
				'left' => __( 'Left', 'woocommerce' ) . ' (' . $currency_symbol . '99.99)',
				'right' => __( 'Right', 'woocommerce' ) . ' (99.99' . $currency_symbol . ')',
				'left_space' => __( 'Left with space', 'woocommerce' ) . ' (' . $currency_symbol . ' 99.99)',
				'right_space' => __( 'Right with space', 'woocommerce' ) . ' (99.99 ' . $currency_symbol . ')'
			),
			'selected' => get_value('symbol_position', $currency_settings, get_option('woocommerce_currency_pos')),
			'attributes' => array(
				'class' => 'currency_symbol_position',
			),
		);
		ob_start();
		$this->render_dropdown($field_args);
		$field_html = ob_get_contents();
		ob_end_clean();
		$html .= $field_html;
		$html .= '</td>';

		$html .= '</tr>';

		return $html;
	}


	/**
	 * Renders a table containing a list of currencies and the payment gateways
	 * enabled for each one of them.
	 *
	 * @param array args An array of arguments passed by add_settings_field().
	 * @see add_settings_field().
	 */
	public function render_payment_gateways_options($args) {
		$this->get_field_ids($args, $base_field_id, $base_field_name);

		//var_dump($args);die();
		// Retrieve the enabled currencies
		$enabled_currencies = array_filter($args[Settings::FIELD_ENABLED_CURRENCIES]);
		if(!is_array($enabled_currencies)) {
			throw new InvalidArgumentException(__('Argument "enabled_currencies" must be an array.', $this->_textdomain));
		}

		// Retrieve the payment gateways currently set for each currency
		$payment_gateways = get_value(Settings::FIELD_PAYMENT_GATEWAYS, $args, array());

		$html = '<table id="payment_gateways_settings">';
		// Table header
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="currency">' . __('Currency', $this->_textdomain) . '</th>';
		$html .= '<th class="payment_gateways">' . __('Enabled Gateways', $this->_textdomain) . '</th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';

		foreach($enabled_currencies as $currency) {
			$currency_field_id = $this->group_field($currency, $base_field_id);
			$currency_field_name = $this->group_field($currency, $base_field_name);
			$html .= '<tr>';
			// Output currency label
			$html .= '<td>';
			$html .= '<span>' . $this->_settings_controller->get_currency_description($currency) . '</span>';
			$html .= '</td>';

			$currency_settings = get_value($currency, $payment_gateways, $this->_settings_controller->default_currency_settings());
			//var_dump($payment_gateways);die();

			// Retrieve all enabled Payment Gateways to prepare a list of options to
			// display in the dropdown fields
			$payment_gateways_options = array();
			foreach($this->_settings_controller->woocommerce_payment_gateways() as $gateway_id => $gateway) {
				// Take payment gateway's frontend title or, if it's empty, the internal title
				$gateway_title = !empty($gateway->title) ? $gateway->title : $gateway->method_title;
				$payment_gateways_options[$gateway_id] = $gateway_title;
			}

			// Render payment gateways field
			$html .= '<td>';
			$field_args = array(
				'id' => $currency_field_id . '[enabled_gateways]',
				'options' => $payment_gateways_options,
				'selected' => get_value('enabled_gateways', $currency_settings, ''),
				'attributes' => array(
					'class' => 'currency_payment_gateways',
					'multiple' => 'multiple',
				),
			);
			ob_start();
			$this->render_dropdown($field_args);
      $field_html = ob_get_contents();
      ob_end_clean();
			$html .= $field_html;
			$html .= '</td>';

			//var_dump($currency_settings);
			$html .= '</td>';

			$html .= '</tr>';
		}

		$html .= '</tbody>';
		$html .= '</table>';

		echo $html;
	}
}
