<?php
namespace Aelia\WC\CurrencySwitcher;
if(!defined('ABSPATH')) exit; // Exit if accessed directly

use Aelia\WC\IP2Location;

/**
 * Allows to select a Currency based on a geographic region.
 */
class WC_Aelia_Currencies_Manager {
	// @var array A list of all world currencies. This list will be populated in
	// the world_currencies() method
	protected static $_world_currencies;

	// @var The instance of the logger used by the class
	protected $_logger;

	/**
	 * Returns a logger instance.
	 *
	 * @return Aelia\WC\Logger
	 * @since 4.4.2.170117
	 */
	protected function logger() {
		if(empty($this->_logger)) {
			$this->_logger = WC_Aelia_CurrencySwitcher::instance()->get_logger();
		}
		return $this->_logger;
	}

	// @var array A list of the currencies used by all countries
	protected $_country_currencies = array(
		'AD' => 'EUR', // Andorra - Euro
		'AE' => 'AED', // United Arab Emirates - Arab Emirates Dirham
		'AF' => 'AFA', // Afghanistan - Afghanistan Afghani
		'AG' => 'XCD', // Antigua and Barbuda - East Caribbean Dollar
		'AI' => 'XCD', // Anguilla - East Caribbean Dollar
		'AL' => 'ALL', // Albania - Albanian Lek
		'AM' => 'AMD', // Armenia - Armenian Dram
		'AN' => 'ANG', // Netherlands Antilles - Netherlands Antillean Guilder
		'AO' => 'AOA', // Angola - Angolan Kwanza
		'AQ' => 'ATA', // Antarctica - Dollar
		'AR' => 'ARS', // Argentina - Argentine Peso
		'AS' => 'USD', // American Samoa - US Dollar
		'AT' => 'EUR', // Austria - Euro
		'AU' => 'AUD', // Australia - Australian Dollar
		'AW' => 'AWG', // Aruba - Aruban Florin
		'AX' => 'EUR', // Aland Islands - Euro
		'AZ' => 'AZN', // Azerbaijan - Azerbaijani Manat
		'BA' => 'BAM', // Bosnia-Herzegovina - Marka
		'BB' => 'BBD', // Barbados - Barbados Dollar
		'BD' => 'BDT', // Bangladesh - Bangladeshi Taka
		'BE' => 'EUR', // Belgium - Euro
		'BF' => 'XOF', // Burkina Faso - CFA Franc BCEAO
		'BG' => 'BGN', // Bulgaria - Bulgarian Lev
		'BH' => 'BHD', // Bahrain - Bahraini Dinar
		'BI' => 'BIF', // Burundi - Burundi Franc
		'BJ' => 'XOF', // Benin - CFA Franc BCEAO
		'BL' => 'EUR', // Saint Barthelemy - Euro
		'BM' => 'BMD', // Bermuda - Bermudian Dollar
		'BN' => 'BND', // Brunei Darussalam - Brunei Dollar
		'BO' => 'BOB', // Bolivia - Boliviano
		'BQ' => 'USD', // Bonaire, Sint Eustatius and Saba - US Dollar
		'BR' => 'BRL', // Brazil - Brazilian Real
		'BS' => 'BSD', // Bahamas - Bahamian Dollar
		'BT' => 'BTN', // Bhutan - Bhutan Ngultrum
		'BV' => 'NOK', // Bouvet Island - Norwegian Krone
		'BW' => 'BWP', // Botswana - Botswana Pula
		'BY' => 'BYR', // Belarus - Belarussian Ruble
		'BZ' => 'BZD', // Belize - Belize Dollar
		'CA' => 'CAD', // Canada - Canadian Dollar
		'CC' => 'AUD', // Cocos (Keeling) Islands - Australian Dollar
		'CD' => 'CDF', // Democratic Republic of Congo - Francs
		'CF' => 'XAF', // Central African Republic - CFA Franc BEAC
		'CG' => 'XAF', // Republic of the Congo - CFA Franc BEAC
		'CH' => 'CHF', // Switzerland - Swiss Franc
		'CI' => 'XOF', // Ivory Coast - CFA Franc BCEAO
		'CK' => 'NZD', // Cook Islands - New Zealand Dollar
		'CL' => 'CLP', // Chile - Chilean Peso
		'CM' => 'XAF', // Cameroon - CFA Franc BEAC
		'CN' => 'CNY', // China - Yuan Renminbi
		'CO' => 'COP', // Colombia - Colombian Peso
		'CR' => 'CRC', // Costa Rica - Costa Rican Colon
		'CU' => 'CUP', // Cuba - Cuban Peso
		'CV' => 'CVE', // Cape Verde - Cape Verde Escudo
		'CW' => 'ANG', // Curacao - Netherlands Antillean Guilder
		'CX' => 'AUD', // Christmas Island - Australian Dollar
		'CY' => 'EUR', // Cyprus - Euro
		'CZ' => 'CZK', // Czech Rep. - Czech Koruna
		'DE' => 'EUR', // Germany - Euro
		'DJ' => 'DJF', // Djibouti - Djibouti Franc
		'DK' => 'DKK', // Denmark - Danish Krone
		'DM' => 'XCD', // Dominica - East Caribbean Dollar
		'DO' => 'DOP', // Dominican Republic - Dominican Peso
		'DZ' => 'DZD', // Algeria - Algerian Dinar
		'EC' => 'ECS', // Ecuador - Ecuador Sucre
		'EE' => 'EUR', // Estonia - Euro
		'EG' => 'EGP', // Egypt - Egyptian Pound
		'EH' => 'MAD', // Western Sahara - Moroccan Dirham
		'ER' => 'ERN', // Eritrea - Eritrean Nakfa
		'ES' => 'EUR', // Spain - Euro
		'ET' => 'ETB', // Ethiopia - Ethiopian Birr
		'FI' => 'EUR', // Finland - Euro
		'FJ' => 'FJD', // Fiji - Fiji Dollar
		'FK' => 'FKP', // Falkland Islands - Falkland Islands Pound
		'FM' => 'USD', // Micronesia - US Dollar
		'FO' => 'DKK', // Faroe Islands - Danish Krone
		'FR' => 'EUR', // France - Euro
		'GA' => 'XAF', // Gabon - CFA Franc BEAC
		'GB' => 'GBP', // United Kingdom - Pound Sterling
		'GD' => 'XCD', // Grenada - East Carribean Dollar
		'GE' => 'GEL', // Georgia - Georgian Lari
		'GF' => 'EUR', // French Guiana - Euro
		'GG' => 'GBP', // Guernsey - Pound Sterling
		'GH' => 'GHS', // Ghana - Ghanaian Cedi
		'GI' => 'GIP', // Gibraltar - Gibraltar Pound
		'GL' => 'DKK', // Greenland - Danish Krone
		'GM' => 'GMD', // Gambia - Gambian Dalasi
		'GN' => 'GNF', // Guinea - Guinea Franc
		'GP' => 'EUR', // Guadeloupe (French) - Euro
		'GQ' => 'XAF', // Equatorial Guinea - CFA Franc BEAC
		'GR' => 'EUR', // Greece - Euro
		'GS' => 'GBP', // South Georgia & South Sandwich Islands - Pound Sterling
		'GT' => 'GTQ', // Guatemala - Guatemalan Quetzal
		'GU' => 'USD', // Guam (USA) - US Dollar
		'GW' => 'XAF', // Guinea Bissau - CFA Franc BEAC
		'GY' => 'GYD', // Guyana - Guyana Dollar
		'HK' => 'HKD', // Hong Kong - Hong Kong Dollar
		'HM' => 'AUD', // Heard Island and McDonald Islands - Australian Dollar
		'HN' => 'HNL', // Honduras - Honduran Lempira
		'HR' => 'HRK', // Croatia - Croatian Kuna
		'HT' => 'HTG', // Haiti - Haitian Gourde
		'HU' => 'HUF', // Hungary - Hungarian Forint
		'ID' => 'IDR', // Indonesia - Indonesian Rupiah
		'IE' => 'EUR', // Ireland - Euro
		'IL' => 'ILS', // Israel - Israeli New Shekel
		'IM' => 'GBP', // Isle of Man - Pound Sterling
		'IN' => 'INR', // India - Indian Rupee
		'IO' => 'USD', // British Indian Ocean Territory - US Dollar
		'IQ' => 'IQD', // Iraq - Iraqi Dinar
		'IR' => 'IRR', // Iran - Iranian Rial
		'IS' => 'ISK', // Iceland - Iceland Krona
		'IT' => 'EUR', // Italy - Euro
		'JE' => 'GBP', // Jersey - Pound Sterling
		'JM' => 'JMD', // Jamaica - Jamaican Dollar
		'JO' => 'JOD', // Jordan - Jordanian Dinar
		'JP' => 'JPY', // Japan - Japanese Yen
		'KE' => 'KES', // Kenya - Kenyan Shilling
		'KG' => 'KGS', // Kyrgyzstan - Som
		'KH' => 'KHR', // Cambodia - Kampuchean Riel
		'KI' => 'AUD', // Kiribati - Australian Dollar
		'KM' => 'KMF', // Comoros - Comoros Franc
		'KN' => 'XCD', // Saint Kitts & Nevis Anguilla - East Caribbean Dollar
		'KP' => 'KPW', // Korea, North - North Korean Won
		'KR' => 'KRW', // Korea, South - Korean Won
		'KW' => 'KWD', // Kuwait - Kuwaiti Dinar
		'KY' => 'KYD', // Cayman Islands - Cayman Islands Dollar
		'KZ' => 'KZT', // Kazakhstan - Kazakhstan Tenge
		'LA' => 'LAK', // Laos - Lao Kip
		'LB' => 'LBP', // Lebanon - Lebanese Pound
		'LC' => 'XCD', // Saint Lucia - East Caribbean Dollar
		'LI' => 'CHF', // Liechtenstein - Swiss Franc
		'LK' => 'LKR', // Sri Lanka - Sri Lanka Rupee
		'LR' => 'LRD', // Liberia - Liberian Dollar
		'LS' => 'LSL', // Lesotho - Lesotho Loti
		//'LT' => 'LTL', // Lithuania - Lithuanian Litas
		'LT' => 'EUR', // Lithuania - Euro, since January 2015
		'LU' => 'EUR', // Luxembourg - Euro
		'LV' => 'LVL', // Latvia - Latvian Lats
		'LY' => 'LYD', // Libya - Libyan Dinar
		'MA' => 'MAD', // Morocco - Moroccan Dirham
		'MC' => 'EUR', // Monaco - Euro
		'MD' => 'MDL', // Moldova - Moldovan Leu
		'ME' => 'EUR', // Montenegro - Euro
		'MF' => 'EUR', // Saint Martin (French Part) - Euro
		'MG' => 'MGA', // Madagascar - Malagasy Ariary
		'MH' => 'USD', // Marshall Islands - US Dollar
		'MK' => 'MKD', // Macedonia - Denar
		'ML' => 'XOF', // Mali - CFA Franc BCEAO
		'MM' => 'MMK', // Myanmar - Myanmar Kyat
		'MN' => 'MNT', // Mongolia - Mongolian Tugrik
		'MO' => 'MOP', // Macau - Macau Pataca
		'MP' => 'USD', // Northern Mariana Islands - US Dollar
		'MQ' => 'EUR', // Martinique (French) - Euro
		'MR' => 'MRO', // Mauritania - Mauritanian Ouguiya
		'MS' => 'XCD', // Montserrat - East Caribbean Dollar
		'MT' => 'EUR', // Malta - Euro
		'MU' => 'MUR', // Mauritius - Mauritius Rupee
		'MV' => 'MVR', // Maldives - Maldive Rufiyaa
		'MW' => 'MWK', // Malawi - Malawi Kwacha
		'MX' => 'MXN', // Mexico - Mexican Peso
		'MY' => 'MYR', // Malaysia - Malaysian Ringgit
		'MZ' => 'MZN', // Mozambique - Mozambique Metical
		'NA' => 'NAD', // Namibia - Namibian Dollar
		'NC' => 'XPF', // New Caledonia (French) - CFP Franc
		'NE' => 'XOF', // Niger - CFA Franc BCEAO
		'NF' => 'AUD', // Norfolk Island - Australian Dollar
		'NG' => 'NGN', // Nigeria - Nigerian Naira
		'NI' => 'NIO', // Nicaragua - Nicaraguan Cordoba Oro
		'NL' => 'EUR', // Netherlands - Euro
		'NO' => 'NOK', // Norway - Norwegian Krone
		'NP' => 'NPR', // Nepal - Nepalese Rupee
		'NR' => 'AUD', // Nauru - Australian Dollar
		'NU' => 'NZD', // Niue - New Zealand Dollar
		'NZ' => 'NZD', // New Zealand - New Zealand Dollar
		'OM' => 'OMR', // Oman - Omani Rial
		'PA' => 'PAB', // Panama - Panamanian Balboa
		'PE' => 'PEN', // Peru - Peruvian Nuevo Sol
		'PF' => 'XPF', // Polynesia (French) - CFP Franc
		'PG' => 'PGK', // Papua New Guinea - Papua New Guinea Kina
		'PH' => 'PHP', // Philippines - Philippine Peso
		'PK' => 'PKR', // Pakistan - Pakistan Rupee
		'PL' => 'PLN', // Poland - Polish Zloty
		'PM' => 'EUR', // Saint Pierre and Miquelon - Euro
		'PN' => 'NZD', // Pitcairn Island - New Zealand Dollar
		'PR' => 'USD', // Puerto Rico - US Dollar
		'PS' => 'ILS', // Palestinian Territories - Israeli New Shekel
		'PT' => 'EUR', // Portugal - Euro
		'PW' => 'USD', // Palau - US Dollar
		'PY' => 'PYG', // Paraguay - Paraguay Guarani
		'QA' => 'QAR', // Qatar - Qatari Rial
		'RE' => 'EUR', // Reunion (French) - Euro
		'RO' => 'RON', // Romania - Romanian New Leu
		'RS' => 'RSD', // Serbia - Serbian Dinar
		'RU' => 'RUB', // Russia - Russian Ruble
		'RW' => 'RWF', // Rwanda - Rwanda Franc
		'SA' => 'SAR', // Saudi Arabia - Saudi Riyal
		'SB' => 'SBD', // Solomon Islands - Solomon Islands Dollar
		'SC' => 'SCR', // Seychelles - Seychelles Rupee
		'SD' => 'SDG', // Sudan - Sudanese Pound
		'SE' => 'SEK', // Sweden - Swedish Krona
		'SG' => 'SGD', // Singapore - Singapore Dollar
		'SH' => 'SHP', // Saint Helena - St. Helena Pound
		'SI' => 'EUR', // Slovenia - Euro
		'SJ' => 'NOK', // Svalbard and Jan Mayen Islands - Norwegian Krone
		'SK' => 'EUR', // Slovakia - Euro
		'SL' => 'SLL', // Sierra Leone - Sierra Leone Leone
		'SM' => 'EUR', // San Marino - Euro
		'SN' => 'XOF', // Senegal - CFA Franc BCEAO
		'SO' => 'SOS', // Somalia - Somali Shilling
		'SR' => 'SRD', // Suriname - Surinamese Dollar
		'SS' => 'SSP', // South Sudan - South Sudanese Pound
		'ST' => 'STD', // Sao Tome and Principe - Dobra
		'SV' => 'USD', // El Salvador - US Dollar
		'SX' => 'ANG', // Sint Maarten (Dutch Part) - Netherlands Antillean Guilder
		'SY' => 'SYP', // Syria - Syrian Pound
		'SZ' => 'SZL', // Swaziland - Swaziland Lilangeni
		'TC' => 'USD', // Turks and Caicos Islands - US Dollar
		'TD' => 'XAF', // Chad - CFA Franc BEAC
		'TF' => 'EUR', // French Southern Territories - Euro
		'TG' => 'XOF', // Togo - CFA Franc BCEAO
		'TH' => 'THB', // Thailand - Thai Baht
		'TJ' => 'TJS', // Tajikistan - Tajik Somoni
		'TK' => 'NZD', // Tokelau - New Zealand Dollar
		'TL' => 'USD', // Timor-Leste - US Dollar
		'TM' => 'TMM', // Turkmenistan - Manat
		'TN' => 'TND', // Tunisia - Tunisian Dinar
		'TO' => 'TOP', // Tonga - Tongan Pa&#699;anga
		'TR' => 'TRY', // Turkey - Turkish Lira
		'TT' => 'TTD', // Trinidad and Tobago - Trinidad and Tobago Dollar
		'TV' => 'AUD', // Tuvalu - Australian Dollar
		'TW' => 'TWD', // Taiwan - New Taiwan Dollar
		'TZ' => 'TZS', // Tanzania - Tanzanian Shilling
		'UA' => 'UAH', // Ukraine - Ukraine Hryvnia
		'UG' => 'UGX', // Uganda - Uganda Shilling
		'UM' => 'USD', // USA Minor Outlying Islands - US Dollar
		'US' => 'USD', // USA - US Dollar
		'UY' => 'UYU', // Uruguay - Uruguayan Peso
		'UZ' => 'UZS', // Uzbekistan - Uzbekistan Sum
		'VA' => 'EUR', // Vatican - Euro
		'VC' => 'XCD', // Saint Vincent & Grenadines - East Caribbean Dollar
		'VE' => 'VEF', // Venezuela - Venezuelan Bolivar Fuerte
		'VG' => 'USD', // Virgin Islands (British) - US Dollar
		'VI' => 'USD', // Virgin Islands (USA) - US Dollar
		'VN' => 'VND', // Vietnam - Vietnamese Dong
		'VU' => 'VUV', // Vanuatu - Vanuatu Vatu
		'WF' => 'XPF', // Wallis and Futuna Islands - CFP Franc
		'WS' => 'WST', // Samoa - Samoan Tala
		'YE' => 'YER', // Yemen - Yemeni Rial
		'YT' => 'EUR', // Mayotte - Euro
		'ZA' => 'ZAR', // South Africa - South African Rand
		'ZM' => 'ZMK', // Zambia - Zambian Kwacha
		'ZW' => 'USD', // Zimbabwe - US Dollar
	);

