<?php
/**
 * Amazon S3 Storage Ajax Handlers
 *
 * Handles the Amazon S3 Storage AJAX requests via wp_ajax hook
 *
 * @author 		Gerhard Potgieter
 * @category 	AJAX
 * @package 	WooCommerce
 */

function woo_amazon_s3_load_objects() {
	if( ! is_admin() ) die;
	if( ! current_user_can( 'edit_posts' ) ) wp_die( __( 'You do not have sufficient permissions to access this page.', 'wc_amazon_s3' ) );
	check_ajax_referer( 'amazon-s3-load-objects', 'security' );
	global $WooCommerce_Amazon_S3_Storage;
	require_once 'amazon_sdk/sdk.class.php';
	//Disable error reporting for warning message if ssl verification disabled.
	//error_reporting(0);
	try {
		$s3 = new AmazonS3( $WooCommerce_Amazon_S3_Storage->credentials );
		$WooCommerce_Amazon_S3_Storage->set_ssl( $s3 );
		$current_bucket = $_POST['bucket'];
		$object_arr = array();
		if( ! empty( $current_bucket ) ) {
			$objects = $s3->get_object_list( $current_bucket );
			foreach( $objects as $key => $object ) {
				// Do not add folders
				if ( substr( $object, -1) <> '/' )
					echo '<option value="' . $object . '">' . $object . '</option>';
			}
		}
	} catch( Exception $e ) {

	}
	die();
}
add_action( 'wp_ajax_woo_amazon_s3_load_objects', 'woo_amazon_s3_load_objects' );

function woo_amazon_s3_load_buckets() {

	if( ! current_user_can( 'edit_posts' ) ) wp_die( __( 'You do not have sufficient permissions to access this page.', 'wc_amazon_s3' ) );

	check_ajax_referer( 'amazon-s3-load-objects', 'security' );
	global $WooCommerce_Amazon_S3_Storage;
	require_once 'amazon_sdk/sdk.class.php';
	//Disable error reporting for warning message if ssl verification disabled.
	error_reporting(0);
	try {
		$s3 = new AmazonS3( $WooCommerce_Amazon_S3_Storage->credentials );
		$WooCommerce_Amazon_S3_Storage->set_ssl( $s3 );
		$buckets = $s3->get_bucket_list();

		echo '<option value="-1">No Bucket</option>';

		foreach( $buckets as $key => $bucket ) {
			echo '<option value="' . $bucket . '">' . $bucket . '</option>';
		}
	} catch( Exception $e ) {

	}
	die();
}
add_action( 'wp_ajax_woo_amazon_s3_load_buckets', 'woo_amazon_s3_load_buckets' );
