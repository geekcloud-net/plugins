
var custom_dimensions = {

	/**
	 * Initialize custom_dimensions
	 */
	init : function() {
		this.toggle_add_button();
		jQuery("#monsterinsights_add_row").click( this.add_row );

		jQuery(document).on("click", "[id^=monsterinsights_remove_]", this.remove_row);
		jQuery(document).on('change', "select[name^='monsterinsights_settings[custom_dimensions]']", this.row_change_options);
		jQuery(document).on('change', "input[name^='monsterinsights_settings[custom_dimensions]']", this.row_change_id);
		this.row_init_options();
	},

	toggle_add_button : function() {
		if(tmp_total < limit) {
			jQuery("#monsterinsights_add_row").show();
		}
		else {
			jQuery("#monsterinsights_add_row").hide();
		}
	},

	add_row : function() {
		if( tmp_total < limit ) {

			total     = total + 1;
			tmp_total = tmp_total + 1;

			jQuery("tbody", "#monsterinsights-custom_dimensions").append('<tr id="monsterinsights-' + total + '"><td><select name="monsterinsights_settings[custom_dimensions][' + total + '][type]">' + options_to_add + '</select></td><td align="left"><input type="text" name="monsterinsights_settings[custom_dimensions][' + total + '][id]" value="' + total + '" style="width: 50px;" /></td><td><a href="#" id="monsterinsights_remove_' + total + '" class="monsterinsights-settings-click-excluded">' + translate_delete + '</a></td></tr>');

			custom_dimensions.set_limit_value( tmp_total );

			var current_select = jQuery('#monsterinsights-' + total + ' select');

			// Make new select unique by hiding the selected values from other select
			custom_dimensions.row_hide_options( current_select );

			custom_dimensions.select_first_visible_option( current_select );
		}

		// Toggle the button
		custom_dimensions.toggle_add_button();
	},

	remove_row : function() {

		var current_select_box = jQuery(this).closest('tr').find("select[name^='monsterinsights_settings[custom_dimensions]']");
		custom_dimensions.row_show_options( current_select_box );

		var old_id = this.id;
		var new_id = old_id.replace('monsterinsights_remove_', '');
		var disabled = !!jQuery("#monsterinsights-" + new_id + " select:disabled")[0];

		jQuery("#monsterinsights-" + new_id).remove();

		if ( ! disabled ) {
			tmp_total = tmp_total - 1;
			custom_dimensions.set_limit_value(tmp_total);
		}

		custom_dimensions.toggle_add_button();
	},

	row_init_options : function() {
		var select_boxes = jQuery("select[name^='monsterinsights_settings[custom_dimensions]']");
		select_boxes.each(
			function(i, select_box) {
				custom_dimensions.row_hide_options( select_box );
			}
		);
	},

	row_change_options : function( ) {
		var select_boxes = custom_dimensions.get_other_selects( this);
		select_boxes.find('option').show();

		custom_dimensions.row_init_options();
	},

	row_change_id : function( ) {
		jQuery(".error_custom_dimension").removeClass('error_custom_dimension');
		jQuery("#custom-dimension-error").remove();

		var change_id = jQuery(this).closest('tr').attr('id');
		var total_matches = 0;
		var tmp_value = jQuery(this).val();

		jQuery("input[name^='monsterinsights_settings[custom_dimensions]']").each(function(){
			if( tmp_value == jQuery(this).val() ){
				total_matches = total_matches + 1;
			}
		});

		if( total_matches >= 2 ){
			jQuery('input', '#' + change_id).addClass('error_custom_dimension');
			jQuery("#monsterinsights-custom_dimensions").prepend('<tr id="custom-dimension-error"><td colspan="2"><strong><font color="red">' + jQuery("#string_error_custom_dimensions").val() + '</font></strong></td></tr>');
		}
	},

	row_hide_options : function (current_select) {
		var select_boxes = custom_dimensions.get_other_selects( current_select );
		select_boxes.each(
			function(i, select_box) {
				var option_to_hide = jQuery(select_box).val();
				custom_dimensions.hide_option(current_select, option_to_hide);
			}
		);
	},

	row_show_options : function( current_select ) {
		var select_boxes   = custom_dimensions.get_other_selects( current_select );
		var option_to_show = jQuery( current_select ).val();

		select_boxes.each(
			function(i, select_box) {
				custom_dimensions.show_option(select_box, option_to_show);
			}
		);
	},

	set_limit_value : function( new_value ) {
		jQuery('#monsterinsights_limit').html( new_value );
	},


	get_other_selects : function( exclude_select ) {
		var select_boxes = jQuery("select[name^='monsterinsights_settings[custom_dimensions]']").not( exclude_select );
		return select_boxes;
	},

	hide_option : function(target_select, option_to_hide) {
		jQuery(target_select).children('option[value=' + option_to_hide + ']').hide();
	},

	show_option : function(target_select, option_to_show) {
		jQuery(target_select).children('option[value=' + option_to_show + ']').show();
	},

	select_first_visible_option : function(current_select_box) {
		var get_options  = current_select_box.children('option');
		get_options.each(
			function(i, option) {
				if(jQuery(option).css('display') == 'block') {
					jQuery(option).attr('selected', 'selected');

					current_select_box.change();

					return false;
				}
			}
		);
	},
};
