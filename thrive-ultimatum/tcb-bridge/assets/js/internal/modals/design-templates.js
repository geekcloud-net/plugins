/**
 * Created by Ovidiu on 7/21/2017.
 */
var _instance = null,
	_utils = require( '../_utils' );

module.exports = TVE.modal.base.extend( {
	saved_tpl_delete_confirmation: TVE.tpl( 'landing-pages/delete-confirmation' ),
	events: function () {
		return _.extend( {}, TVE.modal.base.prototype.events(), {
			'click .tcb-cancel-delete-template': 'no_delete_template',
			'click .tcb-apply-delete-template': 'yes_delete_template'
		} );
	},
	after_initialize: function () {
		this.$el.addClass( 'medium' );
		this.$tabs = this.$( '.tab-item' );
		this.$content = this.$( '.tve-tab-content' );
	},
	tab_click: function ( event ) {
		var tab = event.currentTarget.getAttribute( 'data-content' );

		this.$tabs.removeClass( 'active' );
		event.currentTarget.classList.add( 'active' );

		this.$content.removeClass( 'active' );
		this.$content.filter( '[data-content="' + tab + '"]' ).addClass( 'active' );

		if ( tab === 'saved' ) {
			this.get_saved();
		}
	},
	/**
	 * Returns the save templates preview
	 */
	get_saved: function () {
		var self = this;
		this.$( '.tve-saved-templates-list' ).html( tve_ult_page_data.L.fetching_saved_templates );

		_utils.tpl_ajax( {
			custom: 'get_saved'
		}, {
			dataType: 'html'
		}, true ).done( function ( response ) {
			TVE.main.overlay( 'close' );
			self.$( '.tve-saved-templates-list' ).html( response );
		} );
	},
	select_template: function ( event ) {
		this.$( '.template-wrapper.active' ).removeClass( 'active' );
		event.currentTarget.classList.toggle( 'active' );
	},

	/**
	 * Shows The Delete Confirmation View
	 *
	 * @param event
	 */
	delete_confirmation: function ( event ) {
		var $templateItem = jQuery( event.currentTarget ).closest( '.tve-template-item' );

		$templateItem.find( '.template-wrapper' ).hide();
		$templateItem.append( this.saved_tpl_delete_confirmation() );
	},

	/**
	 * Cancel A Delete Action And Returns to Default State
	 *
	 * @param event
	 */
	no_delete_template: function ( event ) {
		var $templateItem = jQuery( event.currentTarget ).closest( '.tve-template-item' );
		$templateItem.find( '.template-wrapper' ).show();
		$templateItem.find( '.tcb-delete-template-confirmation' ).remove();
	},

	/**
	 * Deletes A Saved Landing Page
	 *
	 * @param event
	 */
	yes_delete_template: function ( event ) {
		var $templateItem = jQuery( event.currentTarget ).closest( '.tve-template-item' );

		TVE.main.overlay();
		_utils.tpl_ajax( {
			custom: 'delete'
		}, {
			dataType: 'html'
		}, true ).done( function () {
			$templateItem.remove();
			TVE.main.overlay( 'close' );
		} );
	},
	save: function () {
		var $template = this.$( '.tve-template-item .active' ),
			self = this;
		if ( $template && $template.length ) {

			_utils.tpl_ajax( {
				custom: 'choose',
				tpl: $template.data( 'key' )
			} ).done( function ( response ) {
				_utils.insertResponse( response );

				TVE.main.overlay( 'close' );
				self.close();
			} );

		} else {
			TVE.page_message( TVE.t.SelectTemplate, true, 5000 );
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
			_instance = new TVE_Ult_Int.DesignTemplates( {
				el: el
			} );
		}

		return _instance;
	}
} );