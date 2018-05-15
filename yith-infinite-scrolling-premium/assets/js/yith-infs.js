/**
 * yith-infs.js
 *
 * @author Your Inspiration Themes
 * @package YITH Infinite Scrolling
 * @version 1.0.0
 */

jQuery(document).ready( function($) {
    "use strict";

    if( typeof yith_infs_premium !== 'undefined' && yith_infs_premium.options ) {

        $.fn.init_infinitescroll = function() {
            $.each( yith_infs_premium.options, function (key, value) {

                if( ! ( $( value.nextSelector ).length && $( value.navSelector ).length && $( value.itemSelector ).length && $( value.contentSelector ).length ) ) {
                    return;
                }

                $.yit_infinitescroll(value);
            });
        };

        $.fn.init_infinitescroll();

        $(document).on( 'yith-wcan-ajax-loading', function(){
            $( '.yith-infs-button-wrapper' ).remove();
        });

        $(document).on( 'yith-wcan-ajax-filtered woof_ajax_done facetwp-loaded', function(){
            // reset
            $( window ).unbind( 'yith_infs_start' );
            // initialize 
            $.fn.init_infinitescroll();
        });

    }
});