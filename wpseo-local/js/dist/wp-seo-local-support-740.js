(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

/* global wpseoAdminL10n */
(function () {
	"use strict";

	jQuery(document).ready(function () {

		if (typeof HS !== 'undefined') {
			jQuery(window).on('YoastSEO:ContactSupport', function (e, data) {
				if (data.usedQueries != undefined) {
					var identity = HS.beacon.get_helpscout_beacon_identity();
					identity['User searched for'] = usedQueriesWithHTML(data.usedQueries);
					HS.beacon.identify(identity);
				}
				jQuery('#wpseo-contact-support-popup').hide();
				HS.beacon.open();
			});
		}

		/**
   * Format the search queries done by the user in HTML.
   *
   * @param {array} usedQueries List of queries entered by the user.
   * @returns {string} Table containing link to posts.
   */
		function usedQueriesWithHTML(usedQueries) {
			var output = '';

			if (jQuery.isEmptyObject(usedQueries)) {
				return '<em>Search history is empty.</em>';
			}

			output += '<table><tr><th>Searched for</th><th>Opened article</th></tr>';

			jQuery.each(usedQueries, function (searchString, posts) {
				output += "<tr><td>" + searchString + "</td>";
				output += getPostsHTML(posts);
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
		function getPostsHTML(posts) {
			var output = '';
			var first = true;

			if (jQuery.isEmptyObject(posts)) {
				return "<td><em>No articles were opened.</em></td>";
			}

			jQuery.each(posts, function (postId, post) {
				if (first === false) {
					output += "<td></td>";
				}
				output += "<td><a href='" + post.link + "'>" + post.title + "</a></td>";
				first = false;
			});

			return output;
		}

		// Get the used search strings from the algoliaSearcher React component for the active tab and fire an event with this data.
		jQuery(".contact-support").on("click", function () {
			jQuery(window).trigger("YoastSEO:ContactSupport", { usedQueries: {} });
		});
	});
})();

},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJqcy9zcmMvd3Atc2VvLWxvY2FsLXN1cHBvcnQuanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7OztBQ0FBO0FBQ0EsQ0FBRSxZQUFXO0FBQ1o7O0FBRUEsUUFBUSxRQUFSLEVBQW1CLEtBQW5CLENBQTBCLFlBQVc7O0FBRXBDLE1BQUssT0FBUSxFQUFSLEtBQWlCLFdBQXRCLEVBQW9DO0FBQ25DLFVBQVEsTUFBUixFQUFpQixFQUFqQixDQUFxQix5QkFBckIsRUFBZ0QsVUFBVSxDQUFWLEVBQWEsSUFBYixFQUFvQjtBQUNuRSxRQUFLLEtBQUssV0FBTCxJQUFvQixTQUF6QixFQUFtQztBQUNsQyxTQUFJLFdBQVcsR0FBRyxNQUFILENBQVUsNkJBQVYsRUFBZjtBQUNBLGNBQVUsbUJBQVYsSUFBa0Msb0JBQXFCLEtBQUssV0FBMUIsQ0FBbEM7QUFDQSxRQUFHLE1BQUgsQ0FBVSxRQUFWLENBQW1CLFFBQW5CO0FBQ0E7QUFDRCxXQUFRLDhCQUFSLEVBQXlDLElBQXpDO0FBQ0EsT0FBRyxNQUFILENBQVUsSUFBVjtBQUNBLElBUkQ7QUFTQTs7QUFFRDs7Ozs7O0FBTUEsV0FBUyxtQkFBVCxDQUE4QixXQUE5QixFQUE0QztBQUMzQyxPQUFJLFNBQVMsRUFBYjs7QUFFQSxPQUFLLE9BQU8sYUFBUCxDQUFzQixXQUF0QixDQUFMLEVBQTJDO0FBQzFDLFdBQU8sbUNBQVA7QUFDQTs7QUFFRCxhQUFVLDhEQUFWOztBQUVBLFVBQU8sSUFBUCxDQUFhLFdBQWIsRUFBMEIsVUFBVSxZQUFWLEVBQXdCLEtBQXhCLEVBQWdDO0FBQ3pELGNBQVUsYUFBYSxZQUFiLEdBQTRCLE9BQXRDO0FBQ0EsY0FBVSxhQUFjLEtBQWQsQ0FBVjtBQUNBLGNBQVUsT0FBVjtBQUNBLElBSkQ7O0FBTUEsWUFBUyxTQUFTLFVBQWxCOztBQUVBLFVBQU8sTUFBUDtBQUNBOztBQUVEOzs7Ozs7QUFNQSxXQUFTLFlBQVQsQ0FBdUIsS0FBdkIsRUFBK0I7QUFDOUIsT0FBSSxTQUFTLEVBQWI7QUFDQSxPQUFJLFFBQVEsSUFBWjs7QUFFQSxPQUFLLE9BQU8sYUFBUCxDQUFzQixLQUF0QixDQUFMLEVBQXFDO0FBQ3BDLFdBQU8sNENBQVA7QUFDQTs7QUFFRCxVQUFPLElBQVAsQ0FBYSxLQUFiLEVBQW9CLFVBQVUsTUFBVixFQUFrQixJQUFsQixFQUF5QjtBQUM1QyxRQUFLLFVBQVUsS0FBZixFQUF1QjtBQUN0QixlQUFVLFdBQVY7QUFDQTtBQUNELGNBQVUsa0JBQWtCLEtBQUssSUFBdkIsR0FBOEIsSUFBOUIsR0FBcUMsS0FBSyxLQUExQyxHQUFrRCxXQUE1RDtBQUNBLFlBQVEsS0FBUjtBQUNBLElBTkQ7O0FBUUEsVUFBTyxNQUFQO0FBQ0E7O0FBRUQ7QUFDQSxTQUFRLGtCQUFSLEVBQTZCLEVBQTdCLENBQWlDLE9BQWpDLEVBQTBDLFlBQVk7QUFDckQsVUFBUSxNQUFSLEVBQWlCLE9BQWpCLENBQTBCLHlCQUExQixFQUFxRCxFQUFFLGFBQWEsRUFBZixFQUFyRDtBQUNBLEdBRkQ7QUFHQSxFQXJFRDtBQXNFQSxDQXpFRCIsImZpbGUiOiJnZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uIGUodCxuLHIpe2Z1bmN0aW9uIHMobyx1KXtpZighbltvXSl7aWYoIXRbb10pe3ZhciBhPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7aWYoIXUmJmEpcmV0dXJuIGEobywhMCk7aWYoaSlyZXR1cm4gaShvLCEwKTt2YXIgZj1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK28rXCInXCIpO3Rocm93IGYuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixmfXZhciBsPW5bb109e2V4cG9ydHM6e319O3Rbb11bMF0uY2FsbChsLmV4cG9ydHMsZnVuY3Rpb24oZSl7dmFyIG49dFtvXVsxXVtlXTtyZXR1cm4gcyhuP246ZSl9LGwsbC5leHBvcnRzLGUsdCxuLHIpfXJldHVybiBuW29dLmV4cG9ydHN9dmFyIGk9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtmb3IodmFyIG89MDtvPHIubGVuZ3RoO28rKylzKHJbb10pO3JldHVybiBzfSkiLCIvKiBnbG9iYWwgd3BzZW9BZG1pbkwxMG4gKi9cbiggZnVuY3Rpb24oKSB7XG5cdFwidXNlIHN0cmljdFwiO1xuXG5cdGpRdWVyeSggZG9jdW1lbnQgKS5yZWFkeSggZnVuY3Rpb24oKSB7XG5cblx0XHRpZiAoIHR5cGVvZiggSFMgKSAhPT0gJ3VuZGVmaW5lZCcgKSB7XG5cdFx0XHRqUXVlcnkoIHdpbmRvdyApLm9uKCAnWW9hc3RTRU86Q29udGFjdFN1cHBvcnQnLCBmdW5jdGlvbiggZSwgZGF0YSApIHtcblx0XHRcdFx0aWYgKCBkYXRhLnVzZWRRdWVyaWVzICE9IHVuZGVmaW5lZCl7XG5cdFx0XHRcdFx0dmFyIGlkZW50aXR5ID0gSFMuYmVhY29uLmdldF9oZWxwc2NvdXRfYmVhY29uX2lkZW50aXR5KCk7XG5cdFx0XHRcdFx0aWRlbnRpdHlbICdVc2VyIHNlYXJjaGVkIGZvcicgXSA9IHVzZWRRdWVyaWVzV2l0aEhUTUwoIGRhdGEudXNlZFF1ZXJpZXMgKTtcblx0XHRcdFx0XHRIUy5iZWFjb24uaWRlbnRpZnkoaWRlbnRpdHkpO1xuXHRcdFx0XHR9XG5cdFx0XHRcdGpRdWVyeSggJyN3cHNlby1jb250YWN0LXN1cHBvcnQtcG9wdXAnICkuaGlkZSgpO1xuXHRcdFx0XHRIUy5iZWFjb24ub3BlbigpO1xuXHRcdFx0fSk7XG5cdFx0fVxuXG5cdFx0LyoqXG5cdFx0ICogRm9ybWF0IHRoZSBzZWFyY2ggcXVlcmllcyBkb25lIGJ5IHRoZSB1c2VyIGluIEhUTUwuXG5cdFx0ICpcblx0XHQgKiBAcGFyYW0ge2FycmF5fSB1c2VkUXVlcmllcyBMaXN0IG9mIHF1ZXJpZXMgZW50ZXJlZCBieSB0aGUgdXNlci5cblx0XHQgKiBAcmV0dXJucyB7c3RyaW5nfSBUYWJsZSBjb250YWluaW5nIGxpbmsgdG8gcG9zdHMuXG5cdFx0ICovXG5cdFx0ZnVuY3Rpb24gdXNlZFF1ZXJpZXNXaXRoSFRNTCggdXNlZFF1ZXJpZXMgKSB7XG5cdFx0XHR2YXIgb3V0cHV0ID0gJyc7XG5cblx0XHRcdGlmICggalF1ZXJ5LmlzRW1wdHlPYmplY3QoIHVzZWRRdWVyaWVzICkgKSB7XG5cdFx0XHRcdHJldHVybiAnPGVtPlNlYXJjaCBoaXN0b3J5IGlzIGVtcHR5LjwvZW0+Jztcblx0XHRcdH1cblxuXHRcdFx0b3V0cHV0ICs9ICc8dGFibGU+PHRyPjx0aD5TZWFyY2hlZCBmb3I8L3RoPjx0aD5PcGVuZWQgYXJ0aWNsZTwvdGg+PC90cj4nO1xuXG5cdFx0XHRqUXVlcnkuZWFjaCggdXNlZFF1ZXJpZXMsIGZ1bmN0aW9uKCBzZWFyY2hTdHJpbmcsIHBvc3RzICkge1xuXHRcdFx0XHRvdXRwdXQgKz0gXCI8dHI+PHRkPlwiICsgc2VhcmNoU3RyaW5nICsgXCI8L3RkPlwiO1xuXHRcdFx0XHRvdXRwdXQgKz0gZ2V0UG9zdHNIVE1MKCBwb3N0cyApO1xuXHRcdFx0XHRvdXRwdXQgKz0gXCI8L3RyPlwiO1xuXHRcdFx0fSk7XG5cblx0XHRcdG91dHB1dCA9IG91dHB1dCArIFwiPC90YWJsZT5cIjtcblxuXHRcdFx0cmV0dXJuIG91dHB1dDtcblx0XHR9XG5cblx0XHQvKipcblx0XHQgKiBGb3JtYXQgdGhlIHBvc3RzIGxvb2tlZCBhdCBieSB0aGUgdXNlciBpbiBIVE1MLlxuXHRcdCAqXG5cdFx0ICogQHBhcmFtIHthcnJheX0gcG9zdHMgTGlzdCBvZiBwb3N0cyBvcGVuZWQgYnkgdGhlIHVzZXIuXG5cdFx0ICogQHJldHVybnMge3N0cmluZ30gVGFibGUgY29udGFpbmluZyBsaW5rcyB0byBwb3N0cy5cblx0XHQgKi9cblx0XHRmdW5jdGlvbiBnZXRQb3N0c0hUTUwoIHBvc3RzICkge1xuXHRcdFx0dmFyIG91dHB1dCA9ICcnO1xuXHRcdFx0dmFyIGZpcnN0ID0gdHJ1ZTtcblxuXHRcdFx0aWYgKCBqUXVlcnkuaXNFbXB0eU9iamVjdCggcG9zdHMgKSApIHtcblx0XHRcdFx0cmV0dXJuIFwiPHRkPjxlbT5ObyBhcnRpY2xlcyB3ZXJlIG9wZW5lZC48L2VtPjwvdGQ+XCI7XG5cdFx0XHR9XG5cblx0XHRcdGpRdWVyeS5lYWNoKCBwb3N0cywgZnVuY3Rpb24oIHBvc3RJZCwgcG9zdCApIHtcblx0XHRcdFx0aWYgKCBmaXJzdCA9PT0gZmFsc2UgKSB7XG5cdFx0XHRcdFx0b3V0cHV0ICs9IFwiPHRkPjwvdGQ+XCI7XG5cdFx0XHRcdH1cblx0XHRcdFx0b3V0cHV0ICs9IFwiPHRkPjxhIGhyZWY9J1wiICsgcG9zdC5saW5rICsgXCInPlwiICsgcG9zdC50aXRsZSArIFwiPC9hPjwvdGQ+XCI7XG5cdFx0XHRcdGZpcnN0ID0gZmFsc2U7XG5cdFx0XHR9KTtcblxuXHRcdFx0cmV0dXJuIG91dHB1dDtcblx0XHR9XG5cblx0XHQvLyBHZXQgdGhlIHVzZWQgc2VhcmNoIHN0cmluZ3MgZnJvbSB0aGUgYWxnb2xpYVNlYXJjaGVyIFJlYWN0IGNvbXBvbmVudCBmb3IgdGhlIGFjdGl2ZSB0YWIgYW5kIGZpcmUgYW4gZXZlbnQgd2l0aCB0aGlzIGRhdGEuXG5cdFx0alF1ZXJ5KCBcIi5jb250YWN0LXN1cHBvcnRcIiApLm9uKCBcImNsaWNrXCIsIGZ1bmN0aW9uICgpIHtcblx0XHRcdGpRdWVyeSggd2luZG93ICkudHJpZ2dlciggXCJZb2FzdFNFTzpDb250YWN0U3VwcG9ydFwiLCB7IHVzZWRRdWVyaWVzOiB7fSB9ICk7XG5cdFx0fSApO1xuXHR9ICk7XG59KSgpO1xuIl19
