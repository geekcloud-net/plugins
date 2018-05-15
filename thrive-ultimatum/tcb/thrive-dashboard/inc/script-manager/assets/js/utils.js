(function ( $ ) {

	module.exports = {
		_t: function ( path ) {

			var html = $( 'script#' + path ).html() || '';
			return _.template( html );
		}
	}
})( jQuery );