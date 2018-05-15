<?php
/*
  Plugin Name: WooImporter eBay Variations
  Description: Add-on for WooImporter. WooImporter eBay Variations.
  Version: 1.0.3
  Author: Geometrix
  License: GPLv2+
  Author URI: http://gmetrixteam.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

include_once dirname(__FILE__) . '/include/WPEAE_EbayVariationImporter.php';

if (!class_exists('WooImporter_EbayVariation')) {

    class WooImporter_EbayVariation {

        function __construct() {
            register_activation_hook(__FILE__, array($this, 'install'));
            register_deactivation_hook(__FILE__, array($this, 'uninstall'));

            new WPEAE_EbayVariationImporter();
        }

        function install() { }

        function uninstall() { }
    }

}

new WooImporter_EbayVariation();
