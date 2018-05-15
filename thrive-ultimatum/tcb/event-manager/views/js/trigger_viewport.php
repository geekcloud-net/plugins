<script type="text/javascript">
	(function ( $ ) {
		var _DELTA = 80,
			$window = $( window ),
			trigger_elements = function ( elements ) {
				elements.each( function () {
					var elem = $( this );
					if ( elem.parents( '.tve_p_lb_content' ).length ) {
						elem.parents( '.tve_p_lb_content' ).on( 'tve.lightbox-open', function () {
							if ( ! elem.hasClass( 'tve-viewport-triggered' ) ) {
								elem.trigger( 'tve-viewport' ).addClass( 'tve-viewport-triggered' );
							}
						} );
						return;
					}
					if ( elem.offset().top + _DELTA < $window.height() + $window.scrollTop() && elem.offset().top + elem.outerHeight() > $window.scrollTop() + _DELTA ) {
						elem.trigger( 'tve-viewport' ).addClass( 'tve-viewport-triggered' );
					}
				} );
			},
			trigger_exit = function ( elements ) {
				elements.each( function () {
					var elem = $( this );
					if ( elem.offset().top > $window.height() + $window.scrollTop() || elem.offset().top + elem.outerHeight() < $window.scrollTop() ) {
						elem.trigger( 'tve-viewport-leave' ).removeClass( 'tve-viewport-triggered' );
					}
				} );
			};
		$( document ).ready( function () {
			var $to_test = $( '.tve_et_tve-viewport' );
			$window.scroll( function () {
				trigger_elements( $to_test.filter( ':not(.tve-viewport-triggered)' ) );
				trigger_exit( $to_test.filter( '.tve-viewport-triggered' ) );
			} );
			setTimeout( function () {
				trigger_elements( $to_test );
			}, 200 );
		} );
	})
	( jQuery );
</script>