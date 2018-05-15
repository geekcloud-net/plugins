(function($) {

	// Off Canvas Open
	$('.wpbf-menu-toggle').click(function() {
		$('.wpbf-menu-off-canvas').addClass('active');
		$('body').addClass('active');
	});

	// Off Canvas Close
	$('.wpbf-menu-off-canvas .wpbf-close').click(function() {
		$('.wpbf-menu-off-canvas').removeClass('active');
		$('body').removeClass('active');
	});

	$(window).click(function() {
		if ( $('.wpbf-menu-off-canvas').hasClass('active') ) {
			$('.wpbf-menu-off-canvas').removeClass('active');
			$('body').removeClass('active');
		}
	});

	$('.wpbf-menu-off-canvas, .wpbf-menu-toggle').click(function(event){
		event.stopPropagation();
	});

	// add toggle arrow
	$('.wpbf-menu-off-canvas .menu-item-has-children').each(function() {
		$(this).append('<span class="wpbf-submenu-toggle"><i class="wpbff wpbff-arrow-down"></i></span>');
	});

	// Mobile Submenu
	$('.wpbf-menu-off-canvas .menu-item-has-children .wpbf-submenu-toggle').click(function(event) {

		event.preventDefault();

		if($(this).hasClass("active")) {
			$('i', this).removeClass('wpbff-arrow-up');
			$('i', this).addClass('wpbff-arrow-down');
			$(this).removeClass('active');
			$(this).siblings('.sub-menu').slideUp();
		} else {
			$('i', this).removeClass('wpbff-arrow-down');
			$('i', this).addClass('wpbff-arrow-up');
			$(this).addClass('active');
			$(this).siblings('.sub-menu').slideDown();
		}

	});


    // Hide open Mobile Menu on resize
	$(window).resize(function(){

		// vars
		var windowWidth = $(window).width();

		// resize fallback
		if(windowWidth < 1024) {
			if($('.wpbf-menu-off-canvas').hasClass('active')) {
				$('.wpbf-menu-off-canvas').removeClass('active');
				$('body').removeClass('active');
			}
		}

	});

})( jQuery );