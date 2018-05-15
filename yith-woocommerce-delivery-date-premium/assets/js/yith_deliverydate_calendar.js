jQuery(document).ready(function ($) {

    var general_calendar = $('#ywcdd_general_calendar'),
        start_holiday = $('#yith_datepicker_from'),
        end_holiday = $('#yith_datepicker_to'),
        block_params = {
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            },
            ignoreIfBlocked: true
        },
        clear_form = function () {
            $('.how_holiday').select2('val', null);
                $('#yith_event_name').val('');
                $('#yith_datepicker_from').val('');
                $('#yith_datepicker_to').val('');
        }

    if (start_holiday.length) {

        start_holiday.datepicker({
            dateFormat: ywcdd_calendar_params.dateformat,
            defaultDate: "+1w",
            onClose: function (selectedDate) {
                $("#yith_datepicker_to").datepicker("option", "minDate", selectedDate);
            }
        });
    }

    if (end_holiday.length) {

        end_holiday.datepicker({
            dateFormat: ywcdd_calendar_params.dateformat,
            defaultDate: "+1w",
            onClose: function (selectedDate) {
                $("#yith_datepicker_from").datepicker("option", "maxDate", selectedDate);
            }
        });
    }

    if (general_calendar.length) {

        function renderCalendar() {

            $events_json = general_calendar.data('ywcdd_events_json');
            general_calendar.fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                defaultDate: ywcdd_calendar_params.starday,
                editable: false,
                eventLimit: true, // allow "more" link when too many events
                events: $events_json,
                eventRender: function (event, element, view) {
                    // we can remove only holiday
                    if (event.event_type == 'holiday') {

                        element.append("<span class='ywcdd_delete_calendar'></span>");
                        element.on('click', '.ywcdd_delete_calendar', function (e) {

                            var data = {
                                ywcdd_event_id: event._id,
                                action: ywcdd_calendar_params.actions.delete_holidays
                            };

                            $.ajax({
                                type: 'POST',
                                url: ywcdd_calendar_params.ajax_url,
                                data: data,
                                dataType: 'json',
                                success: function (response) {

                                    if (response.result === 'deleted') {
                                        $('#ywcdd_general_calendar').fullCalendar('removeEvents', event._id);
                                    }
                                }

                            });


                        });
                    }

                    element.find('.fc-title').html(element.find('.fc-title').text());


                }
            });
        }

        renderCalendar();

    }

    $('.yith-wcdd-new-holiday-content').on('blur change','#yith_event_name, #yith_datepicker_from, #yith_datepicker_to',function(e){

        if( $(this).val()  =='' ){

            $(this).addClass('ywcdd_required_field');
        }else{
            $(this).removeClass('ywcdd_required_field');
        }
    } );
    $('.yith-wcdd-new-holiday-content').on('change', '.how_holiday',function(e){
        var how_add_holiday = $('.how_holiday').select2('val');

        if( how_add_holiday == '' ){
            $('div.how_holiday>ul.select2-choices').addClass('ywcdd_required_field');
        }else{
            $('div.how_holiday>ul.select2-choices').removeClass('ywcdd_required_field');
        }

    });
    $('.yith-wcdd-new-holiday-content').on('click', '.yith-add-new-holiday', function (e) {


        e.preventDefault();
        var how_add_holiday = $('.how_holiday').select2('val'),
            event_name = $('#yith_event_name').val(),
            start_holiday = $('#yith_datepicker_from').val(),
            end_holiday = $('#yith_datepicker_to').val(),
            error = false;

        $('select.how_holiday').change();
        $('#yith_event_name').blur();
        $('#yith_datepicker_from').blur();
        $('#yith_datepicker_to').blur();

        if ( how_add_holiday!='' && event_name!='' && start_holiday!='' && end_holiday!='' ) {

            $('.yith-wcdd-new-holiday-content').block(block_params);

            var data = {
                ywcdd_add_holidays: 'add_new_holidays',
                ywcdd_how_add: how_add_holiday,
                ywcdd_start_event: start_holiday,
                ywcdd_end_event: end_holiday,
                ywcdd_event_name: event_name,
                action: ywcdd_calendar_params.actions.add_holidays
            };

            $.ajax({
                type: 'POST',
                url: ywcdd_calendar_params.ajax_url,
                data: data,
                dataType: 'json',
                success: function (response) {

                    clear_form();
                    var events_json = response.result;
                    general_calendar.data('ywcdd_events_json', events_json);
                    general_calendar.fullCalendar("destroy");
                    renderCalendar();
                    $('.yith-wcdd-new-holiday-content').unblock();

                }

            });
        }
    });
});