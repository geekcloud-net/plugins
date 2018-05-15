/**
 * Thrive Dashboard frontend script
 * @var {Object} tve_dash_front
 */
var TVE_Dash = TVE_Dash || {};
var ThriveGlobal = ThriveGlobal || {$j: jQuery.noConflict()};
(function ( $ ) {
	TVE_Dash.ajax_sent = false;
	var ajax_data = {},
		callbacks = {};

	/**
	 * add a load item - this will be sent on the initial ajax load
	 *
	 * @param {string} tag unique identifier for ajax action
	 * @param {object} data object
	 * @param {Function} [callback] optional callback function to handle the response
	 *
	 * @return {boolean} whether or not the data adding has been successful
	 */
	TVE_Dash.add_load_item = function ( tag, data, callback ) {

		if ( typeof callback !== 'function' ) {
			callback = $.noop;
		}

		/**
		 * In case the main request has been sent, we send the ajax request now.
		 */
		if ( TVE_Dash.ajax_sent ) {
			var local_data = {}, local_callbacks = {};
			local_data[tag] = data;
			local_callbacks[tag] = callback;
			this.send_ajax( local_data, local_callbacks );

			return true;
		}
		if ( ! data ) {
			console.error && console.error( 'missing ajax data' );
			return false;
		}
		if ( ajax_data[tag] ) {
			console.error && console.error( tag + ' ajax action already defined' );
		}
		ajax_data[tag] = data;
		callbacks[tag] = callback;

		return true;
	};

	/**
	 * Append external CSS stylesheets to the head
	 *
	 * @param {Object} list
	 */
	TVE_Dash.ajax_load_css = function ( list ) {
		$.each( list, function ( k, href ) {
			k += '-css';
			if ( ! $( 'link#' + k ).length ) {
				$( '<link rel="stylesheet" id="' + k + '" type="text/css" href="' + href + '"/>' ).appendTo( 'head' );
			}
		} );
	};

	/**
	 * Loads all javascripts from the list
	 *
	 * @param {Object} list
	 */
	TVE_Dash.ajax_load_js = function ( list ) {
		var body = document.body;
		$.each( list, function ( k, src ) {
			if ( k.indexOf( '_before' ) !== - 1 ) {
				return true;
			}
			var script = document.createElement( 'script' );
			if ( list[k + '_before'] ) {
				var l = $( '<script type="text/javascript">' + list[k + '_before'] + '</script>' );
				l.after( body.lastChild );
			}
			if ( k ) {
				script.id = k + '-script';
			}
			script.src = src;
			body.appendChild( script );
		} );
	};

	/**
	 * Prepares the data and sends the ajax request
	 *
	 * @param ajax_data
	 * @param callbacks
	 */
	TVE_Dash.send_ajax = function ( ajax_data, callbacks ) {
		$.ajax( {
			url: tve_dash_front.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			data: {
				action: 'tve_dash_front_ajax',
				tve_dash_data: ajax_data
			},
			dataType: 'json',
			type: 'post'
		} ).done( function ( response ) {
			if ( ! response || ! $.isPlainObject( response ) ) {
				return;
			}
			if ( response.__resources ) {
				if ( response.__resources.css ) {
					TVE_Dash.ajax_load_css( response.__resources.css );
				}
				if ( response.__resources.js ) {
					TVE_Dash.ajax_load_js( response.__resources.js );
				}
				delete response.__resources;
			}
			$.each( response, function ( tag, response ) {
				if ( typeof callbacks[tag] !== 'function' ) {
					return true;
				}
				callbacks[tag].call( null, response );
			} );
		} );
	};

	$( function () {
		setTimeout( function () {
			var evt = new $.Event( 'tve-dash.load' );
			$( document ).trigger( evt );
			/* if no ajax-data has been registered, do not make the ajax call */
			if ( $.isEmptyObject( ajax_data ) ) {
				return false;
			}
			/* We don't need to run this initial AJAX request if a bot is currently crawling the site - performance improvement */
			if ( ! tve_dash_front.force_ajax_send && tve_dash_front.is_crawler ) {
				return false;
			}
			TVE_Dash.send_ajax( ajax_data, callbacks );
			TVE_Dash.ajax_sent = true;
		} );
	} );
})( ThriveGlobal.$j );
