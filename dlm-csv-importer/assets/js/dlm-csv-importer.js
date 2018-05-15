jQuery( document.body ).ready( function ( $ ) {

    $( '.dlm-ci-select-map-to' ).change( function () {
        if ( 'custom_meta' === $( this ).find( 'option:selected' ).val() ) {

            var header = $( this ).closest( 'tr' ).data( 'header' );


            // set select width 50%
            $( this ).addClass( 'dlm-ci-select-map-to-meta-active' );

            // add custom meta text input
            $( this ).closest( 'td' ).append( $( '<input>' ).attr( 'type', 'text' ).attr( 'name', 'meta_keys[' + header + ']' ).addClass( 'dlm-ci-text-meta' ).attr( 'placeholder', 'meta_key' ) );

        } else {
            $( this ).removeClass( 'dlm-ci-select-map-to-meta-active' );
            $( this ).closest( 'td' ).find( '.dlm-ci-text-meta' ).remove();
        }
    } );

} );