<?php echo 'function(trigger,action,config){' ?>
if ( ! config || ! config.p || ! config.p.id ) {
	return false;
}
if ( config.s === 'wistia' ) {
	if ( ! window.tcb_w_videos || ! window.tcb_w_videos[config.p.id] ) {
		return false;
	}
	var _v = window.tcb_w_videos[config.p.id];
	if ( config.p.a ) {
		_v.play();
	}
	_v.popover.show();
	return false;
}
var $target = ThriveGlobal.$j( '#tcb-video-popup-' + config.p.id ),
	$overlay = ThriveGlobal.$j( '#tcb-video-popup-overlay' ),
	$body = ThriveGlobal.$j( 'body,html' ),
	$document = ThriveGlobal.$j( document ),
	width = $target.outerWidth(),
	height = $target.outerHeight();

if ( ! $overlay.length ) {
	$overlay = ThriveGlobal.$j( '<div id="tcb-video-popup-overlay" class="tcb-video-popup-overlay"><a href="javascript:void(0)" class="tcb-popup-close" style="display:none"></a></div>' ).css( {
		position: 'fixed',
		zIndex: 999992,
		top: 0,
		left: 0,
		width: '100%',
		height: '100%',
		opacity: '0.3',
		background: '#000',
		display: 'none'
	} ).appendTo( 'body' );
}
$overlay.fadeIn( 300 ).find( '.tcb-popup-close' ).hide();
$target.css( {
	display: 'none',
	visibility: '',
	zIndex: 999993,
	left: '50%',
	top: '50%',
	'margin-left': '-' + (width / 2) + 'px',
	'margin-top': '-' + (height / 2) + 'px',
	boxShadow: '0 10px 25px rgba(0,0,0,0.5)'
} );
var $ifr = $target.find( 'iframe' ),
	is_custom = false;
if ( $target.hasClass( 'tcb-custom-video' ) ) {
	is_custom = true;
	if ( ! $target.data( 'tcb-video-player' ) ) {
		var _id = $target.find( '.wp-video-shortcode' ).attr( 'id' );
		if ( typeof mejs !== 'undefined' && mejs && mejs.players && mejs.players[_id] ) {
			$target.data( 'tcb-video-player', mejs.players[_id] );
		} else {
			$target.data( 'tcb-video-player', $target.find( 'video' )[0] );
		}
	}
} else if ( $ifr.length && $ifr.attr( 'data-src' ) ) {
	$ifr.attr( 'src', $ifr.attr( 'data-src' ) );
}

$body.css( 'overflow', 'hidden' );
$target.show( 300, function () {
	$overlay.find( '.tcb-popup-close' ).css( {
		'margin-top': '-' + (height / 2) + 'px',
		'margin-left': (20 + width / 2) + 'px'
	} ).fadeIn( 200 );
	if ( is_custom && config.p.a ) {
		/* autoplay video */
		$target.data( 'tcb-video-player' ).play();
	}
} );

function close_it() {
	$overlay.find( '.tcb-popup-close' ).hide();
	$document.off( 'keyup.videoPopup' );
	$overlay.fadeOut( 300 );
	$target.hide( 300, function () {
		if ( ! $ifr.attr( 'data-src' ) ) {
			$ifr.attr( 'data-src', $ifr.attr( 'src' ) );
		}
		$ifr.removeAttr( 'src' );
		if ( is_custom ) {
			try {
				$target.data( 'tcb-video-player' ).pause();
				$target.data( 'tcb-video-player' ).setCurrentTime && $target.data( 'tcb-video-player' ).setCurrentTime( 0 );
			} catch ( e ) {
				console.log( 'Cannot pause video' );
			}
		}
	} );
	$body.css( 'overflow', '' );
}

$overlay.off( 'click.videoPopup' ).on( 'click.videoPopup', function () {
	close_it();
} );
// Return on ESC
ThriveGlobal.$j( document ).off( 'keyup.videoPopup' ).on( 'keyup.videoPopup', function ( e ) {
	if ( e.keyCode === 27 ) {   // ESC key
		close_it();
	}
} );

return false;
<?php echo '}' ?>
