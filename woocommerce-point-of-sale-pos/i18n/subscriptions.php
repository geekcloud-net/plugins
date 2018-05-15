<?php
/**
 * POS Subscriptions addon
 *
 * Returns an array of strings
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

return array(
	0   => __( 'A subscription renewal has been removed from your cart. Multiple subscriptions can not be purchased at the same time.', 'woocommerce-subscriptions' ),
	1   => __( 'A subscription has been removed from your cart. Due to payment gateway restrictions, different subscription products can not be purchased at the same time.', 'woocommerce-subscriptions' ),
	2   => __( 'A subscription has been removed from your cart. Products and subscriptions can not be purchased at the same time.', 'woocommerce-subscriptions' ),
	3   => __( '%1$s every %2$s', 'woocommerce-subscriptions' ),
	4   => __( '%1$s every %2$s on %3$s', 'woocommerce-subscriptions' ),
	5   => __( '%s on the last day of each month', 'woocommerce-subscriptions' ),
	6   => __( '%1$s on the %2$s of each month', 'woocommerce-subscriptions' ),
	7   => __( '%1$s on the last day of every %2$s month', 'woocommerce-subscriptions' ),
	8   => __( '%1$s on the %2$s day of every %3$s month', 'woocommerce-subscriptions' ),
	9   => __( '%1$s on %2$s %3$s each year', 'woocommerce-subscriptions' ),
	10  => __( '%1$s on %2$s %3$s every %4$s year', 'woocommerce-subscriptions' ),
	11  => array(
		__( 'day', 'wc_point_of_sale' ),
		__( '%s days', 'wc_point_of_sale' ),
		__( 'week', 'wc_point_of_sale' ),
		__( '%s weeks', 'wc_point_of_sale' ),
		__( 'month', 'wc_point_of_sale' ),
		__( '%s months', 'wc_point_of_sale' ),
		__( 'year', 'wc_point_of_sale' ),
		__( '%s years', 'wc_point_of_sale' ),
	),
	12  => array(
		__( '%sth', 'woocommerce-subscriptions' ),
		__( '%sst', 'woocommerce-subscriptions' ),
		__( '%snd', 'woocommerce-subscriptions' ),
		__( '%srd', 'woocommerce-subscriptions' ),
	),
	13  => array(
		__( '%1$s / %2$s', 'wc_point_of_sale' ),
		__( ' %1$s every %2$s', 'wc_point_of_sale' )
	),
	14  => __( '%1$s for %2$s', 'woocommerce-subscriptions' ),
	15  => __( '%1$s with %2$s free trial', 'woocommerce-subscriptions' ),
	16  => array(
		__( '%s day', 'wc_point_of_sale' ),
		__( 'a %s-day', 'wc_point_of_sale' ),
		__( '%s week', 'wc_point_of_sale' ),
		__( 'a %s-week', 'wc_point_of_sale' ),
		__( '%s month', 'wc_point_of_sale' ),
		__( 'a %s-month', 'wc_point_of_sale' ),
		__( '%s year', 'wc_point_of_sale' ),
		__( 'a %s-year', 'wc_point_of_sale' ),
	),
	17  => __( '%1$s and a %2$s sign-up fee', 'woocommerce-subscriptions' ),
);
