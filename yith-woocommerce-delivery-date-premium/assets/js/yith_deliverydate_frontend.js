jQuery(document).ready(function ($) {



    var get_current_shipping_method = function(){

            if( $('.shipping_method').length > 1 ){
                return $('.shipping_method:checked').val();
            }else{
                return $('.shipping_method').val();
            }
        },
        set_shipping_date = function( shipping ) {

            var shipping_date = $('.ywcdd_shipping_date');

            shipping_date.val(shipping);
        },
        set_time_slot = function( slots ){
            var content_time_slot = $('.ywcdd_timeslot_content'),
                timeslot = $('#ywcdd_timeslot'),
                content_timeslot_message = $('.ywcdd_timeslot_info'),
                row_slot = $('.form-row-slot'),
                timeslot_av = $('.ywcdd_timeslot_av');

            timeslot.find('option').not("[value='']").remove();

            if (!$.isEmptyObject(slots)) {

                $.each(slots, function (key, value) {

                    timeslot
                        .append($("<option></option>")
                            .attr("value", key)
                            .html(value));
                });
                content_time_slot.show();
                content_timeslot_message.show();
                timeslot_av.val('yes');

            } else {
                timeslot_av.val('no');
                content_time_slot.hide();
                content_timeslot_message.hide();
            }

            timeslot.val('').trigger('change');
        },
        ajax_find_time_slot = function (date_selected) {

            var content_time_slot = $('.ywcdd_timeslot_content');

            if (date_selected != '') {
                var carrier_id = $('#ywcdd_carrier').val(),
                    row_slot = $('.form-row-slot'),
                    process_method = $('#ywcdd_process_method');
                data = {
                    ywcdd_carrier_id: carrier_id,
                    ywcdd_date_selected: date_selected,
                    ywcdd_process_method: process_method.length ? process_method.val() : '',
                    action: ywcdd_params.actions.update_timeslot
                };
                content_time_slot.show();
                row_slot.hide();
                delivery_content.block( block_params );
                $.ajax({
                    type: 'POST',
                    url: ywcdd_params.ajax_url,
                    data: data,
                    dataType: 'json',
                    success: function (response) {

                        var slots = response.available_timeslot;
                        set_time_slot( slots );
                        set_shipping_date(response.shipping_date);
                        delivery_content.unblock();
                        open_date_picker();
                    }
                });
            } else {

                content_time_slot.hide();
            }

        },
        ajax_find_date_available = function (carrier_selected) {
            date_picker_content = $('.ywcdd_datepicker_content'),
                datepicker = $('#ywcdd_datepicker'),
                date_picker_info_content = $('.ywcdd_info_content');

            if (carrier_selected != '') {
                var process_method_id = $('#ywcdd_process_method').val(),
                    date_picker_message = date_picker_info_content.find('.ywcdd_message');

                data = {

                    ywcdd_carrier_id: carrier_selected,
                    ywcdd_process_id: process_method_id,
                    action: ywcdd_params.actions.update_datepicker

                };

                date_picker_content.hide();
                date_picker_info_content.hide();
                delivery_content.block( block_params );
                $.ajax({
                    type: 'POST',
                    url: ywcdd_params.ajax_url,
                    data: data,
                    dataType: 'json',
                    success: function (response) {

                        if (typeof response.available_days != 'undefined') {
                            var available_days = response.available_days;
                            datepicker.data('available_days', available_days);

                            var min = 0,
                                max = 0;

                            if (available_days.length > 0) {
                                min = available_days[0];
                                max = available_days[available_days.length - 1];
                            }
                            
                            datepicker.datepicker('option', 'minDate', min);
                            datepicker.datepicker('option', 'maxDate', max);
                            datepicker.datepicker('setDate', min);
                            //ajax_find_time_slot(min);
                            set_time_slot( response.available_timeslot );

                            set_shipping_date(response.shipping_date);
                            date_picker_message.html(response.message);
                            date_picker_info_content.show();
                            delivery_content.unblock();
                            open_date_picker();
                        }

                    }

                });
            } else {
                date_picker_info_content.hide();
                date_picker_content.hide();
                datepicker.val('');
                var timeslot = $('#ywcdd_timeslot');
                timeslot.find('option').not("[value='']").remove();
                timeslot.val('').trigger('change');
            }
            hide_timeslot();
        },
        hide_timeslot = function () {
            var content_time_slot = $('.ywcdd_timeslot_content'),
                row_slot = $('.form-row-slot');

            content_time_slot.hide();
            row_slot.hide();
        },
        block_params = {
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            },
            ignoreIfBlocked: true
        },
        open_date_picker = function(){
        	
        	if( 'yes' == ywcdd_params.open_datepicker ){

        		  var content_info = $(document).find('.ywcdd_info_content'),
                  date_picker_date = content_info.parent().find('.ywcdd_datepicker_content');

              content_info.hide();
              date_picker_date.show('slow');
              var content_info =  $(document).find('.ywcdd_timeslot_info'),
              timeslot = content_info.parent().find('.form-row-slot');

          content_info.hide();
          timeslot.show('slow');
        		
        	}
        }

    /*=== FUNCTION SECTION ===*/


   var delivery_content = $('div.ywcdd_select_delivery_date_content'),
       current_shipping_method = get_current_shipping_method();

    $(document).on('init-delivery-fields', function (e) {

        var datepicker = $('#ywcdd_datepicker'),
            select_carrier = $('#ywcdd_carrier');


        if (datepicker.length) {
            var availableDates = datepicker.data('available_days');
            
            datepicker.datepicker({
                'dateFormat': ywcdd_params.dateformat,
                'numberOfMonths': ywcdd_params.numberOfMonths*1,
                beforeShow: function( input, inst ){
                    $('#ui-datepicker-div').removeClass( 'yith_datepicker' );
                    
                    $('#ui-datepicker-div').addClass( 'yith_datepicker' );
                    setTimeout(function(){
                        $('#ui-datepicker-div').show();
                    }, 0);
                },
                beforeShowDay: function (date) {
                    var availableDates = $(this).data('available_days'),
                        string = $.datepicker.formatDate('yy-mm-dd', date);
                    return [availableDates.indexOf(string) != -1];
                },
                onClose: function (dateSelected, obj) {
                    var availableDates = $(this).data('available_days');

                    if ( typeof availableDates != 'undefined' && availableDates.indexOf(dateSelected) == -1 ) {
                        alert( 'Error: the date '+dateSelected+' isn\'t available' );
                        datepicker.datepicker('setDate', availableDates[0]);
                    } else {

                        ajax_find_time_slot(dateSelected);
                        $('#ui-datepicker-div').hide();
                        $('#ui-datepicker-div').unwrap();
                    }
                }
            });

            if (typeof availableDates != 'undefined' && availableDates.length > 0) {
                min = availableDates[0];
                max = availableDates[availableDates.length - 1];
                datepicker.datepicker('option', 'minDate', min);
                datepicker.datepicker('option', 'maxDate', max);
                datepicker.datepicker('setDate', min);
                ajax_find_time_slot(min);

             
               

            }
        }

        if (select_carrier.length) {

            select_carrier.on('change', function (e) {
                var carrier_selected = $(this).val();

                ajax_find_date_available(carrier_selected);
                
            });
          
            
            if( select_carrier.is( 'input' )  ){
            	
            	var value = select_carrier.val();
            	
            	if( value!=-1){
            		ajax_find_date_available(value);
            	}
            }
        }

       
        
    }).trigger('init-delivery-fields');

    $('form.checkout').on('change', '#ywcdd_timeslot', function (e) {


            $('body').trigger('update_checkout');
        })
        .on('click', '.ywcdd_edit_date', function (e) {
            e.preventDefault();
            var content_info = $(this).parents('.ywcdd_info_content'),
                date_picker_date = content_info.parent().find('.ywcdd_datepicker_content');

            content_info.hide('slow');
            date_picker_date.show('slow');
        })
        .on('click', '.ywcdd_show_timeslot', function (e) {
            e.preventDefault();
            var content_info = $(this).parents('.ywcdd_timeslot_info'),
                timeslot = content_info.parent().find('.form-row-slot');

            content_info.hide('slow');
            timeslot.show('slow');
        });

    $('body').on('updated_checkout', function (e) {

        var shipping_id = get_current_shipping_method(),
            process_method = $('#ywcdd_process_method');
        
        if ('' != shipping_id && current_shipping_method != shipping_id) {
            data = {

                ywcdd_update_carrier: 'update_carrier',
                ywcdd_shipping_id: shipping_id,
                ywcdd_process_method: process_method.val(),
                action: ywcdd_params.actions.update_carrier_list
            };

            current_shipping_method = shipping_id;
            $.ajax({
                type: 'POST',
                url: ywcdd_params.ajax_url,
                data: data,
                dataType: 'json',
                success: function (response) {

                    if (response.update_delivery_form) {
                        $('.ywcdd_select_delivery_date_content').html(response.template);

                        $('body').trigger('init-delivery-fields');


                    }
                }
            
            });
           
        }
    });
});