/**
 * Created by Ovidiu on 7/21/2017.
 */
(function ( $ ) {
	module.exports = {
		tpl_ajax: function ( data, ajax_param, no_loader ) {
			var params = {
				type: 'post',
				dataType: 'json',
				url: tve_ult_page_data.ajaxurl
			};
			if ( typeof no_loader === 'undefined' || ! no_loader ) {
				TVE.main.overlay();
			}
			data.action = tve_ult_page_data.tpl_action;
			data.design_id = data.design_id || tve_ult_page_data.design_id;
			data.post_id = tve_ult_page_data.post_id;
			data.security = tve_ult_page_data.security;
			params.data = data;

			if ( ajax_param ) {
				for ( var k in ajax_param ) {
					params[k] = ajax_param[k];
				}
			}

			return jQuery.ajax( params, data );
		},
		/**
		 * actions related to a state
		 * @param data
		 * @param ajax_param
		 * @returns {*}
		 */
		state_ajax: function ( data, ajax_param ) {
			var params = {
				type: 'post',
				dataType: 'json',
				url: tve_ult_page_data.ajaxurl
			};

			data.action = tve_ult_page_data.state_action;
			data.design_id = data.design_id || tve_ult_page_data.design_id;
			data.post_id = tve_ult_page_data.post_id;
			data.security = tve_ult_page_data.security;

			params.data = data;

			if ( ajax_param ) {
				for ( var k in ajax_param ) {
					params[k] = ajax_param[k];
				}
			}

			return jQuery.ajax( params, data );
		},
		stateResponse: function ( response, self ) {
			if ( ! response || response.success === false ) {
				var _msg = 'Something went wrong';
				if ( response && response.success === false ) {
					_msg += ': ' + response.message;
				}
				TVE.page_message( _msg, true );
				TVE.main.overlay( 'close' );
				return;
			}

			/**
			 * Insert response only if main page content is returned.
			 */
			if ( response.main_page_content ) {
				self.insertResponse( response );
			}

			jQuery( '.design-states' ).replaceWith( response.state_bar );

			TVE.main.overlay( 'close' );
		},
		insertResponse: function ( response ) {
			if ( ! response || response.success === false ) {
				TVE.page_message( 'Something went wrong' + (
						response && response.success === false ? ': ' + response.message : ''
					), true );
				setTimeout( function () {
					TVE.main.overlay( 'close' );
				}, 1 );
				return;
			}

			/**
			 * callback to be applied when all css files are loaded and available
			 */
			function on_resources_loaded() {
				/**
				 * javascript page data
				 */
				tve_ult_page_data = jQuery.extend( tve_ult_page_data, response.tve_ult_page_data, true );

				/**
				 * javascript params that need updating
				 */
				TVE.CONST = jQuery.extend( TVE.CONST, response.tve_path_params, true );

				TVE.inner_$( '#tve-ult-editor-replace' ).replaceWith( response.main_page_content );

				TVE.Editor_Page.initEditorActions();
			}

			/**
			 * browser-compliant way of accessing stylesheet rules
			 */
			var sheet, cssRules, _link = document.createElement( 'link' );
			if ( 'sheet' in _link ) {
				sheet = 'sheet';
				cssRules = 'cssRules';
			} else {
				sheet = 'styleSheet';
				cssRules = 'rules';
			}

			/** custom CSS */
			TVE.inner_$( '.tve_custom_style,.tve_user_custom_style' ).remove();
			TVE.CSS_Rule_Cache.clear();
			if ( response.custom_css && response.custom_css.length ) {
				TVE.inner_$( 'head' ).append( response.custom_css );
			}

			/**
			 * checks if all the added CSS <link> elements are available (finished loading and applied)
			 *
			 * @param {jQuery} $jq_links collection of added <link> nodes
			 * @param {Function} complete_callback
			 */
			function check_loaded( $jq_links, complete_callback ) {
				var all_loaded = true;
				window.tvu_loaded_count = window.tvu_loaded_count || 1;
				window.tvu_loaded_count ++;
				$jq_links.each( function () {

					/** firefox throws an Error when testing this condition and the css is not loaded yet */
					try {
						if ( ! this[sheet] || ! this[sheet][cssRules] || ! this[sheet][cssRules].length ) {
							all_loaded = false;
							return false; // break the loop
						}
					} catch ( e ) {
						all_loaded = false;
						return false;
					}
				} );
				if ( all_loaded || window.tvu_loaded_count > 40 ) {
					complete_callback();
				} else {
					setTimeout( function () {
						check_loaded( $jq_links, complete_callback );
					}, 500 );
				}
			}

			var found = false,
				$css_list = jQuery();
			TVE.inner_$.each( response.css, function ( _id, href ) {
				if ( ! TVE.inner_$( 'link#' + _id + '-css' ).length ) {
					found = true;
					var $link = TVE.inner_$( '<link />', {
						rel: 'stylesheet',
						type: 'text/css',
						id: _id + '-css',
						href: href
					} ).appendTo( 'head' );
					/* for some reason, <link>s from google fonts always have empty cssRules fields - we cannot be sure when those are loaded using the check_loaded function */
					if ( href.indexOf( 'fonts.googleapis' ) === - 1 ) {
						$css_list = $css_list.add( $link );
					}
				}
			} );

			if ( found ) {
				check_loaded( $css_list, on_resources_loaded );
			} else {
				on_resources_loaded()
			}
		}
	}
})( jQuery );