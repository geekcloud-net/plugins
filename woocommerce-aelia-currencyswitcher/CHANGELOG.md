# Aelia Currency Switcher - Change Log

## Version 4.x
####4.5.17.180404
* Fix - Fixed display of variation prices on variable product pages.
* Fix - Fixed active currency when saving order meta.

####4.5.16.180307
* Tweak - Removed redundant logger class and optimised logging logic.
* Fix - Fixed name of `<select>` field in the currency selector widget.
* Feature - Added new filter `wc_aelia_cs_force_currency_by_country`.

####4.5.15.180222
* Tweak - Added new filter `wc_aelia_cs_load_order_edit_scripts`.

####4.5.14.180122
* Fix - Removed notice with Grouped Products.
* Improvement - Added admin message to inform merchants that Yahoo! Finance has been discontinued.

####4.5.13.180118
* Update - Discountinued Yahoo! Finance provider.
* Feature - Added interface with OFX exchange rates service.
* Feature - Added new filter `wc_aelia_cs_exchange_rates_models`.

####4.5.12.171215
* Fix - Fixed bug that sometimes caused an infinite loop when processing refunds on WooCommerce 3.2.5 and newer.

####4.5.11.171210
* Fix - Fixed logic used to collect refund data for reports.

####4.5.10.171206
* Improvement - Improved performance of the logic used to handle variable products.

####4.5.9.171204
* Tweak - Improved compatibility of geolocation logic with WooCommerce 3.2.x.

####4.5.8.171127
* Fix - Fixed integration with BE Table Rates Shipping plugin, to ensure the conversion of "subtotal" thresholds.

####4.5.7.171124
* Fix - Fixed "force currency by country" logic. The new logic makes sure that the "currency by country" takes priority over other selections.
* Improvement - Refactored logic used to show error messages related to the currency selector widget.
* Tweak - Added warning in the currency selector widget when the "force currency by country" option is enabled, to inform the site administrators that the manual currency selection has no effect.

####4.5.6.171120
* Fix - Fixed pricing filter in WooCommerce 3.2.4. The filter range was no longer converted, due to an undocumented breaking change in WooCommerce.

####4.5.5.171114
* Tweak - Added check to prevent the "force currency by country" option from interfering with the manual creation of orders.
* Tweak - Added possibility to specify the currency to be used during Admin operations, such as Edit Order.

####4.5.4.171109
* Tweak - Applied further optimisations to the installation process, to make it run in small steps and minimise the risk of timeouts.

####4.5.3.171108
* Tweak - Improved compatibility of installation process with WP Engine and other managed WP hosts. The process now runs step by step, reducing the chance of timeouts and 502 errors.

####4.5.2.171019
* Tweak - Improved settings page to make it clearer that the Open Exchange Rates service requires an API key.

####4.5.1.171012
* Fix - Removed notice related to the conversion of shipping in WooCommerce 3.2.

####4.5.1.170912
* Fix - Improved logic used to ensure that minicart is updated when the currency changes, to handle the new "hashed" cart fragment IDs.

####4.5.0.170901
* Improved compatibility with WooCommerce 3.2:
	* Altered conversion of shipping costs and thresholds to support the new logic in WC 3.2.

####4.4.21.170830
* Fixed conversion of shipping costs in WooCommerce 3.1.2.

####4.4.20.170807
* Fixed display of coupon amounts in the WooCommerce > Coupons admin page.

####4.4.19.170602
* Feature - New `wc_aelia_cs_get_product_base_currency` filter.

####4.4.18.170517
* Improved compatibility with WooCommerce 3.0.x:
	* Removed legacy code that could trigger a warning.

####4.4.17.170512
* Improved compatibility with WooCommerce 3.0.x:
	* Added workaround to issue caused by the new CRUD classes always returning a currency value, even when the order has none associated.

####4.4.16.170424
* Improved compatibility with WooCommerce 3.0.x:
	* Fixed handling of coupons. Altered logic to use the new coupon hooks.
* Fixed issue of stale data displayed in the mini-cart. Added logic to refresh the mini-cart when the currency is selected via the URL.

####4.4.15.170420
* Improved compatibility with WooCommerce 3.0.3:
	* Added logic to ensure that orders are created in the correct currency in the backend.
* Improved backward compatibility of requirement checking class. Added check to ensure that the parent constructor exists before calling it.

####4.4.14.170415
* Improved performance of reports and dashboard.

####4.4.13.170408
* Fixed bug in logic used to retrieve exchange rates. When the configured exchange rate provider could not be determined, the original logic tried to load an invalid class.
* Set default provider to Yahoo! Finance, to replace the unreliable WebServiceX.

####4.4.12.170407
* Improved compatibility with WooCommerce 3.0.1:
	* Fixed bug caused by WooCommerce 3.0.1 returning dates as objects, instead of timestamps.

