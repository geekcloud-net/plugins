jQuery(window).load(function(){
	( function($) {

		var ups_enabled = '#woocommerce_ups_enabled';

		// All UPS API Settings
		var ups_settings_api = [
			'#woocommerce_ups_user_id',
			'#woocommerce_ups_password',
			'#woocommerce_ups_access_key',
			'#woocommerce_ups_shipper_number'
		];

		$( ups_enabled ).on( 'change', function() {
			if ( $(this).is(':checked') ) {
				$(this).closest('table').siblings().show();

				// Hide other settings until API details entered
				if ( $( '#woocommerce_ups_user_id' ).filter(function() { return $(this).val(); }).length <= 0 || $( '#woocommerce_ups_password' ).filter(function() { return $(this).val(); }).length <= 0 || $( '#woocommerce_ups_access_key' ).filter(function() { return $(this).val(); }).length <= 0 || $( '#woocommerce_ups_shipper_number' ).filter(function() { return $(this).val(); }).length <= 0 ) {
					$( '#woocommerce_ups_shipper_number' ).closest('table').nextAll(':not(p.submit)').hide();
				} else {
					$("select#woocommerce_ups_packing_method").change();
				}
			} else {
				$(this).closest('table').nextAll(':not(p.submit)').hide();
			}
		});

		$( ups_settings_api.join(',') ).on( 'change input', function() {
			if ( $( '#woocommerce_ups_user_id' ).filter(function() { return $(this).val(); }).length <= 0 || $( '#woocommerce_ups_password' ).filter(function() { return $(this).val(); }).length <= 0 || $( '#woocommerce_ups_access_key' ).filter(function() { return $(this).val(); }).length <= 0 || $( '#woocommerce_ups_shipper_number' ).filter(function() { return $(this).val(); }).length <= 0 ) {
					$( '#woocommerce_ups_shipper_number' ).closest('table').nextAll(':not(p.submit)').hide();
			} else {
				$( '#woocommerce_ups_shipper_number' ).closest('table').nextAll(':not(p.submit)').show();
				$("select#woocommerce_ups_packing_method").change();
			}
		});

		// When packing method changes, show/hide packaging options
		$("select#woocommerce_ups_packing_method").on( 'change',function(){
			if ($(this).val() === 'per_item') {
				$( '#woocommerce_ups_ups_packaging, .ups_boxes' ).parents('tr').hide();
			}
			if ($(this).val() === 'box_packing') {
				$( '#woocommerce_ups_ups_packaging, .ups_boxes' ).parents('tr').show();
			}
		});

		// Init
		$( ups_enabled ).change();

	})(jQuery);

	jQuery('.ups_boxes .insert').click( function() {
		var dim_unit    = 'metric' === jQuery( '#woocommerce_ups_units' ).val() ? 'CM' : 'IN';
		var weight_unit = 'metric' === jQuery( '#woocommerce_ups_units' ).val() ? 'KGS' : 'LBS';

		var $tbody = jQuery('.ups_boxes').find('tbody');
		var size = $tbody.find('tr').size();
		var code = '<tr class="new">\
				<td class="check-column"><input type="checkbox" /></td>\
				<td><input type="text" size="5" name="boxes_outer_length[' + size + ']" />' + dim_unit + '</td>\
				<td><input type="text" size="5" name="boxes_outer_width[' + size + ']" />' + dim_unit + '</td>\
				<td><input type="text" size="5" name="boxes_outer_height[' + size + ']" />' + dim_unit + '</td>\
				<td><input type="text" size="5" name="boxes_inner_length[' + size + ']" />' + dim_unit + '</td>\
				<td><input type="text" size="5" name="boxes_inner_width[' + size + ']" />' + dim_unit + '</td>\
				<td><input type="text" size="5" name="boxes_inner_height[' + size + ']" />' + dim_unit + '</td>\
				<td><input type="text" size="5" name="boxes_box_weight[' + size + ']" />' + weight_unit + '</td>\
				<td><input type="text" size="5" name="boxes_max_weight[' + size + ']" />' + weight_unit + '</td>\
			</tr>';

		$tbody.append( code );

		return false;
	} );

	jQuery('.ups_boxes .remove').click(function() {
		var $tbody = jQuery('.ups_boxes').find('tbody');

		$tbody.find('.check-column input:checked').each(function() {
			jQuery(this).closest('tr').hide().find('input').val('');
		});

		return false;
	});

	// Ordering
	jQuery('.ups_services tbody').sortable({
		items:'tr',
		cursor:'move',
		axis:'y',
		handle: '.sort',
		scrollSensitivity:40,
		forcePlaceholderSize: true,
		helper: 'clone',
		opacity: 0.65,
		placeholder: 'wc-metabox-sortable-placeholder',
		start:function(event,ui){
			ui.item.css('baclbsround-color','#f6f6f6');
		},
		stop:function(event,ui){
			ui.item.removeAttr('style');
			ups_services_row_indexes();
		}
	});

	function ups_services_row_indexes() {
		jQuery('.ups_services tbody tr').each(function(index, el){
			jQuery('input.order', el).val( parseInt( jQuery(el).index('.ups_services tr') ) );
		});
	}

});
