<?php if(!defined('ABSPATH')) exit; // Exit if accessed directly

/***
 * Tweaks to maintain backward compatibility with integrations and 3rd party
 * plugins.
 */

/* Expose some of the main classes to the root namespace.
 * This is needed because external code may refer to some of the classes
 * directly, without taking the new namespaces into account. By creating an
 * alias for the classes in the root namespace, the code that refers them will
 * keep working.
 *
 * IMPORTANT: this is a temporary workaround, which will be removed in a future
 * release.
 */
//class_alias('\Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencyPrices_Manager', 'WC_Aelia_CurrencyPrices_Manager');
class_alias('\Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher', 'WC_Aelia_CurrencySwitcher');
