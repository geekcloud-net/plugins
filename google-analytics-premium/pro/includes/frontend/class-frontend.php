<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function monsterinsights_add_analytics_options( $options ) {
	if ( monsterinsights_get_option( 'userid', false ) && is_user_logged_in() ) {
		$options['userid'] = "'set', 'userId', '". get_current_user_id() . "'";
	}
	return $options;
}

add_filter( 'monsterinsights_frontend_tracking_options_analytics_before_scripts', 'monsterinsights_add_analytics_options' );