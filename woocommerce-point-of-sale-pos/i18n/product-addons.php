<?php
/**
 * POS product addons
 *
 * Returns an array of strings
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

return array(
	0   => __( 'Options total', 'wc_point_of_sale' ),
	1   => __( 'Grand total', 'wc_point_of_sale' ),
	2   => __( 'characters remaining', 'wc_point_of_sale' ),
	3   => __( 'The minimum allowed length for "%s - %s" is %s.', 'wc_point_of_sale' ),
	4   => __( 'The maximum allowed length for "%s - %s" is %s.', 'wc_point_of_sale' ),
	5   => __( 'The minimum allowed amount for "%s - %s" is %s.', 'wc_point_of_sale' ),
	6   => __( 'The maximum allowed amount for "%s - %s" is %s.', 'wc_point_of_sale' ),
	7   => __( 'Please enter a value greater than 0 for "%s - %s".', 'wc_point_of_sale' ),
	8   => __( 'Only letters are allowed for "%s - %s".', 'wc_point_of_sale' ),
	9   => __( 'Only digits are allowed for "%s - %s".', 'wc_point_of_sale' ),
	10  => __( 'Only letters and digits are allowed for "%s - %s".', 'wc_point_of_sale' ),
	11  => __( 'A valid email address is required for "%s - %s".', 'wc_point_of_sale' ),
);