####4.4.11.170405
* Improved compatibility with WooCommerce 3.0:
	* Fixed deprecation notice in Edit Order page.
* Fixed logic used to retrieve customer's country when the "force currency by country" option is active.

####4.4.10.170316
* Added new filter `wc_aelia_currencyswitcher_product_base_currency`.
* Changed permission to access the Currency Switcher options to "manage_woocommerce".

####4.4.9.170308
* Fixed minor warning on Product Edit pages.

####4.4.8.170306
* Improved compatibility with WooCommerce 2.7:
	* Replaced call to `WC_Customer::get_country()` with `WC_Customer::get_billing_country()` in WC 2.7 and newer.
* Updated requirement checking class.
* Improved user experience. Added links and information to configure the Currency Switcher.
* Improved Admin UI. Added possibility to sort the currencies from the Currency Switcher Admin page.

####4.4.8.170210
* Improved compatibility with WooCommerce 2.7 and 3rd party plugins:
	* Improved currency conversion logic to prevent affecting plugins that use `$product->set_price()` to override a product price.

####4.4.7.170202
* Improved compatibility with WooCommerce 2.7:
	* Fixed infinite recursion caused by the premature loading of order properties in the new DataStore class.
	* Added caching of orders, for optimised performance.
* Removed obsolete code.
* Improved logic to determine if a product is on sale. The new logic can fix incompatibility issues with 3rd party plugins, such as Bundles.

####4.4.6.170120
* Optimised performance of logic used for conversion of product prices.
* Removed integration with Dynamic Pricing plugin. The integration has been moved to a separate plugin.

####4.4.5.170118
* Updated integration with BE Table Rates Shipping plugin.

####4.4.2.170117
* Improved logger. Replaced basic WooCommerce logger with the more flexible Monolog logger provided by the AFC.

####4.4.1.170108
* Improved compatibility with WooCommerce 2.7:
	* Refactored currency conversion logic to follow the new guidelines.
	* Replaced obsolete filters.
	* Added support for the new logic for the conversion of variable products.

####4.4.0.161221
* Added compatibility with WooCommerce 2.7:
	* Added logic to use the new methods to access properties of products, orders and coupons.
* Updated logic used to force the currency depending on customer's country.
* Added new `wc_aelia_cs_default_selected_currency` filter, to allow overriding the currency used by default when the active currency cannot be determined.
* Added workaround for caching of variable product prices, to ensure that the correct prices are displayed when the exchange rates are updated.

####4.3.9.161104
* Fixed bug in Yahoo Finance integration. The bug prevented the exchange rates from being retrieved correctly in some circumstances.
* Replaced calls to jQuery.delegate() with jQuery.on().

####4.3.8.161028
* Improved installation process. The installation script that processes past orders can now handle better edge cases such as a site with many orders and a low memory limit.

####4.3.7.160816
* Fixed display of on sale/discounted prices with the Dynamic Pricing plugin is installed.

####4.3.6.160628
* Updated logger for compatibility with the new Monolog logger.

####4.3.5.160617
* Added handling of new exceptions introduced in WooCommerce 2.6. The new logic prevents WooCommerce from throwing a fatal error when an orphaned product variation is found.

####4.3.5.160610
* Added support for bulk edit of products' currency prices.

####4.3.5.160530
* Improved performance. Removed `woocommerce_get_children` filter.

####4.3.4.160527
* Improved compatibility with Table Rates Shipping by BolderElements. Added conversion of free shipping, minimun purchase and maximum purchase thresholds.
* Fixed bug in currency conversion function. The bug caused the conversion to use currency's default decimals when zero decimals were specified.

####4.3.3.160527
* Improved compatibility with Table Rates Shipping by BolderElements. Added handling of the new "shipping_free" attribute.
* Added new filter `wc_aelia_cs_shortcode_currency_amount`. The filter will allow to tweak the output of the `aelia_cs_currency_amount` shortcode.

####4.3.2.160408
* Improved checks for invalid exchange rates during plugin installation procedure.
* Fixed text domain reference.

####4.3.1.160328
* Fixed bug with minicart and "force currency by country". The bug prevented the minicart from updating automatically when the "force currency by country" option was selected and the customer changed country using the selector widget.
* Optimised processing of past orders. Now the processing only takes into account the orders from the beginning of last year, instead of the 01/01/2014.

####4.3.0.160302
* Added new `aelia_cs_currency_amount` shortcode. The shortcode will allow to convert any arbitrary amount displayed on a page.
* Added new `aelia_cs_pp_shortcode_price` filter. The filter will allow 3rd parties to alter the price displayed using the `aelia_cs_product_price` shortcode.

####4.2.22.160210
* WC 2.6 Compatibility - Added support for the new shipping logic. Leveraged new `woocommerce_shipping_zone_shipping_methods` filter to ensure that shipping methods' parameters are loaded in the correct currency.