	/**
	 * Returns a list containing the currency to be used for each country. The
	 * method implements a filter to allow altering the currency for each country,
	 * if needed.
	 *
	 * @return array
	 */
	protected function country_currencies() {
		return apply_filters('wc_aelia_currencyswitcher_country_currencies', $this->_country_currencies);
	}

	/**
	 * Returns a list containing all world currencies.
	 *
	 * @return array
	 */
	public static function world_currencies() {
		if(empty(self::$_world_currencies)) {
			// Initialise world currencies
			self::$_world_currencies = array(
				'AED' => __('United Arab Emirates dirham', Definitions::TEXT_DOMAIN),
				'AFN' => __('Afghan afghani', Definitions::TEXT_DOMAIN),
				'ALL' => __('Albanian lek', Definitions::TEXT_DOMAIN),
				'AMD' => __('Armenian dram', Definitions::TEXT_DOMAIN),
				'ANG' => __('Netherlands Antillean guilder', Definitions::TEXT_DOMAIN),
				'AOA' => __('Angolan kwanza', Definitions::TEXT_DOMAIN),
				'ARS' => __('Argentine peso', Definitions::TEXT_DOMAIN),
				'AUD' => __('Australian dollar', Definitions::TEXT_DOMAIN),
				'AWG' => __('Aruban florin', Definitions::TEXT_DOMAIN),
				'AZN' => __('Azerbaijani manat', Definitions::TEXT_DOMAIN),
				'BAM' => __('Bosnia and Herzegovina convertible mark', Definitions::TEXT_DOMAIN),
				'BBD' => __('Barbadian dollar', Definitions::TEXT_DOMAIN),
				'BDT' => __('Bangladeshi taka', Definitions::TEXT_DOMAIN),
				'BGN' => __('Bulgarian lev', Definitions::TEXT_DOMAIN),
				'BHD' => __('Bahraini dinar', Definitions::TEXT_DOMAIN),
				'BIF' => __('Burundian franc', Definitions::TEXT_DOMAIN),
				'BMD' => __('Bermudian dollar', Definitions::TEXT_DOMAIN),
				'BND' => __('Brunei dollar', Definitions::TEXT_DOMAIN),
				'BOB' => __('Bolivian boliviano', Definitions::TEXT_DOMAIN),
				'BRL' => __('Brazilian real', Definitions::TEXT_DOMAIN),
				'BSD' => __('Bahamian dollar', Definitions::TEXT_DOMAIN),
				'BTN' => __('Bhutanese ngultrum', Definitions::TEXT_DOMAIN),
				'BWP' => __('Botswana pula', Definitions::TEXT_DOMAIN),
				'BYR' => __('Belarusian ruble', Definitions::TEXT_DOMAIN),
				'BZD' => __('Belize dollar', Definitions::TEXT_DOMAIN),
				'CAD' => __('Canadian dollar', Definitions::TEXT_DOMAIN),
				'CDF' => __('Congolese franc', Definitions::TEXT_DOMAIN),
				'CHF' => __('Swiss franc', Definitions::TEXT_DOMAIN),
				'CLP' => __('Chilean peso', Definitions::TEXT_DOMAIN),
				'CNY' => __('Chinese yuan', Definitions::TEXT_DOMAIN),
				'COP' => __('Colombian peso', Definitions::TEXT_DOMAIN),
				'CRC' => __('Costa Rican colón', Definitions::TEXT_DOMAIN),
				'CUC' => __('Cuban convertible peso', Definitions::TEXT_DOMAIN),
				'CUP' => __('Cuban peso', Definitions::TEXT_DOMAIN),
				'CVE' => __('Cape Verdean escudo', Definitions::TEXT_DOMAIN),
				'CZK' => __('Czech koruna', Definitions::TEXT_DOMAIN),
				'DJF' => __('Djiboutian franc', Definitions::TEXT_DOMAIN),
				'DKK' => __('Danish krone', Definitions::TEXT_DOMAIN),
				'DOP' => __('Dominican peso', Definitions::TEXT_DOMAIN),
				'DZD' => __('Algerian dinar', Definitions::TEXT_DOMAIN),
				'EGP' => __('Egyptian pound', Definitions::TEXT_DOMAIN),
				'ERN' => __('Eritrean nakfa', Definitions::TEXT_DOMAIN),
				'ETB' => __('Ethiopian birr', Definitions::TEXT_DOMAIN),
				'EUR' => __('Euro', Definitions::TEXT_DOMAIN),
				'FJD' => __('Fijian dollar', Definitions::TEXT_DOMAIN),
				'FKP' => __('Falkland Islands pound', Definitions::TEXT_DOMAIN),
				'GBP' => __('British pound', Definitions::TEXT_DOMAIN),
				'GEL' => __('Georgian lari', Definitions::TEXT_DOMAIN),
				'GGP' => __('Guernsey pound', Definitions::TEXT_DOMAIN),
				'GHS' => __('Ghana cedi', Definitions::TEXT_DOMAIN),
				'GIP' => __('Gibraltar pound', Definitions::TEXT_DOMAIN),
				'GMD' => __('Gambian dalasi', Definitions::TEXT_DOMAIN),
				'GNF' => __('Guinean franc', Definitions::TEXT_DOMAIN),
				'GTQ' => __('Guatemalan quetzal', Definitions::TEXT_DOMAIN),
				'GYD' => __('Guyanese dollar', Definitions::TEXT_DOMAIN),
				'HKD' => __('Hong Kong dollar', Definitions::TEXT_DOMAIN),
				'HNL' => __('Honduran lempira', Definitions::TEXT_DOMAIN),
				'HRK' => __('Croatian kuna', Definitions::TEXT_DOMAIN),
				'HTG' => __('Haitian gourde', Definitions::TEXT_DOMAIN),
				'HUF' => __('Hungarian forint', Definitions::TEXT_DOMAIN),
				'IDR' => __('Indonesian rupiah', Definitions::TEXT_DOMAIN),
				'ILS' => __('Israeli new shekel', Definitions::TEXT_DOMAIN),
				'IMP' => __('Manx pound', Definitions::TEXT_DOMAIN),
				'INR' => __('Indian rupee', Definitions::TEXT_DOMAIN),
				'IQD' => __('Iraqi dinar', Definitions::TEXT_DOMAIN),
				'IRR' => __('Iranian rial', Definitions::TEXT_DOMAIN),
				'ISK' => __('Icelandic króna', Definitions::TEXT_DOMAIN),
				'JEP' => __('Jersey pound', Definitions::TEXT_DOMAIN),
				'JMD' => __('Jamaican dollar', Definitions::TEXT_DOMAIN),
				'JOD' => __('Jordanian dinar', Definitions::TEXT_DOMAIN),
				'JPY' => __('Japanese yen', Definitions::TEXT_DOMAIN),
				'KES' => __('Kenyan shilling', Definitions::TEXT_DOMAIN),
				'KGS' => __('Kyrgyzstani som', Definitions::TEXT_DOMAIN),
				'KHR' => __('Cambodian riel', Definitions::TEXT_DOMAIN),
				'KMF' => __('Comorian franc', Definitions::TEXT_DOMAIN),
				'KPW' => __('North Korean won', Definitions::TEXT_DOMAIN),
				'KRW' => __('South Korean won', Definitions::TEXT_DOMAIN),
				'KWD' => __('Kuwaiti dinar', Definitions::TEXT_DOMAIN),
				'KYD' => __('Cayman Islands dollar', Definitions::TEXT_DOMAIN),
				'KZT' => __('Kazakhstani tenge', Definitions::TEXT_DOMAIN),
				'LAK' => __('Lao kip', Definitions::TEXT_DOMAIN),
				'LBP' => __('Lebanese pound', Definitions::TEXT_DOMAIN),
				'LKR' => __('Sri Lankan rupee', Definitions::TEXT_DOMAIN),
				'LRD' => __('Liberian dollar', Definitions::TEXT_DOMAIN),
				'LSL' => __('Lesotho loti', Definitions::TEXT_DOMAIN),
				'LTL' => __('Lithuanian litas', Definitions::TEXT_DOMAIN),
				'LYD' => __('Libyan dinar', Definitions::TEXT_DOMAIN),
				'MAD' => __('Moroccan dirham', Definitions::TEXT_DOMAIN),
				'MDL' => __('Moldovan leu', Definitions::TEXT_DOMAIN),
				'MGA' => __('Malagasy ariary', Definitions::TEXT_DOMAIN),
				'MKD' => __('Macedonian denar', Definitions::TEXT_DOMAIN),
				'MMK' => __('Burmese kyat', Definitions::TEXT_DOMAIN),
				'MNT' => __('Mongolian tögrög', Definitions::TEXT_DOMAIN),
				'MOP' => __('Macanese pataca', Definitions::TEXT_DOMAIN),
				'MRO' => __('Mauritanian ouguiya', Definitions::TEXT_DOMAIN),
				'MUR' => __('Mauritian rupee', Definitions::TEXT_DOMAIN),
				'MVR' => __('Maldivian rufiyaa', Definitions::TEXT_DOMAIN),
				'MWK' => __('Malawian kwacha', Definitions::TEXT_DOMAIN),
				'MXN' => __('Mexican peso', Definitions::TEXT_DOMAIN),
				'MYR' => __('Malaysian ringgit', Definitions::TEXT_DOMAIN),
				'MZN' => __('Mozambican metical', Definitions::TEXT_DOMAIN),
				'NAD' => __('Namibian dollar', Definitions::TEXT_DOMAIN),
				'NGN' => __('Nigerian naira', Definitions::TEXT_DOMAIN),
				'NIO' => __('Nicaraguan córdoba', Definitions::TEXT_DOMAIN),
				'NOK' => __('Norwegian krone', Definitions::TEXT_DOMAIN),
				'NPR' => __('Nepalese rupee', Definitions::TEXT_DOMAIN),
				'NZD' => __('New Zealand dollar', Definitions::TEXT_DOMAIN),
				'OMR' => __('Omani rial', Definitions::TEXT_DOMAIN),
				'PAB' => __('Panamanian balboa', Definitions::TEXT_DOMAIN),
				'PEN' => __('Peruvian nuevo sol', Definitions::TEXT_DOMAIN),
				'PGK' => __('Papua New Guinean kina', Definitions::TEXT_DOMAIN),
				'PHP' => __('Philippine peso', Definitions::TEXT_DOMAIN),
				'PKR' => __('Pakistani rupee', Definitions::TEXT_DOMAIN),
				'PLN' => __('Polish złoty', Definitions::TEXT_DOMAIN),
				'PRB' => __('Transnistrian ruble', Definitions::TEXT_DOMAIN),
				'PYG' => __('Paraguayan guaraní', Definitions::TEXT_DOMAIN),
				'QAR' => __('Qatari riyal', Definitions::TEXT_DOMAIN),
				'RON' => __('Romanian leu', Definitions::TEXT_DOMAIN),
				'RSD' => __('Serbian dinar', Definitions::TEXT_DOMAIN),
				'RUB' => __('Russian ruble', Definitions::TEXT_DOMAIN),
				'RWF' => __('Rwandan franc', Definitions::TEXT_DOMAIN),
				'SAR' => __('Saudi riyal', Definitions::TEXT_DOMAIN),
				'SBD' => __('Solomon Islands dollar', Definitions::TEXT_DOMAIN),
				'SCR' => __('Seychellois rupee', Definitions::TEXT_DOMAIN),
				'SDG' => __('Sudanese pound', Definitions::TEXT_DOMAIN),
				'SEK' => __('Swedish krona', Definitions::TEXT_DOMAIN),
				'SGD' => __('Singapore dollar', Definitions::TEXT_DOMAIN),
				'SHP' => __('Saint Helena pound', Definitions::TEXT_DOMAIN),
				'SLL' => __('Sierra Leonean leone', Definitions::TEXT_DOMAIN),
				'SOS' => __('Somali shilling', Definitions::TEXT_DOMAIN),
				'SRD' => __('Surinamese dollar', Definitions::TEXT_DOMAIN),
				'SSP' => __('South Sudanese pound', Definitions::TEXT_DOMAIN),
				'STD' => __('São Tomé and Príncipe dobra', Definitions::TEXT_DOMAIN),
				'SYP' => __('Syrian pound', Definitions::TEXT_DOMAIN),
				'SZL' => __('Swazi lilangeni', Definitions::TEXT_DOMAIN),
				'THB' => __('Thai baht', Definitions::TEXT_DOMAIN),
				'TJS' => __('Tajikistani somoni', Definitions::TEXT_DOMAIN),
				'TMT' => __('Turkmenistan manat', Definitions::TEXT_DOMAIN),
				'TND' => __('Tunisian dinar', Definitions::TEXT_DOMAIN),
				'TOP' => __('Tongan paʻanga', Definitions::TEXT_DOMAIN),
				'TRY' => __('Turkish lira', Definitions::TEXT_DOMAIN),
				'TTD' => __('Trinidad and Tobago dollar', Definitions::TEXT_DOMAIN),
				'TWD' => __('New Taiwan dollar', Definitions::TEXT_DOMAIN),
				'TZS' => __('Tanzanian shilling', Definitions::TEXT_DOMAIN),
				'UAH' => __('Ukrainian hryvnia', Definitions::TEXT_DOMAIN),
				'UGX' => __('Ugandan shilling', Definitions::TEXT_DOMAIN),
				'USD' => __('United States dollar', Definitions::TEXT_DOMAIN),
				'UYU' => __('Uruguayan peso', Definitions::TEXT_DOMAIN),
				'UZS' => __('Uzbekistani som', Definitions::TEXT_DOMAIN),
				'VEF' => __('Venezuelan bolívar', Definitions::TEXT_DOMAIN),
				'VND' => __('Vietnamese đồng', Definitions::TEXT_DOMAIN),
				'VUV' => __('Vanuatu vatu', Definitions::TEXT_DOMAIN),
				'WST' => __('Samoan tālā', Definitions::TEXT_DOMAIN),
				'XAF' => __('Central African CFA franc', Definitions::TEXT_DOMAIN),
				'XCD' => __('East Caribbean dollar', Definitions::TEXT_DOMAIN),
				'XOF' => __('West African CFA franc', Definitions::TEXT_DOMAIN),
				'XPF' => __('CFP franc', Definitions::TEXT_DOMAIN),
				'YER' => __('Yemeni rial', Definitions::TEXT_DOMAIN),
				'ZAR' => __('South African rand', Definitions::TEXT_DOMAIN),
				'ZMW' => __('Zambian kwacha', Definitions::TEXT_DOMAIN),
			);
		}
		return apply_filters('wc_aelia_currencyswitcher_world_currencies', self::$_world_currencies);
	}

