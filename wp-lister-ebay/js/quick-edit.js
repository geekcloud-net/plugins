jQuery(function( $ ) {
    $( '#the-list' ).on( 'click', '.editinline', function() {

        var post_id = $( this ).closest( 'tr' ).attr( 'id' );

        post_id = post_id.replace( 'post-', '' );

        var $inline_data = $( '#wplister_inline_' + post_id );

        var ebay_price      = $inline_data.find( '.ebay_start_price' ).text(),
            listing_id      = $inline_data.find( '.ebay_listing_id' ).text();


        $( 'input[name="_ebay_start_price"]', '.inline-edit-row' ).val( ebay_price );

        $( "#wplister-fields" ).show();

        if ( listing_id == 0 ) {
            $( "#wplister-fields" ).hide();
        }

    });
});
