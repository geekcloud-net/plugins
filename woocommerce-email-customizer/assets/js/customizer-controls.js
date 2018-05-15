( function( $ ) {
	'use strict';
	var api = wp.customize;

	$( document.body ).on( 'click', '.wc-email-customizer-send-email', function( e ) {
		e.preventDefault();

		// make sure settings are saved before sending email
		if ( ! api.state( 'saved' )() ) {
			alert( woocommerce_email_customizer_controls_local.saveFirst );

			return false;
		}

		var	button = this,
			$data = {
				action: 'woocommerce_email_customizer_send_email',
				ajaxSendEmailNonce : woocommerce_email_customizer_controls_local.ajaxSendEmailNonce,
				email_to: $( button ).prev( 'p' ).find( 'input[name="send_test_email_to"]' ).val()
			};

		$.post( woocommerce_email_customizer_controls_local.ajaxurl, $data, function( response ) {
			if ( 'success' === response ) {
				alert( woocommerce_email_customizer_controls_local.success );
			} else {
				alert( woocommerce_email_customizer_controls_local.error );
			}
		} );
	});

} )( jQuery );
