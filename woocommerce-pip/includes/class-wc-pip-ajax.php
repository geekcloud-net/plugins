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
 * @package   WC-Print-Invoices-Packing-Lists/AJAX
 * @author    SkyVerge
 * @copyright Copyright (c) 2011-2018, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * AJAX class
 *
 * Handles ajax callbacks in admin or front end
 *
 * @since 3.0.0
 */
class WC_PIP_Ajax {


	/**
	 * Add ajax actions
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		// send a PIP Invoice or Packing List by email
		add_action( 'wp_ajax_wc_pip_order_send_email', array( $this, 'order_send_email' ) );
	}


	/**
	 * Send email for order
	 *
	 * @since 3.0.0
	 */
	public function order_send_email() {

		check_ajax_referer( 'send-order-email', 'security' );

		$document_type = isset( $_POST['document'] ) ? str_replace( 'wc_pip_send_email_', '', $_POST['document'] ) : '';
		$order_id      = isset( $_POST['order_id'] ) ? (int) $_POST['order_id'] : 0;
		$order         = wc_get_order( $order_id );

		if ( $order && $document_type && $document = wc_pip()->get_document( $document_type, array( 'order' => $order ) ) ) {

			/* this action is documented in class-wc-pip-orders-admin.php */
			do_action( 'wc_pip_sending_manual_order_email', $document );

			$document->send_email();

			$pip_message = array(
				'wc_pip_document' => $document_type,
				'wc_pip_action'   => 'admin_message',
				'wc_pip_message'  => 'emails_sent',
				'emails_count'    => 1,
			);

			/* @see WC_PIP_Orders_Admin::render_email_sent_message() */
			wp_send_json_success( add_query_arg( $pip_message, admin_url( 'post.php?post=' . SV_WC_Order_Compatibility::get_prop( $order, 'id' ) . '&action=edit' ) ) );
		}

		die( __( 'Invalid order or document type.', 'woocommerce-pip' ) );
	}


}
