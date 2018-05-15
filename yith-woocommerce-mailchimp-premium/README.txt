=== YITH WooCommerce Mailchimp ===

Contributors: yithemes
Tags: mailchimp, woocommerce, checkout, themes, yit, e-commerce, shop, newsletter, subscribe, subscription, marketing, signup, order, email, mailchimp for wordpress, mailchimp for wp, mailchimp signup, mailchimp subscribe, newsletter, newsletter subscribe, newsletter checkbox, double optin
Requires at least: 4.0
Tested up to: 4.9.2
Stable tag: 1.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Documentation: http://yithemes.com/docs-plugins/yith-woocommerce-mailchimp

== Changelog ==

= 1.1.2 - Released: Feb, 01 - 2018 =

* New: WooCommerce 3.3.0 support
* New: update internal plugin-fw
* New: added Dutch translation
* Tweak: improved performance of code that prints shortcode

= 1.1.1 - Released: Oct, 25 - 2017 =

* New: WooCommerce 3.2.1 support
* New: WordPress 4.8.2 support
* New: update internal plugin-fw
* Tweak: added check over wc_get_notices existence
* Tweak: avoided double form handler execution, adding return false at the end of handler
* Dev: created subscribe wrapper for subscription process and refactored code
* Dev: moved cachable requests init to init hook, to let third party code filter them

= 1.1.0 - Released: May, 05 - 2017 =

* Add: WooCommerce 3.0.x compatibility
* Add: WordPress 4.7.4 compatibility
* Tweak: hidden being emptied when form is clened
* Dev: added yith_wcmc_use_placeholders_instead_of_labels filter, to let use placeholders instead of labels for fields & groups in subscription form (where applicable)

= 1.0.10 - Released: Nov, 28 - 2016 =

* Add: empty all form fields on successful subscription
* Add: spanish translation
* Tweak: changed text domain to yith-woocommerce-mailchimp
* Tweak: updated plugin-fw version

= 1.0.9 - Released: Jun, 13 - 2016 =

* Added: WooCommerce 2.6-RC1 support
* Added: capability for the admin to export to MailChimp Waiting Lists (require YITH WooCommerce Waiting Lists Premium installed)
* Added: option to set MailChimp field where product waiting for slugs should be exported
* Added: capability for the admin to export Waiting Lists via CSV (require YITH WooCommerce Waiting Lists Premium installed)
* Added: trigger yith_wcmc_form_subscription_result after ajax call success
* Tweak: changed sanitize function to let users enter html code in success message
* Tweak: added check over group data, before calling wp_list_pluck

= 1.0.8 - Released: Apr, 26 - 2016 =

* Added: check to avoid warning when MailChimp returns status failure on group retrieving
* Fixed: Warning related to missing check over formatted data structure in plugin option

= 1.0.7 - Released: Apr, 12 - 2016 =

* Added: WooCommerce 2.5.5 compatibility
* Added: WordPress 4.5 compatibility
* Added: capability for admins to select groups to prompt on frontend for the the user to choose among
* Added: action yith_wcmc_after_subscription_form_title
* Added: yith_wcmc_after_subscription_form_notice action in mailchimp-subscription-form.php template
* Tweak: Updated internal plugin-fw
* Fixed: Changed lists/list request, to get all available lists, and not only first page
* Fixed: error with interests groups containing commas in their names
* Fixed: typo in batch-subscribe request (export to MailChimp)
* Fixed: checkout checkbox position option, causing unexpected results
* Fixed: custom css not working for widget
* Fixed: widget class

= 1.0.6 - Released: Dec, 14 - 2015 =

* Added: option to hide form after successful registration, in shortcodes and widgets
* Added: options to customize success message, in shortcodes and widgets
* Added: check over MailChimp class existence, to avoid Fatal Error with other plugins including that class
* Added: MailChimp error translation via .po archives
* Tweak: improved plugin import procedure
* Tweak: Updated internal plugin-fw

= 1.0.5 - Released: Oct, 23 - 2015 =

* Tweak: Performance improved with new plugin core 2.0
* Fixed: eCommerce 360 campaign data workflow

= 1.0.4 - Released: Sep, 10 - 2015 =

* Fixed: WC general notices print in MailChimp widget / shortcode
* Fixed: Missing file on activation

= 1.0.3 - Released: Aug, 12 - 2015 =

* Added: Compatibility with WC 2.4.2
* Tweak: Updated internal plugin-fw
* Tweak: Improved wpml compatibility
* Tweak: Removed class row from subscription form
* Tweak: Removed un-needed nl from types templates
* Fixed: Removed call to deprecated $this->WP_Widget method
* Fixed: added nopriv ajax handling for form subscription
* Removed: control on "show" flag for fields

= 1.0.2 - Released: May, 04 - 2015 =

* Added: WP 4.2.1 support
* Fixed: "Plugin Documentation" appearing on all plugins
* Fixed: various minor bug

= 1.0.1 =

* Initial release