/* global adminpage */
// Interim login dialog
var APP_auth_show;
(function($){
	var wrap, next;

	function show() {
		var parent = $('#wp-auth-check'),
			form = $('#wp-auth-check-form'),
			noframe = wrap.find('.wp-auth-fallback-expired'),
			frame, loaded = false;

		if ( form.length ) {
			closeModal();
			// Add unload confirmation to counter (frame-busting) JS redirects
			$(window).on( 'beforeunload.wp-auth-check', function(e) {
				e.originalEvent.returnValue = wc_pos_params.beforeunload;
			});

			frame = $('<iframe id="wp-auth-check-frame" frameborder="0">').attr( 'title', noframe.text() );
			frame.on( 'load', function() {
				var height, body;

				loaded = true;
				// Remove the spinner to avoid unnecessary CPU/GPU usage.
				form.removeClass( 'loading' );

				try {
					body = $(this).contents().find('body');
					height = body.height();
				} catch(e) {
					wrap.addClass('fallback');
					parent.css( 'max-height', '' );
					form.remove();
					noframe.focus();
					return;
				}

				if ( height ) {
					if ( body && body.hasClass('interim-login-success') ){
						checkUserCanWiev();						
						hide();
					}
					else{
						parent.css( 'max-height', height + 40 + 'px' );
					}
				} else if ( ! body || ! body.length ) {
					// Catch "silent" iframe origin exceptions in WebKit after another page is loaded in the iframe
					wrap.addClass('fallback');
					parent.css( 'max-height', '' );
					form.remove();
					noframe.focus();
				}
			}).attr( 'src', form.data('src') );

			form.append( frame );
		}

		$( 'body' ).addClass( 'modal-open' );
		wrap.removeClass('hidden');

		if ( frame ) {
			frame.focus();
			// WebKit doesn't throw an error if the iframe fails to load because of "X-Frame-Options: DENY" header.
			// Wait for 10 sec. and switch to the fallback text.
			setTimeout( function() {
				if ( ! loaded ) {
					wrap.addClass('fallback');
					form.remove();
					noframe.focus();
				}
			}, 10000 );
		} else {
			noframe.focus();
		}
	}

	function checkUserCanWiev() {
		var register_id = wc_pos_register_id;		
		$('#wpwrap').block({
                            message: null,
                            overlayCSS: {
                              background: '#fff',
                              opacity: 0.6
                            }
                        });
		
		$.ajax({
            type: 'POST',
            url: wc_pos_params.ajax_url,
            data: {
                action      : 'wc_pos_can_user_open_register',
                register_id : register_id,
            },
            success: function(response) {
        		if ( ! response ) {
					return;
				}
				if( response.result === 'locked' ){
					var source        = $('#tmpl-locked-register').html();
					var template      = Handlebars.compile(source);
					var html          = template({message : response.message, avatar_url: response.avatar});
					$('#modal-locked-register .md-content').html(html);
					openModal('modal-locked-register');
					APP.showNotice(pos_i18n[37]);
				}else if( response.result === 'success' ){
        			APP.showNotice(pos_i18n[37]);
					APP.voidRegister(false);
					APP.sync_status = {
			            'product'  : false,
			            'coupon'   : false,
			            'customer' : false
			        };
					APP.sync_data(true);
					if( response.user_id != current_cashier_id ){
						var source        = $('#tmpl-current-cashier-name').html();
    					var template      = Handlebars.compile(source);
    					var html          = template({display_name : response.name, avatar_url: response.avatar});
    					$('.pos_register_user_panel').replaceWith(html);
					}
				}else{
					openModal('modal-permission-denied');
				}

            },
            
        })
        .always( function(response) {
        	$('#wpwrap').unblock();
        }).fail( function( jqXHR, textStatus, error ) {
        	$('#wpwrap').unblock();
		});
	}

	function hide() {
		$(window).off( 'beforeunload.wp-auth-check' );

		// When on the Edit Post screen, speed up heartbeat after the user logs in to quickly refresh nonces
		if ( typeof adminpage !== 'undefined' && ( adminpage === 'post-php' || adminpage === 'post-new-php' ) &&
			typeof wp !== 'undefined' && wp.heartbeat ) {

			$(document).off( 'heartbeat-tick.wp-auth-check' );
			wp.heartbeat.connectNow();
		}

		wrap.fadeOut( 200, function() {
			wrap.addClass('hidden').css('display', '');
			$('#wp-auth-check-frame').remove();
			$( 'body' ).removeClass( 'modal-open' );
		});
	}

	function schedule() {
		var interval = parseInt( wc_pos_params.auth_check_interval, 10 ) || 180; // in seconds, default 3 min.
		next = ( new Date() ).getTime() + ( interval * 1000 );
	}

	$( document ).on( 'heartbeat-tick.wp-auth-check', function( e, data ) {
		if ( 'wp-auth-check' in data ) {
			schedule();
			if ( ! data['wp-auth-check'] && wrap.hasClass('hidden') ) {
				show();
			} else if ( data['wp-auth-check'] && ! wrap.hasClass('hidden') ) {
				hide();
			}
		}
	}).on( 'heartbeat-send.wp-auth-check', function( e, data ) {
		if ( ( new Date() ).getTime() > next ) {
			data['wp-auth-check'] = true;
		}
	}).ready( function() {
		schedule();
		wrap = $('#wp-auth-check-wrap');
		wrap.find('.wp-auth-check-close').on( 'click', function() {
			hide();
		});
		APP_auth_show = show;
	});


}(jQuery));
