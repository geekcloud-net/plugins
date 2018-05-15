<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function monsterinsights_goptimize_frontend_tracking_options_analytics( $options ) {
    $container = monsterinsights_get_option( 'goptimize_container', '' );
    if ( ! empty( $container ) ) {
        $options['goptimize_container'] = "'require', '" . esc_js( $container ) . "'";
    }
    return $options;
}
add_filter( 'monsterinsights_frontend_tracking_options_analytics_before_pageview', 'monsterinsights_goptimize_frontend_tracking_options_analytics', 10, 1 );
remove_filter( 'monsterinsights_frontend_tracking_options_analytics_before_pageview', 'monsterinsights_performance_frontend_tracking_options_analytics', 10, 1 );

function monsterinsights_goptimize_frontend_tracking_options_before_analytics() {
    $pagehide = monsterinsights_get_option( 'goptimize_pagehide', false );
    if ( ! $pagehide ) {
        return;
    }

    $container = monsterinsights_get_option( 'goptimize_container', '' );
    if ( empty( $container ) ) {
        return;
    }
    $speed = absint( monsterinsights_get_option( 'goptimize_pagehide_speed', 4000 ) );
    ob_start();
    ?>
<style>.monsterinsights-async-hide { opacity: 0 !important} </style>
<script>(function(a,s,y,n,c,h,i,d,e){s.className+=' '+y;h.start=1*new Date;
h.end=i=function(){s.className=s.className.replace(RegExp(' ?'+y),'')};
(a[n]=a[n]||[]).hide=h;setTimeout(function(){i();h.end=null},c);h.timeout=c;
})(window,document.documentElement,'monsterinsights-async-hide','dataLayer',<?php echo $speed; ?>,
{<?php echo "'" . esc_js( $container ) . "'"; ?>:true});</script>
        <?php
    echo ob_get_clean();
}
add_action( 'monsterinsights_tracking_before', 'monsterinsights_goptimize_frontend_tracking_options_before_analytics' );
remove_action( 'monsterinsights_tracking_before', 'monsterinsights_performance_frontend_tracking_options_before_analytics' );