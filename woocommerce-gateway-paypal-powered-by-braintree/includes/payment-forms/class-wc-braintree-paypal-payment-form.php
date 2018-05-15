<?php
/**
 * WooCommerce Braintree Gateway
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@woocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Braintree Gateway to newer
 * versions in the future. If you wish to customize WooCommerce Braintree Gateway for your
 * needs please refer to http://docs.woocommerce.com/document/braintree/
 *
 * @package   WC-Braintree/Gateway/Payment-Form/PayPal
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2018, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

use \SkyVerge\Plugin_Framework as WC_Braintree_Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Braintree PayPal Payment Form
 *
 * @since 3.0.0
 */
class WC_Braintree_PayPal_Payment_Form extends WC_Braintree_Payment_Form {


	/**
	 * Return the JS params passed to the the payment form handler script
	 *
	 * @since 3.0.0
	 * @see WC_Braintree_Payment_Form::get_payment_form_handler_js_params()
	 * @return array
	 */
	protected function get_payment_form_handler_js_params() {

		$default_button_styles = array(
			'label' => 'pay',
			'size'  => 'responsive',
			'shape' => 'pill',
			'color' => 'gold',
		);

		// tweak the styles a bit for better display on the Add Payment Method page
		if ( is_add_payment_method_page() ) {
			$default_button_styles['label'] = 'paypal';
			$default_button_styles['size']  = 'medium';
		}

		/**
		 * Filters the PayPal button style parameters.
		 *
		 * See https://developer.paypal.com/docs/integration/direct/express-checkout/integration-jsv4/customize-button/
		 *
		 * @since 2.1.0
		 *
		 * @param array $styles style parameters
		 */
		$button_styles = apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_button_styles', $default_button_styles );

		return array(
			'is_test_environment'           => $this->get_gateway()->is_test_environment(),
			'must_login_message'            => __( 'Please click the blue "PayPal" button below to log into your PayPal account before placing your order.', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			'must_login_add_method_message' => __( 'Please click the blue "PayPal" button below to log into your PayPal account before adding your payment method.', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			'button_styles'                 => wp_parse_args( $button_styles, $default_button_styles ), // ensure all expected parameters are present after filtering to avoid JS errors
		);
	}


	/**
	 * Renders the payment form description.
	 *
	 * Overridden to bail if confirming a cart order.
	 *
	 * @since 2.0.0
	 */
	public function render_payment_form_description() {

		if ( $this->get_gateway()->get_plugin()->get_paypal_cart_instance()->is_checkout_confirmation() ) {
			return;
		}

		parent::render_payment_form_description();
	}


	/**
	 * Renders the saved payment methods.
	 *
	 * Overridden to bail if confirming a cart order.
	 *
	 * @since 2.0.0
	 */
	public function render_saved_payment_methods() {

		if ( $this->get_gateway()->get_plugin()->get_paypal_cart_instance()->is_checkout_confirmation() ) {
			return;
		}

		parent::render_saved_payment_methods();
	}


	/**
	 * Render the PayPal container div, which is replaced by the PayPal button
	 * when the frontend JS executes. This also renders 3 hidden inputs:
	 *
	 * 1) wc_braintree_paypal_amount - order total
	 * 2) wc_braintree_paypal_currency - active store currency
	 * 3) wc_braintree_paypal_locale - site locale
	 *
	 * Note these are rendered as hidden inputs and not passed to the script constructor
	 * because these will be refreshed and re-rendered when the checkout updates,
	 * which is important for the accuracy of things like the order total.
	 *
	 * Also note that the order total is used for rendering info inside the PayPal
	 * modal and _not_ for actual processing for the transaction, so there's no
	 * security concerns here.
	 *
	 * @since 3.0.0
	 */
	public function render_payment_fields() {

		parent::render_payment_fields();

		$order_total = $this->get_order_total();

		?>

		<div id="wc_braintree_paypal_container"></div>
		<input type="hidden" name="wc_braintree_paypal_amount" value="<?php echo esc_attr( WC_Braintree_Framework\SV_WC_Helper::number_format( $order_total, 2 ) ); ?>" />
		<input type="hidden" name="wc_braintree_paypal_currency" value="<?php echo esc_attr( get_woocommerce_currency() ); ?>" />
		<input type="hidden" name="wc_braintree_paypal_locale" value="<?php echo esc_attr( $this->get_gateway()->get_safe_locale() ); ?>" />

		<?php
	}


}
