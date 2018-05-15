jQuery(document).ready(function($){

    //MULTISELECT DAY

    var multiselectday = $('#_ywcdd_workday').select2(),
        custom_select_day= $('.yith_dayworkselect').select2(),
    	initialize_select_all_clear = function(){
    	  $('.yith_timeslot_all_day').on('click', function (e) {
    	        e.preventDefault();
    	        var container = $(this).parents('.yith_single_multiworkday');
    	        
    	          timeselectday = container.find('select.yith_dayworkselect').select2();
    	        
    	       
    	        timeselectday.val(['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat']).trigger('change');
    	    });
    	    $('.yith_timeslot_clear').on('click', function (e) {
    	        e.preventDefault();
    	        var container = $(this).parents('.yith_single_multiworkday'),
    	        timeselectday = container.find('select.yith_dayworkselect').select2();
    	        timeselectday.val(null).trigger('change');
    	    });

    }
    	

    $('.yith_select_all_day').on('click', function (e) {
        e.preventDefault();
        multiselectday.val(['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat']).trigger('change');
    });
    $('.yith_select_clear').on('click', function (e) {
        e.preventDefault();
        multiselectday.val(null).trigger('change');
    });
    
  
    var initialize_time_picker = function () {

        $('.yith_timepicker').timepicker({
            'timeFormat': yith_delivery_parmas.timeformat,
            'step': yith_delivery_parmas.timestep,

        });
    },
     clear_input_add_slot = function () {
        $('#yith_timepicker_from').val('');
        $('#yith_timepicker_to').val('');
        $('#yith_max_tot_order').val('');
        $('#yith_fee').val('');
    };


    initialize_time_picker();

    var carrier_table = $('.ywcdd_carrier_table'),
        carrier_list = carrier_table.find('#the-list'),
        block_params = {
        message: null,
        overlayCSS: {
            background: '#fff',
            opacity: 0.6
        },
        ignoreIfBlocked: true
    };

    $('#yith_add_time_slot').on('click',function(e){
       
        e.preventDefault();
        
        var timefrom = $('#yith_timepicker_from').val(),
            timeto = $('#yith_timepicker_to').val(),
            max_order = $('#yith_max_tot_order').val(),
            fee = $('#yith_fee').val(),
            post_id = $('#yith_carrier_id').val(),
            metakey = $('#yith_metakey').val();
        
        if( timefrom!='' && timeto!='' ){

            var data = {
                ywcdd_time_from: timefrom,
                ywcdd_time_to: timeto,
                ywcdd_max_order: max_order,
                ywcdd_fee: fee,
                ywcdd_post_id : post_id,
                ywcdd_metakey: metakey,
                action: yith_delivery_parmas.actions.add_carrier_time_slot
            };
           carrier_table.block(block_params);

            $.ajax({
                type: 'POST',
                url: yith_delivery_parmas.ajax_url,
                data: data,
                dataType: 'json',
                success: function (response) {
                	carrier_table.unblock();
                	
                	carrier_table.find('.no-items').hide();
                	
                	rows = carrier_table.find('#the-list>tr').not('.no-items').get();
                	
                	if( rows.length > 0 ){
                		
                		carrier_table.find('#the-list>tr:last-child').not('.no-items').after( response.template );
                	}else{
                		carrier_table.find('.no-items').after( response.template );
                	}
              
                    carrier_table.find('select.yith_dayworkselect').select2();
                    initialize_time_picker();
                    initialize_select_all_clear();
                    show_hide_select();
                    update_time_slot_row();
                    delete_time_slot_row();
                    clear_input_add_slot();
                   
                }

            });
           
        }
            
    });

    //UPDATE SINGLE TIMESLOT ROW


    var adjust_index = function () {

            var timeslot_up = carrier_table.find('a.yith_update_time_slot'),
                timeslot_delete = carrier_table.find('a.yith_delete_time_slot');

            timeslot_up.each(function (index) {

                $(this).data('item_id', index);

            });

            timeslot_delete.each(function (index) {

                $(this).data('item_id', index);

            });
        },
        update_time_slot_row = function () {
            update_button = carrier_table.find('.yith_update_time_slot');
            update_button.on('click', function (e) {

                e.preventDefault();
                var row = $(this).parents('tr'),
                    time_from = row.find('.timepicker_timefrom').val(),
                    time_to = row.find('.timepicker_timeto').val(),
                    max_order = row.find('.yith_max_order').val(),
                    fee = row.find('.yith_fee').val(),
                    override_day = row.find('.yith_override_day').is(':checked') ? 'yes' : 'no',
                    days = row.find('.yith_dayworkselect').select2('val'),
                    item_id = $(this).data('item_id'),
                    carrier_id = $('#yith_carrier_id').val(),
                    metakey = $('#yith_metakey').val();

               
                if (time_from != '' && time_to != '') {
                    carrier_table.block(block_params);
                    var data = {
                        ywcdd_time_from: time_from,
                        ywcdd_time_to: time_to,
                        ywcdd_max_order: max_order,
                        ywcdd_fee: fee,
                        ywcdd_day:days,
                        ywcdd_carrier_id : carrier_id,
                        ywcdd_metakey: metakey,
                        override_days: override_day,
                        item_id: item_id,
                        action: yith_delivery_parmas.actions.update_carrier_time_slot
                    };



                    $.ajax({
                        type: 'POST',
                        url: yith_delivery_parmas.ajax_url,
                        data: data,
                        dataType: 'json',
                        success: function (response) {
                            carrier_table.unblock();
                        }

                    });
                }

            });

        };


    //DELETE SINGLE TIMESLOT ROW
    var delete_time_slot_row = function () {

        delete_button = carrier_table.find('.yith_delete_time_slot');

        delete_button.on('click', function (e) {
            e.preventDefault();
            var row = $(this).parents('tr'),
                item_id = $(this).data('item_id'),
                table = $('#the-list'),
                carrier_id = $('#yith_carrier_id').val(),
                metakey = $('#yith_metakey').val();

            var data = {

                item_id: item_id,
                ywcdd_carrier_id : carrier_id,
                ywcdd_metakey: metakey,
                action: yith_delivery_parmas.actions.delete_carrier_time_slot
            };
            carrier_table.block(block_params);

            $.ajax({
                type: 'POST',
                url: yith_delivery_parmas.ajax_url,
                data: data,
                dataType: 'json',
                success: function (response) {

                    row.remove();


                    if( table.find('tr').length == 0 ){

                        table.html( yith_delivery_parmas.empty_row );
                    }
                    adjust_index();
                    carrier_table.unblock();
                }

            });

        });
    };

    update_time_slot_row();
    delete_time_slot_row();

    //SHOW/HIDE MULTISELECT DAY

    var show_hide_select = function () {

        $('.yith_override_day').on('change', function (e) {
            var t = $(this),
                div = t.parents('tr').find('.yith_single_multiworkday'),
                hidden_field = t.parents('tr').find('.yith_over_day');

            if (t.is(':checked')) {

                div.show('slow');
                hidden_field.val('yes');
            }
            else {
                div.hide('slow');
                hidden_field.val('no');

            }
        }).trigger('change');
    };

    show_hide_select();
    
    $(document).on('click','.yith_timeslot_all_day', function(e){
    	e.preventDefault();
    	 var t = $(this),
         div = t.parents('tr').find('.yith_dayworkselect');
    	
    	 div.val(['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat']).trigger('change');
    	
    });
    
    $(document).on('click','.yith_timeslot_clear', function(e){
    	e.preventDefault();
    	 var t = $(this),
         div = t.parents('tr').find('.yith_dayworkselect');
    	
    	 div.val(null).trigger('change');
    	
    });

});
