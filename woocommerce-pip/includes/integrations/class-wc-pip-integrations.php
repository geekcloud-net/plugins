<?php
/**
 * WooCommerce Print Invoices/Packing Lists
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Print
 * Invoices/Packing Lists to newer versions in the future. If you wish to
 * customize WooCommerce Print Invoices/Packing Lists for your needs please refer
 * to http://docs.woocommerce.com/document/woocommerce-print-invoice-packing-list/
 *
 * @package   WC-Print-Invoices-Packing-Lists/Integrations
 * @author    SkyVerge
 * @copyright Copyright (c) 2011-2018, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Integrations class
 * for third party extensions and plugins compatibility
 *
 * @since 3.0.0
 */
class WC_PIP_Integrations {


	/** @var WC_PIP_Integration_Subscriptions WooCommerce Subscriptions */
	public $subscriptions;

	/** @var WC_PIP_Integration_VAT_Number intsance */
	protected $vat_numbers;


	/**
	 * Load integrations
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		// Subscriptions
		if ( wc_pip()->is_plugin_active( 'woocommerce-subscriptions.php' ) ) {
			$this->subscriptions = wc_pip()->load_class( '/includes/integrations/woocommerce-subscriptions/class-wc-pip-integration-subscriptions.php', 'WC_PIP_Integration_Subscriptions' );
		}

		// VAT Number Plugins
		$this->vat_numbers = wc_pip()->load_class( '/includes/integrations/vat-number/class-wc-pip-integration-vat-number.php', 'WC_PIP_Integration_VAT_Number' );
	}


}
