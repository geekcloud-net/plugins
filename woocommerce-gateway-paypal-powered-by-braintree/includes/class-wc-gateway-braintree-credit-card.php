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
 * @package   WC-Braintree/Gateway/Credit-Card
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2018, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

use \SkyVerge\Plugin_Framework as WC_Braintree_Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Braintree Credit Card Gateway Class
 *
 * @since 3.0.0
 */
class WC_Gateway_Braintree_Credit_Card extends WC_Gateway_Braintree {


	/** @var string require CSC field */
	protected $require_csc;

	/** @var string fraud tool to use */
	protected $fraud_tool;

	/** @var string kount merchant ID */
	protected $kount_merchant_id;

	/** @var string 3D Secure enabled */
	protected $threed_secure_enabled;


	/**
	 * Initialize the gateway
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		parent::__construct(
			WC_Braintree::CREDIT_CARD_GATEWAY_ID,
			wc_braintree(),
			array(
				'method_title'       => __( 'Braintree (Credit Card)', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'method_description' => __( 'Allow customers to securely pay using their credit card via Braintree.', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'supports'           => array(
					self::FEATURE_PRODUCTS,
					self::FEATURE_CARD_TYPES,
					self::FEATURE_PAYMENT_FORM,
					self::FEATURE_TOKENIZATION,
					self::FEATURE_CREDIT_CARD_CHARGE,
					self::FEATURE_CREDIT_CARD_CHARGE_VIRTUAL,
					self::FEATURE_CREDIT_CARD_AUTHORIZATION,
					self::FEATURE_CREDIT_CARD_CAPTURE,
					self::FEATURE_DETAILED_CUSTOMER_DECLINE_MESSAGES,
					self::FEATURE_REFUNDS,
					self::FEATURE_VOIDS,
					self::FEATURE_CUSTOMER_ID,
					self::FEATURE_ADD_PAYMENT_METHOD,
					self::FEATURE_TOKEN_EDITOR,
				),
				'payment_type'       => self::PAYMENT_TYPE_CREDIT_CARD,
				'environments'       => $this->get_braintree_environments(),
				'shared_settings'    => $this->shared_settings_names,
				'card_types' => array(
					'VISA'    => 'Visa',
					'MC'      => 'MasterCard',
					'AMEX'    => 'American Express',
					'DISC'    => 'Discover',
					'DINERS'  => 'Diners',
					'MAESTRO' => 'Maestro',
					'JCB'     => 'JCB',
				),
			)
		);

		// sanitize admin options before saving
		add_filter( 'woocommerce_settings_api_sanitized_fields_braintree_credit_card', array( $this, 'filter_admin_options' ) );

		// get the client token via AJAX
		add_filter( 'wp_ajax_wc_' . $this->get_id() . '_get_client_token',        array( $this, 'ajax_get_client_token' ) );
		add_filter( 'wp_ajax_nopriv_wc_' . $this->get_id() . '_get_client_token', array( $this, 'ajax_get_client_token' ) );
	}


	/**
	 * Enqueue credit card method specific scripts, currently:
	 *
	 * + Fraud tool library
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway::enqueue_gateway_assets()
	 * @return bool
	 */
	public function enqueue_gateway_assets() {

		// advanced/kount fraud tool
		if ( $this->is_advanced_fraud_tool_enabled() ) {

			// enqueue braintree-data.js library
			wp_enqueue_script( 'braintree-data', 'https://js.braintreegateway.com/v1/braintree-data.js', array( 'braintree-js-client' ), WC_Braintree::VERSION, true );

			// adjust the script tag to add async attribute
			add_filter( 'clean_url', array( $this, 'adjust_fraud_script_tag' ) );

			// this script must be rendered to the page before the braintree-data.js library, hence priority 1
			add_action( 'wp_print_footer_scripts', array( $this, 'render_fraud_js' ), 1 );
		}

		if ( $this->is_available() && $this->is_payment_form_page() ) {

			parent::enqueue_gateway_assets();

			wp_enqueue_script( 'braintree-js-hosted-fields', 'https://js.braintreegateway.com/web/3.26.0/js/hosted-fields.min.js', array(), WC_Braintree::VERSION, true );

			if ( $this->is_3d_secure_enabled() ) {
				wp_enqueue_script( 'braintree-js-3d-secure', 'https://js.braintreegateway.com/web/3.26.0/js/three-d-secure.min.js', array(), WC_Braintree::VERSION, true );
			}
		}
	}


