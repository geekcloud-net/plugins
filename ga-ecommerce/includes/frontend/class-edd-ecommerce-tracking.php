<?php

/**
 * Class MonsterInsights_GA_EDD_eCommerce_Tracking
 *
 * Tracks Easy Digital Downloads transactions as soon as they're set to paid on the server.
 *
 * @since 6.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MonsterInsights_GA_EDD_eCommerce_Tracking extends MonsterInsights_eCommerce_Tracking_Abstract {

	/**
	 * When order is processed, there is a payment_id created. From that moment the user_id can be saved
	 *
	 * @var string
	 */
	protected $store_user_id_hook = 'edd_insert_payment';

	/**
	 * In edd the name of the order post type is 'edd_payment'
	 *
	 * @var string
	 */
	protected $order_post_type = 'edd_payment';	

	/**
	 * Hook the required functions
	 *
	 * @since 6.0.0
	 */
	public function load() {

		parent::load();

		// add filter
		add_filter( 'edd_paypal_redirect_args', array( $this, 'change_paypal_return_url' ) );
	}

	/**
	 * Add utm_nooverride to the PayPal return URL so the original source of the transaction won't be overridden.
	 *
	 * @since 6.0.0
	 *
	 * @param array $paypal_args
	 *
	 * @link  https://support.bigcommerce.com/questions/1693/How+to+properly+track+orders+in+Google+Analytics+when+you+accept+PayPal+as+a+method+of+payment.
	 *
	 * @return array
	 */
	public function change_paypal_return_url( $paypal_args ) {
		$paypal_args['return'] = add_query_arg( array( 'utm_nooverride' => '1' ), $paypal_args['return'] );

		return $paypal_args;
	}

	/**
	 * This method will return the value of $this->store_user_id_hook.
	 *
	 * This hook is used for saving the user id, after created a payment. So there will be a payment_id existing
	 *
	 * @since 6.0.0
	 *
	 * @return mixed|string
	 */
	protected function get_store_user_id_hook() {
		return $this->store_user_id_hook;
	}


	/**
	 * Get user ID of purchaser.
	 *
	 * @since 6.0.3
	 *
	 * @return void
	 */
	protected function get_user_id( $payment_id = 0 ) {
		return edd_get_payment_user_id( $payment_id );
	}

	/**
	 * This method will return the value of $this->order_post_type.
	 *
	 * This is used to ensure we're detecting the right kind of post.
	 *
	 * @since 6.0.0
	 *
	 * @return mixed|string
	 */
	protected function get_order_post_type() {
		return $this->order_post_type;
	}


	/**
	 * This method will add the actions to add/remove orders to GA on.
	 *
	 * This hook is used for changing the status of the payment.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	protected function get_order_actions() {
		// When to send to GA
		add_action( 'edd_complete_purchase', array( $this, 'maybe_do_transaction' ) );
		add_action( 'edd_update_payment_status',  array( $this, 'add_order' ), 10, 3 );

		// When to remove from GA
		add_action( 'edd_update_payment_status', array( $this, 'maybe_undo_transaction_status' ), 10, 3 );
		add_action( 'edd_payment_delete', array( $this, 'maybe_undo_transaction' ), 10 );
	}

	/**
	 * This method adds EDD subscription renewals to GA.
	 *
	 * @since 7.0.7
	 *
	 * @return void
	 */
	public function add_order( $payment_id, $new_status, $old_status ) {
		if ( 'edd_subscription' !== $new_status  ) {
			return;
		}
		$this->do_transaction( $payment_id );
	}

	/**
	 * This method will determine whether to undo the transaction or not.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function maybe_undo_transaction_status( $payment_id = 0, $new_status, $old_status ){
		if ( 'publish' != $old_status && 'revoked' != $old_status ) {
			return;
		}
		
		if ( 'refunded' != $new_status ) {
			return;
		}

		$this->maybe_undo_transaction( $payment_id );
	}

	/**
	 * This method will determine whether to do the transaction or not.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function maybe_do_transaction( $payment_id = 0 ) {
		$this->do_transaction( $payment_id );
	}

	/**
	 * This method will determine whether to undo the transaction or not.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function maybe_undo_transaction( $payment_id = 0 ) {
		$this->undo_transaction( $payment_id );
	}

	/**
	 * Get the payments payment method.
	 *
	 * @since 6.0.0
	 *
	 * @param int $payment_id
	 *
	 * @return mixed
	 */
	protected function get_payment_method( $payment_id ) {
		return get_post_meta( $payment_id, '_edd_payment_gateway', true );
	}

	/**
	 * Method for getting the order details from EDD
	 *
	 * @since 6.0.0
	 *
	 * @param int $payment_id
	 *
	 * @return array
	 */
	protected function get_order_details( $payment_id ) {
		// Getting the order details
		$payment_data = edd_get_payment_meta( $payment_id );

		// Getting the items in cart
		$cart_info = maybe_unserialize( $payment_data['cart_details'] );

		// Calculating totals
		$total_tax      = edd_get_payment_tax( $payment_id );
		$total_amount   = edd_get_payment_subtotal( $payment_id );
		$total_discount = edd_get_cart_discounted_amount();

		return array(
			'items'        => $cart_info,
			'total_amount' => $total_amount - $total_discount,
			'total_tax'    => $total_tax,
			'currency'     => $payment_data['currency'],
		);
	}

	/**
	 * Parse each item in format for google analytics, containing all required field
	 *
	 * @since 6.0.0
	 *
	 * @param array $item
	 *
	 * @return array
	 */
	protected function parse_item( $item ) {
		$item_category = get_the_terms( $item['id'], 'download_category' );
		if ( is_array( $item_category ) && is_object( $item_category[0] ) ) {
			$item_category = $item_category[0]->slug;
		}

		return array(
			'in' => html_entity_decode( $item['name'], ENT_QUOTES, get_bloginfo( 'charset' ) ),
			'ip' => $item['price'],
			'iq' => $item['quantity'],
			'ic' => $item['id'],
			'iv' => $item_category,
		);
	}

}
