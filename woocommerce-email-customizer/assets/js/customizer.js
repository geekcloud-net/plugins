( function( $ ) {
	'use strict';
	
	wp.customize( 'woocommerce_email_background_color', function( value ) {
		value.bind( function( newval ) {
			$( 'body, body > div, body > #wrapper > table > tbody > tr > td' ).css( 'background-color', newval );
		} );
	} );

	wp.customize( 'woocommerce_email_body_background_color', function( value ) {
		value.bind( function( newval ) {
			$( '#template_body, #template_body td, #template_footer, #template_container' ).css( 'background-color', newval );
			$( '#template_container' ).css( 'border-color', newval );
		} );
	} );

	wp.customize( 'woocommerce_email_link_color', function( value ) {
		value.bind( function( newval ) {
			$( '#template_body div a, #template_body table td a' ).css( 'color', newval );
		} );
	} );

	wp.customize( 'woocommerce_email_header_background_color', function( value ) {
		value.bind( function( newval ) {
			$( '#template_header, #header_wrapper' ).css( 'background-color', newval );
		} );
	} );

	wp.customize( 'woocommerce_email_header_text_color', function( value ) {
		value.bind( function( newval ) {
			$( '#template_header h1' ).css( 'color', newval );
			$( '#template_header h1' ).css( 'text-shadow', '0 1px 0 ' + newval );
		} );
	} );

	wp.customize( 'woocommerce_email_header_font_size', function( value ) {
		value.bind( function( newval ) {
			$( '#template_header h1' ).css( 'font-size', newval + 'px' );
		} );
	} );

	wp.customize( 'woocommerce_email_body_text_color', function( value ) {
		value.bind( function( newval ) {
			$( '#template_body div, #template_body div p, #template_body h2, #template_body td, #template_body th, #template_body table h3' ).css( { color: newval, borderColor: newval } );
		} );
	} );

	wp.customize( 'woocommerce_email_body_font_size', function( value ) {
		value.bind( function( newval ) {
			$( '#template_body div, #template_body div p, #template_body h2, #template_body table td, #template_body table th, #template_body table h3' ).css( 'font-size', newval + 'px' );
		} );
	} );

	wp.customize( 'woocommerce_email_rounded_corners', function( value ) {
		value.bind( function( newval ) {
			$( '#template_container' ).attr( 'style', function( i, style ) {
				return style.replace( /border-radius[^;]+;?/g, '' );
			}).css( 'border-radius', newval + 'px' );
	
			$( '#template_header' ).attr( 'style', function( i, style ) {
				return style.replace( /border-radius[^;]+;?/g, '' );
			}).css( 'border-radius', newval + 'px ' + newval + 'px 0 0' );

			$( '#template_footer' ).attr( 'style', function( i, style ) {
				return style.replace( /border-radius[^;]+;?/g, '' );
			}).css( 'border-radius', '0 0 ' + newval + 'px ' + newval + 'px' );
		} );
	} );

	// Update shadow
	wp.customize( 'woocommerce_email_box_shadow_spread', function( value ) {
		value.bind( function( newval ) {
			$( '#template_container' ).attr( 'style', function( i, style ) {
				return style.replace( /box-shadow[^;]+;?/g, '' );
			}).css( 'box-shadow', '0 0 6px ' + newval + 'px rgba(0,0,0,0.6)' );
		} );
	} );

	wp.customize( 'woocommerce_email_font_family', function( value ) {
		value.bind( function( newval ) {
			$( '#template_container, #template_header h1, #template_body table div, #template_footer p, #body_content_inner table' ).css( 'font-family', newval );
		} );
	} );

	wp.customize( 'woocommerce_email_header_image', function( value ) {
		value.bind( function( newval ) {
			$( '#template_header_image' ).html( '<img src="' + newval + '" />' );
		} );
	} );

	wp.customize( 'woocommerce_email_width', function( value ) {
		value.bind( function( newval ) {
			$( '#template_container, #template_header, #template_body, #template_footer' ).css( 'width', newval );
		} );
	} );

	wp.customize( 'woocommerce_email_footer_text', function( value ) {
		value.bind( function( newval ) {
			$( '#template_footer p' ).html( newval );
		} );
	} );

	wp.customize( 'woocommerce_email_footer_font_size', function( value ) {
		value.bind( function( newval ) {
			$( '#template_footer p' ).css( 'font-size', newval + 'px' );
		} );
	} );

	wp.customize( 'woocommerce_email_footer_text_color', function( value ) {
		value.bind( function( newval ) {
			$( '#template_footer p' ).css( 'color', newval );
		} );
	} );

} )( jQuery );
