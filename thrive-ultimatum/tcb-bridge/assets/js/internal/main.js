/**
 * Created by Ovidiu on 7/21/2017.
 */
var TVE_Ult_Int = window.TVE_Ult_Int = TVE_Ult_Int || {};

(function ( $ ) {

	/**
	 * On TCB Main Ready
	 */
	$( window ).on( 'tcb_main_ready', function () {

		TVE_Ult_Int.DesignTemplates = require( './modals/design-templates' );
		TVE_Ult_Int.DesignReset = require( './modals/design-reset' );
		TVE_Ult_Int.DesignSave = require( './modals/design-save' );
		TVE_Ult_Int.AddEditState = require( './modals/add-edit-state' );

		var _states = require( './states' );
		TVE_Ult_Int.States = new _states( {
			el: jQuery( '#tu-form-states' )[0]
		} );

		TVE.add_filter( 'editor_loaded_callback', function () {
			TVE.main.sidebar_settings.tve_ult_choose_template = function () {
				var designTemplatesModal = TVE_Ult_Int.DesignTemplates.get_instance( TVE.modal.get_element( 'design-templates' ) );
				designTemplatesModal.open( {
					top: '5%',
					css: {
						width: '80%',
						left: '10%'
					},
					dismissible: ( tve_ult_page_data.has_content )
				} );
			};

			TVE.main.sidebar_settings.tve_ult_save_template = function () {
				var designSaveModal = TVE_Ult_Int.DesignSave.get_instance( TVE.modal.get_element( 'design-save' ) );
				designSaveModal.open( {
					top: '20%'
				} );
			};

			TVE.main.sidebar_settings.tve_ult_reset_template = function () {
				var designResetsModal = TVE_Ult_Int.DesignReset.get_instance( TVE.modal.get_element( 'design-reset' ) );
				designResetsModal.open( {
					top: '20%'
				} );
			};

			/**
			 * Open Template Chooser if the variation is empty
			 */
			if ( ! tve_ult_page_data.has_content ) {
				TVE.main.sidebar_settings.tve_ult_choose_template();
			}

			/**
			 * Backwards Compatibility:
			 * Adds thrv-inline-text class to countdown elements that doesn't have it on caption class
			 */
			TVE.inner_$( '.thrv_countdown_timer .t-caption:not(.thrv-inline-text)' ).each( function () {
				jQuery( this ).addClass( 'thrv-inline-text' );
			} );
		} );

	} );
})( jQuery );
