<?php echo 'function(trigger,action,config){' ?>
var $target = jQuery( this ),
	offset = $target.offset(),
	target_w = $target.outerWidth(),
	target_h = $target.outerHeight(),
	$element = $target;
if ( config.id ) {
	$element = jQuery( "#tcb-image-zoom-" + config.id + " img" )
} else if ( $element.find( "img" ).length ) {
	$element = $element.find( "img" )
}
var image_src = $element.attr( "src" ),
	$lightbox = jQuery( '#tve_zoom_lightbox' ),
	$overlay = jQuery( '#tve_zoom_overlay' ),
	windowWidth = window.innerWidth,
	windowHeight = window.innerHeight,
	img_size = $element.data( "tve-zoom-clone" ),
	resizeScale = windowWidth < 600 ? 0.8 : 0.9;

if ( typeof img_size === 'undefined' ) {
	var $clone = $element.clone()
	                     .css( {
		                     position: "absolute",
		                     width: "",
		                     height: "",
		                     left: "-8000px",
		                     top: "-8000px"
	                     } ).removeAttr( "width height" );
	$clone.appendTo( "body" );
	$clone.on( 'load', function () {
		img_size = {
			"originalWidth": $clone.width(),
			"width": $clone.width(),
			"originalHeight": $clone.height(),
			"height": $clone.height()
		};


		if ( img_size.originalWidth > windowWidth * resizeScale || img_size.originalHeight > windowHeight * resizeScale ) {
			var widthPercent = img_size.originalWidth / windowWidth,
				heightPercent = img_size.originalHeight / windowHeight;

			img_size.width = ((widthPercent > heightPercent) ? (windowWidth * resizeScale) : (windowHeight * resizeScale * (img_size.originalWidth / img_size.originalHeight)));
			img_size.height = ((widthPercent > heightPercent) ? (windowWidth * resizeScale * (img_size.originalHeight / img_size.originalWidth)) : (windowHeight * resizeScale));
		}
		$element.data( "tve-zoom-clone", img_size );

		show_lightbox();
	} );
} else {
	show_lightbox();
}


function show_lightbox() {

	if ( $lightbox.length ) {
		$lightbox.show();
	} else {
		$lightbox = jQuery( "<div id='tve_zoom_lightbox'><div class='tve_close_lb thrv-icon-cross'></div><div id='tve_zoom_image_content'></div></div>" )
			.appendTo( 'body' );
		$overlay = jQuery( "<div id='tve_zoom_overlay'></div>" ).hide()
		                                                        .appendTo( 'body' );
		var tve_close_lb = function () {
			$lightbox.hide();
			$overlay.hide();
		};
		/* set listeners for closing the lightbox */
		jQuery( document ).on( "click", ".tve_close_lb", tve_close_lb );
		jQuery( document ).on( "click", "#tve_zoom_overlay", tve_close_lb );
		jQuery( document ).on( "keyup", function ( e ) {
			if ( e.keyCode == 27 ) {
				tve_close_lb();
			}
		} );

		jQuery( window ).resize( function () {
			var _sizes = $lightbox.data( "data-sizes" ),
				windowWidth = window.innerWidth,
				windowHeight = window.innerHeight,
				resizeScale = windowWidth < 600 ? 0.8 : 0.9;

			if ( _sizes.originalWidth > windowWidth * resizeScale || _sizes.originalHeight > windowHeight * resizeScale ) {
				var widthPercent = _sizes.originalWidth / windowWidth,
					heightPercent = _sizes.originalHeight / windowHeight;

				_sizes.width = ((widthPercent > heightPercent) ? (windowWidth * resizeScale) : (windowHeight * resizeScale * (_sizes.originalWidth / _sizes.originalHeight)));
				_sizes.height = ((widthPercent > heightPercent) ? (windowWidth * resizeScale * (_sizes.originalHeight / _sizes.originalWidth)) : (windowHeight * resizeScale));
			}

			$lightbox.width( _sizes.width );
			$lightbox.height( _sizes.height );

			$lightbox.css( "margin-left", - (_sizes.width + 30) / 2 );
			$lightbox.css( "margin-top", - (_sizes.height + 30) / 2 );
		} );
	}

	$lightbox.data( "data-sizes", img_size );

	jQuery( "#tve_zoom_image_content" ).html( "<img src='" + image_src + "'/>" );

	$lightbox.css( {
		left: offset.left + target_w / 2,
		top: offset.top + target_h / 2,
		marginLeft: 0,
		marginTop: 0,
		width: 0,
		height: 0,
		opacity: 0
	} ).animate( {
		opacity: 1,
		left: '50%',
		top: '50%',
		marginLeft: - (img_size.width + 30) / 2,
		marginTop: - (img_size.height + 30) / 2,
		width: img_size.width,
		height: img_size.height
	}, 150 );
	$overlay.fadeIn( 150 );
}

<?php echo 'return false;}';
