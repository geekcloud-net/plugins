(function($) {

	// Off Canvas Open
	$('.wpbf-menu-toggle').click(function() {
		$('.wpbf-menu-full-screen').addClass('active');
		$('.wpbf-menu-full-screen').fadeIn(150);
	});

	$('.wpbf-menu-full-screen .wpbf-close').click(function() {
		$('.wpbf-menu-full-screen').removeClass('active');
		$('.wpbf-menu-full-screen').fadeOut(150);
	});

})( jQuery );