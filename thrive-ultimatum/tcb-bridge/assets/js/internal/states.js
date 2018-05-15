/**
 * Created by Ovidiu on 7/25/2017.
 */
var _utils = require( './_utils' );

module.exports = TVE.Views.Base.base_view.extend( {
	after_initialize: function () {
		this.dom = {
			btn: this.$( '.states-button-container' )
		};
	},
	expand: function () {
		this.$( '.design-states' ).show();
		this.dom.btn.hide();
	},
	collapse: function () {
		this.$( '.design-states' ).hide();
		this.dom.btn.show();
	},
	/**
	 * Add a new state
	 *
	 * @param e
	 */
	add: function ( e ) {
		this.collapse();

		var addEditStateModal = TVE_Ult_Int.AddEditState.get_instance( TVE.modal.get_element( 'add-edit-state' ) );
		addEditStateModal.state_name = null;
		addEditStateModal.state_id = null;
		addEditStateModal.open( {
			top: '20%'
		} );

		return false;
	},
	/**
	 * Edit a state name
	 *
	 * @param e
	 */
	edit: function ( e ) {
		this.collapse();

		var addEditStateModal = TVE_Ult_Int.AddEditState.get_instance( TVE.modal.get_element( 'add-edit-state' ) );
		addEditStateModal.state_name = e.currentTarget.getAttribute( 'data-state-name' );
		addEditStateModal.state_id = e.currentTarget.getAttribute( 'data-id' );
		addEditStateModal.open( {
			top: '20%'
		} );

		return false;
	},
	select: function ( e ) {
		this.collapse();

		TVE.main.overlay();
		TVE.Editor_Page.save( false, function () {
			_utils.state_ajax( {
				custom_action: 'display',
				id: e.currentTarget.getAttribute( 'data-id' )
			} ).done( function ( response ) {
				_utils.stateResponse( response, _utils )
			} );
		} );

		return false;
	},
	duplicate: function ( e, link ) {
		this.collapse();

		TVE.main.overlay();
		TVE.Editor_Page.save( false, function () {
			_utils.state_ajax( {
				custom_action: 'duplicate',
				id: link.getAttribute( 'data-id' )
			} ).done( function ( response ) {
				_utils.stateResponse( response, _utils )
			} );
		} );

		return false;
	},
	remove: function ( e, link ) {
		this.collapse();

		TVE.main.overlay();
		_utils.state_ajax( {
			custom_action: 'delete',
			id: link.getAttribute( 'data-id' )
		} ).done( function ( response ) {
			TVE.page_message( 'State Deleted' );
			_utils.stateResponse( response, _utils );
		} );

		return false;
	}
} );