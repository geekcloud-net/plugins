<?php

$wpseo_local_test_autoload_file = dirname( dirname( __FILE__ ) ) . '/vendor/autoload.php';
if ( PHP_VERSION_ID < 50300 ) {
	$wpseo_local_test_autoload_file = dirname( dirname( __FILE__ ) ) . '/vendor/autoload_52.php';
}

if ( file_exists( $wpseo_local_test_autoload_file ) ) {
	require_once $wpseo_local_test_autoload_file;
}
