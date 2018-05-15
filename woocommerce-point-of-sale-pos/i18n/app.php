<?php
/**
 * POS Application
 *
 * Returns an array of strings
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

return array(
	0    => __( "Product doesn't exist", 'wc_point_of_sale' ),
	1    => __( 'You cannot add another &quot;%s&quot; to your cart.', 'woocommerce' ),
	2    => __( 'Sorry, this product cannot be purchased.', 'woocommerce' ),
	3    => __( 'You cannot add &quot;%s&quot; to the cart because the product is out of stock.', 'woocommerce' ),
	4    => __( 'You cannot add that amount of &quot;%s&quot; to the cart because there is not enough stock (%s remaining).', 'woocommerce' ),
	5    => __( 'You cannot add that amount to the cart &mdash; we have %s in stock and you already have %s in your cart.', 'woocommerce' ),
	6    => __( 'Product added successfully', 'wc_point_of_sale' ),
	7    => __( 'Please choose product options&hellip;', 'woocommerce' ),
	8    => __( "Are you sure you want to clear all fields and start from scratch?", 'wc_point_of_sale'),
	9    => __( "Please add products.", 'wc_point_of_sale'),
	10   => __( "Please select Payment method.", 'wc_point_of_sale'),
	11   => __( "Please enter correct amount.", 'wc_point_of_sale'),
	12   => __( 'Order successful.', 'wc_point_of_sale'),
	13   => __( 'Successfully voided.', 'wc_point_of_sale'),
	14   => __( 'Order successfully saved.', 'wc_point_of_sale'),
	15   => __( 'Please fill Billing Details.', 'wc_point_of_sale'),
	16   => __( 'Please fill Shipping Details.', 'wc_point_of_sale'),
	17   => __( 'Please fill Additional Information.', 'wc_point_of_sale'),
	18   => __( 'Shipping title is required.', 'wc_point_of_sale'),
	19   => __( 'Shipping price is required.', 'wc_point_of_sale'),
	20   => __( 'Product title is required.', 'wc_point_of_sale'),
	21   => __( 'Product price is required.', 'wc_point_of_sale'),
	22   => __( 'Quantity is required.', 'wc_point_of_sale'),
	23   => __( 'Free shipping coupon', 'woocommerce' ),
	24   => __( 'Can\'t load order', 'wc_point_of_sale' ),
	25   => array(
		__( 'item', 'wc_point_of_sale' ),
		__( 'items', 'wc_point_of_sale' ),
		),
	26   => __( 'All', 'woocommerce' ),
	27   => __( 'Please enter the password.', 'woocommerce' ),
	28   => __( 'Incorrect password.', 'woocommerce' ),
	29   => __( 'Free!', 'woocommerce' ),
	30   => __( 'Note successfully added.', 'wc_point_of_sale' ),
	31   => __( 'Note successfully updated.', 'wc_point_of_sale' ),
	32   => __( 'Note deleted.', 'wc_point_of_sale' ),
	33   => __( 'Card not recognized.', 'wc_point_of_sale' ),
	34   => __( 'Please enter correct data.', 'wc_point_of_sale' ),
	35   => __( 'Please fill Custom Fields.', 'wc_point_of_sale'),
	36   => __( 'Invalid Barcode Scan', 'wc_point_of_sale'),
	37   => __( 'You have logged in successfully.', 'wc_point_of_sale'),
	38   => __( 'In Stock', 'wc_point_of_sale'),
	39   => __( 'Out of Stock', 'wc_point_of_sale'),
	40   => __( 'Back-order', 'wc_point_of_sale'),
    41   => __( 'Passwords don\'t match', 'wc_point_of_sale'),
    42   => __( 'Please choose a customer.', 'wc_point_of_sale'),
    43   => __('Offline order successful.', 'wc_point_of_sale'),
    44   => __('Please enter Fee Name', 'wc_point_of_sale'),
    45 => __('Item Note', 'wc_point_of_sale')
);
