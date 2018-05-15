<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function monsterinsights_performance_frontend_tracking_options_analytics_create( $create ) {
    $samplerate      = monsterinsights_get_option( 'samplerate', 100 );
    $speedsamplerate = monsterinsights_get_option( 'speedsamplerate', 1 );

    $samplerate = abs( intval( $samplerate ) );
    if ( $samplerate >= 100 ) {
        $samplerate = ''; // if 100 or over, use default 100 (aka don't send setting to GA)
    }

    if ( ! is_array( $create ) ) {
        $create = array();
    }

    if ( $samplerate > 0 && $samplerate < 100 && $samplerate !== ( int ) 0 ) {
        $create['sampleRate'] = $samplerate;
    }

    $speedsamplerate = abs( intval( $speedsamplerate ) );
    if ( $speedsamplerate > 100 ) {
        $speedsamplerate = ''; // if 100 or over, use default 1 (aka don't send setting to GA)
    }

    if ( $speedsamplerate > 0 && $speedsamplerate <= 100 && $speedsamplerate !== 1 && $speedsamplerate !== ( int ) 0 ) {
        $create['siteSpeedSampleRate'] = $speedsamplerate;
    }

    return $create;
}
add_filter( 'monsterinsights_frontend_tracking_options_analytics_create', 'monsterinsights_performance_frontend_tracking_options_analytics_create', 10, 1 );