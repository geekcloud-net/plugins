=== WP-Lister Lite for Amazon ===
Contributors: wp-lab
Tags: amazon, woocommerce, integration, products, import, export
Requires at least: 4.2
Tested up to: 4.9.5
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

List products from WordPress on Amazon.

== Description ==

WP-Lister for Amazon integrates your WooCommerce product catalog with your inventory on Amazon.

= Features =

* list any number of items
* supports product variations
* supports all official Amazon category feeds
* supports Fulfillment By Amazon (FBA)
* import products from Amazon to WooCommerce
* view buy box price and competitor prices
* includes SKU generator tool

= More information and Pro version =

Visit <https://www.wplab.com/plugins/wp-lister-for-amazon/> to read more about WP-Lister and the Pro version - including documentation, installation instructions and user reviews.

WP-Lister Pro for Amazon will not only help you list items, but synchronize sales and orders across platforms and features an automatic repricing tool.

== Installation ==

1. Install WP-Lister for Amazon either via the WordPress.org plugin repository, or by uploading the files to your server.
2. After activating the plugin, visit the Amazon account settings page and follow our guide on [How to set up WP-Lister for Amazon](https://docs.wplab.com/article/85-first-time-setup).

== Frequently Asked Questions ==

= What are the requirements to run WP-Lister? =

WP-Lister requires a recent version of WordPress (4.2 or newer) and WooCommerce (2.2 or newer) installed. Your server should run on Linux and have PHP 5.3 or better with cURL support.

= Does WP-Lister support windows servers? =

No, and there are no plans on adding support for IIS.

= Are there any more FAQ? =

Yes, there are. Please check out our growing knowledgebase at <https://www.wplab.com/plugins/wp-lister-for-amazon/faq/>

== Changelog ==
= 0.9.7.8 =
* Fixed thousands separator on rounded prices (1,000 would show as 1) 
* Fixed the Create Order link not getting processed 
* Added part_number to the allowed columns for parent listings 
* Disabled the product counts by default to avoid rare performance issues on slow servers 

= 0.9.7.7 =
* Added MusicalInstruments feed template for Amazon FR, DE, CA, IT, ES 
* Added a setting option to enable the product counts on "On Amazon" and "Not on Amazon" views 
* Disabled the product counts by default to avoid rare performance issues on slow servers 
* Use number_format() instead of round() to fix rare precision issue 

= 0.9.7.6 =
* Improved feed generation performance by caching data for variable products 
* Improved security against CSRF (cross site request forgery) 
* Added wpla_order_builder_line_item filter 
* Added wpla_set_tracking_number_for_order and wpla_set_tracking_service_for_order filter hooks 
* Minor CSS fixes 

= 0.9.7.5 =
* Added product counts to the On Amazon and Not on Amazon filter options 
* Added option to enable offer images (aka condition images) in all ListingLoader feeds (Pro) 
* Added experimental option to load B2B templates (only Amazon UK and DE for now) (Pro) 
* Improved edit account page and added information about brand registry option 
* Tweaked "Feed currency format" option, which is now only active on EU sites using Euro (DE, FR IT, ES) and enabled by default 
* Reduce the line total when a discount is present to adjust the line tax accordingly 
* Store the total_tax item meta to fix the shipping display in the PDF Invoices and Packing Slips plugin 
* Fixed shipping tax bug where compound rates are being ignored 
* Fixed multiple issues related to importing products und updating imported products
* Fixed wrong quantity in Listings table if profile quantity override option was used 
* Fixed profile selection not reflecting the active profile on edit product page for variable products 
* Fixed and improved warning when user attempts to change SKU on child variations (and show no warning if SKU was empty) 
* Fixed issue where changing SKU on child variations would lead to duplicate variation listings with identical SKUs 

= 0.9.7.4 =
* Added feed templates for Amazon.es (Office, Luggage, ConsumerElectronics which replaces CE) 
* Show warning on Profiles page if template is meant for a different marketplace than a profile's account is linked to 
* Improved tooltip for repricing options (update interval, explain limit of 2400 items per hour) 
* Fixed issue when importing shipping taxes from Amazon 
* Fixed minor CSS issues on edit product page 
* Fixed possble PHP notices 
* Pass the account ID in WPLA_ListingsModel::processBuyBoxPricingResult() to fix possible error introduced in 0.9.7.3 
* Pass data parameter to the wpla_added_product_from_listing action hook 

= 0.9.7.3 =
* Added support for amazon.com.au (Australia) 
* Added missing PetSupplies and Luggage feed templates for Amazon DE (Haustierbedarf / Koffer) 
* Added support for the new WooCommerce Products Importer 
* Fixed issue where tax was doubled when imported from Amazon 
* Fixed issue where bulk actions on the SKU Generator would not work 
* Fixed issue getting the proper name of the shipping provider in WC Shipment Tracking v1.6.6+ 
* Fixed issue updating buy_box and lowest_price in the listings when using multiple accounts 
* Listings Table: Show custom profile price if set 
* Order creation: Set the total and save the order using WC_Order::set_total() and WC_Order::save() to trigger the currency converter action 
* Improved statename detection in created orders (remove accents from statename) 
* Make sure variation_theme column is empty in feeds for simple products 
* Trim excess whitespace before using nl2br() 
* Skip trashed listings when marking items as modified 
* Use new WPLA_ProductWrapper::getProductTitle() method in the WPLA_SkuGenTable class to display correct product titles 
* Tweaked Custom Product Description field: use wp_editor() instead of woocommerce_wp_textarea_input() to avoid escaping all HTML tags 

= 0.9.7.2 =
* Added option to download and upload listing profiles (backup and restore) 
* Added partial support to qTranslate to translate the title, description, bullet points and keywords based on the account's site code 
* Added Sequential Order Numbers support for FBA orders 
* Added setting to ignore product images and not add them to the feeds 
* Added wpla_build_feed action hook 
* Added wpla_account_locale filter hook 
* Added new amazon.com.br marketplace (experimental, not officially supported yet) 
* Allow profile fields to override the product price under certain conditions 
* Record shipping taxes that are outside the vat_rates array 
* Use WC_Product::get_title() instead of $product->post->post_title for best practice 
* Use wc_update_product_stock() over WC_Product::reduce_stock() which is now deprecated 
* Unset leadtime-to-ship feed column if quantity is empty to avoid errors in feeds 

= 0.9.7.1 =
* Fixed issue with sale price and/or sale start and end dates being empty in feed 
* Fixed issue where listings were not marked as changed when updated via WC REST API in WC 3.x 
* Fixed "WC_Product::get_total_stock is deprecated" warning 

= 0.9.7 =
* Reorganized the Tax options on the settings page 
* Added option to import sales tax data from Amazon orders 
* Added 'Create selected orders in WooCommerce' bulk action on orders page 
* Added wpla_custom_values filter hook to allow custom code snnippets to create custom product properties and attributes 
* Load column values using variation IDs if the current product is a variation 
* Load the variations using the product's parent ID (WC3+) 
* Strip all HTML tags from the product_title 
* Allow more valid parent columns for the Books feed template 
* Marked outdated US feed templates as deprecated (Lighting, Outdoors, removed TiresAndWheels from AutoAccessory template) 
* Fixed bug where the %%% placeholder is not getting processed when used at the start of the title 
* Fixed possible fatal error in the WC_Order class when 3rd party plugins update an order's status 
* Fixed issue where the shipping line was not showing taxes 
* Fixed issue where custom profile sale dates were replaced by default dates 
* Fixed issue with order creation date by replacing the MEST timezone with CEST so PHP can parse it 

= 0.9.6.41 =
* updated process of linking an Amazon seller account to WP-Lister to refrect recent changes on Seller Central
* load the product level feed columns for the parent product when dealing with variable products
* fixed an issue when pulling attached gallery images
* fixed an issue with variation product data on WooCommerc 2.x
* use native WC3.x methods to update the order date after updating its status to prevent the status update from resetting
* added filter hook wpla_order_can_be_fulfilled_via_fba

= 0.9.6.40 =
* improved compatibility with WooCommerce 3.x
* added the option to disable on-hold order emails
* store the currency first when creating orders to make the currency coverter work again
* do not touch the _price product meta when updating products from Amazon to make sale prices function properly
* changed the data column in the amazon_reports column to longblob
* fixed undefined variable error in FeedsPage

= 0.9.6.39 =
* improved compatibility with WooCommerce 3.x 
* fixed child variations not being able to access data in WC3.x 
* added Conditional Order Item Updates setting option to improve order fetching performance in some cases 
* added the wpla_order_post_data filter to allow 3rd-party code to hook into the process of creating orders in WooCommerce 

= 0.9.6.38 =
* improved compatibility with WooCommerce 3.x 
* update existing WC Order's status when importing orders from Amazon 
* added the ability to store multiple tax rates for a single line item 
* skip loading the ProductMatcher JS in the frontend/toolbar if the current user does not have the manage_amazon_listings capability 
* hide the Filter Orders notice on Lite users since they do not have access to that functionality 
* store the item description from the import CSV 
* added Custom feed template for Amazon UK 

= 0.9.6.37 =
* added warnings to the listings page and edit product page when SKUs that start with 0 are found 
* added support for WC Shipment Tracking v1.6.6 
* use the parent ID in variable products for the Edit Product links 
* make sure jQueryFileTree is loaded on the Edit Product screen 
* fixed selected shipping service on edit order page 
* do not auto-submit FBA orders if FBA is not enabled 
* removed “skipped node without parent” warnings during category update 
* improved WooCommerce 3.0 compatibility 
* tested on WordPress 4.8 

= 0.9.6.36 =
* added Software & Video Games feed template for amazon.ca 
* added 'wpla_shipping_method_label' filter hook to allow modification to the shipping labels 
* added 'wpla_added_product_from_listing' action hook 
* removed jQueryFileTree connectors due to a potential security issue 
* store the shipping tax line inside a 'total' index to make shipping taxes appear in the order items list 
* fixed order VAT rates not getting stored properly 
* fixed possible PHP warning on WC Email Settings page 
* fixed product gallery for variable products on WooCommerce 3.0 

= 0.9.6.35 =
* added “Material” to the list of destination attributes for Merge Variation Attributes option 
* fixed possible fatal error during feed generation (which could break cron job execution)
* fixed possible "Undefined index" warning (PHP Notice) when updating stock levels

= 0.9.6.34 =
* fixed issue where disabled emails are still sent on WooCommerce 3.0 
* fixed issue where Amazon metadata was not copied when duplicating a product on WooCommerce 3.0 
* fixed issue where quantity was only updated when an inventory report was processed twice 
* added option to include the shipment time when submitting an Order Fulfillment Feed 
* added tooltip to listing title to indicate whether an item was imported, matched or listed from WooCommerce 
* pull condition_type and condition_note from variations in variable products if set 

= 0.9.6.33 =
* added FoodAndBeverages feed template for amazon.it (Alimentari e cura della casa) 
* added 'wpla_skip_quantity_sync' filter to skip quantity sync via 3rd-party code 
* added 'wpla_product_matches_request_query' filter to allow 3rd-party code to alter the query before sending to Amazon 
* allow 0 to be saved in the Profile edit screen 
* allow 0 profile quantity to override the WC product's quantity 
* fixed autodetect taxes not working when no tax rate ID is set 
* fixed On Amazon filter being reset when using search form, direct pagination or other filter options on Products page 
* fixed fatal error when plugin updates check doesn't return a WP_Error object and an HTTP code other than 200 
* fixed “Sold Out” listing status if stock level is below zero 

= 0.9.6.32 =
* added LawnAndGarden (Jardin) feed template for amazon.fr 
* check for missing database tables and show warning on settings pages 
* updated tooltip for “Process daily inventory report” option (include warning about outdated data in reports) 
* remove amazon_stock_log table when uninstalling the plugin 
* minor layout fixes and improvements 

= 0.9.6.31 =
* added feed template for Collectible Coins US 
* show the Prime icon for orders using Amazon Prime 
* exclude changed and submitted items from having their stocks updated from the reports 
* fixed importing products without ASIN (where the Merchant report only contains SKU and UPC/EAN) 
* fixed issue where VAT would be incorrectly added to created WooCommerce orders if prices are entered without taxes 
* fixed profile editor not loading on PHP7.1 
* fixed importing book specific metadata from amazon.it 
* make sure the daily cron schedule is executed when wp-cron is broken and an external cron job is used (trigger daily cron by external cron if not executed for 36 hours) 
* improved daily schedule: show last run on tools and dev settings page, allow manual execution more often than once in 24 hours, log table cleaning results to log file 
* prevent accidentally removing all listings without a parent from Amazon by running the “Remove from Amazon” bulk action without any products selected 
* prevent resetting the shipment date to today when importing old shipped orders (like after restoring a backup) with both options to “Create Orders” in WooCommerce and to “Mark as shipped on Amazon” enabled 
* auto-detect staging site (if domain contains staging or wpstagecoach) and omit requesting daily reports on staging site 
* only update other listings with the same post/parent ID if there are more than one account set up 
* force all date/time functions to use UTC regardless, of local timezone (use gmdate instead of date, use ‘UTC’ suffix on strtotime) 
* show warning on accounts page if multiple accounts use the same Merchant ID and “Filter orders” option is disabled 
* importing orders: if "Filter orders" option is enabled, make sure an existing order is assigned to the right account_id 
* improved performance when importing orders by not updating pending feeds when updating other listings with the same post/parent ID 
* if invalid data is found show warning and offer to assign all found items to the default account 
* fixed fatal error in SDK class MarketplaceWebService/Model/ResponseHeaderMetadata.php (Redefinition of parameter $quotaMax) 
* improved log viewer 

= 0.9.6.30 =
* added MusicLoader feed template for Amazon UK 
* added support for defining a custom position for variation attribute values in listing title by using the placeholder ‘%%%’ 
* added the filter 'wpla_disable_fba_to_wc_stock_sync' to allow 3rd-party code to disable fba stock sync 
* allow orders to Canada (CA) to be fulfilled from FBA US (AMAZON_NA) 
* fixed a rare issue where the fulfillment_latency column was not being processed correctly 
* fixed the 'Select from Amazon' button to not working properly on Edit Product screen
* fixed an undefined variable warning when calculating FBA shipping (on checkout)

= 0.9.6.29 =
* fixed execution of background tasks (cron job) 

= 0.9.6.28 =
* added missing feed templates for amazon.it 
* added missing BookLoader feed template for Amazon IT, ES, DE, FR and CA 
* added staging site option on developer settings page 
* added support for additional variation images added by WooThumbs plugin 
* added 'wpla_repriced_products' hook that runs after products have been repriced 
* improved tooltip description for custom Amazon price field 
* listen for product updates made via the new WC REST API in WC 2.6+ 
* broadcast stock updates using wpla_product_has_changed to update other listings with the same post and parent ID 
* use MWS Orders API version 2013-09-01 to fetch orders and order line items 
* fixed saving settings on the Advanced Settings page on multisite installs 

= 0.9.6.27 =
* added In WooCommerce / Not in WooCommerce filter views on Orders page 
* added check for ASINs containing leading or trailing whitespace - and prompt user to fix it by clicking a button 
* added Shoes feed template for amazon.de (Schuhe & Handtaschen) - and fixed internal ID for Kitchen template 
* added various missing feed templates for Amazon ES 
* added feed templates for Amazon Japan 
* allow custom quantity in listing profile to overwrite WooCommerce quantity 
* allow orders to Puerto Rico (PR) to be fulfilled from FBA US 
* minimize delay when syncing sales from eBay to Amazon and allow an external cron job to run every minute 
* include only shipping date (not time) in ship-date column in Order Fulfillment Feed 
* fetch more orders at a time by using ListOrdersByNextToken request to process multi-page API results (increases maximum number of orders from 100 to 600 before throttling kicks in) 
* if product has no feature image assigned use first gallery image instead 
* switched recommended web cron service from CronBlast to EasyCron 
* improved processing of browse tree guides 
* store the tracking service and number when saving orders so they'll get submitted when an order gets marked as complete 
* store the currency before storing the order total to allow currency switcher to work when saving order totals 
* fixed issue where FBA items would not automatically fall back to seller fulfilled unless FBA Inventory Report is processed manually 
* fixed order date on WooCommerce 2.6 
* fixed possible PHP Notice "Undefined offset..." for invalid CSV data 
* fixed issue where stock levels on Listings page would not be reverted when an order was cancelled 

= 0.9.6.26 =
* added Eyewear feed template for Amazon UK 
* added option to store Amazon promotional discounts as WooCommerce order discounts 
* added an option to use the Amazon order number when displaying WooCommerce orders 
* fixed possible division by 0 warnings when creating orders in WooCommerce 
* fixed possible fatal error in wp-admin when Shipping Options are enabled 
* fixed possible issue where main product image was sent as variation image 
* disable the shipping options check when running wp-cron to prevent fatal error 
* improved storing stock log records 

= 0.9.6.25 =
* added options to set add-on FBA shipping fees 
* added Luggage feed template for Amazon UK 
* added Entertainment Collectibles browse tree guide for Amazon US 
* added button to repair crashed MySQL tables on developer tools page 
* fixed issue when importing SKUs with an apostrophe 

= 0.9.6.24 =
* fixed product prices in created WooCommerce orders for multiple units of the same product if auto detect tax rate option is disabled 
* fixed item count on Orders page for orders containing multiple units of the same product 

= 0.9.6.23 =
* added option to list/prepare a product and switch listing profile from the Edit Product screen's sidebar 
* added option to "Skip orders for foreign items" when creating orders in WooCommerce 
* added Toys and Baby feed templates for amazon.it (Giochi e giocattoli / Prima infanzia) 
* added support for Amazon Payments Advanced plugin 
* added admin-ajax.php action "wpla_request_inventory_report" to request Merchant Inventory Report via external cron job 
* added class wc_input_price to all price fields on edit product page to enable WooCommerce inline price validation 
* added option to enable/disable automatic tax calculation when creating orders in WooCommerce 
* fixed incorrect order date when creating orders in WooCommerce 2.6 
* fixed issue adding new variations to existing listings if more than one amazon account are set up 
* fixed PHP warning on WC2.6: Declaration of WPLA_Shipping_Method::calculate_shipping() should be compatible with WC_Shipping_Method::calculate_shipping() 
* fixed issue where VAT would be incorrectly added to created WooCommerce orders if prices are entered without taxes and the site has a tax rate set up for the shop's base address 
* fixed issue where customers without a default shipping address (e.g. guests) could not calculating the shipping costs from the cart page 
* fixed wp_remote_* calls which will be returning objects in WP 4.6 
* force merchant_shipping_group_name template field to be an optional text field (fixes issue with Clothing UK feed template where this field is incorrectly marked as required) 
* improved 'On Amazon' and 'Not on Amazon' product filters for large sites 
* improved stock logger - include information about class and method which triggered stock change 
* store optional gift message as order line meta field when creating order in WooCommerce 

= 0.9.6.22 =
* added support WooCommerce Additional Variation Images plugin when listing items on Amazon 
* fixed line totals and VAT amounts when creating WooCommerce orders for more than 1 purchased unit 

= 0.9.6.21 =
* added option to enable FBA Shipping Options on WooCommerce checkout page 
* calculate taxes based on the product's tax class and shipping address when creating orders in WooCommerce 
* improved tax information in created WooCommerce orders if prices are entered without tax 
* added editable eBay Price column to repricing tool 
* added "Remove from Amazon" bulk action on Products page 
* import "Important Information" section (with legal info) when using the "Fetch Full Description" bulk action 
* fixed pagination links loosing search box query on repricing page 
* fixed issue where eBay listing was not updated when variable product was purchased on Amazon 

= 0.9.6.20 =
* added AutoAccessory feed template for amazon.it 
* added MSRP column and discount percentages on repricing tool page 
* added experimental support for handling tracking details stored by WooForce Shipment Tracking plugin 
* added check for Solaris/SunOS - show warning message if running on Solaris 
* added FBA Inventory Health Report to list of reports which are processed automatically 
* allow sort by stock level on repricing page 
* skip parent variations when creating Price&Quantity feed 

= 0.9.6.19 =
* added support for processing FBA Inventory Health reports and show inventory age details in repricing tool 
* added support for custom order numbers when submitting MCF orders to be fulfilled via FBA 
* added support for WooCommerce Sequential Order Numbers Pro 1.7.0+ 
* added option to subtract the quantity sent to Amazon by the value entered as "Out Of Stock Threshold" in WooCommerce 
* added option to skip out of stock items when fetching lowest price data from Amazon 
* added option to display item condition and notes in the product page 
* added separate BuyBox filter on repricing page and changed lowest price filter to ignore BuyBox flag 
* added FBA inventory age filter on repricing page 
* added stock log tools page 
* added FoodAndBeverages feed template for amazon.fr 
* added Eyewear feed template for amazon.de 
* improved support for WooCommerce Product CSV Import Suite (fix issue where updated listings were not marked as changed) 
* improved processing FBA Inventory Reports: mark item as changed if FBA fallback is enabled and Fulfillment Center ID has changed 
* improved repricing algorithm - set ExcludeMe parameter for GetLowestOfferListingsForASIN request to make sure lowest offer data shows only prices from competitors, and enable repricing undercut for competitors prices 
* improved performance by caching account and marketplace data 
* improved displaying listing quality warnings in listings table 
* improved creating orders: convert country state names to ISO code (New South Wales -> NSW) (requires WC2.3+) 
* show FNSKU on listing page for FBA items 
* allow held orders to be submitted again to Amazon (submission status "hold") 
* update WooCommerce order status when Amazon order status has changed (Unshipped to Shipped) 
* fixed issue where product variations with missing parent products (corrupted database) would break feed generation process - show warning on Feeds page if this is detected 
* fixed search box on orders page 
* fixed profile shortcodes being ignored for price and sale price 
* fixed "not lowest price" filter on repricing tool showing items with no lowest price at all (no competitor and no buy box) 
* fixed blank details page if WooCommerce Product Reviews Pro plugin is active 
* fixed fetching feed processing results when there are more than 100 submitted/pending feeds 
* fixed error handling when submitting MCF orders to be fulfilled via FBA 
* fixed Error 99001: A value is required for the "manufacturer" field (for parent variations) 
* fixed Error 8560: Missing Attributes target_audience_keyword (for parent variations, Toys US) 
* fixed rare issue where variation_theme column would be left empty (Error 99006: A value is required in the "variation_theme" field...) 
* database upgrade to version 35 - change feed processing result column to MEDIUMTEXT (wp_amazon_feeds.results) 
* database upgrade to version 36 - increase field size for varchar columns in table wp_amazon_feed_tpl_data (fix incomplete profile data for some templates like Health FR) 

= 0.9.6.18 =
* added option to not use featured image from parent variation for child variations (avoid same swatch image for all child variation, disabled by default) 
* added Kitchen and Office feed template for amazon.de 
* added Clothing (Abbigliamento) feed template for amazon.it 
* added Amazon Logistics shipping provider 
* fixed Entertainment Collectibles feed template 
* remember invalid parent variation problems during import process and show warning message on edit product page 
* fixed fetching reports when there are more than 100 submitted/pending reports 
* fixed possible issue with Map Variation Attributes setting option 
* fixed issue where Main Image URL would show as "Required" when editing a profile 
* fixed empty quantity column issue if fulfillment_center_id is forced empty in profile - and make sure fulfillment_center_id column is set to Optional 

= 0.9.6.17 =
* added option to bulk remove min/max prices from min/max price wizard 
* added support for AMAZON_CA fulfillment center ID (experimental!) 
* improved checking for processed feeds - avoid feeds being stuck as submitted due to agressive caching plugins 
* improved SKU Generator tool: check existing SKUs and skip products where SKU generation would result in duplicates 
* make sure sale price stays within min/max boundaries - prevent Amazon from throwing price alert and deactivating the listing 
* do not use featured image from parent variation for child variations (avoid same swatch image for all childs) 
* renamed Feed ID to Batch ID and improved title on feed details page 
* removed deprecated feed template Miscellaneous (US) 
* fixed custom main_image_url setting on product level being ignored 
* fixed issue updating product details on PHP5.6 with Suhosin patch installed (and suhosin.post.disallow_nul option on) 
* fixed issue where cancelled orders were stuck as "Pending" (since LastUpdateDate apparently stays the same) 
* fixed SKU generator not showing and processing all missing SKUs (check for NULL meta values) 
* fixed missing success indicator and message when preparing items in bulk from Products page (or matching products or applying lowest prices in bulk) 

= 0.9.6.16 =
* trigger stock status notifications when reducing stock level 
* implemented batch mode for FBA inventory check tools 
* improved inventory check memory requirements - disable autoload for temp data (requires WP4.2+) 
* make sure ASINs have no leading or trailing spaces when creating matched listings from product 
* fixed importing listings with identical SKUs from multiple accounts / sites 
* fixed possible SQL error during import: Column 'post_content' cannot be null 
* fixed possible PHP error on edit account page if MWS credentials are incorrect and no marketplaces were found 
* fixed missing categories when processing multiple browse tree guides for the same feed template 
* fixed fatal error when using Min/Max Price Wizard to set prices based on sale price 
* fixed possible fatal error in Woo_ProductBuilder.php on line 1045 
* fixed fatal error: Redefinition of parameter $quotaMax (PHP7) 
* fixed possible fatal error in ListingsModel.php on line 1679 
* fixed narrow tooltips 
* fixed PHP warning on PHP7 

= 0.9.6.15 =
* improved performance on log and orders pages - database version 33 
* improved performance of processing FBA reports 
* improved inventory check tools - implemented batch processing and improved general performance 
* improved address format for in FBA submission feeds - if shipping_company is set, use company name as AddressFieldOne 
* improved error handling on import - if creating product failed (db insert) 
* improved logging: add history record when creating WC order manually 
* fixed search on repricing page 
* fixed support for multiple images in BookLoader feed template 
* fixed wpla_mcf_enabled_order_statuses filter hook not working for automatic FBA submission (only manual) 
* fixed possible fatal error when processing FBA inventory report (Call to a member function set_stock_status() on a non-object) 
* fixed empty log records being created when checking for reports 
* added "Allow direct editing" developer option - hide Edit listing link by default 
* added experimental support for reserving / holding back FBA stock (MCF) - if order status is on-hold set FulfillmentAction to 'Hold' 
* added wpla_run_scheduled_tasks ajax action hook to trigger only the Amazon cron job (equal to wplister_run_scheduled_tasks) 

= 0.9.6.14 =
* fixed gift wrap row being added to WooCommerce orders even if gift wrap option was not selected 
* fixed issue where messages on the settings page would be invisible with certain 3rd party plugins installed 

= 0.9.6.13 =
* added option to leave email address empty when creating orders in WooCommerce 
* added FBA only mode settings option - to force FBA stock levels to be synced to WooCommerce 
* added filter hook wpla_mcf_enabled_order_statuses - allow to control which order statuses are allowed for FBA/MCF 
* added advanced options to make account settings, category settings and repricing tool available in main menu 
* store SKU as order line item meta when creating orders in WooCommerce 
* show warning if OpenSSL is older than 0.9.8o 
* show gift wrap option on orders page and details view 
* fixed creating orders with gift wrap option enabled - add gift wrap as separate shipping row 
* fixed tracking details not being sent to Amazon if orders are completed via ShipStation plugin 
* fixed fetching full listing description from amazon.es 
* fixed imported products not showing on frontend if sort by popularity is enabled (set total_sales meta field) 
* fixed stock status for imported variations 
* fixed issue where tracking details were not sent to eBay if autocomplete sales option was enabled 
* fixed issue where FBA items would not be updated from report if their SKU contains a "&" character 
* improved performance when updating products via CSV import (and show more debug data in log file) 
* improved error handling on storing report content in db (error: Got a packet bigger than 'max_allowed_packet' bytes) 
* improved error handling and logging if set_include_path() is disabled 
* escape HTML special chars in invalid SKU warning (force invisible HTML like soft hyphen / shy to become visible) 
* relabeled "Inventory Sync" option to "Synchronize sales" 

= 0.9.6.12 =
* improved storing taxes in created WooCommerce orders 
* fixed order line item product_id and variation_id for created WooCommerce orders containing variations 
* skip FBA orders from being auto completed on Amazon / Order Fulfillment Feed 
* fixed "The order id ... was not associated with your merchant" error for FBA orders
* repricing tool: restore listing view filters after using min/max price wizard 
* added option to filter order to import by marketplace - prevent duplicate orders if the same account is connected to multiple marketplaces 
* added feed template / category "Sport & Freizeit" on amazon.de 
* added Deutsche Post shipping provider 

= 0.9.6.11 =
* fixed missing variation_theme values for some templates like Jewellery and Clothing 
* fixed Error 99001: A value is required for the "feed_product_type" field (for parent variations) 
* fixed missing bullet points for (parent) variations 
* fixed FBA cron job not running more often than 24 hours 
* fixed duplicate description on imported products - leave product short description empty 
* fixed possible layout issue caused by 3rd party CSS 
* fixed empty external_product_id_type column for amazon.in 
* send SellerId instead of Merchant in SubmitFeed request header (SDK bug) 
* added DPD shipping service on edit order page 

= 0.9.6.10 =
* added experimental support for amazon.in 
* improved order processing for large numbers of orders 
* fixed error: A value is required for the brand_name / item_name field 

= 0.9.6.9 =
* added filter option to show listings with no profile assigned 
* fixed issue where orders were not imported / synced correctly if ListOrderItems requests are throttled 
* fixed issue where some orders were not imported if multiple accounts are used 
* fixed possible issue where lowest prices would not be updated from Amazon 

= 0.9.6.8 =
* fixed issue where variable items would be imported as simple products 
* fixed issue where parent attributes (e.g. brand) were missing for child variations (attribute_brand shortcode) 
* parent variations should only have three columns set: item_sku, parent_child, variation_theme 
* fixed possible php notice on inventory check (tools page) 
* added filter hook wpla_reason_for_not_creating_wc_order - allow other plugins to decide whether an order is created 

= 0.9.6.7 =
* fixed issue where activity indicator could show reports in progress when all reports were already processed 
* improved multiple offers indicator on repricing page - explain possible up-pricing issues in tooltip 
* feed generation: leave external_product_id_type empty if there is no external_product_id (parent variations) 
* skip invalid rows when processing inventory report - prevent inserting empty rows in amazon_listings 
* don't allow processing an inventory report that has localized column headers 
* added filter hook wpla_filter_imported_product_data and wpla_filter_imported_condition_html 

= 0.9.6.6.8 =
* fixed issue where sale dates were sent if sale price was intentionally left blank in listing profile 
* fixed inline price editor for large amounts - remove thousands separator from edit price field 
* fixed no change option in min/max price wizard  

= 0.9.6.6.7 =
* fixed sale start and end date not being set automatically 
* fixed repricing changelog showing integer prices when force decimal comma option was enabled  
* feed generation: leave external_product_id_type empty if there is no external_product_id (parent variations) 

= 0.9.6.6.6 =
* added warning note on import page about sale prices not being imported, but being removed when an imported product is updated 
* fixed issue where sale start and end date would be set for rows without a price (like parent variations in a listing data feed) 

= 0.9.6.6.5 =
* added warning on listing page if listings linked to missing products are found 
* added support for tracking details set by Shipment Tracking and Shipstation plugins (use their tracking number and provider in Order Fulfillment feed) 
* if no sale price is set send regular price with sale end date in the past (the only way to remove previously sent sale prices) 
* fixed stored number of pending feeds when multiple accounts are checked 

= 0.9.6.6.4 =
* include item condition note in imported product description 
* automatically create matched listing for simple products when ASIN is entered manually 
* trigger new Price&Quantity feed when updating min/max prices from WooCommerce (tools page) 
* updating reports checks pending ReportRequestIds only (make sure that each report is processed using the account it was requested by) 
* fixed issue where reports for different marketplaces would return the same results 
* fixed shipping date not being sent as UTC when order is manually marked as shipped 
* fixed importing books with multiple authors 
* added more feed templates for amazon.ca 

= 0.9.6.6.3 =
* added option to filter orders by Amazon account on WooCommerce Orders page 
* added prev/next buttons to import preview and fixed select all checkbox on search results 
* import book specific attributes - like author, publisher, binding and date published 
* extended option to set how often to request FBA Shipment reports to apply to FBA Inventory report as well 
* fixed importing item condition and condition note when report contains special characters 
* fixed possible error updating min/max prices 

= 0.9.6.6.2 =
* profile editor: do not require external_product_id if assigned account has the brand registry option enabled 
* update wp_amazon_listings.account_id when updating / applying listing profile 
* fixed issue where FBA enabled products would be marked as out of stock in WooCommerce if FBA stock is zero but still stock left in WC 
* fixed rare issue saving report processing options on import page 

= 0.9.6.6.1 =
* added option to import variations as simple products 
* fall back to import as simple product if there are no variation attributes on the parent listing (fix importing "variations without attributes") 
* fixed issue importing images for very long listing titles 
* improved error handling during importing process 

= 0.9.6.6 =
* added filter option to hide empty fields in profile editor
* added Industrial & Scientific feed templates for amazon.com
* added support for WooCommerce CSV importer 3.x

= 0.9.6.5.4 =
* added optional field for item condition and condition note on variation level
* added options to specify how long feeds, reports and order data should be kept in the database
* order details page: enter shipping time as local time instead of UTC
* view report: added search box to filter results / limit view to 1000 rows by default
* regard shipping discount when creating orders in WooCommerce (fix shipping total)
* fixed search box on import preview page - returned no results when searching for exact match ASIN or SKU

= 0.9.6.5.3 =
* fixed saving variations via AJAX on WooCommerce 2.4 beta
* show warning on edit product page if variations have no SKU set
* improved SKU mismatch warning on listings page in case the WooCommerce SKU is empty
* edit product: trim spaces from ASINs and UPCs automatically
* when duplicating a profile, jump to edit profile page

= 0.9.6.5.2 =
* shipping feed: make sure carrier-name is not empty if carrier-code is 'Other' (prevent Error 99021)
* edit order page: fixed field for custom service provider name not showing when tracking provider is set to "Other"
* fixed setup warnings not being shown (like missing cURL warning message)

= 0.9.6.5.1 =
* improved performance of generating import preview page
* fixed possible error code 200 when processing import queue

= 0.9.6.5 =
* added support for custom order statuses on settings page
* added gallery fallback option to use attached images if there is no WooCommerce Product Gallery (fixed issue with WooCommerce Dynamic Gallery plugin)
* added loading indicator on edit profile page
* added missing SDK file MarketplaceWebServiceProducts/Model/ErrorResponse.php
* added button to manually convert custom tables to utf8mb4 on WordPress 4.2+ (fix "Illegal mix of collations" sql error)
* improved Amazon column on Products page - show all listings for each product (but group variation listings)
* make sure the latest changes are submitted - even if a feed is "stuck" as submitted
* optimized memory footprint when processing import queue (fixed loading task list for 20k+ items on 192M RAM)
* improved processing of browse tree guide files - link db records to tpl_id to be able to clean incorrectly imported data automatically
* fixed php warning in ajax request when enabling all images on edit product page
* fixed issue with SWVG and Sports feed templates ok Amazon UK

= 0.9.6.4.2 =
* added option to request FBA shipment report every 3 hours
* added Clothing feed template for amazon.ca

= 0.9.6.4.1 =
* fixed possible php error during import 

= 0.9.6.4 =
* added option to set a default product category for products imported from Amazon (advanced settings page) 
* added option to automatically create matched listings for all products with ASINs (developer tools page) 
* improved profile editor for spanish feed templates 
* fixed some CE feed templates not being imported properly (amazon.es) 
* fixed possible fatal error during import 

= 0.9.6.3 =
* added option to process only selected rows when importing / updating products from merchant report 
* added option to enable Brand Registry / UPC exemption for account 
* brand registry: create listings for newly added child variations automatically, even if no UPC or ASIN is provided 
* fixed issue where items listed on multiple marketplaces using the same account would stay "submitted" 
* fixed matching product from edit product page - selected ASIN was removed if products was updated right after matching 
* fixed "View in WP-Lister" toolbar link on frontend 
* addedtooltips for report processing options on import page 
* import process: fixed creating additional (new / missing) variations for existing variable products in WooCommerce 
* regard "fall back to seller fulfilled" option when processing FBA inventory reports - skip zero qty rows entirely if fall back is enabled 

= 0.9.6.2 =
* added option to search / filter report rows in import preview 
* automatically fill in variation attribute columns like size_name and color_name  
* show number of offers considered next to lowest offer price in listings table 
* changed labeling from "imported" to "queued" - and updated text on import and settings pages 
* added developer tool buttons to clean the database - remove orphaned child variations and remove listings where the WooCommerce product has been deleted 
* fixed issue where selecting a category for Item Type Keyword column would insert browse node id instead of keyword (profile editor and edit product page) 
* make sure the customer state (address) is stored as two letter code in created WooCommerce orders (Amazon apparently returns either one or the other) 
* fixed search box on SKU generator page not showing products without listings 
* fixed formatting on ListMarketplaceParticipations response (log details) 
* fixes issue with attribute_ shortcodes on child variations inserting the same size/color value for all variations 

= 0.9.6.1 =
* added option to hide (exclude) specific variations from being listed on Amazon 
* added option to set WooCommerce order status for orders marked as shipped on Amazon 
* added "Sports & Outdoors" category for Amazon CA 
* regard WordPress timezone setting when creating orders 
* automatically update variation_theme for affected items when updating a listing profile 
* make sure sale_price is not higher than standard_price / price - Amazon might silently ignore price updates otherwise 
* fixed issue preparing listings when listing title is longer than 255 characters 
* fixed duplicate ASINs being skipped when importing products from merchant report 
* don't warn about duplicate ASINs if the SKU is unique 
* added action hook wpla_prepare_listing to create new listings from products 

= 0.9.6 =
* Initial release on wordpress.org

