<?php
/**
 * Functions
 *
 * @author Yithemes
 * @package YITH WooCommerce Ajax Search
 * @version 1.1.1
 */

if ( ! defined( 'YITH_WCAS' ) ) {
	exit;
} // Exit if accessed directly
function getmicrotime() {
	list( $usec, $sec ) = explode( " ", microtime() );

	return ( (float) $usec + (float) $sec );
}