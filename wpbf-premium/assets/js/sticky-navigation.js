(function($) {

	/* Sticky Menu
	========================================================================== */

	// Sticky Vars
	var sticky = $('.wpbf-navigation').data('sticky');

	var delay = $(".wpbf-navigation").data('sticky-delay');
	var animation = $(".wpbf-navigation").data('sticky-animation');
	var duration = $(".wpbf-navigation").data('sticky-animation-duration');

	var offset_top = $('.wpbf-navigation').offset().top;
	
	var fired = 0;
	
	var distance = parseInt(offset_top) + parseInt(delay);
	
	$(window).resize(function(){	
		var navHeight = $('.wpbf-navigation').outerHeight();
	});

	var menu_logo = $('.wpbf-logo img').attr('src');
	var menu_active_logo = $('.wpbf-logo').data("menu-active-logo");

	// Sticky Menu Delay/Animation
	var stickyNavigation = function(){

	var scroll_top = $(window).scrollTop();

	var navHeight = $('.wpbf-navigation').outerHeight();

		if (scroll_top > distance && fired == '0') {

			$('.wpbf-navigation').addClass('wpbf-navigation-active');

			if(animation == 'slide') {

				$('.wpbf-navigation').css({ 'position':'fixed', 'left':'0', 'zIndex':'666', 'top': -navHeight }).animate({'top':0}, duration);

			} else if(animation == 'fade') {

				$('.wpbf-navigation').css({ 'display':'none', 'position':'fixed', 'top':'0', 'left':'0', 'zIndex':'666' }).fadeIn(duration);

			} else {

				$('.wpbf-navigation').css({ 'position': 'fixed', 'top':'0', 'left':'0', 'zIndex':'666' });
			}

			if (!$('body').hasClass('wpbf-transparent-header')) {

				$('.wpbf-page-header').css('marginTop', navHeight);

			}

			if ($('.wpbf-logo').data('menu-active-logo')) {
				$('.wpbf-logo img').attr('src', menu_active_logo);
				$('.wpbf-mobile-logo img').attr('src', menu_active_logo);
			}

			fired = '1';

		} else if (scroll_top < distance && fired == '1') {

			$('.wpbf-navigation').removeClass('wpbf-navigation-active');

			if (!$('body').hasClass('wpbf-transparent-header')) {

				$('.wpbf-navigation').css({ 'position':'', 'top':'', 'left':'', 'zIndex':'' });
				$('.wpbf-page-header').css('marginTop', '');

			} else {

				$('.wpbf-navigation').css({ 'position':'absolute', 'top':'', 'left':'', 'zIndex':'' });

			}

			if ($('.wpbf-logo').data('menu-active-logo')) {
				$('.wpbf-logo img').attr('src', menu_logo);
				$('.wpbf-mobile-logo img').attr('src', menu_logo);
			}

			fired = '0';

		}

	};
		
	if(sticky) {

	    $(window).scroll(function() {

			stickyNavigation();

	    });

	}

})( jQuery );