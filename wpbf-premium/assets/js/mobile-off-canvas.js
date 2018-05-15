(function($) {

	// Off Canvas Open
	$('.wpbf-mobile-menu-toggle').click(function() {
		$('body').addClass('active-mobile');
		$(this).addClass('active');
		$('.wpbf-mobile-menu-container').addClass('active');
	});

	// Off Canvas Close
	$('.wpbf-mobile-menu-off-canvas .wpbf-close').click(function() {
		$('body').removeClass('active-mobile');
		$('.wpbf-mobile-menu-toggle').removeClass('active');
		$('.wpbf-mobile-menu-container').removeClass('active');
	});

	$(window).click(function() {
		if ( $('.wpbf-mobile-menu-container').hasClass('active') ) {
			$('body').removeClass('active-mobile');
			$('.wpbf-mobile-menu-toggle').removeClass('active');
			$('.wpbf-mobile-menu-container').removeClass('active');
		}
	});

	$('.wpbf-mobile-menu-container, .wpbf-mobile-menu-toggle').click(function(event){
		event.stopPropagation();
	});

	// get desktop breakpoint value from body class
	var DesktopBreakpointClass = $('body').attr("class").match(/wpbf-desktop-breakpoint-[\w-]*\b/);
	if( DesktopBreakpointClass !== null ) {
		var string = DesktopBreakpointClass.toString();
		var DesktopBreakpoint = string.match(/\d+/);
	} else {
		DesktopBreakpoint = '1024';
	}

    // Hide open Mobile Menu on resize
	$(window).resize(function(){

		// vars
		var windowHeight = $(window).height();
		var windowWidth = $(window).width();

		// resize fallback
		if(windowWidth > DesktopBreakpoint) {
			if($('.wpbf-mobile-menu-toggle').hasClass('active')) {
				$('body').removeClass('active-mobile');
				$('.wpbf-mobile-menu-toggle').removeClass('active');
				$('.wpbf-mobile-menu-container').removeClass('active');
			}
			if($('.wpbf-mobile-mega-menu').length) {
				$('.wpbf-mobile-mega-menu').removeClass('wpbf-mobile-mega-menu').addClass('wpbf-mega-menu');
			}
		} else {
			if($('.wpbf-mega-menu').length) {
				$('.wpbf-mega-menu').removeClass('wpbf-mega-menu').addClass('wpbf-mobile-mega-menu');
			}
		}

	});

	// add toggle arrow
	$('.wpbf-mobile-menu .menu-item-has-children').each(function() {
		$(this).append('<span class="wpbf-submenu-toggle"><i class="wpbff wpbff-arrow-down"></i></span>');
	});

	// // Mobile Submenu
	$('.wpbf-mobile-menu .menu-item-has-children .wpbf-submenu-toggle').click(function(event) {

		event.preventDefault();

		if($(this).hasClass("active")) {
			$('i', this).removeClass('wpbff-arrow-up').addClass('wpbff-arrow-down');
			$(this).removeClass('active').siblings('.sub-menu').slideUp();
		} else {
			$('i', this).removeClass('wpbff-arrow-down').addClass('wpbff-arrow-up');
			$(this).addClass('active').siblings('.sub-menu').slideDown();
		}

	});

})( jQuery );