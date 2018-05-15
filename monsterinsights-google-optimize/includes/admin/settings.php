<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function monsterinsights_warn_performance_settings( $settings ) {
    $container = monsterinsights_get_option( 'goptimize_container', '' );
    if ( ! empty( $container ) && isset( $settings['goptimize_header'] ) ) {
        // Remove old settings from performance addon if they exist.
        unset( $settings['goptimize_header'] );
        unset( $settings['goptimize_container'] );
        unset( $settings['goptimize_pagehide'] );
        unset( $settings['goptimize_pagehide_speed'] );
    }
    return $settings;
}
add_filter( 'monsterinsights_settings_performance', 'monsterinsights_warn_performance_settings', 11 );

function monsterinsights_google_optimize_settings( $settings ) {
    $tracking_mode   = monsterinsights_get_option( 'tracking_mode', 'analytics' );
    if ( 'analytics' === $tracking_mode ) {
        $settings['goptimize_container'] = array(
            'id'          => 'goptimize_container',
            'name'        => __( 'Google Optimize Container ID', 'google-analytics-for-wordpress' ),
            'desc'        => sprintf( esc_html__( 'This should be in the format %s' , 'monsterinsights-google-optimize' ), '<code>GTM-XXXXXX</code>' ),
            'type'        => 'text',
        );
        $settings['goptimize_pagehide'] = array(
            'id'          => 'goptimize_pagehide',
            'name'        => __( 'Enable Google Optimize Async Page Hide', 'google-analytics-for-wordpress' ),
            'desc'        => esc_html__( 'We recommend that you also use this feature to automatically output the page-hiding snippet which reduces the risk of page flicker. This feature also helps ensure that users on slow connections have a better experience by only showing experiment variants when the Optimize container loads within the set timeout (which you can configure below).' , 'monsterinsights-google-optimize' ),
            'type'        => 'checkbox',
        );
        $settings['goptimize_pagehide_speed'] = array(
                'id'          => 'goptimize_pagehide_speed',
                'name'        => __( 'Maximum Time To Hide Page', 'monsterinsights-google-optimize' ),
                'desc'        => esc_html__( 'The maximum time (in milliseconds) the page will be hidden if using the Google Optimize Async Page Hide feature directly above. Once Optimize is ready or this maximum time is reached (whichever comes first) the page will become visible again. Default: 4000.' , 'monsterinsights-google-optimize' ),
                'type'        => 'number',
                'min'         => 1,
                'max'         => 8000,
                'std'         => 4000,
                'step'        => 1,
        );
        // Todo: Allow for multiple Google Optimize Container IDs
        // Todo: Allow for customization of the timeout
    } else {
        $url = esc_url( wp_nonce_url( add_query_arg( array( 'monsterinsights-action' => 'switch_to_analyticsjs', 'return' => 'goptimize' ) ), 'monsterinsights-switch-to-analyticsjs-nonce' ) );
        $settings['goptimize_container_analyticsjs'] = array(
                'id'          => 'goptimize_container_analyticsjs',
                'desc'        => sprintf( esc_html__( 'Google Optimize support is only available on Universal Tracking (analytics.js). You\'re currently using deprecated ga.js tracking. We recommend switching to analytics.js, as it is significantly more accurate than ga.js, and allows for additional functionality (like the more accurate Javascript based events tracking we offer). Further Google Analytics has deprecated support for ga.js, and it may stop working at any time when Google decides to disable it from their server. To switch to using the newer Universal Analytics (analytics.js) %1$sclick here%2$s.', 'monsterinsights-google-optimize' ), '<a href="' . esc_attr( $url ) .'">', '</a>' ),
                'type'        => 'notice',
        );
    }
    return $settings;
}
add_filter( 'monsterinsights_settings_goptimize', 'monsterinsights_google_optimize_settings' );