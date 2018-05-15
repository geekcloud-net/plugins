jQuery(document).ready(function($) {

    //when the location-add button is clicked
	$('#location_setting_add').click(function () {

	    //get the current selected location
		var location_id = $('#location_setting_select').val();

        // if it was the placeholder, quit...
		if ( location_id == 0 ) {
			return;
		}

        // get the current location title
		var location_title = $( '#location_setting_select option:selected' ).text();
        var defaults = $( '#location_setting_select option:selected' ).data( 'defaults') ;

        //remove item from the options ( we don't need it there anymore )
        $( '#location_setting_select option:selected' ).remove();

        var checked = '';
        if ( yoast_wcseo_local_translations.has_categories == 0 ) {
            checked += ' checked="checked"';
        }

        //append a new row tot hte table with these location specs
		$('tbody#shipping_locations').append(
			'<tr class="location" data-id="' + location_id + '" data-title="' + encodeURI( location_title ) + '" data-defaults=\'' + JSON.stringify( defaults ) + '\'  >' +
			'<th scope="row" class="check-column"></th>' +
			'<td>' + location_title + '</td>' +
			'<td><label for="yoast_wcseo_local_pickup_location_allowed[' + location_id + ']" class="screen-reader-text">' + yoast_wcseo_local_translations.label_allow_location.replace( '%s', location_title ) + '</label><input type="checkbox"' + checked + ' name="yoast_wcseo_local_pickup_location_allowed[' + location_id + ']" /> <small>' + defaults.status + '</small></td>' +
            '<td><label for="yoast_wcseo_local_pickup_location_cost[' + location_id + ']" class="screen-reader-text">' + yoast_wcseo_local_translations.label_costs_location.replace( '%s', location_title ) + '</label><input type="text" name="yoast_wcseo_local_pickup_location_cost[' + location_id + ']" placeholder="' + yoast_wcseo_local_translations.placeholder_costs_location + '" class="input-text regular-input" > <small>' + defaults.price + '</small></td>' +
			'<td><input class="location_rule_remove" type="button" class="button" value="' + yoast_wcseo_local_translations.label_remove + '"></td>' +
			'</tr>'
		);

        //un-bind and re-bind click events, because our DOM has changed
        $('.location_rule_remove').unbind( 'click' );
        $('.location_rule_remove').on( 'click', yoast_remove_location );

	});

    // bind click event for the remove button
    $('.location_rule_remove').on( 'click', yoast_remove_location );

	// Show alert when you're activating the Local Store Pickup.
	$('#woocommerce_yoast_wcseo_local_pickup_enabled').on('click', function (e) {
		var $this = $(this);

		if( $this.is(':checked') ) {
			var answer = confirm( yoast_wcseo_local_translations.warning_enable_pickup );
			if( false == answer ) {
				e.preventDefault();
			}
		}
	});

});


function yoast_remove_location() {

    // find the row-element for this item
    var $row =  jQuery(this).closest( 'tr' );

    //get the specs form this row
    var id = $row.data( 'id' );
    var title = $row.data( 'title' );
    var defaults = $row.data( 'defaults' );

    //remove the row
    $row.remove();

    console.log( defaults);

    //but the specs back into our options/select
    jQuery( '#location_setting_select' ).append( '<option value="' + id + '" data-defaults=\'' + JSON.stringify( defaults ) + '\'>' + decodeURI( title ) + '</option>' );
}