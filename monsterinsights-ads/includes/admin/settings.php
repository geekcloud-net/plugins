<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function monsterinsights_ads_settings( $settings ) {
	/** Ads Tracking Settings */
	$settings['track_adsense'] = array(
		'id'          => 'track_adsense',
		'name'        => __( 'Enable Google Adsense tracking', 'google-analytics-for-wordpress' ),
		'desc'        => sprintf( esc_html__( 'This requires integration of your Analytics and AdSense account. For how to do this, see %1$sthis help page%2$s.', 'monsterinsights-ads' ), '<a href="https://support.google.com/adsense/answer/94743?ref_topic=23415&hl=' . get_locale() . '&utm_source=MonsterInsights&utm_medium=partnerships&utm_campaign=MonsterInsightsPartner" target="_blank" rel="noopener noreferrer" referrer="no-referrer">', '</a>' ),
		'type'        => 'checkbox'
	);
	return $settings;
}
add_filter( 'monsterinsights_settings_ads', 'monsterinsights_ads_settings' );