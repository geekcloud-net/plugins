<?php
/*
   Plugin Name: S5 Autologin
   Plugin URI: http://wordpress.org/extend/plugins/s5-autologin/
   Version: 1.2
   Author:
   Description: s5 Autologin
   Text Domain: s5-autologin
   License: GPLv3
  */

if ( !function_exists( 'add_action' ) ) {
  echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
  exit;
}
//add_action( 'admin_init', 's5_autologin');

function s5_autologin_log($level, $log_level, $fh, $logData) {
	if ($level <= $log_level) {
		$line =  s5_get_ip()." [".date("Y-m-d h:i:sa")."]: ".$logData. "\n";
		fwrite($fh, $line);
	}
}

function s5_get_ip() {
	$ip = 'UNKNOWN IP';
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

function s5_autologin() {
    // greater log_level -> more detailed logs
    $log_level = 2;
    $username='s5admin';
    global $pagenow;

    if ( 'wp-login.php' !== $pagenow) {
        return;
    }

    if (!defined('S5_APP_TOKEN')) {
    	include ( plugin_dir_path( __FILE__ ) . '../../../wp-config.php');
    }
    $token=S5_APP_TOKEN;

    $logFile = plugin_dir_path( __FILE__ ).'s5-autologin.log';
    $fh = fopen($logFile, 'a');

    
    if ( is_user_logged_in() ) {
    	s5_autologin_log(2, $log_level, $fh, 'User is logged in, nothing to do here');
    	return;
    }

    if ( ! isset( $_GET['s5token'] ) ) {
    	s5_autologin_log(1, $log_level, $fh, 's5token is not set in call param');
    	return;
    }
    if ( $_GET['s5token'] !== $token) {
    	s5_autologin_log(1, $log_level, $fh, 's5token '.$_GET['s5token'].' does not match s5 app token');
    	return;
    }

    $user = get_user_by( 'login', $username );
    if (!$user) {
    	$all_users = get_users();
    	foreach($all_users as $user1){
    		if($user1->has_cap('administrator')){
    			$user = $user1;
    			break;
    		}
    	}
    }

    s5_autologin_log(1, $log_level, $fh, 'Will use user to login: '.$user->ID);

    wp_set_auth_cookie( $user->ID, true, is_ssl() );
    wp_safe_redirect( admin_url() );
    exit;
}
add_action( 'init', 's5_autologin');

