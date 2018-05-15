== YITH Frontend Manager for WooCommerce Premium ===

Contributors: yithemes
Requires at least: 4.0
Tested up to: 4.9.5
Stable tag: 1.4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Multi vendor e-commerce is a new idea of e-commerce platform that is becoming more and more popular in the web.

== Changelog ==

= 1.4.3 - Released on 23rd April, 2018 =

* New: Italian language
* New: Dutch language
* Fix: Unable to change order status with vendor or shop manager user
* Fix: Unable to delete orders with vendor profile
* Fix: Unable to add attribute if shop manager  can't access in admin area
* Dev: yith_wcfm_logout_redirect_url hook to change the logout redirect url

= 1.4.2 - Released on 14th March, 2018 =

* New: Support for YITH WooCommerce Name Your Price Premium
* Fix: Unable to deactivate Vendor Profile Section

= 1.4.1 - Released on 21th February, 2018 =

* Tweak: 404 permalink issue after theme switching on WordPress 4.9 or greather
* Tweak: 404 permalink issue after YITH WooCommerce Multi Vendor plugin activation on WordPress 4.9 or greather
* Fix: Delete product don't set product to trash status
* Fix: 404 permalink issue after login or logout
* Fix: 404 permalink issue after new user created
* Fix: 404 permalink issue after new vendor created
* Fix: 404 permalink issue when the admin use User Switching plugin
* Dev: Add yith_wcfm_skin_1_header_blog_title hook to change the blog_name in skin-1 header
* Dev: yith_wcfm_force_delete_product hook

= 1.4.0 - Released on 14th February, 2018 =

* New: Add support for WooCommerce 3.3
* New: Support for YITH WooCommerce Tab Manager Premium
* New: Support for YITH WooCommerce Featured Radio and Video Content Premium
* New: Support for YITH Live Chat Premium
* Fix: Plugin create tab manager section without plugin enabled
* Fix: Vendor can create coupon for all products
* Fix: The endpoint is already registered by WooCommerce for vendor settings page in admin mode
* Fix: Unable to create coupon if user use a custom endpoint
* Fix: Vendors can't set product to DRAFT
* Fix: Stock report action icon style missing
* Fix: No message after add/edit product
* Fix: Vendors can't create coupon on frontend
* Fix: Wrong style in order refund table
* Fix: Conflict between Frontend Manager and Divi Builder color picker
* Fix: Unable to see purchased col on front
* Fix: Product Shipping table in edit product variation
* Fix: Wrong path in register style and scripts method
* Fix: Product Categories aren't show in hierarchical mode
* Fix: Unable to show featured products column for vendors
* Fix: Header and Footer sidebars doesn't works with Skin-1
* Fix: Edit order uri redirect to admin area in commission detail page
* Fix: Edit product uri redirect to in admin area in commission detail page

= 1.3.2 - Released on 22nd December, 2017 =

* Fix: Style issue with YITH theme with FW 2.0 or greather
* Fix: Tags and Categories list in add/edit product page
* Fix: Unable to set Enable Reviews in add/edit product page

= 1.3.1 - Released on 15th December, 2017 =

* New: Tested up WooCommerce 3.2.6
* Update: Plugin Framework 3.0.1

= 1.3.0 - Released on 13rd December, 2017 =

* New: Translate frontend manager menu with WPML
* New: Support for YITH WooCommerce Colors and Labels Variations Premium (min. version 1.5.1)
* Tweak: Remove dynamics.css file from YITH 2.0 theme options from Skins
* Tweak: Update the YITH Plugin Framework 3.0
* Update: Spanish language files
* Fix: Fatal error if try to change the status of one commission in bulk action
* Fix: Style issue with long Net sales this month value in dashboard
* Fix: Unable to remove all images from product gallery
* Fix: Lost is_active class in navigation menu if custom slug are set
* Fix: Unable to scroll the commissions table on flatsome theme
* Fix: Column post.ID doesn't exists in Dashboard if vendor haven't orders
* Fix: Responsive style on Reports and Product Edit page
* Fix: Vendor with pending account can access to frontend manager dashboard
* Fix: Style issue with Globe theme by YITH in Product Edit Page

= 1.2.1 =

* New: Support to YITH WooCommerce Featured Audio and Video Content Premium (vers. 1.1.16 or greather)
* Tweak: 2.0 accessibility. Text meant only for screen readers

= 1.2.0 =

