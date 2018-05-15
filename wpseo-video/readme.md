Video SEO
=========
Requires at least: 4.8
Tested up to: 4.9.5
Stable tag: 7.4
Requires PHP: 5.2.4
Depends: Yoast SEO

Video SEO adds Video SEO capabilities to WordPress SEO.

Description
-----------

This plugin adds Video XML Sitemaps as well as the necessary OpenGraph markup, Schema.org videoObject markup and mediaRSS for your videos.

Installation
------------

1. Go to Plugins -> Add New.
2. Click "Upload" right underneath "Install Plugins".
3. Upload the zip file that this readme was contained in.
4. Activate the plugin.
5. Go to SEO -> Extensions and enter your license key.
6. Save settings, your license key will be validated. If all is well, you should now see the XML Video Sitemap settings.
7. Make sure to hit the "Re-index videos" button if you have videos in old posts.

Frequently Asked Questions
--------------------------

You can find the [Video SEO FAQ](https://kb.yoast.com/kb/category/video-seo/) in our knowledge base.

Changelog
=========
### 7.4: May 1st, 2018
* Compatibility with Yoast SEO 7.4

### 7.3: April 17th, 2018
* Compatibility with Yoast SEO 7.3

### 7.2: April 3rd, 2018
* Security hardening.

### 7.1: March 20th, 2018
* Adds messages to all soft-deprecated methods, actions, hooks or filters. Added deprecation messages to four functions that didn't have a message yet. 

### 7.0: March 6th, 2018

Copy:
* Changes activation warning to no longer suggest that activation failed, but rather that features won't be properly available as long as Yoast SEO is not active.

Other:
* Requires Yoast SEO 7.0 or higher to be installed.
* Removes support for the [Vzaar video platform](http://vzaar.com/).
* Removes support for videos added through the following plugins and themes which are no longer available, no longer (actively) maintained or have been deprecated by the plugin author:
    - [Advanced YouTube Embed Plugin by Embed Plus](https://wordpress.org/plugins/embedplus-for-wordpress/)
    - [IFrame Embed for YouTube](https://wordpress.org/plugins/iframe-embed-for-youtube/)
    - [Instabuilder](http://instabuilder.com/v2.0/launch/)
    - [KISS Youtube plugin](https://wordpress.org/plugins/kiss-youtube/)
    - PluginBuddy VidEmbed
    - [Simple Video Embedder](https://wordpress.org/plugins/simple-video-embedder/)
    - [Sublime Video](https://wordpress.org/plugins/sublimevideo-official/)
    - [Titan Lightbox](http://www.wplocker.com/plugins/codecanyon/656-codecanyon-titan-lightbox-for-wordpress.html)
    - [VideoJS - HTML5 Video Player for WordPress](https://wordpress.org/plugins/videojs-html5-video-player-for-wordpress/)
    - [VideoPress](https://wordpress.org/plugins/video/)
    - [Viper Video Quicktags](https://wordpress.org/plugins/vipers-video-quicktags/)
    - [Vippy](https://wordpress.org/plugins/vippy/)
    - [Vzaar Media Management](https://wordpress.org/plugins/vzaar-media-management/)
    - [Vzaar Official Media Manager](https://wordpress.org/plugins/vzaar-official-plugin/)
    - Weaver theme
    - [WordPress Video Plugin](https://wordpress.org/plugins/wordpress-video-plugin/)
    - WP OS FLV
    - [WP YouTube Player](https://wordpress.org/plugins/wp-youtube-player/)
    - [YouTube Insert Me](https://wordpress.org/plugins/youtube-insert-me/)
    - [YouTube Shortcode](https://wordpress.org/plugins/youtube-shortcode/)
    - [YouTube White Label Shortcode](https://wordpress.org/plugins/youtube-white-label-shortcode/)
    - [YouTube with Style](https://wordpress.org/plugins/youtube-with-style/)
    - [YouTuber](https://wordpress.org/plugins/youtuber/)
    - Premise
    - [WordPress Automatic Youtube Video Post](https://wordpress.org/plugins/automatic-youtube-video-posts/)

### 6.3 February 13th, 2018
* Load the XSL stylesheet from a static file when home and site URL are the same.
* Compatibility with Yoast SEO 6.3

### 6.2 January 23rd, 2018
* Compatibility with Yoast SEO 6.2

### 6.1: January 9th, 2018
* Compatibility with Yoast SEO 6.1

### 6.0: December 20th, 2017
* Compatibility with Yoast SEO 6.0

### 5.9: December 5th, 2017
Changes:
* Removes deactivation of this plugin when Yoast SEO Premium is inactive.
* Compatibility with Yoast SEO 5.9

### 5.8: November 15th, 2017
* Compatibility with Yoast SEO 5.8

### 5.7: October 24th, 2017
* Compatibility with Yoast SEO 5.7.

### 5.6: October 10th, 2017
Changes:
* Changes the capability on which the submenu is registered to `wpseo_manage_options`
* Changes the way the submenu is registered to use the `wpseo_submenu_pages` filter

Bugfixes:
* Fixes a bug where the license check endpoint was using an incorrect URL

### 5.5: September 26th, 2017
* Updated the internationalization module to version 3.0.

### 5.4: September 6th, 2017
* Compatibility with Yoast SEO 5.4.

### 5.3: August 22nd, 2017
* Fixes a call to a deprecated method when generating the video sitemap.
* Removed `wp_installing` polyfill.

### 5.2: August 8th, 2017
* Compatibility with Yoast SEO 5.2.

### 5.1: July 25th, 2017
* Fixes a bug where the `isFamilyFriendly` meta property is not set properly.

### 5.0: July 6th, 2017
* Compatibility with Yoast SEO 5.0.
