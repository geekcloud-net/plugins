/**
 * Created by Ovidiu on 7/20/2017.
 */
var TVE = window.TVE || {},
	TVE_Ult_Ext = window.TVE_Ult_Ext = TVE_Ult_Ext || {};

(function ( $ ) {
	/**
	 * On TCB Main Ready
	 */
	$( window ).on( 'tcb_main_ready', function () {
		TVE.Views.Components.ultimatum_countdown = require( './ultimatum-countdown-component' );

		TVE_Ult_Ext.UltimatumCountdown = require( './modals/ultimatum-countdown' );
	} );
})( jQuery );