* New: Support for WooCommerce TM Extra Product Options plugin
* New: Support fo Woocommerce 3.2.1
* Tweak: change the string "Net sales on this month" to "Net commissions on this month" in vendor's dashboard
* Tweak: Change the old wc-tooltip with jquery-tiptip
* Tweak: Add minify js file for coupons script
* Fix: Coupons script loaded two times if the current user is a valid vendor
* Fix: Unable to add variable products with WooCommerce 3.2
* Fix: Unable to add related products with WooCommerce 3.2
* Fix: Net sales commissions on dashboard showing only 0,00 $ with version 1.1.0
* Fix: Vendor can't add order notes
* Fix: WooCommerce admin style.css enqueued in all pages
* Fix: Unable to set percentage coupon for vendors

= 1.1.0 =

* Tweak. Add message if the user enable the plugin without WooCommerce
* Tweak: Prevent Fatal Error if the wc_create_page function doesn't exists
* Tweak: Skin system refactoring
* Fix: Unable to save product shipping class
* Fix: Style in skin-1 sidebar
* Fix: New vendor can see all orders
* Fix: Unable to save shipping for vendors with a long list of shipping classes
* Fix: Wrong net sales in dashboard for vendor users

= 1.0.15 =

* Tweak: Prevent "Nested level too deep" error
* Tweak: Prevent to have different store with the same name
* Fix: Wrong style with Firefox browser
* Fix: Vendor avatar doesn't show in skin-1
* Fix: Vendor name doesn't shown in edit product
* Fix: Wrong net sales value for vendors with no orders in dashboard section
* Fix: Style issue in product variation box
* Fix: Plugin loads admin scripts on frontend on all website pages

= 1.0.14 =

* Tweak: Improved style for shipping zones popup on YITH WooCommerce Multi Vendor panels inside Frontend Manager

= 1.0.13 =

* Fix: Removed notice on templates/skins/skin-1/header.php
* Fix: Datepicker on coupons

= 1.0.12 =

* Tweak: Change vendor admin link with frontend manager page
* Fix: Vendor restrict backend access option fails all admin ajax calls on frontend
* Fix: Administrator can't see WordPress admin bar
* Fix: Products section and Reports section ABSPATH
* Fix ABSPATH in dashboard sections

= 1.0.11 =

* Tweak: Flush permalinks after user login
* Fix: Empty billing and shipping address if admin/vendor click on pencil icon to edit it
* Fix: Vendor can't set shipping zone and shipping method on frontend

= 1.0.10 =

* Fix: Vendor can't see media library if user have no access to admin area
* Fix: Vendor can't upload image in media library if user have no access to admin area
* Fix: Shop Manager can't see media library if user have no access to admin area
* Fix: Shop Manager can't upload image in media library if user have no access to admin area

= 1.0.9 =

* Fix: Unable to save vendor settings if vendor can't access to backend
* Fix: Prevent wrong edit post type url if no default page is set
* Tweak: Enanched style support for Nielsen theme
* Tweak: Enanched style support for Twenty theme
* Tweak: Enanched style support for Flatsome theme
* Tweak: Enanched style support for Sydney theme
* Tweak: Enanched style support for Business Center theme
* Tweak: Enanched style support for R�my theme
* Tweak: Enanched style support for Mindig theme
* Tweak: Enanched style support for Desire Sexy Shop theme

= 1.0.8 =

* Fix: Unable to set price for simple products with Flatsome and WooCommerce 3.1
* Fix: Undefined index tab in panel page

= 1.0.7 =

* New: Flush permalinks option
* New Support for WooCommerce 3.1-RC2
* Fix: get_current_screen() not defined in frontend class
* Fix: Support for Flatsome theme
* Fix: Support for bb-theme
* Fix: 404 on each sections after plugin activation

= 1.0.6 =

* New: Support to YITH Nielsen theme
* Tweak: Version on skin style.css
* Fix: Some strings have a wrong text-domain
* Fix: Missing icon if change sections slug

= 1.0.5 =

* Fix: Some strings have a wrong text-domain
* Fix: missing CSS Rules on default skin and skin-1
* Fix: All sections return a 404 not found error after theme switching or multi vendor plugin activation
* Tweak: Hide live chat popup in frontend manager section

= 1.0.4 =

* Fix: Super admin can't access to backend in WordPress MultiSite if the vendor restrict admin area access are set to "YES"

= 1.0.3 =

* New: Add language catalog file
* Fix: Translation issue in endpoint sections panel

= 1.0.2 =

* Fix: Failed opening required with multi vendor free

= 1.0.1 =

* Fix: The function YITH_Vendor_Shipping doesn't exists

= 1.0.0 =

* Initial release
