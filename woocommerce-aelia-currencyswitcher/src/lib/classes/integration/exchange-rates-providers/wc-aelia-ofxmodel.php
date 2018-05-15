<?php
namespace Aelia\WC\CurrencySwitcher;
if(!defined('ABSPATH')) exit; // Exit if accessed directly

use \Exception;

/**
 * Retrieves the Exchange Rates from OFX.
 *
 * @link https://www.ofx.com/en-gb/
 * @since 4.5.13.180118
 */
class WC_Aelia_OFXModel extends \Aelia\WC\ExchangeRatesModel {
	// @var string The provider ID
	public static $id = 'ofx';

	// @var string The base currency used to retrieve the exchange rates.
	protected $_base_currency;

	// @var string The URL template to use to query OFX
	protected $ofx_url = 'https://api.ofx.com/PublicSite.ApiService/OFX/spotrate/Individual/%1$s/%2$s/1?format=json';

	protected function enabled_currencies() {
		return apply_filters('wc_aelia_cs_enabled_currencies', array(get_option('woocommerce_currency')));
	}

	/**
	 * Tranforms the exchange rates received from OFX into an array of
	 * currency code => exchange rate pairs.
	 *
	 * @param string ofx The JSON received from the remote service.
	 * @return array
	 */
	protected function decode_rates($ofx_rates) {
		$exchange_rates = array();

		foreach($ofx_rates as $currency => $rate) {
			if(!is_object($rate) || !isset($rate->InterbankRate)) {
				continue;
			}

			$exchange_rates[$currency] = (float)$rate->InterbankRate;
		}
		// Set the exchange rate for the base currency to 1
		$exchange_rates[$this->_base_currency] = 1;
		return $exchange_rates;
	}

	/**
	 * Fetches all exchange rates from OFX service.
	 *
	 * @return object|bool An object containing the response from Open Exchange, or
	 * False in case of failure.
	 */
	private function fetch_all_rates() {
		$rates = array();
		$rates_to_request = array();
		foreach($this->enabled_currencies() as $currency) {
			// No need to retrieve the exchange rate for the base currency, it's always 1
			if($currency === $this->_base_currency) {
				continue;
			}

			$query_url = sprintf($this->ofx_url, $this->_base_currency, $currency);
			try {
				$response = \Httpful\Request::get($query_url)
					->expectsJson()
					->send();

				// Debug
				//var_dump("OFX RATES RESPONSE:", $response); die();
				if($response->hasErrors()) {
					// We can find error details in response body
					if($response->hasBody()) {
						$response_data = $response->body;

						$this->add_error(self::ERR_ERROR_RETURNED,
														 sprintf(__('Error returned by OFX. ' .
																				'Error code: %s. Error message: %s - %s.',
																				Definitions::TEXT_DOMAIN),
																		 $response_data->status,
																		 $response_data->message,
																		 $response_data->description));
					}
					continue;
				}
				// Store the FX rate into the result
				$rates[$currency] = $response->body;
			}
			catch(Exception $e) {
				$this->add_error(self::ERR_EXCEPTION_OCCURRED,
												 sprintf(__('Exception occurred while retrieving the exchange rates from OFX. ' .
																		'Error message: %s.',
																		Definitions::TEXT_DOMAIN),
																 $e->getMessage()));
				continue;
			}
		}
		return $rates;
	}

	/**
	 * Returns current exchange rates for the specified currency.
	 *
	 * @param string base_currency The base currency.
	 * @return array An array of Currency => Exchange Rate pairs.
	 */
	private function current_rates($base_currency) {
		if(empty($this->_current_rates) ||
			 $this->_base_currency != $base_currency) {

			// Set the base currency for which to retrieve the exchange rates
			$this->_base_currency = $base_currency;

			// Fetch exchange rates
			$ofx_rates = $this->fetch_all_rates();

			if(empty($ofx_rates)) {
				return null;
			}

			// Debug
			//var_dump($ofx_rates);die();

			// OFX rates are returned as JSON representation of an array of objects.
			// We need to transform it into an array of currency => rate pairs
			$exchange_rates = $this->decode_rates($ofx_rates);
			// Debug
			//var_dump($exchange_rates);die();
			if(!is_array($exchange_rates)) {
				$this->add_error(self::ERR_UNEXPECTED_ERROR_FETCHING_EXCHANGE_RATES,
												 __('An unexpected error occurred while fetching exchange rates ' .
														'from OFX. The most common cause of this issue is the ' .
														'absence of PHP CURL extension. Please make sure that ' .
														'PHP CURL is installed and configured in your system.',
														Definitions::TEXT_DOMAIN));
				return array();
			}

			$this->_current_rates = $exchange_rates;
		}
		return $this->_current_rates;
	}

	/**
	 * Recalculates the exchange rates using another base currency.
	 *
	 * @param array exchange_rates The exchange rates retrieved from Yahoo Finance.
	 * @param string base_currency The base currency against which the rates should
	 * be recalculated.
	 * @return array An array of currency => exchange rate pairs.
	 */
	private function rebase_rates(array $exchange_rates, $base_currency) {
		$recalc_rate = get_value($base_currency, $exchange_rates);
		//var_dump($base_currency, $exchange_rates);

		if(empty($recalc_rate)) {
			$this->add_error(self::ERR_BASE_CURRENCY_NOT_FOUND,
											 sprintf(__('Could not rebase rates against base currency "%s". ' .
																	'Currency not found in data returned by OFX.',
																	Definitions::TEXT_DOMAIN),
															 $base_currency));
			return null;
		}

		$result = array();
		foreach($exchange_rates as $currency => $rate) {
			$result[$currency] = $rate / $recalc_rate;
		}

		// Debug
		//var_dump($result); die();
		return $result;
	}

	/**
	 * Returns the exchange rate of a currency in respect to a base currency.
	 *
	 * @param string base_currency The code of the base currency.
	 * @param string currency The code of the currency for which to find the
	 * Exchange Rate.
	 * @return float
	 */
	protected function get_rate($base_currency, $currency) {
		$current_rates = $this->current_rates($base_currency);
		return get_value($currency, $current_rates);
	}
}
