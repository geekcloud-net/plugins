/**
 * Created by Ovidiu on 7/21/2017.
 */
var _instance = null,
	_utils = require( '../_utils' );

module.exports = TVE.modal.base.extend( {
	after_initialize: function () {
		this.$el.addClass( 'medium' );
	},
	reset: function () {
		var self = this;
		_utils.tpl_ajax( {
			custom: 'reset'
		} ).done( function ( response ) {
			_utils.insertResponse( response );
			self.close();
			TVE.main.overlay( 'close' );
		} );
	}
}, {
	/**
	 * "Singleton" implementation for modal instance
	 *
	 * @param el
	 */
	get_instance: function ( el ) {
		if ( ! _instance ) {
			_instance = new TVE_Ult_Int.DesignReset( {
				el: el
			} );
		}

		return _instance;
	}
} );