<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
function monsterinsights_fbia_settings( $settings ) {
    $tracking_mode   = monsterinsights_get_option( 'tracking_mode', 'analytics' );
    if ( 'ga' === $tracking_mode ) {
        $url = esc_url( wp_nonce_url( add_query_arg( array( 'monsterinsights-action' => 'switch_to_analyticsjs', 'return' => 'fbia' ) ), 'monsterinsights-switch-to-analyticsjs-nonce' ) );
        $settings['fbia'] = array(
                'id'          => 'fbia',
                'desc'        => sprintf( esc_html__( 'Facebook Instant Articles support is only available on Universal Tracking (analytics.js). You\'re currently using deprecated ga.js tracking. We recommend switching to analytics.js, as it is significantly more accurate than ga.js, and allows for additional functionality (like the more accurate Javascript based events tracking we offer). Further Google Analytics has deprecated support for ga.js, and it may stop working at any time when Google decides to disable it from their server. To switch to using the newer Universal Analytics (analytics.js) %1$sclick here%2$s.', 'monsterinsights-facebook-instant-articles' ), '<a href="' . esc_attr( $url ) .'">', '</a>' ),
                'type'        => 'notice',
        );
    } else {
        $settings['fbia'] = array( 
            'id'    => 'fbia',
            'name'  => esc_html__( 'Facebook Instant Articles Tracking:', 'monsterinsights-facebook-instant-articles' ),
            'type'  => 'checkbox',
            'faux'  => true,
            'std'   => true,
            'field_class' => 'monsterinsights-large-checkbox',
            'desc'  => esc_html__( 'Users visiting your site via Facebook Instant Articles will be tracked automatically. No setup or configuration is required.', 'monsterinsights-facebook-instant-articles' )
        );
    }
    return $settings;
}
add_filter( 'monsterinsights_settings_fbia', 'monsterinsights_fbia_settings' );