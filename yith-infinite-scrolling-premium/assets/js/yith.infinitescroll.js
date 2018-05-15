(function ($, window, document) {
    "use strict";

    $.yit_infinitescroll = function (options) {

        var opts = $.extend({

                nextSelector    : '',
                navSelector     : '',
                itemSelector    : '',
                contentSelector : '',
                eventType       : 'scroll',
                presetLoader    : '',
                customLoader    : '',
                buttonLabel     : '',
                buttonClass     : '',
                loadEffect      : 'fadeIn'

            }, options),

            is_shop         =  ( typeof yith_infs_script !== 'undefined' ) ? yith_infs_script.shop : false,
            block_loader    =  ( typeof yith_infs_script !== 'undefined' ) ? yith_infs_script.block_loader : false,

            loading         = false,
            finished        = false,
            loader          = false,
            button          = false,
            desturl         = $( opts.nextSelector ).attr( 'href' ), // init next url
            page_history    = [],
            elm_history     = [],
            last_elem;

        // change url function
        var change_url = function( url, title ) {
            // if different from previous change it
            if( window.location.href != url ) {
                window.history.replaceState({url: "" + url + ""}, "Title", url);
                if( title ) {
                    document.title = title;
                }
                $(document).trigger('yith_infs_url_changed', [ url, title ]);
            }
        };

        // hide std navigation
        if( opts.eventType != 'pagination' ) {
            $( opts.navSelector ).hide();
        }

        // set elem columns ( in shop page )
        if( is_shop ) {
            var first_elem  = $( opts.itemSelector ).first(),
                columns = first_elem.nextUntil( '.first', opts.itemSelector ).length + 1;
        }

        // main ajax function
        var main_ajax = function () {

            // add loader if any
            if( loader ) {
                $( opts.navSelector ).last().after('<div class="yith-infs-loader">' + loader + '</div>');
            }

            // set loading true
            loading = true;
            // decode url to prevent error
            desturl = decodeURIComponent(desturl);
            desturl = desturl.replace(/^(?:\/\/|[^\/]+)*\//, "/");

            // ajax call
            $.ajax({
                // params
                url         : desturl,
                dataType    : 'html',
                cache       : yith_infs_script.use_cache,
                success     : function (data) {

                    // set last elem
                    last_elem   = $( opts.itemSelector ).last();

                    var obj  = $( data ),
                        cont = obj.find( opts.contentSelector ),
                        elem = obj.find( opts.itemSelector ),
                        nav  = obj.find( opts.navSelector ),
                        next = obj.find( opts.nextSelector ),
                        current_url = desturl;

                    if( next.length ) {
                        desturl = next.attr( 'href' );
                    }
                    else {
                        // set finished var true
                        finished = true;
                        $( document ).trigger( 'yith-infs-scroll-finished' );
                    }

                    // recalculate element position in shop
                    if( is_shop && ! last_elem.hasClass( 'last' ) && opts.eventType != 'pagination' ) {
                        position_elem( last_elem, columns, elem );
                    }

                    //change nav
                    $(opts.navSelector).replaceWith( nav );

                    if( opts.eventType != 'pagination' ) {
                        last_elem.after( elem );
                        $(opts.navSelector).hide();
                    }
                    else {
                        $(opts.contentSelector).replaceWith(cont);
                        $( window ).scrollTop( $( opts.contentSelector).offset().top );
                    }

                    // remove loader if any
                    $( '.yith-infs-loader' ).remove();

                    $(document).trigger( 'yith_infs_adding_elem', [elem, current_url] );

                    elem.addClass( 'yith-infs-animated' );
                    elem.addClass( opts.loadEffect );

                    // change url
                    if( yith_infs_script.change_url ) {
                        // get page title
                        var next_title = obj.filter('title').length ? obj.filter('title').text() : '';
                        // save url, title and elem history if event is not pagination
                        if( opts.eventType != 'pagination' ) {
                            // on first call salve window url
                            if( ! page_history.length ) {
                                page_history.push( [ window.location.href, document.title ] );
                            }
                            // save current
                            page_history.push( [ current_url, next_title ] );
                            elm_history.push( last_elem );
                        }

                        change_url( current_url, next_title );
                    }

                    setTimeout( function(){
                        loading = false;
                        // remove animation class
                        elem.removeClass( 'yith-infs-animated' );
                        elem.removeClass( opts.loadEffect );

                        $(document).trigger( 'yith_infs_added_elem', [elem, current_url] );

                    }, 1000 );
                }
            });
        };

        // recalculate element position
        var position_elem = function( last, columns, elem ) {

            var offset  = ( columns - last.prevUntil( '.last', opts.itemSelector ).length ),
                loop    = 0;

            elem.each(function () {

                var t = $(this);
                loop++;

                t.removeClass('first');
                t.removeClass('last');

                if ( ( ( loop - offset ) % columns ) === 0 ) {
                    t.addClass('first');
                }
                else if ( ( ( loop - ( offset - 1 ) ) % columns ) === 0 ) {
                    t.addClass('last');
                }
            });
        };


        // set event
        if( opts.eventType == 'scroll' ) {

            var loader_src = ( opts.customLoader == '' ) ? opts.presetLoader : opts.customLoader;
            loader = '<img src="' + loader_src + '">';

            // then add listener
            $( window ).on( 'scroll touchstart', function (){
                $(this).trigger('yith_infs_start');
            });

            $( window ).on( 'yith_infs_start', function (){

                var t       = $(this),
                    elem  = $( opts.itemSelector ).last();

                if( typeof elem == 'undefined' ) {
                    return;
                }

                if ( ! loading && ! finished && ( t.scrollTop() + t.height() ) >= ( elem.offset().top + elem.height() ) ) {
                    main_ajax();
                }
            });
        }
        else if( opts.eventType == 'button' ) {

            button = '<div class="yith-infs-button-wrapper"><button id="yith-infs-button" class="' + opts.buttonClass + '">' + opts.buttonLabel + '</button></div>';

            // add button if selector is valid
            $(opts.navSelector).last().after(button);

            // remove button if scroll is finished
            $( document ).on( 'yith-infs-scroll-finished', function() {
                $( '.yith-infs-button-wrapper' ).remove();
            });

            // button event
            $( '#yith-infs-button' ).off('click').on( 'click', function() {

                var t = $(this);

                if( ! loading ) {
                    if( block_loader ) {
                        t.block({
                            message   : null,
                            overlayCSS: {
                                background: '#fff url(' + block_loader + ') no-repeat center',
                                opacity   : 0.5,
                                cursor    : 'none'
                            }
                        });
                    }
                    main_ajax();
                }

                $( document ).on( 'yith_infs_adding_elem', function(){
                    if( block_loader )
                        t.unblock();
                });
            })

        }
        else if( opts.eventType == 'pagination' ) {

            $( document).off( 'click', opts.navSelector + ' a' )
                .on( 'click', opts.navSelector + ' a', function(e) {

                    e.preventDefault();
                    desturl = $(this).attr( 'href' );

                    if( block_loader ) {
                        $( opts.navSelector ).block({
                            message   : null,
                            overlayCSS: {
                                background: '#fff url(' + block_loader + ') no-repeat center',
                                opacity   : 0.5,
                                cursor    : 'none'
                            }
                        });
                    }

                    main_ajax();
                })
        }

        var last_scroll_position;

        $(window).on( 'scroll touchstart', function(ev){

            var w = $(window),
                scroll_position = w.scrollTop() + w.height(),
                key, new_key, last_elem_pos;

            if( typeof last_elem == 'undefined' || ! page_history.length ){
                return;
            }

            key = elm_history.indexOf( last_elem );
            last_elem_pos = last_elem.offset().top + last_elem.height();

            if( scroll_position < last_scroll_position ) {
                // set last elem if not zero
                if( scroll_position <= last_elem_pos ) {
                    new_key = key ? key - 1 : key;
                    last_elem = elm_history[ new_key ];
                    // change url
                    change_url( page_history[ key ][0], page_history[ key ][1] );
                }
            }
            else {
                if( scroll_position > ( last_elem_pos + 250 ) ) {
                    if( ( key + 1 ) < elm_history.length ) {
                        last_elem = elm_history[ key + 1 ];
                    }
                    // change url
                    change_url( page_history[ key + 1 ][0], page_history[ key + 1 ][1] );
                }
            }

            // set last scroll position
            last_scroll_position = scroll_position;
        });
    };
})( jQuery, window, document );