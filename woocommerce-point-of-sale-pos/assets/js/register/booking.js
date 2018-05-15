jQuery(document).ready(function ($) {
    window.POS_BOOKING = {
        bookings_timeout: 0,
        fields: {},
        data: {},
        xhr_rice: null,

        init: function () {
            wp.hooks.addFilter('wc_pos_add_to_cart_handler_booking', BOOKING.addToCart, 20, 7);

            $('#booking-add-btn').on('click', function (event) {
                if ($(this).hasClass('disabled')) {
                    APP.showNotice(booking_form_params.i18n_choose_options, 'error');
                } else {
                    var $parent = $(this).closest('.md-modal');
                    var product_id = BOOKING.data.product_id;
                    var variation_id = BOOKING.data.variation_id;
                    var adding_to_cart = BOOKING.data.adding_to_cart;
                    var quantity = 1;

                    var posted_data = BOOKING.create_posted_data();

                    var variation = {};
                    $.each(posted_data, function (key, val) {
                        if (key.substr(0, 1) !== '_') {
                            var label = BOOKING.get_booking_data_label(key, BOOKING.data.adding_to_cart.booking);
                            variation[label] = val;
                        }
                    });
                    adding_to_cart.hidden_fields = {
                        'booking': $('.product-fields-table').find(':input').serialize()
                    };

                    adding_to_cart.price = posted_data['_cost'];
                    adding_to_cart.regular_price = adding_to_cart.price;
                    CART.addToCart(adding_to_cart, product_id, quantity, variation_id, variation, BOOKING.data.cart_item_data);

                    var modalid = $parent.attr('id');
                    closeModal(modalid);
                    window.openwin = false;
                    return true;
                }
                return false;
            });
        },
        addToCart: function (was_added_to_cart, product_id, adding_to_cart, quantity, variation_id, variation, cart_item_data) {
            if (typeof adding_to_cart.item_id != 'undefined') {

                adding_to_cart.price = adding_to_cart.loaded_price;
                adding_to_cart.regular_price = adding_to_cart.loaded_price;

                CART.addToCart(adding_to_cart, product_id, quantity, variation_id, variation, cart_item_data);
                return true;
            }

            BOOKING.data = {
                product_id: product_id,
                adding_to_cart: adding_to_cart,
                quantity: quantity,
                variation_id: variation_id,
                variation: variation,
                cart_item_data: cart_item_data
            };

            if (adding_to_cart.booking.duration_unit == 'minute' || adding_to_cart.booking.duration_unit == 'hour') {
                BOOKING.data.adding_to_cart.booking.duration = 1;
            }

            was_added_to_cart = BOOKING.add_to_cart_handler_booking();
            return was_added_to_cart;
        },
        add_to_cart_handler_booking: function () {
            BOOKING.init_fields();
            var source = $('#tmpl-booking-data').html();
            var template = Handlebars.compile(source);

            var html = template({fields: BOOKING.fields, product: BOOKING.data.adding_to_cart.booking});

            $('#booking-data-content').html(html);

            openModal('modal-booking-data', true);

            $('#wc-bookings-booking-cost').hide();

            BOOKING.init_date_picker();
            BOOKING.init_time_picker();
            BOOKING.init_calculation();

            return false;
        },

        add_field: function (field) {
            if (typeof field != 'object') return;

            var defaults = {
                'name': '',
                'class': [],
                'label': '',
                'type': 'text'
            };

            field = $.extend(defaults, field);

            if (!field['name'] || !field['type']) {
                return;
            }

            var nicename = 'wc_bookings_field_' + field['name'];

            field['name'] = nicename;
            field['class'].push(nicename);

            BOOKING.fields[field['name']] = field;
        },

        init_fields: function () {

            // Destroy existing fields
            BOOKING.fields = {};

            // Add fields in order
            BOOKING.duration_field();
            BOOKING.persons_field();
            BOOKING.resources_field();
            BOOKING.date_field();

        },

        /**
         * Add duration field to the form
         */
        duration_field: function () {

            var product = BOOKING.data.adding_to_cart;

            // Customer defined bookings
            if ('customer' == product.booking.duration_type) {
                var after = '';
                switch (product.booking.duration_unit) {
                    case 'month' :
                        if (product.booking.duration > 1) {
                            after = sprintf(booking_i18n[0], product.booking.duration);
                        } else {
                            after = booking_i18n[1];
                        }
                        break;
                    case 'week' :
                        if (product.booking.duration > 1) {
                            after = sprintf(booking_i18n[2], product.booking.duration);
                        } else {
                            after = booking_i18n[3];
                        }
                        break;
                    case 'day' :
                        if (product.booking.duration % 7) {
                            if (product.booking.duration > 1) {
                                after = sprintf(booking_i18n[4], product.booking.duration);
                            } else {
                                after = booking_i18n[5];
                            }
                        } else {
                            if (product.booking.duration / 7 == 1) {
                                after = booking_i18n[3];
                            } else {
                                after = sprintf(booking_i18n[2], product.booking.duration / 7);
                            }
                        }
                        break;
                    case 'night' :
                        if (product.booking.duration > 1) {
                            after = sprintf(booking_i18n[6], product.booking.duration);
                        } else {
                            after = booking_i18n[7];
                        }
                        break;
                    case 'hour' :
                        if (product.booking.duration > 1) {
                            after = sprintf(booking_i18n[8], product.booking.duration);
                        } else {
                            after = booking_i18n[9];
                        }
                        break;
                    case 'minute' :
                        if (product.booking.duration > 1) {
                            $after = sprintf(booking_i18n[10], product.booking.duration);
                        } else {
                            after = booking_i18n[11];
                        }
                        break;
                }

                BOOKING.add_field({
                    'type': 'number',
                    'name': 'duration',
                    'label': booking_i18n[12],
                    'after': after,
                    'min': product.booking.min_duration,
                    'max': product.booking.max_duration,
                    'step': 1
                });
            }

        },

        /**
         * Add persons field
         */
        persons_field: function () {
            var product = BOOKING.data.adding_to_cart;
            // Persons field
            if (product.booking.has_persons) {
                if (product.booking.has_person_types) {
                    var person_types = product.booking.person_types;
                    var person_keys = Object.keys(person_types);
                    for (var i = 0; i < person_keys.length; i++) {
                        var person_type = person_types[person_keys[i]];
                        var min_person_type_persons = person_type['min_person_type_persons'];
                        var max_person_type_persons = person_type['max_person_type_persons'];
                        BOOKING.add_field({
                            'type': 'number',
                            'step': 1,
                            'min': is_numeric(min_person_type_persons) ? min_person_type_persons : 0,
                            'max': max_person_type_persons != "" ? parseInt(max_person_type_persons) : product.booking.max_persons_group,
                            'name': 'persons_' + person_keys[i],
                            'label': person_type['post_title'],
                            'after': person_type['post_excerpt']
                        });
                    }
                } else {
                    BOOKING.add_field({
                        'type': 'number',
                        'step': 1,
                        'min': is_numeric(product.booking.min_persons) ? product.booking.min_persons : 0,
                        'max': product.booking.max_persons ? product.booking.max_persons : '',
                        'name': 'persons',
                        'label': booking_i18n[13]
                    });
                }
            }
        },

        /**
         * Add resources field
         */
        resources_field: function () {
            var product = BOOKING.data.adding_to_cart;
            // Resources field
            if ('1' == product.booking.has_resources && 'customer' == product.booking.resources_assignment) {

                var resources = product.booking.resources,
                    resource_options = [],
                    data = [],
                    cost = '',
                    additional_cost_string = '';

                for (var i = 0; i < resources.length; i++) {
                    var resource = resources[i],
                        additional_cost = [];
                    var cost_plus_base = ( resource['base_cost'] + resource['block_cost'] + product.booking.base_cost + product.booking.cost );

                    if (resource['base_cost'] && product.booking.base_cost < cost_plus_base) {
                        cost = accountingPOS(cost_plus_base - product.booking.base_cost);
                        additional_cost.push('+' + cost);
                    }

                    if (resource['block_cost']) {
                        switch (product.booking.duration_unit) {
                            case 'month' :
                                cost = accountingPOS(resource['block_cost'] + product.booking.base_cost);
                                additional_cost.push(sprintf(booking_i18n[14], cost));
                                break;
                            case 'day' :
                                cost = accountingPOS(resource['block_cost'] + product.booking.base_cost);
                                additional_cost.push(sprintf(booking_i18n[15], cost));
                                break;
                            case 'night' :
                                cost = accountingPOS(resource['block_cost'] + product.booking.base_cost);
                                additional_cost.push(sprintf(booking_i18n[16], cost));
                                break;
                            default :
                                cost = accountingPOS(resource['block_cost'] + product.booking.base_cost);
                                additional_cost.push(sprintf(booking_i18n[17], cost));
                                break;
                        }
                    }

                    if (additional_cost.length) {
                        additional_cost_string = ' (' + additional_cost.join(', ') + ')';
                    }

                    resource_options[resource['ID']] = resource.post_title + additional_cost_string;
                }

                BOOKING.add_field({
                    'type': 'select',
                    'name': 'resource',
                    'label': product.booking.resouce_label ? product.booking.resouce_label : booking_i18n[18],
                    'options': resource_options
                });
            }
        },

        /**
         * Add the date field to the booking form
         */
        date_field: function () {
            var picker = null;
            var product = BOOKING.data.adding_to_cart;

            // Get date picker specific to the duration unit for this product
            switch (product.booking.duration_unit) {
                case 'month' :
                    picker = product.booking['Month_Picker'];
                    break;
                case 'day' :
                case 'night' :
                    picker = product.booking['Date_Picker'];
                    break;
                case 'minute' :
                case 'hour' :
                    picker = product.booking['Datetime_Picker'];
                    break;
                default :
                    break;
            }

            picker.min_date_js = picker.min_date_js == '' ? 0 : picker.min_date_js;

            if (picker != null) {
                BOOKING.add_field(picker);
            }

        },
        init_date_picker: function (element) {
            $('.product-fields-table').on('change', '#wc_bookings_field_duration, #wc_bookings_field_resource', BOOKING.date_picker);
            $('.product-fields-table').on('click', 'a.wc-bookings-date-picker-choose-date', BOOKING.toggle_calendar);
            $('.product-fields-table').on('input', '.booking_date_year, .booking_date_month, .booking_date_day', BOOKING.input_date_trigger);
            $('.product-fields-table').on('keypress', '.booking_date_year, .booking_date_month, .booking_date_day', BOOKING.input_date_keypress);
            $('.product-fields-table').on('keypress', '.booking_to_date_year, .booking_to_date_month, .booking_to_date_day', BOOKING.input_date_keypress);
            $('.product-fields-table').on('change', '.booking_to_date_year, .booking_to_date_month, .booking_to_date_day', BOOKING.input_date_trigger);


            $('.wc-bookings-date-picker').each(function () {
                var form = $(this).closest('.product-fields-table'),
                    picker = $(this).find('.picker'),
                    fieldset = $(this);

                BOOKING.date_picker(picker);

                if (picker.data('display') == 'always_visible') {
                    $('.wc-bookings-date-picker-date-fields', fieldset).hide();
                    $('.wc-bookings-date-picker-choose-date', fieldset).hide();
                } else {
                    picker.hide();
                }

                if (picker.data('is_range_picker_enabled')) {
                    form.find('#wc_bookings_field_duration').closest('tr').hide();
                    form.find('.wc_bookings_field_start_date span.label').text('always_visible' !== picker.data('display') ? booking_form_params.i18n_dates : booking_form_params.i18n_start_date);
                }
            });
        },
        calc_duration: function (picker) {
            var form = picker.closest('.product-fields-table'),
                fieldset = picker.closest('.wc-bookings-date-picker'),
                unit = picker.data('duration-unit');

            setTimeout(function () {
                var days = 1,
                    e_year = parseInt(fieldset.find('input.booking_to_date_year').val(), 10),
                    e_month = parseInt(fieldset.find('input.booking_to_date_month').val(), 10),
                    e_day = parseInt(fieldset.find('input.booking_to_date_day').val(), 10),
                    s_year = parseInt(fieldset.find('input.booking_date_year').val(), 10),
                    s_month = parseInt(fieldset.find('input.booking_date_month').val(), 10),
                    s_day = parseInt(fieldset.find('input.booking_date_day').val(), 10);

                if (e_year && e_month >= 0 && e_day && s_year && s_month >= 0 && s_day) {
                    var s_date = new Date(Date.UTC(s_year, s_month - 1, s_day)),
                        e_date = new Date(Date.UTC(e_year, e_month - 1, e_day));

                    days = Math.floor(( e_date.getTime() - s_date.getTime() ) / ( 1000 * 60 * 60 * 24 ));
                    if ('day' === unit) {
                        days = days + 1;
                    }
                }

                form.find('#wc_bookings_field_duration').val(days).change();
            });

        },
        toggle_calendar: function () {
            $picker = $(this).closest('.wc-bookings-date-picker').find('.picker:eq(0)');
            BOOKING.date_picker($picker);
            $picker.slideToggle(0);
        },
        input_date_keypress: function () {
            var $fieldset = $(this).closest('.wc-bookings-date-picker'),
                $picker = $fieldset.find('.picker:eq(0)');

            if ($picker.data('is_range_picker_enabled')) {
                clearTimeout(BOOKING.bookings_timeout);

                BOOKING.bookings_timeout = setTimeout(BOOKING.trigger_cost_update, 800);
            }
        },
        trigger_cost_update: function () {
            $('.product-fields-table').find('input').eq(0).trigger('change');
        },
        input_date_trigger: function () {
            var $fieldset = $(this).closest('tr'),
                $picker = $fieldset.find('.picker:eq(0)'),
                $form = $(this).closest('.product-fields-table'),
                year = parseInt($fieldset.find('input.booking_date_year').val(), 10),
                month = parseInt($fieldset.find('input.booking_date_month').val(), 10),
                day = parseInt($fieldset.find('input.booking_date_day').val(), 10);

            if (year && month && day) {
                var date = new Date(year, month - 1, day);
                $picker.datepicker("setDate", date);

                if ($picker.data('is_range_picker_enabled')) {
                    var to_year = parseInt($fieldset.find('input.booking_to_date_year').val(), 10),
                        to_month = parseInt($fieldset.find('input.booking_to_date_month').val(), 10),
                        to_day = parseInt($fieldset.find('input.booking_to_date_day').val(), 10);

                    var to_date = new Date(to_year, to_month - 1, to_day);

                    if (!to_date || to_date < date) {
                        $fieldset.find('input.booking_to_date_year').val('').addClass('error');
                        $fieldset.find('input.booking_to_date_month').val('').addClass('error');
                        $fieldset.find('input.booking_to_date_day').val('').addClass('error');
                    } else {
                        $fieldset.find('input').removeClass('error');
                    }
                }
                $fieldset.triggerHandler('date-selected', date);
            }
        },
        date_picker: function (element) {
            var $picker;
            if ($(element).is('.picker')) {
                $picker = $(element);
            } else {
                $picker = $(this).closest('.product-fields-table').find('.picker:eq(0)');
            }

            $picker.empty().removeClass('hasDatepicker').datepicker({
                dateFormat: $.datepicker.ISO_8601,
                showWeek: false,
                showOn: false,
                beforeShowDay: BOOKING.is_bookable,
                onSelect: BOOKING.select_date_trigger,
                minDate: $picker.data('min_date'),
                maxDate: $picker.data('max_date'),
                defaultDate: $picker.data('default_date'),
                numberOfMonths: 1,
                showButtonPanel: false,
                showOtherMonths: true,
                selectOtherMonths: true,
                closeText: pos_booking_form_args.closeText,
                currentText: pos_booking_form_args.currentText,
                prevText: pos_booking_form_args.prevText,
                nextText: pos_booking_form_args.nextText,
                monthNames: pos_booking_form_args.monthNames,
                monthNamesShort: pos_booking_form_args.monthNamesShort,
                dayNames: pos_booking_form_args.dayNames,
                dayNamesShort: pos_booking_form_args.dayNamesShort,
                dayNamesMin: pos_booking_form_args.dayNamesMin,
                firstDay: pos_booking_form_args.firstDay,
                gotoCurrent: true
            });

            $('.ui-datepicker-current-day').removeClass('ui-datepicker-current-day');

            var table = $picker.closest('.product-fields-table'),
                year = parseInt(table.find('input.booking_date_year').val(), 10),
                month = parseInt(table.find('input.booking_date_month').val(), 10),
                day = parseInt(table.find('input.booking_date_day').val(), 10);

            if (year && month && day) {
                var date = new Date(year, month - 1, day);
                $picker.datepicker("setDate", date);
            }
        },
        init_time_picker: function () {
            $('.block-picker').on('click', 'a', function () {
                var value = $(this).data('value');
                var target = $(this).closest('td').find('input');

                target.val(value).change();
                $(this).closest('ul').find('a').removeClass('button-primary');
                $(this).addClass('button-primary');

                return false;
            });

            $('#wc_bookings_field_resource, #wc_bookings_field_duration').change(function () {
                show_available_time_blocks(this);
            });
            $('.product-fields-table tr').on('date-selected', function () {
                show_available_time_blocks(this);
            });

            var xhr;

            function show_available_time_blocks(element) {
                var $form = $(element).closest('.product-fields-table');
                var block_picker = $(element).next().find('.block-picker');
                var fieldset = $(element);

                var year = parseInt(fieldset.find('input.booking_date_year').val(), 10);
                var month = parseInt(fieldset.find('input.booking_date_month').val(), 10);
                var day = parseInt(fieldset.find('input.booking_date_day').val(), 10);

                if (!year || !month || !day) {
                    return;
                }

                // clear blocks
                block_picker.closest('td').find('input').val('').change();
                block_picker.closest('div').block({
                    message: null,
                    overlayCSS: {background: '#fff', backgroundSize: '16px 16px', opacity: 0.6}
                }).show();

                // Get blocks via ajax
                if (xhr) xhr.abort();

                xhr = $.ajax({
                    type: 'POST',
                    url: booking_form_params.ajax_url,
                    data: {
                        action: 'wc_bookings_get_blocks',
                        form: $form.find(':input').serialize() + '&add-to-cart=' + BOOKING.data.product_id
                    },
                    success: function (code) {
                        var $list = $(code);
                        $list.find('a').addClass('button');
                        block_picker.html($list);
                        resize_blocks();
                        block_picker.closest('div').unblock();
                    },
                    dataType: "html"
                });
            }

            function resize_blocks() {
                var max_width = 0;
                var max_height = 0;

                $('.block-picker a').each(function () {
                    var width = $(this).width();
                    var height = $(this).height();
                    if (width > max_width) {
                        max_width = width;
                    }
                    if (height > max_height) {
                        max_height = height;
                    }
                });

                $('.block-picker a').width(max_width);
                $('.block-picker a').height(max_height);
            }
        },

        init_calculation: function () {
            //var $form  = $('.product-fields-table');
            $('.product-fields-table')
                .on('change', 'input, select', function () {
                    var name = $(this).attr('name');

                    var $fieldset = $(this).closest('.wc-bookings-date-picker');
                    var $picker = $fieldset.find('.picker:eq(0)');
                    if ($picker.data('is_range_picker_enabled')) {
                        if ('wc_bookings_field_duration' !== name) {
                            return;
                        }
                    }

                    $form = $(this).closest('.product-fields-table');

                    var required_fields = $form.find('input.required_for_calculation');
                    var filled = true;
                    $.each(required_fields, function (index, field) {
                        var value = $(field).val();
                        if (!value) {
                            filled = false;
                        }
                    });
                    if (!filled) {
                        $('#wc-bookings-booking-cost').hide();
                        return;
                    }

                    $('#wc-bookings-booking-cost').block({
                        message: null,
                        overlayCSS: {background: '#fff', backgroundSize: '16px 16px', opacity: 0.6}
                    }).show();
                    // Get blocks via ajax
                    if (BOOKING.xhr_rice != null) BOOKING.xhr_rice.abort();
                    BOOKING.xhr_rice = $.ajax({
                        type: 'POST',
                        url: booking_form_params.ajax_url,
                        data: {
                            action: 'wc_bookings_calculate_costs',
                            form: $form.find(':input').serialize() + '&add-to-cart=' + BOOKING.data.product_id
                        },
                        success: function (code) {
                            if (code.charAt(0) !== '{') {
                                code = '{' + code.split(/\{(.+)?/)[1];
                            }

                            result = $.parseJSON(code);

                            if (result.result == 'ERROR') {
                                $('#wc-bookings-booking-cost').show().html(result.html);
                                $('#wc-bookings-booking-cost').show().unblock();
                                $('#booking-add-btn').addClass('disabled');
                            } else if (result.result == 'SUCCESS') {
                                $('#wc-bookings-booking-cost').html(result.html);
                                $('#wc-bookings-booking-cost').unblock();
                                $('#booking-add-btn').removeClass('disabled');
                            } else {
                                $('#wc-bookings-booking-cost').hide();
                                $('#booking-add-btn').addClass('disabled');
                            }
                        },
                        error: function () {
                            $('#booking-add-btn').addClass('disabled');
                        },
                        dataType: "html"
                    });
                });

            $('#booking-add-btn').addClass('disabled');

        },


        /****/
        select_date_trigger: function (date) {
            var fieldset = $(this).closest('tr'),
                picker = fieldset.find('.picker:eq(0)'),
                form = $(this).closest('.product-fields-table'),
                parsed_date = date.split('-'),
                start_or_end_date = picker.data('start_or_end_date');

            if (!picker.data('is_range_picker_enabled') || !start_or_end_date) {
                start_or_end_date = 'start';
            }


            // End date selected
            if (start_or_end_date === 'end') {

                // Set min date to default
                picker.data('min_date', picker.data('o_min_date'));

                // Set fields
                fieldset.find('input.booking_to_date_year').val(parsed_date[0]);
                fieldset.find('input.booking_to_date_month').val(parsed_date[1]);
                fieldset.find('input.booking_to_date_day').val(parsed_date[2]).change();

                // Calc duration
                if (picker.data('is_range_picker_enabled')) {
                    BOOKING.calc_duration(picker);
                }

                // Next click will be start date
                picker.data('start_or_end_date', 'start');

                if (picker.data('is_range_picker_enabled')) {
                    form.find('.wc_bookings_field_start_date span.label').text('always_visible' !== picker.data('display') ? booking_form_params.i18n_dates : booking_form_params.i18n_start_date);
                }

                if ('always_visible' !== picker.data('display')) {
                    $(this).hide();
                }

                // Start date selected
            } else {
                // Set min date to today
                if (picker.data('is_range_picker_enabled')) {
                    picker.data('o_min_date', picker.data('min_date'));
                    picker.data('min_date', date);
                }

                // Set fields
                fieldset.find('input.booking_to_date_year').val('');
                fieldset.find('input.booking_to_date_month').val('');
                fieldset.find('input.booking_to_date_day').val('');

                fieldset.find('input.booking_date_year').val(parsed_date[0]);
                fieldset.find('input.booking_date_month').val(parsed_date[1]);
                fieldset.find('input.booking_date_day').val(parsed_date[2]).change();

                // Calc duration
                if (picker.data('is_range_picker_enabled')) {
                    BOOKING.calc_duration(picker);
                }

                // Next click will be end date
                picker.data('start_or_end_date', 'end');

                if (picker.data('is_range_picker_enabled')) {
                    form.find('.wc_bookings_field_start_date span.label').text(booking_form_params.i18n_end_date);
                }

                if ('always_visible' !== picker.data('display') && !picker.data('is_range_picker_enabled')) {
                    $(this).hide();
                }
            }

            fieldset.triggerHandler('date-selected', date, start_or_end_date);
        },
        is_bookable: function (date) {
            var product = BOOKING.data.adding_to_cart;
            var $form = $(this).closest('.product-fields-table'),
                $picker = $form.find('.picker:eq(0)'),
                availability = $(this).data('availability'),
                default_availability = $(this).data('default-availability'),
                fully_booked_days = $(this).data('fully-booked-days'),
                buffer_days = $(this).data('buffer-days'),
                partially_booked_days = $(this).data('partially-booked-days'),
                check_availability_against = product.booking.check_availability_against,
                css_classes = '',
                title = '',
                resource_id = 0,
                resources_assignment = product.booking.resources_assignment;
            // Get selected resource
            if ($form.find('select#wc_bookings_field_resource').val() > 0) {
                resource_id = $form.find('select#wc_bookings_field_resource').val();
            }


            // Get days needed for block - this affects availability
            var duration = product.booking.duration,
                the_date = new Date(date),
                year = the_date.getFullYear(),
                month = the_date.getMonth() + 1,
                day = the_date.getDate();

            // Fully booked?
            if (fully_booked_days[year + '-' + month + '-' + day]) {
                if (fully_booked_days[year + '-' + month + '-' + day][0] || fully_booked_days[year + '-' + month + '-' + day][resource_id]) {
                    return [false, 'fully_booked', booking_form_params.i18n_date_fully_booked];
                }
            }

            // Buffer days?
            if ('undefined' !== typeof buffer_days && buffer_days[year + '-' + month + '-' + day]) {
                return [false, 'not_bookable', booking_form_params.i18n_date_unavailable];
            }

            if ('' + year + month + day < pos_booking_form_args.current_time) {
                return [false, 'not_bookable', booking_form_params.i18n_date_unavailable];
            }
            // Apply partially booked CSS class.
            if (partially_booked_days && partially_booked_days[year + '-' + month + '-' + day]) {
                if (partially_booked_days[year + '-' + month + '-' + day][0] || partially_booked_days[year + '-' + month + '-' + day][resource_id]) {
                    css_classes = css_classes + 'partial_booked ';
                }
            }

            if ($form.find('#wc_bookings_field_duration').size() > 0 && product.booking.duration_unit != 'minute' && product.booking.duration_unit != 'hour' && !$picker.data('is_range_picker_enabled')) {
                var user_duration = $form.find('#wc_bookings_field_duration').val();
                var days_needed = duration * user_duration;
            } else {
                var days_needed = duration;
            }

            if (days_needed < 1 || product.booking.check_availability_against == 'start') {
                days_needed = 1;
            }

            var bookable = default_availability;
            // Loop all the days we need to check for this block
            for (var i = 0; i < days_needed; i++) {
                var the_date = new Date(date);
                the_date.setDate(the_date.getDate() + i);

                var year = the_date.getFullYear();
                var month = the_date.getMonth() + 1;
                var day = the_date.getDate();
                var day_of_week = the_date.getDay();
                var week = $.datepicker.iso8601Week(the_date);

                // Reset bookable for each day being checked
                bookable = default_availability;

                // Sunday is 0, Monday is 1, and so on.
                if (day_of_week == 0) {
                    day_of_week = 7;
                }
                $.each(availability[resource_id], function (index, rule) {
                    var type = rule.type;
                    var rules = rule.range;
                    try {
                        switch (type) {
                            case 'months':
                                if (typeof rules[month] != 'undefined') {
                                    bookable = rules[month];
                                    return false;
                                }
                                break;
                            case 'weeks':
                                if (typeof rules[week] != 'undefined') {
                                    bookable = rules[week];
                                    return false;
                                }
                                break;
                            case 'days':
                                if (typeof rules[day_of_week] != 'undefined') {
                                    bookable = rules[day_of_week];
                                    return false;
                                }
                                break;
                            case 'custom':
                                if (typeof rules[year][month][day] != 'undefined') {
                                    bookable = rules[year][month][day];
                                    return false;
                                }
                                break;
                            case 'time':
                            case 'time:1':
                            case 'time:2':
                            case 'time:3':
                            case 'time:4':
                            case 'time:5':
                            case 'time:6':
                            case 'time:7':
                                if (false === default_availability && ( day_of_week === rules.day || 0 === rules.day )) {
                                    bookable = rules.rule;
                                    return false;
                                }
                                break;
                            case 'time:range':
                                if (false === default_availability && ( typeof rules[year][month][day] != 'undefined' )) {
                                    bookable = rules[year][month][day].rule;
                                    return false;
                                }
                                break;
                        }

                    } catch (err) {
                    }
                    return true;
                });

                // Fully booked in entire block?
                if (fully_booked_days[year + '-' + month + '-' + day]) {
                    if (fully_booked_days[year + '-' + month + '-' + day][0] || fully_booked_days[year + '-' + month + '-' + day][resource_id]) {
                        bookable = false;
                    }
                }

                if (!bookable) {
                    break;
                }
            }
            if (!bookable) {
                return [bookable, 'not_bookable', booking_form_params.i18n_date_unavailable];
            } else {

                if (css_classes.indexOf('partial_booked') > -1) {
                    title = booking_form_params.i18n_date_partially_booked;
                } else {
                    title = booking_form_params.i18n_date_available;
                }

                if ($picker.data('is_range_picker_enabled')) {
                    var fieldset = $(this).closest('.product-fields-table'),
                        start_date = $.datepicker.parseDate($.datepicker.ISO_8601, BOOKING.get_input_date(fieldset, '')),
                        end_date = $.datepicker.parseDate($.datepicker.ISO_8601, BOOKING.get_input_date(fieldset, 'to_'));

                    return [bookable, start_date && ( ( date.getTime() === start_date.getTime() ) || ( end_date && date >= start_date && date <= end_date ) ) ? css_classes + 'bookable-range' : css_classes + 'bookable', title];
                } else {
                    return [bookable, css_classes + 'bookable', title];
                }
            }
        },

        get_input_date: function (fieldset, where) {
            var year = fieldset.find('input.booking_' + where + 'date_year'),
                month = fieldset.find('input.booking_' + where + 'date_month'),
                day = fieldset.find('input.booking_' + where + 'date_day');

            if (0 !== year.val().length && 0 !== month.val().length && 0 !== day.val().length) {
                return year.val() + '-' + month.val() + '-' + day.val();
            } else {
                return '';
            }
        },
        is_resource_available: function (args) {
            var availability = args.default_availability,
                year = args.date.getFullYear(),
                month = args.date.getMonth() + 1,
                day = args.date.getDate(),
                day_of_week = args.date.getDay(),
                week = $.datepicker.iso8601Week(args.date);

            // Sunday is 0, Monday is 1, and so on.
            if (day_of_week === 0) {
                day_of_week = 7;
            }

            // `args.fully_booked_days` and `args.resource_id` only available
            // when checking 'automatic' resource assignment.
            if (args.fully_booked_days && args.fully_booked_days[year + '-' + month + '-' + day] && args.fully_booked_days[year + '-' + month + '-' + day][args.resource_id]) {
                return false;
            }

            $.each(args.resource_rules, function (index, rule) {
                var type = rule[0];
                var rules = rule[1];
                try {
                    switch (type) {
                        case 'months':
                            if (typeof rules[month] != 'undefined') {
                                availability = rules[month];
                                return false;
                            }
                            break;
                        case 'weeks':
                            if (typeof rules[week] != 'undefined') {
                                availability = rules[week];
                                return false;
                            }
                            break;
                        case 'days':
                            if (typeof rules[day_of_week] != 'undefined') {
                                availability = rules[day_of_week];
                                return false;
                            }
                            break;
                        case 'custom':
                            if (typeof rules[year][month][day] != 'undefined') {
                                availability = rules[year][month][day];
                                return false;
                            }
                            break;
                        case 'time':
                        case 'time:1':
                        case 'time:2':
                        case 'time:3':
                        case 'time:4':
                        case 'time:5':
                        case 'time:6':
                        case 'time:7':
                            if (false === args.default_availability && ( day_of_week === rules.day || 0 === rules.day )) {
                                availability = rules.rule;
                                // remove return false : https://github.com/woothemes/woocommerce-bookings/issues/646
                            }
                            break;
                        case 'time:range':
                            if (false === args.default_availability && ( typeof rules[year][month][day] != 'undefined' )) {
                                availability = rules[year][month][day].rule;
                                return false;
                            }
                            break;
                    }

                } catch (err) {
                }

                return true;
            });
            return availability;
        },

        has_available_resource: function (args) {
            for (var resource_id in args.availability) {
                resource_id = parseInt(resource_id, 10);

                // Skip resource_id '0' that has been performed before.
                if (0 === resource_id) {
                    continue;
                }

                args.resource_rules = args.availability[resource_id];
                args.resource_id = resource_id;
                if (BOOKING.is_resource_available(args)) {
                    return true;
                }
            }

            return false;
        },

        create_posted_data: function () {

            var product = BOOKING.data.adding_to_cart;

            var data = {
                '_year': '',
                '_month': '',
                '_day': '',
                '_persons': []
            };

            var d = new Date();
            // Get date fields (y, m, d)
            if ($('.booking_date_year').val() != '' && $('.booking_date_month').val() != '' && $('.booking_date_day').val() != '') {
                data['_year'] = parseInt($('.booking_date_year').val());
                data['_year'] = data['_year'] ? data['_year'] : d.getFullYear();
                data['_month'] = parseInt($('.booking_date_month').val());
                data['_day'] = parseInt($('.booking_date_day').val());
                data['_date'] = data['_year'] + '-' + data['_month'] + '-' + data['_day'];

                var date = new Date(Date.UTC(data['_year'], data['_month'] - 1, data['_day']));
                var cur_timezone_date = new Date(data['_year'], data['_month'] - 1, data['_day']);
                data['date'] = jQuery.datepicker.formatDate(pos_booking_form_args.date_format, cur_timezone_date);
                data['date'] = data['date'].replace(/([0-9]+)(s)/gi, '$1');

            }
            // Get year month field
            if ($('input[name="wc_bookings_field_start_date_yearmonth"]').length && $('input[name="wc_bookings_field_start_date_yearmonth"]').val() != '') {
                var date_yearmonth = $('input[name="wc_bookings_field_start_date_yearmonth"]').val().split('-');

                data['_year'] = parseInt(date_yearmonth[0]);
                data['_month'] = parseInt(date_yearmonth[1]);
                data['_day'] = 1;
                data['_date'] = data['_year'] + '-' + data['_month'] + '-' + data['_day'];

                var yearmonth = new Date(Date.UTC(data['_year'], data['_month'] - 1, '01'));

                data['date'] = jQuery.datepicker.formatDate('mm yy', yearmonth);
            }

            // Get time field
            if ($('input[name="wc_bookings_field_start_date_time"]').length && $('input[name="wc_bookings_field_start_date_time"]').val() != '') {
                data['_time'] = $('input[name="wc_bookings_field_start_date_time"]').val();

                data['time'] = this.tConvert(data['_time']);
            } else {
                data['_time'] = '';
            }

            // Quantity being booked
            data['_qty'] = 1;

            // Work out persons
            if (product.booking.has_persons) {
                if (product.booking.has_person_types) {
                    var person_types = product.booking.person_types;

                    for (var i = 0; i < person_types.length; i++) {
                        var person_type = person_types[i];
                        var persons = $('#wc_bookings_field_persons_' + person_type.ID).length ? $('#wc_bookings_field_persons_' + person_type.ID).val() : '';
                        persons = persons != '' ? parseInt(persons) : 0;
                        if (persons > 0) {
                            data[person_type.post_title] = persons;
                            data['_persons'][person_type.ID] = data[person_type.post_title];
                        }
                    }
                }
                else if ($('#wc_bookings_field_persons').length) {
                    data[booking_form_params.i18n_label_persons] = parseInt($('#wc_bookings_field_persons').val());
                    data['_persons'][0] = data[booking_form_params.i18n_label_persons];
                }

                if ('yes' == product.booking.person_qty_multiplier) {
                    data['_qty'] = array_sum(data['_persons']);
                }
            }

            // Fixed duration
            var booking_duration = product.booking.duration;
            var booking_duration_unit = product.booking.duration_unit;
            var total_duration = booking_duration;
            // Duration
            if ('customer' == product.booking.duration_type) {
                var field_duration = $('#wc_bookings_field_duration').length && $('#wc_bookings_field_duration').val() != '' ? $('#wc_bookings_field_duration').val() : 0;
                booking_duration = max(0, parseInt(field_duration));
                booking_duration_unit = product.booking.duration_unit;

                data['_duration_unit'] = booking_duration_unit;
                data['_duration'] = booking_duration;

                // Get the duration * block duration
                var total_duration = booking_duration * product.booking.duration;

                // Nice formatted version
                switch (booking_duration_unit) {
                    case 'month' :
                        data['duration'] = total_duration + ' ' + booking_i18n[1];
                        break;
                    case 'day' :
                        if (total_duration % 7) {
                            data['duration'] = total_duration + ' ' + booking_i18n[5];
                        } else {
                            data['duration'] = ( total_duration / 7 ) + ' ' + booking_i18n[3];
                        }
                        break;
                    case 'hour' :
                        data['duration'] = total_duration + ' ' + booking_i18n[9];
                        break;
                    case 'minute' :
                        data['duration'] = total_duration + ' ' + booking_i18n[11];
                        break;
                    case 'night' :
                        data['duration'] = total_duration + ' ' + booking_i18n[7];
                        break;
                    default :
                        data['duration'] = total_duration;
                        break;
                }
            }

            // Work out start and end dates/times
            var s_date = new Date(Date.UTC(data['_year'], data['_month'] - 1, data['_day']));
            var e_date = new Date(Date.UTC(data['_year'], data['_month'] - 1, data['_day']));

            if (data['_time'] != '') {
                var time = data['_time'].split(':');
                s_date = new Date(Date.UTC(data['_year'], data['_month'] - 1, data['_day'], time[0], time[1]));
                e_date = new Date(Date.UTC(data['_year'], data['_month'] - 1, data['_day'], time[0], time[1]));

                data['_all_day'] = 0;
            } else if ('night' === product.booking.duration_unit) {
                data['_all_day'] = 0;
            } else {
                e_date.setSeconds(e_date.getSeconds() - 1);
                data['_all_day'] = 1;
            }
            switch (booking_duration_unit) {
                case 'month' :
                    e_date.setMonth(e_date.getMonth() + total_duration);
                    break;
                case 'day' :
                case 'night' :
                    e_date.setDate(e_date.getDate() + total_duration);
                    break;
                case 'hour' :
                    e_date.setHours(e_date.getHours() + total_duration);
                    break;
                case 'minute' :
                    e_date.setMinutes(e_date.getMinutes() + total_duration);
                    break;
            }

            data['_start_date'] = s_date.getTime();
            data['_end_date'] = e_date.getTime();

            // Get posted resource or assign one for the date range
            if ('yes' == product.booking.has_resources) {
                if ('customer' == product.booking.resources_assignment) {
                    var field_resource = $(':input[name="wc_bookings_field_resource"]');

                    var resource = field_resource.length ? field_resource.val() : '';
                    if (resource != '') {
                        $.each(product.booking.resources, function (index, val) {
                            if (val.ID == resource) {
                                resource = val;
                                return false;
                            }
                        });
                    }

                    if (resource != '') {
                        data['_resource_id'] = resource.ID;
                        data['type'] = resource.post_title;
                    } else {
                        data['_resource_id'] = 0;
                    }
                }
            }
            var $cost = $('#wc-bookings-booking-cost .amount').html();
            var _cost = $('<div>' + $cost + '</div>');
            _cost.find('.woocommerce-Price-currencySymbol').remove();
            _cost = accountingPOS(_cost.text(), 'unformat');
            data['_cost'] = parseFloat(_cost);
            return data;
        },

        get_booking_data_label: function (key, product) {
            var labels = pos_booking_form_args.bookings_data_labels;
            if (typeof labels[key] == 'undefined') {
                return key;
            }
            if (key == 'type') {
                return product.resouce_label != '' ? product.resouce_label : labels[key];
            }
            return labels[key];
        },

        tConvert: function (time) {
            // Check correct time format and split into components
            time = time.toString().match(/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];

            if (time.length > 1) { // If time format correct
                time = time.slice(1);  // Remove full string match value
                time[5] = +time[0] < 12 ? ' AM' : ' PM'; // Set AM/PM
                time[0] = +time[0] % 12 || 12; // Adjust hours
            }
            return time.join(''); // return adjusted time or original string
        }
        /*****END*****/

    }
});

