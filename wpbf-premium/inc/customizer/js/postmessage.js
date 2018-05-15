( function( $ ) {

	/* Social */

	// Social Font Size
	wp.customize( 'social_font_size', function( value ) {
		value.bind( function( newval ) {
			$('.wpbf-social-icon').css('font-size', newval + 'px' );
		} );
	} );

	/* Text */

	// Line Height
	wp.customize( 'page_line_height', function( value ) {
		value.bind( function( newval ) {
			$('#content').css('line-height', newval );
		} );
	} );

	// Bold Color
	wp.customize( 'page_bold_color', function( value ) {
		value.bind( function( newval ) {
			$('b, strong').css('color', newval);
		} );
	} );

	/* Menu */

	// Font Size
	wp.customize( 'menu_font_size', function( value ) {
		value.bind( function( newval ) {
			$('.wpbf-menu a, .wpbf-mobile-menu a').css('font-size', newval );
		} );
	} );

	// Letter Spacing
	wp.customize( 'menu_letter_spacing', function( value ) {
		value.bind( function( newval ) {
			$('.wpbf-menu').css('letter-spacing', newval + 'px' );
		} );
	} );

	/* H1 */

	// Line Height
	wp.customize( 'page_h1_line_height', function( value ) {
		value.bind( function( newval ) {
			$('h1, h2, h3, h4, h5, h6').css('cssText', 'line-height: ' + newval + ' !important;' );
		} );
	} );

	// Letter Spacing
	wp.customize( 'page_h1_letter_spacing', function( value ) {
		value.bind( function( newval ) {
			$('h1, h2, h3, h4, h5, h6').css('letter-spacing', newval + 'px' );
		} );
	} );

	/* H2 */

	// Line Height
	wp.customize( 'page_h2_line_height', function( value ) {
		value.bind( function( newval ) {
			$('h2').css('cssText', 'line-height: ' + newval + ' !important;' );
		} );
	} );

	// Letter Spacing
	wp.customize( 'page_h2_letter_spacing', function( value ) {
		value.bind( function( newval ) {
			$('h2').css('letter-spacing', newval + 'px' );
		} );
	} );

	/* H3 */

	// Line Height
	wp.customize( 'page_h3_line_height', function( value ) {
		value.bind( function( newval ) {
			$('h3').css('cssText', 'line-height: ' + newval + ' !important;' );
		} );
	} );

	// Letter Spacing
	wp.customize( 'page_h3_letter_spacing', function( value ) {
		value.bind( function( newval ) {
			$('h3').css('letter-spacing', newval + 'px' );
		} );
	} );

	/* H4 */

	// Line Height
	wp.customize( 'page_h4_line_height', function( value ) {
		value.bind( function( newval ) {
			$('h4').css('cssText', 'line-height: ' + newval + ' !important;' );
		} );
	} );

	// Letter Spacing
	wp.customize( 'page_h4_letter_spacing', function( value ) {
		value.bind( function( newval ) {
			$('h4').css('letter-spacing', newval + 'px' );
		} );
	} );

	/* H5 */

	// Line Height
	wp.customize( 'page_h5_line_height', function( value ) {
		value.bind( function( newval ) {
			$('h5').css('cssText', 'line-height: ' + newval + ' !important;' );
		} );
	} );

	// Letter Spacing
	wp.customize( 'page_h5_letter_spacing', function( value ) {
		value.bind( function( newval ) {
			$('h5').css('letter-spacing', newval + 'px' );
		} );
	} );

	/* H6 */

	// Line Height
	wp.customize( 'page_h6_line_height', function( value ) {
		value.bind( function( newval ) {
			$('h6').css('cssText', 'line-height: ' + newval + ' !important;' );
		} );
	} );

	// Letter Spacing
	wp.customize( 'page_h6_letter_spacing', function( value ) {
		value.bind( function( newval ) {
			$('h6').css('letter-spacing', newval + 'px' );
		} );
	} );

	/* Navigation */

	// Stacked Advanced Background Color
	wp.customize( 'menu_stacked_bg_color', function( value ) {
		value.bind( function( newval ) {
			$('.wpbf-menu-stacked-advanced-wrapper').css('background-color', newval );
		} );
	} );

	// Stacked Advanced content
	wp.customize( 'menu_stacked_wysiwyg', function( value ) {
		value.bind( function( newval ) {
			$('.wpbf-menu-stacked-advanced-wrapper .wpbf-3-4').html( newval );
		} );
	} );

	// Transparent Header
	wp.customize( 'menu_transparent_background_color', function( value ) {
		value.bind( function( newval ) {
			$('.wpbf-transparent-header .wpbf-navigation, .wpbf-transparent-header .wpbf-mobile-nav-wrapper').css('background-color', newval );
		} );
	} );

	// Off Canvas Hamburger Icon Color
	wp.customize( 'menu_off_canvas_hamburger_color', function( value ) {
		value.bind( function( newval ) {
			$('.wpbf-menu-toggle').css('color', newval );
		} );
	} );

	// Off Canvas Submenu Arrow Color
	wp.customize( 'menu_off_canvas_submenu_arrow_color', function( value ) {
		value.bind( function( newval ) {
			$('.wpbf-menu-off-canvas .wpbf-submenu-toggle').css('color', newval );
		} );
	} );

} )( jQuery );