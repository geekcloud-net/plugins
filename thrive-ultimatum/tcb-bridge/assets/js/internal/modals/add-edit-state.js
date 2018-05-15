/**
 * Created by Ovidiu on 7/25/2017.
 */
var _instance = null,
	_utils = require( '../_utils' );

module.exports = TVE.modal.base.extend( {
	after_initialize: function ( args ) {
		this.$el.addClass( 'medium' );
	},
	before_open: function () {
		this.$( 'input#tve-ult-state-name' ).val( '' );
		if ( this.state_id && this.state_name ) {
			this.$( 'input#tve-ult-state-name' ).val( this.state_name );
		}
	},
	save: function () {
		var _name = this.$( 'input#tve-ult-state-name' ).val(),
			self = this;

		if ( _name && _name.length > 0 ) {
			TVE.main.overlay();

			TVE.Editor_Page.save( false, function () {
				if ( self.state_id && self.state_name ) {
					_utils.state_ajax( {
						custom_action: 'edit_name',
						post_title: _name,
						id: self.state_id
					} ).done( function ( response ) {
						_utils.stateResponse( response, _utils );
						self.close();
					} );
				} else {
					_utils.state_ajax( {
						custom_action: 'add',
						post_title: _name
					} ).done( function ( response ) {
						_utils.stateResponse( response, _utils );
						self.close();
					} );
				}
			} );
		} else {
			TVE.page_message( tve_ult_page_data.L.state_name_required, true, 5000 );
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
			_instance = new TVE_Ult_Int.AddEditState( {
				el: el
			} );
		}

		return _instance;
	}
} );