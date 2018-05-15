<?php
function monsterinsights_fbia_params( $options ) {
	if ( function_exists( 'is_transforming_instant_article' ) && is_transforming_instant_article() ) {
		if ( ! defined( 'MI_NO_TRACKING_OPTOUT' ) ) {
			define( 'MI_NO_TRACKING_OPTOUT', true );
		}
		$options['campaignSource'] = "'set', 'campaignSource', 'Facebook'";
		$options['campaignMedium'] = "'set', 'campaignMedium', 'Social Instant Article'";
		$options['title']          = "'set', 'title', '" . esc_js( get_the_title() ) . "'";
	}
	return $options;
}
add_filter( 'monsterinsights_frontend_tracking_options_analytics_before_pageview', 'monsterinsights_fbia_params' );