<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
function monsterinsights_performance_settings( $settings ) {
    $tracking_mode   = monsterinsights_get_option( 'tracking_mode', 'analytics' );
    
    if ( 'analytics' === $tracking_mode ) {
        $url   = 'https://www.monsterinsights.com/docs/how-to-setup-user-tracking/';
        $settings['samplerate'] = array(
                'id'          => 'samplerate',
                'name'        => __( 'Sample Rate', 'monsterinsights-performance' ),
                'desc'        => esc_html__( 'Specifies what percentage of users should be tracked. This defaults to 100 when field is blank (no users are sampled out) but large sites may need to use a lower sample rate to stay within Google Analytics processing limits. Note, setting this setting to a number lower than 100 means not all of your users will be tracked into Google Analytics.' , 'monsterinsights-performance' ),
                'type'        => 'number',
                'min'         => 0,
                'max'         => 100,
                'std'         => 100,
                'step'        => 1,
        );
        $settings['speedsamplerate'] = array(
                'id'          => 'speedsamplerate',
                'name'        => __( 'Site Speed Sample Rate', 'monsterinsights-performance' ),
                'desc'        => esc_html__( 'This setting determines how often site speed tracking beacons will be sent. By default, 1% of users will automatically be tracked if this setting is empty.' , 'monsterinsights-performance' ),
                'type'        => 'number',
                'min'         => 0,
                'max'         => 100,
                'std'         => 1,
                'step'        => 1,
        );
    } else {
        $url = esc_url( wp_nonce_url( add_query_arg( array( 'monsterinsights-action' => 'switch_to_analyticsjs', 'return' => 'performance' ) ), 'monsterinsights-switch-to-analyticsjs-nonce' ) );
        $settings['samplerate'] = array(
                'id'          => 'samplerate',
                'name'        => __( 'Enable Sample Rate', 'monsterinsights-performance' ),
                'desc'        => sprintf( esc_html__( 'Sample Rate is only available on Universal Tracking (analytics.js). You\'re currently using deprecated ga.js tracking. We recommend switching to analytics.js, as it is significantly more accurate than ga.js, and allows for additional functionality (like the more accurate Javascript based events tracking we offer). Further Google Analytics has deprecated support for ga.js, and it may stop working at any time when Google decides to disable it from their server. To switch to using the newer Universal Analytics (analytics.js) %1$sclick here%2$s.', 'monsterinsights-performance' ), '<a href="' . esc_attr( $url ) .'">', '</a>' ),
                'type'        => 'notice',
        );
        $settings['speedsamplerate'] = array(
                'id'          => 'speedsamplerate',
                'name'        => __( 'Enable Site Speed Sample Rate', 'monsterinsights-performance' ),
                'desc'        => sprintf( esc_html__( 'Site Speed Sample Rate is only available on Universal Tracking (analytics.js). You\'re currently using deprecated ga.js tracking. We recommend switching to analytics.js, as it is significantly more accurate than ga.js, and allows for additional functionality (like the more accurate Javascript based events tracking we offer). Further Google Analytics has deprecated support for ga.js, and it may stop working at any time when Google decides to disable it from their server. To switch to using the newer Universal Analytics (analytics.js) %1$sclick here%2$s.', 'monsterinsights-performance' ), '<a href="' . esc_attr( $url ) .'">', '</a>' ),
                'type'        => 'notice',
        );
    }
    return $settings;
}
add_filter( 'monsterinsights_settings_performance', 'monsterinsights_performance_settings' );