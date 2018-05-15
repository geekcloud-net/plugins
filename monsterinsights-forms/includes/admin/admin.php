<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
function monsterinsights_forms_settings( $settings ) {
    $tracking_mode   = monsterinsights_get_option( 'tracking_mode', 'analytics' );
    if ( 'ga' === $tracking_mode ) {
        $url = esc_url( wp_nonce_url( add_query_arg( array( 'monsterinsights-action' => 'switch_to_analyticsjs', 'return' => 'forms' ) ), 'monsterinsights-switch-to-analyticsjs-nonce' ) );
        $settings['forms'] = array(
                'id'          => 'forms',
                'desc'        => sprintf( esc_html__( 'Form tracking support is only available on Universal Tracking (analytics.js). You\'re currently using deprecated ga.js tracking. We recommend switching to analytics.js, as it is significantly more accurate than ga.js, and allows for additional functionality (like the more accurate Javascript based events tracking we offer). Further Google Analytics has deprecated support for ga.js, and it may stop working at any time when Google decides to disable it from their server. To switch to using the newer Universal Analytics (analytics.js) %1$sclick here%2$s.', 'monsterinsights-forms' ), '<a href="' . esc_attr( $url ) .'">', '</a>' ),
                'type'        => 'notice',
        );
    } else {
        $settings['forms'] = array( 
            'id'    => 'forms',
            'name'  => esc_html__( 'Form Tracking:', 'monsterinsights-forms' ),
            'type'  => 'checkbox',
            'faux'  => true,
            'std'   => true,
            'field_class' => 'monsterinsights-large-checkbox',
            'desc'  => esc_html__( 'Impressions and conversions are being logged for visitors to your site. No setup or configuration is required.', 'monsterinsights-forms' )
        );
    }
    return $settings;
}
add_filter( 'monsterinsights_settings_forms', 'monsterinsights_forms_settings' );