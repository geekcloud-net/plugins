
// init namespace
if ( typeof WPLA != 'object') var WPLA = {};


// revealing module pattern
WPLA.PriceMatcher = function () {
    
    // this will be a private property
    var post_id = 0;
    var currentTask = 0;
    var cancel_operation = 0;
    var self = {};
    
    // this will be a public method
    var init = function () {
        self = this; // assign reference to current object to "self"
    
        // jobs window "close" button
        jQuery('#matcher_window .btn_close').click( function(event) {
            tb_remove();                    
        }).hide();

        // jobs window "cancel" button
        jQuery('#matcher_window .btn_cancel').click( function(event) {
            jQuery('#wpla_jobs_message').html('Cancelling...');
            self.cancel_operation = true;
        });

    }

    var applyPrice = function () {

        var params = jQuery('#wpla_price_matcher_query_form').serialize();

        // var params = {
        //     action: 'wpla_show_product_matches',
        //     post_id: post_id,
        //     nonce: 'TODO'
        // };
        var jqxhr = jQuery.get( ajaxurl, params )
        .success( function( response ) { 

            // jQuery('#TB_ajaxContent').html( response );

            if ( response.success ) {
                
                // request was successful
                tb_remove();                    

                var logMsg = '<div id="message" class="updated" style="display:block !important;"><p>' + 
                'New price was applied successfully to product. ' +
                '</p></div>';
                jQuery('.wrap > h1').append( logMsg );

                // update status column in listings table
                console.log( "updating status column for listing ", response.listing_id ); 
                jQuery('mark#listing-status-'+response.listing_id+'').html( response.listing_status );
                if ( 'changed' == response.listing_status ) {
                    jQuery('mark#listing-status-'+response.listing_id+'').css( 'background-color', 'purple' );
                }

            } else {
                var logMsg = '<div id="message" class="updated" style="display:block !important;"><p>' + 
                'I could not find any matching items. Sorry.' +
                '</p></div>';
                jQuery('#ajax-response').append( logMsg );
    
                alert( "There was a problem applying the price for this product. The server responded:\n\n" + response ); 
                console.log( "response", response ); 

            }


        })
        .error( function(e,xhr,error) { 
            alert( "There was a problem applying the price for this product. The server responded:\n\n" + e.responseText ); 
            console.log( "error", xhr, error ); 
            console.log( e.responseText ); 
            console.log( "ajaxurl", ajaxurl ); 
            console.log( "params", params ); 
        });

    }

    // show jobs window
    var showWindow = function ( title ) {

        // show jobs window
        var tbHeight = tb_getPageSize()[1] - 160;
        var tbURL = "#TB_inline?height="+tbHeight+"&width=500&modal=true&inlineId=matcher_window_container"; 
        jQuery('#wpla_jobs_log').html('').css('height', tbHeight - 130 );
        jQuery('#wpla_jobs_title').html( title );
        // jQuery('#wpla_jobs_message').html('fetching list of tasks...');
        jQuery('#wpla_jobs_message').html( wpla_PriceMatcher_i18n.msg_loading_tasks );
        // jQuery('#wpla_jobs_footer_msg').html( "Please don't close this window until all tasks are completed." );
        jQuery('#wpla_jobs_footer_msg').html( wpla_PriceMatcher_i18n.footer_dont_close );

        // init progressbar
        jQuery("#wpla_progressbar").progressbar({ value: 0.01 });
        jQuery("#wpla_progressbar").children('span.caption').html('0%');

        // hide close button
        jQuery('#matcher_window .btn_close').hide();
        jQuery('#matcher_window .btn_cancel').show();

        // show window
        tb_show("Jobs", tbURL);             

    }


    return {
        // declare which properties and methods are supposed to be public
        init: init,
        applyPrice: applyPrice,
        showWindow: showWindow
    }
}();


