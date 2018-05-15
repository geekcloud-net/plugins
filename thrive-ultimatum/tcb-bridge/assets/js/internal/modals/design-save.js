/**
 * Created by Ovidiu on 7/21/2017.
 */
var _instance = null,
	_utils = require( '../_utils' );

module.exports = TVE.modal.base.extend( {
	after_initialize: function () {
		this.$el.addClass( 'medium' );
	},
	save: function () {
		var _name = this.$( 'input#tve-template-name' ).val(),
			self = this;

		if ( _name && _name.length > 0 ) {
			_utils.tpl_ajax( {
				custom: 'save',
				name: _name
			} ).done( function ( response ) {
				self.close();
				TVE.main.overlay( 'close' );
			} );
		} else {
			TVE.page_message( tve_ult_page_data.L.tpl_name_required, true, 5000 );
		}
	}
}, {
	/**
	 * "Singleton" implementation for modal instance
	 *
	 * @param el
	 */
	get_instance: function ( el ) {
		if ( ! _instance ) {
			_instance = new TVE_Ult_Int.DesignSave( {
				el: el
			} );
		}

		return _instance;
	}
} );