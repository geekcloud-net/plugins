/**
 * admin.js
 *
 * @author Your Inspiration Themes
 * @package YITH Infinite Scrolling Premium
 * @version 1.0.0
 */

jQuery(document).ready( function($) {
    "use strict";

    var wrapper         = $( document ).find( '.infs-sections-group' ),
        container       = wrapper.find( '.infs-section' ),
        head            = container.find( '.section-head' ),
        remove          = head.find( '.remove'),
        eventType       = container.find( '.yith-infs-eventype-select'),
        presetLoader    = container.find( '.yith-infs-loader-select' ),
        block_loader    = ( typeof yith_infs_admin !== 'undefined' ) ? yith_infs_admin.block_loader : false,
        error_msg       = ( typeof yith_infs_admin !== 'undefined' ) ? yith_infs_admin.error_msg : false,
        del_msg         = ( typeof yith_infs_admin !== 'undefined' ) ? yith_infs_admin.del_msg : false,

        input_section   = $( '#yith-infs-add-section' ),
        add_section     = $( '#yith-infs-add-section-button' );



    add_section.on( 'click', function(e) {
        e.preventDefault();

        var t       = $(this),
            id      = t.data( 'section_id'),
            name    = t.data( 'section_name' ),
            title   = input_section.val();

        if( title == '' ) {
            if( error_msg ) {
                t.siblings( '.error-input-section' ).html( error_msg );
            }
        }
        else {
            $.post( yith_infs_admin.ajaxurl, { action: 'yith_infinite_scroll_section', section: title, id: id, name: name, context: 'admin' }, function( resp ) {

                // empty input
                input_section.val('');
                // remove error msg if any
                $( '.error-input-section' ).remove();

                wrapper.append( resp );

                var container       = $( '.infs-section.' + title ),
                    head            = container.find( '.section-head' ),
                    remove          = container.find( '.remove'),
                    eventType       = container.find( '.yith-infs-eventype-select'),
                    presetLoader    = container.find( '.yith-infs-loader-select' );

                if( typeof $.fn.select2 !== 'undefined' ) {                    
                    container.find('select').select2({
                        minimumResultsForSearch: Infinity
                    });
                }

                open_func( head );
                remove_func( remove );
                deps_func( eventType );
                preview_preset( presetLoader );
            })
        }
    });

    /****
     * Open function
     */
    var open_func = function( head ){

        head.on( 'click', function(){
            var t            = $(this);

            t.parents( '.infs-section' ).toggleClass( 'open' );
            t.next( '.section-body' ).slideToggle();
        });
    };

    /****
     * Remove function
     */
    var remove_func = function( remove ) {

        remove.on('click', function (e) {
            e.stopPropagation();

            var t           = $(this),
                section     = t.data('section'),
                container   = t.parents('.infs-section' ),
                confirm     = window.confirm( del_msg );

            if ( confirm == true ) {

                if (block_loader) {
                    container.block({
                        message   : null,
                        overlayCSS: {
                            background: '#fff url(' + block_loader + ') no-repeat center',
                            opacity   : 0.5,
                            cursor    : 'none'
                        }
                    });
                }

                $.post(yith_infs_admin.ajaxurl, {
                    action : 'yith_infinite_scroll_section_remove',
                    section: section,
                    context: 'admin'
                }, function (resp) {
                    container.remove();
                })
            }

        })
    };

    /****
     * Deps function option
     */
    var deps_func = function( eventType ) {

        eventType.each( function(){

            var t           = $(this),
                selected    = t.find( 'option:selected' );

            hide_show_func( t, selected.val() );

            t.on( 'change', function(){
                selected = t.find( 'option:selected' );
                hide_show_func( t, selected.val() );
            })
        });
    };

    var hide_show_func = function( t, val ) {

        var opt_btn     = t.parents('.infs-section').find( 'tr.deps-button' ),
            opt_scroll  = t.parents('.infs-section').find( 'tr.deps-scroll' );

        if( val == 'button' ) {
            opt_btn.show();
            opt_scroll.hide();
        }
        else if ( val == 'scroll' ) {
            opt_btn.hide();
            opt_scroll.show();
        }
        else {
            opt_btn.hide();
            opt_scroll.hide();
        }
    };

    /****
     * Preset preview
     */
    var preview_preset = function( presetLoader ) {

        presetLoader.each( function() {

            var t           = $(this),
                selected    = t.find( 'option:selected'),
                src         = selected.data( 'loader_url'),
                img         = t.closest( 'td' ).find( 'img' );

            if( src ) {
                img.attr( 'src', src );
            }

            t.on( 'change', function(){
                selected    = t.find( 'option:selected' );
                src         = selected.data( 'loader_url');

                img.attr( 'src', src );
            })
        })
    };


    /****
     * Upload Button
     */
    $( document ).on( 'click', '.upload_img_button', function(e) {
        e.preventDefault();

        var t = $(this),
            custom_uploader;

        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }
        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });

        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on( 'select' , function() {
            var attachment = custom_uploader.state().get( 'selection' ).first().toJSON(),
                input_text = t.prev( '.upload_img_url' );

            input_text.val( attachment.url );
        });

        //Open the uploader dialog
        custom_uploader.open();

    });


    // init
    open_func( head );
    remove_func( remove );
    deps_func( eventType );
    preview_preset( presetLoader );

    if( typeof $.fn.select2 !== 'undefined' ) {
        container.find('select').select2({
            minimumResultsForSearch: Infinity
        });
    }

    if( typeof CodeMirror != 'undefined' ) {
        var editor = CodeMirror.fromTextArea(document.getElementById("yit_infs_options_yith-infs-custom-js"), {
            lineNumbers: 1,
            showCursorWhenSelecting: true
        });
    }
});