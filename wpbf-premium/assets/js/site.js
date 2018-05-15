(function($) {

	// Mega Menu | prevent click on headlines
	 $('.wpbf-mega-menu > .sub-menu > .menu-item a[href="#"]').click(function(event) {
	 	event.preventDefault();
	 });

	/* Sub Menu Animations */

	var duration = $(".wpbf-navigation").data('sub-menu-animation-duration');

	// Down Animation
	$('.wpbf-sub-menu-animation-down > .menu-item-has-children').hover(function() {
		$('.sub-menu', this).first().stop().css({display:'block'}).animate({marginTop:'0', opacity:'1'}, duration);
	},
	function(){
		$('.sub-menu', this).first().stop().animate({opacity:'0', marginTop:'-10px'}, duration, function() {
			$(this).css({display:'none'});
		});
	});

	// Up Animation
	$('.wpbf-sub-menu-animation-up > .menu-item-has-children').hover(function() {
		$('.sub-menu', this).first().stop().css({display:'block'}).animate({marginTop:'0', opacity:'1'}, duration);
	},
	function(){
		$('.sub-menu', this).first().stop().animate({opacity:'0', marginTop:'10px'}, duration, function() {
			$(this).css({display:'none'});
		});
	});

	// Zoom In Animation
	$('.wpbf-sub-menu-animation-zoom-in > .menu-item-has-children').hover(function() {
		$('.sub-menu', this).first().stop(true).css({display:'block'}).transition({scale:'1', opacity:'1'}, duration);
	},
	function(){
		$('.sub-menu', this).first().stop(true).transition({scale:'.95', opacity:'0'}, duration).fadeOut(5);
	});

	// Zoom Out Animation
	$('.wpbf-sub-menu-animation-zoom-out > .menu-item-has-children').hover(function() {
		$('.sub-menu', this).first().stop(true).css({display:'block'}).transition({scale:'1', opacity:'1'}, duration);
	},
	function(){
		$('.sub-menu', this).first().stop(true).transition({scale:'1.05', opacity:'0'}, duration).fadeOut(5);
	});

	// WooCommerce Menu Item
	$(document).on({
		mouseenter: function () {
			$('.wpbf-menu-item-cart .woo-sub-menu').stop().fadeIn(duration);
		},
		mouseleave: function () {
			$('.wpbf-menu-item-cart .woo-sub-menu').stop().fadeOut(duration);
		}
	}, ".wpbf-menu-item-cart.menu-item-has-children");

})( jQuery );