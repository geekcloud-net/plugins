<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
function monsterinsights_amp_settings( $settings ) {
    $tracking_mode   = monsterinsights_get_option( 'tracking_mode', 'analytics' );
    if ( 'analytics' === $tracking_mode ) {
        $settings['amp'] = array( 
            'id'    => 'amp',
            'name'  => esc_html__( 'AMP Tracking:', 'monsterinsights-amp' ),
            'type'  => 'checkbox',
            'faux'  => true,
            'std'   => true,
            'field_class' => 'monsterinsights-large-checkbox',
            'desc'  => esc_html__( 'Your AMP setup has been detected and tracking is occurring automatically. No setup or configuration is required.', 'monsterinsights-amp' )
        ); 
    } else {
        $url = esc_url( wp_nonce_url( add_query_arg( array( 'monsterinsights-action' => 'switch_to_analyticsjs', 'return' => 'amp' ) ), 'monsterinsights-switch-to-analyticsjs-nonce' ) );
        $settings['amp'] = array(
                'id'          => 'amp',
                'desc'        => sprintf( esc_html__( 'Google AMP support is only available on Universal Tracking (analytics.js). You\'re currently using deprecated ga.js tracking. We recommend switching to analytics.js, as it is significantly more accurate than ga.js, and allows for additional functionality (like the more accurate Javascript based events tracking we offer). Further Google Analytics has deprecated support for ga.js, and it may stop working at any time when Google decides to disable it from their server. To switch to using the newer Universal Analytics (analytics.js) %1$sclick here%2$s.', 'monsterinsights-amp' ), '<a href="' . esc_attr( $url ) .'">', '</a>' ),
                'type'        => 'notice',
        );
    }
    return $settings;
}
add_filter( 'monsterinsights_settings_amp', 'monsterinsights_amp_settings' );