	/**
	 * Return the credit card payment form instance
	 *
	 * @since 3.0.0
	 * @return \WC_Braintree_Hosted_Fields_Payment_Form
	 */
	public function get_payment_form_instance() {

		return new WC_Braintree_Hosted_Fields_Payment_Form( $this );
	}


	/**
	 * Add credit card method specific form fields, currently:
	 *
	 * + Fraud tool settings
	 *
	 * @since 3.0.0
	 * @see WC_Gateway_Braintree::get_method_form_fields()
	 * @return array
	 */
	protected function get_method_form_fields() {

		$fraud_tool_options = array(
			'basic'    => __( 'Basic', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			'advanced' => __( 'Advanced', 'woocommerce-gateway-paypal-powered-by-braintree' ),
		);

		// Kount is only available for manual API connections
		if ( $this->is_kount_supported() ) {
			$fraud_tool_options['kount_direct'] = __( 'Kount Direct', 'woocommerce-gateway-paypal-powered-by-braintree' );
		}

		$fields = array(

			// fraud tools
			'fraud_settings_title' => array(
				'title' => __( 'Fraud Settings', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'type'  => 'title',
			),
			'fraud_tool'           => array(
				'title'    => __( 'Fraud Tool', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'type'     => 'select',
				'class'    => 'js-fraud-tool',
				'desc_tip' => __( 'Select the fraud tool you want to use. Basic is enabled by default and requires no additional configuration. Advanced requires you to enable advanced fraud tools in your Braintree control panel. To use Kount Direct you must contact Braintree support.', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'options'  => $fraud_tool_options,
			),
			'kount_merchant_id'    => array(
				'title'    => __( 'Kount merchant ID', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'type'     => 'text',
				'class'    => 'js-kount-merchant-id',
				'desc_tip' => __( 'Speak with your account management team at Braintree to get this.', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			),

			// 3D Secure
			'threed_secure_title' => array(
				'title'       => __( '3D Secure (Verified by Visa, MasterCard SecureCode)', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'type'        => 'title',
				'description' => sprintf( __( '3D Secure benefits cardholders and merchants by providing an additional layer of verification. %1$sLearn more about 3D Secure%2$s.', 'woocommerce-gateway-paypal-powered-by-braintree' ), '<a href="' . esc_url( $this->get_plugin()->get_documentation_url() ) . '#3d-secure' . '">', '</a>' ),
			),
			'threed_secure_enabled' => array(
				'title'       => __( '3D Secure', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable 3D Secure (Visa and MasterCard cards only)', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'description' => __( 'You must contact Braintree support to add this feature to your Braintree account before enabling this option.', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'default'     => 'no',
			),
		);

		return array_merge( parent::get_method_form_fields(), $fields );
	}


	/**
	 * Override the standard CSC setting to instead indicate that it's a combined
	 * Display & Require CSC setting. Braintree doesn't allow the CSC field to be
	 * present without also requiring it to be populated.
	 *
	 * @since 3.0.0
	 * @param array $form_fields gateway form fields
	 * @return array $form_fields gateway form fields
	 */
	protected function add_csc_form_fields( $form_fields ) {

		$form_fields['require_csc'] = array(
			'title'   => __( 'Card Verification (CSC)', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			'label'   => __( 'Display and Require the Card Security Code (CVV/CID) field on checkout', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			'type'    => 'checkbox',
			'default' => 'yes',
		);

		return $form_fields;
	}


	/**
	 * Returns true if the CSC field should be displayed and required at checkout
	 *
	 * @since 3.0.0
	 */
	public function is_csc_required() {

		return 'yes' === $this->require_csc;
	}


	/**
	 * Override the standard CSC enabled method to return the value of the csc_required()
	 * check since enabled/required is the same for Braintree
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function csc_enabled() {

		return $this->is_csc_required();
	}


	/**
	 * Render credit card method specific JS to the settings page, currently:
	 *
	 * + Hide/show Fraud tool kount merchant ID setting
	 *
	 * @since 3.0.0
	 * @see WC_Gateway_Braintree::admin_options()
	 */
	public function admin_options() {

		parent::admin_options();

		ob_start();
		?>
		// show/hide the kount merchant ID field based on the fraud tools selection
		$( 'select.js-fraud-tool' ).change( function() {

			var $kount_id_row = $( '.js-kount-merchant-id' ).closest( 'tr' );

			if ( 'kount_direct' === $( this ).val() ) {
				$kount_id_row.show();
			} else {
				$kount_id_row.hide();
			}
		} ).change();
		<?php

		wc_enqueue_js( ob_get_clean() );
	}


	/**
	 * Returns true if the payment nonce is provided when not using a saved
	 * payment token. Note this can't be moved to the parent class because
	 * validation is payment-type specific.
	 *
	 * @since 3.0.0
	 * @param boolean $is_valid true if the fields are valid, false otherwise
	 * @return boolean true if the fields are valid, false otherwise
	 */
	protected function validate_credit_card_fields( $is_valid ) {

		return $this->validate_payment_nonce( $is_valid );
	}


	/**
	 * Returns true if the payment nonce is provided when using a saved payment method
	 * and CSC is required.
	 *
	 * @since 3.2.0
	 * @param string $csc
	 * @return bool
	 */
	protected function validate_csc( $csc ) {

		return $this->validate_payment_nonce( true );
	}


	/**
	 * Add credit card specific data to the order, primarily for 3DS support
	 *
	 * 1) $order->payment->is_3ds_required - require 3DS for every transaction
	 * 2) $order->payment->use_3ds_nonce - use nonce instead of token for transaction
	 *
	 * @since 3.0.0
	 * @param \WC_Order|int $order order
	 * @return \WC_Order
	 */
	public function get_order( $order ) {

		$order = parent::get_order( $order );

		// add information for 3DS transactions, note that server-side verification
		// has already been checked in validate_fields() and passed
		if ( $this->is_3d_secure_enabled() ) {

			// indicate if 3DS should be required for every transaction -- note
			// this will result in a gateway rejection for *every* transaction
			// that doesn't have a liability shift
			$order->payment->is_3ds_required = $this->is_3d_secure_liability_shift_always_required();

			// when using a saved payment method for a transaction that has been
			// 3DS verified, indicate the nonce should be used instead, which
			// passes the 3DS verification details to Braintree
			if ( WC_Braintree_Framework\SV_WC_Helper::get_post( 'wc_braintree_credit_card_3d_secure_verified' ) && ! empty( $order->payment->token ) && ! empty( $order->payment->nonce ) ) {
				$order->payment->use_3ds_nonce = true;
			}
		}

		return $order;
	}


	/**
	 * Overrides the parent method to set the $order->payment members that are
	 * usually set prior to payment with a direct gateway. Because Braintree uses
	 * a nonce, we don't have access to the card info (last four, expiry date, etc)
	 * until after the transaction is processed.
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_Direct::do_credit_card_transaction()
	 * @param WC_Order $order the order object
	 * @param \WC_Braintree_API_Credit_Card_Transaction_Response $response optional credit card transaction response
	 * @return \WC_Braintree_API_Credit_Card_Transaction_Response
	 */
	protected function do_credit_card_transaction( $order, $response = null ) {

		if ( is_null( $response ) ) {

			$response = $this->perform_credit_card_charge( $order ) ? $this->get_api()->credit_card_charge( $order ) : $this->get_api()->credit_card_authorization( $order );

			if ( $response->transaction_approved() ) {
				$order->payment->account_number = $response->get_masked_number();
				$order->payment->last_four      = $response->get_last_four();
				$order->payment->card_type      = WC_Braintree_Framework\SV_WC_Payment_Gateway_Helper::card_type_from_account_number( $response->get_masked_number() );
				$order->payment->exp_month      = $response->get_exp_month();
				$order->payment->exp_year       = $response->get_exp_year();
			}
		}

		return parent::do_credit_card_transaction( $order, $response );
	}


	/**
	 * Adds any gateway-specific transaction data to the order, for credit cards
	 * this is:
	 *
	 * + risk data (if available)
	 * + 3D Secure data (if available)
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_Direct::add_transaction_data()
	 * @param \WC_Order $order the order object
	 * @param \WC_Braintree_API_Credit_Card_Transaction_Response $response transaction response
	 */
	public function add_payment_gateway_transaction_data( $order, $response ) {

		// add risk data
		if ( $this->is_advanced_fraud_tool_enabled() && $response->has_risk_data() ) {
			$this->update_order_meta( $order, 'risk_id', $response->get_risk_id() );
			$this->update_order_meta( $order, 'risk_decision', $response->get_risk_decision() );
		}

		// add 3D secure data
		if ( $this->is_3d_secure_enabled() && $response->has_3d_secure_info() ) {
			$this->update_order_meta( $order, 'threeds_status', $response->get_3d_secure_status() );
		}
	}


	/** Refund/Void feature ***************************************************/


	/**
	 * Void a transaction instead of refunding when it has a submitted for settlement
	 * status. Note that only credit card transactions are eligible for this, as
	 * PayPal transactions are settled immediately
	 *
	 * @since 3.0.0
	 * @param \WC_Order $order order
	 * @param \WC_Braintree_API_Response $response refund response
	 * @return bool true if the transaction should be transaction
	 */
	protected function maybe_void_instead_of_refund( $order, $response ) {

		// Braintree conveniently returns a validation error code that indicates a void can be performed instead of refund
		return $response->has_validation_errors() && in_array( Braintree_Error_Codes::TRANSACTION_CANNOT_REFUND_UNLESS_SETTLED, array_keys( $response->get_validation_errors() ) );
	}


	/** Add Payment Method feature ********************************************/


	/**
	 * Save verification transactional data when a customer
	 * adds a new credit via the add payment method flow
	 *
	 * @since 3.0.0
	 * @param \WC_Braintree_API_Customer_Response|\WC_Braintree_API_Payment_Method_Response $response
	 * @return array
	 */
	protected function get_add_payment_method_payment_gateway_transaction_data( $response ) {

		$data = array();

		// transaction ID
		if ( $response->get_transaction_id() ) {
			$data['trans_id'] = $response->get_transaction_id();
		}

		if ( $this->is_advanced_fraud_tool_enabled() && $response->has_risk_data() ) {
			$data['risk_id'] = $response->get_risk_id();
			$data['risk_decision'] = $response->get_risk_decision();
		}

		return $data;
	}


	/** Fraud Tool feature ****************************************************/


	/**
	 * Render the fraud tool JS, note this is hooked into wp_print_footer_scripts
	 * at priority 1 so that it's rendered prior to the braintree.js/braintree-data.js
	 * scripts being loaded
	 *
	 * @link https://developers.braintreepayments.com/guides/advanced-fraud-tools/overview
	 * @since 3.0.0
	 */
	public function render_fraud_js() {

		$environment = 'BraintreeData.environments.' . ( $this->is_test_environment() ? 'sandbox' : 'production' );

		if ( $this->is_kount_direct_enabled() ) {
			$environment .= '.withId' . $this->get_kount_merchant_id();
		}

		// TODO: consider moving this to it's own file

		?>
		<script>
			jQuery( function ( $ ) {

				var form_id;

				if ( $( 'form.checkout' ).length ) {

					// checkout page
					// WC does not set a form ID, use an existing one if available
					form_id = $( 'form.checkout' ).attr( 'id' ) || 'checkout';

					// otherwise set it ourselves
					if ( 'checkout' === form_id ) {
						$( 'form.checkout' ).attr( 'id', form_id );
					}

				} else if ( $( 'form#order_review' ).length ) {

					// checkout > pay page
					form_id = 'order_review'

				} else if ( $( 'form#add_payment_method' ).length ) {

					// add payment method page
					form_id = 'add_payment_method'
				}

				if ( !form_id ) {
					return;
				}

				window.onBraintreeDataLoad = function () {
					BraintreeData.setup( '<?php echo esc_js( $this->get_merchant_id() ); ?>', form_id, <?php echo esc_js( $environment ); ?> );
				}
			} );
		</script>
		<?php
	}


	/**
	 * Add an async attribute to the braintree-data.js script tag, there's no
	 * way to do this when enqueing so it must be done manually here
	 *
	 * @since 3.0.0
	 * @param string $url cleaned URL from esc_url()
	 * @return string
	 */
	public function adjust_fraud_script_tag( $url ) {

		if ( WC_Braintree_Framework\SV_WC_Helper::str_exists( $url, 'braintree-data.js' ) ) {

			$url = "{$url}' async='true";
		}

		return $url;
	}


	/**
	 * Return the enabled fraud tool setting, either 'basic', 'advanced', or
	 * 'kount_direct'
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_fraud_tool() {

		return $this->fraud_tool;
	}


	/**
	 * Return true if advanced fraud tools are enabled (either advanced or
	 * kount direct)
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function is_advanced_fraud_tool_enabled() {

		return 'advanced' === $this->get_fraud_tool() || 'kount_direct' === $this->get_fraud_tool();
	}


	/**
	 * Return true if the Kount Direct fraud tool is enabled
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function is_kount_direct_enabled() {

		return $this->is_kount_supported() && 'kount_direct' === $this->get_fraud_tool();
	}


	/**
	 * Get the Kount merchant ID, only used when the Kount Direct fraud tool
	 * is enabled
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_kount_merchant_id() {

		return $this->kount_merchant_id;
	}


	/**
	 * Determines if Kount is supported.
	 *
	 * Currently limited to non-US shops who are not using Braintree Auth.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	public function is_kount_supported() {

		return $this->is_connected_manually() && 'US' !== WC()->countries->get_base_country();
	}


	/** 3D Secure feature *****************************************************/


	/**
	 * Return true if 3D secure is enabled. Note this assumes the merchant has
	 * already enabled it for their Braintree account.
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function is_3d_secure_enabled() {

		return 'yes' === $this->threed_secure_enabled;
	}


	/**
	 * Return true if a liability shift is required for *every* 3DS-eligible
	 * transaction (even for those where liability shift wasn't possible, e.g.
	 * the cardholder was not enrolled)
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function is_3d_secure_liability_shift_always_required() {

		/**
		 * Braintree Credit Card Always Require 3D Secure Liability Shift Filter.
		 *
		 * Allow actors to require a liability shift for every 3DS-eligible
		 * transaction, regardless of whether it was possible or not.
		 *
		 * @since 3.0.0
		 * @param bool $require
		 * @param \WC_Gateway_Braintree_Credit_Card $this instance
		 * @return bool true to require the liability shift
		 */
		return apply_filters( 'wc_' . $this->get_id() . '_always_require_3ds_liability_shift', false, $this );
	}


	/**
	 * Get a payment nonce for an existing payment token so that 3D Secure verification
	 * can be performed on a saved payment method
	 *
	 * @link https://developers.braintreepayments.com/guides/3d-secure/server-side/php#vaulted-credit-card-nonces
	 *
	 * @since 3.0.0
	 * @param \WC_Braintree_Payment_Method $token payment method
	 * @return string nonce
	 */
	public function get_3d_secure_nonce_for_token( $token ) {

		$nonce = null;

		try {

			$result = $this->get_api()->get_nonce_from_payment_token( $token->get_id() );

			$nonce = $result->get_nonce();

		} catch ( WC_Braintree_Framework\SV_WC_Plugin_Exception $e ) {

			$this->add_debug_message( $e->getMessage(), 'error' );
		}

		return $nonce;
	}


	/**
	 * If 3D Secure is enabled, perform validation of the provided nonce. This
	 * complements the client-side check and must be performed server-side. Note
	 * that this is done in validate_fields() and not a later validation check
	 * as 3D Secure transactions also apply when using a saved payment token.
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_Direct::validate_fields()
	 * @return bool true if 3DS validations pass (or 3DS not enabled)
	 */
	public function validate_fields() {

		$is_valid = parent::validate_fields();

		// nonce must always be present for validation
		if ( $is_valid && $this->is_3d_secure_enabled() && WC_Braintree_Framework\SV_WC_Helper::get_post( 'wc_braintree_credit_card_payment_nonce' ) ) {

			$error = false;

			try {

				$payment_method = $this->get_api()->get_payment_method_from_nonce( WC_Braintree_Framework\SV_WC_Helper::get_post( 'wc_braintree_credit_card_payment_nonce' ) );

				// decline if liability was *not* shifted and either:
				// 1) liability shift is required for all eligible transactions
				// 2) liability shift was possible (but not shifted due to enrolled cardholder cancelling or failing authorization challenge)
				if ( $payment_method->has_3d_secure_info() && ! $payment_method->get_3d_secure_liability_shifted() && ( $payment_method->get_3d_secure_liability_shift_possible() || $this->is_3d_secure_liability_shift_always_required() ) ) {
					$error = __( 'We cannot process your order with the payment information that you provided. Please use an alternate payment method.', 'woocommerce-gateway-paypal-powered-by-braintree' );
				}

			} catch ( WC_Braintree_Framework\SV_WC_Plugin_Exception $e ) {

				$this->add_debug_message( $e->getMessage(), 'error' );

				$error = __( 'Oops, there was a temporary payment error. Please try another payment method or contact us to complete your transaction.', 'woocommerce-gateway-paypal-powered-by-braintree' );
			}

			if ( $error ) {
				wc_add_notice( $error, 'error' );
				$is_valid = false;
			}
		}

		return $is_valid;
	}


}
