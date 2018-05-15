<?php
/**
 * POS Coupons
 *
 * Returns an array of strings
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

return array(
	100    => __( 'Coupon is not valid.', 'wc_point_of_sale' ),
	101    => __( 'Sorry, it seems the coupon "%s" is invalid - it has now been removed from your order.', 'wc_point_of_sale' ),
	102    => __( 'Sorry, it seems the coupon "%s" is not yours - it has now been removed from your order.', 'wc_point_of_sale' ),
	103    => __( 'Coupon code already applied!', 'wc_point_of_sale' ),
	104    => __( 'Sorry, coupon "%s" has already been applied and cannot be used in conjunction with other coupons.', 'wc_point_of_sale' ),
	105    => __( 'Coupon "%s" does not exist!', 'wc_point_of_sale' ),
	106    => __( 'Coupon usage limit has been reached.', 'wc_point_of_sale' ),
	107    => __( 'This coupon has expired.', 'wc_point_of_sale' ),
	108    => __( 'The minimum spend for this coupon is %s.', 'wc_point_of_sale' ),
	109    => __( 'Sorry, this coupon is not applicable to your cart contents.', 'wc_point_of_sale' ),
	110    => __( 'Sorry, this coupon is not valid for sale items.', 'wc_point_of_sale' ),
	111    => __( 'Please enter a coupon code.', 'wc_point_of_sale' ),
	112    => __( 'The maximum spend for this coupon is %s.', 'wc_point_of_sale' ),
	113    => __( 'Sorry, this coupon is not applicable to the products: %s.', 'wc_point_of_sale' ),
	114    => __( 'Sorry, this coupon is not applicable to the categories: %s.', 'wc_point_of_sale' ),
	200    => __( 'Coupon code applied successfully.', 'wc_point_of_sale' ),
	201    => __( 'Coupon code removed successfully.', 'wc_point_of_sale' ),
	202    => __( 'Discount added successfully.', 'wc_point_of_sale' ),
	203    => __( 'Discount updated successfully.', 'wc_point_of_sale' ),
);