	/**
	 * Returns the Currency used in a specific Country.
	 *
	 * @param string country_code The Country Code.
	 * @return string A Currency Code.
	 */
	public function get_country_currency($country_code) {
		$country_currencies = $this->country_currencies();
		return get_value($country_code, $country_currencies);
	}

	/**
	 * Returns the Currency used in the Country to which a specific IP Address
	 * belongs.
	 *
	 * @param string host A host name or IP Address.
	 * @param string default_currency The Currency to use as a default in case the
	 * Country currency could not be detected.
	 * @return string|bool A currency code, or False if an error occurred.
	 */
	public function get_currency_by_host($host, $default_currency) {
		$ip2location = IP2Location::factory();
		$country_code = $ip2location->get_country_code($host);

		$this->logger()->debug(__('Attempting to select currency by geolocation.', Definitions::TEXT_DOMAIN),
													 array(
														"Host" => $host,
														"Detected country code" => $country_code,
													));

		if($country_code === false) {
			$this->logger()->info(__('Geolocation failed, selecting default currency.', Definitions::TEXT_DOMAIN),
														array(
															'Host' => $host,
															'Geolocation errors' => $ip2location->get_errors(),
															'Default currency' => $default_currency,
														));
			return $default_currency;
		}

		$country_currency = $this->get_country_currency($country_code);

		if(WC_Aelia_CurrencySwitcher::settings()->is_currency_enabled($country_currency)) {
			return $country_currency;
		}
		else {
			return $default_currency;
		}
	}

	/**
	 * Given a currency code, it returns the currency's name. If currency is not
	 * found amongst the available ones, its code is returned instead.
	 *
	 * @param string currency The currency code.
	 * @return string
	 */
	public static function get_currency_name($currency) {
		$available_currencies = get_woocommerce_currencies();
		return get_arr_value($currency, $available_currencies, $currency);
	}

	/**
	 * Given an array of currency codes, it returns an array of
	 * currency code => currency name pairs.
	 *
	 * @param array currencies An array of currency codes.
	 * @return array An array of currency code => currency name pairs.
	 * @since 4.1.0.150701
	 */
	public static function get_currency_names(array $currencies) {
		$available_currencies = get_woocommerce_currencies();
		$result = array();
		foreach($currencies as $currency) {
			$result[$currency] = get_arr_value($currency, $available_currencies, $currency);
		}
		return $result;
	}

	/**
	 * Factory method.
	 *
	 * return WC_Aelia_Currencies_Manager
	 */
	public static function factory() {
		return new self();
	}
}
