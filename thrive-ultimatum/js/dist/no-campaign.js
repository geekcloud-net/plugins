/**
 * loaded
 */
var ThriveGlobal = ThriveGlobal || {$j: jQuery.noConflict()};

(function ( $ ) {
	$( function () {
		var ajax_data = {
			action: TVE_Ult_Data.conversion_events_action,
			post_id: TVE_Ult_Data.post_id
		};
		if ( window.TVE_Dash && ! TVE_Dash.ajax_sent ) {
			$( document ).on( 'tve-dash.load', function ( event ) {
				TVE_Dash.add_load_item( 'tu_conversion_events', ajax_data, $.noop );
			} );
		} else {
			//if not just handle it here
			$.ajax( {
				url: TVE_Ult_Data.ajaxurl,
				type: 'post',
				data: ajax_data
			} );
		}
	} );
})( ThriveGlobal.$j );