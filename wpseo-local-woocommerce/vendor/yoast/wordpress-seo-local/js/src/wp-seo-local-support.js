/* global wpseoAdminL10n */
( function() {
	"use strict";

	jQuery( document ).ready( function() {

		if ( typeof( HS ) !== 'undefined' ) {
			jQuery( window ).on( 'YoastSEO:ContactSupport', function( e, data ) {
				if ( data.usedQueries != undefined){
					var identity = HS.beacon.get_helpscout_beacon_identity();
					identity[ 'User searched for' ] = usedQueriesWithHTML( data.usedQueries );
					HS.beacon.identify(identity);
				}
				jQuery( '#wpseo-contact-support-popup' ).hide();
				HS.beacon.open();
			});
		}

		/**
		 * Format the search queries done by the user in HTML.
		 *
		 * @param {array} usedQueries List of queries entered by the user.
		 * @returns {string} Table containing link to posts.
		 */
		function usedQueriesWithHTML( usedQueries ) {
			var output = '';

			if ( jQuery.isEmptyObject( usedQueries ) ) {
				return '<em>Search history is empty.</em>';
			}

			output += '<table><tr><th>Searched for</th><th>Opened article</th></tr>';

			jQuery.each( usedQueries, function( searchString, posts ) {
				output += "<tr><td>" + searchString + "</td>";
				output += getPostsHTML( posts );
				output += "</tr>";
			});

			output = output + "</table>";

			return output;
		}

		/**
		 * Format the posts looked at by the user in HTML.
		 *
		 * @param {array} posts List of posts opened by the user.
		 * @returns {string} Table containing links to posts.
		 */
		function getPostsHTML( posts ) {
			var output = '';
			var first = true;

			if ( jQuery.isEmptyObject( posts ) ) {
				return "<td><em>No articles were opened.</em></td>";
			}

			jQuery.each( posts, function( postId, post ) {
				if ( first === false ) {
					output += "<td></td>";
				}
				output += "<td><a href='" + post.link + "'>" + post.title + "</a></td>";
				first = false;
			});

			return output;
		}

		// Get the used search strings from the algoliaSearcher React component for the active tab and fire an event with this data.
		jQuery( ".contact-support" ).on( "click", function () {
			jQuery( window ).trigger( "YoastSEO:ContactSupport", { usedQueries: {} } );
		} );
	} );
})();