####4.2.21.160202
* Fixed bug in handling of coupons' minimum and maximum prices. The bug prevented the limits from being converted properly, in some circumstances.

####4.2.20.160130
* Improved WooCommerce version detection in reporting manager class. The class now uses a different logic to find out which reports to use, and returns more information if an unsupported version is found.

####4.2.19.160118
* Fixed edge condition in handling of variable product prices. The condition caused the "from" price to appear as zero if a variation was set to use a base currency for which no prices were specified.

####4.2.18.160114
* Improved compatibility with WooCommerce 2.5:
	* Added logic to ensure the correct calculation of shipping formulas when they depend on cart total.
* Updated clearfix CSS for better compatibility with 3rd party plugins.

####4.2.17.160105
* Restored currency conversion logic (it was disabled for a test).

####4.2.16.151221
* Fixed bug in Yahoo Finance exchange rates provider. The bug caused some currency codes to be misinterpreted and prevented the related FX rates from being fetched.

####4.2.15.151214
* Removed integration with Bundles plugin. The integration is now available as a separate download from Aelia website (http://aelia.co/shop/bundles-integration-currency-switcher/).
* Extended `WC_Aelia_CurrencyPrices_Manager::convert_product_price_from_base()` method. The method now accepts a product and a price type, which are passed to the `wc_aelia_cs_convert_product_price` filter.
* Fixed rendering of prices for grouped products.
* Updated language files.

####4.2.14.151208
* Fixed currency code for Bulgarian Lev.
* Optimised currency prices manager. Added caching of base currency.

####4.2.13.151116
* Removed redundant code. The code was used to set customer's currency when viewing an existing order, but it's no longer required by current architecture.
* Refactored calls to `Aelia\WC\Order::get_order_currency()`. The method doesn't accept arguments anymore, for compatibility with parent class' method signature. Calls to the method have been updated to reflect such change.

####4.2.12.151105
* Added new `aelia_cs_product_price` shortcode. The shortcode allows to display a product price in a currency of choice.
* Optimised logic used to handle coupons. Coupon processing is now skipped when the base currency is active (the conversion would be ineffective, anyway).
* Added new `WC_Aelia_CurrencyPrices_Manager::convert_product_price_from_base()` method. The new method will be used exclusively to convert product prices, and it uses a filter to allow 3rd parties to modify the result of the conversion.


####4.2.11.151028
* Optimised performance of currency conversion logic. The conversion callbacks are now invoked directly, instead of using `call_user_func()`.
* Fixed UI conflicts with EU VAT Assistant (JavaScript and CSS).

####4.2.10.151026
* Improved robustness of `WC_Aelia_CurrencySwitcher::currencyprices_manager()` method. The method can now initialise the currency prices manager class automatically.
* Modified coupon handling hook to handle edge conditions. In some cases (e.g. customisation, 3rd party plugins, etc), the coupon hook could be triggered before the `woocommerce_loaded` event. The new logic handles such case, initialising the pricing manager on the fly.

####4.2.9.150930
* Fixed bug in notification emails. The bug caused order notification emails to display the wrong currency symbol, in some circumstances.

####4.2.8.150917
* Improved support for caching in WooCommerce 2.4.7:
	* Added logic to clear products cache when Currency Switcher settings are saved. This will ensure that the product prices will be recalculated using the latest settings.

####4.2.7.150914
* Fixed caching issue caused by WooCommerce 2.4.7:
	* Added logic to ensure that the variation prices are loaded and cached in the correct currency.

####4.2.6.150911
* Fixed caching issue caused by WooCommerce 2.4:
	* Added logic to clear product cache when exchange rates are updated. This will ensure that product prices are calculated using the new rates.

####4.2.5.150907
* Fixed bug caused by WooCommerce 2.4:
	* Implemented brand new logic to bulk edit variations' currency prices.

####4.2.4.150824
* Fixed bug caused by WooCommerce 2.4:
	* Altered code to handle the new Ajax action triggered when the order is reviewed at checkout.
* Improved compatibility with Dynamic Pricing plugin. Added support for new logic used to handle Role based discounts.

####4.2.3.150818
* Added missing localisation string for world currencies.

####4.2.2.150815
* Fixed issue caused by new Variable Products logic in WC2.4. Added new method to determine when the plugin is running on a frontend page.
* Fixed edge condition in recalculation of mini-cart totals. The condition was caused by an incorrect chain of events triggered by an Ajax call, which prevented the mini-cart total from being calculated correctly.
* Updated requirements. Plugin now requires AFC 1.6.3.15815 or newer.
* Added new `wc_aelia_cs_exchange_rates_updated` action.
* Replaced currency symbols with currency codes in Product Edit page.

####4.2.1.150813
* Fixed issue caused by new Variable Products logic in WC2.4. The new logic added to variable products discards variations when their price in base currency is empty. This caused products with a different base currency to show up without prices.
* Replaced all WooCommerce version checks with calls to `aelia_wc_version_is()`.

####4.2.0.150813
* Fixed bug in logic used to determine if a product is on sale. The bug caused sale's start date to be ignored, putting products on sale prematurely.
* New feature: per-currency coupon options. It's now possible to set coupon parameters for each currency.
* Fixed bug in Yahoo! Finance provider. The bug cause the exchange rates not to be updated in some circumstances.

####4.1.4.150810
* Added new `wc_aelia_cs_product_base_currency` filter. The filter allows to retrieve the base currency associated to a product.
* Added new filter for variable product prices transient key. The filter ensures that the transient key takes the currency into account, and fixes the issue which caused variable product prices to be displayed incorrectly.

####4.1.3.150730
* Improved support for WooCommerce 2.4:
	* Added support for the new logic used to save variations.
* Fixed minor bug in Bundles integration. Removed recalculation of product prices when "per item pricing" option is enabled.
* Added recalculation of mini cart totals on Ajax requests for the cart widget.

####4.1.2.150729
* Fixed issue caused by Bundled Products plugin. The Bundles plugin was changed in a non-backward compatible way, causing some product prices to show as zero.

####4.1.1.150706
* Added new Added new `wc_aelia_cs_converted_amount` filter.

####4.1.0.150701
* Implemented reports by currency in WooCommerce 2.2 and 2.3:
	* Added new UI elements to select the currency.
	* Implemented logic to only retrieve data for the selected currency.

####4.0.11.150625
* Added new `wc_aelia_cs_convert_product_price` filter.
* Added new `wc_aelia_cs_selected_currency` filter.

####4.0.10.150625
* Fixed logic to determine the active currency on Order Edit page. The currency was incorrectly changed to the one in user's profile when the "Force currency by billing country" was active.

####4.0.9.150619
* Fixed logic to determine if a product in on sale. The new logic correctly determines the "on sale" state for variable products.
* Improved Admin UI in WooCommerce 2.3.
* Fixed minor bug in the dashboard widget in WooCommerce 2.3.

####4.0.8.150610
* Fixed bug in Yahoo Finance provider class. The bug caused the fetching process to halt if the provider was used when only the base currency was enabled.
* Fixed table prefixes in auto-update method `WC_Aelia_CurrencySwitcher_Install::update_to_3_6_36_150603`.
* Added logic to prevent conversion of a zero amount.

####4.0.7.150604
* Added recalculation of refund total in base currency for refunds during auto-update process.

####4.0.6.150604
* Fixed calculation of refund data for reports.
* Fixed reports in WooCommerce 2.2 and 2.3.
* Fixed calculation in base currency of `discount_amount` for order items meta.
* Replaced session variables with cookies.

####4.0.5.150522
* Modified text explaining why the *product base currency* feature is disabled in WC 2.2 and earlier.

####4.0.4.150507
* Added logic to store of selected currency in a cookie.

####4.0.3.150420
* Fixed bug with WooCommerce 2.3. The bug prevented the cart from being restored when a customer logged out and then logged back in.
* Fixed bug in saving of the selected currency between user sessions. The bug caused the selected currency to be overwritten by the geo-detected one when a customer logged in.
* Fixed incompatibility with Quick View feature of Flatsome theme. The feature loads the products in a way that prevented the Currency Switcher from converting the prices correctly for variable products.
* Improved support for coupons:
	* Added support for *maximum_amount* property.
	* Added support for legacy *amount* property.

####4.0.2.150417
* Fixed XSS vulnerability that affected the plugin when used with WooCommerce 2.1.

####4.0.1.150405
* Fixed bug in rendering of currency and customer country selector widgets.

####4.0.2.150324
* Removed action for `woocommerce_cart_loaded_from_session` hook.

####4.0.1.150318
* Modified error codes to prevent conflicts.
* Improved plugin settings UI.
* Added thousand and decimal separator settings for each currency.

####4.0.0.150311
* Added possibility to force the currency based on shipping price.
* Removed filter for `woocommerce_get_formatted_order_total` hook.

## Version 3.x

####3.7.22.140227
* Enabled *product base currency* feature on variable products (WooCommerce 2.3 and later).

####3.7.21.150217
* Improved compatibility with WooCommerce 2.3:
	* Added logic to fix undocumented breaking change in `WC_Coupon` class.

####3.7.20.150204
* Removed Google Analytics integration. The GA plugin provided by WooThemes now keeps track of the currency.

####3.7.19.150201
* Fixed warning in the integration with Dynamic Pricing plugin.

####3.7.18.150128
* Improved installation process. The processing of past orders placed in shop base currency is now faster.

####3.7.17.150126
* Merged changes from v3.6.14.140122:
	* Fixed bug in loading of product pricing metadata, introduced by an incorrect patch in v3.6.13.140122.
	* Added new `wc_aelia_currencyswitcher_prices_type_field_map` filter.
	* Added filter for `woocommerce_get_variation_price` hook.
	* Removed *Product Base Currency* feature from Variable Products. Ref. http://aelia.co/2015/01/21/important-bug-product-base-currency-feature/
* Removed calls to functions and filters deprecated in WooCommerce 2.1 and later:
	* Replaced deprecated filter `woocommerce_available_shipping_methods` with `woocommerce_package_rates`.
	* Removed call to `woocommerce_get_page_id()`.
* Added exchange rates provider for Turkey Central Bank.

####3.7.16.150120
* Fixed bug related to Prices by Country. The bug caused the prices specified for a region to be discarded and overwritten by the base ones in some circumstances.

####3.7.15.150119
* Fixed bug related to "product base currency" feature and handling of product sale prices. The bug caused the product not to be considered "on sale", even when it was, when a product base currency was set to one different from shop base currency.
* Fixed bug in loading of decimal places setting for base currency. WooCommerce default decimals were always used instead of the ones set in Currency Switcher Options.

####3.7.14.150115
* Added Yahoo! Finance to the available providers of exchange rates.

####3.7.13.150114
* Fixed bugs in "product base currency" feature:
	* Fixed rendering of product pricing UI.
	* Fixed logic used to calculate product prices from product base prices.

####3.7.12.141230
* Removed Httpful library, now included in the AFC plugin.

####3.7.11.141224
* Refactored settings renderer. Class now used convenience methods to render the fields.

####3.7.10.141218
* Fixed bug in rendering of plugin's settings page.

####3.7.9.141208
* Moved base Exchange Rates Model class to Aelia Foundation Classes.
* Updated dependencies.
* Fixed calls to logger in `Aelia\WC\CurrencySwitcher\WC21\Reports` class.

####3.7.8.141103
* Fixed namespace of `Reports` class for WooCommerce 2.2.x.
* Removed WC1.6 legacy code.

####3.7.7.141021
* Improved performance of conversion of variable product prices.
* Removed support for WooCommerce 2.0.

####3.7.6.141017
* Improved logic used to initialise WooCommerce session. The new logic prevents redundant re-initialisations.

####3.7.5.141008
* Added tweaks to maintain backward compatibility with legacy 3rd party code and integrations.

####3.7.4.140908
* Replaced `WC_Aelia_CurrencySwitcher::VERSION` constant with static variable.

####3.7.3.140828
* Fixed plugin name and text domain definitions.

####3.7.2.140819
* Updated logic used to for requirements checking.

####3.7.1.140805
* Fixed bug in handling of exchange rate providers classes.
* Fixed namespace references.

####3.7.0.140731
* Major refactoring of the plugin to use the Aelia Foundation Classes.

####3.6.7.140114
* Improved admin UI. The message stating importance of entering proper exchange rates is now more prominent.

####3.6.6.140113
* Fixed bug in handling of misconfigured product prices. The bug occurred when a product base currency was set, but the corresponding prices were left empty. It caused the product price conversion to fail, and its prices to be left unaltered, regardless of the active currency.
* Fixed bug in currency selection. The bug caused the currency selection to be ignored until the page was refreshed.

####3.6.5.140108
* Fixed bug that caused `Settings::enabled_currencies()` to return an empty array.

####3.6.4.140103
* Fixed formatting of order totals in `My Account` page.
* Fixed currency decimals in `My Account` page.

####3.6.3.141229
* Improved Admin UI
	* Exchange rates settings are better organised.
	* Added script to show a tooltip when the "help" icon is clicked.
	* Replaced help icon file with font icon.
	* Renamed *Exchange Rates Settings section* to better reflect its purpose.

####3.6.2.141229
* Improved performance. Selected currency is now cached and reused, rather than being determine every time.

####3.6.1.141229
* Added functions to debug currency detection using geolocation.

####3.6.0.141226
* Added possibility to specify a base currency for each product.

####3.5.16.141224
* Added possibility to specify the position of the currency symbol for each currency.
* Added possibility to select a currency via a URL argument.

####3.5.15.141218
* Removed obsolete filter `woocommerce_order_shipping_to_display`.
* Removed obsolete method `WC_Aelia_CurrencySwitcher::get_shipping_to_display()`.

####3.5.14.141211
* Added handling of edge condition causing currency mixups when an order is resumed after switching to currency different from the one in which the order was originally placed.

####3.5.13.141210
* Fixed bug in the queries run by initial installation process.

####3.5.12.141209
* Improved semaphore logic used during auto-updates to reduce race conditions.
* Fixed error message for "invalid destination currency" error.
* Fixed rendering error on orders list page in the admin section on WooCommerce 2
* Added new `wc_aelia_currencyswitcher_settings_saved` action, triggered when plugin settings are saved.

####3.5.11.141205
* Rewritten logic used to render the currency symbol on the Order Edit page.
* Fixed minor notice in `Settings::set_exchange_rates_update_schedule()`.

####3.5.10.141203
* Fixed bug with conversion of product variations' prices. The bug caused variation prices to be hidden in some edge cases.

####3.5.9.141120
* Removed unneeded hooks.
* Improved compatibility with Gravity Forms Product Add-ons plugin.

####3.5.8.141112
* Simplified auto-update process. Reduced the amount of updates executed during first install.

####3.5.7.141030
* Fixed bug in rendering of "buttons" currency selector. The bug caused the widget to use currency names for both currency labels and codes. When the label were changed to something different from a valid currency code, the widget would not work properly anymore.

####3.5.6.141023
* Fixed bug in logic used to update exchange rates. The bug caused the symbol for base currency to be reset when "Save and update exchange rates" button was clicked.
* Modified Admin CSS to make section labels more legible.

####3.5.5.141017
* Improved Admin UI. Clarified the function of the "currency by billing country" option.
* Removed minor notice in WC2.1 and later, generated in `WC_Aelia_CurrencySwitcher::user_is_paying_existing_order()` due to use of page id "pay" (replaced by "checkout" since WC2.1).

####3.5.4.141002
* Fixed bug in handling of minimum order amount for free shipping.

####3.5.3.141001
* Added check to prevent mathematical conversion of empty product prices.

####3.5.2.140929
* Improved currency selector widget:
	* Fixed rendering of "buttons" widget.
	* Added filter for `wc_aelia_currencyswitcher_widget_currency_options` hook, to ensure that the "buttons" widget displays the currency codes by default.

####3.5.1.140926
* Fixed minor bug on settings page that caused a notice to be displayed when some exchange rates were left empty.
* Fixed minor bug on order payment page that caused a notice to be displayed in some circumstances.

####3.5.0.140911
* Improved compatibility with WooCommerce 2.2:
	* Fixed Taxes by Code report.
	* Added new `Aelia\CurrencySwitcher\WC21\Reports` class.
	* Added override for WooCommerce sales report in dashboard widget.

####3.4.17.140903
* Modified plugin to use the jQuery UI Tabs script included with WordPress.

####3.4.16.140902
* Improved warning message displayed when a past order was placed in a currency which is not enabled when the auto-update process runs.

####3.4.15.140828
* Added `WC_Aelia_CurrencySwitcher::base_currency()` method.
* Replaced references to `self::settings()->base_currency` with `$this->base_currency()`.

####3.4.14.140824
* Added processing of widget titles through localisation functions.

####3.4.13.140820
* Removed duplicates from result returned by `WC_Aelia_CurrencySwitcher_Settings::get_enabled_currencies()`.

####3.4.12.140819
* Forced loading of WooCommerce session even when cart is empty. This is necessary for the currency selector to work properly.

####3.4.11.140818
* Added new filters:
	* Filter `wc_aelia_currencyswitcher_product_currency_prices` will simplify integration with Prices by Country plugin.
	* Filter `wc_aelia_cs_convert` can be invoked by 3rd parties to convert an amount from one currency to another, without having to invoke plugin's methods directly.

####3.4.10.140808
* Added check for core report file existence, to prevent warnings when external reports are added.

####3.4.9.140806
* Fixed bug in payment of existing orders. Bug caused the wrong currency to be picked, in some conditions.

####3.4.7.140803
* Fixed method `WC_Aelia_CurrencySwitcher::load_order_currency_settings()`, which failed to return the localisation parameters for JS scripts.
* Updated language files.

####3.4.6.140720
* Fixed bug in display of variation prices (regular and sale) in WooCommerce 2.1.12.

####3.4.5.140717
* Modified logic of "currency by billing country" feature, so that the default GeoIP currency is taken when a billing country uses an unsupported currency.
* Optimised semaphore logic used for auto-updates to improve performance.

####3.4.3.140717
* Added support for bulk edit of variation prices.

####3.4.2.140711
* Updated GeoIP database.
* Set visibility of `WC_Aelia_CurrencySwitcher::is_valid_currency()` to public, to simplify integration with addons.

####3.4.1.140707
* Fixed bug in conversion of coupons.
* Removed legacy function used for WooCommerce 1.6 (no longer supported).

####3.4.0.140706
* Added possibility to specify a currency symbol for each currency.
* Redesigned Currency Switcher Options page.
* Added world currencies to the list of the available currencies.

####3.3.14.140704
* Fixed bug in loading of auxiliary functions.
* Removed unneeded legacy functions.

####3.3.13.140704
* Fixed bug in handling of currency decimals.
* Removed unneeded warning message, originally displayed when the detected currency for a user was not amongst the enabled ones.

####3.3.12.140626
* Added "wc_aelia_ip2location_country_code" filter, to allow overriding the country code detected by the plugin.
* Updated Geolite database file.

####3.3.11.140619
* Improved compatibility with Subscription Integration Add-on.
* Fixed bug that caused incorrect formatting of prices when base currency was active.

####3.3.10.140615
* Fixed loading of JavaScript for Billing Country Selector widget.

####3.3.9.140613
* Fixed bug in JavaScript that handles the price filter widget.
* Improved UI for Currency Selection Options tab in plugin settings page.
* Fixed bug that caused the PayPal checkout to fail in some circumstances.

####3.3.8.140612
* Improved compatibility with Subscription Integration Add-on:
	* Hidden variation product prices.
* Added generic conversion function for unsupported product types.

####3.3.7.140611
* Fixed bug that caused order line totals in base currency not to be calculated in some circumstances.

####3.3.6.140530
* Fixed "Sales overview - This month's sales" report.

####3.3.5.140529
* Optimised auto-update logic to reduce the amount of queries.

####3.3.4.140528
* Fixed bug that raised a warning during geolocation resolution.

####3.3.3.140526
* Fixed bug that caused a notice error to appear in Dashboard/Recent Orders widget in WooCommerce 2.0.

####3.3.2.140520
* Fixed bug that prevented the display of price suffix for products on sale.
* Fixed CSS for currency selector widget (dropdown) and billing country widget (dropdown).
* Fixed bug in logic that stored the selected billing country in checkout page.

####3.3.1.140520
* Completed implementation of "currency by billing country" feature:
	* Added settings page.
	* Added check to determine if feature is enabled.
	* Added conditional loading of billing country selector widget.
* Added instructions on how to use the billing country selector widget.

####3.3.0.140513
* Implemented selection of currency using billing country.
	* Added billing country selector widget.
	* Added logic to store and retrieve the billing country.
* Cleaned up code.

####3.2.36.140519
* Added "wc_aelia_cs_coupon_types_to_convert" filter. This filter will allow to alter the list of coupon types whose amount should be converted in the selected currency.

####3.2.35.140512
* Updated plugin metadata, for integration with Mijireh multi-currency plugin.

####3.2.34.140511
* Improved logic to determine when to run auto-updates.

####3.2.33.140506
* Optimised auto-update code. Added check to improve performance and reduce the chances of deadlocks.
* Updated GeoIP database.

####3.2.32.140429
* Fixed bug that caused payment gateways to be filtered by active currency, rather than order currency, when paying for an existing order.

####3.2.30.140425
* Fixed minor bug that caused a notice message to appear on checkout page in some circumstances.

####3.2.29.140423
* Altered currency selector widget. Added "active" CSS class to the button matching the active currency.

####3.2.28.140414
* Improved error messages when plugin requirements are not met.

####3.2.27.140409
* Altered WC_Aelia_CurrencyPrices_Manager to facilitate integration with Subscriptions.

####3.2.26.140403
* Made plugin more flexible, so that it can be installed in a directory different from "woocommerce-aelia-currencyswitcher".
* Fixed bug in Customer List report.

####3.2.25.140331
* Removed minor "strict standards" messages.

####3.2.24.140327
* Defaulted all log messages as "debug" by default, to reduce the amount of logging.

####3.2.23.140326
* Refactored logging mechanism to remove conflict with WooCommerce auto-update.

####3.2.22.140324
* Added and modified filters:
	* Replaced "wc_currencyswitcher_product_admin_view_load" with "wc_aelia_currencyswitcher_product_pricing_view_load".
	* Replaced "wc_currencyswitcher_product_convert_callback" with "wc_aelia_currencyswitcher_product_convert_callback".
	* Added "wc_aelia_currencyswitcher_variation_product_pricing_view_load" and "wc_aelia_currencyswitcher_simple_product_admin_view_load".
* Refactored product pricing UI to allow integration with WooCommerce Subscriptions.
* Added new events:
	* "wc_aelia_currencyswitcher_recalculate_cart_totals_before", fired before the recalculation of cart totals.
	* "wc_aelia_currencyswitcher_recalculate_cart_totals_after", fired after the recalculation of cart totals.

####3.2.21.140320
* Extended Aelia_Order class to maintain compatibility with both WooCommerce 2.1.x and 2.0.x.

####3.2.20.140319
* Added explicit loading of WooCommerce Admin CSS when rendering plugin settings page.

####3.2.19.140319
* Corrected invalid reference to settings key in WC_Aelia_CurrencySwitcher_Settings class.
* Refactored logic used to instantiate a WooCommerce Logger.
* Removed notice messages.

####3.2.18.140318
* Added "wc_currencyswitcher_product_admin_view_load" filter, to allow overriding the views being loaded by WC_Aelia_CurrencyPrices_Manager class.
* Reduced the amount of logging when debug mode is disabled.

####3.2.17.140313
* Fixed display issues caused by jQuery Chosen library.

####3.2.16.140312
* Commented out SCRIPT_DEBUG line.

####3.2.15.140311
* Updated Chosen library.

####3.2.14.140310
* Improved compatibility with WooCommerce 2.1:
	* Fixed bug that prevented price suffix from being displayed for variable products.
* Improved error handling in Admin UI integration class.

####3.2.13.1402128
* Removed notice related to WooCommerce 2.1 incompatibilities.

####3.2.12.1402127
* Improved compatibility with WooCommerce 2.1:
	* Implemented "Sales by product" report.
	* Implemented "Sales by category" report.

####3.2.11.1402127
* Highlighted order total in base currency in orders list page, to clarify what it represents.
* Fixed display of order currency in order details page.
* Improved description of Exchange Rates Settings in plugin's options page.

####3.2.10.1402126
* Fixed calculation of order totals in base currency.
* Improved session management to making it more compatible with WooCommerce 2.1.x.
* Added display of order total in base currency in orders list page.

####3.2.8.1402124
* Implemented preliminary (partial) compatibility with Product Addons plugin:
	* The plugin is now preserving the absolute values of product addons (i.e. they are not converted) instead of discarding them when a product is added to the cart.
* Improved compatibility with Dynamic Pricing plugin:
	* Improved handling of global Advanced Category rules.

####3.2.7.1402123
* Improved compatibility with WooCommerce 2.1:
	* Updated session handler to use new methods introduced in WooCommerce 2.1.
	* Fixed bug in loading session data, caused by WooCommerce not setting the session cookie until the cart was loaded.

####3.2.6.1402121
* Improved compatibility with Dynamic Pricing plugin:
	* Improved handling of global Order Totals rules.
	* Improved handling of global Category rules.

####3.2.5.1402120
* Fixed bug in savings of settings on first install of the plugin.
* Improved validation of settings related to exchange rates providers.

####3.2.4.140219
* Added locking mechanism to prevent plugin's autoupdate code from causing race conditions.
* Improved logging of minor error conditions, such as an invalid IP address during geolocation detection.

####3.2.3.140218
* Fixed bug in rendering of "Add Variation" user interface on WooCommerce 2.0.x.
* Fixed bug in Aelia\Logger class.
* Added debug mode information in Support section, on plugin settings page.

####3.2.2.140218
* Improved compatibility with WooCommerce 2.1:
	* Fixed bug that caused prices for variable products to be displayed incorrectly when "I will enter prices inclusive of tax" and "display product prices excluding tax" were both enabled.

####3.2.1.140215
* Added logging mechanism.
* Fixed "on the fly" calculation of order tax and order shipping tax.
* Improved compatibility with WooCommerce 2.1:
	* Implemented logic to override standard reports to return consistent data in a multi-currency environment.
	* Implemented base sales report.
	* Implemented sales by date report.
	* Implemented customers list report.
	* Implemented tax by date report.
* Added autoupdate script to recalculate order values in base currency.

####3.2.0.140214
* Restructured reporting classes.
* Implemented override of "WooCommerce Status" dashboard widget.

####3.1.3.140214
* Improved compatibility with WooCommerce 2.1:
	* Fixed bug that prevented the checkout from completing correctly in some circumstances. Issue was caused by a clash with new WC_Order::get_order_currency() method.

####3.1.2.140213
* Fixed bug in calculation of min and max variation prices when the base currency is selected.

####3.1.1.140212
* Added filter "wc_aelia_currencyswitcher_country_currencies", to allow altering the currency associated with each country.

####3.1.0.140212
* Added possibility of choosing the currency when manually creating orders in the backend.
* Fixed bug in display of order currencies in Recent Orders dashboard widget.
* Improved compatibility with WooCommerce 2.1:
	* Fixed bug in display of prices for variable products in products list page, which was caused by the new logic added to WooCommerce 2.1.

####3.0.6.140212
* Removed total in base currency from Recent Orders dashboard widget for orders that were placed in base currency.

####3.0.5.140211
* Changed versioning system to reduce confusion.
* Added warning related to the compatibility with WooCommerce 2.1.
* Added handling of multiple IP addresses in X-Forwarded-For header.

####3.0.4.140210-WC21-Beta
* Improved compatibility with WooCommerce 2.1:
	* Added new filters in WC_Aelia_CurrencyPrices_Manager, to handle the new way prices are retrieved for variable products.

####3.0.3.140210-Beta
* Improved compatibility with WooCommerce 2.1:
	* Fixed bug in display of sale prices for variable products.

####3.0.2.140210-Beta
* Improved compatibility with WooCommerce 2.1:
	* Fixed issue in displaying order totals in orders list page.
	* Refactored logic to override WooCommerce integrations, to ensure proper loading of Google Analytics integration with support for multiple currencies.

####3.0.1.140210-Beta
* Improved compatibility with WooCommerce 2.1:
	* Modified Aelia_Order class to use WC2.1 functions to retrieve order currency, when available, while maintaining compatibility with WC2.0.x.
* Removed unneeded file.

####3.0.0.140206-Beta
* Improved compatibility with WooCommerce 2.1:
	* Added check for existence of WC_Google_Analytics.
