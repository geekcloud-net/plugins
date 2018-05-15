var TVE_Ult = TVE_Ult || {};
var TVE_Ult_Data = TVE_Ult_Data || {};
var ThriveGlobal = ThriveGlobal || {$j: jQuery.noConflict()};

(function ( $ ) {
	/**
	 * array of callback functions to be applied when the designs are shown
	 * @type {Array}
	 */
	var on_show_callbacks = [],
		$body;
	/**
	 * load css files in the current page
	 * @param {Array} stylesheets
	 */
	TVE_Ult.add_page_css = function ( stylesheets ) {
		$.each( stylesheets, function ( _id, href ) {
			_id += '-css';
			if ( ! $( '#' + _id ).length ) {
				$( '<link rel="stylesheet" id="' + _id + '" type="text/css" href="' + href + '"/>' ).appendTo( 'head' );
			}
		} );
	};

	/**
	 * we need to add the scripts using this method, to make sure the onload callback is fired properly
	 * @param src
	 * @param onload
	 */
	TVE_Ult.add_head_script = function ( src, id, onload ) {
		var script = document.createElement( 'script' ),
			head = $( 'head' )[0];

		script.async = true;
		if ( typeof onload === 'function' ) {
			script.onload = script.onreadystatechange = onload;
		}
		if ( typeof id !== 'undefined' ) {
			script.id = id;
		}

		script.src = src;

		head.insertBefore( script, head.firstChild );
	};

	TVE_Ult.add_page_js = function ( links, onLoad ) {
		if ( typeof onLoad !== 'function' ) {
			onLoad = function () {
			};
		}
		var to_load = 0,
			check_loaded_counter = 0;
		$.each( links, function ( _id, href ) {
			if ( _id === 'tve_frontend' && typeof TCB_Front !== 'undefined' ) {
				return true;
			}
			if ( _id === 'tve_leads_frontend' && typeof TL_Front !== 'undefined' && TL_Front.add_page_css ) {
				return true;
			}
			_id += '-js';
			if ( href && $( '#' + _id ).length === 0 ) {
				to_load ++;
				/* facebook needs to be inserted with a custom fragment appended - jQuery.getScript does not allow that */
				if ( href.indexOf( 'connect.facebook.net' ) !== - 1 ) {
					TVE_Ult.add_head_script( href, _id, function () {
						to_load --;
					} );
					return true;
				}
				$.getScript( href, function () {
					to_load --;
				} ).fail( function () {
					console.log( 'Failed to load: ' + href );
					console.log( arguments );
				} );
			}
		} );
		function check_loaded() {
			check_loaded_counter ++;
			if ( to_load === 0 ) {
				onLoad();
				return;
			}
			if ( check_loaded_counter === 100 ) {
				// failsafe - might be that an error occurred at script loading
				return;
			}
			setTimeout( check_loaded, 50 );
		}

		check_loaded();
	};

	$( function () {

		var resources_loaded = false;
		$body = $( 'body' );

		/**
		 * ajax-load the campaign designs
		 */
		function insert_response( r ) {
			if ( ! r ) {
				return;
			}
			/**
			 * append the html to the page
			 */

			$.each( r, function ( campaign_id, response ) {

				campaign_id = parseInt( campaign_id );

				/**
				 * return if not number
				 */
				if ( isNaN( campaign_id ) ) {
					return;
				}

				/**
				 * loop through each campaign object
				 */
				$.each( response.html, function ( _id, _html ) {

					var $html = $( _html ),
						$timers = $html.find( '.thrv_countdown_timer' );

					$.each( response.timer_components, function ( key, v ) {
						$timers.attr( 'data-' + key, v );
					} );

					if ( _id === 'widget' ) {
						// append the widget to the placeholder
						var $container = $( '#tve-ult-widget-container' ),
							widget_id = $container.data( 'widget-id' );
						if ( ! $container.length ) { // simply do not show the widget

							return true;
						}
						/**
						 * set the widget id because of the replaceWith
						 * and to be used later
						 * @see TVE_Ult.hide_design
						 */
						$html.attr( 'data-widget-id', widget_id );

						$container.find( '#tve-ult-widget-placeholder' ).replaceWith( $html );
						$container.children().unwrap();

						return true;

					} else if ( _id === 'header-bar' ) {
						on_show_callbacks.push( TVE_Ult.top_ribbon_show );
					} else if ( _id.indexOf( 'shortcode' ) !== - 1 ) {
						var $placeholder = $( '.tu-' + _id );

						if ( $placeholder.length ) {
							$placeholder.replaceWith( $html );
						}

						return true;
					}

					var $target = $( '#tve-ult-' + _id );

					if ( $target.length ) {
						$target.html( $html );
					} else {
						$body.append( $html );
					}

				} );

				/**
				 * append resources
				 */
				if ( ! response.resources ) {
					return;
				}

				if ( response.resources.css ) {
					TVE_Ult.add_page_css( response.resources.css );
				}

				if ( response.resources.fonts ) {
					TVE_Ult.add_page_css( response.resources.fonts );
				}

				/**
				 * localize javascript
				 */
				if ( response.resources.localize ) {
					$.each( response.resources.localize, function ( o, d ) {
						if ( typeof window[o] === 'undefined' ) {
							window[o] = d;
						}
					} );
				}
			} );

			/**
			 * body end
			 */
			if ( r.body_end ) {
				/**
				 * filter the end-of-body contents to remove any (possible) existing wistia embed divs
				 */
				var $body_end = $( r.body_end );
				$body_end.find( '.tve_wistia_popover' ).each( function () {
					if ( $( '#' + this.id ).length ) {
						this.parentNode.removeChild( this );
					}
				} );
				$body.append( $body_end );
			}

			if ( r.resources && r.resources.js ) {
				TVE_Ult.add_page_js( r.resources.js, function () {
					resources_loaded = true;
				} );
			}

			if ( r.resources && r.resources.css ) {
				TVE_Ult.add_page_css( r.resources.css );
			}

			/**
			 * rebind the TCB event listeners
			 */
			function dom_ready() {
				if ( ! resources_loaded ) {
					setTimeout( dom_ready, 50 );
					return;
				}
				TCB_Front.event_triggers( ThriveGlobal.$j( 'body' ) );
				TCB_Front.onDOMReady();
				var $all = $( '.tve-ult-design' ).css( 'display', '' );
				setTimeout( function () {
					$all.addClass( 'tvu-triggered' );
					setTimeout( function () {
						$.each( on_show_callbacks, function ( i, fn ) {
							fn.call( null, $all );
						} );
					}, 200 );
				}, 200 );

				$body.on( 'click', '.tve-ult-bar-close', TVE_Ult.hide_design );

				/** event thrown by TCB */
				$( ".tve-ult-design" ).on( 'tve.countdown-finished', TVE_Ult.hide_design );
			}

			setTimeout( dom_ready, 50 );
		}

		var ajax_data = {
			action: TVE_Ult_Data.ajax_load_action,
			campaign_ids: TVE_Ult_Data.campaign_ids,
			matched_display_settings: TVE_Ult_Data.matched_display_settings,
			post_id: TVE_Ult_Data.post_id,
			is_singular: TVE_Ult_Data.is_singular,
			tu_em: TVE_Ult_Data.tu_em,
			shortcode_campaign_ids: TVE_Ult_Data.shortcode_campaign_ids
		};

		//if global unique ajax is ready
		if ( window.TVE_Dash && ! TVE_Dash.ajax_sent ) {
			$( document ).on( 'tve-dash.load', function ( event ) {
				TVE_Dash.add_load_item( 'tu_lazy_load', ajax_data, insert_response );
			} );
		} else {
			//if not just handle it here
			$.ajax( {
				url: TVE_Ult_Data.ajaxurl,
				type: 'post',
				data: $.extend( ajax_data, {hard_ajax: true} )
			} ).done( insert_response );
		}
	} );

	/**
	 * pushes the body contents down with an amount equal to the height of the ribbon
	 */
	TVE_Ult.top_ribbon_show = function ( $all_designs ) {
		var $top_ribbon = $all_designs.filter( '.tvu-header' );
		if ( ! $top_ribbon.length ) {
			return;
		}
		var _height = $top_ribbon.outerHeight( true );
		$body.data( 'tvu-original-margin', $body.css( 'margin-top' ) ).animate( {
			marginTop: '+' + _height + 'px'
		}, 300 );
	};

	/**
	 * called when the user clicks the "close" icon on a ribbon
	 *
	 * @param {jQuery.Event} e
	 */
	TVE_Ult.hide_design = function ( e ) {
		var $design = $( e.target ).parents( '.tve-ult-design' );
		$design.removeClass( 'tvu-triggered' );
		if ( $design.hasClass( 'tvu-header' ) ) {
			$body.animate( {
				// it fixes a conflict with leads scroll mat
				// marginTop: $body.data( 'tvu-original-margin' )
				marginTop: 0
			}, 300 );
		}
		if ( $design.is( '.tve-ult-widget' ) ) {
			$design.parents( "#" + $design.data( 'widget-id' ) ).slideUp();
		}
	};

})( ThriveGlobal.$j );

