<?php

/**
 * Part of Woo Mercado Pago Module
 * Author - Mercado Pago
 * Developer - Marcelo Tomio Hama / marcelo.hama@mercadolivre.com
 * Copyright - Copyright(c) MercadoPago [https://www.mercadopago.com]
 * License - https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Build and handle a window for refunding and canceling
add_action( 'add_meta_boxes', 'add_meta_boxes' );
function add_meta_boxes() {
	// Get order.
	global $post;
	$order = wc_get_order( $post->ID );
	if ( ! isset( $order ) || $order == false ) {
		return;
	}
	$order_id = trim( str_replace( '#', '', $order->get_order_number() ) );
	// Get payment information for the order.
	$payments = get_post_meta( $order_id, '_Mercado_Pago_Sub_Payment_IDs', true );
	if ( isset( $payments ) && ! empty( $payments ) ) {
		add_meta_box(
			'woocommerce-mp-order-action-refund',
			__( 'Mercado Pago Subscription', 'woocommerce-mercadopago' ),
			'mp_subscription_order_refund_cancel_box',
			'shop_order',
			'side',
			'default'
		);
	}
}

function mp_subscription_order_refund_cancel_box() {
	// Get order.
	global $post;
	$order = wc_get_order( $post->ID );
	if ( ! isset( $order ) || $order == false ) {
		return;
	}
	$order_id = trim( str_replace( '#', '', $order->get_order_number() ) );
	// Get payment information for the order.
	$payments = get_post_meta( $order_id, '_Mercado_Pago_Sub_Payment_IDs', true );
	$options = '';
	if ( ! empty( $payments ) ) {
		$payment_structs = array();
		$payment_ids = explode( ', ', $payments );
		foreach ( $payment_ids as $p_id ) {
			$options .= '<option value="' . $p_id . '">' . $p_id . '</option>';
		}
	}
	if ( $options == '' ) {
		return;
	}
	// Build javascript for the window.
	$domain = get_site_url() . '/index.php' . '/woocommerce-mercadopago/';
	$domain .= '?wc-api=WC_WooMercadoPago_SubscriptionGateway';
	echo WC_Woo_Mercado_Pago_Module::generate_refund_cancel_subscription(
		$domain,
		__( 'Operation successfully completed.', 'woocommerce-mercadopago' ),
		__( 'This operation could not be completed.', 'woocommerce-mercadopago' ),
		$options,
		__( 'Payment ID:', 'woocommerce-mercadopago' ),
		__( 'Amount:', 'woocommerce-mercadopago' ),
		__( 'Refund Payment', 'woocommerce-mercadopago' ),
		__( 'Cancel Payment', 'woocommerce-mercadopago' )
	);
}

// Makes the recurrent product individually sold
add_filter( 'woocommerce_is_sold_individually', 'default_no_quantities', 10, 2 );
function default_no_quantities( $individually, $product ) {
	$product_id = ( method_exists( $product, 'get_id' ) ) ?
		$product->get_id() :
		$product->id;
	$is_recurrent = get_post_meta( $product_id, '_mp_recurring_is_recurrent', true );
	if ( $is_recurrent == 'yes' ) {
		$individually = true;
	}
	return $individually;
}

// Prevent selling recurrent products together with other products
add_action( 'woocommerce_check_cart_items', 'check_recurrent_product_singularity' );
function check_recurrent_product_singularity() {
	global $woocommerce;
	$w_cart = $woocommerce->cart;
	if ( ! isset( $w_cart ) ) {
		return;
	}
	$items = $w_cart->get_cart();
	if ( sizeof( $items ) > 1 ) {
		foreach ( $items as $cart_item_key => $cart_item ) {
			$is_recurrent = get_post_meta( $cart_item['product_id'], '_mp_recurring_is_recurrent', true );
			if ( $is_recurrent == 'yes' ) {
				wc_add_notice(
					__( 'A recurrent product is a signature that should be bought isolated in your cart. Please, create separated orders.', 'woocommerce-mercadopago' ),
					'error'
				);
			}
		}
	}
}

// Validate product date availability.
add_filter( 'woocommerce_is_purchasable', 'filter_woocommerce_is_purchasable', 10, 2 );
function filter_woocommerce_is_purchasable( $purchasable, $product ) {
	$product_id = ( method_exists( $product, 'get_id' ) ) ?
		$product->get_id() :
		$product->id;
	// skip this check if product is not a subscription
	$is_recurrent = get_post_meta( $product_id, '_mp_recurring_is_recurrent', true );
	if ( $is_recurrent !== 'yes' ) {
		return $purchasable;
	}
	$today_date = date( 'Y-m-d' );
	$end_date = get_post_meta( $product_id, '_mp_recurring_end_date', true );
	// If there is no date, we should just return the original value.
	if ( ! isset( $end_date ) || empty( $end_date ) ) {
		return $purchasable;
	}
	// If end date had passed, this product is no longer available.
	$days_diff = ( strtotime( $today_date ) - strtotime( $end_date ) ) / 86400;
	if ( $days_diff >= 0 ) {
		return false;
	}
	return $purchasable;
}

// Add the settings under 'general' sub-menu.
add_action( 'woocommerce_product_options_general_product_data', 'mp_add_recurrent_settings' );
function mp_add_recurrent_settings() {
	wp_nonce_field( 'woocommerce_save_data', 'woocommerce_meta_nonce' );
	echo '<div class="options_group show_if_simple">';
		woocommerce_wp_checkbox(
			array(
				'id' => '_mp_recurring_is_recurrent',
				'label' => __( 'Recurrent Product', 'woocommerce-mercadopago' ),
				'description' => __( 'Make this product a subscription.', 'woocommerce-mercadopago' )
			)
		);
		woocommerce_wp_text_input(
			array(
				'id' => '_mp_recurring_frequency',
				'label' => __( 'Frequency', 'woocommerce-mercadopago' ),
				'placeholder' => '1',
				'desc_tip' => 'true',
				'description' => __( 'Amount of time (in days or months) for the execution of the next payment.', 'woocommerce-mercadopago' ),
				'type' => 'number'
			)
		);
		woocommerce_wp_select(
			array(
				'id' => '_mp_recurring_frequency_type',
				'label' => __( 'Frequency type', 'woocommerce-mercadopago' ),
				'desc_tip' => 'true',
				'description' => __( 'Indicates the period of time.', 'woocommerce-mercadopago' ),
				'options' => array(
					'days' => __( 'Days', 'woocommerce-mercadopago' ),
					'months' => __( 'Months', 'woocommerce-mercadopago' )
				)
			)
		);
		woocommerce_wp_text_input(
			array(
				'id' => '_mp_recurring_end_date',
				'label' => __( 'End date', 'woocommerce-mercadopago' ),
				'placeholder' => _x( 'YYYY-MM-DD', 'placeholder', 'woocommerce-mercadopago' ),
				'desc_tip' => 'true',
				'description' => __( 'Deadline to generate new charges. Defaults to never if blank.', 'woocommerce-mercadopago' ),
				'class' => 'date-picker',
				'custom_attributes' => array( 'pattern' => "[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" )
			)
		);
	echo '</div>';
}

// Persists the options saved in product metadata.
add_action( 'woocommerce_process_product_meta', 'mp_save_recurrent_settings' );
function mp_save_recurrent_settings( $post_id ) {
	$_mp_recurring_is_recurrent = $_POST['_mp_recurring_is_recurrent'];
	if ( ! empty( $_mp_recurring_is_recurrent ) ) {
		update_post_meta( $post_id, '_mp_recurring_is_recurrent', esc_attr( $_mp_recurring_is_recurrent ) );
	} else {
		update_post_meta( $post_id, '_mp_recurring_is_recurrent', esc_attr( null ) );
	}
	$_mp_recurring_frequency = $_POST['_mp_recurring_frequency'];
	if ( ! empty( $_mp_recurring_frequency ) ) {
		update_post_meta( $post_id, '_mp_recurring_frequency', esc_attr( $_mp_recurring_frequency ) );
	} else {
		update_post_meta( $post_id, '_mp_recurring_frequency', esc_attr( 1 ) );
	}
	$_mp_recurring_frequency_type = $_POST['_mp_recurring_frequency_type'];
	if ( ! empty( $_mp_recurring_frequency_type ) ) {
		update_post_meta( $post_id, '_mp_recurring_frequency_type', esc_attr( $_mp_recurring_frequency_type ) );
	} else {
		update_post_meta( $post_id, '_mp_recurring_frequency_type', esc_attr( 'days' ) );
	}
	$_mp_recurring_end_date = $_POST['_mp_recurring_end_date'];
	if ( ! empty( $_mp_recurring_end_date ) ) {
		update_post_meta( $post_id, '_mp_recurring_end_date', esc_attr( $_mp_recurring_end_date ) );
	} else {
		update_post_meta( $post_id, '_mp_recurring_end_date', esc_attr( null ) );
	}
}
